<?php
/**
 * API endpoint cho đăng ký hội nghị
 */

// Tắt hiển thị lỗi để tránh trả về HTML thay vì JSON
ini_set('display_errors', 0);
error_reporting(0);

// Set headers trước khi output bất cứ thứ gì
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Bắt đầu session để kiểm tra đăng nhập - chỉ start nếu chưa có
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => false,
            'message' => 'Bạn cần đăng nhập để đăng ký tham dự hội nghị'
        ]);
        exit;
    }
    
    require_once '../includes/config.php';
    require_once '../classes/Database.php';
    require_once '../classes/User.php';
    require_once '../classes/Conference.php';
    
    // Kiểm tra phương thức request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'status' => false,
            'message' => 'Phương thức không được hỗ trợ'
        ]);
        exit;
    }
      // Lấy dữ liệu từ form
    $conferenceId = isset($_POST['conferenceId']) ? intval($_POST['conferenceId']) : 0;
    $paymentMethod = isset($_POST['paymentMethod']) ? trim($_POST['paymentMethod']) : 'online';
    $promoCode = isset($_POST['promoCode']) ? trim($_POST['promoCode']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $quickRegister = isset($_POST['quickRegister']) ? filter_var($_POST['quickRegister'], FILTER_VALIDATE_BOOLEAN) : false;
    
    // Log để debug
    error_log("Registration attempt - conferenceId: $conferenceId, userId: {$_SESSION['user_id']}, quickRegister: " . ($quickRegister ? 'true' : 'false'));
    
    // Kiểm tra dữ liệu đầu vào
    if ($conferenceId <= 0) {
        echo json_encode([
            'status' => false,
            'message' => 'ID hội nghị không hợp lệ'
        ]);
        exit;
    }
    
    if (empty($paymentMethod)) {
        echo json_encode([
            'status' => false,
            'message' => 'Vui lòng chọn phương thức thanh toán'
        ]);
        exit;
    }
    
    // Lấy thông tin người dùng và hội nghị
    $userId = $_SESSION['user_id'];
    $db = Database::getInstance();
    $conference = new Conference();
    
    // Kiểm tra hội nghị tồn tại
    $conferenceData = $conference->getConferenceById($conferenceId);
    if (!$conferenceData) {
        echo json_encode([
            'status' => false,
            'message' => 'Không tìm thấy hội nghị với ID: ' . $conferenceId
        ]);
        exit;
    }
    
    // Kiểm tra xem đã đăng ký chưa
    $existingRegistration = $db->fetch(
        "SELECT id FROM registrations WHERE user_id = ? AND conference_id = ?",
        [$userId, $conferenceId]
    );
    
    if ($existingRegistration) {
        echo json_encode([
            'status' => false,
            'message' => 'Bạn đã đăng ký tham gia hội nghị này rồi'
        ]);
        exit;
    }
    
    // Kiểm tra số lượng chỗ còn trống
    if ($conferenceData['capacity'] <= $conferenceData['attendees']) {
        echo json_encode([
            'status' => false,
            'message' => 'Rất tiếc, hội nghị đã đủ số lượng người tham dự'
        ]);
        exit;
    }
    
    // Tính toán giá tiền (có thể xử lý mã khuyến mãi ở đây)
    $price = $conferenceData['price'];
    $discountAmount = 0;
    
    if (!empty($promoCode)) {
        // Kiểm tra mã khuyến mãi (ví dụ)
        if ($promoCode === 'EARLYBIRD2025') {
            $discountAmount = $price * 0.1; // Giảm 10%
        }
    }
    
    $finalPrice = $price - $discountAmount;
    
    // Thực hiện đăng ký
    try {
        // Bắt đầu giao dịch
        $db->beginTransaction();
        
        // Cập nhật số điện thoại nếu có
        if (!empty($phone)) {
            $db->execute(
                "UPDATE users SET phone = ? WHERE id = ?",
                [$phone, $userId]
            );
        }
        
        // Thêm vào bảng đăng ký
        $db->execute(
            "INSERT INTO registrations (user_id, conference_id, status, registration_date, notes) 
             VALUES (?, ?, 'pending', NOW(), ?)",
            [$userId, $conferenceId, 'Phương thức thanh toán: ' . $paymentMethod]
        );
        
        // Cập nhật số lượng người tham dự
        $db->execute(
            "UPDATE conferences SET attendees = attendees + 1 WHERE id = ?",
            [$conferenceId]
        );
        
        // Commit giao dịch
        $db->commit();
        
        // Trả về kết quả thành công
        echo json_encode([
            'status' => true,
            'message' => 'Đăng ký tham dự hội nghị thành công!',
            'data' => [
                'conferenceId' => $conferenceId,
                'userId' => $userId,
                'price' => $finalPrice,
                'discount' => $discountAmount,
                'paymentMethod' => $paymentMethod
            ]
        ]);
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $db->rollback();
        
        throw $e;
    }
} catch (Exception $e) {
    // Bắt tất cả các ngoại lệ và trả về thông báo lỗi
    echo json_encode([
        'status' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
