<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Vente;

class DashboardController extends Controller
{
    public function index(): void
    {
        $venteModel = new Vente();
        $this->view('dashboard/index', [
            'topProduits' => $venteModel->topProduits(),
            'topVendeuses' => $venteModel->topVendeuses(),
            'devise' => setting('devise', 'FCFA'),
        ]);
    }

    public function stats(): void
    {
        $venteModel = new Vente();

        $this->json([
            'stats' => $venteModel->dashboardStats(),
            'monthly' => $venteModel->monthlySales(),
            'monthly_expenses' => $venteModel->monthlyExpenses(),
            'top_produits' => $venteModel->topProduits(),
            'top_vendeuses' => $venteModel->topVendeuses(),
        ]);
    }
}
