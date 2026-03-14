<?php
/**
 * Tickets CAD Security Scanner
 *
 * Scans PHP files for known security vulnerability patterns and reports findings.
 * Run from command line: php tools/security_scan.php [--verbose] [--json]
 *
 * Categories scanned:
 *  1. SQL Injection: $_GET/$_POST directly in SQL query strings
 *  2. Deprecated escaping: addslashes() used for SQL escaping
 *  3. Legacy mysql_query(): calls through the mysql2i shim (should be db_query)
 *  4. Legacy mysql_real_escape_string(): should use prepared statements
 *  5. XSS: $_GET/$_POST echoed without htmlspecialchars/e()
 *  6. MD5 passwords: md5() used for password hashing
 *  7. unserialize(): potential PHP object injection
 */

$verbose = in_array('--verbose', $argv ?? []) || in_array('-v', $argv ?? []);
$jsonOutput = in_array('--json', $argv ?? []);

$baseDir = realpath(__DIR__ . '/..');
$scanDirs = ['ajax', 'incs', 'rm', 'portal', 'forms', 'bulk_admin', 'csv_import_scripts'];

// Collect all PHP files
$files = [];

// Root PHP files
foreach (glob($baseDir . '/*.php') as $f) {
    // Skip this scanner and vendor
    if (basename($f) === 'security_scan.php') continue;
    $files[] = $f;
}

// Subdirectory PHP files
foreach ($scanDirs as $dir) {
    $dirPath = $baseDir . '/' . $dir;
    if (!is_dir($dirPath)) continue;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

// Skip vendor and test files
$files = array_filter($files, function($f) use ($baseDir) {
    $rel = str_replace($baseDir . '/', '', str_replace($baseDir . '\\', '', $f));
    return strpos($rel, 'vendor/') !== 0
        && strpos($rel, 'tests/') !== 0
        && strpos($rel, 'tools/') !== 0;
});

$findings = [
    'sql_injection_direct' => [],      // $_GET/$_POST in SQL strings
    'addslashes_escaping' => [],       // addslashes() for SQL
    'legacy_mysql_query' => [],        // mysql_query() calls
    'legacy_mysql_escape' => [],       // mysql_real_escape_string()
    'xss_unescaped_output' => [],      // echo/print $_GET/$_POST without escaping
    'md5_password' => [],              // md5() for password hashing
    'unserialize_usage' => [],         // unserialize() calls
];

foreach ($files as $filepath) {
    $content = file_get_contents($filepath);
    if ($content === false) continue;

    $lines = explode("\n", $content);
    $relPath = str_replace($baseDir . '/', '', str_replace($baseDir . '\\', '', $filepath));

    foreach ($lines as $lineNum => $line) {
        $ln = $lineNum + 1;
        $trimmed = trim($line);

        // Skip comments
        if (strpos($trimmed, '//') === 0 || strpos($trimmed, '*') === 0 || strpos($trimmed, '#') === 0) {
            continue;
        }

        // 1. SQL Injection: $_GET or $_POST inside a query string
        // Look for patterns like: "SELECT ... $_GET[" or "WHERE ... $_POST["
        if (preg_match('/["\'].*(?:SELECT|INSERT|UPDATE|DELETE|WHERE|SET|VALUES|FROM|INTO|ORDER|GROUP|HAVING).*\$_(?:GET|POST|REQUEST)\[/i', $line)) {
            $findings['sql_injection_direct'][] = ['file' => $relPath, 'line' => $ln, 'code' => $trimmed];
        }

        // 2. addslashes() usage
        if (preg_match('/\baddslashes\s*\(/', $line)) {
            $findings['addslashes_escaping'][] = ['file' => $relPath, 'line' => $ln, 'code' => $trimmed];
        }

        // 3. Legacy mysql_query() calls (not in mysql2i files themselves)
        if (strpos($relPath, 'mysql2i') === false && preg_match('/\bmysql_query\s*\(/', $line)) {
            $findings['legacy_mysql_query'][] = ['file' => $relPath, 'line' => $ln, 'code' => $trimmed];
        }

        // 4. mysql_real_escape_string() usage (not in mysql2i files)
        if (strpos($relPath, 'mysql2i') === false && preg_match('/\bmysql_real_escape_string\s*\(/', $line)) {
            $findings['legacy_mysql_escape'][] = ['file' => $relPath, 'line' => $ln, 'code' => $trimmed];
        }

        // 5. XSS: echo/print of $_GET/$_POST without htmlspecialchars or e()
        if (preg_match('/(?:echo|print)\s+.*\$_(?:GET|POST|REQUEST)\[/', $line)) {
            // Check if it's wrapped in htmlspecialchars or e()
            if (!preg_match('/(?:htmlspecialchars|htmlentities|\be)\s*\(\s*\$_(?:GET|POST|REQUEST)\[/', $line)) {
                $findings['xss_unescaped_output'][] = ['file' => $relPath, 'line' => $ln, 'code' => $trimmed];
            }
        }

        // 6. MD5 for password hashing (look for md5 near password context)
        if (preg_match('/\bmd5\s*\(.*(?:pass|pwd|passwd)/i', $line) ||
            preg_match('/(?:pass|pwd|passwd).*\bmd5\s*\(/i', $line)) {
            $findings['md5_password'][] = ['file' => $relPath, 'line' => $ln, 'code' => $trimmed];
        }

        // 7. unserialize() usage
        if (preg_match('/\bunserialize\s*\(/', $line)) {
            $findings['unserialize_usage'][] = ['file' => $relPath, 'line' => $ln, 'code' => $trimmed];
        }
    }
}

// Output results
if ($jsonOutput) {
    $summary = [];
    foreach ($findings as $category => $items) {
        $summary[$category] = [
            'count' => count($items),
            'files' => array_values(array_unique(array_column($items, 'file'))),
        ];
        if ($verbose) {
            $summary[$category]['details'] = $items;
        }
    }
    echo json_encode($summary, JSON_PRETTY_PRINT) . "\n";
} else {
    $totalFiles = count($files);
    $totalFindings = 0;
    foreach ($findings as $items) {
        $totalFindings += count($items);
    }

    echo "=======================================================\n";
    echo "  Tickets CAD Security Scan Report\n";
    echo "  Scanned: {$totalFiles} PHP files\n";
    echo "  Total findings: {$totalFindings}\n";
    echo "=======================================================\n\n";

    $categories = [
        'sql_injection_direct' => ['CRITICAL', 'SQL Injection (direct $_GET/$_POST in queries)'],
        'addslashes_escaping'  => ['HIGH',     'Deprecated addslashes() for SQL escaping'],
        'legacy_mysql_query'   => ['MEDIUM',   'Legacy mysql_query() calls (should use db_query)'],
        'legacy_mysql_escape'  => ['MEDIUM',   'Legacy mysql_real_escape_string() calls'],
        'xss_unescaped_output' => ['HIGH',     'XSS: Unescaped $_GET/$_POST in output'],
        'md5_password'         => ['HIGH',     'MD5 password hashing'],
        'unserialize_usage'    => ['MEDIUM',   'unserialize() usage (object injection risk)'],
    ];

    foreach ($categories as $key => [$severity, $description]) {
        $count = count($findings[$key]);
        $fileCount = count(array_unique(array_column($findings[$key], 'file')));
        $icon = $count === 0 ? '[PASS]' : "[{$severity}]";
        echo sprintf("  %-10s %-55s %4d findings in %3d files\n", $icon, $description, $count, $fileCount);

        if ($verbose && $count > 0) {
            echo "  " . str_repeat('-', 75) . "\n";
            foreach ($findings[$key] as $item) {
                $code = strlen($item['code']) > 80 ? substr($item['code'], 0, 77) . '...' : $item['code'];
                echo "    {$item['file']}:{$item['line']}\n";
                echo "      {$code}\n";
            }
            echo "\n";
        }
    }

    echo "\n=======================================================\n";
    if ($totalFindings === 0) {
        echo "  ALL CLEAR - No security issues detected!\n";
    } else {
        echo "  Action required: {$totalFindings} issues need remediation.\n";
    }
    echo "=======================================================\n";
}
