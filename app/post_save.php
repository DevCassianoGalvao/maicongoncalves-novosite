<?php
defined('APP') or exit;
/** Regras de endereço (slug) dos artigos, usadas pelo painel e pelo MCP. */

const RESERVED_SLUGS = ['minha-historia', 'fl360', 'descobrindo-talentos', 'contato', 'demandas', 'friburgo-no-mapa', 'friburgonomapa', 'radarfriburgo', 'radar', 'privacidade', 'descadastrar', 'noticias', 'admin', 'install', 'uploads', 'img', 'api', 'app', 'storage', 'category', 'feed', 'sitemap', 'peneira', 'blog', 'home', 'index', 'mcp'];

function unique_slug(string $slug, int $id): string
{
    $base = $slug !== '' ? $slug : 'artigo';
    $slug = in_array($base, RESERVED_SLUGS, true) ? $base . '-1' : $base;
    $n = 1;
    while (q('SELECT COUNT(*) FROM posts WHERE slug = ? AND id <> ?', [$slug, $id])->fetchColumn()) $slug = $base . '-' . (++$n);
    return $slug;
}
