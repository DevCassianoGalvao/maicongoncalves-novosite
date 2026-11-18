<?php
/**
 * Impostômetro de Nova Friburgo.
 *
 * Fonte única: o que a própria Prefeitura declara ao Tesouro Nacional (Siconfi, API pública).
 *  - Ano fechado: DCA (Declaração de Contas Anuais), entregue até 30/abril do ano seguinte.
 *      Anexo I-C = receitas, I-D = despesas por tipo (pessoal, custeio, obras), I-E = despesas por área (função).
 *  - Ano corrente: RREO Anexo 01 (relatório bimestral, até 30 dias depois de cada bimestre).
 * População: IBGE (estimativas anuais).
 * O resumo fica em storage/impostometro.json (atualização semanal). Se a API cair, a página usa o último resumo
 * ou, na primeira vez, o retrato salvo em app/data/impostometro-base.json.
 */
defined('APP') or exit;

const IM_IBGE = 3303401;
const IM_ANOS = 7;          // quantos anos fechados mostrar
const IM_MAX_IDADE = 3 * 86400;

const IM_FUNCOES = [ // nome simples de cada área (função de governo)
    '01' => 'Câmara Municipal', '02' => 'Judiciário', '03' => 'Procuradoria e Justiça', '04' => 'Administração da Prefeitura', '05' => 'Defesa Nacional',
    '06' => 'Segurança Pública', '07' => 'Relações Exteriores', '08' => 'Assistência Social', '09' => 'Aposentadorias dos servidores', '10' => 'Saúde',
    '11' => 'Trabalho e emprego', '12' => 'Educação', '13' => 'Cultura', '14' => 'Direitos da cidadania', '15' => 'Urbanismo (manutenção da cidade e obras urbanas)',
    '16' => 'Habitação', '17' => 'Saneamento', '18' => 'Meio ambiente', '19' => 'Ciência e tecnologia', '20' => 'Agricultura',
    '21' => 'Organização agrária', '22' => 'Indústria', '23' => 'Turismo e comércio', '24' => 'Comunicações', '25' => 'Energia',
    '26' => 'Transporte', '27' => 'Esporte e lazer', '28' => 'Dívidas e outros encargos', '99' => 'Reserva de contingência',
];
const IM_ICONES = ['10' => 'heartbeat', '12' => 'graduation-cap', '04' => 'buildings', '15' => 'road-horizon', '09' => 'users-three', '01' => 'bank',
    '23' => 'storefront', '08' => 'hand-heart', '26' => 'bus', '13' => 'mask-happy', '27' => 'soccer-ball', '20' => 'plant', '18' => 'tree', '17' => 'drop',
    '06' => 'shield-check', '28' => 'receipt', '16' => 'house-line'];

function im_http(string $url): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 40, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json'], CURLOPT_USERAGENT => 'Impostometro-NovaFriburgo/1.0 (site do vereador)']);
    $out = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($out === false || $code !== 200) return null;
    $j = json_decode((string) $out, true);
    return is_array($j) ? $j : null;
}
function im_siconfi(string $tipo, array $q): ?array
{
    $j = im_http('https://apidatalake.tesouro.gov.br/ords/siconfi/tt/' . $tipo . '?' . http_build_query($q + ['id_ente' => IM_IBGE]));
    return $j === null ? null : ($j['items'] ?? []);
}

/** Valor de uma conta numa coluna (soma se repetir). */
function im_v(array $items, string $cod, string $col, ?string $conta = null): float
{
    $t = 0.0;
    foreach ($items as $x) if ($x['cod_conta'] === $cod && $x['coluna'] === $col && ($conta === null || $x['conta'] === $conta)) $t += (float) $x['valor'];
    return $t;
}
/** Receita líquida da conta (bruta menos o que vai para o FUNDEB e outras deduções). */
function im_rec(array $items, string $cod): float
{
    return im_v($items, $cod, 'Receitas Brutas Realizadas') - im_v($items, $cod, 'Deduções - FUNDEB') - im_v($items, $cod, 'Outras Deduções da Receita');
}

/**
 * Acha a conta pelo nome (o plano de contas mudou em 2022, os códigos não batem entre anos).
 * Entre as contas com o código começando por $prefix e nome casando com $re, pega a de maior valor (a conta-mãe).
 */
function im_find(array $items, string $prefix, string $re): float
{
    $best = null; $bv = -1.0;
    foreach ($items as $x) {
        if ($x['coluna'] !== 'Receitas Brutas Realizadas' || strpos($x['cod_conta'], $prefix) !== 0 || !preg_match($re, (string) $x['conta'])) continue;
        if ((float) $x['valor'] > $bv) { $bv = (float) $x['valor']; $best = $x['cod_conta']; }
    }
    return $best === null ? 0.0 : im_rec($items, $best);
}

/** Resume um ano fechado (DCA). Retorna null se os anexos não vieram completos. */
function im_ano(int $ano): ?array
{
    $c = im_siconfi('dca', ['an_exercicio' => $ano, 'no_anexo' => 'DCA-Anexo I-C']);
    $d = im_siconfi('dca', ['an_exercicio' => $ano, 'no_anexo' => 'DCA-Anexo I-D']);
    $f = im_siconfi('dca', ['an_exercicio' => $ano, 'no_anexo' => 'DCA-Anexo I-E']);
    if (!$c || !$d || !$f) return null;

    $total = im_rec($c, 'ReceitasExcetoIntraOrcamentarias');
    $prop = [
        'iptu' => im_find($c, 'RO1.1.1', '/Predial e Territorial Urbana/iu'), 'iss' => im_find($c, 'RO1.1.1', '/Imposto sobre Servi[çc]os|Impostos sobre Servi[çc]os|ISSQN/iu'),
        'itbi' => im_find($c, 'RO1.1.1', '/Inter Vivos/iu'), 'irrf' => im_find($c, 'RO1.1.1', '/Imposto sobre a Renda/iu'),
        'taxas' => im_rec($c, 'RO1.1.2.0.00.0.0'), 'luz' => im_find($c, 'RO1.2', '/Ilumina[çc][ãa]o P[úu]blica/iu'),
    ];
    $prop['outros'] = max(0, im_rec($c, 'RO1.1.0.0.00.0.0') - $prop['iptu'] - $prop['iss'] - $prop['itbi'] - $prop['irrf'] - $prop['taxas']);
    $est = ['icms' => im_find($c, 'RO1.7.2', '/Cota-Parte do ICMS/iu'), 'ipva' => im_find($c, 'RO1.7.2', '/Cota-Parte do IPVA/iu')];
    $estT = im_rec($c, 'RO1.7.2.0.00.0.0') + im_rec($c, 'RO2.4.2.0.00.0.0');
    $est['outros'] = max(0, $estT - $est['icms'] - $est['ipva']);
    $uni = ['fpm' => im_find($c, 'RO1.7.1', '/Fundo de Participa[çc][ãa]o dos Munic/iu'),
        'sus' => im_find($c, 'RO1.7.1', '/Sistema [ÚU]nico de Sa[úu]de/iu') + im_find($c, 'RO2.4.1', '/Sistema [ÚU]nico de Sa[úu]de/iu'),
        'royalties' => im_find($c, 'RO1.7.1', '/Compensa[çc](?:[ãa]o|[õo]es) Financeira/iu')];
    $uniT = im_rec($c, 'RO1.7.1.0.00.0.0') + im_rec($c, 'RO2.4.1.0.00.0.0');
    $uni['outros'] = max(0, $uniT - $uni['fpm'] - $uni['sus'] - $uni['royalties']);
    $fundeb = im_find($c, 'RO1.7.5', '/Fundo de Manuten[çc][ãa]o/iu');
    $propT = array_sum($prop);
    $outras = max(0, $total - $propT - $estT - $uniT - $fundeb);

    // despesas por área (liquidadas = serviço entregue ou obra medida), sem as operações internas da Prefeitura
    $areas = [];
    foreach ($f as $x) {
        if ($x['coluna'] !== 'Despesas Liquidadas' || !preg_match('/^(\d{2}) - /', (string) $x['conta'], $m)) continue;
        $areas[$m[1]] = ($areas[$m[1]] ?? 0) + (float) $x['valor'];
    }
    arsort($areas);
    $despT = im_v($f, 'TotalDespesas', 'Despesas Liquidadas', 'Despesas Exceto Intraorçamentárias');
    if ($despT <= 0) $despT = array_sum($areas);
    $L = 'Despesas Liquidadas';
    $intra = function (string $g) use ($d, $L) { return im_v($d, 'DI' . $g . '.91.00.00.00', $L); };
    $tipo = [
        'pessoal' => im_v($d, 'DO3.1.00.00.00.00', $L) - $intra('3.1'),
        'custeio' => im_v($d, 'DO3.3.00.00.00.00', $L) - $intra('3.3'),
        'obras' => im_v($d, 'DO4.4.00.00.00.00', $L) - $intra('4.4') + im_v($d, 'DO4.5.00.00.00.00', $L),
        'divida' => im_v($d, 'DO3.2.00.00.00.00', $L) + im_v($d, 'DO4.6.00.00.00.00', $L) - $intra('3.2') - $intra('4.6'),
    ];
    $obrasDet = ['obras' => im_v($d, 'DO4.4.90.51.00.00', $L), 'equip' => im_v($d, 'DO4.4.90.52.00.00', $L)];

    // conferências: sem isso o ano não entra (melhor mostrar o ano anterior do que um número quebrado)
    if ($total <= 0 || $despT <= 0 || !$areas) return null;
    if (abs(array_sum($areas) - $despT) / $despT > 0.02) return null;
    if (abs(array_sum($tipo) - $despT) / $despT > 0.05) return null;

    $r = function ($a) { return is_array($a) ? array_map(function ($v) { return round($v, 2); }, $a) : round($a, 2); };
    return [
        'ano' => $ano,
        'receita' => ['total' => $r($total), 'proprios' => $r($prop), 'proprios_total' => $r($propT), 'estado' => $r($est), 'estado_total' => $r($estT),
            'uniao' => $r($uni), 'uniao_total' => $r($uniT), 'fundeb' => $r($fundeb), 'outras' => $r($outras)],
        'despesa' => ['total' => $r($despT), 'areas' => $r($areas), 'tipo' => $r($tipo), 'obras_det' => $r($obrasDet)],
    ];
}

/** Acumulado do ano até cada bimestre (RREO). [bimestre => ['impostos' => , 'total' => ]] */
function im_bimestres(int $ano, array $ja = []): array
{
    $out = [];
    for ($b = 1; $b <= 6; $b++) {
        if (isset($ja[$b])) { $out[$b] = $ja[$b]; continue; } // bimestre já baixado não muda (salvo retificação rara)
        $it = im_siconfi('rreo', ['an_exercicio' => $ano, 'nr_periodo' => $b, 'co_tipo_demonstrativo' => 'RREO', 'no_anexo' => 'RREO-Anexo 01']);
        if ($it === null) break;   // API fora: para aqui
        if (!$it) break;           // bimestre ainda não publicado
        $col = 'Até o Bimestre (c)';
        $imp = im_v($it, 'ReceitaTributaria', $col) + im_v($it, 'ContribuicaoDeIluminacaoPublica', $col);
        $tot = im_v($it, 'ReceitasExcetoIntraOrcamentarias', $col);
        if ($imp <= 0 || $tot <= 0) break;
        $out[$b] = ['impostos' => round($imp, 2), 'total' => round($tot, 2)];
    }
    return $out;
}

function im_populacao(array $atual): array
{
    $j = im_http('https://servicodados.ibge.gov.br/api/v3/agregados/6579/periodos/-12/variaveis/9324?localidades=N6[' . IM_IBGE . ']');
    $serie = $j[0]['resultados'][0]['series'][0]['serie'] ?? null;
    if (!is_array($serie)) return $atual;
    $p = $atual;
    foreach ($serie as $ano => $v) if ((int) $v > 0) $p[(string) $ano] = (int) $v;
    $p['2022'] = $p['2022'] ?? 189939; // Censo 2022 (ano sem estimativa)
    ksort($p);
    return $p;
}

/** IPCA (IBGE): média do número-índice em cada ano completo. Serve para comparar anos em valores de hoje. */
function im_ipca(array $atual): array
{
    $j = im_http('https://servicodados.ibge.gov.br/api/v3/agregados/1737/periodos/-120/variaveis/2266?localidades=N1[all]');
    $serie = $j[0]['resultados'][0]['series'][0]['serie'] ?? null;
    if (!is_array($serie)) return $atual;
    $soma = []; $n = [];
    foreach ($serie as $per => $v) { $a = substr((string) $per, 0, 4); if (is_numeric($v)) { $soma[$a] = ($soma[$a] ?? 0) + (float) $v; $n[$a] = ($n[$a] ?? 0) + 1; } }
    $out = $atual;
    foreach ($soma as $a => $s) if ($n[$a] === 12) $out[$a] = round($s / 12, 4);
    ksort($out);
    return $out;
}
/** Valor do ano $de em reais do ano $para (pelo IPCA médio). Sem índice, devolve o valor da época. */
function im_real(array $d, float $v, int $de, int $para): float
{
    $i = $d['ipca'] ?? [];
    return isset($i[(string) $de], $i[(string) $para]) && $i[(string) $de] > 0 ? $v * $i[(string) $para] / $i[(string) $de] : $v;
}

/** Demandas de rua e infraestrutura no Friburgo no Mapa (só as conferidas e enviadas à Prefeitura). */
const IM_INFRA = ['buraco', 'pavimentacao', 'calcada', 'iluminacao', 'drenagem', 'encosta', 'sinalizacao', 'obras'];
function im_demandas(): ?array
{
    if (!db_or_null()) return null;
    try {
        require_once ROOT . '/app/tickets.php';
        db_migrate();
        $in = implode(',', array_fill(0, count(IM_INFRA), '?'));
        $rows = q("SELECT category, status, published_at, resolved_at FROM tickets WHERE status IN ('publicado','resolvido') AND published_at IS NOT NULL AND category IN ($in)", IM_INFRA)->fetchAll();
    } catch (Throwable $ex) { return null; }
    $ab = 0; $res = 0; $dias = 0; $cats = [];
    foreach ($rows as $r) {
        if ($r['status'] === 'publicado') { $ab++; $dias += max(0, (time() - strtotime($r['published_at'])) / 86400); $cats[$r['category']] = ($cats[$r['category']] ?? 0) + 1; }
        else $res++;
    }
    arsort($cats);
    $nomes = [];
    foreach ($cats as $c => $q) $nomes[] = ['n' => cat_label($c), 'q' => $q];
    return ['abertas' => $ab, 'resolvidas' => $res, 'media_dias' => $ab ? (int) round($dias / $ab) : 0, 'cats' => $nomes];
}

function im_cache_file(): string { return storage() . '/impostometro.json'; }

/** Dados para a página: cache de até 3 dias; se mais velho, a página atualiza depois de responder. */
function im_data(): array
{
    $f = im_cache_file();
    $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    if (!$d) $d = json_decode((string) @file_get_contents(ROOT . '/app/data/impostometro-base.json'), true) ?: [];
    $d['_velho'] = !is_file($f) || filemtime($f) < time() - IM_MAX_IDADE;
    return $d;
}

/** Busca tudo de novo no Tesouro e no IBGE. Mantém o que já tinha se alguma parte falhar. */
function im_refresh(bool $tudo = false): array
{
    $lock = fopen(storage() . '/impostometro.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) return ['ok' => false, 'msg' => 'Já está atualizando.'];
    @set_time_limit(300);
    $old = im_data();
    unset($old['_velho']);
    $anoAtual = (int) date('Y');
    $anos = $old['anos'] ?? [];
    $novos = 0;
    for ($a = $anoAtual - 1; $a >= $anoAtual - IM_ANOS - 1; $a--) {
        // os 2 últimos anos sempre de novo (a Prefeitura pode retificar); os mais antigos só se faltarem
        if (!$tudo && isset($anos[(string) $a]) && $a < $anoAtual - 2) continue;
        $r = im_ano($a);
        if ($r) { $anos[(string) $a] = $r; $novos++; }
    }
    krsort($anos);
    $anos = array_slice($anos, 0, IM_ANOS, true);
    $bim = $old['bimestres'] ?? [];
    foreach ([$anoAtual - 1, $anoAtual] as $a) {
        $ja = $tudo ? [] : array_filter($bim[(string) $a] ?? [], function ($k) use ($a, $anoAtual) { return $a < $anoAtual; }, ARRAY_FILTER_USE_KEY);
        $novo = im_bimestres($a, $ja);
        if (count($novo) >= count($bim[(string) $a] ?? [])) $bim[(string) $a] = $novo;
    }
    $bim = array_intersect_key($bim, [(string) ($anoAtual - 1) => 1, (string) $anoAtual => 1]);
    $data = ['atualizado' => date('c'), 'ibge' => IM_IBGE, 'anos' => $anos, 'bimestres' => $bim, 'populacao' => im_populacao($old['populacao'] ?? []), 'ipca' => im_ipca($old['ipca'] ?? [])];
    if (!$anos) { flock($lock, LOCK_UN); return ['ok' => false, 'msg' => 'Tesouro Nacional fora do ar ou sem dados.']; }
    file_put_contents(im_cache_file() . '.tmp', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    rename(im_cache_file() . '.tmp', im_cache_file());
    flock($lock, LOCK_UN);
    return ['ok' => true, 'msg' => $novos . ' anos fechados, bimestres de ' . implode(' e ', array_map(function ($a) use ($bim) { return $a . ' (' . count($bim[$a]) . ')'; }, array_keys($bim)))];
}

/** Atualiza depois de entregar a página (quem abriu não espera). Só em servidores que permitem isso. */
function im_refresh_later(): void
{
    register_shutdown_function(function () {
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        elseif (function_exists('litespeed_finish_request')) litespeed_finish_request();
        else return;
        ignore_user_abort(true);
        try { im_refresh(); } catch (Throwable $ex) { app_log('impostometro', $ex->getMessage()); }
    });
}

/* ---------- para a página ---------- */
function im_pop(array $d, int $ano): int
{
    $p = $d['populacao'] ?? [];
    if (!$p) return 0;
    if (isset($p[(string) $ano])) return (int) $p[(string) $ano];
    $best = null;
    foreach ($p as $a => $v) if ($best === null || abs((int) $a - $ano) < abs((int) $best - $ano) || (abs((int) $a - $ano) === abs((int) $best - $ano) && (int) $a > (int) $best)) $best = $a;
    return (int) $p[$best];
}
const IM_MESES = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

/**
 * Contador do ano corrente. Parte do último número oficial (fim do último bimestre publicado) e segue o ritmo do
 * mesmo período do ano anterior (o IPTU, por exemplo, entra quase todo no começo do ano). Sem bimestre publicado
 * no ano ainda, usa o mesmo período do ano anterior.
 */
function im_contador(array $d): array
{
    $ano = (int) date('Y');
    $cur = $d['bimestres'][(string) $ano] ?? [];
    $prev = $d['bimestres'][(string) ($ano - 1)] ?? [];
    $fechado = $d['anos'][(string) ($ano - 1)] ?? null;
    // curva do ano anterior: [dia do ano => acumulado], com 0 no dia 1
    $curva = function (string $k) use ($prev, $fechado, $ano) {
        $pts = [[0, 0.0]];
        foreach ($prev as $b => $v) $pts[] = [(int) date('z', mktime(0, 0, 0, $b * 2 + 1, 1, $ano - 1)) ?: 365, (float) $v[$k]];
        if (count($pts) < 7 && $fechado) $pts[] = [365, (float) ($k === 'impostos' ? $fechado['receita']['proprios_total'] : $fechado['receita']['total'])];
        usort($pts, function ($a, $b) { return $a[0] <=> $b[0]; });
        return $pts;
    };
    $lastB = $cur ? max(array_keys($cur)) : 0;
    $out = ['ano' => $ano, 'bimestre' => $lastB, 'oficial_ate' => $lastB ? IM_MESES[$lastB * 2] : null,
        'base_dia' => $lastB ? (int) date('z', mktime(0, 0, 0, $lastB * 2 + 1, 1, $ano)) : 0, 'pop' => im_pop($d, $ano)];
    foreach (['impostos', 'total'] as $k) $out[$k] = ['curva' => $curva($k), 'base' => $lastB ? (float) $cur[$lastB][$k] : 0.0];
    $out['ok'] = count($out['impostos']['curva']) > 1;
    return $out;
}
