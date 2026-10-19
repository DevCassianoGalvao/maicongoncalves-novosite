<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require_once ROOT . '/app/images.php';
require_once ROOT . '/app/newsletter.php';
$user = admin_guard(true);

const RESERVED_SLUGS = ['minha-historia', 'fl360', 'descobrindo-talentos', 'contato', 'demandas', 'friburgo-no-mapa', 'friburgonomapa', 'radarfriburgo', 'radar', 'privacidade', 'descadastrar', 'noticias', 'admin', 'install', 'uploads', 'img', 'api', 'app', 'storage', 'category', 'feed', 'sitemap', 'peneira', 'blog', 'home', 'index'];

function unique_slug(string $slug, int $id): string
{
    $base = $slug !== '' ? $slug : 'artigo';
    $slug = in_array($base, RESERVED_SLUGS, true) ? $base . '-1' : $base;
    $n = 1;
    while (q('SELECT COUNT(*) FROM posts WHERE slug = ? AND id <> ?', [$slug, $id])->fetchColumn()) $slug = $base . '-' . (++$n);
    return $slug;
}

/** Arquivos enviados num campo múltiplo, normalizados. */
function uploaded_files(string $field): array
{
    $f = $_FILES[$field] ?? null;
    if (!$f) return [];
    $out = [];
    foreach ((array) $f['name'] as $i => $name) {
        $err = is_array($f['error']) ? $f['error'][$i] : $f['error'];
        if ($err === UPLOAD_ERR_NO_FILE) continue;
        $out[] = ['name' => $name, 'tmp' => is_array($f['tmp_name']) ? $f['tmp_name'][$i] : $f['tmp_name'], 'error' => $err];
    }
    return $out;
}
function upload_error(int $code): string
{
    return $code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE ? 'arquivo maior que o limite da hospedagem (' . ini_get('upload_max_filesize') . ')' : 'falha no envio (código ' . $code . ')';
}

$id = (int) ($_GET['id'] ?? 0);
$post = $id ? q('SELECT * FROM posts WHERE id = ?', [$id])->fetch() : null;
if ($id && !$post) { flash('err', 'Artigo não encontrado.'); redirect(url('/admin/posts.php')); }
$post = $post ?: ['id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'cover' => '', 'cover_w' => null, 'cover_h' => null, 'cover_alt' => '',
    'gallery' => '', 'category_id' => null, 'status' => 'draft', 'published_at' => null, 'meta_description' => ''];
$gallery = json_decode((string) $post['gallery'], true) ?: [];
$errors = [];

if (is_post()) {
    csrf_check();
    $publish = in('do') !== 'draft';
    $post['title'] = in('title', '', 255);
    $post['slug'] = slugify(in('slug') !== '' ? in('slug') : $post['title']);
    $post['excerpt'] = in('excerpt', '', 1000);
    $post['meta_description'] = in('meta_description', '', 300);
    $post['content'] = html_sanitize((string) ($_POST['content'] ?? ''));
    $post['cover_alt'] = in('cover_alt', '', 255);
    $post['category_id'] = (int) in('category_id') ?: null;
    $when = in('published_at');
    $post['published_at'] = $when !== '' && strtotime($when) ? date('Y-m-d H:i:s', strtotime($when)) : ($post['published_at'] ?: ($publish ? date('Y-m-d H:i:s') : null));
    $post['status'] = $publish ? 'published' : 'draft';

    if ($post['title'] === '') $errors[] = 'Escreva o título do artigo.';
    if ($publish && trim(strip_tags($post['content'], '<img><iframe>')) === '') $errors[] = 'O artigo está sem conteúdo.';

    $newCat = in('new_category', '', 80);
    if (!$errors && $newCat !== '') {
        $cslug = slugify($newCat);
        $found = q('SELECT id FROM categories WHERE slug = ?', [$cslug])->fetchColumn();
        if (!$found) { q('INSERT INTO categories (name, slug, created_at) VALUES (?, ?, NOW())', [$newCat, $cslug]); $found = db()->lastInsertId(); }
        $post['category_id'] = (int) $found;
    }

    if (!$errors) {
        // capa
        $oldCover = $post['cover'];
        if (!empty($_POST['cover_remove'])) { $post['cover'] = null; $post['cover_w'] = $post['cover_h'] = null; }
        foreach (uploaded_files('cover') as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) { $errors[] = 'Capa: ' . upload_error($f['error']) . '.'; break; }
            try { $img = img_store_upload($f['tmp']); $post['cover'] = $img['src']; $post['cover_w'] = $img['w']; $post['cover_h'] = $img['h']; }
            catch (Throwable $ex) { $errors[] = 'Capa: ' . $ex->getMessage(); }
        }
        if ($oldCover && $oldCover !== $post['cover']) delete_upload($oldCover);

        // galeria
        $keep = array_map('strval', (array) ($_POST['gallery_keep'] ?? []));
        $new = [];
        foreach ($gallery as $g) { if (in_array($g['src'], $keep, true)) $new[] = $g; else delete_upload($g['src']); }
        foreach (uploaded_files('gallery_add') as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) { $errors[] = 'Galeria (' . $f['name'] . '): ' . upload_error($f['error']) . '.'; continue; }
            try { $new[] = img_store_upload($f['tmp']); } catch (Throwable $ex) { $errors[] = 'Galeria (' . $f['name'] . '): ' . $ex->getMessage(); }
        }
        $gallery = $new;
        $post['gallery'] = $gallery ? json_encode($gallery, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        $post['slug'] = unique_slug($post['slug'], (int) $post['id']);
        $vals = [$post['title'], $post['slug'], $post['excerpt'], $post['content'], $post['cover'] ?: null, $post['cover_w'], $post['cover_h'], $post['cover_alt'],
            $post['gallery'], $post['category_id'], $post['status'], $post['published_at'], $post['meta_description']];
        if ($post['id']) {
            q('UPDATE posts SET title=?, slug=?, excerpt=?, content=?, cover=?, cover_w=?, cover_h=?, cover_alt=?, gallery=?, category_id=?, status=?, published_at=?, meta_description=?, updated_at=NOW() WHERE id=?',
                array_merge($vals, [$post['id']]));
        } else {
            q('INSERT INTO posts (title, slug, excerpt, content, cover, cover_w, cover_h, cover_alt, gallery, category_id, status, published_at, meta_description, author_id, created_at, updated_at)
               VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())', array_merge($vals, [$user['id']]));
            $post['id'] = (int) db()->lastInsertId();
        }
        $msg = $publish ? (strtotime($post['published_at']) > time() ? 'Artigo agendado para ' . date('d/m/Y \à\s H:i', strtotime($post['published_at'])) . '.' : 'Artigo publicado.') : 'Rascunho salvo.';
        // aviso por e-mail para quem aceitou receber novidades (uma vez por artigo, só quando já está no ar)
        if ($publish && !empty($_POST['notify_subs']) && empty($post['notified_at'])) {
            if (strtotime($post['published_at']) > time()) {
                $msg .= ' O aviso por e-mail não foi enviado porque o artigo ainda não está no ar: depois da data, abra o artigo e clique em Atualizar com a caixa marcada.';
            } else {
                [$sentN, $failN, $err] = post_notify($post);
                if ($sentN > 0) { q('UPDATE posts SET notified_at = NOW(), notified_count = ? WHERE id = ?', [$sentN, $post['id']]); $msg .= ' Aviso enviado por e-mail para ' . $sentN . ($sentN === 1 ? ' pessoa.' : ' pessoas.'); }
                if ($failN > 0 || ($sentN === 0 && $err !== '')) $errors[] = 'Aviso por e-mail: ' . ($failN ? $failN . ' não enviados. ' : '') . $err;
            }
        }
        if ($errors) flash('err', 'Salvo, mas: ' . implode(' ', $errors)); else flash('ok', $msg);
        redirect(url('/admin/post.php?id=' . $post['id']));
    }
}

$categories = q('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$isNew = !$post['id'];
$isPub = $post['status'] === 'published';
$viewUrl = $post['id'] ? url('/' . $post['slug'] . '/') . ($isPub ? '' : '?preview=1') : '';
$base = B;
$tiny = '<script src="' . e(url('/admin/vendor/tinymce/tinymce.min.js')) . '"></script>';

admin_head($isNew ? 'Novo artigo' : 'Editar artigo', $isNew ? 'new' : 'posts', $tiny);
?>
<div class="top">
  <div><h1><?= $isNew ? 'Novo artigo' : 'Editar artigo' ?></h1><?php if (!$isNew): ?><p><?= status_badge($post) ?></p><?php endif; ?></div>
<?php if ($viewUrl): ?>
  <a class="btn btn--ghost" href="<?= e($viewUrl) ?>" target="_blank" rel="noopener"><?= icon('eye') ?><?= $isPub ? 'Ver no site' : 'Pré-visualizar' ?></a>
<?php endif; ?>
</div>
<?php foreach ($errors as $err): ?>
<div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($err) ?></span></div>
<?php endforeach; ?>
<form method="post" enctype="multipart/form-data" id="pf">
  <?= csrf_field() ?>
  <div class="edit">
    <div>
      <div class="card">
        <div class="fd"><label class="sr" for="title">Título</label><input class="input-title" type="text" id="title" name="title" value="<?= e($post['title']) ?>" placeholder="Título do artigo" required></div>
        <div class="slug fd"><span>Endereço: <?= e(abs_url('/')) ?></span><input type="text" id="slug" name="slug" value="<?= e($post['slug']) ?>" placeholder="gerado-pelo-titulo" data-auto="<?= $isNew ? '1' : '' ?>"><span>/</span></div>
        <div class="fd"><label for="excerpt">Linha fina</label><textarea id="excerpt" name="excerpt" rows="2" placeholder="Uma frase que resume a notícia. Aparece abaixo do título."><?= e($post['excerpt']) ?></textarea></div>
        <div class="fd"><label for="content" class="sr">Conteúdo</label><textarea id="content" name="content"><?= e($post['content']) ?></textarea></div>
      </div>
      <div class="card">
        <h2>Galeria de fotos</h2>
        <p class="muted small" style="margin:-6px 0 12px">Aparece no fim do artigo; clicando, a foto abre ampliada.</p>
        <div class="gal-ed" id="galEd">
<?php foreach ($gallery as $g): ?>
          <figure><img src="<?= e(url('/' . $g['src'])) ?>" alt=""><input type="hidden" name="gallery_keep[]" value="<?= e($g['src']) ?>"><button type="button" aria-label="Remover foto" data-rm><?= icon('x', 'bold') ?></button></figure>
<?php endforeach; ?>
        </div>
        <label class="drop"><input type="file" name="gallery_add[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple id="galAdd"><span id="galTxt">Adicionar fotos (pode escolher várias)</span></label>
      </div>
    </div>

    <div class="edit-side">
      <div class="card">
        <div class="fd"><label for="published_at">Data de publicação</label><input type="datetime-local" id="published_at" name="published_at" value="<?= $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>">
          <p class="hint">Vazio = agora. Uma data futura agenda o artigo.</p></div>
<?php if (!empty($post['notified_at'])): ?>
        <p class="small muted" style="margin:0 0 12px"><?= icon('check-circle') ?> Aviso por e-mail enviado em <?= date('d/m/Y H:i', strtotime($post['notified_at'])) ?> para <?= (int) $post['notified_count'] ?> pessoa(s).</p>
<?php else: $nSubs = subscribers_count(); ?>
        <label class="check small" style="margin:0 0 12px"><input type="checkbox" name="notify_subs" value="1"<?= !$isPub && $nSubs ? ' checked' : '' ?><?= $nSubs ? '' : ' disabled' ?>> Avisar por e-mail quem aceita novidades (<?= $nSubs ?> <?= $nSubs === 1 ? 'pessoa' : 'pessoas' ?>)</label>
<?php endif; ?>
        <div class="save-bar">
          <button class="btn btn--or" type="submit" name="do" value="publish"><?= icon('paper-plane-tilt') ?><?= $isPub ? 'Atualizar' : 'Publicar' ?></button>
          <button class="btn btn--ghost" type="submit" name="do" value="draft" formnovalidate><?= $isPub ? 'Voltar a rascunho' : 'Salvar rascunho' ?></button>
        </div>
      </div>
      <div class="card">
        <div class="fd"><label for="category_id">Categoria</label>
          <select id="category_id" name="category_id">
            <option value="">Sem categoria</option>
<?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>"<?= (int) $post['category_id'] === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
<?php endforeach; ?>
          </select></div>
        <div class="fd"><label for="new_category">Ou crie uma nova</label><input type="text" id="new_category" name="new_category" placeholder="Nome da nova categoria"></div>
      </div>
      <div class="card">
        <div class="lbl" style="margin-bottom:8px">Imagem de capa</div>
        <div class="cover-prev" id="coverPrev"<?= $post['cover'] ? '' : ' hidden' ?>><img src="<?= $post['cover'] ? e(url('/' . $post['cover'])) : '' ?>" alt=""></div>
        <label class="drop"><input type="file" name="cover" accept="image/jpeg,image/png,image/webp,image/gif" id="coverIn"><span><?= $post['cover'] ? 'Trocar imagem' : 'Escolher imagem' ?></span></label>
<?php if ($post['cover']): ?>
        <label class="check small" style="margin-top:10px"><input type="checkbox" name="cover_remove" value="1"> Remover a capa</label>
<?php endif; ?>
        <div class="fd" style="margin-top:12px"><label for="cover_alt">Descrição da imagem</label><input type="text" id="cover_alt" name="cover_alt" value="<?= e($post['cover_alt']) ?>" placeholder="Ex.: Vereador discursa na tribuna"><p class="hint">Ajuda quem usa leitor de tela e o Google.</p></div>
      </div>
      <div class="card">
        <div class="fd"><label for="meta_description">Resumo para Google e redes</label><textarea id="meta_description" name="meta_description" rows="3" maxlength="300" placeholder="Opcional. Se vazio, usa a linha fina."><?= e($post['meta_description']) ?></textarea></div>
      </div>
    </div>
  </div>
</form>
<?php
$csrf = csrf_token();
ob_start(); ?>
<script>
(function () {
  var base = <?= json_encode($base) ?>, csrf = <?= json_encode($csrf) ?>, dirty = false;
  var form = document.getElementById('pf'), title = document.getElementById('title'), slug = document.getElementById('slug');
  function slugify(s) { return s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 180); }
  title.addEventListener('input', function () { if (slug.dataset.auto) slug.value = slugify(title.value); });
  slug.addEventListener('input', function () { slug.dataset.auto = ''; });
  slug.addEventListener('blur', function () { slug.value = slugify(slug.value); });
  form.addEventListener('input', function () { dirty = true; });
  form.addEventListener('submit', function () { dirty = false; if (window.tinymce) tinymce.triggerSave(); });
  addEventListener('beforeunload', function (ev) { if (dirty) { ev.preventDefault(); ev.returnValue = ''; } });

  var cin = document.getElementById('coverIn'), cprev = document.getElementById('coverPrev');
  cin.addEventListener('change', function () {
    var f = cin.files[0]; if (!f) return;
    cprev.hidden = false; cprev.querySelector('img').src = URL.createObjectURL(f);
    cin.nextElementSibling.textContent = f.name;
  });
  var gin = document.getElementById('galAdd'), gtxt = document.getElementById('galTxt');
  gin.addEventListener('change', function () { gtxt.textContent = gin.files.length ? gin.files.length + (gin.files.length > 1 ? ' fotos serão adicionadas ao salvar' : ' foto será adicionada ao salvar') : 'Adicionar fotos (pode escolher várias)'; });
  document.getElementById('galEd').addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-rm]'); if (!b) return;
    var fig = b.closest('figure'), inp = fig.querySelector('input');
    var rm = !fig.classList.contains('rm');
    fig.classList.toggle('rm', rm); inp.disabled = rm; dirty = true;
    b.setAttribute('aria-label', rm ? 'Manter foto' : 'Remover foto');
  });
  ['dragenter', 'dragover'].forEach(function (t) { document.addEventListener(t, function (ev) { var d = ev.target.closest && ev.target.closest('.drop'); if (d) d.classList.add('over'); }); });
  ['dragleave', 'drop'].forEach(function (t) { document.addEventListener(t, function (ev) { var d = ev.target.closest && ev.target.closest('.drop'); if (d) d.classList.remove('over'); }); });

  if (!window.tinymce) return;
  tinymce.init({
    selector: '#content',
    base_url: base + '/admin/vendor/tinymce', suffix: '.min',
    language: 'pt_BR', language_url: base + '/admin/vendor/tinymce/langs/pt_BR.js',
    plugins: 'lists link image media table autolink code fullscreen wordcount autoresize charmap quickbars searchreplace visualblocks',
    menubar: false, branding: false, promotion: false,
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist blockquote | alignleft aligncenter | link image media table | removeformat | searchreplace code fullscreen',
    toolbar_mode: 'wrap',
    block_formats: 'Parágrafo=p; Subtítulo=h2; Subtítulo menor=h3',
    quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
    quickbars_insert_toolbar: false,
    content_css: base + '/admin/assets/editor.css',
    body_class: 'prose',
    min_height: 480, autoresize_bottom_margin: 40,
    relative_urls: false, remove_script_host: true, convert_urls: true,
    image_caption: true, image_dimensions: false, image_description: true,
    automatic_uploads: true, paste_data_images: true, images_file_types: 'jpg,jpeg,png,webp,gif',
    link_default_target: '_blank', link_assume_external_targets: 'https',
    media_live_embeds: true,
    images_upload_handler: function (blob) {
      var fd = new FormData(); fd.append('file', blob.blob(), blob.filename());
      return fetch(base + '/admin/upload.php', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-CSRF-Token': csrf } })
        .then(function (r) { return r.json().then(function (j) { if (!r.ok || !j.location) throw { message: j.error || 'Falha no envio da imagem.', remove: true }; return j.location; }); });
    },
    setup: function (ed) { ed.on('input change undo redo', function () { dirty = true; }); }
  });
})();
</script>
<?php admin_foot(ob_get_clean());
