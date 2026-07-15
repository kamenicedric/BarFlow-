<?php

declare(strict_types=1);

namespace App\Models;

class Fournisseur extends Model
{
    protected string $table = 'fournisseurs';

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM fournisseurs WHERE nom = :nom AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['nom' => $name]);

        return $stmt->fetch() ?: null;
    }
}
