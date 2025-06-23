<?php
/**
 * Confab Web Oasis - Database Setup Test Suite
 * Test toàn diện cho quá trình setup database
 */

// Define this file as an API endpoint
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
    'tests' => [],
    'summary' => [
        'total' => 0,
        'passed' => 0,
        'failed' => 0,
        'warnings' => 0
    ]
];

/**
 * Run a test and log the result
 */
function runTest($name, $callback, $description = '') {
    global $result;
    
    $testResult = [
        'name' => $name,
        'description' => $description,
        'status' => 'failed',
        'message' => '',
        'execution_time' => 0
    ];
    
    $startTime = microtime(true);
    
    try {
        $outcome = $callback();
        
        if ($outcome === true) {
            $testResult['status'] = 'passed';
            $testResult['message'] = 'Test passed successfully';
            $result['summary']['passed']++;
        } elseif (is_array($outcome) && isset($outcome['status'])) {
            $testResult['status'] = $outcome['status'];
            $testResult['message'] = $outcome['message'];
            
            if ($outcome['status'] === 'passed') {
                $result['summary']['passed']++;
            } elseif ($outcome['status'] === 'warning') {
                $result['summary']['warnings']++;
            } else {
                $result['summary']['failed']++;
            }
        } else {
            $testResult['status'] = 'failed';
            $testResult['message'] = 'Test returned unexpected result';
            $result['summary']['failed']++;
        }
        
    } catch (Exception $e) {
        $testResult['status'] = 'failed';
        $testResult['message'] = 'Exception: ' . $e->getMessage();
        $result['summary']['failed']++;
    }
    
    $testResult['execution_time'] = round((microtime(true) - $startTime) * 1000, 2);
    $result['tests'][] = $testResult;
    $result['summary']['total']++;
    
    return $testResult['status'] === 'passed';
}

try {
    $result['messages'][] = 'Bắt đầu test suite cho database setup...';
    
    // Test 1: Database Connection
    runTest('database_connection', function() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        return true;
    }, 'Kiểm tra kết nối database');
    
    // Test 2: Schema File Exists
    runTest('schema_file_exists', function() {
        $schemaFile = dirname(__DIR__) . '/sql/schema_complete.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception('File schema_complete.sql không tồn tại');
        }
        
        $content = file_get_contents($schemaFile);
        if (empty($content)) {
            throw new Exception('File schema_complete.sql rỗng');
        }
        
        // Check for key components
        $requiredComponents = [
            'CREATE TABLE',
            'DELIMITER',
            'CREATE PROCEDURE',
            'CREATE TRIGGER',
            'CREATE VIEW'
        ];
        
        foreach ($requiredComponents as $component) {
            if (strpos($content, $component) === false) {
                return [
                    'status' => 'warning',
                    'message' => "Thiếu component: $component"
                ];
            }
        }
        
        return true;
    }, 'Kiểm tra file schema_complete.sql');
    
    // Test 3: API Endpoints
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    $baseApiUrl = $baseUrl . $basePath;
    
    runTest('api_setup_database', function() use ($baseApiUrl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseApiUrl . '/setup_database.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Code: $httpCode");
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            throw new Exception('Invalid JSON response');
        }
        
        if (!isset($data['success'])) {
            throw new Exception('Missing success field in response');
        }
        
        return [
            'status' => $data['success'] ? 'passed' : 'warning',
            'message' => $data['success'] ? 'API setup hoạt động' : 'API setup có cảnh báo: ' . implode(', ', array_slice($data['messages'], 0, 3))
        ];
        
    }, 'Test API setup_database.php');
    
    runTest('api_verify_schema', function() use ($baseApiUrl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseApiUrl . '/verify_schema.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Code: $httpCode");
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            throw new Exception('Invalid JSON response');
        }
        
        return [
            'status' => 'passed',
            'message' => 'API verify_schema hoạt động, completion: ' . ($data['statistics']['completion_percentage'] ?? 'N/A') . '%'
        ];
        
    }, 'Test API verify_schema.php');
    
    // Test 4: Required Tables
    runTest('required_tables', function() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $requiredTables = [
            'users', 'categories', 'venues', 'conferences', 'speakers',
            'registrations', 'notifications', 'settings', 'audit_logs'
        ];
        
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            $query = $conn->prepare("SELECT COUNT(*) FROM information_schema.tables 
                                   WHERE table_schema = ? AND table_name = ?");
            $query->execute([DB_NAME, $table]);
            
            if ($query->fetchColumn() == 0) {
                $missingTables[] = $table;
            }
        }
        
        if (count($missingTables) > 0) {
            return [
                'status' => 'warning',
                'message' => 'Thiếu ' . count($missingTables) . ' bảng: ' . implode(', ', array_slice($missingTables, 0, 5))
            ];
        }
        
        return true;
    }, 'Kiểm tra bảng cần thiết');
    
    // Test 5: Stored Procedures
    runTest('stored_procedures', function() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $procedures = $conn->query("SELECT COUNT(*) FROM information_schema.routines 
                                  WHERE routine_schema = '" . DB_NAME . "' AND routine_type = 'PROCEDURE'")->fetchColumn();
        
        if ($procedures == 0) {
            return [
                'status' => 'warning',
                'message' => 'Không có stored procedures nào'
            ];
        }
        
        return [
            'status' => 'passed',
            'message' => "Tìm thấy $procedures stored procedures"
        ];
    }, 'Kiểm tra stored procedures');
    
    // Test 6: Sample Data
    runTest('sample_data', function() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Check if users table exists and has data
        try {
            $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
            
            if ($userCount == 0) {
                return [
                    'status' => 'warning',
                    'message' => 'Bảng users không có dữ liệu mẫu'
                ];
            }
            
            return [
                'status' => 'passed',
                'message' => "Có $userCount người dùng mẫu"
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'warning',
                'message' => 'Không thể kiểm tra dữ liệu users: ' . $e->getMessage()
            ];
        }
    }, 'Kiểm tra dữ liệu mẫu');
    
    // Test 7: Foreign Key Constraints
    runTest('foreign_keys', function() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $fkCount = $conn->query("SELECT COUNT(*) FROM information_schema.key_column_usage 
                               WHERE table_schema = '" . DB_NAME . "' AND referenced_table_name IS NOT NULL")->fetchColumn();
        
        if ($fkCount == 0) {
            return [
                'status' => 'warning',
                'message' => 'Không có foreign key constraints'
            ];
        }
        
        return [
            'status' => 'passed',
            'message' => "Có $fkCount foreign key constraints"
        ];
    }, 'Kiểm tra foreign key constraints');
    
    // Test 8: Setup Web Interface
    runTest('setup_web_interface', function() use ($baseUrl, $basePath) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . $basePath . '/setup.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Code: $httpCode");
        }
        
        if (strpos($response, 'Confab Web Oasis') === false) {
            throw new Exception('Setup page không chứa title mong đợi');
        }
        
        return true;
    }, 'Kiểm tra giao diện setup.php');
    
    // Calculate success rate
    $successRate = $result['summary']['total'] > 0 ? 
        round(($result['summary']['passed'] / $result['summary']['total']) * 100, 2) : 0;
    
    // Final assessment
    if ($result['summary']['failed'] == 0) {
        if ($result['summary']['warnings'] == 0) {
            $result['success'] = true;
            $result['messages'][] = "🎉 Tất cả tests đã pass! Success rate: $successRate%";
        } else {
            $result['success'] = true;
            $result['messages'][] = "✅ Tests hoàn thành với một số cảnh báo. Success rate: $successRate%";
        }
    } else {
        $result['success'] = false;
        $result['messages'][] = "❌ Có {$result['summary']['failed']} tests thất bại. Success rate: $successRate%";
    }
    
    $result['messages'][] = "Tổng kết: {$result['summary']['passed']} passed, {$result['summary']['warnings']} warnings, {$result['summary']['failed']} failed";
    
} catch (Exception $e) {
    $result['success'] = false;
    $result['messages'][] = '❌ Lỗi khi chạy test suite: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
