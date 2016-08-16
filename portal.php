<?php
/*
9/10/13 - Major re-write to previous versions
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
$logged_in = $logged_out = false;
if (empty($_SESSION)) {
	$logged_out = true;
	header("Location: index.php");
	} else {
	$logged_in = true;
	}
require_once './incs/functions.inc.php';
do_login(basename(__FILE__));

$requester = get_owner($_SESSION['user_id']);


function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $row['user'];
		}
	return $the_ret;
	}

if ($_SESSION['internet']) {				// 8/22/10
	$api_key = trim(get_variable('gmaps_api_key'));
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
	} else {
	$api_key = "";
	$key_str = "";	
	}
	
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
if((array_key_exists('HTTPS', $_SERVER)) && ($_SERVER['HTTPS'] == 'on')) {
	$gmaps_url =  "https://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
	} else {
	$gmaps_url =  "http://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - <?php print get_text('Service User');?> <?php print get_text('Portal');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="./portal/css/stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="./js/Control.Geocoder.css" />
<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
<STYLE>
	.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	#regions_control { font-family: verdana, arial, helvetica, sans-serif; font-size: 5px; background-color: #FEFEFE; font-weight: bold;}
	#sched_flag { font-family: verdana, arial, helvetica, sans-serif; font-size: 12px; color: #0080FF; font-weight: bold; cursor: pointer; }
	table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
	table.cruises td {overflow: hidden; }
	div.scrollableContainer { position: relative; padding-top: 2em; border: 1px solid #999; }
	div.scrollableContainer2 { position: relative; padding-top: 2em; }
	div.scrollingArea { max-height: 200px; overflow: auto; overflow-x: hidden; }
	div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
	table.scrollable thead tr { position: absolute; left: -1px; top: 0px; }
	table.cruises th { text-align: left; overflow: hidden; }
	div.tabBox {}
	div.tabArea { font-size: 12px; font-weight: bold; padding: 0px 0px 3px 0px; }
	span.tab { background-color: #CECECE; color: #8060b0; border: 2px solid #000000; border-bottom-width: 0px; -moz-border-radius: .75em .75em 0em 0em;	border-radius-topleft: .75em; border-radius-topright: .75em;
			padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px; z-index: 100; }
	span.tabinuse {	background-color: #FFFFFF; color: #000000; border: 2px solid #000000; border-bottom-width: 0px;	border-color: #f0d0ff #b090e0 #b090e0 #f0d0ff; -moz-border-radius: .75em .75em 0em 0em;
			border-radius-topleft: .75em; border-radius-topright: .75em; padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px;	z-index: 100;}
	span.tab:hover { background-color: #FEFEFE; border-color: #c0a0f0 #8060b0 #8060b0 #c0a0f0; color: #ffe0ff;}
	div.content { font-size: 12px; background-color: #F0F0F0; border: 2px outset #707070; -moz-border-radius: 0em .5em .5em 0em;	border-radius-topright: .5em; border-radius-bottomright: .5em; padding: .5em;
			position: relative;	z-index: 101; cursor: normal; height: 250px;}
	div.contentwrapper { width: 260px; background-color: #F0F0F0; cursor: normal;}
	.text-labels {font-size: 2em; font-weight: 700;}
	.plain_listheader 	{color:#000000; border: 1px outset #606060;	text-decoration: none; background-color: #CECECE; font-weight: bolder; cursor: pointer;	}
	.hover_listheader 	{color:#000000; border: 1px inset #606060; text-decoration: none; background-color: #DEE3E7; font-weight: bolder; cursor: pointer; }
</STYLE>
<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT>
<script src="./js/proj4js.js"></script>
<script src="./js/proj4-compressed.js"></script>
<script src="./js/leaflet/leaflet.js"></script>
<script src="./js/proj4leaflet.js"></script>
<script src="./js/leaflet/KML.js"></script>
<script src="./js/leaflet/gpx.js"></script>  
<script src="./js/osopenspace.js"></script>
<script src="./js/leaflet-openweathermap.js"></script>
<script src="./js/esri-leaflet.js"></script>
<script src="./js/Control.Geocoder.js"></script>
<SCRIPT TYPE="text/javascript" src="<?php print $gmaps_url;?>"></SCRIPT><script src="./js/Google.js"></script>
<script type="text/javascript" src="./js/osm_map_functions.js.php"></script>
<script type="text/javascript" src="./js/L.Graticule.js"></script>
<script type="text/javascript" src="./js/leaflet-providers.js"></script>
<script type="text/javascript" src="./js/usng.js"></script>
<script type="text/javascript" src="./js/osgb.js"></script>
<script type="text/javascript" src="./js/geotools2.js"></script>
<SCRIPT>
var doDebug = true;
var changed_showhide = true;
var changed_mkrshowhide = true;
var randomnumber;
var viewportwidth;
var viewportheight;
var the_string;
var theClass = "background-color: #CECECE";
var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;
var request_lat;
var request_lng;
var the_color;
var fac_lat = [];
var fac_lng = [];
var fac_street = [];
var fac_city = [];
var fac_state = [];
var tmarkers = [];	//	Incident markers array
var reqmarkers = []	//	Request Markers array
var rmarkers = [];			//	Responder Markers array
var cmarkers = [];			//	conditions markers array
var showall = "no";
var point;
var theLat;
var theLng;
var showhide = 1;
var summary_interval;
var msgs_interval;
var markers_interval;
var c_interval = null;
var iwMaxWidth = 500;
var sortby = '`date`';
var sort = "DESC";
var reqFin = false;
var req_last_display = 0;
var requests_updated = [];
var markers = [];
var reqcell1 = 0;
var reqcell2 = 0;
var reqcell3 = 0;
var reqcell4 = 0;
var reqcell5 = 0;
var reqcell6 = 0;
var reqcell7 = 0;
var reqcell8 = 0;
var reqcell9 = 0;
var reqcell10 = 0;
var reqcell11 = 0;
var reqcell12 = 0;
var reqcell13 = 0;
var icons=[];	
icons[0] = "black.png";
icons[1] = "yellow.png";
icons[2] = "brown.png";
icons[3] = "lt_blue.png";
icons[4] = "green.png";
icons[5] = "white.png";
icons[6] = "orange.png";
icons[7] = "white.png";
icons[8] = "red.png";
icons[9] = "black.png";
icons[10] = "gray.png";
var textlight = [];
textlight[0] = 1;
textlight[1] = 0;
textlight[2] = 1;
textlight[3] = 0;
textlight[4] = 0;
textlight[5] = 0;
textlight[6] = 0;
textlight[7] = 0;
textlight[8] = 0;
textlight[9] = 0;
textlight[10] = 0;
var statusVals = [];
statusVals[0] = "Error";
statusVals[1] = "Open";
statusVals[2] = "Tentative";
statusVals[3] = "Accepted";
statusVals[4] = "Resouced";
statusVals[5] = "Completed";
statusVals[6] = "Declined";
statusVals[7] = "Closed";
statusVals[8] = "Cancelled";
statusVals[9] =  "Error";
var mapCenter;
var mapZoom;
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

function do_hover_listheader (the_id) {
	CngClass(the_id, 'hover_listheader');
	return true;
	}

function do_plain_listheader (the_id) {				// 8/21/10
	CngClass(the_id, 'plain_listheader');
	return true;
	}

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
	var mapWidth = viewportwidth * .5;
	var mapHeight = viewportheight * .5;
	var listWidth = viewportwidth * .96;	
	var listHeight = viewportheight * .35;
	var controlsWidth = viewportwidth * .35;
	var controlsHeight = viewportheight * .4;
	var bannerwidth = viewportwidth * .96;
	var outerWidth = viewportwidth * .97;
	$('outer').style.width = outerWidth + "px";
	$('outer').style.height = viewportheight + "px";
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('requests_list').style.width = listWidth + "px";
	$('requests_list').style.height = listHeight + "px";
	$('tophalf').style.width = outerWidth + "px";
	$('tophalf').style.height = viewportheight * .60 + "px";
	$('bottomhalf').style.width = outerWidth + "px";
	$('bottomhalf').style.height = viewportheight * .35 + "px";
	$('bottomhalf').style.textAlign = "center";
	$('controls').style.width = outerWidth * .4 + "px";
	$('controls').style.height = viewportheight * .5 + "px";
	$('map_wrapper').style.width = mapWidth + "px";
	$('map_wrapper').style.height = mapHeight + "px";
	$('banner').style.width = bannerwidth + "px";		
	$('banner').style.height = "2em";	
	$('list_header').style.width = bannerwidth + "px";		
	$('list_header').style.height = "2em";	
	$('all_requests').style.width = bannerwidth + "px";
	$('the_bottom').style.width = bannerwidth + "px";	
	mapCenter = map.getCenter();
	mapZoom = map.getZoom();
	}
	
function loadIt() {
	get_requests(window.req_field, window.req_direct); 
	get_the_markers(); 
	do_filelist(); 
	get_summary();
	do_conditions();
	map.invalidateSize();
	setTimeout(function() {
		mapCenter = map.getCenter();
		mapZoom = map.getZoom();
		},500);
	}

function myClick(id, canedit, ticketID) {
	if(ticketID == 0) {
		if(reqmarkers[id]) {
			reqmarkers[id].openPopup();
			} else {
			if(canedit) {
				do_window(id);	
				} else {
				do_viewwindow(id)
				}
			}
		} else {
		if(tmarkers[ticketID]) {
			tmarkers[ticketID].openPopup();
			} else {
			if(canedit) {
				do_window(id);					
				} else {
				do_viewwindow(id);
				}
			}
		}
	}	

function out_frames() {		//  onLoad = "out_frames()"
	if (top.location != location) {
		top.location.href = document.location.href;
		location.href = '#top'; 
		setTimeout(function() {
			loadIt();
			},1000);
		} else {
		location.href = '#top';
		loadIt();
		}
	}		// end function out_frames()
	

function $() {									// 1/21/09
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
		
function go_there (where, the_id) {		//
	document.go.action = where;
	document.go.submit();
	}				// end function go there ()	
	
function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}
	
function logged_in() {								// returns boolean
	var temp = <?php print $logged_in;?>;
	return temp;
	}	
	
function isNull(val) {								// checks var stuff = null;
	return val === null;
	}
	
var newwindow = null;
var starting;
function do_window(id) {				// 1/19/09
	if ((newwindow) && (!(newwindow.closed))) {newwindow.focus(); return;}		// 7/28/10	
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		newwindow=window.open("./portal/request.php?id=" + id, "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=100, screenX=100, screenY=100");
		if (isNull(newwindow)) {
			alert ("Portal operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		starting = false;
		}
	}		// end function do_window()

var viewwindow = null;
var starting;
function do_viewwindow(id) {				// 1/19/09
	if ((viewwindow) && (!(viewwindow.closed))) {viewwindow.focus(); return;}		// 7/28/10	
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		viewwindow=window.open("./portal/request.php?func=view&id=" + id, "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=100, screenX=100, screenY=100");
		if (isNull(viewwindow)) {
			alert ("Portal operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		viewwindow.focus();
		starting = false;
		}
	}		// end function do_viewwindow()
	
var newreq = null;
var starting;
function do_newreq() {				// 1/19/09
	if ((newreq) && (!(newreq.closed))) {newreq.focus(); return;}		// 7/28/10	
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		newreq=window.open("./portal/new_request.php", "new_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=300, screenX=100, screenY=300");
		if (isNull(newreq)) {
			alert ("Portal operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newreq.focus();
		starting = false;
		}
	}		// end function do_newreq()
	
var dopasswd = null;
var starting;
function do_passwdchange() {				// 1/19/09
	if ((dopasswd) && (!(dopasswd.closed))) {dopasswd.focus(); return;}		// 7/28/10	
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		dopasswd=window.open("./portal/profile.php", "change_password",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=300, screenX=100, screenY=300");
		if (isNull(dopasswd)) {
			alert ("Portal operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		dopasswd.focus();
		starting = false;
		}
	}		// end function do_passwdchange()

function requests_cb2(req) {
	var i = 1;
	var req_id = 0;
	var the_requests = JSON.decode(req.responseText);
	if(!the_requests && doDebug) { alert(req.responseText); }
	if((the_requests[0]) && (the_requests[0][0] == 0)) {
		for(var key in reqmarkers) {
			if(reqmarkers[key]) {map.removeLayer(reqmarkers[key]);}
			}
		var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Open Requests. You can show closed request by clicking \"Show Closed\"........</marquee>";
		$('all_requests').innerHTML = outputtext;
		window.latest_facility = 0;
		} else {
		var outputtext = "<TABLE id='requeststable' class='cruises scrollable' style='width: " + window.listWidth + "px;'>";
		outputtext += "<thead>";
		outputtext += "<TR style='width: " + window.listWidth + "px;'>";
		outputtext += "<TH id='r13' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Request ID');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'id', '<?php print get_text('ID');?>')\">" + window.r13_text + "</TH>";
		outputtext += "<TH id='r1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Patient');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'patient', '<?php print get_text('Patient');?>')\">" + window.r1_text + "</TH>";
		outputtext += "<TH id='r2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Phone');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'phone', '<?php print get_text('Phone');?>')\">" + window.r2_text + "</TH>";
		outputtext += "<TH id='r3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Contact');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'contact', '<?php print get_text('Contact');?>')\">" + window.r3_text + "</TH>";
		outputtext += "<TH id='r4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Scope');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'scope', '<?php print get_text('Scope');?>')\">" + window.r4_text + "</TH>";
		outputtext += "<TH id='r5' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('To Address');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'toaddress', '<?php print get_text('To Address');?>')\">" + window.r5_text + "</TH>";
		outputtext += "<TH id='r6' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Postcode');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'postcode', '<?php print get_text('Postcode');?>')\">" + window.r6_text + "</TH>";
		outputtext += "<TH id='r7' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Request Date');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'requestdate', '<?php print get_text('Request Date');?>')\">" + window.r7_text + "</TH>";
		outputtext += "<TH id='r8' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Pickup');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'pickup', '<?php print get_text('Pickup');?>')\">" + window.r8_text + "</TH>";
		outputtext += "<TH id='r9' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Arrival');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'arrival', '<?php print get_text('Arrival');?>')\">" + window.r9_text + "</TH>";
		outputtext += "<TH id='r10' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Status');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'status', '<?php print get_text('Status');?>')\">" + window.r10_text + "</TH>";
		outputtext += "<TH id='r11' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Updated');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'updated', '<?php print get_text('Updated');?>')\">" + window.r11_text + "</TH>";
		outputtext += "<TH id='r12' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('By');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'by', '<?php print get_text('By');?>')\">" + window.r12_text + "</TH>";
		outputtext += "</TR>";
		outputtext += "</thead>";
		outputtext += "<tbody>";
		for (var key = 0; key < the_requests.length; key++) {
			$('export_but').style.display = "inline-block";						
			req_id = the_requests[key][0];
			if((the_requests[key][16] == "Open") || (the_requests[key][16] == "Tentative") || (the_requests[key][16] == "Accepted")) {
				var canedit = 1;
				} else {
				var canedit = 0;
				}
				outputtext += "<TR class='list_row' title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + ";' onClick='myClick(" + req_id + ", " + canedit + ", " + the_requests[key][35] + ");'>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(4, the_requests[key][0], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(20, the_requests[key][2], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(16, the_requests[key][3], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(20, the_requests[key][4], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(25, the_requests[key][13], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(25, the_requests[key][31], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(12, the_requests[key][32], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(12, the_requests[key][18], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(8, the_requests[key][33], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(8, the_requests[key][34], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(15, the_requests[key][16], "\u00a0") + "</TD>";	
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(12, the_requests[key][25], "\u00a0") + "</TD>";
				outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(12, the_requests[key][26], "\u00a0") + "</TD>";
				outputtext += "</TR>";
				if(window.requests_updated[req_id]) {
					if(window.requests_updated[req_id] != the_requests[key][9]) {
						window.do_req_update = true;
						} else {
						window.do_req_update = false;
						}
					} else {
					window.requests_updated[req_id] = the_requests[key][9];
					window.do_req_update = true;
					}	
				request_number = req_id;				
				i++;					
				}
		outputtext += "</tbody>";
		outputtext += "</TABLE>";
		setTimeout(function() {	
			if(window.req_last_display == 0) {
				$('all_requests').innerHTML = outputtext;
				window.latest_request = request_number;
				} else {
				if((request_number != window.latest_request) || (window.do_req_update == true) || (window.changed_req_sort == true)) {
					$('all_requests').innerHTML = outputtext;
					window.latest_request = request_number;
					}
				}
			var reqtbl = document.getElementById('requeststable');
			if(reqtbl) {
				var headerRow = reqtbl.rows[0];
				var tableRow = reqtbl.rows[1];
				if(tableRow) {
					if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
					if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
					if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
					if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
					if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
					if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";}
					if(tableRow.cells[6] && headerRow.cells[6]) {headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";}
					if(tableRow.cells[7] && headerRow.cells[7]) {headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";}
					if(tableRow.cells[8] && headerRow.cells[8]) {headerRow.cells[8].style.width = tableRow.cells[8].clientWidth - 4 + "px";}
					if(tableRow.cells[9] && headerRow.cells[9]) {headerRow.cells[9].style.width = tableRow.cells[9].clientWidth - 4 + "px";}
					if(tableRow.cells[10] && headerRow.cells[10]) {headerRow.cells[10].style.width = tableRow.cells[10].clientWidth - 4 + "px";}
					if(tableRow.cells[11] && headerRow.cells[11]) {headerRow.cells[11].style.width = tableRow.cells[11].clientWidth - 4 + "px";}
					if(tableRow.cells[12] && headerRow.cells[12]) {headerRow.cells[12].style.width = tableRow.cells[12].clientWidth - 4 + "px";}
					} else {
					var cellwidthBase = window.listWidth / 52;
					reqcell1 = cellwidthBase * 2;
					reqcell2 = cellwidthBase * 4;
					reqcell3 = cellwidthBase * 4;
					reqcell4 = cellwidthBase * 4;
					reqcell5 = cellwidthBase * 5;
					reqcell6 = cellwidthBase * 6;
					reqcell7 = cellwidthBase * 5;
					reqcell8 = cellwidthBase * 4;
					reqcell9 = cellwidthBase * 4;
					reqcell10 = cellwidthBase * 4;
					reqcell11 = cellwidthBase * 4;
					reqcell12 = cellwidthBase * 3;
					reqcell13 = cellwidthBase * 3;
					headerRow.cells[0].style.width = reqcell1 + "px";
					headerRow.cells[1].style.width = reqcell2 + "px";
					headerRow.cells[2].style.width = reqcell3 + "px";
					headerRow.cells[3].style.width = reqcell4 + "px";						
					headerRow.cells[4].style.width = reqcell5 + "px";
					headerRow.cells[5].style.width = reqcell6 + "px";
					headerRow.cells[6].style.width = reqcell7 + "px";
					headerRow.cells[7].style.width = reqcell8 + "px";
					headerRow.cells[8].style.width = reqcell9 + "px";
					headerRow.cells[9].style.width = reqcell10 + "px";						
					headerRow.cells[10].style.width = reqcell11 + "px";
					headerRow.cells[11].style.width = reqcell12 + "px";
					headerRow.cells[12].style.width = reqcell13 + "px";					
					}
				if(getHeaderHeight(headerRow) >= 20) {
					var theRow = reqtbl.insertRow(1);
					theRow.style.height = "20px";
					var no1 = theRow.insertCell(0);
					var no2 = theRow.insertCell(1);
					var no3 = theRow.insertCell(2);
					var no4 = theRow.insertCell(3);
					var no5 = theRow.insertCell(4);
					var no6 = theRow.insertCell(5);
					var no7 = theRow.insertCell(6);
					var no8 = theRow.insertCell(7);
					var no9 = theRow.insertCell(8);
					var no10 = theRow.insertCell(9);
					var no11 = theRow.insertCell(10);
					var no12 = theRow.insertCell(11);
					var no13 = theRow.insertCell(12);
					no1.innerHTML = " ";
					no2.innerHTML = " ";
					no3.innerHTML = " ";
					no4.innerHTML = " ";
					no5.innerHTML = " ";
					no6.innerHTML = " ";
					no7.innerHTML = " ";
					no8.innerHTML = " ";
					no9.innerHTML = " ";
					no10.innerHTML = " ";
					no11.innerHTML = " ";
					no12.innerHTML = " ";
					no13.innerHTML = " ";
					}
				}
			window.reqFin = true;
			requests_get();
			},500);
		}				// end function requests_cb2()
	}

var r1_text = "<?php print get_text('Patient');?>";
var r2_text = "<?php print get_text('Phone');?>";
var r3_text = "<?php print get_text('Contact');?>";
var r4_text = "<?php print get_text('Scope');?>";
var r5_text = "<?php print get_text('To Address');?>";
var r6_text = "<?php print get_text('Postcode');?>";
var r7_text = "<?php print get_text('Request Date');?>";
var r8_text = "<?php print get_text('Pickup');?>";
var r9_text = "<?php print get_text('Arrival');?>";
var r10_text = "<?php print get_text('Status');?>";
var r11_text = "<?php print get_text('Updated');?>";
var r12_text = "<?php print get_text('By');?>";
var r13_text = "<?php print get_text('ID');?>";
var changed_req_sort = false;
var req_direct = "ASC";
var req_field = "request_date";
var req_id = "r7";
var req_header = "<?php print get_text('Request Date');?>";

function set_req_headers(id, header_text, the_bull) {
	if(id == "r1") {
		window.r1_text = header_text + the_bull;
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";		
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";		
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r2") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = header_text + the_bull;
		window.r3_text = "<?php print get_text('Contact');?>";		
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";		
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r3") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = header_text + the_bull;		
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";		
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r4") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";		
		window.r4_text = header_text + the_bull;
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";		
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r5") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";		
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = header_text + the_bull;
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";		
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r6") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";		
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = header_text + the_bull;
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r7") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";		
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = header_text + the_bull;
		window.r8_text = "<?php print get_text('Pickup');?>";
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r8") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";		
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = header_text + the_bull;
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r9") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";
		window.r9_text = header_text + the_bull;
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r10") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = header_text + the_bull;
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r11") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = header_text + the_bull;
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r12") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = header_text + the_bull;
		window.r13_text = "<?php print get_text('ID');?>";
		} else if(id == "r13") {
		window.r1_text = "<?php print get_text('Patient');?>";
		window.r2_text = "<?php print get_text('Phone');?>";
		window.r3_text = "<?php print get_text('Contact');?>";
		window.r4_text = "<?php print get_text('Scope');?>";
		window.r5_text = "<?php print get_text('To Address');?>";
		window.r6_text = "<?php print get_text('Postcode');?>";
		window.r7_text = "<?php print get_text('Request Date');?>";
		window.r8_text = "<?php print get_text('Pickup');?>";
		window.r9_text = "<?php print get_text('Arrival');?>";
		window.r10_text = "<?php print get_text('Status');?>";
		window.r11_text = "<?php print get_text('Updated');?>";
		window.r12_text = "<?php print get_text('By');?>";
		window.r13_text = header_text + the_bull;
		}
	}
	
function do_req_sort(id, field, header_text) {
	window.changed_req_sort = true;
	window.req_last_display = 0;
	if(window.req_field == field) {
		if(window.req_direct == "ASC") {
			window.req_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.req_header = header_text;
			window.req_field = field;
			set_req_headers(id, header_text, the_bull);
			} else if(window.req_direct == "DESC") { 
			window.req_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.req_header = header_text; 
			window.req_field = field;
			set_req_headers(id, header_text, the_bull);
			}
		} else {
		$(req_id).innerHTML = req_header;
		window.req_field = field;
		window.req_direct = "ASC";
		window.req_id = id;
		window.req_header = header_text;
		var the_bull = "&#9650";
		set_req_headers(id, header_text, the_bull);
		}
	get_requests(field, req_direct);
	return true;
	}
	
function requests_get() {
	msgs_interval = window.setInterval('do_requests_loop()', 60000);
	}
	
function do_requests_loop() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = "./portal/ajax/list_requests.php?id=<?php print $_SESSION['user_id'];?>&sort="+window.req_field+"&dir="+window.req_direct+"&showall=" + showall + "&version=" + randomnumber+"&q="+sessID;
	sendRequest (url, requests_cb2, "");
	}
	
function get_requests(sort, dir) {
	if(msgs_interval && !changed_showhide) {
		return;
		}
	changed_showhide = false;
	msgs_interval = false;
	window.clearInterval(msgs_interval);
	window.reqFin = false;
	var outputtext = "";
	if(sort != window.req_field) {
		window.req_field = sort;
		}
	if(dir != window.req_direct) {
		window.req_direct = dir;
		}
	if($('all_requests').innerHTML == "") {
		$('all_requests').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = "./portal/ajax/list_requests.php?id=<?php print $_SESSION['user_id'];?>&sort=" + window.req_field + "&dir=" + window.req_direct + "&showall=" + showall + "&version=" + randomnumber+"&q="+sessID;
	sendRequest (url,requestlist_cb, "");
	function requestlist_cb(req) {
		var i = 1;
		var req_id = 0;
		var the_requests = JSON.decode(req.responseText);
		if(!the_requests && doDebug) { alert(req.responseText); }
		if((the_requests[0]) && (the_requests[0][0] == "No Current Requests")) {
			for(var key in reqmarkers) {
				if(reqmarkers[key]) {map.removeLayer(reqmarkers[key]);}
				}
			outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Open Requests. You can show closed request by clicking \"Show Closed\"........</marquee>";
			$('all_requests').innerHTML = outputtext;
			window.latest_facility = 0;
			} else {
			var outputtext = "<TABLE id='requeststable' class='cruises scrollable' style='width: " + window.listWidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: " + window.listWidth + "px;'>";
			outputtext += "<TH id='r13' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('ID');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'id', '<?php print get_text('ID');?>')\">" + window.r13_text + "</TH>";
			outputtext += "<TH id='r1' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Patient');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'patient', '<?php print get_text('Patient');?>')\">" + window.r1_text + "</TH>";
			outputtext += "<TH id='r2' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Phone');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'phone', '<?php print get_text('Phone');?>')\">" + window.r2_text + "</TH>";
			outputtext += "<TH id='r3' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Contact');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'contact', '<?php print get_text('Contact');?>')\">" + window.r3_text + "</TH>";
			outputtext += "<TH id='r4' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Scope');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'scope', '<?php print get_text('Scope');?>')\">" + window.r4_text + "</TH>";
			outputtext += "<TH id='r5' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('To Address');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'toaddress', '<?php print get_text('To Address');?>')\">" + window.r5_text + "</TH>";
			outputtext += "<TH id='r6' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Postcode');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'postcode', '<?php print get_text('Postcode');?>')\">" + window.r6_text + "</TH>";
			outputtext += "<TH id='r7' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Request Date');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'requestdate', '<?php print get_text('Request Date');?>')\">" + window.r7_text + "</TH>";
			outputtext += "<TH id='r8' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Pickup');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'pickup', '<?php print get_text('Pickup');?>')\">" + window.r8_text + "</TH>";
			outputtext += "<TH id='r9' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Arrival');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'arrival', '<?php print get_text('Arrival');?>')\">" + window.r9_text + "</TH>";
			outputtext += "<TH id='r10' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Status');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'status', '<?php print get_text('Status');?>')\">" + window.r10_text + "</TH>";
			outputtext += "<TH id='r11' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Updated');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'updated', '<?php print get_text('Updated');?>')\">" + window.r11_text + "</TH>";
			outputtext += "<TH id='r12' class='plain_listheader' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('By');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_req_sort(this.id, 'by', '<?php print get_text('By');?>')\">" + window.r12_text + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for (var key = 0; key < the_requests.length; key++) { 
				$('export_but').style.display = "inline-block";						
				var req_id = the_requests[key][0];
				if((the_requests[key][16] == "Open") || (the_requests[key][16] == "Tentative") || (the_requests[key][16] == "Accepted")) {
					var canedit = 1;
					} else {
					var canedit = 0;
					}
					outputtext += "<TR class='list_row' title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + ";' onClick='myClick(" + req_id + ", " + canedit + ", " + the_requests[key][35] + ");'>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(4, the_requests[key][0], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(20, the_requests[key][2], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(16, the_requests[key][3], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(20, the_requests[key][4], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(25, the_requests[key][13], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(25, the_requests[key][31], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(12, the_requests[key][32], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(12, the_requests[key][18], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(8, the_requests[key][33], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(8, the_requests[key][34], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(15, the_requests[key][16], "\u00a0") + "</TD>";	
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(12, the_requests[key][25], "\u00a0") + "</TD>";
					outputtext += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + pad(12, the_requests[key][26], "\u00a0") + "</TD>";
					outputtext += "</TR>";
					if(window.requests_updated[req_id]) {
						if(window.requests_updated[req_id] != the_requests[key][9]) {
							window.do_req_update = true;
							} else {
							window.do_req_update = false;
							}
						} else {
						window.requests_updated[req_id] = the_requests[key][9];
						window.do_req_update = true;
						}	
					request_number = req_id;
					i++;					
					}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {	
				if(window.req_last_display == 0) {
					$('all_requests').innerHTML = outputtext;
					window.latest_request = request_number;
					} else {
					if((request_number != window.latest_request) || (window.do_req_update == true) || (window.changed_req_sort == true)) {
						$('all_requests').innerHTML = outputtext;
						window.latest_request = request_number;
						}
					}
				var reqtbl = document.getElementById('requeststable');
				if(reqtbl) {
					var headerRow = reqtbl.rows[0];
					var tableRow = reqtbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
						if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
						if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";}
						if(tableRow.cells[6] && headerRow.cells[6]) {headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";}
						if(tableRow.cells[7] && headerRow.cells[7]) {headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";}
						if(tableRow.cells[8] && headerRow.cells[8]) {headerRow.cells[8].style.width = tableRow.cells[8].clientWidth - 4 + "px";}
						if(tableRow.cells[9] && headerRow.cells[9]) {headerRow.cells[9].style.width = tableRow.cells[9].clientWidth - 4 + "px";}
						if(tableRow.cells[10] && headerRow.cells[10]) {headerRow.cells[10].style.width = tableRow.cells[10].clientWidth - 4 + "px";}
						if(tableRow.cells[11] && headerRow.cells[11]) {headerRow.cells[11].style.width = tableRow.cells[11].clientWidth - 4 + "px";}
						if(tableRow.cells[12] && headerRow.cells[12]) {headerRow.cells[12].style.width = tableRow.cells[12].clientWidth - 4 + "px";}
						} else {
						var cellwidthBase = window.listWidth / 52;
						reqcell1 = cellwidthBase * 2;
						reqcell2 = cellwidthBase * 4;
						reqcell3 = cellwidthBase * 4;
						reqcell4 = cellwidthBase * 4;
						reqcell5 = cellwidthBase * 5;
						reqcell6 = cellwidthBase * 6;
						reqcell7 = cellwidthBase * 5;
						reqcell8 = cellwidthBase * 4;
						reqcell9 = cellwidthBase * 4;
						reqcell10 = cellwidthBase * 4;
						reqcell11 = cellwidthBase * 4;
						reqcell12 = cellwidthBase * 3;
						reqcell13 = cellwidthBase * 3;
						headerRow.cells[0].style.width = reqcell1 + "px";
						headerRow.cells[1].style.width = reqcell2 + "px";
						headerRow.cells[2].style.width = reqcell3 + "px";
						headerRow.cells[3].style.width = reqcell4 + "px";						
						headerRow.cells[4].style.width = reqcell5 + "px";
						headerRow.cells[5].style.width = reqcell6 + "px";
						headerRow.cells[6].style.width = reqcell7 + "px";
						headerRow.cells[7].style.width = reqcell8 + "px";
						headerRow.cells[8].style.width = reqcell9 + "px";
						headerRow.cells[9].style.width = reqcell10 + "px";						
						headerRow.cells[10].style.width = reqcell11 + "px";
						headerRow.cells[11].style.width = reqcell12 + "px";	
						headerRow.cells[12].style.width = reqcell13 + "px";	
						}
					if(getHeaderHeight(headerRow) >= 20) {
						var theRow = reqtbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						var no5 = theRow.insertCell(4);
						var no6 = theRow.insertCell(5);
						var no7 = theRow.insertCell(6);
						var no8 = theRow.insertCell(7);
						var no9 = theRow.insertCell(8);
						var no10 = theRow.insertCell(9);
						var no11 = theRow.insertCell(10);
						var no12 = theRow.insertCell(11);
						var no13 = theRow.insertCell(12);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						no5.innerHTML = " ";
						no6.innerHTML = " ";
						no7.innerHTML = " ";
						no8.innerHTML = " ";
						no9.innerHTML = " ";
						no10.innerHTML = " ";
						no11.innerHTML = " ";
						no12.innerHTML = " ";
						no13.innerHTML = " ";
						}
					}
				window.reqFin = true;
				requests_get();
				},500);
			}
		}				// end function facilitylist_cb()
	}				// end function load_facilitylist()	

function requestlist_setwidths() {
	var viewableRow = 1;
	var reqtbl = document.getElementById('requeststable');
	var headerRow = reqtbl.rows[0];
	for (i = 1; i < reqtbl.rows.length; i++) {
		if(!isViewable(reqtbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	if(i != reqtbl.rows.length) {
		var tableRow = reqtbl.rows[viewableRow];
		if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 4 + "px";}
		if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 4 + "px";}
		if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 4 + "px";}
		if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 4 + "px";}
		if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 4 + "px";}
		if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 4 + "px";}
		if(tableRow.cells[6] && headerRow.cells[6]) {headerRow.cells[6].style.width = tableRow.cells[6].clientWidth - 4 + "px";}
		if(tableRow.cells[7] && headerRow.cells[7]) {headerRow.cells[7].style.width = tableRow.cells[7].clientWidth - 4 + "px";}
		if(tableRow.cells[8] && headerRow.cells[8]) {headerRow.cells[8].style.width = tableRow.cells[8].clientWidth - 4 + "px";}
		if(tableRow.cells[9] && headerRow.cells[9]) {headerRow.cells[9].style.width = tableRow.cells[9].clientWidth - 4 + "px";}
		if(tableRow.cells[10] && headerRow.cells[10]) {headerRow.cells[10].style.width = tableRow.cells[10].clientWidth - 4 + "px";}
		if(tableRow.cells[11] && headerRow.cells[11]) {headerRow.cells[11].style.width = tableRow.cells[11].clientWidth - 4 + "px";}
		if(tableRow.cells[12] && headerRow.cells[12]) {headerRow.cells[12].style.width = tableRow.cells[12].clientWidth - 4 + "px";}
		} else {
		var cellwidthBase = window.listWidth / 48;
		reqcell1 = cellwidthBase * 2;
		reqcell2 = cellwidthBase * 4;
		reqcell3 = cellwidthBase * 4;
		reqcell4 = cellwidthBase * 4;
		reqcell5 = cellwidthBase * 5;
		reqcell6 = cellwidthBase * 6;
		reqcell7 = cellwidthBase * 5;
		reqcell8 = cellwidthBase * 4;
		reqcell9 = cellwidthBase * 4;
		reqcell10 = cellwidthBase * 4;
		reqcell11 = cellwidthBase * 4;
		reqcell12 = cellwidthBase * 3;
		reqcell13 = cellwidthBase * 3;
		headerRow.cells[0].style.width = reqcell1 + "px";
		headerRow.cells[1].style.width = reqcell2 + "px";
		headerRow.cells[2].style.width = reqcell3 + "px";
		headerRow.cells[3].style.width = reqcell4 + "px";						
		headerRow.cells[4].style.width = reqcell5 + "px";
		headerRow.cells[5].style.width = reqcell6 + "px";
		headerRow.cells[6].style.width = reqcell7 + "px";
		headerRow.cells[7].style.width = reqcell8 + "px";
		headerRow.cells[8].style.width = reqcell9 + "px";
		headerRow.cells[9].style.width = reqcell10 + "px";						
		headerRow.cells[10].style.width = reqcell11 + "px";
		headerRow.cells[11].style.width = reqcell12 + "px";	
		headerRow.cells[12].style.width = reqcell13 + "px";	
		}
	if(getHeaderHeight(headerRow) >= 20) {
		var theRow = reqtbl.insertRow(1);
		theRow.style.height = "20px";
		var no1 = theRow.insertCell(0);
		var no2 = theRow.insertCell(1);
		var no3 = theRow.insertCell(2);
		var no4 = theRow.insertCell(3);
		var no5 = theRow.insertCell(4);
		var no6 = theRow.insertCell(5);
		var no7 = theRow.insertCell(6);
		var no8 = theRow.insertCell(7);
		var no9 = theRow.insertCell(8);
		var no10 = theRow.insertCell(9);
		var no11 = theRow.insertCell(10);
		var no12 = theRow.insertCell(11);
		var no13 = theRow.insertCell(12);
		no1.innerHTML = " ";
		no2.innerHTML = " ";
		no3.innerHTML = " ";
		no4.innerHTML = " ";
		no5.innerHTML = " ";
		no6.innerHTML = " ";
		no7.innerHTML = " ";
		no8.innerHTML = " ";
		no9.innerHTML = " ";
		no10.innerHTML = " ";
		no11.innerHTML = " ";
		no12.innerHTML = " ";
		no13.innerHTML = " ";
		}
	}	
	
function do_filelist() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/file_list.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
	sendRequest (url, file_cb, "");
	function file_cb(req) {
		var the_files=req.responseText;
		$('file_list').innerHTML = the_files;
		}
	}

function markers_cb2(req) {
	var the_markers=JSON.decode(req.responseText);
	if(the_markers[1]) {
		for (var key = 0; key < the_markers[1].length; key++) {
			if(key == 0 && the_markers[1][key] == "-1") {
				return;
				}
			var theTicketId = the_markers[1][key].id;
			var the_lat = the_markers[1][key].lat;
			var the_lng = the_markers[1][key].lng;	
			var the_scope = the_markers[1][key].scope;
			var the_description = the_markers[1][key].description;
			var the_assoc_request = the_markers[1][key].request;
			var theStatus = the_markers[1][key].status;
			var info_t = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Request');?> " + the_assoc_request + "</SPAN></CENTER><BR />";
			info_t += "<CENTER><TABLE BORDER=1 style='font-size: 0.8em;' WIDTH='80%'>";
			info_t += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Status');?></B></TD><TD class='td_data'>" + statusVals[theStatus] + "</TD></TR>";
			info_t += "<TR class='even'><TD class='td_label'><B><?php print get_text('Job Title');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
			info_t += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Job Description');?></B></TD><TD class='td_data'>" + the_description + "</TD></TR>";
			if(the_markers[1][key].responders) {
				for (var elements = 0; elements < the_markers[1][key].responders.length; elements++) { 	
					var tr_handle = the_markers[1][key].responders[elements].handle;	
					info_t += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Responder');?></B></TD><TD class='td_data'>" + tr_handle + "</TD></TR>";
					}
				}
			info_t += "</TABLE></CENTER><BR />";
			info_t += "<DIV style='width: 100%; text-align: center;'><SPAN id='theBut' class='plain' style='vertical-align: middle; text-align: center; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_viewwindow(" + the_assoc_request + "); tmarkers[" + theTicketId + "].closePopup();'>Open</SPAN></DIV><BR /></DIV>";
			var tmarker = createMarker(the_lat, the_lng, theStatus, the_assoc_request, info_t, "Ticket for " + the_scope);
			if(tmarker) {
				tmarker.addTo(map);
				tmarkers[theTicketId] = tmarker;
				}
			if(the_markers[1][key].responders) {
				for(var elements in the_markers[1][key].responders) {
					var r_lat = the_markers[1][key].responders[elements].lat;
					var r_lng = the_markers[1][key].responders[elements].lng;	
					var r_id = the_markers[1][key].responders[elements].handle;
					var r_handle = the_markers[1][key].responders[elements].id;
					var r_job = the_markers[1][key].responders[elements].jobtitle;
					var info_r = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Responder');?></SPAN></CENTER><BR />";
					info_r += "<CENTER><TABLE BORDER=1 style='font-size: 0.8em;' WIDTH='80%'>";
					info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('On Job');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
					info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Responder Handle');?></B></TD><TD class='td_data'>" + r_handle + "</TD></TR>";
					info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Assigned to Job');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
					info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Job Description');?></B></TD><TD class='td_data'>" + the_description + "</TD></TR></TABLE></CENTER><BR /><BR /></DIV>";
					var rmarker = createMarker(r_lat, r_lng, 10, r_handle, info_r, r_handle + " assigned to " + r_job);
					if(rmarker) {
						rmarker.addTo(map);
						rmarkers[r_id] = rmarker;
						}
					}
				}
			}
		}
	if(the_markers[0]) {
		for (var key2 = 0; key2 < the_markers[0].length; key2++) {
			var req_id = parseInt(the_markers[0][key2].id);
			var theSym = the_markers[0][key2].id;
			var req_lat = the_markers[0][key2].lat;
			var req_lng = the_markers[0][key2].lng;	
			var req_scope = the_markers[0][key2].scope;
			var req_description = the_markers[0][key2].description;
			var canedit = the_markers[0][key2].canedit;
			var theStatus = the_markers[0][key2].status;
			var info_req = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Request');?> " + req_id + "</SPAN></CENTER><BR />";
			info_req += "<CENTER><TABLE BORDER=1 style='font-size: 0.8em;' WIDTH='80%'>";
			info_req += "<TR class='even'><TD class='td_label'><B><?php print get_text('Job Title');?></B></TD><TD class='td_data'>" + req_scope + "</TD></TR>";
			info_req += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Status');?></B></TD><TD class='td_data'>" + statusVals[theStatus] + "</TD></TR>";
			info_req += "<TR class='even'><TD class='td_label'><B><?php print get_text('Job Description');?></B></TD><TD class='td_data'>" + req_description + "</TD></TR>";
			info_req += "</TABLE></CENTER><BR />";
			info_req += "<DIV style='width: 100%; text-align: center;'><SPAN id='theBut' class='plain' style='vertical-align: middle; text-align: center; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_window(" + req_id + "); reqmarkers[" + req_id + "].closePopup();'>Open</SPAN></DIV><BR /></DIV>";
			var reqmarker = createMarker(req_lat, req_lng, theStatus, theSym, info_req, "Request " + req_scope);
			if(reqmarker) {
				reqmarker.addTo(map);
				reqmarkers[req_id] = reqmarker;
				}
			}
		}
	}
	
function markers_get() {
	markers_interval = window.setInterval('do_markers_loop()', 60000);
	}	
	
function do_markers_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/list_ticketsandresponders.php?id=<?php print $_SESSION['user_id'];?>&showall=" + showall + "&version=" + randomnumber;
	sendRequest (url, markers_cb2, "");
	}
	
function get_the_markers() {
	if(markers_interval && !changed_mkrshowhide) {
		return;
		}
	markers_interval = null;
	changed_mkrshowhide = false;	
	window.clearInterval(markers_interval);
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/list_ticketsandresponders.php?id=<?php print $_SESSION['user_id'];?>&showall=" + showall + "&version=" + randomnumber;
	sendRequest (url, markers_cb, "");
	function markers_cb(req) {
		var the_markers=JSON.decode(req.responseText);
		if(the_markers[1]) {
			for (var key = 0; key < the_markers[1].length; key++) {
				if(key == 0 && the_markers[1][key] == "-1") {
					return;
					}
				var theTicketId = the_markers[1][key].id;
				var the_lat = the_markers[1][key].lat;
				var the_lng = the_markers[1][key].lng;	
				var the_scope = the_markers[1][key].scope;
				var the_description = the_markers[1][key].description;
				var the_assoc_request = the_markers[1][key].request;
				var theStatus = the_markers[1][key].status;
				var info_t = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Request');?> " + the_assoc_request + "</SPAN></CENTER><BR />";
				info_t += "<CENTER><TABLE BORDER=1 style='font-size: 0.8em;' WIDTH='80%'>";
				info_t += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Status');?></B></TD><TD class='td_data'>" + statusVals[theStatus] + "</TD></TR>";
				info_t += "<TR class='even'><TD class='td_label'><B><?php print get_text('Job Title');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
				info_t += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Job Description');?></B></TD><TD class='td_data'>" + the_description + "</TD></TR>";
				if(the_markers[1][key].responders) {
					for (var elements = 0; elements < the_markers[1][key].responders.length; elements++) { 	
						var tr_handle = the_markers[1][key].responders[elements].handle;	
						info_t += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Responder');?></B></TD><TD class='td_data'>" + tr_handle + "</TD></TR>";
						}
					}
				info_t += "</TABLE></CENTER><BR />";
				info_t += "<DIV style='width: 100%; text-align: center;'><SPAN id='theBut' class='plain' style='vertical-align: middle; text-align: center; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_viewwindow(" + the_assoc_request + "); tmarkers[" + theTicketId + "].closePopup();'>Open</SPAN></DIV><BR /></DIV>";
				var tmarker = createMarker(the_lat, the_lng, theStatus, the_assoc_request, info_t, "Ticket for " + the_scope);
				if(tmarker) {
					tmarker.addTo(map);
					tmarkers[theTicketId] = tmarker;
					}
				if(the_markers[1][key].responders) {
					for(var elements in the_markers[1][key].responders) {
						var r_lat = the_markers[1][key].responders[elements].lat;
						var r_lng = the_markers[1][key].responders[elements].lng;	
						var r_id = the_markers[1][key].responders[elements].handle;
						var r_handle = the_markers[1][key].responders[elements].id;
						var r_job = the_markers[1][key].responders[elements].jobtitle;
						var info_r = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Responder');?></SPAN></CENTER><BR />";
						info_r += "<CENTER><TABLE BORDER=1 style='font-size: 0.8em;' WIDTH='80%'>";
						info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('On Job');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
						info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Responder Handle');?></B></TD><TD class='td_data'>" + r_handle + "</TD></TR>";
						info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Assigned to Job');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
						info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Job Description');?></B></TD><TD class='td_data'>" + the_description + "</TD></TR></TABLE></CENTER><BR /><BR /></DIV>";
						var rmarker = createMarker(r_lat, r_lng, 10, r_handle, info_r, r_handle + " assigned to " + r_job);
						if(rmarker) {
							rmarker.addTo(map);
							rmarkers[r_id] = rmarker;
							}
						}
					}
				}
			}
		if(the_markers[0]) {
			for (var key2 = 0; key2 < the_markers[0].length; key2++) {
				var req_id = parseInt(the_markers[0][key2].id);
				var theSym = the_markers[0][key2].id;
				var req_lat = the_markers[0][key2].lat;
				var req_lng = the_markers[0][key2].lng;	
				var req_scope = the_markers[0][key2].scope;
				var req_description = the_markers[0][key2].description;
				var canedit = the_markers[0][key2].canedit;
				var theStatus = the_markers[0][key2].status;
				var info_req = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Request');?> " + req_id + "</SPAN></CENTER><BR />";
				info_req += "<CENTER><TABLE BORDER=1 style='font-size: 0.8em;' WIDTH='80%'>";
				info_req += "<TR class='even'><TD class='td_label'><B><?php print get_text('Job Title');?></B></TD><TD class='td_data'>" + req_scope + "</TD></TR>";
				info_req += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Status');?></B></TD><TD class='td_data'>" + statusVals[theStatus] + "</TD></TR>";
				info_req += "<TR class='even'><TD class='td_label'><B><?php print get_text('Job Description');?></B></TD><TD class='td_data'>" + req_description + "</TD></TR>";
				info_req += "</TABLE></CENTER><BR />";
				info_req += "<DIV style='width: 100%; text-align: center;'><SPAN id='theBut' class='plain' style='vertical-align: middle; text-align: center; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_window(" + req_id + "); reqmarkers[" + req_id + "].closePopup();'>Open</SPAN></DIV><BR /></DIV>";
				var reqmarker = createMarker(req_lat, req_lng, theStatus, theSym, info_req, "Request " + req_scope);
				if(reqmarker) {
					reqmarker.addTo(map);
					reqmarkers[req_id] = reqmarker;
					}
				}
			}
		}
	markers_get();
	}	

function summary_cb2(req) {
	var the_summary=JSON.decode(req.responseText);
	var the_output = "<TABLE style='font-size: 1.2em; text-align: center; border: 1px solid #707070; cursor: initial;'>";
	the_output += "<TR style='font-size: 0.8em;'><TH style='background-color: #707070; border: 1px solid #707070; cursor: initial;'>&nbsp;</TH><TH style='border: 1px solid #707070;'><?php print get_text('Week');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Month');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Year');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Total')?></TH><TR>";
	the_output += "<TR><TD style='text-align: left; border: 1px solid #707070; cursor: initial;'><?php print get_text('Requests');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[0] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[1] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[2] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[3] + "</TD></TR>";
	the_output += "<TR><TD style='text-align: left; border: 1px solid #707070; cursor: initial;'><?php print get_text('Accepted');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[4] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[5] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[6] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[7] + "</TD></TR>";
	the_output += "<TR><TD style='text-align: left; border: 1px solid #707070; cursor: initial;'><?php print get_text('Declined');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[8] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[9] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[10] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[11] + "</TD></TR>";
	the_output += "<TR><TD style='text-align: left; border: 1px solid #707070; cursor: initial;'><?php print get_text('Closed');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[12] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[13] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[14] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[15] + "</TD></TR>";
	the_output += "</TABLE>";
	$('summary_table').innerHTML = the_output;		
	}
	
function summary_get() {
	summary_interval = window.setInterval('do_summary_loop()', 60000);
	}	
	
function do_summary_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/requests_summary?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
	sendRequest (url, summary_cb2, "");
	}
	
function get_summary() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/requests_summary.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
	sendRequest (url, summary_cb, "");
	function summary_cb(req) {
		var the_summary=JSON.decode(req.responseText);
		var the_output = "<TABLE style='font-size: 1.2em; text-align: center; border: 1px solid #707070;'>";
		the_output += "<TR style='font-size: 0.8em;'><TH style='background-color: #707070; border: 1px solid #707070;'>&nbsp;</TH><TH style='border: 1px solid #707070;'><?php print get_text('Week');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Month');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Year');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Total');?></TH><TR>";
		the_output += "<TR><TD style='text-align: left; border: 1px solid #707070; cursor: initial;'><?php print get_text('Requests');?></TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[0] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[1] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[2] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[3] + "</TD></TR>";
		the_output += "<TR><TD style='text-align: left; border: 1px solid #707070; cursor: initial;'><?php print get_text('Accepted');?></TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[4] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[5] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[6] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[7] + "</TD></TR>";
		the_output += "<TR><TD style='text-align: left; border: 1px solid #707070; cursor: initial;'><?php print get_text('Declined');?></TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[8] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[9] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[10] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[11] + "</TD></TR>";
		the_output += "<TR><TD style='text-align: left; border: 1px solid #707070; cursor: initial;'><?php print get_text('Closed');?></TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[12] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[13] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[14] + "</TD><TD style='border: 1px solid #707070; cursor: initial;'>" + the_summary[15] + "</TD></TR>";
		the_output += "</TABLE>";
		$('summary_table').innerHTML = the_output;		
		}
	summary_get();
	}	
	
function do_lat (lat) {
	document.add.frm_lat.value=lat;			// 9/9/08
	}
function do_lng (lng) {
	document.add.frm_lng.value=lng;
	}

function do_fac_to_loc(text, index){			// 9/22/09
	var curr_lat = fac_lat[index];
	var curr_lng = fac_lng[index];
	var curr_street = fac_street[index];
	var curr_city = fac_city[index];
	var curr_state = fac_state[index];
	do_lat(curr_lat);
	do_lng(curr_lng);
	pt_to_map(document.forms['add'], curr_lat, curr_lng);			// show it
	document.add.fac_street.value = curr_street;
	document.add.fac_city.value = curr_city;
	document.add.fac_state.value = curr_state;	
	}					// end function do_fac_to_loc
	
function pt_to_map (my_form, lat, lng) {
	if(!$('map_canvas')) {return; }
	if(marker) {map.removeLayer(marker);}
	if(myMarker) {map.removeLayer(myMarker);}
	var theLat = parseFloat(lat).toFixed(6);
	var theLng = parseFloat(lng).toFixed(6);
	my_form.frm_lat.value=theLat;	
	my_form.frm_lng.value=theLng;		
	my_form.show_lat.value=do_lat_fmt(theLat);
	my_form.show_lng.value=do_lng_fmt(theLng);	
	var loc = <?php print get_variable('locale');?>;
	if(loc == 0) { my_form.frm_ngs.value=LLtoUSNG(theLat, theLng, 5); }
	if(loc == 1) { my_form.frm_ngs.value=LLtoOSGB(theLat, theLng, 5); }
	if(loc == 2) { my_form.frm_ngs.value=LLtoUTM(theLat, theLng, 5); }
	var iconurl = "./our_icons/yellow.png";
	icon = new baseIcon({iconUrl: iconurl});	
	marker = L.marker([theLat, theLng], {icon: icon});
	marker.addTo(map);
	map.setView([theLat, theLng], 16);
	}				// end function pt_to_map ()
	
function loc_lkup(my_form) {		   						// 7/5/10
	if(!$('map_canvas')) {return; }
	var theLat = my_form.frm_lat.value;
	var theLng = my_form.frm_lng.value;	
	if(my_form.frm_street.value.trim() != "" && my_form.frm_city.value.trim() == "") {
		var theCity = my_form.frm_street.value.trim();
		var theStreet = "";
		} else {
		var theCity = my_form.frm_city.value.trim();
		var theStreet = my_form.frm_street.value.trim();
		}
	if (theCity == "" || my_form.frm_state.value.trim() == "") {
		alert ("City and State are required for location lookup.");
		return false;
		}
	var myAddress = theStreet + ", " + theCity + " " + my_form.frm_state.value.trim();
	control.options.geocoder.geocode(myAddress, function(results) {
		if(!results[0]) {
			pt_to_map (my_form, theLat, theLng);
			return;
			}
		var r = results[0]['center'];
		theLat = r.lat;
		theLng = r.lng;
		pt_to_map (my_form, theLat, theLng);
		});
	}				// end function loc_lkup()
	
function createMarker(lat, lon, color, sym, info, title) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var point = new L.LatLng(lat, lon);
		var iconStr = sym;
		var iconurl = "./our_icons/gen_portal_icon.php?icon=" + escape(window.icons[color]) + "&light=" + escape(window.textlight[color]) + "&text=" + iconStr;
		icon = new baseIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon, title: title}).bindPopup(info);
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		markers.push(marker);
		bounds.extend(point);
		map.fitBounds(bounds);
		setTimeout(function() {
			mapCenter = map.getCenter();
			mapZoom = map.getZoom();
			},500);
		return marker;
		} else {
		return false;
		}
	}
	
function clearMarkers() {
	for (var i = 0; i < markers.length; i++) {
		map.removeLayer(markers[i]);
		}
	}

function do_logout() {
	document.gout_form.submit();
	}	

function toggle_closed() {
	if(showall == "yes") {
		changed_showhide = true;
		changed_mkrshowhide = true;
		showall = "no";
		$('showhide_but').innerHTML = "<?php print get_text('Show Closed');?>";
		do_req_update = true;
		clearMarkers();
		get_requests(window.req_field, window.req_direct);
		get_the_markers();
		} else {
		changed_showhide = true;
		changed_mkrshowhide = true;
		showall = "yes";
		$('showhide_but').innerHTML = "<?php print get_text('Hide Closed');?>";
		do_req_update = true;
		clearMarkers();
		get_requests(window.req_field, window.req_direct);
		get_the_markers();
		}
	}
		
function do_unload() {
	window.clearInterval(summary_interval);
	window.clearInterval(msgs_interval);
	window.clearInterval(markers_interval);
	window.clearInterval(c_interval);
	}
<?php
$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$rec_fac_menu = "<SELECT NAME='frm_rec_fac'>";
$rec_fac_menu .= "<OPTION VALUE=0 selected>Receiving Facility</OPTION>";
while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
		$rec_fac_menu .= "<OPTION VALUE=" . $row_fc['id'] . ">" . shorten($row_fc['name'], 30) . "</OPTION>";
		}
$rec_fac_menu .= "<SELECT>";

$query_fc2 = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc2 = mysql_query($query_fc2) or do_error($query_fc2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$orig_fac_menu = "<SELECT NAME='frm_orig_fac' onChange='do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())'>";
$orig_fac_menu .= "<OPTION VALUE=0 selected>Originating Facility</OPTION>";
while ($row_fc2 = mysql_fetch_array($result_fc2, MYSQL_ASSOC)) {
		$orig_fac_menu .= "<OPTION VALUE=" . $row_fc2['id'] . ">" . shorten($row_fc2['name'], 30) . "</OPTION>";
		$street = ($row_fc2['street'] != "") ? $row_fc2['street'] : "Empty";
		$city = ($row_fc2['city'] != "") ? $row_fc2['city'] : "Empty";
		$state = ($row_fc2['state'] != "") ? $row_fc2['state'] : "Empty";
		print "\tfac_lat[" . $row_fc2['id'] . "] = " . $row_fc2['lat'] . " ;\n";
		print "\tfac_lng[" . $row_fc2['id'] . "] = " . $row_fc2['lng'] . " ;\n";	
		print "\tfac_street[" . $row_fc2['id'] . "] = '" . $street . "' ;\n";	
		print "\tfac_city[" . $row_fc2['id'] . "] = '" . $city . "' ;\n";
		print "\tfac_state[" . $row_fc2['id'] . "] = '" . $state . "' ;\n";		
		}
$orig_fac_menu .= "<SELECT>";

?>	
</SCRIPT>
</HEAD>
<?php


if((!isset($_SESSION)) && (empty($_POST))) {
	print "Not Logged in";
} elseif((isset($_SESSION)) && (empty($_POST))) {
	$now = time() - (intval(get_variable('delta_mins')*60));
?>

	<BODY style='overflow: hidden;' onLoad="out_frames();" onUnload='do_unload();'>
		<FORM NAME="go" action="#" TARGET = "main"></FORM>
		<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
		<DIV id='outer' style='position: absolute; top: 0px; text-align: center;'>
			<DIV id='tophalf' style='position: relative; top: 0px; width: 100%; font-size: 1em; z-index: 998; overflow: hidden; cursor: default;'>
				<DIV id='banner' style='background-color: #707070; vertical-align: middle; cursor: default;'><SPAN class='heading' style='font-size: 1.5em; vertical-align: middle; cursor: default;'>Tickets <?php print get_text('Service User');?> <?php print get_text('Portal');?></SPAN>
					<SPAN ID='gout' CLASS='plain' style='position: absolute; right: 60px; font-size: 1em; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_logout()"><?php print get_text('Logout');?></SPAN>
				</DIV>
				<DIV id='controls' style='position: absolute; top: 50px; left: 2%; cursor: initial;'>
					<TABLE WIDTH='100%' HEIGHT='100%' style='font-size: 1em; border: 3px outset #707070; cursor: initial;'>
						<TR style='font-size: 1em; cursor: initial;'>
							<TD WIDTH='50%' style='font-size: 1em; border: 3px outset #707070; vertical-align: top; cursor: initial;'>
								<CENTER>
								<TABLE style='width: 100%; cursor: initial;'>
									<TR style='font-size: 1em; cursor: initial;'>
										<TD style='font-size: 1em; cursor: initial;'><SPAN id='sub_but' CLASS ='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_newreq();"><?php print get_text('New Request');?></SPAN></TD>
									</TR>
									<TR style='font-size: 1em;'>
										<TD style='font-size: 1em; cursor: initial;'><SPAN ID='upload_but' CLASS='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.open('./portal/import_requests.php','Import Requests','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')" TITLE='Import Request from CSV File'><?php print get_text('Import');?></SPAN></TD>
									</TR>
									<TR style='font-size: 1em;'>
										<TD style='font-size: 1em; cursor: initial;'><SPAN ID='export_but' CLASS='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.location.href='./portal/csv_export.php'"><?php print get_text('Export Requests to CSV');?></SPAN></TD>
									</TR>
									<TR style='font-size: 1em;'>
										<TD style='font-size: 1em; cursor: initial;'><SPAN ID='showhide_but' CLASS='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_closed();"><?php print get_text('Show Closed');?></SPAN></TD>
									</TR>
									<TR style='font-size: 1em;'>
										<TD style='font-size: 1em; cursor: initial;'><SPAN ID='profile_but' CLASS='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_passwdchange();"><?php print get_text('Change Password');?></SPAN></TD>
									</TR>
								</TABLE><BR /><BR />
								<DIV style='border: 2px outset #707070; cursor: initial;'>
									<DIV class='heading' style='font-size: 1.1em; cursor: default;'><?php print get_text('Useful Documents');?></DIV><BR />
									<DIV id='file_list' style='font-size: 1em; height: 100%; overflow-y: auto;'></DIV>
								</DIV>
								</CENTER>
							</TD>
							<TD WIDTH='50%' style='font-size: 1em; border: 3px outset #707070; text-align: left; cursor: initial;'>
								<TABLE WIDTH='100%'style=' cursor: initial;'>
									<TR CLASS="heading" style='font-size: 1em;'>
										<TD CLASS='heading' style='font-size: 1.1em; cursor: initial;'><?php print get_text('Contact Us');?></TD>
									<TR>
									<TR style='font-size: 1em; cursor: initial;'>
										<TD style='font-size: 1em; cursor: initial;'>&nbsp;</TD>
									</TR>
										<TD style='font-size: 1em; cursor: initial;'><?php print get_text('Telephone');?>: <?php print get_variable('portal_contact_phone');?></TD>
									</TR>
									<TR style='font-size: 1em; cursor: initial;'>
										<TD style='font-size: 1em; cursor: initial;'><?php print get_text('Email');?>: <?php print get_variable('portal_contact_email');?></TD>
									</TR>
									<TR style='font-size: 1em; cursor: initial;'>
										<TD style='font-size: 1em; cursor: initial;'>&nbsp;</TD>
									</TR>
									<TR style='font-size: 1em; cursor: initial;'>
										<TD style='font-size: 1em; cursor: initial;'>&nbsp;</TD>
									</TR>
									<TR class='heading' style='font-size: 1.1em; cursor: initial;'>
										<TD class='heading' style='font-size: 1.1em; cursor: initial;'>Your Request Statistics - <?php print get_user_name($_SESSION['user_id']);?></TD>
									</TR>
									<TR style='font-size: 1em; cursor: initial;'>
										<TD style='font-size: 1em; cursor: initial;'>&nbsp;</TD>
									</TR>
									<TR>
										<TD id='summary_table' ALIGN='center' style=' cursor: initial;'></TD>
									</TR>
								</TABLE>
							</TD>
						</TR>
					</TABLE>
				</DIV>
				<DIV id='map_wrapper' style='position: absolute; top: 50px; right: 4%; text-align: center;'>
					<DIV id = 'map_canvas' style = 'border: 1px outset #707070; text-align: left;'></DIV>
				</DIV>
			</DIV>
			<DIV id='bottomhalf' style='position: relative; top: 0px; margin: 1.5%; overflow: hidden; z-index: 998; display: block;'>
				<DIV id='requests_list' style='display: block; cursor: default;'>
					<DIV id='color_key' style='height: 18px; cursor: default;'>
						<SPAN id='open' style='background-color: #FFFF00; color: #000000; cursor: default;'><?php print get_text('Open');?></SPAN>
						<SPAN id='tentative' style='background-color: #CC9900; color: #000000; cursor: default;'><?php print get_text('Tentative');?></SPAN>
						<SPAN id='accepted' style='background-color: #33CCFF; color: #000000; cursor: default;'><?php print get_text('Accepted');?></SPAN>
						<SPAN id='resourced' style='background-color: #00FF00; color: #000000; cursor: default;'><?php print get_text('Resourced');?></SPAN>
						<SPAN id='completed' style='background-color: #FFFFFF; color: #00FF00; cursor: default;'><?php print get_text('Completed');?></SPAN>
						<SPAN id='decline' style='background-color: #FF9900; color: #FFFF00; cursor: default;'><?php print get_text('Declined');?></SPAN>
						<SPAN id='closed' style='background-color: #000000; color: #FFFFFF; cursor: default;'><?php print get_text('Closed');?></SPAN>
						<SPAN id='cancelled' style='background-color: #FF0000; color: #FFFF00; cursor: default;'><?php print get_text('Cancelled');?></SPAN>			
					</DIV>			
					<DIV id='list_header' class='heading' style='font-size: 16px; border: 1px outset #000000; vertical-align: middle; height: 18px;'><?php print get_text('Current Requests');?></DIV>
					<DIV class="scrollableContainer" id='the_bottom' style='border: 1px outset #707070;'>
						<DIV class="scrollingArea" id='all_requests'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
					</DIV>
				</DIV>
			</DIV>
		</DIV>
		<FORM METHOD='POST' NAME="gout_form" action="index.php">
		<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
		</FORM>
	<SCRIPT>
	//	setup map-----------------------------------//
	var map;
	var minimap;
	var sortby = '`date`';
	var sort = "DESC";
	var tmarkers = [];	//	Incident markers array
	var reqmarkers = []	//	Request Markers array
	var rmarkers = [];			//	Responder Markers array
	var cmarkers = [];			//	conditions markers array
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
	var mapWidth = viewportwidth * .5;
	var mapHeight = viewportheight * .5;
	var listWidth = viewportwidth * .96;	
	var listHeight = viewportheight * .35;
	var controlsWidth = viewportwidth * .35;
	var controlsHeight = viewportheight * .4;
	var bannerwidth = viewportwidth * .96;
	var outerWidth = viewportwidth * .97;
	$('outer').style.width = outerWidth + "px";
	$('outer').style.height = viewportheight + "px";
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('requests_list').style.width = listWidth + "px";
	$('requests_list').style.height = listHeight + "px";
	$('tophalf').style.width = outerWidth + "px";
	$('tophalf').style.height = viewportheight * .60 + "px";
	$('bottomhalf').style.width = outerWidth + "px";
	$('bottomhalf').style.height = viewportheight * .35 + "px";
	$('bottomhalf').style.textAlign = "center";
	$('controls').style.width = outerWidth * .4 + "px";
	$('controls').style.height = viewportheight * .5 + "px";
	$('map_wrapper').style.width = mapWidth + "px";
	$('map_wrapper').style.height = mapHeight + "px";
	$('banner').style.width = outerWidth + "px";		
	$('banner').style.height = "2em";	
	$('list_header').style.width = bannerwidth + "px";		
	$('list_header').style.height = "2em";	
	$('all_requests').style.width = bannerwidth + "px";
	$('the_bottom').style.width = bannerwidth + "px";	
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = 0;
	var initZoom = "15";
	init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
	map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
	var bounds = map.getBounds();
	var got_points = false;
	mapCenter = map.getCenter();
	mapZoom = map.getZoom();
	</SCRIPT>		
	</BODY>
<?php
	}
?> 

</HTML>
