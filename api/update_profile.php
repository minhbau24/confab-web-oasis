<?php
/**
 * API cập nhật hồ sơ người dùng
 */
// CORS and API headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/User.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode([
        'status' => false,
        'message' => 'Authentication required',
        'code' => 401
    ]);
    exit;
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];

    // Get data from request body (JSON)
    $json_data = json_decode(file_get_contents('php://input'), true);
    $post_data = !empty($json_data) ? $json_data : $_POST;

    // Validate required fields
    $required_fields = ['firstName', 'lastName'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($post_data[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode([
            'status' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missing_fields),
            'code' => 400
        ]);
        exit;
    }

    // Prepare data
    $data = [
        'firstName' => sanitize($post_data['firstName']),
        'lastName' => sanitize($post_data['lastName']),
        'phone' => isset($post_data['phone']) ? sanitize($post_data['phone']) : null,
        'company' => isset($post_data['company']) ? sanitize($post_data['company']) : null,
        'position' => isset($post_data['position']) ? sanitize($post_data['position']) : null,
        'bio' => isset($post_data['bio']) ? sanitize($post_data['bio']) : null,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Remove null values
    $data = array_filter($data, function($value) {
        return $value !== null;
    });

    // Cập nhật thông tin
    $userModel = new User();
    $result = $userModel->updateUser($userId, $data);

    if ($result) {
        // Cập nhật tên trong session
        $_SESSION['user_name'] = $data['firstName'] . ' ' . $data['lastName'];

        // Get updated user data
        $userData = $userModel->getUserById($userId);
        
        echo json_encode([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $userData,
            'code' => 200
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to update profile',
            'code' => 500
        ]);
    }
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Method not allowed',
        'code' => 405
    ]);
}
?>