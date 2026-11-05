<?php
/**
 * Fiscalize Você: coleta diária das compras da Prefeitura no PNCP.
 *
 * Cron no cPanel (todo dia às 3h10; pode rodar de hora em hora durante a madrugada para esvaziar a fila da 1ª carga):
 *   10 3 * * * /usr/local/bin/php $HOME/maicongoncalves.com.br/tools/fiscalize-coletar.php >/dev/null 2>&1
 *
 * Opções: --lista (força buscar a lista de novo)  --tempo=280 (segundos)  --recalcular (só refaz os cálculos)
 * A pasta tools/ é bloqueada para a web pelo .htaccess; este arquivo só roda pela linha de comando.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/fiscalize.php';

$opt = getopt('', ['lista', 'tempo::', 'recalcular']);
$lock = fopen(storage() . '/fiscalize.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { echo "Outra coleta já está rodando.\n"; exit(0); }

try {
    if (isset($opt['recalcular'])) { echo 'Recalculadas: ' . fv_recalc_all() . " compras\n"; exit(0); }
    $r = fv_sync(max(30, (int) ($opt['tempo'] ?? 280)), isset($opt['lista']), function ($m) { echo date('H:i:s') . ' ' . $m . "\n"; });
    if ($r['erros']) echo 'Erros: ' . count($r['erros']) . "\n  " . implode("\n  ", array_slice($r['erros'], 0, 10)) . "\n";
} catch (Throwable $ex) {
    app_log('fiscalize', $ex->getMessage());
    try { q('INSERT INTO fv_log (tipo, registros_novos, erros) VALUES (?, 0, ?)', ['coleta', $ex->getMessage()]); } catch (Throwable $e2) {}
    echo 'Falhou: ' . $ex->getMessage() . "\n";
    exit(1);
}
