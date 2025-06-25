// --- Quản lý Người tham dự (Attendees) ---
let attendeesData = [];

function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

// Lấy danh sách người tham dự từ API
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
        attendeesData = [];
    }
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
    if (tableBody) tableBody.innerHTML = attendeesData
        .map(attendee => renderAttendeeRow(attendee))
        .join('');
}

// --- Map trạng thái attendee sang tiếng Việt và badge màu (chuẩn theo schema registrations) ---
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

function renderAttendeeRow(attendee) {
    const statusObj = translateAttendeeStatus(attendee.status);
    const registrationDate = attendee.registration_date ? new Date(attendee.registration_date).toLocaleDateString('vi-VN') : '';
    // Map loại vé sang tiếng Việt
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

function filterAttendees() {
    const searchTerm = document.getElementById('attendee-search').value.toLowerCase();
    const rows = document.querySelectorAll('#attendees-table tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Thêm người tham dự mới (gọi API)
async function addAttendee() {
    const name = document.getElementById('attendeeName').value;
    const email = document.getElementById('attendeeEmail').value;
    const status = document.getElementById('attendeeStatus').value;
    if (!name || !email || !status) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'danger');
        return;
    }
    // Check for duplicate email (trên client, có thể bỏ nếu backend đã kiểm tra)
    if (attendeesData.some(attendee => attendee.email === email)) {
        showToast('Email này đã được sử dụng bởi người tham dự khác!', 'warning');
        return;
    }
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await fetch('api/attendees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conference_id: conferenceId, name, email, status })
        });
        const data = await res.json();
        if (data && data.status) {
            showToast(`${name} đã được thêm vào danh sách người tham dự!`, 'success');
            await initializeAttendeesData();
            loadAttendeesTable();
            loadSummaryStats && loadSummaryStats();
            document.getElementById('addAttendeeForm').reset();
            const modal = bootstrap.Modal.getInstance(document.getElementById('addAttendeeModal'));
            modal.hide();
        } else {
            showToast('Có lỗi khi thêm người tham dự!', 'danger');
        }
    } catch (e) {
        showToast('Có lỗi khi thêm người tham dự!', 'danger');
    }
}

// Sửa attendee: mở modal, load dữ liệu lên form
function editAttendee(attendeeId) {
    const attendee = attendeesData.find(a => a.id === attendeeId);
    // Kiểm tra modal và các input trước khi set value
    const modal = document.getElementById('editAttendeeModal');
    const idInput = document.getElementById('editAttendeeId');
    const nameInput = document.getElementById('editAttendeeName');
    const emailInput = document.getElementById('editAttendeeEmail');
    const ticketTypeInput = document.getElementById('editAttendeeTicketType');
    const statusInput = document.getElementById('editAttendeeStatus');
    if (attendee && modal && idInput && nameInput && emailInput && ticketTypeInput && statusInput) {
        idInput.value = attendee.id;
        nameInput.value = attendee.name || '';
        emailInput.value = attendee.email || '';
        ticketTypeInput.value = attendee.ticket_type || 'regular';
        statusInput.value = attendee.status || 'draft';
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } else {
        showToast('Không thể mở form sửa người tham dự. Vui lòng kiểm tra lại giao diện!', 'danger');
    }
}

// Lưu thay đổi attendee (gọi API)
async function saveAttendeeChanges() {
    const id = document.getElementById('editAttendeeId').value;
    const ticket_type = document.getElementById('editAttendeeTicketType').value;
    const status = document.getElementById('editAttendeeStatus').value;
    if (!id || !ticket_type || !status) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'danger');
        return;
    }
    try {
        const res = await fetch('api/attendees.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, ticket_type, status })
        });
        const data = await res.json();
        if (data && data.status) {
            showToast('Cập nhật người tham dự thành công!', 'success');
            await initializeAttendeesData();
            loadAttendeesTable();
            loadSummaryStats && loadSummaryStats();
            const modal = bootstrap.Modal.getInstance(document.getElementById('editAttendeeModal'));
            modal.hide();
        } else {
            showToast('Có lỗi khi cập nhật người tham dự!', 'danger');
        }
    } catch (e) {
        showToast('Có lỗi khi cập nhật người tham dự!', 'danger');
    }
}

// Xóa người tham dự (gọi API)
async function removeAttendee(attendeeId) {
    const attendee = attendeesData.find(a => a.id === attendeeId);
    if (attendee) {
        if (confirm(`Bạn có chắc chắn muốn xóa ${attendee.name} khỏi hội nghị này?`)) {
            try {
                const res = await fetch('api/attendees.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: attendeeId })
                });
                const data = await res.json();
                if (data && data.status) {
                    showToast(`${attendee.name} đã được xóa khỏi hội nghị.`, 'success');
                    await initializeAttendeesData();
                    loadAttendeesTable();
                    loadSummaryStats && loadSummaryStats();
                } else {
                    showToast('Có lỗi khi xóa người tham dự!', 'danger');
                }
            } catch (e) {
                showToast('Có lỗi khi xóa người tham dự!', 'danger');
            }
        }
    }
}

// --- Khởi tạo tab Người tham dự khi chuyển tab hoặc khi trang load ---
function initializeAttendeesTab() {
    initializeAttendeesData().then(loadAttendeesTable);
}

// Nếu dùng Bootstrap tab, lắng nghe sự kiện khi tab được kích hoạt
const attendeesTab = document.querySelector('[data-bs-target="#attendees"]');
if (attendeesTab) {
    attendeesTab.addEventListener('shown.bs.tab', function () {
        initializeAttendeesTab();
    });
}

// Khi trang load lần đầu, nếu tab attendees đang active thì cũng load luôn
// KHÔNG GỌI TRÙNG với conference-admin.js
// Thay vào đó, chỉ setup event listener cho tab switch
document.addEventListener('DOMContentLoaded', function () {
    console.log('Admin attendees JS loaded');
    // Chỉ setup tab switch event, không init toàn bộ app
    const attendeesTab = document.querySelector('[data-bs-target="#attendees"]');
    if (attendeesTab) {
        attendeesTab.addEventListener('shown.bs.tab', function () {
            initializeAttendeesTab();
        });
    }
    
    // Check nếu attendees tab đang active từ đầu
    const attendeesTabPane = document.getElementById('attendees');
    if (attendeesTabPane && attendeesTabPane.classList.contains('active')) {
        setTimeout(() => initializeAttendeesTab(), 100); // Delay nhỏ để đảm bảo currentConference đã được set
    }
});
