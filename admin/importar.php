<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require ROOT . '/app/wp_import.php';
$user = admin_guard();

$base = in('base', (string) ($_GET['base'] ?? abs_url('/backupdosite/')));
$error = '';
$posts = [];
$results = [];
if (is_post()) {
    csrf_check();
    @set_time_limit(600);
    try {
        $posts = wp_fetch_posts($base);
        if (in('do') === 'import') {
            $chosen = array_map('strval', (array) ($_POST['slugs'] ?? []));
            foreach ($posts as $p) {
                if (!in_array($p['slug'], $chosen, true)) continue;
                try { $results[] = [wp_text($p['title']['rendered']), wp_import_post($p, (int) $user['id']), true]; }
                catch (Throwable $ex) { $results[] = [wp_text($p['title']['rendered']), 'erro: ' . $ex->getMessage(), false]; }
            }
        }
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}
$existing = array_flip(q('SELECT slug FROM posts')->fetchAll(PDO::FETCH_COLUMN));

admin_head('Importar do WordPress', 'posts');
?>
<div class="top"><div><h1>Importar do WordPress</h1><p>Traz artigos de um site WordPress (texto, capa, fotos, data e categoria). Os que já existem aqui são ignorados.</p></div></div>
<?php if ($error): ?><div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($error) ?></span></div><?php endif; ?>
<?php if ($results): ?>
<div class="card">
  <h2>Resultado</h2>
  <ul class="checks" style="margin:0">
<?php foreach ($results as [$t, $msg, $ok]): ?>
    <li><span style="color:var(--<?= $ok ? 'ok' : 'err' ?>)"><?= icon($ok ? 'check-circle' : 'warning-circle') ?></span><span><b><?= e($t) ?></b> — <?= e($msg) ?></span></li>
<?php endforeach; ?>
  </ul>
  <div class="row" style="margin-top:16px"><a class="btn btn--or" href="<?= e(url('/admin/posts.php')) ?>">Ver artigos</a></div>
</div>
<?php endif; ?>
<form method="post" class="card">
  <?= csrf_field() ?>
  <div class="fd"><label for="base">Endereço do WordPress</label><input type="text" id="base" name="base" value="<?= e($base) ?>" placeholder="https://site.com.br/"></div>
  <button class="btn btn--ghost" type="submit" name="do" value="list"><?= icon('magnifying-glass') ?>Buscar artigos</button>
</form>
<?php if ($posts && !$results):
    $new = array_filter($posts, function ($p) use ($existing) { return !isset($existing[$p['slug']]); }); ?>
<form method="post" class="card">
  <?= csrf_field() ?><input type="hidden" name="base" value="<?= e($base) ?>">
  <h2><?= count($posts) ?> artigos encontrados · <?= count($new) ?> ainda não estão no site</h2>
  <table class="tbl">
    <tbody>
<?php foreach ($posts as $p): $has = isset($existing[$p['slug']]); ?>
      <tr>
        <td style="width:34px"><input type="checkbox" name="slugs[]" value="<?= e($p['slug']) ?>"<?= $has ? ' disabled' : ' checked' ?> aria-label="Importar"></td>
        <td><span class="t"><?= e(wp_text($p['title']['rendered'])) ?></span><div class="small muted"><?= date('d/m/Y', strtotime($p['date'])) ?></div></td>
        <td class="hide-m"><?= $has ? '<span class="badge">Já está no site</span>' : '<span class="badge badge--sched">Novo</span>' ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php if ($new): ?>
  <div class="row" style="margin-top:16px"><input type="hidden" name="do" value="import"><button class="btn btn--or" type="submit" onclick="var b=this;setTimeout(function(){b.disabled=true;b.textContent='Importando… pode levar alguns minutos'},0)"><?= icon('plus', 'bold') ?>Importar selecionados</button></div>
<?php endif; ?>
</form>
<?php endif;
admin_foot();
