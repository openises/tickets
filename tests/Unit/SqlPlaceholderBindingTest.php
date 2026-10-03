<?php
/**
 * A SQL fragment that carries `?` placeholders must be executed with its parameters.
 *
 * The September 2026 injection sweep rewrote string-concatenated WHERE clauses into bound ones:
 * the fragment became "`a`.`group` = ?" and its values moved into a $where2Params array. In
 * incs/status_cats.inc.php get_sess_boundaries() builds ONE fragment ($where2) and splices it into
 * four queries, but only the first query was given $where2Params. The other three were run with
 * literal `?` characters in the SQL, so MySQL answered with a syntax error. On PHP 8.1+ mysqli
 * throws, so every page that calls get_sess_boundaries() died with an uncaught
 * mysqli_sql_exception for any user who belongs to a region group (i.e. every user): the New
 * Incident form, the Edit Incident form, Configuration, Call Board, the situation screens.
 * PHPUnit never noticed because no test renders a page; the Semgrep gate checks that tainted
 * values are bound, not that a placeholder has something bound to it.
 *
 * This is a static check, not a proof: for each db_query($q) / db_fetch_*($q) / check_for_rows($q)
 * call that passes only a query variable, it looks at the nearest preceding assignment of that
 * variable and fails if the string interpolates a fragment variable that was itself built from
 * text containing a `?` placeholder.
 *
 * @since v3.44.7
 */

use PHPUnit\Framework\TestCase;

class SqlPlaceholderBindingTest extends TestCase
{
    /** @var string Absolute path of the repository root. */
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
    }

    /** @return string[] Relative paths of the PHP files to check (ours, not bundled libraries). */
    private function sourceFiles(): array
    {
        $files = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->baseDir, FilesystemIterator::SKIP_DOTS)
        );
        $rootLen = strlen($this->baseDir) + 1;
        foreach ($it as $f) {
            $p = str_replace('\\', '/', $f->getPathname());
            $rel = substr($p, $rootLen);
            if (substr($p, -4) === '.php' && !preg_match('#^(\.git|vendor|node_modules|tests|lib|incs/jpgraph)#', $rel)) {
                $files[] = $rel;
            }
        }
        sort($files);
        return $files;
    }

    /**
     * Run the check over one source text.
     *
     * @return string[] "line N: $query runs with no parameters but interpolates $fragment" findings.
     */
    public static function findUnboundFragments(string $code): array
    {
        if (strpos($code, '?') === false) {
            return [];
        }
        // fragment variables: assigned (or appended to) a double-quoted string that looks like SQL and holds a ?
        $fragments = [];
        if (preg_match_all('/\$(\w+)\s*\.?=\s*"([^"\n]*\?[^"\n]*)"/', $code, $m, PREG_SET_ORDER)) {
            foreach ($m as $x) {
                if (preg_match('/`|=\s*\?|\bIN\s*\(|\bLIKE\s*\?/i', $x[2]) && !preg_match('/^(query|sql|msg|text|subject|body|url)\w*$/i', $x[1])) {
                    $fragments[$x[1]] = true;
                }
            }
        }
        if (!$fragments) {
            return [];
        }
        // every double-quoted string assignment, with where it starts
        preg_match_all('/\$(\w+)\s*=\s*"((?:[^"\\\\]|\\\\.)*)"\s*;/s', $code, $assigns, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        // every execution that passes only the query variable
        preg_match_all(
            '/\b(?:db_query|db_fetch_all|db_fetch_one|db_fetch_value|check_for_rows)\s*\(\s*\$(\w+)\s*\)/',
            $code,
            $runs,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );
        $findings = [];
        foreach ($runs as $run) {
            $var = $run[1][0];
            $at = $run[0][1];
            $nearest = null;
            foreach ($assigns as $a) {
                if ($a[1][0] === $var && $a[0][1] < $at) {
                    $nearest = $a;                          // assignments are in file order: the last one wins
                }
            }
            if ($nearest === null) {
                continue;
            }
            foreach (array_keys($fragments) as $frag) {
                if (preg_match('/\{\$' . $frag . '\}|\$' . $frag . '\b/', $nearest[2][0])) {
                    $findings[] = 'line ' . (substr_count(substr($code, 0, $at), "\n") + 1)
                        . ": \$$var runs with no parameters but interpolates \$$frag, which carries a ? placeholder";
                    break;
                }
            }
        }
        return $findings;
    }

    // ----------------------------------------------------------- the checker itself

    public function testCheckerFlagsTheRegressionShape(): void
    {
        $code = <<<'PHP'
<?php
function boundaries() {
    $where2Params = [];
    $where2 = "WHERE (";
    $where2 .= "`a`.`group` = ?";
    $where2Params[] = 1;
    $query = "SELECT * FROM `x` {$where2} AND `use_with_u_rf`= 1";
    $result = db_query($query, $where2Params);
    $query = "SELECT * FROM `y` {$where2} AND `use_with_u_ex`= 1";
    $result = db_query($query);
}
PHP;
        $found = self::findUnboundFragments($code);
        $this->assertCount(1, $found, 'only the second query, which lost its parameters, should be flagged');
        $this->assertStringContainsString('line 10', $found[0]);
        $this->assertStringContainsString('$where2', $found[0]);
    }

    public function testCheckerAcceptsBoundAndUnrelatedQueries(): void
    {
        $code = <<<'PHP'
<?php
function list_things($ids) {
    $where = "WHERE `id` = ?";
    $query = "SELECT * FROM `x` {$where}";
    $r = db_query($query, [1]);
    $query = "SELECT * FROM `responder`";
    $r2 = db_query($query);
    $order = "ORDER BY `date`";
    $query = "SELECT * FROM `z` {$order}";
    $r3 = db_query($query);
}
PHP;
        $this->assertSame([], self::findUnboundFragments($code));
    }

    // ------------------------------------------------------------- the real source

    /**
     * No query in the tree is run without parameters while carrying a placeholder fragment.
     */
    public function testNoQueryCarriesAnUnboundPlaceholderFragment(): void
    {
        $all = [];
        foreach ($this->sourceFiles() as $rel) {
            $code = file_get_contents($this->baseDir . '/' . $rel);
            if ($code === false) {
                continue;
            }
            foreach (self::findUnboundFragments($code) as $finding) {
                $all[] = "$rel $finding";
            }
        }
        $this->assertSame([], $all, "Queries with ? placeholders that are executed without their parameters:\n" . implode("\n", $all));
    }

    /**
     * The specific case that broke every page: all four boundary queries in get_sess_boundaries()
     * share $where2 and so all four must be given $where2Params.
     */
    public function testGetSessBoundariesBindsItsFragmentInEveryQuery(): void
    {
        $code = file_get_contents($this->baseDir . '/incs/status_cats.inc.php');
        $this->assertNotFalse($code);
        $start = strpos($code, 'function get_sess_boundaries()');
        $this->assertNotFalse($start, 'get_sess_boundaries() not found');
        $depth = 0;
        $body = '';
        for ($i = strpos($code, '{', $start), $len = strlen($code); $i < $len; $i++) {   // braces inside the query strings are balanced
            $body .= $code[$i];
            if ($code[$i] === '{') {
                $depth++;
            } elseif ($code[$i] === '}' && --$depth === 0) {
                break;
            }
        }
        $uses = substr_count($body, '{$where2}');
        $bound = preg_match_all('/db_query\(\s*\$query\s*,\s*\$where2Params\s*\)/', $body);
        $this->assertGreaterThanOrEqual(4, $uses, 'expected the four boundary queries to splice in $where2');
        $this->assertGreaterThanOrEqual($uses, $bound, "$uses queries use \$where2 but only $bound are executed with \$where2Params");
    }
}
