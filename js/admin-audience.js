// ===================== TARGET AUDIENCE MANAGEMENT =====================
// Quản lý Đối tượng tham dự (Target Audience)

let audienceData = [];

function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

async function initializeAudienceData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await fetch(`api/conference_detail.php?id=${conferenceId}`);
        const data = await res.json();
        if (data && data.status && data.data && Array.isArray(data.data.audience)) {
            audienceData = data.data.audience;
        } else {
            audienceData = [];
        }
    } catch (e) {
        audienceData = [];
    }
}

function renderAudienceCard(audience) {
    return `
        <div class="col-md-4 mb-4">
            <div class="audience-card management-card p-4 text-center" data-id="${audience.id}">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <i class="${audience.icon || 'fas fa-users'} fa-2x me-2"></i>
                    <h5 class="mb-0">${audience.title || ''}</h5>
                </div>
                <p class="mb-3">${audience.description || ''}</p>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-outline-primary btn-sm me-1" onclick="editAudience(${audience.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-sm" onclick="removeAudience(${audience.id})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    `;
}

function loadAudienceCards() {
    const container = document.getElementById('target-audience-container');
    if (!container) return;
    let html = '';
    if (audienceData.length === 0) {
        html = '<div class="col-12 text-center text-muted">Chưa có đối tượng tham dự nào.</div>';
    } else {
        html = audienceData.map(renderAudienceCard).join('');
    }
    container.innerHTML = html;
}

function initializeAudienceTab() {
    initializeAudienceData().then(loadAudienceCards);
}

const audienceTab = document.querySelector('[data-bs-target="#target-audience"]');
if (audienceTab) {
    audienceTab.addEventListener('shown.bs.tab', function () {
        initializeAudienceTab();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const audienceTabPane = document.getElementById('target-audience');
    if (audienceTabPane && audienceTabPane.classList.contains('active')) {
        initializeAudienceTab();
    }
});

function editAudience(id) {
    alert('Chức năng chỉnh sửa đối tượng sẽ được bổ sung sau!');
}
function removeAudience(id) {
    alert('Chức năng xóa đối tượng sẽ được bổ sung sau!');
}
