<?php
/*
5/28/08 - revised map center to allow icon drag			
6/4/08 - added do_log($GLOBALS['LOG_INCIDENT_DELETE']				
6/4/08 - added submit()			
6/4/08 - corrected table names
6/9/08 - added user type 'super'
9/13/08 - refresh upper frame
9/16/08 remove 'responder.php'
9/16/08 draggable false, pending getting it to work
9/21/08 revised 'top load' via body tag
9/24/08 permissions revised per suggestion JB
10/8/08	'User' revised to 'Operator'
10/8/08	hide 'Unit types'
10/19/08 added trim()
10/23/08 revised notify validation and for severity handling
10/23/08 profile validation added
11/30/08 table user - added md5 password conversion, skip passwd edit if blank
12/1/08 revised profile edit passwd handling
12/2/08 check for dupl userid's added
12/15/08 added member level
1/17/09  added `auto_route` setting to config.inc.php
1/21/09 show_butts, addr lookup button
1/27/09 added super-only notify execute, quote_smart added
3/3/09 correction for MEMBER user type
4/5/09 default map zoom added
5/4/09 handle usng as input
6/4/09 added Constituents and City tables
6/27/09	added function do_glat() - hidden 7/23/09
6/30/09 added do_mail_win(), renamed to do_all_mail
7/16/09	floating div for 'settings' buttons
7/24/09	hide Glat();
7/28/09	Open Glat()
7/28/09	Add function do_gtrack and function do_locatea for test scripts plus links
10/6/09 Added links to Facility Status and Facility Types settings
11/5/09 Changed window caption, per IE complaint
11/17/09 removed password update from 'edit my profile'
9/26/09 corrections to floating div, per IE
11/30/09 dump-to-screen copy function added
*/
	error_reporting(E_ALL);
	require_once('./incs/functions.inc.php');
	require_once('./incs/config.inc.php');
	require_once('./incs/usng.inc.php');				// 9/16/08

	if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/7/09 
	error_reporting (E_ALL  ^ E_DEPRECATED);
	if ($istest) {
		foreach ($_POST as $VarName=>$VarValue) 	{echo "POST:$VarName => $VarValue, <BR />";};
		foreach ($_GET as $VarName=>$VarValue) 		{echo "GET:$VarName => $VarValue, <BR />";};
		echo "<BR/>";
		}
	do_login(basename(__FILE__));	// session_start()
	extract($_GET);
	if (!isset($func)) {$func = "summ";}
	$reload_top = FALSE;		

 	$query	= "SELECT `user` FROM`$GLOBALS[mysql_prefix]user` WHERE `id` <> '${my_session['user_id']}'";		// 12/2/08
 	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$users = "";
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$users .= trim($row['user']) . "\t";			
		}			
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Configuration Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<STYLE>
	LI { margin-left: 20px;}
	.spl { FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000099; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none}

#bar 		{ width: auto; height: auto; background:transparent; z-index: 100; } 
* html #bar { /*\*/position: absolute; top: expression((60 + (ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop)) + 'px'); right: expression((320 + (ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft)) + 'px');/**/ }
#foo > #bar { position: fixed; top: 60px; right: 320px; }

	</STYLE>
	<SCRIPT SRC='./js/md5.js'></SCRIPT>		<!-- 11/30/08 -->

	<SCRIPT>
	
	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	function ck_frames() {
		if(self.location.href==parent.location.href) {
//			alert(self.location.href + " " +parent.location.href );
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
	
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	String.prototype.trim = function () {				// 10/19/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
     
	function in_array (ary, val) {						// 12/2/08
		for (var i = 0; i<ary.length; i++) {
			if(ary[i] == val) {
				return true;
				}
			}
		return false;
		}				// end function in array


	starting=false;	
	function do_mail_win() {			// 6/13/09, 11/5/09
		if(starting) {return;}					
		starting=true;	
	
		newwindow_am=window.open("do_all_mail.php", "E_mail",  "titlebar, resizable=1, scrollbars, height=480,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
		if (isNull(newwindow_am)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_am.focus();
		starting = false;
		}


	function do_test() {				// 8/2/08 -	11/5/09
		var newwindow_t=window.open("opena.php", "Test_APRS",  "titlebar, resizable=1, scrollbars, height=680,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=500,screenX=50,screenY=50"); newwindow_t.focus();
		if (isNull(newwindow_t)) {
			alert ("Test operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_t.focus();
		}

	function do_smtp() {				// 8/2/08 -	11/5/09
		var newwindow_t=window.open("smtp_test.php", "Test_SMTP",  "titlebar, resizable=1, scrollbars, height=600,width=900,status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50"); newwindow_t.focus();
		if (isNull(newwindow_t)) {
			alert ("Test operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_t.focus();
		}

	function do_instam() {				// 7/26/09	- 11/5/09
		var newwindow_t=window.open("test_instam.php", "Test_InstaMapper",  "titlebar, resizable=1, scrollbars, height=400,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50"); newwindow_t.focus();
		if (isNull(newwindow_t)) {
			alert ("Test operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_t.focus();
		}

	function do_glat() {				// 7/29/09	- 11/5/09
		var newwindow_t=window.open("latitude.php", "Test_Google_Latitude",  "titlebar, resizable=1, scrollbars, height=400,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50"); newwindow_t.focus();
		if (isNull(newwindow_t)) {
			alert ("Test operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_t.focus();
		}

	function do_locatea() {				// 7/29/09 - 11/5/09
		var newwindow_t=window.open("locatea.php", "Test_Locatea",  "titlebar, resizable=1, scrollbars, height=400,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50"); newwindow_t.focus();
		if (isNull(newwindow_t)) {
			alert ("Test operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_t.focus();
		}

	function do_gtrack() {				// 7/29/09	- 11/5/09
		var newwindow_t=window.open("gtrack.php", "Test_Gtrack",  "titlebar, resizable=1, scrollbars, height=400,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50"); newwindow_t.focus();
		if (isNull(newwindow_t)) {
			alert ("Test operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_t.focus();
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
//			theForm.frm_lat.disabled = false;
//			theForm.frm_lng.disabled = false;
			theForm.frm_zoom.disabled = false;
			return true;
			}
		}				// end function validate cen(theForm)

	var str_users = "<?php print $users;?>";				// 12/2/08
	var ary_users = str_users.split("\t");				// see usage in function validate_user()

	function validate_user(theForm) {			// user form contents validation
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				if(confirm("Please confirm this removal.")) {return true;}
				else 										{return false;}
				}
			}

		var errmsg="";
		if (theForm.frm_user.value=="")											{errmsg+="\tUserID is required.\n";}
		var got_level = false;
		for (i=0; i<theForm.frm_level.length; i++){
			if (theForm.frm_level[i].checked) {	got_level = true;	}
			}
		if (!got_level)															{errmsg+="\tUser LEVEL is required.\n";}
		if ((theForm.frm_func.value=="a") && (in_array(ary_users, theForm.frm_user.value.trim())))
																				{errmsg+="\tUserID duplicates existing one.\n";}	
		if (theForm.frm_passwd.value!=theForm.frm_passwd_confirm.value) 		{errmsg+="\tPASSWORD and CONFIRM fail to match.\n";}

		if ((theForm.frm_func.value=="a") && (theForm.frm_passwd.value==""))	{errmsg+="\tPASSWORD is required.\n";}		// only for ADD
		if ((theForm.frm_passwd.value.trim().length>0) && (theForm.frm_passwd.value.trim().length<5))	
																				{errmsg+="\tPasswd length 5 or more is required.\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
		
			theForm.frm_hash.value = (theForm.frm_passwd.value.trim()=="")? "": hex_md5(theForm.frm_passwd.value.trim().toLowerCase());			
			theForm.frm_passwd.value="";			// hide them
			theForm.frm_passwd_confirm.value="";
			return true;
			}
		}				// end function validate user(theForm)

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
		}				// end function validate set(theForm)

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
		var num = new Number(lat)
		document.cen_Form.frm_lat.value=num.toFixed(6);			// 9/9/08
		document.cen_Form.show_lat.disabled=false;				// permit read/write
		document.cen_Form.show_lat.value=do_lat_fmt(document.cen_Form.frm_lat.value);
		document.cen_Form.show_lat.disabled=true;
		}
	function do_lng (lng) {
		var num = new Number(lng)
		document.cen_Form.frm_lng.value=num.toFixed(6);
		document.cen_Form.show_lng.disabled=false;
		document.cen_Form.show_lng.value=do_lng_fmt(document.cen_Form.frm_lng.value);
		document.cen_Form.show_lng.disabled=true;
		}

	function do_ngs(theForm) {								// 8/23/08
//		theForm.frm_ngs.disabled=false;						// 9/9/08
		theForm.frm_ngs.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);
//		theForm.frm_ngs.disabled=true;
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

<?php
print "// file as of " . date("l, dS F, Y @ h:ia", filemtime(basename(__FILE__))) . "\n";
print "//" . date("n/j/y", filemtime(basename(__FILE__))) . "\n";
?>

	</SCRIPT>
	

<?php
	switch ($func){

		case 'notify': 
			print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
		if (array_key_exists('id', ($_GET))) {			// 0 -> all tickets notify
			print "<FONT CLASS='header'>Add Notify</FONT><BR /><BR />";
			if (!get_variable('allow_notify')) print "<FONT CLASS='warn'>Warning: Notification is disabled by administrator</FONT><BR /><BR />"; 
			if ($_GET['id']!=0) {
				$query = "SELECT `id`, `scope` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = ${_GET['id']} LIMIT 1";
			 	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				$the_ticket_name = $row['scope'];
				unset($result);
				}
			else {
				$the_ticket_name = "<I>All tickets</I>";			
				}
?>
			<TABLE BORDER="0">
			<FORM METHOD="POST" NAME="notify_form" ACTION="config.php?func=notify&add=true">
			<TR CLASS='even'><TD CLASS="td_label">Ticket:</TD><TD ALIGN="left"><A HREF="main.php?id=<?php print $_GET['id'];?>"><?php print $the_ticket_name;?></A></TD></TR>
			<TR CLASS='odd'><TD CLASS="td_label">Email Address:</TD><TD><INPUT MAXLENGTH="70" SIZE="40" TYPE="text" NAME="frm_email" VALUE=""></TD></TR>
<?php
	$dis = (is_super())? "" : " DISABLED "; // 1/27/09
?>
			<TR CLASS='even'><TD CLASS="td_label">Execute:</TD><TD><INPUT MAXLENGTH="150" SIZE="40" TYPE="text" NAME="frm_execute" VALUE="" <?php print $dis; ?>></TD></TR>
			<TR CLASS='odd'></TR><TD CLASS="td_label">On Patient/Action Change:</TD><TD ALIGN="left"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_on_action"></TD></TR>
			<TR CLASS='even'><TD CLASS="td_label">On Ticket Change: &nbsp;&nbsp;</TD><TD ALIGN="left"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_on_ticket"></TD></TR>
			<TR CLASS='odd'><TD CLASS="td_label">Severity filter:</TD><TD ALIGN='center'>
				All &raquo;		<input type='radio' name='frm_severity' value=1 >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				Highest &raquo;	<input type='radio' name='frm_severity' value=3 checked></TD></TR>
			
				<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $_GET['id'];?>">
			<TR CLASS='even'><TD></TD><TD ALIGN="center"><INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="button" VALUE="Submit" onClick = "validate(this.form)"></TD></TR>
			</FORM></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
			</BODY>

<SCRIPT>
	function validate(theForm) {			// notify record validate 10/23/08
		var errmsg="";
		if (!validate_email(theForm.frm_email.value.trim()))	{errmsg+="\tValid email address is required.\n";}
		if ((!(theForm.frm_on_ticket.checked)) && (!(theForm.frm_on_action.checked)))
																{errmsg+="\tOne or both checkboxes is required.\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			theForm.frm_severity[0].disabled = !(theForm.frm_severity[0].checked);
			theForm.frm_severity[1].disabled = !(theForm.frm_severity[1].checked);
			theForm.submit();
			}
		}				// end function validate(theForm)

	function validate_email(field) {
		apos=field.indexOf("@");
		dotpos=field.lastIndexOf(".");
		return (!(apos<1||dotpos-apos<2));
		}				// end function validate_email()
</SCRIPT>
							
			</HTML>
<?php
			exit();
			}				// end (array_key_exists('id', ($_GET)))
			
		else if ((array_key_exists('save', ($_GET))) && ($_GET['save']== 'true')) {
			for ($i = 0; $i<count($_POST["frm_id"]); $i++) {

				if (isset($_POST['frm_delete'][$i])) {
					$msg = "Notify deletion complete!";					// pre-set
					$query = "DELETE from $GLOBALS[mysql_prefix]notify WHERE id='".$_POST['frm_id'][$i]."' LIMIT 1";
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
					$on_ticket_val  = empty($_POST['frm_on_ticket'][$i])? "":  "1";
					$on_action_val  = empty($_POST['frm_on_action'][$i])? "":  "1";
					$on_patient_val = empty($_POST['frm_on_patient'][$i])? "": "1";;

					
//					$query = "UPDATE `$GLOBALS[mysql_prefix]notify` SET `execute_path`='".$_POST['frm_execute'][$i]."', `email_address`='".$_POST['frm_email'][$i]."', `on_action`='".$on_action_val."', `on_patient`='".$on_patient_val ."', `on_ticket`='".$on_ticket_val ."' WHERE `id`='".$_POST['frm_id'][$i]."'";

					$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
																				// 1/27/09
					$query = "UPDATE `$GLOBALS[mysql_prefix]notify` SET
						`execute_path`=".	quote_smart($_POST['frm_execute'][$i]) .",
						`email_address`=".	quote_smart($_POST['frm_email'][$i]) .",
						`on_action`='".		$on_action_val ."', 
						`on_patient`='".	$on_patient_val ."', 
						`on_ticket`='".		$on_ticket_val ."',
						`by`=".				$my_session['user_id'] .",
						`from`=".			quote_smart($_SERVER['REMOTE_ADDR']) .",
						`on`=".				quote_smart($now) ."
					
						WHERE `id`='".		$_POST['frm_id'][$i]."'";


					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//					dump ($query);
					}
				}
			
			if (!get_variable('allow_notify')) print "<FONT CLASS=\"warn\">Warning: Notification is disabled by administrator</FONT><BR /><BR />";
			print "<FONT CLASS='header'>$msg</FONT><BR /><BR />";
			}				// end array_key_exists('save')

		else if ((array_key_exists('add', ($_GET))) && ($_GET['add']== 'true')) {	//email validation check
			$email = validate_email($_POST['frm_email']);
			if (!$email['status']) {
				print "<FONT CLASS='warn'>Error: email validation failed for '" . $_POST['frm_email'] . "', " . $email['msg'] . ". Go back and check this email address.</FONT>";
				exit();
				}
		
			$on_ticket = (isset($_POST['frm_on_ticket']))? $_POST['frm_on_ticket']:0 ;
			$on_action = (isset($_POST['frm_on_action']))? $_POST['frm_on_action']:0 ;
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]notify` SET ticket_id='$_POST[frm_id]',user='$my_session[user_id]',email_address='$_POST[frm_email]',execute_path='$_POST[frm_execute]',on_action='$on_action',on_ticket='$on_ticket',severities='$_POST[frm_severity]'";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (!get_variable('allow_notify')) print "<FONT CLASS='warn'>Warning: Notification is disabled by administrator</FONT><BR /><BR />";
			print "<FONT SIZE='3'><B>Notify update complete.</B></FONT><BR /><BR />";
			}			// end array_key_exists('add')

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
				print "<TR CLASS='even'><TD CLASS='td_label'>Ticket</TD><TD CLASS=\"td_label\">&nbsp;Email</TD>";
				print '<TD CLASS="td_label">&nbsp;Execute</B></TD><TD CLASS="td_label">&nbsp;On Action&nbsp;</TD><TD CLASS="td_label">&nbsp;On Patient&nbsp;</TD><TD CLASS="td_label">&nbsp;On Ticket Change&nbsp;</TD><TD CLASS="td_label">Delete</TD></TR>';
			
				$i = 0;
				while($row = stripslashes_deep(mysql_fetch_array($result))) {
					if ($row['ticket_id']==0) {
						print "\n<TR CLASS='" .$colors[$i%2] . "'><TD><B>All</B></TD>\n";
						}
					else {
						print "\n<TR CLASS='" .$colors[$i%2] . "'><TD><A HREF='main.php?id=" .  $row['ticket_id'] . "'>#" . $row['ticket_id'] . "</A></TD>\n";	
						}
					print "<TD><INPUT MAXLENGTH=\"70\" SIZE=\"32\" VALUE=\"" . $row['email_address'] . "\" TYPE=\"text\" NAME=\"frm_email[$i]\"></TD>\n";
					print "<TD><INPUT MAXLENGTH=\"150\" SIZE=\"40\" TYPE=\"text\" VALUE=\"" . $row['execute_path'] . "\" NAME=\"frm_execute[$i]\"></TD>\n";
					print "<TD ALIGN='center'><INPUT TYPE='checkbox' VALUE='1' NAME='frm_on_action[$i]'"; print $row['on_action'] ? " checked></TD>\n" : "></TD>\n";
					print "<TD ALIGN='center'><INPUT TYPE='checkbox' VALUE='1' NAME='frm_on_patient[$i]'"; print $row['on_patient'] ? " checked></TD>\n" : "></TD>\n";
					print "<TD ALIGN='center'><INPUT TYPE='checkbox' VALUE='1' NAME='frm_on_ticket[$i]'"; print $row['on_ticket'] ? " checked></TD>\n" : "></TD>\n";
					print "<TD ALIGN='center'><INPUT TYPE='checkbox' VALUE='1' NAME='frm_delete[$i]'></TD>\n";
					print "<INPUT TYPE='hidden' NAME='frm_id[$i]' VALUE='" . $row['id'] . "'></TR>\n";
					$i++;
					}
				print "<TR CLASS='" .$colors[$i%2]  ."'><TD COLSPAN=99 ALIGN='center'><INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<INPUT TYPE='reset' VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Submit'></TD></TR></FORM>";
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
			}			// end not array_key_exists('add')
    break;


case 'profile' :					//update profile
		print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
		$get_go = (array_key_exists('go', ($_GET)))? $_GET['go']  : "" ;
		if ($get_go == 'true') {			//check passwords
			$frm_sort_desc = array_key_exists('frm_sort_desc', ($_POST))? 1: 0 ;	// checkbox handling
			extract($_POST);
			$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET passwd='$frm_hash',info='$frm_info',email='$frm_email',sortorder='$frm_sortorder',sort_desc='$frm_sort_desc',ticket_per_page='$frm_ticket_per_page' WHERE id='$my_session[user_id]'";
//			dump($query);
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print '<B>Your profile has been updated.</B><BR /><BR />';
			}
		else {
			$query = "SELECT id FROM `$GLOBALS[mysql_prefix]user` WHERE id='" . $my_session['user_id'] . "'";
			if ($my_session['user_id'] < 0 OR check_for_rows($query) == 0) {
				print __LINE__ . " Invalid user id '$my_session[user_id]'.";
				exit();
				}

			$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE id='$my_session[user_id]'";
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= mysql_fetch_array($result);
?>
			<FONT CLASS="header">Edit My Profile</FONT><BR /><BR /><TABLE BORDER="0">
			<FORM METHOD="POST" ACTION="config.php?func=profile&go=true"><INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'];?>">
<!--		<TR CLASS="even"><TD CLASS="td_label">New Password:</TD><TD><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd" VALUE=''> &nbsp;&nbsp;<B>Confirm: </B><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd_confirm"  VALUE=''></TD></TR> 11/17/09 -->
			<TR CLASS="even"><TD CLASS="td_label">Email:</TD><TD><INPUT SIZE="47" MAXLENGTH="255" TYPE="text" VALUE="<?php print $row['email'];?>" NAME="frm_email"></TD></TR>
			<TR CLASS="odd"><TD CLASS="td_label">Info:</TD><TD><INPUT SIZE="47" MAXLENGTH="255" TYPE="text" VALUE="<?php print $row['info'];?>" NAME="frm_info"></TD></TR>
<!-- 		<TR><TD CLASS="td_label">Show reporting actions:</TD><TD ALIGN="right"><INPUT TYPE="checkbox" VALUE="1" NAME="frm_reporting" <?php if($row['reporting']) print " checked";?>></TD></TR> -->
			<TR CLASS="even"><TD CLASS="td_label">Tickets per page:</TD><TD><INPUT SIZE="47" MAXLENGTH="3" TYPE="text" VALUE="<?php print $row['ticket_per_page'];?>" NAME="frm_ticket_per_page"></TD></TR>
			<TR CLASS="odd"><TD CLASS="td_label">Sort By:</TD><TD><SELECT NAME="frm_sortorder">
			<OPTION value="date" <?php if($row['sortorder']=='date') print " selected";?>>Date</OPTION>
			<OPTION value="description" <?php if($row['sortorder']=='description') print " selected";?>>Description</OPTION>
			<OPTION value="affected" <?php if($row['sortorder']=='affected') print " selected";?>>Affected</OPTION>
			</SELECT>&nbsp; Descending <INPUT TYPE="checkbox" value="1" name="frm_sort_desc" <?php if ($row['sort_desc']) print "checked";?>></TD></TR>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $my_session['user_id'];?>">
			<INPUT TYPE='hidden' NAME='frm_hash' VALUE='<?php print $row['passwd'];?>'>	<!-- 11/30/08 -->
			<TR CLASS="even">
				<TD ALIGN="center" COLSPAN=2><BR /><INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="button" VALUE="Submit" onClick = validate_prof(this.form)></TD></TR>
			</FORM></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
			</BODY>
<SCRIPT>

	function validate_prof(theForm) {			// profile form contents validation
		var errmsg="";
		if (theForm.frm_passwd.value!=theForm.frm_passwd_confirm.value)  {
			errmsg+="\tPASSWORD and CONFIRM fail to match.\n";
			}
		else {
			if ((theForm.frm_passwd.value!="") && (theForm.frm_passwd.value.trim().length<6))  {errmsg+="\tPasswd length 6 or more is required.\n";}
			}

		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			if(theForm.frm_passwd.value!="") {
				theForm.frm_hash.value = hex_md5(theForm.frm_passwd.value.trim().toLowerCase());
				theForm.frm_passwd.value = theForm.frm_passwd_confirm.value="";					// hide them
				}
			theForm.submit();
			}
		}				// end function validate prof(theForm)


	function validate(theForm) {						//profile validation	- 10/23/08
		var errmsg="";
		if (theForm.frm_passwd.value.trim().length<6)									{errmsg+="\tPasswd length 6 or more is required.\n";}
		if (theForm.frm_passwd.value.trim() != theForm.frm_passwd_confirm.value.trim())	{errmsg+="\tPasswd and confirmation must match.\n";}
		if (theForm.frm_email.value.trim().length>0) {
			if (!validate_email(theForm.frm_email.value.trim())) 						{errmsg+="\tValid email format is required.\n";	}
			}
			
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			theForm.submit();
			return true;
			}
		}				// end function validate(theForm)

</SCRIPT>
			</HTML>
<?php
			exit();
			}
    break;

case 'optimize' :
	print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
	optimize_db();
	print '<FONT CLASS="header">Database optimization complete.</FONT><BR /><BR />';
    break;

case 'reset' :
?>
			</HEAD>\n<BODY onLoad = 'ck_frames()'>
			<FONT CLASS="header">Reset Database</FONT><BR />This operation requires confirmation by entering "yes" into this box.<BR />
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
    break;

case 'settings' :
	if((isset($_GET))&& (isset($_GET['go']))&& ($_GET['go'] == 'true')) {
		print "</HEAD>\n<BODY onLoad = 'ck_frames(); parent.frames[\"upper\"].location.reload();'>\n";		// 9/21/08
		foreach ($_POST as $VarName=>$VarValue) {
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`=". quote_smart($VarValue)." WHERE `name`='".$VarName."'";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			}
		$reload_top = TRUE;			// reload top frame for possible new settings value
		print '<FONT CLASS="header">Settings saved.</FONT><BR /><BR />';
		}
	else {
		print "</HEAD>\n<BODY onLoad = 'ck_frames();'>\n";		// 9/21/08
		$evenodd = array ("even", "odd");
		print '<FONT CLASS="header">Edit Settings</FONT>  (mouseover caption for help information)<BR /><BR /><TABLE BORDER="0"><FORM METHOD="POST" NAME= "set_Form"  onSubmit="return validate_set(document.set_Form);" ACTION="config.php?func=settings&go=true">';
		$counter = 0;
		$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]settings` ORDER BY name") or do_error('config.php::list_settings', 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		while($row = stripslashes_deep(mysql_fetch_array($result))) {
			if ($row['name']{0} <> "_" ) {								// hide these
				$capt = str_replace ( "_", " ", $row['name']);
				print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD CLASS='td_label'><A HREF='#' TITLE='".get_setting_help($row['name'])."'>$capt</A>: &nbsp;</TD>";
				print "<TD><INPUT MAXLENGTH='90' SIZE='90' TYPE='text' VALUE='" . $row['value'] . "' NAME='" . $row['name'] . "'></TD></TR>\n";

				$counter++;
				}
			}		// str_replace ( search, replace, subject)
		
		print "</FORM></TABLE>\n";		// 7/16/09

?>
		<DIV ID="foo"><DIV ID="bar">		<!-- 9/26/09 -->
			<INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'><BR /><BR />
			<INPUT TYPE='button' VALUE='Reset form'  onClick='document.set_Form.reset();'><BR /><BR />
			<INPUT TYPE='button' VALUE='Apply changes'  onClick='document.set_Form.submit();'>
		</DIV></DIV>

		<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
		</BODY>
		<SCRIPT>
			try {
				parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
				parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
				parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
				}
			catch(e) {
				}
		</SCRIPT>
		</HTML>
<?php
		exit();
		}				// end else
    break;

case 'user' :
	print "</HEAD>\n<BODY onLoad = 'ck_frames()'>\n";
	if ((array_key_exists('id', ($_GET))) && ($_GET['id'] != '')) {
		if (is_administrator()) {
			$id = $_GET['id'];
			if ($id < 0 OR check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]user` WHERE id='$id'") == 0) {
				print __LINE__ . " Invalid user id '$id'.";
				exit();
				}

			$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE id='$id' LIMIT 1";
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= mysql_fetch_array($result);
			$caption = (is_administrator() || is_super())? "Edit": "View";
			$disabled = (is_administrator() || is_super())? "": " DISABLED";
?>
			<FONT CLASS="header"><?php print $caption; ?> User Data</FONT><BR /><BR />
			<TABLE BORDER="0" CELLSPACING=1>
			<FORM METHOD="POST" NAME = "user_add_Form" onSubmit="return validate_user(document.user_add_Form);" ACTION="config.php?func=user&edit=true"><INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $id;?>">
			<TR CLASS="even"><TD ALIGN="right" CLASS="td_label">User ID:</TD><TD COLSPAN=3><INPUT MAXLENGTH="20" SIZE="20" TYPE="text" VALUE="<?php print $row['user'];?>" NAME="frm_user"<?php print $disabled;?> ></TD></TR>
			<TR CLASS="odd"><TD ALIGN="right" CLASS="td_label">Password:</TD><TD COLSPAN=3><INPUT MAXLENGTH="20" SIZE="20" TYPE="password" NAME="frm_passwd"<?php print $disabled;?>> &nbsp;&nbsp;<B>Confirm: </B><INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd_confirm"<?php print $disabled;?>></TD></TR>
			<TR CLASS="even" VALIGN='middle'><TD ALIGN="right" CLASS="td_label">Level:</TD><TD COLSPAN=3>&nbsp;&nbsp;&nbsp;
<?php
			$checked = (intval($row['level'])==intval($GLOBALS['LEVEL_USER']))?			"checked":"" ;
			print "Operator &raquo<INPUT TYPE='radio' NAME='frm_level' VALUE='" . $GLOBALS['LEVEL_USER'] . 		"' $checked $disabled>&nbsp;&nbsp;&nbsp;\n";
			$checked = (intval($row['level'])==intval($GLOBALS['LEVEL_GUEST']))? 			"checked":"" ;
			print " Guest &raquo;<INPUT TYPE='radio' NAME='frm_level' VALUE='" . $GLOBALS['LEVEL_GUEST'] . 		"' $checked $disabled>&nbsp;&nbsp;&nbsp;\n";
			$checked = (intval($row['level'])==intval($GLOBALS['LEVEL_ADMINISTRATOR']))? 	"checked":"" ;
			print " Administrator &raquo;<INPUT TYPE='radio' NAME='frm_level' VALUE='" . $GLOBALS['LEVEL_ADMINISTRATOR'] ."' $checked $disabled>&nbsp;&nbsp;&nbsp;\n";
			if (is_super()) {				// 6/9/08
				$checked = (intval($row['level'])==intval($GLOBALS['LEVEL_SUPER']))? 	"checked":"" ;
				print " Super &raquo;<INPUT TYPE='radio' NAME='frm_level' VALUE='" . $GLOBALS['LEVEL_SUPER'] ."' $checked $disabled>&nbsp;&nbsp;&nbsp;\n";
				}
			$checked = (intval($row['level'])==intval($GLOBALS['LEVEL_MEMBER']))? 	"checked":"" ;						// 12/15/08
			print " Member &raquo;<INPUT TYPE='radio' NAME='frm_level' VALUE='" . $GLOBALS['LEVEL_MEMBER'] ."' $checked $disabled>\n";
?>			
			</TD></TR>
			<TR><TD COLSPAN=4 ALIGN='center'>&nbsp;</TD></TR.
			<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Last name: </TD>
				<TD><INPUT ID="ID3" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_name_l" VALUE="<?php print $row['name_l'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>></TD>
				<TD CLASS="td_label" ALIGN="right">First: </TD>
				<TD><INPUT ID="ID4" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_name_f" VALUE="<?php print $row['name_f'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>> </TD></TR>
			<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">MI: </TD>
				<TD><INPUT ID="ID5" MAXLENGTH="4" SIZE=3 type="text" NAME="frm_name_mi" VALUE="<?php print $row['name_mi'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>></TD>
				<TD CLASS="td_label" ALIGN="right">DOB: </TD><TD><INPUT MAXLENGTH=16 ID="fd6" SIZE=16 type="text" NAME="frm_dob" VALUE="<?php print $row['dob'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?> /></TD></TR>
			<TR CLASS="odd"><TD CLASS="td_label" ALIGN="right">Callsign: </TD><TD><INPUT SIZE="20" MAXLENGTH="20" TYPE="text" NAME="frm_callsign" VALUE="<?php print $row['callsign'];?>"<?php print $disabled;?>/></TD>
				<TD CLASS="td_label" ALIGN="right">Ident: </TD>
				<TD><INPUT ID="ID17" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_ident" VALUE="<?php print $row['ident'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>> </TD></TR>
			<TR CLASS="even"><TD CLASS="td_label" ALIGN="right">Info: </TD><TD COLSPAN=3><INPUT SIZE="83" MAXLENGTH="83" TYPE="text" NAME="frm_info" VALUE="<?php print $row['info'];?>"<?php print $disabled;?>></TD></TR>
			<TR CLASS="odd"><TD CLASS="td_label" ALIGN="right">Email: </TD><TD><INPUT SIZE="32" MAXLENGTH="32" TYPE="text" NAME="frm_email" VALUE="<?php print $row['email'];?>"<?php print $disabled;?>></TD>
				<TD CLASS="td_label" ALIGN="right">&nbsp;&nbsp;&nbsp;&nbsp;Alternate: </TD>
				<TD><INPUT ID="ID24" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_email_s" VALUE="<?php print $row['email_s'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>> </TD></TR>
			<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"> Street addr: </TD>
				<TD COLSPAN=3><INPUT ID="ID8" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_addr_street" VALUE="<?php print $row['addr_street'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>> </TD></TR>
			<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">City: </TD>
				<TD><INPUT ID="ID9" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_addr_city" VALUE="<?php print $row['addr_city'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>></TD>
				<TD CLASS="td_label" ALIGN="right">St: </TD>
				<TD><INPUT ID="ID10" MAXLENGTH="2" SIZE=2 type="text" NAME="frm_addr_st" VALUE="<?php print $row['addr_st'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>> </TD></TR>
			<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Phone: </TD>
				<TD><INPUT ID="ID19" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_phone_p" VALUE="<?php print $row['phone_p'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>></TD>
				<TD CLASS="td_label" ALIGN="right">Alternate: </TD><TD>
					<INPUT ID="ID20" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_phone_s" VALUE="<?php print $row['phone_s'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>> </TD></TR>
			<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Mobile: </TD>
				<TD><INPUT ID="ID21" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_phone_m" VALUE="<?php print $row['phone_m'];?>" onChange = "this.value=JSfnTrim(this.value)"<?php print $disabled;?>> </TD></TR>
<?php if ((is_administrator() || is_super())) { ?>					
			<TR CLASS="even" VALIGN='top'><TD CLASS="td_label">Remove User: </TD><TD COLSPAN=3> &raquo; <INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove"></TD></TR>
<?php } ?>			
			<TR CLASS="odd"><TD></TD>
				<TD COLSPAN=3 ALIGN="center">
					<INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if ((is_administrator() || is_super())) { ?>					
					<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<INPUT TYPE="submit" VALUE="Submit">
<?php } ?>					
				</TD></TR>
				<INPUT TYPE='hidden' NAME='frm_hash' VALUE='<?php print $row['passwd'];?>'>	<!-- 11/30/08 -->
				<INPUT TYPE='hidden' NAME='frm_func' VALUE='e'>

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
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]user` WHERE id='$_POST[frm_id]' LIMIT 1";
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	
				//delete notifies belonging to user
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]notify` WHERE user='$_POST[frm_id]'";
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				
				print "<B>User <i>" . $_POST['frm_user'] . "</i> has been deleted from database.</B><BR /><BR />";
				}			
			}
		else {
			$pass = empty($_POST['frm_hash'])? "" : "`passwd`='$_POST[frm_hash]',";		// note trailing comma
			$fields = " `addr_city` = " . quote_smart(trim($_POST['frm_addr_city'])) . ",	
						`addr_st` = " . quote_smart(trim($_POST['frm_addr_st'])) . ",	
						`addr_street` = " . quote_smart(trim($_POST['frm_addr_street'])) . ",
						`callsign` = " . quote_smart(trim($_POST['frm_callsign'])) . ",	
						`dob` = " . quote_smart(trim($_POST['frm_dob'])) . ",		
						`email` = " . quote_smart(trim($_POST['frm_email'])) . ",			
						`email_s` = " . quote_smart(trim($_POST['frm_email_s'])) . ",	
						`ident` = " . quote_smart(trim($_POST['frm_ident'])) . ",			
						`info` = " . quote_smart(trim($_POST['frm_info'])) . ",		
						`level` = " . quote_smart(trim($_POST['frm_level'])) . ",			
						`name_f` = " . quote_smart(trim($_POST['frm_name_f'])) . ",		
						`name_l` = " . quote_smart(trim($_POST['frm_name_l'])) . ",		
						`name_mi` = " . quote_smart(trim($_POST['frm_name_mi'])) . ",	
						`phone_m` = " . quote_smart(trim($_POST['frm_phone_m'])) . ",	
						`phone_p` = " . quote_smart(trim($_POST['frm_phone_p'])) . ",	
						`phone_s` = " . quote_smart(trim($_POST['frm_phone_s'])) . ",	
						`user` = " . quote_smart(trim($_POST['frm_user']));
				
			$where = " WHERE `id`=" . $_POST['frm_id'];

			$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET " . $pass . $fields . $where;
//			$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET `user`='$_POST[frm_user]', `callsign`='$_POST[frm_callsign]'," . $pass . " `info`='$_POST[frm_info]',`level`='$_POST[frm_level]' WHERE `id`='$_POST[frm_id]'";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			print "<B>User <I>" .$_POST['frm_user'] . "</I> data has been updated.</B><BR /><BR />";
			}
		}		// end if ($_GET['edit']
		
	else if(($_GET['func'] == 'user') && ($_GET['add'] == 'true')) {
		if (is_administrator()) {
//			if($_GET['go'] == 'true') {
			if ((array_key_exists('go', ($_GET))) && ($_GET['go']== 'true')) {
				if($_POST['frm_passwd'] == $_POST['frm_passwd_confirm']) {						// 11/30/08
					$query = sprintf("INSERT INTO`$GLOBALS[mysql_prefix]user` (`addr_city`,`addr_st`,`addr_street`,`callsign`,`dob`,`email`,`email_s`,`passwd`,`ident`,`info`,`level`,`name_f`,`name_l`,`name_mi`,`phone_m`,`phone_p`,`phone_s`,`user`)
						 VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",

								quote_smart(trim($_POST['frm_addr_city'])),	
								quote_smart(trim($_POST['frm_addr_st'])),	
								quote_smart(trim($_POST['frm_addr_street'])),
								quote_smart(trim($_POST['frm_callsign'])),	
								quote_smart(trim($_POST['frm_dob'])),		
								quote_smart(trim($_POST['frm_email'])),			
								quote_smart(trim($_POST['frm_email_s'])),	
								quote_smart(trim($_POST['frm_hash'])),		
								quote_smart(trim($_POST['frm_ident'])),			
								quote_smart(trim($_POST['frm_info'])),		
								quote_smart(trim($_POST['frm_level'])),			
								quote_smart(trim($_POST['frm_name_f'])),		
								quote_smart(trim($_POST['frm_name_l'])),		
								quote_smart(trim($_POST['frm_name_mi'])),	
								quote_smart(trim($_POST['frm_phone_m'])),	
								quote_smart(trim($_POST['frm_phone_p'])),	
								quote_smart(trim($_POST['frm_phone_s'])),	
								quote_smart(trim($_POST['frm_user'])));		

//					dump($query);

					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					print "<B>User <i>'$_POST[frm_user]'</i> has been added.</B><BR /><BR />";
					}
				else {
					print "Passwords don't match. Please try again.<BR />";
					?>
					<BR />701 <TABLE BORDER="0">
					<FORM METHOD="POST" NAME = "user_add_Form" onSubmit="return validate_user(document.user_add_Form);" ACTION="config.php?func=user&add=true&go=true">
					<TR CLASS="even"><TD CLASS="td_label">User ID:</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="text" VALUE="<?php print $_POST['frm_user'];?>" NAME="frm_user"></TD></TR>
					<TR CLASS="odd"><TD CLASS="td_label">Password</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="password" NAME="frm_passwd"></TD></TR>
					<TR CLASS="even"><TD CLASS="td_label">Confirm Password: &nbsp;&nbsp;</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="password" NAME="frm_passwd_confirm"></TD></TR>
					<TR CLASS="odd"><TD CLASS="td_label">Callsign:</TD><TD><INPUT MAXLENGTH="20" SIZE="20" TYPE="text" VALUE="<?php print $_POST['frm_callsign'];?>" NAME="frm_callsign"></TD></TR>
					<TR CLASS="even"><TD CLASS="td_label">Level:</TD><TD>
<?php
				if (is_super()) {				// 6/9/08
?>				
						<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_SUPER'];?>" NAME="frm_level" <?php print is_super()?"checked":"";?>> Super<BR />
<?php
					}
?>						
						<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_ADMINISTRATOR'];?>" NAME="frm_level" <?php print is_administrator()?"checked":"";?>> Administrator<BR />
						<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_USER'];?>" NAME="frm_level" <?php print is_user()?"checked":"";?>> Operator<BR />
						<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_GUEST'];?>" NAME="frm_level" <?php print is_guest()?"checked":"";?>> Guest<BR />
						</TD></TR>
					<TR CLASS="odd"><TD CLASS="td_label">Info:</TD><TD><INPUT SIZE="40" MAXLENGTH="80" TYPE="text" VALUE="<?php print $_POST['frm_info'];?>" NAME="frm_info"></TD></TR>
					<TR CLASS="even"><TD></TD><TD><INPUT TYPE="button" VALUE="Cancel" onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit"></TD></TR>
					</FORM></TABLE>
					<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
					</BODY>
					</HTML>
<?php
					exit();
					}
				}
			else {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name` ASC";		// 6/30/09
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				$sel_str = "<SELECT ID='units' NAME='units' STYLE= 'display:none'><\n\t<OPTION VALUE=0 SELECTED>Select one</OPTION>\n";
				while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		
					$sel_str .= "\t<OPTION VALUE='{$row['id']}'>{$row['name']}</OPTION>\n";		
					}
				$sel_str .= "\n</SELECT>\n";			

?>
				<FONT CLASS="header">Add User</FONT><BR /><BR />

				<FORM METHOD="POST" NAME = "user_add_Form" onSubmit="return validate_user(document.user_add_Form);"  ACTION="config.php?func=user&add=true&go=true">
				<TABLE BORDER=0 CELLSPACING=1>
				<TR><TD COLSPAN=4 ALIGN='center'><FONT COLOR='red'>*</FONT> Required</TD></TR>
				<TR CLASS="even"><TD CLASS="td_label" ALIGN="right"> User ID: <FONT COLOR='red'>*</FONT></TD><TD COLSPAN=3><INPUT MAXLENGTH="20" SIZE="20" TYPE="text" NAME="frm_user" VALUE=""></TD></TR>
				<TR CLASS="odd"><TD CLASS="td_label" ALIGN="right"> Password: <FONT COLOR='red'>*</FONT></TD><TD COLSPAN=3><INPUT MAXLENGTH="20" SIZE="20" TYPE="password" NAME="frm_passwd" VALUE="">&nbsp;&nbsp; <B>Confirm: </B> <INPUT MAXLENGTH="255" SIZE="16" TYPE="password" NAME="frm_passwd_confirm"></TD></TR>
				<TR CLASS="even" VALIGN='baseline'><TD CLASS="td_label" ALIGN="right"> Level: <FONT COLOR='red'>*</FONT></TD><TD COLSPAN=3>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (is_super()) { ?>	<!-- / 6/9/08 -->				
					 Super  &raquo;<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_SUPER'];?>" NAME="frm_level">&nbsp;&nbsp;&nbsp;
<?php 					}  ?>
					Admin &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_ADMINISTRATOR'];?>" NAME="frm_level">&nbsp;&nbsp;
					Operator &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_USER'];?>" NAME="frm_level">&nbsp;&nbsp;
					Guest &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_GUEST'];?>" NAME="frm_level"> &nbsp;&nbsp;
					Member &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_MEMBER'];?>" NAME="frm_level"> 	<!-- 3/3/09 -->
					Unit &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['LEVEL_UNIT'];?>" NAME="frm_level" onClick = "set_unit()"> 	<!-- 6/30/09 -->
					</TD></TR>
				<TR><TD COLSPAN=4 ALIGN='center'>&nbsp;</TD></TR.
				<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Last name: </TD>
					<TD><INPUT ID="ID3" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_name_l" VALUE="" onChange = "this.value=JSfnTrim(this.value)"></TD>
					<TD CLASS="td_label" ALIGN="right">First: </TD>
					<TD><INPUT ID="ID4" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_name_f" VALUE="" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
				<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">MI: </TD>
					<TD><INPUT ID="ID5" MAXLENGTH="4" SIZE=3 type="text" NAME="frm_name_mi" VALUE="" onChange = "this.value=JSfnTrim(this.value)"></TD>
					<TD CLASS="td_label" ALIGN="right">DOB: </TD><TD><INPUT MAXLENGTH=16 ID="fd6" SIZE=16 type="text" NAME="frm_dob" VALUE="" onChange = "this.value=JSfnTrim(this.value)"/></TD></TR>
				<TR CLASS="odd"><TD CLASS="td_label" ALIGN="right">Callsign: </TD><TD><INPUT SIZE="20" MAXLENGTH="20" TYPE="text" NAME="frm_callsign" VALUE=""></TD>
					<TD CLASS="td_label" ALIGN="right">Ident: </TD>
					<TD><INPUT ID="ID17" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_ident" VALUE="" onChange = "this.value=JSfnTrim(this.value)"> <?php print $sel_str;?></TD></TR>
				<TR CLASS="even"><TD CLASS="td_label" ALIGN="right">Info: </TD><TD COLSPAN=3><INPUT SIZE="83" MAXLENGTH="83" TYPE="text" NAME="frm_info" VALUE=""></TD></TR>
				<TR CLASS="odd"><TD CLASS="td_label" ALIGN="right">Email: </TD><TD><INPUT SIZE="32" MAXLENGTH="32" TYPE="text" NAME="frm_email" VALUE=""></TD>
					<TD CLASS="td_label" ALIGN="right">Alternate: </TD>
					<TD><INPUT ID="ID24" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_email_s" VALUE="" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
				<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"> Street addr: </TD>
					<TD COLSPAN=3><INPUT ID="ID8" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_addr_street" VALUE="" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
				<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">City: </TD>
					<TD><INPUT ID="ID9" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_addr_city" VALUE="" onChange = "this.value=JSfnTrim(this.value)"></TD>
					<TD CLASS="td_label" ALIGN="right">St: </TD>
					<TD><INPUT ID="ID10" MAXLENGTH="2" SIZE=2 type="text" NAME="frm_addr_st" VALUE="" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
				<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Phone: </TD>
					<TD><INPUT ID="ID19" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_phone_p" VALUE="" onChange = "this.value=JSfnTrim(this.value)"></TD>
					<TD CLASS="td_label" ALIGN="right">Alternate: </TD><TD>
						<INPUT ID="ID20" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_phone_s" VALUE="" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
				<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Mobile: </TD>
					<TD><INPUT ID="ID21" MAXLENGTH="32" SIZE=32 type="text" NAME="frm_phone_m" VALUE="" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
						<INPUT TYPE='hidden' NAME='frm_func' VALUE='a'>
						<INPUT TYPE='hidden' NAME='frm_hash' VALUE=''>	<!-- 11/30/08 -->
						</TD></TR>
				<TR CLASS="even">
					<TD COLSPAN=4 ALIGN="center"><BR /><INPUT TYPE="button" VALUE="Cancel" onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<INPUT TYPE="reset" VALUE="Reset">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<INPUT TYPE="submit" VALUE="Submit"></TD>
					</TR>
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
	<SCRIPT SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
	<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>
	<STYLE>
	label, input[type="radio"]{font-size:10px; vertical-align:bottom;}
	</STYLE>
	</HEAD>
	<BODY onLoad = "ck_frames()" onunload="GUnload()">
<?php

	$get_update = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['update'])))) ) ? "" : $_GET['update'] ;

//	if($_GET['update'] == 'true') {
	if($get_update == 'true') {
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_lat]' WHERE `name`='def_lat';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_lng]' WHERE `name`='def_lng';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_zoom]' WHERE `name`='def_zoom';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_map_caption]' WHERE `name`='map_caption';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_dfz]' WHERE `name`='def_zoom_fixed';";
		$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
		print '<FONT CLASS="header">Settings saved to database.</FONT><BR /><BR />';
		}
	else {
		$lat = get_variable('def_lat');
		$lng = get_variable('def_lng');
		$checks_ar = array("","","","");
		$which = get_variable('def_zoom_fixed');
		$checks_ar[$which] = " CHECKED ";
?>	
		<TABLE BORDER=0 ID='outer'>
		<TR><TD COLSPAN=2 ALIGN='center'><FONT CLASS="header">Select Map Center/Zoom and Caption</FONT><BR /><BR /></TD></TR>
		<TR><TD>
		<TABLE BORDER="0">
		<FORM METHOD="POST" NAME= "cen_Form"  onSubmit="return validate_cen(document.cen_Form);" ACTION="config.php?func=center&update=true">
		<TR CLASS = "even"><TD CLASS="td_label">Lookup:</TD><TD COLSPAN=3>&nbsp;&nbsp;City:&nbsp;<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_city" VALUE="" />
		&nbsp;&nbsp;&nbsp;&nbsp;State:&nbsp;<INPUT MAXLENGTH="2" SIZE="2" TYPE="text" NAME="frm_st" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD COLSPAN=4 ALIGN="center"><button type="button" onClick="addrlkup()"><img src="./markers/glasses.png" alt="Lookup location." /></TD></TR> <!-- 1/21/09 -->
		<TR><TD><BR /><BR /><BR /><BR /><BR /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Caption:</TD><TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_map_caption" VALUE="<?php print get_variable('map_caption');?>" onChange = "document.getElementById('caption').innerHTML=this.value "/></TD></TR>
		<TR CLASS = "odd">
			<TD CLASS="td_label" ROWSPAN=4>Map:</TD>
			<TD ALIGN='right'>&nbsp;&nbsp;Lat:&nbsp;</TD>
			<TD colspan=2><INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=12  />
			&nbsp;&nbsp;&nbsp;&nbsp;Long:&nbsp;<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=12 DISABLED /></TD></TR>
			<TD ALIGN='right' onClick = "usng_to_map()"><U>USNG</U>:&nbsp;</TD><TD COLSPAN=2><INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($lat, $lng) ;?>" SIZE=20  /></TD></TR>
		<TR CLASS = "odd">
			<TD ALIGN='right'>&nbsp;&nbsp;Zoom:&nbsp;</TD>
			<TD><INPUT TYPE="text" NAME="frm_zoom" VALUE="<?php print get_variable('def_zoom');?>" SIZE=4 disabled /></TD></TR>	<!-- 4/5/09 -->
		<TR VALIGN='baseline'><TD CLASS="td_label" ALIGN='right'>Dynamic zoom:</TD><TD ALIGN='center' COLSPAN=2>&nbsp;&nbsp;
		 		Yes &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='0' <?php print $checks_ar[0]; ?> onClick = "document.cen_Form.frm_dfz.value=0";> &nbsp;&nbsp;
				<B>Situation</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='1' <?php print $checks_ar[1]; ?> onClick = "document.cen_Form.frm_dfz.value=1";>&nbsp;&nbsp;
				<B>Units</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='2' <?php print $checks_ar[2]; ?> onClick = "document.cen_Form.frm_dfz.value=2";>&nbsp;&nbsp;
				<B>Both</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='3' <?php print $checks_ar[3]; ?> onClick = "document.cen_Form.frm_dfz.value=3";></TD></TR>
					
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS = "even"><TD COLSPAN=5 ALIGN='center'>
			<INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset' onClick = "map_cen_reset();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Submit'></TD></TR>
			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>">				<!-- // 9/16/08 -->
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>">
			<INPUT TYPE="hidden" NAME="frm_dfz" VALUE="<?php print $which;?>">
		</FORM></TABLE>
		</TD><TD><DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
		<BR><CENTER><FONT CLASS="header"><SPAN ID="caption">Click/Zoom to new default position</SPAN></FONT></CENTER>
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
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_value]' WHERE `name`='gmaps_api_key';";
		
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
			<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset' onClick = "map_cen_reset();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Submit'></TD></TR>
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
?>
<SCRIPT>
function copyit() {						// 11/30/09
	var tempval= document.the_form.the_dump;
	tempval.focus();
	tempval.select();
	therange=tempval.createTextRange();
	therange.execCommand("Copy");
	}
//  End -->
</SCRIPT>

<?php
	$_echo ="\n\n-- start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start  start \n";
	$_echo .="\n-- Dumping tables for database: $mysql_db\n"; //write "intro" ;)
	
	for ($i=0;$i<$broj;$i++) {						//dump all tables:
		$table_name = $backup->tables[$i]; 			//get table name
		$backup->dump_table($table_name); 			//dump it to output (buffer)
		$_echo .=htmlspecialchars($backup->output); 	//write output
		}
	$_echo .="\n\n-- end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end  end \n";
	
	echo "\n<FORM NAME='the_form'><TEXTAREA NAME ='the_dump' COLS=120 ROWS=20>{$_echo}</TEXTAREA>";
	echo "<BR /><BR /><INPUT onclick='copyit()' type='button' value='Click to copy the dump' name='cpy'\>\n</FORM>\n";

	break;
    
case 'delete' :	
	print "<BODY onLoad = 'ck_frames()'>\n";
	$subfunc = (array_key_exists ('subfunc',$_GET ))? $_GET['subfunc']: "list";
	switch ($subfunc) {
		case 'list':
?>		
			<FORM METHOD="POST" NAME= "del_Form" ACTION="config.php?func=delete&subfunc=confirm">
<?php
			$query	= "SELECT *,UNIX_TIMESTAMP(problemend) AS problemend FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = " . $GLOBALS['STATUS_CLOSED']. " ORDER BY `scope`";
	
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
				<INPUT TYPE='button' VALUE='Submit' 	onClick = 'collect();'></TD></TR>
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
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Submit'  onClick='document.can_Form.submit();'>
			</FORM>
			</BODY>
<?php
			exit();
	
		    break;
		    
	
		default :   
		}				// end switch ($subfunc)    
    
	default:
	}						// end switch ($func)
		
?>
		</HEAD>
		<BODY onLoad = 'ck_frames()'>
<?php
	if(!(is_guest())) {								// 6/9/08
?>
		<LI CLASS='spl' onClick = "do_mail_win()">Email users</A>	<!-- 6/30/09 -->
<?php
			}
	if (is_administrator() || is_super()) { 	// SHOW MENU BASED ON USER LEVEL
?>			
		
		<LI><A HREF="config.php?func=user&add=true">Add user</A>
		<LI><A HREF="config.php?func=settings">Edit Settings</A>
		<LI><A HREF="config.php?func=center">Set Default Map</A>
		<LI><A HREF="config.php?func=api_key">Set GMaps API key</A>
<?php
		if(is_super()) {								// 6/9/08
?>	
			<LI><A HREF="config.php?func=reset">Reset Database</A>
			<LI><A HREF="config.php?func=optimize">Optimize Database</A>
			<LI><A HREF="config.php?func=delete">Delete Closed Tickets</A>
			<LI><A HREF="config.php?func=dump">Dump DB to screen</A>
			<LI><A HREF="#" onClick = "do_Post('session');">Session</A>
			
<?php
			}								// end if(is_super()
		}								// end if (is_administrator()|| is_super() ) -- latitude.php
?>
		<LI CLASS='spl'>Test:&nbsp;&nbsp;&nbsp;<A HREF="#" onClick = "do_test()"><U>APRS</U></A>&nbsp;&nbsp;&nbsp;&nbsp;
			<A HREF="#" onClick = "do_instam()"><U>Instamapper</U></A>		&nbsp;&nbsp;&nbsp;&nbsp;
			<A HREF="#" onClick = "do_smtp()"><U>SMTP Mail</U></A>		&nbsp;&nbsp;&nbsp;&nbsp;
		<A HREF="#" onClick = "do_glat()"><U>Google Latitude</U></A>		&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 7/28/09 -->
		<A HREF="#" onClick = "do_locatea()"><U>LocateA</U></A>		&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 7/28/09 -->
		<A HREF="#" onClick = "do_gtrack()"><U>Gtrack</U></A>			<!-- 7/28/09 -->
<?php
		if (!is_guest()) {
?>		
		<LI><A HREF="config.php?func=profile">Edit My Profile</A>			<!-- 12/1/08 -->
<?php
		}																	// end if (!is_guest())
	if (is_administrator() || is_super()) {									// super or admin - 9/24/08
?>
		<LI><A HREF="config.php?func=notify">Add/Edit Notifies</A>
		<LI><A HREF="config.php?func=notify&id=0">All-Tickets Notify</A>
		<BR />
		<BR />
		<LI><A HREF="#" onClick = "do_Post('contacts');">Contacts</A>
		<LI><A HREF="#" onClick = "do_Post('in_types');">Incident types</A>
		<LI><A HREF="#" onClick = "do_Post('unit_types');">Unit types</A><!-- 10/8/08,  6/4/09 -->
		<LI><A HREF="#" onClick = "do_Post('un_status');">Unit status types</A>

<?php
		if (mysql_table_exists("$GLOBALS[mysql_prefix]constituents")) 	{		// 6/4/09
?>	

		<BR />
		<BR />
		<LI><A HREF="#" onClick = "do_Post('city');">Cities</A>
		<LI><A HREF="#" onClick = "do_Post('constituents');">Constituents</A>
		<BR />
		<BR />

<?php
		}
		if ($istest) {
?>
			<LI><A HREF="#" onClick = "do_Post('log');">Log</A>
			<LI><A HREF="#" onClick = "do_Post('settings');">Settings</A>
			<LI><A HREF="#" onClick = "do_Post('ticket');">Tickets</A>
			<LI><A HREF="#" onClick = "do_Post('responder');">Units</A>
			<LI><A HREF="#" onClick = "do_Post('action');">Actions</A>
			<LI><A HREF="#" onClick = "do_Post('patient');">Patients</A>	
			<LI><A HREF="tables.php">Tables</A>

<?php
			}
			
		if (mysql_table_exists("$GLOBALS[mysql_prefix]fac_status")) 	{		// 10/5/09
?>	

			<BR />
			<LI><A HREF="#" onClick = "do_Post('fac_status');">Facility Status</A>
<?php
		}

		if (mysql_table_exists("$GLOBALS[mysql_prefix]fac_types")) 	{		// 10/5/09
?>	

			<BR />
			<LI><A HREF="#" onClick = "do_Post('fac_types');">Facility Types</A>
			<BR />
			<BR />
<?php
		}
			
		}
	print "<BR /><BR />\n";
	list_users();		// 9/24/08

	print "<BR /><BR />";
	print logged_on();
	print "<BR /><BR />";
	show_stats();
	
?>
	<FORM NAME='tables' METHOD = 'post' ACTION='tables.php'>
	<INPUT TYPE='hidden' NAME='func' VALUE='r'>
	<INPUT TYPE='hidden' NAME='tablename' VALUE=''>
	</FORM>
<!-- <INPUT TYPE='button' VALUE='Tables' onclick="document.tables.submit();"> -->

<?php
print "</BODY>\n";
	
function map_cen () {				// specific to map center
	$lat = get_variable('def_lat'); $lng = get_variable('def_lng');
?>
<SCRIPT>
	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08		
	
	function do_coords(inlat, inlng) { 										 //9/14/08
		if((inlat.length==0)||(inlng.length==0)) {return;}
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);		
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(Math.abs(inval));
		d  = Math.floor(d);
		var mi = (Math.abs(inval)-d)*60;	// fraction * 60
		var m = Math.floor(mi)				// min's as fraction
		var si = (mi-m)*60;					// to sec's
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	function lat2ddm(inlat) {				//  lat to degr, dec.min's - 9/9/089/7/08
		var x = new Number(Math.abs(inlat));
		var degs  = Math.floor(x);				// degrees
		var mins = ((Math.abs(x-degs)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return degs + '\260'  + mins +"'" + nors;
		}
	
	function lng2ddm(inlng) {				//  lng to degr, dec.min's - 9/9/089/7/08
		var x = new Number(Math.abs(inlng));
		var degs  = Math.floor(x);				// degrees
		var mins = ((Math.abs(x-degs)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return degs + '\260' + mins +"'" + eorw;
		}

	function do_lat_fmt(inlat) {				// 9/9/08
		switch(lat_lng_frmt) {
			case 0:
				return inlat;
			  	break;
			case 1:
				return ll2dms(inlat);
			  	break;
			case 2:
				return lat2ddm(inlat);
			 	break;
			default:
				alert ("error " + 1023);
			}	
		}

	function do_lng_fmt(inlng) {
		switch(lat_lng_frmt) {
			case 0:
				return inlng;
			  	break;
			case 1:
				return ll2dms(inlng);
			  	break;
			case 2:
				return lng2ddm(inlng);
			 	break;
			default:
				alert ("error " + 1039);
			}	
		}

	function usng_to_map(){			// usng to LL array			- 5/4/09
		tolatlng = new Array();
		USNGtoLL(document.cen_Form.frm_ngs.value, tolatlng);
		var point = new GLatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));

		map.setCenter(point, 9);
		var marker = new GMarker(point);
		map.addOverlay(new GMarker(point, cross));
		
		do_lat (point.lat());
		do_lng (point.lng());
//		do_ngs(document.cen_Form);		// 9/16/08						
		}				// end function

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
						do_ngs(document.cen_Form);		// 9/16/08						
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
//	var map = new GMap2(document.getElementById("div"), {draggableCursor: 'crosshair', draggingCursor: 'pointer'});	
	var myZoom;
	var geocoder = new GClientGeocoder();
	var cross;
	
	map = new GMap2(document.getElementById('map'));
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>	
	map.addControl(new GOverviewMapControl());

	var baseIcon = new GIcon();						// 9/16/08
	baseIcon.iconSize=new GSize(32,32);
	baseIcon.iconAnchor=new GPoint(16,16);
	cross = new GIcon(baseIcon, "./markers/crosshair.png", null);

//	map.setCenter(new GLatLng(<?php print $lat; ?>, <?php print $lng; ?>), <?php print get_variable('def_zoom');?>);	// larger # => tighter zoom

	var center = new GLatLng(<?php print get_variable('def_lat') ?>, <?php print get_variable('def_lng'); ?>);
	map.setCenter(center, <?php print get_variable('def_zoom');?>);
	var thisMarker  = new GMarker(center, {icon: cross, draggable:false} );				// 9/16/08

//	map.addOverlay(marker);
	map.addOverlay(thisMarker);
	map.enableScrollWheelZoom(); 	

	GEvent.addListener(map, "click", function(overlay, latlng) {
		if (latlng) {
//			alert(latlng.lat().toFixed(6));
			map.clearOverlays();
			
			thisMarker  = new GMarker(latlng, {icon: cross, draggable:false}  );		// 9/16/08
			map.setCenter(thisMarker.getPoint());
			map.addOverlay(thisMarker);
//			GEvent.addListener(thisMarker, "dragstart", function() {
//				alert("start");
//				});
			var lat = new Number(latlng.lat());
			var lng = new Number(latlng.lng());
			
			do_lat (lat.toFixed(6));
			do_lng (lng.toFixed(6));
			do_ngs(document.cen_Form);			// 9/16/08
			GEvent.addListener(thisMarker, "dragend", function() {
//				alert(1145);
				map.setCenter(marker.getPoint());
				var gp_lat = new Number(marker.getPoint().lat());
				var gp_lng = new Number(marker.getPoint().lng());
				do_lat (gp_lat.toFixed(6));
				do_lng (gp_lng.toFixed(6));
				do_ngs(document.cen_Form);			// 9/16/08
				});
				
			map.addOverlay(thisMarker);
			}		// end if (latlng)
		});		// end GEvent.addListener()
		
	var theCenter ;
	
	GEvent.addListener(map, "zoomstart", function() {
		theCenter = marker.getPoint();							// save center
		});

	GEvent.addListener(map, "zoomend", function(oldzoom,newzoom) {
		do_zoom (newzoom);										// set form values
		map.setCenter(theCenter);								// to original center
		});

	</SCRIPT>
<?php
	}		// end function map_cen()
?>
</HTML>
