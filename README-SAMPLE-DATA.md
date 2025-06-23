# Hướng Dẫn Import Dữ Liệu Mẫu Confab Web Oasis

## Tổng Quan

Schema database đã được gộp hoàn chỉnh với tất cả sample data vào 1 file duy nhất: `sql/schema_complete.sql`

## Cách Import

### Phương Pháp 1: Sử dụng Setup API (Khuyến Nghị)
```bash
# Truy cập URL sau trong browser:
http://localhost/confab-web-oasis/api/setup_database.php
```

### Phương Pháp 2: Import SQL Trực Tiếp
```bash
# Sử dụng MySQL Command Line:
mysql -u root -p < sql/schema_complete.sql

# Hoặc phpMyAdmin: Import file sql/schema_complete.sql
```

### Phương Pháp 3: Sử dụng XAMPP phpMyAdmin
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Tạo database mới tên `confab_db` (nếu chưa có)
3. Chọn database `confab_db`
4. Chọn tab **Import**
5. Chọn file `sql/schema_complete.sql`
6. Click **Go** để import

## Dữ Liệu Mẫu Bao Gồm

### User Accounts (4 accounts)
| Email | Password | Role | Mô Tả |
|-------|----------|------|-------|
| admin@example.com | password123 | admin | Administrator với full quyền |
| organizer@example.com | password123 | organizer | Event organizer |
| speaker@example.com | password123 | speaker | Professional speaker |
| test@example.com | password123 | user | Regular user |

### Conferences (5 conferences)
1. **Vietnam Tech Summit 2025** (Aug 15-16) - 1000 attendees, AI/Tech focus
2. **Startup Weekend Ho Chi Minh 2025** (Jul 11-13) - 200 attendees, 54h hackathon  
3. **Digital Health Conference 2025** (Sep 20) - 300 attendees, Healthcare AI
4. **AI & Machine Learning Workshop 2025** (Aug 25-26) - 50 attendees, Hands-on training
5. **Blockchain & Web3 Summit 2025** (Oct 5-6) - 800 attendees, DeFi/Web3

### Data Highlights
- **13 Conference-Speaker relationships** với detailed talk information
- **11 Registrations** với payment status, dietary requirements, emergency contacts
- **65+ Schedule Sessions** với detailed timetables cho tất cả conferences
- **21 Session Attendees** tracking participation
- **6 Feedback entries** với ratings và comments
- **3 Certificates** đã được issued
- **2 Waiting list entries**
- **Full support data**: venues, categories, speakers, payment methods, translations

## Test API Endpoints

Sau khi import thành công, test các endpoints:

```bash
# Lấy thông tin conference
GET http://localhost/confab-web-oasis/api/conferences.php?id=1

# Lấy speakers của conference
GET http://localhost/confab-web-oasis/api/conferences.php?id=1&speakers=1

# Lấy schedule của conference  
GET http://localhost/confab-web-oasis/api/conferences.php?id=1&schedule=1

# Debug conference information
GET http://localhost/confab-web-oasis/api/debug_conference.php?id=1
```

## Conference-Detail Page Testing

Test các trang conference-detail với dữ liệu đầy đủ:

```bash
# Vietnam Tech Summit (đầy đủ speakers, registrations, schedule)
http://localhost/confab-web-oasis/conference-detail.html?id=1

# Digital Health Conference (medical focus, detailed sessions)
http://localhost/confab-web-oasis/conference-detail.html?id=3

# AI & ML Workshop (hands-on format, 2-day schedule)
http://localhost/confab-web-oasis/conference-detail.html?id=4

# Blockchain Summit (large scale, diverse sessions)
http://localhost/confab-web-oasis/conference-detail.html?id=5
```

## Verification

Để verify import thành công:

```sql
-- Kiểm tra số lượng conferences
SELECT COUNT(*) as total_conferences FROM conferences;

-- Kiểm tra registrations
SELECT COUNT(*) as total_registrations FROM registrations;

-- Kiểm tra schedule sessions
SELECT COUNT(*) as total_sessions FROM schedule_sessions;

-- Kiểm tra users
SELECT email, role FROM users;
```

Expected results:
- 5 conferences
- 11 registrations  
- 65+ schedule sessions
- 4 users với các roles khác nhau

## Troubleshooting

### Lỗi "Database already exists"
```sql
DROP DATABASE IF EXISTS confab_db;
-- Sau đó import lại
```

### Lỗi "Foreign key constraint"
- Đảm bảo import đúng thứ tự (file schema_complete.sql đã sắp xếp đúng)
- Disable foreign key checks nếu cần:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Import data
SET FOREIGN_KEY_CHECKS = 1;
```

### Lỗi Character Set
- Đảm bảo MySQL sử dụng UTF8MB4
- Set charset khi import:
```bash
mysql -u root -p --default-character-set=utf8mb4 < sql/schema_complete.sql
```

## Development Notes

- Schema bao gồm 30+ bảng với full relationships
- Indexes được tối ưu cho performance
- Sample data realistic và đầy đủ cho development/testing
- Tất cả passwords sử dụng bcrypt hashing
- Support đa ngôn ngữ (vi, en, zh, ja)

## Support

Nếu gặp vấn đề khi import, kiểm tra:
1. MySQL service đang chạy
2. Quyền truy cập database
3. PHP có thể connect đến MySQL
4. Đường dẫn file SQL đúng

---

**Lưu ý**: Tất cả dữ liệu đã được gộp vào 1 file `sql/schema_complete.sql` duy nhất. 
Không cần import thêm file nào khác!
