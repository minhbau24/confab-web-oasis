<?php
/**
 * API cho quản lý người dùng
 */
// Xử lý CORS và set headers trước tiên
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Khởi tạo User
$user = new User();

// Xác định request method
$method = $_SERVER['REQUEST_METHOD'];

// Xử lý các request
switch ($method) {
    case 'GET':
        // Lấy ID từ URL nếu có
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Chỉ admin hoặc chính người dùng mới có quyền xem thông tin chi tiết
        if ($id > 0) {
            if (!isLoggedIn() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] !== $id)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Không có quyền truy cập!'
                ]);
                exit;
            }

            // Lấy thông tin người dùng
            $userData = $user->getUserById($id);

            if ($userData) {
                // Lấy thêm thông tin hội nghị đã tham gia
                $joinedConferences = $user->getJoinedConferences($id);
                $userData['joinedConferences'] = $joinedConferences;
                $userData['stats'] = [
                    'conferencesJoined' => count($joinedConferences)
                ];

                echo json_encode([
                    'status' => true,
                    'data' => $userData
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Không tìm thấy người dùng với ID: ' . $id
                ]);
            }
        }
        // Admin có thể lấy danh sách người dùng
        else {
            if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
                echo json_encode([
                    'status' => false,
                    'message' => 'Không có quyền truy cập!'
                ]);
                exit;
            }

            $users = $user->getAllUsers();
            echo json_encode([
                'status' => true,
                'data' => $users
            ]);
        }
        break;

    case 'POST':
        // Chỉ admin mới có quyền thêm người dùng
        if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'status' => false,
                'message' => 'Không có quyền thực hiện thao tác này!'
            ]);
            exit;
        }

        // Lấy dữ liệu gửi đến
        $data = json_decode(file_get_contents("php://input"), true);

        // Nếu không có dữ liệu JSON, thử lấy từ POST
        if (empty($data)) {
            $data = $_POST;
        }

        // Kiểm tra dữ liệu
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Thiếu thông tin bắt buộc!'
            ]);
            exit;
        }

        // Thêm người dùng mới
        $id = $user->addUser($data);

        if ($id) {
            echo json_encode([
                'status' => true,
                'message' => 'Thêm người dùng thành công',
                'id' => $id
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi thêm người dùng hoặc email đã tồn tại'
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
                'message' => 'Thiếu ID người dùng'
            ]);
            exit;
        }

        $id = $data['id'];

        // Kiểm tra quyền: admin hoặc chính người dùng
        if (!isLoggedIn() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] !== $id)) {
            echo json_encode([
                'status' => false,
                'message' => 'Không có quyền thực hiện thao tác này!'
            ]);
            exit;
        }

        // Cập nhật thông tin người dùng
        if ($user->updateUser($id, $data)) {
            echo json_encode([
                'status' => true,
                'message' => 'Cập nhật thông tin người dùng thành công'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thông tin người dùng'
            ]);
        }
        break;

    case 'DELETE':
        // Chỉ admin mới có quyền xóa người dùng
        if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'status' => false,
                'message' => 'Không có quyền thực hiện thao tác này!'
            ]);
            exit;
        }

        // Lấy dữ liệu gửi đến
        $data = json_decode(file_get_contents("php://input"), true);

        // Kiểm tra ID
        if (!isset($data['id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Thiếu ID người dùng'
            ]);
            exit;
        }

        $id = $data['id'];

        // Không cho phép xóa tài khoản admin đang đăng nhập
        if ($id === $_SESSION['user_id']) {
            echo json_encode([
                'status' => false,
                'message' => 'Không thể xóa tài khoản đang sử dụng!'
            ]);
            exit;
        }

        // Xóa người dùng
        if ($user->deleteUser($id)) {
            echo json_encode([
                'status' => true,
                'message' => 'Xóa người dùng thành công'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi xóa người dùng'
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