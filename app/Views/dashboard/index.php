<section class="hero-strip mb-4">
    <div>
        <h3 class="mb-1">Pilotage de performance</h3>
        <p class="mb-0">Vue temps reel des ventes, depenses et stocks critiques.</p>
    </div>
    <span class="hero-chip"><i class="bi bi-lightning-charge"></i> Live data</span>
</section>

<div class="row g-2 mb-3">
    <div class="col-md-4"><?= ui_stat_card('Caisses ouvertes', '1 active', 'bi-cash') ?></div>
    <div class="col-md-4"><?= ui_stat_card('Top canal', 'Especes', 'bi-credit-card') ?></div>
    <div class="col-md-4"><?= ui_stat_card('Objectif du jour', '68%', 'bi-bullseye') ?></div>
</div>

<div class="row g-3" id="dashboardCards">
    <div class="col-6 col-xl-3"><div class="card card-stat"><div class="card-body"><small>Ventes du jour</small><h4 id="v-jour">0</h4></div></div></div>
    <div class="col-6 col-xl-3"><div class="card card-stat"><div class="card-body"><small>Nombre ventes</small><h4 id="nb-ventes">0</h4></div></div></div>
    <div class="col-6 col-xl-3"><div class="card card-stat"><div class="card-body"><small>Depenses jour</small><h4 id="dep-jour">0</h4></div></div></div>
    <div class="col-6 col-xl-3"><div class="card card-stat"><div class="card-body"><small>Stock critique</small><h4 id="st-critique">0</h4></div></div></div>
</div>

<div class="card mt-4 border-0 shadow-sm">
    <div class="card-header border-0 pt-4 px-4 fw-semibold">Ventes mensuelles</div>
    <div class="card-body"><canvas id="salesChart" height="100"></canvas></div>
</div>

<script src="<?= url('/assets/js/dashboard.js') ?>"></script>
