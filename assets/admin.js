// Admin-specific assets
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import './styles/admin/picture_dropzone.scss';

// Expose Quill globally for admin pages
window.Quill = Quill;

// Initialize Quill editors on textareas with rich-text-editor class
function initQuillEditors() {
    document.querySelectorAll('textarea.rich-text-editor:not(.quill-initialized)').forEach(function(textarea) {
        textarea.classList.add('quill-initialized');

        const editorDiv = document.createElement('div');
        editorDiv.style.minHeight = '300px';
        textarea.parentNode.insertBefore(editorDiv, textarea);
        textarea.style.display = 'none';

        const quill = new Quill(editorDiv, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'font': [] }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'script': 'sub' }, { 'script': 'super' }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
                    [{ 'indent': '-1' }, { 'indent': '+1' }],
                    [{ 'align': [] }],
                    [{ 'direction': 'rtl' }],
                    ['blockquote', 'code-block'],
                    ['link', 'image', 'video'],
                    ['clean'],
                ],
                clipboard: {
                    matchVisual: false,
                },
            },
        });

        if (textarea.value) {
            quill.root.innerHTML = textarea.value;
        }

        quill.on('text-change', function() {
            textarea.value = quill.root.innerHTML;
            textarea.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initQuillEditors, 100);

    document.addEventListener('sonata-collection-item-added', function() {
        setTimeout(initQuillEditors, 100);
    });
});

window.initQuillEditors = initQuillEditors;

// Drag & drop picture upload (e.g. games list "picture" column)
function initPictureDropzones() {
    document.querySelectorAll('[data-vgr-picture-dropzone]:not(.vgr-picture-dropzone-initialized)').forEach(function(container) {
        container.classList.add('vgr-picture-dropzone-initialized');

        const zone = container.querySelector('.vgr-picture-dropzone__zone');
        const input = container.querySelector('.vgr-picture-dropzone__input');
        const filenameEl = container.querySelector('.vgr-picture-dropzone__filename');
        const messageEl = container.querySelector('.vgr-picture-dropzone__message');
        const uploadUrl = container.dataset.uploadUrl;
        const csrfToken = container.dataset.csrfToken;
        const successMessage = container.dataset.successMessage;

        if (!zone || !input || !uploadUrl) {
            return;
        }

        function showMessage(text, isError) {
            if (!messageEl) return;
            messageEl.textContent = text;
            messageEl.classList.toggle('is-error', !!isError);
            messageEl.classList.toggle('is-success', !isError);
        }

        function uploadFile(file) {
            zone.classList.add('is-uploading');
            showMessage('', false);

            const formData = new FormData();
            formData.append('picture', file);
            formData.append('_token', csrfToken);

            fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function(response) {
                    return response.json().then(function(data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function(result) {
                    if (result.ok && result.data.success) {
                        if (filenameEl) {
                            filenameEl.textContent = result.data.filename;
                        }
                        showMessage(successMessage || 'Uploaded', false);
                    } else {
                        showMessage(result.data.message || 'Upload failed', true);
                    }
                })
                .catch(function() {
                    showMessage('Network error', true);
                })
                .finally(function() {
                    zone.classList.remove('is-uploading');
                    input.value = '';
                });
        }

        zone.addEventListener('click', function() {
            input.click();
        });

        input.addEventListener('change', function() {
            if (input.files.length > 0) {
                uploadFile(input.files[0]);
            }
        });

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
            zone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(function(eventName) {
            zone.addEventListener(eventName, function() {
                zone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function(eventName) {
            zone.addEventListener(eventName, function() {
                zone.classList.remove('is-dragover');
            });
        });

        zone.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                uploadFile(files[0]);
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initPictureDropzones();
});

window.initPictureDropzones = initPictureDropzones;
