<?php
// 5/28/08 - revised map center to allow icon drag			
// 6/4/08 - added do_log($GLOBALS['LOG_INCIDENT_DELETE']				
// 6/4/08 - added submit()			
// 6/4/08 - corrected table names			
	error_reporting(E_ALL);
	require_once('functions.inc.php');
	require_once('config.inc.php');
	require_once('responders.php');
	if ($istest) {
		foreach ($_POST as $VarName=>$VarValue) 	{echo "POST:$VarName => $VarValue, <BR />";};
		foreach ($_GET as $VarName=>$VarValue) 		{echo "GET:$VarName => $VarValue, <BR />";};
		echo "<BR/>";
		}
	do_login(basename(__FILE__));	// session_start()
	extract($_GET);
	if (!isset($func)) {$func = "summ";}
	$reload_top = FALSE;		
	
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
<?php
//	dump ($reload_top);
//	if ($reload_top) {
//		print "\n\talert(32);\n";
//		}
?>
			}		// end function ck_frames()
	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	function do_Post(the_table) {
		document.tables.tablename.value=the_table;
		document.tables.submit();
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
		if (theForm.frm_un_status_id.value==0)		{errmsg+="\tUnit STATUS is required.\n";}
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
	function do_zoom (zoom) {
		document.cen_Form.frm_zoom.disabled=false;
		document.cen_Form.frm_zoom.value=zoom;
		document.cen_Form.frm_zoom.disabled=true;
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
		document.del_Form.submit();									// 6/4/08 - added
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
			<TR CLASS='odd'><TD></TD><TD ALIGN="center"><INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit"></TD></TR>
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
			$query = "INSERT INTO $GLOBALS[mysql_prefix]notify SET ticket_id='$_POST[frm_id]',user='$my_session[user_id]',email_address='$_POST[frm_email]',execute_path='$_POST[frm_execute]',on_action='$on_action',on_ticket='$on_ticket'";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (!get_variable('allow_notify')) print "<FONT CLASS='warn'>Warning: Notification is disabled by administrator</FONT><BR /><BR />";
			print "<FONT SIZE='3'><B>Notify added.</B></FONT><BR /><BR />";
			}
		else {
			if ($my_session['user_id'])
				$query = "SELECT * FROM $GLOBALS[mysql_prefix]notify WHERE user='$my_session[user_id]'";
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
				print "<TR CLASS='" .$colors[$i%2]  ."'><TD COLSPAN=99 ALIGN='center'><INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
				$query = "UPDATE $GLOBALS[mysql_prefix]user SET info='$_POST[frm_info]',email='$_POST[frm_email]',sortorder='$_POST[frm_sortorder]',sort_desc='$frm_sort_desc',ticket_per_page='$_POST[frm_ticket_per_page]' WHERE id='$my_session[user_id]'";
				}
			else {
				$query = "UPDATE $GLOBALS[mysql_prefix]user SET passwd=PASSWORD('$_POST[frm_passwd]'),info='$_POST[frm_info]',email='$_POST[frm_email]',sortorder='$_POST[frm_sortorder]',sort_desc='$frm_sort_desc',ticket_per_page='$_POST[frm_ticket_per_page]' WHERE id='$my_session[user_id]'";
				}
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			reload_session();
			print '<B>Your profile has been updated.</B><BR /><BR />';
			}
		else {
			$query = "SELECT id FROM $GLOBALS[mysql_prefix]user WHERE id='" . $my_session['user_id'] . "'";
			if ($my_session['user_id'] < 0 OR check_for_rows($query) == 0) {
				print __LINE__ . " Invalid user id '$my_session[user_id]'.";
				exit();
				}

			$query	= "SELECT * FROM $GLOBALS[mysql_prefix]user WHERE id='$my_session[user_id]'";
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
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $my_session['user_id'];?>">
			<TR CLASS="odd"><TD></TD><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Apply"></TD></TR>
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
				<TR><TD></TD><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Apply"></TD></TR>
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
				$reload_top = TRUE;			// reload top frame for possible new settings value
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
					<INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Apply'></TD></TR></FORM></TABLE>";
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
			<TR CLASS="odd"><TD></TD><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Apply"></TD></TR>
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
					<TR CLASS="even"><TD></TD><TD><INPUT TYPE="button" VALUE="Cancel" onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Add User"></TD></TR>
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
				<TR CLASS="even"><TD></TD><TD ALIGN="center"><INPUT TYPE="button" VALUE="Cancel" onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Add this user"></TD></TR>
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
			<INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset' onClick = "map_cen_reset();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Apply'></TD></TR>
		</FORM></TABLE>
		</TD><TD><DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
		<BR><CENTER><FONT CLASS="header"><SPAN ID="caption">Click/Drag/Zoom to new default position</SPAN></FONT></CENTER>
		</TD></TR>
		</TABLE>
		<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
		</BODY>
<?php
	map_cen () ;				// call GMap center js
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
			<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset' onClick = "map_cen_reset();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Apply'></TD></TR>
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
    
case 'dump' :				// see mysql.inc.php	for MySQL parameters
	require_once('./incs/MySQLDump.class.php');
	$backup = new MySQLDump(); //create new instance of MySQLDump
	
	$the_db = $mysql_prefix . $mysql_db;
	$backup->connect($mysql_host,$mysql_user,$mysql_passwd,$the_db);		// connect
	if (!$backup->connected) { die('Error: '.$backup->mysql_error); } 		// MySQL parameters from mysql.inc.php
	$backup->list_tables(); 												// list all tables
	$broj = count($backup->tables); 										// count all tables, $backup->tables 
																			//   will be array of table names
	echo "<pre>\n"; //start preformatted output
	echo "\n\n-- start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start \n";
	echo "\n-- Dumping tables for database: $mysql_db\n"; //write "intro" ;)
	
	for ($i=0;$i<$broj;$i++) {						//dump all tables:
		$table_name = $backup->tables[$i]; 			//get table name
		$backup->dump_table($table_name); 			//dump it to output (buffer)
		echo htmlspecialchars($backup->output); 	//write output
		}
	echo "\n\n-- end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end \n";
	
	echo "\n<pre>"; 
	
	break;
    
case 'delete' :	
	print "<BODY onLoad = 'ck_frames()'>\n";
	$subfunc = (array_key_exists ('subfunc',$_GET ))? $_GET['subfunc']: "list";
	switch ($subfunc) {
		case 'list':
?>		
			<FORM METHOD="POST" NAME= "del_Form" ACTION="config.php?func=delete&subfunc=confirm">
<?php
			$query	= "SELECT *,UNIX_TIMESTAMP(problemend) AS problemend FROM $GLOBALS[mysql_prefix]ticket WHERE `status` = " . $GLOBALS['STATUS_CLOSED']. " ORDER BY `scope`";
	
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (mysql_affected_rows()>0) {																				// inventory affected rows
				print "<TABLE BORDER=0>";
				print "<TR CLASS = 'even'><TD CLASS='td_label' ALIGN='center'  COLSPAN=6>Select Closed Tickets for Permanent Deletion</TD></TR>";
//				print "<TR CLASS = 'odd'><TD COLSPAN=3>&nbsp;</TD></TR>";
				print "<TR CLASS = 'odd'><TD COLSPAN=2 ALIGN='center'>Ticket</TD><TD>Actions</TD><TD ALIGN='center'>Patients</TD><TD ALIGN='center'>Assigns</TD><TD>Del</TD></TR>";

					$i = 0;
					while($row = stripslashes_deep(mysql_fetch_array($result))) {
						$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `ticket_id` = " . $row['id'];
						$res_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
						$no_acts = mysql_affected_rows();
						$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE `ticket_id` = " . $row['id'];
						$res_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
						$no_pers = mysql_affected_rows();
						$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . $row['id'];
						$res_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
						$no_assns = mysql_affected_rows();
					
					
						print "<TR CLASS='" . $evenodd[$i%2] . "'><TD CLASS='td_label'>" . shorten($row['scope'], 50) . "</TD>";
						print "<TD CLASS='td_label'>" . format_sb_date($row['problemend']) . "</TD>";
						print "<TD ALIGN='center'>{$no_acts}</TD>";
						print "<TD ALIGN='center'>{$no_pers}</TD>";
						print "<TD ALIGN='center'>{$no_assns}</TD>";
						print "<TD CLASS='td_label'><INPUT TYPE='checkbox' NAME = 'T" . $row['id'] . "' onClick = 'this.form.delcount.value++;'></TD></TR>\n";
						$i++;
						}		// end while($row ...)
				print "<TR CLASS='" . $evenodd[$i%2] . "'><TD ALIGN='center' COLSPAN=6><BR/>";
?>
				<INPUT TYPE='button' VALUE='Cancel' 	onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' VALUE='Select All' onClick = 'document.del_Form.delcount.value=1; all_ticks(true)';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' VALUE='Reset' 		onClick = 'document.del_Form.reset();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' VALUE='Continue' 	onClick = 'collect();'></TD></TR>
				<INPUT TYPE='hidden' NAME = 'idstr' VALUE=''>
				<INPUT TYPE='hidden' NAME = 'delcount' VALUE=0>
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
			<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();'>&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Confirmed'></TD></TR>
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
				$query = "DELETE from `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . $temp[$i] . " LIMIT 1";
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	// 6/4/08 - corrected table names
				$query = "DELETE from `$GLOBALS[mysql_prefix]action` WHERE `ticket_id` = " . $temp[$i] ;
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$query = "DELETE from `$GLOBALS[mysql_prefix]patient` WHERE `ticket_id` = " . $temp[$i];
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$query = "DELETE from `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . $temp[$i];
				$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				do_log($GLOBALS['LOG_INCIDENT_DELETE'],$temp[$i]);																// added 6/4/08 
				
//				dump ($query);
				}
			$plu = ($i>1)? "s":"";
?>
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php">	
			<BR /><BR /><BR /><BR /><B><?php print count($temp); ?> Ticket<?php print $plu;?> and associated Assigns, Action and Patient record<?php print $plu;?> deleted</B><BR /><BR />
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
<!--		<LI><A HREF="config.php?func=responder">Units</A> -->
		<LI><A HREF="config.php?func=reset">Reset Database</A>
		<LI><A HREF="config.php?func=optimize">Optimize Database</A>
		<LI><A HREF="config.php?func=settings">Edit Settings</A>
		<LI><A HREF="config.php?func=center">Set Default Map</A>
		<LI><A HREF="config.php?func=api_key">Set GMaps API key</A>
		<LI><A HREF="config.php?func=delete">Delete Closed Tickets</A>
		<LI><A HREF="config.php?func=dump">Dump DB to screen</A>
<?php 
		}							// end if (is_administrator())
	if (!is_guest()) {				// USER OR ADMIN
?>
		<LI><A HREF="config.php?func=profile">Edit My Profile</A>
		<LI><A HREF="config.php?func=notify">Edit My Notifies</A>
		<LI></LI>
		<LI><A HREF="#" onClick = "do_Post('contacts');">Contacts</A>
		<LI><A HREF="#" onClick = "do_Post('in_types');">Incident types</A>
		<LI><A HREF="#" onClick = "do_Post('un_status');">Unit status types</A>
<?php
	if ($istest) {
?>
			<LI><A HREF="#" onClick = "do_Post('log');">Log</A>
			<LI><A HREF="#" onClick = "do_Post('session');">Session</A>
			<LI><A HREF="#" onClick = "do_Post('settings');">Settings</A>
			<LI><A HREF="#" onClick = "do_Post('ticket');">Tickets</A>
			<LI><A HREF="#" onClick = "do_Post('responder');">Units</A>
<?php
			}
?>			
		<BR /><BR />
<?php
		list_users();
		}
	show_stats();
	
?>
	<FORM NAME='tables' METHOD = 'post' ACTION='tables.php'>
	<INPUT TYPE='hidden' NAME='func' VALUE='r'>
	<INPUT TYPE='hidden' NAME='tablename' VALUE=''>
	</FORM>

<?php
print "</BODY>\n";
	
function map_cen () {				// specific to map center
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
	var marker = new GMarker(map.center, {draggable: true});
	map.enableScrollWheelZoom(); 	

	GEvent.addListener(map, "zoomend", function(oldzoom,newzoom) {
		if (document.forms[0].frm_zoom) {						// get zoom?
			document.forms[0].frm_zoom.disabled = false;
			document.forms[0].frm_zoom.value = newzoom;
			document.forms[0].frm_zoom.disabled = true;					
			};
		});
		
	GEvent.addListener(map, "dragend", function(marker, point) {
		alert(994);
		map.panTo(point);
		do_lat (point.lat());							// display
		do_lng (point.lng());
		});

	GEvent.addListener(map, "click", function(marker, point) {
		if (point) {
			map.clearOverlays();
			map.addOverlay(new GMarker(point));				// display
			map.panTo(point);
			do_lat (point.lat());				
			do_lng (point.lng());
			}
		});

	</SCRIPT>
<?php
	}		// end function map_cen()
?>
</HTML>

function(marker, point)