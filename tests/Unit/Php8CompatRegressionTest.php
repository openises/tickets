<?php
/**
 * PHP 7/8 compatibility regression tests (GH #16, PR #17 and the gaps found reviewing it).
 *
 * Everything here is static or pure: no database, no web server, no session. The checks read
 * the PRODUCTION source with the PHP tokenizer (never a regex over raw text, so a comment or a
 * string that merely mentions "MYSQL_ASSOC" is not a hit) and, for parsedate(), extract the real
 * function out of each of its four files and execute it.
 *
 * What went wrong, so the next person can see why each test exists:
 *
 *  1. ajax/reports.php passed MYSQL_ASSOC (an ext/mysql constant, removed in PHP 7) to four
 *     stripslashes_deep() calls. On PHP 7+ that is a fatal "Undefined constant" -- but only inside
 *     the `if ($num_incs > 0)` branch, so any period WITHOUT data rendered fine and the bug hid
 *     for years. The browser received an empty HTTP 500 and the spinner never cleared.
 *  2. Two lines read $_Get['tick_sel'] (not a superglobal) so filtering a report to one incident
 *     produced `AND ticket_id = ` and a SQL syntax error.
 *  3. parsedate() (four copies) did `$diff % 86400` on a float; PHP 8.1 deprecates the implicit
 *     lossy conversion, so every render logged a burst of notices.
 *  4. show_log() used $types without declaring it global. $types is built by log_codes.inc.php at
 *     file scope; `require_once` inside a function is a no-op once the file was loaded, so the
 *     function saw null and the "Code" column rendered blank. show_unit_log(), log_details() and
 *     my_show_log() had exactly the same defect.
 *
 * @since v3.44.7
 */

use PHPUnit\Framework\TestCase;

class Php8CompatRegressionTest extends TestCase
{
    /** @var string Absolute path of the repository root. */
    private string $baseDir;

    /** @var array|null Result of the one-pass whole-tree scan (small: only hits), filled once per run. */
    private static ?array $scanResult = null;

    /** @var string[]|null Relative paths of every scanned PHP file. */
    private static ?array $fileList = null;

    protected function setUp(): void
    {
        $this->baseDir = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Every PHP file we ship, except the test code itself and the bundled third-party
     * charting library (incs/jpgraph*.php), which is not ours to fix.
     *
     * @return string[] Relative paths with forward slashes.
     */
    private function sourceFiles(): array
    {
        if (self::$fileList !== null) {
            return self::$fileList;
        }
        $files = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->baseDir, FilesystemIterator::SKIP_DOTS)
        );
        $rootLen = strlen($this->baseDir) + 1;
        foreach ($it as $f) {
            $p = str_replace('\\', '/', $f->getPathname());
            if (substr($p, -4) !== '.php') {
                continue;
            }
            $rel = substr($p, $rootLen);
            if (preg_match('#^(\.git|vendor|node_modules|tests)/#', $rel)) {
                continue;
            }
            if (preg_match('#^incs/jpgraph#', $rel)) {
                continue;
            }
            $files[] = $rel;
        }
        sort($files);
        self::$fileList = $files;
        return $files;
    }

    /** @return array Token list for a repo file. Not cached: holding every file's tokens costs hundreds of MB. */
    private function tokens(string $rel): array
    {
        $code = file_get_contents($this->baseDir . '/' . $rel);
        return $code === false ? [] : token_get_all($code);
    }

    /**
     * One tokenizer pass over the whole tree that gathers everything the tree-wide tests need, so
     * each file is tokenized once per run and only the (small) findings are kept.
     *
     * @return array{mysql: string[], super: string[], calls: array, declared: array<string, bool>, logcodes: array, logcodeFns: int}
     */
    private function scan(): array
    {
        if (self::$scanResult !== null) {
            return self::$scanResult;
        }
        $removed = '/^(mysql_[a-z_]+|each|create_function|split|spliti|ereg|eregi|ereg_replace|eregi_replace|'
            . 'get_magic_quotes_gpc|get_magic_quotes_runtime|set_magic_quotes_runtime|money_format|'
            . 'session_register|session_unregister|session_is_registered|call_user_method|call_user_method_array|'
            . 'is_real|restore_include_path|mcrypt_[a-z_]+|mssql_[a-z_]+|sql_regcase|hebrevc|convert_cyr_string|'
            . 'ezmlm_hash|fgetss|gzgetss|image2wbmp|jpeg2wbmp|png2wbmp|read_exif_data|libxml_disable_entity_loader|'
            . 'socket_set_blocking|socket_set_timeout|strptime|wddx_[a-z_]+)$/i';
        $proper = ['$_GET', '$_POST', '$_SESSION', '$_SERVER', '$_REQUEST', '$_COOKIE', '$_FILES', '$_ENV'];
        $r = ['mysql' => [], 'super' => [], 'calls' => [], 'declared' => [], 'logcodes' => [], 'logcodeFns' => 0];

        foreach ($this->sourceFiles() as $rel) {
            $code = file_get_contents($this->baseDir . '/' . $rel);
            if ($code === false) {
                continue;
            }
            $t = token_get_all($code);
            foreach ($t as $i => $tok) {
                if (!is_array($tok)) {
                    continue;
                }
                if ($tok[0] === T_FUNCTION) {                                   // declared function names (polyfills count)
                    $nx = self::significant($t, $i, 1);
                    if ($nx === '&') {
                        $k = $i + 1;
                        while ($t[$k] !== '&') {
                            $k++;
                        }
                        $nx = self::significant($t, $k, 1);
                    }
                    if (is_array($nx) && $nx[0] === T_STRING) {
                        $r['declared'][strtolower($nx[1])] = true;
                    }
                } elseif ($tok[0] === T_VARIABLE) {                             // $_Get and friends
                    if (preg_match('/^\$_(get|post|session|server|request|cookie|files|env)$/i', $tok[1])
                        && !in_array($tok[1], $proper, true)) {
                        $r['super'][] = "$rel:{$tok[2]} {$tok[1]}";
                    }
                } elseif ($tok[0] === T_STRING) {
                    $prev = self::significant($t, $i, -1);
                    $prevId = is_array($prev) ? $prev[0] : $prev;
                    $member = in_array($prevId, [T_OBJECT_OPERATOR, T_DOUBLE_COLON], true);
                    if (!$member && preg_match('/^MYSQL_(ASSOC|NUM|BOTH|CLIENT_[A-Z_]+)$/', $tok[1])) {
                        $r['mysql'][] = "$rel:{$tok[2]} {$tok[1]}";
                    }
                    if (preg_match($removed, $tok[1]) && self::significant($t, $i, 1) === '('
                        && !in_array($prevId, [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW], true)) {
                        $r['calls'][] = [strtolower($tok[1]), "$rel:{$tok[2]} {$tok[1]}()"];
                    }
                }
            }
            if (strpos($code, 'log_codes.inc.php') !== false) {                 // functions that load the log codes
                foreach (self::functions($t) as $f) {
                    if (self::bodyIncludes($f['body'], 'log_codes.inc.php')) {
                        $r['logcodeFns']++;
                        if (!in_array('$types', self::globalsDeclared($f['body']), true)) {
                            $r['logcodes'][] = "$rel:{$f['line']} {$f['name']}()";
                        }
                    }
                }
            }
            unset($t, $code);
        }
        self::$scanResult = $r;
        return $r;
    }

    /** Next/previous token that is not whitespace or a comment, or null. */
    private static function significant(array $t, int $i, int $dir)
    {
        $n = count($t);
        $k = $i + $dir;
        while ($k >= 0 && $k < $n && is_array($t[$k])
            && in_array($t[$k][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            $k += $dir;
        }
        return ($k < 0 || $k >= $n) ? null : $t[$k];
    }

    /**
     * All named function declarations in a token list, with their body tokens.
     *
     * @return array<int, array{name: string, line: int, body: array, source: string}>
     */
    private static function functions(array $t): array
    {
        $out = [];
        $n = count($t);
        for ($i = 0; $i < $n; $i++) {
            if (!is_array($t[$i]) || $t[$i][0] !== T_FUNCTION) {
                continue;
            }
            $k = $i + 1;
            while ($k < $n && (is_array($t[$k]) ? $t[$k][0] === T_WHITESPACE : $t[$k] === '&')) {
                $k++;
            }
            if (!is_array($t[$k]) || $t[$k][0] !== T_STRING) {
                continue;                                   // closure / arrow fn, no name
            }
            $name = $t[$k][1];
            $line = $t[$k][2];
            $j = $k;
            while ($j < $n && $t[$j] !== '{') {
                $j++;
            }
            if ($j >= $n) {
                continue;                                   // abstract / interface method
            }
            $depth = 0;
            $end = $j;
            for ($m = $j; $m < $n; $m++) {
                $x = $t[$m];
                if ($x === '{' || (is_array($x) && in_array($x[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                    $depth++;
                } elseif ($x === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $end = $m;
                        break;
                    }
                }
            }
            $src = '';
            for ($m = $i; $m <= $end; $m++) {
                $src .= is_array($t[$m]) ? $t[$m][1] : $t[$m];
            }
            $out[] = [
                'name'   => $name,
                'line'   => $line,
                'body'   => array_slice($t, $j, $end - $j + 1),
                'source' => $src,
            ];
        }
        return $out;
    }

    /** @return string[] Names declared global inside a function body. */
    private static function globalsDeclared(array $body): array
    {
        $names = [];
        $n = count($body);
        for ($i = 0; $i < $n; $i++) {
            if (is_array($body[$i]) && $body[$i][0] === T_GLOBAL) {
                for ($k = $i + 1; $k < $n && $body[$k] !== ';'; $k++) {
                    if (is_array($body[$k]) && $body[$k][0] === T_VARIABLE) {
                        $names[] = $body[$k][1];
                    }
                }
            }
        }
        return $names;
    }

    /** True when the body includes/requires a path mentioning $needle. */
    private static function bodyIncludes(array $body, string $needle): bool
    {
        $n = count($body);
        $inc = [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE];
        for ($i = 0; $i < $n; $i++) {
            if (is_array($body[$i]) && in_array($body[$i][0], $inc, true)) {
                for ($k = $i + 1; $k < $n && $body[$k] !== ';'; $k++) {
                    if (is_array($body[$k]) && $body[$k][0] === T_CONSTANT_ENCAPSED_STRING
                        && strpos($body[$k][1], $needle) !== false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /** Source text of one named function from a repo file, or '' when absent. */
    private function functionSource(string $rel, string $name): string
    {
        foreach (self::functions($this->tokens($rel)) as $f) {
            if ($f['name'] === $name) {
                return $f['source'];
            }
        }
        return '';
    }

    // ------------------------------------------------- 1. removed ext/mysql constants

    /**
     * MYSQL_ASSOC / MYSQL_NUM / MYSQL_BOTH came from ext/mysql (removed in PHP 7). Any use is an
     * immediate "Undefined constant" fatal. MYSQLI_* constants are fine and are not matched.
     */
    public function testNoRemovedMysqlExtensionConstants(): void
    {
        $hits = $this->scan()['mysql'];
        $this->assertSame([], $hits, "ext/mysql constants are undefined on PHP 7+ (fatal):\n" . implode("\n", $hits));
    }

    // ------------------------------------------------------ 2. mis-cased superglobals

    /**
     * $_Get, $_Post, ... are ordinary (always undefined) variables, not superglobals. They read as
     * null, so a query built from one is silently empty.
     */
    public function testNoMiscasedSuperglobals(): void
    {
        $hits = $this->scan()['super'];
        $this->assertSame([], $hits, "Mis-cased superglobals are always undefined:\n" . implode("\n", $hits));
    }

    // ---------------------------------------------- 3. other functions PHP 7/8 removed

    /**
     * Calls to functions removed in PHP 7.0-8.x. A function the tree declares itself (the
     * each()/create_function() polyfills in incs/compat.inc.php) is not a hit.
     */
    public function testNoCallsToRemovedPhpFunctions(): void
    {
        $scan = $this->scan();
        $hits = [];
        foreach ($scan['calls'] as $call) {
            if (!isset($scan['declared'][$call[0]])) {
                $hits[] = $call[1];
            }
        }
        $this->assertSame([], $hits, "Functions removed from PHP are called:\n" . implode("\n", $hits));
    }

    // -------------------------------------- 4. $types must be global where log codes load

    /**
     * log_codes.inc.php assigns $types (and $patient, $facility, ...) as plain variables. Included
     * from inside a function they would be LOCAL -- and require_once is a no-op after the first
     * include in a request. Any function that includes it and then reads $types must therefore
     * bind $types to the global first. This is what blanked the Code column of every log table.
     */
    public function testFunctionsIncludingLogCodesDeclareTypesGlobal(): void
    {
        $scan = $this->scan();
        $this->assertGreaterThanOrEqual(
            4,
            $scan['logcodeFns'],
            'The scan should find show_log, show_unit_log, log_details and my_show_log; if this fails the scanner broke, not the code.'
        );
        $this->assertSame(
            [],
            $scan['logcodes'],
            "These functions include log_codes.inc.php but do not declare global \$types:\n" . implode("\n", $scan['logcodes'])
        );
    }

    // --------------------------------- 5. reports use the sanitised selector, not $_GET

    /** @return array<string, array{string, string}> */
    public static function reportFunctionsUsingSelectors(): array
    {
        return [
            'incident summary'  => ['do_inc_report', 'tick_sel'],
            'incident report'   => ['do_inc_report_2', 'tick_sel'],
            'dispatch report'   => ['do_dispreport', 'tick_sel'],
            'dispatch report 2' => ['do_dispreport', 'resp_sel'],
        ];
    }

    /**
     * ajax/reports.php sanitises tick_sel/resp_sel once at file scope (sanitize_int). The report
     * functions must use those globals, never reach back into $_GET (which is how the $_Get typo
     * happened, and how an absent parameter became an "Undefined array key" warning).
     *
     * @dataProvider reportFunctionsUsingSelectors
     */
    public function testReportFunctionsUseSanitisedSelectors(string $function, string $selector): void
    {
        $src = $this->functionSource('ajax/reports.php', $function);
        $this->assertNotSame('', $src, "$function() not found in ajax/reports.php");
        $this->assertDoesNotMatchRegularExpression(
            '/\$_(GET|Get)\s*\[\s*[\'"]' . $selector . '[\'"]\s*\]/',
            $src,
            "$function() must use the sanitised \$$selector global, not \$_GET['$selector']"
        );
        $this->assertMatchesRegularExpression(
            '/global\s[^;]*\$' . $selector . '\b/',
            $src,
            "$function() must declare global \$$selector"
        );
    }

    // ------------------------------------------------------------- 6. parsedate()

    /** @return array<string, array{string}> */
    public static function parsedateFiles(): array
    {
        return [
            'ajax/reports.php'          => ['ajax/reports.php'],
            'ajax/basic_statistics.php' => ['ajax/basic_statistics.php'],
            'ajax/statistics.php'       => ['ajax/statistics.php'],
            'stats_scr.php'             => ['stats_scr.php'],
        ];
    }

    /** Durations as callers really pass them: averages, i.e. floats, plus boundaries. */
    private static function durations(): array
    {
        return [0, 0.0, 0.4, 0.999, 1, 1.5, 59.9, 60, 60.1, 542.4, 636.5, 3599.99, 3600, 3600.5, 86399.5, 86400,
                86400.5, 90061.7, 123456.789, 1000000.5, 7.25, -5.5, -86400.25, '636.5', null];
    }

    /**
     * Load one copy of parsedate() under a unique name. $original=true rebuilds the ORIGINAL
     * behaviour by turning `$whole = (int) $diff;` back into `$whole = $diff;`, i.e. the modulo
     * operands are floats again, exactly as before the fix.
     *
     * @return string The callable's name.
     */
    private function loadParsedate(string $rel, bool $original): string
    {
        static $n = 0;
        $src = $this->functionSource($rel, 'parsedate');
        $this->assertNotSame('', $src, "parsedate() not found in $rel");
        if ($original) {
            $src = preg_replace('/\$whole\s*=\s*\(int\)\s*\$diff;/', '$whole = $diff;', $src, -1, $count);
            $this->assertSame(1, $count, "$rel: expected exactly one `\$whole = (int) \$diff;` line");
        }
        $name = 'parsedate_' . ($original ? 'old' : 'new') . '_' . (++$n);
        $src = preg_replace('/function\s+parsedate\s*\(/', 'function ' . $name . '(', $src, 1);
        $tmp = tempnam(sys_get_temp_dir(), 'pd');
        file_put_contents($tmp, "<?php\n" . $src . "\n");
        include $tmp;
        unlink($tmp);
        return $name;
    }

    /** Call $fn($arg) and return [result, [captured PHP diagnostics]]. */
    private static function callCapturing(string $fn, $arg): array
    {
        $caught = [];
        set_error_handler(function ($no, $str) use (&$caught) {
            $caught[] = $str;
            return true;
        });
        $prev = error_reporting(E_ALL);
        try {
            $result = $fn($arg);
        } finally {
            error_reporting($prev);
            restore_error_handler();
        }
        return [$result, $caught];
    }

    /**
     * The fixed copy takes floats without a single deprecation or warning.
     *
     * @dataProvider parsedateFiles
     */
    public function testParsedateAcceptsFloatsWithoutDiagnostics(string $rel): void
    {
        $fn = $this->loadParsedate($rel, false);
        foreach (self::durations() as $d) {
            list(, $caught) = self::callCapturing($fn, $d);
            $this->assertSame([], $caught, "$rel parsedate(" . var_export($d, true) . ') raised: ' . implode(' | ', $caught));
        }
    }

    /**
     * Positive control: the ORIGINAL function (cast line removed) really does complain about a
     * float on PHP 8.1+. Without this the test above could pass for the wrong reason.
     *
     * @dataProvider parsedateFiles
     */
    public function testOriginalParsedateDidRaiseTheDeprecation(string $rel): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Implicit float-to-int deprecation only exists on PHP 8.1+.');
        }
        $fn = $this->loadParsedate($rel, true);
        list(, $caught) = self::callCapturing($fn, 636.5);
        $this->assertNotSame([], $caught, "$rel: the un-fixed parsedate(636.5) should raise a deprecation on PHP " . PHP_VERSION);
        $this->assertStringContainsString('Implicit conversion', implode(' ', $caught));
    }

    /**
     * The fix is behaviour-preserving: rendered text is byte-identical to the original for every
     * input, fractional ones included. (It would be easy to cast $diff itself at the top of the
     * function, but then the two statistics copies, which test `$days > 0` before floor(), would
     * stop printing their "0 Days: " prefix for fractional averages, a visible change in a bug
     * fix. Only the `%` operands are cast.)
     *
     * @dataProvider parsedateFiles
     */
    public function testParsedateOutputIsByteIdenticalToTheOriginal(string $rel): void
    {
        $old = $this->loadParsedate($rel, true);
        $new = $this->loadParsedate($rel, false);
        foreach (self::durations() as $d) {
            list($was) = self::callCapturing($old, $d);
            list($now) = self::callCapturing($new, $d);
            $this->assertSame($was, $now, "$rel parsedate(" . var_export($d, true) . ') changed output');
        }
    }

    /** Spot-check real values so the equivalence test cannot pass on two equally wrong functions. */
    public function testParsedateKnownValues(): void
    {
        $reports = $this->loadParsedate('ajax/reports.php', false);
        $stats   = $this->loadParsedate('ajax/statistics.php', false);
        $this->assertSame('0 D 00:10:36', $reports(636.5));
        $this->assertSame('1 D 01:01:01', $reports(90061.7));
        $this->assertSame('10 Minutes: 36 Seconds', $stats(636));
        $this->assertSame('1 Days: 1 Hours: 1 Minutes: 1 Seconds', $stats(90061));
    }
}
