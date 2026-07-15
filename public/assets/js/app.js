window.BarFlow = {
    basePath: document.querySelector('meta[name="barflow-base"]')?.content || '',

    url(path) {
        const normalized = '/' + String(path || '').replace(/^\/+/, '');
        return this.basePath + (normalized === '/' ? '' : normalized);
    },

    formatMoney(value) {
        return new Intl.NumberFormat('fr-FR').format(Number(value || 0));
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const savedTheme = localStorage.getItem('barflow_theme') || 'light';
    root.setAttribute('data-theme', savedTheme);

    const links = document.querySelectorAll('.app-sidebar .nav-link');
    const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
    links.forEach((link) => {
        const href = (link.getAttribute('href') || '').replace(/\/+$/, '') || '/';
        if (href === currentPath) {
            link.classList.add('active');
        }
    });

    const sidebar = document.querySelector('.app-sidebar');
    const toggle = document.getElementById('sidebarToggle');
    if (sidebar && toggle) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const updateToggleLabel = () => {
            const isDark = root.getAttribute('data-theme') === 'dark';
            themeToggle.innerHTML = isDark
                ? '<i class="bi bi-sun"></i> Clair'
                : '<i class="bi bi-moon-stars"></i> Sombre';
        };

        updateToggleLabel();
        themeToggle.addEventListener('click', () => {
            const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', nextTheme);
            localStorage.setItem('barflow_theme', nextTheme);
            updateToggleLabel();
        });
    }
});
