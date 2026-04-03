<?php
require_once('../incs/functions.inc.php');
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

function veh_list($member) {
    global $istest, $internet, $num_rows;
    $time = microtime(true); // Gets microseconds
    $eols = array ("\r\n", "\n", "\r");        // all flavors of eol

    // initiate arrays
    $veh_row = array();

    // search rules

    $query = "SELECT *, `v`.`id` AS `vid`,
        `a`.`id` AS `id`,
        `a`.`member_id` AS `mid`,
        `v`.`make` AS `make`,
        `v`.`model` AS `model`,
        `v`.`year` AS `year`,
        `v`.`color` AS `color`,
        `v`.`regno` AS `regno`,
        `t`.`name` AS `type_name`
        FROM `$GLOBALS[mysql_prefix]allocations` `a`
        LEFT JOIN `$GLOBALS[mysql_prefix]vehicles` `v` ON ( `a`.`skill_id` = v.id )
        LEFT JOIN `$GLOBALS[mysql_prefix]vehicle_types` `t` ON ( `v`.`type` = `t`.`id` )
        WHERE `a`.`member_id` = ? AND `a`.`skill_type` = '4'
        ORDER BY `v`.`regno`";

    $result = db_query($query, [$member]);
    $the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
    $num_rows = $result->num_rows;
//    Major While
    if($num_rows == 0) {
        $veh_row[0][0] = 0;
        } else {
        $temp  = (string) ( round((microtime(true) - $time), 3));
        $i = 1;
        while ($row = stripslashes_deep($result->fetch_assoc()))     {
            $veh_row[$i][0] = $row['id'];
            $veh_row[$i][1] = safe_htmlentities($row['make'], ENT_QUOTES);
            $veh_row[$i][2] = safe_htmlentities($row['model'], ENT_QUOTES);
            $veh_row[$i][3] = safe_htmlentities($row['year'], ENT_QUOTES);
            $veh_row[$i][4] = safe_htmlentities($row['color'], ENT_QUOTES);
            $veh_row[$i][5] = safe_htmlentities($row['regno'], ENT_QUOTES);
            $veh_row[$i][6] = safe_htmlentities($row['type_name'], ENT_QUOTES);
            $i++;
            }                // end tickets while ($row = ...)
        }
    return $veh_row;
    }
$output_arr = veh_list($member);

print json_encode($output_arr);
exit();
?>