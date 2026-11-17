<?php
/**
 * Backup semanal só deste site (banco + fotos e vídeos novos) para o Google Drive.
 * Cron no cPanel (domingo, 4h20):  20 4 * * 0 /usr/local/bin/php $HOME/maicongoncalves.com.br/tools/backup.php >/dev/null 2>&1
 * A pasta tools/ é bloqueada para a web; roda só pela linha de comando. Configuração: Painel › Armazenamento e backup.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/backup.php';
try {
    $r = bk_run(0, function ($m) { echo date('H:i:s') . ' ' . $m . "\n"; });
    echo $r['msg'] . "\n";
} catch (Throwable $ex) {
    echo 'Falhou: ' . $ex->getMessage() . "\n";
    exit(1);
}
