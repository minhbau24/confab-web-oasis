<?php
/**
 * Script xóa các file PHP gốc sau khi đã chuyển đổi sang mô hình API
 * CHÚ Ý: Chỉ chạy script này sau khi đã đảm bảo:
 * 1. Các file API endpoint đã được tạo và hoạt động đúng
 * 2. Các file HTML đã có và hiển thị đúng
 * 3. Các file JavaScript đã được cập nhật để gọi API endpoint
 */

echo "========== XÓA CÁC FILE PHP GỐC ==========\n\n";
echo "CẢNH BÁO: Script này sẽ xóa các file PHP gốc trong thư mục chính!\n";
echo "Đảm bảo bạn đã sao lưu dữ liệu và hoàn tất quá trình chuyển đổi.\n\n";

// Hàm xác nhận từ người dùng
function confirm($message) {
    echo $message . " (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return trim($line) === 'y';
}

// Danh sách các file PHP cần xóa
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

// Xác nhận trước khi xóa
if (!confirm("Bạn có chắc chắn muốn xóa các file PHP gốc không? Hãy đảm bảo các file API và HTML đã hoạt động đúng.")) {
    echo "Hủy bỏ thao tác.\n";
    exit;
}

// Xóa từng file
foreach ($files as $file) {
    if (file_exists($file)) {
        if (confirm("Xóa file $file?")) {
            if (unlink($file)) {
                echo "Đã xóa: $file\n";
            } else {
                echo "Không thể xóa: $file\n";
            }
        } else {
            echo "Đã bỏ qua: $file\n";
        }
    } else {
        echo "Không tìm thấy file: $file\n";
    }
}

echo "\n========== HOÀN TẤT ==========\n\n";
echo "Lưu ý: Nếu bạn cần khôi phục các file PHP, bạn có thể tìm thấy bản sao lưu ở các file .backup\n";
