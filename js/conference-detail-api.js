// Conference detail page JavaScript logic - API version

document.addEventListener('DOMContentLoaded', function() {
    loadConferenceDetail();
});

async function loadConferenceDetail() {
    // Get conference ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (conferenceId) {
        try {            // Fetch conference data from API
            // Sử dụng phương thức no-cache để tránh cache
            const response = await fetch(`api/conferences.php?id=${conferenceId}`, {
                cache: 'no-cache',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            // Check if the response is OK before trying to parse JSON
            if (!response.ok) {
                throw new Error(`API responded with status: ${response.status}`);
            }
            
            // Try to capture the raw response for debugging
            const rawResponse = await response.text();
            console.log("Raw API response:", rawResponse);
            
            // Check content type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`API did not return JSON. ContentType: ${contentType || 'undefined'}`);
            }
            
            // Parse the raw response
            let result;
            try {
                result = JSON.parse(rawResponse);
            } catch (jsonError) {
                throw new Error(`Failed to parse JSON: ${jsonError.message}, Response: ${rawResponse.substring(0, 100)}...`);
            }
            
            if (result.status && result.data) {
                const conference = result.data;
                
                // Update page title
                document.title = `ConferenceHub - ${conference.title}`;
                
                // Update hero section
                updateHeroSection(conference);
                
                // Render conference details
                updateConferenceDetails(conference);
                
                // Fetch and load speakers
                await loadSpeakers(conferenceId);
                
                // Fetch and load agenda (schedule)
                await loadAgenda(conferenceId);
            } else {
                // Conference not found
                showConferenceNotFound();
            }
        } catch (error) {
            console.error("Error loading conference details:", error);
            // Show a more specific error message to help with debugging
            showErrorMessage("Không thể tải thông tin hội nghị. Lỗi: " + error.message);
        }
    } else {
        // No ID provided
        showConferenceNotFound();
    }
}

function updateHeroSection(conference) {
    const startDate = new Date(conference.date);
    const dateFormatted = startDate.toLocaleDateString('vi-VN', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    let dateDisplay = dateFormatted;
    
    // If conference has an end date, format it as a range
    if (conference.end_date) {
        const endDate = new Date(conference.end_date);
        const endDateFormatted = endDate.toLocaleDateString('vi-VN', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        dateDisplay = `${dateFormatted} - ${endDateFormatted}`;
    }

    const priceFormatted = parseInt(conference.price).toLocaleString('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0
    });

    // Update hero section content
    document.getElementById('conference-category').textContent = conference.category;
    document.getElementById('conference-title').textContent = conference.title;
    document.getElementById('conference-description').textContent = conference.description;
    document.getElementById('conference-date').textContent = dateDisplay;
    document.getElementById('conference-location').textContent = conference.location;
    
    // Handle attendees data if available
    if (conference.attendees !== undefined && conference.capacity !== undefined) {
        document.getElementById('conference-attendees').textContent = `${conference.attendees}/${conference.capacity} người tham dự`;
        document.getElementById('spots-remaining').textContent = `Còn ${conference.capacity - conference.attendees} chỗ trống`;
    }
    
    document.getElementById('conference-price').textContent = priceFormatted;
    
    // Update hero background if image available
    const heroSection = document.getElementById('hero-section');
    if (conference.image) {
        heroSection.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('${conference.image}')`;
    }
}

function updateConferenceDetails(conference) {
    // Update detailed description
    document.getElementById('detailed-description').textContent = conference.description;
    
    // You could add more detailed information here if available from the API
}

async function loadSpeakers(conferenceId) {
    const speakersContainer = document.getElementById('speakers-section');
    if (!speakersContainer) return;
    
    try {        // Fetch speakers data from API
        const response = await fetch(`api/conference_speakers.php?conference_id=${conferenceId}`, {
            cache: 'no-cache',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        // Check if the response is OK
        if (!response.ok) {
            throw new Error(`API responded with status: ${response.status}`);
        }
        
        // Try to parse JSON response
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Speakers API did not return JSON');
        }
        
        const result = await response.json();
        
        if (result.status && result.data && result.data.length > 0) {
            const speakers = result.data;
            
            const speakersHtml = speakers.map(speaker => `
                <div class="col-md-6 mb-4">
                    <div class="speaker-card card h-100">
                        <div class="card-body text-center">
                            <img src="${speaker.image || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=120&h=120&fit=crop&crop=face'}" 
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
        } else {
            // Sử dụng dữ liệu mẫu nếu API không trả về dữ liệu
            useFallbackSpeakerData(speakersContainer);
        }
    } catch (error) {
        console.error("Error loading speakers:", error);
        // Sử dụng dữ liệu mẫu trong trường hợp lỗi
        useFallbackSpeakerData(speakersContainer);
    }
}

// Hàm sử dụng dữ liệu cứng để hiển thị danh sách diễn giả trong trường hợp API thất bại
function useFallbackSpeakerData(speakersContainer) {
    // Dữ liệu mẫu cho diễn giả
    const sampleSpeakers = [
        { name: "Nguyễn Thị Minh", title: "CEO, InnovateTech Vietnam", bio: "Chuyên gia hàng đầu về AI và học máy" },
        { name: "Trần Đức Khải", title: "CTO, VietStartup", bio: "Tiên phong trong công nghệ blockchain" },
        { name: "Phạm Thị Hương", title: "Founder, GreenTech Solutions", bio: "Chuyên gia về phát triển bền vững và năng lượng sạch" },
        { name: "Lê Văn Bách", title: "AI Research Director, FPT Software", bio: "Tiến sĩ AI với hơn 15 năm kinh nghiệm nghiên cứu và ứng dụng thực tế" },
    ];
    
    const speakersHtml = `
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i> Hiển thị thông tin diễn giả mẫu do không thể kết nối với API.
        </div>
        <div class="row">
            ${sampleSpeakers.map(speaker => `
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
            `).join('')}
        </div>
    `;
    
    speakersContainer.innerHTML = speakersHtml;
}

async function loadAgenda(conferenceId) {
    const agendaSection = document.getElementById('agenda-section');
    const agendaContainer = document.getElementById('agendaAccordion');
    if (!agendaSection || !agendaContainer) return;
      try {        
        console.log(`Loading schedule for conference ID: ${conferenceId}`);
        // Fetch schedule data from API
        const response = await fetch(`api/conference_schedule.php?conference_id=${conferenceId}`, {
            cache: 'no-cache',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        // Check if the response is OK
        if (!response.ok) {
            throw new Error(`API responded with status: ${response.status}`);
        }
          // Try to parse JSON response
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Schedule API did not return JSON');
        }
        
        const result = await response.json();
        
        console.log("Schedule API response:", result);
        
        if (result.status && result.data && result.data.length > 0) {
            const schedule = result.data;
            
            // Group schedule by day
            const scheduleByDay = {};
            schedule.forEach(item => {
                if (!scheduleByDay[item.eventDate]) {
                    scheduleByDay[item.eventDate] = [];
                }
                scheduleByDay[item.eventDate].push(item);
            });
            
            // Create tabs for days
            let daysHtml = '';
            let tabPanesHtml = '';
            let isFirst = true;
            
            Object.keys(scheduleByDay).forEach(day => {
                const date = new Date(day);
                const formattedDate = date.toLocaleDateString('vi-VN', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'numeric'
                });
                
                const tabId = `tab-${day.replace(/\-/g, '')}`;
                const activeClass = isFirst ? 'active' : '';
                
                daysHtml += `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${activeClass}" id="${tabId}-tab" data-bs-toggle="tab" 
                                data-bs-target="#${tabId}" type="button" role="tab" 
                                aria-controls="${tabId}" aria-selected="${isFirst ? 'true' : 'false'}">
                            ${formattedDate}
                        </button>
                    </li>
                `;
                
                let dayScheduleHtml = '';
                scheduleByDay[day].forEach(item => {
                    dayScheduleHtml += `
                        <div class="timeline-item">
                            <span class="badge bg-primary mb-2">${item.startTime} - ${item.endTime}</span>
                            <h5 class="fw-bold mb-1">${item.title}</h5>
                            ${item.speaker ? `<p class="text-primary">Người trình bày: ${item.speaker}</p>` : ''}
                            <p>${item.description}</p>
                        </div>
                    `;
                });
                
                tabPanesHtml += `
                    <div class="tab-pane fade show ${activeClass}" id="${tabId}" role="tabpanel" aria-labelledby="${tabId}-tab">
                        <div class="timeline">
                            ${dayScheduleHtml}
                        </div>
                    </div>
                `;
                
                isFirst = false;
            });
            
            // Create full HTML
            const agendaHtml = `
                <ul class="nav nav-tabs mb-4" id="dayTabs" role="tablist">
                    ${daysHtml}
                </ul>
                <div class="tab-content" id="dayTabsContent">
                    ${tabPanesHtml}
                </div>
            `;
            
            agendaContainer.innerHTML = agendaHtml;
            console.log("Schedule loaded successfully from API");
            console.log(schedule);
        } else {
            // Sử dụng lịch trình mẫu từ dữ liệu cứng nếu API không trả về dữ liệu
            useFallbackScheduleData(conferenceId, agendaContainer);
        }
    } catch (error) {
        console.error("Error loading agenda:", error);
        // Sử dụng lịch trình mẫu từ dữ liệu cứng trong trường hợp lỗi
        useFallbackScheduleData(conferenceId, agendaContainer);
    }
}

// Hàm sử dụng dữ liệu cứng để hiển thị lịch trình trong trường hợp API thất bại
function useFallbackScheduleData(conferenceId, agendaContainer) {
    console.log("Using fallback schedule data");
    // Dữ liệu mẫu cho lịch trình
    const sampleSchedule = [
        { 
            eventDate: "2025-05-15", 
            startTime: "08:30", 
            endTime: "09:30", 
            title: "Đăng ký & Kết nối", 
            speaker: "",
            description: "Đón tiếp và cung cấp thẻ hội nghị, tài liệu, thời gian kết nối với các đồng nghiệp"
        },        { 
            eventDate: "2025-05-15", 
            startTime: "09:30", 
            endTime: "10:00", 
            title: "Khai mạc Hội nghị", 
            speaker: "Ban Tổ chức",
            description: "Phát biểu khai mạc, giới thiệu chương trình và các diễn giả chính"
        },
        { 
            eventDate: "2025-05-15", 
            startTime: "10:00", 
            endTime: "11:30", 
            title: "Keynote: Công nghệ AI", 
            speaker: "Nguyễn Thị Minh",
            description: "Phân tích xu hướng AI toàn cầu và cơ hội áp dụng tại Việt Nam"
        },
        { 
            eventDate: "2025-05-16", 
            startTime: "09:00", 
            endTime: "10:30", 
            title: "Deep Dive: Kiến trúc AI hiện đại", 
            speaker: "Lê Văn Bách",
            description: "Phân tích chuyên sâu về các mô hình AI tiên tiến"
        }
    ];
    
    // Group schedule by day
    const scheduleByDay = {};
    sampleSchedule.forEach(item => {
        if (!scheduleByDay[item.eventDate]) {
            scheduleByDay[item.eventDate] = [];
        }
        scheduleByDay[item.eventDate].push(item);
    });
    
    // Create tabs for days
    let daysHtml = '';
    let tabPanesHtml = '';
    let isFirst = true;
    
    Object.keys(scheduleByDay).forEach(day => {
        const date = new Date(day);
        const formattedDate = date.toLocaleDateString('vi-VN', {
            weekday: 'long',
            day: 'numeric',
            month: 'numeric'
        });
        
        const tabId = `tab-${day.replace(/\-/g, '')}`;
        const activeClass = isFirst ? 'active' : '';
        
        daysHtml += `
            <li class="nav-item" role="presentation">
                <button class="nav-link ${activeClass}" id="${tabId}-tab" data-bs-toggle="tab" 
                        data-bs-target="#${tabId}" type="button" role="tab" 
                        aria-controls="${tabId}" aria-selected="${isFirst ? 'true' : 'false'}">
                    ${formattedDate}
                </button>
            </li>
        `;
        
        let dayScheduleHtml = '';
        scheduleByDay[day].forEach(item => {
            dayScheduleHtml += `
                <div class="timeline-item">
                    <span class="badge bg-primary mb-2">${item.startTime} - ${item.endTime}</span>
                    <h5 class="fw-bold mb-1">${item.title}</h5>
                    ${item.speaker ? `<p class="text-primary">Người trình bày: ${item.speaker}</p>` : ''}
                    <p>${item.description}</p>
                </div>
            `;
        });
        
        tabPanesHtml += `
            <div class="tab-pane fade show ${activeClass}" id="${tabId}" role="tabpanel" aria-labelledby="${tabId}-tab">
                <div class="timeline">
                    ${dayScheduleHtml}
                </div>
            </div>
        `;
        
        isFirst = false;
    });        // Create full HTML with notice that this is fallback data
    const agendaHtml = `
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i> Hiển thị lịch trình mẫu do không thể kết nối với API.
        </div>
        <ul class="nav nav-tabs mb-4" id="dayTabs" role="tablist">
            ${daysHtml}
        </ul>
        <div class="tab-content" id="dayTabsContent">
            ${tabPanesHtml}
        </div>
    `;
    
    // In ra console để debug
    console.log("Fallback agenda HTML generated");
    
    // Hiển thị lịch trình trong agendaContainer
    if (agendaContainer) {
        agendaContainer.innerHTML = agendaHtml;
    } else {
        console.error("agendaContainer not found!");
    }
}

function showConferenceNotFound() {
    // Thay thế nội dung chính của trang với thông báo lỗi
    document.querySelector('.container.my-5').innerHTML = `
        <div class="container py-5 text-center">
            <div class="alert alert-warning">
                <h3>Không tìm thấy hội nghị</h3>
                <p>Hội nghị bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
                <a href="conferences.html" class="btn btn-primary">Xem tất cả hội nghị</a>
            </div>
        </div>
    `;
}

function showErrorMessage(message) {
    // Hiển thị thông báo lỗi chi tiết để dễ debug
    document.querySelector('.container.my-5').innerHTML = `
        <div class="container py-5 text-center">
            <div class="alert alert-danger">
                <h3>Đã xảy ra lỗi</h3>
                <p>${message}</p>
                <div class="mt-3">
                    <a href="conferences.html" class="btn btn-primary mr-2">Xem tất cả hội nghị</a>
                    <button onclick="location.reload()" class="btn btn-outline-secondary">Thử lại</button>
                </div>
                <div class="mt-3 text-left">
                    <small class="text-muted">Gợi ý: Hãy kiểm tra console để xem thông tin chi tiết về lỗi.</small>
                </div>
            </div>
        </div>
    `;
}

function showSpeakerModal(speakerName) {
    // This function would show a modal with speaker details
    // For now, just showing an alert
    alert(`Thông tin chi tiết về diễn giả ${speakerName} sẽ hiển thị ở đây.`);
}

function joinConference() {
    // Lấy ID của hội nghị từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (!conferenceId) {
        alert('Không tìm thấy thông tin hội nghị.');
        return;
    }
    
    // Check if user is logged in
    if (isLoggedIn()) {
        // Redirect to registration page or process registration
        window.location.href = `conference-register.html?id=${conferenceId}`;
    } else {
        // Redirect to login page with return URL
        window.location.href = `login.html?redirect=conference-detail.html?id=${conferenceId}`;
    }
}

function shareConference() {
    // Lấy ID của hội nghị từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (!conferenceId) {
        alert('Không tìm thấy thông tin hội nghị để chia sẻ.');
        return;
    }
    
    const conferenceTitle = document.getElementById('conference-title').textContent;
    const conferenceUrl = `${window.location.origin}/conference-detail.html?id=${conferenceId}`;
    
    // Kiểm tra xem trình duyệt có hỗ trợ Web Share API không
    if (navigator.share) {
        navigator.share({
            title: conferenceTitle,
            text: `Tôi muốn mời bạn tham dự hội nghị: ${conferenceTitle}`,
            url: conferenceUrl
        })
        .then(() => console.log('Đã chia sẻ thành công'))
        .catch((error) => console.log('Lỗi khi chia sẻ:', error));
    } else {
        // Fallback cho trường hợp không hỗ trợ Web Share API
        // Copy URL vào clipboard
        const tempInput = document.createElement('input');
        document.body.appendChild(tempInput);
        tempInput.value = conferenceUrl;
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        alert(`Đã sao chép đường dẫn: ${conferenceUrl}\nBạn có thể dán và chia sẻ với bạn bè.`);
    }
}
