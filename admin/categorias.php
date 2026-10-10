<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
admin_guard();

if (is_post()) {
    csrf_check();
    $action = in('action');
    $id = (int) in('id');
    $name = in('name');
    $slug = slugify(in('slug') !== '' ? in('slug') : $name);
    if (in_array($action, ['add', 'edit'], true)) {
        if ($name === '' || $slug === '') {
            flash('err', 'Informe o nome da categoria.');
        } elseif (q('SELECT COUNT(*) FROM categories WHERE slug = ? AND id <> ?', [$slug, $id])->fetchColumn()) {
            flash('err', 'Já existe uma categoria com esse endereço (' . $slug . ').');
        } elseif ($action === 'add') {
            q('INSERT INTO categories (name, slug, created_at) VALUES (?, ?, NOW())', [$name, $slug]);
            flash('ok', 'Categoria "' . $name . '" criada.');
        } else {
            q('UPDATE categories SET name = ?, slug = ? WHERE id = ?', [$name, $slug, $id]);
            flash('ok', 'Categoria atualizada.');
        }
    } elseif ($action === 'delete') {
        $c = q('SELECT name FROM categories WHERE id = ?', [$id])->fetchColumn();
        if ($c !== false) {
            q('UPDATE posts SET category_id = NULL WHERE category_id = ?', [$id]);
            q('DELETE FROM categories WHERE id = ?', [$id]);
            flash('ok', 'Categoria "' . $c . '" excluída. Os artigos dela ficaram sem categoria.');
        }
    }
    redirect(url('/admin/categorias.php'));
}

$rows = q('SELECT c.id, c.name, c.slug, COUNT(p.id) AS total FROM categories c LEFT JOIN posts p ON p.category_id = c.id GROUP BY c.id, c.name, c.slug ORDER BY c.name')->fetchAll();
$edit = (int) ($_GET['edit'] ?? 0);

admin_head('Categorias', 'cats');
?>
<div class="top"><div><h1>Categorias</h1><p>Organizam as notícias e viram os filtros da página de notícias.</p></div></div>
<div class="grid" style="grid-template-columns:minmax(0,1fr) minmax(0,340px);align-items:start" id="catgrid">
  <div class="card">
<?php if (!$rows): ?>
    <p class="empty">Nenhuma categoria ainda.</p>
<?php else: ?>
    <table class="tbl">
      <thead><tr><th>Nome</th><th class="hide-m">Endereço</th><th>Artigos</th><th></th></tr></thead>
      <tbody>
<?php foreach ($rows as $c): if ($edit === (int) $c['id']): ?>
        <tr><td colspan="4">
          <form method="post" class="row">
            <?= csrf_field() ?><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <input type="text" name="name" value="<?= e($c['name']) ?>" style="flex:1 1 160px" required aria-label="Nome">
            <input type="text" name="slug" value="<?= e($c['slug']) ?>" style="flex:1 1 160px" aria-label="Endereço">
            <button class="btn btn--sm" type="submit">Salvar</button><a class="small" href="<?= e(url('/admin/categorias.php')) ?>">Cancelar</a>
          </form></td></tr>
<?php else: ?>
        <tr>
          <td class="t"><?= e($c['name']) ?></td>
          <td class="hide-m small muted">/noticias/?cat=<?= e($c['slug']) ?></td>
          <td><a href="<?= e(url('/admin/posts.php?cat=' . $c['id'])) ?>"><?= (int) $c['total'] ?></a></td>
          <td class="acts">
            <a class="small" href="<?= e(url('/admin/categorias.php?edit=' . $c['id'])) ?>">Editar</a>
            <form method="post" style="display:inline" data-confirm="Excluir a categoria &quot;<?= e($c['name']) ?>&quot;? Os <?= (int) $c['total'] ?> artigos dela continuam no site, só ficam sem categoria.">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <button class="link-btn danger small" type="submit">Excluir</button>
            </form>
          </td>
        </tr>
<?php endif; endforeach; ?>
      </tbody>
    </table>
<?php endif; ?>
  </div>
  <div class="card">
    <h2>Nova categoria</h2>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="add">
      <div class="fd"><label for="name">Nome</label><input type="text" id="name" name="name" required placeholder="Ex.: Saúde"></div>
      <button class="btn btn--or btn--block" type="submit"><?= icon('plus', 'bold') ?>Criar categoria</button>
    </form>
  </div>
</div>
<style>@media (max-width:860px){#catgrid{grid-template-columns:minmax(0,1fr)!important}}</style>
<?php admin_foot();
