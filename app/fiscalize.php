<?php
/**
 * Fiscalize Você: compras da Prefeitura de Nova Friburgo vindas do PNCP, com o valor por unidade calculado.
 *
 * Fluxo: fv_sync() lista as contratações do órgão no PNCP (API de consulta), guarda o JSON bruto, busca os itens
 * e os resultados homologados (fornecedor, quantidade, valor) e calcula o valor por unidade e a comparação com a
 * referência. O gabinete revisa no painel (admin/fiscalize.php) e só o que estiver "publicado" aparece na página.
 */
defined('APP') or exit;
require_once __DIR__ . '/tickets.php'; // SECRETARIAS

const FV_STATUS = ['novo' => 'Nova', 'revisado' => 'Revisada', 'publicado' => 'Publicada', 'ocultado' => 'Ocultada'];
const FV_SITUACAO = ['normal' => 'Dentro da referência', 'acima' => 'Acima da referência', 'muito_acima' => 'Muito acima da referência', 'sem_referencia' => 'Sem referência para comparar', 'revisar' => 'Revisar'];
const FV_CFG_DEFAULTS = [
    'pagina_publica' => '0', 'limite_acima' => '20', 'limite_muito_acima' => '50',
    'ibge_municipio' => '3303401', // Nova Friburgo (3303500 é Nova Iguaçu)
    'orgaos_cnpj' => '28606630000123', // Município de Nova Friburgo (Prefeitura). Separe outros CNPJs por vírgula.
    'coleta_desde' => '2025-01-01', 'ultima_coleta' => '', 'ultima_lista' => '',
];
// Modalidades da Lei 14.133 no PNCP (1 a 13)
const FV_MODALIDADES = [6 => 'Pregão eletrônico', 8 => 'Dispensa', 9 => 'Inexigibilidade', 4 => 'Concorrência eletrônica', 5 => 'Concorrência presencial', 7 => 'Pregão presencial',
    12 => 'Credenciamento', 1 => 'Leilão eletrônico', 2 => 'Diálogo competitivo', 3 => 'Concurso', 10 => 'Manifestação de interesse', 11 => 'Pré-qualificação', 13 => 'Leilão presencial'];
const FV_TERMOS = [
    'pregão' => 'Disputa de preços entre empresas, em geral pela internet. Ganha quem oferece o menor preço.',
    'dispensa' => 'Compra feita sem licitação, nos casos que a lei permite (valor baixo, emergência etc.).',
    'inexigibilidade' => 'Compra sem licitação porque não há como competir (fornecedor único, artista, credenciamento etc.).',
    'registro de preços' => 'A Prefeitura registra o preço e compra aos poucos, conforme precisar. O valor mostrado é o máximo que pode ser comprado, não o que já foi pago.',
    'homologação' => 'Ato que confirma o resultado da compra: quem venceu e por quanto.',
];

/* ---------- banco ---------- */
function fv_tables(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS fv_compras (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pncp_id VARCHAR(100) NOT NULL UNIQUE,
        orgao_cnpj VARCHAR(20) NOT NULL, ano SMALLINT NOT NULL, sequencial INT NOT NULL,
        numero_processo VARCHAR(100) NULL, numero_compra VARCHAR(50) NULL,
        modalidade VARCHAR(100) NULL, modalidade_id TINYINT NULL, srp TINYINT(1) NOT NULL DEFAULT 0,
        secretaria VARCHAR(255) NULL, secretaria_editada TINYINT(1) NOT NULL DEFAULT 0,
        objeto TEXT NULL,
        fornecedor_nome VARCHAR(255) NULL, fornecedor_cnpj VARCHAR(20) NULL,
        valor_estimado DECIMAL(16,2) NULL, valor_total DECIMAL(16,2) NULL,
        data_publicacao DATE NULL, data_homologacao DATE NULL,
        situacao_pncp VARCHAR(100) NULL, pncp_atualizacao VARCHAR(30) NULL,
        url_fonte VARCHAR(500) NULL,
        status ENUM('novo','revisado','publicado','ocultado') NOT NULL DEFAULT 'novo',
        itens_sync ENUM('pendente','ok','erro') NOT NULL DEFAULT 'pendente', itens_n INT NOT NULL DEFAULT 0,
        esclarecimento TEXT NULL, esclarecimento_data DATE NULL,
        nota_interna TEXT NULL, corrigido_em DATETIME NULL,
        raw_json MEDIUMTEXT NULL,
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
        atualizado_em DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        KEY (status), KEY (data_homologacao), KEY (itens_sync)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS fv_itens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        compra_id INT NOT NULL,
        numero_item INT NOT NULL, seq_resultado INT NOT NULL DEFAULT 0,
        descricao VARCHAR(1000) NULL, titulo_simples VARCHAR(255) NULL, descricao_normalizada VARCHAR(500) NULL,
        quantidade DECIMAL(16,3) NULL, unidade_medida VARCHAR(80) NULL,
        valor_total_item DECIMAL(16,2) NULL, valor_unitario DECIMAL(16,2) NULL,
        valor_unitario_informado DECIMAL(16,4) NULL, valor_unitario_estimado DECIMAL(16,4) NULL,
        fornecedor_nome VARCHAR(255) NULL, fornecedor_cnpj VARCHAR(20) NULL,
        data_resultado DATE NULL,
        referencia_valor DECIMAL(16,2) NULL, referencia_fonte VARCHAR(255) NULL, referencia_url VARCHAR(500) NULL, referencia_manual TINYINT(1) NOT NULL DEFAULT 0,
        diferenca_percentual DECIMAL(10,2) NULL,
        situacao ENUM('normal','acima','muito_acima','sem_referencia','revisar') NOT NULL DEFAULT 'sem_referencia',
        aviso VARCHAR(255) NULL,
        editado TINYINT(1) NOT NULL DEFAULT 0, oculto TINYINT(1) NOT NULL DEFAULT 0,
        raw_json TEXT NULL,
        UNIQUE KEY fv_item_uk (compra_id, numero_item, seq_resultado),
        KEY (descricao_normalizada(120)),
        CONSTRAINT fv_itens_compra FOREIGN KEY (compra_id) REFERENCES fv_compras(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS fv_referencias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        descricao_normalizada VARCHAR(500) NOT NULL,
        unidade_medida VARCHAR(80) NULL,
        valor_unitario DECIMAL(16,2) NOT NULL,
        fonte VARCHAR(255) NOT NULL, url VARCHAR(500) NULL,
        data_referencia DATE NULL,
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS fv_config (chave VARCHAR(100) PRIMARY KEY, valor VARCHAR(255) NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS fv_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        executado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
        tipo VARCHAR(50), registros_novos INT NOT NULL DEFAULT 0, erros TEXT NULL, detalhes VARCHAR(500) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function fv_cfg(string $k): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = FV_CFG_DEFAULTS;
        try { fv_tables(); foreach (q('SELECT chave, valor FROM fv_config')->fetchAll() as $r) $cache[$r['chave']] = (string) $r['valor']; } catch (Throwable $ex) {}
    }
    return $cache[$k] ?? '';
}
function fv_cfg_set(string $k, string $v): void
{
    fv_tables();
    q('INSERT INTO fv_config (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)', [$k, $v]);
}
/** Página aberta ao público? (flag no banco; muda sem novo deploy) */
function fv_is_public(): bool
{
    if (!db_or_null()) return false;
    try { return fv_cfg('pagina_publica') === '1'; } catch (Throwable $ex) { return false; }
}

/* ---------- textos ---------- */
function fv_fold(string $s): string
{
    $s = mb_strtolower($s, 'UTF-8');
    $s = strtr($s, ['á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'é' => 'e', 'ê' => 'e', 'è' => 'e', 'í' => 'i', 'ì' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ò' => 'o', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c', 'º' => 'o', 'ª' => 'a']);
    return $s;
}
/** Chave para achar o mesmo item em outras compras: sem acento, minúsculo, sem palavras vazias, só o começo da descrição. */
function fv_normalize(string $desc): string
{
    $s = fv_fold($desc);
    $s = preg_split('/especifica[cç][oõ]es? conforme|conforme (termo|edital|anexo)|\bobs[.:]/u', $s)[0];
    $s = preg_replace('/[^a-z0-9 ]+/', ' ', $s);
    $stop = ['de', 'da', 'do', 'das', 'dos', 'e', 'a', 'o', 'com', 'para', 'em', 'tipo', 'cor', 'apresentacao', 'caracteristicas', 'adicionais', 'material', 'aplicacao', 'unidade', 'fornecimento'];
    $w = array_values(array_filter(explode(' ', $s), function ($x) use ($stop) { return strlen($x) > 1 && !in_array($x, $stop, true); }));
    return implode(' ', array_slice($w, 0, 8));
}
/** "MICROCOMPUTADOR, PROCESSADOR..." -> "Microcomputador" (título curto e legível). */
function fv_short_title(string $desc): string
{
    $s = trim(preg_replace('/\s+/u', ' ', $desc));
    $s = preg_split('/\s*(?:[,;:(]|\s-\s|especifica[cç][aãoõ]|conforme)\s*/iu', $s)[0] ?: $s;
    for ($i = 0; $i < 3; $i++) $s = preg_replace('/\s+(tipo|modelo|marca|material|com|de|da|do|para|e|em|-)$/iu', '', $s);
    if (mb_strlen($s) > 90) $s = rtrim(mb_substr($s, 0, 88)) . '…';
    if ($s === mb_strtoupper($s, 'UTF-8')) $s = mb_strtolower($s, 'UTF-8');
    return mb_strtoupper(mb_substr($s, 0, 1), 'UTF-8') . mb_substr($s, 1);
}
function fv_money($v, bool $cents = true): string
{
    if ($v === null || $v === '') return '—';
    return 'R$ ' . number_format((float) $v, $cents ? 2 : 0, ',', '.');
}
function fv_qty($v): string
{
    if ($v === null || $v === '') return '—';
    $f = (float) $v;
    return abs($f - round($f)) < 0.0005 ? number_format($f, 0, ',', '.') : rtrim(rtrim(number_format($f, 3, ',', '.'), '0'), ',');
}
function fv_cnpj(?string $c): string
{
    $d = preg_replace('/\D/', '', (string) $c);
    return strlen($d) === 14 ? vsprintf('%s.%s.%s/%s-%s', [substr($d, 0, 2), substr($d, 2, 3), substr($d, 5, 3), substr($d, 8, 4), substr($d, 12, 2)]) : (string) $c;
}
/** "por unidade", "por caixa", "por kg" */
function fv_per(?string $unit): string
{
    $u = trim(mb_strtolower((string) $unit, 'UTF-8'));
    if ($u === '' || in_array(fv_fold($u), ['unidade', 'un', 'und', 'unid', 'un.', 'peca', 'pc'], true)) return 'por unidade';
    return 'por ' . $u;
}
/** Unidade que esconde outra quantidade dentro (caixa com 10, pacote, kit...): vale conferir. */
function fv_unit_is_pack(?string $unit): bool
{
    return (bool) preg_match('/\b(caixa|cx|pacote|pct|kit|fardo|conjunto|jogo|lote|embalagem|frasco com|galao|bombona|resma|cartela|blister)\b|\d/u', fv_fold((string) $unit));
}

/** Descobre a secretaria pelo texto do objeto ("...para atender a Secretaria de Saúde"). Devolve o slug de SECRETARIAS ou null. */
function fv_guess_secretaria(string $objeto): ?string
{
    $o = fv_fold($objeto);
    foreach (SECRETARIAS as $slug => $name) {
        $n = fv_fold(preg_replace('/^(Secretaria Executiva de |Secretaria de |Secretaria da |Secretaria do )/u', '', $name));
        if (strlen($n) > 5 && strpos($o, $n) !== false) return $slug;
    }
    $kw = [
        'saude' => ['saude', 'farmacia', 'medicament', 'hospital', 'upa ', 'odontolog', 'oftalmolog', 'exames de', 'diagnostic', 'cirurg', 'samu', 'opme', 'vacina', 'laborator', 'ambulanc', 'insumos hospitalares', 'ubs', 'policlinica', 'raiz de dente'],
        'educacao' => ['educacao', 'escola', 'escolar', 'merenda', 'aluno', 'creche', 'ensino', 'pedagog'],
        'obras' => ['obras', 'infraestrutura', 'pavimenta', 'asfalt', 'cbuq', 'emulsao', 'drenagem', 'ponte', 'muro de conten'],
        'servicos' => ['servicos publicos', 'iluminacao publica', 'coleta de lixo', 'limpeza urbana', 'cemiterio', 'pracas e jardins', 'capina'],
        'social' => ['assistencia social', 'cras', 'creas', 'desenvolvimento social', 'acolhimento', 'cesta basica', 'bolsa familia'],
        'gestao' => ['recursos humanos', 'gestao e recursos'],
        'fazenda' => ['fazenda', 'tributa', 'arrecada'],
        'ambiente' => ['meio ambiente', 'ambiental', 'arboriza'],
        'agricultura' => ['agricultura', 'rural', 'agricola', 'trator'],
        'esportes' => ['esporte', 'lazer', 'esportiv'],
        'cultura' => ['cultura', 'cultural', 'teatro', 'biblioteca'],
        'turismo' => ['turismo', 'turistic', 'natal', 'carnaval', 'festividade'],
        'mobilidade' => ['mobilidade', 'transito', 'semaforo', 'sinalizacao viaria', 'urbanismo'],
        'defesacivil' => ['defesa civil'],
        'seguranca' => ['guarda municipal', 'seguranca publica', 'ordem publica', 'videomonitoramento'],
        'habitacao' => ['habitacao', 'regularizacao fundiaria', 'moradia'],
        'mulher' => ['mulher', 'violencia domestica'],
        'animal' => ['animal', 'animais', 'veterinar', 'castracao', 'canil', 'canino', 'felino', 'racao'],
        'procuradoria' => ['procuradoria'],
        'controladoria' => ['controladoria'],
        'gabinete' => ['gabinete do prefeito'],
    ];
    foreach ($kw as $slug => $words) foreach ($words as $w) if (strpos($o, $w) !== false) return $slug;
    return null;
}
/** Quantidade com unidade: "90 unidades", "6.000 quilograma". */
function fv_qty_unit($qty, ?string $unit): string
{
    $u = mb_strtolower(trim((string) $unit), 'UTF-8');
    if ((float) $qty != 1.0 && in_array(fv_fold($u), ['unidade', 'unid', 'und', 'un'], true)) $u = 'unidades';
    return trim(fv_qty($qty) . ' ' . $u);
}
/** Rótulo curto para o selo do card. */
function fv_secretaria_label(?string $s): string { return $s && isset(SECRETARIAS[$s]) ? secretaria_short(SECRETARIAS[$s]) : 'Prefeitura'; }
function fv_secretaria_name(?string $s): string
{
    if (!$s) return 'Prefeitura (várias secretarias ou não identificada)';
    return SECRETARIAS[$s] ?? $s;
}

/* ---------- PNCP ---------- */
/** GET na API do PNCP. Devolve o JSON decodificado, [] para "sem conteúdo" (204) e lança exceção em erro. */
function fv_http(string $url, int $tries = 2)
{
    $err = '';
    for ($i = 0; $i < $tries; $i++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 40, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'], CURLOPT_USERAGENT => 'FiscalizeVoce/1.0 (+https://maicongoncalves.com.br)']);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($code === 204 || ($code === 200 && trim((string) $body) === '')) return [];
        if ($code === 200) {
            $j = json_decode((string) $body, true);
            if (is_array($j)) return $j;
            $err = 'JSON inválido';
        } else {
            $err = 'HTTP ' . $code . ($cerr ? ' ' . $cerr : '') . ' ' . mb_substr(strip_tags((string) $body), 0, 160);
            if ($code >= 400 && $code < 500 && $code !== 429) break;
        }
        usleep(800000);
    }
    throw new RuntimeException($err . ' em ' . preg_replace('#^https://pncp\.gov\.br#', '', $url));
}
function fv_url_fonte(string $cnpj, int $ano, int $seq): string { return "https://pncp.gov.br/app/editais/$cnpj/$ano/$seq"; }

/**
 * Coleta. $budget = segundos disponíveis (cron: 280; botão do painel: 50). Pode ser chamada várias vezes:
 * a 1ª parte (lista de contratações) roda no máximo 1x a cada 20 h ou quando $forceList; a 2ª (itens) continua de onde parou.
 */
function fv_sync(int $budget = 280, bool $forceList = false, ?callable $say = null): array
{
    fv_tables();
    @set_time_limit($budget + 60);
    $t0 = microtime(true);
    $left = function () use ($t0, $budget) { return $budget - (microtime(true) - $t0); };
    $say = $say ?: function ($m) {};
    $out = ['novas' => 0, 'atualizadas' => 0, 'itens' => 0, 'compras_itens' => 0, 'erros' => [], 'pendentes' => 0, 'lista' => false];
    $orgaos = array_filter(array_map(function ($x) { return preg_replace('/\D/', '', $x); }, explode(',', fv_cfg('orgaos_cnpj'))));
    $ibge = preg_replace('/\D/', '', fv_cfg('ibge_municipio'));

    // 1) lista de contratações atualizadas no período
    $last = fv_cfg('ultima_lista');
    if ($forceList || $last === '' || strtotime($last) < time() - 20 * 3600) {
        $from = fv_cfg('ultima_coleta') !== '' ? date('Y-m-d', strtotime(fv_cfg('ultima_coleta') . ' -3 days')) : fv_cfg('coleta_desde');
        $to = date('Y-m-d');
        $complete = true;
        for ($a = strtotime($from); $a <= strtotime($to) && $complete; $a = strtotime('+1 day', $b)) {
            $b = min(strtotime('+364 days', $a), strtotime($to));
            foreach (FV_MODALIDADES as $mod => $mname) {
                for ($pg = 1; $pg <= 200; $pg++) {
                    if ($left() < 15) { $complete = false; break 2; }
                    $url = 'https://pncp.gov.br/api/consulta/v1/contratacoes/atualizacao?' . http_build_query(['dataInicial' => date('Ymd', $a), 'dataFinal' => date('Ymd', $b),
                        'codigoModalidadeContratacao' => $mod, 'codigoMunicipioIbge' => $ibge, 'pagina' => $pg, 'tamanhoPagina' => 50]);
                    try { $j = fv_http($url); } catch (Throwable $ex) { $out['erros'][] = $ex->getMessage(); break; }
                    foreach (($j['data'] ?? []) as $c) {
                        if (!in_array((string) ($c['orgaoEntidade']['cnpj'] ?? ''), $orgaos, true)) continue;
                        $r = fv_upsert_compra($c);
                        if ($r === 'nova') $out['novas']++; elseif ($r === 'atualizada') $out['atualizadas']++;
                    }
                    if ($pg >= (int) ($j['totalPaginas'] ?? 0)) break;
                }
            }
        }
        if ($complete) {
            fv_cfg_set('ultima_lista', date('Y-m-d H:i:s'));
            fv_cfg_set('ultima_coleta', $to);
            $out['lista'] = true;
        }
        $say("lista: {$out['novas']} novas, {$out['atualizadas']} atualizadas" . ($complete ? '' : ' (parcial, continua na próxima)'));
    }

    // 2) itens e resultados das compras pendentes (as mais recentes primeiro)
    foreach (q("SELECT * FROM fv_compras WHERE itens_sync = 'pendente' ORDER BY data_publicacao DESC, id DESC")->fetchAll() as $c) {
        if ($left() < 20) break;
        try {
            $n = fv_sync_itens($c, $left);
            if ($n === null) break; // acabou o tempo no meio: continua depois
            $out['itens'] += $n;
            $out['compras_itens']++;
        } catch (Throwable $ex) {
            $out['erros'][] = $c['pncp_id'] . ': ' . $ex->getMessage();
            q("UPDATE fv_compras SET itens_sync = 'erro' WHERE id = ?", [$c['id']]);
        }
    }
    $out['pendentes'] = (int) q("SELECT COUNT(*) FROM fv_compras WHERE itens_sync = 'pendente'")->fetchColumn();
    $say("itens: {$out['itens']} resultados em {$out['compras_itens']} compras; {$out['pendentes']} compras na fila");
    q('INSERT INTO fv_log (tipo, registros_novos, erros, detalhes) VALUES (?, ?, ?, ?)', ['coleta', $out['novas'],
        $out['erros'] ? mb_substr(implode("\n", array_slice($out['erros'], 0, 30)), 0, 60000) : null,
        mb_substr("{$out['atualizadas']} atualizadas, {$out['itens']} itens em {$out['compras_itens']} compras, {$out['pendentes']} na fila" . ($out['lista'] ? '' : ($last === '' || $forceList ? ', lista parcial' : '')), 0, 500)]);
    return $out;
}

/** Grava/atualiza uma contratação da lista. Marca os itens para buscar de novo quando o PNCP mudou algo. */
function fv_upsert_compra(array $c): string
{
    $id = (string) $c['numeroControlePNCP'];
    $cur = q('SELECT id, pncp_atualizacao, secretaria_editada FROM fv_compras WHERE pncp_id = ?', [$id])->fetch();
    $upd = (string) ($c['dataAtualizacaoGlobal'] ?? $c['dataAtualizacao'] ?? '');
    $cnpj = (string) $c['orgaoEntidade']['cnpj'];
    $vals = [
        'numero_processo' => $c['processo'] ?? null, 'numero_compra' => $c['numeroCompra'] ?? null,
        'modalidade' => $c['modalidadeNome'] ?? null, 'modalidade_id' => $c['modalidadeId'] ?? null, 'srp' => !empty($c['srp']) ? 1 : 0,
        'objeto' => $c['objetoCompra'] ?? null, 'valor_estimado' => $c['valorTotalEstimado'] ?? null,
        'data_publicacao' => !empty($c['dataPublicacaoPncp']) ? substr($c['dataPublicacaoPncp'], 0, 10) : null,
        'situacao_pncp' => $c['situacaoCompraNome'] ?? null, 'pncp_atualizacao' => $upd,
        'url_fonte' => fv_url_fonte($cnpj, (int) $c['anoCompra'], (int) $c['sequencialCompra']),
        'raw_json' => json_encode($c, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];
    if (!$cur) {
        $vals += ['pncp_id' => $id, 'orgao_cnpj' => $cnpj, 'ano' => (int) $c['anoCompra'], 'sequencial' => (int) $c['sequencialCompra'],
            'secretaria' => fv_guess_secretaria((string) ($c['objetoCompra'] ?? '') . ' ' . (string) ($c['informacaoComplementar'] ?? '')), 'itens_sync' => 'pendente'];
        q('INSERT INTO fv_compras (' . implode(',', array_keys($vals)) . ') VALUES (' . implode(',', array_fill(0, count($vals), '?')) . ')', array_values($vals));
        return 'nova';
    }
    if ($cur['pncp_atualizacao'] === $upd) return 'igual';
    if (!$cur['secretaria_editada']) $vals['secretaria'] = fv_guess_secretaria((string) ($c['objetoCompra'] ?? '') . ' ' . (string) ($c['informacaoComplementar'] ?? ''));
    $vals['itens_sync'] = 'pendente';
    q('UPDATE fv_compras SET ' . implode(', ', array_map(function ($k) { return "$k = ?"; }, array_keys($vals))) . ' WHERE id = ?', array_merge(array_values($vals), [$cur['id']]));
    return 'atualizada';
}

/** Busca os itens e, nos que têm resultado, o fornecedor e os valores homologados. Devolve quantos resultados gravou, ou null se faltou tempo. */
function fv_sync_itens(array $c, callable $left): ?int
{
    $base = "https://pncp.gov.br/api/pncp/v1/orgaos/{$c['orgao_cnpj']}/compras/{$c['ano']}/{$c['sequencial']}";
    $itens = [];
    for ($pg = 1; $pg <= 50; $pg++) {
        $j = fv_http("$base/itens?pagina=$pg&tamanhoPagina=500");
        if (!$j) break;
        $itens = array_merge($itens, $j);
        if (count($j) < 500) break;
    }
    $n = 0;
    $seen = [];
    foreach ($itens as $it) {
        if (empty($it['temResultado'])) continue;
        if ($left() < 8) return null;
        $res = fv_http("$base/itens/{$it['numeroItem']}/resultados");
        foreach ($res as $r) {
            if (!empty($r['dataCancelamento'])) continue;
            fv_upsert_item((int) $c['id'], $it, $r);
            $seen[] = (int) $it['numeroItem'] . '-' . (int) ($r['sequencialResultado'] ?? 0);
            $n++;
        }
    }
    // resultados que sumiram ou foram cancelados no PNCP saem (se não foram editados à mão)
    foreach (q('SELECT id, numero_item, seq_resultado, editado FROM fv_itens WHERE compra_id = ?', [$c['id']])->fetchAll() as $old) {
        if (!$old['editado'] && !in_array($old['numero_item'] . '-' . $old['seq_resultado'], $seen, true)) q('DELETE FROM fv_itens WHERE id = ?', [$old['id']]);
    }
    fv_refresh_compra((int) $c['id']);
    q("UPDATE fv_compras SET itens_sync = 'ok', itens_n = ? WHERE id = ?", [count($itens), $c['id']]);
    return $n;
}

function fv_upsert_item(int $compraId, array $it, array $r): void
{
    $qty = isset($r['quantidadeHomologada']) ? (float) $r['quantidadeHomologada'] : (isset($it['quantidade']) ? (float) $it['quantidade'] : null);
    $tot = isset($r['valorTotalHomologado']) ? (float) $r['valorTotalHomologado'] : null;
    $vals = [
        'descricao' => mb_substr((string) ($it['descricao'] ?? ''), 0, 1000),
        'descricao_normalizada' => fv_normalize((string) ($it['descricao'] ?? '')),
        'quantidade' => $qty, 'unidade_medida' => mb_substr((string) ($it['unidadeMedida'] ?? ''), 0, 80) ?: null,
        'valor_total_item' => $tot,
        'valor_unitario_informado' => $r['valorUnitarioHomologado'] ?? null,
        'valor_unitario_estimado' => !empty($it['valorUnitarioEstimado']) ? $it['valorUnitarioEstimado'] : null, // 0 = orçamento sigiloso
        'fornecedor_nome' => mb_substr((string) ($r['nomeRazaoSocialFornecedor'] ?? ''), 0, 255) ?: null,
        'fornecedor_cnpj' => ($r['tipoPessoa'] ?? 'PJ') === 'PJ' ? (string) ($r['niFornecedor'] ?? '') : null, // CPF de pessoa física não é exibido
        'data_resultado' => !empty($r['dataResultado']) ? substr($r['dataResultado'], 0, 10) : null,
        'raw_json' => json_encode(['item' => array_intersect_key($it, array_flip(['numeroItem', 'descricao', 'quantidade', 'unidadeMedida', 'valorUnitarioEstimado', 'valorTotal', 'materialOuServicoNome', 'criterioJulgamentoNome'])), 'resultado' => $r], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];
    if (($r['tipoPessoa'] ?? 'PJ') !== 'PJ') $vals['fornecedor_nome'] = 'Pessoa física';
    $cur = q('SELECT id, editado FROM fv_itens WHERE compra_id = ? AND numero_item = ? AND seq_resultado = ?', [$compraId, (int) $it['numeroItem'], (int) ($r['sequencialResultado'] ?? 0)])->fetch();
    if (!$cur) {
        $vals += ['compra_id' => $compraId, 'numero_item' => (int) $it['numeroItem'], 'seq_resultado' => (int) ($r['sequencialResultado'] ?? 0)];
        q('INSERT INTO fv_itens (' . implode(',', array_keys($vals)) . ') VALUES (' . implode(',', array_fill(0, count($vals), '?')) . ')', array_values($vals));
        return;
    }
    if ($cur['editado']) { q('UPDATE fv_itens SET raw_json = ? WHERE id = ?', [$vals['raw_json'], $cur['id']]); return; } // conferido à mão: só guarda o bruto novo
    q('UPDATE fv_itens SET ' . implode(', ', array_map(function ($k) { return "$k = ?"; }, array_keys($vals))) . ' WHERE id = ?', array_merge(array_values($vals), [$cur['id']]));
}

/** Recalcula itens (valor unitário, referência, situação) e os totais da compra. */
function fv_refresh_compra(int $id): void
{
    foreach (q('SELECT * FROM fv_itens WHERE compra_id = ?', [$id])->fetchAll() as $it) fv_calc_item($it);
    $agg = q('SELECT SUM(valor_total_item) tot, MAX(data_resultado) dr, COUNT(DISTINCT fornecedor_nome) nf, MIN(fornecedor_nome) f1, MIN(fornecedor_cnpj) c1 FROM fv_itens WHERE compra_id = ? AND oculto = 0', [$id])->fetch();
    $forn = (int) $agg['nf'] === 1 ? $agg['f1'] : ((int) $agg['nf'] > 1 ? $agg['nf'] . ' fornecedores' : null);
    q('UPDATE fv_compras SET valor_total = ?, data_homologacao = ?, fornecedor_nome = ?, fornecedor_cnpj = ? WHERE id = ?',
        [$agg['tot'], $agg['dr'], $forn, (int) $agg['nf'] === 1 ? $agg['c1'] : null, $id]);
}

/** Valor por unidade + comparação com a referência. */
function fv_calc_item(array $it): void
{
    $qty = $it['quantidade'] !== null ? (float) $it['quantidade'] : 0.0;
    $tot = $it['valor_total_item'] !== null ? (float) $it['valor_total_item'] : null;
    $avisos = [];
    $unit = null;
    $sit = 'sem_referencia';
    if ($qty <= 0 || $tot === null) {
        $sit = 'revisar';
        $avisos[] = 'Quantidade não informada';
    } else {
        $unit = round($tot / $qty, 2);
        $inf = $it['valor_unitario_informado'] !== null ? (float) $it['valor_unitario_informado'] : null;
        if ($inf && abs($inf - $unit) / max($inf, 0.01) > 0.01) $avisos[] = 'Valor unitário informado no PNCP (' . fv_money($inf) . ') difere do cálculo';
        if (fv_unit_is_pack($it['unidade_medida'])) $avisos[] = 'Unidade "' . $it['unidade_medida'] . '": confira quantas unidades vêm em cada uma';
    }
    // referência: manual (no item) > tabela de referências > histórico da própria Prefeitura
    $ref = null; $fonte = null; $rurl = null;
    if ($it['referencia_manual'] && $it['referencia_valor'] !== null) {
        $ref = (float) $it['referencia_valor']; $fonte = $it['referencia_fonte']; $rurl = $it['referencia_url'];
    } elseif ($unit !== null && $it['descricao_normalizada']) {
        $r = q('SELECT valor_unitario, fonte, url FROM fv_referencias WHERE descricao_normalizada = ? AND (unidade_medida IS NULL OR unidade_medida = \'\' OR unidade_medida = ?) ORDER BY data_referencia DESC, id DESC LIMIT 1',
            [$it['descricao_normalizada'], (string) $it['unidade_medida']])->fetch();
        if ($r) { $ref = (float) $r['valor_unitario']; $fonte = $r['fonte']; $rurl = $r['url']; }
        elseif (fv_item_is_material($it)) {
            // mesma descrição e unidade em OUTRA compra da Prefeitura (mediana dos valores). Só para materiais:
            // serviços com a mesma descrição de catálogo (shows, obras, locação) costumam ser coisas bem diferentes.
            $h = q('SELECT i.valor_unitario, c.numero_processo, c.url_fonte, c.data_homologacao FROM fv_itens i JOIN fv_compras c ON c.id = i.compra_id
                    WHERE i.descricao_normalizada = ? AND i.unidade_medida <=> ? AND i.compra_id <> ? AND i.valor_unitario IS NOT NULL AND i.oculto = 0 AND c.status <> \'ocultado\'
                    ORDER BY c.data_homologacao DESC LIMIT 15', [$it['descricao_normalizada'], $it['unidade_medida'], $it['compra_id']])->fetchAll();
            if ($h) {
                $vs = array_map('floatval', array_column($h, 'valor_unitario'));
                sort($vs);
                $m = count($vs);
                $ref = round($m % 2 ? $vs[intdiv($m, 2)] : ($vs[$m / 2 - 1] + $vs[$m / 2]) / 2, 2);
                $fonte = count($h) === 1 ? 'Outra compra da Prefeitura (processo ' . ($h[0]['numero_processo'] ?: '—') . ')' : 'Mediana de ' . count($h) . ' outras compras da Prefeitura';
                $rurl = $h[0]['url_fonte'];
            }
        }
    }
    if ($ref && !$it['referencia_manual'] && $unit !== null) {
        $ratio = $unit / $ref;
        if ($ratio > 4 || $ratio < 0.25) { // diferença grande demais para ser o mesmo produto: não compara sozinho
            $avisos[] = 'Referência automática descartada (' . fv_money($ref) . ', ' . $fonte . '): valores muito diferentes, provavelmente outro produto';
            $ref = null; $fonte = null; $rurl = null;
        } else $avisos[] = 'Referência automática: confira se é o mesmo produto antes de publicar';
    }
    $dif = null;
    if ($unit !== null && $ref) {
        $dif = round(($unit - $ref) / $ref * 100, 2);
        $sit = $dif > (float) fv_cfg('limite_muito_acima') ? 'muito_acima' : ($dif > (float) fv_cfg('limite_acima') ? 'acima' : 'normal');
    }
    if ((!$it['editado'] || !$it['titulo_simples']) && $it['descricao']) $it['titulo_simples'] = fv_short_title($it['descricao']); // título automático acompanha melhorias; o corrigido à mão fica
    q('UPDATE fv_itens SET valor_unitario = ?, referencia_valor = ?, referencia_fonte = ?, referencia_url = ?, diferenca_percentual = ?, situacao = ?, aviso = ?, titulo_simples = ? WHERE id = ?',
        [$unit, $ref, $fonte, $rurl, $dif, $sit, $avisos ? mb_substr(implode(' · ', $avisos), 0, 255) : null, $it['titulo_simples'], $it['id']]);
}

function fv_item_is_material(array $it): bool
{
    $j = json_decode((string) $it['raw_json'], true);
    return (($j['item']['materialOuServicoNome'] ?? '') === 'Material');
}

/** Recalcula tudo (quando muda o limite ou entra uma referência nova). */
function fv_recalc_all(): int
{
    fv_tables();
    $n = 0;
    foreach (q('SELECT id FROM fv_compras')->fetchAll(PDO::FETCH_COLUMN) as $id) { fv_refresh_compra((int) $id); $n++; }
    return $n;
}

/* ---------- consulta pública ---------- */
/** Itens publicados conforme os filtros da página. */
function fv_public_items(array $f): array
{
    $w = ["c.status = 'publicado'", 'i.oculto = 0', 'i.valor_total_item IS NOT NULL'];
    $a = [];
    $date = 'COALESCE(i.data_resultado, c.data_homologacao, c.data_publicacao)';
    if ($f['de']) { $w[] = "$date >= ?"; $a[] = $f['de']; }
    if ($f['ate']) { $w[] = "$date <= ?"; $a[] = $f['ate']; }
    if ($f['sec'] !== '') { if ($f['sec'] === '_') $w[] = '(c.secretaria IS NULL OR c.secretaria = \'\')'; else { $w[] = 'c.secretaria = ?'; $a[] = $f['sec']; } }
    if ($f['q'] !== '') {
        foreach (array_slice(array_filter(explode(' ', $f['q'])), 0, 6) as $word) {
            $l = '%' . addcslashes($word, '%_\\') . '%';
            $w[] = '(i.descricao LIKE ? OR i.titulo_simples LIKE ? OR c.objeto LIKE ? OR i.fornecedor_nome LIKE ? OR c.numero_processo LIKE ?)';
            array_push($a, $l, $l, $l, $l, $l);
        }
    }
    if ($f['sit'] === 'acima') $w[] = "i.situacao IN ('acima','muito_acima')";
    elseif ($f['sit'] === 'sem') $w[] = "i.situacao IN ('sem_referencia','revisar')";
    $order = ['recentes' => "$date DESC, i.valor_total_item DESC", 'total' => 'i.valor_total_item DESC', 'unitario' => 'i.valor_unitario DESC'][$f['ord']] ?? "$date DESC";
    return q("SELECT i.*, c.secretaria, c.objeto, c.modalidade, c.modalidade_id, c.numero_processo, c.url_fonte, c.srp, c.esclarecimento, c.esclarecimento_data, c.pncp_id,
              $date AS data_compra FROM fv_itens i JOIN fv_compras c ON c.id = i.compra_id WHERE " . implode(' AND ', $w) . " ORDER BY $order", $a)->fetchAll();
}
