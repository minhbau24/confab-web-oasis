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
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

    // Xác định request method
    $method = $_SERVER['REQUEST_METHOD'];

    // Chỉ cho phép phương thức GET và POST
    if ($method !== 'GET' && $method !== 'POST') {
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

    // Ưu tiên xử lý POST trước
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = isset($input['action']) ? $input['action'] : '';
        // Thêm mục tiêu
        if ($action === 'add_objective' && !empty($input['text'])) {
            $db = Database::getInstance();
            $sqlOrder = "SELECT MAX(display_order) AS max_order FROM conference_objectives WHERE conference_id = ?";
            $row = $db->fetch($sqlOrder, [$id]);
            $display_order = isset($row['max_order']) ? ((int)$row['max_order'] + 1) : 1;
            $sqlInsert = "INSERT INTO conference_objectives (conference_id, text, display_order) VALUES (?, ?, ?)";
            $result = $db->execute($sqlInsert, [$id, trim($input['text']), $display_order]);
            if ($result) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Thêm mục tiêu thành công!'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Không thể thêm mục tiêu. Vui lòng thử lại!'
                ]);
            }
            exit;
        }
        // Sửa mục tiêu
        if ($action === 'edit_objective' && !empty($input['objective_id']) && isset($input['text'])) {
            $db = Database::getInstance();
            $sqlUpdate = "UPDATE conference_objectives SET text = ?, updated_at = NOW() WHERE id = ? AND conference_id = ?";
            $result = $db->execute($sqlUpdate, [trim($input['text']), intval($input['objective_id']), $id]);
            if ($result) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Cập nhật mục tiêu thành công!'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Không thể cập nhật mục tiêu. Vui lòng thử lại!'
                ]);
            }
            exit;
        }
        // Xóa mục tiêu
        if ($action === 'delete_objective' && !empty($input['objective_id'])) {
            $db = Database::getInstance();
            $sqlDelete = "DELETE FROM conference_objectives WHERE id = ? AND conference_id = ?";
            $result = $db->execute($sqlDelete, [intval($input['objective_id']), $id]);
            if ($result) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Đã xóa mục tiêu!'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Không thể xóa mục tiêu. Vui lòng thử lại!'
                ]);
            }
            exit;
        }
        // ...existing code...
    }

    // Nếu là GET thì trả về dữ liệu hội nghị
    $conference = new Conference();
    $conferenceData = $conference->getConferenceById($id);
    if (!$conferenceData) {
        echo json_encode([
            'status' => false,
            'message' => 'Không tìm thấy hội nghị với ID: ' . $id
        ]);
        exit;
    }
    $speakers = $conference->getConferenceSpeakers($id);
    $schedule = $conference->getConferenceSchedule($id);
    $objectives = method_exists($conference, 'getConferenceObjectives') ? $conference->getConferenceObjectives($id) : [];
    $audience = method_exists($conference, 'getConferenceAudience') ? $conference->getConferenceAudience($id) : [];
    $faq = method_exists($conference, 'getConferenceFaq') ? $conference->getConferenceFaq($id) : [];
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
    echo json_encode([
        'status' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}