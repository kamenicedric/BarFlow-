<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly array $roles)
    {
    }

    public function handle(): void
    {
        if (!Auth::hasRole($this->roles)) {
            http_response_code(403);
            exit('Acces refuse');
        }
    }
}
