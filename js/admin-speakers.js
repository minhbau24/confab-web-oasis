// ===================== SPEAKERS MANAGEMENT =====================
// Thêm code xử lý cho tab Diễn giả ở đây nếu có

let speakersData = [];

function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

async function initializeSpeakersData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await fetch(`api/conference_speakers.php?conference_id=${conferenceId}`);
        const data = await res.json();
        if (data && data.status && Array.isArray(data.data)) {
            speakersData = data.data;
        } else {
            speakersData = [];
        }
    } catch (e) {
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
                <button class="btn btn-outline-primary btn-sm" onclick="editSpeaker('${speaker.id || ''}')"><i class="fas fa-edit"></i> Sửa</button>
                <button class="btn btn-outline-danger btn-sm" onclick="removeSpeaker('${speaker.id || ''}')"><i class="fas fa-trash"></i> Xóa</button>
            </div>
        </div>
    </div>
    `;
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

function initializeSpeakersTab() {
    initializeSpeakersData().then(loadSpeakers);
}

// Nếu dùng Bootstrap tab, lắng nghe sự kiện khi tab được kích hoạt
const speakersTab = document.querySelector('[data-bs-target="#speakers"]');
if (speakersTab) {
    speakersTab.addEventListener('shown.bs.tab', function () {
        initializeSpeakersTab();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const speakersTabPane = document.getElementById('speakers');
    if (speakersTabPane && speakersTabPane.classList.contains('active')) {
        initializeSpeakersTab();
    }
});

// Placeholder cho edit/remove
function editSpeaker(id) {
    const speaker = speakersData.find(s => s.id == id);
    if (!speaker) return;
    document.getElementById('edit-speaker-id').value = speaker.id;
    document.getElementById('edit-speaker-name').value = speaker.name || '';
    document.getElementById('edit-speaker-title').value = speaker.title || '';
    document.getElementById('edit-speaker-company').value = speaker.company || '';
    document.getElementById('edit-speaker-bio').value = speaker.bio || '';
    document.getElementById('edit-speaker-image').value = speaker.image || speaker.image_url || '';
    const modal = new bootstrap.Modal(document.getElementById('editSpeakerModal'));
    modal.show();
}

function removeSpeaker(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa diễn giả này?')) return;
    // Gọi API xóa
    fetch('api/conference_speakers.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            speakersData = speakersData.filter(s => s.id != id);
            loadSpeakers();
            showAdminToast('Xóa diễn giả thành công!');
        } else {
            showAdminToast('Xóa diễn giả thất bại!', true);
        }
    });
}

// Thêm mới diễn giả
const addSpeakerForm = document.getElementById('add-speaker-form');
if (addSpeakerForm) {
    addSpeakerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const newSpeaker = {
            name: document.getElementById('new-speaker-name').value,
            title: document.getElementById('new-speaker-title').value,
            company: document.getElementById('new-speaker-company').value,
            bio: document.getElementById('new-speaker-bio').value,
            image: document.getElementById('new-speaker-image').value,
            conference_id: getConferenceIdFromUrl()
        };
        fetch('api/conference_speakers.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newSpeaker)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status && data.data) {
                speakersData.push(data.data);
                loadSpeakers();
                showAdminToast('Thêm diễn giả thành công!');
                addSpeakerForm.reset();
                // Đóng modal đúng cách, tránh lỗi backdrop undefined
                setTimeout(function() {
                    var modalEl = document.getElementById('addSpeakerModal');
                    if (modalEl) {
                        var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalInstance.hide();
                    }
                }, 100);
            } else {
                showAdminToast('Thêm diễn giả thất bại!', true);
            }
        });
    });
}

// Sửa diễn giả
const editSpeakerForm = document.getElementById('edit-speaker-form');
if (editSpeakerForm) {
    editSpeakerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-speaker-id').value;
        const updatedSpeaker = {
            id,
            name: document.getElementById('edit-speaker-name').value,
            title: document.getElementById('edit-speaker-title').value,
            company: document.getElementById('edit-speaker-company').value,
            bio: document.getElementById('edit-speaker-bio').value,
            image: document.getElementById('edit-speaker-image').value,
            conference_id: getConferenceIdFromUrl()
        };
        fetch('api/conference_speakers.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updatedSpeaker)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                const idx = speakersData.findIndex(s => s.id == id);
                if (idx > -1) speakersData[idx] = updatedSpeaker;
                loadSpeakers();
                showAdminToast('Cập nhật diễn giả thành công!');
                bootstrap.Modal.getInstance(document.getElementById('editSpeakerModal')).hide();
            } else {
                showAdminToast('Cập nhật diễn giả thất bại!', true);
            }
        });
    });
}

// Toast helper
function showAdminToast(msg, isError) {
    const toastBody = document.getElementById('adminToastBody');
    toastBody.textContent = msg;
    const toast = new bootstrap.Toast(document.getElementById('adminToast'));
    document.getElementById('adminToast').classList.toggle('bg-danger', !!isError);
    document.getElementById('adminToast').classList.toggle('bg-primary', !isError);
    toast.show();
}
