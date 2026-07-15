<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = app_base_path();
if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
}
$requestPath = rtrim($requestPath, '/') ?: '/';
$pageTitles = [
    '/dashboard' => 'Tableau de bord',
    '/' => 'Tableau de bord',
    '/ventes' => 'Point de vente',
    '/produits' => 'Catalogue produits',
    '/stock' => 'Mouvements de stock',
    '/caisse' => 'Gestion de caisse',
    '/pertes' => 'Gestion des pertes',
    '/dons' => 'Gestion des dons',
    '/depenses' => 'Gestion des depenses',
    '/approvisionnements' => 'Approvisionnements',
    '/rapports' => 'Rapports financiers',
];
$pageTitle = $pageTitles[$requestPath] ?? 'BarFlow';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="barflow-base" content="<?= e(app_base_path()) ?>">
    <title>BarFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('/assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <aside class="app-sidebar">
        <div class="brand-block">
            <div class="brand-logo"><i class="bi bi-cup-hot-fill"></i></div>
            <div>
                <h1 class="brand-title">BarFlow</h1>
                <p class="brand-subtitle">Gestion intelligente du bar</p>
            </div>
        </div>
        <nav class="nav flex-column gap-1 mt-3">
            <a class="nav-link" href="<?= url('/dashboard') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a class="nav-link" href="<?= url('/ventes') ?>"><i class="bi bi-cart3"></i> Ventes</a>
            <a class="nav-link" href="<?= url('/produits') ?>"><i class="bi bi-box-seam"></i> Produits</a>
            <a class="nav-link" href="<?= url('/stock') ?>"><i class="bi bi-graph-up-arrow"></i> Stock</a>
            <a class="nav-link" href="<?= url('/caisse') ?>"><i class="bi bi-cash-coin"></i> Caisse</a>
            <a class="nav-link" href="<?= url('/pertes') ?>"><i class="bi bi-exclamation-triangle"></i> Pertes</a>
            <a class="nav-link" href="<?= url('/dons') ?>"><i class="bi bi-gift"></i> Dons</a>
            <a class="nav-link" href="<?= url('/depenses') ?>"><i class="bi bi-receipt"></i> Depenses</a>
            <a class="nav-link" href="<?= url('/approvisionnements') ?>"><i class="bi bi-truck"></i> Approvisionnements</a>
            <a class="nav-link" href="<?= url('/rapports') ?>"><i class="bi bi-bar-chart-line"></i> Rapports</a>
        </nav>
    </aside>
    <main class="app-main">
        <header class="topbar mb-4">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle" type="button">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <h2 class="page-title mb-0"><?= e($pageTitle) ?></h2>
                <p class="page-subtitle mb-0">Bonjour <?= e($_SESSION['user']['nom'] ?? '') ?> - Role: <?= e($_SESSION['user']['role'] ?? '') ?></p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm px-3" id="themeToggle" type="button">
                    <i class="bi bi-moon-stars"></i> Theme
                </button>
                <form method="POST" action="<?= url('/logout') ?>" class="m-0">
                    <?= csrf_field() ?>
                    <button class="btn btn-danger btn-sm px-3" type="submit"><i class="bi bi-box-arrow-right"></i> Deconnexion</button>
                </form>
            </div>
        </header>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success border-0 shadow-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm"><?= e($msg) ?></div>
        <?php endif; ?>

        <section class="content-surface">
            <?= $content ?>
        </section>
        <footer class="app-footer">
            <small>BarFlow - Tableau de gestion temps reel pour bar/snack</small>
        </footer>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= url('/assets/js/app.js') ?>"></script>
</body>
</html>
