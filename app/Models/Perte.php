<?php

declare(strict_types=1);

namespace App\Models;

class Perte extends Model
{
    protected string $table = 'pertes';

    public function listWithRelations(): array
    {
        $sql = 'SELECT p.*, pr.nom AS produit_nom, u.nom AS responsable_nom
                FROM pertes p
                JOIN produits pr ON pr.id = p.produit_id
                LEFT JOIN users u ON u.id = p.responsable_id
                WHERE p.deleted_at IS NULL
                ORDER BY p.id DESC';

        return $this->db->query($sql)->fetchAll();
    }
}
