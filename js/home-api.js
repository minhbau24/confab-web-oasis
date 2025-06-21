// Home page JavaScript logic - API Version

document.addEventListener('DOMContentLoaded', function() {
    initializeHomePage();
});

/**
 * Initialize the home page content
 */
async function initializeHomePage() {
    try {
        // Kiểm tra URL hiện tại trước
        if (!checkCurrentUrl()) return;
        
        console.log('Fetching home API data...');
        
        // Sử dụng API helper để gọi API an toàn
        const result = await apiGet('api/home.php');
        
        if (result.success) {
            // Update featured conferences
            displayFeaturedConferences(result.data.featuredConferences);
            
            // Update statistics
            updateStatistics(result.data.stats);
            
            // Update testimonials
            displayTestimonials(result.data.testimonials);
            
            // Update user specific elements if user is logged in
            if (result.data.user) {
                updateUserElements(result.data.user);
            }
        } else {
            console.error("Error loading home page data:", result.error);
            // Fallback to static data
            loadFallbackData();
        }
    } catch (error) {
        console.error("Error loading home page data:", error);
        // Fallback to static data
        loadFallbackData();
    }
}

/**
 * Fallback to load static data when API fails
 */
function loadFallbackData() {
    // Load featured conferences from data.js
    const featuredConferences = getConferences().slice(0, 3);
    displayFeaturedConferences(featuredConferences);
    
    // Set default statistics
    updateStatistics({
        conferences: 25,
        speakers: 42,
        attendees: 1200,
        locations: 8
    });
    
    // Display default testimonials
    displayTestimonials([
        {
            name: "Nguyễn Văn A",
            company: "Công ty ABC",
            content: "Hội nghị được tổ chức rất chuyên nghiệp và đầy đủ tiện nghi.",
            rating: 5
        },
        {
            name: "Trần Thị B",
            company: "Tổ chức XYZ",
            content: "Tôi rất hài lòng với chất lượng hội nghị và các diễn giả.",
            rating: 5
        },
        {
            name: "Phạm Văn C",
            company: "Startup DEF",
            content: "Đây là một cơ hội tuyệt vời để kết nối và học hỏi từ các chuyên gia.",
            rating: 4
        }
    ]);
}

/**
 * Display featured conferences
 */
function displayFeaturedConferences(conferences) {
    const container = document.getElementById('featured-conferences-container');
    if (!container) return;
    
    if (conferences && conferences.length > 0) {
        container.innerHTML = conferences.map(conference => {
            return `
                <div class="col-md-4">
                    <div class="card upcoming-conference-card h-100">
                        <img src="${conference.image || 'public/placeholder.svg'}" class="card-img-top" alt="${conference.title}">
                        <div class="card-body">
                            <h5 class="card-title">${conference.title}</h5>
                            <div class="mb-2 text-muted">
                                <i class="fas fa-calendar me-2"></i>${formatDate(conference.date)}
                            </div>
                            <div class="mb-2 text-muted">
                                <i class="fas fa-map-marker-alt me-2"></i>${conference.location}
                            </div>
                            <div class="mb-3">
                                <span class="badge bg-primary">${conference.category}</span>
                                <span class="badge bg-info">${formatPrice(conference.price)}</span>
                            </div>
                            <p class="card-text">${truncateText(conference.description, 80)}</p>
                        </div>
                        <div class="card-footer">
                            <a href="conference-detail.html?id=${conference.id}" class="btn btn-outline-primary w-100">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } else {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Không có hội nghị nổi bật</h4>
                <p class="text-muted">Vui lòng quay lại sau để xem các sự kiện mới</p>
            </div>
        `;
    }
}

/**
 * Update statistics counters
 */
function updateStatistics(stats) {
    // Update conferences count
    const conferencesElement = document.getElementById('stats-conferences');
    if (conferencesElement) {
        conferencesElement.setAttribute('data-count', stats.conferences);
        conferencesElement.textContent = stats.conferences;
    }
    
    // Update speakers count
    const speakersElement = document.getElementById('stats-speakers');
    if (speakersElement) {
        speakersElement.setAttribute('data-count', stats.speakers);
        speakersElement.textContent = stats.speakers;
    }
    
    // Update attendees count
    const attendeesElement = document.getElementById('stats-attendees');
    if (attendeesElement) {
        attendeesElement.setAttribute('data-count', stats.attendees);
        attendeesElement.textContent = stats.attendees;
    }
    
    // Update locations count
    const locationsElement = document.getElementById('stats-locations');
    if (locationsElement) {
        locationsElement.setAttribute('data-count', stats.locations);
        locationsElement.textContent = stats.locations;
    }
    
    // Initialize count up animation
    animateCounters();
}

/**
 * Animate statistics counters
 */
function animateCounters() {
    document.querySelectorAll('.stats-count').forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const count = +counter.innerText;
        
        if (count < target) {
            const inc = target / 20;
            
            // Update counter for duration
            const interval = setInterval(() => {
                const newCount = Math.ceil(+counter.innerText + inc);
                
                if (newCount < target) {
                    counter.innerText = newCount;
                } else {
                    counter.innerText = target;
                    clearInterval(interval);
                }
            }, 50);
        }
    });
}

/**
 * Display testimonials
 */
function displayTestimonials(testimonials) {
    const container = document.getElementById('testimonials-container');
    if (!container) return;
    
    if (testimonials && testimonials.length > 0) {
        container.innerHTML = testimonials.map(testimonial => {
            const stars = getStarRating(testimonial.rating || 5);
            
            return `
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div>
                                <h5 class="mb-0">${testimonial.name}</h5>
                                <p class="text-muted mb-0">${testimonial.company || ''}</p>
                                <div class="text-warning">${stars}</div>
                            </div>
                        </div>
                        <p class="mb-0">"${testimonial.content}"</p>
                    </div>
                </div>
            `;
        }).join('');
    } else {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Chưa có đánh giá</h4>
            </div>
        `;
    }
}

/**
 * Update UI elements for logged in user
 */
function updateUserElements(user) {
    // Update call to action buttons if user is logged in
    if (user.role === 'organizer' || user.role === 'admin') {
        const organizeBtn = document.getElementById('organizeBtn');
        if (organizeBtn) {
            organizeBtn.href = 'conference-manager.html';
        }
        
        const ctaRegisterBtn = document.getElementById('ctaRegisterBtn');
        if (ctaRegisterBtn) {
            ctaRegisterBtn.href = 'conference-manager.html';
            ctaRegisterBtn.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tổ chức Hội nghị';
        }
    }
}

/**
 * Helper function to get star rating HTML
 */
function getStarRating(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star"></i> ';
        } else if (i - rating < 1) {
            stars += '<i class="fas fa-star-half-alt"></i> ';
        } else {
            stars += '<i class="far fa-star"></i> ';
        }
    }
    return stars;
}

/**
 * Helper function to format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Helper function to format price
 */
function formatPrice(price) {
    if (price === 0) return "Miễn phí";
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

/**
 * Helper function to truncate text
 */
function truncateText(text, maxLength) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}
