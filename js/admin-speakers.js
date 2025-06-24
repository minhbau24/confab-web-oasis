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
    alert('Chức năng chỉnh sửa diễn giả sẽ được bổ sung sau!');
}
function removeSpeaker(id) {
    alert('Chức năng xóa diễn giả sẽ được bổ sung sau!');
}
