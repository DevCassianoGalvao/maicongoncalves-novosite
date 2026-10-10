<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
$user = admin_guard();

$errors = [];
$name = $user['name'];
$email = $user['email'];
if (is_post()) {
    csrf_check();
    $name = in('name');
    $email = mb_strtolower(in('email'));
    $cur = (string) ($_POST['current'] ?? '');
    $new = (string) ($_POST['password'] ?? '');
    $hash = q('SELECT password_hash FROM users WHERE id = ?', [$user['id']])->fetchColumn();
    if ($name === '') $errors[] = 'Informe seu nome.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido.';
    elseif (q('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?', [$email, $user['id']])->fetchColumn()) $errors[] = 'Esse e-mail já é usado por outro acesso.';
    if (!password_verify($cur, $hash)) $errors[] = 'Senha atual incorreta.';
    if ($new !== '' && mb_strlen($new) < 8) $errors[] = 'A nova senha precisa ter pelo menos 8 caracteres.';
    if ($new !== '' && $new !== (string) ($_POST['password2'] ?? '')) $errors[] = 'As novas senhas não conferem.';
    if (!$errors) {
        q('UPDATE users SET name = ?, email = ? WHERE id = ?', [$name, $email, $user['id']]);
        if ($new !== '') q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        flash('ok', $new !== '' ? 'Dados e senha atualizados.' : 'Dados atualizados.');
        redirect(url('/admin/conta.php'));
    }
}

admin_head('Minha conta', 'account');
?>
<div class="top"><div><h1>Minha conta</h1><p>Seu nome, e-mail de acesso e senha.</p></div></div>
<?php foreach ($errors as $err): ?>
<div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($err) ?></span></div>
<?php endforeach; ?>
<form method="post" class="card" style="max-width:620px">
  <?= csrf_field() ?>
  <div class="fd"><label for="name">Nome</label><input type="text" id="name" name="name" value="<?= e($name) ?>" required></div>
  <div class="fd"><label for="email">E-mail de acesso</label><input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="username"></div>
  <div class="grid g2">
    <div class="fd"><label for="password">Nova senha</label><input type="password" id="password" name="password" minlength="8" autocomplete="new-password"><p class="hint">Deixe em branco para manter.</p></div>
    <div class="fd"><label for="password2">Repita a nova senha</label><input type="password" id="password2" name="password2" minlength="8" autocomplete="new-password"></div>
  </div>
  <div class="fd"><label for="current">Senha atual</label><input type="password" id="current" name="current" required autocomplete="current-password"><p class="hint">Para confirmar que é você.</p></div>
  <button class="btn btn--or" type="submit"><?= icon('check', 'bold') ?>Salvar</button>
</form>
<?php admin_foot();
