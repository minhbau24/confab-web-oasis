-- ========================================================
-- Schema SQL cho dự án Confab Web Oasis - Hệ thống quản lý Hội nghị
-- Tác giả: Confab Web Oasis Team
-- Ngày tạo: 22/06/2025
-- Phiên bản: 2.0
-- ========================================================

-- --------------------------------------------------------
-- Tạo cơ sở dữ liệu và cài đặt charset
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `confab_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `confab_db`;

-- --------------------------------------------------------
-- Cấu trúc bảng `users` - Người dùng
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','organizer','admin') DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `categories` - Danh mục hội nghị
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `venues` - Địa điểm tổ chức
-- --------------------------------------------------------
DROP TABLE IF EXISTS `venues`;
CREATE TABLE `venues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Vietnam',
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `facilities` json DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_city` (`city`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `speakers` - Diễn giả
-- --------------------------------------------------------
DROP TABLE IF EXISTS `speakers`;
CREATE TABLE `speakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `specialties` json DEFAULT NULL,
  `languages` json DEFAULT NULL,
  `fee_range` varchar(50) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `total_talks` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `speakers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `conferences` - Hội nghị
-- --------------------------------------------------------
DROP TABLE IF EXISTS `conferences`;
CREATE TABLE `conferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` text NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'Asia/Ho_Chi_Minh',
  `category_id` int(11) DEFAULT NULL,
  `venue_id` int(11) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'VND',
  `early_bird_price` decimal(10,2) DEFAULT NULL,
  `early_bird_until` datetime DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `min_attendees` int(11) DEFAULT 1,
  `registration_start` datetime DEFAULT NULL,
  `registration_end` datetime DEFAULT NULL,
  `status` enum('draft','published','cancelled','completed','postponed') DEFAULT 'draft',
  `visibility` enum('public','private','invite_only') DEFAULT 'public',
  `featured` tinyint(1) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `agenda` json DEFAULT NULL,
  `organizer_name` varchar(100) DEFAULT NULL,
  `organizer_email` varchar(100) DEFAULT NULL,
  `organizer_phone` varchar(20) DEFAULT NULL,
  `contact_info` json DEFAULT NULL,
  `social_links` json DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  `refund_policy` text DEFAULT NULL,
  `certificate_available` tinyint(1) DEFAULT 0,
  `certificate_template` varchar(255) DEFAULT NULL,
  `feedback_form` json DEFAULT NULL,
  `external_url` varchar(255) DEFAULT NULL,
  `live_stream_url` varchar(255) DEFAULT NULL,
  `recording_url` varchar(255) DEFAULT NULL,
  `sponsor_info` json DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `venue_id` (`venue_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`featured`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_location` (`location`),
  CONSTRAINT `conferences_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conferences_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conferences_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conferences_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `conference_speakers` - Liên kết hội nghị với diễn giả
-- --------------------------------------------------------
DROP TABLE IF EXISTS `conference_speakers`;
CREATE TABLE `conference_speakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) NOT NULL,
  `speaker_id` int(11) NOT NULL,
  `role` enum('keynote','speaker','panelist','moderator','mc') DEFAULT 'speaker',
  `session_title` varchar(255) DEFAULT NULL,
  `session_description` text DEFAULT NULL,
  `session_start` datetime DEFAULT NULL,
  `session_end` datetime DEFAULT NULL,
  `session_order` int(11) DEFAULT 0,
  `fee` decimal(10,2) DEFAULT NULL,
  `travel_covered` tinyint(1) DEFAULT 0,
  `accommodation_covered` tinyint(1) DEFAULT 0,
  `status` enum('invited','confirmed','declined','cancelled') DEFAULT 'invited',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `conference_speaker_unique` (`conference_id`,`speaker_id`),
  KEY `speaker_id` (`speaker_id`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  CONSTRAINT `conference_speakers_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conference_speakers_ibfk_2` FOREIGN KEY (`speaker_id`) REFERENCES `speakers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `registrations` - Đăng ký tham dự hội nghị
-- --------------------------------------------------------
DROP TABLE IF EXISTS `registrations`;
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `registration_type` enum('regular','early_bird','vip','student','group') DEFAULT 'regular',
  `ticket_code` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `dietary_requirements` varchar(255) DEFAULT NULL,
  `special_needs` text DEFAULT NULL,
  `emergency_contact` json DEFAULT NULL,
  `price_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'VND',
  `payment_method` enum('free','credit_card','bank_transfer','paypal','vnpay','momo','cash') DEFAULT 'free',
  `payment_status` enum('pending','paid','failed','refunded','cancelled') DEFAULT 'pending',
  `payment_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `status` enum('pending','confirmed','checked_in','attended','cancelled','no_show') DEFAULT 'pending',
  `confirmation_code` varchar(50) DEFAULT NULL,
  `check_in_time` timestamp NULL DEFAULT NULL,
  `check_out_time` timestamp NULL DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `badge_printed` tinyint(1) DEFAULT 0,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `feedback_submitted` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `source` varchar(100) DEFAULT 'direct',
  `referral_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_code` (`ticket_code`),
  UNIQUE KEY `user_conference_unique` (`user_id`,`conference_id`),
  KEY `conference_id` (`conference_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_confirmation_code` (`confirmation_code`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `schedule_sessions` - Lịch trình phiên hội nghị
-- --------------------------------------------------------
DROP TABLE IF EXISTS `schedule_sessions`;
CREATE TABLE `schedule_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `session_type` enum('session','break','lunch','networking','workshop','panel') DEFAULT 'session',
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `room` varchar(100) DEFAULT NULL,
  `max_attendees` int(11) DEFAULT NULL,
  `is_break` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `live_stream_url` varchar(255) DEFAULT NULL,
  `materials` json DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conference_id` (`conference_id`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_session_type` (`session_type`),
  CONSTRAINT `schedule_sessions_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `session_speakers` - Diễn giả cho từng phiên
-- --------------------------------------------------------
DROP TABLE IF EXISTS `session_speakers`;
CREATE TABLE `session_speakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `speaker_id` int(11) NOT NULL,
  `role` enum('presenter','moderator','panelist') DEFAULT 'presenter',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_speaker_unique` (`session_id`,`speaker_id`),
  KEY `speaker_id` (`speaker_id`),
  CONSTRAINT `session_speakers_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `schedule_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_speakers_ibfk_2` FOREIGN KEY (`speaker_id`) REFERENCES `speakers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `feedback` - Phản hồi và đánh giá
-- --------------------------------------------------------
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `speaker_id` int(11) DEFAULT NULL,
  `type` enum('conference','session','speaker','venue','organization') DEFAULT 'conference',
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `suggestions` text DEFAULT NULL,
  `recommend` tinyint(1) DEFAULT NULL,
  `anonymous` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `conference_id` (`conference_id`),
  KEY `session_id` (`session_id`),
  KEY `speaker_id` (`speaker_id`),
  KEY `idx_type` (`type`),
  KEY `idx_rating` (`rating`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `schedule_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_ibfk_4` FOREIGN KEY (`speaker_id`) REFERENCES `speakers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `certificates` - Chứng chỉ tham dự
-- --------------------------------------------------------
DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `certificate_number` varchar(100) NOT NULL,
  `recipient_name` varchar(200) NOT NULL,
  `issue_date` date NOT NULL,
  `template_used` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `verification_code` varchar(100) NOT NULL,
  `status` enum('generated','issued','revoked') DEFAULT 'generated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificate_number` (`certificate_number`),
  UNIQUE KEY `verification_code` (`verification_code`),
  UNIQUE KEY `user_conference_cert` (`user_id`,`conference_id`),
  KEY `registration_id` (`registration_id`),
  KEY `conference_id` (`conference_id`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_3` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `notifications` - Thông báo
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_read_at` (`read_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `settings` - Cài đặt hệ thống
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `group` varchar(50) DEFAULT 'general',
  `is_public` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng `audit_logs` - Nhật ký hoạt động
-- --------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tạo các indexes tối ưu hóa hiệu năng
-- --------------------------------------------------------

-- Index cho truy vấn thường xuyên
CREATE INDEX idx_conferences_date_status ON conferences(start_date, status);
CREATE INDEX idx_registrations_user_status ON registrations(user_id, status);
CREATE INDEX idx_feedback_conference_rating ON feedback(conference_id, rating);

-- --------------------------------------------------------
-- Tạo các views hữu ích
-- --------------------------------------------------------

-- View thống kê hội nghị
CREATE OR REPLACE VIEW conference_stats AS
SELECT 
    c.id,
    c.title,
    c.capacity,
    COUNT(r.id) as total_registrations,
    COUNT(CASE WHEN r.status = 'confirmed' THEN 1 END) as confirmed_registrations,
    COUNT(CASE WHEN r.status = 'attended' THEN 1 END) as attended_count,
    ROUND(AVG(f.rating), 2) as average_rating,
    COUNT(f.id) as feedback_count
FROM conferences c
LEFT JOIN registrations r ON c.id = r.conference_id
LEFT JOIN feedback f ON c.id = f.conference_id AND f.type = 'conference'
GROUP BY c.id;

-- View thống kê người dùng
CREATE OR REPLACE VIEW user_stats AS
SELECT 
    u.id,
    u.firstName,
    u.lastName,
    u.email,
    COUNT(r.id) as total_registrations,
    COUNT(CASE WHEN r.status = 'attended' THEN 1 END) as conferences_attended,
    COUNT(c.id) as certificates_earned,
    COUNT(f.id) as feedback_given,
    u.created_at
FROM users u
LEFT JOIN registrations r ON u.id = r.user_id
LEFT JOIN certificates c ON u.id = c.user_id
LEFT JOIN feedback f ON u.id = f.user_id
GROUP BY u.id;

-- --------------------------------------------------------
-- Tạo triggers để tự động cập nhật
-- --------------------------------------------------------

DELIMITER $$

-- Trigger cập nhật số lượng attendees khi có đăng ký mới
CREATE TRIGGER update_conference_attendees_after_registration_insert
AFTER INSERT ON registrations
FOR EACH ROW
BEGIN
    UPDATE conferences 
    SET attendees = (
        SELECT COUNT(*) 
        FROM registrations 
        WHERE conference_id = NEW.conference_id 
        AND status IN ('confirmed', 'attended')
    )
    WHERE id = NEW.conference_id;
END$$

-- Trigger cập nhật số lượng attendees khi cập nhật trạng thái đăng ký
CREATE TRIGGER update_conference_attendees_after_registration_update
AFTER UPDATE ON registrations
FOR EACH ROW
BEGIN
    UPDATE conferences 
    SET attendees = (
        SELECT COUNT(*) 
        FROM registrations 
        WHERE conference_id = NEW.conference_id 
        AND status IN ('confirmed', 'attended')
    )
    WHERE id = NEW.conference_id;
END$$

-- Trigger tạo mã ticket tự động
CREATE TRIGGER generate_ticket_code_before_registration_insert
BEFORE INSERT ON registrations
FOR EACH ROW
BEGIN
    IF NEW.ticket_code IS NULL OR NEW.ticket_code = '' THEN
        SET NEW.ticket_code = CONCAT('CONF', NEW.conference_id, '-', UNIX_TIMESTAMP(), '-', SUBSTRING(MD5(RAND()), 1, 4));
    END IF;
    
    IF NEW.confirmation_code IS NULL OR NEW.confirmation_code = '' THEN
        SET NEW.confirmation_code = SUBSTRING(MD5(CONCAT(NEW.user_id, NEW.conference_id, NOW())), 1, 8);
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- Cấu hình bảo mật và hiệu năng
-- --------------------------------------------------------

-- Tối ưu hóa bộ nhớ cho MyISAM (nếu cần)
-- SET GLOBAL key_buffer_size = 128M;

-- Tối ưu hóa cho InnoDB
-- SET GLOBAL innodb_buffer_pool_size = 256M;

-- --------------------------------------------------------
-- Kết thúc schema
-- --------------------------------------------------------
