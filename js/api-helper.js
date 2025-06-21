/**
 * Helper để gọi API với đường dẫn tuyệt đối, tránh các vấn đề về đường dẫn
 */

// Hàm tạo URL API tuyệt đối
function getApiUrl(endpoint) {
    // Lấy đường dẫn cơ sở từ cấu hình
    const origin = window.location.origin; // Ví dụ: http://localhost
    const basePath = window.appConfig ? window.appConfig.basePath : '/confab-web-oasis/'; 
    const apiPath = window.appConfig ? window.appConfig.apiBasePath : '/confab-web-oasis/api/';
    
    // Đảm bảo endpoint không có dấu / ở đầu
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    
    // Kiểm tra nếu endpoint đã chứa 'api/' thì không thêm vào nữa
    let fullUrl;
    if (cleanEndpoint.startsWith('api/')) {
        fullUrl = `${origin}${basePath}${cleanEndpoint}`;
    } else {
        // Nếu đường dẫn là file .php nhưng không nằm trong thư mục api/, thêm tiền tố api/
        if (cleanEndpoint.endsWith('.php') && !cleanEndpoint.startsWith('api/')) {
            fullUrl = `${origin}${apiPath}${cleanEndpoint}`;
        } else {
            fullUrl = `${origin}${basePath}${cleanEndpoint}`;
        }
    }
    
    if (window.appConfig && window.appConfig.debug) {
        console.log(`API URL resolved: ${fullUrl}`);
    }
    return fullUrl;
}

// Hàm gọi API với kiểu GET
async function apiGet(endpoint, options = {}) {
    try {
        const url = getApiUrl(endpoint);
        console.log(`API GET request to: ${url}`);
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        return await response.json();
    } catch (error) {
        console.error(`Error in API GET to ${endpoint}:`, error);
        throw error;
    }
}

// Hàm gọi API với kiểu POST
async function apiPost(endpoint, data, options = {}) {
    try {
        const url = getApiUrl(endpoint);
        console.log(`API POST request to: ${url}`);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...options.headers
            },
            body: JSON.stringify(data),
            ...options
        });
        
        return await response.json();
    } catch (error) {
        console.error(`Error in API POST to ${endpoint}:`, error);
        throw error;
    }
}

// Hàm kiểm tra URL hiện tại và cảnh báo nếu có vấn đề
function checkCurrentUrl() {
    const currentUrl = window.location.href;
    console.log('Current URL:', currentUrl);
    
    // Kiểm tra các mẫu URL có vấn đề - chỉ kiểm tra đường dẫn Windows
    const hasWindowsPath = currentUrl.includes(':\\') || currentUrl.match(/\/[A-Za-z]:/);
    const hasAbsolutePath = currentUrl.match(/\/\/[^/]+\/[A-Za-z]:/);
    
    if (hasWindowsPath || hasAbsolutePath) {
        console.error('CRITICAL: Bad URL detected:', {
            hasWindowsPath,
            hasAbsolutePath
        });
        
        // Hiển thị cảnh báo
        alert('URL hiện tại có vấn đề. Đang chuyển hướng đến URL an toàn...');
        
        // Chuyển hướng đến URL an toàn
        const safeUrl = window.location.origin + '/confab-web-oasis/index.html';
        window.location.replace(safeUrl);
        return false;
    }
    
    return true;
}

// Kiểm tra URL khi tải trang
document.addEventListener('DOMContentLoaded', () => {
    checkCurrentUrl();
});
