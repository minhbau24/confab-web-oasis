
// Profile page JavaScript logic

document.addEventListener('DOMContentLoaded', function() {
    loadUserProfile();
    loadJoinedConferences();
    loadUserProjects();
});

function loadUserProfile() {
    const user = getCurrentUser();
    
    // Update user information
    document.getElementById('user-name').textContent = user.name;
    document.getElementById('user-title').textContent = user.title;
    document.getElementById('user-location').innerHTML = `
        <i class="fas fa-map-marker-alt me-1"></i>${user.location}
    `;
    
    // Update stats
    document.getElementById('conferences-joined').textContent = user.stats.conferencesJoined;
    document.getElementById('projects-count').textContent = user.stats.projectsCount;
    document.getElementById('connections-count').textContent = user.stats.connections;
}

function loadJoinedConferences() {
    const joinedConferences = getUserJoinedConferences();
    const container = document.getElementById('joined-conferences');
    
    if (joinedConferences.length > 0) {
        container.innerHTML = joinedConferences
            .map(conference => renderConferenceCard(conference))
            .join('');
    } else {
        container.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="fas fa-calendar-plus fa-2x text-muted mb-2"></i>
                <p class="text-muted">Bạn chưa tham gia hội nghị nào.</p>
                <a href="conferences.html" class="btn btn-primary">
                    Khám phá Hội nghị
                </a>
            </div>
        `;
    }
}

function loadUserProjects() {
    const user = getCurrentUser();
    const container = document.getElementById('user-projects');
    
    if (user.projects.length > 0) {
        container.innerHTML = user.projects
            .map(project => renderProjectCard(project))
            .join('');
    } else {
        container.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="fas fa-project-diagram fa-2x text-muted mb-2"></i>
                <p class="text-muted">Không có dự án nào để hiển thị.</p>
                <button class="btn btn-primary" onclick="addProject()">
                    Thêm dự án
                </button>
            </div>
        `;
    }
}

function addProject() {
    showToast('Chức năng thêm dự án sẽ được triển khai tại đây!', 'info');
}

function editProfile() {
    showToast('Chức năng chỉnh sửa hồ sơ sẽ được triển khai tại đây!', 'info');
}

// Global function for joining conferences (if not already defined)
if (typeof joinConference === 'undefined') {
    function joinConference(conferenceId) {
        const conference = getConferenceById(conferenceId);
        if (conference) {
            if (conference.attendees < conference.capacity) {
                showToast(`Đăng ký thành công cho hội nghị ${conference.title}!`);
                conference.attendees += 1;
                
                const user = getCurrentUser();
                if (!user.joinedConferences.includes(conferenceId)) {
                    user.joinedConferences.push(conferenceId);
                    user.stats.conferencesJoined += 1;
                }
                
                // Reload sections to show updated data
                loadUserProfile();
                loadJoinedConferences();
            } else {
                showToast('Rất tiếc, hội nghị này đã đủ số lượng!', 'warning');
            }
        }
    }
}
