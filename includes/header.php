<?php
/**
 * Header chung cho toàn bộ trang web
 */

// Xác định trang hiện tại
$currentPage = basename($_SERVER['PHP_SELF']);

// Hàm kiểm tra trang active
function isActivePage($page)
{
    global $currentPage;
    return ($currentPage == $page) ? 'active' : '';
}

// Kiểm tra trạng thái đăng nhập nếu chưa được xác định
if (!isset($isLoggedIn)) {
    $isLoggedIn = isset($_SESSION['user_id']);
    $userName = $isLoggedIn ? $_SESSION['user_name'] : "Đăng nhập";
    $userRole = $isLoggedIn ? $_SESSION['user_role'] : "guest";
}
?>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-users-cog me-2"></i>Trung tâm Hội nghị
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActivePage('index.php'); ?>" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActivePage('conferences.php'); ?>" href="conferences.php">Hội
                            nghị</a>
                    </li>

                    <?php if ($userRole == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActivePage('admin.php'); ?>" href="admin.php">Quản trị</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($userRole == 'admin' || $userRole == 'organizer'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActivePage('conference-manager.php'); ?>"
                                href="conference-manager.php">Quản lý Hội nghị</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActivePage('profile.php'); ?>" href="profile.php">Hồ sơ</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo $userName; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Hồ sơ</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>