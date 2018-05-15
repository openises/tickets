<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
if (!(array_key_exists('ticket_id', $_GET))) {		//	3/15/11
	exit();
	} else {
	extract ($_GET);
	}
	
$query = "SELECT DISTINCT `ticket_id` , `scope`, `severity`, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
	WHERE `t`.`status` = {$GLOBALS['STATUS_OPEN']} OR `t`.`status` = {$GLOBALS['STATUS_SCHEDULED']}	
	ORDER BY `t`.`severity` DESC, `t`.`scope` ASC" ;				// 4/28/10

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
$no_assigns = mysql_affected_rows();

$outputtext = "<DIV><BR />";	
$tabindex = 1;
$outputtext .= "<SPAN roll='button' aria-label='View Incident " . $ticket_id . "' tabindex=" . $tabindex . " id='view" . $ticket_id . "' class='plain text' style='width: 120px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);'  onClick='get_ticket(" . $ticket_id . ");'><SPAN style='float: left;'>" . get_text('View') . "</SPAN><IMG style='vertical-align: middle; float: right;' SRC='./images/list_small.png' BORDER=0></SPAN><BR />";
$tabindex++;
$outputtext .= "<SPAN roll='button' aria-label='Add Patient to Incident " . $ticket_id . "' tabindex=" . $tabindex . " id='pat" . $ticket_id . "' class='plain text' style='width: 120px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(" . $ticket_id . ", 'Add Patient', 'patient');\"><SPAN style='float: left;'>Add " . get_text('Patient') . "</SPAN><IMG style='vertical-align: middle; float: right;' SRC='./images/patient_small.png' BORDER=0></SPAN><BR />";
$tabindex++;
$outputtext .= "<SPAN roll='button' aria-label='Add Action to Incident " . $ticket_id . "' tabindex=" . $tabindex . " id='act" . $ticket_id . "' class='plain text' style='width: 120px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(" . $ticket_id . ", 'Add Action', 'action');\"><SPAN style='float: left;'>Add " .  get_text('Action') . "</SPAN><IMG style='vertical-align: middle; float: right;' SRC='./images/action_small.png' BORDER=0></SPAN><BR />";
$tabindex++;
$outputtext .= "<SPAN roll='button' aria-label='Add Note to Incident " . $ticket_id . "' tabindex=" . $tabindex . " id='note" . $ticket_id . "' class='plain text' style='width: 120px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(" . $ticket_id . ", 'Add Note', 'note');\"><SPAN style='float: left;'>Add " . get_text('Note') . "</SPAN><IMG style='vertical-align: middle; float: right;' SRC='./images/edit_small.png' BORDER=0></SPAN><BR />";
$tabindex++;
$outputtext .= "<SPAN roll='button' aria-label='Dispatch Incident " . $ticket_id . "' tabindex=" . $tabindex . " id='disp_" . $ticket_id . "' CLASS='plain text' style='width: 120px; float: none; display: inline-block;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick=\"load_dispatch(" . $ticket_id . ", window.disp_field, window.disp_direct, window.searchitem);\"><SPAN style='float: left;'>" . get_text('Dispatch') . "</SPAN><IMG style='vertical-align: middle; float: right;' SRC='./images/dispatch_small.png' BORDER=0></SPAN><BR />";			
$tabindex++;
$outputtext .= "<SPAN roll='button' aria-label='Print Incident " . $ticket_id . "' tabindex=" . $tabindex . " id='prt" . $ticket_id . "' class='plain text' style='width: 120px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"do_print_ticket(" . $ticket_id . ");\"><SPAN style='float: left;'>" . get_text('Print') . "</SPAN><IMG style='vertical-align: middle; float: right;' SRC='./images/print_small.png' BORDER=0></SPAN><BR />";
$tabindex++;
$outputtext .= "<SPAN roll='button' aria-label='Contact Units Assigned to Incident " . $ticket_id . "' tabindex=" . $tabindex . " id='contact" . $ticket_id . "' class='plain text' style='width: 120px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(" . $ticket_id . ", 'Contact Units', 'contact_all_sel');\"><SPAN style='float: left;'>" . get_text('Contact') . "</SPAN><IMG style='vertical-align: middle; float: right;' SRC='./images/mail_small.png' BORDER=0></SPAN><BR />";
$outputtext .= "</DIV>";
$output_arr = array();
$output_arr[0] = $outputtext;
$output_arr[1] = "Incident " . $ticket_id . ", " . get_scope($ticket_id);
print json_encode($output_arr);
exit();
?>