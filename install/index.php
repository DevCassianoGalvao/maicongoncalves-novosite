<?php
/**
 * Instalador: cria as tabelas, importa os artigos atuais e cria o usuário do painel.
 * Depois de concluído, fica travado (storage/installed.lock).
 */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';

$lock = storage() . '/installed.lock';
csrf_token(); // inicia a sessão antes de qualquer saída
$ok = icon('check-circle', 'fill');
$no = icon('x-circle', 'fill');
$warnIc = icon('warning-circle', 'fill');

if (is_file($lock)) {
    auth_page_start('Instalação concluída');
    echo '<h1>Instalação já concluída</h1><p class="sub">O site já está instalado. Para reinstalar, apague o arquivo <code>storage/installed.lock</code> pelo Gerenciador de Arquivos.</p>';
    echo '<a class="btn btn--or btn--block" href="' . e(url('/admin/')) . '">Ir para o painel</a>';
    auth_page_end();
    exit;
}

/* ---------- verificações ---------- */
$checks = [];
$add = function (string $label, bool $pass, bool $required = true, string $help = '') use (&$checks) { $checks[] = compact('label', 'pass', 'required', 'help'); };
$add('PHP 7.4 ou mais novo (atual: ' . PHP_VERSION . ')', version_compare(PHP_VERSION, '7.4', '>='));
foreach (['pdo_mysql' => 'PDO MySQL', 'gd' => 'GD (imagens)', 'curl' => 'cURL (envio de e-mail)', 'mbstring' => 'mbstring', 'fileinfo' => 'fileinfo', 'json' => 'JSON'] as $ext => $label) {
    $add('Extensão ' . $label, extension_loaded($ext), true, 'Ative em cPanel › Selecionar versão do PHP › Extensões.');
}
$add('WEBP no GD (imagens mais leves)', function_exists('imagewebp'), false, 'Sem WEBP as imagens são salvas em JPG. Funciona igual.');
$add('Arquivo .env encontrado' . (ENV_FILE ? ' (' . (ENV_FILE === ROOT . '/.env' ? 'na raiz do site' : 'fora da pasta pública') . ')' : ''), (bool) ENV_FILE, true, 'Copie .env.example para .env e preencha os dados do banco.');
foreach (['uploads', 'storage'] as $d) $add('Pasta ' . $d . '/ com permissão de escrita', is_dir(ROOT . '/' . $d) && is_writable(ROOT . '/' . $d), true, 'Ajuste a permissão da pasta para 755 (ou 775).');
$dbError = '';
$dbOk = false;
if (ENV_FILE) {
    try { db(); $dbOk = true; } catch (Throwable $ex) { $dbError = $ex->getMessage(); }
}
$add('Conexão com o banco de dados' . ($dbOk ? ' (' . e(env('DB_NAME')) . ')' : ''), $dbOk, true, $dbError ? 'Erro: ' . $dbError : 'Confira DB_HOST, DB_NAME, DB_USER e DB_PASS no .env.');
$add('Chave do Brevo (BREVO_API_KEY)', env('BREVO_API_KEY', '') !== '', false, 'Sem a chave o formulário de contato não envia. Pode preencher depois.');
$add('Remetente do e-mail (MAIL_FROM_EMAIL)', env('MAIL_FROM_EMAIL', '') !== '', false, 'Use um remetente validado no Brevo.');
$add('Endereço do site (APP_URL)', env('APP_URL', '') !== '', false, 'Ex.: https://maicongoncalves.com.br — usado nos links de compartilhar.');
$ready = !array_filter($checks, function ($c) { return $c['required'] && !$c['pass']; });

/* ---------- instalação ---------- */
$errors = [];
$done = false;
if ($ready && is_post()) {
    csrf_check();
    $name = in('name');
    $email = mb_strtolower(in('email'));
    $pass = (string) ($_POST['password'] ?? '');
    if ($name === '') $errors[] = 'Informe o nome.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Informe um e-mail válido.';
    if (mb_strlen($pass) < 8) $errors[] = 'A senha precisa ter pelo menos 8 caracteres.';
    if ($pass !== (string) ($_POST['password2'] ?? '')) $errors[] = 'As senhas não conferem.';

    if (!$errors) {
        try {
            $pdo = db();
            $sql = preg_replace('/^--.*$/m', '', file_get_contents(__DIR__ . '/schema.sql'));
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) $pdo->exec($stmt);

            $now = date('Y-m-d H:i:s');
            $seed = json_decode(file_get_contents(__DIR__ . '/seed.json'), true);
            $pdo->beginTransaction();

            $st = $pdo->prepare('INSERT IGNORE INTO settings (name, value) VALUES (?, ?)');
            foreach (SETTINGS_DEFAULTS as $k => $v) $st->execute([$k, $v]);

            $imported = 0;
            if ((int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn() === 0) {
                $catId = [];
                $insCat = $pdo->prepare('INSERT INTO categories (name, slug, created_at) VALUES (?, ?, ?)');
                foreach ($seed['categories'] as $c) {
                    $slug = slugify($c);
                    $found = q('SELECT id FROM categories WHERE slug = ?', [$slug])->fetchColumn();
                    if (!$found) { $insCat->execute([$c, $slug, $now]); $found = $pdo->lastInsertId(); }
                    $catId[$c] = (int) $found;
                }
                $insPost = $pdo->prepare('INSERT INTO posts (title, slug, excerpt, content, cover, cover_w, cover_h, cover_alt, gallery, category_id, status, published_at, meta_description, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'published\', ?, ?, ?, ?)');
                foreach ($seed['posts'] as $p) {
                    $insPost->execute([$p['title'], $p['slug'], $p['excerpt'], $p['content'], $p['cover'], $p['cover_w'], $p['cover_h'], $p['cover_alt'],
                        $p['gallery'] ? json_encode($p['gallery'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                        $catId[$p['category']] ?? null, $p['published_at'], $p['meta_description'], $now, $now]);
                    $imported++;
                }
            }

            $hash = password_hash($pass, PASSWORD_DEFAULT);
            if (q('SELECT id FROM users WHERE email = ?', [$email])->fetchColumn()) {
                q('UPDATE users SET name = ?, password_hash = ? WHERE email = ?', [$name, $hash, $email]);
            } else {
                q('INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, ?)', [$name, $email, $hash, $now]);
            }
            $pdo->commit();
            file_put_contents($lock, 'Instalado em ' . $now . "\n");
            $done = true;
        } catch (Throwable $ex) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Falha na instalação: ' . $ex->getMessage();
        }
    }
}

auth_page_start('Instalação do site', true);
if ($done): ?>
    <h1>Pronto! Site instalado.</h1>
    <p class="sub">Tabelas criadas, <?= (int) $imported ?> artigos importados e seu acesso ao painel criado.</p>
    <ul class="checks">
      <li><span class="ok"><?= $ok ?></span><span>Entre no painel com <b><?= e($email) ?></b> e a senha que você acabou de definir.</span></li>
      <li><span class="ok"><?= $ok ?></span><span>O instalador agora está travado. Se quiser, apague a pasta <code>install/</code> da hospedagem.</span></li>
    </ul>
    <a class="btn btn--or btn--block" href="<?= e(url('/admin/')) ?>">Entrar no painel</a>
<?php else: ?>
    <h1>Instalação do site</h1>
    <p class="sub">Confira os requisitos da hospedagem e crie o primeiro acesso ao painel.</p>
    <ul class="checks">
<?php foreach ($checks as $c): ?>
      <li><span class="<?= $c['pass'] ? 'ok' : ($c['required'] ? 'no' : 'warn') ?>" style="<?= !$c['pass'] && !$c['required'] ? 'color:var(--warn)' : '' ?>"><?= $c['pass'] ? $ok : ($c['required'] ? $no : $warnIc) ?></span>
        <span><?= $c['label'] ?><?php if (!$c['pass'] && $c['help']): ?><br><small class="muted"><?= e($c['help']) ?></small><?php endif; ?></span></li>
<?php endforeach; ?>
    </ul>
<?php foreach ($errors as $err): ?>
    <div class="alert alert--err"><?= icon('warning-circle') ?><span><?= e($err) ?></span></div>
<?php endforeach; ?>
<?php if ($ready): ?>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <div class="fd"><label for="name">Seu nome</label><input type="text" id="name" name="name" value="<?= e(in('name')) ?>" required></div>
      <div class="fd"><label for="email">E-mail de acesso ao painel</label><input type="email" id="email" name="email" value="<?= e(in('email')) ?>" required></div>
      <div class="grid g2">
        <div class="fd"><label for="password">Senha</label><input type="password" id="password" name="password" minlength="8" required autocomplete="new-password"></div>
        <div class="fd"><label for="password2">Repita a senha</label><input type="password" id="password2" name="password2" minlength="8" required autocomplete="new-password"></div>
      </div>
      <p class="hint muted small" style="margin:-4px 0 16px">Mínimo de 8 caracteres. As tabelas são criadas e os 6 artigos atuais do site são importados.</p>
      <button class="btn btn--or btn--block" type="submit">Instalar</button>
    </form>
<?php else: ?>
    <div class="alert alert--warn"><?= icon('warning-circle') ?><span>Resolva os itens em vermelho e recarregue esta página.</span></div>
<?php endif; ?>
<?php endif;
auth_page_end();
