<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Caisse;

class CaisseController extends Controller
{
    public function index(): void
    {
        $active = (new Caisse())->openCaisseForUser((int) Auth::user()['id']);
        $this->view('caisse/index', ['active' => $active]);
    }

    public function ouvrir(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/caisse');
        }

        $montant = (float) ($_POST['montant_initial'] ?? 0);
        if ($montant < 0) {
            flash('error', 'Montant invalide');
            $this->redirect('/caisse');
        }

        $model = new Caisse();
        if ($model->openCaisseForUser((int) Auth::user()['id'])) {
            flash('error', 'Une caisse est deja ouverte');
            $this->redirect('/caisse');
        }

        $id = $model->create([
            'utilisateur_ouverture_id' => Auth::user()['id'],
            'montant_initial' => $montant,
            'date_ouverture' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        audit_log('open_cash', 'caisses', $id, null, ['montant_initial' => $montant]);
        flash('success', 'Caisse ouverte');
        $this->redirect('/caisse');
    }

    public function fermer(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/caisse');
        }

        $db = Database::connection();
        $model = new Caisse();
        $active = $model->openCaisseForUser((int) Auth::user()['id']);

        if (!$active) {
            flash('error', 'Aucune caisse ouverte');
            $this->redirect('/caisse');
        }

        $totalVentesStmt = $db->prepare('SELECT COALESCE(SUM(total), 0) AS total FROM ventes WHERE caisse_id = :caisse_id AND deleted_at IS NULL');
        $totalVentesStmt->execute(['caisse_id' => $active['id']]);
        $totalVentes = (float) ($totalVentesStmt->fetch()['total'] ?? 0);

        $totalDepenses = (float) ($db->query('SELECT COALESCE(SUM(montant), 0) AS total FROM depenses WHERE DATE(date_depense) = CURDATE() AND deleted_at IS NULL')->fetch()['total'] ?? 0);
        $totalPertes = (float) ($db->query('SELECT COALESCE(SUM(valeur_totale), 0) AS total FROM pertes WHERE DATE(date_perte) = CURDATE() AND deleted_at IS NULL')->fetch()['total'] ?? 0);
        $totalDons = (float) ($db->query('SELECT COALESCE(SUM(valeur_totale), 0) AS total FROM dons WHERE DATE(date_don) = CURDATE() AND deleted_at IS NULL')->fetch()['total'] ?? 0);

        $montantTheorique = (float) $active['montant_initial'] + $totalVentes - $totalDepenses - $totalPertes - $totalDons;
        $montantReel = (float) ($_POST['montant_reel'] ?? 0);
        $ecart = $montantReel - $montantTheorique;

        $model->updateById((int) $active['id'], [
            'utilisateur_fermeture_id' => Auth::user()['id'],
            'date_fermeture' => date('Y-m-d H:i:s'),
            'montant_reel' => $montantReel,
            'montant_theorique' => $montantTheorique,
            'ecart' => $ecart,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        audit_log('close_cash', 'caisses', (int) $active['id'], $active, ['montant_reel' => $montantReel, 'montant_theorique' => $montantTheorique, 'ecart' => $ecart]);

        flash('success', 'Caisse fermee. Ecart: ' . number_format($ecart, 2, ',', ' ') . ' FCFA');
        $this->redirect('/caisse');
    }
}
