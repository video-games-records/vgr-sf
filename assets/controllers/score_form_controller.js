import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller for score form
 * Handles change detection and visual feedback for modified scores
 */
export default class extends Controller {
    static targets = ['counter', 'summary', 'row'];

    connect() {
        this.modifiedCharts = new Set();
        this.updateCounter();
    }

    /**
     * Handle input/change events on form fields
     */
    onChange(event) {
        const row = event.target.closest('tr[data-chart-id]');
        if (!row) return;

        const chartId = row.dataset.chartId;
        const original = this.parseOriginalData(row);
        const current = this.getCurrentValues(row);

        const isModified = this.hasChanges(original, current);

        if (isModified) {
            this.modifiedCharts.add(chartId);
            row.classList.add('table-warning');
            this.setModifiedInput(row, chartId, true);
        } else {
            this.modifiedCharts.delete(chartId);
            row.classList.remove('table-warning');
            this.setModifiedInput(row, chartId, false);
        }

        this.updateCounter();
    }

    /**
     * Parse the original data from the row's data attribute
     */
    parseOriginalData(row) {
        try {
            return JSON.parse(row.dataset.original);
        } catch (e) {
            console.warn('Failed to parse original data', e);
            return { libs: {}, platform: null, hasProof: false };
        }
    }

    /**
     * Get current form values from a row
     */
    getCurrentValues(row) {
        const chartId = row.dataset.chartId;
        const values = {
            libs: {},
            platform: null,
            hasProof: false
        };

        // Get all score inputs
        const inputs = row.querySelectorAll('input.score-input');
        inputs.forEach(input => {
            const libId = input.dataset.libId;
            const index = parseInt(input.dataset.index, 10);

            if (!values.libs[libId]) {
                values.libs[libId] = {};
            }
            values.libs[libId][index] = input.value || '';
        });

        // Get platform select
        const platformSelect = row.querySelector(`select[name="scores[${chartId}][platform]"]`);
        if (platformSelect) {
            values.platform = platformSelect.value ? parseInt(platformSelect.value, 10) : null;
        }

        // Get hasProof checkbox
        const hasProofCheckbox = row.querySelector(`input[name="scores[${chartId}][hasProof]"]`);
        if (hasProofCheckbox) {
            values.hasProof = hasProofCheckbox.checked;
        }

        return values;
    }

    /**
     * Compare original and current values to detect changes
     */
    hasChanges(original, current) {
        // Check platform
        if ((original.platform || null) !== (current.platform || null)) {
            return true;
        }

        // Check hasProof
        if ((original.hasProof || false) !== (current.hasProof || false)) {
            return true;
        }

        // Check libs values
        const originalLibs = original.libs || {};
        const currentLibs = current.libs || {};

        // Get all lib IDs
        const libIds = new Set([
            ...Object.keys(originalLibs),
            ...Object.keys(currentLibs)
        ]);

        for (const libId of libIds) {
            const origLib = originalLibs[libId] || {};
            const currLib = currentLibs[libId] || {};

            // Get all indices
            const indices = new Set([
                ...Object.keys(origLib).map(k => parseInt(k, 10)),
                ...Object.keys(currLib).map(k => parseInt(k, 10))
            ]);

            for (const index of indices) {
                const origValue = (origLib[index] || '').toString().trim();
                const currValue = (currLib[index] || '').toString().trim();

                if (origValue !== currValue) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add or remove hidden input to mark row as modified
     */
    setModifiedInput(row, chartId, isModified) {
        const inputName = `scores[${chartId}][modified]`;
        let input = row.querySelector(`input[name="${inputName}"]`);

        if (isModified) {
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = inputName;
                input.value = '1';
                row.appendChild(input);
            }
        } else {
            if (input) {
                input.remove();
            }
        }
    }

    /**
     * Update the counter badge
     */
    updateCounter() {
        const count = this.modifiedCharts.size;

        if (this.hasCounterTarget) {
            this.counterTarget.textContent = `${count} modified`;
            this.counterTarget.classList.toggle('bg-primary', count === 0);
            this.counterTarget.classList.toggle('bg-warning', count > 0);
        }

        if (this.hasSummaryTarget) {
            if (count > 0) {
                this.summaryTarget.textContent = `${count} chart(s) will be updated`;
                this.summaryTarget.classList.add('text-warning');
            } else {
                this.summaryTarget.textContent = '';
                this.summaryTarget.classList.remove('text-warning');
            }
        }
    }
}
