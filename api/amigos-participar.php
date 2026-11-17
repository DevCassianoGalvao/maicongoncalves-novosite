<?php
/** Amigos do Bem: "Sua instituição quer participar?" → rascunho no painel + atendimento para a equipe ligar. */
require dirname(__DIR__) . '/app/bootstrap.php';
require_once ROOT . '/app/amigos.php';
require_once ROOT . '/app/tickets.php';
header('Cache-Control: no-store');
if (!is_post()) json_out(['ok' => false, 'error' => 'Método não permitido.'], 405);
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($origin !== '' && $host !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('http://' . $host, PHP_URL_HOST)) json_out(['ok' => false, 'error' => 'Origem não permitida.'], 403);
$f = function (string $k, int $max = 255): string { $v = $_POST[$k] ?? ''; return is_string($v) ? mb_substr(trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v)), 0, $max) : ''; };
$started = (int) $f('t', 20);
if ($f('website') !== '' || ($started > 0 && time() - intdiv($started, 1000) < 3)) json_out(['ok' => true]);
if (!rate_ok('ab-part', 4, 3600)) json_out(['ok' => false, 'error' => 'Você já enviou agora há pouco. Tente mais tarde ou fale pelo WhatsApp do gabinete.'], 429);
$nome = $f('nome', 160); $resp = $f('responsavel', 120); $wa = $f('whatsapp', 30);
if ($nome === '' || $resp === '') json_out(['ok' => false, 'error' => 'Preencha o nome da instituição e o seu nome.']);
if (strlen(ab_digits($wa)) < 10) json_out(['ok' => false, 'error' => 'Informe um WhatsApp com DDD para a gente falar com você.']);
$causa = isset(AB_CAUSAS[$f('causa', 20)]) ? $f('causa', 20) : 'outra';
$msg = $f('mensagem', 1200);
try {
    ab_tables();
    q('INSERT INTO ab_instituicoes (slug, nome, causa, bairro, whatsapp, resp_nome, resp_telefone, obs_doacao, status, origem, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, \'rascunho\', \'formulario\', ?)',
        [ab_slug_unique($nome), $nome, $causa, $f('bairro', 120) ?: null, $wa, $resp, $wa, null, ab_new_token()]);
    $iid = (int) db()->lastInsertId();
    if ($msg !== '') q('UPDATE ab_instituicoes SET nota_interna = ? WHERE id = ?', ['Mensagem no cadastro: ' . $msg, $iid]);
    ticket_create(['source' => 'contato', 'subject' => 'Amigos do Bem: instituição quer participar', 'name' => $resp, 'phone' => $wa, 'email' => '',
        'description' => "Instituição: $nome (" . AB_CAUSAS[$causa] . ")\nBairro: " . ($f('bairro', 120) ?: '—') . "\nO que precisam: " . ($msg ?: '—') .
            "\n\nCadastro em rascunho no painel: " . abs_url('/admin/amigos-instituicao.php?id=' . $iid)]);
} catch (Throwable $ex) {
    app_log('amigos', $ex->getMessage());
    json_out(['ok' => false, 'error' => 'Não foi possível enviar agora. Tente pelo WhatsApp do gabinete.'], 500);
}
json_out(['ok' => true]);
