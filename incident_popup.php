<?php
/*
7/08/09 Created Incident Popup from track_u.php
7/29/09 Revised code for statistics display and background color determined by severity
3/12/10 added incident age to stats, revised display 
3/25/10 added 'dispatched' and 'cleared' to display
6/25/10 added year check to NULL for cleared assigns
7/4/10 added ticket details to head section
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/26/10 fmp added - AH
3/15/11 changed stylesheet.php to stylesheet.php
*/

error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);		// 8/26/10
$internet = ((isset($_SESSION['internet'])) && ($_SESSION['internet'] == true)) ? true: false;

if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {
	do_logout();
	exit();
	}
else {
	do_login(basename(__FILE__));
	}
if ($istest) {
	print "GET<BR/>\n";
	if (!empty($_GET)) {
		dump ($_GET);
		}
	print "POST<BR/>\n";
	if (!empty($_POST)) {
		dump ($_POST);
		}
	}
	
$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}
unset($result);

$f_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$f_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);	

$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

$status_vals = array();											// build array of $status_vals
$status_vals[''] = $status_vals['0']="TBD";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	$status_hide[$temp] = $row_st['hide'];
	}

unset($result_st);

$fac_status_vals = array();											// build array of $status_vals
$fac_status_vals[''] = $fac_status_vals['0']="TBD";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$fac_status_vals[$temp] = $row_st['status_val'];
	}

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
	
$assigns = array();					// 8/3/08
$tickets = array();					// ticket id's

$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, 
	`$GLOBALS[mysql_prefix]assigns`.`responder_id`, 
	`$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` 
	FROM `$GLOBALS[mysql_prefix]assigns` 
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";
$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
	$assigns[$row_as['responder_id']] = $row_as['ticket'];
	$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
	}
unset($result_as);

$temp = get_variable('auto_poll');
$poll_val = ($temp==0)? "none" : $temp ;
$ticket_id = (array_key_exists('id', ($_GET)))?	$_GET['id']  :	NULL;

$result = mysql_query("SELECT *,`problemstart` AS problemstart ,`problemend` AS problemend FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$ticket_id'");
$row = mysql_fetch_assoc($result);
$title = $row['scope'];
$ticket_severity = get_severity($row['severity']);
$ticket_type = get_type($row['in_types_id']);
$ticket_status = get_status($row['status']);
$ticket_updated = format_date_time($row['updated']);
$ticket_addr = "{$row['street']}, {$row['city']} {$row['state']} ";
$ticket_start = $row['problemstart'];		//
$ticket_end = $row['problemend'];		//
$ticket_start_str = format_date($row['problemstart']);		//
if($row['status'] == $GLOBALS['STATUS_CLOSED']) {
	$elapsed = my_date_diff($ticket_start, $ticket_end);
	} else {
	$elapsed = get_elapsed_time($row);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Incident Popup - Incident <?php print $title;?> <?php print $ticket_updated;?></TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		#regions_control { font-family: verdana, arial, helvetica, sans-serif; font-size: 5px; background-color: #FEFEFE; font-weight: bold;}
		table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
		table.cruises td {overflow: hidden; }
		div.scrollableContainer { position: relative; padding-top: 2em; border: 1px solid #999; }
		div.scrollableContainer2 { position: relative; padding-top: 2em; }
		div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
		div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
		table.scrollable thead tr { left: -1px; top: 0; position: absolute; }
		table.cruises th { text-align: left; border-left: 1px solid #999; background: #CECECE; color: black; font-weight: bold; overflow: hidden; }
		div.tabBox {}
		div.tabArea { font-size: 80%; font-weight: bold; padding: 0px 0px 3px 0px; }
		span.tab { background-color: #CECECE; color: #8060b0; border: 2px solid #000000; border-bottom-width: 0px; -moz-border-radius: .75em .75em 0em 0em;	border-radius-topleft: .75em; border-radius-topright: .75em;
				padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px; z-index: 100; }
		span.tabinuse {	background-color: #FFFFFF; color: #000000; border: 2px solid #000000; border-bottom-width: 0px;	border-color: #f0d0ff #b090e0 #b090e0 #f0d0ff; -moz-border-radius: .75em .75em 0em 0em;
				border-radius-topleft: .75em; border-radius-topright: .75em; padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px;	z-index: 100;}
		span.tab:hover { background-color: #FEFEFE; border-color: #c0a0f0 #8060b0 #8060b0 #c0a0f0; color: #ffe0ff;}
		div.content { font-size: 80%; background-color: #F0F0F0; border: 2px outset #707070; -moz-border-radius: 0em .5em .5em 0em;	border-radius-topright: .5em; border-radius-bottomright: .5em; padding: .5em;
				position: relative;	z-index: 101; cursor: normal; height: 250px;}
		div.contentwrapper { width: 260px; background-color: #F0F0F0; cursor: normal;}
	</STYLE>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT><!-- 10/23/12-->
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/OSOpenspace.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
<?php
	if ($_SESSION['internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
			if($https) {
?>
				<script src="https://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php
				} else {
?>
				<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php				
				}
			}
		}
?>
	<script type="application/x-javascript" src="./js/jss.js"></script>
	<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var tmarkers = [];	//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var fmarkers = [];			//	Responder Markers array
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var inner_listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var r_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = new Array();
var inc_sortby = "tick_id";		//	options tick_id, scope, ticket_street, type, updated
var inc_sortdir = "ASC";		// Initial sort direction ascending;
var inc_sortbyfield = "";
var inc_sortvalue = "";
var inc_period = 0;
var mapCenter;
var baseIcon = L.Icon.extend({options: {
	iconSize: [20, 32], 
	iconAnchor: [0, 0], 
	popupAnchor: [6, -5], 
	shadowUrl: './our_icons/shadow.png', 
	shadowRetinaUrl: './our_icons/shadow.png', 
	shadowSize: [20, 32], 
	shadowAnchor: [0, 0]
	}
	});

var colors = new Array ('odd', 'even');
var bounds;
var zoom;
var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [0, 0],	shadowAnchor: [5, -5], popupAnchor: [6, -5]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [0, 0], popupAnchor: [6, -5]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [0, 0], popupAnchor: [6, -5]
	}
	});

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
	mapWidth = viewportwidth * .95;
	mapHeight = viewportheight * .60;
	outerwidth = viewportwidth * .95;
	outerheight = viewportheight * .95;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('theTable').style.width = outerwidth + "px";
	map.invalidateSize();
	mapCenter = map.getCenter();
	mapZoom = map.getZoom();
	}
	
function do_tab(tabid, suffix, lat, lng) {
	theTabs = new Array(1,2,3);
	for(var key in theTabs) {
		if(key == (suffix -1)) {
			}
		}
	if(tabid == "tab1") {
		if($('tab1')) {CngClass('tab1', 'tabinuse');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content1')) {$("content1").style.display = "block";}
		} else if(tabid == "tab2") {
		if($('tab2')) {CngClass('tab2', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content2')) {$("content2").style.display = "block";}
		} else {
		if($('tab3')) {CngClass('tab3', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "block";}
		init_minimap(3, lat,lng, "", 13, <?php print get_variable('locale');?>, 1);
		minimap.setView([lat,lng], 13);
		}
	}
</SCRIPT>
</HEAD>
<?php
$severities = $colors = array();
$severities[$GLOBALS['SEVERITY_NORMAL']] = "#DEE3E7";
$severities[$GLOBALS['SEVERITY_MEDIUM']] = "#00FF00";
$severities[$GLOBALS['SEVERITY_HIGH']] = "#F80000";

$colors[$GLOBALS['SEVERITY_NORMAL']] = "black";
$colors[$GLOBALS['SEVERITY_MEDIUM']] = "black";
$colors[$GLOBALS['SEVERITY_HIGH']] = "yellow";

?>
<BODY style="background-color:{$severities[$row['severity']]}; text-color: {$colors[$row['severity']]};">
<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
	<DIV id='button_bar' class='but_container'>
		<SPAN id='print_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.print();'><SPAN STYLE='float: left;'><?php print get_text("Print");?></SPAN><IMG STYLE='float: right;' SRC='./images/print_small.png' BORDER=0></SPAN>
		<SPAN id='close_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
	</DIV>
	<DIV id='leftcol' style='position: absolute; left: 2px; top: 70px; z-index: 3; text-align: center;'>
		<TABLE ALIGN = 'center'>
			<TR>
				<TD class='text' style='text-align: left;'>
					<TABLE ID='theTable' style='border: 1px outset #707070;'>
<?php

/* Creates statistics header and details of responding and en-route units 7/29/09 */

					$result_dispatched = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
						WHERE ticket_id='$ticket_id'
						AND `dispatched` IS NOT NULL 
						AND `responding` IS NULL 
						AND `on_scene` IS NULL 
						AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) GROUP BY `responder_id`");		// 6/25/10
					$num_rows_dispatched = mysql_num_rows($result_dispatched);

					$result_responding = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
						WHERE ticket_id='$ticket_id'
						AND `responding` IS NOT NULL 
						AND `on_scene` IS NULL 
						AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) GROUP BY `responder_id`");		// 6/25/10
					$num_rows_responding = mysql_num_rows($result_responding);

					$result_on_scene = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
						WHERE ticket_id='$ticket_id' 
						AND `on_scene` IS NOT NULL 
						AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') GROUP BY `responder_id`");		// 6/25/10
					$num_rows_on_scene = mysql_num_rows($result_on_scene);
						
					$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, UNIX_TIMESTAMP(problemstart) AS problemstart, 
						`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
						`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
						`r`.`id` AS `unit_id`,
						`r`.`name` AS `unit_name` ,
						`r`.`type` AS `unit_type` ,
						`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
						FROM `$GLOBALS[mysql_prefix]assigns` 
						LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
						LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
							WHERE (`clear` IS NOT NULL OR DATE_FORMAT(`clear`,'%y') <> '00')
							AND ticket_id='$ticket_id' GROUP BY `r`.`id` ";

					$result_cleared  = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
					$num_rows_cleared = mysql_affected_rows();
					$ticket_end = ($ticket_end > 1)? $ticket_end:  (time() - (get_variable('delta_mins')*60));

					echo "<TR CLASS='even'><TD CLASS='td_label text' style='border-right: 1px outset #000000;'>Ticket:</TD><TD CLASS='td_data text'>{$title}</TD></TR>";
					echo "<TR CLASS='odd'><TD CLASS='td_label text' style='border-right: 1px outset #000000;'>Opened:</TD><TD CLASS='td_data text'>{$ticket_start_str},&nbsp;&nbsp;<SPAN STYLE='background-color:white; color:black;'>&nbsp;&nbsp;Elapsed: $elapsed&nbsp;</SPAN></TD><TR>";
					echo "<TR CLASS='even'><TD CLASS='td_label text' style='border-right: 1px outset #000000;'>Status:</TD><TD CLASS='td_data text'>{$ticket_status}</TD></TR>";
					echo "<TR CLASS='odd'><TD CLASS='td_label text' style='border-right: 1px outset #000000;'>Severity:</TD><TD CLASS='td_data text'>{$ticket_severity}</TD></TR>";					
					echo "<TR CLASS='even'><TD CLASS='td_label text' style='border-right: 1px outset #000000;'>Units dispatched:</TD><TD CLASS='td_data text'>({$num_rows_dispatched})&nbsp;";
					
					while ($row_base= mysql_fetch_array($result_dispatched, MYSQL_ASSOC)) {
						$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
						$row = mysql_fetch_assoc($result);
						echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
						}
					echo "</TD></TR>";
					echo "<TR CLASS='odd'><TD CLASS='td_label text' style='border-right: 1px outset #000000;'>Units responding:</TD><TD CLASS='td_data_wrap text'>({$num_rows_responding})&nbsp;";
					while ($row_base= mysql_fetch_array($result_responding, MYSQL_ASSOC)) {
						$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
						$row = mysql_fetch_assoc($result);
						echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
						}
					echo "</TD></TR>";
					echo "<TR CLASS='even'><TD CLASS='td_label text' style='border-right: 1px outset #000000;'>Units on scene:</TD><TD CLASS='td_data_wrap text'>({$num_rows_on_scene})&nbsp;";
					while ($row_base= mysql_fetch_array($result_on_scene, MYSQL_ASSOC)) {
						$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
						$row = mysql_fetch_assoc($result);
						echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
						}
					echo "</TD></TR>";
					echo "<TR CLASS='odd'><TD CLASS='td_label text' style='border-right: 1px outset #000000;'>Units clear:</TD><TD CLASS='td_data_wrap text'>({$num_rows_cleared})&nbsp;";
					while ($row_base= mysql_fetch_array($result_cleared, MYSQL_ASSOC)) {
						echo "{$row_base['unit_name']}:&nbsp;{$row_base['handle']}&nbsp;&nbsp;";
						}
					echo "</TD></TR>";
					echo "</TABLE>"
?>
				</TD>
			</TR>
		</TABLE>
	<BR />
	<CENTER>
	<DIV ID='map_canvas' style='position: relative; left: 2px; border: 1px outset #707070; z-index: 1;'></DIV>
	<BR clear=all/><SPAN STYLE='background-color:white; font-weight:bold; color:black;'>&nbsp;<?php print $ticket_addr;?>&nbsp;</SPAN></CENTER>
	</DIV>
</DIV>
<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>'>
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE=''>
</FORM>
<?php
		$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
											// 1/7/10
		$query = "SELECT *,
			`problemstart` AS `my_start`,
			`problemstart` AS `problemstart`,
			`problemend` AS `problemend`,
			`date` AS `date`,
			`booked_date` AS `booked_date`,		
			`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
			`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
			`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
			`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
			`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
			`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
			`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
			`rf`.`name` AS `rec_fac_name`,
			`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
			`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
			FROM `$GLOBALS[mysql_prefix]ticket` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
			WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $ticket_id $restrict_ticket";			// 7/16/09, 8/12/09


		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));

		$lat = $row['lat']; $lng = $row['lng'];
?>
<SCRIPT>
var map;
var minimap;
var latLng;
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
mapWidth = viewportwidth * .95;
mapHeight = viewportheight * .60;
outerwidth = viewportwidth * .95;
outerheight = viewportheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('theTable').style.width = outerwidth + "px";
var theLocale = <?php print get_variable('locale');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
init_map(1, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
map.setView([<?php print $lat;?>, <?php print $lng;?>], parseInt(initZoom));
var bounds = map.getBounds();
var zoom = map.getZoom();
</SCRIPT>
<?php

		$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;

		$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#{$ticket_id})</I>" : "";			// 1/25/09, 2/18/12
		$un_stat_cats = get_all_categories();
		$istest = FALSE;
		if($istest) {
			print "GET<br />\n";
			dump($_GET);
			print "POST<br />\n";
			dump($_POST);
			}

		if ($ticket_id == '' OR $ticket_id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$ticket_id'")) {	/* sanity check */
			print "Invalid Ticket ID: '$ticket_id'<BR />";
			return;
			}

		$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
											// 1/7/10
		$query = "SELECT *,
			`problemstart` AS `my_start`,
			`problemstart` AS `problemstart`,
			`problemend` AS `problemend`,
			`date` AS `date`,
			`booked_date` AS `booked_date`,		
			`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
			`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
			`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
			`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
			`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
			`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
			`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
			`rf`.`name` AS `rec_fac_name`,
			`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
			`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,
			`ty`.`type` AS `type`, 			
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
			FROM `$GLOBALS[mysql_prefix]ticket` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
			WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $ticket_id $restrict_ticket";			// 7/16/09, 8/12/09


		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		$tip =  htmlentities ("{$row['contact']}/{$row['tick_street']}/{$row['tick_city']}/{$row['tick_state']}/{$row['phone']}/{$row['scope']}", ENT_QUOTES);		// tooltip string - 10/28/2012
		$sched_flag = ($row['status'] == $GLOBALS['STATUS_SCHEDULED']) ? "*" : "";		
		$type = shorten($row['type'], 18);
		$severity = $row['severity'];
		$status = $row['status'];
		$the_id = $row['tick_id'];		// 11/27/09
		$radius = $row['radius'];
		$updated = format_sb_date_2($row['updated']);
		$the_scope = htmlentities(shorten($row['scope'], 30), ENT_QUOTES);
		$address_street=htmlentities(shorten($row['tick_street'] . " " . $row['tick_city'], 20), ENT_QUOTES);
		$locale = get_variable('locale');	// 08/03/09		
		if ($status== $GLOBALS['STATUS_CLOSED']) {
			$strike = "<strike>"; $strikend = "</strike>";
			}
		else { $strike = $strikend = "";}
		if (my_is_float($row['lat'])) {		// 6/21/10
			$temp_array[0] = $row['lat'];
			$temp_array[1] = $row['lng'];
			$temp_array[2] = htmlentities(shorten($row['scope'], 48), ENT_QUOTES);
			$temp_array[3] = htmlentities(shorten(str_replace($eols, " ", $row['tick_descr']), 256), ENT_QUOTES);
			$street = empty($row['ticket_street'])? "" : replace_quotes($row['ticket_street']) . "<BR/>" . replace_quotes($row['ticket_city']) . " " . replace_quotes($row['ticket_state']) ;
			$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
			$theTabs = "<div class='infowin'><BR />";
			$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
			$theTabs .= '<div class="tabArea">';
			$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
			$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab2\', 2, null, null);">Details</span>';
			$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row['lat'] . ',' . $row['lng'] . ');">Location</span>';
			$theTabs .= '</div>';
			$theTabs .= '<div class="contentwrapper">';
		
			$tab_1 = "<TABLE width='280px' style='height: 260px;'><TR><TD><TABLE width='98%'>";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . htmlentities(shorten($row['scope'], 48), ENT_QUOTES)  . "$strikend</B></TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD class='td_label text'>As of:</TD><TD class='td_data text'>" . format_date_2(($row['updated'])) . "</TD></TR>";
			if (is_date($row['booked_date'])){
				$tab_1 .= "<TR CLASS='odd'><TD class='td_label text'>Booked Date:</TD><TD class='td_data text'>" . format_date_2($row['booked_date']) . "</TD></TR>";	//10/27/09, 3/15/11
				}
			$tab_1 .= "<TR CLASS='even'><TD class='td_label text'>Reported by:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['contact'], 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD class='td_label text'>Phone:</TD><TD class='td_data text'>" . format_phone($row['phone']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD class='td_label text'>Addr:</TD><TD class='td_data text'>$address_street</TD></TR>";
	
			$tab_1 .= "<TR CLASS='odd'><TD class='td_label text' ALIGN='left'>Status:</TD><TD class='td_data text'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;($elapsed)</TD></TR>";	// 3/27/10
			$tab_1 .= (empty($row['fac_name']))? "" : "<TR CLASS='even'><TD class='td_label text'>Receiving Facility:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['fac_name'], 30))  . "</TD></TR>";	//3/27/10, 3/15/11
			$utm = get_variable('UTM');
			if ($utm==1) {
				$coords =  $row['lat'] . "," . $row['lng'];																	// 8/12/09
				$tab_1 .= "<TR CLASS='even'><TD class='td_label text'>UTM grid:</TD><TD class='td_data text'>" . toUTM($coords) . "</TD></TR>";
				}
			$tab_1 .= "</TABLE></TD></TR>";
			$tab_1 .= 	"</FONT></TD></TR></TABLE>";			// 11/6/08	
			$tab_2 = "<TABLE width='280px' style='height: 280px;' ><TR><TD><TABLE width='98%'>";
			$tab_2 .= "<TR CLASS='even'><TD class='td_label text'>Description:</TD><TD class='td_data text'>" . replace_quotes(shorten(str_replace($eols, " ", $row['tick_descr']), 48)) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD class='td_label text'>" . get_text("911 Contacted") . "</TD><TD class='td_data text'>" . shorten($row['nine_one_one'], 48) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD class='td_label text'>{$disposition}:</TD><TD class='td_data text'>" . shorten(replace_quotes($row['comments']), 48) . "</TD></TR></TABLE></TD></TR>";		// 8/13/09, 3/15/11
			$tab_2 .= "<TR><TD COLSPAN=2 ALIGN='left'><DIV style='max-height: 200px; overflow-y: scroll;'>" . show_assigns(0, $the_id) . "</DIV></TD></TR>";

			$tab_2 .= "</TABLE>";			// 11/6/08			
			
			$tab_3 = "<TABLE width='280px' style='height: 280px;'><TR><TD>";
			$tab_3 .= "<TABLE width='98%'>";

			switch($locale) { 
				case "0":
				$tab_3 .= "<TR CLASS='odd'><TD class='td_label text'>USNG:</TD><TD class='td_data text'>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "1":
				$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label text'>OSGB:</TD><TD class='td_data text'>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "2":
				$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
				$tab_3 .= "<TR CLASS='odd'>	<TD class='td_label text'>UTM:</TD><TD class='td_data text'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				default:
				print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
				}
			$tab_3 .= "<TR><TD class='td_label text'>Lat</TD><TD class='td_data text'>" . $row['lat'] . "</TD></TR>";
			$tab_3 .= "<TR><TD class='td_label text'>Lng</TD><TD class='td_data text'>" . $row['lng'] . "</TD></TR>";
			$tab_3 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
			$tab_3 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
			$tab_3 .= "</TABLE></TD</TR></TABLE>";
			}
			
		$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
		$theTabs .= "<div class='content' id='content2' style = 'display: none;'>" . $tab_2 . "</div>";
		$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_3 . "</div>";
		$theTabs .= "</div>";
		$theTabs .= "</div>";
		$theTabs .= "</div>";
		$lat = $row['lat']; $lng = $row['lng'];

		if ((($lat == $GLOBALS['NM_LAT_VAL']) && ($lng == $GLOBALS['NM_LAT_VAL'])) || (($lat == "") || ($lat == NULL)) || (($lng == "") || ($lng == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
			$lat = get_variable('def_lat'); $lng = get_variable('def_lng');
			$icon_file = "./our_icons/question1.png";
			}
		else {
			$icon_file = "./markers/crosshair.png";
			}
			if ((my_is_float($lat)) && (my_is_float($lng))) {
?>
<SCRIPT>
				var marker = createMarker(<?php print $lat;?>, <?php print $lng;?>, <?php print quote_smart($theTabs);?>, <?php print $row['severity'];?>, "<?php print $row['type'];?>", 0, 0, "Incident", 0, "<?php print $tip?>");
				marker.addTo(map);
				map.setView([<?php print $lat;?>, <?php print $lng;?>], 13);		
</SCRIPT>
<?php
				}	// end if my_is_float
			
// ====================================Add Facilities to Map 8/1/09================================================
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
			ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";	

		$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

		while($row_fac = mysql_fetch_array($result_fac)){

			$fac_name = $row_fac['facility_name'];			//	10/8/09
			$fac_temp = explode("/", $fac_name );
			$fac_index = $row_fac['icon_str'];	
			
			$fac_id=($row_fac['fac_id']);
			$fac_type=($row_fac['icon']);

			$f_disp_name = $row_fac['facility_name'];		//	10/8/09
			$f_disp_temp = explode("/", $f_disp_name );
			$facility_display_name = $f_disp_temp[0];
			$faclat = $row_fac['lat'];
			$faclng = $row_fac['lng'];
			// BEDS
				$beds_info = "<TD ALIGN='right'>{$row_fac['beds_a']}/{$row_fac['beds_o']}</TD>";
			// STATUS
				$status = get_status_sel($row_fac['fac_id'], $row_fac['fac_status_id'], "f");
				$status_id = $row_fac['fac_status_id'];
				$temp = $row_fac['status_id'] ;
				$the_status = (array_key_exists($temp, $fac_status_vals))? $fac_status_vals[$temp] : "??";
			// AS-OF - 11/3/2012
				$updated = format_sb_date_2 ( $row_fac['updated'] );
				
			if (my_is_float($row_fac['lat'])) {										// position data of any type?
				$temptype = $f_types[$row_fac['type_id']];
				$the_type = $temptype[0];
				$line_ctr = 0;
				$temp_array[0] = $row_fac['lat'];
				$temp_array[1] = $row_fac['lng'];
				$temp_array[2] = htmlentities(shorten($facility_display_name, 48), ENT_QUOTES);
				$temp_array[3] = htmlentities(shorten(str_replace($eols, " ", $facility_display_name), 48), ENT_QUOTES);
				$theTabs = "<div class='infowin'><BR />";
				$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
				$theTabs .= '<div class="tabArea">';
				$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
				$theTabs .= '<span id="tab3" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row_fac['lat'] . ',' . $row_fac['lng'] . ');">Location</span>';
				$theTabs .= '</div>';
				$theTabs .= '<div class="contentwrapper">';		

				$tab_1 = "<TABLE width='280px' style='height: 280px;'><TR><TD><TABLE width='98%'>";	
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . htmlentities(shorten($facility_display_name, 48), ENT_QUOTES) . "</B> - " . $the_type . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . htmlentities(shorten(str_replace($eols, " ", $row_fac['facility_description']), 32), ENT_QUOTES) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . $the_status . " </TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date(strtotime($row_fac['updated'])) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). " Via: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
				if(!(isempty(trim($row_fac['security_contact']))))	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD ALIGN='right' STYLE= 'width:50%'>Security contact:&nbsp;</TD><TD ALIGN='left' STYLE= 'width:50%'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";}
				if(!(isempty(trim($row_fac['security_email']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";}
				if(!(isempty(trim($row_fac['security_phone']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";}
				if(!(isempty(trim($row_fac['access_rules']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>" . get_text("Access rules") . ":&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";}
				if(!(isempty(trim($row_fac['security_reqs']))))  	{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";}
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
					$openingTimes = "Opening Times Today (" . $the_day . ")  ---  " . $outputstring;
					$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Opening today (" . $the_day . ")&nbsp;</TD><TD ALIGN='left'>" . $outputstring . "</TD></TR>";
					}
				if(!(isempty(trim($row_fac['pager_p']))))  			{$line_ctr++; $tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";}
				if(!(isempty(trim($row_fac['pager_s']))))  			{$line_ctr++; $tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";}
				$tab_1 .= "</TABLE></TD></TR>";
				$tab_1 .= "<TR><TD COLSPAN=2 ALIGN='center'><TABLE>";
				$tab_1 .= "</TABLE></TD></TR></TABLE>";
				$tab_2 = "<TABLE width='280px' style='height: 280px;'><TR><TD>";
				$tab_2 .= "<TABLE width='98%'>";

				switch($locale) { 
					case "0":
					$tab_2 .= "<TR CLASS='odd'><TD class='td_label text' ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row_fac['lat'], $row_fac['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "1":
					$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label text' ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row_fac['lat'], $row_fac['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "2":
					$coords =  $row_fac['lat'] . "," . $row_fac['lng'];							// 8/12/09
					$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label text' ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					default:
					print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
					}
				$tab_2 .= "<TR><TD class='td_label text' style='font-size: 80%;'>Lat</TD><TD class='td_data text' style='font-size: 80%;'>" . $row_fac['lat'] . "</TD></TR>";
				$tab_2 .= "<TR><TD class='td_label text' style='font-size: 80%;'>Lng</TD><TD class='td_data text' style='font-size: 80%;'>" . $row_fac['lng'] . "</TD></TR>";
				$tab_2 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
				$tab_2 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
				$tab_2 .= "</TABLE></TD</TR></TABLE>";
					
				$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
				$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_2 . "</div>";
				$theTabs .= "</div>";
				$theTabs .= "</div>";
				$theTabs .= "</div>";
				$line_ctr++;
				}		// end if/else			
			

			if ((my_is_float($faclat)) && (my_is_float($faclng))) {
?>
<SCRIPT>
				var marker = createFacilityMarker(<?php print $faclat;?>, <?php print $faclng;?>, <?php print quote_smart($theTabs);?>, <?php print $fac_type;?>, 0, <?php print $fac_id;?>, '<?php print $fac_index;?>', 0, 0, '<?php print $facility_display_name;?>');
				marker.addTo(map);
</SCRIPT>
<?php
				}	// end if my_is_float
			}	// end while
// ================================End of Facilities========================================
// ====================================Add Responding Units to Map================================================

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$ticket_id' AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while($row = mysql_fetch_array($result)){
			$responder_id=($row['responder_id']);
			$query_unit = "SELECT *, r.updated AS `r_updated`,
				`r`.`status_updated` AS `status_updated`,
				`r`.`status_about` AS `status_about`,
				`t`.`id` AS `type_id`,
				`r`.`id` AS `unit_id`,
				`r`.`name` AS `name`,
				`t`.`name` AS `un_type_name`,
				`s`.`description` AS `stat_descr`,
				`r`.`description` AS `unit_descr`, 
				`r`.`ring_fence` AS `ring_fence`,	
				`r`.`excl_zone` AS `excl_zone`,		
				(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns`
				WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id  AND  (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )) AS `nr_assigned` 
				FROM `$GLOBALS[mysql_prefix]responder` `r` 
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = a.resource_id )			
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 		
				WHERE `r`.`id`='$responder_id';";
			$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while($row_unit = mysql_fetch_array($result_unit)){
				$unit_id = $row_unit['unit_id'];
				$mobile = $row_unit['mobile'];
				$handle = $row_unit['handle'];
				$index = $row_unit['icon_str'];
				$resp_cat = $un_stat_cats[$row_unit['unit_id']];
				$temp = $row_unit['un_status_id'] ;
				$the_time = $row_unit['updated'];
				$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
				if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {
					$theTabs = "<div class='infowin'><BR />";
					$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
					$theTabs .= '<div class="tabArea">';
					$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
					$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row_unit['lat'] . ',' . $row_unit['lng'] . ');">Location</span>';
					$theTabs .= '</div>';
					$theTabs .= '<div class="contentwrapper">';
					
					$tab_1 = "<TABLE width='{$iw_width}' style='height: 280px;'><TR><TD><TABLE>";			
					$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_unit['name'], 48)) . "</B></TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . addslashes(shorten(str_replace($eols, " ", $row_unit['description']), 32)) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . addslashes($row_unit['contact_name']). " Via: " . addslashes($row_unit['contact_via']) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($the_time) . "</TD></TR>";		// 4/11/10
					if (array_key_exists($unit_id, $assigns)) {
						$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$unit_id] . "'>" . addslashes(shorten($assigns[$unit_id], 20)) . "</A></TD></TR>";
						}
					$tab_1 .= "</TABLE></TD></TR></TABLE>";
				
					$tab_2 = "<TABLE width='{$iw_width}' style='height: 280px;'><TR><TD>";
					$tab_2 .= "<TABLE width='100%'>";
					$locale = get_variable('locale');	// 08/03/09
					switch($locale) { 
						case "0":
						$tab_2 .= "<TR CLASS='odd'><TD class='td_label text' ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row_unit['lat'], $row_unit['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						case "1":
						$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label text' ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row_unit['lat'], $row_unit['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						case "2":
						$coords =  $row_unit['lat'] . "," . $row_unit['lng'];							// 8/12/09
						$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label text' ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						default:
						print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
						}
					$tab_2 .= "<TR><TD class='td_label text' style='font-size: 80%;'>Lat</TD><TD class='td_data text' style='font-size: 80%;'>" . $row_unit['lat'] . "</TD></TR>";
					$tab_2 .= "<TR><TD class='td_label text' style='font-size: 80%;'>Lng</TD><TD class='td_data text' style='font-size: 80%;'>" . $row_unit['lng'] . "</TD></TR>";
					$tab_2 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
					$tab_2 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
					$tab_2 .= "</TABLE></TD</TR></TABLE>";
						
					$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
					$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_2 . "</div>";
					$theTabs .= "</div>";
					$theTabs .= "</div>";
					$theTabs .= "</div>";
?>				
<SCRIPT>
					var isMobile = <?php print $mobile;?>;
					var theCol = (isMobile == 1) ? 0 : 1;
					var marker = createUnitMarker(<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>, <?php print quote_smart($theTabs);?>, theCol, 0, <?php print $unit_id;?>, '<?php print $index;?>', '<?php print $resp_cat;?>', 0, '<?php print $handle;?>', <?php print $row_unit['icon'];?>);
					marker.addTo(map);
</SCRIPT>						
<?php
					}	// end if mys_is_float
				}	// end while row unit
			}	//	end while row
// =====================================End of functions to show responding units========================================================================
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
mapWidth = viewportwidth * .95;
mapHeight = viewportheight * .60;
outerwidth = viewportwidth * .95;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .7;
listwidth = colwidth * .95
inner_listwidth = listwidth *.9;
celwidth = listwidth * .20;
res_celwidth = listwidth * .15;
fac_celwidth = listwidth * .15;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('theTable').style.width = outerwidth + "px";
</SCRIPT>
</BODY>
</HTML>
