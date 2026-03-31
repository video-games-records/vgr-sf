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

        this._handleDrop = this.handleDrop.bind(this);
        this._handleDragOver = (e) => { if (e.dataTransfer?.types?.includes('Files')) e.preventDefault(); };
        this._handlePaste = this.handlePaste.bind(this);
        editorDiv.addEventListener('drop', this._handleDrop, true);
        editorDiv.addEventListener('dragover', this._handleDragOver);
        editorDiv.addEventListener('paste', this._handlePaste);

        this.editorDiv = editorDiv;
    }

    disconnect() {
        if (this.editorDiv) {
            this.editorDiv.removeEventListener('drop', this._handleDrop, true);
            this.editorDiv.removeEventListener('dragover', this._handleDragOver);
            this.editorDiv.removeEventListener('paste', this._handlePaste);
            this.editorDiv.remove();
        }
    }

    handleDrop(event) {
        const files = event.dataTransfer?.files;
        if (!files || files.length === 0) return;

        const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
        if (imageFiles.length === 0) return;

        event.preventDefault();
        event.stopPropagation();

        imageFiles.forEach(file => this.insertImageFile(file));
    }

    handlePaste(event) {
        const items = event.clipboardData?.items;
        if (!items) return;

        const imageItems = Array.from(items).filter(i => i.type.startsWith('image/'));
        if (imageItems.length === 0) return;

        event.preventDefault();
        imageItems.forEach(item => this.insertImageFile(item.getAsFile()));
    }

    insertImageFile(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            const range = this.quill.getSelection(true);
            this.quill.insertEmbed(range.index, 'image', e.target.result, 'user');
            this.quill.setSelection(range.index + 1);
        };
        reader.readAsDataURL(file);
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
