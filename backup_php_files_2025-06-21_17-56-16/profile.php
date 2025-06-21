<?php
/**
 * Trang hồ sơ người dùng
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'classes/User.php';
require_once 'classes/Conference.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    // Redirect nếu chưa đăng nhập
    setFlashMessage('warning', 'Vui lòng đăng nhập để xem hồ sơ của bạn!');
    redirect('login.php');
}

// Lấy thông tin người dùng
$userModel = new User();
$userId = $_SESSION['user_id'];
$userData = $userModel->getUserById($userId);

// Lấy danh sách hội nghị đã tham gia
$joinedConferences = $userModel->getJoinedConferences($userId);
$joinedConferencesCount = count($joinedConferences);

$pageTitle = "Hồ sơ cá nhân";
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
        .conference-card img {
            height: 160px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

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

    <!-- Profile Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Profile Info -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face" 
                             alt="Hồ sơ" class="rounded-circle mb-3" width="150" height="150">
                        <h4><?php echo htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']); ?></h4>
                        <p class="text-muted">
                            <span class="badge bg-<?php 
                                if ($userData['role'] == 'admin') echo 'danger';
                                elseif ($userData['role'] == 'organizer') echo 'success';
                                else echo 'primary';
                            ?>">
                                <?php 
                                    if ($userData['role'] == 'admin') echo 'Quản trị viên';
                                    elseif ($userData['role'] == 'organizer') echo 'Tổ chức';
                                    else echo 'Người dùng';
                                ?>
                            </span>
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($userData['email']); ?>
                        </p>
                        <?php if (!empty($userData['phone'])): ?>
                        <p class="text-muted">
                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($userData['phone']); ?>
                        </p>
                        <?php endif; ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            Chỉnh sửa hồ sơ
                        </button>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thống kê</h5>
                        <div class="row text-center">
                            <div class="col-12">
                                <h4><?php echo $joinedConferencesCount; ?></h4>
                                <small class="text-muted">Hội nghị đã tham gia</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions Card -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Thao tác nhanh</h5>
                        <div class="d-grid gap-2">
                            <a href="conferences.php" class="btn btn-outline-primary">
                                <i class="fas fa-search me-1"></i>Tìm hội nghị mới
                            </a>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fas fa-key me-1"></i>Đổi mật khẩu
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Joined Conferences -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>Hội nghị đã tham gia
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (count($joinedConferences) > 0): ?>
                                <?php foreach ($joinedConferences as $conference): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100 conference-card">
                                            <img src="<?php echo htmlspecialchars($conference['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($conference['title']); ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($conference['title']); ?></h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($conference['location']); ?><br>
                                                    <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($conference['date'])); ?>
                                                </p>
                                                <a href="conference-detail.php?id=<?php echo $conference['id']; ?>" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-4">
                                    <i class="fas fa-calendar-plus fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Bạn chưa tham gia hội nghị nào.</p>
                                    <a href="conferences.php" class="btn btn-primary">
                                        Khám phá Hội nghị
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($userData['role'] == 'organizer' || $userData['role'] == 'admin'): ?>
                <!-- Organized Conferences -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bullhorn me-2"></i>Hội nghị đang tổ chức
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Danh sách các hội nghị do bạn tổ chức sẽ hiển thị tại đây.
                        </div>
                        <a href="conference-manager.php" class="btn btn-success">
                            <i class="fas fa-plus-circle me-1"></i>Quản lý hội nghị
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa hồ sơ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" action="api/update_profile.php" method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">Họ</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" 
                                       value="<?php echo htmlspecialchars($userData['firstName']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Tên</label>
                                <input type="text" class="form-control" id="lastName" name="lastName"
                                       value="<?php echo htmlspecialchars($userData['lastName']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="<?php echo htmlspecialchars($userData['phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($userData['email']); ?>" readonly>
                            <small class="text-muted">Email không thể thay đổi.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" form="editProfileForm" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Đổi mật khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm" action="api/change_password.php" method="post">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" 
                                   minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" 
                                   minlength="6" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" form="changePasswordForm" class="btn btn-primary">Đổi mật khẩu</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript cho form xác nhận mật khẩu
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.getElementById('changePasswordForm');
            
            passwordForm.addEventListener('submit', function(event) {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (newPassword !== confirmPassword) {
                    event.preventDefault();
                    alert('Mật khẩu xác nhận không khớp!');
                }
            });
            
            // Tự động ẩn toast sau 3 giây
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
