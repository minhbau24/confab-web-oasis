
// Admin page JavaScript logic - Tiếng Việt

document.addEventListener('DOMContentLoaded', function() {
    loadAdminDashboard();
    loadConferencesTable();
});

function loadAdminDashboard() {
    const stats = getAdminStats();
    
    // Update statistics cards
    document.getElementById('total-conferences').textContent = stats.totalConferences;
    document.getElementById('active-conferences').textContent = stats.activeConferences;
    document.getElementById('total-attendees').textContent = stats.totalAttendees.toLocaleString('vi-VN');
    document.getElementById('total-revenue').textContent = 
        stats.totalRevenue.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
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
        showToast(`Chức năng chỉnh sửa cho "${conference.title}" sẽ được triển khai tại đây!`, 'info');
        // In a real application, this would open a modal or navigate to an edit page
    }
}

function deleteConference(conferenceId) {
    const conference = getConferenceById(conferenceId);
    if (conference) {
        if (confirm(`Bạn có chắc chắn muốn xóa "${conference.title}"?`)) {
            // In a real application, this would make an API call to delete the conference
            showToast(`"${conference.title}" đã được xóa!`, 'success');
            
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
        showToast('Vui lòng điền đầy đủ thông tin!', 'danger');
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
            name: 'Quản trị viên Trung tâm Hội nghị',
            email: 'admin@trungtamhoinghi.vn',
            phone: '(+84) 123-456-789'
        },
        speakers: [
            {
                name: 'Diễn giả (chưa xác định)',
                title: 'Chuyên gia trong ngành',
                bio: 'Thông tin diễn giả sẽ được cập nhật sau'
            }
        ],        schedule: [
            {
                time: '09:00',
                title: 'Đăng ký & Chào mừng',
                speaker: ''
            },
            {
                time: '10:00',
                title: 'Bài phát biểu khai mạc',
                speaker: 'Diễn giả (chưa xác định)'
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
    
    showToast(`Hội nghị "${title}" đã được thêm thành công!`, 'success');
}

// Helper function to format currency
function formatCurrency(amount) {
    return amount.toLocaleString('vi-VN', {
        style: 'currency',
        currency: 'VND'
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
