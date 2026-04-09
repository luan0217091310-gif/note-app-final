<?php
/**
 * Model User - Xử lý dữ liệu người dùng
 * Sử dụng MySQLi Prepared Statements + bcrypt
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Đăng ký tài khoản mới
     */
    public function register($email, $displayName, $password) {
        // Hash mật khẩu bằng bcrypt
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $activationToken = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare(
            "INSERT INTO users (email, display_name, password, activation_token) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $email, $displayName, $hashedPassword, $activationToken);

        if ($stmt->execute()) {
            return [
                'id' => $this->db->insert_id,
                'email' => $email,
                'display_name' => $displayName,
                'activation_token' => $activationToken,
                'is_activated' => 0
            ];
        }
        return false;
    }

    /**
     * Đăng nhập - Xác thực email + password
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    /**
     * Kích hoạt tài khoản qua token
     */
    public function activate($token) {
        $stmt = $this->db->prepare(
            "UPDATE users SET is_activated = 1, activation_token = NULL WHERE activation_token = ?"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Tạo OTP reset mật khẩu (6 số, hết hạn sau 15 phút)
     */
    public function generateResetOTP($email) {
        $user = $this->findByEmail($email);
        if (!$user) return false;

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $stmt = $this->db->prepare(
            "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?"
        );
        $stmt->bind_param("sss", $otp, $expires, $email);
        $stmt->execute();

        return $otp;
    }

    /**
     * Xác nhận OTP và đặt lại mật khẩu
     */
    public function resetPassword($email, $otp, $newPassword) {
        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE email = ? AND reset_token = ? AND reset_expires > NOW()"
        );
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) return false;

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt2 = $this->db->prepare(
            "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?"
        );
        $stmt2->bind_param("ss", $hashedPassword, $email);
        return $stmt2->execute();
    }

    /**
     * Tìm user theo email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Tìm user theo ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Cập nhật profile (display_name, avatar)
     */
    public function updateProfile($id, $displayName, $avatarPath = null) {
        if ($avatarPath) {
            $stmt = $this->db->prepare(
                "UPDATE users SET display_name = ?, avatar = ? WHERE id = ?"
            );
            $stmt->bind_param("ssi", $displayName, $avatarPath, $id);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE users SET display_name = ? WHERE id = ?"
            );
            $stmt->bind_param("si", $displayName, $id);
        }
        return $stmt->execute();
    }

    /**
     * Đổi mật khẩu (cần xác nhận mật khẩu cũ)
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        $user = $this->findById($id);
        if (!$user || !password_verify($oldPassword, $user['password'])) {
            return false;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $id);
        return $stmt->execute();
    }

    /**
     * Cập nhật tùy chọn cá nhân (font size, màu sắc, theme)
     */
    public function updatePreferences($id, $fontSize, $noteColor, $theme) {
        $stmt = $this->db->prepare(
            "UPDATE users SET font_size = ?, note_color = ?, theme = ? WHERE id = ?"
        );
        $stmt->bind_param("sssi", $fontSize, $noteColor, $theme, $id);
        return $stmt->execute();
    }
}
