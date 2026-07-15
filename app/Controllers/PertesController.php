<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Perte;
use App\Models\Produit;
use App\Services\StockService;

class PertesController extends Controller
{
    public function index(): void
    {
        $pertes = (new Perte())->listWithRelations();
        $produits = (new Produit())->all('deleted_at IS NULL');
        $this->view('pertes/index', ['pertes' => $pertes, 'produits' => $produits]);
    }

    public function store(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/pertes');
        }

        $validator = (new Validator())
            ->required($_POST, ['produit_id', 'quantite', 'type_perte'])
            ->numeric($_POST, ['quantite']);

        if ($validator->fails()) {
            flash('error', 'Donnees invalides');
            $this->redirect('/pertes');
        }

        $db = Database::connection();
        $produitModel = new Produit();
        $perteModel = new Perte();

        $produit = $produitModel->find((int) $_POST['produit_id']);
        $quantite = (float) $_POST['quantite'];

        if (!$produit || $quantite <= 0 || (float) $produit['stock'] < $quantite) {
            flash('error', 'Stock insuffisant ou produit invalide');
            $this->redirect('/pertes');
        }

        $valeur = $quantite * (float) $produit['prix_achat'];

        try {
            $db->beginTransaction();

            $id = $perteModel->create([
                'produit_id' => (int) $_POST['produit_id'],
                'responsable_id' => (int) Auth::user()['id'],
                'type_perte' => trim((string) $_POST['type_perte']),
                'quantite' => $quantite,
                'valeur_totale' => $valeur,
                'justification' => trim((string) ($_POST['justification'] ?? '')),
                'date_perte' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            (new StockService($db))->applyMovement($produit, 'perte', -$quantite, (int) Auth::user()['id'], 'Perte #' . $id);

            $db->commit();
            audit_log('create', 'pertes', $id, null, ['quantite' => $quantite, 'valeur' => $valeur]);
            flash('success', 'Perte enregistree');
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', 'Erreur enregistrement perte');
        }

        $this->redirect('/pertes');
    }

    public function delete(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/pertes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $model = new Perte();
        $old = $model->find($id);

        if (!$old) {
            flash('error', 'Perte introuvable');
            $this->redirect('/pertes');
        }

        $model->softDelete($id);
        audit_log('delete', 'pertes', $id, $old, null);
        flash('success', 'Perte supprimee (soft delete)');
        $this->redirect('/pertes');
    }
}
