<?php 
/* Change log - email_lists.php
8/28/13	New File - accessed from Config to set configure Mailing List members;	
*/
error_reporting(E_ALL);
@session_start();
session_write_close();
require_once('./incs/functions.inc.php');	
if((empty($_REQUEST)) || ((!empty($_GET)) && (!isset($_GET['func'])))) {
	exit();
	}
	
$func = (isset($_GET['func'])) ? $_GET['func'] : 0;
$id = (isset($_GET['id'])) ? strip_tags($_GET['id']) : 0 ;

function get_mailgroup_name($theid) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` WHERE `id` = " . $theid;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_ret = $row['name'];
	return $the_ret;
	}
	
function get_email_from_contacts($theid) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` WHERE `id` = " . $theid;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = $row['name'] . "(" . $row['email'] . ")";
		} else {
		$the_ret = "";
		}		
	return $the_ret;
	}
	
function get_email_from_responder($theid) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $theid;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = $row['handle'] . "(" . $row['contact_via'] . ")";
		} else {
		$the_ret = "";
		}
	return $the_ret;
	}

if(!empty($_POST)) {
	if ($_POST['frm_formname'] == 'edit') {
		if(array_key_exists('frm_remove', $_POST) && $_POST['frm_remove'] == "yes") {
			$theEmail = (intval($_POST['frm_contacts']) != 0) ? get_email_from_contacts(intval($_POST['frm_contacts'])) : get_email_from_responder(intval($_POST['frm_responder']));
			$query = "DELETE FROM $GLOBALS[mysql_prefix]mailgroup_x WHERE `id`=" . $_POST['frm_id'];
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$caption = "Entry  <B><I>" . stripslashes_deep($theEmail) . "</I></B> has been deleted from the mailing list <B><I>" . get_mailgroup_name($_POST['frm_mailgroup']) . "</I></B><BR /><BR />";	
			} else {
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));		
			$by = $_SESSION['user_id'];
			$query = "UPDATE `$GLOBALS[mysql_prefix]mailgroup_x` SET
				`mailgroup`= " . 	quote_smart(trim($_POST['frm_mailgroup'])) . ",
				`contacts`= " . 	quote_smart(trim($_POST['frm_contacts'])) . ",
				`responder`= " . 	quote_smart(trim($_POST['frm_responder'])) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
			$caption = "<B>The Email List Entry <i> " . get_mailgroup_name($_POST['frm_id']) . "</i>' has been updated </B><BR /><BR />";
			}
		} elseif($_POST['frm_formname'] == 'add') {
		$by = $_SESSION['user_id'];
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]mailgroup_x` (
			`mailgroup`, `contacts`, `responder`)
			VALUES (" .
				quote_smart(trim($_POST['frm_mailgroup'])) . "," .
				quote_smart(trim($_POST['frm_contacts'])) . "," .
				quote_smart(trim($_POST['frm_responder'])) . ");";

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();
		$theEmail = (intval($_POST['frm_contacts']) != 0) ? get_email_from_contacts(intval($_POST['frm_contacts'])) : get_email_from_responder(intval($_POST['frm_responder']));
		$caption = "<B><I>" . $theEmail . " </B></I>has been added to Mailing List <B><I>" . get_mailgroup_name($_POST['frm_mailgroup']) . "</I></B><BR /><BR />";
		}							// end if ($_getgoadd == 'true')
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<HEAD>
		<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW" />
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
		<META HTTP-EQUIV="Expires" CONTENT="0" />
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
		<TITLE>Tickets - Email Lists Configuration</TITLE>
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
		<link rel="shortcut icon" href="favicon.ico" />
	<SCRIPT>
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
		}		// end function ck_frames()	
		
	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}	
		
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}
	</SCRIPT>
	</HEAD>
	<BODY onLoad='ck_frames();'>
	<DIV style='font-size: 14px; position: fixed; top: 150px; left: 100px;'>
	<?php print $caption;?><BR />
	Settings Saved<br /><br />
	<A id='cont_but' class='plain' onMouseover='do_hover(this);' onMouseout='do_plain(this);' style='font-size: 14px;' href="email_lists.php?func=list">Continue</A>		
	</DIV>
	</BODY>
	</HTML>
<?php
	exit();
	}
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<HEAD>
		<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW" />
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
		<META HTTP-EQUIV="Expires" CONTENT="0" />
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
		<TITLE>Tickets - Email Lists Configuration</TITLE>
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
		<link rel="shortcut icon" href="favicon.ico" />
		<STYLE>
			.table_header	{ color: #FFFFFF; text-align: left; height: 20px; border: 1px solid #000000; background: #707070;}	
			.table_hdr_cell { color: #FFFFFF; width: 20%; font-weight: bold; font-size: 16px; border: 1px solid #000000;}
			.table_row		{ color: #000000; text-align: left; height: 15px; border: 1px solid #000000;}	
			.table_cell 	{ width: 20%; font-size: 14px; border: 1px solid #000000; word-wrap: break-word;}			
			.header			{ display: table-cell; color: #000000; width: 5%;}
			.page_heading	{ font-size: 20px; font-weight: bold; text-align: left; background: #707070; color: #FFFFFF;}	
			.page_heading_text { font-size: 20px; font-weight: bold; text-align: left; background: #707070; color: #FFFFFF; width: 50%; display: inline;}
			.button_bar 	{ font-size: 1.2em; text-align: center; display: inline; width: 30%; position: fixed; right:30%; top: 0px;}					
			.buttons 		{ border: 2px outset #FFFFFF; padding: 2px; background-color: #EFEFEF; font-weight: bold; display: inline; cursor: pointer;}	
			.flag 			{ border: 2px outset #707070; background: #CECECE; font-size: 20px; font-weight: bold; display: inline; position: fixed; right:30%; top: 5%;}				
		</STYLE>			
	<SCRIPT>
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
		}		// end function ck_frames()	
	
	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}	

	function myclick(id) {
		document.go_form.id.value=id;
		document.go_form.func.value='edit';
		document.go_form.submit();
		}
		
	function addnew() {
		document.go_form.func.value='add';
		document.go_form.submit();
		}

	function goto_list() {
		document.go_form.func.value='list';
		document.go_form.submit();
		}

	function goto_config() {
		document.can_form.submit();
		}
		
	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}	
		
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

	function validate(theForm) {
		var errmsg = "";
		if(theForm.frm_mailgroup.value == 0) {
			errmsg += "You need to select a Mailgroup (mailing list) for the entry.\n";	
			}
		if((theForm.frm_contacts.value > 0) && (theForm.frm_responder.value > 0)) {
			errmsg += "You can only add one addess to an entry,/n you have selected both from the responder table and the contacts table.\n";
			}
		if((theForm.frm_contacts.value == 0) && (theForm.frm_responder.value == 0)) {
			errmsg += "You haven't selected an email address\n";
			}
		if(errmsg != "") {
			errmsg += "\n\nPlease correct the above and re-submit.\n";
			alert(errmsg);
			return false;
			} else {
			theForm.submit();
			}
		}
			
	</SCRIPT>
	</HEAD>
<?php
	if($func == "edit") {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup_x` WHERE `id` = " . $id . " LIMIT 1";		// 12/18/10
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
?>
		<BODY onLoad='ck_frames();'>

			<DIV id='outer' style='position: absolute; top: 5%; width: 100%; height: 75%; border: 1px solid #FFFFFF;'>
				<DIV class='heading' style='width: 100%; position: absolute; text-align: center;'>EMAIL LIST ADMIN</DIV>
				<DIV id='left_col' style='width: 45%; position: absolute; top: 60px; left: 2%; border: 3px outset #CECECE;'>
					<FORM NAME='edit_form' METHOD="post" ACTION="<?php print basename(__FILE__);?>">
					<TABLE style='width: 100%;'>
						<TR class='spacer'>
							<TD class='spacer' COLSPAN=99>&nbsp;</TD>
						</TR>
						<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Email List:</TD>
							<TD CLASS="td_data">
								<SELECT NAME="frm_mailgroup" onChange = "this.value=JSfnTrim(this.value)">
									<OPTION VALUE=0>Select</OPTION>
<?php
									$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` ORDER BY `id` ASC";
									$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
									while ($row_mailgroup = stripslashes_deep(mysql_fetch_assoc($result))) {
										$sel = ($row['mailgroup'] == $row_mailgroup['id']) ? "SELECTED" : "";
										print "\t<OPTION {$sel} VALUE='{$row_mailgroup['id']}'>{$row_mailgroup['name']} </OPTION>\n";
										}
?>
								</SELECT>
							</TD>
						</TR>	
						<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">From Contacts:</TD>
							<TD CLASS="td_data">
								<SELECT NAME="frm_contacts" onChange = "this.value=JSfnTrim(this.value)">
									<OPTION VALUE=0>Select</OPTION>
<?php
									$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` ORDER BY `id` ASC";
									$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
									while ($row_contact = stripslashes_deep(mysql_fetch_assoc($result))) {
										$sel = ($row['contacts'] == $row_contact['id']) ? "SELECTED" : "";
										print "\t<OPTION {$sel} VALUE='{$row_contact['id']}'>{$row_contact['name']} ({$row_contact['email']}) </OPTION>\n";
										}
?>
								</SELECT>
							</TD>
						</TR>	
						<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">From Responders:</TD>
							<TD CLASS="td_data">
								<SELECT NAME="frm_responder" onChange = "this.value=JSfnTrim(this.value)">
									<OPTION VALUE=0>Select</OPTION>
<?php
									$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id` ASC";		// 12/18/10
									$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
									while ($row_responder = stripslashes_deep(mysql_fetch_assoc($result))) {
										$sel = ($row['responder'] == $row_responder['id']) ? "SELECTED" : "";
										print "\t<OPTION {$sel} VALUE='{$row_responder['id']}'>{$row_responder['handle']} ({$row_responder['contact_via']}) </OPTION>\n";
										}
?>
								</SELECT>
							</TD>
						</TR>
						<TR class='spacer'>
							<TD class='spacer' COLSPAN=99>&nbsp;</TD>
						</TR>	
						<TR CLASS="odd" VALIGN='baseline'>
							<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Delete from mailing list.">Remove Entry</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove">
						</TR>
					</TABLE>
				</DIV>
				<DIV id='right_col' style='width: 40%; height: 500px; position: absolute; top: 60px; right: 2%; border: 3px outset #DEDEDE; background-color: #F0F0F0;'>
					<DIV class='heading' style='width: 100%;'>HELP</DIV>
					<DIV style='width: 100%; word-wrap: break-word;'>
					On the left is the form to edit an entry in a Email List.<BR /><BR />
					Using the select menus, select the email address you want to edit, either from the contacts list or from configured responders.<BR />
					You can only chose <B>one</B> email address from the select menus, if you select <B>something from both</B> you will get an error when you submit the form and will need to revise and resubmit.
					</DIV>
				</DIV>
				<DIV style='width: 100%; text-align: center; position: absolute; bottom: 10%; left: 20%;'>
					<SPAN id='bsub_but' class='plain' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='validate(document.edit_form);'>Submit</SPAN>
					<SPAN id='can_but' class='plain' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='goto_list();'>Back</SPAN>
				</DIV>
				<INPUT TYPE="hidden" NAME="frm_id" VALUE=<?php print $row['id'];?>>
				<INPUT TYPE="hidden" NAME="frm_formname" VALUE="edit">
				<INPUT TYPE='hidden' NAME='func' VALUE='list'>
				</FORM>			
			</DIV>
<?php	
		} elseif($func == "add") {
?>
		<BODY onLoad='ck_frames();'>

			<DIV id='outer' style='position: absolute; top: 5%; width: 100%; height: 75%; border: 1px solid #FFFFFF;'>
				<DIV class='heading' style='width: 100%; position: absolute; text-align: center;'>EMAIL LIST ADMIN</DIV>
				<DIV id='left_col' style='width: 45%; position: absolute; top: 60px; left: 2%; border: 3px outset #CECECE;'>
					<FORM NAME='add_form' METHOD="post" ACTION="<?php print basename(__FILE__);?>">
					<TABLE style='width: 100%;'>
						<TR class='spacer'>
							<TD class='spacer' COLSPAN=99>&nbsp;</TD>
						</TR>
						<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Email List:</TD>
							<TD CLASS="td_data">
								<SELECT NAME="frm_mailgroup" onChange = "this.value=JSfnTrim(this.value)">
									<OPTION VALUE=0>Select</OPTION>
<?php
									$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` ORDER BY `id` ASC";
									$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
									while ($row_mailgroup = stripslashes_deep(mysql_fetch_assoc($result))) {
										print "\t<OPTION VALUE='{$row_mailgroup['id']}'>{$row_mailgroup['name']} </OPTION>\n";
										}
?>
								</SELECT>
							</TD>
						</TR>	
						<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">From Contacts:</TD>
							<TD CLASS="td_data">
								<SELECT NAME="frm_contacts" onChange = "this.value=JSfnTrim(this.value)">
									<OPTION VALUE=0>Select</OPTION>
<?php
									$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` ORDER BY `id` ASC";
									$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
									while ($row_contact = stripslashes_deep(mysql_fetch_assoc($result))) {
										print "\t<OPTION VALUE='{$row_contact['id']}'>{$row_contact['name']} {$row_contact['email']} </OPTION>\n";
										}
?>
								</SELECT>
							</TD>
						</TR>	
						<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">From Responders:</TD>
							<TD CLASS="td_data">
								<SELECT NAME="frm_responder" onChange = "this.value=JSfnTrim(this.value)">
									<OPTION VALUE=0>Select</OPTION>
<?php
									$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id` ASC";		// 12/18/10
									$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
									while ($row_responder = stripslashes_deep(mysql_fetch_assoc($result))) {
										print "\t<OPTION VALUE='{$row_responder['id']}'>{$row_responder['handle']} {$row_responder['contact_via']} </OPTION>\n";
										}
?>
								</SELECT>
							</TD>
						</TR>	
						<TR class='spacer'>
							<TD class='spacer' COLSPAN=99>&nbsp;</TD>
						</TR>
					</TABLE>
				</DIV>
				<DIV id='right_col' style='width: 40%; height: 500px; position: absolute; top: 60px; right: 2%; border: 3px outset #DEDEDE; background-color: #F0F0F0;'>
					<DIV class='heading' style='width: 100%;'>HELP</DIV>
					<DIV style='width: 100%; word-wrap: break-word;'>
					On the left is the form to add a new email address to an existing Email List.<BR /><BR />
					Using the select menus, select first the Email List that this entry is to be added to and then select the email address you want to add, either from the contacts list or from configured responders.<BR />
					You can only chose <B>one</B> email address from the select menus, if you select <B>something from both</B> you will get an error when you submit the form and will need to revise and resubmit.
					</DIV>
				</DIV>
				<DIV style='width: 100%; text-align: center; position: absolute; bottom: 10%; left: 20%;'>
					<SPAN id='bsub_but' class='plain' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='validate(document.add_form);'>Submit</SPAN>				
					<SPAN id='back_but' class='plain' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='goto_list();'>Back</SPAN>
				</DIV>
				<INPUT TYPE="hidden" NAME="frm_formname" VALUE="add">
				<INPUT TYPE='hidden' NAME='func' VALUE='list'>
				</FORM>			
			</DIV>
<?php	
		} elseif($func == "list") {
?>
		<BODY onLoad='ck_frames();'>

			<DIV id='outer' style='position: absolute; top: 5%; width: 100%; height: 75%; border: 1px solid #FFFFFF;'>
				<DIV class='heading' style='width: 100%; position: absolute; text-align: center;'>EMAIL LIST ADMIN</DIV>
				<DIV id='left_col' style='width: 45%; position: absolute; top: 60px; left: 2%; border: 3px outset #CECECE;'>
					<TABLE style='width: 100%;'>
						<TR class='heading'>
							<TH class='heading' style='text-align: left;'>Email List</TH>
							<TH class='heading' style='text-align: left;'>Contact</TH>
							<TH class='heading' style='text-align: left;'>Responder</TH>
						</TR>
						<TR class='spacer'>
							<TD class='spacer' COLSPAN='3'>&nbsp;</TD>
						</TR>
<?php
						$class='odd';
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup_x` ORDER BY `mailgroup` ASC";		// 12/18/10
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
?>
							<TR VALIGN="baseline" CLASS="<?php print $class;?>" onClick="myclick('<?php print $row['id'];?>');">
								<TD CLASS="td_data"><?php print get_mailgroup_name($row['mailgroup']);?></TD>	
								<TD CLASS="td_data"><?php print get_email_from_contacts($row['contacts']);?></TD>	
								<TD CLASS="td_data"><?php print get_email_from_responder($row['responder']);?></TD>
							<TR>
<?php
							$class = ($class == 'even') ? 'odd' : 'even';
							}						
?>
					</TABLE>

				</DIV>

				<DIV id='right_col' style='width: 40%; height: 500px; position: absolute; top: 60px; right: 2%; border: 3px outset #DEDEDE; background-color: #F0F0F0;'>
					<DIV class='heading' style='width: 100%;'>HELP</DIV>
					<DIV style='width: 100%; word-wrap: break-word;'>
					On the left is a list of all Mailing list entries showing the email address and the Mailing list it belongs to.<BR /><BR /> Click <B>Add New</B> and then fill out the resulting form and submit,
					to add a new entry to an existing Mailing list or <B>Back to Config</B> to return to the Tickets main configuration page.
					You can click on an entry in the list to <B>edit</B> it.
					</DIV>
				</DIV>
				<DIV style='width: 100%; text-align: center; position: absolute; bottom: 10%; left: 20%;'>
					<SPAN id='add_but' class='plain' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='addnew();'>Add New</SPAN>
					<SPAN id='add_but' class='plain' onMouseover='do_hover(this);' onMouseout='do_plain(this);' onClick='goto_config();'>Back to Config</SPAN>
				<DIV>
			</DIV>

<?php	
		} else {
?>
		<BODY onLoad='ck_frames();'>		
		<p>Not Called correctly</p>
<?php
		}
?>
<FORM NAME='go_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
<INPUT TYPE='hidden' NAME='func' VALUE=''>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>
<FORM NAME='can_form' METHOD='get' ACTION='config.php'>
</FORM>
</BODY>
</HTML>