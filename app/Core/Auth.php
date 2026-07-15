<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
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

        self::login($user);
        return true;
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function hasRole(array $roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role'], $roles, true);
    }
}
