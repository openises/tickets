<?php
/*
9/10/13 - new file Shows road condition alerts in mobile screen
*/
@session_start();
require_once('../../incs/functions.inc.php');

function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}

$the_user = ($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$alert_id = (isset($_GET['alert_id'])) ? $_GET['alert_id'] : NULL;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]roadinfo` WHERE `id` = '" . $alert_id . "'"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num=mysql_num_rows($result);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$print = "<TABLE style='width: 100%; border: 2px outset #707070;'>";	
$print .= "<TR style='width: 100%; color: #FFFFFF; background-color: #707070;'><TD COLSPAN=2 style='text-align: center; font-weight: bold;'>ALERT DETAIL";
$print .= "<SPAN id='close_alert_but' class='plain' style='float: right; z-index: 999999; text-align: center;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='close_alert_detail();'>Close Detail<IMG SRC = './images/close.png' BORDER=0 STYLE = 'vertical-align: middle'></span>";
$print .= "</TD></TR>";
$print .= "<TR style='width: 100%;'>";
$print .= "<TD style='width: 30%; border: 1px solid #707070;'>ADDRESS</TD>";		
$print .= "<TD style='width: 70%; border: 1px solid #707070;'>" . $row['address'] . "</TD></TR>";
$print .= "<TR style='width: 100%;'>";
$print .= "<TD style='width: 30%; border: 1px solid #707070;'>DESCRIPTION</TD>";			
$print .= "<TD style='width: 70%; border: 1px solid #707070;'>" . $row['description'] . "</TD></TR>";
$print .= "<TR style='width: 100%;'>";
$print .= "<TD style='width: 30%; border: 1px solid #707070;'>DATE</TD>";			
$print .= "<TD style='width: 20%; border: 1px solid #707070;'>" . format_date_2(strtotime($row['_on'])) . "</TD></TR>";		
$print .= "</TABLE>";
print $print;
exit();
?>