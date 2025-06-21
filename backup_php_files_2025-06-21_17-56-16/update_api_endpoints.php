<?php
/**
 * Script để thêm khai báo API_ENDPOINT vào tất cả file API
 * Điều này giúp ngăn chặn chuyển hướng không mong muốn từ includes/redirect.php
 */

// Danh sách tất cả các file API
$api_files = [
    'login.php',
    'register.php',
    'import_sample_data.php',
    'home.php',
    'conference_speakers.php',
    'conference_schedule.php',
    'conference_registration.php',
    'conferences_delete.php',
    'conferences.php',
    'change_password.php',
    'users.php',
    'update_profile.php',
    'update_conference.php',
    'setup_database.php'
];

$api_dir = __DIR__ . '/api/';
$count = 0;

foreach ($api_files as $file) {
    $filepath = $api_dir . $file;
    
    if (!file_exists($filepath)) {
        echo "File không tồn tại: $file<br>";
        continue;
    }
    
    // Đọc nội dung file
    $content = file_get_contents($filepath);
    
    // Kiểm tra xem đã có khai báo API_ENDPOINT chưa
    if (strpos($content, "define('API_ENDPOINT', true)") === false) {
        // Tìm vị trí sau <?php để thêm khai báo
        $pattern = '/(<\?php.*?)(\r?\n)/s';
        $replacement = '$1$2// Define this file as an API endpoint to prevent HTML redirects' . PHP_EOL . "define('API_ENDPOINT', true);" . PHP_EOL . PHP_EOL;
        
        $new_content = preg_replace($pattern, $replacement, $content, 1);
        
        if ($new_content !== $content) {
            // Ghi lại nội dung đã cập nhật
            file_put_contents($filepath, $new_content);
            $count++;
            echo "Đã thêm khai báo API_ENDPOINT vào: $file<br>";
        } else {
            echo "Không thể thêm khai báo vào: $file<br>";
        }
    } else {
        echo "File đã có khai báo API_ENDPOINT: $file<br>";
    }
}

echo "<br>Hoàn thành! Đã cập nhật $count file API.";
