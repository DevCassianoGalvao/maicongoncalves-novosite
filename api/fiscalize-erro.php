<?php
/** Fiscalize Você: "Encontrou algum erro?" → vira um atendimento no painel (origem: contato). */
require dirname(__DIR__) . '/app/bootstrap.php';
require_once ROOT . '/app/fiscalize.php';
header('Cache-Control: no-store');
if (!is_post()) json_out(['ok' => false, 'error' => 'Método não permitido.'], 405);
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($origin !== '' && $host !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('http://' . $host, PHP_URL_HOST)) json_out(['ok' => false, 'error' => 'Origem não permitida.'], 403);

$f = function (string $k, int $max = 255): string {
    $v = $_POST[$k] ?? '';
    return is_string($v) ? mb_substr(trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v)), 0, $max) : '';
};
$started = (int) $f('t', 20);
if ($f('website') !== '' || ($started > 0 && time() - intdiv($started, 1000) < 3)) json_out(['ok' => true]);
if (!rate_ok('fv-erro', 5, 3600)) json_out(['ok' => false, 'error' => 'Você já enviou várias mensagens agora há pouco. Tente mais tarde ou fale pelo WhatsApp.'], 429);

$contato = $f('contato', 190);
$msg = $f('mensagem', 1500);
if ($msg === '' || mb_strlen($msg) < 5) json_out(['ok' => false, 'error' => 'Conte o que está errado.']);
$email = filter_var($contato, FILTER_VALIDATE_EMAIL) ? mb_strtolower($contato) : '';
$phone = $email === '' ? preg_replace('/[^\d()+\- ]/', '', $contato) : '';
if ($email === '' && strlen(preg_replace('/\D/', '', $phone)) < 10) json_out(['ok' => false, 'error' => 'Informe um e-mail ou um WhatsApp com DDD para a gente responder.']);

$ref = '';
$iid = (int) $f('item', 12);
if ($iid && db_or_null()) {
    $it = q('SELECT i.titulo_simples, i.descricao, i.quantidade, c.numero_processo, c.pncp_id, c.id cid FROM fv_itens i JOIN fv_compras c ON c.id = i.compra_id WHERE i.id = ?', [$iid])->fetch();
    if ($it) $ref = 'Compra: ' . fv_qty($it['quantidade']) . ' × ' . ($it['titulo_simples'] ?: fv_short_title((string) $it['descricao'])) . ' · processo ' . ($it['numero_processo'] ?: '—') . ' · PNCP ' . $it['pncp_id'] . "\nPainel: " . abs_url('/admin/fiscalize-compra.php?id=' . $it['cid'] . '#item-' . $iid) . "\n\n";
}
try {
    $id = ticket_create(['source' => 'contato', 'subject' => 'Fiscalize Você: possível erro', 'name' => $f('nome', 120) ?: null, 'phone' => $phone ?: null, 'email' => $email, 'description' => $ref . $msg]);
} catch (Throwable $ex) {
    app_log('fiscalize', $ex->getMessage());
    json_out(['ok' => false, 'error' => 'Não foi possível enviar agora. Tente pelo WhatsApp.'], 500);
}
json_out(['ok' => true, 'code' => ticket_code($id)]);
