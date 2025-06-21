-- ========================================================
-- Migration Script: Chuyển đổi từ Schema cũ sang Schema Complete
-- Tác giả: Confab Web Oasis Team
-- Ngày tạo: 22/06/2025
-- Mô tả: Script chuyển đổi dữ liệu từ schema cũ sang schema hoàn chỉnh
-- ========================================================

-- Tạo database backup trước khi migration
CREATE DATABASE IF NOT EXISTS `confab_db_backup` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Backup toàn bộ database hiện tại
-- Lưu ý: Chạy lệnh này trong terminal: mysqldump -u root -p confab_db > confab_db_backup.sql

-- Kiểm tra và tạo bảng backup
USE `confab_db`;

-- Backup users table
DROP TABLE IF EXISTS `users_backup`;
CREATE TABLE `users_backup` AS SELECT * FROM `users`;

-- Backup conferences table
DROP TABLE IF EXISTS `conferences_backup`;
CREATE TABLE `conferences_backup` AS SELECT * FROM `conferences`;

-- Backup registrations table
DROP TABLE IF EXISTS `registrations_backup`;
CREATE TABLE `registrations_backup` AS SELECT * FROM `registrations`;

-- Backup speakers table if exists
DROP TABLE IF EXISTS `speakers_backup`;
CREATE TABLE `speakers_backup` AS SELECT * FROM `speakers`;

-- ========================================================
-- MIGRATION PROCESS
-- ========================================================

-- 1. Chạy schema_complete.sql để tạo structure mới
-- 2. Chuyển đổi dữ liệu users

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Migrate users data
INSERT INTO `users` (
    `id`, `firstName`, `lastName`, `email`, `password`, `role`, 
    `phone`, `avatar`, `bio`, `remember_token`, 
    `last_login`, `status`, `created_at`, `updated_at`
)
SELECT 
    ub.`id`, ub.`firstName`, ub.`lastName`, ub.`email`, ub.`password`, 
    ub.`role`, ub.`phone`, ub.`avatar`, ub.`bio`, ub.`remember_token`,
    ub.`last_login`, 
    CASE 
        WHEN ub.`status` IS NULL THEN 'active'
        ELSE ub.`status`
    END,
    ub.`created_at`, ub.`updated_at`
FROM `users_backup` ub
ON DUPLICATE KEY UPDATE
    `firstName` = VALUES(`firstName`),
    `lastName` = VALUES(`lastName`),
    `email` = VALUES(`email`),
    `updated_at` = NOW();

-- 3. Migrate categories data (tạo từ dữ liệu category string cũ)
-- Lấy danh sách unique categories từ conferences cũ
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `status`)
SELECT DISTINCT 
    cb.`category` as name,
    LOWER(REPLACE(REPLACE(REPLACE(cb.`category`, ' ', '-'), 'ă', 'a'), 'đ', 'd')) as slug,
    CONCAT('Danh mục ', cb.`category`) as description,
    'active' as status
FROM `conferences_backup` cb 
WHERE cb.`category` IS NOT NULL AND cb.`category` != '';

-- 4. Migrate venues data (tạo từ location string cũ)
INSERT IGNORE INTO `venues` (`name`, `slug`, `address`, `city`, `country`, `status`)
SELECT DISTINCT 
    cb.`location` as name,
    LOWER(REPLACE(REPLACE(REPLACE(cb.`location`, ' ', '-'), 'ă', 'a'), 'đ', 'd')) as slug,
    cb.`location` as address,
    CASE 
        WHEN cb.`location` LIKE '%Hà Nội%' OR cb.`location` LIKE '%Hanoi%' THEN 'Hà Nội'
        WHEN cb.`location` LIKE '%Hồ Chí Minh%' OR cb.`location` LIKE '%TP.HCM%' OR cb.`location` LIKE '%TPHCM%' THEN 'TP. Hồ Chí Minh'
        WHEN cb.`location` LIKE '%Đà Nẵng%' OR cb.`location` LIKE '%Da Nang%' THEN 'Đà Nẵng'
        ELSE 'Khác'
    END as city,
    'Vietnam' as country,
    'active' as status
FROM `conferences_backup` cb 
WHERE cb.`location` IS NOT NULL AND cb.`location` != '';

-- 5. Migrate speakers data
INSERT IGNORE INTO `speakers` (
    `id`, `user_id`, `name`, `slug`, `title`, `bio`, `image`, 
    `status`, `created_at`, `updated_at`
)
SELECT 
    sb.`id`, sb.`user_id`, sb.`name`,
    LOWER(REPLACE(REPLACE(REPLACE(sb.`name`, ' ', '-'), 'ă', 'a'), 'đ', 'd')) as slug,
    sb.`title`, sb.`bio`, sb.`image`,
    'active' as status,
    sb.`created_at`, sb.`updated_at`
FROM `speakers_backup` sb
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `title` = VALUES(`title`),
    `bio` = VALUES(`bio`),
    `updated_at` = NOW();

-- 6. Migrate conferences data
INSERT INTO `conferences` (
    `id`, `title`, `slug`, `description`, `start_date`, `end_date`, 
    `category_id`, `venue_id`, `location`, `price`, `capacity`, 
    `current_attendees`, `status`, `image`, `organizer_name`, 
    `organizer_email`, `organizer_phone`, `created_by`, 
    `created_at`, `updated_at`
)
SELECT 
    cb.`id`, cb.`title`,
    LOWER(REPLACE(REPLACE(REPLACE(cb.`title`, ' ', '-'), 'ă', 'a'), 'đ', 'd')) as slug,
    cb.`description`, 
    TIMESTAMP(cb.`date`, '09:00:00') as start_date,
    CASE 
        WHEN cb.`endDate` IS NOT NULL THEN TIMESTAMP(cb.`endDate`, '17:00:00')
        ELSE TIMESTAMP(cb.`date`, '17:00:00')
    END as end_date,
    cat.`id` as category_id,
    ven.`id` as venue_id,
    cb.`location`, cb.`price`, cb.`capacity`,
    COALESCE(cb.`attendees`, 0) as current_attendees,
    CASE 
        WHEN cb.`status` = 'active' THEN 'published'
        WHEN cb.`status` = 'draft' THEN 'draft'
        WHEN cb.`status` = 'cancelled' THEN 'cancelled'
        WHEN cb.`status` = 'completed' THEN 'completed'
        ELSE 'draft'
    END as status,
    cb.`image`, cb.`organizer_name`, cb.`organizer_email`, cb.`organizer_phone`,
    cb.`created_by`, cb.`created_at`, cb.`updated_at`
FROM `conferences_backup` cb
LEFT JOIN `categories` cat ON cat.`name` = cb.`category`
LEFT JOIN `venues` ven ON ven.`name` = cb.`location`
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `start_date` = VALUES(`start_date`),
    `end_date` = VALUES(`end_date`),
    `price` = VALUES(`price`),
    `capacity` = VALUES(`capacity`),
    `current_attendees` = VALUES(`current_attendees`),
    `updated_at` = NOW();

-- 7. Migrate registrations data
INSERT INTO `registrations` (
    `id`, `user_id`, `conference_id`, `registration_code`, `status`, 
    `registration_date`, `updated_at`, `notes`
)
SELECT 
    rb.`id`, rb.`user_id`, rb.`conference_id`,
    CONCAT('REG', YEAR(rb.`registration_date`), LPAD(rb.`id`, 6, '0')) as registration_code,
    rb.`status`, rb.`registration_date`, rb.`updated_at`, rb.`notes`
FROM `registrations_backup` rb
ON DUPLICATE KEY UPDATE
    `status` = VALUES(`status`),
    `updated_at` = NOW();

-- 8. Migrate conference_speakers data if exists
INSERT IGNORE INTO `conference_speakers` (
    `id`, `conference_id`, `speaker_id`, `status`, `created_at`
)
SELECT 
    csb.`id`, csb.`conference_id`, csb.`speaker_id`, 
    'confirmed' as status, csb.`created_at`
FROM `conference_speakers_backup` csb
WHERE EXISTS (SELECT 1 FROM `conferences` WHERE `id` = csb.`conference_id`)
AND EXISTS (SELECT 1 FROM `speakers` WHERE `id` = csb.`speaker_id`);

-- 9. Migrate schedule data if exists
INSERT IGNORE INTO `schedule_sessions` (
    `id`, `conference_id`, `title`, `description`, `session_date`, 
    `start_time`, `end_time`, `speaker_id`, `created_at`, `updated_at`
)
SELECT 
    csb.`id`, csb.`conference_id`, csb.`title`, csb.`description`, 
    csb.`eventDate`, csb.`startTime`, csb.`endTime`,
    (SELECT `id` FROM `speakers` WHERE `name` = csb.`speaker` LIMIT 1) as speaker_id,
    csb.`created_at`, csb.`updated_at`
FROM `conference_schedule_backup` csb
WHERE EXISTS (SELECT 1 FROM `conferences` WHERE `id` = csb.`conference_id`);

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- POST-MIGRATION UPDATES
-- ========================================================

-- Update conference attendees count
UPDATE `conferences` c
SET `current_attendees` = (
    SELECT COUNT(*) FROM `registrations` r 
    WHERE r.`conference_id` = c.`id` 
    AND r.`status` IN ('confirmed', 'attended')
);

-- Update speaker total talks
UPDATE `speakers` s
SET `total_talks` = (
    SELECT COUNT(*) FROM `conference_speakers` cs 
    WHERE cs.`speaker_id` = s.`id` 
    AND cs.`status` = 'confirmed'
);

-- Create slugs for existing records without slugs
UPDATE `conferences` 
SET `slug` = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`title`, ' ', '-'), 'ă', 'a'), 'đ', 'd'), 'ư', 'u'), 'ô', 'o'))
WHERE `slug` IS NULL OR `slug` = '';

UPDATE `categories` 
SET `slug` = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`name`, ' ', '-'), 'ă', 'a'), 'đ', 'd'), 'ư', 'u'), 'ô', 'o'))
WHERE `slug` IS NULL OR `slug` = '';

UPDATE `venues` 
SET `slug` = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`name`, ' ', '-'), 'ă', 'a'), 'đ', 'd'), 'ư', 'u'), 'ô', 'o'))
WHERE `slug` IS NULL OR `slug` = '';

UPDATE `speakers` 
SET `slug` = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`name`, ' ', '-'), 'ă', 'a'), 'đ', 'd'), 'ư', 'u'), 'ô', 'o'))
WHERE `slug` IS NULL OR `slug` = '';

-- ========================================================
-- VERIFICATION QUERIES
-- ========================================================

-- Kiểm tra số lượng records sau migration
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'categories' as table_name, COUNT(*) as count FROM categories
UNION ALL
SELECT 'venues' as table_name, COUNT(*) as count FROM venues
UNION ALL
SELECT 'speakers' as table_name, COUNT(*) as count FROM speakers
UNION ALL
SELECT 'conferences' as table_name, COUNT(*) as count FROM conferences
UNION ALL
SELECT 'registrations' as table_name, COUNT(*) as count FROM registrations
UNION ALL
SELECT 'conference_speakers' as table_name, COUNT(*) as count FROM conference_speakers;

-- Kiểm tra dữ liệu conferences
SELECT 
    c.id, c.title, c.slug, c.start_date, c.status,
    cat.name as category_name,
    ven.name as venue_name,
    c.current_attendees, c.capacity
FROM conferences c
LEFT JOIN categories cat ON c.category_id = cat.id
LEFT JOIN venues ven ON c.venue_id = ven.id
LIMIT 10;

-- Kiểm tra registrations
SELECT 
    r.id, r.registration_code, r.status, 
    u.firstName, u.lastName, c.title as conference_title
FROM registrations r
JOIN users u ON r.user_id = u.id
JOIN conferences c ON r.conference_id = c.id
LIMIT 10;

-- ========================================================
-- CLEANUP (Chạy sau khi đã kiểm tra và xác nhận migration thành công)
-- ========================================================

-- Uncomment để xóa bảng backup sau khi migration thành công
-- DROP TABLE IF EXISTS `users_backup`;
-- DROP TABLE IF EXISTS `conferences_backup`;
-- DROP TABLE IF EXISTS `registrations_backup`;
-- DROP TABLE IF EXISTS `speakers_backup`;

COMMIT;

-- ========================================================
-- NOTES
-- ========================================================
-- 1. Chạy schema_complete.sql trước khi chạy migration này
-- 2. Backup database trước khi migration
-- 3. Kiểm tra kết quả migration bằng verification queries
-- 4. Chỉ cleanup backup tables sau khi đã xác nhận migration thành công
-- 5. Cập nhật code PHP để sử dụng structure mới
-- ========================================================
