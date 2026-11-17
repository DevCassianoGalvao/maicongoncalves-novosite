<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require ROOT . '/app/backup.php';
$user = admin_guard(true);
$self = url('/admin/backup.php');

// volta do Google (autorização)
if (isset($_GET['code']) || isset($_GET['error'])) {
    if (!hash_equals((string) ($_SESSION['bk_state'] ?? ''), (string) ($_GET['state'] ?? ''))) { flash('err', 'Autorização inválida. Tente conectar de novo.'); redirect($self); }
    unset($_SESSION['bk_state']);
    if (isset($_GET['error'])) { flash('err', 'O Google não autorizou (' . e((string) $_GET['error']) . ').'); redirect($self); }
    try { bk_exchange_code((string) $_GET['code']); bk_folder(); flash('ok', 'Google Drive conectado. A pasta "Backup – site vereador Maicon Gonçalves" foi criada no seu Drive.'); }
    catch (Throwable $ex) { flash('err', $ex->getMessage()); }
    redirect($self);
}

if (is_post()) {
    csrf_check();
    $do = in('do');
    if ($do === 'google') {
        $cid = in('client_id', '', 200); $sec = in('client_secret', '', 200);
        if ($cid === '' || ($sec === '' && S('bk_client_secret') === '')) { flash('err', 'Cole o ID do cliente e a chave secreta do Google.'); redirect($self . '#drive'); }
        settings_save(['bk_client_id' => $cid] + ($sec !== '' ? ['bk_client_secret' => $sec] : []));
        $_SESSION['bk_state'] = bin2hex(random_bytes(16));
        redirect(bk_auth_url($_SESSION['bk_state']));
    }
    if ($do === 'desconectar') {
        settings_save(['bk_refresh_token' => '', 'bk_folder_id' => '']);
        flash('ok', 'Google Drive desconectado. Os backups que já estão lá continuam no seu Drive.');
        redirect($self . '#drive');
    }
    if ($do === 'senha') {
        $p = (string) ($_POST['senha'] ?? '');
        if ($p !== '' && mb_strlen($p) < 10) { flash('err', 'Use uma senha com pelo menos 10 caracteres.'); redirect($self . '#senha'); }
        settings_save(['bk_password' => $p]);
        flash('ok', $p === '' ? 'Senha removida: os próximos backups saem sem criptografia.' : 'Senha salva. Guarde essa senha num lugar seguro: sem ela não dá para abrir os backups.');
        redirect($self . '#senha');
    }
    if ($do === 'agora') {
        session_write_close();
        try {
            $r = bk_run(150 * 1024 * 1024);
            flash('ok', $r['msg'] . ' Banco: ' . bk_mb($r['banco']) . ($r['arquivos'] ? ', ' . $r['arquivos'] . ' arquivos (' . bk_mb($r['arquivos_bytes']) . ')' : '') . '.');
        } catch (Throwable $ex) { flash('err', 'O backup falhou: ' . $ex->getMessage()); }
        redirect($self);
    }
    if ($do === 'recalcular') { bk_storage_report(true); redirect($self); }
}
if (($_GET['baixar'] ?? '') !== '') {
    $f = basename((string) $_GET['baixar']);
    $path = bk_dir() . '/' . $f;
    if (!preg_match('/^banco-[\d_-]+\.zip$/', $f) || !is_file($path)) { flash('err', 'Arquivo não encontrado.'); redirect($self); }
    app_log('backup', 'Download do banco ' . $f . ' por ' . $user['email']);
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $f . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

$rep = bk_storage_report();
$log = bk_log_read();
$last = S('bk_last_ok');
$late = !$last || strtotime($last) < strtotime('-9 days');
$locals = glob(bk_dir() . '/banco-*.zip') ?: [];
rsort($locals);
$months = array_slice($rep['demandas_mes'] ?? [], -12, null, true);
$maxM = $months ? max($months) : 1;

admin_head('Armazenamento e backup', 'bk');
?>
<div class="top">
  <div><h1>Armazenamento e backup</h1><p>Quanto o site ocupa e a cópia de segurança semanal, só deste site.</p></div>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="agora"><button class="btn btn--or" type="submit"<?= bk_connected() ? '' : ' title="Sem o Google Drive, só o banco é salvo no servidor"' ?>><?= icon('cloud-arrow-up') ?>Fazer backup agora</button></form>
</div>

<?php if (!bk_connected()): ?>
<div class="alert alert--warn"><?= icon('warning-circle') ?><span>O Google Drive ainda não está conectado. Sem ele, o backup fica só no próprio servidor. Siga o passo a passo em <a href="#drive">Google Drive</a>.</span></div>
<?php elseif ($late): ?>
<div class="alert alert--err"><?= icon('warning-circle') ?><span>O último backup completo foi <?= $last ? 'em ' . date('d/m/Y', strtotime($last)) : 'nunca' ?>. Confira se a tarefa agendada (cron) está ativa ou clique em "Fazer backup agora".</span></div>
<?php endif; ?>

<div class="bk-kpis">
  <div class="card bk-kpi"><small>Banco de dados</small><b><?= bk_mb((int) ($rep['db'] ?? 0)) ?></b><span><?= count($rep['tables'] ?? []) ?> tabelas</span></div>
  <div class="card bk-kpi"><small>Fotos e vídeos das demandas</small><b><?= bk_mb((int) ($rep['demandas'] ?? 0)) ?></b><span><?= (int) ($rep['demandas_n'] ?? 0) ?> arquivos · vídeos: <?= bk_mb((int) ($rep['videos'] ?? 0)) ?> (<?= (int) ($rep['videos_n'] ?? 0) ?>)</span></div>
  <div class="card bk-kpi"><small>Imagens do blog e páginas</small><b><?= bk_mb((int) ($rep['uploads'] ?? 0)) ?></b><span><?= (int) ($rep['uploads_n'] ?? 0) ?> arquivos</span></div>
  <div class="card bk-kpi"><small>Miniaturas</small><b><?= bk_mb((int) ($rep['thumbs'] ?? 0)) ?></b><span>refeitas sozinhas, não entram no backup</span></div>
</div>

<div class="grid g2" style="align-items:start;margin-top:18px">
  <div class="card">
    <h2>Fotos e vídeos por mês</h2>
<?php if (!$months): ?>
    <p class="empty">Ainda não há arquivos.</p>
<?php else: ?>
    <div class="bk-bars">
<?php foreach ($months as $m => $b): ?>
      <div class="bk-bar"><span><?= substr($m, 5, 2) . '/' . substr($m, 2, 2) ?></span><i style="width:<?= max(1, round($b / $maxM * 100)) ?>%"></i><b><?= bk_mb($b) ?></b></div>
<?php endforeach; ?>
    </div>
<?php endif; ?>
    <form method="post" class="row" style="justify-content:space-between;margin-top:12px"><?= csrf_field() ?><input type="hidden" name="do" value="recalcular">
      <span class="small muted">Calculado em <?= !empty($rep['quando']) ? date('d/m H:i', strtotime($rep['quando'])) : '—' ?><?= !empty($rep['free']) ? ' · espaço livre no servidor: ' . bk_mb((int) $rep['free']) : '' ?></span>
      <button class="link-btn small" type="submit">Recalcular</button></form>
  </div>
  <div class="card">
    <h2>Maiores tabelas do banco</h2>
    <table class="tbl">
      <thead><tr><th>Tabela</th><th>Registros</th><th>Tamanho</th></tr></thead>
      <tbody>
<?php foreach (array_slice($rep['tables'] ?? [], 0, 8) as $t): ?>        <tr><td><code><?= e($t['t']) ?></code></td><td class="small"><?= number_format($t['r'], 0, ',', '.') ?></td><td class="small"><?= bk_mb($t['b']) ?></td></tr>
<?php endforeach; ?>
      </tbody>
    </table>
    <p class="hint small muted" style="margin:10px 0 0">As tabelas <code>fv_*</code> são do Fiscalize Você (guardam a cópia original de cada compra do PNCP).</p>
  </div>
</div>

<div class="grid g2" style="align-items:start;margin-top:18px">
  <div class="card" id="drive">
    <h2>Google Drive</h2>
<?php if (bk_connected()): ?>
    <p class="small"><span class="badge badge--pub">Conectado</span> Os backups vão para a pasta <b>Backup – site vereador Maicon Gonçalves</b> no Drive da conta que autorizou. O site só enxerga os arquivos que ele mesmo criou lá.</p>
    <form method="post" data-confirm="Desconectar o Google Drive? Os próximos backups ficam só no servidor."><?= csrf_field() ?><input type="hidden" name="do" value="desconectar"><button class="link-btn small" type="submit" style="color:var(--err)">Desconectar</button></form>
<?php else: ?>
    <p class="small">Configuração feita uma vez só, com a conta Google onde os backups vão ficar (uns 10 minutos):</p>
    <ol class="small bk-steps">
      <li>Entre em <a href="https://console.cloud.google.com/projectcreate" target="_blank" rel="noopener">console.cloud.google.com</a> com essa conta e crie um projeto chamado <b>Backup site Maicon</b>.</li>
      <li>Em <a href="https://console.cloud.google.com/apis/library/drive.googleapis.com" target="_blank" rel="noopener">APIs e serviços › Biblioteca</a>, procure <b>Google Drive API</b> e clique em <b>Ativar</b>.</li>
      <li>Em <a href="https://console.cloud.google.com/auth/branding" target="_blank" rel="noopener">Tela de consentimento OAuth</a>: tipo <b>Externo</b>, nome do app <b>Backup site Maicon</b>, seu e-mail nos campos de contato. Depois, em <b>Público-alvo</b>, clique em <b>Publicar aplicativo</b> (sem isso a conexão expira em 7 dias).</li>
      <li>Em <a href="https://console.cloud.google.com/auth/clients" target="_blank" rel="noopener">Clientes</a> › <b>Criar cliente</b>: tipo <b>Aplicativo da Web</b>; em <b>URIs de redirecionamento autorizados</b> cole exatamente:<br><code class="bk-uri"><?= e(bk_redirect_uri()) ?></code></li>
      <li>Copie o <b>ID do cliente</b> e a <b>Chave secreta</b> para os campos abaixo e clique em Conectar. O Google vai avisar que o app não foi verificado: clique em <b>Avançado › Acessar</b> (é o seu próprio app).</li>
    </ol>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="do" value="google">
      <div class="fd"><label for="cid">ID do cliente</label><input id="cid" name="client_id" value="<?= e(S('bk_client_id')) ?>" placeholder="....apps.googleusercontent.com"></div>
      <div class="fd"><label for="csec">Chave secreta do cliente</label><input id="csec" name="client_secret" type="password" placeholder="<?= S('bk_client_secret') !== '' ? '(já salva — deixe em branco para manter)' : 'GOCSPX-...' ?>" autocomplete="off"></div>
      <button class="btn" type="submit"><?= icon('google-logo') ?>Conectar Google Drive</button>
    </form>
<?php endif; ?>
    <h2 style="margin-top:20px">Tarefa agendada</h2>
    <p class="small">No cPanel, em <b>Cron Jobs</b>, adicione (todo domingo às 4h20). Minuto <code>20</code>, Hora <code>4</code>, Dia <code>*</code>, Mês <code>*</code>, Dia da semana <code>0</code> e o comando:</p>
    <pre class="code"><?= e('/usr/local/bin/php ' . ROOT . '/tools/backup.php >/dev/null 2>&1') ?></pre>
  </div>

  <div>
    <form method="post" class="card" id="senha">
      <?= csrf_field() ?><input type="hidden" name="do" value="senha">
      <h2>Senha dos backups</h2>
<?php if (bk_can_encrypt()): ?>
      <p class="small">O banco tem nome, telefone e e-mail de quem enviou demandas. Com senha, os arquivos no Drive ficam criptografados (AES-256): mesmo que alguém acesse o Drive, não consegue abrir. Para abrir, use 7-Zip ou WinRAR.</p>
      <div class="fd"><label for="senha-i">Senha <?= S('bk_password') !== '' ? '<span class="badge badge--pub">definida</span>' : '<span class="badge badge--draft">sem senha</span>' ?></label><input id="senha-i" name="senha" type="password" autocomplete="new-password" placeholder="<?= S('bk_password') !== '' ? 'Digite uma nova para trocar (vazio remove)' : 'Pelo menos 10 caracteres' ?>"></div>
      <p class="hint small" style="color:var(--err)">Guarde a senha fora do site (num gerenciador de senhas). Sem ela, não dá para restaurar.</p>
      <button class="btn btn--ghost" type="submit"><?= icon('check', 'bold') ?>Salvar senha</button>
<?php else: ?>
      <p class="small muted">A hospedagem não oferece criptografia de ZIP no PHP. Os backups saem sem senha; o acesso fica protegido pela conta Google.</p>
<?php endif; ?>
    </form>
    <div class="card">
      <h2>Últimos backups</h2>
<?php if (!$log): ?>
      <p class="empty">Nenhum backup feito ainda.</p>
<?php else: ?>
      <ul class="bk-log">
<?php foreach (array_slice($log, 0, 10) as $l): ?>
        <li class="<?= $l['ok'] ? 'ok' : 'err' ?>"><b><?= date('d/m/Y H:i', strtotime($l['quando'])) ?></b> · <?= $l['ok'] ? 'ok' : 'falhou' ?> · banco <?= bk_mb((int) $l['banco']) ?><?= $l['arquivos'] ? ' · ' . (int) $l['arquivos'] . ' arquivos (' . bk_mb((int) $l['arquivos_bytes']) . ')' : '' ?><br><span class="small muted"><?= e($l['msg']) ?></span></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
<?php if ($locals): ?>
      <p class="small" style="margin:12px 0 0">Cópia do banco no servidor: <?php foreach ($locals as $k => $lf): ?><a href="<?= e($self . '?baixar=' . rawurlencode(basename($lf))) ?>"><?= e(basename($lf)) ?></a> (<?= bk_mb((int) filesize($lf)) ?>)<?= $k < count($locals) - 1 ? ', ' : '' ?><?php endforeach; ?></p>
<?php endif; ?>
    </div>
    <details class="card">
      <summary><b>Como restaurar</b></summary>
      <ol class="small bk-steps">
        <li>No Google Drive, baixe o <b>banco-*.zip</b> mais recente e <b>todos</b> os <b>arquivos-*.zip</b>.</li>
        <li>Banco: extraia o .sql (com a senha) e importe no phpMyAdmin do cPanel, no banco do site (aba Importar).</li>
        <li>Arquivos: extraia os arquivos-*.zip na pasta do site, do mais antigo para o mais novo, aceitando substituir. Eles recriam as pastas <code>storage/demandas</code> e <code>uploads</code>.</li>
        <li>O código do site vem do GitHub (Deploy no cPanel) e o arquivo <code>.env</code> com as senhas deve estar guardado à parte.</li>
      </ol>
    </details>
  </div>
</div>
<?php admin_foot();
