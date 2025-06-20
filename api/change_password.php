<?php
/**
 * API đổi mật khẩu người dùng
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

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];

    // Lấy dữ liệu từ form
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Kiểm tra xác nhận mật khẩu
    if ($newPassword !== $confirmPassword) {
        setFlashMessage('danger', 'Mật khẩu xác nhận không khớp!');
        redirect('../profile.php');
        exit;
    }

    // Kiểm tra mật khẩu hiện tại
    try {
        $db = connectDB();

        // Lấy thông tin người dùng
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra mật khẩu
        if ($user && password_verify($currentPassword, $user['password'])) {
            // Mật khẩu hiện tại đúng, thực hiện đổi mật khẩu
            $userModel = new User();
            $result = $userModel->changePassword($userId, $newPassword);

            if ($result) {
                setFlashMessage('success', 'Đổi mật khẩu thành công!');
            } else {
                setFlashMessage('danger', 'Có lỗi xảy ra khi đổi mật khẩu!');
            }
        } else {
            setFlashMessage('danger', 'Mật khẩu hiện tại không đúng!');
        }
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Đã có lỗi xảy ra: ' . $e->getMessage());
    }

    // Chuyển hướng về trang hồ sơ
    redirect('../profile.php');
} else {
    setFlashMessage('danger', 'Phương thức không được hỗ trợ!');
    redirect('../profile.php');
}
?>