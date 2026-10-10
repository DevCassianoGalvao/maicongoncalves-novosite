<?php
/**
 * Recebe o formulário da página Contato e envia para o gabinete pelo Brevo,
 * com as fotos em anexo e a localização (quando a pessoa compartilha).
 */
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/images.php';
require ROOT . '/app/mail.php';

if (!is_post()) json_out(['ok' => false, 'error' => 'Método não permitido.'], 405);

// Corpo maior que o limite do PHP: o PHP descarta tudo e $_POST chega vazio
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    json_out(['ok' => false, 'error' => 'As fotos ficaram pesadas demais para enviar. Tente com menos fotos.'], 413);
}

// Só aceita envios vindos do próprio site
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($origin !== '' && $host !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('http://' . $host, PHP_URL_HOST)) {
    json_out(['ok' => false, 'error' => 'Origem não permitida.'], 403);
}

$f = function (string $k, int $max = 255): string {
    $v = $_POST[$k] ?? '';
    return is_string($v) ? mb_substr(trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v)), 0, $max) : '';
};
$ok = ['ok' => true, 'message' => 'Mensagem enviada! Meu time vai analisar sua demanda e retornar o mais breve possível.'];

// Robôs: campo escondido preenchido ou formulário enviado rápido demais
$started = (int) $f('t', 20);
if ($f('website') !== '' || ($started > 0 && time() - intdiv($started, 1000) < 3)) json_out($ok);

if (!rate_ok('contato', 5, 1800)) json_out(['ok' => false, 'error' => 'Você já enviou várias mensagens. Aguarde alguns minutos ou fale pelo WhatsApp.'], 429);

$d = [
    'nome' => $f('nome', 120),
    'nascimento' => $f('nascimento', 10),
    'whatsapp' => $f('whatsapp', 30),
    'bairro' => $f('bairro', 120),
    'endereco' => $f('endereco', 255),
    'assunto' => $f('assunto', 80),
    'mensagem' => $f('mensagem', 5000),
];
$lat = filter_var($_POST['lat'] ?? '', FILTER_VALIDATE_FLOAT);
$lng = filter_var($_POST['lng'] ?? '', FILTER_VALIDATE_FLOAT);
$acc = (int) round((float) ($_POST['acc'] ?? 0));
$geoAddr = $f('geo_addr', 300);
$geoManual = $f('geo_manual', 1) === '1';
$hasGeo = $lat !== false && $lng !== false && abs($lat) <= 90 && abs($lng) <= 180 && !($lat == 0 && $lng == 0);

$digits = preg_replace('/\D+/', '', $d['whatsapp']);
$need = ['nome' => 'Informe seu nome.', 'whatsapp' => 'Informe seu telefone/WhatsApp.', 'bairro' => 'Escolha o bairro ou localidade.', 'assunto' => 'Escolha o assunto.', 'mensagem' => 'Escreva sua mensagem.'];
foreach ($need as $k => $msg) if ($d[$k] === '') json_out(['ok' => false, 'error' => $msg, 'field' => $k], 422);
if (strlen($digits) < 10 || strlen($digits) > 13) json_out(['ok' => false, 'error' => 'Confira o número de telefone/WhatsApp (com DDD).', 'field' => 'whatsapp'], 422);

/* ---------- fotos ---------- */
$attachments = [];
$skipped = [];
$files = $_FILES['fotos'] ?? null;
if ($files && is_array($files['name'])) {
    $total = 0;
    foreach ($files['name'] as $i => $name) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if (count($attachments) >= 6) { $skipped[] = $name . ' (limite de 6 fotos)'; continue; }
        if ($files['error'][$i] !== UPLOAD_ERR_OK) { $skipped[] = $name . ' (falha no envio)'; continue; }
        try {
            $jpg = img_to_jpeg($files['tmp_name'][$i]);
        } catch (Throwable $ex) {
            $skipped[] = $name . ' (' . $ex->getMessage() . ')';
            continue;
        }
        if ($total + strlen($jpg) > 15 * 1024 * 1024) { $skipped[] = $name . ' (tamanho total excedido)'; continue; }
        $total += strlen($jpg);
        $attachments[] = ['name' => sprintf('foto-%d-%s.jpg', count($attachments) + 1, slugify($d['bairro']) ?: 'local'), 'content' => $jpg];
    }
}

/* ---------- e-mail ---------- */
$waDigits = strlen($digits) <= 11 ? '55' . $digits : $digits;
$rows = [
    ['Assunto', e($d['assunto'])],
    ['Nome', e($d['nome'])],
    ['WhatsApp', e($d['whatsapp']) . ' &nbsp;<a href="https://wa.me/' . $waDigits . '" style="color:#0144C9">abrir conversa</a>'],
    ['Nascimento', e($d['nascimento'] ?: '—')],
    ['Bairro / localidade', e($d['bairro'])],
    ['Endereço informado', e($d['endereco'] ?: '—')],
];
$text = "NOVO CONTATO VINDO DO SITE\n\nAssunto: {$d['assunto']}\nNome: {$d['nome']}\nWhatsApp: {$d['whatsapp']}\nNascimento: " . ($d['nascimento'] ?: '—') .
    "\nBairro: {$d['bairro']}\nEndereço informado: " . ($d['endereco'] ?: '—') . "\n";
if ($hasGeo) {
    $ll = sprintf('%.6f,%.6f', $lat, $lng);
    $gmaps = 'https://www.google.com/maps/search/?api=1&query=' . $ll;
    $osm = sprintf('https://www.openstreetmap.org/?mlat=%.6f&mlon=%.6f#map=19/%.6f/%.6f', $lat, $lng, $lat, $lng);
    $how = $geoManual ? 'ponto ajustado pela pessoa no mapa' : ('GPS do aparelho' . ($acc ? ', precisão de ±' . $acc . ' m' : ''));
    $rows[] = ['Localização', ($geoAddr !== '' ? e($geoAddr) . '<br>' : '') . '<span style="color:#5B6884">' . $ll . ' (' . e($how) . ')</span><br>' .
        '<a href="' . e($gmaps) . '" style="display:inline-block;margin-top:8px;padding:8px 14px;border-radius:999px;background:#FE6500;color:#fff;text-decoration:none;font-weight:700">Ver no Google Maps</a> ' .
        '<a href="' . e($osm) . '" style="color:#0144C9;margin-left:8px">OpenStreetMap</a>'];
    $text .= "Localização: " . ($geoAddr !== '' ? $geoAddr . ' — ' : '') . "$ll ($how)\nMapa: $gmaps\n";
} else {
    $rows[] = ['Localização', '<span style="color:#5B6884">Não compartilhada</span>'];
}
$rows[] = ['Fotos', $attachments ? count($attachments) . ' em anexo' : 'Nenhuma'];
$text .= "\nMensagem:\n{$d['mensagem']}\n\nFotos: " . count($attachments) . "\n";
if ($skipped) { $rows[] = ['Fotos não anexadas', e(implode('; ', $skipped))]; $text .= 'Não anexadas: ' . implode('; ', $skipped) . "\n"; }

$tr = '';
foreach ($rows as [$k, $v]) {
    $tr .= '<tr><td style="padding:10px 14px;border-bottom:1px solid #E3EAF4;color:#5B6884;font-size:13px;font-weight:700;white-space:nowrap;vertical-align:top">' . $k .
           '</td><td style="padding:10px 14px;border-bottom:1px solid #E3EAF4;color:#0F1B3A;font-size:15px">' . $v . '</td></tr>';
}
$html = '<!doctype html><html><body style="margin:0;background:#F1F4F9;font-family:Arial,Helvetica,sans-serif">
<div style="max-width:640px;margin:0 auto;padding:24px 14px">
  <div style="background:#031436;color:#fff;border-radius:16px 16px 0 0;padding:20px 22px">
    <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#FF8A3D;font-weight:700">Novo contato vindo do site</div>
    <div style="font-size:20px;font-weight:700;margin-top:6px">' . e($d['assunto']) . ' — ' . e($d['nome']) . '</div>
  </div>
  <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;background:#fff;border-collapse:collapse">' . $tr . '</table>
  <div style="background:#fff;padding:18px 22px;border-top:4px solid #FE6500">
    <div style="font-size:13px;font-weight:700;color:#5B6884;margin-bottom:6px">Mensagem</div>
    <div style="font-size:15px;line-height:1.6;color:#0F1B3A">' . nl2br(e($d['mensagem'])) . '</div>
  </div>
  <div style="background:#fff;border-radius:0 0 16px 16px;padding:12px 22px 18px;color:#8A96B0;font-size:12px">Enviado pelo formulário de contato do site em ' . date('d/m/Y \à\s H:i') . '.</div>
</div></body></html>';

$subject = (string) env('MAIL_SUBJECT', 'Novo contato vindo do site do Vereador Maicon Gonçalves');
[$sent, $info] = brevo_send($subject, $html, $text, $attachments);

$logLine = sprintf('%s | %s | %s | %s | %s | fotos=%d | geo=%s', $sent ? 'ENVIADO ' . $info : 'FALHOU ' . $info, $d['nome'], $d['whatsapp'], $d['bairro'], $d['assunto'], count($attachments), $hasGeo ? $ll : '-');
app_log('contato', $logLine . ($sent ? '' : "\n" . $text));

if (!$sent) json_out(['ok' => false, 'error' => 'Não conseguimos enviar agora. Tente de novo em instantes ou fale pelo WhatsApp.'], 502);
if ($skipped) $ok['message'] .= ' Algumas fotos não foram anexadas: ' . implode('; ', $skipped) . '.';
json_out($ok);
