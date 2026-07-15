<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;

class Middleware
{
    public static function run(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if ($middleware === 'auth') {
                (new AuthMiddleware())->handle();
                continue;
            }

            if ($middleware === 'guest') {
                (new GuestMiddleware())->handle();
                continue;
            }

            if (str_starts_with($middleware, 'role:')) {
                $roles = explode(',', str_replace('role:', '', $middleware));
                (new RoleMiddleware($roles))->handle();
            }
        }
    }
}
