<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
admin_guard(true);

$errors = [];
$v = settings_all(true);
if (is_post()) {
    csrf_check();
    $v = array_merge($v, array_map(function ($x) { return is_string($x) ? mb_substr(trim($x), 0, 500) : ''; }, array_intersect_key($_POST, SETTINGS_DEFAULTS)));
    $digits = preg_replace('/\D+/', '', $v['whatsapp']);
    if ($digits !== '' && strlen($digits) <= 11) $digits = '55' . $digits;
    if ($digits !== '' && (strlen($digits) < 12 || strlen($digits) > 13)) $errors[] = 'WhatsApp: informe DDD + número, ex.: (22) 99801-2278.';
    $v['whatsapp'] = $digits;
    if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido.';
    foreach (SOCIALS as $k => [$label]) {
        if ($v[$k] !== '' && !preg_match('#^https?://#i', $v[$k])) $v[$k] = 'https://' . ltrim($v[$k], '/');
        if ($v[$k] !== '' && !filter_var($v[$k], FILTER_VALIDATE_URL)) $errors[] = $label . ': link inválido.';
    }
    if (!$errors) {
        settings_save(array_intersect_key($v, SETTINGS_DEFAULTS));
        flash('ok', 'Configurações salvas. Já aparecem no site.');
        redirect(url('/admin/configuracoes.php'));
    }
}
function wa_pretty(string $d): string
{
    $d = preg_replace('/^55/', '', $d);
    return strlen($d) === 11 ? sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 5), substr($d, 7)) : (strlen($d) === 10 ? sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 4), substr($d, 6)) : $d);
}

admin_head('Configurações', 'settings');
?>
<div class="top"><div><h1>Configurações do site</h1><p>Contato e redes sociais que aparecem no topo e no rodapé do site.</p></div></div>
<?php foreach ($errors as $err): ?>
<div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($err) ?></span></div>
<?php endforeach; ?>
<form method="post">
  <?= csrf_field() ?>
  <div class="grid g2" style="align-items:start">
    <div class="card">
      <h2>Contato</h2>
      <div class="fd"><label for="whatsapp">WhatsApp</label><input type="tel" id="whatsapp" name="whatsapp" value="<?= e(wa_pretty($v['whatsapp'])) ?>" placeholder="(22) 99999-9999">
        <p class="hint">Usado no botão flutuante do site.</p></div>
      <div class="fd"><label for="whatsapp_msg">Mensagem que já vem escrita no WhatsApp</label><input type="text" id="whatsapp_msg" name="whatsapp_msg" value="<?= e($v['whatsapp_msg']) ?>"></div>
      <div class="fd"><label for="email">E-mail exibido no site</label><input type="email" id="email" name="email" value="<?= e($v['email']) ?>">
        <p class="hint">As demandas enviadas pelo site chegam no e-mail definido no arquivo .env (MAIL_TO).</p></div>
      <div class="fd"><label for="address">Endereço</label><textarea id="address" name="address" rows="2"><?= e($v['address']) ?></textarea><p class="hint">Cada linha vira uma linha no rodapé.</p></div>
      <div class="fd"><label for="office">Gabinete</label><input type="text" id="office" name="office" value="<?= e($v['office']) ?>"></div>
    </div>
    <div class="card">
      <h2>Redes sociais</h2>
      <p class="muted small" style="margin:-6px 0 14px">Deixe em branco para esconder o ícone no site.</p>
<?php foreach (SOCIALS as $k => [$label, $ic]): ?>
      <div class="fd"><label for="<?= $k ?>"><?= $label ?></label><input type="text" inputmode="url" id="<?= $k ?>" name="<?= $k ?>" value="<?= e($v[$k]) ?>" placeholder="https://"></div>
<?php endforeach; ?>
    </div>
  </div>
  <div class="row" style="margin-top:18px"><button class="btn btn--or" type="submit"><?= icon('check', 'bold') ?>Salvar configurações</button></div>
</form>
<?php admin_foot();
