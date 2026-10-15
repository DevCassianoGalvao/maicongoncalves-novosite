<?php
/** Recebe as imagens coladas/enviadas dentro do editor de texto. */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/auth.php';
require_once ROOT . '/app/images.php';

if (!auth_user()) json_out(['error' => 'Sessão expirada. Entre de novo no painel.'], 401);
if (!is_admin()) json_out(['error' => 'Só o administrador pode enviar imagens para o blog.'], 403);
if (!is_post()) json_out(['error' => 'Método não permitido.'], 405);
$t = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($t) || !hash_equals(csrf_token(), $t)) json_out(['error' => 'Sessão expirada. Recarregue a página.'], 419);
$f = $_FILES['file'] ?? null;
if (!$f || $f['error'] !== UPLOAD_ERR_OK) json_out(['error' => 'Nenhuma imagem recebida (limite da hospedagem: ' . ini_get('upload_max_filesize') . ').'], 400);
try {
    $img = img_store_upload($f['tmp_name'], 1600);
    json_out(['location' => url('/' . $img['src'])]);
} catch (Throwable $ex) {
    json_out(['error' => $ex->getMessage()], 422);
}
