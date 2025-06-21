<?php
/**
 * Confab Web Oasis - Database Setup Script
 * Tự động setup database với schema hoàn chỉnh
 */

// Cấu hình database
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'confab_db',
    'charset' => 'utf8mb4'
];

// Các file SQL cần chạy theo thứ tự
$sqlFiles = [
    'schema_complete.sql' => 'Tạo cấu trúc database hoàn chỉnh',
    'sample_data.sql' => 'Thêm dữ liệu mẫu (tùy chọn)'
];

function log_message($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] [$type] $message\n";
}

function create_connection($config, $database = null) {
    try {
        $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
        if ($database) {
            $dsn .= ";dbname=$database";
        }
        
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        log_message("Lỗi kết nối database: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

function execute_sql_file($pdo, $filepath, $description) {
    if (!file_exists($filepath)) {
        log_message("File không tồn tại: $filepath", 'ERROR');
        return false;
    }
    
    log_message("Bắt đầu: $description", 'INFO');
    
    $sql = file_get_contents($filepath);
    
    // Tách các câu lệnh SQL
    $statements = preg_split('/;\s*$/m', $sql);
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Bỏ qua comments và câu lệnh rỗng
        if (empty($statement) || 
            strpos($statement, '--') === 0 || 
            strpos($statement, '/*') === 0 ||
            strtoupper(substr($statement, 0, 9)) === 'DELIMITER') {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Bỏ qua một số lỗi không quan trọng
            if (strpos($e->getMessage(), 'already exists') === false &&
                strpos($e->getMessage(), 'Duplicate') === false) {
                log_message("Lỗi SQL: " . $e->getMessage(), 'WARNING');
                log_message("Statement: " . substr($statement, 0, 100) . "...", 'DEBUG');
                $errors++;
            }
        }
    }
    
    log_message("Hoàn thành: $description ($executed câu lệnh, $errors lỗi)", 'SUCCESS');
    return $errors === 0;
}

function check_database_exists($pdo, $database) {
    try {
        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$database]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

function get_table_count($pdo, $database) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?");
        $stmt->execute([$database]);
        $result = $stmt->fetch();
        return $result['count'];
    } catch (PDOException $e) {
        return 0;
    }
}

function show_setup_status($pdo, $database) {
    log_message("=== TRẠNG THÁI DATABASE ===", 'INFO');
    
    $tableCount = get_table_count($pdo, $database);
    log_message("Số bảng: $tableCount", 'INFO');
    
    if ($tableCount > 0) {
        try {
            // Kiểm tra một số bảng chính
            $mainTables = ['users', 'conferences', 'registrations', 'categories', 'venues', 'speakers'];
            
            foreach ($mainTables as $table) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM `$table`");
                $stmt->execute();
                $result = $stmt->fetch();
                log_message("Bảng $table: {$result['count']} records", 'INFO');
            }
        } catch (PDOException $e) {
            log_message("Không thể kiểm tra dữ liệu bảng: " . $e->getMessage(), 'WARNING');
        }
    }
    
    log_message("=== KẾT THÚC TRẠNG THÁI ===", 'INFO');
}

function create_sample_admin_user($pdo) {
    try {
        // Kiểm tra xem đã có admin chưa
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (firstName, lastName, email, password, role, status, email_verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute(['Admin', 'System', 'admin@confab.local', $password, 'admin', 'active', 1]);
            
            log_message("Đã tạo tài khoản admin mặc định:", 'SUCCESS');
            log_message("Email: admin@confab.local", 'INFO');
            log_message("Password: admin123", 'INFO');
            log_message("Vui lòng đổi mật khẩu sau khi đăng nhập!", 'WARNING');
        } else {
            log_message("Đã có tài khoản admin trong hệ thống", 'INFO');
        }
    } catch (PDOException $e) {
        log_message("Lỗi tạo tài khoản admin: " . $e->getMessage(), 'ERROR');
    }
}

// Main execution
echo "=======================================================\n";
echo "   CONFAB WEB OASIS - DATABASE SETUP SCRIPT\n";
echo "=======================================================\n\n";

log_message("Bắt đầu quá trình setup database", 'INFO');

// 1. Kết nối database (không chọn database cụ thể)
log_message("Đang kết nối MySQL server...", 'INFO');
$pdo = create_connection($config);

if (!$pdo) {
    log_message("Không thể kết nối MySQL server. Vui lòng kiểm tra cấu hình.", 'ERROR');
    exit(1);
}

log_message("Kết nối MySQL server thành công", 'SUCCESS');

// 2. Kiểm tra và tạo database
if (!check_database_exists($pdo, $config['database'])) {
    log_message("Database {$config['database']} chưa tồn tại, đang tạo...", 'INFO');
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        log_message("Tạo database {$config['database']} thành công", 'SUCCESS');
    } catch (PDOException $e) {
        log_message("Lỗi tạo database: " . $e->getMessage(), 'ERROR');
        exit(1);
    }
} else {
    log_message("Database {$config['database']} đã tồn tại", 'INFO');
}

// 3. Kết nối với database cụ thể
$pdo = create_connection($config, $config['database']);
if (!$pdo) {
    log_message("Không thể kết nối database {$config['database']}", 'ERROR');
    exit(1);
}

// 4. Kiểm tra trạng thái hiện tại
$currentTableCount = get_table_count($pdo, $config['database']);
log_message("Database hiện có $currentTableCount bảng", 'INFO');

if ($currentTableCount > 0) {
    echo "\nDatabase đã có dữ liệu. Bạn có muốn:\n";
    echo "1. Tiếp tục (có thể ghi đè dữ liệu hiện có)\n";
    echo "2. Thoát\n";
    echo "Lựa chọn (1-2): ";
    
    $choice = trim(fgets(STDIN));
    if ($choice !== '1') {
        log_message("Hủy bỏ quá trình setup", 'INFO');
        exit(0);
    }
}

// 5. Chạy các file SQL
$sqlDir = __DIR__;

foreach ($sqlFiles as $filename => $description) {
    $filepath = $sqlDir . '/' . $filename;
    
    if ($filename === 'sample_data.sql') {
        echo "\nBạn có muốn thêm dữ liệu mẫu không? (y/n): ";
        $choice = trim(fgets(STDIN));
        if (strtolower($choice) !== 'y') {
            log_message("Bỏ qua thêm dữ liệu mẫu", 'INFO');
            continue;
        }
    }
    
    execute_sql_file($pdo, $filepath, $description);
}

// 6. Tạo tài khoản admin mặc định
create_sample_admin_user($pdo);

// 7. Hiển thị trạng thái cuối cùng
echo "\n";
show_setup_status($pdo, $config['database']);

echo "\n=======================================================\n";
echo "   SETUP HOÀN THÀNH!\n";
echo "=======================================================\n";

log_message("Quá trình setup database hoàn tất", 'SUCCESS');
log_message("Bạn có thể bắt đầu sử dụng hệ thống", 'INFO');

echo "\nNext steps:\n";
echo "1. Cập nhật file config.php với thông tin database\n";
echo "2. Đăng nhập với tài khoản admin đã tạo\n";
echo "3. Đổi mật khẩu admin và cập nhật thông tin\n";
echo "4. Tạo các danh mục và địa điểm cho hội nghị\n";
echo "5. Bắt đầu tạo hội nghị đầu tiên!\n\n";
?>
