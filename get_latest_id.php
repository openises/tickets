<?php
/*
4/9/10 initial release 
6/11/10 disabled unit_flag_2 setting
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
9/1/10 - fix  to error_reporting(E_ALL);
1/6/11 - json encode added
6/10/11 Added groups capability - restricts 
2/10/12 added error handling
*/
error_reporting(E_ALL);
@session_start();
require_once('./incs/functions.inc.php');		//7/28/10
// snap(basename(__FILE__), __LINE__);

function error_out($err_arg) {							// 2/10/12
	do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);		// logs supplied error message
	echo "";											// ajax return data
	exit();												// finished - die
	}				// end function error_out()


get_current();
if (empty($_SESSION)) {error_out(basename(__FILE__) . "@"  . __LINE__);}
$me = $_SESSION['user_id'];

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	// 4/18/11
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;	// 2/10/12
$al_groups = array();
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/18/11
	$al_groups[] = $row['group'];
	}

if(isset($_SESSION['viewed_groups'])) {		//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}
				// most recent chat invites other than written by 'me'
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]chat_invites` WHERE `_by` <> {$me}  AND (`to` = 0   OR `to` = {$me}) ORDER BY `id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;			// 2/10/12
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$the_chat_id = ($row)? $row['id'] : "0";

				// most recent ticket other than written by 'me'

if(!isset($curr_viewed)) {			//	6/10/11
	$x=0;	
	$where2 = "AND (";
	foreach($al_groups as $grp) {
		$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
		$where2 .= "`a`.`group` = '{$grp}'";
		$where2 .= $where3;
		$x++;
		}
	} else {
	$x=0;	
	$where2 = "AND (";	
	foreach($curr_viewed as $grp) {
		$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
		$where2 .= "`a`.`group` = '{$grp}'";
		$where2 .= $where3;
		$x++;
		}
	}

	$where2 .= "AND `a`.`type` = 1";	
				
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` `t`
 		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `t`.`id` = `a`.`resource_id`
		WHERE `t`.`_by` <> {$me} AND `t`.`status` = {$GLOBALS['STATUS_OPEN']} $where2 ORDER BY `t`.`id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;				// 2/10/12
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$the_tick_id = ($row)? $row['id'] : "0";

							// position updates?
							
if(!isset($curr_viewed)) {			//	6/10/11
	$x=0;	
	$where2 = "AND (";
	foreach($al_groups as $grp) {
		$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
		$where2 .= "`a`.`group` = '{$grp}'";
		$where2 .= $where3;
		$x++;
		}
	} else {
	$x=0;	
	$where2 = "AND (";	
	foreach($curr_viewed as $grp) {
		$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
		$where2 .= "`a`.`group` = '{$grp}'";
		$where2 .= $where3;
		$x++;
		}
	}

	$where2 .= "AND `a`.`type` = 2";	
							
$where4 = "AND `a`.`type` = 2";							
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `r`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `r`.`id` = `a`.`resource_id`
		WHERE  `callsign` > '' AND (`aprs` = 1 OR  `instam` = 1 OR  `locatea` = 1 OR  `gtrack` = 1 OR  `glat` = 1 ) $where2 ORDER BY `r`.`updated` DESC LIMIT 1";
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;			// 2/10/12
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

if (!($row )) {				// latest unit status updates written by others
	if(!isset($curr_viewed)) {			//	6/10/11
		$x=0;	
		$where2 = "AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		} else {
		$x=0;	
		$where2 = "AND (";	
		foreach($curr_viewed as $grp) {
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		}

		$where2 .= "AND `a`.`type` = 2";
		
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `r`
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `r`.`id` = `a`.`resource_id`
	WHERE `r`.`user_id` != {$me} $where2 $where4 ORDER BY `r`.`updated` DESC LIMIT 1";		// get most recent
	$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
	$row =  (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;
	}

if ($row) {
	$_SESSION['unit_flag_1'] = $row['id'];
//	$_SESSION['unit_flag_2'] = $me;		// 6/11/10
	}
						// 1/21/11 - get most recent dispatch

if(!isset($curr_viewed)) {			//	6/10/11
	$x=0;	
	$where2 = "AND (";
	foreach($al_groups as $grp) {
		$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
		$where2 .= "`a`.`group` = '{$grp}'";
		$where2 .= $where3;
		$x++;
		}
	} else {
	$x=0;	
	$where2 = "AND (";	
	foreach($curr_viewed as $grp) {
		$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
		$where2 .= "`a`.`group` = '{$grp}'";
		$where2 .= $where3;
		$x++;
		}
	}

	$where2 .= "AND `a`.`type` = 1";							
						
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` `as`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON `as`.`ticket_id` = `t`.`id`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `t`.`id` = `a`.`resource_id`		
		WHERE `as`.`user_id` != {$me} $where2 ORDER BY `as`.`as_of` DESC LIMIT 1";		// get most recent
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$assign_row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;


$the_unit_id = ($row)? $row['id'] : "0";
$the_updated = ($row)? $row['updated'] : "0";
$the_dispatch_change = ($assign_row)? $assign_row['as_of']: "";
$the_hash = md5($the_chat_id . $the_tick_id . $the_unit_id . $the_updated . $the_dispatch_change);
$ret_arr = array ($the_chat_id, $the_tick_id, $the_unit_id, $the_updated, $the_dispatch_change, $the_hash);

print json_encode($ret_arr);				// 1/6/11
?>