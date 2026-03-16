<?php
/**
 * Code hygiene tests for the Tickets CAD codebase.
 *
 * Static analysis tests that enforce code quality standards:
 * - Debug flags are disabled in production code
 * - Dead test files stay removed
 * - extract() usage is tracked and doesn't spread to new files
 * - Critical functions have PHPDoc documentation
 * - Main entry point files have file-level doc headers
 *
 * @since v3.44.0
 */

use PHPUnit\Framework\TestCase;

class CodeHygieneTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = realpath(__DIR__ . '/../../');
    }

    /**
     * Verify the debug flag $istest is disabled in tables.php.
     *
     * tables.php has two $istest assignments. Both must be FALSE in production.
     * When TRUE, raw POST data is dumped via dump($_POST) on every page load,
     * leaking form data into the HTML output visible to admin users.
     */
    public function testDebugFlagDisabledInTables(): void
    {
        $file = $this->baseDir . '/tables.php';
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        // Should NOT contain $istest = TRUE (with any whitespace variation)
        $this->assertDoesNotMatchRegularExpression(
            '/\$istest\s*=\s*TRUE/',
            $content,
            'tables.php must not have $istest = TRUE — debug output must be disabled in production'
        );
    }

    /**
     * Verify dead test/debug scripts have not been re-added to the root directory.
     *
     * These standalone scripts were removed in Phase 2 cleanup. They are not
     * referenced by any other file and should not reappear.
     */
    public function testNoDeadTestFilesInRoot(): void
    {
        $deadFiles = [
            'test_chunk.php',
            'test_eparam.php',
            'test_glat.php',
            'test_instam.php',
            'test_ssl_locations.php',
            'test_xastir.php',
        ];

        foreach ($deadFiles as $file) {
            $this->assertFileDoesNotExist(
                $this->baseDir . '/' . $file,
                "Dead test script '{$file}' should not exist in the root directory"
            );
        }
    }

    /**
     * Track files that use extract($_POST/GET/REQUEST) and fail if new ones appear.
     *
     * extract() from superglobals is a security anti-pattern that:
     * - Creates variables from user input silently
     * - Makes code harder to follow (variable origins unclear)
     * - Enables variable injection attacks
     *
     * The known list below tracks files that still have extract(). As each file
     * is fixed, remove it from this list. The test fails if a NEW file adds extract().
     */
    public function testExtractSuperglobalsDoNotSpread(): void
    {
        // Known files that still use extract() — remove from this list as they are fixed
        $knownExtractFiles = [
            // All superglobal extract() calls have been replaced with explicit assignments
        ];

        // Scan all PHP files for extract() usage
        $foundFiles = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;
            $path = $file->getPathname();

            // Skip vendor and tests directories
            if (strpos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR) !== false) continue;

            $content = file_get_contents($path);
            if (preg_match('/extract\(\$_(POST|GET|REQUEST)/', $content)) {
                // Normalize path separators and make relative
                $relative = str_replace(
                    [$this->baseDir . DIRECTORY_SEPARATOR, $this->baseDir . '/'],
                    '',
                    $path
                );
                $relative = str_replace('\\', '/', $relative);
                $foundFiles[] = $relative;
            }
        }

        // Check for NEW files that aren't in the known list
        $normalizedKnown = array_map(function($f) { return str_replace('\\', '/', $f); }, $knownExtractFiles);
        $newFiles = array_diff($foundFiles, $normalizedKnown);

        $this->assertEmpty(
            $newFiles,
            "New files using extract() found — add explicit variable assignments instead:\n" .
            implode("\n", $newFiles)
        );
    }

    /**
     * Verify that critical functions in functions.inc.php have PHPDoc blocks.
     *
     * These are the most-used functions in the codebase. Each should have
     * a /** ... * / doc block immediately before its function declaration.
     */
    public function testCriticalFunctionsHavePhpdoc(): void
    {
        $file = $this->baseDir . '/incs/functions.inc.php';
        $this->assertFileExists($file);
        $content = file_get_contents($file);
        $lines = explode("\n", $content);

        // Functions that must have PHPDoc (add to this list as docs are written)
        $requiredDocs = [
            'get_variable',
            'get_text',
            'is_administrator',
            'is_super',
            'do_log',
            'stripslashes_deep',
            'format_date',
            'mysql_format_date',
            'check_for_rows',
            'get_severity',
            'get_status',
            'get_owner',
        ];

        foreach ($requiredDocs as $funcName) {
            // Find the function declaration line
            $funcLine = null;
            foreach ($lines as $i => $line) {
                if (preg_match('/^\s*function\s+' . preg_quote($funcName, '/') . '\s*\(/', $line)) {
                    $funcLine = $i;
                    break;
                }
            }

            if ($funcLine === null) {
                // Function not found — skip (might be in a different file)
                continue;
            }

            // Check that a PHPDoc block exists in the 20 lines before the function
            $hasDoc = false;
            $searchStart = max(0, $funcLine - 20);
            for ($j = $searchStart; $j < $funcLine; $j++) {
                if (strpos($lines[$j], '/**') !== false) {
                    $hasDoc = true;
                    break;
                }
            }

            $this->assertTrue(
                $hasDoc,
                "Function {$funcName}() in functions.inc.php is missing PHPDoc documentation (near line " . ($funcLine + 1) . ")"
            );
        }
    }

    /**
     * Verify no raw addslashes() calls on DB row values remain.
     *
     * All addslashes($row[...]) patterns should use safe_addslashes() instead,
     * which handles null values without PHP 8.2 deprecation warnings.
     */
    public function testNoRawAddslashesOnDbRows(): void
    {
        $foundFiles = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;
            $path = $file->getPathname();
            if (strpos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR) !== false) continue;

            $content = file_get_contents($path);
            // Match addslashes($row[ but not safe_addslashes
            if (preg_match('/[^_]addslashes\(\$row\[/', $content)) {
                $relative = str_replace([$this->baseDir . DIRECTORY_SEPARATOR, $this->baseDir . '/'], '', $path);
                $foundFiles[] = str_replace('\\', '/', $relative);
            }
        }

        $this->assertEmpty(
            $foundFiles,
            "Raw addslashes(\$row[...]) found — use safe_addslashes() instead:\n" . implode("\n", $foundFiles)
        );
    }

    /**
     * Verify no raw htmlentities() calls on DB row values remain.
     */
    public function testNoRawHtmlentitiesOnDbRows(): void
    {
        $foundFiles = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;
            $path = $file->getPathname();
            if (strpos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR) !== false) continue;

            $content = file_get_contents($path);
            if (preg_match('/[^_]htmlentities\(\$row\[/', $content)) {
                $relative = str_replace([$this->baseDir . DIRECTORY_SEPARATOR, $this->baseDir . '/'], '', $path);
                $foundFiles[] = str_replace('\\', '/', $relative);
            }
        }

        $this->assertEmpty(
            $foundFiles,
            "Raw htmlentities(\$row[...]) found — use safe_htmlentities() instead:\n" . implode("\n", $foundFiles)
        );
    }

    /**
     * Verify no deprecated mysql_*() function calls remain in application code.
     *
     * The mysql2i shim has been removed. All database queries must use db_query()
     * or db_fetch_one()/db_fetch_all() from incs/db.inc.php.
     *
     * String references like 'mysql_query() failed' in error messages are ignored.
     * Custom functions mysql_format_date(), mysql_table_exists(), and
     * mysql_real_escape_string_deep() are not deprecated — they use mysqli internally.
     */
    public function testNoDeprecatedMysqlCalls(): void
    {
        // Deprecated mysql_* functions from the removed shim.
        // Excludes custom functions: mysql_format_date, mysql_table_exists,
        // mysql_real_escape_string_deep, mysql_field_type_compat
        $deprecated = [
            'mysql_query', 'mysql_fetch_array', 'mysql_fetch_assoc',
            'mysql_fetch_row', 'mysql_num_rows', 'mysql_insert_id',
            'mysql_affected_rows', 'mysql_connect', 'mysql_close',
            'mysql_select_db', 'mysql_free_result', 'mysql_num_fields',
            'mysql_field_name', 'mysql_field_type', 'mysql_list_dbs',
            'mysql_escape_string', 'mysql_real_escape_string',
        ];
        $pattern = '/\b(' . implode('|', $deprecated) . ')\s*\(/';

        $foundFiles = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') continue;
            $path = $file->getPathname();
            if (strpos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR) !== false) continue;
            if (strpos($path, DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR) !== false) continue;

            $content = file_get_contents($path);
            // Strip all comments before scanning for deprecated calls
            $stripped = preg_replace('#/\*.*?\*/#s', '', $content);
            $stripped = preg_replace('#//.*$#m', '', $stripped);
            $lines = explode("\n", $stripped);
            foreach ($lines as $lineNum => $line) {
                // Skip string references like 'mysql_query() failed'
                if (preg_match('/[\'"].*mysql_/', $line)) continue;
                // Skip function definitions (mysql_real_escape_string_deep, etc.)
                if (strpos($line, 'function ') !== false) continue;

                if (preg_match($pattern, $line)) {
                    $relative = str_replace([$this->baseDir . DIRECTORY_SEPARATOR, $this->baseDir . '/'], '', $path);
                    $relative = str_replace('\\', '/', $relative);
                    $foundFiles[$relative] = ($lineNum + 1);
                    break;
                }
            }
        }

        $this->assertEmpty(
            $foundFiles,
            "Deprecated mysql_*() calls found — use db_query()/mysqli instead:\n" .
            implode("\n", array_map(function($f, $l) { return "$f:$l"; }, array_keys($foundFiles), $foundFiles))
        );
    }

    /**
     * Verify main entry point files have file-level documentation headers.
     *
     * Each major page should have a /** ... * / block near the top of the file
     * describing its purpose, URL, and authentication requirements.
     */
    public function testEntryPointFilesHaveHeaders(): void
    {
        $entryPoints = [
            'index.php',
            'main.php',
            'config.php',
            'tables.php',
            'board.php',
            'add.php',
            'edit.php',
            'top.php',
        ];

        foreach ($entryPoints as $filename) {
            $file = $this->baseDir . '/' . $filename;
            if (!file_exists($file)) continue;

            // Read the first 20 lines and check for /** doc block
            $content = file_get_contents($file);
            $firstLines = implode("\n", array_slice(explode("\n", $content), 0, 20));

            $this->assertTrue(
                strpos($firstLines, '/**') !== false || strpos($firstLines, '/*') !== false,
                "Entry point '{$filename}' is missing a file-level doc header (/* or /**) in the first 20 lines"
            );
        }
    }
}
