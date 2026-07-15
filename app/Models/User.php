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

    public function findWithRole(int $id): ?array
    {
        $sql = 'SELECT u.*, r.nom AS role_nom
                FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE u.id = :id AND u.deleted_at IS NULL
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function allWithRoles(): array
    {
        $sql = 'SELECT u.*, r.nom AS role_nom
                FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE u.deleted_at IS NULL
                ORDER BY u.nom ASC';

        return $this->db->query($sql)->fetchAll();
    }

    public function roles(): array
    {
        return $this->db->query('SELECT id, nom FROM roles WHERE deleted_at IS NULL ORDER BY nom ASC')->fetchAll();
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

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) AS total FROM users WHERE username = :username AND deleted_at IS NULL';
        $params = ['username' => $username];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetch()['total'] ?? 0) > 0;
    }

    public function getRoleIdByName(string $roleName): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE nom = :nom AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['nom' => $roleName]);
        $value = $stmt->fetchColumn();
        return $value === false ? null : (int) $value;
    }

    public function updatePasswordById(int $userId, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        return $stmt->execute([
            'password' => $hashedPassword,
            'id' => $userId,
        ]);
    }

    // ---- Reinitialisation par token securise ----

    public function createPasswordReset(int $userId, string $tokenHash, int $minutes = 15): void
    {
        // Expiration calculee cote SQL pour rester coherent avec NOW() lors de la verification.
        $sql = 'INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
                VALUES (:user_id, :token_hash, (NOW() + INTERVAL :minutes MINUTE), NOW())';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':token_hash', $tokenHash, \PDO::PARAM_STR);
        $stmt->bindValue(':minutes', $minutes, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function findValidPasswordReset(string $tokenHash): ?array
    {
        $sql = 'SELECT * FROM password_resets
                WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at >= NOW()
                ORDER BY id DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token_hash' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function markPasswordResetUsed(int $resetId): void
    {
        $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id')
            ->execute(['id' => $resetId]);
    }

    // ---- Remember me securise ----

    public function storeRememberToken(int $userId, string $tokenHash, int $days = 15): void
    {
        $sql = 'INSERT INTO user_remember_tokens (user_id, token_hash, expires_at, created_at)
                VALUES (:user_id, :token_hash, (NOW() + INTERVAL :days DAY), NOW())';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':token_hash', $tokenHash, \PDO::PARAM_STR);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function findRememberToken(int $userId, string $tokenHash): ?array
    {
        $sql = 'SELECT * FROM user_remember_tokens
                WHERE user_id = :user_id AND token_hash = :token_hash AND expires_at >= NOW()
                ORDER BY id DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'token_hash' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function deleteRememberTokens(int $userId): void
    {
        $this->db->prepare('DELETE FROM user_remember_tokens WHERE user_id = :user_id')
            ->execute(['user_id' => $userId]);
    }
}
