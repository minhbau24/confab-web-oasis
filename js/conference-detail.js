
// Conference detail page JavaScript logic

document.addEventListener('DOMContentLoaded', function() {
    loadConferenceDetail();
});

function loadConferenceDetail() {
    // Get conference ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (conferenceId) {
        const conference = getConferenceById(parseInt(conferenceId));
        
        if (conference) {
            // Update page title
            document.title = `ConferenceHub - ${conference.title}`;
            
            // Render conference details
            const container = document.getElementById('conference-details');
            container.innerHTML = renderConferenceDetail(conference);
        } else {
            // Conference not found
            showConferenceNotFound();
        }
    } else {
        // No ID provided
        showConferenceNotFound();
    }
}

function showConferenceNotFound() {
    const container = document.getElementById('conference-details');
    container.innerHTML = `
        <div class="row justify-content-center">
            <div class="col-md-6 text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h2>Conference Not Found</h2>
                <p class="text-muted mb-4">The conference you're looking for doesn't exist or has been removed.</p>
                <a href="conferences.html" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Conferences
                </a>
            </div>
        </div>
    `;
}

//Global functions for conference actions
function joinConference(conferenceId) {
    const conference = getConferenceById(conferenceId);
    if (conference) {
        if (conference.attendees < conference.capacity) {
            showToast(`Successfully registered for ${conference.title}!`);
            
            // Update attendee count
            conference.attendees += 1;
            
            // Add to user's joined conferences
            const user = getCurrentUser();
            if (!user.joinedConferences.includes(conferenceId)) {
                user.joinedConferences.push(conferenceId);
                user.stats.conferencesJoined += 1;
            }
            
            // Reload the page to show updated information
            loadConferenceDetail();
        } else {
            showToast('Sorry, this conference is full!', 'warning');
        }
    }
}

function shareConference(conferenceId) {
    const conference = getConferenceById(conferenceId);
    if (conference) {
        const url = window.location.href;
        
        if (navigator.share) {
            navigator.share({
                title: conference.title,
                text: conference.description,
                url: url
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(url).then(() => {
                showToast('Conference link copied to clipboard!');
            }).catch(() => {
                // Manual fallback
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('Conference link copied to clipboard!');
            });
        }
    }
}
