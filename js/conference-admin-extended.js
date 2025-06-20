// Conference Admin Extended Functions - For new management tabs
// This file extends the conference-admin.js with additional functionality

// Enhance the showToast function from the main file
function showToast(message, type = 'info') {
    // If the main script already has a showToast function, use it
    if (window.showToastOriginal && typeof window.showToastOriginal === 'function') {
        window.showToastOriginal(message, type);
        return;
    }
    
    // Simple toast implementation using Bootstrap
    const toastContainer = document.createElement('div');
    toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '5';
    
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type}`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    
    const toastContent = document.createElement('div');
    toastContent.className = 'd-flex';
    
    const toastBody = document.createElement('div');
    toastBody.className = 'toast-body';
    toastBody.textContent = message;
    
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close btn-close-white me-2 m-auto';
    closeButton.setAttribute('data-bs-dismiss', 'toast');
    closeButton.setAttribute('aria-label', 'Close');
    
    toastContent.appendChild(toastBody);
    toastContent.appendChild(closeButton);
    toastEl.appendChild(toastContent);
    toastContainer.appendChild(toastEl);
    
    document.body.appendChild(toastContainer);
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    // Remove from DOM after hidden
    toastEl.addEventListener('hidden.bs.toast', function() {
        document.body.removeChild(toastContainer);
    });
}

// Mock data for objectives
let conferenceObjectives = [
    { id: 1, text: "Học hỏi công nghệ mới", visible: true },
    { id: 2, text: "Kết nối với chuyên gia trong ngành", visible: true },
    { id: 3, text: "Tìm hiểu xu hướng và cập nhật mới nhất", visible: true },
    { id: 4, text: "Mở rộng mạng lưới quan hệ trong ngành", visible: true }
];

// Mock data for target audience
let targetAudiences = [
    { id: 1, icon: "fas fa-laptop-code", title: "Nhà phát triển", description: "Các lập trình viên và kỹ sư phần mềm", visible: true },
    { id: 2, icon: "fas fa-chart-line", title: "Nhà quản lý sản phẩm", description: "Người quản lý và phát triển sản phẩm công nghệ", visible: true },
    { id: 3, icon: "fas fa-user-tie", title: "Giám đốc công nghệ", description: "CTO và những người đưa ra quyết định về công nghệ", visible: true }
];

// Mock data for FAQs
let conferenceFAQs = [
    { id: 1, question: "Làm thế nào để đăng ký tham dự hội nghị?", answer: "Bạn có thể đăng ký trực tuyến thông qua trang web chính thức của hội nghị bằng cách điền vào mẫu đăng ký.", visible: true },
    { id: 2, question: "Hội nghị có cung cấp chứng chỉ tham dự không?", answer: "Có, tất cả người tham dự sẽ nhận được chứng chỉ tham dự sau khi hội nghị kết thúc.", visible: true },
    { id: 3, question: "Làm thế nào để trở thành diễn giả tại hội nghị?", answer: "Bạn cần gửi đề xuất bài thuyết trình đến ban tổ chức trước thời hạn được công bố trên trang web.", visible: true }
];

// Mock data for testimonials
let conferenceTestimonials = [
    { id: 1, name: "Nguyễn Văn A", position: "CTO tại Tech Solutions", comment: "Đây là hội nghị công nghệ tốt nhất mà tôi từng tham dự. Nội dung phong phú và rất nhiều cơ hội kết nối.", rating: 5, avatar: "https://randomuser.me/api/portraits/men/1.jpg", visible: true },
    { id: 2, name: "Trần Thị B", position: "Product Manager tại InnoTech", comment: "Tôi đã học được rất nhiều từ các diễn giả chuyên nghiệp. Sẽ quay lại vào năm sau!", rating: 4, avatar: "https://randomuser.me/api/portraits/women/2.jpg", visible: true }
];

// Support information
let supportInfo = {
    email: "support@techcrunch2024.com",
    phone: "(+84) 123-456-789",
    address: "Trung tâm Hội nghị Quốc gia, Thăng Long, Hà Nội, Việt Nam",
    facebook: "https://facebook.com/techcrunch2024vn",
    twitter: "https://twitter.com/techcrunch2024vn",
    linkedin: "https://linkedin.com/company/techcrunch2024vn"
};

// Hero image configuration
let heroConfig = {
    imageUrl: "https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3",
    overlay: true,
    overlayOpacity: 0.5,
    textPosition: "center"
};

// Initialize extended functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the conference admin page
    if (document.getElementById('objectives')) {
        initializeObjectivesTab();
        initializeTargetAudienceTab();
        initializeFAQTab();
        initializeTestimonialsTab();
        initializeSupportInfoTab();
        initializeHeroImageTab();
    }
});

// ===================== OBJECTIVES MANAGEMENT =====================

function initializeObjectivesTab() {
    renderObjectivesTable();
    
    // Event listeners for objectives
    document.getElementById('add-objective-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        addObjective();
    });
    
    // Delegate event for dynamically created elements
    document.getElementById('objectives-table')?.addEventListener('click', function(e) {
        if (e.target.closest('.btn-outline-danger')) {
            const row = e.target.closest('tr');
            const objectiveId = parseInt(row.dataset.id);
            removeObjective(objectiveId);
        } else if (e.target.closest('.btn-outline-primary')) {
            const row = e.target.closest('tr');
            const objectiveId = parseInt(row.dataset.id);
            prepareEditObjective(objectiveId);
        } else if (e.target.closest('.form-check-input')) {
            const row = e.target.closest('tr');
            const objectiveId = parseInt(row.dataset.id);
            toggleObjectiveVisibility(objectiveId);
        }
    });
    
    document.getElementById('edit-objective-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveEditedObjective();
    });
}

function renderObjectivesTable() {
    const tableBody = document.getElementById('objectives-table');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    conferenceObjectives.forEach(objective => {
        const row = document.createElement('tr');
        row.dataset.id = objective.id;
        
        row.innerHTML = `
            <td>${objective.text}</td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" ${objective.visible ? 'checked' : ''}>
                </div>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editObjectiveModal"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
}

function addObjective() {
    const objectiveText = document.getElementById('new-objective-text').value.trim();
    
    if (!objectiveText) {
        showToast('Vui lòng nhập nội dung mục tiêu!', 'warning');
        return;
    }
    
    const newId = conferenceObjectives.length > 0 ? Math.max(...conferenceObjectives.map(o => o.id)) + 1 : 1;
    
    conferenceObjectives.push({
        id: newId,
        text: objectiveText,
        visible: true
    });
    
    renderObjectivesTable();
    
    // Close modal and reset form
    document.getElementById('new-objective-text').value = '';
    bootstrap.Modal.getInstance(document.getElementById('addObjectiveModal')).hide();
    
    showToast('Đã thêm mục tiêu mới!', 'success');
}

function prepareEditObjective(objectiveId) {
    const objective = conferenceObjectives.find(o => o.id === objectiveId);
    if (objective) {
        document.getElementById('edit-objective-id').value = objective.id;
        document.getElementById('edit-objective-text').value = objective.text;
    }
}

function saveEditedObjective() {
    const objectiveId = parseInt(document.getElementById('edit-objective-id').value);
    const objectiveText = document.getElementById('edit-objective-text').value.trim();
    
    if (!objectiveText) {
        showToast('Vui lòng nhập nội dung mục tiêu!', 'warning');
        return;
    }
    
    const objectiveIndex = conferenceObjectives.findIndex(o => o.id === objectiveId);
    if (objectiveIndex !== -1) {
        conferenceObjectives[objectiveIndex].text = objectiveText;
        
        renderObjectivesTable();
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('editObjectiveModal')).hide();
        
        showToast('Đã cập nhật mục tiêu!', 'success');
    }
}

function removeObjective(objectiveId) {
    if (confirm('Bạn có chắc chắn muốn xóa mục tiêu này?')) {
        conferenceObjectives = conferenceObjectives.filter(o => o.id !== objectiveId);
        renderObjectivesTable();
        showToast('Đã xóa mục tiêu!', 'success');
    }
}

function toggleObjectiveVisibility(objectiveId) {
    const objective = conferenceObjectives.find(o => o.id === objectiveId);
    if (objective) {
        objective.visible = !objective.visible;
        showToast(`Đã ${objective.visible ? 'hiển thị' : 'ẩn'} mục tiêu!`, 'info');
    }
}

// ===================== TARGET AUDIENCE MANAGEMENT =====================

function initializeTargetAudienceTab() {
    renderTargetAudienceCards();
    
    document.getElementById('add-audience-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        addTargetAudience();
    });
    
    // Event delegation for dynamic elements
    document.getElementById('target-audience-container')?.addEventListener('click', function(e) {
        const card = e.target.closest('.audience-card');
        if (!card) return;
        
        const audienceId = parseInt(card.dataset.id);
        
        if (e.target.closest('.edit-audience')) {
            prepareEditAudience(audienceId);
        } else if (e.target.closest('.delete-audience')) {
            removeTargetAudience(audienceId);
        } else if (e.target.closest('.toggle-audience-visibility')) {
            toggleAudienceVisibility(audienceId);
        }
    });
    
    document.getElementById('edit-audience-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveEditedAudience();
    });
}

function renderTargetAudienceCards() {
    const container = document.getElementById('target-audience-container');
    if (!container) return;
    
    container.innerHTML = '';
    
    targetAudiences.forEach(audience => {
        const col = document.createElement('div');
        col.className = 'col-md-4 mb-4';
        col.innerHTML = `
            <div class="audience-card management-card p-4 text-center" data-id="${audience.id}">
                <div class="visibility-toggle position-absolute top-0 end-0 p-2">
                    <button class="btn btn-sm toggle-audience-visibility ${audience.visible ? 'btn-success' : 'btn-secondary'}">
                        <i class="fas ${audience.visible ? 'fa-eye' : 'fa-eye-slash'}"></i>
                    </button>
                </div>
                
                <div class="audience-icon mb-3">
                    <i class="${audience.icon} fa-3x"></i>
                </div>
                
                <h5>${audience.title}</h5>
                <p class="text-muted">${audience.description}</p>
                
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary me-1 edit-audience" data-bs-toggle="modal" data-bs-target="#editAudienceModal">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-audience">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(col);
    });
}

function addTargetAudience() {
    const title = document.getElementById('new-audience-title').value.trim();
    const description = document.getElementById('new-audience-description').value.trim();
    const icon = document.getElementById('new-audience-icon').value.trim();
    
    if (!title || !description || !icon) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'warning');
        return;
    }
    
    const newId = targetAudiences.length > 0 ? Math.max(...targetAudiences.map(a => a.id)) + 1 : 1;
    
    targetAudiences.push({
        id: newId,
        title: title,
        description: description,
        icon: icon,
        visible: true
    });
    
    renderTargetAudienceCards();
    
    // Reset form and close modal
    document.getElementById('new-audience-title').value = '';
    document.getElementById('new-audience-description').value = '';
    document.getElementById('new-audience-icon').value = 'fas fa-users';
    
    bootstrap.Modal.getInstance(document.getElementById('addAudienceModal')).hide();
    
    showToast('Đã thêm đối tượng tham dự mới!', 'success');
}

function prepareEditAudience(audienceId) {
    const audience = targetAudiences.find(a => a.id === audienceId);
    if (audience) {
        document.getElementById('edit-audience-id').value = audience.id;
        document.getElementById('edit-audience-title').value = audience.title;
        document.getElementById('edit-audience-description').value = audience.description;
        document.getElementById('edit-audience-icon').value = audience.icon;
    }
}

function saveEditedAudience() {
    const audienceId = parseInt(document.getElementById('edit-audience-id').value);
    const title = document.getElementById('edit-audience-title').value.trim();
    const description = document.getElementById('edit-audience-description').value.trim();
    const icon = document.getElementById('edit-audience-icon').value.trim();
    
    if (!title || !description || !icon) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'warning');
        return;
    }
    
    const audienceIndex = targetAudiences.findIndex(a => a.id === audienceId);
    if (audienceIndex !== -1) {
        targetAudiences[audienceIndex].title = title;
        targetAudiences[audienceIndex].description = description;
        targetAudiences[audienceIndex].icon = icon;
        
        renderTargetAudienceCards();
        
        bootstrap.Modal.getInstance(document.getElementById('editAudienceModal')).hide();
        
        showToast('Đã cập nhật đối tượng tham dự!', 'success');
    }
}

function removeTargetAudience(audienceId) {
    if (confirm('Bạn có chắc chắn muốn xóa đối tượng tham dự này?')) {
        targetAudiences = targetAudiences.filter(a => a.id !== audienceId);
        renderTargetAudienceCards();
        showToast('Đã xóa đối tượng tham dự!', 'success');
    }
}

function toggleAudienceVisibility(audienceId) {
    const audience = targetAudiences.find(a => a.id === audienceId);
    if (audience) {
        audience.visible = !audience.visible;
        renderTargetAudienceCards();
        showToast(`Đã ${audience.visible ? 'hiển thị' : 'ẩn'} đối tượng tham dự!`, 'info');
    }
}

// ===================== FAQ MANAGEMENT =====================

function initializeFAQTab() {
    renderFAQAccordion();
    
    // Add FAQ form submission
    document.getElementById('add-faq-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        addFAQ();
    });
    
    // Event delegation for dynamic elements
    document.getElementById('faq-accordion')?.addEventListener('click', function(e) {
        if (e.target.closest('.edit-faq')) {
            const item = e.target.closest('.accordion-item');
            const faqId = parseInt(item.dataset.id);
            prepareEditFAQ(faqId);
            e.stopPropagation();
        } else if (e.target.closest('.delete-faq')) {
            const item = e.target.closest('.accordion-item');
            const faqId = parseInt(item.dataset.id);
            removeFAQ(faqId);
            e.stopPropagation();
        } else if (e.target.closest('.toggle-faq-visibility')) {
            const item = e.target.closest('.accordion-item');
            const faqId = parseInt(item.dataset.id);
            toggleFAQVisibility(faqId);
            e.stopPropagation();
        }
    });
    
    document.getElementById('edit-faq-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveEditedFAQ();
    });
}

function renderFAQAccordion() {
    const accordion = document.getElementById('faq-accordion');
    if (!accordion) return;
    
    accordion.innerHTML = '';
    
    conferenceFAQs.forEach((faq, index) => {
        const item = document.createElement('div');
        item.className = 'accordion-item';
        item.dataset.id = faq.id;
        
        item.innerHTML = `
            <h2 class="accordion-header">
                <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" 
                  data-bs-toggle="collapse" data-bs-target="#faq-collapse-${faq.id}">
                    ${faq.question}
                    <div class="ms-auto">
                        <span class="badge ${faq.visible ? 'bg-success' : 'bg-secondary'} me-2">
                            ${faq.visible ? 'Hiển thị' : 'Ẩn'}
                        </span>
                    </div>
                </button>
            </h2>
            <div id="faq-collapse-${faq.id}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}">
                <div class="accordion-body">
                    <p>${faq.answer}</p>
                    <div class="mt-2 d-flex justify-content-end">
                        <button class="btn btn-sm btn-outline-secondary me-2 toggle-faq-visibility">
                            <i class="fas ${faq.visible ? 'fa-eye-slash' : 'fa-eye'}"></i> 
                            ${faq.visible ? 'Ẩn' : 'Hiển thị'}
                        </button>
                        <button class="btn btn-sm btn-outline-primary me-2 edit-faq" data-bs-toggle="modal" data-bs-target="#editFAQModal">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-faq">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        accordion.appendChild(item);
    });
}

function addFAQ() {
    const question = document.getElementById('new-faq-question').value.trim();
    const answer = document.getElementById('new-faq-answer').value.trim();
    
    if (!question || !answer) {
        showToast('Vui lòng điền đầy đủ câu hỏi và câu trả lời!', 'warning');
        return;
    }
    
    const newId = conferenceFAQs.length > 0 ? Math.max(...conferenceFAQs.map(f => f.id)) + 1 : 1;
    
    conferenceFAQs.push({
        id: newId,
        question: question,
        answer: answer,
        visible: true
    });
    
    renderFAQAccordion();
    
    // Reset form and close modal
    document.getElementById('new-faq-question').value = '';
    document.getElementById('new-faq-answer').value = '';
    
    bootstrap.Modal.getInstance(document.getElementById('addFAQModal')).hide();
    
    showToast('Đã thêm câu hỏi mới!', 'success');
}

function prepareEditFAQ(faqId) {
    const faq = conferenceFAQs.find(f => f.id === faqId);
    if (faq) {
        document.getElementById('edit-faq-id').value = faq.id;
        document.getElementById('edit-faq-question').value = faq.question;
        document.getElementById('edit-faq-answer').value = faq.answer;
    }
}

function saveEditedFAQ() {
    const faqId = parseInt(document.getElementById('edit-faq-id').value);
    const question = document.getElementById('edit-faq-question').value.trim();
    const answer = document.getElementById('edit-faq-answer').value.trim();
    
    if (!question || !answer) {
        showToast('Vui lòng điền đầy đủ câu hỏi và câu trả lời!', 'warning');
        return;
    }
    
    const faqIndex = conferenceFAQs.findIndex(f => f.id === faqId);
    if (faqIndex !== -1) {
        conferenceFAQs[faqIndex].question = question;
        conferenceFAQs[faqIndex].answer = answer;
        
        renderFAQAccordion();
        
        bootstrap.Modal.getInstance(document.getElementById('editFAQModal')).hide();
        
        showToast('Đã cập nhật câu hỏi!', 'success');
    }
}

function removeFAQ(faqId) {
    if (confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) {
        conferenceFAQs = conferenceFAQs.filter(f => f.id !== faqId);
        renderFAQAccordion();
        showToast('Đã xóa câu hỏi!', 'success');
    }
}

function toggleFAQVisibility(faqId) {
    const faq = conferenceFAQs.find(f => f.id === faqId);
    if (faq) {
        faq.visible = !faq.visible;
        renderFAQAccordion();
        showToast(`Đã ${faq.visible ? 'hiển thị' : 'ẩn'} câu hỏi!`, 'info');
    }
}

// ===================== TESTIMONIALS MANAGEMENT =====================

function initializeTestimonialsTab() {
    renderTestimonialCards();
    
    document.getElementById('add-testimonial-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        addTestimonial();
    });
    
    // Event delegation for dynamic elements
    document.getElementById('testimonials-container')?.addEventListener('click', function(e) {
        const card = e.target.closest('.testimonial-card');
        if (!card) return;
        
        const testimonialId = parseInt(card.dataset.id);
        
        if (e.target.closest('.edit-testimonial')) {
            prepareEditTestimonial(testimonialId);
        } else if (e.target.closest('.delete-testimonial')) {
            removeTestimonial(testimonialId);
        } else if (e.target.closest('.toggle-testimonial-visibility')) {
            toggleTestimonialVisibility(testimonialId);
        }
    });
    
    document.getElementById('edit-testimonial-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveEditedTestimonial();
    });
}

function renderTestimonialCards() {
    const container = document.getElementById('testimonials-container');
    if (!container) return;
    
    container.innerHTML = '';
    
    conferenceTestimonials.forEach(testimonial => {
        const col = document.createElement('div');
        col.className = 'col-md-6 mb-4';
        
        // Create star rating HTML
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<i class="fas fa-star ${i <= testimonial.rating ? 'text-warning' : 'text-muted'}"></i> `;
        }
        
        col.innerHTML = `
            <div class="testimonial-card management-card p-4" data-id="${testimonial.id}">
                <div class="visibility-toggle position-absolute top-0 end-0 p-2">
                    <button class="btn btn-sm toggle-testimonial-visibility ${testimonial.visible ? 'btn-success' : 'btn-secondary'}">
                        <i class="fas ${testimonial.visible ? 'fa-eye' : 'fa-eye-slash'}"></i>
                    </button>
                </div>
                
                <div class="d-flex mb-3">
                    <div class="testimonial-avatar me-3">
                        <img src="${testimonial.avatar}" alt="${testimonial.name}" class="rounded-circle" width="60" height="60">
                    </div>
                    <div>
                        <h5 class="mb-0">${testimonial.name}</h5>
                        <p class="text-muted mb-1">${testimonial.position}</p>
                        <div class="rating">
                            ${starsHtml}
                        </div>
                    </div>
                </div>
                
                <p class="testimonial-text">"${testimonial.comment}"</p>
                
                <div class="mt-3 text-end">
                    <button class="btn btn-sm btn-outline-primary me-1 edit-testimonial" data-bs-toggle="modal" data-bs-target="#editTestimonialModal">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-testimonial">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(col);
    });
}

function addTestimonial() {
    const name = document.getElementById('new-testimonial-name').value.trim();
    const position = document.getElementById('new-testimonial-position').value.trim();
    const comment = document.getElementById('new-testimonial-comment').value.trim();
    const rating = parseInt(document.getElementById('new-testimonial-rating').value);
    const avatar = document.getElementById('new-testimonial-avatar').value.trim() || "https://randomuser.me/api/portraits/men/1.jpg";
    
    if (!name || !position || !comment || isNaN(rating)) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'warning');
        return;
    }
    
    const newId = conferenceTestimonials.length > 0 ? Math.max(...conferenceTestimonials.map(t => t.id)) + 1 : 1;
    
    conferenceTestimonials.push({
        id: newId,
        name: name,
        position: position,
        comment: comment,
        rating: rating,
        avatar: avatar,
        visible: true
    });
    
    renderTestimonialCards();
    
    // Reset form and close modal
    document.getElementById('new-testimonial-name').value = '';
    document.getElementById('new-testimonial-position').value = '';
    document.getElementById('new-testimonial-comment').value = '';
    document.getElementById('new-testimonial-rating').value = '5';
    document.getElementById('new-testimonial-avatar').value = '';
    
    bootstrap.Modal.getInstance(document.getElementById('addTestimonialModal')).hide();
    
    showToast('Đã thêm đánh giá mới!', 'success');
}

function prepareEditTestimonial(testimonialId) {
    const testimonial = conferenceTestimonials.find(t => t.id === testimonialId);
    if (testimonial) {
        document.getElementById('edit-testimonial-id').value = testimonial.id;
        document.getElementById('edit-testimonial-name').value = testimonial.name;
        document.getElementById('edit-testimonial-position').value = testimonial.position;
        document.getElementById('edit-testimonial-comment').value = testimonial.comment;
        document.getElementById('edit-testimonial-rating').value = testimonial.rating;
        document.getElementById('edit-testimonial-avatar').value = testimonial.avatar;
    }
}

function saveEditedTestimonial() {
    const testimonialId = parseInt(document.getElementById('edit-testimonial-id').value);
    const name = document.getElementById('edit-testimonial-name').value.trim();
    const position = document.getElementById('edit-testimonial-position').value.trim();
    const comment = document.getElementById('edit-testimonial-comment').value.trim();
    const rating = parseInt(document.getElementById('edit-testimonial-rating').value);
    const avatar = document.getElementById('edit-testimonial-avatar').value.trim();
    
    if (!name || !position || !comment || isNaN(rating)) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'warning');
        return;
    }
    
    const testimonialIndex = conferenceTestimonials.findIndex(t => t.id === testimonialId);
    if (testimonialIndex !== -1) {
        conferenceTestimonials[testimonialIndex].name = name;
        conferenceTestimonials[testimonialIndex].position = position;
        conferenceTestimonials[testimonialIndex].comment = comment;
        conferenceTestimonials[testimonialIndex].rating = rating;
        conferenceTestimonials[testimonialIndex].avatar = avatar;
        
        renderTestimonialCards();
        
        bootstrap.Modal.getInstance(document.getElementById('editTestimonialModal')).hide();
        
        showToast('Đã cập nhật đánh giá!', 'success');
    }
}

function removeTestimonial(testimonialId) {
    if (confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
        conferenceTestimonials = conferenceTestimonials.filter(t => t.id !== testimonialId);
        renderTestimonialCards();
        showToast('Đã xóa đánh giá!', 'success');
    }
}

function toggleTestimonialVisibility(testimonialId) {
    const testimonial = conferenceTestimonials.find(t => t.id === testimonialId);
    if (testimonial) {
        testimonial.visible = !testimonial.visible;
        renderTestimonialCards();
        showToast(`Đã ${testimonial.visible ? 'hiển thị' : 'ẩn'} đánh giá!`, 'info');
    }
}

// ===================== SUPPORT INFO MANAGEMENT =====================

function initializeSupportInfoTab() {
    // Load support info
    document.getElementById('support-email')?.setAttribute('value', supportInfo.email);
    document.getElementById('support-phone')?.setAttribute('value', supportInfo.phone);
    document.getElementById('support-address')?.value = supportInfo.address;
    document.getElementById('facebook-url')?.setAttribute('value', supportInfo.facebook);
    document.getElementById('twitter-url')?.setAttribute('value', supportInfo.twitter);
    document.getElementById('linkedin-url')?.setAttribute('value', supportInfo.linkedin);
    
    // Add event listeners
    document.getElementById('contact-info-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveContactInfo();
    });
    
    document.getElementById('social-links-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveSocialLinks();
    });
}

function saveContactInfo() {
    supportInfo.email = document.getElementById('support-email').value.trim();
    supportInfo.phone = document.getElementById('support-phone').value.trim();
    supportInfo.address = document.getElementById('support-address').value.trim();
    
    showToast('Đã cập nhật thông tin liên hệ!', 'success');
}

function saveSocialLinks() {
    supportInfo.facebook = document.getElementById('facebook-url').value.trim();
    supportInfo.twitter = document.getElementById('twitter-url').value.trim();
    supportInfo.linkedin = document.getElementById('linkedin-url').value.trim();
    
    showToast('Đã cập nhật liên kết mạng xã hội!', 'success');
}

// ===================== HERO IMAGE MANAGEMENT =====================

function initializeHeroImageTab() {
    // Load hero image settings
    document.getElementById('hero-image-url')?.setAttribute('value', heroConfig.imageUrl);
    document.getElementById('hero-overlay')?.checked = heroConfig.overlay;
    document.getElementById('hero-overlay-opacity')?.setAttribute('value', heroConfig.overlayOpacity);
    document.getElementById('hero-text-position')?.value = heroConfig.textPosition;
    
    // Update hero preview
    updateHeroPreview();
    
    // Add event listeners
    document.getElementById('hero-image-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveHeroConfig();
    });
    
    document.getElementById('hero-image-url')?.addEventListener('input', updateHeroPreview);
    document.getElementById('hero-overlay')?.addEventListener('change', updateHeroPreview);
    document.getElementById('hero-overlay-opacity')?.addEventListener('input', updateHeroPreview);
    document.getElementById('hero-text-position')?.addEventListener('change', updateHeroPreview);
}

function updateHeroPreview() {
    const previewContainer = document.getElementById('hero-preview');
    if (!previewContainer) return;
    
    const imageUrl = document.getElementById('hero-image-url').value.trim();
    const hasOverlay = document.getElementById('hero-overlay').checked;
    const overlayOpacity = document.getElementById('hero-overlay-opacity').value;
    const textPosition = document.getElementById('hero-text-position').value;
    
    previewContainer.style.backgroundImage = `url('${imageUrl || heroConfig.imageUrl}')`;
    
    // Update overlay
    let overlayElement = previewContainer.querySelector('.hero-overlay');
    if (!overlayElement) {
        overlayElement = document.createElement('div');
        overlayElement.className = 'hero-overlay';
        previewContainer.appendChild(overlayElement);
    }
    
    overlayElement.style.display = hasOverlay ? 'block' : 'none';
    overlayElement.style.opacity = overlayOpacity;
    
    // Update text position
    const textContainer = previewContainer.querySelector('.hero-text');
    if (textContainer) {
        textContainer.className = `hero-text text-${textPosition}`;
    }
}

function saveHeroConfig() {
    heroConfig.imageUrl = document.getElementById('hero-image-url').value.trim();
    heroConfig.overlay = document.getElementById('hero-overlay').checked;
    heroConfig.overlayOpacity = document.getElementById('hero-overlay-opacity').value;
    heroConfig.textPosition = document.getElementById('hero-text-position').value;
    
    updateHeroPreview();
    showToast('Đã lưu cấu hình ảnh nền hero!', 'success');
}
