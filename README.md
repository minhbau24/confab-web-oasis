# Confab Web Oasis - Conference Management System

## Phiên bản 3.0 - Complete Edition

Hệ thống quản lý hội nghị và sự kiện chuyên nghiệp với đầy đủ tính năng nâng cao.

## ⭐ Tính năng chính

### 🔐 Quản lý người dùng nâng cao
- Authentication với BCrypt và password history
- User sessions và activity tracking
- Multi-role support (Admin, Organizer, Speaker, User)
- Security logs và audit trails

### 🎪 Hệ thống hội nghị đầy đủ
- Quản lý venues (địa điểm) với GPS coordinates
- Speaker management với profile đầy đủ
- Conference scheduling và timeline
- Registration system với payment integration
- Certificate generation tự động

### 🌍 Đa ngôn ngữ (i18n)
- Hỗ trợ multiple languages
- Dynamic translation system
- Fallback language support

### 💰 Payment System
- Multiple payment methods
- Invoice generation
- Transaction tracking
- Billing information management

### 📁 Media Management
- File upload với metadata
- Folder organization
- CDN integration ready

### 📊 Analytics & Reporting
- Comprehensive audit logs
- User activity analytics
- Conference statistics
- Payment reports

## 🚀 Quick Start

### Cách 1: Thiết lập qua Web Interface (Khuyến nghị)

1. **Truy cập setup wizard:**
   ```
   http://localhost/confab-web-oasis/setup.php
   ```

2. **Làm theo hướng dẫn:**
   - Kiểm tra requirements
   - Cấu hình database
   - Thiết lập schema hoàn chỉnh
   - Import dữ liệu mẫu

### Cách 2: Setup thủ công

1. **Cấu hình database:**
   ```php
   // includes/config.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'confab_web_oasis');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

2. **Tạo database:**
   ```sql
   CREATE DATABASE confab_web_oasis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import schema:**
   ```bash
   mysql -u username -p confab_web_oasis < sql/schema_complete.sql
   ```

## 🔧 API Endpoints

### Database Setup
```bash
# Thiết lập hoàn chỉnh database
POST /api/setup_database.php

# Kiểm tra tính toàn vẹn schema
GET /api/verify_schema.php

# Test toàn diện hệ thống
GET /api/test_setup.php
```

### Core APIs
```bash
# User management
GET /api/users.php
POST /api/users.php

# Conference management
GET /api/conferences.php
POST /api/conferences.php

# Registration
POST /api/register_conference.php
```

## 📋 Requirements

- **PHP:** 7.4+ (8.0+ khuyến nghị)
- **MySQL:** 5.7+ hoặc MariaDB 10.3+
- **Web Server:** Apache hoặc Nginx
- **Extensions:** PDO, JSON, cURL, GD

## 🗄️ Database Schema

### Core Tables (26 bảng)
- `users` - Quản lý người dùng với security features
- `conferences` - Hội nghị với full metadata
- `venues` - Địa điểm tổ chức
- `speakers` - Diễn giả
- `registrations` - Đăng ký tham dự
- `payments` - Hệ thống thanh toán
- `translations` - Đa ngôn ngữ
- `audit_logs` - Audit trails
- `media_files` - Media management

### Advanced Features
- **12 Stored Procedures** - Business logic
- **8 Triggers** - Auto-logging và validation
- **5 Views** - Reporting và analytics
- **25+ Indexes** - Performance optimization

## 🛠️ Development

### Cấu trúc project
```
confab-web-oasis/
├── api/                 # REST API endpoints
├── classes/             # PHP classes
├── includes/            # Common includes
├── js/                  # Frontend JavaScript
├── sql/                 # Database schemas
├── public/              # Static assets
├── setup.php            # Setup wizard
└── *.php               # Frontend pages
```

### Debugging Tools
- `api/check_database_tables.php` - Kiểm tra bảng
- `api/debug_import.php` - Debug import process
- `api/test_setup.php` - Comprehensive testing

## 📚 Documentation

- [DATABASE-SETUP.md](DATABASE-SETUP.md) - Chi tiết setup database
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Xử lý lỗi thường gặp
- [API-DOCUMENTATION.md](API-DOCUMENTATION.md) - API reference

## 🔐 Default Accounts

Sau khi setup, hệ thống tạo sẵn các tài khoản:

| Role      | Email                  | Password    |
|-----------|------------------------|-------------|
| Admin     | admin@confab.local     | password123 |
| Organizer | organizer@confab.local | password123 |
| Speaker   | speaker@confab.local   | password123 |
| User      | user@confab.local      | password123 |

## 🔄 Upgrade Path

### Từ phiên bản cũ
1. Backup database hiện tại
2. Chạy `setup.php` - tự động detect và migrate
3. Verify với `api/verify_schema.php`

## 🐛 Troubleshooting

### Lỗi thường gặp
1. **Database connection:** Kiểm tra config.php
2. **Missing tables:** Chạy lại setup_database.php
3. **Permission denied:** Chmod 755 cho thư mục

### Debug tools
```bash
# Kiểm tra schema
curl http://localhost/confab-web-oasis/api/verify_schema.php

# Test toàn diện
curl http://localhost/confab-web-oasis/api/test_setup.php
```

## 📞 Support

- **Issues:** Tạo issue trên GitHub
- **Documentation:** Xem thư mục docs/
- **Email:** support@confab-oasis.com

## 📄 License

MIT License - Xem file [LICENSE](LICENSE) để biết chi tiết.

---

**Version:** 3.0 Complete Edition  
**Last Updated:** December 2024  
**Minimum PHP:** 7.4+  
**Database:** MySQL 5.7+ / MariaDB 10.3+

# Step 3: Install the necessary dependencies.
npm i

# Step 4: Start the development server with auto-reloading and an instant preview.
npm run dev
```

**Edit a file directly in GitHub**

- Navigate to the desired file(s).
- Click the "Edit" button (pencil icon) at the top right of the file view.
- Make your changes and commit the changes.

**Use GitHub Codespaces**

- Navigate to the main page of your repository.
- Click on the "Code" button (green button) near the top right.
- Select the "Codespaces" tab.
- Click on "New codespace" to launch a new Codespace environment.
- Edit files directly within the Codespace and commit and push your changes once you're done.

## What technologies are used for this project?

This project is built with:

- Vite
- TypeScript
- React
- shadcn-ui
- Tailwind CSS

## How can I deploy this project?

Simply open [Lovable](https://lovable.dev/projects/a6b3d4d2-464e-4d6c-b7ca-0cded0604a1d) and click on Share -> Publish.

## Can I connect a custom domain to my Lovable project?

Yes, you can!

To connect a domain, navigate to Project > Settings > Domains and click Connect Domain.

Read more here: [Setting up a custom domain](https://docs.lovable.dev/tips-tricks/custom-domain#step-by-step-guide)
