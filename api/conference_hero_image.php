<?php
// api/conference_hero_image.php
// API lấy/cập nhật ảnh nền hero cho từng conference
header('Content-Type: application/json');
require_once '../classes/Database.php';

$pdo = Database::getInstance()->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Lấy thông tin ảnh nền hero của 1 conference
    $conference_id = isset($_GET['conference_id']) ? intval($_GET['conference_id']) : 0;
    if ($conference_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'conference_id is required']);
        exit;
    }
    $stmt = $pdo->prepare('SELECT id, name, image, banner_image, gallery, hero_title, hero_description, hero_overlay, hero_overlay_opacity, hero_text_position FROM conferences WHERE id = ?');
    $stmt->execute([$conference_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Conference not found']);
    }
    exit;
}

if ($method === 'POST') {
    // Cập nhật thông tin ảnh nền hero cho 1 conference
    $input = json_decode(file_get_contents('php://input'), true);
    $conference_id = isset($input['conference_id']) ? intval($input['conference_id']) : 0;
    if ($conference_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'conference_id is required']);
        exit;
    }
    $fields = [
        'image', 'banner_image', 'gallery',
        'hero_title', 'hero_description', 'hero_overlay', 'hero_overlay_opacity', 'hero_text_position'
    ];
    $set = [];
    $params = [];
    foreach ($fields as $f) {
        if (isset($input[$f])) {
            $set[] = "$f = ?";
            $params[] = $input[$f];
        }
    }
    if (empty($set)) {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
        exit;
    }
    $params[] = $conference_id;
    $sql = 'UPDATE conferences SET ' . implode(', ', $set) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($params);
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Update failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
