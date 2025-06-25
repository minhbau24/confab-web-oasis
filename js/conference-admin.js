// Conference Admin page JavaScript logic
console.log('=== conference-admin.js đã được load! ===');

let currentConference = null;

// Helper function for toasts
function showToast(message, type = 'info') {
    // Tạo một toast notification đơn giản
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `alert alert-${type === 'success' ? 'success' : type === 'danger' ? 'danger' : 'info'} alert-dismissible fade show`;
    toast.style.cssText = 'position: relative; margin-bottom: 10px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (document.getElementById(toastId)) {
            document.getElementById(toastId).remove();
        }
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 1050; max-width: 300px;';
    document.body.appendChild(container);
    return container;
}

// ===== Thêm: Load venue list khi mở modal chỉnh sửa =====
function loadVenueList(selectedVenueId = null) {
    console.log('loadVenueList được gọi với selectedVenueId:', selectedVenueId);
    fetch('api/venue.php')
        .then(res => res.json())
        .then(data => {
            console.log('Venue API response:', data);
            if (data.status && data.data && data.data.items) {
                const venues = data.data.items;
                const select = document.getElementById('conference-venue-input');
                if (!select) {
                    console.log('Không tìm thấy conference-venue-input select');
                    return;
                }
                select.innerHTML = '<option value="">Chọn địa điểm...</option>';
                venues.forEach(venue => {
                    const option = document.createElement('option');
                    option.value = venue.id;
                    option.textContent = venue.name;
                    if (selectedVenueId && selectedVenueId == venue.id) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
                console.log('Đã load', venues.length, 'venues vào dropdown');
            } else {
                console.log('Venue API không trả về dữ liệu hợp lệ');
            }
        })
        .catch(err => {
            console.error('Error loading venues:', err);
            const select = document.getElementById('conference-venue-input');
            if (select) {
                select.innerHTML = '<option value="">Không thể tải danh sách địa điểm</option>';
            }
        });
}

// ===== Thêm: Load category list khi mở modal chỉnh sửa =====
function loadCategoryList(selectedCategoryId = null) {
    console.log('loadCategoryList được gọi với selectedCategoryId:', selectedCategoryId);
    fetch('api/category.php')
        .then(res => res.json())
        .then(data => {
            console.log('Category API response:', data);
            if (data.status && data.data) {
                // Category API trả về data trực tiếp là array, không phải {items: [...]}
                const categories = Array.isArray(data.data) ? data.data : [];
                const select = document.getElementById('conference-category-input');
                if (!select) {
                    console.log('Không tìm thấy conference-category-input select');
                    return;
                }
                select.innerHTML = '<option value="">Chọn danh mục...</option>';
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    if (selectedCategoryId && selectedCategoryId == category.id) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
                console.log('Đã load', categories.length, 'categories vào dropdown');
            } else {
                console.log('Category API không trả về dữ liệu hợp lệ');
            }
        })
        .catch(err => {
            console.error('Error loading categories:', err);
        });
}

// Test đơn giản trước
console.log('=== Test 1: Script executing ===');
console.log('=== Test 1.1: document.readyState =', document.readyState);

// Kiểm tra xem DOM đã sẵn sàng chưa
if (document.readyState === 'loading') {
    console.log('=== DOM đang loading, đợi DOMContentLoaded ===');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== Test 2: DOMContentLoaded fired! ===');
        initApp();
    });
} else {
    console.log('=== DOM đã sẵn sàng, chạy ngay ===');
    initApp();
}

function initApp() {
    console.log('=== initApp được gọi ===');
    const urlParams = new URLSearchParams(window.location.search);
    console.log('=== Test 3: URL params:', urlParams.toString());
    
    try {
        initializeConferenceAdmin();
        console.log('=== initializeConferenceAdmin thành công ===');
    } catch (error) {
        console.error('=== Lỗi trong initializeConferenceAdmin:', error);
    }
    
    try {
        setupEventListeners();
        console.log('=== Test 4: Đã gọi setupEventListeners thành công ===');
    } catch (error) {
        console.error('=== Lỗi trong setupEventListeners:', error);
    }
    
    // Sau khi init xong, gọi các tab init functions
    try {
        if (typeof initializeOverviewTab === 'function') {
            initializeOverviewTab();
            console.log('=== Overview tab initialized ===');
        }
        if (typeof setupOverviewEventListeners === 'function') {
            setupOverviewEventListeners();
            console.log('=== Overview event listeners setup ===');
        }
    } catch (error) {
        console.error('=== Lỗi khi init overview tab:', error);
    }
    
    // Set flag để các file khác biết đã init xong
    window.conferenceAdminInitialized = true;
}

// Backup - nếu các cách trên không chạy
setTimeout(function() {
    console.log('=== BACKUP: Timeout fallback chạy ===');
    console.log('=== BACKUP: document.readyState =', document.readyState);
    console.log('=== BACKUP: window.conferenceAdminInitialized =', window.conferenceAdminInitialized);
    
    // Nếu sau 2 giây mà chưa chạy, thử force chạy
    if (!window.conferenceAdminInitialized) {
        console.log('=== BACKUP: Force khởi tạo ===');
        try {
            // Thử gọi trực tiếp các hàm
            initializeConferenceAdmin();
            setupEventListeners();
            window.conferenceAdminInitialized = true;
            console.log('=== BACKUP: Khởi tạo thành công ===');
        } catch (error) {
            console.error('=== BACKUP: Lỗi khi force khởi tạo:', error);
        }
    } else {
        console.log('=== BACKUP: App đã được khởi tạo rồi ===');
    }
}, 2000);

function initializeConferenceAdmin() {
    console.log('=== initializeConferenceAdmin được gọi ===');
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    console.log('=== Conference ID từ URL:', conferenceId, '===');
    
    if (!conferenceId) {
        console.log('=== Không có conference ID - chuyển hướng ===');
        showToast('Không tìm thấy ID hội nghị. Chuyển hướng về trang quản trị...', 'warning');
        setTimeout(() => {
            window.location.href = 'admin.html';
        }, 2000);
        return;    }
    
    console.log('=== BẮT ĐẦU GỌI API conference_by_id.php với ID:', conferenceId, '===');
    // Gọi API để lấy dữ liệu hội nghị
    fetch('api/conference_by_id.php?id=' + conferenceId)
        .then(res => {
            console.log('=== API Response status:', res.status, '===');
            return res.json();
        })
        .then(data => {
            console.log('=== API Response data:', data, '===');
            if (data.status && data.data) {
                currentConference = data.data;
                console.log('=== ĐÃ SET currentConference:', currentConference, '===');
                // Render lại thông tin hội nghị nếu có hàm
                if (typeof renderConferenceInfo === 'function') {
                    renderConferenceInfo(currentConference);
                    console.log('=== ĐÃ RENDER CONFERENCE INFO ===');
                } else {
                    console.log('=== KHÔNG TÌM THẤY HÀM renderConferenceInfo ===');
                }
            } else {
                console.log('=== API KHÔNG TRẢ VỀ DỮ LIỆU HỢP LỆ ===');
                showToast('Không tìm thấy hội nghị. Chuyển hướng về trang quản trị...', 'danger');
                setTimeout(() => {
                    window.location.href = 'admin.html';
                }, 2000);
            }
        })
        .catch((error) => {
            console.error('=== LỖI KHI GỌI API:', error, '===');
            showToast('Không thể kết nối máy chủ!', 'danger');
            setTimeout(() => {
                window.location.href = 'admin.html';
            }, 2000);
        });
    // Không gọi getConferenceById giả lập nữa
    // loadAttendeesTable(); // Nếu cần, gọi lại sau khi currentConference đã có
}

function setupEventListeners() {
    console.log('setupEventListeners được gọi!');
    
    // Đảm bảo không reload trang khi submit form chỉnh sửa hội nghị
    const editForm = document.getElementById('edit-conference-form');
    console.log('editForm:', editForm);
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveConferenceChanges();
        });
    }
      // Pre-populate edit modal when it opens
    const editConferenceModal = document.getElementById('editConferenceModal');
    console.log('editConferenceModal:', editConferenceModal);
    if (editConferenceModal) {
        console.log('Đang gán event listener cho modal...');        editConferenceModal.addEventListener('show.bs.modal', function () {
            console.log('=== MODAL SHOW EVENT TRIGGERED ===');
            console.log('Modal mở, currentConference:', currentConference);
            if (!currentConference) {
                console.log('=== KHÔNG CÓ currentConference ===');
                showToast('Chưa có dữ liệu hội nghị để chỉnh sửa!', 'warning');
                return;
            }
            
            console.log('=== BẮT ĐẦU LOAD VENUE VÀ CATEGORY ===');
            // Load venue list và category list trước
            loadVenueList(currentConference.venue_id || null);
            loadCategoryList(currentConference.category_id || null);
            
            // Delay để đảm bảo dropdowns đã được populate
            setTimeout(() => {
                console.log('Đang điền dữ liệu vào form...');
                document.getElementById('conference-title-input').value = currentConference.title || '';
                document.getElementById('conference-type-input').value = currentConference.type || 'onsite';
                document.getElementById('conference-format-input').value = currentConference.format || 'workshop';
                document.getElementById('conference-currency-input').value = currentConference.currency || 'VND';
                document.getElementById('conference-registration-enabled-input').checked = !!currentConference.registration_enabled;
                document.getElementById('conference-date-input').value = currentConference.start_date ? currentConference.start_date.substring(0,10) : '';
                document.getElementById('conference-end-date-input').value = currentConference.end_date ? currentConference.end_date.substring(0,10) : '';
                document.getElementById('conference-status-input').value = currentConference.status || 'active';
                document.getElementById('conference-price-input').value = currentConference.price || 0;
                document.getElementById('conference-capacity-input').value = currentConference.capacity || 1;
                document.getElementById('conference-description-input').value = currentConference.description || '';
                console.log('Đã điền xong dữ liệu vào form');
            }, 300); // Tăng delay để chắc chắn dropdowns đã load xong
        });
        console.log('Đã gán xong event listener cho modal');
    } else {
        console.log('KHÔNG TÌM THẤY editConferenceModal!');
    }
}

function toggleConferenceStatus() {
    const newStatus = currentConference.status === 'active' ? 'archived' : 'active';
    const actionText = newStatus === 'archived' ? 'lưu trữ' : 'kích hoạt';
    
    if (confirm(`Bạn có chắc chắn muốn ${actionText} hội nghị này không?`)) {
        currentConference.status = newStatus;
        const statusMessage = newStatus === 'archived' ? 'đã được lưu trữ' : 'đã được kích hoạt';
        showToast(`Hội nghị ${statusMessage}!`, 'success');
    }
}

function saveConferenceChanges() {
    // Thu thập dữ liệu từ form/modal mới (chỉ các trường cần thiết)
    const title = document.getElementById('conference-title-input').value || '';
    const venue_id = document.getElementById('conference-venue-input').value || '';
    const type = document.getElementById('conference-type-input').value || 'onsite';
    const format = document.getElementById('conference-format-input').value || 'workshop';
    const currency = document.getElementById('conference-currency-input').value || 'VND';
    const registration_enabled = document.getElementById('conference-registration-enabled-input').checked ? 1 : 0;
    const date = document.getElementById('conference-date-input').value || '';
    const endDate = document.getElementById('conference-end-date-input').value || '';    const category_id = document.getElementById('conference-category-input').value || '';
    const status = document.getElementById('conference-status-input').value || 'active';
    const price = parseFloat(document.getElementById('conference-price-input').value) || 0;
    const capacity = parseInt(document.getElementById('conference-capacity-input').value) || 1;
    const description = document.getElementById('conference-description-input').value || '';
    
    // Validate form
    if (!title || !category_id || !date || !venue_id || price < 0 || capacity < 1 || !description) {
        showToast('Vui lòng điền đầy đủ thông tin bắt buộc!', 'danger');
        return;
    }

    // Chuẩn bị payload (chỉ các trường cần thiết)
    const payload = {
        id: currentConference.id,
        title,
        slug: title.toLowerCase().replace(/\s+/g, '-'),
        description,
        short_description: description.substring(0, 150) + '...',
        start_date: date,
        end_date: endDate || date,
        category_id,
        venue_id,
        type,
        format,
        price,
        currency,
        capacity,
        status,
        registration_enabled
    };

    // Gửi AJAX tới API update_conference.php
    fetch('api/update_conference.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            showToast('Thông tin hội nghị đã được cập nhật thành công!', 'success');
            // Cập nhật lại currentConference với dữ liệu mới nếu backend trả về
            if (data.data) {
                currentConference = data.data;
            } else {
                Object.assign(currentConference, payload);
            }
            // Render lại thông tin hội nghị (không reload trang)
            if (typeof renderConferenceInfo === 'function') {
                renderConferenceInfo(currentConference);
            }
            // Đóng modal nếu đang mở
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editConferenceModal'));
            if (modal) modal.hide();
        } else {
            showToast(data.message || 'Cập nhật thất bại!', 'danger');
        }
    })
    .catch(() => {
        showToast('Có lỗi khi kết nối máy chủ!', 'danger');
    });
}

// ===== Hàm render thông tin hội nghị =====
function renderConferenceInfo(conference) {
    if (!conference) return;
    
    // Cập nhật tiêu đề chính
    const titleElement = document.getElementById('conference-title');
    if (titleElement) {
        titleElement.textContent = conference.title || 'Hội nghị';
    }
    
    // Cập nhật các thống kê nếu có
    if (conference.current_attendees !== undefined) {
        const attendeesElement = document.getElementById('total-attendees');
        if (attendeesElement) {
            attendeesElement.textContent = conference.current_attendees || '0';
        }
    }
    
    if (conference.price !== undefined) {
        const revenueElement = document.getElementById('total-revenue');
        if (revenueElement) {
            const revenue = (conference.current_attendees || 0) * (conference.price || 0);
            revenueElement.textContent = new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: conference.currency || 'VND'
            }).format(revenue);
        }
    }
    
    if (conference.capacity !== undefined && conference.current_attendees !== undefined) {
        const fillRateElement = document.getElementById('fill-rate');
        if (fillRateElement) {
            const fillRate = conference.capacity > 0 ? 
                Math.round((conference.current_attendees / conference.capacity) * 100) : 0;
            fillRateElement.textContent = fillRate + '%';
        }
    }
    
    if (conference.start_date) {
        const daysLeftElement = document.getElementById('days-left');
        if (daysLeftElement) {
            const today = new Date();
            const conferenceDate = new Date(conference.start_date);
            const diffTime = conferenceDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            daysLeftElement.textContent = diffDays > 0 ? diffDays : '0';
        }
    }
}

// ====== DUMMY/PLACEHOLDER FUNCTIONS FOR ATTENDEES TAB ======
// Nếu đã tách sang file nhỏ, hãy xóa các hàm này và đảm bảo import đúng ở file nhỏ. Nếu chưa, giữ lại giả lập để tránh lỗi.

// XÓA: getConferenceById, initializeAttendeesData, loadAttendeesTable, filterAttendees nếu đã tách file nhỏ
