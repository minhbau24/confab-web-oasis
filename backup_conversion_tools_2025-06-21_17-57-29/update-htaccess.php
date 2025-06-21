<?php
/**
 * Script cập nhật file .htaccess để chuyển hướng từ PHP sang HTML
 */

echo "========== CẬP NHẬT FILE .HTACCESS ==========\n\n";

// Nội dung .htaccess mới
$htaccess = <<<'EOT'
RewriteEngine On

# Set base directory preference
DirectoryIndex index.html index.php

# Prevent redirection of API requests
RewriteCond %{REQUEST_URI} !^/confab-web-oasis/api/
RewriteCond %{REQUEST_URI} !^/api/

# Block access to any URL containing Windows drive letters (C: etc)
RewriteCond %{THE_REQUEST} [A-Za-z]:[\\/]
RewriteRule ^ /confab-web-oasis/index.html [R=301,L]

# Redirect all .php files (except api/) to their .html versions
RewriteCond %{REQUEST_URI} !^/confab-web-oasis/api/
RewriteCond %{REQUEST_URI} !^/api/
RewriteRule ^([^/]+)\.php$ $1.html [R=301,L]

# Ensure direct access to index.php redirects to index.html
RewriteRule ^index\.php$ index.html [R=301,L]

# Đảm bảo PHP errors không hiển thị trong API responses
<IfModule mod_php7.c>
  php_flag display_errors off
</IfModule>

# Đặt JSON content type cho API
<FilesMatch "^api/.*\.php$">
  <IfModule mod_headers.c>
    Header set Content-Type "application/json; charset=UTF-8"
  </IfModule>
</FilesMatch>

# Cấu hình báo lỗi 404
ErrorDocument 404 /confab-web-oasis/404.html
EOT;

// Tạo bản sao lưu của file .htaccess hiện tại nếu có
if (file_exists('.htaccess')) {
    copy('.htaccess', '.htaccess.backup');
    echo "Đã tạo bản sao lưu file .htaccess hiện tại: .htaccess.backup\n";
}

// Ghi nội dung mới vào file .htaccess
file_put_contents('.htaccess', $htaccess);
echo "Đã cập nhật file .htaccess với cấu hình mới\n";

echo "\n========== HOÀN TẤT ==========\n";
