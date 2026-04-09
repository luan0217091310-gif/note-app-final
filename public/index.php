<?php
/**
 * Front Controller - Điều hướng chính của ứng dụng
 * Mọi request đều đi qua file này
 */

session_start();

// Định nghĩa đường dẫn gốc
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Autoload config
require_once BASE_PATH . '/config/database.php';

/**
 * Helper - Lấy base URL tương đối
 * Trả về path đến thư mục note_app (không bao gồm /public)
 */
function getBaseUrl() {
    // Tìm vị trí của /public/ trong SCRIPT_NAME
    $scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /note_app/public/index.php
    $basePath = dirname(dirname($scriptName)); // Lên 2 cấp: /note_app
    return rtrim($basePath, '/');
}

// Lấy URL từ query string
$url = isset($_GET['url']) ? trim($_GET['url'], '/') : '';

// Bỏ prefix 'public/' nếu có (do .htaccess)
$url = preg_replace('#^public/?#', '', $url);

$parts = explode('/', $url);

$controllerName = !empty($parts[0]) ? $parts[0] : 'notes';
$action = !empty($parts[1]) ? $parts[1] : 'index';
$param = !empty($parts[2]) ? $parts[2] : null;

// Các route không cần đăng nhập
$publicRoutes = [
    'auth' => ['login', 'register', 'doLogin', 'doRegister', 'activate', 'forgot', 'doForgot', 'reset', 'doReset']
];

// Kiểm tra đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$isPublicRoute = isset($publicRoutes[$controllerName]) && in_array($action, $publicRoutes[$controllerName]);

if (!$isLoggedIn && !$isPublicRoute) {
    header('Location: ' . getBaseUrl() . '/auth/login');
    exit;
}

// Nếu đã login mà truy cập trang auth (login/register) → redirect về notes
if ($isLoggedIn && $controllerName === 'auth' && in_array($action, ['login', 'register'])) {
    header('Location: ' . getBaseUrl() . '/notes');
    exit;
}

// Điều hướng đến Controller tương ứng
switch ($controllerName) {
    case 'auth':
        require_once BASE_PATH . '/controllers/AuthController.php';
        $controller = new AuthController();
        break;

    case 'notes':
        require_once BASE_PATH . '/controllers/NoteController.php';
        $controller = new NoteController();
        break;

    case 'labels':
        require_once BASE_PATH . '/controllers/LabelController.php';
        $controller = new LabelController();
        break;

    case 'profile':
        require_once BASE_PATH . '/controllers/ProfileController.php';
        $controller = new ProfileController();
        break;

    case 'shared':
        require_once BASE_PATH . '/controllers/NoteController.php';
        $controller = new NoteController();
        $action = 'sharedWithMe';
        break;

    default:
        header('Location: ' . getBaseUrl() . '/notes');
        exit;
}

// Gọi action
if (method_exists($controller, $action)) {
    $controller->$action($param);
} else {
    // Action không tồn tại → về trang chủ
    header('Location: ' . getBaseUrl() . '/notes');
    exit;
}
