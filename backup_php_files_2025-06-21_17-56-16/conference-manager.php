<?php
/**
 * Trang quản lý hội nghị dành cho tổ chức và admin
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'classes/Conference.php';

// Kiểm tra quyền
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'organizer')) {
    // Redirect nếu không có quyền
    setFlashMessage('danger', 'Bạn không có quyền truy cập trang này!');
    redirect('login.php');
}

// Khởi tạo đối tượng Conference
$conferenceModel = new Conference();
$userId = $_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role'] === 'admin');

// Lấy danh sách hội nghị theo người dùng
if ($isAdmin) {
    // Admin có thể xem tất cả hội nghị
    $conferences = $conferenceModel->getAllConferences();
} else {
    // Người tổ chức chỉ xem hội nghị của mình
    $conferences = $conferenceModel->getConferencesByUserId($userId);
}

// Lấy thống kê cơ bản
$totalMyConferences = count($conferences);
$totalAttendees = 0;
$totalRevenue = 0;

foreach ($conferences as $conference) {
    $totalAttendees += $conference['attendees'];
    $totalRevenue += $conference['price'] * $conference['attendees'];
}

$pageTitle = "Quản lý Hội nghị";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm Hội nghị - <?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 55px; /* Đảm bảo nội dung không bị che khuất bởi thanh navbar fixed */
        }
        .hero-section {
            background: linear-gradient(135deg, #4b6cb7 0%, #253545 100%);
            color: white;
            padding: 100px 0 80px;
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
            background: url('https://images.unsplash.com/photo-1551818255-e6e10975bc17?w=1200&h=600&fit=crop') center/cover;
            opacity: 0.1;
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .manager-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .manager-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .action-buttons .btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 3rem 0;
            margin-top: 3rem;
        }
        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        .toast-container {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 1050;
        }
        .conf-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content text-center">
            <h1 class="display-5 fw-bold">Quản lý Hội nghị của bạn</h1>
            <p class="lead">Tạo, quản lý và phát triển các hội nghị chuyên nghiệp</p>
            <button class="btn btn-light btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#createConferenceModal">
                <i class="fas fa-plus-circle me-2"></i>Tạo Hội nghị mới
            </button>
        </div>
    </section>

    <!-- Toast Container for Notifications -->
    <div class="toast-container">
        <?php if ($flashMessage = getFlashMessage()): ?>
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-<?php echo $flashMessage['type']; ?> text-white">
                    <strong class="me-auto">Thông báo</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <?php echo $flashMessage['message']; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dashboard Stats -->
    <section class="container my-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo $totalMyConferences; ?></h3>
                                <p class="mb-0">Hội nghị của tôi</p>
                            </div>
                            <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo number_format($totalAttendees, 0, ',', '.'); ?></h3>
                                <p class="mb-0">Người tham dự</p>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo number_format($totalRevenue, 0, ',', '.'); ?> VNĐ</h3>
                                <p class="mb-0">Doanh thu</p>
                            </div>
                            <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Conferences List -->
    <section class="container my-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Hội nghị của tôi</h4>
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" placeholder="Tìm kiếm..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Hội nghị</th>
                                <th>Ngày</th>
                                <th>Địa điểm</th>
                                <th>Người tham dự</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="conferenceTable">
                            <?php if (count($conferences) > 0): ?>
                                <?php foreach ($conferences as $conference): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($conference['image']); ?>" alt="<?php echo htmlspecialchars($conference['title']); ?>" class="me-3 conf-image" style="width: 80px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($conference['title']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($conference['category']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($conference['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($conference['location']); ?></td>
                                        <td><?php echo $conference['attendees']; ?>/<?php echo $conference['capacity']; ?></td>
                                        <td>
                                            <?php if ($conference['status'] == 'active'): ?>
                                                <span class="badge bg-success status-badge">Đang diễn ra</span>
                                            <?php elseif ($conference['status'] == 'completed'): ?>
                                                <span class="badge bg-secondary status-badge">Đã kết thúc</span>
                                            <?php elseif ($conference['status'] == 'cancelled'): ?>
                                                <span class="badge bg-danger status-badge">Đã hủy</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning status-badge">Bản nháp</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="conference-edit.php?id=<?php echo $conference['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="conference-detail.php?id=<?php echo $conference['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $conference['id']; ?>, '<?php echo addslashes($conference['title']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="py-5">
                                            <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                                            <h5>Bạn chưa có hội nghị nào</h5>
                                            <p class="text-muted mb-4">Bắt đầu tạo hội nghị đầu tiên của bạn ngay bây giờ</p>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createConferenceModal">
                                                <i class="fas fa-plus-circle me-2"></i>Tạo Hội nghị
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Create Conference Modal -->
    <div class="modal fade" id="createConferenceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tạo Hội nghị mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createConferenceForm" action="api/conferences.php" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Tên hội nghị</label>
                                <input type="text" class="form-control" name="title" id="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Danh mục</label>
                                <select class="form-select" name="category" id="category" required>
                                    <option value="">Chọn danh mục</option>
                                    <option value="Công nghệ">Công nghệ</option>
                                    <option value="Kinh doanh">Kinh doanh</option>
                                    <option value="Thiết kế">Thiết kế</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Y tế">Y tế</option>
                                    <option value="Giáo dục">Giáo dục</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Ngày bắt đầu</label>
                                <input type="date" class="form-control" name="date" id="date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="endDate" class="form-label">Ngày kết thúc</label>
                                <input type="date" class="form-control" name="endDate" id="endDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Địa điểm</label>
                            <input type="text" class="form-control" name="location" id="location" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" id="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Giá (VNĐ)</label>
                                <input type="number" class="form-control" name="price" id="price" min="0" step="1000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="capacity" class="form-label">Sức chứa</label>
                                <input type="number" class="form-control" name="capacity" id="capacity" min="1" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="active">Đang diễn ra</option>
                                    <option value="draft">Bản nháp</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control" name="image" id="image" accept="image/*">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="organizer_name" class="form-label">Tên tổ chức</label>
                                <input type="text" class="form-control" name="organizer_name" id="organizer_name" value="<?php echo $_SESSION['user_name']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="organizer_email" class="form-label">Email liên hệ</label>
                                <input type="email" class="form-control" name="organizer_email" id="organizer_email" value="<?php echo $_SESSION['user_email']; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="organizer_phone" class="form-label">Điện thoại liên hệ</label>
                            <input type="text" class="form-control" name="organizer_phone" id="organizer_phone" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" form="createConferenceForm" class="btn btn-primary">Tạo Hội nghị</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa hội nghị "<span id="conferenceTitle"></span>"?</p>
                    <p class="text-danger">Hành động này không thể hoàn tác!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteForm" method="post" action="api/conferences_delete.php">
                        <input type="hidden" name="id" id="conferenceId">
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript để xử lý các tác vụ
        function confirmDelete(id, title) {
            document.getElementById('conferenceId').value = id;
            document.getElementById('conferenceTitle').textContent = title;
            
            // Hiển thị modal xác nhận
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        }
        
        // Tìm kiếm trong bảng
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#conferenceTable tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Tự động ẩn toast sau 3 giây
        document.addEventListener('DOMContentLoaded', function() {
            const toastElements = document.querySelectorAll('.toast');
            toastElements.forEach(toast => {
                setTimeout(function() {
                    const bsToast = bootstrap.Toast.getInstance(toast);
                    if (bsToast) bsToast.hide();
                }, 3000);
            });
        });
    </script>
</body>
</html>
