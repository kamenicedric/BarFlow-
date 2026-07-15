<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Auth;

class App
{
    public Router $router;

    public function __construct()
    {
        Session::start();

        if (!Auth::check() && isset($_COOKIE['barflow_remember'])) {
            Auth::attemptRememberCookie();
        }

        $this->router = new Router();
    }

    public function run(): void
    {
        $this->router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
    }
}
