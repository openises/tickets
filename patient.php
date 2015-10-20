<?php 
/*

ALTER TABLE `pre_patient` CHANGE `insurance_id` `insurance_id` INT( 3 ) NULL DEFAULT NULL 

8/16/08	lots of changes; date_dropdown used, lock icon for date entry control, date validation, 'mysql_fetch_assoc' vs 'fetch_array', 'delete' process, 'LIMIT 1' added
10/1/08	added error reporting
10/7/08	set  WRAP="virtual"
10/19/08 added 'required' flag
10/22/08 added 'priorities' as notify selection criteria
1/21/09 added show butts - re button menu
2/12/09 corrections for am/pm handling, added dollar function
3/18/10 log corrections made
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/15/10	added dupe prevention, per JG email
8/27/10 fmp call added
11/30/10 get_text('patient') added
12/15/10 Patient ID added as patient identifier
3/15/11 changed stylesheet.php to stylesheet.php
4/22/11 addslashes() added for embedded apostrophes
6/10/11 added intrusion detection, accommodate window operation
7/27/11 fixed, per kb email
8/4/11 added call to google maps script
7/3/2013 - socket2me conditioned on internet and broadcast settings
7/12/13 - revised to catch 0 value in gender and insurance.
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

@session_start();
session_write_close();
require_once('incs/functions.inc.php');	
do_login(basename(__FILE__));
if ((isset($_REQUEST['ticket_id'])) && 	(strlen(trim($_REQUEST['ticket_id']))>6)) {	shut_down();}			// 6/10/11
require_once($_SESSION['fmp']);		// 8/27/10
if($istest) {
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
$get_action = (array_key_exists ( "action", $_REQUEST ))? $_REQUEST['action'] : "new" ;
$api_key = get_variable('gmaps_api_key');
$gmaps = $_SESSION['internet'];
//dump($get_action);	

$fullname =	 		get_text("Full name");
$dateofbirth =	 	get_text("Date of birth");
$gender =	 		get_text("Gender");
$insurance =	 	get_text("Insurance");
$facilitycontact = 	get_text("Facility contact");
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - <?php print get_text("Patient");?> Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="8/16/08">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT>
function ck_frames() {		//  onLoad = "ck_frames()"
<?php	if (array_key_exists('in_win', $_GET)) {echo "\n return;\n";} ?>	// 6/10/11

	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();										// 1/21/09
		}
	}		// end function ck_frames()
	
	if(document.all && !document.getElementById) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}				

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	function $() {									// 2/11/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function do_cancel () {				// 6/10/11
<?php	
	$can_str = (array_key_exists('in_win', $_GET))? "window.close()" : "history.back()";
	echo $can_str;
?>	
		}				// end function do_cancel ()

	function chknum(str) {
		var nums = str.trim().replace(/\D/g, "" );							// strip all non-digits
		return (nums == str.trim());
		}
	
	function chkval(val, lo, hi) { 
		return  (chknum(val) && !((val> hi) || (val < lo)));}

	function datechk_r(theForm) {		// as-of vs now
		var yr = theForm.frm_year_asof.options[theForm.frm_year_asof.selectedIndex].value;
		var mo = theForm.frm_month_asof.options[theForm.frm_month_asof.selectedIndex].value;
		var da = theForm.frm_day_asof.options[theForm.frm_day_asof.selectedIndex].value;

		var start = new Date();
		start.setFullYear(yr, mo-1, da);
		start.setHours(theForm.frm_hour_asof.value, theForm.frm_minute_asof.value, 0,0);
	
		var end = new Date();
		return (start.valueOf() <= end.valueOf());	
		}

	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_name.value == "")						{errmsg+= "\tID/Name is required\n";}
		if (theForm.frm_gender_val.value==0) 					{errmsg+= "\t<?php echo $gender;?> required\n";}
//		if (theForm.frm_ins_id.value==0) 						{errmsg+= "\t<?php echo $insurance;?> selection required\n";}		// 4/7/2014
		if (theForm.frm_description.value == "")				{errmsg+= "\tDescription is required\n";}
		do_unlock(theForm) ;
		if (!chkval(theForm.frm_hour_asof.value, 0,23)) 		{errmsg+= "\tAs-of time error - Hours\n";}
		if (!chkval(theForm.frm_minute_asof.value, 0,59)) 		{errmsg+= "\tAs-of time error - Minutes\n";}
		if (!datechk_r(theForm))								{errmsg+= "\tAs-of date/time error - future?\n" ;}

		if (errmsg!="") {
			do_lock(theForm);
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
<?php
		if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) { 		// 7/2/2013
?>
			var theMessage = "New  <?php print get_text('Patient');?> record by <?php echo $_SESSION['user'];?>";
			broadcast(theMessage ) ;
<?php
	}			// end if (broadcast)
?>						
			theForm.submit();
			}
		}				// end function validate(theForm)

	function set_signal(inval) {
		var temp_ary = inval.split("|", 2);		// inserted separator
		if (document.patientAdd) {
			var lh_sep = (document.patientAdd.frm_description.value.trim().length>0)? " " : "";
			document.patientAdd.frm_description.value+=lh_sep + temp_ary[1] + ' ';		
			document.patientAdd.frm_description.focus();		
			}
		else {
		var lh_sep = (document.patientEd.frm_description.value.trim().length>0)? " " : "";
			document.patientEd.frm_description.value+= lh_sep + temp_ary[1] + ' ';		
			document.patientEd.frm_description.focus();		
			}
		}		// end function set_signal()

	function do_asof(theForm, theBool) {							// 8/10/08
		theForm.frm_year_asof.disabled = theBool;
		theForm.frm_month_asof.disabled = theBool;
		theForm.frm_day_asof.disabled = theBool;
		theForm.frm_hour_asof.disabled = theBool;
		theForm.frm_minute_asof.disabled = theBool;

		}

	function do_unlock(theForm) {									// 8/10/08
		do_asof(theForm, false)
		document.getElementById("lock").style.visibility = "hidden";		
		}
		
	function do_lock(theForm) {										// 8/10/08
		do_asof(theForm, true)
		document.getElementById("lock").style.visibility = "visible";
		}
		
	function do_reset (the_form) {
		do_lock(the_form);
		the_form.reset();
		the_form.frm_ins_id.value="";
		the_form.frm_gender_val.value=0;
		}

	</SCRIPT>
<?php				// 7/3/2013
	if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) { 	
		require_once('./incs/socket2me.inc.php');		// 5/22/2013
		}
?>
	</HEAD>
<?php 
	print (($get_action == "add")||($get_action == "update"))? "<BODY onLoad = 'ck_frames();'>\n": "<BODY onLoad = 'ck_frames();'>\n";
	if ($get_action == 'add') {		/* update ticket */
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

		if ($_GET['ticket_id'] == '' OR $_GET['ticket_id'] <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$_GET[ticket_id]' LIMIT 1"))
			print "<FONT CLASS='warn'>Invalid Ticket ID: '$_GET[ticket_id]'</FONT>";
		elseif ($_POST['frm_description'] == '')
			print '<FONT CLASS="warn">Please enter Description.</FONT><BR />';
		else {
			$_POST['frm_description'] = strip_html($_POST['frm_description']); 				//fix formatting, custom tags etc.

			$post_frm_meridiem_asof = empty($_POST['frm_meridiem_asof'])? "" : $_POST['frm_meridiem_asof'] ;
			$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$post_frm_meridiem_asof";
															//  8/15/10	
     		$query 	= "SELECT * FROM  `$GLOBALS[mysql_prefix]patient` WHERE 
     			`description` =	'" . addslashes($_POST['frm_description']) . "' AND
     			`ticket_id` =	'{$_GET['ticket_id']}' AND
     			`user` =		'{$_SESSION['user_id']}' AND
     			`action_type` =	'{$GLOBALS['ACTION_COMMENT']}' AND 
     			`name` = 		'" . addslashes($_POST['frm_name']) . "' AND 
     			`updated` =		'{$frm_asof}' LIMIT 1";
     			
			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_affected_rows()==0) {		// not a duplicate - 8/15/10	

				if ((array_key_exists ('frm_fullname', $_POST))) {		// 6/22/11
					$ins_data = "
						`fullname`	= " . 			quote_smart(addslashes(trim($_POST['frm_fullname']))) . ",
						`dob`	= " .				quote_smart(addslashes(trim($_POST['frm_dob']))) . ",
						`gender`	= " .			quote_smart(addslashes(trim($_POST['frm_gender_val']))) . ",
						`insurance_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_ins_id']))) . ",
						`facility_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_facility_id']))) . ",						
						`facility_contact` = " .	quote_smart(addslashes(trim($_POST['frm_fac_cont']))) . ",";
					}
				else { $ins_data = "";}
					
	     		$query 	= "INSERT INTO `$GLOBALS[mysql_prefix]patient` SET 
	     			{$ins_data}
	     			`description`= " .  quote_smart(addslashes(trim($_POST['frm_description']))) . ",
	     			`ticket_id`= " .  	quote_smart(addslashes(trim($_GET['ticket_id']))) .	",
	     			`date`= " .  		quote_smart(addslashes(trim($now))) . ",
	     			`user`= " .  		quote_smart(addslashes(trim($_SESSION['user_id']))) . ",
	     			`action_type` = " . quote_smart(addslashes(trim($GLOBALS['ACTION_COMMENT']))) .	",
	     			`name` = " .  		quote_smart(addslashes(trim($_POST['frm_name']))) . ", 
	     			`updated` = " .  	quote_smart(addslashes(trim($frm_asof)));

				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				do_log($GLOBALS['LOG_PATIENT_ADD'], $_GET['ticket_id'], 0, mysql_insert_id());		// 3/18/10
//				($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) 		// generic log table writer - 5/31/08, 10/6/09
	
				$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'  LIMIT 1") or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
				}

			add_header($_GET['ticket_id']);
			$id = $_GET['ticket_id'];
			print "<BR><BR><FONT CLASS='header'>" . get_text("Patient") ." record has been added</FONT><BR /><BR />";
			print "<A HREF='main.php'><U>Continue</U></A>";
			$addrs = notify_user($_GET['ticket_id'],$GLOBALS['NOTIFY_PERSON_CHG']);		// returns array or FALSE
			if ($addrs) {
				$theTo = implode("|", array_unique($addrs));
				$theText = "TICKET - PATIENT: ";
				mail_it ($theTo, "", $theText, $id, 1 );
				}				// end if ($addrs)
			if($_SESSION['internet']) {
				require_once('./forms/ticket_view_screen.php');
				} else {
				require_once('./forms/ticket_view_screen_NM.php');
				}
			print "</BODY>";				// 10/19/08			
			print "</HTML>";				// 10/19/08
			}		// end else ...
// ________________________________________________________		
			exit();
			
		}			// end if ($get_action == 'add')
		
	else if ($get_action == 'delete') {
		if (array_key_exists('confirm', ($_GET))) {
			do_log($GLOBALS['LOG_PATIENT_DELETE'], $_GET['ticket_id'], 0, $_GET['id']);		// 3/18/10
//			($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) {		// generic log table writer - 5/31/08, 10/6/09
			$query = "DELETE FROM `$GLOBALS[mysql_prefix]patient` WHERE `id`='$_GET[id]' LIMIT 1";
			$result = mysql_query($query) or do_error('',$query,mysql_error(), basename( __FILE__), __LINE__);
			print '<FONT CLASS="header">' . get_text("Patient") . ' record deleted</FONT><BR /><BR />';
			$col_width= max(320, intval($_SESSION['scr_width']* 0.45));
			add_header($_GET['ticket_id']);				// 8/16/08
			show_ticket($_GET['ticket_id']);
			}
		else {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE `id`='$_GET[id]' LIMIT 1";
			$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			print "<FONT CLASS='header'>Really delete " . get_text("Patient") . " record ' " .shorten($row['description'], 24) . "' ?</FONT><BR /><BR />";
			print "<FORM METHOD='post' ACTION='patient.php?action=delete&id=$_GET[id]&ticket_id=$_GET[ticket_id]&confirm=1'><INPUT TYPE='Submit' VALUE='Yes'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			print "<INPUT TYPE='button' VALUE='Cancel'  onClick='do_cancel();'></FORM>";
			}
		}
	else if ($get_action == 'update') {		//update patient record and show ticket

		$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;

		$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
//		$query = "UPDATE `$GLOBALS[mysql_prefix]patient` SET `description`='$_POST[frm_description]' , `name`='$_POST[frm_name]', `updated` = '$frm_asof' WHERE id='$_GET[id]' LIMIT 1";
		$now = mysql_format_date(now());

		if ((array_key_exists ('frm_fullname', $_POST))) {		// 6/22/11
			$ins_data = "
				`fullname`	= " . 			quote_smart(addslashes(trim($_POST['frm_fullname']))) . ",
				`dob`	= " .				quote_smart(addslashes(trim($_POST['frm_dob']))) . ",
				`gender`	= " .			quote_smart(addslashes(trim($_POST['frm_gender_val']))) . ",
				`insurance_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_ins_id']))) . ",";

			}
		else { $ins_data = "";}
	    $query 	= "UPDATE `$GLOBALS[mysql_prefix]patient` SET 
	    	{$ins_data}
	    	`description`= " .  quote_smart(addslashes(trim($_POST['frm_description']))) . ",
	    	`ticket_id`= " .  	quote_smart(addslashes(trim($_GET['ticket_id']))) .	",
	    	`date`= " .  		quote_smart(addslashes(trim($frm_asof))) . ",
	    	`user`= " .  		quote_smart(addslashes(trim($_SESSION['user_id']))) . ",
	    	`action_type` = " . quote_smart(addslashes(trim($GLOBALS['ACTION_COMMENT']))) .	",
	    	`name` = " .  		quote_smart(addslashes(trim($_POST['frm_name']))) . ",
			`facility_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_facility_id']))) . ",
			`facility_contact` = " .	quote_smart(addslashes(trim($_POST['frm_fac_cont']))) . ",			
	    	`updated` = " .  	quote_smart(addslashes(trim($now))) . "
	    	WHERE id= " . 		quote_smart($_GET['id']) . " LIMIT 1";

		$result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);

		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'";
		$result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);

		$result = mysql_query("SELECT ticket_id FROM `$GLOBALS[mysql_prefix]patient` WHERE id='$_GET[id]'") or do_error('patient.php::update patient record','mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		
		print '<br><br><FONT CLASS="header">' . get_text("Patient") . ' record updated</FONT><BR /><BR />';
		add_header($_GET['ticket_id']);				// 8/16/08
		show_ticket($row['ticket_id']);
		}
	else if ($get_action == 'edit') {		//get and show action to update
		$query = "SELECT *, UNIX_TIMESTAMP(date) AS `date` FROM `$GLOBALS[mysql_prefix]patient` WHERE id='$_GET[id]' LIMIT 1";	// 8/11/08
		$result = mysql_query($query) or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
//		dump($row);
//		dump(stripslashes($row['description']));
?>
		<FONT CLASS="header">Edit <?php print get_text("Patient");?> Record</FONT><BR /><BR />
		<FORM METHOD='post' NAME='patientEd' onSubmit='return validate(document.patientEd);' ACTION="patient.php?id=<?php print $_GET['id'];?>&ticket_id=<?php print $_GET['ticket_id'];?>&action=update"><TABLE BORDER="0">

		<TR CLASS='even' ><TD CLASS='td_label'><B><?php print get_text("Patient ID");?>: <font color='red' size='-1'>*</font></B></TD><TD><INPUT TYPE="text" NAME="frm_name" value="<?php print $row['name'];?>" size="32"></TD></TR>
<?php
	$checks = array("", "", "", "", "");		// gender checks
	$row_gender = ($row['gender'] != 0) ? $row['gender'] : 4;	//	7/12/13
	$checks[intval($row_gender)] = "CHECKED";	//	7/12/13

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` ORDER BY `sort_order` ASC, `ins_value` ASC";
	$result = mysql_query($query);
	if(@mysql_num_rows($result) > 0) {
		$ins_sel_str = "<SELECT CLASS='sit' name='frm_insurance' onChange = 'this.form.frm_ins_id.value = this.options[this.selectedIndex].value;'>\n";
		
		while ($row_ins = stripslashes_deep(mysql_fetch_assoc($result))) {
			$sel = (($row['insurance_id'] != 0) && (intval($row['insurance_id']) == intval($row_ins['id'])))? "SELECTED": "";	//	7/12/13
			$ins_sel_str .= "\t\t\t<OPTION VALUE={$row_ins['id']} {$sel}>{$row_ins['ins_value']}</OPTION>\n";		
			}		// end while()
		$ins_sel_str .= "</SELECT>\n";
?>
		<TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $fullname;?>: &nbsp;&nbsp;</TD>
			<TD><INPUT TYPE = 'text' NAME = 'frm_fullname' VALUE='<?php print $row['fullname'];?>' SIZE = '64' /></TD></TR>
		<TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><?php echo $dateofbirth;?>: &nbsp;&nbsp;</TD>
			<TD><INPUT TYPE = 'text' NAME = 'frm_dob' VALUE='<?php print $row['dob'];?>' SIZE = '24' /></TD></TR>
		<TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $gender;?>:  <font color='red' size='-1'>*</font></B>&nbsp;&nbsp;</TD>
			<TD>			
				&nbsp;&nbsp;
				M&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[1];?> />
				&nbsp;&nbsp;F&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 2 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[2];?> />
				&nbsp;&nbsp;T&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 3 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[3];?>/>
				&nbsp;&nbsp;U&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 4 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[4];?>/>
			</TD></TR>
		<TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><?php echo $insurance;?>: <font color='red' size='-1'>*</font></B> &nbsp;&nbsp;</TD>
			<TD><?php echo $ins_sel_str;?></TD></TR>
		<TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $facilitycontact;?>: &nbsp;&nbsp;</TD>
			<TD><INPUT TYPE = 'text' NAME = 'frm_fac_cont' VALUE='<?php print $row['facility_contact'];?>' SIZE = '64' /></TD>
		</TR>
<?php
		}		// end 	if($num_rows>0) 
?>		
		<TR CLASS='odd'  VALIGN='top'><TD><B>Description:</B> <font color='red' size='-1'>*</font></TD><TD><TEXTAREA ROWS="8" COLS="45" NAME="frm_description" WRAP="virtual"><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
			<TD ALIGN='right' CLASS="td_label">Signal: </TD><TD>

				<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>
<?php
			print "\n<TR CLASS='even'><TD CLASS='td_label'>As of:</TD><TD>";
			print  generate_date_dropdown("asof",$row['date'], TRUE);
			print "&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.patientEd);'></TD></TR>\n";

?>

		<TR CLASS='odd' ><TD></TD><TD ALIGN='center'><INPUT TYPE="button" VALUE="Cancel" onClick="do_cancel();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="Reset" VALUE="Reset"  onClick = "do_lock(this.form); this.form.reset();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="Submit" VALUE="Submit"></TD></TR>
		</TABLE><BR />
			<INPUT TYPE = 'hidden' NAME = 'frm_gender_val' VALUE = <?php print $row['gender'];?> />
			<INPUT TYPE = 'hidden' NAME = 'frm_ins_id' VALUE = <?php print $row['insurance_id'];?> />
		</FORM>
		
<?php
		}
	else {
		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 		
		$regions_inuse = get_regions_inuse($user_level);	//	5/4/11
		$group = get_regions_inuse_numbers($user_level);	//	5/4/11		

		$al_groups = $_SESSION['user_groups'];
			
		if(array_key_exists('viewed_groups', $_SESSION)) {	//	5/4/11
			$curr_viewed= explode(",",$_SESSION['viewed_groups']);
			} else {
			$curr_viewed = $al_groups;
			}

		$curr_names="";	//	5/4/11
		$z=0;	//	5/4/11
		foreach($curr_viewed as $grp_id) {	//	5/4/11
			$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
			$curr_names .= get_groupname($grp_id);
			$curr_names .= $counter;
			$z++;
			}	

		$regs_string = "<FONT SIZE='-1'>Showing " . get_text("Regions") . ":&nbsp;&nbsp;" . $curr_names . "</FONT>";	//	5/4/11	
		
		if(!isset($curr_viewed)) {	
			if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
				$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
				} else {
				$x=0;	//	6/10/11
				$where2 = "WHERE (";	//	6/10/11
				foreach($al_groups as $grp) {	//	6/10/11
					$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
					$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
					$where2 .= $where3;
					$x++;
					}
				$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11					
				}
			} else {
			if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
				$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
				} else {				
				$x=0;	//	6/10/11
				$where2 = "WHERE (";	//	6/10/11
				foreach($curr_viewed as $grp) {	//	6/10/11
					$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
					$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
					$where2 .= $where3;
					$x++;
					}
				$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11						
				}
			}
		
		$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )		
			$where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";		
		$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$pulldown = '<option value = 0 selected>Select</option>\n'; 	// 3/18/10
			while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
				$pulldown .= "<option value=\"{$row_fc['id']}\">" . $row_fc['name'] . "</option>\n";
				}		
?>
		<TABLE BORDER="0">
		<TR CLASS='header'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'>Add <?php print get_text("Patient");?> Record</FONT></TD></TR>	<!-- 5/4/11 -->
		<TR CLASS='spacer'><TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>				<!-- 5/4/11 -->			
		<FORM METHOD="post" NAME='patientAdd' onSubmit='return validate(document.patientAdd);'  ACTION="patient.php?ticket_id=<?php print $_GET['ticket_id'];?>&action=add">
		<TR CLASS='even'><TD class='td_label'><B><?php print get_text("Patient ID");?>:</B> <font color='red' size='-1'>*</font></TD><TD><INPUT TYPE="text" NAME="frm_name" value="" size="32"></TD></TR>
<?php

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` ORDER BY `sort_order` ASC, `ins_value` ASC";
	$result = mysql_query($query);
	if(@mysql_num_rows($result) > 0) {
		$ins_sel_str = "<SELECT name='frm_insurance' onChange = 'this.form.frm_ins_id.value = this.options[this.selectedIndex].value;'>\n";
		$ins_sel_str .= "\t\t\t<OPTION VALUE=0 SELECTED >Select</OPTION>\n";		// 7/27/11
		
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$ins_sel_str .= "\t\t\t<OPTION VALUE={$row['id']}>{$row['ins_value']}</OPTION>\n";		
			}		// end while()
		$ins_sel_str .= "</SELECT>\n";
?>
		<TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $fullname;?>: &nbsp;&nbsp;</TD>
			<TD CLASS='td_data'><INPUT TYPE = 'text' NAME = 'frm_fullname' VALUE='' SIZE = '64' /></TD></TR>
		<TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><?php echo $dateofbirth;?>: &nbsp;&nbsp;</TD>
			<TD CLASS='td_data'><INPUT TYPE = 'text' NAME = 'frm_dob' VALUE='' SIZE = '24' /></TD></TR>
		<TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $gender;?>:  <font color='red' size='-1'>*</font></B>&nbsp;&nbsp;</TD>
			<TD class='td_label'>			
				&nbsp;&nbsp;M&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;' />
				&nbsp;&nbsp;F&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 2 onClick = 'this.form.frm_gender_val.value=this.value;' />
				&nbsp;&nbsp;T&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 3 onClick = 'this.form.frm_gender_val.value=this.value;' />
				&nbsp;&nbsp;U&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 4 onClick = 'this.form.frm_gender_val.value=this.value;' />
			</TD></TR>
		<TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><?php echo $insurance;?>: <font color='red' size='-1'>*</font></B> &nbsp;&nbsp;</TD>
			<TD CLASS='td_data'><?php echo $ins_sel_str;?></TD></TR>
			
		<TR CLASS='odd'>
			<TD CLASS="td_label">Facility:</TD><TD COLSPAN='2' class='td_label'>
				<SELECT NAME="frm_facility_id"  tabindex=11 onChange="this.options[selectedIndex].value.trim())"><?php print $pulldown; ?></SELECT>
			</TD>
		</TR>
		<TR CLASS='odd'>
			<TD CLASS="td_label"><?php echo $facilitycontact;?>:&nbsp;&nbsp;</TD>		
			<TD CLASS='td_data'>
				<INPUT TYPE = 'text' NAME = 'frm_fac_cont' VALUE='' SIZE = '32' />
			</TD>
		</TR>
<?php
		}		// end 	if($num_rows>0) 
?>		

		<TR VALIGN = 'TOP' CLASS='even'>		<!-- 11/15/10 -->
			<TD ALIGN='right' CLASS="td_label">Signal: </TD><TD>

				<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>

		<TR CLASS='even' ><TD class='td_label'><B>Description: </B><font color='red' size='-1'>*</font></TD><TD><TEXTAREA ROWS="6" COLS="62" NAME="frm_description" WRAP="virtual"></TEXTAREA></TD></TR> <!-- 10/19/08 -->

		<TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label">As of: &nbsp;&nbsp;</TD><TD><?php print generate_date_dropdown('asof',0,TRUE);?>&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.patientAdd);'></TD></TR>

		<TR CLASS='odd'><TD></TD><TD><INPUT TYPE="button" VALUE="Cancel"  onClick="do_cancel();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="Reset" VALUE="Reset" onClick = "do_reset(this.form);">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="Next" onclick = "validate(this.form);"></TD></TR>
		</TABLE><BR />
			<INPUT TYPE = 'hidden' NAME = 'frm_ins_id' VALUE = 0 />
			<INPUT TYPE = 'hidden' NAME = 'frm_gender_val' VALUE = 0 />
		</FORM>
<?php
		}
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>">
</FORM>
</HTML>
