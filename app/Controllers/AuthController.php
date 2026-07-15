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

    public function showForgotPassword(): void
    {
        $this->view('auth/forgot-password', ['resetLink' => null], 'auth');
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
            flash('error', 'Nom utilisateur ou mot de passe incorrect (ou compte desactive)');
            $this->redirect('/login');
        }

        $userModel->logConnection((int) Auth::user()['id'], 'success');
        audit_log('login', 'users', (int) Auth::user()['id'], null, ['status' => 'success']);

        if (!empty($_POST['remember'])) {
            Auth::rememberUser((int) Auth::user()['id']);
        }

        clear_old();
        $this->redirect('/dashboard');
    }

    /**
     * Etape 1 : l'utilisateur demande une reinitialisation.
     * Sans service mail (reseau local), le lien securise est affiche a l'ecran.
     */
    public function requestReset(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/forgot-password');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        if ($username === '') {
            flash('error', 'Indique ton nom utilisateur.');
            $this->redirect('/forgot-password');
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        // Pour ne pas divulguer l'existence d'un compte, message identique.
        $resetLink = null;
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $userModel->createPasswordReset((int) $user['id'], $tokenHash, 15);
            $resetLink = url('/reset-password?token=' . $token);
            audit_log('password_reset_request', 'users', (int) $user['id'], null, null);
        }

        $this->view('auth/forgot-password', [
            'resetLink' => $resetLink,
            'requested' => true,
        ], 'auth');
    }

    public function showResetPassword(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        $valid = false;
        if ($token !== '') {
            $reset = (new User())->findValidPasswordReset(hash('sha256', $token));
            $valid = $reset !== null;
        }

        $this->view('auth/reset-password', [
            'token' => $token,
            'valid' => $valid,
        ], 'auth');
    }

    public function resetPassword(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/login');
        }

        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');

        $validator = (new Validator())
            ->required($_POST, ['token', 'password', 'password_confirmation'])
            ->min($_POST, 'password', 6);

        if ($validator->fails() || $password !== $confirmation) {
            flash('error', 'Mot de passe invalide (min 6 caracteres et confirmation identique).');
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        $userModel = new User();
        $reset = $userModel->findValidPasswordReset(hash('sha256', $token));
        if (!$reset) {
            flash('error', 'Lien de reinitialisation invalide ou expire.');
            $this->redirect('/forgot-password');
        }

        $userModel->updatePasswordById((int) $reset['user_id'], password_hash($password, PASSWORD_BCRYPT));
        $userModel->markPasswordResetUsed((int) $reset['id']);
        audit_log('password_reset', 'users', (int) $reset['user_id'], null, null);

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
        $this->redirect('/login');
    }
}
