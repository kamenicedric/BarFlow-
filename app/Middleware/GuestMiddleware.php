<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (Auth::check()) {
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}
