<?php
defined('APP') or exit;
header('Content-Type: application/xml; charset=utf-8');
$urls = [['/', null], ['/minha-historia/', null], ['/fl360/', null], ['/descobrindo-talentos/', null], ['/noticias/', null], ['/contato/', null]];
foreach (posts_published() as $p) $urls[] = ['/' . $p['slug'] . '/', $p['updated_at'] ?: $p['published_at']];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$u, $mod]) {
    echo '  <url><loc>' . e(abs_url($u)) . '</loc>' . ($mod ? '<lastmod>' . date('Y-m-d', strtotime($mod)) . '</lastmod>' : '') . "</url>\n";
}
echo "</urlset>\n";
