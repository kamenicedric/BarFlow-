<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Nouveau don</div>
            <div class="card-body">
                <form method="POST" action="<?= url('/dons') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label">Produit</label>
                        <select class="form-select" name="produit_id" required>
                            <option value="">Selectionner...</option>
                            <?php foreach ($produits as $produit): ?>
                                <option value="<?= (int) $produit['id'] ?>"><?= e($produit['nom']) ?> (Stock: <?= e((string) $produit['stock']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Quantite</label>
                        <input class="form-control" type="number" step="0.01" name="quantite" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Raison</label>
                        <textarea class="form-control" name="raison" rows="2" required></textarea>
                    </div>
                    <button class="btn btn-warning w-100">Enregistrer don</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Historique dons</div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead><tr><th>Date</th><th>Produit</th><th>Qte</th><th>Valeur</th><th>Autorise par</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($dons as $don): ?>
                        <tr>
                            <td><?= e($don['date_don']) ?></td>
                            <td><?= e($don['produit_nom']) ?></td>
                            <td><?= e((string) $don['quantite']) ?></td>
                            <td><?= number_format((float) $don['valeur_totale'], 0, ',', ' ') ?></td>
                            <td><?= e((string) ($don['autorise_nom'] ?? '-')) ?></td>
                            <td>
                                <form method="POST" action="<?= url('/dons/delete') ?>" onsubmit="return confirm('Supprimer ce don ?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $don['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Soft delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
