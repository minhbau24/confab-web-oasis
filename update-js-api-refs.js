/**
 * Script để cập nhật tham chiếu API trong các file JavaScript
 * 
 * Script này kiểm tra và cập nhật đường dẫn API trong các file JavaScript
 * để đảm bảo chúng gọi các endpoint trong thư mục api/ thay vì các file PHP gốc
 */

// Danh sách các file JavaScript cần kiểm tra
const jsFiles = [
    'js/auth.js',
    'js/data.js',
    'js/home-api.js',
    'js/profile.js',
    'js/conferences.js',
    'js/conference-detail.js',
    'js/conference-manager.js',
    'js/conference-register.js',
    'js/admin.js',
    'js/api-helper.js'
];

// Mẫu cần tìm và thay thế
const patterns = [
    // Thay thế tất cả fetch/POST đến các file PHP gốc
    { 
        search: /fetch\(['"]([^'"]*\.php)['"]/g, 
        replace: 'fetch(\'api/$1\''
    },
    { 
        search: /fetch\(['"]([^'"\/]*)(\.php)['"]/g, 
        replace: 'fetch(\'api/$1$2\''
    },
    {
        search: /url: ['"]([^'"]*\.php)['"]/g,
        replace: 'url: \'api/$1\''
    },
    {
        search: /url: ['"]([^'"\/]*)(\.php)['"]/g,
        replace: 'url: \'api/$1$2\''
    },
    // Các mẫu khác có thể thêm ở đây
];

// Mẫu cần lưu ý nhưng không thay thế tự động
const warningPatterns = [
    /\.(php|html)/g,
    /window\.location\.href\s*=\s*['"][^'"]*\.php['"]/g,
    /form.*action\s*=\s*['"][^'"]*\.php['"]/g
];

// Đường dẫn tương đối có thể tạo thêm tham chiếu không hợp lệ
const relativePathWarning = /fetch\(['"]\.\.\/|fetch\(['"]\.\/|url:\s*['"]\.\.\/|url:\s*['"]\.\/|window\.location\.href\s*=\s*['"]\.\./g;

console.log('======= Đang phân tích các file JavaScript =======');

jsFiles.forEach(file => {
    try {
        const fs = require('fs');
        const path = require('path');
        
        console.log(`Đang kiểm tra file: ${file}`);
        
        // Đảm bảo đường dẫn tuyệt đối
        const filePath = path.resolve(__dirname, file);
        
        // Kiểm tra file tồn tại
        if (!fs.existsSync(filePath)) {
            console.log(`  - Bỏ qua: File không tồn tại`);
            return;
        }
        
        // Đọc nội dung file
        let content = fs.readFileSync(filePath, 'utf8');
        const originalContent = content;
        
        // Tìm và thay thế các mẫu
        let changesCount = 0;
        patterns.forEach(pattern => {
            const matches = content.match(pattern.search);
            if (matches) {
                changesCount += matches.length;
                content = content.replace(pattern.search, pattern.replace);
                console.log(`  - Đã tìm thấy ${matches.length} tham chiếu theo mẫu: ${pattern.search}`);
            }
        });
        
        // Cảnh báo về các mẫu có thể cần kiểm tra thủ công
        let warningsCount = 0;
        warningPatterns.forEach(pattern => {
            const matches = content.match(pattern);
            if (matches) {
                warningsCount += matches.length;
                console.log(`  - CHÚ Ý: Tìm thấy ${matches.length} tham chiếu có thể cần kiểm tra: ${pattern}`);
                matches.forEach(match => {
                    console.log(`    > ${match}`);
                });
            }
        });
        
        // Cảnh báo về đường dẫn tương đối
        const relativeMatches = content.match(relativePathWarning);
        if (relativeMatches) {
            console.log(`  - CẢNH BÁO: Tìm thấy ${relativeMatches.length} đường dẫn tương đối có thể gây vấn đề:`);
            relativeMatches.forEach(match => {
                console.log(`    > ${match}`);
            });
        }
        
        // Ghi lại nội dung nếu có thay đổi
        if (content !== originalContent) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`  - ĐÃ CẬP NHẬT: Lưu ${changesCount} thay đổi vào file`);
            
            // Tạo bản sao lưu
            fs.writeFileSync(`${filePath}.backup`, originalContent, 'utf8');
            console.log(`  - Đã tạo bản sao lưu: ${file}.backup`);
        } else {
            console.log(`  - Không cần cập nhật file này`);
        }
        
        if (warningsCount > 0) {
            console.log(`  - LƯU Ý: File này có ${warningsCount} đoạn cần kiểm tra thủ công`);
        }
        
        console.log(`  - Hoàn tất kiểm tra`);
    } catch (error) {
        console.error(`Lỗi khi xử lý file ${file}:`, error);
    }
    
    console.log('----------------------------------------');
});

console.log(`
======= HƯỚNG DẪN TIẾP THEO =======
1. Kiểm tra các file JavaScript đã được cập nhật và sửa các cảnh báo nếu cần
2. Thử chạy ứng dụng để đảm bảo các API endpoint mới hoạt động đúng
3. Chạy file convert-php-to-api.php để tạo các API endpoint mới từ các file PHP cũ
4. Kiểm tra sau cùng và xóa các file PHP gốc khi đã hoàn tất
`);
