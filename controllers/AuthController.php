<?php
/**
 * AuthController - Xử lý Đăng ký, Đăng nhập, Kích hoạt, Reset Password
 */

require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/config/mail.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function login() {
        $error = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
        require BASE_PATH . '/views/auth/login.php';
    }

    /**
     * Xử lý đăng nhập
     */
    public function doLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . getBaseUrl() . '/auth/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['auth_error'] = 'Vui lòng nhập đầy đủ thông tin.';
            header('Location: ' . getBaseUrl() . '/auth/login');
            exit;
        }

        $user = $this->userModel->login($email, $password);

        if ($user) {
            if ($user['is_activated'] == 0) {
                $_SESSION['auth_error'] = 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email của bạn.';
                header('Location: ' . getBaseUrl() . '/auth/login');
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['display_name'];
            $_SESSION['user_avatar'] = $user['avatar'];
            $_SESSION['is_activated'] = $user['is_activated'];
            $_SESSION['theme'] = $user['theme'];
            $_SESSION['font_size'] = $user['font_size'];
            $_SESSION['note_color'] = $user['note_color'];

            header('Location: ' . getBaseUrl() . '/notes');
            exit;
        } else {
            $_SESSION['auth_error'] = 'Email hoặc mật khẩu không đúng.';
            header('Location: ' . getBaseUrl() . '/auth/login');
            exit;
        }
    }

    /**
     * Hiển thị form đăng ký
     */
    public function register() {
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        require BASE_PATH . '/views/auth/register.php';
    }

    /**
     * Xử lý đăng ký
     */
    public function doRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . getBaseUrl() . '/auth/register');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $displayName = trim($_POST['display_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate
        if (empty($email) || empty($displayName) || empty($password)) {
            $_SESSION['auth_error'] = 'Vui lòng nhập đầy đủ thông tin.';
            header('Location: ' . getBaseUrl() . '/auth/register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_error'] = 'Email không hợp lệ.';
            header('Location: ' . getBaseUrl() . '/auth/register');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['auth_error'] = 'Mật khẩu xác nhận không khớp.';
            header('Location: ' . getBaseUrl() . '/auth/register');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['auth_error'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
            header('Location: ' . getBaseUrl() . '/auth/register');
            exit;
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
            $_SESSION['auth_error'] = 'Mật khẩu phải có chữ HOA, chữ thường, số, và ký tự đặc biệt.';
            header('Location: ' . getBaseUrl() . '/auth/register');
            exit;
        }

        // Kiểm tra email đã tồn tại
        if ($this->userModel->findByEmail($email)) {
            $_SESSION['auth_error'] = 'Email đã được sử dụng.';
            header('Location: ' . getBaseUrl() . '/auth/register');
            exit;
        }

        // Tạo tài khoản
        $result = $this->userModel->register($email, $displayName, $password);

        if ($result) {
            // Gửi email kích hoạt
            sendActivationEmail($email, $result['activation_token']);

            // Chuyển hướng người dùng về trang đăng nhập yêu cầu kích hoạt
            $_SESSION['auth_success'] = 'Đăng ký thành công! Vui lòng kiểm tra email để kích hoạt tài khoản.';
            header('Location: ' . getBaseUrl() . '/auth/login');
            exit;
        } else {
            $_SESSION['auth_error'] = 'Đã xảy ra lỗi. Vui lòng thử lại.';
            header('Location: ' . getBaseUrl() . '/auth/register');
            exit;
        }
    }

    /**
     * Kích hoạt tài khoản qua link trong email
     */
    public function activate() {
        $token = $_GET['token'] ?? '';

        if (!empty($token) && $this->userModel->activate($token)) {
            // Cập nhật session nếu đang đăng nhập
            if (isset($_SESSION['user_id'])) {
                $_SESSION['is_activated'] = 1;
            }
            $_SESSION['auth_success'] = 'Tài khoản đã được kích hoạt thành công!';
        } else {
            $_SESSION['auth_error'] = 'Liên kết kích hoạt không hợp lệ hoặc đã hết hạn.';
        }

        if (isset($_SESSION['user_id'])) {
            header('Location: ' . getBaseUrl() . '/notes');
        } else {
            header('Location: ' . getBaseUrl() . '/auth/login');
        }
        exit;
    }

    /**
     * Hiển thị form quên mật khẩu
     */
    public function forgot() {
        $error = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
        require BASE_PATH . '/views/auth/forgot.php';
    }

    /**
     * Xử lý gửi OTP reset password
     */
    public function doForgot() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . getBaseUrl() . '/auth/forgot');
            exit;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['auth_error'] = 'Vui lòng nhập email.';
            header('Location: ' . getBaseUrl() . '/auth/forgot');
            exit;
        }

        $otp = $this->userModel->generateResetOTP($email);

        if ($otp) {
            sendResetEmail($email, $otp);
            $_SESSION['reset_email'] = $email;
            header('Location: ' . getBaseUrl() . '/auth/reset');
        } else {
            $_SESSION['auth_error'] = 'Email không tồn tại trong hệ thống.';
            header('Location: ' . getBaseUrl() . '/auth/forgot');
        }
        exit;
    }

    /**
     * Hiển thị form nhập OTP + mật khẩu mới
     */
    public function reset() {
        if (!isset($_SESSION['reset_email'])) {
            header('Location: ' . getBaseUrl() . '/auth/forgot');
            exit;
        }
        $email = $_SESSION['reset_email'];
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        require BASE_PATH . '/views/auth/reset.php';
    }

    /**
     * Xử lý reset password
     */
    public function doReset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . getBaseUrl() . '/auth/forgot');
            exit;
        }

        $email = $_SESSION['reset_email'] ?? '';
        $otp = trim($_POST['otp'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($otp) || empty($newPassword)) {
            $_SESSION['auth_error'] = 'Vui lòng nhập đầy đủ thông tin.';
            header('Location: ' . getBaseUrl() . '/auth/reset');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['auth_error'] = 'Mật khẩu xác nhận không khớp.';
            header('Location: ' . getBaseUrl() . '/auth/reset');
            exit;
        }

        if ($this->userModel->resetPassword($email, $otp, $newPassword)) {
            unset($_SESSION['reset_email']);
            $_SESSION['auth_success'] = 'Mật khẩu đã được đặt lại. Vui lòng đăng nhập.';
            header('Location: ' . getBaseUrl() . '/auth/login');
        } else {
            $_SESSION['auth_error'] = 'Mã OTP không đúng hoặc đã hết hạn.';
            header('Location: ' . getBaseUrl() . '/auth/reset');
        }
        exit;
    }

    /**
     * Đăng xuất
     */
    public function logout() {
        session_destroy();
        header('Location: ' . getBaseUrl() . '/auth/login');
        exit;
    }
}
