// ===================== COMMUNICATION MANAGEMENT =====================
// Thêm code xử lý cho tab Liên lạc ở đây nếu có

// ========== GỬI EMAIL HÀNG LOẠT (giả lập demo) ==========
document.addEventListener('DOMContentLoaded', function () {
    const emailForm = document.querySelector('#communication form');
    if (emailForm) {
        emailForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const recipient = emailForm.querySelector('select').value;
            const subject = emailForm.querySelector('input[type="text"]').value;
            const content = emailForm.querySelector('textarea').value;
            // Gửi email (giả lập, thực tế cần gọi API backend gửi email)
            alert(`Đã gửi email tới: ${recipient}\nTiêu đề: ${subject}`);
            // Thêm vào lịch sử liên lạc (giả lập)
            addCommunicationHistory({
                date: new Date().toLocaleDateString('vi-VN'),
                type: 'Email',
                subject: subject,
                recipient: recipient,
                status: 'Đã gửi'
            });
            emailForm.reset();
        });
    }

    // ========== XUẤT DỮ LIỆU ==========
    const exportBtns = document.querySelectorAll('#communication .btn-outline-success, #communication .btn-outline-info, #communication .btn-outline-warning, #communication .btn-outline-secondary');
    exportBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const text = btn.innerText.trim();
            alert(`Chức năng xuất: ${text} (demo)`);
        });
    });

    // ========== LỊCH SỬ LIÊN LẠC (giả lập) ==========
    renderCommunicationHistory();
});

function addCommunicationHistory(entry) {
    let history = JSON.parse(localStorage.getItem('communicationHistory') || '[]');
    history.unshift(entry);
    if (history.length > 20) history = history.slice(0, 20);
    localStorage.setItem('communicationHistory', JSON.stringify(history));
    renderCommunicationHistory();
}

function renderCommunicationHistory() {
    const tbody = document.querySelector('#communication table tbody');
    if (!tbody) return;
    let history = JSON.parse(localStorage.getItem('communicationHistory') || '[]');
    let html = '';
    history.forEach(item => {
        html += `<tr><td>${item.date}</td><td><span class='badge bg-info'>${item.type}</span></td><td>${item.subject}</td><td>${item.recipient}</td><td><span class='badge bg-success'>${item.status}</span></td></tr>`;
    });
    tbody.innerHTML = html;
}
