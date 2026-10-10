<?php
/**
 * Carregado por todas as páginas, pela API, pelo painel e pelo instalador.
 * Lê o .env, define constantes e funções básicas.
 */
if (defined('APP')) return;
define('APP', true);
define('ROOT', dirname(__DIR__));

/* ---------- .env ---------- */
function load_env(array $files): ?string
{
    foreach ($files as $f) {
        if (!is_file($f) || !is_readable($f)) continue;
        foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && substr($v, -1) === $v[0]) {
                $v = substr($v, 1, -1);
            } else {
                $v = preg_replace('/\s+#.*$/', '', $v); // comentário no fim da linha
            }
            $GLOBALS['__ENV'][$k] = $v;
        }
        return $f;
    }
    return null;
}
function env(string $key, $default = null)
{
    $v = $GLOBALS['__ENV'][$key] ?? getenv($key);
    return ($v === false || $v === null || $v === '') ? $default : $v;
}
// Preferência: .env fora da pasta pública (um nível acima). Senão, na raiz do site.
define('ENV_FILE', load_env([dirname(ROOT) . '/.env', ROOT . '/.env']));

define('DEBUG', filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));
error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? '1' : '0');
date_default_timezone_set(env('APP_TIMEZONE', 'America/Sao_Paulo'));
if (function_exists('mb_internal_encoding')) mb_internal_encoding('UTF-8');

/** Subpasta onde o site está (vazio quando está na raiz do domínio). */
define('B', rtrim((string) env('APP_BASE_PATH', ''), '/'));

/* ---------- utilidades ---------- */
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = '/'): string { return B . $path; }
function site_url(): string
{
    $u = rtrim((string) env('APP_URL', ''), '/');
    if ($u !== '') return $u;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . B;
}
function abs_url(string $path = '/'): string { return site_url() . $path; }
function redirect(string $to, int $code = 302): void { header('Location: ' . $to, true, $code); exit; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function client_ip(): string { return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45); }
function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function slugify(string $s): string
{
    $s = trim($s);
    if (function_exists('transliterator_transliterate')) {
        $s = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s);
    } else {
        $s = strtolower((string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s));
    }
    $s = preg_replace('/[^a-z0-9]+/', '-', strtolower($s));
    return trim(substr(trim($s, '-'), 0, 180), '-');
}
function str_limit(string $s, int $n): string
{
    $s = trim(preg_replace('/\s+/u', ' ', $s));
    return mb_strlen($s) > $n ? rtrim(mb_substr($s, 0, $n - 1)) . '…' : $s;
}
const MESES = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
function date_long(?string $dt): string
{
    if (!$dt) return '';
    $t = strtotime($dt);
    return (int) date('j', $t) . ' de ' . MESES[(int) date('n', $t) - 1] . ' de ' . date('Y', $t);
}
function date_short(?string $dt): string { return $dt ? date('d/m/Y', strtotime($dt)) : ''; }

/** Pasta privada para logs, travas e cache. */
function storage(string $sub = ''): string
{
    $dir = ROOT . '/storage' . ($sub !== '' ? '/' . $sub : '');
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    return $dir;
}
function app_log(string $channel, string $msg): void
{
    @file_put_contents(storage('logs') . '/' . $channel . '-' . date('Y-m') . '.log', '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}
/** Limite simples por IP guardado em arquivo: true se ainda pode. */
function rate_ok(string $bucket, int $max, int $seconds): bool
{
    $f = storage('rate') . '/' . $bucket . '-' . sha1(client_ip()) . '.json';
    $now = time();
    $hits = is_file($f) ? (json_decode((string) @file_get_contents($f), true) ?: []) : [];
    $hits = array_values(array_filter($hits, function ($t) use ($now, $seconds) { return $t > $now - $seconds; }));
    if (count($hits) >= $max) return false;
    $hits[] = $now;
    @file_put_contents($f, json_encode($hits), LOCK_EX);
    return true;
}

/* ---------- ícones (gerados pelo site/build.py) ---------- */
function icon(string $name, string $weight = 'regular'): string
{
    static $map = null;
    if ($map === null) $map = is_file(__DIR__ . '/icons.php') ? require __DIR__ . '/icons.php' : [];
    return $map[$name . ($weight === 'regular' ? '' : '-' . $weight)] ?? '';
}

require __DIR__ . '/db.php';
require __DIR__ . '/content.php';
