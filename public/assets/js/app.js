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

    initPasswordToggles();
    initStockAlerts();
});

// Gestion centralisee de la visibilite des mots de passe.
// Tout bouton portant la classe .js-toggle-password avec data-target="idInput".
function initPasswordToggles() {
    document.querySelectorAll('.js-toggle-password').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.innerHTML = isHidden
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';
        });
    });
}

// Alertes stock temps reel affichees dans la barre du haut.
function initStockAlerts() {
    const badge = document.getElementById('stockAlertBadge');
    const menu = document.getElementById('stockAlertMenu');
    if (!badge || !menu) return;

    const refresh = async () => {
        try {
            const response = await fetch(BarFlow.url('/api/stock/alerts'));
            if (!response.ok) return;
            const payload = await response.json();
            const items = payload.data || payload.alerts || [];

            if (!items.length) {
                badge.classList.add('d-none');
                menu.innerHTML = '<li><span class="dropdown-item-text text-muted">Aucune alerte stock</span></li>';
                return;
            }

            badge.textContent = items.length;
            badge.classList.remove('d-none');
            menu.innerHTML = items.map((item) => {
                const nom = item.nom || 'Produit';
                const stock = item.stock ?? '?';
                const seuil = item.stock_critique ?? '?';
                return `<li><span class="dropdown-item-text"><i class="bi bi-exclamation-triangle text-warning"></i> <strong>${nom}</strong> — stock ${stock} / seuil ${seuil}</span></li>`;
            }).join('');
        } catch (error) {
            // Silencieux : l'utilisateur peut ne pas etre sur une page protegee.
        }
    };

    refresh();
    setInterval(refresh, 60000);
}
