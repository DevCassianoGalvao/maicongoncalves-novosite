<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
admin_guard();

$status = (string) ($_GET['status'] ?? 'abertos');
$source = (string) ($_GET['origem'] ?? '');
$term = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));
$per = 25;


$base = []; // filtros que valem para as abas (origem + busca); a situação entra só na listagem
$bargs = [];
if (in_array($source, ['mapa', 'painel', 'contato'], true)) { $base[] = 't.source = ?'; $bargs[] = $source; }
$rank = '0';
$rargs = [];
if ($term !== '') {
    [$conds, $cargs, $rank, $rargs] = ticket_search_sql($term);
    foreach ($conds as $c) $base[] = $c;
    $bargs = array_merge($bargs, $cargs);
}
$from = ' FROM tickets t LEFT JOIN ticket_categories c ON c.slug = t.category';
$bw = $base ? ' WHERE ' . implode(' AND ', $base) : '';

$counts = ['novo' => 0, 'analise' => 0, 'publicado' => 0, 'resolvido' => 0, 'arquivado' => 0];
foreach (q("SELECT t.status, COUNT(*) n$from$bw GROUP BY t.status", $bargs)->fetchAll() as $r) $counts[$r['status']] = (int) $r['n'];
$tabs = ['abertos' => ['Abertos', $counts['novo'] + $counts['analise']], 'novo' => ['Novos', $counts['novo']], 'analise' => ['Em análise', $counts['analise']],
    'publicado' => ['Na Prefeitura', $counts['publicado']], 'resolvido' => ['Resolvidos', $counts['resolvido']], 'arquivado' => ['Arquivados', $counts['arquivado']], 'todos' => ['Todos', array_sum($counts)]];
if (!isset($tabs[$status])) $status = 'abertos';
// buscou e a aba atual não tem resultado, mas outras têm: mostra em "Todos"
if ($term !== '' && $tabs[$status][1] === 0 && $tabs['todos'][1] > 0 && !isset($_GET['p'])) $status = 'todos';

$where = $base;
$args = $bargs;
if ($status === 'abertos') $where[] = "t.status IN ('novo','analise')";
elseif ($status !== 'todos') { $where[] = 't.status = ?'; $args[] = $status; }
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$total = (int) q("SELECT COUNT(*)$from$w", $args)->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page = min($page, $pages);
$order = $term !== '' ? "($rank) DESC, t.created_at DESC" : "(t.status = 'novo') DESC, t.created_at DESC";
$rows = q("SELECT t.*$from$w ORDER BY $order LIMIT $per OFFSET " . (($page - 1) * $per), array_merge($args, $term !== '' ? $rargs : []))->fetchAll();

function tqs(array $over): string
{
    $q = array_filter(array_merge(['status' => $GLOBALS['status'] ?? '', 'origem' => $_GET['origem'] ?? '', 'q' => $_GET['q'] ?? ''], $over), 'strlen');
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
    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Buscar por ofício, protocolo, nome, telefone, bairro, rua, tipo ou secretaria" aria-label="Buscar atendimentos"<?= $term === '' ? ' autofocus' : '' ?>>
    <select name="origem" onchange="this.form.submit()">
      <option value="">Todas as origens</option>
      <option value="mapa"<?= $source === 'mapa' ? ' selected' : '' ?>>Demandas do site</option>
      <option value="painel"<?= $source === 'painel' ? ' selected' : '' ?>>Cadastradas no painel</option>
      <option value="contato"<?= $source === 'contato' ? ' selected' : '' ?>>Formulário de contato (antigo)</option>
    </select>
    <button class="btn btn--ghost" type="submit"><?= icon('magnifying-glass') ?>Buscar</button>
  </form>
<?php if ($term !== ''): ?>
  <p class="small muted search-info"><?= $total ?> <?= $total === 1 ? 'resultado' : 'resultados' ?> para “<?= e($term) ?>”<?= $status !== 'todos' && $tabs['todos'][1] > $total ? ' em ' . e($tabs[$status][0]) . ' (' . $tabs['todos'][1] . ' no total)' : '' ?> · <a href="<?= e(tqs(['q' => '', 'p' => ''])) ?>">limpar busca</a></p>
<?php endif; ?>
<?php if (!$rows): ?>
  <p class="empty"><?= $term !== '' ? 'Nada encontrado para “' . e($term) . '”. Tente só o número do ofício (ex.: 209) ou uma palavra do nome ou do bairro.' : 'Nenhum atendimento aqui.' ?></p>
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
        <td><a class="t" href="<?= e($link) ?>"><?= e($what) ?></a> <span class="small muted"><?= ticket_code((int) $t['id']) ?></span><?php if (!empty($t['protocol'])): ?> <span class="badge badge--of"><?= e(oficio_label((string) $t['protocol'])) ?></span><?php endif; ?>
          <div class="small muted"><?= e($t['neighborhood'] ?: ($t['address'] ?: '—')) ?><?= !empty($t['secretaria']) ? ' · ' . e(secretaria_short(secretaria_name($t['secretaria']))) : '' ?> · <span class="badge badge--src"><?= ['mapa' => 'Site', 'painel' => 'Painel', 'contato' => 'Contato'][$t['source']] ?? 'Site' ?></span></div></td>
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
