// Hàm render header cho toàn bộ trang web
function renderHeader() {
    // Kiểm tra trạng thái đăng nhập và quyền người dùng (nếu auth.js được load)
    let isUserLoggedIn = false;
    let userName = "Đăng nhập";
    let userRole = "guest";
    
    // Kiểm tra xem auth.js có được load không
    if (typeof isLoggedIn === 'function') {
        isUserLoggedIn = isLoggedIn();
        if (isUserLoggedIn && typeof getCurrentUser === 'function') {
            const user = getCurrentUser();
            // Handle potentially null user
            if (user) {
                userName = user.name || "Người dùng";
                userRole = user.role || "user";
            } else {
                userName = "Người dùng";
                userRole = "user";
            }
        }
    }    // Menu items sẽ hiển thị dựa trên quyền
    const adminMenu = userRole === 'admin' ? `
        <li class="nav-item">
            <a class="nav-link ${isActivePage('admin.html') ? 'active' : ''}" href="admin.html">
                <i class="fas fa-cog me-1"></i>Quản trị
            </a>
        </li>` : '';
    
    const organizerMenu = (userRole === 'admin' || userRole === 'organizer') ? `
        <li class="nav-item">
            <a class="nav-link ${isActivePage('conference-manager.html') ? 'active' : ''}" href="conference-manager.html">
                <i class="fas fa-tasks me-1"></i>Quản lý Hội nghị
            </a>
        </li>` : '';

    const profileMenu = isUserLoggedIn ? `        <li class="nav-item">
            <a class="nav-link ${isActivePage('profile.html') ? 'active' : ''}" href="profile.html">
                <i class="fas fa-user me-1"></i>Hồ sơ
            </a>
        </li>` : '';    
    
    // User menu
    const userMenu = isUserLoggedIn ? `
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <i class="fas fa-user me-1"></i>${userName}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">                <li><a class="dropdown-item" href="profile.html">Hồ sơ</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="logout()">Đăng xuất</a></li>
            </ul>
        </li>` : `
        <li class="nav-item me-2">
            <a class="nav-link" href="login.html">
                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="register.html">
                <i class="fas fa-user-plus me-1"></i>Đăng ký
            </a>
        </li>`;const header = `
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.html">
                <i class="fas fa-calendar-alt me-2"></i>Confab Web Oasis
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link ${isActivePage('index.html') ? 'active' : ''}" href="index.html">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>                    <li class="nav-item">
                        <a class="nav-link ${isActivePage('conferences.html') ? 'active' : ''}" href="conferences.html">
                            <i class="fas fa-calendar me-1"></i>Danh sách Hội nghị
                        </a>
                    </li>
                    ${organizerMenu}
                    ${profileMenu}
                    ${adminMenu}
                </ul>
                <ul class="navbar-nav">
                    ${userMenu}
                </ul>
            </div>
        </div>
    </nav>`;

    document.getElementById('header-container').innerHTML = header;
    
    // Thêm modal đăng nhập nếu chưa có
    if (!document.getElementById('loginModal') && !isUserLoggedIn) {
        addLoginModal();
    }
}

// Xác định trang hiện tại có active không
function isActivePage(pagePath) {
    // Lấy đường dẫn hiện tại
    const currentPath = window.location.pathname;
    const currentFile = currentPath.split('/').pop() || 'index.html'; // Mặc định là index.html nếu không có file
    
    // So sánh tên file
    return currentFile === pagePath;
}

// Hàm tạo URL tương đối từ gốc của ứng dụng
function getBaseUrl(path) {
    // Sử dụng đường dẫn tương đối để linh hoạt hơn
    return path;
}

// Thêm hàm để kiểm tra quyền người dùng (sẽ triển khai sau)
function checkUserPermission(requiredRole) {
    // Giả lập hàm kiểm tra quyền, sau này có thể thay đổi để tích hợp với hệ thống xác thực
    const userRole = getCurrentUserRole();
    return userRole === requiredRole;
}

// Hàm lấy quyền của người dùng hiện tại (sẽ triển khai sau)
function getCurrentUserRole() {
    // Nếu có auth.js, dùng hàm từ đó
    if (typeof getCurrentUser === 'function') {
        const user = getCurrentUser();
        return user ? user.role : 'guest';
    }
    // Fallback nếu không có hàm từ auth.js
    return 'guest';
}

// Thêm modal đăng nhập
function addLoginModal() {
    const loginModalHTML = `
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="loginModalLabel">Đăng nhập</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-login-alert" class="alert" role="alert" style="display: none;"></div>
                    <form id="modal-login-form">
                        <div class="mb-3">
                            <label for="modal-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="modal-email" required>
                        </div>
                        <div class="mb-3">
                            <label for="modal-password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="modal-password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="modal-remember-me">
                            <label class="form-check-label" for="modal-remember-me">Ghi nhớ đăng nhập</label>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Đăng nhập</button>
                    </form>
                </div>                <div class="modal-footer justify-content-center">
                    <p class="mb-0">Chưa có tài khoản? <a href="register.html" class="fw-bold">Đăng ký ngay</a></p>
                </div>
            </div>
        </div>
    </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', loginModalHTML);
    
    // Xử lý form đăng nhập trong modal
    document.getElementById('modal-login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('modal-email').value;
        const password = document.getElementById('modal-password').value;
        const rememberMe = document.getElementById('modal-remember-me').checked;
        
        // Sử dụng hàm login từ auth.js nếu có
        if (typeof login === 'function') {
            login(email, password, rememberMe)
                .then(result => {
                    if (result.success) {
                        showModalAlert('success', 'Đăng nhập thành công! Đang chuyển hướng...');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showModalAlert('danger', result.error || 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.');
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);
                    showModalAlert('danger', 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại sau.');
                });
        } else {
            // Fallback nếu không có hàm login từ auth.js
            console.log('Đăng nhập với:', { email, password, rememberMe });
            showModalAlert('success', 'Đăng nhập thành công! Đang chuyển hướng...');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    });
    
    function showModalAlert(type, message) {
        const alertElement = document.getElementById('modal-login-alert');
        alertElement.className = `alert alert-${type}`;
        alertElement.textContent = message;
        alertElement.style.display = 'block';
    }
}

// Khi trang được tải sẽ render header
document.addEventListener('DOMContentLoaded', function() {
    renderHeader();
});
