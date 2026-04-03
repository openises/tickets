<?php
/*
list_waste_messages.php - gets messages from messages wastebasket table for display in message window and ticket view and unit view
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');
require_once('../incs/html2text.php');
$filter = "";

$cols_width = array(30,30,120,120,150,250,90,90);
$columns_arr = explode(',', get_msg_variable('columns'));

$the_win_width = 60;
$the_screen = ((isset($_GET['screen'])) && ($_GET['screen'] == 'msg_win')) ? 1 : 0;
$ret_arr = array();
$counter = 0;

if((in_array('1', $columns_arr)) && ((isset($_GET['screen'])) && ($_GET['screen'] == 'msg_win'))) { $the_win_width = $the_win_width + $cols_width[0];}
if(in_array('2', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[1];}
if(in_array('3', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[2];}
if(in_array('4', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[3];}
if(in_array('5', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[4];}
if(in_array('6', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[5];}
if(in_array('7', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[6];}
if(in_array('8', $columns_arr)) { $the_win_width = $the_win_width + $cols_width[7];}

function br2nl($input) {
    return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
    }

$ticket_id = (isset($_GET['ticket_id'])) ? sanitize_int($_GET['ticket_id']) : null;
$responder_id = (isset($_GET['responder_id'])) ? sanitize_int($_GET['responder_id']) : null;
$filter = (isset($_GET['filter'])) ? sanitize_string($_GET['filter']) : "";
$sort = (isset($_GET['sort'])) ? sanitize_string($_GET['sort']) : null;
$way = (isset($_GET['way'])) ? sanitize_string($_GET['way']) : null;
$columns = (isset($_GET['columns'])) ? explode("," ,sanitize_string($_GET['columns'])) : explode(",", get_msg_variable('columns')) ;

$where = "";
$params = [];

if(isset($ticket_id)) {
    $where = "WHERE `ticket_id` = ?";
    $params[] = $ticket_id;
    } elseif(isset($responder_id)) {
    $where = "WHERE `resp_id` = ?";
    $params[] = $responder_id;
    }

if((isset($filter) && ($filter != "") && ((isset($ticket_id)) || (isset($responder_id))))) {
    $where = "WHERE `ticket_id` = ? AND ((`m`.`fromname` REGEXP ?) OR (`m`.`message` REGEXP ?) OR (`m`.`recipients` REGEXP ?) OR  (`m`.`subject` REGEXP ?))";
    $params = [$ticket_id, $filter, $filter, $filter, $filter];
    } elseif((isset($filter)) && ($filter != "") && ((!isset($ticket_id)) && (!isset($responder_id)))) {
    $where = "WHERE ((`m`.`fromname` REGEXP ?) OR (`m`.`message` REGEXP ?) OR (`m`.`recipients` REGEXP ?) OR  (`m`.`subject` REGEXP ?))";
    $params = [$filter, $filter, $filter, $filter];
    }

// Whitelist sort columns
$allowed_sorts = ['date', 'read_status', 'fromname', 'message', 'recipients', 'subject', 'msg_type', 'id', 'ticket_id'];
$order = "ORDER BY `date`";
if(isset($sort) && in_array($sort, $allowed_sorts)) {
    $order = "ORDER BY `" . $sort . "`";
}
$allowed_ways = ['ASC', 'DESC'];
$order2 = "DESC";
if(isset($way) && in_array(strtoupper($way), $allowed_ways)) {
    $order2 = strtoupper($way);
}
$actr=0;

$print = "<TABLE BORDER='0' ID='messages' style='width: " . $the_win_width . "px; max-height: 300px; padding: 10px;'>";
$query = "SELECT *,date AS `date`,_on AS `_on`,
        `m`.`id` AS `message_id`,
        `m`.`fromname` AS `fromname`,
        `m`.`message` AS `message`,
        `m`.`ticket_id` AS `ticket_id`,
        `m`.`message_id` AS `msg_id`,
        `m`.`msg_type` AS `msg_type`,
        `m`.`recipients` AS `recipients`,
        `m`.`subject` AS `subject`
        FROM `$GLOBALS[mysql_prefix]messages_bin` `m`
        {$where} {$order} {$order2}";
$result = db_query($query, $params);

if ($result->num_rows == 0) {                 // 8/6/08
    $ret_arr[$counter][0] = "No Messages";
    } else {
    while ($msg_row = stripslashes_deep($result->fetch_assoc())){
        $the_class = ($msg_row['read_status'] == 0) ? 0 : 1;
        $the_message_id = $msg_row['message_id'];
        if($msg_row['resp_id'] != "") {
            $the_responder = $msg_row['resp_id'];
            $resp_name = get_respondername($the_responder);
            } else {
            $resp_name = "NA";
            }
        $the_message = strip_tags($msg_row['message']);
        if($msg_row['recipients'] == null) {
            $respstring = $resp_name;
            } else {
            $responders = explode (" ", trim($msg_row['recipients']));    // space-separated list to array
            $sep = $respstring = "";
            for ($i=0 ;$i < count($responders);$i++) {                // build string of responder names
                $respstring .= $sep . $responders[$i];
                }
            }

        if ($msg_row['msg_type'] == 1) {
            $type_flag = "OE";
            $color = "background-color: blue; color: white;";
            } elseif ($msg_row['msg_type'] ==2) {
            $type_flag = "IE";
            $color = "background-color: white; color: blue;";
            } elseif ($msg_row['msg_type'] ==3) {
            $color = "background-color: orange; color: white;";
            $type_flag = "OS";
            } elseif (($msg_row['msg_type'] ==4) || ($msg_row['msg_type'] ==5) || ($msg_row['msg_type'] ==6)) {
            $color = "background-color: white; color: orange;";
            $type_flag = "IS";
            } else {
            $color = "";
            $type_flag = "?";
            }
        $long = (safe_strlen($the_message) > 100) ? "&#9660" : "";

        $fromname = ($msg_row['fromname'] != "") ? shorten($msg_row['fromname'], 80) : "TBA";
        $ret_arr[$counter][0] = $the_message_id;
        $ret_arr[$counter][1] = $msg_row['ticket_id'];
        $ret_arr[$counter][2] = $type_flag;
        $ret_arr[$counter][3] = $fromname;
        $ret_arr[$counter][4] = $respstring;
        $ret_arr[$counter][5] = stripslashes_deep(shorten($msg_row['subject'], 18));
        $ret_arr[$counter][6] = htmlentities(shorten($the_message, 2000));
        $ret_arr[$counter][7] = format_date_2(safe_strtotime($msg_row['date']));
        $ret_arr[$counter][8] = get_owner($msg_row['_by']);
        $ret_arr[$counter][9] = $the_class;
        $ret_arr[$counter][10] = $msg_row['id'];
        $counter++;
        } // end while
    }                // end else
print json_encode($ret_arr);
exit();
?>