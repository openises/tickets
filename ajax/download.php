<?php
if(empty($_GET)) {
    exit();
    }
require_once('../incs/functions.inc.php');

$filename = "../files/" . sanitize_string($_GET['filename']);
$properFilename = sanitize_string($_GET['origname']);
$filetype = sanitize_string($_GET['type']);

header('Content-Type: {$filetype}');
header("Content-Disposition: attachment; filename=\"$properFilename\";" );
header("Content-Transfer-Encoding: binary");
header("Pragma: public");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
readfile("$filename");
exit();