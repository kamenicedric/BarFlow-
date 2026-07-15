<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Produit;

class ProduitsController extends Controller
{
    public function index(): void
    {
        $produits = (new Produit())->allWithCategory();
        $this->view('produits/index', ['produits' => $produits]);
    }

    public function store(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/produits');
        }

        $validator = (new Validator())
            ->required($_POST, ['nom', 'prix_achat', 'prix_vente', 'stock', 'stock_critique', 'unite'])
            ->numeric($_POST, ['prix_achat', 'prix_vente', 'stock', 'stock_critique']);

        if ($validator->fails()) {
            flash('error', 'Donnees invalides');
            $this->redirect('/produits');
        }

        $payload = [
            'nom' => trim((string) $_POST['nom']),
            'categorie_id' => $_POST['categorie_id'] ?: null,
            'prix_achat' => (float) $_POST['prix_achat'],
            'prix_vente' => (float) $_POST['prix_vente'],
            'stock' => (float) $_POST['stock'],
            'stock_critique' => (float) $_POST['stock_critique'],
            'unite' => trim((string) $_POST['unite']),
            'code_barre' => trim((string) ($_POST['code_barre'] ?? '')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $model = new Produit();
        $id = $model->create($payload);
        audit_log('create', 'produits', $id, null, $payload);

        flash('success', 'Produit ajoute');
        $this->redirect('/produits');
    }

    public function update(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/produits');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $model = new Produit();
        $old = $model->find($id);

        if (!$old) {
            flash('error', 'Produit introuvable');
            $this->redirect('/produits');
        }

        $payload = [
            'nom' => trim((string) $_POST['nom']),
            'prix_achat' => (float) $_POST['prix_achat'],
            'prix_vente' => (float) $_POST['prix_vente'],
            'stock' => (float) $_POST['stock'],
            'stock_critique' => (float) $_POST['stock_critique'],
            'unite' => trim((string) $_POST['unite']),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $model->updateById($id, $payload);
        audit_log('update', 'produits', $id, $old, $payload);

        flash('success', 'Produit modifie');
        $this->redirect('/produits');
    }

    public function delete(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/produits');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $model = new Produit();
        $old = $model->find($id);
        if (!$old) {
            flash('error', 'Produit introuvable');
            $this->redirect('/produits');
        }

        $model->softDelete($id);
        audit_log('delete', 'produits', $id, $old, null);

        flash('success', 'Produit supprime (soft delete)');
        $this->redirect('/produits');
    }

    public function search(): void
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        $produits = (new Produit())->allWithCategory($search);
        $this->json(['data' => $produits]);
    }
}
