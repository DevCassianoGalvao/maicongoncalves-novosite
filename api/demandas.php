<?php
/**
 * GET  → demandas publicadas no mapa (JSON)
 * POST → registra uma nova demanda (fica "Nova" no painel até o gabinete aprovar)
 */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/tickets.php';
require ROOT . '/app/mail.php';

if (!is_post()) {
    header('Cache-Control: public, max-age=60');
    json_out(['ok' => true, 'items' => tickets_public()]);
}

if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) json_out(['ok' => false, 'error' => 'As fotos ficaram pesadas demais. Tente com menos fotos.'], 413);
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($origin !== '' && $host !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('http://' . $host, PHP_URL_HOST)) json_out(['ok' => false, 'error' => 'Origem não permitida.'], 403);

$f = function (string $k, int $max = 255): string {
    $v = $_POST[$k] ?? '';
    return is_string($v) ? mb_substr(trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v)), 0, $max) : '';
};
$started = (int) $f('t', 20);
if ($f('website') !== '' || ($started > 0 && time() - intdiv($started, 1000) < 4)) json_out(['ok' => true, 'code' => '#00000']);
if (!rate_ok('demanda', 6, 3600)) json_out(['ok' => false, 'error' => 'Você já registrou várias demandas agora há pouco. Tente mais tarde ou fale pelo WhatsApp.'], 429);

$anon = $f('anonimo', 1) === '1';
$d = [
    'source' => 'mapa',
    'category' => $f('categoria', 20),
    'name' => $f('nome', 120),
    'phone' => $f('whatsapp', 30),
    'anonymous' => $anon,
    'description' => $f('descricao', 1500),
    'address' => $f('endereco', 255),
    'neighborhood' => $f('bairro', 120),
    'geo_address' => $f('geo_addr', 300),
    'consent' => $f('consentimento', 1) === '1',
];
$lat = filter_var($_POST['lat'] ?? '', FILTER_VALIDATE_FLOAT);
$lng = filter_var($_POST['lng'] ?? '', FILTER_VALIDATE_FLOAT);
$digits = preg_replace('/\D+/', '', $d['phone']);

if (!isset(TICKET_CATS[$d['category']])) json_out(['ok' => false, 'error' => 'Escolha o tipo do problema.', 'field' => 'categoria'], 422);
if ($lat === false || $lng === false || abs($lat + 22.28) > 0.6 || abs($lng + 42.53) > 0.6) json_out(['ok' => false, 'error' => 'Marque no mapa onde fica o problema (dentro de Nova Friburgo).', 'field' => 'lat'], 422);
if ($d['description'] === '') json_out(['ok' => false, 'error' => 'Descreva rapidamente o problema.', 'field' => 'descricao'], 422);
if (!$anon && $d['name'] === '') json_out(['ok' => false, 'error' => 'Informe seu nome (ou marque para não aparecer).', 'field' => 'nome'], 422);
if (strlen($digits) < 10 || strlen($digits) > 13) json_out(['ok' => false, 'error' => 'Informe seu WhatsApp com DDD para o gabinete falar com você.', 'field' => 'whatsapp'], 422);
if (!$d['consent']) json_out(['ok' => false, 'error' => 'Para enviar, é preciso concordar com o uso dos dados.', 'field' => 'consentimento'], 422);

$photos = [];
$files = $_FILES['fotos'] ?? null;
if ($files && is_array($files['name'])) {
    foreach ($files['name'] as $i => $name) {
        if (count($photos) >= 3 || $files['error'][$i] !== UPLOAD_ERR_OK) continue;
        try { $photos[] = ticket_photo_store($files['tmp_name'][$i]); }
        catch (Throwable $ex) { json_out(['ok' => false, 'error' => 'Foto "' . $name . '": ' . $ex->getMessage(), 'field' => 'fotos'], 422); }
    }
}
if (!$photos) json_out(['ok' => false, 'error' => 'Envie pelo menos uma foto do problema.', 'field' => 'fotos'], 422);

$d['lat'] = round($lat, 7);
$d['lng'] = round($lng, 7);
$d['accuracy'] = (int) round((float) ($_POST['acc'] ?? 0)) ?: null;
$d['geo_manual'] = $f('geo_manual', 1) === '1';
$d['photos'] = $photos;
$id = ticket_create($d);
ticket_note($id, 'Demanda registrada pelo site (' . S('radar_name') . ').');

// aviso rápido para o gabinete (sem dados sensíveis no assunto)
$cat = TICKET_CATS[$d['category']];
$link = abs_url('/admin/atendimento.php?id=' . $id);
[$sent] = brevo_send(
    'Nova demanda no ' . S('radar_name') . ': ' . $cat . ($d['neighborhood'] ? ' – ' . $d['neighborhood'] : ''),
    '<div style="font-family:Arial,sans-serif;font-size:15px;color:#0F1B3A"><p><b>Nova demanda ' . ticket_code($id) . '</b> — ' . e($cat) . '</p><p>' . e($d['address'] ?: $d['geo_address']) . ' ' . e($d['neighborhood']) . '</p><p>' . nl2br(e($d['description'])) . '</p><p><a href="' . e($link) . '" style="display:inline-block;padding:10px 16px;border-radius:999px;background:#FE6500;color:#fff;text-decoration:none;font-weight:700">Abrir no painel</a></p></div>',
    "Nova demanda " . ticket_code($id) . " — $cat\n" . $d['description'] . "\nAbrir no painel: $link"
);
app_log('demandas', ticket_code($id) . ' | ' . $cat . ' | aviso por e-mail ' . ($sent ? 'enviado' : 'falhou'));

json_out(['ok' => true, 'code' => ticket_code($id)]);
