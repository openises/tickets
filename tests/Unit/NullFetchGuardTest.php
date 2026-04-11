<?php
/**
 * Static Analysis: Detect Unsafe Database Fetch Patterns
 *
 * Scans all PHP files for patterns where database fetch results
 * (fetch_assoc, fetch_array) are accessed without null checks.
 * In PHP 8, accessing an array key on null throws:
 *   "Trying to access array offset on value of type null"
 *
 * SAFE patterns (these are OK):
 *   while ($row = $result->fetch_assoc()) { ... }     // loop checks null
 *   if ($result->num_rows > 0) { $row = ...; }        // num_rows guard
 *   if ($row) { ... $row['field'] ... }                // explicit null check
 *   $row = $result ? ... : null; if (!$row) return;   // null coalescing
 *
 * UNSAFE pattern (this test catches):
 *   $row = $result->fetch_assoc();
 *   $value = $row['field'];    // CRASH if 0 rows returned
 *
 * This test reports all instances and tracks the count over time.
 * The goal is to reduce the count to zero.
 */

use PHPUnit\Framework\TestCase;

class NullFetchGuardTest extends TestCase
{
    /**
     * Known unsafe fetch count — update this as fixes are applied.
     * When you fix instances, lower this number. When this reaches 0,
     * change the assertion to assertEquals(0, ...) to prevent regressions.
     *
     * Tracking: started at ~300, goal is 0.
     */
    private const MAX_ALLOWED_UNSAFE_FETCHES = 0;

    /**
     * Files that are known-safe (already audited and guarded).
     * Add files here as they are fixed to prevent regression.
     */
    private const AUDITED_SAFE_FILES = [
        'incs/db.inc.php',      // DB layer itself
        'incs/security.inc.php', // No fetches
        'incs/compat.inc.php',  // No fetches
        'tests/',               // Test files excluded
        'tools/',               // Fix tools contain patterns as strings
        'vendor/',
        'node_modules/',
        'lib/',
        'rss/',                 // RSS feed (low priority)
        'install.php',          // Installer has its own error handling
    ];

    /**
     * Critical files that MUST have zero unsafe fetches.
     * If any of these fail, the test fails regardless of the global count.
     */
    private const CRITICAL_FILES = [
        'board.php',
        'close_in.php',
        'action.php',
        'action_w.php',
    ];

    /**
     * Scan for the unsafe fetch pattern across the codebase.
     *
     * Pattern detected:
     * 1. Line A: $row = ...->fetch_assoc() or $row = stripslashes_deep(...->fetch_assoc())
     * 2. NOT inside a while() loop
     * 3. NOT followed by if (!$row) / if ($row) / if ($result->num_rows) guard
     * 4. Line B (within 5 lines): $row['something'] access
     */
    public function testNoUnsafeFetchPatternsInCriticalFiles(): void
    {
        $root = realpath(__DIR__ . '/../..');
        $findings = [];

        foreach (self::CRITICAL_FILES as $relPath) {
            $fullPath = $root . '/' . $relPath;
            if (!file_exists($fullPath)) {
                continue;
            }

            $fileFindings = $this->scanFile($fullPath);
            foreach ($fileFindings as $f) {
                $findings[] = $relPath . ':' . $f['line'] . ' — ' . trim($f['code']);
            }
        }

        $this->assertEmpty(
            $findings,
            "Critical files have unsafe fetch patterns (PHP 8 null array access risk):\n" .
            implode("\n", $findings)
        );
    }

    /**
     * Count total unsafe fetches across all files.
     * Fails if count exceeds the allowed maximum (decreasing over time).
     */
    public function testUnsafeFetchCountDecreasing(): void
    {
        $root = realpath(__DIR__ . '/../..');
        $totalUnsafe = 0;
        $fileDetails = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relPath = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relPath = str_replace('\\', '/', $relPath);

            // Skip excluded paths
            $skip = false;
            foreach (self::AUDITED_SAFE_FILES as $excluded) {
                if (strpos($relPath, $excluded) !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            $findings = $this->scanFile($file->getPathname());
            if (!empty($findings)) {
                $count = count($findings);
                $totalUnsafe += $count;
                $fileDetails[] = "  {$relPath}: {$count} unsafe fetch(es)";
            }
        }

        $this->assertLessThanOrEqual(
            self::MAX_ALLOWED_UNSAFE_FETCHES,
            $totalUnsafe,
            "Unsafe fetch count ({$totalUnsafe}) exceeds maximum (" .
            self::MAX_ALLOWED_UNSAFE_FETCHES . ").\n" .
            "Files with unsafe fetches:\n" . implode("\n", array_slice($fileDetails, 0, 30))
        );
    }

    /**
     * Scan a single file for unsafe fetch patterns.
     *
     * @param string $filePath
     * @return array  [{line => int, code => string}, ...]
     */
    private function scanFile(string $filePath): array
    {
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        $lines = explode("\n", $content);
        $findings = [];
        $lineCount = count($lines);

        for ($i = 0; $i < $lineCount; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            // Skip lines containing while() loops — those are safe
            // (includes same-line patterns like: $result = db_query(...); while ($row = ...)
            if (preg_match('/\bwhile\s*\(/', $line)) {
                continue;
            }

            // Detect: $var = ...->fetch_assoc() or $var = stripslashes_deep(...->fetch_assoc())
            // but NOT inside a while()
            if (!preg_match('/\$(\w+)\s*=\s*(?:stripslashes_deep\s*\()?\s*\$\w+->fetch_(?:assoc|array)\s*\(/', $line)) {
                continue;
            }

            // Extract the variable name
            preg_match('/\$(\w+)\s*=/', $line, $varMatch);
            $varName = $varMatch[1] ?? '';
            if (!$varName) {
                continue;
            }

            // Check if there's a guard within the next 3 lines
            $hasGuard = false;
            for ($j = $i + 1; $j <= min($i + 3, $lineCount - 1); $j++) {
                $nextLine = trim($lines[$j]);
                // Guards: if (!$var), if ($var), if ($result->num_rows), if(!$var), $var ?? default
                if (preg_match('/if\s*\(\s*!?\s*\$' . preg_quote($varName) . '\b/', $nextLine) ||
                    preg_match('/if\s*\(\s*\$\w+->num_rows/', $nextLine) ||
                    preg_match('/if\s*\(\s*!\s*\$' . preg_quote($varName) . '\s*\)/', $nextLine) ||
                    preg_match('/\?\?/', $nextLine) ||
                    preg_match('/return\s+\(\s*\$\w+->num_rows/', $nextLine) ||
                    strpos($nextLine, 'if (!$' . $varName . ')') !== false ||
                    strpos($nextLine, 'if($' . $varName . ')') !== false ||
                    strpos($nextLine, 'if (' . '$' . $varName . ')') !== false) {
                    $hasGuard = true;
                    break;
                }
            }

            // Check preceding line for guard (e.g., if (num_rows > 0) { $row = fetch... })
            if ($i > 0) {
                $prevLine = trim($lines[$i - 1]);
                if (preg_match('/if\s*\(\s*\(?\s*\$\w+->num_rows\s*\)?\s*[>!=]/', $prevLine) ||
                    preg_match('/if\s*\(\s*\$\w+\s*&&/', $prevLine)) {
                    $hasGuard = true;
                }
            }

            if (!$hasGuard) {
                $findings[] = [
                    'line' => $i + 1,
                    'code' => $trimmed,
                ];
            }
        }

        return $findings;
    }
}
