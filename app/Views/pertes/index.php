<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Nouvelle perte</div>
            <div class="card-body">
                <form method="POST" action="<?= url('/pertes') ?>">
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
                        <label class="form-label">Type perte</label>
                        <select class="form-select" name="type_perte" required>
                            <option value="bouteille_cassee_pleine">Bouteille cassee pleine</option>
                            <option value="bouteille_cassee_vide">Bouteille cassee vide</option>
                            <option value="vol">Vol</option>
                            <option value="erreur_comptage">Erreur comptage</option>
                            <option value="perte_inconnue">Perte inconnue</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Quantite</label>
                        <input class="form-control" type="number" step="0.01" name="quantite" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Justification</label>
                        <textarea class="form-control" name="justification" rows="2"></textarea>
                    </div>
                    <button class="btn btn-danger w-100">Enregistrer perte</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Historique pertes</div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead><tr><th>Date</th><th>Produit</th><th>Type</th><th>Qte</th><th>Valeur</th><th>Responsable</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($pertes as $perte): ?>
                        <tr>
                            <td><?= e($perte['date_perte']) ?></td>
                            <td><?= e($perte['produit_nom']) ?></td>
                            <td><?= e($perte['type_perte']) ?></td>
                            <td><?= e((string) $perte['quantite']) ?></td>
                            <td><?= number_format((float) $perte['valeur_totale'], 0, ',', ' ') ?></td>
                            <td><?= e((string) ($perte['responsable_nom'] ?? '-')) ?></td>
                            <td>
                                <form method="POST" action="<?= url('/pertes/delete') ?>" onsubmit="return confirm('Supprimer cette perte ?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $perte['id'] ?>">
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
