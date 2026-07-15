<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Perte;
use App\Models\Produit;

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

        $ancienStock = (float) $produit['stock'];
        $nouveauStock = $ancienStock - $quantite;
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

            $produitModel->updateById((int) $produit['id'], [
                'stock' => $nouveauStock,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $db->prepare('INSERT INTO mouvements_stock (produit_id, type_mouvement, quantite, ancien_stock, nouveau_stock, utilisateur_id, justification, date_mouvement, created_at, updated_at)
                          VALUES (:produit_id, :type_mouvement, :quantite, :ancien_stock, :nouveau_stock, :utilisateur_id, :justification, NOW(), NOW(), NOW())')
                ->execute([
                    'produit_id' => $produit['id'],
                    'type_mouvement' => 'perte',
                    'quantite' => $quantite,
                    'ancien_stock' => $ancienStock,
                    'nouveau_stock' => $nouveauStock,
                    'utilisateur_id' => Auth::user()['id'],
                    'justification' => 'Perte #' . $id,
                ]);

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
