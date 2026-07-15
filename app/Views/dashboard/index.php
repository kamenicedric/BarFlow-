<section class="hero-strip mb-4">
    <div>
        <h3 class="mb-1">Pilotage de performance</h3>
        <p class="mb-0">Vue temps reel des ventes, depenses, benefices et stocks critiques.</p>
    </div>
    <span class="hero-chip"><i class="bi bi-lightning-charge"></i> Live data</span>
</section>

<div class="row g-3" id="dashboardCards">
    <div class="col-6 col-xl-3"><div class="card card-stat"><div class="card-body"><small>Ventes du jour</small><h4 id="v-jour">0</h4></div></div></div>
    <div class="col-6 col-xl-3"><div class="card card-stat"><div class="card-body"><small>Nombre ventes</small><h4 id="nb-ventes">0</h4></div></div></div>
    <div class="col-6 col-xl-3"><div class="card card-stat"><div class="card-body"><small>Depenses jour</small><h4 id="dep-jour">0</h4></div></div></div>
    <div class="col-6 col-xl-3"><div class="card card-stat"><div class="card-body"><small>Benefice estime</small><h4 id="benef-jour">0</h4></div></div></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div><small>Stock critique</small><h6 id="st-critique">0</h6></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-trophy"></i></div>
            <div><small>Top produit (30j)</small><h6 id="top-produit"><?= e($topProduits[0]['nom'] ?? '-') ?></h6></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-person-badge"></i></div>
            <div><small>Top vendeur/se (30j)</small><h6 id="top-vendeuse"><?= e($topVendeuses[0]['nom'] ?? '-') ?></h6></div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 pt-4 px-4 fw-semibold">Ventes & depenses (6 mois)</div>
            <div class="card-body"><canvas id="salesChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 pt-4 px-4 fw-semibold">Top produits (30j)</div>
            <div class="card-body"><canvas id="topProduitsChart" height="220"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 pt-4 px-4 fw-semibold">Classement produits (30j)</div>
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Produit</th><th class="text-end">Qte</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    <?php if (empty($topProduits)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Aucune donnee</td></tr>
                    <?php endif; ?>
                    <?php foreach ($topProduits as $tp): ?>
                        <tr>
                            <td><?= e($tp['nom']) ?></td>
                            <td class="text-end"><?= e((string) $tp['quantite']) ?></td>
                            <td class="text-end"><?= number_format((float) $tp['total'], 0, ',', ' ') ?> <?= e($devise) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 pt-4 px-4 fw-semibold">Classement vendeurs (30j)</div>
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Vendeur/se</th><th class="text-end">Ventes</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    <?php if (empty($topVendeuses)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Aucune donnee</td></tr>
                    <?php endif; ?>
                    <?php foreach ($topVendeuses as $tv): ?>
                        <tr>
                            <td><?= e($tv['nom']) ?></td>
                            <td class="text-end"><?= e((string) $tv['nombre_ventes']) ?></td>
                            <td class="text-end"><?= number_format((float) $tv['total'], 0, ',', ' ') ?> <?= e($devise) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('/assets/js/dashboard.js') ?>"></script>
