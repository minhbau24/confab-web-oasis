
// Rendering functions for creating HTML elements with Bootstrap styling - Tiếng Việt

function renderConferenceCard(conference, showFullDetails = false) {
    const dateFormatted = new Date(conference.date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const endDateFormatted = conference.endDate ? 
        new Date(conference.endDate).toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';

    const priceFormatted = conference.price.toLocaleString('vi-VN', {
        style: 'currency',
        currency: 'VND'
    });

    return `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card conference-card h-100 shadow-sm">
                <img src="${conference.image}" class="card-img-top" alt="${conference.title}" style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-primary mb-2 align-self-start">${conference.category}</span>
                    <h5 class="card-title">${conference.title}</h5>
                    <p class="card-text text-muted">${conference.description.substring(0, 100)}...</p>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>${dateFormatted}
                                ${conference.endDate ? ` - ${endDateFormatted}` : ''}
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>${conference.location}
                            </small>
                            <strong class="text-success">${priceFormatted}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>${conference.attendees}/${conference.capacity} người tham dự
                            </small>
                        </div>                        <div class="mt-3">
                            <a href="conference-detail.html?id=${conference.id}" class="btn btn-primary btn-sm me-2">
                                Xem chi tiết
                            </a>
                            <button class="btn btn-outline-success btn-sm" onclick="joinConference(${conference.id})">
                                <i class="fas fa-plus me-1"></i>Tham gia
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderConferenceDetail(conference) {
    const dateFormatted = new Date(conference.date).toLocaleDateString('vi-VN', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const endDateFormatted = conference.endDate ? 
        new Date(conference.endDate).toLocaleDateString('vi-VN', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';

    const priceFormatted = conference.price.toLocaleString('vi-VN', {
        style: 'currency',
        currency: 'VND'
    });

    const speakersHtml = conference.speakers.map(speaker => `
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" 
                         class="rounded-circle mb-2" width="80" height="80" alt="${speaker.name}">
                    <h6 class="card-title">${speaker.name}</h6>
                    <p class="text-muted small">${speaker.title}</p>
                    <p class="card-text small">${speaker.bio}</p>
                </div>
            </div>
        </div>
    `).join('');

    const scheduleHtml = conference.schedule.map(item => `
        <div class="d-flex align-items-center py-2 border-bottom">
            <div class="me-3">
                <span class="badge bg-primary">${item.time}</span>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">${item.title}</h6>
                ${item.speaker ? `<small class="text-muted">by ${item.speaker}</small>` : ''}
            </div>
        </div>
    `).join('');

    return `
        <div class="row">
            <div class="col-lg-8">
                <img src="${conference.image}" class="img-fluid rounded mb-4" alt="${conference.title}">
                
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary mb-2">${conference.category}</span>
                                <h1 class="display-6">${conference.title}</h1>
                            </div>
                            <div class="text-end">
                                <h3 class="text-success mb-0">${priceFormatted}</h3>
                                <small class="text-muted">per ticket</small>
                            </div>
                        </div>
                        
                        <p class="lead">${conference.description}</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6><i class="fas fa-calendar me-2 text-primary"></i>Date</h6>
                                <p>${dateFormatted}${conference.endDate ? ` - ${endDateFormatted}` : ''}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i>Location</h6>
                                <p>${conference.location}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-users me-2 text-primary"></i>Attendees</h6>
                                <p>${conference.attendees} / ${conference.capacity}</p>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: ${(conference.attendees / conference.capacity) * 100}%"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-envelope me-2 text-primary"></i>Contact</h6>
                                <p>${conference.organizer.name}<br>
                                   <a href="mailto:${conference.organizer.email}">${conference.organizer.email}</a><br>
                                   ${conference.organizer.phone}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Speakers Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-microphone me-2"></i>Speakers</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            ${speakersHtml}
                        </div>
                    </div>
                </div>

                <!-- Schedule Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Schedule</h5>
                    </div>
                    <div class="card-body">
                        ${scheduleHtml}
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 2rem;">
                    <div class="card-body text-center">
                        <h5 class="card-title">Register Now</h5>
                        <h3 class="text-success mb-3">${priceFormatted}</h3>
                        <p class="text-muted mb-3">
                            ${conference.capacity - conference.attendees} spots remaining
                        </p>
                        <button class="btn btn-success btn-lg w-100 mb-2" onclick="joinConference(${conference.id})">
                            <i class="fas fa-ticket-alt me-2"></i>Register
                        </button>
                        <button class="btn btn-outline-primary w-100" onclick="shareConference(${conference.id})">
                            <i class="fas fa-share me-2"></i>Share
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderProjectCard(project) {
    return `
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <img src="${project.image}" class="card-img-top" alt="${project.title}" style="height: 150px; object-fit: cover;">
                <div class="card-body">
                    <h6 class="card-title">${project.title}</h6>
                    <p class="card-text small">${project.description}</p>
                    <div class="mb-2">
                        ${project.technologies.map(tech => 
                            `<span class="badge bg-secondary me-1 mb-1">${tech}</span>`
                        ).join('')}
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="${project.github}" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="fab fa-github me-1"></i>Mã nguồn
                        </a>
                        <a href="${project.demo}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Demo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderAdminConferenceRow(conference) {
    const dateFormatted = new Date(conference.date).toLocaleDateString('vi-VN');
    const statusText = conference.status === 'active' ? 'đang diễn ra' : 'kết thúc';
    const statusBadge = conference.status === 'active' ? 'success' : 'secondary';
    
    return `
        <tr>
            <td>${conference.id}</td>
            <td>${conference.title}</td>
            <td>${dateFormatted}</td>
            <td>${conference.location}</td>
            <td><span class="badge bg-primary">${conference.category}</span></td>
            <td>${conference.attendees}/${conference.capacity}</td>
            <td><span class="badge bg-${statusBadge}">${statusText}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editConference(${conference.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteConference(${conference.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
}

// Utility function to show toast notifications - Tiếng Việt
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1070';
    document.body.appendChild(container);
    return container;
}
