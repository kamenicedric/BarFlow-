<?php

declare(strict_types=1);

namespace App\Models;

class User extends Model
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT u.*, r.nom AS role_nom
                FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE u.username = :username AND u.deleted_at IS NULL
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);

        return $stmt->fetch() ?: null;
    }

    public function logConnection(int $userId, string $status): void
    {
        $sql = 'INSERT INTO login_history (user_id, ip_address, user_agent, status, created_at)
                VALUES (:user_id, :ip_address, :user_agent, :status, NOW())';
        $this->db->prepare($sql)->execute([
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 0, 255),
            'status' => $status,
        ]);
    }

    public function bruteForceAttempts(string $username): int
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM login_attempts
                WHERE username = :username AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function addAttempt(string $username): void
    {
        $sql = 'INSERT INTO login_attempts (username, ip_address, attempted_at) VALUES (:username, :ip, NOW())';
        $this->db->prepare($sql)->execute([
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM users WHERE username = :username AND deleted_at IS NULL');
        $stmt->execute(['username' => $username]);
        return (int) ($stmt->fetch()['total'] ?? 0) > 0;
    }

    public function getRoleIdByName(string $roleName): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE nom = :nom AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['nom' => $roleName]);
        $value = $stmt->fetchColumn();
        return $value === false ? null : (int) $value;
    }

    public function updatePasswordByUsername(string $username, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE username = :username AND deleted_at IS NULL');
        return $stmt->execute([
            'password' => $hashedPassword,
            'username' => $username,
        ]);
    }
}
