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
-- CORE TABLES - Bảng cốt lõi
-- ========================================================

-- Bảng users - Người dùng
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
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
-- VIEWS - Các view hữu ích
-- ========================================================

-- View thống kê hội nghị
CREATE OR REPLACE VIEW `conference_stats` AS
SELECT 
    c.id,
    c.title,
    c.start_date,
    c.capacity,
    c.current_attendees,
    COALESCE(reg_stats.total_registrations, 0) AS total_registrations,
    COALESCE(reg_stats.confirmed_registrations, 0) AS confirmed_registrations,
    COALESCE(reg_stats.pending_registrations, 0) AS pending_registrations,
    COALESCE(reg_stats.cancelled_registrations, 0) AS cancelled_registrations,
    COALESCE(reg_stats.total_revenue, 0) AS total_revenue,
    COALESCE(feedback_stats.avg_rating, 0) AS avg_rating,
    COALESCE(feedback_stats.total_feedback, 0) AS total_feedback,
    ROUND((c.current_attendees / c.capacity) * 100, 2) AS occupancy_rate,
    CASE 
        WHEN c.current_attendees >= c.capacity THEN 'sold_out'
        WHEN c.current_attendees >= c.capacity * 0.8 THEN 'almost_full'
        WHEN c.current_attendees >= c.capacity * 0.5 THEN 'half_full'
        ELSE 'available'
    END AS booking_status
FROM conferences c
LEFT JOIN (
    SELECT 
        conference_id,
        COUNT(*) AS total_registrations,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_registrations,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_registrations,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_registrations,
        SUM(CASE WHEN payment_status = 'paid' THEN price_paid ELSE 0 END) AS total_revenue
    FROM registrations 
    GROUP BY conference_id
) reg_stats ON c.id = reg_stats.conference_id
LEFT JOIN (
    SELECT 
        conference_id,
        AVG(overall_rating) AS avg_rating,
        COUNT(*) AS total_feedback
    FROM feedback 
    WHERE status = 'approved'
    GROUP BY conference_id
) feedback_stats ON c.id = feedback_stats.conference_id;

-- View người dùng tích cực
CREATE OR REPLACE VIEW `active_users_stats` AS
SELECT 
    u.id,
    u.firstName,
    u.lastName,
    u.email,
    u.role,
    COALESCE(reg_stats.total_registrations, 0) AS total_registrations,
    COALESCE(reg_stats.confirmed_registrations, 0) AS confirmed_registrations,
    COALESCE(reg_stats.total_spent, 0) AS total_spent,
    COALESCE(feedback_stats.feedback_count, 0) AS feedback_count,
    COALESCE(feedback_stats.avg_rating_given, 0) AS avg_rating_given,
    u.last_login,
    DATEDIFF(NOW(), u.last_login) AS days_since_last_login
FROM users u
LEFT JOIN (
    SELECT 
        user_id,
        COUNT(*) AS total_registrations,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_registrations,
        SUM(CASE WHEN payment_status = 'paid' THEN price_paid ELSE 0 END) AS total_spent
    FROM registrations 
    GROUP BY user_id
) reg_stats ON u.id = reg_stats.user_id
LEFT JOIN (
    SELECT 
        user_id,
        COUNT(*) AS feedback_count,
        AVG(overall_rating) AS avg_rating_given
    FROM feedback 
    GROUP BY user_id
) feedback_stats ON u.id = feedback_stats.user_id
WHERE u.status = 'active';

-- ========================================================
-- TRIGGERS - Tự động cập nhật dữ liệu
-- ========================================================

DELIMITER $$

-- Trigger cập nhật số lượng người đăng ký
CREATE TRIGGER `update_conference_attendees_after_registration_insert`
AFTER INSERT ON `registrations`
FOR EACH ROW
BEGIN
    UPDATE conferences 
    SET current_attendees = (
        SELECT COUNT(*) FROM registrations 
        WHERE conference_id = NEW.conference_id 
        AND status IN ('confirmed', 'attended')
    )
    WHERE id = NEW.conference_id;
END$$

CREATE TRIGGER `update_conference_attendees_after_registration_update`
AFTER UPDATE ON `registrations`
FOR EACH ROW
BEGIN
    UPDATE conferences 
    SET current_attendees = (
        SELECT COUNT(*) FROM registrations 
        WHERE conference_id = NEW.conference_id 
        AND status IN ('confirmed', 'attended')
    )
    WHERE id = NEW.conference_id;
END$$

CREATE TRIGGER `update_conference_attendees_after_registration_delete`
AFTER DELETE ON `registrations`
FOR EACH ROW
BEGIN
    UPDATE conferences 
    SET current_attendees = (
        SELECT COUNT(*) FROM registrations 
        WHERE conference_id = OLD.conference_id 
        AND status IN ('confirmed', 'attended')
    )
    WHERE id = OLD.conference_id;
END$$

-- Trigger cập nhật tổng số bài nói của diễn giả
CREATE TRIGGER `update_speaker_talks_after_conference_speaker_insert`
AFTER INSERT ON `conference_speakers`
FOR EACH ROW
BEGIN
    UPDATE speakers 
    SET total_talks = (
        SELECT COUNT(*) FROM conference_speakers 
        WHERE speaker_id = NEW.speaker_id 
        AND status = 'confirmed'
    )
    WHERE id = NEW.speaker_id;
END$$

CREATE TRIGGER `update_speaker_talks_after_conference_speaker_update`
AFTER UPDATE ON `conference_speakers`
FOR EACH ROW
BEGIN
    UPDATE speakers 
    SET total_talks = (
        SELECT COUNT(*) FROM conference_speakers 
        WHERE speaker_id = NEW.speaker_id 
        AND status = 'confirmed'
    )
    WHERE id = NEW.speaker_id;
END$$

-- Trigger tạo mã đăng ký tự động
CREATE TRIGGER `generate_registration_code_before_insert`
BEFORE INSERT ON `registrations`
FOR EACH ROW
BEGIN
    DECLARE code_exists INT DEFAULT 1;
    DECLARE new_code VARCHAR(20);
    
    WHILE code_exists > 0 DO
        SET new_code = CONCAT('REG', YEAR(NOW()), LPAD(FLOOR(RAND() * 999999), 6, '0'));
        SELECT COUNT(*) INTO code_exists FROM registrations WHERE registration_code = new_code;
    END WHILE;
    
    SET NEW.registration_code = new_code;
END$$

-- Trigger tạo mã chứng chỉ tự động
CREATE TRIGGER `generate_certificate_number_before_insert`
BEFORE INSERT ON `certificates`
FOR EACH ROW
BEGIN
    DECLARE cert_exists INT DEFAULT 1;
    DECLARE new_cert_number VARCHAR(50);
    DECLARE new_verification_code VARCHAR(100);
    
    WHILE cert_exists > 0 DO
        SET new_cert_number = CONCAT('CERT', YEAR(NOW()), LPAD(FLOOR(RAND() * 9999999), 7, '0'));
        SELECT COUNT(*) INTO cert_exists FROM certificates WHERE certificate_number = new_cert_number;
    END WHILE;
    
    SET cert_exists = 1;
    WHILE cert_exists > 0 DO
        SET new_verification_code = UPPER(CONCAT(
            SUBSTRING(MD5(CONCAT(NEW.user_id, NEW.conference_id, NOW(), RAND())), 1, 8),
            '-',
            SUBSTRING(MD5(CONCAT(NEW.user_id, NEW.conference_id, NOW(), RAND())), 9, 4),
            '-',
            SUBSTRING(MD5(CONCAT(NEW.user_id, NEW.conference_id, NOW(), RAND())), 13, 4),
            '-',
            SUBSTRING(MD5(CONCAT(NEW.user_id, NEW.conference_id, NOW(), RAND())), 17, 12)
        ));
        SELECT COUNT(*) INTO cert_exists FROM certificates WHERE verification_code = new_verification_code;
    END WHILE;
    
    SET NEW.certificate_number = new_cert_number;
    SET NEW.verification_code = new_verification_code;
END$$

-- Trigger cập nhật usage_count cho tags
CREATE TRIGGER `update_tag_usage_after_conference_tag_insert`
AFTER INSERT ON `conference_tags`
FOR EACH ROW
BEGIN
    UPDATE tags 
    SET usage_count = (
        SELECT COUNT(*) FROM conference_tags 
        WHERE tag_id = NEW.tag_id
    )
    WHERE id = NEW.tag_id;
END$$

CREATE TRIGGER `update_tag_usage_after_conference_tag_delete`
AFTER DELETE ON `conference_tags`
FOR EACH ROW
BEGIN
    UPDATE tags 
    SET usage_count = (
        SELECT COUNT(*) FROM conference_tags 
        WHERE tag_id = OLD.tag_id
    )
    WHERE id = OLD.tag_id;
END$$

DELIMITER ;

-- ========================================================
-- INDEXES - Tối ưu hiệu suất
-- ========================================================

-- Indexes cho tìm kiếm và sắp xếp
CREATE INDEX idx_conferences_search ON conferences (title, location, status, start_date);
CREATE INDEX idx_users_search ON users (firstName, lastName, email, role);
CREATE INDEX idx_speakers_search ON speakers (name, company, specialties);
CREATE INDEX idx_feedback_ratings ON feedback (conference_id, overall_rating, status);
CREATE INDEX idx_registrations_stats ON registrations (conference_id, status, payment_status, registration_date);

-- Composite indexes cho các truy vấn phức tạp
CREATE INDEX idx_conferences_public ON conferences (status, visibility, featured, start_date);
CREATE INDEX idx_registrations_user_status ON registrations (user_id, status, registration_date);
CREATE INDEX idx_notifications_user_unread ON notifications (user_id, read_at, created_at);

-- ========================================================
-- SAMPLE DATA - Dữ liệu mẫu
-- ========================================================

-- Thêm categories mẫu
INSERT INTO `categories` (`name`, `slug`, `description`, `color`, `icon`) VALUES
('Công nghệ', 'cong-nghe', 'Hội nghị về công nghệ thông tin và chuyển đổi số', '#007bff', 'fas fa-laptop-code'),
('Kinh doanh', 'kinh-doanh', 'Hội nghị về quản trị kinh doanh và khởi nghiệp', '#28a745', 'fas fa-chart-line'),
('Giáo dục', 'giao-duc', 'Hội nghị về giáo dục và đào tạo', '#ffc107', 'fas fa-graduation-cap'),
('Y tế', 'y-te', 'Hội nghị về y tế và sức khỏe cộng đồng', '#dc3545', 'fas fa-heartbeat'),
('Khoa học', 'khoa-hoc', 'Hội nghị khoa học và nghiên cứu', '#6f42c1', 'fas fa-flask'),
('Môi trường', 'moi-truong', 'Hội nghị về môi trường và phát triển bền vững', '#20c997', 'fas fa-leaf');

-- Thêm venues mẫu
INSERT INTO `venues` (`name`, `slug`, `address`, `city`, `country`, `capacity`, `description`) VALUES
('Trung tâm Hội nghị Quốc gia', 'trung-tam-hoi-nghi-quoc-gia', 'Số 1 Thành Công, Quận Ba Đình', 'Hà Nội', 'Vietnam', 2000, 'Trung tâm hội nghị hiện đại với đầy đủ tiện nghi'),
('Trung tâm Hội nghị Gem Center', 'gem-center', '8 Nguyễn Bỉnh Khiêm, Quận 1', 'TP. Hồ Chí Minh', 'Vietnam', 1500, 'Trung tâm hội nghị cao cấp tại trung tâm thành phố'),
('Rex Hotel', 'rex-hotel', '141 Nguyễn Huệ, Quận 1', 'TP. Hồ Chí Minh', 'Vietnam', 800, 'Khách sạn lịch sử với không gian hội nghị đẳng cấp'),
('JW Marriott Hanoi', 'jw-marriott-hanoi', '8 Đỗ Đức Dục, Cầu Giấy', 'Hà Nội', 'Vietnam', 1200, 'Khách sạn 5 sao với tiện nghi hội nghị hiện đại');

-- Thêm settings mẫu
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `label`, `description`) VALUES
('site_name', 'Confab Web Oasis', 'string', 'general', 'Tên trang web', 'Tên của hệ thống quản lý hội nghị'),
('site_description', 'Hệ thống quản lý hội nghị chuyên nghiệp', 'string', 'general', 'Mô tả trang web', 'Mô tả ngắn về hệ thống'),
('default_currency', 'VND', 'string', 'payment', 'Tiền tệ mặc định', 'Tiền tệ sử dụng cho thanh toán'),
('timezone', 'Asia/Ho_Chi_Minh', 'string', 'general', 'Múi giờ', 'Múi giờ mặc định của hệ thống'),
('max_file_size', '10485760', 'integer', 'upload', 'Kích thước file tối đa', 'Kích thước file upload tối đa (bytes)'),
('email_notifications', 'true', 'boolean', 'notification', 'Thông báo email', 'Bật/tắt gửi email thông báo'),
('registration_auto_confirm', 'false', 'boolean', 'registration', 'Tự động xác nhận đăng ký', 'Tự động xác nhận đăng ký mà không cần duyệt');

-- Thêm tags mẫu
INSERT INTO `tags` (`name`, `slug`, `description`, `color`) VALUES
('AI', 'ai', 'Trí tuệ nhân tạo', '#e74c3c'),
('Machine Learning', 'machine-learning', 'Học máy', '#3498db'),
('Blockchain', 'blockchain', 'Công nghệ blockchain', '#f39c12'),
('IoT', 'iot', 'Internet of Things', '#9b59b6'),
('Cloud Computing', 'cloud-computing', 'Điện toán đám mây', '#1abc9c'),
('Cybersecurity', 'cybersecurity', 'An ninh mạng', '#e67e22'),
('Data Science', 'data-science', 'Khoa học dữ liệu', '#2ecc71'),
('Digital Transformation', 'digital-transformation', 'Chuyển đổi số', '#34495e');

COMMIT;
