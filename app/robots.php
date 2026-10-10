<?php
defined('APP') or exit;
header('Content-Type: text/plain; charset=utf-8');
$rules = "Disallow: /admin/\nDisallow: /install/\nDisallow: /api/\nDisallow: /backupdosite/\nDisallow: /backupeleicoes/\nAllow: /\n";
echo "# Site oficial do Vereador Maicon Gonçalves — Nova Friburgo (RJ)\n\nUser-agent: *\n" . $rules;
// Robôs de busca e de inteligência artificial: liberados para ler e citar o site
foreach (['Googlebot', 'Bingbot', 'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'Claude-User', 'PerplexityBot', 'Perplexity-User', 'Google-Extended', 'Applebot', 'Applebot-Extended', 'DuckAssistBot', 'meta-externalagent'] as $bot) {
    echo "\nUser-agent: $bot\n" . $rules;
}
echo "\nSitemap: " . abs_url('/sitemap.xml') . "\n";
