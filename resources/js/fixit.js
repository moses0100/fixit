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
    if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
    const file = input.files[0];
    preview.hidden = !file;
    if (file && file.type.startsWith('image/')) {
        preview.dataset.objectUrl = URL.createObjectURL(file);
        preview.src = preview.dataset.objectUrl;
    } else preview.hidden = true;
});
