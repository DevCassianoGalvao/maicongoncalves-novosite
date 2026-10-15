<?php
defined('APP') or exit;
header('Content-Type: application/xml; charset=utf-8');
$mod = function (string $page) { $f = ROOT . '/app/pages/' . $page . '.php'; return is_file($f) ? date('Y-m-d', filemtime($f)) : null; };
$posts = posts_published();
$latest = $posts ? date('Y-m-d', strtotime($posts[0]['updated_at'] ?: $posts[0]['published_at'])) : null;
$urls = [
    ['/', max($mod('home'), (string) $latest), [abs_url('/img/hero-retrato.webp')]],
    ['/minha-historia/', $mod('historia'), []], ['/fl360/', $mod('fl360'), []], ['/descobrindo-talentos/', $mod('peneira'), []],
    ['/noticias/', $latest, []], ['/demandas/', $mod('radar'), []], ['/privacidade/', $mod('privacidade'), []],
];
foreach ($posts as $p) {
    $imgs = $p['cover'] ? [abs_url('/' . ltrim($p['cover'], '/'))] : [];
    foreach ($p['gallery'] as $g) $imgs[] = abs_url('/' . ltrim($g['src'], '/'));
    $urls[] = ['/' . $p['slug'] . '/', date('Y-m-d', strtotime($p['updated_at'] ?: $p['published_at'])), $imgs];
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
foreach ($urls as [$u, $lm, $imgs]) {
    echo '  <url><loc>' . e(abs_url($u)) . '</loc>' . ($lm ? '<lastmod>' . $lm . '</lastmod>' : '');
    foreach (array_slice($imgs, 0, 10) as $i) echo '<image:image><image:loc>' . e($i) . '</image:loc></image:image>';
    echo "</url>\n";
}
echo "</urlset>\n";
