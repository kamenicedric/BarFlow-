<?= ui_page_header('Gestion des utilisateurs', 'Cree, modifie et securise les comptes du personnel') ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Nouvel utilisateur</div>
            <div class="card-body">
                <form method="POST" action="<?= url('/users') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2"><label class="form-label">Nom complet</label><input class="form-control" name="nom" required></div>
                    <div class="mb-2"><label class="form-label">Nom utilisateur</label><input class="form-control" name="username" required></div>
                    <div class="mb-2"><label class="form-label">Email (optionnel)</label><input class="form-control" type="email" name="email"></div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role_id" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= (int) $role['id'] ?>"><?= e($role['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <input class="form-control" type="password" id="newUserPassword" name="password" required>
                            <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="newUserPassword"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-person-plus"></i> Creer le compte</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Comptes existants</div>
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Nom</th><th>Utilisateur</th><th>Role</th><th>Actif</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= e($u['nom']) ?></td>
                            <td><?= e($u['username']) ?></td>
                            <td><span class="badge bg-secondary"><?= e($u['role_nom']) ?></span></td>
                            <td>
                                <?php if ((int) $u['actif'] === 1): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit-<?= (int) $u['id'] ?>"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#pwd-<?= (int) $u['id'] ?>"><i class="bi bi-key"></i></button>
                                <form method="POST" action="<?= url('/users/delete') ?>" class="d-inline" onsubmit="return confirm('Desactiver ce compte ?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-<?= (int) $u['id'] ?>">
                            <td colspan="5">
                                <form method="POST" action="<?= url('/users/update') ?>" class="row g-2 align-items-end">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <div class="col-md-3"><label class="form-label small">Nom</label><input class="form-control form-control-sm" name="nom" value="<?= e($u['nom']) ?>"></div>
                                    <div class="col-md-3"><label class="form-label small">Utilisateur</label><input class="form-control form-control-sm" name="username" value="<?= e($u['username']) ?>"></div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Role</label>
                                        <select class="form-select form-select-sm" name="role_id">
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= (int) $role['id'] ?>" <?= (int) $role['id'] === (int) $u['role_id'] ? 'selected' : '' ?>><?= e($role['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-check ms-2">
                                        <input class="form-check-input" type="checkbox" name="actif" id="actif-<?= (int) $u['id'] ?>" <?= (int) $u['actif'] === 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="actif-<?= (int) $u['id'] ?>">Actif</label>
                                    </div>
                                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Enregistrer</button></div>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="pwd-<?= (int) $u['id'] ?>">
                            <td colspan="5">
                                <form method="POST" action="<?= url('/users/reset-password') ?>" class="row g-2 align-items-end">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <div class="col-md-4">
                                        <label class="form-label small">Nouveau mot de passe</label>
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" type="password" id="resetpwd-<?= (int) $u['id'] ?>" name="password" required>
                                            <button class="btn btn-outline-secondary js-toggle-password" type="button" data-target="resetpwd-<?= (int) $u['id'] ?>"><i class="bi bi-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-3"><button class="btn btn-sm btn-warning w-100">Reinitialiser</button></div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
