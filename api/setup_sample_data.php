<?php
/**
 * Script test và verify dữ liệu database
 * Đảm bảo các bảng có dữ liệu mẫu cần thiết
 */

// Define this file as an API endpoint to prevent HTML redirects  
define('API_ENDPOINT', true);

// Tắt hiển thị lỗi để tránh trả về HTML thay vì JSON
ini_set('display_errors', 0);
error_reporting(0);

// Set JSON header ngay từ đầu
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

try {    require_once '../includes/config.php';
    require_once '../classes/Database.php';
    
    $db = Database::getInstance();
    
    // Kiểm tra và tạo dữ liệu mẫu nếu cần
    $results = [];
    
    // 1. Kiểm tra bảng categories
    $categoriesCount = $db->fetch("SELECT COUNT(*) as count FROM categories");
    $results['categories'] = [
        'count' => $categoriesCount['count'],
        'has_data' => $categoriesCount['count'] > 0
    ];
    
    if ($categoriesCount['count'] == 0) {
        // Thêm dữ liệu mẫu categories
        $db->execute("INSERT INTO categories (name, slug, description, color, status) VALUES 
            ('Công nghệ', 'cong-nghe', 'Hội nghị về công nghệ thông tin', '#007bff', 'active'),
            ('Kinh doanh', 'kinh-doanh', 'Hội nghị về kinh doanh và quản lý', '#28a745', 'active'),
            ('Giáo dục', 'giao-duc', 'Hội nghị về giáo dục và đào tạo', '#ffc107', 'active')");
        $results['categories']['data_inserted'] = true;
    }
    
    // 2. Kiểm tra bảng venues
    $venuesCount = $db->fetch("SELECT COUNT(*) as count FROM venues");
    $results['venues'] = [
        'count' => $venuesCount['count'],
        'has_data' => $venuesCount['count'] > 0
    ];
    
    if ($venuesCount['count'] == 0) {
        // Thêm dữ liệu mẫu venues
        $db->execute("INSERT INTO venues (name, slug, description, address, city, country, capacity, status) VALUES 
            ('Trung tâm Hội nghị Quốc gia', 'trung-tam-hoi-nghi-quoc-gia', 'Trung tâm hội nghị hiện đại tại Hà Nội', 'Số 1 Thăng Long, Ba Đình, Hà Nội', 'Hà Nội', 'Vietnam', 1000, 'active'),
            ('Khách sạn Rex', 'khach-san-rex', 'Khách sạn 5 sao tại TP.HCM', '141 Nguyễn Huệ, Quận 1, TP.HCM', 'Hồ Chí Minh', 'Vietnam', 500, 'active'),
            ('Trung tâm Hội nghị FPT', 'trung-tam-hoi-nghi-fpt', 'Trung tâm hội nghị công nghệ', 'Khu Công nghệ cao FPT, Hòa Lạc', 'Hà Nội', 'Vietnam', 300, 'active')");
        $results['venues']['data_inserted'] = true;
    }
    
    // 3. Kiểm tra bảng speakers
    $speakersCount = $db->fetch("SELECT COUNT(*) as count FROM speakers");
    $results['speakers'] = [
        'count' => $speakersCount['count'],
        'has_data' => $speakersCount['count'] > 0
    ];
    
    if ($speakersCount['count'] == 0) {
        // Thêm dữ liệu mẫu speakers
        $db->execute("INSERT INTO speakers (name, slug, title, company, bio, short_bio, image, email, status) VALUES 
            ('Nguyễn Văn A', 'nguyen-van-a', 'CTO', 'FPT Software', 'Chuyên gia công nghệ với 15 năm kinh nghiệm', 'CTO tại FPT Software', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400', 'nguyenvana@fpt.com', 'active'),
            ('Trần Thị B', 'tran-thi-b', 'CEO', 'VNG Corporation', 'Doanh nhân thành công trong lĩnh vực công nghệ', 'CEO của VNG Corporation', 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=400', 'tranthib@vng.com', 'active'),
            ('Lê Minh C', 'le-minh-c', 'Giáo sư', 'Đại học Bách Khoa', 'Giáo sư về AI và Machine Learning', 'Chuyên gia AI hàng đầu Việt Nam', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400', 'leminhc@hust.edu.vn', 'active')");
        $results['speakers']['data_inserted'] = true;
    }
    
    // 4. Kiểm tra bảng conferences
    $conferencesCount = $db->fetch("SELECT COUNT(*) as count FROM conferences");
    $results['conferences'] = [
        'count' => $conferencesCount['count'],
        'has_data' => $conferencesCount['count'] > 0
    ];
    
    if ($conferencesCount['count'] == 0) {
        // Lấy ID của categories và venues vừa tạo
        $category1 = $db->fetch("SELECT id FROM categories WHERE slug = 'cong-nghe'");
        $category2 = $db->fetch("SELECT id FROM categories WHERE slug = 'kinh-doanh'");
        $venue1 = $db->fetch("SELECT id FROM venues WHERE slug = 'trung-tam-hoi-nghi-quoc-gia'");
        $venue2 = $db->fetch("SELECT id FROM venues WHERE slug = 'khach-san-rex'");
        
        // Thêm dữ liệu mẫu conferences
        $db->execute("INSERT INTO conferences (title, slug, short_description, description, start_date, end_date, category_id, venue_id, location, type, price, capacity, status) VALUES 
            ('Hội nghị Công nghệ số 2024', 'hoi-nghi-cong-nghe-so-2024', 'Hội nghị lớn nhất về công nghệ số tại Việt Nam', 'Hội nghị tập trung vào các xu hướng công nghệ mới như AI, IoT, Blockchain...', '2024-07-15 09:00:00', '2024-07-17 17:00:00', ?, ?, 'Hà Nội', 'in_person', 500000, 500, 'published'),
            ('Vietnam Startup Summit 2024', 'vietnam-startup-summit-2024', 'Hội nghị khởi nghiệp lớn nhất Việt Nam', 'Kết nối các startup, nhà đầu tư và chuyên gia trong hệ sinh thái khởi nghiệp', '2024-08-20 08:30:00', '2024-08-22 18:00:00', ?, ?, 'Hồ Chí Minh', 'hybrid', 750000, 800, 'published')", 
            [$category1['id'], $venue1['id'], $category2['id'], $venue2['id']]);
        $results['conferences']['data_inserted'] = true;
    }
    
    // 5. Kiểm tra bảng conference_speakers
    $conferenceSpeakersCount = $db->fetch("SELECT COUNT(*) as count FROM conference_speakers");
    $results['conference_speakers'] = [
        'count' => $conferenceSpeakersCount['count'],
        'has_data' => $conferenceSpeakersCount['count'] > 0
    ];
    
    if ($conferenceSpeakersCount['count'] == 0) {
        // Lấy ID của conferences và speakers
        $conf1 = $db->fetch("SELECT id FROM conferences WHERE slug = 'hoi-nghi-cong-nghe-so-2024'");
        $conf2 = $db->fetch("SELECT id FROM conferences WHERE slug = 'vietnam-startup-summit-2024'");
        $speaker1 = $db->fetch("SELECT id FROM speakers WHERE slug = 'nguyen-van-a'");
        $speaker2 = $db->fetch("SELECT id FROM speakers WHERE slug = 'tran-thi-b'");
        $speaker3 = $db->fetch("SELECT id FROM speakers WHERE slug = 'le-minh-c'");
        
        if ($conf1 && $conf2 && $speaker1 && $speaker2 && $speaker3) {
            $db->execute("INSERT INTO conference_speakers (conference_id, speaker_id, role, talk_title, talk_description, status) VALUES 
                (?, ?, 'keynote', 'Tương lai của AI trong doanh nghiệp', 'Phân tích xu hướng AI và ứng dụng thực tế', 'confirmed'),
                (?, ?, 'speaker', 'Xây dựng startup công nghệ thành công', 'Chia sẻ kinh nghiệm từ CEO VNG', 'confirmed'),
                (?, ?, 'speaker', 'Machine Learning trong giáo dục', 'Ứng dụng ML để cải thiện chất lượng giáo dục', 'confirmed')", 
                [$conf1['id'], $speaker1['id'], $conf2['id'], $speaker2['id'], $conf1['id'], $speaker3['id']]);
            $results['conference_speakers']['data_inserted'] = true;
        }
    }
    
    // 6. Kiểm tra bảng schedule_sessions
    $scheduleCount = $db->fetch("SELECT COUNT(*) as count FROM schedule_sessions");
    $results['schedule_sessions'] = [
        'count' => $scheduleCount['count'],
        'has_data' => $scheduleCount['count'] > 0
    ];
    
    if ($scheduleCount['count'] == 0) {
        $conf1 = $db->fetch("SELECT id FROM conferences WHERE slug = 'hoi-nghi-cong-nghe-so-2024'");
        $speaker1 = $db->fetch("SELECT id FROM speakers WHERE slug = 'nguyen-van-a'");
        $speaker3 = $db->fetch("SELECT id FROM speakers WHERE slug = 'le-minh-c'");
        
        if ($conf1 && $speaker1 && $speaker3) {
            $db->execute("INSERT INTO schedule_sessions (conference_id, title, description, session_date, start_time, end_time, type, room, speaker_id, status) VALUES 
                (?, 'Khai mạc và Keynote', 'Phiên khai mạc với bài phát biểu chính', '2024-07-15', '09:00:00', '10:30:00', 'keynote', 'Hội trường A', ?, 'scheduled'),
                (?, 'AI trong thực tế', 'Thảo luận về ứng dụng AI', '2024-07-15', '11:00:00', '12:00:00', 'presentation', 'Phòng 101', ?, 'scheduled'),
                (?, 'Nghỉ trưa', 'Nghỉ trưa và networking', '2024-07-15', '12:00:00', '13:30:00', 'lunch', 'Khu ẩm thực', NULL, 'scheduled')", 
                [$conf1['id'], $speaker1['id'], $conf1['id'], $speaker3['id'], $conf1['id']]);
            $results['schedule_sessions']['data_inserted'] = true;
        }
    }
    
    // Trả về kết quả
    echo json_encode([
        'status' => true,
        'message' => 'Kiểm tra và tạo dữ liệu mẫu hoàn tất',
        'results' => $results
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Lỗi: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
