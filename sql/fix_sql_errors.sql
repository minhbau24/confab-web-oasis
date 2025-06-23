-- ========================================================
-- FIX SCRIPT FOR REMAINING SQL ERRORS
-- Sửa 3 lỗi còn lại trong schema_complete.sql
-- ========================================================

-- BƯỚC 1: XÓA DỮ LIỆU CŨ ĐỂ TRÁNH DUPLICATE ENTRIES
-- Chạy trước khi import schema_complete.sql

DELETE FROM `user_activity_logs`;
DELETE FROM `invoices`;
DELETE FROM `invoice_items`;
DELETE FROM `transactions`;
DELETE FROM `error_logs`;

-- Xóa các session có thể bị lỗi column count
DELETE FROM `schedule_sessions` WHERE id > 50;

-- BƯỚC 2: THÊM DỮ LIỆU ĐÚNG CHO CÁC BẢNG BỊ DUPLICATE

-- Dữ liệu mẫu cho bảng user_activity_logs
INSERT INTO `user_activity_logs` (`user_id`, `activity_type`, `description`, `entity_type`, `entity_id`, `ip_address`, `device_type`, `os`, `browser`) VALUES
(1, 'login', 'Đăng nhập hệ thống', 'user', 1, '127.0.0.1', 'desktop', 'Windows', 'Chrome'),
(2, 'create_conference', 'Tạo hội nghị mới', 'conference', 1, '127.0.0.1', 'desktop', 'Windows', 'Chrome'),
(3, 'register_conference', 'Đăng ký tham dự hội nghị', 'conference', 1, '127.0.0.1', 'mobile', 'Android', 'Chrome'),
(4, 'view_profile', 'Xem trang cá nhân', 'user', 4, '127.0.0.1', 'desktop', 'macOS', 'Safari');

-- Dữ liệu mẫu cho bảng invoices
INSERT INTO `invoices` (`invoice_number`, `user_id`, `conference_id`, `amount_subtotal`, `amount_total`, `currency`, `status`, `due_date`) VALUES
('INV-2024-001', 3, 1, 2500000.00, 2500000.00, 'VND', 'paid', '2024-12-10 23:59:59'),
('INV-2024-002', 4, 2, 500000.00, 500000.00, 'VND', 'sent', '2024-11-25 23:59:59'),
('INV-2024-003', 3, 3, 1500000.00, 1500000.00, 'VND', 'draft', '2024-12-15 23:59:59');

-- Dữ liệu mẫu cho bảng invoice_items
INSERT INTO `invoice_items` (`invoice_id`, `description`, `quantity`, `unit_price`) VALUES
(1, 'Vietnam Tech Summit 2024 - Vé thường', 1, 2500000.00),
(2, 'Startup Weekend Ho Chi Minh - Vé thường', 1, 500000.00),
(3, 'Digital Health Conference 2024 - Vé thường', 1, 1500000.00);

-- Dữ liệu mẫu cho bảng transactions
INSERT INTO `transactions` (`transaction_id`, `user_id`, `conference_id`, `invoice_id`, `payment_method_id`, `type`, `amount`, `currency`, `status`, `gateway`, `payment_date`) VALUES
('TXN-2024-001', 3, 1, 1, 2, 'payment', 2500000.00, 'VND', 'completed', 'momo', '2024-12-01 10:30:00'),
('TXN-2024-002', 4, 2, 2, 1, 'payment', 500000.00, 'VND', 'pending', 'bank_transfer', NULL);

-- Dữ liệu mẫu cho bảng error_logs
INSERT INTO `error_logs` (`user_id`, `level`, `message`, `exception_class`, `file`, `line`, `ip_address`, `user_agent`) VALUES
(NULL, 'error', 'Database connection timeout', 'PDOException', '/includes/database.php', 25, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, 'warning', 'File upload size exceeded', 'FileUploadException', '/api/upload.php', 45, '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'),
(NULL, 'info', 'Scheduled task completed successfully', NULL, '/cron/backup.php', 10, '127.0.0.1', 'CLI');

-- BƯỚC 3: THÊM CÁC SCHEDULE_SESSIONS BỊ THIẾU CỘT (FIX LỖI COLUMN COUNT)

-- Thêm các session còn lại với đúng format 22 cột
INSERT INTO `schedule_sessions` (
    `id`, `conference_id`, `title`, `description`, `session_date`, `start_time`, `end_time`, 
    `type`, `room`, `capacity`, `speaker_id`, `additional_speakers`, `materials`, `slides_url`, 
    `video_url`, `live_stream_url`, `is_mandatory`, `requires_registration`, `level`, `tags`, 
    `status`, `sort_order`
) VALUES
-- Digital Health Conference 2025 (Conference ID: 3) - Sessions bị thiếu cột
(54, 3, 'Telemedicine Implementation Workshop', 'Workshop thực hành về telemedicine', 
'2025-09-20', '10:15:00', '11:15:00', 'workshop', 'Workshop Room A', 50, 8, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'intermediate', '["telemedicine", "workshop"]', 'scheduled', 54),

(55, 3, 'Panel: Future of Healthcare Technology', 'Panel discussion với các chuyên gia', 
'2025-09-20', '11:30:00', '12:30:00', 'panel', 'Main Auditorium', 300, 2, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["panel", "future", "healthcare"]', 'scheduled', 55),

(56, 3, 'Lunch Break', 'Nghỉ trưa', 
'2025-09-20', '12:30:00', '13:30:00', 'lunch', 'Restaurant', 300, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["lunch"]', 'scheduled', 56),

(57, 3, 'Digital Health Regulations & Compliance', 'Presentation about legal aspects', 
'2025-09-20', '13:30:00', '14:30:00', 'presentation', 'Conference Room B', 100, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'advanced', '["regulation", "compliance", "legal"]', 'scheduled', 57),

(58, 3, 'Closing Remarks & Networking', 'Kết thúc hội nghị và networking', 
'2025-09-20', '16:30:00', '17:30:00', 'networking', 'Main Auditorium', 300, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["closing", "networking"]', 'scheduled', 58),

-- AI & ML Workshop 2025 (Conference ID: 4) - Day 1
(59, 4, 'Workshop Opening & Introduction', 'Giới thiệu workshop và setup environment', 
'2025-08-25', '09:00:00', '09:30:00', 'keynote', 'Lab Room 1', 50, 1, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'beginner', '["opening", "introduction"]', 'scheduled', 59),

(60, 4, 'Machine Learning Fundamentals', 'Cơ bản về ML và data preprocessing', 
'2025-08-25', '09:30:00', '11:00:00', 'workshop', 'Lab Room 1', 50, 1, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'beginner', '["ML", "fundamentals", "preprocessing"]', 'scheduled', 60),

(61, 4, 'Coffee Break', 'Giải lao', 
'2025-08-25', '11:00:00', '11:15:00', 'break', 'Break Area', 50, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["break"]', 'scheduled', 61),

(62, 4, 'Hands-on: Building Your First ML Model', 'Thực hành xây dựng model đầu tiên', 
'2025-08-25', '11:15:00', '12:45:00', 'workshop', 'Lab Room 1', 50, 1, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'beginner', '["hands-on", "ML", "model"]', 'scheduled', 62),

(63, 4, 'Lunch Break', 'Nghỉ trưa', 
'2025-08-25', '12:45:00', '14:00:00', 'lunch', 'Cafeteria', 50, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["lunch"]', 'scheduled', 63),

(64, 4, 'Deep Learning Introduction', 'Giới thiệu neural networks và deep learning', 
'2025-08-25', '14:00:00', '15:30:00', 'workshop', 'Lab Room 1', 50, 1, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'intermediate', '["deep learning", "neural networks"]', 'scheduled', 64),

(65, 4, 'Computer Vision Workshop', 'Thực hành với image processing và CV', 
'2025-08-25', '15:45:00', '17:00:00', 'workshop', 'Lab Room 1', 50, 9, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'intermediate', '["computer vision", "image processing"]', 'scheduled', 65),

-- AI & Machine Learning Workshop 2025 - Day 2
(66, 4, 'Day 2 Opening & Recap', 'Mở đầu ngày 2 và review ngày 1', 
'2025-08-26', '09:00:00', '09:30:00', 'presentation', 'Lab Room 1', 50, 1, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["opening", "recap"]', 'scheduled', 66),

(67, 4, 'Advanced Deep Learning Techniques', 'Kỹ thuật advanced trong deep learning', 
'2025-08-26', '09:30:00', '11:00:00', 'workshop', 'Lab Room 1', 50, 1, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'advanced', '["advanced", "deep learning"]', 'scheduled', 67),

(68, 4, 'Model Deployment & Production', 'Deploy ML models vào production environment', 
'2025-08-26', '11:15:00', '12:30:00', 'workshop', 'Lab Room 1', 50, 1, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'intermediate', '["deployment", "production", "MLOps"]', 'scheduled', 68),

(69, 4, 'Final Project Presentations', 'Thuyết trình projects của participants', 
'2025-08-26', '15:00:00', '16:30:00', 'presentation', 'Lab Room 1', 50, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["presentation", "projects"]', 'scheduled', 69),

(70, 4, 'Workshop Closing & Certificates', 'Kết thúc workshop và trao chứng chỉ', 
'2025-08-26', '16:30:00', '17:00:00', 'keynote', 'Lab Room 1', 50, 1, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["closing", "certificates"]', 'scheduled', 70),

-- Blockchain & Web3 Summit 2025 - Day 1
(71, 5, 'Summit Opening Ceremony', 'Lễ khai mạc Blockchain & Web3 Summit', 
'2025-10-05', '08:00:00', '08:30:00', 'keynote', 'Grand Hall', 800, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["opening", "ceremony"]', 'scheduled', 71),

(72, 5, 'The Future of Decentralized Finance', 'Keynote by blockchain expert', 
'2025-10-05', '08:30:00', '09:30:00', 'keynote', 'Grand Hall', 800, 10, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'intermediate', '["DeFi", "blockchain", "keynote"]', 'scheduled', 72),

(73, 5, 'Coffee Break & Expo Visit', 'Thăm triển lãm blockchain projects', 
'2025-10-05', '09:30:00', '10:00:00', 'break', 'Expo Area', 800, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["break", "expo"]', 'scheduled', 73),

(74, 5, 'Smart Contract Development Workshop', 'Hands-on smart contract coding', 
'2025-10-05', '10:00:00', '11:30:00', 'workshop', 'Dev Room A', 100, 11, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'intermediate', '["smart contracts", "development"]', 'scheduled', 74),

(75, 5, 'NFT & Digital Assets Panel', 'Panel discussion về NFT market', 
'2025-10-05', '11:45:00', '12:45:00', 'panel', 'Grand Hall', 800, 10, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'beginner', '["NFT", "digital assets", "panel"]', 'scheduled', 75),

(76, 5, 'Networking Lunch', 'Lunch và networking', 
'2025-10-05', '12:45:00', '14:00:00', 'lunch', 'Restaurant Area', 800, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["lunch", "networking"]', 'scheduled', 76),

(77, 5, 'Web3 Gaming & Metaverse', 'Presentation about gaming on blockchain', 
'2025-10-05', '14:00:00', '15:00:00', 'presentation', 'Gaming Hall', 200, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'intermediate', '["gaming", "metaverse", "web3"]', 'scheduled', 77),

(78, 5, 'Crypto Investment Strategies', 'Workshop về investment và trading', 
'2025-10-05', '15:15:00', '16:15:00', 'workshop', 'Trading Room', 150, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'advanced', '["investment", "trading", "crypto"]', 'scheduled', 78),

(79, 5, 'Day 1 Closing & Evening Networking', 'Kết thúc ngày 1', 
'2025-10-05', '17:00:00', '18:00:00', 'networking', 'Grand Hall', 800, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["closing", "networking"]', 'scheduled', 79),

-- Blockchain & Web3 Summit - Day 2
(80, 5, 'Day 2 Opening & Market Updates', 'Cập nhật thị trường crypto', 
'2025-10-06', '08:30:00', '09:00:00', 'presentation', 'Grand Hall', 800, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["opening", "market update"]', 'scheduled', 80),

(81, 5, 'Regulatory Landscape in Vietnam', 'Quy định pháp lý về blockchain tại VN', 
'2025-10-06', '09:00:00', '10:00:00', 'presentation', 'Grand Hall', 800, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'intermediate', '["regulation", "legal", "vietnam"]', 'scheduled', 81),

(82, 5, 'Startup Pitch Competition', 'Cuộc thi pitch các startup blockchain', 
'2025-10-06', '10:15:00', '12:00:00', 'presentation', 'Grand Hall', 800, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["startup", "pitch", "competition"]', 'scheduled', 82),

(83, 5, 'Closing Ceremony & Awards', 'Lễ bế mạc và trao giải', 
'2025-10-06', '17:00:00', '18:00:00', 'keynote', 'Grand Hall', 800, NULL, NULL, NULL, NULL, 
NULL, NULL, 0, 0, 'all_levels', '["closing", "ceremony", "awards"]', 'scheduled', 83);

-- ========================================================
-- HƯỚNG DẪN SỬ DỤNG
-- ========================================================

/*
CÁCH SỬ DỤNG FILE NÀY:

1. Backup file schema_complete.sql gốc:
   Copy-Item "schema_complete.sql" "schema_complete_backup.sql"

2. Chạy file này TRƯỚC KHI chạy schema_complete.sql:
   - Import file fix_sql_errors.sql vào database trước
   - Sau đó mới import schema_complete.sql

3. Hoặc chạy file này SAU KHI gặp lỗi:
   - Nếu đã import schema_complete.sql và gặp lỗi
   - Chạy file này để sửa lỗi

HOẶC:

Sửa trực tiếp trong file schema_complete.sql:
1. Tìm dòng 1725 có "-- Dữ liệu mẫu cho bảng user_activity_logs"
2. Thêm các DELETE statements ở trên TRƯỚC dòng đó
3. Xóa toàn bộ phần duplicate từ dòng 2134 trở đi (phần COMPLETION MESSAGE thứ hai)
4. Sửa tất cả các INSERT INTO schedule_sessions có thiếu cột theo format trên

FILE NÀY ĐÃ SỬA:
✅ Lỗi 87: Column count doesn't match value count at row 8
✅ Lỗi 112: Duplicate entry 'INV-2024-001' for key 'invoice_number'  
✅ Lỗi 114: Duplicate entry 'TXN-2024-001' for key 'transaction_id'
*/
