<?php
/**
 * API đổi mật khẩu người dùng
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

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    // Get data from request body (JSON)
    $json_data = json_decode(file_get_contents('php://input'), true);
    $post_data = !empty($json_data) ? $json_data : $_POST;
    
    // Validate required fields
    if (empty($post_data['currentPassword']) || empty($post_data['newPassword']) || empty($post_data['confirmPassword'])) {
        echo json_encode([
            'status' => false,
            'message' => 'All password fields are required',
            'code' => 400
        ]);
        exit;
    }
    
    $currentPassword = $post_data['currentPassword'];
    $newPassword = $post_data['newPassword'];
    $confirmPassword = $post_data['confirmPassword'];

    // Kiểm tra xác nhận mật khẩu
    if ($newPassword !== $confirmPassword) {
        echo json_encode([
            'status' => false,
            'message' => 'Password confirmation does not match',
            'code' => 400
        ]);
        exit;
    }
    
    // Password strength validation
    if (strlen($newPassword) < 8) {
        echo json_encode([
            'status' => false,
            'message' => 'Password must be at least 8 characters long',
            'code' => 400
        ]);
        exit;
    }

    // Kiểm tra mật khẩu hiện tại
    try {
        $db = new Database();

        // Lấy thông tin người dùng
        $user = $db->fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]);

        // Kiểm tra mật khẩu
        if ($user && password_verify($currentPassword, $user['password'])) {
            // Mật khẩu hiện tại đúng, thực hiện đổi mật khẩu
            $userModel = new User();
            $result = $userModel->changePassword($userId, $newPassword);

            if ($result) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Password changed successfully',
                    'code' => 200
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to change password',
                    'code' => 500
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Current password is incorrect',
                'code' => 400
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Database error: ' . $e->getMessage(),
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