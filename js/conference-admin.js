
// Conference Admin page JavaScript logic

let currentConference = null;
let attendeesData = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeConferenceAdmin();
    setupEventListeners();
});

function initializeConferenceAdmin() {
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (!conferenceId) {
        showToast('No conference ID provided. Redirecting to admin dashboard...', 'warning');
        setTimeout(() => {
            window.location.href = 'admin.html';
        }, 2000);
        return;
    }
    
    currentConference = getConferenceById(parseInt(conferenceId));
    
    if (!currentConference) {
        showToast('Conference not found. Redirecting to admin dashboard...', 'danger');
        setTimeout(() => {
            window.location.href = 'admin.html';
        }, 2000);
        return;
    }
    
    // Initialize attendees data (simulate with dummy data)
    initializeAttendeesData();
    
    // Load conference information
    loadConferenceInfo();
    loadSummaryStats();
    loadAttendeesTable();
}

function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('attendee-search');
    if (searchInput) {
        searchInput.addEventListener('input', filterAttendees);
    }
}

function initializeAttendeesData() {
    // Generate dummy attendees for demonstration
    const dummyAttendees = [
        { id: 1, name: 'Alice Johnson', email: 'alice.johnson@email.com', registrationDate: '2024-01-05', status: 'confirmed' },
        { id: 2, name: 'Bob Smith', email: 'bob.smith@email.com', registrationDate: '2024-01-08', status: 'registered' },
        { id: 3, name: 'Carol Davis', email: 'carol.davis@email.com', registrationDate: '2024-01-10', status: 'attended' },
        { id: 4, name: 'David Wilson', email: 'david.wilson@email.com', registrationDate: '2024-01-12', status: 'confirmed' },
        { id: 5, name: 'Eva Brown', email: 'eva.brown@email.com', registrationDate: '2024-01-15', status: 'registered' }
    ];
    
    // Use actual attendee count or dummy data
    const attendeeCount = Math.min(currentConference.attendees, dummyAttendees.length);
    attendeesData = dummyAttendees.slice(0, attendeeCount);
}

function loadConferenceInfo() {
    // Update page title
    document.getElementById('conference-title').textContent = currentConference.title;
    document.title = `ConferenceHub - ${currentConference.title} Admin`;
    
    // Load conference details card
    const detailsCard = document.getElementById('conference-details-card');
    const dateFormatted = new Date(currentConference.date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const endDateFormatted = currentConference.endDate ? 
        new Date(currentConference.endDate).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';
    
    detailsCard.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">Status</h6>
                            <span class="badge bg-${currentConference.status === 'active' ? 'success' : 'secondary'} fs-6">
                                ${currentConference.status.charAt(0).toUpperCase() + currentConference.status.slice(1)}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">Category</h6>
                            <span class="badge bg-primary fs-6">${currentConference.category}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1"><i class="fas fa-calendar me-1"></i>Date</h6>
                            <p class="mb-0">${dateFormatted}${currentConference.endDate ? ` - ${endDateFormatted}` : ''}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i>Location</h6>
                            <p class="mb-0">${currentConference.location}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Description</h6>
                        <p class="mb-0">${currentConference.description}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <img src="${currentConference.image}" class="img-fluid rounded" alt="${currentConference.title}" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>
    `;
}

function loadSummaryStats() {
    const attendeeCount = attendeesData.length;
    const totalRevenue = attendeeCount * currentConference.price;
    const capacityPercentage = Math.round((attendeeCount / currentConference.capacity) * 100);
    
    // Calculate days until event
    const eventDate = new Date(currentConference.date);
    const today = new Date();
    const timeDiff = eventDate.getTime() - today.getTime();
    const daysUntil = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    // Update stats
    document.getElementById('attendee-count').textContent = attendeeCount;
    document.getElementById('total-revenue').textContent = totalRevenue.toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD'
    });
    document.getElementById('capacity-percentage').textContent = `${capacityPercentage}%`;
    document.getElementById('days-until').textContent = daysUntil > 0 ? daysUntil : 'Event Passed';
}

function loadAttendeesTable() {
    const tableBody = document.getElementById('attendees-table');
    const noAttendeesMessage = document.getElementById('no-attendees-message');
    
    if (attendeesData.length === 0) {
        tableBody.innerHTML = '';
        noAttendeesMessage.classList.remove('d-none');
        return;
    }
    
    noAttendeesMessage.classList.add('d-none');
    tableBody.innerHTML = attendeesData
        .map(attendee => renderAttendeeRow(attendee))
        .join('');
}

function renderAttendeeRow(attendee) {
    const statusBadgeClass = {
        'registered': 'bg-warning',
        'confirmed': 'bg-info',
        'attended': 'bg-success'
    }[attendee.status] || 'bg-secondary';
    
    const registrationDate = new Date(attendee.registrationDate).toLocaleDateString();
    
    return `
        <tr>
            <td>${attendee.name}</td>
            <td>${attendee.email}</td>
            <td>${registrationDate}</td>
            <td><span class="badge ${statusBadgeClass}">${attendee.status}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editAttendee(${attendee.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="removeAttendee(${attendee.id})" title="Remove">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
}

function filterAttendees() {
    const searchTerm = document.getElementById('attendee-search').value.toLowerCase();
    const rows = document.querySelectorAll('#attendees-table tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

function toggleConferenceStatus() {
    const newStatus = currentConference.status === 'active' ? 'archived' : 'active';
    const actionText = newStatus === 'archived' ? 'archive' : 'activate';
    
    if (confirm(`Are you sure you want to ${actionText} this conference?`)) {
        currentConference.status = newStatus;
        showToast(`Conference has been ${newStatus}!`, 'success');
        loadConferenceInfo();
    }
}

function saveConferenceChanges() {
    // Get form data
    const title = document.getElementById('editTitle').value;
    const category = document.getElementById('editCategory').value;
    const date = document.getElementById('editDate').value;
    const endDate = document.getElementById('editEndDate').value;
    const location = document.getElementById('editLocation').value;
    const price = parseFloat(document.getElementById('editPrice').value);
    const capacity = parseInt(document.getElementById('editCapacity').value);
    const status = document.getElementById('editStatus').value;
    const description = document.getElementById('editDescription').value;
    
    // Validate form
    if (!title || !category || !date || !location || !price || !capacity || !description) {
        showToast('Please fill in all required fields!', 'danger');
        return;
    }
    
    // Update conference object
    currentConference.title = title;
    currentConference.category = category;
    currentConference.date = date;
    currentConference.endDate = endDate || date;
    currentConference.location = location;
    currentConference.price = price;
    currentConference.capacity = capacity;
    currentConference.status = status;
    currentConference.description = description;
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('editConferenceModal'));
    modal.hide();
    
    // Refresh display
    loadConferenceInfo();
    loadSummaryStats();
    
    showToast('Conference information updated successfully!', 'success');
}

function addAttendee() {
    const name = document.getElementById('attendeeName').value;
    const email = document.getElementById('attendeeEmail').value;
    const status = document.getElementById('attendeeStatus').value;
    
    if (!name || !email || !status) {
        showToast('Please fill in all fields!', 'danger');
        return;
    }
    
    // Check for duplicate email
    if (attendeesData.some(attendee => attendee.email === email)) {
        showToast('An attendee with this email already exists!', 'warning');
        return;
    }
    
    // Create new attendee
    const newAttendee = {
        id: Math.max(...attendeesData.map(a => a.id), 0) + 1,
        name: name,
        email: email,
        registrationDate: new Date().toISOString().split('T')[0],
        status: status
    };
    
    attendeesData.push(newAttendee);
    
    // Update conference attendee count
    currentConference.attendees = attendeesData.length;
    
    // Reset form
    document.getElementById('addAttendeeForm').reset();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addAttendeeModal'));
    modal.hide();
    
    // Refresh displays
    loadAttendeesTable();
    loadSummaryStats();
    
    showToast(`${name} has been added as an attendee!`, 'success');
}

function editAttendee(attendeeId) {
    const attendee = attendeesData.find(a => a.id === attendeeId);
    if (attendee) {
        // For now, just show a toast - in a real app, this would open an edit modal
        showToast(`Edit functionality for ${attendee.name} would be implemented here!`, 'info');
    }
}

function removeAttendee(attendeeId) {
    const attendee = attendeesData.find(a => a.id === attendeeId);
    if (attendee) {
        if (confirm(`Are you sure you want to remove ${attendee.name} from this conference?`)) {
            attendeesData = attendeesData.filter(a => a.id !== attendeeId);
            
            // Update conference attendee count
            currentConference.attendees = attendeesData.length;
            
            // Refresh displays
            loadAttendeesTable();
            loadSummaryStats();
            
            showToast(`${attendee.name} has been removed from the conference.`, 'success');
        }
    }
}

// Pre-populate edit modal when it opens
document.getElementById('editConferenceModal').addEventListener('show.bs.modal', function() {
    if (currentConference) {
        document.getElementById('editTitle').value = currentConference.title;
        document.getElementById('editCategory').value = currentConference.category;
        document.getElementById('editDate').value = currentConference.date;
        document.getElementById('editEndDate').value = currentConference.endDate || '';
        document.getElementById('editLocation').value = currentConference.location;
        document.getElementById('editPrice').value = currentConference.price;
        document.getElementById('editCapacity').value = currentConference.capacity;
        document.getElementById('editStatus').value = currentConference.status;
        document.getElementById('editDescription').value = currentConference.description;
    }
});
