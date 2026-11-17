<?php
/**
 * Amigos do Bem: instituições de Nova Friburgo que recebem doações, o que cada uma aceita e como doar.
 * O gabinete só divulga: nenhuma doação passa por ele. Tabelas criadas sozinhas (ab_tables()).
 */
defined('APP') or exit;

const AB_STATUS = ['rascunho' => 'Rascunho', 'publicada' => 'Publicada', 'oculta' => 'Oculta'];
const AB_CAUSAS = [
    'criancas' => 'Crianças e adolescentes', 'idosos' => 'Idosos', 'animais' => 'Animais', 'rua' => 'Pessoas em situação de rua',
    'deficiencia' => 'Pessoas com deficiência', 'familias' => 'Famílias em vulnerabilidade', 'saude' => 'Saúde', 'mulheres' => 'Mulheres',
    'dependencia' => 'Recuperação de dependentes', 'educacao' => 'Educação e cultura', 'outra' => 'Outras causas',
];
/** Ícones (Phosphor, preenchidos) que a equipe pode escolher para um tipo de doação. Listados também no site/build.py. */
const AB_ICONES = ['bowl-food', 't-shirt', 'bed', 'puzzle-piece', 'pencil-simple', 'hand-soap', 'baby', 'paw-print', 'armchair', 'book-open',
    'hand-heart', 'hand-coins', 'gift', 'drop', 'first-aid-kit', 'tote', 'heart', 'house-line'];
const AB_TIPOS_PADRAO = [
    ['alimentos', 'Alimentos', 'bowl-food'], ['roupas', 'Roupas e calçados', 't-shirt'], ['cobertores', 'Cobertores e agasalhos', 'bed'],
    ['brinquedos', 'Brinquedos', 'puzzle-piece'], ['escolar', 'Material escolar', 'pencil-simple'], ['higiene', 'Higiene e limpeza', 'hand-soap'],
    ['fraldas', 'Fraldas', 'baby'], ['racao', 'Ração e itens para animais', 'paw-print'], ['moveis', 'Móveis e eletrodomésticos', 'armchair'],
    ['livros', 'Livros', 'book-open'], ['voluntario', 'Trabalho voluntário', 'hand-heart'], ['dinheiro', 'Dinheiro (PIX)', 'hand-coins'],
];
const AB_CFG_DEFAULTS = [
    'pagina_publica' => '1', // o link funciona para todos (fora do menu e do Google)
    'indexar' => '0',        // aparece no Google e no sitemap
    'intro' => 'Quer ajudar e não sabe por onde começar? Escolha o que você tem para doar e encontre as instituições de Nova Friburgo que precisam disso, com endereço, horários e contato. A doação é feita direto à instituição.',
    'demo_criado' => '0',
];

function ab_tables(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS ab_instituicoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(120) NOT NULL UNIQUE,
        nome VARCHAR(160) NOT NULL, causa VARCHAR(20) NULL,
        resumo VARCHAR(300) NULL, historia TEXT NULL,
        logo VARCHAR(255) NULL, capa VARCHAR(255) NULL, fotos TEXT NULL,
        cnpj VARCHAR(20) NULL, mostrar_cnpj TINYINT(1) NOT NULL DEFAULT 0,
        nao_aceita VARCHAR(500) NULL, busca_em_casa TINYINT(1) NOT NULL DEFAULT 0, obs_doacao TEXT NULL,
        endereco VARCHAR(200) NULL, bairro VARCHAR(120) NULL, lat DECIMAL(10,7) NULL, lng DECIMAL(10,7) NULL, horarios VARCHAR(500) NULL,
        whatsapp VARCHAR(30) NULL, telefone VARCHAR(30) NULL, email VARCHAR(160) NULL, instagram VARCHAR(160) NULL, facebook VARCHAR(255) NULL, site VARCHAR(255) NULL,
        pix_chave VARCHAR(140) NULL, pix_tipo VARCHAR(20) NULL, pix_titular VARCHAR(160) NULL,
        resp_nome VARCHAR(120) NULL, resp_cargo VARCHAR(80) NULL, resp_telefone VARCHAR(30) NULL, resp_email VARCHAR(160) NULL,
        status ENUM('rascunho','publicada','oculta') NOT NULL DEFAULT 'rascunho',
        autorizado TINYINT(1) NOT NULL DEFAULT 0, autorizado_em DATE NULL,
        verificada_em DATE NULL, verificada_por INT NULL, nota_interna TEXT NULL,
        demo TINYINT(1) NOT NULL DEFAULT 0, origem VARCHAR(20) NOT NULL DEFAULT 'painel',
        token CHAR(32) NULL, cliques_wa INT NOT NULL DEFAULT 0,
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP, atualizado_em DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        KEY (status), KEY (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS ab_tipos (
        id INT AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(40) NOT NULL UNIQUE, nome VARCHAR(80) NOT NULL,
        icone VARCHAR(40) NOT NULL DEFAULT 'gift', ordem INT NOT NULL DEFAULT 0, ativo TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS ab_inst_tipos (
        inst_id INT NOT NULL, tipo_id INT NOT NULL, obs VARCHAR(200) NULL, PRIMARY KEY (inst_id, tipo_id),
        CONSTRAINT ab_it_inst FOREIGN KEY (inst_id) REFERENCES ab_instituicoes(id) ON DELETE CASCADE,
        CONSTRAINT ab_it_tipo FOREIGN KEY (tipo_id) REFERENCES ab_tipos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS ab_necessidades (
        id INT AUTO_INCREMENT PRIMARY KEY, inst_id INT NOT NULL, item VARCHAR(160) NOT NULL, quantidade VARCHAR(60) NULL,
        prazo DATE NULL, criado_em DATETIME DEFAULT CURRENT_TIMESTAMP, KEY (inst_id),
        CONSTRAINT ab_nec_inst FOREIGN KEY (inst_id) REFERENCES ab_instituicoes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS ab_config (chave VARCHAR(60) PRIMARY KEY, valor TEXT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    if (!(int) $pdo->query('SELECT COUNT(*) FROM ab_tipos')->fetchColumn()) {
        foreach (AB_TIPOS_PADRAO as $i => [$s, $n, $ic]) q('INSERT INTO ab_tipos (slug, nome, icone, ordem) VALUES (?, ?, ?, ?)', [$s, $n, $ic, $i + 1]);
    }
    if (ab_cfg('demo_criado') !== '1') { ab_seed_demo(); ab_cfg_set('demo_criado', '1'); }
}

function ab_cfg(string $k): string
{
    if (!isset($GLOBALS['ab_cfg_cache'])) {
        $GLOBALS['ab_cfg_cache'] = AB_CFG_DEFAULTS;
        try { foreach (q('SELECT chave, valor FROM ab_config')->fetchAll() as $r) $GLOBALS['ab_cfg_cache'][$r['chave']] = (string) $r['valor']; } catch (Throwable $ex) {}
    }
    return $GLOBALS['ab_cfg_cache'][$k] ?? '';
}
function ab_cfg_set(string $k, string $v): void
{
    q('INSERT INTO ab_config (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)', [$k, $v]);
    ab_cfg($k);
    $GLOBALS['ab_cfg_cache'][$k] = $v;
}
/** O link /amigos-do-bem/ funciona para quem não está logado? */
function ab_is_public(): bool
{
    if (!db_or_null()) return false;
    try { ab_tables(); return ab_cfg('pagina_publica') === '1'; } catch (Throwable $ex) { return false; }
}

/* ---------- leitura ---------- */
function ab_tipos(bool $soAtivos = true): array
{
    $out = [];
    foreach (q('SELECT * FROM ab_tipos' . ($soAtivos ? ' WHERE ativo = 1' : '') . ' ORDER BY ordem, nome')->fetchAll() as $t) $out[(int) $t['id']] = $t;
    return $out;
}
/** Instituições com os tipos aceitos e as necessidades em dia. $where = 'publicada' para o site. */
function ab_list(?string $status = 'publicada'): array
{
    $rows = q('SELECT * FROM ab_instituicoes' . ($status ? ' WHERE status = ?' : '') . ' ORDER BY demo, nome', $status ? [$status] : [])->fetchAll();
    return ab_hydrate($rows);
}
function ab_get(string $slug, bool $any = false): ?array
{
    $r = q('SELECT * FROM ab_instituicoes WHERE slug = ?' . ($any ? '' : " AND status = 'publicada'"), [$slug])->fetch();
    return $r ? ab_hydrate([$r])[0] : null;
}
function ab_hydrate(array $rows): array
{
    if (!$rows) return [];
    $ids = array_map(function ($r) { return (int) $r['id']; }, $rows);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $tipos = []; $nec = [];
    foreach (q("SELECT it.inst_id, it.obs, t.* FROM ab_inst_tipos it JOIN ab_tipos t ON t.id = it.tipo_id WHERE t.ativo = 1 AND it.inst_id IN ($ph) ORDER BY t.ordem", $ids)->fetchAll() as $t) $tipos[(int) $t['inst_id']][] = $t;
    foreach (q("SELECT * FROM ab_necessidades WHERE inst_id IN ($ph) AND (prazo IS NULL OR prazo >= CURDATE()) ORDER BY prazo IS NULL, prazo, id", $ids)->fetchAll() as $n) $nec[(int) $n['inst_id']][] = $n;
    foreach ($rows as &$r) {
        $r['tipos'] = $tipos[(int) $r['id']] ?? [];
        $r['necessidades'] = $nec[(int) $r['id']] ?? [];
        $r['fotos'] = json_decode((string) $r['fotos'], true) ?: [];
    }
    return $rows;
}

/* ---------- formatação ---------- */
function ab_causa(?string $c): string { return AB_CAUSAS[(string) $c] ?? 'Outras causas'; }
function ab_img(?string $src): string { return $src ? url('/' . ltrim($src, '/')) : ''; }
function ab_digits(?string $s): string { return preg_replace('/\D+/', '', (string) $s); }
/** WhatsApp no formato wa.me (55 + DDD + número). */
function ab_wa(?string $n): string
{
    $d = ab_digits($n);
    if ($d === '') return '';
    if (strlen($d) <= 11) $d = '55' . $d;
    return $d;
}
function ab_phone_fmt(?string $n): string
{
    $d = ab_digits($n);
    if (strlen($d) === 11) return sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 5), substr($d, 7));
    if (strlen($d) === 10) return sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 4), substr($d, 6));
    return (string) $n;
}
function ab_insta(?string $s): string
{
    $s = trim((string) $s);
    if ($s === '') return '';
    if (preg_match('#instagram\.com/([A-Za-z0-9_.]+)#', $s, $m)) return $m[1];
    return ltrim($s, '@');
}
function ab_cnpj(?string $c): string
{
    $d = ab_digits($c);
    return strlen($d) === 14 ? vsprintf('%s.%s.%s/%s-%s', [substr($d, 0, 2), substr($d, 2, 3), substr($d, 5, 3), substr($d, 8, 4), substr($d, 12, 2)]) : (string) $c;
}
function ab_prazo(?string $d): string
{
    if (!$d) return '';
    $days = (int) floor((strtotime($d) - strtotime(date('Y-m-d'))) / 86400);
    if ($days <= 0) return 'até hoje';
    if ($days === 1) return 'até amanhã';
    return 'até ' . date('d/m', strtotime($d));
}
/** "Verificada há X" para o selo da página. */
function ab_verificada(?string $d): string { return $d ? 'Informações conferidas pelo gabinete em ' . date('d/m/Y', strtotime($d)) : ''; }
function ab_slug_unique(string $nome, int $exceptId = 0): string
{
    $base = slugify($nome) ?: 'instituicao';
    $base = substr($base, 0, 100);
    $s = $base; $i = 2;
    while (q('SELECT id FROM ab_instituicoes WHERE slug = ? AND id <> ?', [$s, $exceptId])->fetch()) $s = $base . '-' . $i++;
    return $s;
}
function ab_new_token(): string { return bin2hex(random_bytes(16)); }

/* ---------- exemplos (fictícios) ---------- */
function ab_seed_demo(): void
{
    $tipoId = [];
    foreach (q('SELECT id, slug FROM ab_tipos')->fetchAll() as $t) $tipoId[$t['slug']] = (int) $t['id'];
    $img = function (string $k) { return 'img/amigos-exemplo/' . $k . '.webp'; };
    $hoje = date('Y-m-d');
    $demo = [
        ['Lar Semente de Esperança', 'criancas', 'Conselheiro Paulino', 'Rua das Hortênsias, 120', -22.2318, -42.5232,
            'Acolhe 32 crianças e adolescentes de 4 a 17 anos com reforço escolar, alimentação e atividades no contraturno.',
            "O Lar Semente de Esperança começou em 2009, na garagem de uma moradora de Conselheiro Paulino que passou a receber as crianças da rua depois da escola. Com a ajuda de vizinhos, a garagem virou uma casa e hoje atende 32 crianças e adolescentes de segunda a sexta.\n\nAs crianças chegam depois da aula, almoçam, fazem o dever de casa com voluntários e participam de oficinas de leitura, música e esporte. Muitas famílias atendidas dependem dessa refeição.\n\nO maior desafio é manter a despensa cheia e o material escolar em dia ao longo do ano.",
            ['alimentos' => 'Arroz, feijão, leite e frutas', 'roupas' => 'Tamanhos 4 a 16', 'brinquedos' => '', 'escolar' => 'Cadernos, lápis e mochilas', 'voluntario' => 'Reforço escolar e oficinas'],
            'Roupas rasgadas ou muito usadas', 'Segunda a sexta, das 8h às 17h', 1, [['Leite integral', '60 litros', '+12 days'], ['Cadernos brochura', '40 unidades', '+25 days']], 'lar-semente'],
        ['Casa Vovó Aurora', 'idosos', 'Olaria', 'Rua Coronel Viana, 845', -22.2705, -42.5258,
            'Casa de repouso filantrópica que cuida de 18 idosos sem família ou em situação de abandono.',
            "A Casa Vovó Aurora recebeu esse nome em homenagem a dona Aurora, primeira moradora, que viveu ali até os 98 anos. A casa abriga 18 idosos, a maioria sem família por perto, com cuidadoras, alimentação e acompanhamento de saúde.\n\nAlém dos cuidados do dia a dia, a casa organiza visitas, aniversários do mês e passeios. Os voluntários que vêm conversar, ler ou tocar música fazem diferença na rotina dos moradores.",
            ['fraldas' => 'Fraldas geriátricas tamanhos M e G', 'higiene' => 'Sabonete, xampu, creme hidratante', 'alimentos' => '', 'cobertores' => '', 'voluntario' => 'Visitas, música e leitura'],
            'Remédios vencidos ou abertos', 'Todos os dias, das 9h às 18h', 0, [['Fraldas geriátricas G', '15 pacotes', '+8 days']], 'vovo-aurora'],
        ['Abrigo Patas da Serra', 'animais', 'Riograndina', 'Estrada Riograndina, km 4', -22.2105, -42.5602,
            'Resgata, trata e encaminha para adoção cães e gatos abandonados. Hoje cuida de 74 animais.',
            "O Abrigo Patas da Serra nasceu de um grupo de protetores independentes que se juntou para dividir as despesas de ração e veterinário. Hoje o abrigo mantém 74 animais, entre cães e gatos, todos vacinados e castrados antes da adoção.\n\nAos sábados acontece a feira de adoção. O abrigo também orienta sobre castração e posse responsável.",
            ['racao' => 'Ração para cães e gatos adultos e filhotes', 'cobertores' => 'Cobertores e toalhas velhas servem', 'higiene' => 'Água sanitária e desinfetante', 'dinheiro' => 'Ajuda a pagar veterinário', 'voluntario' => 'Passeios e feira de adoção'],
            'Ração vencida ou aberta', 'Terça a domingo, das 9h às 16h', 1, [['Ração para filhotes', '50 kg', '+10 days'], ['Desinfetante', '20 litros', null]], 'patas-da-serra'],
        ['Projeto Mesa Solidária', 'rua', 'Centro', 'Praça Getúlio Vargas (ponto de encontro)', -22.2846, -42.5316,
            'Grupo de voluntários que serve jantar quente para cerca de 120 pessoas em situação de rua, três noites por semana.',
            "O Projeto Mesa Solidária reúne voluntários de várias igrejas e bairros que cozinham juntos e servem jantar no Centro às segundas, quartas e sextas. Em média, 120 pessoas são atendidas por noite.\n\nNo inverno, o projeto também distribui cobertores e agasalhos e organiza mutirões de banho e corte de cabelo com parceiros.",
            ['alimentos' => 'Arroz, macarrão, carne e legumes', 'cobertores' => 'Principalmente no inverno', 'roupas' => 'Roupas de frio e meias', 'higiene' => 'Kits com escova, pasta e sabonete', 'voluntario' => 'Cozinha e distribuição'],
            'Alimentos já preparados em casa', 'Entregas nas segundas, quartas e sextas, das 17h às 19h', 1, [['Cobertores', '80 unidades', '+20 days']], 'mesa-solidaria'],
        ['Instituto Passo a Passo', 'deficiencia', 'Cônego', 'Rua Monsenhor Miranda, 300', -22.3021, -42.5487,
            'Oferece fisioterapia, terapia ocupacional e oficinas para 45 crianças e jovens com deficiência.',
            "O Instituto Passo a Passo foi criado por um grupo de mães de crianças com deficiência que não encontravam atendimento perto de casa. Hoje atende 45 crianças e jovens com fisioterapia, terapia ocupacional, fonoaudiologia e oficinas de arte.\n\nO instituto também apoia as famílias com orientação sobre direitos e benefícios.",
            ['brinquedos' => 'Brinquedos pedagógicos e de encaixe', 'livros' => 'Livros infantis', 'moveis' => 'Cadeiras, mesas e colchonetes', 'dinheiro' => '', 'voluntario' => 'Profissionais de saúde e oficineiros'],
            '', 'Segunda a sexta, das 8h às 16h', 0, [], 'passo-a-passo'],
        ['Associação Mãos que Acolhem', 'familias', 'Duas Pedras', 'Rua São Jorge, 77', -22.2652, -42.5476,
            'Distribui cestas básicas e apoia 90 famílias em vulnerabilidade, com encaminhamento para emprego e cursos.',
            "A Associação Mãos que Acolhem começou durante as chuvas de 2011, quando moradores de Duas Pedras se organizaram para ajudar as famílias desabrigadas. A ajuda emergencial virou trabalho permanente.\n\nHoje a associação acompanha 90 famílias com cesta básica mensal, doação de móveis para quem está recomeçando e encaminhamento para cursos e vagas de emprego.",
            ['alimentos' => 'Itens de cesta básica', 'moveis' => 'Fogão, geladeira, camas e colchões em bom estado', 'roupas' => '', 'escolar' => ''],
            'Móveis quebrados', 'Sábados, das 9h às 13h', 1, [['Óleo de soja', '90 unidades', '+15 days']], 'maos-que-acolhem'],
    ];
    foreach ($demo as [$nome, $causa, $bairro, $end, $lat, $lng, $resumo, $hist, $tipos, $nao, $hor, $busca, $necs, $k]) {
        q('INSERT INTO ab_instituicoes (slug, nome, causa, resumo, historia, logo, capa, fotos, nao_aceita, busca_em_casa, endereco, bairro, lat, lng, horarios,
            whatsapp, telefone, email, instagram, pix_chave, pix_tipo, pix_titular, status, autorizado, autorizado_em, verificada_em, demo, origem, token)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'publicada\', 1, ?, ?, 1, \'exemplo\', ?)', [
            ab_slug_unique($nome), $nome, $causa, $resumo, $hist, $img($k . '-logo'), $img($k . '-capa'),
            json_encode([['src' => $img($k . '-1')], ['src' => $img($k . '-2')]]), $nao ?: null, $busca, $end, $bairro, $lat, $lng, $hor,
            '22900000000', '2225000000', 'contato@exemplo.org', 'exemplo', '00.000.000/0001-00', 'cnpj', $nome . ' (exemplo)', $hoje, $hoje, ab_new_token()]);
        $iid = (int) db()->lastInsertId();
        foreach ($tipos as $slug => $obs) if (isset($tipoId[$slug])) q('INSERT INTO ab_inst_tipos (inst_id, tipo_id, obs) VALUES (?, ?, ?)', [$iid, $tipoId[$slug], $obs ?: null]);
        foreach ($necs as [$item, $qtd, $prazo]) q('INSERT INTO ab_necessidades (inst_id, item, quantidade, prazo) VALUES (?, ?, ?, ?)', [$iid, $item, $qtd, $prazo ? date('Y-m-d', strtotime($prazo)) : null]);
    }
}

/** Salva o que a instituição mudou pelo link secreto: necessidades, tipos, o que não aceita e horários. */
function ab_save_from_token(array $inst, array $p): void
{
    $id = (int) $inst['id'];
    $rem = array_map('intval', (array) ($p['remover'] ?? []));
    if ($rem) q('DELETE FROM ab_necessidades WHERE inst_id = ? AND id IN (' . implode(',', array_fill(0, count($rem), '?')) . ')', array_merge([$id], $rem));
    $items = (array) ($p['nec_item'] ?? []);
    foreach ($items as $k => $item) {
        $item = mb_substr(trim((string) $item), 0, 160);
        if ($item === '') continue;
        $prazo = (string) ($p['nec_prazo'][$k] ?? '');
        q('INSERT INTO ab_necessidades (inst_id, item, quantidade, prazo) VALUES (?, ?, ?, ?)', [$id, $item,
            mb_substr(trim((string) ($p['nec_qtd'][$k] ?? '')), 0, 60) ?: null, preg_match('/^\d{4}-\d{2}-\d{2}$/', $prazo) ? $prazo : null]);
    }
    $valid = array_keys(ab_tipos());
    $tipos = array_values(array_intersect($valid, array_map('intval', (array) ($p['tipos'] ?? []))));
    $obs = [];
    foreach (q('SELECT tipo_id, obs FROM ab_inst_tipos WHERE inst_id = ?', [$id])->fetchAll() as $r) $obs[(int) $r['tipo_id']] = $r['obs'];
    q('DELETE FROM ab_inst_tipos WHERE inst_id = ?', [$id]);
    foreach ($tipos as $t) q('INSERT INTO ab_inst_tipos (inst_id, tipo_id, obs) VALUES (?, ?, ?)', [$id, $t, $obs[$t] ?? null]);
    q('UPDATE ab_instituicoes SET nao_aceita = ?, horarios = ? WHERE id = ?', [mb_substr(trim((string) ($p['nao_aceita'] ?? '')), 0, 500) ?: null, mb_substr(trim((string) ($p['horarios'] ?? '')), 0, 500) ?: null, $id]);
    app_log('amigos', 'Atualizado pelo link da instituição: ' . $inst['nome']);
}
