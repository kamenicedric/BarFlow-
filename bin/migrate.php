<?php

declare(strict_types=1);

/**
 * Migration idempotente pour le deploiement (Railway, Docker, VPS...).
 * Lit les variables d'environnement DB_* et applique les fichiers SQL.
 * Peut etre executee a chaque demarrage sans risque (CREATE IF NOT EXISTS,
 * INSERT ... WHERE NOT EXISTS, ALTER protege).
 */

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_DATABASE') ?: 'barflow';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

echo "[migrate] Connexion a {$host}:{$port}/{$db}...\n";

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
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
    // Retirer les lignes de commentaires -- ...
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
            // Colonne/table deja existante -> normal en re-execution
            $skip++;
        }
    }
    echo "[migrate] " . basename($file) . " : {$ok} ok, {$skip} ignores\n";
};

$migrationsDir = dirname(__DIR__) . '/database/migrations';
$runFile($pdo, $migrationsDir . '/001_initial_barflow.sql');
$runFile($pdo, $migrationsDir . '/002_improvements.sql');

echo "[migrate] Termine.\n";
