<?php
/**
 * Script để sửa lỗi session_start() trong tất cả API files
 */

$apiDir = __DIR__ . '/api/';
$files = glob($apiDir . '*.php');

$sessionStartPattern = '/session_start\(\);/';
$sessionStartReplacement = 'if (session_status() === PHP_SESSION_NONE) { session_start(); }';

$headersPattern = '/header\([\'"]Content-Type: application\/json[\'"]?\);/';
$headersReplacement = 'header(\'Content-Type: application/json; charset=UTF-8\');';

echo "Đang sửa lỗi session trong các API files...\n";

foreach ($files as $file) {
    $filename = basename($file);
    echo "Đang xử lý: $filename\n";
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Sửa session_start
    if (preg_match($sessionStartPattern, $content)) {
        $content = preg_replace($sessionStartPattern, $sessionStartReplacement, $content);
        echo "  - Đã sửa session_start()\n";
    }
    
    // Sửa header Content-Type
    if (preg_match($headersPattern, $content)) {
        $content = preg_replace($headersPattern, $headersReplacement, $content);
        echo "  - Đã sửa Content-Type header\n";
    }
    
    // Ghi lại file nếu có thay đổi
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "  - File đã được cập nhật\n";
    } else {
        echo "  - Không cần thay đổi\n";
    }
    
    echo "\n";
}

echo "Hoàn thành!\n";
?>
