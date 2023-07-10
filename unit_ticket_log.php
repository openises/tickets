<?php
/*
9/10/13 New File - for writing unit or ticket specific log entry
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);
if(!isset($_POST)) {
	if((isset($_GET)) && (($_GET['responder'] == 0) || ($_GET['responder'] == ""))) {
		$responder = 0;
		} elseif(!isset($_GET)) {
		$responder = 0;
		} else {
		$responder = $_GET['responder'];
		}

	if((isset($_GET)) && (($_GET['ticket'] == 0) || ($_GET['ticket'] == ""))) {
		$ticket = 0;
		} elseif(!isset($_GET)) {
		$ticket = 0;
		} else {
		$ticket = $_GET['ticket'];
		}	
	}

//dump($_POST);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Tickets Log Processing</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="Tickets Log Entry"">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js" type="application/x-javascript"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size()};

function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
		
	set_fontsizes(viewportwidth, "popup");
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	}

function validate_del() {
	if (document.del_form.frm_days_val.value==0) { 
		alert("check days value");
		return false;
		}
	else {
		return true;
		}
	}			// end function

function get_new_colors() {								// 4/5/11
	window.location.href = '<?php print basename(__FILE__);?>';
	}
</SCRIPT>
</HEAD>
<BODY>
<?php
if (empty($_POST)) {
	if (is_guest()) {
?>
		<CENTER><BR /><BR /><BR /><BR /><BR /><H3>Guests not allowed Log access. </CENTER><BR /><BR />

		<SPAN CLASS='plain text' style='float: none; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Cancel</SPAN>
<?php
		exit();
		}
		
	if(($responder != 0) && ($ticket == 0)) {
		$theTag = get_text('Unit');
		} elseif(($ticket != 0) && ($responder == 0)) {
		$theTag = get_text('Incident');	
		} else {
		$theTag = "";
		}
?>
	<DIV ID='outer'>
		<DIV id='button_bar' class='but_container'>
			<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'><?php print $theTag;?> Log</SPAN>
			<SPAN ID='can_but' class='plain text' style='float: right; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
			<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.log_form.reset(); init();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
			<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.log_form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
		</DIV>
		<FORM NAME="log_form" METHOD = "post" ACTION="<?php print basename(__FILE__); ?>">
		<TABLE STYLE='margin-left: 50px; position: relative; top: 100px;'>
<?php
			if(intval($responder) != 0) {
				$al_groups = $_SESSION['user_groups'];
				
				if(empty($al_groups)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
					$where2 = "";
					} else {
					if(array_key_exists('viewed_groups', $_SESSION)) {		//	6/10/11
						$curr_viewed= explode(",",$_SESSION['viewed_groups']);
						}

					if(!isset($curr_viewed)) {			//	6/10/11
						$x=0;
						$where2 = "AND (";
						foreach($al_groups as $grp) {
							$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
							$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
							$where2 .= $where3;
							$x++;
							}
						} else {
						$x=0;
						$where2 = "AND (";
						foreach($curr_viewed as $grp) {
							$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
							$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
							$where2 .= $where3;
							$x++;
							}
						}
					}		

				$query = "SELECT *, `$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
						FROM `$GLOBALS[mysql_prefix]ticket`
						LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON `$GLOBALS[mysql_prefix]ticket`.`id`=`$GLOBALS[mysql_prefix]allocates`.`resource_id`
						WHERE (`status` = {$GLOBALS['STATUS_OPEN']} OR `status` = {$GLOBALS['STATUS_SCHEDULED']}) {$where2}
						GROUP BY `tick_id` ORDER BY `severity` DESC, `problemstart` ASC "; // highest severity, oldest open
				$result = mysql_query($query);
				if (mysql_num_rows($result) >= 1) {			// if a single, do it
					$row = mysql_fetch_assoc($result);
					$inc_ctr = mysql_num_rows($result);
					print "<TR CLASS='even'><TD CLASS='td_label text'>Select Ticket</TD><TD class='td_label text'>";	
					print "<SELECT CLASS='text' NAME='frm_ticket_sel' onChange='document.log_form.ticket.value=this.value;'>";
					print "<OPTION CLASS='text' VALUE='0' SELECTED>Ignore</OPTION>";
					while ($row = mysql_fetch_array($result))  {
						$addr = substr($row['street'] . " " . $row['city'] . " " . $row['state'], 0, 24);
						$descr = substr($row['scope'] , 0, 24) . " - " . $addr ;
						print "<OPTION CLASS='text' value='{$row['tick_id']}'> {$descr}</OPTION>";
						}
					print "</SELECT></TD></TR><TR CLASS='even'><TD COLSPAN=2>&nbsp;</TD></TR>";
					}
				}
?>
			<TR CLASS = 'even'><TD CLASS='td_label text'>Log entry:</TD><TD CLASS='td_data text'><TEXTAREA NAME="frm_comment" COLS="50" ROWS="20" WRAP="virtual"></TEXTAREA></TD></TR>
		</TABLE>
		<INPUT TYPE='hidden' NAME='func' VALUE='add'>
		<INPUT TYPE='hidden' NAME='responder' VALUE=<?php print $responder;?>>
		<INPUT TYPE='hidden' NAME='ticket' VALUE=<?php print $ticket;?>>
		</FORM>
	</DIV>
<?php 
	} else {										// not empty
	extract($_POST);
	do_log($GLOBALS['LOG_UNIT_COMMENT'], $ticket, $responder, strip_tags(trim($_POST['frm_comment'])));
?>
	<DIV style='width: 100%; text-align: center;'><BR /><BR /><BR /><BR /><BR /><BR />Log entry inserted
	<BR /><BR /><BR /><SPAN id='close_but' class='plain text' style='float: none; width: 100px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.close()" />Close</SPAN>
	</DIV>
<?php
	} 
	
?>
<SCRIPT LANGUAGE="Javascript">
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
	
set_fontsizes(viewportwidth, "popup");
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
</SCRIPT>
</BODY>
</HTML>
