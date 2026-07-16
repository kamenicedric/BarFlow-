<?php

declare(strict_types=1);

/**
 * Migration idempotente pour le deploiement (Render, Docker, VPS...).
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

if (!function_exists('env')) {
    function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return ($value === false || $value === null || $value === '') ? $default : (string) $value;
    }
}

$config = require dirname(__DIR__) . '/config/database.php';

echo "[migrate] Connexion a {$config['host']}:{$config['port']}/{$config['database']}...\n";

$dsn = sprintf(
    '%s:host=%s;port=%d;dbname=%s;charset=%s',
    $config['driver'],
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
} catch (Throwable $e) {
    fwrite(STDERR, "[migrate] Connexion impossible: " . $e->getMessage() . "\n");
    exit(1);
}

$runFile = static function (PDO $pdo, string $file): void {
    if (!is_file($file)) {
        echo "[migrate] Fichier absent: {$file}\n";
        return;
    }

    $sql = (string) file_get_contents($file);
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;

    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $ok = 0;
    $skip = 0;
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        try {
            $pdo->exec($statement);
            $ok++;
        } catch (Throwable $e) {
            $skip++;
        }
    }
    echo "[migrate] " . basename($file) . " : {$ok} ok, {$skip} ignores\n";
};

$migrationsDir = dirname(__DIR__) . '/database/migrations';
$runFile($pdo, $migrationsDir . '/001_initial_barflow.sql');
$runFile($pdo, $migrationsDir . '/002_improvements.sql');

echo "[migrate] Termine.\n";
