<?php
/**
 * File thiết lập cơ sở dữ liệu - API Logic
 * Chạy file này để tạo các bảng cần thiết cho hệ thống
 */

// Set content type for API responses
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/config.php';

$result = [
    'success' => false,
    'messages' => [],
    'data' => null
];

try {
    $result['messages'][] = 'Đang kết nối đến cơ sở dữ liệu...';
    $conn = connectDB();
    $result['messages'][] = 'Kết nối thành công!';    // Bật chế độ lỗi
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tạo bảng users
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            firstName VARCHAR(50) NOT NULL,
            lastName VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'organizer', 'admin') DEFAULT 'user',
            phone VARCHAR(20),
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Tạo bảng conferences
    $conn->exec("
        CREATE TABLE IF NOT EXISTS conferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            date DATE NOT NULL,
            endDate DATE,
            location VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            capacity INT NOT NULL,
            attendees INT DEFAULT 0,
            status ENUM('draft', 'active', 'cancelled', 'completed') DEFAULT 'draft',
            isManaged BOOLEAN DEFAULT false,
            image VARCHAR(255),
            organizer_name VARCHAR(100),
            organizer_email VARCHAR(100),
            organizer_phone VARCHAR(20),
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Tạo bảng speakers (diễn giả)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS speakers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            title VARCHAR(100),
            bio TEXT,
            image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Tạo bảng liên kết conference_speakers
    $conn->exec("
        CREATE TABLE IF NOT EXISTS conference_speakers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conference_id INT NOT NULL,
            speaker_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conference_id) REFERENCES conferences(id) ON DELETE CASCADE,
            FOREIGN KEY (speaker_id) REFERENCES speakers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");    // Tạo bảng conference_schedule (lịch hội nghị)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS conference_schedule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conference_id INT NOT NULL,
            eventDate DATE NOT NULL,
            startTime TIME NOT NULL,
            endTime TIME NOT NULL,
            title VARCHAR(255) NOT NULL,
            speaker VARCHAR(100),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conference_id) REFERENCES conferences(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Tạo bảng conference_attendees (người tham dự)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS conference_attendees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conference_id INT NOT NULL,
            user_id INT NOT NULL,
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('registered', 'confirmed', 'cancelled', 'attended') DEFAULT 'registered',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conference_id) REFERENCES conferences(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Tạo bảng testimonials (đánh giá của khách hàng)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            name VARCHAR(100) NOT NULL,
            company VARCHAR(100),
            content TEXT NOT NULL,
            rating INT NOT NULL DEFAULT 5,
            is_featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Tạo bảng conference_objectives (mục tiêu hội nghị)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS conference_objectives (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conference_id INT NOT NULL,
            description VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conference_id) REFERENCES conferences(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Tạo bảng conference_faqs (câu hỏi thường gặp)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS conference_faqs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conference_id INT NOT NULL,
            question TEXT NOT NULL,
            answer TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conference_id) REFERENCES conferences(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Chèn dữ liệu người dùng mẫu (mật khẩu: password123)
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);    $conn->exec("
        INSERT INTO users (firstName, lastName, email, password, role)
        VALUES 
            ('Admin', 'System', 'admin@example.com', '$hashedPassword', 'admin'),
            ('Nguyễn', 'Tổ Chức', 'organizer@example.com', '$hashedPassword', 'organizer'),
            ('Nguyễn', 'Văn Nam', 'nam@example.com', '$hashedPassword', 'user')
    ");
    
    $result['messages'][] = 'Thành công! Cấu trúc cơ sở dữ liệu đã được thiết lập thành công.';
    $result['success'] = true;

} catch (PDOException $e) {
    $result['messages'][] = 'Lỗi khi thiết lập cơ sở dữ liệu: ' . $e->getMessage();
    $result['success'] = false;
}

// Return JSON response
echo json_encode($result);
?>