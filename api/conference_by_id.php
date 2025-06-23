<?php
/**
 * API endpoint mới cho conference_by_id
 * Phiên bản mới với xử lý lỗi và debug tốt hơn
 */
// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

// Tắt hiển thị lỗi để tránh trả về HTML thay vì JSON
ini_set('display_errors', 0);
error_reporting(0);

try {
    require_once '../includes/config.php';
    require_once '../classes/Database.php';
    require_once '../classes/Conference.php';

    // Xử lý CORS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=UTF-8');

    // Lấy ID từ URL nếu có
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode([
            'status' => false,
            'message' => 'Cần cung cấp ID hội nghị hợp lệ'
        ]);
        exit;
    }    // Khởi tạo Database
    $db = Database::getInstance();
    
    // Truy vấn trực tiếp database để kiểm tra sự tồn tại
    $checkSql = "SELECT id, title FROM conferences WHERE id = ?";
    $checkResult = $db->fetch($checkSql, [$id]);
    
    if (!$checkResult) {
        echo json_encode([
            'status' => false,
            'message' => 'Không tìm thấy hội nghị với ID: ' . $id,
            'debug_info' => 'Hội nghị không tồn tại trong database hoặc đã bị xóa (deleted_at có giá trị)',
        ]);
        exit;
    }
    
    // Khởi tạo Conference và lấy dữ liệu
    $conference = new Conference();
    $conferenceData = $conference->getConferenceById($id);
    
    // Xử lý nếu không lấy được dữ liệu từ phương thức getConferenceById
    if (!$conferenceData) {        // Thực hiện truy vấn thủ công để lấy dữ liệu cơ bản
        $manualSql = "SELECT 
                        c.*, 
                        COALESCE(cat.name, 'Không phân loại') as category_name,
                        COALESCE(v.name, 'Chưa có địa điểm') as venue_name,
                        COALESCE(v.city, '') as venue_city
                      FROM conferences c
                      LEFT JOIN categories cat ON c.category_id = cat.id
                      LEFT JOIN venues v ON c.venue_id = v.id
                      WHERE c.id = ?";
        $manualResult = $db->fetch($manualSql, [$id]);
        
        // Nếu có kết quả, trả về kết quả cơ bản
        if ($manualResult) {
            echo json_encode([
                'status' => true,
                'data' => $manualResult,
                'message' => 'Dữ liệu hội nghị được truy xuất theo phương thức thay thế'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Không thể lấy được dữ liệu hội nghị với ID: ' . $id,
                'debug_info' => 'Hội nghị tồn tại trong database nhưng có lỗi khi truy xuất thông tin chi tiết'
            ]);
        }
        exit;
    }
    
    // Trả về dữ liệu hội nghị
    echo json_encode([
        'status' => true,
        'data' => $conferenceData
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Lỗi server: ' . $e->getMessage(),
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
    ]);
}
?>
