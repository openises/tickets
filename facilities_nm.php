<?php
error_reporting(E_ALL);
$facs_side_bar_height = .5;		// max height of units sidebar as decimal fraction of screen height - default is 0.5 (50%)
$iw_width = "300px";		// map infowindow with
$side_bar_height = 0.5;		// height of units sidebar as decimal fraction - default is 0.9 (90%)
$zoom_tight = FALSE;		// replace with a decimal number to over-ride the standard default zoom setting
/*
7/16/10 Initial Release for no internet operation - created from facilities.php
8/25/10 - removed map-related script functions, lit top-frame button
9/22/10 - corrected missing frm_handle in insert, location data and display class values
2/17/11 Changed wrong log events from log_unit_status to LOG_FACILITY_ADD or LOG_FACILITY_CHANGE as appropriate
3/15/11 Added reference to stylesheet.php for revisable day night colors
3/19/11 revised index length to 6 chars
5/4/11 get_new_colors() added
6/10/11 Added group functonality
7/1/11 permissions corrected
8/1/11 state length increased to 4 chars
2/8/12 Fixed error on single region operation - editing a unit removes region 1 region allocation.
10/31/2012 Beds information added
11/1/2012 remove unix conversion from field 'updated'
5/30/13 Implement catch for when there are no allocated regions for current user. 
6/4/2013 beds information added for all operations
8/28/13 Added mailgroup capability - email to mailgroup when facility set as originating or receiving facility. Also about status field.
*/

function do_updated ($instr) {		// 11/1/2012
	return substr($instr, 8, 8);
	}
	
@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));

$key_field_size = 30;						// 7/23/09
$st_size = (get_variable("locale") ==0)?  2: 4;		

//$tolerance = 5 * 60;		// nr. seconds report time may differ from UTC

$FacID = (isset($_GET['id'])) ? $_GET['id'] : 0;	

extract($_GET);
extract($_POST);
/*
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}
*/
$usng = get_text('USNG');
$u_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);

$icons = $GLOBALS['fac_icons'];
$sm_icons = $GLOBALS['sm_fac_icons'];

function get_icon_legend (){			// returns legend string
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `type`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$temp = $u_types[$row['type']];
		$print .= "\t\t<DIV class='legend' style='height: 3em; text-align: center; vertical-align: middle; float: left;'> ". $temp[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$temp[1]] . "' STYLE = 'vertical-align: middle' BORDER=0 PADDING='10'>&nbsp;&nbsp;&nbsp;</DIV>\n";
		}
	return $print;
	}			// end function get_icon_legend ()
	
function get_mailgroup_name($id) {	//	8/28/13
	if($id == 0) {
		return "";
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_ret = $row['name'];
	return $the_ret;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Facilities Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
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

	parent.upper.show_butts();
	parent.upper.light_butt('facy');										// light the button - 8/25/10

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;

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
	
	function $() {
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

	function get_new_colors() {								// 5/4/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()

	function file_window(id) {										// 9/10/13
		var url = "file_upload.php?facility_id="+ id;
		var nfWindow = window.open(url, 'NewFileWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		setTimeout(function() { nfWindow.focus(); }, 1);
		}
	
	function get_files() {										// 9/10/13
		var the_fac_id = <?php print $FacID;?>;
		if(the_fac_id == 0) {
			return;
			}
		$('the_file_list').innerHTML = "Please Wait, loading files";
		randomnumber=Math.floor(Math.random()*99999999);
		var url ="./ajax/file_list.php?facility_id=" + the_fac_id + "&version=" + randomnumber;
		sendRequest (url, filelist_cb, "");
		function filelist_cb(req) {
			var theFiles=req.responseText;
			$('the_file_list').innerHTML = theFiles;		
			}
		}

	function checkAll() {	//	9/10/13
		var theField = document.res_add_Form.elements["frm_group[]"];
		for (i = 0; i < theField.length; i++) {
			theField[i].checked = true ;
			}
		}

	function uncheckAll() {	//	9/10/13
		var theField = document.res_add_Form.elements["frm_group[]"];
		for (i = 0; i < theField.length; i++) {
			theField[i].checked = false ;
			}
		}
				
	function sendRequest(url,callback,postData) {								// 9/10/13
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}

	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];

	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}

	function to_str(instr) {
		function ord( string ) {
		    return (string+'').charCodeAt(0);
			}

		function chr( ascii ) {
		    return String.fromCharCode(ascii);
			}
		function to_char(val) {
			return(chr(ord("A")+val));
			}

		var lop = (instr % 26);								// low-order portion, a number
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

	function do_mail_win() {
		if(starting) {return;}					
		starting=true;	
	
		newwindow_um=window.open('do_fac_mail.php', 'E_mail_Window',  'titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');

		if (isNull(newwindow_um)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_um.focus();
		starting = false;
		}

	function do_mail_in_win(id) {			// individual email
		if(starting) {return;}					
		starting=true;	
		var url = "do_fac_mail.php?fac_id=" + id;	
		newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height=300,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
		if (isNull(newwindow_in)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_in.focus();
		starting = false;
		}


	function to_routes(id) {
		document.routes_Form.ticket_id.value=id;
		document.routes_Form.submit();
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

	function validate(theForm) {						// Facility form contents validation
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				var str = "Please confirm removing '" + theForm.frm_name.value + "'";
				if(confirm(str)) 	{
					theForm.submit();
					return true;}
				else 				{return false;}
				}
			}

		var errmsg="";
		if (theForm.frm_name.value.trim()=="")											{errmsg+="Facility NAME is required.\n";}
		if (theForm.frm_handle.value.trim()=="")										{errmsg+="Facility HANDLE is required.\n";}
		if (theForm.frm_icon_str.value.trim()=="")										{errmsg+="Facility ICON is required.\n";}
		if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)			{errmsg+="Facility TYPE is required.\n";}
		if (theForm.frm_status_id.options[theForm.frm_status_id.selectedIndex].value==0)	{errmsg+="Facility STATUS is required.\n";}
		if (theForm.frm_descr.value.trim()=="")											{errmsg+="Facility DESCRIPTION is required.\n";}
		
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {														// good to go!
//			top.upper.calls_start();
			theForm.submit();
//			return true;
			}
		}				// end function va lidate(theForm)

		
	function add_res () {		// turns on add responder form
		showit('res_add_form');
		hideit('tbl_facilities');
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

	function do_disp(){											// show incidents for dispatch
		$('incidents').style.display='block';
		$('view_unit').style.display='none';
		}

	function do_add_reset(the_form) {
		the_form.reset();
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


<?php

function list_facilities($addon = '', $start) {
	global $iw_width, $u_types, $tolerance;
//	$assigns = array();
//	$tickets = array();

	// $query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";

	// $result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	// while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
		// $assigns[$row_as['responder_id']] = $row_as['ticket'];
		// $tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		// }
	// unset($result_as);
	// $calls = array();
	// $calls_nr = array();
	// $calls_time = array();

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
	
	function do_sidebar (sidebar, id, the_class, fac_id, fac_index) {
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = 'myclick(" + fac_index + ");'>";
		side_bar_html += sidebar + "</TR>\n";	//10/29/09 removed period
		}

	function myclick(fac_index) {				// Responds to sidebar click - view facility data
		document.view_form.id.value=fac_index;
		document.view_form.submit();
		}
		
	var icons=new Array;							// maps type to icon blank

<?php
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$icons = $GLOBALS['fac_icons'];

while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// map type to blank icon id
	$blank = $icons[$row['icon']];
	print "\ticons[" . $row['id'] . "] = " . $row['icon'] . ";\n";	//
	}
unset($result);

$dzf = get_variable('def_zoom_fixed');
print "\tvar map_is_fixed = ";

print (((my_is_int($dzf)) && ($dzf==2)) || ((my_is_int($dzf)) && ($dzf==3)))? "true;\n":"false;\n";

?>
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_facilities' WIDTH = <?php print max(320, intval($_SESSION['scr_width']* 0.6));?> >";
	side_bar_html += "<TR class='even'>	<TD><B>Icon</B></TD><TD ALIGN='left'><B><?php print get_text("Type"); ?></B></TD><TD><B>Handle</B></TD><TD ALIGN='left'><B>Name</B></TD><TD ALIGN='center' COLSPAN=2><B><?php print get_text("Beds"); ?></B></TD><TD ALIGN='left'>&nbsp;<B><?php print get_text("Status"); ?></B></TD><TD ALIGN='left'><B><?php print get_text("As of"); ?></B></TD></TR>";

	var which;
	var i = <?php print $start; ?>;					// sidebar/icon index
	var points = false;								// none

<?php

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

//	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `id`";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);

	$type_vals = array();											// build array of $status_vals
	$type_vals[''] = $type_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";
	$result_ty = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_ty = stripslashes_deep(mysql_fetch_assoc($result_ty))) {
		$temp = $row_ty['id'];
		$type_vals[$temp] = $row_ty['name'];
		}
	unset($result_ty);

	$al_groups = $_SESSION['user_groups'];
	if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}

	if(!isset($curr_viewed)) {	
		if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = "WHERE `a`.`type` = 3";
			} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= "AND `a`.`type` = 3";	//	6/10/11	
			}
		} else {
		if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = "WHERE `a`.`type` = 3";
			} else {			
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($curr_viewed as $grp) {	//	6/10/11
				$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= "AND `a`.`type` = 3";	//	6/10/11	
			}
		}	//	end if count($al_groups == 0)
	//	3/15/11, 6/10/11
	
	$query = "SELECT *,
		`t`.`id` AS `type_id`, 	
		`f`.id AS `id`, 
		`f`.status_id AS status_id,
		`f`.description AS facility_description,
		`t`.name AS fac_type_name, 
		`f`.name AS name, `f`.street AS street,
		`f`.city AS city, `f`.state AS state 
		FROM `$GLOBALS[mysql_prefix]facilities` `f`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = a.resource_id )			
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` ON `f`.type = `t`.id 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` ON `f`.status_id = `s`.id 
		{$where2}  GROUP BY `f`.id ORDER BY `f`.type ASC";	

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_facilities = mysql_affected_rows();
	$i=0;				// counter
// =============================================================================
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// ==========  major while() for Facility ==========

		$fac_gps = get_allocates(3, $row['id']);	//	6/10/11
		$grp_names = "Groups Assigned: ";	//	6/10/11
		$y=0;	//	6/10/11
		foreach($fac_gps as $value) {	//	6/10/11
			$counter = (count($fac_gps) > ($y+1)) ? ", " : "";
			$grp_names .= get_groupname($value);
			$grp_names .= $counter;
			$y++;
			}
		$grp_names .= " / ";
		$the_bg_color = 	$GLOBALS['FACY_TYPES_BG'][$row['icon']];		// 2/8/10
		$the_text_color = 	$GLOBALS['FACY_TYPES_TEXT'][$row['icon']];		// 2/8/10	
		$got_point = FALSE;
		print "\n\t\tvar i=$i;\n";

	if(is_guest()) {
		$toedit = $tomail = $toroute = "";
		}
	else {
		$toedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='facilities_nm.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>" ;
		$tomail = "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = 'do_mail_in_win({$row['id']})'><U><B>Email</B></U></SPAN>" ;
		$toroute = "&nbsp;<A HREF='routes_nm.php?fac_id=" . $row['id'] . "'><U>Route To Facility</U></A>";	
		}

		$temp = $row['status_id'] ;	
		$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";	
		$temp_type = $row['type_id'] ;	
		$the_type = (array_key_exists($temp_type, $type_vals))? $type_vals[$temp_type] : "??";

		$update_error = strtotime('now - 6 hours');							// set the time for silent setting
// Icon
		$sidebar_line = "<TD>{$row['icon_str']}</TD>";			// 10/8/09		
// Type
		$sidebar_line .= "<TD>{$the_type}</TD>";	//	6/10/11
// Handle		
		$handle = addslashes($row['handle']);		//	5/30/10
		$sidebar_line .= "<TD TITLE = '{$handle}'><B>". shorten($handle, 16) . "</B></TD>";			// 10/8/09		
// name
		$name = $row['name'];		//	10/8/09
		$fac_index = $row['id'];		//	10/8/09
		$temp = explode("/", $name );
		$display_name = $temp[0];
		$sidebar_line .= "<TD TITLE = '" . $grp_names . "Facility Name: " . addslashes($display_name) . "' ><U><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>" . addslashes(shorten($display_name, 40)) ."</SPAN></U></TD>";
// Beds
		$sidebar_line .= "<TD WIDTH='15%' TITLE = 'Beds' ALIGN='right'><U><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'><NOBR>{$row['beds_a']}/{$row['beds_o']}</NOBR></SPAN></U></TD>";
		$sidebar_line .= "<TD WIDTH='15%' TITLE = '{$row['beds_info']}'  ALIGN='left'><U><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'><NOBR>" . shorten($row['beds_info'], 20) . "</NOBR></SPAN></U></TD>";

// status
		$sidebar_line .= "<TD CLASS='td_data' TITLE = '" . addslashes ($the_status) . "'> " . get_status_sel($row['id'], $row['status_id'], 'f') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";	//	3/15/11
// as of
		$the_time = $row['updated'];
		$the_class = "td_data";
		$strike = $strike_end = "";

		$sidebar_line .= "<TD CLASS='$the_class'> $strike" . do_updated($the_time) . "$strike_end</TD>";
		$name = $row['name'];	// 10/8/09	
		$fac_index = $row['id'];		//	10/8/09
		$temp = explode("/", $name );
		$index = substr($temp[count($temp) -1], -6 , strlen($temp[count($temp) -1]));		// 3/19/11			
?>
		var fac_id = "<?php print $index;?>";	//	10/8/09
		var fac_index = "<?php print $fac_index;?>";	//	10/8/09		
		var the_class = "td_label";

<?php
		print "\tdo_sidebar (\" {$sidebar_line} \", i, the_class, fac_id, fac_index);\n";
	$i++;				// zero-based
	}				// end  ==========  while() for Facility ==========

?>
var buttons_html = "";
<?php

	if(!empty($addon)) {
		print "\n\tbuttons_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div
	$("buttons").innerHTML = buttons_html;	// append the assembled side_bar_html contents to the side_bar div
	$("num_facilities").innerHTML = <?php print $num_facilities;?>;

</SCRIPT>

<?php
	}				// end function list_Facilities() ===========================================================

	function finished ($caption) {
		print "</HEAD><BODY>";
		require_once('./incs/links.inc.php');	// 10/6/09
//		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
		print "</FORM>\n<A NAME='bottom' />\n</BODY></HTML>";
		}

	function do_calls($id = 0) {				// generates js callsigns array
		$print = "\n<SCRIPT >\n";
		$print .="\t\tvar calls = new Array();\n";
		$query	= "SELECT `id`, `callsign` FROM `$GLOBALS[mysql_prefix]facilities` where `id` != $id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			if (!empty($row['callsign'])) {
				$print .="\t\tcalls.push('" .$row['callsign'] . "');\n";
				}
			}				// end while();
		$print .= "</SCRIPT>\n";
		return $print;
		}		// end function do calls()

	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";
	$_dodisp = 			(array_key_exists ('disp',$_GET ))? $_GET['disp']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Facility - checkbox
		$query = "DELETE FROM $GLOBALS[mysql_prefix]facilities WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Facility <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? "0.999999" : quote_smart(trim($_POST['frm_lat'])) ;
			$the_lng = empty($_POST['frm_lng'])? "0.999999" : quote_smart(trim($_POST['frm_lng'])) ;
			
			$curr_groups = $_POST['frm_exist_groups']; 	//	4/14/11
			$groups = isset($_POST['frm_group']) ? ", " . implode(',', $_POST['frm_group']) . "," : $_POST['frm_exist_groups'];	//	3/28/12 - fixes error when accessed from view ticket screen..	
			$fac_id = $_POST['frm_id'];
			$fac_stat = $_POST['frm_status_id'];
			$by = $_SESSION['user_id'];			
			$query = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET
				`name`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) . ",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) . ",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`handle`= " . 		quote_smart(trim($_POST['frm_handle'])) . ",
				`icon_str`= " . 	quote_smart(trim($_POST['frm_icon_str'])) . ",
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",
				`beds_a`= " . 		quote_smart(trim($_POST['frm_beds_a'])) . ",
				`beds_o`= " . 		quote_smart(trim($_POST['frm_beds_o'])) . ",
				`beds_info`= " . 	quote_smart(trim($_POST['frm_beds_info'])) . ",
				`capab`= " . 		quote_smart(trim($_POST['frm_capab'])) . ",
				`status_id`= " . quote_smart(trim($_POST['frm_status_id'])) . ",
				`status_about`= " . quote_smart(trim($_POST['frm_status_about'])) . ",
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`contact_name`= " . quote_smart(trim($_POST['frm_contact_name'])) . ",
				`contact_email`= " . 	quote_smart(trim($_POST['frm_contact_email'])) . ",
				`contact_phone`= " . 	quote_smart(trim($_POST['frm_contact_phone'])) . ",
				`security_contact`= " . quote_smart(trim($_POST['frm_security_contact'])) . ",
				`security_email`= " . 	quote_smart(trim($_POST['frm_security_email'])) . ",
				`security_phone`= " . 	quote_smart(trim($_POST['frm_security_phone'])) . ",
				`opening_hours`= " . 	quote_smart(trim($_POST['frm_opening_hours'])) . ",
				`access_rules`= " . 	quote_smart(trim($_POST['frm_access_rules'])) . ",
				`security_reqs`= " . 	quote_smart(trim($_POST['frm_security_reqs'])) . ",
				`pager_p`= " . 	quote_smart(trim($_POST['frm_pager_p'])) . ",
				`pager_s`= " . 	quote_smart(trim($_POST['frm_pager_s'])) . ",
				`type`= " . 		quote_smart(trim($_POST['frm_type'])) . ",
				`user_id`= " . 		quote_smart(trim($_SESSION['user_id'])) . ",
				`notify_mailgroup` = " . quote_smart(trim($_POST['frm_notify_mailgroup'])) . ",
				`notify_email` = " . quote_smart(trim($_POST['frm_notify_email'])) . ",
				`notify_when` = " . quote_smart(trim($_POST['frm_notify_when'])) . ",
				`updated`= " . 		quote_smart(trim($now)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";	//	8/28/13

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_FACILITY_CHANGE'], 0, $_POST['frm_id'], $_POST['frm_status_id']);}	//	2/17/11
			
			$list = $_POST['frm_exist_groups']; 	//	4/14/11
			$ex_grps = explode(',', $list); 	//	4/14/11 
			
			if($curr_groups != $groups) { 	//	4/14/11
				foreach($_POST['frm_group'] as $posted_grp) { 	//	4/14/11
					if(!in_array($posted_grp, $ex_grps)) {
						$query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
								($posted_grp, 3, '$now', $fac_stat, $fac_id, 'Allocated to Group' , $by)";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				foreach($ex_grps as $existing_grps) { 	//	4/14/11
					if(!in_array($existing_grps, $_POST['frm_group'])) {
						$query  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type` = 3 AND `group` = $existing_grps AND `resource_id` = {$fac_id}";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				}				
			
			
			$caption = "<i>" . stripslashes_deep($_POST['frm_name']) . "</i><B>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$by = $_SESSION['user_id'];		//	4/14/11

		$frm_lat = (empty($_POST['frm_lat']))? '0.999999': quote_smart(trim($_POST['frm_lat'])); // 9/22/10
		$frm_lng = (empty($_POST['frm_lng']))? '0.999999': quote_smart(trim($_POST['frm_lng']));
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]facilities` (
			`name`, `street`, `city`, `state`, `handle`, `icon_str`, `boundary`, `description`, `beds_a`, `beds_o`, `beds_info`, `capab`, `status_id`, `status_about`, `contact_name`, `contact_email`, `contact_phone`, `security_contact`, `security_email`, `security_phone`, `opening_hours`, `access_rules`, `security_reqs`, `pager_p`, `pager_s`, `type`, `user_id`, `notify_mailgroup`, `notify_email`, `notify_when`, `updated` )
			VALUES (" .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_street'])) . "," .
				quote_smart(trim($_POST['frm_city'])) . "," .
				quote_smart(trim($_POST['frm_state'])) . "," .
				quote_smart(trim($_POST['frm_handle'])) . "," .
				quote_smart(trim($_POST['frm_icon_str'])) . "," .
				quote_smart(trim($_POST['frm_descr'])) . "," .
				quote_smart(trim($_POST['frm_beds_a'])) . "," .
				quote_smart(trim($_POST['frm_beds_o'])) . "," .
				quote_smart(trim($_POST['frm_beds_info'])) . "," .
				quote_smart(trim($_POST['frm_capab'])) . "," .
				quote_smart(trim($_POST['frm_status_id'])) . "," .
				quote_smart(trim($_POST['frm_status_about'])) . "," .
				quote_smart(trim($_POST['frm_contact_name'])) . "," .
				quote_smart(trim($_POST['frm_contact_email'])) . "," .
				quote_smart(trim($_POST['frm_contact_phone'])) . "," .
				quote_smart(trim($_POST['frm_security_contact'])) . "," .
				quote_smart(trim($_POST['frm_security_email'])) . "," .
				quote_smart(trim($_POST['frm_security_phone'])) . "," .
				quote_smart(trim($_POST['frm_opening_hours'])) . "," .
				quote_smart(trim($_POST['frm_access_rules'])) . "," .
				quote_smart(trim($_POST['frm_security_reqs'])) . "," .
				quote_smart(trim($_POST['frm_pager_p'])) . "," .
				quote_smart(trim($_POST['frm_pager_s'])) . "," .
				$frm_lat . "," .
				$frm_lng . "," .
				quote_smart(trim($_POST['frm_type'])) . "," .
				quote_smart(trim($_SESSION['user_id'])) . "," .
				quote_smart(trim($_POST['frm_notify_mailgroup'])) . "," .
				quote_smart(trim($_POST['frm_notify_email'])) . "," .
				quote_smart(trim($_POST['frm_notify_when'])) . "," .
				quote_smart(trim($now)) . ");";	// 8/28/13

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();

//	9/10/13 File Upload support
		$print = "";
		if ((isset($_FILES['frm_file'])) && ($_FILES['frm_file']['name'] != "")){
			$nogoodFile = false;	
			$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py"); 
			foreach ($blacklist as $file) { 
				if(preg_match("/$file\$/i", $_FILES['frm_file']['name'])) { 
					$nogoodFile = true;
					}
				}
			if(!$nogoodFile) {
				$exists = false;
				$existing_file = "";
				$upload_directory = "./files/";
				if (!(file_exists($upload_directory))) {				
					mkdir ($upload_directory, 0770);
					}
				chmod($upload_directory, 0770);	
				$filename = rand(1,999999);
				$realfilename = $_FILES["frm_file"]["name"];
				$file = $upload_directory . $filename;
					
//	Does the file already exist in the files table		

				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `orig_filename` = '" . $realfilename . "'";
				$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);	
				if(mysql_affected_rows() == 0) {	//	file doesn't exist already
					if (move_uploaded_file($_FILES['frm_file']['tmp_name'], $file)) {	// If file uploaded OK
						if (strlen(filesize($file)) < 20000000) {
							$print .= "";
							} else {
							$print .= "Attached file is too large!";
							}
						} else {
						$print .= "Error uploading file";
						}
					} else {
					$row = stripslashes_deep(mysql_fetch_assoc($result));			
					$exists = true;
					$existing_file = $row['filename'];	//	get existing file name
					}
					
				$from = $_SERVER['REMOTE_ADDR'];	
				$filename = ($existing_file == "") ? $filename : $existing_file;	//	if existing file, use this file and write new db entry with it.
				$query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]files` (
						`title` , `filename` , `orig_filename`, `ticket_id` , `responder_id` , `facility_id`, `type`, `filetype`, `_by`, `_on`, `_from`
					) VALUES (
						'" . $_POST['frm_file_title'] . "', '" . $filename . "', '" . $realfilename . "', " . $id . ", 0,
						0, 0, '" . $_FILES['frm_file']['type'] . "', $by, '" . $now . "', '" . $from . "'
					)";
				$result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
				if($result_insert) {	//	is the database insert successful
					$dbUpdated = true;
					} else {	//	problem with the database insert
					$dbUpdated = false;				
					}
				}
			} else {	// Problem with the file upload
			$fileUploaded = false;
			}	
			
// End of file upload
		
		$status_id = $_POST['frm_status_id'];	//4/14/11
		foreach ($_POST['frm_group'] as $grp_val) {	// 6/10/11
			$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
					($grp_val, 3, '$now', $status_id, $new_id, 'Allocated to Group' , $by)";
			$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);				
		}
		
		do_log($GLOBALS['LOG_FACILITY_ADD'], 0, mysql_insert_id(), $_POST['frm_status_id']);	//	2/17/11

		$caption = "<B>Facility  <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";

		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
// add ===========================================================================================================================
// add ===========================================================================================================================

	if ($_getadd == 'true') {
		print do_calls();		// call signs to JS array for validation
?>
		</HEAD>
		<BODY  onLoad = "ck_frames();" >
		<A NAME='top'>		<!-- 11/11/09 -->
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></div>
<?php
		require_once('./incs/links.inc.php');
?>
		<TABLE BORDER=0 ID='outer' BORDER=><TR><TD>
		<TABLE BORDER="0" ID='addform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'><?php print get_text("Add Facility"); ?></FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>		
		<FORM NAME= "res_add_Form" METHOD="POST" ACTION="facilities_nm.php?func=responder&goadd=true">
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker"><?php print get_text("Name"); ?></A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility"><?php print get_text("Handle"); ?></A>:&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="48" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="" />
				<SPAN STYLE = "margin-left:40px;" CLASS="td_label" TITLE="A 3-letter value to be used in the map icon">Icon:</SPAN>&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;
					<INPUT TYPE="text" SIZE = 3 MAXLENGTH=3 NAME="frm_icon_str" VALUE="" />			
			</TD></TR>			
			
<?php
	if(get_num_groups()) {
		if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>		
			<TR CLASS='even' VALIGN="top">	<!--  4/12/11 -->
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
			<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
			<DIV id='checkButts' style='display: none;'>
				<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
				<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>	
			</DIV>
			<?php
			
			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
			print get_user_group_butts(($_SESSION['user_id']));	//	6/10/11		
			
			} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11
?>		
			<TR CLASS='even' VALIGN="top">	<!--  4/12/11 -->
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
			<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
			<DIV id='checkButts' style='display: none;'>
				<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
				<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>	
			</DIV>
<?php

			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
			print get_user_group_butts(($_SESSION['user_id']));	//	6/10/11		
?>	
			</TD></TR>
<?php
			} else {
?>
			<TR CLASS='even' VALIGN="top">	<!--  4/12/11 -->
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
			<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
			<DIV id='checkButts' style='display: none;'>
				<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
				<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>	
			</DIV>
<?php
			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
			print get_user_group_butts_readonly($_SESSION['user_id']);	//	6/10/11		
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
		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Type - Select from pulldown menu"><?php print get_text("Type"); ?></A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN='left'><SELECT NAME='frm_type'><OPTION VALUE=0>Select one</OPTION>
<?php
	foreach ($u_types as $key => $value) {
		$temp = $value; 												// 2-element array
		print "\t\t\t\t<OPTION VALUE='" . $key . "'>" .$temp[0] . "</OPTION>\n";
		}
?>
			</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<A HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked /></TD>
			</TR>

		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Status - Select from pulldown menu"><?php print get_text("Status"); ?></A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN ='left'><SELECT NAME="frm_status_id" onChange = "document.res_add_Form.frm_log_it.value='1'">
				<OPTION VALUE=0 SELECTED>Select one</OPTION>
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		if ($the_grp != $row_st['group']) {
			print ($i == 0)? "": "\t</OPTGROUP>\n";
			$the_grp = $row_st['group'];
			print "\t<OPTGROUP LABEL='$the_grp'>\n";
			}
		print "\t<OPTION VALUE=' {$row_st['id']}'  CLASS='{$row_st['group']}' title='{$row_st['description']}'> {$row_st['status_val']} </OPTION>\n";
		$i++;
		}		// end while()
	print "\n</OPTGROUP>\n";
	unset($result_st);
?>
			</SELECT>
			</TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="About facility status - information about particular status values for this unit">About Status</A></TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_status_about" VALUE="" MAXLENGTH="512"></TD></TR>	<!-- 8/28/13 -->
		<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>	
		<TR CLASS='odd'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location - type in location in fields or click location on map "><?php print get_text("Location"); ?></A>:</TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='even'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.res_add_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD> <!-- 7/5/10 -->
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)"> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="<?php print $st_size;?>"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3 ><TEXTAREA NAME="frm_descr" COLS=60 ROWS=2></TEXTAREA></TD></TR>

		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility beds "><?php print get_text("Beds"); ?></A>&nbsp;</TD>
			<TD COLSPAN=3 >		<!-- 	6/4/2013 -->
			<SPAN  CLASS = "td_label" STYLE = "margin-left:20px;">Available: </SPAN><INPUT SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_a" VALUE="" />			
			<SPAN  CLASS = "td_label" STYLE = "margin-left:20px;">Occupied: </SPAN><INPUT SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_o" VALUE="" />			
			</TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Beds information"><?php print get_text("Beds"); ?> information</A>:&nbsp;</TD>
			<TD COLSPAN=3 ><TEXTAREA NAME="frm_beds_info" COLS=60 ROWS=2></TEXTAREA>			
			</TD></TR>

		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution"><?php print get_text("Capability"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_capab" COLS=60 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility main contact name"><?php print get_text("Contact name"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact email - main contact email address"><?php print get_text("Contact email"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact phone number - main contact phone number"><?php print get_text("Contact phone"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact"><?php print get_text("Security contact"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact email"><?php print get_text("Security email"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact phone number"><?php print get_text("Security phone"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc."><?php print get_text("Opening hours"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_opening_hours" COLS=60 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc"><?php print get_text("Access rules"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_access_rules" COLS=60 ROWS=5></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility securtiy requirements - e.g. phone security first, visitors must be security cleared etc."><?php print get_text("Security reqs"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_security_reqs" COLS=60 ROWS=5></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact primary pager number"><?php print get_text("Primary pager"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact secondary pager number"><?php print get_text("Secondary pager"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="" /></TD></TR>
<?php
		$mg_select = "<SELECT NAME='frm_notify_mailgroup'>";
		$mg_select .= "<OPTION VALUE=0>Select Mail List</OPTION>";
		$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup`";		// 12/18/10
		$result_mg = mysql_query($query_mg) or do_error($query_mg, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		while ($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {
			$mg_select .= "<OPTION VALUE=" . $row_mg['id'] . ">" . $row_mg['name'] . "</OPTION>";
			}
		$mg_select .= "</SELECT>";
?>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Notify Facility with selected mail list"><?php print get_text("Notify Mail List"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><?php print $mg_select;?></TD></TR>	<!-- 8/28/13 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Notify Facility with this email address"><?php print get_text("Notify Email Address"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="128" TYPE="text" NAME="frm_notify_email" VALUE="" /></TD></TR>	<!-- 8/28/13 -->
<!-- 9/10/13 -->
		<TR class='spacer'>
			<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
		</TR>
		<TR class='spacer'>
			<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
		</TR>
		<TR class='heading'>
			<TD COLSPAN='4' class='heading' style='text-align: center;'>File Upload</TD>
		</TR>
		<TR class='even'>
			<TD class='td_label' style='text-align: left;'>Choose a file to upload:</TD>
			<TD COLSPAN='3' class='td_data' style='text-align: left;'><INPUT NAME="frm_file" TYPE="file" /></TD>
		</TR>
		<TR class='odd'>
			<TD class='td_label' style='text-align: left;'>File Name</TD>
			<TD COLSPAN='3'  class='td_data' style='text-align: left;'><INPUT NAME="frm_file_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE=""></TD>
		</TR>
		<TR class='spacer'>
			<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
		</TR>
		<TR class='spacer'>
			<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
		</TR>
<!-- 9/10/13 -->
		<TR><TD COLSPAN=4 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD></TR>
		<TR CLASS = "even">
			<TD COLSPAN=4 ALIGN='center'>
			<INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="reset" VALUE="<?php print get_text("Reset"); ?>" onClick = "do_add_reset(this.form);">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>"  onClick="validate(document.res_add_Form);" >
			</TD>
		</TR>
		<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_direcs' VALUE=1 />  <!-- note default -->
		</FORM></TABLE> <!-- end inner left -->
		</TD></TR></TABLE><!-- end outer -->

		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- 1100 -->
		<A NAME="bottom" />
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>		
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($_getedit == 'true') {
		$id = $_GET['id'];
		$query	= "SELECT * FROM $GLOBALS[mysql_prefix]facilities WHERE id=$id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_assoc($result);
		$is_mobile = FALSE;

		$lat = $row['lat'];
		$lng = $row['lng'];
		$type = $row['type'];

		$type_checks = array ("", "", "", "", "");
		$type_checks[$row['type']] = " checked";
		$direcs_checked = (($row['direcs']==1))? " CHECKED" : "" ;

//		print do_calls($id);								// generate JS calls array
?>
		</HEAD>
		<BODY onLoad = "ck_frames(); get_files();">
		<A NAME='top'>		<!-- 11/11/09 -->
		<DIV ID='to_bottom' style="position:fixed; top: 2px; left: 50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0></div>
		<?php
		require_once('./incs/links.inc.php');
		?>
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER=0 ID='editform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit Facility '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>
		<FORM METHOD="POST" NAME= "res_edit_Form" ACTION="facilities_nm.php?func=responder&goedit=true">
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker">Name</A>:&nbsp;<font color='red' size='-1'>*</font></TD>			<TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility">Handle</A>:&nbsp;<font color='red' size='-1'>*</font></TD>			
			<TD COLSPAN=3><INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="<?php print $row['handle'] ;?>" />
				<SPAN STYLE = "margin-left:40px;" CLASS="td_label"  TITLE="A 3-letter value to be used in the map icon">Icon:</SPAN>&nbsp;<font color='red' size='-1'>*</font>
				<INPUT TYPE="text" SIZE = 3 MAXLENGTH=3 NAME="frm_icon_str" VALUE="<?php print $row['icon_str'];?>" />			
			</TD></TR>

<?php
	if(get_num_groups()) {
		if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>			
			<TR CLASS='even' VALIGN='top'>
			<TD CLASS='td_label'><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Region');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php			
			$alloc_groups = implode(',', get_allocates(3, $id));	//	6/10/11
			print get_sub_group_butts(($_SESSION['user_id']), 3, $id) ;	//	6/10/11		
			print "</TD></TR>";		// 4/12/11
			} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11
?>
			<TR CLASS='even' VALIGN='top'>;
			<TD CLASS='td_label'><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Region');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php
			$alloc_groups = implode(',', get_allocates(3, $id));	//	6/10/11
			print get_sub_group_butts(($_SESSION['user_id']), 3, $id) ;	//	6/10/11	
			print "</TD></TR>";		// 4/12/11		
			} else {
?>
			<TR CLASS='even' VALIGN='top'>;
			<TD CLASS='td_label'><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text('Region');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php
			$alloc_groups = implode(',', get_allocates(3, $id));	//	6/10/11	
			print get_sub_group_butts_readonly(($_SESSION['user_id']), 3, $id) ;	//	4/
			print "</TD></TR>";		// 4/12/11				
			}
		} else {
?>
		<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 2/8/12 -->
<?php
		}
?>
		<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>
		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Type - Select from pulldown menu">Type</A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN='left'><FONT SIZE='-2'>
				<SELECT NAME='frm_type'>
<?php
	foreach ($u_types as $key => $value) {
		$temp = $value; 												// 2-element array
		$sel = ($row['type']==$key)? " SELECTED": "";
		print "\t\t\t\t<OPTION VALUE='{$key}'{$sel}>{$temp[0]}</OPTION>\n";
		}
?>
				</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<A CLASS="td_label" HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked /></TD>
				
		</TD>
		</TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Status - Select from pulldown menu">Status</A>:&nbsp;</TD>
			<TD ALIGN='left'><SELECT NAME="frm_status_id" onChange = "document.res_edit_Form.frm_log_it.value='1'">
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `status_val` ASC, `group` ASC, `sort` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		if ($the_grp != $row_st['group']) {
			print ($i == 0)? "": "</OPTGROUP>\n";
			$the_grp = $row_st['group'];
			print "\t\t<OPTGROUP LABEL='$the_grp'>\n";
			}
		$sel = ($row['status_id']== $row_st['id'])? " SELECTED" : "";
		print "\t\t<OPTION VALUE=" . $row_st['id'] . $sel .">" . $row_st['status_val']. "</OPTION>\n";
		$i++;
		}
	print "\n\t\t</SELECT>\n";
	unset($result_st);

	$dis_rmv = " ENABLED";
?>
			</TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="About facility status - information about particular status values for this facility">About Status</A></TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_status_about" VALUE="<?php print $row['status_about'] ;?>"  MAXLENGTH="512"></TD></TR>	<!-- 8/28/13 -->
		<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>	
		<TR CLASS='odd'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:</TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='even'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.res_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD> <!-- 7/5/10 -->
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)"> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3><TEXTAREA NAME="frm_descr" COLS=60 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>

					<!-- 6/4/2013  -->
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility beds "><?php print get_text("Beds"); ?></A>&nbsp;</TD>
			<TD COLSPAN=3 >		<!-- 	6/4/2013 -->
			<SPAN  CLASS = "td_label" STYLE = "margin-left:20px;">Available: </SPAN><INPUT SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_a" VALUE="<?php print $row['beds_a'];?>" />			
			<SPAN  CLASS = "td_label" STYLE = "margin-left:20px;">Occupied: </SPAN><INPUT SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_o" VALUE="<?php print $row['beds_o'];?>" />			
			</TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Beds information"><?php print get_text("Beds"); ?> information</A>:&nbsp;</TD>
			<TD COLSPAN=3 ><TEXTAREA NAME="frm_beds_info" COLS=60 ROWS=2><?php print $row['beds_info'];?></TEXTAREA>			
			</TD></TR>


		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution"><?php print get_text("Capability"); ?></A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_capab" COLS=60 ROWS=2><?php print $row['capab'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility main contact name">Contact name</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact email - main contact email address"><?php print get_text("Contact email"); ?></A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="<?php print $row['contact_email'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact phone number - main contact phone number">Contact phone</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="<?php print $row['contact_phone'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact">Security contact</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="<?php print $row['security_contact'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact email">Security email</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="<?php print $row['security_email'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact phone number">Security phone</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="<?php print $row['security_phone'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc.">Opening hours</A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_opening_hours" COLS=60 ROWS=2><?php print $row['opening_hours'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc"><?php print get_text("Access rules"); ?></A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_access_rules" COLS=60 ROWS=5><?php print $row['access_rules'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility securtiy requirements - e.g. phone security first, visitors must be security cleared etc.">Security reqs</A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_security_reqs" COLS=60 ROWS=5><?php print $row['security_reqs'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact primary pager number">Pager Primary</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="<?php print $row['pager_p'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact secondary pager number">Pager Secondary</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="<?php print $row['pager_s'] ;?>" /></TD></TR>
<?php
		$mg_select = "<SELECT NAME='frm_notify_mailgroup'>";
		$mg_select .= "<OPTION VALUE=0>Select Mail List</OPTION>";
		$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` ORDER BY `id` ASC";
		$result_mg = mysql_query($query_mg) or do_error($query_mg, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		while ($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {
			$sel = ($row['notify_mailgroup'] == $row_mg['id']) ? "SELECTED" : "";
			$mg_select .= "\t<OPTION {$sel} VALUE='{$row_mg['id']}'>{$row_mg['name']} </OPTION>\n";
			}
		$mg_select .= "</SELECT>";
?>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Notify Facility with selected mail list"><?php print get_text("Notify Mail List"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><?php print $mg_select;?></TD></TR>	<!-- 8/28/13 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Notify Facility with this email address"><?php print get_text("Notify Email Address"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="128" TYPE="text" NAME="frm_notify_email" VALUE="<?php print $row['notify_email'] ;?>" /></TD></TR>	<!-- 8/28/13 -->
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="even" VALIGN='baseline'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Delete Facility from system">Remove Facility</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
		</TD></TR>
		<TR CLASS = "odd">
			<TD COLSPAN=4 ALIGN='center'><BR>
			<TD COLSPAN=4 ALIGN='center'><BR><INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <!-- 11/27/09 -->
				<INPUT TYPE="reset" VALUE="<?php print get_text("Reset"); ?>" onClick="map_reset()";>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>" onClick="validate(document.res_edit_Form);"></TD></TR>
				</TD></TR>

		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
		<INPUT TYPE="hidden" NAME="frm_exist_groups" VALUE="<?php print (isset($alloc_groups)) ? $alloc_groups : 1;?>">	<!-- 2/8/12 -->		
		</FORM></TABLE>
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		
			<!-- 9/10/13 File List -->
		<SPAN id='s_fl' class='plain' style='position: fixed; top: 10px; right: 0px; height: 20px; width: 100px; font-size: 1.2em; float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="$('file_list').style.display= 'block'; $('s_fl').style.display='none'; $('h_fl').style.display='inline-block';">Files</SPAN>
		<DIV id='file_list' style='position: fixed; right: 10px; top: 10px; width: 400px; height: 600px; border: 2px outset #707070; text-align: center; display: none;'>
			<SPAN id='h_fl' class='plain' style='float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="$('file_list').style.display= 'none'; $('h_fl').style.display='none'; $('s_fl').style.display='inline-block';">Hide</SPAN>
			<DIV class='heading' style='text-align: center;'>FILE LIST</DIV><BR /><CENTER>
			<SPAN id='nf_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='file_window(<?php print $id;?>);'>Add file</SPAN><BR /><BR />
			<DIV id='the_file_list' style='width: 100%; height: 100%; overflow-y: auto; text-align: left;'></DIV>
		</DIV>
			<!-- 9/10/13 File List -->
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>		
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['edit'])
// view =================================================================================================================
// view =================================================================================================================
// view =================================================================================================================

		if ($_getview == 'true') {
		
			$query_fa = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 3 AND `resource_id` = '$_GET[id]' ORDER BY `id` ASC;";	// 6/10/11
			$result_fa = mysql_query($query_fa);	// 6/10/11
			$fa_groups = array();
			$fa_names = "";	
			while ($row_fa = stripslashes_deep(mysql_fetch_assoc($result_fa))) 	{	// 6/10/11
				$fa_groups[] = $row_fa['group'];
				$query_fa2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row_fa[group]';";	// 6/10/11
				$result_fa2 = mysql_query($query_fa2);	// 6/10/11
				while ($row_fa2 = stripslashes_deep(mysql_fetch_assoc($result_fa2))) 	{	// 6/10/11		
					$fa_names .= $row_fa2['group_name'] . " ";
					}
				}
				
			$id = $_GET['id'];
			$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`=$id LIMIT 1";

			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= stripslashes_deep(mysql_fetch_assoc($result));
			$lat = $row['lat'];
			$lng = $row['lng'];

			if (isset($row['status_id'])) {
				$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` WHERE `id`=" . $row['status_id'];	// status value
				$result_st	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$row_st	= mysql_fetch_assoc($result_st);
				unset($result_st);
				}
			$un_st_val = (isset($row['status_id']))? $row_st['status_val'] : "?";
			$type_checks = array ("", "", "", "", "", "");
			$type_checks[$row['type']] = " checked";
			$coords =  $row['lat'] . "," . $row['lng'];		// for UTM

		$direcs_checked = (!empty($row['direcs']))? " checked" : "" ;

?>
		</HEAD><!-- 1387 -->
		<BODY onLoad = "ck_frames(); get_files();">
		<A NAME='top'>		<!-- 11/11/09 -->
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></div>
<?php
		print "\t<BODY onLoad = 'ck_frames()'>\n";
		print "<A NAME='top'>\n";			// 11/11/09
		require_once('./incs/links.inc.php');

		$temp = $u_types[$row['type']];
		$the_type = $temp[0];			// name of type

?>
			<FONT CLASS="header">&nbsp;'<?php print $row['name'] ;?>' Data</FONT> (#<?php print$row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER=0 ID='view_unit' STYLE='display: block'>
			<FORM METHOD="POST" NAME= "res_view_Form" ACTION="facilities_nm.php?func=responder">
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Name"); ?>: </TD>			<TD><?php print $row['name'];?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label"><?php print get_text("Location"); ?>: </TD><TD><?php print $row['street'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = 'even'><TD CLASS="td_label"><?php print get_text("City"); ?>: &nbsp;&nbsp;&nbsp;&nbsp;</TD><TD><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Handle"); ?>: </TD>
				<TD><?php print $row['handle'];?>
				<SPAN STYLE = "margin-left:40px;" CLASS="td_label">Icon:</SPAN>&nbsp;<?php print $row['icon_str'];?>
				</TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Regions: </TD>			<TD><?php print $fa_names;?></TD></TR><!-- 6/10/11 -->	
			<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>			
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Type"); ?>: </TD>
				<TD><?php print $the_type;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Status"); ?>:</TD><TD><?php print $un_st_val;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">About Status</TD>  <TD><?php print $row['status_about'] ;?></TD></TR>	<!-- 8/28/13 -->
			<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Description"); ?>: </TD>	<TD><?php print $row['description'];?></TD></TR>

					<!-- 6/4/2013  -->
			<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility beds "><?php print get_text("Beds"); ?> Available/Occupied:</A>&nbsp;</TD>
				<TD><?php print $row['beds_a'];?>/<?php print $row['beds_o'];?>	</TD></TR><!-- 	6/4/2013 -->
			<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Beds information"><?php print get_text("Beds"); ?> information</A>:&nbsp;</TD>
				<TD><?php print $row['beds_info'];?></TD></TR>
	
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Capability"); ?>: </TD>	<TD><?php print $row['capab'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Contact name"); ?>:</TD>	<TD><?php print $row['contact_name'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Contact email"); ?>:</TD>	<TD><?php print $row['contact_email'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Contact phone"); ?>:</TD>	<TD><?php print $row['contact_phone'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Security contact"); ?>:</TD>	<TD><?php print $row['security_contact'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Security email"); ?>:</TD>	<TD><?php print $row['security_email'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Security phone"); ?>:</TD>	<TD><?php print $row['security_phone'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Opening hours"); ?>:</TD>	<TD><?php print $row['opening_hours'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Access rules"); ?>:</TD>	<TD><?php print $row['access_rules'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Security reqs"); ?>:</TD>	<TD><?php print $row['security_reqs'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Primary pager"); ?>:</TD>	<TD><?php print $row['pager_p'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Secondary pager"); ?>:</TD>	<TD><?php print $row['pager_s'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Notify Mail List"); ?>:</TD>	<TD><?php print get_mailgroup_name($row['notify_mailgroup']);?></TD></TR>	<!-- 8/28/13 -->
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Notify Email Address"); ?>:</TD>	<TD><?php print $row['notify_email'];?></TD></TR>	<!-- 8/28/13 -->
			<TR CLASS = 'odd'><TD CLASS="td_label">As of:</TD>	<TD><?php print do_updated($row['updated']); ?></TD></TR>
<?php
		$toedit = (is_administrator() || is_super())? "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;": "" ;
?>
			<TR><TD>&nbsp;</TD></TR>
<?php
		if (is_administrator() || is_super()) {
?>
			<TR CLASS = "even"><TD COLSPAN=2 ALIGN='center'>
			<INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="to Edit" 	onClick= "to_edit_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>" />
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>" />
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			</TD></TR>
<?php
			}		// end if (is_administrator() || is_super())
		print "</FORM></TABLE>\n";
?>
			<BR /><BR /><BR />
			</TD></TR></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "facilities_nm.php?func=responder&edit=true&id=<?php print $id; ?>"></FORM>
			<INPUT TYPE="hidden" NAME="fac_id" 	VALUE="">						<!-- 10/16/08 -->
			<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
			</FORM>
							<!-- END FACILITY VIEW -->
							
			<!-- 9/10/13 File List -->
			<SPAN id='s_fl' class='plain' style='position: fixed; top: 10px; right: 0px; height: 20px; width: 100px; font-size: 1.2em; float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="$('file_list').style.display= 'block'; $('s_fl').style.display='none'; $('h_fl').style.display='inline-block';">Files</SPAN>
			<DIV id='file_list' style='position: fixed; right: 10px; top: 10px; width: 400px; height: 600px; border: 2px outset #707070; text-align: center; display: none;'>
				<SPAN id='h_fl' class='plain' style='float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="$('file_list').style.display= 'none'; $('h_fl').style.display='none'; $('s_fl').style.display='inline-block';">Hide</SPAN>
				<DIV class='heading' style='text-align: center;'>FILE LIST</DIV><BR /><CENTER>
				<SPAN id='nf_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='file_window(<?php print $id;?>);'>Add file</SPAN><BR /><BR />
				<DIV id='the_file_list' style='width: 100%; height: 100%; overflow-y: auto; text-align: left;'></DIV>
			</DIV>
			<!-- 9/10/13 File List -->
			
			<A NAME="bottom" /> <!-- 5/3/10 -->
			<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
			</BODY>
			</HTML>
<?php
			exit();
			}		// end if ($_GET['view'])
// ============================================= initial display =======================

		$do_list_and_map = TRUE;

		print "<SPAN STYLE = 'margin-left:100px;'>{$caption}<SPAN>";
?>
		</HEAD><!-- 1387 -->
		<BODY onLoad = "ck_frames(); set_regions_control();" >
		<A NAME='top'>		<!-- 11/11/09 -->
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></div>		
		<?php
		require_once('./incs/links.inc.php');
		$required = 250 + (mysql_affected_rows()*40);;
		$the_height = (integer)  min (round($facs_side_bar_height * $_SESSION['scr_height']), $required );		// set the max
		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
		$regions_inuse = get_regions_inuse($user_level);	//	6/10/11
		$group = get_regions_inuse_numbers($user_level);	//	6/10/11		
		
		$al_groups = $_SESSION['user_groups'];
			
		if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
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
			
		$heading = "Facilities - " . get_variable('map_caption');	//	6/10/11
		$the_height = (integer)  min (round($facs_side_bar_height * $_SESSION['scr_height']), $required );		// set the max	
		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]facilities`";		// 12/17/08
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		unset($result);		
?>
			<DIV id='top_reg_box' style='display: none;'>
				<DIV id='region_boxes' class='header_reverse' style='align: center; width: 100%; text-align: center; margin-left: auto; margin-right: auto; height: 30px; z-index: 1;'></DIV>
			</DIV>
			<DIV style='z-index: 1;'>		
			<TABLE ID='outer' style='width: 100%;'>
			<TR CLASS='header'><TD ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'><?php print $heading; ?> </FONT></TD></TR>	<!-- 4/11/11 -->
			<TR CLASS='spacer'><TD CLASS='spacer' ALIGN='center'>&nbsp;</TD></TR>				<!-- 4/11/11 -->
			<TR><TD ALIGN='left' width='50%'>
			<TABLE ID = 'sidebar' BORDER = 0 style='text-align: left;'>
				<TR class='even'>	<TD ALIGN='center'><B>Facilities (<DIV id="num_facilities" style="display: inline;"></DIV>)</B></TD></TR>
				<TR class='odd'>	<TD ALIGN='center'>Click line or icon for details</TD></TR>			
				<TR><TD>
				<DIV ID='side_bar' style="height: auto;  overflow-y: scroll; overflow-x: hidden; align: center;"></DIV></TD></TR>
				<TR class='spacer'><TD class='spacer'>&nbsp;</TD></TR>
				<TR><TD ALIGN='center'><DIV style='width: 100%;'><?php print get_facilities_legend();?></DIV></TD></TR>
				<TR class='spacer'><TD class='spacer'>&nbsp;</TD></TR>
				<TR><TD ALIGN='center' COLSPAN=99><DIV ID='buttons' style="width: 100%; align: center;"></DIV></TD></TR>
			</TABLE>
<?php
		$from_right = 20;	//	5/3/11
		$from_top = 10;		//	5/3/11	
?>
			</DIV></TD></TR></TABLE></DIV>	<!-- end of outer -->
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
		<INPUT TYPE='hidden' NAME='view' VALUE='true'>
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>

		<FORM NAME='add_Form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
		<INPUT TYPE='hidden' NAME='add' VALUE='true'>
		</FORM>

		<FORM NAME='can_Form' METHOD="post" ACTION = "facilities_nm.php?func=responder"></FORM>
		<!-- <?php print __LINE__; ?> -->
<?php
		$buttons = "<TR><TD COLSPAN=5 ALIGN='center'><BR />";
		if ((!(is_guest())) && (!(is_unit()))) {		// 7/27/10
			$buttons .="<INPUT TYPE='button' value= 'Add a Facility'  onClick ='document.add_Form.submit();'>";
			}
		if (may_email()) {
			$buttons .= "<INPUT TYPE = 'button' onClick = 'do_mail_win()' VALUE='Email facilities'  style = 'margin-left:20px'>";	// 6/13/09
			}
		$buttons .= "</TD></TR>";
		print list_facilities($buttons, 0);				// ($addon = '', $start)
?>
		</TD></TR></TABLE></DIV>
		<A NAME="bottom" />
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>		
		</BODY>				<!-- END FACILITIES LIST and ADD -->
		</HTML>
<?php
		exit();
?>