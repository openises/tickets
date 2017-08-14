<?php
set_time_limit(0);
require_once('./incs/functions.inc.php');


function get_textnumbers($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `smsg_id` = '" . $id . "'";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret = $row['cellphone'];
		} else {
		$ret = 0;
		}
	return $ret;
	}
	
$recipients = $_GET['responders'];

$smsgs_arr = array_unique(explode(",", ($recipients)));
$cells_arr = array();
foreach($smsgs_arr as $val) {
	$responderNos = get_textnumbers($val);
	$respondNosArr = explode(",", ($responderNos));
	if(array_key_exists(1, $respondNosArr)) {
		foreach($respondNosArr as $val2) {
			$cells_arr[] = $val2;
			}
		} else {
		$cells_arr[] = $respondNosArr[0];
		}
	}

$numbers = implode(",", $cells_arr);
print $numbers . "<BR />";