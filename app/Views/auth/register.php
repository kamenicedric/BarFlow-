<div class="card auth-card border-0 shadow-lg">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4">
            <span class="auth-badge"><i class="bi bi-person-plus"></i> Nouveau compte</span>
            <h4 class="mt-3 mb-1">Creer un compte</h4>
            <p class="text-muted mb-0">Le compte cree sera associe au role serveuse.</p>
        </div>

        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger border-0"><?= e($msg) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/register') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nom complet</label>
                <input type="text" name="nom" class="form-control form-control-lg" value="<?= e(old('nom')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nom utilisateur</label>
                <input type="text" name="username" class="form-control form-control-lg" value="<?= e(old('username')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Mot de passe</label>
                <div class="input-group input-group-lg">
                    <input type="password" id="registerPassword" name="password" class="form-control" required>
                    <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="registerPassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                <div class="input-group input-group-lg">
                    <input type="password" id="registerPasswordConfirm" name="password_confirmation" class="form-control" required>
                    <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="registerPasswordConfirm">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Creer mon compte</button>
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
