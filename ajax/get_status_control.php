<?php
require_once('../incs/functions.inc.php');
set_time_limit(0);
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
	exit();
	} */
$id = $_GET['responder_id'];
$initial =(array_key_exists('initial', $_GET)) ? TRUE : FALSE;
$ret_arr = array();
$status_vals = array();											// build array of $status_vals
$status_bg = array();
$status_fg = array();
$status_vals[''] = $status_vals['0']="TBD";
$dis = (is_guest())? " DISABLED": "";	

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	$status_hide[$temp] = $row_st['hide'];
	$status_bg[$temp] = $row_st['bg_color'];
	$status_fg[$temp] = $row_st['text_color'];
	}

unset($result_st);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;											// 2/1/10, 3/15/10, 6/10/11
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result)); 
$init_bg_color = (valid_status($row['un_status_id'])) ? $status_bg[$row['un_status_id']] : "red";
$init_txt_color = (valid_status($row['un_status_id'])) ? $status_fg[$row['un_status_id']] : "#FFFFFF";
if($initial) {
	$status_cont = "<SELECT CLASS='sit text' id='frm_status_id_u_" . $row['id'] . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color}; width: 120px;' onFocus='add_to_select(" . $row['id'] . ");'>";
	$status_cont .= "<OPTION VALUE=" . $row['un_status_id'] . ">" . $status_vals[$row['un_status_id']] . "</OPTION>";
	$status_cont .= "</SELECT>";
//	$status_cont .= "<img id='lock_" . $row['id'] . "' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = ' get_status_selector(" . $row['id'] . "); add_to_select(" . $row['id'] . ")'>";
	} else {
	$status_cont = get_status_sel($row['id'], $row['un_status_id'], "u");
	}
//$status = (valid_status($row['un_status_id'])) ? get_status_sel($row['id'], $row['un_status_id'], "u") : "Status Error";		// status
$status = (valid_status($row['un_status_id'])) ? $status_cont : "Status Error";		// status
$ret_arr[] = $status;

//dump($ret_arr);
print json_encode($ret_arr);
exit();
?>