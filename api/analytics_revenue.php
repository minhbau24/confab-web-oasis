<?php
require_once __DIR__ . '/../classes/Database.php';
header('Content-Type: application/json');

$conference_id = isset($_GET['conference_id']) ? intval($_GET['conference_id']) : 0;
if ($conference_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Missing or invalid conference_id']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    // Tổng doanh thu
    $stmt = $db->prepare("SELECT SUM(payment_amount) as total_revenue FROM attendees WHERE conference_id = :cid AND payment_status = 'paid'");
    $stmt->execute(['cid' => $conference_id]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC);

    // Doanh thu theo loại vé
    $stmt = $db->prepare("SELECT ticket_type, SUM(payment_amount) as revenue FROM attendees WHERE conference_id = :cid AND payment_status = 'paid' GROUP BY ticket_type");
    $stmt->execute(['cid' => $conference_id]);
    $by_type = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Doanh thu theo trạng thái thanh toán
    $stmt = $db->prepare("SELECT payment_status, SUM(payment_amount) as revenue FROM attendees WHERE conference_id = :cid GROUP BY payment_status");
    $stmt->execute(['cid' => $conference_id]);
    $by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'total' => $total['total_revenue'] ?? 0,
        'by_type' => $by_type,
        'by_status' => $by_status
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
