<div class="card auth-card border-0 shadow-lg">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4">
            <span class="auth-badge"><i class="bi bi-key"></i> Recuperation</span>
            <h4 class="mt-3 mb-1">Mot de passe oublie</h4>
            <p class="text-muted mb-0">Genere un lien securise a usage unique (valable 15 min).</p>
        </div>

        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger border-0"><?= e($msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($requested)): ?>
            <?php if (!empty($resetLink)): ?>
                <div class="alert alert-success border-0">
                    <p class="mb-2 fw-semibold">Lien de reinitialisation genere :</p>
                    <a href="<?= e($resetLink) ?>" class="d-block small text-break"><?= e($resetLink) ?></a>
                    <p class="small text-muted mt-2 mb-0">Ce lien expire dans 15 minutes et ne fonctionne qu'une seule fois.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-info border-0">
                    Si ce compte existe, un lien de reinitialisation a ete genere. Contacte un administrateur si besoin.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST" action="<?= url('/forgot-password') ?>">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Nom utilisateur</label>
                    <input type="text" name="username" class="form-control form-control-lg" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Generer le lien</button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a class="small text-decoration-none fw-semibold" href="<?= url('/login') ?>">Retour a la connexion</a>
        </div>
    </div>
</div>
