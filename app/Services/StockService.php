<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

/**
 * Centralise toute la logique de mouvement de stock.
 * A utiliser a l'interieur d'une transaction geree par l'appelant
 * afin de garantir l'atomicite avec l'operation metier associee.
 */
class StockService
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * Applique un mouvement de stock et journalise le mouvement.
     *
     * @param array  $produit       Ligne produit (doit contenir id et stock)
     * @param string $type          approvisionnement|vente|perte|don|ajustement
     * @param float  $delta         Variation appliquee au stock (positive ou negative)
     * @param int    $userId        Utilisateur a l'origine du mouvement
     * @param string $justification Motif / reference
     *
     * @return float Le nouveau stock
     */
    public function applyMovement(array $produit, string $type, float $delta, int $userId, string $justification): float
    {
        $ancienStock = (float) $produit['stock'];
        $nouveauStock = $ancienStock + $delta;

        if ($nouveauStock < 0) {
            throw new RuntimeException('Stock insuffisant pour le produit #' . ($produit['id'] ?? '?'));
        }

        $this->db->prepare('UPDATE produits SET stock = :stock, updated_at = NOW() WHERE id = :id')
            ->execute([
                'stock' => $nouveauStock,
                'id' => (int) $produit['id'],
            ]);

        $this->db->prepare(
            'INSERT INTO mouvements_stock
                (produit_id, type_mouvement, quantite, ancien_stock, nouveau_stock, utilisateur_id, justification, date_mouvement, created_at, updated_at)
             VALUES
                (:produit_id, :type_mouvement, :quantite, :ancien_stock, :nouveau_stock, :utilisateur_id, :justification, NOW(), NOW(), NOW())'
        )->execute([
            'produit_id' => (int) $produit['id'],
            'type_mouvement' => $type,
            'quantite' => abs($delta),
            'ancien_stock' => $ancienStock,
            'nouveau_stock' => $nouveauStock,
            'utilisateur_id' => $userId,
            'justification' => $justification,
        ]);

        return $nouveauStock;
    }

    /**
     * Convertit une quantite d'unites d'achat en unites de vente (stock).
     * Ex: 1 casier * facteur 12 = 12 bouteilles ajoutees au stock.
     */
    public function convertPurchaseToStock(array $produit, float $quantiteAchat): float
    {
        $facteur = (float) ($produit['facteur_conversion'] ?? 1);
        if ($facteur <= 0) {
            $facteur = 1;
        }

        return $quantiteAchat * $facteur;
    }
}
