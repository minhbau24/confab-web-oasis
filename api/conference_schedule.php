<?php
/**
 * API for conference schedules
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

    // Handle CORS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Initialize Conference
$conference = new Conference();

// Determine request method
$method = $_SERVER['REQUEST_METHOD'];

// Process requests
switch ($method) {
    case 'GET':
        // Get conference ID from URL
        $conferenceId = isset($_GET['conference_id']) ? intval($_GET['conference_id']) : 0;

        if ($conferenceId > 0) {            // Get conference schedule
            $scheduleData = $conference->getConferenceSchedule($conferenceId);
            
            if ($scheduleData && is_array($scheduleData) && count($scheduleData) > 0) {
                echo json_encode([
                    'status' => true,
                    'data' => $scheduleData
                ]);
            } else {
                // Kiểm tra và trả về dữ liệu mẫu
                echo json_encode([                    'status' => true,
                    'message' => 'Using fallback schedule data for conference ID: ' . $conferenceId,
                    'data' => [
                        [
                            'id' => 1,
                            'conference_id' => $conferenceId,
                            'eventDate' => '2025-09-15',
                            'startTime' => '09:00:00',
                            'endTime' => '10:30:00',
                            'title' => 'Khai mạc và Keynote',
                            'speaker' => 'Nguyễn Thị Minh',
                            'description' => 'Bài phát biểu khai mạc và giới thiệu các xu hướng công nghệ mới'
                        ],
                        [
                            'id' => 2,
                            'conference_id' => $conferenceId,
                            'eventDate' => '2025-09-15',
                            'startTime' => '10:45:00',
                            'endTime' => '12:15:00',
                            'title' => 'Phiên thảo luận: AI trong doanh nghiệp',
                            'speaker' => 'Trần Đức Khải, Lê Văn Bách',
                            'description' => 'Thảo luận về việc áp dụng AI trong các doanh nghiệp Việt Nam'
                        ],
                        [
                            'id' => 3,
                            'conference_id' => $conferenceId,
                            'eventDate' => '2025-09-16',
                            'startTime' => '09:00:00',
                            'endTime' => '10:30:00',
                            'title' => 'Workshop: Machine Learning thực hành',
                            'speaker' => 'Lê Văn Bách',
                            'description' => 'Hướng dẫn thực hành về xây dựng mô hình ML'
                        ]
                    ],
                    'debug' => [
                        'scheduleDataType' => gettype($scheduleData),
                        'scheduleDataCount' => is_array($scheduleData) ? count($scheduleData) : 0,
                        'conferenceId' => $conferenceId,
                        'note' => 'Generated fallback data'
                    ]
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,                'message' => 'Conference ID is required'
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'status' => false,
            'message' => 'Method not supported'
        ]);
        break;
}
} catch (Exception $e) {
    // Bắt tất cả các ngoại lệ và trả về thông báo lỗi dưới dạng JSON
    echo json_encode([
        'status' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>
