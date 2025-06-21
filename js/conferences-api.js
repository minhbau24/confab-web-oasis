// Conferences page JavaScript logic with API integration

let allConferences = [];
let filteredConferences = [];
let userRegisteredConferences = []; // Danh sách conference IDs mà user đã đăng ký

document.addEventListener('DOMContentLoaded', function() {
    initializeConferencesPage();
    setupEventListeners();
});

async function initializeConferencesPage() {
    try {
        // Load both conferences and user registrations
        await Promise.all([
            fetchConferencesFromAPI(),
            fetchUserRegistrations()
        ]);
        renderConferences();
    } catch (error) {
        console.error('Error initializing conferences page:', error);
        showErrorMessage('Không thể tải dữ liệu hội nghị. Vui lòng thử lại sau.');
    }
}

async function fetchConferencesFromAPI() {
    try {
        // Show loading indicator
        document.getElementById('conferences-grid').innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-3">Đang tải dữ liệu hội nghị...</p>
            </div>
        `;

        // Call the API endpoint
        const response = await fetch('api/conferences.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status && result.data) {
            allConferences = result.data;
            filteredConferences = [...allConferences];
        } else {
            throw new Error(result.message || 'Không có dữ liệu trả về');
        }
    } catch (error) {
        console.error('Error fetching conferences:', error);
        throw error;
    }
}

// Function để lấy danh sách hội nghị mà user đã đăng ký
async function fetchUserRegistrations() {
    try {
        const user = getCurrentUser();
        if (!user || !user.id) {
            console.log("User not logged in, skipping registration fetch");
            userRegisteredConferences = [];
            return;
        }
        
        console.log("Fetching user registrations...");
        const response = await fetch('api/user_registrations.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status && result.data) {
            userRegisteredConferences = result.data.conference_ids || [];
            console.log("User registered conferences:", userRegisteredConferences);
        } else {
            console.warn("Failed to fetch user registrations:", result.message);
            userRegisteredConferences = [];
        }
    } catch (error) {
        console.error('Error fetching user registrations:', error);
        userRegisteredConferences = [];
    }
}

function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const locationFilter = document.getElementById('locationFilter');

    // Search functionality
    searchInput.addEventListener('input', filterConferences);
    categoryFilter.addEventListener('change', filterConferences);
    locationFilter.addEventListener('change', filterConferences);
}

function filterConferences() {
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const location = document.getElementById('locationFilter').value;

    // Apply filters
    filteredConferences = allConferences.filter(conference => {
        // Search query filter
        const matchesSearch = searchQuery === '' || 
                             conference.title.toLowerCase().includes(searchQuery) || 
                             conference.description.toLowerCase().includes(searchQuery) ||
                             conference.location.toLowerCase().includes(searchQuery);
        
        // Category filter
        const matchesCategory = category === '' || conference.category === category;
        
        // Location filter
        const matchesLocation = location === '' || conference.location.includes(location);
        
        return matchesSearch && matchesCategory && matchesLocation;
    });

    renderConferences();
}

function renderConferences() {
    const container = document.getElementById('conferences-grid');
    const noResultsElement = document.getElementById('no-results');
    
    if (filteredConferences.length > 0) {
        container.innerHTML = filteredConferences.map(conference => {
            // Calculate progress of registrations
            const progress = Math.min(100, Math.round((conference.attendees / conference.capacity) * 100));
            
            // Format date
            const date = new Date(conference.date);
            const formattedDate = date.toLocaleDateString('vi-VN', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });            // Đảm bảo luôn có hình ảnh cho hội nghị
            const imageUrl = conference.image || 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop';
            
            // Kiểm tra xem user đã đăng ký hội nghị này chưa
            const user = getCurrentUser();
            const isRegistered = checkIfUserRegistered(conference.id, user);
            
            // Tạo nút đăng ký dựa trên trạng thái
            const registerButton = isRegistered ? 
                `<button class="btn btn-secondary register-btn" data-id="${conference.id}" disabled title="Bạn đã đăng ký tham dự hội nghị này">
                    <i class="fas fa-check me-1"></i>Đã đăng ký
                </button>` :
                `<button class="btn btn-success register-btn" data-id="${conference.id}">
                    <i class="fas fa-check-circle me-1"></i>Đăng ký
                </button>`;
            
            return `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card conference-card shadow-sm h-100">
                        <img src="${imageUrl}" class="card-img-top" alt="${conference.title}" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-primary mb-2 align-self-start">${conference.category}</span>
                            <h5 class="card-title">${conference.title}</h5>
                            <p class="card-text">${conference.description.substring(0, 100)}...</p>
                            <div class="mt-auto">
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>${conference.location}
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>${formattedDate}
                                </div>
                                <div class="mb-3">
                                    <p class="mb-1 small">Số người tham dự: ${conference.attendees}/${conference.capacity}</p>
                                    <div class="progress">
                                        <div class="progress-bar ${progress >= 90 ? 'bg-danger' : 'bg-success'}" 
                                            role="progressbar" 
                                            style="width: ${progress}%" 
                                            aria-valuenow="${progress}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="conference-detail.html?id=${conference.id}" class="btn btn-outline-primary">
                                        <i class="fas fa-info-circle me-1"></i>Chi tiết
                                    </a>
                                    ${registerButton}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
          // Add event listeners for register buttons
        document.querySelectorAll('.register-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Không cho phép đăng ký nếu nút đã disabled
                if (this.disabled) {
                    return;
                }
                registerForConference(this.dataset.id);
            });
        });
        
        noResultsElement.style.display = 'none';
    } else {
        container.innerHTML = '';
        noResultsElement.style.display = 'block';
    }
}

async function registerForConference(conferenceId) {
    console.log("=== Starting conference registration ===");
    console.log("Conference ID:", conferenceId);
    
    // Check if user is logged in
    const user = getCurrentUser();
    console.log("Current user:", user);
    
    if (!user || !user.id) {
        console.warn("User not logged in or no user ID");
        showToast('Vui lòng đăng nhập để đăng ký tham gia hội nghị', 'warning');
        
        // Debug authentication state
        if (typeof window.debugAuthState === 'function') {
            console.log("Running debug auth state...");
            window.debugAuthState();
        }
        
        setTimeout(() => {
            window.location.href = 'login.html?redirect=conferences.html';
        }, 2000);
        return;
    }
    
    // Hiển thị lựa chọn: đăng ký nhanh hoặc điền form đầy đủ
    if (confirm('Bạn muốn điền đầy đủ thông tin đăng ký (nhấn OK) hay đăng ký nhanh (nhấn Cancel)?')) {
        // Chuyển hướng đến trang đăng ký đầy đủ
        console.log("Redirecting to full registration form");
        window.location.href = `conference-register.html?id=${conferenceId}`;
        return;
    }
    
    console.log("Proceeding with quick registration");
    
    // Nếu chọn đăng ký nhanh, tiếp tục với API call
    try {
        // Sử dụng FormData thay vì JSON
        const formData = new FormData();
        formData.append('conferenceId', conferenceId);
        formData.append('userId', user.id);
        formData.append('quickRegister', 'true');
        
        console.log("Sending registration request with data:", {
            conferenceId: conferenceId,
            userId: user.id,
            quickRegister: true
        });
          const response = await fetch('api/conference_registration.php', {
            method: 'POST',
            credentials: 'include', // Include cookies for session
            body: formData
        });
        
        console.log("Response status:", response.status);
        console.log("Response headers:", response.headers.get('content-type'));
        
        // Kiểm tra response trước khi parse JSON
        const responseText = await response.text();
        console.log("Raw response:", responseText);
        
        // Cố gắng parse JSON từ response text
        let result;
        try {
            // Nếu response có HTML error trước JSON, cố gắng extract JSON
            const jsonStart = responseText.indexOf('{');
            if (jsonStart > 0) {
                console.warn("Response có HTML error, đang cố gắng extract JSON...");
                const jsonPart = responseText.substring(jsonStart);
                result = JSON.parse(jsonPart);
            } else {
                result = JSON.parse(responseText);
            }
        } catch (parseError) {
            console.error("JSON parse error:", parseError);
            console.error("Response text:", responseText);
            throw new Error('Server trả về response không hợp lệ. Vui lòng thử lại sau.');
        }          if (result.status) {
            showToast(result.message || 'Đăng ký thành công!');
            
            // Thêm conference ID vào danh sách đã đăng ký
            const confId = parseInt(conferenceId);
            if (!userRegisteredConferences.includes(confId)) {
                userRegisteredConferences.push(confId);
            }
            
            // Cập nhật trạng thái nút đăng ký ngay lập tức
            updateRegistrationButton(conferenceId, true);
            
            // Cập nhật số lượng người tham dự trong data
            const conference = allConferences.find(c => c.id == conferenceId);
            if (conference) {
                conference.attendees = parseInt(conference.attendees) + 1;
                console.log(`Cập nhật số người tham dự cho hội nghị ${conferenceId}: ${conference.attendees}`);
            }
            
            // Refresh toàn bộ dữ liệu từ server để đảm bảo đồng bộ
            setTimeout(async () => {
                await Promise.all([
                    fetchConferencesFromAPI(),
                    fetchUserRegistrations()
                ]);
                renderConferences();
            }, 1000); // Delay 1 giây để user thấy được thay đổi tức thì
            
        } else {
            showToast(result.message || 'Đăng ký thất bại!', 'danger');
        }
    } catch (error) {
        console.error('Error registering for conference:', error);
        showToast('Có lỗi xảy ra khi đăng ký. Vui lòng thử lại sau.', 'danger');
    }
}

// Function để cập nhật trạng thái nút đăng ký
function updateRegistrationButton(conferenceId, isRegistered) {
    const button = document.querySelector(`button[data-id="${conferenceId}"]`);
    if (button) {
        if (isRegistered) {
            // Thay đổi thành trạng thái "Đã đăng ký"
            button.innerHTML = '<i class="fas fa-check me-1"></i>Đã đăng ký';
            button.classList.remove('btn-success');
            button.classList.add('btn-secondary');
            button.disabled = true;
            
            // Thêm tooltip
            button.setAttribute('title', 'Bạn đã đăng ký tham dự hội nghị này');
            button.setAttribute('data-bs-toggle', 'tooltip');
        } else {
            // Trở về trạng thái ban đầu
            button.innerHTML = '<i class="fas fa-check-circle me-1"></i>Đăng ký';
            button.classList.remove('btn-secondary');
            button.classList.add('btn-success');
            button.disabled = false;
            
            // Xóa tooltip
            button.removeAttribute('title');
            button.removeAttribute('data-bs-toggle');
        }
        
        // Khởi tạo lại tooltip nếu cần
        if (typeof bootstrap !== 'undefined') {
            const tooltip = bootstrap.Tooltip.getInstance(button);
            if (tooltip) {
                tooltip.dispose();
            }
            if (isRegistered) {
                new bootstrap.Tooltip(button);
            }
        }
    }
}

function getCurrentUser() {
    // Use a simple approach that can't cause recursion
    // Try multiple sources to get the current user
    
    // First, try the exposed global object directly
    if (typeof window.authCurrentUser !== 'undefined' && window.authCurrentUser) {
        // Create a shallow copy to avoid reference issues
        const user = window.authCurrentUser;
        return {
            id: user.id,
            name: user.name,
            email: user.email,
            role: user.role,
            token: user.token,
            firstName: user.firstName,
            lastName: user.lastName,
            phone: user.phone
        };
    }
    
    // Second, try the global function
    if (typeof window.getCurrentUser === 'function') {
        try {
            const user = window.getCurrentUser();
            if (user && user.id) {
                return user;
            }
        } catch (error) {
            console.warn('Error calling window.getCurrentUser:', error);
        }
    }
    
    // Third, try to get from localStorage as fallback
    try {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            if (parsedUser && parsedUser.id) {
                return parsedUser;
            }
        }
    } catch (error) {
        console.warn('Error parsing localStorage user:', error);
    }
    
    // Return default empty user if nothing found
    return { 
        id: null, 
        name: null, 
        email: null, 
        role: 'guest' 
    };
}

function showToast(message, type = 'success') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Thông báo</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-${type} text-white">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Initialize and show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function showErrorMessage(message) {
    const container = document.getElementById('conferences-grid');
    container.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
            <button class="btn btn-primary mt-3" onclick="initializeConferencesPage()">
                <i class="fas fa-sync-alt me-2"></i>Thử lại
            </button>
        </div>
    `;
}

// Function để kiểm tra xem user đã đăng ký hội nghị này chưa
function checkIfUserRegistered(conferenceId, user) {
    if (!user || !user.id) {
        return false;
    }
    
    // Kiểm tra từ database data đã load
    const confId = parseInt(conferenceId);
    const isRegistered = userRegisteredConferences.includes(confId);
      console.log(`Check registration for conference ${confId}:`, isRegistered);
    return isRegistered;
}

// Function đã được xóa vì không cần localStorage nữa, sử dụng database
