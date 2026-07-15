<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Caisse;
use App\Models\Produit;
use App\Services\VenteService;

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

        $caisse = (new Caisse())->openCaisseForUser((int) Auth::user()['id']);
        if (!$caisse) {
            $this->json(['message' => 'Aucune caisse ouverte'], 422);
        }

        try {
            $service = new VenteService(Database::connection());
            $result = $service->create($items, $modePaiement, (int) Auth::user()['id'], (int) $caisse['id']);

            audit_log('create', 'ventes', $result['vente_id'], null, [
                'total' => $result['total'],
                'mode_paiement' => $modePaiement,
            ]);

            $this->json([
                'message' => 'Vente enregistree',
                'vente_id' => $result['vente_id'],
                'total' => $result['total'],
            ]);
        } catch (\Throwable $exception) {
            $this->json(['message' => $exception->getMessage()], 422);
        }
    }
}
