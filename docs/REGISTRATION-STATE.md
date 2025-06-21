# Tính năng thay đổi trạng thái nút đăng ký hội nghị (Database-based)

## Mô tả
Sau khi user đăng ký hội nghị thành công, nút "Đăng ký" sẽ thay đổi thành "Đã đăng ký" và được disable để tránh đăng ký trùng lặp. Trạng thái được lưu trong database và đồng bộ giữa các thiết bị.

## Tính năng chính

### 1. Thay đổi trạng thái nút ngay lập tức
- **Trước đăng ký**: Nút màu xanh lá với text "Đăng ký"
- **Sau đăng ký**: Nút màu xám với text "Đã đăng ký" và bị disable

### 2. Lưu trạng thái trong database
- Trạng thái đăng ký được lưu trong bảng `registrations`
- API `user_registrations.php` để lấy danh sách đã đăng ký
- Đồng bộ giữa các thiết bị và session

### 3. Cập nhật số lượng người tham dự
- Số người tham dự được cập nhật ngay lập tức
- Progress bar cũng được cập nhật theo

### 4. Tooltip thông báo
- Nút "Đã đăng ký" có tooltip: "Bạn đã đăng ký tham dự hội nghị này"

## Database Schema

### Bảng `registrations`:
```sql
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled','attended') DEFAULT 'pending',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_conference_unique` (`user_id`,`conference_id`)
);
```

## API Endpoints

### `api/user_registrations.php`
- **Method**: GET
- **Purpose**: Lấy danh sách conference IDs mà user đã đăng ký
- **Response**: 
```json
{
  "status": true,
  "data": {
    "user_id": 7,
    "conference_ids": [1, 3, 5],
    "registrations": [...]
  }
}
```

### `api/conference_registration.php`
- **Method**: POST
- **Purpose**: Đăng ký tham dự hội nghị
- **Data**: FormData với conferenceId, userId

## Code Functions

### `fetchUserRegistrations()`
Lấy danh sách hội nghị đã đăng ký từ database khi load trang.

### `checkIfUserRegistered(conferenceId, user)`
Kiểm tra xem user đã đăng ký hội nghị chưa dựa trên data từ database.

### `updateRegistrationButton(conferenceId, isRegistered)`
Cập nhật trạng thái nút đăng ký cho một hội nghị cụ thể.

## Cách hoạt động

1. **Khi load trang**: 
   - Fetch conferences và user registrations song song
   - Hiển thị đúng trạng thái nút dựa trên database
2. **Khi click đăng ký**: 
   - Gửi request đến API
   - Nếu thành công: cập nhật array trong memory, cập nhật nút
   - Refresh data từ server để đảm bảo đồng bộ
3. **Khi reload trang**: Trạng thái được load từ database

## Ưu điểm so với localStorage:
- ✅ Đồng bộ giữa các thiết bị
- ✅ Không mất data khi clear browser
- ✅ Chính xác và đáng tin cậy
- ✅ Có thể audit và track history

## Test Cases
1. Đăng ký hội nghị → Nút thay đổi thành "Đã đăng ký"
2. Reload trang → Nút vẫn hiển thị "Đã đăng ký" 
3. Truy cập từ thiết bị khác → Nút hiển thị "Đã đăng ký"
4. Click nút "Đã đăng ký" → Không có action nào xảy ra
