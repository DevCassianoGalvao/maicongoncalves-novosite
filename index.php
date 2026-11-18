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
    'fiscalize-voce'       => 'fiscalize',
    'privacidade'          => 'privacidade',
    'descadastrar'         => 'descadastrar',
];
$legacy = [ // endereços antigos ou alternativos -> endereço oficial
    'index.html' => '/', 'index.php' => '/', 'home' => '/',
    'minha-historia.html' => '/minha-historia/', 'fl360.html' => '/fl360/',
    'peneira' => '/descobrindo-talentos/', 'peneira.html' => '/descobrindo-talentos/',
    'contato' => '/friburgo-no-mapa/', 'contato.html' => '/friburgo-no-mapa/', 'demandas' => '/friburgo-no-mapa/', 'radarfriburgo' => '/friburgo-no-mapa/', 'radar' => '/friburgo-no-mapa/', 'friburgonomapa' => '/friburgo-no-mapa/', 'impostometro' => '/impostometro-nova-friburgo/', 'noticias.html' => '/noticias/', 'blog' => '/noticias/', 'feed' => '/noticias/',
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
if ($slug === 'fiscalize-voce') {
    // Página oculta até o gabinete liberar (flag pagina_publica no banco). Oculta: 404 para quem não está logado no painel.
    require_once ROOT . '/app/fiscalize.php';
    require_once ROOT . '/app/auth.php';
    $fvOpen = fv_is_public();
    if (!$fvOpen && (!db_or_null() || auth_user() === null)) render_404();
    header('Cache-Control: ' . ($fvOpen ? 'public, max-age=300' : 'no-store'));
    $meta = [
        'title' => 'Fiscalize Você – compras da Prefeitura de Nova Friburgo | Vereador Maicon Gonçalves',
        'description' => 'Veja as compras da Prefeitura de Nova Friburgo, com o valor de cada unidade calculado a partir dos dados públicos do PNCP.',
        'robots' => $fvOpen ? '' : 'noindex, nofollow',
    ];
}
if ($slug === 'impostometro-nova-friburgo') {
    // Link aberto, fora do menu e fora do Google até o gabinete decidir publicar.
    require_once ROOT . '/app/impostometro.php';
    header('Cache-Control: public, max-age=300');
    header('X-Robots-Tag: noindex');
    $meta = [
        'title' => 'Impostômetro de Nova Friburgo | Vereador Maicon Gonçalves',
        'description' => 'Quanto Nova Friburgo pagou de impostos à Prefeitura e como o dinheiro foi usado, com dados oficiais do Tesouro Nacional.',
        'robots' => 'noindex, nofollow',
    ];
    include ROOT . '/app/pages/impostometro.php';
    exit;
}
if ($slug === 'amigos-do-bem' || strpos($slug, 'amigos-do-bem/') === 0) {
    // Amigos do Bem: link aberto conforme a configuração (painel); fora do menu; no Google só se "indexar" estiver ligado.
    require_once ROOT . '/app/amigos.php';
    require_once ROOT . '/app/auth.php';
    $abOpen = ab_is_public();
    $abIdx = $abOpen && ab_cfg('indexar') === '1';
    $abLogged = db_or_null() && auth_user() !== null;
    // link secreto da instituição para atualizar as necessidades (funciona mesmo com a página fechada)
    if (preg_match('#^amigos-do-bem/atualizar/([a-f0-9]{32})$#', $slug, $m)) {
        ab_tables();
        $abRow = q('SELECT slug FROM ab_instituicoes WHERE token = ? AND demo = 0', [$m[1]])->fetch();
        if (!$abRow) render_404();
        $abInst = ab_get($abRow['slug'], true);
        $abSaved = isset($_GET['ok']);
        if (is_post() && hash_equals($m[1], (string) ($_POST['_t'] ?? ''))) {
            if (!rate_ok('ab-upd', 30, 3600)) { http_response_code(429); exit('Muitas atualizações seguidas. Tente mais tarde.'); }
            ab_save_from_token($abInst, $_POST);
            redirect(url('/amigos-do-bem/atualizar/' . $m[1] . '/') . '?ok=1', 303);
        }
        header('Cache-Control: no-store');
        header('X-Robots-Tag: noindex');
        $meta = ['title' => 'Atualizar necessidades – ' . $abInst['nome'], 'robots' => 'noindex, nofollow'];
        include ROOT . '/app/pages/amigos-atualizar.php';
        exit;
    }
    if (!$abOpen && !$abLogged) render_404();
    header('Cache-Control: ' . ($abOpen ? 'public, max-age=120' : 'no-store'));
    if ($slug === 'amigos-do-bem') {
        $meta = [
            'title' => 'Amigos do Bem – onde doar em Nova Friburgo | Vereador Maicon Gonçalves',
            'description' => 'Encontre instituições de Nova Friburgo que recebem doações: o que cada uma precisa, endereço, horários e contato. A doação é feita direto à instituição.',
            'robots' => $abIdx ? '' : 'noindex, nofollow',
        ];
        include ROOT . '/app/pages/amigos.php';
        exit;
    }
    if (preg_match('#^amigos-do-bem/([a-z0-9-]+)$#', $slug, $m)) {
        $abInst = ab_get($m[1], $abLogged);
        if (!$abInst) render_404();
        $meta = [
            'title' => $abInst['nome'] . ' – como doar | Amigos do Bem',
            'description' => str_limit((string) ($abInst['resumo'] ?: $abInst['nome'] . ': veja o que a instituição precisa e como doar.'), 160),
            'image' => $abInst['capa'] ? abs_url('/' . ltrim($abInst['capa'], '/')) : '',
            'image_alt' => $abInst['nome'],
            'robots' => ($abIdx && $abInst['status'] === 'publicada' && !$abInst['demo']) ? '' : 'noindex, nofollow',
        ];
        include ROOT . '/app/pages/amigos-inst.php';
        exit;
    }
    render_404();
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
