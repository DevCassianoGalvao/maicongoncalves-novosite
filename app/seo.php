<?php
defined('APP') or exit;

/* =========================================================
   Dados estruturados (schema.org) — ajudam Google e IAs a
   entender quem é o vereador, onde atua e o que é cada página.
   Tudo aqui vem do conteúdo do próprio site.
   ========================================================= */

const PERSON_DESC = 'Maicon Gonçalves é vereador de Nova Friburgo (RJ), eleito em 2024. Professor de Educação Física há quase 20 anos no município, pós-graduado em Administração e Marketing Esportivo, atua pela educação, esporte, inclusão, mobilidade urbana, juventude e transparência no uso do dinheiro público.';

function seo_person(): array
{
    $same = [];
    foreach (array_keys(SOCIALS) as $k) if (S($k) !== '') $same[] = S($k);
    $addr = address_lines();
    $tel = preg_replace('/\D+/', '', S('whatsapp'));
    return [
        '@type' => 'Person',
        '@id' => abs_url('/#maicon'),
        'name' => 'Maicon Gonçalves',
        'alternateName' => ['Vereador Maicon Gonçalves', 'Maicon Gonçalves vereador'],
        'jobTitle' => 'Vereador de Nova Friburgo',
        'description' => PERSON_DESC,
        'url' => abs_url('/'),
        'image' => abs_url('/img/hero-retrato.webp'),
        'sameAs' => $same,
        'nationality' => 'BR',
        'homeLocation' => ['@type' => 'City', 'name' => 'Nova Friburgo', 'containedInPlace' => ['@type' => 'State', 'name' => 'Rio de Janeiro']],
        'knowsAbout' => ['Educação', 'Esporte', 'Inclusão', 'Mobilidade urbana', 'Juventude', 'Transparência pública', 'Futebol de base', 'Formação cidadã', 'Nova Friburgo'],
        'award' => 'Medalha Manoel José Gomes Tubino (2025)',
        'worksFor' => [
            '@type' => 'GovernmentOrganization',
            'name' => 'Câmara Municipal de Nova Friburgo',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => trim(($addr[0] ?? '') . (S('office') !== '' ? ' – ' . S('office') : '')),
                'addressLocality' => 'Nova Friburgo',
                'addressRegion' => 'RJ',
                'postalCode' => preg_match('/\d{5}-?\d{3}/', S('address'), $m) ? $m[0] : '',
                'addressCountry' => 'BR',
            ],
        ],
        'contactPoint' => array_filter([
            '@type' => 'ContactPoint',
            'contactType' => 'Gabinete do vereador',
            'email' => S('email') ?: null,
            'telephone' => $tel ? '+' . $tel : null,
            'areaServed' => 'Nova Friburgo, RJ',
            'availableLanguage' => 'Portuguese',
            'url' => abs_url('/contato/'),
        ]),
    ];
}

function seo_website(): array
{
    return [
        '@type' => 'WebSite',
        '@id' => abs_url('/#site'),
        'url' => abs_url('/'),
        'name' => 'Vereador Maicon Gonçalves',
        'description' => 'Site oficial do vereador Maicon Gonçalves, de Nova Friburgo (RJ): ações do mandato, projetos, notícias e contato com o gabinete.',
        'inLanguage' => 'pt-BR',
        'publisher' => ['@id' => abs_url('/#maicon')],
        'about' => ['@id' => abs_url('/#maicon')],
    ];
}

function seo_breadcrumb(array $items): array
{
    $list = [];
    foreach ($items as $i => [$name, $path]) $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => abs_url($path)];
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
}

function seo_article(array $p): array
{
    $img = [];
    if ($p['cover']) $img[] = array_filter(['@type' => 'ImageObject', 'url' => abs_url('/' . ltrim($p['cover'], '/')), 'width' => $p['cover_w'] ?: null, 'height' => $p['cover_h'] ?: null]);
    foreach ($p['gallery'] as $g) $img[] = abs_url('/' . ltrim($g['src'], '/'));
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $p['content']), ENT_QUOTES, 'UTF-8')));
    return array_filter([
        '@type' => 'NewsArticle',
        '@id' => $p['abs_url'] . '#artigo',
        'headline' => mb_substr($p['title'], 0, 110),
        'description' => $p['meta_description'] ?: $p['excerpt'],
        'datePublished' => date('c', strtotime($p['published_at'])),
        'dateModified' => date('c', strtotime($p['updated_at'] ?: $p['published_at'])),
        'author' => ['@id' => abs_url('/#maicon')],
        'publisher' => ['@id' => abs_url('/#maicon')],
        'mainEntityOfPage' => $p['abs_url'],
        'url' => $p['abs_url'],
        'image' => $img ?: null,
        'articleSection' => $p['category'] ?: null,
        'inLanguage' => 'pt-BR',
        'wordCount' => $text !== '' ? count(preg_split('/\s+/u', $text)) : null,
        'contentLocation' => ['@type' => 'City', 'name' => 'Nova Friburgo', 'address' => ['@type' => 'PostalAddress', 'addressRegion' => 'RJ', 'addressCountry' => 'BR']],
        'isPartOf' => ['@id' => abs_url('/#site')],
    ]);
}

/** <script type="application/ld+json"> com o grafo da página. */
function seo_jsonld(array $extra = []): string
{
    $graph = array_merge([seo_website(), seo_person()], $extra);
    $json = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    return '<script type="application/ld+json">' . $json . '</script>';
}
