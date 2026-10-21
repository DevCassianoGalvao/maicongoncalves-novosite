<?php
defined('APP') or exit;

/**
 * Markdown simples -> HTML para os artigos: títulos (#, ##, ###), parágrafos, **negrito**, *itálico*,
 * [links](url), listas (- e 1.), citações (>) e quebras de linha. O resultado SEMPRE passa por html_sanitize().
 */
function md_inline(string $t): string
{
    $t = e($t);
    $t = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)(?:\s+&quot;[^&]*&quot;)?\)/', function ($m) {
        $href = html_entity_decode($m[2], ENT_QUOTES);
        $ext = preg_match('#^https?://#i', $href) && stripos(parse_url($href, PHP_URL_HOST) ?: '', parse_url(site_url(), PHP_URL_HOST) ?: '-') === false;
        return '<a href="' . e($href) . '"' . ($ext ? ' rel="noopener"' : '') . '>' . $m[1] . '</a>';
    }, $t);
    $t = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $t);
    $t = preg_replace('/(?<![\*\w])\*(?!\s)(.+?)(?<!\s)\*(?![\*\w])/s', '<em>$1</em>', $t);
    $t = preg_replace('/(?<![\w_])_(?!\s)(.+?)(?<!\s)_(?![\w_])/s', '<em>$1</em>', $t);
    return $t;
}

function md_to_html(string $md): string
{
    $md = str_replace(["\r\n", "\r"], "\n", trim($md));
    $md = strip_tags(preg_replace('#<(script|style|iframe|object|embed|svg|form)\b.*?</\1\s*>#is', '', $md)); // HTML solto no Markdown não passa
    $out = [];
    foreach (preg_split('/\n{2,}/', $md) as $block) {
        $block = trim($block, "\n");
        if ($block === '') continue;
        $lines = explode("\n", $block);
        // títulos (um título pode vir colado ao parágrafo seguinte)
        $buf = [];
        $flush = function () use (&$buf, &$out) {
            if (!$buf) return;
            $first = $buf[0];
            if (preg_match('/^\s*[-*+]\s+/', $first)) {
                $out[] = '<ul>' . implode('', array_map(function ($l) { return '<li>' . md_inline(preg_replace('/^\s*[-*+]\s+/', '', $l)) . '</li>'; }, $buf)) . '</ul>';
            } elseif (preg_match('/^\s*\d+[.)]\s+/', $first)) {
                $out[] = '<ol>' . implode('', array_map(function ($l) { return '<li>' . md_inline(preg_replace('/^\s*\d+[.)]\s+/', '', $l)) . '</li>'; }, $buf)) . '</ol>';
            } elseif (preg_match('/^\s*>/', $first)) {
                $out[] = '<blockquote><p>' . implode('<br>', array_map(function ($l) { return md_inline(preg_replace('/^\s*>\s?/', '', $l)); }, $buf)) . '</p></blockquote>';
            } else {
                $out[] = '<p>' . implode('<br>', array_map('md_inline', array_map('trim', $buf))) . '</p>';
            }
            $buf = [];
        };
        $kind = function ($l) {
            if (preg_match('/^\s*[-*+]\s+/', $l)) return 'ul';
            if (preg_match('/^\s*\d+[.)]\s+/', $l)) return 'ol';
            if (preg_match('/^\s*>/', $l)) return 'bq';
            return 'p';
        };
        foreach ($lines as $l) {
            if (preg_match('/^(#{1,6})\s+(.+?)\s*#*$/', $l, $m)) {
                $flush();
                $lvl = strlen($m[1]) <= 2 ? 2 : 3; // o título do artigo já é o h1 da página
                $out[] = "<h$lvl>" . md_inline($m[2]) . "</h$lvl>";
                continue;
            }
            if ($buf && $kind($l) !== $kind($buf[0])) $flush();
            $buf[] = $l;
        }
        $flush();
    }
    return implode("\n", $out);
}
