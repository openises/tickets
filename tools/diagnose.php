<?php
/**
 * TicketsCAD Diagnostic Tool
 *
 * Checks PHP version compatibility, database connectivity, table existence,
 * and common configuration issues. Run this in a browser to diagnose problems.
 *
 * SECURITY: Delete this file after diagnosing the issue!
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><title>TicketsCAD Diagnostics</title>";
echo "<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;line-height:1.6}";
echo ".pass{color:#4ade80}.fail{color:#f87171}.warn{color:#fbbf24}.info{color:#60a5fa}";
echo "h2{color:#93c5fd;border-bottom:1px solid #334;padding-bottom:4px}pre{background:#12131a;padding:10px;border-radius:6px;overflow:auto}</style>";
echo "</head><body>";
echo "<h1>TicketsCAD Diagnostic Report</h1>";
echo "<p>Generated: " . date('Y-m-d H:i:s T') . "</p>";

// ── PHP Version ──
echo "<h2>1. PHP Environment</h2>";
$phpVer = PHP_VERSION;
echo "<p>PHP Version: <strong>$phpVer</strong> ";
if (version_compare($phpVer, '8.2', '>=')) {
    echo "<span class='warn'>(Warning: utf8_encode/utf8_decode removed in 8.2)</span>";
} elseif (version_compare($phpVer, '8.0', '>=')) {
    echo "<span class='pass'>(Good — PHP 8.x supported)</span>";
} elseif (version_compare($phpVer, '7.4', '>=')) {
    echo "<span class='warn'>(PHP 7.4 — end of life but should work)</span>";
} else {
    echo "<span class='fail'>(Too old — PHP 7.4+ recommended)</span>";
}
echo "</p>";

// Check critical functions
$criticalFunctions = [
    'password_hash' => 'Password hashing (PHP 5.5+)',
    'password_verify' => 'Password verification (PHP 5.5+)',
    'json_encode' => 'JSON encoding',
    'json_decode' => 'JSON decoding',
    'mysqli_connect' => 'MySQLi extension',
    'openssl_encrypt' => 'OpenSSL extension',
    'session_start' => 'Session support',
];
foreach ($criticalFunctions as $func => $desc) {
    $exists = function_exists($func);
    echo "<p>" . ($exists ? "<span class='pass'>✓</span>" : "<span class='fail'>✗</span>") . " $desc (<code>$func</code>)</p>";
}

// Check removed functions in PHP 8.2+
if (version_compare($phpVer, '8.2', '>=')) {
    echo "<h3>PHP 8.2+ Removed Functions Check</h3>";
    $removed = ['utf8_encode', 'utf8_decode'];
    foreach ($removed as $func) {
        $exists = function_exists($func);
        if ($exists) {
            echo "<p><span class='pass'>✓</span> <code>$func</code> — available (polyfill installed)</p>";
        } else {
            echo "<p><span class='fail'>✗</span> <code>$func</code> — MISSING! This will cause 500 errors if used in the code.</p>";
        }
    }
}

// ── Database Connection ──
echo "<h2>2. Database Connection</h2>";
$configFile = __DIR__ . '/../incs/mysql.inc.php';
if (!file_exists($configFile)) {
    echo "<p><span class='fail'>✗</span> Config file not found: <code>incs/mysql.inc.php</code></p>";
    echo "<p class='info'>Run the installer first to create this file.</p>";
} else {
    echo "<p><span class='pass'>✓</span> Config file exists</p>";
    include($configFile); // NOSONAR — $configFile is hardcoded to __DIR__.'/../incs/mysql.inc.php', no user input

    $host = isset($mysql_host) ? $mysql_host : '(not set)';
    $user = isset($mysql_user) ? $mysql_user : '(not set)';
    $db = isset($mysql_db) ? $mysql_db : '(not set)';
    echo "<p>Host: <code>$host</code> | User: <code>$user</code> | Database: <code>$db</code></p>";

    // Try connecting
    $pass = isset($mysql_passwd) ? $mysql_passwd : '';
    $conn = @new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        echo "<p><span class='fail'>✗</span> Connection failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p><span class='pass'>✓</span> Connected to database: " . htmlspecialchars($conn->server_info) . "</p>";

        // ── Table Check ──
        echo "<h2>3. Required Tables</h2>";
        $prefix = isset($mysql_prefix) ? $mysql_prefix : '';
        $requiredTables = [
            'user', 'ticket', 'responder', 'in_types', 'facilities', 'action',
            'assigns', 'allocates', 'log', 'settings', 'sound_settings', 'codes',
            'regions', 'region_types', 'road_info', 'mmarkup', 'mmarkup_cats'
        ];
        $existing = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $existing[] = $row[0];
        }
        echo "<p>Total tables found: <strong>" . count($existing) . "</strong></p>";

        $missing = [];
        foreach ($requiredTables as $t) {
            $fullName = $prefix . $t;
            if (in_array($fullName, $existing)) {
                echo "<p><span class='pass'>✓</span> <code>$fullName</code></p>";
            } else {
                echo "<p><span class='fail'>✗</span> <code>$fullName</code> — MISSING</p>";
                $missing[] = $fullName;
            }
        }

        if (!empty($missing)) {
            echo "<p class='warn'>Missing tables detected. Re-run the installer in Upgrade mode to create them.</p>";
        }

        // Check user table for admin account
        echo "<h2>4. Admin Account</h2>";
        $userTable = $prefix . 'user';
        if (in_array($userTable, $existing)) {
            $res = $conn->query("SELECT `id`, `user`, `level`, LENGTH(`passwd`) AS pass_len, LEFT(`passwd`, 4) AS pass_prefix FROM `$userTable` WHERE `level` IN (0, 1) LIMIT 5");
            if ($res && $res->num_rows > 0) {
                echo "<table style='border-collapse:collapse'><tr><th style='padding:4px 8px;border:1px solid #444'>ID</th><th style='padding:4px 8px;border:1px solid #444'>Username</th><th style='padding:4px 8px;border:1px solid #444'>Level</th><th style='padding:4px 8px;border:1px solid #444'>Hash Type</th></tr>";
                while ($row = $res->fetch_assoc()) {
                    $hashType = 'unknown';
                    if ($row['pass_prefix'] === '$2y$' || $row['pass_prefix'] === '$2a$') $hashType = 'bcrypt (modern)';
                    elseif ($row['pass_len'] == 32) $hashType = 'MD5';
                    elseif ($row['pass_len'] == 41 && $row['pass_prefix'][0] === '*') $hashType = 'MySQL PASSWORD()';
                    elseif ($row['pass_len'] == 40) $hashType = 'SHA1';
                    elseif ($row['pass_len'] < 30) $hashType = 'Plain text or short hash';
                    elseif ($row['pass_len'] == 0) $hashType = 'EMPTY';
                    echo "<tr><td style='padding:4px 8px;border:1px solid #444'>{$row['id']}</td><td style='padding:4px 8px;border:1px solid #444'>{$row['user']}</td><td style='padding:4px 8px;border:1px solid #444'>{$row['level']}</td><td style='padding:4px 8px;border:1px solid #444'>$hashType (len={$row['pass_len']})</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p><span class='fail'>✗</span> No admin accounts found in user table!</p>";
            }
        }

        // Check version
        echo "<h2>5. Version Info</h2>";
        $settingsTable = $prefix . 'settings';
        if (in_array($settingsTable, $existing)) {
            // Settings table uses 'name' column in legacy schema, 'key' in some newer installs
            $res = $conn->query("SELECT `value` FROM `$settingsTable` WHERE `name` = '_version' LIMIT 1");
            if (!$res || !$res->num_rows) {
                // Try 'key' column as fallback
                $res = @$conn->query("SELECT `value` FROM `$settingsTable` WHERE `key` = '_version' LIMIT 1");
            }
            if ($res && $row = $res->fetch_assoc()) {
                echo "<p>Database version: <strong>" . htmlspecialchars($row['value']) . "</strong></p>";
            } else {
                echo "<p><span class='warn'>⚠</span> No _version key in settings table (legacy install)</p>";
            }
        }

        // ── Quick PHP compatibility test ──
        echo "<h2>6. PHP Compatibility Test</h2>";

        // Test 1: Try loading functions.inc.php
        echo "<p>Testing <code>functions.inc.php</code> load... ";
        ob_start();
        try {
            // Functions.inc.php has already been partially loaded via the config include chain
            // but let's test a key function
            if (function_exists('get_variable')) {
                echo "<span class='pass'>✓ get_variable() available</span></p>";
            } else {
                // Try loading it
                $funcFile = __DIR__ . '/../incs/functions.inc.php';
                if (file_exists($funcFile)) {
                    include_once($funcFile);
                    echo "<span class='pass'>✓ loaded successfully</span></p>";
                } else {
                    echo "<span class='fail'>✗ file not found</span></p>";
                }
            }
        } catch (Throwable $e) {
            $err = ob_get_clean();
            echo "<span class='fail'>✗ FATAL: " . htmlspecialchars($e->getMessage()) . "</span></p>";
            if ($err) echo "<pre>" . htmlspecialchars($err) . "</pre>";
        }
        $output = ob_get_clean();
        if ($output) echo $output;

        // Test 2: Try an AJAX-style query
        echo "<p>Testing incident query... ";
        try {
            $ticketTable = $prefix . 'ticket';
            if (in_array($ticketTable, $existing)) {
                $res = $conn->query("SELECT COUNT(*) AS cnt FROM `$ticketTable`");
                $row = $res->fetch_assoc();
                echo "<span class='pass'>✓ " . $row['cnt'] . " incidents in database</span></p>";
            } else {
                echo "<span class='warn'>⚠ ticket table not found</span></p>";
            }
        } catch (Throwable $e) {
            echo "<span class='fail'>✗ " . htmlspecialchars($e->getMessage()) . "</span></p>";
        }

        $conn->close();
    }
}

// ── File Permissions ──
echo "<h2>7. File Permissions</h2>";
$checkPaths = [
    'incs/mysql.inc.php' => __DIR__ . '/../incs/mysql.inc.php',
    'incs/functions.inc.php' => __DIR__ . '/../incs/functions.inc.php',
    'incs/browser.inc.php' => __DIR__ . '/../incs/browser.inc.php',
    'top.php' => __DIR__ . '/../top.php',
    'index.php' => __DIR__ . '/../index.php',
];
foreach ($checkPaths as $label => $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $readable = is_readable($path);
        echo "<p>" . ($readable ? "<span class='pass'>✓</span>" : "<span class='fail'>✗</span>") . " $label (perms: $perms)</p>";
    } else {
        echo "<p><span class='fail'>✗</span> $label — NOT FOUND</p>";
    }
}

// ── Server Info ──
echo "<h2>8. Server Info</h2>";
echo "<p>Server software: " . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "</p>";
echo "<p>Document root: " . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "</p>";
echo "<p>Script path: " . htmlspecialchars(__FILE__) . "</p>";
echo "<p>PHP SAPI: " . htmlspecialchars(php_sapi_name()) . "</p>";
echo "<p>Memory limit: " . htmlspecialchars(ini_get('memory_limit')) . "</p>";
echo "<p>Max execution time: " . htmlspecialchars(ini_get('max_execution_time')) . "s</p>";
echo "<p>display_errors: " . htmlspecialchars(ini_get('display_errors')) . "</p>";
echo "<p>error_reporting: " . error_reporting() . "</p>";
$errorLog = ini_get('error_log');
echo "<p>error_log: " . ($errorLog ? htmlspecialchars($errorLog) : 'default (server log)') . "</p>";

echo "<hr><p style='color:#fbbf24'><strong>Security Notice:</strong> Delete this file (<code>tools/diagnose.php</code>) after diagnosing the issue!</p>";
echo "</body></html>";
