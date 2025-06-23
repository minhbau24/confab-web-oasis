<?php
/**
 * Confab Web Oasis - Schema Verification Tool
 * Kiểm tra tính toàn vẹn và đầy đủ của database schema
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
    'data' => [
        'tables' => [],
        'procedures' => [],
        'functions' => [],
        'triggers' => [],
        'views' => [],
        'indexes' => [],
        'foreign_keys' => []
    ],
    'statistics' => []
];

try {
    // Kết nối database
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
    $result['messages'][] = 'Kết nối database thành công';
    
    // Danh sách bảng yêu cầu từ schema_complete.sql
    $expectedTables = [
        'users', 'categories', 'venues', 'conferences', 'speakers',
        'conference_speakers', 'conference_schedule', 'registrations',
        'notifications', 'settings', 'audit_logs', 'password_history',
        'user_sessions', 'media_files', 'media_folders', 'translations',
        'languages', 'user_activity_logs', 'error_logs', 'security_logs',
        'scheduled_tasks', 'payment_methods', 'invoices', 'transactions',
        'billing_info', 'certificates'
    ];
    
    // Kiểm tra bảng
    $result['messages'][] = 'Đang kiểm tra các bảng...';
    $existingTables = [];
    $missingTables = [];
    
    foreach ($expectedTables as $table) {
        $query = $conn->prepare("SELECT COUNT(*) FROM information_schema.tables 
                                WHERE table_schema = ? AND table_name = ?");
        $query->execute([DB_NAME, $table]);
        
        if ($query->fetchColumn() > 0) {
            $existingTables[] = $table;
            
            // Lấy thông tin chi tiết về bảng
            $tableInfo = $conn->prepare("SELECT 
                COUNT(*) as row_count,
                CREATE_TIME as created_at,
                UPDATE_TIME as updated_at,
                TABLE_COMMENT as comment
                FROM information_schema.tables 
                WHERE table_schema = ? AND table_name = ?");
            $tableInfo->execute([DB_NAME, $table]);
            $tableData = $tableInfo->fetch();
            
            // Lấy số lượng cột
            $columnCount = $conn->prepare("SELECT COUNT(*) FROM information_schema.columns 
                                         WHERE table_schema = ? AND table_name = ?");
            $columnCount->execute([DB_NAME, $table]);
            
            $result['data']['tables'][$table] = [
                'exists' => true,
                'columns' => $columnCount->fetchColumn(),
                'rows' => 0, // Sẽ được cập nhật bên dưới
                'created_at' => $tableData['created_at'],
                'updated_at' => $tableData['updated_at'],
                'comment' => $tableData['comment']
            ];
            
            // Đếm số dòng dữ liệu (an toàn)
            try {
                $rowCount = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                $result['data']['tables'][$table]['rows'] = $rowCount;
            } catch (Exception $e) {
                $result['data']['tables'][$table]['rows'] = 'N/A';
            }
            
        } else {
            $missingTables[] = $table;
            $result['data']['tables'][$table] = ['exists' => false];
        }
    }
    
    // Kiểm tra stored procedures
    $result['messages'][] = 'Đang kiểm tra stored procedures...';
    $procedures = $conn->query("SELECT routine_name, routine_comment, created, last_altered 
                               FROM information_schema.routines 
                               WHERE routine_schema = '" . DB_NAME . "' AND routine_type = 'PROCEDURE'
                               ORDER BY routine_name")->fetchAll();
    
    foreach ($procedures as $proc) {
        $result['data']['procedures'][] = [
            'name' => $proc['routine_name'],
            'comment' => $proc['routine_comment'],
            'created' => $proc['created'],
            'last_altered' => $proc['last_altered']
        ];
    }
    
    // Kiểm tra functions
    $result['messages'][] = 'Đang kiểm tra functions...';
    $functions = $conn->query("SELECT routine_name, routine_comment, created, last_altered 
                              FROM information_schema.routines 
                              WHERE routine_schema = '" . DB_NAME . "' AND routine_type = 'FUNCTION'
                              ORDER BY routine_name")->fetchAll();
    
    foreach ($functions as $func) {
        $result['data']['functions'][] = [
            'name' => $func['routine_name'],
            'comment' => $func['routine_comment'],
            'created' => $func['created'],
            'last_altered' => $func['last_altered']
        ];
    }
    
    // Kiểm tra triggers
    $result['messages'][] = 'Đang kiểm tra triggers...';
    $triggers = $conn->query("SELECT trigger_name, event_manipulation, event_object_table, 
                             action_timing, created 
                             FROM information_schema.triggers 
                             WHERE trigger_schema = '" . DB_NAME . "'
                             ORDER BY trigger_name")->fetchAll();
    
    foreach ($triggers as $trigger) {
        $result['data']['triggers'][] = [
            'name' => $trigger['trigger_name'],
            'event' => $trigger['event_manipulation'],
            'table' => $trigger['event_object_table'],
            'timing' => $trigger['action_timing'],
            'created' => $trigger['created']
        ];
    }
    
    // Kiểm tra views
    $result['messages'][] = 'Đang kiểm tra views...';
    $views = $conn->query("SELECT table_name as view_name, view_definition 
                          FROM information_schema.views 
                          WHERE table_schema = '" . DB_NAME . "'
                          ORDER BY table_name")->fetchAll();
    
    foreach ($views as $view) {
        $result['data']['views'][] = [
            'name' => $view['view_name'],
            'definition' => substr($view['view_definition'], 0, 100) . '...'
        ];
    }
    
    // Kiểm tra foreign keys
    $result['messages'][] = 'Đang kiểm tra foreign keys...';
    $foreignKeys = $conn->query("SELECT 
                                table_name,
                                column_name,
                                constraint_name,
                                referenced_table_name,
                                referenced_column_name
                                FROM information_schema.key_column_usage 
                                WHERE table_schema = '" . DB_NAME . "' 
                                AND referenced_table_name IS NOT NULL
                                ORDER BY table_name, column_name")->fetchAll();
    
    foreach ($foreignKeys as $fk) {
        $result['data']['foreign_keys'][] = [
            'table' => $fk['table_name'],
            'column' => $fk['column_name'],
            'constraint' => $fk['constraint_name'],
            'references_table' => $fk['referenced_table_name'],
            'references_column' => $fk['referenced_column_name']
        ];
    }
    
    // Tính toán thống kê
    $result['statistics'] = [
        'total_tables' => count($expectedTables),
        'existing_tables' => count($existingTables),
        'missing_tables' => count($missingTables),
        'stored_procedures' => count($procedures),
        'functions' => count($functions),
        'triggers' => count($triggers),
        'views' => count($views),
        'foreign_keys' => count($foreignKeys),
        'completion_percentage' => round((count($existingTables) / count($expectedTables)) * 100, 2)
    ];
    
    // Tạo báo cáo tổng kết
    if (count($missingTables) === 0) {
        $result['success'] = true;
        $result['messages'][] = '✅ Schema hoàn chỉnh! Tất cả ' . count($existingTables) . ' bảng cần thiết đã được tạo.';
    } else {
        $result['success'] = false;
        $result['messages'][] = '⚠️ Schema chưa hoàn chỉnh. Thiếu ' . count($missingTables) . ' bảng: ' . implode(', ', $missingTables);
    }
    
    $result['messages'][] = 'Tổng kết: ' . count($existingTables) . '/' . count($expectedTables) . ' bảng (' . $result['statistics']['completion_percentage'] . '%)';
    $result['messages'][] = 'Stored Procedures: ' . count($procedures) . ', Functions: ' . count($functions) . ', Triggers: ' . count($triggers) . ', Views: ' . count($views);
    
    // Kiểm tra dữ liệu mẫu
    if (count($existingTables) > 0) {
        $result['messages'][] = 'Đang kiểm tra dữ liệu mẫu...';
        
        $sampleDataTables = ['users', 'languages', 'payment_methods', 'settings'];
        $dataStatus = [];
        
        foreach ($sampleDataTables as $table) {
            if (in_array($table, $existingTables)) {
                try {
                    $count = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                    $dataStatus[$table] = $count;
                } catch (Exception $e) {
                    $dataStatus[$table] = 'Error: ' . $e->getMessage();
                }
            }
        }
        
        $result['data']['sample_data'] = $dataStatus;
        
        if (isset($dataStatus['users']) && $dataStatus['users'] > 0) {
            $result['messages'][] = '👥 Dữ liệu người dùng: ' . $dataStatus['users'] . ' tài khoản';
        }
    }
    
} catch (Exception $e) {
    $result['success'] = false;
    $result['messages'][] = '❌ Lỗi khi kiểm tra schema: ' . $e->getMessage();
}

// Trả về kết quả JSON
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
