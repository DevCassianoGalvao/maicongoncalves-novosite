<?php
/**
 * Gerador de ofício (Word .docx) a partir de uma demanda do Friburgo no Mapa.
 * Segue o modelo do gabinete: cabeçalho com brasões, destinatário, saudação, texto formal
 * montado por tipo de demanda, data por extenso, foto ao lado da data e anexo com as demais fotos.
 * O número do ofício pode ficar em branco (destacado em amarelo) para ser preenchido à mão.
 */
defined('APP') or exit;
require_once __DIR__ . '/tickets.php';

/** Como cada órgão aparece no ofício: [linha do destinatário, cargo masculino, cargo feminino]. */
function oficio_destino(string $slug, string $g): array
{
    $name = SECRETARIAS[$slug] ?? 'Prefeitura Municipal de Nova Friburgo';
    $f = $g === 'f';
    if (strpos($slug, 'sub_') === 0) {
        $place = ['sub_olaria' => 'Olaria', 'sub_conselheiro' => 'Conselheiro Paulino'][$slug] ?? trim(preg_replace('/^Subprefeitura\s*-\s*/u', '', $name));
        return ['À Subprefeitura de ' . $place, $f ? 'Senhora Subprefeita' : 'Senhor Subprefeito'];
    }
    if ($slug === 'gabinete') return ['Ao Gabinete do Prefeito', $f ? 'Senhora Prefeita' : 'Senhor Prefeito'];
    if ($slug === 'controladoria') return ['À ' . $name, $f ? 'Senhora Controladora-Geral' : 'Senhor Controlador-Geral'];
    if ($slug === 'procuradoria') return ['À ' . $name, $f ? 'Senhora Procuradora-Geral' : 'Senhor Procurador-Geral'];
    if (strpos($name, 'Secretaria Executiva') === 0) return ['À ' . $name, $f ? 'Senhora Secretária Executiva' : 'Senhor Secretário Executivo'];
    if (!isset(SECRETARIAS[$slug])) return ['À ' . $name, $f ? 'Senhora Prefeita' : 'Senhor Prefeito'];
    return ['À ' . $name, $f ? 'Senhora Secretária' : 'Senhor Secretário'];
}

/** Texto formal por tipo de demanda: [o que se pede (com {loc}), por quê]. Sem IA: modelos fixos, revisáveis no Word. */
const OFICIO_MODELOS = [
    'buraco'       => ['a realização de operação tapa-buracos e o reparo do pavimento {loc}', 'a via apresenta buracos que oferecem risco a motoristas, motociclistas e pedestres, podendo causar acidentes e danos aos veículos'],
    'pavimentacao' => ['a recuperação do pavimento {loc}', 'o asfalto/calçamento da via encontra-se danificado, dificultando a circulação de veículos e pedestres e comprometendo a segurança de quem transita pelo local'],
    'calcada'      => ['a recuperação da calçada {loc}', 'a calçada encontra-se danificada, dificultando a passagem de pedestres e oferecendo risco de quedas, especialmente a idosos, crianças e pessoas com mobilidade reduzida'],
    'iluminacao'   => ['a vistoria e a manutenção da iluminação pública {loc}', 'há pontos de iluminação apagados ou com defeito, deixando o local escuro e comprometendo a segurança de moradores e pedestres no período noturno'],
    'lixo'         => ['o recolhimento de lixo e entulho {loc}', 'há acúmulo de lixo e entulho no local, o que favorece a proliferação de insetos, roedores e outros vetores de doenças'],
    'limpeza'      => ['a realização de capina, roçada e limpeza {loc}', 'o mato alto e a sujeira acumulada no local comprometem a circulação, favorecem o aparecimento de animais peçonhentos e prejudicam a qualidade de vida dos moradores'],
    'agua'         => ['a vistoria e as providências necessárias quanto à rede de água e esgoto {loc}', 'há problemas na rede de abastecimento de água e/ou de esgoto no local, causando transtornos aos moradores e riscos à saúde pública'],
    'drenagem'     => ['a limpeza e a desobstrução dos bueiros e da rede de drenagem {loc}', 'os bueiros e a rede de drenagem encontram-se obstruídos, provocando acúmulo de água e alagamentos em dias de chuva'],
    'encosta'      => ['a vistoria técnica da encosta e a adoção das medidas de contenção necessárias {loc}', 'a encosta apresenta sinais de instabilidade, com risco de deslizamento que pode colocar em perigo moradores e imóveis da região'],
    'arvore'       => ['a vistoria e a poda, ou a remoção, se necessário, de árvore {loc}', 'a árvore oferece risco de queda de galhos sobre pedestres, veículos, imóveis e a rede elétrica'],
    'sinalizacao'  => ['a vistoria e a adequação da sinalização de trânsito {loc}', 'a sinalização no local encontra-se ausente, apagada ou danificada, comprometendo a segurança de motoristas e pedestres'],
    'transporte'   => ['a avaliação e a melhoria do serviço de transporte público que atende a região {loc}', 'os moradores enfrentam dificuldades com o transporte público, como atrasos, lotação e falta de horários'],
    'saude'        => ['a avaliação e as providências cabíveis quanto ao atendimento de saúde {loc}', 'os moradores enfrentam dificuldades no acesso aos serviços de saúde na região'],
    'educacao'     => ['a vistoria e as providências cabíveis referentes à unidade de ensino {loc}', 'há problemas que comprometem o atendimento aos alunos e a segurança da comunidade escolar'],
    'assistencia'  => ['o atendimento e o acompanhamento pela rede de assistência social {loc}', 'há situações de vulnerabilidade social que necessitam da atenção do poder público'],
    'praca'        => ['a realização de vistoria e manutenção na praça e área de lazer localizada {loc}', 'os equipamentos e a estrutura do espaço encontram-se danificados, apresentando riscos à segurança das crianças e das famílias que frequentam o local'],
    'esporte'      => ['a vistoria e a manutenção do espaço esportivo localizado {loc}', 'a estrutura encontra-se deteriorada, comprometendo a prática esportiva e a segurança dos usuários'],
    'cultura'      => ['a avaliação e as providências cabíveis referentes ao espaço cultural e turístico localizado {loc}', 'há problemas de conservação e manutenção que prejudicam o uso do espaço pela população e pelos visitantes'],
    'animais'      => ['a adoção das providências cabíveis quanto à situação de animais {loc}', 'há animais em situação de abandono ou risco, o que demanda atenção quanto ao bem-estar animal e à saúde pública'],
    'meioambiente' => ['a fiscalização e as providências cabíveis quanto a problema ambiental {loc}', 'há danos ao meio ambiente que prejudicam a qualidade de vida dos moradores'],
    'acessib'      => ['a adequação das condições de acessibilidade {loc}', 'o local não oferece condições adequadas de acessibilidade, dificultando a circulação de pessoas com deficiência, idosos e pessoas com mobilidade reduzida'],
    'seguranca'    => ['o reforço do patrulhamento e as providências cabíveis {loc}', 'há situações que comprometem a segurança e a ordem pública na região'],
    'obras'        => ['a vistoria e a fiscalização da obra {loc}', 'há problemas na execução ou na conservação da obra, causando transtornos aos moradores'],
    '_'            => ['a realização de vistoria e a adoção das providências cabíveis {loc}', 'há problemas no local que afetam a segurança e a qualidade de vida dos moradores'],
];

/** "na Rua X, 50, no bairro Y" (vai em negrito no texto). */
function oficio_local(array $t): string
{
    $a = trim((string) $t['address']);
    $b = trim((string) $t['neighborhood']);
    if ($a !== '') {
        $prep = preg_match('/^(largo|beco|caminho|loteamento|condom[ií]nio|parque|morro|alto|conjunto|s[ií]tio)\b/iu', $a) ? 'no' : 'na';
        return $prep . ' ' . rtrim($a, ' ,.') . ($b !== '' && mb_stripos($a, $b) === false ? ', no bairro ' . $b : '');
    }
    if ($b !== '') return 'no bairro ' . $b;
    $g = trim((string) ($t['geo_address'] ?? ''));
    return $g !== '' ? 'na região de ' . $g : '';
}

function oficio_data(int $ts): string
{
    $m = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    return 'Nova Friburgo, ' . (int) date('j', $ts) . ' de ' . $m[(int) date('n', $ts) - 1] . ' de ' . date('Y', $ts);
}

/** Parágrafos do corpo: lista de listas de [texto, negrito]. */
function oficio_corpo(array $t, string $complemento, int $nFotos): array
{
    [$pedido, $motivo] = OFICIO_MODELOS[$t['category']] ?? OFICIO_MODELOS['_'];
    $c = trim(preg_replace('/\s+/u', ' ', $complemento));
    if ($c !== '') $motivo = rtrim(mb_strtolower(mb_substr($c, 0, 1), 'UTF-8') . mb_substr($c, 1), ' .');
    $loc = oficio_local($t);
    [$antes, $depois] = array_pad(explode('{loc}', $pedido, 2), 2, '');
    $p1 = [['Solicito ' . rtrim($antes) . ($loc !== '' ? ' ' : ''), false]];
    if ($loc !== '') $p1[] = [$loc, true];
    $p1[] = [ltrim($depois) . ', tendo em vista que, segundo relatos dos moradores, ' . $motivo . '.', false];
    $p1[0][0] = preg_replace('/\s+,/', ',', $p1[0][0]);
    $p2 = [['Diante disso, solicitamos que o setor responsável avalie as condições do local e providencie as medidas necessárias, garantindo a segurança e o bem-estar dos moradores da região.' .
        ($nFotos ? ' Encaminhamos, em anexo, ' . ($nFotos > 1 ? 'registros fotográficos' : 'registro fotográfico') . ' do local.' : ''), false]];
    return [$p1, $p2];
}

/* ---------- montagem do .docx (WordprocessingML) ---------- */
function ox(string $s): string { return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }
function oficio_run(string $text, array $o = []): string
{
    $pr = '';
    if (!empty($o['font'])) $pr .= '<w:rFonts w:ascii="' . $o['font'] . '" w:hAnsi="' . $o['font'] . '" w:cs="' . $o['font'] . '"/>';
    if (!empty($o['b'])) $pr .= '<w:b/><w:bCs/>';
    if (!empty($o['sz'])) $pr .= '<w:sz w:val="' . $o['sz'] . '"/><w:szCs w:val="' . $o['sz'] . '"/>';
    if (!empty($o['hl'])) $pr .= '<w:highlight w:val="yellow"/>';
    return '<w:r>' . ($pr ? "<w:rPr>$pr</w:rPr>" : '') . '<w:t xml:space="preserve">' . ox($text) . '</w:t></w:r>';
}
function oficio_p(string $runs, array $o = []): string
{
    $pr = '';
    if (!empty($o['keep'])) $pr .= '<w:keepNext/>';
    if (!empty($o['break'])) $pr .= '<w:pageBreakBefore/>';
    $pr .= '<w:spacing w:before="' . ($o['before'] ?? 0) . '" w:after="' . ($o['after'] ?? 160) . '" w:line="276" w:lineRule="auto"/>';
    if (!empty($o['jc'])) $pr .= '<w:jc w:val="' . $o['jc'] . '"/>';
    return "<w:p><w:pPr>$pr</w:pPr>$runs</w:p>";
}
function oficio_img(string $rid, int $id, int $cx, int $cy, string $name): string
{
    return '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="' . $cx . '" cy="' . $cy . '"/><wp:docPr id="' . $id . '" name="' . ox($name) . '"/>' .
        '<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>' .
        '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">' .
        '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="' . $id . '" name="' . ox($name) . '"/><pic:cNvPicPr/></pic:nvPicPr>' .
        '<pic:blipFill><a:blip r:embed="' . $rid . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>' .
        '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic>' .
        '</a:graphicData></a:graphic></wp:inline></w:drawing></w:r>';
}
/** Tamanho em EMU para caber em maxW x maxH polegadas mantendo a proporção. */
function oficio_fit(int $w, int $h, float $maxW, float $maxH): array
{
    $s = min($maxW / $w, $maxH / $h);
    return [(int) round($w * $s * 914400), (int) round($h * $s * 914400)];
}
/** Foto da demanda como JPEG (o Word não abre WEBP). Retorna [bytes, w, h] ou null. */
function oficio_foto_jpeg(string $file): ?array
{
    $info = @getimagesize($file);
    if (!$info) return null;
    $im = null;
    if ($info[2] === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) $im = @imagecreatefromwebp($file);
    elseif ($info[2] === IMAGETYPE_JPEG) $im = @imagecreatefromjpeg($file);
    elseif ($info[2] === IMAGETYPE_PNG) $im = @imagecreatefrompng($file);
    if (!$im) return null;
    $w = imagesx($im); $h = imagesy($im);
    $max = 1400;
    if (max($w, $h) > $max) {
        $s = $max / max($w, $h);
        $nw = (int) round($w * $s); $nh = (int) round($h * $s);
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($im);
        $im = $dst; $w = $nw; $h = $nh;
    }
    ob_start();
    imagejpeg($im, null, 82);
    $bytes = (string) ob_get_clean();
    imagedestroy($im);
    return [$bytes, $w, $h];
}

/**
 * Gera o .docx. $o: secretaria (slug), g ('m'|'f'), numero (pode ser vazio), complemento, data (timestamp).
 * Retorna o caminho de um arquivo temporário.
 */
function oficio_docx(array $t, array $o): string
{
    if (!class_exists('ZipArchive')) throw new RuntimeException('O servidor não tem a extensão ZIP do PHP.');
    $ano = date('Y', $o['data']);
    $num = trim((string) $o['numero']);
    [$dest, $cargo] = oficio_destino((string) $o['secretaria'], $o['g']);

    // fotos
    $media = []; // nome => bytes
    $fotos = [];
    foreach ((json_decode((string) $t['photos'], true) ?: []) as $i => $p) {
        $f = ticket_photo_path((string) ($p['src'] ?? ''));
        if (!$f || !($j = oficio_foto_jpeg($f))) continue;
        $name = 'foto' . ($i + 1) . '.jpg';
        $media[$name] = $j[0];
        $fotos[] = ['name' => $name, 'w' => $j[1], 'h' => $j[2], 'rid' => 'rIdF' . ($i + 1)];
    }
    [$p1, $p2] = oficio_corpo($t, (string) $o['complemento'], count($fotos));
    $runs = function (array $segs) { return implode('', array_map(function ($s) { return oficio_run($s[0], ['b' => $s[1]]); }, $segs)); };
    $numRuns = $num !== '' ? oficio_run($num . '/' . $ano) : oficio_run('____', ['hl' => true]) . oficio_run('/' . $ano);

    $body = oficio_p(oficio_run('Ofício: nº ') . $numRuns, ['jc' => 'right', 'after' => 240]);
    $body .= oficio_p(oficio_run($dest), ['after' => 600]);
    $body .= oficio_p(oficio_run($cargo . ','), ['after' => 360]);
    $body .= oficio_p($runs($p1), ['jc' => 'both']);
    $body .= oficio_p($runs($p2), ['jc' => 'both']);
    $body .= oficio_p(oficio_run('Atenciosamente,'), ['before' => 120, 'after' => 360, 'keep' => true]);

    // foto 1 à esquerda, data e espaço da assinatura à direita (tabela sem bordas)
    $data = oficio_p(oficio_run(oficio_data($o['data'])), ['jc' => 'right', 'after' => 0]);
    $left = oficio_p('', ['after' => 0]);
    if ($fotos) { [$cx, $cy] = oficio_fit($fotos[0]['w'], $fotos[0]['h'], 2.7, 3.0); $left = oficio_p(oficio_img($fotos[0]['rid'], 10, $cx, $cy, 'Foto 1'), ['after' => 0]); }
    $cell = function (int $w, string $content, string $valign) { return '<w:tc><w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/><w:vAlign w:val="' . $valign . '"/></w:tcPr>' . $content . '</w:tc>'; };
    $body .= '<w:tbl><w:tblPr><w:tblW w:w="9000" w:type="dxa"/><w:tblBorders><w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/><w:insideH w:val="nil"/><w:insideV w:val="nil"/></w:tblBorders><w:tblLayout w:type="fixed"/><w:tblLook w:val="0000"/></w:tblPr>' .
        '<w:tblGrid><w:gridCol w:w="4200"/><w:gridCol w:w="4800"/></w:tblGrid><w:tr><w:trPr><w:cantSplit/></w:trPr>' .
        $cell(4200, $left, 'top') . $cell(4800, $data . oficio_p('', ['after' => 1400]), 'center') . '</w:tr></w:tbl>';

    // anexo com as demais fotos
    if (count($fotos) > 1) {
        $body .= oficio_p(oficio_run('ANEXO – REGISTRO FOTOGRÁFICO', ['b' => true]), ['jc' => 'center', 'break' => true, 'after' => 80]);
        $body .= oficio_p(oficio_run('Referente ao Ofício nº ') . ($num !== '' ? oficio_run($num . '/' . $ano) : oficio_run('____', ['hl' => true]) . oficio_run('/' . $ano)) .
            oficio_run(' · Friburgo no Mapa, protocolo ' . ticket_code((int) $t['id'])), ['jc' => 'center', 'after' => 240]);
        foreach (array_slice($fotos, 1) as $k => $f) {
            [$cx, $cy] = oficio_fit($f['w'], $f['h'], 5.6, 3.2);
            $body .= oficio_p(oficio_img($f['rid'], 20 + $k, $cx, $cy, 'Foto ' . ($k + 2)), ['jc' => 'center', 'after' => 60]);
            $body .= oficio_p(oficio_run('Foto ' . ($k + 2), ['sz' => 18]), ['jc' => 'center', 'after' => 200]);
        }
    }

    $ns = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"';
    $sect = '<w:sectPr><w:headerReference w:type="default" r:id="rIdH"/><w:footerReference w:type="default" r:id="rIdFt"/><w:pgSz w:w="11906" w:h="16838"/>' .
        '<w:pgMar w:top="3260" w:right="1300" w:bottom="2300" w:left="1500" w:header="560" w:footer="400" w:gutter="0"/></w:sectPr>';
    $doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document ' . $ns . '><w:body>' . $body . $sect . '</w:body></w:document>';

    [$hx, $hy] = oficio_fit(1171, 425, 3.6, 1.4);
    $header = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:hdr ' . $ns . '>' .
        oficio_p(oficio_img('rIdLogo', 1, $hx, $hy, 'Brasões'), ['jc' => 'center', 'after' => 200]) .
        oficio_p(oficio_run('GABINETE DO VEREADOR MAICON GONÇALVES', ['b' => true, 'sz' => 24]), ['jc' => 'center', 'after' => 0]) . '</w:hdr>';
    [$fx, $fy] = oficio_fit(1356, 244, 3.9, 0.8);
    $footer = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:ftr ' . $ns . '>' .
        oficio_p(oficio_img('rIdRod', 2, $fx, $fy, 'Rodapé'), ['jc' => 'center', 'after' => 0]) .
        oficio_p(oficio_run('R. Farinha Filho, 50 – Centro, Nova Friburgo – RJ CEP 28610-280 – Tel (22) 2524-1700', ['font' => 'Times New Roman', 'sz' => 18]), ['jc' => 'center', 'after' => 0]) .
        oficio_p(oficio_run('https://maicongoncalves.com.br/', ['font' => 'Times New Roman', 'sz' => 18]), ['jc' => 'center', 'after' => 0]) . '</w:ftr>';

    $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
        '<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:eastAsia="Calibri" w:cs="Calibri"/><w:sz w:val="22"/><w:szCs w:val="22"/><w:lang w:val="pt-BR"/></w:rPr></w:rPrDefault>' .
        '<w:pPrDefault><w:pPr><w:spacing w:after="160" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>' .
        '<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style></w:styles>';

    $rel = function (string $id, string $type, string $target) { return '<Relationship Id="' . $id . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/' . $type . '" Target="' . $target . '"/>'; };
    $docRels = $rel('rIdS', 'styles', 'styles.xml') . $rel('rIdH', 'header', 'header1.xml') . $rel('rIdFt', 'footer', 'footer1.xml');
    foreach ($fotos as $f) $docRels .= $rel($f['rid'], 'image', 'media/' . $f['name']);
    $wrapRels = function (string $r) { return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $r . '</Relationships>'; };

    $tmp = tempnam(sys_get_temp_dir(), 'oficio');
    $z = new ZipArchive();
    if ($z->open($tmp, ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Não foi possível criar o arquivo.');
    $z->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="jpg" ContentType="image/jpeg"/>' .
        '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' .
        '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>' .
        '<Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>' .
        '<Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>' .
        '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/></Types>');
    $z->addFromString('_rels/.rels', $wrapRels('<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' .
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'));
    $z->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
        '<dc:title>' . ox('Ofício – ' . cat_label($t['category']) . ' – ' . ticket_code((int) $t['id'])) . '</dc:title><dc:creator>Gabinete do Vereador Maicon Gonçalves</dc:creator>' .
        '<dcterms:created xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:created></cp:coreProperties>');
    $z->addFromString('word/document.xml', $doc);
    $z->addFromString('word/styles.xml', $styles);
    $z->addFromString('word/header1.xml', $header);
    $z->addFromString('word/footer1.xml', $footer);
    $z->addFromString('word/_rels/document.xml.rels', $wrapRels($docRels));
    $z->addFromString('word/_rels/header1.xml.rels', $wrapRels($rel('rIdLogo', 'image', 'media/cabecalho.jpg')));
    $z->addFromString('word/_rels/footer1.xml.rels', $wrapRels($rel('rIdRod', 'image', 'media/rodape.jpg')));
    $z->addFile(__DIR__ . '/oficio/cabecalho.jpg', 'word/media/cabecalho.jpg');
    $z->addFile(__DIR__ . '/oficio/rodape.jpg', 'word/media/rodape.jpg');
    foreach ($media as $n => $b) $z->addFromString('word/media/' . $n, $b);
    $z->close();
    return $tmp;
}
