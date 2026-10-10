<?php
defined('APP') or exit;

/* =========================================================
   Configurações do site (editáveis no painel)
   ========================================================= */
const SETTINGS_DEFAULTS = [
    'whatsapp'     => '5522998012278',
    'whatsapp_msg' => 'Olá Vereador Maicon Gonçalves! Gostaria de falar com você',
    'email'        => 'vereadormaicongoncalves@gmail.com',
    'address'      => "R. Farinha Filho, 50 - Centro\nNova Friburgo - RJ, 28610-280",
    'office'       => 'Gabinete 20 - Câmara Municipal',
    'facebook'     => 'https://www.facebook.com/share/1KTDkCFLzc/?mibextid=wwXIfr',
    'instagram'    => 'https://www.instagram.com/maicongoncalvesnf/',
    'tiktok'       => 'https://www.tiktok.com/@maicongoncalvesnf',
    'youtube'      => '',
];

function settings_all(bool $fresh = false): array
{
    static $cache = null;
    if ($cache !== null && !$fresh) return $cache;
    $cache = SETTINGS_DEFAULTS;
    if ($pdo = db_or_null()) {
        try {
            foreach ($pdo->query('SELECT name, value FROM settings') as $r) $cache[$r['name']] = (string) $r['value'];
        } catch (Throwable $ex) { /* tabela ainda não criada */ }
    }
    return $cache;
}
function S(string $key): string { return settings_all()[$key] ?? ''; }
function settings_save(array $values): void
{
    $st = db()->prepare('INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
    foreach ($values as $k => $v) $st->execute([$k, $v]);
    settings_all(true);
}

function wa_link(): string
{
    $n = preg_replace('/\D+/', '', S('whatsapp'));
    return 'https://wa.me/' . $n . (S('whatsapp_msg') !== '' ? '?text=' . rawurlencode(S('whatsapp_msg')) : '');
}
function address_lines(): array { return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', S('address'))))); }

const SOCIALS = ['facebook' => ['Facebook', 'facebook-logo'], 'instagram' => ['Instagram', 'instagram-logo'], 'tiktok' => ['TikTok', 'tiktok-logo'], 'youtube' => ['YouTube', 'youtube-logo']];
/** Links das redes sociais preenchidas no painel. */
function social_links(): string
{
    $out = '';
    foreach (SOCIALS as $key => [$label, $ic]) {
        $href = S($key);
        if ($href === '') continue;
        $out .= '<a href="' . e($href) . '" target="_blank" rel="noopener" aria-label="' . $label . '">' . icon($ic) . '</a>';
    }
    return $out;
}

/* =========================================================
   Artigos
   ========================================================= */
const POST_COLS = 'p.id, p.title, p.slug, p.excerpt, p.content, p.cover, p.cover_w, p.cover_h, p.cover_alt, p.gallery, p.status, p.published_at, p.meta_description, p.updated_at, c.name AS category, c.slug AS category_slug';

function post_prepare(array $p): array
{
    $p['url'] = url('/' . $p['slug'] . '/');
    $p['abs_url'] = abs_url('/' . $p['slug'] . '/');
    $p['cover_url'] = $p['cover'] ? url('/' . ltrim($p['cover'], '/')) : '';
    $p['gallery'] = $p['gallery'] ? (json_decode($p['gallery'], true) ?: []) : [];
    $p['category'] = $p['category'] ?? '';
    $p['iso'] = $p['published_at'] ? date('Y-m-d', strtotime($p['published_at'])) : '';
    return $p;
}

/** Publicados, mais recentes primeiro. */
function posts_published(int $limit = 0, ?int $exceptId = null): array
{
    $pdo = db_or_null();
    if (!$pdo) return [];
    $sql = 'SELECT ' . POST_COLS . ' FROM posts p LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.status = \'published\' AND p.published_at <= NOW()' . ($exceptId ? ' AND p.id <> ' . (int) $exceptId : '') .
           ' ORDER BY p.published_at DESC, p.id DESC' . ($limit ? ' LIMIT ' . (int) $limit : '');
    try {
        return array_map('post_prepare', $pdo->query($sql)->fetchAll());
    } catch (Throwable $ex) {
        app_log('db', $ex->getMessage());
        return [];
    }
}

function post_by_slug(string $slug, bool $allowDraft = false): ?array
{
    $pdo = db_or_null();
    if (!$pdo) return null;
    try {
        $st = $pdo->prepare('SELECT ' . POST_COLS . ' FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ?' .
            ($allowDraft ? '' : ' AND p.status = \'published\' AND p.published_at <= NOW()') . ' LIMIT 1');
        $st->execute([$slug]);
        $row = $st->fetch();
        return $row ? post_prepare($row) : null;
    } catch (Throwable $ex) {
        app_log('db', $ex->getMessage());
        return null;
    }
}

/** Card de notícia usado na home, na lista e no fim dos artigos. */
function card_html(array $p, bool $big = false, bool $lazy = true): string
{
    $size = ($p['cover_w'] && $p['cover_h']) ? ' width="' . (int) $p['cover_w'] . '" height="' . (int) $p['cover_h'] . '"' : '';
    $img = $p['cover_url'] ? '<img src="' . e($p['cover_url']) . '" alt=""' . $size . ($lazy ? ' loading="lazy"' : '') . '>' : '';
    $tag = $p['category'] !== '' ? '<span class="tag">' . e($p['category']) . '</span>' : '';
    return '<article class="card' . ($big ? ' card--big' : '') . '" data-cat="' . e($p['category']) . '" data-q="' . e($p['title'] . ' ' . $p['category'] . ' ' . $p['excerpt']) . '">
        <div class="card-img">' . $img . $tag . '</div>
        <div class="card-tx"><h3 class="card-t"><a href="' . e($p['url']) . '">' . e($p['title']) . '</a></h3>
          <div class="card-ft"><time datetime="' . e($p['iso']) . '">' . date_short($p['published_at']) . '</time><span class="card-go" aria-hidden="true">' . icon('arrow-right', 'bold') . '</span></div></div>
      </article>';
}

/** Página de erro 404 com o layout do site. */
function render_404(): void
{
    http_response_code(404);
    $meta = ['title' => 'Página não encontrada – Vereador Maicon Gonçalves', 'description' => 'A página que você procurou não existe ou mudou de endereço.', 'robots' => 'noindex'];
    include ROOT . '/app/pages/404.php';
    exit;
}
