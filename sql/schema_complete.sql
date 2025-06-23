-- ========================================================
-- Confab Web Oasis - Complete Database Schema
-- Hệ thống quản lý hội nghị hoàn chỉnh và tối ưu
-- Phiên bản: 3.0 (Complete Edition)
-- Ngày tạo: 22/06/2025
-- Mô tả: Schema database hoàn chỉnh với tất cả tính năng cần thiết
-- ========================================================

-- Tạo database và thiết lập charset
CREATE DATABASE IF NOT EXISTS `confab_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `confab_db`;

-- Thiết lập múi giờ
SET time_zone = '+07:00';

-- ========================================================
-- INTERNATIONALIZATION TABLES - Bảng đa ngôn ngữ
-- ========================================================

-- Bảng languages - Ngôn ngữ hỗ trợ
DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `native_name` varchar(100) NOT NULL,
  `direction` enum('ltr','rtl') DEFAULT 'ltr',
  `flag` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng translations - Bản dịch
DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_code` varchar(10) NOT NULL,
  `translation_key` varchar(255) NOT NULL,
  `translation_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_key_unique` (`lang_code`,`translation_key`),
  KEY `idx_lang_code` (`lang_code`),
  KEY `idx_translation_key` (`translation_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- PAYMENT TABLES - Bảng thanh toán
-- ========================================================

-- Bảng payment_methods - Phương thức thanh toán
DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('bank_transfer','credit_card','e_wallet','paypal','cash') NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'VND',
  `is_active` tinyint(1) DEFAULT 1,
  `config` json DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- CORE TABLES - Bảng cốt lõi
-- ========================================================

-- Bảng users - Người dùng
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Mật khẩu được mã hóa sử dụng PHP password_hash() với thuật toán bcrypt (PASSWORD_DEFAULT). KHÔNG sử dụng MD5 hoặc SHA1 vì không an toàn.',
  `password_algorithm` varchar(20) DEFAULT 'BCRYPT' COMMENT 'Thuật toán mã hóa sử dụng cho mật khẩu (BCRYPT, ARGON2ID)',
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `role` enum('user','organizer','speaker','admin') DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Vietnam',
  `timezone` varchar(50) DEFAULT 'Asia/Ho_Chi_Minh',
  `language` varchar(10) DEFAULT 'vi',
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `email_verification_sent_at` timestamp NULL DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `password_reset_sent_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','suspended','pending') DEFAULT 'active',
  `preferences` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `idx_last_login` (`last_login`),
  KEY `idx_email_verified` (`email_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng categories - Danh mục hội nghị
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `icon` varchar(50) DEFAULT 'fas fa-calendar',
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`is_featured`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng venues - Địa điểm tổ chức
DROP TABLE IF EXISTS `venues`;
CREATE TABLE `venues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Vietnam',
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `rooms` json DEFAULT NULL,
  `facilities` json DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `parking_info` text DEFAULT NULL,
  `transport_info` text DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `virtual_tour_url` varchar(255) DEFAULT NULL,
  `pricing` json DEFAULT NULL,
  `availability` json DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `total_events` int(11) DEFAULT 0,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_city` (`city`),
  KEY `idx_status` (`status`),
  KEY `idx_rating` (`rating`),
  KEY `idx_capacity` (`capacity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng speakers - Diễn giả
DROP TABLE IF EXISTS `speakers`;
CREATE TABLE `speakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `short_bio` varchar(500) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `specialties` json DEFAULT NULL,
  `languages` json DEFAULT NULL,
  `topics` json DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `fee_range` varchar(50) DEFAULT NULL,
  `travel_preference` json DEFAULT NULL,
  `availability` json DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `total_talks` int(11) DEFAULT 0,
  `total_ratings` int(11) DEFAULT 0,
  `featured_video` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `testimonials` json DEFAULT NULL,
  `status` enum('active','inactive','unavailable') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_rating` (`rating`),
  KEY `idx_total_talks` (`total_talks`),
  CONSTRAINT `speakers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- CONFERENCE TABLES - Bảng hội nghị
-- ========================================================

-- Bảng conferences - Hội nghị chính
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
  `address` text DEFAULT NULL,
  `type` enum('in_person','online','hybrid') DEFAULT 'in_person',
  `format` enum('conference','workshop','seminar','webinar','meetup') DEFAULT 'conference',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'VND',
  `early_bird_price` decimal(10,2) DEFAULT NULL,
  `early_bird_until` datetime DEFAULT NULL,
  `group_discount` json DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `min_attendees` int(11) DEFAULT 1,
  `current_attendees` int(11) DEFAULT 0,
  `waiting_list_limit` int(11) DEFAULT 0,
  `registration_start` datetime DEFAULT NULL,
  `registration_end` datetime DEFAULT NULL,
  `status` enum('draft','published','sold_out','cancelled','completed','postponed') DEFAULT 'draft',
  `visibility` enum('public','private','invite_only') DEFAULT 'public',
  `featured` tinyint(1) DEFAULT 0,
  `trending` tinyint(1) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `level` enum('beginner','intermediate','advanced','all_levels') DEFAULT 'all_levels',
  `language` varchar(10) DEFAULT 'vi',
  `requirements` text DEFAULT NULL,
  `what_you_learn` json DEFAULT NULL,
  `agenda` json DEFAULT NULL,
  `materials` json DEFAULT NULL,
  `organizer_name` varchar(100) DEFAULT NULL,
  `organizer_email` varchar(100) DEFAULT NULL,
  `organizer_phone` varchar(20) DEFAULT NULL,
  `organizer_company` varchar(100) DEFAULT NULL,
  `contact_info` json DEFAULT NULL,
  `social_links` json DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  `refund_policy` text DEFAULT NULL,
  `privacy_policy` text DEFAULT NULL,
  `certificate_available` tinyint(1) DEFAULT 0,
  `certificate_template` varchar(255) DEFAULT NULL,
  `certificate_criteria` json DEFAULT NULL,
  `feedback_form` json DEFAULT NULL,
  `external_url` varchar(255) DEFAULT NULL,
  `live_stream_url` varchar(255) DEFAULT NULL,
  `recording_url` varchar(255) DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `meeting_password` varchar(100) DEFAULT NULL,
  `sponsor_info` json DEFAULT NULL,
  `partner_info` json DEFAULT NULL,
  `special_offers` json DEFAULT NULL,
  `discount_codes` json DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `meta_data` json DEFAULT NULL,
  `analytics_data` json DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
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
  KEY `idx_trending` (`trending`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_end_date` (`end_date`),
  KEY `idx_location` (`location`),
  KEY `idx_type` (`type`),
  KEY `idx_price` (`price`),
  KEY `idx_deleted` (`deleted_at`),
  CONSTRAINT `conferences_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conferences_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conferences_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conferences_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng conference_speakers - Liên kết hội nghị với diễn giả
DROP TABLE IF EXISTS `conference_speakers`;
CREATE TABLE `conference_speakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) NOT NULL,
  `speaker_id` int(11) NOT NULL,
  `role` enum('keynote','speaker','panelist','moderator','facilitator') DEFAULT 'speaker',
  `bio_override` text DEFAULT NULL,
  `talk_title` varchar(255) DEFAULT NULL,
  `talk_description` text DEFAULT NULL,
  `talk_duration` int(11) DEFAULT NULL,
  `talk_slides_url` varchar(255) DEFAULT NULL,
  `talk_video_url` varchar(255) DEFAULT NULL,
  `speaking_fee` decimal(10,2) DEFAULT NULL,
  `travel_required` tinyint(1) DEFAULT 0,
  `accommodation_required` tinyint(1) DEFAULT 0,
  `special_requirements` text DEFAULT NULL,
  `status` enum('invited','confirmed','declined','cancelled') DEFAULT 'invited',
  `invited_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `conference_speaker_unique` (`conference_id`,`speaker_id`),
  KEY `speaker_id` (`speaker_id`),
  KEY `idx_status` (`status`),
  KEY `idx_role` (`role`),
  CONSTRAINT `conference_speakers_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conference_speakers_ibfk_2` FOREIGN KEY (`speaker_id`) REFERENCES `speakers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- REGISTRATION TABLES - Bảng đăng ký
-- ========================================================

-- Bảng registrations - Đăng ký tham dự
DROP TABLE IF EXISTS `registrations`;
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `registration_code` varchar(20) NOT NULL,
  `ticket_type` enum('regular','early_bird','vip','student','group','complimentary') DEFAULT 'regular',
  `quantity` int(11) DEFAULT 1,
  `price_paid` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'VND',
  `discount_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` enum('cash','bank_transfer','credit_card','paypal','momo','zalopay') DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded','cancelled') DEFAULT 'pending',
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','attended','no_show','refunded') DEFAULT 'pending',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmation_date` timestamp NULL DEFAULT NULL,
  `check_in_date` timestamp NULL DEFAULT NULL,
  `additional_info` json DEFAULT NULL,
  `dietary_requirements` text DEFAULT NULL,
  `accessibility_needs` text DEFAULT NULL,
  `emergency_contact` json DEFAULT NULL,
  `marketing_consent` tinyint(1) DEFAULT 0,
  `certificate_requested` tinyint(1) DEFAULT 0,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `certificate_issued_at` timestamp NULL DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `invitation_sent` tinyint(1) DEFAULT 0,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `feedback_submitted` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `registration_code` (`registration_code`),
  UNIQUE KEY `user_conference_unique` (`user_id`,`conference_id`),
  KEY `conference_id` (`conference_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_ticket_type` (`ticket_type`),
  KEY `idx_registration_date` (`registration_date`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng waiting_list - Danh sách chờ
DROP TABLE IF EXISTS `waiting_list`;
CREATE TABLE `waiting_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `ticket_type` enum('regular','early_bird','vip','student','group') DEFAULT 'regular',
  `max_price` decimal(10,2) DEFAULT NULL,
  `notification_sent` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('waiting','notified','converted','expired','cancelled') DEFAULT 'waiting',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_conference_unique` (`user_id`,`conference_id`),
  KEY `conference_id` (`conference_id`),
  KEY `idx_status` (`status`),
  KEY `idx_position` (`position`),
  CONSTRAINT `waiting_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `waiting_list_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SCHEDULE TABLES - Bảng lịch trình
-- ========================================================

-- Bảng schedule_sessions - Phiên lịch trình
DROP TABLE IF EXISTS `schedule_sessions`;
CREATE TABLE `schedule_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration` int(11) GENERATED ALWAYS AS (TIME_TO_SEC(`end_time`) - TIME_TO_SEC(`start_time`)) STORED,
  `type` enum('presentation','workshop','panel','break','lunch','networking','keynote') DEFAULT 'presentation',
  `room` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `speaker_id` int(11) DEFAULT NULL,
  `additional_speakers` json DEFAULT NULL,
  `materials` json DEFAULT NULL,
  `slides_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `live_stream_url` varchar(255) DEFAULT NULL,
  `is_mandatory` tinyint(1) DEFAULT 0,
  `requires_registration` tinyint(1) DEFAULT 0,
  `level` enum('beginner','intermediate','advanced','all_levels') DEFAULT 'all_levels',
  `tags` json DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conference_id` (`conference_id`),
  KEY `speaker_id` (`speaker_id`),
  KEY `idx_session_date` (`session_date`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `schedule_sessions_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_sessions_ibfk_2` FOREIGN KEY (`speaker_id`) REFERENCES `speakers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng session_attendees - Người tham dự phiên
DROP TABLE IF EXISTS `session_attendees`;
CREATE TABLE `session_attendees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `attendance_status` enum('registered','attended','absent','cancelled') DEFAULT 'registered',
  `check_in_time` timestamp NULL DEFAULT NULL,
  `check_out_time` timestamp NULL DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_user_unique` (`session_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `registration_id` (`registration_id`),
  KEY `idx_attendance_status` (`attendance_status`),
  CONSTRAINT `session_attendees_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `schedule_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_attendees_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_attendees_ibfk_3` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- FEEDBACK TABLES - Bảng phản hồi
-- ========================================================

-- Bảng feedback - Phản hồi và đánh giá
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `speaker_id` int(11) DEFAULT NULL,
  `overall_rating` int(11) NOT NULL,
  `content_rating` int(11) DEFAULT NULL,
  `speaker_rating` int(11) DEFAULT NULL,
  `venue_rating` int(11) DEFAULT NULL,
  `organization_rating` int(11) DEFAULT NULL,
  `value_rating` int(11) DEFAULT NULL,
  `would_recommend` tinyint(1) DEFAULT NULL,
  `would_attend_again` tinyint(1) DEFAULT NULL,
  `feedback_text` text DEFAULT NULL,
  `suggestions` text DEFAULT NULL,
  `best_aspects` text DEFAULT NULL,
  `improvements` text DEFAULT NULL,
  `additional_comments` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'submitted',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_conference_unique` (`user_id`,`conference_id`),
  KEY `conference_id` (`conference_id`),
  KEY `session_id` (`session_id`),
  KEY `speaker_id` (`speaker_id`),
  KEY `idx_overall_rating` (`overall_rating`),
  KEY `idx_is_public` (`is_public`),
  KEY `idx_status` (`status`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `schedule_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `feedback_ibfk_4` FOREIGN KEY (`speaker_id`) REFERENCES `speakers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- CERTIFICATE TABLES - Bảng chứng chỉ
-- ========================================================

-- Bảng certificates - Chứng chỉ
DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `certificate_type` enum('attendance','completion','achievement','speaker') DEFAULT 'attendance',
  `template_id` varchar(50) DEFAULT NULL,
  `recipient_name` varchar(200) NOT NULL,
  `conference_title` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `completion_date` date DEFAULT NULL,
  `hours_completed` decimal(4,2) DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `skills_acquired` json DEFAULT NULL,
  `verification_code` varchar(100) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `last_downloaded` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 1,
  `revoked` tinyint(1) DEFAULT 0,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoked_reason` text DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificate_number` (`certificate_number`),
  UNIQUE KEY `verification_code` (`verification_code`),
  UNIQUE KEY `user_conference_type_unique` (`user_id`,`conference_id`,`certificate_type`),
  KEY `conference_id` (`conference_id`),
  KEY `registration_id` (`registration_id`),
  KEY `idx_issue_date` (`issue_date`),
  KEY `idx_is_verified` (`is_verified`),
  KEY `idx_revoked` (`revoked`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_3` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- NOTIFICATION TABLES - Bảng thông báo
-- ========================================================

-- Bảng notifications - Thông báo
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('general','conference','registration','payment','reminder','system') DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `channels` json DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `read_at` timestamp NULL DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `action_text` varchar(100) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivery_status` enum('pending','sent','failed','bounced') DEFAULT 'pending',
  `delivery_attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_read_at` (`read_at`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_priority` (`priority`),
  KEY `idx_delivery_status` (`delivery_status`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SYSTEM TABLES - Bảng hệ thống
-- ========================================================

-- Bảng settings - Cài đặt hệ thống
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('string','integer','boolean','json','text') DEFAULT 'string',
  `group` varchar(50) DEFAULT 'general',
  `label` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `options` json DEFAULT NULL,
  `validation_rules` json DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `is_editable` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `idx_group` (`group`),
  KEY `idx_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng audit_logs - Nhật ký hệ thống
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_record_id` (`record_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- ADDITIONAL TABLES - Bảng bổ sung
-- ========================================================

-- Bảng user_activity_logs - Lịch sử hoạt động người dùng
DROP TABLE IF EXISTS `user_activity_logs`;
CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_entity_type` (`entity_type`),
  KEY `idx_entity_id` (`entity_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `user_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng transactions - Giao dịch thanh toán
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) DEFAULT NULL,
  `registration_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `type` enum('payment','refund','partial_refund') DEFAULT 'payment',
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'VND',
  `fee` decimal(15,2) DEFAULT 0.00,
  `net_amount` decimal(15,2) GENERATED ALWAYS AS (`amount` - `fee`) STORED,
  `status` enum('pending','processing','completed','failed','cancelled','refunded') DEFAULT 'pending',
  `gateway` varchar(50) DEFAULT NULL,
  `gateway_transaction_id` varchar(255) DEFAULT NULL,
  `gateway_response` json DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `description` text DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `user_id` (`user_id`),
  KEY `conference_id` (`conference_id`),
  KEY `registration_id` (`registration_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_gateway` (`gateway`),  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_5` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng invoices - Hóa đơn
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) DEFAULT NULL,
  `amount_subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount_discount` decimal(15,2) DEFAULT 0.00,
  `amount_tax` decimal(15,2) DEFAULT 0.00,
  `amount_total` decimal(15,2) NOT NULL,
  `amount_paid` decimal(15,2) DEFAULT 0.00,
  `amount_due` decimal(15,2) GENERATED ALWAYS AS (`amount_total` - `amount_paid`) STORED,
  `currency` varchar(3) DEFAULT 'VND',
  `status` enum('draft','sent','paid','overdue','cancelled','refunded') DEFAULT 'draft',
  `due_date` datetime DEFAULT NULL,
  `paid_date` datetime DEFAULT NULL,
  `billing_address` json DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `user_id` (`user_id`),
  KEY `conference_id` (`conference_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_paid_date` (`paid_date`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng invoice_items - Chi tiết hóa đơn
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `registration_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) GENERATED ALWAYS AS ((`quantity` * `unit_price`) - `discount_amount` + `tax_amount`) STORED,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `registration_id` (`registration_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng error_logs - Nhật ký lỗi hệ thống
DROP TABLE IF EXISTS `error_logs`;
CREATE TABLE `error_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `level` enum('debug','info','notice','warning','error','critical','alert','emergency') DEFAULT 'error',
  `message` text NOT NULL,
  `context` json DEFAULT NULL,
  `exception_class` varchar(255) DEFAULT NULL,
  `exception_message` text DEFAULT NULL,
  `stack_trace` text DEFAULT NULL,
  `file` varchar(500) DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `request_data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_level` (`level`),
  KEY `idx_exception_class` (`exception_class`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_file_line` (`file`, `line`),  CONSTRAINT `error_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng scheduled_tasks - Các tác vụ định thời
DROP TABLE IF EXISTS `scheduled_tasks`;
CREATE TABLE `scheduled_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `command` varchar(500) NOT NULL,
  `schedule` varchar(50) NOT NULL COMMENT 'Cron expression',
  `is_active` tinyint(1) DEFAULT 1,
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NULL DEFAULT NULL,
  `run_count` int(11) DEFAULT 0,
  `failure_count` int(11) DEFAULT 0,
  `max_failures` int(11) DEFAULT 3,
  `timeout` int(11) DEFAULT 300 COMMENT 'Timeout in seconds',
  `output` text DEFAULT NULL,
  `status` enum('idle','running','success','failed','disabled') DEFAULT 'idle',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_status` (`status`),
  KEY `idx_next_run` (`next_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng tags - Thẻ gắn nhãn
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#6c757d',
  `usage_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_usage_count` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng conference_tags - Liên kết hội nghị với thẻ
DROP TABLE IF EXISTS `conference_tags`;
CREATE TABLE `conference_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `conference_tag_unique` (`conference_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `conference_tags_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conference_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng discount_codes - Mã giảm giá
DROP TABLE IF EXISTS `discount_codes`;
CREATE TABLE `discount_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `conference_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('percentage','fixed_amount','free_ticket') DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `user_limit` int(11) DEFAULT 1,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `applicable_ticket_types` json DEFAULT NULL,
  `restrictions` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `conference_id` (`conference_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_valid_from` (`valid_from`),
  KEY `idx_valid_until` (`valid_until`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `discount_codes_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discount_codes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- MEDIA MANAGEMENT - Quản lý files và media
-- ========================================================

-- Bảng media_categories - Danh mục media
DROP TABLE IF EXISTS `media_categories`;
CREATE TABLE `media_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `allowed_types` varchar(255) DEFAULT NULL COMMENT 'Các loại file cho phép (jpg,png,pdf...)',
  `max_file_size` int(11) DEFAULT NULL COMMENT 'Kích thước file tối đa (KB)',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `media_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `media_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng media_files - File media
DROP TABLE IF EXISTS `media_files`;
CREATE TABLE `media_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `uploader_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `full_url` varchar(1000) DEFAULT NULL,
  `file_size` int(11) NOT NULL COMMENT 'Kích thước file (KB)',
  `file_type` varchar(100) NOT NULL COMMENT 'MIME type',
  `extension` varchar(10) NOT NULL,
  `media_type` enum('image','document','video','audio','archive','other') NOT NULL DEFAULT 'other',
  `width` int(11) DEFAULT NULL COMMENT 'Chiều rộng (cho ảnh/video)',
  `height` int(11) DEFAULT NULL COMMENT 'Chiều cao (cho ảnh/video)',
  `duration` int(11) DEFAULT NULL COMMENT 'Thời lượng (giây, cho video/audio)',
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `metadata` json DEFAULT NULL COMMENT 'EXIF và metadata khác',
  `thumbnails` json DEFAULT NULL COMMENT 'Các phiên bản thumbnail',
  `tags` json DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `access_roles` json DEFAULT NULL COMMENT 'Vai trò được phép truy cập',
  `download_count` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('active','archived','trashed') DEFAULT 'active',
  `last_accessed` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `uploader_id` (`uploader_id`),
  KEY `idx_media_type` (`media_type`),
  KEY `idx_status` (`status`),
  KEY `idx_is_public` (`is_public`),
  KEY `idx_file_name` (`file_name`),
  KEY `idx_extension` (`extension`),
  CONSTRAINT `media_files_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `media_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_files_ibfk_2` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng media_folders - Thư mục media
DROP TABLE IF EXISTS `media_folders`;
CREATE TABLE `media_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `path` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `owner_id` (`owner_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_is_public` (`is_public`),
  CONSTRAINT `media_folders_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `media_folders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_folders_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_folders_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng folder_files - Liên kết thư mục và file
DROP TABLE IF EXISTS `folder_files`;
CREATE TABLE `folder_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `folder_file_unique` (`folder_id`,`file_id`),
  KEY `file_id` (`file_id`),
  CONSTRAINT `folder_files_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `media_folders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `folder_files_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dữ liệu mẫu cho media_categories
INSERT INTO `media_categories` (`name`, `slug`, `description`, `allowed_types`) VALUES
('Hình ảnh hội nghị', 'hinh-anh-hoi-nghi', 'Ảnh chụp tại các sự kiện hội nghị', 'jpg,jpeg,png,gif'),
('Tài liệu', 'tai-lieu', 'Tài liệu công khai của hội nghị', 'pdf,doc,docx,ppt,pptx,xls,xlsx'),
('Thuyết trình', 'thuyet-trinh', 'Slides thuyết trình của diễn giả', 'pdf,ppt,pptx'),
('Video hội nghị', 'video-hoi-nghi', 'Video ghi lại các phiên hội nghị', 'mp4,webm,mov');

-- ========================================================
-- VIEWS - Các khung nhìn
-- ========================================================

-- Khung nhìn tổng hợp thông tin người dùng và hoạt động
CREATE OR REPLACE VIEW user_activity_summary AS
SELECT 
    u.id AS user_id, 
    u.email,
    CONCAT(u.firstName, ' ', u.lastName) AS full_name,
    u.role,
    u.status,
    COUNT(DISTINCT r.id) AS total_registrations,
    COUNT(DISTINCT c.id) AS conferences_attended,
    COUNT(DISTINCT cert.id) AS certificates_received,
    MAX(log.created_at) AS last_activity,
    COUNT(DISTINCT log.id) AS activity_count
FROM 
    users u
LEFT JOIN 
    registrations r ON u.id = r.user_id
LEFT JOIN 
    certificates cert ON u.id = cert.user_id
LEFT JOIN 
    conferences c ON (u.id = r.user_id AND r.conference_id = c.id AND r.status = 'attended')
LEFT JOIN 
    user_activity_logs log ON u.id = log.user_id
GROUP BY 
    u.id, u.email, u.firstName, u.lastName, u.role, u.status;

-- Khung nhìn thống kê hội nghị
CREATE OR REPLACE VIEW conference_statistics AS
SELECT 
    c.id,
    c.title,
    c.status,
    c.capacity,
    c.current_attendees,
    COUNT(DISTINCT r.id) AS total_registrations,
    SUM(CASE WHEN r.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count,
    SUM(CASE WHEN r.status = 'attended' THEN 1 ELSE 0 END) AS attended_count,
    SUM(CASE WHEN r.payment_status = 'paid' THEN r.price_paid ELSE 0 END) AS total_revenue,
    SUM(CASE WHEN r.certificate_issued = 1 THEN 1 ELSE 0 END) AS certificates_issued,
    ROUND((c.current_attendees / c.capacity) * 100, 2) AS occupancy_rate,
    COUNT(DISTINCT s.id) AS session_count
FROM 
    conferences c
LEFT JOIN 
    registrations r ON c.id = r.conference_id
LEFT JOIN 
    schedule_sessions s ON c.id = s.conference_id
GROUP BY 
    c.id, c.title, c.status, c.capacity, c.current_attendees;

-- Khung nhìn báo cáo thanh toán
CREATE OR REPLACE VIEW payment_reports AS
SELECT 
    i.id AS invoice_id,
    i.invoice_number,
    i.user_id,
    u.email AS user_email,
    CONCAT(u.firstName, ' ', u.lastName) AS payer_name,
    i.amount_total,
    i.amount_paid,
    i.status AS invoice_status,
    i.created_at AS invoice_date,
    c.id AS conference_id,
    c.title AS conference_title,
    pm.name AS payment_method,
    t.transaction_id,
    t.status AS transaction_status
FROM 
    invoices i
LEFT JOIN 
    users u ON i.user_id = u.id
LEFT JOIN 
    transactions t ON i.id = t.invoice_id
LEFT JOIN 
    payment_methods pm ON t.payment_method_id = pm.id
LEFT JOIN 
    invoice_items ii ON i.id = ii.invoice_id
LEFT JOIN 
    registrations r ON ii.registration_id = r.id
LEFT JOIN 
    conferences c ON r.conference_id = c.id
GROUP BY
    i.id, i.invoice_number, i.user_id, u.email, u.firstName, u.lastName,
    i.amount_total, i.amount_paid, i.status, i.created_at,
    c.id, c.title, pm.name, t.transaction_id, t.status;

-- View thống kê tổng quan hội nghị
CREATE OR REPLACE VIEW conference_overview AS
SELECT
    c.id,
    c.title,
    c.start_date,
    c.end_date,
    c.capacity,
    c.status,
    cat.name AS category_name,
    v.name AS venue_name,
    COUNT(DISTINCT r.id) AS total_registrations,
    COUNT(DISTINCT CASE WHEN r.status = 'confirmed' THEN r.id END) AS confirmed_registrations,
    COUNT(DISTINCT CASE WHEN r.status = 'attended' THEN r.id END) AS attended_registrations,
    COUNT(DISTINCT cs.speaker_id) AS total_speakers,
    COALESCE(SUM(CASE WHEN r.payment_status = 'paid' THEN r.price_paid ELSE 0 END), 0) AS total_revenue,
    ROUND((COUNT(DISTINCT CASE WHEN r.status IN ('confirmed', 'attended') THEN r.id END) / c.capacity) * 100, 2) AS occupancy_rate
FROM
    conferences c
LEFT JOIN categories cat ON c.category_id = cat.id
LEFT JOIN venues v ON c.venue_id = v.id
LEFT JOIN registrations r ON c.id = r.conference_id
LEFT JOIN conference_speakers cs ON c.id = cs.conference_id
GROUP BY
    c.id, c.title, c.start_date, c.end_date, c.capacity, c.status, cat.name, v.name;

-- View hoạt động người dùng gần đây
CREATE OR REPLACE VIEW recent_user_activities AS
SELECT
    ual.id,
    ual.user_id,
    CONCAT(u.firstName, ' ', u.lastName) AS user_name,
    u.email,
    ual.activity_type,
    ual.description,
    ual.entity_type,
    ual.entity_id,
    ual.ip_address,
    ual.device_type,
    ual.os,
    ual.browser,
    ual.created_at
FROM
    user_activity_logs ual
LEFT JOIN users u ON ual.user_id = u.id
ORDER BY ual.created_at DESC;

-- View báo cáo doanh thu theo tháng
CREATE OR REPLACE VIEW monthly_revenue_report AS
SELECT
    YEAR(t.payment_date) AS year,
    MONTH(t.payment_date) AS month,
    DATE_FORMAT(t.payment_date, '%Y-%m') AS month_year,
    COUNT(DISTINCT t.id) AS total_transactions,
    COUNT(DISTINCT t.user_id) AS unique_customers,
    SUM(t.amount) AS total_revenue,
    SUM(t.fee) AS total_fees,
    SUM(t.amount - t.fee) AS net_revenue,
    AVG(t.amount) AS average_transaction_amount
FROM
    transactions t
WHERE
    t.status = 'completed'
    AND t.type = 'payment'
GROUP BY
    YEAR(t.payment_date), MONTH(t.payment_date)
ORDER BY
    year DESC, month DESC;

-- View thống kê lỗi hệ thống
CREATE OR REPLACE VIEW error_statistics AS
SELECT
    DATE(el.created_at) AS error_date,
    el.level,
    el.exception_class,
    COUNT(*) AS error_count,
    COUNT(DISTINCT el.user_id) AS affected_users,
    COUNT(DISTINCT el.ip_address) AS affected_ips,
    MIN(el.created_at) AS first_occurrence,
    MAX(el.created_at) AS last_occurrence
FROM
    error_logs el
GROUP BY
    DATE(el.created_at), el.level, el.exception_class
ORDER BY
    error_date DESC, error_count DESC;

-- ========================================================
-- INDEXES - Tối ưu hóa performance
-- ========================================================

-- Indexes cho bảng users để tối ưu bảo mật
CREATE INDEX idx_users_last_login ON users(last_login);
CREATE INDEX idx_users_login_attempts ON users(login_attempts);

-- Indexes cho bảng translations
CREATE INDEX idx_translations_lang_key ON translations(lang_code, translation_key);

-- Indexes cho bảng media_files
CREATE INDEX idx_media_files_file_type_status ON media_files(file_type, status);
CREATE INDEX idx_media_files_uploader_created ON media_files(uploader_id, created_at);

-- Indexes cho bảng user_activity_logs
CREATE INDEX idx_activity_logs_user_activity_date ON user_activity_logs(user_id, activity_type, created_at);

-- Indexes cho bảng transactions
CREATE INDEX idx_transactions_payment_date_status ON transactions(payment_date, status);
CREATE INDEX idx_transactions_user_date ON transactions(user_id, created_at);

-- ========================================================
-- SAMPLE DATA - Dữ liệu mẫu
-- ========================================================

-- Dữ liệu mẫu cho bảng users
INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `password`, `role`, `status`, `email_verified`, `language`, `timezone`, `created_at`) VALUES
(1, 'Admin', 'System', 'admin@confab.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 1, 'vi', 'Asia/Ho_Chi_Minh', NOW()),
(2, 'Nguyễn', 'Tổ Chức', 'organizer@confab.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'organizer', 'active', 1, 'vi', 'Asia/Ho_Chi_Minh', NOW()),
(3, 'Trần', 'Diễn Giả', 'speaker@confab.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'speaker', 'active', 1, 'vi', 'Asia/Ho_Chi_Minh', NOW()),
(4, 'Lê', 'Tham Dự', 'user@confab.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', 1, 'vi', 'Asia/Ho_Chi_Minh', NOW());

-- Dữ liệu mẫu cho bảng languages
INSERT INTO `languages` (`id`, `code`, `name`, `native_name`, `direction`, `flag`, `is_default`, `is_active`, `sort_order`) VALUES
(1, 'vi', 'Vietnamese', 'Tiếng Việt', 'ltr', '🇻🇳', 1, 1, 1),
(2, 'en', 'English', 'English', 'ltr', '🇺🇸', 0, 1, 2),
(3, 'zh', 'Chinese', '中文', 'ltr', '🇨🇳', 0, 1, 3),
(4, 'ja', 'Japanese', '日本語', 'ltr', '🇯🇵', 0, 1, 4);

-- Dữ liệu mẫu cho bảng translations
INSERT INTO `translations` (`id`, `lang_code`, `translation_key`, `translation_value`) VALUES
(1, 'vi', 'app.name', 'Confab Web Oasis'),
(2, 'en', 'app.name', 'Confab Web Oasis'),
(3, 'vi', 'app.description', 'Hệ thống quản lý hội nghị chuyên nghiệp'),
(4, 'en', 'app.description', 'Professional Conference Management System'),
(5, 'vi', 'menu.home', 'Trang chủ'),
(6, 'en', 'menu.home', 'Home'),
(7, 'vi', 'menu.conferences', 'Hội nghị'),
(8, 'en', 'menu.conferences', 'Conferences'),
(9, 'vi', 'menu.speakers', 'Diễn giả'),
(10, 'en', 'menu.speakers', 'Speakers'),
(11, 'vi', 'button.register', 'Đăng ký'),
(12, 'en', 'button.register', 'Register'),
(13, 'vi', 'button.login', 'Đăng nhập'),
(14, 'en', 'button.login', 'Login'),
(15, 'vi', 'status.active', 'Hoạt động'),
(16, 'en', 'status.active', 'Active');

-- Dữ liệu mẫu cho bảng categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `color`, `icon`, `is_featured`, `status`) VALUES
(1, 'Công nghệ thông tin', 'cong-nghe-thong-tin', 'Hội nghị về công nghệ thông tin và phần mềm', '#007bff', 'fas fa-laptop-code', 1, 'active'),
(2, 'Kinh doanh', 'kinh-doanh', 'Hội nghị về quản trị kinh doanh và khởi nghiệp', '#28a745', 'fas fa-chart-line', 1, 'active'),
(3, 'Y tế', 'y-te', 'Hội nghị y khoa và chăm sóc sức khỏe', '#dc3545', 'fas fa-heartbeat', 0, 'active'),
(4, 'Giáo dục', 'giao-duc', 'Hội nghị về giáo dục và đào tạo', '#ffc107', 'fas fa-graduation-cap', 0, 'active'),
(5, 'Khoa học', 'khoa-hoc', 'Hội nghị khoa học và nghiên cứu', '#6f42c1', 'fas fa-microscope', 0, 'active');

-- Dữ liệu mẫu cho bảng venues
INSERT INTO `venues` (`id`, `name`, `slug`, `description`, `address`, `city`, `country`, `capacity`, `contact_name`, `contact_email`, `contact_phone`, `status`) VALUES
(1, 'Trung tâm Hội nghị Quốc gia', 'trung-tam-hoi-nghi-quoc-gia', 'Trung tâm hội nghị lớn nhất Việt Nam với đầy đủ tiện ích hiện đại', 'Đường Thành Thái, Quận 10, TP.HCM', 'Hồ Chí Minh', 'Vietnam', 2000, 'Nguyễn Văn A', 'venue1@confab.local', '028-1234-5678', 'active'),
(2, 'Khách sạn Rex', 'khach-san-rex', 'Khách sạn 5 sao với phòng hội nghị sang trọng', '141 Nguyễn Huệ, Quận 1, TP.HCM', 'Hồ Chí Minh', 'Vietnam', 500, 'Trần Thị B', 'venue2@confab.local', '028-8765-4321', 'active'),
(3, 'Đại học Bách Khoa', 'dai-hoc-bach-khoa', 'Giảng đường hiện đại tại Đại học Bách Khoa TP.HCM', '268 Lý Thường Kiệt, Quận 10, TP.HCM', 'Hồ Chí Minh', 'Vietnam', 1000, 'PGS. Lê Văn C', 'venue3@confab.local', '028-1111-2222', 'active');

-- Dữ liệu mẫu cho bảng speakers
INSERT INTO `speakers` (`id`, `user_id`, `name`, `slug`, `title`, `company`, `bio`, `email`, `status`) VALUES
(1, 3, 'Trần Diễn Giả', 'tran-dien-gia', 'CEO & Founder', 'TechViet Solutions', 'Chuyên gia công nghệ với hơn 15 năm kinh nghiệm trong lĩnh vực phát triển phần mềm và quản lý dự án công nghệ.', 'speaker@confab.local', 'active'),
(2, NULL, 'Dr. Nguyễn Khoa Học', 'dr-nguyen-khoa-hoc', 'Giáo sư', 'Đại học Bách Khoa', 'Tiến sĩ về Trí tuệ nhân tạo và Machine Learning, tác giả của nhiều nghiên cứu được công bố quốc tế.', 'nguyenkhoahoc@example.com', 'active'),
(3, NULL, 'Phạm Kinh Doanh', 'pham-kinh-doanh', 'Giám đốc điều hành', 'Startup Hub Vietnam', 'Doanh nhân thành công với kinh nghiệm xây dựng và phát triển nhiều startup công nghệ tại Việt Nam.', 'phamkinhdoanh@example.com', 'active');

-- Dữ liệu mẫu cho bảng conferences
INSERT INTO `conferences` (`id`, `title`, `slug`, `short_description`, `description`, `start_date`, `end_date`, `category_id`, `venue_id`, `location`, `type`, `format`, `price`, `currency`, `capacity`, `status`, `featured`, `created_by`) VALUES
(1, 'Vietnam Tech Summit 2024', 'vietnam-tech-summit-2024', 'Hội nghị công nghệ lớn nhất Việt Nam năm 2024', 'Hội nghị tập trung vào các xu hướng công nghệ mới như AI, Blockchain, IoT và Digital Transformation. Sự kiện quy tụ hơn 1000 chuyên gia công nghệ hàng đầu.', '2024-12-15 08:00:00', '2024-12-16 18:00:00', 1, 1, 'TP. Hồ Chí Minh', 'in_person', 'conference', 2500000.00, 'VND', 1000, 'published', 1, 2),
(2, 'Startup Weekend Ho Chi Minh', 'startup-weekend-hcm', 'Cuối tuần khởi nghiệp dành cho các bạn trẻ có ý tưởng kinh doanh', 'Sự kiện 54 giờ liên tục giúp các bạn trẻ biến ý tưởng thành startup thực tế. Có sự tham gia của các mentor và nhà đầu tư hàng đầu.', '2024-11-30 18:00:00', '2024-12-02 20:00:00', 2, 3, 'TP. Hồ Chí Minh', 'in_person', 'workshop', 500000.00, 'VND', 200, 'published', 1, 2),
(3, 'Digital Health Conference 2024', 'digital-health-conference-2024', 'Hội nghị về công nghệ số trong y tế', 'Khám phá những ứng dụng công nghệ mới nhất trong lĩnh vực chăm sóc sức khỏe, từ telemedicine đến AI trong chẩn đoán y khoa.', '2024-12-20 08:30:00', '2024-12-20 17:30:00', 3, 2, 'TP. Hồ Chí Minh', 'hybrid', 'conference', 1500000.00, 'VND', 300, 'published', 0, 2);

-- Dữ liệu mẫu cho bảng conference_speakers
INSERT INTO `conference_speakers` (`id`, `conference_id`, `speaker_id`, `role`, `talk_title`, `talk_description`, `status`) VALUES
(1, 1, 1, 'keynote', 'Tương lai của AI trong phát triển phần mềm', 'Phân tích xu hướng và tác động của trí tuệ nhân tạo đến ngành công nghiệp phần mềm trong 5 năm tới.', 'confirmed'),
(2, 1, 2, 'speaker', 'Machine Learning cho người mới bắt đầu', 'Hướng dẫn cơ bản về Machine Learning và các ứng dụng thực tế trong doanh nghiệp.', 'confirmed'),
(3, 2, 3, 'keynote', 'Xây dựng startup công nghệ bền vững', 'Chia sẻ kinh nghiệm và bài học từ việc xây dựng các startup công nghệ thành công.', 'confirmed'),
(4, 3, 2, 'speaker', 'AI trong chẩn đoán y khoa', 'Ứng dụng học máy và thị giác máy tính trong việc chẩn đoán và điều trị bệnh.', 'confirmed');

-- Dữ liệu mẫu cho bảng payment_methods
INSERT INTO `payment_methods` (`id`, `name`, `type`, `provider`, `currency`, `is_active`, `sort_order`) VALUES
(1, 'Chuyển khoản ngân hàng', 'bank_transfer', 'manual', 'VND', 1, 1),
(2, 'Ví MoMo', 'e_wallet', 'momo', 'VND', 1, 2),
(3, 'ZaloPay', 'e_wallet', 'zalopay', 'VND', 1, 3),
(4, 'Thẻ tín dụng/ghi nợ', 'credit_card', 'stripe', 'VND', 1, 4),
(5, 'PayPal', 'paypal', 'paypal', 'USD', 1, 5);

-- Dữ liệu mẫu cho bảng media_folders
INSERT INTO `media_folders` (`id`, `name`, `slug`, `description`, `parent_id`, `is_public`, `created_by`) VALUES
(1, 'Conferences', 'conferences', 'Thư mục chứa hình ảnh và tài liệu hội nghị', NULL, 1, 1),
(2, 'Speakers', 'speakers', 'Thư mục chứa ảnh diễn giả', NULL, 1, 1),
(3, 'Venues', 'venues', 'Thư mục chứa ảnh địa điểm tổ chức', NULL, 1, 1),
(4, 'Certificates', 'certificates', 'Thư mục chứa mẫu chứng chỉ', NULL, 0, 1),
(5, 'Documents', 'documents', 'Thư mục chứa tài liệu và slide', 1, 1, 1);

-- Dữ liệu mẫu cho bảng scheduled_tasks
INSERT INTO `scheduled_tasks` (`id`, `name`, `description`, `command`, `schedule`, `is_active`) VALUES
(1, 'Gửi email nhắc nhở', 'Gửi email nhắc nhở trước hội nghị 24h', 'php /path/to/send_reminders.php', '0 9 * * *', 1),
(2, 'Backup database', 'Sao lưu cơ sở dữ liệu hàng ngày', 'php /path/to/backup_db.php', '0 2 * * *', 1),
(3, 'Làm sạch logs cũ', 'Xóa logs cũ hơn 30 ngày', 'php /path/to/cleanup_logs.php', '0 3 * * 0', 1),
(4, 'Cập nhật thống kê', 'Cập nhật báo cáo thống kê hệ thống', 'php /path/to/update_stats.php', '0 1 * * *', 1);

-- Dữ liệu mẫu cho bảng settings
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `label`, `description`, `is_public`) VALUES
('site_name', 'Confab Web Oasis', 'string', 'general', 'Tên website', 'Tên hiển thị của website', 1),
('site_description', 'Hệ thống quản lý hội nghị chuyên nghiệp', 'string', 'general', 'Mô tả website', 'Mô tả ngắn về website', 1),
('default_language', 'vi', 'string', 'localization', 'Ngôn ngữ mặc định', 'Ngôn ngữ mặc định của hệ thống', 1),
('default_timezone', 'Asia/Ho_Chi_Minh', 'string', 'general', 'Múi giờ mặc định', 'Múi giờ mặc định của hệ thống', 1),
('email_from_address', 'noreply@confab.local', 'string', 'email', 'Email gửi đi', 'Địa chỉ email mặc định cho gửi thông báo', 0),
('email_from_name', 'Confab Web Oasis', 'string', 'email', 'Tên người gửi', 'Tên hiển thị khi gửi email', 0),
('registration_enabled', '1', 'boolean', 'conference', 'Cho phép đăng ký', 'Bật/tắt tính năng đăng ký hội nghị', 1),
('certificate_enabled', '1', 'boolean', 'conference', 'Bật chứng chỉ', 'Cho phép tạo chứng chỉ tham dự', 1),
('max_file_size', '10485760', 'integer', 'media', 'Kích thước file tối đa', 'Kích thước file upload tối đa (bytes)', 0),
('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,ppt,pptx', 'string', 'media', 'Loại file cho phép', 'Danh sách extension file được phép upload', 0);

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

-- ========================================================
-- COMPLETION MESSAGE
-- ========================================================

/*
SCHEMA VÀ SAMPLE DATA HOÀN THÀNH!

Schema này bao gồm:
✅ 30+ bảng chính với đầy đủ tính năng
✅ Stored Procedures và Functions
✅ Triggers tự động
✅ Views cho reporting (đã sửa lỗi)
✅ Indexes tối ưu performance
✅ Sample data đầy đủ cho testing

Sample data bao gồm:
- 4 user accounts (admin, organizer, speaker, user) - password: password123
- 4 ngôn ngữ hỗ trợ (vi, en, zh, ja)
- 16 bản dịch cơ bản
- 5 categories hội nghị
- 3 venues
- 3 speakers
- 3 conferences với liên kết speakers
- 5 payment methods
- 5 media folders
- 4 scheduled tasks
- 10 system settings
- 4 user activity logs
- 3 invoices với invoice items
- 2 transactions
- 3 error logs

Mật khẩu mặc định cho tất cả accounts: password123
*/
