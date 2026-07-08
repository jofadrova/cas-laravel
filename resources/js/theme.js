const STORAGE_KEY = 'scas-theme';

function applyTheme(theme) {

    // Tema propio del SCAS
    document.documentElement.setAttribute('data-theme', theme);

    // Tema nativo de Bootstrap
    document.documentElement.setAttribute('data-bs-theme', theme);

    localStorage.setItem(STORAGE_KEY, theme);

    updateIcon(theme);

}

function updateIcon(theme) {

    const icon = document.querySelector('#themeToggle i');

    if (!icon) return;

    icon.className = theme === 'dark'
        ? 'bi bi-sun-fill'
        : 'bi bi-moon-stars-fill';

}

function loadTheme() {

    const saved = localStorage.getItem(STORAGE_KEY);

    applyTheme(saved ?? 'light');

}

document.addEventListener('DOMContentLoaded', () => {

    loadTheme();

    const button = document.getElementById('themeToggle');

    if (!button) return;

    button.addEventListener('click', () => {

        const current = document.documentElement.getAttribute('data-bs-theme');
        applyTheme(current === 'dark'
            ? 'light'
            : 'dark');
    });
});