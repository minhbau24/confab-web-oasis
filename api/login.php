<?php
/**
 * API endpoint cho việc đăng nhập người dùng
 */
// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

// Force PHP to use relative paths only
$_SERVER['SCRIPT_NAME'] = '/api/login.php';
$_SERVER['PHP_SELF'] = '/api/login.php';

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Thiết lập header trước tiên
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Chỉ chấp nhận method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ'
    ]);
    exit;
}

try {
    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Nếu không có dữ liệu JSON, thử lấy từ POST
    if (empty($data)) {
        $data = $_POST;
    }
    
    // Kiểm tra dữ liệu bắt buộc
    if (empty($data['email']) || empty($data['password'])) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu thông tin bắt buộc: email, password'
        ]);
        exit;
    }
    
    // Lấy dữ liệu
    $email = sanitize($data['email']);
    $password = $data['password'];
    $rememberMe = isset($data['rememberMe']) ? (bool)$data['rememberMe'] : false;
    
    // Đăng nhập
    $result = login($email, $password, $rememberMe);
    
    if ($result['success']) {
        http_response_code(200); // OK        // Add debugging information
        $debugInfo = [
            'session_id' => session_id(),
            'session_vars' => $_SESSION, // This will include path info if any
            'server_vars' => [
                'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
                'PHP_SELF' => $_SERVER['PHP_SELF'],
                'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'],
                'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'],
                'REQUEST_URI' => $_SERVER['REQUEST_URI']
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'user' => $result['user'],
            'message' => 'Đăng nhập thành công',
            'debug' => $debugInfo
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Email hoặc mật khẩu không đúng'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
