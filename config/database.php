<?php

declare(strict_types=1);

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// TiDB Cloud (et la plupart des MySQL cloud) exigent SSL en production
$sslEnabled = filter_var(env('DB_SSL', 'false'), FILTER_VALIDATE_BOOL);
if ($sslEnabled) {
    $caPath = env('DB_SSL_CA', '/etc/ssl/certs/ca-certificates.crt');
    if (is_file($caPath)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
    }
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = filter_var(
        env('DB_SSL_VERIFY', 'true'),
        FILTER_VALIDATE_BOOL
    );
}

return [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => (int) env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'barflow'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => $options,
];
