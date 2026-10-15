<?php
defined('APP') or exit;

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('mg_admin');
    session_set_cookie_params(['lifetime' => 0, 'path' => (B ?: '') . '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => is_https()]);
    session_start();
}

/** Usuário logado ou null. Sessão expira após 8 h sem uso. */
function auth_user(): ?array
{
    static $user = false;
    if ($user !== false) return $user;
    session_boot();
    $user = null;
    if (empty($_SESSION['uid'])) return null;
    if (($_SESSION['seen'] ?? 0) < time() - 8 * 3600) { $_SESSION = []; return null; }
    $_SESSION['seen'] = time();
    try {
        $row = q('SELECT * FROM users WHERE id = ?', [$_SESSION['uid']])->fetch();
        if ($row) { unset($row['password_hash']); $row['role'] = ($row['role'] ?? 'admin') === 'assessoria' ? 'assessoria' : 'admin'; }
        $user = $row ?: null;
    } catch (Throwable $ex) {
        $user = null;
    }
    return $user;
}

const USER_ROLES = ['admin' => 'Administrador', 'assessoria' => 'Assessoria'];
function is_admin(?array $u = null): bool { $u = $u ?? auth_user(); return ($u['role'] ?? '') === 'admin'; }

function require_login(): array
{
    $u = auth_user();
    if (!$u) redirect(url('/admin/login.php') . '?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? url('/admin/')));
    return $u;
}

function login_blocked(): bool
{
    q('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
    return (int) q('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > (NOW() - INTERVAL 15 MINUTE)', [client_ip()])->fetchColumn() >= 8;
}

function attempt_login(string $email, string $password): bool
{
    $row = q('SELECT id, password_hash FROM users WHERE email = ?', [mb_strtolower(trim($email))])->fetch();
    $hash = $row['password_hash'] ?? '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG'; // evita descobrir e-mails pelo tempo de resposta
    if (password_verify($password, $hash) && $row) {
        session_boot();
        session_regenerate_id(true);
        $_SESSION = ['uid' => (int) $row['id'], 'seen' => time(), 'csrf' => bin2hex(random_bytes(32))];
        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $row['id']]);
        }
        q('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$row['id']]);
        q('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
        return true;
    }
    q('INSERT INTO login_attempts (ip, email, attempted_at) VALUES (?, ?, NOW())', [client_ip(), substr($email, 0, 190)]);
    return false;
}

function logout(): void
{
    session_boot();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---------- proteção contra envio forjado (CSRF) ---------- */
function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void
{
    $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(419);
        exit('Sessão expirada. Volte, recarregue a página e tente de novo.');
    }
}

/* ---------- avisos entre páginas ---------- */
function flash(string $type, string $msg): void { session_boot(); $_SESSION['flash'][] = [$type, $msg]; }
function flashes(): array { session_boot(); $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }
