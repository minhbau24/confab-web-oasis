<?php
/**
 * File thiết lập cơ sở dữ liệu
 * Chạy file này để tạo các bảng cần thiết cho hệ thống
 */

// Header và Content-Type để hiển thị kết quả
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập cơ sở dữ liệu - Trung tâm Hội nghị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Thiết lập cơ sở dữ liệu</h1>';

require_once dirname(__DIR__) . '/includes/config.php';

try {
    echo '<div class="alert alert-info">Đang kết nối đến cơ sở dữ liệu...</div>';

    $conn = connectDB();

    echo '<div class="alert alert-success">Kết nối thành công!</div>';

    // Bật chế độ lỗi
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
    ");

    // Tạo bảng conference_schedule (lịch hội nghị)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS conference_schedule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conference_id INT NOT NULL,
            event_date DATE NOT NULL,
            time VARCHAR(50) NOT NULL,
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
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);

    $conn->exec("
        INSERT INTO users (firstName, lastName, email, password, role)
        VALUES 
            ('Admin', 'System', 'admin@example.com', '$hashedPassword', 'admin'),
            ('Nguyễn', 'Tổ Chức', 'organizer@example.com', '$hashedPassword', 'organizer'),
            ('Nguyễn', 'Văn Nam', 'nam@example.com', '$hashedPassword', 'user')
    ");
    echo '<div class="alert alert-success mt-4"><h4>Thành công!</h4><p>Cấu trúc cơ sở dữ liệu đã được thiết lập thành công.</p></div>';

    // Hiển thị nút nhập dữ liệu mẫu
    echo '<div class="mt-4">
        <a href="import_sample_data.php" class="btn btn-primary">Nhập dữ liệu mẫu</a>
        <a href="../index.php" class="btn btn-secondary ms-2">Quay lại trang chủ</a>
    </div>';

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Lỗi khi thiết lập cơ sở dữ liệu: ' . $e->getMessage() . '</div>';
    echo '<div class="alert alert-warning">
        <h4>Kiểm tra cấu hình kết nối</h4>
        <p>Hãy đảm bảo:</p>
        <ol>
            <li>Máy chủ XAMPP/MySQL đang chạy</li>
            <li>Thông tin kết nối trong file <code>includes/config.php</code> là chính xác</li>
            <li>Người dùng MySQL có đủ quyền để tạo cơ sở dữ liệu và bảng</li>
        </ol>
    </div>';
}

echo '</div>
</body>
</html>';
?>