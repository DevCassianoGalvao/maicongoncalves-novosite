<?php
/**
 * Mostra a foto ou o vídeo de uma demanda: só se ela já estiver no mapa, ou para quem está logado no painel.
 * Vídeos são servidos em partes (Range), para começar a tocar sem baixar tudo.
 */
require dirname(__DIR__) . '/app/bootstrap.php';
require_once ROOT . '/app/tickets.php';
require ROOT . '/app/auth.php';

$id = (int) ($_GET['t'] ?? 0);
$src = (string) ($_GET['f'] ?? '');
$isVideo = (bool) preg_match('#\.(mp4|mov|webm)$#', $src);
$file = $isVideo ? ticket_video_path($src) : ticket_photo_path($src);
$t = $id && $file ? q('SELECT * FROM tickets WHERE id = ?', [$id])->fetch() : null;
$needle = json_encode($src, JSON_UNESCAPED_SLASHES);
$belongs = $t && (strpos((string) $t['photos'], $needle) !== false || strpos((string) $t['after_photo'], $needle) !== false || strpos((string) ($t['video'] ?? ''), $needle) !== false || strpos((string) ($t['instagram_thumb'] ?? ''), $needle) !== false);
$public = $t && in_array($t['status'], ['publicado', 'resolvido'], true);
if (!$belongs || (!$public && !auth_user())) { http_response_code(404); exit; }

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$type = ['webp' => 'image/webp', 'jpg' => 'image/jpeg', 'mp4' => 'video/mp4', 'mov' => 'video/quicktime', 'webm' => 'video/webm'][$ext] ?? 'application/octet-stream';
$size = filesize($file);
header('Content-Type: ' . $type);
header('Cache-Control: ' . ($public ? 'public, max-age=604800' : 'private, no-store'));
header('X-Content-Type-Options: nosniff');
header('Accept-Ranges: bytes');
if (session_status() === PHP_SESSION_ACTIVE) session_write_close(); // não trava outras abas enquanto o vídeo carrega

$start = 0; $end = $size - 1;
if ($isVideo && isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $m)) {
    if ($m[1] === '' && $m[2] !== '') { $start = max(0, $size - (int) $m[2]); }
    else { $start = (int) $m[1]; if ($m[2] !== '') $end = min((int) $m[2], $size - 1); }
    if ($start > $end || $start >= $size) { http_response_code(416); header('Content-Range: bytes */' . $size); exit; }
    http_response_code(206);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
}
$len = $end - $start + 1;
header('Content-Length: ' . $len);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') exit;
@set_time_limit(0);
$fh = fopen($file, 'rb');
fseek($fh, $start);
while ($len > 0 && !feof($fh) && !connection_aborted()) {
    $chunk = fread($fh, (int) min(262144, $len));
    if ($chunk === false || $chunk === '') break;
    echo $chunk; flush();
    $len -= strlen($chunk);
}
fclose($fh);
