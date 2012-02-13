<?php
/*
1/15/09 initial release
1/20/09 added $frm_tick, $frm_unit
1/21/09 added frm_id as info param
2/1/09 corrections to logging
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/28/10 frm_u2fenr and frm_u2farr added
9/1/10 set unit 'updated' time
*/
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);		//7/28/10

$vals_ary = explode("%", $_POST['frm_vals']);		// example: "frm_id=17&frm_vals=frm_dispatched%frm_responding%frm_clear"

$frm_id = $_POST['frm_id'];
$frm_tick = $_POST['frm_tick'];
$frm_unit = $_POST['frm_unit'];

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$date_part="";
if (in_array("frm_dispatched",$vals_ary))	{				// accommodate multiples
	$date_part .= "`dispatched` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_DISP'], 	$frm_tick, $frm_unit, $frm_id);	// 1/21/09
	}
if (in_array("frm_responding",$vals_ary))	{
	$date_part .= "`responding` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_RESP'], 	$frm_tick, $frm_unit, $frm_id);
	}
if (in_array("frm_on_scene",$vals_ary))		{
	$date_part .= "`on_scene` = ". quote_smart($now) . ", ";				// 2/1/09
	do_log($GLOBALS['LOG_CALL_ONSCN'], 	$frm_tick, $frm_unit, $frm_id);
	}
if (in_array("frm_clear",$vals_ary))		{
	$date_part .= "`clear` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_CLR'], 	$frm_tick, $frm_unit, $frm_id);
	}

if (in_array("frm_u2fenr",$vals_ary))		{		// 8/28/10
	$date_part .= "`u2fenr` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_U2FENR'], 	$frm_tick, $frm_unit, $frm_id);
	}

if (in_array("frm_u2farr",$vals_ary))		{
	$date_part .= "`u2farr` = " . quote_smart($now) . ", ";
	do_log($GLOBALS['LOG_CALL_U2FARR'], 	$frm_tick, $frm_unit, $frm_id);
	}


$date_part .= substr($date_part, 0, -2);							//drop terminal separator pair

$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `as_of`= " . quote_smart($now) .", " . $date_part ;
$query .=  " WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";
//snap(basename( __FILE__), $query);
$result	= mysql_query($query) or do_error($query,'',mysql_error(), basename( __FILE__), __LINE__);

set_u_updated ($_POST['frm_id']); 								// set unit 'updated' time - 9/1/10
?>