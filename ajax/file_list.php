<?php
/*
9/10/13 - new file, lists tickets that are assigned to the mobile user
*/
require_once('../incs/functions.inc.php');
function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}


$ticket_id = (isset($_GET['ticket_id'])) ? $_GET['ticket_id'] : 0;
$responder_id = (isset($_GET['responder_id'])) ? $_GET['responder_id'] : 0;
$facility_id = (isset($_GET['facility_id'])) ? $_GET['facility_id'] : 0;
$type = (isset($_GET['type'])) ? $_GET['type'] : 0;
$portaluser = (isset($_GET['portaluser'])) ? $_GET['portaluser'] : 0;

if($ticket_id != 0) {
	$where = " WHERE `ticket_id` = " . $ticket_id;
	} elseif($responder_id != 0) {
	$where = " WHERE `responder_id` = " . $responder_id;
	} elseif($facility_id != 0) {
	$where = " WHERE `facility_id` = " . $facility_id;
	} elseif($type != 0) {
	$where = " WHERE `type` = " . $type;
	} else {
	$where = "";
	}

if($portaluser!=0) {
	$query = "SELECT *, 
		`fx`.`id` AS fx_id, 
		`f`.`id` AS file_id
		FROM `$GLOBALS[mysql_prefix]files_x` `fx`  
		LEFT JOIN `$GLOBALS[mysql_prefix]files` `f`	ON (`f`.`id` = `fx`.`file_id`)
		WHERE `fx`.`user_id` = " . $portaluser . " ORDER BY `f`.`id` ASC"; 
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	} else {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files`" . $where . " ORDER BY `id` ASC";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	}
$bgcolor = "#EEEEEE";
if (($result) && (mysql_num_rows($result) >=1)) {
	$print = "<TABLE style='width: 100%;'>";
	$print .= "<TR style='width: 100%; font-weight: bold; background-color: #707070;'><TD style='color: #FFFFFF;'>File Name</TD><TD style='color: #FFFFFF;'>Uploaded By</TD><TD style='color: #FFFFFF;'>Date</TD></TR>";		
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
		$print .= "<TR>";
		$filename = $row['filename'];
		$origfilename = $row['orig_filename'];
		$filetype = $row['filetype'];
		$title = $row['title'];
		$print .= "<TD><A HREF='./ajax/download.php?filename=" . $filename . "&origname=" . $origfilename . "&type=" . $filetype . "'>" . $row['title'] . "</A></TD>";
		$print .= "<TD>" . get_owner($row['_by']) . "</TD>";
		$print .= "<TD>" . format_date_2(strtotime($row['_on'])) . "</TD>";		
		$print .= "</TR>";
		$bgcolor = ($bgcolor == "#EEEEEE") ? "#FEFEFE" : "#EEEEEE";
		}				// end while
		$print .= "</TABLE>";
	} else {
	$print = "<TABLE style='width: 100%;'>";
	$print .= "<TR class='spacer'><TD COLSPAN=99 class='spacer'>&nbsp;</TD></TR>";			
	$print .="<TR style='width: 100%;'><TD style='width: 100%; text-align: center;'>No Files</TD></TR></TABLE>";
	}	//	end else
	
print $print;
exit();
?>