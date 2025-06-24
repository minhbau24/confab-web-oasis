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
    // Thống kê giới tính
    $stmt = $db->prepare("SELECT gender, COUNT(*) as count FROM attendees WHERE conference_id = :cid GROUP BY gender");
    $stmt->execute(['cid' => $conference_id]);
    $gender = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Thống kê độ tuổi
    $stmt = $db->prepare("SELECT 
        CASE 
            WHEN age < 18 THEN '<18'
            WHEN age BETWEEN 18 AND 24 THEN '18-24'
            WHEN age BETWEEN 25 AND 34 THEN '25-34'
            WHEN age BETWEEN 35 AND 44 THEN '35-44'
            WHEN age BETWEEN 45 AND 54 THEN '45-54'
            ELSE '55+' END as age_group, COUNT(*) as count
        FROM attendees WHERE conference_id = :cid GROUP BY age_group");
    $stmt->execute(['cid' => $conference_id]);
    $age = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Thống kê ngành nghề (occupation)
    $stmt = $db->prepare("SELECT occupation, COUNT(*) as count FROM attendees WHERE conference_id = :cid GROUP BY occupation");
    $stmt->execute(['cid' => $conference_id]);
    $occupation = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'gender' => $gender,
        'age' => $age,
        'occupation' => $occupation
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
