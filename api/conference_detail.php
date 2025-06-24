<?php
/**
 * API đúng định dạng cho trang chi tiết hội nghị
 * Phiên bản: 3.0 (Complete Edition) - Tương thích với schema mới
 */
// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

// Tắt hiển thị lỗi để tránh trả về HTML thay vị JSON
ini_set('display_errors', 0);
error_reporting(0);

try {
    require_once '../includes/config.php';
    require_once '../classes/Database.php';
    require_once '../classes/Conference.php';
    
    // Xử lý CORS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

    // Khởi tạo Conference
    $conference = new Conference();

    // Xác định request method
    $method = $_SERVER['REQUEST_METHOD'];

    // Chỉ cho phép phương thức GET
    if ($method !== 'GET') {
        echo json_encode([
            'status' => false,
            'message' => 'Phương thức không được hỗ trợ'
        ]);
        exit;
    }

    // Lấy ID từ URL
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo json_encode([
            'status' => false,
            'message' => 'ID hội nghị không hợp lệ'
        ]);
        exit;
    }

    // Lấy toàn bộ thông tin hội nghị
    $conferenceData = $conference->getConferenceById($id);

    if (!$conferenceData) {
        echo json_encode([
            'status' => false,
            'message' => 'Không tìm thấy hội nghị với ID: ' . $id
        ]);
        exit;
    }

    // Lấy thông tin diễn giả
    $speakers = $conference->getConferenceSpeakers($id);
    
    // Lấy lịch trình
    $schedule = $conference->getConferenceSchedule($id);
    
    // Lấy mục tiêu (nếu có phương thức này)
    $objectives = method_exists($conference, 'getConferenceObjectives') ? $conference->getConferenceObjectives($id) : [];
    
    // Lấy đối tượng tham dự (nếu có phương thức này)
    $audience = method_exists($conference, 'getConferenceAudience') ? $conference->getConferenceAudience($id) : [];

    // Lấy FAQ (nếu có phương thức này)
    $faq = method_exists($conference, 'getConferenceFaq') ? $conference->getConferenceFaq($id) : [];

    // Trả về dữ liệu đầy đủ
    echo json_encode([
        'status' => true,
        'data' => [
            'conference' => $conferenceData,
            'speakers' => $speakers ?: [],
            'schedule' => $schedule ?: [],
            'objectives' => $objectives ?: [],
            'audience' => $audience ?: [],
            'faq' => $faq ?: []
        ]
    ]);

} catch (Exception $e) {
    // Trả về lỗi
    echo json_encode([
        'status' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
