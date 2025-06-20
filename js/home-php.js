// Home page JavaScript logic for PHP version

document.addEventListener('DOMContentLoaded', function() {
    loadFeaturedConferences();
});

/**
 * Tải dữ liệu hội nghị nổi bật (sắp tới)
 */
async function loadFeaturedConferences() {
    const container = document.getElementById('featuredConferences');
    if (!container) return;
    
    try {
        // Gọi API để lấy hội nghị sắp tới
        const response = await fetch('api/conferences.php?upcoming=1&limit=3');
        if (response.ok) {
            const data = await response.json();
            
            // Nếu có dữ liệu
            if (data.status && data.data && data.data.length > 0) {
                // Hiển thị hội nghị
                displayConferences(data.data, container);
                return;
            }
        }
        
        // Nếu chưa có API hoặc lỗi, sử dụng dữ liệu mẫu
        displayFeaturedConferencesFallback(container);
    } catch (error) {
        console.error("Lỗi khi tải dữ liệu hội nghị nổi bật:", error);
        displayFeaturedConferencesFallback(container);
    }
}

/**
 * Hiển thị dữ liệu hội nghị nổi bật
 * @param {Array} conferences Danh sách hội nghị
 * @param {HTMLElement} container Container hiển thị
 */
function displayConferences(conferences, container) {
    if (!conferences || conferences.length === 0) {
        displayNoConferencesMessage(container);
        return;
    }
    
    let html = '';
    conferences.forEach(conference => {
        html += `
            <div class="col-md-4 mb-4">
                <div class="card conference-card h-100">
                    <img src="${conference.image || 'https://via.placeholder.com/800x400?text=Hội+nghị'}" 
                         class="card-img-top" alt="${conference.title}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-primary">${conference.category || 'Chưa phân loại'}</span>
                            <small class="text-muted">${formatDate(conference.date)}</small>
                        </div>
                        <h5 class="card-title">${conference.title}</h5>
                        <p class="card-text flex-grow-1">${truncateText(conference.description, 100)}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span><i class="fas fa-map-marker-alt me-2"></i>${conference.location}</span>
                            <span><i class="fas fa-users me-2"></i>${conference.attendees}/${conference.capacity}</span>
                        </div>
                        <a href="conference-detail.php?id=${conference.id}" class="btn btn-primary mt-3">Chi tiết</a>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Hiển thị thông báo khi không có hội nghị
 * @param {HTMLElement} container Container hiển thị
 */
function displayNoConferencesMessage(container) {
    container.innerHTML = `
        <div class="col-12 text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Không có hội nghị sắp diễn ra</h4>
            <p class="text-muted">Vui lòng quay lại sau để xem các sự kiện mới</p>
        </div>
    `;
}

/**
 * Hiển thị dữ liệu mẫu khi không thể kết nối API
 * @param {HTMLElement} container Container hiển thị
 */
function displayFeaturedConferencesFallback(container) {
    // Kiểm tra xem dữ liệu mẫu có sẵn không
    if (typeof conferences !== 'undefined') {
        console.log("Sử dụng dữ liệu mẫu từ data.js");
        // Lọc các hội nghị sắp tới
        const today = new Date();
        const upcomingConferences = conferences
            .filter(conf => new Date(conf.date) >= today)
            .sort((a, b) => new Date(a.date) - new Date(b.date))
            .slice(0, 3);
        
        displayConferences(upcomingConferences, container);
    } else {
        displayNoConferencesMessage(container);
    }
}

/**
 * Cắt ngắn văn bản
 * @param {string} text - Văn bản cần cắt
 * @param {number} maxLength - Độ dài tối đa
 * @returns {string} Văn bản đã cắt
 */
function truncateText(text, maxLength) {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

/**
 * Định dạng ngày tháng
 * @param {string} dateString - Chuỗi ngày tháng
 * @returns {string} Ngày tháng đã định dạng
 */
function formatDate(dateString) {
    if (!dateString) return '';
    
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(dateString).toLocaleDateString('vi-VN', options);
}
