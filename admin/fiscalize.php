<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require ROOT . '/app/fiscalize.php';
admin_guard(true);
fv_tables();

$aba = ($_GET['aba'] ?? '') === 'config' ? 'config' : 'lista';

if (is_post()) {
    csrf_check();
    $do = in('do');
    if ($do === 'coletar') {
        $lock = fopen(storage() . '/fiscalize.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { flash('err', 'Já tem uma coleta rodando. Espere alguns minutos.'); redirect(url('/admin/fiscalize.php')); }
        try {
            $r = fv_sync(25, !empty($_POST['lista']));
            flash($r['erros'] ? 'err' : 'ok', "Coleta: {$r['novas']} compras novas, {$r['atualizadas']} atualizadas, {$r['itens']} resultados de itens. " .
                ($r['lista'] ? '' : 'A lista do PNCP ainda está sendo lida (continua de onde parou). ') . ($r['pendentes'] ? "Faltam buscar os itens de {$r['pendentes']} compras: a coleta automática termina, ou clique de novo." : 'Fila vazia.') .
                ($r['erros'] ? ' Erros: ' . count($r['erros']) . ' (veja o registro em Configurações).' : ''));
        } catch (Throwable $ex) { flash('err', 'A coleta falhou: ' . $ex->getMessage()); }
        redirect(url('/admin/fiscalize.php'));
    }
    if ($do === 'lote') {
        $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
        $to = in('para');
        if ($ids && isset(FV_STATUS[$to])) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            q("UPDATE fv_compras SET status = ?" . ($to === 'ocultado' ? ', corrigido_em = NOW()' : '') . " WHERE id IN ($ph)", array_merge([$to], $ids));
            flash('ok', count($ids) . ' compra(s) agora: ' . mb_strtolower(FV_STATUS[$to], 'UTF-8') . '.');
        }
        redirect(url('/admin/fiscalize.php') . '?' . http_build_query(array_filter(['st' => $_POST['st'] ?? '', 'q' => $_POST['q'] ?? '', 'sec' => $_POST['sec'] ?? ''])));
    }
    if ($do === 'config') {
        $old = [fv_cfg('limite_acima'), fv_cfg('limite_muito_acima')];
        $a = max(1, min(500, (int) in('limite_acima', '20')));
        $m = max($a + 1, min(1000, (int) in('limite_muito_acima', '50')));
        fv_cfg_set('pagina_publica', !empty($_POST['pagina_publica']) ? '1' : '0');
        fv_cfg_set('limite_acima', (string) $a);
        fv_cfg_set('limite_muito_acima', (string) $m);
        $d = in('coleta_desde');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) fv_cfg_set('coleta_desde', $d);
        $cn = implode(',', array_filter(array_map(function ($x) { $x = preg_replace('/\D/', '', $x); return strlen($x) === 14 ? $x : ''; }, explode(',', in('orgaos_cnpj')))));
        if ($cn !== '') fv_cfg_set('orgaos_cnpj', $cn);
        $ib = preg_replace('/\D/', '', in('ibge_municipio'));
        if (strlen($ib) === 7) fv_cfg_set('ibge_municipio', $ib);
        if ($old !== [(string) $a, (string) $m]) fv_recalc_all();
        flash('ok', 'Configurações salvas.' . (!empty($_POST['pagina_publica']) ? ' A página Fiscalize Você está ABERTA ao público.' : ' A página continua oculta.'));
        redirect(url('/admin/fiscalize.php?aba=config'));
    }
    if ($do === 'ref_add') {
        $desc = in('descricao', '', 500);
        $val = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^\d,.]/', '', in('valor')));
        $fonte = in('fonte', '', 255);
        $u = in('url', '', 500);
        if ($desc === '' || $val <= 0 || $fonte === '') flash('err', 'Preencha descrição, valor e fonte da referência.');
        elseif ($u !== '' && !filter_var($u, FILTER_VALIDATE_URL)) flash('err', 'Link da referência inválido.');
        else {
            q('INSERT INTO fv_referencias (descricao_normalizada, unidade_medida, valor_unitario, fonte, url, data_referencia) VALUES (?, ?, ?, ?, ?, ?)',
                [fv_normalize($desc), in('unidade', '', 80) ?: null, $val, $fonte, $u ?: null, preg_match('/^\d{4}-\d{2}-\d{2}$/', in('data')) ? in('data') : date('Y-m-d')]);
            fv_recalc_all();
            flash('ok', 'Referência salva e comparações recalculadas.');
        }
        redirect(url('/admin/fiscalize.php?aba=config#referencias'));
    }
    if ($do === 'ref_del') {
        q('DELETE FROM fv_referencias WHERE id = ?', [(int) in('id')]);
        fv_recalc_all();
        flash('ok', 'Referência removida.');
        redirect(url('/admin/fiscalize.php?aba=config#referencias'));
    }
}

$st = (string) ($_GET['st'] ?? 'novo');
$term = trim((string) ($_GET['q'] ?? ''));
$sec = (string) ($_GET['sec'] ?? '');
$comValor = ($_GET['res'] ?? '') === '1';
$page = max(1, (int) ($_GET['p'] ?? 1));
$per = 30;

$base = []; $args = [];
if ($comValor) $base[] = 'c.valor_total IS NOT NULL';
if ($sec !== '') { if ($sec === '_') $base[] = '(c.secretaria IS NULL OR c.secretaria = \'\')'; else { $base[] = 'c.secretaria = ?'; $args[] = $sec; } }
if ($term !== '') {
    foreach (array_slice(array_filter(explode(' ', $term)), 0, 6) as $wd) {
        $l = '%' . addcslashes($wd, '%_\\') . '%';
        $base[] = '(c.objeto LIKE ? OR c.numero_processo LIKE ? OR c.pncp_id LIKE ? OR c.fornecedor_nome LIKE ? OR EXISTS (SELECT 1 FROM fv_itens i WHERE i.compra_id = c.id AND (i.descricao LIKE ? OR i.fornecedor_nome LIKE ?)))';
        array_push($args, $l, $l, $l, $l, $l, $l);
    }
}
$bw = $base ? ' WHERE ' . implode(' AND ', $base) : '';
$counts = array_fill_keys(array_keys(FV_STATUS), 0);
foreach (q("SELECT c.status, COUNT(*) n FROM fv_compras c$bw GROUP BY c.status", $args)->fetchAll() as $r) $counts[$r['status']] = (int) $r['n'];
if (!isset(FV_STATUS[$st]) && $st !== 'todos') $st = 'novo';
$where = $base; $wargs = $args;
if ($st !== 'todos') { $where[] = 'c.status = ?'; $wargs[] = $st; }
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$total = (int) q("SELECT COUNT(*) FROM fv_compras c$w", $wargs)->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page = min($page, $pages);
$rows = q("SELECT c.*, (SELECT COUNT(*) FROM fv_itens i WHERE i.compra_id = c.id AND i.oculto = 0) n_res,
           (SELECT COUNT(*) FROM fv_itens i WHERE i.compra_id = c.id AND i.oculto = 0 AND i.situacao IN ('acima','muito_acima')) n_acima,
           (SELECT COUNT(*) FROM fv_itens i WHERE i.compra_id = c.id AND i.oculto = 0 AND i.aviso IS NOT NULL) n_aviso
           FROM fv_compras c$w ORDER BY COALESCE(c.data_homologacao, c.data_publicacao) DESC, c.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $wargs)->fetchAll();
$fila = (int) q("SELECT COUNT(*) FROM fv_compras WHERE itens_sync = 'pendente'")->fetchColumn();
$lastLog = q('SELECT * FROM fv_log ORDER BY id DESC LIMIT 1')->fetch();
$secsUsed = q('SELECT secretaria, COUNT(*) n FROM fv_compras GROUP BY secretaria ORDER BY n DESC')->fetchAll();

function fvq(array $over): string
{
    $q = array_filter(array_merge(['st' => $_GET['st'] ?? '', 'q' => $_GET['q'] ?? '', 'sec' => $_GET['sec'] ?? '', 'res' => $_GET['res'] ?? ''], $over), 'strlen');
    return url('/admin/fiscalize.php') . ($q ? '?' . http_build_query($q) : '');
}

admin_head('Fiscalize Você', 'fv');
?>
<div class="top">
  <div><h1>Fiscalize Você</h1><p>Compras da Prefeitura vindas do PNCP. Só as <b>publicadas</b> aparecem na página.
    <?= fv_cfg('pagina_publica') === '1' ? '<span class="badge badge--pub">Página aberta ao público</span>' : '<span class="badge badge--draft">Página oculta</span>' ?></p></div>
  <div class="row">
    <a class="btn btn--ghost" href="<?= e(url('/fiscalize-voce/')) ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>Ver a página</a>
    <a class="btn btn--ghost" href="<?= e(url('/admin/fiscalize.php?aba=' . ($aba === 'config' ? 'lista' : 'config'))) ?>"><?= icon($aba === 'config' ? 'currency-circle-dollar' : 'gear-six') ?><?= $aba === 'config' ? 'Compras' : 'Configurações e referências' ?></a>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="coletar"><button class="btn btn--or" type="submit"><?= icon('arrows-clockwise') ?>Coletar agora</button></form>
  </div>
</div>
<?php if ($aba === 'config'):
    $refs = q('SELECT * FROM fv_referencias ORDER BY id DESC LIMIT 200')->fetchAll();
    $logs = q('SELECT * FROM fv_log ORDER BY id DESC LIMIT 15')->fetchAll(); ?>
<div class="grid g2" style="align-items:start">
  <form method="post" class="card">
    <?= csrf_field() ?><input type="hidden" name="do" value="config">
    <h2>Publicação e alertas</h2>
    <label class="check" style="margin-bottom:6px"><input type="checkbox" name="pagina_publica" value="1"<?= fv_cfg('pagina_publica') === '1' ? ' checked' : '' ?>> Página aberta ao público</label>
    <p class="hint small muted" style="margin:0 0 16px">Desmarcada, o endereço /fiscalize-voce/ dá "página não encontrada" para quem não está logado no painel. Para pôr no menu do site, peça a alteração no código.</p>
    <div class="grid g2" style="gap:12px">
      <div class="fd"><label for="la">"Acima da referência" a partir de (%)</label><input type="number" id="la" name="limite_acima" min="1" max="500" value="<?= e(fv_cfg('limite_acima')) ?>"></div>
      <div class="fd"><label for="lm">"Muito acima" a partir de (%)</label><input type="number" id="lm" name="limite_muito_acima" min="2" max="1000" value="<?= e(fv_cfg('limite_muito_acima')) ?>"></div>
    </div>
    <h2 style="margin-top:6px">Coleta</h2>
    <div class="fd"><label for="cd">Buscar compras desde</label><input type="date" id="cd" name="coleta_desde" value="<?= e(fv_cfg('coleta_desde')) ?>"><p class="hint">Vale para a primeira carga. Depois a coleta pega só o que mudou desde a última vez.</p></div>
    <div class="fd"><label for="cn">CNPJ dos órgãos (separe por vírgula)</label><input type="text" id="cn" name="orgaos_cnpj" value="<?= e(implode(', ', array_map('fv_cnpj', explode(',', fv_cfg('orgaos_cnpj'))))) ?>"><p class="hint">28.606.630/0001-23 = Município de Nova Friburgo. O filtro por cidade do PNCP também traz Câmara e órgãos federais, por isso o filtro por CNPJ.</p></div>
    <div class="fd"><label for="ib">Código IBGE do município</label><input type="text" id="ib" name="ibge_municipio" value="<?= e(fv_cfg('ibge_municipio')) ?>"><p class="hint">Nova Friburgo = 3303401.</p></div>
    <button class="btn" type="submit"><?= icon('check', 'bold') ?>Salvar</button>
  </form>
  <div class="card">
    <h2>Coleta automática</h2>
    <p class="small">No cPanel, em <b>Cron Jobs</b>, adicione (todo dia às 3h10):</p>
    <pre class="code">10 3 * * * /usr/local/bin/php $HOME/maicongoncalves.com.br/tools/fiscalize-coletar.php &gt;/dev/null 2&gt;&amp;1</pre>
    <p class="small muted">Na primeira carga são centenas de compras. Para esvaziar a fila mais rápido, deixe o cron de hora em hora por um ou dois dias (<code>10 * * * *</code>) e depois volte para uma vez por dia.</p>
    <p class="small">Última lista: <b><?= fv_cfg('ultima_lista') ? date('d/m/Y H:i', strtotime(fv_cfg('ultima_lista'))) : 'nunca' ?></b> · Compras na fila de itens: <b><?= $fila ?></b></p>
    <h2 style="margin-top:18px">Registro</h2>
    <ul class="fv-log">
<?php foreach ($logs as $l): ?>
      <li><b><?= date('d/m H:i', strtotime($l['executado_em'])) ?></b> · <?= (int) $l['registros_novos'] ?> novas · <?= e($l['detalhes'] ?? '') ?>
        <?php if ($l['erros']): ?><details><summary class="small" style="color:var(--err)">Erros</summary><pre class="code"><?= e($l['erros']) ?></pre></details><?php endif; ?></li>
<?php endforeach; ?>
<?php if (!$logs): ?>      <li class="muted">Nenhuma coleta ainda.</li><?php endif; ?>
    </ul>
  </div>
</div>
<div class="card" id="referencias" style="margin-top:18px">
  <h2>Referências de preço</h2>
  <p class="small muted" style="margin:-6px 0 14px">Usadas quando a descrição do item bate com a da referência (sem acento, sem maiúsculas, primeiras palavras). Sem referência cadastrada, o sistema usa a mediana do mesmo item em outras compras da Prefeitura. O alerta só aparece com fonte citada.</p>
  <form method="post" class="grid g4" style="gap:10px;align-items:end">
    <?= csrf_field() ?><input type="hidden" name="do" value="ref_add">
    <div class="fd" style="grid-column:span 2"><label for="rd">Descrição do item</label><input id="rd" name="descricao" placeholder="Ex.: Microcomputador" required></div>
    <div class="fd"><label for="ru">Unidade</label><input id="ru" name="unidade" placeholder="Unidade"></div>
    <div class="fd"><label for="rv">Valor por unidade (R$)</label><input id="rv" name="valor" inputmode="decimal" placeholder="4.500,00" required></div>
    <div class="fd" style="grid-column:span 2"><label for="rf">Fonte</label><input id="rf" name="fonte" placeholder="Painel de Preços (compras.gov.br), mediana 2026" required></div>
    <div class="fd"><label for="rl">Link</label><input id="rl" name="url" type="url" placeholder="https://"></div>
    <div class="fd"><label for="rdt">Data</label><input id="rdt" name="data" type="date" value="<?= date('Y-m-d') ?>"></div>
    <div><button class="btn btn--ghost" type="submit"><?= icon('plus', 'bold') ?>Adicionar referência</button></div>
  </form>
<?php if ($refs): ?>
  <table class="tbl" style="margin-top:12px">
    <thead><tr><th>Descrição (normalizada)</th><th>Unidade</th><th>Valor</th><th class="hide-m">Fonte</th><th></th></tr></thead>
    <tbody>
<?php foreach ($refs as $r): ?>
      <tr><td><?= e($r['descricao_normalizada']) ?></td><td><?= e($r['unidade_medida'] ?: 'qualquer') ?></td><td><?= fv_money($r['valor_unitario']) ?></td>
        <td class="hide-m small"><?= $r['url'] ? '<a href="' . e($r['url']) . '" target="_blank" rel="noopener">' . e($r['fonte']) . '</a>' : e($r['fonte']) ?><br><span class="muted"><?= date_short($r['data_referencia']) ?></span></td>
        <td><form method="post" data-confirm="Remover esta referência?"><?= csrf_field() ?><input type="hidden" name="do" value="ref_del"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="link-btn" type="submit" style="color:var(--err)">remover</button></form></td></tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>
<?php else: ?>
<?php if ($fila): ?><div class="alert alert--warn"><?= icon('info') ?><span><?= $fila ?> compras ainda estão na fila para buscar os itens no PNCP. A coleta automática continua sozinha; "Coletar agora" adianta um pouco.</span></div><?php endif; ?>
<nav class="tabs" aria-label="Situação">
<?php foreach (FV_STATUS + ['todos' => 'Todas'] as $k => $label): ?>
  <a href="<?= e(fvq(['st' => $k, 'p' => ''])) ?>"<?= $k === $st ? ' class="on" aria-current="page"' : '' ?>><?= $label ?><span><?= $k === 'todos' ? array_sum($counts) : $counts[$k] ?></span></a>
<?php endforeach; ?>
</nav>
<div class="card">
  <form class="filters" method="get">
    <input type="hidden" name="st" value="<?= e($st) ?>">
    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Buscar por item, objeto, processo ou fornecedor">
    <select name="sec" onchange="this.form.submit()">
      <option value="">Todas as secretarias</option>
<?php foreach ($secsUsed as $s): ?>
      <option value="<?= e($s['secretaria'] ?: '_') ?>"<?= $sec === ($s['secretaria'] ?: '_') ? ' selected' : '' ?>><?= e($s['secretaria'] ? secretaria_short(fv_secretaria_name($s['secretaria'])) : 'Sem secretaria identificada') ?> (<?= (int) $s['n'] ?>)</option>
<?php endforeach; ?>
    </select>
    <label class="check small"><input type="checkbox" name="res" value="1"<?= $comValor ? ' checked' : '' ?> onchange="this.form.submit()"> Só com valor homologado</label>
    <button class="btn btn--ghost" type="submit"><?= icon('magnifying-glass') ?>Buscar</button>
  </form>
<?php if (!$rows): ?>
  <p class="empty"><?= array_sum($counts) ? 'Nenhuma compra aqui.' : 'Nenhuma compra coletada ainda. Clique em "Coletar agora".' ?></p>
<?php else: ?>
  <form method="post" id="fv-lote">
    <?= csrf_field() ?><input type="hidden" name="do" value="lote"><input type="hidden" name="st" value="<?= e($st) ?>"><input type="hidden" name="q" value="<?= e($term) ?>"><input type="hidden" name="sec" value="<?= e($sec) ?>">
    <div class="row fv-bulk"><label class="check small"><input type="checkbox" id="fv-all"> Selecionar todas</label>
      <select name="para"><option value="revisado">Marcar como revisadas</option><option value="publicado">Publicar</option><option value="ocultado">Ocultar</option><option value="novo">Voltar para novas</option></select>
      <button class="btn btn--ghost btn--sm" type="submit">Aplicar às selecionadas</button></div>
  <table class="tbl fv-tbl">
    <thead><tr><th style="width:28px"></th><th>Compra</th><th class="hide-m">Secretaria</th><th>Valor homologado</th><th class="hide-m">Itens</th><th class="hide-m">Data</th></tr></thead>
    <tbody>
<?php foreach ($rows as $c): $link = url('/admin/fiscalize-compra.php?id=' . $c['id']); ?>
      <tr data-href="<?= e($link) ?>" tabindex="0">
        <td><input type="checkbox" name="ids[]" value="<?= (int) $c['id'] ?>" aria-label="Selecionar"></td>
        <td><a class="t" href="<?= e($link) ?>"><?= e(str_limit((string) $c['objeto'], 120)) ?></a>
          <div class="small muted"><?= e($c['modalidade']) ?> · proc. <?= e($c['numero_processo'] ?: '—') ?><?= $c['srp'] ? ' · <span class="badge">registro de preços</span>' : '' ?> <span class="badge <?= ['novo' => 'badge--new', 'revisado' => 'badge--sched', 'publicado' => 'badge--pub', 'ocultado' => ''][$c['status']] ?>"><?= FV_STATUS[$c['status']] ?></span></div></td>
        <td class="hide-m small"><?= $c['secretaria'] ? e(secretaria_short(fv_secretaria_name($c['secretaria']))) : '<span class="muted">não identificada</span>' ?></td>
        <td><?= $c['valor_total'] !== null ? '<b>' . fv_money($c['valor_total']) . '</b>' : '<span class="small muted">' . ($c['itens_sync'] === 'pendente' ? 'buscando itens…' : 'sem resultado ainda') . '</span>' ?><?php if ((float) $c['valor_estimado'] > 0): ?><div class="small muted">estimado <?= fv_money($c['valor_estimado'], false) ?></div><?php endif; ?></td>
        <td class="hide-m small"><?= (int) $c['n_res'] ?> com resultado<?= $c['itens_sync'] === 'pendente' ? ' <span class="badge badge--draft">na fila</span>' : ($c['itens_sync'] === 'erro' ? ' <span class="badge" style="color:var(--err)">erro</span>' : '') ?>
          <?= $c['n_acima'] ? '<br><span class="badge badge--new">' . (int) $c['n_acima'] . ' acima da ref.</span>' : '' ?><?= $c['n_aviso'] ? ' <span class="badge badge--draft">' . (int) $c['n_aviso'] . ' p/ conferir</span>' : '' ?></td>
        <td class="hide-m small muted"><?= $c['data_homologacao'] ? 'homol. ' . date_short($c['data_homologacao']) : 'publ. ' . date_short($c['data_publicacao']) ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
  </form>
<?php if ($pages > 1): ?>
  <nav class="pager" aria-label="Páginas">
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <?= $i === $page ? '<span>' . $i . '</span>' : '<a href="' . e(fvq(['p' => $i])) . '">' . $i . '</a>' ?>
<?php endfor; ?>
  </nav>
<?php endif; ?>
<?php endif; ?>
</div>
<?php endif; ?>
<?php admin_foot('<script>(function(){var a=document.getElementById("fv-all");if(a)a.addEventListener("change",function(){document.querySelectorAll("#fv-lote input[name=\'ids[]\']").forEach(function(c){c.checked=a.checked;});});})();</script>');
