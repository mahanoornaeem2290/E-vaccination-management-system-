<?php
require __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=300');
$u = me();
if (!$u || $u['role'] !== 'parent') { http_response_code(403); exit(json_encode(['error' => 'Sign in as a parent to search the directory.'])); }
$city = trim((string)($_GET['city'] ?? ''));
if (mb_strlen($city) < 2 || mb_strlen($city) > 80 || !preg_match('/^[\p{L}\p{N} .,\'-]+$/u', $city)) {
    http_response_code(400); exit(json_encode(['error' => 'Enter a city name (2–80 characters).']));
}
$key = mb_strtolower($city);
if (isset($_SESSION['osm_hospital_cache'][$key]) && $_SESSION['osm_hospital_cache'][$key]['until'] > time()) {
    echo json_encode($_SESSION['osm_hospital_cache'][$key]['data']); exit;
}
if (isset($_SESSION['osm_last_lookup']) && time() - $_SESSION['osm_last_lookup'] < 1) {
    http_response_code(429); exit(json_encode(['error' => 'Please wait a moment before searching again.']));
}
$_SESSION['osm_last_lookup'] = time();
session_write_close();
function get_json(string $url, ?string $body = null): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 18,
        CURLOPT_USERAGENT => 'VaxCareHospitalDirectory/1.0 (OpenStreetMap public data; user-triggered search)',
        CURLOPT_HTTPHEADER => ['Accept: application/json']]);
    if ($body !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $body); }
    $raw = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if (!$raw || $status < 200 || $status >= 300) throw new RuntimeException('Directory service unavailable. Try again later.');
    $data = json_decode($raw, true);
    if (!is_array($data)) throw new RuntimeException('Directory service returned an invalid response.');
    return $data;
}
try {
    $places = get_json('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' . rawurlencode($city . ', Pakistan'));
    if (!$places) { echo json_encode(['results' => [], 'source' => 'OpenStreetMap']); exit; }
    $lat = (float)$places[0]['lat']; $lon = (float)$places[0]['lon'];
    $query = '[out:json][timeout:15];(node["amenity"~"^(hospital|clinic)$"](around:12000,' . $lat . ',' . $lon . ');way["amenity"~"^(hospital|clinic)$"](around:12000,' . $lat . ',' . $lon . ');relation["amenity"~"^(hospital|clinic)$"](around:12000,' . $lat . ',' . $lon . '););out center tags 40;';
    $data = get_json('https://overpass-api.de/api/interpreter', 'data=' . rawurlencode($query));
    $results = []; $seen = [];
    foreach (($data['elements'] ?? []) as $el) {
        $tags = $el['tags'] ?? []; $name = trim((string)($tags['name'] ?? ''));
        if ($name === '' || isset($seen[mb_strtolower($name)])) continue;
        $seen[mb_strtolower($name)] = true;
        $address = array_filter([$tags['addr:street'] ?? '', $tags['addr:suburb'] ?? '', $tags['addr:city'] ?? '']);
        $results[] = ['name' => $name, 'type' => $tags['amenity'] ?? 'healthcare', 'address' => implode(', ', $address),
            'phone' => $tags['phone'] ?? $tags['contact:phone'] ?? '', 'url' => 'https://www.openstreetmap.org/' . $el['type'] . '/' . $el['id']];
    }
    $result = ['results' => $results, 'source' => 'OpenStreetMap', 'notice' => 'Community-mapped directory only. Confirm details with the facility; stock, prices and booking are not provided.'];
    // Cache the public directory result only in this user's session.
    session_start(); $_SESSION['osm_hospital_cache'][$key] = ['until' => time() + 1800, 'data' => $result]; session_write_close();
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    http_response_code(502); echo json_encode(['error' => $e->getMessage()]);
}
