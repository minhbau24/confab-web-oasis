<?php
/**
 * Confab Web Oasis - Database Setup API
 * Setup database với schema hoàn chỉnh từ schema_complete.sql
 * Phiên bản: 3.0 (Complete Edition)
 */

// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

// Set content type for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once dirname(__DIR__) . '/includes/config.php';

$result = [
    'success' => false,
    'messages' => [],
    'data' => null,
    'steps' => []
];

/**
 * Parse và thực thi file SQL hoàn chỉnh
 */
function executeSQLFile($conn, $filePath) {
    global $result;
    
    if (!file_exists($filePath)) {
        throw new Exception("File schema không tìm thấy: $filePath");
    }
    
    $result['messages'][] = "Đang đọc file schema: " . basename($filePath);
    $sql = file_get_contents($filePath);
    
    if (empty($sql)) {
        throw new Exception("File schema rỗng hoặc không thể đọc được");
    }
    
    // Loại bỏ các dòng comment và dòng trống
    $lines = explode("\n", $sql);
    $cleanedLines = [];
    $inComment = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines
        if (empty($line)) continue;
        
        // Skip single line comments
        if (strpos($line, '--') === 0) continue;
        
        // Handle multi-line comments
        if (strpos($line, '/*') !== false) {
            $inComment = true;
        }
        if ($inComment) {
            if (strpos($line, '*/') !== false) {
                $inComment = false;
            }
            continue;
        }
        
        // Skip CREATE DATABASE and USE statements - we're already connected
        if (stripos($line, 'CREATE DATABASE') === 0 || stripos($line, 'USE ') === 0) {
            continue;
        }
        
        $cleanedLines[] = $line;
    }
    
    $cleanedSQL = implode("\n", $cleanedLines);
    
    // Split SQL into individual statements
    $statements = [];
    $currentStatement = '';
    $delimiter = ';';
    $inDelimiterBlock = false;
    
    $lines = explode("\n", $cleanedSQL);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Handle DELIMITER changes
        if (stripos($line, 'DELIMITER') === 0) {
            if (!$inDelimiterBlock) {
                $delimiter = trim(substr($line, 9));
                $inDelimiterBlock = true;
            } else {
                $delimiter = ';';
                $inDelimiterBlock = false;
            }
            continue;
        }
        
        $currentStatement .= $line . "\n";
        
        // Check if statement ends with current delimiter
        if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
            // Remove the delimiter from the end
            $currentStatement = substr($currentStatement, 0, -strlen($delimiter)-1);
            $currentStatement = trim($currentStatement);
            
            if (!empty($currentStatement)) {
                $statements[] = $currentStatement;
            }
            $currentStatement = '';
        }
    }
    
    // Add remaining statement if any
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    $result['messages'][] = "Tìm thấy " . count($statements) . " câu lệnh SQL để thực thi";
    
    // Execute each statement
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        if (empty($statement)) continue;
        
        try {
            // Log what we're executing (first 100 chars)
            $preview = substr(str_replace(["\n", "\r", "\t"], " ", $statement), 0, 100) . "...";
            $result['steps'][] = "Thực thi: " . $preview;
            
            $conn->exec($statement);
            $executed++;
            
        } catch (PDOException $e) {
            $errors++;
            $errorMsg = "Lỗi câu lệnh " . ($index + 1) . ": " . $e->getMessage();
            $result['messages'][] = $errorMsg;
            
            // Log the problematic statement for debugging
            $result['steps'][] = "Câu lệnh lỗi: " . substr($statement, 0, 200) . "...";
            
            // Don't stop on errors, continue with next statement
            continue;
        }
    }
    
    $result['messages'][] = "Hoàn thành: $executed câu lệnh thành công, $errors lỗi";
    
    return $errors === 0;
}

/**
 * Kiểm tra và import sample data nếu cần
 */
function importSampleDataIfNeeded($conn) {
    global $result;
    
    // Check if users table has data
    try {
        $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        
        if ($userCount == 0) {
            $result['messages'][] = 'Bảng users trống, đang thêm dữ liệu mẫu...';
            
            // Add sample users
            $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
            
            $conn->exec("
                INSERT INTO users (firstName, lastName, email, password, role, status, email_verified, created_at)
                VALUES 
                    ('Admin', 'System', 'admin@confab.local', '$hashedPassword', 'admin', 'active', 1, NOW()),
                    ('Nguyễn', 'Tổ Chức', 'organizer@confab.local', '$hashedPassword', 'organizer', 'active', 1, NOW()),
                    ('Trần', 'Diễn Giả', 'speaker@confab.local', '$hashedPassword', 'speaker', 'active', 1, NOW()),
                    ('Lê', 'Tham Dự', 'user@confab.local', '$hashedPassword', 'user', 'active', 1, NOW())
            ");
            
            $result['messages'][] = 'Đã thêm 4 tài khoản mẫu (mật khẩu: password123)';
        } else {
            $result['messages'][] = "Đã có $userCount người dùng trong hệ thống";
        }
        
    } catch (Exception $e) {
        $result['messages'][] = 'Cảnh báo khi kiểm tra/thêm dữ liệu mẫu: ' . $e->getMessage();
    }
}

/**
 * Kiểm tra tính toàn vẹn của schema sau khi setup
 */
function validateSchema($conn) {
    global $result;
    
    $result['messages'][] = 'Đang kiểm tra tính toàn vẹn của schema...';
    
    // List of required tables from schema_complete.sql
    $requiredTables = [
        'users', 'categories', 'venues', 'conferences', 'speakers', 'conference_speakers',
        'conference_schedule', 'registrations', 'notifications', 'settings', 'audit_logs',
        'password_history', 'user_sessions', 'media_files', 'media_folders', 'translations',
        'languages', 'user_activity_logs', 'error_logs', 'security_logs', 'scheduled_tasks',
        'payment_methods', 'invoices', 'transactions', 'billing_info', 'certificates'
    ];
    
    $missingTables = [];
    $existingTables = [];
    
    foreach ($requiredTables as $table) {
        try {
            $count = $conn->query("SELECT COUNT(*) FROM information_schema.tables 
                                 WHERE table_schema = '" . DB_NAME . "' AND table_name = '$table'")->fetchColumn();
            if ($count > 0) {
                $existingTables[] = $table;
            } else {
                $missingTables[] = $table;
            }
        } catch (Exception $e) {
            $missingTables[] = $table;
        }
    }
    
    $result['data'] = [
        'existing_tables' => $existingTables,
        'missing_tables' => $missingTables,
        'tables_count' => count($existingTables)
    ];
    
    if (count($missingTables) > 0) {
        $result['messages'][] = 'Cảnh báo: Thiếu ' . count($missingTables) . ' bảng: ' . implode(', ', $missingTables);
    } else {
        $result['messages'][] = 'Tất cả ' . count($existingTables) . ' bảng cần thiết đã được tạo thành công!';
    }
    
    // Check for stored procedures and functions
    try {
        $procedures = $conn->query("SELECT COUNT(*) FROM information_schema.routines 
                                  WHERE routine_schema = '" . DB_NAME . "' AND routine_type = 'PROCEDURE'")->fetchColumn();
        $functions = $conn->query("SELECT COUNT(*) FROM information_schema.routines 
                                 WHERE routine_schema = '" . DB_NAME . "' AND routine_type = 'FUNCTION'")->fetchColumn();
        
        $result['messages'][] = "Đã tạo $procedures stored procedures và $functions functions";
        
    } catch (Exception $e) {
        $result['messages'][] = 'Không thể kiểm tra stored procedures: ' . $e->getMessage();
    }
    
    return count($missingTables) === 0;
}

try {
    $result['messages'][] = 'Bắt đầu thiết lập cơ sở dữ liệu...';
    
    // Kết nối database
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        $result['messages'][] = 'Kết nối cơ sở dữ liệu thành công!';
    } catch (PDOException $e) {
        throw new PDOException('Không thể kết nối đến cơ sở dữ liệu: ' . $e->getMessage());
    }    
    // Set timezone
    $conn->exec("SET time_zone = '+07:00'");
    $result['messages'][] = 'Đã thiết lập múi giờ: +07:00';
    
    // Disable foreign key checks during setup
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    $result['messages'][] = 'Tạm thời tắt kiểm tra khóa ngoại';
    
    // Execute the complete schema
    $schemaFile = dirname(__DIR__) . '/sql/schema_complete.sql';
    $success = executeSQLFile($conn, $schemaFile);
    
    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    $result['messages'][] = 'Đã bật lại kiểm tra khóa ngoại';
    
    // Import sample data if needed
    importSampleDataIfNeeded($conn);
    
    // Validate schema integrity
    $schemaValid = validateSchema($conn);
    
    if ($schemaValid && $success) {
        $result['success'] = true;
        $result['messages'][] = '🎉 Thiết lập cơ sở dữ liệu hoàn tất thành công!';
        $result['messages'][] = 'Hệ thống đã sẵn sàng để sử dụng với tất cả các tính năng nâng cao.';
    } else {
        $result['messages'][] = '⚠️ Thiết lập hoàn tất nhưng có một số cảnh báo. Kiểm tra log để biết chi tiết.';
        $result['success'] = true; // Still consider it successful even with warnings
    }

} catch (Exception $e) {
    $result['messages'][] = '❌ Lỗi khi thiết lập cơ sở dữ liệu: ' . $e->getMessage();
    $result['success'] = false;
    
    // Try to re-enable foreign key checks even on error
    try {
        if (isset($conn)) {
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
    } catch (Exception $e2) {
        // Ignore errors when re-enabling foreign key checks
    }
}

// Return JSON response
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>