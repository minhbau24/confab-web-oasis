# CONFAB WEB OASIS - FINAL SETUP GUIDE

## 🎯 Complete Database Setup and Schema Implementation

This document provides the final setup guide for the fully refactored and expanded Confab Web Oasis conference management system.

## 📋 What's New in This Version

### ✅ Completed Features

1. **Comprehensive Schema (`schema_complete.sql`)**
   - 26+ fully normalized tables
   - Advanced security features (password policies, audit logs, 2FA support)
   - Internationalization (i18n) with translations system
   - Media management with file uploads and categorization
   - Payment processing with multiple gateways
   - Certificate generation system
   - Scheduled tasks and background jobs
   - Comprehensive logging and auditing
   - System settings and configuration management

2. **Robust Setup Process**
   - Web-based setup wizard (`setup.php`)
   - API-driven database initialization (`setup_database.php`)
   - Automatic schema loading from `schema_complete.sql`
   - Smart sample data import with conflict detection
   - Complete schema verification and testing

3. **Sample Data & Testing**
   - Full sample dataset with 12 categories of data
   - Multiple user roles (admin, organizer, speaker, user)
   - Sample conferences, speakers, venues, and payment methods
   - System settings and translations
   - Comprehensive test suite for validation

4. **Documentation & Troubleshooting**
   - Step-by-step setup instructions
   - Troubleshooting guide for common issues
   - API documentation for all endpoints
   - Database repair and maintenance tools

## 🚀 Quick Setup (New Installation)

### Step 1: Environment Setup

1. **Web Server Requirements:**
   - PHP 7.4+ (recommended: PHP 8.1+)
   - MySQL 5.7+ or MariaDB 10.2+
   - Apache/Nginx with mod_rewrite enabled

2. **PHP Extensions Required:**
   - PDO and PDO_MySQL
   - mbstring
   - json
   - openssl (for password hashing)
   - curl (for API integrations)

### Step 2: Database Preparation

1. Create a new database:
   ```sql
   CREATE DATABASE confab_web_oasis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'confab_user'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON confab_web_oasis.* TO 'confab_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. Update `includes/config.php` with your database credentials.

### Step 3: Automated Setup (Recommended)

1. **Open the web-based setup wizard:**
   ```
   http://your-domain.com/setup.php
   ```

2. **The wizard will:**
   - Test database connection
   - Load the complete schema from `sql/schema_complete.sql`
   - Create all tables, procedures, triggers, and views
   - Import comprehensive sample data
   - Verify the installation

3. **Default accounts created:**
   - **Admin:** admin@confab.local / password123
   - **Organizer:** organizer@confab.local / password123
   - **Speaker:** speaker@confab.local / password123
   - **User:** user@confab.local / password123

### Step 4: Verification

Run the setup test to verify everything is working:
```
http://your-domain.com/api/test_setup.php
```

## 🔧 Manual Setup (Advanced)

If you prefer manual setup or need to troubleshoot:

### 1. Database Schema

Execute the complete schema:
```bash
mysql -u confab_user -p confab_web_oasis < sql/schema_complete.sql
```

### 2. Sample Data Import

```bash
# Via API
curl -X POST http://your-domain.com/api/import_sample_data.php

# Or via browser
http://your-domain.com/api/import_sample_data.php
```

### 3. Schema Verification

```bash
curl -X GET http://your-domain.com/api/verify_schema.php
```

## 📊 Database Schema Overview

### Core Tables (26 total)

1. **User Management**
   - `users` - User accounts with enhanced security
   - `user_sessions` - Session management
   - `user_activity_logs` - Activity tracking

2. **Internationalization**
   - `languages` - Supported languages
   - `translations` - Translation strings

3. **Conference Management**
   - `categories` - Conference categories
   - `venues` - Event locations
   - `speakers` - Speaker profiles
   - `conferences` - Main conference data
   - `conference_speakers` - Speaker assignments
   - `registrations` - User registrations
   - `certificates` - Certificate generation

4. **Payment System**
   - `payment_methods` - Payment gateways
   - `transactions` - Payment transactions
   - `invoices` - Invoice management

5. **Media Management**
   - `media_folders` - File organization
   - `media_files` - File storage with metadata
   - `media_categories` - File categorization

6. **System Features**
   - `settings` - System configuration
   - `scheduled_tasks` - Background jobs
   - `audit_logs` - System audit trail
   - `error_logs` - Error tracking

### Advanced Features

- **Stored Procedures:** Certificate generation, statistics
- **Triggers:** Automatic logging, data validation
- **Views:** Reporting and analytics
- **Indexes:** Optimized for performance

## 🔒 Security Features

1. **Password Security**
   - Bcrypt hashing with salt
   - Password expiration policies
   - Failed login attempt tracking
   - Account lockout protection

2. **Data Protection**
   - SQL injection prevention (prepared statements)
   - XSS protection
   - CSRF token support
   - Role-based access control

3. **Audit Trail**
   - Complete user activity logging
   - Data change tracking
   - IP address and browser logging
   - Error and security event logging

## 🌍 Internationalization

- **Multi-language Support:** Vietnamese, English, Chinese, Japanese
- **Dynamic Translation:** Database-driven translation system
- **Locale Support:** Date, time, and currency formatting
- **RTL Support:** Ready for right-to-left languages

## 💳 Payment Integration

Supported payment gateways:
- Bank transfer (manual)
- MoMo wallet
- ZaloPay
- Credit/debit cards (Stripe)
- PayPal

## 📁 File Management

- **Smart Upload:** File type validation and size limits
- **Media Organization:** Folder-based file management
- **Image Processing:** Automatic thumbnail generation
- **Access Control:** Public/private file access

## 🔄 Background Tasks

- Email reminders
- Database backups
- Log cleanup
- Statistics updates
- Certificate generation

## 📈 Reporting & Analytics

Built-in views for:
- Conference overview and statistics
- Revenue reporting
- User activity analysis
- Error monitoring
- Registration analytics

## 🛠️ Troubleshooting

### Common Issues

1. **Schema loading fails:**
   ```bash
   php api/debug_schema.php
   ```

2. **Sample data import issues:**
   ```bash
   php api/debug_import.php
   ```

3. **Missing tables or procedures:**
   ```bash
   php api/database_repair.php
   ```

### Diagnostic Tools

- `api/check_database_tables.php` - Table existence check
- `api/check_table_structure.php` - Column validation
- `api/verify_schema.php` - Complete schema verification
- `api/test_setup.php` - Comprehensive test suite

### Log Files

Check these for detailed error information:
- PHP error logs
- MySQL error logs
- Application logs in `audit_logs` table

## 📖 API Endpoints

All setup and management APIs:

- `POST /api/setup_database.php` - Main setup
- `POST /api/import_sample_data.php` - Sample data import
- `GET /api/verify_schema.php` - Schema verification
- `GET /api/test_setup.php` - Setup testing
- `POST /api/database_repair.php` - Emergency repair

## 🎯 Next Steps

After successful setup:

1. **Change default passwords** for all sample accounts
2. **Configure email settings** in system settings
3. **Upload your logo and branding** in media management
4. **Set up payment gateways** with real credentials
5. **Configure scheduled tasks** for your server
6. **Review and customize** system settings
7. **Test the complete workflow** from registration to certificates

## 📞 Support

If you encounter issues:

1. Check the troubleshooting section
2. Run the diagnostic tools
3. Review the error logs
4. Check the API documentation
5. Verify your server meets the requirements

---

**🎉 Congratulations!** Your Confab Web Oasis system is now ready for production use with a complete, secure, and scalable database schema supporting all advanced conference management features.
