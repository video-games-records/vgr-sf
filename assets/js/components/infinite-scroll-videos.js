/**
 * Infinite Scroll Videos Component
 * 
 * Usage:
 * <div id="videos-container" data-infinite-scroll data-url="/api/videos" data-locale="en">
 *   <!-- videos cards here -->
 * </div>
 * <button id="load-more-btn">Load more</button>
 */

class InfiniteScrollVideos {
    constructor(containerElement, loadMoreButton, options = {}) {
        this.container = containerElement;
        this.loadMoreBtn = loadMoreButton;
        this.options = {
            autoScroll: true,
            scrollThreshold: 200,
            ...options
        };
        
        this.currentPage = 1;
        this.isLoading = false;
        this.apiUrl = this.container.dataset.url || '/videos';
        this.locale = this.container.dataset.locale || 'en';
        
        this.init();
    }
    
    init() {
        if (!this.container || !this.loadMoreBtn) return;
        
        // Event listeners
        this.loadMoreBtn.addEventListener('click', () => this.loadMoreVideos());
        
        if (this.options.autoScroll) {
            window.addEventListener('scroll', () => this.handleScroll());
        }
    }
    
    handleScroll() {
        if (this.isLoading) return;
        
        const scrollPosition = window.innerHeight + window.pageYOffset;
        const documentHeight = document.documentElement.offsetHeight;
        
        if (scrollPosition >= documentHeight - this.options.scrollThreshold) {
            this.loadMoreVideos();
        }
    }
    
    async loadMoreVideos() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const nextPage = this.currentPage + 1;
            const url = new URL(this.apiUrl, window.location);
            url.searchParams.set('page', nextPage);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.videos && data.videos.length > 0) {
                data.videos.forEach(video => {
                    this.container.insertAdjacentHTML('beforeend', this.createVideoCard(video));
                });
                this.currentPage = nextPage;
                
                if (!data.has_more) {
                    this.loadMoreBtn.remove();
                }
            } else {
                this.loadMoreBtn.remove();
            }
        } catch (error) {
            console.error('Error loading videos:', error);
            this.showError();
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    createVideoCard(video) {
        const badgeColor = video.type === 'Youtube' ? 'danger' : 'primary';
        const playerInfo = video.player ? 
            `<small class="text-muted">
                <i class="bi bi-person-circle me-1"></i>
                ${this.escapeHtml(video.player.pseudo)}
            </small>` : '';
        const gameInfo = video.game ? 
            `<small class="text-muted ms-auto">
                <i class="bi bi-controller me-1"></i>
                ${this.escapeHtml(video.game.name)}
            </small>` : '';
        const thumbnail = video.thumbnail ? 
            `<img src="${this.escapeHtml(video.thumbnail)}" class="object-fit-cover" alt="${this.escapeHtml(video.title)}">` :
            `<div class="bg-light d-flex align-items-center justify-content-center">
                <i class="bi bi-play-circle fs-1 text-muted"></i>
            </div>`;
        const shortTitle = video.title.length > 50 ? video.title.substring(0, 50) + '...' : video.title;
        const videoUrl = this.buildVideoUrl(video.id, video.slug);
        
        return `
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card h-100 video-card">
                    <div class="position-relative">
                        <div class="ratio ratio-16x9">
                            ${thumbnail}
                        </div>
                        <span class="position-absolute top-0 end-0 m-2 badge bg-${badgeColor}">
                            ${this.escapeHtml(video.type)}
                        </span>
                        <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark">
                            ${video.viewCount} views
                        </span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${this.escapeHtml(shortTitle)}</h6>
                        <div class="d-flex align-items-center mb-2">
                            ${playerInfo}
                            ${gameInfo}
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-muted">
                                <i class="bi bi-heart me-1"></i>${video.likeCount}
                            </small>
                            <small class="text-muted">${this.escapeHtml(video.createdAt)}</small>
                        </div>
                        <a href="${videoUrl}" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-play-fill me-1"></i>Watch
                        </a>
                    </div>
                </div>
            </div>
        `;
    }
    
    buildVideoUrl(videoId, videoSlug) {
        // Détection automatique du chemin basé sur l'URL actuelle
        const pathParts = window.location.pathname.split('/').filter(part => part);
        
        // Si on est sur une page de locale (/en/, /fr/, etc.)
        if (pathParts.length > 0 && pathParts[0].length === 2) {
            return `/${pathParts[0]}/video/${videoId}/${videoSlug}`;
        }
        
        // Sinon URL sans locale
        return `/video/${videoId}/${videoSlug}`;
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, (m) => map[m]);
    }
    
    showLoading() {
        const loadText = this.loadMoreBtn.querySelector('.load-text');
        const loadingText = this.loadMoreBtn.querySelector('.loading-text');
        
        if (loadText) loadText.classList.add('d-none');
        if (loadingText) loadingText.classList.remove('d-none');
        this.loadMoreBtn.disabled = true;
    }
    
    hideLoading() {
        const loadText = this.loadMoreBtn.querySelector('.load-text');
        const loadingText = this.loadMoreBtn.querySelector('.loading-text');
        
        if (loadText) loadText.classList.remove('d-none');
        if (loadingText) loadingText.classList.add('d-none');
        this.loadMoreBtn.disabled = false;
    }
    
    showError() {
        // Optionnel: afficher un message d'erreur
        const errorMsg = document.createElement('div');
        errorMsg.className = 'alert alert-warning mt-3';
        errorMsg.innerHTML = 'Error loading more videos. Please try again.';
        this.loadMoreBtn.parentNode.insertBefore(errorMsg, this.loadMoreBtn);
        
        setTimeout(() => errorMsg.remove(), 5000);
    }
    
    static autoInit() {
        document.addEventListener('DOMContentLoaded', () => {
            const containers = document.querySelectorAll('[data-infinite-scroll]');
            
            containers.forEach(container => {
                // Chercher le bouton load-more dans plusieurs contextes
                let loadMoreBtn = null;
                
                // 1. Dans le conteneur parent direct
                loadMoreBtn = container.parentNode.querySelector('[data-load-more]');
                
                // 2. Dans la section videos complète (chercher vers le haut)
                if (!loadMoreBtn) {
                    let videosSection = container.closest('.videos-section');
                    if (videosSection) {
                        loadMoreBtn = videosSection.querySelector('[data-load-more]');
                    }
                }
                
                // 3. Dans tout le document comme fallback
                if (!loadMoreBtn) {
                    loadMoreBtn = document.querySelector('[data-load-more]');
                }
                
                if (loadMoreBtn) {
                    new InfiniteScrollVideos(container, loadMoreBtn);
                }
            });
        });
    }
}

// Auto-initialization
InfiniteScrollVideos.autoInit();

// Export pour utilisation manuelle
window.InfiniteScrollVideos = InfiniteScrollVideos;