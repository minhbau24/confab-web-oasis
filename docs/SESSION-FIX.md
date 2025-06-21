# Lỗi JSON Parse và Session_start() - Khắc phục

## Mô tả lỗi:
```
Unexpected token '<', "..."... is not valid JSON
```

## Nguyên nhân:
API trả về HTML error notices trước JSON response:
```html
<br/><b>Notice</b>: session_start(): Ignoring session_start() because a session is already active in <b>C:\xampp\htdocs\confab-web-oasis\includes\config.php</b> on line <b>26</b><br/>
{
    "status": true,
    "message": "Đăng ký thành công!"
}
```

## Giải pháp đã áp dụng:

### 1. Sửa config.php:
```php
// Trước:
session_start();

// Sau:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

### 2. Sửa API files:
- Đặt headers trước khi include config.php
- Thêm charset=UTF-8 cho Content-Type
- Kiểm tra session_status trước khi gọi session_start()

### 3. Cải thiện JavaScript error handling:
```javascript
// Trích xuất JSON từ response có HTML error
const jsonStart = responseText.indexOf('{');
if (jsonStart > 0) {
    const jsonPart = responseText.substring(jsonStart);
    result = JSON.parse(jsonPart);
}
```

## Files đã được sửa:
- `includes/config.php`
- `api/conference_registration.php`
- `api/login.php` 
- `api/users.php`
- `js/conferences-api.js`

## Cách test:
1. Đăng nhập vào hệ thống
2. Thử đăng ký hội nghị từ trang conferences.html
3. Kiểm tra console log để xem response
4. Không còn thấy HTML error notices

## Lưu ý:
- Nếu vẫn gặp lỗi, kiểm tra PHP error log
- Đảm bảo tất cả API files đều set headers trước khi output
- Kiểm tra session_start() trong tất cả include files
