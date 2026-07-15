<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Caisse;
use App\Models\Produit;

class VentesController extends Controller
{
    public function index(): void
    {
        $produits = (new Produit())->all('stock > 0 AND deleted_at IS NULL');
        $this->view('ventes/index', ['produits' => $produits]);
    }

    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['message' => 'Token CSRF invalide'], 422);
        }

        $items = $_POST['items'] ?? [];
        $modePaiement = trim((string) ($_POST['mode_paiement'] ?? 'especes'));

        if (!is_array($items) || empty($items)) {
            $this->json(['message' => 'Panier vide'], 422);
        }

        $db = Database::connection();
        $produitModel = new Produit();
        $caisseModel = new Caisse();

        $caisse = $caisseModel->openCaisseForUser((int) Auth::user()['id']);
        if (!$caisse) {
            $this->json(['message' => 'Aucune caisse ouverte'], 422);
        }

        try {
            $db->beginTransaction();

            $total = 0.0;
            foreach ($items as $item) {
                $product = $produitModel->find((int) $item['produit_id']);
                $quantity = (float) $item['quantite'];

                if (!$product || $quantity <= 0 || (float) $product['stock'] < $quantity) {
                    throw new \RuntimeException('Stock insuffisant sur un produit');
                }

                $total += $quantity * (float) $product['prix_vente'];
            }

            $saleSql = 'INSERT INTO ventes (caisse_id, utilisateur_id, mode_paiement, total, created_at, updated_at)
                        VALUES (:caisse_id, :utilisateur_id, :mode_paiement, :total, NOW(), NOW())';
            $db->prepare($saleSql)->execute([
                'caisse_id' => $caisse['id'],
                'utilisateur_id' => Auth::user()['id'],
                'mode_paiement' => $modePaiement,
                'total' => $total,
            ]);

            $venteId = (int) $db->lastInsertId();

            $detailSql = 'INSERT INTO ventes_details (vente_id, produit_id, quantite, prix_unitaire, sous_total, created_at, updated_at)
                          VALUES (:vente_id, :produit_id, :quantite, :prix_unitaire, :sous_total, NOW(), NOW())';
            $mvtSql = 'INSERT INTO mouvements_stock (produit_id, type_mouvement, quantite, ancien_stock, nouveau_stock, utilisateur_id, justification, date_mouvement, created_at, updated_at)
                       VALUES (:produit_id, :type_mouvement, :quantite, :ancien_stock, :nouveau_stock, :utilisateur_id, :justification, NOW(), NOW(), NOW())';

            foreach ($items as $item) {
                $product = $produitModel->find((int) $item['produit_id']);
                $quantity = (float) $item['quantite'];
                $oldStock = (float) $product['stock'];
                $newStock = $oldStock - $quantity;

                $db->prepare($detailSql)->execute([
                    'vente_id' => $venteId,
                    'produit_id' => $product['id'],
                    'quantite' => $quantity,
                    'prix_unitaire' => $product['prix_vente'],
                    'sous_total' => $quantity * (float) $product['prix_vente'],
                ]);

                $db->prepare('UPDATE produits SET stock = :stock, updated_at = NOW() WHERE id = :id')
                    ->execute(['stock' => $newStock, 'id' => $product['id']]);

                $db->prepare($mvtSql)->execute([
                    'produit_id' => $product['id'],
                    'type_mouvement' => 'vente',
                    'quantite' => $quantity,
                    'ancien_stock' => $oldStock,
                    'nouveau_stock' => $newStock,
                    'utilisateur_id' => Auth::user()['id'],
                    'justification' => 'Vente #' . $venteId,
                ]);
            }

            $db->commit();

            audit_log('create', 'ventes', $venteId, null, ['total' => $total, 'mode_paiement' => $modePaiement]);

            $this->json(['message' => 'Vente enregistree', 'vente_id' => $venteId, 'total' => $total]);
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['message' => $exception->getMessage()], 422);
        }
    }
}
