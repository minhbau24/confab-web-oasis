<?php
/**
 * API Debug cho hội nghị
 * Sử dụng API này để debug và kiểm tra dữ liệu hội nghị
 */
// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

// Tắt hiển thị lỗi để tránh trả về HTML thay vì JSON
ini_set('display_errors', 0);
error_reporting(0);

// Bắt đầu output buffering để kiểm soát output
ob_start();

// Set JSON header ngay từ đầu
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

try {
    require_once '../includes/config.php';
    require_once '../classes/Database.php';
    require_once '../classes/Conference.php';

    // Xử lý CORS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=UTF-8');
      // Khởi tạo Database
    $db = Database::getInstance();
    
    // Lấy thông tin ID từ request
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode([
            'status' => false,
            'message' => 'Cần cung cấp ID hội nghị hợp lệ'
        ]);
        exit;
    }

    // Kiểm tra dữ liệu thô từ bảng conferences
    $sql = "SELECT * FROM conferences WHERE id = ?";
    $conference = $db->fetch($sql, [$id]);

    if (!$conference) {
        echo json_encode([
            'status' => false,
            'message' => 'Không tìm thấy hội nghị nào với ID: ' . $id . ' trong database',
            'debug' => [
                'query' => $sql,
                'id_checked' => $id
            ]
        ]);
        exit;
    }    // Kiểm tra bảng categories
    $category = null;
    if ($conference['category_id']) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $category = $db->fetch($sql, [$conference['category_id']]);
    }

    // Kiểm tra bảng venues
    $venue = null;
    if ($conference['venue_id']) {
        $sql = "SELECT * FROM venues WHERE id = ?";
        $venue = $db->fetch($sql, [$conference['venue_id']]);
    }

    echo json_encode([
        'status' => true,
        'message' => 'Dữ liệu hội nghị tồn tại trong database',
        'raw_conference_data' => $conference,
        'category_exists' => !empty($category),
        'category_data' => $category,
        'venue_exists' => !empty($venue),
        'venue_data' => $venue,
        'sql_log' => 'SQL logs không khả dụng'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Lỗi debug: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString() 
    ]);
}
?>
