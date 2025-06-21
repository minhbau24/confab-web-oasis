<?php
/**
 * File chứa các chức năng xác thực người dùng
 */
require_once 'config.php';

/**
 * Kiểm tra đăng nhập
 *
 * @param string $email Email người dùng
 * @param string $password Mật khẩu
 * @param bool $remember Ghi nhớ đăng nhập
 * @return array Kết quả đăng nhập
 */
function login($email, $password, $remember = false)
{
    try {
        $db = connectDB();

        // Tìm người dùng theo email
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra mật khẩu
        if ($user && password_verify($password, $user['password'])) {
            // Lưu thông tin người dùng vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Lưu cookie nếu chọn ghi nhớ đăng nhập
            if ($remember) {
                $token = bin2hex(random_bytes(32)); // Tạo token ngẫu nhiên

                // Lưu token vào database
                $stmt = $db->prepare("UPDATE users SET remember_token = :token WHERE id = :id");
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':id', $user['id']);
                $stmt->execute();

                // Lưu token vào cookie (30 ngày)
                setcookie('auth_token', $token, time() + 30 * 24 * 60 * 60, '/', '', false, true);
            }

            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['firstName'] . ' ' . $user['lastName'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
        }

        return [
            'success' => false,
            'error' => 'Email hoặc mật khẩu không đúng.'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
        ];
    }
}

/**
 * Đăng ký người dùng mới
 *
 * @param string $firstName Họ người dùng
 * @param string $lastName Tên người dùng
 * @param string $email Email người dùng
 * @param string $password Mật khẩu
 * @param string $phone Số điện thoại (tùy chọn)
 * @param string $userType Loại người dùng (tùy chọn, mặc định là 'user')
 * @return array Kết quả đăng ký
 */
function register($firstName, $lastName, $email, $password, $phone = '', $userType = 'user')
{
    try {
        $db = connectDB();

        // Kiểm tra email đã tồn tại chưa
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Email đã được sử dụng. Vui lòng dùng email khác hoặc đăng nhập.'
            ];
        }

        // Hash mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Xác định role dựa trên userType
        $role = 'user'; // Mặc định
        if ($userType === 'organizer') {
            $role = 'organizer';
        } elseif ($userType === 'admin') {
            // Chỉ cho phép tạo admin nếu đang là admin
            if (isLoggedIn() && $_SESSION['user_role'] === 'admin') {
                $role = 'admin';
            }
        }

        // Thêm người dùng vào database
        $stmt = $db->prepare("
            INSERT INTO users (firstName, lastName, email, password, role, phone, created_at)
            VALUES (:firstName, :lastName, :email, :password, :role, :phone, NOW())
        ");
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        $userId = $db->lastInsertId();

        return [
            'success' => true,
            'message' => 'Đăng ký thành công. Vui lòng đăng nhập.',
            'user_id' => $userId,
            'user' => [
                'id' => $userId,
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'role' => $role
            ]
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
        ];
    }
}

/**
 * Đăng xuất
 */
function logout()
{
    // Xóa session
    session_unset();
    session_destroy();    
    // Xóa cookie
    setcookie('auth_token', '', time() - 3600, '/', '', false, true);

    // Chuyển hướng đến trang login HTML, sử dụng hàm redirect từ config.php để đảm bảo URL hợp lệ
    redirect('login.html');
}

/**
 * Kiểm tra quyền người dùng
 *
 * @param string $requiredRole Quyền yêu cầu
 * @return bool Người dùng có quyền không
 */
function checkPermission($requiredRole)
{
    if (!isLoggedIn()) {
        return false;
    }

    $userRole = $_SESSION['user_role'];

    switch ($userRole) {
        case 'admin':
            return true; // Admin có tất cả quyền
        case 'organizer':
            return $requiredRole !== 'admin'; // Organizer có tất cả trừ admin
        case 'user':
            return $requiredRole === 'user' || $requiredRole === 'guest';
        default:
            return $requiredRole === 'guest';
    }
}

/**
 * Kiểm tra trạng thái đăng nhập
 *
 * @return bool Đã đăng nhập chưa
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Lấy thông tin người dùng hiện tại
 *
 * @return array|null Thông tin người dùng
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

/**
 * Kiểm tra cookie và tự động đăng nhập
 */
function autoLogin()
{
    if (isLoggedIn() || !isset($_COOKIE['auth_token'])) {
        return;
    }

    try {
        $token = $_COOKIE['auth_token'];
        $db = connectDB();

        $stmt = $db->prepare("SELECT * FROM users WHERE remember_token = :token LIMIT 1");
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
        }
    } catch (PDOException $e) {
        // Log lỗi
    }
}

// Chạy autoLogin khi file được include
autoLogin();
?>