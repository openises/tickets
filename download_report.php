<?php
require_once './incs/functions.inc.php';
require_login_or_401();
include_once './incs/html_to_doc.inc.php';
$currDate = date('m,d,Y');
// Replaced extract — explicit variable assignments (Phase 2 cleanup)
$report       = sanitize_string($_GET['report'] ?? '');
$func         = sanitize_string($_GET['func'] ?? '');
$date         = sanitize_string($_GET['date'] ?? '');
$ticksel      = sanitize_string($_GET['ticksel'] ?? '');
$respsel      = sanitize_string($_GET['respsel'] ?? '');
$organisation = sanitize_string($_GET['organisation'] ?? '');
$startdate    = sanitize_string($_GET['startdate'] ?? '');
$enddate      = sanitize_string($_GET['enddate'] ?? '');
$title        = sanitize_string($_GET['title'] ?? '');
$mode         = sanitize_string($_GET['mode'] ?? '');

// The report HTML comes from ajax/reports.php, run inside this request. It used to be fetched over HTTP from
// the server to itself without cookies, which only worked because ajax/reports.php asked for no login at all.
// In-process there is no second request: the session that passed the gate above is what authorizes the data.
$report_inline_get = array(
    'report' => $report, 'func' => $func, 'date' => $date,
    'tick_sel' => $ticksel, 'resp_sel' => $respsel, 'organisation' => $organisation,
    'startdate' => $startdate, 'enddate' => $enddate, 'dohtml' => 'true');
require './incs/report_inline.inc.php';        // leaves the report HTML in $thePage
//$htmltodoc= new HTML_TO_DOC();

//$htmltodoc->createDoc($thePage,"test");
//$htmltodoc->createDocFromURL($url,"test");

//echo $htmltodoc;

$reportname = base64_decode($title);
$str     = $reportname;
$order   = array(" ", ",");
$replace = '_';

// Processes \r\n's first so they aren't converted twice.
$reportname = str_replace($order, $replace, $str);

if($mode == "doc") {
    header("Content-Type: application/msword");
    header("Content-Disposition: attachment; filename=" . $reportname . ".doc");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
    } else {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=" . $reportname . ".xls");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
    }
echo $thePage;
