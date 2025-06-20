<?php
/**
 * Trang chủ - Trung tâm Hội nghị
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : "Đăng nhập";
$userRole = $isLoggedIn ? $_SESSION['user_role'] : "guest";

// Tiêu đề trang
$pageTitle = "Trung tâm Hội nghị - Khám phá & Tổ chức Hội nghị Tuyệt vời";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 55px; /* Đảm bảo nội dung không bị che khuất bởi thanh navbar fixed */
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 120px 0;
            position: relative;
            overflow: hidden;
            margin-top: -70px; /* Bù trừ padding-top của body để hero section tiếp nối với navbar */
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&h=800&fit=crop') center/cover;
            opacity: 0.1;
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .conference-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .conference-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 28px;
        }
        .testimonial-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .testimonial-card:hover {
            transform: translateY(-5px);
        }
        .footer {
            background: #2c3e50;
            color: white;
            padding: 60px 0 30px;
        }
        .social-icons a {
            color: white;
            font-size: 20px;
            margin-right: 15px;
            transition: all 0.3s;
        }
        .social-icons a:hover {
            transform: translateY(-3px);
            color: #007bff;
        }
    </style>
</head>
<body>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Kết nối. Học hỏi. Truyền cảm hứng.</h1>
                <p class="lead mb-4">Khám phá và tham gia các hội nghị đa dạng hàng đầu tại Việt Nam và quốc tế. Tìm kiếm hội nghị phù hợp với bạn hoặc tổ chức hội nghị của riêng bạn ngay hôm nay!</p>
                <div class="d-flex gap-3">
                    <a href="conferences.html" class="btn btn-light btn-lg px-4">Khám phá Hội nghị</a>
                    <a href="conference-manager.html" class="btn btn-outline-light btn-lg px-4">Tổ chức Hội nghị</a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0 text-center">
                <img src="https://i.imgur.com/EWyf94Q.png" alt="Conference Illustration" class="img-fluid" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Featured Conferences -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Hội nghị Nổi bật</h2>
        <div id="featuredConferences" class="row"></div>
        <div class="text-center mt-4">
            <a href="conferences.html" class="btn btn-primary">Xem tất cả hội nghị</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Dịch vụ Hàng đầu</h2>
        <div class="row">
            <div class="col-md-4 mb-5">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-center">Khám phá Hội nghị</h3>
                <p class="text-center text-muted">Tìm kiếm hội nghị phù hợp với lĩnh vực và sở thích của bạn từ kho dữ liệu đa dạng các sự kiện.</p>
            </div>
            <div class="col-md-4 mb-5">
                <div class="feature-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="text-center">Quản lý Lịch trình</h3>
                <p class="text-center text-muted">Quản lý toàn bộ lịch trình hội nghị, đăng ký tham dự và nhận thông báo về các sự kiện quan trọng.</p>
            </div>
            <div class="col-md-4 mb-5">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="text-center">Tổ chức & Kết nối</h3>
                <p class="text-center text-muted">Tổ chức hội nghị của riêng bạn và kết nối với những người tham dự có cùng đam mê và sở thích.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Người dùng Nói gì về Chúng tôi</h2>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card h-100 bg-white p-4">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="User" class="rounded-circle" width="60">
                        <div class="ms-3">
                            <h5 class="mb-0">Lê Thị Hồng</h5>
                            <p class="text-muted mb-0">Nhà Tổ chức Sự kiện</p>
                        </div>
                    </div>
                    <p class="mb-0">"Nền tảng này đã giúp tôi tổ chức hội nghị một cách dễ dàng và chuyên nghiệp. Các công cụ quản lý hội nghị rất tiện lợi và dễ sử dụng!"</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card h-100 bg-white p-4">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/men/44.jpg" alt="User" class="rounded-circle" width="60">
                        <div class="ms-3">
                            <h5 class="mb-0">Nguyễn Văn Tuấn</h5>
                            <p class="text-muted mb-0">Chuyên gia CNTT</p>
                        </div>
                    </div>
                    <p class="mb-0">"Tôi đã tìm thấy nhiều hội nghị công nghệ thú vị thông qua trang web này. Giao diện tìm kiếm rất thuận tiện và tôi luôn cập nhật được những sự kiện mới nhất."</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card h-100 bg-white p-4">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="User" class="rounded-circle" width="60">
                        <div class="ms-3">
                            <h5 class="mb-0">Phạm Minh Anh</h5>
                            <p class="text-muted mb-0">Giáo viên</p>
                        </div>
                    </div>
                    <p class="mb-0">"Các hội nghị giáo dục mà tôi tham gia qua nền tảng này đều rất chất lượng. Tôi đã học hỏi được nhiều kiến thức mới và mở rộng mạng lưới kết nối."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Sẵn sàng tham gia hội nghị tiếp theo?</h2>
        <p class="lead mb-4">Hãy tạo tài khoản ngay hôm nay để khám phá và đăng ký tham gia các hội nghị hấp dẫn</p>
        <a href="register.html" class="btn btn-light btn-lg px-4 me-2">Đăng ký Ngay</a>
        <a href="conferences.html" class="btn btn-outline-light btn-lg px-4">Tìm Hội nghị</a>
    </div>
</section>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/data.js"></script>
<script src="js/home-php.js"></script>

</body>
</html>
