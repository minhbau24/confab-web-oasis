<?php
// api/attendees.php
// Lấy danh sách attendee cho một hội nghị (chỉ cho admin/organizer của hội nghị đó)
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../classes/Database.php';

header('Content-Type: application/json');
if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}
$user = getCurrentUser();
if (!in_array($user['role'], ['admin', 'organizer'])) {
    echo json_encode(['status' => false, 'error' => 'Không có quyền truy cập']);
    exit;
}
$conferenceId = isset($_GET['conference_id']) ? intval($_GET['conference_id']) : 0;
if ($conferenceId <= 0) {
    echo json_encode(['status' => false, 'error' => 'conference_id không hợp lệ']);
    exit;
}
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    // Kiểm tra quyền sở hữu hội nghị
    $stmt = $pdo->prepare('SELECT * FROM conferences WHERE id = :id AND created_by = :user_id');
    $stmt->execute(['id' => $conferenceId, 'user_id' => $user['id']]);
    $conf = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$conf) {
        echo json_encode(['status' => false, 'error' => 'Không tìm thấy hội nghị hoặc không có quyền']);
        exit;
    }
    // Lấy danh sách attendee
    $sql = 'SELECT r.id, r.user_id, u.name, u.email, r.ticket_type, r.status, r.registration_date, r.conference_id FROM registrations r LEFT JOIN users u ON r.user_id = u.id WHERE r.conference_id = :conf_id ORDER BY r.registration_date DESC';
    $stmt2 = $pdo->prepare($sql);
    $stmt2->execute(['conf_id' => $conferenceId]);
    $attendees = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => true, 'data' => $attendees]);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'error' => $e->getMessage()]);
}
