<?php
//	areas server-side create script
require_once('./incs/functions.inc.php');		// some irv_functions

$istest=FALSE;
extract($_POST);
if(!isset($theTable)) {
	print "-TABLE NAME ERROR";
	}
$temp1 = $temp2 = "";
foreach ($_POST as $VarName=>$VarValue) {
	if(substr($VarName, 0, 4)== "frm_" ) {			// substr(  string, start ,length )
		$temp1 .= "`" . substr($VarName, 4)  . "`,";						// drop 4 fm field names - add tic's
		$temp2 .= "'" . mysql_real_escape_string(trim($VarValue)) . "',";	// field values, apostrophe-enclosed and NOT escaped
		}
	}		// end foreach () ...
																	// build query and drop trailing comma
$query  = "INSERT INTO `$mysql_prefix$theTable` (" . substr($temp1, 0, (strlen($temp1) - 1)) . ") VALUES (" . substr($temp2, 0, (strlen($temp2) - 1)) .")";

print ("-" . $query);
//$result = mysql_query($query) or myerror(basename(__file__), __line__, "mysql_error ", $query );
$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

$insert_id = mysql_insert_id();

//$query = "UPDATE `{$mysql_prefix}sit_ago`  SET  `e` = NOW() WHERE `id` = 1 LIMIT 1";		//the map date column
//$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
unset ($result);

?>
