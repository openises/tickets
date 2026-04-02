<?php
/**
 * TicketsCAD Docker Deployment Test Suite
 * Tests actual application functionality against a running instance.
 *
 * Usage: php test_docker_deploy.php [base_url]
 * Default: http://localhost:8080
 */

$base = $argv[1] ?? 'http://localhost:8080';
$pass = 0;
$fail = 0;
$cookieFile = '/tmp/ticketscad_test_cookies.txt';
@unlink($cookieFile);

function test($label, $ok) {
    global $pass, $fail;
    if ($ok) { echo "[PASS] $label\n"; $pass++; }
    else { echo "[FAIL] $label\n"; $fail++; }
    return $ok;
}

function http($url, $post = null) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
}

function httpHeaders($url) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $headers = curl_exec($ch);
    curl_close($ch);
    return $headers;
}

function dbQuery($sql) {
    $result = trim(shell_exec("docker exec ticketscad_db mariadb -u tickets -ptickets tickets -N -e " . escapeshellarg($sql) . " 2>/dev/null"));
    return $result;
}

echo "=== TicketsCAD Deployment Test Suite ===\n";
echo "Target: $base\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$n = 1;

// ── SERVER ──
echo "── Server Reachability ──\n";
$r = http("$base/");
test("$n. Web server responds (HTTP {$r['code']})", $r['code'] == 200); $n++;
test("$n. Returns HTML", strpos($r['body'], '<html') !== false || strpos($r['body'], '<!DOCTYPE') !== false); $n++;

// ── SECURITY HEADERS ──
echo "\n── Security Headers ──\n";
$h = httpHeaders("$base/");
test("$n. X-Content-Type-Options", stripos($h, 'X-Content-Type-Options: nosniff') !== false); $n++;
test("$n. X-Frame-Options", stripos($h, 'X-Frame-Options') !== false); $n++;
test("$n. X-XSS-Protection", stripos($h, 'X-XSS-Protection') !== false); $n++;
test("$n. Server header hidden", stripos($h, 'Apache/') === false); $n++;

// ── LOGIN ──
echo "\n── Authentication ──\n";
$r = http("$base/index.php");
test("$n. Login page loads", strpos($r['body'], 'frm_passwd') !== false || strpos($r['body'], 'password') !== false || strpos($r['body'], 'Login') !== false); $n++;

$r = http("$base/incs/login.inc.php", "frm_user=admin&frm_passwd=admin");
test("$n. Login POST returns 200", $r['code'] == 200); $n++;

// Check authenticated access
$r = http("$base/index.php");
$authenticated = strpos($r['body'], 'admin') !== false || strpos($r['body'], 'Situation') !== false || strpos($r['body'], 'sit_screen') !== false;
test("$n. Session authenticated", $authenticated); $n++;

// Try frameset pages
$r = http("$base/top.php");
test("$n. top.php loads (menu frame)", $r['code'] == 200); $n++;

// ── DATABASE TABLES ──
echo "\n── Database Integrity ──\n";
$tableCount = (int) dbQuery("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='tickets'");
test("$n. Table count ($tableCount, expect 50+)", $tableCount > 50); $n++;

$critical = ['user', 'ticket', 'action', 'responder', 'facilities', 'in_types',
             'settings', 'region', 'allocates', 'assigns', 'codes', 'log',
             'un_status', 'fac_status', 'captions', 'states_translator'];
foreach ($critical as $t) {
    $exists = (int) dbQuery("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='tickets' AND TABLE_NAME='$t'");
    test("$n. Table: $t", $exists === 1); $n++;
}

// ── USERS ──
echo "\n── User Accounts ──\n";
$admin = dbQuery("SELECT user FROM user WHERE level=0 LIMIT 1");
test("$n. Admin user exists ('$admin')", $admin === 'admin'); $n++;

$hash = dbQuery("SELECT passwd FROM user WHERE user='admin' LIMIT 1");
test("$n. Admin password is bcrypt", strpos($hash, '$2y$') === 0); $n++;

$guest = dbQuery("SELECT user FROM user WHERE level=3 LIMIT 1");
test("$n. Guest user exists ('$guest')", $guest === 'guest'); $n++;

$userCount = (int) dbQuery("SELECT COUNT(*) FROM user");
test("$n. User count ($userCount, expect 2+)", $userCount >= 2); $n++;

// ── SEED DATA ──
echo "\n── Seed Data ──\n";
$seeds = [
    ["SELECT COUNT(*) FROM in_types", "Incident types", 1],
    ["SELECT COUNT(*) FROM un_status", "Unit statuses", 1],
    ["SELECT COUNT(*) FROM fac_status", "Facility statuses", 1],
    ["SELECT COUNT(*) FROM region", "Regions", 1],
    ["SELECT COUNT(*) FROM settings", "Settings", 5],
    ["SELECT COUNT(*) FROM states_translator", "US states", 50],
    ["SELECT COUNT(*) FROM captions", "Captions", 1],
    ["SELECT COUNT(*) FROM codes", "Signal codes", 0],
];
foreach ($seeds as $s) {
    $count = (int) dbQuery($s[0]);
    test("$n. {$s[1]} ($count rows, expect {$s[2]}+)", $count >= $s[2]); $n++;
}

// ── PHP EXTENSIONS ──
echo "\n── PHP Extensions ──\n";
$exts = trim(shell_exec("docker exec ticketscad php -m 2>/dev/null"));
$required = ['mysqli', 'gd', 'mbstring', 'zip', 'pdo_mysql', 'curl', 'xml', 'openssl'];
foreach ($required as $ext) {
    test("$n. Extension: $ext", stripos($exts, $ext) !== false); $n++;
}

$phpVer = trim(shell_exec("docker exec ticketscad php -r \"echo PHP_VERSION;\" 2>/dev/null"));
test("$n. PHP version ($phpVer, expect 8.x)", version_compare($phpVer, '8.0.0', '>=')); $n++;

// ── FILE PERMISSIONS ──
echo "\n── File Permissions ──\n";
$configOk = trim(shell_exec("docker exec ticketscad test -r /var/www/html/incs/mysql.inc.php && echo yes || echo no"));
test("$n. mysql.inc.php readable", $configOk === 'yes'); $n++;

$uploadsOk = trim(shell_exec("docker exec ticketscad test -d /var/www/html/uploads && echo yes || echo no"));
test("$n. uploads directory exists", $uploadsOk === 'yes'); $n++;

$owner = trim(shell_exec("docker exec ticketscad stat -c '%U' /var/www/html/incs/mysql.inc.php 2>/dev/null"));
test("$n. Web files owned by www-data ($owner)", $owner === 'www-data'); $n++;

// ── XSS PROTECTION ──
echo "\n── XSS Protection (reported vulnerabilities) ──\n";
$xss = urlencode('123\'><script>alert(1)</script>');

$r = http("$base/single_unit.php?id=$xss");
test("$n. single_unit.php escapes id param", strpos($r['body'], '<script>alert(1)</script>') === false); $n++;

$r = http("$base/single.php?ticket_id=$xss");
test("$n. single.php escapes ticket_id", strpos($r['body'], '<script>alert(1)</script>') === false); $n++;

$r = http("$base/street_view.php?thelat=$xss&thelng=$xss");
test("$n. street_view.php escapes lat/lng", strpos($r['body'], '<script>alert(1)</script>') === false); $n++;

$r = http("$base/add_note.php?ticket_id=$xss");
test("$n. add_note.php escapes ticket_id", strpos($r['body'], '<script>alert(1)</script>') === false); $n++;

$r = http("$base/opena.php?frm_call=$xss");
test("$n. opena.php escapes frm_call", strpos($r['body'], '<script>alert(1)</script>') === false); $n++;

// ── PHP COMPAT LAYER ──
echo "\n── PHP Compatibility ──\n";
$compat = trim(shell_exec("docker exec ticketscad php -r \"require '/var/www/html/incs/compat.inc.php'; echo 'OK';\" 2>&1"));
test("$n. Compat layer loads without error", $compat === 'OK'); $n++;

$utf8 = trim(shell_exec("docker exec ticketscad php -r \"require '/var/www/html/incs/compat.inc.php'; echo function_exists('utf8_encode') ? 'yes' : 'no';\" 2>&1"));
test("$n. utf8_encode available", $utf8 === 'yes'); $n++;

// ── PASSWORD FORMATS ──
echo "\n── Password Hash Support ──\n";
$hashTest = trim(shell_exec("docker exec ticketscad php -r \"
require '/var/www/html/incs/compat.inc.php';
require '/var/www/html/incs/security.inc.php';
\\\$tests = [
    ['bcrypt', password_hash('test', PASSWORD_BCRYPT), 'test'],
    ['md5', md5('test'), 'test'],
    ['mysql_pw', '*' . strtoupper(sha1(sha1('test', true))), 'test'],
    ['sha1', sha1('test'), 'test'],
];
\\\$ok = 0;
foreach (\\\$tests as \\\$t) {
    \\\$r = verify_password(\\\$t[2], \\\$t[1]);
    if (\\\$r['valid']) \\\$ok++;
}
echo \\\$ok;
\" 2>&1"));
test("$n. All 4 password hash formats verified ($hashTest/4)", $hashTest === '4'); $n++;

// ── SUMMARY ──
echo "\n" . str_repeat("=", 60) . "\n";
$total = $pass + $fail;
echo "RESULTS: $pass passed, $fail failed out of $total tests\n";
echo str_repeat("=", 60) . "\n";

if ($fail === 0) {
    echo "\nALL TESTS PASS — Deployment verified successfully!\n";
} else {
    echo "\nSOME TESTS FAILED — Review output above.\n";
}

@unlink($cookieFile);
exit($fail > 0 ? 1 : 0);
