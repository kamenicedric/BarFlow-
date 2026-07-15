<?= ui_page_header('Journal d\'audit', 'Historique complet des actions sensibles (qui, quoi, quand)') ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('/audit') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Action</label>
                <select class="form-select form-select-sm" name="action">
                    <option value="">Toutes</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?= e($action) ?>" <?= ($filters['action'] ?? '') === $action ? 'selected' : '' ?>><?= e($action) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Table</label>
                <select class="form-select form-select-sm" name="table">
                    <option value="">Toutes</option>
                    <?php foreach ($tables as $table): ?>
                        <option value="<?= e($table) ?>" <?= ($filters['table'] ?? '') === $table ? 'selected' : '' ?>><?= e($table) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label small">Du</label><input type="date" class="form-control form-control-sm" name="date_debut" value="<?= e((string) ($filters['date_debut'] ?? '')) ?>"></div>
            <div class="col-md-2"><label class="form-label small">Au</label><input type="date" class="form-control form-control-sm" name="date_fin" value="<?= e((string) ($filters['date_fin'] ?? '')) ?>"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel"></i> Filtrer</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Derniers evenements (max 200)</div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Table</th><th>Enreg.</th><th>Details</th><th>IP</th></tr></thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="7" class="text-center text-muted">Aucun evenement</td></tr>
            <?php endif; ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-nowrap"><?= e((string) $log['created_at']) ?></td>
                    <td><?= e((string) ($log['user_nom'] ?? $log['user_username'] ?? 'Systeme')) ?></td>
                    <td><span class="badge bg-info text-dark"><?= e((string) $log['action_type']) ?></span></td>
                    <td><?= e((string) $log['table_name']) ?></td>
                    <td><?= e((string) ($log['record_id'] ?? '-')) ?></td>
                    <td style="max-width:280px;">
                        <?php if (!empty($log['old_value']) || !empty($log['new_value'])): ?>
                            <details>
                                <summary class="small text-primary" style="cursor:pointer;">Voir</summary>
                                <?php if (!empty($log['old_value'])): ?>
                                    <div class="small text-muted mt-1"><strong>Avant:</strong> <code><?= e((string) $log['old_value']) ?></code></div>
                                <?php endif; ?>
                                <?php if (!empty($log['new_value'])): ?>
                                    <div class="small text-muted mt-1"><strong>Apres:</strong> <code><?= e((string) $log['new_value']) ?></code></div>
                                <?php endif; ?>
                            </details>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= e((string) ($log['ip_address'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
