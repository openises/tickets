<?php
error_reporting(E_ALL);
@session_start();
session_write_close();
require_once('../incs/functions.inc.php');
require_once('../incs/functions_major.inc.php');
$id = sanitize_int($_GET['id']);
$the_width = '98%';
$query = "SELECT *,
    `problemstart` AS `problemstart`,
    `problemend` AS `problemend`,
    `booked_date` AS `booked_date`,
    `date` AS `date`,
    `t`.`updated` AS updated,
    `t`.`description` AS `tick_descr`,
    `t`.`lat` AS `lat`,
    `t`.`lng` AS `lng`,
    `t`.`_by` AS `call_taker`,
    `t`.`street` AS `tick_street`,
    `t`.`city` AS `tick_city`,
    `t`.`state` AS `tick_state`,
    `f`.`name` AS `fac_name`,
    `rf`.`name` AS `rec_fac_name`,
    `rf`.`street` AS `rec_fac_street`,
    `rf`.`city` AS `rec_fac_city`,
    `rf`.`state` AS `rec_fac_state`,
    `rf`.`lat` AS `rf_lat`,
    `rf`.`lng` AS `rf_lng`,
    `f`.`lat` AS `fac_lat`,
    `f`.`lng` AS `fac_lng` FROM `{$GLOBALS['mysql_prefix']}ticket` `t`
    LEFT JOIN `{$GLOBALS['mysql_prefix']}in_types` `ty` ON (`t`.`in_types_id` = `ty`.`id`)
    LEFT JOIN `{$GLOBALS['mysql_prefix']}facilities` `f` ON (`f`.`id` = `t`.`facility`)
    LEFT JOIN `{$GLOBALS['mysql_prefix']}facilities` `rf` ON (`rf`.`id` = `t`.`rec_facility`)
    WHERE `t`.`id`= ? LIMIT 1";
$result = db_query($query, [$id]);
$row_ticket = $result ? stripslashes_deep($result->fetch_array()) : null;
$ret_arr = array();
$ret_arr[0] = do_ticket_wm($row_ticket, "100%", false, false);
$ret_arr[1] = "<SPAN id='edit_button' roll='button' aria-label='Edit Incident' CLASS='plain text' style='width: 100px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='open_edit_window(" . intval($id) . ");'><SPAN STYLE='float: left;'>" . get_text('Edit') . "</SPAN><IMG STYLE='float: right;' SRC='./images/edit_small.png' BORDER=0></SPAN>";
print json_encode($ret_arr);
exit();
?>
