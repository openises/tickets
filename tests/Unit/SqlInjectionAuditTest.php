<?php
/**
 * Static analysis tests verifying SQL injection audit fixes (2026-04-01).
 *
 * These tests scan the fixed PHP files to confirm that user input is properly
 * sanitized before being used in SQL queries.
 *
 * @since v3.44.1
 */

use PHPUnit\Framework\TestCase;

class SqlInjectionAuditTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = realpath(__DIR__ . '/../../');
    }

    /**
     * Verify fullsit_incidents.php uses sanitize_int for LIMIT offset.
     */
    public function testFullsitIncidentsLimitIsSanitized(): void
    {
        $file = $this->baseDir . '/ajax/fullsit_incidents.php';
        $content = file_get_contents($file);

        // Must NOT contain raw $_GET[offset] in LIMIT clause
        $this->assertDoesNotMatchRegularExpression(
            '/LIMIT\s+\$_GET\[offset\]/',
            $content,
            'fullsit_incidents.php should not use raw $_GET[offset] in LIMIT'
        );

        // Must contain sanitize_int for offset
        $this->assertStringContainsString(
            'sanitize_int($_GET[\'offset\']',
            $content,
            'fullsit_incidents.php should use sanitize_int() for offset'
        );

        // my_offset must also be sanitized
        $this->assertDoesNotMatchRegularExpression(
            '/\$my_offset\s*=.*\$_GET\[.my_offset.\]\s*;/',
            $content,
            'fullsit_incidents.php my_offset should be sanitized'
        );
    }

    /**
     * Verify sit_incidents.php uses sanitize_int for LIMIT offset.
     */
    public function testSitIncidentsLimitIsSanitized(): void
    {
        $file = $this->baseDir . '/ajax/sit_incidents.php';
        $content = file_get_contents($file);

        // Must NOT contain raw $_GET[offset] in LIMIT clause
        $this->assertDoesNotMatchRegularExpression(
            '/LIMIT\s+\$_GET\[offset\]/',
            $content,
            'sit_incidents.php should not use raw $_GET[offset] in LIMIT'
        );

        // Must contain sanitize_int for offset
        $this->assertStringContainsString(
            'sanitize_int($_GET[\'offset\']',
            $content,
            'sit_incidents.php should use sanitize_int() for offset'
        );
    }

    /**
     * Verify tables.php sanitizes table name, index name, sort column, and search string.
     */
    public function testTablesPhpSanitizesUserInput(): void
    {
        $file = $this->baseDir . '/tables.php';
        $content = file_get_contents($file);

        // tablename must use preg_replace to strip non-alphanumeric chars
        $this->assertMatchesRegularExpression(
            '/\$tablename\s*=.*preg_replace.*\$_POST\[.tablename.\]/',
            $content,
            'tables.php should sanitize $tablename with preg_replace'
        );

        // indexname must use preg_replace to strip non-alphanumeric chars
        $this->assertMatchesRegularExpression(
            '/\$indexname\s*=.*preg_replace.*\$_POST\[.indexname.\]/',
            $content,
            'tables.php should sanitize $indexname with preg_replace'
        );

        // sortby must use preg_replace to strip non-alphanumeric chars
        $this->assertMatchesRegularExpression(
            '/\$sortby\s*=.*preg_replace.*\$_POST\[.sortby.\]/',
            $content,
            'tables.php should sanitize $sortby with preg_replace'
        );

        // Search term must use real_escape_string
        $this->assertStringContainsString(
            'real_escape_string($ary_srch[0])',
            $content,
            'tables.php should escape search term with real_escape_string'
        );

        // Column names in search must be sanitized
        $this->assertStringContainsString(
            '$safe_col = preg_replace',
            $content,
            'tables.php should sanitize column names in search'
        );

        // Column names in UPDATE must be sanitized
        $this->assertStringContainsString(
            '$safe_col_name = preg_replace',
            $content,
            'tables.php should sanitize column names in UPDATE'
        );
    }

    /**
     * Verify portal list_requests.php uses whitelist for ORDER BY.
     */
    public function testPortalListRequestsOrderByWhitelist(): void
    {
        $file = $this->baseDir . '/portal/ajax/list_requests.php';
        $content = file_get_contents($file);

        // Must contain allowed_sorts whitelist
        $this->assertStringContainsString(
            '$allowed_sorts',
            $content,
            'portal list_requests.php should have an allowed_sorts whitelist'
        );

        // Must use in_array for validation
        $this->assertStringContainsString(
            'in_array($_GET[\'sort\']',
            $content,
            'portal list_requests.php should validate sort with in_array'
        );

        // Sort direction must be whitelisted
        $this->assertStringContainsString(
            "strtoupper(\$_GET['dir']) === 'DESC'",
            $content,
            'portal list_requests.php should whitelist sort direction'
        );
    }

    /**
     * Verify message.php sanitizes ticket_id and responder_id from POST.
     */
    public function testMessagePhpSanitizesIds(): void
    {
        $file = $this->baseDir . '/message.php';
        $content = file_get_contents($file);

        // tick_id from POST must use sanitize_int
        $this->assertStringContainsString(
            "sanitize_int(\$_POST['frm_ticket_id'])",
            $content,
            'message.php should use sanitize_int for frm_ticket_id'
        );

        // resp_id from POST must use sanitize_int
        $this->assertStringContainsString(
            "sanitize_int(\$_POST['frm_resp_id'])",
            $content,
            'message.php should use sanitize_int for frm_resp_id'
        );
    }

    /**
     * Verify reports.php uses intval() for tick_id in SQL queries.
     */
    public function testReportsPhpUsesIntvalForTickId(): void
    {
        $file = $this->baseDir . '/ajax/reports.php';
        $content = file_get_contents($file);

        // Must NOT contain raw $tick_id in WHERE clause
        $this->assertDoesNotMatchRegularExpression(
            '/WHERE\s+`ticket_id`\s*=\s*\$tick_id\b(?!\s*\))/',
            $content,
            'reports.php should not use raw $tick_id in WHERE clause'
        );

        // Must contain intval($tick_id) pattern
        $this->assertStringContainsString(
            'intval($tick_id)',
            $content,
            'reports.php should use intval($tick_id) for SQL safety'
        );
    }

    /**
     * Verify statistics.php uses intval() for tick_id in SQL queries.
     */
    public function testStatisticsPhpUsesIntvalForTickId(): void
    {
        $file = $this->baseDir . '/ajax/statistics.php';
        $content = file_get_contents($file);

        // Must NOT contain raw $tick_id in WHERE clause
        $this->assertDoesNotMatchRegularExpression(
            '/WHERE\s+`ticket_id`\s*=\s*\$tick_id\b/',
            $content,
            'statistics.php should not use raw $tick_id in WHERE clause'
        );

        // Must contain intval($tick_id) pattern
        $this->assertStringContainsString(
            'intval($tick_id)',
            $content,
            'statistics.php should use intval($tick_id) for SQL safety'
        );
    }

    /**
     * Verify remotes.inc.php sanitizes external API data before SQL use.
     */
    public function testRemotesIncSanitizesExternalData(): void
    {
        $file = $this->baseDir . '/incs/remotes.inc.php';
        $content = file_get_contents($file);

        // InstaMapper: must sanitize lat/lng with floatval and callsign with real_escape_string
        $this->assertStringContainsString(
            '$safe_lat = floatval($lat)',
            $content,
            'remotes.inc.php should sanitize InstaMapper lat with floatval'
        );
        $this->assertStringContainsString(
            '$safe_key = db()->real_escape_string($key)',
            $content,
            'remotes.inc.php should escape InstaMapper callsign'
        );

        // gTrack: must sanitize external XML data
        $this->assertStringContainsString(
            '$safe_gt_lat = floatval($lat)',
            $content,
            'remotes.inc.php should sanitize gTrack lat with floatval'
        );

        // APRS: must sanitize external API data
        $this->assertStringContainsString(
            '$safe_aprs_lat = floatval($lat)',
            $content,
            'remotes.inc.php should sanitize APRS lat with floatval'
        );

        // OpenGTS: must sanitize external JSON data
        $this->assertStringContainsString(
            '$safe_ogts_lat = floatval($lat)',
            $content,
            'remotes.inc.php should sanitize OpenGTS lat with floatval'
        );

        // FollowMee: must sanitize external API data
        $this->assertStringContainsString(
            '$safe_fm_lat = floatval($lat)',
            $content,
            'remotes.inc.php should sanitize FollowMee lat with floatval'
        );
    }

    /**
     * Verify mobile_main.php uses parameterized check_for_rows.
     */
    public function testMobileMainUsesParameterizedQuery(): void
    {
        $file = $this->baseDir . '/ajax/mobile_main.php';
        $content = file_get_contents($file);

        // Must NOT contain raw $id in check_for_rows SQL
        $this->assertDoesNotMatchRegularExpression(
            "/check_for_rows.*WHERE id='\\\$id'/",
            $content,
            'mobile_main.php should not use raw $id in check_for_rows'
        );

        // Must contain intval($id)
        $this->assertStringContainsString(
            '$id = intval($id)',
            $content,
            'mobile_main.php should use intval for $id'
        );
    }

    /**
     * Verify functions_major_nm.inc.php uses parameterized check_for_rows.
     */
    public function testFunctionsMajorNmUsesParameterizedQuery(): void
    {
        $file = $this->baseDir . '/incs/functions_major_nm.inc.php';
        $content = file_get_contents($file);

        // Must NOT contain raw $id in check_for_rows without placeholder
        $this->assertDoesNotMatchRegularExpression(
            "/check_for_rows.*WHERE id='\\\$id'/",
            $content,
            'functions_major_nm.inc.php should not use raw $id in check_for_rows'
        );
    }

    /**
     * Verify that all message list files have ORDER BY whitelists.
     */
    public function testMessageListFilesHaveOrderByWhitelist(): void
    {
        $files = [
            'ajax/list_messages.php',
            'ajax/list_all_messages.php',
            'ajax/list_sent_messages.php',
            'ajax/list_waste_messages.php',
        ];

        foreach ($files as $relPath) {
            $file = $this->baseDir . '/' . $relPath;
            if (!file_exists($file)) {
                continue;
            }
            $content = file_get_contents($file);
            $this->assertStringContainsString(
                '$allowed_sorts',
                $content,
                "{$relPath} should have an ORDER BY whitelist"
            );
        }
    }
}
