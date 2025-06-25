// ===================== CONFERENCE ADMIN COMBINED JS =====================
// File gộp toàn bộ chức năng quản trị hội nghị
// Version: Refactored - Removed duplicates and conflicts

// ===================== GLOBAL VARIABLES & UTILITIES =====================
let currentConference = null;
let agendaData = [];
let attendeesData = [];
let speakersData = [];
let objectivesData = [];
let audienceData = [];
let faqData = [];
let editingSessionId = null;

// ===================== COMMON UTILITIES =====================
function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

function getCurrentConferenceId() {
    return localStorage.getItem('currentConferenceId') || getConferenceIdFromUrl();
}

function showToast(message, type = 'success', duration = 3000) {
    const toastEl = document.getElementById('adminToast');
    const toastBody = document.getElementById('adminToastBody');
    if (!toastEl || !toastBody) {
        // Fallback nếu không có toast element
        alert(message);
        return;
    }
    
    toastBody.innerHTML = message;
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-primary', 'bg-warning');
    
    if (type === 'success') toastEl.classList.add('bg-success');
    else if (type === 'error' || type === 'danger') toastEl.classList.add('bg-danger');
    else if (type === 'warning') toastEl.classList.add('bg-warning');
    else toastEl.classList.add('bg-primary');
    
    const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
    toast.show();
    
    if (duration > 0) {
        setTimeout(() => toast.hide(), duration);
    }
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

function translateAttendeeStatus(status) {
    const map = {
        'draft': { label: 'Chờ xử lý', color: 'secondary' },
        'sent': { label: 'Đã gửi', color: 'info' },
        'paid': { label: 'Đã thanh toán', color: 'success' },
        'cancelled': { label: 'Đã hủy', color: 'danger' },
        'refunded': { label: 'Hoàn tiền', color: 'warning' },
        'active': { label: 'Đang tham dự', color: 'primary' },
        'inactive': { label: 'Ngưng tham dự', color: 'dark' }
    };
    return map[status] || { label: status || 'Không xác định', color: 'secondary' };
}

// ===================== API HELPERS =====================
async function apiGet(url) {
    const response = await fetch(url);
    return await response.json();
}

async function apiPost(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function apiPut(url, data) {
    const response = await fetch(url, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function apiDelete(url, data) {
    const response = await fetch(url, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

// ===================== OVERVIEW TAB =====================
async function loadConferenceInfo() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiGet(`api/conference_by_id.php?id=${conferenceId}`);
        const conf = res.data;
        if (res && res.status && conf) {
            currentConference = conf;
            
            // Get venue info if venue_id exists
            let venueInfo = '';
            if (conf.venue_id) {
                try {
                    const venueRes = await apiGet(`api/venue.php?id=${conf.venue_id}`);
                    if (venueRes && venueRes.status && venueRes.data) {
                        venueInfo = `${venueRes.data.name} - ${venueRes.data.address}`;
                    }
                } catch (e) {
                    console.error('Error loading venue info:', e);
                }
            }
            
            // Use venue info or fallback to location field
            const locationDisplay = venueInfo || conf.location || 'Chưa xác định';
            
            // Update page title and header
            document.getElementById('conference-title').textContent = conf.title;
            document.title = `ConferenceHub - ${conf.title} Admin`;
            
            // Update conference details card
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
                                        <p class="mb-0">${locationDisplay}</p>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1">Mô tả</h6>
                                    <p class="mb-0">${conf.description || ''}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <img src="${conf.image || conf.featured_image || 'https://via.placeholder.com/300x200'}" class="img-fluid rounded" alt="${conf.title}" style="max-height: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        }
    } catch (e) {
        console.error('Error loading conference info:', e);
        showToast('Không thể tải thông tin hội nghị!', 'error');
    }
}

function loadSummaryStats() {
    if (!currentConference) {
        console.log('loadSummaryStats: currentConference not loaded yet');
        return;
    }
    
    console.log('Loading summary stats...', {
        currentConference: currentConference,
        attendeesData: attendeesData,
        attendeesCount: Array.isArray(attendeesData) ? attendeesData.length : 0
    });
    
    const attendeeCount = Array.isArray(attendeesData) ? attendeesData.length : 0;
    const price = parseFloat(currentConference.price) || 0;
    const totalRevenue = attendeeCount * price;
    const capacity = parseInt(currentConference.capacity) || 1;
    const fillRate = Math.round((attendeeCount / capacity) * 100);
    
    const eventDate = new Date(currentConference.start_date);
    const today = new Date();
    const daysLeft = Math.ceil((eventDate - today) / (1000 * 3600 * 24));
    
    console.log('Calculated stats:', {
        attendeeCount,
        price,
        totalRevenue,
        capacity,
        fillRate,
        daysLeft
    });
    
    // Update overview stats
    const totalAttendeesEl = document.getElementById('total-attendees');
    const totalRevenueEl = document.getElementById('total-revenue');
    const fillRateEl = document.getElementById('fill-rate');
    const daysLeftEl = document.getElementById('days-left');
    
    if (totalAttendeesEl) {
        totalAttendeesEl.textContent = attendeeCount.toLocaleString('vi-VN');
        console.log('Updated total-attendees:', attendeeCount);
    }
    if (totalRevenueEl) {
        totalRevenueEl.textContent = totalRevenue.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
        console.log('Updated total-revenue:', totalRevenue);
    }
    if (fillRateEl) {
        fillRateEl.textContent = fillRate + '%';
        console.log('Updated fill-rate:', fillRate);
    }
    if (daysLeftEl) {
        daysLeftEl.textContent = daysLeft > 0 ? daysLeft : 'Sự kiện đã qua';
        console.log('Updated days-left:', daysLeft);
    }
}

async function fetchConferenceInfo() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiGet(`api/conference_by_id.php?id=${conferenceId}`);
        const conf = res.data;
        if (res && res.status && conf) {
            console.log('Conference data loaded:', conf); // Debug log
            
            // Load categories and venues first
            await loadCategoriesForModal();
            await loadVenuesForModal();
              // Fill form fields with correct IDs from the modal
            const formFields = {
                'conference-title-input': conf.title,
                'conference-date-input': formatDateForInput(conf.start_date),
                'conference-end-date-input': formatDateForInput(conf.end_date),
                'conference-description-input': conf.description,
                'conference-status-input': conf.status,
                'conference-category-input': conf.category_id,
                'conference-venue-input': conf.venue_id,
                'conference-type-input': conf.type,
                'conference-format-input': conf.format,
                'conference-currency-input': conf.currency,
                'conference-registration-enabled-input': conf.registration_enabled,
                'conference-capacity-input': conf.capacity,
                'conference-price-input': conf.price
            };
            
            console.log('Form fields to fill:', formFields); // Debug log
            
            Object.entries(formFields).forEach(([id, value]) => {
                const element = document.getElementById(id);
                console.log(`Filling field ${id} with value:`, value, 'Element:', element); // Debug log
                if (element && value !== undefined && value !== null) {
                    if (element.type === 'checkbox') {
                        element.checked = Boolean(value);
                    } else {
                        element.value = value;
                    }
                }
            });
        }
    } catch (e) {
        console.error('Error fetching conference info:', e);
        showToast('Lỗi khi tải thông tin hội nghị!', 'error');
    }
}

async function saveConferenceChanges() {
    const conferenceId = getConferenceIdFromUrl();
    
    // Get data from correct form field IDs
    const payload = {
        id: conferenceId,
        title: document.getElementById('conference-title-input').value,
        start_date: document.getElementById('conference-date-input').value,
        end_date: document.getElementById('conference-end-date-input').value,
        description: document.getElementById('conference-description-input').value,
        status: document.getElementById('conference-status-input').value,
        category_id: document.getElementById('conference-category-input').value,
        venue_id: document.getElementById('conference-venue-input').value,
        type: document.getElementById('conference-type-input').value,
        format: document.getElementById('conference-format-input').value,
        currency: document.getElementById('conference-currency-input').value,
        registration_enabled: document.getElementById('conference-registration-enabled-input').checked ? 1 : 0,
        capacity: document.getElementById('conference-capacity-input').value,
        price: document.getElementById('conference-price-input').value
    };
    
    try {
        const res = await apiPost('api/update_conference.php', payload);
        if (res && res.status) {
            // Update UI elements
            const titleElement = document.getElementById('conference-title');
            if (titleElement) titleElement.textContent = payload.title;
            
            await loadConferenceInfo();
            showToast('Cập nhật thành công!', 'success');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('editConferenceModal'));
            if (modal) modal.hide();
        } else {
            showToast('Có lỗi khi cập nhật hội nghị!', 'error');
        }
    } catch (e) {
        console.error('Error saving conference changes:', e);
        showToast('Có lỗi khi cập nhật hội nghị!', 'error');
    }
}

async function toggleConferenceStatus() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiGet(`api/conference_by_id.php?id=${conferenceId}`);
        if (res && res.status && res.data) {
            const currentStatus = res.data.status;
            const newStatus = currentStatus === 'archived' ? 'active' : 'archived';
            
            const updateRes = await apiPost('api/update_conference.php', {
                id: conferenceId,
                status: newStatus
            });
            
            if (updateRes && updateRes.status) {
                showToast('Đã cập nhật trạng thái hội nghị!', 'success');
                await loadConferenceInfo();
            } else {
                showToast('Có lỗi khi cập nhật trạng thái!', 'error');
            }
        }
    } catch (e) {
        console.error('Error toggling conference status:', e);
        showToast('Có lỗi khi cập nhật trạng thái!', 'error');
    }
}

// ===================== AGENDA TAB =====================
async function initializeAgendaData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiGet(`api/conference_schedule.php?conference_id=${conferenceId}`);
        if (res && res.status && Array.isArray(res.data)) {
            agendaData = res.data;
        } else {
            agendaData = [];
        }
    } catch (e) {
        console.error('Failed to initialize agenda data:', e);
        agendaData = [];
    }
}

function renderAgendaRow(session) {
    let duration = '';
    if (session.start_time && session.end_time) {
        const [h1, m1] = session.start_time.split(':').map(Number);
        const [h2, m2] = session.end_time.split(':').map(Number);
        duration = ((h2 * 60 + m2) - (h1 * 60 + m1)) + ' phút';
    }
    
    const dateStr = session.session_date ? new Date(session.session_date).toLocaleDateString('vi-VN') : '';
    
    return `
    <div class="row mb-3 align-items-center text-center p-2 bg-light rounded">
        <div class="col-md-1">${dateStr}</div>
        <div class="col-md-1">${session.start_time ? session.start_time.substring(0,5) : ''}</div>
        <div class="col-md-1">${duration}</div>
        <div class="col-md-3">${session.title || ''}</div>
        <div class="col-md-1">${session.speaker_name || ''}</div>
        <div class="col-md-2">${session.type || ''}</div>
        <div class="col-md-2">${session.room || ''}</div>
        <div class="col-md-1">
            <div class='d-flex justify-content-center align-items-center gap-1'>
                <button class="btn btn-sm btn-outline-primary" onclick="editAgendaSession(${session.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="removeAgendaSession(${session.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>`;
}

function loadAgendaTable() {
    const agendaContainer = document.getElementById('agenda-management');
    if (!agendaContainer) return;
    
    const headerHtml = `
    <div class="row mb-3 fw-bold text-center">
        <div class="col-md-1">Ngày</div>
        <div class="col-md-1">Bắt đầu</div>
        <div class="col-md-1">Thời lượng</div>
        <div class="col-md-3">Tiêu đề</div>
        <div class="col-md-1">Diễn giả</div>
        <div class="col-md-2">Loại</div>
        <div class="col-md-2">Phòng</div>
        <div class="col-md-1">Thao tác</div>
    </div>
    <hr class="my-2" />`;
    
    let html = '';
    if (agendaData.length === 0) {
        html = '<div class="text-center text-muted">Chưa có lịch trình nào.</div>';
    } else {
        html = agendaData.map(renderAgendaRow).join('');
    }
    
    agendaContainer.innerHTML = headerHtml + html;
}

async function loadSpeakersToSelect(selectedId = null) {
    const modal = document.getElementById('addSessionModal');
    if (!modal) return;
    
    const select = modal.querySelector('select[name="session-speaker"]');
    if (!select) return;
    
    const conferenceId = getConferenceIdFromUrl();
    select.innerHTML = '<option value="">Chọn Diễn giả</option>';
    
    try {
        const res = await apiGet(`api/conference_speakers.php?conference_id=${conferenceId}`);
        if (res && res.status && Array.isArray(res.data)) {
            res.data.forEach(speaker => {
                let value = null;
                if (typeof speaker.id !== 'undefined' && !isNaN(Number(speaker.id))) {
                    value = parseInt(speaker.id);
                } else if (typeof speaker.speaker_id !== 'undefined' && !isNaN(Number(speaker.speaker_id))) {
                    value = parseInt(speaker.speaker_id);
                }
                
                if (value !== null) {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = speaker.name + (speaker.title ? ` (${speaker.title})` : '');
                    if (selectedId && value == selectedId) option.selected = true;
                    select.appendChild(option);
                }
            });
        }
        
        if (select.options.length === 1) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Chưa có diễn giả nào';
            select.appendChild(option);
        }
    } catch (e) {
        console.error('Error loading speakers:', e);
    }
}

async function handleAddOrEditSession() {
    const modal = document.getElementById('addSessionModal');
    if (!modal) return;
    
    const title = modal.querySelector('input[type="text"]').value.trim();
    const sessionDate = modal.querySelector('input[type="date"]').value;
    const startTime = modal.querySelectorAll('input[type="time"]')[0].value;
    const duration = modal.querySelector('input[type="number"]').value;
    
    // Additional fields
    const type = modal.querySelector('select[name="session-type"]')?.value || 'presentation';
    const room = modal.querySelector('input[name="session-room"]')?.value || null;
    
    // Speaker ID
    const speakerSelect = modal.querySelector('select[name="session-speaker"]');
    let speakerId = speakerSelect ? speakerSelect.value : null;
    if (speakerId === '' || speakerId === null || isNaN(Number(speakerId))) {
        speakerId = null;
    } else {
        speakerId = parseInt(speakerId);
    }
    
    const description = modal.querySelector('textarea').value.trim();
    
    // Validation
    if (!title || !sessionDate || !startTime || !duration) {
        showToast('Vui lòng nhập đầy đủ thông tin bắt buộc!', 'warning');
        return;
    }
    
    const conferenceId = getConferenceIdFromUrl();
    
    // Calculate end time
    let [h, m] = startTime.split(':').map(Number);
    let endMinutes = h * 60 + m + parseInt(duration);
    let endH = Math.floor(endMinutes / 60);
    let endM = endMinutes % 60;
    let endTime = `${endH.toString().padStart(2, '0')}:${endM.toString().padStart(2, '0')}:00`;
    
    let sessionData = {
        conference_id: conferenceId,
        title,
        session_date: sessionDate,
        start_time: startTime + ':00',
        end_time: endTime,
        type,
        room,
        speaker_id: speakerId,
        description
    };
    
    let endpoint = 'api/conference_schedule.php';
    let method = editingSessionId ? 'PUT' : 'POST';
    if (editingSessionId) sessionData.id = editingSessionId;
    
    try {
        let res;
        if (method === 'POST') {
            res = await apiPost(endpoint, sessionData);
        } else {
            res = await apiPost(endpoint + '?_method=PUT', sessionData);
        }
        
        if (res && res.status) {
            showToast(editingSessionId ? 'Cập nhật phiên thành công!' : 'Thêm phiên thành công!', 'success');
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) modalInstance.hide();
            await initializeAgendaData();
            loadAgendaTable();
        } else {
            showToast(res && res.message ? res.message : 'Có lỗi xảy ra!', 'error');
        }
    } catch (e) {
        console.error('Error handling session:', e);
        showToast('Lỗi khi lưu phiên!', 'error');
    }
}

function clearSessionModal() {
    const modal = document.getElementById('addSessionModal');
    if (!modal) return;
    
    const inputs = modal.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        if (input.type === 'text' || input.type === 'date' || input.type === 'time' || input.type === 'number' || input.tagName === 'TEXTAREA') {
            input.value = '';
        } else if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        }
    });
}

window.editAgendaSession = function(id) {
    const session = agendaData.find(s => s.id == id);
    if (!session) return;
    
    editingSessionId = id;
    const modal = document.getElementById('addSessionModal');
    if (!modal) return;
    
    // Fill form with session data
    modal.querySelector('input[type="text"]').value = session.title || '';
    modal.querySelectorAll('input[type="time"]')[0].value = session.start_time ? session.start_time.substring(0,5) : '';
    
    // Calculate duration
    let duration = '';
    if (session.start_time && session.end_time) {
        const [h1, m1] = session.start_time.split(':').map(Number);
        const [h2, m2] = session.end_time.split(':').map(Number);
        duration = (h2 * 60 + m2) - (h1 * 60 + m1);
    }
    modal.querySelector('input[type="number"]').value = duration;
    
    // Set other fields
    const dateInput = modal.querySelector('input[type="date"]');
    if (dateInput) dateInput.value = session.session_date || '';
    
    const typeSelect = modal.querySelector('select[name="session-type"]');
    if (typeSelect) typeSelect.value = session.type || '';
    
    const roomInput = modal.querySelector('input[name="session-room"]');
    if (roomInput) roomInput.value = session.room || '';
    
    modal.querySelector('textarea').value = session.description || '';
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
};

window.removeAgendaSession = async function(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa phiên này?')) return;
    
    try {
        const res = await apiPost('api/conference_schedule.php?_method=DELETE', { id });
        if (res && res.status) {
            showToast('Đã xóa phiên thành công!', 'success');
            await initializeAgendaData();
            loadAgendaTable();
        } else {
            showToast(res && res.message ? res.message : 'Xóa phiên thất bại!', 'error');
        }
    } catch (e) {
        console.error('Error removing session:', e);
        showToast('Lỗi khi xóa phiên!', 'error');
    }
};

function initializeAgendaTab() {
    initializeAgendaData().then(loadAgendaTable);
}

// ===================== ATTENDEES TAB =====================
async function initializeAttendeesData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await fetch(`api/attendees.php?conference_id=${conferenceId}`);
        const data = await res.json();
        if (data && data.status && Array.isArray(data.data)) {
            attendeesData = data.data;
        } else {
            attendeesData = [];
        }
    } catch (e) {
        console.error('Error loading attendees:', e);
        attendeesData = [];
    }
}

function renderAttendeeRow(attendee) {
    const statusObj = translateAttendeeStatus(attendee.status);
    const registrationDate = attendee.registration_date ? new Date(attendee.registration_date).toLocaleDateString('vi-VN') : '';
    
    const ticketTypeMap = {
        'regular': 'Thường',
        'early_bird': 'Early Bird',
        'vip': 'VIP',
        'student': 'Sinh viên',
        'group': 'Nhóm',
        'complimentary': 'Mời/Free'
    };
    const ticketTypeLabel = ticketTypeMap[attendee.ticket_type] || attendee.ticket_type || '';
    
    return `
        <tr>
            <td>${attendee.name || ''}</td>
            <td>${attendee.email || ''}</td>
            <td>${registrationDate}</td>
            <td>${ticketTypeLabel}</td>
            <td><span class="badge bg-${statusObj.color}">${statusObj.label}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editAttendee(${attendee.id})" title="Sửa">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="removeAttendee(${attendee.id})" title="Xóa">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
}

function loadAttendeesTable() {
    const tableBody = document.getElementById('attendees-table');
    const noAttendeesMessage = document.getElementById('no-attendees-message');
    
    if (!attendeesData || attendeesData.length === 0) {
        if (tableBody) tableBody.innerHTML = '';
        if (noAttendeesMessage) noAttendeesMessage.classList.remove('d-none');
        return;
    }
    
    if (noAttendeesMessage) noAttendeesMessage.classList.add('d-none');
    if (tableBody) {
        tableBody.innerHTML = attendeesData.map(attendee => renderAttendeeRow(attendee)).join('');
    }
}

function filterAttendees() {
    const searchTerm = document.getElementById('attendee-search')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('#attendees-table tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

async function addAttendee() {
    const name = document.getElementById('attendeeName')?.value;
    const email = document.getElementById('attendeeEmail')?.value;
    const status = document.getElementById('attendeeStatus')?.value;
    
    if (!name || !email || !status) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'warning');
        return;
    }
    
    // Check for duplicate email
    if (attendeesData.some(attendee => attendee.email === email)) {
        showToast('Email này đã được sử dụng bởi người tham dự khác!', 'warning');
        return;
    }
    
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiPost('api/attendees.php', {
            conference_id: conferenceId,
            name,
            email,
            status
        });
        
        if (res && res.status) {
            showToast(`${name} đã được thêm vào danh sách người tham dự!`, 'success');
            await initializeAttendeesData();
            loadAttendeesTable();
            loadSummaryStats();
            
            document.getElementById('addAttendeeForm')?.reset();
            const modal = bootstrap.Modal.getInstance(document.getElementById('addAttendeeModal'));
            if (modal) modal.hide();
        } else {
            showToast('Có lỗi khi thêm người tham dự!', 'error');
        }
    } catch (e) {
        console.error('Error adding attendee:', e);
        showToast('Có lỗi khi thêm người tham dự!', 'error');
    }
}

function editAttendee(attendeeId) {
    const attendee = attendeesData.find(a => a.id === attendeeId);
    if (!attendee) return;
    
    const modal = document.getElementById('editAttendeeModal');
    const fields = {
        'editAttendeeId': attendee.id,
        'editAttendeeName': attendee.name || '',
        'editAttendeeEmail': attendee.email || '',
        'editAttendeeTicketType': attendee.ticket_type || 'regular',
        'editAttendeeStatus': attendee.status || 'draft'
    };
    
    let allFieldsExist = true;
    Object.entries(fields).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value;
        } else {
            allFieldsExist = false;
        }
    });
    
    if (modal && allFieldsExist) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } else {
        showToast('Không thể mở form sửa người tham dự!', 'error');
    }
}

async function saveAttendeeChanges() {
    const id = document.getElementById('editAttendeeId')?.value;
    const ticket_type = document.getElementById('editAttendeeTicketType')?.value;
    const status = document.getElementById('editAttendeeStatus')?.value;
    
    if (!id || !ticket_type || !status) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'warning');
        return;
    }
    
    try {
        const res = await apiPut('api/attendees.php', { id, ticket_type, status });
        if (res && res.status) {
            showToast('Cập nhật người tham dự thành công!', 'success');
            await initializeAttendeesData();
            loadAttendeesTable();
            loadSummaryStats();
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('editAttendeeModal'));
            if (modal) modal.hide();
        } else {
            showToast('Có lỗi khi cập nhật người tham dự!', 'error');
        }
    } catch (e) {
        console.error('Error saving attendee changes:', e);
        showToast('Có lỗi khi cập nhật người tham dự!', 'error');
    }
}

async function removeAttendee(attendeeId) {
    const attendee = attendeesData.find(a => a.id === attendeeId);
    if (!attendee) return;
    
    if (!confirm(`Bạn có chắc chắn muốn xóa ${attendee.name} khỏi hội nghị này?`)) return;
    
    try {
        const res = await apiDelete('api/attendees.php', { id: attendeeId });
        if (res && res.status) {
            showToast(`${attendee.name} đã được xóa khỏi hội nghị.`, 'success');
            await initializeAttendeesData();
            loadAttendeesTable();
            loadSummaryStats();
        } else {
            showToast('Có lỗi khi xóa người tham dự!', 'error');
        }
    } catch (e) {
        console.error('Error removing attendee:', e);
        showToast('Có lỗi khi xóa người tham dự!', 'error');
    }
}

async function initializeAttendeesTab() {
    await initializeAttendeesData();
    loadAttendeesTable();
    loadSummaryStats();
}

// ===================== SPEAKERS TAB =====================
async function initializeSpeakersData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiGet(`api/conference_speakers.php?conference_id=${conferenceId}`);
        if (res && res.status && Array.isArray(res.data)) {
            speakersData = res.data;
        } else {
            speakersData = [];
        }
    } catch (e) {
        console.error('Error loading speakers:', e);
        speakersData = [];
    }
}

function renderSpeakerCard(speaker) {
    return `
    <div class="col-md-6 mb-4">
        <div class="management-card p-4">
            <div class="d-flex align-items-center mb-3">
                <img src="${speaker.image || speaker.image_url || 'https://via.placeholder.com/80'}" class="rounded-circle me-3" width="80" height="80" alt="Avatar">
                <div>
                    <h5 class="mb-1">${speaker.name || ''}</h5>
                    <div class="text-muted small">${speaker.title || ''}</div>
                    <div class="text-muted small">${speaker.company || ''}</div>
                </div>
            </div>
            <p class="mb-3">${speaker.bio || ''}</p>
            <div class="d-flex justify-content-between">
                <button class="btn btn-outline-primary btn-sm" onclick="editSpeaker('${speaker.id || ''}')">
                    <i class="fas fa-edit"></i> Sửa
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="removeSpeaker('${speaker.id || ''}')">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </div>
        </div>
    </div>`;
}

function loadSpeakers() {
    const speakersContainer = document.getElementById('speakers-management');
    if (!speakersContainer) return;
    
    let html = '';
    if (speakersData.length === 0) {
        html = '<div class="text-center text-muted">Chưa có diễn giả nào.</div>';
    } else {
        html = speakersData.map(renderSpeakerCard).join('');
    }
    speakersContainer.innerHTML = html;
}

function editSpeaker(id) {
    const speaker = speakersData.find(s => s.id == id);
    if (!speaker) return;
    
    const fields = {
        'edit-speaker-id': speaker.id,
        'edit-speaker-name': speaker.name || '',
        'edit-speaker-title': speaker.title || '',
        'edit-speaker-company': speaker.company || '',
        'edit-speaker-bio': speaker.bio || '',
        'edit-speaker-image': speaker.image || speaker.image_url || ''
    };
    
    Object.entries(fields).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.value = value;
    });
    
    const modal = new bootstrap.Modal(document.getElementById('editSpeakerModal'));
    modal.show();
}

async function removeSpeaker(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa diễn giả này?')) return;
    
    try {
        const res = await apiDelete('api/conference_speakers.php', { id });
        if (res.status) {
            speakersData = speakersData.filter(s => s.id != id);
            loadSpeakers();
            showToast('Xóa diễn giả thành công!', 'success');
        } else {
            showToast('Xóa diễn giả thất bại!', 'error');
        }
    } catch (e) {
        console.error('Error removing speaker:', e);
        showToast('Xóa diễn giả thất bại!', 'error');
    }
}

function initializeSpeakersTab() {
    initializeSpeakersData().then(loadSpeakers);
}

// ===================== OBJECTIVES TAB =====================
async function initializeObjectivesData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiGet(`api/conference_detail.php?id=${conferenceId}`);
        if (res && res.status && res.data && Array.isArray(res.data.objectives)) {
            objectivesData = res.data.objectives;
        } else {
            objectivesData = [];
        }
    } catch (e) {
        console.error('Error loading objectives:', e);
        objectivesData = [];
    }
}

function renderObjectiveRow(objective, idx) {
    return `
        <tr>
            <td>${idx + 1}</td>
            <td>${objective.text || objective}</td>
            <td class="text-end">
                <button class="btn btn-outline-primary btn-sm me-1" onclick="editObjective(${idx})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="removeObjective(${idx})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
}

function loadObjectivesTable() {
    const table = document.querySelector('#objectives .table.table-hover');
    if (!table) return;
    
    let html = `
        <thead><tr>
            <th style="width:40px">#</th>
            <th>Nội dung mục tiêu</th>
            <th style="width:120px" class="text-end">Thao tác</th>
        </tr></thead>
        <tbody>`;
    
    if (objectivesData.length === 0) {
        html += '<tr><td colspan="3" class="text-center text-muted">Chưa có mục tiêu nào.</td></tr>';
    } else {
        html += objectivesData.map(renderObjectiveRow).join('');
    }
    
    html += '</tbody>';
    table.innerHTML = html;
}

function editObjective(idx) {
    const obj = objectivesData[idx];
    if (!obj) return;
    
    const modal = document.getElementById('editObjectiveModal');
    if (!modal) return;
    
    const inputEdit = modal.querySelector('#edit-objective-text');
    const inputId = modal.querySelector('#edit-objective-id');
    
    if (inputEdit) inputEdit.value = obj.text || obj;
    if (inputId) inputId.value = obj.id || idx;
    
    const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
    modalInstance.show();
}

function removeObjective(idx) {
    const obj = objectivesData[idx];
    if (!obj) return;
    
    if (!confirm('Bạn có chắc chắn muốn xóa mục tiêu này?')) return;
    
    const conferenceId = getConferenceIdFromUrl();
    fetch('api/conference_detail.php?id=' + conferenceId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_objective', objective_id: obj.id || idx })
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.status) {
            showToast('Đã xóa mục tiêu!', 'success');
            reloadObjectivesList();
        } else {
            showToast(data && data.message ? data.message : 'Xóa thất bại!', 'error');
        }
    })
    .catch(() => showToast('Lỗi kết nối API!', 'error'));
}

function reloadObjectivesList() {
    initializeObjectivesData().then(loadObjectivesTable);
}

function initializeObjectivesTab() {
    initializeObjectivesData().then(loadObjectivesTable);
}

// ===================== OTHER TABS (Simplified) =====================
async function initializeAudienceData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiGet(`api/conference_detail.php?id=${conferenceId}`);
        if (res && res.status && res.data && Array.isArray(res.data.audience)) {
            audienceData = res.data.audience;
        } else {
            audienceData = [];
        }
    } catch (e) {
        console.error('Error loading audience:', e);
        audienceData = [];
    }
}

function initializeAudienceTab() {
    initializeAudienceData().then(() => {
        console.log('Audience data loaded:', audienceData.length);
    });
}

async function initializeFaqData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await apiGet(`api/conference_detail.php?id=${conferenceId}`);
        if (res && res.status && res.data && Array.isArray(res.data.faq)) {
            faqData = res.data.faq;
        } else {
            faqData = [];
        }
    } catch (e) {
        console.error('Error loading FAQ:', e);
        faqData = [];
    }
}

function initializeFaqTab() {
    initializeFaqData().then(() => {
        console.log('FAQ data loaded:', faqData.length);
    });
}

async function initializeAnalyticsTab() {
    const conferenceId = getConferenceIdFromUrl();
    if (!conferenceId) return;
    
    try {
        // Load basic analytics data
        const attendees = Array.isArray(attendeesData) ? attendeesData : [];
        console.log('Analytics initialized for', attendees.length, 'attendees');
    } catch (e) {
        console.error('Error initializing analytics:', e);
    }
}

function initializeResourcesTab() {
    console.log('Resources tab initialized');
}

function initializeCommunicationTab() {
    console.log('Communication tab initialized');
}

function initializeTestimonialsTab() {
    console.log('Testimonials tab initialized');
}

function initializeSupportTab() {
    console.log('Support tab initialized');
}

function initializeHeroImageTab() {
    console.log('Hero image tab initialized');
}

// ===================== TAB MANAGEMENT =====================
const tabInitializers = {
    'overview': async () => {
        loadConferenceInfo();
        await initializeAttendeesData();
        loadSummaryStats();
    },
    'agenda': initializeAgendaTab,
    'attendees': initializeAttendeesTab,
    'speakers': initializeSpeakersTab,
    'objectives': initializeObjectivesTab,
    'target-audience': initializeAudienceTab,
    'faq-management': initializeFaqTab,
    'analytics': initializeAnalyticsTab,
    'resources': initializeResourcesTab,
    'communication': initializeCommunicationTab,
    'testimonials': initializeTestimonialsTab,
    'support': initializeSupportTab,
    'hero-image': initializeHeroImageTab
};

async function initializeTab(tabId) {
    const initializer = tabInitializers[tabId];
    if (initializer && typeof initializer === 'function') {
        try {
            await initializer();
        } catch (e) {
            console.error(`Error initializing tab ${tabId}:`, e);
        }
    }
}

// ===================== EVENT LISTENERS SETUP =====================
function setupEventListeners() {
    // Tab switching
    document.querySelectorAll('.nav-link[data-bs-toggle="pill"]').forEach(tabBtn => {
        tabBtn.addEventListener('shown.bs.tab', async function (event) {
            const target = event.target.dataset.bsTarget;
            if (target) {
                const tabId = target.replace('#', '');
                await initializeTab(tabId);
            }
        });
    });
    
    // Conference edit modal
    const editConferenceModal = document.getElementById('editConferenceModal');
    if (editConferenceModal) {
        editConferenceModal.addEventListener('show.bs.modal', fetchConferenceInfo);
    }
    
    // Edit conference form
    const editConferenceForm = document.getElementById('edit-conference-form');
    if (editConferenceForm) {
        editConferenceForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await saveConferenceChanges();
        });
    }
    
    // Agenda session modal
    const addSessionModal = document.getElementById('addSessionModal');
    if (addSessionModal) {
        const addSessionBtn = document.querySelector('#agenda .btn-success[data-bs-toggle="modal"]');
        if (addSessionBtn) {
            addSessionBtn.addEventListener('click', function () {
                editingSessionId = null;
                clearSessionModal();
            });
        }
        
        const confirmBtn = addSessionModal.querySelector('.btn-success');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', handleAddOrEditSession);
        }
        
        addSessionModal.addEventListener('shown.bs.modal', function () {
            loadSpeakersToSelect(editingSessionId ? agendaData.find(s => s.id == editingSessionId)?.speaker_id : null);
        });
    }
    
    // Add attendee form
    const addAttendeeForm = document.getElementById('addAttendeeForm');
    if (addAttendeeForm) {
        const submitBtn = addAttendeeForm.querySelector('.btn-primary');
        if (submitBtn) {
            submitBtn.addEventListener('click', addAttendee);
        }
    }
    
    // Edit attendee form
    const saveAttendeeBtn = document.getElementById('saveAttendeeChanges');
    if (saveAttendeeBtn) {
        saveAttendeeBtn.addEventListener('click', saveAttendeeChanges);
    }
    
    // Add speaker form
    const addSpeakerForm = document.getElementById('add-speaker-form');
    if (addSpeakerForm) {
        addSpeakerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const newSpeaker = {
                name: document.getElementById('new-speaker-name')?.value,
                title: document.getElementById('new-speaker-title')?.value,
                company: document.getElementById('new-speaker-company')?.value,
                bio: document.getElementById('new-speaker-bio')?.value,
                image: document.getElementById('new-speaker-image')?.value,
                conference_id: getConferenceIdFromUrl()
            };
            
            try {
                const res = await apiPost('api/conference_speakers.php', newSpeaker);
                if (res.status && res.data) {
                    speakersData.push(res.data);
                    loadSpeakers();
                    showToast('Thêm diễn giả thành công!', 'success');
                    addSpeakerForm.reset();
                    
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addSpeakerModal'));
                        if (modal) modal.hide();
                    }, 100);
                } else {
                    showToast('Thêm diễn giả thất bại!', 'error');
                }
            } catch (e) {
                console.error('Error adding speaker:', e);
                showToast('Thêm diễn giả thất bại!', 'error');
            }
        });
    }
    
    // Edit speaker form
    const editSpeakerForm = document.getElementById('edit-speaker-form');
    if (editSpeakerForm) {
        editSpeakerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('edit-speaker-id')?.value;
            const updatedSpeaker = {
                id,
                name: document.getElementById('edit-speaker-name')?.value,
                title: document.getElementById('edit-speaker-title')?.value,
                company: document.getElementById('edit-speaker-company')?.value,
                bio: document.getElementById('edit-speaker-bio')?.value,
                image: document.getElementById('edit-speaker-image')?.value,
                conference_id: getConferenceIdFromUrl()
            };
            
            try {
                const res = await apiPut('api/conference_speakers.php', updatedSpeaker);
                if (res.status) {
                    const idx = speakersData.findIndex(s => s.id == id);
                    if (idx > -1) speakersData[idx] = updatedSpeaker;
                    loadSpeakers();
                    showToast('Cập nhật diễn giả thành công!', 'success');
                    
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editSpeakerModal'));
                    if (modal) modal.hide();
                } else {
                    showToast('Cập nhật diễn giả thất bại!', 'error');
                }
            } catch (e) {
                console.error('Error updating speaker:', e);
                showToast('Cập nhật diễn giả thất bại!', 'error');
            }
        });
    }
    
    // Add objective form
    const addObjectiveForm = document.getElementById('add-objective-form');
    if (addObjectiveForm) {
        addObjectiveForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const input = document.getElementById('new-objective-text');
            const text = input?.value.trim();
            
            if (!text) {
                showToast('Vui lòng nhập nội dung mục tiêu!', 'warning');
                return;
            }
            
            const conferenceId = getConferenceIdFromUrl();
            try {
                const res = await apiPost(`api/conference_detail.php?id=${conferenceId}`, {
                    action: 'add_objective',
                    text
                });
                
                if (res && res.status) {
                    if (input) input.value = '';
                    reloadObjectivesList();
                    showToast('Thêm mục tiêu thành công!', 'success');
                    
                    const modal = document.getElementById('addObjectiveModal');
                    if (modal) {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) modalInstance.hide();
                    }
                } else {
                    showToast(res && res.message ? res.message : 'Thêm mục tiêu thất bại!', 'error');
                }
            } catch (err) {
                console.error('Error adding objective:', err);
                showToast('Lỗi kết nối API!', 'error');
            }
        });
    }
    
    // Edit objective form
    const editObjectiveModal = document.getElementById('editObjectiveModal');
    if (editObjectiveModal) {
        const saveBtn = editObjectiveModal.querySelector('#btn-edit-objective');
        if (saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const inputEdit = editObjectiveModal.querySelector('#edit-objective-text');
                const inputId = editObjectiveModal.querySelector('#edit-objective-id');
                
                const newText = inputEdit?.value.trim();
                const objectiveId = inputId?.value;
                
                if (!newText) {
                    showToast('Vui lòng nhập nội dung mục tiêu!', 'warning');
                    return;
                }
                
                const conferenceId = getConferenceIdFromUrl();
                fetch('api/conference_detail.php?id=' + conferenceId, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'edit_objective',
                        objective_id: objectiveId,
                        text: newText
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.status) {
                        showToast('Cập nhật mục tiêu thành công!', 'success');
                        reloadObjectivesList();
                        
                        const modalInstance = bootstrap.Modal.getInstance(editObjectiveModal);
                        if (modalInstance) modalInstance.hide();
                    } else {
                        showToast(data && data.message ? data.message : 'Cập nhật thất bại!', 'error');
                    }
                })
                .catch(() => showToast('Lỗi kết nối API!', 'error'));
            });
        }
    }
    
    // Attendee search
    const attendeeSearch = document.getElementById('attendee-search');
    if (attendeeSearch) {
        attendeeSearch.addEventListener('input', filterAttendees);
    }
    
    // Clean up modal backdrop on hide
    document.addEventListener('hidden.bs.modal', function (event) {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style = '';
    });
}

// ===================== INITIALIZATION =====================
document.addEventListener('DOMContentLoaded', async function() {
    console.log('Conference Admin Combined JS loaded');
    
    // Setup all event listeners
    setupEventListeners();
    
    // Initialize first tab (overview)
    setTimeout(async () => {
        await initializeTab('overview');
        
        // Check if any specific tab is active
        const activeTab = document.querySelector('.nav-link.active[data-bs-target]');
        if (activeTab) {
            const tabId = activeTab.dataset.bsTarget.replace('#', '');
            if (tabId !== 'overview') {
                await initializeTab(tabId);
            }
        }
    }, 100);
});

// ===================== GLOBAL FUNCTION ASSIGNMENTS =====================
// Make functions available globally for onclick handlers
window.editAgendaSession = window.editAgendaSession;
window.removeAgendaSession = window.removeAgendaSession;
window.editAttendee = editAttendee;
window.removeAttendee = removeAttendee;
window.editSpeaker = editSpeaker;
window.removeSpeaker = removeSpeaker;
window.editObjective = editObjective;
window.removeObjective = removeObjective;
window.saveConferenceChanges = saveConferenceChanges;
window.toggleConferenceStatus = toggleConferenceStatus;
window.addAttendee = addAttendee;
window.saveAttendeeChanges = saveAttendeeChanges;
window.filterAttendees = filterAttendees;

// ===================== CATEGORY & VENUE LOADING =====================
// Load categories for modal dropdown
async function loadCategoriesForModal() {
    try {
        const res = await apiGet('api/category.php');
        if (res && res.status && res.data) {
            const categorySelect = document.getElementById('conference-category-input');
            if (categorySelect) {
                categorySelect.innerHTML = '<option value="">Chọn danh mục...</option>';
                
                // Check if data is paginated or simple array
                const categories = res.data.items || res.data;
                
                if (Array.isArray(categories)) {
                    categories.forEach(category => {
                        categorySelect.innerHTML += `<option value="${category.id}">${category.name}</option>`;
                    });
                }
            }
        }
    } catch (e) {
        console.error('Error loading categories:', e);
    }
}

// Load venues for modal dropdown
async function loadVenuesForModal() {
    try {
        const res = await apiGet('api/venue.php');
        if (res && res.status && res.data) {
            const venueSelect = document.getElementById('conference-venue-input');
            if (venueSelect) {
                venueSelect.innerHTML = '<option value="">Chọn địa điểm...</option>';
                
                // Check if data is paginated or simple array
                const venues = res.data.items || res.data;
                
                if (Array.isArray(venues)) {
                    venues.forEach(venue => {
                        venueSelect.innerHTML += `<option value="${venue.id}">${venue.name} - ${venue.address}</option>`;
                    });
                }
            }
        }
    } catch (e) {
        console.error('Error loading venues:', e);
    }
}

// Helper function to format date for input fields
function formatDateForInput(dateString) {
    if (!dateString) return '';
    
    // Handle different date formats
    let date;
    if (dateString.includes(' ')) {
        // Format: "2024-12-25 14:30:00" -> "2024-12-25"
        date = dateString.split(' ')[0];
    } else if (dateString.includes('T')) {
        // Format: "2024-12-25T14:30:00" -> "2024-12-25"
        date = dateString.split('T')[0];
    } else {
        // Already in correct format
        date = dateString;
    }
    
    // Validate date format (YYYY-MM-DD)
    if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return date;
    }
    
    // Try to parse and reformat if needed
    try {
        const parsedDate = new Date(dateString);
        if (!isNaN(parsedDate.getTime())) {
            return parsedDate.toISOString().split('T')[0];
        }
    } catch (e) {
        console.warn('Could not parse date:', dateString);
    }
    
    return '';
}

// ===================== END OF FILE =====================
