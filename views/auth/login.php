<?php
/**
 * Trang Đăng nhập
 */
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Note App</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">📝</div>
                <h1>Đăng nhập</h1>
                <p>Chào mừng trở lại! Đăng nhập để quản lý ghi chú.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/auth/doLogin" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Nhập email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
            </form>

            <div class="auth-links">
                <a href="<?= $baseUrl ?>/auth/forgot">Quên mật khẩu?</a>
                <span class="divider">|</span>
                <a href="<?= $baseUrl ?>/auth/register">Tạo tài khoản mới</a>
            </div>
        </div>
    </div>
</body>
</html>
