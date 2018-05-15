<?php
require_once('./incs/functions.inc.php');
$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$isGuest = (is_guest()) ? 1 : 0;
$interval = 48;				// booked date limit - hide if date is > n hours ahead of 'now'
$blink_duration = 5;		// blink for n (5, here) minutes after ticket was written
$button_height = 50;		// height in pixels
$button_width = 160;		// width in pixels
$button_spacing = 4;		// spacing in pixels
$dispWin_width = round (0.8 * ($_SESSION['scr_width']));
$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$ticket_id = (array_key_exists('ticket_id', $_GET) ) ? $_GET['ticket_id'] : "";
$currentSelected = (array_key_exists('mobile_selected', $_SESSION) && $_SESSION['mobile_selected'] != 0) ? $_SESSION['mobile_selected'] : 0;
define("UNIT", 0);
define("MINE", 1);
define("ALL", 2);
$initScreen = "";
$selected = (array_key_exists('mobile_selected', $_SESSION) && $_SESSION['mobile_selected'] != "undefined") ? $_SESSION['mobile_selected'] : 0;

if (array_key_exists('frm_mode', $_GET)) {
	$mode =  $_GET['frm_mode'];
	print "MODE: " . $mode . "<BR />";
	if($mode == UNIT) {
		$initScreen = (get_variable('restrict_units') == "1") ? "hide_topframe(); hide_barselect();" : "";
		}
	} else {
	if (is_unit())  {
		$mode = UNIT;
		$initScreen = (get_variable('restrict_units') == "1") ? "hide_topframe(); hide_barselect();" : "";
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";			
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$user_row = stripslashes_deep(mysql_fetch_assoc($result));
		$mode = (intval ($user_row['responder_id'])>0)? MINE: ALL;		// $mode => 'all' if no unit associated this user - 10/3/10
		}
	}		// end if/else initialize $mode
	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";			
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$user_row = stripslashes_deep(mysql_fetch_assoc($result));
$unit_id =  intval($user_row['responder_id']);

$users_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result_users = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_users = stripslashes_deep(mysql_fetch_assoc($result_users))) 	{
	$users_arr[$row_users['id']] = $row_users['responder_id'];
	}

function get_unitname($id) {
	$query = "SELECT * FROM  `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$return = $row['handle'];
		} else {
		$return = "Unk";
		}
	return $return;
	}

$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
$unitname = get_unitname($unit_id);

function get_butts($ticket_id, $unit_id) {
	global $patient, $unitname;
	$win_height =  get_variable('map_height') + 120;
	$win_width = get_variable('map_width') + 10;
	if ($_SESSION['internet']) {
		print "<INPUT TYPE='button' ID='map_but' CLASS = 'btn_smaller text_big' VALUE = 'Map' onClick  = \"open_map_window();\" />\n";
		}
	if (can_edit()) {		// 5/23/11
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'New' onClick = \"open_add_window();\" />\n";
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Edit' onClick = \"open_edit_window();\" />\n";

		if (!is_closed($ticket_id)) {
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Close' onClick = \"open_closein_window();\" />\n";
			}
		} 		// end if ($can_edit())
	if (is_administrator() || is_super() || is_unit()){
		if (!is_closed($ticket_id)) {
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Action' onClick  = \"open_action_window();\" />\n";
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = '{$patient}' onClick  = \"open_patient_window();\" />\n";
			}
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Notify' onClick  = \"open_notify_window();\" />\n";
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Email " . get_text("Units") . "' onClick = 'do_mail_win();' />\n";
		}
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Note' onClick = \"open_note_window();\" />\n";
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'E-mail' onClick = \"open_mail_window();\" />\n";
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Dispatch' onClick = \"open_dispatch_window();\" />\n";
	if (is_administrator() || is_super() || is_unit()){
		print "<BR /><INPUT TYPE='button' ID='all_switch' CLASS = 'btn_smaller text_big' VALUE = 'All " . get_text("Calls") . "' onClick = 'switch_allcalls();' />\n";
		}		
	}				// end function get butts()



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD><TITLE>Tickets - Mobile Module</TITLE>
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
#leftcol {float: left; text-align: center;}
#middlecol {float: left; padding: 10px; text-align: center; margin-right: 30px;}
#rightcol {float: left; text-align: center;}
input.btn_chkd {margin-top: 5px; width: 160px; height: 50px; color:#050; background-color:#EFEFEF;  border:1px solid;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center; border-radius:.5em; } 
input.btn_not_chkd {margin-top: 5px; width: 160px; height: 50px; color:#050; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; border-radius:.5em;} 
input.btn_smaller {margin-top: 5px; width: 160px; height: 50px; color:#050; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; border-radius:.5em;} 
input:hover {background-color: white; border-width: 4px; border-STYLE: outset;}
</STYLE>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT>
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
<script type="application/x-javascript" src="./js/usng.js"></script>
<script type="application/x-javascript" src="./js/osgb.js"></script>
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
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var theTickets = [];
var theTicket = 0;
var theAssign = 0;
var theUnit = <?php print $unit_id;?>;
var minimap;
var dispWin_width = <?php print $dispWin_width;?>;
var mapWidth =  <?php print get_variable('map_width');?>;
var mapHeight = <?php print get_variable('map_height');?>;
var theWinHeight = mapHeight + 120;
var theWinWidth = mapWidth + 10;
var listHeight;
var colwidth;
var listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var t_interval = null;
var tkts_interval = null;
var num_tickets = 0;
var latest_responder = 0;
var latest_facility = 0;
var inc_last_display = 0;
var inc_period_changed = 0;
var do_inc_refresh = false;
var do_update = true;
var do_resp_update = true;
var do_resp_refresh = false;
var do_fac_update = true;
var tickets_updated = [];
var responders_updated = [];
var facilities_updated = [];
var inc_period = 0;
var last_disp = 0;
var mapCenter;
var mapZoom;
var colors = new Array ('odd', 'even');
var is_messaging = parseInt("<?php print get_variable('use_messaging');?>");
var current_selected = <?php print $currentSelected;?>;;
var topisHidden = false;
var theMode = 0;

parent.frames["upper"].$("gout").style.display  = "inline-block";
parent.frames["upper"].mu_init ();

function do_logout() {
	show_topframe();
	document.gout_form.submit();
	}

function do_save_handleResult(req) {			// the called-back function
	}			// end function handle Result()
	
function mobile_selected(in_val) {
	window.current_selected = in_val;
	var params = "f_n=mobile_selected&v_n=" + in_val + "&sess_id=<?php print get_sess_key(__LINE__); ?>";
	var url = "persist2.php";
	sendRequest (url, do_save_handleResult, params);
	}		// end function	
	
function do_save(in_val) {
	if(in_val == "h") {topisHidden = true;} else {topisHidden = false;}
	var params = "f_n=show_hide_upper&v_n=" + in_val + "&sess_id=<?php print get_sess_key(__LINE__); ?>";
	var url = "persist2.php";								//	3/15/11
	sendRequest (url, do_save_handleResult, params);
	}		// end function	

var row_str;
var frames_obj = window.top.document.getElementsByTagName("frameset")[0];
var rows_arr = frames_obj.rows.split(",", 4);
if(rows_arr.length == 3) {
	rows_arr[1] = 0;
	row_str = rows_arr.join(",");
	} else {
	row_str = rows_arr.join(",");
	}

if (parseInt(rows_arr[0]) > 0) { 							// save as the normalizing string
	var temp = rows_arr.join(",");
	frames_obj.rows = temp;
	row_str = temp;
	}

function hide_topframe() {
	frames_obj = window.top.document.getElementsByTagName("frameset")[0];
	rows_arr = frames_obj.rows.split(",", 4);	
	rows_arr[0] = 0;
	frames_obj.rows = rows_arr.join(",");						// string to attribute - hide the top frame
	do_save("h");
	$('logout_but').style.display = "inline-block";
	}
	
function show_topframe() {
	frames_obj.rows = row_str;								// make top frame visible
	$('logout_but').style.display = "none";
	}

function showhideFrame(btn) {
	frames_obj = window.top.document.getElementsByTagName("frameset")[0];
	rows_arr = frames_obj.rows.split(",", 4);		
	if (parseInt(rows_arr[0]) > 0){ 
		rows_arr[0] = 0;
		frames_obj.rows = rows_arr.join(",");						// string to attribute - hide the top frame
		do_save("h");
		if($(btn)) { btn.value = "Show Menu"; }
		$('logout_but').style.display = "inline-block";
		} else {
		frames_obj.rows = row_str;								// make top frame visible
		do_save("s");
		if($(btn)) { btn.value = "Hide Menu"; }
		$('logout_but').style.display = "none";
		}
	}

function checkUpper() {
	var upperVis = "<?php print $_SESSION['show_hide_upper'];?>";
	if (upperVis == "h") {
		rows_arr[0] = 0;
		frames_obj.rows = rows_arr.join(",");		// string to attribute - hide the top frame
		if($('b1')) { $('b1').value = "Show Menu"; }
		} else {
		frames_obj.rows = row_str;				// make top frame visible
		if($('b1')) { $('b1').value = "Hide Menu"; }
		}
	}	

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
	
function hide_barselect() {
	if($('hide_topbar')) {$('hide_topbar').style.display = 'none';}
	if($('show_topbar')) {$('show_topbar').style.display = 'none';}
	if($('logout_but')) {$('logout_but').style.display = 'inline-block';}
	}
	
function show_btns_closed() {						// 4/30/10
	$('btn_go').style.display = 'inline';
	$('btn_can').style.display = 'inline';
	}
function hide_btns_closed() {
	$('btn_go').style.display = 'none';
	$('btn_can').style.display = 'none';
	document.frm_interval_sel.frm_interval.selectedIndex=0;
	}

var announce = true;
function handleResult(req) {			// the called-back function
	}			// end function handle Result()

function toss() {				// ignores button click
	return;
	}

var watch_val;										// interval var - for clearInterval() - 2/19/12

function start_watch() {							// get initial values from top
	parent.frames['upper'].mu_init();				// start the polling
	if($("div_ticket_id")) { $("div_ticket_id").innerHTML = parent.frames["upper"].$("div_ticket_id").innerHTML;	}	// copy for monitoring
	if($("div_assign_id")) { $("div_assign_id").innerHTML = parent.frames["upper"].$("div_assign_id").innerHTML; }
	if($("div_action_id")) { $("div_action_id").innerHTML = parent.frames["upper"].$("div_action_id").innerHTML;	}
	if($("div_patient_id")) { $("div_patient_id").innerHTML = parent.frames["upper"].$("div_patient_id").innerHTML; }
	watch_val = window.setInterval("do_watch()",5000);		// 4/7/10 - 5 seconds
	}				// end function start watch()

function end_watch(){
	window.clearInterval(watch_val);
	do_reload();			// 6/3/2013
	}				// end function end_watch()

function do_watch() {								// monitor for changes - 4/10/10, 6/10/11
	if (							// any change?
		($("div_ticket_id").innerHTML != parent.frames["upper"].$("div_ticket_id").innerHTML) ||
		($("div_assign_id").innerHTML != parent.frames["upper"].$("div_assign_id").innerHTML) ||
		($("div_action_id").innerHTML != parent.frames["upper"].$("div_action_id").innerHTML) ||
		($("div_patient_id").innerHTML != parent.frames["upper"].$("div_patient_id").innerHTML)			
		)
			{			  // a change
			end_watch();
			do_reload();			
		}
	}			// end function do_watch()	
	
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

function isFramed() {
	if(top.location == self.location) {return true;} else {return false;}
	}

function set_assign(which) {
	if (!(parseInt(theAssign)) > 0) {return;}		
	var params = "frm_id=" + theAssign;
	params += "&frm_tick=" + theTicket;
	params += "&frm_unit=" + theUnit;
	params += "&frm_vals=" + dbfns[which];
	sendRequest ('assigns_t.php',handleResult, params);			// does the work
	var curr_time = do_time();
	replaceButtonText(btn_ids[which], btn_labels[which] + curr_time)
	CngClass(btn_ids[which], 'btn_chkd');				// CngClass(obj, the_class)
	if(isFramed()) {
		parent.frames['upper'].show_msg (btn_labels_full[which] + curr_time);
		}
	if(which == "c") {
		current_selected = 0;
		load_tickets();
		} else {
		load_ticket(theTicket, theAssign);
		}
	}		// end function set_assign()

function set_rec_fac(which) {	//	10/18/11 function to update receiving facility
	if (!(parseInt(theAssign)) > 0) {return;}		
	var params = "rec_fac=" +which;
	params += "&unit=" + theUnit;
	params += "&tick_id=" + theTicket;
	params += "&frm_id=" + theAssign;		
	sendRequest ('rec_fac_t.php',handleResult, params);			// does the work	
	if(isFramed()) {
		parent.frames['upper'].show_msg ("Receiving Facility Updated");
		}
	load_ticket(theTicket, theAssign, current_selected);
	}	//	end function set_rec_fac
		
function do_blink() {																// 2/27/12
	for(i=0; i<document.getElementsByTagName("blink").length; i++){					// each element
		s=document.getElementsByTagName("blink")[i];
		s.style.visibility=(s.style.visibility=='visible')?'hidden':'visible';		// swap visibility
		}
	blink_count--;								// limit blink duration
	if (blink_count==0) {end_blink();}
	}		// end function do_blink()

var blink_var = false;
var blink_count;									// duration of blink

function start_blink () {
	var temp = document.getElementsByTagName("blink").length;
	if (document.getElementsByTagName("blink").length > 0){			// don't bother if non set
		blink_var = setInterval('do_blink()',500);					// on/off cycle is once per second
		blink_count = 60;											// = 60 seconds
		}
	}
function end_blink() {
	for(i=0; i<document.getElementsByTagName("blink").length; i++){		//  force visibility each element
		s=document.getElementsByTagName("blink")[i];
		s.style.visibility='visible';	
		}	
	if (blink_var) {clearInterval(blink_var);}
	}

function do_incident_refresh() {
	window.do_inc_refresh = true; 
	$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>"; 
	load_incidentlist(window.inc_field, window.inc_direct);
	}

function secondsToTime(secs) {
	var numdays = Math.floor(secs / 86400);
	var numhours = Math.floor((secs % 86400) / 3600);
	var numminutes = Math.floor(((secs % 86400) % 3600) / 60);
	var numseconds = ((secs % 86400) % 3600) % 60;
	var outputText =  numdays + "D " + numhours + ":" + numminutes + ":" + Math.round(numseconds);
	return outputText;
	}
	
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
	outerwidth = viewportwidth;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .15;
	colheight = outerheight * .95;
	middlecolwidth = outerwidth * .6;
	middlecolheight = outerheight * .95;	
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('middlecol').style.width = middlecolwidth + "px";
	$('middlecol').style.height = middlecolheight + "px";
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";
	set_fontsizes(viewportwidth, "fullscreen");
	load_buttons(theTicket, theAssign);
	load_tickets();
	}
	
function loadData() {
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
	outerwidth = viewportwidth;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .15;
	colheight = outerheight * .95;
	middlecolwidth = outerwidth * .6;
	middlecolheight = outerheight * .95;	
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('middlecol').style.width = middlecolwidth + "px";
	$('middlecol').style.height = middlecolheight + "px";
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";
	set_fontsizes(viewportwidth, "fullscreen");
	load_buttons(theTicket, theAssign);
	load_tickets();
	}
	
function pageUnload() {
	clearInterval(t_interval); 
	}

var thelevel = '<?php print $the_level;?>';

function get_new_colors() {
	window.location.href = 'main.php';
	}
	
function out_frames() {		//  onLoad = "out_frames()"
	if (top.location != location) {
		top.location.href = document.location.href;
		location.href = '#top'; 
		} else {
		location.href = '#top';
		}
	}		// end function out_frames()
	
function ck_frames() {
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		} else {
		try {
			parent.upper.show_butts();
			} 
		catch (e) {
			}
		parent.upper.do_day_night("<?php print $_SESSION['day_night'];?>")
		try {
			parent.upper.theConnection();
			} 
		catch (e) {
			}
		}
	}
	
function switch_allcalls() {
	if(!window.theMode) {window.theMode = 1; $('all_switch').value="My Calls";} else {window.theMode = 0; $('all_switch').value="All Calls";}
	load_tickets();
	}
	
function load_buttons(tick_id, assign_id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	if(tick_id == 0) {
		var url = './ajax/mobile_buttons.php?version=' + randomnumber;
		} else if(tick_id != 0 && assign_id != 0) {
		var url = './ajax/mobile_buttons.php?ticket_id=' + tick_id + '&assign_id=' + assign_id + '&version=' + randomnumber;			
		} else if(tick_id != 0 && assign_id == 0) {
		var url = './ajax/mobile_buttons.php?ticket_id=' + tick_id + '&version=' + randomnumber;				
		} else {
		var url = './ajax/mobile_buttons.php?version=' + randomnumber;			
		}
	sendRequest (url,buttons_cb, "");
	function buttons_cb(req) {
		var but_arr = JSON.decode(req.responseText);
		var the_info = "<TABLE style='padding-top: 20px;'><TR><TD>";
		for (i = 0; i < but_arr.length-1; i++) {
			the_info += but_arr[i];
			}
		the_info += "</TD></TR></TABLE>";
		$('rightcol').innerHTML = the_info;
		}				// end function buttons_cb()
	}				// end function load_buttons()
	
function load_tickets() {
	var selected = window.current_selected;
	var randomnumber=Math.floor(Math.random()*99999999);
	if(window.theMode ==0) {
		var url = './ajax/mobile_tktlist.php?selected=' + selected + '&version=' + randomnumber;
		} else {
		var url = './ajax/mobile_tktlist.php?frm_mode=2&selected=' + selected + '&version=' + randomnumber;			
		}
	sendRequest (url,tktlst_cb, "");
	function tktlst_cb(req) {
		var theOutput = "";
		var tkts_arr = JSON.decode(req.responseText);
		var unitStr = tkts_arr[0][4];
		theOutput += "<TABLE BORDER=0 CLASS='calls' WIDTH='100%'>";
		if(tkts_arr[0][5] == 0) {
			theOutput += "<TR CLASS = 'even'><TH COLSPAN=99 ALIGN='center'>No Current calls for " + unitStr + "</TH></TR>";	
			theOutput += "</TABLE>";
			$('m_top').innerHTML = theOutput;
			$('m_middle').innerHTML = "";
			$('m_bottom') .innerHTML = "";
			$('rightcol').innerHTML = "";
			tickets_get();
			} else {
			if(num_tickets != tkts_arr[0][5]) {
				theOutput += "<TR CLASS = 'even'><TH COLSPAN=99 ALIGN='center'>Current calls for " + unitStr + "</TH></TR>";	
				theOutput += "<TR CLASS = 'even'><TD COLSPAN=99 CLASS='even'>&nbsp;</TD></TR>";	
				theOutput += "<TR CLASS = 'even' WIDTH='100%'>";
				theOutput += "<TH class='heading text_large text_left'>&nbsp;&nbsp;</TH>";
				theOutput += "<TH class='heading text_large text_left'>Unit</TH>";
				theOutput += "<TH class='heading text_large text_left'>Assigns</TH>";
				theOutput += "<TH class='heading text_large text_left'>Scope</TH>";
				theOutput += "<TH class='heading text_large text_left'>Address</TH>";
				theOutput += "<TH class='heading text_large text_left'>Date</TH>";
				theOutput += "<TH class='heading text_large text_left'>&nbsp;</TH>";
				theOutput += "<TH class='heading text_large text_left'>Type</TH></TR>";	
				for (i = 0; i < tkts_arr.length; i++) {
					theOutput += tkts_arr[i][3];
					var tickId = tkts_arr[i][0];
					theTickets[tickId] = tkts_arr[i][2];
					}
				theOutput += "</TABLE>";
				$('m_top').innerHTML = theOutput;
				var TheAssign = (tkts_arr[selected][1] == "") ? 0 : tkts_arr[selected][1];
				load_ticket(tkts_arr[selected][0], TheAssign, selected);
				num_tickets = tkts_arr[0][5];
				}
			}
		}				// end function tktlst_cb()	
	}
	
function tickets_get() {
	if (tkts_interval!=null) {return;}
	tkts_interval = window.setInterval('tickets_loop()', 5000);
	}			// end function mu get()
	
function tickets_loop() {
	load_tickets();
	}			// end function do_loop()
	
function load_messages() {
	if(is_messaging == 0) {
		return;
		}
	$('m_middle').style.display = 'block';
	$('m_middle').innerHTML = "";
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/mob_messagelist.php?ticket_id=' + theTicket + '&version=' + randomnumber;
	sendRequest (url,msglst_cb, "");
	function msglst_cb(req) {
		var theOutput = "";
		var msgs_arr = JSON.decode(req.responseText);
		var unitStr = msgs_arr[0][10];
		theOutput += "<TABLE BORDER=0 CLASS='calls' WIDTH='100%'>";
		if(msgs_arr[0][10] == "NA") {
			var title1 = "Messages regarding ";
			} else {
			var title1 = "Messages for " + msgs_arr[0][10] + " regarding ";
			}
		theOutput += "<TR CLASS = 'even'><TH COLSPAN=99 ALIGN='center'>" + title1 + "Ticket <SPAN style='background-color: #CECECE; color: #000000;'>" + msgs_arr[0][9] + "</SPAN></TH></TR>";	
		theOutput += "<TR CLASS = 'even'><TD COLSPAN=99 CLASS='even'>&nbsp;</TD></TR>";	
		if(msgs_arr[0][12] == 0) {
			theOutput = "<TABLE BORDER=0 CLASS='calls' WIDTH='100%'>";
			theOutput += "<TR CLASS = 'even'><TH COLSPAN=99 ALIGN='center'>No " + title1 + "Ticket <SPAN style='background-color: #CECECE; color: #000000;'>" + msgs_arr[0][9] + "</SPAN></TH></TR>";	
			theOutput += "</TABLE>";
			$('m_middle').innerHTML = theOutput;
			} else {
			var theClass = "odd";
			theOutput += "<TR CLASS = 'even'><TH class='heading text text_left' style='text-align: left;'>Type</TH><TH class='heading text text_left' style='text-align: left;'>From</TH><TH class='heading text text_left' style='text-align: left;'>Subject</TH><TH class='heading text text_left' style='text-align: left;'>Message</TH><TH class='heading text text_left' style='text-align: left;'>Date</TH></TR>";	
			for (i = 0; i < msgs_arr.length; i++) {
				theOutput += "<TR CLASS = '" + theClass + "' style='text-align: center;' onClick=\"window.open('message.php?mode=1&id=" + msgs_arr[i][0] + "&screen=mobile&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">";
				theOutput += "<TD class='td_data tablecell text' style='" + msgs_arr[i][8] + "'>" + msgs_arr[i][2] + "</TD>";
				theOutput += "<TD class='td_data tablecell text'>" + msgs_arr[i][3] + "</TD>";
				theOutput += "<TD class='td_data tablecell text'>" + msgs_arr[i][4] + "</TD>";
				theOutput += "<TD class='td_data tablecell text'>" + msgs_arr[i][5] + "</TD>";
				theOutput += "<TD class='td_data tablecell text'>" + msgs_arr[i][6] + "</TD>";
				theClass = (theClass == "even") ? "odd" : "even";
				}
			theOutput += "</TABLE>";
			$('m_middle').innerHTML = theOutput;
			}
		}				// end function msglst_cb()	
	}
	
function load_message(id) {
	$('message_details').innerHTML = "";
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/mob_message.php?message_id=' + id + '&version=' + randomnumber;
	sendRequest (url,msg_cb, "");
	function msg_cb(req) {
		var theOutput = "";
		var msg_arr = JSON.decode(req.responseText);
		theOutput += "<TABLE BORDER=0 CLASS='calls' WIDTH='100%'>";
		theOutput += "<TR CLASS = 'even'><TH COLSPAN=99 ALIGN='center'>Message " + id + "</TH></TR>";	
		theOutput += "<TR CLASS = 'even'><TD COLSPAN=99 CLASS='even'>&nbsp;</TD></TR>";	
		var theClass = "odd";
		theOutput += "<TR CLASS = '" + theClass + "'><TD class='td_label text'>Message Type</TD><TD class='td_data' style='" + msg_arr[8] + "'>" + msg_arr[2] + "</TD></TR>";
		theOutput += "<TR CLASS = '" + theClass + "'><TD class='td_label text'>Ticket Number</TD><TD>" + msg_arr[2] + "</TD></TR>";
		theOutput += "<TR CLASS = '" + theClass + "'><TD class='td_label text'>From</TD><TD>" + msg_arr[3] + "</TD></TR>";
		theOutput += "<TR CLASS = '" + theClass + "'><TD class='td_label text'>Date</TD><TD>" + msg_arr[6] + "</TD></TR>";
		theOutput += "<TR CLASS = '" + theClass + "'><TD class='td_label text'>Subject</TD><TD>" + msg_arr[4] + "</TD></TR>";
		theOutput += "<TR CLASS = '" + theClass + "'><TD class='td_label text'>Message</TD><TD>" + msg_arr[5] + "</TD></TR>";
		theClass = (theClass == "even") ? "odd" : "even";
		theOutput += "</TABLE>";
		$('message_details').innerHTML = theOutput;
		$('message_popup').style.display = 'block';
		}				// end function msg_cb()	
	}
	
function refresh_ticket(id, assign_id) {
	theTicket = id;
	theAssign = assign_id;
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/mobile_main.php?ticket_id=' + id + '&version=' + randomnumber;
	sendRequest (url,tkt_cb2, "");
	function tkt_cb2(req) {
		var tkt_arr2 = JSON.decode(req.responseText);
		if(theTickets[id] && (theTickets[id] == tkt_arr2[1])) {
			theTickets[id] = tkt_arr2[1];
			$('m_bottom').innerHTML = tkt_arr2[0];
			load_buttons(id, assign_id);
			load_tickets();
			}
		}				// end function tkt_cb2()		
	}
	
function incident_get() {
	if (t_interval!=null) {return;}
	t_interval = window.setInterval('incident_loop()', 5000);
	}			// end function mu get()
	
function incident_loop() {
	refresh_ticket(theTicket, theAssign);
	}			// end function do_loop()

function load_ticket(id, assign_id, selectedID) {
	theTicket = id;
	theAssign = assign_id;
	mobile_selected(selectedID);
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/mobile_main.php?ticket_id=' + id + '&version=' + randomnumber;
	sendRequest (url,tkt_cb, "");
	function tkt_cb(req) {
		var tkt_arr = JSON.decode(req.responseText);
		$('m_bottom').innerHTML = tkt_arr[0];
		load_buttons(id, assign_id);
		}				// end function tkt_cb()
	load_messages();
	incident_get();
	}
	
function open_map_window () {
	var url = "map_popup.php?id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'mapWindow', 'resizable=1, scrollbars, height=' + theHeight + ', width=' + theWidth + ', left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}
	
function open_act_window(ticket_id, id, action) {
	var url = "action_w.php?mode=1&ticket_id=" + ticket_id + "&id=" + id + "&action=" + action;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'addWindow', 'resizable=1, scrollbars, height=640, width=800, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}

function open_pat_window(ticket_id, id, action) {
	var url = "patient_w.php?mode=1&ticket_id=" + ticket_id + "&id=" + id + "&action=" + action;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'addWindow', 'resizable=1, scrollbars, height=640, width=800, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}

function open_add_window () {
	var url = "add.php?mode=1";
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'addWindow', 'resizable=1, scrollbars, height=640, width=800, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}
	
function open_edit_window () {
	var url = "edit_nm.php?mode=1&id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'editWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}
	
function open_closein_window () {
	var url = "close_in.php?mode=1&ticket_id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'closeinWindow', 'resizable=1, scrollbars, height=480, width=700, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}
	
function open_action_window () {
	var url = "action_w.php?mode=1&ticket_id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'actionWindow', 'resizable=1, scrollbars, height=800, width=800, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}
	
function open_patient_window () {
	var url = "patient_w.php?mode=1&ticket_id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'actionWindow', 'resizable=1, scrollbars, height=480, width=720, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}	
	
function open_notify_window () {
	var url = "config.php?mode=1&func=notify&id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'actionWindow', 'resizable=1, scrollbars, height=400, width=600, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}	
	
function open_note_window () {
	var url = "add_note.php?ticket_id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'actionWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}
	
function open_mail_window () {
	var url = "mail.php?ticket_id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'actionWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}	

function open_dispatch_window () {
	var url = "routes_nm.php?frm_mode=1&ticket_id=" + window.theTicket;
	var theHeight = window.theWinHeight;
	var theWidth = window.theWinWidth;
	var popWindow = window.open(url, 'actionWindow', 'resizable=1, scrollbars, height=480, width=' + dispWin_width + ', left=100,top=100,screenX=100,screenY=100');
	popWindow.focus();
	}
	
function checkWS() {
	if(typeof parent.frames["main"].theConnection == 'function') {
		parent.frames["main"].theConnection();
		}
	if(typeof theConnection == 'function') {
		theConnection();
		}
	if(parent.frames["upper"].isLocal == 1) {
		if(!parent.frames["upper"].checkConn) {
			if(parent.frames["main"].$('has_button')) {parent.frames["main"].$('has_button').style.display = "none";}
			if($('help_but')) {$('help_but').style.display = 'none';}	
			} else {
			if(parent.frames["main"].$('has_button')) {parent.frames["main"].$('has_button').style.display = "block";}
			if($('help_but')) {$('help_but').style.display = 'inline-block';}				
			}
		} else {
		if(!parent.frames["upper"].checkConn) {
			if(parent.frames["main"].$('has_button')) {parent.frames["main"].$('has_button').style.display = "none";}
			if($('help_but')) {$('help_but').style.display = 'none';}	
			} else {
			if(parent.frames["main"].$('has_button')) {parent.frames["main"].$('has_button').style.display = "block";}
			if($('help_but')) {$('help_but').style.display = 'inline-block';}				
			}
		}
	}
	
</SCRIPT>
</HEAD>
<?php

$gunload = "pageUnload();";
?>
<BODY onLoad = "checkWS(); <?php print $initScreen;?> loadData(); ck_frames(); parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<A NAME='top'></A>
<DIV ID = "to_bottom" style='position:fixed; top: 2px; left:5 0px; height: 12px; width: 10px; z-index: 99;' onclick = "location.href = '#bottom';"><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
<DIV id='screenname' style='display: none;'>situation</DIV>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>

<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV id = "outer">
	<DIV id = "leftcol">
<?php
	get_butts($ticket_id, $unit_id);
?>
	</DIV>
	<DIV id = "middlecol">
		<DIV id='logout_line' style='width: 100%; display: inline-block; text-align: center; padding: 10px;'>
			<INPUT id='logout_but' style='display: none;' TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Logout' onClick="do_logout();" />
		</DIV>

		<DIV id='top_line' style='width: 100%; display: inline-block; text-align: center; padding: 10px;'>
<?php
			if($unit_id != 0) {
?>
				<INPUT TYPE='button' ID='help_but' CLASS = 'btn_smaller text_big' STYLE='display: none; background-color: red; color: white;' VALUE = 'Help' onClick = "parent.frames['upper'].broadcast('Responder <?php print $unitname;?> Needs Assistance',99);" />
<?php
				}
?>
			<INPUT id='hide_topbar' style='display: inline-block; width: 200px' TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Hide Top Menu' onClick="hide_topframe(); $('show_topbar').style.display='inline-block'; $('hide_topbar').style.display='none';" />
			<INPUT id='show_topbar' style='display: none; width: 200px' TYPE='button' CLASS = 'btn_smaller text_big' VALUE = 'Show Top Menu' onClick="show_topframe(); $('show_topbar').style.display='none'; $('hide_topbar').style.display='inline-block';" />
		</DIV>
		<DIV id = "m_top" style='max-height: 20%; overflow-y: auto; border: 2px inset #CECECE; padding: 10px;'>
		</DIV>
		<BR /><BR />
		<DIV id = "m_middle" style='max-height: 20; overflow-y: auto; border: 2px inset #CECECE; padding: 10px; display: none;'>
		</DIV>
		<BR /><BR />
		<DIV id = "m_bottom" CLASS='even' style='height: 45%; overflow-y: auto; border: 2px inset #CECECE; padding: 10px;'>
		</DIV><BR /><BR />		
	</DIV>
	<DIV id="message_popup" class='even' style='position: fixed; top: 20%; left: 20%; width: 40%; max-height: 60%; z-index=9999; display: none; border: 2px outset #707070;'>
		<BR />
		<CENTER>
			<SPAN id='msg_close' class='plain' style='text-align: center; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id)' onClick="$('message_popup').style.display='none';">Close</SPAN>
		</CENTER>
		<BR />
		<DIV id='message_details' style='padding: 10px;'></DIV>
	</DIV>
	<DIV id='rightcol'>
	</DIV>
</DIV>
<FORM NAME="gout_form" action="main.php" TARGET = "main">
<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
</FORM>
<DIV id='has_line' style='display: none;'>
	<SPAN id='closeHas' class='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' onClick="$('has_line').style.display = 'none';">Close</SPAN>
	<SPAN id='has_wrapper'><marquee id='has_text' behavior="scroll" direction="left"></marquee></SPAN>
<DIV>	
</BODY>
</HTML>
