<?php
/**
 * Cấu hình cơ sở dữ liệu và các thiết lập cơ bản
 */

// Thông tin kết nối cơ sở dữ liệu
define('DB_HOST', 'localhost');    // Host database
define('DB_NAME', 'confab_db');    // Tên database
define('DB_USER', 'root');         // Username (mặc định: root)
define('DB_PASS', '');             // Password (mặc định: để trống)

// Các thiết lập website
define('SITE_NAME', 'Trung tâm Hội nghị');
define('SITE_URL', 'http://localhost/confab-web-oasis');
define('BASE_PATH', dirname(__DIR__));

// Múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Múi giờ Việt Nam

// Xử lý lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập session
session_start();

// Hàm autoload các class
spl_autoload_register(function ($class) {
    $classFile = BASE_PATH . '/classes/' . $class . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

/**
 * Các hàm hỗ trợ
 */

// Hàm kết nối database
function connectDB()
{
    try {
        $conn = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $conn;
    } catch (PDOException $e) {
        die('Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage());
    }
}

// Hàm sanitize input để tránh SQL injection và XSS
function sanitize($input)
{
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
    } else {
        $input = htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

// Hàm chuyển hướng
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Hàm hiển thị thông báo
function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Hàm lấy và xóa thông báo flash
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
?>