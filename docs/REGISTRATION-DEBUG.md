# Hướng dẫn kiểm tra và khắc phục lỗi đăng ký hội nghị

## Các thay đổi đã thực hiện:

### 1. Sửa lỗi trong `conference-register.js`:
- Thay thế function `getUserData()` không tồn tại bằng `getCurrentUserFromMultipleSources()`
- Thêm debug logging để theo dõi quá trình xác thực
- Cải thiện xử lý lỗi khi không tìm thấy thông tin người dùng

### 2. Sửa lỗi trong `conferences-api.js`:
- Cập nhật function `getCurrentUser()` để tránh recursion và lấy từ nhiều nguồn
- Thay đổi API call từ JSON sang FormData để phù hợp với backend
- Thêm extensive logging để debug
- Thêm session credentials cho API calls

### 3. Cập nhật `conference-register.html`:
- Thêm `auth-debug.js` và `api-helper.js`
- Cải thiện khả năng debug

### 4. Tạo `auth-test.html`:
- Trang test để kiểm tra tình trạng xác thực
- Có thể truy cập qua: `http://localhost/confab-web-oasis/auth-test.html`

## Cách kiểm tra và debug:

### Bước 1: Kiểm tra tình trạng xác thực
1. Mở `http://localhost/confab-web-oasis/auth-test.html`
2. Kiểm tra các thông tin hiển thị:
   - Global Variables (window.authCurrentUser, etc.)
   - Functions (isLoggedIn, getCurrentUser)
   - localStorage data
3. Click "Test API Profile" để kiểm tra API
4. Click "Debug Auth State" để xem thông tin chi tiết

### Bước 2: Test đăng nhập
1. Nếu chưa đăng nhập, click "Go to Login" và đăng nhập
2. Kiểm tra lại `auth-test.html` sau khi đăng nhập

### Bước 3: Test đăng ký hội nghị
1. Vào `conferences.html`
2. Mở Developer Console (F12)
3. Click nút "Đăng ký" trên một hội nghị
4. Xem log trong console để theo dõi quá trình

### Bước 4: Test trang đăng ký đầy đủ
1. Vào `conference-register.html?id=1` (thay 1 bằng ID hội nghị thực tế)
2. Kiểm tra xem thông tin có được load không
3. Xem console log để debug

## Các lỗi thường gặp và cách khắc phục:

### Lỗi: "Vui lòng đăng nhập lại"
- **Nguyên nhân**: Session hết hạn hoặc thông tin user không được lưu đúng
- **Khắc phục**: Đăng nhập lại, kiểm tra localStorage và session

### Lỗi: Nút đăng ký không hoạt động
- **Nguyên nhân**: Lỗi JavaScript hoặc API không phản hồi
- **Khắc phục**: Kiểm tra console log, test API qua auth-test.html

### Lỗi: Không load được thông tin hội nghị
- **Nguyên nhân**: API conferences.php có vấn đề hoặc ID không hợp lệ
- **Khắc phục**: Kiểm tra URL có ID đúng không, test API trực tiếp

## Debug Commands:
Mở console và chạy các lệnh sau:

```javascript
// Kiểm tra user hiện tại
console.log(window.authCurrentUser);

// Debug authentication state
window.debugAuthState();

// Kiểm tra localStorage
console.log(localStorage.getItem('user'));

// Test API
fetch('api/users.php?action=profile', {credentials: 'include'})
  .then(r => r.json())
  .then(console.log);
```
