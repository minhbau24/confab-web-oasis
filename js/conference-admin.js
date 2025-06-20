// Conference Admin page JavaScript logic

let currentConference = null;
let attendeesData = [];

// Helper function for toasts
function showToast(message, type = 'info') {
    // Simple toast implementation if not provided elsewhere
    alert(message);
}

document.addEventListener('DOMContentLoaded', function() {
    initializeConferenceAdmin();
    setupEventListeners();
});

function initializeConferenceAdmin() {
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
      if (!conferenceId) {
        showToast('Không tìm thấy ID hội nghị. Chuyển hướng về trang quản trị...', 'warning');
        setTimeout(() => {
            window.location.href = 'admin.html';
        }, 2000);
        return;
    }
    
    currentConference = getConferenceById(parseInt(conferenceId));
    
    if (!currentConference) {
        showToast('Không tìm thấy hội nghị. Chuyển hướng về trang quản trị...', 'danger');
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
                            <h6 class="text-muted mb-1">Status</h6>                            <span class="badge bg-${currentConference.status === 'active' ? 'success' : 'secondary'} fs-6">
                                ${currentConference.status === 'active' ? 'Đang hoạt động' : 'Đã lưu trữ'}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">Danh mục</h6>
                            <span class="badge bg-primary fs-6">${translateCategory(currentConference.category)}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1"><i class="fas fa-calendar me-1"></i>Ngày</h6>
                            <p class="mb-0">${dateFormatted}${currentConference.endDate ? ` - ${endDateFormatted}` : ''}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i>Địa điểm</h6>
                            <p class="mb-0">${currentConference.location}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Mô tả</h6>
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
    document.getElementById('attendee-count').textContent = attendeeCount.toLocaleString('vi-VN');
    document.getElementById('total-revenue').textContent = totalRevenue.toLocaleString('vi-VN', {
        style: 'currency',
        currency: 'VND'
    });
    document.getElementById('capacity-percentage').textContent = `${capacityPercentage}%`;
    document.getElementById('days-until').textContent = daysUntil > 0 ? daysUntil : 'Sự kiện đã qua';
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
    
    const statusTranslation = {
        'registered': 'Đã đăng ký',
        'confirmed': 'Đã xác nhận',
        'attended': 'Đã tham dự'
    }[attendee.status] || attendee.status;
    
    const registrationDate = new Date(attendee.registrationDate).toLocaleDateString('vi-VN');
    
    return `
        <tr>
            <td>${attendee.name}</td>
            <td>${attendee.email}</td>
            <td>${registrationDate}</td>
            <td><span class="badge ${statusBadgeClass}">${statusTranslation}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editAttendee(${attendee.id})" title="Sửa">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="removeAttendee(${attendee.id})" title="Xóa">
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
    const actionText = newStatus === 'archived' ? 'lưu trữ' : 'kích hoạt';
    
    if (confirm(`Bạn có chắc chắn muốn ${actionText} hội nghị này không?`)) {
        currentConference.status = newStatus;
        const statusMessage = newStatus === 'archived' ? 'đã được lưu trữ' : 'đã được kích hoạt';
        showToast(`Hội nghị ${statusMessage}!`, 'success');
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
        showToast('Vui lòng điền đầy đủ thông tin bắt buộc!', 'danger');
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
    
    showToast('Thông tin hội nghị đã được cập nhật thành công!', 'success');
}

function addAttendee() {
    const name = document.getElementById('attendeeName').value;
    const email = document.getElementById('attendeeEmail').value;
    const status = document.getElementById('attendeeStatus').value;
    
    if (!name || !email || !status) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'danger');
        return;
    }
    
    // Check for duplicate email
    if (attendeesData.some(attendee => attendee.email === email)) {
        showToast('Email này đã được sử dụng bởi người tham dự khác!', 'warning');
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
    
    showToast(`${name} đã được thêm vào danh sách người tham dự!`, 'success');
}

function editAttendee(attendeeId) {
    const attendee = attendeesData.find(a => a.id === attendeeId);
    if (attendee) {
        // For now, just show a toast - in a real app, this would open an edit modal
        showToast(`Chức năng chỉnh sửa cho ${attendee.name} sẽ được triển khai tại đây!`, 'info');
    }
}

function removeAttendee(attendeeId) {
    const attendee = attendeesData.find(a => a.id === attendeeId);
    if (attendee) {
        if (confirm(`Bạn có chắc chắn muốn xóa ${attendee.name} khỏi hội nghị này?`)) {
            attendeesData = attendeesData.filter(a => a.id !== attendeeId);
            
            // Update conference attendee count
            currentConference.attendees = attendeesData.length;
            
            // Refresh displays
            loadAttendeesTable();
            loadSummaryStats();
            
            showToast(`${attendee.name} đã được xóa khỏi hội nghị.`, 'success');
        }
    }
}

// Helper function to translate category names
function translateCategory(category) {
    const translations = {
        'Technology': 'Công nghệ',
        'Business': 'Kinh doanh',
        'Marketing': 'Marketing',
        'Design': 'Thiết kế'
    };
    return translations[category] || category;
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
