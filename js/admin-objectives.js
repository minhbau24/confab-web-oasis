// Quản lý Mục tiêu (Objectives)
// Thêm code xử lý cho tab Mục tiêu ở đây

// ===================== OBJECTIVES MANAGEMENT =====================

let objectivesData = [];

function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

async function initializeObjectivesData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await fetch(`api/conference_detail.php?id=${conferenceId}`);
        const data = await res.json();
        if (data && data.status && data.data && Array.isArray(data.data.objectives)) {
            objectivesData = data.data.objectives;
        } else {
            objectivesData = [];
        }
    } catch (e) {
        objectivesData = [];
    }
}

function renderObjectiveRow(objective, idx) {
    return `
        <tr>
            <td>${idx + 1}</td>
            <td>${objective.text || objective}</td>
            <td class="text-end">
                <button class="btn btn-outline-primary btn-sm me-1" onclick="editObjective(${idx})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-outline-danger btn-sm" onclick="removeObjective(${idx})"><i class="fas fa-trash"></i></button>
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
        <tbody>
    `;
    if (objectivesData.length === 0) {
        html += '<tr><td colspan="3" class="text-center text-muted">Chưa có mục tiêu nào.</td></tr>';
    } else {
        html += objectivesData.map(renderObjectiveRow).join('');
    }
    html += '</tbody>';
    table.innerHTML = html;
}

function initializeObjectivesTab() {
    initializeObjectivesData().then(loadObjectivesTable);
}

const objectivesTab = document.querySelector('[data-bs-target="#objectives"]');
if (objectivesTab) {
    objectivesTab.addEventListener('shown.bs.tab', function () {
        initializeObjectivesTab();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const objectivesTabPane = document.getElementById('objectives');
    if (objectivesTabPane && objectivesTabPane.classList.contains('active')) {
        initializeObjectivesTab();
    }

    // Gắn event submit cho form thêm mục tiêu
    const addObjectiveForm = document.getElementById('add-objective-form');
    if (addObjectiveForm) {
        addObjectiveForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const input = document.getElementById('new-objective-text');
            const text = input.value.trim();
            if (!text) {
                alert('Vui lòng nhập nội dung mục tiêu!');
                return;
            }
            const conferenceId = getConferenceIdFromUrl();
            try {
                const res = await fetch('api/conference_detail.php?id=' + conferenceId, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add_objective', text })
                });
                const data = await res.json();
                if (data && data.status) {
                    input.value = '';
                    reloadObjectivesList();
                    showToast('Thêm mục tiêu thành công!', 'success');
                    // Đóng modal nếu có
                    const modal = document.getElementById('addObjectiveModal');
                    if (modal) {
                        const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                        modalInstance.hide();
                    }
                } else {
                    alert(data && data.message ? data.message : 'Thêm mục tiêu thất bại!');
                }
            } catch (err) {
                console.error('Error adding objective:', err);
                alert('Lỗi kết nối API!');
            }
        });
    }
});

// Hiện modal sửa mục tiêu
function editObjective(idx) {
    const obj = objectivesData[idx];
    if (!obj) return;
    // Lấy modal sửa mục tiêu đã có trong HTML
    let modal = document.getElementById('editObjectiveModal');
    if (!modal) return;
    // Hiện modal
    const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
    // Gán dữ liệu vào input
    const inputEdit = modal.querySelector('#edit-objective-text');
    if (inputEdit) inputEdit.value = obj.text;
    // Gán id mục tiêu vào hidden input
    const inputId = modal.querySelector('#edit-objective-id');
    if (inputId) inputId.value = obj.id;
    // Gán lại sự kiện cho nút cập nhật (btn-edit-objective)
    const saveBtn = modal.querySelector('#btn-edit-objective');
    if (saveBtn) {
        // Xóa sự kiện cũ nếu có
        saveBtn.onclick = null;
        saveBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const newText = inputEdit.value.trim();
            if (!newText) {
                showToast('Vui lòng nhập nội dung mục tiêu!', 'error');
                return;
            }
            const conferenceId = getConferenceIdFromUrl();
            fetch('api/conference_detail.php?id=' + conferenceId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'edit_objective', objective_id: obj.id, text: newText })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.status) {
                    showToast('Cập nhật mục tiêu thành công!', 'success');
                    reloadObjectivesList();
                    bootstrap.Modal.getInstance(modal).hide();
                } else {
                    showToast(data && data.message ? data.message : 'Cập nhật thất bại!', 'error');
                }
            })
            .catch(() => showToast('Lỗi kết nối API!', 'error'));
            return false;
        };
    }
    modalInstance.show();
}

// Hiện modal xác nhận xóa mục tiêu ở giữa màn hình
function showDeleteObjectiveConfirm(message, onConfirm) {
    let modal = document.getElementById('confirmDeleteObjectiveModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'confirmDeleteObjectiveModal';
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title">Xác nhận xóa mục tiêu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                        <div id="deleteObjectiveMessage"></div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-danger" id="btn-confirm-delete-objective">Xóa</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);
    }
    modal.querySelector('#deleteObjectiveMessage').innerText = message;
    const confirmBtn = modal.querySelector('#btn-confirm-delete-objective');
    confirmBtn.onclick = null;
    confirmBtn.onclick = function() {
        bootstrap.Modal.getInstance(modal).hide();
        if (typeof onConfirm === 'function') onConfirm();
    };
    const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
    modalInstance.show();
}

// Xác nhận và xóa mục tiêu (CÓ xác nhận)
function removeObjective(idx) {
    const obj = objectivesData[idx];
    if (!obj) return;
    showDeleteObjectiveConfirm('Bạn có chắc chắn muốn xóa mục tiêu này?', function() {
        const conferenceId = getConferenceIdFromUrl();
        fetch('api/conference_detail.php?id=' + conferenceId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_objective', objective_id: obj.id })
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
    });
}

// Hàm reload lại danh sách mục tiêu (dùng sau khi thêm/sửa/xóa)
function reloadObjectivesList() {
    initializeObjectivesData().then(loadObjectivesTable);
}

// Đã loại bỏ mọi code reload trang, chỉ dùng reloadObjectivesList() để cập nhật danh sách mục tiêu.
// Ví dụ sử dụng:
// Sau khi thêm mới mục tiêu thành công:
// reloadObjectivesList();
// Sau khi cập nhật mục tiêu thành công:
// reloadObjectivesList();

// Hàm hiển thị toast thông báo ở góc màn hình
function showToast(message, type = 'success') {
    let toast = document.createElement('div');
    toast.className = `custom-toast custom-toast-${type}`;
    toast.innerText = message;
    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '24px', // chuyển sang góc dưới
        right: '24px',
        zIndex: 9999,
        background: type === 'success' ? '#198754' : '#dc3545',
        color: '#fff',
        padding: '12px 24px',
        borderRadius: '6px',
        boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
        fontSize: '1rem',
        opacity: 0,
        transition: 'opacity 0.3s',
        pointerEvents: 'none',
    });
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = 1; }, 10);
    setTimeout(() => {
        toast.style.opacity = 0;
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}
