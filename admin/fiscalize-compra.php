<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require ROOT . '/app/fiscalize.php';
admin_guard(true);
fv_tables();

$id = (int) ($_GET['id'] ?? 0);
$c = q('SELECT * FROM fv_compras WHERE id = ?', [$id])->fetch();
if (!$c) { flash('err', 'Compra não encontrada.'); redirect(url('/admin/fiscalize.php')); }
$self = url('/admin/fiscalize-compra.php?id=' . $id);
$num = function (string $k): ?float { $v = trim(in($k)); if ($v === '') return null; $v = preg_replace('/[^\d,.\-]/', '', $v); if (strpos($v, ',') !== false) $v = str_replace(['.', ','], ['', '.'], $v); return (float) $v; };

if (is_post()) {
    csrf_check();
    $do = in('do');
    if (isset(FV_STATUS[$do])) {
        q('UPDATE fv_compras SET status = ?' . ($do === 'ocultado' && $c['status'] === 'publicado' ? ', corrigido_em = NOW()' : '') . ' WHERE id = ?', [$do, $id]);
        flash('ok', ['novo' => 'Voltou para novas.', 'revisado' => 'Marcada como revisada.', 'publicado' => 'Publicada: já aparece na página Fiscalize Você.', 'ocultado' => 'Ocultada: saiu da página.'][$do]);
        redirect($self);
    }
    if ($do === 'dados') {
        $s = in('secretaria', '', 20);
        q('UPDATE fv_compras SET secretaria = ?, secretaria_editada = 1, esclarecimento = ?, esclarecimento_data = ?, nota_interna = ? WHERE id = ?', [
            isset(SECRETARIAS[$s]) ? $s : null, in('esclarecimento', '', 5000) ?: null,
            preg_match('/^\d{4}-\d{2}-\d{2}$/', in('esclarecimento_data')) ? in('esclarecimento_data') : (in('esclarecimento') !== '' ? date('Y-m-d') : null),
            in('nota_interna', '', 5000) ?: null, $id]);
        flash('ok', 'Dados da compra salvos.');
        redirect($self);
    }
    if ($do === 'item') {
        $iid = (int) in('item_id');
        $it = q('SELECT * FROM fv_itens WHERE id = ? AND compra_id = ?', [$iid, $id])->fetch();
        if ($it) {
            $refv = $num('referencia_valor');
            $refu = in('referencia_url', '', 500);
            if ($refu !== '' && !filter_var($refu, FILTER_VALIDATE_URL)) { flash('err', 'Link da referência inválido.'); redirect($self . '#item-' . $iid); }
            if ($refv !== null && in('referencia_fonte') === '') { flash('err', 'Informe a fonte da referência (ex.: Painel de Preços, compra de Teresópolis…).'); redirect($self . '#item-' . $iid); }
            q('UPDATE fv_itens SET titulo_simples = ?, quantidade = ?, unidade_medida = ?, valor_total_item = ?, oculto = ?, referencia_manual = ?, referencia_valor = ?, referencia_fonte = ?, referencia_url = ?, editado = 1 WHERE id = ?', [
                in('titulo_simples', '', 255) ?: null, $num('quantidade'), in('unidade_medida', '', 80) ?: null, $num('valor_total_item'),
                !empty($_POST['oculto']) ? 1 : 0, $refv !== null ? 1 : 0, $refv, $refv !== null ? in('referencia_fonte', '', 255) : null, $refv !== null ? ($refu ?: null) : null, $iid]);
            if (!empty($_POST['salvar_ref']) && $refv !== null) {
                q('INSERT INTO fv_referencias (descricao_normalizada, unidade_medida, valor_unitario, fonte, url, data_referencia) VALUES (?, ?, ?, ?, ?, CURDATE())',
                    [$it['descricao_normalizada'] ?: fv_normalize((string) $it['descricao']), in('unidade_medida', '', 80) ?: null, $refv, in('referencia_fonte', '', 255), $refu ?: null]);
                fv_recalc_all();
            } else fv_refresh_compra($id);
            if ($c['status'] === 'publicado') q('UPDATE fv_compras SET corrigido_em = NOW() WHERE id = ?', [$id]);
            flash('ok', 'Item conferido e salvo. O valor por unidade foi recalculado.');
        }
        redirect($self . '#item-' . $iid);
    }
    if ($do === 'item_reset') {
        q('UPDATE fv_itens SET editado = 0, titulo_simples = NULL, referencia_manual = 0 WHERE id = ? AND compra_id = ?', [(int) in('item_id'), $id]);
        q("UPDATE fv_compras SET itens_sync = 'pendente' WHERE id = ?", [$id]);
        flash('ok', 'O item volta a seguir o PNCP na próxima coleta.');
        redirect($self);
    }
    if ($do === 'resync') {
        try {
            $t0 = microtime(true);
            $n = fv_sync_itens($c, function () use ($t0) { return 50 - (microtime(true) - $t0); });
            flash($n === null ? 'err' : 'ok', $n === null ? 'Compra grande: parte dos itens foi atualizada; a coleta automática termina o resto.' : "Itens atualizados do PNCP: $n resultados.");
            if ($n === null) q("UPDATE fv_compras SET itens_sync = 'pendente' WHERE id = ?", [$id]);
        } catch (Throwable $ex) { flash('err', 'Não foi possível falar com o PNCP: ' . $ex->getMessage()); }
        redirect($self);
    }
}

$itens = q('SELECT * FROM fv_itens WHERE compra_id = ? ORDER BY numero_item, seq_resultado', [$id])->fetchAll();
$avisos = count(array_filter($itens, function ($i) { return $i['aviso'] && !$i['oculto']; }));
$acima = count(array_filter($itens, function ($i) { return in_array($i['situacao'], ['acima', 'muito_acima'], true) && !$i['oculto']; }));
$sitCls = ['normal' => 'badge--pub', 'acima' => 'badge--new', 'muito_acima' => 'badge--err', 'sem_referencia' => '', 'revisar' => 'badge--draft'];

admin_head('Compra ' . ($c['numero_processo'] ?: $c['pncp_id']), 'fv');
?>
<div class="top">
  <div><h1 style="font-size:22px"><?= e(str_limit((string) $c['objeto'], 160)) ?></h1>
    <p><span class="badge <?= ['novo' => 'badge--new', 'revisado' => 'badge--sched', 'publicado' => 'badge--pub', 'ocultado' => ''][$c['status']] ?>"><?= FV_STATUS[$c['status']] ?></span>
      <?= e($c['modalidade']) ?> · processo <?= e($c['numero_processo'] ?: '—') ?> · PNCP <?= e($c['pncp_id']) ?><?= $c['srp'] ? ' · <span class="badge">registro de preços</span>' : '' ?></p></div>
  <a class="btn btn--ghost" href="<?= e(url('/admin/fiscalize.php')) ?>"><?= icon('arrow-left') ?>Voltar</a>
</div>
<div class="edit">
  <div>
    <div class="card">
      <div class="grid g4 fv-kpis">
        <div><small class="muted">Valor homologado</small><b><?= fv_money($c['valor_total']) ?></b></div>
        <div><small class="muted">Valor estimado</small><b><?= fv_money($c['valor_estimado']) ?></b></div>
        <div><small class="muted">Homologação</small><b><?= $c['data_homologacao'] ? date_short($c['data_homologacao']) : '—' ?></b></div>
        <div><small class="muted">Publicação no PNCP</small><b><?= date_short($c['data_publicacao']) ?: '—' ?></b></div>
      </div>
      <p class="small" style="margin:14px 0 0"><b>Objeto completo:</b> <?= e($c['objeto']) ?></p>
      <p class="small" style="margin:8px 0 0"><b>Fornecedor:</b> <?= e($c['fornecedor_nome'] ?: '—') ?><?= $c['fornecedor_cnpj'] ? ' · CNPJ ' . e(fv_cnpj($c['fornecedor_cnpj'])) : '' ?> · <a href="<?= e($c['url_fonte']) ?>" target="_blank" rel="noopener">Abrir no PNCP <?= icon('arrow-square-out') ?></a></p>
<?php if ($c['srp']): ?>
      <p class="alert alert--warn small" style="margin:12px 0 0"><?= icon('info') ?><span>Registro de preços: o valor homologado é o máximo que a Prefeitura pode comprar no período, não o que já foi pago. A página explica isso no card.</span></p>
<?php endif; ?>
    </div>

    <div class="card" style="margin-top:18px">
      <div class="row" style="justify-content:space-between;margin-bottom:10px">
        <h2 style="margin:0">Itens com resultado (<?= count($itens) ?>)</h2>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="resync"><button class="btn btn--ghost btn--sm" type="submit"><?= icon('arrows-clockwise') ?>Buscar de novo no PNCP</button></form>
      </div>
<?php if (!$itens): ?>
      <p class="empty"><?= $c['itens_sync'] === 'pendente' ? 'Itens ainda na fila da coleta.' : 'Nenhum item com resultado homologado ainda (licitação em andamento, deserta ou fracassada).' ?></p>
<?php else: ?>
      <p class="small muted" style="margin:0 0 12px">Antes de publicar, confira valor total, quantidade e unidade com o processo. O que você corrigir aqui não é sobrescrito pela coleta.</p>
      <div class="fv-items">
<?php foreach ($itens as $it): ?>
        <details class="fv-item<?= $it['oculto'] ? ' is-off' : '' ?>" id="item-<?= (int) $it['id'] ?>"<?= $it['aviso'] ? ' open' : '' ?>>
          <summary>
            <span class="fv-item-n"><?= (int) $it['numero_item'] ?></span>
            <span class="fv-item-t"><b><?= e($it['titulo_simples'] ?: fv_short_title((string) $it['descricao'])) ?></b>
              <small class="muted"><?= e(fv_qty_unit($it['quantidade'], $it['unidade_medida'])) ?> · total <?= fv_money($it['valor_total_item']) ?> · <?= e(str_limit((string) $it['fornecedor_nome'], 50)) ?></small></span>
            <span class="fv-item-v"><b><?= fv_money($it['valor_unitario']) ?></b><small class="muted"><?= e(fv_per($it['unidade_medida'])) ?></small></span>
            <span class="badge <?= $sitCls[$it['situacao']] ?>"><?= $it['diferenca_percentual'] !== null ? ($it['diferenca_percentual'] > 0 ? '+' : '') . number_format((float) $it['diferenca_percentual'], 0, ',', '.') . '%' : FV_SITUACAO[$it['situacao']] ?></span>
            <?= $it['editado'] ? '<span class="badge badge--sched">conferido</span>' : '' ?><?= $it['oculto'] ? '<span class="badge">oculto</span>' : '' ?>
          </summary>
<?php if ($it['aviso']): ?>          <p class="alert alert--warn small"><?= icon('warning-circle') ?><span><?= e($it['aviso']) ?></span></p><?php endif; ?>
          <p class="small muted" style="margin:0 0 10px"><?= e($it['descricao']) ?></p>
          <p class="small" style="margin:0 0 12px">Fornecedor: <b><?= e($it['fornecedor_nome'] ?: '—') ?></b><?= $it['fornecedor_cnpj'] ? ' · ' . e(fv_cnpj($it['fornecedor_cnpj'])) : '' ?> · Valor unitário informado no PNCP: <?= fv_money($it['valor_unitario_informado']) ?> · Estimado pela Prefeitura: <?= fv_money($it['valor_unitario_estimado']) ?>
            <?php if ($it['referencia_valor'] !== null): ?><br>Referência: <b><?= fv_money($it['referencia_valor']) ?></b> — <?= $it['referencia_url'] ? '<a href="' . e($it['referencia_url']) . '" target="_blank" rel="noopener">' . e($it['referencia_fonte']) . '</a>' : e($it['referencia_fonte']) ?><?php endif; ?></p>
          <form method="post" class="grid g4 fv-item-form">
            <?= csrf_field() ?><input type="hidden" name="do" value="item"><input type="hidden" name="item_id" value="<?= (int) $it['id'] ?>">
            <div class="fd" style="grid-column:1/-1"><label>Título simples (o que o cidadão lê)</label><input name="titulo_simples" value="<?= e($it['titulo_simples']) ?>" placeholder="Ex.: Computadores de mesa"></div>
            <div class="fd"><label>Quantidade</label><input name="quantidade" inputmode="decimal" value="<?= e($it['quantidade'] !== null ? fv_qty($it['quantidade']) : '') ?>"></div>
            <div class="fd"><label>Unidade</label><input name="unidade_medida" value="<?= e($it['unidade_medida']) ?>"></div>
            <div class="fd" style="grid-column:span 2"><label>Valor total do item (R$)</label><input name="valor_total_item" inputmode="decimal" value="<?= e($it['valor_total_item'] !== null ? number_format((float) $it['valor_total_item'], 2, ',', '.') : '') ?>"></div>
            <div class="fd"><label>Referência por unidade (R$)</label><input name="referencia_valor" inputmode="decimal" value="<?= e($it['referencia_manual'] && $it['referencia_valor'] !== null ? number_format((float) $it['referencia_valor'], 2, ',', '.') : '') ?>" placeholder="automática"></div>
            <div class="fd"><label>Fonte da referência</label><input name="referencia_fonte" value="<?= e($it['referencia_manual'] ? $it['referencia_fonte'] : '') ?>" placeholder="Painel de Preços…"></div>
            <div class="fd" style="grid-column:span 2"><label>Link da referência</label><input name="referencia_url" type="url" value="<?= e($it['referencia_manual'] ? $it['referencia_url'] : '') ?>" placeholder="https://"></div>
            <div class="row" style="grid-column:1/-1">
              <label class="check small"><input type="checkbox" name="salvar_ref" value="1"> Usar esta referência para o mesmo item em outras compras</label>
              <label class="check small"><input type="checkbox" name="oculto" value="1"<?= $it['oculto'] ? ' checked' : '' ?>> Não mostrar este item na página</label>
            </div>
            <div class="row" style="grid-column:1/-1"><button class="btn btn--sm" type="submit"><?= icon('check', 'bold') ?>Salvar item conferido</button></div>
          </form>
<?php if ($it['editado']): ?>
          <form method="post" style="margin-top:8px"><?= csrf_field() ?><input type="hidden" name="do" value="item_reset"><input type="hidden" name="item_id" value="<?= (int) $it['id'] ?>"><button class="link-btn small" type="submit">Desfazer correções e voltar a seguir o PNCP</button></form>
<?php endif; ?>
        </details>
<?php endforeach; ?>
      </div>
<?php endif; ?>
    </div>
  </div>

  <div class="edit-side">
    <div class="card">
      <h2>Publicação</h2>
<?php if ($avisos): ?><p class="alert alert--warn small"><?= icon('warning-circle') ?><span><?= $avisos ?> item(ns) com algo para conferir antes de publicar.</span></p><?php endif; ?>
<?php if ($acima): ?><p class="small" style="margin:0 0 10px"><span class="badge badge--new"><?= $acima ?> acima da referência</span></p><?php endif; ?>
      <div class="tk-acts">
<?php if ($c['status'] !== 'publicado'): ?>
        <form method="post"<?= $avisos ? ' data-confirm="Há itens com aviso para conferir. Publicar mesmo assim?"' : '' ?>><?= csrf_field() ?><input type="hidden" name="do" value="publicado"><button class="btn btn--or btn--block" type="submit"<?= $itens ? '' : ' disabled' ?>><?= icon('seal-check') ?>Publicar na página</button></form>
<?php endif; ?>
<?php if ($c['status'] === 'novo'): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="revisado"><button class="btn btn--ghost btn--block" type="submit"><?= icon('check') ?>Marcar como revisada</button></form>
<?php endif; ?>
<?php if ($c['status'] !== 'ocultado'): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="ocultado"><button class="btn btn--danger btn--block" type="submit"><?= icon('eye-slash') ?><?= $c['status'] === 'publicado' ? 'Tirar da página (ocultar)' : 'Ocultar' ?></button></form>
<?php else: ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="novo"><button class="btn btn--ghost btn--block" type="submit"><?= icon('arrow-counter-clockwise') ?>Voltar para novas</button></form>
<?php endif; ?>
      </div>
<?php if ($c['corrigido_em']): ?><p class="small muted" style="margin:10px 0 0">Corrigida em <?= date('d/m/Y H:i', strtotime($c['corrigido_em'])) ?>.</p><?php endif; ?>
    </div>
    <form method="post" class="card">
      <?= csrf_field() ?><input type="hidden" name="do" value="dados">
      <h2>Dados da compra</h2>
      <div class="fd"><label for="secretaria">Secretaria</label><select id="secretaria" name="secretaria"><?= secretaria_options($c['secretaria']) ?></select>
        <p class="hint"><?= $c['secretaria_editada'] ? 'Definida por você.' : 'Identificada automaticamente pelo texto do objeto. Confira.' ?></p></div>
      <div class="fd"><label for="esc">Esclarecimento da Prefeitura</label><textarea id="esc" name="esclarecimento" rows="4" placeholder="Se a Prefeitura explicar a diferença, cole aqui. Aparece no card."><?= e($c['esclarecimento']) ?></textarea></div>
      <div class="fd"><label for="escd">Data do esclarecimento</label><input type="date" id="escd" name="esclarecimento_data" value="<?= e($c['esclarecimento_data']) ?>"></div>
      <div class="fd"><label for="nota">Nota interna <small class="muted">(só equipe)</small></label><textarea id="nota" name="nota_interna" rows="3"><?= e($c['nota_interna']) ?></textarea></div>
      <button class="btn btn--block" type="submit"><?= icon('check', 'bold') ?>Salvar</button>
    </form>
  </div>
</div>
<?php admin_foot();
