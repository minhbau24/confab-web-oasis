<?php
/**
 * Trang quản trị hệ thống
 * Chỉ admin mới có quyền truy cập trang này
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'classes/Conference.php';

// Kiểm tra quyền admin
if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    // Redirect nếu không phải admin
    setFlashMessage('danger', 'Bạn không có quyền truy cập trang này!');
    redirect('login.php');
}

// Khởi tạo đối tượng Conference
$conferenceModel = new Conference();

// Lấy thống kê tổng quan
$totalConferences = $conferenceModel->countAllConferences();
$activeConferences = $conferenceModel->countFilteredConferences('', '', 'active');
$totalAttendees = $conferenceModel->getTotalAttendees();
$totalRevenue = $conferenceModel->getTotalRevenue();

// Lấy danh sách hội nghị cho bảng quản lý
$conferences = $conferenceModel->getAllConferences(20, 0); // Lấy 20 hội nghị mới nhất

$pageTitle = "Quản trị hệ thống";
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <div class="bg-danger text-white py-4">
        <div class="container">
            <h1 class="display-6 fw-bold">
                <i class="fas fa-cog me-2"></i><?php echo $pageTitle; ?>
            </h1>
            <p class="lead">Quản lý hội nghị và người dùng</p>
        </div>
    </div>

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

    <!-- Admin Content -->
    <div class="container my-5">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $totalConferences; ?></h4>
                                <p class="mb-0">Tổng số Hội nghị</p>
                            </div>
                            <i class="fas fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $activeConferences; ?></h4>
                                <p class="mb-0">Hội nghị đang diễn ra</p>
                            </div>
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo number_format($totalAttendees, 0, ',', '.'); ?></h4>
                                <p class="mb-0">Tổng số Người tham dự</p>
                            </div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo number_format($totalRevenue, 0, ',', '.'); ?> VNĐ</h4>
                                <p class="mb-0">Tổng doanh thu</p>
                            </div>
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conference Management -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Quản lý Hội nghị</h5>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addConferenceModal">
                    <i class="fas fa-plus me-1"></i>Thêm Hội nghị
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="adminSearch" placeholder="Tìm kiếm hội nghị..." oninput="searchConferencesTable()">
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên hội nghị</th>
                                <th>Ngày</th>
                                <th>Địa điểm</th>
                                <th>Danh mục</th>
                                <th>Người tham dự</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="conferences-table">
                            <?php foreach ($conferences as $conf): ?>
                            <tr>
                                <td><?php echo $conf['id']; ?></td>
                                <td><?php echo htmlspecialchars($conf['title']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($conf['date'])); ?></td>
                                <td><?php echo htmlspecialchars($conf['location']); ?></td>
                                <td><?php echo htmlspecialchars($conf['category']); ?></td>
                                <td><?php echo $conf['attendees']; ?>/<?php echo $conf['capacity']; ?></td>
                                <td>
                                    <?php if ($conf['status'] == 'active'): ?>
                                        <span class="badge bg-success">Đang diễn ra</span>
                                    <?php elseif ($conf['status'] == 'completed'): ?>
                                        <span class="badge bg-secondary">Đã kết thúc</span>
                                    <?php elseif ($conf['status'] == 'cancelled'): ?>
                                        <span class="badge bg-danger">Đã hủy</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Bản nháp</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="conference-edit.php?id=<?php echo $conf['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger" onclick="confirmDelete(<?php echo $conf['id']; ?>, '<?php echo addslashes($conf['title']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($conferences) == 0): ?>
                            <tr>
                                <td colspan="8" class="text-center">Không có hội nghị nào</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- User Management -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Quản lý Người dùng</h5>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-1"></i>Thêm Người dùng
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Chức năng quản lý người dùng đang được phát triển
                </div>
                <!-- User table will be implemented here -->
            </div>
        </div>
    </div>

    <!-- Add Conference Modal -->
    <div class="modal fade" id="addConferenceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Hội nghị mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addConferenceForm" action="api/conferences.php" method="post" enctype="multipart/form-data">
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
                                <input type="text" class="form-control" name="organizer_name" id="organizer_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="organizer_email" class="form-label">Email liên hệ</label>
                                <input type="email" class="form-control" name="organizer_email" id="organizer_email" required>
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
                    <button type="submit" form="addConferenceForm" class="btn btn-success">Thêm Hội nghị</button>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Người dùng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">Họ</label>
                                <input type="text" class="form-control" id="firstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Tên</label>
                                <input type="text" class="form-control" id="lastName" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Quyền</label>
                            <select class="form-select" id="role" required>
                                <option value="user">Người dùng</option>
                                <option value="organizer">Tổ chức</option>
                                <option value="admin">Quản trị viên</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" onclick="addUser()">Thêm Người dùng</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript để xử lý các tác vụ trong trang admin
        function confirmDelete(id, title) {
            document.getElementById('conferenceId').value = id;
            document.getElementById('conferenceTitle').textContent = title;
            
            // Hiển thị modal xác nhận
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        }
        
        function searchConferencesTable() {
            const searchTerm = document.getElementById('adminSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#conferences-table tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }
        
        function addUser() {
            // Chức năng này sẽ được triển khai sau
            alert('Chức năng thêm người dùng sẽ được triển khai trong phiên bản tiếp theo!');
        }
        
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
