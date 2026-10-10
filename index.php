<?php
/**
 * Roteador do site. O .htaccess manda para cá tudo o que não é arquivo/pasta real.
 * Mantém os mesmos endereços do site antigo em WordPress (ex.: /contato/, /slug-da-noticia/).
 */
require __DIR__ . '/app/bootstrap.php';

$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if (B !== '' && strpos($path, B) === 0) $path = substr($path, strlen(B));
$slug = trim($path, '/');
$qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';

$pages = [
    ''                     => 'home',
    'minha-historia'       => 'historia',
    'fl360'                => 'fl360',
    'descobrindo-talentos' => 'peneira',
    'contato'              => 'contato',
    'noticias'             => 'noticias',
];
$legacy = [ // endereços antigos ou alternativos -> endereço oficial
    'index.html' => '/', 'index.php' => '/', 'home' => '/',
    'minha-historia.html' => '/minha-historia/', 'fl360.html' => '/fl360/',
    'peneira' => '/descobrindo-talentos/', 'peneira.html' => '/descobrindo-talentos/',
    'contato.html' => '/contato/', 'noticias.html' => '/noticias/', 'blog' => '/noticias/', 'feed' => '/noticias/',
];

if (isset($legacy[$slug])) redirect(url($legacy[$slug]) . $qs, 301);
if (preg_match('#^category/([a-z0-9-]+)$#', $slug, $m)) redirect(url('/noticias/') . '?cat=' . $m[1], 301);
if (preg_match('#^\d{4}(/\d{2}){0,2}$#', $slug)) redirect(url('/noticias/'), 301);
if ($slug === 'sitemap.xml') { require ROOT . '/app/sitemap.php'; exit; }

// Barra no final, como no WordPress (evita conteúdo duplicado)
if ($slug !== '' && substr($path, -1) !== '/' && strpos($slug, '.') === false) redirect(url('/' . $slug . '/') . $qs, 301);

$meta = [];
if (isset($pages[$slug])) {
    include ROOT . '/app/pages/' . $pages[$slug] . '.php';
    exit;
}

if (preg_match('#^[a-z0-9][a-z0-9-]*$#', $slug)) {
    $preview = isset($_GET['preview']);
    if ($preview) { require ROOT . '/app/auth.php'; $preview = auth_user() !== null; }
    $post = post_by_slug($slug, $preview);
    if ($post) {
        $meta = [
            'title' => $post['title'] . ' – Vereador Maicon Gonçalves',
            'description' => str_limit($post['meta_description'] ?: ($post['excerpt'] ?: strip_tags((string) $post['content'])), 160),
            'image' => $post['cover'] ? abs_url('/' . ltrim($post['cover'], '/')) : '',
            'type' => 'article',
            'robots' => $post['status'] === 'published' ? '' : 'noindex',
        ];
        $others = posts_published(3, (int) $post['id']);
        include ROOT . '/app/pages/noticia.php';
        exit;
    }
}

render_404();
