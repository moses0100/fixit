import 'bootstrap/dist/js/bootstrap.bundle.min.js';

const themeBtn = document.querySelector('[data-theme-toggle]');
function paintThemeBtn(){ if(!themeBtn) return; const dark = document.documentElement.getAttribute('data-theme')==='dark'; themeBtn.textContent = dark ? '☀️' : '🌙'; }
if (themeBtn) themeBtn.addEventListener('click', () => {
    const dark = document.documentElement.getAttribute('data-theme')==='dark';
    if (dark) { document.documentElement.removeAttribute('data-theme'); try{localStorage.removeItem('fixit-theme')}catch(e){} }
    else { document.documentElement.setAttribute('data-theme','dark'); try{localStorage.setItem('fixit-theme','dark')}catch(e){} }
    paintThemeBtn();
});
paintThemeBtn();
const statusCanvas = document.querySelector('#statusChart');
if (statusCanvas) {
    let counts = {};
    try { counts = JSON.parse(statusCanvas.dataset.counts || '{}'); } catch (e) { }
    const summary = document.querySelector('#statusSummary');
    let chart = null;
    const labels = ['รอตรวจสอบ', 'กำลังซ่อม', 'ซ่อมเสร็จ', 'ยกเลิก'];
    const values = current => [current.pending || 0, current.repairing || 0, current.completed || 0, current.cancelled || 0];
    const render = () => {
        const total = values(counts).reduce((sum, value) => sum + Number(value), 0);
        if (summary) summary.textContent = total > 0
            ? 'ทั้งหมด ' + total + ' รายการ · ซ่อมเสร็จ ' + (counts.completed || 0) + ' · กำลังซ่อม ' + (counts.repairing || 0) + ' · รอตรวจ ' + (counts.pending || 0)
            : 'ยังไม่มีข้อมูลงานซ่อม';
        document.querySelectorAll('[data-count-status]').forEach(link => {
            const value = link.querySelector('.metric-value');
            const key = link.getAttribute('data-count-status');
            if (value && key === 'all') value.textContent = total;
            else if (value && counts[key] !== undefined) value.textContent = counts[key];
        });
        if (chart) {
            chart.data.datasets[0].data = values(counts);
            chart.update();
        }
    };
    render();
    import('chart.js/auto').then(({ default: Chart }) => {
        chart = new Chart(statusCanvas, {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values(counts), backgroundColor: ['#e8b739', '#4a8bd4', '#2f9e6e', '#9aa5a0'], borderWidth: 2, borderColor: '#fff' }] },
            options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } } }, cutout: '62%' },
        });
    }).catch(() => { });
    window.setInterval(() => {
        fetch(statusCanvas.dataset.countsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(nextCounts => { counts = nextCounts; render(); })
            .catch(() => { });
    }, 15000);
}
const liveForm = document.querySelector('form[data-live-search]');
if (liveForm) {
    const result = document.querySelector('[data-live-result]');
    const total = document.querySelector('[data-live-total]');
    const hint = document.querySelector('[data-live-hint]');
    let timer = null;
    const exp = document.querySelector('[data-export-link]');
    function syncExport(fromUrl) {
        if (!exp) return;
        const base = new URL(exp.href, window.location.origin);
        const params = new URL(fromUrl, window.location.origin).searchParams;
        base.search = params.toString();
        exp.href = base.toString();
    }
    async function runLive() {
        const url = new URL(liveForm.action);
        url.search = new URLSearchParams(new FormData(liveForm)).toString();
        if (hint) hint.textContent = 'กำลังค้นหา...';
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (result) result.innerHTML = data.html;
            if (total) total.textContent = '/ ' + data.total + ' รายการ';
            syncExport(url);
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
            .then(r => r.json()).then(data => { if (result) result.innerHTML = data.html; if (total) total.textContent = '/ ' + data.total + ' รายการ'; syncExport(link.href); window.history.replaceState(null, '', link.href); });
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
document.querySelectorAll('[data-device]').forEach(btn => {
    btn.addEventListener('click', () => {
        const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
        set('device_type', btn.dataset.type);
        set('brand', btn.dataset.brand);
        set('model', btn.dataset.model);
        set('serial_number', btn.dataset.serial);
        if (btn.dataset.phone) set('contact_phone', btn.dataset.phone);
        document.getElementById('title')?.focus();
    });
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
                suggestBox.textContent = 'อาการนี้เคยเจอ ' + data.count + ' ครั้ง' + (data.hint ? ' · แนวทางที่ช่างใช้บ่อย: ' + data.hint : '');
                suggestBox.hidden = false;
            } catch (e) { suggestBox.hidden = true; }
        }, 400);
    });
}
