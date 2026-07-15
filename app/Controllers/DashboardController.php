<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Vente;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->view('dashboard/index');
    }

    public function stats(): void
    {
        $venteModel = new Vente();
        $stats = $venteModel->dashboardStats();
        $monthly = $venteModel->monthlySales();

        $this->json([
            'stats' => $stats,
            'monthly' => $monthly,
        ]);
    }
}
