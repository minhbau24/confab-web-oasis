// ===================== ANALYTICS MANAGEMENT =====================
// Thống kê xu hướng đăng ký và phân loại người tham dự cho Conference Admin

// Hàm fetch dữ liệu attendees cho hội nghị
async function fetchAttendeesForAnalytics(conferenceId) {
    const res = await fetch(`api/attendees.php?conference_id=${conferenceId}`);
    const data = await res.json();
    if (data && data.status && Array.isArray(data.data)) {
        return data.data;
    }
    return [];
}

// Hàm vẽ biểu đồ xu hướng đăng ký (dựa vào registration_date)
function renderRegistrationTrends(attendees) {
    // Gom nhóm theo ngày đăng ký
    const trends = {};
    attendees.forEach(a => {
        const date = a.registration_date ? a.registration_date.split(' ')[0] : 'N/A';
        trends[date] = (trends[date] || 0) + 1;
    });
    // Chuyển thành mảng để vẽ biểu đồ
    const labels = Object.keys(trends).sort();
    const dataPoints = labels.map(date => trends[date]);
    // Vẽ bằng Chart.js (hoặc render bảng nếu không có Chart.js)
    if (window.Chart && document.getElementById('registrationTrendsChart')) {
        const ctx = document.getElementById('registrationTrendsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số lượt đăng ký',
                    data: dataPoints,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { title: { display: true, text: 'Ngày' } }, y: { title: { display: true, text: 'Số lượt đăng ký' }, beginAtZero: true } }
            }
        });
    } else if (document.getElementById('registrationTrendsTable')) {
        // Nếu không có Chart.js, render bảng đơn giản
        const table = document.getElementById('registrationTrendsTable');
        table.innerHTML = '<tr><th>Ngày</th><th>Số lượt đăng ký</th></tr>' + labels.map(date => `<tr><td>${date}</td><td>${trends[date]}</td></tr>`).join('');
    }
}

// Hàm phân loại người tham dự (theo ticket_type, status, ...)
function renderAttendeeSegmentation(attendees) {
    // Phân loại theo loại vé
    const byTicketType = {};
    attendees.forEach(a => {
        const type = a.ticket_type || 'Không xác định';
        byTicketType[type] = (byTicketType[type] || 0) + 1;
    });
    // Phân loại theo trạng thái
    const byStatus = {};
    attendees.forEach(a => {
        const status = a.status || 'Không xác định';
        byStatus[status] = (byStatus[status] || 0) + 1;
    });
    // Render bảng phân loại
    const ticketTable = document.getElementById('attendeeSegmentationByTicket');
    if (ticketTable) {
        ticketTable.innerHTML = '<tr><th>Loại vé</th><th>Số lượng</th></tr>' + Object.keys(byTicketType).map(type => `<tr><td>${type}</td><td>${byTicketType[type]}</td></tr>`).join('');
    }
    const statusTable = document.getElementById('attendeeSegmentationByStatus');
    if (statusTable) {
        statusTable.innerHTML = '<tr><th>Trạng thái</th><th>Số lượng</th></tr>' + Object.keys(byStatus).map(status => `<tr><td>${status}</td><td>${byStatus[status]}</td></tr>`).join('');
    }
}

// ===================== DEMOGRAPHICS =====================
async function fetchDemographics(conferenceId) {
    const res = await fetch(`api/analytics_demographics.php?conference_id=${conferenceId}`);
    return await res.json();
}

function renderDemographicsChart(data) {
    const ctx = document.getElementById('demographicsChart');
    if (!ctx || !window.Chart) return;
    // Xử lý dữ liệu
    const genderLabels = data.gender.map(g => g.gender || 'Khác');
    const genderCounts = data.gender.map(g => parseInt(g.count));
    const ageLabels = data.age.map(a => a.age_group);
    const ageCounts = data.age.map(a => parseInt(a.count));
    const occupationLabels = data.occupation.map(o => o.occupation || 'Khác');
    const occupationCounts = data.occupation.map(o => parseInt(o.count));
    // Vẽ 3 chart nhỏ trong 1 canvas (doughnut cho gender, bar cho age, bar cho occupation)
    // Đơn giản: chỉ vẽ gender (doughnut) và age (bar) trên cùng 1 canvas, occupation show tooltip hoặc console
    // Xóa chart cũ nếu có
    if (ctx._chartInstance) ctx._chartInstance.destroy();
    ctx._chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: genderLabels,
            datasets: [{
                label: 'Giới tính',
                data: genderCounts,
                backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#888'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Phân bố giới tính' },
                legend: { position: 'bottom' }
            }
        }
    });
    // Có thể vẽ thêm age chart bên dưới nếu muốn (tùy UI)
}

// ===================== REVENUE =====================
async function fetchRevenue(conferenceId) {
    const res = await fetch(`api/analytics_revenue.php?conference_id=${conferenceId}`);
    return await res.json();
}

function renderRevenueChart(data) {
    const ctx = document.getElementById('revenueChart');
    if (!ctx || !window.Chart) return;
    // Dữ liệu doanh thu theo loại vé
    const typeLabels = data.by_type.map(t => t.ticket_type || 'Không xác định');
    const typeRevenue = data.by_type.map(t => parseFloat(t.revenue));
    // Xóa chart cũ nếu có
    if (ctx._chartInstance) ctx._chartInstance.destroy();
    ctx._chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: typeLabels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: typeRevenue,
                backgroundColor: '#36A2EB',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Doanh thu theo loại vé' },
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'VNĐ' } }
            }
        }
    });
}

// ===================== REFERRALS =====================
async function fetchReferrals(conferenceId) {
    const res = await fetch(`api/analytics_referrals.php?conference_id=${conferenceId}`);
    return await res.json();
}

function renderReferralsList(data) {
    const container = document.querySelector('#analytics .list-group');
    if (!container) return;
    container.innerHTML = '';
    let total = 0;
    data.referrals.forEach(r => total += parseInt(r.count));
    data.referrals.forEach(r => {
        const percent = total > 0 ? Math.round((parseInt(r.count) / total) * 100) : 0;
        const div = document.createElement('div');
        div.className = 'list-group-item d-flex justify-content-between';
        div.innerHTML = `<span>${r.referral_source || 'Khác'}</span><span class=\"badge bg-primary\">${percent}%</span>`;
        container.appendChild(div);
    });
}

// Hàm khởi tạo thống kê khi vào tab Thống kê
async function initializeAnalyticsTab() {
    const conferenceId = (new URLSearchParams(window.location.search)).get('id');
    if (!conferenceId) return;
    const attendees = await fetchAttendeesForAnalytics(conferenceId);
    renderRegistrationTrends(attendees);
    renderAttendeeSegmentation(attendees);
    // Fetch & render demographics
    fetchDemographics(conferenceId).then(data => {
        if (data && data.success) renderDemographicsChart(data);
    });
    // Fetch & render revenue
    fetchRevenue(conferenceId).then(data => {
        if (data && data.success) renderRevenueChart(data);
    });
    // Fetch & render referrals
    fetchReferrals(conferenceId).then(data => {
        if (data && data.success) renderReferralsList(data);
    });
}

// Nếu tab Thống kê được load, tự động gọi hàm khởi tạo
if (document.getElementById('registrationTrendsChart') || document.getElementById('registrationTrendsTable')) {
    initializeAnalyticsTab();
}
