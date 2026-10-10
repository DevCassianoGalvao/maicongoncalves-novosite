# Site do Vereador Maicon Gonçalves — Nova Friburgo/RJ

Site estático (HTML/CSS/JS), pronto para deploy direto na Vercel (sem build necessário — os arquivos na raiz já são o site publicável).

## Estrutura
- `index.html`, `minha-historia.html`, `fl360.html`, `peneira.html`, `contato.html`, `noticias.html`, `noticia-*.html` — páginas do site (prontas, na raiz)
- `site.css`, `site.js` — estilos e scripts
- `img/` — imagens otimizadas (WebP)
- `site/src/` — fontes do site (fragments HTML, CSS/JS de desenvolvimento) e `site/build.py`, o gerador que monta as páginas da raiz a partir de `site/src/` + `content/posts.json`
- `content/posts.json` — conteúdo dos artigos do blog
- `tools/` — scripts de extração e teste (Playwright)

## Como editar
1. Edite os arquivos em `site/src/`
2. Rode `python3 site/build.py` (gera as páginas atualizadas na raiz)
3. Commit e push — a Vercel republica automaticamente
