<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="barflow-base" content="<?= e(app_base_path()) ?>">
    <title>Connexion | BarFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('/assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="auth-body">
<div class="container-fluid min-vh-100">
    <div class="row min-vh-100">
        <div class="col-lg-6 d-none d-lg-flex auth-hero">
            <div>
                <h1 class="auth-hero-title">BarFlow</h1>
                <p class="auth-hero-subtitle">Pilote tes ventes, ton stock et ta caisse en temps reel sur tablette ou PC.</p>
                <ul class="auth-feature-list">
                    <li><i class="bi bi-check-circle-fill"></i> Encaissement tactile rapide</li>
                    <li><i class="bi bi-check-circle-fill"></i> Alertes stock critiques</li>
                    <li><i class="bi bi-check-circle-fill"></i> Rapports financiers instantanes</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4">
            <div class="w-100 auth-panel">
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('/assets/js/app.js') ?>"></script>
</body>
</html>
