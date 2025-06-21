<?php
/**
 * Header chung cho toàn bộ trang web
 */

// Xác định trang hiện tại
$currentPage = basename($_SERVER['PHP_SELF']);

// Hàm kiểm tra trang active đã đơn giản hóa - chỉ dùng HTML
function isActivePage($page)
{
    global $currentPage;
    
    // Đảm bảo chỉ so sánh với file HTML
    $currentPageHtml = substr($currentPage, 0, -4) . '.html';
    
    // Đảm bảo page cũng là HTML
    if (!str_ends_with($page, '.html')) {
        $page = substr($page, 0, -4) . '.html';
    }
    
    // Kiểm tra trang hiện tại có khớp không
    return ($currentPageHtml == $page) ? 'active' : '';
}

// Kiểm tra trạng thái đăng nhập nếu chưa được xác định
if (!isset($isLoggedIn)) {
    $isLoggedIn = isset($_SESSION['user_id']);
    $userName = $isLoggedIn ? $_SESSION['user_name'] : "Đăng nhập";
    $userRole = $isLoggedIn ? $_SESSION['user_role'] : "guest";
}
?>

<header>    <!-- Basic URL logging -->
    <script>
        // Log thông tin URL hiện tại
        console.log('Current URL:', window.location.href);
        console.log('Current pathname:', window.location.pathname);
    </script>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-users-cog me-2"></i>Trung tâm Hội nghị
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActivePage('index.html'); ?>" href="index.html">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActivePage('conferences.php'); ?>" href="conferences.html">Hội
                            nghị</a>
                    </li>

                    <?php if ($userRole == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActivePage('admin.php'); ?>" href="admin.html">Quản trị</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($userRole == 'admin' || $userRole == 'organizer'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActivePage('conference-manager.php'); ?>"
                                href="conference-manager.html">Quản lý Hội nghị</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActivePage('profile.php'); ?>" href="profile.html">Hồ sơ</a>
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
                                <li><a class="dropdown-item" href="profile.html">Hồ sơ</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#" onclick="logout()">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="nav-link" href="login.html">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3" href="register.html">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>