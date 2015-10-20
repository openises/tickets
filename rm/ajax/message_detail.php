<?php
/*
9/10/13 - new file - gets message detail for mobile screen
*/
@session_start();
require_once('../../incs/functions.inc.php');

function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}

$the_user = ($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$message_id = (isset($_GET['message_id'])) ? $_GET['message_id'] : NULL;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `id` = '" . $message_id . "'"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$reply_address = $row['from_address'];
$print = "<TABLE style='width: 100%; border: 2px outset #707070;'>";	
$print .= "<TR style='width: 100%; color: #FFFFFF; background-color: #707070;'><TD COLSPAN=2 style='text-align: center; font-weight: bold;'>MESSAGE DETAIL";
$print .= "<SPAN id='close_message_detail' class='plain' style='float: right; z-index: 999999; text-align: center; width: 40px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='close_message_detail();'><IMG SRC = './images/close.png' BORDER=0 STYLE = 'vertical-align: middle'></span>";
$print .= "<SPAN id='reply_but' class='plain' style='float: right; display: none; z-index: 10; width: 40px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'do_reply(\"" . $reply_address . "\");'><IMG SRC = './images/email_reply.png' BORDER=0 STYLE = 'vertical-align: middle'></span>";
$print .= "</TD></TR>";
$print .= "<TR style='width: 100%;'>";
$print .= "<TD style='width: 30%; border: 1px solid #707070;'>FROM</TD>";		
$print .= "<TD style='width: 70%; border: 1px solid #707070;'>" . $row['from_address'] . "</TD></TR>";
$print .= "<TR style='width: 100%;'>";
$print .= "<TD style='width: 30%; border: 1px solid #707070;'>TO</TD>";			
$print .= "<TD style='width: 70%; border: 1px solid #707070;'>" . $row['recipients'] . "</TD></TR>";
$print .= "<TR style='width: 100%;'>";
$print .= "<TD style='width: 30%; border: 1px solid #707070;'>DATE</TD>";			
$print .= "<TD style='width: 20%; border: 1px solid #707070;'>" . format_date_2(strtotime($row['date'])) . "</TD></TR>";		
$print .= "<TR style='width: 100%;'>";
$print .= "<TD style='width: 30%; border: 1px solid #707070;'>SUBJECT</TD>";			
$print .= "<TD style='width: 20%; border: 1px solid #707070;'>" . $row['subject'] . "</TD></TR>";	
$print .= "<TR style='width: 100%;'>";
$print .= "<TD style='width: 30%; border: 1px solid #707070;'>MESSAGE</TD>";			
$print .= "<TD style='width: 20%; border: 1px solid #707070;'>" . $row['message'] . "</TD></TR>";	
$print .= "</TABLE>";

$ret_arr[0] = $row['from_address'];
$ret_arr[1] = $row['ticket_id'];
$ret_arr[2] = $row['subject'];
$ret_arr[3] = $row['message'];
$ret_arr[4] = $print;

print json_encode($ret_arr);
exit();
?>