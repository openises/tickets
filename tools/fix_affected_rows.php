<?php
/**
 * Fix db()->affected_rows used after SELECT queries
 *
 * In the legacy codebase, db()->affected_rows was used to check if a SELECT
 * returned rows. This worked with mysql_query() but fails with prepared
 * statements (db_query with parameters) because mysqli stores affected_rows
 * per-statement, not on the connection object.
 *
 * This script identifies and fixes patterns where affected_rows is used
 * to check SELECT results. The fix uses $result->num_rows instead.
 *
 * Usage:
 *   php tools/fix_affected_rows.php           # dry-run
 *   php tools/fix_affected_rows.php --execute # apply fixes
 */

$root = dirname(__DIR__);
$execute = in_array('--execute', $argv ?? []);

echo $execute ? "=== Applying affected_rows fixes ===\n\n" : "=== Dry-run: affected_rows fixes ===\n\n";

$excludeDirs = ['vendor', 'node_modules', 'lib', 'tools', 'tests'];
$totalFixed = 0;
$filesFixed = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if ($fileInfo->getExtension() !== 'php') continue;

    $path = $fileInfo->getPathname();
    $relPath = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
    $relPath = str_replace('\\', '/', $relPath);

    $skip = false;
    foreach ($excludeDirs as $dir) {
        if (strpos($relPath, $dir . '/') === 0) { $skip = true; break; }
    }
    if ($skip) continue;

    // Skip the db.inc.php file itself (it defines db_affected_rows)
    if (strpos($relPath, 'db.inc.php') !== false) continue;

    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $modified = false;
    $fileFixCount = 0;

    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];

        // Look for db()->affected_rows used after SELECT queries
        // We need to find the SELECT query variable and replace db()->affected_rows with $result->num_rows

        // Pattern: db()->affected_rows immediately after a SELECT query stored in a variable
        // We look backward up to 10 lines for a db_query("SELECT...") and get the result variable

        if (strpos($line, 'db()->affected_rows') === false) continue;

        // Find the most recent db_query result variable
        $resultVar = null;
        for ($j = $i - 1; $j >= max(0, $i - 15); $j--) {
            if (preg_match('/(\$\w+)\s*=\s*db_query\s*\(/', $lines[$j], $m)) {
                $resultVar = $m[1];
                break;
            }
        }

        if (!$resultVar) continue;

        // Check if the query is a SELECT (look at the query string)
        $isSelect = false;
        for ($j = $i - 1; $j >= max(0, $i - 15); $j--) {
            if (preg_match('/SELECT\s/i', $lines[$j])) {
                $isSelect = true;
                break;
            }
            if (preg_match('/INSERT\s|UPDATE\s|DELETE\s/i', $lines[$j])) {
                break; // It's a DML query, affected_rows is correct
            }
        }

        if (!$isSelect) continue;

        // Replace db()->affected_rows with $resultVar->num_rows
        $newLine = str_replace('db()->affected_rows', $resultVar . '->num_rows', $line);
        if ($newLine !== $line) {
            $lines[$i] = $newLine;
            $modified = true;
            $fileFixCount++;
        }
    }

    if ($modified) {
        if ($execute) {
            file_put_contents($path, implode("\n", $lines));
        }
        echo ($execute ? "[FIXED] " : "[WOULD FIX] ") . "{$relPath} ({$fileFixCount})\n";
        $totalFixed += $fileFixCount;
        $filesFixed++;
    }
}

echo "\n" . ($execute ? "Applied" : "Would apply") . ": {$totalFixed} fixes across {$filesFixed} files\n";
