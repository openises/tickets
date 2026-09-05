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

$issues = [];
foreach ($semgrepReport['results'] ?? [] as $r) {
    $path = preg_replace('#^/src/#', '', $r['path']);
    $severity = strtoupper($r['extra']['severity'] ?? 'ERROR');
    $sonarSeverity = match ($severity) {
        'ERROR' => 'CRITICAL',
        'WARNING' => 'MAJOR',
        default => 'MINOR',
    };

    $issues[] = [
        'engineId' => 'semgrep-ticketscad',
        'ruleId' => $r['check_id'] ?? 'unknown',
        'severity' => $sonarSeverity,
        'type' => 'VULNERABILITY',
        'primaryLocation' => [
            'message' => $r['extra']['message'] ?? 'SQL injection risk (Semgrep taint analysis)',
            'filePath' => $path,
            'textRange' => [
                'startLine' => max(1, (int) ($r['start']['line'] ?? 1)),
                'endLine' => max(1, (int) ($r['end']['line'] ?? $r['start']['line'] ?? 1)),
            ],
        ],
        'effortMinutes' => 30,
    ];
}

file_put_contents($argv[2], json_encode(['issues' => $issues], JSON_PRETTY_PRINT));
echo "Wrote " . count($issues) . " issues to " . $argv[2] . "\n";
