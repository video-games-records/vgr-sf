import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller for My Proofs drag & drop upload with auto-submit.
 *
 * Usage:
 *   <form data-controller="my-proof-upload" ...>
 *       <div data-my-proof-upload-target="dropzone" data-action="click->my-proof-upload#triggerFileInput">
 *           ...
 *       </div>
 *       <input type="file" data-my-proof-upload-target="fileInput"
 *              data-action="change->my-proof-upload#onFileSelected" class="d-none" ...>
 *       <div data-my-proof-upload-target="preview" class="d-none">
 *           <img data-my-proof-upload-target="previewImage" ...>
 *       </div>
 *       <div data-my-proof-upload-target="loading" class="d-none">...</div>
 *   </form>
 */
export default class extends Controller {
    static targets = ['dropzone', 'fileInput', 'preview', 'previewImage', 'loading'];

    static values = {
        maxSize: { type: Number, default: 5242880 } // 5MB
    };

    connect() {
        this.setupDropzone();
    }

    setupDropzone() {
        if (!this.hasDropzoneTarget) return;

        const dropzone = this.dropzoneTarget;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            });
        });

        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFile(files[0]);
            }
        });
    }

    triggerFileInput() {
        if (this.hasFileInputTarget) {
            this.fileInputTarget.click();
        }
    }

    onFileSelected(event) {
        const files = event.target.files;
        if (files.length > 0) {
            this.handleFile(files[0]);
        }
    }

    handleFile(file) {
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            return;
        }

        if (file.size > this.maxSizeValue) {
            return;
        }

        // Set file on the hidden input so it's included in the form submission
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        this.fileInputTarget.files = dataTransfer.files;

        // Show preview briefly then auto-submit
        const reader = new FileReader();
        reader.onload = (e) => {
            if (this.hasPreviewImageTarget) {
                this.previewImageTarget.src = e.target.result;
            }
            if (this.hasPreviewTarget) {
                this.previewTarget.classList.remove('d-none');
            }
            if (this.hasDropzoneTarget) {
                this.dropzoneTarget.classList.add('d-none');
            }

            // Auto-submit after a short delay to show the preview
            setTimeout(() => {
                this.submitForm();
            }, 500);
        };
        reader.readAsDataURL(file);
    }

    submitForm() {
        if (this.hasLoadingTarget) {
            this.loadingTarget.classList.remove('d-none');
        }
        if (this.hasPreviewTarget) {
            this.previewTarget.classList.add('d-none');
        }

        this.element.submit();
    }
}
