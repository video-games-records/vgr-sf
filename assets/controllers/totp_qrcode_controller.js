import { Controller } from '@hotwired/stimulus';
import QRCode from 'qrcode';

export default class extends Controller {
    static values = {
        content: String,
    };

    connect() {
        const canvas = document.createElement('canvas');
        this.element.appendChild(canvas);

        QRCode.toCanvas(canvas, this.contentValue, { width: 200, margin: 2 }, (error) => {
            if (error) {
                this.element.innerHTML = '<p class="text-danger small">QR code generation failed.</p>';
            }
        });
    }
}
