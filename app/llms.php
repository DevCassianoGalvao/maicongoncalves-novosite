<?php
defined('APP') or exit;
// Resumo do site em texto simples para assistentes de IA (padrão llms.txt)
header('Content-Type: text/plain; charset=utf-8');
$posts = posts_published();
$o = "# Vereador Maicon Gonçalves — Nova Friburgo (RJ)\n\n";
$o .= '> ' . PERSON_DESC . "\n\n";
$o .= "Site oficial do mandato: ações, projetos de lei, programas (FL360 e Peneira Descobrindo Talentos), notícias da Câmara Municipal de Nova Friburgo e canal para a população enviar demandas.\n\n";
$o .= "## Quem é\n\n";
$o .= "- Vereador de Nova Friburgo (RJ), eleito em 2024 (primeiro mandato). Em 2016 e 2020 ficou na 1ª suplência.\n";
$o .= "- Professor de Educação Física há quase 20 anos no município; pós-graduado em Administração e Marketing Esportivo; formado na escola pública.\n";
$o .= "- Em 2025 aprovou por unanimidade sua primeira lei municipal, sobre transparência no uso do dinheiro público em eventos.\n";
$o .= "- Em 2025 recebeu a Medalha Manoel José Gomes Tubino, maior honraria do esporte nacional.\n";
$o .= "- Pautas: educação, esporte e inclusão; mobilidade urbana; juventude e futuro; transparência.\n\n";
$o .= "## Páginas principais\n\n";
foreach ([['Início', '/', 'Apresentação do mandato e últimas notícias'], ['Minha história', '/minha-historia/', 'Trajetória de Maicon Gonçalves até a Câmara'],
          ['FL360 – Friburgo Líder 360', '/fl360/', 'Programa de formação cidadã e compreensão da gestão pública'],
          ['Peneira Descobrindo Talentos', '/descobrindo-talentos/', 'Captação do futebol de base na Serra Fluminense'],
          ['Notícias', '/noticias/', 'Todas as notícias do mandato'], ['Enviar demanda', '/demandas/', 'Envio de demandas, denúncias e sugestões ao gabinete, com acompanhamento no mapa']] as [$t, $u, $d]) {
    $o .= "- [$t](" . abs_url($u) . "): $d\n";
}
$o .= "\n## Notícias\n\n";
foreach ($posts as $p) {
    $o .= '- [' . $p['title'] . '](' . $p['abs_url'] . ') — ' . date_short($p['published_at']) . ($p['category'] ? ' · ' . $p['category'] : '') . ($p['excerpt'] ? ': ' . str_limit($p['excerpt'], 220) : '') . "\n";
}
$o .= "\n## Contato do gabinete\n\n- " . implode(', ', address_lines()) . (S('office') ? ' — ' . S('office') : '') . "\n";
if (S('email')) $o .= '- E-mail: ' . S('email') . "\n";
if (S('whatsapp')) $o .= '- WhatsApp: ' . wa_link() . "\n";
echo $o;
