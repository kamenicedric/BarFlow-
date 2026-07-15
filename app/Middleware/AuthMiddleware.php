<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::check()) {
            header('Location: ' . url('/login'));
            exit;
        }
    }
}
