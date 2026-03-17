import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller for My Proofs delete action.
 *
 * Usage:
 *   <form data-controller="my-proof-delete" ...>
 *       <button data-my-proof-delete-target="button" data-action="click->my-proof-delete#confirm">...</button>
 *       <div data-my-proof-delete-target="loading" class="d-none">...</div>
 *   </form>
 */
export default class extends Controller {
    static targets = ['button', 'loading'];

    async confirm(event) {
        event.preventDefault();

        const button = this.buttonTarget;

        if (!button.dataset.confirmed) {
            button.dataset.confirmed = '1';
            button.classList.remove('btn-outline-danger');
            button.classList.add('btn-danger');
            button.textContent = button.dataset.confirmLabel ?? '?';

            setTimeout(() => {
                delete button.dataset.confirmed;
                button.classList.remove('btn-danger');
                button.classList.add('btn-outline-danger');
                button.textContent = button.dataset.deleteLabel ?? '';
            }, 3000);

            return;
        }

        await this.submit();
    }

    async submit() {
        if (this.hasButtonTarget) {
            this.buttonTarget.classList.add('d-none');
        }
        if (this.hasLoadingTarget) {
            this.loadingTarget.classList.remove('d-none');
        }

        const form = this.element;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const html = await response.text();
                const container = form.closest('[id^="score-card-"]');
                if (container) {
                    container.innerHTML = html;
                }
            } else {
                this.showError();
            }
        } catch {
            this.showError();
        }
    }

    showError() {
        if (this.hasLoadingTarget) {
            this.loadingTarget.classList.add('d-none');
        }
        if (this.hasButtonTarget) {
            this.buttonTarget.classList.remove('d-none');
        }
    }
}