// Conference Admin page JavaScript logic

let currentConference = null;

// Helper function for toasts
function showToast(message, type = 'info') {
    // Simple toast implementation if not provided elsewhere
    alert(message);
}

// ===== Thêm: Load venue list khi mở modal chỉnh sửa =====
function loadVenueList(selectedVenueId = null) {
    fetch('api/venue.php')
        .then(res => res.json())
        .then(data => {
            if (data.status && data.data && data.data.items) {
                const venues = data.data.items;
                const select = document.getElementById('conference-venue-input');
                if (!select) return;
                select.innerHTML = '';
                venues.forEach(venue => {
                    const option = document.createElement('option');
                    option.value = venue.id;
                    option.textContent = venue.name;
                    if (selectedVenueId && selectedVenueId == venue.id) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    // Không dùng localStorage, chỉ lấy id từ URL
    initializeConferenceAdmin();
    setupEventListeners();
});

function initializeConferenceAdmin() {
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    if (!conferenceId) {
        showToast('Không tìm thấy ID hội nghị. Chuyển hướng về trang quản trị...', 'warning');
        setTimeout(() => {
            window.location.href = 'admin.html';
        }, 2000);
        return;
    }
    // Gọi API để lấy dữ liệu hội nghị
    fetch('api/conference_detail.php?id=' + conferenceId)
        .then(res => res.json())
        .then(data => {
            if (data.status && data.data && data.data.conference) {
                currentConference = data.data.conference;
                // Render lại thông tin hội nghị nếu có hàm
                if (typeof renderConferenceInfo === 'function') {
                    renderConferenceInfo(currentConference);
                }
            } else {
                showToast('Không tìm thấy hội nghị. Chuyển hướng về trang quản trị...', 'danger');
                setTimeout(() => {
                    window.location.href = 'admin.html';
                }, 2000);
            }
        })
        .catch(() => {
            showToast('Không thể kết nối máy chủ!', 'danger');
            setTimeout(() => {
                window.location.href = 'admin.html';
            }, 2000);
        });
    // Không gọi getConferenceById giả lập nữa
    // loadAttendeesTable(); // Nếu cần, gọi lại sau khi currentConference đã có
}

function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('attendee-search');
    if (searchInput) {
        searchInput.addEventListener('input', filterAttendees);
    }
}

// Đảm bảo không reload trang khi submit form chỉnh sửa hội nghị
const editForm = document.getElementById('edit-conference-form');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        saveConferenceChanges();
    });
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
    const endDate = document.getElementById('conference-end-date-input').value || '';
    const category_id = document.getElementById('conference-category-input').value || '';
    const status = document.getElementById('conference-status-input').value || 'active';
    const price = parseFloat(document.getElementById('conference-price-input').value) || 0;
    const capacity = parseInt(document.getElementById('conference-capacity-input').value) || 1;
    const description = document.getElementById('conference-description-input').value || '';

    // Validate form
    if (!title || !category_id || !date || !venue_id || !price || !capacity || !description) {
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

// Helper function to translate category names
function translateCategory(category) {
    const translations = {
        'Technology': 'Công nghệ',
        'Business': 'Kinh doanh',
        'Marketing': 'Marketing',
        'Design': 'Thiết kế'
    };
    return translations[category] || category;
}

// Pre-populate edit modal when it opens
const editConferenceModal = document.getElementById('editConferenceModal');
if (editConferenceModal) {
    editConferenceModal.addEventListener('show.bs.modal', function () {
        if (!currentConference) return;
        // Load venue list và chọn venue hiện tại, sau đó set lại các trường khác sau khi venue đã render xong
        loadVenueList(currentConference.venue_id || null);
        setTimeout(() => {
            document.getElementById('conference-title-input').value = currentConference.title || '';
            document.getElementById('conference-type-input').value = currentConference.type || 'onsite';
            document.getElementById('conference-format-input').value = currentConference.format || 'workshop';
            document.getElementById('conference-currency-input').value = currentConference.currency || 'VND';
            document.getElementById('conference-registration-enabled-input').checked = !!currentConference.registration_enabled;
            document.getElementById('conference-date-input').value = currentConference.start_date ? currentConference.start_date.substring(0,10) : '';
            document.getElementById('conference-end-date-input').value = currentConference.end_date ? currentConference.end_date.substring(0,10) : '';
            document.getElementById('conference-category-input').value = currentConference.category || currentConference.category_id || '';
            document.getElementById('conference-status-input').value = currentConference.status || 'active';
            document.getElementById('conference-price-input').value = currentConference.price || 0;
            document.getElementById('conference-capacity-input').value = currentConference.capacity || 1;
            document.getElementById('conference-description-input').value = currentConference.description || '';
        }, 200);
    });
}

// ====== DUMMY/PLACEHOLDER FUNCTIONS FOR ATTENDEES TAB ======
// Nếu đã tách sang file nhỏ, hãy xóa các hàm này và đảm bảo import đúng ở file nhỏ. Nếu chưa, giữ lại giả lập để tránh lỗi.

// XÓA: getConferenceById, initializeAttendeesData, loadAttendeesTable, filterAttendees nếu đã tách file nhỏ
