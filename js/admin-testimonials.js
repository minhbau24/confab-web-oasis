// Quản lý Testimonials
// Thêm code xử lý cho tab Testimonials ở đây

// ===================== TESTIMONIALS MANAGEMENT =====================

// Lấy conferenceId động từ URL
function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

// Fetch testimonials từ API
async function fetchTestimonials() {
    const conferenceId = getConferenceIdFromUrl();
    const res = await fetch(`api/testimonials.php?conference_id=${conferenceId}`);
    const data = await res.json();
    if (data && data.success) {
        renderTestimonials(data.testimonials);
    }
}

// Render testimonials ra ngoài trang
function renderTestimonials(testimonials) {
    const container = document.getElementById('testimonials-container');
    if (!container) return;
    container.innerHTML = '';
    testimonials.forEach(item => {
        const card = document.createElement('div');
        card.className = 'col-md-6 mb-4';
        card.innerHTML = `
            <div class="management-card p-4">
                <div class="d-flex align-items-center mb-3">
                    <img src="${item.avatar || 'https://placehold.co/80x80'}" class="rounded-circle me-3" width="80" height="80" alt="Avatar">
                    <div>
                        <strong>${item.name}</strong><br>
                        <span class="text-muted">${item.position || ''}</span>
                    </div>
                </div>
                <p class="mb-3">"${item.comment}"</p>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-outline-primary btn-sm" data-id="${item.id}" onclick="openEditTestimonialModal(${item.id})"><i class="fas fa-edit me-1"></i>Sửa</button>
                    <button class="btn btn-outline-danger btn-sm" data-id="${item.id}" onclick="deleteTestimonial(${item.id})"><i class="fas fa-trash me-1"></i>Xóa</button>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

// Mở modal sửa testimonial
function openEditTestimonialModal(id) {
    // Lấy testimonial từ API (hoặc cache)
    fetch(`api/testimonials.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                document.getElementById('edit-testimonial-id').value = data.testimonial.id;
                document.getElementById('edit-testimonial-name').value = data.testimonial.name;
                document.getElementById('edit-testimonial-position').value = data.testimonial.position;
                document.getElementById('edit-testimonial-avatar').value = data.testimonial.avatar;
                document.getElementById('edit-testimonial-comment').value = data.testimonial.comment;
                // Đánh dấu radio rating
                const rating = data.testimonial.rating || 5;
                document.querySelectorAll('input[name="editRating"]').forEach(radio => {
                    radio.checked = (parseInt(radio.value) === parseInt(rating));
                });
                // Hiển thị modal
                const editTestimonialModal = new bootstrap.Modal(document.getElementById('editTestimonialModal'));
                editTestimonialModal.show();
            }
        });
}

// Xử lý submit form sửa testimonial
const editTestimonialForm = document.getElementById('edit-testimonial-form');
if (editTestimonialForm) {
    editTestimonialForm.onsubmit = function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-testimonial-id').value;
        const name = document.getElementById('edit-testimonial-name').value;
        const position = document.getElementById('edit-testimonial-position').value;
        const avatar = document.getElementById('edit-testimonial-avatar').value;
        const comment = document.getElementById('edit-testimonial-comment').value;
        const rating = document.querySelector('input[name="editRating"]:checked').value;
        fetch('api/testimonials.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, name, position, avatar, comment, rating })
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                showToast('Cập nhật testimonial thành công!', 'success');
                fetchTestimonials();
                const modal = bootstrap.Modal.getInstance(document.getElementById('editTestimonialModal'));
                modal.hide();
            } else {
                showToast('Có lỗi khi cập nhật testimonial!', 'danger');
            }
        });
    }
}

// Xóa testimonial
function deleteTestimonial(id) {
    if (!confirm('Bạn có chắc muốn xóa testimonial này?')) return;
    fetch('api/testimonials.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            showToast('Đã xóa testimonial!', 'success');
            fetchTestimonials();
        } else {
            showToast('Có lỗi khi xóa testimonial!', 'danger');
        }
    });
}

// Khi load tab, tự động fetch testimonials
if (document.getElementById('testimonials')) {
    fetchTestimonials();
}
