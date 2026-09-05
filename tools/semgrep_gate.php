<?php
/**
 * Semgrep SQL-injection gate for the Tickets CAD legacy codebase.
 *
 * Runs the taint-mode rule at .semgrep/ticketscad-sql-injection.yml (via the
 * semgrep/semgrep Docker image) against the whole tree, then diffs the
 * findings against tools/semgrep_baseline.txt -- a reviewed-and-accepted
 * exceptions list, one "file:line" per line, generated the same way
 * NewUI's tools/*_audit.php family (schema_audit, api_contract_audit, ...)
 * already works: a NEW finding not in the baseline fails the build; a
 * baselined finding is reported but does not fail it.
 *
 * Every entry in the baseline is a taint-tracking false positive against
 * code that already uses the correct fix for its position (an allowlist
 * check against a literal set of known-safe strings for an IDENTIFIER or
 * SQL-keyword position -- Semgrep's OSS taint engine doesn't reliably
 * recognize every syntactic shape of that idiom as sanitizing). It is NOT a
 * list of accepted vulnerabilities. Re-verify with `php tools/sqli_audit.php`
 * style manual review (or just read the flagged line) before adding a new
 * entry -- the baseline should only grow when a human has actually confirmed
 * the flagged value is checked against a real allowlist before use.
 *
 * Usage: php tools/semgrep_gate.php [--update-baseline]
 *
 * IMPORTANT if regenerating the baseline from a Windows checkout: git's
 * autocrlf converts tracked files to CRLF in a Windows working tree, but
 * GitHub Actions' Linux runners check out pure LF. Semgrep's line-based
 * taint tracker can genuinely report DIFFERENT line numbers for the exact
 * same logical content depending on which encoding it scans (confirmed:
 * a CRLF Windows working-tree scan and CI's LF checkout disagreed on 4
 * findings in tables.php for identical code). Regenerate the baseline
 * from a line-ending-normalized extraction of the exact commit, e.g.:
 *   git -c core.autocrlf=false archive <commit> | tar -x -C <clean-dir>
 * -- never from a plain Windows working-tree copy -- or the baseline will
 * silently fail to match what CI actually sees.
 */

$root = dirname(__DIR__);
$baselinePath = $root . '/tools/semgrep_baseline.txt';
$updateBaseline = in_array('--update-baseline', $argv ?? [], true);

// Pinned, not :latest -- an un-pinned tag makes the gate non-reproducible:
// the same committed code can start failing or passing between runs as the
// upstream image updates its taint engine, which is exactly what happened
// during development here (a later "latest" pull found 5 more matches in
// already-reviewed tables.php code that an earlier pull didn't). Bump this
// deliberately, re-run the whole baseline-review process, never silently.
$cmd = 'docker run --rm -v ' . escapeshellarg($root) . ':/src semgrep/semgrep:1.175.1 '
     . 'semgrep scan --config /src/.semgrep/ --json /src 2>/dev/null';
$output = shell_exec($cmd);
if ($output === null || trim($output) === '') {
    fwrite(STDERR, "semgrep_gate: semgrep produced no output -- is Docker available?\n");
    exit(2);
}

$data = json_decode($output, true);
if ($data === null) {
    fwrite(STDERR, "semgrep_gate: could not parse semgrep JSON output\n");
    fwrite(STDERR, substr($output, 0, 2000) . "\n");
    exit(2);
}

if (!empty($data['errors'])) {
    fwrite(STDERR, "semgrep_gate: semgrep reported errors:\n");
    foreach ($data['errors'] as $e) {
        fwrite(STDERR, '  ' . ($e['message'] ?? json_encode($e)) . "\n");
    }
    exit(2);
}

$findings = [];
foreach ($data['results'] as $r) {
    $path = preg_replace('#^/src/#', '', $r['path']);
    $key = $path . ':' . $r['start']['line'];
    $findings[$key] = [
        'file' => $path,
        'line' => $r['start']['line'],
        'rule' => $r['check_id'],
        'message' => $r['extra']['message'] ?? '',
    ];
}

$baseline = [];
if (file_exists($baselinePath)) {
    foreach (file($baselinePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $baseline[$line] = true;
    }
}

if ($updateBaseline) {
    ksort($findings);
    file_put_contents(
        $baselinePath,
        "# Reviewed Semgrep SQL-injection taint-tracking false positives.\n"
        . "# See tools/semgrep_gate.php's docblock before adding an entry --\n"
        . "# this file records ACCEPTED FALSE POSITIVES, never real findings.\n"
        . "# Regenerate with: php tools/semgrep_gate.php --update-baseline\n"
        . implode("\n", array_keys($findings)) . "\n"
    );
    echo "Baseline updated: " . count($findings) . " entries.\n";
    exit(0);
}

$new = [];
foreach ($findings as $key => $f) {
    if (!isset($baseline[$key])) {
        $new[$key] = $f;
    }
}

$knownCount = count($findings) - count($new);
echo "Semgrep SQL-injection gate: " . count($findings) . " total findings, "
   . "{$knownCount} baselined, " . count($new) . " new.\n\n";

if ($new) {
    echo "=== NEW findings not in tools/semgrep_baseline.txt ===\n\n";
    foreach ($new as $key => $f) {
        echo "[{$f['rule']}] {$f['file']}:{$f['line']}\n";
        echo '  ' . wordwrap($f['message'], 100, "\n  ") . "\n\n";
    }
    echo "If a finding above is a genuine false positive (the value IS checked\n";
    echo "against a literal allowlist before use), re-run with --update-baseline\n";
    echo "after confirming that by reading the code -- do not add an entry\n";
    echo "without reading the flagged line first.\n";
    exit(1);
}

echo "No new SQL-injection findings.\n";
exit(0);
