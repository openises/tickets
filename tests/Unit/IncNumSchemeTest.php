<?php
/**
 * Incident numbering scheme (`_inc_num`) - GH #18 / PR #19 and the gaps found reviewing it.
 *
 * The scheme is a six-element array stored as base64(serialize()). Five places unserialized it
 * unchecked and add.php wrote its copy back after incrementing, so a value that did not
 * unserialize (empty, truncated, hand edited) became serialize(false) = 'YjowOw==' for good: a
 * stable, self-perpetuating loss of the operator's scheme. These tests pin the pure logic in
 * incs/inc_num.inc.php, then run the production code that wraps it (get_inc_num_ary() and the
 * counter-advance block of add.php / add_nm.php, each extracted from its real file) against
 * stubbed database calls, and check from the source that every reader and writer uses the helpers.
 *
 * No database: the helpers take the stored value as an argument and the database calls are stubbed.
 *
 * @since v3.44.7
 */

require_once __DIR__ . '/../../incs/inc_num.inc.php';

use PHPUnit\Framework\TestCase;

/** A class whose __wakeup() would run if unserialize() were allowed to instantiate it. */
class IncNumTestGadget
{
    /** @var bool */
    public static $woke = false;

    public function __wakeup()
    {
        self::$woke = true;
    }
}

/** Stand-ins for the two collaborators of get_inc_num_ary(); the tests set what they return. */
function inc_num_test_get_variable($name)
{
    return $GLOBALS['inc_num_test_stored'];
}

function inc_num_test_error_log($message)
{
    $GLOBALS['inc_num_test_logged'][] = $message;
}

class IncNumSchemeTest extends TestCase
{
    /** @var string Absolute path of the repository root. */
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
        $GLOBALS['inc_num_test_stored'] = false;
        $GLOBALS['inc_num_test_logged'] = [];
        $GLOBALS['mysql_prefix'] = '';
    }

    private function file(string $rel): string
    {
        $code = file_get_contents($this->baseDir . '/' . $rel);
        $this->assertNotFalse($code, "cannot read $rel");
        return $code;
    }

    /** Source text of one named function in a repo file ('' when absent), found with the tokenizer. */
    private function functionSource(string $rel, string $name): string
    {
        $t = token_get_all($this->file($rel));
        $n = count($t);
        for ($i = 0; $i < $n; $i++) {
            if (!is_array($t[$i]) || $t[$i][0] !== T_FUNCTION) {
                continue;
            }
            $k = $i + 1;
            while ($k < $n && is_array($t[$k]) && $t[$k][0] === T_WHITESPACE) {
                $k++;
            }
            if (!is_array($t[$k]) || $t[$k][0] !== T_STRING || $t[$k][1] !== $name) {
                continue;
            }
            $depth = 0;
            $src = '';
            for ($m = $i; $m < $n; $m++) {
                $x = is_array($t[$m]) ? $t[$m][1] : $t[$m];
                $src .= $x;
                if ($x === '{' || (is_array($t[$m]) && in_array($t[$m][0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                    $depth++;
                } elseif ($x === '}') {
                    $depth--;
                    if ($depth === 0) {
                        return $src;
                    }
                }
            }
        }
        return '';
    }

    /** Define $source under a fresh name via a temp file; returns the new function name. */
    private function defineAs(string $source, string $oldName, array $replacements): string
    {
        static $n = 0;
        $new = $oldName . '_under_test_' . (++$n);
        $source = preg_replace('/function\s+' . preg_quote($oldName, '/') . '\s*\(/', 'function ' . $new . '(', $source, 1, $count);
        $this->assertSame(1, $count);
        foreach ($replacements as $from => $to) {
            $this->assertStringContainsString($from, $source, "expected the production code to call $from");
            $source = str_replace($from, $to, $source);
        }
        $tmp = tempnam(sys_get_temp_dir(), 'inum');
        file_put_contents($tmp, "<?php\n" . $source . "\n");
        include $tmp;
        unlink($tmp);
        return $new;
    }

    // ----------------------------------------------------- the default and the encoding

    public function testDefaultIsNoSerialNumbersForTheCurrentYear(): void
    {
        $d = inc_num_default();
        $this->assertCount(6, $d);
        $this->assertSame('0', $d[0], 'style 0 = no serial numbers');
        $this->assertSame(date('y'), $d[5]);
    }

    public function testEncodeRoundTripsThroughParse(): void
    {
        $scheme = ['3', '', '-', '42', '1', '26'];
        $this->assertSame($scheme, inc_num_parse(inc_num_encode($scheme), $status));
        $this->assertSame('ok', $status);
    }

    public function testEncodeIsBase64OfSerialize(): void
    {
        $scheme = ['2', 'INC', '-', '7', '0', '26'];
        $this->assertSame(base64_encode(serialize($scheme)), inc_num_encode($scheme));
    }

    // ------------------------------------------------------------- parsing stored values

    /** @return array<string, array{mixed, string}> stored value => expected status */
    public static function unusableValues(): array
    {
        return [
            'serialize(false), the state the bug left behind'    => [base64_encode(serialize(false)), 'corrupt'],
            'the one-byte payload from the report (MA==)'         => ['MA==', 'corrupt'],
            'a scalar payload'                                    => [base64_encode(serialize('hello')), 'corrupt'],
            'plain text that is not base64 of anything'           => ['not a scheme at all', 'corrupt'],
            'style outside 0-3'                                   => [base64_encode(serialize(['9', '', '', '1', '0', '26'])), 'corrupt'],
            'non-numeric style'                                   => [base64_encode(serialize(['x', '', '', '1', '0', '26'])), 'corrupt'],
            'array without a style'                               => [base64_encode(serialize([1 => 'a', 3 => '5'])), 'corrupt'],
            'a nested array where a plain value belongs'          => [base64_encode(serialize(['2', ['INC'], '-', '5', '0', '26'])), 'corrupt'],
            'an object inside an otherwise valid scheme'          => [base64_encode('a:6:{i:0;s:1:"2";i:1;O:16:"IncNumTestGadget":0:{}i:2;s:1:"-";i:3;s:1:"5";i:4;s:1:"0";i:5;s:2:"26";}'), 'corrupt'],
            'truncated payload'                                   => [substr(base64_encode(serialize(['3', '', '-', '42', '1', '26'])), 0, 14), 'corrupt'],
            'empty string (the old reset seed)'                   => ['', 'empty'],
            'whitespace only'                                     => ["  \n", 'empty'],
            'false, as get_variable() returns for a missing row'  => [false, 'empty'],
            'null'                                                => [null, 'empty'],
        ];
    }

    /**
     * Whatever is stored, the result is a usable six-element default and the status says it was a
     * fallback. A fallback must never be written back (see inc_num_advance()).
     *
     * @dataProvider unusableValues
     * @param mixed $stored
     */
    public function testUnusableValuesYieldTheDefaultAndSayWhy($stored, string $expectedStatus): void
    {
        $got = inc_num_parse($stored, $status);
        $this->assertSame($expectedStatus, $status);
        $this->assertSame(inc_num_default(), $got);
    }

    public function testShippedInstallerDefaultIsAUsableScheme(): void
    {
        $this->assertSame(
            1,
            preg_match('/_inc_num[^A-Za-z0-9+\/=]+([A-Za-z0-9+\/=]{20,})/', $this->file('incs/install_schema.inc.php'), $m),
            'could not find the _inc_num seed in incs/install_schema.inc.php'
        );
        $got = inc_num_parse($m[1], $status);
        $this->assertSame('ok', $status);
        $this->assertSame('0', $got[0], 'a new install starts with numbering off');
        $this->assertCount(6, $got);
    }

    public function testValidSchemePassesThroughUnchanged(): void
    {
        $scheme = ['3', '', '-', '42', '1', '26'];
        $got = inc_num_parse(base64_encode(serialize($scheme)), $status);
        $this->assertSame('ok', $status);
        $this->assertSame($scheme, $got);
    }

    public function testPlainSerializedPre2011FormIsStillRead(): void
    {
        $scheme = ['2', 'INC', '-', '12', '0', '10'];
        $got = inc_num_parse(serialize($scheme), $status);
        $this->assertSame('ok', $status);
        $this->assertSame($scheme, $got);
    }

    public function testSurroundingWhitespaceIsIgnored(): void
    {
        $scheme = ['1', '', '/', '5', '0', '26'];
        $got = inc_num_parse("\n" . base64_encode(serialize($scheme)) . "\r\n", $status);
        $this->assertSame('ok', $status);
        $this->assertSame($scheme, $got);
    }

    public function testShortOrSparseArrayIsCompletedNotDiscarded(): void
    {
        $got = inc_num_parse(base64_encode(serialize(['2', 'INC'])), $status);
        $this->assertSame('ok', $status);
        $this->assertSame('2', $got[0]);
        $this->assertSame('INC', $got[1], 'what survived is kept');
        $this->assertSame(inc_num_default()[5], $got[5], 'what is missing comes from the default');
        $this->assertCount(6, $got);
    }

    public function testIntegerStyleIsAccepted(): void
    {
        $got = inc_num_parse(base64_encode(serialize([1, '', '', 9, 0, '26'])), $status);
        $this->assertSame('ok', $status);
        $this->assertSame(1, $got[0]);
    }

    public function testStoredObjectsAreNeverInstantiated(): void
    {
        IncNumTestGadget::$woke = false;
        $this->assertSame(16, strlen('IncNumTestGadget'), 'fixture length must match the serialized class name');
        $bare   = 'O:16:"IncNumTestGadget":0:{}';
        $nested = 'a:6:{i:0;s:1:"2";i:1;O:16:"IncNumTestGadget":0:{}i:2;s:1:"-";i:3;s:1:"5";i:4;s:1:"0";i:5;s:2:"26";}';
        foreach ([$bare, base64_encode($bare), $nested, base64_encode($nested)] as $stored) {
            $got = inc_num_parse($stored, $status);
            $this->assertSame('corrupt', $status);
            $this->assertSame(inc_num_default(), $got);
        }
        $this->assertFalse(IncNumTestGadget::$woke, 'unserialize() must not run __wakeup() on a stored object');
    }

    public function testParsingNeverRaisesPhpDiagnostics(): void
    {
        $caught = [];
        set_error_handler(function ($no, $str) use (&$caught) {
            if (!(error_reporting() & $no)) {
                return false;                       // suppressed with @, as PHP's own handler would treat it
            }
            $caught[] = $str;
            return true;
        });
        $prev = error_reporting(E_ALL);
        try {
            foreach (self::unusableValues() as $case) {
                inc_num_parse($case[0], $status);
            }
        } finally {
            error_reporting($prev);
            restore_error_handler();
        }
        $this->assertSame([], $caught, 'a bad stored value must not log warnings or notices: ' . implode(' | ', $caught));
    }

    // ------------------------------------------------------------- advancing the counter

    public function testLabelStyleCountsUpAndIsPersisted(): void
    {
        list($next, $persist) = inc_num_advance(['2', 'INC', '-', '5', '0', date('y')], 'ok');
        $this->assertSame(6, (int) $next[3]);
        $this->assertTrue($persist);
    }

    public function testYearStyleSameYearCountsUp(): void
    {
        list($next, $persist) = inc_num_advance(['3', '', '-', '41', '1', date('y')], 'ok');
        $this->assertSame(42, (int) $next[3]);
        $this->assertSame(date('y'), $next[5]);
        $this->assertTrue($persist);
    }

    public function testYearStyleRollsOverToOneWhenTheYearChanges(): void
    {
        list($next, $persist) = inc_num_advance(['3', '', '-', '41', '1', '19'], 'ok');
        $this->assertSame(1, $next[3]);
        $this->assertSame(date('y'), $next[5]);
        $this->assertTrue($persist);
    }

    public function testStyleZeroNeverChangesAndNeverWrites(): void
    {
        $scheme = ['0', '', '', '', '0', date('y')];
        list($next, $persist) = inc_num_advance($scheme, 'ok');
        $this->assertSame($scheme, $next);
        $this->assertFalse($persist, 'nothing moved, so nothing to write');
    }

    /**
     * The heart of GH #18: even a scheme that looks fine must not be written when it did not come
     * from the database as a real value, and the fallback default (style 0) is never written.
     */
    public function testAFallbackIsNeverWrittenBack(): void
    {
        foreach (['empty', 'corrupt'] as $status) {
            list(, $persist) = inc_num_advance(inc_num_default(), $status);
            $this->assertFalse($persist, "status '$status' with the default scheme");
            list(, $persist) = inc_num_advance(['2', 'INC', '-', '5', '0', '26'], $status);
            $this->assertFalse($persist, "status '$status' even with a counting style");
        }
    }

    // ------------------------------------- production wrapper: get_inc_num_ary() with stubs

    /** @return string The callable name of the real get_inc_num_ary(), wired to the test stubs. */
    private function loadGetIncNumAry(): string
    {
        return $this->defineAs(
            $this->functionSource('incs/functions.inc.php', 'get_inc_num_ary'),
            'get_inc_num_ary',
            ['get_variable(' => 'inc_num_test_get_variable(', 'error_log(' => 'inc_num_test_error_log(']
        );
    }

    public function testGetIncNumAryReturnsAStoredSchemeWithoutLogging(): void
    {
        $fn = $this->loadGetIncNumAry();
        $scheme = ['2', 'INC', '-', '5', '0', '26'];
        $GLOBALS['inc_num_test_stored'] = base64_encode(serialize($scheme));
        $this->assertSame($scheme, $fn($status));
        $this->assertSame('ok', $status);
        $this->assertSame([], $GLOBALS['inc_num_test_logged']);
    }

    public function testGetIncNumAryLogsOnlyCorruption(): void
    {
        $fn = $this->loadGetIncNumAry();

        $GLOBALS['inc_num_test_stored'] = base64_encode(serialize(false));        // the post-bug value
        $this->assertSame(inc_num_default(), $fn($status));
        $this->assertSame('corrupt', $status);
        $this->assertCount(1, $GLOBALS['inc_num_test_logged']);
        $this->assertStringContainsString('Incident Numbers', $GLOBALS['inc_num_test_logged'][0], 'the log line should say where to repair it');

        $GLOBALS['inc_num_test_logged'] = [];
        $GLOBALS['inc_num_test_stored'] = '';                                      // never configured: normal, silent
        $this->assertSame(inc_num_default(), $fn($status));
        $this->assertSame('empty', $status);
        $this->assertSame([], $GLOBALS['inc_num_test_logged']);
    }

    public function testGetIncNumAryWorksWithoutAStatusArgument(): void
    {
        $fn = $this->loadGetIncNumAry();
        $GLOBALS['inc_num_test_stored'] = false;                                   // row absent
        $this->assertSame(inc_num_default(), $fn());
    }

    // ------------------- production writers: the counter-advance block of add.php / add_nm.php

    /** @return array<string, array{string}> */
    public static function advancingFiles(): array
    {
        return ['add.php' => ['add.php'], 'add_nm.php' => ['add_nm.php']];
    }

    /**
     * Pull the statements add.php / add_nm.php run after an incident took its serial -- from
     * `$inc_num_next = inc_num_advance(` to the closing brace of the `if` that follows -- and wrap
     * them in a function whose db_query() is a capturing closure.
     *
     * @return callable function (array $scheme, string $status): array of captured db_query calls
     */
    private function loadAdvanceBlock(string $rel): callable
    {
        $code = $this->file($rel);
        $start = strpos($code, '$inc_num_next = inc_num_advance(');
        $this->assertNotFalse($start, "$rel must advance the counter with inc_num_advance()");
        $ifAt = strpos($code, 'if ($inc_num_next[1])', $start);
        $this->assertNotFalse($ifAt, "$rel must test \$inc_num_next[1] before writing");
        $open = strpos($code, '{', $ifAt);
        $depth = 0;
        $end = $open;
        for ($i = $open, $len = strlen($code); $i < $len; $i++) {
            if ($code[$i] === '{') {
                $depth++;
            } elseif ($code[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    $end = $i;
                    break;
                }
            }
        }
        $block = substr($code, $start, $end - $start + 1);
        $this->assertSame(1, substr_count($block, "'_inc_num'"), "$rel: the extracted block should hold the one _inc_num UPDATE");
        $block = str_replace('db_query(', '$capture(', $block);
        $name = 'advance_block_' . preg_replace('/\W/', '_', $rel) . '_' . mt_rand();
        $tmp = tempnam(sys_get_temp_dir(), 'adv');
        file_put_contents(
            $tmp,
            "<?php\nfunction $name(\$inc_num_ary, \$inc_num_status) {\n    \$calls = [];\n"
            . "    \$capture = function (\$sql, \$params = []) use (&\$calls) { \$calls[] = [\$sql, \$params]; return true; };\n"
            . $block . "\n    return \$calls;\n}\n"
        );
        include $tmp;
        unlink($tmp);
        return $name;
    }

    /**
     * A real stored scheme that moved is written exactly once, as base64 (the form the Configuration
     * page reads back), with the counter advanced.
     *
     * @dataProvider advancingFiles
     */
    public function testARealSchemeIsWrittenOnceAsBase64(string $rel): void
    {
        $run = $this->loadAdvanceBlock($rel);
        $calls = $run(['2', 'INC', '-', '5', '0', date('y')], 'ok');
        $this->assertCount(1, $calls);
        $this->assertStringContainsString("`name` = '_inc_num'", $calls[0][0]);
        $this->assertCount(1, $calls[0][1], 'the value is bound as the only parameter');
        $stored = inc_num_parse($calls[0][1][0], $status);
        $this->assertSame('ok', $status, "$rel must store a value the readers and the Configuration page can parse");
        $this->assertSame(base64_encode(serialize($stored)), $calls[0][1][0], "$rel must store base64(serialize()), not the plain form");
        $this->assertSame(6, (int) $stored[3], 'counter advanced from 5');
        $this->assertSame('INC', $stored[1]);
    }

    /**
     * Nothing is written for a fallback or for a scheme that did not move: this is the guard that
     * stops a bad read replacing the operator's scheme.
     *
     * @dataProvider advancingFiles
     */
    public function testNothingIsWrittenForAFallbackOrAnUnusedScheme(string $rel): void
    {
        $run = $this->loadAdvanceBlock($rel);
        $this->assertSame([], $run(inc_num_default(), 'corrupt'), "$rel wrote back a corrupt-value fallback");
        $this->assertSame([], $run(inc_num_default(), 'empty'), "$rel wrote back an empty-value fallback");
        $this->assertSame([], $run(['0', '', '', '', '0', date('y')], 'ok'), "$rel rewrote an unused scheme");
        $this->assertSame([], $run(['2', 'INC', '-', '5', '0', '26'], 'corrupt'), "$rel wrote under a corrupt status");
    }

    // ------------------------------------------------- wiring: every reader and writer

    /** @return array<string, array{string}> */
    public static function numberingFiles(): array
    {
        return [
            'add.php'                                 => ['add.php'],
            'add_nm.php'                              => ['add_nm.php'],
            'portal/ajax/insert_ticket.php'           => ['portal/ajax/insert_ticket.php'],
            'portal/ajax/insert_ticket_tentative.php' => ['portal/ajax/insert_ticket_tentative.php'],
            'config.php'                              => ['config.php'],
        ];
    }

    /** @return string[] Names of every plain function called in a token list (lower case). */
    private static function calledFunctions(array $tokens): array
    {
        $names = [];
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_STRING) {
                continue;
            }
            $k = $i + 1;
            while ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) {
                $k++;
            }
            $p = $i - 1;
            while ($p >= 0 && is_array($tokens[$p]) && $tokens[$p][0] === T_WHITESPACE) {
                $p--;
            }
            $prev = $p >= 0 ? (is_array($tokens[$p]) ? $tokens[$p][0] : $tokens[$p]) : null;
            if (($tokens[$k] ?? null) === '('
                && !in_array($prev, [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW], true)) {
                $names[] = strtolower($tokens[$i][1]);
            }
        }
        return $names;
    }

    /**
     * Nothing but incs/inc_num.inc.php may unserialize the scheme: every reader goes through
     * get_inc_num_ary() (which includes the Configuration page, whose own unserialize(base64_decode())
     * was the fifth unchecked read) and the files that advance the counter store it with
     * inc_num_encode().
     *
     * @dataProvider numberingFiles
     */
    public function testReadersAndWritersGoThroughTheHelpers(string $rel): void
    {
        $called = self::calledFunctions(token_get_all($this->file($rel)));
        $this->assertContains('get_inc_num_ary', $called, "$rel must read the scheme with get_inc_num_ary()");
        $this->assertNotContains('unserialize', $called, "$rel must not unserialize the scheme itself");
        if ($rel !== 'config.php') {
            // config.php builds its own value from the submitted form; the others only ever advance one.
            $this->assertNotContains('serialize', $called, "$rel must store the scheme with inc_num_encode()");
        }
    }

    /**
     * The Configuration page must tell the operator when the stored scheme is unreadable, since
     * saving the form is how it gets repaired.
     */
    public function testConfigurationPageWarnsWhenTheStoredSchemeIsUnreadable(): void
    {
        $code = $this->file('config.php');
        $this->assertMatchesRegularExpression('/get_inc_num_ary\(\s*\$in_status\s*\)/', $code);
        $this->assertMatchesRegularExpression('/\$in_status\s*==\s*\'corrupt\'/', $code);
    }

    // ------------------------------------------ where the empty value came from (reset)

    /**
     * Config > Reset deletes every setting and re-seeds the defaults. It seeded `_inc_num` with an
     * EMPTY string, which no reader could parse - so a reset install met this bug on its first
     * incident. It must seed a real, parseable default.
     */
    public function testResettingSettingsSeedsAParseableNumberingDefault(): void
    {
        $code = $this->file('incs/config.inc.php');
        $this->assertSame(
            1,
            preg_match('/do_insert_settings\(\s*\'_inc_num\'\s*,\s*([^;]*)\)\s*;/', $code, $m),
            "reset_db() no longer seeds '_inc_num'"
        );
        $this->assertStringContainsString(
            'inc_num_encode(inc_num_default())',
            $m[1],
            "reset_db() must seed a real scheme, not '" . trim($m[1]) . "'"
        );
        inc_num_parse(inc_num_encode(inc_num_default()), $status);       // and the parser accepts what that produces
        $this->assertSame('ok', $status);
    }

    public function testFunctionsIncludeLoadsTheHelpers(): void
    {
        $this->assertStringContainsString("require_once __DIR__ . '/inc_num.inc.php'", $this->file('incs/functions.inc.php'));
        $this->assertStringContainsString("inc_num.inc.php", $this->file('incs/config.inc.php'), 'reset_db() needs the helpers when included on its own');
    }
}
