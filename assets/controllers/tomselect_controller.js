import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {
    static values = {
        url: String,
        locale: { type: String, default: 'en' },
        placeholder: { type: String, default: 'Search...' },
        maxItems: { type: Number, default: null },
        items: { type: Array, default: [] },
    };

    connect() {
        // Pre-load items if provided
        const preloadedOptions = {};
        const preloadedItems = [];

        if (this.itemsValue && this.itemsValue.length > 0) {
            this.itemsValue.forEach(item => {
                preloadedOptions[item.id] = item;
                preloadedItems.push(item.id.toString());
            });
        }

        this.tomSelect = new TomSelect(this.element, {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            placeholder: this.placeholderValue,
            maxItems: this.maxItemsValue,
            delimiter: ',',
            persist: false,
            options: Object.values(preloadedOptions),
            items: preloadedItems,
            plugins: ['remove_button'],
            load: (query, callback) => {
                if (!query.length) return callback();

                const url = `${this.urlValue}?query=${encodeURIComponent(query)}&locale=${this.localeValue}`;

                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            render: {
                option: (data, escape) => {
                    return `<div class="option">${escape(data.text)}</div>`;
                },
                item: (data, escape) => {
                    return `<div class="item">${escape(data.text)}</div>`;
                },
                no_results: () => {
                    return '<div class="no-results">No results found</div>';
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
