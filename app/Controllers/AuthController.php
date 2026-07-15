<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', [], 'auth');
    }

    public function showRegister(): void
    {
        $this->view('auth/register', [], 'auth');
    }

    public function showForgotPassword(): void
    {
        $this->view('auth/forgot-password', [], 'auth');
    }

    public function login(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/login');
        }

        $validator = (new Validator())->required($_POST, ['username', 'password']);
        if ($validator->fails()) {
            with_old($_POST);
            flash('error', 'Identifiants invalides');
            $this->redirect('/login');
        }

        $username = trim((string) $_POST['username']);
        $password = (string) $_POST['password'];

        $userModel = new User();
        if ($userModel->bruteForceAttempts($username) >= 5) {
            flash('error', 'Trop de tentatives. Reessaye dans 15 minutes.');
            $this->redirect('/login');
        }

        if (!Auth::attempt($username, $password)) {
            $userModel->addAttempt($username);
            flash('error', 'Nom utilisateur ou mot de passe incorrect');
            $this->redirect('/login');
        }

        $userModel->logConnection((int) Auth::user()['id'], 'success');
        audit_log('login', 'users', (int) Auth::user()['id'], null, ['status' => 'success']);

        if (!empty($_POST['remember'])) {
            setcookie('barflow_remember', (string) Auth::user()['id'], time() + (86400 * 15), '/', '', false, true);
        }

        clear_old();
        $this->redirect('/dashboard');
    }

    public function register(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/register');
        }

        $validator = (new Validator())
            ->required($_POST, ['nom', 'username', 'password', 'password_confirmation'])
            ->min($_POST, 'password', 6);

        if ($validator->fails()) {
            with_old($_POST);
            flash('error', 'Donnees invalides. Minimum 6 caracteres pour le mot de passe.');
            $this->redirect('/register');
        }

        $nom = trim((string) $_POST['nom']);
        $username = trim((string) $_POST['username']);
        $password = (string) $_POST['password'];
        $confirmation = (string) $_POST['password_confirmation'];

        if ($password !== $confirmation) {
            with_old($_POST);
            flash('error', 'La confirmation du mot de passe est incorrecte.');
            $this->redirect('/register');
        }

        $userModel = new User();
        if ($userModel->usernameExists($username)) {
            with_old($_POST);
            flash('error', 'Ce nom utilisateur existe deja.');
            $this->redirect('/register');
        }

        $roleId = $userModel->getRoleIdByName('serveuse');
        if ($roleId === null) {
            flash('error', 'Role par defaut introuvable. Contacte l administrateur.');
            $this->redirect('/register');
        }

        $userId = $userModel->create([
            'role_id' => $roleId,
            'nom' => $nom,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'email' => null,
            'actif' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        flash('success', 'Compte cree avec succes. Tu peux te connecter.');
        clear_old();
        audit_log('register', 'users', $userId, null, ['username' => $username]);
        $this->redirect('/login');
    }

    public function resetPassword(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/forgot-password');
        }

        $validator = (new Validator())
            ->required($_POST, ['username', 'password', 'password_confirmation'])
            ->min($_POST, 'password', 6);

        if ($validator->fails()) {
            with_old($_POST);
            flash('error', 'Donnees invalides. Minimum 6 caracteres pour le mot de passe.');
            $this->redirect('/forgot-password');
        }

        $username = trim((string) $_POST['username']);
        $password = (string) $_POST['password'];
        $confirmation = (string) $_POST['password_confirmation'];

        if ($password !== $confirmation) {
            with_old($_POST);
            flash('error', 'La confirmation du mot de passe est incorrecte.');
            $this->redirect('/forgot-password');
        }

        $userModel = new User();
        if (!$userModel->usernameExists($username)) {
            with_old($_POST);
            flash('error', 'Compte introuvable.');
            $this->redirect('/forgot-password');
        }

        $userModel->updatePasswordByUsername($username, password_hash($password, PASSWORD_BCRYPT));
        clear_old();
        flash('success', 'Mot de passe reinitialise. Connecte-toi maintenant.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        if (!verify_csrf()) {
            $this->redirect('/dashboard');
        }

        audit_log('logout', 'users', (int) (Auth::user()['id'] ?? 0), null, null);
        Auth::logout();
        setcookie('barflow_remember', '', time() - 3600, '/');
        $this->redirect('/login');
    }
}
