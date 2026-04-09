<?php
/**
 * Trang Quên mật khẩu - Nhập email để nhận OTP
 */
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Note App</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">🔐</div>
                <h1>Quên mật khẩu</h1>
                <p>Nhập email đã đăng ký để nhận mã OTP đặt lại mật khẩu.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/auth/doForgot" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Nhập địa chỉ email" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Gửi mã OTP</button>
            </form>

            <div class="auth-links">
                <a href="<?= $baseUrl ?>/auth/login">← Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
</body>
</html>
