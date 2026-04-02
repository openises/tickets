<?php
/**
 * SQL Injection Audit Scanner for Tickets CAD Legacy Codebase
 * Scans all PHP files for potential SQL injection vulnerabilities.
 *
 * Usage: php sqli_audit.php
 */

$base = dirname(__DIR__);  // tickets/ directory
$findings = [];
$file_count = 0;

$skip_dirs = ['vendor', 'lib', 'tests', 'node_modules', '.git'];
$skip_files = ['MySQLDump.class.php', 'compat.inc.php', 'sqli_audit.php'];

function scan_directory($dir, $skip_dirs, $skip_files, &$findings, &$file_count) {
    $entries = scandir($dir);
    foreach ($entries as $e) {
        if ($e === '.' || $e === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $e;
        if (is_dir($path)) {
            if (!in_array($e, $skip_dirs)) {
                scan_directory($path, $skip_dirs, $skip_files, $findings, $file_count);
            }
            continue;
        }
        if (substr($e, -4) !== '.php') continue;
        if (in_array($e, $skip_files)) continue;

        $file_count++;
        scan_file($path, $findings);
    }
}

function scan_file($path, &$findings) {
    $lines = @file($path);
    if (!$lines) return;

    $in_block_comment = false;

    // SQL keywords pattern
    $sql_kw = '/\b(SELECT|INSERT|UPDATE|DELETE|WHERE|LIMIT|ORDER\s+BY|GROUP\s+BY|HAVING|LIKE|SET\s|VALUES|INTO|FROM|JOIN)\b/i';

    // Direct user input pattern
    $user_input = '/\$_(GET|POST|REQUEST|COOKIE)\s*\[/';

    // Sanitization patterns
    $sanitized = '/sanitize_int|sanitize_string|intval|\(int\)|\(integer\)|floatval|\(float\)|sanitize_coordinate/';

    // Prepared statement indicator
    $prepared = '/\?\s*[,\)\'"]|\?\s*$/';

    // Track user-input-derived variables
    $user_vars = [];

    foreach ($lines as $idx => $line) {
        $ln = $idx + 1;
        $t = trim($line);

        // Handle block comments
        if ($in_block_comment) {
            if (strpos($t, '*/') !== false) $in_block_comment = false;
            continue;
        }
        if (strpos($t, '/*') === 0 && strpos($t, '*/') === false) {
            $in_block_comment = true;
            continue;
        }
        if (strpos($t, '//') === 0 || strpos($t, '#') === 0) continue;

        // Track variable assignments from user input WITHOUT sanitization
        if (preg_match('/\$(\w+)\s*=\s*\$_(GET|POST|REQUEST|COOKIE)\s*\[([\'"])(\w+)\3\]/', $line, $m)) {
            $varname = $m[1];
            $source = '$_' . $m[2] . "['" . $m[4] . "']";

            if (!preg_match($sanitized, $line)) {
                $user_vars[$varname] = ['source' => $source, 'line' => $ln, 'sanitized' => false];
            } else {
                $user_vars[$varname] = ['source' => $source, 'line' => $ln, 'sanitized' => true];
            }
        }

        // === HIGH SEVERITY: Direct $_GET/$_POST in SQL string ===
        if (preg_match($sql_kw, $line) && preg_match($user_input, $line)) {
            // Skip if the line uses ? placeholders with arrays
            if (preg_match('/,\s*\[/', $line)) continue;
            // Skip if wrapped in sanitize_int/intval
            if (preg_match($sanitized, $line)) continue;
            // Skip HTML SELECT tags
            if (preg_match('/<SELECT|<\/SELECT/i', $line)) continue;

            $findings[] = [
                'file' => $path,
                'line' => $ln,
                'severity' => 'HIGH',
                'type' => 'Direct user input in SQL',
                'source' => 'direct',
                'code' => substr($t, 0, 200),
            ];
        }

        // === HIGH SEVERITY: check_for_rows with interpolated user-derived variable ===
        if (preg_match('/check_for_rows\s*\(\s*"/', $line) && !preg_match('/\?\s*"/', $line)) {
            // Has variable interpolation but no placeholder
            if (preg_match('/\$\w+/', $line) && !preg_match('/mysql_prefix|GLOBALS/', $line)) {
                // Check if the interpolated variable is from user input
                if (preg_match_all('/\$(\w+)/', $line, $vars)) {
                    foreach ($vars[1] as $v) {
                        if ($v === 'GLOBALS' || $v === 'this') continue;
                        if (isset($user_vars[$v]) && !$user_vars[$v]['sanitized']) {
                            $findings[] = [
                                'file' => $path,
                                'line' => $ln,
                                'severity' => 'HIGH',
                                'type' => 'Unsanitized user var in check_for_rows',
                                'source' => $user_vars[$v]['source'],
                                'code' => substr($t, 0, 200),
                            ];
                        }
                    }
                }
            }
        }

        // === MEDIUM SEVERITY: ORDER BY with user-controlled column name ===
        if (preg_match('/ORDER\s+BY\s+[`"\']*\$(\w+)/i', $line, $m)) {
            $varname = $m[1];
            if (isset($user_vars[$varname])) {
                $findings[] = [
                    'file' => $path,
                    'line' => $ln,
                    'severity' => 'MEDIUM',
                    'type' => 'ORDER BY with user-controlled column',
                    'source' => $user_vars[$varname]['source'],
                    'code' => substr($t, 0, 200),
                ];
            }
        }

        // === MEDIUM SEVERITY: LIMIT with user-controlled offset (unsanitized) ===
        if (preg_match('/LIMIT\s+\$(\w+)/i', $line, $m)) {
            $varname = $m[1];
            if ($varname === '_GET' || $varname === '_POST' || $varname === '_REQUEST') {
                $findings[] = [
                    'file' => $path,
                    'line' => $ln,
                    'severity' => 'HIGH',
                    'type' => 'LIMIT with direct user input',
                    'source' => '$_' . $varname,
                    'code' => substr($t, 0, 200),
                ];
            } elseif (isset($user_vars[$varname]) && !$user_vars[$varname]['sanitized']) {
                $findings[] = [
                    'file' => $path,
                    'line' => $ln,
                    'severity' => 'HIGH',
                    'type' => 'LIMIT with unsanitized user variable',
                    'source' => $user_vars[$varname]['source'],
                    'code' => substr($t, 0, 200),
                ];
            }
        }

        // === MEDIUM: WHERE clause with user-derived var, no placeholder ===
        if (preg_match('/WHERE\b/i', $line) && !preg_match('/\?/', $line)) {
            if (preg_match_all('/\$(\w+)/', $line, $vars)) {
                foreach ($vars[1] as $v) {
                    if ($v === 'GLOBALS' || $v === 'this' || $v === 'query') continue;
                    if (isset($user_vars[$v]) && !$user_vars[$v]['sanitized']) {
                        $findings[] = [
                            'file' => $path,
                            'line' => $ln,
                            'severity' => 'HIGH',
                            'type' => 'WHERE clause with unsanitized user variable',
                            'source' => $user_vars[$v]['source'],
                            'code' => substr($t, 0, 200),
                        ];
                    }
                }
            }
        }

        // === MEDIUM: db_query/db_fetch with string interpolation, no params ===
        if (preg_match('/db_(query|fetch_all|fetch_one|fetch_value)\s*\(\s*"/', $line)) {
            if (!preg_match('/\?/', $line) && preg_match('/\$\w+/', $line)) {
                // Check if variables are non-trivial (not just mysql_prefix)
                if (!preg_match('/^\s*\$(result|query)\s*=\s*db_/', $line)) {
                    $filtered = preg_replace('/GLOBALS\[.mysql_prefix.\]/', '', $line);
                    if (preg_match('/\$(\w+)/', $filtered, $vm)) {
                        $vn = $vm[1];
                        if ($vn !== 'GLOBALS' && $vn !== 'result' && $vn !== 'query') {
                            $sev = (isset($user_vars[$vn]) && !$user_vars[$vn]['sanitized']) ? 'HIGH' : 'LOW';
                            $src = isset($user_vars[$vn]) ? $user_vars[$vn]['source'] : 'internal variable';
                            $findings[] = [
                                'file' => $path,
                                'line' => $ln,
                                'severity' => $sev,
                                'type' => 'db_* call with string interpolation (no placeholders)',
                                'source' => $src,
                                'code' => substr($t, 0, 200),
                            ];
                        }
                    }
                }
            }
        }

        // === MEDIUM: remotes.inc.php patterns with WHERE/SET containing variables ===
        if (preg_match('/SET\s+`(lat|lng)`\s*=\s*\'\$/', $line) ||
            preg_match('/callsign\s*=\s*\'\$/', $line) ||
            preg_match('/LIKE\s+\'%\$/', $line)) {
            $findings[] = [
                'file' => $path,
                'line' => $ln,
                'severity' => 'MEDIUM',
                'type' => 'Variable interpolation in SQL SET/WHERE/LIKE',
                'source' => 'derived variable',
                'code' => substr($t, 0, 200),
            ];
        }
    }
}

scan_directory($base, $skip_dirs, $skip_files, $findings, $file_count);

// Deduplicate by file:line
$seen = [];
$unique = [];
foreach ($findings as $f) {
    $key = $f['file'] . ':' . $f['line'];
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[] = $f;
    }
}

// Sort by severity
$severity_order = ['HIGH' => 0, 'MEDIUM' => 1, 'LOW' => 2];
usort($unique, function($a, $b) use ($severity_order) {
    $sa = $severity_order[$a['severity']] ?? 3;
    $sb = $severity_order[$b['severity']] ?? 3;
    if ($sa !== $sb) return $sa - $sb;
    return strcmp($a['file'], $b['file']);
});

echo "=== SQL Injection Audit Report ===\n";
echo "Files scanned: {$file_count}\n";
echo "Total findings: " . count($unique) . "\n";

$by_sev = ['HIGH' => 0, 'MEDIUM' => 0, 'LOW' => 0];
foreach ($unique as $f) $by_sev[$f['severity']]++;
echo "HIGH: {$by_sev['HIGH']}, MEDIUM: {$by_sev['MEDIUM']}, LOW: {$by_sev['LOW']}\n\n";

foreach ($unique as $i => $f) {
    $rel = str_replace($base . DIRECTORY_SEPARATOR, '', $f['file']);
    echo "[{$f['severity']}] {$rel}:{$f['line']}\n";
    echo "  Type: {$f['type']}\n";
    echo "  Source: {$f['source']}\n";
    echo "  Code: {$f['code']}\n\n";
}
