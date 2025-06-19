
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
            
            // Update hero section
            updateHeroSection(conference);
            
            // Render conference details
            updateConferenceDetails(conference);
            
            // Load speakers
            loadSpeakers(conference);
            
            // Load agenda
            loadAgenda(conference);
        } else {
            // Conference not found
            showConferenceNotFound();
        }
    } else {
        // No ID provided
        showConferenceNotFound();
    }
}

function updateHeroSection(conference) {
    const dateFormatted = new Date(conference.date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const endDateFormatted = conference.endDate ? 
        ' - ' + new Date(conference.endDate).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';

    const priceFormatted = conference.price.toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD'
    });

    // Update hero section content
    document.getElementById('conference-category').textContent = conference.category;
    document.getElementById('conference-title').textContent = conference.title;
    document.getElementById('conference-description').textContent = conference.description;
    document.getElementById('conference-date').textContent = dateFormatted + endDateFormatted;
    document.getElementById('conference-location').textContent = conference.location;
    document.getElementById('conference-attendees').textContent = `${conference.attendees}/${conference.capacity} attendees`;
    document.getElementById('conference-price').textContent = priceFormatted;
    document.getElementById('spots-remaining').textContent = `${conference.capacity - conference.attendees} spots remaining`;
    
    // Update hero background
    const heroSection = document.getElementById('hero-section');
    heroSection.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('${conference.image}')`;
}

function updateConferenceDetails(conference) {
    // Update detailed description
    document.getElementById('detailed-description').textContent = conference.description;
}

function loadSpeakers(conference) {
    const speakersContainer = document.getElementById('speakers-section');
    
    const speakersHtml = conference.speakers.map(speaker => `
        <div class="col-md-6 mb-4">
            <div class="speaker-card card h-100">
                <div class="card-body text-center">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=120&h=120&fit=crop&crop=face" 
                         class="rounded-circle mb-3" width="120" height="120" alt="${speaker.name}">
                    <h5 class="card-title">${speaker.name}</h5>
                    <p class="text-primary mb-2">${speaker.title}</p>
                    <p class="card-text">${speaker.bio}</p>
                    <button class="btn btn-outline-primary btn-sm" onclick="showSpeakerModal('${speaker.name}')">
                        View More
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    speakersContainer.innerHTML = speakersHtml;
}

function loadAgenda(conference) {
    const agendaContainer = document.getElementById('agenda-day1');
    
    const agendaHtml = conference.schedule.map(item => `
        <div class="timeline-item">
            <h6>${item.time}</h6>
            <h5>${item.title}</h5>
            ${item.speaker ? `<p class="text-muted">Speaker: ${item.speaker}</p>` : ''}
        </div>
    `).join('');
    
    agendaContainer.innerHTML = agendaHtml;
}

function showSpeakerModal(speakerName) {
    // This would show a modal with more detailed speaker information
    showToast(`More information about ${speakerName} coming soon!`, 'info');
}

function showConferenceNotFound() {
    document.body.innerHTML = `
        <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
            <div class="text-center">
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
    const conference = getConferenceById(conference


Id);
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
