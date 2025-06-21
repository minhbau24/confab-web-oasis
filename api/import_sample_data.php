<?php
/**
 * API nhập dữ liệu mẫu từ data.js vào database
 */
require_once '../includes/config.php';
require_once '../classes/Database.php';

// Set content type for API responses
header('Content-Type: application/json');

$result = [
    'success' => false,
    'messages' => [],
    'data' => [
        'users' => 0,
        'conferences' => 0,
        'speakers' => 0
    ]
];

try {
    // Kết nối database
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Khởi tạo các biến đếm
    $usersCount = 0;
    $conferencesCount = 0;
    $speakersCount = 0;
    $schedulesCount = 0;
    $objectivesCount = 0;
    $audienceCount = 0;
    $faqCount = 0;

    echo '<div class="alert alert-info">Bắt đầu nhập dữ liệu mẫu...</div>';

    // Dữ liệu mẫu cho người dùng
    $users = [
        [
            'firstName' => 'Admin',
            'lastName' => 'System',
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'phone' => '0123456789'
        ],
        [
            'firstName' => 'Nguyễn',
            'lastName' => 'Văn Nam',
            'email' => 'nam@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user',
            'phone' => '0987654321'
        ],
        [
            'firstName' => 'Trần',
            'lastName' => 'Minh Tuấn',
            'email' => 'tuan@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'phone' => '0909123456'
        ]
    ];

    // Thêm người dùng
    $stmt = $pdo->prepare("
        INSERT INTO users (firstName, lastName, email, password, role, phone, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE firstName = VALUES(firstName), lastName = VALUES(lastName), 
                            role = VALUES(role), phone = VALUES(phone)
    ");

    foreach ($users as $user) {
        $stmt->execute([
            $user['firstName'],
            $user['lastName'],
            $user['email'],
            $user['password'],
            $user['role'],
            $user['phone']
        ]);
        $usersCount++;
    }

    echo '<div class="alert alert-success">Đã thêm ' . $usersCount . ' người dùng vào hệ thống</div>';

    // Lấy ID của người tổ chức để dùng làm organizer_id
    $organizerId = $pdo->query("SELECT id FROM users WHERE role = 'organizer' LIMIT 1")->fetchColumn();

    // Dữ liệu mẫu cho hội nghị
    $conferences = [
        [
            'title' => 'Vietnam Tech Summit 2025',
            'description' => 'Sự kiện công nghệ hàng đầu Việt Nam quy tụ các công ty khởi nghiệp tiên phong, ra mắt công nghệ đột phá, và kết nối các chuyên gia trong ngành.',
            'date' => '2025-09-15',
            'endDate' => '2025-09-17',
            'location' => 'TP. Hồ Chí Minh',
            'category' => 'Công nghệ',
            'price' => 1999000,
            'capacity' => 3000,
            'attendees' => 2600,
            'status' => 'active',
            'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop',
            'organizer_name' => 'VnTech Media',
            'organizer_email' => 'events@vntech.com.vn',
            'organizer_phone' => '+84 28 1234 5678'
        ],
        [
            'title' => 'Hội nghị Y tế Quốc tế 2025',
            'description' => 'Hội nghị y tế quốc tế mang đến những chia sẻ và cập nhật mới nhất trong nghiên cứu, điều trị và công nghệ y tế toàn cầu.',
            'date' => '2025-06-20',
            'endDate' => '2025-06-22',
            'location' => 'Hà Nội',
            'category' => 'Y tế',
            'price' => 2500000,
            'capacity' => 1500,
            'attendees' => 1200,
            'status' => 'active',
            'image' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=800&h=400&fit=crop',
            'organizer_name' => 'Hiệp hội Y khoa Việt Nam',
            'organizer_email' => 'contact@vmassoc.org.vn',
            'organizer_phone' => '+84 24 3762 5555'
        ],
        [
            'title' => 'Hội thảo Marketing Số 2025',
            'description' => 'Khám phá các xu hướng, chiến lược và công cụ marketing số mới nhất để thúc đẩy doanh nghiệp của bạn trong kỷ nguyên số.',
            'date' => '2025-07-10',
            'endDate' => '2025-07-11',
            'location' => 'Đà Nẵng',
            'category' => 'Marketing',
            'price' => 1500000,
            'capacity' => 800,
            'attendees' => 650,
            'status' => 'active',
            'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=800&h=400&fit=crop',
            'organizer_name' => 'Digital Marketing Association',
            'organizer_email' => 'info@dma.vn',
            'organizer_phone' => '+84 28 3915 3782'
        ],
        [
            'title' => 'Diễn đàn Giáo dục Việt Nam 2025',
            'description' => 'Diễn đàn thường niên tập trung về đổi mới giáo dục, phương pháp giảng dạy hiện đại và ứng dụng công nghệ trong giáo dục tại Việt Nam.',
            'date' => '2025-08-25',
            'endDate' => '2025-08-27',
            'location' => 'Hà Nội',
            'category' => 'Giáo dục',
            'price' => 1200000,
            'capacity' => 1000,
            'attendees' => 850,
            'status' => 'active',
            'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=400&fit=crop',
            'organizer_name' => 'Bộ Giáo dục và Đào tạo',
            'organizer_email' => 'forum@moet.gov.vn',
            'organizer_phone' => '+84 24 3869 4585'
        ]
    ];

    // Thêm hội nghị
    $stmt = $pdo->prepare("
        INSERT INTO conferences (
            title, description, date, endDate, location, category, price, capacity, 
            attendees, status, image, organizer_id, organizer_name, organizer_email, 
            organizer_phone, created_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    foreach ($conferences as $conf) {
        $stmt->execute([
            $conf['title'],
            $conf['description'],
            $conf['date'],
            $conf['endDate'],
            $conf['location'],
            $conf['category'],
            $conf['price'],
            $conf['capacity'],
            $conf['attendees'],
            $conf['status'],
            $conf['image'],
            $organizerId,
            $conf['organizer_name'],
            $conf['organizer_email'],
            $conf['organizer_phone']
        ]);
        $conferencesCount++;
    }

    echo '<div class="alert alert-success">Đã thêm ' . $conferencesCount . ' hội nghị vào hệ thống</div>';

    // Lấy danh sách ID hội nghị để thêm thông tin chi tiết
    $conferenceIds = $pdo->query("SELECT id FROM conferences ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

    // Thêm diễn giả
    $speakers = [
        ['name' => 'Nguyễn Thị Minh', 'title' => 'CEO, InnovateTech Vietnam', 'bio' => 'Chuyên gia hàng đầu về AI và học máy'],
        ['name' => 'Trần Đức Khải', 'title' => 'CTO, VietStartup', 'bio' => 'Tiên phong trong công nghệ blockchain'],
        ['name' => 'Phạm Thị Hương', 'title' => 'Founder, GreenTech Solutions', 'bio' => 'Chuyên gia về phát triển bền vững và năng lượng sạch'],
        ['name' => 'Lê Văn Bách', 'title' => 'AI Research Director, FPT Software', 'bio' => 'Tiến sĩ AI với hơn 15 năm kinh nghiệm nghiên cứu và ứng dụng thực tế'],
        ['name' => 'Đinh Thu Trang', 'title' => 'Cloud Architect, AWS Vietnam', 'bio' => 'Chuyên gia giải pháp đám mây cho doanh nghiệp lớn']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO conference_speakers (conference_id, name, title, bio)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($conferenceIds as $confId) {
        // Thêm một số diễn giả ngẫu nhiên cho mỗi hội nghị
        $speakerCount = rand(2, 5);
        for ($i = 0; $i < $speakerCount; $i++) {
            $speaker = $speakers[array_rand($speakers)];
            $stmt->execute([
                $confId,
                $speaker['name'],
                $speaker['title'],
                $speaker['bio']
            ]);
            $speakersCount++;
        }
    }

    echo '<div class="alert alert-success">Đã thêm ' . $speakersCount . ' diễn giả vào hệ thống</div>';    // Thêm lịch trình theo ngày (lịch trình cho ngày khác nhau)
    $schedulesByDay = [
        // Ngày 1
        1 => [
            ['time' => ['08:30', '09:30'], 'title' => 'Đăng ký & Kết nối', 'speaker' => '', 'description' => 'Đón tiếp và cung cấp thẻ hội nghị, tài liệu, thời gian kết nối với các đồng nghiệp'],
            ['time' => ['09:30', '10:00'], 'title' => 'Khai mạc Hội nghị', 'speaker' => 'Ban Tổ chức', 'description' => 'Phát biểu khai mạc, giới thiệu chương trình và các diễn giả chính'],
            ['time' => ['10:00', '11:30'], 'title' => 'Keynote: Tương lai của AI trong doanh nghiệp Việt Nam', 'speaker' => 'Nguyễn Thị Minh', 'description' => 'Phân tích xu hướng AI toàn cầu và cơ hội áp dụng tại Việt Nam, các trường hợp thành công điển hình'],
            ['time' => ['11:30', '13:00'], 'title' => 'Ăn trưa & Kết nối', 'speaker' => '', 'description' => 'Bữa trưa và thời gian giao lưu, kết nối với các đồng nghiệp'],
            ['time' => ['13:00', '14:30'], 'title' => 'Panel: Chuyển đổi số trong doanh nghiệp Việt', 'speaker' => 'Nhóm chuyên gia', 'description' => 'Thảo luận về thực trạng, thách thức và giải pháp chuyển đổi số cho doanh nghiệp Việt Nam'],
            ['time' => ['14:30', '15:00'], 'title' => 'Giải lao', 'speaker' => '', 'description' => 'Thời gian nghỉ giải lao, giao lưu với đồng nghiệp'],
            ['time' => ['15:00', '16:30'], 'title' => 'Workshop: Ứng dụng AI trong marketing', 'speaker' => 'Phạm Thị Hương', 'description' => 'Hướng dẫn thực hành ứng dụng các công cụ AI trong chiến lược marketing hiện đại'],
            ['time' => ['16:30', '17:00'], 'title' => 'Tổng kết ngày 1', 'speaker' => 'Ban Tổ chức', 'description' => 'Tóm tắt những điểm chính trong ngày và thông báo chương trình ngày tiếp theo']
        ],
        // Ngày 2
        2 => [
            ['time' => ['09:00', '09:30'], 'title' => 'Recap ngày 1 & Giới thiệu ngày 2', 'speaker' => 'Ban Tổ chức', 'description' => 'Tóm tắt nội dung ngày 1 và giới thiệu chương trình ngày 2'],
            ['time' => ['09:30', '11:00'], 'title' => 'Workshop: Cloud Solutions', 'speaker' => 'Đinh Thu Trang', 'description' => 'Các giải pháp đám mây tiên tiến cho doanh nghiệp vừa và nhỏ'],
            ['time' => ['11:00', '12:00'], 'title' => 'Keynote: Bảo mật thông tin trong kỷ nguyên số', 'speaker' => 'Lê Văn Bách', 'description' => 'Giải pháp bảo mật thông tin toàn diện cho doanh nghiệp'],
            ['time' => ['12:00', '13:30'], 'title' => 'Ăn trưa & Networking', 'speaker' => '', 'description' => 'Bữa trưa và cơ hội kết nối với các chuyên gia trong ngành'],
            ['time' => ['13:30', '15:00'], 'title' => 'Demo: Giải pháp công nghệ mới', 'speaker' => 'Nhiều diễn giả', 'description' => 'Trình diễn các giải pháp công nghệ tiên tiến từ các đối tác'],
            ['time' => ['15:00', '15:30'], 'title' => 'Giải lao', 'speaker' => '', 'description' => 'Thời gian nghỉ giữa giờ và giao lưu'],
            ['time' => ['15:30', '17:00'], 'title' => 'Roundtable: Trao đổi kinh nghiệm thực tế', 'speaker' => 'Nhóm chuyên gia', 'description' => 'Chia sẻ kinh nghiệm thực tế trong việc triển khai dự án công nghệ']
        ],
        // Ngày 3
        3 => [
            ['time' => ['09:00', '09:30'], 'title' => 'Recap ngày 2 & Giới thiệu ngày 3', 'speaker' => 'Ban Tổ chức', 'description' => 'Tổng hợp nội dung ngày 2 và giới thiệu chương trình ngày cuối'],
            ['time' => ['09:30', '11:00'], 'title' => 'Workshop: Data Analytics', 'speaker' => 'Lê Văn Bách', 'description' => 'Khai thác giá trị từ dữ liệu doanh nghiệp với các công cụ phân tích hiện đại'],
            ['time' => ['11:00', '12:30'], 'title' => 'Panel: Tương lai của công việc', 'speaker' => 'Nguyễn Thị Minh, Trần Đức Khải', 'description' => 'Thảo luận về xu hướng thị trường lao động và kỹ năng cần thiết trong tương lai'],
            ['time' => ['12:30', '14:00'], 'title' => 'Ăn trưa', 'speaker' => '', 'description' => 'Bữa trưa ngày cuối cùng của hội nghị'],
            ['time' => ['14:00', '15:30'], 'title' => 'Startup Showcase', 'speaker' => 'Các công ty khởi nghiệp', 'description' => 'Giới thiệu các startup tiềm năng trong lĩnh vực công nghệ'],
            ['time' => ['15:30', '16:30'], 'title' => 'Tổng kết & Bế mạc', 'speaker' => 'Ban Tổ chức', 'description' => 'Tổng kết hội nghị và phát biểu bế mạc']
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO conference_schedule (conference_id, eventDate, startTime, endTime, title, speaker, description)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($conferenceIds as $confId) {
        // Lấy ngày bắt đầu và kết thúc của hội nghị
        $confDates = $pdo->query("SELECT date, endDate FROM conferences WHERE id = $confId")->fetch(PDO::FETCH_ASSOC);
        $startDate = new DateTime($confDates['date']);
        $endDate = !empty($confDates['endDate']) ? new DateTime($confDates['endDate']) : $startDate;        // Tính số ngày của hội nghị
        $interval = $startDate->diff($endDate);
        $durationDays = $interval->days + 1; // +1 vì bao gồm cả ngày bắt đầu
        
        // Giới hạn tối đa 3 ngày cho lịch trình mẫu
        $durationDays = min($durationDays, 3);
        
        // Tạo lịch trình cho từng ngày
        $currentDate = clone $startDate;
        for ($dayIndex = 1; $dayIndex <= $durationDays; $dayIndex++) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Lấy lịch trình phù hợp cho ngày
            $daySchedules = isset($schedulesByDay[$dayIndex]) ? $schedulesByDay[$dayIndex] : $schedulesByDay[1];
            
            // Thêm lịch trình cho ngày này
            foreach ($daySchedules as $schedule) {
                $stmt->execute([
                    $confId,
                    $dateStr,
                    $schedule['time'][0],
                    $schedule['time'][1],
                    $schedule['title'],
                    $schedule['speaker'],
                    $schedule['description']
                ]);
                $schedulesCount++;
            }

            // Chuyển sang ngày tiếp theo
            $currentDate->modify('+1 day');
        }
    }

    echo '<div class="alert alert-success">Đã thêm ' . $schedulesCount . ' mục lịch trình vào hệ thống</div>';

    // Thêm mục tiêu hội nghị
    $objectives = [
        'Tạo sân chơi để kết nối các chuyên gia, lãnh đạo và cơ hội kinh doanh trong ngành',
        'Chia sẻ kiến thức, xu hướng và giải pháp mới nhất trong lĩnh vực',
        'Thảo luận về các thách thức và cơ hội trong bối cảnh toàn cầu hóa và chuyển đổi số',
        'Xây dựng mạng lưới hợp tác giữa các doanh nghiệp, nhà nghiên cứu và chính phủ',
        'Truyền cảm hứng và thúc đẩy đổi mới sáng tạo trong ngành'
    ];

    $stmt = $pdo->prepare("
        INSERT INTO conference_objectives (conference_id, description, order_num)
        VALUES (?, ?, ?)
    ");

    foreach ($conferenceIds as $confId) {
        // Thêm ngẫu nhiên 3-5 mục tiêu
        $objectiveCount = rand(3, 5);
        $objectiveKeys = array_rand($objectives, $objectiveCount);
        if (!is_array($objectiveKeys))
            $objectiveKeys = [$objectiveKeys];

        foreach ($objectiveKeys as $index => $key) {
            $stmt->execute([
                $confId,
                $objectives[$key],
                $index + 1
            ]);
            $objectivesCount++;
        }
    }

    echo '<div class="alert alert-success">Đã thêm ' . $objectivesCount . ' mục tiêu hội nghị vào hệ thống</div>';

    // Thêm đối tượng tham dự
    $audienceTypes = [
        'Quản lý cấp cao và giám đốc điều hành trong ngành',
        'Chuyên gia và nhà nghiên cứu',
        'Doanh nhân và người sáng lập startup',
        'Nhà đầu tư và quỹ đầu tư',
        'Sinh viên và những người mới bắt đầu sự nghiệp trong lĩnh vực'
    ];

    $stmt = $pdo->prepare("
        INSERT INTO conference_audience (conference_id, description, order_num)
        VALUES (?, ?, ?)
    ");

    foreach ($conferenceIds as $confId) {
        // Thêm ngẫu nhiên 2-4 đối tượng
        $audienceCount = rand(2, 4);
        $audienceKeys = array_rand($audienceTypes, $audienceCount);
        if (!is_array($audienceKeys))
            $audienceKeys = [$audienceKeys];

        foreach ($audienceKeys as $index => $key) {
            $stmt->execute([
                $confId,
                $audienceTypes[$key],
                $index + 1
            ]);
            $audienceCount++;
        }
    }

    echo '<div class="alert alert-success">Đã thêm ' . $audienceCount . ' đối tượng tham dự vào hệ thống</div>';

    // Thêm FAQ
    $faqs = [
        ['question' => 'Làm thế nào để đăng ký tham gia hội nghị?', 'answer' => 'Bạn có thể đăng ký trực tuyến thông qua trang web của chúng tôi bằng cách nhấp vào nút "Đăng ký tham dự" trên trang chi tiết hội nghị. Sau khi điền đầy đủ thông tin, bạn sẽ nhận được email xác nhận đăng ký.'],
        ['question' => 'Chi phí tham dự hội nghị bao gồm những gì?', 'answer' => 'Chi phí tham dự hội nghị bao gồm quyền tham gia tất cả các phiên, tài liệu hội nghị, bữa trưa và giải lao. Chi phí không bao gồm đi lại và chỗ ở.'],
        ['question' => 'Có chính sách hoàn tiền không?', 'answer' => 'Chúng tôi có chính sách hoàn tiền nếu bạn hủy đăng ký ít nhất 14 ngày trước khi hội nghị bắt đầu. Hoàn tiền sẽ trừ phí xử lý 10%. Không có hoàn tiền cho việc hủy muộn hơn.'],
        ['question' => 'Tôi có thể đổi người tham dự không?', 'answer' => 'Có, bạn có thể thay đổi thông tin người tham dự mà không mất thêm phí. Vui lòng liên hệ với ban tổ chức ít nhất 3 ngày trước khi sự kiện diễn ra.'],
        ['question' => 'Có chỗ đỗ xe tại địa điểm hội nghị không?', 'answer' => 'Có, địa điểm hội nghị có bãi đỗ xe miễn phí cho người tham dự. Tuy nhiên, số lượng có hạn nên chúng tôi khuyến khích đi chung xe hoặc sử dụng phương tiện công cộng.']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO conference_faq (conference_id, question, answer, order_num)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($conferenceIds as $confId) {
        // Thêm ngẫu nhiên 3-5 FAQ
        $faqCount = rand(3, 5);
        $faqKeys = array_rand($faqs, $faqCount);
        if (!is_array($faqKeys))
            $faqKeys = [$faqKeys];

        foreach ($faqKeys as $index => $key) {
            $stmt->execute([
                $confId,
                $faqs[$key]['question'],
                $faqs[$key]['answer'],
                $index + 1
            ]);
            $faqCount++;
        }
    }    $result['messages'][] = 'Đã thêm ' . $faqCount . ' FAQ vào hệ thống';
    
    // Update result with counts
    $result['data']['users'] = $usersCount;
    $result['data']['conferences'] = $conferencesCount;
    $result['data']['speakers'] = $speakersCount;
    
    $result['messages'][] = 'Hoàn tất! Dữ liệu mẫu đã được nhập thành công!';
    $result['success'] = true;

} catch (Exception $e) {
    $result['messages'][] = 'Lỗi: ' . $e->getMessage();
    $result['success'] = false;
}

// Return JSON response
echo json_encode($result);
?>