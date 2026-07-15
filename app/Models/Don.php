<?php

declare(strict_types=1);

namespace App\Models;

class Don extends Model
{
    protected string $table = 'dons';

    public function listWithRelations(): array
    {
        $sql = 'SELECT d.*, p.nom AS produit_nom, u.nom AS autorise_nom
                FROM dons d
                JOIN produits p ON p.id = d.produit_id
                LEFT JOIN users u ON u.id = d.autorise_par
                WHERE d.deleted_at IS NULL
                ORDER BY d.id DESC';

        return $this->db->query($sql)->fetchAll();
    }
}
