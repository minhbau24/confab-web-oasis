<?php
/**
 * Trang đăng nhập
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Kiểm tra nếu người dùng đã đăng nhập thì chuyển hướng đến trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Khai báo biến lưu thông báo lỗi
$error_message = '';

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;

    // Kiểm tra các trường bắt buộc
    if (empty($email) || empty($password)) {
        $error_message = 'Vui lòng nhập đầy đủ email và mật khẩu!';
    } else {
        // Thực hiện đăng nhập
        $login_result = login($email, $password, $remember);

        if ($login_result['success']) {
            // Đăng nhập thành công, chuyển hướng đến trang chủ
            header('Location: index.php');
            exit;
        } else {
            // Đăng nhập thất bại
            $error_message = $login_result['message'] ?? 'Email hoặc mật khẩu không chính xác!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Trung tâm Hội nghị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 55px; /* Đảm bảo nội dung không bị che khuất bởi thanh navbar fixed */
            background-color: #f8f9fa;
        }
        .login-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: calc(100vh - 55px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
            position: relative;
        }
        .login-section::before {
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
        .login-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            z-index: 2;
            position: relative;
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            padding: 30px;
            color: white;
            text-align: center;
        }
        .login-form {
            padding: 30px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .social-login {
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

<div class="login-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="login-header">
                        <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Đăng nhập</h3>
                    </div>
                    <div class="login-form">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form id="loginForm" method="post" action="login.php">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                <label for="email"><i class="fas fa-envelope me-2"></i>Địa chỉ Email</label>
                            </div>
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                <label for="password"><i class="fas fa-lock me-2"></i>Mật khẩu</label>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                                </div>
                                <a href="#" class="text-primary">Quên mật khẩu?</a>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <p>Hoặc đăng nhập với:</p>
                            <div class="social-login">
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
                            <p>Chưa có tài khoản? <a href="register.php" class="text-primary">Đăng ký ngay</a></p>
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
