<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
admin_guard();

$status = (string) ($_GET['status'] ?? 'abertos');
$source = (string) ($_GET['origem'] ?? '');
$term = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));
$per = 25;

$counts = ['novo' => 0, 'analise' => 0, 'publicado' => 0, 'resolvido' => 0, 'arquivado' => 0];
foreach (q('SELECT status, COUNT(*) n FROM tickets GROUP BY status')->fetchAll() as $r) $counts[$r['status']] = (int) $r['n'];
$tabs = ['abertos' => ['Abertos', $counts['novo'] + $counts['analise']], 'novo' => ['Novos', $counts['novo']], 'analise' => ['Em análise', $counts['analise']],
    'publicado' => ['Na Prefeitura', $counts['publicado']], 'resolvido' => ['Resolvidos', $counts['resolvido']], 'arquivado' => ['Arquivados', $counts['arquivado']], 'todos' => ['Todos', array_sum($counts)]];
if (!isset($tabs[$status])) $status = 'abertos';

$where = [];
$args = [];
if ($status === 'abertos') $where[] = "status IN ('novo','analise')";
elseif ($status !== 'todos') { $where[] = 'status = ?'; $args[] = $status; }
if (in_array($source, ['mapa', 'painel', 'contato'], true)) { $where[] = 'source = ?'; $args[] = $source; }
if ($term !== '') {
    $id = (int) ltrim($term, '#0');
    $where[] = '(name LIKE ? OR phone LIKE ? OR neighborhood LIKE ? OR address LIKE ? OR description LIKE ?' . ($id ? ' OR id = ' . $id : '') . ')';
    array_push($args, "%$term%", "%$term%", "%$term%", "%$term%", "%$term%");
}
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$total = (int) q("SELECT COUNT(*) FROM tickets$w", $args)->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page = min($page, $pages);
$rows = q("SELECT * FROM tickets$w ORDER BY (status = 'novo') DESC, created_at DESC LIMIT $per OFFSET " . (($page - 1) * $per), $args)->fetchAll();

function tqs(array $over): string
{
    $q = array_filter(array_merge(['status' => $_GET['status'] ?? '', 'origem' => $_GET['origem'] ?? '', 'q' => $_GET['q'] ?? ''], $over), 'strlen');
    return url('/admin/atendimentos.php') . ($q ? '?' . http_build_query($q) : '');
}

admin_head('Atendimentos', 'tickets');
?>
<div class="top">
  <div><h1>Atendimentos</h1><p>Demandas do Friburgo no Mapa e as cadastradas pelo painel.</p></div>
  <div class="row"><a class="btn btn--ghost" href="<?= e(url('/admin/tipos.php')) ?>"><?= icon('tag') ?>Tipos de demanda</a>
  <a class="btn btn--ghost" href="<?= e(url('/friburgo-no-mapa/')) ?>" target="_blank" rel="noopener"><?= icon('map-trifold') ?>Ver o Friburgo no Mapa</a>
<?php if (is_admin()): ?>  <a class="btn btn--ghost" href="<?= e(url('/admin/exportar.php')) ?>"><?= icon('download-simple') ?>Exportar planilha</a>
<?php endif; ?>  <a class="btn btn--or" href="<?= e(url('/admin/demanda-nova.php')) ?>"><?= icon('plus', 'bold') ?>Cadastrar demanda</a></div>
</div>
<nav class="tabs" aria-label="Situação">
<?php foreach ($tabs as $k => [$label, $n]): ?>
  <a href="<?= e(tqs(['status' => $k, 'p' => ''])) ?>"<?= $k === $status ? ' class="on" aria-current="page"' : '' ?>><?= $label ?><span><?= $n ?></span></a>
<?php endforeach; ?>
</nav>
<div class="card">
  <form class="filters" method="get">
    <input type="hidden" name="status" value="<?= e($status) ?>">
    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Buscar por nome, telefone, bairro ou protocolo">
    <select name="origem" onchange="this.form.submit()">
      <option value="">Todas as origens</option>
      <option value="mapa"<?= $source === 'mapa' ? ' selected' : '' ?>>Demandas do site</option>
      <option value="painel"<?= $source === 'painel' ? ' selected' : '' ?>>Cadastradas no painel</option>
      <option value="contato"<?= $source === 'contato' ? ' selected' : '' ?>>Formulário de contato (antigo)</option>
    </select>
    <button class="btn btn--ghost" type="submit"><?= icon('magnifying-glass') ?>Buscar</button>
  </form>
<?php if (!$rows): ?>
  <p class="empty">Nenhum atendimento aqui.</p>
<?php else: ?>
  <table class="tbl">
    <thead><tr><th class="hide-m"></th><th>Atendimento</th><th class="hide-m">Pessoa</th><th class="hide-m">Situação</th><th>Recebido</th></tr></thead>
    <tbody>
<?php foreach ($rows as $t):
    $ph = json_decode((string) $t['photos'], true) ?: [];
    $what = $t['source'] !== 'contato' ? cat_label($t['category']) : ($t['subject'] ?: 'Contato');
    $link = url('/admin/atendimento.php?id=' . $t['id']); ?>
      <tr data-href="<?= e($link) ?>" tabindex="0"<?= $t['status'] === 'novo' ? ' class="is-new"' : '' ?>>
        <td class="hide-m" style="width:76px"><?php if ($ph): ?><img class="thumb" src="<?= e(ticket_photo_url((int) $t['id'], $ph[0]['src'])) ?>" alt="" loading="lazy"><?php else: ?><div class="thumb thumb--ic"><?= icon($t['source'] !== 'contato' ? 'map-pin' : 'envelope-simple') ?></div><?php endif; ?></td>
        <td><a class="t" href="<?= e($link) ?>"><?= e($what) ?></a> <span class="small muted"><?= ticket_code((int) $t['id']) ?></span>
          <div class="small muted"><?= e($t['neighborhood'] ?: ($t['address'] ?: '—')) ?> · <span class="badge badge--src"><?= ['mapa' => 'Site', 'painel' => 'Painel', 'contato' => 'Contato'][$t['source']] ?? 'Site' ?></span></div></td>
        <td class="hide-m"><?= e($t['name'] ?: 'Sem nome') ?><div class="small muted"><?= e($t['phone']) ?></div></td>
        <td class="hide-m"><?= ticket_badge($t) ?></td>
        <td class="small muted"><?= date('d/m H:i', strtotime($t['created_at'])) ?><br>há <?= human_span(time() - strtotime($t['created_at'])) ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php if ($pages > 1): ?>
  <nav class="pager" aria-label="Páginas">
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <?= $i === $page ? '<span>' . $i . '</span>' : '<a href="' . e(tqs(['p' => $i])) . '">' . $i . '</a>' ?>
<?php endfor; ?>
  </nav>
<?php endif; ?>
<?php endif; ?>
</div>
<?php admin_foot();
