<?php
/**
 * Converte coordenadas (GPS do celular) em endereço, usando o OpenStreetMap (Nominatim).
 * Passa pelo servidor para respeitar a política de uso (identificação e cache).
 */
require dirname(__DIR__) . '/app/bootstrap.php';

$lat = filter_var($_GET['lat'] ?? '', FILTER_VALIDATE_FLOAT);
$lng = filter_var($_GET['lng'] ?? '', FILTER_VALIDATE_FLOAT);
if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) json_out(['ok' => false, 'error' => 'Coordenadas inválidas.'], 422);

$key = sprintf('%.5f_%.5f', $lat, $lng);
$cache = storage('geo') . '/' . $key . '.json';
if (is_file($cache) && filemtime($cache) > time() - 30 * 86400) {
    header('X-Cache: hit');
    json_out(json_decode((string) file_get_contents($cache), true));
}
if (!rate_ok('geo', 40, 600)) json_out(['ok' => false, 'error' => 'Muitas consultas. Aguarde um pouco.'], 429);

$url = (string) env('GEO_REVERSE_URL', 'https://nominatim.openstreetmap.org/reverse') . '?' . http_build_query([
    'format' => 'jsonv2', 'lat' => sprintf('%.6f', $lat), 'lon' => sprintf('%.6f', $lng), 'zoom' => 18,
    'addressdetails' => 1, 'accept-language' => 'pt-BR', 'email' => (string) env('GEO_CONTACT_EMAIL', ''),
]);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 12, CURLOPT_CONNECTTIMEOUT => 6,
    CURLOPT_USERAGENT => 'SiteVereadorMaiconGoncalves/1.0 (+' . site_url() . ')',
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
]);
$body = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$j = $body ? json_decode((string) $body, true) : null;
if ($code !== 200 || !is_array($j) || empty($j['address'])) {
    app_log('geo', "falha $code em $key: " . substr((string) $body, 0, 200));
    json_out(['ok' => false, 'error' => 'Não foi possível descobrir o endereço. A localização no mapa será enviada mesmo assim.'], 502);
}

$a = $j['address'];
$pick = function (array $keys) use ($a) { foreach ($keys as $k) if (!empty($a[$k])) return $a[$k]; return ''; };
$street = $pick(['road', 'pedestrian', 'footway', 'path', 'residential']);
$number = $pick(['house_number']);
$suburb = $pick(['suburb', 'neighbourhood', 'quarter', 'city_district', 'village', 'hamlet', 'isolated_dwelling']);
$city = $pick(['city', 'town', 'municipality', 'village']);
$uf = isset($a['ISO3166-2-lvl4']) ? preg_replace('/^BR-/', '', $a['ISO3166-2-lvl4']) : $pick(['state']);
$line1 = trim($street . ($number !== '' ? ', ' . $number : ''));
$line2 = trim(($suburb !== '' && $suburb !== $city ? $suburb . ', ' : '') . $city . ($uf !== '' ? ' - ' . $uf : ''), ', ');
$out = [
    'ok' => true,
    'street' => $street, 'number' => $number, 'suburb' => $suburb, 'city' => $city, 'uf' => $uf, 'postcode' => $pick(['postcode']),
    'line' => $line1,
    'label' => trim($line1 . ($line1 !== '' && $line2 !== '' ? ' - ' : '') . $line2) ?: (string) ($j['display_name'] ?? ''),
    'candidates' => array_values(array_unique(array_filter([$a['suburb'] ?? '', $a['neighbourhood'] ?? '', $a['quarter'] ?? '', $a['city_district'] ?? '', $a['village'] ?? '', $a['hamlet'] ?? '']))),
];
@file_put_contents($cache, json_encode($out, JSON_UNESCAPED_UNICODE));
json_out($out);
