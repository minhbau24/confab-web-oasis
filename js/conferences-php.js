// Các hàm JS cho trang conferences.php

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo: Tải dữ liệu hội nghị và hiển thị trên trang
    loadConferences();
    
    // Đăng ký sự kiện search và filter
    document.getElementById('searchInput').addEventListener('input', filterConferences);
    document.getElementById('categoryFilter').addEventListener('change', filterConferences);
    document.getElementById('dateFilter').addEventListener('change', filterConferences);
});

/**
 * Tải dữ liệu hội nghị từ API
 */
async function loadConferences() {
    try {
        // Nếu API đã sẵn sàng, sử dụng API để tải dữ liệu
        if (typeof fetch === 'function') {
            const response = await fetch('api/conferences.php');
            if (response.ok) {
                const data = await response.json();
                displayConferences(data);
                return;
            }
        }
        
        // Dự phòng: nếu API chưa hoạt động, sử dụng dữ liệu mẫu từ data.js
        console.warn("Sử dụng dữ liệu mẫu từ data.js. Kết nối API thất bại.");
        displayConferences(conferences);
    } catch (error) {
        console.error("Lỗi khi tải dữ liệu hội nghị:", error);
        // Hiển thị thông báo lỗi
        document.getElementById('conferencesList').innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Đã xảy ra lỗi!</h4>
                    <p>Không thể tải dữ liệu hội nghị. Vui lòng thử lại sau.</p>
                </div>
            </div>
        `;
        
        // Dự phòng: nếu có lỗi, sử dụng dữ liệu mẫu từ data.js
        if (typeof conferences !== 'undefined') {
            displayConferences(conferences);
        }
    }
}

/**
 * Hiển thị danh sách hội nghị
 * @param {Array} data - Dữ liệu hội nghị
 */
function displayConferences(data) {
    const conferencesList = document.getElementById('conferencesList');
    conferencesList.innerHTML = '';
    
    if (!data || data.length === 0) {
        conferencesList.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">Không tìm thấy hội nghị!</h4>
                    <p>Hiện tại không có hội nghị nào phù hợp với bộ lọc của bạn.</p>
                    <hr>
                    <p class="mb-0">Hãy thay đổi các bộ lọc hoặc quay lại sau.</p>
                </div>
            </div>
        `;
        return;
    }
    
    data.forEach(conference => {
        // Tạo thẻ cho từng hội nghị
        const conferenceCard = document.createElement('div');
        conferenceCard.className = 'col-md-4 mb-4';
        conferenceCard.innerHTML = `
            <div class="card conference-card h-100 shadow-sm">
                <img src="${conference.image || 'https://via.placeholder.com/800x400?text=Hình+ảnh+hội+nghị'}" class="card-img-top" alt="${conference.title}">
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
                    <a href="conference-detail.php?id=${conference.id}" class="btn btn-primary mt-3 stretched-link">Chi tiết</a>
                </div>
            </div>
        `;
        conferencesList.appendChild(conferenceCard);
    });
    
    // Cập nhật phân trang
    updatePagination(data.length);
}

/**
 * Lọc hội nghị theo bộ lọc
 */
function filterConferences() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    // Gửi request đến API với các tham số lọc
    // Trong trường hợp API chưa hoạt động, lọc dữ liệu từ data.js
    if (typeof conferences !== 'undefined') {
        const filtered = conferences.filter(conference => {
            // Lọc theo tìm kiếm
            const matchesSearch = !searchTerm || 
                conference.title.toLowerCase().includes(searchTerm) || 
                conference.description.toLowerCase().includes(searchTerm) || 
                conference.location.toLowerCase().includes(searchTerm);
            
            // Lọc theo danh mục
            const matchesCategory = !categoryFilter || 
                conference.category.toLowerCase().includes(categoryFilter);
            
            // Lọc theo thời gian
            let matchesDate = true;
            const today = new Date();
            const confDate = new Date(conference.date);
            
            if (dateFilter === 'upcoming') {
                matchesDate = confDate >= today;
            } else if (dateFilter === 'thisMonth') {
                matchesDate = confDate.getMonth() === today.getMonth() && 
                             confDate.getFullYear() === today.getFullYear();
            } else if (dateFilter === 'thisYear') {
                matchesDate = confDate.getFullYear() === today.getFullYear();
            } else if (dateFilter === 'past') {
                matchesDate = confDate < today;
            }
            
            return matchesSearch && matchesCategory && matchesDate;
        });
        
        displayConferences(filtered);
    } else {
        // TODO: Gửi API request với các tham số lọc
        loadConferences();
    }
}

/**
 * Tạo phân trang
 * @param {number} totalItems - Tổng số hội nghị
 */
function updatePagination(totalItems) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    
    // Tạm thời hiển thị phân trang cố định
    const itemsPerPage = 9;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    if (totalPages <= 1) return; // Không hiển thị phân trang nếu chỉ có 1 trang
    
    // Thêm nút Previous
    pagination.innerHTML += `
        <li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Trước</a>
        </li>
    `;
    
    // Thêm các số trang
    for (let i = 1; i <= Math.min(5, totalPages); i++) {
        pagination.innerHTML += `
            <li class="page-item ${i === 1 ? 'active' : ''}">
                <a class="page-link" href="#">${i}</a>
            </li>
        `;
    }
    
    // Thêm nút Next
    pagination.innerHTML += `
        <li class="page-item">
            <a class="page-link" href="#">Tiếp</a>
        </li>
    `;
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
