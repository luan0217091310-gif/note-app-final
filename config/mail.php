<?php
/**
 * Mail Configuration
 * Cấu hình gửi email kích hoạt tài khoản và reset password
 * Sử dụng PHPMailer qua SMTP Gmail
 */

// Kiểm tra xem vendor/autoload.php có tồn tại không
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Nếu download thủ công:
// require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
// require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
// require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ================================
// CẤU HÌNH SMTP - THAY ĐỔI THEO TÀI KHOẢN CỦA BẠN
// ================================
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'luan0817091310@gmail.com');      // Email Gmail của bạn
define('MAIL_PASSWORD', 'ziokmqdoekompaqf');           // App Password (không phải mật khẩu Gmail)
define('MAIL_FROM_NAME', 'Note App');
define('APP_URL', 'http://localhost/note_app');             // URL ứng dụng

/**
 * Tạo instance PHPMailer đã cấu hình sẵn SMTP
 */
function createMailer() {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        throw new \Exception("Vui lòng chạy 'composer require phpmailer/phpmailer' để gửi email.");
    }

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);

    return $mail;
}

/**
 * Gửi email kích hoạt tài khoản
 * @param string $toEmail Địa chỉ email người nhận
 * @param string $token   Token kích hoạt
 * @return bool Thành công hay không
 */
function sendActivationEmail($toEmail, $token) {
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Kích hoạt tài khoản Note App';

        $activationLink = APP_URL . '/auth/activate?token=' . urlencode($token);

        // Lưu log để test local mà không cần gửi email thật
        $logContent = "[" . date('Y-m-d H:i:s') . "] ACTIVATION: To: $toEmail | Link: $activationLink\n";
        file_put_contents(__DIR__ . '/../mail_debug.log', $logContent, FILE_APPEND);

        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #6c5ce7;">Note App - Kích hoạt tài khoản</h2>
            <p>Xin chào,</p>
            <p>Cảm ơn bạn đã đăng ký tài khoản. Vui lòng nhấn nút bên dưới để kích hoạt:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="' . $activationLink . '" 
                   style="background: #6c5ce7; color: #fff; padding: 12px 30px; 
                          text-decoration: none; border-radius: 6px; font-weight: bold;">
                    Kích hoạt tài khoản
                </a>
            </p>
            <p style="color: #999; font-size: 13px;">Nếu bạn không đăng ký, vui lòng bỏ qua email này.</p>
        </div>';

        $mail->AltBody = "Kích hoạt tài khoản: $activationLink";

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log("Mail Error (Activation): " . $e->getMessage());
        return false;
    }
}

/**
 * Gửi email OTP reset mật khẩu
 * @param string $toEmail Địa chỉ email người nhận
 * @param string $otp     Mã OTP 6 số
 * @return bool Thành công hay không
 */
function sendResetEmail($toEmail, $otp) {
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Đặt lại mật khẩu - Note App';

        // Lưu log để test local mà không cần gửi email thật
        $logContent = "[" . date('Y-m-d H:i:s') . "] RESET OTP: To: $toEmail | OTP: $otp\n";
        file_put_contents(__DIR__ . '/../mail_debug.log', $logContent, FILE_APPEND);

        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #6c5ce7;">Note App - Đặt lại mật khẩu</h2>
            <p>Xin chào,</p>
            <p>Mã OTP để đặt lại mật khẩu của bạn là:</p>
            <p style="text-align: center; margin: 30px 0;">
                <span style="background: #f0f0f0; padding: 15px 30px; font-size: 28px; 
                             font-weight: bold; letter-spacing: 8px; border-radius: 8px; color: #6c5ce7;">
                    ' . $otp . '
                </span>
            </p>
            <p>Mã này có hiệu lực trong <strong>15 phút</strong>.</p>
            <p style="color: #999; font-size: 13px;">Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
        </div>';

        $mail->AltBody = "Mã OTP đặt lại mật khẩu: $otp (có hiệu lực 15 phút)";

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log("Mail Error (Reset): " . $e->getMessage());
        return false;
    }
}

/**
 * Gửi thông báo chia sẻ ghi chú
 * @param string $toEmail    Email người nhận
 * @param string $ownerName  Tên người chia sẻ
 * @param string $noteTitle  Tiêu đề ghi chú
 * @param string $permission Quyền (read/edit)
 * @return bool
 */
function sendShareNotification($toEmail, $ownerName, $noteTitle, $permission) {
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $ownerName . ' đã chia sẻ ghi chú với bạn - Note App';

        $permText = $permission === 'edit' ? 'chỉnh sửa' : 'chỉ xem';

        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #6c5ce7;">Note App - Ghi chú được chia sẻ</h2>
            <p><strong>' . htmlspecialchars($ownerName) . '</strong> đã chia sẻ ghi chú 
               "<em>' . htmlspecialchars($noteTitle) . '</em>" với bạn.</p>
            <p>Quyền: <strong>' . $permText . '</strong></p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="' . APP_URL . '/shared" 
                   style="background: #6c5ce7; color: #fff; padding: 12px 30px; 
                          text-decoration: none; border-radius: 6px;">
                    Xem ghi chú
                </a>
            </p>
        </div>';

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log("Mail Error (Share): " . $e->getMessage());
        return false;
    }
}
