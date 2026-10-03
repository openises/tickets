<?php
/**
 * Run ajax/reports.php inside the current request and leave its HTML in $thePage.
 *
 * download_report.php and download_report_csv.php used to fetch ajax/reports.php over HTTP from the
 * server to itself. That request carried no cookies, so it could only work while ajax/reports.php
 * asked for no login at all -- which is the hole this replaces: an anonymous visitor could read the
 * same reports (incident contacts, addresses, descriptions). Inside the same request there is no
 * second request to authenticate: the session that passed the including script's gate is what
 * authorizes the data, and ajax/reports.php re-checks it (a cheap no-op the second time).
 *
 * Include it at the TOP LEVEL of a script, never inside a function: the report code reads and writes
 * globals. Expects $report_inline_get (array of the query parameters ajax/reports.php reads from
 * $_GET). The caller's $_GET and working directory are restored afterwards.
 *
 * @since v3.44.7
 */
require_once __DIR__ . '/functions.inc.php';
require_login_or_401();

if (!isset($report_inline_get) || !is_array($report_inline_get)) {
    $report_inline_get = array();
    }
$report_inline_saved_get = $_GET;
$report_inline_saved_cwd = getcwd();
$_GET = $report_inline_get;
if (!defined('TICKETS_REPORT_IN_PROCESS')) {
    define('TICKETS_REPORT_IN_PROCESS', true);
    }
chdir(dirname(__DIR__) . '/ajax');            // ajax/reports.php requires '../incs/...' relative to its own directory
ob_start();
include dirname(__DIR__) . '/ajax/reports.php';
$thePage = ob_get_clean();
chdir($report_inline_saved_cwd);
$_GET = $report_inline_saved_get;
unset($report_inline_saved_get, $report_inline_saved_cwd);
