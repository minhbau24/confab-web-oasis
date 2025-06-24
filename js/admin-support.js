// ===================== SUPPORT INFO MANAGEMENT =====================
// Quản lý Thông tin hỗ trợ (Support Info)
// Thêm code xử lý cho tab Thông tin hỗ trợ ở đây

// Lấy conferenceId hiện tại (giả sử lấy từ #conference-title hoặc biến toàn cục)
function getCurrentConferenceId() {
    // Nếu có biến global, thay thế ở đây. Tạm thời lấy id đầu tiên trong mảng mẫu
    if (typeof conferences !== 'undefined' && conferences.length > 0) {
        return conferences[0].id;
    }
    // Hoặc lấy từ DOM nếu có
    // return parseInt(document.getElementById('conference-title').dataset.id);
    return 1;
}

function fetchSupportInfo() {
    const conferenceId = getCurrentConferenceId();
    fetch(`api/conference_support_info.php?conference_id=${conferenceId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                const info = data.data;
                document.getElementById('support-email').value = info.support_email || '';
                document.getElementById('support-phone').value = info.support_phone || '';
                document.getElementById('support-address').value = info.support_address || '';
                document.getElementById('facebook-url').value = info.facebook_url || '';
                document.getElementById('twitter-url').value = info.twitter_url || '';
                document.getElementById('linkedin-url').value = info.linkedin_url || '';
            }
        });
}

function updateSupportInfo(e) {
    e.preventDefault();
    const conferenceId = getCurrentConferenceId();
    const formData = new FormData();
    formData.append('conference_id', conferenceId);
    formData.append('support_email', document.getElementById('support-email').value);
    formData.append('support_phone', document.getElementById('support-phone').value);
    formData.append('support_address', document.getElementById('support-address').value);
    formData.append('facebook_url', document.getElementById('facebook-url').value);
    formData.append('twitter_url', document.getElementById('twitter-url').value);
    formData.append('linkedin_url', document.getElementById('linkedin-url').value);
    fetch('api/conference_support_info.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Cập nhật thông tin hỗ trợ thành công!');
        } else {
            alert('Cập nhật thất bại!');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Tải dữ liệu khi vào tab
    fetchSupportInfo();
    document.getElementById('contact-info-form').addEventListener('submit', updateSupportInfo);
    document.getElementById('social-links-form').addEventListener('submit', updateSupportInfo);
});
