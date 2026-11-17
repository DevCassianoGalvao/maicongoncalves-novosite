<?php
/**
 * Backup só deste site, para uma pasta do Google Drive.
 *
 * - Banco de dados inteiro: um .zip por semana (guarda os últimos 8 no Drive e 2 no servidor).
 * - Fotos e vídeos (storage/demandas e uploads/): incremental. A primeira vez envia tudo; depois, só o que é novo.
 *   Para restaurar, basta extrair todos os "arquivos-*.zip" na pasta do site, do mais antigo para o mais novo.
 * - Com senha definida no painel, os .zip saem criptografados (AES-256). Abra com 7-Zip ou WinRAR.
 * Google: OAuth com escopo drive.file (o site só enxerga os arquivos que ele mesmo criou no Drive).
 */
defined('APP') or exit;
require_once __DIR__ . '/content.php';

const BK_KEEP_DB_DRIVE = 8;
const BK_KEEP_DB_LOCAL = 2;
const BK_PART_BYTES = 400 * 1024 * 1024; // cada .zip de arquivos tem no máximo ~400 MB
const BK_SCOPE = 'https://www.googleapis.com/auth/drive.file';

function bk_dir(): string { return storage('backup'); }
function bk_redirect_uri(): string { return abs_url('/admin/backup.php'); }
function bk_connected(): bool { return S('bk_refresh_token') !== '' && S('bk_client_id') !== ''; }
function bk_can_encrypt(): bool { return class_exists('ZipArchive') && method_exists('ZipArchive', 'setEncryptionName') && defined('ZipArchive::EM_AES_256'); }

/* ---------- registro ---------- */
function bk_log_read(): array
{
    $f = bk_dir() . '/historico.json';
    return is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
}
function bk_log_add(array $entry): void
{
    $log = bk_log_read();
    array_unshift($log, $entry);
    file_put_contents(bk_dir() . '/historico.json', json_encode(array_slice($log, 0, 40), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}
function bk_manifest(): array
{
    $f = bk_dir() . '/manifesto.json';
    return is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
}
function bk_manifest_save(array $m): void { file_put_contents(bk_dir() . '/manifesto.json', json_encode($m), LOCK_EX); }

/* ---------- Google ---------- */
function bk_http(string $method, string $url, array $headers = [], $body = null, bool $raw = false): array
{
    $ch = curl_init($url);
    $opt = [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 300, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_HTTPHEADER => array_merge($headers, ['Expect:'])];
    if ($body !== null) $opt[CURLOPT_POSTFIELDS] = $body;
    curl_setopt_array($ch, $opt);
    $res = curl_exec($ch);
    if ($res === false) { $e = curl_error($ch); curl_close($ch); throw new RuntimeException('Sem conexão com o Google: ' . $e); }
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $head = substr($res, 0, $hs);
    $out = substr($res, $hs);
    $hdrs = [];
    foreach (explode("\r\n", $head) as $l) if (strpos($l, ':') !== false) { [$k, $v] = explode(':', $l, 2); $hdrs[strtolower(trim($k))] = trim($v); }
    return [$code, $raw ? $out : (json_decode($out, true) ?? $out), $hdrs];
}
function bk_auth_url(string $state): string
{
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query(['client_id' => S('bk_client_id'), 'redirect_uri' => bk_redirect_uri(), 'response_type' => 'code',
        'scope' => BK_SCOPE, 'access_type' => 'offline', 'prompt' => 'consent', 'include_granted_scopes' => 'true', 'state' => $state]);
}
function bk_exchange_code(string $code): void
{
    [$st, $j] = bk_http('POST', 'https://oauth2.googleapis.com/token', ['Content-Type: application/x-www-form-urlencoded'], http_build_query([
        'code' => $code, 'client_id' => S('bk_client_id'), 'client_secret' => S('bk_client_secret'), 'redirect_uri' => bk_redirect_uri(), 'grant_type' => 'authorization_code']));
    if ($st !== 200 || empty($j['refresh_token'])) throw new RuntimeException('O Google não devolveu a autorização (' . (is_array($j) ? ($j['error_description'] ?? $j['error'] ?? $st) : $st) . ').');
    settings_save(['bk_refresh_token' => $j['refresh_token'], 'bk_folder_id' => '']);
}
function bk_token(): string
{
    static $tok = null, $exp = 0;
    if ($tok && time() < $exp) return $tok; // o acesso do Google vale 1 hora; renova no meio de envios longos
    [$st, $j] = bk_http('POST', 'https://oauth2.googleapis.com/token', ['Content-Type: application/x-www-form-urlencoded'], http_build_query([
        'client_id' => S('bk_client_id'), 'client_secret' => S('bk_client_secret'), 'refresh_token' => S('bk_refresh_token'), 'grant_type' => 'refresh_token']));
    if ($st !== 200 || empty($j['access_token'])) {
        $err = is_array($j) ? ($j['error'] ?? '') : '';
        throw new RuntimeException($err === 'invalid_grant' ? 'A conexão com o Google Drive expirou ou foi removida. Conecte de novo no painel (Armazenamento e backup).' : 'Não foi possível entrar no Google Drive (' . $st . ' ' . $err . ').');
    }
    $exp = time() + (int) ($j['expires_in'] ?? 3600) - 300;
    return $tok = $j['access_token'];
}
function bk_api(string $method, string $path, $json = null): array
{
    [$st, $j] = bk_http($method, 'https://www.googleapis.com/drive/v3/' . $path, ['Authorization: Bearer ' . bk_token(), 'Content-Type: application/json'], $json !== null ? json_encode($json) : null);
    if ($st >= 400) throw new RuntimeException('Google Drive respondeu ' . $st . ': ' . (is_array($j) ? ($j['error']['message'] ?? '') : ''));
    return is_array($j) ? $j : [];
}
/** Pasta "Backup – site vereador Maicon Gonçalves" (criada pelo próprio site). */
function bk_folder(): string
{
    $id = S('bk_folder_id');
    if ($id !== '') {
        try { $f = bk_api('GET', 'files/' . rawurlencode($id) . '?fields=id,trashed'); if (empty($f['trashed'])) return $id; } catch (Throwable $ex) {}
    }
    $f = bk_api('POST', 'files?fields=id', ['name' => 'Backup – site vereador Maicon Gonçalves', 'mimeType' => 'application/vnd.google-apps.folder']);
    settings_save(['bk_folder_id' => $f['id']]);
    return $f['id'];
}
function bk_list(): array
{
    $q = "'" . bk_folder() . "' in parents and trashed = false";
    $j = bk_api('GET', 'files?' . http_build_query(['q' => $q, 'fields' => 'files(id,name,size,createdTime)', 'orderBy' => 'createdTime desc', 'pageSize' => 1000]));
    return $j['files'] ?? [];
}
/** Envio em partes de 8 MB (não estoura a memória do PHP, aguenta arquivos grandes). */
function bk_upload(string $file, string $name): string
{
    $size = filesize($file);
    [$st, , $h] = bk_http('POST', 'https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable&fields=id', [
        'Authorization: Bearer ' . bk_token(), 'Content-Type: application/json; charset=UTF-8', 'X-Upload-Content-Type: application/zip', 'X-Upload-Content-Length: ' . $size],
        json_encode(['name' => $name, 'parents' => [bk_folder()]]));
    if ($st !== 200 || empty($h['location'])) throw new RuntimeException('O Google Drive recusou o envio (' . $st . ').');
    $loc = $h['location'];
    $fp = fopen($file, 'rb');
    $chunk = 8 * 1024 * 1024; $pos = 0; $tries = 0;
    while ($pos < $size) {
        fseek($fp, $pos);
        $data = fread($fp, $chunk);
        $end = $pos + strlen($data) - 1;
        try {
            [$st, $j, $h] = bk_http('PUT', $loc, ['Content-Length: ' . strlen($data), 'Content-Range: bytes ' . $pos . '-' . $end . '/' . $size], $data);
        } catch (Throwable $ex) { $st = 0; }
        if ($st === 200 || $st === 201) { fclose($fp); return (string) ($j['id'] ?? ''); }
        if ($st === 308) { $pos = isset($h['range']) && preg_match('/-(\d+)$/', $h['range'], $m) ? (int) $m[1] + 1 : $end + 1; $tries = 0; continue; }
        if (++$tries > 4) { fclose($fp); throw new RuntimeException('O envio para o Google Drive falhou no meio (' . $st . ').'); }
        sleep(3 * $tries);
        [$st2, , $h2] = bk_http('PUT', $loc, ['Content-Length: 0', 'Content-Range: bytes */' . $size]); // pergunta até onde chegou
        if ($st2 === 200 || $st2 === 201) { fclose($fp); return ''; }
        $pos = isset($h2['range']) && preg_match('/-(\d+)$/', $h2['range'], $m) ? (int) $m[1] + 1 : 0;
    }
    fclose($fp);
    return '';
}

/* ---------- conteúdo ---------- */
/** Exporta o banco inteiro em SQL (sem depender do mysqldump da hospedagem). */
function bk_dump_db(string $out): void
{
    $pdo = db();
    $fp = fopen($out, 'wb');
    fwrite($fp, "-- Backup do banco do site do vereador Maicon Gonçalves\n-- " . date('Y-m-d H:i:s') . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
    foreach ($pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM) as [$table, $type]) {
        if ($type !== 'BASE TABLE') continue;
        $create = $pdo->query('SHOW CREATE TABLE `' . $table . '`')->fetch(PDO::FETCH_NUM)[1];
        fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n$create;\n");
        $last = null; $pk = null;
        foreach ($pdo->query('SHOW KEYS FROM `' . $table . "` WHERE Key_name = 'PRIMARY'")->fetchAll() as $k) if ((int) $k['Seq_in_index'] === 1) $pk = $k['Column_name'];
        $offset = 0; $batch = 300;
        while (true) {
            $rows = $pdo->query('SELECT * FROM `' . $table . '`' . ($pk ? ' ORDER BY `' . $pk . '`' : '') . " LIMIT $batch OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) break;
            $cols = '`' . implode('`,`', array_keys($rows[0])) . '`';
            $vals = [];
            foreach ($rows as $r) $vals[] = '(' . implode(',', array_map(function ($v) use ($pdo) { return $v === null ? 'NULL' : $pdo->quote((string) $v); }, $r)) . ')';
            fwrite($fp, "INSERT INTO `$table` ($cols) VALUES\n" . implode(",\n", $vals) . ";\n");
            $offset += $batch;
            if (count($rows) < $batch) break;
        }
        fwrite($fp, "\n");
    }
    fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fp);
}
function bk_zip(string $zipPath, array $files, string $password): void
{
    $z = new ZipArchive();
    if ($z->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Não foi possível criar o arquivo de backup.');
    if ($password !== '') $z->setPassword($password);
    foreach ($files as $inZip => $disk) {
        $z->addFile($disk, $inZip);
        if (preg_match('/\.(webp|jpe?g|png|mp4|mov|webm|zip|gz)$/i', $inZip)) $z->setCompressionName($inZip, ZipArchive::CM_STORE); // já comprimidos
        if ($password !== '') $z->setEncryptionName($inZip, ZipArchive::EM_AES_256);
    }
    if (!$z->close()) throw new RuntimeException('Falha ao fechar o arquivo de backup (falta de espaço?).');
}
/** Fotos e vídeos que ainda não estão no backup. */
function bk_pending_files(array $manifest): array
{
    $roots = ['storage/demandas' => ROOT . '/storage/demandas', 'uploads' => ROOT . '/uploads'];
    $out = [];
    foreach ($roots as $prefix => $dir) {
        if (!is_dir($dir)) continue;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if (!$f->isFile()) continue;
            $rel = $prefix . '/' . str_replace('\\', '/', substr($f->getPathname(), strlen($dir) + 1));
            if (strpos($rel, '/_thumbs/') !== false || preg_match('#/\.(htaccess|gitkeep)$#', $rel) || substr($rel, -4) === '.tmp') continue; // miniaturas se refazem sozinhas
            $sig = $f->getSize() . ':' . $f->getMTime();
            if (($manifest[$rel] ?? '') !== $sig) $out[$rel] = ['path' => $f->getPathname(), 'size' => $f->getSize(), 'sig' => $sig];
        }
    }
    return $out;
}

/**
 * Faz o backup. $maxFileBytes limita quanto de fotos/vídeos novos vai nesta execução (o resto vai na próxima).
 * Retorna o resumo; lança exceção em erro (já registrado no histórico).
 */
function bk_run(int $maxFileBytes = 0, ?callable $say = null): array
{
    $say = $say ?: function ($m) {};
    @set_time_limit(0);
    @ignore_user_abort(true);
    $dir = bk_dir();
    $lock = fopen($dir . '/backup.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Já tem um backup rodando.');
    $t0 = time();
    $res = ['quando' => date('Y-m-d H:i:s'), 'ok' => false, 'banco' => 0, 'arquivos' => 0, 'arquivos_bytes' => 0, 'pendentes' => 0, 'drive' => bk_connected(), 'msg' => ''];
    $pwd = bk_can_encrypt() ? S('bk_password') : '';
    try {
        // 1) banco
        $stamp = date('Y-m-d_His');
        $sql = $dir . '/banco.sql';
        bk_dump_db($sql);
        $dbZip = $dir . '/banco-' . $stamp . '.zip';
        bk_zip($dbZip, ['banco-' . $stamp . '.sql' => $sql], $pwd);
        @unlink($sql);
        $res['banco'] = filesize($dbZip);
        $say('banco: ' . round($res['banco'] / 1048576, 1) . ' MB');
        if (bk_connected()) {
            bk_upload($dbZip, basename($dbZip));
            // mantém só os últimos bancos no Drive
            $dbs = array_values(array_filter(bk_list(), function ($f) { return strpos($f['name'], 'banco-') === 0; }));
            foreach (array_slice($dbs, BK_KEEP_DB_DRIVE) as $old) { try { bk_api('DELETE', 'files/' . rawurlencode($old['id'])); } catch (Throwable $ex) {} }
        }
        $locals = glob($dir . '/banco-*.zip') ?: [];
        rsort($locals);
        foreach (array_slice($locals, BK_KEEP_DB_LOCAL) as $old) @unlink($old);

        // 2) fotos e vídeos novos (só com o Drive: uma cópia no mesmo servidor não protege de nada)
        if (bk_connected()) {
            $man = bk_manifest();
            $pend = bk_pending_files($man);
            $res['pendentes'] = count($pend);
            $part = 1; $batch = []; $bytes = 0; $sent = 0;
            $flush = function () use (&$batch, &$bytes, &$part, &$man, &$res, $dir, $stamp, $pwd, $say) {
                if (!$batch) return;
                $zip = $dir . '/arquivos-' . $stamp . '-parte' . $part . '.zip';
                $map = []; foreach ($batch as $rel => $f) $map[$rel] = $f['path'];
                bk_zip($zip, $map, $pwd);
                bk_upload($zip, basename($zip));
                @unlink($zip);
                foreach ($batch as $rel => $f) $man[$rel] = $f['sig'];
                bk_manifest_save($man);
                $res['arquivos'] += count($batch); $res['arquivos_bytes'] += $bytes; $res['pendentes'] -= count($batch);
                $say('arquivos parte ' . $part . ': ' . count($batch) . ' arquivos, ' . round($bytes / 1048576) . ' MB');
                $batch = []; $bytes = 0; $part++;
            };
            foreach ($pend as $rel => $f) {
                if ($maxFileBytes && $sent + $f['size'] > $maxFileBytes && $sent > 0) break;
                if ($bytes + $f['size'] > BK_PART_BYTES && $batch) $flush();
                $batch[$rel] = $f; $bytes += $f['size']; $sent += $f['size'];
            }
            $flush();
        }
        $res['ok'] = true;
        $res['msg'] = bk_connected() ? 'Backup enviado ao Google Drive.' . ($res['pendentes'] ? ' Ainda faltam ' . $res['pendentes'] . ' arquivos (vão no próximo backup).' : '') : 'Banco salvo só no servidor: conecte o Google Drive para ter uma cópia fora da hospedagem.';
        settings_save(['bk_last_ok' => date('Y-m-d H:i:s')]);
    } catch (Throwable $ex) {
        $res['msg'] = $ex->getMessage();
        foreach (glob($dir . '/arquivos-*.zip') ?: [] as $z) @unlink($z);
        @unlink($dir . '/banco.sql');
    }
    $res['segundos'] = time() - $t0;
    bk_log_add($res);
    flock($lock, LOCK_UN);
    if (!$res['ok']) {
        try {
            require_once __DIR__ . '/mail.php';
            brevo_send('Falha no backup do site', '<p>O backup semanal do site do vereador falhou:</p><p><b>' . e($res['msg']) . '</b></p><p>Veja em ' . e(abs_url('/admin/backup.php')) . '</p>',
                "O backup semanal do site falhou: {$res['msg']}\nVeja em " . abs_url('/admin/backup.php'), [], null, ['tag' => 'backup']);
        } catch (Throwable $ex) {}
        throw new RuntimeException($res['msg']);
    }
    return $res;
}

/* ---------- armazenamento ---------- */
function bk_dir_size(string $dir, array $skip = []): array
{
    $total = 0; $n = 0; $byMonth = [];
    if (!is_dir($dir)) return [0, 0, []];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile()) continue;
        $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($dir) + 1));
        foreach ($skip as $s) if (strpos($rel, $s) === 0) continue 2;
        $total += $f->getSize(); $n++;
        if (preg_match('#^(\d{4})/(\d{2})/#', $rel, $m)) $byMonth[$m[1] . '-' . $m[2]] = ($byMonth[$m[1] . '-' . $m[2]] ?? 0) + $f->getSize();
    }
    ksort($byMonth);
    return [$total, $n, $byMonth];
}
/** Tamanho do banco, das fotos e vídeos (guardado por 1 hora para não pesar). */
function bk_storage_report(bool $fresh = false): array
{
    $cache = bk_dir() . '/armazenamento.json';
    if (!$fresh && is_file($cache) && filemtime($cache) > time() - 3600) return json_decode((string) file_get_contents($cache), true) ?: [];
    $tables = [];
    foreach (q('SELECT table_name t, table_rows r, data_length + index_length b FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY b DESC')->fetchAll() as $r) $tables[] = ['t' => $r['t'], 'r' => (int) $r['r'], 'b' => (int) $r['b']];
    [$dem, $demN, $demMonth] = bk_dir_size(ROOT . '/storage/demandas', ['_thumbs/']);
    [$thumbs] = bk_dir_size(ROOT . '/storage/demandas/_thumbs');
    [$up, $upN] = bk_dir_size(ROOT . '/uploads');
    $vid = 0; $vidN = 0;
    foreach (q("SELECT video FROM tickets WHERE video IS NOT NULL AND video <> ''")->fetchAll(PDO::FETCH_COLUMN) as $v) { $j = json_decode((string) $v, true); if (!empty($j['size'])) { $vid += (int) $j['size']; $vidN++; } }
    $rep = ['quando' => date('Y-m-d H:i:s'), 'tables' => $tables, 'db' => array_sum(array_column($tables, 'b')), 'demandas' => $dem, 'demandas_n' => $demN, 'demandas_mes' => $demMonth,
        'videos' => $vid, 'videos_n' => $vidN, 'thumbs' => $thumbs, 'uploads' => $up, 'uploads_n' => $upN, 'free' => @disk_free_space(ROOT) ?: null];
    file_put_contents($cache, json_encode($rep), LOCK_EX);
    return $rep;
}
function bk_mb(int $b): string
{
    if ($b >= 1073741824) return number_format($b / 1073741824, 1, ',', '.') . ' GB';
    if ($b >= 1048576) return number_format($b / 1048576, 1, ',', '.') . ' MB';
    return number_format(max(0, $b) / 1024, 0, ',', '.') . ' KB';
}
