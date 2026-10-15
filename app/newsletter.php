<?php
/**
 * Aviso de artigo novo para quem aceitou receber novidades (tabela subscribers).
 * Um e-mail por pessoa, cada um com o seu link de descadastro. Envia em paralelo pelo Brevo.
 * Remetente: PUBLIC_FROM_EMAIL / PUBLIC_FROM_NAME (o mesmo dos avisos de demanda).
 */
defined('APP') or exit;
require_once __DIR__ . '/tickets.php';

function subscribers_count(): int
{
    db_migrate();
    try { return (int) q('SELECT COUNT(*) FROM subscribers WHERE unsub_at IS NULL')->fetchColumn(); } catch (Throwable $ex) { return 0; }
}

function unsub_url(string $token): string { return abs_url('/descadastrar/') . '?t=' . $token; }

/** Monta o e-mail do artigo para uma pessoa. */
function post_mail(array $post, string $name, string $unsub): array
{
    $link = abs_url('/' . $post['slug'] . '/');
    $first = first_name($name);
    $cover = !empty($post['cover']) ? abs_url('/' . ltrim($post['cover'], '/')) : '';
    $lead = trim((string) ($post['excerpt'] ?: $post['meta_description']));
    $lead = $lead !== '' ? str_limit($lead, 220) : '';
    $html = '<!doctype html><html><body style="margin:0;background:#F1F4F9;font-family:Arial,Helvetica,sans-serif">
<div style="max-width:600px;margin:0 auto;padding:24px 14px">
  <div style="background:#031436;color:#fff;border-radius:16px 16px 0 0;padding:22px">
    <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#FF8A3D;font-weight:700">Novo artigo no site</div>
    <div style="font-size:16px;font-weight:700;margin-top:6px;color:#C9D5EE">Vereador Maicon Gonçalves</div>
  </div>' .
    ($cover ? '<a href="' . e($link) . '"><img src="' . e($cover) . '" alt="" width="600" style="display:block;width:100%;height:auto;border:0"></a>' : '') . '
  <div style="background:#fff;border-radius:0 0 16px 16px;padding:22px 22px 26px;border-top:4px solid #FE6500">
    <p style="font-size:15px;color:#3B4763;margin:0 0 10px">Olá' . ($first !== '' ? ', ' . e($first) : '') . '! Tem artigo novo no site:</p>
    <h1 style="font-size:22px;line-height:1.3;color:#0F1B3A;margin:0 0 12px">' . e($post['title']) . '</h1>' .
    ($lead !== '' ? '<p style="font-size:15px;line-height:1.6;color:#3B4763;margin:0 0 18px">' . e($lead) . '</p>' : '') . '
    <a href="' . e($link) . '" style="display:inline-block;padding:13px 22px;border-radius:999px;background:#FE6500;color:#fff;text-decoration:none;font-weight:700">Ler o artigo</a>
  </div>
  <p style="color:#8A96B0;font-size:12px;line-height:1.6;margin:14px 6px 0">Você recebe este e-mail porque pediu para receber novidades do mandato do vereador Maicon Gonçalves.
    Não quer mais receber? <a href="' . e($unsub) . '" style="color:#5B6884">Descadastrar</a>.</p>
</div></body></html>';
    $text = "NOVO ARTIGO NO SITE\n\n" . $post['title'] . "\n\n" . ($lead !== '' ? $lead . "\n\n" : '') . "Ler o artigo: $link\n\nPara não receber mais: $unsub\n";
    return ['subject' => 'Novo artigo no site: ' . $post['title'], 'html' => $html, 'text' => $text];
}

/**
 * Envia o aviso do artigo para todos os inscritos ativos.
 * Retorna [enviados, falhas, mensagem de erro (se houver)].
 */
function post_notify(array $post): array
{
    $key = (string) env('BREVO_API_KEY', '');
    $from = (string) env('PUBLIC_FROM_EMAIL', '') ?: (string) env('MAIL_FROM_EMAIL', '');
    if ($key === '' || $from === '') return [0, 0, 'BREVO_API_KEY ou o remetente não estão configurados no .env'];
    if (!function_exists('curl_multi_init')) return [0, 0, 'extensão cURL do PHP não está ativa'];
    $subs = q('SELECT email, name, token FROM subscribers WHERE unsub_at IS NULL ORDER BY id')->fetchAll();
    if (!$subs) return [0, 0, 'ninguém inscrito ainda'];
    @set_time_limit(600);
    ignore_user_abort(true);

    $fromName = (string) env('PUBLIC_FROM_NAME', '') ?: 'Vereador Maicon Gonçalves';
    $reply = (string) env('PUBLIC_REPLY_TO', '') ?: (S('email') ?: $from);
    $url = (string) env('BREVO_API_URL', 'https://api.brevo.com/v3/smtp/email');
    $ok = 0; $fail = 0; $lastErr = '';
    foreach (array_chunk($subs, 8) as $chunk) { // 8 envios ao mesmo tempo
        $mh = curl_multi_init();
        $hs = [];
        foreach ($chunk as $s) {
            $unsub = unsub_url($s['token']);
            $m = post_mail($post, (string) $s['name'], $unsub);
            $payload = [
                'sender' => ['email' => $from, 'name' => $fromName],
                'to' => [['email' => $s['email'], 'name' => $s['name'] ?: $s['email']]],
                'subject' => $m['subject'], 'htmlContent' => $m['html'], 'textContent' => $m['text'],
                'tags' => ['artigo-novo'],
                'headers' => ['List-Unsubscribe' => '<' . $unsub . '>'],
            ];
            if (filter_var($reply, FILTER_VALIDATE_EMAIL)) $payload['replyTo'] = ['email' => $reply, 'name' => 'Gabinete do Vereador Maicon Gonçalves'];
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 40, CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['accept: application/json', 'content-type: application/json', 'api-key: ' . $key],
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            curl_multi_add_handle($mh, $ch);
            $hs[] = [$ch, $s['email']];
        }
        do { $st = curl_multi_exec($mh, $running); if ($running) curl_multi_select($mh, 1); } while ($running && $st === CURLM_OK);
        foreach ($hs as [$ch, $email]) {
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code >= 200 && $code < 300) $ok++;
            else { $fail++; $lastErr = 'Brevo respondeu ' . $code . ' para ' . $email . ': ' . substr((string) curl_multi_getcontent($ch), 0, 200); }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
    }
    app_log('artigos', 'aviso "' . $post['title'] . '": ' . $ok . ' enviados, ' . $fail . ' falhas' . ($lastErr ? ' | ' . $lastErr : ''));
    return [$ok, $fail, $lastErr];
}
