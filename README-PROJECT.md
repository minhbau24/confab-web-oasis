# Confab Web Oasis - Hệ thống quản lý hội nghị

## Kiến trúc dự án

Confab Web Oasis được phát triển theo kiến trúc SPA (Single Page Application) với việc tách biệt rõ ràng giữa frontend và backend:

### Frontend
- **HTML/CSS/JS**: Xử lý toàn bộ phần giao diện người dùng (UI)
- **JavaScript Client**: Gọi API từ backend để lấy và cập nhật dữ liệu

### Backend
- **PHP API**: Cung cấp các API endpoint để xử lý dữ liệu
- **MySQL Database**: Lưu trữ dữ liệu người dùng, hội nghị, đăng ký, v.v.

## Cấu trúc thư mục

```
confab-web-oasis/
├── api/                # API endpoints
│   ├── login.php       # API đăng nhập
│   ├── register.php    # API đăng ký
│   ├── conferences.php # API quản lý hội nghị
│   └── ...             # Các API khác
├── classes/            # PHP classes
│   ├── Database.php    # Lớp xử lý kết nối database
│   ├── User.php        # Lớp xử lý người dùng
│   └── Conference.php  # Lớp xử lý hội nghị
├── includes/           # PHP includes
│   ├── config.php      # Cấu hình chung
│   ├── auth.php        # Xử lý xác thực
│   └── ...             # Các include khác
├── js/                 # JavaScript files
│   ├── api-helper.js   # Hỗ trợ gọi API
│   ├── auth.js         # Xử lý xác thực client-side
│   └── ...             # Các file JS khác
├── css/                # CSS files
├── index.html          # Trang chủ
├── login.html          # Trang đăng nhập
├── register.html       # Trang đăng ký
└── ...                 # Các file HTML khác
```

## Kiến trúc SPA (Single Page Application)

Dự án sử dụng hoàn toàn kiến trúc SPA hiện đại với sự tách biệt rõ ràng giữa frontend và backend:

### Frontend (Client-side)
- **HTML/CSS**: Cấu trúc và giao diện tĩnh
- **JavaScript**: Xử lý tương tác người dùng, render động và gọi API
- **Tự động chuyển hướng**: Các URL `.php` tự động chuyển hướng sang `.html`

### Backend (Server-side)
- **API Endpoints**: Tất cả logic xử lý đều nằm trong các API endpoint
- **Authentication**: Xác thực thông qua API token và session
- **Database Access**: Chỉ thực hiện qua API, không có truy cập trực tiếp từ frontend

## Luồng xử lý

1. **Client Request**: User truy cập URL `.html`
2. **Frontend Render**: JavaScript hiển thị giao diện ban đầu
3. **API Call**: JavaScript gọi API để lấy hoặc cập nhật dữ liệu
4. **Backend Processing**: API endpoint xử lý yêu cầu, tương tác với database
5. **Response**: API trả về dữ liệu dạng JSON
6. **Client Update**: JavaScript cập nhật UI với dữ liệu nhận được

## Quản lý phiên đăng nhập

- **Login**: Client gọi API đăng nhập, nhận token và lưu trong localStorage/sessionStorage
- **Validation**: Mỗi API call gửi kèm token để xác thực
- **Logout**: Client gọi API logout, xóa token và session server-side

## Bảo mật

- **API Token**: Xác thực mỗi request API
- **path-sanitizer.js**: Tự động phát hiện và sửa các URL không hợp lệ
- **api-helper.js**: Đảm bảo các API call đều sử dụng đường dẫn tuyệt đối an toàn
- **.htaccess**: Chuyển hướng các request .php sang .html và chặn các URL có chứa đường dẫn Windows
- **Strict Separation**: Không có PHP render trực tiếp, mọi tương tác dữ liệu qua API
- **Input Validation**: Kiểm tra dữ liệu ở cả client-side và server-side

## Hướng dẫn sử dụng

### Cài đặt

1. Clone repo về thư mục web server (ví dụ: `htdocs` trong XAMPP)
2. Import database từ file `sql/schema.sql`
3. Cấu hình thông tin kết nối database trong `includes/config.php`
4. Truy cập ứng dụng tại: `http://localhost/confab-web-oasis/`

### Phát triển

Khi phát triển tính năng mới:

1. **Frontend**: 
   - Chỉ cần chỉnh sửa các file HTML, CSS, JavaScript trong thư mục gốc
   - Thêm code để gọi API endpoints thích hợp
   - Xử lý và render dữ liệu trả về từ API
   
2. **Backend**: 
   - Thêm hoặc sửa các file PHP trong thư mục `/api/`
   - Mỗi API endpoint sẽ trả về dữ liệu dạng JSON
   - Không tạo mới PHP file nào ở thư mục gốc

3. **Database**: 
   - Cập nhật các class trong thư mục `/classes/`
   - Mọi tương tác database đều phải thông qua API

## Liên hệ

Để biết thêm thông tin, vui lòng liên hệ:
