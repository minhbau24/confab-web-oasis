# Hướng dẫn khắc phục các lỗi phổ biến

## Lỗi "Table 'categories' doesn't exist"

Nếu bạn gặp lỗi "Table 'categories' doesn't exist" khi nhập dữ liệu mẫu, đây là hướng dẫn chi tiết để khắc phục:

## Nguyên nhân

Lỗi này xảy ra vì hệ thống đang cố gắng nhập dữ liệu vào bảng `categories`, nhưng bảng này chưa được tạo trong cơ sở dữ liệu. Thông thường, điều này xảy ra khi bước "Thiết lập cấu trúc bảng dữ liệu" bị bỏ qua hoặc không thành công.

## Các bước khắc phục

### 1. Kiểm tra cơ sở dữ liệu

1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Chọn cơ sở dữ liệu của bạn từ menu bên trái
3. Kiểm tra xem bảng `categories` đã tồn tại hay chưa

### 2. Thiết lập lại cấu trúc bảng dữ liệu

1. Truy cập trang thiết lập: http://localhost/confab-web-oasis/setup.php
2. Nhấp vào nút "Kiểm tra kết nối" để kiểm tra kết nối cơ sở dữ liệu
3. Sau khi kiểm tra thành công, nhấp vào nút "Thiết lập cơ sở dữ liệu"
4. Đợi quá trình thiết lập hoàn tất (bạn sẽ thấy thông báo thành công)
5. Tiếp tục với việc nhập dữ liệu mẫu

### 3. Nếu vẫn gặp lỗi

Nếu các bước trên không giải quyết được vấn đề, bạn có thể thử cách thủ công:

1. Mở file `sql/schema_complete.sql` trong dự án
2. Sao chép toàn bộ nội dung
3. Mở phpMyAdmin và chọn cơ sở dữ liệu của bạn
4. Chọn tab "SQL" và dán nội dung của file vào
5. Nhấn nút "Go" để thực thi
6. Sau đó quay lại trình cài đặt và tiếp tục với việc nhập dữ liệu mẫu

## Đặt lại từ đầu (nếu cần thiết)

Nếu bạn muốn bắt đầu lại từ đầu:

1. Xóa tất cả các bảng trong cơ sở dữ liệu (hoặc xóa và tạo lại cơ sở dữ liệu)
2. Làm theo quy trình cài đặt từ đầu, đảm bảo tuân thủ đúng thứ tự các bước

## Kiểm tra cấu trúc bảng cơ sở dữ liệu

Hệ thống yêu cầu tối thiểu các bảng sau để hoạt động đúng:

- users
- categories
- venues
- conferences
- speakers
- conference_speakers
- conference_schedule
- registrations

Đảm bảo tất cả các bảng này đã được tạo trước khi nhập dữ liệu mẫu.

## Lỗi "Duplicate entry for key 'email'" hoặc "Integrity constraint violation"

Khi thiết lập cơ sở dữ liệu, bạn có thể gặp lỗi "Duplicate entry for key 'email'" hoặc "Integrity constraint violation".

### Nguyên nhân

Lỗi này xảy ra khi:

- Bạn đã chạy script thiết lập cơ sở dữ liệu trước đó
- Dữ liệu người dùng đã tồn tại trong bảng `users`
- Script đang cố gắng thêm người dùng với email trùng lặp

### Các bước khắc phục

#### Phương pháp 1: Bỏ qua và tiếp tục

1. Lỗi này là không nghiêm trọng và không ảnh hưởng đến chức năng của hệ thống
2. Bạn có thể bỏ qua thông báo lỗi và tiếp tục với bước tiếp theo (nhập dữ liệu mẫu)
3. Nhấn nút "Tiếp theo: Nhập dữ liệu mẫu" để tiếp tục quá trình cài đặt

#### Phương pháp 2: Thiết lập lại cơ sở dữ liệu từ đầu

1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Chọn cơ sở dữ liệu của bạn từ menu bên trái
3. Chọn tab "Operations" hoặc "Thao tác"
4. Tìm phần "Delete database" hoặc "Xóa cơ sở dữ liệu" và xác nhận
5. Tạo lại cơ sở dữ liệu trống mới với cùng tên
6. Quay lại trang thiết lập và thực hiện lại toàn bộ quy trình

#### Phương pháp 3: Xóa dữ liệu người dùng hiện có

1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Chọn cơ sở dữ liệu của bạn từ menu bên trái
3. Chọn bảng `users`
4. Chọn tab "Operations" hoặc "Thao tác"
5. Nhấn vào "Truncate table" hoặc "Làm trống bảng" để xóa tất cả người dùng
6. Quay lại trang thiết lập và chạy lại script thiết lập cơ sở dữ liệu

## Lỗi "Cannot read properties of undefined (reading 'json')"

Lỗi này xảy ra trong quá trình nhập dữ liệu mẫu và liên quan đến JavaScript fetch API.

### Nguyên nhân

Lỗi này thường xảy ra khi:
- API endpoint trả về lỗi 500 (Internal Server Error)
- API không trả về dữ liệu đúng định dạng JSON
- Có lỗi PHP không được xử lý đúng cách trong API

### Các bước khắc phục

#### Phương pháp 1: Kiểm tra lỗi trực tiếp

1. Mở trình duyệt và truy cập trực tiếp các API:
   - `http://localhost/confab-web-oasis/api/check_database_tables.php`
   - `http://localhost/confab-web-oasis/api/import_sample_data.php`
   
2. Nếu có lỗi PHP hiển thị, ghi lại và sửa lỗi trong file tương ứng

#### Phương pháp 2: Sử dụng API debug

1. Truy cập API debug: `http://localhost/confab-web-oasis/api/debug_import.php`
2. Kiểm tra thông tin lỗi được hiển thị
3. Dựa vào kết quả trả về để biết lỗi đang xảy ra ở API nào

#### Phương pháp 3: Kiểm tra Console của trình duyệt

1. Mở DevTools của trình duyệt (F12 hoặc Chuột phải > Kiểm tra)
2. Chọn tab "Console" để xem chi tiết lỗi JavaScript
3. Chọn tab "Network" để xem các request API và response

#### Phương pháp 4: Nhập dữ liệu mẫu theo cách thủ công

Nếu các phương pháp trên không giúp giải quyết vấn đề:

1. Truy cập công cụ sửa chữa: `http://localhost/confab-web-oasis/database_repair.php`
2. Kiểm tra trạng thái cơ sở dữ liệu
3. Nhấn vào "Thiết lập lại cấu trúc cơ sở dữ liệu" nếu cần
4. Nhấn vào "Nhập lại dữ liệu mẫu" để nhập dữ liệu

## Lỗi "Unknown column 'status' in 'field list'"

Nếu bạn gặp lỗi "Unknown column 'status' in 'field list'" khi nhập dữ liệu mẫu, có nghĩa là cột status trong bảng users không tồn tại hoặc bị thiếu.

### Nguyên nhân

Lỗi này xảy ra khi bảng users trong cơ sở dữ liệu không có cột 'status', nhưng script nhập dữ liệu đang cố gắng thêm giá trị vào cột này.

### Các bước khắc phục

#### 1. Sử dụng công cụ sửa chữa cấu trúc cơ sở dữ liệu

Cách đơn giản nhất là sử dụng công cụ sửa chữa được tích hợp sẵn:

1. Truy cập: http://localhost/confab-web-oasis/db_structure_fix.php
2. Nhấn vào nút "Kiểm tra cấu trúc DB" để xem tình trạng của các bảng
3. Nếu bảng users không có cột status, nhấn vào nút "Thêm cột status vào bảng users"

#### 2. Sử dụng API sửa chữa tự động

Bạn có thể sử dụng API đặc biệt để tự động thêm cột status:

1. Truy cập: http://localhost/confab-web-oasis/api/fix_users_table.php
2. API sẽ tự động thêm cột status nếu nó chưa tồn tại

#### 3. Thiết lập lại cơ sở dữ liệu

Nếu các cách trên không giải quyết được vấn đề:

1. Truy cập: http://localhost/confab-web-oasis/setup.php
2. Làm theo quy trình thiết lập từ đầu, bao gồm việc thiết lập cấu trúc cơ sở dữ liệu
3. Script thiết lập được cập nhật để đảm bảo cột status luôn được tạo đúng cách

#### 4. Sửa chữa thủ công qua phpMyAdmin (Nếu cần)

Nếu bạn muốn sửa thủ công:

1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Chọn cơ sở dữ liệu của bạn
3. Chọn bảng users
4. Chọn tab "Cấu trúc" và nhấn "Thêm cột"
5. Thêm cột với các thông số:
   - Tên: `status`
   - Loại: `ENUM`
   - Giá trị: `'active','inactive','suspended','pending'`
   - Mặc định: `active`
   - Vị trí: Sau cột `locked_until`
