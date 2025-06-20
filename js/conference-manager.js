// Conference manager page JavaScript logic

document.addEventListener('DOMContentLoaded', function() {
    loadManagerDashboard();
    populateConferenceList();
    populateConferenceSelect();
});

function loadManagerDashboard() {
    // In a real application, these would be fetched from an API
    const stats = {
        activeConferences: 3,
        totalAttendees: 542,
        totalRevenue: 75300,
        growthRate: 28
    };
    
    // Update the dashboard stats
    document.getElementById('activeConferences').textContent = stats.activeConferences;
    document.getElementById('totalAttendees').textContent = stats.totalAttendees;
    document.getElementById('totalRevenue').textContent = formatCurrency(stats.totalRevenue);
    document.getElementById('growthRate').textContent = `${stats.growthRate}%`;
}

function populateConferenceList() {
    // Get conferences managed by the current user
    const myConferences = getConferences().filter(conf => conf.isManaged);
    
    // Get the container element
    const conferencesList = document.getElementById('conferencesList');
    
    // Clear loading indicator
    conferencesList.innerHTML = '';
    
    if (myConferences.length === 0) {
        conferencesList.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You don't have any conferences yet. Create one by clicking the "Create New Conference" button above.
                </div>
            </div>
        `;
        return;
    }
    
    // Create a card for each conference
    myConferences.forEach(conference => {
        // Calculate status based on dates
        const today = new Date();
        const startDate = new Date(conference.date);
        const endDate = conference.endDate ? new Date(conference.endDate) : startDate;
        
        let statusClass = 'bg-secondary';
        let statusText = 'Draft';
        
        if (startDate <= today && endDate >= today) {
            statusClass = 'bg-success';
            statusText = 'Active';
        } else if (startDate > today) {
            statusClass = 'bg-primary';
            statusText = 'Upcoming';
        } else if (endDate < today) {
            statusClass = 'bg-secondary';
            statusText = 'Past';
        }
        
        const card = document.createElement('div');
        card.className = 'col-md-6 col-lg-4 mb-4';
        card.innerHTML = `
            <div class="manager-card card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge ${statusClass} status-badge">${statusText}</span>
                    <div class="action-buttons">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewConference(${conference.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="editConference(${conference.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="showDeleteConfirmation(${conference.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-3">${conference.title}</h5>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-2"></i>${formatDate(conference.date)}
                            ${conference.endDate ? ' - ' + formatDate(conference.endDate) : ''}
                        </small><br>
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt me-2"></i>${conference.location}
                        </small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${conference.attendees || 0}</strong>/<span class="text-muted">${conference.capacity}</span> 
                            <small class="text-muted">attendees</small>
                        </div>
                        <div>
                            <span class="badge bg-info">${formatCurrency(conference.price)}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                            style="width: ${Math.round((conference.attendees || 0) / conference.capacity * 100)}%"
                            aria-valuenow="${Math.round((conference.attendees || 0) / conference.capacity * 100)}" 
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">${Math.round((conference.attendees || 0) / conference.capacity * 100)}% filled</small>
                        <a href="conference-detail.html?id=${conference.id}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        `;
        
        conferencesList.appendChild(card);
    });
}

function populateConferenceSelect() {
    const myConferences = getConferences().filter(conf => conf.isManaged);
    const conferenceSelect = document.getElementById('conferenceSelect');
    
    // Clear existing options except the first one
    conferenceSelect.innerHTML = '<option selected>Select a conference...</option>';
    
    // Add options for each conference
    myConferences.forEach(conference => {
        const option = document.createElement('option');
        option.value = conference.id;
        option.textContent = conference.title;
        conferenceSelect.appendChild(option);
    });
    
    // Add event listener to load attendees when a conference is selected
    conferenceSelect.addEventListener('change', function() {
        if (this.value !== 'Select a conference...') {
            loadAttendees(parseInt(this.value));
        }
    });
}

function loadAttendees(conferenceId) {
    // In a real application, these would be fetched from an API
    const mockAttendees = [
        { id: 1, name: "Alice Johnson", email: "alice@example.com", registrationDate: "2023-05-15", ticketType: "VIP", checkedIn: true },
        { id: 2, name: "Bob Smith", email: "bob@example.com", registrationDate: "2023-05-18", ticketType: "Standard", checkedIn: true },
        { id: 3, name: "Charlie Davis", email: "charlie@example.com", registrationDate: "2023-05-20", ticketType: "Standard", checkedIn: false },
        { id: 4, name: "Dana Garcia", email: "dana@example.com", registrationDate: "2023-05-25", ticketType: "Early Bird", checkedIn: false },
        { id: 5, name: "Evan Lee", email: "evan@example.com", registrationDate: "2023-06-01", ticketType: "VIP", checkedIn: false }
    ];
    
    const attendeesList = document.getElementById('attendeesList');
    attendeesList.innerHTML = '';
    
    mockAttendees.forEach(attendee => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${attendee.name}</td>
            <td>${attendee.email}</td>
            <td>${formatDate(attendee.registrationDate)}</td>
            <td><span class="badge bg-${attendee.ticketType === 'VIP' ? 'info' : 'secondary'}">${attendee.ticketType}</span></td>
            <td>
                <span class="badge ${attendee.checkedIn ? 'bg-success' : 'bg-warning'}">
                    ${attendee.checkedIn ? 'Checked In' : 'Not Checked In'}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="toggleCheckIn(${attendee.id})">
                        <i class="fas fa-${attendee.checkedIn ? 'times' : 'check'}"></i>
                        ${attendee.checkedIn ? 'Undo Check-in' : 'Check In'}
                    </button>
                    <button class="btn btn-outline-secondary" onclick="emailAttendee(${attendee.id})">
                        <i class="fas fa-envelope"></i>
                    </button>
                </div>
            </td>
        `;
        attendeesList.appendChild(row);
    });
}

function viewConference(conferenceId) {
    window.location.href = `conference-detail.html?id=${conferenceId}`;
}

function editConference(conferenceId) {
    // In a real application, you would fetch the conference details and populate the form
    // For now, we'll just show an alert
    alert(`Editing conference with ID ${conferenceId}`);
}

function showDeleteConfirmation(conferenceId) {
    if (confirm('Are you sure you want to delete this conference?')) {
        deleteConference(conferenceId);
    }
}

function deleteConference(conferenceId) {
    // In a real application, this would send a request to delete the conference
    // For now, we'll just show an alert and refresh the list
    alert(`Conference ${conferenceId} has been deleted`);
    populateConferenceList();
}

function createConference() {
    // Get form values
    const title = document.getElementById('conferenceTitle').value.trim();
    const category = document.getElementById('conferenceCategory').value;
    const startDate = document.getElementById('conferenceStartDate').value;
    const endDate = document.getElementById('conferenceEndDate').value;
    const location = document.getElementById('conferenceLocation').value.trim();
    const price = parseFloat(document.getElementById('conferencePrice').value);
    const capacity = parseInt(document.getElementById('conferenceCapacity').value);
    const description = document.getElementById('conferenceDescription').value.trim();
    
    // Form validation
    if (!title || !category || !startDate || !endDate || !location || isNaN(price) || isNaN(capacity) || !description) {
        alert('Please fill out all required fields');
        return;
    }
    
    // In a real application, this would send a request to create the conference
    // For now, we'll just show an alert and refresh the list
    alert(`Conference "${title}" has been created!`);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addConferenceModal'));
    modal.hide();
    
    // Reset form
    document.getElementById('addConferenceForm').reset();
    
    // Refresh conference list
    populateConferenceList();
    populateConferenceSelect();
}

function updateConference() {
    // Similar to createConference, but for updates
    alert('Conference updated successfully!');
}

function toggleCheckIn(attendeeId) {
    // In a real application, this would send a request to toggle the check-in status
    alert(`Toggled check-in status for attendee ${attendeeId}`);
    
    // Reload attendees to reflect the change
    const conferenceId = document.getElementById('conferenceSelect').value;
    loadAttendees(parseInt(conferenceId));
}

function emailAttendee(attendeeId) {
    // In a real application, this might open an email composition form
    alert(`Opening email form for attendee ${attendeeId}`);
}

function filterConferences(filter) {
    alert(`Filtering conferences by: ${filter}`);
    // In a real application, this would filter the conferences list
}

function searchConferences() {
    const query = document.getElementById('searchConference').value.trim();
    if (query) {
        alert(`Searching for conferences matching: "${query}"`);
        // In a real application, this would search the conferences list
    }
}

// Helper function to format dates
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Helper function to format currency
function formatCurrency(amount) {
    return '$' + amount.toLocaleString('en-US');
}
