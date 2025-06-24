<?php
require_once __DIR__ . '/../includes/config.php';
// api/resources.php
// API quản lý tài liệu (tệp tin) cho từng conference
header('Content-Type: application/json');
require_once '../classes/Database.php';

$pdo = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Lấy danh sách tài liệu của 1 conference
    $conference_id = isset($_GET['conference_id']) ? intval($_GET['conference_id']) : 0;
    if ($conference_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'conference_id is required']);
        exit;
    }
    $stmt = $pdo->prepare('SELECT id, file_name, original_name, file_path, full_url, file_size, file_type, extension, media_type, title, description, created_at FROM media_files WHERE category_id = ? ORDER BY created_at DESC');
    $stmt->execute([$conference_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

if ($method === 'POST') {
    // Thêm mới tài liệu (chỉ nhận metadata, không upload file thực tế)
    $input = json_decode(file_get_contents('php://input'), true);
    $conference_id = isset($input['conference_id']) ? intval($input['conference_id']) : 0;
    if ($conference_id <= 0 || empty($input['file_name']) || empty($input['file_path'])) {
        http_response_code(400);
        echo json_encode(['error' => 'conference_id, file_name, file_path are required']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO media_files (category_id, file_name, original_name, file_path, full_url, file_size, file_type, extension, media_type, title, description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $ok = $stmt->execute([
        $conference_id,
        $input['file_name'],
        $input['original_name'] ?? $input['file_name'],
        $input['file_path'],
        $input['full_url'] ?? null,
        $input['file_size'] ?? 0,
        $input['file_type'] ?? '',
        $input['extension'] ?? '',
        $input['media_type'] ?? 'document',
        $input['title'] ?? null,
        $input['description'] ?? null
    ]);
    if ($ok) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Insert failed']);
    }
    exit;
}

if ($method === 'DELETE') {
    // Xóa tài liệu
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'id is required']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM media_files WHERE id = ?');
    $ok = $stmt->execute([$id]);
    if ($ok) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Delete failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
