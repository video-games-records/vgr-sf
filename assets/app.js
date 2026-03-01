// Import Bootstrap JS depuis vendor/twbs/bootstrap
import 'bootstrap';

// Import des styles SCSS
import './styles/app.scss';

// Import des thèmes VGR (après app.scss pour override Bootstrap)
import './styles/themes/themes.css';

// Import Stimulus
import './bootstrap.js';

console.log('This log comes from assets/app.js - welcome to Webpack Encore! 🎉');

// ============================================
// Theme Management
// ============================================

const DARK_THEMES = [
    'dark',
    'cyber', 'phantasy', 'godofwar', 'lastofus',
    'forza', 'halo', 'gears',
    'streetfighter', 'residentevil', 'monsterhunter',
    'darksouls', 'tekken', 'pacman',
    'burnout', 'doom',
];

function getStoredTheme() {
    return localStorage.getItem('theme');
}

function setStoredTheme(theme) {
    localStorage.setItem('theme', theme);
}

function getPreferredTheme() {
    const storedTheme = getStoredTheme();
    if (storedTheme) {
        return storedTheme;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);

    // Update the light/dark toggle icon based on whether the active theme is dark
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = DARK_THEMES.includes(theme) ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
    }

    // Sync active state in the theme picker if present
    document.querySelectorAll('[data-vgr-theme]').forEach(el => {
        el.classList.toggle('active', el.dataset.vgrTheme === theme);
    });
}

window.applyTheme = function(theme) {
    setTheme(theme);
    setStoredTheme(theme);
};

// Kept for backward compatibility (navbar toggle button)
window.toggleTheme = function() {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    window.applyTheme(newTheme);
};

// Apply theme immediately (before DOMContentLoaded to avoid flash)
setTheme(getPreferredTheme());

document.addEventListener('DOMContentLoaded', () => {
    setTheme(getPreferredTheme());
});

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!getStoredTheme()) {
        setTheme(e.matches ? 'dark' : 'light');
    }
});

// ============================================
// Ajax Modal Component
// ============================================

/**
 * Open the ajax modal and load content from URL
 * @param {string} url - URL to fetch content from
 */
window.openAjaxModal = async function(url) {
    const modal = document.getElementById('ajaxModal');
    if (!modal) return;

    const modalContent = modal.querySelector('.ajax-modal-content');

    // Show modal with loading spinner
    modalContent.innerHTML = '<div class="ajax-modal-body text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    try {
        const response = await fetch(url);
        const html = await response.text();
        modalContent.innerHTML = html;
    } catch (error) {
        console.error('Error loading modal content:', error);
        modalContent.innerHTML = '<div class="ajax-modal-header"><h5 class="ajax-modal-title">Error</h5><button type="button" class="ajax-modal-close" onclick="closeAjaxModal()">&times;</button></div><div class="ajax-modal-body text-center text-danger py-4"><i class="bi bi-exclamation-triangle"></i> An error occurred</div>';
    }
};

/**
 * Close the ajax modal
 */
window.closeAjaxModal = function() {
    const modal = document.getElementById('ajaxModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
};

// Close modal when clicking outside content or pressing Escape
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('ajaxModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeAjaxModal();
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAjaxModal();
        }
    });
});
