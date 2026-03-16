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
     * Verify no mysql_query() calls remain outside the shim files.
     *
     * All database queries should use db_query() or db_fetch_one()/db_fetch_all()
     * from incs/db.inc.php instead of the deprecated mysql_query() function.
     */
    public function testNoMysqlQueryCalls(): void
    {
        // Known files that still use mysql_query — remove from list as migrated
        $knownMysqlFiles = [
            'ajax/del_message.php',
            'ajax/del_messages.php',
            'ajax/del_sel_messages.php',
            'ajax/delfile.php',
            'ajax/empty_wastebasket.php',
            'ajax/get_latest_messages.php',
            'ajax/get_message_totals.php',
            'ajax/msg_status.php',
            'action_w.php',
            'areas_sc.php',
            'delete_module.php',
            'hints_config.php',
            'instam.php',
            'persist.php',
            'persist2.php',
            'persist3.php',
            'units.php',
            'units_nm.php',
            'warn_locations.php',
        ];

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
            // Skip the shim files themselves
            if (strpos($path, 'mysql2i') !== false) continue;

            $content = file_get_contents($path);
            if (preg_match('/\bmysql_query\s*\(/', $content)) {
                $relative = str_replace([$this->baseDir . DIRECTORY_SEPARATOR, $this->baseDir . '/'], '', $path);
                $relative = str_replace('\\', '/', $relative);
                $foundFiles[] = $relative;
            }
        }

        $normalizedKnown = array_map(function($f) { return str_replace('\\', '/', $f); }, $knownMysqlFiles);
        $newFiles = array_diff($foundFiles, $normalizedKnown);

        $this->assertEmpty(
            $newFiles,
            "New files using mysql_query() found — use db_query() instead:\n" . implode("\n", $newFiles)
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
