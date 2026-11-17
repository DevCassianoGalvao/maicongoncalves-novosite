<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require ROOT . '/app/amigos.php';
$user = admin_guard();
ab_tables();

$aba = in_array($_GET['aba'] ?? '', ['tipos', 'config', 'importar'], true) ? $_GET['aba'] : 'lista';
$self = url('/admin/amigos.php');

if (is_post()) {
    csrf_check();
    $do = in('do');
    if ($do === 'config' && is_admin($user)) {
        ab_cfg_set('pagina_publica', !empty($_POST['pagina_publica']) ? '1' : '0');
        ab_cfg_set('indexar', !empty($_POST['indexar']) ? '1' : '0');
        $intro = in('intro', '', 600);
        if ($intro !== '') ab_cfg_set('intro', $intro);
        flash('ok', 'Configurações salvas.' . (!empty($_POST['pagina_publica']) ? ' O link está aberto.' : ' A página está fechada (só quem está logado vê).'));
        redirect($self . '?aba=config');
    }
    if ($do === 'demo_del' && is_admin($user)) {
        $n = (int) q('SELECT COUNT(*) FROM ab_instituicoes WHERE demo = 1')->fetchColumn();
        q('DELETE FROM ab_instituicoes WHERE demo = 1');
        flash('ok', "$n instituição(ões) de exemplo apagada(s).");
        redirect($self . '?aba=config');
    }
    if ($do === 'tipo_add') {
        $nome = in('nome', '', 80);
        $ic = in('icone', 'gift', 40);
        if ($nome === '') flash('err', 'Dê um nome ao tipo de doação.');
        else {
            $slug = substr(slugify($nome), 0, 40) ?: 'tipo';
            $s = $slug; $k = 2;
            while (q('SELECT id FROM ab_tipos WHERE slug = ?', [$s])->fetch()) $s = $slug . '-' . $k++;
            q('INSERT INTO ab_tipos (slug, nome, icone, ordem) VALUES (?, ?, ?, ?)', [$s, $nome, in_array($ic, AB_ICONES, true) ? $ic : 'gift', (int) q('SELECT COALESCE(MAX(ordem),0)+1 FROM ab_tipos')->fetchColumn()]);
            flash('ok', 'Tipo de doação criado.');
        }
        redirect($self . '?aba=tipos');
    }
    if ($do === 'tipos_save') {
        foreach ((array) ($_POST['t'] ?? []) as $id => $v) {
            $nome = mb_substr(trim((string) ($v['nome'] ?? '')), 0, 80);
            if ($nome === '') continue;
            $ic = (string) ($v['icone'] ?? 'gift');
            q('UPDATE ab_tipos SET nome = ?, icone = ?, ordem = ?, ativo = ? WHERE id = ?', [$nome, in_array($ic, AB_ICONES, true) ? $ic : 'gift', (int) ($v['ordem'] ?? 0), !empty($v['ativo']) ? 1 : 0, (int) $id]);
        }
        flash('ok', 'Tipos de doação salvos.');
        redirect($self . '?aba=tipos');
    }
    if ($do === 'importar') {
        $file = $_FILES['csv']['tmp_name'] ?? '';
        if (!$file || !is_uploaded_file($file)) { flash('err', 'Escolha o arquivo CSV.'); redirect($self . '?aba=importar'); }
        $raw = (string) file_get_contents($file);
        if (!mb_check_encoding($raw, 'UTF-8')) $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $lines = preg_split('/\r\n|\n|\r/', trim($raw));
        $sep = substr_count($lines[0] ?? '', ';') >= substr_count($lines[0] ?? '', ',') ? ';' : ',';
        $head = array_map(function ($h) { return slugify(trim($h)); }, str_getcsv(array_shift($lines) ?? '', $sep));
        $tipoBy = [];
        foreach (ab_tipos(false) as $t) { $tipoBy[slugify($t['nome'])] = (int) $t['id']; $tipoBy[$t['slug']] = (int) $t['id']; }
        $causaBy = [];
        foreach (AB_CAUSAS as $k => $v) { $causaBy[$k] = $k; $causaBy[slugify($v)] = $k; }
        $n = 0; $skip = 0;
        foreach ($lines as $ln) {
            if (trim($ln) === '') continue;
            $row = array_pad(str_getcsv($ln, $sep), count($head), '');
            $r = array_combine($head, array_slice($row, 0, count($head)));
            $nome = mb_substr(trim((string) ($r['nome'] ?? '')), 0, 160);
            if ($nome === '' || q('SELECT id FROM ab_instituicoes WHERE nome = ?', [$nome])->fetch()) { $skip++; continue; }
            $c = slugify((string) ($r['causa'] ?? ''));
            q('INSERT INTO ab_instituicoes (slug, nome, causa, resumo, endereco, bairro, whatsapp, telefone, email, instagram, horarios, resp_nome, resp_telefone, status, origem, token)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'rascunho\', \'importacao\', ?)', [ab_slug_unique($nome), $nome, $causaBy[$c] ?? 'outra',
                mb_substr(trim((string) ($r['resumo'] ?? '')), 0, 300) ?: null, mb_substr(trim((string) ($r['endereco'] ?? '')), 0, 200) ?: null,
                mb_substr(trim((string) ($r['bairro'] ?? '')), 0, 120) ?: null, mb_substr(trim((string) ($r['whatsapp'] ?? '')), 0, 30) ?: null,
                mb_substr(trim((string) ($r['telefone'] ?? '')), 0, 30) ?: null, mb_substr(trim((string) ($r['email'] ?? '')), 0, 160) ?: null,
                mb_substr(trim((string) ($r['instagram'] ?? '')), 0, 160) ?: null, mb_substr(trim((string) ($r['horarios'] ?? '')), 0, 500) ?: null,
                mb_substr(trim((string) ($r['responsavel'] ?? '')), 0, 120) ?: null, mb_substr(trim((string) ($r['telefone-responsavel'] ?? '')), 0, 30) ?: null, ab_new_token()]);
            $iid = (int) db()->lastInsertId();
            foreach (preg_split('/\s*[,|\/]\s*/u', (string) ($r['tipos'] ?? '')) as $tn) {
                $tid = $tipoBy[slugify($tn)] ?? null;
                if ($tid) q('INSERT IGNORE INTO ab_inst_tipos (inst_id, tipo_id) VALUES (?, ?)', [$iid, $tid]);
            }
            $n++;
        }
        flash($n ? 'ok' : 'err', "$n instituição(ões) importada(s) como rascunho." . ($skip ? " $skip linha(s) ignorada(s) (sem nome ou já cadastrada)." : ''));
        redirect($self);
    }
}
if (($_GET['modelo'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="amigos-do-bem-modelo.csv"');
    echo "\xEF\xBB\xBF" . "nome;causa;resumo;endereco;bairro;whatsapp;telefone;email;instagram;horarios;tipos;responsavel;telefone responsavel\n";
    echo "Lar Exemplo;Crianças e adolescentes;Atende 30 crianças no contraturno;Rua Exemplo, 10;Centro;(22) 99999-9999;;contato@exemplo.org;@larexemplo;Seg a sex, 8h às 17h;Alimentos, Roupas e calçados, Brinquedos;Maria;(22) 98888-8888\n";
    exit;
}

$st = (string) ($_GET['st'] ?? '');
$term = trim((string) ($_GET['q'] ?? ''));
$where = []; $args = [];
if (isset(AB_STATUS[$st])) { $where[] = 'status = ?'; $args[] = $st; }
if ($term !== '') { $l = '%' . addcslashes($term, '%_\\') . '%'; $where[] = '(nome LIKE ? OR bairro LIKE ? OR resp_nome LIKE ?)'; array_push($args, $l, $l, $l); }
$rows = ab_hydrate(q('SELECT * FROM ab_instituicoes' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY FIELD(status, \'rascunho\', \'publicada\', \'oculta\'), demo, nome', $args)->fetchAll());
$counts = array_fill_keys(array_keys(AB_STATUS), 0);
foreach (q('SELECT status, COUNT(*) n FROM ab_instituicoes GROUP BY status')->fetchAll() as $r) $counts[$r['status']] = (int) $r['n'];
$velhas = (int) q("SELECT COUNT(*) FROM ab_instituicoes WHERE status = 'publicada' AND demo = 0 AND (verificada_em IS NULL OR verificada_em < CURDATE() - INTERVAL 90 DAY)")->fetchColumn();
$nDemo = (int) q('SELECT COUNT(*) FROM ab_instituicoes WHERE demo = 1')->fetchColumn();

admin_head('Amigos do Bem', 'ab');
?>
<div class="top">
  <div><h1>Amigos do Bem</h1><p>Instituições que recebem doações. Só as <b>publicadas</b> aparecem no site.
    <?= ab_cfg('pagina_publica') === '1' ? '<span class="badge badge--pub">Link aberto</span>' : '<span class="badge badge--draft">Página fechada</span>' ?><?= ab_cfg('indexar') === '1' ? ' <span class="badge badge--pub">No Google</span>' : '' ?></p></div>
  <div class="row">
    <a class="btn btn--ghost" href="<?= e(url('/amigos-do-bem/')) ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>Ver a página</a>
    <a class="btn btn--or" href="<?= e(url('/admin/amigos-instituicao.php')) ?>"><?= icon('plus', 'bold') ?>Nova instituição</a>
  </div>
</div>
<nav class="tabs" aria-label="Seções">
  <a href="<?= e($self) ?>"<?= $aba === 'lista' ? ' class="on"' : '' ?>>Instituições<span><?= array_sum($counts) ?></span></a>
  <a href="<?= e($self . '?aba=tipos') ?>"<?= $aba === 'tipos' ? ' class="on"' : '' ?>>Tipos de doação</a>
  <a href="<?= e($self . '?aba=importar') ?>"<?= $aba === 'importar' ? ' class="on"' : '' ?>>Importar planilha</a>
<?php if (is_admin($user)): ?>  <a href="<?= e($self . '?aba=config') ?>"<?= $aba === 'config' ? ' class="on"' : '' ?>>Configurações</a><?php endif; ?>
</nav>

<?php if ($aba === 'lista'): ?>
<?php if ($velhas): ?><div class="alert alert--warn"><?= icon('warning-circle') ?><span><?= $velhas ?> instituição(ões) publicada(s) sem conferência há mais de 90 dias. Confirme endereço, contatos e PIX e clique em "Marcar como conferida hoje".</span></div><?php endif; ?>
<div class="card">
  <form class="filters" method="get">
    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Buscar por nome, bairro ou responsável">
    <select name="st" onchange="this.form.submit()"><option value="">Todas as situações</option><?php foreach (AB_STATUS as $k => $v): ?><option value="<?= $k ?>"<?= $st === $k ? ' selected' : '' ?>><?= $v ?> (<?= $counts[$k] ?>)</option><?php endforeach; ?></select>
    <button class="btn btn--ghost" type="submit"><?= icon('magnifying-glass') ?>Buscar</button>
  </form>
<?php if (!$rows): ?>
  <p class="empty">Nenhuma instituição aqui.</p>
<?php else: ?>
  <table class="tbl">
    <thead><tr><th>Instituição</th><th class="hide-m">Recebe</th><th>Situação</th><th class="hide-m">Conferida</th><th class="hide-m">Cliques no WhatsApp</th></tr></thead>
    <tbody>
<?php foreach ($rows as $r): $link = url('/admin/amigos-instituicao.php?id=' . $r['id']); $old = !$r['verificada_em'] || strtotime($r['verificada_em']) < strtotime('-90 days'); ?>
      <tr data-href="<?= e($link) ?>" tabindex="0">
        <td><div class="ab-adm-name"><?php if ($r['logo']): ?><img src="<?= e(ab_img($r['logo'])) ?>" alt="" width="40" height="40"><?php endif; ?><div><a class="t" href="<?= e($link) ?>"><?= e($r['nome']) ?></a>
          <div class="small muted"><?= e(ab_causa($r['causa'])) ?> · <?= e($r['bairro'] ?: '—') ?><?= $r['demo'] ? ' · <span class="badge">exemplo</span>' : '' ?><?= $r['origem'] === 'formulario' ? ' · <span class="badge badge--new">pelo site</span>' : '' ?><?= $r['necessidades'] ? ' · ' . count($r['necessidades']) . ' pedido(s) urgente(s)' : '' ?></div></div></div></td>
        <td class="hide-m small"><?= e(implode(', ', array_map(function ($t) { return $t['nome']; }, $r['tipos'])) ?: '—') ?></td>
        <td><span class="badge <?= ['rascunho' => 'badge--draft', 'publicada' => 'badge--pub', 'oculta' => ''][$r['status']] ?>"><?= AB_STATUS[$r['status']] ?></span><?= !$r['autorizado'] ? '<div class="small" style="color:var(--err)">sem autorização</div>' : '' ?></td>
        <td class="hide-m small<?= $old && $r['status'] === 'publicada' && !$r['demo'] ? '" style="color:var(--err)' : ' muted' ?>"><?= $r['verificada_em'] ? date_short($r['verificada_em']) : 'nunca' ?></td>
        <td class="hide-m small"><?= (int) $r['cliques_wa'] ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>

<?php elseif ($aba === 'tipos'): $tipos = ab_tipos(false); $usos = []; foreach (q('SELECT tipo_id, COUNT(*) n FROM ab_inst_tipos GROUP BY tipo_id')->fetchAll() as $u) $usos[(int) $u['tipo_id']] = (int) $u['n']; ?>
<div class="grid g2" style="align-items:start">
  <form method="post" class="card">
    <?= csrf_field() ?><input type="hidden" name="do" value="tipos_save">
    <h2>Tipos de doação</h2>
    <p class="small muted" style="margin:-6px 0 12px">Aparecem como botões no topo da página ("O que você quer doar?"). Desativado some do site.</p>
    <table class="tbl">
      <thead><tr><th>Nome</th><th>Ícone</th><th style="width:70px">Ordem</th><th>Ativo</th></tr></thead>
      <tbody>
<?php foreach ($tipos as $id => $t): ?>
        <tr><td><input name="t[<?= $id ?>][nome]" value="<?= e($t['nome']) ?>" maxlength="80"><div class="small muted"><?= $usos[$id] ?? 0 ?> instituição(ões)</div></td>
          <td><div class="row" style="gap:6px;flex-wrap:nowrap"><span class="ab-adm-ic"><?= icon($t['icone'], 'fill') ?></span><select name="t[<?= $id ?>][icone]"><?php foreach (AB_ICONES as $ic): ?><option<?= $ic === $t['icone'] ? ' selected' : '' ?>><?= $ic ?></option><?php endforeach; ?></select></div></td>
          <td><input type="number" name="t[<?= $id ?>][ordem]" value="<?= (int) $t['ordem'] ?>"></td>
          <td><label class="check"><input type="checkbox" name="t[<?= $id ?>][ativo]" value="1"<?= $t['ativo'] ? ' checked' : '' ?>></label></td></tr>
<?php endforeach; ?>
      </tbody>
    </table>
    <button class="btn" type="submit" style="margin-top:12px"><?= icon('check', 'bold') ?>Salvar</button>
  </form>
  <form method="post" class="card">
    <?= csrf_field() ?><input type="hidden" name="do" value="tipo_add">
    <h2>Novo tipo</h2>
    <div class="fd"><label for="tn">Nome</label><input id="tn" name="nome" maxlength="80" placeholder="Ex.: Doação de sangue" required></div>
    <div class="fd"><label for="ti">Ícone</label><select id="ti" name="icone"><?php foreach (AB_ICONES as $ic): ?><option><?= $ic ?></option><?php endforeach; ?></select></div>
    <button class="btn btn--ghost" type="submit"><?= icon('plus', 'bold') ?>Criar</button>
  </form>
</div>

<?php elseif ($aba === 'importar'): ?>
<form method="post" enctype="multipart/form-data" class="card" style="max-width:760px">
  <?= csrf_field() ?><input type="hidden" name="do" value="importar">
  <h2>Importar instituições de uma planilha</h2>
  <p class="small">Salve a planilha como <b>CSV</b> (no Excel ou Google Planilhas: Arquivo › Baixar › CSV) com uma linha por instituição. Todas entram como <b>rascunho</b>: a equipe confere, completa fotos e PIX e publica.</p>
  <p class="small">Colunas aceitas (a primeira linha com os nomes): <code>nome</code> (obrigatória), <code>causa</code>, <code>resumo</code>, <code>endereco</code>, <code>bairro</code>, <code>whatsapp</code>, <code>telefone</code>, <code>email</code>, <code>instagram</code>, <code>horarios</code>, <code>tipos</code> (separados por vírgula, com os nomes da aba Tipos de doação), <code>responsavel</code>, <code>telefone responsavel</code>. Nomes já cadastrados são ignorados.</p>
  <p><a class="btn btn--ghost btn--sm" href="<?= e($self . '?modelo=csv') ?>"><?= icon('download-simple') ?>Baixar planilha modelo</a></p>
  <div class="fd"><label for="csv">Arquivo CSV</label><input type="file" id="csv" name="csv" accept=".csv,text/csv" required></div>
  <button class="btn" type="submit"><?= icon('arrow-square-out') ?>Importar</button>
</form>

<?php elseif ($aba === 'config' && is_admin($user)): ?>
<div class="grid g2" style="align-items:start">
  <form method="post" class="card">
    <?= csrf_field() ?><input type="hidden" name="do" value="config">
    <h2>Página pública</h2>
    <label class="check" style="margin-bottom:6px"><input type="checkbox" name="pagina_publica" value="1"<?= ab_cfg('pagina_publica') === '1' ? ' checked' : '' ?>> Link aberto (qualquer pessoa com o link vê)</label>
    <p class="hint small muted" style="margin:0 0 14px">Desmarcado, /amigos-do-bem/ dá "página não encontrada" para quem não está logado no painel. O link das instituições para atualizar as necessidades continua funcionando.</p>
    <label class="check" style="margin-bottom:6px"><input type="checkbox" name="indexar" value="1"<?= ab_cfg('indexar') === '1' ? ' checked' : '' ?>> Aparecer no Google</label>
    <p class="hint small muted" style="margin:0 0 14px">Deixe desmarcado enquanto estiver em teste. O link no menu do site entra quando for lançar.</p>
    <div class="fd"><label for="intro">Texto do topo da página</label><textarea id="intro" name="intro" rows="4" maxlength="600"><?= e(ab_cfg('intro')) ?></textarea></div>
    <button class="btn" type="submit"><?= icon('check', 'bold') ?>Salvar</button>
  </form>
  <div class="card">
    <h2>Instituições de exemplo</h2>
<?php if ($nDemo): ?>
    <p class="small">Há <b><?= $nDemo ?></b> instituições fictícias, criadas para testar a página. Elas aparecem com o selo "Exemplo" e os contatos não funcionam. Apague antes de lançar.</p>
    <form method="post" data-confirm="Apagar as <?= $nDemo ?> instituições de exemplo? Isso não tem volta."><?= csrf_field() ?><input type="hidden" name="do" value="demo_del"><button class="btn btn--danger" type="submit"><?= icon('trash') ?>Apagar exemplos</button></form>
<?php else: ?>
    <p class="small muted">Não há instituições de exemplo.</p>
<?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php admin_foot();
