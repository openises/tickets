<?php
/**
 * Unit tests for the database abstraction layer (incs/db.inc.php)
 *
 * These tests verify the non-connection parts of the db layer.
 * Tests requiring an actual database connection are marked with @group integration
 * and will be skipped unless a test database is configured.
 */

use PHPUnit\Framework\TestCase;

class DatabaseLayerTest extends TestCase
{
    protected function setUp(): void
    {
        // Load the db layer if not already loaded
        if (!function_exists('db_build_types')) {
            require_once __DIR__ . '/../../incs/db.inc.php';
        }
    }

    /**
     * Test type string builder
     */
    public function testBuildTypesWithStrings(): void
    {
        $this->assertEquals('ss', db_build_types(['hello', 'world']));
    }

    public function testBuildTypesWithIntegers(): void
    {
        $this->assertEquals('ii', db_build_types([1, 2]));
    }

    public function testBuildTypesWithMixed(): void
    {
        $this->assertEquals('sid', db_build_types(['hello', 42, 3.14]));
    }

    public function testBuildTypesWithNull(): void
    {
        $this->assertEquals('s', db_build_types([null]));
    }

    public function testBuildTypesEmpty(): void
    {
        $this->assertEquals('', db_build_types([]));
    }

    /**
     * Test table name sanitization
     */
    public function testSanitizeTableName(): void
    {
        $this->assertEquals('my_table', db_sanitize_table_name('my_table'));
    }

    public function testSanitizeTableNameStripsInjection(): void
    {
        $this->assertEquals('mytable', db_sanitize_table_name('my;table'));
    }

    public function testSanitizeTableNameAllowsPrefix(): void
    {
        $this->assertEquals('prefix_table', db_sanitize_table_name('prefix_table'));
    }

    public function testSanitizeTableNameRejectsDots(): void
    {
        $this->assertEquals('tablename', db_sanitize_table_name('table.name'));
    }
}
