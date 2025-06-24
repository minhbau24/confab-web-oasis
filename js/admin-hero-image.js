// ===================== HERO IMAGE MANAGEMENT =====================
// Quản lý Ảnh nền Hero cho từng conference

const HERO_API = 'api/conference_hero_image.php';
const HERO_TAB_ID = '#hero-image';
const HERO_FORM_ID = '#hero-image-form';

// Lấy conference_id hiện tại (giả sử lưu ở localStorage hoặc global JS)
function getCurrentConferenceId() {
    // Ưu tiên lấy từ localStorage, fallback: 1
    return localStorage.getItem('currentConferenceId') || 1;
}

// Render dữ liệu hero image lên giao diện
function renderHeroImage(data) {
    // Ảnh preview
    const img = document.querySelector(`${HERO_TAB_ID} img`);
    if (img) img.src = data.image || data.banner_image || '';
    // Tiêu đề
    const titleInput = document.querySelector(`${HERO_TAB_ID} input[type="text"]`);
    if (titleInput) titleInput.value = data.hero_title || '';
    // Mô tả
    const descTextarea = document.querySelector(`${HERO_TAB_ID} textarea`);
    if (descTextarea) descTextarea.value = data.hero_description || '';
    // URL ảnh
    const urlInput = document.getElementById('hero-image-url');
    if (urlInput) urlInput.value = data.image || '';
    // Overlay
    const overlayCheckbox = document.getElementById('hero-overlay');
    if (overlayCheckbox) overlayCheckbox.checked = !!data.hero_overlay;
    // Overlay opacity
    const opacityRange = document.getElementById('hero-overlay-opacity');
    if (opacityRange) opacityRange.value = data.hero_overlay_opacity || 50;
    // Text position
    const textPosSelect = document.getElementById('hero-text-position');
    if (textPosSelect) textPosSelect.value = data.hero_text_position || 'center';
    // Preview background
    const preview = document.getElementById('hero-preview');
    if (preview) preview.style.backgroundImage = `url('${data.image || ''}')`;
}

// Fetch hero image info
function fetchHeroImage() {
    const id = getCurrentConferenceId();
    fetch(`${HERO_API}?conference_id=${id}`)
        .then(res => res.json())
        .then(json => {
            if (json.success && json.data) {
                renderHeroImage(json.data);
            }
        });
}

// Update hero image info
function updateHeroImage(e) {
    e.preventDefault();
    const id = getCurrentConferenceId();
    const urlInput = document.getElementById('hero-image-url');
    const titleInput = document.querySelector(`${HERO_TAB_ID} input[type="text"]`);
    const descTextarea = document.querySelector(`${HERO_TAB_ID} textarea`);
    const overlayCheckbox = document.getElementById('hero-overlay');
    const opacityRange = document.getElementById('hero-overlay-opacity');
    const textPosSelect = document.getElementById('hero-text-position');
    const data = {
        conference_id: id,
        image: urlInput.value,
        hero_title: titleInput.value,
        hero_description: descTextarea.value,
        hero_overlay: overlayCheckbox.checked ? 1 : 0,
        hero_overlay_opacity: opacityRange.value,
        hero_text_position: textPosSelect.value
    };
    fetch(HERO_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(json => {
        if (json.success) {
            fetchHeroImage();
            alert('Cập nhật ảnh nền hero thành công!');
        } else {
            alert('Lỗi cập nhật: ' + (json.error || 'Không xác định'));
        }
    });
}

// Xóa ảnh hero (set image = null)
function deleteHeroImage() {
    const id = getCurrentConferenceId();
    fetch(HERO_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ conference_id: id, image: '' })
    })
    .then(res => res.json())
    .then(json => {
        if (json.success) {
            fetchHeroImage();
        }
    });
}

// Cập nhật preview khi thay đổi input
function setupHeroImagePreview() {
    const urlInput = document.getElementById('hero-image-url');
    const preview = document.getElementById('hero-preview');
    if (urlInput && preview) {
        urlInput.addEventListener('input', () => {
            preview.style.backgroundImage = `url('${urlInput.value}')`;
        });
    }
    const opacityRange = document.getElementById('hero-overlay-opacity');
    const overlayDiv = preview ? preview.querySelector('.hero-overlay') : null;
    const opacityValue = document.getElementById('opacity-value');
    if (opacityRange && overlayDiv && opacityValue) {
        opacityRange.addEventListener('input', () => {
            overlayDiv.style.opacity = (opacityRange.value / 100).toString();
            opacityValue.textContent = `${opacityRange.value}%`;
        });
    }
}

// Khởi tạo khi vào tab hero image
function initHeroImageTab() {
    fetchHeroImage();
    const form = document.querySelector(HERO_FORM_ID);
    if (form) form.onsubmit = updateHeroImage;
    // Nút xóa ảnh
    const delBtn = document.querySelector(`${HERO_TAB_ID} .btn-danger`);
    if (delBtn) delBtn.onclick = deleteHeroImage;
    setupHeroImagePreview();
}

document.addEventListener('DOMContentLoaded', () => {
    // Nếu tab hero image hiển thị thì khởi tạo
    const tab = document.querySelector(HERO_TAB_ID);
    if (tab) {
        initHeroImageTab();
    }
});
