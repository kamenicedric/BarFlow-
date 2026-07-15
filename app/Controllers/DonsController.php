<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Don;
use App\Models\Produit;
use App\Services\StockService;

class DonsController extends Controller
{
    public function index(): void
    {
        $dons = (new Don())->listWithRelations();
        $produits = (new Produit())->all('deleted_at IS NULL');
        $this->view('dons/index', ['dons' => $dons, 'produits' => $produits]);
    }

    public function store(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/dons');
        }

        $validator = (new Validator())
            ->required($_POST, ['produit_id', 'quantite', 'raison'])
            ->numeric($_POST, ['quantite']);

        if ($validator->fails()) {
            flash('error', 'Donnees invalides');
            $this->redirect('/dons');
        }

        $db = Database::connection();
        $produitModel = new Produit();
        $donModel = new Don();

        $produit = $produitModel->find((int) $_POST['produit_id']);
        $quantite = (float) $_POST['quantite'];

        if (!$produit || $quantite <= 0 || (float) $produit['stock'] < $quantite) {
            flash('error', 'Stock insuffisant ou produit invalide');
            $this->redirect('/dons');
        }

        $valeur = $quantite * (float) $produit['prix_achat'];

        try {
            $db->beginTransaction();

            $id = $donModel->create([
                'produit_id' => (int) $_POST['produit_id'],
                'quantite' => $quantite,
                'raison' => trim((string) $_POST['raison']),
                'autorise_par' => (int) Auth::user()['id'],
                'valeur_totale' => $valeur,
                'date_don' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            (new StockService($db))->applyMovement($produit, 'don', -$quantite, (int) Auth::user()['id'], 'Don #' . $id);

            $db->commit();
            audit_log('create', 'dons', $id, null, ['quantite' => $quantite, 'valeur' => $valeur]);
            flash('success', 'Don enregistre');
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', 'Erreur enregistrement don');
        }

        $this->redirect('/dons');
    }

    public function delete(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/dons');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $model = new Don();
        $old = $model->find($id);

        if (!$old) {
            flash('error', 'Don introuvable');
            $this->redirect('/dons');
        }

        $model->softDelete($id);
        audit_log('delete', 'dons', $id, $old, null);
        flash('success', 'Don supprime (soft delete)');
        $this->redirect('/dons');
    }
}
