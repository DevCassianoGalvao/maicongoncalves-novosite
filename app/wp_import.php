<?php
defined('APP') or exit;
require_once __DIR__ . '/images.php';

/** GET simples com cURL. Retorna [corpo, código http]. */
function http_get(string $url, int $timeout = 40): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 4,
        CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'SiteVereadorMaiconGoncalves-Importador/1.0',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$body === false ? '' : (string) $body, $code];
}

/** Lê todos os artigos publicados de um WordPress pela API REST. */
function wp_fetch_posts(string $base): array
{
    $base = rtrim($base, '/');
    $all = [];
    for ($page = 1; $page <= 20; $page++) {
        [$body, $code] = http_get($base . '/wp-json/wp/v2/posts?per_page=100&_embed=1&page=' . $page);
        if ($code === 400 && $page > 1) break; // passou da última página
        if ($code !== 200) throw new RuntimeException('Não consegui ler ' . $base . '/wp-json/ (resposta ' . $code . '). Confira o endereço.');
        $list = json_decode($body, true);
        if (!is_array($list)) throw new RuntimeException('Resposta inesperada do WordPress.');
        $all = array_merge($all, $list);
        if (count($list) < 100) break;
    }
    return $all;
}

function wp_text(string $html): string { return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }

/**
 * Limpa o HTML do editor do WordPress: tira classes/estilos, parágrafos vazios,
 * separa a linha fina (primeiro subtítulo) e coleta as imagens do corpo para a galeria.
 * Retorna [linha fina, html, [urls das imagens]].
 */
function wp_clean(string $html): array
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="wp-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('wp-root');
    $xp = new DOMXPath($doc);

    // imagens -> galeria
    $images = [];
    foreach (iterator_to_array($xp->query('.//img', $root)) as $img) {
        $src = $img->getAttribute('data-orig-file') ?: $img->getAttribute('src');
        if ($img->getAttribute('srcset')) {
            $best = 0;
            foreach (explode(',', $img->getAttribute('srcset')) as $cand) {
                $p = preg_split('/\s+/', trim($cand));
                if (isset($p[1]) && (int) $p[1] > $best) { $best = (int) $p[1]; $src = $p[0]; }
            }
        }
        if ($src) $images[] = preg_replace('/-\d+x\d+(\.[a-z]+)$/i', '$1', $src); // versão original, sem o "-300x200"
        $box = $img;
        while ($box->parentNode && $box->parentNode !== $root && in_array(strtolower($box->parentNode->nodeName), ['a', 'figure', 'div', 'p'], true)) $box = $box->parentNode;
        if ($box->parentNode) $box->parentNode->removeChild($box);
    }
    // limpa atributos
    foreach (iterator_to_array($xp->query('.//*', $root)) as $el) {
        $keep = ['a' => ['href', 'target', 'rel'], 'iframe' => ['src', 'title', 'allowfullscreen', 'allow']][strtolower($el->nodeName)] ?? [];
        foreach (iterator_to_array($el->attributes) as $at) if (!in_array($at->name, $keep, true)) $el->removeAttribute($at->name);
    }
    // títulos h1/h4+ viram h2/h3, parágrafos vazios saem
    foreach (iterator_to_array($xp->query('.//p|.//h2|.//h3|.//figure|.//div', $root)) as $el) {
        if ($el->parentNode && trim(str_replace("\xC2\xA0", ' ', $el->textContent)) === '' && !$xp->query('.//iframe', $el)->length) $el->parentNode->removeChild($el);
    }
    $lead = '';
    $first = null;
    foreach ($root->childNodes as $n) if ($n->nodeType === XML_ELEMENT_NODE) { $first = $n; break; }
    if ($first && in_array(strtolower($first->nodeName), ['h2', 'h3'], true)) {
        $lead = trim(preg_replace('/\s+/u', ' ', $first->textContent));
        $root->removeChild($first);
    }
    $out = '';
    foreach ($root->childNodes as $n) $out .= $doc->saveHTML($n);
    $out = preg_replace(['/<(\/?)h[1]>/i', '/<(\/?)h[4-6]>/i', '/\n{3,}/', '/&nbsp;/'], ['<$1h2>', '<$1h3>', "\n\n", ' '], $out);
    return [$lead, trim($out), array_values(array_unique($images))];
}

/** Baixa uma imagem e salva em uploads/. Retorna ['src','w','h'] ou null. */
function wp_download_image(string $url): ?array
{
    [$body, $code] = http_get($url, 60);
    if ($code !== 200 || $body === '') return null;
    $tmp = tempnam(sys_get_temp_dir(), 'wpimg');
    file_put_contents($tmp, $body);
    try { return img_store_upload($tmp); } catch (Throwable $ex) { return null; } finally { @unlink($tmp); }
}

/** Importa um artigo do WordPress. Retorna mensagem do que foi feito. */
function wp_import_post(array $p, int $authorId): string
{
    $slug = $p['slug'];
    if (q('SELECT COUNT(*) FROM posts WHERE slug = ?', [$slug])->fetchColumn()) return 'já existia';
    @set_time_limit(120);

    // categoria (a primeira que não for "Sem categoria")
    $catId = null;
    foreach ($p['_embedded']['wp:term'] ?? [] as $group) foreach ($group as $t) {
        if (($t['taxonomy'] ?? '') !== 'category' || in_array($t['slug'], ['uncategorized', 'sem-categoria'], true)) continue;
        $name = wp_text($t['name']);
        $found = q('SELECT id FROM categories WHERE slug = ?', [$t['slug']])->fetchColumn();
        if (!$found) { q('INSERT INTO categories (name, slug, created_at) VALUES (?, ?, NOW())', [$name, $t['slug']]); $found = db()->lastInsertId(); }
        $catId = (int) $found;
        break 2;
    }

    [$lead, $html, $imgs] = wp_clean((string) ($p['content']['rendered'] ?? ''));
    if ($lead === '') $lead = str_limit(wp_text((string) ($p['excerpt']['rendered'] ?? '')), 300);

    $cover = null;
    $media = $p['_embedded']['wp:featuredmedia'][0] ?? null;
    if ($media && !empty($media['source_url'])) $cover = wp_download_image($media['source_url']);
    $gallery = [];
    foreach ($imgs as $u) if ($g = wp_download_image($u)) $gallery[] = $g;

    $date = date('Y-m-d H:i:s', strtotime($p['date']));
    q('INSERT INTO posts (title, slug, excerpt, content, cover, cover_w, cover_h, cover_alt, gallery, category_id, status, published_at, meta_description, author_id, created_at, updated_at)
       VALUES (?,?,?,?,?,?,?,?,?,?,\'published\',?,?,?,NOW(),NOW())', [
        wp_text($p['title']['rendered']), $slug, $lead, $html,
        $cover['src'] ?? null, $cover['w'] ?? null, $cover['h'] ?? null, wp_text((string) ($media['alt_text'] ?? '')),
        $gallery ? json_encode($gallery, JSON_UNESCAPED_SLASHES) : null, $catId, $date, '', $authorId,
    ]);
    return 'importado' . ($cover ? ' com capa' : ' sem capa') . ($gallery ? ' e ' . count($gallery) . ' fotos na galeria' : '');
}
