<?php
defined('APP') or exit;

/** Conexão PDO (MySQL/MariaDB) com os dados do .env. Lança exceção se falhar. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', env('DB_HOST', 'localhost'), env('DB_PORT', '3306'), env('DB_NAME', ''));
    $pdo = new PDO($dsn, (string) env('DB_USER', ''), (string) env('DB_PASS', ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET time_zone = '" . date('P') . "'");
    return $pdo;
}

/** Igual a db(), mas devolve null (e registra no log) se o banco não estiver disponível. O site continua no ar. */
function db_or_null(): ?PDO
{
    static $failed = false;
    if ($failed) return null;
    try {
        return db();
    } catch (Throwable $ex) {
        $failed = true;
        app_log('db', $ex->getMessage());
        return null;
    }
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
