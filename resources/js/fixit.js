import 'bootstrap/dist/js/bootstrap.bundle.min.js';

const themeBtn = document.querySelector('[data-theme-toggle]');
function paintThemeBtn(){ if(!themeBtn) return; const dark = document.documentElement.getAttribute('data-theme')==='dark'; themeBtn.textContent = dark ? '☀️ โหมดสว่าง' : '🌙 โหมดมืด'; }
if (themeBtn) themeBtn.addEventListener('click', () => {
    const dark = document.documentElement.getAttribute('data-theme')==='dark';
    if (dark) { document.documentElement.removeAttribute('data-theme'); try{localStorage.removeItem('fixit-theme')}catch(e){} }
    else { document.documentElement.setAttribute('data-theme','dark'); try{localStorage.setItem('fixit-theme','dark')}catch(e){} }
    paintThemeBtn();
});
paintThemeBtn();
const liveForm = document.querySelector('form[data-live-search]');
if (liveForm) {
    const result = document.querySelector('[data-live-result]');
    const total = document.querySelector('[data-live-total]');
    const hint = document.querySelector('[data-live-hint]');
    let timer = null;
    async function runLive() {
        const url = new URL(liveForm.action);
        url.search = new URLSearchParams(new FormData(liveForm)).toString();
        if (hint) hint.textContent = 'กำลังค้นหา...';
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (result) result.innerHTML = data.html;
            if (total) total.textContent = '/ ' + data.total + ' รายการ';
            window.history.replaceState(null, '', url);
        } catch (e) { }
        if (hint) hint.textContent = '';
    }
    liveForm.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(runLive, 350); });
    liveForm.addEventListener('change', () => { clearTimeout(timer); timer = setTimeout(runLive, 200); });
    liveForm.addEventListener('submit', e => { e.preventDefault(); clearTimeout(timer); runLive(); });
    document.addEventListener('click', e => {
        const link = e.target.closest('[data-live-result] .pagination a');
        if (!link) return;
        e.preventDefault();
        fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(data => { if (result) result.innerHTML = data.html; if (total) total.textContent = '/ ' + data.total + ' รายการ'; window.history.replaceState(null, '', link.href); });
    });
}
document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            const bad = form.querySelector(':invalid');
            if (bad) bad.focus();
            return;
        }
        const btn = form.querySelector('button[type="submit"]');
        const overlay = document.querySelector('[data-send-overlay]');
        if (btn) { btn.classList.add('btn-loading'); btn.disabled = true; }
        if (overlay) overlay.hidden = false;
    });
    form.addEventListener('input', () => form.classList.remove('was-validated'), { once: true });
});
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
const input = document.querySelector('[data-image-input]');
if (input) input.addEventListener('change', () => {
    const preview = document.querySelector('[data-image-preview]');
    if (!preview) return;
    if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
    const file = input.files[0];
    preview.hidden = !file;
    if (file && file.type.startsWith('image/')) {
        preview.dataset.objectUrl = URL.createObjectURL(file);
        preview.src = preview.dataset.objectUrl;
    } else preview.hidden = true;
});
const suggestInput = document.querySelector('[data-suggest]');
const suggestBox = document.querySelector('[data-suggest-box]');
if (suggestInput && suggestBox) {
    let sTimer = null;
    suggestInput.addEventListener('input', () => {
        clearTimeout(sTimer);
        sTimer = setTimeout(async () => {
            const q = suggestInput.value.trim();
            if (q.length < 2) { suggestBox.hidden = true; return; }
            try {
                const res = await fetch('/repairs/suggest?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (!data.count) { suggestBox.hidden = true; return; }
                suggestBox.innerHTML = 'อาการนี้เคยเจอ ' + data.count + ' ครั้ง' + (data.hint ? ' · แนวทางที่ช่างใช้บ่อย: ' + data.hint.replace(/</g, '&lt;') : '');
                suggestBox.hidden = false;
            } catch (e) { suggestBox.hidden = true; }
        }, 400);
    });
}
