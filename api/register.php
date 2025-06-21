<?php
/**
 * API endpoint cho việc đăng ký người dùng mới
 */
// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Thiết lập header
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
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
    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || empty($data['password'])) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu thông tin bắt buộc: firstName, lastName, email, password'
        ]);
        exit;
    }
    
    // Lấy dữ liệu
    $firstName = sanitize($data['firstName']);
    $lastName = sanitize($data['lastName']);
    $email = sanitize($data['email']);
    $password = $data['password']; // Password sẽ được hash trong hàm register
    $phone = isset($data['phone']) ? sanitize($data['phone']) : '';
    $userType = isset($data['userType']) ? sanitize($data['userType']) : 'user';
    
    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'message' => 'Địa chỉ email không hợp lệ'
        ]);
        exit;
    }
    
    // Đăng ký người dùng
    $result = register($firstName, $lastName, $email, $password, $phone, $userType);
    
    if ($result['success']) {
        http_response_code(201); // Created
        echo json_encode([
            'success' => true,
            'message' => 'Đăng ký thành công. Vui lòng đăng nhập.',
            'user' => [
                'id' => $result['user_id'],
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
            ]
        ]);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Đăng ký thất bại. Vui lòng thử lại sau.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
