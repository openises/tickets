<?php
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');
$theTerm = $_GET['q'];
$theAnswer = get_text($theTerm);
print json_encode($theAnswer);
exit();
?>