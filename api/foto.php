<?php
/** Mostra a foto de uma demanda: só se ela já estiver no mapa, ou para quem está logado no painel. */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/tickets.php';
require ROOT . '/app/auth.php';

$id = (int) ($_GET['t'] ?? 0);
$src = (string) ($_GET['f'] ?? '');
$file = ticket_photo_path($src);
$t = $id && $file ? q('SELECT status, photos, after_photo FROM tickets WHERE id = ?', [$id])->fetch() : null;
$belongs = $t && (strpos((string) $t['photos'], json_encode($src, JSON_UNESCAPED_SLASHES)) !== false || strpos((string) $t['after_photo'], json_encode($src, JSON_UNESCAPED_SLASHES)) !== false);
$public = $t && in_array($t['status'], ['publicado', 'resolvido'], true);
if (!$belongs || (!$public && !auth_user())) { http_response_code(404); exit; }

header('Content-Type: ' . (substr($file, -4) === 'webp' ? 'image/webp' : 'image/jpeg'));
header('Content-Length: ' . filesize($file));
header('Cache-Control: ' . ($public ? 'public, max-age=604800' : 'private, no-store'));
header('X-Content-Type-Options: nosniff');
readfile($file);
