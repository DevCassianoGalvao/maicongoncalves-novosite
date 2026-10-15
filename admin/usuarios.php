<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
$me = admin_guard(true);

$errors = [];
$form = ['name' => '', 'email' => '', 'role' => 'assessoria'];
$admins = function (): int { return (int) q("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(); };

if (is_post()) {
    csrf_check();
    $do = in('do');
    $id = (int) in('id');
    $target = $id ? q('SELECT id, name, role FROM users WHERE id = ?', [$id])->fetch() : null;
    if ($do === 'create') {
        $form = ['name' => in('name'), 'email' => mb_strtolower(in('email')), 'role' => in('role') === 'admin' ? 'admin' : 'assessoria'];
        $pass = (string) ($_POST['password'] ?? '');
        if ($form['name'] === '') $errors[] = 'Informe o nome.';
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido.';
        elseif (q('SELECT COUNT(*) FROM users WHERE email = ?', [$form['email']])->fetchColumn()) $errors[] = 'Já existe um acesso com esse e-mail.';
        if (mb_strlen($pass) < 8) $errors[] = 'A senha precisa ter pelo menos 8 caracteres.';
        if (!$errors) {
            q('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())', [$form['name'], $form['email'], password_hash($pass, PASSWORD_DEFAULT), $form['role']]);
            flash('ok', 'Acesso criado para ' . $form['name'] . '. Envie o e-mail e a senha para a pessoa entrar em ' . abs_url('/admin/') . '.');
            redirect(url('/admin/usuarios.php'));
        }
    } elseif ($target) {
        if ($do === 'role') {
            $role = in('role') === 'admin' ? 'admin' : 'assessoria';
            if ($target['role'] === 'admin' && $role !== 'admin' && $admins() <= 1) flash('err', 'Precisa existir pelo menos um administrador.');
            else { q('UPDATE users SET role = ? WHERE id = ?', [$role, $id]); flash('ok', $target['name'] . ' agora é ' . USER_ROLES[$role] . '.'); }
        } elseif ($do === 'password') {
            $pass = (string) ($_POST['password'] ?? '');
            if (mb_strlen($pass) < 8) flash('err', 'A nova senha precisa ter pelo menos 8 caracteres.');
            else { q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pass, PASSWORD_DEFAULT), $id]); flash('ok', 'Senha de ' . $target['name'] . ' trocada.'); }
        } elseif ($do === 'delete') {
            if ($id === (int) $me['id']) flash('err', 'Você não pode excluir o seu próprio acesso.');
            elseif ($target['role'] === 'admin' && $admins() <= 1) flash('err', 'Precisa existir pelo menos um administrador.');
            else { q('UPDATE ticket_notes SET user_id = NULL WHERE user_id = ?', [$id]); q('DELETE FROM users WHERE id = ?', [$id]); flash('ok', 'Acesso de ' . $target['name'] . ' excluído.'); }
        }
        redirect(url('/admin/usuarios.php'));
    }
}

$users = q('SELECT id, name, email, role, created_at, last_login_at FROM users ORDER BY role, name')->fetchAll();
admin_head('Usuários', 'users');
?>
<div class="top"><div><h1>Usuários</h1><p>Quem acessa o painel e o que cada um pode fazer.</p></div></div>
<?php foreach ($errors as $err): ?>
<div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($err) ?></span></div>
<?php endforeach; ?>
<div class="grid g2" style="align-items:start">
  <div class="card">
    <h2>Acessos</h2>
    <ul class="users">
<?php foreach ($users as $u): $self = (int) $u['id'] === (int) $me['id']; ?>
      <li>
        <div class="users-hd">
          <div><b><?= e($u['name']) ?></b><?= $self ? ' <span class="small muted">(você)</span>' : '' ?><div class="small muted"><?= e($u['email']) ?></div>
            <div class="small muted"><?= $u['last_login_at'] ? 'Último acesso em ' . date('d/m/Y H:i', strtotime($u['last_login_at'])) : 'Ainda não entrou' ?></div></div>
          <span class="badge <?= $u['role'] === 'admin' ? 'badge--pub' : 'badge--sched' ?>"><?= e(USER_ROLES[$u['role']] ?? $u['role']) ?></span>
        </div>
<?php if (!$self): ?>
        <details class="users-ed"><summary>Gerenciar</summary>
          <form method="post" class="row"><?= csrf_field() ?><input type="hidden" name="do" value="role"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
            <select name="role"><?php foreach (USER_ROLES as $k => $v): ?><option value="<?= $k ?>"<?= $u['role'] === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
            <button class="btn btn--ghost btn--sm" type="submit">Trocar nível</button></form>
          <form method="post" class="row"><?= csrf_field() ?><input type="hidden" name="do" value="password"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
            <input type="text" name="password" minlength="8" placeholder="Nova senha (mín. 8)" autocomplete="off" required>
            <button class="btn btn--ghost btn--sm" type="submit">Trocar senha</button></form>
          <form method="post" data-confirm="Excluir o acesso de <?= e($u['name']) ?>?"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
            <button class="link-btn small" type="submit" style="color:#C0262D">Excluir acesso</button></form>
        </details>
<?php endif; ?>
      </li>
<?php endforeach; ?>
    </ul>
  </div>
  <div>
    <form method="post" class="card">
      <?= csrf_field() ?><input type="hidden" name="do" value="create">
      <h2>Novo acesso</h2>
      <div class="fd"><label for="name">Nome</label><input type="text" id="name" name="name" value="<?= e($form['name']) ?>" required></div>
      <div class="fd"><label for="email">E-mail</label><input type="email" id="email" name="email" value="<?= e($form['email']) ?>" required autocomplete="off"></div>
      <div class="fd"><label for="password">Senha inicial</label><input type="text" id="password" name="password" minlength="8" required autocomplete="off"><p class="hint">A pessoa pode trocar depois em Minha conta.</p></div>
      <div class="fd"><label for="role">Nível</label><select id="role" name="role"><?php foreach (USER_ROLES as $k => $v): ?><option value="<?= $k ?>"<?= $form['role'] === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
      <button class="btn btn--or" type="submit"><?= icon('user-plus') ?>Criar acesso</button>
    </form>
    <div class="card">
      <h2>O que cada nível pode fazer</h2>
      <p class="small" style="margin:0 0 8px"><b>Assessoria:</b> atendimentos (aprovar, publicar no mapa, resolver, cadastrar demandas) e blog (artigos e categorias).</p>
      <p class="small" style="margin:0"><b>Administrador:</b> tudo da assessoria e também configurações do site, usuários e exclusão de atendimentos.</p>
    </div>
  </div>
</div>
<?php admin_foot();
