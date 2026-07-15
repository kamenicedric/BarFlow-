<div class="card auth-card border-0 shadow-lg">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4">
            <span class="auth-badge"><i class="bi bi-key"></i> Recuperation</span>
            <h4 class="mt-3 mb-1">Mot de passe oublie</h4>
            <p class="text-muted mb-0">Saisis ton compte et defini un nouveau mot de passe.</p>
        </div>

        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger border-0"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success border-0"><?= e($msg) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/forgot-password') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nom utilisateur</label>
                <input type="text" name="username" class="form-control form-control-lg" value="<?= e(old('username')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nouveau mot de passe</label>
                <div class="input-group input-group-lg">
                    <input type="password" id="resetPassword" name="password" class="form-control" required>
                    <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="resetPassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Confirmer le nouveau mot de passe</label>
                <div class="input-group input-group-lg">
                    <input type="password" id="resetPasswordConfirm" name="password_confirmation" class="form-control" required>
                    <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="resetPasswordConfirm">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Reinitialiser le mot de passe</button>
        </form>

        <div class="text-center mt-3">
            <a class="small text-decoration-none fw-semibold" href="<?= url('/login') ?>">Retour a la connexion</a>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.js-toggle-password').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        button.innerHTML = input.type === 'password'
            ? '<i class="bi bi-eye"></i>'
            : '<i class="bi bi-eye-slash"></i>';
    });
});
</script>
