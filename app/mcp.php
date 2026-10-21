<?php
defined('APP') or exit;
/**
 * Servidor MCP (Streamable HTTP, JSON-RPC 2.0) para o Claude publicar artigos no portal.
 * Endereço: /mcp/{MCP_SECRET}  (segredo no .env). Segredo errado ou ausente = 404.
 * Usa as mesmas tabelas e regras do painel (posts, categories, html_sanitize, unique_slug, img_*).
 */
require_once ROOT . '/app/images.php';
require_once ROOT . '/app/sanitize.php';
require_once ROOT . '/app/markdown.php';
require_once ROOT . '/app/post_save.php';

const MCP_IMG_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic'];
const MCP_IMG_MAX = 20 * 1024 * 1024;

function mcp_secret(): string { return (string) env('MCP_SECRET', ''); }

function mcp_tables(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    db()->exec("CREATE TABLE IF NOT EXISTS mcp_uploads (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      token CHAR(64) NOT NULL,
      nome_arquivo VARCHAR(190) NULL,
      mime VARCHAR(40) NULL,
      expira_em DATETIME NOT NULL,
      usado_em DATETIME NULL,
      src VARCHAR(255) NULL,
      w INT UNSIGNED NULL,
      h INT UNSIGNED NULL,
      criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY mcp_uploads_token (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    db()->exec("CREATE TABLE IF NOT EXISTS mcp_log (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      ferramenta VARCHAR(60) NULL,
      parametros TEXT NULL,
      resultado VARCHAR(20) NULL,
      post_id INT UNSIGNED NULL,
      ip VARCHAR(45) NULL,
      criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function mcp_log(string $tool, array $params, string $result, ?int $postId = null): void
{
    try {
        q('INSERT INTO mcp_log (ferramenta, parametros, resultado, post_id, ip) VALUES (?, ?, ?, ?, ?)',
            [$tool, mb_substr(json_encode(mcp_log_trim($params), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 0, 4000), $result, $postId, client_ip()]);
    } catch (Throwable $ex) { app_log('mcp', 'log: ' . $ex->getMessage()); }
}
function mcp_log_trim(array $p): array
{
    foreach (['corpo_markdown', 'corpo_html'] as $k) if (isset($p[$k])) $p[$k] = mb_strlen($p[$k]) . ' caracteres';
    return $p;
}

class McpError extends Exception {}

/* ---------- token assinado de pré-visualização (rascunho visível sem login por 7 dias) ---------- */
function mcp_preview_token(int $postId, ?int $exp = null): string
{
    $exp = $exp ?: time() + 7 * 86400;
    return $exp . '.' . hash_hmac('sha256', $postId . '|' . $exp, 'preview|' . mcp_secret());
}
function mcp_preview_valid(int $postId, string $token): bool
{
    if (mcp_secret() === '' || !preg_match('/^(\d{9,11})\.([a-f0-9]{64})$/', $token, $m) || (int) $m[1] < time()) return false;
    return hash_equals(hash_hmac('sha256', $postId . '|' . $m[1], 'preview|' . mcp_secret()), $m[2]);
}

/* ---------- imagens ---------- */
/** Processa o arquivo: confere o tipo pelo conteúdo, corrige a orientação, tira o EXIF, 1688 px de largura, WebP. */
function mcp_process_image(string $tmp): array
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset(MCP_IMG_MIMES[$mime])) throw new McpError('O arquivo não é uma imagem aceita (JPG, PNG ou WebP).');
    if ($mime === 'image/heic') {
        if (!class_exists('Imagick')) throw new McpError('HEIC não é suportado nesta hospedagem. Converta a foto para JPG antes de enviar.');
        try { $im = new Imagick($tmp); $im->setImageFormat('jpeg'); $im->autoOrient(); $conv = $tmp . '.jpg'; $im->writeImage($conv); $im->destroy(); $tmp = $conv; }
        catch (Throwable $ex) { throw new McpError('Não consegui ler a foto HEIC. Converta para JPG.'); }
    }
    try { $im = img_open($tmp); } catch (Throwable $ex) { throw new McpError($ex->getMessage()); }
    $w = imagesx($im); $h = imagesy($im);
    if ($w > 1688) { $nh = (int) round($h * 1688 / $w); $out = imagecreatetruecolor(1688, $nh); imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255)); imagecopyresampled($out, $im, 0, 0, 0, 0, 1688, $nh, $w, $h); $im = $out; }
    else { $out = imagecreatetruecolor($w, $h); imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255)); imagecopy($out, $im, 0, 0, 0, 0, $w, $h); $im = $out; }
    $rel = 'uploads/' . date('Y/m');
    if (!is_dir(ROOT . '/' . $rel) && !@mkdir(ROOT . '/' . $rel, 0775, true)) throw new McpError('Sem permissão para gravar na pasta uploads/.');
    $name = date('dHis') . '-' . bin2hex(random_bytes(5));
    if (function_exists('imagewebp')) { $file = "$rel/$name.webp"; $ok = imagewebp($im, ROOT . '/' . $file, 82); }
    else { $file = "$rel/$name.jpg"; $ok = imagejpeg($im, ROOT . '/' . $file, 85); }
    if (!$ok) throw new McpError('Não foi possível salvar a imagem.');
    return ['src' => $file, 'w' => imagesx($im), 'h' => imagesy($im)];
}

/** Endpoint PUT /api/mcp-upload/{token}. */
function mcp_handle_upload(string $token): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    if (mcp_secret() === '' || !preg_match('/^[a-f0-9]{64}$/', $token) || !in_array($_SERVER['REQUEST_METHOD'] ?? '', ['PUT', 'POST'], true)) { http_response_code(404); exit; }
    mcp_tables();
    if (!rate_ok('mcp-put', 30, 3600)) json_out(['ok' => false, 'error' => 'Muitos envios. Tente mais tarde.'], 429);
    $row = q('SELECT * FROM mcp_uploads WHERE token = ?', [$token])->fetch();
    if (!$row) { http_response_code(404); exit; }
    if ($row['usado_em'] !== null) json_out(['ok' => false, 'error' => 'Este link de upload já foi usado. Peça um novo.'], 410);
    if (strtotime($row['expira_em']) < time()) json_out(['ok' => false, 'error' => 'Este link de upload venceu (15 minutos). Peça um novo.'], 410);
    // uso único: marca antes de processar (se falhar, pede outro link)
    $claim = q('UPDATE mcp_uploads SET usado_em = NOW() WHERE id = ? AND usado_em IS NULL', [$row['id']]);
    if ($claim->rowCount() !== 1) json_out(['ok' => false, 'error' => 'Este link de upload já foi usado.'], 410);
    $tmp = tempnam(sys_get_temp_dir(), 'mcpup');
    try {
        $in = fopen('php://input', 'rb'); $out = fopen($tmp, 'wb'); $n = 0;
        while (!feof($in)) {
            $chunk = fread($in, 65536);
            if ($chunk === false || $chunk === '') break;
            $n += strlen($chunk);
            if ($n > MCP_IMG_MAX) throw new McpError('Imagem maior que 20 MB.');
            fwrite($out, $chunk);
        }
        fclose($in); fclose($out);
        if ($n === 0) throw new McpError('Nenhum arquivo recebido no corpo da requisição.');
        $img = mcp_process_image($tmp);
        q('UPDATE mcp_uploads SET src = ?, w = ?, h = ? WHERE id = ?', [$img['src'], $img['w'], $img['h'], $row['id']]);
        mcp_log('upload_put', ['media_id' => (int) $row['id'], 'bytes' => $n], 'ok');
        echo json_encode(['ok' => true, 'media_id' => (int) $row['id'], 'url' => abs_url('/' . $img['src']), 'largura' => $img['w'], 'altura' => $img['h']], JSON_UNESCAPED_SLASHES);
    } catch (McpError $ex) {
        mcp_log('upload_put', ['media_id' => (int) $row['id']], 'erro');
        json_out(['ok' => false, 'error' => $ex->getMessage()], 422);
    } finally { @unlink($tmp); @unlink($tmp . '.jpg'); }
    exit;
}

/* ---------- download por URL (plano B) com proteção contra SSRF ---------- */
function mcp_ip_public(string $ip): bool
{
    return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}
function mcp_fetch_url(string $url, string $dest): void
{
    if (preg_match('#^https://drive\.google\.com/file/d/([\w-]+)#', $url, $m)) $url = 'https://drive.google.com/uc?export=download&id=' . $m[1];
    for ($hop = 0; $hop <= 3; $hop++) {
        $p = parse_url($url);
        if (!$p || ($p['scheme'] ?? '') !== 'https' || empty($p['host']) || isset($p['user'])) throw new McpError('Use um endereço https público.');
        $host = $p['host']; $port = $p['port'] ?? 443;
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (array) @gethostbynamel($host);
        if (!$ips) throw new McpError('Não consegui resolver o endereço da imagem.');
        foreach ($ips as $ip) if (!mcp_ip_public($ip)) throw new McpError('Endereço não permitido.');
        $fh = fopen($dest, 'wb'); $size = 0; $loc = null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RESOLVE => [$host . ':' . $port . ':' . $ips[0]], // evita trocar o IP depois da checagem
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_USERAGENT => 'Mozilla/5.0 (portal-mcp)',
            CURLOPT_HEADERFUNCTION => function ($c, $h) use (&$loc) { if (stripos($h, 'location:') === 0) $loc = trim(substr($h, 9)); return strlen($h); },
            CURLOPT_WRITEFUNCTION => function ($c, $d) use ($fh, &$size) { $size += strlen($d); if ($size > MCP_IMG_MAX) return 0; return fwrite($fh, $d); },
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_errno($ch);
        curl_close($ch); fclose($fh);
        if ($size > MCP_IMG_MAX) throw new McpError('Imagem maior que 20 MB.');
        if ($code >= 300 && $code < 400 && $loc) {
            $url = preg_match('#^https?://#i', $loc) ? $loc : ($p['scheme'] . '://' . $host . (strpos($loc, '/') === 0 ? '' : '/') . $loc);
            continue;
        }
        if ($err || $code !== 200) throw new McpError('Não consegui baixar a imagem (resposta ' . ($code ?: 'sem resposta') . '). O link precisa ser público.');
        return;
    }
    throw new McpError('Redirecionamentos demais.');
}

/* ---------- ferramentas ---------- */
function mcp_post_url(array $p): string { return abs_url('/' . $p['slug'] . '/'); }
function mcp_status_out(array $p): string
{
    if ($p['status'] === 'published') return strtotime((string) $p['published_at']) > time() ? 'agendado' : 'publicado';
    return 'rascunho';
}
function mcp_post_summary(array $p): array
{
    return ['id' => (int) $p['id'], 'titulo' => $p['title'], 'slug' => $p['slug'], 'categoria' => $p['category'] ?? null, 'status' => mcp_status_out($p),
        'data_publicacao' => $p['published_at'] ? date('c', strtotime($p['published_at'])) : null, 'url' => mcp_post_url($p)];
}
function mcp_post_links(array $p): array
{
    $out = ['id' => (int) $p['id'], 'status' => mcp_status_out($p), 'url' => mcp_post_url($p), 'url_editar_painel' => abs_url('/admin/post.php?id=' . $p['id'])];
    if ($p['status'] !== 'published') $out['url_preview'] = mcp_post_url($p) . '?preview=' . mcp_preview_token((int) $p['id']);
    elseif (strtotime((string) $p['published_at']) > time()) $out['url_preview'] = mcp_post_url($p) . '?preview=' . mcp_preview_token((int) $p['id']);
    if ($p['status'] === 'published') $out['data_publicacao'] = date('c', strtotime((string) $p['published_at']));
    return $out;
}
function mcp_get_post(array $a): array
{
    if (!empty($a['id'])) $row = q('SELECT p.*, c.slug AS cat_slug, c.name AS category FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?', [(int) $a['id']])->fetch();
    elseif (!empty($a['slug'])) $row = q('SELECT p.*, c.slug AS cat_slug, c.name AS category FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ?', [slugify((string) $a['slug'])])->fetch();
    else throw new McpError('Informe o id ou o slug do artigo.');
    if (!$row) throw new McpError('Artigo não encontrado.');
    return $row;
}
function mcp_clean_body(array $a): string
{
    if (isset($a['corpo_markdown']) && trim((string) $a['corpo_markdown']) !== '') $html = md_to_html((string) $a['corpo_markdown']);
    else $html = (string) ($a['corpo_html'] ?? '');
    $html = strip_tags($html, '<p><h2><h3><strong><em><blockquote><ul><ol><li><a><br>');
    $html = preg_replace('#<h1(\s[^>]*)?>#i', '<h2>', preg_replace('#</h1>#i', '</h2>', $html));
    return html_sanitize($html);
}
function mcp_cover(int $mediaId): array
{
    $m = q('SELECT * FROM mcp_uploads WHERE id = ?', [$mediaId])->fetch();
    if (!$m || !$m['src']) throw new McpError('capa_media_id inválido ou a foto ainda não foi enviada (faça o PUT no upload_url antes).');
    return $m;
}
function mcp_date(string $iso): string
{
    $t = strtotime($iso);
    if (!$t) throw new McpError('data_publicacao inválida. Use o formato ISO 8601, ex.: 2026-10-21T14:30:00-03:00.');
    return date('Y-m-d H:i:s', $t);
}
function mcp_category_id(string $slug): int
{
    $id = q('SELECT id FROM categories WHERE slug = ?', [slugify($slug)])->fetchColumn();
    if (!$id) {
        $list = implode(', ', q('SELECT slug FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN));
        throw new McpError('Categoria "' . $slug . '" não existe. Use listar_categorias. Disponíveis: ' . ($list ?: 'nenhuma') . '.');
    }
    return (int) $id;
}

function mcp_tool(string $name, array $a, ?int &$postId): array
{
    switch ($name) {
        case 'listar_categorias':
            return array_map(function ($c) { return ['slug' => $c['slug'], 'nome' => $c['name']]; }, q('SELECT slug, name FROM categories ORDER BY name')->fetchAll());

        case 'listar_posts':
            $where = []; $args = [];
            $st = $a['status'] ?? 'todos';
            if ($st === 'publicado') $where[] = "p.status = 'published' AND p.published_at <= NOW()";
            elseif ($st === 'rascunho') $where[] = "p.status = 'draft'";
            if (!empty($a['categoria'])) { $where[] = 'c.slug = ?'; $args[] = slugify((string) $a['categoria']); }
            if (!empty($a['busca'])) { $where[] = '(p.title LIKE ? OR p.content LIKE ?)'; $like = '%' . str_replace(['%', '_'], ['\%', '\_'], mb_substr((string) $a['busca'], 0, 100)) . '%'; $args[] = $like; $args[] = $like; }
            $lim = max(1, min(50, (int) ($a['limite'] ?? 20)));
            $rows = q('SELECT p.*, c.name AS category FROM posts p LEFT JOIN categories c ON c.id = p.category_id' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
                " ORDER BY COALESCE(p.published_at, p.created_at) DESC LIMIT $lim", $args)->fetchAll();
            return array_map('mcp_post_summary', $rows);

        case 'obter_post':
            $p = mcp_get_post($a);
            return array_merge(mcp_post_summary($p), [
                'linha_fina' => $p['excerpt'], 'corpo_html' => $p['content'], 'categoria_slug' => $p['cat_slug'],
                'capa_url' => $p['cover'] ? abs_url('/' . $p['cover']) : null, 'alt_capa' => $p['cover_alt'], 'meta_description' => $p['meta_description'],
            ]);

        case 'solicitar_upload_imagem':
        case 'importar_imagem_url':
            if (!rate_ok('mcp-upload', 10, 3600)) throw new McpError('Limite de 10 imagens por hora atingido. Tente mais tarde.');
            if ($name === 'solicitar_upload_imagem') {
                $mime = strtolower((string) ($a['mime'] ?? ''));
                if (!isset(MCP_IMG_MIMES[$mime])) throw new McpError('mime não aceito. Use image/jpeg, image/png, image/webp ou image/heic.');
                if ((int) ($a['tamanho_bytes'] ?? 0) > MCP_IMG_MAX) throw new McpError('A imagem passa de 20 MB. Reduza antes de enviar.');
                $token = bin2hex(random_bytes(32));
                $exp = date('Y-m-d H:i:s', time() + 900);
                q('INSERT INTO mcp_uploads (token, nome_arquivo, mime, expira_em) VALUES (?, ?, ?, ?)', [$token, mb_substr((string) ($a['nome_arquivo'] ?? ''), 0, 190), $mime, $exp]);
                return ['upload_url' => abs_url('/api/mcp-upload/' . $token), 'metodo' => 'PUT', 'header_content_type' => $mime, 'expira_em' => date('c', strtotime($exp)), 'media_id' => (int) db()->lastInsertId(),
                    'como_enviar' => 'curl -sS -X PUT -H "Content-Type: ' . $mime . '" --data-binary @ARQUIVO "<upload_url>"'];
            }
            $tmp = tempnam(sys_get_temp_dir(), 'mcpurl');
            try {
                mcp_fetch_url((string) ($a['url'] ?? ''), $tmp);
                $img = mcp_process_image($tmp);
            } finally { @unlink($tmp); @unlink($tmp . '.jpg'); }
            q('INSERT INTO mcp_uploads (token, nome_arquivo, mime, expira_em, usado_em, src, w, h) VALUES (?, ?, ?, NOW(), NOW(), ?, ?, ?)',
                [bin2hex(random_bytes(32)), mb_substr((string) ($a['nome_arquivo'] ?? 'url'), 0, 190), 'image/webp', $img['src'], $img['w'], $img['h']]);
            return ['ok' => true, 'media_id' => (int) db()->lastInsertId(), 'url' => abs_url('/' . $img['src']), 'largura' => $img['w'], 'altura' => $img['h']];

        case 'criar_post':
            $title = trim((string) ($a['titulo'] ?? ''));
            if ($title === '') throw new McpError('Informe o título.');
            if (mb_strlen($title) > 160) throw new McpError('O título passa de 160 caracteres.');
            $excerpt = trim((string) ($a['linha_fina'] ?? ''));
            if ($excerpt === '') throw new McpError('Informe a linha fina.');
            $body = mcp_clean_body($a);
            if (trim(strip_tags($body)) === '') throw new McpError('Informe o corpo do artigo (corpo_markdown ou corpo_html).');
            $catId = mcp_category_id((string) ($a['categoria'] ?? ''));
            $alt = trim((string) ($a['alt_capa'] ?? ''));
            if ($alt === '') throw new McpError('Informe o alt_capa (texto alternativo da capa).');
            $cover = mcp_cover((int) ($a['capa_media_id'] ?? 0));
            $status = ($a['status'] ?? 'rascunho') === 'publicado' ? 'published' : 'draft';
            $when = !empty($a['data_publicacao']) ? mcp_date((string) $a['data_publicacao']) : ($status === 'published' ? date('Y-m-d H:i:s') : null);
            $slug = unique_slug(slugify(trim((string) ($a['slug'] ?? '')) !== '' ? (string) $a['slug'] : $title), 0);
            $caption = trim((string) ($a['legenda_capa'] ?? ''));
            q('INSERT INTO posts (title, slug, excerpt, content, cover, cover_w, cover_h, cover_alt, gallery, category_id, status, published_at, meta_description, author_id, created_at, updated_at)
               VALUES (?,?,?,?,?,?,?,?,NULL,?,?,?,?,NULL,NOW(),NOW())', [$title, $slug, $excerpt, $body, $cover['src'], $cover['w'], $cover['h'],
                $alt, $catId, $status, $when, trim((string) ($a['meta_description'] ?? '')) ?: null]);
            $postId = (int) db()->lastInsertId();
            if ($caption !== '') app_log('mcp', 'legenda_capa do post ' . $postId . ' ignorada (o tema não tem legenda de capa)');
            return mcp_post_links(mcp_get_post(['id' => $postId]));

        case 'atualizar_post':
            $p = mcp_get_post($a);
            $postId = (int) $p['id'];
            $set = []; $args = [];
            if (array_key_exists('titulo', $a)) { $t = trim((string) $a['titulo']); if ($t === '' || mb_strlen($t) > 160) throw new McpError('Título inválido (1 a 160 caracteres).'); $set[] = 'title = ?'; $args[] = $t; }
            if (array_key_exists('slug', $a) && trim((string) $a['slug']) !== '') { $set[] = 'slug = ?'; $args[] = unique_slug(slugify((string) $a['slug']), $postId); }
            if (array_key_exists('linha_fina', $a)) { $set[] = 'excerpt = ?'; $args[] = trim((string) $a['linha_fina']); }
            if (array_key_exists('meta_description', $a)) { $set[] = 'meta_description = ?'; $args[] = trim((string) $a['meta_description']) ?: null; }
            if (isset($a['corpo_markdown']) || isset($a['corpo_html'])) {
                $b = mcp_clean_body($a);
                if (trim(strip_tags($b)) === '') throw new McpError('O corpo ficou vazio.');
                $set[] = 'content = ?'; $args[] = $b;
            }
            if (array_key_exists('categoria', $a)) { $set[] = 'category_id = ?'; $args[] = mcp_category_id((string) $a['categoria']); }
            if (array_key_exists('alt_capa', $a)) { $set[] = 'cover_alt = ?'; $args[] = trim((string) $a['alt_capa']); }
            if (!empty($a['capa_media_id'])) {
                $c = mcp_cover((int) $a['capa_media_id']);
                $set[] = 'cover = ?, cover_w = ?, cover_h = ?'; array_push($args, $c['src'], $c['w'], $c['h']);
                if ($p['cover'] && $p['cover'] !== $c['src'] && strpos($p['cover'], 'uploads/') === 0 && strpos($p['cover'], '..') === false && is_file(ROOT . '/' . $p['cover'])) @unlink(ROOT . '/' . $p['cover']);
            }
            if (!empty($a['data_publicacao'])) { $set[] = 'published_at = ?'; $args[] = mcp_date((string) $a['data_publicacao']); }
            if (isset($a['status'])) {
                if (!in_array($a['status'], ['rascunho', 'publicado'], true)) throw new McpError('status deve ser "rascunho" ou "publicado".');
                $set[] = 'status = ?'; $args[] = $a['status'] === 'publicado' ? 'published' : 'draft';
                if ($a['status'] === 'publicado' && !$p['published_at'] && empty($a['data_publicacao'])) { $set[] = 'published_at = ?'; $args[] = date('Y-m-d H:i:s'); }
            }
            if (!$set) throw new McpError('Nenhum campo para alterar foi enviado.');
            $args[] = $postId;
            q('UPDATE posts SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE id = ?', $args);
            return mcp_post_links(mcp_get_post(['id' => $postId]));

        case 'publicar_post':
            $p = mcp_get_post($a);
            $postId = (int) $p['id'];
            if (!$p['cover']) throw new McpError('O artigo está sem capa. Use atualizar_post com capa_media_id antes de publicar.');
            $when = !empty($a['data_publicacao']) ? mcp_date((string) $a['data_publicacao']) : ($p['status'] === 'published' && $p['published_at'] ? $p['published_at'] : date('Y-m-d H:i:s'));
            q("UPDATE posts SET status = 'published', published_at = ?, updated_at = NOW() WHERE id = ?", [$when, $postId]);
            return mcp_post_links(mcp_get_post(['id' => $postId]));
    }
    throw new McpError('Ferramenta desconhecida: ' . $name);
}

function mcp_tool_defs(): array
{
    $s = function (array $props, array $req = []) { return ['type' => 'object', 'properties' => $props, 'required' => $req]; };
    $str = function ($d) { return ['type' => 'string', 'description' => $d]; };
    return [
        ['name' => 'listar_categorias', 'description' => 'Lista as categorias do portal do vereador Maicon Gonçalves (slug e nome). Use o slug no campo categoria de criar_post.', 'inputSchema' => $s([])],
        ['name' => 'listar_posts', 'description' => 'Lista artigos do portal (mais recentes primeiro). Use para checar se o tema já foi publicado e sugerir links internos.',
            'inputSchema' => $s(['busca' => $str('Texto no título ou no corpo'), 'categoria' => $str('Slug da categoria'), 'status' => ['type' => 'string', 'enum' => ['publicado', 'rascunho', 'todos'], 'description' => 'Padrão: todos'], 'limite' => ['type' => 'integer', 'description' => 'Padrão 20, máximo 50']])],
        ['name' => 'obter_post', 'description' => 'Devolve todos os campos de um artigo, inclusive o corpo em HTML. Informe id ou slug.', 'inputSchema' => $s(['id' => ['type' => 'integer'], 'slug' => $str('Slug do artigo')])],
        ['name' => 'solicitar_upload_imagem', 'description' => 'Gera um link temporário (15 min, uso único) para enviar a foto de capa por HTTP PUT com o terminal (curl --data-binary). Depois do envio, use o media_id em criar_post. Se o PUT não funcionar, use importar_imagem_url.',
            'inputSchema' => $s(['nome_arquivo' => $str('Ex.: sessao-14-09.jpg'), 'mime' => ['type' => 'string', 'enum' => ['image/jpeg', 'image/png', 'image/webp', 'image/heic']], 'tamanho_bytes' => ['type' => 'integer', 'description' => 'Máximo 20 MB']], ['nome_arquivo', 'mime', 'tamanho_bytes'])],
        ['name' => 'importar_imagem_url', 'description' => 'Plano B: o servidor baixa a foto de uma URL https pública (ex.: link aberto do Google Drive) e devolve o media_id para usar em criar_post.',
            'inputSchema' => $s(['url' => $str('URL https pública da imagem'), 'nome_arquivo' => $str('Opcional')], ['url'])],
        ['name' => 'criar_post', 'description' => 'Cria uma notícia no portal do vereador Maicon Gonçalves. Antes, envie a foto de capa com solicitar_upload_imagem e use o media_id retornado. O status padrão é rascunho; só use "publicado" quando o usuário pedir para publicar. Devolve também url_preview para ver o rascunho sem login (7 dias).',
            'inputSchema' => $s([
                'titulo' => $str('Até 160 caracteres'), 'slug' => $str('Opcional; gerado do título se vazio'), 'linha_fina' => $str('Frase que resume a notícia; também serve de resumo para o Google'),
                'corpo_markdown' => $str('Corpo em Markdown (## subtítulos, > citação, **negrito**, listas, links). Use este ou corpo_html'), 'corpo_html' => $str('Alternativa ao Markdown'),
                'categoria' => $str('Slug de uma categoria existente (veja listar_categorias)'), 'capa_media_id' => ['type' => 'integer', 'description' => 'media_id do upload'], 'alt_capa' => $str('Texto alternativo da capa'),
                'legenda_capa' => $str('Opcional'), 'meta_description' => $str('Opcional; se vazio usa a linha fina'),
                'data_publicacao' => $str('ISO 8601. Padrão: agora. Data futura agenda'), 'status' => ['type' => 'string', 'enum' => ['rascunho', 'publicado'], 'description' => 'Padrão: rascunho'],
            ], ['titulo', 'linha_fina', 'categoria', 'capa_media_id', 'alt_capa'])],
        ['name' => 'atualizar_post', 'description' => 'Altera um artigo existente (id ou slug). Só os campos enviados mudam. Serve para corrigir erros sem passar pelo painel.',
            'inputSchema' => $s(['id' => ['type' => 'integer'], 'slug' => $str('Slug atual (se não enviar o id). Para trocar o slug, use id e envie o novo slug'), 'titulo' => $str(''), 'linha_fina' => $str(''), 'corpo_markdown' => $str(''), 'corpo_html' => $str(''),
                'categoria' => $str(''), 'capa_media_id' => ['type' => 'integer'], 'alt_capa' => $str(''), 'meta_description' => $str(''), 'data_publicacao' => $str('ISO 8601'), 'status' => ['type' => 'string', 'enum' => ['rascunho', 'publicado']]])],
        ['name' => 'publicar_post', 'description' => 'Passa um rascunho para publicado (ou agenda, se data_publicacao for futura). Use só quando o usuário pedir para publicar.',
            'inputSchema' => $s(['id' => ['type' => 'integer'], 'data_publicacao' => $str('ISO 8601; padrão agora')], ['id'])],
    ];
}

/* ---------- JSON-RPC ---------- */
function mcp_rpc(array $req): ?array
{
    $id = $req['id'] ?? null;
    $method = (string) ($req['method'] ?? '');
    $ok = function ($result) use ($id) { return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]; };
    $fail = function ($code, $msg) use ($id) { return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $msg]]; };
    if (!array_key_exists('id', $req)) return null; // notificação
    switch ($method) {
        case 'initialize':
            $v = (string) ($req['params']['protocolVersion'] ?? '2025-03-26');
            return $ok(['protocolVersion' => in_array($v, ['2024-11-05', '2025-03-26', '2025-06-18'], true) ? $v : '2025-03-26', 'capabilities' => ['tools' => ['listChanged' => false]],
                'serverInfo' => ['name' => 'portal-maicon-goncalves', 'version' => '1.0.0'],
                'instructions' => 'Publica artigos no portal do vereador Maicon Gonçalves. Fluxo: solicitar_upload_imagem -> PUT da foto -> criar_post (rascunho) -> publicar_post quando o usuário pedir.']);
        case 'ping': return $ok(new stdClass());
        case 'tools/list': return $ok(['tools' => mcp_tool_defs()]);
        case 'tools/call':
            $name = (string) ($req['params']['name'] ?? '');
            $args = $req['params']['arguments'] ?? [];
            if (!is_array($args)) $args = [];
            $postId = null;
            try {
                $data = mcp_tool($name, $args, $postId);
                mcp_log($name, $args, 'ok', $postId);
                return $ok(['content' => [['type' => 'text', 'text' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)]], 'isError' => false]);
            } catch (McpError $ex) {
                mcp_log($name, $args, 'erro: ' . mb_substr($ex->getMessage(), 0, 10), $postId);
                return $ok(['content' => [['type' => 'text', 'text' => $ex->getMessage()]], 'isError' => true]);
            } catch (Throwable $ex) {
                app_log('mcp', $name . ': ' . $ex->getMessage());
                mcp_log($name, $args, 'falha', $postId);
                return $ok(['content' => [['type' => 'text', 'text' => 'Erro no servidor ao executar ' . $name . '. Tente de novo; se continuar, avise a equipe do site.']], 'isError' => true]);
            }
    }
    return $fail(-32601, 'Método não encontrado: ' . $method);
}

/** Ponto de entrada de /mcp/{segredo}. */
function mcp_handle(string $given): void
{
    $secret = mcp_secret();
    if (strlen($secret) < 32 || !hash_equals($secret, $given)) { http_response_code(404); exit; } // não revela que existe
    if (!rate_ok('mcp', 60, 60)) json_out(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32000, 'message' => 'Muitas chamadas por minuto. Aguarde um instante.']], 429);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'POST') { http_response_code(405); header('Allow: POST'); exit; }
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 1048576) json_out(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32600, 'message' => 'Corpo maior que 1 MB.']], 413);
    $raw = (string) file_get_contents('php://input', false, null, 0, 1048577);
    if (strlen($raw) > 1048576) json_out(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32600, 'message' => 'Corpo maior que 1 MB.']], 413);
    $req = json_decode($raw, true);
    if (!is_array($req)) json_out(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32700, 'message' => 'JSON inválido.']], 400);
    mcp_tables();
    $batch = isset($req[0]);
    $resp = [];
    foreach ($batch ? $req : [$req] as $r) { if (is_array($r) && ($x = mcp_rpc($r)) !== null) $resp[] = $x; }
    if (!$resp) { http_response_code(202); exit; }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($batch ? $resp : $resp[0], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
