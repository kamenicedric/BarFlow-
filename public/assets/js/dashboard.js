(async function dashboardInit() {
    const response = await fetch(BarFlow.url('/api/dashboard/stats'));
    if (!response.ok) {
        return;
    }

    const payload = await response.json();
    const stats = payload.stats || {};

    document.getElementById('v-jour').textContent = BarFlow.formatMoney(stats.ventes_jour || 0) + ' FCFA';
    document.getElementById('nb-ventes').textContent = stats.nombre_ventes || 0;
    document.getElementById('dep-jour').textContent = BarFlow.formatMoney(stats.depenses_jour || 0) + ' FCFA';
    document.getElementById('st-critique').textContent = stats.stock_critique || 0;

    const labels = (payload.monthly || []).map(item => item.periode);
    const values = (payload.monthly || []).map(item => Number(item.total));

    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Ventes',
                    data: values,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.12)',
                    fill: true,
                    tension: 0.3
                }]
            }
        });
    }
})();
