import { Controller } from '@hotwired/stimulus';
import Quill from 'quill';

export default class extends Controller {
    static values = {
        toolbar: { type: String, default: 'minimal' },
        minHeight: { type: String, default: '200px' },
    };

    connect() {
        const editorDiv = document.createElement('div');
        editorDiv.style.minHeight = this.minHeightValue;
        this.element.parentNode.insertBefore(editorDiv, this.element);
        this.element.style.display = 'none';

        this.quill = new Quill(editorDiv, {
            theme: 'snow',
            modules: {
                toolbar: this.getToolbarConfig(),
            },
        });

        if (this.element.value) {
            this.quill.root.innerHTML = this.element.value;
        }

        this.quill.on('text-change', () => {
            this.element.value = this.quill.root.innerHTML;
            this.element.dispatchEvent(new Event('change', { bubbles: true }));
        });

        this.editorDiv = editorDiv;
    }

    disconnect() {
        if (this.editorDiv) {
            this.editorDiv.remove();
        }
    }

    getToolbarConfig() {
        if (this.toolbarValue === 'full') {
            return [
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
            ];
        }

        // Minimal toolbar (default)
        return [
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['link'],
            ['clean'],
        ];
    }
}
