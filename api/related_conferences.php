<?php
/**
 * API trả về các hội nghị liên quan theo category hoặc tags
 * Phiên bản: 3.0 (Complete Edition)
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

    // Lấy các tham số từ URL
    $conferenceId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;
    
    if ($conferenceId <= 0 && $categoryId <= 0) {
        echo json_encode([
            'status' => false,
            'message' => 'Thiếu ID hội nghị hoặc ID danh mục'
        ]);
        exit;
    }

    // Nếu chỉ có conference_id, tìm category_id của nó
    if ($conferenceId > 0 && $categoryId <= 0) {
        $conferenceData = $conference->getConferenceById($conferenceId);
        if ($conferenceData && isset($conferenceData['category_id'])) {
            $categoryId = $conferenceData['category_id'];
        }
    }

    // Lấy các hội nghị liên quan
    if ($categoryId > 0) {
        $relatedConferences = $conference->getRelatedConferences($conferenceId, $categoryId, $limit);
        
        echo json_encode([
            'status' => true,
            'data' => $relatedConferences
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Không thể xác định danh mục hội nghị'
        ]);
    }

} catch (Exception $e) {
    // Trả về lỗi
    echo json_encode([
        'status' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
