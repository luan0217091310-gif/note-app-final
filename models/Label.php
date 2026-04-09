<?php
/**
 * Model Label - Quản lý nhãn và quan hệ với ghi chú
 * Sử dụng MySQLi Prepared Statements
 */

require_once __DIR__ . '/../config/database.php';

class Label {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Lấy tất cả labels của user
     */
    public function getAllByUser($userId) {
        $stmt = $this->db->prepare(
            "SELECT l.*, 
                    (SELECT COUNT(*) FROM note_labels nl WHERE nl.label_id = l.id) as note_count
             FROM labels l 
             WHERE l.user_id = ? 
             ORDER BY l.name ASC"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy label theo ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM labels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Thêm nhãn mới
     */
    public function create($userId, $name) {
        $stmt = $this->db->prepare(
            "INSERT INTO labels (user_id, name) VALUES (?, ?)"
        );
        $stmt->bind_param("is", $userId, $name);
        $stmt->execute();
        return $this->db->insert_id;
    }

    /**
     * Đổi tên nhãn (tự động cập nhật cho tất cả notes liên quan vì dùng FK)
     */
    public function update($id, $name) {
        $stmt = $this->db->prepare("UPDATE labels SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        return $stmt->execute();
    }

    /**
     * Xóa nhãn (CASCADE sẽ tự xóa liên kết trong note_labels)
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM labels WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Gắn labels cho ghi chú (xóa hết rồi gắn lại)
     */
    public function syncNoteLabels($noteId, $labelIds = []) {
        // Xóa tất cả labels hiện tại của note
        $stmt = $this->db->prepare("DELETE FROM note_labels WHERE note_id = ?");
        $stmt->bind_param("i", $noteId);
        $stmt->execute();

        // Gắn labels mới
        if (!empty($labelIds)) {
            $stmt2 = $this->db->prepare(
                "INSERT INTO note_labels (note_id, label_id) VALUES (?, ?)"
            );
            foreach ($labelIds as $labelId) {
                $stmt2->bind_param("ii", $noteId, $labelId);
                $stmt2->execute();
            }
        }
        return true;
    }

    /**
     * Gắn 1 label cho ghi chú
     */
    public function attachToNote($noteId, $labelId) {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO note_labels (note_id, label_id) VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $noteId, $labelId);
        return $stmt->execute();
    }

    /**
     * Gỡ 1 label khỏi ghi chú
     */
    public function detachFromNote($noteId, $labelId) {
        $stmt = $this->db->prepare(
            "DELETE FROM note_labels WHERE note_id = ? AND label_id = ?"
        );
        $stmt->bind_param("ii", $noteId, $labelId);
        return $stmt->execute();
    }

    /**
     * Lấy labels của 1 ghi chú
     */
    public function getNoteLabels($noteId) {
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
