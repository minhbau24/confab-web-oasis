# Confab Web Oasis - Complete Database Schema Documentation

## Tổng quan

Database schema hoàn chỉnh cho hệ thống quản lý hội nghị Confab Web Oasis được thiết kế để hỗ trợ một hệ thống quản lý sự kiện chuyên nghiệp với đầy đủ tính năng.

## Thông tin cơ bản

- **Database**: confab_db
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Engine**: InnoDB
- **Schema Version**: 3.0 (Complete Edition)

## Cấu trúc bảng

### 1. Core Tables (Bảng cốt lõi)

#### 1.1 users - Người dùng
Lưu trữ thông tin người dùng hệ thống với các vai trò khác nhau.

**Các trường chính:**
- `id`: Primary key
- `firstName`, `lastName`: Tên của người dùng
- `email`: Email (unique)
- `password`: Mật khẩu đã hash
- `role`: Vai trò (user, organizer, speaker, admin)
- `phone`, `avatar`, `bio`: Thông tin cá nhân
- `company`, `position`: Thông tin công việc
- `website`, `linkedin`, `twitter`, `github`: Liên kết xã hội
- `email_verified`: Trạng thái xác thực email
- `status`: Trạng thái tài khoản (active, inactive, suspended, pending)

**Indexes:**
- Primary: `id`
- Unique: `email`
- Index: `email`, `role`, `status`, `last_login`, `email_verified`

#### 1.2 categories - Danh mục hội nghị
Phân loại hội nghị theo chủ đề.

**Các trường chính:**
- `id`: Primary key
- `name`: Tên danh mục
- `slug`: URL friendly name
- `description`: Mô tả
- `color`: Màu sắc hiển thị
- `icon`: Icon CSS class
- `parent_id`: Danh mục cha (hỗ trợ phân cấp)
- `is_featured`: Đánh dấu nổi bật

**Relationships:**
- Self-referencing: `parent_id` → `categories.id`

#### 1.3 venues - Địa điểm tổ chức
Quản lý thông tin địa điểm tổ chức sự kiện.

**Các trường chính:**
- `id`: Primary key
- `name`: Tên địa điểm
- `slug`: URL friendly name
- `address`, `city`, `state`, `country`: Địa chỉ
- `latitude`, `longitude`: Tọa độ GPS
- `capacity`: Sức chứa
- `facilities`: Tiện nghi (JSON)
- `contact_info`: Thông tin liên hệ
- `images`: Hình ảnh (JSON)
- `rating`: Đánh giá trung bình

#### 1.4 speakers - Diễn giả
Quản lý thông tin diễn giả.

**Các trường chính:**
- `id`: Primary key
- `user_id`: Liên kết với bảng users
- `name`: Tên diễn giả
- `slug`: URL friendly name
- `title`, `company`: Chức vụ và công ty
- `bio`, `short_bio`: Tiểu sử
- `specialties`: Chuyên môn (JSON)
- `languages`: Ngôn ngữ (JSON)
- `rating`: Đánh giá
- `total_talks`: Tổng số bài nói

**Relationships:**
- `user_id` → `users.id`

### 2. Conference Tables (Bảng hội nghị)

#### 2.1 conferences - Hội nghị chính
Bảng trung tâm lưu trữ thông tin hội nghị.

**Các trường chính:**
- `id`: Primary key
- `title`: Tiêu đề hội nghị
- `slug`: URL friendly name
- `description`, `short_description`: Mô tả
- `start_date`, `end_date`: Thời gian
- `category_id`: Danh mục
- `venue_id`: Địa điểm
- `type`: Loại (in_person, online, hybrid)
- `format`: Định dạng (conference, workshop, seminar, webinar, meetup)
- `price`, `currency`: Giá và đơn vị tiền tệ
- `capacity`: Sức chứa
- `current_attendees`: Số người tham dự hiện tại
- `status`: Trạng thái (draft, published, sold_out, cancelled, completed, postponed)
- `featured`, `trending`: Đánh dấu nổi bật/xu hướng
- `certificate_available`: Có cấp chứng chỉ
- `meta_data`: Dữ liệu meta (JSON)

**Relationships:**
- `category_id` → `categories.id`
- `venue_id` → `venues.id`
- `created_by` → `users.id`

#### 2.2 conference_speakers - Liên kết hội nghị với diễn giả
Quản lý diễn giả của từng hội nghị.

**Các trường chính:**
- `conference_id` → `conferences.id`
- `speaker_id` → `speakers.id`
- `role`: Vai trò (keynote, speaker, panelist, moderator)
- `talk_title`: Tiêu đề bài nói
- `speaking_fee`: Phí diễn thuyết
- `status`: Trạng thái (invited, confirmed, declined, cancelled)

### 3. Registration Tables (Bảng đăng ký)

#### 3.1 registrations - Đăng ký tham dự
Quản lý đăng ký tham dự hội nghị.

**Các trường chính:**
- `id`: Primary key
- `user_id` → `users.id`
- `conference_id` → `conferences.id`
- `registration_code`: Mã đăng ký (unique)
- `ticket_type`: Loại vé (regular, early_bird, vip, student, group)
- `price_paid`: Số tiền đã trả
- `payment_status`: Trạng thái thanh toán
- `status`: Trạng thái đăng ký (pending, confirmed, cancelled, attended)
- `certificate_requested`: Yêu cầu chứng chỉ
- `qr_code`: Mã QR check-in

#### 3.2 waiting_list - Danh sách chờ
Quản lý danh sách chờ khi hội nghị đầy.

**Các trường chính:**
- `user_id` → `users.id`
- `conference_id` → `conferences.id`
- `position`: Vị trí trong danh sách chờ
- `status`: Trạng thái (waiting, notified, converted, expired)

### 4. Schedule Tables (Bảng lịch trình)

#### 4.1 schedule_sessions - Phiên lịch trình
Chi tiết lịch trình từng phiên của hội nghị.

**Các trường chính:**
- `conference_id` → `conferences.id`
- `title`: Tiêu đề phiên
- `session_date`, `start_time`, `end_time`: Thời gian
- `type`: Loại phiên (presentation, workshop, panel, break, lunch)
- `speaker_id` → `speakers.id`
- `room`: Phòng/địa điểm
- `capacity`: Sức chứa phiên
- `materials`: Tài liệu (JSON)

#### 4.2 session_attendees - Người tham dự phiên
Theo dõi tham dự từng phiên cụ thể.

**Các trường chính:**
- `session_id` → `schedule_sessions.id`
- `user_id` → `users.id`
- `attendance_status`: Trạng thái tham dự
- `check_in_time`: Thời gian check-in
- `rating`: Đánh giá phiên

### 5. Feedback Tables (Bảng phản hồi)

#### 5.1 feedback - Phản hồi và đánh giá
Lưu trữ phản hồi và đánh giá từ người tham dự.

**Các trường chính:**
- `user_id` → `users.id`
- `conference_id` → `conferences.id`
- `overall_rating`: Đánh giá tổng thể (1-5)
- `content_rating`, `speaker_rating`, `venue_rating`: Đánh giá chi tiết
- `feedback_text`: Phản hồi văn bản
- `would_recommend`: Có giới thiệu không
- `is_public`: Hiển thị công khai

### 6. Certificate Tables (Bảng chứng chỉ)

#### 6.1 certificates - Chứng chỉ
Quản lý chứng chỉ hoàn thành.

**Các trường chính:**
- `user_id` → `users.id`
- `conference_id` → `conferences.id`
- `certificate_number`: Số chứng chỉ (unique)
- `certificate_type`: Loại chứng chỉ
- `verification_code`: Mã xác thực (unique)
- `file_path`: Đường dẫn file PDF
- `revoked`: Đã thu hồi không

### 7. System Tables (Bảng hệ thống)

#### 7.1 notifications - Thông báo
Hệ thống thông báo cho người dùng.

**Các trường chính:**
- `user_id` → `users.id`
- `type`: Loại thông báo
- `title`, `message`: Tiêu đề và nội dung
- `read_at`: Thời gian đọc
- `priority`: Độ ưu tiên

#### 7.2 settings - Cài đặt hệ thống
Cấu hình hệ thống.

**Các trường chính:**
- `key`: Khóa cài đặt (unique)
- `value`: Giá trị
- `type`: Kiểu dữ liệu
- `group`: Nhóm cài đặt

#### 7.3 audit_logs - Nhật ký hệ thống
Theo dõi các thao tác trong hệ thống.

**Các trường chính:**
- `user_id` → `users.id`
- `action`: Hành động
- `table_name`: Bảng bị tác động
- `old_values`, `new_values`: Giá trị cũ và mới (JSON)

### 8. Additional Tables (Bảng bổ sung)

#### 8.1 tags - Thẻ gắn nhãn
Hệ thống tag cho hội nghị.

#### 8.2 conference_tags - Liên kết hội nghị với thẻ

#### 8.3 discount_codes - Mã giảm giá
Quản lý mã giảm giá và khuyến mãi.

## Views (Các view)

### conference_stats
Thống kê chi tiết về hội nghị bao gồm:
- Số lượng đăng ký
- Doanh thu
- Tỷ lệ lấp đầy
- Đánh giá trung bình

### active_users_stats
Thống kê về người dùng tích cực:
- Số hội nghị đã tham dự
- Tổng chi tiêu
- Hoạt động gần đây

## Triggers (Tự động hóa)

### Cập nhật số lượng tham dự
- Tự động cập nhật `current_attendees` khi có đăng ký mới
- Cập nhật `total_talks` cho diễn giả

### Tạo mã tự động
- Tự động tạo `registration_code` cho đăng ký
- Tự động tạo `certificate_number` và `verification_code`

### Cập nhật thống kê
- Tự động cập nhật `usage_count` cho tags

## Indexes và Performance

### Primary Indexes
Tất cả bảng đều có primary key với auto-increment.

### Foreign Key Indexes
Tất cả foreign key đều được index để tối ưu JOIN.

### Search Indexes
- Full-text search cho conferences
- Composite indexes cho các truy vấn phức tạp

### Performance Indexes
- Indexes cho các trường thường được filter/sort
- Covering indexes cho các truy vấn phổ biến

## Data Integrity

### Foreign Key Constraints
- Cascade delete cho dữ liệu phụ thuộc
- Set NULL cho dữ liệu tham chiếu

### Check Constraints
- Validation rating (1-5)
- Validation date ranges
- Validation email format

### Unique Constraints
- Email addresses
- Slugs
- Registration codes
- Certificate numbers

## Security Features

### Data Protection
- Password hashing
- Sensitive data encryption
- Audit trail

### Access Control
- Role-based permissions
- Soft delete cho dữ liệu quan trọng
- Session management

## Migration và Maintenance

### Backup Strategy
- Daily automated backups
- Point-in-time recovery
- Schema versioning

### Performance Monitoring
- Query performance tracking
- Index usage monitoring
- Storage optimization

## API Integration

Schema được thiết kế để hỗ trợ RESTful API với:
- JSON data types cho flexibility
- Proper indexing cho API queries
- Pagination support
- Search capabilities

## Extensibility

Schema hỗ trợ mở rộng trong tương lai:
- JSON fields cho metadata
- Pluggable authentication
- Multi-language support
- Custom fields support

---

**Lưu ý**: Schema này được thiết kế cho production environment với focus vào performance, scalability và maintainability. Trước khi deploy, cần review và test kỹ lưỡng trong môi trường staging.
