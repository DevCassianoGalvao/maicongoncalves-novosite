<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
admin_guard(true);

if (is_post() && in('action') === 'delete') {
    csrf_check();
    $p = q('SELECT id, title, cover, gallery FROM posts WHERE id = ?', [(int) in('id')])->fetch();
    if ($p) {
        q('DELETE FROM posts WHERE id = ?', [$p['id']]);
        delete_upload($p['cover']);
        foreach (json_decode((string) $p['gallery'], true) ?: [] as $g) delete_upload($g['src'] ?? '');
        flash('ok', 'Artigo "' . $p['title'] . '" excluído.');
    }
    redirect(url('/admin/posts.php'));
}

$term = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$cat = (int) ($_GET['cat'] ?? 0);
$page = max(1, (int) ($_GET['p'] ?? 1));
$per = 20;

$where = [];
$args = [];
if ($term !== '') { $where[] = '(p.title LIKE ? OR p.excerpt LIKE ?)'; $args[] = "%$term%"; $args[] = "%$term%"; }
if ($status === 'published') $where[] = "p.status = 'published'";
if ($status === 'draft') $where[] = "p.status <> 'published'";
if ($cat) { $where[] = 'p.category_id = ?'; $args[] = $cat; }
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$total = (int) q("SELECT COUNT(*) FROM posts p$w", $args)->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page = min($page, $pages);
$rows = q("SELECT p.id, p.title, p.slug, p.status, p.published_at, p.cover, c.name AS category FROM posts p LEFT JOIN categories c ON c.id = p.category_id$w
           ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $args)->fetchAll();
$categories = q('SELECT id, name FROM categories ORDER BY name')->fetchAll();

function qs(array $over): string
{
    $q = array_filter(array_merge(['q' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? '', 'cat' => $_GET['cat'] ?? ''], $over), 'strlen');
    return url('/admin/posts.php') . ($q ? '?' . http_build_query($q) : '');
}

admin_head('Artigos', 'posts');
?>
<div class="top">
  <div><h1>Artigos</h1><p><?= $total ?> <?= $total === 1 ? 'artigo' : 'artigos' ?><?= $where ? ' encontrados' : '' ?></p></div>
  <a class="btn btn--or" href="<?= e(url('/admin/post.php')) ?>"><?= icon('plus', 'bold') ?>Novo artigo</a>
</div>
<div class="card">
  <form class="filters" method="get">
    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Buscar pelo título">
    <select name="status" onchange="this.form.submit()">
      <option value="">Todos os status</option>
      <option value="published"<?= $status === 'published' ? ' selected' : '' ?>>Publicados</option>
      <option value="draft"<?= $status === 'draft' ? ' selected' : '' ?>>Rascunhos</option>
    </select>
    <select name="cat" onchange="this.form.submit()">
      <option value="">Todas as categorias</option>
<?php foreach ($categories as $c): ?>
      <option value="<?= (int) $c['id'] ?>"<?= $cat === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
<?php endforeach; ?>
    </select>
    <button class="btn btn--ghost" type="submit"><?= icon('magnifying-glass') ?>Filtrar</button>
  </form>
<?php if (!$rows): ?>
  <p class="empty">Nenhum artigo encontrado.</p>
<?php else: ?>
  <table class="tbl">
    <thead><tr><th class="hide-m"></th><th>Título</th><th class="hide-m">Status</th><th class="hide-m">Data</th><th></th></tr></thead>
    <tbody>
<?php foreach ($rows as $p): ?>
      <tr>
        <td class="hide-m" style="width:76px"><?php if ($p['cover']): ?><img class="thumb" src="<?= e(url('/' . $p['cover'])) ?>" alt="" loading="lazy"><?php else: ?><div class="thumb"></div><?php endif; ?></td>
        <td><a class="t" href="<?= e(url('/admin/post.php?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a><div class="small muted"><?= e($p['category'] ?: 'Sem categoria') ?></div></td>
        <td class="hide-m"><?= status_badge($p) ?></td>
        <td class="hide-m small muted"><?= $p['published_at'] ? date('d/m/Y', strtotime($p['published_at'])) : '—' ?></td>
        <td class="acts">
          <a href="<?= e(url('/' . $p['slug'] . '/') . ($p['status'] === 'published' ? '' : '?preview=1')) ?>" target="_blank" rel="noopener" class="small">Ver</a>
          <a href="<?= e(url('/admin/post.php?id=' . $p['id'])) ?>" class="small">Editar</a>
          <form method="post" style="display:inline" data-confirm="Excluir o artigo &quot;<?= e($p['title']) ?>&quot;? Essa ação não pode ser desfeita.">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="link-btn danger small" type="submit">Excluir</button>
          </form>
        </td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php if ($pages > 1): ?>
  <nav class="pager" aria-label="Páginas">
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <?= $i === $page ? '<span>' . $i . '</span>' : '<a href="' . e(qs(['p' => $i])) . '">' . $i . '</a>' ?>
<?php endfor; ?>
  </nav>
<?php endif; ?>
<?php endif; ?>
</div>
<?php admin_foot();
