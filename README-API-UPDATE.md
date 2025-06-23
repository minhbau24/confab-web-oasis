# Cập nhật API Conference-Detail cho Confab Web Oasis

## Tổng quan
Dự án này cập nhật trang conference-detail để tương thích với cơ sở dữ liệu mới, đảm bảo hiển thị đúng các thông tin của hội nghị, diễn giả, lịch trình, và các thông tin bổ sung.

## Các thay đổi chính

### 1. Cập nhật API

- **conferences.php**: Bổ sung các endpoint để lấy speakers và schedule
- **conference_detail.php**: API mới tổng hợp tất cả dữ liệu liên quan đến hội nghị
- **related_conferences.php**: API mới để hiển thị các hội nghị liên quan

### 2. Cập nhật cấu trúc dữ liệu

Đã cập nhật các phương thức trong `Conference.php` để phù hợp với schema mới:
- `getConferenceById()` - JOIN với bảng categories, venues, users
- `getConferenceSpeakers()` - JOIN với bảng speakers
- `getConferenceSchedule()` - JOIN với bảng schedule_sessions và speakers
- `getRelatedConferences()` - Lấy hội nghị liên quan dựa vào danh mục

### 3. Cập nhật JavaScript Frontend

- Cập nhật `conference-detail-api.js` để tương thích với cấu trúc dữ liệu mới
- Hỗ trợ cả API mới và API cũ để đảm bảo khả năng tương thích ngược
- Bổ sung các phương thức hiển thị thông tin mục tiêu, tính năng, FAQ và nhà tài trợ

## Cấu trúc API mới

### 1. Chi tiết hội nghị
```
GET /api/conferences.php?id={id}
```

### 2. Diễn giả của hội nghị
```
GET /api/conferences.php?id={id}&speakers=1
```

### 3. Lịch trình hội nghị
```
GET /api/conferences.php?id={id}&schedule=1
```

### 4. API tổng hợp (tất cả thông tin)
```
GET /api/conference_detail.php?id={id}
```

### 5. Hội nghị liên quan
```
GET /api/related_conferences.php?id={id}&limit=3
```

## Các trường dữ liệu mới sử dụng

- **Hội nghị**: title, description, short_description, start_date, end_date, price, capacity, current_attendees, location, banner_image, image
- **Venue**: name, address, city, state, country, description, transport_info, parking_info
- **Diễn giả**: name, title, company, bio, image, website, linkedin, twitter
- **Lịch trình**: session_date, start_time, end_time, title, description, speaker_name

## Hướng dẫn kiểm tra

1. Truy cập trang chi tiết hội nghị: `/conference-detail.html?id={id}`
2. Kiểm tra API trong DevTools: `api/conferences.php?id={id}`
3. Kiểm tra API diễn giả: `api/conferences.php?id={id}&speakers=1`
4. Kiểm tra API lịch trình: `api/conferences.php?id={id}&schedule=1`

## Developer

Cập nhật ngày: 23/06/2025
