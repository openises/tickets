<?php 
/*
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
12/1/10 $patient get_text added, FIP change
3/15/11 changed stylesheet.php to stylesheet.php
5/26/11 added intrusion detection
7/27/11	fix multiple selects per KB email
4/8/2014 - insurance made non-mandatory
*/
error_reporting(E_ALL);			// 10/1/08

@session_start();
session_write_close();
require_once('incs/functions.inc.php');	
do_login(basename(__FILE__));
if ((isset($_REQUEST['ticket_id'])) && 	(strlen(trim($_REQUEST['ticket_id']))>6)) {	shut_down();}			// 5/26/11
//require_once($_SESSION['fmp']);		// 8/27/10
//$istest = true;
if($istest) {
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
$get_action = (array_key_exists ( "action", $_REQUEST ))? $_REQUEST['action'] : "new" ;
	switch ($get_action) {
		case "add":		// db insert
			$w=720; $h=520;
			break;
		case "delete":
			$w=400; $h=240;
			break;
		case "update":
			$w=400; $h=240;
			break;
		case "list":
			$w=550; $h=300;
			break;
		case "new":	
		case "edit":
			$w=660; $h=520;
			break;

		}	

//dump($get_action);
$patient = 			get_text("Patient"); 		// 12/1/10
$fullname =	 		get_text("Full name");
$dateofbirth =	 	get_text("Date of birth");
$gender =	 		get_text("Gender");
$insurance =	 	get_text("Insurance");
$facilitycontact = 	get_text("Facility contact");

$responder_details = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id` ASC";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$responder_details[$row['id']] = $row['handle'];
	}
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - <?php print $patient; ?> Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="8/16/08">
<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
	var viewportwidth;
	var viewportheight;
	var outerwidth;
	var outerheight;
	
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
		outerwidth = viewportwidth * .95;
		outerheight = viewportheight * .95;
		listHeight = viewportheight * .25;
		colwidth = outerwidth * .42;
		colheight = outerheight * .95;
		listHeight = viewportheight * .5;
		listwidth = colwidth;
		if($('outer')) {$('outer').style.width = outerwidth + "px";}
		if($('outer')) {$('outer').style.height = outerheight + "px";}
		}

	function ck_window() {		//
		window.resizeTo(<?php echo "{$w}, {$h}";?>);
		if (window.opener == null) { alert ("<?php print __LINE__;?>")}
		}		// end function ck_window()
	
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

	function validate(theForm) {		// 4/8/2014
		var errmsg="";
		if (theForm.frm_name.value == "")						{errmsg+= "\t<?php echo get_text("Patient ID");?> is required\n";}
		if (theForm.frm_gender_val.value==0) 					{errmsg+= "\t<?php echo $gender;?> required\n";}
//		if (theForm.frm_ins_id.value==0) 						{errmsg+= "\t<?php echo $insurance;?> selection required\n";}
//		if (theForm.frm_description.value == "")				{errmsg+= "\tDescription is required\n";}
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
			theForm.submit();
			}
		}				// end function validate(theForm)

	function do_asof(theForm, theBool) {							// 8/10/08
		theForm.frm_year_asof.disabled = theBool;
		theForm.frm_month_asof.disabled = theBool;
		theForm.frm_day_asof.disabled = theBool;
		theForm.frm_hour_asof.disabled = theBool;
		theForm.frm_minute_asof.disabled = theBool;
		try {
			theForm.frm_meridiem_asof.disabled = theBool;		// 
			}
		catch (e) {
//			continue;
			}			
		}

	function do_unlock(theForm) {									// 8/10/08
		do_asof(theForm, false)
		document.getElementById("lock").style.visibility = "hidden";		
		}
		
	function do_lock(theForm) {										// 8/10/08
		do_asof(theForm, true)
		document.getElementById("lock").style.visibility = "visible";
		}
		
	function do_cancel () {		
		window.close();
		}				// end function do_cancel ()
	
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

	</SCRIPT>
	</HEAD>
<?php 
	if ($get_action == 'add') {		/* update ticket */
?>
		<BODY onLoad = 'ck_window();'>
<?php		
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

		if ($_GET['ticket_id'] == '' OR $_GET['ticket_id'] <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$_GET[ticket_id]' LIMIT 1")) {
			print "<FONT CLASS='warn'>Invalid Ticket ID: '$_GET[ticket_id]'</FONT>";
			} else {
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
						`insurance_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_ins_id']))) . ",";
					} else { 
					$ins_data = "";
					}
					
	     		$query 	= "INSERT INTO `$GLOBALS[mysql_prefix]patient` SET 
	     			{$ins_data}
	     			`description`= " .  quote_smart(addslashes(trim($_POST['frm_description']))) . ",
	     			`ticket_id`= " .  	quote_smart(addslashes(trim($_GET['ticket_id']))) .	",
	     			`date`= " .  		quote_smart(addslashes(trim($now))) . ",
	     			`user`= " .  		quote_smart(addslashes(trim($_SESSION['user_id']))) . ",
	     			`action_type` = " . quote_smart(addslashes(trim($GLOBALS['ACTION_COMMENT']))) .	",
	     			`name` = " .  		quote_smart(addslashes(trim($_POST['frm_name']))) . ",
					`facility_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_facility_id']))) . ",	
					`facility_contact` = " .	quote_smart(addslashes(trim($_POST['frm_fac_cont']))) . ",
	     			`updated` = " .  	quote_smart(addslashes(trim($frm_asof)));

				$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				do_log($GLOBALS['LOG_PATIENT_ADD'], $_GET['ticket_id'], 0, mysql_insert_id());		// 3/18/10
//				($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) 		// generic log table writer - 5/31/08, 10/6/09
				$result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'  LIMIT 1") or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
				}
			print "<br /><CENTER><FONT CLASS='header text_large'>" . $patient . " record has been added</FONT><BR /><BR />";
			print "<BR /><BR /><SPAN ID='fin_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='opener.location.reload(true); opener.parent.frames[\"upper\"].show_msg(\"Action added!\"); window.close();'><SPAN STYLE='float: left;'>" . get_text('Finished') . "</SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN><BR /><BR /><BR /></CENTER>";

?>
<SCRIPT>
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
</SCRIPT>
<?php
			$id = $_GET['ticket_id'];			
			$addrs = notify_user($_GET['ticket_id'],$GLOBALS['NOTIFY_PERSON_CHG']);		// returns array or FALSE
			if ($addrs) {
				$theTo = implode("|", array_unique($addrs));
				$theText = "TICKET - PATIENT: ";
				mail_it ($theTo, "", $theText, $id, 1 );
				}				// end if ($addrs)
			}
?>
		</HTML>
<?php
		exit();
		} else if ($get_action == 'delete') {
// ________________________________________________________				
?>
		<BODY onLoad = 'ck_window();'>
<?php
		if (array_key_exists('confirm', ($_GET))) {
			do_log($GLOBALS['LOG_PATIENT_DELETE'], $_GET['ticket_id'], 0, $_GET['id']);		// 3/18/10
//			($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) {		// generic log table writer - 5/31/08, 10/6/09
			$query = "DELETE FROM `$GLOBALS[mysql_prefix]patient` WHERE `id`='$_GET[id]' LIMIT 1";
			$result = mysql_query($query) or do_error('',$query,mysql_error(), basename( __FILE__), __LINE__);
?>
<script>
			setTimeout("document.next_Form.submit()",1500);
</script>
			<FONT CLASS='header'><?php print $patient;?> record deleted</FONT><BR /><BR />
			<SPAN ID='fin_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='opener.location.reload(true); window.close();'><SPAN STYLE='float: left;'><?php print get_text('Finished');?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0 /></SPAN></CENTER>
<?php
			} else {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE `id`='$_GET[id]' LIMIT 1";
			$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			print "<FONT CLASS='header text_large'>Really delete " . $patient . " record '" . shorten($row['description'], 24) . "' ? </FONT><BR /><BR />";
?>
			<FORM METHOD='post' NAME='delfrm' ACTION='patient_w.php?action=delete&id=<?php print $_GET['id'];?>&ticket_id=<?php print $_GET['ticket_id'];?>&confirm=1'>
			<SPAN ID='sub_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.delfrm.submit();'><SPAN STYLE='float: left;'><?php print get_text('Yes');?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0 /></SPAN>
			<SPAN ID='can_but' CLASS='plain text' STYLE='width: 100px; float: none; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text('Cancel');?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0 /></SPAN>
			</FORM>
<?php
			}
		} else if ($get_action == 'update') {		//update patient record and show ticket
?>
		<BODY onLoad = 'ck_window();'>
<?php
		$frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;

		$frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
		$now = mysql_format_date(now());

		if ((array_key_exists ('frm_fullname', $_POST))) {		// 6/22/11
			$ins_data = "
				`fullname`	= " . 			quote_smart(addslashes(trim($_POST['frm_fullname']))) . ",
				`dob`	= " .				quote_smart(addslashes(trim($_POST['frm_dob']))) . ",
				`gender`	= " .			quote_smart(addslashes(trim($_POST['frm_gender_val']))) . ",
				`insurance_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_ins_id']))) . ",
				`facility_contact` = " .	quote_smart(addslashes(trim($_POST['frm_fac_cont']))) . ",";
			} else { 
			$ins_data = "";
			}
	    $query 	= "UPDATE `$GLOBALS[mysql_prefix]patient` SET 
	    	{$ins_data}
	    	`description`= " .  quote_smart(addslashes(trim($_POST['frm_description']))) . ",
	    	`ticket_id`= " .  	quote_smart(addslashes(trim($_GET['ticket_id']))) .	",
	    	`date`= " .  		quote_smart(addslashes(trim($frm_asof))) . ",
	    	`user`= " .  		quote_smart(addslashes(trim($_SESSION['user_id']))) . ",
	    	`action_type` = " . quote_smart(addslashes(trim($GLOBALS['ACTION_COMMENT']))) .	",
	    	`name` = " .  		quote_smart(addslashes(trim($_POST['frm_name']))) . ", 
	    	`updated` = " .  	quote_smart(addslashes(trim($now))) . "
	    	WHERE id= " . 		quote_smart($_GET['id']) . " LIMIT 1";

		$result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'";
		$result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$result = mysql_query("SELECT ticket_id FROM `$GLOBALS[mysql_prefix]patient` WHERE id='$_GET[id]'") or do_error('patient_w.php::update patient record','mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		
		if($_POST['assigns'] != "0") {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id` = " . $_GET['id'];
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			if(mysql_num_rows($result) > 0) {
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id`= " . $_GET['id'];
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				}			
			
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));							// 6/4/2013
			$query  = "INSERT INTO `$GLOBALS[mysql_prefix]patient_x` (
					`patient_id`, `assign_id`, `_by`, `_on`, `_from`
					) VALUES (" .
					quote_smart(trim($_GET['id'])) . "," .
					quote_smart(trim($_POST['assigns'])) . "," .
					quote_smart(trim($_SESSION['user_id'])) . "," .
					quote_smart(trim($now)) . "," .
					quote_smart(trim($_SERVER['REMOTE_ADDR'])) . ");";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			} else {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id` = " . $_GET['id'];
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			if(mysql_num_rows($result) > 0) {
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id`= " . $_GET['id'];
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
				}					
			}
?>
<script>
			setTimeout("document.next_Form.submit()",1500);
</script>
		<BR />
		<BR />
		<FONT CLASS='header'><?php print $patient;?> record updated</FONT>
		<BR />
		<BR />
<?php
		} else if ($get_action == 'edit') {		//get and show action to update
?>
		<BODY onLoad = 'ck_window(); init();'>
<?php
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient_x` WHERE `patient_id` = " . $_GET['id'];
			$result = mysql_query($query) or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
			if(mysql_num_rows($result) > 0) {
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				$assigned_to = $row['assign_id'];
				} else {
				$assigned_to = 0;				
				}
		
			$user_level = is_super() ? 9999 : $_SESSION['user_id']; 		
			$regions_inuse = get_regions_inuse($user_level);	//	5/4/11
			$group = get_regions_inuse_numbers($user_level);	//	5/4/11		
			$al_groups = $_SESSION['user_groups'];
			if(array_key_exists('viewed_groups', $_SESSION)) {	//	5/4/11
				$curr_viewed= explode(",",$_SESSION['viewed_groups']);
				} else {
				$curr_viewed = $al_groups;
				}
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
			$query = "SELECT *, UNIX_TIMESTAMP(date) AS `date` FROM `$GLOBALS[mysql_prefix]patient` WHERE id='$_GET[id]' LIMIT 1";	// 8/11/08
			$result = mysql_query($query) or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			if ( can_edit()) {										// 8/27/10
				$hdr_str = "Edit";
				$dis = "";
				} else {
				$hdr_str = "Showing";
				$dis = "DISABLED";
				}
			$heading = $hdr_str . " " . $patient . "Record";
?>
			<DIV ID='outer'>
				<DIV id='button_bar' class='but_container'>
					<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'><?php echo $heading;?>
					<SPAN ID='can_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_cancel();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
					<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_lock(document.patientEd); document.patientEd.reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
					<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.patientEd.submit();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				</DIV>
				<FORM METHOD='post' NAME='patientEd' onSubmit='return validate(document.patientEd);' ACTION="<?php echo basename(__FILE__);?>?id=<?php print $_GET['id'];?>&ticket_id=<?php print $_GET['ticket_id'];?>&action=update">
				<TABLE BORDER="0" STYLE='margin-left: 20px; position: relative; top: 70px;'>
					<TR CLASS='even' >
						<TD CLASS='td_label text'><?php print get_text("Patient ID");?>: <font color='red' size='-1'>*</font></TD>
						<TD CLASS='td_data text'><INPUT TYPE="text" NAME="frm_name" value="<?php print $row['name'];?>" size="32" <?php print $dis;?>></TD>
					</TR>
<?php
					$checks = array("", "", "", "", "");		// gender checks
					$checks[intval($row['gender'])] = "CHECKED";

					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` ORDER BY `sort_order` ASC, `ins_value` ASC";
					$result = mysql_query($query);
					if(@mysql_num_rows($result) > 0) {
						$ins_sel_str = "<SELECT CLASS='sit' name='frm_insurance' onChange = 'this.form.frm_ins_id.value = this.options[this.selectedIndex].value;'>\n";
						
						while ($row_ins = stripslashes_deep(mysql_fetch_assoc($result))) {
							$sel = (intval($row['insurance_id']) == intval($row_ins['id']))? "SELECTED": "";
							$ins_sel_str .= "\t\t\t<OPTION VALUE={$row_ins['id']} {$sel}>{$row_ins['ins_value']}</OPTION>\n";		
							}		// end while()
						$ins_sel_str .= "</SELECT>\n";
?>
						<TR CLASS='odd' VALIGN='bottom'>
							<TD CLASS="td_label text"><?php echo $fullname;?>: &nbsp;&nbsp;</TD>
							<TD CLASS='td_data text'>
								<INPUT TYPE = 'text' NAME = 'frm_fullname' VALUE='<?php print $row['fullname'];?>' SIZE = '64' <?php print $dis;?> />
							</TD>
						</TR>
						<TR CLASS='even' VALIGN='bottom'>
							<TD CLASS="td_label text"><?php echo $dateofbirth;?>: &nbsp;&nbsp;</TD>
							<TD CLASS='td_data text'>
								<INPUT TYPE = 'text' NAME = 'frm_dob' VALUE='<?php print $row['dob'];?>' SIZE = '24' />
							</TD>
						</TR>
						<TR CLASS='odd' VALIGN='bottom'>
							<TD CLASS="td_label text">
								<?php echo $gender;?>:  
<?php
								if(get_variable('locale') != 1) {
?>
									<font color='red' size='-1'>*</font>
<?php
									}
?>
								</B>&nbsp;&nbsp;
							</TD>
							<TD CLASS='td_data text'>			
								&nbsp;&nbsp;
								M&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[1];?> <?php print $dis;?> />
								&nbsp;&nbsp;F&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 2 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[2];?> <?php print $dis;?>/>
								&nbsp;&nbsp;T&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 3 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[3];?> <?php print $dis;?>/>
								&nbsp;&nbsp;U&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 4 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[4];?> <?php print $dis;?>/>
							</TD>
						</TR>
<?php
						if(get_variable('locale') != 1) {
?>
							<TR CLASS='even' VALIGN='bottom'>
								<TD CLASS="td_label text"><?php echo $insurance;?>: <font color='red' size='-1'>*</font></B> &nbsp;&nbsp;</TD>
								<TD CLASS='td_data text'><?php echo $ins_sel_str;?></TD>
							</TR>
<?php
							}
						$query_fac = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities`
							LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )		
							$where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";		
						$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						$pulldown = '<option value = 0 selected>Select</option>\n'; 	// 3/18/10
							while ($row_fac = mysql_fetch_array($result_fac, MYSQL_ASSOC)) {
								$sel = ($row_fac['fac_id'] == $row['facility_id']) ? "SELECTED" : "";
								$pulldown .= "<option value=\"{$row_fac['fac_id']}\" {$sel}>" . $row_fac['name'] . "</option>\n";
								}	
?>
						<TR CLASS='odd'>
							<TD CLASS="td_label text">Facility:</TD>
							<TD COLSPAN='2' class='td_data text'>
								<SELECT NAME="frm_facility_id"  tabindex=11 onChange="this.options[selectedIndex].value.trim())"><?php print $pulldown; ?></SELECT>
							</TD>
						</TR>
						<TR CLASS='odd' VALIGN='bottom'>
							<TD CLASS="td_label text"><?php echo $facilitycontact;?>: &nbsp;&nbsp;</TD>
							<TD CLASS='td_data text'><INPUT TYPE = 'text' NAME = 'frm_fac_cont' VALUE='<?php print $row['facility_contact'];?>' SIZE = '64' <?php print $dis;?>/></TD>
						</TR>
<?php
						}		// end 	if($num_rows>0) 
?>		
					<TR CLASS='even'  VALIGN='top'><TD class='td_label text'>Description: </TD><TD><TEXTAREA ROWS="8" COLS="64" NAME="frm_description" WRAP="virtual" <?php print $dis;?>><?php print $row['description'];?></TEXTAREA></TD></TR>
					<TR VALIGN = 'TOP' CLASS='even'>		<!-- 11/15/10 -->
						<TD  CLASS="td_label text">Signal: </TD>
						<TD CLASS='td_data text'>

							<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;' <?php print $dis;?>>	<!--  11/17/10 -->
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
							$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
							$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
								print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
								}
?>
							</SELECT>
						</TD>
					</TR>
					<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
						<TD CLASS="td_label text">Add to Assign: </TD>
						<TD CLASS='td_data text'>

							<SELECT NAME='assigns'>
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . $row['ticket_id'] . " AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_ass = stripslashes_deep(mysql_fetch_assoc($result))) {
									$sel = ($row_ass['id'] == $assigned_to) ? "SELECTED" : "";
									print "\t<OPTION VALUE='{$row_ass['id']}' {$sel}>{$responder_details[$row_ass['responder_id']]}&nbsp;|&nbsp;{$row_ass['as_of']}</OPTION>\n";		// pipe separator
									}
?>
							</SELECT>
						</TD>
					</TR>
					<TR CLASS='odd'>
						<TD CLASS='td_label text'>As of:</TD>
						<TD CLASS='td_data text'><?php print generate_date_dropdown("asof",$row['date'], TRUE);?>
							&nbsp;&nbsp;&nbsp;&nbsp;
							<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.patientEd);' />
						</TD>
					</TR>
					<TR CLASS='even'>
						<TD COLSPAN=99>&nbsp;</TD>
					</TR>
				<INPUT TYPE = 'hidden' NAME = 'frm_gender_val' VALUE = <?php print $row['gender'];?> />
				<INPUT TYPE = 'hidden' NAME = 'frm_ins_id' VALUE = <?php print $row['insurance_id'];?> />
				</FORM>
				</TABLE>
			</DIV>
<?php
		} else if ($get_action == 'list') {		// given a ticket id list its patient records for selection
?>
<?php
		$query 	= "SELECT *, `p`.`id` AS `pat_id`     		
		FROM  `$GLOBALS[mysql_prefix]patient` `p`
		LEFT JOIN `$GLOBALS[mysql_prefix]insurance` `i` 
		ON (`p`.`insurance_id` = `i`.`id`)
		WHERE `ticket_id` = {$_GET['ticket_id']}
		ORDER BY `name` ASC, `fullname` ASC";

		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			
		if (mysql_num_rows($result)==1) {

			$row = stripslashes_deep(mysql_fetch_assoc($result));		// proceed directly to edit
?>
<SCRIPT>
			document.list_form.id.value = <?php echo $row['id'];?>
			document.list_form.submit();
</SCRIPT>
<?php			
			}				// end if (mysql_num_rows($result)==1)
		$i = 0;
?>
		<BODY onLoad = 'ck_window()'>
		<DIV>
		<TABLE BORDER=0 STYLE = 'margin-top:50px;'>
			<TR CLASS = 'even'>
				<TD COLSPAN=99 ALIGN='center'><H3><?php print $patient;?> records - click line to edit</H3></TD>
			</TR>
<?php
			while($row =stripslashes_deep( mysql_fetch_array($result))){
?>
				<TR CLASS='"<?php print $evenodd[($i+1)%2];?>"' VALIGN='baseline' onClick = \"to_edit(<?php print $row['pat_id'];?>)\">
					<TD CLASS='td_data text'><?php print $row['name'];?></TD>
					<TD CLASS='td_data text'><?php print shorten($row['fullname'], 24);?></TD>
					<TD CLASS='td_data text'><?php print $row['ins_value'];?></TD>
					<TD CLASS='td_data text'><?php print shorten($row['description'], 24);?></TD>
				</TR>
<?php
				$i++;
				}
?>
			<TR CLASS='even'>
				<TD COLSPAN=2>&nbsp;</TD>
			</TR>
		</TABLE>
		<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
		<SPAN ID='add_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.list_form.action.value='new'; document.list_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/plus_small.png' BORDER=0></SPAN>
		</CENTER>
		<script>	
			function to_edit(id) {						
				document.list_form.id.value=id;	// 
				document.list_form.submit();
				}
		</script>

		<FORM NAME = "list_form" METHOD = "get" ACTION = "<?php echo basename(__FILE__);?>">
		<INPUT TYPE="hidden" NAME = "ticket_id" VALUE = "<?php echo $_GET['ticket_id'];?>">
		<INPUT TYPE="hidden" NAME = "id" VALUE = "">
		<INPUT TYPE="hidden" NAME = "action" VALUE = "edit">
		</FORM>

<?php
		} else {				// $get_action - NOTA - default
?>
		<BODY onLoad = 'ck_window(); init();'>
<?php
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
					$pulldown .= "<option value=\"{$row_fc['id']}\">" . shorten($row_fc['name'], 20) . "</option>\n";
					}
			$heading = "Add " . $patient . " Record";
?>		
			<DIV ID='outer'>
				<DIV id='button_bar' class='but_container'>
					<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'><?php echo $heading;?>
					<SPAN ID='can_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_cancel();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
					<SPAN ID='reset_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_asof(document.patientAdd, false) reset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
					<SPAN ID='sub_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate(document.patientAdd);'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				</DIV>
				<FORM METHOD="post" NAME='patientAdd' onSubmit='return validate(document.patientAdd);'  ACTION="<?php echo basename(__FILE__);?>?ticket_id=<?php print $_GET['ticket_id'];?>&action=add">
				<TABLE BORDER="0" STYLE='margin-left: 20px; position: relative; top: 70px;'>
					<TR CLASS='even' >
						<TD CLASS='td_label text text'><?php print get_text("Patient ID");?>: <font color='red' size='-1'>*</font></TD>
						<TD CLASS='td_data text'><INPUT TYPE="text" NAME="frm_name" value="" size="32"></TD>
					</TR>
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` ORDER BY `sort_order` ASC, `ins_value` ASC";
					$result = mysql_query($query);
					if(@mysql_num_rows($result) > 0) {
						$ins_sel_str = "<SELECT name='frm_insurance' onChange = 'this.form.frm_ins_id.value = this.options[this.selectedIndex].value;'>\n";
						$ins_sel_str .= "\t\t\t<OPTION VALUE=0 SELECTED >Select</OPTION>\n";		// 7/27/11		
						while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
							$ins_sel_str .= "\t\t\t<OPTION VALUE={$row['id']}>{$row['ins_value']}</OPTION>\n";		
							}		// end while()
						$ins_sel_str .= "</SELECT>";
?>
						<TR CLASS='odd' VALIGN='bottom'>
							<TD CLASS="td_label text text"><?php echo $fullname;?>: &nbsp;&nbsp;</TD>
							<TD CLASS='td_data text'><INPUT TYPE = 'text' NAME = 'frm_fullname' VALUE='' SIZE = '64' /></TD>
						</TR>
						<TR CLASS='even' VALIGN='bottom'>
							<TD CLASS="td_label text text"><?php echo $dateofbirth;?>: &nbsp;&nbsp;</TD>
							<TD CLASS='td_data text'><INPUT TYPE = 'text' NAME = 'frm_dob' VALUE='' SIZE = '24' /></TD>
						</TR>
						<TR CLASS='odd' VALIGN='bottom'>
							<TD CLASS="td_label text text"><?php echo $gender;?>:  
<?php
								if(get_variable('locale') != 1) {
?>		
									<font color='red' size='-1'>*</font>
<?php
									}
?>
								</B>&nbsp;&nbsp;
							</TD>
							<TD CLASS='td_data text'>			
								&nbsp;&nbsp;
								M&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;' />
								&nbsp;&nbsp;F&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 2 onClick = 'this.form.frm_gender_val.value=this.value;' />
								&nbsp;&nbsp;T&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 3 onClick = 'this.form.frm_gender_val.value=this.value;' />
								&nbsp;&nbsp;U&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 4 onClick = 'this.form.frm_gender_val.value=this.value;' />
							</TD>
						</TR>
<?php
						if(get_variable('locale') != 1) {
?>
							<TR CLASS='even' VALIGN='bottom'>
								<TD CLASS="td_label text text"><?php echo $insurance;?>: <font color='red' size='-1'>*</font></B> &nbsp;&nbsp;</TD>
								<TD CLASS='td_data text'><?php echo $ins_sel_str;?></TD>
							</TR>
<?php
							}
?>
						<TR CLASS='odd'>
							<TD CLASS="td_label text text">Facility:</TD>
							<TD COLSPAN='2' class='td_data text'>
								<SELECT NAME="frm_facility_id"  tabindex=11 onChange="this.options[selectedIndex].value.trim())"><?php print $pulldown; ?></SELECT>
							</TD>
						</TR>
						<TR CLASS='even'>
							<TD CLASS="td_label text text"><?php echo $facilitycontact;?>:&nbsp;&nbsp;</TD>
							<TD class='td_data text'>
								<INPUT TYPE = 'text' NAME = 'frm_fac_cont' VALUE='' SIZE = '64' />
							</TD>
						</TR>
<?php
						}		// end 	if($num_rows>0) 
?>		
					<TR CLASS='even' >
						<TD CLASS="td_label text text">Description:</TD>
						<TD CLASS='td_data text'>
							<TEXTAREA ROWS="6" COLS="62" NAME="frm_description" WRAP="virtual"></TEXTAREA>
						</TD>
					</TR>

					<TR VALIGN = 'TOP' CLASS='even'>		<!-- 11/15/10 -->
						<TD ALIGN='right' CLASS="td_label text text"></TD>
						<TD CLASS='td_data text'>
							<SPAN CLASS="td_label text">Signal: </SPAN>
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
						</TD>
					</TR>
					<TR CLASS='odd' VALIGN='bottom'>
						<TD CLASS="td_label text text">As of: &nbsp;&nbsp;</TD>
						<TD CLASS='td_data text'><?php print generate_date_dropdown('asof',0,TRUE);?>&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.patientAdd);'></TD>
					</TR>
					<TR CLASS='even'>
						<TD COLSPAN=2>&nbsp;</TD>
					</TR>
				</TABLE>
				<INPUT TYPE = 'hidden' NAME = 'frm_ins_id' VALUE = 0 />
				<INPUT TYPE = 'hidden' NAME = 'frm_gender_val' VALUE = 0 />
				</FORM>
			</DIV>
<?php
		}
?>
<SCRIPT>
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
outerwidth = viewportwidth * .95;
outerheight = viewportheight * .95;
listHeight = viewportheight * .25;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .5;
listwidth = colwidth;
if($('outer')) {$('outer').style.width = outerwidth + "px";
if($('outer')) {$('outer').style.height = outerheight + "px";

function init() {
	do_unlock(document.forms[0])
	var now = new Date();
	if (now.getYear()>2000) {
		document.forms[0].frm_year_asof.value= now.getYear() - 2000;
		}
	else {
		if (now.getYear()>100) {
			document.forms[0].frm_year_asof.value=now.getYear() - 100;
			}
		else {
			document.forms[0].frm_year_asof.value=now.getYear();
			}
		}
	document.forms[0].frm_year_asof.value=now.getFullYear();
	document.forms[0].frm_month_asof.value=now.getMonth()+1;
	document.forms[0].frm_day_asof.value=now.getDate();
	document.forms[0].frm_hour_asof.value=now.getHours();
	document.forms[0].frm_minute_asof.value=now.getMinutes() ;
	if (document.forms[0].frm_hour_asof.value<10) {document.forms[0].frm_hour_asof.value = "0" + document.forms[0].frm_hour_asof.value;}
	if (document.forms[0].frm_minute_asof.value<10) {document.forms[0].frm_minute_asof.value = "0" + document.forms[0].frm_minute_asof.value;}
	do_lock(document.forms[0]);
	}
</SCRIPT>
</BODY>
<FORM NAME='next_Form' METHOD='get' ACTION='<?php echo basename(__FILE__); ?>'>
<INPUT TYPE='hidden' NAME='action' VALUE='list' />
<INPUT TYPE='hidden' NAME='ticket_id' VALUE='<?php print $_GET['ticket_id'];?>' />
</FORM>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>">
</FORM>
</HTML>
