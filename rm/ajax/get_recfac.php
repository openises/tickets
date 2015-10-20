<?php
/*
*/
error_reporting(E_ALL);	
require_once('../../incs/functions.inc.php');
$tick_id = $_GET['ticket_id'];
$ret_arr = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $tick_id;	
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_affected_rows()!=0) {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_fac = $row['rec_facility'];
		}
	} else {
	$the_fac = 0;
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`";	
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_affected_rows()!=0) {
	$outstr = "<SELECT name='frm_recfac' ONCHANGE = 'update_recfac(tick_id, this.value);'>";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$sel = ($row['id']==$the_fac)? " SELECTED": "";
		$outstr .= "<OPTION VALUE=" . $row['id'] . $sel .">" . shorten($row['handle'], 12) . "</OPTION>";		
		}
	$outstr .= "</SELECT>";
	} else {
	$outstr = "";
	}
$ret_arr[0] = $outstr;
print json_encode($ret_arr);
exit();
?>
