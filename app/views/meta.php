<?php
defined('APP') or exit;
$__path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (B !== '' && strpos($__path, B) === 0) $__path = substr($__path, strlen(B));
$meta += [
    'canonical' => abs_url($__path ?: '/'),
    'image' => '',
    'image_alt' => 'Vereador Maicon Gonçalves em Nova Friburgo',
    'type' => 'website',
    'robots' => '',
    'page' => '',
];
// imagem padrão para compartilhar: logo em fundo azul (1200x630). Páginas e artigos podem definir a sua.
if (!$meta['image']) { $meta['image'] = abs_url('/img/og-padrao.jpg'); $meta['image_w'] = 1200; $meta['image_h'] = 630; $meta['image_alt'] = 'Vereador Maicon Gonçalves – Nova Friburgo'; }

// dados estruturados de cada tipo de página
$__page = ['@type' => 'WebPage', '@id' => $meta['canonical'] . '#pagina', 'url' => $meta['canonical'], 'name' => $meta['title'], 'description' => $meta['description'],
    'inLanguage' => 'pt-BR', 'isPartOf' => ['@id' => abs_url('/#site')], 'about' => ['@id' => abs_url('/#maicon')]];
$__graph = [];
switch ($meta['page']) {
    case 'home': $__page['@type'] = ['WebPage', 'AboutPage']; $__page['mainEntity'] = ['@id' => abs_url('/#maicon')]; break;
    case 'historia': $__page['@type'] = 'ProfilePage'; $__page['mainEntity'] = ['@id' => abs_url('/#maicon')]; break;
    case 'demandas': $__page['@type'] = 'ContactPage'; break;
    case 'noticias':
        $__page['@type'] = 'CollectionPage';
        $__items = [];
        foreach (array_slice(posts_published(), 0, 30) as $__i => $__p) $__items[] = ['@type' => 'ListItem', 'position' => $__i + 1, 'url' => $__p['abs_url'], 'name' => $__p['title']];
        $__page['mainEntity'] = ['@type' => 'ItemList', 'itemListElement' => $__items];
        break;
    case 'artigo':
        $__graph[] = seo_article($post);
        $__graph[] = seo_breadcrumb([['Início', '/'], ['Notícias', '/noticias/'], [$post['title'], '/' . $post['slug'] . '/']]);
        $__page = null;
        break;
}
if ($meta['page'] !== '' && $meta['page'] !== 'home' && $meta['page'] !== 'artigo' && $meta['page'] !== '404') {
    $__graph[] = seo_breadcrumb([['Início', '/'], [preg_replace('/\s+[–|].*$/u', '', $meta['title']), $__path ?: '/']]);
}
if ($__page && $meta['page'] !== '404') array_unshift($__graph, $__page);
?>
<title><?= e($meta['title']) ?></title>
<?php if ($meta['description'] !== ''): ?><meta name="description" content="<?= e($meta['description']) ?>">
<?php endif; ?>
<meta name="robots" content="<?= e($meta['robots'] !== '' ? $meta['robots'] : 'index, follow, max-image-preview:large, max-snippet:-1') ?>">
<link rel="canonical" href="<?= e($meta['canonical']) ?>">
<meta name="author" content="Vereador Maicon Gonçalves">
<meta name="geo.region" content="BR-RJ">
<meta name="geo.placename" content="Nova Friburgo">
<link rel="icon" href="<?= e(url('/favicon.ico')) ?>" sizes="48x48">
<link rel="icon" href="<?= e(url('/img/icon-192.png')) ?>" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="<?= e(url('/img/apple-touch-icon.png')) ?>">
<link rel="alternate" type="text/plain" href="<?= e(url('/llms.txt')) ?>" title="Resumo do site para assistentes de IA">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="Vereador Maicon Gonçalves">
<meta property="og:type" content="<?= e($meta['type']) ?>">
<meta property="og:title" content="<?= e($meta['title']) ?>">
<?php if ($meta['description'] !== ''): ?><meta property="og:description" content="<?= e($meta['description']) ?>">
<?php endif; ?>
<meta property="og:url" content="<?= e($meta['canonical']) ?>">
<meta property="og:image" content="<?= e($meta['image']) ?>">
<?php if (!empty($meta['image_w'])): ?><meta property="og:image:width" content="<?= (int) $meta['image_w'] ?>"><meta property="og:image:height" content="<?= (int) $meta['image_h'] ?>">
<?php endif; ?>
<meta property="og:image:alt" content="<?= e($meta['image_alt']) ?>">
<?php if ($meta['page'] === 'artigo'): ?>
<meta property="article:published_time" content="<?= e(date('c', strtotime($post['published_at']))) ?>">
<meta property="article:modified_time" content="<?= e(date('c', strtotime($post['updated_at'] ?: $post['published_at']))) ?>">
<?php if ($post['category']): ?><meta property="article:section" content="<?= e($post['category']) ?>">
<?php endif; ?>
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<?= seo_jsonld($__graph) ?>

