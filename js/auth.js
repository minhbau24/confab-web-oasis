// auth.js - File xử lý xác thực và phân quyền người dùng

// Định nghĩa các role người dùng
const USER_ROLES = {
    GUEST: 'guest',     // Khách (chưa đăng nhập)
    USER: 'user',       // Người dùng đã đăng nhập
    ORGANIZER: 'organizer',  // Người tổ chức hội nghị
    ADMIN: 'admin'      // Quản trị viên
};

// Dữ liệu mẫu cho người dùng đăng ký
let users = [
    {
        id: 1,
        firstName: 'Nguyễn',
        lastName: 'Văn Nam',
        name: 'Nguyễn Văn Nam',
        email: 'nam@example.com',
        password: 'password123', // Trong thực tế, mật khẩu sẽ được mã hóa
        role: USER_ROLES.USER,
        phone: '0987654321',
        createdAt: '2025-05-15'
    },
    {
        id: 2,
        firstName: 'Admin',
        lastName: 'System',
        name: 'Admin System',
        email: 'admin@example.com',
        password: 'admin123', // Trong thực tế, mật khẩu sẽ được mã hóa
        role: USER_ROLES.ADMIN,
        phone: '0123456789',
        createdAt: '2025-05-10'
    }
];

// Lưu trữ thông tin người dùng hiện tại
let currentUser = {
    id: null,
    name: null,
    email: null,
    role: USER_ROLES.GUEST,
    token: null
};

// Hàm kiểm tra trạng thái đăng nhập
function isLoggedIn() {
    // Kiểm tra token hoặc session
    return currentUser && currentUser.token;
}

// Expose isLoggedIn to window for other scripts to use
window.isLoggedIn = isLoggedIn;

// Hàm lấy thông tin người dùng hiện tại
function getCurrentUser() {
    return currentUser;
}

// Expose currentUser and getCurrentUser to window for other scripts to use
window.authCurrentUser = currentUser; // Direct access to the user object
window.getCurrentUser = getCurrentUser; // Access through function

// Hàm cập nhật lại global user sau mỗi lần thay đổi
function updateGlobalUser() {
    window.authCurrentUser = currentUser;
    window.getCurrentUser = getCurrentUser;
}

// Hàm lấy role của người dùng hiện tại
function getCurrentUserRole() {
    return currentUser ? currentUser.role : USER_ROLES.GUEST;
}

// Kiểm tra người dùng có quyền cần thiết không
function hasPermission(requiredRole) {
    const userRole = getCurrentUserRole();
    
    switch(userRole) {
        case USER_ROLES.ADMIN:
            return true; // Admin có tất cả các quyền
        case USER_ROLES.ORGANIZER:
            return requiredRole !== USER_ROLES.ADMIN; // Organizer có tất cả các quyền trừ Admin
        case USER_ROLES.USER:
            return requiredRole === USER_ROLES.USER || requiredRole === USER_ROLES.GUEST;
        case USER_ROLES.GUEST:
            return requiredRole === USER_ROLES.GUEST;
        default:
            return false;
    }
}

// Hàm đăng ký người dùng mới
async function register(userData) {
    try {
        // Kiểm tra URL hiện tại trước
        if (!checkCurrentUrl()) return { success: false, error: 'Invalid URL detected' };
          // Sử dụng API helper để gọi API an toàn
        const result = await apiPost('api/register.php', {
            firstName: userData.firstName,
            lastName: userData.lastName,
            email: userData.email,
            password: userData.password,
            userType: userData.userType,
            phone: userData.phone || ''
        });
        
        // Nếu API trả về thành công
        if (result.success) {
            console.log('Đăng ký thành công:', result.user);
            
            return { 
                success: true, 
                message: result.message || 'Đăng ký thành công. Vui lòng đăng nhập với tài khoản mới.',
                user: result.user
            };
        }
        
        // Nếu API trả về lỗi
        return { 
            success: false, 
            error: result.message || 'Đăng ký thất bại. Vui lòng kiểm tra lại thông tin.'
        };
    } catch (error) {
        console.error('Lỗi khi đăng ký:', error);
        
        // Fallback nếu API không hoạt động: sử dụng dữ liệu mẫu (chỉ cho môi trường demo)
        // Kiểm tra email đã tồn tại chưa
        const existingUser = users.find(user => user.email === userData.email);
        if (existingUser) {
            return { 
                success: false, 
                error: 'Email đã được sử dụng. Vui lòng dùng email khác hoặc đăng nhập nếu đã có tài khoản.' 
            };
        }

        // Tạo người dùng mới
        const newUser = {
            id: users.length + 1,
            firstName: userData.firstName,
            lastName: userData.lastName,
            name: `${userData.firstName} ${userData.lastName}`,
            email: userData.email,
            password: userData.password,
            role: userData.userType === 'organizer' ? USER_ROLES.ORGANIZER : USER_ROLES.USER,
            phone: userData.phone || null,
            createdAt: new Date().toISOString().split('T')[0]
        };

        // Thêm vào danh sách người dùng
        users.push(newUser);
        
        console.log('Đăng ký thành công (dữ liệu mẫu):', newUser);
        
        return { 
            success: true, 
            message: 'Đăng ký thành công (chế độ demo). Vui lòng đăng nhập với tài khoản mới.',
            user: { ...newUser, password: undefined }
        };
    }
}

// Hàm đăng nhập
async function login(email, password, rememberMe = false) {
    try {
        // Kiểm tra URL hiện tại trước
        if (!checkCurrentUrl()) return { success: false, error: 'Invalid URL detected' };
        
        // Sử dụng API helper để gọi API an toàn
        const result = await apiPost('api/login.php', {
            email: email,
            password: password,
            rememberMe: rememberMe
        });
        
        // Nếu API trả về thành công
        if (result.success) {
            // Tạo token (nếu API không trả về)
            const token = result.user.token || 'jwt-token-' + Math.random().toString(36).substring(2, 15);
            
            // Lưu thông tin người dùng
            currentUser = {
                id: result.user.id,
                name: result.user.name,
                email: result.user.email,
                role: result.user.role,
                token: token
            };
              // Kiểm tra và log thông tin session từ server
            if (result.debug) {
                console.log('Server session data:', result.debug);
            }
            
            // Lưu token
            if (rememberMe) {
                localStorage.setItem('authToken', token);
                localStorage.setItem('user', JSON.stringify(currentUser));
                console.log('User data saved to localStorage');
            } else {
                sessionStorage.setItem('authToken', token);
                sessionStorage.setItem('user', JSON.stringify(currentUser));
                console.log('User data saved to sessionStorage');
            }
            
            console.log('Đăng nhập thành công:', currentUser);
            
            // Thêm debug để kiểm tra nếu có URL chuyển hướng không mong muốn
            try {
                const pageUrl = window.location.href;
                console.log('Current page URL:', pageUrl);
                
                // Kiểm tra các trường hợp URL đặc biệt trong document
                if (document.referrer) {
                    console.log('Referrer URL:', document.referrer);
                }
                
                // Log ra các meta redirect nếu có
                const metaTags = document.querySelectorAll('meta[http-equiv="refresh"]');
                if (metaTags.length > 0) {
                    console.warn('Found meta refresh tags:', metaTags.length);
                    metaTags.forEach(tag => {
                        console.warn('Meta refresh content:', tag.getAttribute('content'));
                    });
                }
            } catch (e) {
                console.error('Error in login debugging:', e);
            }
            
            updateGlobalUser(); // Cập nhật global user sau khi đăng nhập thành công
            
            return { success: true, user: currentUser };
        }
        
        return { success: false, error: result.message || 'Email hoặc mật khẩu không đúng.' };
    } catch (error) {
        console.error('Lỗi khi đăng nhập:', error);
        
        // Fallback cho môi trường demo
        const user = users.find(u => u.email === email && u.password === password);
        if (user) {
            const token = 'jwt-token-' + Math.random().toString(36).substring(2, 15);
            currentUser = {
                id: user.id,
                name: user.name,
                email: user.email,
                role: user.role,
                token: token
            };
            
            if (rememberMe) {
                localStorage.setItem('authToken', token);
                localStorage.setItem('user', JSON.stringify(currentUser));
            } else {
                sessionStorage.setItem('authToken', token);
                sessionStorage.setItem('user', JSON.stringify(currentUser));
            }
            
            updateGlobalUser(); // Cập nhật global user sau khi đăng nhập thành công (demo)
            
            return { success: true, user: currentUser };
        }
        
        return { success: false, error: 'Email hoặc mật khẩu không đúng.' };
    }
}

// Hàm đăng xuất
function logout() {
    // Xóa thông tin người dùng
    currentUser = {
        id: null,
        name: null,
        email: null,
        role: USER_ROLES.GUEST,
        token: null
    };
    
    // Call the logout API endpoint for server-side logout
    fetch('api/logout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .catch(error => {
        console.error('Logout API error:', error);
    })
    .finally(() => {
        // Regardless of API success, clear client-side data
        // Xóa token từ localStorage và sessionStorage
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        sessionStorage.removeItem('authToken');
        sessionStorage.removeItem('user');
        
        updateGlobalUser(); // Cập nhật global user sau khi đăng xuất
        
        // Chuyển hướng về trang chủ (đảm bảo dùng .html)
        window.location.href = 'index.html';
    });
}

// Kiểm tra quyền truy cập vào trang
function checkPageAccess(requiredRole) {
    if (!hasPermission(requiredRole)) {
        // Hiển thị thông báo và chuyển hướng đến trang đăng nhập
        alert('Bạn không có quyền truy cập trang này!');
        
        // Lấy tên file hiện tại thay vì toàn bộ đường dẫn để tránh lỗi đường dẫn tuyệt đối
        let currentPage = 'index.html';
        
        try {
            const currentPath = window.location.pathname;
            const filename = currentPath.substring(currentPath.lastIndexOf('/') + 1);
            
            // Nếu tên file có phần mở rộng
            if (filename.includes('.')) {                // Sử dụng tên file như nó là
                currentPage = filename;
            } else if (filename) {
                // Nếu không có phần mở rộng, thêm .html
                currentPage = filename + '.html';
            }
        } catch (e) {
            console.error('Lỗi khi xử lý đường dẫn trang:', e);
        }
        
        // Chuyển hướng tới trang đăng nhập với tham số redirect là trang hiện tại
        window.location.href = 'login.html?redirect=' + encodeURIComponent(currentPage);
    }
}

// Khởi tạo auth khi tải trang
function initAuth() {
    // Thử lấy token từ localStorage hoặc sessionStorage
    const token = localStorage.getItem('authToken') || sessionStorage.getItem('authToken');
    const user = localStorage.getItem('user') || sessionStorage.getItem('user');
    
    if (token && user) {
        try {
            currentUser = JSON.parse(user);
            currentUser.token = token;
            updateGlobalUser(); // Đảm bảo cập nhật global
            console.log('Đã khôi phục phiên đăng nhập:', currentUser.name);
        } catch (error) {
            console.error('Lỗi khi phân tích thông tin người dùng:', error);
            logout(); // Đăng xuất nếu có lỗi
        }
    } else {
        updateGlobalUser(); // Đảm bảo cập nhật global khi không có user
    }
}

// Hiển thị thông báo trạng thái đăng nhập/đăng ký
function showAuthMessage(message, type = 'info') {
    // Tạo và hiển thị thông báo cho người dùng
    // Trong thực tế, bạn có thể sử dụng thư viện toast hoặc alert
    const alertClass = type === 'error' ? 'danger' : type;
    
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${alertClass} alert-dismissible fade show`;
    alertContainer.setAttribute('role', 'alert');
    
    const messageText = document.createTextNode(message);
    alertContainer.appendChild(messageText);
    
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.setAttribute('data-bs-dismiss', 'alert');
    closeButton.setAttribute('aria-label', 'Close');
    alertContainer.appendChild(closeButton);
    
    // Thêm vào đầu trang hoặc vùng thông báo
    const formContainer = document.querySelector('.login-form') || document.querySelector('.register-form');
    if (formContainer) {
        formContainer.insertBefore(alertContainer, formContainer.firstChild);
    }
}

// Tạo URL chuyển hướng sau đăng nhập
function getRedirectUrl() {
    // Default to homepage
    let redirectUrl = 'index.html';

    // Get redirect URL from query parameter if available
    const params = new URLSearchParams(window.location.search);
    const redirect = params.get('redirect');
    
    if (redirect && redirect.trim()) {
        // Simple validation - accept only HTML files with basic names
        const simplifiedRedirect = redirect.replace(/[^a-zA-Z0-9_\-\.]/g, '');
        
        // Only accept .html files with valid names
        if (simplifiedRedirect.endsWith('.html')) {
            redirectUrl = simplifiedRedirect;
        }
        
        console.log('Redirect parameter:', redirect);
        console.log('Using redirect URL:', redirectUrl);
    }
    
    return redirectUrl;
}

// Gọi hàm khởi tạo khi trang được tải
document.addEventListener('DOMContentLoaded', function() {
    initAuth();
});
