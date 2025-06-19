
// Conferences page JavaScript logic

let allConferences = [];
let filteredConferences = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeConferencesPage();
    setupEventListeners();
});

function initializeConferencesPage() {
    allConferences = getConferences();
    filteredConferences = [...allConferences];
    renderConferences();
}

function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const locationFilter = document.getElementById('locationFilter');

    // Search functionality
    searchInput.addEventListener('input', filterConferences);
    categoryFilter.addEventListener('change', filterConferences);
    locationFilter.addEventListener('change', filterConferences);
}

function filterConferences() {
    const searchQuery = document.getElementById('searchInput').value;
    const selectedCategory = document.getElementById('categoryFilter').value;
    const selectedLocation = document.getElementById('locationFilter').value;

    filteredConferences = searchConferences(searchQuery, selectedCategory, selectedLocation);
    renderConferences();
}

function renderConferences() {
    const container = document.getElementById('conferences-grid');
    const noResults = document.getElementById('no-results');

    if (filteredConferences.length > 0) {
        container.innerHTML = filteredConferences
            .map(conference => renderConferenceCard(conference))
            .join('');
        
        container.style.display = 'flex';
        noResults.style.display = 'none';
    } else {
        container.style.display = 'none';
        noResults.style.display = 'block';
    }
}

// Add global functions if they don't exist in other files
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
                
                // Re-render to update attendee count
                renderConferences();
            } else {
                showToast('Sorry, this conference is full!', 'warning');
            }
        }
    }
}
