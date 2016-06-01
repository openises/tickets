<?php
/*
09/02/2015 - New File
*/

$from_top = 20;				// buttons alignment, user-reviseable as needed
$from_left = 300;

error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
if($istest) {
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}

function get_ticket_id () {				// 5/4/11
	if (array_key_exists('ticket_id', ($_REQUEST))) {
		@session_start();
		$_SESSION['active_ticket'] = $_REQUEST['ticket_id'];
		session_write_close();
		return (integer) $_REQUEST['ticket_id'];
		}
	elseif (array_key_exists('active_ticket', $_SESSION)) {
		return (integer) $_SESSION['active_ticket'];	
		}
	else {
		echo "error at "	 . __LINE__;
		}								// end if/else
	}				// end function	

function isempty($arg) {
	return (bool) (strlen($arg) == 0) ;
	}
	
function fac_cat($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` WHERE `id` = " . $id;	// all dispatches this unit
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = stripslashes_deep(mysql_fetch_array($result));
	return $row['name'];
	}
	
function get_day() {
	$timestamp = (time() - (intval(get_variable('delta_mins'))*60));
	if(strftime("%w",$timestamp)==0) {$timestamp = $timestamp + 86400;}
	return strftime("%A",$timestamp);
	}
	

function valid_status($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		return true;
		} else {
		return false;
		}
	}	

$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;	
$conversion = get_dist_factor();				// KM vs mi - 11/23/10
$_GET = stripslashes_deep($_GET);
$eol = "< br />\n";
$fac_order_values = array(1 => "`handle`,`fac_type_name` ASC", 2 => "`fac_type_name`,`handle` ASC",  3 => "`fac_status_val`,`fac_type_name` ASC");		// 3/15/11
if (array_key_exists ('forder' , $_POST))	{$_SESSION['fac_flag_2'] =  $_POST['forder'];}		// 3/15/11
elseif (empty ($_SESSION['fac_flag_2'])) 	{$_SESSION['fac_flag_2'] = 2;}		// 3/15/11

$status_vals = array();											// build array of $status_vals
$status_vals[''] = $status_vals['0']="TBD";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	}

$fac_order_str = $fac_order_values[$_SESSION['fac_flag_2']];		// 3/15/11	

$f_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$f_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);	

$icons = $GLOBALS['icons'];	
$sm_icons = $GLOBALS['sm_icons'];
$fac_icons = $GLOBALS['fac_icons'];

function get_unit_name($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret = $row['name'];
		} else {
		$ret = "Unk?";
		}
	return $ret;
	}

function get_unit_status($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret = $row['un_status_id'];
		} else {
		$ret = false;
		}
	return $ret;
	}
	
function get_fac_name($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret = $row['name'];
		} else {
		$ret = "Unk?";
		}
	return $ret;
	}

function get_unit_coords($id) {
	$ret_array = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr[0] = $row['lat'];
		$ret_arr[1] = $row['lng'];
		} else {
		$ret_arr[0] = 0.999999;
		$ret_arr[1] = 0.999999;
		}
	return $ret_arr;
	}

function get_fac_coords($id) {
	$ret_array = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr[0] = $row['lat'];
		$ret_arr[1] = $row['lng'];
		} else {
		$ret_arr[0] = 0.999999;
		$ret_arr[1] = 0.999999;
		}
	return $ret_arr;
	}

function get_icon_legend (){
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$type_data = $u_types[$row['type']];
		$print .= "\t\t" .$type_data[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$type_data[1]] . "' BORDER=0>&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()
	
function do_fac($id) {
	$query = "SELECT *,`$GLOBALS[mysql_prefix]facilities`.`updated` AS `updated`, 
		`$GLOBALS[mysql_prefix]facilities`.`id` 						AS `fac_id`, 
		`$GLOBALS[mysql_prefix]facilities`.`name` 						AS `fac_name`, 
		`$GLOBALS[mysql_prefix]fac_types`.`id` 							AS `type_id`,
		`$GLOBALS[mysql_prefix]facilities`.`description` 				AS `facility_description`,
		`$GLOBALS[mysql_prefix]facilities`.`boundary` 					AS `boundary`,		
		`$GLOBALS[mysql_prefix]fac_types`.`name` 						AS `fac_type_name`, 
		`$GLOBALS[mysql_prefix]fac_types`.`icon` 						AS `icon`, 
		`$GLOBALS[mysql_prefix]facilities`.`name` 						AS `facility_name`, 
		`$GLOBALS[mysql_prefix]fac_status`.`status_val` 				AS `fac_status_val`, 
		`$GLOBALS[mysql_prefix]facilities`.`status_id` 					AS `fac_status_id`
		FROM `$GLOBALS[mysql_prefix]facilities`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 	ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = 			`$GLOBALS[mysql_prefix]allocates`.`resource_id` )	
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` 	ON (`$GLOBALS[mysql_prefix]facilities`.`type` = 		`$GLOBALS[mysql_prefix]fac_types`.`id` )
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` 	ON (`$GLOBALS[mysql_prefix]facilities`.`status_id` = 	`$GLOBALS[mysql_prefix]fac_status`.`id` )
		WHERE `$GLOBALS[mysql_prefix]facilities`.`id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$print = "<TABLE BORDER='0'ID='left' width='60%'>\n";		//
		$print .= "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Description:</TD><TD CLASS='td_data'>" . $row['facility_description'] . "</TD></TR>\n";
		$print .= "<TR CLASS='even' VALIGN='top'><TD CLASS='td_label'>Capability:</TD><TD CLASS='td_data'>" . nl2br($row['capab']) . "</TD></TR>\n";
		$print .= "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Status:</TD><TD CLASS='td_data'>" . $row['status_val'] . "</TD></TR>\n";
		$print .= "<TR CLASS = 'even'><TD CLASS='td_label'><A CLASS='td_label' HREF='#' TITLE='Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc.'>Opening hours</A>:&nbsp;</TD>";
		$print .= "<TD><TABLE style='width: 100%;'><TR>";
		$print .= "<TH style='text-align: left;'><A CLASS='td_label' HREF='#' TITLE='Day of the Week'>" . get_text("Day") . "</A></TH>";
		$print .= "<TH style='text-align: left;'><A CLASS='td_label' HREF='#' TITLE='Opening Time'>" . get_text("Opening") . "</A></TH>";
		$print .= "<TH style='text-align: left;'><A CLASS='td_label' HREF='#' TITLE='Closing Time'>" . get_text("Closing") . "</A></TH>";
		$print .= "</TR>";
		$opening_arr_serial = base64_decode($row['opening_hours']);
		$opening_arr = unserialize($opening_arr_serial);
		$z=0;
		foreach($opening_arr as $val) {
			switch($z) {
				case 0:
				$dayname = "Monday";
				break;
				case 1:
				$dayname = "Tuesday";
				break;
				case 2:
				$dayname = "Wednesday";
				break;
				case 3:
				$dayname = "Thursday";
				break;
				case 4:
				$dayname = "Friday";
				break;
				case 5:
				$dayname = "Saturday";
				break;
				case 6:
				$dayname = "Sunday";
				break;
				}
			if($val[0] == "on") {
				$print .= "<TR>";
				$print .= "<TD style='text-align: left;'><SPAN CLASS='td_data'>" . $dayname . "</SPAN></TD>";
				$print .= "<TD style='text-align: left;'><SPAN CLASS='td_data'>" . $val[1] . "</SPAN></TD>";
				$print .= "<TD style='text-align: left;'><SPAN CLASS='td_data'>" . $val[2] . "</SPAN></TD>";
				$print .= "</TR>";
				}
			$z++;
			}
		$print .= "</TABLE>";
		$print .= "</TD>";			
		$print .= "</TR>";
		$print .= "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Access Rules:</TD><TD CLASS='td_data'>" . $row['access_rules'] . "</TD></TR>\n";
		$print .= "<TR CLASS='even' VALIGN='top'><TD CLASS='td_label'>Sec Reqs:</TD><TD CLASS='td_data'>" . $row['security_reqs'] . "</TD></TR>\n";
		$print .= "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Cont name:</TD><TD CLASS='td_data'>" . $row['contact_name'] . "</TD></TR>\n";
		$print .= "<TR CLASS='even' VALIGN='top'><TD CLASS='td_label'>Cont email:</TD><TD CLASS='td_data'>" . $row['contact_email'] . "</TD></TR>\n";
		$print .= "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Cont phone:</TD><TD CLASS='td_data'>" . $row['contact_phone'] . "</TD></TR>\n";
		$print .= "<TR CLASS='even' VALIGN='top'><TD CLASS='td_label'>Sec contact:</TD><TD CLASS='td_data'>" . $row['security_contact'] . "</TD></TR>\n";
		$print .= "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Sec email:</TD><TD CLASS='td_data'>" . $row['security_email'] . "</TD></TR>\n";
		$print .= "<TR CLASS='even' VALIGN='top'><TD CLASS='td_label'>Sec phone:</TD><TD CLASS='td_data'>" . $row['security_phone'] . "</TD></TR>\n";
		$print .= "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Prim pager:</TD><TD CLASS='td_data'>" . $row['pager_p'] . "</TD></TR>\n";
		$print .= "<TR CLASS='even' VALIGN='top'><TD CLASS='td_label'>Sec pager:</TD><TD CLASS='td_data'>" . $row['pager_s'] . "</TD></TR>\n";
		$print .= "<TR CLASS='odd' VALIGN='top'><TD CLASS='td_label'>Updated:</TD><TD CLASS='td_data'>" . format_date($row['updated']) . "</TD></TR>\n";
		$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $row['lat'] . "</SPAN><SPAN ID='oldlng'>" . $row['lng'] . "</SPAN></TD></TR>";
		$print .= "</TABLE>\n";
		return $print;
		} else {
		return "Error";
		}
	}		// end function do_fac(

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/leaflet/leaflet-routing-machine_2.css" />
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
		body 				{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
		table 				{border-collapse: collapse; }
		table.directions th {background-color:#EEEEEE;}	  
		img 				{color: #000000;}
		span.even 			{background-color: #DEE3E7;}
		span.warn			{display:none; background-color: #FF0000; color: #FFFFFF; font-weight: bold; font-family: Verdana, Arial, sans serif; }

		span.mylink			{margin-right: 32PX; text-decoration:underline; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_1		{margin-right: 32PX; text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_2		{margin-right: 8PX;  text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}

		.box { background-color: transparent; border: none; color: #000000; padding: 0px; position: absolute; }
		.bar { background-color: transparent; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em; }
		.bar_header { height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}			
		
		.box2 { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:10000; width: 180px; }
		.bar2 { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:10000; text-align: center;}
		.content { padding: 1em; text-align: center; }
		table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
		table.cruises td {overflow: hidden; }
		div.scrollableContainer { position: relative; padding-top: 1.5em; border: 1px solid #999; }
		div.scrollableContainer2 { position: relative; padding-top: 1.3em; }
		div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
		div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
		table.scrollable thead tr { position: absolute; left: -1px; top: 0px; }
		table.cruises th { text-align: left; border-left: 1px solid #999; background: #CECECE; color: black; font-weight: bold; overflow: hidden; }
		div.tabBox {}
		div.tabArea { font-size: 80%; font-weight: bold; padding: 0px 0px 3px 0px; }
		span.tab { background-color: #CECECE; color: #8060b0; border: 2px solid #000000; border-bottom-width: 0px; -moz-border-radius: .75em .75em 0em 0em;	border-radius-topleft: .75em; border-radius-topright: .75em;
				padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px; z-index: 100; }
		span.tabinuse {	background-color: #FFFFFF; color: #000000; border: 2px solid #000000; border-bottom-width: 0px;	border-color: #f0d0ff #b090e0 #b090e0 #f0d0ff; -moz-border-radius: .75em .75em 0em 0em;
				border-radius-topleft: .75em; border-radius-topright: .75em; padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px;	z-index: 100;}
		span.tab:hover { background-color: #FEFEFE; border-color: #c0a0f0 #8060b0 #8060b0 #c0a0f0; color: #ffe0ff;}
		div.content { font-size: 80%; background-color: #F0F0F0; border: 2px outset #707070; -moz-border-radius: 0em .5em .5em 0em;	border-radius-topright: .5em; border-radius-bottomright: .5em; padding: .5em;
				position: relative;	z-index: 101; cursor: normal; height: 300px;}
		div.contentwrapper { width: 260px; background-color: #F0F0F0; cursor: normal;}
		#directions { background-color: white;}
		#fac_table { overflow-y: auto; }
	</STYLE>
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="text/javascript" SRC="./js/json2.js"></SCRIPT>
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT>
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/leaflet/leaflet-routing-machine_2.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/osopenspace.js"></script>
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
	<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
	<script src="./js/Google.js"></script>
	<script type="text/javascript" src="./js/osm_map_functions.js.php"></script>
	<script type="text/javascript" src="./js/L.Graticule.js"></script>
	<script type="text/javascript" src="./js/leaflet-providers.js"></script>
	<script type="text/javascript" src="./js/usng.js"></script>
	<script type="text/javascript" src="./js/osgb.js"></script>
	<script type="text/javascript" src="./js/geotools2.js"></script>
<SCRIPT>
var map;
var minimap;
var thelevel = '<?php print $the_level;?>';
var fmarkers = [];			//	Facilities Markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var latLng;
var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
	}
	});
var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
	}
	});
var fac_icons=[];
fac_icons[0] = 1;
fac_icons[1] = 2;
fac_icons[2] = 3;
fac_icons[3] = 4;	
fac_icons[4] = 5;
fac_icons[5] = 6;
fac_icons[6] = 7;
fac_icons[7] = 8;
			
var colors = new Array ('odd', 'even');

try {	
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
	}
catch(e) {
	}

function isNull(arg) {
	return arg===null;
	}
</SCRIPT>	
<script type="text/javascript">//<![CDATA[
//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************
// Determine browser and version.
function Browser() {
	var ua, s, i;
	this.isIE		= false;
	this.isNS		= false;
	this.version = null;
	ua = navigator.userAgent;
	s = "MSIE";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isIE = true;
		this.version = parseFloat(ua.substr(i + s.length));
		return;
		}
	s = "Netscape6/";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isNS = true;
		this.version = parseFloat(ua.substr(i + s.length));
		return;
		}
	// Treat any other "Gecko" browser as NS 6.1.
	s = "Gecko";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isNS = true;
		this.version = 6.1;
		return;
		}
	}
var browser = new Browser();
var dragObj = new Object();		// Global object to hold drag information.
dragObj.zIndex = 0;
function dragStart(event, id) {
	var el;
	var x, y;
	if (id)										// If an element id was given, find it. Otherwise use the element being
		dragObj.elNode = document.getElementById(id);	// clicked on.
	else {
		if (browser.isIE)
			dragObj.elNode = window.event.srcElement;
		if (browser.isNS)
			dragObj.elNode = event.target;
		if (dragObj.elNode.nodeType == 3)		// If this is a text node, use its parent element.
			dragObj.elNode = dragObj.elNode.parentNode;
		}
	if (browser.isIE) {			// Get cursor position with respect to the page.
		x = window.event.clientX + document.documentElement.scrollLeft
			+ document.body.scrollLeft;
		y = window.event.clientY + document.documentElement.scrollTop
			+ document.body.scrollTop;
		}
	if (browser.isNS) {
		x = event.clientX + window.scrollX;
		y = event.clientY + window.scrollY;
		}
	dragObj.cursorStartX = x;		// Save starting positions of cursor and element.
	dragObj.cursorStartY = y;
	dragObj.elStartLeft	= parseInt(dragObj.elNode.style.left, 10);
	dragObj.elStartTop	 = parseInt(dragObj.elNode.style.top,	10);
	if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
	if (isNaN(dragObj.elStartTop))	dragObj.elStartTop	= 0;
	dragObj.elNode.style.zIndex = ++dragObj.zIndex;		// Update element's z-index.
	if (browser.isIE) {									// Capture mousemove and mouseup events on the page.
		document.attachEvent("onmousemove", dragGo);
		document.attachEvent("onmouseup",	 dragStop);
		window.event.cancelBubble = true;
		window.event.returnValue = false;
		}
	if (browser.isNS) {
		document.addEventListener("mousemove", dragGo,	 true);
		document.addEventListener("mouseup",	 dragStop, true);
		event.preventDefault();
		}
	}
function dragGo(event) {
	var x, y;
	if (browser.isIE) {	// Get cursor position with respect to the page.
		x = window.event.clientX + document.documentElement.scrollLeft
			+ document.body.scrollLeft;
		y = window.event.clientY + document.documentElement.scrollTop
			+ document.body.scrollTop;
		}
	if (browser.isNS) {
		x = event.clientX + window.scrollX;
		y = event.clientY + window.scrollY;
		}
	dragObj.elNode.style.left = (dragObj.elStartLeft + x - dragObj.cursorStartX) + "px";	// Move drag element by the same amount the cursor has moved.
	dragObj.elNode.style.top	= (dragObj.elStartTop	+ y - dragObj.cursorStartY) + "px";
	if (browser.isIE) {
		window.event.cancelBubble = true;
		window.event.returnValue = false;
		}
	if (browser.isNS)
		event.preventDefault();
	}
function dragStop(event) {
	if (browser.isIE) {	// Stop capturing mousemove and mouseup events.
		document.detachEvent("onmousemove", dragGo);
		document.detachEvent("onmouseup",	 dragStop);
		}
	if (browser.isNS) {
		document.removeEventListener("mousemove", dragGo,	 true);
		document.removeEventListener("mouseup",	 dragStop, true);
		}
	}
//]]></script>
<SCRIPT>
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
	
function checkForm(form) {
	var errmsg="";
	var itemsChecked = checkArray(form, "frm_group[]");
	if(itemsChecked.length > 0) {
		var params = "f_n=viewed_groups&v_n=" +itemsChecked+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
		var url = "persist3.php";	//	3/15/11	
		sendRequest (url, fvg_handleResult, params);				
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
	
function update_location(fac_id, unit_id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var status_val = $('frm_status_sel').value;
	var url = './ajax/update_responder_location.php?fac_id=' + fac_id + '&resp_id=' + unit_id + '&status=' + status_val + '&version=' + randomnumber+'&q='+sessID;
	sendRequest (url,updateRespCB, "");
	function updateRespCB(req) {
		var theResponse = JSON.decode(req.responseText);
		if(theResponse[0] == 1) {
			$('outer').style.display='none';
			$('finished').style.display='block';
			} else {
			alert("failed");	
			}
		}
	}
	
function form_validate(theForm) {	//	6/10/11
	checkForm(theForm);
	}				// end function validate(theForm)
	
function handleResult(req) {				// the 'called-back' function
	}

var starting = false;

function do_mail_win(addrs, fac_id) {	
	if(starting) {return;}					// dbl-click catcher
	starting=true;	
	var url = "mail_edit.php?fac_id=" + fac_id + "&addrs=" + addrs + "&text=";	// no text
	newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
	if (isNull(newwindow_mail)) {
		alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	newwindow_mail.focus();
	starting = false;
	}		// end function do mail_win()

<?php 
	if(get_variable('call_board')==2) {
		print "\n\tparent.top.calls.location.reload(true);\n";
		}
?>
parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

String.prototype.parseDeg = function() {
	if (!isNaN(this)) return Number(this);								// signed decimal degrees without NSEW
	
	var degLL = this.replace(/^-/,'').replace(/[NSEW]/i,'');			// strip off any sign or compass dir'n
	var dms = degLL.split(/[^0-9.,]+/);									// split out separate d/m/s
	for (var i in dms) if (dms[i]=='') dms.splice(i,1);					// remove empty elements (see note below)
	switch (dms.length) {												// convert to decimal degrees...
		case 3:															// interpret 3-part result as d/m/s
			var deg = dms[0]/1 + dms[1]/60 + dms[2]/3600; break;
		case 2:															// interpret 2-part result as d/m
			var deg = dms[0]/1 + dms[1]/60; break;
		case 1:															// decimal or non-separated dddmmss
			if (/[NS]/i.test(this)) degLL = '0' + degLL;	// - normalise N/S to 3-digit degrees
			var deg = dms[0].slice(0,3)/1 + dms[0].slice(3,5)/60 + dms[0].slice(5)/3600; break;
		default: return NaN;
		}
	if (/^-/.test(this) || /[WS]/i.test(this)) deg = -deg; // take '-', west and south as -ve
	return deg;
	}

Number.prototype.toRad = function() {  // convert degrees to radians
	return this * Math.PI / 180;
	}

Number.prototype.toDeg = function() {  // convert radians to degrees (signed)
	return this * 180 / Math.PI;
	}

Number.prototype.toBrng = function() {  // convert radians to degrees (as bearing: 0...360)
	return (this.toDeg()+360) % 360;
	}

function brng(lat1, lon1, lat2, lon2) {
	lat1 = lat1.toRad(); lat2 = lat2.toRad();
	var dLon = (lon2-lon1).toRad();

	var y = Math.sin(dLon) * Math.cos(lat2);
	var x = Math.cos(lat1)*Math.sin(lat2) -
					Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
	return Math.atan2(y, x).toBrng();
	}

distCosineLaw = function(lat1, lon1, lat2, lon2) {
	var R = 6371; // earth's mean radius in km
	var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
			Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
	return d;
	}
var km2feet = 3280.83;

function min(inArray) {				// returns index of least float value in inArray
	var minsofar =  40076.0;		// initialize to earth circumference (km)
	var j=-1;
	for (var i=0; i< inArray.length; i++){
		if (parseFloat(inArray[i]) < parseFloat(minsofar)) {
			j=i;
			minsofar=inArray[i];
			}
		}
	return j;
	}		// end function min()

function ck_frames() {		// onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();
		}
	}		// end function ck_frames()
function doReset() {
	document.reLoad_Form.submit();
	}	// end function doReset()

var starting = false;	

function go_there(id) {
	document.to_stage2.fac_id.value=id;			// 10/6/09
	document.to_stage2.submit();
	}	

</script>
<BODY>
<?php
if(array_key_exists('stage', $_GET) && $_GET['stage'] == 1 && array_key_exists('id', $_GET) && $_GET['id'] != 0) {		//	List Facilities
?>
	<DIV ID='outer' style='position: absolute; left: 0px; top: 0px; width: 100%; height: 98%; display: block;'>
		<DIV ID='leftcol' style='position: absolute; left: 20px; top: 20px; width: 45%; height: 98%; display: block;'>
			<SPAN>
<?php
				//	 user groups
				$al_groups = $_SESSION['user_groups'];
				
				if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13		
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

				$query_fac = "SELECT *,`$GLOBALS[mysql_prefix]facilities`.`updated` AS `updated`, 
					`$GLOBALS[mysql_prefix]facilities`.`id` 						AS `fac_id`, 
					`$GLOBALS[mysql_prefix]fac_types`.`id` 							AS `type_id`,
					`$GLOBALS[mysql_prefix]facilities`.`description` 				AS `facility_description`,
					`$GLOBALS[mysql_prefix]facilities`.`boundary` 					AS `boundary`,		
					`$GLOBALS[mysql_prefix]fac_types`.`name` 						AS `fac_type_name`, 
					`$GLOBALS[mysql_prefix]fac_types`.`icon` 						AS `icon`, 
					`$GLOBALS[mysql_prefix]facilities`.`name` 						AS `facility_name`, 
					`$GLOBALS[mysql_prefix]fac_status`.`status_val` 				AS `fac_status_val`, 
					`$GLOBALS[mysql_prefix]facilities`.`status_id` 					AS `fac_status_id`
					FROM `$GLOBALS[mysql_prefix]facilities`
					LEFT JOIN `$GLOBALS[mysql_prefix]allocates` 	ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = 			`$GLOBALS[mysql_prefix]allocates`.`resource_id` )	
					LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` 	ON (`$GLOBALS[mysql_prefix]facilities`.`type` = 		`$GLOBALS[mysql_prefix]fac_types`.`id` )
					LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` 	ON (`$GLOBALS[mysql_prefix]facilities`.`status_id` = 	`$GLOBALS[mysql_prefix]fac_status`.`id` )
					{$where2} 
					GROUP BY fac_id ORDER BY {$fac_order_str} ";											// 3/15/11, 6/10/11

				$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
				$facs_ct = mysql_affected_rows();			// 1/4/10
				if($facs_ct > 0) {
					$i = 0;
					$facs_arr = array();
					$class='even';
?>
					<SPAN CLASS='heading' style='width: 100%; display: inline-block;'>CHOSE FACILITY TO ROUTE TO</SPAN>
					<DIV id='facs_list' style='width: 100%; height: 98%; display: block; overflow-y: auto; overflow-x: auto;'>
						<TABLE style='width: 100%;'>
						<TR class='header'>
							<TD class='plain_listheader'>Icon</TD><TD class='plain_listheader'>Name</TD><TD class='plain_listheader'>Type</TD><TD class='plain_listheader'>Opening Times (Today)</TD>
						</TR>
<?php
						while($row_fac = mysql_fetch_assoc($result_fac)){		// 7/7/10
							$name = htmlentities($row_fac['facility_name'],ENT_QUOTES);
							$handle = htmlentities($row_fac['handle'],ENT_QUOTES);

							$fac_id=$row_fac['fac_id'];
							$fac_type=$row_fac['icon'];
							$fac_type_name = $row_fac['fac_type_name'];
							$fac_region = get_first_group(3, $fac_id);
							$fac_lat = $row_fac['lat'];
							$fac_lng = $row_fac['lng'];
							
							$fac_index = $row_fac['icon_str'];	

							$latitude = $row_fac['lat'];
							$longitude = $row_fac['lng'];

							$the_bg_color = ($GLOBALS['FACY_TYPES_BG'][$row_fac['icon']]) ? $GLOBALS['FACY_TYPES_BG'][$row_fac['icon']] : "#FFFFFF";
							$the_text_color = ($GLOBALS['FACY_TYPES_TEXT'][$row_fac['icon']]) ? $GLOBALS['FACY_TYPES_TEXT'][$row_fac['icon']] : "#000000";			

// STATUS
							$temp = $row_fac['status_id'] ;
							$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
// AS-OF - 11/3/2012
							$updated = format_sb_date_2 ( $row_fac['updated'] );
	
							if(!(isempty(trim($row_fac['opening_hours']))))  	{
								$opening_arr_serial = base64_decode($row_fac['opening_hours']);
								$opening_arr = unserialize($opening_arr_serial);
								$outputstring = "";
								$the_day = "";
								$z = 0;
								foreach($opening_arr as $val) {
									switch($z) {
										case 0:
										$dayname = "Monday";
										break;
										case 1:
										$dayname = "Tuesday";
										break;
										case 2:
										$dayname = "Wednesday";
										break;
										case 3:
										$dayname = "Thursday";
										break;
										case 4:
										$dayname = "Friday";
										break;
										case 5:
										$dayname = "Saturday";
										break;
										case 6:
										$dayname = "Sunday";
										break;
										}
									$openstring = ($dayname == get_day()) ? "Open" : "Closed";
									if($dayname == get_day()) {
										$the_day .= $dayname;
										$outputstring .= " Opens: " . $val[1] . " Closes: " . $val[2];
										}
									$z++;
									}
								$openingTimes = "(" . $the_day . ")  ---  " . $outputstring;
								}
							print "<TR CLASS='" . $class . "' style='width: 100%;' onCLick='go_there(" . $fac_id . ");'><TD CLASS='plain_list' style='background-color: " . $the_bg_color . "; color: " . $the_text_color . ";'>" . $fac_index . "</TD><TD CLASS='plain_list' style='background-color: " . $the_bg_color . "; color: " . $the_text_color . ";'>" . $name . "</TD><TD CLASS='plain_list'>" . $fac_type_name . "</TD><TD CLASS='plain_list'>" . $openingTimes . "</TD></TR>";
							$class = ($class == "even") ? "odd" : "even";
							$fac_stat = 0;
							$facs_arr[$i][0] = $fac_id;	//	theid
							$facs_arr[$i][1] = $fac_type;	//	color
							$facs_arr[$i][2] = $fac_stat;	//	stat
							$facs_arr[$i][3] = "";	//	info						
							$facs_arr[$i][4] = $fac_index;	//	sym	
							$facs_arr[$i][5] = $fac_type_name;	//	category
							$facs_arr[$i][6] = $fac_region;	//	region
							$facs_arr[$i][7] = $fac_type_name . ", " . $name;	//	tip		
							$facs_arr[$i][8] = $fac_lat;	//	lat
							$facs_arr[$i][9] = $fac_lng;	//	lon
							$i++;
							}
						print "</TABLE>";
						}
?>
					</DIV>
			</SPAN>
		</DIV>
		<DIV ID='rightcol' style='position: absolute; right: 100px; top: 20px; width: 45%; height: 98%; display: block;'>
		</DIV>
	</DIV>
	<FORM NAME="to_stage2" METHOD="get" ACTION = "fac_routes_nm.php">
	<INPUT TYPE="hidden" NAME="stage" 	VALUE=2>
	<INPUT TYPE="hidden" NAME="fac_id" 	VALUE="">
	<INPUT TYPE="hidden" NAME="unit_id" VALUE=<?php print $_GET['id'];?> />
	</FORM>
<?php	
	} elseif(array_key_exists('stage', $_GET) && $_GET['stage'] == 2 && array_key_exists('fac_id', $_GET) && $_GET['fac_id'] != 0) {		//	Route to Facility
	$facName = explode("/", get_fac_name($_GET['fac_id']));
	$respName = explode("/", get_unit_name($_GET['unit_id']));
?>
<SCRIPT>
	var facID = <?php print $_GET['fac_id'];?>;
	var respID = <?php print $_GET['unit_id'];?>;
</SCRIPT>
	<DIV ID='finished' style='display: none;'>
	<CENTER>		
	<BR /><BR /><BR /><H3>Responder <?php print $respName[0];?> Location Updated</H3>
	<BR /><BR /><BR /><SPAN ID='fin_but' class='plain' style='float: none;' onMouseover="do_hover(this.id);" onMouseout="do_plain(this.id);" onClick="document.forms['fin_form'].submit();" />Finished</SPAN><BR /><BR />
	</DIV>
	<DIV ID='outer' style='position: absolute; left: 0px; top: 0px; width: 100%; height: 98%; display: block;'>
		<DIV ID='leftcol' style='position: absolute; left: 0px; top: 0px; width: 45%; height: 98%; display: block;'>
			<SPAN CLASS='heading' style='width: 100%; display: block;'>Dispatching <?php print $respName[0];?> to <?php print $facName[0];?></SPAN>
			<DIV ID='fac_table'><?php print do_fac($_GET['fac_id']);?></DIV><BR /><BR />
			<DIV ID='map_canvas' style='height: 500px; width: 500px; display: block;'></DIV>
		</DIV>
		<DIV ID='middlecol' style='width: 10%;'>
		</DIV>
		<DIV ID='rightcol' style='position: absolute; right: 100px; top: 0px; width: 45%; height: 98%; display: block;'>
			<BR />
			<SPAN ID = 'disp_but' CLASS='plain' STYLE="display: block" onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('disp_but').style.display='none'; $('un_status').style.display='inline-block'; $('disp2_but').style.display='inline-block';" />Move Unit to Facility</SPAN>
			<SPAN ID = 'un_status' style='display: none;'>
				<SELECT ID="frm_status_sel" NAME="frm_un_status_id" onChange = "this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color;">
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
					$sel = (get_unit_status($_GET['unit_id'])== $row_st['id'])? " SELECTED" : "";
					print "\t\t<OPTION VALUE=" . $row_st['id'] . $sel ." STYLE='background-color:{$row_st['bg_color']}; color:{$row_st['text_color']};'  >" . $row_st['status_val']. "</OPTION>\n";	// 3/15/10
					$i++;
					}
?>
				</SELECT>
				<SPAN ID = 'disp2_but' CLASS='plain' STYLE="display: none; float: right;" onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="update_location(facID, respID);" />Submit Move</SPAN>
			</SPAN>
			<SPAN ID='can_but' class='plain' onMouseover="do_hover(this.id);" onMouseout="do_plain(this.id);" onClick="document.forms['can_form'].submit();" />Cancel</SPAN><BR /><BR /><BR /><BR />
		</DIV>

	</DIV>
	<FORM NAME="can_form" METHOD="get" ACTION = "new_fac_routes.php">
	<INPUT TYPE="hidden" NAME="stage" 	VALUE=1>
	<INPUT TYPE="hidden" NAME="id" VALUE=<?php print $_GET['unit_id'];?> />
	</FORM>
	<FORM NAME="fin_form" METHOD="get" ACTION = "main.php">
	</FORM>
<?php
	} else {
?>
	<DIV>Error</DIV>
<?php
	}
?>
</BODY>
</HTML>

	


