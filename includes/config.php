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

// Thiết lập session - chỉ start nếu chưa có session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Hàm chuyển hướng với xử lý URL
function redirect($url)
{
    // Mặc định về trang chủ nếu không có URL
    if (empty($url)) {
        $url = 'index.html';
    }
    
    // Xử lý URL để đảm bảo đường dẫn tương đối và định dạng HTML
    
    // 1. Loại bỏ đường dẫn tuyệt đối Windows (C:\...)
    if (preg_match('/^[A-Za-z]:\\\\/', $url) || strpos($url, ':\\') !== false) {
        // Lấy chỉ tên tệp tin
        $parts = explode('\\', $url);
        $url = end($parts);
    }
    
    // 2. Loại bỏ đường dẫn tuyệt đối web (http://, https://)
    if (strpos($url, '://') !== false) {
        $parsedUrl = parse_url($url);
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $parts = explode('/', $path);
        $url = end($parts);
    }
    
    // 3. Loại bỏ đường dẫn tuyệt đối từ gốc (bắt đầu bằng /)
    if (strpos($url, '/') === 0) {
        $parts = explode('/', $url);
        // Lọc các phần tử rỗng
        $parts = array_filter($parts);
        $url = end($parts);
    }
    
    // 4. Chuyển đổi .php thành .html
    if (substr($url, -4) === '.php') {
        $url = substr($url, 0, -4) . '.html';
    }
    
    // 5. Thêm .html nếu không có phần mở rộng
    if (strpos($url, '.') === false) {
        $url .= '.html';
    }
    
    // 6. Kiểm tra cuối cùng để đảm bảo là URL hợp lệ
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.html$/', $url)) {
        $url = 'index.html';
    }
    
    // Logs for debugging
    error_log("Redirect to: " . $url);
    
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