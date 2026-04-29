import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {
    static values = {
        placeholder: { type: String, default: 'Select tags...' },
        maxItems: { type: Number, default: null },
    };

    connect() {
        // Get all existing tags from the select options
        const existingOptions = {};
        const existingItems = [];

        // Parse current selected options
        this.element.querySelectorAll('option[selected]').forEach(option => {
            if (option.value) {
                existingOptions[option.value] = {
                    value: option.value,
                    text: option.textContent
                };
                existingItems.push(option.value);
            }
        });

        // Parse all available options
        this.element.querySelectorAll('option').forEach(option => {
            if (option.value) {
                existingOptions[option.value] = {
                    value: option.value,
                    text: option.textContent
                };
            }
        });

        this.tomSelect = new TomSelect(this.element, {
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            placeholder: this.placeholderValue,
            maxItems: this.maxItemsValue,
            delimiter: ',',
            persist: false,
            options: Object.values(existingOptions),
            items: existingItems,
            plugins: ['remove_button'],
            render: {
                option: (data, escape) => {
                    return `<div class="option">${escape(data.text)}</div>`;
                },
                item: (data, escape) => {
                    return `<div class="item">${escape(data.text)}</div>`;
                },
                no_results: () => {
                    return '<div class="no-results">No tags found matching your search.</div>';
                },
            },
        });
    }

    disconnect() {
        if (this.tomSelect) {
            this.tomSelect.destroy();
        }
    }
}