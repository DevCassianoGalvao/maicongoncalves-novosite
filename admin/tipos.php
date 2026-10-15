<?php
/** Tipos de demanda: criar, renomear, ordenar, desativar e excluir (quando não há demandas usando). */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
$user = admin_guard();

$errors = [];
if (is_post()) {
    csrf_check();
    $do = in('do', '', 12);
    $id = (int) in('id', '', 10);
    $row = $id ? q('SELECT * FROM ticket_categories WHERE id = ?', [$id])->fetch() : null;
    if ($do === 'add') {
        $name = in('name', '', 80);
        $slug = substr(str_replace('-', '', slugify($name)), 0, 20);
        if ($name === '' || $slug === '') $errors[] = 'Escreva o nome do tipo.';
        elseif (q('SELECT COUNT(*) FROM ticket_categories WHERE name = ?', [$name])->fetchColumn()) $errors[] = 'Já existe um tipo com esse nome.';
        if (!$errors) {
            $base = $slug; $n = 1;
            while (q('SELECT COUNT(*) FROM ticket_categories WHERE slug = ?', [$slug])->fetchColumn()) $slug = substr($base, 0, 17) . (++$n);
            $sort = (int) q("SELECT COALESCE(MAX(sort), 0) FROM ticket_categories WHERE slug <> 'outro'")->fetchColumn() + 10;
            q('INSERT INTO ticket_categories (slug, name, sort, active) VALUES (?, ?, ?, 1)', [$slug, $name, $sort]);
            flash('ok', 'Tipo "' . $name . '" criado. Já aparece no formulário do site.');
            redirect(url('/admin/tipos.php'));
        }
    } elseif ($row) {
        if ($do === 'rename') {
            $name = in('name', '', 80);
            if ($name === '') flash('err', 'O nome não pode ficar vazio.');
            else { q('UPDATE ticket_categories SET name = ? WHERE id = ?', [$name, $id]); flash('ok', 'Nome atualizado.'); }
        } elseif ($do === 'toggle') {
            if ($row['slug'] === 'outro') flash('err', '"Outra demanda" fica sempre ativo, para quem não acha o tipo certo.');
            else { q('UPDATE ticket_categories SET active = 1 - active WHERE id = ?', [$id]); flash('ok', $row['active'] ? 'Tipo desativado: some do formulário, mas as demandas antigas continuam com ele.' : 'Tipo ativado.'); }
        } elseif ($do === 'up' || $do === 'down') {
            $list = q("SELECT id FROM ticket_categories WHERE slug <> 'outro' ORDER BY sort, name")->fetchAll(PDO::FETCH_COLUMN);
            $i = array_search($id, array_map('intval', $list), true);
            $j = $do === 'up' ? $i - 1 : $i + 1;
            if ($i !== false && isset($list[$j])) { [$list[$i], $list[$j]] = [$list[$j], $list[$i]]; }
            foreach ($list as $k => $cid) q('UPDATE ticket_categories SET sort = ? WHERE id = ?', [($k + 1) * 10, $cid]);
        } elseif ($do === 'delete') {
            $used = (int) q('SELECT COUNT(*) FROM tickets WHERE category = ?', [$row['slug']])->fetchColumn();
            if ($row['slug'] === 'outro') flash('err', '"Outra demanda" não pode ser excluído.');
            elseif ($used) flash('err', 'Esse tipo tem ' . $used . ' demanda(s). Em vez de excluir, desative.');
            else { q('DELETE FROM ticket_categories WHERE id = ?', [$id]); flash('ok', 'Tipo "' . $row['name'] . '" excluído.'); }
        }
        redirect(url('/admin/tipos.php'));
    }
}

db_migrate();
$rows = q('SELECT c.*, (SELECT COUNT(*) FROM tickets t WHERE t.category = c.slug) AS used FROM ticket_categories c ORDER BY (c.slug = \'outro\'), c.sort, c.name')->fetchAll();
admin_head('Tipos de demanda', 'tickets');
?>
<div class="top">
  <div><h1>Tipos de demanda</h1><p>A lista que aparece no formulário do site, no filtro do mapa e no painel.</p></div>
  <a class="btn btn--ghost" href="<?= e(url('/admin/atendimentos.php')) ?>"><?= icon('arrow-left') ?>Atendimentos</a>
</div>
<?php foreach ($errors as $err): ?>
<div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($err) ?></span></div>
<?php endforeach; ?>
<form method="post" class="card row" style="margin-bottom:18px">
  <?= csrf_field() ?><input type="hidden" name="do" value="add">
  <input type="text" name="name" maxlength="80" placeholder="Novo tipo, ex.: Habitação" required style="flex:1 1 260px;width:auto">
  <button class="btn btn--or" type="submit"><?= icon('plus', 'bold') ?>Adicionar tipo</button>
</form>
<div class="card">
  <ul class="types">
<?php $last = count($rows) - 2; foreach ($rows as $i => $c): $fixed = $c['slug'] === 'outro'; ?>
    <li class="<?= $c['active'] ? '' : 'is-off' ?>">
      <div class="types-ord">
<?php if (!$fixed): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="up"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><button class="icon-btn" type="submit" aria-label="Subir"<?= $i === 0 ? ' disabled' : '' ?>><?= icon('caret-up', 'bold') ?></button></form>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="down"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><button class="icon-btn" type="submit" aria-label="Descer"<?= $i >= $last ? ' disabled' : '' ?>><?= icon('caret-down', 'bold') ?></button></form>
<?php endif; ?>
      </div>
      <form method="post" class="types-name"><?= csrf_field() ?><input type="hidden" name="do" value="rename"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
        <input type="text" name="name" value="<?= e($c['name']) ?>" maxlength="80" aria-label="Nome do tipo" required>
        <button class="btn btn--ghost btn--sm" type="submit">Salvar</button></form>
      <span class="small muted types-n"><?= (int) $c['used'] ?> <?= (int) $c['used'] === 1 ? 'demanda' : 'demandas' ?></span>
      <div class="types-acts">
<?php if (!$fixed): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="toggle"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><button class="link-btn small" type="submit"><?= $c['active'] ? 'Desativar' : 'Ativar' ?></button></form>
<?php if (!(int) $c['used']): ?>
        <form method="post" data-confirm="Excluir o tipo &quot;<?= e($c['name']) ?>&quot;?"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><button class="link-btn small danger" type="submit">Excluir</button></form>
<?php endif; ?>
<?php else: ?>
        <span class="small muted">sempre por último</span>
<?php endif; ?>
      </div>
    </li>
<?php endforeach; ?>
  </ul>
  <p class="hint small muted" style="margin:14px 0 0">Desativado: some do formulário, mas as demandas que já usam o tipo continuam com ele. Só dá para excluir tipos sem nenhuma demanda.</p>
</div>
<?php admin_foot();
