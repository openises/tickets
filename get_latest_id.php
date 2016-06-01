<?php
/*
4/9/10 initial release
6/11/10 disabled unit_flag_2 setting
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
9/1/10 - fix  to error_reporting(E_ALL);
1/6/11 - json encode added
6/10/11 Added groups capability - restricts
2/10/12 added error handling
2/21/12 corrections two places to disambiguate 'id'
2/25/12 added act_id and pat_id to the returned array  -AS
5/29/2013 error handling upgrades several lines, notably error_out()
5/30/2013 relocated get_current() call to after json data return;	// update remotes position
6/23/2013 correction to obtain affected row count
7/15/13 Replaced mysql_affected_rows with mysql_num_rows
3/23/2015 - added os-watch functions
4/4/2015 - expanded osw for incident type inclusion
4/13/2015 - handle empty 'not-in' string
*/

error_reporting(E_ALL);
@session_start();
require_once('./incs/functions.inc.php');				//7/28/10
// snap(basename(__FILE__), __LINE__);

$_SESSION["osw_ntrupt_ok"] = TRUE; 						// usage TBD

function error_out($err_arg) {							// 2/10/12
	do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);		// logs supplied error message
	return;
	}				// end function error_out()


//	if (empty($_SESSION)) {error_out(basename(__FILE__) . "@"  . __LINE__);}
$me = array_key_exists('user_id', $_SESSION)? $_SESSION['user_id'] :  1;

$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {		//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}
				// most recent chat invites other than written by 'me'
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]chat_invites` WHERE `_by` <> {$me}  AND (`to` = 0 OR `to` = {$me}) ORDER BY `id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;			// 2/10/12
$row = (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;		// 6/23/2013

$the_chat_id = ($row)? $row['id'] : "0";

				// most recent ticket other than written by 'me'

if(!isset($curr_viewed)) {
	if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
		$where2 = "AND `a`.`type` = 1";
		} else {
		$x=0;
		$where2 = "AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= " AND `a`.`type` = 1";
		}
	} else {
	if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
		$where2 = "AND `a`.`type` = 1";
		} else {
		$x=0;
		$where2 = "AND (";
		foreach($curr_viewed as $grp) {
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= " AND `a`.`type` = 1";
		}
	}

										// 2/21/12
	$query = "SELECT *, `t`.`id` AS `the_ticket_id` FROM `$GLOBALS[mysql_prefix]ticket` `t`
	 		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `t`.`id` = `a`.`resource_id`
			WHERE `t`.`_by` <> {$me} AND `t`.`status` = {$GLOBALS['STATUS_OPEN']} $where2 ORDER BY `t`.`id` DESC LIMIT 1";		// broadcasts
	$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;				// 2/10/12
	$row = (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

	$the_tick_id = ($row)? $row['the_ticket_id'] : "0";		// 2/21/12

							// position updates?

if(!isset($curr_viewed)) {
	if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
		$where2 = "AND `a`.`type` = 2";
		} else {
		$x=0;
		$where2 = "AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= " AND `a`.`type` = 2";
		}
	} else {
	if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
		$where2 = " AND `a`.`type` = 2";
		} else {
		$x=0;
		$where2 = " AND (";
		foreach($curr_viewed as $grp) {
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= " AND `a`.`type` = 2";
		}
	}

$query = "SELECT *, `r`.`id` AS `the_responder_id` FROM `$GLOBALS[mysql_prefix]responder` `r`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `r`.`id` = `a`.`resource_id`
		WHERE  `callsign` > '' AND (`aprs` = 1 OR  `instam` = 1 OR  `locatea` = 1 OR  `gtrack` = 1 OR  `glat` = 1 ) $where2 ORDER BY `r`.`updated` DESC LIMIT 1";
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;			// 2/10/12
$row = (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;
//	Changed 8/3/12
if ($row ) {	//	Latest unit Status update written by current user.
	$_SESSION['unit_flag_1'] = $row['the_responder_id'];			// 2/21/12
//	$_SESSION['unit_flag_2'] = $me;		// 6/11/10
	} else {				// latest unit status updates written by others
	if(!isset($curr_viewed)) {
		if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = "AND `a`.`type` = 2";
			} else {
			$x=0;
			$where2 = " AND (";
			foreach($al_groups as $grp) {
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= " AND `a`.`type` = 2";
			}
		} else {
		if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = " AND `a`.`type` = 2";
			} else {
			$x=0;
			$where2 = " AND (";
			foreach($curr_viewed as $grp) {
				$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= " AND `a`.`type` = 2";
			}
		}

										// 2/21/12
	$query = "SELECT *, `r`.`id` AS `the_responder_id` FROM `$GLOBALS[mysql_prefix]responder` `r`
	LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `r`.`id` = `a`.`resource_id`
	WHERE `r`.`user_id` != {$me} $where2 ORDER BY `r`.`updated` DESC LIMIT 1";		// get most recent
	$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
	$row =  (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;
	}

if ($row) {
	$_SESSION['unit_flag_1'] = $row['the_responder_id'];			// 2/21/12
//	$_SESSION['unit_flag_2'] = $me;		// 6/11/10
	}

						//	9/10/13 Most recent status updates
if(!isset($curr_viewed)) {
	if(count($al_groups) == 0) {
		$where2 = " AND `a`.`type` = 2";
		} else {
		$x=0;
		$where2 = " AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= " AND `a`.`type` = 2";
		}
	} else {
	if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.
		$where2 = " AND `a`.`type` = 2";
		} else {
		$x=0;
		$where2 = " AND (";
		foreach($curr_viewed as $grp) {
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= " AND `a`.`type` = 2";
		}
	}

$query = "SELECT *, `r`.`id` AS `the_responder_id` FROM `$GLOBALS[mysql_prefix]responder` `r`
LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `r`.`id` = `a`.`resource_id`
WHERE `r`.`user_id` != {$me} $where2 ORDER BY `r`.`status_updated` DESC LIMIT 1";		// get most recent
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;
$row2 =  (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

if ($row2) {		//	9/10/13
	$status_updated = $row2['the_responder_id'];
	$status_updated_time = $row2['status_updated'];
	$status_updated_time = $row2['un_status_id'];
	}

						// 1/21/11 - get most recent dispatch

if(!isset($curr_viewed)) {
	if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
		$where2 = " AND `a`.`type` = 1";
		} else {
		$x=0;
		$where2 = " AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= " AND `a`.`type` = 1";
		}
	} else {
	if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
		$where2 = " AND `a`.`type` = 1";
		} else {
		$x=0;
		$where2 = " AND (";
		foreach($curr_viewed as $grp) {
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		$where2 .= " AND `a`.`type` = 1";
		}
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` `as`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON `as`.`ticket_id` = `t`.`id`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `t`.`id` = `a`.`resource_id`
		WHERE `as`.`user_id` != {$me} $where2 ORDER BY `as`.`as_of` DESC LIMIT 1";		// get most recent
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$assign_row = (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

// 2/25/12 - AS

$query = "SELECT `updated` FROM `$GLOBALS[mysql_prefix]action` WHERE `updated` = ( SELECT MAX(`updated`) FROM `$GLOBALS[mysql_prefix]action` ) LIMIT 1";
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$act_row =  (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$query = "SELECT `updated` FROM `$GLOBALS[mysql_prefix]patient` WHERE `updated` = ( SELECT MAX(`updated`) FROM `$GLOBALS[mysql_prefix]patient` ) LIMIT 1";
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$pat_row =  (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `status` = 'Open'";	//	10/23/12
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$the_reqs = (mysql_affected_rows() > 0) ? mysql_num_rows($result) : "0";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `closed` = '' OR `closed` IS NULL";	//	10/23/12
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$the_reqs2 = (mysql_affected_rows() > 0) ? mysql_num_rows($result) : "0";

$the_act_id = ($act_row)? $act_row['updated'] : "0";		// action item
$the_pat_id = ($pat_row)? $pat_row['updated'] : "0";		// patient item

$the_unit_id = ($row)? $row['the_responder_id'] : "0";	//	10/23/12
$the_updated = ($row)? $row['updated'] : "0";

$status_updated = ($row2)? $row2['the_responder_id'] : "0";		//	9/10/13
$the_status =($row2)? $row2['un_status_id'] : "0";		//	9/10/13
$status_updated_time = ($row2)? $row2['status_updated'] : "0";		//	9/10/13

$the_dispatch_change = ($assign_row)? $assign_row['as_of']: "";

//	3/23/2015

//	ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `watch` INT(2) NOT NULL DEFAULT '0' COMMENT 'Used in on-scene-watch' AFTER `set_severity`;

$osw_str = get_variable('os_watch');
$osw_arr = explode ("/", $osw_str);			// p, n, r

function ck_int ($var) {
	return  preg_match('/^\d+$/', $var);
	}
function is_auth() {		// returns boolean
	global $osw_arr, $osw_str;
	return ( is_super() || ( ( is_admin() ) && ( count( $osw_arr ) > 3 ) && ( intval(trim($osw_arr[3])) == 1 ) ) ) ;
	}
function  is_ok() {
	global $osw_arr, $osw_str;
	return  ( ( ( count ($osw_arr) >= 3 ) && ( ck_int ($osw_arr[0] ) && ck_int ( $osw_arr[1] && ck_int ($osw_arr[2] ) ) ) ) ) ;
	}

function  is_empty() {
	global $osw_arr, $osw_str;
	return  ( ( $osw_str == "//" ) || ( $osw_str == "0/0/0" ) || ( $osw_str == "" ) );
	}

$query = array ("", "", "");
$now = ( time() - 30 );

$watch_val = 0;					// default

if ( ( ! ( is_empty() ) ) && ( is_auth() ) && ( is_ok() ) ) {
//	snap(basename(__FILE__), __LINE__);

	$limit = 1;				// following sql is a clone of os_watch.php - differentiated by $limit value

	$query_core = "SELECT `t`.`severity`, `t`.`scope`, `t`.`status` AS `tickstatus`, CONCAT_WS(' ', `t`.`street`, `t`.`city`, `t`.`state`) AS `tickaddr`, `y`.`type`, `on_scene`, `handle`, `contact_via`, `r`.`callsign` AS `unit_call`, `r`.`id` AS `unitid`, `t`.`id` AS `tickid`, `u`.`expires`
			FROM `$GLOBALS[mysql_prefix]assigns` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	`t` 	ON (`a`.`ticket_id` 	= `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	`r` 	ON (`a`.`responder_id` 	= `r`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types`	`y` 	ON (`t`.`in_types_id` 	= `y`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user`		`u` 	ON (`u`.`responder_id` 	= `a`.`responder_id`)
			WHERE ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
			AND ((`on_scene` IS  NOT NULL) AND  (DATE_FORMAT(`on_scene`,'%y') <> '00')) ";

	$query [0] = "{$query_core} AND `severity` <> {$GLOBALS['SEVERITY_NORMAL']} LIMIT {$limit}";	/*	PRIORITIES medium and high on-scene:		*/

	$query [1] = "{$query_core} AND `severity` = {$GLOBALS['SEVERITY_NORMAL']} LIMIT {$limit}";		/*	PRIORITY normal  on-scene:		*/

	$query [2] = 																					/*	ROUTINES not on-scene:		*/
			"SELECT * , `t`.`id` AS `tickid`, CONCAT_WS(' ', `t`.`street`, `t`.`city`, `t`.`state`) AS `tickaddr`, 999999 AS `handle`
				FROM `$GLOBALS[mysql_prefix]ticket` `t`
				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` ON (`t`.`in_types_id` = `y`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON (`a`.`ticket_id` = `t`.`id`)
			WHERE ( `t`.`status` = {$GLOBALS['STATUS_OPEN']} OR `t`.`status` = {$GLOBALS['STATUS_SCHEDULED']} )
			AND ( (`a`.`on_scene` IS NULL ) OR ( DATE_FORMAT(`a`.`on_scene`,'%y') = '00') )
			AND `y`.`watch` = 1
			ORDER BY `t`.`severity` DESC, `t`.`scope` ASC LIMIT {$limit} ";

	if ( ! (array_key_exists ( "osw_run_at", $_SESSION ) ) ) { $_SESSION['osw_run_at'] = array ( $now-1, $now-1, $now-1 ) ; }		// initialize routines, normals, priorities
																																	// ensure operation at startup
	for ($j=0; $j< 3; $j++) {
		if ($now >= $_SESSION['osw_run_at'][$j]) {						// seconds

			$result = mysql_query($query[$j]) or do_error($query[$j], 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$temp = mysql_num_rows($result);
			if ( $temp > 0 ) {
				$watch_val = 1;					// this does the work
				break;							// used ONLY for get_latest_id.php
				}
			}
		}
	}			// end is_super() if/else

$the_hash = md5($the_chat_id . $the_tick_id . $the_unit_id . $the_updated . $the_dispatch_change . $the_act_id . $the_pat_id . $the_reqs . $the_reqs2 . $status_updated . $the_status . $status_updated_time . $watch_val . $osw_str);	//	10/23/12
$ret_arr = array ($the_chat_id, $the_tick_id, $the_unit_id, $the_updated, $the_dispatch_change, $the_act_id, $the_pat_id, $the_reqs, $the_reqs2, $status_updated, $the_status, $status_updated_time, $watch_val, $osw_str, $the_hash);	//	10/23/12
// snap( __LINE__, $ret_arr[12] );

// 3/23/2015
get_current();								// update remotes position - 5/30/2013
print json_encode($ret_arr);				// 1/6/11
exit();
?>