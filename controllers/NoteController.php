<?php
/**
 * NoteController - CRUD, Auto-save, Pin, Lock, Share, Upload ảnh
 */

require_once BASE_PATH . '/models/Note.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Label.php';

class NoteController {
    private $noteModel;
    private $userModel;
    private $labelModel;

    public function __construct() {
        $this->noteModel = new Note();
        $this->userModel = new User();
        $this->labelModel = new Label();
    }

    /**
     * Trang chủ - Hiển thị danh sách ghi chú (Grid/List)
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $search = $_GET['search'] ?? '';
        $labelId = $_GET['label_id'] ?? null;
        $view = $_GET['view'] ?? 'grid'; // grid hoặc list

        $notes = $this->noteModel->getAllByUser($userId, $search, $labelId);
        $labels = $this->labelModel->getAllByUser($userId);
        $user = $this->userModel->findById($userId);

        require BASE_PATH . '/views/layouts/header.php';
        require BASE_PATH . '/views/layouts/sidebar.php';
        require BASE_PATH . '/views/notes/index.php';
        require BASE_PATH . '/views/layouts/footer.php';
    }

    /**
     * Lấy chi tiết ghi chú (API - JSON)
     */
    public function get($id = null) {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        if (!$id) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $access = $this->noteModel->checkAccess($id, $userId);
        if (!$access) {
            echo json_encode(['error' => 'Không có quyền truy cập']);
            return;
        }

        // Kiểm tra lock (trừ khi đã verify trong session)
        if ($this->noteModel->isLocked($id) && !isset($_SESSION['unlocked_notes'][$id])) {
            echo json_encode(['locked' => true, 'id' => $id]);
            return;
        }

        $note = $this->noteModel->getById($id);
        $note['access'] = $access;
        echo json_encode($note);
    }

    /**
     * Tạo ghi chú mới (API - JSON)
     */
    public function create() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        $noteId = $this->noteModel->create($userId);

        if ($noteId) {
            echo json_encode(['success' => true, 'id' => $noteId]);
        } else {
            echo json_encode(['error' => 'Không thể tạo ghi chú']);
        }
    }

    /**
     * Auto-save - Cập nhật tiêu đề và nội dung (API - JSON)
     */
    public function save() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';

        if (!$id) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $access = $this->noteModel->checkAccess($id, $userId);
        if ($access !== 'owner' && $access !== 'edit') {
            echo json_encode(['error' => 'Không có quyền chỉnh sửa']);
            return;
        }

        $result = $this->noteModel->update($id, $title, $content);
        echo json_encode(['success' => $result]);
    }

    /**
     * Xóa ghi chú (API - JSON)
     */
    public function delete($id = null) {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        if (!$id) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        // Chỉ chủ sở hữu mới được xóa
        $access = $this->noteModel->checkAccess($id, $userId);
        if ($access !== 'owner') {
            echo json_encode(['error' => 'Chỉ chủ sở hữu mới có thể xóa']);
            return;
        }

        $result = $this->noteModel->delete($id);
        echo json_encode(['success' => $result]);
    }

    /**
     * Bật/tắt ghim ghi chú (API - JSON)
     */
    public function togglePin($id = null) {
        header('Content-Type: application/json');

        if (!$id) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $result = $this->noteModel->togglePin($id);
        echo json_encode(['success' => $result]);
    }

    // ========================
    // LOCK / UNLOCK
    // ========================

    /**
     * Đặt/Đổi mật khẩu khóa ghi chú (API - JSON)
     */
    public function setLock() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $password = $data['password'] ?? '';

        if (!$id || empty($password)) {
            echo json_encode(['error' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->noteModel->setLockPassword($id, $password);
        echo json_encode(['success' => $result]);
    }

    /**
     * Gỡ khóa ghi chú (API - JSON)
     */
    public function removeLock() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $result = $this->noteModel->removeLock($id);
        // Xóa trạng thái unlock trong session
        unset($_SESSION['unlocked_notes'][$id]);
        echo json_encode(['success' => $result]);
    }

    /**
     * Xác nhận mật khẩu ghi chú bị khóa (API - JSON)
     */
    public function verifyLock() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $password = $data['password'] ?? '';

        if (!$id) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        if ($this->noteModel->verifyLock($id, $password)) {
            // Lưu trạng thái đã unlock vào session
            if (!isset($_SESSION['unlocked_notes'])) {
                $_SESSION['unlocked_notes'] = [];
            }
            $_SESSION['unlocked_notes'][$id] = true;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Mật khẩu không đúng']);
        }
    }

    // ========================
    // UPLOAD HÌNH ẢNH
    // ========================

    /**
     * Upload hình ảnh đính kèm (API - JSON)
     */
    public function uploadImages() {
        header('Content-Type: application/json');
        $noteId = $_POST['note_id'] ?? null;

        if (!$noteId || empty($_FILES['images'])) {
            echo json_encode(['error' => 'Thiếu thông tin']);
            return;
        }

        $uploadDir = PUBLIC_PATH . '/uploads/notes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploaded = [];
        $files = $_FILES['images'];

        // Xử lý nhiều file
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $fileCount; $i++) {
            $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];

            if ($error !== UPLOAD_ERR_OK) continue;

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) continue;

            // Tạo tên file unique
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $newName = uniqid('note_') . '.' . $ext;
            $destPath = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, $destPath)) {
                $relativePath = 'uploads/notes/' . $newName;
                $this->noteModel->addImage($noteId, $relativePath);
                $uploaded[] = ['path' => $relativePath, 'name' => $name];
            }
        }

        echo json_encode(['success' => true, 'images' => $uploaded]);
    }

    /**
     * Xóa hình ảnh đính kèm (API - JSON)
     */
    public function removeImage($imageId = null) {
        header('Content-Type: application/json');

        if (!$imageId) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $result = $this->noteModel->removeImage($imageId);
        echo json_encode(['success' => $result]);
    }

    // ========================
    // CHIA SẺ GHI CHÚ
    // ========================

    /**
     * Chia sẻ ghi chú (API - JSON)
     */
    public function share() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        $data = json_decode(file_get_contents('php://input'), true);
        $noteId = $data['note_id'] ?? null;
        $email = trim($data['email'] ?? '');
        $permission = $data['permission'] ?? 'read';

        if (!$noteId || empty($email)) {
            echo json_encode(['error' => 'Thiếu thông tin']);
            return;
        }

        // Tìm user được chia sẻ
        $sharedUser = $this->userModel->findByEmail($email);
        if (!$sharedUser) {
            echo json_encode(['error' => 'Email không tồn tại trong hệ thống']);
            return;
        }

        if ($sharedUser['id'] == $userId) {
            echo json_encode(['error' => 'Không thể chia sẻ với chính mình']);
            return;
        }

        $result = $this->noteModel->share($noteId, $userId, $sharedUser['id'], $permission);

        if ($result) {
            // Gửi email thông báo
            $note = $this->noteModel->getById($noteId);
            sendShareNotification($email, $_SESSION['user_name'], $note['title'], $permission);

            echo json_encode(['success' => true, 'shared_with' => $sharedUser['display_name']]);
        } else {
            echo json_encode(['error' => 'Không thể chia sẻ']);
        }
    }

    /**
     * Hiển thị ghi chú được chia sẻ với tôi
     */
    public function sharedWithMe() {
        $userId = $_SESSION['user_id'];
        $sharedNotes = $this->noteModel->getSharedWithMe($userId);
        $labels = $this->labelModel->getAllByUser($userId);
        $user = $this->userModel->findById($userId);

        require BASE_PATH . '/views/layouts/header.php';
        require BASE_PATH . '/views/layouts/sidebar.php';
        require BASE_PATH . '/views/shared/index.php';
        require BASE_PATH . '/views/layouts/footer.php';
    }

    /**
     * Cập nhật quyền chia sẻ (API - JSON)
     */
    public function updatePermission() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $shareId = $data['share_id'] ?? null;
        $permission = $data['permission'] ?? 'read';

        if (!$shareId) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $result = $this->noteModel->updateSharePermission($shareId, $permission);
        echo json_encode(['success' => $result]);
    }

    /**
     * Thu hồi chia sẻ (API - JSON)
     */
    public function revokeShare($shareId = null) {
        header('Content-Type: application/json');

        if (!$shareId) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $result = $this->noteModel->revokeShare($shareId);
        echo json_encode(['success' => $result]);
    }

    /**
     * Gắn labels cho ghi chú (API - JSON)
     */
    public function syncLabels() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $noteId = $data['note_id'] ?? null;
        $labelIds = $data['label_ids'] ?? [];

        if (!$noteId) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $result = $this->labelModel->syncNoteLabels($noteId, $labelIds);
        echo json_encode(['success' => $result]);
    }

    /**
     * Tìm kiếm ghi chú (API - JSON cho live search)
     */
    public function search() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $search = $_GET['q'] ?? '';
        $labelId = $_GET['label_id'] ?? null;

        $notes = $this->noteModel->getAllByUser($userId, $search, $labelId);
        echo json_encode($notes);
    }
}
