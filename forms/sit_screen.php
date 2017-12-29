<?php

error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
$units_side_bar_height = .6;
$not_sit = (array_key_exists('id', ($_GET)))?  $_GET['id'] : NULL;
if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}

// set auto-refresh if any mobile units														
$temp = get_variable('auto_poll');
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
		.text-labels {font-size: 2em; font-weight: 700;}
		.leaflet-control-layers-expanded { padding: 10px 10px 10px 10px; color: #333; background-color: #F1F1F1; border: 3px outset #707070;}
		.leaflet-control-layers-expanded .leaflet-control-layers-list {height: auto; display: block; position: relative; margin-bottom: 20px;}
		.centerbuttons {width: 80px; font-size: 1.2em;}
	</STYLE>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/messaging.js"></SCRIPT>
<?php 
if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}	
?>
	<script type="application/x-javascript" src="./js/proj4js.js"></script>
	<script type="application/x-javascript" src="./js/proj4-compressed.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/leaflet.js"></script>
	<script type="application/x-javascript" src="./js/proj4leaflet.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/KML.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/gpx.js"></script>  
	<script type="application/x-javascript" src="./js/osopenspace.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-openweathermap.js"></script>
	<script type="application/x-javascript" src="./js/esri-leaflet.js"></script>
	<script type="application/x-javascript" src="./js/Control.Geocoder.js"></script>
	<script type="application/x-javascript" src="./js/usng.js"></script>
	<script type="application/x-javascript" src="./js/osgb.js"></script>
<?php
	if ($_SESSION['internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
?>
			<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
			<script src="./js/Google.js"></script>
<?php
			}
		}
?>
	<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
	<script type="application/x-javascript" src="./js/geotools2.js"></script>
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var counter = 0;
var pagetimerStart = new Date();
var pagetimerEnd = 0;
var doTime = false;
var incFin = false;
var respFin = false;
var facFin = false;
var logFin = false;
var statSel = false;
var facstatSel = false;
var minimap;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var leftlistwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var i_interval = null;
var r_interval = null;
var f_interval = null;
var b_interval = null;
var c_interval = null;
var s_interval = null;
var log_interval = null;
var latest_logid = 0;
var latest_ticket = 0;
var latest_responder = 0;
var latest_facility = 0;
var latest_log = 0;
var inc_last_display = 0;
var inc_period_changed = 0;
var do_inc_refresh = false;
var do_update = true;
var do_resp_update = true;
var do_resp_refresh = false;
var do_fac_update = true;
var do_log_refresh = false;
var tickets_updated = [];
var responders_updated = [];
var facilities_updated = [];
var inc_period = 0;
var last_disp = 0;
var mapCenter;
var mapZoom;
var isGuest = <?php print $isGuest;?>;
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

var captions = ["Current situation", "Incidents closed today", "Incidents closed yesterday+", "Incidents closed this week", "Incidents closed last week", "Incidents closed last week+", "Incidents closed this month", "Incidents closed last month", "Incidents closed this year", "Incidents closed last year", "Scheduled"];
var heading = captions[inc_period];
heading += " - ";
heading += "<?php print get_variable('map_caption');?>";
		
/* Initial period selection - current tickets, 
	options available 0 (current tickets), 
	1 - Closed today
	2 - Closed Yesterday+
	3 - Closed this week
	4 - Closed last week
	5 - Closed last week+
	6 - Closed this month
	7 - Closed last month
	8 - Closed this year
	9 - Closed last year
*/
var colors = new Array ('odd', 'even');

function set_period(period) {
	window.inc_period = period;
	thelength = document.getElementById('period_select').options.length;
	for(var f = 0; f < thelength; f++) {
		if(document.getElementById('period_select').options[f].value == period) {
			document.getElementById('period_select').options[f].selected = true;
			}
		}
	$('theHeading').innerHTML = window.captions[window.inc_period];
	}
	
function do_responder_refresh() {
	window.do_resp_refresh = true; 
	$('the_rlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	setTimeout(function() {
		load_responderlist(window.resp_field, window.resp_direct);
		},1000);
	}
	
function do_facility_refresh() {
	window.do_fac_refresh = true;
	$('the_flist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	setTimeout(function() {
		load_facilitylist(window.fac_field, window.fac_direct);
		},1000);
	}

function do_incident_refresh() {
	window.do_inc_refresh = true; 
	$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>"; 
	load_incidentlist(window.inc_field, window.inc_direct);
	}
	
function refreshonclosed() {
	var incFin = false;
	var respFin = false;
	var facFin = false;
	var logFin = false;
	var statSel = false;
	var facstatSel = false;
	window.do_inc_refresh = true;
	window.do_resp_refresh = true; 
	$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	$('the_rlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	load_incidentlist(window.inc_field, window.inc_direct);	
	}		
	
function do_loglist_refresh() {
	window.do_log_refresh = true;
	latest_log = 0;
	$('the_loglist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>"; 
	load_log(window.log_field, window.log_direct);
	}

function submit_period() {
	$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	inc_period_changed = 1;
	load_incidentlist(window.inc_field, window.inc_direct);
	}

function destroy_unitmarkers() {
	for (var i = 1; i < rmarkers.length; i++) {
		map.removeLayer(rmarkers[i]);
		}
	rmarkers.length = 0;
	}
	
function destroy_incmarkers() {
	for (var i = 1; i < tmarkers.length; i++) { 
		map.removeLayer(tmarkers[i]);
		}
	tmarkers.length = 0;
	}
	
function destroy_facmarkers() {
	for (var i = 1; i < fmarkers.length; i++) { 
		map.removeLayer(fmarkers[i]);
		}
	fmarkers.length = 0;
	}
	

function secondsToTime(secs) {
	var numdays = Math.floor(secs / 86400);
	var numhours = Math.floor((secs % 86400) / 3600);
	var numminutes = Math.floor(((secs % 86400) % 3600) / 60);
	var numseconds = ((secs % 86400) % 3600) % 60;
	var outputText =  numdays + "D " + numhours + ":" + numminutes + ":" + Math.round(numseconds);
	return outputText;
	}

function do_statistics() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/basic_statistics.php?version=' + randomnumber;
	sendRequest (url,stats_cb, "");
	function stats_cb(req) {
		var theStats = JSON.decode(req.responseText);
		$('s1').innerHTML = theStats[0];
		$('s2').innerHTML = theStats[1];
		$('s3').innerHTML = theStats[4];
		$('s4').innerHTML = secondsToTime(theStats[5]);
		$('s5').innerHTML = secondsToTime(theStats[8]);
		$('s6').innerHTML = theStats[9];
		}
	statistics_get();
	}
	
function statistics_get() {								// set cycle
	if (s_interval!=null) {return;}
	s_interval = window.setInterval('statistics_loop()', 10000);
	}			// end statistics_get mu get()

function statistics_loop() {
	do_statistics();
	}			// end statistics_loop do_loop()
	
function get_scheduled_number() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = './ajax/sit_scheduled.php?version=' + randomnumber + '&q=' +sessID;
	sendRequest (url,sched_cb, "");
	function sched_cb(req) {
		var thescheds = JSON.decode(req.responseText);
		if(thescheds) {
			if(thescheds[0] > 0) {
				var theOutput = " There is (are) " + thescheds[0] + " Scheduled Incident(s)";
				} else {
				var theOutput = "";
				}
			} else {
			var theOutput = "";
			}
		$('sched_flag').innerHTML = theOutput;
		$('sched_flag').setAttribute('onclick','show_btns_closed(); set_period(10);');
		}
	}
	
function set_size() {
	window.resp_last_display = 0;
	window.inc_last_display = 0;
	window.do_inc_update = true;	
	window.do_resp_update = true;
	window.do_fac_update = true;
	do_log_refresh = true;
	responders_updated = [];
	facilities_updated = [];
	if($('the_list')) {$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";}
	if($('the_rlist')) {$('the_rlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";}
	if($('the_flist')) {$('the_flist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";}
	if($('the_loglist')) {$('the_loglist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";}
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
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .5;
	listwidth = colwidth;
	leftlistwidth = colwidth;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('ticketlist').style.maxHeight = listHeight + "px";
	$('ticketlist').style.width = listwidth + "px";
	$('ticketheading').style.width = listwidth + "px";
	$('responderlist').style.maxHeight = listHeight + "px";
	$('responderlist').style.width = listwidth + "px";
	$('the_rlist').style.maxHeight = listHeight + "px";
	$('the_rlist').style.width = listwidth + "px";
	$('respondersheading').style.width = listwidth + "px";
	$('facilitylist').style.maxHeight = listHeight + "px";	
	$('facilitylist').style.width = listwidth + "px";
	$('the_flist').style.maxHeight = listHeight + "px";
	$('the_flist').style.width = listwidth + "px";
	$('facilitiesheading').style.width = listwidth + "px";
	load_responderlist(window.resp_field, window.resp_direct);
	load_facilitylist(window.fac_field, window.fac_direct);
	load_exclusions();
	load_ringfences();
	load_catchments();
	load_basemarkup();
	load_groupbounds();
	load_incidentlist(window.inc_field, window.inc_direct);
	load_warnlocations();
	do_conditions();
	load_regions();
	set_initial_pri_disp();
	load_poly_controls();
	if(!isGuest) {
		if(showEvents == 1) {
			if($('logheading')) {$('logheading').style.width = mapWidth + "px";}
			if($('loglist')) {$('loglist').style.width = mapWidth + "px";}
			if($('the_loglist')) {$('the_loglist').style.width = mapWidth + "px";}
			}
		if(showStats == 1) {		
			if($('stats_wrapper')) {$('stats_wrapper').style.width = mapWidth + "px";}
			if($('stats_heading')) {$('stats_heading').style.width = mapWidth + "px";}
			if($('stats_table')) {$('stats_table').style.width = mapWidth + "px";}
			}
		}
	get_scheduled_number();
	loadData();
	map.invalidateSize();
	}
	
function pageLoaded() {
	if(respFin && !facFin && !incFin && !logFin) {
		load_facilitylist(window.fac_field, window.fac_direct);
		} else if(respFin && facFin && !incFin && !logFin) {
		load_incidentlist(window.inc_field, window.inc_direct);			
		} else if(respFin && facFin && incFin && !logFin) {
		load_exclusions();
		load_ringfences();
		load_catchments();
		load_basemarkup();
		load_groupbounds();
		do_conditions();
		load_regions();
		set_initial_pri_disp();
		load_poly_controls();
		if(!isGuest) {
			if(showEvents == 1) {
				load_log(window.log_field, window.log_direct);
				} else {
				logFin = true;
				pageLoaded();
				}
			if(showStats == 1) {		
				do_statistics();
				}
			}
		get_scheduled_number();
		} else if(incFin && respFin && facFin && logFin) {		
		pagetimerEnd = new Date();
		var elapsedTime = pagetimerEnd - window.pagetimerStart;
		var theTimeLoadString = "Page Loaded in: " + pageLoadTime + " seconds, Data Loaded in " + elapsedTime/1000 + " seconds";
		$('timer_div').innerHTML = theTimeLoadString;
		window.incFin = false;
		window.respFin = false;
		window.facFin = false;
		window.logFin = false;
		window.statSel = false;
		window.facstatSel = false;
		mapCenter = map.getCenter();
		mapZoom = map.getZoom();
		map.invalidateSize();
		}
	set_fontsizes(viewportwidth, "fullscreen");
	}
	
function loadData() {
	get_mi_totals();
	load_responderlist(window.resp_field, window.resp_direct);
	load_warnlocations("situation");
	load_status_bgcolors();
	load_status_textcolors();
	}

function pageUnload() {
	clearInterval(i_interval); 
	clearInterval(r_interval); 
	clearInterval(f_interval); 
	clearInterval(b_interval); 
	clearInterval(c_interval); 	
	clearInterval(s_interval); 
	}

var thelevel = '<?php print $the_level;?>';
<?php
if ( get_variable('call_board') == 2) {
	$cb_per_line = 22;
	$cb_fixed_part = 60;
	$cb_min = 96;
	$cb_max = 300;
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";
	$result = @mysql_query($query);
	$lines = mysql_affected_rows();
	unset($result);
	$height = (($lines*$cb_per_line ) + $cb_fixed_part);
	$height = ($height<$cb_min)? $cb_min: $height;
	$height = ($height>$cb_max)? $cb_max: $height;
?>
	frame_rows = parent.document.getElementById('the_frames').getAttribute('rows');
	var rows = frame_rows.split(",", 4);
	rows[1] = <?php print $height ;?>;
	frame_rows = rows.join(",");
	parent.document.getElementById('the_frames').setAttribute('rows', frame_rows);
	parent.calls.location.href = 'board.php';
<?php
	}		// end if ( get_variable('call_board') == 2) 
	
if ((!($_SESSION['internet'])) && (!$_SESSION['good_internet'])) {
?>
	parent.frames["upper"].$("full").style.display  = "none";
<?php
	}
if (is_guest()) {
?>	
	parent.frames["upper"].$("add").style.display = "none";
	try { parent.frames["upper"].$("ics").style.display = "none";}	
	catch(e) { }
	try { parent.frames["upper"].$("has_button").style.display = "none";}
	catch(e) { }
	try { parent.frames["upper"].guest_hide_buttons(isGuest);}
	catch(e) { }		
<?php
	}		// end guest - needs other levels!

	if (array_key_exists('log_in', $_GET)) {
?>
		parent.frames["upper"].$("gout").style.display  = "inline-block";
		parent.frames["upper"].mu_init ();
		var theBroadcast =  <?php print get_variable('broadcast');?>;
		if(parseInt(theBroadcast) == 1) {
			parent.frames["upper"].theConnection();		
			}
		if (parent.frames.length == 3) {
			parent.calls.location.href = 'board.php';
			}
<?php
		}
		$temp = get_unit();
		$term_str = ($temp )? $temp : "Mobile" ;

?>
	try {
		parent.frames["upper"].$("user_id").innerHTML  = "<?php print $_SESSION['user_id'];?>";	
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename(__FILE__));?>";
		parent.frames["upper"].$("main_body").style.backgroundColor  = "<?php print get_css('page_background', $day_night);?>";
		parent.frames["upper"].$("main_body").style.color  = "<?php print get_css('normal_text', $day_night);?>";
		parent.frames["upper"].$("tagline").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("user_id").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("unit_id").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("script").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("time_of_day").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("whom").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("level").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("logged_in_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("perms_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("modules_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("time_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";
		parent.frames["upper"].$("term").innerHTML  = "<?php print $term_str;?>";

		}
	catch(e) {
		}
		
	function get_new_colors() {
		window.location.href = 'main.php';
		}
		
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();
			}
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

<?php 
	if ($_SESSION['internet']) {	
?>
		<SCRIPT SRC='./js/usng.js' 			TYPE='application/x-javascript'></SCRIPT>
<?php
	}
	if($_SESSION['good_internet']) {
		$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;
		if((module_active("Ticker")==1) && (!($sit_scr))) {
?>
			<SCRIPT SRC='./modules/Ticker/js/mootools-1.2-core.js' type='application/x-javascript'></SCRIPT>
			<SCRIPT SRC='./modules/Ticker/js/ticker_core.js' type='application/x-javascript'></SCRIPT>
			<LINK REL=StyleSheet HREF="./modules/Ticker/css/ticker_css.php?version=<?php print time();?>" TYPE="text/css">
<?php
			$ld_ticker = "ticker_init();";
			}
		}

?>	
</HEAD>
<?php
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;	
	
	if((!(is_guest())) && ($_SESSION['good_internet']) && (!($get_id))) {
		if(file_exists("./incs/modules.inc.php")) {
			get_modules('main');
			}
		}	
	
	$gunload = "pageUnload();";
	$from_right = 20;
	$from_top = 10;
	$the_api_key = trim(get_variable('gmaps_api_key'));	
	$set_map = "";	// 1/16/2013
	$get_messages = ($get_id) ? "get_mainmessages(" . $get_id . " ,'',sortby, sort, '', 'ticket');" : "";
?>
<BODY style="overflow-y: scroll;" onLoad = "loadData(); ck_frames(); <?php print $ld_ticker;?> parent.frames['upper'].document.getElementById('gout').style.display = 'inline-block'; location.href = '#top'; <?php print $do_mu_init;?>" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");
?>

<A NAME='top'></A>
<DIV ID = "to_bottom" style='position:fixed; top: 40px; left: 0px; height: 12px; width: 10px; z-index: 99; cursor: pointer;' onclick = "location.href = '#bottom';"><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
<DIV id='screenname' style='display: none;'>situation</DIV>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>

<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
	<DIV CLASS='header' style = "height:32px; width: 100%; float: none; text-align: center;">
		<A id='maj_incs' class='plainmi text_bold text_biggest' style='display: none;' onMouseover='do_hover_mi(this.id);' onMouseout='do_plain_mi(this.id);' HREF="maj_inc.php"></A>
		<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'></SPAN>&nbsp;&nbsp;&nbsp;
		<SPAN ID='theRegions' CLASS='heading' STYLE='background-color: #707070; cursor: hand;'>Viewing Regions (mouse over to view)</SPAN>
		<SPAN ID='sev_counts' CLASS='sev_counts'></SPAN>
		<DIV id='timer_div' class='text_medium' style='color: #707070; float: right;'></DIV>
	</DIV>
	<DIV id = "leftcol" style='position: relative; left: 30px; float: left;'>
		<DIV id='ticketheading' class = 'heading text' style='border: 1px outset #707070; padding-top: 3px; padding-bottom: 3px;'>
			<DIV style='text-align: center;'>
				<FORM NAME = 'frm_interval_sel' STYLE = 'float: left; display:inline' >
					<SELECT id='period_select' NAME = 'frm_interval' onChange = 'show_btns_closed(); set_period(this.value);'>
						<OPTION VALUE='99' SELECTED><?php print get_text("Change display"); ?></OPTION>
						<OPTION VALUE='0'><?php print get_text("Current situation"); ?></OPTION>
						<OPTION VALUE='1'><?php print $incidents;?> closed today</OPTION>
						<OPTION VALUE='2'><?php print $incidents;?> closed yesterday+</OPTION>
						<OPTION VALUE='3'><?php print $incidents;?> closed this week</OPTION>
						<OPTION VALUE='4'><?php print $incidents;?> closed last week</OPTION>
						<OPTION VALUE='5'><?php print $incidents;?> closed last week+</OPTION>
						<OPTION VALUE='6'><?php print $incidents;?> closed this month</OPTION>
						<OPTION VALUE='7'><?php print $incidents;?> closed last month</OPTION>
						<OPTION VALUE='8'><?php print $incidents;?> closed this year</OPTION>
						<OPTION VALUE='9'><?php print $incidents;?> closed last year</OPTION>
						<OPTION VALUE='10'><?php print $incidents;?> Scheduled</OPTION>
					</SELECT>
				</FORM>
				Incidents <SPAN ID='sched_flag'></SPAN>
				<SPAN id='popout' class='plain text' onmouseover='do_hover(this.id); Tip("Pop out full incident list");' onmouseout='do_plain(this.id); UnTip();' onClick="do_full_inc_scr(); hideDiv('ticketlist', 'collapse_incs', 'expand_incs'); $('ticketheading').style.display='none';" style = 'float: left;'>Popout</SPAN>
				<SPAN id='collapse_incs' class='plain_square text' onmouseover='do_hover_squarebuttons(this.id); Tip("Minimize List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="hideDiv('ticketlist', 'collapse_incs', 'expand_incs');" style = 'float: right; display: "";'><IMG SRC = './markers/collapse.png' ALIGN='right' height="19" width="19"></SPAN>
				<SPAN id='expand_incs' class='plain_square text' onmouseover='do_hover_squarebuttons(this.id); Tip("Expand List");' onmouseout='do_plain(this.id); UnTip();' onClick="showDiv('ticketlist', 'collapse_incs', 'expand_incs')" style = 'float: right; display: none;'><IMG SRC = './markers/expand.png' ALIGN='right' height="19" width="19"></SPAN>
				<SPAN id='reload_incs' class='plain_square text' style='float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover_squarebuttons(this.id); Tip("Click to refresh Incident List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="do_incident_refresh();" style = 'float: right; display: "";'><IMG SRC = './markers/refresh.png' ALIGN='right' height="19" width="19"></SPAN><BR />
				<SPAN ID = 'btn_go' class='plain text' style='width: 50px; float: none; display: none; font-size: .8em; color: green;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);' onClick='submit_period(); hide_btns_closed();' CLASS='conf_button' STYLE = 'margin-left: 10px; color: green; display: none;'>Next</SPAN>
				<SPAN ID = 'btn_can' class='plain text' style='width: 50px; float: none; display: none; font-size: .8em; color: red;' onmouseover='do_hover(this.id);' onmouseout='do_plain(this.id);' onClick='hide_btns_closed(); hide_btns_scheduled(); ' CLASS='conf_button' STYLE = 'margin-left: 10px; color: red; display: none'>Cancel</SPAN>
				<SPAN class='text_medium' style='color: #FFFFFF;' id='caption'>click item to view / edit, right click for act / pat / notes, Click headers to sort</SPAN>
			</DIV>
		</DIV>
		<DIV class="scrollableContainer" id='ticketlist' style='border: 1px outset #707070;'>
			<DIV ID='spinner_i' STYLE='display: none; position: absolute; left: 45%; top: 35%;'></DIV>
			<DIV class="scrollingArea" id='the_list'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
		<DIV id='respondersheading' class = 'heading text' style='border: 1px outset #707070; padding-top: 3px; padding-bottom: 3px;'>
			<DIV style='text-align: center;'>Responders 
				<SPAN id='collapse_resp' class='plain_square text' onmouseover='do_hover_squarebuttons(this.id); Tip("Minimize List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="hideDiv('responderlist', 'collapse_resp', 'expand_resp')" style = 'float: right; display: "";'><IMG SRC = './markers/collapse.png' ALIGN='right'></SPAN>
				<SPAN id='expand_resp' class='plain_square text' onmouseover='do_hover_squarebuttons(this.id); Tip("Expand List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="showDiv('responderlist', 'collapse_resp', 'expand_resp')" style = 'float: right; display: none;'><IMG SRC = './markers/expand.png' ALIGN='right'></SPAN>
				<SPAN id='reload_resp' class='plain_square text' style='float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover_squarebuttons(this.id); Tip("Click to refresh Responder List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="do_responder_refresh();"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
				<BR />
				<SPAN class='text_medium' style='color: #FFFFFF;' id='caption'>click on item to view / edit, Click headers to sort</SPAN>
			</DIV>
		</DIV>				
		<DIV class="scrollableContainer" id='responderlist' style='border: 1px outset #707070;'>
			<DIV ID='spinner_r' STYLE='display: none; position: absolute; left: 45%; top: 35%;'></DIV>
			<DIV class="scrollingArea" id='the_rlist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
		<BR />
		<DIV id='facilitiesheading' class = 'heading text' style='border: 1px outset #707070; padding-top: 3px; padding-bottom: 3px;'>
			<DIV style='text-align: center;'>Facilities 
				<SPAN id='collapse_facs' class='plain_square text' onmouseover='do_hover_squarebuttons(this.id); Tip("Minimize List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="hideDiv('facilitylist', 'collapse_facs', 'expand_facs')" style = 'float: right; display: "";'><IMG SRC = './markers/collapse.png' ALIGN='right'></SPAN>
				<SPAN id='expand_facs' class='plain_square text' onmouseover='do_hover_squarebuttons(this.id); Tip("Expand List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="showDiv('facilitylist', 'collapse_facs', 'expand_facs')" style = 'float: right; display: none;'><IMG SRC = './markers/expand.png' ALIGN='right'></SPAN>
				<SPAN id='reload_facs' class='plain_square text' style='float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover_squarebuttons(this.id); Tip("Click to refresh Facility List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="do_facility_refresh();"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
				<BR />
				<SPAN class='text_medium' style='color: #FFFFFF;' id='caption'>click on item to view / edit, Click headers to sort</SPAN>
			</DIV>
		</DIV>
		<DIV class="scrollableContainer" id='facilitylist' style='border: 1px outset #707070;'>
			<DIV ID='spinner_f' STYLE='display: none; position: absolute; left: 45%; top: 35%;'></DIV>
			<DIV class="scrollingArea" id='the_flist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
		<BR />
		<BR />
		<BR />
		<A NAME="bottom" />
		<DIV ID='to_top' style="position:fixed; bottom:30px; left:0px; height: 12px; width: 10px; cursor: pointer;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" ID = "up" BORDER=0></div>
	</DIV>
	<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
		<DIV style='position: fixed; top: 50px; z-index: 1;'>
			<SPAN id='show_asgn' class='plain_centerbuttons text' style='color: #CFCFCF; cursor: default; width: 80px; display: block; float: none;'>Show Assigned<BR />
				<IMG id='show_asgn_img' SRC='./images/assigned.png' style='opacity: 0.3;'>
			</SPAN>
<?php
				if (!(is_guest())) {
?>
					<SPAN id='rc_but' class='plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver='do_hover_centerbuttons(this.id); Tip("Show current road condition alerts");' onMouseOut='do_plain_centerbuttons(this.id); UnTip();' onClick = "document.rc_form.submit();">Road Conditions<BR />
						<IMG SRC='./images/caution.png' BORDER=0>
					</SPAN>
<?php
					if(may_email()) {
?>
						<SPAN id='mail_but' class='plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver='do_hover_centerbuttons(this.id); Tip("Click to message all Units");' onMouseOut='do_plain_centerbuttons(this.id); UnTip();' onClick='do_mail_win();'>Contact <?php print get_text("Units");?><BR />
							<IMG SRC='./images/mail.png' BORDER=0>
						</SPAN>
						<SPAN id='facmail_but' class='plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver='do_hover_centerbuttons(this.id); Tip("Click to message all Facilities");' onMouseOut='do_plain_centerbuttons(this.id); UnTip();' onClick='do_fac_mail_win();'>Contact <?php print get_text("Facilities");?><BR />
							<IMG SRC='./images/mail.png' BORDER=0>
						</SPAN>
<?php
						}
					}
?>
		</DIV>
	</DIV>
	<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
		<DIV id = 'map_canvas' style = 'border: 1px outset #707070;'></DIV>
		<CENTER><SPAN class='text_blue text text_bold' style='width: 100%; text-align: center;'><?php print get_variable('map_caption');?></SPAN></CENTER><BR />
		<BR />
<?php
	if(!is_guest()) {
		if(intval($customSit_arr[0]) == 1) {
?>
			<DIV id='logheading' class = 'heading text' style='border: 1px outset #707070; padding-top: 3px; padding-bottom: 3px;'>
				<DIV style='text-align: center;'>Recent Events
					<SPAN id='collapse_log' class='plain_square text' onmouseover='do_hover_squarebuttons(this.id); Tip("Minimize List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="hideDiv('loglist', 'collapse_log', 'expand_log')" style = 'float: right; display: none;'><IMG SRC = './markers/collapse.png' ALIGN='right'></SPAN>
					<SPAN id='expand_log' class='plain_square text' onmouseover='do_hover_squarebuttons(this.id); Tip("Expand List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="showDiv('loglist', 'collapse_log', 'expand_log'); do_loglist_refresh();" style = 'float: right; display: "";'><IMG SRC = './markers/expand.png' ALIGN='right'></SPAN>
					<SPAN id='reload_log'class='plain_square text' style='float: right; text-align: center; vertical-align: middle;' onmouseover='do_hover_squarebuttons(this.id); Tip("Click to refresh Log List");' onmouseout='do_plain_squarebuttons(this.id); UnTip();' onClick="do_loglist_refresh();"><IMG SRC = './markers/refresh.png' ALIGN='right'></SPAN>
					<BR />
					<SPAN class='text_medium' style='color: #FFFFFF;' id='caption'>click on underlined item to view, Click headers to sort</SPAN>
				</DIV>
			</DIV>
			<DIV class="scrollableContainer" id='loglist' style='border: 1px outset #707070; display: none;'>
				<DIV class="scrollingArea" id='the_loglist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
			</DIV>
			<BR />
			<BR />
<?php
			}
		if(intval($customSit_arr[1]) == 1) {
?>
			<DIV id='stats_heading' class = 'heading text' style='border: 1px outset #707070; width: 100%; padding-top: 3px; padding-bottom: 3px;'>
				<DIV style='text-align: center;'>Statistics<BR />
					<SPAN class='text_medium' style='color: #FFFFFF;' id='caption'>hover over header for details on what each element is</SPAN>
				</DIV>
			</DIV>
			<DIV id='stats_wrapper' style='border: 1px outset #707070; width: 100%;'>
				<TABLE id='stats_table' style='width: 100%;'>
					<TR class='heading' style='width: 100%;'>
						<TH class='heading' onMouseover="Tip('Number of Tickets');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>NT</TH>
						<TH class='heading' onMouseover="Tip('Number of Tickets not assigned');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>NA</TH>
						<TH class='heading' onMouseover="Tip('Number of Responders on Scene');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>RO</TH>
						<TH class='heading' onMouseover="Tip('Average time to dispatch (Days Hours-Mins:Secs)');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>AD</TH>
						<TH class='heading' onMouseover="Tip('Average time ticket is open (Days Hours-Mins:Secs)');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>TO</TH>
						<TH class='heading' onMouseover="Tip('Number of available responders');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>AR</TH>
					</TR>
					<TR class='even' style='width: 100%;'>
						<TD id='s1' style='font-size: 0.8em; width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
						<TD id='s2' style='font-size: 0.8em; width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
						<TD id='s3' style='font-size: 0.8em; width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
						<TD id='s4' style='font-size: 0.8em; width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
						<TD id='s5' style='font-size: 0.8em; width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
						<TD id='s6' style='font-size: 0.8em; width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
					</TR>
				</TABLE>
			</DIV>
			<BR />
			<BR />
<?php
			}
		}
?>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, TRUE, TRUE, $allow_filedelete, 0, 0, 0, 0);
?>	
</DIV>
<SCRIPT>

var controlsHTML = "<TABLE id='controlstable' ALIGN='center'>";
controlsHTML += "<SPAN class='heading' style='width: 100%; text-align: center; display: inline-block;'>Map Controls</SPAN></BR>";
controlsHTML +=	"<TR class='even'><TD><CENTER><TABLE ID='buttons_sh' style='display: <?php print $show_controls;?>;'><TR CLASS='odd'><TD>";
controlsHTML +=	"<TABLE WIDTH='100%'><TR class='heading_2' WIDTH='100%'><TH ALIGN='center'>Incidents</TH></TR><TR><TD>";
controlsHTML +=	"<DIV class='pri_button text' onClick=\"set_pri_chkbox('normal'); hideGroup(0, 'Incident');\"><IMG SRC = './our_icons/sm_blue.png' STYLE = 'vertical-align: middle'BORDER=0>&nbsp;&nbsp;Normal: <input type=checkbox id='normal'  onClick=\"set_pri_chkbox('normal')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"<DIV class='pri_button text' onClick=\"set_pri_chkbox('medium'); hideGroup(1, 'Incident');\"><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;Medium: <input type=checkbox id='medium'  onClick=\"set_pri_chkbox('medium')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"<DIV class='pri_button text' onClick=\"set_pri_chkbox('high'); hideGroup(2, 'Incident');\"><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;High: <input type=checkbox id='high'  onClick=\"set_pri_chkbox('high')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"<DIV class='pri_button text' ID = 'pri_all' class='pri_button' STYLE = 'display: none; width: 70px;' onClick=\"set_pri_chkbox('all'); hideGroup(4, 'Incident');\"><IMG SRC = './our_icons/sm_blue.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;All <input type=checkbox id='all'  STYLE = 'display:none;' onClick=\"set_pri_chkbox('all')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"<DIV class='pri_button text' ID = 'pri_none' class='pri_button' STYLE = 'width: 60px;' onClick=\"set_pri_chkbox('none'); hideGroup(5, 'Incident');\"><IMG SRC = './our_icons/sm_white.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;None <input type=checkbox id='none' STYLE = 'display:none;' onClick=\"set_pri_chkbox('none')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"</TD></TR></TABLE></TD></TR><TR CLASS='odd'><TD><DIV ID = 'boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'fac_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'poly_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR></TABLE></CENTER></TD></TR></TABLE>";

//	setup map-----------------------------------//
var map;
var minimap;
var sortby = '`date`';
var sort = "DESC";
var columns = "<?php print get_msg_variable('columns');?>";
var the_columns = new Array(<?php print get_msg_variable('columns');?>);
var thescreen = 'ticket';
var thelevel = '<?php print $the_level;?>';
var tmarkers = [];			//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var fmarkers = [];			//	Responder Markers array
var cmarkers = [];			//	conditions markers array
var wlmarkers = [];			//	Locations warning markers array
var rss_markers = [];		//	RSS markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var latLng;

// set widths
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
mapWidth = viewportwidth * .40;
mapHeight = viewportheight * .55;
logwidth = mapWidth *.98;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
listHeight = viewportheight * .25;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .5;
listwidth = colwidth;
leftlistwidth = colwidth;
celwidth = listwidth * .20;
res_celwidth = listwidth * .15;
fac_celwidth = listwidth * .15;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = colwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('ticketlist').style.maxHeight = listHeight + "px";
$('ticketlist').style.width = listwidth + "px";
$('ticketheading').style.width = listwidth + "px";
$('responderlist').style.maxHeight = listHeight + "px";
$('responderlist').style.width = listwidth + "px";
$('the_rlist').style.maxHeight = listHeight + "px";
$('the_rlist').style.width = listwidth + "px";
$('respondersheading').style.width = listwidth + "px";
$('facilitylist').style.maxHeight = listHeight + "px";	
$('facilitylist').style.width = listwidth + "px";
$('the_flist').style.maxHeight = listHeight + "px";
$('the_flist').style.width = listwidth + "px";
$('facilitiesheading').style.width = listwidth + "px";
if(!isGuest) {
	if(showEvents == 1) {
		if($('logheading')) {$('logheading').style.width = mapWidth + "px";}
		if($('loglist')) {$('loglist').style.width = mapWidth + "px";}
		if($('the_loglist')) {$('the_loglist').style.width = mapWidth + "px";}
		}
	if(showStats == 1) {		
		if($('stats_wrapper')) {$('stats_wrapper').style.width = mapWidth + "px";}
		if($('stats_heading')) {$('stats_heading').style.width = mapWidth + "px";}
		if($('stats_table')) {$('stats_table').style.width = mapWidth + "px";}
		}
	}
// end of set widths
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
bounds = map.getBounds();	
zoom = map.getZoom();
var got_points = false;
$('controls').innerHTML = controlsHTML;
$('theHeading').innerHTML = heading;
<?php
do_kml();
?>
</SCRIPT>
<?php
	$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;		//	10/23/12	
	if(($_SESSION['good_internet']) && (module_active("Ticker")==1) && (!($sit_scr))) {			//	10/23/12
		require_once('./modules/Ticker/incs/ticker.inc.php');
		$the_markers = buildmarkers();
		foreach($the_markers AS $value) {
			if(my_is_float($value[3]) && my_is_float($value[3])) {
?>
<SCRIPT>
				var theLat = <?php print $value[3];?>;
				var theLng = <?php print $value[4];?>;
				var theA = "<?php print $value[6];?>";
				var the_point = new L.LatLng(theLat, theLng);		//	10/23/12
				var the_header = "Traffic Alert";		//	10/23/12
				var the_text = "<?php print $value[1];?>";		//	10/23/12
				var the_id = "<?php print $value[0];?>";		//	10/23/12
				var the_category = "<?php print $value[5];?>";		//	10/23/12
				var the_link = '<A CLASS="link" HREF="' + theA + '" TARGET="_blank" TITLE="' + the_text + '">' + the_text + '</A>';
				var the_descrip = "<DIV style='border: 1px outset #707070; background-color: yellow;'>";
				the_descrip += "<DIV style='font-size: 1em; color: #FFFFFF; background-color: #707070; font-weight: bold;'>" + the_header + "</DIV><BR />";		//	10/23/12
				the_descrip += "<DIV style='font-size: 1em; color: #000000; font-weight: bold;'>" + the_category + "</DIV><BR />";		//	10/23/12
				the_descrip += "<DIV><SPAN>" + the_link + "</SPAN></DIV><BR />";		
				the_descrip += "<DIV style='font-size: 1em; color: blue; font-weight: normal;'>";		//	10/23/12
				the_descrip += "<?php print $value[2];?>";		//	10/23/12
				the_descrip += "</DIV></DIV>";		//	10/23/12
				var rss_marker = create_feedMarker(the_point, the_text, the_descrip, the_id, the_id);		//	10/23/12
				rss_marker.addTo(map);			
</SCRIPT>
<?php
			}
		}
	}
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
?>
<SCRIPT>
var pageLoadTime = "<?php print $total_time;?>";
</SCRIPT>
<FORM NAME='to_listtype' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='func' VALUE='' />
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' />
</FORM>
<FORM NAME='to_scheduled' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' />
<INPUT TYPE='hidden' NAME='func' VALUE='1' />
</FORM>
<FORM NAME='to_map' METHOD='get' ACTION = 'config.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='api_key' />
</FORM>
<FORM NAME='tick_form' METHOD='get' ACTION='edit.php'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>
<FORM NAME='rc_form' METHOD='get' ACTION='rc_redirect.php'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>
<FORM NAME='resp_form' METHOD='get' ACTION='units.php?func=responder&edit=true'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
<INPUT TYPE='hidden' NAME='view' VALUE=''>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>
<FORM NAME='fac_form' METHOD='get' ACTION='facilities.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
<INPUT TYPE='hidden' NAME='view' VALUE=''>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>
</BODY>
<?php
if (array_key_exists('print', ($_GET))) {
?>
<script>
$("down").style.display = $("up").style.display = "none";
</script>
<?php
	}
?>
</HTML>
