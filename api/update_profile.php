<?php
/**
 * API cập nhật hồ sơ người dùng
 */
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/User.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    setFlashMessage('danger', 'Bạn cần đăng nhập để thực hiện thao tác này!');
    redirect('../login.php');
    exit;
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];

    // Lấy dữ liệu từ form
    $data = [
        'firstName' => sanitize($_POST['firstName']),
        'lastName' => sanitize($_POST['lastName']),
        'phone' => sanitize($_POST['phone'])
    ];

    // Cập nhật thông tin
    $userModel = new User();
    $result = $userModel->updateUser($userId, $data);

    if ($result) {
        // Cập nhật tên trong session
        $_SESSION['user_name'] = $data['firstName'] . ' ' . $data['lastName'];

        setFlashMessage('success', 'Cập nhật thông tin thành công!');
    } else {
        setFlashMessage('danger', 'Có lỗi xảy ra khi cập nhật thông tin!');
    }

    // Chuyển hướng về trang hồ sơ
    redirect('../profile.php');
} else {
    setFlashMessage('danger', 'Phương thức không được hỗ trợ!');
    redirect('../profile.php');
}
?>