<?php
/**
 * Converts semgrep's JSON report into SonarQube's Generic Issue Import
 * Format (sonar.externalIssuesReportPaths), so every Semgrep security
 * finding shows up natively in the SonarQube dashboard alongside the
 * built-in PHP analyzer's own issues -- this project's SonarQube Community
 * Build has no PHP rule-template mechanism and no built-in taint-analysis
 * rule (php:S3649 is Enterprise/Developer-tier only), so Semgrep's
 * taint-mode engine is the actual detector; this script is just the wire
 * format SonarQube expects to display and track its output.
 *
 * This is a dashboard/visibility integration, NOT the CI gate -- the gate
 * that actually blocks a bad merge is tools/semgrep_gate.php (which uses
 * its own reviewed-exceptions baseline). This script imports every current
 * finding, including ones already in that baseline, so a human reviewing
 * the SonarQube UI sees the full picture and can use SonarQube's own
 * issue-lifecycle (Won't Fix / False Positive) to track review state there
 * too if they choose to.
 *
 * Schema per SonarQube's own docs (docs.sonarsource.com, "Generic
 * formatted reports" -- verified live 2026-09, after the version below
 * this comment was committed on the strength of a WARNING message alone
 * and never actually re-run against the server, which is exactly how it
 * shipped with two more schema violations: an ISSUE object may carry ONLY
 * `ruleId`, `effortMinutes`, `primaryLocation`, and `secondaryLocations`
 * -- no `type`, `severity`, `impacts`, or `engineId` at the issue level.
 * All of that lives on the RULE object instead (`type`/`severity` are
 * valid there, but optional once `impacts` is provided, which this file
 * always does). Lesson: a schema change that only warns instead of
 * failing on the OLD shape will still hard-fail on a WRONG NEW shape --
 * re-run the scan for real after any format change, don't trust the
 * warning text to mean the replacement is correct.
 *
 * Usage: php tools/semgrep_to_sonar.php <semgrep-report.json> <output.json>
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php semgrep_to_sonar.php <semgrep-report.json> <output.json>\n");
    exit(2);
}

$semgrepReport = json_decode(file_get_contents($argv[1]), true);
if ($semgrepReport === null) {
    fwrite(STDERR, "semgrep_to_sonar: could not parse " . $argv[1] . "\n");
    exit(2);
}

$seenRuleIds = [];
$rules = [];
$issues = [];
foreach ($semgrepReport['results'] ?? [] as $r) {
    $path = preg_replace('#^/src/#', '', $r['path']);
    $ruleId = $r['check_id'] ?? 'unknown';
    // Semgrep's check_id is often config-path-qualified (e.g.
    // "semgrep.ticketscad-tainted-sql-query-string") -- keep only the
    // final segment as the bare rule id this format expects.
    $bareRuleId = substr(strrchr('.' . $ruleId, '.'), 1);

    if (!isset($seenRuleIds[$bareRuleId])) {
        $seenRuleIds[$bareRuleId] = true;
        $rules[] = [
            'id' => $bareRuleId,
            'name' => 'Tainted SQL query string (Semgrep taint analysis)',
            'description' => $r['extra']['message'] ?? 'SQL injection risk (Semgrep taint analysis)',
            'engineId' => 'semgrep-ticketscad',
            'cleanCodeAttribute' => 'TRUSTWORTHY',
            'impacts' => [['softwareQuality' => 'SECURITY', 'severity' => 'HIGH']],
        ];
    }

    $issues[] = [
        'ruleId' => $bareRuleId,
        'effortMinutes' => 30,
        'primaryLocation' => [
            'message' => $r['extra']['message'] ?? 'SQL injection risk (Semgrep taint analysis)',
            'filePath' => $path,
            'textRange' => [
                'startLine' => max(1, (int) ($r['start']['line'] ?? 1)),
                'endLine' => max(1, (int) ($r['end']['line'] ?? $r['start']['line'] ?? 1)),
            ],
        ],
    ];
}

file_put_contents($argv[2], json_encode(['rules' => $rules, 'issues' => $issues], JSON_PRETTY_PRINT));
echo "Wrote " . count($issues) . " issues (" . count($rules) . " distinct rules) to " . $argv[2] . "\n";
