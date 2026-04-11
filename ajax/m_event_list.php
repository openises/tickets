<?php
require_once '../incs/functions.inc.php';
@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
    exit();
    } */
$member = (isset($_GET['member'])) ? sanitize_int($_GET['member']) : 0;
$internet = ((array_key_exists('internet', $_SESSION)) && ($_SESSION['internet'] == true)) ? true: false;
$istest = false;
$output_arr = array();
$num_rows = 0;

function eve_list($member) {
    global $istest, $internet, $num_rows;
    $time = microtime(true); // Gets microseconds
    $eols = array ("\r\n", "\n", "\r");        // all flavors of eol

    // initiate arrays
    $eve_row = array();

    // search rules
    $query = "SELECT *, `ev`.`id` AS `evid`,
        `a`.`id` AS `id`,
        `a`.`member_id` AS `mid`,
        `ev`.`event_name` AS `event_name`,
        `ev`.`description` AS `description`,
        `a`.`start` AS `start`,
        `a`.`end` AS `end`
        FROM `$GLOBALS[mysql_prefix]allocations` `a`
        LEFT JOIN `$GLOBALS[mysql_prefix]events` `ev` ON ( `a`.`skill_id` = ev.id )
        WHERE `a`.`member_id` = ? AND `a`.`skill_type` = '6'
        ORDER BY `start`";

    $result = db_query($query, [$member]);
    $the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
    $num_rows = $result->num_rows;
//    Major While
    if($num_rows == 0) {
        $eve_row[0][0] = 0;
        } else {
        $temp  = (string) ( round((microtime(true) - $time), 3));
        $i = 1;
        while ($row = stripslashes_deep($result->fetch_assoc())) {
            $start = $row['start'];
            $end = $row['end'];
            $start = do_datestring(safe_strtotime($start));
            $end = do_datestring(safe_strtotime($end));
            $eve_row[$i][0] = $row['id'];
            $eve_row[$i][1] = safe_htmlentities($row['event_name'], ENT_QUOTES);
            $eve_row[$i][2] = safe_htmlentities($row['description'], ENT_QUOTES);
            $eve_row[$i][3] = safe_htmlentities($start, ENT_QUOTES);
            $eve_row[$i][4] = safe_htmlentities($end, ENT_QUOTES);
            $i++;
            }                // end tickets while ($row = ...)
        }
    return $eve_row;
    }
$output_arr = eve_list($member);
print json_encode($output_arr);
exit();
?>