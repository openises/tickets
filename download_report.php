<?php
require_once('./incs/functions.inc.php');
include("./incs/html_to_doc.inc.php");
$randomnumber = rand(0000000 , 9999999);
$currDate = date('m,d,Y');
extract($_GET);
$httpuser = get_variable('httpuser');
$httppwd = get_variable('httppwd');

function curPageURL() {
	$pageURL = 'http';
	if (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$uri;
		} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$uri;
		}
	return $pageURL;
	}
	
$serverpath = curPageURL();
$url = $serverpath . "/ajax/reports.php?report=$report&func=$func&date=$date&tick_sel=$ticksel&resp_sel=$respsel&organisation=$organisation&startdate=$startdate&enddate=$enddate&dohtml=true&version=$randomnumber";
if (function_exists("curl_init")) {
	$ch = curl_init();
	$timeout = 20;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	if(($httpuser!="") && ($httppwd!="")) {
		$security = $httpuser .":" . $httppwd;
		curl_setopt($ch, CURLOPT_USERPWD, $security);
		}
	$thePage = curl_exec($ch);
	$thePage = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)  ([\"'>]+)#",'$1'.$url.'$2$3', $thePage);
	curl_close($ch);
	} else {				// no CURL
	if ($fp = @fopen($url, "r")) {
		while (!feof($fp) && (strlen($thePage)<9000)) $thePage .= fgets($fp, 128);
		fclose($fp);
		}
	}

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
?>