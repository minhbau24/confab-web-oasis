<?php
/**
 * API test kết nối database và kiểm tra dữ liệu
 */
// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

// Tắt hiển thị lỗi để tránh trả về HTML thay vì JSON
ini_set('display_errors', 0);
error_reporting(0);

// Set JSON header ngay từ đầu
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

try {
    require_once '../includes/config.php';
    require_once '../classes/Database.php';
    
    $db = Database::getInstance();
    
    // Test kết nối
    $connectionTest = $db->fetch("SELECT 1 as test");
    
    // Đếm số bản ghi trong các bảng chính
    $counts = [];
    $counts['users'] = $db->fetch("SELECT COUNT(*) as count FROM users")['count'];
    $counts['categories'] = $db->fetch("SELECT COUNT(*) as count FROM categories")['count'];
    $counts['venues'] = $db->fetch("SELECT COUNT(*) as count FROM venues")['count'];
    $counts['speakers'] = $db->fetch("SELECT COUNT(*) as count FROM speakers")['count'];
    $counts['conferences'] = $db->fetch("SELECT COUNT(*) as count FROM conferences")['count'];
    $counts['conference_speakers'] = $db->fetch("SELECT COUNT(*) as count FROM conference_speakers")['count'];
    $counts['schedule_sessions'] = $db->fetch("SELECT COUNT(*) as count FROM schedule_sessions")['count'];
    
    // Lấy một vài conference mẫu
    $sampleConferences = $db->fetchAll("SELECT id, title, status FROM conferences ORDER BY id LIMIT 5");
    
    echo json_encode([
        'status' => true,
        'message' => 'Database kết nối thành công',
        'connection' => $connectionTest ? 'OK' : 'FAILED',
        'table_counts' => $counts,
        'sample_conferences' => $sampleConferences
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Lỗi kết nối database: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}
?>
