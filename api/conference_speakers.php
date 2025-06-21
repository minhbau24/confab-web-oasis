<?php
/**
 * API for conference speakers
 */
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

        if ($conferenceId > 0) {
            // Get conference speakers
            // Note: This is a placeholder - you'll need to implement the getSpeakers method in Conference class
            $speakersData = $conference->getConferenceSpeakers($conferenceId);
            
            if ($speakersData) {
                echo json_encode([
                    'status' => true,
                    'data' => $speakersData
                ]);
            } else {
                // Return sample speakers for now
                $sampleSpeakers = [
                    [
                        'name' => 'Nguyễn Thị Minh',
                        'title' => 'CEO, InnovateTech Vietnam',
                        'bio' => 'Chuyên gia hàng đầu về AI và học máy',
                        'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=120&h=120&fit=crop&crop=face'
                    ],
                    [
                        'name' => 'Trần Đức Khải',
                        'title' => 'CTO, VietStartup',
                        'bio' => 'Tiên phong trong công nghệ blockchain',
                        'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=120&h=120&fit=crop&crop=face'
                    ],
                    [
                        'name' => 'Phạm Thị Hương',
                        'title' => 'Founder, GreenTech Solutions',
                        'bio' => 'Chuyên gia về phát triển bền vững và năng lượng sạch',
                        'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=120&h=120&fit=crop&crop=face'
                    ],
                    [
                        'name' => 'Lê Văn Bách',
                        'title' => 'AI Research Director, FPT Software',
                        'bio' => 'Tiến sĩ AI với hơn 15 năm kinh nghiệm nghiên cứu và ứng dụng thực tế',
                        'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120&h=120&fit=crop&crop=face'
                    ]
                ];
                
                echo json_encode([
                    'status' => true,
                    'data' => $sampleSpeakers,
                    'message' => 'Returning sample data as speakers data is not available'                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Conference ID is required'
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
