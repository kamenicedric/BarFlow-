<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Approvisionnement;
use App\Models\Fournisseur;
use App\Models\Produit;

class ApprovisionnementsController extends Controller
{
    public function index(): void
    {
        $approvisionnements = (new Approvisionnement())->listWithRelations();
        $produits = (new Produit())->all('deleted_at IS NULL');
        $this->view('approvisionnements/index', [
            'approvisionnements' => $approvisionnements,
            'produits' => $produits,
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/approvisionnements');
        }

        $validator = (new Validator())
            ->required($_POST, ['produit_id', 'quantite', 'prix_total', 'fournisseur'])
            ->numeric($_POST, ['quantite', 'prix_total']);

        if ($validator->fails()) {
            flash('error', 'Donnees invalides');
            $this->redirect('/approvisionnements');
        }

        $db = Database::connection();
        $produitModel = new Produit();
        $fournisseurModel = new Fournisseur();
        $approModel = new Approvisionnement();

        $produit = $produitModel->find((int) $_POST['produit_id']);
        $quantite = (float) $_POST['quantite'];

        if (!$produit || $quantite <= 0) {
            flash('error', 'Produit ou quantite invalide');
            $this->redirect('/approvisionnements');
        }

        $fournisseurNom = trim((string) $_POST['fournisseur']);
        $fournisseur = $fournisseurModel->findByName($fournisseurNom);
        $fournisseurId = $fournisseur['id'] ?? $fournisseurModel->create([
            'nom' => $fournisseurNom,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $ancienStock = (float) $produit['stock'];
        $nouveauStock = $ancienStock + $quantite;

        try {
            $db->beginTransaction();

            $id = $approModel->create([
                'produit_id' => (int) $produit['id'],
                'fournisseur_id' => (int) $fournisseurId,
                'quantite' => $quantite,
                'prix_total' => (float) $_POST['prix_total'],
                'facture_path' => null,
                'date_approvisionnement' => date('Y-m-d H:i:s'),
                'utilisateur_id' => (int) Auth::user()['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $produitModel->updateById((int) $produit['id'], [
                'stock' => $nouveauStock,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $db->prepare('INSERT INTO mouvements_stock (produit_id, type_mouvement, quantite, ancien_stock, nouveau_stock, utilisateur_id, justification, date_mouvement, created_at, updated_at)
                          VALUES (:produit_id, :type_mouvement, :quantite, :ancien_stock, :nouveau_stock, :utilisateur_id, :justification, NOW(), NOW(), NOW())')
                ->execute([
                    'produit_id' => $produit['id'],
                    'type_mouvement' => 'approvisionnement',
                    'quantite' => $quantite,
                    'ancien_stock' => $ancienStock,
                    'nouveau_stock' => $nouveauStock,
                    'utilisateur_id' => Auth::user()['id'],
                    'justification' => 'Appro #' . $id,
                ]);

            $db->commit();
            audit_log('create', 'approvisionnements', $id, null, ['quantite' => $quantite, 'prix_total' => (float) $_POST['prix_total']]);
            flash('success', 'Approvisionnement enregistre');
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', 'Erreur enregistrement approvisionnement');
        }

        $this->redirect('/approvisionnements');
    }

    public function delete(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/approvisionnements');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $model = new Approvisionnement();
        $old = $model->find($id);

        if (!$old) {
            flash('error', 'Approvisionnement introuvable');
            $this->redirect('/approvisionnements');
        }

        $model->softDelete($id);
        audit_log('delete', 'approvisionnements', $id, $old, null);

        flash('success', 'Approvisionnement supprime (soft delete)');
        $this->redirect('/approvisionnements');
    }
}
