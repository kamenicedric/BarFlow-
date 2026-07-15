<div class="card auth-card border-0 shadow-lg">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4">
            <span class="auth-badge"><i class="bi bi-key"></i> Nouveau mot de passe</span>
            <h4 class="mt-3 mb-1">Reinitialisation</h4>
            <p class="text-muted mb-0">Choisis un nouveau mot de passe securise.</p>
        </div>

        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger border-0"><?= e($msg) ?></div>
        <?php endif; ?>

        <?php if (!($valid ?? false)): ?>
            <div class="alert alert-warning border-0">Ce lien de reinitialisation est invalide ou a expire.</div>
            <div class="text-center mt-3">
                <a class="small text-decoration-none fw-semibold" href="<?= url('/forgot-password') ?>">Demander un nouveau lien</a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= url('/reset-password') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e((string) $token) ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nouveau mot de passe</label>
                    <div class="input-group input-group-lg">
                        <input type="password" id="resetPassword" name="password" class="form-control" required minlength="6">
                        <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="resetPassword"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                    <div class="input-group input-group-lg">
                        <input type="password" id="resetPasswordConfirm" name="password_confirmation" class="form-control" required minlength="6">
                        <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="resetPasswordConfirm"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Valider le nouveau mot de passe</button>
            </form>
        <?php endif; ?>
    </div>
</div>
