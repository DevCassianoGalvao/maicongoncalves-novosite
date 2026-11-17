<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require ROOT . '/app/amigos.php';
require_once ROOT . '/app/images.php';
$user = admin_guard();
ab_tables();

$id = (int) ($_GET['id'] ?? 0);
$i = $id ? q('SELECT * FROM ab_instituicoes WHERE id = ?', [$id])->fetch() : null;
if ($id && !$i) { flash('err', 'Instituição não encontrada.'); redirect(url('/admin/amigos.php')); }
$i = $i ? ab_hydrate([$i])[0] : null;
$self = url('/admin/amigos-instituicao.php' . ($id ? '?id=' . $id : ''));
$tipos = ab_tipos(false);
$errors = [];

if (is_post()) {
    csrf_check();
    $do = in('do', 'save');
    if ($id && $do === 'delete') {
        q('DELETE FROM ab_instituicoes WHERE id = ?', [$id]);
        app_log('amigos', 'Excluída: ' . $i['nome'] . ' por ' . $user['email']);
        flash('ok', 'Instituição excluída.');
        redirect(url('/admin/amigos.php'));
    }
    if ($id && $do === 'verificar') {
        q('UPDATE ab_instituicoes SET verificada_em = CURDATE(), verificada_por = ? WHERE id = ?', [(int) $user['id'], $id]);
        flash('ok', 'Marcada como conferida hoje.');
        redirect($self);
    }
    if ($id && $do === 'token') {
        q('UPDATE ab_instituicoes SET token = ? WHERE id = ?', [ab_new_token(), $id]);
        flash('ok', 'Novo link gerado. O link antigo parou de funcionar.');
        redirect($self . '#link');
    }
    if ($do === 'save') {
        $v = function (string $k, int $max = 255) { return mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max); };
        $nome = $v('nome', 160);
        $status = isset(AB_STATUS[$v('status', 20)]) ? $v('status', 20) : 'rascunho';
        $autorizado = !empty($_POST['autorizado']) ? 1 : 0;
        $pixChave = $v('pix_chave', 140);
        $lat = filter_var($_POST['lat'] ?? '', FILTER_VALIDATE_FLOAT); $lng = filter_var($_POST['lng'] ?? '', FILTER_VALIDATE_FLOAT);
        if ($nome === '') $errors[] = 'Informe o nome da instituição.';
        $semAut = $status === 'publicada' && !$autorizado && !($i['demo'] ?? 0);
        if ($semAut) $status = $i['status'] ?? 'rascunho'; // salva o resto, mas não publica sem autorização
        if ($status === 'publicada' && $semAut) $status = 'rascunho';
        if ($pixChave !== '' && $v('pix_titular', 160) === '') $errors[] = 'Informe o nome do titular do PIX (o doador confere no banco).';
        foreach (['site', 'facebook'] as $k) if ($v($k) !== '' && !filter_var($v($k), FILTER_VALIDATE_URL)) $errors[] = 'Link inválido em ' . ($k === 'site' ? 'Site' : 'Facebook') . ' (comece com https://).';
        if ($v('email', 160) !== '' && !filter_var($v('email', 160), FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail público inválido.';
        // fotos
        $logo = $i['logo'] ?? null; $capa = $i['capa'] ?? null; $fotos = $i['fotos'] ?? [];
        try {
            if (!empty($_POST['logo_del'])) $logo = null;
            if (!empty($_POST['capa_del'])) $capa = null;
            if (is_uploaded_file($_FILES['logo']['tmp_name'] ?? '')) $logo = img_store_upload($_FILES['logo']['tmp_name'], 600)['src'];
            if (is_uploaded_file($_FILES['capa']['tmp_name'] ?? '')) $capa = img_store_upload($_FILES['capa']['tmp_name'], 1800)['src'];
            $del = array_map('intval', (array) ($_POST['foto_del'] ?? []));
            $fotos = array_values(array_filter($fotos, function ($f, $k) use ($del) { return !in_array($k, $del, true); }, ARRAY_FILTER_USE_BOTH));
            foreach ((array) ($_FILES['fotos']['tmp_name'] ?? []) as $tmp) {
                if (count($fotos) >= 8) break;
                if (is_uploaded_file($tmp)) { $s = img_store_upload($tmp, 1600); $fotos[] = ['src' => $s['src'], 'w' => $s['w'], 'h' => $s['h']]; }
            }
        } catch (Throwable $ex) { $errors[] = 'Foto: ' . $ex->getMessage(); }
        if (!$errors) {
            $cols = [
                'nome' => $nome, 'causa' => isset(AB_CAUSAS[$v('causa', 20)]) ? $v('causa', 20) : 'outra', 'resumo' => $v('resumo', 300) ?: null, 'historia' => $v('historia', 20000) ?: null,
                'logo' => $logo, 'capa' => $capa, 'fotos' => $fotos ? json_encode($fotos, JSON_UNESCAPED_SLASHES) : null,
                'cnpj' => $v('cnpj', 20) ?: null, 'mostrar_cnpj' => !empty($_POST['mostrar_cnpj']) ? 1 : 0,
                'nao_aceita' => $v('nao_aceita', 500) ?: null, 'busca_em_casa' => !empty($_POST['busca_em_casa']) ? 1 : 0, 'obs_doacao' => $v('obs_doacao', 3000) ?: null,
                'endereco' => $v('endereco', 200) ?: null, 'bairro' => $v('bairro', 120) ?: null, 'lat' => $lat !== false ? round($lat, 7) : null, 'lng' => $lng !== false ? round($lng, 7) : null,
                'horarios' => $v('horarios', 500) ?: null,
                'whatsapp' => $v('whatsapp', 30) ?: null, 'telefone' => $v('telefone', 30) ?: null, 'email' => $v('email', 160) ?: null, 'instagram' => $v('instagram', 160) ?: null,
                'facebook' => $v('facebook') ?: null, 'site' => $v('site') ?: null,
                'pix_chave' => $pixChave ?: null, 'pix_tipo' => $v('pix_tipo', 20) ?: null, 'pix_titular' => $v('pix_titular', 160) ?: null,
                'resp_nome' => $v('resp_nome', 120) ?: null, 'resp_cargo' => $v('resp_cargo', 80) ?: null, 'resp_telefone' => $v('resp_telefone', 30) ?: null, 'resp_email' => $v('resp_email', 160) ?: null,
                'status' => $status, 'autorizado' => $autorizado, 'autorizado_em' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v('autorizado_em', 10)) ? $v('autorizado_em', 10) : ($autorizado ? date('Y-m-d') : null),
                'nota_interna' => $v('nota_interna', 5000) ?: null,
            ];
            if ($id) {
                if ($nome !== $i['nome'] && !empty($_POST['novo_slug'])) $cols['slug'] = ab_slug_unique($nome, $id);
                q('UPDATE ab_instituicoes SET ' . implode(', ', array_map(function ($c) { return "$c = ?"; }, array_keys($cols))) . ' WHERE id = ?', array_merge(array_values($cols), [$id]));
            } else {
                $cols += ['slug' => ab_slug_unique($nome), 'token' => ab_new_token(), 'origem' => 'painel', 'verificada_em' => date('Y-m-d'), 'verificada_por' => (int) $user['id']];
                q('INSERT INTO ab_instituicoes (' . implode(',', array_keys($cols)) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')', array_values($cols));
                $id = (int) db()->lastInsertId();
            }
            // tipos aceitos
            q('DELETE FROM ab_inst_tipos WHERE inst_id = ?', [$id]);
            foreach ((array) ($_POST['tipo'] ?? []) as $tid => $on) {
                if (!isset($tipos[(int) $tid])) continue;
                q('INSERT INTO ab_inst_tipos (inst_id, tipo_id, obs) VALUES (?, ?, ?)', [$id, (int) $tid, mb_substr(trim((string) ($_POST['tipo_obs'][$tid] ?? '')), 0, 200) ?: null]);
            }
            // necessidades
            $rem = array_map('intval', (array) ($_POST['nec_del'] ?? []));
            if ($rem) q('DELETE FROM ab_necessidades WHERE inst_id = ? AND id IN (' . implode(',', array_fill(0, count($rem), '?')) . ')', array_merge([$id], $rem));
            foreach ((array) ($_POST['nec_item'] ?? []) as $k => $item) {
                $item = mb_substr(trim((string) $item), 0, 160);
                if ($item === '') continue;
                $pz = (string) ($_POST['nec_prazo'][$k] ?? '');
                q('INSERT INTO ab_necessidades (inst_id, item, quantidade, prazo) VALUES (?, ?, ?, ?)', [$id, $item, mb_substr(trim((string) ($_POST['nec_qtd'][$k] ?? '')), 0, 60) ?: null, preg_match('/^\d{4}-\d{2}-\d{2}$/', $pz) ? $pz : null]);
            }
            app_log('amigos', 'Salva: ' . $nome . ' por ' . $user['email']);
            if ($semAut) flash('err', 'Salva, mas não publicada: marque que a instituição autorizou a divulgação para publicar.');
            else flash('ok', $status === 'publicada' ? 'Salva e publicada no site.' : 'Salva.');
            redirect(url('/admin/amigos-instituicao.php?id=' . $id));
        }
    }
}

// valores do formulário (do POST com erro, da instituição ou vazios)
$f = $i ?: ['nome' => '', 'causa' => 'criancas', 'resumo' => '', 'historia' => '', 'logo' => null, 'capa' => null, 'fotos' => [], 'cnpj' => '', 'mostrar_cnpj' => 0,
    'nao_aceita' => '', 'busca_em_casa' => 0, 'obs_doacao' => '', 'endereco' => '', 'bairro' => '', 'lat' => null, 'lng' => null, 'horarios' => '', 'whatsapp' => '', 'telefone' => '',
    'email' => '', 'instagram' => '', 'facebook' => '', 'site' => '', 'pix_chave' => '', 'pix_tipo' => 'cnpj', 'pix_titular' => '', 'resp_nome' => '', 'resp_cargo' => '', 'resp_telefone' => '',
    'resp_email' => '', 'status' => 'rascunho', 'autorizado' => 0, 'autorizado_em' => null, 'nota_interna' => '', 'tipos' => [], 'necessidades' => [], 'demo' => 0];
if ($errors) foreach ($f as $k => $val) if (isset($_POST[$k]) && is_string($_POST[$k])) $f[$k] = $_POST[$k];
$marc = []; foreach ($f['tipos'] as $t) $marc[(int) $t['id']] = (string) $t['obs'];
$bairros = @include ROOT . '/app/bairros.php';
$leaflet = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"><script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>';
admin_head($id ? $f['nome'] : 'Nova instituição', 'ab', $leaflet);
?>
<div class="top">
  <div><h1><?= $id ? e($f['nome']) : 'Nova instituição' ?></h1>
<?php if ($id): ?>    <p><span class="badge <?= ['rascunho' => 'badge--draft', 'publicada' => 'badge--pub', 'oculta' => ''][$i['status']] ?>"><?= AB_STATUS[$i['status']] ?></span><?= $i['demo'] ? ' <span class="badge">exemplo fictício</span>' : '' ?> · <?= (int) $i['cliques_wa'] ?> clique(s) no WhatsApp · <?= $i['verificada_em'] ? 'conferida em ' . date_short($i['verificada_em']) : 'nunca conferida' ?></p><?php endif; ?></div>
  <div class="row">
<?php if ($id): ?>    <a class="btn btn--ghost" href="<?= e(url('/amigos-do-bem/' . $i['slug'] . '/')) ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>Ver página</a><?php endif; ?>
    <a class="btn btn--ghost" href="<?= e(url('/admin/amigos.php')) ?>"><?= icon('arrow-left') ?>Voltar</a>
  </div>
</div>
<?php if ($errors): ?><div class="alert alert--err"><?= icon('warning-circle') ?><div><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" id="ab-form">
<?= csrf_field() ?><input type="hidden" name="do" value="save">
<div class="edit">
  <div>
    <div class="card">
      <h2>Sobre a instituição</h2>
      <div class="grid g2" style="gap:12px">
        <div class="fd"><label for="nome">Nome</label><input id="nome" name="nome" value="<?= e($f['nome']) ?>" maxlength="160" required></div>
        <div class="fd"><label for="causa">Quem atende</label><select id="causa" name="causa"><?php foreach (AB_CAUSAS as $k => $vv): ?><option value="<?= $k ?>"<?= $f['causa'] === $k ? ' selected' : '' ?>><?= e($vv) ?></option><?php endforeach; ?></select></div>
      </div>
<?php if ($id): ?>      <label class="check small" style="margin:-4px 0 12px"><input type="checkbox" name="novo_slug" value="1"> Se mudar o nome, mudar também o endereço da página (o link antigo para de funcionar)</label><?php endif; ?>
      <div class="fd"><label for="resumo">Resumo <small class="muted">(aparece no card; até 300 caracteres)</small></label><textarea id="resumo" name="resumo" rows="2" maxlength="300" placeholder="Ex.: Acolhe 32 crianças no contraturno escolar com alimentação e reforço."><?= e($f['resumo']) ?></textarea></div>
      <div class="fd"><label for="historia">História <small class="muted">(página da instituição; deixe uma linha em branco entre os parágrafos)</small></label><textarea id="historia" name="historia" rows="8"><?= e($f['historia']) ?></textarea></div>
      <div class="grid g2" style="gap:12px">
        <div class="fd"><label for="cnpj">CNPJ</label><input id="cnpj" name="cnpj" value="<?= e($f['cnpj']) ?>" maxlength="20"><label class="check small"><input type="checkbox" name="mostrar_cnpj" value="1"<?= $f['mostrar_cnpj'] ? ' checked' : '' ?>> Mostrar no site</label></div>
      </div>
    </div>

    <div class="card">
      <h2>Fotos</h2>
      <div class="grid g2" style="gap:16px">
        <div class="fd"><span class="lbl">Logo <small class="muted">(quadrada)</small></span>
          <?php if ($f['logo']): ?><img class="ab-adm-logo" src="<?= e(ab_img($f['logo'])) ?>" alt=""><label class="check small"><input type="checkbox" name="logo_del" value="1"> Remover</label><?php endif; ?>
          <input type="file" name="logo" accept="image/*"></div>
        <div class="fd"><span class="lbl">Capa <small class="muted">(horizontal, aparece no card e no topo da página)</small></span>
          <?php if ($f['capa']): ?><img class="ab-adm-capa" src="<?= e(ab_img($f['capa'])) ?>" alt=""><label class="check small"><input type="checkbox" name="capa_del" value="1"> Remover</label><?php endif; ?>
          <input type="file" name="capa" accept="image/*"></div>
      </div>
      <div class="fd"><span class="lbl">Galeria <small class="muted">(até 8 fotos)</small></span>
<?php if ($f['fotos']): ?>        <div class="ab-adm-gal"><?php foreach ($f['fotos'] as $k => $ft): ?><label><img src="<?= e(ab_img($ft['src'])) ?>" alt=""><span class="check small"><input type="checkbox" name="foto_del[]" value="<?= $k ?>"> Remover</span></label><?php endforeach; ?></div><?php endif; ?>
        <input type="file" name="fotos[]" accept="image/*" multiple>
        <p class="hint">Fotos de crianças e pessoas atendidas só com autorização.</p></div>
    </div>

    <div class="card">
      <h2>O que recebe</h2>
      <div class="ab-adm-tipos">
<?php foreach ($tipos as $tid => $t): ?>
        <div class="ab-adm-tipo<?= isset($marc[$tid]) ? ' is-on' : '' ?>">
          <label class="check"><input type="checkbox" name="tipo[<?= $tid ?>]" value="1"<?= isset($marc[$tid]) ? ' checked' : '' ?>> <span class="ab-adm-ic"><?= icon($t['icone'], 'fill') ?></span> <?= e($t['nome']) ?><?= $t['ativo'] ? '' : ' <small class="muted">(desativado)</small>' ?></label>
          <input name="tipo_obs[<?= $tid ?>]" value="<?= e($marc[$tid] ?? '') ?>" maxlength="200" placeholder="Detalhe (opcional): ex.: tamanhos 4 a 16">
        </div>
<?php endforeach; ?>
      </div>
      <div class="fd" style="margin-top:12px"><label for="nao_aceita">O que não recebe</label><input id="nao_aceita" name="nao_aceita" value="<?= e($f['nao_aceita']) ?>" maxlength="500" placeholder="Ex.: roupas rasgadas, remédios vencidos"></div>
      <div class="fd"><label for="obs_doacao">Observações para quem vai doar</label><textarea id="obs_doacao" name="obs_doacao" rows="2"><?= e($f['obs_doacao']) ?></textarea></div>
      <label class="check small"><input type="checkbox" name="busca_em_casa" value="1"<?= $f['busca_em_casa'] ? ' checked' : '' ?>> Busca a doação em casa</label>
    </div>

    <div class="card">
      <h2>Precisam agora <small class="muted">(pedidos urgentes)</small></h2>
<?php foreach ($f['necessidades'] as $n): ?>
      <label class="check small" style="display:flex;margin-bottom:8px"><input type="checkbox" name="nec_del[]" value="<?= (int) $n['id'] ?>"> Remover: <b style="margin-left:4px"><?= e($n['item']) ?></b><?= $n['quantidade'] ? ' (' . e($n['quantidade']) . ')' : '' ?><?= $n['prazo'] ? ' · ' . e(ab_prazo($n['prazo'])) : '' ?></label>
<?php endforeach; ?>
<?php for ($k = 0; $k < 3; $k++): ?>
      <div class="grid g4" style="gap:10px;align-items:end">
        <div class="fd" style="grid-column:span 2"><label>Item</label><input name="nec_item[]" maxlength="160" placeholder="Ex.: Leite integral"></div>
        <div class="fd"><label>Quantidade</label><input name="nec_qtd[]" maxlength="60" placeholder="60 litros"></div>
        <div class="fd"><label>Até quando</label><input type="date" name="nec_prazo[]"></div>
      </div>
<?php endfor; ?>
      <p class="hint">Depois do prazo, o pedido some do site sozinho. Sem prazo, fica até alguém remover.</p>
    </div>

    <div class="card">
      <h2>Onde e quando entregar</h2>
      <div class="grid g2" style="gap:12px">
        <div class="fd"><label for="endereco">Rua e número</label><input id="endereco" name="endereco" value="<?= e($f['endereco']) ?>" maxlength="200"></div>
        <div class="fd"><label for="bairro">Bairro</label><input id="bairro" name="bairro" value="<?= e($f['bairro']) ?>" maxlength="120" list="ab-bairros"><datalist id="ab-bairros"><?php foreach ((array) $bairros as $b): ?><option value="<?= e($b) ?>"><?php endforeach; ?></datalist></div>
      </div>
      <p class="small muted" style="margin:0 0 8px">Clique no mapa para marcar o local. Dá para arrastar o pino.</p>
      <div id="ab-map" class="tk-map" style="height:300px"></div>
      <p class="small muted" id="ab-st" style="margin:8px 0 12px"><?= $f['lat'] !== null && $f['lat'] !== '' ? 'Local marcado.' : 'Nenhum local marcado (a instituição não aparece no mapa).' ?></p>
      <input type="hidden" name="lat" id="lat" value="<?= e($f['lat']) ?>"><input type="hidden" name="lng" id="lng" value="<?= e($f['lng']) ?>">
      <div class="fd"><label for="horarios">Dias e horários para entregar</label><input id="horarios" name="horarios" value="<?= e($f['horarios']) ?>" maxlength="500" placeholder="Ex.: Segunda a sexta, das 8h às 17h"></div>
    </div>

    <div class="card">
      <h2>Contatos públicos e PIX</h2>
      <div class="grid g2" style="gap:12px">
        <div class="fd"><label for="whatsapp">WhatsApp</label><input id="whatsapp" name="whatsapp" value="<?= e($f['whatsapp']) ?>" maxlength="30" inputmode="tel"></div>
        <div class="fd"><label for="telefone">Telefone</label><input id="telefone" name="telefone" value="<?= e($f['telefone']) ?>" maxlength="30" inputmode="tel"></div>
        <div class="fd"><label for="email">E-mail</label><input id="email" name="email" type="email" value="<?= e($f['email']) ?>" maxlength="160"></div>
        <div class="fd"><label for="instagram">Instagram</label><input id="instagram" name="instagram" value="<?= e($f['instagram']) ?>" maxlength="160" placeholder="@usuario"></div>
        <div class="fd"><label for="facebook">Facebook</label><input id="facebook" name="facebook" value="<?= e($f['facebook']) ?>" placeholder="https://"></div>
        <div class="fd"><label for="site">Site</label><input id="site" name="site" value="<?= e($f['site']) ?>" placeholder="https://"></div>
      </div>
      <div class="grid g4" style="gap:12px">
        <div class="fd" style="grid-column:span 2"><label for="pix_chave">Chave PIX</label><input id="pix_chave" name="pix_chave" value="<?= e($f['pix_chave']) ?>" maxlength="140"></div>
        <div class="fd"><label for="pix_tipo">Tipo</label><select id="pix_tipo" name="pix_tipo"><?php foreach (['cnpj' => 'CNPJ', 'email' => 'E-mail', 'telefone' => 'Telefone', 'aleatoria' => 'Aleatória'] as $k => $vv): ?><option value="<?= $k ?>"<?= $f['pix_tipo'] === $k ? ' selected' : '' ?>><?= $vv ?></option><?php endforeach; ?></select></div>
        <div class="fd"><label for="pix_titular">Titular</label><input id="pix_titular" name="pix_titular" value="<?= e($f['pix_titular']) ?>" maxlength="160"></div>
      </div>
      <p class="hint">Só aceite PIX no nome da própria instituição (CNPJ), nunca de pessoa física. Se a instituição mudar a chave, confirme por telefone antes de salvar.</p>
    </div>
  </div>

  <div class="edit-side">
    <div class="card">
      <h2>Publicação</h2>
      <div class="fd"><label for="status">Situação</label><select id="status" name="status"><?php foreach (AB_STATUS as $k => $vv): ?><option value="<?= $k ?>"<?= $f['status'] === $k ? ' selected' : '' ?>><?= $vv ?></option><?php endforeach; ?></select></div>
      <label class="check small"><input type="checkbox" name="autorizado" value="1"<?= $f['autorizado'] ? ' checked' : '' ?>> A instituição autorizou a divulgação</label>
      <div class="fd" style="margin-top:8px"><label for="autorizado_em">Autorizou em</label><input type="date" id="autorizado_em" name="autorizado_em" value="<?= e($f['autorizado_em']) ?>"><p class="hint">Guarde o print da conversa ou o termo assinado.</p></div>
      <button class="btn btn--or btn--block" type="submit"><?= icon('check', 'bold') ?>Salvar</button>
    </div>
    <div class="card">
      <h2>Responsável <small class="muted">(só equipe)</small></h2>
      <div class="fd"><label for="resp_nome">Nome</label><input id="resp_nome" name="resp_nome" value="<?= e($f['resp_nome']) ?>" maxlength="120"></div>
      <div class="fd"><label for="resp_cargo">Cargo</label><input id="resp_cargo" name="resp_cargo" value="<?= e($f['resp_cargo']) ?>" maxlength="80"></div>
      <div class="fd"><label for="resp_telefone">Telefone</label><input id="resp_telefone" name="resp_telefone" value="<?= e($f['resp_telefone']) ?>" maxlength="30"></div>
      <div class="fd"><label for="resp_email">E-mail</label><input id="resp_email" name="resp_email" value="<?= e($f['resp_email']) ?>" maxlength="160"></div>
      <div class="fd"><label for="nota_interna">Nota interna</label><textarea id="nota_interna" name="nota_interna" rows="3"><?= e($f['nota_interna']) ?></textarea></div>
    </div>
  </div>
</div>
</form>

<?php if ($id): ?>
<div class="edit" style="margin-top:0">
  <div>
<?php if (!$i['demo']): ?>
    <div class="card" id="link">
      <h2>Link da instituição para atualizar</h2>
      <p class="small">Mande este link para o responsável. Por ele, a instituição atualiza o que está precisando, o que recebe e os horários, sem senha.</p>
      <div class="row" style="flex-wrap:nowrap"><input type="text" readonly id="ab-tok" value="<?= e(abs_url('/amigos-do-bem/atualizar/' . $i['token'] . '/')) ?>" onclick="this.select()">
        <button class="btn btn--ghost btn--sm" type="button" onclick="navigator.clipboard&&navigator.clipboard.writeText(document.getElementById('ab-tok').value);this.textContent='Copiado!'">Copiar</button></div>
<?php if ($i['resp_telefone'] || $i['whatsapp']): ?>      <p style="margin:10px 0 0"><a class="btn btn--sm" style="background:#1FAF5A" target="_blank" rel="noopener" href="https://wa.me/<?= e(ab_wa($i['resp_telefone'] ?: $i['whatsapp'])) ?>?text=<?= rawurlencode('Olá! Segue o link para vocês atualizarem o que a ' . $i['nome'] . ' está precisando no Amigos do Bem: ' . abs_url('/amigos-do-bem/atualizar/' . $i['token'] . '/')) ?>"><?= icon('whatsapp') ?>Enviar pelo WhatsApp</a></p><?php endif; ?>
      <form method="post" data-confirm="Gerar um novo link? O link atual para de funcionar." style="margin-top:10px"><?= csrf_field() ?><input type="hidden" name="do" value="token"><button class="link-btn small" type="submit">Gerar novo link</button></form>
    </div>
<?php endif; ?>
  </div>
  <div class="edit-side">
    <div class="card">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="verificar"><button class="btn btn--ghost btn--block" type="submit"><?= icon('seal-check') ?>Marcar como conferida hoje</button></form>
      <p class="hint small muted" style="margin:8px 0 14px">Use depois de confirmar endereço, contatos e PIX com a instituição. Aparece na página como "Informações conferidas pelo gabinete em…".</p>
      <form method="post" data-confirm="Excluir <?= e($i['nome']) ?>? Isso não tem volta."><?= csrf_field() ?><input type="hidden" name="do" value="delete"><button class="link-btn small" type="submit" style="color:var(--err)">Excluir instituição</button></form>
    </div>
  </div>
</div>
<?php endif; ?>
<?php admin_foot('<script>(function () {
  var el = document.getElementById("ab-map"); if (!window.L || !el) return;
  var lat = document.getElementById("lat"), lng = document.getElementById("lng"), msg = document.getElementById("ab-st");
  var rua = document.getElementById("endereco"), bairro = document.getElementById("bairro");
  var start = lat.value ? [parseFloat(lat.value), parseFloat(lng.value)] : [-22.2819, -42.5311];
  var m = L.map(el).setView(start, lat.value ? 16 : 12);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19, attribution: "&copy; OpenStreetMap" }).addTo(m);
  var pin = null;
  var place = function (ll, lookup) {
    if (!pin) { pin = L.marker(ll, { draggable: true }).addTo(m); pin.on("dragend", function () { place(pin.getLatLng(), true); }); } else pin.setLatLng(ll);
    lat.value = ll.lat.toFixed(7); lng.value = ll.lng.toFixed(7); msg.textContent = "Local marcado.";
    if (!lookup) return;
    fetch(' . json_encode(url('/api/geocode.php')) . ' + "?lat=" + ll.lat + "&lng=" + ll.lng).then(function (r) { return r.json(); }).then(function (j) {
      if (!j.ok) return; msg.textContent = "Local marcado: " + j.label;
      if (!rua.value.trim() && j.line) rua.value = j.line;
      if (!bairro.value.trim() && j.candidates && j.candidates[0]) bairro.value = j.candidates[0];
    }).catch(function () {});
  };
  if (lat.value) place(L.latLng(start), false);
  m.on("click", function (ev) { place(ev.latlng, true); });
  document.querySelectorAll(".ab-adm-tipo input[type=checkbox]").forEach(function (c) { c.addEventListener("change", function () { c.closest(".ab-adm-tipo").classList.toggle("is-on", c.checked); }); });
})();</script>');
