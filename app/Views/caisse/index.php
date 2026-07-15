<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Ouverture caisse</div>
            <div class="card-body">
                <form method="POST" action="<?= url('/caisse/ouvrir') ?>">
                    <?= csrf_field() ?>
                    <label class="form-label">Montant initial</label>
                    <input class="form-control mb-2" name="montant_initial" type="number" step="0.01" required>
                    <button class="btn btn-primary" <?= $active ? 'disabled' : '' ?>>Ouvrir</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Fermeture caisse</div>
            <div class="card-body">
                <form method="POST" action="<?= url('/caisse/fermer') ?>">
                    <?= csrf_field() ?>
                    <label class="form-label">Montant reel</label>
                    <input class="form-control mb-2" name="montant_reel" type="number" step="0.01" required>
                    <button class="btn btn-danger" <?= !$active ? 'disabled' : '' ?>>Fermer</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($active): ?>
    <div class="alert alert-info mt-3">Caisse ouverte depuis le <?= e($active['date_ouverture']) ?>, montant initial: <?= number_format((float) $active['montant_initial'], 0, ',', ' ') ?> FCFA</div>
<?php endif; ?>
