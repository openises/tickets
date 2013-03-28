<?php
error_reporting(E_ALL);
$units_side_bar_height = .5;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$iw_width= "300px";			// map infowindow with
$side_bar_height = 0.5;		// height of units sidebar as decimal fraction - default is 0.9 (90%)
$zoom_tight = FALSE;		// replace with a decimal number to over-ride the standard default zoom setting
/*
7/16/10 Initial Release for no internet operation - created from units.php
8/16/10 phone and location fields added to all forms
8/24/10 access to tracks removed
8/25/10 light top-frame button
9/11/10 status update added
10/28/10 Added include and function calls for addon modules. 
3/15/11 added reference to stylesheet.php for revisable day night colors.
3/19/11 revised unit  index to 6 chars length
4/5/11 get_new_colors added
6/10/11 changes for multi region capability
8/1/11 state length increased to 4 chars
2/8/12 Fixed error on single region operation - editing a unit removes region 1 region allocation.
3/24/12 fixed to accommodate OGTS in validate()
12/1/2012 - unix time revisions
4/19/13 - Revision to fix blank unit list
*/

session_start();

require_once($_SESSION['fip']);		//7/28/10
if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
	require_once('./incs/modules.inc.php');
	}
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);		// 8/27/10
$facility = get_text("Facility");
$key_field_size = 30;						// 7/23/09
$st_size = (get_variable("locale") ==0)?  2: 4;		

//$tolerance = 5 * 60;		// nr. seconds report time may differ from UTC
extract($_GET);
extract($_POST);
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}
$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}

unset($result);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Units Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
	<SCRIPT  SRC='./js/misc_function.js' type='text/javascript'></SCRIPT>  <!-- 4/14/10 -->
	<SCRIPT >

	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();												// 11/2/08
	parent.upper.light_butt('resp');										// light the button - 8/25/10

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08

	function set_regions_control() {
		var reg_control = "<?php print get_variable('regions_control');?>";
		var regions_showing = "<?php print get_num_groups();?>";
		if(regions_showing) {
			if (reg_control == 0) {
				$('top_reg_box').style.display = 'none';
				$('regions_outer').style.display = 'block';
				} else {
				$('top_reg_box').style.display = 'block';
				$('regions_outer').style.display = 'none';			
				}
			}
		}

	function $() {															// 12/20/08
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
		
	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}
	function do_lo_hover (the_id) {
		CngClass(the_id, 'lo_hover');
		return true;
		}
	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
		return true;
		}
	function do_lo_plain (the_id) {
		CngClass(the_id, 'lo_plain');
		return true;
		}

	function get_new_colors() {
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	String.prototype.trim = function () {									// added 6/10/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()

	function open_tick_window (id) {										// 4/29/10
		var url = "single.php?ticket_id="+ id;
		var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		tickWindow.focus();
		}

	function to_str(instr) {			// 0-based conversion - 2/13/09
		function ord( string ) {
		    return (string+'').charCodeAt(0);
			}

		function chr( ascii ) {
		    return String.fromCharCode(ascii);
			}
		function to_char(val) {
			return(chr(ord("A")+val));
			}

		var lop = (instr % 26);													// low-order portion, a number
		var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
		return hop+to_char(lop);
		}

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && $) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && $) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}

	var starting = false;

	function do_mail_win() {			// 6/13/09
		if(starting) {return;}					
		starting=true;	
		newwindow_um=window.open('do_unit_mail.php', 'E_mail_Window',  'titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
		if (isNull(newwindow_um)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_um.focus();
		starting = false;
		}

	function do_mail_in_win(id) {			// individual email 8/17/09
		if(starting) {return;}					
		starting=true;	
		var url = "do_indiv_mail.php?the_id=" + id;	
		newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height=300,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
		if (isNull(newwindow_in)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_in.focus();
		starting = false;
		}


	function to_routes(id) {
		document.routes_Form.ticket_id.value=id;			// 10/16/08, 10/25/08
		document.routes_Form.submit();
		}

	function to_fac_routes(id) {
		document.fac_routes_Form.fac_id.value=id;			// 10/6/09
		document.fac_routes_Form.submit();
		}

	function whatBrows() {									//Displays the generic browser type
		window.alert("Browser is : " + type);
		}

	function ShowLayer(id, action){							// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
		if (type=="IE") 				eval("document.all." + id + ".style.display='" + action + "'");  	// id is the span/layer, action is either hidden or visible
		if (type=="NN") 				eval("document." + id + ".display='" + action + "'");
		if (type=="MO" || type=="OP") 	eval("$('" + id + "').style.display='" + action + "'");
		}

	function hideit (elid) {
		ShowLayer(elid, "none");
		}

	function showit (elid) {
		ShowLayer(elid, "block");
		}
		
	function any_track(theForm) {					// returns boolean 8/8/09, 5/11/11, 3/24/12 
//		return ((theForm.frm_aprs.value.trim()==1)||(theForm.frm_instam.value.trim()==1)||(theForm.frm_locatea.value.trim()==1)||(theForm.frm_gtrack.value.trim()==1)||(theForm.frm_glat.value.trim()==1)||(theForm.frm_t_tracker.value.trim()==1));
		return (theForm.frm_track_disp.selectedIndex > 0);
		}

	function validate(theForm) {						// Responder form contents validation	8/11/09
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				var str = "Please confirm removing '" + theForm.frm_name.value + "'";
				if(confirm(str)) 	{
					theForm.submit();					// 8/11/09
					return true;}
				else 				{return false;}
				}
			}
		theForm.frm_mobile.value = (theForm.frm_mob_disp.checked)? 1:0;
		theForm.frm_multi.value =  (theForm.frm_multi_disp.checked)? 1:0;		// 4/27/09

		theForm.frm_direcs.value = (theForm.frm_direcs_disp.checked)? 1:0;
		var errmsg="";
								// 2/24/09, 3/24/10
		if (theForm.frm_name.value.trim()=="")													{errmsg+="<?php print get_text("Units");?> NAME is required.\n";}
		if (theForm.frm_handle.value.trim()=="")												{errmsg+="<?php print get_text("Units");?> HANDLE is required.\n";}
		if (theForm.frm_icon_str.value.trim()=="")												{errmsg+="<?php print get_text("Units");?> ICON is required.\n";}
		if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)					{errmsg+="<?php print get_text("Units");?> TYPE selection is required.\n";}	// 1/1/09
//		if (!(any_track(theForm)))																{errmsg+="<?php print get_text("Units");?> TRACKING selection is required.\n";}	// 1/1/09
		
		if (any_track(theForm)){
			if (theForm.frm_callsign.value.trim()=="")											{errmsg+="License information is required with Tracking.\n";}
			}
		else {
			if (!(theForm.frm_callsign.value.trim()==""))										{errmsg+="License information used ONLY with Tracking.\n";}
			}
		if (theForm.frm_un_status_id.options[theForm.frm_un_status_id.selectedIndex].value==0)	{errmsg+="<?php print get_text("Units");?> STATUS is required.\n";}
		if (theForm.frm_descr.value.trim()=="")													{errmsg+="<?php print get_text("Units");?> DESCRIPTION is required.\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {																	// good to go!
//			top.upper.calls_start();											// 1/21/09
			theForm.submit();													// 7/21/09
//			return true;
			}
		}				// end function va lidate(theForm)

	function add_res () {		// turns on add responder form
		showit('res_add_form');
		hideit('tbl_responders');
		hideIcons();			// hides responder icons
		}

	function hideIcons() {
		}				// end function hideicons()

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
		}				// end function all ticks()

	function do_disp(){												// show incidents for dispatch - added 6/7/08
		$('incidents').style.display='block';
		$('view_unit').style.display='none';
		}

	function do_dispfac(){												// show incidents for dispatch - added 6/7/08
		$('facilities').style.display='block';
		$('view_unit').style.display='none';
		}

	function do_add_reset(the_form) {								// 1/22/09
		the_form.reset();
		}

	var track_captions = ["", "Callsign", "Device key", "Userid ", "Userid ", "Badge", "Device", "Userid"];
	function do_tracking(theForm, theVal) {							// 7/10/09, 7/24/09 added specific code to switch off unselected
		theForm.frm_aprs.value=theForm.frm_instam.value=theForm.frm_locatea.value=theForm.frm_gtrack.value= theForm.frm_glat.value= theForm.frm_ogts.value = theForm.frm_t_tracker.value = 0;	
		switch(parseInt(theVal)) {
			case <?php print $GLOBALS['TRACK_NONE'];?>:		 break;
			case <?php print $GLOBALS['TRACK_APRS'];?>:		 theForm.frm_aprs.value=1;	 break;
			case <?php print $GLOBALS['TRACK_INSTAM'];?>:	 theForm.frm_instam.value=1;	 break;
			case <?php print $GLOBALS['TRACK_LOCATEA'];?>:	 theForm.frm_locatea.value=1; break;
			case <?php print $GLOBALS['TRACK_GTRACK'];?>:	 theForm.frm_gtrack.value=1;  break;
			case <?php print $GLOBALS['TRACK_GLAT'];?>:		 theForm.frm_glat.value=1;	 break;
			case <?php print $GLOBALS['TRACK_T_TRACKER'];?>:	theForm.frm_t_tracker.value=1;	break;
			case <?php print $GLOBALS['TRACK_OGTS'];?>:		 theForm.frm_ogts.value=1;	 break;
			default:  alert("error <?php print __LINE__;?>");
			}		// end switch()
		}				// end function do tracking()	
	</SCRIPT>


<?php

function list_responders($addon = '', $start) {
//	global {$_SESSION['fip']}, $fmp, {$_SESSION['editfile']}, {$_SESSION['addfile']}, {$_SESSION['unitsfile']}, {$_SESSION['facilitiesfile']}, {$_SESSION['routesfile']},	{$_SESSION['facroutesfile']}; 
	global $iw_width, $u_types, $tolerance;

	$assigns = array();					// 08/8/3
	$tickets = array();					// ticket id's
	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`,
		`$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` 
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`
		WHERE ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";

	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);
	$calls = array();									// 6/17/08
	$calls_nr = array();
?>

<SCRIPT >

var color=0;
	var colors = new Array ('odd', 'even');

	function hideDiv(div_area, hide_cont, show_cont) {	//	3/15/11
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "resp_list_sh") {
			var controlarea = "resp_list";
			}
		if (div_area == "facs_list_sh") {
			var controlarea = "facs_list";
			}
		if (div_area == "incs_list_sh") {
			var controlarea = "incs_list";
			}
		if (div_area == "region_boxes") {
			var controlarea = "region_boxes";
			}			
		var divarea = div_area 
		var hide_cont = hide_cont 
		var show_cont = show_cont 
		if($(divarea)) {
			$(divarea).style.display = 'none';
			$(hide_cont).style.display = 'none';
			$(show_cont).style.display = '';
			} 
		var params = "f_n=" +controlarea+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);			
		} 

	function showDiv(div_area, hide_cont, show_cont) {	//	3/15/11
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "resp_list_sh") {
			var controlarea = "resp_list";
			}
		if (div_area == "facs_list_sh") {
			var controlarea = "facs_list";
			}
		if (div_area == "incs_list_sh") {
			var controlarea = "incs_list";
			}
		if (div_area == "region_boxes") {
			var controlarea = "region_boxes";
			}				
		var divarea = div_area
		var hide_cont = hide_cont 
		var show_cont = show_cont 
		if($(divarea)) {
			$(divarea).style.display = '';
			$(hide_cont).style.display = '';
			$(show_cont).style.display = 'none';
			}
		var params = "f_n=" +controlarea+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);					
		} 	

	function checkArray(form, arrayName)	{	//	5/3/11
		var retval = new Array();
		for(var i=0; i < form.elements.length; i++) {
			var el = form.elements[i];
			if(el.type == "checkbox" && el.name == arrayName && el.checked) {
				retval.push(el.value);
			}
		}
	return retval;
	}		
		
	function checkForm(form)	{	//	6/10/11
		var errmsg="";
		var itemsChecked = checkArray(form, "frm_group[]");
		if(itemsChecked.length > 0) {
			var params = "f_n=viewed_groups&v_n=" +itemsChecked+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, fvg_handleResult, params);				
//			form.submit();
		} else {
			errmsg+= "\tYou cannot Hide all the regions\n";
			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
			}
		}
	}
	
	function fvg_handleResult(req) {	// 6/10/11	The persist callback function for viewed groups.
		document.region_form.submit();
		}
		
	function form_validate(theForm) {	//	5/3/11
//		alert("Validating");
		checkForm(theForm);
		}				// end function validate(theForm)			

	function do_sidebar (sidebar, id, the_class, unit_id, index) {
		var unit_id = unit_id;
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' >";		
		side_bar_html += "<TD CLASS='" + the_class + "' onClick = 'myclick(" + unit_id + ");'>" + index + "</TD>" + sidebar +"</TD></TR>\n";	// 1/5/09, 3/4/09, 10/29/09 removed period
		}

	function myclick(unit_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=unit_id;
		document.view_form.submit();
		}
		
	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

<?php

$dzf = get_variable('def_zoom_fixed');
print "\tvar map_is_fixed = ";

print (((my_is_int($dzf)) && ($dzf==2)) || ((my_is_int($dzf)) && ($dzf==3)))? "true;\n":"false;\n";

?>
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' WIDTH = <?php print max(320, intval($_SESSION['scr_width']* 0.6));?> >";
	side_bar_html += "<TR class='even'>	<TD></TD><TD ALIGN='left'><B><?php print get_text("Units");?></B></TD><TD ALIGN='left'><B>Handle</B></TD><TD ALIGN='left'><B>Dispatch</B></TD><TD ALIGN='left'><B>Status</B></TD><TD ALIGN='left'><B>M</B></TD><TD ALIGN='left'><B>As of</B></TD></TR>";
	var which;
	var i = <?php print $start; ?>;					// sidebar/icon index
	var points = false;								// none

<?php

	function can_do_dispatch($the_row) {
		if (intval($the_row['multi'])==1) return TRUE;
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = {$the_row['unit_id']}";	// all dispatches this unit
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row_temp = stripslashes_deep(mysql_fetch_array($result_temp))) {		// check any open runs this unit
			if (!(is_date($row_temp['clear']))) { 			// if  clear is empty, then NOT dispatch-able
				unset ($result_temp, $row_temp); 
				return FALSE;
				}
			}		// end while ($row_temp ...)
		unset ($result_temp, $row_temp); 
		return TRUE;					// none found, can dispatch
		}		// end function can_do_dispatch()

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	6/10/11
	$result = mysql_query($query);	// 4/13/11
	$al_groups = array();
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	//	6/10/11
		$al_groups[] = $row['group'];
		}	

	if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}

	if(!isset($curr_viewed)) {	
		$x=0;	//	6/10/11
		$where2 = "WHERE (";	//	6/10/11
		foreach($al_groups as $grp) {	//	6/10/11
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
	} else {
		$x=0;	//	6/10/11
		$where2 = "WHERE (";	//	6/10/11
		foreach($curr_viewed as $grp) {	//	6/10/11
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
	}
	$where2 .= "AND `a`.`type` = 2";	//	6/10/11		
		
	$query = "SELECT *, 
		`updated` AS `updated`,
		`t`.`id` AS `type_id`,
		`r`.`id` AS `unit_id`,
		`r`.`name` AS `name`,
		`s`.`description` AS `stat_descr`,
		`r`.`description` AS `unit_descr`, 
		(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
		WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id  AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) AS `nr_assigned` 
		FROM `$GLOBALS[mysql_prefix]responder` `r` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = a.resource_id )			
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 		
		{$where2}  GROUP BY unit_id ORDER BY `nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC ";											// 2/1/10, 3/15/10, 6/10/11
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_units = mysql_affected_rows();	
	$aprs = FALSE;
	$instam = FALSE;
	$locatea = FALSE;				// 7/23/09
	$gtrack = FALSE;				// 7/23/09
	$glat = FALSE;				// 7/23/09
	$i=0;				// counter
// =============================================================================
	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");

	$utc = gmdate ("U");
//									 ==========  major while() for RESPONDER ==========
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		

		$resp_gps = get_allocates(2, $row['unit_id']);	//	6/10/11
		$grp_names = "Groups Assigned: ";	//	6/10/11
		$y=0;	//	6/10/11
		foreach($resp_gps as $value) {	//	6/10/11
			$counter = (count($resp_gps) > ($y+1)) ? ", " : "";
			$grp_names .= get_groupname($value);
			$grp_names .= $counter;
			$y++;
			}
		$grp_names .= " / ";
	
		$aprs = $instam = $locatea = $gtrack = $glat = $t_tracker = FALSE;			// all trackers off, 5/11/11 added internal Tickets Tracker
		$temp = explode("/", $row['name'] );
		$index = substr($temp[count($temp) -1], -6, strlen($temp[count($temp) -1]));	// 3/19/11

		$the_on_click = (my_is_float($row['lat']))? " onClick = myclick({$row['unit_id']}); " : " onClick = myclick_nm({$row['unit_id']}); ";
		$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 2/1/10
		$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];		// 2/1/10
		$index = $row['icon_str'] ;											// 4/28/11
		$track_type = get_remote_type ($row);								// 7/6/11
		$do_dispatch = can_do_dispatch($row);								// 11/17/09
		$got_point = FALSE;
		print "\n\t\tvar i=$i;\n";
		$tofac = (is_guest())? "" : "&nbsp;&nbsp;<A HREF='{units_nm.php?func=responder&view=true&dispfac=true&id=" . $row['unit_id'] . "'><U>To Facility</U></A>&nbsp;&nbsp;";	// 10/6/09
		$todisp = ((is_guest()) || (!(can_do_dispatch($row))))? "" : "&nbsp;&nbsp;<A HREF='" . basename(__FILE__) . "?func=responder&view=true&disp=true&id=" . $row['unit_id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;&nbsp;";	// 08/8/02, 9/19/09
		$toedit = (is_guest())? "" :"&nbsp;&nbsp;<A HREF='" . basename(__FILE__) . "?func=responder&edit=true&id=" . $row['unit_id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;" ;	// 10/8/08

		$temp = $row['un_status_id'] ;		// 2/24/09
		$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09

		$the_bull = "";											// define the bullet
		$update_error = strtotime('now - 6 hours');							// set the time for silent setting
		if ($track_type> 0) {				// get most recent position data
			$do_legend = TRUE;	
			}

// name, handle
		$name =  addslashes(shorten($row['name'], 40));		//	10/8/09
		$handle =  addslashes($row['handle']);
		$sidebar_line = "<TD TITLE = '{$handle}' {$the_on_click}><U><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>{$handle}</SPAN></U></TD>";			// 10/8/09
		$sidebar_line .= "<TD TITLE = '" . addslashes($row['name']) . "' {$the_on_click}><U>{$name}</TD>";			// 10/8/09

// assignments 3/16/09, 3/15/10

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			WHERE `responder_id` = '{$row['unit_id']}' AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";
		$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_assign = (mysql_affected_rows()==0)?  FALSE : stripslashes_deep(mysql_fetch_assoc($result_as)) ;
		switch($row_assign['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 				$severityclass='severity_normal'; break;
			}

		switch (mysql_num_rows($result_as)) {		// 10/4/10
			case 0:
				$the_disp_stat="";
				break;			
			case 1:
				$the_disp_stat =  get_disp_status ($row_assign) . "&nbsp;";
				break;
			default:							// multiples
			    $the_disp_stat = "<SPAN CLASS='disp_stat'>&nbsp;" . mysql_affected_rows() . "&nbsp;</SPAN>&nbsp;";
			    break;
			}						// end switch()

		$tick_ct = (mysql_affected_rows()>1)? "(" . mysql_num_rows($result_as) . ") ": "";
		$ass_td =  (mysql_affected_rows()>0)? 
			"<TD CLASS='$severityclass' TITLE = '{$row_assign['scope']}' STYLE = 'white-space:nowrap;' >{$the_disp_stat}" . shorten($row_assign['scope'], 24) . "</TD>":
			"<TD>na</TD>";
		$sidebar_line .= ($row_assign)? $ass_td : "<TD>na</TD>";

// status, mobility  - 9/11/10
		$sidebar_line .= "<TD TITLE = '" . addslashes ($the_status) . "'> " . get_status_sel($row['unit_id'], $row['un_status_id'], "u") . "</TD>";
		$sidebar_line .= "<TD TITLE ='{$row['callsign']}'>&nbsp;{$GLOBALS['TRACK_2L'][$track_type]}&nbsp;{$the_bull}</TD>";					// 4/14/10

// as of
		$strike = $strike_end = "";
		$the_time = $row['updated'];				// 7/6/11
		$the_class = "";
		if (($row['mobile']==1) && (abs($utc - $the_time) > $GLOBALS['TOLERANCE'])) {				// identify  non-current values
			$strike = "<STRIKE>";
			$strike_end = "</STRIKE>";
		} 

		$sidebar_line .= "<TD CLASS='$the_class'> $strike" . format_sb_date_2($the_time) . "$strike_end</TD>";	// 6/17/08

?>
		var unit_id = "<?php print $index;?>";	//	10/8/09
	
		var the_class = "td_label";		// 4/3/09
		var handle = "<?php print substr(($row['handle']),1);?>";
		var longhandle = "<?php print $row['handle'];?>";
<?php		
		print "\tdo_sidebar(\" {$sidebar_line} \" , i, {$row['unit_id']}, {$row['unit_id']}, unit_id);\n";	// sidebar only - no map, 11/11/09, 04/19/13
	$i++;				// zero-based
	}				// end  ==========  while() for RESPONDER ==========


	$source_legend = (isset($do_legend))? "<TD CLASS='emph' ALIGN='left'>Source time</TD>": "<TD></TD>";		// if any remote data/time 3/24/09
?>
	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=5>&nbsp;</TD><?php print $source_legend;?></TR>";
<?php

	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side bar div
	$("num_units").innerHTML = <?php print $num_units;?>;			

</SCRIPT>

<?php
	}				// end function list_responders() ===========================================================

	function finished ($caption) {
		print "</HEAD><BODY>";
		require_once('./incs/links.inc.php');
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
		print "</FORM></BODY></HTML>";
		}

	function do_calls($id = 0) {				// generates js callsigns array
		$print = "\n<SCRIPT >\n";
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
		}		// end function do calls()
	
	$_postmap_clear = 	(array_key_exists ('frm_clr_pos',$_POST ))? 	$_POST['frm_clr_pos']: "";	// 11/19/09
	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? 		$_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? 			$_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? 			$_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? 				$_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? 				$_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? 			$_GET['view']: "";
	$_dodisp = 			(array_key_exists ('disp',$_GET ))? 			$_GET['disp']: "";
	$_dodispfac = 		(array_key_exists ('dispfac',$_GET ))? 			$_GET['dispfac']: "";	//10/6/09

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Responder - checkbox - 8/12/09
		$query = "DELETE FROM $GLOBALS[mysql_prefix]responder WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>" . get_text("Units") . "<I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? "0.999999" : quote_smart(trim($_POST['frm_lat'])) ; // 2/24/09
			$the_lng = empty($_POST['frm_lng'])? "0.999999" : quote_smart(trim($_POST['frm_lng'])) ;
			$curr_groups = $_POST['frm_exist_groups']; 	//	4/14/11
			$groups = isset($_POST['frm_group']) ? ", " . implode(',', $_POST['frm_group']) . "," : $_POST['frm_exist_groups'];	//	3/28/12 - fixes error when accessed from view ticket screen..	
			$resp_id = $_POST['frm_id'];
			$resp_stat = $_POST['frm_un_status_id'];
			$by = $_SESSION['user_id'];
//			if (($_POST['frm_clr_pos'])=='on') {$the_lat = $the_lng = "NULL";}				// 11/15/09
			if ($_postmap_clear=='on') {$the_lat = $the_lng = "NULL";}				// 11/19/09
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
				`name`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) . ",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) . ",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
				`handle`= " . 		quote_smart(trim($_POST['frm_handle'])) . ",
				`icon_str`= " . 	quote_smart(trim($_POST['frm_icon_str'])) . ",
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",
				`capab`= " . 		quote_smart(trim($_POST['frm_capab'])) . ",
				`un_status_id`= " . quote_smart(trim($_POST['frm_un_status_id'])) . ",
				`callsign`= " . 	quote_smart(trim($_POST['frm_callsign'])) . ",
				`mobile`= " . 		quote_smart(trim($_POST['frm_mobile'])) . ",
				`multi`= " . 		quote_smart(trim($_POST['frm_multi'])) . ",
				`aprs`= " . 		quote_smart(trim($_POST['frm_aprs'])) . ",
				`instam`= " . 		quote_smart(trim($_POST['frm_instam'])) . ",
				`locatea`= " . 		quote_smart(trim($_POST['frm_locatea'])) . ",
				`gtrack`= " . 		quote_smart(trim($_POST['frm_gtrack'])) . ",
				`glat`= " . 		quote_smart(trim($_POST['frm_glat'])) . ",
				`t_tracker`= " . 		quote_smart(trim($_POST['frm_t_tracker'])) . ",				
				`ogts`= " . 		quote_smart(trim($_POST['frm_ogts'])) . ",
				`direcs`= " . 		quote_smart(trim($_POST['frm_direcs'])) . ",
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`contact_name`= " . quote_smart(trim($_POST['frm_contact_name'])) . ",
				`contact_via`= " . 	quote_smart(trim($_POST['frm_contact_via'])) . ",
				`smsg_id`= " . 		quote_smart(trim($_POST['frm_smsg_id'])) . ",				
				`type`= " . 		quote_smart(trim($_POST['frm_type'])) . ",
				`user_id`= " . 		quote_smart(trim($_SESSION['user_id'])) . ",
				`updated`= " . 		quote_smart(trim($now)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_UNIT_STATUS'], 0, $_POST['frm_id'], $_POST['frm_un_status_id']);}	// 6/2/08
			$list = $_POST['frm_exist_groups']; 	//	4/14/11
			$ex_grps = explode(',', $list); 	//	4/14/11 
			
			if($curr_groups != $groups) { 	//	4/14/11
				foreach($_POST['frm_group'] as $posted_grp) { 	//	4/14/11
					if(!in_array($posted_grp, $ex_grps)) {
						$query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
								($posted_grp, 2, '$now', $resp_stat, $resp_id, 'Allocated to Group' , $by)";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				foreach($ex_grps as $existing_grps) { 	//	4/14/11
					if(!in_array($existing_grps, $_POST['frm_group'])) {
						$query  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type` = 2 AND `group` = $existing_grps AND `resource_id` = {$resp_id}";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				}			
			$mobstr = (($frm_mobile) && ($frm_aprs)||($frm_instam))? "Mobile": get_text("Units");
			$caption = "<B>" . $mobstr . " '<i>" . stripslashes_deep($_POST['frm_name']) . "</i>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$by = $_SESSION['user_id'];
		$frm_lat = (empty($_POST['frm_lat']))? '0.999999': quote_smart(trim($_POST['frm_lat']));						// 9/3/08
		$frm_lng = (empty($_POST['frm_lng']))? '0.999999': quote_smart(trim($_POST['frm_lng']));						// 9/3/08
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));							// 1/27/09
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]responder` (
			`name`, `street`, `city`, `state`, `phone`, `handle`,  `icon_str`, `description`, `capab`, `un_status_id`, `callsign`, `mobile`, `multi`, `aprs`, `instam`, `locatea`, `gtrack`, `glat`, `t_tracker`, `ogts`, `direcs`, `contact_name`, `contact_via`, `smsg_id`, `lat`, `lng`, `type`, `user_id`, `updated` )
			VALUES (" .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_street'])) . "," .
				quote_smart(trim($_POST['frm_city'])) . "," .
				quote_smart(trim($_POST['frm_state'])) . "," .
				quote_smart(trim($_POST['frm_phone'])) . "," .
				quote_smart(trim($_POST['frm_handle'])) . "," .
				quote_smart(trim($_POST['frm_icon_str'])) . "," .
				quote_smart(trim($_POST['frm_descr'])) . "," .
				quote_smart(trim($_POST['frm_capab'])) . "," .
				quote_smart(trim($_POST['frm_un_status_id'])) . "," .
				quote_smart(trim($_POST['frm_callsign'])) . "," .
				quote_smart(trim($_POST['frm_mobile'])) . "," .
				quote_smart(trim($_POST['frm_multi'])) . "," .
				quote_smart(trim($_POST['frm_aprs'])) . "," .
				quote_smart(trim($_POST['frm_instam'])) . "," .
				quote_smart(trim($_POST['frm_locatea'])) . "," .
				quote_smart(trim($_POST['frm_gtrack'])) . "," .
				quote_smart(trim($_POST['frm_glat'])) . "," .
				quote_smart(trim($_POST['frm_t_tracker'])) . "," .
				quote_smart(trim($_POST['frm_ogts'])) . "," .
				quote_smart(trim($_POST['frm_direcs'])) . "," .
				quote_smart(trim($_POST['frm_contact_name'])) . "," .
				quote_smart(trim($_POST['frm_contact_via'])) . "," .
				quote_smart(trim($_POST['frm_smsg_id'])) . "," .
				$frm_lat . "," .
				$frm_lng . "," .
				quote_smart(trim($_POST['frm_type'])) . "," .
				quote_smart(trim($_SESSION['user_id'])) . "," .
				quote_smart(trim($now)) . ");";								// 8/23/08

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();

		$status_id = $_POST['frm_un_status_id'];
		foreach ($_POST['frm_group'] as $grp_val) {	// 4/13/11
			$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
					($grp_val, 2, '$now', $status_id, $new_id, 'Allocated to Group' , $by)";
			$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);				
		}
		do_log($GLOBALS['LOG_UNIT_STATUS'], 0, mysql_insert_id(), $_POST['frm_un_status_id']);	// 6/2/08

		$mobstr = ($frm_mobile)? "Mobile " . get_text("Units"): "Station ";
		$caption = "<B>" . get_text("Units") . "<i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";

		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
// add ===========================================================================================================================
// add ===========================================================================================================================

	if ($_getadd == 'true') {
		print do_calls();		// call signs to JS array for validation
?>
		</HEAD>
		<BODY onLoad = "ck_frames();" > <!-- <?php print __LINE__;?> -->
		<A NAME='top'>		<!-- 11/11/09 -->
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></div>		
		<?php
		require_once('./incs/links.inc.php');
		?>
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER="0" ID='addform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>Add <?php print get_text("Units");?></FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>
		<FORM NAME= "res_add_Form" METHOD="POST" ACTION="<?php echo basename(__FILE__);?>?func=responder&goadd=true"> <!-- 7/9/09 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Name ">Name</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="64" SIZE="64" TYPE="text" NAME="frm_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Handle - local rules, could be callsign or badge number, generally for radio comms use">Handle</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="" />
			<SPAN STYLE = 'margin-left:30px'  CLASS="td_label"> Icon: </SPAN>&nbsp;<FONT COLOR='red' size='-1'>*</FONT>&nbsp;<INPUT TYPE = "text" NAME = "frm_icon_str" SIZE = 3 MAXLENGTH=3 VALUE="" />

<?php
if(get_num_groups() > 1) {
		if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>		
			<TR CLASS='even' VALIGN="top">	<!--  4/12/11 -->
			<TD CLASS="td_label"><A HREF="#"  TITLE="Sets Regions that Responder is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD COLSPAN='2'>
<?php
			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	4/18/11
			print get_user_group_butts(($_SESSION['user_id']));	//	4/18/11		
			} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11
?>		
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A HREF="#" TITLE="Sets Regions that Responder is allocated to - click + to expand, - to collapse"><?php print get_text("Regions");?></A>: 
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			</TD>
			<TD COLSPAN='2'>
<?php

			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	4/18/11
			print get_user_group_butts(($_SESSION['user_id']));	//	4/18/11		
?>	
			</TD></TR>
<?php
			} else {
?>
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A HREF="#" TITLE="Sets Regions that Responder is allocated to - click + to expand, - to collapse"><?php print get_text("Regions");?></A>: 
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			</TD>
			<TD COLSPAN='2'>
<?php
			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	4/18/11
			print get_user_group_butts_readonly(get_allocates(4, $_SESSION['user_id']));	//	4/18/11		
?>	
			</TD></TR>
<?php		
			}
		} else {
?>
		<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 6/10/11 -->
<?php
		}
?>
		<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>	
		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Type - Select from pulldown menu">Type</A>: <font color='red' size='-1'>*</font></TD>
			<TD ALIGN='left'><SELECT NAME='frm_type'><OPTION VALUE=0>Select one</OPTION>		<!-- 1/8/09 -->
<?php
	foreach ($u_types as $key => $value) {								// 12/27/08
		$temp = $value; 												// 2-element array
		print "\t\t\t\t<OPTION VALUE='" . $key . "'>" .$temp[0] . "</OPTION>\n";
		}
?>
			</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<A HREF="#" TITLE="<?php print get_text("Units");?> is mobile unit?">Mobile</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" />&nbsp;&nbsp;&nbsp;
			<A HREF="#" TITLE="<?php print get_text("Units");?> can be dispatched to multiple incidents?">Multiple</A>  &raquo;<INPUT TYPE="checkbox" NAME="frm_multi_disp" />&nbsp;&nbsp;&nbsp;
			<A HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked /></TD>
			</TR>

		<TR CLASS = "odd" VALIGN='top'  TITLE = 'Select one'><TD CLASS="td_label" ><A HREF="#" TITLE="Tracking Type - select from the pulldown menu - you must also fill in the callsign or tracking id which is used by the tracking provider to identify the unit - each unit should have a unique id.">Tracking</A>:&nbsp;</TD>
			<TD ALIGN='left'> <!-- 7/10/09 -->
				<SELECT NAME='frm_track_disp' onChange = "do_tracking(this.form, this.options[this.selectedIndex].value);">	<!-- 7/10/09 -->
					<OPTION VALUE='<?php print $GLOBALS['TRACK_NONE'];?>' SELECTED>None</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['TRACK_APRS'];?>'>APRS</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['TRACK_INSTAM'];?>'>Instamapper</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['TRACK_LOCATEA'];?>'>LocateA</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['TRACK_GTRACK'];?>'>Gtrack</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['TRACK_GLAT'];?>'>Google Lat</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['TRACK_T_TRACKER'];?>'>Tickets Tracker</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['TRACK_OGTS'];?>'>OpenGTS</OPTION>
					</SELECT>&nbsp;&nbsp;
			<A HREF="#" TITLE="Callsign / License key - required for all tracking types - APRS will be unit radio callsign, others will be license key given by provider">
				</A>
<SCRIPT>				
				var track_info = "APRS:   callsign\nInstamapper:   Device key\nLocateA:   Userid\nGtrack:   Userid\nLatitude:   Badge\nOpenGTS:   Device\n";
</SCRIPT>
				<INPUT TYPE = 'button' onClick = alert(track_info) value="?">&nbsp;&raquo;&nbsp;&nbsp;
				
				<INPUT ID = "track_key" SIZE="<?php print $key_field_size;?>" MAXLENGTH="<?php print $key_field_size;?>" TYPE="text" NAME="frm_callsign" VALUE="" />&nbsp;
			</TD>
			</TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Status - Select from pulldown menu">Status</A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN ='left'><SELECT NAME="frm_un_status_id" onChange = "document.res_add_Form.frm_log_it.value='1'">
				<OPTION VALUE=0 SELECTED>Select one</OPTION>
<?php			

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		if ($the_grp != $row_st['group']) {
			print ($i == 0)? "": "\t</OPTGROUP>\n";
			$the_grp = $row_st['group'];
			print "\t<OPTGROUP LABEL='$the_grp'>\n";
			}
		print "\t<OPTION VALUE=' {$row_st['id']}'  title='{$row_st['description']}'><SPAN STYLE='background-color:{$row_st['bg_color']}; color:{$row_st['text_color']};'> {$row_st['status_val']} </SPAN></OPTION>\n";
		$i++;
		}		// end while()
	print "\n</OPTGROUP>\n";
	unset($result_st);
?>
			</SELECT>
			</TD></TR>
<?php
		if(is_administrator()) {	//	6/10/11
?>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS='td_label'><A HREF="#"  TITLE="Sets Boundaries for Ring Fences and exclusion zones"><?php print get_text("Boundaries");?></A>:</TD>
			<TD COLSPAN='3'><A HREF="#"  TITLE="Sets boundary used to ring-fence the area this unit is allowed in"><?php print get_text("Ringfence");?></A>:&nbsp;
			<SELECT NAME="frm_ringfence" onChange = "this.value=JSfnTrim(this.value)">
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_u_rf` = 1 ORDER BY `line_name` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>&nbsp;
			<A HREF="#"  TITLE="Sets exclusion zone for this unit"><?php print get_text("Exclusion Zone");?></A>:&nbsp
			<SELECT NAME="frm_excl_zone" onChange = "this.value=JSfnTrim(this.value)">
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_u_ex` = 1 ORDER BY `line_name` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT></TD></TR>
<?php
			}	
?>			
		<TR CLASS='even'><TD CLASS="td_label"><A HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:</TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='odd'><TD CLASS="td_label"><A HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required">City</A></TD> <!-- 7/5/10 -->
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)"> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="<?php print $st_size;?>"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Phone Number">Phone</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="12" MAXLENGTH="48" TYPE="text" NAME="frm_phone" VALUE="" /></TD></TR> <!-- 7/5/10 -->

		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3 ><TEXTAREA NAME="frm_descr" COLS=56 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Capability - training, equipment on board etc">Capability</A>:&nbsp;</TD>	<TD COLSPAN=3 ><TEXTAREA NAME="frm_capab" COLS=56 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Contact name">Contact Name</A>:&nbsp;</TD>	<TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Contact via - for email to unit this must be a valid email address or email to SMS address">Contact Via</A>:&nbsp;</TD>	<TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="<?php get_provider_name(get_msg_variable('smsg_provider'));?> ID - This is for <?php get_provider_name(get_msg_variable('smsg_provider'));?> Integration and is the ID used by <?php get_provider_name(get_msg_variable('smsg_provider'));?> to send SMS messages"><?php get_provider_name(get_msg_variable('smsg_provider'));?> ID</A>:&nbsp;</TD>	<TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_smsg_id" VALUE="" /></TD></TR>	<!-- 10/23/12 -->
		<TR CLASS = "even"><TD COLSPAN=4 ALIGN='center'>
			<INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="reset" VALUE="Reset" onClick = "do_add_reset(this.form);">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <!-- 1/22/09 -->
			<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>"  onClick="validate(document.res_add_Form);" ></TD></TR>	<!-- 7/21/09 -->
		<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_mobile' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_multi' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_aprs' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_instam' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_locatea' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_gtrack' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_glat' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_t_tracker' VALUE=0 />	  <!-- 5/11/11 -->	
		<INPUT TYPE='hidden' NAME = 'frm_ogts' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_direcs' VALUE=1 />  <!-- note default -->
		</FORM></TABLE> <!-- end inner left -->
		</TD></TR></TABLE><!-- end outer -->

		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- 1100 -->
		</BODY>
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
		<SCRIPT>
//		if (!(document.res_add_Form.frm_lat.value=="")){
//			do_ngs();		// 1/24/09
//			}
		</SCRIPT>
		</HTML>
<?php
		if(file_exists("./incs/modules.inc.php")) {	//	10/28/10 Added for add on modules
			get_modules('res_add_Form');
			}			
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($_getedit == 'true') {
		$id = $_GET['id'];
		$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id`={$id}";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_array($result);
		$is_mobile = (($row['mobile']==1) && (!(empty($row['callsign']))));		// 1/27/09, 3/15/10

		$lat = $row['lat'];
		$lng = $row['lng'];

		$type_checks = array ("", "", "", "", "");
		$type_checks[$row['type']] = " checked";
		$mob_checked = (($row['mobile']==1))? " CHECKED" : "" ;				// 1/24/09
		$multi_checked = (($row['multi']==1))? " CHECKED" : "" ;				// 1/24/09
		$direcs_checked = (($row['direcs']==1))? " CHECKED" : "" ;			// 3/11/09
		print do_calls($id);								// generate JS calls array
		$track_type = get_remote_type ($row); 
?>
		<SCRIPT>		
		function track_reset(the_Form) {		// reset to original as-loaded values
			the_Form.frm_aprs.value = <?php echo $row['aprs'];?>;
			the_Form.frm_instam.value = <?php echo $row['instam'];?>;
			the_Form.frm_locatea.value = <?php echo $row['locatea'];?>;
			the_Form.frm_gtrack.value = <?php echo $row['gtrack'];?>;
			the_Form.frm_glat.value = <?php echo $row['glat'];?>;
			the_Form.frm_ogts.value = <?php echo $row['ogts'];?>;
			the_Form.frm_t_tracker.value = <?php echo $row['t_tracker'];?>;			
			}		// end function track reset()
			
		var track_captions = ["", "Callsign&nbsp;&raquo;", "Device key&nbsp;&raquo;", "Userid&nbsp;&raquo;", "Userid&nbsp;&raquo;", "Badge&nbsp;&raquo;", "Device&nbsp;&raquo;", "Userid&nbsp;&raquo;"];
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "ck_frames(); do_tracking(document.res_edit_Form, <?php print $track_type;?>)"; > <!-- <?php print __LINE__;?> -->
		<A NAME='top'>		<!-- 11/11/09 -->
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></div>
		<?php 
		require_once('./incs/links.inc.php');
		?>
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER=0 ID='editform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit unit '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>
		<FORM METHOD="POST" NAME= "res_edit_Form" ACTION="<?php echo basename(__FILE__);?>?func=responder&goedit=true"> <!-- 7/9/09 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Name ">Name</A>: <font color='red' size='-1'>*</font></TD>			<TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">
		<A HREF="#" TITLE="Handle - local rules, could be callsign or badge number, generally for radio comms use">Handle</A>: &nbsp;<FONT COLOR='red' size='-1'>*</FONT>&nbsp;</TD>
		<TD COLSPAN=3><INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="<?php print $row['handle'] ;?>" />
		<SPAN STYLE = 'margin-left:30px'  CLASS="td_label"> Icon: </SPAN>&nbsp;<FONT COLOR='red' size='-1'>*</FONT>&nbsp;<INPUT TYPE = 'text' NAME = 'frm_icon_str' SIZE = 3 MAXLENGTH=3 VALUE='<?php print $row['icon_str'] ;?>'>
		</TD></TR>
<?php
		if(get_num_groups()) {
			if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>			
			<TR CLASS='even' VALIGN='top'>;
			<TD CLASS='td_label'><A HREF="#" TITLE="Click + to expand control"><?php print get_text('Regions');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php			
			$alloc_groups = implode(',', get_allocates(2, $id));	//	4/18/11
			print get_sub_group_butts(($_SESSION['user_id']), 2, $id) ;	//	4/18/11		
			print "</TD></TR>";		// 6/10/11
			
			} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11	
?>
			<TR CLASS='even' VALIGN='top'>;
			<TD CLASS="td_label"><A HREF="#" TITLE="Click + to expand control"><?php print get_text('Regions');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php
			$alloc_groups = implode(',', get_allocates(2, $id));	//	4/18/11
			print get_sub_group_butts(($_SESSION['user_id']), 2, $id) ;	//	4/18/11	
			print "</TD></TR>";		// 6/10/11		

			} else {
?>
			<TR CLASS='even' VALIGN='top'>;
			<TD CLASS='td_label'><?php print get_text('Regions');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php
			$alloc_groups = implode(',', get_allocates(3, $id));	//	6/10/11	
			print get_sub_group_butts_readonly(($_SESSION['user_id']), 2, $id) ;	//	4/
			print "</TD></TR>";		// 6/10/11			
			}
		} else {
?>
		<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 6/10/11 -->
<?php
		}
?>
		<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>
		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Type - Select from pulldown menu">Type</A>: <font color='red' size='-1'>*</font></TD>
		<TD ALIGN='left'><FONT SIZE='-2'>
			<SELECT NAME='frm_type'>
<?php
	foreach ($u_types as $key => $value) {								// 1/9/09
		$temp = $value; 												// 2-element array
		$sel = ($row['type']==$key)? " SELECTED": "";					// 9/11/09
		print "\t\t\t\t<OPTION VALUE='{$key}'{$sel}>{$temp[0]}</OPTION>\n";
		}
?>
				</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<A HREF="#" TITLE="<?php print get_text("Units");?> is mobile unit?">Mobile</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" <?php print $mob_checked; ?> />&nbsp;&nbsp;&nbsp;
				<A HREF="#" TITLE="<?php print get_text("Units");?> can be dispatched to multiple incidents?">Multiple</A>  &raquo;<INPUT TYPE="checkbox" NAME="frm_multi_disp" <?php print $multi_checked; ?> />&nbsp;&nbsp;&nbsp;
				<A HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" <?php print $direcs_checked; ?> /></TD>
		</TR>
		<TR CLASS = "odd" VALIGN='top'><TD CLASS="td_label"><A HREF="#" TITLE="Tracking Type - select from the pulldown menu - you must also fill in the callsign or tracking id which is used by the tracking provider to identify the unit - each unit should have a unique id.">
			Tracking</A>:&nbsp;</TD>
			<TD ALIGN='left'>

				<SELECT NAME='frm_track_disp' onChange = "do_tracking(this.form, this.options[this.selectedIndex].value);"> <!-- 7/10/09 -->
<?php
	$selects = array("", "", "", "", "", "", "", "");
	$selects[$track_type] = "SELECTED";
	print "<OPTION VALUE={$GLOBALS['TRACK_NONE']} 		{$selects[$GLOBALS['TRACK_NONE']]} > 	None </OPTION>";
	print "<OPTION VALUE={$GLOBALS['TRACK_APRS']} 		{$selects[$GLOBALS['TRACK_APRS']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_APRS']]} </OPTION>";
	print "<OPTION VALUE={$GLOBALS['TRACK_INSTAM']} 	{$selects[$GLOBALS['TRACK_INSTAM']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_INSTAM']]} </OPTION>";
	print "<OPTION VALUE={$GLOBALS['TRACK_GTRACK']} 	{$selects[$GLOBALS['TRACK_GTRACK']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_GTRACK']]} </OPTION>";
	print "<OPTION VALUE={$GLOBALS['TRACK_LOCATEA']}	{$selects[$GLOBALS['TRACK_LOCATEA']]} > {$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_LOCATEA']]} </OPTION>";
	print "<OPTION VALUE={$GLOBALS['TRACK_GLAT']} 		{$selects[$GLOBALS['TRACK_GLAT']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_GLAT']]} </OPTION>";
	print "<OPTION VALUE={$GLOBALS['TRACK_OGTS']} 		{$selects[$GLOBALS['TRACK_OGTS']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_OGTS']]} </OPTION>";
	print "<OPTION VALUE={$GLOBALS['TRACK_T_TRACKER']} 		{$selects[$GLOBALS['TRACK_T_TRACKER']]} > 	{$GLOBALS['TRACK_NAMES'][$GLOBALS['TRACK_T_TRACKER']]} </OPTION>";	
?>
			</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;
<SCRIPT>				
				var track_info = "APRS:   callsign\nInstamapper:   Device key\nLocateA:   Userid\nGtrack:   Userid\nLatitude:   Badge\nOpenGTS:   Device\n";
</SCRIPT>
				<INPUT TYPE = 'button' onClick = alert(track_info) value="?">
			
				&nbsp;&raquo; <INPUT ID = "track_key" SIZE="<?php print $key_field_size;?>" MAXLENGTH="<?php print $key_field_size;?>" TYPE="text" NAME="frm_callsign" VALUE="<?php print $row['callsign'];?>" /> <!-- 7/23/09 -->
			</TD>
			</TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Status - Select from pulldown menu">Status</A>:&nbsp;</TD>
			<TD ALIGN='left'><SELECT NAME="frm_un_status_id" onChange = "this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; document.res_edit_Form.frm_log_it.value='1'">
<?php
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `status_val` ASC, `group` ASC, `sort` ASC";
			$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			$the_grp = strval(rand());			//  force initial optgroup value
			$i = 0;
			while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
				if ($the_grp != $row_st['group']) {
					print ($i == 0)? "": "</OPTGROUP>\n";
					$the_grp = $row_st['group'];
					print "\t\t<OPTGROUP LABEL='$the_grp'>\n";
					}
				$sel = ($row['un_status_id']== $row_st['id'])? " SELECTED" : "";
				print "\t\t<OPTION VALUE=" . $row_st['id'] . $sel ." STYLE='background-color:{$row_st['bg_color']}; color:{$row_st['text_color']};'  >" . $row_st['status_val']. "</OPTION>\n";	// 3/15/10
				$i++;
				}
			print "\n\t\t</SELECT>\n";
			unset($result_st);
//
		if(is_administrator()) {
?>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A HREF="#" TITLE="Sets boundary used to ring-fence the area this unit is allowed in"><?php print get_text("Ringfence");?></A>:</TD>
			<TD><SELECT NAME="frm_ringfence" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
				<OPTION VALUE=0>Select</OPTION>
<?php
				$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_u_rf` = 1 ORDER BY `line_name` ASC";		// 12/18/10
				$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
					$sel = ($row['ring_fence'] == $row_bound['id']) ? "SELECTED" : "";
					print "\t<OPTION VALUE='{$row_bound['id']}' {$sel}>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT></TD></TR>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A HREF="#" TITLE="Sets exclusion zone for this unit"><?php print get_text("Exclusion Zone");?></A>:</TD>
			<TD><SELECT NAME="frm_excl_zone" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
				<OPTION VALUE=0>Select</OPTION>
<?php
				$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_u_ex` = 1 ORDER BY `line_name` ASC";		// 12/18/10
				$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
					$sel = ($row['excl_zone'] == $row_bound['id']) ? "SELECTED" : "";
					print "\t<OPTION VALUE='{$row_bound['id']}' {$sel}>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT></TD></TR>	
<?php
		}
																						// check any assign records this unit - added 5/23/08
		$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id`=$id AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') ";		// 6/27/08
		$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$cbcount = mysql_affected_rows();				// count of incomplete assigns
		$dis_rmv = ($cbcount==0)? "": " DISABLED";		// allow/disallow removal
		$cbtext = ($cbcount==0)? "": "&nbsp;&nbsp;<FONT size=-2>(NA - calls in progress: " .$cbcount . " )</FONT>";
?>
			</TD></TR>
		<TR CLASS='odd'><TD CLASS="td_label"><A HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:</TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='even'><TD CLASS="td_label"><A HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required">City</A>:</TD> <!-- 7/5/10 -->
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)"> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Phone number">Phone</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="12" MAXLENGTH="48" TYPE="text" NAME="frm_phone" VALUE="<?php print $row['phone'] ;?>" /></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3><TEXTAREA NAME="frm_descr" COLS=56 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Capability - training, equipment on board etc">Capability</A>:&nbsp; </TD>										<TD COLSPAN=3><TEXTAREA NAME="frm_capab" COLS=56 ROWS=2><?php print $row['capab'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="<?php print get_text("Units");?> Contact name">Contact Name</A>:&nbsp;</TD>	<TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Contact via - for email to unit this must be a valid email address or email to SMS address">Contact Via</A>:&nbsp;</TD>	<TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="<?php print $row['contact_via'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="<?php get_provider_name(get_msg_variable('smsg_provider'));?> ID - This is for <?php get_provider_name(get_msg_variable('smsg_provider'));?> Integration and is the ID used by <?php get_provider_name(get_msg_variable('smsg_provider'));?> to send SMS messages"><?php get_provider_name(get_msg_variable('smsg_provider'));?> ID</A>:&nbsp;</TD>	<TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_smsg_id" VALUE="<?php print $row['smsg_id'] ;?>" /></TD></TR>	<!-- 10/23/12-->
		<TR CLASS="odd" VALIGN='baseline'><TD CLASS="td_label"><A HREF="#" TITLE="Delete unit from system - disallowed if unit is assigned to any calls.">Remove <?php print get_text("Units");?></A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
<?php 	print $cbtext; 
?>
		</TD></TR>
		<TR CLASS = "even">
			<TD COLSPAN=4 ALIGN='center'><BR><INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="reset" VALUE="Reset" onClick="track_reset(this.form) ; this.form.reset();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>" onClick="validate(document.res_edit_Form);"></TD></TR>
		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
		<INPUT TYPE="hidden" NAME = "frm_mobile" VALUE=<?php print $row['mobile'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_multi" VALUE=<?php print $row['multi'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_aprs" VALUE=<?php print $row['aprs'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_instam" VALUE=<?php print $row['instam'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_locatea" VALUE=<?php print $row['locatea'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_gtrack" VALUE=<?php print $row['gtrack'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_glat" VALUE=<?php print $row['glat'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_t_tracker" VALUE=<?php print $row['t_tracker'] ;?> />	 <!-- 5/11/11 -->	
		<INPUT TYPE="hidden" NAME = "frm_ogts" VALUE=<?php print $row['ogts'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_direcs" VALUE=<?php print $row['direcs'] ;?> />
		<INPUT TYPE="hidden" NAME="frm_exist_groups" VALUE="<?php print (isset($alloc_groups)) ? $alloc_groups : 1;?>">	 <!-- 2/8/12 -->	
		</FORM></TABLE>
		</TD></TR></TABLE>
<?php
		print do_calls($id);					// generate JS calls array

?>

		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- 1231 -->
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
		</BODY>
		</HTML>
<?php
		if(file_exists("./incs/modules.inc.php")) {	//	10/28/10 Added for add on modules
			$handle=$row['handle'];
			get_modules('res_edit_Form');
			}
		exit();
		}		// end if ($_GET['edit'])
// =================================================================================================================
// view =================================================================================================================

		if ($_getview == 'true') {

			$query_un = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 2 AND `resource_id` = '$_GET[id]' ORDER BY `id` ASC;";	// 6/10/11
			$result_un = mysql_query($query_un);	// 6/10/11
			$un_groups = array();
			$un_names = "";	
			while ($row_un = stripslashes_deep(mysql_fetch_assoc($result_un))) 	{	// 6/10/11
				$un_groups[] = $row_un['group'];
				$query_un2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row_un[group]';";	// 6/10/11
				$result_un2 = mysql_query($query_un2);	// 6/10/11
				while ($row_un2 = stripslashes_deep(mysql_fetch_assoc($result_un2))) 	{	// 6/10/11		
					$un_names .= $row_un2['group_name'] . " ";
					}
				}		
		
			$id = $_GET['id'];
			$query	= "SELECT *, 
				`updated` AS `updated` 
				FROM `$GLOBALS[mysql_prefix]responder` `r` 
				WHERE `r`.`id`={$id} LIMIT 1";
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= stripslashes_deep(mysql_fetch_assoc($result));
//			$is_mobile = (($row['mobile']==1) && ($row['callsign'] != ''));				// 1/27/09
			$lat = $row['lat'];
			$lng = $row['lng'];

			if (isset($row['un_status_id'])) {
				$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id`=" . $row['un_status_id'];	// status value
				$result_st	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$row_st	= mysql_fetch_assoc($result_st);
				unset($result_st);
				}
			$un_st_val = (isset($row['un_status_id']))? $row_st['status_val'] : "?";
			$un_st_bg = (isset($row['bg_color']))? $row_st['bg_color'] : "white";		// 3/14/10
			$un_st_txt = (isset($row['text_color']))? $row_st['text_color'] : "black";
			$type_checks = array ("", "", "", "", "", "");
			$type_checks[$row['type']] = " checked";
			$checked = (!empty($row['mobile']))? " checked" : "" ;

			$coords =  $row['lat'] . "," . $row['lng'];		// for UTM

			$mob_checked = (!empty($row['mobile']))? " checked" : "" ;				// 1/24/09
			$multi_checked = (!empty($row['multi']))? " checked" : "" ;				// 1/24/09
			$aprs_checked = (!empty($row['aprs']))? " checked" : "" ;				// 3/11/09
			$instam_checked = (!empty($row['instam']))? " checked" : "" ;			// 3/11/09
			$locatea_checked = (!empty($row['locatea']))? " checked" : "" ;			// 7/23/09
			$gtrack_checked = (!empty($row['gtrack']))? " checked" : "" ;			// 7/23/09
			$glat_checked = (!empty($row['glat']))? " checked" : "" ;				// 7/23/09
			$t_tracker_checked = (!empty($row['t_tracker']))? " checked" : "" ;		// 5/11/11			
			$ogts_checked = (!empty($row['ogts']))? " checked" : "" ;		// 5/11/11			
			$direcs_checked = (!empty($row['direcs']))? " checked" : "" ;			// 3/19/09
?>
		</HEAD><!-- 1387 -->
		<BODY onLoad = "ck_frames()">
		<A NAME='top'>		<!-- 11/11/09 -->
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></div>
<?php
		if ($_dodisp == 'true') {				// dispatch
			print "\t<BODY onLoad = 'ck_frames(); do_disp();' >\n";
			require_once('./incs/links.inc.php');
			}
		if ($_dodispfac == 'true') {				// dispatch to facility
			print "\t<BODY onLoad = 'ck_frames(); do_dispfac();' onUnload='GUnload()'>\n";
			require_once('./incs/links.inc.php');
			}
		else {
			print "\t<BODY onLoad = 'ck_frames()'>\n";
			require_once('./incs/links.inc.php');
			}

		$temp = $u_types[$row['type']];
		$the_type = $temp[0];			// name of type
?>
			<FONT CLASS="header"><?php print get_text("Units");?>&nbsp;'<?php print $row['name'] ;?>'</FONT> (#<?php print $row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER=0 ID='view_unit' STYLE='display: block'>
			<FORM METHOD="POST" NAME= "res_view_Form" ACTION="<?php echo basename(__FILE__);?>?func=responder">
			<TR CLASS = "even"><TD CLASS="td_label">Name: </TD>			<TD><?php print $row['name'];?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Handle: </TD>		<TD><?php print $row['handle'];?>
			<SPAN STYLE = 'margin-left:30px'  CLASS="td_label"> Icon: </SPAN>&nbsp;<?php print $row['icon_str'];?></TD></TR>
			<TR CLASS = 'even'><TD CLASS="td_label">Location: </TD><TD><?php print $row['street'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = 'odd'><TD CLASS="td_label">City: &nbsp;&nbsp;&nbsp;&nbsp;</TD><TD><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = "even"><TD CLASS="td_label">Phone: &nbsp;</TD><TD COLSPAN=3><?php print $row['phone'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = "odd"><TD CLASS="td_label">Regions: </TD>			<TD><?php print $un_names;?></TD></TR><!-- 6/10/11 -->				
			<TR CLASS = "even"><TD CLASS="td_label">Type: </TD>
				<TD><?php print $the_type;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN CLASS="td_label">
					Mobile  &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" <?php print $mob_checked; ?> DISABLED />&nbsp;&nbsp;
					Multiple  &raquo;<INPUT TYPE="checkbox" NAME="frm_multi_disp" <?php print $multi_checked; ?> DISABLED />&nbsp;&nbsp;
					Directions &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp"<?php print $direcs_checked; ?> DISABLED />
					</SPAN>
				</TD></TR> <!-- // 1/8/09 -->
			<TR CLASS = "odd" VALIGN='top'><TD CLASS="td_label" >Tracking:</TD>
				<TD><?php print $GLOBALS['TRACK_NAMES'][$track_type];?></TD></TR>&nbsp;&nbsp;&nbsp;&nbsp;<!-- 7/10/09 -->
			<TR CLASS = "even" VALIGN='top'>
					<TD CLASS="td_label">Callsign/License/Key: </TD>	<TD><?php print $row['callsign'];?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Status:</TD>		<TD><SPAN STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'><?php print $un_st_val;?>
				</SPAN></TD></TR>
		<TR CLASS='even'><TD CLASS="td_label"><A HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:</TD><TD><?php print $row['street'] ;?></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='odd'><TD CLASS="td_label"><A HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required">City</A>:</TD> <!-- 7/5/10 -->
		<TD><?php print $row['city'] ;?> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<?php print $row['state'] ;?></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Phone number">Phone</A>:&nbsp;</TD><TD COLSPAN=3><?php print $row['phone'] ;?></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "odd"><TD CLASS="td_label">Description: </TD>	<TD><?php print $row['description'];?></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Capability: </TD>	<TD><?php print $row['capab'];?></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Contact name:</TD>	<TD><?php print $row['contact_name'] ;?></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Contact via:</TD>	<TD><?php print $row['contact_via'] ;?></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><?php get_provider_name(get_msg_variable('smsg_provider'));?> ID:</TD>	<TD><?php print $row['smsg_id'] ;?></TD></TR>	<!-- 10/23/12 -->		
		
		<TR CLASS = 'even'><TD CLASS="td_label">As of:</TD>	<TD><?php print format_date_2($row['updated']); ?></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS = "odd"><TD COLSPAN=2 ALIGN='center'>
			<INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<?php		// 1/2/10
		print (is_administrator() || is_super())? 	"<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>\n": "" ;
		print (is_guest())? "" : 					"<INPUT TYPE='button' VALUE='to Dispatch' onClick= \"$('incidents').style.display='block'; $('view_unit').style.display='none';\" STYLE = 'margin-left:12px;'>"; //  8/1/09
?>
			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>" />
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>" />
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			</TD></TR>
<?php
		print "</FORM></TABLE>\n";
		print "\n" . show_assigns(1,$row['id'] ) . "\n";
?>
			<BR /><BR /><BR />
			<TABLE BORDER=0 ID = 'incidents' STYLE = 'display:none' >
			<TR CLASS='even'><TH COLSPAN=99 CLASS='header'> Click incident to dispatch '<?php print $row['handle'] ;?>'</TH></TR>
			<TR><TD></TD></TR>

<?php
											// 11/15/09 - identify candidate incidents - i. e., open and not already assigned to this unit
		$query_t = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = {$row['id']}";
		$result_temp = mysql_query($query_t) or do_error($query_t, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$ctr = 0;		// count hits
		if (mysql_affected_rows()>0) {
			$work = $sep = "";
			$ctr = 0;		// count hits
			while ($row_temp = stripslashes_deep(mysql_fetch_array($result_temp))) {
				if (!(is_date($row_temp['clear']))) {
					$ctr++;										// if open
					$work .= $sep . $row_temp['ticket_id'];
					$sep = ", ";								// set comma separator for next
					}					// end if (is_date())
				}					// end while ($row_temp)
			}					// end if (mysql_affected_rows()>0)

		$instr = ($ctr == 0)? "" : " AND `id` NOT IN ({$work})";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	// 6/10/11
		$result = mysql_query($query);	// 6/10/11
		$al_groups = array();
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 6/10/11
			$al_groups[] = $row['group'];
			}	
		
		if(isset($_SESSION['viewed_groups'])) {		//	6/10/11
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
		$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";	//	6/10/11				
		
		$query_t = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` 
					LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`	
			WHERE `status` IN ({$GLOBALS['STATUS_OPEN']}, {$GLOBALS['STATUS_SCHEDULED']}) {$instr} {$where2}
			GROUP BY `$GLOBALS[mysql_prefix]ticket`.`id`";	//	6/10/11
		$result_t = mysql_query($query_t) or do_error($query_t, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$i=0;			
		while ($row_t = stripslashes_deep(mysql_fetch_array($result_t))) 	{
			switch($row_t['severity'])		{								//color tickets by severity
			 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
				case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
				default: 							$severityclass='severity_normal'; break;
				}

			print "\t<TR CLASS ='" .  $evenodd[($i+1)%2] . "' onClick = 'to_routes(\"" . $row_t['id'] . "\")'>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row_t['scope']}'>" . 						shorten($row_t['scope'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row_t['description']}'>" . 				shorten($row_t['description'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row_t['street']} {$row_t['city']}'>" . 	shorten($row_t['street'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row_t['city']}'>" . 						shorten($row_t['city'], 8). "</TD>";
			print "\t\t</TR>\n";
			$i++;
			}				// end while ($row_t ... )

			print ($i>0)? "" : "<TR><TD COLSPAN=99 ALIGN='center'><BR />No incidents available</TD></TR>\n";
?>
			<TR><TD ALIGN="center" COLSPAN=99><BR /><BR />
				<INPUT TYPE="button" VALUE="Cancel" onClick = "$('incidents').style.display='none'; $('view_unit').style.display='block';">
			</TD></TR>
			</TABLE><BR><BR>

			<BR /><BR /><BR />
			<TABLE BORDER=0 ID = 'facilities' STYLE = 'display:none' >
			<TR CLASS='odd'><TH COLSPAN=99 CLASS='header'> Click Facility to route '<?php print $row['handle'] ;?>'</TH></TR>
			<TR><TD></TD></TR>

<?php																								// 6/1/08 - added
		$query_fa = "SELECT * FROM $GLOBALS[mysql_prefix]facilities ORDER BY `type`";
		$result_fa = mysql_query($query_fa) or do_error($query_fa, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
							// major while ... starts here
		$ff=0;
		while ($row_fa = stripslashes_deep(mysql_fetch_array($result_fa))) 	{
			print "\t<TR CLASS ='" .  $evenodd[($ff+1)%2] . "' onClick = 'to_fac_routes(\"" . $row_fa['id'] . "\")'>\n";
			print "\t\t<TD>" . $row_fa['id'] . "</TD>\n";
			print "\t\t<TD TITLE ='{$row_fa['name']}'>" . shorten($row_fa['name'], 24) . "</TD>\n";
			print "\t\t<TD TITLE ='{$row_fa['description']}'>" . shorten($row_fa['description'], 40) . "</TD>\n";
			print "\t\t</TR>\n";
			$ff++;
			}
?>

			<TR><TD ALIGN="center" COLSPAN=99><BR /><BR />
				<INPUT TYPE="button" VALUE="Cancel" onClick = "$('facilities').style.display='none'; $('view_unit').style.display='block';">
			</TD></TR>
			</TABLE><BR><BR>
			</TD></TR></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "<?php echo basename(__FILE__);?>?func=responder&edit=true&id=<?php print $id; ?>"></FORM>
			<FORM NAME="routes_Form" METHOD="get" ACTION = "routes_nm.php">
			<INPUT TYPE="hidden" NAME="ticket_id" 	VALUE="">						<!-- 10/16/08 -->
			<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
			</FORM>
			<FORM NAME="fac_routes_Form" METHOD="get" ACTION = "fac_routes_nm.php">
			<INPUT TYPE="hidden" NAME="fac_id" 	VALUE="">						<!-- 10/16/08 -->
			<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
			</FORM>

							<!-- END UNIT VIEW -->
			<!-- 1408 -->
			<A NAME="bottom" /> <!-- 5/3/10 -->
			<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
			</BODY>
			</HTML>
<?php
			if((is_super()) || (is_administrator()) || (is_user())) {	//	10/28/10 Added for add on modules
				if(file_exists("./incs/modules.inc.php")) {
					$handle=$row['handle'];
					get_modules('view_form');
					}
				}	
			exit();
			}		// end if ($_GET['view'])
// ============================================= initial display =======================

			if (!isset($mapmode)) {$mapmode="a";}
			print "<SPAN STYLE='margin-left:120px;'>{$caption}</SPAN>";
?>
		</HEAD><!-- 1387 -->
		<BODY onLoad = "ck_frames(); set_regions_control();">
		<A NAME='top'>		<!-- 11/11/09 -->
			<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></div>
		
<?php
		require_once('./incs/links.inc.php');
		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";		// 12/17/08
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		unset($result);		
		$required = 40 + (mysql_affected_rows()*22);
		$the_height = (integer)  min (round($units_side_bar_height * $_SESSION['scr_height']), $required );		// set the max		
		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 		
		$regions_inuse = get_regions_inuse($user_level);	//	6/10/11
		$group = get_regions_inuse_numbers($user_level);	//	6/10/11		
		
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 4/13/11
		$result = mysql_query($query);	// 4/13/11
		$al_groups = array();
		$al_names = "";	
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/13/11
			$al_groups[] = $row['group'];
			if(!(is_super())) {
				$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 4/13/11
				$result2 = mysql_query($query2);	// 4/13/11
				while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	// 4/13/11		
					$al_names .= $row2['group_name'] . ", ";
					}
				} else {
					$al_names = "ALL. Superadmin Level";
				}
			}
			
		if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
			$curr_viewed= explode(",",$_SESSION['viewed_groups']);
			} else {
			$curr_viewed = $al_groups;
			}

		$curr_names="";	//	6/10/11
		$z=0;	//	6/10/11
		foreach($curr_viewed as $grp_id) {	//	6/10/11
			$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
			$curr_names .= get_groupname($grp_id);
			$curr_names .= $counter;
			$z++;
			}			

		$heading = get_text("Units") . " - " . get_variable('map_caption');
?>
			<DIV id='top_reg_box' style='display: none;'>
				<DIV id='region_boxes' class='header_reverse' style='align: center; width: 100%; text-align: center; margin-left: auto; margin-right: auto; height: 30px; z-index: 1;'></DIV>
			</DIV>
			<DIV style='z-index: 1;'>
			<TABLE ID='outer' style='width: 100%;'>
			<TR CLASS='header'><TD ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'><?php print $heading; ?> </FONT></TD></TR>	<!-- 4/11/11 -->
			<TR CLASS='spacer'><TD CLASS='spacer' ALIGN='center'>&nbsp;</TD></TR>				<!-- 4/11/11 -->			
			<TR><TD ALIGN='left'>
		<DIV ID='resp_table'>	
			<TABLE BORDER=0 ID='outer' STYLE = 'width:auto;'><TR><TD>
				<TABLE ID = 'sidebar' BORDER = 0 style='text-align: left;'>
				<TR class='even'>	<TD colspan=99 ALIGN='center'><B><?php print get_text("Units");?> (<DIV id="num_units" style="display: inline;"></DIV>)</B></TD></TR>
				<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for details - or to dispatch</TD></TR>
			
				<TR><TD>
				<DIV ID='side_bar' style="height: <?php print $the_height; ?>px;  overflow-y: scroll; overflow-x: hidden;"></DIV></TD></TR>
				<TR class='spacer'><TD class='spacer'>&nbsp;</TD></TR>
				<TR><TD COLSPAN=99 ALIGN='center'>
<?php
		print "<TR CLASS='odd'><TD COLSPAN=99 ALIGN='center'><DIV style='width: 80%;'>" . get_units_legend() . "</DIV></TD></TR>";
		print "<TR class='spacer'><TD class='spacer'>&nbsp;</TD></TR>";		
		$buttons = "<TR><TD COLSPAN=99 ALIGN='center'><BR />
			<INPUT TYPE = 'button' onClick = 'document.tracks_Form.submit();' VALUE='" . get_text("Units") . " Tracks' STYLE = 'margin-left: 60px'>";
		if (!(is_guest())) {
			if ((!(is_user())) && (!(is_unit()))) {				// 7/27/10
				$buttons .="<INPUT TYPE='button' value= 'Add a " . get_text("Units") . "'  onClick ='document.add_Form.submit();' style = 'margin-left:20px'>";	// 10/8/08
				}			
			$buttons .= "<INPUT TYPE = 'button' onClick = 'do_mail_win()' VALUE='Email " . get_text("Units") . "'  style = 'margin-left:20px'>";	// 6/13/09
			}

		$buttons .= "</TD></TR>";
		print $buttons;
		$the_func = (can_edit())? "edit" : "view" ;
?>

			</TABLE></TABLE></DIV>
<?php

		$from_right = 20;	//	5/3/11
		$from_top = 10;		//	5/3/11		
?>
			</TD></TABLE></DIV></TD></TR></TABLE>	<!-- end of outer -->
<?php
	if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	//	6/10/11
		$regs_col_butt = ((isset($_SESSION['regions_boxes'])) && ($_SESSION['regions_boxes'] == "s")) ? "" : "none";	//	6/10/11
		$regs_exp_butt = ((isset($_SESSION['regions_boxes'])) && ($_SESSION['regions_boxes'] == "h")) ? "" : "none";	//	6/10/11	
?>		
		<DIV id = 'regions_outer' style = "position: fixed; right: 20%; top: 10%; z-index: 1000;">
			<DIV id="boxB" class="box" style="z-index:1000;">
				<DIV class="bar_header" class="heading_2" style='white-space: nowrap;'>	
				<DIV class="bar" STYLE="color:red; z-index: 1000; position: relative; top: 2px;"
					onmousedown="dragStart(event, 'boxB')"><i>Drag me</i>
					<DIV id="collapse_regs" class='plain' style ="display: inline; z-index:1001; cursor: pointer; float: right; margin-left: 0px; font-size: 10px;" onclick="$('top_reg_box').style.display = 'block'; $('regions_outer').style.display = 'none';">Dock</DIV><BR /><BR />
				</DIV>
				<DIV id="region_boxes2" class="content" style="z-index: 1000;"></DIV>
				</DIV>
			</DIV>
		</DIV>	
<?php	
	}		
		print get_buttons_inner();	//	3/28/12
		print get_buttons_inner2();	//	3/28/12		
?>
			<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='<?php echo $the_func;?>' VALUE='true'>
			<INPUT TYPE='hidden' NAME='id' VALUE=''>
			</FORM>
			
			<FORM NAME='add_Form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='add' VALUE='true'>
			</FORM>

			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php echo basename(__FILE__);?>?func=responder"></FORM>
			<!-- 1452 -->
			<A NAME="bottom" /> <!-- 5/3/10 -->
			<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
			</BODY>				<!-- END RESPONDER LIST and ADD -->
<?php
		print do_calls();		// generate JS calls array

		print list_responders("", 0);				// ($addon = '', $start)
		print "\n</HTML> \n";
		if((is_super()) || (is_administrator())) {	//	10/28/10 Added for add on modules
			if(file_exists("./incs/modules.inc.php")) {
				get_modules('list_form');
				}
			}					
		exit();
    break;
?>

