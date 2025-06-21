# Hướng dẫn chuyển đổi kiến trúc HTML/PHP

## Tổng quan về thay đổi

Chúng ta đã chuyển đổi kiến trúc của ứng dụng để tuân theo mô hình phân chia rõ ràng:

1. **HTML files**: Chịu trách nhiệm hiển thị giao diện người dùng (UI)
2. **PHP files (API)**: Chịu trách nhiệm xử lý dữ liệu và logic nghiệp vụ
3. **JavaScript files**: Kết nối các file HTML và API lại với nhau

## Các file đã cập nhật

### API Files
1. `api/setup_database.php`: Đã sửa để trả về JSON thay vì render HTML
2. `api/import_sample_data.php`: Đã sửa để trả về JSON thay vì render HTML
3. `api/home.php`: File mới tạo để cung cấp dữ liệu cho trang chủ
4. `api/conferences.php`: API cung cấp dữ liệu về hội nghị, hỗ trợ lấy chi tiết một hội nghị theo ID
5. `api/conference_schedule.php`: API mới để lấy lịch trình của một hội nghị dựa trên ID
6. `api/conference_speakers.php`: API mới để lấy thông tin diễn giả của một hội nghị

### HTML Files
1. `setup-database.html`: File mới tạo để hiển thị giao diện thiết lập cơ sở dữ liệu
2. `index.html`: Đã cập nhật để sử dụng API
3. `conference-detail.html`: Đã cập nhật để tải dữ liệu từ API thay vì sử dụng dữ liệu cứng

### JavaScript Files
1. `js/setup-database.js`: File mới để kết nối `setup-database.html` với các API
2. `js/home-api.js`: File mới để kết nối `index.html` với API home.php
3. `js/conference-detail-api.js`: File mới thay thế `conference-detail.js` để lấy dữ liệu từ API

## Luồng dữ liệu mới

1. Người dùng truy cập file HTML
2. File HTML tải các file JavaScript cần thiết
3. JavaScript gọi API PHP để lấy dữ liệu
4. PHP xử lý yêu cầu, truy vấn cơ sở dữ liệu và trả về kết quả dạng JSON
5. JavaScript nhận dữ liệu JSON và hiển thị lên giao diện HTML

## Kết quả đạt được

Đã hoàn thành các phần chính của việc chuyển đổi kiến trúc:

1. Đã tạo các API endpoint chính cho các chức năng chính của ứng dụng
2. Cập nhật các file JavaScript để sử dụng API thay vì dữ liệu cứng
3. Tách biệt phần UI (HTML) và phần logic xử lý dữ liệu (PHP/API)
4. Đã xử lý các trường hợp lỗi khi kết nối với API

## Các bước tiếp theo

Để hoàn thiện việc chuyển đổi toàn bộ ứng dụng, cần thực hiện các bước sau:

1. Tạo các API endpoint còn lại (ví dụ: quản lý người dùng, đăng ký hội nghị)
2. Cập nhật các phần còn lại của ứng dụng để sử dụng API
3. Kiểm tra toàn diện ứng dụng để đảm bảo hoạt động chính xác
4. Tối ưu hóa hiệu năng và trải nghiệm người dùng

## Các cập nhật bổ sung

### Sửa lỗi đường dẫn trong header và trang chính
- Sửa tất cả các đường dẫn từ tương đối thành tuyệt đối với tiền tố `/confab-web-oasis/`
- Thêm file .htaccess để xử lý các yêu cầu URL
- Tạo trang 404 tùy chỉnh

### Sửa lỗi render lịch trình hội nghị
- Cập nhật cấu trúc bảng `conference_schedule` để hỗ trợ thời gian bắt đầu và kết thúc
- Sửa lại dữ liệu mẫu để mỗi ngày hội nghị có lịch trình khác nhau
- Tối ưu hóa cách render lịch trình để hiển thị đúng theo ngày

## Lợi ích của kiến trúc mới

1. **Phân tách trách nhiệm**: HTML chỉ hiển thị UI, PHP chỉ xử lý dữ liệu
2. **Dễ bảo trì**: Có thể thay đổi UI mà không ảnh hưởng đến logic xử lý dữ liệu
3. **Hiệu suất**: Có thể lưu HTML vào cache và chỉ gọi API khi cần
4. **Khả năng mở rộng**: Có thể thêm frontend mới (mobile app, desktop app) mà vẫn sử dụng chung API
