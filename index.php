<?php
/**
 * Roteador do site. O .htaccess manda para cá tudo o que não é arquivo/pasta real.
 * Mantém os mesmos endereços do site antigo em WordPress (ex.: /noticias/, /slug-da-noticia/).
 */
require __DIR__ . '/app/bootstrap.php';

// Um endereço só para o Google: o de APP_URL (https e sem/com www conforme configurado)
$canon = env('APP_URL', '');
if ($canon !== '' && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    $want = strtolower(rtrim(preg_replace('#^(https?://[^/]+).*$#i', '$1', $canon), '/'));
    $have = strtolower((is_https_req() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''));
    if ($want !== $have && strpos($want, 'localhost') === false) redirect($want . ($_SERVER['REQUEST_URI'] ?? '/'), 301);
}
function is_https_req(): bool { return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'); }

$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if (B !== '' && strpos($path, B) === 0) $path = substr($path, strlen(B));
$slug = trim($path, '/');
$qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';

$pages = [
    ''                     => 'home',
    'minha-historia'       => 'historia',
    'fl360'                => 'fl360',
    'descobrindo-talentos' => 'peneira',
    'noticias'             => 'noticias',
    'friburgo-no-mapa'     => 'radar',
    'privacidade'          => 'privacidade',
    'descadastrar'         => 'descadastrar',
];
$legacy = [ // endereços antigos ou alternativos -> endereço oficial
    'index.html' => '/', 'index.php' => '/', 'home' => '/',
    'minha-historia.html' => '/minha-historia/', 'fl360.html' => '/fl360/',
    'peneira' => '/descobrindo-talentos/', 'peneira.html' => '/descobrindo-talentos/',
    'contato' => '/friburgo-no-mapa/', 'contato.html' => '/friburgo-no-mapa/', 'demandas' => '/friburgo-no-mapa/', 'radarfriburgo' => '/friburgo-no-mapa/', 'radar' => '/friburgo-no-mapa/', 'friburgonomapa' => '/friburgo-no-mapa/', 'noticias.html' => '/noticias/', 'blog' => '/noticias/', 'feed' => '/noticias/',
];

if (isset($legacy[$slug])) redirect(url($legacy[$slug]) . $qs, 301);
if (preg_match('#^category/([a-z0-9-]+)$#', $slug, $m)) redirect(url('/noticias/') . '?cat=' . $m[1], 301);
if (preg_match('#^\d{4}(/\d{2}){0,2}$#', $slug)) redirect(url('/noticias/'), 301);
// MCP (publicação de artigos pelo Claude): rotas próprias, sem redirecionamento de barra
if (preg_match('#^mcp/([^/]+)/?$#', $slug, $m)) { require ROOT . '/app/mcp.php'; mcp_handle($m[1]); }
if (preg_match('#^api/mcp-upload/([a-f0-9]{64})$#', $slug, $m)) { require ROOT . '/app/mcp.php'; mcp_handle_upload($m[1]); }
if ($slug === 'mcp') render_404();
if ($slug === 'sitemap.xml') { require ROOT . '/app/sitemap.php'; exit; }
if ($slug === 'robots.txt') { require ROOT . '/app/robots.php'; exit; }
if ($slug === 'llms.txt') { require ROOT . '/app/llms.php'; exit; }

// Barra no final, como no WordPress (evita conteúdo duplicado)
if ($slug !== '' && substr($path, -1) !== '/' && strpos($slug, '.') === false) redirect(url('/' . $slug . '/') . $qs, 301);

$meta = [];
if ($slug === 'descadastrar') { require_once ROOT . '/app/tickets.php'; db_migrate(); $meta = ['robots' => 'noindex, nofollow']; header('Cache-Control: no-store'); }
if ($slug === 'friburgo-no-mapa') {
    require_once ROOT . '/app/tickets.php';
    $meta = [
        'title' => 'Friburgo no Mapa – envie sua demanda | Vereador Maicon Gonçalves',
        'image' => abs_url('/img/og-friburgo-no-mapa.jpg') . '?v=2', 'image_w' => 1200, 'image_h' => 630,
        'image_alt' => 'Friburgo no Mapa: envie sua demanda e acompanhe em tempo real',
        'description' => 'Envie sua demanda ao nosso gabinete. Nós conferimos, encaminhamos oficialmente à Prefeitura de Nova Friburgo e você acompanha tudo em tempo real no mapa.',
    ];
}
if (isset($pages[$slug])) {
    include ROOT . '/app/pages/' . $pages[$slug] . '.php';
    exit;
}

if (preg_match('#^[a-z0-9][a-z0-9-]*$#', $slug)) {
    $preview = isset($_GET['preview']);
    $signed = false;
    if ($preview && preg_match('/^\d{9,11}\.[a-f0-9]{64}$/', (string) $_GET['preview'])) { // link assinado (7 dias) gerado pelo MCP
        require_once ROOT . '/app/mcp.php';
        $pp = post_by_slug($slug, true);
        $signed = $pp && mcp_preview_valid((int) $pp['id'], (string) $_GET['preview']);
        $preview = $signed;
    } elseif ($preview) { require ROOT . '/app/auth.php'; $preview = auth_user() !== null && is_admin(); }
    $post = post_by_slug($slug, $preview);
    if ($post && $preview) { header('Cache-Control: no-store'); header('X-Robots-Tag: noindex'); }
    if ($post) {
        $meta = [
            'title' => $post['title'] . ' | Vereador Maicon Gonçalves',
            'description' => str_limit($post['meta_description'] ?: ($post['excerpt'] ?: strip_tags((string) $post['content'])), 160),
            'image' => $post['cover'] ? abs_url('/' . ltrim($post['cover'], '/')) : '',
            'image_w' => $post['cover_w'], 'image_h' => $post['cover_h'],
            'image_alt' => $post['cover_alt'] ?: $post['title'],
            'page' => 'artigo',
            'type' => 'article',
            'robots' => $post['status'] === 'published' ? '' : 'noindex',
        ];
        $others = posts_published(3, (int) $post['id']);
        include ROOT . '/app/pages/noticia.php';
        exit;
    }
}

render_404();
