// Admin-specific assets
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

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
