import 'bootstrap/dist/js/bootstrap.bundle.min.js';

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
