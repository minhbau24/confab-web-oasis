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

// --- Xử lý PUT (update attendee) ---
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Sửa attendee: chỉ cho phép sửa ticket_type và status
    $input = json_decode(file_get_contents('php://input'), true);
    $attendeeId = isset($input['id']) ? intval($input['id']) : 0;
    $ticketType = isset($input['ticket_type']) ? $input['ticket_type'] : null;
    $status = isset($input['status']) ? $input['status'] : null;
    $conferenceId = 0;
    if ($attendeeId > 0) {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare('SELECT conference_id FROM registrations WHERE id = :id');
            $stmt->execute(['id' => $attendeeId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) $conferenceId = intval($row['conference_id']);
        } catch (Exception $e) { $conferenceId = 0; }
    }
    if ($attendeeId <= 0 || !$ticketType || !$status || $conferenceId <= 0) {
        echo json_encode(['status' => false, 'error' => 'Thiếu thông tin cần thiết hoặc conference_id không hợp lệ']);
        exit;
    }
    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        // Kiểm tra attendee thuộc conference này và quyền
        $stmt = $pdo->prepare('SELECT r.*, c.created_by FROM registrations r JOIN conferences c ON r.conference_id = c.id WHERE r.id = :id');
        $stmt->execute(['id' => $attendeeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['created_by'] != $user['id']) {
            echo json_encode(['status' => false, 'error' => 'Không có quyền sửa attendee này']);
            exit;
        }
        // Cập nhật
        $stmt2 = $pdo->prepare('UPDATE registrations SET ticket_type = :ticket_type, status = :status WHERE id = :id');
        $stmt2->execute([
            'ticket_type' => $ticketType,
            'status' => $status,
            'id' => $attendeeId
        ]);
        echo json_encode(['status' => true]);
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// --- Xử lý GET (lấy danh sách attendee) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
        $sql = 'SELECT r.id, r.user_id, CONCAT(u.firstName, " ", u.lastName) AS name, u.email, r.ticket_type, r.status, r.registration_date, r.conference_id FROM registrations r LEFT JOIN users u ON r.user_id = u.id WHERE r.conference_id = :conf_id ORDER BY r.registration_date DESC';
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute(['conf_id' => $conferenceId]);
        $attendees = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => true, 'data' => $attendees]);
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
