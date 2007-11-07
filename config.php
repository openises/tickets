<?php 
	require_once('functions.inc.php');
	require_once('config.inc.php');
	require_once('responders.php');
	do_login(basename(__FILE__));
//	foreach ($_POST as $VarName=>$VarValue) {echo "POST:$VarName => $VarValue, <BR />";};
//	foreach ($_GET as $VarName=>$VarValue) 	{echo "GET:$VarName => $VarValue, <BR />";};
//	echo "<BR/>";

extract($_GET);
if (!isset($func)) {$func = "summ";}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Configuration Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT>
		function ck_frames() {
			if(self.location.href==parent.location.href) {
				self.location.href = 'index.php';
				}
			}		// end function ck_frames()
	</SCRIPT>

<?php
	print "<SCRIPT>\n";
	print "var user = '";
	print $_SESSION['user_name'];
	print "'\n";
	print "\nvar level = '" . get_level_text ($_SESSION['level']) . "'\n";
?>	
//	parent.frames["upper"].document.getElementById("whom").innerHTML  = user;
//	parent.frames["upper"].document.getElementById("level").innerHTML  = level;

	function do_Cancel() {
		window.location = "config.php?func=responder";
		}

	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && document.getElementById) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && document.getElementById) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}
	
	function whatBrows() {					//Displays the generic browser type
		window.alert("Browser is : " + type);
		}
	
	function ShowLayer(id, action){												// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
		if (type=="IE") 				eval("document.all." + id + ".style.display='" + action + "'");  	// id is the span/layer, action is either hidden or visible
		if (type=="NN") 				eval("document." + id + ".display='" + action + "'");
		if (type=="MO" || type=="OP") 	eval("document.getElementById('" + id + "').style.display='" + action + "'");
		}
	
	function hideit (elid) {
		ShowLayer(elid, "none");
		}
	
	function showit (elid) {
		ShowLayer(elid, "block");
		}

	function validate_cen(theForm) {			// Map center  validation	
		var errmsg="";
		if (theForm.frm_lat.value=="")			{errmsg+="\tMap center is required.\n";}
		if (theForm.frm_map_caption.value=="")	{errmsg+="\tMap caption is required.\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			theForm.frm_lat.disabled = false;
			theForm.frm_lng.disabled = false;
			theForm.frm_zoom.disabled = false;
			return true;
			}
		}				// end function validate(theForm)

	function validate_res(theForm) {			// Responder form contents validation	
//		alert (theForm.frm_mobile.checked);
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				if(confirm("Please confirm removing this Unit")) 	{return true;}
				else 													{return false;}
				}
			}
		var errmsg="";
		var got_type = false;
		for (i=0; i<theForm.frm_type.length; i++){
			if (theForm.frm_type[i].checked) {	got_type = true;	}
			}
		if (theForm.frm_name.value=="")				{errmsg+="\tUnit NAME is required.\n";}
		if (theForm.frm_descr.value=="")			{errmsg+="\tUnit DESCRIPTION is required.\n";}
		if (theForm.frm_status.value=="")			{errmsg+="\tUnit STATUS is required.\n";}
		if (!got_type)								{errmsg+="\tUnit TYPE is required.\n";}
		if (!theForm.frm_mobile.checked) {		// fixed
			theForm.frm_lat.disabled=false;
			if (theForm.frm_lat.value == "") 		{errmsg+= "\tMAP LOCATION is required\n";}
			theForm.frm_lat.disabled=true;
			}
		else {										// mobile
			if (theForm.frm_callsign.value=="")		{errmsg+="\tCALLSIGN is required.\n";}
			else {									// not empty
				for (i=0; i< calls.length;i++) {	// duplicate?
					if (calls[i] == theForm.frm_callsign.value) {
						errmsg+="\tDuplicate CALLSIGN - not permitted.\n";
						break;
						}			
					}		// end for (...)
				}			// end else {}
			}				// end mobile
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			theForm.frm_lat.disabled = false;
			theForm.frm_lng.disabled = false;
			return true;
			}
		}				// end function validate(theForm)

	function validate_user(theForm) {			// Responder form contents validation
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				if(confirm("Please confirm removing this Unit")) 	{return true;}
				else 														{return false;}
				}
			}
		var errmsg="";
		var got_level = false;
		for (i=0; i<theForm.frm_level.length; i++){
			if (theForm.frm_level[i].checked) {	got_level = true;	}
			}
		if (theForm.frm_user.value=="")				{errmsg+="\tUserID is required.\n";}
		if (theForm.frm_passwd.value=="")			{errmsg+="\tPASSWORD is required.\n";}
		if (theForm.frm_passwd_confirm.value=="")	{errmsg+="\tCONFIRM PASSWORD is required.\n";}
		if (theForm.frm_passwd.value!=theForm.frm_passwd_confirm.value) {errmsg+="\tPASSWORD and CONFIRM PASSWORD fail to match.\n";}
		if (!got_level)								{errmsg+="\tUser LEVEL is required.\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			return true;
			}
		}				// end function validate(theForm)

	function validate_set(theForm) {			// limited form contents validation  
		var errmsg="";
		if (theForm.gmaps_api_key.value.length!=86)			{errmsg+= "\tInvalid GMaps API key\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			return true;
			}
		}				// end function validate(theForm)

	function add_res () {		// turns on add responder form
		showit('res_add_form'); 
		hideit('tbl_responders');
		hideIcons();			// hides responder icons
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
		
	function hideIcons() {
		map.clearOverlays();
		}				// end function hideicons() 

	function do_lat (lat) {
		document.forms[0].frm_lat.disabled=false;
		document.forms[0].frm_lat.value=lat.toFixed(6);
		document.forms[0].frm_lat.disabled=true;
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.disabled=false;
		document.forms[0].frm_lng.value=lng.toFixed(6);
		document.forms[0].frm_lng.disabled=true;
		}
	
	function collect(){				// constructs a string of id's for deletion
		var str = sep = "";
		for (i=0; i< document.del_Form.elements.length; i++) {
			if (document.del_Form.elements[i].type == 'checkbox' && (document.del_Form.elements[i].checked==true)) {
				str += (sep + document.del_Form.elements[i].name.substring(1));		// drop T
				sep = ",";
				}
			}
		document.del_Form.idstr.value=str;	
		}
		
	function all_ticks(bool_val) {									// set checkbox = true/false
		for (i=0; i< document.del_Form.elements.length; i++) {
			if (document.del_Form.elements[i].type == 'checkbox') {
				document.del_Form.elements[i].checked = bool_val;		
				}
			}			// end for (...)
		}				// end function all_ticks()
		
	</SCRIPT>
	

<?php
	switch ($func){

		case 'notify': 
			print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
		if (array_key_exists('id', ($_GET))) {
			print "<FONT CLASS='header'>Add Notify Event</FONT><BR /><BR />";
			if (!get_variable('allow_notify')) print "<FONT CLASS='warn'>Warning: Notification is disabled by administrator</FONT><BR /><BR />"; 
?>
			<TABLE BORDER="0">
			<FORM METHOD="POST" ACTION="config.php?func=notify&add=true">
			<TR CLASS='even'><TD CLASS="td_label">Ticket:</TD><TD ALIGN="right"><A HREF="main.php?id=<?php print $_GET['id'];?>">#<?php print $_GET['id'];?></A></TD></TR>
			<TR CLASS='odd'><TD CLASS="td_label">Email Address:</TD><TD><INPUT MAXLENGTH="70" SIZE="40" TYPE="text" NAME="frm_email"></TD></TR>
			<TR CLASS='even'><TD CLASS="td_label">Execute:</TD><TD><INPUT MAXLENGTH="150" SIZE="40" TYPE="text" NAME="frm_execute"></TD></TR>
			<TR CLASS='odd'></TR><TD CLASS="td_label">On Patient/Action Change:</TD><TD ALIGN="right"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_on_action"></TD></TR>
			<TR CLASS='even'><TD CLASS="td_label">On Ticket Change: &nbsp;&nbsp;</TD><TD ALIGN="right"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_on_ticket"></TD></TR>
				<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $_GET['id'];?>">
			<TR CLASS='odd'><TD></TD><TD ALIGN="center"><INPUT TYPE='button' VALUE='Cancel'  onClick='document.can_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit"></TD></TR>
			</FORM></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
			</BODY>
			</HTML>
<?php
			exit();
			}
		else if ((array_key_exists('save', ($_GET))) && ($_GET['save']== 'true')) {
			for ($i = 0; $i<count($_POST["frm_id"]); $i++) {

				if (isset($_POST['frm_delete'][$i])) {
					$msg = "Notify deleted!";					// pre-set
					$query = "DELETE from $GLOBALS[mysql_prefix]notify WHERE id='".$_POST['frm_id'][$i]."'";
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					}
				else {					//email validation check
					$msg = "Notify update complete.";			// pre-set

					$email = validate_email($_POST['frm_email'][$i]);
					$email_address = $_POST['frm_email'][$i];
					if (!$email['status']) {
						print "<FONT CLASS='warn'>Error: email validation failed for '$email_address', $email[msg]. Go back and check this email address.</FONT>";
						exit();
						}
					
					$query = "UPDATE $GLOBALS[mysql_prefix]notify SET execute_path='".$_POST['frm_execute'][$i]."', email_address='".$_POST['frm_email'][$i]."',on_action='".$_POST['frm_on_action'][$i]."',on_ticket='".$_POST['frm_on_ticket'][$i]."' WHERE id='".$_POST['frm_id'][$i]."'";
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					}
				}
			
			if (!get_variable('allow_notify')) print "<FONT CLASS=\"warn\">Warning: Notification is disabled by administrator</FONT><BR /><BR />";
			print "<FONT CLASS='header'>$msg</FONT><BR /><BR />";
			}

		else if ((array_key_exists('add', ($_GET))) && ($_GET['add']== 'true')) {	//email validation check
			$email = validate_email($_POST['frm_email']);
			if (!$email['status']) {
				print "<FONT CLASS='warn'>Error: email validation failed for '" . $_POST['frm_email'] . "', " . $email['msg'] . ". Go back and check this email address.</FONT>";
				exit();
				}
		
			$on_ticket = (isset($_POST['frm_on_ticket']))? $_POST['frm_on_ticket']:0 ;
			$on_action = (isset($_POST['frm_on_action']))? $_POST['frm_on_action']:0 ;
			$query = "INSERT INTO $GLOBALS[mysql_prefix]notify SET ticket_id='$_POST[frm_id]',user='$_SESSION[user_id]',email_address='$_POST[frm_email]',execute_path='$_POST[frm_execute]',on_action='$on_action',on_ticket='$on_ticket'";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (!get_variable('allow_notify')) print "<FONT CLASS='warn'>Warning: Notification is disabled by administrator</FONT><BR /><BR />";
			print "<FONT SIZE='3'><B>Notify added.</B></FONT><BR /><BR />";
			}
		else {
			if ($_SESSION['user_id'])
				$query = "SELECT * FROM $GLOBALS[mysql_prefix]notify WHERE user='$_SESSION[user_id]'";
			else
				$query = "SELECT * FROM $GLOBALS[mysql_prefix]notify";
				
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);

			if (mysql_num_rows($result)) {
				print "<FONT CLASS='header'>Update Notifies<BR /><BR />";
				if (!get_variable('allow_notify')) print "<FONT CLASS=\"warn\">Warning: Notification is disabled by administrator</FONT><BR /><BR />";
				print '<TABLE BORDER="0"><FORM METHOD="POST" ACTION="config.php?func=notify&save=true">';
				print "<TR CLASS='even'><TD CLASS='td_label'>Ticket</TD><TD CLASS=\"td_label\">Email</TD>";
				print '<TD CLASS="td_label">Execute</B></TD><TD CLASS="td_label">On Action</TD><TD CLASS="td_label">On Ticket Change</TD><TD CLASS="td_label">Delete</TD></TR>';
			
				$i = 0;
				while($row = stripslashes_deep(mysql_fetch_array($result))) {
					print "\n<TR CLASS='" .$colors[$i%2] . "'><TD><A HREF='main.php?id=" .  $row['ticket_id'] . "'>#" . $row['ticket_id'] . "</A></FONT></TD>\n";
					print "<TD><INPUT MAXLENGTH=\"70\" SIZE=\"32\" VALUE=\"" . $row['email_address'] . "\" TYPE=\"text\" NAME=\"frm_email[$i]\"></TD>\n";
					print "<TD><INPUT MAXLENGTH=\"150\" SIZE=\"40\" TYPE=\"text\" VALUE=\"" . $row['execute_path'] . "\" NAME=\"frm_execute[$i]\"></TD>\n";
					print "<TD ALIGN='right'><INPUT TYPE='checkbox' VALUE='1' NAME='frm_on_action[$i]'"; print $row['on_action'] ? " checked></TD>\n" : "></TD>\n";
					print "<TD ALIGN='right'><INPUT TYPE='checkbox' VALUE='1' NAME='frm_on_ticket[$i]'"; print $row['on_ticket'] ? " checked></TD>\n" : "></TD>\n";
					print "<TD ALIGN='right'><INPUT TYPE='checkbox' VALUE='1' NAME='frm_delete[$i]'></TD>\n";
					print "<INPUT TYPE='hidden' NAME='frm_id[$i]' VALUE='" . $row['id'] . "'></TR>\n";
					$i++;
					}
				print "<TR CLASS='" .$colors[$i%2]  ."'><TD COLSPAN=99 ALIGN='center'><INPUT TYPE='button' VALUE='Cancel'  onClick='document.can_Form.submit();' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<INPUT TYPE='reset' VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Continue'></TD></TR></FORM>";
				print "</TABLE><BR />";
?>
				<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
				</BODY>
				</HTML>
<?php
				
				exit();
				}
			else {
				print '<B>No notifies to update.</B><BR /><BR />';
				}
			}
    break;

case 'profile' :					//update profile
		print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
		$get_go = (array_key_exists('go', ($_GET)))? $_GET['go']  : "" ;
		if ($get_go == 'true') {			//check passwords
			$set_passwd = FALSE;
			$frm_sort_desc = array_key_exists('frm_sort_desc', ($_POST))? 1: 0 ;	// checkbox handling
			if($_POST['frm_passwd'] != '') {
				if($_POST['frm_passwd'] != $_POST['frm_passwd_confirm']) {
					print "<FONT CLASS='warn'>Passwords don't match. Click 'back' and try again.</font>";
					exit();
					}
				else {
					$set_passwd = TRUE;
					}
				}
			else if($_POST['frm_passwd_confirm'] != '') {	
				print '<FONT CLASS="warn">BOTH password fields are required. Password is not updated.</FONT><BR />';
				}
			if(!$set_passwd) {		// skip password update
				$query = "UPDATE $GLOBALS[mysql_prefix]user SET info='$_POST[frm_info]',email='$_POST[frm_email]',sortorder='$_POST[frm_sortorder]',sort_desc='$frm_sort_desc',ticket_per_page='$_POST[frm_ticket_per_page]' WHERE id='$_SESSION[user_id]'";
				}
			else {
				$query = "UPDATE $GLOBALS[mysql_prefix]user SET passwd=PASSWORD('$_POST[frm_passwd]'),info='$_POST[frm_info]',email='$_POST[frm_email]',sortorder='$_POST[frm_sortorder]',sort_desc='$frm_sort_desc',ticket_per_page='$_POST[frm_ticket_per_page]' WHERE id='$_SESSION[user_id]'";
				}
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			reload_session();
			print '<B>Your profile has been updated.</B><BR /><BR />';
			}
		else {
			$query = "SELECT id FROM $GLOBALS[mysql_prefix]user WHERE id='" . $_SESSION['user_id'] . "'";
			if ($_SESSION['user_id'] < 0 OR check_for_rows($query) == 0) {
				print __LINE__ . " Invalid user id '$_SESSION[user_id]'.";
				exit();
				}

			$query	= "SELECT * FROM $GLOBALS[mysql_prefix]user WHERE id='$_SESSION[user_id]'";
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= mysql_fetch_array($result);

			?>
			<FONT CLASS="header">Edit My Profile</FONT><BR /><BR /><TABLE BORDER="0">
			<FORM METHOD="POST" ACTION="config.php?func=profile&go=true"><INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'];?>">
			<TR CLASS="even"><TD CLASS="td_label">New Password:</TD><TD><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd"> &nbsp;&nbsp;<B>Confirm: </B><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd_confirm"></TD></TR>
			<TR CLASS="odd"><TD CLASS="td_label">Email:</TD><TD><INPUT SIZE="47" MAXLENGTH="255" TYPE="text" VALUE="<?php print $row['email'];?>" NAME="frm_email"></TD></TR>
			<TR CLASS="even"><TD CLASS="td_label">Info:</TD><TD><INPUT SIZE="47" MAXLENGTH="255" TYPE="text" VALUE="<?php print $row['info'];?>" NAME="frm_info"></TD></TR>
			<!-- <TR><TD CLASS="td_label">Show reporting actions:</TD><TD ALIGN="right"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_reporting" <?php if($row['reporting']) print " checked";?>></TD></TR> -->
			<TR CLASS="odd"><TD CLASS="td_label">Tickets per page:</TD><TD><INPUT SIZE="47" MAXLENGTH="3" TYPE="text" VALUE="<?php print $row['ticket_per_page'];?>" NAME="frm_ticket_per_page"></TD></TR>
			<TR CLASS="even"><TD CLASS="td_label">Sort By:</TD><TD><SELECT NAME="frm_sortorder">
			<OPTION value="date" <?php if($row['sortorder']=='date') print " selected";?>>Date</OPTION>
			<OPTION value="description" <?php if($row['sortorder']=='description') print " selected";?>>Description</OPTION>
			<OPTION value="affected" <?php if($row['sortorder']=='affected') print " selected";?>>Affected</OPTION>
			</SELECT>&nbsp; Descending <INPUT TYPE="checkbox" value="1" name="frm_sort_desc" <?php if ($row['sort_desc']) print "checked";?>></TD></TR>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $_SESSION['user_id'];?>">
			<TR CLASS="odd"><TD></TD><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel"  onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Apply"></TD></TR>
			</FORM></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
			</BODY>
			</HTML>
<?php
			exit();
			}
    break;

case 'optimize' :
		print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
		if (is_administrator())	{
			optimize_db();
			print '<FONT CLASS="header">Database optimized.</FONT><BR /><BR />';
			}
		else
			print '<FONT CLASS="warn">Not authorized.</FONT><BR /><BR />';
    break;

case 'reset' :
		print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
		if (is_administrator())		{
			if ($_GET['auth'] != 'true') {
				?><FONT CLASS="header">Reset Database</FONT><BR />This operation requires confirmation by entering "yes" into this box.<BR />
				<FONT CLASS="warn"><BR />Warning! This deletes all previous tickets, actions, patients, users, resets<BR /> settings and creates a default admin user.</FONT><BR /><BR />
				<TABLE BORDER="0"><FORM METHOD="POST" ACTION="config.php?func=reset&auth=true">
				<!-- <TR><TD CLASS="td_label">Purge closed tickets:</TD><TD ALIGN="right"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_purge"></TD></TR> -->
				<TR><TD CLASS="td_label">Reset tickets/actions:</TD><TD ALIGN="right"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_ticket"></TD></TR>
				<TR><TD CLASS="td_label">Reset users:</TD><TD ALIGN="right"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_user"></TD></TR>
				<TR><TD CLASS="td_label">Reset settings:</TD><TD ALIGN="right"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_settings"></TD></TR>
				<TR><TD CLASS="td_label">Really reset database? &nbsp;&nbsp;</TD><TD><INPUT MAXLENGTH="20" SIZE="40" TYPE="text" NAME="frm_confirm"></TD></TR>
				<TR><TD></TD><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel"  onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Apply"></TD></TR>
				</FORM></TABLE>
				<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
				</BODY>
				</HTML>
<?php
				exit();
				}
			else {
				if ($_POST['frm_confirm'] == 'yes')
					reset_db($_POST['frm_user'],$_POST['frm_ticket'],$_POST['frm_settings'],$_POST['frm_purge']);
				else
					print '<FONT CLASS="warn">Not authorized or confirmation failed.</FONT><BR /><BR />'; 
				}
			}
		else
			print '<FONT CLASS="warn">Not authorized.</FONT><BR /><BR />';
    break;

case 'settings' :
		print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
		if (is_administrator())	{
			if((isset($_GET))&& (isset($_GET['go']))&& ($_GET['go'] == 'true')) {
				foreach ($_POST as $VarName=>$VarValue) {
				
					$query = "UPDATE $GLOBALS[mysql_prefix]settings SET `value`=". quote_smart($VarValue)." WHERE `name`='".$VarName."'";
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					}		
	
				print '<FONT CLASS="header">Settings saved.</FONT><BR /><BR />';
				}
			else {
				$evenodd = array ("even", "odd");
				print '<FONT CLASS="header">Edit Settings</FONT>  (mouseover caption for help information)<BR /><BR /><TABLE BORDER="0"><FORM METHOD="POST" NAME= "set_Form"  onSubmit="return validate_set(document.set_Form);" ACTION="config.php?func=settings&go=true">';
				$counter = 0;
				$result = mysql_query("SELECT * FROM $GLOBALS[mysql_prefix]settings ORDER BY name") or do_error('config.php::list_settings', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				while($row = stripslashes_deep(mysql_fetch_array($result))) {
					if ($row['name']{0} <> "_" ) {								// hide these
						$capt = str_replace ( "_", " ", $row['name']);
						print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD CLASS='td_label'><A HREF='#' TITLE='".get_setting_help($row['name'])."'>$capt</A>: &nbsp;</TD>";
						print "<TD><INPUT MAXLENGTH='90' SIZE='90' TYPE='text' VALUE='" . $row['value'] . "' NAME='" . $row['name'] . "'></TD></TR>\n";

						$counter++;
						}
					}		// str_replace ( search, replace, subject)
				
				print "<TR><TD></TD><TD ALIGN='center'>
					<INPUT TYPE='button' VALUE='Cancel'  onClick='document.can_Form.submit();' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Apply'></TD></TR></FORM></TABLE>";
?>
				<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
				</BODY>
				</HTML>
<?php
				exit();
				}
			}
		else
			print '<FONT CLASS="warn">Not authorized.</FONT><BR /><BR />';
    break;

case 'user' :
	print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
	if ((array_key_exists('id', ($_GET))) && ($_GET['id'] != '')) {
		if (is_administrator()) {
			$id = $_GET['id'];
			if ($id < 0 OR check_for_rows("SELECT id FROM $GLOBALS[mysql_prefix]user WHERE id='$id'") == 0) {
				print __LINE__ . " Invalid user id '$id'.";
				exit();
				}

			$query	= "SELECT * FROM $GLOBALS[mysql_prefix]user WHERE id='$id' LIMIT 1";
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= mysql_fetch_array($result);

?>
			<FONT CLASS="header">Edit User</FONT><BR /><BR /><TABLE BORDER="0">
			<FORM METHOD="POST" NAME = "user_add_Form" onSubmit="return validate_user(document.user_add_Form);" ACTION="config.php?func=user&edit=true"><INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $id;?>">
			<TR CLASS="even"><TD CLASS="td_label">User ID:</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="text" VALUE="<?php print $row['user'];?>" NAME="frm_user"></TD></TR>
			<TR CLASS="odd"><TD CLASS="td_label">Password:</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="password" NAME="frm_passwd"> &nbsp;&nbsp;<B>Confirm: </B><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd_confirm"></TD></TR>
			<TR CLASS="even"><TD CLASS="td_label">Callsign:</TD><TD><INPUT SIZE="20" MAXLENGTH="20" TYPE="text" VALUE="<?php print $row['callsign'];?>" NAME="frm_callsign"></TD></TR>
			<TR CLASS="odd"><TD CLASS="td_label">Info:</TD><TD><INPUT SIZE="47" MAXLENGTH="255" TYPE="text" VALUE="<?php print $row['info'];?>" NAME="frm_info"></TD></TR>
			<TR CLASS="even"><TD CLASS="td_label">Email:</TD><TD><INPUT SIZE="47" MAXLENGTH="47" TYPE="text" VALUE="<?php print $row['email'];?>" NAME="frm_email"></TD></TR>
			<TR CLASS="odd"><TD CLASS="td_label">Level:</TD><TD>
<?php
			$checked = (intval($row['level'])==intval($GLOBALS['LEVEL_USER']))?			"checked":"" ;
			print "<INPUT TYPE='radio' NAME='frm_level' VALUE='" . $GLOBALS['LEVEL_USER'] . 		"' $checked> User<BR />\n";
			$checked = (intval($row['level'])==intval($GLOBALS['LEVEL_GUEST']))? 			"checked":"" ;
			print "<INPUT TYPE='radio' NAME='frm_level' VALUE='" . $GLOBALS['LEVEL_GUEST'] . 		"' $checked> Guest<BR />\n";
			$checked = (intval($row['level'])==intval($GLOBALS['LEVEL_ADMINISTRATOR']))? 	"checked":"" ;
			print "<INPUT TYPE='radio' NAME='frm_level' VALUE='" . $GLOBALS['LEVEL_ADMINISTRATOR'] ."' $checked> Administrator<BR />\n";
?>			
			</TD></TR>
			<TR CLASS="even"><TD CLASS="td_label">Remove User:</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove"></TD></TR>
			<TR CLASS="odd"><TD></TD><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel"  onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Apply"></TD></TR>
			</FORM></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
			</BODY>
			</HTML>
<?php
			exit();	//	
			}
		else
			print '<FONT CLASS="warn">Not authorized.</FONT><BR /><BR />';
		}		// end if ($_GET['id']
	else if ((array_key_exists('edit', ($_GET))) && ($_GET['edit'] == 'true') && 
			(array_key_exists('func', ($_GET))) && ($_GET['func'] == 'user')) {

		if ((array_key_exists('frm_remove', $_POST)) && ($_POST['frm_remove'] == 'yes')) {
//		if ($_POST['frm_remove'] == 'yes') {
			$ctr = 0;
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE owner='$_POST[frm_id]' LIMIT 1";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$ctr += mysql_affected_rows();
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE user='$_POST[frm_id]' LIMIT 1";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$ctr += mysql_affected_rows();
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE user='$_POST[frm_id]' LIMIT 1";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$ctr += mysql_affected_rows();
			if ($ctr > 0) {	
				print '<B>DENIED! - User has active database records.</B><BR /><BR />';			
				}
			else {		// OK - delete user		
				$query = "DELETE FROM $GLOBALS[mysql_prefix]user WHERE id='$_POST[frm_id]' LIMIT 1";
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	
				//delete notifies belonging to user
				$query = "DELETE FROM $GLOBALS[mysql_prefix]notify WHERE user='$_POST[frm_id]'";
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				
				print "<B>User <i>" . $_POST['frm_user'] . "</i> has been deleted from database.</B><BR /><BR />";
				}			
			}
		else {
			if ($_POST['frm_passwd'] == '')
				$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `user`='$_POST[frm_user]', `callsign` = '$_POST[frm_callsign]',`info`='$_POST[frm_info]',`level`='$_POST[frm_level]',`email`='$_POST[frm_email]' WHERE `id`='$_POST[frm_id]'";
			else {
				if($_POST['frm_passwd'] != $_POST['frm_passwd_confirm']) {
					print "Passwords don't match. Try again.<BR />";
					exit();
					}
				$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `user`='$_POST[frm_user]', `callsign`='$_POST[frm_callsign]',`passwd`=PASSWORD('$_POST[frm_passwd]'),`info`='$_POST[frm_info]',`level`='$_POST[frm_level]' WHERE `id`='$_POST[frm_id]'";
				}
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<B>User <I>" .$_POST['frm_user'] . "</I> data has been updated.</B><BR /><BR />";
			}
		}		// end if ($_GET['edit']
		
	else if(($_GET['func'] == 'user') && ($_GET['add'] == 'true')) {
		if (is_administrator()) {
//			if($_GET['go'] == 'true') {
			if ((array_key_exists('go', ($_GET))) && ($_GET['go']== 'true')) {
				if (check_for_rows("SELECT user FROM $GLOBALS[mysql_prefix]user WHERE user='$_POST[frm_user]'")) {
					print "<FONT CLASS=\"warn\">User '$_POST[frm_user]' already exists in database. Go back and try again.</FONT><BR />";
					exit();
					}

				if($_POST['frm_passwd'] == $_POST['frm_passwd_confirm']) {
					$passwd = "PASSWORD(" . quote_smart(trim($_POST['frm_passwd'])) . ")";
					$query = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]user` (`user`,`callsign`,`passwd`,`info`,`level`,`email`,`sortorder`)  VALUES(%s,%s,$passwd,%s,%s,%s,'date')",
								quote_smart(trim($_POST['frm_user'])),
								quote_smart(trim($_POST['frm_callsign'])),
								quote_smart(trim($_POST['frm_info'])),
								quote_smart(trim($_POST['frm_level'])),
								quote_smart(trim($_POST['frm_email'])));

					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					print "<B>User <i>'$_POST[frm_user]'</i> has been added.</B><BR /><BR />";
					}
				else {
					print "Passwords don't match. Please try again.<BR />";
					?>
					<BR /><TABLE BORDER="0">
					<FORM METHOD="POST" NAME = "user_add_Form" onSubmit="return validate_user(document.user_add_Form);" ACTION="config.php?func=user&add=true&go=true">
					<TR CLASS="even"><TD CLASS="td_label">User ID:</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="text" VALUE="<?php print $_POST['frm_user'];?>" NAME="frm_user"></TD></TR>
					<TR CLASS="odd"><TD CLASS="td_label">Password</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="password" NAME="frm_passwd"></TD></TR>
					<TR CLASS="even"><TD CLASS="td_label">Confirm Password: &nbsp;&nbsp;</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="password" NAME="frm_passwd_confirm"></TD></TR>
					<TR CLASS="odd"><TD CLASS="td_label">Callsign:</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="text" VALUE="<?php print $_POST['frm_callsign'];?>" NAME="frm_callsign"></TD></TR>
					<TR CLASS="even"><TD CLASS="td_label">Level:</TD><TD>
						<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_ADMINISTRATOR'];?>" NAME="frm_level" <?php print is_administrator()?"checked":"";?>> Administrator<BR />
						<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_USER'];?>" NAME="frm_level" <?php print is_user()?"checked":"";?>> User<BR />
						<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_GUEST'];?>" NAME="frm_level" <?php print is_guest()?"checked":"";?>> Guest<BR />
						</TD></TR>
					<TR CLASS="odd"><TD CLASS="td_label">Info:</TD><TD><INPUT SIZE="40" MAXLENGTH="80" TYPE="text" VALUE="<?php print $_POST['frm_info'];?>" NAME="frm_info"></TD></TR>
					<TR CLASS="even"><TD></TD><TD><INPUT TYPE="button" VALUE="Cancel"  onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Add User"></TD></TR>
					</FORM></TABLE>
					<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
					</BODY>
					</HTML>
<?php
					exit();
					}
				}
			else {	
?>
				<FONT CLASS="header">Add User</FONT><BR /><BR /><TABLE BORDER="0">
				<FORM METHOD="POST" NAME = "user_add_Form" onSubmit="return validate_user(document.user_add_Form);"  ACTION="config.php?func=user&add=true&go=true">
				<TR CLASS="even"><TD CLASS="td_label">User ID:</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="text" NAME="frm_user"></TD></TR>
				<TR CLASS="odd"><TD CLASS="td_label">Password:</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="password" NAME="frm_passwd">&nbsp;&nbsp; <B>Confirm:</B> <INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd_confirm"></TD></TR>
				<TR CLASS="even"><TD CLASS="td_label">Callsign:</TD><TD><INPUT SIZE="20" MAXLENGTH="20" TYPE="text" NAME="frm_callsign"></TD></TR>
				<TR CLASS="odd"><TD CLASS="td_label">Info:</TD><TD><INPUT SIZE="47" MAXLENGTH="80" TYPE="text" NAME="frm_info"></TD></TR>
				<TR CLASS="even"><TD CLASS="td_label">Email:</TD><TD><INPUT SIZE="47" MAXLENGTH="47" TYPE="text" NAME="frm_email"></TD></TR>
				<TR CLASS="odd"><TD CLASS="td_label">Level:</TD><TD>
				<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_ADMINISTRATOR'];?>" NAME="frm_level"> Administrator<BR />
				<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_USER'];?>" NAME="frm_level"> User<BR />
				<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_GUEST'];?>" NAME="frm_level"> Guest<BR />
				</TD></TR>
				<TR CLASS="even"><TD></TD><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel"  onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Add this user"></TD></TR>
				</FORM></TABLE>
				<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
				</BODY>
				</HTML>
<?php
				exit();
				}
			}
		else
			print '<FONT CLASS="warn">Not authorized.</FONT><BR /><BR />';
		}				// end if($_GET['add'] ...		
    break;

case 'responder' :
	function finished ($caption) {
		print "</HEAD><BODY onLoad = 'document.fin_form.submit();'>";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
		print "</FORM></BODY></HTML>";	
		}

	function do_calls($id = 0) {
		$print = "\n<SCRIPT>\n";
		$print .="\t\tvar calls = new Array();\n";
		$query	= "SELECT `id`, `callsign` FROM `$GLOBALS[mysql_prefix]responder` where `id` != $id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		while($row = stripslashes_deep(mysql_fetch_array($result))) {
			if (!empty($row['callsign'])) {
				$print .="\t\tcalls.push('" .$row['callsign'] . "');\n";
				}
			}				// end while();
		$print .= "</SCRIPT>\n";
		return $print;
		}		// end function 

	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Responder	
		$query = "DELETE FROM $GLOBALS[mysql_prefix]responder WHERE id='$_POST[frm_id]'";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Unit <i>" . stripslashes_deep($_POST['frm_name']) . "</i> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$frm_mobile = ((array_key_exists ('frm_mobile',$_POST )) && ($_POST['frm_mobile']=='on'))? 1 : 0 ;		
			$query = "UPDATE $GLOBALS[mysql_prefix]responder SET 
				`name`='$_POST[frm_name]',
				`description`='$_POST[frm_descr]',
				`status`='$_POST[frm_status]',
				`callsign`='$_POST[frm_callsign]',
				`mobile`='$frm_mobile',
				`contact_name`='$_POST[frm_contact_name]',
				`contact_via`='$_POST[frm_contact_via]',
				`lat`='$_POST[frm_lat]',
				`lng`='$_POST[frm_lng]',
				`type`='$_POST[frm_type]',
				`updated`='$now' 
				WHERE `id`='$_POST[frm_id]';";		// 
	
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$caption = "<B>Unit <i>" . stripslashes_deep($_POST['frm_name']) . "</i> has been updated.</B><BR /><BR />";
			finished ($caption);			// wrap it up
			}
		}				// end else {}
		
	if ($_getgoadd == 'true') {
		$frm_mobile = ((array_key_exists ('frm_mobile',$_POST )) && ($_POST['frm_mobile']=='on'))? 1 : 0 ;		
	
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]responder` (
			name, description, status, callsign, mobile, contact_name, contact_via, lat, lng, type, updated ) 
			VALUES(
			'$_POST[frm_name]', '$_POST[frm_descr]', '$_POST[frm_status]', '$_POST[frm_callsign]', '$frm_mobile', '$_POST[frm_contact_name]', '$_POST[frm_contact_via]', '$_POST[frm_lat]', '$_POST[frm_lng]', '$_POST[frm_type]', '$now')";

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Unit <i>" . stripslashes_deep($_POST['frm_name']) . "</i> has been added.</B><BR /><BR />";
		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')
	
	if ($_getadd == 'true') {
		print do_calls();		// call signs to JS array for validation
?>		
		<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
		</HEAD>
		<BODY  onLoad = "ck_frames()" onunload="GUnload()">
		<FONT CLASS="header">Add Unit</FONT><BR /><BR />
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER="0" ID='addform'>
		<!-- 688 good -->
		<FORM NAME= "res_add_Form" METHOD="POST" onSubmit="return validate_res(document.res_add_Form);" ACTION="config.php?func=responder&goadd=true">
		<TR CLASS = "even"><TD CLASS="td_label">Name: <font color='red' size='-1'>*</font></TD>			<TD><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD>	<TD><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Status:</TD>		<TD><INPUT SIZE="48" MAXLENGTH="80" TYPE="text" NAME="frm_status" VALUE="" /></TD></TR>
		<TR CLASS = "odd" VALIGN='bottom'><TD CLASS="td_label">Callsign:</TD>		<TD><INPUT SIZE="24" MAXLENGTH="24" TYPE="text" NAME="frm_callsign" VALUE="" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS="td_label">Mobile:</SPAN>&nbsp;&nbsp;<INPUT TYPE="checkbox" NAME="frm_mobile"></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Type: <font color='red' size='-1'>*</font></TD><TD>
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_MEDS'];?>" NAME="frm_type"> Medical<BR />
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_FIRE'];?>" NAME="frm_type"> Fire<BR />
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_COPS'];?>" NAME="frm_type"> Police<BR />
			<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_OTHR'];?>" NAME="frm_type"> Other<BR />
			</TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Map:<TD><INPUT TYPE="text" NAME="frm_lat" VALUE="" disabled />&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="text" NAME="frm_lng" VALUE="" disabled /></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS = "even"><TD COLSPAN=2 ALIGN='center'><INPUT TYPE="button" VALUE="Cancel"   onClick = "document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit for Update"></TD></TR>
		</FORM></TABLE> <!-- end inner left -->
		</TD><TD ALIGN='center'>
		<DIV ID='map' style='width: 500px; height: 400px; border-style: outset'></DIV>
		<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
			Medical: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
			Fire: 		<IMG SRC = './markers/sm_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
			Police: 	<IMG SRC = './markers/sm_blue.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
			Other: 		<IMG SRC = './markers/sm_green.png' BORDER=0>		
		</TD></TR></TABLE><!-- end outer -->

<?php
		map("a") ;				// call GMap js ADD mode
?>
		<FORM NAME='can_Form' METHOD="get" ACTION = "config.php">
		<INPUT TYPE='hidden' NAME = 'func' VALUE='responder'/>
		</FORM>
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['add'])

	if ($_getedit == 'true') {
		$id = $_GET['id'];
		$query	= "SELECT * FROM $GLOBALS[mysql_prefix]responder WHERE id=$id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_array($result);		
		$type_checks = array ("", "", "", "", "");
		$type_checks[$row['type']] = " checked";
		$checked = (!empty($row['mobile']))? " checked" : "" ;
		print do_calls($id);								// generate JS calls array
?>		
		<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
		</HEAD>
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
		<FONT CLASS="header">Edit Unit Data</FONT><BR /><BR />
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER="0" ID='editform'>
		<FORM METHOD="POST" NAME= "res_edit_Form" onSubmit="return validate_res(document.res_edit_Form);" ACTION="config.php?func=responder&goedit=true">
		<TR CLASS = "even"><TD CLASS="td_label">Name: <font color='red' size='-1'>*</font></TD>			<TD><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD>	<TD><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Status:</TD>		<TD><INPUT SIZE="48" MAXLENGTH="80" TYPE="text" NAME="frm_status" VALUE="<?php print $row['status'] ;?>" /></TD></TR>
		<TR VALIGN = 'baseline' CLASS = "odd" VALIGN='bottom'><TD CLASS="td_label">Callsign:</TD>		<TD><INPUT SIZE="24" MAXLENGTH="24" TYPE="text" NAME="frm_callsign" VALUE="<?php print $row['callsign'] ;?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<SPAN CLASS="td_label">Mobile:</SPAN>&nbsp;&nbsp;<INPUT TYPE="checkbox" NAME="frm_mobile" <?php print $checked ; ?>></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="<?php print $row['contact_via'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Type: <font color='red' size='-1'>*</font></TD><TD>
<?php
		$type_checks = array ("", "", "", "", "");	// all empty
		$type_checks[$row['type']] = " checked";		// set the nth entry
?>		
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_MEDS']; ?>" NAME="frm_type" <?php print $type_checks[1];?>> Medical<BR />
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_FIRE']; ?>" NAME="frm_type" <?php print $type_checks[2];?>> Fire<BR />
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_COPS']; ?>" NAME="frm_type" <?php print $type_checks[3];?>> Police<BR />
		<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_OTHR']; ?>" NAME="frm_type" <?php print $type_checks[4];?>> Other<BR />
		</TD></TR>
	
		<TR CLASS = "odd"><TD CLASS="td_label">Map:<TD><INPUT TYPE="text" NAME="frm_lat" VALUE="<?php print $row['lat'] ;?>" SIZE=12 disabled />&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="text" NAME="frm_lng" VALUE="<?php print $row['lng'] ;?>" SIZE=12 disabled /></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="even"><TD CLASS="td_label">Remove Unit:</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" ></TD></TR>
		<TR CLASS = "odd"><TD COLSPAN=2 ALIGN='center'><INPUT TYPE="button" VALUE="Cancel" onClick= "do_Cancel();return false;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset" onClick="map_reset()";>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit for Update"></TD></TR>
		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		</FORM></TABLE>
		</TD><TD><DIV ID='map' style='width: 400px; height: 400px; border-style: inset'></DIV></TD></TR></TABLE>
<?php
		print do_calls($id);		// generate JS calls array
		map("e") ;				// call GMap js EDIT mode
?>
		<FORM NAME='can_Form' METHOD="get" ACTION = "config.php">
		<INPUT TYPE='hidden' NAME = 'func' VALUE='responder'/>
		</FORM>
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['edit'])

		if ($_getview == 'true') {
			$id = $_GET['id'];
			$query	= "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]responder WHERE id=$id";
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= mysql_fetch_array($result);		
			$type_checks = array ("", "", "", "", "");
			$type_checks[$row['type']] = " checked";
			$checked = (!empty($row['mobile']))? " checked" : "" ;			
			$coords =  $row['lat'] . "," . $row['lng'];		// for UTM
?>			
		<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
		</HEAD>
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
			<FONT CLASS="header">Unit Data</FONT><BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER="0" ID='viewform'>
			<FORM METHOD="POST" NAME= "res_view_Form" ACTION="config.php?func=responder">
			<TR CLASS = "even"><TD CLASS="td_label">Name: </TD>			<TD><?php print $row['name'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Description: </TD>	<TD><?php print $row['description'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Status:</TD>		<TD><?php print $row['status'] ;?> </TD></TR>
			<TR VALIGN = 'baseline' CLASS = "odd"><TD CLASS="td_label">Callsign:</TD>		<TD><?php print $row['callsign'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS="td_label">Mobile:</SPAN>&nbsp;&nbsp;<INPUT disabled TYPE="checkbox" NAME="frm_mobile" <?php print $checked ; ?>></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><?php print $row['contact_name'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><?php print $row['contact_via'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Type: </TD><TD>
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_MEDS']; ?>" NAME="frm_type" <?php print $type_checks[1];?>> Medical<BR />
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_FIRE']; ?>" NAME="frm_type" <?php print $type_checks[2];?>> Fire<BR />
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_COPS']; ?>" NAME="frm_type" <?php print $type_checks[3];?>> Police<BR />
				<INPUT disabled TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_OTHR']; ?>" NAME="frm_type" <?php print $type_checks[4];?>> Other<BR />
				</TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label">As of:</TD>							<TD><?php print format_date($row['updated']); ?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Map:<TD ALIGN='center'><INPUT TYPE="text" NAME="frm_lat" VALUE="<?php print $row['lat'] ;?>" SIZE=12 disabled />&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="text" NAME="frm_lng" VALUE="<?php print $row['lng'] ;?>" SIZE=12 disabled /></TD></TR>
<?php
		$utm = get_variable('UTM');
		if ($utm==1) {
			$coords =  $row['lat'] . "," . $row['lng'];
			print "<TR CLASS='odd'><TD CLASS='td_label'>UTM:</TD><TD>" . toUTM($coords) . "</TD></TR>\n";
			}
		$toedit = (is_guest())? "" : "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
?>			
			<TR><TD>&nbsp;</TD></TR>
			<TR CLASS = "odd"><TD COLSPAN=2 ALIGN='center'><?php print $toedit; ?></TD></TR>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			</FORM></TABLE>
			</TD><TD><DIV ID='map' style="width: 400px; height: 400px; border-style: inset"></DIV></TD></TR></TABLE>
			<FORM NAME="can_Form" METHOD="post" ACTION = "config.php"></FORM>		
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "config.php?func=responder&edit=true&id=<?php print $id; ?>"></FORM>		
			</BODY>					<!-- END RESPONDER VIEW -->
<?php
			map("v") ;				// call GMap js EDIT mode
?>
			</BODY>
			</HTML>
<?php
			exit();
			}		// end if ($_GET['view'])

		$do_list_and_map = TRUE;
		
		if($do_list_and_map) {
			if (!isset($mapmode)) {$mapmode="a";}
			print $caption;
?>
		<META HTTP-EQUIV="REFRESH" CONTENT="180">
		<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
		</HEAD><!-- 797 -->
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
		<TABLE ID='outer'><TR><TD>
			<DIV ID='side_bar'></DIV>
			</TD><TD ALIGN='center'>
			<DIV ID='map' style='width: 500px; height: 400px; border-style: outset'></DIV>
			<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
				Medical: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
				Fire: 		<IMG SRC = './markers/sm_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
				Police: 	<IMG SRC = './markers/sm_blue.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
				Other: 		<IMG SRC = './markers/sm_green.png' BORDER=0>		
			</TD></TR></TABLE><!-- end outer -->
			
			<FORM NAME='view_form' METHOD='get' ACTION='config.php'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='view' VALUE='true'>
			<INPUT TYPE='hidden' NAME='id' VALUE=''>
			</FORM>
			
			<FORM NAME='add_Form' METHOD='get' ACTION='config.php'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='add' VALUE='true'>
			</FORM>
			
			<FORM NAME='can_Form' METHOD="get" ACTION = "config.php?func=responder"></FORM>
			<FORM NAME='tracks_Form' METHOD="get" ACTION = "tracks.php"></FORM>
			</BODY>				<!-- END RESPONDER LIST and ADD -->
<?php
		print do_calls();		// generate JS calls array
//		$button = (is_guest())? "": "<TR><TD COLSPAN='99' ALIGN='center'><BR /><INPUT TYPE='button' value= 'Add a Unit'  onClick ='document.add_Form.submit();'></TD></TR>";

		$buttons = "<TR><TD COLSPAN=99 ALIGN='center'><BR /><INPUT TYPE = 'button' onClick = 'document.tracks_Form.submit();' VALUE='Unit Tracks'>";
		$buttons .= (is_guest())? "":"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' value= 'Add a Unit'  onClick ='document.add_Form.submit();'>";
		$buttons .= "</TD></TR>";

		print list_responders($buttons, 0);
		print "\n</HTML> \n";
		exit();
		}				// end if($do_list_and_map)
    break;

case 'center' :
?>
	<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
	</HEAD>
	<BODY onLoad = "ck_frames()" onunload="GUnload()">
<?php

	$get_update = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['update'])))) ) ? "" : $_GET['update'] ;

//	if($_GET['update'] == 'true') {
	if($get_update == 'true') {
		$query = "UPDATE $GLOBALS[mysql_prefix]settings SET `value`='$_POST[frm_lat]' WHERE `name`='def_lat';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		$query = "UPDATE $GLOBALS[mysql_prefix]settings SET `value`='$_POST[frm_lng]' WHERE `name`='def_lng';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		$query = "UPDATE $GLOBALS[mysql_prefix]settings SET `value`='$_POST[frm_zoom]' WHERE `name`='def_zoom';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		$query = "UPDATE $GLOBALS[mysql_prefix]settings SET `value`='$_POST[frm_map_caption]' WHERE `name`='map_caption';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		print '<FONT CLASS="header">Settings saved to database.</FONT><BR /><BR />';
		}
	else {
?>	
		<TABLE BORDER=0 ID='outer'>
		<TR><TD COLSPAN=2 ALIGN='center'><FONT CLASS="header">Select Map Center/Zoom and Caption</FONT><BR /><BR /></TD></TR>
		<TR><TD>
		<TABLE BORDER="0">
		<FORM METHOD="POST" NAME= "cen_Form"  onSubmit="return validate_cen(document.cen_Form);" ACTION="config.php?func=center&update=true">
		<TR CLASS = "even"><TD CLASS="td_label">Lookup:</TD><TD COLSPAN=3>&nbsp;&nbsp;City:&nbsp;<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_city" VALUE="" />
		&nbsp;&nbsp;&nbsp;&nbsp;State:&nbsp;<INPUT MAXLENGTH="2" SIZE="2" TYPE="text" NAME="frm_st" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD COLSPAN=4 ALIGN="center"><INPUT TYPE="BUTTON" VALUE="Locate it" onClick="addrlkup()" /></TD></TR>
		<TR><TD><BR /><BR /><BR /><BR /><BR /></TD></TR>

		<TR CLASS = "even"><TD CLASS="td_label">Caption:</TD><TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_map_caption" VALUE="<?php print get_variable('map_caption');?>" onChange = "document.getElementById('caption').innerHTML=this.value "/></TD></TR>
		<TR CLASS = "odd">
			<TD CLASS="td_label">Map:</TD>
			<TD >&nbsp;&nbsp;Lat:&nbsp;</TD>
			<TD><INPUT TYPE="text" NAME="frm_lat" VALUE="<?php print get_variable('def_lat');?>" SIZE=12 disabled /></TD>
			<TD >Long:&nbsp;<INPUT TYPE="text" NAME="frm_lng" VALUE="<?php print get_variable('def_lng');?>" SIZE=12 disabled /></TD></TR>
		<TR CLASS = "odd">
			<TD></TD>
			<TD>&nbsp;&nbsp;Zoom:&nbsp;</TD>
			<TD><INPUT TYPE="text" NAME="frm_zoom" VALUE="<?php print get_variable('def_zoom');?>" SIZE=4 disabled /></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS = "even"><TD COLSPAN=5 ALIGN='center'>
			<INPUT TYPE='button' VALUE='Cancel'  onClick='document.can_Form.submit();' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset' onClick = "map_cen_reset();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Apply'></TD></TR>
		</FORM></TABLE>
		</TD><TD><DIV ID='map' style='width: 500px; height: 400px; border-style: outset'></DIV>
		<BR><CENTER><FONT CLASS="header"><SPAN ID="caption">Drag/Zoom and double-click to new default position</SPAN></FONT></CENTER>
		</TD></TR>
		</TABLE>
		<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
		</BODY>
<?php
	map_cen ("a") ;				// call GMap center js
?>
		</HTML> <!-- 732  -->
<?php		
		exit();
		}		// end if/else ($_GET['update'] 	
    break;
    
case 'api_key' :		
	if((isset($_GET)) && (isset($_GET['update'])) && ($_GET['update'] == 'true')) {
		$query = "UPDATE $GLOBALS[mysql_prefix]settings SET `value`='$_POST[frm_value]' WHERE `name`='gmaps_api_key';";
		
		$result = mysql_query($query) or die("do_insert_settings($name,$value) failed, execution halted");
		print '<BODY onLoad = "ck_frames()">\n<FONT CLASS="header">GMaps API Key saved to database.</FONT><BR /><BR />';
		}
	else {
		$curr_key = get_variable('gmaps_api_key')
?>	
		<BODY onLoad = 'ck_frames()'>
		<TABLE BORDER="0">
		<FORM METHOD="POST" NAME= "api_Form"  onSubmit="return validate_key(document.api_Form);" ACTION="config.php?func=api_key&update=true">
		<TR CLASS = "even"><TD CLASS="td_label" ALIGN='center'>Obtain GMaps API key at http://www.google.com/apis/maps/signup.html</TD></TR>
		<TR CLASS = "odd"><TD><BR /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Copy/paste key:</TD></TR>
		<TR CLASS = "odd"><TD><INPUT MAXLENGTH="88" SIZE="120" TYPE="text" NAME="frm_value" VALUE="<?php print $curr_key; ?>" /></TD></TR>
		<TR CLASS = "even"><TD ALIGN='center'>
			<INPUT TYPE='button' VALUE='Cancel'  onClick='document.can_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset' onClick = "map_cen_reset();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Apply'></TD></TR>
		</FORM></TABLE>
		<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
		</BODY>
<SCRIPT>		
	function validate_key(theForm) {			// limited form contents validation  
		var errmsg="";
		if (theForm.frm_value.value.length!=86)			{errmsg+= "\tEntered GMaps API key is Invalid\n\t - length must be 86 chars.";}
		if (errmsg!="") {
			alert ("Please correct and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			return true;
			}
		}				// end function validate_key()

</SCRIPT>
</HTML>
<?php		
		exit();
		}		// end  else	
    break;
    
case 'delete' :	
	print "<BODY onLoad = 'ck_frames()'>\n";
	$subfunc = (array_key_exists ('subfunc',$_GET ))? $_GET['subfunc']: "list";
	switch ($subfunc) {
		case 'list':
?>		
			<FORM METHOD="POST" NAME= "del_Form" ACTION="config.php?func=delete&subfunc=confirm">
<?php
//			$query = "SELECT *,UNIX_TIMESTAMP(problemend) AS problemend FROM $GLOBALS[mysql_prefix]ticket";
			$query	= "SELECT *,UNIX_TIMESTAMP(problemend) AS problemend FROM $GLOBALS[mysql_prefix]ticket WHERE `status` = " . $GLOBALS['STATUS_CLOSED']. " ORDER BY `scope`";
	
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (mysql_affected_rows()>0) {
				print "<TABLE BORDER=0>";
				print "<TR CLASS = 'even'><TD CLASS='td_label' ALIGN='center'  COLSPAN=3>Select Closed Tickets for Permanent Deletion</TD></TR>";
				print "<TR CLASS = 'odd'><TD COLSPAN=3>&nbsp;</TD></TR>";
					$i = 0;
					while($row = stripslashes_deep(mysql_fetch_array($result))) {
						print "<TR CLASS='" . $evenodd[$i%2] . "'><TD CLASS='td_label'>" . shorten($row['scope'], 50) . "</TD>";
						print "<TD CLASS='td_label'>" . format_sb_date($row['problemend']) . "</TD>";
						print "<TD CLASS='td_label'><INPUT TYPE='checkbox' NAME = 'T" . $row['id'] . "'></TD></TR>\n";
						$i++;
						}		// end while($row ...)
				print "<TR CLASS='" . $evenodd[$i%2] . "'><TD ALIGN='center' COLSPAN=3>";
?>
				<INPUT TYPE='button' VALUE='Cancel' 	onClick = 'document.can_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' VALUE='Select All' onClick = 'all_ticks(true)';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' VALUE='Reset' 		onClick = 'document.del_Form.reset();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' VALUE='Continue' 	onClick = 'collect(); document.del_Form.submit()'></TD></TR>
				<INPUT TYPE='hidden' NAME = 'idstr' VALUE=''>
				</FORM></TABLE>
<?php
				}				// end if (mysql_affected_rows()>0)
			else {
				print "</FORM><BR />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>No Closed Tickets!</B><br /><br />";
				print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Continue' onClick = 'document.can_Form.submit();'>";
				}
?>
			
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
			</BODY>
	</HTML>
<?php		
		exit();
		    break;
	
		case 'confirm':
?>
			<BR /><BR /><BR /><FONT CLASS='warn' SIZE = "+1"><B>Please confirm deletions - cannot be undone!</B></FONT><BR /><BR />
			<FORM METHOD="POST" NAME= "del_Form" ACTION="config.php?func=delete&subfunc=do_del">
			<INPUT TYPE='hidden' NAME='idstr' VALUE="<?php print $_POST['idstr'];?>">
			<INPUT TYPE='button' VALUE='Cancel'  onClick='document.can_Form.submit();'>&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Confirmed'></TD></TR>
			</FORM>
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
			</BODY>
	</HTML>
<?php
			exit();
		    break;
	
		case 'do_del':	
			$temp = explode(",", $_POST['idstr'], 20);
			for ($i=0; $i<count($temp); $i++) {
				$query = "DELETE from $GLOBALS[mysql_prefix]ticket WHERE `id` = " . $temp[$i] . " LIMIT 1";
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$query = "DELETE from $GLOBALS[mysql_prefix]action_bu WHERE `ticket_id` = " . $temp[$i] ;
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$query = "DELETE from $GLOBALS[mysql_prefix]patient_bu WHERE `ticket_id` = " . $temp[$i];
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				dump ($query);
				}
?>
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php">	
			<BR /><BR /><BR /><BR /><B><?php print count($temp); ?> Tickets and associated Action and Patient records deleted</B><BR /><BR />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Continue'  onClick='document.can_Form.submit();'>
			</FORM>
			</BODY>
<?php
			exit();
	
		    break;
	
		default :   
		}				// end switch ($subfunc)
    
    
	default:
	}						// end switch ($func)
		
	if (is_administrator()) { 	// SHOW MENU BASED ON USER LEVEL
	
?>
		</HEAD>
		<BODY onLoad = 'ck_frames()'>
		<LI><A HREF="config.php?func=user&add=true">Add user</A>
		<LI><A HREF="config.php?func=responder">Units</A>
		<LI><A HREF="config.php?func=reset">Reset Database</A>
		<LI><A HREF="config.php?func=optimize">Optimize Database</A>
		<LI><A HREF="config.php?func=settings">Edit Settings</A>
		<LI><A HREF="config.php?func=center">Set Default Map</A>
		<LI><A HREF="config.php?func=api_key">Set GMaps API key</A>
		<LI><A HREF="config.php?func=delete">Delete Tickets</A>
<?php 
		}
	if (!is_guest()) {				// USER OR ADMIN
?>
		<LI><A HREF="config.php?func=profile">Edit My Profile</A>
		<LI><A HREF="config.php?func=notify">Edit My Notifies</A>
		<BR /><BR />
<?php
		list_users();
		}
	show_stats();
	print "</BODY>\n";
	
function map($mode) {				// RESPONDER ADD AND EDIT
	global $row;
			if (($mode=="a") || (empty($row['lat']))) 	{$lat = get_variable('def_lat'); $lng = get_variable('def_lng'); $gotpt=FALSE;}
			else 										{$lat = $row['lat']; $lng = $row['lng']; $gotpt=TRUE;}
?>

<SCRIPT>
//
		function writeConsole(content) {
			top.consoleRef=window.open('','myconsole',
				'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=0' +',resizable=0')
		 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
				+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></HTML>'
				)				// end top.consoleRef.document.writeln()
		 	top.consoleRef.document.close();
			}				// end function writeConsole(content)
		
		function map_reset() {
			map.clearOverlays();
			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
			map.setCenter(point, <?php print get_variable('def_zoom');?>);
			map.addOverlay(new GMarker(point, myIcon));		
			}
		function map_cen_reset() {				// reset map center icon
			map.clearOverlays();
			}
		
		var map = new GMap2(document.getElementById('map'));
								// note globals
		var myZoom;
		var marker;
		
		var myIcon = new GIcon();
		myIcon.image = "./markers/yellow.png";	
		myIcon.shadow = "./markers/sm_shadow.png";
		myIcon.iconSize = new GSize(16, 28);
		myIcon.shadowSize = new GSize(22, 20);
		myIcon.iconAnchor = new GPoint(8, 28);
		myIcon.infoWindowAnchor = new GPoint(5, 1);
		
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
		map.addControl(new GOverviewMapControl());
		var tab1contents;				// info window contents - first/only tab
										// default point - possible dummy
		map.setCenter(new GLatLng(<?php print $lat; ?>, <?php print $lng; ?>), <?php print get_variable('def_zoom');?>);	// larger # => tighter zoom
		map.enableScrollWheelZoom(); 	

<?php
		if ($gotpt) 	{		// got a location?
?>		
	 		var point = new GLatLng(<?php print $row['lat'] . ", " . $row['lng']; ?>);
			var marker = new GMarker(point, {icon: myIcon, draggable:true});
//			map.addOverlay(marker);
	 		map.addOverlay(new GMarker(point, myIcon));
			GEvent.addListener(marker, "dragend", function() {
//				alert (780);
				var point = marker.getPoint();
				map.panTo(point);
				});		// end GEvent.addListener "dragend"
<?php
			}
		if (!((isset ($mode)) && ($mode=="v"))) {	// disallow if view mode
?>

		GEvent.addListener(map, "click", function(marker, point) {
			if (marker) {
				map.removeOverlay(marker);
				document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=false;
				document.forms[0].frm_lat.value=document.forms[0].frm_lng.value="";
				document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=true;
				}
			if (point) {
				myZoom = map.getZoom();
				map.clearOverlays();
				do_lat (point.lat())							// display
				do_lng (point.lng())
//				map.setCenter(point, myZoom);		// panTo(center)
				map.panTo(point);				// panTo(center)
				if (document.forms[0].frm_zoom) {				// get zoom?
					document.forms[0].frm_zoom.disabled = false;
					document.forms[0].frm_zoom.value = myZoom;
					document.forms[0].frm_zoom.disabled = true;					
					}	
				marker = new GMarker(point, {icon: myIcon, draggable:true});
				map.addOverlay(marker);
				
//				map.openInfoWindowHtml(point,tab1contents);
				}
			});				// end GEvent.addListener() "click"
<?php
			}				// end if ($mode=="v")
?>			
			
	</SCRIPT>
<?php
	}		// end function map()


function map_cen ($mode) {				// specific to map center
	$lat = get_variable('def_lat'); $lng = get_variable('def_lng');
?>

<SCRIPT>
	function addrlkup() {		   // added 8/3 by AS -- getLocations(address,  callback) -- not currently used
		var address = document.forms[0].frm_city.value + " "  +document.forms[0].frm_st.value;
		if (geocoder) {
			geocoder.getLatLng(
				address,
				function(point) {
					if (!point) {
						alert(address + " not found");
						} 
					else {
						map.setCenter(point, 9);
						var marker = new GMarker(point);
						do_lat (point.lat());
						do_lng (point.lng());
						}
					}
				);
			}
		}				// end function addrlkup()

	function writeConsole(content) {
		top.consoleRef=window.open('','myconsole',
			'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=0' +',resizable=0')
	 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
			+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></HTML>'
			)				// end top.consoleRef.document.writeln()
	 	top.consoleRef.document.close();
		}				// end function writeConsole(content)
	
	function map_cen_reset() {				// reset map center icon
		map.clearOverlays();
		}
	
	var map;								// note globals
	var myZoom;
	var geocoder = new GClientGeocoder();
	
	map = new GMap2(document.getElementById('map'));
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	map.addControl(new GOverviewMapControl());

	map.setCenter(new GLatLng(<?php print $lat; ?>, <?php print $lng; ?>), <?php print get_variable('def_zoom');?>);	// larger # => tighter zoom
	map.enableScrollWheelZoom(); 	

	GEvent.addListener(map, "zoomend", function(oldzoom,newzoom) {
		if (document.forms[0].frm_zoom) {						// get zoom?
			document.forms[0].frm_zoom.disabled = false;
			document.forms[0].frm_zoom.value = newzoom;
			document.forms[0].frm_zoom.disabled = true;					
			};
		});
		
	GEvent.addListener(map, "dragend", function() {
		var center = map.getCenter();
		do_lat (center.lat());							// display
		do_lng (center.lng());
		myZoom = map.getZoom();
		document.cen_Form.frm_zoom.disabled = false;
		document.cen_Form.frm_zoom.value = myZoom;
		document.cen_Form.frm_zoom.disabled = true;					
		});

	GEvent.addListener(map, "click", function() {
		var center = map.getCenter();
		do_lat (center.lat());							// display
		do_lng (center.lng());
		myZoom = map.getZoom();
		document.cen_Form.frm_zoom.disabled = false;
		document.cen_Form.frm_zoom.value = myZoom;
		document.cen_Form.frm_zoom.disabled = true;					
		});

	</SCRIPT>
<?php
	}		// end function map_cen()
?>
</HTML>