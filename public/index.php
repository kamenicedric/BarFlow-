<?php

declare(strict_types=1);

use App\Core\App;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

if (!function_exists('env')) {
    function env(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

$appConfig = require dirname(__DIR__) . '/config/app.php';
date_default_timezone_set($appConfig['timezone']);

require_once dirname(__DIR__) . '/app/Helpers/functions.php';

$app = new App();
require dirname(__DIR__) . '/routes/web.php';
$app->run();
