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
});

function editObjective(idx) {
    alert('Chức năng chỉnh sửa mục tiêu sẽ được bổ sung sau!');
}
function removeObjective(idx) {
    alert('Chức năng xóa mục tiêu sẽ được bổ sung sau!');
}
