# Cập nhật API Hội Nghị

Tài liệu này mô tả các thay đổi và cải tiến trong API liên quan đến hội nghị để giải quyết vấn đề truy vấn chi tiết hội nghị.

## Vấn Đề

API `conferences.php?id=X` trả về lỗi `{status: false, message: "Không tìm thấy hội nghị với ID: X"}` khi truy vấn chi tiết hội nghị dù dữ liệu tồn tại trong database.

## Giải Pháp

Chúng tôi đã tạo các API mới để giải quyết vấn đề:

### 1. API Debug: `/api/debug_conference.php?id=X`

API này kiểm tra trực tiếp dữ liệu thô trong database để xem hội nghị có tồn tại hay không.

**Ví dụ Response:**
```json
{
  "status": true,
  "message": "Dữ liệu hội nghị tồn tại trong database",
  "raw_conference_data": {
    "id": 123,
    "title": "Tên hội nghị",
    ...
  },
  "category_exists": true,
  "category_data": { ... },
  "venue_exists": true,
  "venue_data": { ... }
}
```

### 2. API Conference By ID Mới: `/api/conference_by_id.php?id=X`

API này cải thiện xử lý lỗi và cung cấp dữ liệu dự phòng nếu phương thức tổng hợp không hoạt động.

**Ví dụ Response:**
```json
{
  "status": true,
  "data": {
    "id": 123,
    "title": "Tên hội nghị",
    "description": "Mô tả hội nghị",
    ...
  }
}
```

### 3. JavaScript Cải Tiến: `/js/conference-detail-api-updated.js`

Script JavaScript mới sẽ thử lấy dữ liệu từ nhiều nguồn API khác nhau để đảm bảo luôn hiển thị được dữ liệu:

1. Thử API mới `conference_by_id.php` đầu tiên
2. Nếu không thành công, thử API cũ `conferences.php`
3. Nếu vẫn không thành công, thử API debug `debug_conference.php`

## Cách Sử Dụng

Không cần thay đổi gì trong code hiện tại, vì script mới sẽ tự động hoạt động song song với script cũ.

## Kiểm Tra Database

Nếu vẫn gặp lỗi, có thể do dữ liệu trong database có vấn đề:

1. Kiểm tra xem ID hội nghị có tồn tại trong bảng `conferences`
2. Đảm bảo `deleted_at` là NULL
3. Kiểm tra các khóa ngoại như `category_id` và `venue_id` có tồn tại trong các bảng tương ứng
4. Kiểm tra các trường JSON như `metadata`, `sponsors`, v.v. có đúng định dạng không

## Phiên Bản Tiếp Theo

Trong phiên bản tiếp theo, chúng tôi sẽ:

1. Cải thiện xử lý JSON fields và schema validation
2. Thêm tính năng cache để tăng tốc độ truy vấn
3. Thêm tính năng preview hội nghị trước khi xuất bản

**Lưu ý**: Hãy sử dụng API mới (`conference_by_id.php`) cho các dự án mới.
