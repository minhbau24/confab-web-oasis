
// Admin page JavaScript logic

document.addEventListener('DOMContentLoaded', function() {
    loadAdminDashboard();
    loadConferencesTable();
});

function loadAdminDashboard() {
    const stats = getAdminStats();
    
    // Update statistics cards
    document.getElementById('total-conferences').textContent = stats.totalConferences;
    document.getElementById('active-conferences').textContent = stats.activeConferences;
    document.getElementById('total-attendees').textContent = stats.totalAttendees.toLocaleString();
    document.getElementById('total-revenue').textContent = 
        stats.totalRevenue.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
}

function loadConferencesTable() {
    const conferences = getConferences();
    const tableBody = document.getElementById('conferences-table');
    
    tableBody.innerHTML = conferences
        .map(conference => renderAdminConferenceRow(conference))
        .join('');
}

function editConference(conferenceId) {
    const conference = getConferenceById(conferenceId);
    if (conference) {
        showToast(`Edit functionality for "${conference.title}" would be implemented here!`, 'info');
        // In a real application, this would open a modal or navigate to an edit page
    }
}

function deleteConference(conferenceId) {
    const conference = getConferenceById(conferenceId);
    if (conference) {
        if (confirm(`Are you sure you want to delete "${conference.title}"?`)) {
            // In a real application, this would make an API call to delete the conference
            showToast(`"${conference.title}" has been deleted!`, 'success');
            
            // Remove from the conferences array (simulation)
            const index = conferences.findIndex(c => c.id === conferenceId);
            if (index > -1) {
                conferences.splice(index, 1);
                loadAdminDashboard();
                loadConferencesTable();
            }
        }
    }
}

function addConference() {
    // Get form data
    const title = document.getElementById('conferenceTitle').value;
    const category = document.getElementById('conferenceCategory').value;
    const date = document.getElementById('conferenceDate').value;
    const location = document.getElementById('conferenceLocation').value;
    const description = document.getElementById('conferenceDescription').value;
    const price = parseFloat(document.getElementById('conferencePrice').value);
    const capacity = parseInt(document.getElementById('conferenceCapacity').value);
    
    // Validate form
    if (!title || !category || !date || !location || !description || !price || !capacity) {
        showToast('Please fill in all fields!', 'danger');
        return;
    }
    
    // Create new conference object
    const newConference = {
        id: Math.max(...conferences.map(c => c.id)) + 1,
        title: title,
        description: description,
        date: date,
        endDate: date, // For simplicity, using same date
        location: location,
        category: category,
        price: price,
        capacity: capacity,
        attendees: 0,
        status: 'active',
        image: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop',
        organizer: {
            name: 'ConferenceHub Admin',
            email: 'admin@conferencehub.com',
            phone: '+1 (555) 000-0000'
        },
        speakers: [
            {
                name: 'TBD Speaker',
                title: 'Industry Expert',
                bio: 'Speaker details to be announced'
            }
        ],
        schedule: [
            {
                time: '09:00',
                title: 'Registration & Welcome',
                speaker: ''
            },
            {
                time: '10:00',
                title: 'Opening Keynote',
                speaker: 'TBD Speaker'
            }
        ]
    };
    
    // Add to conferences array
    conferences.push(newConference);
    
    // Reset form
    document.getElementById('addConferenceForm').reset();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addConferenceModal'));
    modal.hide();
    
    // Refresh dashboard and table
    loadAdminDashboard();
    loadConferencesTable();
    
    showToast(`Conference "${title}" has been added successfully!`, 'success');
}

// Helper function to format currency
function formatCurrency(amount) {
    return amount.toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD'
    });
}

// Search functionality for admin table (bonus feature)
function searchConferencesTable() {
    const searchTerm = document.getElementById('adminSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#conferences-table tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}
