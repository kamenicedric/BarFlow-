<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Depense;

class DepensesController extends Controller
{
    public function index(): void
    {
        $depenses = (new Depense())->all('deleted_at IS NULL');
        $this->view('depenses/index', ['depenses' => $depenses]);
    }

    public function store(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/depenses');
        }

        $validator = (new Validator())
            ->required($_POST, ['montant', 'description', 'categorie'])
            ->numeric($_POST, ['montant']);

        if ($validator->fails()) {
            flash('error', 'Donnees invalides');
            $this->redirect('/depenses');
        }

        $payload = [
            'montant' => (float) $_POST['montant'],
            'description' => trim((string) $_POST['description']),
            'categorie' => trim((string) $_POST['categorie']),
            'donneur_ordre' => trim((string) ($_POST['donneur_ordre'] ?? '')),
            'executant' => trim((string) ($_POST['executant'] ?? '')),
            'preuve_path' => handle_upload('preuve'),
            'date_depense' => date('Y-m-d H:i:s'),
            'utilisateur_id' => (int) Auth::user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $id = (new Depense())->create($payload);
        audit_log('create', 'depenses', $id, null, $payload);

        flash('success', 'Depense ajoutee');
        $this->redirect('/depenses');
    }

    public function delete(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/depenses');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $model = new Depense();
        $old = $model->find($id);

        if (!$old) {
            flash('error', 'Depense introuvable');
            $this->redirect('/depenses');
        }

        $model->softDelete($id);
        audit_log('delete', 'depenses', $id, $old, null);

        flash('success', 'Depense supprimee (soft delete)');
        $this->redirect('/depenses');
    }
}
