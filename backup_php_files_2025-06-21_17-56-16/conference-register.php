<?php
/**
 * Xử lý đăng ký tham dự hội nghị
 */
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Conference.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Khởi tạo đối tượng cần thiết
$db = Database::getInstance();
$user = new User();
$conference = new Conference();

// Kiểm tra xem có tham số ID hội nghị không
$conferenceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($conferenceId <= 0) {
    // Không có ID hội nghị hợp lệ
    header('Location: conferences.php?error=invalid_conference');
    exit;
}

// Lấy thông tin hội nghị
$conferenceData = $conference->getConferenceById($conferenceId);

if (!$conferenceData) {
    // Không tìm thấy hội nghị
    header('Location: conferences.php?error=conference_not_found');
    exit;
}

$userId = $_SESSION['user_id'];
$userInfo = $user->getUserById($userId);

// Xử lý form submit
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Kiểm tra xem đã đăng ký chưa
        $existingRegistration = $db->fetch(
            "SELECT id FROM registrations WHERE user_id = ? AND conference_id = ?",
            [$userId, $conferenceId]
        );

        if ($existingRegistration) {
            $error = "Bạn đã đăng ký tham gia hội nghị này rồi.";
        } else {
            // Kiểm tra số lượng chỗ còn trống
            if ($conferenceData['capacity'] <= $conferenceData['attendees']) {
                $error = "Rất tiếc, hội nghị đã đủ số lượng người tham dự.";
            } else {
                // Thực hiện đăng ký
                $result = $db->execute(
                    "INSERT INTO registrations (user_id, conference_id, status, registration_date) 
                     VALUES (?, ?, 'pending', NOW())",
                    [$userId, $conferenceId]
                );

                if ($result) {
                    // Cập nhật số lượng người tham dự
                    $db->execute(
                        "UPDATE conferences SET attendees = attendees + 1 WHERE id = ?",
                        [$conferenceId]
                    );

                    $message = "Đăng ký tham dự hội nghị thành công! Chúng tôi sẽ gửi thông tin chi tiết qua email của bạn.";
                    
                    // Chuyển hướng sau khi đăng ký thành công
                    header("Refresh: 3; URL=conference-detail.html?id=$conferenceId");
                } else {
                    $error = "Có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại sau.";
                }
            }
        }
    } catch (Exception $e) {
        $error = "Lỗi hệ thống: " . $e->getMessage();
    }
}

// Hiển thị form đăng ký
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tham dự - <?php echo htmlspecialchars($conferenceData['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 70px;
        }
        .registration-form {
            max-width: 800px;
            margin: 0 auto;
        }
        .conference-info {
            background-color: #f8f9fa;
            border-left: 3px solid #0d6efd;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation Container -->
    <div id="header-container"></div>

    <!-- Main Content -->
    <div class="container my-5">
        <h1 class="mb-4">Đăng ký tham dự hội nghị</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="conference-info mb-4">
            <h4><?php echo htmlspecialchars($conferenceData['title']); ?></h4>
            <div class="row">
                <div class="col-md-6">
                    <p><i class="fas fa-calendar me-2"></i> 
                        <?php 
                        $date = date('d/m/Y', strtotime($conferenceData['date']));
                        echo $date;
                        
                        if (!empty($conferenceData['end_date'])) {
                            $endDate = date('d/m/Y', strtotime($conferenceData['end_date']));
                            echo " - $endDate";
                        }
                        ?>
                    </p>
                    <p><i class="fas fa-map-marker-alt me-2"></i> <?php echo htmlspecialchars($conferenceData['location']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><i class="fas fa-tag me-2"></i> <?php echo number_format($conferenceData['price'], 0, ',', '.'); ?>₫</p>
                    <p><i class="fas fa-users me-2"></i> <?php echo $conferenceData['attendees']; ?>/<?php echo $conferenceData['capacity']; ?> người tham dự</p>
                </div>
            </div>
        </div>

        <?php if (!$message): // Chỉ hiển thị form nếu chưa đăng ký thành công ?>
            <div class="registration-form">
                <form method="post" action="">
                    <div class="mb-4">
                        <h5>Thông tin cá nhân</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">Họ</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($userInfo['firstName']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Tên</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($userInfo['lastName']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($userInfo['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($userInfo['phone']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Thông tin thanh toán</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="paymentMethod" class="form-label">Phương thức thanh toán</label>
                                <select class="form-select" id="paymentMethod" name="paymentMethod" required>
                                    <option value="">Chọn phương thức thanh toán</option>
                                    <option value="vnpay">VNPAY</option>
                                    <option value="momo">MoMo</option>
                                    <option value="bank">Chuyển khoản ngân hàng</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="promoCode" class="form-label">Mã khuyến mãi (nếu có)</label>
                                <input type="text" class="form-control" id="promoCode" name="promoCode">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                Tôi đồng ý với <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">điều khoản và điều kiện</a> của hội nghị
                            </label>
                        </div>
                    </div>

                    <div class="mb-4 text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-check me-2"></i>Xác nhận đăng ký
                        </button>
                    </div>
                </form>
            </div>

            <!-- Modal Điều khoản -->
            <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="termsModalLabel">Điều khoản và điều kiện</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h6>1. Đăng ký và thanh toán</h6>
                            <p>Việc đăng ký chỉ được xác nhận sau khi đã thanh toán đầy đủ. Chúng tôi sẽ gửi xác nhận qua email sau khi nhận được thanh toán.</p>

                            <h6>2. Hủy đăng ký và hoàn tiền</h6>
                            <p>- Hoàn tiền 100% nếu hủy trước 30 ngày trước ngày diễn ra sự kiện.<br>
                            - Hoàn tiền 50% nếu hủy 15-29 ngày trước ngày diễn ra sự kiện.<br>
                            - Không hoàn tiền nếu hủy trong vòng 14 ngày trước sự kiện.</p>

                            <h6>3. Thay đổi lịch trình</h6>
                            <p>Ban tổ chức có quyền thay đổi lịch trình, diễn giả hoặc địa điểm nếu cần thiết. Mọi thay đổi sẽ được thông báo qua email.</p>

                            <h6>4. Quyền riêng tư và dữ liệu</h6>
                            <p>Thông tin cá nhân của bạn sẽ được bảo mật theo chính sách quyền riêng tư của chúng tôi và chỉ được sử dụng cho mục đích liên quan đến hội nghị.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="document.getElementById('agreeTerms').checked = true;">Đồng ý</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer Container -->
    <div id="footer-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/render.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/header.js"></script>
    <script src="js/footer.js"></script>
</body>
</html>
