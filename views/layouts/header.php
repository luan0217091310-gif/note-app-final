<?php
/**
 * Header Layout - Navbar + Activation Banner
 */
$baseUrl = getBaseUrl();
$theme = $_SESSION['theme'] ?? 'light';
$fontSize = $_SESSION['font_size'] ?? 'medium';
$isActivated = $_SESSION['is_activated'] ?? 1;
?>
<!DOCTYPE html>
<html lang="vi" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Note App - Ứng dụng quản lý ghi chú cá nhân">
    <title>Note App - Quản lý ghi chú</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="theme-color" content="#6c5ce7">
</head>
<body class="font-<?= htmlspecialchars($fontSize) ?>">

<!-- Banner kích hoạt tài khoản -->
<?php if (isset($_SESSION['user_id']) && !$isActivated): ?>
<div class="activation-banner" id="activationBanner">
    <div class="activation-content">
        <span class="activation-icon">⚠️</span>
        <span>Tài khoản chưa được xác minh. Vui lòng kiểm tra email để kích hoạt tài khoản.</span>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
<!-- Navbar chính -->
<nav class="navbar">
    <div class="navbar-left">
        <button class="btn-icon sidebar-toggle" id="sidebarToggle" title="Menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <a href="<?= $baseUrl ?>/notes" class="navbar-brand">
            <span class="brand-icon">📝</span>
            <span class="brand-text">Note App</span>
        </a>
    </div>

    <div class="navbar-center">
        <div class="search-box" id="searchBox">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="searchInput" class="search-input" placeholder="Tìm kiếm ghi chú..." autocomplete="off">
            <button class="search-clear" id="searchClear" style="display:none;" title="Xóa">✕</button>
        </div>
    </div>

    <div class="navbar-right">
        <button class="btn-icon" id="themeToggle" title="Đổi giao diện">
            <svg class="theme-icon-light" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg class="theme-icon-dark" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>

        <div class="user-menu" id="userMenu">
            <button class="user-avatar-btn" id="userAvatarBtn">
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                    <img src="<?= $baseUrl ?>/public/<?= htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Avatar" class="avatar-img">
                <?php else: ?>
                    <div class="avatar-placeholder"><?= mb_substr($_SESSION['user_name'] ?? 'U', 0, 1) ?></div>
                <?php endif; ?>
            </button>
            <div class="user-dropdown" id="userDropdown">
                <div class="dropdown-header">
                    <strong><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></strong>
                    <small><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
                </div>
                <hr>
                <a href="<?= $baseUrl ?>/profile" class="dropdown-item">
                    <span>👤</span> Hồ sơ cá nhân
                </a>
                <a href="<?= $baseUrl ?>/auth/logout" class="dropdown-item">
                    <span>🚪</span> Đăng xuất
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="app-container">
<?php endif; ?>
