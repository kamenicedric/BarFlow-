<?php

declare(strict_types=1);

namespace App\Models;

class Vente extends Model
{
    protected string $table = 'ventes';

    public function dashboardStats(): array
    {
        $daily = $this->db->query(
            'SELECT COALESCE(SUM(total), 0) AS ventes_jour, COUNT(*) AS nombre_ventes
             FROM ventes
             WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL'
        )->fetch();

        $depenses = $this->db->query(
            "SELECT COALESCE(SUM(montant), 0) AS total FROM depenses WHERE DATE(date_depense) = CURDATE() AND deleted_at IS NULL"
        )->fetch();

        $stockCritique = $this->db->query(
            'SELECT COUNT(*) AS total FROM produits WHERE stock <= stock_critique AND deleted_at IS NULL'
        )->fetch();

        // Cout d'achat des articles vendus aujourd'hui (pour estimer le benefice)
        $coutVendu = $this->db->query(
            'SELECT COALESCE(SUM(vd.quantite * p.prix_achat), 0) AS total
             FROM ventes_details vd
             JOIN ventes v ON v.id = vd.vente_id
             JOIN produits p ON p.id = vd.produit_id
             WHERE DATE(v.created_at) = CURDATE() AND v.deleted_at IS NULL'
        )->fetch();

        $ventesJour = (float) ($daily['ventes_jour'] ?? 0);
        $depensesJour = (float) ($depenses['total'] ?? 0);
        $coutJour = (float) ($coutVendu['total'] ?? 0);

        return [
            'ventes_jour' => $ventesJour,
            'nombre_ventes' => (int) ($daily['nombre_ventes'] ?? 0),
            'depenses_jour' => $depensesJour,
            'stock_critique' => (int) ($stockCritique['total'] ?? 0),
            'benefice_jour' => $ventesJour - $coutJour - $depensesJour,
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

    public function monthlyExpenses(): array
    {
        $sql = 'SELECT DATE_FORMAT(date_depense, "%Y-%m") AS periode, SUM(montant) AS total
                FROM depenses
                WHERE date_depense >= (CURDATE() - INTERVAL 6 MONTH) AND deleted_at IS NULL
                GROUP BY DATE_FORMAT(date_depense, "%Y-%m")
                ORDER BY periode';

        return $this->db->query($sql)->fetchAll();
    }

    public function topProduits(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        $sql = "SELECT p.nom, SUM(vd.quantite) AS quantite, SUM(vd.sous_total) AS total
                FROM ventes_details vd
                JOIN ventes v ON v.id = vd.vente_id
                JOIN produits p ON p.id = vd.produit_id
                WHERE v.deleted_at IS NULL
                  AND v.created_at >= (CURDATE() - INTERVAL 30 DAY)
                GROUP BY p.id, p.nom
                ORDER BY quantite DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->fetchAll();
    }

    public function topVendeuses(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        $sql = "SELECT u.nom, COUNT(v.id) AS nombre_ventes, COALESCE(SUM(v.total), 0) AS total
                FROM ventes v
                JOIN users u ON u.id = v.utilisateur_id
                WHERE v.deleted_at IS NULL
                  AND v.created_at >= (CURDATE() - INTERVAL 30 DAY)
                GROUP BY u.id, u.nom
                ORDER BY total DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->fetchAll();
    }
}
