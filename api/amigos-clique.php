<?php
/** Amigos do Bem: conta cliques no WhatsApp de cada instituição (para mostrar no painel). */
require dirname(__DIR__) . '/app/bootstrap.php';
require_once ROOT . '/app/amigos.php';
header('Cache-Control: no-store');
if (!is_post()) json_out(['ok' => false], 405);
$slug = (string) ($_POST['slug'] ?? '');
if (!preg_match('/^[a-z0-9-]{1,120}$/', $slug) || !db_or_null() || !rate_ok('ab-clique-' . $slug, 5, 3600)) json_out(['ok' => true]);
try { ab_tables(); q("UPDATE ab_instituicoes SET cliques_wa = cliques_wa + 1 WHERE slug = ? AND status = 'publicada'", [$slug]); } catch (Throwable $ex) {}
json_out(['ok' => true]);
