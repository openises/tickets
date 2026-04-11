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

function capab_list($member) {
    global $istest, $internet, $num_rows;
    $time = microtime(true); // Gets microseconds
    $eols = array ("\r\n", "\n", "\r");        // all flavors of eol

    // initiate arrays
    $capab_row = array();

    // search rules
    $query = "SELECT *, `ct`.`id` AS `ctid`,
        `a`.`id` AS `id`,
        `a`.`member_id` AS `mid`,
        `a`.`_on` AS `updated`,
        `ct`.`name` AS `name`,
        `ct`.`description` AS `description`
        FROM `$GLOBALS[mysql_prefix]allocations` `a`
        LEFT JOIN `$GLOBALS[mysql_prefix]capability_types` `ct` ON ( `a`.`skill_id` = ct.id )
        WHERE `a`.`member_id` = ? AND `a`.`skill_type` = '2'
        ORDER BY `a`.`completed`";

    $result = db_query($query, [$member]);
    $the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
    $num_rows = $result->num_rows;
//    Major While
    if($num_rows == 0) {
        $capab_row[0][0] = 0;
        } else {
        $temp  = (string) ( round((microtime(true) - $time), 3));
        $i = 1;
        while ($row = stripslashes_deep($result->fetch_assoc()))     {
            $capab_row[$i][0] = $row['id'];
            $capab_row[$i][1] = safe_htmlentities($row['name'], ENT_QUOTES);
            $capab_row[$i][2] = safe_htmlentities($row['description'], ENT_QUOTES);
            $capab_row[$i][3] = safe_htmlentities($row['updated'], ENT_QUOTES);
            $i++;
            }                // end tickets while ($row = ...)
        }
    return $capab_row;
    }
$output_arr = capab_list($member);

print json_encode($output_arr);
exit();
?>