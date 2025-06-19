
// Home page JavaScript logic

document.addEventListener('DOMContentLoaded', function() {
    loadUpcomingConferences();
});

function loadUpcomingConferences() {
    const upcomingConferences = getUpcomingConferences(3);
    const container = document.getElementById('upcoming-conferences');
    
    if (upcomingConferences.length > 0) {
        container.innerHTML = upcomingConferences
            .map(conference => renderConferenceCard(conference))
            .join('');
    } else {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No upcoming conferences</h4>
                <p class="text-muted">Check back later for new events!</p>
            </div>
        `;
    }
}

// Global function for joining conferences
function joinConference(conferenceId) {
    const conference = getConferenceById(conferenceId);
    if (conference) {
        if (conference.attendees < conference.capacity) {
            // Simulate joining (in real app, this would be an API call)
            showToast(`Successfully registered for ${conference.title}!`);
            
            // Update attendee count (simulate)
            conference.attendees += 1;
            
            // Add to user's joined conferences if not already joined
            const user = getCurrentUser();
            if (!user.joinedConferences.includes(conferenceId)) {
                user.joinedConferences.push(conferenceId);
                user.stats.conferencesJoined += 1;
            }
        } else {
            showToast('Sorry, this conference is full!', 'warning');
        }
    } else {
        showToast('Conference not found!', 'danger');
    }
}

// Global function for sharing conferences
function shareConference(conferenceId) {
    const conference = getConferenceById(conferenceId);
    if (conference) {
        const url = `${window.location.origin}/conference-detail.html?id=${conferenceId}`;
        
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
            });
        }
    }
}
