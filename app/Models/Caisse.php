<?php

declare(strict_types=1);

namespace App\Models;

class Caisse extends Model
{
    protected string $table = 'caisses';

    public function openCaisseForUser(int $userId): ?array
    {
        $sql = 'SELECT * FROM caisses WHERE utilisateur_ouverture_id = :user_id AND date_fermeture IS NULL AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch() ?: null;
    }
}
