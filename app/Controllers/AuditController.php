<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index(): void
    {
        $model = new AuditLog();

        $filters = [
            'action' => trim((string) ($_GET['action'] ?? '')),
            'table' => trim((string) ($_GET['table'] ?? '')),
            'date_debut' => trim((string) ($_GET['date_debut'] ?? '')),
            'date_fin' => trim((string) ($_GET['date_fin'] ?? '')),
        ];

        $this->view('audit/index', [
            'logs' => $model->listWithUser($filters),
            'actions' => $model->distinctActions(),
            'tables' => $model->distinctTables(),
            'filters' => $filters,
        ]);
    }
}
