/**
 * JavaScript để xử lý đăng ký hội nghị
 */

document.addEventListener('DOMContentLoaded', function() {
    // Debug authentication state
    console.log("Conference register page loaded");
    if (typeof window.debugAuthState === 'function') {
        window.debugAuthState();
    }
    
    // Kiểm tra xem người dùng đã đăng nhập chưa
    let userLoggedIn = false;
    
    // Kiểm tra nhiều cách để xác định trạng thái đăng nhập
    if (typeof window.isLoggedIn === 'function') {
        userLoggedIn = window.isLoggedIn();
    } else if (typeof isLoggedIn === 'function') {
        userLoggedIn = isLoggedIn();
    } else {
        // Kiểm tra bằng cách khác
        const user = getCurrentUserFromMultipleSources();
        userLoggedIn = user && user.id;
    }
    
    console.log("User logged in status:", userLoggedIn);
    
    if (!userLoggedIn) {
        // Lưu URL hiện tại để sau khi đăng nhập quay lại
        const currentUrl = window.location.href;
        showMessage('Vui lòng đăng nhập để tiếp tục đăng ký hội nghị.', 'error');
        setTimeout(() => {
            window.location.href = `login.html?redirect=${encodeURIComponent(currentUrl)}`;
        }, 3000);
        return;
    }

    // Lấy ID hội nghị từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (!conferenceId) {
        showMessage('Không tìm thấy thông tin hội nghị.', 'error');
        document.getElementById('registration-form').style.display = 'none';
        return;
    }

    // Tải thông tin hội nghị
    loadConferenceInfo(conferenceId);
    
    // Tải thông tin người dùng
    loadUserInfo();
    
    // Xử lý form đăng ký
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            registerForConference(conferenceId);
        });
    }
});

// Helper function để lấy user từ nhiều nguồn
function getCurrentUserFromMultipleSources() {
    // Thử lấy từ các nguồn khác nhau
    if (typeof window.getCurrentUser === 'function') {
        return window.getCurrentUser();
    } else if (typeof getCurrentUser === 'function') {
        return getCurrentUser();
    } else if (typeof window.authCurrentUser !== 'undefined') {
        return window.authCurrentUser;
    } else {
        // Cuối cùng thử lấy từ localStorage
        const storedUser = localStorage.getItem('user');
        return storedUser ? JSON.parse(storedUser) : null;
    }
}

/**
 * Hiển thị thông báo
 */
function showMessage(message, type = 'success') {
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const icon = type === 'error' ? 'exclamation-circle' : 'check-circle';
    
    document.getElementById('message-container').innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${icon} me-2"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Cuộn lên đầu trang nếu có lỗi
    if (type === 'error') {
        window.scrollTo(0, 0);
    }
}

/**
 * Tải thông tin hội nghị
 */
async function loadConferenceInfo(conferenceId) {
    try {
        const response = await fetch(`api/conferences.php?id=${conferenceId}`);
        if (!response.ok) {
            throw new Error('Không thể tải thông tin hội nghị');
        }
        
        const result = await response.json();
        
        if (result.status && result.data) {
            const conference = result.data;
            
            // Hiển thị thông tin hội nghị
            const conferenceInfo = document.getElementById('conference-info');
            conferenceInfo.classList.remove('loading');
            
            // Format ngày
            let dateDisplay = formatDate(conference.date);
            if (conference.end_date) {
                dateDisplay += ` - ${formatDate(conference.end_date)}`;
            }
            
            // Format giá
            const price = parseInt(conference.price).toLocaleString('vi-VN') + '₫';
            
            conferenceInfo.innerHTML = `
                <h4>${conference.title}</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p><i class="fas fa-calendar me-2"></i>${dateDisplay}</p>
                        <p><i class="fas fa-map-marker-alt me-2"></i>${conference.location}</p>
                    </div>
                    <div class="col-md-6">
                        <p><i class="fas fa-tag me-2"></i>${price}</p>
                        <p><i class="fas fa-users me-2"></i>${conference.attendees}/${conference.capacity} người tham dự</p>
                    </div>
                </div>
            `;
            
            // Kiểm tra xem hội nghị còn chỗ không
            if (conference.capacity <= conference.attendees) {
                showMessage('Rất tiếc, hội nghị đã đủ số lượng người tham dự.', 'error');
                document.getElementById('registration-form').style.display = 'none';
            }
        } else {
            throw new Error(result.message || 'Không tìm thấy thông tin hội nghị');
        }
    } catch (error) {
        showMessage(error.message || 'Có lỗi xảy ra khi tải thông tin hội nghị', 'error');
        document.getElementById('registration-form').style.display = 'none';
    }
}

/**
 * Tải thông tin người dùng
 */
async function loadUserInfo() {
    try {
        // Sử dụng helper function để lấy thông tin user
        let userData = getCurrentUserFromMultipleSources();
        
        console.log("User data from sources:", userData);
        
        if (userData && userData.id) {
            // Điền thông tin vào form
            const firstNameField = document.getElementById('firstName');
            const lastNameField = document.getElementById('lastName');
            const emailField = document.getElementById('email');
            const phoneField = document.getElementById('phone');
            
            if (firstNameField) firstNameField.value = userData.firstName || userData.name?.split(' ')[0] || '';
            if (lastNameField) lastNameField.value = userData.lastName || userData.name?.split(' ').slice(1).join(' ') || '';
            if (emailField) emailField.value = userData.email || '';
            if (phoneField) phoneField.value = userData.phone || '';
            
            console.log("Tải thông tin người dùng thành công:", userData);
        } else {
            console.warn("Không tìm thấy thông tin user từ local sources, thử lấy từ API...");
            
            // Nếu không có thông tin người dùng, có thể lấy từ API
            const response = await fetch('api/users.php?action=profile', {
                method: 'GET',
                credentials: 'include' // Đảm bảo gửi cookie session
            });
            
            if (!response.ok) {
                throw new Error('Không thể tải thông tin người dùng từ API');
            }
            
            const result = await response.json();
            
            if (result.status && result.data) {
                const user = result.data;
                
                // Điền thông tin vào form
                const firstNameField = document.getElementById('firstName');
                const lastNameField = document.getElementById('lastName');
                const emailField = document.getElementById('email');
                const phoneField = document.getElementById('phone');
                
                if (firstNameField) firstNameField.value = user.firstName || '';
                if (lastNameField) lastNameField.value = user.lastName || '';
                if (emailField) emailField.value = user.email || '';
                if (phoneField) phoneField.value = user.phone || '';
                
                console.log("Tải thông tin người dùng từ API thành công:", user);
            } else {
                throw new Error(result.message || 'Không tìm thấy thông tin người dùng');
            }
        }
    } catch (error) {
        showMessage('Không thể tải thông tin người dùng. Vui lòng đăng nhập lại.', 'error');
    }
}

/**
 * Đăng ký tham dự hội nghị
 */
async function registerForConference(conferenceId) {
    try {
        // Kiểm tra điều khoản
        const agreeTerms = document.getElementById('agreeTerms').checked;
        if (!agreeTerms) {
            showMessage('Bạn cần đồng ý với điều khoản và điều kiện để tiếp tục.', 'error');
            return;
        }
        
        // Lấy dữ liệu từ form
        const formData = new FormData(document.getElementById('register-form'));
        formData.append('conferenceId', conferenceId);
        
        // Disable nút đăng ký để tránh gửi nhiều lần
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
        
        // Gửi request đăng ký
        const response = await fetch('api/conference_registration.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status) {
            // Đăng ký thành công
            showMessage('Đăng ký tham dự hội nghị thành công! Chúng tôi sẽ gửi thông tin chi tiết qua email của bạn.');
            
            // Ẩn form đăng ký
            document.getElementById('registration-form').innerHTML = `
                <div class="text-center my-5">
                    <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                    <h4>Đăng ký thành công!</h4>
                    <p class="lead">Cảm ơn bạn đã đăng ký tham dự hội nghị.</p>
                    <div class="mt-4">
                        <a href="conference-detail.html?id=${conferenceId}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại thông tin hội nghị
                        </a>
                        <a href="conferences.html" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>Xem tất cả hội nghị
                        </a>
                    </div>
                </div>
            `;
            
            // Sau 3 giây chuyển hướng về trang chi tiết hội nghị
            setTimeout(() => {
                window.location.href = `conference-detail.html?id=${conferenceId}`;
            }, 3000);
        } else {
            // Đăng ký thất bại
            showMessage(result.message || 'Có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại sau.', 'error');
            
            // Enable lại nút đăng ký
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Xác nhận đăng ký';
        }
    } catch (error) {
        showMessage('Có lỗi xảy ra trong quá trình đăng ký: ' + error.message, 'error');
        
        // Enable lại nút đăng ký
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Xác nhận đăng ký';
    }
}

/**
 * Format ngày tháng
 */
function formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Lấy thông tin người dùng từ local storage
 */
function getUserData() {
    const userDataString = localStorage.getItem('userData');
    return userDataString ? JSON.parse(userDataString) : null;
}
