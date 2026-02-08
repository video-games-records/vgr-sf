import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['display', 'form'];

    toggle() {
        this.displayTarget.classList.toggle('d-none');
        this.formTarget.classList.toggle('d-none');
    }
}
