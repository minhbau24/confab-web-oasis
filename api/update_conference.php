<?php
/**
 * API cập nhật thông tin hội nghị
 */
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Conference.php';

// Kiểm tra đăng nhập và quyền
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'organizer')) {
    setFlashMessage('danger', 'Bạn không có quyền thực hiện thao tác này!');
    redirect('../login.php');
    exit;
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $conferenceId = intval($_POST['id']);
    $conferenceModel = new Conference();
    $conference = $conferenceModel->getConferenceById($conferenceId);

    // Kiểm tra hội nghị tồn tại
    if (!$conference) {
        setFlashMessage('danger', 'Không tìm thấy hội nghị!');
        redirect('../conference-manager.php');
        exit;
    }

    // Kiểm tra quyền: admin có thể sửa tất cả, organizer chỉ sửa của mình
    if ($_SESSION['user_role'] === 'organizer' && $conference['created_by'] != $_SESSION['user_id']) {
        setFlashMessage('danger', 'Bạn không có quyền chỉnh sửa hội nghị này!');
        redirect('../conference-manager.php');
        exit;
    }

    // Lấy dữ liệu từ form
    $data = [
        'title' => sanitize($_POST['title']),
        'description' => sanitize($_POST['description']),
        'date' => $_POST['date'],
        'endDate' => $_POST['endDate'],
        'location' => sanitize($_POST['location']),
        'category' => sanitize($_POST['category']),
        'price' => floatval($_POST['price']),
        'capacity' => intval($_POST['capacity']),
        'status' => $_POST['status'],
        'organizer_name' => sanitize($_POST['organizer_name']),
        'organizer_email' => sanitize($_POST['organizer_email']),
        'organizer_phone' => sanitize($_POST['organizer_phone'])
    ];

    // Xử lý tải lên hình ảnh nếu có
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/';

        // Tạo thư mục uploads nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $data['image'] = 'uploads/' . $fileName;
        } else {
            setFlashMessage('warning', 'Không thể tải lên hình ảnh, chỉ cập nhật thông tin khác.');
        }
    }

    // Cập nhật thông tin
    $result = $conferenceModel->updateConference($conferenceId, $data);

    if ($result) {
        setFlashMessage('success', 'Cập nhật thông tin hội nghị thành công!');
    } else {
        setFlashMessage('danger', 'Có lỗi xảy ra khi cập nhật thông tin hội nghị!');
    }

    // Chuyển hướng về trang chỉnh sửa
    redirect("../conference-edit.php?id=$conferenceId");

} else {
    // Phương thức không được hỗ trợ hoặc thiếu tham số
    setFlashMessage('danger', 'Yêu cầu không hợp lệ!');
    redirect('../conference-manager.php');
}
?>