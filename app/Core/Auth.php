<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    private const REMEMBER_COOKIE = 'barflow_remember';
    private const REMEMBER_DAYS = 15;

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'username' => $user['username'],
            'role' => $user['role_nom'],
        ];
    }

    public static function attempt(string $username, string $password): bool
    {
        $model = new User();
        $user = $model->findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        if (isset($user['actif']) && (int) $user['actif'] === 0) {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function logout(): void
    {
        self::forgetRemember();
        Session::destroy();
    }

    public static function hasRole(array $roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role'], $roles, true);
    }

    // ---- Remember me securise (selecteur:validateur) ----

    public static function rememberUser(int $userId): void
    {
        $model = new User();
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $model->storeRememberToken($userId, $tokenHash, self::REMEMBER_DAYS);

        setcookie(
            self::REMEMBER_COOKIE,
            $userId . ':' . $token,
            [
                'expires' => time() + (86400 * self::REMEMBER_DAYS),
                'path' => '/',
                'secure' => Session::isHttps(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    public static function attemptRememberCookie(): bool
    {
        if (self::check()) {
            return true;
        }

        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? '';
        if (!str_contains($cookie, ':')) {
            return false;
        }

        [$userId, $token] = explode(':', $cookie, 2);
        $userId = (int) $userId;
        if ($userId <= 0 || $token === '') {
            return false;
        }

        $model = new User();
        $tokenHash = hash('sha256', $token);
        $stored = $model->findRememberToken($userId, $tokenHash);
        if (!$stored) {
            self::forgetRemember();
            return false;
        }

        $user = $model->findWithRole($userId);
        if (!$user || (isset($user['actif']) && (int) $user['actif'] === 0)) {
            self::forgetRemember();
            return false;
        }

        self::login($user);
        return true;
    }

    public static function forgetRemember(): void
    {
        $user = self::user();
        if ($user !== null) {
            (new User())->deleteRememberTokens((int) $user['id']);
        }
        setcookie(self::REMEMBER_COOKIE, '', time() - 3600, '/');
        unset($_COOKIE[self::REMEMBER_COOKIE]);
    }
}
