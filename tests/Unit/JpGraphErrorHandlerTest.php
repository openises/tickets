<?php
/**
 * The chart library's error reporter must not recurse (v3.44.7).
 *
 * Found by the first GitHub run of EndpointAuthRuntimeTest: requesting incs/jpgraph.php directly killed the test's
 * web server. On PHP 8 the library's own start-up check (CheckPHPVersion) reports an error; the image error handler
 * then draws a picture of it; drawing needs a colour table that is built by an old-style constructor (function RGB()),
 * which PHP 8 no longer calls as a constructor, so Image::SetColor() raised "Unknown color"; that raised another error
 * picture, and so on. With PHP's usual 128M limit it ended in "Allowed memory size exhausted"; with no limit (php-cli
 * default, some hosts) the operating system killed the process.
 *
 * This test includes the real file in a child PHP with a small memory limit and requires a bounded, one-line result.
 * It fails on the code before the re-entrancy guard (the child dies of memory exhaustion) and needs the GD extension,
 * because without GD the handler takes a different, already-bounded branch and the test would prove nothing.
 *
 * @since v3.44.7
 */

use PHPUnit\Framework\TestCase;

class JpGraphErrorHandlerTest extends TestCase
{
    /**
     * Errors go to STDERR (display_errors=stderr) so nothing reaches STDOUT before the handler runs: output already
     * sent would make the handler take its plain "headers already sent" branch and hide the recursion.
     *
     * @return array{out:string,err:string,code:int}
     */
    private function runChild(string $code): array
    {
        $root = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
        $args = [PHP_BINARY, '-d', 'memory_limit=64M', '-d', 'display_errors=stderr', '-d', 'html_errors=0', '-r', $code];
        $proc = proc_open($args, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, null, ['bypass_shell' => true]);
        $this->assertIsResource($proc, 'could not start a child PHP');
        // The child writes at most a few lines (it is bounded -- that is what is being tested); a failing run that
        // recurses dies of memory exhaustion long before either pipe buffer fills.
        $out = (string) stream_get_contents($pipes[1]);
        $err = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return ['out' => $out, 'err' => $err, 'code' => proc_close($proc)];
    }

    protected function setUp(): void
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('SKIP: the GD extension is not loaded; without it the error handler takes its plain-text branch');
        }
    }

    public function testRequestingTheChartLibraryDirectlyEndsInOneBoundedLine(): void
    {
        $r = $this->runChild('include "incs/jpgraph.php"; echo "RETURNED";');
        $this->assertStringNotContainsString('Allowed memory size', $r['err'] . $r['out'], 'the error reporter recursed until memory ran out: ' . substr($r['err'], 0, 300));
        // PHP 8: the library reports its version problem once and stops. PHP 7 and older would load it quietly.
        if (PHP_MAJOR_VERSION >= 8) {
            $this->assertStringContainsString('a chart could not be drawn', $r['out'], 'expected the bounded one-line report; stdout: ' . substr($r['out'], 0, 200) . ' stderr: ' . substr($r['err'], 0, 300));
            $this->assertStringNotContainsString(PHP_VERSION, $r['out'], 'the report must not tell a visitor which PHP version the server runs');
            $this->assertLessThan(400, strlen($r['out']), 'a bounded report is a line, not a flood');
        }
    }

    public function testARequestForTheLibraryFileItselfAnswersWithNothing(): void
    {
        // What php -S or Apache sets when the visitor asks for /incs/jpgraph.php: SCRIPT_FILENAME is the file itself.
        $r = $this->runChild('$_SERVER["SCRIPT_FILENAME"] = realpath("incs/jpgraph.php"); include "incs/jpgraph.php"; echo "LOADED";');
        $this->assertSame('', $r['out'], 'a direct request for the library must produce no output at all');
        $this->assertStringNotContainsString('Allowed memory size', $r['err']);
        $this->assertSame(0, $r['code'], 'the request ends cleanly, not with a fatal error');
    }

    public function testTheGuardResetsAfterANormalReport(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../../incs/jpgraph_errhandler.inc.php');
        $this->assertSame(1, substr_count($src, 'static $raising = false;'), 'one guard flag in the image handler');
        $this->assertSame(1, substr_count($src, '$raising = true;'));
        // A report that finishes without halting (aHalt = false) must clear the flag, or the NEXT genuine error in the
        // same request would be mistaken for a nested one.
        $this->assertSame(1, preg_match('/\$raising\s*=\s*false;\s*if\(\s*\$aHalt\s*\)\s*die\(\);/', $src), 'the flag is cleared before the final halt check');
    }
}
