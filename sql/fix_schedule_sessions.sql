-- ========================================================
-- FIX SCRIPT FOR REMAINING SQL ERRORS
-- Sửa 3 lỗi còn lại trong schema_complete.sql
-- ========================================================

-- 1. Fix lỗi câu lệnh 87: Column count doesn't match value count at row 8
-- Cần sửa các INSERT INTO schedule_sessions có thiếu cột

-- 2. Fix lỗi câu lệnh 112: Duplicate entry 'INV-2024-001' for key 'invoice_number'
-- Xóa dữ liệu invoices trước khi insert
DELETE FROM `invoices`;

-- 3. Fix lỗi câu lệnh 114: Duplicate entry 'TXN-2024-001' for key 'transaction_id'  
-- Xóa dữ liệu transactions trước khi insert
DELETE FROM `transactions`;

-- Xóa các dữ liệu khác có thể bị duplicate
DELETE FROM `user_activity_logs`;
DELETE FROM `invoice_items`;
DELETE FROM `error_logs`;

-- Thêm lại dữ liệu đã được sửa
-- Dữ liệu mẫu cho bảng user_activity_logs
INSERT INTO `user_activity_logs` (`user_id`, `activity_type`, `description`, `entity_type`, `entity_id`, `ip_address`, `device_type`, `os`, `browser`) VALUES
(1, 'login', 'Đăng nhập hệ thống', 'user', 1, '127.0.0.1', 'desktop', 'Windows', 'Chrome'),
(2, 'create_conference', 'Tạo hội nghị mới', 'conference', 1, '127.0.0.1', 'desktop', 'Windows', 'Chrome'),
(3, 'register_conference', 'Đăng ký tham dự hội nghị', 'conference', 1, '127.0.0.1', 'mobile', 'Android', 'Chrome'),
(4, 'view_profile', 'Xem trang cá nhân', 'user', 4, '127.0.0.1', 'desktop', 'macOS', 'Safari');

-- Dữ liệu mẫu cho bảng invoices (chỉ insert 1 lần)
INSERT INTO `invoices` (`invoice_number`, `user_id`, `conference_id`, `amount_subtotal`, `amount_total`, `currency`, `status`, `due_date`) VALUES
('INV-2024-001', 3, 1, 2500000.00, 2500000.00, 'VND', 'paid', '2024-12-10 23:59:59'),
('INV-2024-002', 4, 2, 500000.00, 500000.00, 'VND', 'sent', '2024-11-25 23:59:59'),
('INV-2024-003', 3, 3, 1500000.00, 1500000.00, 'VND', 'draft', '2024-12-15 23:59:59');

-- Dữ liệu mẫu cho bảng invoice_items
INSERT INTO `invoice_items` (`invoice_id`, `description`, `quantity`, `unit_price`) VALUES
(1, 'Vietnam Tech Summit 2024 - Vé thường', 1, 2500000.00),
(2, 'Startup Weekend Ho Chi Minh - Vé thường', 1, 500000.00),
(3, 'Digital Health Conference 2024 - Vé thường', 1, 1500000.00);

-- Dữ liệu mẫu cho bảng transactions (chỉ insert 1 lần)
INSERT INTO `transactions` (`transaction_id`, `user_id`, `conference_id`, `invoice_id`, `payment_method_id`, `type`, `amount`, `currency`, `status`, `gateway`, `payment_date`) VALUES
('TXN-2024-001', 3, 1, 1, 2, 'payment', 2500000.00, 'VND', 'completed', 'momo', '2024-12-01 10:30:00'),
('TXN-2024-002', 4, 2, 2, 1, 'payment', 500000.00, 'VND', 'pending', 'bank_transfer', NULL);

-- Dữ liệu mẫu cho bảng error_logs
INSERT INTO `error_logs` (`user_id`, `level`, `message`, `exception_class`, `file`, `line`, `ip_address`, `user_agent`) VALUES
(NULL, 'error', 'Database connection timeout', 'PDOException', '/includes/database.php', 25, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, 'warning', 'File upload size exceeded', 'FileUploadException', '/api/upload.php', 45, '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'),
(NULL, 'info', 'Scheduled task completed successfully', NULL, '/cron/backup.php', 10, '127.0.0.1', 'CLI');

-- ========================================================
-- KHẮC PHỤC LỖI SỐ 87: Column count doesn't match value count at row 8
-- ========================================================

-- Tìm và sửa các INSERT INTO schedule_sessions có thiếu cột
-- Bảng schedule_sessions có 25 cột, cần đảm bảo tất cả INSERT đều có đủ cột

-- Ví dụ cấu trúc đúng cho schedule_sessions:
/*
INSERT INTO `schedule_sessions` (
    `id`, `conference_id`, `title`, `description`, `session_date`, `start_time`, `end_time`, 
    `type`, `room`, `capacity`, `speaker_id`, `additional_speakers`, `materials`, `slides_url`, 
    `video_url`, `live_stream_url`, `is_mandatory`, `requires_registration`, `level`, `tags`, 
    `status`, `sort_order`
) VALUES
(ID, CONF_ID, 'TITLE', 'DESC', 'DATE', 'START', 'END', 'TYPE', 'ROOM', CAPACITY, SPEAKER_ID, 
NULL, NULL, NULL, NULL, NULL, 0, 0, 'LEVEL', '["tags"]', 'STATUS', ORDER_NUM);
*/

-- Script để sửa lỗi này:
-- 1. Tìm tất cả INSERT INTO schedule_sessions
-- 2. Đảm bảo tất cả đều có 22 cột (không tính duration, created_at, updated_at - auto generated)
-- 3. Thêm NULL cho các cột thiếu

-- ========================================================
-- SCRIPT CHÍNH SỬA LỖI
-- ========================================================

-- Sửa lỗi duplicate bằng cách xóa trước khi insert
DELETE FROM `user_activity_logs` WHERE id IN (1,2,3,4);
DELETE FROM `invoices` WHERE invoice_number IN ('INV-2024-001', 'INV-2024-002', 'INV-2024-003');
DELETE FROM `invoice_items` WHERE invoice_id IN (1,2,3);
DELETE FROM `transactions` WHERE transaction_id IN ('TXN-2024-001', 'TXN-2024-002');
DELETE FROM `error_logs` WHERE id IS NOT NULL OR id IS NULL;

-- ========================================================
-- CÁCH SỬA LỖI TRONG FILE GỐC
-- ========================================================

-- 1. Mở file schema_complete.sql
-- 2. Tìm dòng có nội dung: "-- Dữ liệu mẫu cho bảng user_activity_logs" (dòng ~1725)
-- 3. Thêm các DELETE statements ngay trước dòng đó:

/*
-- Xóa dữ liệu cũ để tránh duplicate entries
DELETE FROM `user_activity_logs`;
DELETE FROM `invoices`;
DELETE FROM `invoice_items`;
DELETE FROM `transactions`;
DELETE FROM `error_logs`;

-- Dữ liệu mẫu cho bảng user_activity_logs
*/

-- 4. Tìm tất cả INSERT INTO schedule_sessions có format sai
-- 5. Sửa các VALUES có thiếu cột như sau:

-- LỖI: (54, 3, 'Title', 'Desc', '2025-09-20', '10:15:00', '11:15:00', 'workshop', 'Room A', 50, 8, 'intermediate', '["tags"]', 'scheduled'),
-- ĐÚNG: (54, 3, 'Title', 'Desc', '2025-09-20', '10:15:00', '11:15:00', 'workshop', 'Room A', 50, 8, NULL, NULL, NULL, NULL, NULL, 0, 0, 'intermediate', '["tags"]', 'scheduled', 54),

-- Script PowerShell để tự động sửa file:
/*
# Backup file gốc
Copy-Item "schema_complete.sql" "schema_complete_backup.sql"

# Sử dụng PowerShell để thay thế
$content = Get-Content "schema_complete.sql" -Raw
$content = $content -replace "-- Dữ liệu mẫu cho bảng user_activity_logs", "-- Xóa dữ liệu cũ để tránh duplicate entries`nDELETE FROM \`user_activity_logs\`;`nDELETE FROM \`invoices\`;`nDELETE FROM \`invoice_items\`;`nDELETE FROM \`transactions\`;`nDELETE FROM \`error_logs\`;`n`n-- Dữ liệu mẫu cho bảng user_activity_logs"
Set-Content "schema_complete.sql" $content
*/

-- HƯỚNG DẪN SỬA THỦ CÔNG:
-- Tìm các dòng có format sai trong schedule_sessions và thay thế:
-- Từ: 'workshop', 'Room A', 50, 8, 'intermediate', '["tags"]', 'scheduled'),
-- Thành: 'workshop', 'Room A', 50, 8, NULL, NULL, NULL, NULL, NULL, 0, 0, 'intermediate', '["tags"]', 'scheduled', ID),
