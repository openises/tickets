<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
if(empty($_GET)) {
	exit();
	}
require_once('../incs/functions.inc.php');

$filename = "../files/" . $_GET['filename'];
$properFilename = $_GET['origname'];
$filetype = $_GET['type'];

header('Content-Type: {$filetype}');
header("Content-Disposition: attachment; filename=\"$properFilename\";" );
header("Content-Transfer-Encoding: binary");
header("Pragma: public");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
readfile("$filename");
exit();