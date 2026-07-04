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
        initialQuery: { type: String, default: '' },
    };

    connect() {
        this._debounceTimer = null;

        if (this.initialQueryValue.length >= 2) {
            this._search(this.initialQueryValue);
        }
    }

    disconnect() {
        if (this._debounceTimer) clearTimeout(this._debounceTimer);
    }

    onInput() {
        if (this._debounceTimer) clearTimeout(this._debounceTimer);

        const query = this.inputTarget.value.trim();

        if (query.length < 2) {
            this.resultsTarget.innerHTML = `<p class="text-muted text-center mt-4">${this.minCharsLabelValue}</p>`;
            return;
        }

        this._debounceTimer = setTimeout(() => this._search(query), 300);
    }

    async _search(query) {
        this.resultsTarget.innerHTML = '<div class="text-center mt-5"><div class="spinner-border" role="status"></div></div>';

        const locale = this.localeValue;

        const [games, players, teams] = await Promise.all([
            this._fetch(`${this.gamesUrlValue}?query=${encodeURIComponent(query)}&locale=${locale}`),
            this._fetch(`${this.playersUrlValue}?query=${encodeURIComponent(query)}`),
            this._fetch(`${this.teamsUrlValue}?query=${encodeURIComponent(query)}`),
        ]);

        if (!games.length && !players.length && !teams.length) {
            this.resultsTarget.innerHTML = `<p class="text-muted text-center mt-4">${this.noResultsLabelValue}</p>`;
            return;
        }

        const firstActiveTab = games.length ? 'games' : (players.length ? 'players' : 'teams');

        const tabs = `
            <ul class="nav nav-tabs mt-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link ${firstActiveTab === 'games' ? 'active' : ''}" data-bs-toggle="tab" data-bs-target="#search-tab-games" type="button" role="tab">
                        <i class="bi bi-controller me-1"></i>${this.gameLabelValue}
                        ${games.length ? `<span class="badge bg-secondary ms-1">${games.length}</span>` : ''}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link ${firstActiveTab === 'players' ? 'active' : ''}" data-bs-toggle="tab" data-bs-target="#search-tab-players" type="button" role="tab">
                        <i class="bi bi-person me-1"></i>${this.playerLabelValue}
                        ${players.length ? `<span class="badge bg-secondary ms-1">${players.length}</span>` : ''}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link ${firstActiveTab === 'teams' ? 'active' : ''}" data-bs-toggle="tab" data-bs-target="#search-tab-teams" type="button" role="tab">
                        <i class="bi bi-people me-1"></i>${this.teamLabelValue}
                        ${teams.length ? `<span class="badge bg-secondary ms-1">${teams.length}</span>` : ''}
                    </button>
                </li>
            </ul>
            <div class="tab-content pt-3">
                <div class="tab-pane fade ${firstActiveTab === 'games' ? 'show active' : ''}" id="search-tab-games" role="tabpanel">
                    ${games.length ? this._renderGrid(games, locale, 'game') : `<p class="text-muted">${this.noResultsLabelValue}</p>`}
                </div>
                <div class="tab-pane fade ${firstActiveTab === 'players' ? 'show active' : ''}" id="search-tab-players" role="tabpanel">
                    ${players.length ? this._renderGrid(players, locale, 'player') : `<p class="text-muted">${this.noResultsLabelValue}</p>`}
                </div>
                <div class="tab-pane fade ${firstActiveTab === 'teams' ? 'show active' : ''}" id="search-tab-teams" role="tabpanel">
                    ${teams.length ? this._renderGrid(teams, locale, 'team') : `<p class="text-muted">${this.noResultsLabelValue}</p>`}
                </div>
            </div>`;

        this.resultsTarget.innerHTML = tabs;
    }

    _renderGrid(items, locale, type) {
        const cards = items.map(item => {
            const url = `/${locale}/${type}/${item.id}-${item.slug}`;
            return this._renderCard(item, url, type);
        }).join('');
        return `<div class="row g-3">${cards}</div>`;
    }

    _renderCard(item, url, type) {
        if (type === 'game') {
            const platformBadges = (item.platforms || []).slice(0, 5).map(p =>
                `<span class="platform-badge platform-badge--${p.id}" title="${this._escapeHtml(p.name)}">${this._escapeHtml(p.name)}</span>`
            ).join('');

            return `<div class="col-6 col-md-4 col-lg-3">
                <a href="${url}" class="card h-100 text-decoration-none search-result-card">
                    <img src="${this._escapeHtml(item.picture)}" alt="${this._escapeHtml(item.text)}" class="card-img-top search-card-img">
                    <div class="card-body p-2">
                        <p class="card-title small fw-semibold mb-1 text-truncate">${this._escapeHtml(item.text)}</p>
                        ${platformBadges ? `<div class="d-flex flex-wrap gap-1">${platformBadges}</div>` : ''}
                    </div>
                </a>
            </div>`;
        }

        const img = type === 'player'
            ? `<img src="${this._escapeHtml(item.avatar)}" alt="" class="rounded-circle mb-2 search-avatar" width="64" height="64">`
            : `<img src="${this._escapeHtml(item.logo)}" alt="" class="rounded mb-2 search-avatar" width="64" height="64">`;

        return `<div class="col-6 col-md-4 col-lg-3">
            <a href="${url}" class="card h-100 text-decoration-none text-center search-result-card">
                <div class="card-body p-3">
                    ${img}
                    <p class="card-title small fw-semibold mb-0">${this._escapeHtml(item.text)}</p>
                </div>
            </a>
        </div>`;
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
