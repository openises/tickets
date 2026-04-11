<?php
/*
9/10/13 - new file, lists tickets that are assigned to the mobile user
*/
require_once('../incs/functions.inc.php');
function br2nl($input) {
    return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
    }
$sortby = (!(array_key_exists('sort', $_GET))) ? "name" : sanitize_string($_GET['sort']);
$sortdir = (!(array_key_exists('dir', $_GET))) ? "ASC" : sanitize_string($_GET['dir']);
$ticket_id = (isset($_GET['ticket_id'])) ? sanitize_int($_GET['ticket_id']) : 0;
$responder_id = (isset($_GET['responder_id'])) ? sanitize_int($_GET['responder_id']) : 0;
$facility_id = (isset($_GET['facility_id'])) ? sanitize_int($_GET['facility_id']) : 0;
$mi_id = (isset($_GET['mi_id'])) ? sanitize_int($_GET['mi_id']) : 0;
$type = (isset($_GET['type'])) ? sanitize_int($_GET['type']) : 0;
$portaluser = (isset($_GET['portaluser'])) ? sanitize_int($_GET['portaluser']) : 0;

function get_mi($id) {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `id` = ?";
    $result = db_query($query, [$id]);
    $row = $result ? stripslashes_deep($result->fetch_assoc()) : null;
    return $row['name'];
    }

function subval_sort($a,$subkey, $dd) {
    foreach($a as $k=>$v) {
        $b[$k] = strtolower($v[$subkey]);
        }
    if($dd == 1) {
        asort($b);
        } else {
        arsort($b);
        }
    foreach($b as $key=>$val) {
        $c[] = $a[$key];
        }
    return $c;
    }

if($portaluser!=0) {
    $query = "SELECT *,
        `fx`.`id` AS fx_id,
        `f`.`id` AS file_id
        FROM `$GLOBALS[mysql_prefix]files_x` `fx`
        LEFT JOIN `$GLOBALS[mysql_prefix]files` `f`    ON (`f`.`id` = `fx`.`file_id`)
        WHERE `fx`.`user_id` = ? ORDER BY `f`.`id` ASC";
    $result = db_query($query, [$portaluser]);
    } else {
    $params = [];
    if($ticket_id != 0) {
        $where = " WHERE `ticket_id` = ? OR (`ticket_id` = 0 AND `responder_id` = 0 AND `facility_id` = 0 AND `mi_id` = 0 AND `type` = ?)";
        $params[] = $ticket_id;
        $params[] = $type;
        } elseif($responder_id != 0) {
        $where = " WHERE `responder_id` = ? OR (`ticket_id` = 0 AND `responder_id` = 0 AND `facility_id` = 0 AND `mi_id` = 0 AND `type` = ?)";
        $params[] = $responder_id;
        $params[] = $type;
        } elseif($facility_id != 0) {
        $where = " WHERE `facility_id` = ? OR (`ticket_id` = 0 AND `responder_id` = 0 AND `facility_id` = 0 AND `mi_id` = 0 AND `type` = ?)";
        $params[] = $facility_id;
        $params[] = $type;
        } elseif($mi_id != 0) {
        $where = " WHERE `mi_id` = ? OR (`ticket_id` = 0 AND `responder_id` = 0 AND `facility_id` = 0 AND `mi_id` = 0 AND `type` = ?)";
        $params[] = $mi_id;
        $params[] = $type;
        } elseif($type != 2) {
        $where = " WHERE `type` = ? OR `type` = 0";
        $params[] = $type;
        } elseif($type == 2) {
        $where = " WHERE `type` = ?";
        $params[] = $type;
        } else {
        $where = "";
        }

    $query = "SELECT *,
        `id` AS `file_id`
        FROM `$GLOBALS[mysql_prefix]files`" . $where . " ORDER BY `id` ASC";
    $result = db_query($query, $params);
    }

$num_rows = $result->num_rows;

if (($result) && ($num_rows > 0)) {
    $i=1;
    while ($row = stripslashes_deep($result->fetch_assoc())){
        if(($row['ticket_id'] ==0 && $row['responder_id'] == 0 && $row['facility_id'] == 0 && $row['mi_id'] == 0) || ($row['filetype'] == 1)) {
            $theflag = "General";
            } elseif(($row['ticket_id'] ==0 && $row['responder_id'] == 0 && $row['facility_id'] == 0 && $row['mi_id'] == 0) || ($row['filetype'] == 2)) {
            $theflag = "Portal";
            } elseif($row['ticket_id'] > 0) {
            $theflag = "Tick {$row['ticket_id']}";
            } elseif($row['responder_id'] > 0) {
            $theflag = "Resp {$row['responder_id']}";
            } elseif($row['facility_id'] > 0) {
            $theflag = "Fac {$row['facility_id']}";
            } elseif($row['mi_id'] > 0) {
            $theflag = "MI " . shorten(get_mi($row['mi_id']), 10);
            } else {
            $theflag = "";
            }
        $ret_arr[$i][0] = $row['filename'];
        $ret_arr[$i][1] = $row['orig_filename'];
        $ret_arr[$i][2] = $row['filetype'];
        $ret_arr[$i][3] = $row['title'];
        $ret_arr[$i][4] = get_owner($row['_by']);
        $ret_arr[$i][5] = format_date_2(safe_strtotime($row['_on']));
        $ret_arr[$i][6] = $row['file_id'];
        $ret_arr[$i][7] = $theflag;
        $i++;
        }                // end while
    } else {
    $ret_arr[0][0] = 0;
    }    //    end else


$output_arr = $ret_arr;
if($sortdir == "ASC") {
    $dd = 1;
    } else {
    $dd = 0;
    }

switch($sortby) {
    case 'name':
        $sortval = 0;
        break;
    case 'owner':
        $sortval = 4;
        break;
    case 'updated':
        $sortval = 5;
        break;
    default:
        $sortval = 0;
    }

if($num_rows > 0) {
    $the_arr = subval_sort($output_arr, $sortval, $dd);
    $the_output = array();
    $z=1;
    foreach($the_arr as $val) {
        $the_output[$z] = $val;
        $z++;
        }
    print json_encode($the_output);
    } else {
    $output_arr[0][0] = 0;
    print json_encode($output_arr);
    }

exit();
?>