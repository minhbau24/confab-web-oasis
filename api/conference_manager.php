<?php
// api/conference_manager.php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Conference.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập và quyền tổ chức/admin
if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}
$user = getCurrentUser();
if (!in_array($user['role'], ['admin', 'organizer'])) {
    echo json_encode(['status' => false, 'error' => 'Không có quyền truy cập']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    if ($method === 'GET') {
        // Chỉ lấy các hội nghị do chính user hiện tại tạo ra
        $sql = "SELECT * FROM conferences WHERE created_by = :user_id ORDER BY start_date DESC";
        $params = ['user_id' => $user['id']];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $conferences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Đếm số người đăng ký (attendees) cho từng hội nghị
        foreach ($conferences as &$conf) {
            $conf_id = $conf['id'];
            $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE conference_id = :conf_id");
            $stmt2->execute(['conf_id' => $conf_id]);
            $conf['attendees'] = (int)$stmt2->fetchColumn();
        }
        unset($conf); // good practice
        // Thống kê loại đăng ký toàn bộ hội nghị của user
        $early = 0; $normal = 0; $late = 0;
        $sqlReg = "SELECT registration_date, ticket_type FROM registrations WHERE conference_id IN (SELECT id FROM conferences WHERE created_by = :user_id)";
        $stmtReg = $pdo->prepare($sqlReg);
        $stmtReg->execute(['user_id' => $user['id']]);
        $regs = $stmtReg->fetchAll(PDO::FETCH_ASSOC);
        $registrationsByDate = [];
        foreach ($regs as $reg) {
            $type = strtolower(trim($reg['ticket_type'] ?? ''));
            $date = substr($reg['registration_date'], 0, 10); // yyyy-mm-dd
            if (!isset($registrationsByDate[$date])) $registrationsByDate[$date] = 0;
            $registrationsByDate[$date]++;
            if ($type === 'early_bird') $early++;
            else if ($type === 'regular') $normal++;
            else $normal++;
        }
        $stats = [
            'early' => $early,
            'normal' => $normal,
            'late' => $late // luôn 0 nếu không có logic riêng
        ];
        // Thống kê doanh thu theo ngày
        $revenueByDate = [];
        $sqlRevenue = "SELECT DATE(registration_date) as reg_date, SUM(price_paid * quantity) as total_revenue FROM registrations WHERE conference_id IN (SELECT id FROM conferences WHERE created_by = :user_id) GROUP BY reg_date ORDER BY reg_date";
        $stmtRevenue = $pdo->prepare($sqlRevenue);
        $stmtRevenue->execute(['user_id' => $user['id']]);
        $revenueRows = $stmtRevenue->fetchAll(PDO::FETCH_ASSOC);
        foreach ($revenueRows as $row) {
            $revenueByDate[$row['reg_date']] = (float)$row['total_revenue'];
        }
        // Thống kê phản hồi (feedback)
        $feedbackStats = [
            'avg_score' => null,
            'nps' => null,
            'speaker_quality' => null,
            'venue_rating' => null
        ];
        $sqlFeedback = "SELECT AVG(overall_rating) as avg_score, AVG(speaker_rating) as speaker_quality, AVG(venue_rating) as venue_rating, SUM(CASE WHEN would_recommend=1 THEN 1 WHEN would_recommend=0 THEN -1 ELSE 0 END) as nps_sum, COUNT(*) as total, SUM(CASE WHEN would_recommend=1 THEN 1 ELSE 0 END) as recommend_yes, SUM(CASE WHEN would_recommend=0 THEN 1 ELSE 0 END) as recommend_no FROM feedback WHERE conference_id IN (SELECT id FROM conferences WHERE created_by = :user_id)";
        $stmtFeedback = $pdo->prepare($sqlFeedback);
        $stmtFeedback->execute(['user_id' => $user['id']]);
        $row = $stmtFeedback->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['total'] > 0) {
            $feedbackStats['avg_score'] = round((float)$row['avg_score'], 2);
            $feedbackStats['speaker_quality'] = $row['speaker_quality'] !== null ? round((float)$row['speaker_quality'], 2) : null;
            $feedbackStats['venue_rating'] = $row['venue_rating'] !== null ? round((float)$row['venue_rating'], 2) : null;
            // NPS: % recommend_yes - % recommend_no
            $nps = ($row['recommend_yes'] / $row['total'] * 100) - ($row['recommend_no'] / $row['total'] * 100);
            $feedbackStats['nps'] = round($nps);
        }
        // Thống kê nguồn doanh thu
        $ticketSales = 0;
        $sqlTicket = "SELECT SUM(price_paid * quantity) as total FROM registrations WHERE conference_id IN (SELECT id FROM conferences WHERE created_by = :user_id)";
        $stmtTicket = $pdo->prepare($sqlTicket);
        $stmtTicket->execute(['user_id' => $user['id']]);
        $ticketSales = (float)($stmtTicket->fetchColumn() ?: 0);
        // Tài trợ (sponsorship) lấy từ sponsor_info JSON trong bảng conferences
        $sponsorship = 0;
        foreach ($conferences as $conf) {
            if (!empty($conf['sponsor_info'])) {
                $info = json_decode($conf['sponsor_info'], true);
                if (isset($info['sponsors']) && is_array($info['sponsors'])) {
                    foreach ($info['sponsors'] as $s) {
                        if (isset($s['amount'])) $sponsorship += (float)$s['amount'];
                    }
                }
            }
        }
        // Bán hàng (merchandise): chưa có dữ liệu, để 0
        $merchandise = 0;
        // Tổng lợi nhuận = bán vé + tài trợ + bán hàng
        $totalProfit = $ticketSales + $sponsorship + $merchandise;
        $revenueSources = [
            'ticket_sales' => $ticketSales,
            'sponsorship' => $sponsorship,
            'merchandise' => $merchandise,
            'total_profit' => $totalProfit
        ];
        echo json_encode([
            'status' => true,
            'data' => $conferences,
            'stats' => $stats,
            'registrations_by_date' => $registrationsByDate,
            'revenue_by_date' => $revenueByDate,
            'feedback_stats' => $feedbackStats,
            'revenue_sources' => $revenueSources
        ]);
        exit;
    }
    // TODO: POST, PUT, DELETE cho các thao tác khác
    echo json_encode(['status' => false, 'error' => 'Method not allowed']);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'error' => $e->getMessage()]);
}
