<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
$user = admin_guard();

$pub = (int) q("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$draft = (int) q("SELECT COUNT(*) FROM posts WHERE status <> 'published'")->fetchColumn();
$cats = (int) q('SELECT COUNT(*) FROM categories')->fetchColumn();
$last = q('SELECT p.id, p.title, p.slug, p.status, p.published_at, p.updated_at, c.name AS category FROM posts p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.updated_at DESC LIMIT 6')->fetchAll();
$mailReady = env('BREVO_API_KEY', '') !== '' && env('MAIL_FROM_EMAIL', '') !== '';
$first = explode(' ', trim($user['name']))[0];

admin_head('Painel', 'dash');
?>
<div class="top">
  <div><h1>Olá, <?= e($first) ?></h1><p>O que vamos publicar hoje?</p></div>
  <a class="btn btn--or" href="<?= e(url('/admin/post.php')) ?>"><?= icon('plus', 'bold') ?>Novo artigo</a>
</div>
<?php if (!$mailReady): ?>
<div class="alert alert--warn"><?= icon('warning-circle') ?><span>O formulário de contato ainda não envia e-mails: preencha <code>BREVO_API_KEY</code> e <code>MAIL_FROM_EMAIL</code> no arquivo .env.</span></div>
<?php endif; ?>
<div class="grid g3">
  <a class="card stat" href="<?= e(url('/admin/posts.php?status=published')) ?>" style="text-decoration:none;color:inherit"><?= icon('newspaper') ?><div><b><?= $pub ?></b><span>publicados</span></div></a>
  <a class="card stat" href="<?= e(url('/admin/posts.php?status=draft')) ?>" style="text-decoration:none;color:inherit"><?= icon('note-pencil') ?><div><b><?= $draft ?></b><span>rascunhos</span></div></a>
  <a class="card stat" href="<?= e(url('/admin/categorias.php')) ?>" style="text-decoration:none;color:inherit"><?= icon('tag') ?><div><b><?= $cats ?></b><span>categorias</span></div></a>
</div>
<div class="card" style="margin-top:18px">
  <div class="row" style="justify-content:space-between;margin-bottom:6px"><h2 style="margin:0">Editados recentemente</h2><a href="<?= e(url('/admin/posts.php')) ?>" class="small">Ver todos</a></div>
<?php if (!$last): ?>
  <p class="empty">Nenhum artigo ainda.</p>
<?php else: ?>
  <table class="tbl">
    <tbody>
<?php foreach ($last as $p): ?>
      <tr>
        <td><a class="t" href="<?= e(url('/admin/post.php?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a><div class="small muted"><?= e($p['category'] ?: 'Sem categoria') ?></div></td>
        <td class="hide-m"><?= status_badge($p) ?></td>
        <td class="hide-m small muted">Editado em <?= date('d/m/Y H:i', strtotime($p['updated_at'])) ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>
<?php admin_foot();
