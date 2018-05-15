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
$sev_colors = array('blue','green','red');
$sev_names = array('Normal','Medium','High');
$print = "";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = '" . $the_user . "' AND ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
if (mysql_num_rows($result) == 0) { 
	$print = "<SPAN style='width: 100%;'>No Current Assignments</SPAN>";
	} else {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$query2 = "SELECT *, `t`.`id` AS `tick_id`, `i`.`type` AS `type_name`, `i`.`id` AS 'in_type' 
			FROM `$GLOBALS[mysql_prefix]ticket` `t` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `i` ON `t`.`in_types_id` = `i`.`id` 		
			WHERE `t`.`id` = " . $row['ticket_id'] . " AND `status` = " . $GLOBALS['STATUS_OPEN']; 
		$result2 = mysql_query($query2) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))){
			$type_color = $row2['color'];
			$type_name = $row2['type_name'];
			$sev_color = $sev_colors[$row2['severity']];
			$sev_name = $sev_names[$row2['severity']];
			$print .= "<SPAN id='tick_" . $row2['tick_id'] . "' CLASS='plain text_large' STYLE='width: 42%; height: 60px; float: none; display: inline-block; float: left;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='get_ticket(" . $row2['tick_id'] . ");'>";
			$print .= $row2['scope'] . "<BR />";
			$print .= "<SPAN style='color: " . $type_color . ";'>Type: " . $type_name . "</SPAN><BR />";
			$print .= "<SPAN style='color: " . $sev_color . ";'>Severity: " . $sev_name . "</SPAN>";			
			$print .= "</SPAN>";
			} // end while	
		}				// end while
	}	//	end else
print $print;
exit();
?>