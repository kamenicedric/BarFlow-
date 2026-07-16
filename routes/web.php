<?php

declare(strict_types=1);

use App\Controllers\ApprovisionnementsController;
use App\Controllers\AuditController;
use App\Controllers\AuthController;
use App\Controllers\CaisseController;
use App\Controllers\DashboardController;
use App\Controllers\DepensesController;
use App\Controllers\DonsController;
use App\Controllers\HealthController;
use App\Controllers\PertesController;
use App\Controllers\ProduitsController;
use App\Controllers\RapportsController;
use App\Controllers\SettingsController;
use App\Controllers\StockController;
use App\Controllers\UsersController;
use App\Controllers\VentesController;

/** @var App\Core\App $app */

$app->router->get('/health', [HealthController::class, 'index']);
$app->router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$app->router->post('/login', [AuthController::class, 'login'], ['guest']);
$app->router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], ['guest']);
$app->router->post('/forgot-password', [AuthController::class, 'requestReset'], ['guest']);
$app->router->get('/reset-password', [AuthController::class, 'showResetPassword'], ['guest']);
$app->router->post('/reset-password', [AuthController::class, 'resetPassword'], ['guest']);
$app->router->post('/logout', [AuthController::class, 'logout'], ['auth']);

$app->router->get('/', [DashboardController::class, 'index'], ['auth']);
$app->router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);
$app->router->get('/api/dashboard/stats', [DashboardController::class, 'stats'], ['auth']);

$app->router->get('/produits', [ProduitsController::class, 'index'], ['auth']);
$app->router->post('/produits', [ProduitsController::class, 'store'], ['auth']);
$app->router->post('/produits/update', [ProduitsController::class, 'update'], ['auth']);
$app->router->post('/produits/delete', [ProduitsController::class, 'delete'], ['auth', 'role:administrateur,gerant']);
$app->router->get('/api/produits/search', [ProduitsController::class, 'search'], ['auth']);

$app->router->get('/ventes', [VentesController::class, 'index'], ['auth', 'role:administrateur,gerant,serveuse']);
$app->router->post('/ventes', [VentesController::class, 'store'], ['auth', 'role:administrateur,gerant,serveuse']);

$app->router->get('/stock', [StockController::class, 'index'], ['auth']);
$app->router->get('/api/stock/alerts', [StockController::class, 'alerts'], ['auth']);

$app->router->get('/caisse', [CaisseController::class, 'index'], ['auth', 'role:administrateur,gerant']);
$app->router->post('/caisse/ouvrir', [CaisseController::class, 'ouvrir'], ['auth', 'role:administrateur,gerant']);
$app->router->post('/caisse/fermer', [CaisseController::class, 'fermer'], ['auth', 'role:administrateur,gerant']);

$app->router->get('/pertes', [PertesController::class, 'index'], ['auth']);
$app->router->post('/pertes', [PertesController::class, 'store'], ['auth']);
$app->router->post('/pertes/delete', [PertesController::class, 'delete'], ['auth', 'role:administrateur,gerant']);

$app->router->get('/dons', [DonsController::class, 'index'], ['auth']);
$app->router->post('/dons', [DonsController::class, 'store'], ['auth']);
$app->router->post('/dons/delete', [DonsController::class, 'delete'], ['auth', 'role:administrateur,gerant']);

$app->router->get('/depenses', [DepensesController::class, 'index'], ['auth']);
$app->router->post('/depenses', [DepensesController::class, 'store'], ['auth']);
$app->router->post('/depenses/delete', [DepensesController::class, 'delete'], ['auth', 'role:administrateur,gerant']);

$app->router->get('/approvisionnements', [ApprovisionnementsController::class, 'index'], ['auth']);
$app->router->post('/approvisionnements', [ApprovisionnementsController::class, 'store'], ['auth']);
$app->router->post('/approvisionnements/delete', [ApprovisionnementsController::class, 'delete'], ['auth', 'role:administrateur,gerant']);

$app->router->get('/rapports', [RapportsController::class, 'index'], ['auth']);
$app->router->get('/rapports/export/excel', [RapportsController::class, 'exportExcel'], ['auth']);
$app->router->get('/rapports/export/pdf', [RapportsController::class, 'exportPdf'], ['auth']);

$app->router->get('/users', [UsersController::class, 'index'], ['auth', 'role:administrateur']);
$app->router->post('/users', [UsersController::class, 'store'], ['auth', 'role:administrateur']);
$app->router->post('/users/update', [UsersController::class, 'update'], ['auth', 'role:administrateur']);
$app->router->post('/users/reset-password', [UsersController::class, 'resetPassword'], ['auth', 'role:administrateur']);
$app->router->post('/users/delete', [UsersController::class, 'delete'], ['auth', 'role:administrateur']);

$app->router->get('/settings', [SettingsController::class, 'index'], ['auth', 'role:administrateur,gerant']);
$app->router->post('/settings', [SettingsController::class, 'update'], ['auth', 'role:administrateur,gerant']);

$app->router->get('/audit', [AuditController::class, 'index'], ['auth', 'role:administrateur,gerant']);
