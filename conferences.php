<?php
/**
 * Trang danh sách hội nghị
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'classes/Conference.php';

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : "Đăng nhập";
$userRole = $isLoggedIn ? $_SESSION['user_role'] : "guest";

// Khởi tạo đối tượng Conference
$conferenceObj = new Conference();

// Lấy danh sách các hội nghị (có thể áp dụng phân trang và lọc ở đây)
$conferences = $conferenceObj->getAllConferences();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm Hội nghị - Danh sách Hội nghị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">    
    <style>
        body {
            padding-top: 55px; /* Đảm bảo nội dung không bị che khuất bởi thanh navbar fixed */
        }
        .conference-card {
            transition: transform 0.3s ease;
            height: 100%;
        }
        .conference-card:hover {
            transform: translateY(-5px);
        }
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <div class="bg-primary text-white py-4">
        <div class="container">
            <h1 class="display-6 fw-bold">Tất cả Hội nghị</h1>
            <p class="lead">Khám phá các hội nghị thú vị tại Việt Nam</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Search and Filter Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="searchInput" class="form-label">Tìm kiếm Hội nghị</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Tìm theo tên, mô tả, hoặc địa điểm...">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="categoryFilter" class="form-label">Danh mục</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="" selected>Tất cả danh mục</option>
                        <option value="technology">Công nghệ thông tin</option>
                        <option value="business">Kinh doanh & Marketing</option>
                        <option value="science">Khoa học & Nghiên cứu</option>
                        <option value="education">Giáo dục & Đào tạo</option>
                        <option value="medical">Y tế & Sức khỏe</option>
                        <option value="arts">Nghệ thuật & Văn hóa</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="dateFilter" class="form-label">Thời gian</label>
                    <select class="form-select" id="dateFilter">
                        <option value="" selected>Mọi thời gian</option>
                        <option value="upcoming">Sắp diễn ra</option>
                        <option value="thisMonth">Tháng này</option>
                        <option value="thisYear">Năm nay</option>
                        <option value="past">Đã diễn ra</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Conferences List -->
        <div class="row" id="conferencesList">
            <?php if (empty($conferences)): ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">Không tìm thấy hội nghị!</h4>
                        <p>Hiện tại không có hội nghị nào phù hợp với bộ lọc của bạn.</p>
                        <hr>
                        <p class="mb-0">Hãy thay đổi các bộ lọc hoặc quay lại sau.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Dữ liệu sẽ được hiển thị thông qua JavaScript -->
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <nav class="mt-5" aria-label="Điều hướng phân trang">
            <ul class="pagination justify-content-center" id="pagination">
                <!-- Phân trang sẽ được hiển thị thông qua JavaScript -->
            </ul>
        </nav>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/data.js"></script>
    <script src="js/conferences-php.js"></script>
</body>
</html>
