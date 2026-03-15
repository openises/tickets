<?php
/**
 * Static analysis tests verifying SQL injection fixes.
 *
 * These tests scan PHP source files to confirm that legacy mysql_query()
 * calls have been replaced with db_query() prepared statements.
 *
 * @since v3.44.0
 */

use PHPUnit\Framework\TestCase;

class SqlInjectionFixesTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = realpath(__DIR__ . '/../../');
    }

    /**
     * Verify no active mysql_query() calls remain in socket server files.
     */
    public function testSocketServerUsesDbQuery(): void
    {
        $this->assertFileDoesNotContainLegacyMysql('socketserver/server.php');
    }

    /**
     * Verify no active mysql_query() calls remain in socket server backup.
     */
    public function testSocketServerBackupUsesDbQuery(): void
    {
        $this->assertFileDoesNotContainLegacyMysql('socketserver/server_BU.php');
    }

    /**
     * Verify no mysql_prepare/mysql_stmt calls remain in mobile login.
     */
    public function testMobileLoginUsesDbQuery(): void
    {
        $file = $this->baseDir . '/rm/incs/mobile_login.inc.php';
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertStringNotContainsString(
            'mysql_prepare(',
            $content,
            'mobile_login.inc.php should not use mysql_prepare()'
        );
        $this->assertStringNotContainsString(
            'mysql_stmt_',
            $content,
            'mobile_login.inc.php should not use mysql_stmt_* functions'
        );
    }

    /**
     * Verify no active mysql_query() calls remain in db_loader.
     */
    public function testDbLoaderUsesDbQueryOrMysqli(): void
    {
        $file = $this->baseDir . '/db_loader.php';
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        // Should not contain mysql_query, mysql_fetch_assoc, mysql_connect, etc.
        $this->assertDoesNotMatchRegularExpression(
            '/\bmysql_query\s*\(/',
            $content,
            'db_loader.php should not use mysql_query()'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\bmysql_connect\s*\(/',
            $content,
            'db_loader.php should not use mysql_connect()'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\bmysql_fetch_assoc\s*\(/',
            $content,
            'db_loader.php should not use mysql_fetch_assoc()'
        );
    }

    /**
     * Verify no active mysql_query() calls remain in import tool.
     */
    public function testImportToolUsesDbQueryOrMysqli(): void
    {
        $file = $this->baseDir . '/ticketsmdb_import.php';
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertDoesNotMatchRegularExpression(
            '/\bmysql_query\s*\(/',
            $content,
            'ticketsmdb_import.php should not use mysql_query()'
        );
    }

    /**
     * Verify mobile login uses verify_password() instead of inline md5 checks.
     */
    public function testMobileLoginUsesVerifyPassword(): void
    {
        $file = $this->baseDir . '/rm/incs/mobile_login.inc.php';
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'verify_password(',
            $content,
            'mobile_login.inc.php should use verify_password() for authentication'
        );
    }

    /**
     * Helper: assert that a file does not contain legacy mysql_* function calls.
     */
    private function assertFileDoesNotContainLegacyMysql(string $relativePath): void
    {
        $file = $this->baseDir . '/' . $relativePath;
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $legacyFunctions = [
            'mysql_query',
            'mysql_fetch_assoc',
            'mysql_num_rows',
            'mysql_connect',
            'mysql_select_db',
            'mysql_close',
        ];

        foreach ($legacyFunctions as $func) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b' . preg_quote($func, '/') . '\s*\(/',
                $content,
                "{$relativePath} should not use {$func}()"
            );
        }
    }
}
