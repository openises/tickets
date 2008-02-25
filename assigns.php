<?php
require_once('functions.inc.php'); 
do_login(basename(__FILE__));
if($istest) {
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
	
extract($_GET);
extract($_POST);
$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
$func = (empty($_POST))? "list" : $_POST['func'];

?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Assignments Module</TITLE>
	<META HTTP-EQUIV="Content-Type" 		CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" 				CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<SCRIPT>

if (window.opener && !window.opener.closed) {
	window.opener.document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
	window.opener.document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
	window.opener.document.getElementById("script").innerHTML = "<?php print LessExtension(basename( __FILE__));?>";
	}

	function editA(id) {							// edit assigns
		document.nav_form.frm_id.value=id;
<?php
		print "\t\tdocument.nav_form.func.value=";	// guest priv's = 'read-only'
		print is_guest()? "'view';" : "'edit';";
?>	
		document.nav_form.action="<?php print basename(__FILE__); ?>";
		document.nav_form.method='POST';
		document.nav_form.submit();
		}

	function viewT(id) {			// view ticket
		document.T_nav_form.id.value=id;
		document.T_nav_form.action='main.php';
		document.T_nav_form.submit();
		}

	function viewU(id) {			// view unit
		document.U_nav_form.id.value=id;
		document.U_nav_form.submit();
		}

</SCRIPT>

<?php 								// id, as_of, status_id, ticket_id, unit_id, comment, user_id
switch ($func) {					// =====================================================================

	case 'add': 					// first build JS array of existing assigns for dupe prevention
	print "\n<SCRIPT>\n";
	print "assigns = new Array();\n";
	
	$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of FROM `$GLOBALS[mysql_prefix]assigns` ORDER BY `as_of` DESC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	while($row = stripslashes_deep(mysql_fetch_array($result))) {
		print "assigns['" .$row['ticket_id'] .":" . $row['responder_id'] . "']=true;\n";
		}
?>		
	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_ticket_id.value == "")	{errmsg+= "\tSelect Incident\n";}
		if (theForm.frm_unit_id.value == "")	{errmsg+= "\tSelect Unit\n";}
		if (theForm.frm_status_id.value == "")	{errmsg+= "\tSelect Status\n";}
		if (assigns[theForm.frm_ticket_id.value + ":" +theForm.frm_unit_id.value]) {
									errmsg+= "\tDuplicates existing assignment\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.submit();
			}
		}				// end function vali date(theForm)

	function reSizeScr() {
		window.resizeTo(740,300);		
		}

	</SCRIPT>
	</HEAD>
	<BODY onLoad = "reSizeScr()">
		<TABLE BORDER=0 ALIGN='center'>
		<FORM NAME="add_Form" onSubmit="return validate(document.add_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
		<TR CLASS="even"><th colspan=2 ALIGN="center">Assign Unit to Incident</th></TR>
		<TR CLASS="odd" VALIGN="baseline">
			<TD CLASS="td_label" ALIGN="right">Incident:</TD>
			<TD><SELECT NAME="frm_ticket_id">
				<OPTION VALUE= '' selected>Select</OPTION>
<?php

				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = " . $GLOBALS['STATUS_OPEN']. " ORDER BY `scope`"; 
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//				if (mysql_affected_rows()>0) 
				while ($row = mysql_fetch_array($result))  {
					print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['scope'] . "</OPTION>\n";		
					}
?>
				</SELECT>	
			</TD></TR>
		<TR CLASS="even" VALIGN="baseline">
			<TD CLASS="td_label" ALIGN="right">Unit:</TD>
			<TD><SELECT name="frm_unit_id" onChange = "document.add_Form.frm_log_it.value='1'" >
				<OPTION value= '' selected>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ";	//  
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				while ($row = mysql_fetch_array($result))  {
					print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['name'] . "</OPTION>\n";		
					}
?>
		</SELECT></TD></TR>
		<TR CLASS="odd" VALIGN="baseline">
			<TD CLASS="td_label" ALIGN="right">&nbsp;&nbsp;Unit Status:</TD>
			<TD><SELECT name="frm_status_id"  onChange = "document.add_Form.frm_log_it.value='1'"> 
				<OPTION value= '' selected>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ";	//  unit status
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				while ($row = mysql_fetch_array($result))  {
					print "\t\t<OPTION value='" . $row['id'] . "'>" . $row['status_val'] . "</OPTION>\n";		
					}
?>
				</SELECT>	
			</TD></TR>
		<TR CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Comments:</TD>
			<TD><INPUT MAXLENGTH="64" SIZE="64" NAME="frm_comments" VALUE="" TYPE="text"></TD></TR>
		
		<TR CLASS="odd" VALIGN="baseline"><TD colspan="99" ALIGN="center">
			<BR>
			<INPUT TYPE="button" VALUE="Cancel" onclick="document.can_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;	
			<INPUT TYPE="button" VALUE="Reset" onclick="Javascript: this.form.reset();">&nbsp;&nbsp;&nbsp;&nbsp;	
			<INPUT TYPE="submit" VALUE="               Submit           " name="sub_but" >  
			</TD></TR>
		 </tbody></table>
		<INPUT TYPE='hidden' NAME='frm_by_id'	VALUE= "<?php print $my_session['user_id'];?>">
		<INPUT TYPE='hidden' NAME='func' 		VALUE= 'add_db'>
		<INPUT TYPE='hidden' NAME='frm_log_it' 	VALUE=''/>
		</FORM>
<?php	
		break;				// end case 'add'
		
			//	id, as_of, status_id, ticket_id, unit_id, comment, user_id
	case 'add_db' : 		// =================================================================================================
		 	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 
			$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`)
							VALUES (%s,%s,%s,%s,%s,%s)",
								quote_smart($now),
								quote_smart($frm_status_id),
								quote_smart($frm_ticket_id),
								quote_smart($frm_unit_id),
								quote_smart($frm_comments),
								quote_smart($frm_by_id));

			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
								// apply status update to unit status
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($_POST['frm_status_id']) . " WHERE `id` = " .quote_smart($frm_unit_id)  ." LIMIT 1";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//			do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_unit_id, $frm_status_id, $frm_ticket_id);
			do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_unit_id, $frm_status_id);
?>
	</HEAD>
<BODY>
	<CENTER><BR><BR><H3>Call Assignment made</H3><BR><BR>
	<FORM NAME='add_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
	<INPUT TYPE='button' VALUE='Continue' onClick = "document.add_cont_form.submit()">
	<INPUT TYPE='hidden' NAME='func' VALUE='list'>
	</FORM></BODY></HTML>
<?php	
		break;				// end case 'add_db' 
	
	case 'list' :			// ==============================================================================
	
?>
<SCRIPT>

function reSizeScr() {
	var lines = document.can_Form.lines.value;
//	height = (((lines * 18)+180)<100)? 100: (lines * 18)+180 ;
	window.resizeTo(740,((lines * 18)+180));		// derived via trial/error (more of the latter, mostly)
	}

</SCRIPT>	
	</HEAD>
<BODY onLoad = "reSizeScr ()";>
<?php
		$priorities = array("text_black","text_blue","text_red" );
		print "<TABLE BORDER=0 ALIGN='center' WIDTH='90%' ID='call_board' STYLE='display:block'>";
		print "<TR CLASS='even'><TD COLSPAN=3 ALIGN = 'right'><B>Call Board</B></TD><TD COLSPAN=4 ALIGN='center'<FONT SIZE='-3'><I> (* click line for details)</I></FONT></TD></TR>\n";

		$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, `assigns`.`id` AS `assign_id` , `assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`,
			`s`.`status_val` AS `thestatus`, `r`.`name` AS `theunit` FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]assigns`.`status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 		ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
			ORDER BY `as_of` ASC ";

		$i = 1;	
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$lines = mysql_affected_rows();
		if ($lines>0) {
			$now = time() - (get_variable('delta_mins')*60);
			$delta = 24*60*60;							// 24 hours
			$items = mysql_affected_rows();
			$header= "<TR CLASS='odd'><TD ALIGN='center'>* Call</TD><TD ALIGN='center'>Addr</TD><TD ALIGN='center'>* Comment</TD><TD ALIGN='center'>* Unit</TD><TD ALIGN='center'>Unit Status</TD><TD ALIGN='center'>As of</TD><TD ALIGN='center'>By</TD></TR>\n";
			while($row = stripslashes_deep(mysql_fetch_array($result))) {
				if (((empty($row['clear'])) || (!empty($row['clear']) && ((totime($row['clear']) > ($now-$delta)))))) {
					if ($i == 1) {print $header;}
					$theClass = $priorities[$row['severity']];
					print "<TR CLASS='" . $evenodd[($i+1)%2] . "'>\n";

					if (!empty($row['clear'])) {
						$strike = "<STRIKE>"; $strikend = "</STRIKE>";		// strikethrough on closed assigns
						}
					else {
						$strike = $strikend = "";
						}	
		
					print "\t<TD onClick = viewT('" . $row['ticket_id'] . "') CLASS='$theClass' TITLE= '" . $row['theticket'] . "'><U>" . $strike . shorten($row['theticket'], 16) . $strikend . "</U></TD>\n";		// call
	
					$address = (empty($row['street']))? "" : $row['street'] . ", ";
					$address .= $row['city'];
					
					print "\t<TD TITLE='". $address ."'>" .  $strike . shorten($address, 16) .  $strikend .	"</TD>\n";		// address
					print "\t<TD onClick = editA(" . $row['assign_id'] . ") TITLE='" . shorten ($row['assign_comments'], 72) . "'><U>" . $strike .  shorten ($row['assign_comments'], 16) . $strikend .  "</U></TD>\n";				// comment
					$unit_name = ($row['theunit']=="")? "??": $row['theunit'];
					print "\t<TD TITLE = '" .$unit_name . "' onClick = viewU('" . $row['responder_id'] . "') ><U>" .  $strike . shorten($unit_name, 16)  . $strikend . "</U></TD>\n";						// unit
					print "\t<TD>" .  $strike . shorten ($row['thestatus'], 12) .  $strikend . "</TD>\n";						// status
					print "\t<TD>" .  $strike . date("d H:i", $row['as_of'])  .  $strikend . "</TD>\n";						// as of 
					print "\t<TD TITLE = '" . $row['theuser'] . "'>" .  $strike . shorten ($row['theuser'], 8) .  $strikend . "</TD>\n";						// user  
					print "</TR>";
					$i++;
					}
				}		// end while($row ...)
			}		// end if ($lines>0) 
		if ($i>1) {
			print "<TR CLASS='" . $evenodd[($i+1)%2] . "'><TD COLSPAN=99 ALIGN='center'>";
			print "<FONT SIZE='-1'><I>Call severity:&nbsp;&nbsp;&nbsp;&nbsp;<span CLASS='text_black'>Normal</span>&nbsp;&nbsp;&nbsp;&nbsp; <span CLASS='text_blue'>Medium</span>&nbsp;&nbsp;&nbsp;&nbsp; <span CLASS='text_red'>High</span></I></FONT>";
			print "</TD></TR>";				
			}				// if (mysql_affected_rows()>0)
		else {
			print "<TR><TH COLSPAN=99>&nbsp;</TH></TR>";
			print "<TR><TH COLSPAN=99><BR />No Current Call Assignments<BR /></TH></TR>";
			}
		print "<TR CLASS='" . $evenodd[($i+1)%2] . "'>&nbsp;<TD COLSPAN=99 ALIGN='center'>";
		print "<INPUT TYPE='button' VALUE = 'Add' onClick = 'document.add_form.submit()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		print "<INPUT TYPE='button' VALUE = 'Close' onClick = 'self.close()'>";

		print "</TD></TR>";
		print "</TABLE>";		

		break;				// end case 'list'
	
	case 'view' :			// read-only =============================================================================
?>
	<SCRIPT>
	function reSizeScr() {
		window.resizeTo(740,300);		
		}
	</SCRIPT>
	</HEAD>
	<BODY onLoad = "reSizeScr()">
<?php	
													// if (!empty($row['clear']))	
		$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, `assigns`.`id` AS `assign_id` , `assigns`.`comments` AS `assign_comments`,`u`.`user` AS `theuser`, `t`.`scope` AS `theticket`,
			`s`.`status_val` AS `thestatus`, `r`.`name` AS `theunit` FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]assigns`.`status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 		ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
			WHERE `assigns`.`id` = $frm_id LIMIT 1";

		$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result));

?>
		<TABLE BORDER=0 ALIGN='center'>
		<FORM NAME="edit_Form" onSubmit="return validate_ed(document.edit_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
		<TR CLASS="even"><TD colspan=2 ALIGN="center">Call Assignment (#<?php print $asgn_row['id']; ?>)</TD></TR>
		<TR CLASS="odd" VALIGN="baseline" onClick = "viewT('<?php print $asgn_row['ticket_id'];?>')">
			<TD CLASS="td_label" ALIGN="right">&raquo; <U>Incident</U>:</TD><TD>
<?php
		print $asgn_row['scope'] . "</TD></TR>\n";		

		if (!$asgn_row['responder_id']=="0"){
			$unit_name = $asgn_row['name'];
			$unit_link = " onClick = \"viewU('" . $asgn_row['responder_id'] . "')\";";
			$highlight = "&raquo;";
			}
		else {
			$highlight = "";
			$unit_name = "<FONT COLOR='red'><B>UNASSIGNED</B></FONT>";
			$unit_link = "";
			}
		print "<TR CLASS='even' VALIGN='baseline'><TD CLASS='td_label' ALIGN='right'>As of:</TD><TD>" . format_date($asgn_row['as_of']) .
			"&nbsp;&nbsp;&nbsp;&nbsp;By " . $asgn_row['user'] . "</TD></TR>";		
		print "<TR CLASS='odd' VALIGN='baseline' " . $unit_link . ">";
		print "<TD CLASS='td_label' ALIGN='right'> " . $highlight . "<U>Unit</U>:</TD><TD>" . $unit_name ."</TD></TR>";

		print "<TR CLASS='even' VALIGN='baseline'>\n";
		print "<TD CLASS='td_label' ALIGN='right'>&nbsp;&nbsp;Unit Status:</TD><TD>";
		if ($asgn_row['responder_id']!="0"){
			print $asgn_row['status_val'];
			}		// end if (!$asgn_row['responder_id']=="0")
		else {
			print "NA";
			}
?>
		</TD></TR>
		<TR CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Comments:</TD>
			<TD><?php print $asgn_row['comments']; ?></TD></TR>
		
		<TR CLASS="even" VALIGN="baseline"><TD colspan="99" ALIGN="center">
			<br>
			<INPUT TYPE="BUTTON" VALUE="Cancel" onclick="document.can_Form.submit();" style="height: 1.5em;">&nbsp;&nbsp;&nbsp;&nbsp;	
			</TD></TR>
		 </tbody></table>
		<INPUT TYPE='hidden' NAME='func' value= ''>
		<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'>
		</FORM>
			
<?php	
	
		break;			// end case 'view'
	
	case 'edit':		// ======================================================================================
?>
<SCRIPT>
		function validate_ed(theForm) {
			var errmsg="";
			if (document.forms[0].frm_status_id.value == "")	{errmsg+= "\tSelect Status\n";}

			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
				}
			else {
				document.forms[0].submit();
				}
			}				// end function validate_ed(theForm)
			

		function confirmation() {
			var answer = confirm("This dispatch run completed?")
			if (answer){
				document.edit_Form.delete_db.value='true'; 
				document.edit_Form.submit();
				}
			}		// end function confirmation()
	function reSizeScr() {
		window.resizeTo(740,300);		
		}
	</SCRIPT>
	</HEAD>
	<BODY onLoad = "reSizeScr()">
<?php	

		$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, `assigns`.`id` AS `assign_id` , `assigns`.`comments` AS `assign_comments`,
			`u`.`user` AS `theuser`	FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
			WHERE `assigns`.`id` = $frm_id LIMIT 1";

		$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result));
//		dump ($query);
		$clear = (empty($asgn_row['clear'])) ? "": "<FONT COLOR='red'><B>Cleared</B></FONT>";
		$disabled = (empty($asgn_row['clear'])) ? "": " DISABLED ";
?>
		<TABLE BORDER=0 ALIGN='center'>
		<FORM NAME="edit_Form" onSubmit="return validate_ed(document.edit_Form);" action = "<?php print basename(__FILE__); ?>" method = "post">
		<TR CLASS="odd"><TD colspan=2 ALIGN="center">Edit this Call Assignment (#<?php print $asgn_row['id'] ?>) <?php print $clear; ?></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS='even' VALIGN='baseline'><TD CLASS='td_label' ALIGN='right'>As of:</TD><TD><?php print format_date($asgn_row['as_of']);?>
			&nbsp;&nbsp;&nbsp;&nbsp;By <?php print $asgn_row['user'];?></TD></TR>		

		<TR CLASS="odd" VALIGN="baseline">
			<TD CLASS="td_label" ALIGN="right">Incident:</TD>
			<TD><SELECT NAME="frm_ticket_id" onChange = "document.edit_Form.frm_log_it.value='1'" <?php print $disabled;?>>
<?php	
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = " . $GLOBALS['STATUS_OPEN']. " ORDER BY `scope`"; 
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				while ($row2 = mysql_fetch_array($result))  {
					$sel = ($asgn_row['ticket_id']== $row2['id'])? " SELECTED": "";
					print "\t\t<OPTION value='" . $row2['id'] ."'  $sel>" . $row2['scope'] . "</OPTION>\n";		
					}
?>
				</SELECT>	
			</TD></TR>
		<TR CLASS="even" VALIGN="baseline">
			<TD CLASS="td_label" ALIGN="right">Unit:</TD>
			<TD><SELECT name="frm_unit_id" onChange = "document.edit_Form.frm_log_it.value='1'" <?php print $disabled;?>>
<?php	// UNITS
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ";	//  
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				while ($row2 = mysql_fetch_array($result))  {
					$sel = ($asgn_row['responder_id']== $row2['id'])? " SELECTED": "";
					print "\t\t<OPTION value='" . $row2['id'] . "' $sel>" . $row2['name'] . "</OPTION>\n";		
					}
?>
		</SELECT></TD></TR>
		<TR CLASS="odd" VALIGN="baseline">
			<TD CLASS="td_label" ALIGN="right">&nbsp;&nbsp;Unit Status:</TD>
			<TD><SELECT name="frm_status_id"  onChange = "document.edit_Form.frm_log_it.value='1'" <?php print $disabled;?>> 
<?php	// UNIT STATUS
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ";	//  unit status
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				while ($row2 = mysql_fetch_array($result))  {
					$sel = ($asgn_row['status_id']== $row2['id'])? " SELECTED": "";
					print "\t\t<OPTION value='" . $row2['id'] . "' $sel>" . $row2['status_val'] . "</OPTION>\n";		
					}
?>
				</SELECT>	
			</TD></TR>
		<TR CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Comments:</TD>
			<TD><INPUT MAXLENGTH="64" SIZE="64" NAME="frm_comments" VALUE="<?php print $asgn_row['comments']; ?>" TYPE="text"<?php print $disabled;?>></TD></TR>
		
		<TR CLASS="odd" VALIGN="baseline"><TD colspan="99" ALIGN="center">
			<br>
			<INPUT TYPE="BUTTON" VALUE="Cancel" onclick="document.can_Form.submit();" style="height: 1.5em;">
<?php
			if (!$disabled) {
?>			
			&nbsp;&nbsp;&nbsp;&nbsp;	
			<INPUT TYPE="BUTTON" VALUE="Reset"  onclick="Javascript: this.form.reset();" style="height: 1.5em;">&nbsp;&nbsp;&nbsp;&nbsp;	
			<INPUT TYPE="BUTTON" VALUE=" Submit " name="sub_but" onClick = "validate_ed(document.edit_Form)" style="width: 12em;height: 1.5em;" ><br><br>
			<INPUT TYPE="BUTTON" VALUE="Run Complete" onClick="confirmation()" style="height: 1.5em;">
<?php
			}
?>			
			</TD></TR>
		 </tbody></table>
		<INPUT TYPE='hidden' NAME='frm_by_id' value= "<?php print $my_session['user_id'];?>">
		<INPUT TYPE='hidden' NAME='func' value= 'edit_db'>
		<INPUT TYPE='hidden' NAME='delete_db' value= ''>
		<INPUT TYPE='hidden' NAME='frm_id' value= '<?php print $frm_id; ?>'>
		<INPUT TYPE='hidden' NAME='frm_unit_id' value= '<?php print $asgn_row['responder_id'];?>'>
		<INPUT TYPE='hidden' NAME='frm_ticket_id' value= '<?php print $asgn_row['ticket_id'];?>'>
		<INPUT TYPE='hidden' NAME='frm_log_it' value=''/>
		<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'>
		</FORM>
		
<?php
		break;			// end 	case 'edit':
		
	case 'edit_db':		// ===============================================================================================
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

		if (!empty($delete_db)) 	{
			$age_delta = 60*60*24;
			$cutoff = mysql_format_date(time() - (get_variable('delta_mins')*60) - $age_delta);

			$query  = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NOT NULL AND `clear` < " . quote_smart($cutoff);		// delete all older assigns 
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		
			do_log($GLOBALS['LOG_UNIT_COMPLETE'], $frm_ticket_id, $frm_unit_id);		// set clear times
			$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `as_of`= " . quote_smart($now) . ", `clear`= " . quote_smart($now) . " WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

			$message = "Run completion recorded";
			}
		else {
			$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `as_of`= " . quote_smart($now) . ", `comments`= " . quote_smart($_POST['frm_comments']) . " WHERE `id` = " .$_POST['frm_id'] . " LIMIT 1";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($_POST['frm_status_id']) . ", `updated` = " . quote_smart($now) . " WHERE `id` = " . quote_smart($frm_unit_id) ." LIMIT 1";

			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			if (intval($frm_log_it)==1) { do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $frm_unit_id, $frm_status_id);}
			$message = "Update Applied";
			}
?>
		</HEAD>
<BODY>
	<BR><BR><CENTER><H3><?php print $message; ?></H3><BR><BR>
	<FORM NAME='ed_cont_form' METHOD = 'post' ACTION = "<?php print basename(__FILE__); ?>">
	<INPUT TYPE='button' VALUE='Continue' onClick = "document.ed_cont_form.submit()">
	<INPUT TYPE='hidden' NAME='func' VALUE='list'>
	</FORM></BODY></HTML>
<?php	
		break;				// end 	case 'edit_db'

	default:				// =======================================================================================
		print "	error: " . __LINE__;
	}				// end switch ($func)
?>

<FORM NAME='nav_form' METHOD='' ACTION = "">
<INPUT TYPE='hidden' NAME='frm_id' VALUE=''>
<INPUT TYPE='hidden' NAME='func' VALUE=''>
<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'>
</FORM>

<FORM NAME='add_form' METHOD='POST' ACTION = "<?php print basename(__FILE__); ?>">
<INPUT TYPE='hidden' NAME='func' VALUE='add'>
<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'>
</FORM>

<FORM NAME='T_nav_form' METHOD='get' TARGET = 'main' ACTION = "main.php">
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='U_nav_form' METHOD='get' TARGET = 'main' ACTION = "units.php">
<INPUT TYPE='hidden' 	NAME='id' VALUE=''>
<INPUT TYPE='hidden' 	NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' 	NAME='view' VALUE='true'>
</FORM>
<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>">
<INPUT TYPE='hidden' NAME='func' VALUE='list'>
<INPUT TYPE='hidden' NAME='lines' value='<?php print $lines; ?>'>
</FORM>
</BODY></HTML>

