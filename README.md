# Site do Vereador Maicon Gonçalves

Site em PHP + MySQL, com painel para as notícias e o Friburgo no Mapa (envio e acompanhamento de demandas, em `/friburgo-no-mapa/`) que avisa o gabinete pelo Brevo.
Os endereços são os mesmos do WordPress antigo (`/noticias/`, `/slug-da-noticia/`…), então nenhum link do Google se perde.

## Subir na hospedagem (cPanel)

1. **Código**: cPanel › Git Version Control › Create › clone deste repositório.
   - Mais simples: clonar direto na pasta do site (ex.: `public_html`, precisa estar vazia).
   - Se já houver coisas em `public_html` (ex.: a pasta `plataformafl360`), clone em outra pasta e copie os arquivos para `public_html` **sem apagar** o que já existe lá.
2. **Banco**: cPanel › Bancos de dados MySQL › crie o banco, crie o usuário, adicione o usuário ao banco com **todos os privilégios**.
3. **.env**: copie `.env.example` para `.env` e preencha (banco, Brevo, `APP_URL`).
   De preferência coloque o `.env` **um nível acima** da pasta pública (ex.: `/home/conta/.env`). Na raiz do site também funciona; o `.htaccess` bloqueia o acesso.
4. **Instalar**: abra `https://seudominio/install/`, confira os itens e crie o seu acesso. O instalador cria as tabelas e importa os 6 artigos atuais. Depois disso ele trava sozinho.
5. **Painel**: `https://seudominio/admin/`.

Permissões: as pastas `uploads/` e `storage/` precisam permitir escrita (755 normalmente basta no cPanel).

Fotos grandes: o arquivo `.user.ini` sobe o limite de envio para 16 MB por foto. Se a hospedagem ignorar, ajuste `upload_max_filesize` e `post_max_size` em cPanel › MultiPHP INI Editor.

## Brevo (aviso de nova demanda)

- Gere a chave em Brevo › SMTP & API › Chaves de API e coloque em `BREVO_API_KEY`.
- O remetente (`MAIL_FROM_EMAIL`) precisa estar validado no Brevo (ideal: um e-mail do próprio domínio, com o domínio autenticado).
- As demandas chegam em `MAIL_TO` com as fotos em anexo (convertidas para JPG) e, quando a pessoa compartilha, a localização com link para o Google Maps.
- Se o envio falhar, os dados ficam registrados em `storage/logs/demandas-AAAA-MM.log`.

## Avisos para quem enviou a demanda

- Quando o gabinete aprova (vai à Prefeitura) e quando marca como resolvida, a pessoa recebe um e-mail com o protocolo e o link para acompanhar no mapa (dá para desmarcar na hora).
- Remetente: `PUBLIC_FROM_EMAIL` / `PUBLIC_FROM_NAME` (o e-mail do vereador, validado no Brevo). Vazio = usa `MAIL_FROM_EMAIL`.
- Registro dos envios em `storage/logs/avisos-AAAA-MM.log`.

## Novidades por e-mail (artigo novo)

- No formulário de demandas há a opção "Quero receber por e-mail as novidades do mandato". Só quem marca entra na lista (LGPD).
- Ao publicar um artigo, a caixa "Avisar por e-mail quem aceita novidades" manda o título e o link para essa lista. Cada artigo avisa uma vez só.
- Todo e-mail tem link de descadastro (`/descadastrar/`). Registro em `storage/logs/artigos-AAAA-MM.log`.

## Vídeos nas demandas

- A pessoa pode enviar 1 vídeo (além de até 3 fotos). O vídeo é comprimido **no próprio celular**, antes do envio: até 1 minuto, lado maior de 960 px, ~1 Mbps (1 min ≈ 7 MB). O site não fica mais pesado: o compressor (`js/video*.js`) só é baixado quando alguém escolhe um vídeo, e no mapa o vídeo só carrega quando a pessoa aperta play.
- Gera MP4 (H.264 + AAC) no Chrome, Safari e Android; WebM (VP9) nos navegadores sem H.264. Navegador sem suporte: aceita o arquivo original até 25 MB.
- Para atualizar o compressor: `cd tools && npm install && npm run video`, depois `python3 site/build.py`.
- Limites do PHP em `.user.ini` (32 MB por arquivo, 64 MB por envio).

## Exportar dados

- Painel › Demandas › Exportar dados (só administrador): planilha de pessoas (uma linha por pessoa) e de demandas.
- Cada exportação fica registrada em `storage/logs/exportacoes-AAAA-MM.log`.

## Níveis de acesso no painel

- **Administrador:** tudo, inclusive blog, Configurações, Usuários e excluir atendimentos.
- **Assessoria:** só as demandas (atendimentos e cadastrar demanda).
- Contas novas em Painel › Sistema › Usuários.

## Localização no formulário

Usa o GPS do aparelho (precisa de **https**), mostra um mapa do OpenStreetMap com pino que a pessoa pode arrastar para o ponto exato e preenche rua e bairro automaticamente pelo Nominatim (gratuito, sem chave).

## Estrutura

| Pasta | O que tem |
| --- | --- |
| `index.php` | Roteador do site (todas as páginas passam por ele) |
| `app/` | Núcleo em PHP e páginas geradas (`app/pages/`). Bloqueada para a web |
| `admin/` | Painel (artigos, categorias, configurações, conta) |
| `api/` | Envio das demandas (`demandas.php`) e endereço pela localização (`geocode.php`) |
| `install/` | Instalador, `schema.sql` (tabelas) e `seed.json` (artigos iniciais) |
| `uploads/` | Imagens enviadas pelo painel |
| `storage/` | Logs, cache e trava da instalação |
| `site/` | Fontes do layout + `build.py` (só para desenvolvimento) |

## Alterar o layout (desenvolvimento)

As páginas em `app/pages/` são geradas. Para mudar layout ou textos fixos:

```bash
cd tools && npm install   # uma vez (ícones)
python3 site/build.py     # edita site/src/* e roda isto
```

## Publicar artigos pelo Claude (MCP)

O site tem um servidor MCP em `/mcp/<MCP_SECRET>` (Streamable HTTP). O projeto "Gerador de Artigos" usa as ferramentas
`listar_categorias`, `listar_posts`, `obter_post`, `solicitar_upload_imagem`, `importar_imagem_url`, `criar_post`,
`atualizar_post` e `publicar_post`. Não existe ferramenta de excluir (só no painel).

1. No `.env` do servidor, crie `MCP_SECRET=` com 48+ caracteres aleatórios (`openssl rand -hex 32`). Vazio = MCP desligado (404).
2. No Claude: Configurações > Conectores > Adicionar conector personalizado, com a URL `https://maicongoncalves.com.br/mcp/<MCP_SECRET>`.
3. Ative o conector no projeto "Gerador de Artigos".

Foto: o Claude pede um link de upload (15 min, uso único), envia a foto com `curl -X PUT` e usa o `media_id` em `criar_post`.
A foto é salva sem EXIF/GPS, com 1688 px de largura, em WebP. Artigos nascem como **rascunho**, com um `url_preview` (7 dias, sem login).
Auditoria: tabela `mcp_log` (ferramenta, parâmetros sem texto do corpo, IP, resultado, id do artigo). Limites: 60 chamadas/min e 10 imagens/hora por IP.
