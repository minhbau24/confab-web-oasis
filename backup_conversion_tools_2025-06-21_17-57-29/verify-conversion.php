<?php
/**
 * Script kiểm tra và xác minh việc chuyển đổi từ PHP sang HTML/API
 */

echo "========== KIỂM TRA CHUYỂN ĐỔI ==========\n\n";

// Kiểm tra các file HTML cần thiết
$htmlFiles = [
    'index.html',
    'login.html',
    'register.html',
    'profile.html',
    'conferences.html',
    'conference-detail.html',
    'conference-manager.html',
    'admin.html',
];

echo "Kiểm tra các file HTML...\n";
foreach ($htmlFiles as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} - OK\n";
    } else {
        echo "✗ {$file} - KHÔNG TÌM THẤY\n";
    }
}
echo "\n";

// Kiểm tra các API endpoint
$apiFiles = [
    'api/login.php',
    'api/register.php',
    'api/conferences.php',
    'api/update_profile.php',
];

echo "Kiểm tra các API endpoint...\n";
foreach ($apiFiles as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} - OK\n";
        
        // Kiểm tra xem file có định nghĩa API_ENDPOINT = true không
        $content = file_get_contents($file);
        if (strpos($content, "define('API_ENDPOINT', true)") !== false) {
            echo "  ✓ API_ENDPOINT được định nghĩa đúng\n";
        } else {
            echo "  ✗ API_ENDPOINT không được định nghĩa hoặc định nghĩa sai\n";
        }
    } else {
        echo "✗ {$file} - KHÔNG TÌM THẤY\n";
    }
}
echo "\n";

// Kiểm tra các file PHP cũ (không nên tồn tại)
$oldPhpFiles = [
    'index.php',
    'login.php',
    'register.php',
    'profile.php',
    'conferences.php',
];

echo "Kiểm tra các file PHP cũ (không nên tồn tại)...\n";
$oldPhpFound = false;
foreach ($oldPhpFiles as $file) {
    if (file_exists($file)) {
        $oldPhpFound = true;
        echo "✗ {$file} - VẬN CÒN TỒN TẠI\n";
    } else {
        echo "✓ {$file} - ĐÃ XÓA - OK\n";
    }
}

if ($oldPhpFound) {
    echo "\nCẢNH BÁO: Một số file PHP cũ vẫn còn tồn tại. Bạn nên chạy script cleanup-php-files.php sau khi đã hoàn tất chuyển đổi.\n";
}

echo "\n";

// Kiểm tra file .htaccess
echo "Kiểm tra file .htaccess...\n";
if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'RewriteRule ^([^/]+)\.php$ $1.html') !== false) {
        echo "✓ .htaccess có quy tắc chuyển hướng từ .php sang .html\n";
    } else {
        echo "✗ .htaccess không có quy tắc chuyển hướng từ .php sang .html\n";
    }
} else {
    echo "✗ .htaccess không tồn tại\n";
}

echo "\n========== KIỂM TRA HOÀN TẤT ==========\n\n";

echo "HƯỚNG DẪN TIẾP THEO:\n";
echo "1. Nếu tất cả kiểm tra đều OK, bạn đã hoàn tất việc chuyển đổi\n";
echo "2. Nếu phát hiện lỗi, hãy sửa chúng và chạy lại script này\n";
echo "3. Sau khi chuyển đổi hoàn tất, bạn có thể xóa các file PHP cũ bằng script cleanup-php-files.php\n";
