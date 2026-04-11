<?php
/**
 * Fix Unsafe Database Fetch Patterns for PHP 8 Compatibility
 *
 * Scans all PHP files and adds null guards after single-row fetch operations.
 * Transforms:
 *   $row = $result->fetch_assoc();
 *   $row = $result->fetch_array();
 *   $row = stripslashes_deep($result->fetch_assoc());
 *   $row = stripslashes_deep($result->fetch_array());
 *
 * Into:
 *   $row = $result ? $result->fetch_assoc() : null;
 *   $row = $result ? stripslashes_deep($result->fetch_assoc()) : null;
 *
 * Skips patterns that are already safe:
 *   - Inside while() loops (null terminates the loop)
 *   - Already has $result ? ternary guard
 *   - Already followed by if (!$row) guard
 *
 * Usage:
 *   php tools/fix_null_fetch.php           # dry-run (preview)
 *   php tools/fix_null_fetch.php --execute # apply fixes
 */

$root = dirname(__DIR__);
$execute = in_array('--execute', $argv ?? []);

echo $execute ? "=== Applying null-fetch fixes ===\n\n" : "=== Dry-run: null-fetch fixes (use --execute to apply) ===\n\n";

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

    // Skip excluded directories
    $skip = false;
    foreach ($excludeDirs as $dir) {
        if (strpos($relPath, $dir . '/') === 0 || $relPath === $dir) {
            $skip = true;
            break;
        }
    }
    if ($skip) continue;

    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $modified = false;
    $fileFixCount = 0;

    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        $trimmed = trim($line);

        // Skip if inside a while() — these are loop-safe
        if (preg_match('/^\s*while\s*\(/', $line)) continue;

        // Skip if already has ternary guard ($result ? ... : null)
        if (strpos($line, '? $') !== false && strpos($line, ': null') !== false) continue;
        if (strpos($line, '?$') !== false) continue;

        // Pattern A: $var = stripslashes_deep($result->fetch_assoc());
        if (preg_match('/(\$\w+)\s*=\s*stripslashes_deep\s*\(\s*(\$\w+)->fetch_(assoc|array)\s*\(\s*\)\s*\)\s*;/', $line, $m)) {
            $varName = $m[1];
            $resultVar = $m[2];
            $fetchMethod = $m[3];

            // Check if next line already has a guard
            $nextLine = isset($lines[$i + 1]) ? trim($lines[$i + 1]) : '';
            if (preg_match('/if\s*\(\s*!?\s*\\' . preg_quote($varName) . '\b/', $nextLine)) continue;

            // Replace just the fetch portion of the line (preserves anything before it on the same line)
            $lines[$i] = preg_replace(
                '/' . preg_quote($varName, '/') . '\s*=\s*stripslashes_deep\s*\(\s*' . preg_quote($resultVar, '/') . '->fetch_' . $fetchMethod . '\s*\(\s*\)\s*\)\s*;/',
                $varName . ' = ' . $resultVar . ' ? stripslashes_deep(' . $resultVar . '->fetch_' . $fetchMethod . '()) : null;',
                $lines[$i]
            );
            $modified = true;
            $fileFixCount++;
            continue;
        }

        // Pattern B: $var = $result->fetch_assoc(); or $var = $result->fetch_array();
        if (preg_match('/(\$\w+)\s*=\s*(\$\w+)->fetch_(assoc|array)\s*\(\s*\)\s*;/', $line, $m)) {
            $varName = $m[1];
            $resultVar = $m[2];
            $fetchMethod = $m[3];

            // Check if next line already has a guard
            $nextLine = isset($lines[$i + 1]) ? trim($lines[$i + 1]) : '';
            if (preg_match('/if\s*\(\s*!?\s*\\' . preg_quote($varName) . '\b/', $nextLine)) continue;

            // Replace just the fetch portion
            $lines[$i] = preg_replace(
                '/' . preg_quote($varName, '/') . '\s*=\s*' . preg_quote($resultVar, '/') . '->fetch_' . $fetchMethod . '\s*\(\s*\)\s*;/',
                $varName . ' = ' . $resultVar . ' ? ' . $resultVar . '->fetch_' . $fetchMethod . '() : null;',
                $lines[$i]
            );
            $modified = true;
            $fileFixCount++;
            continue;
        }
    }

    if ($modified) {
        if ($execute) {
            file_put_contents($path, implode("\n", $lines));
        }
        echo ($execute ? "[FIXED] " : "[WOULD FIX] ") . "{$relPath} ({$fileFixCount} fixes)\n";
        $totalFixed += $fileFixCount;
        $filesFixed++;
    }
}

echo "\n" . ($execute ? "Applied" : "Would apply") . ": {$totalFixed} fixes across {$filesFixed} files\n";
