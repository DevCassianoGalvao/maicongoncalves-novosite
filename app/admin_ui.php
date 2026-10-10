<?php
defined('APP') or exit;
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/tickets.php';

/** Garante instalação feita e usuário logado. */
function admin_guard(): array
{
    if (!is_file(ROOT . '/storage/installed.lock')) redirect(url('/install/'));
    $u = require_login();
    db_migrate();
    return $u;
}

function admin_css(): string
{
    $v = substr(md5((string) @filemtime(ROOT . '/admin/assets/admin.css')), 0, 8);
    return '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="' . e(url('/admin/assets/admin.css')) . '?v=' . $v . '">';
}

function admin_head(string $title, string $active = '', string $extraHead = ''): void
{
    $u = auth_user();
    $items = [
        'dash' => ['/admin/', 'squares-four', 'Painel'],
        'tickets' => ['/admin/atendimentos.php', 'tray', 'Atendimentos'],
        'posts' => ['/admin/posts.php', 'newspaper', 'Artigos'],
        'new' => ['/admin/post.php', 'plus-circle', 'Novo artigo'],
        'cats' => ['/admin/categorias.php', 'tag', 'Categorias'],
        'settings' => ['/admin/configuracoes.php', 'gear-six', 'Configurações'],
        'account' => ['/admin/conta.php', 'user-circle', 'Minha conta'],
    ];
    $newTickets = 0;
    try { $newTickets = (int) q("SELECT COUNT(*) FROM tickets WHERE status = 'novo'")->fetchColumn(); } catch (Throwable $ex) {}
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Painel</title>
<?= admin_css() ?>
<?= $extraHead ?>
</head>
<body>
<div class="mbar"><img src="<?= e(url('/img/logo.webp')) ?>" alt="Vereador Maicon Gonçalves"><button type="button" id="menu" aria-label="Abrir menu"><?= icon('list') ?></button></div>
<div class="shell">
  <aside class="side" id="side">
    <a class="side-logo" href="<?= e(url('/admin/')) ?>"><img src="<?= e(url('/img/logo.webp')) ?>" alt="Vereador Maicon Gonçalves"></a>
    <nav class="nav" aria-label="Painel">
<?php foreach ($items as $key => [$href, $ic, $label]): ?>
      <a href="<?= e(url($href)) ?>"<?= $key === $active ? ' class="on" aria-current="page"' : '' ?>><?= icon($ic) ?><?= $label ?><?= $key === 'tickets' && $newTickets ? '<span class="nav-n" title="Novos">' . $newTickets . '</span>' : '' ?></a>
<?php endforeach; ?>
      <div class="nav-sep"></div>
      <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>Ver o site</a>
      <a href="<?= e(url('/admin/sair.php')) ?>"><?= icon('sign-out') ?>Sair</a>
    </nav>
    <div class="side-ft">Conectado como<b><?= e($u['name'] ?? '') ?></b></div>
  </aside>
  <main class="main">
<?php $fl = flashes(); if ($fl): ?>
    <div class="toasts" role="status" aria-live="polite">
<?php foreach ($fl as [$type, $msg]): ?>
      <div class="toast toast--<?= e($type) ?>"><?= icon($type === 'ok' ? 'check-circle' : 'warning-circle') ?><span><?= e($msg) ?></span><button type="button" aria-label="Fechar">&times;</button></div>
<?php endforeach; ?>
    </div>
<?php endif;
}

function admin_foot(string $extra = ''): void
{
    ?>
  </main>
</div>
<script>
(function () {
  var m = document.getElementById('menu'), s = document.getElementById('side');
  if (m) m.addEventListener('click', function (ev) { ev.stopPropagation(); s.classList.toggle('open'); });
  document.addEventListener('click', function (ev) { if (s.classList.contains('open') && !s.contains(ev.target)) s.classList.remove('open'); });
  document.querySelectorAll('[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (ev) { if (!confirm(f.getAttribute('data-confirm'))) { ev.preventDefault(); ev.stopImmediatePropagation(); } });
  });
  // avisos somem sozinhos (erros ficam até fechar)
  document.querySelectorAll('.toast').forEach(function (t) {
    var close = function () { t.classList.add('out'); setTimeout(function () { t.remove(); }, 300); };
    t.querySelector('button').addEventListener('click', close);
    if (t.classList.contains('toast--ok')) setTimeout(close, 5000);
  });
  // botão mostra "Salvando…" enquanto envia
  document.querySelectorAll('form[method="post"]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
      if (ev.defaultPrevented) return;
      var b = ev.submitter || f.querySelector('button[type="submit"], button:not([type])');
      if (!b || b.classList.contains('link-btn')) return;
      b.classList.add('is-saving'); // não usa "disabled" para o valor do botão continuar indo no envio
      var label = b.lastChild && b.lastChild.nodeType === 3 ? b.lastChild : null;
      if (label) label.textContent = /exclu/i.test(label.textContent) ? 'Excluindo…' : /public|atualiz/i.test(label.textContent) ? 'Publicando…' : 'Salvando…';
    });
  });
})();
</script>
<?= $extra ?>
</body>
</html>
<?php
}

/** Tela simples (login, instalador). */
function auth_page_start(string $title, bool $wide = false): void
{
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?></title>
<?= admin_css() ?>
</head>
<body class="auth">
<div class="auth-box<?= $wide ? ' auth-box--wide' : '' ?>">
  <div class="auth-logo"><img src="<?= e(url('/img/logo.webp')) ?>" alt="Vereador Maicon Gonçalves"></div>
  <div class="card">
<?php
}
function auth_page_end(): void
{
    echo "  </div>\n</div>\n</body>\n</html>\n";
}

/** Valor de um campo do formulário enviado (texto). */
function in(string $k, string $default = ''): string
{
    $v = $_POST[$k] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function status_badge(array $p): string
{
    if ($p['status'] === 'published') {
        if ($p['published_at'] && strtotime($p['published_at']) > time()) return '<span class="badge badge--sched">Agendado</span>';
        return '<span class="badge badge--pub">Publicado</span>';
    }
    return '<span class="badge badge--draft">Rascunho</span>';
}

/** Apaga um arquivo enviado pelo painel (só dentro de uploads/). */
function delete_upload(?string $rel): void
{
    if (!$rel || strpos($rel, 'uploads/') !== 0 || strpos($rel, '..') !== false) return;
    $f = ROOT . '/' . $rel;
    if (is_file($f)) @unlink($f);
}

function ticket_badge(array $t): string
{
    $cls = ['novo' => 'badge--new', 'analise' => 'badge--draft', 'publicado' => 'badge--sched', 'resolvido' => 'badge--pub', 'arquivado' => ''][$t['status']] ?? '';
    return '<span class="badge ' . $cls . '">' . e(TICKET_STATUS[$t['status']] ?? $t['status']) . '</span>';
}
