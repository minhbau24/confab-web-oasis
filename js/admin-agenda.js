// ===================== AGENDA MANAGEMENT =====================
// Thêm code xử lý cho tab Lịch trình ở đây nếu có

let agendaData = [];

function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

async function initializeAgendaData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await fetch(`api/conference_schedule.php?conference_id=${conferenceId}`);
        const data = await res.json();
        if (data && data.status && Array.isArray(data.data)) {
            agendaData = data.data;
        } else {
            agendaData = [];
        }
    } catch (e) {
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
    return `
    <div class="row mb-3 align-items-center text-center p-2 bg-light rounded">
        <div class="col-md-1">${session.start_time ? session.start_time.substring(0,5) : ''}</div>
        <div class="col-md-1">${session.end_time ? session.end_time.substring(0,5) : ''}</div>
        <div class="col-md-3">${session.title || ''}</div>
        <div class="col-md-2">${session.speaker_name || ''}</div>
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
    // Tạo header cho bảng với cột Bắt đầu và Kết thúc nhỏ hơn
    const headerHtml = `
    <div class="row mb-3 fw-bold text-center">
        <div class="col-md-1">Bắt đầu</div>
        <div class="col-md-1">Kết thúc</div>
        <div class="col-md-3">Tiêu đề</div>
        <div class="col-md-2">Diễn giả</div>
        <div class="col-md-1">Loại</div>
        <div class="col-md-2">Phòng</div>
        <div class="col-md-1">Cấp độ</div>
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

// Nếu dùng Bootstrap tab, lắng nghe sự kiện khi tab được kích hoạt
const agendaTab = document.querySelector('[data-bs-target="#agenda"]');
if (agendaTab) {
    agendaTab.addEventListener('shown.bs.tab', function () {
        initializeAgendaTab();
    });
}

// Khi trang load lần đầu, nếu tab agenda đang active thì cũng load luôn
document.addEventListener('DOMContentLoaded', function () {
    const agendaTabPane = document.getElementById('agenda');
    if (agendaTabPane && agendaTabPane.classList.contains('active')) {
        initializeAgendaTab();
    }
});

// Placeholder cho edit/remove
function editAgendaSession(id) {
    alert('Chức năng chỉnh sửa phiên sẽ được bổ sung sau!');
}
function removeAgendaSession(id) {
    alert('Chức năng xóa phiên sẽ được bổ sung sau!');
}
