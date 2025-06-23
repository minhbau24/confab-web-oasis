// Conference detail page JavaScript logic - API version sửa lỗi

document.addEventListener('DOMContentLoaded', function() {
    loadConferenceDetail();
});

async function loadConferenceDetail() {
    // Get conference ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (!conferenceId) {
        showConferenceNotFound();
        return;
    }
    
    // Display loading indicator
    showLoadingIndicator();

    try {
        let conferenceData = null;
        
        // 1. Thử API mới đầu tiên với xử lý lỗi tốt hơn
        try {
            console.log(`Trying new API: api/conference_by_id.php?id=${conferenceId}`);
            const response1 = await fetch(`api/conference_by_id.php?id=${conferenceId}`, {
                cache: 'no-cache',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (response1.ok) {
                const rawText1 = await response1.text();
                console.log("Raw response from new API:", rawText1.substring(0, 200));
                
                // Kiểm tra xem response có phải là JSON không
                if (rawText1.trim().startsWith('{') || rawText1.trim().startsWith('[')) {
                    const result = JSON.parse(rawText1);
                    if (result.status && result.data) {
                        conferenceData = result.data;
                        console.log("✅ Dữ liệu từ API mới:", conferenceData);
                    }
                } else {
                    console.warn("API mới trả về HTML thay vì JSON:", rawText1.substring(0, 100));
                }
            }
        } catch (err1) {
            console.warn("❌ Không thể sử dụng API mới:", err1.message);
        }
        
        // 2. Nếu API mới không thành công, thử API cũ
        if (!conferenceData) {
            try {
                console.log(`Trying old API: api/conferences.php?id=${conferenceId}`);
                const response2 = await fetch(`api/conferences.php?id=${conferenceId}`, {
                    cache: 'no-cache',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response2.ok) {
                    const rawText2 = await response2.text();
                    console.log("Raw response from old API:", rawText2.substring(0, 200));
                    
                    // Kiểm tra xem response có phải là JSON không
                    if (rawText2.trim().startsWith('{') || rawText2.trim().startsWith('[')) {
                        const result = JSON.parse(rawText2);
                        if (result.status && result.data) {
                            conferenceData = result.data;
                            console.log("✅ Dữ liệu từ API cũ:", conferenceData);
                        } else {
                            console.warn("API cũ trả về status false:", result);
                        }
                    } else {
                        console.warn("API cũ trả về HTML thay vì JSON:", rawText2.substring(0, 100));
                    }
                } else {
                    console.error("API cũ trả về lỗi HTTP:", response2.status);
                }
            } catch (err2) {
                console.warn("❌ Lỗi khi gọi API cũ:", err2.message);
            }
        }
        
        // 3. Nếu vẫn không có dữ liệu, thử API debug
        if (!conferenceData) {
            try {
                console.log(`Trying debug API: api/debug_conference.php?id=${conferenceId}`);
                const response3 = await fetch(`api/debug_conference.php?id=${conferenceId}`, {
                    cache: 'no-cache',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response3.ok) {
                    const rawText3 = await response3.text();
                    console.log("Raw response from debug API:", rawText3.substring(0, 200));
                    
                    // Kiểm tra xem response có phải là JSON không
                    if (rawText3.trim().startsWith('{') || rawText3.trim().startsWith('[')) {
                        const debugResult = JSON.parse(rawText3);
                        console.log("Kết quả debug:", debugResult);
                        
                        // Nếu có raw_conference_data, sử dụng nó làm dữ liệu khẩn cấp
                        if (debugResult.status && debugResult.raw_conference_data) {
                            conferenceData = debugResult.raw_conference_data;
                            
                            // Thêm thông tin từ category và venue nếu có
                            if (debugResult.category_data) {
                                conferenceData.category_name = debugResult.category_data.name;
                                conferenceData.category_slug = debugResult.category_data.slug;
                                conferenceData.category_color = debugResult.category_data.color;
                            }
                            
                            if (debugResult.venue_data) {
                                conferenceData.venue_name = debugResult.venue_data.name;
                                conferenceData.venue_city = debugResult.venue_data.city;
                                conferenceData.venue_address = debugResult.venue_data.address;
                            }
                            
                            console.log("✅ Dữ liệu khôi phục từ debug API:", conferenceData);
                        }
                    } else {
                        console.warn("Debug API trả về HTML thay vì JSON:", rawText3.substring(0, 100));
                    }
                }
            } catch (err3) {
                console.warn("❌ Lỗi khi gọi API debug:", err3.message);
            }
        }
        
        // 4. Nếu vẫn không có dữ liệu, sử dụng dữ liệu mẫu
        if (!conferenceData) {
            console.warn("🔄 Sử dụng dữ liệu mẫu do không thể kết nối API");
            conferenceData = getFallbackConferenceData(conferenceId);
        }
        
        // Hiển thị dữ liệu hội nghị
        if (conferenceData) {
            await displayConferenceData(conferenceData, conferenceId);
        } else {
            showConferenceNotFound();
        }
        
    } catch (error) {
        console.error("❌ Lỗi chung khi tải thông tin hội nghị:", error);
        // Sử dụng dữ liệu mẫu trong trường hợp lỗi nghiêm trọng
        const fallbackData = getFallbackConferenceData(conferenceId);
        await displayConferenceData(fallbackData, conferenceId);
    }
}

// Hiển thị loading indicator
function showLoadingIndicator() {
    const heroSection = document.getElementById('hero-section');
    if (heroSection) {
        // Hiển thị loading trong hero section
        document.getElementById('conference-title').textContent = 'Đang tải...';
        document.getElementById('conference-description').textContent = 'Đang tải thông tin hội nghị...';
        document.getElementById('conference-date').textContent = 'Đang tải...';
        document.getElementById('conference-location').textContent = 'Đang tải...';
        document.getElementById('conference-attendees').textContent = 'Đang tải...';
        
        // Cập nhật detailed description
        const detailedDescElement = document.getElementById('detailed-description');
        if (detailedDescElement) {
            detailedDescElement.textContent = 'Đang tải thông tin chi tiết...';
        }
    }
}

// Hiển thị dữ liệu hội nghị
async function displayConferenceData(conferenceData, conferenceId) {
    try {
        // Update page title
        document.title = `ConferenceHub - ${conferenceData.title}`;
        
        // Update hero section
        if (typeof updateHeroSection === 'function') {
            updateHeroSection(conferenceData);
        } else {
            updateHeroSectionFallback(conferenceData);
        }
        
        // Render conference details
        if (typeof updateConferenceDetails === 'function') {
            updateConferenceDetails(conferenceData);
        }
        
        // Update các phần khác dựa trên dữ liệu có sẵn
        if (typeof updateObjectivesAndFeatures === 'function') {
            updateObjectivesAndFeatures(conferenceData);
        } else {
            updateObjectivesAndFeaturesFallback(conferenceData);
        }
        
        // Update audience section
        updateAudienceSection(conferenceData);
        
        // Update venue details
        updateVenueDetails(conferenceData);
        
        if (typeof renderFAQ === 'function') {
            renderFAQ(conferenceData);
        } else {
            renderFAQFallback(conferenceData);
        }
        
        if (typeof renderSponsors === 'function') {
            renderSponsors(conferenceData);
        }
        
        // Load speakers và schedule
        await loadSpeakersWithFallback(conferenceId);
        await loadAgendaWithFallback(conferenceId);
        
        // Load hội nghị liên quan
        if (typeof loadRelatedConferences === 'function') {
            await loadRelatedConferences(conferenceId);
        } else {
            await loadRelatedConferencesFallback(conferenceId);
        }
        
    } catch (error) {
        console.error("Lỗi khi hiển thị dữ liệu:", error);
    }
}

// Fallback conference data
function getFallbackConferenceData(conferenceId) {
    const fallbackData = {
        1: {
            id: 1,
            title: 'Vietnam Tech Summit 2025',
            short_description: 'Hội nghị công nghệ lớn nhất Việt Nam năm 2025',
            description: 'Hội nghị tập trung vào các xu hướng công nghệ mới như AI, Blockchain, IoT và Digital Transformation. Sự kiện quy tụ hơn 1000 chuyên gia công nghệ hàng đầu từ khắp nơi trên thế giới.',
            start_date: '2025-08-15 08:00:00',
            end_date: '2025-08-16 18:00:00',
            category_name: 'Công nghệ',
            venue_name: 'Trung tâm Hội nghị Quốc gia',
            venue_city: 'TP. Hồ Chí Minh',
            location: 'TP. Hồ Chí Minh',
            price: 2500000,
            capacity: 1000,
            current_attendees: 450,
            image: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop',
            banner_image: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&h=600&fit=crop'
        },
        2: {
            id: 2,
            title: 'Startup Weekend Ho Chi Minh 2025',
            short_description: 'Cuối tuần khởi nghiệp dành cho các bạn trẻ có ý tưởng kinh doanh',
            description: 'Sự kiện 54 giờ liên tục giúp các bạn trẻ biến ý tưởng thành startup thực tế. Có sự tham gia của các mentor và nhà đầu tư hàng đầu.',
            start_date: '2025-07-11 18:00:00',
            end_date: '2025-07-13 20:00:00',
            category_name: 'Khởi nghiệp',
            venue_name: 'Saigon Innovation Hub',
            venue_city: 'TP. Hồ Chí Minh',
            location: 'TP. Hồ Chí Minh',
            price: 500000,
            capacity: 200,
            current_attendees: 85,
            image: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=800&h=600&fit=crop',
            banner_image: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&h=600&fit=crop'
        }
    };
    
    return fallbackData[conferenceId] || fallbackData[1];
}

// Fallback hero section update
function updateHeroSectionFallback(conference) {
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
    
    // Update detailed description section
    const detailedDescElement = document.getElementById('detailed-description');
    if (detailedDescElement) {
        detailedDescElement.textContent = conference.description || conference.short_description || 'Thông tin chi tiết sẽ được cập nhật sớm.';
    }
    
    // Handle attendees data if available
    if (conference.current_attendees !== undefined && conference.capacity !== undefined) {
        document.getElementById('conference-attendees').textContent = `${conference.current_attendees}/${conference.capacity} người tham dự`;
        document.getElementById('spots-remaining').textContent = `Còn ${conference.capacity - conference.current_attendees} chỗ trống`;
    }
    
    document.getElementById('conference-price').textContent = priceFormatted;
    
    // Update hero background if image available
    const heroSection = document.getElementById('hero-section');
    const conferenceImage = conference.banner_image || conference.image;
    if (conferenceImage) {
        heroSection.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('${conferenceImage}')`;
    }
}

// Load speakers with fallback
async function loadSpeakersWithFallback(conferenceId) {
    const speakersContainer = document.getElementById('speakers-section');
    if (!speakersContainer) return;
    
    try {
        // Thử API speakers
        const response = await fetch(`api/conferences.php?id=${conferenceId}&speakers=1`, {
            cache: 'no-cache',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const rawText = await response.text();
            if (rawText.trim().startsWith('{') || rawText.trim().startsWith('[')) {
                const result = JSON.parse(rawText);
                if (result.status && result.data && result.data.length > 0) {
                    const speakers = result.data;
                    displaySpeakers(speakers);
                    return;
                }
            }
        }
    } catch (error) {
        console.warn("Lỗi khi tải speakers:", error);
    }
    
    // Fallback speakers data
    const fallbackSpeakers = [
        {
            name: "Nguyễn Minh Tuấn",
            title: "Chief Technology Officer",
            company: "FPT Software",
            bio: "CTO FPT Software, chuyên gia AI với 15+ năm kinh nghiệm",
            image: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop&crop=face"
        },
        {
            name: "Dr. Lê Thị Hương",
            title: "Director of AI Research", 
            company: "Vingroup",
            bio: "Giám đốc Nghiên cứu AI Vingroup, chuyên gia Healthcare AI",
            image: "https://images.unsplash.com/photo-1494790108755-2616b62dd6c3?w=400&h=400&fit=crop&crop=face"
        }
    ];
    
    displaySpeakers(fallbackSpeakers);
}

// Display speakers
function displaySpeakers(speakers) {
    const speakersContainer = document.getElementById('speakers-section');
    if (!speakersContainer) return;
    
    const speakersHtml = speakers.map(speaker => `
        <div class="col-md-6 mb-4">
            <div class="speaker-card card h-100">
                <div class="card-body text-center">
                    <img src="${speaker.image}" 
                         class="rounded-circle mb-3" width="120" height="120" alt="${speaker.name}">
                    <h5 class="card-title">${speaker.name}</h5>
                    <p class="text-primary mb-2">${speaker.title}${speaker.company ? `, ${speaker.company}` : ''}</p>
                    <p class="card-text">${speaker.bio || 'Chuyên gia hàng đầu trong lĩnh vực'}</p>
                </div>
            </div>
        </div>
    `).join('');
    
    speakersContainer.innerHTML = speakersHtml;
}

// Load agenda with fallback
async function loadAgendaWithFallback(conferenceId) {
    const agendaContainer = document.getElementById('agendaAccordion');
    if (!agendaContainer) return;
    
    try {
        // Thử API schedule
        const response = await fetch(`api/conferences.php?id=${conferenceId}&schedule=1`, {
            cache: 'no-cache',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const rawText = await response.text();
            if (rawText.trim().startsWith('{') || rawText.trim().startsWith('[')) {
                const result = JSON.parse(rawText);
                if (result.status && result.data && result.data.length > 0) {
                    const schedule = result.data;
                    displaySchedule(schedule);
                    return;
                }
            }
        }
    } catch (error) {
        console.warn("Lỗi khi tải schedule:", error);
    }
    
    // Fallback schedule data
    const fallbackSchedule = [
        {
            session_date: '2025-08-15',
            start_time: '09:00',
            end_time: '10:00',
            title: 'Khai mạc hội nghị',
            description: 'Lễ khai mạc và giới thiệu chương trình',
            speaker_name: 'Ban tổ chức'
        },
        {
            session_date: '2025-08-15',
            start_time: '10:30',
            end_time: '11:30',
            title: 'Keynote: Tương lai của AI',
            description: 'Phân tích xu hướng AI trong tương lai',
            speaker_name: 'Nguyễn Minh Tuấn'
        }
    ];
    
    displaySchedule(fallbackSchedule);
}

// Display schedule
function displaySchedule(schedule) {
    const agendaContainer = document.getElementById('agendaAccordion');
    if (!agendaContainer) return;
    
    // Group schedule by day
    const scheduleByDay = {};
    schedule.forEach(item => {
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
}

// Update objectives and features fallback
function updateObjectivesAndFeaturesFallback(conference) {
    // Update objectives list
    const objectivesList = document.getElementById('objectives-list');
    if (objectivesList) {
        const objectives = conference.objectives || [
            'Nắm bắt các xu hướng công nghệ mới nhất',
            'Networking với các chuyên gia hàng đầu',
            'Tìm hiểu về các giải pháp ứng dụng thực tế',
            'Cập nhật kiến thức và kỹ năng chuyên môn'
        ];
        
        const objectivesHtml = objectives.map(objective => 
            `<li><i class="fas fa-check text-primary me-2"></i>${objective}</li>`
        ).join('');
        
        objectivesList.innerHTML = objectivesHtml;
    }
    
    // Update features list
    const featuresList = document.getElementById('features-list');
    if (featuresList) {
        const features = conference.features || [
            {
                icon: 'fas fa-users',
                title: 'Networking',
                description: 'Kết nối với chuyên gia và doanh nghiệp'
            },
            {
                icon: 'fas fa-certificate',
                title: 'Chứng chỉ',
                description: 'Nhận chứng chỉ tham dự có giá trị'
            },
            {
                icon: 'fas fa-gift',
                title: 'Quà tặng',
                description: 'Quà tặng và tài liệu độc quyền'
            },
            {
                icon: 'fas fa-utensils',
                title: 'Ăn uống',
                description: 'Buffet cao cấp và coffee break'
            }
        ];
        
        const featuresHtml = features.map(feature => 
            `<div class="col-md-6 mb-3">
                <div class="d-flex align-items-start">
                    <div class="feature-icon me-3">
                        <i class="${feature.icon} fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">${feature.title}</h6>
                        <p class="mb-0 text-muted">${feature.description}</p>
                    </div>
                </div>
            </div>`
        ).join('');
        
        featuresList.innerHTML = featuresHtml;
    }
}

// Update audience section
function updateAudienceSection(conference) {
    const audienceSection = document.getElementById('audience-section');
    if (audienceSection) {
        const audiences = conference.target_audience || [
            {
                title: 'Lập trình viên',
                description: 'Developers muốn cập nhật công nghệ mới',
                icon: 'fas fa-code'
            },
            {
                title: 'Quản lý dự án',
                description: 'PM/BA quan tâm đến digital transformation',
                icon: 'fas fa-tasks'
            },
            {
                title: 'Startup',
                description: 'Founder tìm kiếm cơ hội và đối tác',
                icon: 'fas fa-rocket'
            },
            {
                title: 'Sinh viên',
                description: 'Sinh viên IT muốn mở rộng kiến thức',
                icon: 'fas fa-graduation-cap'
            }
        ];
        
        const audienceHtml = audiences.map(audience => 
            `<div class="col-md-6 mb-4">
                <div class="text-center">
                    <div class="audience-icon mb-3">
                        <i class="${audience.icon} fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">${audience.title}</h5>
                    <p class="text-muted">${audience.description}</p>
                </div>
            </div>`
        ).join('');
        
        audienceSection.innerHTML = audienceHtml;
    }
}

// Update venue details
function updateVenueDetails(conference) {
    const venueDetails = document.getElementById('venue-details');
    if (venueDetails) {
        const venueName = conference.venue_name || 'Địa điểm sẽ được thông báo';
        const venueAddress = conference.venue_address || conference.location || 'Địa chỉ sẽ được cập nhật';
        const venueCity = conference.venue_city || '';
        
        const venueHtml = `
            <h5 class="card-title mb-3">
                <i class="fas fa-map-marker-alt text-primary me-2"></i>${venueName}
            </h5>
            <p class="mb-2">
                <i class="fas fa-location-dot me-2"></i>
                <strong>Địa chỉ:</strong> ${venueAddress}${venueCity ? `, ${venueCity}` : ''}
            </p>
            <p class="mb-2">
                <i class="fas fa-car me-2"></i>
                <strong>Đỗ xe:</strong> Có bãi đỗ xe miễn phí
            </p>
            <p class="mb-0">
                <i class="fas fa-bus me-2"></i>
                <strong>Giao thông:</strong> Thuận tiện di chuyển bằng xe buýt và taxi
            </p>
        `;
        
        venueDetails.innerHTML = venueHtml;
    }
    
    // Update venue map
    const venueMap = document.getElementById('venue-map');
    if (venueMap) {
        const mapHtml = `
            <div class="embed-responsive embed-responsive-16by9">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4205729999!2d106.692430515427!3d10.776530462205!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f4b3bb0ee67%3A0x1e1c4c2d1e1e1e1e!2sVietnam%20National%20Convention%20Center!5e0!3m2!1sen!2s!4v1234567890123!5m2!1sen!2s" 
                        width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade" class="rounded">
                </iframe>
            </div>
        `;
        
        venueMap.innerHTML = mapHtml;
    }
}

// Render FAQ fallback
function renderFAQFallback(conference) {
    const faqAccordion = document.getElementById('faqAccordion');
    if (faqAccordion) {
        const faqs = conference.faqs || [
            {
                question: 'Tôi có cần mang laptop không?',
                answer: 'Có, bạn nên mang laptop để tham gia các workshop thực hành. Chúng tôi cũng cung cấp WiFi miễn phí.'
            },
            {
                question: 'Có cấp chứng chỉ tham dự không?',
                answer: 'Có, tất cả học viên hoàn thành khóa học sẽ nhận được chứng chỉ tham dự có giá trị.'
            },
            {
                question: 'Có hỗ trợ ăn uống không?',
                answer: 'Có, chúng tôi cung cấp coffee break và buffet trưa cho tất cả người tham dự.'
            }
        ];
        
        // Tìm các accordion item hiện có và cập nhật nội dung
        faqs.forEach((faq, index) => {
            const faqId = `faq${index + 1}`;
            const questionElement = document.querySelector(`#${faqId} ~ .accordion-header button`);
            const answerElement = document.querySelector(`#${faqId} .accordion-body`);
            
            if (questionElement) {
                questionElement.textContent = faq.question;
            }
            if (answerElement) {
                answerElement.textContent = faq.answer;
            }
        });
    }
}

function showConferenceNotFound() {
    const mainContainer = document.querySelector('.container.my-5');
    if (mainContainer) {
        mainContainer.innerHTML = `
            <div class="container py-5 text-center">
                <div class="alert alert-warning">
                    <h3>Không tìm thấy hội nghị</h3>
                    <p>Hội nghị bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
                    <a href="conferences.html" class="btn btn-primary">Xem tất cả hội nghị</a>
                </div>
            </div>
        `;
    }
}

function showErrorMessage(message) {
    const mainContainer = document.querySelector('.container.my-5');
    if (mainContainer) {
        mainContainer.innerHTML = `
            <div class="container py-5 text-center">
                <div class="alert alert-danger">
                    <h3>Đã xảy ra lỗi</h3>
                    <p>${message}</p>
                    <div class="mt-3">
                        <a href="conferences.html" class="btn btn-primary me-2">Xem tất cả hội nghị</a>
                        <button onclick="location.reload()" class="btn btn-outline-secondary">Thử lại</button>
                    </div>
                </div>
            </div>
        `;
    }
}

// Check if user is logged in (placeholder function)
function isLoggedIn() {
    return localStorage.getItem('user_token') !== null;
}

function joinConference() {
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (!conferenceId) {
        alert('Không tìm thấy thông tin hội nghị.');
        return;
    }
    
    if (isLoggedIn()) {
        window.location.href = `conference-register.html?id=${conferenceId}`;
    } else {
        window.location.href = `login.html?redirect=conference-detail.html?id=${conferenceId}`;
    }
}

function shareConference() {
    const urlParams = new URLSearchParams(window.location.search);
    const conferenceId = urlParams.get('id');
    
    if (!conferenceId) {
        alert('Không tìm thấy thông tin hội nghị để chia sẻ.');
        return;
    }
    
    const conferenceTitle = document.getElementById('conference-title').textContent;
    const conferenceUrl = `${window.location.origin}/conference-detail.html?id=${conferenceId}`;
    
    if (navigator.share) {
        navigator.share({
            title: conferenceTitle,
            text: `Tôi muốn mời bạn tham dự hội nghị: ${conferenceTitle}`,
            url: conferenceUrl
        });
    } else {
        navigator.clipboard.writeText(conferenceUrl).then(() => {
            alert(`Đã sao chép đường dẫn: ${conferenceUrl}`);
        });
    }
}

// Load hội nghị liên quan fallback
async function loadRelatedConferencesFallback(conferenceId) {
    const relatedContainer = document.getElementById('related-conferences-container');
    if (!relatedContainer) return;
    
    // Dữ liệu hội nghị liên quan mẫu
    const relatedConferences = [
        {
            id: 2,
            title: 'Startup Weekend Ho Chi Minh 2025',
            short_description: 'Cuối tuần khởi nghiệp dành cho các bạn trẻ có ý tưởng kinh doanh',
            start_date: '2025-07-11',
            location: 'TP. Hồ Chí Minh',
            price: 500000,
            image: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=400&h=300&fit=crop'
        },
        {
            id: 3,
            title: 'Digital Marketing Summit 2025',
            short_description: 'Hội nghị Marketing số lớn nhất năm',
            start_date: '2025-09-20',
            location: 'Hà Nội',
            price: 1500000,
            image: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=400&h=300&fit=crop'
        },
        {
            id: 4,
            title: 'Blockchain & Fintech Expo 2025',
            short_description: 'Triển lãm công nghệ Blockchain và Fintech',
            start_date: '2025-10-15',
            location: 'Đà Nẵng',
            price: 2000000,
            image: 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=400&h=300&fit=crop'
        }
    ];
    
    // Filter out current conference
    const filteredConferences = relatedConferences.filter(conf => conf.id != conferenceId);
    
    const relatedHtml = filteredConferences.map(conf => {
        const startDate = new Date(conf.start_date);
        const dateFormatted = startDate.toLocaleDateString('vi-VN', {
            day: 'numeric',
            month: 'numeric',
            year: 'numeric'
        });
        
        const priceFormatted = parseInt(conf.price).toLocaleString('vi-VN', {
            style: 'currency',
            currency: 'VND',
            maximumFractionDigits: 0
        });
        
        return `
            <div class="col-md-4 mb-4">
                <div class="card conference-card h-100">
                    <img src="${conf.image}" class="card-img-top" alt="${conf.title}" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${conf.title}</h6>
                        <p class="card-text text-muted small flex-grow-1">${conf.short_description}</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>${dateFormatted}
                                </small>
                                <span class="badge bg-primary">${priceFormatted}</span>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>${conf.location}
                                </small>
                            </div>
                            <a href="conference-detail.html?id=${conf.id}" class="btn btn-outline-primary btn-sm mt-2 w-100">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    relatedContainer.innerHTML = relatedHtml;
}
