<?php
// Start output buffering to prevent stray whitespace from included files
// from corrupting binary PNG output.
ob_start();

/**
 * Tile Caching Proxy for Tickets CAD
 *
 * Serves map tiles through a local cache, fetching from the configured
 * upstream tile server on cache miss. Complies with OSM tile usage policy
 * by only fetching tiles users actively request.
 *
 * Cache directory: _osm/tiles/z/x/y.png (shared with bulk downloader)
 * Cache duration: Configurable via tile_cache_days setting (default 60 days).
 *                 Set to 0 to bypass cache and always fetch fresh tiles.
 *
 * 3/14/2026 - New file
 */

// Fallback cache age if DB setting is unavailable (7 days minimum per OSM policy)
define('TILE_CACHE_FALLBACK_SECONDS', 604800);

// Transparent 1x1 PNG for error responses
define('TRANSPARENT_PNG', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQI12NgAAIABQABNjN9GQAAAABJRElEQrkJggg=='));

// Helper functions declared before use

function serve_tile($file_path, $source, $max_age) {
    // Discard any stray output from included PHP files
    if (ob_get_level()) {
        ob_end_clean();
    }
    $size = filesize($file_path);
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=' . intval($max_age));
    header('X-Tile-Source: ' . $source);
    header('Content-Length: ' . $size);
    readfile($file_path);
}

function build_tile_url($template, $z, $x, $y) {
    $subdomains = array('a', 'b', 'c');
    $s = $subdomains[array_rand($subdomains)];
    $url = str_replace(
        array('{z}', '{x}', '{y}', '{s}'),
        array($z, $x, $y, $s),
        $template
    );
    return $url;
}

// ---- Validate inputs ----

$z = isset($_GET['z']) ? intval($_GET['z']) : -1;
$x = isset($_GET['x']) ? intval($_GET['x']) : -1;
$y = isset($_GET['y']) ? intval($_GET['y']) : -1;

if ($z < 0 || $z > 20 || $x < 0 || $y < 0) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(400);
    header('Content-Type: image/png');
    echo TRANSPARENT_PNG;
    exit;
}

// ---- Load application ----

require_once('./incs/functions.inc.php');
require_once('./incs/versions.inc.php');

// ---- Check that proxy mode is enabled ----

if (get_tile_mode() !== 'proxy') {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo 'Tile proxy is not enabled. Set tile_mode to proxy in Tile Settings.';
    exit;
}

// ---- Read configurable cache duration ----

$cache_days_raw = get_variable('tile_cache_days');
if ($cache_days_raw === FALSE || trim($cache_days_raw) === '') {
    $tile_cache_seconds = TILE_CACHE_FALLBACK_SECONDS;
} else {
    $cache_days = intval($cache_days_raw);
    $tile_cache_seconds = ($cache_days > 0) ? $cache_days * 86400 : 0;
}

// ---- Cache path ----

$tile_root = rtrim(getcwd(), '/\\') . DIRECTORY_SEPARATOR . '_osm' . DIRECTORY_SEPARATOR . 'tiles';
$cache_dir = $tile_root . DIRECTORY_SEPARATOR . $z . DIRECTORY_SEPARATOR . $x;
$cache_file = $cache_dir . DIRECTORY_SEPARATOR . $y . '.png';

// ---- Serve from cache if fresh (skip when tile_cache_days=0) ----

if ($tile_cache_seconds > 0 && file_exists($cache_file)) {
    $age = time() - filemtime($cache_file);
    if ($age < $tile_cache_seconds) {
        serve_tile($cache_file, 'cache', $tile_cache_seconds - $age);
        exit;
    }
}

// ---- Fetch from upstream ----

$tile_server_url = get_variable('tile_server_url');
if ($tile_server_url === FALSE || trim($tile_server_url) === '') {
    $tile_server_url = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
}

$upstream_url = build_tile_url($tile_server_url, $z, $x, $y);
$user_agent = get_tile_user_agent();

$tile_data = false;
$http_code = 0;
$stale_exists = file_exists($cache_file);

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $upstream_url);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Conditional request if we have a stale cached copy
    if ($stale_exists) {
        $if_modified = gmdate('D, d M Y H:i:s', filemtime($cache_file)) . ' GMT';
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'If-Modified-Since: ' . $if_modified
        ));
    }

    $tile_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
} else {
    // Fallback to file_get_contents with stream context
    $context = stream_context_create(array(
        'http' => array(
            'user_agent' => $user_agent,
            'timeout' => 15,
            'ignore_errors' => true,
        )
    ));

    $tile_data = @file_get_contents($upstream_url, false, $context);
    // Parse HTTP response code from wrapper
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/^HTTP\/\d+\.?\d*\s+(\d{3})/', $header, $m)) {
                $http_code = (int)$m[1];
            }
        }
    }
}

// ---- Handle upstream response ----

if ($http_code === 304 && $stale_exists) {
    // Not modified -- refresh cache timestamp and serve stale copy
    touch($cache_file);
    serve_tile($cache_file, 'revalidated', $tile_cache_seconds);
    exit;
}

if ($http_code === 200 && $tile_data !== false && strlen($tile_data) > 0) {
    // Save to cache
    if (!is_dir($tile_root)) {
        @mkdir($tile_root, 0755, true);
    }
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }
    if ($fp = @fopen($cache_file, 'wb')) {
        fwrite($fp, $tile_data);
        fclose($fp);
    }

    // Serve the freshly fetched tile -- clean buffer to prevent stray output
    if (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=' . $tile_cache_seconds);
    header('X-Tile-Source: upstream');
    header('Content-Length: ' . strlen($tile_data));
    echo $tile_data;
    exit;
}

// ---- Error handling ----

if ($stale_exists) {
    // Serve stale cache as fallback
    touch($cache_file);
    serve_tile($cache_file, 'stale', 300);
    exit;
}

// No cached copy and upstream failed -- return transparent tile
if (ob_get_level()) {
    ob_end_clean();
}
http_response_code(503);
header('Content-Type: image/png');
header('Cache-Control: no-cache');
header('X-Tile-Source: error');
echo TRANSPARENT_PNG;
exit;
