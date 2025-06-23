# Database Setup Documentation - Confab Web Oasis

## Tổng quan

Confab Web Oasis phiên bản 3.0 đã được cải tiến với hệ thống database setup hoàn toàn mới, sử dụng schema hoàn chỉnh với các tính năng nâng cao.

## Các tính năng mới trong Schema

### 1. Quản lý người dùng nâng cao
- **password_history**: Lưu trữ lịch sử mật khẩu để tránh tái sử dụng
- **user_sessions**: Quản lý phiên đăng nhập an toàn
- **user_activity_logs**: Theo dõi hoạt động người dùng
- **security_logs**: Ghi lại các sự kiện bảo mật

### 2. Hệ thống hội nghị đầy đủ
- **venues**: Quản lý địa điểm tổ chức
- **speakers**: Quản lý diễn giả với profile đầy đủ
- **conference_schedule**: Lịch trình chi tiết
- **registrations**: Đăng ký tham dự với payment tracking
- **certificates**: Chứng chỉ tham dự tự động

### 3. Đa ngôn ngữ (i18n)
- **languages**: Danh sách ngôn ngữ hỗ trợ
- **translations**: Bản dịch theo key/value
- Hỗ trợ fallback language

### 4. Media Management
- **media_folders**: Cấu trúc thư mục
- **media_files**: Quản lý file upload với metadata
- Tích hợp với CDN và cloud storage

### 5. Payment System
- **payment_methods**: Các phương thức thanh toán
- **invoices**: Hóa đơn chi tiết
- **transactions**: Giao dịch với audit trail
- **billing_info**: Thông tin thanh toán khách hàng

### 6. Audit và Logging
- **audit_logs**: Theo dõi mọi thay đổi dữ liệu
- **error_logs**: Ghi lại lỗi hệ thống
- **scheduled_tasks**: Quản lý tác vụ định kỳ

## Cách sử dụng

### 1. Setup Database qua Web Interface

1. Truy cập `setup.php`
2. Chọn "Database Setup"
3. Nhấn "Thiết lập Schema Hoàn Chỉnh"
4. Chờ quá trình hoàn tất

### 2. Setup Database qua API

```bash
# Gọi API setup
curl -X POST http://your-domain/api/setup_database.php

# Kiểm tra schema
curl http://your-domain/api/verify_schema.php
```

### 3. Kiểm tra tính toàn vẹn

Sử dụng API `verify_schema.php` để kiểm tra:
- Số lượng bảng đã tạo
- Stored procedures và functions
- Triggers và views
- Foreign key constraints
- Dữ liệu mẫu

## Stored Procedures

### 1. Certificate Management
```sql
CALL GenerateCertificateCode(@user_id, @conference_id);
```

### 2. Registration Statistics
```sql
CALL GetRegistrationStats(@conference_id);
```

### 3. Translation Management
```sql
CALL GetTranslation(@key, @lang_code);
```

## Triggers

### 1. Audit Logging
- Tự động ghi lại mọi thay đổi vào `audit_logs`
- Tracking INSERT/UPDATE/DELETE operations

### 2. User Activity
- Cập nhật `last_login` khi user đăng nhập
- Tự động lock account sau số lần thất bại

### 3. Conference Management
- Tự động cập nhật `attendees_count` khi có registration
- Validate capacity limits

## Views

### 1. Conference Statistics
```sql
SELECT * FROM vw_conference_stats;
```

### 2. User Activity Summary
```sql
SELECT * FROM vw_user_activity_summary;
```

### 3. Payment Reports
```sql
SELECT * FROM vw_payment_reports;
```

## Migration và Upgrade

### Từ phiên bản cũ lên v3.0

1. **Backup dữ liệu hiện tại**
```bash
mysqldump -u username -p database_name > backup.sql
```

2. **Chạy migration script**
```php
// Sử dụng setup_database.php sẽ tự động detect và migrate
```

3. **Verify sau migration**
```bash
curl http://your-domain/api/verify_schema.php
```

## Troubleshooting

### Lỗi thường gặp

1. **Table already exists**
   - Script tự động handle, không cần can thiệp

2. **Foreign key constraint fails**
   - Kiểm tra data integrity
   - Chạy `SHOW ENGINE INNODB STATUS`

3. **Schema incomplete**
   - Chạy lại `setup_database.php`
   - Kiểm tra log errors

### Tools hỗ trợ

1. **check_database_tables.php** - Kiểm tra bảng
2. **verify_schema.php** - Kiểm tra toàn vẹn
3. **debug_import.php** - Debug import process

## Performance Optimization

### Indexes được tạo tự động
- Email indexes cho fast lookup
- Composite indexes cho queries phức tạp
- Foreign key indexes

### Query Optimization
- Sử dụng prepared statements
- Connection pooling
- Query caching

## Security Features

### 1. Password Security
- BCrypt hashing với salt
- Password history tracking
- Complexity requirements

### 2. Session Management
- Secure session storage
- Session timeout
- Multiple device support

### 3. Audit Trail
- Mọi thay đổi được log
- User activity tracking
- Security event logging

## API Documentation

### Setup Database
```
POST /api/setup_database.php
Response: {
  "success": true,
  "messages": [...],
  "data": {...},
  "steps": [...]
}
```

### Verify Schema
```
GET /api/verify_schema.php
Response: {
  "success": true,
  "statistics": {...},
  "data": {
    "tables": {...},
    "procedures": [...],
    "triggers": [...],
    "views": [...]
  }
}
```

## Maintenance

### Regular Tasks
1. **Cleanup old logs** (monthly)
2. **Backup database** (daily)
3. **Update statistics** (weekly)
4. **Security audit** (quarterly)

### Monitoring
- Database size growth
- Query performance
- Error rates
- User activity patterns

---

## Liên hệ và Hỗ trợ

Nếu gặp vấn đề trong quá trình setup, vui lòng:

1. Kiểm tra `TROUBLESHOOTING.md`
2. Xem logs trong `error_logs` table
3. Sử dụng debug tools trong `/api/`
4. Liên hệ support team

**Version:** 3.0 Complete Edition  
**Last Updated:** December 2024  
**Schema Version:** 3.0.0
