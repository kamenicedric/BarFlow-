const searchInput = document.getElementById('searchProduit');
const tableBody = document.querySelector('#tableProduits tbody');

if (searchInput && tableBody) {
    searchInput.addEventListener('input', async (event) => {
        const q = encodeURIComponent(event.target.value || '');
        const response = await fetch(`${BarFlow.url('/api/produits/search')}?q=${q}`);
        if (!response.ok) {
            return;
        }

        const data = await response.json();
        tableBody.innerHTML = '';

        (data.data || []).forEach((p) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.id}</td>
                <td>${p.nom}</td>
                <td>${BarFlow.formatMoney(p.prix_vente)}</td>
                <td>${p.stock}</td>
                <td>${p.stock_critique}</td>
                <td>${p.unite}</td>
                <td>-</td>
            `;
            tableBody.appendChild(tr);
        });
    });
}
