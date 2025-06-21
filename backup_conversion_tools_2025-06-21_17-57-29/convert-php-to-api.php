<?php
/**
 * Công cụ chuyển đổi các file PHP còn render giao diện thành các API endpoint
 * Chạy script này để tự động xử lý các file PHP còn lại
 */

echo "========== Công cụ chuyển đổi PHP sang API ==========\n";

// Danh sách các file PHP trong thư mục gốc cần xử lý
$files = [
    'index.php',
    'login.php',
    'register.php',
    'profile.php',
    'conferences.php',
    'conference-detail.php',
    'conference-manager.php',
    'conference-edit.php',
    'conference-register.php',
    'admin.php'
];

// Tạo thư mục api nếu chưa có
if (!file_exists('api')) {
    mkdir('api', 0755);
    echo "Đã tạo thư mục api\n";
}

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Bỏ qua {$file} - File không tồn tại\n";
        continue;
    }
    
    $baseFileName = basename($file, '.php');
    $apiFile = "api/{$baseFileName}.php";
    
    // Kiểm tra xem API file đã tồn tại chưa
    if (file_exists($apiFile)) {
        echo "API Endpoint cho {$baseFileName} đã tồn tại: {$apiFile}\n";
    } else {
        // Tạo API endpoint mới
        $apiContent = "<?php
/**
 * API endpoint cho {$baseFileName}
 */
// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

// Force PHP to use relative paths only
\$_SERVER['SCRIPT_NAME'] = '/api/{$baseFileName}.php';
\$_SERVER['PHP_SELF'] = '/api/{$baseFileName}.php';

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Thiết lập header
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Lấy dữ liệu gửi lên
\$data = json_decode(file_get_contents('php://input'), true);

// Nếu không có dữ liệu từ JSON body, kiểm tra POST và GET
if (empty(\$data)) {
    \$data = array_merge(\$_GET, \$_POST);
}

// Chuẩn bị response mặc định
\$response = [
    'success' => false,
    'message' => 'Lỗi không xác định.',
    'data' => null
];

// Xử lý yêu cầu dựa vào method
\$method = \$_SERVER['REQUEST_METHOD'];

switch (\$method) {
    case 'GET':
        // TODO: Chuyển logic xử lý GET từ file PHP gốc qua đây
        // Ví dụ: Lấy dữ liệu từ database và trả về JSON
        \$response['success'] = true;
        \$response['message'] = 'Đã lấy dữ liệu thành công';
        \$response['data'] = []; // Thêm dữ liệu thực tế tại đây
        break;
        
    case 'POST':
        // TODO: Chuyển logic xử lý POST từ file PHP gốc qua đây
        // Ví dụ: Xác thực, lưu dữ liệu vào database, etc.
        \$response['success'] = true;
        \$response['message'] = 'Đã xử lý dữ liệu thành công';
        break;
        
    default:
        \$response['message'] = 'Method không được hỗ trợ';
        http_response_code(405); // Method Not Allowed
        break;
}

// Trả về kết quả dưới dạng JSON
echo json_encode(\$response);
";
        
        // Ghi nội dung vào file API mới
        file_put_contents($apiFile, $apiContent);
        echo "Đã tạo API endpoint mới: {$apiFile}\n";
    }
    
    // Tạo bản sao lưu file PHP gốc nếu cần
    $backupFile = "{$file}.backup";
    if (!file_exists($backupFile)) {
        copy($file, $backupFile);
        echo "Đã tạo bản sao lưu: {$backupFile}\n";
    }
    
    echo "Đã xử lý xong file {$file}\n";
    echo "-----------------------------------------\n";
}

echo "\nHướng dẫn tiếp theo:\n";
echo "1. Kiểm tra các file API mới trong thư mục api/\n";
echo "2. Chuyển logic xử lý PHP từ các file .php.backup vào các file API tương ứng\n";
echo "3. Cập nhật các file JavaScript để gọi API thay vì form submit\n";
echo "4. Xóa các file PHP gốc khi đã hoàn tất chuyển đổi\n";
echo "\n========== Hoàn tất ==========\n";
