<?php
/**
 * Xử lý đăng xuất
 */
require_once 'includes/config.php';

// Xóa tất cả dữ liệu session
session_unset();
session_destroy();

// Xóa cookie ghi nhớ đăng nhập nếu có
if (isset($_COOKIE['auth_token'])) {
    setcookie('auth_token', '', time() - 3600, '/');
}

// Chuyển hướng về trang chủ
header('Location: index.php');
exit;
