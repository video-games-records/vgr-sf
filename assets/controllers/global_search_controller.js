import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'results'];
    static values = {
        gamesUrl: String,
        playersUrl: String,
        teamsUrl: String,
        locale: { type: String, default: 'en' },
        gameLabel: { type: String, default: 'Games' },
        playerLabel: { type: String, default: 'Players' },
        teamLabel: { type: String, default: 'Teams' },
        noResultsLabel: { type: String, default: 'No results found' },
        minCharsLabel: { type: String, default: 'Type at least 2 characters to search' },
    };

    connect() {
        this._debounceTimer = null;
        this._selectedIndex = -1;
        this._allItems = [];
    }

    disconnect() {
        if (this._debounceTimer) {
            clearTimeout(this._debounceTimer);
        }
    }

    onInput() {
        if (this._debounceTimer) {
            clearTimeout(this._debounceTimer);
        }

        const query = this.inputTarget.value.trim();

        if (query.length < 2) {
            this.resultsTarget.innerHTML = `<p class="text-muted text-center mt-3">${this.minCharsLabelValue}</p>`;
            this._selectedIndex = -1;
            this._allItems = [];
            return;
        }

        this._debounceTimer = setTimeout(() => this._search(query), 300);
    }

    onKeydown(event) {
        if (!this._allItems.length) return;

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this._selectedIndex = Math.min(this._selectedIndex + 1, this._allItems.length - 1);
            this._highlightItem();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this._selectedIndex = Math.max(this._selectedIndex - 1, 0);
            this._highlightItem();
        } else if (event.key === 'Enter' && this._selectedIndex >= 0) {
            event.preventDefault();
            const items = this.resultsTarget.querySelectorAll('.search-result-item');
            const selected = items[this._selectedIndex];
            if (selected) {
                const link = selected.querySelector('a');
                if (link) window.location.href = link.href;
            }
        }
    }

    async _search(query) {
        this.resultsTarget.innerHTML = '<div class="text-center mt-3"><div class="spinner-border spinner-border-sm" role="status"></div></div>';

        const locale = this.localeValue;

        const [games, players, teams] = await Promise.all([
            this._fetch(`${this.gamesUrlValue}?query=${encodeURIComponent(query)}&locale=${locale}`),
            this._fetch(`${this.playersUrlValue}?query=${encodeURIComponent(query)}`),
            this._fetch(`${this.teamsUrlValue}?query=${encodeURIComponent(query)}`),
        ]);

        this._selectedIndex = -1;
        this._allItems = [];

        let html = '';

        html += this._renderSection(this.gameLabelValue, 'bi-controller', games, 'game', locale);
        html += this._renderSection(this.playerLabelValue, 'bi-person', players, 'player', locale);
        html += this._renderSection(this.teamLabelValue, 'bi-people', teams, 'team', locale);

        if (!games.length && !players.length && !teams.length) {
            html = `<p class="text-muted text-center mt-3">${this.noResultsLabelValue}</p>`;
        }

        this.resultsTarget.innerHTML = html;
    }

    _renderSection(label, icon, items, type, locale) {
        if (!items.length) return '';

        let html = `<h6 class="mt-3 mb-2 text-muted"><i class="bi ${icon} me-1"></i>${label}</h6>`;
        html += '<div class="list-group list-group-flush">';

        items.forEach(item => {
            const url = this._buildUrl(type, item, locale);
            const idx = this._allItems.length;
            this._allItems.push(item);
            html += `<div class="search-result-item" data-index="${idx}">`;
            html += `<a href="${url}" class="list-group-item list-group-item-action">${this._escapeHtml(item.text)}</a>`;
            html += '</div>';
        });

        html += '</div>';
        return html;
    }

    _buildUrl(type, item, locale) {
        const base = `/${locale}`;
        switch (type) {
            case 'game':
                return `${base}/game/${item.id}-${item.slug}`;
            case 'player':
                return `${base}/player/${item.id}-${item.slug}`;
            case 'team':
                return `${base}/team/${item.id}-${item.slug}`;
        }
    }

    _highlightItem() {
        const items = this.resultsTarget.querySelectorAll('.search-result-item');
        items.forEach((el, i) => {
            const link = el.querySelector('a');
            if (i === this._selectedIndex) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    async _fetch(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) return [];
            return await response.json();
        } catch {
            return [];
        }
    }

    _escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
