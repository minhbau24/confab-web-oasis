# API Documentation - Confab Web Oasis

## Thông tin chung

- Base URL: `http://localhost/confab-web-oasis/api`
- Phương thức: `GET`, `POST`
- Format trả về: `JSON`
- Headers:
  ```
  Content-Type: application/json
  ```

## API Endpoints

### 1. Thiết lập và khởi tạo

#### 1.1. Thiết lập database

- URL: `/setup_database.php`
- Method: `GET`
- Description: Tạo cấu trúc database từ schema hoàn chỉnh
- Response:
  ```json
  {
    "success": true,
    "messages": ["Database setup completed", "20 tables created", "5 views created"]
  }
  ```

#### 1.2. Nhập dữ liệu mẫu

- URL: `/import_sample_data.php`
- Method: `GET`
- Description: Nhập dữ liệu mẫu vào hệ thống
- Response:
  ```json
  {
    "success": true,
    "messages": ["Sample data imported successfully", "6 categories added", "3 venues added"],
    "data": {
      "categories": 6,
      "venues": 3,
      "users": 4,
      "conferences": 4
    }
  }
  ```

### 2. Quản lý người dùng

#### 2.1. Đăng nhập

- URL: `/users.php?action=login`
- Method: `POST`
- Body:
  ```json
  {
    "email": "admin@confab.local",
    "password": "admin123"
  }
  ```
- Response:
  ```json
  {
    "success": true,
    "message": "Login successful",
    "user": {
      "id": 1,
      "firstName": "Admin",
      "lastName": "System",
      "role": "admin",
      "email": "admin@confab.local"
    },
    "token": "JWT_TOKEN_HERE"
  }
  ```

#### 2.2. Đăng ký người dùng

- URL: `/users.php?action=register`
- Method: `POST`
- Body:
  ```json
  {
    "firstName": "Nguyen",
    "lastName": "Van A",
    "email": "nguyenvana@example.com",
    "password": "password123",
    "phone": "0987654321"
  }
  ```
- Response:
  ```json
  {
    "success": true,
    "message": "Registration successful",
    "userId": 5
  }
  ```

#### 2.3. Cập nhật thông tin người dùng

- URL: `/update_profile.php`
- Method: `POST`
- Headers: `Authorization: Bearer JWT_TOKEN`
- Body:
  ```json
  {
    "firstName": "Nguyen",
    "lastName": "Van B",
    "phone": "0987654321",
    "company": "Company XYZ",
    "position": "Developer"
  }
  ```
- Response:
  ```json
  {
    "success": true,
    "message": "Profile updated successfully"
  }
  ```

#### 2.4. Đổi mật khẩu

- URL: `/change_password.php`
- Method: `POST`
- Headers: `Authorization: Bearer JWT_TOKEN`
- Body:
  ```json
  {
    "currentPassword": "password123",
    "newPassword": "newpassword123",
    "confirmPassword": "newpassword123"
  }
  ```
- Response:
  ```json
  {
    "success": true,
    "message": "Password changed successfully"
  }
  ```

### 3. Quản lý hội nghị

#### 3.1. Danh sách hội nghị

- URL: `/conferences.php`
- Method: `GET`
- Parameters:
  - `category_id` (optional): Lọc theo danh mục
  - `search` (optional): Tìm kiếm theo từ khóa
  - `page` (optional): Số trang, mặc định 1
  - `limit` (optional): Số lượng mỗi trang, mặc định 10
- Response:
  ```json
  {
    "success": true,
    "data": {
      "conferences": [
        {
          "id": 1,
          "title": "Vietnam Tech Summit 2025",
          "description": "Sự kiện công nghệ hàng đầu",
          "start_date": "2025-09-15 08:00:00",
          "end_date": "2025-09-17 18:00:00",
          "location": "TP. Hồ Chí Minh",
          "price": 1999000,
          "image": "https://example.com/image.jpg",
          "category_name": "Công nghệ"
        },
        // More conferences
      ],
      "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "total_items": 25,
        "limit": 10
      }
    }
  }
  ```

#### 3.2. Chi tiết hội nghị

- URL: `/conferences.php?id=1` or `/conferences.php?slug=vietnam-tech-summit-2025`
- Method: `GET`
- Response:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "title": "Vietnam Tech Summit 2025",
      "slug": "vietnam-tech-summit-2025",
      "description": "Sự kiện công nghệ hàng đầu...",
      "start_date": "2025-09-15 08:00:00",
      "end_date": "2025-09-17 18:00:00",
      "location": "TP. Hồ Chí Minh",
      "address": "28C Nguyễn Đình Chiểu, Quận 3",
      "price": 1999000,
      "currency": "VND",
      "early_bird_price": 1599000,
      "early_bird_until": "2025-08-15 23:59:59",
      "image": "https://example.com/image.jpg",
      "category": {
        "id": 1,
        "name": "Công nghệ",
        "color": "#007bff"
      },
      "venue": {
        "id": 1,
        "name": "Saigon Convention Center",
        "address": "28C Nguyễn Đình Chiểu, Quận 3"
      },
      "speakers": [
        {
          "id": 1,
          "name": "Nguyễn Thị Minh",
          "title": "CEO, InnovateTech Vietnam",
          "bio": "Chuyên gia hàng đầu về AI và học máy"
        },
        // More speakers
      ],
      "schedule": [
        {
          "id": 1,
          "title": "Khai mạc và Keynote",
          "description": "Phát biểu khai mạc và bài phát biểu chính",
          "session_date": "2025-09-15",
          "start_time": "09:00",
          "end_time": "10:30"
        },
        // More schedule sessions
      ]
    }
  }
  ```

#### 3.3. Tạo hội nghị mới (yêu cầu quyền organizer/admin)

- URL: `/update_conference.php`
- Method: `POST`
- Headers: `Authorization: Bearer JWT_TOKEN`
- Body:
  ```json
  {
    "title": "New Conference",
    "slug": "new-conference",
    "description": "Description...",
    "start_date": "2025-10-15 08:00:00",
    "end_date": "2025-10-17 18:00:00",
    "category_id": 1,
    "venue_id": 2,
    "price": 1500000
    // More fields...
  }
  ```
- Response:
  ```json
  {
    "success": true,
    "message": "Conference created successfully",
    "conferenceId": 5
  }
  ```

#### 3.4. Cập nhật hội nghị (yêu cầu quyền organizer/admin)

- URL: `/update_conference.php`
- Method: `POST`
- Headers: `Authorization: Bearer JWT_TOKEN`
- Body:
  ```json
  {
    "id": 5,
    "title": "Updated Conference Title",
    "description": "Updated description...",
    // More fields...
  }
  ```
- Response:
  ```json
  {
    "success": true,
    "message": "Conference updated successfully"
  }
  ```

#### 3.5. Xóa hội nghị (yêu cầu quyền admin)

- URL: `/conferences_delete.php`
- Method: `POST`
- Headers: `Authorization: Bearer JWT_TOKEN`
- Body:
  ```json
  {
    "id": 5
  }
  ```
- Response:
  ```json
  {
    "success": true,
    "message": "Conference deleted successfully"
  }
  ```

### 4. Danh mục và Venues

#### 4.1. Danh sách danh mục

- URL: `/categories.php`
- Method: `GET`
- Response:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "name": "Công nghệ",
        "slug": "cong-nghe",
        "description": "Các hội nghị về công nghệ thông tin, AI, blockchain",
        "color": "#007bff",
        "icon": "fas fa-laptop-code"
      },
      // More categories...
    ]
  }
  ```

#### 4.2. Danh sách địa điểm

- URL: `/venues.php`
- Method: `GET`
- Response:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "name": "Trung tâm Hội nghị Quốc gia",
        "description": "Trung tâm hội nghị hiện đại với đầy đủ tiện nghi",
        "address": "8 Lê Thánh Tông, Hoàn Kiếm",
        "city": "Hà Nội",
        "capacity": 3000
      },
      // More venues...
    ]
  }
  ```

## Lỗi và Mã trạng thái

### Mã lỗi thường gặp

- `400 Bad Request`: Thiếu tham số hoặc tham số không hợp lệ
- `401 Unauthorized`: Chưa đăng nhập hoặc token không hợp lệ
- `403 Forbidden`: Không có quyền truy cập vào tài nguyên
- `404 Not Found`: Không tìm thấy tài nguyên
- `500 Internal Server Error`: Lỗi server

### Format lỗi

```json
{
  "success": false,
  "error": {
    "code": 400,
    "message": "Thiếu thông tin bắt buộc"
  }
}
```

## Phiên bản API

- Phiên bản hiện tại: 1.0
- Ngày cập nhật: 22/06/2025
