<?php
/**
 * Tests for installer safety — catches common bugs before they reach users.
 *
 * These tests verify that install.php handles missing/optional POST fields
 * gracefully, has proper security on download endpoints, and doesn't
 * reference undefined array keys.
 */
class InstallerSafetyTest extends \PHPUnit\Framework\TestCase
{
    private $installPhp;

    protected function setUp(): void
    {
        $this->installPhp = file_get_contents(__DIR__ . '/../../install.php');
        $this->assertNotEmpty($this->installPhp, 'install.php must be readable');
    }

    /**
     * Every $_POST['admin_*'] access must use null-coalescing (??) since
     * admin fields are only present during clean install, not upgrade.
     */
    public function testAdminFieldsUseNullCoalescing()
    {
        // Match $_POST['admin_user'], $_POST['admin_pass'], $_POST['admin_name']
        // that do NOT have ?? after them
        preg_match_all('/\$_POST\[[\'"](admin_\w+)[\'"]\](?!\s*\?\?)/', $this->installPhp, $matches);

        $this->assertEmpty(
            $matches[0],
            "Found bare \$_POST['admin_*'] without ?? operator (will cause 'Undefined array key' on upgrade):\n" .
            implode("\n", $matches[0])
        );
    }

    /**
     * The CSV download endpoint must restrict table names to _unmigrated suffix.
     */
    public function testCsvDownloadRestrictedToUnmigratedTables()
    {
        $this->assertStringContainsString(
            '_unmigrated',
            $this->installPhp,
            'CSV download handler must check for _unmigrated suffix'
        );

        // Must have a check that rejects non-unmigrated tables
        $this->assertMatchesRegularExpression(
            '/unmigrated.*403|403.*unmigrated/s',
            $this->installPhp,
            'CSV download must return 403 for non-unmigrated tables'
        );
    }

    /**
     * Table names from GET params must be validated with a character whitelist.
     */
    public function testCsvDownloadValidatesTableName()
    {
        $this->assertMatchesRegularExpression(
            '/preg_match.*A-Za-z0-9_.*download_unmigrated|download_unmigrated.*preg_match.*A-Za-z0-9_/s',
            $this->installPhp,
            'CSV download must validate table name with regex whitelist'
        );
    }

    /**
     * No duplicate function definitions in the JS output.
     */
    public function testNoDuplicateJsFunctionDefinitions()
    {
        preg_match_all('/function\s+(escapeHtml|renderLogLine)\s*\(/', $this->installPhp, $matches);

        $counts = array_count_values($matches[1]);
        foreach ($counts as $fn => $count) {
            $this->assertEquals(
                1,
                $count,
                "JS function '{$fn}' is defined {$count} times in install.php (should be exactly 1)"
            );
        }
    }

    /**
     * The installer must not use innerHTML for log lines that could contain
     * user-controlled content.
     */
    public function testNoRawInnerHtmlForLogLines()
    {
        // Should not have: target.innerHTML = line (without escaping)
        $this->assertDoesNotMatchRegularExpression(
            '/target\.innerHTML\s*=\s*line\s*;/',
            $this->installPhp,
            'Log lines must not be rendered with raw innerHTML (XSS risk)'
        );
    }

    /**
     * sanitize_coordinate should exist in security.inc.php (not duplicated in other files).
     */
    public function testSanitizeCoordinateNotDuplicatedInPortalFiles()
    {
        $files = [
            __DIR__ . '/../../edit.php',
            __DIR__ . '/../../portal/ajax/insert_request.php',
            __DIR__ . '/../../portal/request.php',
        ];

        foreach ($files as $file) {
            if (!file_exists($file)) continue;
            $content = file_get_contents($file);
            $this->assertDoesNotMatchRegularExpression(
                '/function\s+sanitize_coordinate\s*\(/',
                $content,
                basename($file) . ' should not define sanitize_coordinate() — it belongs in security.inc.php'
            );
        }
    }

    /**
     * security.inc.php must contain the canonical sanitize_coordinate definition.
     */
    public function testSanitizeCoordinateExistsInSecurityInc()
    {
        $security = file_get_contents(__DIR__ . '/../../incs/security.inc.php');
        $this->assertMatchesRegularExpression(
            '/function\s+sanitize_coordinate\s*\(/',
            $security,
            'sanitize_coordinate() must be defined in incs/security.inc.php'
        );
    }
}
