// Quản lý tab Tổng quan (Overview)
// Thêm code xử lý cho tab Tổng quan ở đây

// --- Các hàm dùng chung cho nhiều tab (ví dụ: showToast, translateCategory) ---
function showToast(message, type = 'info') {
    alert(message);
}

function translateCategory(category) {
    const translations = {
        'Technology': 'Công nghệ',
        'Business': 'Kinh doanh',
        'Marketing': 'Marketing',
        'Design': 'Thiết kế'
    };
    return translations[category] || category;
}

// --- Khởi tạo trang Conference Admin và các sự kiện ---
document.addEventListener('DOMContentLoaded', function () {
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
    // Không dùng window.currentConference từ data.js nữa
    // Chỉ khởi tạo attendeesData nếu cần, không fetch conference tĩnh
    initializeAttendeesData();
    loadConferenceInfo();
    loadSummaryStats();
    loadAttendeesTable();
}

function setupEventListeners() {
    const searchInput = document.getElementById('attendee-search');
    if (searchInput) {
        searchInput.addEventListener('input', filterAttendees);
    }
}

// --- Quản lý thông tin hội nghị (Overview tab) ---
async function loadConferenceInfo() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await fetch(`api/conference_by_id.php?id=${conferenceId}`);
        const data = await res.json();
        console.log('Loading conference info for ID:', conferenceId);
        console.log('Conference data:', data);
        // Sửa lại để lấy đúng cấu trúc mới: data.data là object hội nghị
        const conf = data.data;
        if (data && data.status && conf) {
            console.log('Conference data loaded:', conf);
            // Render tiêu đề và các trường ngoài trang
            document.getElementById('conference-title').textContent = conf.title;
            document.title = `ConferenceHub - ${conf.title} Admin`;
            const detailsCard = document.getElementById('conference-details-card');
            if (detailsCard) {
                const dateFormatted = new Date(conf.start_date).toLocaleDateString('vi-VN', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                });
                detailsCard.innerHTML = `
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted mb-1">Status</h6>
                                        <span class="badge bg-${conf.status === 'active' ? 'success' : 'secondary'} fs-6">
                                            ${conf.status === 'active' ? 'Đang hoạt động' : 'Đã lưu trữ'}
                                        </span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted mb-1">Danh mục</h6>
                                        <span class="badge bg-primary fs-6">${translateCategory(conf.category || '')}</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted mb-1"><i class="fas fa-calendar me-1"></i>Ngày</h6>
                                        <p class="mb-0">${dateFormatted}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i>Địa điểm</h6>
                                        <p class="mb-0">${conf.location || ''}</p>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1">Mô tả</h6>
                                    <p class="mb-0">${conf.description || ''}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <img src="${conf.image || ''}" class="img-fluid rounded" alt="${conf.title}" style="max-height: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            // Lưu lại conference hiện tại cho các hàm khác dùng
            window.currentConference = conf;
            // Sau khi load xong, cập nhật lại các thống kê
            loadSummaryStats();
        }
    } catch (e) {
        showToast('Không thể tải thông tin hội nghị!', 'danger');
    }
}

function loadSummaryStats() {
    // Lấy dữ liệu từ window.currentConference (đã đồng bộ trường)
    if (!window.currentConference) return;
    // Số người tham dự
    const attendeeCount = Array.isArray(window.attendeesData) ? window.attendeesData.length : 0;
    // Tổng doanh thu
    const totalRevenue = attendeeCount * (window.currentConference.price || 0);
    // Tỷ lệ lấp đầy
    const capacity = window.currentConference.capacity || 1;
    const fillRate = Math.round((attendeeCount / capacity) * 100);
    // Số ngày còn lại
    const eventDate = new Date(window.currentConference.start_date);
    const today = new Date();
    const daysLeft = Math.ceil((eventDate - today) / (1000 * 3600 * 24));
    // Render ra Overview
    document.getElementById('total-attendees').textContent = attendeeCount.toLocaleString('vi-VN');
    document.getElementById('total-revenue').textContent = totalRevenue.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    document.getElementById('fill-rate').textContent = fillRate + '%';
    document.getElementById('days-left').textContent = daysLeft > 0 ? daysLeft : 'Sự kiện đã qua';
}

// --- Render biểu đồ cho Overview tab ---
function renderOverviewCharts(attendees) {
    // Xu hướng đăng ký
    const trends = {};
    attendees.forEach(a => {
        const date = a.registration_date ? a.registration_date.split(' ')[0] : 'N/A';
        trends[date] = (trends[date] || 0) + 1;
    });
    const trendLabels = Object.keys(trends).sort();
    const trendData = trendLabels.map(date => trends[date]);
    const ctxTrend = document.getElementById('registrationTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: { labels: trendLabels, datasets: [{ label: 'Đăng ký', data: trendData, borderColor: '#667eea', fill: false }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
    // Phân loại theo loại vé
    const byTicketType = {};
    attendees.forEach(a => {
        const type = a.ticket_type || 'Không xác định';
        byTicketType[type] = (byTicketType[type] || 0) + 1;
    });
    const ticketLabels = Object.keys(byTicketType);
    const ticketData = ticketLabels.map(type => byTicketType[type]);
    const ctxTicket = document.getElementById('ticketTypeChart').getContext('2d');
    new Chart(ctxTicket, {
        type: 'doughnut',
        data: { labels: ticketLabels, datasets: [{ data: ticketData, backgroundColor: ['#667eea', '#764ba2', '#f6c23e', '#e74a3b', '#36b9cc'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    // Phân loại theo trạng thái
    const byStatus = {};
    attendees.forEach(a => {
        const status = a.status || 'Không xác định';
        byStatus[status] = (byStatus[status] || 0) + 1;
    });
    const statusLabels = Object.keys(byStatus);
    const statusData = statusLabels.map(status => byStatus[status]);
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'pie',
        data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: ['#36b9cc', '#f6c23e', '#e74a3b', '#764ba2', '#667eea'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}

// --- Load attendees và render thống kê/biểu đồ cho Overview ---
async function loadOverviewStatsAndCharts() {
    const conferenceId = getConferenceIdFromUrl();
    const res = await fetch(`api/attendees.php?conference_id=${conferenceId}`);
    const data = await res.json();
    if (data && data.status && Array.isArray(data.data)) {
        window.attendeesData = data.data;
        loadSummaryStats();
        renderOverviewCharts(data.data);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(loadOverviewStatsAndCharts, 500);
});

// --- Lấy conferenceId động từ URL ---
function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

// --- Các hàm xử lý hội nghị (tab Tổng quan) ---
function fetchConferenceInfo() {
    const conferenceId = getConferenceIdFromUrl();
    fetch(`api/conference_by_id.php?id=${conferenceId}`)
        .then(res => res.json())
        .then(data => {
            // Lấy đúng cấu trúc mới: data.data là object hội nghị
            const conf = data.data;
            if (data && data.status && conf) {
                const nameInput = document.getElementById('conference-name');
                if (nameInput) nameInput.value = conf.title || '';
                const dateInput = document.getElementById('conference-date');
                if (dateInput) dateInput.value = conf.start_date || '';
                const descInput = document.getElementById('conference-description');
                if (descInput) descInput.value = conf.description || '';
                const statusInput = document.getElementById('conference-status');
                if (statusInput) statusInput.value = conf.status || 'active';
                // Nếu có các trường khác (category_id, location, image, capacity, price, ...) thì cập nhật tương tự
                const catInput = document.getElementById('conference-category');
                if (catInput) catInput.value = conf.category_id || '';
                const locInput = document.getElementById('conference-location');
                if (locInput) locInput.value = conf.location || '';
                const imgInput = document.getElementById('conference-image');
                if (imgInput) imgInput.value = conf.image || '';
                const capInput = document.getElementById('conference-capacity');
                if (capInput) capInput.value = conf.capacity || '';
                const priceInput = document.getElementById('conference-price');
                if (priceInput) priceInput.value = conf.price || '';
            }
        });
}

// Đổi tên biến modal để tránh trùng lặp toàn cục
if (!window.editConferenceModal) {
    window.editConferenceModal = document.getElementById('editConferenceModal');
    if (window.editConferenceModal) {
        window.editConferenceModal.addEventListener('show.bs.modal', fetchConferenceInfo);
    }
}

function saveConferenceChanges() {
    const conferenceId = getConferenceIdFromUrl();
    // Lấy đúng các trường theo schema mới
    const title = document.getElementById('conference-name').value;
    const start_date = document.getElementById('conference-date').value;
    const description = document.getElementById('conference-description').value;
    const status = document.getElementById('conference-status').value;
    // Lấy thêm các trường khác nếu có
    const category_id = document.getElementById('conference-category') ? document.getElementById('conference-category').value : undefined;
    const location = document.getElementById('conference-location') ? document.getElementById('conference-location').value : undefined;
    const image = document.getElementById('conference-image') ? document.getElementById('conference-image').value : undefined;
    const capacity = document.getElementById('conference-capacity') ? document.getElementById('conference-capacity').value : undefined;
    const price = document.getElementById('conference-price') ? document.getElementById('conference-price').value : undefined;
    // Tạo payload đúng schema
    const payload = { id: conferenceId, title, start_date, description, status };
    if (category_id !== undefined) payload.category_id = category_id;
    if (location !== undefined) payload.location = location;
    if (image !== undefined) payload.image = image;
    if (capacity !== undefined) payload.capacity = capacity;
    if (price !== undefined) payload.price = price;
    fetch('api/update_conference.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(data => {
            if (data && data.status) {
                // Cập nhật lại tiêu đề và trạng thái ngoài trang
                document.getElementById('conference-title').textContent = title;
                // Reload lại thông tin hội nghị để đồng bộ ngoài trang
                loadConferenceInfo();
                showToast('Cập nhật thành công!', 'success');
                // Đóng modal
                const modal = bootstrap.Modal.getInstance(window.editConferenceModal);
                modal.hide();
            } else {
                showToast('Có lỗi khi cập nhật hội nghị!', 'danger');
            }
        });
}

function toggleConferenceStatus() {
    const conferenceId = getConferenceIdFromUrl();
    fetch(`api/conference_by_id.php?id=${conferenceId}`)
        .then(res => res.json())
        .then(data => {
            // Sửa lại để lấy đúng cấu trúc mới: data.data là object hội nghị
            if (data && data.status && data.data) {
                const currentStatus = data.data.status;
                const newStatus = currentStatus === 'archived' ? 'active' : 'archived';
                fetch('api/update_conference.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: conferenceId, status: newStatus })
                })
                    .then(res => res.json())
                    .then(result => {
                        if (result && result.status) {
                            showToast('Đã cập nhật trạng thái hội nghị!', 'success');
                            loadConferenceInfo();
                        } else {
                            showToast('Có lỗi khi cập nhật trạng thái!', 'danger');
                        }
                    });
            }
        });
}
