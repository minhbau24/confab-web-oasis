// ===================== AGENDA MANAGEMENT =====================
// Thêm code xử lý cho tab Lịch trình ở đây nếu có

let agendaData = [];
let editingSessionId = null;

function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

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
        console.error('[ERROR] Failed to initialize agenda data:', e);
        agendaData = [];
    }
}

function renderAgendaRow(session) {
    // Tính thời lượng (phút)
    let duration = '';
    if (session.start_time && session.end_time) {
        const [h1, m1] = session.start_time.split(':').map(Number);
        const [h2, m2] = session.end_time.split(':').map(Number);
        duration = ((h2 * 60 + m2) - (h1 * 60 + m1)) + ' phút';
    }
    // Hiển thị ngày tháng (session_date)
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
                <button class="btn btn-sm btn-outline-primary" onclick="editAgendaSession(${session.id})"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-outline-danger" onclick="removeAgendaSession(${session.id})"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
    `;
}

function loadAgendaTable() {
    const agendaContainer = document.getElementById('agenda-management');
    if (!agendaContainer) return;
    // Header mới: Ngày, Bắt đầu, Thời lượng, Tiêu đề, Diễn giả, Loại, Phòng, Thao tác
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
    <hr class="my-2" />
    `;
    let html = '';
    if (agendaData.length === 0) {
        html = '<div class="text-center text-muted">Chưa có lịch trình nào.</div>';
    } else {
        html = agendaData.map(renderAgendaRow).join('');
    }
    agendaContainer.innerHTML = headerHtml + html;
}

function initializeAgendaTab() {
    initializeAgendaData().then(loadAgendaTable);
}

// ===================== CRUD SESSION =====================

document.addEventListener('DOMContentLoaded', function () {
    // Nút mở modal thêm phiên
    const addSessionBtn = document.querySelector('#agenda .btn-success[data-bs-toggle="modal"]');
    if (addSessionBtn) {
        addSessionBtn.addEventListener('click', function () {
            editingSessionId = null;
            clearSessionModal();
            // XÓA gọi loadSpeakersToSelect() ở đây, sẽ gọi khi modal mở xong
            const modal = new bootstrap.Modal(document.getElementById('addSessionModal'));
            modal.show();
        });
    }
    // Nút xác nhận thêm phiên
    const addSessionModal = document.getElementById('addSessionModal');
    if (addSessionModal) {
        const confirmBtn = addSessionModal.querySelector('.btn-success');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', async function () {
                await handleAddOrEditSession();
            });
        }
        // Thêm lắng nghe sự kiện modal mở xong để load speakers
        addSessionModal.addEventListener('shown.bs.modal', function () {
            // Nếu đang sửa thì truyền id, nếu thêm mới thì không
            loadSpeakersToSelect(editingSessionId ? agendaData.find(s => s.id == editingSessionId)?.speaker_id : null);
        });
    }
});

function clearSessionModal() {
    const modal = document.getElementById('addSessionModal');
    if (!modal) return;
    modal.querySelector('input[type="text"]').value = '';
    modal.querySelector('input[type="time"]').value = '';
    modal.querySelector('input[type="number"]').value = '';
    modal.querySelector('select').selectedIndex = 0;
    modal.querySelector('textarea').value = '';
}

// Lấy danh sách diễn giả và đổ vào select[name='session-speaker']
async function loadSpeakersToSelect(selectedId = null) {
    console.log('[DEBUG][loadSpeakersToSelect] Bắt đầu load danh sách diễn giả');
    const modal = document.getElementById('addSessionModal');
    if (!modal) return;
    console.log('[DEBUG][loadSpeakersToSelect] Tìm select[name="session-speaker"] trong modal');
    const select = modal.querySelector('select[name="session-speaker"]');
    if (!select) {
        console.warn('[WARNING][loadSpeakersToSelect] Không tìm thấy select[name="session-speaker"] trong modal');
        return;
    }
    console.log('[DEBUG][loadSpeakersToSelect] Đã tìm thấy select, bắt đầu gọi API để lấy danh sách diễn giả');
    const conferenceId = getConferenceIdFromUrl();
    select.innerHTML = '<option value="">Chọn Diễn giả</option>';
    try {
        const res = await apiGet(`api/conference_speakers.php?conference_id=${conferenceId}`);
        if (res && res.status && Array.isArray(res.data)) {
            res.data.forEach(speaker => {
                // Chỉ render option nếu có id số nguyên hợp lệ
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
        // Nếu không có diễn giả nào, vẫn giữ option mặc định
        if (select.options.length === 1) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Chưa có diễn giả nào';
            select.appendChild(option);
        }
        console.log('[DEBUG][loadSpeakersToSelect] Đã load danh sách diễn giả:', res.data);
    } catch (e) {
        // Nếu lỗi thì giữ lại option mặc định
        console.error('[ERROR][loadSpeakersToSelect]', e);
    }
}

async function handleAddOrEditSession() {
    const modal = document.getElementById('addSessionModal');
    if (!modal) return;
    const title = modal.querySelector('input[type="text"]').value.trim();
    const sessionDate = modal.querySelector('input[type="date"]').value;
    const startTime = modal.querySelectorAll('input[type="time"]')[0].value;
    const duration = modal.querySelector('input[type="number"]').value;
    // Lấy thêm các trường nâng cao nếu có
    const type = modal.querySelector('select[name="session-type"]') ? modal.querySelector('select[name="session-type"]').value : 'presentation';
    const room = modal.querySelector('input[name="session-room"]') ? modal.querySelector('input[name="session-room"]').value : null;
    // Lấy speaker_id đúng chuẩn (id số nguyên)
    const speakerSelect = modal.querySelector('select[name="session-speaker"]');
    let speakerId = speakerSelect ? speakerSelect.value : null;
    if (speakerId === '' || speakerId === null || isNaN(Number(speakerId))) speakerId = null;
    else speakerId = parseInt(speakerId);
    const description = modal.querySelector('textarea').value.trim();
    // Validate
    if (!title || !sessionDate || !startTime || !duration) {
        showToast('Vui lòng nhập đầy đủ thông tin bắt buộc!', 'warning');
        return;
    }
    const conferenceId = getConferenceIdFromUrl();
    // Tính end_time
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
    // Log debug giá trị gửi lên API
    console.log('[DEBUG][handleAddOrEditSession] sessionData gửi lên API:', sessionData);
    let endpoint = 'api/conference_schedule.php';
    let method = editingSessionId ? 'PUT' : 'POST';
    if (editingSessionId) sessionData.id = editingSessionId;
    try {
        let res;
        if (method === 'POST') {
            res = await apiPost(endpoint, sessionData);
        } else {
            res = await apiPost(endpoint + '?_method=PUT', sessionData); // giả lập PUT
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
        showToast('Lỗi khi lưu phiên!', 'error');
    }
}

// Sửa phiên
window.editAgendaSession = function(id) {
    const session = agendaData.find(s => s.id == id);
    if (!session) return;
    editingSessionId = id;
    const modal = document.getElementById('addSessionModal');
    if (!modal) return;
    // KHÔNG gọi loadSpeakersToSelect ở đây nữa, sẽ gọi khi modal mở xong
    modal.querySelector('input[type="text"]').value = session.title || '';
    modal.querySelectorAll('input[type="time"]')[0].value = session.start_time ? session.start_time.substring(0,5) : '';
    let duration = '';
    if (session.start_time && session.end_time) {
        const [h1, m1] = session.start_time.split(':').map(Number);
        const [h2, m2] = session.end_time.split(':').map(Number);
        duration = (h2 * 60 + m2) - (h1 * 60 + m1);
    }
    modal.querySelector('input[type="number"]').value = duration;
    // Set session_date
    if (modal.querySelector('input[type="date"]')) {
        modal.querySelector('input[type="date"]').value = session.session_date || '';
    }
    // Set type
    if (modal.querySelector('select[name="session-type"]')) {
        modal.querySelector('select[name="session-type"]').value = session.type || '';
    }
    // Set room
    if (modal.querySelector('input[name="session-room"]')) {
        modal.querySelector('input[name="session-room"]').value = session.room || '';
    }
    
    // Bỏ set speaker_id thủ công ở đây vì đã truyền vào loadSpeakersToSelect
    modal.querySelector('textarea').value = session.description || '';
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
};

// Hiện modal xác nhận xóa phiên ở giữa màn hình
function showDeleteConfirmModal(onConfirm) {
    const modalEl = document.getElementById('confirmDeleteSessionModal');
    if (!modalEl) return;
    const btnConfirm = document.getElementById('btn-confirm-delete-session-modal');
    // Xóa sự kiện cũ nếu có
    if (btnConfirm) {
        const newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        newBtn.onclick = function() {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.hide();
            onConfirm();
        };
    }
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
}

// Xóa phiên
window.removeAgendaSession = async function(id) {
    showDeleteConfirmModal(async () => {
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
            showToast('Lỗi khi xóa phiên!', 'error');
        }
    });
};

// ========== TOAST NOTIFICATION =============
function showToast(message, type = 'success', duration = 3000) {
    const toastEl = document.getElementById('adminToast');
    const toastBody = document.getElementById('adminToastBody');
    if (!toastEl || !toastBody) return;
    toastBody.innerHTML = message;
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-primary', 'bg-warning');
    if (type === 'success') toastEl.classList.add('bg-success');
    else if (type === 'error') toastEl.classList.add('bg-danger');
    else if (type === 'warning') toastEl.classList.add('bg-warning');
    else toastEl.classList.add('bg-primary');
    const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
    toast.show();
    if (duration > 0) {
        setTimeout(() => toast.hide(), duration);
    }
}

// Đảm bảo luôn lắng nghe sự kiện chuyển tab để load lại dữ liệu lịch trình

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-link[data-bs-toggle="pill"]').forEach(function(tabBtn) {
        tabBtn.addEventListener('shown.bs.tab', function (event) {
            if (event.target.dataset.bsTarget === "#agenda") {
                initializeAgendaTab();
            }
        });
    });
});

// Khi trang load lần đầu, luôn kiểm tra và load dữ liệu nếu tab agenda đang hiện
function ensureAgendaTabLoaded() {
    const agendaTabPane = document.getElementById('agenda');
    if (agendaTabPane && (agendaTabPane.classList.contains('active') || agendaTabPane.style.display !== 'none')) {
        initializeAgendaTab();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    ensureAgendaTabLoaded();
});

document.addEventListener('hidden.bs.modal', function (event) {
    // Xóa backdrop nếu còn sót lại
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style = '';
});
