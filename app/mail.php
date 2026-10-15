<?php
defined('APP') or exit;

/**
 * Envia um e-mail transacional pela API do Brevo.
 * $attachments: lista de ['name' => 'foto.jpg', 'content' => bytes]
 * $opts (para e-mails ao cidadão): 'to' => [['email' => .., 'name' => ..]], 'sender' => ['email' => .., 'name' => ..], 'tag' => '..'
 *   Sem 'to', vai para o gabinete (MAIL_TO, com MAIL_BCC).
 * Retorna [true, messageId] ou [false, mensagem de erro].
 */
function brevo_send(string $subject, string $html, string $text, array $attachments = [], ?array $replyTo = null, array $opts = []): array
{
    $key = (string) env('BREVO_API_KEY', '');
    $sender = $opts['sender'] ?? ['email' => (string) env('MAIL_FROM_EMAIL', ''), 'name' => (string) env('MAIL_FROM_NAME', 'Site')];
    if ($key === '' || $sender['email'] === '') return [false, 'BREVO_API_KEY ou MAIL_FROM_EMAIL não configurados no .env'];
    if (!function_exists('curl_init')) return [false, 'extensão cURL do PHP não está ativa'];

    $to = $opts['to'] ?? [];
    if (!$to) {
        foreach (explode(',', (string) env('MAIL_TO', 'vereadormaicongoncalves@gmail.com')) as $addr) {
            if (trim($addr) !== '') $to[] = ['email' => trim($addr), 'name' => (string) env('MAIL_TO_NAME', '')];
        }
    }
    $payload = [
        'sender' => $sender,
        'to' => $to,
        'subject' => $subject,
        'htmlContent' => $html,
        'textContent' => $text,
        'tags' => [$opts['tag'] ?? 'demanda-gabinete'],
    ];
    $bcc = empty($opts['to']) ? array_filter(array_map('trim', explode(',', (string) env('MAIL_BCC', '')))) : [];
    if ($bcc) $payload['bcc'] = array_map(function ($e) { return ['email' => $e]; }, array_values($bcc));
    if ($replyTo) $payload['replyTo'] = $replyTo;
    if ($attachments) {
        $payload['attachment'] = array_map(function ($a) { return ['name' => $a['name'], 'content' => base64_encode($a['content'])]; }, $attachments);
    }

    $ch = curl_init((string) env('BREVO_API_URL', 'https://api.brevo.com/v3/smtp/email'));
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['accept: application/json', 'content-type: application/json', 'api-key: ' . $key],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($body === false) return [false, 'falha de conexão com o Brevo: ' . $err];
    $json = json_decode((string) $body, true);
    if ($code >= 200 && $code < 300) return [true, $json['messageId'] ?? ''];
    return [false, 'Brevo respondeu ' . $code . ': ' . ($json['message'] ?? substr((string) $body, 0, 300))];
}
