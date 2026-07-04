import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'results', 'dropdown'];
    static values = {
        gamesUrl: String,
        playersUrl: String,
        teamsUrl: String,
        searchUrl: String,
        locale: { type: String, default: 'en' },
        gameLabel: { type: String, default: 'Games' },
        playerLabel: { type: String, default: 'Players' },
        teamLabel: { type: String, default: 'Teams' },
        noResultsLabel: { type: String, default: 'No results found' },
        seeAllLabel: { type: String, default: 'See all results for' },
        maxResults: { type: Number, default: 5 },
    };

    connect() {
        this._debounceTimer = null;
        this._selectedIndex = -1;
        this._allItems = [];
        this._onDocumentClick = this._handleDocumentClick.bind(this);
        document.addEventListener('click', this._onDocumentClick);
    }

    disconnect() {
        if (this._debounceTimer) clearTimeout(this._debounceTimer);
        document.removeEventListener('click', this._onDocumentClick);
    }

    onInput() {
        if (this._debounceTimer) clearTimeout(this._debounceTimer);

        const query = this.inputTarget.value.trim();

        if (query.length < 2) {
            this._hide();
            return;
        }

        this._debounceTimer = setTimeout(() => this._search(query), 300);
    }

    onFocus() {
        if (this.resultsTarget.innerHTML.trim() && this.inputTarget.value.trim().length >= 2) {
            this._show();
        }
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
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (this._selectedIndex >= 0) {
                const items = this.resultsTarget.querySelectorAll('.search-result-item');
                const selected = items[this._selectedIndex];
                if (selected) {
                    const link = selected.querySelector('a');
                    if (link) window.location.href = link.href;
                }
            } else if (this.searchUrlValue) {
                const query = this.inputTarget.value.trim();
                if (query.length >= 2) {
                    window.location.href = `${this.searchUrlValue}?q=${encodeURIComponent(query)}`;
                }
            }
        } else if (event.key === 'Escape') {
            this._hide();
        }
    }

    async _search(query) {
        this.resultsTarget.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm" role="status"></div></div>';
        this._show();

        const locale = this.localeValue;
        const max = this.maxResultsValue;

        const [games, players, teams] = await Promise.all([
            this._fetch(`${this.gamesUrlValue}?query=${encodeURIComponent(query)}&locale=${locale}`),
            this._fetch(`${this.playersUrlValue}?query=${encodeURIComponent(query)}`),
            this._fetch(`${this.teamsUrlValue}?query=${encodeURIComponent(query)}`),
        ]);

        this._selectedIndex = -1;
        this._allItems = [];

        const gamesSlice = games.slice(0, max);
        const playersSlice = players.slice(0, max);
        const teamsSlice = teams.slice(0, max);

        let html = '';
        html += this._renderGames(this.gameLabelValue, gamesSlice, locale);
        html += this._renderPlayers(this.playerLabelValue, playersSlice, locale);
        html += this._renderTeams(this.teamLabelValue, teamsSlice, locale);

        if (!gamesSlice.length && !playersSlice.length && !teamsSlice.length) {
            html = `<p class="text-muted text-center py-2 mb-0">${this.noResultsLabelValue}</p>`;
        } else if (this.searchUrlValue) {
            const seeAllUrl = `${this.searchUrlValue}?q=${encodeURIComponent(query)}`;
            html += `<div class="border-top pt-2 mt-2 text-center">
                <a href="${seeAllUrl}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>${this.seeAllLabelValue} "${this._escapeHtml(query)}"
                </a>
            </div>`;
        }

        this.resultsTarget.innerHTML = html;
    }

    _renderGames(label, items, locale) {
        if (!items.length) return '';

        let html = `<p class="text-muted small fw-semibold mb-1 mt-2"><i class="bi bi-controller me-1"></i>${label}</p>`;

        items.forEach(item => {
            const url = `/${locale}/game/${item.id}-${item.slug}`;
            const idx = this._allItems.length;
            this._allItems.push(item);

            const platformIcons = (item.platforms || []).slice(0, 4).map(p =>
                `<span class="platform-badge platform-badge--${p.id}" title="${this._escapeHtml(p.name)}">${this._escapeHtml(p.name)}</span>`
            ).join('');

            html += `<div class="search-result-item" data-index="${idx}">
                <a href="${url}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-1 px-2 border-0">
                    <img src="${this._escapeHtml(item.picture)}" alt="" class="search-thumb rounded" width="36" height="36">
                    <div class="flex-grow-1 min-width-0">
                        <div class="small fw-semibold text-truncate">${this._escapeHtml(item.text)}</div>
                        ${platformIcons ? `<div class="d-flex gap-1 mt-1">${platformIcons}</div>` : ''}
                    </div>
                </a>
            </div>`;
        });

        return html;
    }

    _renderPlayers(label, items, locale) {
        if (!items.length) return '';

        let html = `<p class="text-muted small fw-semibold mb-1 mt-2"><i class="bi bi-person me-1"></i>${label}</p>`;

        items.forEach(item => {
            const url = `/${locale}/player/${item.id}-${item.slug}`;
            const idx = this._allItems.length;
            this._allItems.push(item);

            html += `<div class="search-result-item" data-index="${idx}">
                <a href="${url}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-1 px-2 border-0">
                    <img src="${this._escapeHtml(item.avatar)}" alt="" class="search-thumb rounded-circle" width="36" height="36">
                    <span class="small fw-semibold">${this._escapeHtml(item.text)}</span>
                </a>
            </div>`;
        });

        return html;
    }

    _renderTeams(label, items, locale) {
        if (!items.length) return '';

        let html = `<p class="text-muted small fw-semibold mb-1 mt-2"><i class="bi bi-people me-1"></i>${label}</p>`;

        items.forEach(item => {
            const url = `/${locale}/team/${item.id}-${item.slug}`;
            const idx = this._allItems.length;
            this._allItems.push(item);

            html += `<div class="search-result-item" data-index="${idx}">
                <a href="${url}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-1 px-2 border-0">
                    <img src="${this._escapeHtml(item.logo)}" alt="" class="search-thumb rounded" width="36" height="36">
                    <span class="small fw-semibold">${this._escapeHtml(item.text)}</span>
                </a>
            </div>`;
        });

        return html;
    }

    _show() {
        if (this.hasDropdownTarget) {
            this.dropdownTarget.classList.remove('d-none');
        }
    }

    _hide() {
        this._selectedIndex = -1;
        if (this.hasDropdownTarget) {
            this.dropdownTarget.classList.add('d-none');
        }
    }

    _handleDocumentClick(event) {
        if (!this.element.contains(event.target)) {
            this._hide();
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
        div.textContent = text ?? '';
        return div.innerHTML;
    }
}
