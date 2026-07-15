<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class StockController extends Controller
{
    public function index(): void
    {
        $sql = 'SELECT m.*, p.nom AS produit_nom, u.nom AS utilisateur_nom
                FROM mouvements_stock m
                JOIN produits p ON p.id = m.produit_id
                LEFT JOIN users u ON u.id = m.utilisateur_id
                WHERE m.deleted_at IS NULL
                ORDER BY m.id DESC
                LIMIT 200';

        $mouvements = Database::connection()->query($sql)->fetchAll();
        $this->view('stock/index', ['mouvements' => $mouvements]);
    }

    public function alerts(): void
    {
        $sql = 'SELECT id, nom, stock, stock_critique FROM produits WHERE stock <= stock_critique AND deleted_at IS NULL ORDER BY stock ASC';
        $alerts = Database::connection()->query($sql)->fetchAll();

        $this->json(['data' => $alerts]);
    }
}
