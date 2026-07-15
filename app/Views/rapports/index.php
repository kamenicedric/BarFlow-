<?php
$startDateParam = substr($startDate, 0, 10);
$endDateParam = substr($endDate, 0, 10);
$data = array_merge([
    'ventes' => 0,
    'approvisionnements' => 0,
    'depenses' => 0,
    'pertes' => 0,
    'dons' => 0,
    'benefice' => 0,
], is_array($data ?? null) ? $data : []);
?>
<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET" action="<?= url('/rapports') ?>">
            <div class="col-md-3">
                <label class="form-label">Date debut</label>
                <input class="form-control" type="date" name="start" value="<?= e($startDateParam) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date fin</label>
                <input class="form-control" type="date" name="end" value="<?= e($endDateParam) ?>">
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Filtrer</button></div>
            <div class="col-md-2"><a class="btn btn-outline-success w-100" href="<?= url('/rapports/export/excel') ?>?start=<?= e($startDateParam) ?>&end=<?= e($endDateParam) ?>">Export Excel</a></div>
            <div class="col-md-2"><a class="btn btn-outline-dark w-100" target="_blank" href="<?= url('/rapports/export/pdf') ?>?start=<?= e($startDateParam) ?>&end=<?= e($endDateParam) ?>">Export PDF</a></div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-6 col-xl-2"><div class="card card-stat"><div class="card-body"><small>Ventes</small><h5><?= number_format((float) $data['ventes'], 0, ',', ' ') ?></h5></div></div></div>
    <div class="col-6 col-xl-2"><div class="card card-stat"><div class="card-body"><small>Appro</small><h5><?= number_format((float) $data['approvisionnements'], 0, ',', ' ') ?></h5></div></div></div>
    <div class="col-6 col-xl-2"><div class="card card-stat"><div class="card-body"><small>Depenses</small><h5><?= number_format((float) $data['depenses'], 0, ',', ' ') ?></h5></div></div></div>
    <div class="col-6 col-xl-2"><div class="card card-stat"><div class="card-body"><small>Pertes</small><h5><?= number_format((float) $data['pertes'], 0, ',', ' ') ?></h5></div></div></div>
    <div class="col-6 col-xl-2"><div class="card card-stat"><div class="card-body"><small>Dons</small><h5><?= number_format((float) $data['dons'], 0, ',', ' ') ?></h5></div></div></div>
    <div class="col-6 col-xl-2"><div class="card card-stat"><div class="card-body"><small>Benefice</small><h5><?= number_format((float) $data['benefice'], 0, ',', ' ') ?></h5></div></div></div>
</div>
