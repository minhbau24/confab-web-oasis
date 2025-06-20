
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
    const dateFormatted = new Date(conference.date).toLocaleDateString('vi-VN', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const endDateFormatted = conference.endDate ? 
        ' - ' + new Date(conference.endDate).toLocaleDateString('vi-VN', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';

    const priceFormatted = conference.price.toLocaleString('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0
    });

    // Update hero section content    document.getElementById('conference-category').textContent = conference.category;
    document.getElementById('conference-title').textContent = conference.title;
    document.getElementById('conference-description').textContent = conference.description;
    document.getElementById('conference-date').textContent = dateFormatted + endDateFormatted;
    document.getElementById('conference-location').textContent = conference.location;
    document.getElementById('conference-attendees').textContent = `${conference.attendees}/${conference.capacity} người tham dự`;
    document.getElementById('conference-price').textContent = priceFormatted;
    document.getElementById('spots-remaining').textContent = `Còn ${conference.capacity - conference.attendees} chỗ trống`;
    
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
                        Xem thêm
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    speakersContainer.innerHTML = speakersHtml;
}

function loadAgenda(conference) {
    const agendaContainer = document.getElementById('agendaAccordion');
    
    // Chuẩn bị dữ liệu cho lịch trình theo ngày
    let agendaByDay = {};
    
    // Tính toán số ngày diễn ra hội nghị
    const startDate = new Date(conference.date);
    const endDate = conference.endDate ? new Date(conference.endDate) : startDate;
    const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
    
    // Tạo cấu trúc lịch trình cho mỗi ngày
    for (let i = 0; i < days; i++) {
        const currentDate = new Date(startDate);
        currentDate.setDate(startDate.getDate() + i);
        const dateKey = currentDate.toISOString().split('T')[0]; // Format: YYYY-MM-DD
        
        // Format ngày tháng hiển thị
        const dateFormatted = currentDate.toLocaleDateString('vi-VN', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        agendaByDay[dateKey] = {
            date: dateFormatted,
            items: []
        };
    }
    
    // Phân chia lịch trình vào các ngày
    // Nếu không có thông tin ngày cụ thể trong mỗi mục lịch trình, 
    // giả định tất cả thuộc ngày đầu tiên
    if (conference.schedule && conference.schedule.length > 0) {
        conference.schedule.forEach(item => {
            // Nếu item có trường eventDate, sử dụng nó
            // Nếu không, sử dụng ngày đầu tiên của hội nghị
            const itemDate = item.eventDate ? item.eventDate : conference.date;
            const dateKey = new Date(itemDate).toISOString().split('T')[0];
            
            if (agendaByDay[dateKey]) {
                agendaByDay[dateKey].items.push(item);
            } else {
                // Nếu không tìm thấy ngày, đặt vào ngày đầu tiên
                const firstDayKey = Object.keys(agendaByDay)[0];
                agendaByDay[firstDayKey].items.push(item);
            }
        });
    }
    
    // Tạo HTML cho accordion
    let agendaHtml = '';
    let firstDay = true;
    
    Object.keys(agendaByDay).forEach((dateKey, index) => {
        const dayData = agendaByDay[dateKey];
        const dayId = `day${index + 1}`;
        
        // Tạo accordion item cho mỗi ngày
        agendaHtml += `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button ${!firstDay ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse"
                        data-bs-target="#${dayId}">
                        <strong>Ngày ${index + 1} - ${dayData.date}</strong>
                    </button>
                </h2>
                <div id="${dayId}" class="accordion-collapse collapse ${firstDay ? 'show' : ''}" data-bs-parent="#agendaAccordion">
                    <div class="accordion-body">
        `;
        
        // Thêm các mục lịch trình cho ngày này
        if (dayData.items && dayData.items.length > 0) {
            dayData.items.forEach(item => {
                agendaHtml += `
                    <div class="timeline-item">
                        <h6>${item.time}</h6>
                        <h5>${item.title}</h5>
                        ${item.speaker ? `<p class="text-muted">Diễn giả: ${item.speaker}</p>` : ''}
                        ${item.description ? `<p>${item.description}</p>` : ''}
                    </div>
                `;
            });
        } else {
            // Nếu không có mục lịch trình nào
            agendaHtml += `<p class="text-muted">Chưa có thông tin lịch trình cho ngày này.</p>`;
        }
        
        agendaHtml += `
                    </div>
                </div>
            </div>
        `;
        
        firstDay = false;
    });
    
    // Cập nhật nội dung
    agendaContainer.innerHTML = agendaHtml;
}

function showSpeakerModal(speakerName) {
    // This would show a modal with more detailed speaker information
    showToast(`Thông tin chi tiết về ${speakerName} sẽ có sớm!`, 'info');
}

function showConferenceNotFound() {
    document.body.innerHTML = `
        <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h2>Không tìm thấy Hội nghị</h2>
                <p class="text-muted mb-4">Hội nghị bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
                <a href="conferences.html" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại Danh sách Hội nghị
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
            showToast(`Đăng ký thành công cho ${conference.title}!`);
            
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
            showToast('Rất tiếc, hội nghị này đã đủ người!', 'warning');
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
        } else {            // Fallback: copy to clipboard
            navigator.clipboard.writeText(url).then(() => {
                showToast('Đã sao chép liên kết hội nghị vào clipboard!');
            }).catch(() => {
                // Manual fallback
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('Đã sao chép liên kết hội nghị vào clipboard!');
            });
        }
    }
}
