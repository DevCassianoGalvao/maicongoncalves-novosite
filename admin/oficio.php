<?php
/** Baixa o ofício (.docx) de uma demanda. Chamado pelo card "Gerar ofício" em atendimento.php. */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/admin_ui.php';
require ROOT . '/app/oficio.php';
$user = admin_guard();
if (!is_post()) redirect(url('/admin/atendimentos.php'));
csrf_check();

$id = (int) in('id');
$t = q('SELECT * FROM tickets WHERE id = ?', [$id])->fetch();
if (!$t) { flash('err', 'Atendimento não encontrado.'); redirect(url('/admin/atendimentos.php')); }
$self = url('/admin/atendimento.php?id=' . $id);

$sec = in('secretaria', '', 20);
if (!isset(SECRETARIAS[$sec])) { flash('err', 'Escolha a secretaria ou subprefeitura que vai receber o ofício.'); redirect($self . '#oficio'); }
$g = in('g') === 'f' ? 'f' : 'm';
$num = preg_replace('/\D/', '', in('numero', '', 10));
$num = $num !== '' ? (string) (int) $num : '';

try {
    $file = oficio_docx($t, ['secretaria' => $sec, 'g' => $g, 'numero' => $num, 'complemento' => in('complemento', '', 1500), 'data' => time()]);
} catch (Throwable $ex) {
    app_log('oficio', ticket_code($id) . ' | ' . $ex->getMessage());
    flash('err', 'Não foi possível gerar o ofício: ' . $ex->getMessage());
    redirect($self . '#oficio');
}

// guarda na demanda o que foi escolhido (secretaria e, se informado e ainda vazio, o nº do ofício)
$sets = []; $args = [];
if (($t['secretaria'] ?? '') !== $sec) { $sets[] = 'secretaria = ?'; $args[] = $sec; }
if ($num !== '' && trim((string) $t['protocol']) === '') { $sets[] = 'protocol = ?'; $args[] = 'Ofício nº ' . $num . '/' . date('Y'); }
if ($sets) q('UPDATE tickets SET ' . implode(', ', $sets) . ', updated_at = NOW() WHERE id = ?', array_merge($args, [$id]));
ticket_note($id, 'Ofício gerado' . ($num !== '' ? ' (nº ' . $num . '/' . date('Y') . ')' : ' (número a preencher)') . ' para ' . SECRETARIAS[$sec] . '.', (int) $user['id']);

$slug = trim(preg_replace('/[^A-Za-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', cat_label($t['category']))), '-');
$name = 'Oficio' . ($num !== '' ? '-' . $num . '-' . date('Y') : '') . '-' . ltrim(ticket_code($id), '#') . '-' . $slug . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-store');
readfile($file);
@unlink($file);
