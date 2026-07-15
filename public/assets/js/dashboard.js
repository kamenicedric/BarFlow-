(async function dashboardInit() {
    const devise = ' ' + (document.querySelector('meta[name="barflow-devise"]')?.content || 'FCFA');

    const response = await fetch(BarFlow.url('/api/dashboard/stats'));
    if (!response.ok) {
        return;
    }

    const payload = await response.json();
    const stats = payload.stats || {};

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    };

    setText('v-jour', BarFlow.formatMoney(stats.ventes_jour || 0) + devise);
    setText('nb-ventes', stats.nombre_ventes || 0);
    setText('dep-jour', BarFlow.formatMoney(stats.depenses_jour || 0) + devise);
    setText('benef-jour', BarFlow.formatMoney(stats.benefice_jour || 0) + devise);
    setText('st-critique', stats.stock_critique || 0);

    // Graphique combine ventes + depenses
    const monthly = payload.monthly || [];
    const monthlyExpenses = payload.monthly_expenses || [];
    const periods = Array.from(new Set([
        ...monthly.map(i => i.periode),
        ...monthlyExpenses.map(i => i.periode)
    ])).sort();

    const salesMap = Object.fromEntries(monthly.map(i => [i.periode, Number(i.total)]));
    const expMap = Object.fromEntries(monthlyExpenses.map(i => [i.periode, Number(i.total)]));

    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: periods,
                datasets: [
                    {
                        label: 'Ventes',
                        data: periods.map(p => salesMap[p] || 0),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.12)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Depenses',
                        data: periods.map(p => expMap[p] || 0),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.10)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // Graphique top produits
    const topProduits = payload.top_produits || [];
    const topCtx = document.getElementById('topProduitsChart');
    if (topCtx && topProduits.length) {
        new Chart(topCtx, {
            type: 'doughnut',
            data: {
                labels: topProduits.map(p => p.nom),
                datasets: [{
                    data: topProduits.map(p => Number(p.quantite)),
                    backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#fd7e14', '#6f42c1', '#dc3545']
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }
})();
