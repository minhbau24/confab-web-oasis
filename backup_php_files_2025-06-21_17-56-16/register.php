<?php
/**
 * Trang đăng ký
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Kiểm tra nếu người dùng đã đăng nhập thì chuyển hướng đến trang chủ HTML
if (isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Khai báo biến lưu thông báo lỗi và thành công
$error_message = '';
$success_message = '';

// Xử lý form đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
    
    // Kiểm tra các trường bắt buộc
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    }
    // Kiểm tra định dạng email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Địa chỉ email không hợp lệ!';
    }
    // Kiểm tra mật khẩu trùng khớp
    elseif ($password !== $confirmPassword) {
        $error_message = 'Mật khẩu xác nhận không khớp!';
    }
    // Kiểm tra độ dài mật khẩu
    elseif (strlen($password) < 6) {
        $error_message = 'Mật khẩu phải có ít nhất 6 ký tự!';
    }
    else {
        // Thực hiện đăng ký
        $register_result = register($firstName, $lastName, $email, $password, $phone);
        
        if ($register_result['success']) {
            // Đăng ký thành công
            $success_message = 'Đăng ký tài khoản thành công! Vui lòng đăng nhập để tiếp tục.';
            // Có thể tự động đăng nhập sau khi đăng ký
            // $_SESSION['user_id'] = $register_result['user_id'];
            // $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            // $_SESSION['user_email'] = $email;
            // $_SESSION['user_role'] = 'user';
            // header('Location: index.php');
            // exit;
        } else {
            // Đăng ký thất bại
            $error_message = $register_result['message'] ?? 'Đã xảy ra lỗi khi đăng ký tài khoản!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Trung tâm Hội nghị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 55px; /* Đảm bảo nội dung không bị che khuất bởi thanh navbar fixed */
            background-color: #f8f9fa;
        }
        .register-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: calc(100vh - 55px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
            position: relative;
        }
        .register-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&h=800&fit=crop') center/cover;
            opacity: 0.1;
            z-index: 1;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            z-index: 2;
            position: relative;
            width: 100%;
            max-width: 600px;
        }
        .register-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            padding: 30px;
            color: white;
            text-align: center;
        }
        .register-form {
            padding: 30px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .social-register {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 10px;
            font-size: 18px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        .social-btn:hover {
            transform: translateY(-3px);
        }
        .btn-facebook { background-color: #3b5998; }
        .btn-google { background-color: #dd4b39; }
        .btn-linkedin { background-color: #0077b5; }
    </style>
</head>
<body>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<div class="register-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="register-card">
                    <div class="register-header">
                        <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản</h3>
                    </div>
                    <div class="register-form">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                                <p class="mt-2 mb-0"><a href="login.php" class="alert-link">Đăng nhập ngay</a></p>
                            </div>
                        <?php endif; ?>

                        <form id="registerForm" method="post" action="register.php" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Nguyễn" required>
                                        <label for="firstName"><i class="fas fa-user me-2"></i>Họ</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Văn A" required>
                                        <label for="lastName"><i class="fas fa-user me-2"></i>Tên</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                <label for="email"><i class="fas fa-envelope me-2"></i>Địa chỉ Email</label>
                            </div>
                            
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="0912345678">
                                <label for="phone"><i class="fas fa-phone me-2"></i>Số điện thoại (tùy chọn)</label>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                <label for="password"><i class="fas fa-lock me-2"></i>Mật khẩu</label>
                                <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                                <label for="confirmPassword"><i class="fas fa-lock me-2"></i>Xác nhận mật khẩu</label>
                            </div>
                            
                            <div class="form-check mb-4 mt-2">
                                <input class="form-check-input" type="checkbox" id="agree" name="agree" required>
                                <label class="form-check-label" for="agree">
                                    Tôi đồng ý với các <a href="#" class="text-primary">Điều khoản sử dụng</a> và <a href="#" class="text-primary">Chính sách bảo mật</a>
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <p>Hoặc đăng ký với:</p>
                            <div class="social-register">
                                <a href="#" class="social-btn btn-facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-btn btn-google">
                                    <i class="fab fa-google"></i>
                                </a>
                                <a href="#" class="social-btn btn-linkedin">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p>Đã có tài khoản? <a href="login.php" class="text-success">Đăng nhập ngay</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
