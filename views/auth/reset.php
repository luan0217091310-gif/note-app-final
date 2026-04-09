<?php
/**
 * Trang Reset mật khẩu - Nhập OTP + mật khẩu mới
 */
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Note App</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">🔑</div>
                <h1>Đặt lại mật khẩu</h1>
                <p>Mã OTP đã được gửi đến <strong><?= htmlspecialchars($email ?? '') ?></strong></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/auth/doReset" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="otp">Mã OTP</label>
                    <input type="text" id="otp" name="otp" placeholder="Nhập mã 6 số" required maxlength="6" 
                           pattern="[0-9]{6}" class="otp-input" autofocus autocomplete="one-time-code">
                </div>

                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Tối thiểu 6 ký tự" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Đặt lại mật khẩu</button>
            </form>

            <div class="auth-links">
                <a href="<?= $baseUrl ?>/auth/forgot">← Gửi lại mã OTP</a>
            </div>
        </div>
    </div>
</body>
</html>
