<?php
/*
PHP_EOL (string) 
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
	$query = "
		SELECT * FROM `$GLOBALS[mysql_prefix]log`
		WHERE `code` = {$GLOBALS['LOG_ERROR']}
		AND `when` > DATE_SUB(NOW(),INTERVAL 10 DAY )
		ORDER BY `when` DESC";
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$TAB = '\n';
	$tsv = "";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		extract ($row);
		$tsv .="{$when}{$TAB}{$info}{$TAB}{$who}{$TAB}{$ticket_id}{$TAB}{$responder_id}{$TAB}{$who}{$TAB}{$from}{$TAB}PHP_EOL";
		}
		
$fileName = 'error-log.tsv';

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $fileName);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
echo $tsv;
?>
