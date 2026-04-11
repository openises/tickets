<?php
/*
9/10/13 - new file, lists tickets that are assigned to the mobile user
*/
require_once '../incs/functions.inc.php';
function br2nl($input) {
    return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
    }


$ticket_id = sanitize_int((isset($_GET['ticket_id'])) ? $_GET['ticket_id'] : 0);
$responder_id = sanitize_int((isset($_GET['responder_id'])) ? $_GET['responder_id'] : 0);
$facility_id = sanitize_int((isset($_GET['facility_id'])) ? $_GET['facility_id'] : 0);
$type = sanitize_int((isset($_GET['type'])) ? $_GET['type'] : 0);
$portaluser = sanitize_int((isset($_GET['portaluser'])) ? $_GET['portaluser'] : 0);

if($portaluser!=0) {
    $query = "SELECT *,
        `fx`.`id` AS fx_id,
        `f`.`id` AS file_id
        FROM `$GLOBALS[mysql_prefix]files_x` `fx`
        LEFT JOIN `$GLOBALS[mysql_prefix]files` `f`    ON (`f`.`id` = `fx`.`file_id`)
        WHERE `fx`.`user_id` = ? ORDER BY `f`.`id` ASC";
    $result = db_query($query, [$portaluser]) or do_error('', 'mysql query failed', '', basename( __FILE__), __LINE__);
    } elseif($ticket_id != 0) {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `ticket_id` = ? ORDER BY `id` ASC";
    $result = db_query($query, [$ticket_id]) or do_error('', 'mysql query failed', '', basename( __FILE__), __LINE__);
    } elseif($responder_id != 0) {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `responder_id` = ? ORDER BY `id` ASC";
    $result = db_query($query, [$responder_id]) or do_error('', 'mysql query failed', '', basename( __FILE__), __LINE__);
    } elseif($facility_id != 0) {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `facility_id` = ? ORDER BY `id` ASC";
    $result = db_query($query, [$facility_id]) or do_error('', 'mysql query failed', '', basename( __FILE__), __LINE__);
    } elseif($type != 0) {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `type` = ? ORDER BY `id` ASC";
    $result = db_query($query, [$type]) or do_error('', 'mysql query failed', '', basename( __FILE__), __LINE__);
    } else {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` ORDER BY `id` ASC";
    $result = db_query($query) or do_error('', 'mysql query failed', '', basename( __FILE__), __LINE__);
    }

$bgcolor = "#EEEEEE";
if (($result) && ($result->num_rows >=1)) {
    $print = "<TABLE style='width: 100%;'>";
    $print .= "<TR style='width: 100%; font-weight: bold; background-color: #707070;'><TD style='color: #FFFFFF;'>File Name</TD><TD style='color: #FFFFFF;'>Uploaded By</TD><TD style='color: #FFFFFF;'>Date</TD></TR>";
    while ($row = stripslashes_deep($result->fetch_assoc())){
        $print .= "<TR>";
        $filename = e($row['filename']);
        $origfilename = e($row['orig_filename']);
        $filetype = e($row['filetype']);
        $title = e($row['title']);
        $print .= "<TD><A HREF='./ajax/download.php?filename=" . $filename . "&origname=" . $origfilename . "&type=" . $filetype . "'>" . $title . "</A></TD>";
        $print .= "<TD>" . get_owner($row['_by']) . "</TD>";
        $print .= "<TD>" . format_date_2(safe_strtotime($row['_on'])) . "</TD>";
        $print .= "</TR>";
        $bgcolor = ($bgcolor == "#EEEEEE") ? "#FEFEFE" : "#EEEEEE";
        }                // end while
        $print .= "</TABLE>";
    } else {
    $print = "<TABLE style='width: 100%;'>";
    $print .= "<TR class='spacer'><TD COLSPAN=99 class='spacer'>&nbsp;</TD></TR>";
    $print .="<TR style='width: 100%;'><TD style='width: 100%; text-align: center;'>No Files</TD></TR></TABLE>";
    }    //    end else

print $print;
exit();
?>