<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
if (!is_file(ROOT . '/storage/installed.lock')) redirect(url('/install/'));

$next = (string) ($_GET['next'] ?? '');
if ($next === '' || $next[0] !== '/' || strpos($next, '//') === 0 || strpbrk($next, "\\\r\n") !== false) $next = url('/admin/');
if (auth_user()) redirect($next);

$error = '';
if (is_post()) {
    csrf_check();
    if (login_blocked()) {
        $error = 'Muitas tentativas. Aguarde 15 minutos e tente de novo.';
    } elseif (attempt_login(in('email'), (string) ($_POST['password'] ?? ''))) {
        redirect($next);
    } else {
        $error = 'E-mail ou senha incorretos.';
    }
}

auth_page_start('Entrar · Painel');
?>
    <h1>Entrar no painel</h1>
    <p class="sub">Gerencie as notícias e as informações do site.</p>
<?php if ($error): ?>
    <div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($error) ?></span></div>
<?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="fd"><label for="email">E-mail</label><input type="email" id="email" name="email" value="<?= e(in('email')) ?>" autocomplete="username" required autofocus></div>
      <div class="fd"><label for="password">Senha</label><input type="password" id="password" name="password" autocomplete="current-password" required></div>
      <button class="btn btn--or btn--block" type="submit">Entrar</button>
    </form>
    <p class="small muted" style="margin:18px 0 0;text-align:center"><a href="<?= e(url('/')) ?>">← Voltar para o site</a></p>
<?php auth_page_end();
