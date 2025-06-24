// Quản lý FAQ
// Thêm code xử lý cho tab FAQ ở đây

// ===================== FAQ MANAGEMENT =====================

let faqData = [];

function getConferenceIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1';
}

async function initializeFaqData() {
    const conferenceId = getConferenceIdFromUrl();
    try {
        const res = await fetch(`api/conference_detail.php?id=${conferenceId}`);
        const data = await res.json();
        if (data && data.status && data.data && Array.isArray(data.data.faq)) {
            faqData = data.data.faq;
        } else {
            faqData = [];
        }
    } catch (e) {
        faqData = [];
    }
}

function renderFaqAccordion() {
    const container = document.getElementById('faq-accordion');
    if (!container) return;
    if (faqData.length === 0) {
        container.innerHTML = '<div class="text-center text-muted">Chưa có câu hỏi nào.</div>';
        return;
    }
    container.innerHTML = faqData.map((faq, idx) => `
        <div class="accordion-item">
            <h2 class="accordion-header" id="faq-heading-${faq.id}">
                <button class="accordion-button${idx === 0 ? '' : ' collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-${faq.id}" aria-expanded="${idx === 0 ? 'true' : 'false'}" aria-controls="faq-collapse-${faq.id}">
                    ${faq.question}
                </button>
            </h2>
            <div id="faq-collapse-${faq.id}" class="accordion-collapse collapse${idx === 0 ? ' show' : ''}" aria-labelledby="faq-heading-${faq.id}" data-bs-parent="#faq-accordion">
                <div class="accordion-body">
                    ${faq.answer}
                </div>
            </div>
        </div>
    `).join('');
}

function initializeFaqTab() {
    initializeFaqData().then(renderFaqAccordion);
}

const faqTab = document.querySelector('[data-bs-target="#faq-management"]');
if (faqTab) {
    faqTab.addEventListener('shown.bs.tab', function () {
        initializeFaqTab();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const faqTabPane = document.getElementById('faq-management');
    if (faqTabPane && faqTabPane.classList.contains('active')) {
        initializeFaqTab();
    }
});
