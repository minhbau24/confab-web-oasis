
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
                <p class="text-muted">You haven't joined any conferences yet.</p>
                <a href="conferences.html" class="btn btn-primary">
                    Browse Conferences
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
                <p class="text-muted">No projects to display.</p>
                <button class="btn btn-primary" onclick="addProject()">
                    Add Project
                </button>
            </div>
        `;
    }
}

function addProject() {
    showToast('Add project functionality would be implemented here!', 'info');
}

function editProfile() {
    showToast('Edit profile functionality would be implemented here!', 'info');
}

// Global function for joining conferences (if not already defined)
if (typeof joinConference === 'undefined') {
    function joinConference(conferenceId) {
        const conference = getConferenceById(conferenceId);
        if (conference) {
            if (conference.attendees < conference.capacity) {
                showToast(`Successfully registered for ${conference.title}!`);
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
                showToast('Sorry, this conference is full!', 'warning');
            }
        }
    }
}
