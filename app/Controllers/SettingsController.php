<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->view('settings/index', [
            'settings' => (new Setting())->current(),
        ]);
    }

    public function update(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Token CSRF invalide');
            $this->redirect('/settings');
        }

        $model = new Setting();
        $current = $model->current();

        $logoPath = handle_upload('logo', ['jpg', 'jpeg', 'png', 'webp', 'svg']);

        $payload = [
            'nom_bar' => trim((string) ($_POST['nom_bar'] ?? 'BarFlow')) ?: 'BarFlow',
            'devise' => trim((string) ($_POST['devise'] ?? 'FCFA')) ?: 'FCFA',
            'taux_tva' => (float) ($_POST['taux_tva'] ?? 0),
            'seuil_stock_critique_global' => (float) ($_POST['seuil_stock_critique_global'] ?? 5),
            'sauvegarde_auto' => isset($_POST['sauvegarde_auto']) ? 1 : 0,
            'theme' => in_array($_POST['theme'] ?? 'light', ['light', 'dark'], true) ? $_POST['theme'] : 'light',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($logoPath !== null) {
            $payload['logo_path'] = $logoPath;
        }

        if ($current) {
            $model->updateById((int) $current['id'], $payload);
            audit_log('update', 'settings', (int) $current['id'], $current, $payload);
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $id = $model->create($payload);
            audit_log('create', 'settings', $id, null, $payload);
        }

        flash('success', 'Parametres enregistres.');
        $this->redirect('/settings');
    }
}
