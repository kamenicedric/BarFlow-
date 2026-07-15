<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\User;

class UsersController extends Controller
{
    public function index(): void
    {
        $model = new User();
        $this->view('users/index', [
            'users' => $model->allWithRoles(),
            'roles' => $model->roles(),
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/users');
        }

        $validator = (new Validator())
            ->required($_POST, ['nom', 'username', 'password', 'role_id'])
            ->min($_POST, 'password', 6);

        if ($validator->fails()) {
            flash('error', 'Donnees invalides (mot de passe : 6 caracteres minimum).');
            $this->redirect('/users');
        }

        $model = new User();
        $username = trim((string) $_POST['username']);

        if ($model->usernameExists($username)) {
            flash('error', 'Ce nom utilisateur existe deja.');
            $this->redirect('/users');
        }

        $id = $model->create([
            'role_id' => (int) $_POST['role_id'],
            'nom' => trim((string) $_POST['nom']),
            'username' => $username,
            'password' => password_hash((string) $_POST['password'], PASSWORD_BCRYPT),
            'email' => trim((string) ($_POST['email'] ?? '')) ?: null,
            'actif' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        audit_log('create', 'users', $id, null, ['username' => $username, 'role_id' => (int) $_POST['role_id']]);
        flash('success', 'Utilisateur cree.');
        $this->redirect('/users');
    }

    public function update(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/users');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $model = new User();
        $old = $model->find($id);
        if (!$old) {
            flash('error', 'Utilisateur introuvable.');
            $this->redirect('/users');
        }

        $username = trim((string) ($_POST['username'] ?? $old['username']));
        if ($username !== '' && $model->usernameExists($username, $id)) {
            flash('error', 'Ce nom utilisateur est deja pris.');
            $this->redirect('/users');
        }

        $payload = [
            'nom' => trim((string) ($_POST['nom'] ?? $old['nom'])),
            'username' => $username,
            'role_id' => (int) ($_POST['role_id'] ?? $old['role_id']),
            'email' => trim((string) ($_POST['email'] ?? '')) ?: null,
            'actif' => isset($_POST['actif']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $model->updateById($id, $payload);
        audit_log('update', 'users', $id, $old, $payload);
        flash('success', 'Utilisateur mis a jour.');
        $this->redirect('/users');
    }

    public function resetPassword(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/users');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');

        if (strlen($password) < 6) {
            flash('error', 'Mot de passe : 6 caracteres minimum.');
            $this->redirect('/users');
        }

        $model = new User();
        if (!$model->find($id)) {
            flash('error', 'Utilisateur introuvable.');
            $this->redirect('/users');
        }

        $model->updatePasswordById($id, password_hash($password, PASSWORD_BCRYPT));
        $model->deleteRememberTokens($id);
        audit_log('password_reset_admin', 'users', $id, null, null);
        flash('success', 'Mot de passe reinitialise.');
        $this->redirect('/users');
    }

    public function delete(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/users');
        }

        $id = (int) ($_POST['id'] ?? 0);

        if ($id === (int) Auth::user()['id']) {
            flash('error', 'Impossible de supprimer ton propre compte.');
            $this->redirect('/users');
        }

        $model = new User();
        $old = $model->find($id);
        if (!$old) {
            flash('error', 'Utilisateur introuvable.');
            $this->redirect('/users');
        }

        $model->softDelete($id);
        $model->deleteRememberTokens($id);
        audit_log('delete', 'users', $id, $old, null);
        flash('success', 'Utilisateur desactive (soft delete).');
        $this->redirect('/users');
    }
}
