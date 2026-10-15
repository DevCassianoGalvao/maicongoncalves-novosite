<?php
/**
 * Exporta planilhas (CSV que abre no Excel e no Google Planilhas):
 *  - pessoas: uma linha por pessoa que enviou demanda (junta pelo e-mail ou WhatsApp)
 *  - demandas: uma linha por demanda
 * Só o administrador. Cada exportação fica registrada em storage/logs/exportacoes-AAAA-MM.log.
 */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
$user = admin_guard(true);

/** Célula segura para planilha (evita fórmulas maliciosas como =HYPERLINK(...)). */
function cell($v): string
{
    $v = str_replace(["\r\n", "\r", "\n"], ' ', trim((string) $v));
    if ($v !== '' && strpos('=+-@', $v[0]) !== false && !preg_match('/^-?[\d.,]+$/', $v)) $v = "'" . $v;
    return '"' . str_replace('"', '""', $v) . '"';
}
function csv_out(string $name, array $head, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Cache-Control: no-store');
    echo "\xEF\xBB\xBF"; // acentos certos no Excel
    echo implode(';', array_map('cell', $head)) . "\r\n";
    foreach ($rows as $r) echo implode(';', array_map('cell', $r)) . "\r\n";
    exit;
}
$fmt = function (?string $d): string { return $d ? date('d/m/Y H:i', strtotime($d)) : ''; };
$subs = [];
foreach (q('SELECT email, unsub_at FROM subscribers')->fetchAll() as $s) $subs[$s['email']] = $s['unsub_at'] === null;

$tipo = (string) ($_GET['tipo'] ?? '');
if ($tipo === 'pessoas') {
    $people = [];
    foreach (q("SELECT * FROM tickets ORDER BY created_at")->fetchAll() as $t) {
        $mail = mb_strtolower(trim((string) $t['email']));
        $digits = preg_replace('/\D+/', '', (string) $t['phone']);
        $key = $mail !== '' ? 'e:' . $mail : ($digits !== '' ? 'p:' . substr($digits, -11) : 't:' . $t['id']);
        if (!isset($people[$key])) $people[$key] = ['name' => '', 'email' => '', 'phone' => '', 'hoods' => [], 'codes' => [], 'first' => $t['created_at'], 'last' => $t['created_at']];
        $p = &$people[$key];
        if ($t['name']) $p['name'] = $t['name'];
        if ($mail !== '') $p['email'] = $mail;
        if ($t['phone']) $p['phone'] = $t['phone'];
        if ($t['neighborhood']) $p['hoods'][$t['neighborhood']] = true;
        $p['codes'][] = ticket_code((int) $t['id']);
        $p['last'] = $t['created_at'];
        unset($p);
    }
    $rows = [];
    foreach ($people as $p) {
        $rows[] = [$p['name'], $p['email'], $p['phone'], implode(', ', array_keys($p['hoods'])), count($p['codes']), implode(' ', $p['codes']),
            $fmt($p['first']), $fmt($p['last']), $p['email'] !== '' && !empty($subs[$p['email']]) ? 'Sim' : 'Não'];
    }
    usort($rows, function ($a, $b) { return strcasecmp($a[0], $b[0]); });
    app_log('exportacoes', $user['email'] . ' exportou pessoas (' . count($rows) . ')');
    csv_out('pessoas-demandas-' . date('Y-m-d') . '.csv',
        ['Nome', 'E-mail', 'WhatsApp', 'Bairro(s)', 'Qtd. de demandas', 'Protocolos', 'Primeira demanda', 'Última demanda', 'Aceita novidades por e-mail'], $rows);
}
if ($tipo === 'demandas') {
    $rows = [];
    foreach (q('SELECT * FROM tickets ORDER BY id')->fetchAll() as $t) {
        $mail = mb_strtolower(trim((string) $t['email']));
        $rows[] = [ticket_code((int) $t['id']), TICKET_STATUS[$t['status']] ?? $t['status'],
            $t['source'] === 'contato' ? ($t['subject'] ?: 'Contato') : (TICKET_CATS[$t['category']] ?? ''),
            ['mapa' => 'Site', 'painel' => 'Painel', 'contato' => 'Formulário antigo'][$t['source']] ?? $t['source'],
            $t['name'], $mail, $t['phone'], $t['address'], $t['neighborhood'], $t['lat'] !== null ? $t['lat'] . ',' . $t['lng'] : '',
            $t['description'], $t['department'], $t['protocol'], $fmt($t['created_at']), $fmt($t['published_at']), $fmt($t['resolved_at']),
            $mail !== '' && !empty($subs[$mail]) ? 'Sim' : 'Não'];
    }
    app_log('exportacoes', $user['email'] . ' exportou demandas (' . count($rows) . ')');
    csv_out('demandas-' . date('Y-m-d') . '.csv',
        ['Protocolo', 'Situação', 'Tipo', 'Origem', 'Nome', 'E-mail', 'WhatsApp', 'Rua', 'Bairro', 'Coordenadas', 'Relato', 'Secretaria', 'Nº ofício', 'Recebida em', 'Enviada à Prefeitura em', 'Resolvida em', 'Aceita novidades por e-mail'], $rows);
}

$nTickets = (int) q('SELECT COUNT(*) FROM tickets')->fetchColumn();
$nMails = (int) q("SELECT COUNT(DISTINCT LOWER(email)) FROM tickets WHERE email IS NOT NULL AND email <> ''")->fetchColumn();
$nSubs = (int) q('SELECT COUNT(*) FROM subscribers WHERE unsub_at IS NULL')->fetchColumn();
admin_head('Exportar dados', 'export');
?>
<div class="top"><div><h1>Exportar dados</h1><p>Planilhas para abrir no Excel ou no Google Planilhas.</p></div>
  <a class="btn btn--ghost" href="<?= e(url('/admin/atendimentos.php')) ?>"><?= icon('arrow-left') ?>Atendimentos</a></div>
<div class="grid g2 exp">
  <div><div class="card">
    <h2>Pessoas</h2>
    <p class="muted" style="margin:-4px 0 14px">Uma linha por pessoa que enviou demanda: nome, e-mail, WhatsApp, bairros, quantas demandas e se aceita receber novidades.</p>
    <p class="small" style="margin:0 0 16px"><b><?= $nMails ?></b> e-mails diferentes · <b><?= $nSubs ?></b> aceitam novidades</p>
    <a class="btn btn--or" href="<?= e(url('/admin/exportar.php?tipo=pessoas')) ?>"><?= icon('download-simple') ?>Baixar planilha de pessoas</a>
  </div></div>
  <div><div class="card">
    <h2>Demandas</h2>
    <p class="muted" style="margin:-4px 0 14px">Uma linha por demanda, com situação, local, relato, secretaria e as datas.</p>
    <p class="small" style="margin:0 0 16px"><b><?= $nTickets ?></b> demandas no total</p>
    <a class="btn btn--ghost" href="<?= e(url('/admin/exportar.php?tipo=demandas')) ?>"><?= icon('download-simple') ?>Baixar planilha de demandas</a>
  </div></div>
</div>
<div class="alert alert--warn" style="margin-top:18px"><?= icon('warning-circle') ?><span>São dados pessoais (LGPD). Guarde a planilha em local seguro e, para disparos de e-mail, use só quem está como <b>"Sim"</b> em "Aceita novidades por e-mail".</span></div>
<?php admin_foot();
