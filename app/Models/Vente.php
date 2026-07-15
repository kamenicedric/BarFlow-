<?php

declare(strict_types=1);

namespace App\Models;

class Vente extends Model
{
    protected string $table = 'ventes';

    public function dashboardStats(): array
    {
        $sql = 'SELECT
                    COALESCE(SUM(total), 0) AS ventes_jour,
                    COUNT(*) AS nombre_ventes
                FROM ventes
                WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL';
        $daily = $this->db->query($sql)->fetch();

        $depenses = $this->db->query("SELECT COALESCE(SUM(montant), 0) AS total FROM depenses WHERE DATE(date_depense) = CURDATE() AND deleted_at IS NULL")->fetch();
        $stockCritique = $this->db->query('SELECT COUNT(*) AS total FROM produits WHERE stock <= stock_critique AND deleted_at IS NULL')->fetch();

        return [
            'ventes_jour' => (float) ($daily['ventes_jour'] ?? 0),
            'nombre_ventes' => (int) ($daily['nombre_ventes'] ?? 0),
            'depenses_jour' => (float) ($depenses['total'] ?? 0),
            'stock_critique' => (int) ($stockCritique['total'] ?? 0),
        ];
    }

    public function monthlySales(): array
    {
        $sql = 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS periode, SUM(total) AS total
                FROM ventes
                WHERE created_at >= (CURDATE() - INTERVAL 6 MONTH) AND deleted_at IS NULL
                GROUP BY DATE_FORMAT(created_at, "%Y-%m")
                ORDER BY periode';

        return $this->db->query($sql)->fetchAll();
    }
}
