<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HealthController extends Controller
{
    public function index(): void
    {
        $payload = ['status' => 'ok', 'app' => 'BarFlow'];

        try {
            Database::connection()->query('SELECT 1');
            $payload['database'] = 'ok';
        } catch (\Throwable $exception) {
            $payload['database'] = 'error';
            $payload['message'] = 'Base de donnees indisponible';
            $this->json($payload, 503);
        }

        $this->json($payload);
    }
}
