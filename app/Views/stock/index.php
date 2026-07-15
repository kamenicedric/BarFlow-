<div class="card">
    <div class="card-header">Mouvements de stock</div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-striped">
            <thead><tr><th>Date</th><th>Produit</th><th>Type</th><th>Qte</th><th>Ancien</th><th>Nouveau</th><th>Utilisateur</th><th>Justification</th></tr></thead>
            <tbody>
            <?php foreach ($mouvements as $m): ?>
                <tr>
                    <td><?= e($m['date_mouvement']) ?></td>
                    <td><?= e($m['produit_nom']) ?></td>
                    <td><?= e($m['type_mouvement']) ?></td>
                    <td><?= e((string) $m['quantite']) ?></td>
                    <td><?= e((string) $m['ancien_stock']) ?></td>
                    <td><?= e((string) $m['nouveau_stock']) ?></td>
                    <td><?= e((string) ($m['utilisateur_nom'] ?? '-')) ?></td>
                    <td><?= e((string) $m['justification']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
