<?php $s = $settings ?? []; ?>
<?= ui_page_header('Parametres de l\'application', 'Personnalise ton bar, ta devise et tes seuils') ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Configuration generale</div>
            <div class="card-body">
                <form method="POST" action="<?= url('/settings') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nom du bar</label><input class="form-control" name="nom_bar" value="<?= e((string) ($s['nom_bar'] ?? 'BarFlow')) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Devise</label><input class="form-control" name="devise" value="<?= e((string) ($s['devise'] ?? 'FCFA')) ?>"></div>
                        <div class="col-md-3"><label class="form-label">TVA (%)</label><input class="form-control" type="number" step="0.01" name="taux_tva" value="<?= e((string) ($s['taux_tva'] ?? 0)) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Seuil stock critique global</label><input class="form-control" type="number" step="0.01" name="seuil_stock_critique_global" value="<?= e((string) ($s['seuil_stock_critique_global'] ?? 5)) ?>"></div>
                        <div class="col-md-4">
                            <label class="form-label">Theme par defaut</label>
                            <select class="form-select" name="theme">
                                <option value="light" <?= ($s['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Clair</option>
                                <option value="dark" <?= ($s['theme'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Sombre</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="sauvegarde_auto" id="sauvegarde_auto" <?= (int) ($s['sauvegarde_auto'] ?? 1) === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sauvegarde_auto">Sauvegarde auto</label>
                            </div>
                        </div>
                        <div class="col-md-6"><label class="form-label">Logo (image)</label><input class="form-control" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg"></div>
                    </div>
                    <button class="btn btn-primary mt-3"><i class="bi bi-save"></i> Enregistrer les parametres</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Logo actuel</div>
            <div class="card-body text-center">
                <?php if (!empty($s['logo_path'])): ?>
                    <img src="<?= url($s['logo_path']) ?>" alt="Logo" class="img-fluid" style="max-height: 160px;">
                <?php else: ?>
                    <p class="text-muted mb-0">Aucun logo defini</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
