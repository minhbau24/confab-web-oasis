<?php
// api/conference_support_info.php
// API lấy và cập nhật thông tin hỗ trợ cho từng conference

require_once '../includes/config.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

function get_support_info($conference_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM conference_support_info WHERE conference_id = ? LIMIT 1");
    $stmt->bindValue(1, $conference_id, PDO::PARAM_INT);
    $stmt->execute();
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return $row;
    }
    return null;
}

function update_support_info($conference_id, $data, $conn) {
    // Kiểm tra đã có chưa
    $stmt = $conn->prepare("SELECT id FROM conference_support_info WHERE conference_id = ?");
    $stmt->bindValue(1, $conference_id, PDO::PARAM_INT);
    $stmt->execute();
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Update
        $stmt2 = $conn->prepare("UPDATE conference_support_info SET support_email=?, support_phone=?, support_address=?, facebook_url=?, twitter_url=?, linkedin_url=?, updated_at=NOW() WHERE conference_id=?");
        $stmt2->bindValue(1, $data['support_email']);
        $stmt2->bindValue(2, $data['support_phone']);
        $stmt2->bindValue(3, $data['support_address']);
        $stmt2->bindValue(4, $data['facebook_url']);
        $stmt2->bindValue(5, $data['twitter_url']);
        $stmt2->bindValue(6, $data['linkedin_url']);
        $stmt2->bindValue(7, $conference_id, PDO::PARAM_INT);
        $stmt2->execute();
        return $stmt2->rowCount() > 0;
    } else {
        // Insert
        $stmt2 = $conn->prepare("INSERT INTO conference_support_info (conference_id, support_email, support_phone, support_address, facebook_url, twitter_url, linkedin_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bindValue(1, $conference_id, PDO::PARAM_INT);
        $stmt2->bindValue(2, $data['support_email']);
        $stmt2->bindValue(3, $data['support_phone']);
        $stmt2->bindValue(4, $data['support_address']);
        $stmt2->bindValue(5, $data['facebook_url']);
        $stmt2->bindValue(6, $data['twitter_url']);
        $stmt2->bindValue(7, $data['linkedin_url']);
        $stmt2->execute();
        return $stmt2->rowCount() > 0;
    }
}

if ($method === 'GET') {
    $conference_id = isset($_GET['conference_id']) ? intval($_GET['conference_id']) : 0;
    if ($conference_id > 0) {
        require_once '../classes/Database.php';
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $info = get_support_info($conference_id, $conn);
        echo json_encode(['success' => true, 'data' => $info]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing conference_id']);
    }
    exit;
}

if ($method === 'POST') {
    $conference_id = isset($_POST['conference_id']) ? intval($_POST['conference_id']) : 0;
    if ($conference_id > 0) {
        require_once '../classes/Database.php';
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $data = [
            'support_email' => $_POST['support_email'] ?? '',
            'support_phone' => $_POST['support_phone'] ?? '',
            'support_address' => $_POST['support_address'] ?? '',
            'facebook_url' => $_POST['facebook_url'] ?? '',
            'twitter_url' => $_POST['twitter_url'] ?? '',
            'linkedin_url' => $_POST['linkedin_url'] ?? ''
        ];
        $ok = update_support_info($conference_id, $data, $conn);
        echo json_encode(['success' => $ok]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing conference_id']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
