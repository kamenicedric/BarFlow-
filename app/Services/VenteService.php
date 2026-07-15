<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Produit;
use PDO;
use RuntimeException;

/**
 * Orchestration complete d'une vente : validation stock, ecriture vente,
 * details, mouvements de stock, le tout dans une transaction atomique.
 */
class VenteService
{
    public function __construct(private PDO $db, private ?Produit $produitModel = null)
    {
        $this->produitModel = $produitModel ?? new Produit();
    }

    /**
     * @param array<int,array{produit_id:mixed,quantite:mixed}> $items
     *
     * @return array{vente_id:int,total:float}
     */
    public function create(array $items, string $modePaiement, int $userId, int $caisseId): array
    {
        if (empty($items)) {
            throw new RuntimeException('Panier vide');
        }

        $stockService = new StockService($this->db);

        $this->db->beginTransaction();

        try {
            // Validation prealable
            $lignes = [];
            $total = 0.0;
            foreach ($items as $item) {
                $produit = $this->produitModel->find((int) ($item['produit_id'] ?? 0));
                $quantite = (float) ($item['quantite'] ?? 0);

                if (!$produit || $quantite <= 0) {
                    throw new RuntimeException('Article invalide dans le panier');
                }
                if ((float) $produit['stock'] < $quantite) {
                    throw new RuntimeException('Stock insuffisant : ' . $produit['nom']);
                }

                $sousTotal = $quantite * (float) $produit['prix_vente'];
                $total += $sousTotal;
                $lignes[] = ['produit' => $produit, 'quantite' => $quantite, 'sous_total' => $sousTotal];
            }

            $this->db->prepare(
                'INSERT INTO ventes (caisse_id, utilisateur_id, mode_paiement, total, created_at, updated_at)
                 VALUES (:caisse_id, :utilisateur_id, :mode_paiement, :total, NOW(), NOW())'
            )->execute([
                'caisse_id' => $caisseId,
                'utilisateur_id' => $userId,
                'mode_paiement' => $modePaiement,
                'total' => $total,
            ]);

            $venteId = (int) $this->db->lastInsertId();

            $detailStmt = $this->db->prepare(
                'INSERT INTO ventes_details (vente_id, produit_id, quantite, prix_unitaire, sous_total, created_at, updated_at)
                 VALUES (:vente_id, :produit_id, :quantite, :prix_unitaire, :sous_total, NOW(), NOW())'
            );

            foreach ($lignes as $ligne) {
                $produit = $ligne['produit'];
                $quantite = $ligne['quantite'];

                $detailStmt->execute([
                    'vente_id' => $venteId,
                    'produit_id' => (int) $produit['id'],
                    'quantite' => $quantite,
                    'prix_unitaire' => (float) $produit['prix_vente'],
                    'sous_total' => $ligne['sous_total'],
                ]);

                $stockService->applyMovement($produit, 'vente', -$quantite, $userId, 'Vente #' . $venteId);
            }

            $this->db->commit();

            return ['vente_id' => $venteId, 'total' => $total];
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
