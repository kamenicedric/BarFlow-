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

$debug = (bool) ($appConfig['debug'] ?? false);
$logDir = dirname(__DIR__) . '/storage/logs';
$logFile = $logDir . '/app.log';

if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}

ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', $logFile);
error_reporting(E_ALL);

set_exception_handler(static function (\Throwable $exception) use ($debug, $logFile): void {
    $message = sprintf(
        "[%s] %s: %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    );
    @error_log($message, 3, $logFile);

    http_response_code(500);
    if ($debug) {
        echo '<pre style="padding:16px;font-family:monospace;">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        echo 'Une erreur interne est survenue. Reessaie plus tard.';
    }
});

require_once dirname(__DIR__) . '/app/Helpers/functions.php';

$app = new App();
require dirname(__DIR__) . '/routes/web.php';
$app->run();
