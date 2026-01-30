import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller for proof upload (image drag & drop and video URL)
 */
export default class extends Controller {
    static targets = [
        'dropzone',
        'preview',
        'previewImage',
        'fileInput',
        'pictureSubmit',
        'videoUrl',
        'videoPreview',
        'videoSubmit',
        'successMessage',
        'errorMessage',
        'form'
    ];

    static values = {
        playerChartId: Number,
        pictureUrl: String,
        videoUrl: String,
        maxSize: { type: Number, default: 5242880 } // 5MB default
    };

    connect() {
        this.selectedFile = null;
        this.setupDropzone();
    }

    setupDropzone() {
        if (!this.hasDropzoneTarget) return;

        const dropzone = this.dropzoneTarget;

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        // Highlight on drag
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            });
        });

        // Remove highlight on leave
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            });
        });

        // Handle drop
        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFile(files[0]);
            }
        });
    }

    // Triggered when clicking on dropzone
    triggerFileInput() {
        if (this.hasFileInputTarget) {
            this.fileInputTarget.click();
        }
    }

    // Handle file selection from input
    onFileSelected(event) {
        const files = event.target.files;
        if (files.length > 0) {
            this.handleFile(files[0]);
        }
    }

    handleFile(file) {
        this.hideMessages();

        // Validate file type
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            this.showError('Invalid file type. Please use JPEG or PNG images.');
            return;
        }

        // Validate file size
        if (file.size > this.maxSizeValue) {
            this.showError('File is too large. Maximum size is 5MB.');
            return;
        }

        this.selectedFile = file;

        // Show preview
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
            if (this.hasPictureSubmitTarget) {
                this.pictureSubmitTarget.disabled = false;
            }
        };
        reader.readAsDataURL(file);
    }

    // Remove selected image
    removeImage() {
        this.selectedFile = null;
        if (this.hasPreviewTarget) {
            this.previewTarget.classList.add('d-none');
        }
        if (this.hasDropzoneTarget) {
            this.dropzoneTarget.classList.remove('d-none');
        }
        if (this.hasPictureSubmitTarget) {
            this.pictureSubmitTarget.disabled = true;
        }
        if (this.hasFileInputTarget) {
            this.fileInputTarget.value = '';
        }
    }

    // Submit picture proof
    async submitPicture(event) {
        event.preventDefault();

        if (!this.selectedFile) {
            this.showError('Please select an image first.');
            return;
        }

        this.hideMessages();
        this.setLoading(true, 'picture');

        try {
            const base64 = await this.fileToBase64(this.selectedFile);

            const response = await fetch(this.pictureUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ file: base64 }),
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (response.ok) {
                this.showSuccess('Proof uploaded successfully!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showError(data.error || 'Failed to upload proof.');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showError('Network error. Please try again.');
        } finally {
            this.setLoading(false, 'picture');
        }
    }

    // Validate YouTube URL input
    onVideoUrlInput() {
        if (!this.hasVideoUrlTarget || !this.hasVideoSubmitTarget) return;

        const url = this.videoUrlTarget.value.trim();
        const isValid = this.isValidYoutubeUrl(url);

        this.videoSubmitTarget.disabled = !isValid;

        // Show/hide preview
        if (isValid && this.hasVideoPreviewTarget) {
            const videoId = this.extractYoutubeId(url);
            if (videoId) {
                this.videoPreviewTarget.innerHTML = `
                    <div class="ratio ratio-16x9 mt-3">
                        <iframe
                            src="https://www.youtube.com/embed/${videoId}"
                            title="Video preview"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            class="rounded"
                        ></iframe>
                    </div>
                `;
                this.videoPreviewTarget.classList.remove('d-none');
            }
        } else if (this.hasVideoPreviewTarget) {
            this.videoPreviewTarget.classList.add('d-none');
            this.videoPreviewTarget.innerHTML = '';
        }
    }

    // Submit video proof
    async submitVideo(event) {
        event.preventDefault();

        if (!this.hasVideoUrlTarget) return;

        const url = this.videoUrlTarget.value.trim();

        if (!this.isValidYoutubeUrl(url)) {
            this.showError('Please enter a valid YouTube URL.');
            return;
        }

        this.hideMessages();
        this.setLoading(true, 'video');

        try {
            const response = await fetch(this.videoUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ url: url }),
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (response.ok) {
                this.showSuccess('Video proof submitted successfully!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showError(data.error || 'Failed to submit video proof.');
            }
        } catch (error) {
            console.error('Submit error:', error);
            this.showError('Network error. Please try again.');
        } finally {
            this.setLoading(false, 'video');
        }
    }

    // Helper methods
    fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    isValidYoutubeUrl(url) {
        const patterns = [
            /^https?:\/\/(www\.)?youtube\.com\/watch\?v=[\w-]+/,
            /^https?:\/\/youtu\.be\/[\w-]+/,
            /^https?:\/\/(www\.)?youtube\.com\/embed\/[\w-]+/,
            /^https?:\/\/(www\.)?youtube\.com\/v\/[\w-]+/
        ];

        return patterns.some(pattern => pattern.test(url));
    }

    extractYoutubeId(url) {
        const patterns = [
            /youtube\.com\/watch\?v=([\w-]+)/,
            /youtu\.be\/([\w-]+)/,
            /youtube\.com\/embed\/([\w-]+)/,
            /youtube\.com\/v\/([\w-]+)/
        ];

        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match) {
                return match[1];
            }
        }

        return null;
    }

    setLoading(isLoading, type) {
        const submitButton = type === 'picture' ? this.pictureSubmitTarget : this.videoSubmitTarget;

        if (submitButton) {
            submitButton.disabled = isLoading;
            if (isLoading) {
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
            } else {
                submitButton.innerHTML = type === 'picture'
                    ? '<i class="bi bi-upload me-2"></i>Upload Proof'
                    : '<i class="bi bi-send me-2"></i>Submit Video';
            }
        }
    }

    showSuccess(message) {
        if (this.hasSuccessMessageTarget) {
            this.successMessageTarget.textContent = message;
            this.successMessageTarget.classList.remove('d-none');
        }
    }

    showError(message) {
        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.textContent = message;
            this.errorMessageTarget.classList.remove('d-none');
        }
    }

    hideMessages() {
        if (this.hasSuccessMessageTarget) {
            this.successMessageTarget.classList.add('d-none');
        }
        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.classList.add('d-none');
        }
    }
}
