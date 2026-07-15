<?php

declare(strict_types=1);

namespace App\Models;

class Produit extends Model
{
    protected string $table = 'produits';

    public function allWithCategory(?string $search = null): array
    {
        $sql = 'SELECT p.*, c.nom AS categorie_nom
                FROM produits p
                LEFT JOIN categories c ON c.id = p.categorie_id
                WHERE p.deleted_at IS NULL';
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' AND (p.nom LIKE :search OR p.code_barre LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY p.nom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function decrementStock(int $id, float $quantity): bool
    {
        $sql = 'UPDATE produits SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity';
        return $this->db->prepare($sql)->execute([
            'quantity' => $quantity,
            'id' => $id,
        ]);
    }

    public function incrementStock(int $id, float $quantity): bool
    {
        $sql = 'UPDATE produits SET stock = stock + :quantity WHERE id = :id';
        return $this->db->prepare($sql)->execute([
            'quantity' => $quantity,
            'id' => $id,
        ]);
    }
}
