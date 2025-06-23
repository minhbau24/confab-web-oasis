<?php
/**
 * Schema Validation Tool - Kiểm tra tính hợp lệ của schema SQL
 * Công cụ này sẽ phân tích schema_complete.sql và báo cáo các vấn đề tiềm ẩn
 */

header('Content-Type: text/plain; charset=utf-8');

$schemaFile = __DIR__ . '/../sql/schema_complete.sql';

if (!file_exists($schemaFile)) {
    die("❌ Không tìm thấy file schema: $schemaFile\n");
}

echo "🔍 SCHEMA VALIDATION TOOL\n";
echo "========================\n";
echo "Đang kiểm tra: $schemaFile\n\n";

$content = file_get_contents($schemaFile);
$lines = explode("\n", $content);

$errors = [];
$warnings = [];
$tables = [];
$views = [];
$indexes = [];

// 1. Tìm tất cả các bảng được định nghĩa
echo "📋 1. Kiểm tra định nghĩa bảng...\n";
preg_match_all('/CREATE TABLE.*?`([^`]+)`/i', $content, $matches);
foreach ($matches[1] as $table) {
    $tables[] = $table;
}
echo "   ✅ Tìm thấy " . count($tables) . " bảng: " . implode(', ', $tables) . "\n\n";

// 2. Tìm tất cả các VIEW
echo "👁️ 2. Kiểm tra định nghĩa VIEWs...\n";
preg_match_all('/CREATE.*?VIEW.*?`?([a-zA-Z_]+)`?\s+AS/i', $content, $matches);
foreach ($matches[1] as $view) {
    $views[] = $view;
}
echo "   ✅ Tìm thấy " . count($views) . " views: " . implode(', ', $views) . "\n\n";

// 3. Kiểm tra VIEW references các bảng có tồn tại
echo "🔗 3. Kiểm tra tham chiếu bảng trong VIEWs...\n";
foreach ($views as $view) {
    // Tìm pattern CREATE VIEW ... AS SELECT ... FROM
    $pattern = '/CREATE.*?VIEW.*?' . preg_quote($view) . '.*?AS\s+(.*?)(?=CREATE|$)/is';
    if (preg_match($pattern, $content, $match)) {
        $viewDefinition = $match[1];
        
        // Tìm các bảng được tham chiếu trong FROM và JOIN
        preg_match_all('/(?:FROM|JOIN)\s+`?([a-zA-Z_]+)`?/i', $viewDefinition, $refMatches);
        foreach ($refMatches[1] as $referencedTable) {
            if (!in_array($referencedTable, $tables)) {
                $errors[] = "VIEW '$view' tham chiếu bảng '$referencedTable' không tồn tại";
            }
        }
    }
}

// 4. Kiểm tra các cột được sử dụng trong VIEW
echo "📊 4. Kiểm tra tham chiếu cột trong VIEWs...\n";

// Định nghĩa cấu trúc bảng (rút gọn để kiểm tra)
$tableColumns = [
    'users' => ['id', 'firstName', 'lastName', 'email', 'password', 'role', 'status', 'created_at', 'updated_at', 'last_login', 'login_attempts'],
    'conferences' => ['id', 'title', 'status', 'capacity', 'current_attendees', 'start_date', 'end_date', 'category_id', 'venue_id'],
    'registrations' => ['id', 'user_id', 'conference_id', 'status', 'payment_status', 'price_paid', 'certificate_issued'],
    'certificates' => ['id', 'user_id', 'conference_id'],
    'user_activity_logs' => ['id', 'user_id', 'activity_type', 'description', 'entity_type', 'entity_id', 'ip_address', 'device_type', 'os', 'browser', 'created_at'],
    'schedule_sessions' => ['id', 'conference_id'],
    'categories' => ['id', 'name'],
    'venues' => ['id', 'name'],
    'conference_speakers' => ['id', 'conference_id', 'speaker_id'],
    'transactions' => ['id', 'user_id', 'amount', 'fee', 'status', 'type', 'payment_date'],
    'invoices' => ['id', 'invoice_number', 'user_id', 'conference_id', 'amount_total', 'amount_paid', 'status', 'created_at'],
    'invoice_items' => ['id', 'invoice_id', 'registration_id'],
    'payment_methods' => ['id', 'name'],
    'error_logs' => ['id', 'user_id', 'level', 'exception_class', 'created_at', 'ip_address']
];

// Kiểm tra các cột được sử dụng có tồn tại không
$problematicColumns = [
    'users' => ['username', 'full_name'], // Những cột không tồn tại
    'transactions' => ['transaction_status'] // Cột này là 'status'
];

foreach ($problematicColumns as $table => $cols) {
    foreach ($cols as $col) {
        if (strpos($content, "$table.$col") !== false || strpos($content, "u.$col") !== false) {
            $errors[] = "Cột '$col' trong bảng '$table' không tồn tại trong schema";
        }
    }
}

// 5. Kiểm tra INDEX
echo "📇 5. Kiểm tra định nghĩa INDEX...\n";
preg_match_all('/CREATE INDEX.*?ON\s+`?([a-zA-Z_]+)`?\s*\(([^)]+)\)/i', $content, $matches);
for ($i = 0; $i < count($matches[0]); $i++) {
    $table = $matches[1][$i];
    $columns = $matches[2][$i];
    $indexes[] = ['table' => $table, 'columns' => $columns, 'definition' => $matches[0][$i]];
    
    // Kiểm tra bảng có tồn tại không
    if (!in_array($table, $tables)) {
        $errors[] = "INDEX tham chiếu bảng '$table' không tồn tại: " . $matches[0][$i];
    }
    
    // Kiểm tra một số cột phổ biến có vấn đề
    if (strpos($columns, 'password_expires_at') !== false && $table === 'users') {
        $errors[] = "INDEX tham chiếu cột 'password_expires_at' không tồn tại trong bảng users";
    }
    if (strpos($columns, 'failed_login_attempts') !== false && $table === 'users') {
        $errors[] = "INDEX tham chiếu cột 'failed_login_attempts' không tồn tại trong bảng users (nên là 'login_attempts')";
    }
    if (strpos($columns, 'language_code') !== false && $table === 'translations') {
        $errors[] = "INDEX tham chiếu cột 'language_code' không tồn tại trong bảng translations (nên là 'lang_code')";
    }
}

echo "   ✅ Tìm thấy " . count($indexes) . " indexes\n\n";

// 6. Kiểm tra FOREIGN KEY constraints
echo "🔗 6. Kiểm tra FOREIGN KEY constraints...\n";
preg_match_all('/CONSTRAINT.*?FOREIGN KEY.*?REFERENCES\s+`?([a-zA-Z_]+)`?/i', $content, $matches);
foreach ($matches[1] as $referencedTable) {
    if (!in_array($referencedTable, $tables)) {
        $errors[] = "FOREIGN KEY tham chiếu bảng '$referencedTable' không tồn tại";
    }
}

// 7. Kiểm tra sample data INSERT statements
echo "💾 7. Kiểm tra INSERT statements cho sample data...\n";
preg_match_all('/INSERT INTO\s+`?([a-zA-Z_]+)`?/i', $content, $matches);
$insertTables = array_unique($matches[1]);
foreach ($insertTables as $table) {
    if (!in_array($table, $tables)) {
        $errors[] = "INSERT statement tham chiếu bảng '$table' không tồn tại";
    }
}
echo "   ✅ Tìm thấy INSERT statements cho " . count($insertTables) . " bảng\n\n";

// 8. Tổng kết
echo "📊 KẾT QUẢ KIỂM TRA\n";
echo "==================\n";

if (empty($errors)) {
    echo "✅ THÀNH CÔNG: Không phát hiện lỗi nghiêm trọng!\n";
} else {
    echo "❌ PHÁT HIỆN " . count($errors) . " LỖI:\n";
    foreach ($errors as $i => $error) {
        echo "   " . ($i + 1) . ". $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  CẢNH BÁO (" . count($warnings) . "):\n";
    foreach ($warnings as $i => $warning) {
        echo "   " . ($i + 1) . ". $warning\n";
    }
}

echo "\n📈 THỐNG KÊ:\n";
echo "- Tổng số bảng: " . count($tables) . "\n";
echo "- Tổng số views: " . count($views) . "\n";
echo "- Tổng số indexes: " . count($indexes) . "\n";
echo "- Bảng có sample data: " . count($insertTables) . "\n";

if (empty($errors)) {
    echo "\n🎉 Schema đã sẵn sàng để deploy!\n";
} else {
    echo "\n🔧 Cần sửa các lỗi trước khi deploy.\n";
}
?>
