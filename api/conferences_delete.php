<?php
/**
 * API xóa hội nghị
 */
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Conference.php';

// Kiểm tra xác thực
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'organizer')) {
    // Redirect nếu không có quyền
    setFlashMessage('danger', 'Bạn không có quyền thực hiện thao tác này!');
    redirect('../login.php');
    exit;
}

// Xử lý yêu cầu xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $conferenceId = intval($_POST['id']);
    $conferenceModel = new Conference();

    // Kiểm tra nếu là organizer thì chỉ được xóa hội nghị do mình tạo
    if ($_SESSION['user_role'] === 'organizer') {
        $conference = $conferenceModel->getConferenceById($conferenceId);

        if (!$conference || $conference['created_by'] != $_SESSION['user_id']) {
            setFlashMessage('danger', 'Bạn không có quyền xóa hội nghị này!');
            redirect('../admin.php');
            exit;
        }
    }

    // Thực hiện xóa hội nghị
    $result = $conferenceModel->deleteConference($conferenceId);

    if ($result) {
        setFlashMessage('success', 'Hội nghị đã được xóa thành công!');
    } else {
        setFlashMessage('danger', 'Có lỗi xảy ra khi xóa hội nghị!');
    }

    // Chuyển hướng về trang admin
    redirect('../admin.php');
} else {
    // Phương thức không được hỗ trợ hoặc thiếu tham số
    setFlashMessage('danger', 'Yêu cầu không hợp lệ!');
    redirect('../admin.php');
}
?>