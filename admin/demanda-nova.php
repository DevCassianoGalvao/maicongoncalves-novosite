<?php
/**
 * Cadastro de demanda pelo painel (inclusive antigas, com datas retroativas).
 * Não envia e-mails: serve para registrar o que o gabinete já vinha acompanhando.
 */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
$user = admin_guard();
$bairros = require ROOT . '/app/bairros.php';

$v = ['category' => '', 'description' => '', 'public_text' => '', 'name' => '', 'phone' => '', 'email' => '', 'anonymous' => '1',
    'address' => '', 'neighborhood' => '', 'lat' => '', 'lng' => '', 'status' => 'publicado', 'department' => '', 'protocol' => '',
    'created_at' => date('Y-m-d'), 'published_at' => date('Y-m-d'), 'resolved_at' => ''];
$errors = [];

/** "2025-03-10" ou "2025-03-10T14:30" → DATETIME, ou null. */
function form_date(string $s, string $time = '09:00:00'): ?string
{
    if ($s === '') return null;
    $ts = strtotime(strlen($s) <= 10 ? $s . ' ' . $time : $s);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}

if (is_post()) {
    csrf_check();
    $max = ['category' => 20, 'description' => 3000, 'public_text' => 3000, 'name' => 120, 'phone' => 30, 'email' => 190, 'address' => 255,
        'neighborhood' => 120, 'lat' => 20, 'lng' => 20, 'status' => 12, 'department' => 160, 'protocol' => 80, 'created_at' => 16, 'published_at' => 16, 'resolved_at' => 16];
    foreach ($v as $k => $_) if ($k !== 'anonymous') $v[$k] = in($k, '', $max[$k] ?? 255);
    $v['anonymous'] = !empty($_POST['anonymous']) ? '1' : '';
    $v['email'] = mb_strtolower($v['email']);
    if (!isset(TICKET_STATUS[$v['status']]) || $v['status'] === 'novo' || $v['status'] === 'arquivado') $v['status'] = 'analise';
    $lat = filter_var($v['lat'], FILTER_VALIDATE_FLOAT);
    $lng = filter_var($v['lng'], FILTER_VALIDATE_FLOAT);
    $created = form_date($v['created_at']) ?? date('Y-m-d H:i:s');
    $published = in_array($v['status'], ['publicado', 'resolvido'], true) ? form_date($v['published_at'], '10:00:00') : null;
    $resolved = $v['status'] === 'resolvido' ? form_date($v['resolved_at'], '17:00:00') : null;

    if (!isset(ticket_cats()[$v['category']])) $errors[] = 'Escolha o tipo do problema.';
    if ($v['description'] === '') $errors[] = 'Descreva a demanda.';
    if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail da pessoa inválido.';
    if (strtotime($created) > time() + 3600) $errors[] = 'A data em que a demanda chegou não pode ser no futuro.';
    if (in_array($v['status'], ['publicado', 'resolvido'], true)) {
        if ($lat === false || $lng === false || abs($lat + 22.28) > 0.6 || abs($lng + 42.53) > 0.6) $errors[] = 'Para ir ao mapa, marque o local no mapa (dentro de Nova Friburgo).';
        if (!$published) $errors[] = 'Informe a data em que a demanda foi enviada à Prefeitura.';
        elseif (strtotime($published) < strtotime(substr($created, 0, 10))) $errors[] = 'A data de envio à Prefeitura não pode ser antes da data em que a demanda chegou.';
        elseif (strtotime($published) > time() + 3600) $errors[] = 'A data de envio à Prefeitura não pode ser no futuro.';
    }
    if ($v['status'] === 'resolvido') {
        if (!$resolved) $errors[] = 'Informe a data em que foi resolvida.';
        elseif ($published && strtotime($resolved) < strtotime($published)) $errors[] = 'A data de resolução não pode ser antes do envio à Prefeitura.';
        elseif (strtotime($resolved) > time() + 3600) $errors[] = 'A data de resolução não pode ser no futuro.';
    }

    $photos = [];
    $files = $_FILES['fotos'] ?? null;
    if (!$errors && $files && is_array($files['name'])) {
        foreach ($files['name'] as $i => $name) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            if (count($photos) >= 3) break;
            if ($files['error'][$i] !== UPLOAD_ERR_OK) { $errors[] = 'Foto "' . $name . '": falha no envio.'; continue; }
            try { $photos[] = ticket_photo_store($files['tmp_name'][$i]); } catch (Throwable $ex) { $errors[] = 'Foto "' . $name . '": ' . $ex->getMessage(); }
        }
    }
    if (!$errors && in_array($v['status'], ['publicado', 'resolvido'], true) && !$photos) $errors[] = 'Para ir ao mapa, envie pelo menos uma foto.';

    if ($errors) {
        foreach ($photos as $p) if ($f = ticket_photo_path($p['src'])) @unlink($f);
    } else {
        $after = null;
        if ($v['status'] === 'resolvido' && !empty($_FILES['after']['tmp_name']) && $_FILES['after']['error'] === UPLOAD_ERR_OK) {
            try { $after = json_encode(ticket_photo_store($_FILES['after']['tmp_name']), JSON_UNESCAPED_SLASHES); } catch (Throwable $ex) {}
        }
        $id = ticket_create([
            'source' => 'painel', 'category' => $v['category'], 'name' => $v['name'], 'phone' => $v['phone'], 'email' => $v['email'],
            'anonymous' => $v['anonymous'] === '1', 'description' => $v['description'], 'address' => $v['address'], 'neighborhood' => $v['neighborhood'],
            'lat' => $lat !== false ? round($lat, 7) : null, 'lng' => $lng !== false ? round($lng, 7) : null, 'geo_manual' => true,
            'photos' => $photos, 'consent' => true,
        ]);
        q('UPDATE tickets SET status = ?, public_text = ?, department = ?, protocol = ?, after_photo = ?, published_at = ?, resolved_at = ?, created_at = ?, created_by = ?, ip = NULL WHERE id = ?', [
            $v['status'], $v['public_text'] !== '' ? $v['public_text'] : ($published ? $v['description'] : null), $v['department'] ?: null, $v['protocol'] ?: null,
            $after, $published, $resolved, $created, (int) $user['id'], $id]);
        if (!empty($_POST['news']) && $v['email'] !== '') { q('UPDATE tickets SET news_optin = 1 WHERE id = ?', [$id]); subscriber_optin($v['email'], $v['name'], 'painel'); }
        ticket_note($id, 'Cadastrada pelo painel' . (substr($created, 0, 10) < date('Y-m-d') ? ', com data de ' . date('d/m/Y', strtotime($created)) : '') . '. Situação: ' . TICKET_STATUS[$v['status']] . '.', (int) $user['id']);
        flash('ok', 'Demanda ' . ticket_code($id) . ' cadastrada' . ($published ? ' e já está no mapa.' : '.'));
        redirect(url(in('again') ? '/admin/demanda-nova.php' : '/admin/atendimento.php?id=' . $id));
    }
}

$leaflet = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"><script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>';
admin_head('Cadastrar demanda', 'tnew', $leaflet);
?>
<div class="top">
  <div><h1>Cadastrar demanda</h1><p>Para registrar demandas que chegaram por outros canais ou que o gabinete já acompanha, com as datas reais.</p></div>
  <a class="btn btn--ghost" href="<?= e(url('/admin/atendimentos.php')) ?>"><?= icon('arrow-left') ?>Atendimentos</a>
</div>
<?php foreach ($errors as $err): ?>
<div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($err) ?></span></div>
<?php endforeach; ?>
<form method="post" enctype="multipart/form-data" id="dn">
  <?= csrf_field() ?>
  <div class="edit">
    <div>
      <div class="card">
        <h2>A demanda</h2>
        <div class="fd"><label for="category">Tipo do problema</label><select id="category" name="category" required><option value="">Escolha</option>
<?php foreach (ticket_cats() as $k => $label): ?><option value="<?= $k ?>"<?= $v['category'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="fd"><label for="description">Relato</label><textarea id="description" name="description" rows="4" required placeholder="O que está acontecendo, desde quando, o que já foi feito."><?= e($v['description']) ?></textarea></div>
        <div class="fd"><label for="public_text">Texto que aparece no mapa <small class="muted">(opcional)</small></label><textarea id="public_text" name="public_text" rows="3" placeholder="Se vazio, usa o relato. Tire dados pessoais."><?= e($v['public_text']) ?></textarea></div>
        <div class="fd"><span class="lbl">Fotos <small class="muted">(até 3; obrigatório para ir ao mapa)</small></span>
          <label class="drop"><input type="file" name="fotos[]" accept="image/*" multiple id="fotos"><span id="fotosTxt">Escolher fotos</span></label></div>
      </div>
      <div class="card">
        <h2>Local</h2>
        <p class="small muted" style="margin:-6px 0 10px">Clique no mapa para marcar o ponto exato. Dá para arrastar o pino.</p>
        <div id="dn-map" class="tk-map" style="height:340px"></div>
        <p class="small muted" id="dn-st" style="margin:8px 0 12px"><?= $v['lat'] !== '' ? 'Local marcado.' : 'Nenhum local marcado ainda.' ?></p>
        <input type="hidden" name="lat" id="lat" value="<?= e($v['lat']) ?>"><input type="hidden" name="lng" id="lng" value="<?= e($v['lng']) ?>">
        <div class="grid g2">
          <div class="fd"><label for="address">Rua e número</label><input type="text" id="address" name="address" value="<?= e($v['address']) ?>" maxlength="255"></div>
          <div class="fd"><label for="neighborhood">Bairro / localidade</label><select id="neighborhood" name="neighborhood"><option value="">—</option>
<?php foreach ($bairros as $b): ?><option<?= $v['neighborhood'] === $b ? ' selected' : '' ?>><?= e($b) ?></option><?php endforeach; ?></select></div>
        </div>
      </div>
      <div class="card">
        <h2>Quem pediu <small class="muted" style="font-weight:600">(opcional)</small></h2>
        <div class="grid g2">
          <div class="fd"><label for="name">Nome</label><input type="text" id="name" name="name" value="<?= e($v['name']) ?>" maxlength="120"></div>
          <div class="fd"><label for="phone">WhatsApp</label><input type="tel" id="phone" name="phone" value="<?= e($v['phone']) ?>" maxlength="30"></div>
        </div>
        <div class="fd"><label for="email">E-mail</label><input type="email" id="email" name="email" value="<?= e($v['email']) ?>" maxlength="190"><p class="hint">Com e-mail, a pessoa recebe aviso quando a demanda for resolvida (você escolhe na hora de resolver).</p></div>
        <label class="check small"><input type="checkbox" name="anonymous" value="1"<?= $v['anonymous'] ? ' checked' : '' ?>> Não mostrar o nome no mapa</label>
        <label class="check small" style="margin-top:8px"><input type="checkbox" name="news" value="1"<?= !empty($_POST['news']) ? ' checked' : '' ?>> A pessoa autorizou receber novidades por e-mail (novos artigos)</label>
      </div>
    </div>

    <div class="edit-side">
      <div class="card">
        <h2>Situação e datas</h2>
        <div class="fd"><label for="created_at">Chegou ao gabinete em</label><input type="date" id="created_at" name="created_at" value="<?= e(substr($v['created_at'], 0, 10)) ?>" max="<?= date('Y-m-d') ?>"></div>
        <div class="fd"><label for="status">Situação</label><select id="status" name="status">
          <option value="analise"<?= $v['status'] === 'analise' ? ' selected' : '' ?>>Em análise (não vai ao mapa)</option>
          <option value="publicado"<?= $v['status'] === 'publicado' ? ' selected' : '' ?>>Na Prefeitura (no mapa)</option>
          <option value="resolvido"<?= $v['status'] === 'resolvido' ? ' selected' : '' ?>>Resolvida (pino verde)</option>
        </select></div>
        <div class="fd" data-show="publicado resolvido"><label for="published_at">Enviada à Prefeitura em</label><input type="date" id="published_at" name="published_at" value="<?= e(substr($v['published_at'], 0, 10)) ?>" max="<?= date('Y-m-d') ?>"><p class="hint">O contador do mapa conta a partir desta data.</p></div>
        <div class="fd" data-show="resolvido"><label for="resolved_at">Resolvida em</label><input type="date" id="resolved_at" name="resolved_at" value="<?= e(substr($v['resolved_at'], 0, 10)) ?>" max="<?= date('Y-m-d') ?>"></div>
        <div class="fd" data-show="resolvido"><span class="lbl">Foto de depois <small class="muted">(opcional)</small></span><label class="drop"><input type="file" name="after" accept="image/*"><span>Escolher foto</span></label></div>
        <div class="save-bar" style="flex-direction:column;align-items:stretch">
          <button class="btn btn--or" type="submit"><?= icon('check', 'bold') ?>Cadastrar demanda</button>
          <button class="btn btn--ghost" type="submit" name="again" value="1">Cadastrar e fazer outra</button>
        </div>
        <p class="hint small muted" style="margin:10px 0 0">Cadastros pelo painel não enviam e-mail na hora.</p>
      </div>
      <div class="card">
        <h2>Encaminhamento</h2>
        <div class="fd"><label for="department">Secretaria / órgão</label><input type="text" id="department" name="department" value="<?= e($v['department']) ?>" placeholder="Ex.: Secretaria de Obras"></div>
        <div class="fd"><label for="protocol">Nº do ofício ou protocolo</label><input type="text" id="protocol" name="protocol" value="<?= e($v['protocol']) ?>"><p class="hint">Uso interno, não aparece no site.</p></div>
      </div>
    </div>
  </div>
</form>
<?php ob_start(); ?>
<script>
(function () {
  var st = document.getElementById('status');
  var sync = function () { document.querySelectorAll('[data-show]').forEach(function (el) { el.hidden = el.dataset.show.split(' ').indexOf(st.value) < 0; }); };
  st.addEventListener('change', sync); sync();
  var fi = document.getElementById('fotos'), ft = document.getElementById('fotosTxt');
  fi.addEventListener('change', function () { ft.textContent = fi.files.length ? fi.files.length + (fi.files.length > 1 ? ' fotos escolhidas' : ' foto escolhida') + (fi.files.length > 3 ? ' (só as 3 primeiras serão usadas)' : '') : 'Escolher fotos'; });

  var el = document.getElementById('dn-map'); if (!window.L || !el) return;
  var lat = document.getElementById('lat'), lng = document.getElementById('lng'), msg = document.getElementById('dn-st');
  var rua = document.getElementById('address'), bairro = document.getElementById('neighborhood');
  var start = lat.value ? [parseFloat(lat.value), parseFloat(lng.value)] : [-22.2819, -42.5311];
  var m = L.map(el).setView(start, lat.value ? 17 : 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(m);
  var pin = null;
  var norm = function (s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim(); };
  var place = function (ll, lookup) {
    if (!pin) { pin = L.marker(ll, { draggable: true }).addTo(m); pin.on('dragend', function () { place(pin.getLatLng(), true); }); }
    else pin.setLatLng(ll);
    lat.value = ll.lat.toFixed(7); lng.value = ll.lng.toFixed(7);
    msg.textContent = 'Local marcado.';
    if (!lookup) return;
    fetch(<?= json_encode(url('/api/geocode.php')) ?> + '?lat=' + ll.lat + '&lng=' + ll.lng).then(function (r) { return r.json(); }).then(function (j) {
      if (!j.ok) return;
      msg.textContent = 'Local marcado: ' + j.label;
      if (!rua.value.trim() && j.line) rua.value = j.line;
      if (!bairro.value) (j.candidates || []).some(function (c) {
        var o = Array.prototype.find.call(bairro.options, function (o) { return o.value && norm(o.value) === norm(c); });
        if (o) { bairro.value = o.value; return true; }
      });
    }).catch(function () {});
  };
  if (lat.value) place(L.latLng(start), false);
  m.on('click', function (ev) { place(ev.latlng, true); });
})();
</script>
<?php admin_foot(ob_get_clean());
