#!/usr/bin/env python3
"""Gera as páginas PHP do site a partir de site/src.

    python3 site/build.py

Saída:
  app/pages/*.php   páginas (layout + conteúdo), incluídas pelo roteador /index.php
  app/icons.php     ícones usados pelo PHP (função icon())
  site.css, site.js estilos e scripts públicos (raiz)
  install/seed.json artigos, categorias e configurações iniciais para o instalador

Precisa dos ícones Phosphor: cd tools && npm install
"""
import json, re, os, html, shutil, glob, hashlib

SITE = os.path.dirname(os.path.abspath(__file__))
REPO = os.path.dirname(SITE)
SRC = os.path.join(SITE, 'src')
PAGES = os.path.join(REPO, 'app', 'pages')
ICONS = os.path.join(REPO, 'tools', 'node_modules', '@phosphor-icons', 'core', 'assets')
e = html.escape


def read(n): return open(os.path.join(SRC, n), encoding='utf-8').read()


def svg(name, kind='i'):
    w = {'i': 'regular', 'ib': 'bold', 'if': 'fill'}[kind]
    f = os.path.join(ICONS, w, name + ('' if w == 'regular' else '-' + w) + '.svg')
    inner = re.search(r'<svg[^>]*>(.*)</svg>', open(f, encoding='utf-8').read(), re.S).group(1)
    return f'<svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true">{inner}</svg>'


SCR = read('_scr.html')


def render(t, **kw):
    t = t.replace('{{scr}}', SCR)
    for k, v in kw.items(): t = t.replace('{{' + k + '}}', v)
    t = re.sub(r'\{\{(i|ib|if):([a-z0-9-]+)\}\}', lambda m: svg(m.group(2), m.group(1)), t)
    left = re.findall(r'\{\{[^}]+\}\}', t)
    assert not left, left
    return t


def phps(s): return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"


def version(f): return hashlib.md5(open(os.path.join(SRC, f), 'rb').read()).hexdigest()[:8]


def paths(t):
    """Links e arquivos passam a respeitar a subpasta do site (constante B)."""
    t = re.sub(r'((?:href|src|data-full|action)=")/(?!/)', r'\1<?= B ?>/', t)
    t = re.sub(r'((?:src|data-full)=")img/', r'\1<?= B ?>/img/', t)
    t = t.replace('href="site.css"', f'href="<?= B ?>/site.css?v={version("site.css")}"')
    t = t.replace('src="site.js"', f'src="<?= B ?>/site.js?v={version("site.js")}"')
    return t


HEAD, HEADER, FOOTER = read('_head.html'), read('_header.html'), read('_footer.html')
NAV = ('home', 'hist', 'fl', 'pen', 'news', 'cont')


def page(name, title, desc, body, nav='', intro='false', ft_top=''):
    head = render(HEAD, intro=intro)
    header = render(HEADER, **{'on_' + k: ('is-on' if nav == k else '') for k in NAV})
    footer = render(FOOTER, ft_top=ft_top)
    doc = f'''<?php
defined('APP') or exit;
$meta = ($meta ?? []) + ['title' => {phps(title)}, 'description' => {phps(desc)}];
?><!doctype html>
<html lang="pt-BR" data-base="<?= e(B) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php require ROOT . '/app/views/meta.php'; ?>
{head}</head>
<body>
{header}
{render(body)}
{footer}
</body>
</html>
'''
    open(os.path.join(PAGES, name + '.php'), 'w', encoding='utf-8').write(paths(doc))


os.makedirs(PAGES, exist_ok=True)
for f in glob.glob(os.path.join(PAGES, '*.php')): os.remove(f)

bairros = read('_bairros.txt').split('\n')
bopts = '<option value="">' + e(bairros[0]) + '</option>' + ''.join(f'<option>{e(b)}</option>' for b in bairros[1:] if b.strip())
T = 'Vereador Maicon Gonçalves'
page('home', T, 'Ações, projetos e atividades do mandato do vereador Maicon Gonçalves em Nova Friburgo, RJ.', read('index.html'), nav='home', intro='true')
page('historia', 'Minha história – ' + T, 'A caminhada que me trouxe até aqui.', read('minha-historia.html'), nav='hist', ft_top='on-white')
page('fl360', 'FL360 – ' + T, 'Formação cidadã e compreensão da gestão pública.', read('fl360.html'), nav='fl', ft_top='on-white')
page('peneira', 'Descobrindo talentos – ' + T, 'O maior movimento de captação do futebol de base da Serra Fluminense.', read('peneira.html'), nav='pen', ft_top='on-white')
page('contato', 'Contato – ' + T, 'O mandato é construído com você.', read('contato.html').replace('{{bairros}}', bopts), nav='cont')
page('noticias', 'Notícias – ' + T, 'Acompanhe as ações, projetos e conquistas do mandato.', read('noticias.html'), nav='news')
page('noticia', T, '', read('noticia.html'), nav='news')
page('404', 'Página não encontrada – ' + T, '', read('404.html'))

for f in ('site.css', 'site.js'): shutil.copy(os.path.join(SRC, f), os.path.join(REPO, f))

# ícones pedidos pelo PHP: icon('nome') ou icon('nome', 'bold'|'fill')
need = {(n, 'i') for n in ('facebook-logo', 'instagram-logo', 'tiktok-logo', 'youtube-logo',  # redes (content.php)
                           'squares-four', 'newspaper', 'plus-circle', 'tag', 'gear-six', 'user-circle',  # menu do painel
                           'check-circle', 'warning-circle')}
for f in glob.glob(os.path.join(REPO, '**', '*.php'), recursive=True):
    if '/vendor/' in f or '/app/pages/' in f: continue
    for name, w in re.findall(r"icon\('([a-z0-9-]+)'(?:,\s*'(bold|fill)')?\)", open(f, encoding='utf-8').read()):
        need.add((name, {'': 'i', 'bold': 'ib', 'fill': 'if'}[w]))
with open(os.path.join(REPO, 'app', 'icons.php'), 'w', encoding='utf-8') as out:
    out.write("<?php\n// Gerado por site/build.py. Não edite à mão.\nreturn [\n")
    for name, k in sorted(need):
        key = name + {'i': '', 'ib': '-bold', 'if': '-fill'}[k]
        out.write(f"    '{key}' => '{svg(name, k)}',\n")
    out.write("];\n")

# conteúdo inicial para o instalador (os 6 artigos do site antigo)
POSTS = json.load(open(os.path.join(REPO, 'content', 'posts.json'), encoding='utf-8'))
seed = {'categories': [], 'posts': []}
for i, p in enumerate(POSTS):
    blocks = p['blocks']
    lead = blocks[0]['text'] if blocks and blocks[0]['tag'] == 'h2' else (p['description'] or '')
    if blocks and blocks[0]['tag'] == 'h2': blocks = blocks[1:]
    parts = []
    for b in blocks:
        t = e(b['text'], quote=False)
        if b['tag'] in ('h2', 'h3', 'h4'): parts.append(f'<h2>{t[0] + t[1:].lower() if t.isupper() else t}</h2>')
        elif b['tag'] == 'figcaption': continue
        elif b['text'].startswith('“') and '”' in b['text'] and b['text'].index('”') > len(b['text']) * 0.5: parts.append(f'<blockquote><p>{t}</p></blockquote>')
        else: parts.append(f'<p>{t}</p>')
    if p['category'] not in seed['categories']: seed['categories'].append(p['category'])
    y, m, d = p['date'].split('/')
    w, h = p['img_size']
    seed['posts'].append({
        'title': p['title'], 'slug': p['slug'], 'category': p['category'], 'excerpt': lead,
        'meta_description': p['description'] or '', 'content': '\n'.join(parts),
        'cover': 'img/' + p['img'], 'cover_w': w, 'cover_h': h, 'cover_alt': '',
        'gallery': [{'src': 'img/' + g['src'], 'w': g['w'], 'h': g['h']} for g in p['gallery']],
        'published_at': f'{y}-{m}-{d} 09:00:00',
    })
os.makedirs(os.path.join(REPO, 'install'), exist_ok=True)
json.dump(seed, open(os.path.join(REPO, 'install', 'seed.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print('ok:', ', '.join(sorted(os.path.basename(f) for f in glob.glob(os.path.join(PAGES, '*.php')))), '|', len(need), 'ícones |', len(seed['posts']), 'artigos no seed')
