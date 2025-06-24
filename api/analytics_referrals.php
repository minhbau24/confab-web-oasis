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
    // Thống kê nguồn giới thiệu
    $stmt = $db->prepare("SELECT referral_source, COUNT(*) as count FROM attendees WHERE conference_id = :cid GROUP BY referral_source ORDER BY count DESC LIMIT 10");
    $stmt->execute(['cid' => $conference_id]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'referrals' => $referrals
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
