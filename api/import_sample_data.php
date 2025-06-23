<?php
/**
 * API nhập dữ liệu mẫu vào database
 * Phiên bản: 3.0 (Complete Edition) - Tương thích với schema_complete.sql
 */

// Define this file as an API endpoint
define('API_ENDPOINT', true);

// Set content type for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once dirname(__DIR__) . '/includes/config.php';

$result = [
    'success' => false,
    'messages' => [],    'data' => [
        'users' => 0,
        'languages' => 0,
        'translations' => 0,
        'categories' => 0,
        'venues' => 0,
        'speakers' => 0,
        'conferences' => 0,
        'conference_speakers' => 0,
        'payment_methods' => 0,
        'media_folders' => 0,
        'scheduled_tasks' => 0,
        'settings' => 0,
        'user_activity_logs' => 0,
        'invoices' => 0,
        'transactions' => 0,
        'error_logs' => 0
    ]
];

try {
    // Kết nối database
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $result['messages'][] = 'Kết nối database thành công';

    // Kiểm tra xem đã có sample data chưa
    $existingUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    if ($existingUsers > 0) {
        $result['messages'][] = "Đã có $existingUsers users trong hệ thống. Chỉ import dữ liệu còn thiếu...";
    }

    $result['messages'][] = 'Bắt đầu import sample data từ schema_complete.sql...';

    // 1. Users (nếu chưa có)
    if ($existingUsers == 0) {
        $result['messages'][] = '1. Đang thêm sample users...';
        
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        $usersSql = "INSERT INTO `users` (`firstName`, `lastName`, `email`, `password`, `role`, `status`, `email_verified`, `language`, `timezone`, `created_at`) VALUES
            ('Admin', 'System', 'admin@confab.local', :password, 'admin', 'active', 1, 'vi', 'Asia/Ho_Chi_Minh', NOW()),
            ('Nguyễn', 'Tổ Chức', 'organizer@confab.local', :password, 'organizer', 'active', 1, 'vi', 'Asia/Ho_Chi_Minh', NOW()),
            ('Trần', 'Diễn Giả', 'speaker@confab.local', :password, 'speaker', 'active', 1, 'vi', 'Asia/Ho_Chi_Minh', NOW()),
            ('Lê', 'Tham Dự', 'user@confab.local', :password, 'user', 'active', 1, 'vi', 'Asia/Ho_Chi_Minh', NOW())";
            
        $stmt = $pdo->prepare($usersSql);
        $stmt->execute(['password' => $hashedPassword]);
        
        $result['data']['users'] = $pdo->lastInsertId() ? 4 : 0;
        $result['messages'][] = "✓ Đã thêm {$result['data']['users']} users (password: password123)";
    } else {
        $result['data']['users'] = $existingUsers;
        $result['messages'][] = "✓ Đã có {$existingUsers} users trong hệ thống";
    }
    
    // 2. Languages
    $result['messages'][] = '2. Đang thêm languages...';
    $existingLanguages = $pdo->query("SELECT COUNT(*) FROM languages")->fetchColumn();
    
    if ($existingLanguages == 0) {
        $languagesSql = "INSERT INTO `languages` (`code`, `name`, `native_name`, `direction`, `flag`, `is_default`, `is_active`, `sort_order`) VALUES
            ('vi', 'Vietnamese', 'Tiếng Việt', 'ltr', '🇻🇳', 1, 1, 1),
            ('en', 'English', 'English', 'ltr', '🇺🇸', 0, 1, 2),
            ('zh', 'Chinese', '中文', 'ltr', '🇨🇳', 0, 1, 3),
            ('ja', 'Japanese', '日本語', 'ltr', '🇯🇵', 0, 1, 4)";
            
        $pdo->exec($languagesSql);
        $result['data']['languages'] = 4;
        $result['messages'][] = "✓ Đã thêm {$result['data']['languages']} languages";
    } else {
        $result['data']['languages'] = $existingLanguages;
        $result['messages'][] = "✓ Đã có {$existingLanguages} languages trong hệ thống";
    }

    // 3. Translations
    $result['messages'][] = '3. Đang thêm translations...';
    $existingTranslations = $pdo->query("SELECT COUNT(*) FROM translations")->fetchColumn();
    
    if ($existingTranslations == 0) {
        $translationsSql = "INSERT INTO `translations` (`lang_code`, `translation_key`, `translation_value`) VALUES
            ('vi', 'app.name', 'Confab Web Oasis'),
            ('en', 'app.name', 'Confab Web Oasis'),
            ('vi', 'app.description', 'Hệ thống quản lý hội nghị chuyên nghiệp'),
            ('en', 'app.description', 'Professional Conference Management System'),
            ('vi', 'menu.home', 'Trang chủ'),
            ('en', 'menu.home', 'Home'),
            ('vi', 'menu.conferences', 'Hội nghị'),
            ('en', 'menu.conferences', 'Conferences'),
            ('vi', 'menu.speakers', 'Diễn giả'),
            ('en', 'menu.speakers', 'Speakers'),
            ('vi', 'button.register', 'Đăng ký'),
            ('en', 'button.register', 'Register'),
            ('vi', 'button.login', 'Đăng nhập'),
            ('en', 'button.login', 'Login'),
            ('vi', 'status.active', 'Hoạt động'),
            ('en', 'status.active', 'Active')";
            
        $pdo->exec($translationsSql);
        $result['data']['translations'] = 16;
        $result['messages'][] = "✓ Đã thêm {$result['data']['translations']} translations";
    } else {
        $result['data']['translations'] = $existingTranslations;
        $result['messages'][] = "✓ Đã có {$existingTranslations} translations trong hệ thống";
    }

    // 4. Categories
    $result['messages'][] = '4. Đang thêm categories...';
    $existingCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    
    if ($existingCategories == 0) {
        $categoriesSql = "INSERT INTO `categories` (`name`, `slug`, `description`, `color`, `icon`, `is_featured`, `status`) VALUES
            ('Công nghệ thông tin', 'cong-nghe-thong-tin', 'Hội nghị về công nghệ thông tin và phần mềm', '#007bff', 'fas fa-laptop-code', 1, 'active'),
            ('Kinh doanh', 'kinh-doanh', 'Hội nghị về quản trị kinh doanh và khởi nghiệp', '#28a745', 'fas fa-chart-line', 1, 'active'),
            ('Y tế', 'y-te', 'Hội nghị y khoa và chăm sóc sức khỏe', '#dc3545', 'fas fa-heartbeat', 0, 'active'),
            ('Giáo dục', 'giao-duc', 'Hội nghị về giáo dục và đào tạo', '#ffc107', 'fas fa-graduation-cap', 0, 'active'),
            ('Khoa học', 'khoa-hoc', 'Hội nghị khoa học và nghiên cứu', '#6f42c1', 'fas fa-microscope', 0, 'active')";
            
        $pdo->exec($categoriesSql);
        $result['data']['categories'] = 5;
        $result['messages'][] = "✓ Đã thêm {$result['data']['categories']} categories";
    } else {
        $result['data']['categories'] = $existingCategories;
        $result['messages'][] = "✓ Đã có {$existingCategories} categories trong hệ thống";
    }

    // 5. Venues
    $result['messages'][] = '5. Đang thêm venues...';
    $existingVenues = $pdo->query("SELECT COUNT(*) FROM venues")->fetchColumn();
    
    if ($existingVenues == 0) {
        $venuesSql = "INSERT INTO `venues` (`name`, `slug`, `description`, `address`, `city`, `country`, `capacity`, `contact_name`, `contact_email`, `contact_phone`, `status`) VALUES
            ('Trung tâm Hội nghị Quốc gia', 'trung-tam-hoi-nghi-quoc-gia', 'Trung tâm hội nghị lớn nhất Việt Nam với đầy đủ tiện ích hiện đại', 'Đường Thành Thái, Quận 10, TP.HCM', 'Hồ Chí Minh', 'Vietnam', 2000, 'Nguyễn Văn A', 'venue1@confab.local', '028-1234-5678', 'active'),
            ('Khách sạn Rex', 'khach-san-rex', 'Khách sạn 5 sao với phòng hội nghị sang trọng', '141 Nguyễn Huệ, Quận 1, TP.HCM', 'Hồ Chí Minh', 'Vietnam', 500, 'Trần Thị B', 'venue2@confab.local', '028-8765-4321', 'active'),
            ('Đại học Bách Khoa', 'dai-hoc-bach-khoa', 'Giảng đường hiện đại tại Đại học Bách Khoa TP.HCM', '268 Lý Thường Kiệt, Quận 10, TP.HCM', 'Hồ Chí Minh', 'Vietnam', 1000, 'PGS. Lê Văn C', 'venue3@confab.local', '028-1111-2222', 'active')";
            
        $pdo->exec($venuesSql);
        $result['data']['venues'] = 3;
        $result['messages'][] = "✓ Đã thêm {$result['data']['venues']} venues";
    } else {
        $result['data']['venues'] = $existingVenues;
        $result['messages'][] = "✓ Đã có {$existingVenues} venues trong hệ thống";
    }

    // 6. Speakers
    $result['messages'][] = '6. Đang thêm speakers...';
    $existingSpeakers = $pdo->query("SELECT COUNT(*) FROM speakers")->fetchColumn();
    
    if ($existingSpeakers == 0) {
        $speakersSql = "INSERT INTO `speakers` (`user_id`, `name`, `slug`, `title`, `company`, `bio`, `email`, `status`) VALUES
            (3, 'Trần Diễn Giả', 'tran-dien-gia', 'CEO & Founder', 'TechViet Solutions', 'Chuyên gia công nghệ với hơn 15 năm kinh nghiệm trong lĩnh vực phát triển phần mềm và quản lý dự án công nghệ.', 'speaker@confab.local', 'active'),
            (NULL, 'Dr. Nguyễn Khoa Học', 'dr-nguyen-khoa-hoc', 'Giáo sư', 'Đại học Bách Khoa', 'Tiến sĩ về Trí tuệ nhân tạo và Machine Learning, tác giả của nhiều nghiên cứu được công bố quốc tế.', 'nguyenkhoahoc@example.com', 'active'),
            (NULL, 'Phạm Kinh Doanh', 'pham-kinh-doanh', 'Giám đốc điều hành', 'Startup Hub Vietnam', 'Doanh nhân thành công với kinh nghiệm xây dựng và phát triển nhiều startup công nghệ tại Việt Nam.', 'phamkinhdoanh@example.com', 'active')";
            
        $pdo->exec($speakersSql);
        $result['data']['speakers'] = 3;
        $result['messages'][] = "✓ Đã thêm {$result['data']['speakers']} speakers";
    } else {
        $result['data']['speakers'] = $existingSpeakers;
        $result['messages'][] = "✓ Đã có {$existingSpeakers} speakers trong hệ thống";
    }

    // 7. Conferences
    $result['messages'][] = '7. Đang thêm conferences...';
    $existingConferences = $pdo->query("SELECT COUNT(*) FROM conferences")->fetchColumn();
    
    if ($existingConferences == 0) {
        $conferencesSql = "INSERT INTO `conferences` (`title`, `slug`, `short_description`, `description`, `start_date`, `end_date`, `category_id`, `venue_id`, `location`, `type`, `format`, `price`, `currency`, `capacity`, `status`, `featured`, `created_by`) VALUES
            ('Vietnam Tech Summit 2024', 'vietnam-tech-summit-2024', 'Hội nghị công nghệ lớn nhất Việt Nam năm 2024', 'Hội nghị tập trung vào các xu hướng công nghệ mới như AI, Blockchain, IoT và Digital Transformation. Sự kiện quy tụ hơn 1000 chuyên gia công nghệ hàng đầu.', '2024-12-15 08:00:00', '2024-12-16 18:00:00', 1, 1, 'TP. Hồ Chí Minh', 'in_person', 'conference', 2500000.00, 'VND', 1000, 'published', 1, 2),
            ('Startup Weekend Ho Chi Minh', 'startup-weekend-hcm', 'Cuối tuần khởi nghiệp dành cho các bạn trẻ có ý tưởng kinh doanh', 'Sự kiện 54 giờ liên tục giúp các bạn trẻ biến ý tưởng thành startup thực tế. Có sự tham gia của các mentor và nhà đầu tư hàng đầu.', '2024-11-30 18:00:00', '2024-12-02 20:00:00', 2, 3, 'TP. Hồ Chí Minh', 'in_person', 'workshop', 500000.00, 'VND', 200, 'published', 1, 2),
            ('Digital Health Conference 2024', 'digital-health-conference-2024', 'Hội nghị về công nghệ số trong y tế', 'Khám phá những ứng dụng công nghệ mới nhất trong lĩnh vực chăm sóc sức khỏe, từ telemedicine đến AI trong chẩn đoán y khoa.', '2024-12-20 08:30:00', '2024-12-20 17:30:00', 3, 2, 'TP. Hồ Chí Minh', 'hybrid', 'conference', 1500000.00, 'VND', 300, 'published', 0, 2)";
            
        $pdo->exec($conferencesSql);
        $result['data']['conferences'] = 3;
        $result['messages'][] = "✓ Đã thêm {$result['data']['conferences']} conferences";
    } else {
        $result['data']['conferences'] = $existingConferences;
        $result['messages'][] = "✓ Đã có {$existingConferences} conferences trong hệ thống";
    }

    // 8. Conference Speakers
    $result['messages'][] = '8. Đang thêm conference speakers...';
    $existingConferenceSpeakers = $pdo->query("SELECT COUNT(*) FROM conference_speakers")->fetchColumn();
    
    if ($existingConferenceSpeakers == 0) {
        $conferenceSpeakersSql = "INSERT INTO `conference_speakers` (`conference_id`, `speaker_id`, `role`, `talk_title`, `talk_description`, `status`) VALUES
            (1, 1, 'keynote', 'Tương lai của AI trong phát triển phần mềm', 'Phân tích xu hướng và tác động của trí tuệ nhân tạo đến ngành công nghiệp phần mềm trong 5 năm tới.', 'confirmed'),
            (1, 2, 'speaker', 'Machine Learning cho người mới bắt đầu', 'Hướng dẫn cơ bản về Machine Learning và các ứng dụng thực tế trong doanh nghiệp.', 'confirmed'),
            (2, 3, 'keynote', 'Xây dựng startup công nghệ bền vững', 'Chia sẻ kinh nghiệm và bài học từ việc xây dựng các startup công nghệ thành công.', 'confirmed'),
            (3, 2, 'speaker', 'AI trong chẩn đoán y khoa', 'Ứng dụng học máy và thị giác máy tính trong việc chẩn đoán và điều trị bệnh.', 'confirmed')";
            
        $pdo->exec($conferenceSpeakersSql);
        $result['data']['conference_speakers'] = 4;
        $result['messages'][] = "✓ Đã thêm {$result['data']['conference_speakers']} conference speakers";
    } else {
        $result['data']['conference_speakers'] = $existingConferenceSpeakers;
        $result['messages'][] = "✓ Đã có {$existingConferenceSpeakers} conference speakers trong hệ thống";
    }

    // 9. Payment Methods
    $result['messages'][] = '9. Đang thêm payment methods...';
    $existingPaymentMethods = $pdo->query("SELECT COUNT(*) FROM payment_methods")->fetchColumn();
    
    if ($existingPaymentMethods == 0) {
        $paymentMethodsSql = "INSERT INTO `payment_methods` (`name`, `type`, `provider`, `currency`, `is_active`, `sort_order`) VALUES
            ('Chuyển khoản ngân hàng', 'bank_transfer', 'manual', 'VND', 1, 1),
            ('Ví MoMo', 'e_wallet', 'momo', 'VND', 1, 2),
            ('ZaloPay', 'e_wallet', 'zalopay', 'VND', 1, 3),
            ('Thẻ tín dụng/ghi nợ', 'credit_card', 'stripe', 'VND', 1, 4),
            ('PayPal', 'paypal', 'paypal', 'USD', 1, 5)";
            
        $pdo->exec($paymentMethodsSql);
        $result['data']['payment_methods'] = 5;
        $result['messages'][] = "✓ Đã thêm {$result['data']['payment_methods']} payment methods";
    } else {
        $result['data']['payment_methods'] = $existingPaymentMethods;
        $result['messages'][] = "✓ Đã có {$existingPaymentMethods} payment methods trong hệ thống";
    }

    // 10. Media Folders
    $result['messages'][] = '10. Đang thêm media folders...';
    $existingMediaFolders = $pdo->query("SELECT COUNT(*) FROM media_folders")->fetchColumn();
    
    if ($existingMediaFolders == 0) {
        $mediaFoldersSql = "INSERT INTO `media_folders` (`name`, `slug`, `description`, `parent_id`, `is_public`, `created_by`) VALUES
            ('Conferences', 'conferences', 'Thư mục chứa hình ảnh và tài liệu hội nghị', NULL, 1, 1),
            ('Speakers', 'speakers', 'Thư mục chứa ảnh diễn giả', NULL, 1, 1),
            ('Venues', 'venues', 'Thư mục chứa ảnh địa điểm tổ chức', NULL, 1, 1),
            ('Certificates', 'certificates', 'Thư mục chứa mẫu chứng chỉ', NULL, 0, 1),
            ('Documents', 'documents', 'Thư mục chứa tài liệu và slide', 1, 1, 1)";
            
        $pdo->exec($mediaFoldersSql);
        $result['data']['media_folders'] = 5;
        $result['messages'][] = "✓ Đã thêm {$result['data']['media_folders']} media folders";
    } else {
        $result['data']['media_folders'] = $existingMediaFolders;
        $result['messages'][] = "✓ Đã có {$existingMediaFolders} media folders trong hệ thống";
    }

    // 11. Scheduled Tasks
    $result['messages'][] = '11. Đang thêm scheduled tasks...';
    $existingScheduledTasks = $pdo->query("SELECT COUNT(*) FROM scheduled_tasks")->fetchColumn();
    
    if ($existingScheduledTasks == 0) {
        $scheduledTasksSql = "INSERT INTO `scheduled_tasks` (`name`, `description`, `command`, `schedule`, `is_active`) VALUES
            ('Gửi email nhắc nhở', 'Gửi email nhắc nhở trước hội nghị 24h', 'php /path/to/send_reminders.php', '0 9 * * *', 1),
            ('Backup database', 'Sao lưu cơ sở dữ liệu hàng ngày', 'php /path/to/backup_db.php', '0 2 * * *', 1),
            ('Làm sạch logs cũ', 'Xóa logs cũ hơn 30 ngày', 'php /path/to/cleanup_logs.php', '0 3 * * 0', 1),
            ('Cập nhật thống kê', 'Cập nhật báo cáo thống kê hệ thống', 'php /path/to/update_stats.php', '0 1 * * *', 1)";
            
        $pdo->exec($scheduledTasksSql);
        $result['data']['scheduled_tasks'] = 4;
        $result['messages'][] = "✓ Đã thêm {$result['data']['scheduled_tasks']} scheduled tasks";
    } else {
        $result['data']['scheduled_tasks'] = $existingScheduledTasks;
        $result['messages'][] = "✓ Đã có {$existingScheduledTasks} scheduled tasks trong hệ thống";
    }

    // 12. Settings
    $result['messages'][] = '12. Đang thêm system settings...';
    $existingSettings = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    
    if ($existingSettings == 0) {
        $settingsSql = "INSERT INTO `settings` (`key`, `value`, `type`, `group`, `label`, `description`, `is_public`) VALUES
            ('site_name', 'Confab Web Oasis', 'string', 'general', 'Tên website', 'Tên hiển thị của website', 1),
            ('site_description', 'Hệ thống quản lý hội nghị chuyên nghiệp', 'string', 'general', 'Mô tả website', 'Mô tả ngắn về website', 1),
            ('default_language', 'vi', 'string', 'localization', 'Ngôn ngữ mặc định', 'Ngôn ngữ mặc định của hệ thống', 1),
            ('default_timezone', 'Asia/Ho_Chi_Minh', 'string', 'general', 'Múi giờ mặc định', 'Múi giờ mặc định của hệ thống', 1),
            ('email_from_address', 'noreply@confab.local', 'string', 'email', 'Email gửi đi', 'Địa chỉ email mặc định cho gửi thông báo', 0),
            ('email_from_name', 'Confab Web Oasis', 'string', 'email', 'Tên người gửi', 'Tên hiển thị khi gửi email', 0),
            ('registration_enabled', '1', 'boolean', 'conference', 'Cho phép đăng ký', 'Bật/tắt tính năng đăng ký hội nghị', 1),
            ('certificate_enabled', '1', 'boolean', 'conference', 'Bật chứng chỉ', 'Cho phép tạo chứng chỉ tham dự', 1),
            ('max_file_size', '10485760', 'integer', 'media', 'Kích thước file tối đa', 'Kích thước file upload tối đa (bytes)', 0),
            ('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,ppt,pptx', 'string', 'media', 'Loại file cho phép', 'Danh sách extension file được phép upload', 0)";
            
        $pdo->exec($settingsSql);
        $result['data']['settings'] = 10;
        $result['messages'][] = "✓ Đã thêm {$result['data']['settings']} system settings";
    } else {
        $result['data']['settings'] = $existingSettings;
        $result['messages'][] = "✓ Đã có {$existingSettings} settings trong hệ thống";
    }

    // Tính tổng dữ liệu đã import
    $totalImported = array_sum($result['data']);
    
    $result['success'] = true;
    $result['messages'][] = "🎉 Hoàn thành! Đã import tổng cộng {$totalImported} bản ghi sample data";
    $result['messages'][] = "📧 Tài khoản test: admin@confab.local / organizer@confab.local / speaker@confab.local / user@confab.local";
    $result['messages'][] = "🔑 Mật khẩu: password123";

} catch (Exception $e) {
    $result['success'] = false;
    $result['messages'][] = '❌ Lỗi khi import sample data: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
        ['name' => 'Y tế', 'slug' => 'y-te', 'description' => 'Hội nghị y học, sức khỏe và nghiên cứu y tế', 'color' => '#28a745', 'icon' => 'fas fa-heartbeat'],
        ['name' => 'Giáo dục', 'slug' => 'giao-duc', 'description' => 'Các sự kiện về giáo dục, đào tạo và phát triển', 'color' => '#ffc107', 'icon' => 'fas fa-graduation-cap'],
        ['name' => 'Kinh doanh', 'slug' => 'kinh-doanh', 'description' => 'Hội nghị kinh doanh, tài chính và quản lý', 'color' => '#17a2b8', 'icon' => 'fas fa-chart-line'],
        ['name' => 'Marketing', 'slug' => 'marketing', 'description' => 'Sự kiện về marketing, quảng cáo và truyền thông', 'color' => '#e83e8c', 'icon' => 'fas fa-bullhorn'],
        ['name' => 'Khoa học', 'slug' => 'khoa-hoc', 'description' => 'Hội nghị khoa học, nghiên cứu và phát triển', 'color' => '#6f42c1', 'icon' => 'fas fa-flask']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO categories (name, slug, description, color, icon, status, is_featured, created_at)
        VALUES (?, ?, ?, ?, ?, 'active', ?, NOW())
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name), 
            description = VALUES(description),
            color = VALUES(color),
            icon = VALUES(icon),
            updated_at = NOW()
    ");

    foreach ($categories as $index => $category) {
        $stmt->execute([
            $category['name'],
            $category['slug'],
            $category['description'],
            $category['color'],
            $category['icon'],
            ($index < 3) ? 1 : 0 // Featured cho 3 category đầu
        ]);
        $categoriesCount++;
    }

    $result['messages'][] = "Đã thêm {$categoriesCount} categories.";

    // 2. Thêm venues
    $result['messages'][] = '2. Đang thêm venues...';
    
    $venues = [
        [
            'name' => 'Trung tâm Hội nghị Quốc gia',
            'slug' => 'trung-tam-hoi-nghi-quoc-gia',
            'description' => 'Trung tâm hội nghị hiện đại với đầy đủ tiện nghi',
            'address' => '8 Lê Thánh Tông, Hoàn Kiếm',
            'city' => 'Hà Nội',
            'country' => 'Vietnam',
            'capacity' => 3000,
            'contact_name' => 'Nguyễn Văn A',
            'contact_phone' => '+84 24 3936 2020',
            'contact_email' => 'info@ncc.gov.vn'
        ],
        [
            'name' => 'Saigon Convention Center',
            'slug' => 'saigon-convention-center',
            'description' => 'Trung tâm hội nghị lớn nhất TP.HCM',
            'address' => '28C Nguyễn Đình Chiểu, Quận 3',
            'city' => 'TP. Hồ Chí Minh',
            'country' => 'Vietnam',
            'capacity' => 2500,
            'contact_name' => 'Trần Thị B',
            'contact_phone' => '+84 28 3930 9999',
            'contact_email' => 'contact@scc.com.vn'
        ],
        [
            'name' => 'Ariyana Convention Centre',
            'slug' => 'ariyana-convention-centre',
            'description' => 'Trung tâm hội nghị ven biển Đà Nẵng',
            'address' => 'Ariyana Beach Resort & Suites',
            'city' => 'Đà Nẵng',
            'country' => 'Vietnam',
            'capacity' => 1500,
            'contact_name' => 'Lê Văn C',
            'contact_phone' => '+84 236 3959 999',
            'contact_email' => 'events@ariyana.com'
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO venues (name, slug, description, address, city, country, capacity, 
                          contact_name, contact_phone, contact_email, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            description = VALUES(description),
            updated_at = NOW()
    ");

    foreach ($venues as $venue) {
        $stmt->execute([
            $venue['name'],
            $venue['slug'],
            $venue['description'],
            $venue['address'],
            $venue['city'],
            $venue['country'],
            $venue['capacity'],
            $venue['contact_name'],
            $venue['contact_phone'],
            $venue['contact_email']
        ]);
        $venuesCount++;
    }

    $result['messages'][] = "Đã thêm {$venuesCount} venues.";

    // 3. Thêm users
    $result['messages'][] = '3. Đang thêm users...';
    
    $users = [
        [
            'firstName' => 'Admin',
            'lastName' => 'System',
            'email' => 'admin@confab.local',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'phone' => '0123456789',
            'status' => 'active',
            'email_verified' => 1
        ],
        [
            'firstName' => 'Nguyễn',
            'lastName' => 'Văn Nam',
            'email' => 'nam.nguyen@email.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user',
            'phone' => '0987654321',
            'status' => 'active',
            'email_verified' => 1,
            'company' => 'FPT Software',
            'position' => 'Senior Developer'
        ],
        [
            'firstName' => 'Trần',
            'lastName' => 'Minh Tuấn',
            'email' => 'tuan.tran@email.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'phone' => '0909123456',
            'status' => 'active',
            'email_verified' => 1,
            'company' => 'VnTech Media',
            'position' => 'Event Manager'
        ],
        [
            'firstName' => 'Lê',
            'lastName' => 'Thị Hoa',
            'email' => 'hoa.le@email.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'speaker',
            'phone' => '0912345678',
            'status' => 'active',
            'email_verified' => 1,
            'company' => 'Viettel Group',
            'position' => 'AI Research Director'
        ]
    ];    // Check if status column exists
    $hasStatusColumn = false;
    try {
        $columnsQuery = $pdo->query("SHOW COLUMNS FROM users LIKE 'status'");
        $hasStatusColumn = $columnsQuery->rowCount() > 0;
    } catch (Exception $e) {
        $result['messages'][] = 'Không thể kiểm tra cột status: ' . $e->getMessage();
    }

    // Prepare the query based on whether status column exists
    if ($hasStatusColumn) {
        $stmt = $pdo->prepare("
            INSERT INTO users (firstName, lastName, email, password, role, phone, status, email_verified, 
                            company, position, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                firstName = VALUES(firstName), 
                lastName = VALUES(lastName), 
                role = VALUES(role), 
                phone = VALUES(phone),
                company = VALUES(company),
                position = VALUES(position),
                updated_at = NOW()
        ");
    } else {        // Fallback query without status column
        $stmt = $pdo->prepare("
            INSERT INTO users (firstName, lastName, email, password, role, phone, email_verified, 
                            company, position, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                firstName = VALUES(firstName), 
                lastName = VALUES(lastName), 
                role = VALUES(role), 
                phone = VALUES(phone),
                company = VALUES(company),
                position = VALUES(position),
                updated_at = NOW()
        ");
    }    foreach ($users as $user) {
        try {
            if ($hasStatusColumn) {
                $stmt->execute([
                    $user['firstName'],
                    $user['lastName'],
                    $user['email'],
                    $user['password'],
                    $user['role'],
                    $user['phone'],
                    $user['status'],
                    $user['email_verified'],
                    $user['company'] ?? null,
                    $user['position'] ?? null
                ]);
            } else {
                // Execute without status field
                $stmt->execute([
                    $user['firstName'],
                    $user['lastName'],
                    $user['email'],
                    $user['password'],
                    $user['role'],
                    $user['phone'],
                    $user['email_verified'],
                    $user['company'] ?? null,
                    $user['position'] ?? null
                ]);
            }
            $usersCount++;
        } catch (PDOException $e) {
            $result['messages'][] = 'Lỗi khi thêm user ' . $user['email'] . ': ' . $e->getMessage();
            // Continue with other users
            continue;
        }
    }

    $result['messages'][] = "Đã thêm {$usersCount} users.";

    // 4. Thêm speakers
    $result['messages'][] = '4. Đang thêm speakers...';
    
    // Lấy user có role speaker
    $speakerUsers = $pdo->query("SELECT id, firstName, lastName, email, company, position FROM users WHERE role IN ('speaker', 'organizer')")->fetchAll(PDO::FETCH_ASSOC);
    
    $speakers = [
        [
            'name' => 'Nguyễn Thị Minh',
            'slug' => 'nguyen-thi-minh',
            'title' => 'CEO, InnovateTech Vietnam',
            'company' => 'InnovateTech Vietnam',
            'bio' => 'Chuyên gia hàng đầu về AI và học máy với hơn 15 năm kinh nghiệm trong ngành công nghệ. Bà Minh đã lãnh đạo nhiều dự án AI thành công và là diễn giả tại các hội nghị quốc tế.',
            'short_bio' => 'CEO InnovateTech Vietnam, Chuyên gia AI hàng đầu',
            'email' => 'minh.nguyen@innovatetech.vn',
            'specialties' => '["Artificial Intelligence", "Machine Learning", "Deep Learning", "Computer Vision"]',
            'languages' => '["Vietnamese", "English", "Japanese"]',
            'experience_years' => 15,
            'status' => 'active'
        ],
        [
            'name' => 'Trần Đức Khải',
            'slug' => 'tran-duc-khai',
            'title' => 'CTO, VietStartup',
            'company' => 'VietStartup',
            'bio' => 'Tiên phong trong công nghệ blockchain tại Việt Nam. Anh Khải có kinh nghiệm phát triển các ứng dụng blockchain cho ngành tài chính và là tác giả của nhiều bài nghiên cứu về cryptocurrency.',
            'short_bio' => 'CTO VietStartup, Chuyên gia Blockchain',
            'email' => 'khai.tran@vietstartup.com',
            'specialties' => '["Blockchain", "Cryptocurrency", "Smart Contracts", "DeFi"]',
            'languages' => '["Vietnamese", "English"]',
            'experience_years' => 12,
            'status' => 'active'
        ],
        [
            'name' => 'Phạm Thị Hương',
            'slug' => 'pham-thi-huong',
            'title' => 'Founder, GreenTech Solutions',
            'company' => 'GreenTech Solutions',
            'bio' => 'Chuyên gia về phát triển bền vững và năng lượng sạch. Cô Hương đã khởi nghiệp thành công với các giải pháp công nghệ xanh và được vinh danh là "Doanh nhân trẻ xuất sắc năm 2023".',
            'short_bio' => 'Founder GreenTech Solutions, Chuyên gia Năng lượng sạch',
            'email' => 'huong.pham@greentech.vn',
            'specialties' => '["Green Technology", "Renewable Energy", "Sustainability", "Environmental Tech"]',
            'languages' => '["Vietnamese", "English", "French"]',
            'experience_years' => 8,
            'status' => 'active'
        ],
        [
            'name' => 'Lê Văn Bách',
            'slug' => 'le-van-bach',
            'title' => 'AI Research Director, FPT Software',
            'company' => 'FPT Software',
            'bio' => 'Tiến sĩ AI với hơn 15 năm kinh nghiệm nghiên cứu và ứng dụng thực tế. Ông Bách đã dẫn dắt nhiều dự án AI cho các tập đoàn lớn và có hơn 50 bài báo khoa học được công bố quốc tế.',
            'short_bio' => 'AI Research Director FPT Software, Tiến sĩ AI',
            'email' => 'bach.le@fpt.com.vn',
            'specialties' => '["AI Research", "Data Science", "Natural Language Processing", "Computer Vision"]',
            'languages' => '["Vietnamese", "English", "Korean"]',
            'experience_years' => 15,
            'status' => 'active'
        ],
        [
            'name' => 'Đinh Thu Trang',
            'slug' => 'dinh-thu-trang',
            'title' => 'Cloud Architect, AWS Vietnam',
            'company' => 'AWS Vietnam',
            'bio' => 'Chuyên gia giải pháp đám mây cho doanh nghiệp lớn với chứng chỉ AWS Solutions Architect Professional. Cô Trang đã tư vấn chuyển đổi số cho hàng trăm doanh nghiệp Việt Nam.',
            'short_bio' => 'Cloud Architect AWS Vietnam, Chuyên gia AWS',
            'email' => 'trang.dinh@aws.com',
            'specialties' => '["Cloud Computing", "AWS", "DevOps", "Microservices"]',
            'languages' => '["Vietnamese", "English"]',
            'experience_years' => 10,
            'status' => 'active'
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO speakers (user_id, name, slug, title, company, bio, short_bio, email, 
                            specialties, languages, experience_years, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            title = VALUES(title),
            bio = VALUES(bio),
            updated_at = NOW()
    ");

    foreach ($speakers as $speaker) {
        // Tìm user_id phù hợp (có thể null)
        $userId = null;
        foreach ($speakerUsers as $user) {
            if (strpos($speaker['email'], strtolower($user['firstName'])) !== false) {
                $userId = $user['id'];
                break;
            }
        }

        $stmt->execute([
            $userId,
            $speaker['name'],
            $speaker['slug'],
            $speaker['title'],
            $speaker['company'],
            $speaker['bio'],
            $speaker['short_bio'],
            $speaker['email'],
            $speaker['specialties'],
            $speaker['languages'],
            $speaker['experience_years'],
            $speaker['status']
        ]);
        $speakersCount++;
    }

    $result['messages'][] = "Đã thêm {$speakersCount} speakers.";

    // 5. Lấy IDs để tạo conferences
    $categoryIds = $pdo->query("SELECT id, slug FROM categories ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
    $venueIds = $pdo->query("SELECT id FROM venues ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $organizerIds = $pdo->query("SELECT id FROM users WHERE role IN ('organizer', 'admin')")->fetchAll(PDO::FETCH_COLUMN);

    // 6. Thêm conferences
    $result['messages'][] = '5. Đang thêm conferences...';
    
    $conferences = [
        [
            'title' => 'Vietnam Tech Summit 2025',
            'slug' => 'vietnam-tech-summit-2025',
            'short_description' => 'Sự kiện công nghệ hàng đầu Việt Nam 2025',
            'description' => 'Sự kiện công nghệ hàng đầu Việt Nam quy tụ các công ty khởi nghiệp tiên phong, ra mắt công nghệ đột phá, và kết nối các chuyên gia trong ngành. Hội nghị sẽ có các phiên thảo luận về AI, Blockchain, IoT và các xu hướng công nghệ mới nhất.',
            'start_date' => '2025-09-15 08:00:00',
            'end_date' => '2025-09-17 18:00:00',
            'category_slug' => 'cong-nghe',
            'venue_id' => 1,
            'location' => 'TP. Hồ Chí Minh',
            'address' => '28C Nguyễn Đình Chiểu, Quận 3',
            'type' => 'hybrid',
            'format' => 'conference',
            'price' => 1999000,
            'currency' => 'VND',
            'early_bird_price' => 1599000,
            'early_bird_until' => '2025-08-15 23:59:59',
            'capacity' => 3000,
            'status' => 'published',
            'featured' => 1,
            'trending' => 1,
            'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop',
            'level' => 'intermediate',
            'tags' => '["AI", "Blockchain", "IoT", "Startup", "Innovation"]',
            'organizer_name' => 'VnTech Media',
            'organizer_email' => 'events@vntech.com.vn',
            'organizer_phone' => '+84 28 1234 5678',
            'organizer_company' => 'VnTech Media Group'
        ],
        [
            'title' => 'Hội nghị Y tế Quốc tế 2025',
            'slug' => 'hoi-nghi-y-te-quoc-te-2025',
            'short_description' => 'Hội nghị y tế quốc tế với các chuyên gia hàng đầu',
            'description' => 'Hội nghị y tế quốc tế mang đến những chia sẻ và cập nhật mới nhất trong nghiên cứu, điều trị và công nghệ y tế toàn cầu. Sự kiện quy tụ các bác sĩ, nhà nghiên cứu và chuyên gia y tế từ khắp nơi trên thế giới.',
            'start_date' => '2025-06-20 08:00:00',
            'end_date' => '2025-06-22 18:00:00',
            'category_slug' => 'y-te',
            'venue_id' => 0, // Hà Nội
            'location' => 'Hà Nội',
            'address' => '8 Lê Thánh Tông, Hoàn Kiếm',
            'type' => 'in_person',
            'format' => 'conference',
            'price' => 2500000,
            'currency' => 'VND',
            'capacity' => 1500,
            'status' => 'published',
            'featured' => 1,
            'image' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=800&h=400&fit=crop',
            'level' => 'advanced',
            'tags' => '["Healthcare", "Medical Research", "Digital Health", "Telemedicine"]',
            'organizer_name' => 'Hiệp hội Y khoa Việt Nam',
            'organizer_email' => 'contact@vmassoc.org.vn',
            'organizer_phone' => '+84 24 3762 5555',
            'organizer_company' => 'Vietnam Medical Association'
        ],
        [
            'title' => 'Hội thảo Marketing Số 2025',
            'slug' => 'hoi-thao-marketing-so-2025',
            'short_description' => 'Khám phá xu hướng marketing số mới nhất',
            'description' => 'Khám phá các xu hướng, chiến lược và công cụ marketing số mới nhất để thúc đẩy doanh nghiệp của bạn trong kỷ nguyên số. Hội thảo bao gồm các case study thực tế và workshop hands-on.',
            'start_date' => '2025-07-10 08:30:00',
            'end_date' => '2025-07-11 17:30:00',
            'category_slug' => 'marketing',
            'venue_id' => 2, // Đà Nẵng
            'location' => 'Đà Nẵng',
            'address' => 'Ariyana Beach Resort & Suites',
            'type' => 'hybrid',
            'format' => 'workshop',
            'price' => 1500000,
            'currency' => 'VND',
            'early_bird_price' => 1200000,
            'early_bird_until' => '2025-06-10 23:59:59',
            'capacity' => 800,
            'status' => 'published',
            'featured' => 0,
            'trending' => 1,
            'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=800&h=400&fit=crop',
            'level' => 'beginner',
            'tags' => '["Digital Marketing", "SEO", "Social Media", "Content Marketing"]',
            'organizer_name' => 'Digital Marketing Association',
            'organizer_email' => 'info@dma.vn',
            'organizer_phone' => '+84 28 3915 3782',
            'organizer_company' => 'DMA Vietnam'
        ],
        [
            'title' => 'Diễn đàn Giáo dục Việt Nam 2025',
            'slug' => 'dien-dan-giao-duc-viet-nam-2025',
            'short_description' => 'Diễn đàn về đổi mới giáo dục và công nghệ',
            'description' => 'Diễn đàn thường niên tập trung về đổi mới giáo dục, phương pháp giảng dạy hiện đại và ứng dụng công nghệ trong giáo dục tại Việt Nam. Sự kiện quy tụ các nhà giáo dục, quản lý và chuyên gia công nghệ giáo dục.',
            'start_date' => '2025-08-25 08:00:00',
            'end_date' => '2025-08-27 17:00:00',
            'category_slug' => 'giao-duc',
            'venue_id' => 0, // Hà Nội
            'location' => 'Hà Nội',
            'address' => '8 Lê Thánh Tông, Hoàn Kiếm',
            'type' => 'in_person',
            'format' => 'conference',
            'price' => 1200000,
            'currency' => 'VND',
            'capacity' => 1000,
            'status' => 'published',
            'featured' => 0,
            'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=400&fit=crop',
            'level' => 'all_levels',
            'tags' => '["Education", "EdTech", "E-learning", "Innovation"]',
            'organizer_name' => 'Bộ Giáo dục và Đào tạo',
            'organizer_email' => 'forum@moet.gov.vn',
            'organizer_phone' => '+84 24 3869 4585',
            'organizer_company' => 'Ministry of Education and Training'
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO conferences (
            title, slug, short_description, description, start_date, end_date, timezone,
            category_id, venue_id, location, address, type, format, price, currency,
            early_bird_price, early_bird_until, capacity, status, visibility, featured, trending,
            image, level, language, tags, organizer_name, organizer_email, organizer_phone, 
            organizer_company, created_by, published_at, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 'Asia/Ho_Chi_Minh',
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, 'public', ?, ?,
            ?, ?, 'vi', ?, ?, ?, ?, 
            ?, ?, NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE 
            title = VALUES(title),
            description = VALUES(description),
            updated_at = NOW()
    ");

    foreach ($conferences as $conf) {
        $categoryId = $categoryIds[$conf['category_slug']] ?? 1;
        $venueId = ($conf['venue_id'] < count($venueIds)) ? $venueIds[$conf['venue_id']] : null;
        $organizerId = $organizerIds[0] ?? 1;

        $stmt->execute([
            $conf['title'],
            $conf['slug'],
            $conf['short_description'],
            $conf['description'],
            $conf['start_date'],
            $conf['end_date'],
            $categoryId,
            $venueId,
            $conf['location'],
            $conf['address'],
            $conf['type'],
            $conf['format'],
            $conf['price'],
            $conf['currency'],
            $conf['early_bird_price'] ?? null,
            $conf['early_bird_until'] ?? null,
            $conf['capacity'],
            $conf['status'],
            $conf['featured'],
            $conf['trending'] ?? 0,
            $conf['image'],
            $conf['level'],
            $conf['tags'],
            $conf['organizer_name'],
            $conf['organizer_email'],
            $conf['organizer_phone'],
            $conf['organizer_company'],
            $organizerId
        ]);
        $conferencesCount++;
    }

    $result['messages'][] = "Đã thêm {$conferencesCount} conferences.";

    // 7. Thêm conference_speakers (liên kết speakers với conferences)
    $result['messages'][] = '6. Đang thêm conference speakers...';
    
    $conferenceIds = $pdo->query("SELECT id FROM conferences ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $speakerIds = $pdo->query("SELECT id FROM speakers ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("
        INSERT INTO conference_speakers (conference_id, speaker_id, role, status, created_at)
        VALUES (?, ?, ?, 'confirmed', NOW())
        ON DUPLICATE KEY UPDATE role = VALUES(role)
    ");

    foreach ($conferenceIds as $confId) {
        // Thêm 2-4 speakers cho mỗi conference
        $numSpeakers = rand(2, 4);
        $selectedSpeakers = array_rand($speakerIds, $numSpeakers);
        if (!is_array($selectedSpeakers)) $selectedSpeakers = [$selectedSpeakers];

        foreach ($selectedSpeakers as $index => $speakerIndex) {
            $role = ($index === 0) ? 'keynote' : 'speaker';
            $stmt->execute([
                $confId,
                $speakerIds[$speakerIndex],
                $role
            ]);
            $conferenceSpeakersCount++;
        }
    }

    $result['messages'][] = "Đã thêm {$conferenceSpeakersCount} conference speakers.";

    // 8. Thêm registrations mẫu
    $result['messages'][] = '7. Đang thêm registrations...';
    
    $userIds = $pdo->query("SELECT id FROM users WHERE role = 'user'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($userIds) && !empty($conferenceIds)) {
        $stmt = $pdo->prepare("
            INSERT INTO registrations (user_id, conference_id, registration_code, ticket_type, 
                                     price_paid, currency, payment_status, status, created_at)
            VALUES (?, ?, ?, 'regular', ?, 'VND', 'paid', 'confirmed', NOW())
        ");

        foreach ($conferenceIds as $confId) {
            // Đăng ký một số users cho mỗi conference
            $numRegistrations = rand(1, min(3, count($userIds)));
            $selectedUsers = array_rand($userIds, $numRegistrations);
            if (!is_array($selectedUsers)) $selectedUsers = [$selectedUsers];

            foreach ($selectedUsers as $userIndex) {
                $registrationCode = 'REG' . $confId . str_pad($userIds[$userIndex], 3, '0', STR_PAD_LEFT);
                $stmt->execute([
                    $userIds[$userIndex],
                    $confId,
                    $registrationCode,
                    1999000 // Sample price
                ]);
                $registrationsCount++;
            }
        }
    }

    $result['messages'][] = "Đã thêm {$registrationsCount} registrations.";

    // 9. Thêm schedule sessions mẫu
    $result['messages'][] = '8. Đang thêm schedule sessions...';
    
    $stmt = $pdo->prepare("
        INSERT INTO schedule_sessions (conference_id, title, description, session_date, 
                                     start_time, end_time, session_type, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'presentation', NOW())
    ");

    $sampleSessions = [
        ['title' => 'Khai mạc và Keynote', 'description' => 'Phát biểu khai mạc và bài phát biểu chính', 'start' => '09:00', 'end' => '10:30'],
        ['title' => 'Panel Discussion', 'description' => 'Thảo luận nhóm về chủ đề chính', 'start' => '11:00', 'end' => '12:30'],
        ['title' => 'Workshop', 'description' => 'Phiên thực hành và workshop', 'start' => '14:00', 'end' => '15:30'],
        ['title' => 'Networking', 'description' => 'Thời gian giao lưu và kết nối', 'start' => '16:00', 'end' => '17:00']
    ];

    foreach ($conferenceIds as $confId) {
        // Lấy ngày bắt đầu của conference
        $confDate = $pdo->query("SELECT DATE(start_date) as conf_date FROM conferences WHERE id = $confId")->fetchColumn();
        
        for ($i = 0; $i < 2; $i++) { // 2 sessions per conference
            $session = $sampleSessions[$i];
            $stmt->execute([
                $confId,
                $session['title'],
                $session['description'],
                $confDate,
                $session['start'],
                $session['end']
            ]);
            $scheduleSessionsCount++;
        }
    }

    $result['messages'][] = "Đã thêm {$scheduleSessionsCount} schedule sessions.";

    // Update result data
    $result['data']['categories'] = $categoriesCount;
    $result['data']['venues'] = $venuesCount;
    $result['data']['users'] = $usersCount;
    $result['data']['speakers'] = $speakersCount;
    $result['data']['conferences'] = $conferencesCount;
    $result['data']['conference_speakers'] = $conferenceSpeakersCount;
    $result['data']['registrations'] = $registrationsCount;
    $result['data']['schedule_sessions'] = $scheduleSessionsCount;
    
    $result['messages'][] = 'Hoàn tất! Dữ liệu mẫu đã được nhập thành công vào schema mới!';
    $result['success'] = true;

} catch (Exception $e) {
    $result['messages'][] = 'Lỗi: ' . $e->getMessage();
    $result['success'] = false;
}

// Return JSON response
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>