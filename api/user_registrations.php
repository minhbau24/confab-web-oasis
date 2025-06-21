<?php
/**
 * API endpoint để lấy danh sách hội nghị mà user đã đăng ký
 */

// Tắt hiển thị lỗi để tránh trả về HTML thay vì JSON
ini_set('display_errors', 0);
error_reporting(0);

// Set headers trước tiên
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Bắt đầu session để kiểm tra đăng nhập
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => false,
            'message' => 'Bạn cần đăng nhập để xem danh sách đăng ký'
        ]);
        exit;
    }
    
    require_once '../includes/config.php';
    require_once '../classes/Database.php';
    
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    // Lấy danh sách conference IDs mà user đã đăng ký
    $registrations = $db->fetchAll("
        SELECT conference_id, status, registration_date 
        FROM registrations 
        WHERE user_id = ? AND status != 'cancelled'
        ORDER BY registration_date DESC
    ", [$userId]);
    
    // Chuyển đổi thành array of conference IDs để dễ sử dụng
    $conferenceIds = [];
    foreach ($registrations as $reg) {
        $conferenceIds[] = (int)$reg['conference_id'];
    }
    
    echo json_encode([
        'status' => true,
        'data' => [
            'user_id' => $userId,
            'conference_ids' => $conferenceIds,
            'registrations' => $registrations
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in user_registrations.php: " . $e->getMessage());
    echo json_encode([
        'status' => false,
        'message' => 'Có lỗi xảy ra khi lấy danh sách đăng ký'
    ]);
}
?>
