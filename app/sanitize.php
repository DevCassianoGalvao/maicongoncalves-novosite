<?php
defined('APP') or exit;

/**
 * Limpa o HTML dos artigos: só passam tags e atributos de texto comuns.
 * Remove scripts, eventos (onclick…), links "javascript:", formulários e iframes
 * que não sejam de YouTube, Vimeo, Instagram, Facebook ou Google Maps.
 */
function html_sanitize(?string $html): string
{
    $html = (string) $html;
    if (trim($html) === '') return '';
    static $allowed = [
        'p' => ['style'], 'h2' => ['style'], 'h3' => ['style'], 'h4' => ['style'], 'br' => [], 'hr' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'sub' => [], 'sup' => [], 'span' => ['style'],
        'blockquote' => [], 'ul' => [], 'ol' => [], 'li' => ['style'], 'code' => [], 'pre' => [],
        'a' => ['href', 'target', 'rel', 'title'], 'img' => ['src', 'alt', 'width', 'height', 'title'],
        'figure' => ['class'], 'figcaption' => [],
        'iframe' => ['src', 'width', 'height', 'title', 'allow', 'allowfullscreen', 'frameborder'],
        'table' => [], 'thead' => [], 'tbody' => [], 'tfoot' => [], 'tr' => [], 'th' => ['colspan', 'rowspan', 'style'], 'td' => ['colspan', 'rowspan', 'style'], 'caption' => [],
    ];
    static $drop = ['script', 'style', 'object', 'embed', 'applet', 'form', 'input', 'button', 'textarea', 'select', 'option',
        'svg', 'math', 'link', 'meta', 'base', 'noscript', 'template', 'frame', 'frameset', 'audio', 'video', 'source', 'canvas', 'dialog'];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="san-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    $root = $doc->getElementById('san-root');
    if (!$root) return '';

    $walk = function (DOMNode $node) use (&$walk, $allowed, $drop, $doc) {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) { $node->removeChild($child); continue; }
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $tag = strtolower($child->nodeName);
            if (in_array($tag, $drop, true)) { $node->removeChild($child); continue; }
            if (!isset($allowed[$tag])) { // tag desconhecida: mantém só o conteúdo
                $walk($child);
                while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                $node->removeChild($child);
                continue;
            }
            foreach (iterator_to_array($child->attributes) as $at) {
                $name = strtolower($at->name);
                $val = trim($at->value);
                $ok = in_array($name, $allowed[$tag], true);
                if ($ok && in_array($name, ['href', 'src'], true)) $ok = safe_url($val, $tag);
                if ($ok && $name === 'style') { $val = safe_style($val); $ok = $val !== ''; if ($ok) $child->setAttribute('style', $val); }
                if ($ok && $name === 'target') $ok = $val === '_blank';
                if (!$ok) $child->removeAttribute($at->name);
            }
            if ($tag === 'iframe' && !$child->hasAttribute('src')) { $node->removeChild($child); continue; }
            if ($tag === 'img' && !$child->hasAttribute('src')) { $node->removeChild($child); continue; }
            if ($tag === 'a' && $child->getAttribute('target') === '_blank') $child->setAttribute('rel', 'noopener noreferrer');
            if ($tag === 'img' && !$child->hasAttribute('loading')) $child->setAttribute('loading', 'lazy');
            $walk($child);
        }
    };
    $walk($root);

    $out = '';
    foreach ($root->childNodes as $n) $out .= $doc->saveHTML($n);
    return $out;
}

function safe_url(string $url, string $tag): bool
{
    $u = preg_replace('/[\x00-\x20]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($u === '') return false;
    if ($tag === 'iframe') {
        if (!preg_match('~^https://([^/?#]+)~i', $u, $m)) return false;
        return (bool) preg_match('/(^|\.)(youtube\.com|youtube-nocookie\.com|vimeo\.com|instagram\.com|facebook\.com|google\.com)$/i', $m[1]);
    }
    if ($u[0] === '/' || $u[0] === '#') return strpos($u, '//') !== 0 || $tag === 'img';
    if ($tag === 'img') return (bool) preg_match('#^https?://#i', $u);
    return (bool) preg_match('#^(https?://|mailto:|tel:)#i', $u);
}

function safe_style(string $style): string
{
    $keep = [];
    foreach (explode(';', $style) as $rule) {
        $rule = strtolower(trim($rule));
        if (preg_match('/^text-align\s*:\s*(left|center|right|justify)$/', $rule, $m)) $keep[] = 'text-align: ' . $m[1];
        elseif (preg_match('/^text-decoration\s*:\s*(underline|line-through)$/', $rule, $m)) $keep[] = 'text-decoration: ' . $m[1];
    }
    return implode('; ', $keep);
}
