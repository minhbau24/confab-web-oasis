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
                const conference = result.data;                // Update page title
                document.title = `ConferenceHub - ${conference.title}`;
                
                // Update hero section
                updateHeroSection(conference);
                
                // Render conference details
                updateConferenceDetails(conference);
                  // Update objectives and features if available
                updateObjectivesAndFeatures(conference);
                
                // Render FAQ and sponsors
                renderFAQ(conference);
                renderSponsors(conference);
                
                // Load related conferences
                loadRelatedConferences(conferenceId);
                
                // Fetch and load speakers
                await loadSpeakers(conferenceId);
                
                // Fetch and load agenda (schedule)
                await loadAgenda(conferenceId);
                
                // Update objectives and features
                updateObjectivesAndFeatures(conference);
                
                // Load related conferences
                await loadRelatedConferences(conferenceId);
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
    const startDate = new Date(conference.start_date);
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
    });    // Update hero section content
    document.getElementById('conference-category').textContent = conference.category_name || 'Hội nghị';
    document.getElementById('conference-title').textContent = conference.title;
    document.getElementById('conference-description').textContent = conference.short_description || conference.description;
    document.getElementById('conference-date').textContent = dateDisplay;
    document.getElementById('conference-location').textContent = conference.venue_name ? `${conference.venue_name}, ${conference.venue_city}` : conference.location;
      // Handle attendees data if available
    if (conference.current_attendees !== undefined && conference.capacity !== undefined) {
        document.getElementById('conference-attendees').textContent = `${conference.current_attendees}/${conference.capacity} người tham dự`;
        document.getElementById('spots-remaining').textContent = `Còn ${conference.capacity - conference.current_attendees} chỗ trống`;
    }
    
    document.getElementById('conference-price').textContent = priceFormatted;
    
    // Update hero background if image available
    const heroSection = document.getElementById('hero-section');
    // Ưu tiên sử dụng banner_image nếu có, nếu không thì dùng image
    const conferenceImage = conference.banner_image || conference.image;
    if (conferenceImage) {
        heroSection.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('${conferenceImage}')`;
    }
}

function updateConferenceDetails(conference) {
    // Update detailed description
    const detailedDescriptionElement = document.getElementById('detailed-description');
    if (detailedDescriptionElement) {
        detailedDescriptionElement.textContent = conference.description;
    }
    
    // Update venue information if available
    const venueDetails = document.getElementById('venue-details');
    if (venueDetails) {
        // Sử dụng đối tượng venue nếu có, ngược lại sử dụng các trường venue_ riêng lẻ
        const venue = conference.venue || {};
        const venueName = venue.name || conference.venue_name;
        
        if (venueName) {
            let venueHtml = `
                <div class="mb-3">
                    <h6 class="mb-2"><i class="fas fa-building me-2"></i>${venueName}</h6>
                    <p class="mb-3">${venue.address || conference.venue_address || ''}</p>
                    <p class="mb-1">
                        ${venue.city || conference.venue_city || ''} 
                        ${venue.state || conference.venue_state || ''}
                        ${venue.postal_code || conference.venue_postal_code || ''}<br>
                        ${venue.country || conference.venue_country || ''}
                    </p>
                </div>
            `;
            
            const venueDescription = venue.description || conference.venue_description;
            if (venueDescription) {
                venueHtml += `
                    <div class="mb-3">
                        <p>${venueDescription}</p>
                    </div>
                `;
            }
            
            const transportInfo = venue.transport_info || conference.venue_transport_info;
            if (transportInfo) {
                venueHtml += `
                    <div class="mb-3">
                        <h6 class="mb-2"><i class="fas fa-bus me-2"></i>Thông tin di chuyển:</h6>
                        <p>${transportInfo}</p>
                    </div>
                `;
            }
            
            const parkingInfo = venue.parking_info || conference.venue_parking_info;
            if (parkingInfo) {
                venueHtml += `
                    <div class="mb-3">
                        <h6 class="mb-2"><i class="fas fa-parking me-2"></i>Thông tin đỗ xe:</h6>
                        <p>${parkingInfo}</p>
                    </div>
                `;
            }
            
            const accessibility = venue.accessibility || conference.venue_accessibility;
            if (accessibility) {
                venueHtml += `
                    <div class="mb-3">
                        <h6 class="mb-2"><i class="fas fa-wheelchair me-2"></i>Trợ năng:</h6>
                        <p>${accessibility}</p>
                    </div>
                `;
            }
            
            // Giữ lại phần iframe bản đồ nếu có
            const mapIframe = document.getElementById('venue-map');
            if (mapIframe) {
                venueHtml += `<div id="venue-map">${mapIframe.innerHTML}</div>`;
            }
            
            venueDetails.innerHTML = venueHtml;
        }
    }
}

function updateObjectivesAndFeatures(conference) {
    // Cập nhật mục tiêu nếu có
    if (conference.objectives || conference.metadata?.objectives) {
        const objectives = conference.objectives || conference.metadata?.objectives || [];
        const objectivesList = document.getElementById('objectives-list');
        
        if (objectivesList && Array.isArray(objectives) && objectives.length > 0) {
            objectivesList.innerHTML = objectives.map(objective => 
                `<li><i class="fas fa-check text-success me-2"></i>${objective}</li>`
            ).join('');
        }
    }
    
    // Cập nhật tính năng nổi bật nếu có
    if (conference.features || conference.metadata?.features) {
        const features = conference.features || conference.metadata?.features || [];
        const featuresList = document.getElementById('features-list');
        
        if (featuresList && Array.isArray(features) && features.length > 0) {
            featuresList.innerHTML = features.map(feature => 
                `<div class="col-md-6 mb-2">
                    <i class="${feature.icon || 'fas fa-check'} text-primary me-2"></i> ${feature.text || feature}
                </div>`
            ).join('');
        }
    }
    
    // Cập nhật đối tượng tham dự nếu có
    if (conference.audience || conference.metadata?.audience) {
        const audience = conference.audience || conference.metadata?.audience || [];
        const audienceSection = document.getElementById('audience-section');
        
        if (audienceSection && Array.isArray(audience) && audience.length > 0) {
            audienceSection.innerHTML = audience.map(item => 
                `<div class="col-md-6 mb-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="${item.icon || 'fas fa-user'} fa-3x text-primary mb-3"></i>
                            <h5>${item.title || item}</h5>
                            <p class="mb-0">${item.description || ''}</p>
                        </div>
                    </div>
                </div>`
            ).join('');
        }
    }
}

async function loadSpeakers(conferenceId) {
    const speakersContainer = document.getElementById('speakers-section');
    if (!speakersContainer) return;
    
    try {
        // Thử sử dụng API mới (conferences.php?id=X&speakers=1)
        const response = await fetch(`api/conferences.php?id=${conferenceId}&speakers=1`, {
            cache: 'no-cache',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        // Check if the response is OK 
        if (!response.ok) {
            // Nếu API mới không hoạt động, thử dùng API cũ
            return await loadSpeakersLegacy(conferenceId);
        }
        
        // Try to parse JSON response
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return await loadSpeakersLegacy(conferenceId);
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
                            <p class="text-primary mb-2">${speaker.title || speaker.speaker_title}${speaker.company ? `, ${speaker.company}` : ''}</p>
                            <p class="card-text">${speaker.bio || speaker.short_bio || 'Không có thông tin'}</p>
                            <div class="mt-3">
                                ${speaker.linkedin ? `<a href="${speaker.linkedin}" target="_blank" class="btn btn-sm btn-outline-secondary me-1"><i class="fab fa-linkedin"></i></a>` : ''}
                                ${speaker.twitter ? `<a href="${speaker.twitter}" target="_blank" class="btn btn-sm btn-outline-info me-1"><i class="fab fa-twitter"></i></a>` : ''}
                                ${speaker.website ? `<a href="${speaker.website}" target="_blank" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-globe"></i></a>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            speakersContainer.innerHTML = speakersHtml;
        } else {
            // Thử API cũ nếu API mới không trả về dữ liệu
            return await loadSpeakersLegacy(conferenceId);
        }
    } catch (error) {
        console.error("Error loading speakers from new API:", error);
        // Sử dụng API cũ trong trường hợp API mới gặp lỗi
        return await loadSpeakersLegacy(conferenceId);
    }
}

async function loadSpeakersLegacy(conferenceId) {
    const speakersContainer = document.getElementById('speakers-section');
    if (!speakersContainer) return;
    
    try {
        // Sử dụng API cũ
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
                            <p class="card-text">${speaker.bio || speaker.short_bio || 'Không có thông tin'}</p>
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
        console.error("Error loading speakers from legacy API:", error);
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
        // Thử sử dụng API mới (conferences.php?id=X&schedule=1) 
        const response = await fetch(`api/conferences.php?id=${conferenceId}&schedule=1`, {
            cache: 'no-cache',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        // Check if the response is OK
        if (!response.ok) {
            // Nếu API mới không hoạt động, thử dùng API cũ
            return await loadAgendaLegacy(conferenceId);
        }
          
        // Try to parse JSON response
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return await loadAgendaLegacy(conferenceId);
        }
        
        const result = await response.json();
        
        console.log("Schedule API response:", result);
        
        if (result.status && result.data && result.data.length > 0) {
            const schedule = result.data;
            
            // Group schedule by day
            const scheduleByDay = {};
            schedule.forEach(item => {
                // Sử dụng session_date từ schema mới nếu có, nếu không thì dùng eventDate
                const eventDate = item.session_date || item.eventDate;
                if (!scheduleByDay[eventDate]) {
                    scheduleByDay[eventDate] = [];
                }
                scheduleByDay[eventDate].push(item);
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
                    // Tương thích với cả schema cũ và mới
                    const startTime = item.start_time || item.startTime;
                    const endTime = item.end_time || item.endTime;
                    const speaker = item.speaker_name || item.speaker || '';
                    
                    dayScheduleHtml += `
                        <div class="timeline-item">
                            <span class="badge bg-primary mb-2">${startTime} - ${endTime}</span>
                            <h5 class="fw-bold mb-1">${item.title}</h5>
                            ${speaker ? `<p class="mb-2 text-primary"><i class="fas fa-user me-1"></i>${speaker}</p>` : ''}
                            <p class="mb-0">${item.description || ''}</p>
                        </div>
                    `;
                });
                
                tabPanesHtml += `
                    <div class="tab-pane fade ${isFirst ? 'show active' : ''}" id="${tabId}" role="tabpanel" aria-labelledby="${tabId}-tab">
                        ${dayScheduleHtml}
                    </div>
                `;
                
                isFirst = false;
            });
            
            const agendaHtml = `
                <ul class="nav nav-tabs mb-4" id="dayTabs" role="tablist">
                    ${daysHtml}
                </ul>
                <div class="tab-content" id="dayTabsContent">
                    ${tabPanesHtml}
                </div>
            `;
            
            agendaContainer.innerHTML = agendaHtml;
        } else {
            // Thử API cũ nếu API mới không trả về dữ liệu
            return await loadAgendaLegacy(conferenceId);
        }
    } catch (error) {
        console.error("Error loading agenda:", error);
        // Thử API cũ trong trường hợp lỗi
        return await loadAgendaLegacy(conferenceId);
    }
}

async function loadAgendaLegacy(conferenceId) {
    const agendaSection = document.getElementById('agenda-section');
    const agendaContainer = document.getElementById('agendaAccordion');
    if (!agendaSection || !agendaContainer) return;
    
    try {        
        console.log(`Loading schedule using legacy API for conference ID: ${conferenceId}`);
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
        
        console.log("Legacy Schedule API response:", result);
        
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
                            ${item.speaker ? `<p class="mb-2 text-primary"><i class="fas fa-user me-1"></i>${item.speaker}</p>` : ''}
                            <p class="mb-0">${item.description || ''}</p>
                        </div>
                    `;
                });
                
                tabPanesHtml += `
                    <div class="tab-pane fade ${isFirst ? 'show active' : ''}" id="${tabId}" role="tabpanel" aria-labelledby="${tabId}-tab">
                        ${dayScheduleHtml}
                    </div>
                `;
                
                isFirst = false;
            });
            
            const agendaHtml = `
                <ul class="nav nav-tabs mb-4" id="dayTabs" role="tablist">
                    ${daysHtml}
                </ul>
                <div class="tab-content" id="dayTabsContent">
                    ${tabPanesHtml}
                </div>
            `;
            
            agendaContainer.innerHTML = agendaHtml;
        } else {
            // Sử dụng dữ liệu mẫu trong trường hợp API không trả về dữ liệu
            useFallbackScheduleData(conferenceId, agendaContainer);
        }
    } catch (error) {
        console.error("Error loading schedule from legacy API:", error);
        // Sử dụng dữ liệu mẫu trong trường hợp lỗi
        useFallbackScheduleData(conferenceId, agendaContainer);
    }
}

// Hàm hiển thị các hội nghị liên quan
async function loadRelatedConferences(conferenceId) {
    const relatedSection = document.getElementById('related-conferences-section');
    const relatedContainer = document.getElementById('related-conferences-container');
    
    if (!relatedSection || !relatedContainer) return;
    
    try {
        const response = await fetch(`api/related_conferences.php?id=${conferenceId}&limit=3`, {
            cache: 'no-cache',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            relatedSection.style.display = 'none';
            return;
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            relatedSection.style.display = 'none';
            return;
        }
        
        const result = await response.json();
        
        if (result.status && result.data && result.data.length > 0) {
            const relatedConfs = result.data;
            
            const conferencesHtml = relatedConfs.map(conf => {
                // Định dạng ngày
                const startDate = new Date(conf.start_date);
                const dateFormatted = startDate.toLocaleDateString('vi-VN', {
                    day: 'numeric',
                    month: 'numeric',
                    year: 'numeric'
                });
                
                // Xử lý giá tiền
                const priceFormatted = parseInt(conf.price).toLocaleString('vi-VN', {
                    style: 'currency',
                    currency: 'VND',
                    maximumFractionDigits: 0
                });
                
                return `
                    <div class="col-md-4 mb-4">
                        <div class="card conference-card h-100">
                            <img src="${conf.banner_image || conf.image || 'public/placeholder.svg'}" 
                                 class="card-img-top" alt="${conf.title}" style="height: 160px; object-fit: cover;">
                            <div class="card-body">
                                <span class="badge bg-${conf.category_color || 'primary'} mb-2">${conf.category_name || 'Hội nghị'}</span>
                                <h5 class="card-title">${conf.title}</h5>
                                <p class="card-text text-truncate">${conf.short_description || conf.description}</p>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i> ${dateFormatted}
                                    </small>
                                    <a href="conference-detail.html?id=${conf.id}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            relatedContainer.innerHTML = conferencesHtml;
        } else {
            // Ẩn section nếu không có hội nghị liên quan
            relatedSection.style.display = 'none';
        }
    } catch (error) {
        console.error("Error loading related conferences:", error);
        // Ẩn section nếu có lỗi
        relatedSection.style.display = 'none';
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

// Hiển thị thông tin FAQ từ hội nghị
function renderFAQ(conference) {
    const faqContainer = document.getElementById('faqAccordion');
    if (!faqContainer) return;
    
    // Kiểm tra xem có dữ liệu FAQ không
    if (!conference.metadata || !conference.metadata.faq || !Array.isArray(conference.metadata.faq) || conference.metadata.faq.length === 0) {
        // Sử dụng dữ liệu mẫu nếu không có
        const sampleFAQs = [
            { question: "Tôi có thể hủy đăng ký không?", answer: "Có, bạn có thể hủy và nhận lại 80% phí đăng ký nếu hủy trước 30 ngày so với ngày diễn ra." },
            { question: "Các phiên hội nghị có được ghi lại không?", answer: "Có, tất cả các phiên chính sẽ được ghi lại và người tham dự có thể truy cập trong vòng 90 ngày sau sự kiện." },
            { question: "Có cấp chứng chỉ tham dự không?", answer: "Có, tất cả người tham dự sẽ nhận được chứng chỉ tham dự số có thể xác minh." }
        ];
        
        let faqHtml = '';
        sampleFAQs.forEach((faq, index) => {
            faqHtml += createFAQItem(faq.question, faq.answer, index);
        });
        
        faqContainer.innerHTML = faqHtml;
        return;
    }
    
    // Hiển thị FAQ từ dữ liệu hội nghị
    let faqHtml = '';
    conference.metadata.faq.forEach((faq, index) => {
        faqHtml += createFAQItem(faq.question, faq.answer, index);
    });
    
    faqContainer.innerHTML = faqHtml;
}

// Tạo một mục FAQ
function createFAQItem(question, answer, index) {
    return `
        <div class="faq-item">
            <div class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#faq${index}">
                    ${question}
                </button>
            </div>
            <div id="faq${index}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    ${answer}
                </div>
            </div>
        </div>
    `;
}

// Hiển thị danh sách nhà tài trợ
function renderSponsors(conference) {
    const sponsorsSection = document.getElementById('sponsors-section');
    const sponsorsContainer = document.getElementById('sponsors-container');
    
    if (!sponsorsSection || !sponsorsContainer) return;
    
    // Kiểm tra xem có dữ liệu nhà tài trợ không
    if (!conference.sponsors && (!conference.metadata || !conference.metadata.sponsors)) {
        sponsorsSection.style.display = 'none';
        return;
    }
    
    const sponsors = conference.sponsors || conference.metadata.sponsors;
    
    if (!Array.isArray(sponsors) || sponsors.length === 0) {
        sponsorsSection.style.display = 'none';
        return;
    }
    
    // Nhóm nhà tài trợ theo mức độ
    const sponsorsByTier = {};
    sponsors.forEach(sponsor => {
        const tier = sponsor.tier || 'other';
        if (!sponsorsByTier[tier]) {
            sponsorsByTier[tier] = [];
        }
        sponsorsByTier[tier].push(sponsor);
    });
    
    // Hiển thị nhà tài trợ theo mức độ
    let sponsorsHtml = '';
    
    // Xác định thứ tự hiển thị các tier
    const tierOrder = ['platinum', 'gold', 'silver', 'bronze', 'other'];
    
    // Tiêu đề hiển thị tiếng Việt cho từng tier
    const tierTitles = {
        'platinum': 'Nhà tài trợ Kim Cương',
        'gold': 'Nhà tài trợ Vàng',
        'silver': 'Nhà tài trợ Bạc',
        'bronze': 'Nhà tài trợ Đồng',
        'other': 'Nhà tài trợ khác'
    };
    
    tierOrder.forEach(tier => {
        if (sponsorsByTier[tier] && sponsorsByTier[tier].length > 0) {
            sponsorsHtml += `<div class="col-12 mb-4"><h4>${tierTitles[tier] || tier}</h4></div>`;
            
            sponsorsByTier[tier].forEach(sponsor => {
                sponsorsHtml += `
                    <div class="col-md-${tier === 'platinum' ? '6' : '4'} col-sm-6 mb-4">
                        <div class="card h-100 text-center border-0">
                            <div class="card-body">
                                <img src="${sponsor.logo || 'public/placeholder.svg'}" 
                                     class="img-fluid mb-3 brand-logo" style="max-height: 80px;" 
                                     alt="${sponsor.name}">
                                <h5 class="card-title">${sponsor.name}</h5>
                                ${sponsor.description ? `<p class="card-text small">${sponsor.description}</p>` : ''}
                                ${sponsor.website ? `<a href="${sponsor.website}" target="_blank" class="btn btn-sm btn-outline-primary">Trang web</a>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    });
    
    if (sponsorsHtml) {
        sponsorsContainer.innerHTML = sponsorsHtml;
    } else {
        sponsorsSection.style.display = 'none';
    }
}
