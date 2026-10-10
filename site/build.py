#!/usr/bin/env python3
"""Monta o site estático: python3 build.py  ->  dist/"""
import json, re, os, html, shutil, sys
ROOT=os.path.dirname(os.path.abspath(__file__)); SRC=os.path.join(ROOT,'src'); DIST=os.path.join(ROOT,'dist')
ICONS=os.path.join(ROOT,'..','tools','node_modules','@phosphor-icons','core','assets')
POSTS=json.load(open(os.path.join(ROOT,'..','content','posts.json'),encoding='utf-8'))
FILES=['noticia-protecao-criancas','noticia-estacio-30-anos','noticia-comissao-rota-116','noticia-faixa-taxistas','noticia-fl360-formatura','noticia-viaduto-recursos']
MESES=['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro']
e=html.escape
def read(n): return open(os.path.join(SRC,n),encoding='utf-8').read()
def icon(m):
    kind,name=m.group(1),m.group(2)
    w={'i':'regular','ib':'bold','if':'fill'}[kind]
    f=os.path.join(ICONS,w,name+('' if w=='regular' else '-'+w)+'.svg')
    s=open(f,encoding='utf-8').read()
    inner=re.search(r'<svg[^>]*>(.*)</svg>',s,re.S).group(1)
    return f'<svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true">{inner}</svg>'
SCR=read('_scr.html')
def render(t,**kw):
    t=t.replace('{{scr}}',SCR)
    for k,v in kw.items(): t=t.replace('{{'+k+'}}',v)
    t=re.sub(r'\{\{(i|ib|if):([a-z0-9-]+)\}\}',icon,t)
    left=re.findall(r'\{\{[^}]+\}\}',t)
    assert not left, left
    return t
for i,p in enumerate(POSTS):
    p['file']=FILES[i]+'.html'; y,m,d=p['date'].split('/'); p['d_short']=f'{d}/{m}/{y}'; p['d_long']=f'{int(d)} de {MESES[int(m)-1]} de {y}'; p['iso']=f'{y}-{m}-{d}'
    p['url']=f"https://maicongoncalves.com.br/{p['slug']}/"
    p['lead']=p['blocks'][0]['text'] if p['blocks'] and p['blocks'][0]['tag']=='h2' else (p['description'] or '')
ORDER=sorted(range(len(POSTS)),key=lambda i:(POSTS[i]['iso'],-i),reverse=True)
def card(p,big=False,lazy=True):
    w,h=p['img_size']
    return f'''      <article class="card{' card--big' if big else ''}" data-cat="{e(p['category'])}" data-q="{e(p['title']+' '+p['category'])}">
        <div class="card-img"><img src="img/{p['img']}" alt="" width="{w}" height="{h}"{' loading="lazy"' if lazy else ''}><span class="tag">{e(p['category'])}</span></div>
        <div class="card-tx"><h3 class="card-t"><a href="{p['file']}">{e(p['title'])}</a></h3>
          <div class="card-ft"><time datetime="{p['iso']}">{p['d_short']}</time><span class="card-go" aria-hidden="true">{{{{ib:arrow-right}}}}</span></div></div>
      </article>'''
HEAD=read('_head.html'); HEADER=read('_header.html'); FOOTER=read('_footer.html')
def page(title,desc,body,on='',intro='false',ft_top=''):
    head=render(HEAD,intro=intro)
    header=render(HEADER,**{'on_'+k:('is-on' if on==k else '') for k in('home','hist','fl','pen','news','cont')})
    footer=render(FOOTER,ft_top=ft_top)
    inner=f'{header}\n{render(body)}\n{footer}'
    full=f'<!doctype html>\n<html lang="pt-BR">\n<head>\n<meta charset="utf-8">\n<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">\n<title>{e(title)}</title>\n<meta name="description" content="{e(desc)}">\n{head}</head>\n<body>\n{inner}\n</body>\n</html>\n'
    frag=f'<title>Redesign Maicon Gonçalves</title>\n{head}{inner}\n'
    return full,frag
out={}
# home
home=read('index.html').replace('{{news_home}}','\n'.join(card(POSTS[i],big=k<2) for k,i in enumerate(ORDER)))
out['index.html'],ARTIFACT=page('Vereador Maicon Gonçalves','Ações, projetos e atividades do mandato do vereador Maicon Gonçalves em Nova Friburgo, RJ.',home,on='home',intro='true')
# lista
cats=[]; [cats.append(POSTS[i]['category']) for i in ORDER if POSTS[i]['category'] not in cats]
chips='<button class="fl" aria-pressed="true" data-cat="">Todas</button>'+''.join(f'<button class="fl" aria-pressed="false" data-cat="{e(c)}">{e(c)}</button>' for c in cats)
lista=f'''<main>
<section class="ph-hero ph-hero--c"><div class="hero-bg" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span></div>
  <div class="wrap ph-hero-in"><h1 class="h1" data-split><span class="scr">Notícias{{{{scr}}}}</span></h1><p class="lead" data-h>Acompanhe as ações, projetos e conquistas do mandato</p></div></section>
<section class="sec news"><div class="wrap">
  <div class="tools"><div class="filters" role="group" aria-label="Filtrar por categoria">{chips}</div>
    <label class="search"><span class="sr">Pesquisar</span>{{{{i:magnifying-glass}}}}<input id="busca" type="search" placeholder="Pesquisar" autocomplete="off"></label></div>
  <div class="news-grid news-list" id="lista" data-per="3">
{chr(10).join(card(POSTS[i],lazy=k>2) for k,i in enumerate(ORDER))}
    <p class="empty" id="vazio" hidden>Nenhuma notícia encontrada. Tente outro termo ou escolha outra categoria.</p>
  </div>
  <nav class="pager" id="pager" aria-label="Paginação"></nav>
</div></section>
</main>'''
out['noticias.html'],_=page('Notícias – Vereador Maicon Gonçalves','Acompanhe as ações, projetos e conquistas do mandato.',lista,on='news')
# artigos
for i,p in enumerate(POSTS):
    blocks=p['blocks'][1:] if p['blocks'] and p['blocks'][0]['tag']=='h2' else p['blocks']
    parts=[]
    for b in blocks:
        t=e(b['text'])
        if b['tag'] in('h2','h3','h4'): parts.append(f'<h2>{t[0]+t[1:].lower() if t.isupper() else t}</h2>')
        elif b['tag']=='figcaption': continue
        elif b['text'].startswith('“') and b['text'].count('”')>=1 and b['text'].index('”')>len(b['text'])*0.5: parts.append(f'<blockquote>{t}</blockquote>')
        else: parts.append(f'<p>{t}</p>')
    gal=''
    if p['gallery']:
        gal='<div class="gal">'+''.join(f'<button type="button" data-full="img/{g["src"]}" aria-label="Ampliar foto {k+1}"><img src="img/{g["src"]}" alt="Foto {k+1} de {len(p["gallery"])}: {e(p["title"])}" width="{g["w"]}" height="{g["h"]}" loading="lazy"></button>' for k,g in enumerate(p['gallery']))+'</div>'
    others=[POSTS[j] for j in ORDER if j!=i][:3]
    wa='https://api.whatsapp.com/send?text='+__import__('urllib.parse').parse.quote(p['title']+' '+p['url'])
    fb='https://www.facebook.com/sharer/sharer.php?u='+__import__('urllib.parse').parse.quote(p['url'],safe='')
    w,h=p['img_size']
    body=f'''<div class="progress" id="progress" aria-hidden="true"></div>
<main>
<header class="art-hd"><div class="hero-bg" aria-hidden="true"><span class="orb orb-2"></span></div>
  <div class="wrap"><div class="art-hd-in">
    <a class="back" href="noticias.html" data-h>{{{{ib:arrow-left}}}}Voltar para as notícias</a>
    <span class="tag" data-h>{e(p['category'])}</span>
    <h1 data-split>{e(p['title'])}</h1>
    <p class="art-sub" data-h>{e(p['lead'])}</p>
    <div class="art-meta" data-h><span>{{{{i:calendar-blank}}}}<time datetime="{p['iso']}">{p['d_long']}</time></span></div>
  </div></div></header>
<div class="art-cover"><div class="wrap"><figure data-h><img src="img/{p['img']}" alt="" width="{w}" height="{h}"></figure></div></div>
<div class="wrap art-body">
  <article class="prose">
{chr(10).join(parts)}
{gal}
  </article>
  <aside class="side" aria-label="Compartilhar"><div class="side-h">Compartilhe este artigo</div>
    <div class="share">
      <a href="{wa}" target="_blank" rel="noopener">{{{{i:whatsapp-logo}}}}WhatsApp</a>
      <a href="{fb}" target="_blank" rel="noopener">{{{{i:facebook-logo}}}}Facebook</a>
      <button type="button" data-copy="{p['url']}">{{{{ib:link-simple}}}}Copiar link</button>
    </div></aside>
</div>
<section class="sec news"><div class="wrap">
  <div class="sec-hd"><h2 class="h2" data-split>Últimas notícias</h2><a class="btn btn--line btn--sm" href="noticias.html" data-r><span>Ver mais</span><span class="btn-i">{{{{ib:arrow-right}}}}{{{{ib:arrow-right}}}}</span></a></div>
  <div class="news-grid news-list" data-rs>
{chr(10).join(card(o) for o in others)}
  </div></div></section>
</main>
<dialog class="lb" id="lb" aria-label="Foto ampliada"><div class="lb-in"><img src="" alt=""></div>
  <button class="lb-x" aria-label="Fechar">{{{{ib:x}}}}</button><button class="lb-p" aria-label="Foto anterior">{{{{ib:caret-left}}}}</button><button class="lb-n" aria-label="Próxima foto">{{{{ib:caret-right}}}}</button></dialog>'''
    out[p['file']],_=page(p['title']+' – Vereador Maicon Gonçalves',p['description'] or p['lead'],body,on='news')
# páginas internas
bairros=open(os.path.join(SRC,'_bairros.txt'),encoding='utf-8').read().split('\n')
bopts='<option value="">'+e(bairros[0])+'</option>'+''.join(f'<option>{e(b)}</option>' for b in bairros[1:])
for fn,title,desc,on,ft in [
  ('minha-historia.html','Minha história – Vereador Maicon Gonçalves','A caminhada que me trouxe até aqui.','hist','on-white'),
  ('fl360.html','FL360 – Vereador Maicon Gonçalves','Formação cidadã e compreensão da gestão pública.','fl','on-white'),
  ('peneira.html','Descobrindo talentos – Vereador Maicon Gonçalves','O maior movimento de captação do futebol de base da Serra Fluminense.','pen','on-white'),
  ('contato.html','Contato – Vereador Maicon Gonçalves','O mandato é construído com você.','cont','')]:
    out[fn],_=page(title,desc,read(fn).replace('{{bairros}}',bopts),on=on,ft_top=ft)
os.makedirs(DIST,exist_ok=True)
for n,c in out.items(): open(os.path.join(DIST,n),'w',encoding='utf-8').write(c)
open(os.path.join(ROOT,'artifact.html'),'w',encoding='utf-8').write(ARTIFACT)
for f in('site.css','site.js'): shutil.copy(os.path.join(SRC,f),os.path.join(DIST,f))
print('ok',len(out),'páginas;',', '.join(sorted(out)))
