<div class="card auth-card border-0 shadow-lg">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4">
            <span class="auth-badge"><i class="bi bi-shield-lock"></i> Acces securise</span>
            <h4 class="mt-3 mb-1">Connexion BarFlow</h4>
            <p class="text-muted mb-0">Connecte-toi pour gerer ton bar en toute securite.</p>
        </div>
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger border-0"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success border-0"><?= e($msg) ?></div>
        <?php endif; ?>
        <form method="POST" action="<?= url('/login') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nom utilisateur</label>
                <input type="text" name="username" class="form-control form-control-lg" value="<?= e(old('username')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Mot de passe</label>
                <div class="input-group input-group-lg">
                    <input type="password" name="password" id="loginPassword" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                <a href="<?= url('/forgot-password') ?>" class="small text-decoration-none">Mot de passe oublie ?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Se connecter</button>
        </form>
        <div class="text-center mt-3">
            <span class="text-muted small">Pas encore de compte ?</span>
            <a class="small text-decoration-none fw-semibold" href="<?= url('/register') ?>">Creer un compte</a>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleLoginPassword')?.addEventListener('click', function () {
    const input = document.getElementById('loginPassword');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    this.innerHTML = input.type === 'password'
        ? '<i class="bi bi-eye"></i>'
        : '<i class="bi bi-eye-slash"></i>';
});
</script>
