<?php
/**
 * Atualiza os números do Impostômetro (Tesouro Nacional/Siconfi e IBGE).
 * A página já se atualiza sozinha quando os dados passam de 3 dias; este cron é só uma garantia.
 * Cron (cPanel), toda segunda às 5h10:  10 5 * * 1 /usr/local/bin/php $HOME/maicongoncalves.com.br/tools/impostometro-coletar.php >/dev/null 2>&1
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/impostometro.php';
$r = im_refresh(in_array('--tudo', $argv, true));
echo ($r['ok'] ? 'OK: ' : 'Falhou: ') . $r['msg'] . "\n";
exit($r['ok'] ? 0 : 1);
