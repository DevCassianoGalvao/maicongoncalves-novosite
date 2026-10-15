<?php
/**
 * GET  → demandas publicadas no mapa (JSON)
 * POST → registra uma nova demanda (fica "Nova" no painel até o gabinete aprovar)
 */
require dirname(__DIR__) . '/app/bootstrap.php';
require_once ROOT . '/app/tickets.php';
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
    'email' => mb_strtolower($f('email', 190)),
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

if (!isset(ticket_cats()[$d['category']])) json_out(['ok' => false, 'error' => 'Escolha o tipo da demanda.', 'field' => 'categoria'], 422);
if ($lat === false || $lng === false || abs($lat + 22.28) > 0.6 || abs($lng + 42.53) > 0.6) json_out(['ok' => false, 'error' => 'Marque no mapa onde fica o problema (dentro de Nova Friburgo).', 'field' => 'lat'], 422);
if ($d['description'] === '') json_out(['ok' => false, 'error' => 'Descreva rapidamente o problema.', 'field' => 'descricao'], 422);
if (!$anon && $d['name'] === '') json_out(['ok' => false, 'error' => 'Informe seu nome (ou marque para não aparecer).', 'field' => 'nome'], 422);
if (strlen($digits) < 10 || strlen($digits) > 13) json_out(['ok' => false, 'error' => 'Informe seu WhatsApp com DDD para o gabinete falar com você.', 'field' => 'whatsapp'], 422);
if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) json_out(['ok' => false, 'error' => 'Informe um e-mail válido para receber os avisos da sua demanda.', 'field' => 'email'], 422);
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
ticket_note($id, 'Demanda registrada pelo site.');
if ($f('novidades', 1) === '1') {
    q('UPDATE tickets SET news_optin = 1 WHERE id = ?', [$id]);
    try { subscriber_optin($d['email'], $d['name'], 'demanda'); } catch (Throwable $ex) { app_log('demandas', 'inscrição de novidades falhou: ' . $ex->getMessage()); }
}

// e-mail para o gabinete: "Nova demanda", com as fotos em anexo
$cat = cat_label($d['category']);
$code = ticket_code($id);
$link = abs_url('/admin/atendimento.php?id=' . $id);
$ll = sprintf('%.6f,%.6f', $d['lat'], $d['lng']);
$gmaps = 'https://www.google.com/maps/search/?api=1&query=' . $ll;
$waNum = strlen($digits) <= 11 ? '55' . $digits : $digits;
$attach = [];
foreach ($files['tmp_name'] ?? [] as $i => $tmp) {
    if (count($attach) >= 3 || ($files['error'][$i] ?? 1) !== UPLOAD_ERR_OK) continue;
    try { $attach[] = ['name' => sprintf('foto-%d-%s.jpg', count($attach) + 1, ltrim($code, '#')), 'content' => img_to_jpeg($tmp)]; } catch (Throwable $ex) {}
}
$place = trim($d['address'] . ($d['neighborhood'] ? ($d['address'] ? ' – ' : '') . $d['neighborhood'] : ''));
$rows = [
    ['Protocolo', $code],
    ['Tipo', e($cat)],
    ['Nome', e($d['name'] ?: 'Não informado') . ($anon ? ' <span style="color:#9A5B00">(não quer o nome no mapa)</span>' : '')],
    ['WhatsApp', e($d['phone']) . ' &nbsp;<a href="https://wa.me/' . $waNum . '" style="color:#0144C9">abrir conversa</a>'],
    ['E-mail', '<a href="mailto:' . e($d['email']) . '" style="color:#0144C9">' . e($d['email']) . '</a>'],
    ['Local', ($place !== '' ? e($place) . '<br>' : '') . ($d['geo_address'] !== '' ? '<span style="color:#5B6884">' . e($d['geo_address']) . '</span><br>' : '') .
        '<a href="' . e($gmaps) . '" style="color:#0144C9">Ver no Google Maps</a>'],
    ['Fotos', count($attach) . ' em anexo'],
];
$tr = '';
foreach ($rows as [$k, $v]) {
    $tr .= '<tr><td style="padding:10px 14px;border-bottom:1px solid #E3EAF4;color:#5B6884;font-size:13px;font-weight:700;white-space:nowrap;vertical-align:top">' . $k .
           '</td><td style="padding:10px 14px;border-bottom:1px solid #E3EAF4;color:#0F1B3A;font-size:15px">' . $v . '</td></tr>';
}
$html = '<!doctype html><html><body style="margin:0;background:#F1F4F9;font-family:Arial,Helvetica,sans-serif">
<div style="max-width:640px;margin:0 auto;padding:24px 14px">
  <div style="background:#031436;color:#fff;border-radius:16px 16px 0 0;padding:20px 22px">
    <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#FF8A3D;font-weight:700">Nova demanda pelo site</div>
    <div style="font-size:20px;font-weight:700;margin-top:6px">' . e($cat) . ($d['neighborhood'] ? ' — ' . e($d['neighborhood']) : '') . '</div>
  </div>
  <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;background:#fff;border-collapse:collapse">' . $tr . '</table>
  <div style="background:#fff;padding:18px 22px;border-top:4px solid #FE6500">
    <div style="font-size:13px;font-weight:700;color:#5B6884;margin-bottom:6px">O que está acontecendo</div>
    <div style="font-size:15px;line-height:1.6;color:#0F1B3A">' . nl2br(e($d['description'])) . '</div>
  </div>
  <div style="background:#fff;border-radius:0 0 16px 16px;padding:6px 22px 22px">
    <a href="' . e($link) . '" style="display:inline-block;padding:12px 20px;border-radius:999px;background:#FE6500;color:#fff;text-decoration:none;font-weight:700">Abrir no painel</a>
    <p style="color:#8A96B0;font-size:12px;margin:14px 0 0">Registrado em ' . date('d/m/Y \à\s H:i') . '. Só aparece no mapa depois que o gabinete aprovar.</p>
  </div>
</div></body></html>';
$text = "NOVA DEMANDA PELO SITE\n\nProtocolo: $code\nTipo: $cat\nNome: " . ($d['name'] ?: 'Não informado') . ($anon ? ' (não quer o nome no mapa)' : '') .
    "\nWhatsApp: {$d['phone']}\nE-mail: {$d['email']}\nLocal: $place " . $d['geo_address'] . "\nMapa: $gmaps\n\n{$d['description']}\n\nAbrir no painel: $link\n";
[$sent, $info] = brevo_send('Nova demanda: ' . $cat . ($d['neighborhood'] ? ' – ' . $d['neighborhood'] : ''), $html, $text, $attach, ['email' => $d['email'], 'name' => $d['name'] ?: $d['email']]);
app_log('demandas', $code . ' | ' . $cat . ' | e-mail ' . ($sent ? 'enviado ' . $info : 'falhou: ' . $info));

json_out(['ok' => true, 'code' => ticket_code($id)]);
