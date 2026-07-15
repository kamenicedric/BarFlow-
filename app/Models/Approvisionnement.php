<?php

declare(strict_types=1);

namespace App\Models;

class Approvisionnement extends Model
{
    protected string $table = 'approvisionnements';

    public function listWithRelations(): array
    {
        $sql = 'SELECT a.*, p.nom AS produit_nom, f.nom AS fournisseur_nom
                FROM approvisionnements a
                JOIN produits p ON p.id = a.produit_id
                LEFT JOIN fournisseurs f ON f.id = a.fournisseur_id
                WHERE a.deleted_at IS NULL
                ORDER BY a.id DESC';

        return $this->db->query($sql)->fetchAll();
    }
}
