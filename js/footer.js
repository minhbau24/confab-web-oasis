// Hàm render footer cho toàn bộ trang web
function renderFooter() {
    const footer = `
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Trung tâm Hội nghị</h5>
                    <p class="text-muted">Nền tảng quản lý và tham gia hội nghị hàng đầu Việt Nam</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Liên kết</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.html" class="text-decoration-none text-muted">Trang chủ</a></li>
                        <li><a href="conferences.html" class="text-decoration-none text-muted">Danh sách Hội nghị</a></li>
                        <li><a href="profile.html" class="text-decoration-none text-muted">Hồ sơ cá nhân</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Liên hệ</h5>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-envelope me-2"></i>contact@trungtamhoinghi.vn</li>
                        <li><i class="fas fa-phone me-2"></i>(+84) 123-456-789</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>Hà Nội, Việt Nam</li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="mb-0">&copy; ${new Date().getFullYear()} Trung tâm Hội nghị. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>`;

    document.getElementById('footer-container').innerHTML = footer;
}

// Khi trang được tải sẽ render footer
document.addEventListener('DOMContentLoaded', function() {
    renderFooter();
});
