<?php
/**
 * Trang chỉnh sửa hội nghị
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'classes/Conference.php';

// Kiểm tra đăng nhập và quyền
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'organizer')) {
    setFlashMessage('danger', 'Bạn không có quyền truy cập trang này!');
    redirect('login.php');
    exit;
}

// Kiểm tra ID hội nghị
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('danger', 'ID hội nghị không hợp lệ!');
    redirect('conference-manager.php');
    exit;
}

$conferenceId = intval($_GET['id']);
$conferenceModel = new Conference();
$conference = $conferenceModel->getConferenceById($conferenceId);

// Kiểm tra hội nghị tồn tại
if (!$conference) {
    setFlashMessage('danger', 'Không tìm thấy hội nghị!');
    redirect('conference-manager.php');
    exit;
}

// Kiểm tra quyền: admin có thể sửa tất cả, organizer chỉ sửa của mình
if ($_SESSION['user_role'] === 'organizer' && $conference['created_by'] != $_SESSION['user_id']) {
    setFlashMessage('danger', 'Bạn không có quyền chỉnh sửa hội nghị này!');
    redirect('conference-manager.php');
    exit;
}

// Lấy dữ liệu chi tiết cho hội nghị
$speakers = $conferenceModel->getConferenceSpeakers($conferenceId);
$schedule = $conferenceModel->getConferenceSchedule($conferenceId);
$objectives = $conferenceModel->getConferenceObjectives($conferenceId);
$audience = $conferenceModel->getConferenceAudience($conferenceId);
$faq = $conferenceModel->getConferenceFaq($conferenceId);

$pageTitle = "Chỉnh sửa hội nghị";
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
        .move-handle {
            cursor: move;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <div class="bg-primary text-white py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="display-6 fw-bold">
                    <i class="fas fa-edit me-2"></i><?php echo $pageTitle; ?>
                </h1>
                <div>
                    <a href="conference-manager.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                    <a href="conference-detail.php?id=<?php echo $conferenceId; ?>" class="btn btn-light ms-2">
                        <i class="fas fa-eye me-2"></i>Xem hội nghị
                    </a>
                </div>
            </div>
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

    <!-- Edit Conference Form -->
    <div class="container my-5">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs nav-fill mb-4" id="editTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                    <i class="fas fa-info-circle me-1"></i>Thông tin cơ bản
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="speakers-tab" data-bs-toggle="tab" data-bs-target="#speakers" type="button" role="tab">
                    <i class="fas fa-user-tie me-1"></i>Diễn giả
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-1"></i>Lịch trình
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                    <i class="fas fa-list-alt me-1"></i>Chi tiết mở rộng
                </button>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content">
            <!-- Basic Info Tab -->
            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <form id="basicInfoForm" action="api/update_conference.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $conferenceId; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">Tên hội nghị</label>
                                    <input type="text" class="form-control" name="title" id="title" value="<?php echo htmlspecialchars($conference['title']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Danh mục</label>
                                    <select class="form-select" name="category" id="category" required>
                                        <option value="">Chọn danh mục</option>
                                        <option value="Công nghệ" <?php echo ($conference['category'] == 'Công nghệ') ? 'selected' : ''; ?>>Công nghệ</option>
                                        <option value="Kinh doanh" <?php echo ($conference['category'] == 'Kinh doanh') ? 'selected' : ''; ?>>Kinh doanh</option>
                                        <option value="Thiết kế" <?php echo ($conference['category'] == 'Thiết kế') ? 'selected' : ''; ?>>Thiết kế</option>
                                        <option value="Marketing" <?php echo ($conference['category'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                        <option value="Y tế" <?php echo ($conference['category'] == 'Y tế') ? 'selected' : ''; ?>>Y tế</option>
                                        <option value="Giáo dục" <?php echo ($conference['category'] == 'Giáo dục') ? 'selected' : ''; ?>>Giáo dục</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label">Ngày bắt đầu</label>
                                    <input type="date" class="form-control" name="date" id="date" value="<?php echo $conference['date']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="endDate" class="form-label">Ngày kết thúc</label>
                                    <input type="date" class="form-control" name="endDate" id="endDate" value="<?php echo $conference['endDate']; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Địa điểm</label>
                                <input type="text" class="form-control" name="location" id="location" value="<?php echo htmlspecialchars($conference['location']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea class="form-control" name="description" id="description" rows="4" required><?php echo htmlspecialchars($conference['description']); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ)</label>
                                    <input type="number" class="form-control" name="price" id="price" min="0" step="1000" value="<?php echo $conference['price']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="capacity" class="form-label">Sức chứa</label>
                                    <input type="number" class="form-control" name="capacity" id="capacity" min="1" value="<?php echo $conference['capacity']; ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" name="status" id="status" required>
                                        <option value="active" <?php echo ($conference['status'] == 'active') ? 'selected' : ''; ?>>Đang diễn ra</option>
                                        <option value="draft" <?php echo ($conference['status'] == 'draft') ? 'selected' : ''; ?>>Bản nháp</option>
                                        <option value="cancelled" <?php echo ($conference['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                                        <option value="completed" <?php echo ($conference['status'] == 'completed') ? 'selected' : ''; ?>>Đã kết thúc</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="image" class="form-label">Hình ảnh</label>
                                    <?php if (!empty($conference['image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo htmlspecialchars($conference['image']); ?>" alt="Hình ảnh hiện tại" class="img-thumbnail" style="height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="image" id="image" accept="image/*">
                                    <small class="text-muted">Để trống nếu không muốn thay đổi hình ảnh</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="organizer_name" class="form-label">Tên tổ chức</label>
                                    <input type="text" class="form-control" name="organizer_name" id="organizer_name" value="<?php echo htmlspecialchars($conference['organizer_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="organizer_email" class="form-label">Email liên hệ</label>
                                    <input type="email" class="form-control" name="organizer_email" id="organizer_email" value="<?php echo htmlspecialchars($conference['organizer_email']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="organizer_phone" class="form-label">Điện thoại liên hệ</label>
                                <input type="text" class="form-control" name="organizer_phone" id="organizer_phone" value="<?php echo htmlspecialchars($conference['organizer_phone']); ?>" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Lưu thông tin cơ bản</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Speakers Tab -->
            <div class="tab-pane fade" id="speakers" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Chức năng quản lý diễn giả đang được phát triển
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Tab -->
            <div class="tab-pane fade" id="schedule" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Chức năng quản lý lịch trình đang được phát triển
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Details Tab -->
            <div class="tab-pane fade" id="details" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Chức năng quản lý mục tiêu, đối tượng và FAQ đang được phát triển
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
