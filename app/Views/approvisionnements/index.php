<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Nouvel approvisionnement</div>
            <div class="card-body">
                <form method="POST" action="<?= url('/approvisionnements') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label">Produit</label>
                        <select class="form-select" name="produit_id" required>
                            <option value="">Selectionner...</option>
                            <?php foreach ($produits as $produit): ?>
                                <option value="<?= (int) $produit['id'] ?>"><?= e($produit['nom']) ?> (Stock: <?= e((string) $produit['stock']) ?><?= !empty($produit['unite_achat']) ? ' | achat: ' . e((string) $produit['unite_achat']) . ' x' . e((string) $produit['facteur_conversion']) : '' ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label">Fournisseur</label><input class="form-control" name="fournisseur" required></div>
                    <div class="mb-2"><label class="form-label">Quantite (en unite d'achat)</label><input class="form-control" type="number" step="0.01" name="quantite" required></div>
                    <div class="mb-2"><label class="form-label">Prix total</label><input class="form-control" type="number" step="0.01" name="prix_total" required></div>
                    <div class="mb-2"><label class="form-label">Facture (PDF/image, optionnel)</label><input class="form-control" type="file" name="facture" accept=".pdf,.jpg,.jpeg,.png,.webp"></div>
                    <button class="btn btn-success w-100">Enregistrer approvisionnement</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Historique approvisionnements</div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead><tr><th>Date</th><th>Produit</th><th>Fournisseur</th><th>Qte</th><th>Prix total</th><th>Facture</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($approvisionnements as $appro): ?>
                        <tr>
                            <td><?= e($appro['date_approvisionnement']) ?></td>
                            <td><?= e($appro['produit_nom']) ?></td>
                            <td><?= e((string) ($appro['fournisseur_nom'] ?? '-')) ?></td>
                            <td><?= e((string) $appro['quantite']) ?></td>
                            <td><?= number_format((float) $appro['prix_total'], 0, ',', ' ') ?></td>
                            <td>
                                <?php if (!empty($appro['facture_path'])): ?>
                                    <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= url($appro['facture_path']) ?>"><i class="bi bi-file-earmark-text"></i></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="<?= url('/approvisionnements/delete') ?>" onsubmit="return confirm('Supprimer cet approvisionnement ?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $appro['id'] ?>">
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
