<?php
/**
 * Chèn tiêu đề API vào tất cả các file API để ngăn chặn chuyển hướng HTML
 */

$apiPath = __DIR__ . '/api';
$apiFiles = glob($apiPath . '/*.php');

foreach ($apiFiles as $file) {
    $content = file_get_contents($file);
    
    // Kiểm tra xem tệp đã có định nghĩa API_ENDPOINT chưa
    if (strpos($content, "define('API_ENDPOINT'") === false) {
        // Tìm dòng đầu tiên sau <?php
        $pattern = '/^<\?php\s*\n/';
        $replacement = "<?php\n// Define this file as an API endpoint to prevent HTML redirects\ndefine('API_ENDPOINT', true);\n\n";
        
        $content = preg_replace($pattern, $replacement, $content);
        file_put_contents($file, $content);
        echo "Updated: " . basename($file) . "\n";
    }
}

echo "Done!\n";
?>
