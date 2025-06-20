<?php
/**
 * API cho hội nghị
 */
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Conference.php';

// Xử lý CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Khởi tạo Conference
$conference = new Conference();

// Xác định request method
$method = $_SERVER['REQUEST_METHOD'];

// Xử lý các request
switch ($method) {
    case 'GET':
        // Lấy ID từ URL nếu có
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Lấy các tham số lọc từ URL
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $dateFilter = isset($_GET['date']) ? trim($_GET['date']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 9; // Mặc định 9 hội nghị/trang

        if ($id > 0) {
            // Lấy một hội nghị cụ thể
            $data = $conference->getConferenceById($id);
            if ($data) {
                echo json_encode([
                    'status' => true,
                    'data' => $data
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Không tìm thấy hội nghị với ID: ' . $id
                ]);
            }
        } else {
            // Xem có phải là tìm kiếm không
            if (isset($_GET['search'])) {
                $query = isset($_GET['query']) ? $_GET['query'] : '';
                $category = isset($_GET['category']) ? $_GET['category'] : '';
                $location = isset($_GET['location']) ? $_GET['location'] : '';

                $data = $conference->searchConferences($query, $category, $location);

                echo json_encode([
                    'status' => true,
                    'data' => $data
                ]);
            }
            // Xem có phải lấy các hội nghị sắp tới không
            elseif (isset($_GET['upcoming'])) {
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;

                $data = $conference->getUpcomingConferences($limit);

                echo json_encode([
                    'status' => true,
                    'data' => $data
                ]);
            }            // Mặc định lấy tất cả hội nghị với các bộ lọc
            else {
                // Tính toán vị trí bắt đầu dựa trên phân trang
                $offset = ($page - 1) * $limit;

                // Nếu có các bộ lọc thì sử dụng hàm filter
                if (!empty($searchTerm) || !empty($category) || !empty($dateFilter)) {
                    $data = $conference->filterConferences($searchTerm, $category, $dateFilter, $limit, $offset);
                    $total = $conference->countFilteredConferences($searchTerm, $category, $dateFilter);
                } else {
                    $data = $conference->getAllConferences($limit, $offset);
                    $total = $conference->countAllConferences();
                }

                // Tính tổng số trang
                $totalPages = ceil($total / $limit);

                echo json_encode([
                    'status' => true,
                    'count' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'totalPages' => $totalPages,
                    'data' => $data
                ]);
            }
        }
        break;

    case 'POST':
        // Lấy dữ liệu gửi đến
        $data = json_decode(file_get_contents("php://input"), true);

        // Nếu không có dữ liệu JSON, thử lấy từ POST
        if (empty($data)) {
            $data = $_POST;
        }

        // Kiểm tra quyền (cần triển khai auth.php)
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Bạn cần đăng nhập để thực hiện thao tác này'
            ]);
            exit;
        }

        // Xử lý tải lên hình ảnh nếu có
        if (isset($_FILES['image'])) {
            $uploadDir = '../uploads/';
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $data['image'] = 'uploads/' . $fileName;
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Không thể tải lên hình ảnh'
                ]);
                exit;
            }
        }

        // Thêm người tạo
        $data['created_by'] = $_SESSION['user_id'];

        // Thêm hội nghị mới
        $id = $conference->addConference($data);

        if ($id) {
            echo json_encode([
                'status' => true,
                'message' => 'Thêm hội nghị thành công',
                'id' => $id
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi thêm hội nghị'
            ]);
        }
        break;

    case 'PUT':
        // Lấy dữ liệu gửi đến
        $data = json_decode(file_get_contents("php://input"), true);

        // Kiểm tra ID
        if (!isset($data['id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Thiếu ID hội nghị'
            ]);
            exit;
        }

        $id = $data['id'];

        // Kiểm tra quyền (cần triển khai auth.php)
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Bạn cần đăng nhập để thực hiện thao tác này'
            ]);
            exit;
        }

        // Cập nhật hội nghị
        if ($conference->updateConference($id, $data)) {
            echo json_encode([
                'status' => true,
                'message' => 'Cập nhật hội nghị thành công'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật hội nghị'
            ]);
        }
        break;

    case 'DELETE':
        // Lấy dữ liệu gửi đến
        $data = json_decode(file_get_contents("php://input"), true);

        // Kiểm tra ID
        if (!isset($data['id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Thiếu ID hội nghị'
            ]);
            exit;
        }

        $id = $data['id'];

        // Kiểm tra quyền (cần triển khai auth.php)
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Bạn cần đăng nhập để thực hiện thao tác này'
            ]);
            exit;
        }

        // Xóa hội nghị
        if ($conference->deleteConference($id)) {
            echo json_encode([
                'status' => true,
                'message' => 'Xóa hội nghị thành công'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi xóa hội nghị'
            ]);
        }
        break;

    default:
        echo json_encode([
            'status' => false,
            'message' => 'Phương thức không được hỗ trợ'
        ]);
        break;
}
?>