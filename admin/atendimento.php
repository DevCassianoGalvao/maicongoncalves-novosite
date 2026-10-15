<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require_once ROOT . '/app/notify.php';
$user = admin_guard();

$id = (int) ($_GET['id'] ?? 0);
$t = q('SELECT * FROM tickets WHERE id = ?', [$id])->fetch();
if (!$t) { flash('err', 'Atendimento não encontrado.'); redirect(url('/admin/atendimentos.php')); }
$self = url('/admin/atendimento.php?id=' . $id);
$photos = json_decode((string) $t['photos'], true) ?: [];
$after = json_decode((string) $t['after_photo'], true);
$canPublish = $t['lat'] !== null && $photos;

if (is_post()) {
    csrf_check();
    $do = in('do');
    $from = $t['status'];
    $set = function (string $status, array $extra = []) use ($t, $user, $from) {
        $cols = array_merge(['status' => $status], $extra);
        $sql = implode(', ', array_map(function ($c) { return "$c = ?"; }, array_keys($cols)));
        q("UPDATE tickets SET $sql, updated_at = NOW() WHERE id = ?", array_merge(array_values($cols), [$t['id']]));
        ticket_note((int) $t['id'], 'Situação: ' . TICKET_STATUS[$from] . ' → ' . TICKET_STATUS[$status] . '.', (int) $user['id']);
    };
    // aviso por e-mail para quem enviou (quando marcado e houver e-mail)
    $notify = function (string $kind) use ($id, $user) {
        if (empty($_POST['notify'])) return '';
        [$ok, $info] = citizen_notify($id, $kind);
        ticket_note($id, $ok ? 'E-mail de aviso enviado para a pessoa.' : 'E-mail de aviso não enviado: ' . $info . '.', (int) $user['id']);
        return $ok ? ' A pessoa foi avisada por e-mail.' : ' Não foi possível enviar o e-mail de aviso (' . $info . ').';
    };
    switch ($do) {
        case 'save':
            $cat = in('category');
            $mail = mb_strtolower(in('email'));
            q('UPDATE tickets SET category = ?, department = ?, protocol = ?, public_text = ?, anonymous = ?, address = ?, neighborhood = ?, email = ?, updated_at = NOW() WHERE id = ?', [
                isset(TICKET_CATS[$cat]) ? $cat : null, in('department') ?: null, in('protocol') ?: null, in('public_text') ?: null,
                !empty($_POST['anonymous']) ? 1 : 0, in('address') ?: null, in('neighborhood') ?: null, filter_var($mail, FILTER_VALIDATE_EMAIL) ? $mail : null, $id]);
            flash('ok', 'Dados do atendimento salvos.');
            break;
        case 'note':
            if (in('body') !== '') { ticket_note($id, in('body'), (int) $user['id']); flash('ok', 'Anotação registrada.'); }
            break;
        case 'analise':
            $set('analise');
            flash('ok', 'Atendimento em análise.');
            break;
        case 'publish':
            if (!$canPublish) { flash('err', 'Para ir ao mapa, o atendimento precisa de foto e localização.'); break; }
            if (!$t['category']) q('UPDATE tickets SET category = ? WHERE id = ?', [isset(TICKET_CATS[in('category')]) ? in('category') : 'outro', $id]);
            if ($t['public_text'] === null || $t['public_text'] === '') q('UPDATE tickets SET public_text = description WHERE id = ?', [$id]);
            $set('publicado', ['published_at' => date('Y-m-d H:i:s'), 'resolved_at' => null]);
            flash('ok', 'Aprovado! Já está no mapa e o contador começou a contar.' . $notify('publicado'));
            break;
        case 'resolve':
            $extra = ['resolved_at' => date('Y-m-d H:i:s')];
            if (!empty($_FILES['after']['tmp_name']) && $_FILES['after']['error'] === UPLOAD_ERR_OK) {
                try { $extra['after_photo'] = json_encode(ticket_photo_store($_FILES['after']['tmp_name']), JSON_UNESCAPED_SLASHES); }
                catch (Throwable $ex) { flash('err', 'Foto de depois: ' . $ex->getMessage()); }
            }
            $set('resolvido', $extra);
            flash('ok', 'Marcado como resolvido. O pino ficou verde no mapa.' . $notify('resolvido'));
            break;
        case 'unpublish':
            $set('analise', ['published_at' => null, 'resolved_at' => null]);
            flash('ok', 'Retirado do mapa. Voltou para análise.');
            break;
        case 'reopen':
            $t['status'] === 'resolvido' ? $set('publicado', ['resolved_at' => null]) : $set('analise');
            flash('ok', 'Atendimento reaberto.');
            break;
        case 'archive':
            $set('arquivado', ['published_at' => null, 'resolved_at' => null]);
            flash('ok', 'Atendimento arquivado. Não aparece no mapa.');
            break;
        case 'delete':
            if (!is_admin($user)) { flash('err', 'Só o administrador pode excluir atendimentos.'); break; }
            foreach (array_merge($photos, $after ? [$after] : []) as $p) if ($f = ticket_photo_path($p['src'])) @unlink($f);
            q('DELETE FROM tickets WHERE id = ?', [$id]);
            flash('ok', 'Atendimento ' . ticket_code($id) . ' e as fotos foram apagados.');
            redirect(url('/admin/atendimentos.php'));
    }
    redirect($self);
}

$notes = q('SELECT n.*, u.name AS author FROM ticket_notes n LEFT JOIN users u ON u.id = n.user_id WHERE n.ticket_id = ? ORDER BY n.created_at DESC, n.id DESC', [$id])->fetchAll();
$digits = preg_replace('/\D+/', '', (string) $t['phone']);
$wa = $digits ? 'https://wa.me/' . (strlen($digits) <= 11 ? '55' : '') . $digits . '?text=' . rawurlencode('Olá' . ($t['name'] ? ', ' . first_name($t['name']) : '') . '! Aqui é do gabinete do Vereador Maicon Gonçalves, sobre a sua ' . ($t['source'] !== 'contato' ? 'demanda ' : 'mensagem ') . ticket_code($id) . '.') : '';
$what = $t['source'] !== 'contato' ? (TICKET_CATS[$t['category']] ?? 'Demanda') : ($t['subject'] ?: 'Contato');
$leaflet = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"><script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>';

admin_head('Atendimento ' . ticket_code($id), 'tickets', $leaflet);
?>
<div class="top">
  <div><h1><?= e($what) ?> <span class="muted" style="font-weight:600"><?= ticket_code($id) ?></span></h1>
    <p><?= ticket_badge($t) ?> <span class="badge badge--src"><?= ['mapa' => 'Enviada pelo site', 'painel' => 'Cadastrada no painel', 'contato' => 'Formulário de contato (antigo)'][$t['source']] ?? 'Site' ?></span> · recebido em <?= date('d/m/Y \à\s H:i', strtotime($t['created_at'])) ?></p></div>
  <a class="btn btn--ghost" href="<?= e(url('/admin/atendimentos.php')) ?>"><?= icon('arrow-left') ?>Voltar</a>
</div>
<div class="edit">
  <div>
<?php if ($photos): ?>
    <div class="card">
      <h2>Fotos</h2>
      <div class="tk-photos">
<?php foreach ($photos as $p): $u = ticket_photo_url($id, $p['src']); ?>
        <a href="<?= e($u) ?>" target="_blank" rel="noopener"><img src="<?= e($u) ?>" alt="Foto enviada"></a>
<?php endforeach; ?>
      </div>
    </div>
<?php endif; ?>
    <div class="card">
      <h2><?= $t['source'] !== 'contato' ? 'Relato' : 'Mensagem' ?></h2>
      <p class="tk-text"><?= nl2br(e($t['description'])) ?></p>
    </div>
    <div class="card">
      <h2>Local</h2>
      <p style="margin:0 0 4px"><b><?= e($t['address'] ?: 'Endereço não informado') ?></b><?= $t['neighborhood'] ? ' – ' . e($t['neighborhood']) : '' ?></p>
<?php if ($t['geo_address']): ?><p class="small muted" style="margin:0 0 10px">Pela localização: <?= e($t['geo_address']) ?></p><?php endif; ?>
<?php if ($t['lat'] !== null): ?>
      <div id="tk-map" class="tk-map" data-lat="<?= e($t['lat']) ?>" data-lng="<?= e($t['lng']) ?>"></div>
      <p class="small muted" style="margin:8px 0 0"><?= $t['geo_manual'] ? 'Ponto marcado à mão no mapa.' : ($t['accuracy'] ? 'GPS do aparelho, precisão de ±' . (int) $t['accuracy'] . ' m.' : '') ?>
        <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= e($t['lat'] . ',' . $t['lng']) ?>" target="_blank" rel="noopener">Abrir no Google Maps</a></p>
<?php else: ?>
      <p class="small muted">A pessoa não compartilhou a localização.</p>
<?php endif; ?>
    </div>
    <div class="card">
      <h2>Histórico e anotações</h2>
      <form method="post" class="tk-note">
        <?= csrf_field() ?><input type="hidden" name="do" value="note">
        <textarea name="body" rows="2" placeholder="Ex.: Liguei para a moradora, buraco confirmado. Ofício enviado à Secretaria de Obras." required></textarea>
        <button class="btn btn--sm" type="submit">Anotar</button>
      </form>
      <ol class="tk-log">
<?php foreach ($notes as $n): ?>
        <li><span class="small muted"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?> · <?= e($n['author'] ?: 'Sistema') ?></span><p><?= nl2br(e($n['body'])) ?></p></li>
<?php endforeach; ?>
      </ol>
    </div>
  </div>

  <div class="edit-side">
    <div class="card">
      <h2>Situação</h2>
<?php if ($t['status'] === 'publicado'): ?>
      <div class="tk-timer"><?= icon('timer') ?><div><small>Aguardando a Prefeitura há</small><b><?= human_span(time() - strtotime($t['published_at'])) ?></b><small>desde <?= date('d/m/Y H:i', strtotime($t['published_at'])) ?></small></div></div>
<?php elseif ($t['status'] === 'resolvido'): ?>
      <div class="tk-timer tk-timer--ok"><?= icon('check-circle') ?><div><small>Resolvido em</small><b><?= human_span(strtotime($t['resolved_at']) - strtotime($t['published_at'])) ?></b><small>em <?= date('d/m/Y', strtotime($t['resolved_at'])) ?></small></div></div>
<?php if ($after): ?><img class="tk-after" src="<?= e(ticket_photo_url($id, $after['src'])) ?>" alt="Foto depois"><?php endif; ?>
<?php endif; ?>
      <div class="tk-acts">
<?php if (in_array($t['status'], ['novo', 'analise'], true)): ?>
<?php if ($t['status'] === 'novo'): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="analise"><button class="btn btn--ghost btn--block" type="submit"><?= icon('magnifying-glass') ?>Começar análise</button></form>
<?php endif; ?>
        <form method="post" data-confirm="Aprovar e publicar no mapa? O contador começa a contar agora."><?= csrf_field() ?><input type="hidden" name="do" value="publish">
          <?php if ($t['email']): ?><label class="check small"><input type="checkbox" name="notify" value="1" checked> Avisar <?= e($t['email']) ?> por e-mail</label><?php endif; ?>
          <button class="btn btn--or btn--block" type="submit"<?= $canPublish ? '' : ' disabled' ?>><?= icon('map-pin') ?>Aprovar e publicar no mapa</button></form>
<?php if (!$canPublish): ?><p class="hint small muted" style="margin:0">Só vai ao mapa com foto e localização.</p><?php else: ?><p class="hint small muted" style="margin:0">Use depois de enviar o pedido à Prefeitura. O contador começa a contar no momento da aprovação.</p><?php endif; ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="archive"><button class="link-btn small" type="submit">Arquivar (não vai ao mapa)</button></form>
<?php elseif ($t['status'] === 'publicado'): ?>
        <form method="post" enctype="multipart/form-data" class="tk-resolve" data-confirm="Marcar como resolvido? O contador para e o pino fica verde.">
          <?= csrf_field() ?><input type="hidden" name="do" value="resolve">
          <label class="drop"><input type="file" name="after" accept="image/*"><span>Foto de depois (opcional)</span></label>
          <?php if ($t['email']): ?><label class="check small"><input type="checkbox" name="notify" value="1" checked> Avisar <?= e($t['email']) ?> por e-mail</label><?php endif; ?>
          <button class="btn btn--block" type="submit" style="background:#16A34A"><?= icon('check-circle') ?>Marcar como resolvido</button>
        </form>
        <form method="post" data-confirm="Tirar do mapa e voltar para análise? O contador será zerado."><?= csrf_field() ?><input type="hidden" name="do" value="unpublish"><button class="link-btn small" type="submit">Tirar do mapa</button></form>
<?php else: ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="reopen"><button class="btn btn--ghost btn--block" type="submit"><?= icon('arrow-counter-clockwise') ?>Reabrir</button></form>
<?php endif; ?>
      </div>
    </div>
    <div class="card">
      <h2>Quem enviou</h2>
      <p style="margin:0 0 4px"><b><?= e($t['name'] ?: 'Sem nome') ?></b><?= $t['anonymous'] ? ' <span class="badge">não quer o nome no mapa</span>' : '' ?></p>
      <p class="small muted" style="margin:0 0 12px"><?= e($t['phone']) ?><?= $t['birth'] ? ' · nasc. ' . e($t['birth']) : '' ?><?php if ($t['email']): ?><br><a href="mailto:<?= e($t['email']) ?>"><?= e($t['email']) ?></a><?php endif; ?></p>
<?php if ($wa): ?><a class="btn btn--block" style="background:#1FAF5A" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?>Conversar no WhatsApp</a><?php endif; ?>
    </div>
    <form method="post" class="card">
      <?= csrf_field() ?><input type="hidden" name="do" value="save">
      <h2>Encaminhamento</h2>
      <div class="fd"><label for="category">Tipo do problema</label><select id="category" name="category"><option value="">—</option>
<?php foreach (TICKET_CATS as $k => $v): ?><option value="<?= $k ?>"<?= $t['category'] === $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
      <div class="fd"><label for="department">Secretaria / órgão</label><input type="text" id="department" name="department" value="<?= e($t['department']) ?>" placeholder="Ex.: Secretaria de Obras"></div>
      <div class="fd"><label for="protocol">Nº do ofício ou protocolo</label><input type="text" id="protocol" name="protocol" value="<?= e($t['protocol']) ?>"><p class="hint">Uso interno, não aparece no site.</p></div>
      <div class="fd"><label for="email">E-mail da pessoa</label><input type="email" id="email" name="email" value="<?= e($t['email'] ?? '') ?>"><p class="hint">Recebe os avisos quando for à Prefeitura e quando for resolvida.</p></div>
      <div class="fd"><label for="address">Rua e número</label><input type="text" id="address" name="address" value="<?= e($t['address']) ?>"></div>
      <div class="fd"><label for="neighborhood">Bairro</label><input type="text" id="neighborhood" name="neighborhood" value="<?= e($t['neighborhood']) ?>"></div>
      <div class="fd"><label for="public_text">Texto que aparece no mapa</label><textarea id="public_text" name="public_text" rows="4" placeholder="Se vazio, usa o relato da pessoa."><?= e($t['public_text'] ?? '') ?></textarea><p class="hint">Revise e tire dados pessoais antes de publicar.</p></div>
      <label class="check small" style="margin-bottom:14px"><input type="checkbox" name="anonymous" value="1"<?= $t['anonymous'] ? ' checked' : '' ?>> Não mostrar o nome no mapa</label>
      <button class="btn btn--block" type="submit"><?= icon('check', 'bold') ?>Salvar</button>
    </form>
<?php if (is_admin($user)): ?>
    <form method="post" class="card" data-confirm="Apagar este atendimento e as fotos para sempre? Use quando a pessoa pedir a exclusão dos dados.">
      <?= csrf_field() ?><input type="hidden" name="do" value="delete">
      <button class="btn btn--danger btn--block" type="submit">Excluir atendimento</button>
    </form>
<?php endif; ?>
  </div>
</div>
<?php ob_start(); ?>
<script>
(function () {
  var el = document.getElementById('tk-map');
  if (!el || !window.L) return;
  var ll = [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)];
  var m = L.map(el, { scrollWheelZoom: false }).setView(ll, 17);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(m);
  L.marker(ll).addTo(m);
})();
</script>
<?php admin_foot(ob_get_clean());
