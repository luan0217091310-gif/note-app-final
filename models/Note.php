<?php
/**
 * Model Note - Xử lý ghi chú, hình ảnh, pin, lock, share
 * Sử dụng MySQLi Prepared Statements
 */

require_once __DIR__ . '/../config/database.php';

class Note {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Lấy tất cả ghi chú của user (pinned lên đầu, mới nhất trước)
     * Hỗ trợ tìm kiếm và lọc theo label
     */
    public function getAllByUser($userId, $search = '', $labelId = null) {
        $sql = "SELECT DISTINCT n.*, 
                (SELECT GROUP_CONCAT(l.name SEPARATOR ', ') 
                 FROM note_labels nl 
                 JOIN labels l ON nl.label_id = l.id 
                 WHERE nl.note_id = n.id) as label_names,
                (SELECT GROUP_CONCAT(l.id SEPARATOR ',') 
                 FROM note_labels nl 
                 JOIN labels l ON nl.label_id = l.id 
                 WHERE nl.note_id = n.id) as label_ids,
                (SELECT COUNT(*) FROM note_shares ns WHERE ns.note_id = n.id) as share_count
                FROM notes n ";

        $params = [];
        $types = '';

        // JOIN nếu lọc theo label
        if ($labelId) {
            $sql .= "JOIN note_labels nl ON n.id = nl.note_id ";
        }

        $sql .= "WHERE n.user_id = ? ";
        $params[] = $userId;
        $types .= 'i';

        // Tìm kiếm theo title và content
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $sql .= "AND (n.title LIKE ? OR n.content LIKE ?) ";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        // Lọc theo label
        if ($labelId) {
            $sql .= "AND nl.label_id = ? ";
            $params[] = $labelId;
            $types .= 'i';
        }

        // Sắp xếp: pinned trước, sau đó theo updated_at mới nhất
        $sql .= "ORDER BY n.is_pinned DESC, n.pinned_at DESC, n.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy chi tiết ghi chú theo ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $note = $stmt->get_result()->fetch_assoc();

        if ($note) {
            // Lấy danh sách hình ảnh
            $note['images'] = $this->getImages($id);
            // Lấy labels
            $note['labels'] = $this->getNoteLabels($id);
            // Lấy share info
            $note['shares'] = $this->getShares($id);
        }

        return $note;
    }

    /**
     * Tạo ghi chú mới
     */
    public function create($userId, $title = '', $content = '') {
        $stmt = $this->db->prepare(
            "INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iss", $userId, $title, $content);
        $stmt->execute();
        return $this->db->insert_id;
    }

    /**
     * Cập nhật ghi chú (dùng cho auto-save)
     */
    public function update($id, $title, $content) {
        $stmt = $this->db->prepare(
            "UPDATE notes SET title = ?, content = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $title, $content, $id);
        return $stmt->execute();
    }

    /**
     * Xóa ghi chú (CASCADE sẽ xóa images, labels, shares)
     */
    public function delete($id) {
        // Xóa file ảnh trên server trước
        $images = $this->getImages($id);
        foreach ($images as $img) {
            $filepath = __DIR__ . '/../public/' . $img['image_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Bật/tắt ghim ghi chú
     */
    public function togglePin($id) {
        $note = $this->getById($id);
        $newPinned = $note['is_pinned'] ? 0 : 1;
        $pinnedAt = $newPinned ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare(
            "UPDATE notes SET is_pinned = ?, pinned_at = ? WHERE id = ?"
        );
        $stmt->bind_param("isi", $newPinned, $pinnedAt, $id);
        return $stmt->execute();
    }

    /**
     * Đặt/đổi mật khẩu khóa ghi chú
     */
    public function setLockPassword($id, $password) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE notes SET lock_password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $id);
        return $stmt->execute();
    }

    /**
     * Gỡ khóa ghi chú
     */
    public function removeLock($id) {
        $stmt = $this->db->prepare("UPDATE notes SET lock_password = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Xác nhận mật khẩu khóa
     */
    public function verifyLock($id, $password) {
        $stmt = $this->db->prepare("SELECT lock_password FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result || !$result['lock_password']) return true; // Không có lock
        return password_verify($password, $result['lock_password']);
    }

    /**
     * Kiểm tra ghi chú có bị khóa không
     */
    public function isLocked($id) {
        $stmt = $this->db->prepare("SELECT lock_password FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result && $result['lock_password'] !== null;
    }

    // ========================
    // QUẢN LÝ HÌNH ẢNH
    // ========================

    /**
     * Lấy danh sách hình ảnh của ghi chú
     */
    public function getImages($noteId) {
        $stmt = $this->db->prepare("SELECT * FROM note_images WHERE note_id = ?");
        $stmt->bind_param("i", $noteId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Thêm hình ảnh đính kèm
     */
    public function addImage($noteId, $imagePath) {
        $stmt = $this->db->prepare(
            "INSERT INTO note_images (note_id, image_path) VALUES (?, ?)"
        );
        $stmt->bind_param("is", $noteId, $imagePath);
        return $stmt->execute();
    }

    /**
     * Xóa hình ảnh
     */
    public function removeImage($imageId) {
        // Lấy path trước khi xóa
        $stmt = $this->db->prepare("SELECT image_path FROM note_images WHERE id = ?");
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        $img = $stmt->get_result()->fetch_assoc();

        if ($img) {
            $filepath = __DIR__ . '/../public/' . $img['image_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $stmt2 = $this->db->prepare("DELETE FROM note_images WHERE id = ?");
            $stmt2->bind_param("i", $imageId);
            return $stmt2->execute();
        }
        return false;
    }

    // ========================
    // CHIA SẺ GHI CHÚ
    // ========================

    /**
     * Chia sẻ ghi chú với user khác
     */
    public function share($noteId, $ownerId, $sharedWithId, $permission = 'read') {
        $stmt = $this->db->prepare(
            "INSERT INTO note_shares (note_id, owner_id, shared_with_id, permission) 
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE permission = ?"
        );
        $stmt->bind_param("iiiss", $noteId, $ownerId, $sharedWithId, $permission, $permission);
        return $stmt->execute();
    }

    /**
     * Lấy ghi chú được chia sẻ với user
     */
    public function getSharedWithMe($userId) {
        $stmt = $this->db->prepare(
            "SELECT n.*, ns.permission, ns.shared_at, ns.id as share_id,
                    u.display_name as owner_name, u.email as owner_email
             FROM note_shares ns
             JOIN notes n ON ns.note_id = n.id
             JOIN users u ON ns.owner_id = u.id
             WHERE ns.shared_with_id = ?
             ORDER BY ns.shared_at DESC"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy thông tin chia sẻ của ghi chú
     */
    public function getShares($noteId) {
        $stmt = $this->db->prepare(
            "SELECT ns.*, u.display_name, u.email 
             FROM note_shares ns 
             JOIN users u ON ns.shared_with_id = u.id 
             WHERE ns.note_id = ?"
        );
        $stmt->bind_param("i", $noteId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Cập nhật quyền chia sẻ
     */
    public function updateSharePermission($shareId, $permission) {
        $stmt = $this->db->prepare(
            "UPDATE note_shares SET permission = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $permission, $shareId);
        return $stmt->execute();
    }

    /**
     * Thu hồi chia sẻ
     */
    public function revokeShare($shareId) {
        $stmt = $this->db->prepare("DELETE FROM note_shares WHERE id = ?");
        $stmt->bind_param("i", $shareId);
        return $stmt->execute();
    }

    /**
     * Kiểm tra quyền truy cập ghi chú (chủ sở hữu hoặc được chia sẻ)
     */
    public function checkAccess($noteId, $userId) {
        // Kiểm tra chủ sở hữu
        $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $noteId, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return 'owner';
        }

        // Kiểm tra chia sẻ
        $stmt2 = $this->db->prepare(
            "SELECT permission FROM note_shares WHERE note_id = ? AND shared_with_id = ?"
        );
        $stmt2->bind_param("ii", $noteId, $userId);
        $stmt2->execute();
        $share = $stmt2->get_result()->fetch_assoc();

        return $share ? $share['permission'] : false;
    }

    /**
     * Lấy labels của ghi chú
     */
    private function getNoteLabels($noteId) {
        $stmt = $this->db->prepare(
            "SELECT l.* FROM labels l 
             JOIN note_labels nl ON l.id = nl.label_id 
             WHERE nl.note_id = ?"
        );
        $stmt->bind_param("i", $noteId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
