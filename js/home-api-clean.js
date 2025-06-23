/**
 * Home page API integration - Clean version
 * HTML for UI, PHP for data, JS for rendering
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeHomePage();
});

/**
 * Initialize the home page content
 */
async function initializeHomePage() {
    try {
        console.log('Loading home page data...');
        
        // Call API to get data
        const result = await apiGet('api/home.php');
        
        if (result && result.success) {
            console.log('Home data loaded successfully');
            
            // Render data using JS
            if (result.data.featuredConferences) {
                displayFeaturedConferences(result.data.featuredConferences);
            }
            
            if (result.data.stats) {
                updateStatistics(result.data.stats);
            }
            
            if (result.data.testimonials) {
                displayTestimonials(result.data.testimonials);
            }
            
            if (result.data.user) {
                updateUserElements(result.data.user);
            }
        } else {
            console.warn('API returned error or no data, using fallback');
            loadFallbackData();
        }
    } catch (error) {
        console.error('Error loading home data:', error);
        
        // Show friendly message and use fallback
        showApiErrorMessage();
        loadFallbackData();
    }
}

/**
 * Show user-friendly error message
 */
function showApiErrorMessage() {
    const container = document.querySelector('.container');
    if (container && container.firstChild) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-info alert-dismissible fade show mt-3';
        errorDiv.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            <strong>Thông báo:</strong> Đang sử dụng dữ liệu mẫu. 
            Một số tính năng có thể không khả dụng.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        container.insertBefore(errorDiv, container.firstChild);
    }
}

/**
 * Fallback to load static data when API fails
 */
function loadFallbackData() {
    console.log('Loading fallback data...');
    
    // Load featured conferences from data.js
    if (typeof getConferences === 'function') {
        const featuredConferences = getConferences().slice(0, 3);
        displayFeaturedConferences(featuredConferences);
    }
    
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
            rating: 5,
            avatar: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face",
            position: "CEO"
        },
        {
            name: "Trần Thị B",
            company: "StartupXYZ",
            content: "Tôi đã có được nhiều kết nối quý giá và học hỏi được nhiều kiến thức mới.",
            rating: 5,
            avatar: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=100&h=100&fit=crop&crop=face",
            position: "Founder"
        },
        {
            name: "Lê Văn C",
            company: "TechCorp",
            content: "Chất lượng diễn giả và nội dung hội nghị vượt xa mong đợi của tôi.",
            rating: 5,
            avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face",
            position: "CTO"
        }
    ]);
}

/**
 * Display featured conferences
 */
function displayFeaturedConferences(conferences) {
    const container = document.getElementById('featured-conferences');
    if (!container) return;
    
    const html = conferences.map(conf => `
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card conference-card h-100">
                <img src="${conf.image}" class="card-img-top" alt="${conf.title}" style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-primary mb-2 align-self-start">${conf.category}</span>
                    <h5 class="card-title">${conf.title}</h5>
                    <p class="card-text">${conf.description}</p>
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>${new Date(conf.date).toLocaleDateString('vi-VN')}
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>${conf.location}
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h6 text-primary mb-0">${conf.price.toLocaleString('vi-VN')} ₫</span>
                            <a href="conference-detail.html?id=${conf.id}" class="btn btn-outline-primary btn-sm">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

/**
 * Update statistics
 */
function updateStatistics(stats) {
    // Update counter elements if they exist
    const elements = {
        'conferences-count': stats.conferences,
        'speakers-count': stats.speakers,
        'attendees-count': stats.attendees,
        'locations-count': stats.locations
    };
    
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            animateCounter(element, elements[id]);
        }
    });
}

/**
 * Animate counter
 */
function animateCounter(element, target) {
    const start = 0;
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

/**
 * Display testimonials
 */
function displayTestimonials(testimonials) {
    const container = document.getElementById('testimonials-container');
    if (!container) return;
    
    const html = testimonials.map(testimonial => `
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card testimonial-card h-100">
                <div class="card-body text-center">
                    <img src="${testimonial.avatar}" class="testimonial-avatar mb-3" alt="${testimonial.name}">
                    <div class="mb-3">
                        ${Array(testimonial.rating).fill('<i class="fas fa-star text-warning"></i>').join('')}
                    </div>
                    <p class="card-text mb-3">"${testimonial.content}"</p>
                    <div class="mt-auto">
                        <h6 class="card-title mb-1">${testimonial.name}</h6>
                        <small class="text-muted">${testimonial.position}, ${testimonial.company}</small>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

/**
 * Update user-specific elements
 */
function updateUserElements(user) {
    // Update welcome message or user-specific content
    const welcomeElement = document.getElementById('user-welcome');
    if (welcomeElement) {
        welcomeElement.innerHTML = `Chào mừng trở lại, <strong>${user.name}</strong>!`;
    }
    
    // Update any other user-specific elements
    console.log('User logged in:', user);
}
