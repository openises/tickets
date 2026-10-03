<?php
/**
 * Every PHP file a web server can be asked for must either authenticate its caller or be on an
 * explicit, reasoned list. This is the durable half of the 3.44.7 fix for anonymous access to
 * ajax/reports.php and ~450 sibling endpoints (see docs/security/legacy-endpoint-auth-inventory.md).
 *
 * The inventory document IS the allowlist: one row per file with a class (LOGIN, GATED, PUBLIC, OWN,
 * CLI, TOOL, INCLUDE, STATIC) and a reason. This test then checks the code against the row:
 *
 *   - a file missing from the table fails (so a NEW unauthenticated endpoint cannot be added silently);
 *   - LOGIN/GATED files must call do_login() / require_login_or_401() as a top-level statement before
 *     any output, query, file access or mail (found with the tokenizer, never with a text search, so a
 *     comment mentioning the gate proves nothing);
 *   - PUBLIC files must carry a real reason;
 *   - CLI/TOOL files must end a web request with their very first statement;
 *   - INCLUDE/STATIC files must print nothing at top level.
 *
 * The runtime counterpart (real PHP server, real database) is EndpointAuthRuntimeTest.
 *
 * No database, no web server: pure static analysis.
 *
 * @since v3.44.7
 */

require_once __DIR__ . '/EndpointAuthScanner.php';

use PHPUnit\Framework\TestCase;

class EndpointAuthTest extends TestCase
{
    /** @var string */
    private static $root;
    /** @var array<string,array{class:string,before:string,reason:string}> */
    private static $inventory = [];

    /** Directories that hold user data or tooling, never application code. */
    private const SKIP_DIRS = ['.git', '.github', '.idea', 'vendor', 'node_modules', '_osm', 'backups', 'kml_files',
        'files', 'file_waste', 'mdb_files', 'mdb_files_waste', 'mdb_pictures', 'mdb_pictures_waste', 'pictures',
        'pictures_waste', 'uploads', '_tile_cache'];

    /**
     * Files that exist only on an installed system or a developer machine and are written by the
     * installer, not shipped: not part of the repository, so not part of the inventory.
     */
    private const LOCAL_ONLY = ['incs/mysql.inc.php'];

    public static function setUpBeforeClass(): void
    {
        self::$root = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
        $doc = file_get_contents(self::$root . '/docs/security/legacy-endpoint-auth-inventory.md');
        if ($doc === false) {
            return;
        }
        foreach (preg_split('/\R/', $doc) as $line) {
            if (preg_match('/^\|\s*`([^`]+)`\s*\|\s*([A-Z]+)\s*\|\s*([a-z\/]+)\s*\|\s*(.+?)\s*\|\s*$/', $line, $m)) {
                self::$inventory[$m[1]] = ['class' => $m[2], 'before' => $m[3], 'reason' => $m[4]];
            }
        }
    }

    /** @return array<int,string> repo-relative paths of every .php file that could be requested */
    private static function phpFiles(): array
    {
        $out = [];
        $it = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator(self::$root, FilesystemIterator::SKIP_DOTS),
            static function ($file, $key, $iterator) {
                if ($iterator->hasChildren()) {
                    return !in_array($file->getFilename(), self::SKIP_DIRS, true);
                }
                return true;
            }
        ));
        foreach ($it as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            $rel = ltrim(substr(str_replace('\\', '/', $file->getPathname()), strlen(self::$root)), '/');
            if (in_array($rel, self::LOCAL_ONLY, true)) {
                continue;
            }
            $out[] = $rel;
        }
        sort($out);
        return $out;
    }

    private function source(string $rel): string
    {
        $s = file_get_contents(self::$root . '/' . $rel);
        $this->assertNotFalse($s, "cannot read $rel");
        return $s;
    }

    /** @param array<int,string> $problems */
    private function assertNoProblems(array $problems, string $what): void
    {
        $this->assertSame([], $problems, $what . ":\n  " . implode("\n  ", array_slice($problems, 0, 40)) . (count($problems) > 40 ? "\n  ... and " . (count($problems) - 40) . ' more' : ''));
    }

    // ---------------------------------------------------------------- the inventory itself

    public function testInventoryIsPresentAndParses(): void
    {
        $this->assertGreaterThan(600, count(self::$inventory), 'docs/security/legacy-endpoint-auth-inventory.md has too few parsable rows');
        $classes = ['LOGIN', 'GATED', 'PUBLIC', 'OWN', 'CLI', 'TOOL', 'INCLUDE', 'STATIC'];
        $bad = [];
        foreach (self::$inventory as $f => $r) {
            if (!in_array($r['class'], $classes, true)) {
                $bad[] = "$f has unknown class {$r['class']}";
            }
            if (strlen($r['reason']) < 12) {
                $bad[] = "$f has no reason";
            }
        }
        $this->assertNoProblems($bad, 'inventory rows');
    }

    public function testEveryPhpFileIsInTheInventory(): void
    {
        $missing = [];
        foreach (self::phpFiles() as $f) {
            if (!isset(self::$inventory[$f])) {
                $missing[] = $f;
            }
        }
        $this->assertNoProblems(
            $missing,
            'PHP files missing from docs/security/legacy-endpoint-auth-inventory.md. Gate the file with do_login(basename(__FILE__)) (a page) or require_login_or_401() (data) and add a row, '
            . 'or add a PUBLIC row that says why it is safe to serve to anyone'
        );
    }

    public function testNoInventoryRowForAFileThatIsGone(): void
    {
        $stale = [];
        foreach (array_keys(self::$inventory) as $f) {
            if (!is_file(self::$root . '/' . $f) && !in_array($f, self::LOCAL_ONLY, true)) {
                $stale[] = $f;
            }
        }
        $this->assertNoProblems($stale, 'inventory rows for files that do not exist');
    }

    // ---------------------------------------------------------------- LOGIN / GATED

    public function testEveryGatedFileAuthenticatesBeforeItDoesAnything(): void
    {
        $problems = [];
        $n = 0;
        foreach (self::$inventory as $f => $r) {
            if ($r['class'] !== 'LOGIN' && $r['class'] !== 'GATED') {
                continue;
            }
            if (!is_file(self::$root . '/' . $f)) {
                continue;
            }
            $n++;
            $a = EndpointAuthScanner::analyze($this->source($f));
            if ($a['gate'] === null) {
                $problems[] = "$f: no top-level do_login()/require_login_or_401() call" . ($a['toplevel_gate_calls'] ? ' (found only: ' . implode(',', $a['toplevel_gate_calls']) . ')' : '');
                continue;
            }
            if ($r['class'] === 'LOGIN' && $a['gate'] !== 'do_login') {
                $problems[] = "$f is a LOGIN page but its gate is {$a['gate']}()";
            }
            if ($a['violations']) {
                $problems[] = "$f: gate at line {$a['gate_line']} comes too late: " . implode('; ', array_slice($a['violations'], 0, 2));
            }
        }
        $this->assertGreaterThan(500, $n, 'expected the inventory to list 500+ gated files');
        $this->assertNoProblems($problems, 'gated files that do not gate first');
    }

    public function testPagesUseTheLoginFormAndDataEndpointsUse401(): void
    {
        // The two idioms exist for different callers: only check that the helper behind each is defined.
        $login = $this->source('incs/login.inc.php');
        $this->assertMatchesRegularExpression('/function\s+do_login\s*\(/', $login);
        $this->assertMatchesRegularExpression('/function\s+require_login_or_401\s*\(\s*\)/', $login);
        $this->assertStringContainsString('http_response_code(401)', $login, 'the data gate must answer 401');
        $this->assertStringNotContainsString('WWW-Authenticate', $login, '401 must not trigger the browser basic-auth prompt');
    }

    // ---------------------------------------------------------------- OWN

    public function testOwnRowsFollowTheirDocumentedRule(): void
    {
        $problems = [];
        foreach (self::$inventory as $f => $r) {
            if ($r['class'] !== 'OWN') {
                continue;
            }
            $src = $this->source($f);
            if (strpos($src, 'responder_mobile_forcelogin') === false) {
                $problems[] = "$f is OWN but never reads responder_mobile_forcelogin";
            }
            $hasGate = (strpos($src, 'require_login_or_401(') !== false) || (strpos($src, 'do_mobile_login(') !== false);
            if (!$hasGate) {
                $problems[] = "$f is OWN but calls neither require_login_or_401() nor do_mobile_login()";
            }
        }
        $this->assertNoProblems($problems, 'OWN rows');
    }

    // ---------------------------------------------------------------- PUBLIC

    public function testPublicRowsSayWhyTheyArePublic(): void
    {
        $problems = [];
        foreach (self::$inventory as $f => $r) {
            if ($r['class'] !== 'PUBLIC') {
                continue;
            }
            if (strlen($r['reason']) < 40) {
                $problems[] = "$f: reason too short to justify serving it to anyone";
            }
            // A public file must not quietly carry a gate: that would make the row a lie in one direction.
            if (preg_match('/require_login_or_401\s*\(\s*\)\s*;/', $this->source($f))) {
                $problems[] = "$f is PUBLIC but calls require_login_or_401() as a statement";
            }
        }
        $this->assertNoProblems($problems, 'PUBLIC rows');
    }

    // ---------------------------------------------------------------- CLI / TOOL

    public function testCommandLineScriptsEndAWebRequestFirst(): void
    {
        $problems = [];
        foreach (self::$inventory as $f => $r) {
            if ($r['class'] !== 'CLI' && $r['class'] !== 'TOOL') {
                continue;
            }
            $src = $this->source($f);
            if ($r['class'] === 'CLI') {
                if (!EndpointAuthScanner::hasCliGuard(token_get_all($src))) {
                    $problems[] = "$f: first statement is not the CLI-only guard (if (PHP_SAPI !== 'cli') { ...exit }";
                }
                continue;
            }
            $tokens = token_get_all($src);
            $flat = '';
            $count = 0;
            foreach ($tokens as $t) {
                if (is_array($t)) {
                    if (in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG], true)) {
                        continue;
                    }
                    $flat .= strtolower($t[1]);
                } else {
                    $flat .= strtolower($t);
                }
                if (++$count > 60) {
                    break;
                }
            }
            if (!preg_match('/^if\(php_sapi!==[\'"]cli[\'"]&&!is_file\(__dir__\.[\'"]\/[a-z0-9_.\-]+\.enable[\'"]\)\)\{.{0,120}?(exit|die)/s', $flat)) {
                $problems[] = "$f: first statement is not the .enable-file guard";
            }
        }
        $this->assertNoProblems($problems, 'CLI/TOOL rows');
    }

    // ---------------------------------------------------------------- INCLUDE / STATIC

    public function testIncludeFilesPrintNothingAtTopLevel(): void
    {
        $problems = [];
        foreach (self::$inventory as $f => $r) {
            if ($r['class'] !== 'INCLUDE' || !is_file(self::$root . '/' . $f)) {
                continue;
            }
            $a = EndpointAuthScanner::analyze($this->source($f));
            if ($a['has_inline_html'] || $a['toplevel_output']) {
                $problems[] = "$f is listed as a library include but prints at top level; gate it (GATED) or make it print nothing";
            }
        }
        $this->assertNoProblems($problems, 'INCLUDE rows');
    }

    public function testStaticFilesContainNoPhp(): void
    {
        $problems = [];
        foreach (self::$inventory as $f => $r) {
            if ($r['class'] !== 'STATIC') {
                continue;
            }
            foreach (token_get_all($this->source($f)) as $t) {
                // An empty open/close tag pair is not code; any token besides tags, whitespace and comments is.
                if (is_array($t) && in_array($t[0], [T_INLINE_HTML, T_OPEN_TAG, T_CLOSE_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }
                $problems[] = "$f is listed as STATIC but contains PHP code (" . (is_array($t) ? token_name($t[0]) . ' at line ' . $t[2] : $t) . ')';
                break;
            }
        }
        $this->assertNoProblems($problems, 'STATIC rows');
    }

    // ---------------------------------------------------------------- the report download fix

    public function testDownloadScriptsNoLongerFetchTheReportOverHttp(): void
    {
        // They used to curl ajax/reports.php from the server to itself WITHOUT cookies; that only worked
        // because the endpoint asked for no login. Any self-HTTP fetch here would break under the gate.
        foreach (['download_report.php', 'download_report_csv.php', 'incs/report_inline.inc.php'] as $f) {
            $src = $this->source($f);
            foreach (['curl_init', 'fsockopen', 'SERVER_NAME', 'HTTP_HOST'] as $needle) {
                $this->assertStringNotContainsString($needle, $src, "$f must not fetch its own endpoint over HTTP ($needle)");
            }
        }
        $this->assertStringContainsString("define('TICKETS_REPORT_IN_PROCESS'", $this->source('incs/report_inline.inc.php'));
        $this->assertStringContainsString("TICKETS_REPORT_IN_PROCESS", $this->source('ajax/reports.php'), 'ajax/reports.php must not exit() inside the download scripts');
    }

    // ---------------------------------------------------------------- the scanner itself

    public function testScannerAcceptsAGateThatComesFirst(): void
    {
        $a = EndpointAuthScanner::analyze("<?php\nrequire_once '../incs/functions.inc.php';\nrequire_login_or_401();\n\$r = db_query('SELECT 1');\necho 'x';\n");
        $this->assertSame('require_login_or_401', $a['gate']);
        $this->assertSame([], $a['violations']);
    }

    public function testScannerFlagsDataAccessBeforeTheGate(): void
    {
        $a = EndpointAuthScanner::analyze("<?php\nrequire_once '../incs/functions.inc.php';\n\$r = db_query('SELECT 1');\nrequire_login_or_401();\n");
        $this->assertNotEmpty($a['violations']);
        $this->assertStringContainsString('db_query', $a['violations'][0]);
    }

    public function testScannerFlagsOutputBeforeTheGate(): void
    {
        $this->assertNotEmpty(EndpointAuthScanner::analyze("<?php\necho 'hello';\ndo_login(basename(__FILE__));\n")['violations']);
        $this->assertNotEmpty(EndpointAuthScanner::analyze("<html>\n<?php do_login(basename(__FILE__));\n")['violations']);
    }

    public function testScannerDoesNotTrustAConditionalGate(): void
    {
        $a = EndpointAuthScanner::analyze("<?php\nif (\$x) {\n    do_login(basename(__FILE__));\n}\necho db_query('x');\n");
        $this->assertNull($a['gate']);
    }

    public function testScannerDoesNotTrustAMentionInAComment(): void
    {
        $a = EndpointAuthScanner::analyze("<?php\n// do_login(basename(__FILE__));\n/* require_login_or_401(); */\n\$s = 'do_login(x);';\necho 'data';\n");
        $this->assertNull($a['gate']);
    }

    public function testScannerIgnoresWhatOnlyRunsWhenAFunctionIsCalled(): void
    {
        $a = EndpointAuthScanner::analyze("<?php\nfunction later() { echo db_query('x'); }\nrequire_login_or_401();\nlater();\n");
        $this->assertSame('require_login_or_401', $a['gate']);
        $this->assertSame([], $a['violations']);
    }

    public function testScannerAcceptsTheLogoutOrLoginShape(): void
    {
        $src = "<?php\nrequire_once 'incs/functions.inc.php';\nif (isset(\$_GET['logout'])) {\n    do_logout();\n    exit();\n    }\nelse {\n    do_login(basename(__FILE__));\n    }\necho 'page';\n";
        $a = EndpointAuthScanner::analyze($src);
        $this->assertSame('do_login', $a['gate']);
        $this->assertSame([], $a['violations']);
        // ...but not when the logout branch can fall through to the page
        $bad = "<?php\nif (isset(\$_GET['logout'])) {\n    do_logout();\n    }\nelse {\n    do_login(basename(__FILE__));\n    }\n";
        $this->assertNull(EndpointAuthScanner::analyze($bad)['gate']);
    }

    public function testCliGuardDetector(): void
    {
        $ok = "<?php\nif (PHP_SAPI !== 'cli') {\n    http_response_code(404);\n    exit();\n    }\n\$x = 1;\n";
        $this->assertTrue(EndpointAuthScanner::hasCliGuard(token_get_all($ok)));
        $late = "<?php\nerror_reporting(E_ALL);\nif (PHP_SAPI !== 'cli') { exit(); }\n";
        $this->assertFalse(EndpointAuthScanner::hasCliGuard(token_get_all($late)), 'a statement before the guard could already have run');
        $noexit = "<?php\nif (PHP_SAPI !== 'cli') {\n    \$web = true;\n}\n";
        $this->assertFalse(EndpointAuthScanner::hasCliGuard(token_get_all($noexit)));
    }
}
