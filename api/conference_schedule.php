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
            // Sử dụng truy vấn đúng với bảng schedule_sessions
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare('
                SELECT 
                    ss.*, 
                    s.name as speaker_name, 
                    s.title as speaker_title, 
                    s.company as speaker_company, 
                    s.image as speaker_image
                FROM schedule_sessions ss
                LEFT JOIN speakers s ON ss.speaker_id = s.id
                WHERE ss.conference_id = ? 
                ORDER BY ss.session_date ASC, ss.start_time ASC
            ');
            $stmt->execute([$conferenceId]);
            $scheduleData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($scheduleData && is_array($scheduleData) && count($scheduleData) > 0) {
                echo json_encode([
                    'status' => true,
                    'data' => $scheduleData
                ]);
            } else {            // Kiểm tra và trả về dữ liệu mẫu
                echo json_encode([
                    'status' => true,
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
    case 'POST':
        // Xử lý PUT/DELETE giả lập qua _method
        $methodOverride = $_GET['_method'] ?? $_POST['_method'] ?? null;
        if ($methodOverride === 'PUT') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            require_once '../includes/auth.php';
            if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'organizer'])) {
                echo json_encode(['status' => false, 'message' => 'Không có quyền']);
                exit;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;
            // Log debug input và speaker_id
            error_log('[DEBUG][API][PUT] input: ' . json_encode($input));
            $id = isset($input['id']) ? intval($input['id']) : 0;
            $title = trim($input['title'] ?? '');
            $start_time = $input['start_time'] ?? '';
            $end_time = $input['end_time'] ?? '';
            $session_date = $input['session_date'] ?? date('Y-m-d');
            $description = $input['description'] ?? '';
            $type = $input['type'] ?? 'presentation';
            $room = $input['room'] ?? null;
            $speaker_id = isset($input['speaker_id']) && $input['speaker_id'] !== '' ? intval($input['speaker_id']) : null;
            error_log('[DEBUG][API][PUT] speaker_id: ' . var_export($speaker_id, true));
            if ($id <= 0 || !$title || !$start_time || !$end_time || !$session_date) {
                echo json_encode(['status' => false, 'message' => 'Thiếu thông tin bắt buộc']);
                exit;
            }
            try {
                $db = Database::getInstance();
                $pdo = $db->getConnection();
                $stmt = $pdo->prepare('UPDATE schedule_sessions SET title = :title, start_time = :start_time, end_time = :end_time, session_date = :session_date, description = :description, type = :type, room = :room, speaker_id = :speaker_id WHERE id = :id');
                $stmt->execute([
                    'title' => $title,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'session_date' => $session_date,
                    'description' => $description,
                    'type' => $type,
                    'room' => $room,
                    'speaker_id' => $speaker_id,
                    'id' => $id
                ]);
                echo json_encode(['status' => true]);
            } catch (Exception $e) {
                echo json_encode(['status' => false, 'message' => 'Lỗi khi cập nhật phiên: ' . $e->getMessage()]);
            }
            exit;
        } elseif ($methodOverride === 'DELETE') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            require_once '../includes/auth.php';
            if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'organizer'])) {
                echo json_encode(['status' => false, 'message' => 'Không có quyền']);
                exit;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;
            $id = isset($input['id']) ? intval($input['id']) : 0;
            if ($id <= 0) {
                echo json_encode(['status' => false, 'message' => 'Thiếu id phiên']);
                exit;
            }
            try {
                $db = Database::getInstance();
                $pdo = $db->getConnection();
                $stmt = $pdo->prepare('DELETE FROM schedule_sessions WHERE id = :id');
                $stmt->execute(['id' => $id]);
                echo json_encode(['status' => true]);
            } catch (Exception $e) {
                echo json_encode(['status' => false, 'message' => 'Lỗi khi xóa phiên: ' . $e->getMessage()]);
            }
            exit;
        } else {
            // Thêm phiên mới
            if (session_status() === PHP_SESSION_NONE) session_start();
            require_once '../includes/auth.php';
            if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'organizer'])) {
                echo json_encode(['status' => false, 'message' => 'Không có quyền']);
                exit;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            // Log debug input và speaker_id
            error_log('[DEBUG][API][POST] input: ' . json_encode($input));
            $conferenceId = isset($input['conference_id']) ? intval($input['conference_id']) : 0;
            $title = trim($input['title'] ?? '');
            $start_time = $input['start_time'] ?? '';
            $end_time = $input['end_time'] ?? '';
            $session_date = $input['session_date'] ?? date('Y-m-d');
            $description = $input['description'] ?? '';
            $type = $input['type'] ?? 'presentation';
            $room = $input['room'] ?? null;
            $speaker_id = isset($input['speaker_id']) && $input['speaker_id'] !== '' ? intval($input['speaker_id']) : null;
            error_log('[DEBUG][API][POST] speaker_id: ' . var_export($speaker_id, true));
            if ($conferenceId <= 0 || !$title || !$start_time || !$end_time || !$session_date) {
                echo json_encode(['status' => false, 'message' => 'Thiếu thông tin bắt buộc']);
                exit;
            }
            try {
                $db = Database::getInstance();
                $pdo = $db->getConnection();
                $stmt = $pdo->prepare('INSERT INTO schedule_sessions (conference_id, title, start_time, end_time, session_date, description, type, room, speaker_id) VALUES (:conference_id, :title, :start_time, :end_time, :session_date, :description, :type, :room, :speaker_id)');
                $stmt->execute([
                    'conference_id' => $conferenceId,
                    'title' => $title,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'session_date' => $session_date,
                    'description' => $description,
                    'type' => $type,
                    'room' => $room,
                    'speaker_id' => $speaker_id
                ]);
                echo json_encode(['status' => true, 'id' => $pdo->lastInsertId()]);
            } catch (Exception $e) {
                echo json_encode(['status' => false, 'message' => 'Lỗi khi thêm phiên: ' . $e->getMessage()]);
            }
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
