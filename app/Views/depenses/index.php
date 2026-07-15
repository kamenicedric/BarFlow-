<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Nouvelle depense</div>
            <div class="card-body">
                <form method="POST" action="<?= url('/depenses') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label">Montant</label>
                        <input class="form-control" type="number" step="0.01" name="montant" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <input class="form-control" name="description" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Categorie</label>
                        <select class="form-select" name="categorie" required>
                            <option value="electricite">Electricite</option>
                            <option value="eau">Eau</option>
                            <option value="salaire">Salaire</option>
                            <option value="reparation">Reparation</option>
                            <option value="divers">Divers</option>
                        </select>
                    </div>
                    <div class="mb-2"><input class="form-control" name="donneur_ordre" placeholder="Donneur d'ordre"></div>
                    <div class="mb-2"><input class="form-control" name="executant" placeholder="Executant"></div>
                    <div class="mb-2"><label class="form-label">Preuve (PDF/image, optionnel)</label><input class="form-control" type="file" name="preuve" accept=".pdf,.jpg,.jpeg,.png,.webp"></div>
                    <button class="btn btn-primary w-100">Enregistrer depense</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Historique depenses</div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead><tr><th>Date</th><th>Description</th><th>Categorie</th><th>Montant</th><th>Preuve</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($depenses as $depense): ?>
                        <tr>
                            <td><?= e($depense['date_depense']) ?></td>
                            <td><?= e($depense['description']) ?></td>
                            <td><?= e($depense['categorie']) ?></td>
                            <td><?= number_format((float) $depense['montant'], 0, ',', ' ') ?></td>
                            <td>
                                <?php if (!empty($depense['preuve_path'])): ?>
                                    <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= url($depense['preuve_path']) ?>"><i class="bi bi-file-earmark-text"></i></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="<?= url('/depenses/delete') ?>" onsubmit="return confirm('Supprimer cette depense ?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $depense['id'] ?>">
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
