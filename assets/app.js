// Import Bootstrap JS depuis vendor/twbs/bootstrap
import 'bootstrap';

// Import des styles SCSS
import './styles/app.scss';

// Import Stimulus
import './bootstrap.js';

console.log('This log comes from assets/app.js - welcome to Webpack Encore! 🎉');

// ============================================
// Theme Management (Light/Dark Mode)
// ============================================

/**
 * Get the current theme from localStorage or system preference
 * @returns {string} 'light' or 'dark'
 */
function getStoredTheme() {
    return localStorage.getItem('theme');
}

/**
 * Store theme preference in localStorage
 * @param {string} theme - 'light' or 'dark'
 */
function setStoredTheme(theme) {
    localStorage.setItem('theme', theme);
}

/**
 * Get preferred theme based on system settings
 * @returns {string} 'light' or 'dark'
 */
function getPreferredTheme() {
    const storedTheme = getStoredTheme();
    if (storedTheme) {
        return storedTheme;
    }

    // Detect system preference
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

/**
 * Apply the theme to the document
 * @param {string} theme - 'light' or 'dark'
 */
function setTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);

    // Update button icon if it exists
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
    }
}

/**
 * Toggle between light and dark theme
 */
window.toggleTheme = function() {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    setTheme(newTheme);
    setStoredTheme(newTheme);
};

// Apply theme on page load
document.addEventListener('DOMContentLoaded', () => {
    const preferredTheme = getPreferredTheme();
    setTheme(preferredTheme);
});

// Listen for system theme changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    // Only apply system preference if user hasn't manually set a theme
    if (!getStoredTheme()) {
        setTheme(e.matches ? 'dark' : 'light');
    }
});
