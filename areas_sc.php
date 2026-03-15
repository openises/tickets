<?php
//	areas server-side create script

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10

$istest=FALSE;
// Replaced extract — explicit variable assignment (Phase 2 cleanup)
$theTable = $_POST['theTable'] ?? '';
if(empty($theTable)) {
	print "-TABLE NAME ERROR";
	}
$columns = [];
$placeholders = [];
$values = [];
foreach ($_POST as $VarName=>$VarValue) {
	if(substr($VarName, 0, 4)== "frm_" ) {			// substr(  string, start ,length )
		$columns[] = "`" . substr($VarName, 4) . "`";
		$placeholders[] = "?";
		$values[] = trim($VarValue);
		}
	}		// end foreach () ...
																	// build query
$query  = "INSERT INTO `{$mysql_prefix}{$theTable}` (" . implode(",", $columns) . ") VALUES (" . implode(",", $placeholders) . ")";

print ("-" . $query);
$result = db_query($query, $values);

$insert_id = db()->insert_id;

//$query = "UPDATE `{$mysql_prefix}sit_ago`  SET  `e` = NOW() WHERE `id` = 1 LIMIT 1";		//the map date column
//$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
unset ($result);

?>
