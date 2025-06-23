// Conference manager page JavaScript logic

document.addEventListener('DOMContentLoaded', function() {
    fetchAndRenderStats();
    fetchAndRenderConferences();
    // populateConferenceSelect sẽ được gọi sau khi có dữ liệu thật
});

async function fetchAndRenderConferences() {
    const conferencesList = document.getElementById('conferencesList');
    conferencesList.innerHTML = `<div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tải...</span>
        </div>
    </div>`;
    try {
        console.log('[Conference API] Gọi: api/conference_manager.php');
        const res = await fetch('api/conference_manager.php', { credentials: 'include' });
        const text = await res.text();
        console.log('[Conference API] Raw response:', text);
        let result;
        try { result = JSON.parse(text); } catch (err) { console.error('[Conference API] JSON parse error:', err); conferencesList.innerHTML = `<div class='col-12 text-center py-5'><div class='alert alert-danger'>Lỗi parse JSON từ API conference_manager.php</div></div>`; return; }
        if (result.status && Array.isArray(result.data)) {
            renderConferenceList(result.data);
            // populateConferenceSelect(result.data);
            // populateAttendeeConferenceSelect(result.data); // Mới thêm
        } else {
            conferencesList.innerHTML = `<div class='col-12 text-center py-5'><div class='alert alert-danger'>${result.error || 'Không thể tải danh sách hội nghị.'}</div></div>`;
        }
    } catch (e) {
        console.error('[Conference API] Exception:', e);
        conferencesList.innerHTML = `<div class='col-12 text-center py-5'><div class='alert alert-danger'>Lỗi kết nối API conference_manager.php.</div></div>`;
    }
}

function renderConferenceList(conferences) {
    const conferencesList = document.getElementById('conferencesList');
    conferencesList.innerHTML = '';
    if (!conferences || conferences.length === 0) {
        conferencesList.innerHTML = `<div class='col-12 text-center py-5'><div class='alert alert-info'>Bạn chưa có hội nghị nào. Hãy tạo mới!</div></div>`;
        return;
    }
    conferences.forEach(conference => {
        // ...render card như cũ, dùng dữ liệu từ API...
        const today = new Date();
        const startDate = new Date(conference.start_date || conference.date);
        const endDate = conference.end_date ? new Date(conference.end_date) : startDate;
        let statusClass = 'bg-secondary';
        let statusText = 'Draft';
        if (startDate <= today && endDate >= today) {
            statusClass = 'bg-success'; statusText = 'Active';
        } else if (startDate > today) {
            statusClass = 'bg-primary'; statusText = 'Upcoming';
        } else if (endDate < today) {
            statusClass = 'bg-secondary'; statusText = 'Past';
        }
        const attendees = parseInt(conference.attendees || 0);
        const capacity = parseInt(conference.capacity || 1);
        const percentFilled = Math.round(attendees / capacity * 100);
        const card = document.createElement('div');
        card.className = 'col-md-6 col-lg-4 mb-4';
        card.innerHTML = `
            <div class="manager-card card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge ${statusClass} status-badge">${statusText}</span>
                    <div class="action-buttons">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewConference(${conference.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="editConference(${conference.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="showDeleteConfirmation(${conference.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-3">${conference.title}</h5>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-2"></i>${formatDate(conference.start_date || conference.date)}
                            ${conference.end_date ? ' - ' + formatDate(conference.end_date) : ''}
                        </small><br>
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt me-2"></i>${conference.location}
                        </small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${attendees}</strong>/<span class="text-muted">${capacity}</span> 
                            <small class="text-muted">attendees</small>
                        </div>
                        <div>
                            <span class="badge bg-info">${formatCurrency(conference.price)}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                            style="width: ${percentFilled}%"
                            aria-valuenow="${percentFilled}" 
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">${percentFilled}% filled</small>
                        <a href="conference-admin.html?id=${conference.id}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        `;
        conferencesList.appendChild(card);
    });
}

// --- Phân tích thống kê ---
async function fetchAndRenderStats() {
    try {
        // Lấy danh sách hội nghị do user tạo
        const res = await fetch('api/conference_manager.php', { credentials: 'include' }); // Đảm bảo gửi cookie/session
        const data = await res.json();
        if (!data.status) return;
        const conferences = data.data || [];
        // Hội nghị đang hoạt động
        const activeConfs = conferences.filter(c => c.status === 'active');
        document.getElementById('activeConferences').textContent = activeConfs.length;
        // Tổng người tham dự và tổng doanh thu
        let totalAttendees = 0;
        let totalRevenue = 0;
        let totalCapacity = 0;
        let maxFill = -1, minFill = 101;
        let maxFillConf = null, minFillConf = null;
        for (const conf of conferences) {
            const attendees = parseInt(conf.attendees || 0);
            const capacity = parseInt(conf.capacity || 1);
            totalAttendees += attendees;
            totalRevenue += (parseFloat(conf.price || 0) * attendees);
            totalCapacity += capacity;
            const fillRate = (attendees / capacity) * 100;
            if (fillRate > maxFill) { maxFill = fillRate; maxFillConf = conf; }
            if (fillRate < minFill) { minFill = fillRate; minFillConf = conf; }
        }
        document.getElementById('totalAttendees').textContent = totalAttendees;
        document.getElementById('totalRevenue').textContent = totalRevenue.toLocaleString('en-US') + '₫';
        // Tỷ lệ tăng trưởng (ví dụ: so sánh số hội nghị active tháng này với tháng trước)
        const now = new Date();
        const thisMonth = now.getMonth() + 1;
        const thisYear = now.getFullYear();
        const lastMonth = thisMonth === 1 ? 12 : thisMonth - 1;
        const lastMonthYear = thisMonth === 1 ? thisYear - 1 : thisYear;
        const activeThisMonth = activeConfs.filter(c => {
            const d = new Date(c.start_date);
            return d.getMonth() + 1 === thisMonth && d.getFullYear() === thisYear;
        }).length;
        const activeLastMonth = activeConfs.filter(c => {
            const d = new Date(c.start_date);
            return d.getMonth() + 1 === lastMonth && d.getFullYear() === lastMonthYear;
        }).length;
        let growth = 0;
        if (activeLastMonth > 0) {
            growth = ((activeThisMonth - activeLastMonth) / activeLastMonth) * 100;
        } else if (activeThisMonth > 0) {
            growth = 100;
        }
        document.getElementById('growthRate').textContent = Math.round(growth) + '%';

        // --- Performance Analysis ---
        // Tỷ lệ lấp đầy trung bình
        let avgFillRate = totalCapacity > 0 ? (totalAttendees / totalCapacity) * 100 : 0;
        // Doanh thu trung bình trên mỗi hội nghị
        let avgRevenue = conferences.length > 0 ? totalRevenue / conferences.length : 0;
        // Số hội nghị đã kết thúc, đang diễn ra, sắp diễn ra
        let nowDate = new Date();
        let ended = 0, ongoing = 0, upcoming = 0;
        conferences.forEach(conf => {
            const start = new Date(conf.start_date);
            const end = conf.end_date ? new Date(conf.end_date) : start;
            if (end < nowDate) ended++;
            else if (start > nowDate) upcoming++;
            else ongoing++;
        });
        // Render các chỉ số này nếu có chỗ hiển thị (giả sử có các id tương ứng)
        if (document.getElementById('avgFillRate'))
            document.getElementById('avgFillRate').textContent = avgFillRate.toFixed(1) + '%';
        if (document.getElementById('avgRevenue'))
            document.getElementById('avgRevenue').textContent = avgRevenue.toLocaleString('en-US') + '₫';
        if (document.getElementById('maxFillConf'))
            document.getElementById('maxFillConf').textContent = maxFillConf ? `${maxFillConf.title} (${Math.round(maxFill)}%)` : 'N/A';
        if (document.getElementById('minFillConf'))
            document.getElementById('minFillConf').textContent = minFillConf ? `${minFillConf.title} (${Math.round(minFill)}%)` : 'N/A';
        if (document.getElementById('endedConfs'))
            document.getElementById('endedConfs').textContent = ended;
        if (document.getElementById('ongoingConfs'))
            document.getElementById('ongoingConfs').textContent = ongoing;
        if (document.getElementById('upcomingConfs'))
            document.getElementById('upcomingConfs').textContent = upcoming;

        // --- Đăng ký sớm/thông thường ---
        if (data.stats) {
            if (document.getElementById('earlyReg'))
                document.getElementById('earlyReg').textContent = data.stats.early;
            if (document.getElementById('normalReg'))
                document.getElementById('normalReg').textContent = data.stats.normal;
        }
        // --- Biểu đồ xu hướng đăng ký ---
        if (data.registrations_by_date) {
            renderRegistrationTrendChart(data.registrations_by_date);
        }
        // --- Biểu đồ xu hướng doanh thu ---
        if (data.revenue_by_date) {
            renderRevenueTrendChart(data.revenue_by_date);
        }
        // --- Phản hồi ---
        if (data.feedback_stats) {
            renderFeedbackStats(data.feedback_stats);
        }
    } catch (e) {
        // fallback nếu lỗi
        document.getElementById('activeConferences').textContent = '0';
        document.getElementById('totalAttendees').textContent = '0';
        document.getElementById('totalRevenue').textContent = '0₫';
        document.getElementById('growthRate').textContent = '0%';
        if (document.getElementById('avgFillRate'))
            document.getElementById('avgFillRate').textContent = '0%';
        if (document.getElementById('avgRevenue'))
            document.getElementById('avgRevenue').textContent = '0₫';
        if (document.getElementById('maxFillConf'))
            document.getElementById('maxFillConf').textContent = 'N/A';
        if (document.getElementById('minFillConf'))
            document.getElementById('minFillConf').textContent = 'N/A';
        if (document.getElementById('endedConfs'))
            document.getElementById('endedConfs').textContent = '0';
        if (document.getElementById('ongoingConfs'))
            document.getElementById('ongoingConfs').textContent = '0';
        if (document.getElementById('upcomingConfs'))
            document.getElementById('upcomingConfs').textContent = '0';
        if (document.getElementById('earlyReg'))
            document.getElementById('earlyReg').textContent = '0';
        if (document.getElementById('normalReg'))
            document.getElementById('normalReg').textContent = '0';
        if (document.getElementById('avgScore')) document.getElementById('avgScore').textContent = '-';
        if (document.getElementById('speakerQuality')) document.getElementById('speakerQuality').textContent = '-';
        if (document.getElementById('venueRating')) document.getElementById('venueRating').textContent = '-';
        if (document.getElementById('npsScore')) document.getElementById('npsScore').textContent = '-';
    }
}

// --- Phản hồi ---
function renderFeedbackStats(stats) {
    if (document.getElementById('avgScore'))
        document.getElementById('avgScore').textContent = stats.avg_score !== null ? stats.avg_score + '/5' : '-';
    if (document.getElementById('speakerQuality'))
        document.getElementById('speakerQuality').textContent = stats.speaker_quality !== null ? stats.speaker_quality + '/5' : '-';
    if (document.getElementById('venueRating'))
        document.getElementById('venueRating').textContent = stats.venue_rating !== null ? stats.venue_rating + '/5' : '-';
    if (document.getElementById('npsScore'))
        document.getElementById('npsScore').textContent = stats.nps !== null ? stats.nps : '-';
    // Vẽ biểu đồ điểm phản hồi nếu muốn
    if (document.getElementById('feedbackScoreChart')) {
        if (window.feedbackScoreChartInstance) window.feedbackScoreChartInstance.destroy();
        window.feedbackScoreChartInstance = new Chart(document.getElementById('feedbackScoreChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Điểm TB', 'Chất lượng diễn giả', 'Địa điểm', 'NPS'],
                datasets: [{
                    label: 'Chỉ số',
                    data: [
                        stats.avg_score !== null ? stats.avg_score : 0,
                        stats.speaker_quality !== null ? stats.speaker_quality : 0,
                        stats.venue_rating !== null ? stats.venue_rating : 0,
                        stats.nps !== null ? stats.nps : 0
                    ],
                    backgroundColor: ['#28a745', '#4b6cb7', '#ffc107', '#6c757d']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    }
}

// Vẽ biểu đồ đường xu hướng đăng ký
function renderRegistrationTrendChart(registrationsByDate) {
    const ctx = document.getElementById('registrationTrendChart').getContext('2d');
    // Xử lý dữ liệu: labels là ngày, data là số đăng ký mỗi ngày
    const labels = Object.keys(registrationsByDate).sort();
    const data = labels.map(date => registrationsByDate[date]);
    if (window.registrationTrendChartInstance) {
        window.registrationTrendChartInstance.destroy();
    }
    window.registrationTrendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Số đăng ký',
                data: data,
                borderColor: '#4b6cb7',
                backgroundColor: 'rgba(75,108,183,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#253545',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { title: { display: true, text: 'Ngày' } },
                y: { title: { display: true, text: 'Số đăng ký' }, beginAtZero: true }
            }
        }
    });
}

// Vẽ biểu đồ đường xu hướng doanh thu
function renderRevenueTrendChart(revenueByDate) {
    const ctx = document.getElementById('revenueTrendChart').getContext('2d');
    const labels = Object.keys(revenueByDate).sort();
    const data = labels.map(date => revenueByDate[date]);
    if (window.revenueTrendChartInstance) {
        window.revenueTrendChartInstance.destroy();
    }
    window.revenueTrendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (₫)',
                data: data,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40,167,69,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#28a745',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { title: { display: true, text: 'Ngày' } },
                y: { title: { display: true, text: 'Doanh thu (₫)' }, beginAtZero: true }
            }
        }
    });
}

function populateConferenceList() {
    // Get conferences managed by the current user
    const myConferences = getConferences().filter(conf => conf.isManaged);
    
    // Get the container element
    const conferencesList = document.getElementById('conferencesList');
    
    // Clear loading indicator
    conferencesList.innerHTML = '';
    
    if (myConferences.length === 0) {
        conferencesList.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You don't have any conferences yet. Create one by clicking the "Create New Conference" button above.
                </div>
            </div>
        `;
        return;
    }
    
    // Create a card for each conference
    myConferences.forEach(conference => {
        // Calculate status based on dates
        const today = new Date();
        const startDate = new Date(conference.date);
        const endDate = conference.endDate ? new Date(conference.endDate) : startDate;
        
        let statusClass = 'bg-secondary';
        let statusText = 'Draft';
        
        if (startDate <= today && endDate >= today) {
            statusClass = 'bg-success';
            statusText = 'Active';
        } else if (startDate > today) {
            statusClass = 'bg-primary';
            statusText = 'Upcoming';
        } else if (endDate < today) {
            statusClass = 'bg-secondary';
            statusText = 'Past';
        }
        
        const card = document.createElement('div');
        card.className = 'col-md-6 col-lg-4 mb-4';
        card.innerHTML = `
            <div class="manager-card card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge ${statusClass} status-badge">${statusText}</span>
                    <div class="action-buttons">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewConference(${conference.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="editConference(${conference.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="showDeleteConfirmation(${conference.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-3">${conference.title}</h5>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-2"></i>${formatDate(conference.date)}
                            ${conference.endDate ? ' - ' + formatDate(conference.endDate) : ''}
                        </small><br>
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt me-2"></i>${conference.location}
                        </small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${conference.attendees || 0}</strong>/<span class="text-muted">${conference.capacity}</span> 
                            <small class="text-muted">attendees</small>
                        </div>
                        <div>
                            <span class="badge bg-info">${formatCurrency(conference.price)}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                            style="width: ${Math.round((conference.attendees || 0) / (conference.capacity || 1) * 100)}%"
                            aria-valuenow="${Math.round((conference.attendees || 0) / (conference.capacity || 1) * 100)}" 
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">${Math.round((conference.attendees || 0) / (conference.capacity || 1) * 100)}% filled</small>
                        <a href="conference-admin.html?id=${conference.id}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        `;
        
        conferencesList.appendChild(card);
    });
}

// --- Đăng ký sớm/thông thường ---
async function fetchAndRenderStats() {
    try {
        // Lấy danh sách hội nghị do user tạo
        const res = await fetch('api/conference_manager.php', { credentials: 'include' }); // Đảm bảo gửi cookie/session
        const data = await res.json();
        if (!data.status) return;
        const conferences = data.data || [];
        // Hội nghị đang hoạt động
        const activeConfs = conferences.filter(c => c.status === 'active');
        document.getElementById('activeConferences').textContent = activeConfs.length;
        // Tổng người tham dự và tổng doanh thu
        let totalAttendees = 0;
        let totalRevenue = 0;
        let totalCapacity = 0;
        let maxFill = -1, minFill = 101;
        let maxFillConf = null, minFillConf = null;
        for (const conf of conferences) {
            const attendees = parseInt(conf.attendees || 0);
            const capacity = parseInt(conf.capacity || 1);
            totalAttendees += attendees;
            totalRevenue += (parseFloat(conf.price || 0) * attendees);
            totalCapacity += capacity;
            const fillRate = (attendees / capacity) * 100;
            if (fillRate > maxFill) { maxFill = fillRate; maxFillConf = conf; }
            if (fillRate < minFill) { minFill = fillRate; minFillConf = conf; }
        }
        document.getElementById('totalAttendees').textContent = totalAttendees;
        document.getElementById('totalRevenue').textContent = totalRevenue.toLocaleString('en-US') + '₫';
        // Tỷ lệ tăng trưởng (ví dụ: so sánh số hội nghị active tháng này với tháng trước)
        const now = new Date();
        const thisMonth = now.getMonth() + 1;
        const thisYear = now.getFullYear();
        const lastMonth = thisMonth === 1 ? 12 : thisMonth - 1;
        const lastMonthYear = thisMonth === 1 ? thisYear - 1 : thisYear;
        const activeThisMonth = activeConfs.filter(c => {
            const d = new Date(c.start_date);
            return d.getMonth() + 1 === thisMonth && d.getFullYear() === thisYear;
        }).length;
        const activeLastMonth = activeConfs.filter(c => {
            const d = new Date(c.start_date);
            return d.getMonth() + 1 === lastMonth && d.getFullYear() === lastMonthYear;
        }).length;
        let growth = 0;
        if (activeLastMonth > 0) {
            growth = ((activeThisMonth - activeLastMonth) / activeLastMonth) * 100;
        } else if (activeThisMonth > 0) {
            growth = 100;
        }
        document.getElementById('growthRate').textContent = Math.round(growth) + '%';

        // --- Performance Analysis ---
        // Tỷ lệ lấp đầy trung bình
        let avgFillRate = totalCapacity > 0 ? (totalAttendees / totalCapacity) * 100 : 0;
        // Doanh thu trung bình trên mỗi hội nghị
        let avgRevenue = conferences.length > 0 ? totalRevenue / conferences.length : 0;
        // Số hội nghị đã kết thúc, đang diễn ra, sắp diễn ra
        let nowDate = new Date();
        let ended = 0, ongoing = 0, upcoming = 0;
        conferences.forEach(conf => {
            const start = new Date(conf.start_date);
            const end = conf.end_date ? new Date(conf.end_date) : start;
            if (end < nowDate) ended++;
            else if (start > nowDate) upcoming++;
            else ongoing++;
        });
        // Render các chỉ số này nếu có chỗ hiển thị (giả sử có các id tương ứng)
        if (document.getElementById('avgFillRate'))
            document.getElementById('avgFillRate').textContent = avgFillRate.toFixed(1) + '%';
        if (document.getElementById('avgRevenue'))
            document.getElementById('avgRevenue').textContent = avgRevenue.toLocaleString('en-US') + '₫';
        if (document.getElementById('maxFillConf'))
            document.getElementById('maxFillConf').textContent = maxFillConf ? `${maxFillConf.title} (${Math.round(maxFill)}%)` : 'N/A';
        if (document.getElementById('minFillConf'))
            document.getElementById('minFillConf').textContent = minFillConf ? `${minFillConf.title} (${Math.round(minFill)}%)` : 'N/A';
        if (document.getElementById('endedConfs'))
            document.getElementById('endedConfs').textContent = ended;
        if (document.getElementById('ongoingConfs'))
            document.getElementById('ongoingConfs').textContent = ongoing;
        if (document.getElementById('upcomingConfs'))
            document.getElementById('upcomingConfs').textContent = upcoming;

        // --- Đăng ký sớm/thông thường ---
        if (data.stats) {
            if (document.getElementById('earlyReg'))
                document.getElementById('earlyReg').textContent = data.stats.early;
            if (document.getElementById('normalReg'))
                document.getElementById('normalReg').textContent = data.stats.normal;
        }
        // --- Biểu đồ xu hướng đăng ký ---
        if (data.registrations_by_date) {
            renderRegistrationTrendChart(data.registrations_by_date);
        }
        // --- Biểu đồ xu hướng doanh thu ---
        if (data.revenue_by_date) {
            renderRevenueTrendChart(data.revenue_by_date);
        }
        // --- Phản hồi ---
        if (data.feedback_stats) {
            renderFeedbackStats(data.feedback_stats);
        }
    } catch (e) {
        // fallback nếu lỗi
        document.getElementById('activeConferences').textContent = '0';
        document.getElementById('totalAttendees').textContent = '0';
        document.getElementById('totalRevenue').textContent = '0₫';
        document.getElementById('growthRate').textContent = '0%';
        if (document.getElementById('avgFillRate'))
            document.getElementById('avgFillRate').textContent = '0%';
        if (document.getElementById('avgRevenue'))
            document.getElementById('avgRevenue').textContent = '0₫';
        if (document.getElementById('maxFillConf'))
            document.getElementById('maxFillConf').textContent = 'N/A';
        if (document.getElementById('minFillConf'))
            document.getElementById('minFillConf').textContent = 'N/A';
        if (document.getElementById('endedConfs'))
            document.getElementById('endedConfs').textContent = '0';
        if (document.getElementById('ongoingConfs'))
            document.getElementById('ongoingConfs').textContent = '0';
        if (document.getElementById('upcomingConfs'))
            document.getElementById('upcomingConfs').textContent = '0';
        if (document.getElementById('earlyReg'))
            document.getElementById('earlyReg').textContent = '0';
        if (document.getElementById('normalReg'))
            document.getElementById('normalReg').textContent = '0';
        if (document.getElementById('avgScore')) document.getElementById('avgScore').textContent = '-';
        if (document.getElementById('speakerQuality')) document.getElementById('speakerQuality').textContent = '-';
        if (document.getElementById('venueRating')) document.getElementById('venueRating').textContent = '-';
        if (document.getElementById('npsScore')) document.getElementById('npsScore').textContent = '-';
    }
}

// --- Phản hồi ---
function renderFeedbackStats(stats) {
    if (document.getElementById('avgScore'))
        document.getElementById('avgScore').textContent = stats.avg_score !== null ? stats.avg_score + '/5' : '-';
    if (document.getElementById('speakerQuality'))
        document.getElementById('speakerQuality').textContent = stats.speaker_quality !== null ? stats.speaker_quality + '/5' : '-';
    if (document.getElementById('venueRating'))
        document.getElementById('venueRating').textContent = stats.venue_rating !== null ? stats.venue_rating + '/5' : '-';
    if (document.getElementById('npsScore'))
        document.getElementById('npsScore').textContent = stats.nps !== null ? stats.nps : '-';
    // Vẽ biểu đồ điểm phản hồi nếu muốn
    if (document.getElementById('feedbackScoreChart')) {
        if (window.feedbackScoreChartInstance) window.feedbackScoreChartInstance.destroy();
        window.feedbackScoreChartInstance = new Chart(document.getElementById('feedbackScoreChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Điểm TB', 'Chất lượng diễn giả', 'Địa điểm', 'NPS'],
                datasets: [{
                    label: 'Chỉ số',
                    data: [
                        stats.avg_score !== null ? stats.avg_score : 0,
                        stats.speaker_quality !== null ? stats.speaker_quality : 0,
                        stats.venue_rating !== null ? stats.venue_rating : 0,
                        stats.nps !== null ? stats.nps : 0
                    ],
                    backgroundColor: ['#28a745', '#4b6cb7', '#ffc107', '#6c757d']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    }
}

// Vẽ biểu đồ đường xu hướng đăng ký
function renderRegistrationTrendChart(registrationsByDate) {
    const ctx = document.getElementById('registrationTrendChart').getContext('2d');
    // Xử lý dữ liệu: labels là ngày, data là số đăng ký mỗi ngày
    const labels = Object.keys(registrationsByDate).sort();
    const data = labels.map(date => registrationsByDate[date]);
    if (window.registrationTrendChartInstance) {
        window.registrationTrendChartInstance.destroy();
    }
    window.registrationTrendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Số đăng ký',
                data: data,
                borderColor: '#4b6cb7',
                backgroundColor: 'rgba(75,108,183,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#253545',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { title: { display: true, text: 'Ngày' } },
                y: { title: { display: true, text: 'Số đăng ký' }, beginAtZero: true }
            }
        }
    });
}

// Vẽ biểu đồ đường xu hướng doanh thu
function renderRevenueTrendChart(revenueByDate) {
    const ctx = document.getElementById('revenueTrendChart').getContext('2d');
    const labels = Object.keys(revenueByDate).sort();
    const data = labels.map(date => revenueByDate[date]);
    if (window.revenueTrendChartInstance) {
        window.revenueTrendChartInstance.destroy();
    }
    window.revenueTrendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (₫)',
                data: data,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40,167,69,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#28a745',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { title: { display: true, text: 'Ngày' } },
                y: { title: { display: true, text: 'Doanh thu (₫)' }, beginAtZero: true }
            }
        }
    });
}

function populateConferenceList() {
    // Get conferences managed by the current user
    const myConferences = getConferences().filter(conf => conf.isManaged);
    
    // Get the container element
    const conferencesList = document.getElementById('conferencesList');
    
    // Clear loading indicator
    conferencesList.innerHTML = '';
    
    if (myConferences.length === 0) {
        conferencesList.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You don't have any conferences yet. Create one by clicking the "Create New Conference" button above.
                </div>
            </div>
        `;
        return;
    }
    
    // Create a card for each conference
    myConferences.forEach(conference => {
        // Calculate status based on dates
        const today = new Date();
        const startDate = new Date(conference.date);
        const endDate = conference.endDate ? new Date(conference.endDate) : startDate;
        
        let statusClass = 'bg-secondary';
        let statusText = 'Draft';
        
        if (startDate <= today && endDate >= today) {
            statusClass = 'bg-success';
            statusText = 'Active';
        } else if (startDate > today) {
            statusClass = 'bg-primary';
            statusText = 'Upcoming';
        } else if (endDate < today) {
            statusClass = 'bg-secondary';
            statusText = 'Past';
        }
        
        const card = document.createElement('div');
        card.className = 'col-md-6 col-lg-4 mb-4';
        card.innerHTML = `
            <div class="manager-card card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge ${statusClass} status-badge">${statusText}</span>
                    <div class="action-buttons">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewConference(${conference.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="editConference(${conference.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="showDeleteConfirmation(${conference.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-3">${conference.title}</h5>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-2"></i>${formatDate(conference.date)}
                            ${conference.endDate ? ' - ' + formatDate(conference.endDate) : ''}
                        </small><br>
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt me-2"></i>${conference.location}
                        </small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${conference.attendees || 0}</strong>/<span class="text-muted">${conference.capacity}</span> 
                            <small class="text-muted">attendees</small>
                        </div>
                        <div>
                            <span class="badge bg-info">${formatCurrency(conference.price)}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                            style="width: ${Math.round((conference.attendees || 0) / (conference.capacity || 1) * 100)}%"
                            aria-valuenow="${Math.round((conference.attendees || 0) / (conference.capacity || 1) * 100)}" 
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">${Math.round((conference.attendees || 0) / (conference.capacity || 1) * 100)}% filled</small>
                        <a href="conference-admin.html?id=${conference.id}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        `;
        
        conferencesList.appendChild(card);
    });
}

function populateConferenceSelect() {
    const myConferences = getConferences().filter(conf => conf.isManaged);
    const conferenceSelect = document.getElementById('conferenceSelect');
    
    // Clear existing options except the first one
    conferenceSelect.innerHTML = '<option selected>Select a conference...</option>';
    
    // Add options for each conference
    myConferences.forEach(conference => {
        const option = document.createElement('option');
        option.value = conference.id;
        option.textContent = conference.title;
        conferenceSelect.appendChild(option);
    });
    
    // Add event listener to load attendees when a conference is selected
    conferenceSelect.addEventListener('change', function() {
        if (this.value !== 'Select a conference...') {
            loadAttendees(parseInt(this.value));
        }
    });
}

function loadAttendees(conferenceId) {
    // In a real application, these would be fetched from an API
    const mockAttendees = [
        { id: 1, name: "Alice Johnson", email: "alice@example.com", registrationDate: "2023-05-15", ticketType: "VIP", checkedIn: true },
        { id: 2, name: "Bob Smith", email: "bob@example.com", registrationDate: "2023-05-18", ticketType: "Standard", checkedIn: true },
        { id: 3, name: "Charlie Davis", email: "charlie@example.com", registrationDate: "2023-05-20", ticketType: "Standard", checkedIn: false },
        { id: 4, name: "Dana Garcia", email: "dana@example.com", registrationDate: "2023-05-25", ticketType: "Early Bird", checkedIn: false },
        { id: 5, name: "Evan Lee", email: "evan@example.com", registrationDate: "2023-06-01", ticketType: "VIP", checkedIn: false }
    ];
    
    const attendeesList = document.getElementById('attendeesList');
    attendeesList.innerHTML = '';
    
    mockAttendees.forEach(attendee => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${attendee.name}</td>
            <td>${attendee.email}</td>
            <td>${formatDate(attendee.registrationDate)}</td>
            <td><span class="badge bg-${attendee.ticketType === 'VIP' ? 'info' : 'secondary'}">${attendee.ticketType}</span></td>
            <td>
                <span class="badge ${attendee.checkedIn ? 'bg-success' : 'bg-warning'}">
                    ${attendee.checkedIn ? 'Checked In' : 'Not Checked In'}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="toggleCheckIn(${attendee.id})">
                        <i class="fas fa-${attendee.checkedIn ? 'times' : 'check'}"></i>
                        ${attendee.checkedIn ? 'Undo Check-in' : 'Check In'}
                    </button>
                    <button class="btn btn-outline-secondary" onclick="emailAttendee(${attendee.id})">
                        <i class="fas fa-envelope"></i>
                    </button>
                </div>
            </td>
        `;
        attendeesList.appendChild(row);
    });
}

function viewConference(conferenceId) {
    window.location.href = `conference-detail.html?id=${conferenceId}`;
}

function editConference(conferenceId) {
    // In a real application, you would fetch the conference details and populate the form
    // For now, we'll just show an alert
    alert(`Editing conference with ID ${conferenceId}`);
}

function showDeleteConfirmation(conferenceId) {
    if (confirm('Are you sure you want to delete this conference?')) {
        deleteConference(conferenceId);
    }
}

function deleteConference(conferenceId) {
    // In a real application, this would send a request to delete the conference
    // For now, we'll just show an alert and refresh the list
    alert(`Conference ${conferenceId} has been deleted`);
    populateConferenceList();
}

function createConference() {
    // Get form values
    const title = document.getElementById('conferenceTitle').value.trim();
    const category = document.getElementById('conferenceCategory').value;
    const startDate = document.getElementById('conferenceStartDate').value;
    const endDate = document.getElementById('conferenceEndDate').value;
    const location = document.getElementById('conferenceLocation').value.trim();
    const price = parseFloat(document.getElementById('conferencePrice').value);
    const capacity = parseInt(document.getElementById('conferenceCapacity').value);
    const description = document.getElementById('conferenceDescription').value.trim();
    
    // Form validation
    if (!title || !category || !startDate || !endDate || !location || isNaN(price) || isNaN(capacity) || !description) {
        alert('Please fill out all required fields');
        return;
    }
    
    // In a real application, this would send a request to create the conference
    // For now, we'll just show an alert and refresh the list
    alert(`Conference "${title}" has been created!`);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addConferenceModal'));
    modal.hide();
    
    // Reset form
    document.getElementById('addConferenceForm').reset();
    
    // Refresh conference list
    populateConferenceList();
    // populateConferenceSelect();
}

function updateConference() {
    // Similar to createConference, but for updates
    alert('Conference updated successfully!');
}

function toggleCheckIn(attendeeId) {
    // In a real application, this would send a request to toggle the check-in status
    alert(`Toggled check-in status for attendee ${attendeeId}`);
    
    // Reload attendees to reflect the change
    const conferenceId = document.getElementById('conferenceSelect').value;
    loadAttendees(parseInt(conferenceId));
}

function emailAttendee(attendeeId) {
    // In a real application, this might open an email composition form
    alert(`Opening email form for attendee ${attendeeId}`);
}

function filterConferences(filter) {
    alert(`Filtering conferences by: ${filter}`);
    // In a real application, this would filter the conferences list
}

function searchConferences() {
    const query = document.getElementById('searchConference').value.trim();
    if (query) {
        alert(`Searching for conferences matching: "${query}"`);
        // In a real application, this would search the conferences list
    }
}

// Helper function to format dates
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Helper function to format currency
function formatCurrency(amount) {
    return '$' + amount.toLocaleString('en-US');
}
