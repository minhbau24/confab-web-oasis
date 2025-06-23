<?php
/**
 * Confab Web Oasis - Setup Wizard
 * Script giúp thiết lập dự án lần đầu tiên
 */

// Đặt biến environment để sử dụng
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$baseApiUrl = $baseUrl . $basePath . '/api';

// Khởi tạo biến trạng thái
$setupStatus = [
    'database_connection' => [
        'status' => 'pending',
        'message' => 'Chưa kiểm tra kết nối cơ sở dữ liệu'
    ],
    'database_schema' => [
        'status' => 'pending',
        'message' => 'Chưa thiết lập cơ sở dữ liệu'
    ],
    'sample_data' => [
        'status' => 'pending',
        'message' => 'Chưa nhập dữ liệu mẫu'
    ]
];

$step = isset($_GET['step']) ? $_GET['step'] : 'welcome';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Hàm kết nối database
function checkDatabaseConnection()
{
    global $setupStatus;

    try {
        require_once 'includes/config.php';

        $dsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $setupStatus['database_connection']['status'] = 'success';
        $setupStatus['database_connection']['message'] = 'Kết nối cơ sở dữ liệu thành công';

        // Kiểm tra database đã tồn tại chưa
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        $dbExists = $stmt->fetchColumn();

        if ($dbExists) {
            $setupStatus['database_schema']['status'] = 'warning';
            $setupStatus['database_schema']['message'] = 'Cơ sở dữ liệu ' . DB_NAME . ' đã tồn tại. Bạn có thể thiết lập lại hoặc bỏ qua bước này.';

            // Kiểm tra xem các bảng đã được tạo chưa
            try {
                $tableCheck = $pdo->query("SHOW TABLES FROM `" . DB_NAME . "` LIKE 'categories'");
                if ($tableCheck->rowCount() > 0) {
                    // Cấu trúc DB đã được thiết lập trước đó
                    $setupStatus['database_schema']['status'] = 'success';
                    $setupStatus['database_schema']['message'] = 'Cơ sở dữ liệu ' . DB_NAME . ' đã tồn tại và có vẻ đã được thiết lập trước đó.';
                }
            } catch (PDOException $e) {
                // Không làm gì cả, giữ cảnh báo ban đầu
            }
        } else {
            $setupStatus['database_schema']['status'] = 'error';
            $setupStatus['database_schema']['message'] = 'Cơ sở dữ liệu ' . DB_NAME . ' không tồn tại. Vui lòng tạo cơ sở dữ liệu trong phpMyAdmin trước khi tiếp tục.';
        }

        return true;
    } catch (PDOException $e) {
        $setupStatus['database_connection']['status'] = 'error';
        $setupStatus['database_connection']['message'] = 'Lỗi kết nối: ' . $e->getMessage();
        return false;
    }
}

// Hàm kiểm tra và verify schema
function verifyDatabaseSchema() {
    global $setupStatus, $baseApiUrl;
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseApiUrl . '/verify_schema.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                if ($data['success']) {
                    $setupStatus['database_schema']['status'] = 'success';
                    $setupStatus['database_schema']['message'] = 'Schema database hoàn chỉnh (' . $data['statistics']['completion_percentage'] . '%)';
                    $setupStatus['database_schema']['data'] = $data;
                } else {
                    $setupStatus['database_schema']['status'] = 'warning';
                    $setupStatus['database_schema']['message'] = 'Schema chưa hoàn chỉnh: ' . implode(', ', $data['messages']);
                    $setupStatus['database_schema']['data'] = $data;
                }
                return true;
            }
        }
        
        $setupStatus['database_schema']['status'] = 'error';
        $setupStatus['database_schema']['message'] = 'Không thể kiểm tra schema database';
        return false;
        
    } catch (Exception $e) {
        $setupStatus['database_schema']['status'] = 'error';
        $setupStatus['database_schema']['message'] = 'Lỗi khi kiểm tra schema: ' . $e->getMessage();
        return false;
    }
}

// Xử lý action
if ($action === 'check_connection') {
    checkDatabaseConnection();
    header('Location: setup.php?step=database');
    exit;
}

// Xử lý khi thực hiện thiết lập database
if ($action === 'setup_database') {
    checkDatabaseConnection();
    $step = 'database_result';
}

// Xử lý khi thực hiện xác minh hệ thống
if ($action === 'run_verification') {
    $step = 'verification_result';
}

// Kiểm tra kết nối trước khi vào bước tiếp theo
if (in_array($step, ['database', 'database_result'])) {
    checkDatabaseConnection();
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confab Web Oasis - Thiết lập lần đầu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .step-indicator {
            display: flex;
            margin-bottom: 2rem;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 1rem;
            position: relative;
        }

        .step::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background-color: #ddd;
            top: 50%;
            left: 50%;
        }

        .step:last-child::after {
            display: none;
        }

        .step.active {
            font-weight: bold;
            color: #007bff;
        }

        .status-pending {
            color: #6c757d;
        }

        .status-success {
            color: #28a745;
        }

        .status-warning {
            color: #ffc107;
        }

        .status-error {
            color: #dc3545;
        }

        .setup-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }

        .feature-list i {
            color: #007bff;
            margin-right: 10px;
        }

        code {
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="container setup-container">
        <div class="logo">
            <h1>Confab Web Oasis</h1>
            <p class="text-muted">Hệ thống quản lý hội nghị và sự kiện</p>
        </div>

        <div class="step-indicator">
            <div class="step <?php echo in_array($step, ['welcome']) ? 'active' : ''; ?>">
                <i class="fas fa-home"></i><br>Chào mừng
            </div>
            <div class="step <?php echo in_array($step, ['database', 'database_result']) ? 'active' : ''; ?>">
                <i class="fas fa-database"></i><br>Cơ sở dữ liệu
            </div>            <div class="step <?php echo in_array($step, ['verification', 'verification_result']) ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i><br>Xác minh
            </div>
            <div class="step <?php echo $step === 'complete' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i><br>Hoàn thành
            </div>
        </div>

        <?php if ($step === 'welcome'): ?>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Chào mừng đến với Confab Web Oasis!</h2>
                    <p class="card-text">Trình thiết lập này sẽ giúp bạn cấu hình hệ thống lần đầu tiên.</p>

                    <div class="feature-list my-4">
                        <p><i class="fas fa-check-circle"></i> Kiểm tra kết nối cơ sở dữ liệu</p>
                        <p><i class="fas fa-check-circle"></i> Thiết lập cấu trúc bảng dữ liệu</p>
                        <p><i class="fas fa-check-circle"></i> Nhập dữ liệu mẫu (tùy chọn)</p>
                    </div>

                    <h4>Yêu cầu hệ thống:</h4>
                    <ul>
                        <li>PHP 7.4 hoặc cao hơn</li>
                        <li>MySQL 5.7+ / MariaDB 10.4+</li>
                        <li>PDO PHP Extension</li>
                    </ul>

                    <h4>Trước khi tiếp tục:</h4>
                    <ol>
                        <li>Đảm bảo đã tạo cơ sở dữ liệu trong MySQL</li>
                        <li>Cấu hình kết nối trong file <code>includes/config.php</code> (nếu cần)</li>
                    </ol>

                    <div class="text-end mt-4">
                        <a href="setup.php?action=check_connection" class="btn btn-primary">Kiểm tra kết nối <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        <?php elseif ($step === 'database'): ?>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Thiết lập cơ sở dữ liệu</h2>

                    <!-- Hiển thị trạng thái kết nối -->
                    <div
                        class="alert alert-<?php echo $setupStatus['database_connection']['status'] === 'success' ? 'success' : ($setupStatus['database_connection']['status'] === 'error' ? 'danger' : 'warning'); ?>">
                        <?php if ($setupStatus['database_connection']['status'] === 'success'): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php elseif ($setupStatus['database_connection']['status'] === 'error'): ?>
                            <i class="fas fa-exclamation-triangle"></i>
                        <?php else: ?>
                            <i class="fas fa-info-circle"></i>
                        <?php endif; ?>
                        <?php echo $setupStatus['database_connection']['message']; ?>
                    </div>
                    <?php if ($setupStatus['database_connection']['status'] === 'success'): ?>
                        <!-- Hiển thị thông tin cơ sở dữ liệu -->
                        <div
                            class="alert alert-<?php echo $setupStatus['database_schema']['status'] === 'success' ? 'success' : ($setupStatus['database_schema']['status'] === 'error' ? 'danger' : 'info'); ?>">
                            <?php echo $setupStatus['database_schema']['message']; ?>
                        </div>                        <?php if ($setupStatus['database_schema']['status'] === 'error'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> <strong>Hành động cần thiết:</strong>
                                <ol>
                                    <li>Mở phpMyAdmin: <a href="http://localhost/phpmyadmin"
                                            target="_blank">http://localhost/phpmyadmin</a></li>
                                    <li>Tạo cơ sở dữ liệu mới tên <code><?php echo DB_NAME; ?></code></li>
                                    <li>Thiết lập bảng mã <code>utf8mb4_unicode_ci</code></li>
                                    <li>Nhấn "Thiết lập Schema" bên dưới</li>
                                </ol>
                            </div>
                            
                            <div class="mb-3">
                                <button onclick="setupDatabase()" id="setupDatabaseBtn" class="btn btn-success me-2">
                                    <i class="fas fa-database"></i> Thiết lập Schema Hoàn Chỉnh
                                </button>
                                <button onclick="verifySchema()" id="verifySchemaBtn" class="btn btn-info">
                                    <i class="fas fa-search"></i> Kiểm tra Schema
                                </button>
                            </div>
                            
                            <div id="databaseSetupResult"></div>
                            <div id="schemaVerifyResult"></div>                            <div id="continueAfterSetup" style="display: none;" class="mt-3">
                                <a href="setup.php?step=verification" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> Tiếp tục
                                </a>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="setup.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                                <a href="setup.php?action=check_connection" class="btn btn-outline-primary">Kiểm tra lại <i
                                        class="fas fa-sync"></i></a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Schema database hoàn chỉnh</strong> với các tính năng nâng cao:
                                <ul class="mt-2 mb-0">
                                    <li>Quản lý người dùng nâng cao (password history, sessions, activity logs)</li>
                                    <li>Hệ thống hội nghị đầy đủ (venues, speakers, schedule, registrations)</li>
                                    <li>Đa ngôn ngữ (i18n) và media management</li>
                                    <li>Payment system và billing</li>
                                    <li>Security logs và audit trails</li>
                                    <li>Scheduled tasks và notifications</li>
                                </ul>
                            </div>
                            
                            <div class="mb-3">
                                <button onclick="verifySchema()" id="verifySchemaBtn" class="btn btn-info me-2">
                                    <i class="fas fa-search"></i> Kiểm tra Schema
                                </button>
                                <button onclick="setupDatabase()" id="setupDatabaseBtn" class="btn btn-outline-success">
                                    <i class="fas fa-redo"></i> Thiết lập lại Schema
                                </button>
                            </div>
                            
                            <div id="schemaVerifyResult"></div>
                            <div id="databaseSetupResult"></div>                            <div class="d-flex justify-content-between mt-4">
                                <a href="setup.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                                <a href="setup.php?step=verification" class="btn btn-primary">Tiếp tục <i class="fas fa-arrow-right"></i></a>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <p>Vui lòng kiểm tra lại thông tin kết nối trong file <code>includes/config.php</code> và thử lại.</p>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="setup.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                            <a href="setup.php?action=check_connection" class="btn btn-primary">Kiểm tra lại <i
                                    class="fas fa-sync"></i></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($step === 'database_result'): ?>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Thiết lập cơ sở dữ liệu</h2>

                    <div id="setupResult">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Đang thiết lập cơ sở dữ liệu, vui lòng đợi...</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4" id="resultButtons" style="display: none !important;">
                        <a href="setup.php?step=database" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay
                            lại</a>                        <a href="setup.php?step=verification" class="btn btn-primary">Tiếp theo: Xác minh thiết lập <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Gọi API thiết lập cơ sở dữ liệu
                    fetch('api/setup_database.php')
                        .then(response => response.json())
                        .then(data => {
                            const resultDiv = document.getElementById('setupResult');
                            let alertClass = data.success ? 'success' : 'danger';
                            let icon = data.success ? 'check-circle' : 'exclamation-triangle';

                            let resultHtml = `<div class="alert alert-${alertClass}">
                                <i class="fas fa-${icon}"></i> <strong>Kết quả:</strong>
                            </div>`;

                            // Hiện thị các thông báo
                            if (data.messages && data.messages.length) {
                                resultHtml += '<div class="mt-3"><h5>Chi tiết:</h5>';
                                resultHtml += '<div class="border p-3 bg-light" style="max-height: 300px; overflow-y: auto;">';
                                data.messages.forEach(msg => {
                                    resultHtml += `<p>${msg}</p>`;
                                });
                                resultHtml += '</div></div>';
                            }

                            resultDiv.innerHTML = resultHtml;
                            document.getElementById('resultButtons').style.display = 'flex';
                        })
                        .catch(error => {
                            document.getElementById('setupResult').innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Lỗi khi gọi API: ${error.message}
                                </div>
                                <p>Vui lòng kiểm tra lại cài đặt PHP và MySQL.</p>
                            `;
                            document.getElementById('resultButtons').style.display = 'flex';
                        });
                });
            </script>        <?php elseif ($step === 'verification'): ?>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Xác minh thiết lập</h2>

                    <p>Bước này sẽ kiểm tra tính toàn vẹn của hệ thống sau khi thiết lập:</p>
                    <ul>
                        <li>✅ Cấu trúc cơ sở dữ liệu hoàn chỉnh</li>
                        <li>✅ Dữ liệu mẫu đã được tạo</li>
                        <li>✅ Tài khoản quản trị đã sẵn sàng</li>
                        <li>✅ Cấu hình hệ thống đã được thiết lập</li>
                        <li>✅ API endpoints hoạt động bình thường</li>
                    </ul>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Quá trình xác minh sẽ kiểm tra toàn bộ hệ thống và đảm bảo
                        mọi thứ hoạt động chính xác trước khi bạn bắt đầu sử dụng.
                    </div>

                    <div class="mb-3">
                        <button onclick="runVerification()" id="verificationBtn" class="btn btn-primary">
                            <i class="fas fa-search"></i> Bắt đầu xác minh
                        </button>
                    </div>
                    
                    <div id="verificationResult"></div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="setup.php?step=database" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay
                            lại</a>
                        <div>
                            <a href="setup.php?step=complete" class="btn btn-outline-secondary me-2">Bỏ qua <i
                                    class="fas fa-forward"></i></a>
                            <a href="setup.php?step=complete" class="btn btn-success" id="proceedBtn" style="display: none;">
                                Hoàn thành thiết lập <i class="fas fa-check"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>        <?php elseif ($step === 'complete'): ?>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center"><i class="fas fa-check-circle text-success"></i> Thiết lập hoàn tất!
                    </h2>

                    <div class="alert alert-success text-center">
                        <p class="mb-0"><strong>Chúc mừng!</strong> Bạn đã thiết lập thành công Confab Web Oasis.</p>
                    </div>

                    <div class="row mt-5">
                        <div class="col-md-6">
                            <div class="card mb-3">                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-user-shield"></i> Truy cập quản trị</h5>
                                    <p>Quản lý hội nghị, người dùng, danh mục...</p>
                                    <a href="admin.html" class="btn btn-primary">Trang quản trị</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-home"></i> Trang người dùng</h5>
                                    <p>Trải nghiệm giao diện người dùng</p>
                                    <a href="index.html" class="btn btn-outline-primary">Trang chủ</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5><i class="fas fa-key"></i> Thông tin đăng nhập</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Loại tài khoản</th>
                                            <th>Email</th>
                                            <th>Mật khẩu</th>
                                        </tr>
                                    </thead>                                    <tbody>
                                        <tr>
                                            <td>Quản trị viên</td>
                                            <td>admin@confab.local</td>
                                            <td>password</td>
                                        </tr>
                                        <tr>
                                            <td>Tổ chức sự kiện</td>
                                            <td>organizer@confab.local</td>
                                            <td>password</td>
                                        </tr>
                                        <tr>
                                            <td>Diễn giả</td>
                                            <td>speaker@confab.local</td>
                                            <td>password</td>
                                        </tr>
                                        <tr>
                                            <td>Người dùng</td>
                                            <td>user@confab.local</td>
                                            <td>password</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Lưu ý bảo mật:</strong> Khi triển khai hệ thống
                        vào môi trường thực tế, hãy nhớ thay đổi mật khẩu mặc định.
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <p class="text-muted small">Confab Web Oasis &copy; 2025</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hàm setup database với schema hoàn chỉnh
                function setupDatabase() {
                    const setupBtn = document.getElementById('setupDatabaseBtn');
                    const resultDiv = document.getElementById('databaseSetupResult');
                    
                    if (setupBtn) {
                        setupBtn.disabled = true;
                        setupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thiết lập...';
                    }
                    
                    resultDiv.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-database"></i> <strong>Đang thiết lập cơ sở dữ liệu...</strong>
                            <div class="progress mt-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    `;
                    
                    fetch('<?php echo $baseApiUrl; ?>/setup_database.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        let resultHtml = '';
                        
                        if (data.success) {
                            resultHtml = `
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <strong>Thiết lập thành công!</strong>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Chi tiết thiết lập:</h6>
                                        <ul class="list-unstyled">
                            `;
                            
                            data.messages.forEach(message => {
                                resultHtml += `<li><i class="fas fa-check text-success"></i> ${message}</li>`;
                            });
                            
                            if (data.data && data.data.existing_tables) {
                                resultHtml += `<li><i class="fas fa-table text-info"></i> Đã tạo ${data.data.tables_count} bảng database</li>`;
                            }
                            
                            resultHtml += `
                                        </ul>
                                    </div>
                                </div>
                            `;
                        } else {
                            resultHtml = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Có lỗi xảy ra!</strong>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Chi tiết lỗi:</h6>
                                        <ul class="list-unstyled">
                            `;
                            
                            data.messages.forEach(message => {
                                const isError = message.includes('Lỗi') || message.includes('❌');
                                const icon = isError ? 'fas fa-times text-danger' : 'fas fa-info-circle text-info';
                                resultHtml += `<li><i class="${icon}"></i> ${message}</li>`;
                            });
                            
                            resultHtml += `
                                        </ul>
                                    </div>
                                </div>
                            `;
                        }
                          // Verbose execution log removed to prevent screen overflow
                        // Details are still available in the API response if needed for debugging
                        
                        resultDiv.innerHTML = resultHtml;
                        
                        if (setupBtn) {
                            setupBtn.disabled = false;
                            setupBtn.innerHTML = data.success ? 
                                '<i class="fas fa-check"></i> Thiết lập thành công' : 
                                '<i class="fas fa-redo"></i> Thử lại';
                        }
                        
                        // Nếu thành công, hiển thị nút tiếp tục
                        if (data.success) {
                            const continueBtn = document.getElementById('continueAfterSetup');
                            if (continueBtn) {
                                continueBtn.style.display = 'block';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Lỗi kết nối API:</strong> ${error.message}
                            </div>
                        `;
                        
                        if (setupBtn) {
                            setupBtn.disabled = false;
                            setupBtn.innerHTML = '<i class="fas fa-redo"></i> Thử lại';
                        }
                    });                }
                
                // Hàm chạy xác minh hệ thống
                function runVerification() {
                    const verifyBtn = document.getElementById('verificationBtn');
                    const resultDiv = document.getElementById('verificationResult');
                    const proceedBtn = document.getElementById('proceedBtn');
                    
                    if (verifyBtn) {
                        verifyBtn.disabled = true;
                        verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xác minh...';
                    }
                    
                    resultDiv.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-search"></i> <strong>Đang xác minh tính toàn vẹn hệ thống...</strong>
                            <div class="progress mt-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    `;
                    
                    // Kiểm tra schema trước
                    fetch('<?php echo $baseApiUrl; ?>/verify_schema.php')
                    .then(response => response.json())
                    .then(data => {
                        let resultHtml = '';
                        
                        if (data.success) {
                            resultHtml = `
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <strong>Xác minh thành công!</strong>
                                    <p class="mt-2 mb-0">Hệ thống đã được thiết lập hoàn chỉnh và sẵn sàng sử dụng.</p>
                                </div>
                            `;
                            
                            // Hiển thị thống kê
                            if (data.statistics) {
                                resultHtml += `
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6>Thống kê hệ thống:</h6>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="h4 text-primary">${data.statistics.existing_tables}/${data.statistics.total_tables}</div>
                                                        <small>Bảng database</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="h4 text-success">${data.statistics.completion_percentage}%</div>
                                                        <small>Hoàn thiện</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="h4 text-info">${data.statistics.stored_procedures}</div>
                                                        <small>Procedures</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="h4 text-warning">${data.statistics.triggers}</div>
                                                        <small>Triggers</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                            
                            // Hiển thị nút tiếp tục
                            if (proceedBtn) {
                                proceedBtn.style.display = 'inline-block';
                            }
                        } else {
                            resultHtml = `
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Cần xem xét</strong>
                                    <p class="mt-2 mb-0">Một số thành phần có thể chưa được thiết lập đầy đủ.</p>
                                </div>
                            `;
                        }
                        
                        resultDiv.innerHTML = resultHtml;
                        
                        if (verifyBtn) {
                            verifyBtn.disabled = false;
                            verifyBtn.innerHTML = '<i class="fas fa-search"></i> Xác minh lại';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Lỗi xác minh:</strong> ${error.message}
                            </div>
                        `;
                        
                        if (verifyBtn) {
                            verifyBtn.disabled = false;
                            verifyBtn.innerHTML = '<i class="fas fa-redo"></i> Thử lại';
                        }
                    });
                }
                
                // Hàm verify schema
                function verifySchema() {
                    const verifyBtn = document.getElementById('verifySchemaBtn');
                    const resultDiv = document.getElementById('schemaVerifyResult');
                    
                    if (verifyBtn) {
                        verifyBtn.disabled = true;
                        verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';
                    }
                    
                    resultDiv.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-search"></i> <strong>Đang kiểm tra tính toàn vẹn schema...</strong>
                        </div>
                    `;
                    
                    fetch('<?php echo $baseApiUrl; ?>/verify_schema.php')
                    .then(response => response.json())
                    .then(data => {
                        let resultHtml = '';
                        
                        if (data.success) {
                            resultHtml = `
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <strong>Schema database hoàn chỉnh!</strong>
                                </div>
                            `;
                        } else {
                            resultHtml = `
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Schema chưa hoàn chỉnh</strong>
                                </div>
                            `;
                        }
                        
                        // Hiển thị thống kê
                        if (data.statistics) {
                            resultHtml += `
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Thống kê Schema:</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="h4 text-primary">${data.statistics.existing_tables}/${data.statistics.total_tables}</div>
                                                    <small>Bảng</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="h4 text-info">${data.statistics.stored_procedures}</div>
                                                    <small>Procedures</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="h4 text-success">${data.statistics.triggers}</div>
                                                    <small>Triggers</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="h4 text-warning">${data.statistics.completion_percentage}%</div>
                                                    <small>Hoàn thành</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        // Hiển thị chi tiết bảng
                        if (data.data && data.data.tables) {
                            const tables = Object.entries(data.data.tables);
                            const existingTables = tables.filter(([name, info]) => info.exists);
                            const missingTables = tables.filter(([name, info]) => !info.exists);
                            
                            if (existingTables.length > 0) {
                                resultHtml += `
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6 class="text-success">Bảng đã tạo (${existingTables.length}):</h6>
                                            <div class="row">
                                `;
                                
                                existingTables.forEach(([name, info]) => {
                                    resultHtml += `
                                        <div class="col-md-4">
                                            <div class="small mb-1">
                                                <i class="fas fa-table text-success"></i> <strong>${name}</strong>
                                                <br><small class="text-muted">${info.columns} cột, ${info.rows} dòng</small>
                                            </div>
                                        </div>
                                    `;
                                });
                                
                                resultHtml += `
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                            
                            if (missingTables.length > 0) {
                                resultHtml += `
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6 class="text-danger">Bảng thiếu (${missingTables.length}):</h6>
                                            <div class="row">
                                `;
                                
                                missingTables.forEach(([name, info]) => {
                                    resultHtml += `
                                        <div class="col-md-4">
                                            <div class="small mb-1">
                                                <i class="fas fa-times text-danger"></i> ${name}
                                            </div>
                                        </div>
                                    `;
                                });
                                
                                resultHtml += `
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                        }
                        
                        resultDiv.innerHTML = resultHtml;
                        
                        if (verifyBtn) {
                            verifyBtn.disabled = false;
                            verifyBtn.innerHTML = '<i class="fas fa-search"></i> Kiểm tra lại';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Lỗi kết nối API:</strong> ${error.message}
                            </div>
                        `;
                        
                        if (verifyBtn) {
                            verifyBtn.disabled = false;
                            verifyBtn.innerHTML = '<i class="fas fa-redo"></i> Thử lại';
                        }
                    });
                }
    </script>
</body>

</html>