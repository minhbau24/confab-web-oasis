// ===================== RESOURCES MANAGEMENT =====================
// Quản lý Tài liệu (Resources) cho từng conference

const RES_API = 'api/resources.php';
const RES_TAB_ID = '#resources';

function getCurrentConferenceId() {
    return localStorage.getItem('currentConferenceId') || 1;
}

// Render danh sách tài liệu
function renderResources(data) {
    const cols = document.querySelectorAll(`${RES_TAB_ID} .col-md-4 .management-card`);
    if (!cols.length) return;
    cols.forEach(col => col.innerHTML = '');
    data.forEach((item, idx) => {
        if (cols[idx]) {
            cols[idx].innerHTML = `
                <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                <h6>${item.title || item.file_name}</h6>
                <p class="text-muted small">${(item.file_size/1024).toFixed(2)} MB • ${item.extension.toUpperCase()}</p>
                <div class="btn-group" role="group">
                    <a href="${item.full_url || item.file_path}" class="btn btn-outline-info btn-sm" target="_blank"><i class="fas fa-download me-1"></i>Tải về</a>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteResource(${item.id})"><i class="fas fa-trash me-1"></i>Xóa</button>
                </div>
            `;
        }
    });
}

// Fetch danh sách tài liệu
function fetchResources() {
    const id = getCurrentConferenceId();
    fetch(`${RES_API}?conference_id=${id}`)
        .then(res => res.json())
        .then(json => {
            if (json.success && json.data) {
                renderResources(json.data);
            }
        });
}

// Xóa tài liệu
function deleteResource(id) {
    if (!confirm('Bạn có chắc muốn xóa tài liệu này?')) return;
    fetch(`${RES_API}?id=${id}`, { method: 'DELETE' })
        .then(res => res.json())
        .then(json => {
            if (json.success) fetchResources();
            else alert('Lỗi xóa tài liệu!');
        });
}

// Thêm mới tài liệu (chỉ metadata, không upload file thực tế)
function setupResourceUpload() {
    const form = document.getElementById('uploadResourceModal')?.querySelector('form');
    if (!form) return;
    form.onsubmit = function(e) {
        e.preventDefault();
        const id = getCurrentConferenceId();
        const fileInput = form.querySelector('input[type="file"]');
        const titleInput = form.querySelector('input[name="resource-title"]');
        const descInput = form.querySelector('textarea[name="resource-description"]');
        const file = fileInput.files[0];
        if (!file) return alert('Vui lòng chọn file!');
        // Giả lập upload: chỉ lưu metadata
        const data = {
            conference_id: id,
            file_name: file.name,
            original_name: file.name,
            file_path: 'public/' + file.name,
            full_url: '',
            file_size: file.size,
            file_type: file.type,
            extension: file.name.split('.').pop(),
            media_type: 'document',
            title: titleInput.value,
            description: descInput.value
        };
        fetch(RES_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(json => {
            if (json.success) {
                fetchResources();
                alert('Thêm tài liệu thành công!');
                form.reset();
            } else {
                alert('Lỗi thêm tài liệu!');
            }
        });
    };
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector(RES_TAB_ID)) {
        fetchResources();
        setupResourceUpload();
    }
});
