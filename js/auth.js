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

// Hàm lấy thông tin người dùng hiện tại
function getCurrentUser() {
    return currentUser;
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
            password: userData.password, // Trong thực tế, cần mã hóa mật khẩu
            role: userData.userType === 'organizer' ? USER_ROLES.ORGANIZER : USER_ROLES.USER,
            phone: userData.phone || null,
            createdAt: new Date().toISOString().split('T')[0]
        };

        // Thêm vào danh sách người dùng
        users.push(newUser);
        
        // Trong môi trường thực tế, sẽ gọi API để lưu thông tin vào database

        console.log('Đăng ký thành công:', newUser);
        
        // Trả về kết quả thành công
        return { 
            success: true, 
            message: 'Đăng ký thành công. Vui lòng đăng nhập với tài khoản mới.',
            user: { ...newUser, password: undefined } // Không trả về mật khẩu
        };
    } catch (error) {
        console.error('Lỗi khi đăng ký:', error);
        return { success: false, error: 'Đã có lỗi xảy ra. Vui lòng thử lại sau.' };
    }
}

// Hàm đăng nhập
async function login(email, password, rememberMe = false) {
    // Giả lập API call
    try {
        // Kiểm tra thông tin đăng nhập
        const user = users.find(u => u.email === email && u.password === password);
        
        if (user) {
            // Tạo token (giả lập)
            const token = 'jwt-token-' + Math.random().toString(36).substring(2, 15);
            
            // Lưu thông tin người dùng
            currentUser = {
                id: user.id,
                name: user.name,
                email: user.email,
                role: user.role,
                token: token
            };
            
            // Lưu token vào localStorage nếu chọn "remember me"
            if (rememberMe) {
                localStorage.setItem('authToken', token);
                localStorage.setItem('user', JSON.stringify(currentUser));
            } else {
                // Lưu vào sessionStorage nếu không chọn "remember me"
                sessionStorage.setItem('authToken', token);
                sessionStorage.setItem('user', JSON.stringify(currentUser));
            }
            
            console.log('Đăng nhập thành công:', currentUser);
            
            // Chuyển hướng tới trang chủ
            window.location.href = 'index.html';
            
            return { success: true, user: currentUser };
        }
        
        return { success: false, error: 'Email hoặc mật khẩu không đúng.' };
    } catch (error) {
        console.error('Lỗi khi đăng nhập:', error);
        return { success: false, error: 'Đã có lỗi xảy ra. Vui lòng thử lại sau.' };
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
    
    // Xóa token từ localStorage và sessionStorage
    localStorage.removeItem('authToken');
    localStorage.removeItem('user');
    sessionStorage.removeItem('authToken');
    sessionStorage.removeItem('user');
    
    // Chuyển hướng về trang chủ
    window.location.href = 'index.html';
}

// Kiểm tra quyền truy cập vào trang
function checkPageAccess(requiredRole) {
    if (!hasPermission(requiredRole)) {
        // Hiển thị thông báo và chuyển hướng đến trang đăng nhập
        alert('Bạn không có quyền truy cập trang này!');
        window.location.href = 'login.html?redirect=' + encodeURIComponent(window.location.href);
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
            console.log('Đã khôi phục phiên đăng nhập:', currentUser.name);
        } catch (error) {
            console.error('Lỗi khi phân tích thông tin người dùng:', error);
            logout(); // Đăng xuất nếu có lỗi
        }
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
    const params = new URLSearchParams(window.location.search);
    return params.get('redirect') || 'index.html';
}

// Gọi hàm khởi tạo khi trang được tải
document.addEventListener('DOMContentLoaded', function() {
    initAuth();
});
