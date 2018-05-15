<?php

error_reporting(E_ALL);
$units_side_bar_height = .6;
$do_blink = TRUE;
$ld_ticker = "";
$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$isGuest = (is_guest()) ? 1 : 0;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';

function hexToRgb($hex, $alpha = false) {
	$hex = str_replace('#', '', $hex);
	$length = strlen($hex);
	$rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
	$rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
	$rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
	if ( $alpha ) {
		$rgb['a'] = $alpha;
		}
	return implode(array_keys($rgb)) . '(' . implode(', ', $rgb) . ')';
	}

require_once('./incs/functions.inc.php');
require_once('./incs/all_forms_js_variables.inc.php');
$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$not_sit = (array_key_exists('id', ($_GET)))?  $_GET['id'] : NULL;
if ($broadcast == 1) {
	require_once('./incs/full_sit_comms.inc.php');
	}
$sess_id = $_SESSION['id'];
$curr_cats = get_category_butts();	//	get current categories.
$fac_curr_cats = get_fac_category_butts();
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
$api_key = get_variable('gmaps_api_key');
$statsRedThresholds = explode(',', get_variable('inc_statistics_red_thresholds'));
$statsOrangeThresholds = explode(',', get_variable('inc_statistics_orange_thresholds'));
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$states[$row['name']] = $row['code'];
	}
$pageBG = get_css("row_dark", $day_night);
$headerBG = get_css("header_text", $day_night);
$divBG = hexToRgb($pageBG, $alpha = 0.9);
$divHead = hexToRgb($headerBG, $alpha = 0.9);
$divBlackShade = hexToRgb('#000000', $alpha = 0.6);
$locale = get_variable('locale');
$routesUnits = (($locale == 0) || ($locale == 1)) ? 'imperial' : 'metric';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Incidents Full Screen</TITLE>
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
    <link rel="stylesheet" href="./js/leaflet/leaflet-routing-machine.css" />
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
	#leftcol {float: left; text-align: center;}
	#middlecol {float: left; padding: 10px; text-align: center; margin-right: 30px;}
	#rightcol {float: left; text-align: center;}
	input.btn_chkd {margin-top: 5px; width: 120px; height: 120px; color:#050; background-color:#EFEFEF;  border:1px solid;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center; border-radius:.5em; } 
	input.btn_not_chkd {margin-top: 5px; width: 120px; height: 120px; color:#050; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; border-radius:.5em;} 
	input.btn_smaller {margin-top: 5px; width: 120px; height: 120px; color:#050; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; border-radius:.5em;} 
	table td + td {border-left:1px solid red; }
	table.directions	{width: 100%;}
	table.directions th {background-color:#EEEEEE;}
	table.directions tr {background-color:#EEEEEE;}
	.stagelink {color: blue; text-decoration: underline; white-space: nowrap;}
	.stagedistance {color: black; font-weight: bold; white-space: nowrap;}	
	</STYLE>
	<script src="./js/jss.js" type="application/x-javascript"></script>
	<script src="./js/misc_function.js" type="application/x-javascript"></script>
	<script src="./js/json2.js"></script>
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/leaflet/leaflet-routing-machine.js"></script>
	<script src="./js/L.Graticule.js" type="application/x-javascript"></script>
	<script src="./js/leaflet-providers.js" type="application/x-javascript"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/osopenspace.js"></script>
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
	<script src="./js/domready.js" type="application/x-javascript"></script>
	<script src="./js/messaging.js" type="application/x-javascript"></script>
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
	<script src="./js/osm_map_functions.js" type="application/x-javascript"></script>
	<script src="./js/usng.js" type="application/x-javascript"></script>
	<script src="./js/osgb.js" type="application/x-javascript"></script>
	<script src="./js/geotools2.js" type="application/x-javascript"></script>
<SCRIPT>
window.onresize=function(){set_size();}
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var statsRedThresholds = '<?php echo json_encode($statsRedThresholds); ?>';
var statsOrangeThresholds = '<?php echo json_encode($statsOrangeThresholds); ?>';
var routesUnits = '<?php print $routesUnits;?>';
var tabindex = 1;
var msgtabindex = 500;
var reqtabindex = 1000;
var alertstabindex = 2000;
var listHeight;
var sidebar_height;
var outerwidth;
var outerheight;
var mapWidth;
var mapHeight;
var middlewidth;
var middlerowWidth;
var middlerowHeight;
var bottomRowHeight
var colwidth;
var listwidth;
var middlesubcolumnWidth;
var messageColumn;
var celwidth;
var add_winWidth = 0;
var edit_winWidth = 0;
var mess_winWidth = 0;
var note_winWidth = 0;
var pat_winWidth = 0;
var act_winWidth = 0;
var add_winHeight = 0;
var edit_winHeight = 0;
var mess_winHeight = 0;
var note_winHeight = 0;
var pat_winHeight = 0;
var act_winHeight = 0;
var disp_listheight = 0;
var showall = "no";
var inc_last_display = 0;
var inc_period_changed = 0;
var i_interval = null;
var s_interval = null;
var reqs_interval = null;
var log_interval = null;
var latest_logid = 0;
var latest_log = 0;
var latest_ticket = 0;
var do_update = true;
var tickets_updated = [];
var inc_period = 0;
var last_disp = 0;
var sortby = '`date`';
var sort = "DESC";
var thelevel = '<?php print $the_level;?>';
var cell1 = "0px";
var cell2 = "0px";
var cell3 = "0px";
var cell4 = "0px";
var cell5 = "0px";
var cell6 = "0px";
var cell7 = "0px";
var captions = ["Current situation", "Incidents closed today", "Incidents closed yesterday+", "Incidents closed this week", "Incidents closed last week", "Incidents closed last week+", "Incidents closed this month", "Incidents closed last month", "Incidents closed this year", "Incidents closed last year", "Scheduled"];
var heading = captions[inc_period];
var colors = new Array ('odd', 'even');
var isGuest = <?php print $isGuest;?>;
var tick_id = 0;
dbfns = new Array ();					//  field names per assigns_t.php expectations
dbfns['d'] = 'frm_dispatched';
dbfns['r'] = 'frm_responding';
dbfns['s'] = 'frm_on_scene';
dbfns['c'] = 'frm_clear';
dbfns['e'] = 'frm_u2fenr';
dbfns['a'] = 'frm_u2farr';
btn_ids = new Array ();					//  
btn_ids['d'] = 'disp_btn';
btn_ids['r'] = 'resp_btn';
btn_ids['s'] = 'onsc_btn';
btn_ids['c'] = 'clear_btn';
btn_ids['e'] = 'f_enr_btn';
btn_ids['a'] = 'f_arr_btn';
btn_labels = new Array ();				//  
btn_labels['d'] = '<?php print get_text("Disp"); ?> @ ';
btn_labels['r'] = '<?php print get_text("Resp"); ?> @ ';
btn_labels['s'] = '<?php print get_text("Onsc"); ?> @ ';
btn_labels['c'] = '<?php print get_text("Clear"); ?> @';
btn_labels['e'] = 'Fac enr @';
btn_labels['a'] = 'Fac arr @';
btn_labels_full = new Array ();				//  
btn_labels_full['d'] = '<?php print get_text("Dispatched"); ?> @ ';
btn_labels_full['r'] = '<?php print get_text("Responding"); ?> @ ';
btn_labels_full['s'] = '<?php print get_text("On-scene"); ?> @ ';
btn_labels_full['c'] = '<?php print get_text("Clear"); ?> @';
btn_labels_full['e'] = "Fac'y Enr @";
btn_labels_full['a'] = "Fac'y Arr @";
var fs_sit = true;
var lats = [];
var lngs = [];
var unit_names = [];		// names 
var unit_handles = [];
var unit_sets = [];			// settings
var unit_ids = [];			// id's
var unit_assigns = [];		// unit id's assigned this incident
var direcs = [];			// if true, do directions - 7/13/09
var nm_coord = 0.999999;
var current_id = 0;
var current_row = 0;
var current_row_class = "even";
var theLat;
var theLng;
var nr_units = 0;
var inc_header = textScope;
var inc_field = 'scope';
var inc_id = "t2";
var sel_inc = 0;
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
var currentTicket = 0;
var viewportwidth;
var viewportheight;

function blink_element(id) {
	var bgcol = "#FF0000";
	var bgcol2 = "#FFFF00";
	if(!document.getElementById(id)) {
	} else {	
		function BlinkIt () {
			if(document.getElementById (id)) {
				var blink = document.getElementById (id);
				var flag = id + "_flag";	
				back = (back == bgcol) ? bgcol2 : bgcol;
				blink.style.background = back;
				document.getElementById(id).title = "Alert";
				}
			}
		window.setInterval (BlinkIt, 1000);
		var back = bgcol;				
		}
	}
	
function unblink_element(id) {
	if(!document.getElementById(id)) {
	} else {	
	if(document.getElementById (id)) {
		var unblink = document.getElementById (id);
		unblink.style.background = "";
		unblink.style.color = "";			
			}
		}
	}

function start_server() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url ="./socketserver/server.php?version=" + randomnumber;
	var obj; 
	obj = new XMLHttpRequest();
	obj.onreadystatechange = function() {
		if(typeof Socket_startup == 'function') {
			setTimeout(function(){Socket_startup(); }, 5000);
			}
		}
	obj.open("POST", url, true);
	obj.send(null);
	}

function end_server() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './socketserver/deletefile.php';
	sendRequest (url,server2_cb, "");
	function server2_cb(req) {
		}
	}
	
function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth;
		viewportheight = window.innerHeight;
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth;
		viewportheight = document.documentElement.clientHeight;
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth;
		viewportheight = document.getElementsByTagName('body')[0].clientHeight;
		}
	document.body.style.overflow = "hidden";
	set_fontsizes(viewportwidth, "fullscreen");
	outerwidth = viewportwidth;
	outerheight = viewportheight * .98;
	mapWidth = outerwidth * .35;
	mapHeight = outerheight * .5;
	listHeight = outerheight * .35;
	middlerowWidth = outerwidth;
	middlerowHeight = outerheight * .25;
	colwidth = outerwidth;
	listwidth = outerwidth * .95;
	middlesubcolumnWidth = (middlerowWidth * .98) / 3;
	messageColumnHeight = middlerowHeight * .69;
	bottomRowHeight = outerheight * .10;
	add_winWidth = outerwidth * .70;
	edit_winWidth = outerwidth * .90;
	mess_winWidth = outerwidth * .80;
	note_winWidth = outerwidth * .40;
	pat_winWidth = outerwidth * .60;
	act_winWidth = outerwidth * .40;
	disp_winWidth = outerwidth * .80;
	add_winHeight = outerheight * .95;
	edit_winHeight = outerheight * .95;
	mess_winHeight = outerheight * .80;
	note_winHeight = outerheight * .40;
	pat_winHeight = outerheight * .75;
	act_winHeight = outerheight * .70;
	disp_listheight = outerheight * .70;
	var alertswidth = (theBroadcast == 1) ? middlesubcolumnWidth * .9 : middlesubcolumnWidth * 1.8;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = outerwidth + "px";}
	if($('leftcol')) {$('leftcol').style.height = (outerheight *.96) + "px";}
	if($('messages')) {$('messages').style.width = (middlesubcolumnWidth * 1.2) + "px";}
	if($('messagestable')) {$('messagestable').style.height = messageColumnHeight + "px";}
	if($('the_msglist')) {$('the_msglist').style.height = messageColumnHeight + "px";}
	if($('requests')) {$('requests').style.width = (middlesubcolumnWidth * 1.2) + "px";}
	if($('buttons')) {$('buttons').style.width = (middlesubcolumnWidth * 1.2) + "px";}
	if($('incbuttons')) {$('incbuttons').style.height = listHeight + "px";}
	if($('incbuttons')) {$('incbuttons').style.width = outerwidth + "px";}
	if($('middle')) {$('middle').style.width = middlerowWidth + "px";}
	if($('middle')) {$('middle').style.height = middlerowHeight + "px";}
	if($('alertswrapper')) {$('alertswrapper').style.width = alertswidth + "px";}
	if($('center_column')) {$('center_column').style.width = (middlesubcolumnWidth * 1.2) + "px";}
	if($('imwrapper')) {$('imwrapper').style.width = (middlesubcolumnWidth * .8) + "px";}
	get_scheduled_number();
	if(showStats == 1) {
		if($('stats_wrapper')) {$('stats_wrapper').style.height = bottomRowHeight + "px";}
		if($('stats_table')) {$('stats_table').style.height = bottomRowHeight * .8 + "px";}
		if($('stats_wrapper')) {$('stats_wrapper').style.width = viewportwidth + "px";}
		if($('stats_heading')) {$('stats_heading').style.width = viewportwidth + "px";}
		if($('stats_table')) {$('stats_table').style.width = viewportwidth + "px";}
		}
	}
	
function loadData() {
	if(parseInt(window.theBroadcast) == 1) {
		start_server();
		}
	get_mi_totals();
	load_full_incidentlist_incbuttons();
	get_mainmessages(0, 0, 0, 0, sortby, sort, 'inbox');
	get_requests();
	if(showStats == 1) {		
		do_statistics();
		}
	load_regions();
	}
	
function pageLoaded() {
	}

function toss() {
	return;
	}
	
function validate_mailform(myForm) {
	}
	
function validate(myForm) {
	}
	
var Now;
var mystart;
var myend;

var theDirections;
var textDirections;
var current_unit;

function setDirections(fromLat, fromLng, toLat, toLng, recLat, recLng, locale, unit_id, lineCalled) {
//	alert(fromLat + ", " + fromLng + ", " + toLat + ", " + toLng + ", " + recLat + ", " + recLng + ", " + locale + ", " + unit_id + ", " + lineCalled);
//	$('mail_dir_but').style.display = "none";
//	$('loading').style.display = "inline-block";
//	$("mail_button").style.display = "none";	//10/6/09
	if(window.theDirections) { window.theDirections.removeFrom(map);}
	window.current_unit = unit_id;
	window.theDirections = L.Routing.control({
		waypoints: [
			L.latLng(fromLat,fromLng),
			L.latLng(toLat,toLng)
		],
		  lineOptions: {
			styles: [
			  // Shadow
			  {color: 'black', opacity: 0.8, weight: 11},
			  // Outline
			  {color: 'green', opacity: 0.8, weight: 8},
			  // Center
			  {color: 'orange', opacity: 1, weight: 4}
			],
		},routeWhileDragging: true
	});
	window.theDirections.on('routingerror', function(o) { console.log(o); });
	window.theDirections.addTo(map);
	setTimeout(function() {
	direcs = $('directions').innerHTML;
	document.email_form.frm_direcs.value = textDirections;
	document.email_form.frm_u_id.value = current_unit;	
	$('mail_dir_but').style.display = "inline-block";
//	$('loading').style.display = "none";
	},500);
	}

var to_visible = "inline-block";
var to_hidden = "none";
function show_butts(strValue) {								// 3/15/11
	$('mail_dir_but').style.display = strValue;
	$('reset_but').style.display = strValue;
	$('can_but').style.display = strValue;
	if ($('disp_but')) {$('disp_but').style.display = strValue;}
	}
	
function show_disp_line(id, the_row) {
	if (document.getElementById(current_id)) {
		document.getElementById(current_id).style.visibility = "hidden";
		}
	current_id = "R"+id;
	document.getElementById(current_id).style.visibility = "visible";
	row_select(the_row);
	if($("C_" + id).checked == true) {show_butts(to_visible); unit_sets[id] = true;} else {show_butts(to_hidden); unit_sets[id] = false;}
	setDirections(lats[id], lngs[id], theLat, theLng, "", "", "en_US", id, "Line 206");
	}
	
function row_select(the_row) {
	if($(current_row)) {$(current_row).className = window.current_row_class;}
	window.current_row = the_row;
	window.current_row_class = $(the_row).className;
	CngClass(the_row, 'rowselect');
	return true;
	}

function do_clear(){
	for (i=0;i<document.contact_form.elements.length; i++) {
		if(document.contact_form.elements[i].type =='checkbox'){
			document.contact_form.elements[i].checked = false;
			}
		}
	$('clr_spn').style.display = "none";
	$('chk_spn').style.display = "inline-block";
	}		// end function do_clear

function do_check(){
	for (i=0;i<document.contact_form.elements.length; i++) {
		if(document.contact_form.elements[i].type =='checkbox'){
			document.contact_form.elements[i].checked = true;
			}
		}
	$('clr_spn').style.display = "inline-block";
	$('chk_spn').style.display = "none";
	}		// end function do_clear
	
function set_message(id, myForm) {	//	10/23/12
	var randomnumber=Math.floor(Math.random()*99999999);
	var theMessages = <?php echo json_encode($std_messages);?>;
	var message = theMessages[parseInt(id)]['message'];
	var url = './ajax/get_replacetext.php?tick=' + window.tick_id + '&version=' + randomnumber + '&text=' + encodeURIComponent(message);
	sendRequest (url,replacetext_cb, "");			
	function replacetext_cb(req) {
		var the_text=JSON.decode(req.responseText);
		if (the_text[0] == "") {
			var replacement_text = message;
			} else {
			var replacement_text = the_text[0];					
			}
		myForm.frm_text.value += replacement_text;					
		}			// end function replacetext_cb()	
	}		// end function set_message(message)
	
function do_sendform(myForm) {
	if (myForm.frm_text.value.trim()=="") {
		alert ("Message text is required");
		return false;
		}
	var sep = "";
	var sep2 = "";	//	10/23/12
	var z;	//	10/23/12
	for (i=0;i<myForm.elements.length; i++) {	//	10/23/12
		if((myForm.elements[i].type =='checkbox') && (myForm.elements[i].checked)){		// frm_add_str
			var the_val_arr = myForm.elements[i].value.split(":"); 
			var the_e_add = the_val_arr[0];
			var the_r_id = the_val_arr[1];
			var the_smsg_id = the_val_arr[2];
			var x=1;
			if((myForm.use_smsg[1]) && (myForm_form.use_smsg[1].checked)) {
				if((the_smsg_id != "NONE") && (the_smsg_id != "")) {
					myForm.frm_smsg_ids.value += sep2 + the_smsg_id;	
					myForm.frm_resp_ids.value += sep + the_r_id;							
					} else {
					myForm.frm_resp_ids.value += sep + the_r_id;					
					myForm.frm_add_str.value += sep + the_e_add;
					}
				} else {
				myForm.frm_resp_ids.value += sep + the_r_id;				
				myForm.frm_add_str.value += sep + the_e_add;
				}
			sep = "|";
			sep2 = ",";				
			}
		}
	if ((myForm.frm_add_str.value.trim()=="") && (myForm.frm_smsg_ids.value.trim()=="")) {	//	10/23/12
		alert ("Addressees required");
		return false;
		}
	sendajax(myForm);	
	}

function sendajax(myForm) {
	var action = myForm.getAttribute("action"), //Getting Form Action URL
	method = myForm.getAttribute("method"); //Getting Form Submit Method (Post/Get)
	var data = new FormData(myForm);
	var http = new XMLHttpRequest();
	http.open(method,action,true);
	$('extra_details').innerHTML = "";
	http.onload = function() {
		if (http.status == 200) {
			$('extra_header').innerHTML = "Complete";
			var outputtext = "<BR /><BR /><BR /><BR /><BR /><CENTER><SPAN class='text_biggest text_bold text_blue text_center'>";
			outputtext += this.responseText;
			outputtext += "</SPAN>";
			$('extra_details').innerHTML = outputtext;			
			}
		};
	http.send(data);
	}
	
function do_mail_win(addrs, smsgaddrs, ticket_id) {	
	if(starting) {return;}					// dbl-click catcher
	starting=true;	
	var url = "mail_edit.php?ticket_id=" + ticket_id + "&addrs=" + addrs + "&smsgaddrs=" + smsgaddrs + "&text=";	// no text
	newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
	if (isNull(newwindow_mail)) {
		alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	newwindow_mail.focus();
	starting = false;
	}		// end function do mail_win()
	
function do_mail_all(the_id) {
	if(starting) {return;}					// dbl-click catcher
	starting=true;
	var the_height = window.screen.height * 0.7;
	var the_width = window.screen.width * 0.5;
	var url = "do_unit_mail.php?the_ticket=doselect";	//
	newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height="+the_height+",width="+the_width+",status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50");
	if (isNull(newwindow_mail)) {
		alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	newwindow_mail.focus();
	starting = false;
	}		// end function do mail_win()
	
function open_add_window () {
	var url = "add.php?mode=1";
	var theWidth = window.add_winWidth;
	var theHeight = window.add_winHeight;
	var popWindow = window.open(url, 'addWindow', 'resizable=1, scrollbars, height='+theHeight+',width='+theWidth+', left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}

function open_edit_window(id) {
	var url = "edit.php?id=" + id + "&mode=1";
	var theWidth = window.edit_winWidth;
	var theHeight = window.edit_winHeight;
	var popWindow = window.open(url, 'editWindow', 'resizable=1, scrollbars, height='+theHeight+',width='+theWidth+', left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}

function senddispatches(myForm) {
	var action = myForm.getAttribute("action"), //Getting Form Action URL
	method = myForm.getAttribute("method"); //Getting Form Submit Method (Post/Get)
	var data = new FormData(myForm);
	var http = new XMLHttpRequest();
	http.open(method,action,true);
	http.onload = function() {
		if (http.status == 200) {
			var response = JSON.decode(this.responseText);
			var theText = response[0];
			var addrs_str = response[1];
			var sms_str = response[2];
			var tick_id = response[3];
			send_dispatchmail(myForm, theText, addrs_str, sms_str, tick_id);
			}
		};
	http.send(data);
	}
	
function send_dispatchmail(myForm, theText, addrs_str, sms_str, tick_id) {
	if(confirm("Send Dispatch Message ?")) {
		get_dispatchmail_form(addrs_str, sms_str, tick_id);
		} else {
		$('extra_header').innerHTML = "Complete";
		var outputtext = "<BR /><BR /><BR /><BR /><BR /><CENTER><SPAN class='text_biggest text_bold text_blue text_center'>";
		outputtext += theText;
		outputtext += "</SPAN>";
		$('extra_details').innerHTML = outputtext;
		return;
		}
	}
	
function validate_disp(myForm){
	msgstr="";
	for (var i =1;i<unit_sets.length;i++) {
		if (unit_sets[i]) {
			msgstr +=unit_names[i] + " - " + unit_handles[i] + "\n";
			myForm.frm_id_str.value += unit_ids[i] + "|";
			}
		}
	if (msgstr.length==0) {
		var more = (nr_units>1)? "s": ""
		alert ("Please select unit" + more + ", or cancel");
		return false;
		} else {
		if(!confirm("Confirm dispatching\n" + msgstr)) {
			return;
			}
		senddispatches(myForm);
		}
	}		// end function validate()
	
function addrcheck(str) {
	var at="@"
	var dot="."
	var lat=str.indexOf(at)
	var lstr=str.length
	var ldot=str.indexOf(dot)
	if (str.indexOf(at)==-1)													{return false;}
	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr) 	{return false;}
	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr)	{return false;}
	if (str.indexOf(at,(lat+1))!=-1)											{return false;}
	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot)		{return false;}
	if (str.indexOf(dot,(lat+2))==-1)											{return false;}
	if (str.indexOf(" ")!=-1)													{return false;}
	return true					
	}
var temp;
var lines;

function do_val(theForm) {										// 2/28/09, 10/23/12
	if((theForm.frm_use_smsg) && (theForm.frm_use_smsg == 0)) {
		if(theForm.frm_addrs.value == "") {
			var sep = "";
			theForm.frm_addrs.value = theForm.frm_addrs.value + sep + theForm.frm_theothers.value;
			} else {
			var sep = ",";
			theForm.frm_addrs.value = theForm.frm_addrs.value + sep + theForm.frm_theothers.value;
			}
			theForm.frm_smsgaddrs.value = "";
		}
		
	if ((theForm.frm_addrs.value.trim() == "") && (theForm.frm_smsgaddrs.value.trim() == "")) {
		alert("Addressee required");
		return false;
		}

	if (theForm.frm_addrs.value.trim() != "") {
		temp = theForm.frm_addrs.value.trim().split("|");		// explode to array
		var emerr = false;
		for (i=0; i<temp.length; i++) {								// check each addr
			if (!(addrcheck(temp[i].trim()))) {
				emerr = true;
				}
			}

		if (emerr) {
			alert("Valid addressee email required");
			return false;
			}
		}
		
	if (theForm.frm_text.value.trim() == "") {
		alert("Message text required");
		return false;
		}
	sendajax(theForm);
	}
	
function set_message(id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var theMessages = <?php echo json_encode($std_messages);?>;
	var message = theMessages[parseInt(id)]['message'];
	var url = './ajax/get_replacetext.php?tick=' + window.tick_id + '&version=' + randomnumber + '&text=' + encodeURIComponent(message);
	sendRequest (url,replacetext_cb, "");			
	function replacetext_cb(req) {
		var the_text=JSON.decode(req.responseText);
		if (the_text[0] == "") {
			var replacement_text = message;
			} else {
			var replacement_text = the_text[0];					
			}
		document.mail_form.frm_text.value += replacement_text;					
		}
	}		// end function set_message(message)

function handleResult(req) {			// the called-back function
	}			// end function handle Result()

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
		var statsThRed = JSON.decode(statsRedThresholds);
		var statsThOrange = JSON.decode(statsOrangeThresholds);		
		var thr0 = statsThRed[0];
		var thr1 = statsThRed[1];
		var thr2 = statsThRed[2];
		var thr3 = statsThRed[3];
		var thr4 = statsThRed[4];
		var thr5 = statsThRed[5];
		var tho0 = statsThOrange[0];
		var tho1 = statsThOrange[1];
		var tho2 = statsThOrange[2];
		var tho3 = statsThOrange[3];
		var tho4 = statsThOrange[4];
		var tho5 = statsThOrange[5];
		$('s1').innerHTML = theStats[0];
		$('s2').innerHTML = theStats[1];
		$('s3').innerHTML = theStats[4];
		$('s4').innerHTML = secondsToTime(theStats[5]);
		$('s5').innerHTML = secondsToTime(theStats[8]);
		$('s6').innerHTML = theStats[9];
		if(parseInt(theStats[0]) >= tho0) {$('s1').style.backgroundColor = 'orange'; $('s1').style.color = '#000000';}
		if(parseInt(theStats[0]) >= thr0) {$('s1').style.backgroundColor = 'red'; $('s1').style.color = '#000000';}
		if(parseInt(theStats[1]) >= tho1) {$('s2').style.backgroundColor = 'orange'; $('s2').style.color = '#000000';}
		if(parseInt(theStats[1]) >= thr1) {$('s2').style.backgroundColor = 'red'; $('s2').style.color = '#000000';}
		if(parseInt(theStats[2]) >= tho2) {$('s3').style.backgroundColor = 'orange'; $('s3').style.color = '#000000';}
		if(parseInt(theStats[2]) >= thr2) {$('s3').style.backgroundColor = 'red'; $('s3').style.color = '#000000';}
		if(parseInt(theStats[3]) >= tho3) {$('s4').style.backgroundColor = 'orange'; $('s4').style.color = '#000000';}
		if(parseInt(theStats[3]) >= thr3) {$('s4').style.backgroundColor = 'red'; $('s4').style.color = '#000000';}
		if(parseInt(theStats[4]) >= tho4) {$('s5').style.backgroundColor = 'orange'; $('s5').style.color = '#000000';}
		if(parseInt(theStats[4]) >= thr4) {$('s5').style.backgroundColor = 'red'; $('s5').style.color = '#000000';}
		if(parseInt(theStats[5]) <= tho5) {$('s6').style.backgroundColor = 'orange'; $('s6').style.color = '#000000';}
		if(parseInt(theStats[5]) <= thr5) {$('s6').style.backgroundColor = 'red'; $('s6').style.color = '#000000';}
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

function set_assign(which, id, theTicket, theUnit) {
	if (!(parseInt(id)) > 0) {return;}		
	var params = "frm_id=" + id;
	params += "&frm_tick=" + theTicket;
	params += "&frm_unit=" + theUnit;
	params += "&frm_vals=" + dbfns[which];
	sendRequest ('assigns_t.php',handleResult, params);			// does the work
	var curr_time = do_time();
	replaceButtonText(btn_ids[which], btn_labels[which] + curr_time)
	CngClass(btn_ids[which], 'btn_chkd');				// CngClass(obj, the_class)
	get_ticket(theTicket);
	}		// end function set_assign()
	
function replaceButtonText(buttonId, text) {
	if (document.getElementById) {
		var button=document.getElementById(buttonId);
		if (button) {
			if (button.childNodes[0]) {
				button.childNodes[0].nodeValue=text;
				}
			else if (button.value) {
				button.value=text;
				}
			else {					//if (button.innerHTML) 
				button.innerHTML=text;
				}
			}
		}
	}		// end function replaceButtonText()
	
function get_ticket(id) {
	if($('extra').style.display == "block") {$('extra').style.display = "none";}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/show_ticket.php?id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,ticketCB, "");
	function ticketCB(req) {
		var theResponse = JSON.decode(req.responseText);
		$('detail').style.display = 'block';
		$('edit_but').innerHTML = theResponse[1];
		$('ticket_details').innerHTML = theResponse[0];
		$('close_but').setAttribute('tabindex', 1);
		$('close_but').focus();
		get_assigns(id);
		}
	}
	
function get_contactform(id) {
	window.tick_id = id;
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/mail_form.php?ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,contactCB, "");
	function contactCB(req) {
		var theResponse = JSON.decode(req.responseText);
		$('extra').style.display = 'block';
		$('extra_details').innerHTML = theResponse[0];
		$('extra_close_but').setAttribute('tabindex', 1);
		$('extra_close_but').focus();
		}
	}
	
function get_noteform(id) {
	window.tick_id = id;
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/note_form.php?ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,noteCB, "");
	function noteCB(req) {
		var theResponse = JSON.decode(req.responseText);
		$('extra_details').innerHTML = theResponse[0];
		$('extra').style.display = 'block';
		$('extra_close_but').setAttribute('tabindex', 1);
		$('extra_close_but').focus();
		}
	}
	
function get_actionform(id) {
	window.tick_id = id;
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/action_form.php?ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,actionCB, "");
	function actionCB(req) {
		var theResponse = JSON.decode(req.responseText);
		$('extra_details').innerHTML = theResponse[0];
		$('extra').style.display = 'block';
		$('extra_close_but').setAttribute('tabindex', 1);
		$('extra_close_but').focus();
		}
	}
	
function get_dispatchmail_form(addrs_str, sms_str, id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/dispatchmail_form.php?ticket_id=' + id + '&addrs=' + addrs_str + '&smsgaddrs=' + sms_str + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,dispMailCB, "");
	function dispMailCB(req) {
		var theResponse = JSON.decode(req.responseText);
		$('extra_details').innerHTML = theResponse[0];
		$('dispmailform').innerHTML = theResponse[0];
		}
	}
	
function patientedit(ticket_id, id) {
	var url = './ajax/patient_form.php?ticket_id=' + ticket_id + '&action=edit&id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
	var theWidth = window.pat_winWidth;
	var theHeight = window.pat_winHeight;
	sendRequest (url,patCallback, "");
	function patCallback(req) {
		var theResponse = JSON.decode(req.responseText);
		$('extra_details').innerHTML = theResponse[0];
		if(theResponse[1]) {
			$('extra_header').innerHTML = theResponse[1];
			} else {
			$('extra_header').innerHTML = theTitle;
			}
		$('extra').style.width = theWidth + "px";
		$('extra').style.height = theHeight + "px";
		$('extra_details').style.width = theWidth + "px";
		$('extra_header').style.width = theWidth + "px";
		$('extra').style.display = 'block';
		$('extra_close_but').setAttribute('tabindex', 1);
		$('extra_close_but').focus();
		}
	}
	
function get_auxForm(id, theTitle, theFunc, theX, theY) {
	if(theFunc == "incbutton_opts") {
		var coords = $(id).getBoundingClientRect();
		var elemwidth = $(id).clientWidth;
		var right = (coords.right - (elemwidth/2)) + "px";
		var bottom = (coords.bottom) - 20 + "px";
		}
	if(typeof id == "string" && id.substring(0, 3) == "but") {
		id = id.substring(8);
		}
	window.tick_id = id;
	var randomnumber=Math.floor(Math.random()*99999999);
	var displayHeader = true;
	if(theFunc == "contact_sel" && id == 0) {theFunc = "contact_all_active";}
	switch(theFunc) {
		case "contact_all_sel":	
		var the_text = "<SPAN class='header'>Send only to assigned units ? (click no for all units)</SPAN>";
		the_text += "<SPAN ID='yes_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(window.sel_inc, 'Contact Units', 'contact_sel');\"><SPAN STYLE='float: left;'>Yes</SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>";
		the_text += "<SPAN ID='no_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"get_auxForm(window.sel_inc, 'Contact Units', 'contact_all');\"><SPAN STYLE='float: left;'>No</SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>";
		var theWidth = window.mess_winWidth;
		var theHeight = window.mess_winHeight;
		$('extra_details').innerHTML = the_text;
		$('extra').style.width = theWidth + "px";
		$('extra').style.height = theHeight + "px";
		$('extra_details').style.width = theWidth + "px";
		$('extra_header').style.width = theWidth + "px";
		$('extra').style.display = 'block';
		$('extra_close_but').setAttribute('tabindex', 1);
		$('extra_close_but').focus();
		break;
		
		case "contact_all":		//	All Units
		var url = './ajax/mail_form.php?func=all_units&ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
		var theWidth = window.mess_winWidth;
		var theHeight = window.mess_winHeight;
		break;
		
		case "action":
		var url = './ajax/action_form.php?ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
		var theWidth = window.act_winWidth;
		var theHeight = window.act_winHeight;
		break;
		
		case "note":
		var url = './ajax/note_form.php?ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
		var theWidth = window.note_winWidth;
		var theHeight = window.note_winHeight;
		break;
		
		case "patient":
		var url = './ajax/patient_form.php?ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
		var theWidth = window.pat_winWidth;
		var theHeight = window.pat_winHeight;
		break;
		
		case "contact":		//	get incident selector
		var url = './ajax/mail_form.php?func=doselect&ticket_id=0&version=' + randomnumber + '&q=' + sess_id;
		var theWidth = window.mess_winWidth;
		var theHeight = window.mess_winHeight;
		break;
		
		case "contact_sel":		// contact specific incident
		var url = './ajax/mail_form.php?func=selected&ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
		var theWidth = window.mess_winWidth;
		var theHeight = window.mess_winHeight;
		break;
		
		case "contact_all_active":		// contact responders assigned to any incident
		var url = './ajax/mail_form.php?func=all_incidents&version=' + randomnumber + '&q=' + sess_id;
		var theWidth = window.mess_winWidth;
		var theHeight = window.mess_winHeight;
		break;
		
		case "incbutton_opts":		// Incident Button Options
		var url = './ajax/fullsit_incident_options.php?ticket_id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
		var theWidth = 170;
		var theHeight = 280;
		break;
		
		default:
		return;
		}
	sendRequest (url,theCallback, "");
	function theCallback(req) {
		var theResponse = JSON.decode(req.responseText);
		if(theFunc == "incbutton_opts") {
			$('incoptions_details').innerHTML = theResponse[0];
			$('incoptions').style.position = "absolute";
			$('incoptions').style.top = bottom;
			$('incoptions').style.left = right;
			$('incoptions').style.width = theWidth + "px";
			$('incoptions').style.height = theHeight + "px";
			$('incoptions_details').style.width = theWidth + "px";
			$('incoptions').style.display = 'block';
			$('incoptions_close_but').setAttribute('tabindex', 1);
			$('incoptions_close_but').focus();			
			} else {
			$('extra_details').innerHTML = theResponse[0];
			if(theResponse[1]) {
				$('extra_header').innerHTML = theResponse[1];
				} else {
				$('extra_header').innerHTML = theTitle;
				}
			$('extra').style.width = theWidth + "px";
			$('extra').style.height = theHeight + "px";
			$('extra_details').style.width = theWidth + "px";
			$('extra_header').style.width = theWidth + "px";
			$('extra').style.display = 'block';
			$('extra_close_but').setAttribute('tabindex', 1);
			$('extra_close_but').focus();
			}
		}

	}
	
function get_assigns(id) {
	var theText = "";
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_assigns.php?id=' + id + '&version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,assignsCB, "");
	function assignsCB(req) {
		var theResponse = JSON.decode(req.responseText);
		if(theResponse[0][0] != 0) {
			i=0;
			for(key in theResponse) {
				if(key != 0) {
					var theNotes = (htmlentities(theResponse[key][13]) != "New") ? htmlentities(theResponse[key][13]) : "";
					theText += "<SPAN aria-hidden='true' id='ass_" + key + "' CLASS='" + colors[i%2] +"' style='width: 100%; display: table-row; vertical-align: middle;'>";
					theText += "<SPAN CLASS='" + colors[i%2] +"' aria-hidden='true' style='width: 20%; display: table-cell; vertical-align: middle;'>" + theResponse[key][1] + "</SPAN>";
					theText += "<SPAN CLASS='" + colors[i%2] +"' aria-hidden='true' style='width: 10%; display: table-cell; vertical-align: middle;'>" + theResponse[key][3] + "</SPAN>";
					theText += "<SPAN CLASS='" + colors[i%2] +"' aria-hidden='true' style='width: 10%; display: table-cell; vertical-align: middle;'>" + theResponse[key][4] + "</SPAN>";
					theText += "<SPAN CLASS='" + colors[i%2] +"' aria-hidden='true' style='width: 10%; display: table-cell; vertical-align: middle;'>" + theResponse[key][6] + "</SPAN>";
					theText += "<SPAN CLASS='" + colors[i%2] +"' aria-hidden='true' style='width: 10%; display: table-cell; vertical-align: middle;'>" + theResponse[key][7] + "</SPAN>";
					theText += "<SPAN CLASS='" + colors[i%2] +"' aria-hidden='true' style='width: 10%; display: table-cell; vertical-align: middle;'>" + theResponse[key][8] + "</SPAN>";	
					theText += "<SPAN CLASS='" + colors[i%2] +"' aria-hidden='true' style='width: 10%; display: table-cell; vertical-align: middle;'>" + theResponse[key][5] + "</SPAN>";
					theText += "<SPAN CLASS='" + colors[i%2] +"' aria-label='Dispatch Notes " + theNotes + "' tabindex=8 style='width: auto; display: table-cell; vertical-align: text-top;'>" + theNotes + "</SPAN>";					
					theText += "</SPAN>";
					}
				i++;					
				}
			}
		$('assigns_details').innerHTML = theText;
		}
	}

function set_period(period) {
	if(period == 99 || period == window.inc_period) {return;}
	window.inc_period = period;
	thelength = document.getElementById('frm_interval').options.length;
	for(var f = 0; f < thelength; f++) {
		if($('frm_interval').options[f].value == period) {
			$('frm_interval').options[f].selected = true;
			}
		}
	$('theHeading').innerHTML = window.captions[window.inc_period];
	$('incbuttons').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	inc_period_changed = 1;
	load_full_incidentlist_incbuttons();
	}
	
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
		$('sched_flag').setAttribute('onclick','set_period(10);');
		}
	}

function get_new_colors() {
	window.location.href = '<?php print basename(__FILE__);?>';
	}
	
function show_btns_closed() {
	$('btn_go').style.display = 'inline';
	$('btn_can').style.display = 'inline';
	}
function hide_btns_closed() {
	$('btn_go').style.display = 'none';
	$('btn_can').style.display = 'none';
	document.dummy.frm_interval.selectedIndex=99;
	}
	
function backtoSituation() {
	if(!window.opener) {return false;}
	var params = "f_n=fullscr_sit&v_n=false&sess_id=" + sess_id;
	var url = "persist2.php";
	sendRequest (url, gb_handleResult, params);
	if(window.opener.$('ticketheading')) {
		window.opener.$('ticketheading').style.display = 'block';
		window.opener.showDiv('ticketlist', 'collapse_incs', 'expand_incs');
		}
	}
	
function do_incident_refresh() {
	window.do_inc_refresh = true; 
	$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>"; 
	load_incidentlist(window.inc_field, window.inc_direct);
	}
	
function setTableCells(theTable, tableWidth) {
	var table = document.getElementById(theTable);
	if(table) {
		var headerRow = table.rows[0];
		var tableRow = table.rows[1];
		if(tableRow) {
			for (var i = 0; i < tableRow.cells.length; i++) {
				headerRow.cells[i].style.width = tableRow.cells[i].clientWidth +1 + "px";
				}
			} else {
			var numCols = headerRow.cells.length;
			var cellwidth = tableWidth / numCols;
			for (var i = 0; i < headerRow.cells.length; i++) {
				headerRow.cells[i].style.width = cellwidth + "px";
				}				
			}
		if(getHeaderHeight(headerRow) >= 10) {
			var theRow = table.insertRow(1);
			theRow.style.height = "20px";
			for (var i = 0; i < headerRow.cells.length; i++) {
				var theCell = theRow.insertCell(i);
				theCell.innerHTML = " ";
				}
			}
		}	
	}
	
function do_sel_update (the_id, the_val) {							// 12/17/09
	status_update(the_id, the_val);
	}
	
function status_update(the_id, the_val) {									// write unit status data via ajax xfer
	var querystr = "the_id=" + the_id;
	querystr += "&status=" + the_val;
	var url = "up_status.php?" + querystr;			// 
	var payload = syncAjax(url);						// 
	if (payload.substring(0,1)=="-") {	
		alert ("<?php print __LINE__;?>: msg failed ");
		return false;
		}
	else {
	get_requests();
		return true;
		}
	}		// end function status_update()
	
	
function get_requests() {
	window.reqs_interval = null;
	$('all_requests').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/list_requests_admin.php?showall=" + showall + "&version=" + randomnumber;
	sendRequest (url, requests_cb, "");
	function requests_cb(req) {
		var the_requests=JSON.decode(req.responseText);
		the_string = "<TABLE id='requeststable' class='fixedheadscrolling scrollable' style='width: 100%;'>";
		the_string += "<thead>";
		the_string += "<TR class='plain_listheader text' style='width: " + window.listwidth + "px;'>";
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Request ID' tabindex=" + reqtabindex + " style='width: 40px; border-right: 1px solid #FFFFFF;'>" + textID + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Patient Name' tabindex=" + reqtabindex + " style='border-right: 1px solid #FFFFFF;'>" + textPatient + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Contact Phone' tabindex=" + reqtabindex + " style='bold; border-right: 1px solid #FFFFFF;'>" + textPhone + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Contact Name' tabindex=" + reqtabindex + " style='border-right: 1px solid #FFFFFF;'>" + textContact + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Request Name' tabindex=" + reqtabindex + " style='width: 15%; border-right: 1px solid #FFFFFF;'>" + textScope + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Request Description' tabindex=" + reqtabindex + " style='width: 10%; border-right: 1px solid #FFFFFF;'>" + textDescription + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Request Status' tabindex=" + reqtabindex + " style='border-right: 1px solid #FFFFFF;'>" + textStatus + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Requested Date' tabindex=" + reqtabindex + " style='border-right: 1px solid #FFFFFF;'>" + textRequested + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Updated Date' tabindex=" + reqtabindex + " style='border-right: 1px solid #FFFFFF;'>" + textUpdated + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' roll='button' aria-label='Updated By' tabindex=" + reqtabindex + " style='border-right: 1px solid #FFFFFF;'>" + textBy + "</TH>";
		reqtabindex ++;
		the_string += "<TH class='plain_listheader text' aria-hidden='true' style='border-right: 1px solid #FFFFFF;'>...</TH>";				
		reqtabindex ++;
		the_string += "</TR>";
		the_string += "</thead>";
		the_string += "<tbody>";		
		theClass = "background-color: #CECECE";
		for(var key in the_requests) {
			if(the_requests[key][0] == "No Current Requests") {
				var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Service Requests.........</marquee>";	
				$('all_requests').innerHTML = outputtext;
				return false;
				} else {
				var the_request_id = the_requests[key][0];
				if((the_requests[key][16] == 'Open') || (the_requests[key][16] == 'Tentative')) {
					the_onclick = "onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"";
					} else {
					the_onclick = "";
					}
				var theTitle = the_requests[key][13];
				var theField = the_requests[key][13];
				if(theField.length > 48) {
					theField = theField.substring(0,48)+"...";
					}
				the_string += "<TR title='" + theTitle + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px; width: 100%;'>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='width: 15%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + theField + "</TD>";
				the_string += "<TD CLASS='plain_list text' title='" + the_requests[key][14] + "' style='width: 10%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap: sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14].substring(0,24)+"..." + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' " + the_onclick + ">" + the_requests[key][16] + "</TD>";	
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
				the_string += "<TD CLASS='plain_list text'style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
				if(the_requests[key][35] != 0) {
					the_string += "<TD CLASS='plain_list text'><SPAN id='ed_but' class='plain' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"location.href='../edit.php?id=" + the_requests[key][35] + "';\">Open Ticket</SPAN></TD>";
					} else {
					the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;'>&nbsp;</TD>";
					}
				the_string += "</TR>";
				reqtabindex ++;
				}
			}
		the_string += "</tbody></TABLE>";
		setTimeout(function() {
			$('all_requests').innerHTML = the_string;
			setTableCells("requeststable", window.middlesubcolumnWidth);
			requests_get();
			},1500);
		}
	}		
	
function requests_get() {
	reqs_interval = window.setInterval('do_requests_loop()', 30000);
	}	
	
function do_requests_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/list_requests_admin.php?showall=" + showall + "&version=" + randomnumber;
	sendRequest (url, requests_cb2, "");
	}

function requests_cb2(req) {
	var the_requests=JSON.decode(req.responseText);
	if(the_requests[0] == "No Current Requests") {
		var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Service Requests.........</marquee>";	
		$('all_requests').innerHTML = outputtext;
		return false;
		} else {
		width = "";
		}
	the_string = "<TABLE id='requeststable' class='fixedheadscrolling scrollable' style='width: " + window.listwidth + "px;'>";
	the_string += "<thead>";
	the_string += "<TR class='plain_listheader text' style='width: " + window.listwidth + "px;'>";
	the_string += "<TH class='plain_listheader text' style='width: 40px; border-right: 1px solid #FFFFFF;'>" + textID + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textPatient + "</TH>";
	the_string += "<TH class='plain_listheader text' style='bold; border-right: 1px solid #FFFFFF;'>" + textPhone + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textContact + "</TH>";
	the_string += "<TH class='plain_listheader text' style='width: 15%; border-right: 1px solid #FFFFFF;'>" + textScope + "</TH>";
	the_string += "<TH class='plain_listheader text' style='width: 10%; border-right: 1px solid #FFFFFF;'>" + textDescription + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textStatus + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textRequested + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textUpdated + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>" + textBy + "</TH>";
	the_string += "<TH class='plain_listheader text' style='border-right: 1px solid #FFFFFF;'>...</TH>";		
	the_string += "</TR>";
	the_string += "</thead>";
	the_string += "<tbody>";		
	theClass = "background-color: #CECECE";
	for(var key in the_requests) {
		if(the_requests[key][0] == "No Current Requests") {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Service Requests.........</marquee>";	
			$('all_requests').innerHTML = outputtext;
			return false;
			} else {
			var the_request_id = the_requests[key][0];
			if((the_requests[key][16] == 'Open') || (the_requests[key][16] == 'Tentative')) {
				the_onclick = "onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"";
				} else {
				the_onclick = "";
				}
			var theTitle = the_requests[key][13];
			var theField = the_requests[key][13];
			if(theField.length > 48) {
				theField = theField.substring(0,48)+"...";
				}
			the_string += "<TR title='" + theTitle + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px; width: 100%;'>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='width: 15%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + theField + "</TD>";
			the_string += "<TD CLASS='plain_list text' title='" + the_requests[key][14] + "' style='width: 10%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap: sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14].substring(0,24)+"..." + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' " + the_onclick + ">" + the_requests[key][16] + "</TD>";	
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
			the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
			the_string += "<TD CLASS='plain_list text'style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
			if(the_requests[key][35] != 0) {
				the_string += "<TD CLASS='plain_list text'><SPAN id='ed_but' class='plain' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"location.href='../edit.php?id=" + the_requests[key][35] + "';\">Open Ticket</SPAN></TD>";
				} else {
				the_string += "<TD CLASS='plain_list text' style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;'>&nbsp;</TD>";
				}
			the_string += "</TR>";
			}
		}
	the_string += "</TABLE>";
	setTimeout(function() {
		$('all_requests').innerHTML = the_string;
		setTableCells("requeststable", window.middlesubcolumnWidth);
		},1500);
	}
	
function summary_get() {
	summary_interval = window.setInterval('do_summary_loop()', 10000);
	}	
	
function do_summary_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/requests_wallboard.php?version=" + randomnumber;
	sendRequest (url, summary_cb2, "");
	}

function get_summary() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./portal/ajax/requests_wallboard.php?version=" + randomnumber;
	sendRequest (url, summary_cb, "");
	function summary_cb(req) {
		var the_summary=JSON.decode(req.responseText);
		var theColor = "style='background-color: #CECECE; color: #000000;'";
		if(the_summary[0] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
		var numOpen = "<TD class='summ_td_label text'>Requests Open (not accepted): </TD><TD class='summ_td_data text' " + theColor + ">" + the_summary[0] + "</TD>";
		if(the_summary[1] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }			
		var numAcc = "<TD class='summ_td_label text'>Requests Accepted (not resourced): </TD><TD class='summ_td_data text'>" + the_summary[1] + "</TD>";
		var numComp = "<TD class='summ_td_label text'>Requests Completed: </TD><TD class='summ_td_data text'>" + the_summary[3] + "</TD>";
		if(the_summary[7] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
		var totTent = "<TD class='summ_td_label text'>Requests Tentative: </TD><TD class='summ_td_data text' " + theColor + ">" + the_summary[7] + "</TD>";
		var totCan = "<TD class='summ_td_label text'>Requests Cancelled: </TD><TD class='summ_td_data text'>" + the_summary[8] + "</TD>";
		var totDec = "<TD class='summ_td_label text'>Requests Declined: </TD><TD class='summ_td_data text'>" + the_summary[9] + "</TD>";
		var summaryText = "<TABLE style='width: 100%; background-color: #FFFFFF;'>";
		summaryText += "<TR>" + numOpen + numAcc + numComp + "</TR>";
		summaryText += "<TR>" + totTent + totCan + totDec + "</TR>";
		summaryText += "</TABLE>";
		$('theSummary').innerHTML = summaryText;
		summary_get();			
		}
	}
	
function summary_cb2(req) {
	var the_summary=JSON.decode(req.responseText);
	var theColor = "style='background-color: #CECECE; color: #000000;'";
	if(the_summary[0] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
	var numOpen = "<TD class='summ_td_label text'>Requests Open (not accepted): </TD><TD class='summ_td_data text' " + theColor + ">" + the_summary[0] + "</TD>";
	if(the_summary[1] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }			
	var numAcc = "<TD class='summ_td_label text'>Requests Accepted (not resourced): </TD><TD class='summ_td_data text'>" + the_summary[1] + "</TD>";
	var numComp = "<TD class='summ_td_label text'>Requests Completed: </TD><TD class='summ_td_data text'>" + the_summary[3] + "</TD>";
	if(the_summary[7] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
	var totTent = "<TD class='summ_td_label text'>Requests Tentative: </TD><TD class='summ_td_data text' " + theColor + ">" + the_summary[7] + "</TD>";
	var totCan = "<TD class='summ_td_label text'>Requests Cancelled: </TD><TD class='summ_td_data text'>" + the_summary[8] + "</TD>";
	var totDec = "<TD class='summ_td_label text'>Requests Declined: </TD><TD class='summ_td_data text'>" + the_summary[9] + "</TD>";
	var summaryText = "<TABLE style='width: 100%; background-color: #FFFFFF;'>";
	summaryText += "<TR>" + numOpen + numAcc + numComp + "</TR>";
	summaryText += "<TR>" + totTent + totCan + totDec + "</TR>";
	summaryText += "</TABLE>";
	$('theSummary').innerHTML = summaryText;
	}
	

function hide_closed() {
	showall = "no";
	$('hideBut').style.display = "none";
	$('showBut').style.display = "inline-block";
	get_requests();
	}

function show_closed() {
	showall = "yes";
	$('showBut').style.display = "none";
	$('hideBut').style.display = "inline-block";
	get_requests();
	}
</SCRIPT>
</HEAD>
<?php
$gunload = "backtoSituation(); clearInterval(i_interval); clearInterval(s_interval);";
?>
<BODY onLoad = "loadData(); location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<DIV id='screenname' style='display: none;'>Incidents Screen</DIV>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV id='outer' style='position: absolute; left: 0px; top: 0px; z-index: 1; text-align: center;'>
	<DIV CLASS='heading' style = 'width: 100%; float: none; text-align: center; display: block; height: 50px; line-height: 50px;'>
		<A id='maj_incs' class='plainmi text_bold text_biggest' style='display: none;' onMouseover='do_hover_mi(this.id);' onMouseout='do_plain_mi(this.id);' HREF="maj_inc.php"></A>
		<SPAN ID='theHeading' CLASS='text_white text_bold text_biggest' STYLE='background-color: inherit;'></SPAN>&nbsp;&nbsp;&nbsp;
		<SPAN ID='theRegions' CLASS='text_white text_bold text_biggest' STYLE='background-color: #707070; cursor: hand;'>Viewing Regions (mouse over to view)</SPAN>
		<SPAN ID='sev_counts' CLASS='sev_counts text_biggest'></SPAN>
		<DIV id='timer_div' class='text_white text_bold text_biggest' style='color: #707070; float: right;'></DIV>
<?php
		if(intval(get_variable('alternate_sit')) == 0) {
?>
			<SPAN id='closewin_but' class='plain text' style='float: right; vertical-align: middle; display: block; padding: 0px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="backtoSituation(); self.close();"><IMG STYLE='vertical-align: middle; height: 40px; width: 40px;' SRC='./images/close_large.png' BORDER=0></SPAN>
<?php
			}
?>	</DIV>
	<DIV id='leftcol' style='width: 100%; text-align: center; display: block;'>
		<DIV id='ticketheader' class='header text_center text_bold text_biggest' style='padding-top: 5px; padding-bottom: 5px; width: 100%; display: block; height: 30px;'>
			<DIV id='period_selector' style='float: left; display: inline-block;'>
				<SPAN style='float: left; height: 100%;'>
					<FORM NAME = 'frm_interval_sel' STYLE = 'display:inline' >
						<SELECT tabindex=1 CLASS='text' ID='frm_interval' NAME = 'frm_interval' onChange = 'set_period(this.value);'>
							<OPTION CLASS='text' VALUE='99' SELECTED><?php print get_text("Change display"); ?></OPTION>
							<OPTION CLASS='text' VALUE='0'><?php print get_text("Current situation"); ?></OPTION>
							<OPTION CLASS='text' VALUE='1'><?php print $incidents;?> closed today</OPTION>
							<OPTION CLASS='text' VALUE='2'><?php print $incidents;?> closed yesterday+</OPTION>
							<OPTION CLASS='text' VALUE='3'><?php print $incidents;?> closed this week</OPTION>
							<OPTION CLASS='text' VALUE='4'><?php print $incidents;?> closed last week</OPTION>
							<OPTION CLASS='text' VALUE='5'><?php print $incidents;?> closed last week+</OPTION>
							<OPTION CLASS='text' VALUE='6'><?php print $incidents;?> closed this month</OPTION>
							<OPTION CLASS='text' VALUE='7'><?php print $incidents;?> closed last month</OPTION>
							<OPTION CLASS='text' VALUE='8'><?php print $incidents;?> closed this year</OPTION>
							<OPTION CLASS='text' VALUE='9'><?php print $incidents;?> closed last year</OPTION>
							<OPTION VALUE='10'><?php print $incidents;?> Scheduled</OPTION>
						</SELECT>
					</FORM>
				</SPAN>
				<SPAN id='newinc_but' roll='button' tabindex=5001 aria-label='New Incident' class='plain text' style='vertical-align: middle; display: inline-block; width: 100px; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="open_add_window();"><SPAN STYLE='float: left;'><?php print get_text("New Incident");?></SPAN><IMG STYLE='float: right;' SRC='./images/add.png' BORDER=0></SPAN>
			</DIV>		
			Incidents&nbsp;&nbsp;&nbsp;<SPAN style='background-color: blue; color: white; font-weight: bold;' ID='sched_flag'></SPAN>
			</DIV><BR />
		<DIV id='incbuttons' style='padding: 5px; border: 2px outset #707070; overflow-y: auto;'></DIV>
		<BR />
		<DIV id='middle' style='display: block; position: absolute; left: 0%; text-align: left; width: 100%; padding: 5px;'>
			<DIV id='center_column' style='display: inline-block; vertical-align: top; border: 1px outset #707070;'>
				<DIV id='buttons' style='display: block;'>
					<SPAN id='msgs_but' roll='button' tabindex=5001 aria-label='Show Messages' class='plain text' style='vertical-align: middle; display: inline-block; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('requests').style.display='none'; $('messages').style.display='block';"><?php print get_text("Messages");?><IMG style='vertical-align: middle; float: right;' SRC='./images/mail_small.png' BORDER=0></SPAN>
					<SPAN id='requests_but' roll='button' tabindex=5002 aria-label='Show Service Requests'  class='plain text' style='vertical-align: middle; display: inline-block; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('messages').style.display='none'; $('requests').style.display='block';"><?php print get_text("Requests");?><IMG style='vertical-align: middle; float: right;' SRC='./images/request.png' BORDER=0></SPAN>
					<SPAN id='msg_all_but' roll='button' tabindex=5001 aria-label='Send Message to all' class='plain text' style='vertical-align: middle; display: inline-block; width: 150px; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="get_auxForm(0, 'Contact All <?php print get_text('Units');?>', 'contact');">Mail All <?php print get_text("Units");?><IMG style='vertical-align: middle; float: right;' SRC='./images/mail_small.png' BORDER=0></SPAN>
				</DIV>
				<BR />
				<BR />
				<DIV id='messages' style='display: block; width: 100%; height: 90%;'>
					<DIV id='messagesheader' class='header text_center text_bold text_biggest' style='display: block; width: 98%; text-align: middle;'>Messages</DIV>
					<DIV class="scrollableContainer" id='messageslist'  style='width: 98%'>
						<DIV class="scrollingArea" id='the_msglist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>		
					</DIV>
				</DIV>
				<DIV id='requests' style='display: none; width: 100%; height: 90%;'>
					<DIV id='requestheader' class='header text_center text_bold text_biggest' style='display: block; width: 98%; text-align: middle;'>Service Requests</DIV>
					<DIV class="scrollableContainer" id='requestlist'  style='min-height: 30%; max-height: 50%; width: 98%'>
						<DIV class="scrollingArea" id='all_requests'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>			
					</DIV>
				</DIV>
			</DIV>
<?php
			if($broadcast) {
				$alertswidth = "30%";

?>
				<DIV id='imwrapper' style='display: inline-block; width: 30%; height: 100%; vertical-align: top; border: 1px outset #707070; float: right; padding-left: 5px; padding-right: 5px;'>
					<DIV id='commsheader' class='header text_center text_bold text_biggest' style='display: block; width: 99%; text-align: middle;'>Broadcast Comms</DIV>
					<DIV class='even' id='has_flag' style='width: 99%; height: auto; border: 1px outset #707070;'>Loading Broadcast....<IMG style='display: inline;' src='./images/owmloadingsmall.gif'></DIV>
					<DIV class='odd' id='usercount' style='width: 99%; height: auto; border: 1px outset #707070;' onClick='showhide_hasusers();'>Calculating Broadcast Users.....<IMG style='display: inline;' src='./images/owmloadingsmall.gif'></DIV>
					<DIV id='has_users' style='display: none; width: 99%; height: 40px; background-color: yellow; color: #000000; z-index: 999999; overflow-y: scroll;'></DIV>
					<DIV id='has_messages' style='width: 99%; height: 50%; background-color: #000000; color: #FFFFFF; overflow-y: scroll; z-index: 9999;'></DIV>
					<FORM NAME = 'has_form' METHOD = post ACTION = "javascript: void(0)">
					<INPUT TYPE = 'text' NAME = 'has_text' ID = 'has_text' style='width: 95%;' value = "" placeholder="enter your broadcast message" aria-label='enter your broadcast message' />
					<BUTTON ID='has_send' VALUE="Send" onclick = "has_check ( this.form.has_text.value.trim() )" STYLE = "margin-left:16px;" aria-label='Send'>Send</BUTTON>
					<BUTTON ID='has_cancel' VALUE="Cancel" onclick = "can_has();" STYLE = "margin-left:24px;" aria-label='Cancel'>Cancel</BUTTON>
					</FORM>
					</SPAN>
				</DIV>
<?php
				} else {
				$alertswidth = "60%";
				}
?>
			<DIV id='alertswrapper' style='display: inline-block; width: <?php print $alertswidth;?>; height: 100%; vertical-align: top; border: 1px outset #707070; float: right;'>
				<DIV id='alertsheader' class='header text_center text_bold text_biggest' style='display: block; width: 99%; text-align: middle;'>Responder Alerts</DIV>
				<DIV id='alerts' style='display: inline-block; width: 96%;'>&nbsp;</DIV>
			</DIV>
		</DIV>
		<DIV id='stats_wrapper' style='width: 100%; position: absolute; bottom: 2px; left: 0px; text-align: center;'>
			<DIV id='stats_heading' class = 'header text_center text_bold text_biggest' style='width: 100%; padding-top: 2px; padding-bottom: 2px;'>Statistics</DIV>
			<TABLE id='stats_table' style='width: 100%; border: 1px solid #FFFFFF;'>
				<TR class='heading' style='width: 100%; border: 1px solid #FFFFFF;'>
					<TH class='heading' onMouseover="Tip('Number of Incidents');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>Number of Tickets</TH>
					<TH class='heading' onMouseover="Tip('Number of Incidents not assigned');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>Number Not Assigned</TH>
					<TH class='heading' onMouseover="Tip('Number of Responders on Scene');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>Responders on Scene</TH>
					<TH class='heading' onMouseover="Tip('Average time to dispatch (Days Hours-Mins:Secs)');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>Average Time To Dispatch</TH>
					<TH class='heading' onMouseover="Tip('Average time ticket is open (Days Hours-Mins:Secs)');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>Average Time Ticket Open</TH>
					<TH class='heading' onMouseover="Tip('Number of available responders');" onMouseOut="UnTip();" style='width: 16%; text-align: center; border: 1px solid #FFFFFF;'>Available Responders</TH>
				</TR>
				<TR class='even' style='width: 100%; border: 1px solid #FFFFFF; height: 40px;'>
					<TD id='s1' class='text_biggest text_bold text_blue' tabindex=5003 roll='button' aria-label='Number of Incidents' style='width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
					<TD id='s2' class='text_biggest text_bold text_blue' tabindex=5004 roll='button' aria-label='Number of Incidents not assigned' style='width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
					<TD id='s3' class='text_biggest text_bold text_blue' tabindex=5005 roll='button' aria-label='Number of Responders on scene' style='width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
					<TD id='s4' class='text_biggest text_bold text_blue' tabindex=5006 roll='button' aria-label='Average time to dispatch and incident' style='width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
					<TD id='s5' class='text_biggest text_bold text_blue' tabindex=5007 roll='button' aria-label='Average time Incident is open' style='width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
					<TD id='s6' class='text_biggest text_bold text_blue' tabindex=5008 roll='button' aria-label='Number of available Responders' style='width: 16%; text-align: center; background-color: #CECECE; border: 1px solid #707070; cursor: default;'></TD>
				</TR>
			</TABLE>
		</DIV>
	</DIV>
	<DIV id='detail' style='position: absolute; right: 0px; top: 0px; width: 80%; height: 80%; display: none; border: 7px outset #707070; z-index: 999999; background-color: <?php print $divBG;?>;'>
		<DIV id='detail_buttons' style='padding-top: 10px; padding-bottom: 10px; width: 100%; height: 30px; text-align: center; display: block; background-color: <?php print $divHead;?>;'>
			<SPAN id='close_but' roll='button' tabindex=1000 aria-label='Close Incident Detail'  class='plain text' style='position: absolute; right: 0px; top: 0px; float: none; vertical-align: middle; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('detail').style.display='none';"><SPAN STYLE='float: left;'></SPAN><IMG STYLE='float: right;' SRC='./images/close.png' BORDER=0></SPAN>
		</DIV>
		<DIV id='ticket_div' style='position: relative; top: 0px; left: 0px; height: 95%; line-height: 20px; width: 100%; display: inline-block; z-index: 999999;'>
			<DIV id='ticket_header' class='header text_biggest text_center' style='vertical-align: top; width: 100%; display: inline-block; background-color: #000000; color: #FFFFFF;'>Ticket Details<SPAN id='edit_but' style='float: right;'></SPAN></DIV>
			<DIV id='ticket_details' style='max-height: 45%; overflow-y: auto; width: 100%; background-color: <?php print $divBG;?>;'></DIV><BR />
			<DIV id='assigns_header' class='header text_biggest text_center' style='povertical-align: top; width: 100%; display: inline-block; background-color: <?php print $divBG;?>;'>Assignments</DIV>
			<DIV id='assigns_details' style='max-height: 45%; overflow-y: auto; background-color: #CECECE;'></DIV>
			<BR /><BR />
		</DIV>
	</DIV>
	<DIV id='incoptions' style='position: absolute; right: 10%; top: 10%; display: none; border: 3px outset #707070; z-index: 999998; background-color: <?php print $divBlackShade;?>;'>
		<DIV id='incoptions_buttons' style='padding-top: 10px; padding-bottom: 10px; text-align: center; display: block;'>
			<SPAN id='incoptions_close_but' roll='button' tabindex=1000 aria-label='Close Form'  class='plain text' style='position: absolute; right: 0px; top: 0px; float: none; vertical-align: middle; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('incoptions').style.display='none';"><SPAN STYLE='float: left;'></SPAN><IMG STYLE='float: right;' SRC='./images/close.png' BORDER=0></SPAN>
		</DIV>
		<DIV id='incoptions_details' style='position: relative; top: 15px; overflow-y: auto;'></DIV>
	</DIV>
	<DIV id='extra' style='position: absolute; right: 10%; top: 10%; display: none; border: 3px outset #707070; z-index: 999999; background-color: <?php print $divBG;?>;'>
		<DIV id='extra_buttons' style='padding-top: 10px; padding-bottom: 10px; width: 100%; height: 30px; text-align: center; display: block; background-color: <?php print $divHead;?>;'>
			<SPAN id='extra_close_but' roll='button' tabindex=1000 aria-label='Close Form'  class='plain text' style='position: absolute; right: 0px; top: 0px; float: none; vertical-align: middle; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('extra').style.display='none';"><SPAN STYLE='float: left;'></SPAN><IMG STYLE='float: right;' SRC='./images/close.png' BORDER=0></SPAN>
		</DIV>
		<DIV id='extra_header' class='header text_biggest text_center' style='position: relative; top: 10px; vertical-align: top; width: 100%; display: inline-block;'></DIV>
		<DIV id='extra_details' style='position: relative; top: 15px; height: 90%; width: auto; overflow-y: auto;'></DIV>
	</DIV>
	<SPAN ID='whom' style='display: none;' aria-hidden='true'><?php print $the_whom;?></SPAN>
	<SPAN ID='user_id' style='display: none;' aria-hidden='true'><?php print $user_id;?></SPAN>
</DIV>
<SCRIPT>
document.addEventListener("keyup", function(event) {	//	Captures return key click on login button to simulate it being an input button
	event.preventDefault();
	if (event.keyCode == 13 || event.keyCode == 32) {
		document.activeElement.click();
		}
	});
	
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
document.body.style.overflow = "hidden";
set_fontsizes(viewportwidth, "fullscreen");
outerwidth = viewportwidth;
outerheight = viewportheight * .98;
mapWidth = outerwidth * .35;
mapHeight = outerheight * .5;
listHeight = outerheight * .35;
middlerowWidth = outerwidth;
middlerowHeight = outerheight * .25;
colwidth = outerwidth;
listwidth = outerwidth * .95;
middlesubcolumnWidth = (middlerowWidth * .98) / 3;
messageColumnHeight = middlerowHeight * .69;
bottomRowHeight = outerheight * .10;
add_winWidth = outerwidth * .70;
edit_winWidth = outerwidth * .90;
mess_winWidth = outerwidth * .80;
note_winWidth = outerwidth * .40;
pat_winWidth = outerwidth * .60;
act_winWidth = outerwidth * .40;
disp_winWidth = outerwidth * .80;
add_winHeight = outerheight * .95;
edit_winHeight = outerheight * .95;
mess_winHeight = outerheight * .80;
note_winHeight = outerheight * .40;
pat_winHeight = outerheight * .75;
act_winHeight = outerheight * .70;
disp_listheight = outerheight * .70;
var alertswidth = (theBroadcast == 1) ? middlesubcolumnWidth * .9 : middlesubcolumnWidth * 1.8;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
if($('leftcol')) {$('leftcol').style.width = outerwidth + "px";}
if($('leftcol')) {$('leftcol').style.height = (outerheight *.96) + "px";}
if($('messages')) {$('messages').style.width = (middlesubcolumnWidth * 1.2) + "px";}
if($('messagestable')) {$('messagestable').style.height = messageColumnHeight + "px";}
if($('the_msglist')) {$('the_msglist').style.height = messageColumnHeight + "px";}
if($('requests')) {$('requests').style.width = (middlesubcolumnWidth * 1.2) + "px";}
if($('buttons')) {$('buttons').style.width = (middlesubcolumnWidth * 1.2) + "px";}
if($('incbuttons')) {$('incbuttons').style.height = listHeight + "px";}
if($('incbuttons')) {$('incbuttons').style.width = outerwidth + "px";}
if($('middle')) {$('middle').style.width = middlerowWidth + "px";}
if($('middle')) {$('middle').style.height = middlerowHeight + "px";}
if($('alertswrapper')) {$('alertswrapper').style.width = alertswidth + "px";}
if($('center_column')) {$('center_column').style.width = (middlesubcolumnWidth * 1.2) + "px";}
if($('imwrapper')) {$('imwrapper').style.width = (middlesubcolumnWidth * .8) + "px";}
get_scheduled_number();
if(showStats == 1) {
	if($('stats_wrapper')) {$('stats_wrapper').style.height = bottomRowHeight + "px";}
	if($('stats_table')) {$('stats_table').style.height = bottomRowHeight * .8 + "px";}
	if($('stats_wrapper')) {$('stats_wrapper').style.width = viewportwidth + "px";}
	if($('stats_heading')) {$('stats_heading').style.width = viewportwidth + "px";}
	if($('stats_table')) {$('stats_table').style.width = viewportwidth + "px";}
	}
$('theHeading').innerHTML = heading;
</SCRIPT>
</BODY>
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
</HTML>
