<?php
/*
9/10/13 - new file, lists tickets that are assigned to the mobile user
*/
@session_start();
require_once('../../incs/functions.inc.php');
function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}

if($_GET['user_id'] != 0) {
	$the_user = $_GET['user_id'];
	} else{
	exit;
	}
	
$responder_id = (isset($_GET['responder_id'])) ? clean_string($_GET['responder_id']) : NULL;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = '" . $the_user . "' AND ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))"; 
$bgcolor = "#EEEEEE";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
if (mysql_num_rows($result) == 0) { 
	$print = "<TABLE style='width: 100%;'><TR style='width: 100%;'><TD style='width: 100%;'>No Current Assignments</TD></TR></TABLE>";
	} else {
	$print = "<TABLE style='width: 100%;'>";	
	$print .= "<TR class='heading' style='width: 100%; font-weight: bold; color: #FFFFFF; background-color: #707070;'><TD style='width: 30%;'>TICKET NAME</TD><TD style='width: 50%;'>DESCRIPTION</TD><TD style='width: 20%;'>PROBLEMSTART</TD></TR>";		
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = '" . $row['ticket_id'] . "' AND `status` = " . $GLOBALS['STATUS_OPEN']; 
		$result2 = mysql_query($query2) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))){
			$print .= "<TR title='Click for detail' style='width: 100%; cursor: pointer; background-color: " . $bgcolor . ";' onClick='get_ticket(" . $row2['id'] . ");'>";
			$print .= "<TD style='width: 30%;'>" . $row2['scope'] . "</TD>";
			$print .= "<TD style='width: 50%;'>" . stripslashes_deep(shorten($row2['description'], 30)) . "</TD>";
			$print .= "<TD style='width: 20%;'>" . format_date_2(strtotime($row2['problemstart'])) . "</TD>";		
			$print .= "</TR>";
			$bgcolor = ($bgcolor == "#EEEEEE") ? "#FEFEFE" : "#EEEEEE";
			} // end while	
		}				// end while
		$print .= "</TABLE>";
	}	//	end else
print $print;
exit();
?>