<?php
/**
 * Avisos por e-mail para quem enviou a demanda:
 *  - 'publicado': a demanda foi enviada à Prefeitura e entrou no mapa
 *  - 'resolvido': a Prefeitura resolveu
 * Remetente: PUBLIC_FROM_EMAIL / PUBLIC_FROM_NAME no .env (e-mail do vereador validado no Brevo).
 */
defined('APP') or exit;
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/tickets.php';

/** Retorna [enviou?, detalhe]. */
function citizen_notify(int $id, string $kind): array
{
    $t = q('SELECT * FROM tickets WHERE id = ?', [$id])->fetch();
    if (!$t || !filter_var((string) $t['email'], FILTER_VALIDATE_EMAIL)) return [false, 'sem e-mail cadastrado'];
    if (!in_array($kind, ['publicado', 'resolvido'], true)) return [false, 'tipo de aviso desconhecido'];

    $code = ticket_code($id);
    $cat = cat_label($t['category']);
    $first = first_name($t['name']);
    $place = trim(($t['address'] ?: '') . ($t['neighborhood'] ? ($t['address'] ? ' – ' : '') . $t['neighborhood'] : ''));
    $track = abs_url('/friburgo-no-mapa/') . '?d=' . $id . '#mapa';
    $sent = $t['published_at'] ? date('d/m/Y', strtotime($t['published_at'])) : date('d/m/Y');
    $hello = 'Olá' . ($first !== '' ? ', ' . $first : '') . '!';

    if ($kind === 'publicado') {
        $subject = 'Sua demanda ' . $code . ' foi enviada à Prefeitura';
        $kicker = 'Demanda enviada à Prefeitura';
        $lead = $hello . ' O gabinete do vereador Maicon Gonçalves conferiu a sua demanda e ela foi encaminhada oficialmente à Prefeitura de Nova Friburgo' .
            ($t['department'] ? ' (' . $t['department'] . ')' : '') . '.';
        $more = 'A partir de agora ela aparece no Friburgo no Mapa, com um contador mostrando há quantos dias a Prefeitura foi avisada. Você pode acompanhar tudo em tempo real.';
        $btn = 'Acompanhar no Friburgo no Mapa';
        $rows = [['Protocolo', $code], ['Tipo', $cat], ['Local', $place ?: '—'], ['Enviada à Prefeitura em', $sent]];
    } else {
        $days = $t['published_at'] && $t['resolved_at'] ? max(0, (int) round((strtotime($t['resolved_at']) - strtotime($t['published_at'])) / 86400)) : null;
        $subject = 'Sua demanda ' . $code . ' foi resolvida';
        $kicker = 'Demanda resolvida';
        $lead = $hello . ' Boa notícia: a sua demanda foi resolvida' . ($days !== null ? ' em ' . $days . ($days === 1 ? ' dia' : ' dias') . ' depois de chegar à Prefeitura' : '') . '.';
        $more = 'Obrigado por ajudar a cuidar de Nova Friburgo. No mapa, o pino da sua demanda agora aparece verde, como resolvido.';
        $btn = 'Ver no mapa';
        $rows = [['Protocolo', $code], ['Tipo', $cat], ['Local', $place ?: '—'], ['Resolvida em', $t['resolved_at'] ? date('d/m/Y', strtotime($t['resolved_at'])) : date('d/m/Y')]];
    }

    $tr = '';
    foreach ($rows as [$k, $v]) {
        $tr .= '<tr><td style="padding:10px 14px;border-bottom:1px solid #E3EAF4;color:#5B6884;font-size:13px;font-weight:700;width:40%;vertical-align:top">' . e($k) .
               '</td><td style="padding:10px 14px;border-bottom:1px solid #E3EAF4;color:#0F1B3A;font-size:15px">' . e($v) . '</td></tr>';
    }
    $wa = wa_link();
    $accent = $kind === 'resolvido' ? '#16A34A' : '#FE6500';
    $html = '<!doctype html><html><body style="margin:0;background:#F1F4F9;font-family:Arial,Helvetica,sans-serif">
<div style="max-width:600px;margin:0 auto;padding:24px 14px">
  <div style="background:#031436;color:#fff;border-radius:16px 16px 0 0;padding:22px">
    <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:' . ($kind === 'resolvido' ? '#4ADE80' : '#FF8A3D') . ';font-weight:700">' . e($kicker) . '</div>
    <div style="font-size:21px;font-weight:700;margin-top:6px">Gabinete do Vereador Maicon Gonçalves</div>
  </div>
  <div style="background:#fff;padding:22px 22px 8px;border-top:4px solid ' . $accent . '">
    <p style="font-size:16px;line-height:1.6;color:#0F1B3A;margin:0 0 12px">' . e($lead) . '</p>
    <p style="font-size:15px;line-height:1.6;color:#3B4763;margin:0">' . e($more) . '</p>
  </div>
  <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;background:#fff;border-collapse:collapse">' . $tr . '</table>
  <div style="background:#fff;border-radius:0 0 16px 16px;padding:20px 22px 24px">
    <a href="' . e($track) . '" style="display:inline-block;padding:13px 22px;border-radius:999px;background:' . $accent . ';color:#fff;text-decoration:none;font-weight:700">' . e($btn) . '</a>
    <p style="color:#5B6884;font-size:13px;line-height:1.6;margin:18px 0 0">Dúvidas? Responda este e-mail' . ($wa ? ' ou <a href="' . e($wa) . '" style="color:#0144C9">fale com a equipe pelo WhatsApp</a>' : '') . '.</p>
  </div>
  <p style="color:#8A96B0;font-size:12px;line-height:1.6;margin:14px 6px 0">Você recebeu este e-mail porque enviou a demanda ' . e($code) . ' pelo site ' . e(preg_replace('#^https?://#', '', rtrim(abs_url('/'), '/'))) . '.</p>
</div></body></html>';
    $text = strtoupper($kicker) . "\n\n$lead\n\n$more\n\n";
    foreach ($rows as [$k, $v]) $text .= "$k: $v\n";
    $text .= "\n$btn: $track\n";

    $from = (string) env('PUBLIC_FROM_EMAIL', '') ?: (string) env('MAIL_FROM_EMAIL', '');
    $fromName = (string) env('PUBLIC_FROM_NAME', '') ?: 'Vereador Maicon Gonçalves';
    $reply = (string) env('PUBLIC_REPLY_TO', '') ?: (S('email') ?: $from);
    [$ok, $info] = brevo_send($subject, $html, $text, [], filter_var($reply, FILTER_VALIDATE_EMAIL) ? ['email' => $reply, 'name' => 'Gabinete do Vereador Maicon Gonçalves'] : null, [
        'to' => [['email' => $t['email'], 'name' => $t['name'] ?: $t['email']]],
        'sender' => ['email' => $from, 'name' => $fromName],
        'tag' => 'demanda-' . $kind,
    ]);
    app_log('avisos', $code . ' | ' . $kind . ' | ' . $t['email'] . ' | ' . ($ok ? 'enviado ' . $info : 'falhou: ' . $info));
    return [$ok, $info];
}
