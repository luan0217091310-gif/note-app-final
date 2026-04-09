<?php
/**
 * ProfileController - Chỉnh sửa hồ sơ, avatar, mật khẩu, tùy chọn cá nhân
 */

require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Label.php';

class ProfileController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Hiển thị trang profile
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);

        $labelModel = new Label();
        $labels = $labelModel->getAllByUser($userId);

        $success = $_SESSION['profile_success'] ?? null;
        $error = $_SESSION['profile_error'] ?? null;
        unset($_SESSION['profile_success'], $_SESSION['profile_error']);

        require BASE_PATH . '/views/layouts/header.php';
        require BASE_PATH . '/views/layouts/sidebar.php';
        require BASE_PATH . '/views/profile/index.php';
        require BASE_PATH . '/views/layouts/footer.php';
    }

    /**
     * Cập nhật thông tin profile (display_name + avatar)
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . getBaseUrl() . '/profile');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $displayName = trim($_POST['display_name'] ?? '');

        if (empty($displayName)) {
            $_SESSION['profile_error'] = 'Tên hiển thị không được trống.';
            header('Location: ' . getBaseUrl() . '/profile');
            exit;
        }

        $avatarPath = null;

        // Upload avatar nếu có
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Validate
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
            finfo_close($finfo);

            if (in_array($mimeType, $allowedTypes)) {
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $newName = 'avatar_' . $userId . '_' . time() . '.' . $ext;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $newName)) {
                    $avatarPath = 'uploads/avatars/' . $newName;

                    // Xóa avatar cũ
                    $user = $this->userModel->findById($userId);
                    if ($user['avatar']) {
                        $oldPath = PUBLIC_PATH . '/' . $user['avatar'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                }
            }
        }

        $result = $this->userModel->updateProfile($userId, $displayName, $avatarPath);

        if ($result) {
            $_SESSION['user_name'] = $displayName;
            if ($avatarPath) {
                $_SESSION['user_avatar'] = $avatarPath;
            }
            $_SESSION['profile_success'] = 'Cập nhật hồ sơ thành công!';
        } else {
            $_SESSION['profile_error'] = 'Đã xảy ra lỗi.';
        }

        header('Location: ' . getBaseUrl() . '/profile');
        exit;
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . getBaseUrl() . '/profile');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($oldPassword) || empty($newPassword)) {
            $_SESSION['profile_error'] = 'Vui lòng nhập đầy đủ thông tin.';
            header('Location: ' . getBaseUrl() . '/profile');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['profile_error'] = 'Mật khẩu xác nhận không khớp.';
            header('Location: ' . getBaseUrl() . '/profile');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['profile_error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
            header('Location: ' . getBaseUrl() . '/profile');
            exit;
        }

        $result = $this->userModel->changePassword($userId, $oldPassword, $newPassword);

        if ($result) {
            $_SESSION['profile_success'] = 'Đổi mật khẩu thành công!';
        } else {
            $_SESSION['profile_error'] = 'Mật khẩu cũ không đúng.';
        }

        header('Location: ' . getBaseUrl() . '/profile');
        exit;
    }

    /**
     * Cập nhật tùy chọn cá nhân (API - JSON)
     */
    public function updatePreferences() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        $data = json_decode(file_get_contents('php://input'), true);
        $fontSize = $data['font_size'] ?? 'medium';
        $noteColor = $data['note_color'] ?? '#ffffff';
        $theme = $data['theme'] ?? 'light';

        $result = $this->userModel->updatePreferences($userId, $fontSize, $noteColor, $theme);

        if ($result) {
            $_SESSION['theme'] = $theme;
            $_SESSION['font_size'] = $fontSize;
            $_SESSION['note_color'] = $noteColor;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Không thể cập nhật']);
        }
    }
}
