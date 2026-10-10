<?php
/**
 * Atendimentos: demandas registradas no mapa e mensagens do formulário de contato.
 */
defined('APP') or exit;
require_once __DIR__ . '/images.php';

const TICKET_CATS = [
    'buraco'      => 'Buraco na rua',
    'iluminacao'  => 'Iluminação pública',
    'lixo'        => 'Lixo e entulho',
    'calcada'     => 'Calçada',
    'agua'        => 'Água e esgoto',
    'arvore'      => 'Árvore e poda',
    'sinalizacao' => 'Sinalização e trânsito',
    'outro'       => 'Outro problema',
];
const TICKET_STATUS = [
    'novo'      => 'Novo',
    'analise'   => 'Em análise',
    'publicado' => 'Na Prefeitura (no mapa)',
    'resolvido' => 'Resolvido',
    'arquivado' => 'Arquivado',
];

/* ---------- atualização automática do banco ---------- */
function db_migrate(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = db_or_null();
    if (!$pdo) return;
    try {
        $v = (int) $pdo->query("SELECT value FROM settings WHERE name = 'db_version'")->fetchColumn();
    } catch (Throwable $ex) {
        return; // site ainda não instalado
    }
    if ($v >= 2) return;
    $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      source VARCHAR(12) NOT NULL DEFAULT 'mapa',
      status VARCHAR(12) NOT NULL DEFAULT 'novo',
      category VARCHAR(20) NULL,
      subject VARCHAR(80) NULL,
      name VARCHAR(120) NULL,
      phone VARCHAR(30) NULL,
      birth VARCHAR(10) NULL,
      anonymous TINYINT(1) NOT NULL DEFAULT 0,
      description TEXT NULL,
      public_text TEXT NULL,
      address VARCHAR(255) NULL,
      neighborhood VARCHAR(120) NULL,
      geo_address VARCHAR(300) NULL,
      lat DECIMAL(10,7) NULL,
      lng DECIMAL(10,7) NULL,
      accuracy INT NULL,
      geo_manual TINYINT(1) NOT NULL DEFAULT 0,
      photos TEXT NULL,
      after_photo TEXT NULL,
      department VARCHAR(160) NULL,
      protocol VARCHAR(80) NULL,
      published_at DATETIME NULL,
      resolved_at DATETIME NULL,
      consent TINYINT(1) NOT NULL DEFAULT 0,
      ip VARCHAR(45) NULL,
      created_at DATETIME NOT NULL,
      updated_at DATETIME NOT NULL,
      PRIMARY KEY (id),
      KEY tickets_status (status, created_at),
      KEY tickets_map (status, published_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_notes (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      ticket_id INT UNSIGNED NOT NULL,
      user_id INT UNSIGNED NULL,
      body TEXT NOT NULL,
      created_at DATETIME NOT NULL,
      PRIMARY KEY (id),
      KEY ticket_notes_ticket (ticket_id),
      CONSTRAINT ticket_notes_fk FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("INSERT INTO settings (name, value) VALUES ('db_version', '2') ON DUPLICATE KEY UPDATE value = '2'");
}

/* ---------- fotos (ficam fora da web; só aparecem pelo api/foto.php) ---------- */
function ticket_photo_store(string $tmp): array
{
    $im = img_fit(img_open($tmp), 1600);
    $rel = date('Y/m');
    $dir = storage('demandas/' . $rel);
    $name = date('dHis') . '-' . bin2hex(random_bytes(6));
    if (function_exists('imagewebp')) { $file = "$rel/$name.webp"; $ok = imagewebp($im, storage('demandas') . '/' . $file, 82); }
    else { $file = "$rel/$name.jpg"; $ok = imagejpeg($im, storage('demandas') . '/' . $file, 84); }
    if (!$ok) throw new RuntimeException('Não foi possível salvar a foto.');
    return ['src' => $file, 'w' => imagesx($im), 'h' => imagesy($im)];
}
/** Guarda bytes JPEG já prontos (anexos do formulário de contato). */
function ticket_photo_store_bytes(string $jpg): ?array
{
    $tmp = tempnam(sys_get_temp_dir(), 'tk');
    file_put_contents($tmp, $jpg);
    try { return ticket_photo_store($tmp); } catch (Throwable $ex) { return null; } finally { @unlink($tmp); }
}
function ticket_photo_url(int $id, string $src): string { return url('/api/foto.php') . '?t=' . $id . '&f=' . rawurlencode($src); }
function ticket_photo_path(string $src): ?string
{
    if (!preg_match('#^\d{4}/\d{2}/[\w-]+\.(webp|jpg)$#', $src)) return null;
    $f = storage('demandas') . '/' . $src;
    return is_file($f) ? $f : null;
}

function ticket_create(array $d): int
{
    db_migrate();
    q('INSERT INTO tickets (source, status, category, subject, name, phone, birth, anonymous, description, address, neighborhood, geo_address, lat, lng, accuracy, geo_manual, photos, consent, ip, created_at, updated_at)
       VALUES (?, \'novo\', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())', [
        $d['source'], $d['category'] ?? null, $d['subject'] ?? null, $d['name'] ?? null, $d['phone'] ?? null, $d['birth'] ?? null,
        !empty($d['anonymous']) ? 1 : 0, $d['description'] ?? null, $d['address'] ?? null, $d['neighborhood'] ?? null, $d['geo_address'] ?? null,
        $d['lat'] ?? null, $d['lng'] ?? null, $d['accuracy'] ?? null, !empty($d['geo_manual']) ? 1 : 0,
        !empty($d['photos']) ? json_encode($d['photos'], JSON_UNESCAPED_SLASHES) : null, !empty($d['consent']) ? 1 : 0, client_ip(),
    ]);
    return (int) db()->lastInsertId();
}

function ticket_note(int $id, string $body, ?int $userId = null): void
{
    q('INSERT INTO ticket_notes (ticket_id, user_id, body, created_at) VALUES (?, ?, ?, NOW())', [$id, $userId, $body]);
}

function ticket_code(int $id): string { return '#' . str_pad((string) $id, 5, '0', STR_PAD_LEFT); }

/** "23 dias", "5 horas", "1 dia". */
function human_span(int $seconds): string
{
    $seconds = max(0, $seconds);
    $d = intdiv($seconds, 86400);
    if ($d >= 1) return $d . ($d === 1 ? ' dia' : ' dias');
    $h = intdiv($seconds, 3600);
    if ($h >= 1) return $h . ($h === 1 ? ' hora' : ' horas');
    $m = max(1, intdiv($seconds, 60));
    return $m . ($m === 1 ? ' minuto' : ' minutos');
}

function first_name(?string $n): string
{
    $n = trim((string) $n);
    return $n === '' ? '' : mb_convert_case(explode(' ', $n)[0], MB_CASE_TITLE, 'UTF-8');
}

/** Demandas que aparecem no mapa público (publicadas ou resolvidas). */
function tickets_public(): array
{
    $pdo = db_or_null();
    if (!$pdo) return [];
    db_migrate();
    try {
        $rows = $pdo->query("SELECT * FROM tickets WHERE status IN ('publicado','resolvido') AND published_at IS NOT NULL AND lat IS NOT NULL ORDER BY published_at DESC")->fetchAll();
    } catch (Throwable $ex) {
        return [];
    }
    $out = [];
    foreach ($rows as $t) {
        $photos = json_decode((string) $t['photos'], true) ?: [];
        $after = json_decode((string) $t['after_photo'], true);
        $start = strtotime($t['published_at']);
        $end = $t['status'] === 'resolvido' && $t['resolved_at'] ? strtotime($t['resolved_at']) : null;
        $out[] = [
            'id' => (int) $t['id'],
            'code' => ticket_code((int) $t['id']),
            'cat' => $t['category'] ?: 'outro',
            'catLabel' => TICKET_CATS[$t['category']] ?? 'Problema na cidade',
            'status' => $t['status'],
            'lat' => (float) $t['lat'], 'lng' => (float) $t['lng'],
            'place' => trim(($t['address'] ?: '') . ($t['neighborhood'] ? ($t['address'] ? ' – ' : '') . $t['neighborhood'] : '')),
            'text' => (string) ($t['public_text'] !== null && $t['public_text'] !== '' ? $t['public_text'] : $t['description']),
            'by' => $t['anonymous'] ? '' : first_name($t['name']),
            'department' => (string) $t['department'],
            'createdAt' => date('c', strtotime($t['created_at'])),
            'publishedAt' => date('c', $start),
            'resolvedAt' => $end ? date('c', $end) : null,
            'days' => $end ? max(0, (int) round(($end - $start) / 86400)) : null,
            'photos' => array_map(function ($p) use ($t) { return ['url' => ticket_photo_url((int) $t['id'], $p['src']), 'w' => $p['w'] ?? null, 'h' => $p['h'] ?? null]; }, $photos),
            'after' => $after ? ['url' => ticket_photo_url((int) $t['id'], $after['src']), 'w' => $after['w'] ?? null, 'h' => $after['h'] ?? null] : null,
        ];
    }
    return $out;
}
