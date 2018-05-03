<?php
/*
9/10/13 New file - Index for mobile page
4/24/14 Revised to load map with default position and then move on location found.
*/
require_once('../incs/functions.inc.php');
require_once('./incs/mobile_login.inc.php');
require_once('../incs/browser.inc.php');			// 6/12/10
@session_start();
$c_types = array();
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$logged_in = (!(empty($_SESSION))) ? 1 : 0;
$cycle = 5000;			// user reviseable delay between chat polls, in milliseconds
$list_length = 99;		// chat list length maximum
$browser = trim(checkBrowser(FALSE));						// 6/12/10

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$c_types[$row['id']]['id'] = $row['id'];
	$c_types[$row['id']]['title'] = $row['title'];
	$c_types[$row['id']]['description'] = $row['description'];	
	$c_types[$row['id']]['icon'] = $row['icon'];
	}
if (isset($_GET['logout'])) {
	do_mobile_logout();
	}
if(((!empty($_GET)) && ($_GET['do_login'] == 1)) || (!empty($_POST)) || (intval(get_variable('responder_mobile_forcelogin')) == 1)) {
	do_mobile_login(basename(__FILE__));
	}
$al_names = "";
$the_user = 0;
$the_responder = 0;
$the_email = "";
$poll_cycle_time = 5000;
$chat_user = 0;

$status_vals = array();
$status_vals[''] = $status_vals['0']="TBD";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	}
unset($result_st);

$users_arr = array();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result_users = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_users = stripslashes_deep(mysql_fetch_assoc($result_users))) {
	$users_arr[$row_users['id']] = $row_users['responder_id'];
	}

function get_status_selector($unit_in, $status_val_in, $tbl_in) {
	switch ($tbl_in) {
		case ("u") :
			$tablename = "responder";
			$link_field = "un_status_id";
			$status_table = "un_status";
			$status_field = "status_val";
			break;
		case ("f") :
			$tablename = "facilities";
			$link_field = "status_id";
			$status_table = "fac_status";
			$status_field = "status_val";
			break;
		default:
			print "ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ";	
			}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$tablename}`, `$GLOBALS[mysql_prefix]{$status_table}` WHERE `$GLOBALS[mysql_prefix]{$tablename}`.`id` = $unit_in 
		AND `$GLOBALS[mysql_prefix]{$status_table}`.`id` = `$GLOBALS[mysql_prefix]{$tablename}`.`{$link_field}` LIMIT 1" ;	

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==0) {				// 2/7/10
		$init_bg_color = "transparent";
		$init_txt_color = "black";	
		}
	else {
		$row = stripslashes_deep(mysql_fetch_assoc($result)); 
		$init_bg_color = $row['bg_color'];
		$init_txt_color = $row['text_color'];
		}

	$guest = is_guest();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$status_table}` ORDER BY `group` ASC, `sort` ASC, `{$status_field}` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$dis = ($guest)? " DISABLED": "";								// 9/17/08
	$the_grp = strval(rand());			//  force initial OPTGROUP value
	$i = 0;
	$outstr = ($tbl_in == "u") ? "\t\t<SELECT CLASS='sit' id='frm_status_id_u_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color};' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; update_status({$unit_in}, this.value)' >" :
	"\t\t<SELECT CLASS='sit' id='frm_status_id_f_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color}; width: 90%;' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update_fac({$unit_in}, this.value)' >";	// 12/19/09, 1/1/10. 3/15/11
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		if ($the_grp != $row['group']) {
			$outstr .= ($i == 0)? "": "\t</OPTGROUP>";
			$the_grp = $row['group'];
			$outstr .= "\t\t<OPTGROUP LABEL='$the_grp'>";
			}
		$sel = ($row['id']==$status_val_in)? " SELECTED": "";
		$outstr .= "\t\t\t<OPTION VALUE=" . $row['id'] . $sel ." STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'  onMouseover = 'style.backgroundColor = this.backgroundColor;'>$row[$status_field] </OPTION>";		
		$i++;
		}		// end while()
	$outstr .= "\t\t</OPTGROUP>\t\t</SELECT>";
	return $outstr;
	}

function get_responder_details($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id`= " . $id;
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$ret = $row['contact_via'];
	return $ret;
	}
	
function get_responder_status($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id`= " . $id;
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$ret = $row['un_status_id'];
	return $ret;
	}
	
function get_responder_name($id) {
	if(($id == 0) && (isset($_SESSION['user_id']))) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = " . $_SESSION['user_id'];
		$result = mysql_query($query);	
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['name_f'] . " " . $row['name_l'];
		} elseif(($id == 0) && (!isset($_SESSION['user_id']))) {
		return "NA";
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
		$result = mysql_query($query);	
		if(mysql_num_rows($result) != 0) {
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$ret = $row['name'];	
			return $ret;
			} else {
			return "NA";
			}
		}
	}
	
function get_responder_handle($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id;
	$result = mysql_query($query);	
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret = $row['handle'];	
		return $ret;
		} else {
		return "NA";
		}
	}

if (isset($_SESSION['user_id'])) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id`= '" . $_SESSION['user_id'] . "'";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_responder = (($row['responder_id'] != 0) && ($row['responder_id'] != NULL) && ($row['responder_id'] != "")) ? $row['responder_id']: 0;
	$chat_user = $_SESSION['user_id'];
	$the_user = ($the_responder != 0) ? $the_responder : $_SESSION['user_id'];
	$the_email = ($the_responder != 0) ? get_responder_details($the_user) : $row['email'];
	}

if($the_user != 0) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$the_user' ORDER BY `id` ASC;";
	$result = mysql_query($query);	//	6/10/11
	$al_names = "Showing " . get_text("Region") . "(s): ";	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";
		$result2 = mysql_query($query2);	// 4/18/11
		while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{
				$al_names .= $row2['group_name'] . ", ";
			}
		}
	}

if($the_responder != 0) {
	$the_status_sel = get_status_selector($the_responder, get_responder_status($the_responder), "u");
	}
	
$logged_in_load = ($logged_in == 1) ? "get_conditions(); get_ticket_markers(" . $the_user . ");" : "";
$respondername = get_responder_handle($the_responder);
?>
<!DOCTYPE html>
<html>
<head>
<title>Tickets Mobile Screen</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<link rel="stylesheet" href="../js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
     <link rel="stylesheet" href="../js/leaflet/leaflet.ie.css" />
<![endif]-->
<LINK REL=StyleSheet HREF="./css/stylesheet.php?version=<?php print time();?>" TYPE="text/css">	
<style type="text/css">
	*, html { margin:0; padding:0 }
	div#map_canvas {z-index: 1; position: fixed; top: 0px; left: 0px;}
	div#map_outer { width:100%; height: auto; z-index: 1;}
	div#has_line {z-index: 100; position: fixed; bottom: 50%; left: 10%; width: 80%; line-height: 40px; background-color: yellow; border: 2px outset #707070;}
	#has_wrapper {color: black; font-size: 20px; font-weight: bold; width: 80%; display: inline-block; line-height: 40px; vertical-align: middle;}
	#closeHas {display: inline-block; vertical-align: middle; float: right;}
	div#screen_buttons { width: 100%; height: 50px; position: absolute; bottom: 0px; z-index: 999; text-align: center; }	
	div#app_outer { position: absolute; top: 0px; left: 0%; width: 80%; height: auto; z-index: 6; color: #000000;}
	div#app_title { position: relative; top: 2px; left: 0%; width: 80%; z-index: 6; color: #000000; background-color: #FEFEFE; font-size: 1em; font-weight: bold; display: inline-block; border: 4px outset #DEDEDE;}
	div#info { width:100%; overflow: hidden; text-align: center; top:0; left:0; }
	div#outer { position: absolute; top: 0px; left: 0px; overflow: hidden; text-align: center;}	
	div#menu_but { z-index: 99; display: inline-block; position: fixed; top: 100px; left: 0px; float: left; }
	div#menu_but2 { z-index: 99; display: inline-block; position: fixed; top: 100px; left: 0px; float: left; }	
	div#toggle_tracks_but { z-index: 5; position: fixed; top: 50px; right: 165px; }	
	div#toggle_tracks_off_but { z-index: 5; position: fixed; top: 50px; right: 165px; }	
	div#center_but { z-index: 5; display: inline-block; position: fixed; top: 100px; right: 160px; }		
	div#day_but { z-index: 5; display: inline-block; position: fixed; top: 150px; right: 160px; }
	div#night_but { z-index: 5; display: inline-block; position: fixed; top: 150px; right: 160px; }	
	div#plus_but { z-index: 5; display: inline-block; position: fixed; top: 150px; right: 20px; }
	div#minus_but { z-index: 5; display: inline-block; position: fixed; top: 220px; right: 20px; }
	div#help_but { z-index: 5; display: inline-block; position: fixed; top: 290px; right: 20px; }
	div#map_controls { z-index: 5; display: inline-block; position: fixed; top: 5px; right: 150px;}	
	div#map_but { z-index: 5; display: inline-block; position: fixed; top: 5px; right: 5px; }		
	div#screen_title { width: auto; height: auto; text-align: center; z-index: 5; color: #707070; background-color: #FFFFFF; font-size: 1.5em; font-weight: bold;  }	
	.screen { z-index: 5; position: relative; left: 5%; top: 10%; width:90%; height: auto; background: transparent;}
	.chat_screen { z-index: 5; position: relative; left: 5%; top: 10%; width:90%; height: auto; background: transparent;}	
	.screen_but_hover { display:-moz-inline-block; display:-moz-inline-box; display:inline-block; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 2px; border-STYLE: inset; border-color: #FFFFFF;
  				 padding: 4px; text-decoration: none; background-color: #DEE3E7; font-weight: bolder; text-align: center; width: 15%;}
	.screen_but_hover_r { display:-moz-inline-block; display:-moz-inline-box; display:inline-block; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#FF3366; border-width: 2px; border-STYLE: inset; border-color: #FFFFFF;
  				 padding: 4px; text-decoration: none; background-color: #DEE3E7; font-weight: bolder; text-align: center; width: 15%;}
	.screen_but_plain { display:-moz-inline-block; display:-moz-inline-box; display:inline-block; float: none; font: normal 12px Arial, Helvetica, sans-serif; color: #000000;  border-width: 2px; border-STYLE: outset; border-color: #FFFFFF;
  				 padding: 4px; text-decoration: none; background-color: #EFEFEF; font-weight: bolder; text-align: center; width: 15%; }	
	.signal_b_but { display:-moz-inline-block; display:-moz-inline-box; display:inline-block; float: none; font: normal 12px Arial, Helvetica, sans-serif; color: #FFFFFF;  border-width: 2px; border-STYLE: outset; border-color: #00CCFF;
  				 padding: 4px; text-decoration: none; background-color: #00CCFF; font-weight: bolder; text-align: center; width: 15%; }	
	.signal_r_but { display:-moz-inline-block; display:-moz-inline-box; display:inline-block; float: none; font: normal 12px Arial, Helvetica, sans-serif; color: #FFFFFF;  border-width: 2px; border-STYLE: outset; border-color: #00CCFF;
  				 padding: 4px; text-decoration: none; background-color: #FF3366; font-weight: bolder; text-align: center; width: 15%; }					 
	.lightBox { filter:alpha(opacity=60); -moz-opacity:0.6; -khtml-opacity: 0.6; opacity: 0.6; background-color:white; padding:2px; }
	.hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 2px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none;float: none; background-color: #DEE3E7;font-weight: bolder; width: 100px; text-align: center;}
	.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color: #000000;  border-width: 2px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; float: none; background-color: #EFEFEF;font-weight: bold; width: 100px; text-align: center;}
	.sm_hover 	{ font: normal 10px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  margin: 2px; padding: 2px; text-decoration: none;float: none; background-color: #DEE3E7;font-weight: bolder; width: auto; text-align: center;}
	.sm_plain 	{ font: normal 10px Arial, Helvetica, sans-serif; color: #000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  margin: 2px; padding: 2px; text-decoration: none; float: none; background-color: #EFEFEF;font-weight: bolder; width: auto; text-align: center;}
	.regions_text { font: normal 12px Arial, Helvetica, sans-serif; color: #000000; border-width: 2px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em; text-decoration: none; float: none; background-color: #EFEFEF; text-align: center; width: 100%; word-wrap: break-word;}
	.title_text { z-index: 6; color: #000000; background-color: #CECECE; font-size: 1.5em; font-weight: bold; padding: 10px; border: 2px outset #707070; }	
	.lists {z-index: 5; text-align: left; overflow-y: scroll; display: block; background-color: #EFEFEF; margin: 10px; }
	.detail_page { z-index: 5; text-align: left; height: 60%; overflow-y: auto; overflow-x: hidden; display: none; background-color: #EFEFEF; margin: 10px; }
</style>
<script src="../js/leaflet/leaflet.js"></script>
<script src="../js/misc_function.js" type="text/javascript"></script>
<SCRIPT SRC="../js/usng.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC='../js/jscoord.js' TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC="../js/lat_lng.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC="../js/geotools2.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC="../js/osgb.js" TYPE="text/javascript"></SCRIPT>
<script src="./js/leaflet-openweathermap.js"></script>
<script src="./js/esri-leaflet.js"></script>
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
<script src="./js/Control.Geocoder.js"></script>
<script src="../js/L.Graticule.js" TYPE="text/javascript"></script>
<script src="../js/leaflet-providers.js" TYPE="text/javascript"></script>
<script>
var the_counter = 0;
var current_butt_id = "sb1";
var the_lat;
var the_lng;
var polyline;
var themarkers = new Array();
var responder_id = parseInt("<?php print $the_responder;?>");
var responder_name = "<?php print get_responder_name($the_responder);?>";
var randomnumber;
var url;
var the_assigns_id = 0;
var tick_id = 0;
var place;
var form_add;
var msg_subject;
var msg_text;
var lit=new Array();
var lit_r=new Array();
var posMarker;
var theCircle;
var	theAltitude=0;
var theHeading;
var theSpeed;
var primary_timer;
var secondary_timer;	
var tertiary_timer;	
var track_timer = "<?php print get_variable('responder_mobile_tracking');?>";
var in_local_bool = "<?php print get_variable('local_maps');?>";
var dobroadcast = "<?php print get_variable('broadcast');?>";
var daynight = "day";
var tracksOn = false;
var theLatLng = false;
var chat_id = 0;
var last_tick = 0;
var new_msg = 0;
var chat_user = parseInt("<?php print $chat_user;?>");
var do_chat = false;
var theIcon;

window.onresize=function(){set_size()};

window.onload = function() {
	if(dobroadcast == 1) {start_connection();}
	set_size();
	screen1(); 
	<?php print $logged_in_load;?>
	};
function set_size() {
	var viewportwidth;
	var viewportheight;
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
	var mapWidth = viewportwidth;
	var mapHeight = viewportheight - 20;
	var listHeight = viewportheight * .6;
	$('outer').style.width = viewportwidth + "px";
	$('outer').style.height = viewportheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('alert_list').style.height = listHeight + "px";
	$('alert_detail').style.height = listHeight + "px";	
	$('ticket_list').style.height = listHeight + "px";
	$('ticket_detail').style.height = listHeight + "px";	
	$('ticket_detail_wrapper').style.height = listHeight + "px";		
	$('message_list').style.height = listHeight + "px";
	$('message_detail').style.height = listHeight + "px";
	$('directions_wrapper').style.height = listHeight + "px";		
	$('directions').style.height = listHeight + "px";	
	$('chat').style.height = listHeight + "px";
	initialise();
	}

Number.prototype.between = function(first,last){    return (first < last ? this >= first && this <= last : this >= last && this <= first);}

var condIcon = L.Icon.extend({
    options: {
        iconSize:     [30, 30],
        iconAnchor:   [0, 0],
        popupAnchor:  [-3, -76]
    }
});

var baseIcon = L.Icon.extend({options: {shadowUrl: './images/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [0, 0],	shadowAnchor: [5, -5], popupAnchor: [6, -5]
	}
	});

var circIcon = L.Icon.extend({options: {iconSize: [39, 39],	iconAnchor: [19, 19], popupAnchor: [6, -5]
	}
	});

var rediconUrl = "./images/gen_icon.php?blank=0&text=";
var blackiconUrl = "./images/gen_icon.php?blank=3&text=T";
var yellowiconUrl = "./images/gen_icon.php?blank=2&text=";
var greeniconUrl = "./images/gen_icon.php?blank=1&text=";
var blueiconUrl = "./images/gen_icon.php?blank=4&text=";
var redcircleUrl = "./images/gen_icon.php?blank=5&text=";

var redicon = 		new baseIcon({iconUrl: rediconUrl}),
blackicon = 			new baseIcon({iconUrl: blackiconUrl}),
yellowicon = 			new baseIcon({iconUrl: yellowiconUrl});
greenicon = 			new baseIcon({iconUrl: greeniconUrl});
blueicon = 				new baseIcon({iconUrl: blueiconUrl});
redcircle = 			new circIcon({iconUrl: redcircleUrl});

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}

function do_sb_hover (the_id) {
	CngClass(the_id, 'screen_but_hover');
	return true;
	}

function do_sb_plain (the_id) {
	CngClass(the_id, 'screen_but_plain');
	return true;
	}
	

function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}
	
function $() {
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}

function do_ngs(lat,lng) {											// LL to USNG - 6/2/2013
	var the_grid;
	var loc = <?php print get_variable('locale');?>;
	if(loc == 0) { the_grid = LLtoUSNG(lat,lng,5);			}
	if(loc == 1) { the_grid = LLtoOSGB(lat,lng);			}
	if(loc == 2) { the_grid = do_utm(lat,lng);			}			
	return the_grid;
	}
	
function do_utm(lat_lng) {
	var ll_in = new LatLng(parseFloat(lat), parseFloat(lng));
	var utm_out = ll_in.toUTMRef().toString();
	temp_ary = utm_out.split(" ");
	var the_utm = (temp_ary.length == 3)? temp_ary[0] + " " +  parseInt(temp_ary[1]) + " " + parseInt(temp_ary[2]) : "";
	return the_utm;
	}

function convertDMS( lat, lng ) {
	lat = (lat < 0) ? lat*-1: lat;
	var lat_ns = (lat > 0) ? "N" : "S";
	var lat_deg = Math.floor(lat);
	var lat_deg_points = (lat-lat_deg)*60;
	var lat_min = Math.floor(lat_deg_points);
	var lat_sec = Math.floor((lat_deg_points - lat_min)*60);
	var lng_ew = (lng > 0) ? "E" : "W";	
	lng = (lng < 0) ? lng*-1: lng;
	var lng_deg = Math.floor(lng);
	var lng_deg_points = (lng-lng_deg)*60;
	var lng_min = Math.floor(lng_deg_points);
	var lng_sec = Math.floor((lng_deg_points - lng_min)*60);
	var lng_ew = (lng > 0) ? "E" : "W";
	var theCoords = lat_deg + "\u00B0 " + lat_min + "'" +  lat_sec + "' " + lat_ns + "   " + lng_deg + "\u00B0 " + lng_min + "'" +  lng_sec + "' " + lng_ew;
	return theCoords;	
	}

function update_status(the_unit, the_status) {							// write unit status data via ajax xfer
	var querystr = "frm_responder_id=" + the_unit;
	querystr += "&frm_status_id=" + the_status;
	var url = "../as_up_un_status.php?" + querystr;			// 
	var payload = syncAjax(url);						// 
	if (payload.substring(0,1)=="-") {	
		return false;
		}
	else {
		show_flag ('Your status update applied!');
		return true;
		}				// end if/else (payload.substring(... )
	}		// end function to_server()
	
function get_recfac() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url ='./ajax/get_recfac.php?ticket_id=' + tick_id + '&version=' + randomnumber;	
	sendRequest (url,get_recfac_cb, "");	
	}		// end function get_recfac()
	
function get_recfac_cb(req) {
	var recfac_str=JSON.decode(req.responseText);
	$('recfac_but').innerHTML = recfac_str[0];
	}			// end function get_recfac_cb()	
	
function update_recfac(tick_id, recfac) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url ='./ajax/update_recfac.php?ticket_id=' + tick_id + '&recfac=' + recfac + '&version=' + randomnumber;	
	sendRequest (url,up_recfac_cb, "");	
	}		// end function update_recfac()
	
function up_recfac_cb(req) {
	var up_str=JSON.decode(req.responseText);
	if(up_str[0] == 100) {
		alert("Update Applied");
		} else {
		alert("Update Couldn't be applied, please try again");
		}
	}			// end function up_recfac_cb()	
	
function do_audible() {	// 6/12/10
	try 		{document.getElementsByTagName('audio')[0].play();}
	catch (e) 	{}		// ignore	
	}				// end function do_audible()

function do_logout() {
	clearInterval(primary_timer);
	clearInterval(secondary_timer);
	clearInterval(tertiary_timer);
	document.gout_form.submit();			// send logout 	
	}
	
function show_flag (msg) {	
	$('theFlag').innerHTML = msg;			
	setTimeout("$('theFlag').innerHTML =''", 3000);	// show for 3 seconds
	}
		
function do_login() {
	primary_timer = null;
	secondary_timer = null;
	tertiary_timer = null;
	document.gin_form.submit();			// send login 	
	}

function get_latest_ids() {				// get latest chat invites and new assignments
	var randomnumber=Math.floor(Math.random()*99999999);		
	sendRequest ('./ajax/get_latest_ids.php?version=' + randomnumber,get_latest_id_cb, "");	
	}			// end function do_loop()	

function get_latest_id_cb(req) {					// get_latest_id callback()
	var arr_lgth_good = 3;								// size of a valid returned array
	try {
		var the_id_arr=JSON.decode(req.responseText);
		}
	catch (e) {
		return;
		}

	try {			
		var the_arr_lgth = the_id_arr.length;		// sanity check
		}
	catch (e) {
		return;
		}			
	
	if (the_arr_lgth != arr_lgth_good)  {
		}

	var temp = parseInt(the_id_arr[0]);				// new chat invite?
	if (temp > chat_id) {
		chat_id = temp;
		chat_signal();
		}
	var temp2 = parseInt(the_id_arr[1]);			// new assignment?
	if (temp2 > last_tick) {
		last_tick = temp2;
		inc_signal();
		} else {
		if(lit_r['sb3']) {
			inc_signal_r_off();
			}
		}
	}			// end function get_latest_id_cb()		
	
function chat_signal() {
	if(!$('sb5')) {return; }
	if (lit_r["sb5"]) {return; }
	CngClass("sb5", "signal_r_but");
	lit_r["sb5"] = true;
	do_audible();
	}
	
function chat_signal_r_off() {
	if(!$('sb5')) {return; }
	if (!lit_r["sb5"]) {return; }
	if(!lit["sb5"]) {
		CngClass("sb5", "screen_but_plain");
		lit_r["sb5"] = false;		
		} else {
		CngClass("sb5", "signal_b_but");
		lit_r["sb5"] = false;			
		lit["sb5"] = true;
		}
	}

function inc_signal() {
	if(!$('sb3')) {return; }
	if (lit_r["sb3"]) {return; }
	CngClass("sb3", "signal_r_but");
	lit_r["sb3"] = true;
	do_audible();
	}

function inc_signal_r_off() {
	if(!$('sb3')) {return; }
	if (!lit_r["sb3"]) {return; }
	if(!lit["sb3"]) {
		CngClass("sb3", "screen_but_plain");
		lit_r["sb3"] = false;		
		} else {
		CngClass("sb3", "signal_b_but");
		lit_r["sb3"] = false;			
		lit["sb3"] = true;
		}
	}
	
function msg_signal_r() {
	if(!$('sb4')) {return; }
	if (lit_r["sb4"]) {return; }
	CngClass("sb4", "signal_r_but");
	lit_r["sb4"] = true;
	do_audible();
	}
	
function msg_signal_r_off() {
	if(!$('sb4')) {return; }
	if (!lit_r["sb4"]) {return; }
	if(!lit["sb4"]) {
		CngClass("sb4", "screen_but_plain");
		lit_r["sb4"] = false;		
		} else {
		CngClass("sb4", "signal_b_but");
		lit_r["sb4"] = false;			
		lit["sb4"] = true;
		}
	}
	
function get_latest_messages() {
	var randomnumber=Math.floor(Math.random()*99999999);
	user_id = parseInt("<?php print $the_user;?>");
	var url ='./ajax/get_latest_messages.php?responder_id=' + user_id + '&version=' + randomnumber;	
	sendRequest (url,get_latest_messages_cb, "");	
	}

function get_latest_messages_cb(req) {
	try {
		var the_msg_arr=JSON.decode(req.responseText);
		}
	catch (e) {
		return;
		}

	var msgtemp = parseInt(the_msg_arr[0]);				// new message?
	if (msgtemp > new_msg) {
		new_msg = msgtemp;
		msg_signal_r();								// light the msg button
		} else if (msgtemp < new_msg) {
		new_msg = msgtemp;
		msg_signal_r_off();
		}	
	}			// end function get_latest_messages_cb()	

function do_sub(the_status,the_button) {				// form submitted
	var params = "frm_id="+the_assigns_id;
	params += "&frm_tick="+tick_id;
	params += "&frm_unit="+responder_id;
	params += "&frm_vals="+ the_status;
	sendRequest ('./ajax/update_assigns.php',handleSubmit, params);			// does the work
	function handleSubmit(req) {
		var theResponse=JSON.decode(req.responseText);
		if($(the_button)) {
			$(the_button).innerHTML += "<BR />" + theResponse[0]; 
			$(the_button).style.backgroundColor = "#66FF00"; 
			$(the_button).style.color = "#707070";	
			}
		}
	get_ticket(tick_id);
	}			// end function do_sub()
	
function start_miles() {
	var value = prompt("Enter your start miles", "");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/update_mileage.php?type=start_miles&value=' + value + '&assigns_id=' + the_assigns_id + '&version=' + randomnumber;
	sendRequest (url, st_miles_cb, "");
	function st_miles_cb(req) {
		var theResponse=JSON.decode(req.responseText);	
		if(theResponse == 100) {
			$('mileage_start_but').innerHTML = "Start Miles<BR />" + value; 
			$('mileage_start_but').style.backgroundColor = "#66FF00"; 
			$('mileage_start_but').style.color = "#707070";			
			}
		}
	}
	
function end_miles() {
	var value = prompt("Enter your end miles", "");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/update_mileage.php?type=end_miles&value=' + value + '&assigns_id=' + the_assigns_id + '&version=' + randomnumber;
	sendRequest (url, end_miles_cb, "");
	function end_miles_cb(req) {
		var theResponse=JSON.decode(req.responseText);	
		if(theResponse == 100) {
			$('mileage_end_but').innerHTML = "End Miles<BR />" + value; 
			$('mileage_end_but').style.backgroundColor = "#66FF00"; 
			$('mileage_end_but').style.color = "#707070";
			}
		}	
	}
	
function os_miles() {
	var value = prompt("Enter your on scene miles", "");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/update_mileage.php?type=on_scene_miles&value=' + value + '&assigns_id=' + the_assigns_id + '&version=' + randomnumber;
	sendRequest (url, os_miles_cb, "");
	function os_miles_cb(req) {
		var theResponse=JSON.decode(req.responseText);	
		if(theResponse == 100) {
			$('mileage_os_but').innerHTML = "On Scene Miles<BR />" + value; 
			$('mileage_os_but').style.backgroundColor = "#66FF00"; 
			$('mileage_os_but').style.color = "#707070";			
			}	
		}	
	}
	
function notes() {
	user_id = parseInt("<?php print $the_user;?>");
	var value = prompt("Enter call notes", "");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/update_notes.php?notes=' + value + '&user_id=' + user_id + '&assigns_id=' + the_assigns_id + '&ticket_id=' + tick_id + '&version=' + randomnumber;
	sendRequest (url, notes_cb, "");
	function notes_cb(req) {
		var theResponse=JSON.decode(req.responseText);	
		if(theResponse == 100) {
			$('notes_but').style.backgroundColor = "#66FF00"; 
			$('notes_but').style.color = "#707070";			
			}
		}
	setTimeout(function() { get_ticket(tick_id); },1000);		
	}

function get_messages(ticket_id) {
	user_id = parseInt("<?php print $the_user;?>");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/messagelist.php?responder_id=' + user_id + '&ticket_id=' + ticket_id + '&version=' + randomnumber;
	sendRequest (url, main_mess_cb, "");
	function main_mess_cb(req) {
		the_messages=req.responseText;
		$('message_list').innerHTML = "Loading Messages............";
		setTimeout(function() {$('message_list').innerHTML = the_messages;},1000);
		}
	}	
	
function tkt_messages(ticket_id) {
	var close_button = '<span id="close_directions" class="plain" style="width: auto; display: block; z-index: 10; float: right; width: 40px;"';
	close_button += 'onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "close_tkt_messages();">';
	close_button += '<IMG SRC = "./images/close.png" ALT="Close Messages" BORDER=0 STYLE = "vertical-align: middle;"></span>';	
	$('ticket_detail').style.display = 'none';
	$('tkt_message_list').style.display = 'block';
	user_id = parseInt("<?php print $the_user;?>");
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/messagelist2.php?ticket_id=' + ticket_id + '&version=' + randomnumber;
	sendRequest (url, main_mess_cb, "");
	function main_mess_cb(req) {
		the_messages=close_button;
		the_messages+=req.responseText;		
		$('tkt_message_list').innerHTML = "Loading Messages............";
		setTimeout(function() {$('tkt_message_list').innerHTML = the_messages;},1000);
		}
	}
	
function update_position() {
	if((responder_id) && (the_lat) && (the_lng)) {
		randomnumber=Math.floor(Math.random()*99999999);
		url ='./ajax/update_position.php?responder=' + responder_id + '&lat=' + the_lat + '&lng=' + the_lng + '&altitude=' + theAltitude + '&heading=' + theHeading + '&speed=' + theSpeed + '&version=' + randomnumber;
		sendRequest (url, pos_cb, "");
		function pos_cb(req) {
			var success=JSON.decode(req.responseText);
			}
		}
	}

function get_alerts() {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/alertlist.php?lat=' + the_lat + '&lng=' + the_lng + '&unit=M&version=' + randomnumber;
	sendRequest (url, alerts_cb, "");
	function alerts_cb(req) {
		the_alerts=req.responseText;
		$('alert_list').innerHTML = "Loading Alerts............";
		setTimeout(function() {$('alert_list').innerHTML = the_alerts;},1000);
		}
	}	

function get_tickets(user_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/ticket_list.php?user_id=' + user_id + '&version=' + randomnumber;
	sendRequest (url, tickets_cb, "");
	function tickets_cb(req) {
		var the_tickets=req.responseText;
		$('ticket_list').innerHTML = "Loading Ticket List............";
		setTimeout(function() {$('ticket_list').innerHTML = the_tickets;},1000);
		}
	}	
	
function get_ticket_markers(user_id) {
	if(user_id == 0) return;
	randomnumber=Math.floor(Math.random()*99999999);
	var tkticonUrl;	
	var tkticon;
	url ='./ajax/ticket_markers.php?user_id=' + user_id + '&version=' + randomnumber;
	sendRequest (url, ticketMarkers_cb, "");
	function ticketMarkers_cb(req) {
		var the_tickets=JSON.decode(req.responseText);
		if(the_tickets[0] == 0) {return true;}
		for(var key in the_tickets) {
			var tkt_id = the_tickets[key][0];
			var tkt_scope = the_tickets[key][1];
			var tkt_lat = the_tickets[key][2];
			var tkt_lng = the_tickets[key][3];
			var tkt_desc = the_tickets[key][4];
			var tkt_opened = the_tickets[key][5];
			var info = "<TABLE style='width: 100%; border: 3px outset #707070; background-colour: #CECECE;'>";
			info += "<TR style='border: 1px solid #707070;'><TH colspan=99 class='header'>Incident</TH></TR>";
			info += "<TR style='border: 1px solid #707070;'><TD style='background-color: #707070; font-weight: bold; color: #FFFFFF'>Title</TD><TD style='font-weight: bold;'>" + tkt_scope + "</TD></TR>";
			info += "<TR style='border: 1px solid #707070;'><TD style='background-color: #707070; font-weight: bold; color: #FFFFFF'>Description</TD><TD>" + tkt_desc + "</TD></TR>";
			info += "<TR style='border: 1px solid #707070;'><TD style='background-color: #707070; font-weight: bold; color: #FFFFFF'>Incident Started</TD><TD>" + tkt_opened + "</TD></TR>";
			info += "</TABLE>";
			tkticonUrl = "./images/gen_icon.php?blank=3&text=" + tkt_id;	
			tkticon = new baseIcon({iconUrl: tkticonUrl});			
			createMarker(tkt_lat, tkt_lng, info, tkticon, tkt_scope);
			}
		}
	}

function get_ticket(ticket_id) {
	tick_id = ticket_id;
	randomnumber=Math.floor(Math.random()*99999999);
	user_id = parseInt("<?php print $the_user;?>");
	url ='./ajax/ticket_detail.php?ticket_id=' + ticket_id + '&user_id=' + user_id + '&version=' + randomnumber;
	sendRequest (url, ticket_cb, "");
	function ticket_cb(req) {
		var the_ticket=JSON.decode(req.responseText);
		the_assigns_id = the_ticket[0];
		$('ticket_list').style.display = "none";
		$('ticket_detail').style.display = "block";				
		$('ticket_detail_wrapper').style.display = "block";	
		var the_text_alert = "Dispatching Assigns ID " + the_assigns_id;
		$('disp_but').innerHTML = "Dispatched<BR />" + the_ticket[1]; 
		if(the_ticket[1] != "") { $('disp_but').setAttribute( "onClick", "" ); $('disp_but').setAttribute( "onMouseover", "" );	$('disp_but').setAttribute( "onMouseout", "" );}
		$('resp_but').innerHTML = "Responding<BR />" + the_ticket[2]; 
		if(the_ticket[2] != "") { $('resp_but').setAttribute( "onClick", "" ); $('resp_but').setAttribute( "onMouseover", "" );	$('resp_but').setAttribute( "onMouseout", "" );}
		$('os_but').innerHTML = "On Scene<BR />" + the_ticket[3]; 
		if(the_ticket[3] != "") { $('os_but').setAttribute( "onClick", "" ); $('os_but').setAttribute( "onMouseover", "" ); $('os_but').setAttribute( "onMouseout", "" );}
		$('fenr_but').innerHTML = "Fac enroute<BR />" + the_ticket[4]; 
		if(the_ticket[4] != "") { $('fenr_but').setAttribute( "onClick", "" ); $('fenr_but').setAttribute( "onMouseover", "" ); $('fenr_but').setAttribute( "onMouseout", "" );}
		$('farr_but').innerHTML = "Fac Arrived<BR />" + the_ticket[5]; 
		if(the_ticket[5] != "") { $('farr_but').setAttribute( "onClick", "" ); $('farr_but').setAttribute( "onMouseover", "" ); $('farr_but').setAttribute( "onMouseout", "" );}
		$('clear_but').innerHTML = "Clear<BR />" + the_ticket[6]; 
		if(the_ticket[6] != "") { $('clear_but').setAttribute( "onClick", "" ); $('clear_but').setAttribute( "onMouseover", "" ); $('clear_but').setAttribute( "onMouseout", "" );}
		if((the_ticket[7] != "") && (the_ticket[7] != 0)) { $('mileage_start_but').innerHTML = "Start Miles<BR />" + the_ticket[7];} 
		if((the_ticket[7] != "") && (the_ticket[7] != 0)) { $('mileage_start_but').setAttribute( "onClick", "" ); $('mileage_start_but').setAttribute( "onMouseover", "" ); $('mileage_start_but').setAttribute( "onMouseout", "" );}
		if((the_ticket[8] != "") && (the_ticket[8] != 0)) { $('mileage_end_but').innerHTML = "End Miles<BR />" + the_ticket[8]; }
		if((the_ticket[8] != "") && (the_ticket[8] != 0)) { $('mileage_end_but').setAttribute( "onClick", "" ); $('mileage_end_but').setAttribute( "onMouseover", "" ); $('mileage_end_but').setAttribute( "onMouseout", "" );}
		if((the_ticket[9] != "") && (the_ticket[9] != 0)) {$('mileage_os_but').innerHTML = "On Scene Miles<BR />" + the_ticket[9]; }
		if((the_ticket[9] != "") && (the_ticket[9] != 0)) { $('mileage_os_but').setAttribute( "onClick", "" ); $('mileage_os_but').setAttribute( "onMouseover", "" ); $('mileage_os_but').setAttribute( "onMouseout", "" );}	
		if(the_ticket[1] != "") { $('disp_but').style.backgroundColor = "#66FF00"; $('disp_but').style.color = "#707070"; }
		if(the_ticket[2] != "") { $('resp_but').style.backgroundColor = "#66FF00"; $('resp_but').style.color = "#707070"; }
		if(the_ticket[3] != "") { $('os_but').style.backgroundColor = "#66FF00"; $('os_but').style.color = "#707070"; }
		if(the_ticket[4] != "") { $('fenr_but').style.backgroundColor = "#66FF00"; $('fenr_but').style.color = "#707070"; }
		if(the_ticket[5] != "") { $('farr_but').style.backgroundColor = "#66FF00"; $('farr_but').style.color = "#707070"; }
		if(the_ticket[6] != "") { $('clear_but').style.backgroundColor = "#66FF00"; $('clear_but').style.color = "#707070"; }	
		if((the_ticket[7] != "") && (the_ticket[7] != 0)) { $('mileage_start_but').style.backgroundColor = "#66FF00"; $('mileage_start_but').style.color = "#707070"; }	
		if((the_ticket[8] != "") && (the_ticket[8] != 0)) { $('mileage_end_but').style.backgroundColor = "#66FF00"; $('mileage_end_but').style.color = "#707070"; }	
		if((the_ticket[9] != "") && (the_ticket[9] != 0)) { $('mileage_os_but').style.backgroundColor = "#66FF00"; $('mileage_os_but').style.color = "#707070"; }	
		if(the_ticket[12] != "") { $('notes_but').style.backgroundColor = "#66FF00"; $('notes_but').style.color = "#707070"; }				
		$('resp_but').setAttribute( "onClick", "javascript:do_sub('frm_responding','resp_but');" );
		$('os_but').setAttribute( "onClick", "javascript:do_sub('frm_on_scene','os_but');" );
		$('os_but').setAttribute( "onClick", "javascript:do_sub('frm_on_scene','os_but');" );
		if(the_ticket[10] == 0) {
			$('fenr_but').style.display = "none";
			$('farr_but').style.display = "none";
			} else {
			$('fenr_but').setAttribute( "onClick", "javascript:do_sub('frm_u2fenr','fenr_but');" );
			$('farr_but').setAttribute( "onClick", "javascript:do_sub('frm_u2farr','farr_but');" );
			}
		$('clear_but').setAttribute( "onClick", "javascript:do_sub('frm_clear','clear_but');" );		
		$('ticket_detail').innerHTML = "Loading Ticket Details............";
		setTimeout(function() {$('ticket_detail').innerHTML = the_ticket[11]; $('menu_but2').style.display = 'inline-block'; },1000);
		}
	}	
	
function get_message(message_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/message_detail.php?message_id=' + message_id + '&version=' + randomnumber;
	sendRequest (url, message_cb, "");
	function message_cb(req) {
		var the_message=JSON.decode(req.responseText);
		var the_return_add = the_message[0];
		var tickets_address = "<?php print get_variable('email_reply_to');?>";
		$('message_list').style.display = "none";
		$('message_detail').style.display = "block";				
		$('message_detail').innerHTML = "Loading Message Details............";
		tick_id = the_message[1];
		setTimeout(function() {$('message_detail').innerHTML = the_message[4];},1000);
		setTimeout(function() {if(the_return_add != tickets_address) { $('reply_but').style.display = "inline"; msg_subject = the_message[2]; msg_text = the_message[3];}},1000);	
		update_msgread(message_id);	
		}
	}	
	
function update_msgread(message_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	user_id = parseInt("<?php print $the_user;?>");
	url ='./ajax/update_message_read.php?responder_id=' +  user_id + '&uid=' + message_id + '&version=' + randomnumber;
	sendRequest (url, message_cb, "");
	function message_cb(req) {
		var the_success=JSON.decode(req.responseText);
		}
	}	
	
function chat_invite_off() {
	randomnumber=Math.floor(Math.random()*99999999);
	user_id = parseInt("<?php print $the_user;?>");
	url ='./ajax/chat_invite_del.php?responder_id=' +  chat_user + '&version=' + randomnumber;
	sendRequest (url, message_cb, "");
	function message_cb(req) {
		var the_success=JSON.decode(req.responseText);
		chat_signal_r_off();	
		}
	}	
	
function get_tkt_message(message_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/message_detail2.php?message_id=' + message_id + '&version=' + randomnumber;
	sendRequest (url, message_cb, "");
	function message_cb(req) {
		var the_message=JSON.decode(req.responseText);
		var the_return_add = the_message[0];
		var tickets_address = "<?php print get_variable('email_reply_to');?>";
		$('tkt_message_list').style.display = "none";
		$('tkt_message_detail').style.display = "block";				
		$('tkt_message_detail').innerHTML = "Loading Message Details............";
		tick_id = the_message[1];
		setTimeout(function() {$('tkt_message_detail').innerHTML = the_message[4];},1000);
		setTimeout(function() {if(the_return_add != tickets_address) { $('tkt_reply_but').style.display = "inline"; msg_subject = the_message[2]; msg_text = the_message[3];}},1000);		
		}
	}	

function do_reply(to_address) {
	var user_email = "<?php print $the_email;?>";
	$('message_detail').style.display = "none";
	$('message_reply').style.display = "block";
	document.reply_form.frm_to.value =	"Tickets";
	document.reply_form.frm_from.value = user_email;	
	document.reply_form.frm_subject.value = msg_subject;
	document.reply_form.frm_msg.value = msg_text;	
	}
	
function can_reply() {
	$('message_detail').style.display = "block";
	$('message_reply').style.display = "none";
	}

function get_alert(alert_id) {
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/alert_detail.php?alert_id=' + alert_id + '&version=' + randomnumber;
	sendRequest (url, ticket_cb, "");
	function ticket_cb(req) {
		var the_ticket=req.responseText;
		$('alert_list').style.display = "none";
		$('alert_detail').style.display = "block";				
		$('alert_detail').innerHTML = "Loading Alert Details............";
		setTimeout(function() {$('alert_detail').innerHTML = the_ticket;},1000);
		}
	}	

function close_alert_detail() {
	$('close_alert_detail').style.display = "none";
	$('alert_detail').style.display = "none";
	$('alert_list').style.display = "block";	
	}
	
function close_ticket_detail() {
	slideIn('buttons2', 'menu_but2');	
	$('menu_but2').style.display = 'none';
	$('ticket_detail').style.display = "none";
	$('ticket_detail_wrapper').style.display = "none";	
	$('ticket_list').style.display = "block";
	$('disp_but').innerHTML = "Dispatched"; 
	$('disp_but').style.backgroundColor = '#EFEFEF'; $('disp_but').style.color = '#000000';	$('disp_but').setAttribute( "onClick", "alert(\"Dispatch Button\");" ); $('disp_but').setAttribute( "onMouseover", "do_hover(this.id)" ); $('disp_but').setAttribute( "onMouseout", "do_plain(this.id)" );
	$('resp_but').innerHTML = "Responding"; $('resp_but').style.backgroundColor = '#EFEFEF'; $('resp_but').style.color = '#000000';	$('resp_but').setAttribute( "onClick", "do_sub(\"frm_responding\",\"resp_but\");" ); $('resp_but').setAttribute( "onMouseover", "do_hover(this.id)" ); $('resp_but').setAttribute( "onMouseout", "do_plain(this.id)" );
	$('os_but').innerHTML = "On Scene"; $('os_but').style.backgroundColor = '#EFEFEF'; $('os_but').style.color = '#000000'; $('os_but').setAttribute( "onClick", "do_sub(\"frm_on_scene\",\"os_but\");" ); $('os_but').setAttribute( "onMouseover", "do_hover(this.id)" ); $('os_but').setAttribute( "onMouseout", "do_plain(this.id)" );
	$('fenr_but').innerHTML = "Fac enroute"; $('fenr_but').style.backgroundColor = '#EFEFEF'; $('fenr_but').style.color = '#000000'; $('fenr_but').setAttribute( "onClick", "do_sub(\"frm_u2fenr\",\"fenr_but\");" ); $('fenr_but').setAttribute( "onMouseover", "do_hover(this.id)" );	$('fenr_but').setAttribute( "onMouseout", "do_plain(this.id)" );
	$('farr_but').innerHTML = "Fac Arrived"; $('farr_but').style.backgroundColor = '#EFEFEF'; $('farr_but').style.color = '#000000'; $('farr_but').setAttribute( "onClick", "do_sub(\"frm_u2farr\",\"farr_butt\");" ); $('farr_but').setAttribute( "onMouseover", "do_hover(this.id)" ); $('farr_but').setAttribute( "onMouseout", "do_plain(this.id)" );
	$('clear_but').innerHTML = "Clear"; $('clear_but').style.backgroundColor = '#EFEFEF'; $('clear_but').style.color = '#000000'; $('clear_but').setAttribute( "onClick", "do_sub(\"frm_clear\",\"clear_but\");" ); $('clear_but').setAttribute( "onMouseover", "do_hover(this.id)" ); $('clear_but').setAttribute( "onMouseout", "do_plain(this.id)" );
	$('mileage_start_but').innerHTML = "Start Miles"; $('mileage_start_but').style.backgroundColor = '#EFEFEF'; $('mileage_start_but').style.color = '#000000'; $('mileage_start_but').setAttribute( "onClick", "start_miles();" ); $('mileage_start_but').setAttribute( "onMouseover", "do_hover(this.id)" ); $('mileage_start_but').setAttribute( "onMouseout", "do_plain(this.id)" );
	$('mileage_end_but').innerHTML = "End Miles"; $('mileage_end_but').style.backgroundColor = '#EFEFEF'; $('mileage_end_but').style.color = '#000000'; $('mileage_end_but').setAttribute( "onClick", "end_miles();" ); $('mileage_end_but').setAttribute( "onMouseover", "do_hover(this.id)" ); $('mileage_end_but').setAttribute( "onMouseout", "do_plain(this.id)" );
	$('mileage_os_but').innerHTML = "On Scene Miles"; $('mileage_os_but').style.backgroundColor = '#EFEFEF'; $('mileage_os_but').style.color = '#000000'; $('mileage_os_but').setAttribute( "onClick", "os_miles();" ); $('mileage_os_but').setAttribute( "onMouseover", "do_hover(this.id)" ); $('mileage_os_but').setAttribute( "onMouseout", "do_plain(this.id)" );	
	$('disp_but').className  = "plain"; 
	$('resp_but').className  = "plain"; 
	$('os_but').className  = "plain"; 
	$('fenr_but').className  = "plain"; 
	$('farr_but').className  = "plain";
	$('clear_but').className  = "plain"; 
	}

function close_message_detail() {
	$('message_detail').style.display = "none";
	$('message_list').style.display = "block";	
	get_messages(0);
	}
	
function close_tkt_message_detail() {
	$('tkt_message_detail').style.display = "none";
	$('tkt_message_list').style.display = "block";	
	get_messages(tick_id);
	}
	
function close_tkt_messages() {
	$('ticket_detail').style.display = "block";
	$('tkt_message_list').style.display = "none";	
	}

function slideIt(theDiv, theButton) {
	var slidingDiv = $(theDiv);
	var stopPosition = 0;
	if (parseInt(slidingDiv.style.left) < stopPosition ) {
		slidingDiv.style.left = parseInt(slidingDiv.style.left) + 4 + "px";
		setTimeout(function(){slideIt(theDiv, theButton)}, .5);
		
		}
	$(theButton).setAttribute( "onClick", 'javascript: slideIn("' + theDiv + '", this.id);' );	
	$(theButton).innerHTML = "Hide Menu";	
	}

function slideIn(theDiv, theButton) {
	var slidingDiv = $(theDiv);
	var stopPosition = -150;
	if (parseInt(slidingDiv.style.left) > stopPosition ) { 
		slidingDiv.style.left = parseInt(slidingDiv.style.left) - 4 + "px";
		setTimeout(function(){slideIn(theDiv, theButton)}, .5);		
		}
	$(theButton).setAttribute( "onClick", 'javascript: slideIt("' + theDiv + '", this.id);' );	
	$(theButton).innerHTML = "Show Menu";		
	}
	
function get_conditions() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/infolist2.php?version=" + randomnumber;
	sendRequest (url, info_cb, "");
	function info_cb(req) {
		var cond_response=JSON.decode(req.responseText);
		if(cond_response[0] == 0) {return true;}
		for(var key in cond_response) {
			if(cond_response[key][0] == 100) {	
				alert("error");
				} else {
				var the_id = cond_response[key][0];
				var the_title = cond_response[key][1];	
				var the_notes = cond_response[key][2];			
				var the_description = cond_response[key][3];
				var the_type = cond_response[key][4];
				var the_user = cond_response[key][5];	
				var theLat = cond_response[key][6];	
				var theLng = cond_response[key][7];	
				var theIcon = cond_response[key][8];
				var theAddress = cond_response[key][9];	
				var theInfo = the_description + "<BR />" + theAddress + "<BR />";
				var title = the_title + "\r\n" + theAddress;
				createCondMarker(theLat, theLng, theInfo, theIcon, title);				
				}
			}
		}
	}

function sub_data(title,address,lat,lng,type) {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/submit_entry.php?id=0&version=" + randomnumber + "&type=" + type + "&address=" + address + "&title=" + title + "&lat=" + lat + "&lng=" + lng;
	sendRequest (url, sub_cb, "");
	function sub_cb(req) {
		var response=JSON.decode(req.responseText);
		if(response[0] == 100) {
			msg = "Report Submitted - Thank You";
			} else {
			msg = "There was an error submitting the data, please try again";
			}
		show_msg(msg);	
		}
	$('condition_selector').selectedIndex = 0;
	}
	
function sendRequest(url,callback,postData) {
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
		try { xmlhttp = XMLHttpFactories[i](); }
		catch (e) { continue; }
		break;
		}
	return xmlhttp;
	}

function syncAjax(strURL) {	//	10/23/12
	if (window.XMLHttpRequest) {						 
		AJAX=new XMLHttpRequest();						 
		} 
	else {																 
		AJAX=new ActiveXObject("Microsoft.XMLHTTP");
		}
	if (AJAX) {
		AJAX.open("GET", strURL, false);														 
		AJAX.send(null);							// form name
		return AJAX.responseText;																				 
		} 
	else {
		return false;
		}																						 
	}		// end function sync Ajax()
	
function contains(array, item) {
	for (var i = 0, I = array.length; i < I; ++i) {
		if (array[i] == item) return true;
		}
	return false;
	}

function createMarker(lat, lon, info, icon, title){
	L.marker([lat, lon], {icon: icon}).addTo(map)
		.bindPopup(info);
	}
	
function createCondMarker(lat, lon, info, theIcon, title){
	var condMarker	= new condIcon({iconUrl: './roadinfo_icons/' + theIcon});
	L.marker([lat, lon], {icon: condMarker}).addTo(map)
		.bindPopup(info);
	}
	
function closeIW() {
//	$('adverts').style.display = 'inline-block';
	}
	
function alert_coords() {
	var thecoords = convertDMS(the_lat,the_lng);
	var the_text = "Decimal: " + the_lat + "   " + the_lng + "\r\nDMS: " + thecoords + "\r\nGRID: " + do_ngs(the_lat,the_lng);
	alert(the_text);
	slideIn('buttons', 'menu_but');		
	}

function alert_location() {
	do_geolocate(latLng, the_lat, the_lng);
	setTimeout(function() {alert(form_add); slideIn('buttons', 'menu_but');	},1000);
	}	
	
function the_status(status, title) {
	do_geolocate(latLng, the_lat, the_lng);
	if (confirm("Are you sure you want submit this " + title + " report?")) { 
		do_geolocate(latLng, the_lat, the_lng);
		setTimeout(function() {sub_data(title,form_add,the_lat,the_lng,status); get_conditions(); slideIn('buttons', 'menu_but'); alert("Conditions Report Submitted"); },2000);
		}
	}
	
function refresh_screen() {
	initialise();
	get_conditions();		
	slideIn('buttons', 'menu_but');	
	}
	
function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}

function do_sm_hover (the_id) {
	CngClass(the_id, 'sm_hover');
	return true;
	}

function do_sm_plain (the_id) {
	CngClass(the_id, 'sm_plain');
	return true;
	}	

function do_sb_hover (the_id) {
	if (the_id == current_butt_id) {return true;}
	if (lit[the_id]) {return true;}
	if (lit_r[the_id]) {
		CngClass(the_id, 'screen_but_hover_r');	
		} else {
		CngClass(the_id, 'screen_but_hover');
		}
	return true;
	}
	
function do_sb_plain (the_id) {
	if (the_id == current_butt_id) {return true;}
	if (lit[the_id] ) {return true;}
	if (lit_r[the_id] ) {
		CngClass(the_id, 'signal_r_but');	
		} else {
		CngClass(the_id, 'screen_but_plain');
		}
	return true;
	}

function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}
	
function $() {
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
	
function show_msg (msg) {
	$('msg_span').style.display = "block";
	$('msg_span').innerHTML = msg;			
	setTimeout("$('msg_span').innerHTML =''", 6000);	// show for 3 seconds
	$('msg_span').style.display = "none";	
	}

function close_directions() {
	$('ticket_detail').style.display = 'block';		
	$('directions_wrapper').style.display = 'none';
	$('menu_but2').style.display = 'inline-block';	
	map.removeLayer(route);
	}
	
function get_geo() {	//	Start up the geo location
	if(map) {
		map.locate({setView: true, watch: false, maxZoom: 16, enableHighAccuracy:true});	//	get location, don't auto watch, set max zoom, zoom and center on location, enable GPS
		update_position();
		}
	get_conditions();	//	get condition alerts first time
	if(user_id != 0) {	//	if not logged in, only get position updates - no messages, chat or tickets
		get_latest_messages();	//	get messages first time
		get_ticket_markers(parseInt("<?php print $the_user;?>"));	// get ticket markers first time
		get_latest_ids();	//	Get chat and ticket assignment updates first time	
		update_position();
		}
	do_loop();
	}
	
function do_loop() {
	primary_timer = window.setInterval('primary_repeats()', 2000);	// the shortest timer, get new location data
	if(user_id == 0) return;	//	if not logged in, only get position updates - no messages, chat or tickets
	var the_time = track_timer * 60000;	//	the time is number of minutes set in mobile tracking system setting default 2 minutes
	secondary_timer = window.setInterval('secondary_repeats()', 30000);	// 30 second timer - chat, messages and markers
	tertiary_timer = window.setInterval('tertiary_repeats()', the_time);	// The long timer - send position updates to server
	}
	
function primary_repeats() {
	next_update();
	get_conditions();	//	get condition alerts
	}
	
function secondary_repeats() {
	get_latest_messages();	//	get messages
	get_ticket_markers(parseInt("<?php print $the_user;?>"));	// get new ticket markers
	get_latest_ids();	//	Get chat and ticket assignment updates
	}
	
function tertiary_repeats() {
	update_position();
	}
	
function stop_loop() {
	window.clearInterval(primary_timer);	//	stop location finding
	window.clearInterval(secondary_timer);	//	stop second timer - the shorter timer
	window.clearInterval(tertiary_timer);	//	stop the longer timer - position updates to server
	}
	
function next_update() {
	map.locate({setView: false});	//	find location, don't re-center and zoom map.
	}

function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
													// NOT correspond with what browsers actually do...
	var SAFECHARS = "0123456789" +					// Numeric
					"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// guess
					"abcdefghijklmnopqrstuvwxyz" +	// guess again
					"-_.!~*'()";					// RFC2396 Mark characters
	var HEX = "0123456789ABCDEF";

	var encoded = "";
	for (var i = 0; i < plaintext.length; i++ ) {
		var ch = plaintext.charAt(i);
		if (ch == " ") {
			encoded += "+";				// x-www-urlencoded, rather than %20
		} else if (SAFECHARS.indexOf(ch) != -1) {
			encoded += ch;
		} else {
			var charCode = ch.charCodeAt(0);
			if (charCode > 255) {
				alert( "Unicode Character '"
						+ ch
						+ "' cannot be encoded using standard URL encoding.\n" +
						  "(URL encoding only supports 8-bit characters.)\n" +
						  "A space (+) will be substituted." );
				encoded += "+";
			} else {
				encoded += "%";
				encoded += HEX.charAt((charCode >> 4) & 0xF);
				encoded += HEX.charAt(charCode & 0xF);
				}
			}
		} 			// end for(...)
	return encoded;
	};			// end function

function URLDecode(encoded ){   					// Replace + with ' '
   var HEXCHARS = "0123456789ABCDEFabcdef";  		// Replace %xx with equivalent character
   var plaintext = "";   							// Place [ERROR] in output if %xx is invalid.
   var i = 0;
   while (i < encoded.length) {
	   var ch = encoded.charAt(i);
	   if (ch == "+") {
		   plaintext += " ";
		   i++;
	   } else if (ch == "%") {
			if (i < (encoded.length-2)
					&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1
					&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
				plaintext += unescape( encoded.substr(i,3) );
				i += 3;
			} else {
				alert( '-- invalid escape combination near ...' + encoded.substr(i) );
				plaintext += "%[ERROR]";
				i++;
			}
		} else {
			plaintext += ch;
			i++;
			}
	} 				// end  while (...)
	return plaintext;
	};				// end function URLDecode()
	
function send_message() {
	var theForm = document.forms['reply_form'];
	var theTo = theForm.elements["frm_to"].value;
	var theFrom = theForm.elements["frm_from"].value;
	var theSubject = urlencode(theForm.elements["frm_subject"].value);
	var theMessage = urlencode(theForm.elements["frm_msg"].value);
	var theTicket = tick_id;
	randomnumber=Math.floor(Math.random()*99999999);
	url ='./ajax/send_email.php?resp_id=' + responder_id + '&ticket_id=' + theTicket + '&from_address=' + theFrom + '&fromname=' + responder_name + '&subject=' + theSubject + '&message=' + theMessage + '&version=' + randomnumber;
	sendRequest (url, send_msg_cb, "");
	function send_msg_cb(req) {
		var the_response=JSON.decode(req.responseText);
		if(the_response[0] == 100) {
			$('message_alert').innerHTML = "Reply Sent";
			can_reply();
			} else {
			$('message_alert').innerHTML = "Reply FAILED";
			can_reply();
			}
		}	
	}

function screen1() {
	if(!$('sb1')) {return; }
	if (lit["sb1"]) {return; }									// already lit
	if(do_chat) {do_chat = false; chat_stop();}		
	if((!lit_r['sb1']) && ($('sb1'))) {
		CngClass("sb1", "signal_b_but");
		} else if ((lit_r['sb1']) && ($('sb1'))) {
		CngClass("sb1", "signal_r_but");
		}
	lit['sb1'] = true;
	if((!lit_r['sb2']) && ($('sb2'))) {CngClass("sb2", "screen_but_plain");}
	if((!lit_r['sb3']) && ($('sb3'))) {CngClass("sb3", "screen_but_plain");}
	if((!lit_r['sb4']) && ($('sb4'))) {CngClass("sb4", "screen_but_plain");}
	if((!lit_r['sb5']) && ($('sb5'))) {CngClass("sb5", "screen_but_plain");}	
	$("screen1").style.display="block"; 
	$("screen2").style.display="none"; 
	$("screen3").style.display="none"; 
	$("screen4").style.display="none"; 
	$("screen5").style.display="none";
	current_butt_id = "sb1";	
	lit['sb2'] = lit['sb3'] = lit['sb4'] = lit['sb5'] = false;		
	}
	
function screen2() {
	if (lit["sb2"]) {return; }									// already lit	
	if(do_chat) {do_chat = false; chat_stop();}		
	get_alerts();	
	if((!lit_r['sb1']) && ($('sb1'))) {CngClass("sb1", "screen_but_plain");}
	if((!lit_r['sb2']) && ($('sb2'))) {
		CngClass("sb2", "signal_b_but");
		} else if ((lit_r['sb2']) && ($('sb2'))) {
		CngClass("sb2", "signal_r_but");
		}
	lit['sb2'] = true;	
	if((!lit_r['sb3']) && ($('sb3'))) {CngClass("sb3", "screen_but_plain");}
	if((!lit_r['sb4']) && ($('sb4'))) {CngClass("sb4", "screen_but_plain");}
	if((!lit_r['sb5']) && ($('sb5'))) {CngClass("sb5", "screen_but_plain");}	
	$("screen1").style.display="none"; 
	$("screen2").style.display="block"; 
	$("screen3").style.display="none"; 
	$("screen4").style.display="none"; 
	$("screen5").style.display="none";
	current_butt_id = "sb2";		
	lit['sb1'] = lit['sb3'] = lit['sb4'] = lit['sb5'] = false;		
	}

function screen3() {
	if (lit["sb3"]) {return; }									// already lit	
	if(do_chat) {do_chat = false; chat_stop(); }	
	get_tickets(parseInt("<?php print $the_user;?>"));
	if((!lit_r['sb1']) && ($('sb1'))) {CngClass("sb1", "screen_but_plain");}
	if((!lit_r['sb2']) && ($('sb2'))) {CngClass("sb2", "screen_but_plain");}
	if((!lit_r['sb3']) && ($('sb3'))) {
		CngClass("sb3", "signal_b_but");
		} else if ((lit_r['sb3']) && ($('sb3'))) {
		CngClass("sb3", "signal_r_but");
		inc_signal_r_off();
		}
	lit['sb3'] = true;	
	if((!lit_r['sb4']) && ($('sb4'))) {CngClass("sb4", "screen_but_plain");}
	if((!lit_r['sb5']) && ($('sb5'))) {CngClass("sb5", "screen_but_plain");}	
	$("screen1").style.display="none"; 
	$("screen2").style.display="none"; 
	$("screen3").style.display="block"; 
	$("screen4").style.display="none"; 
	$("screen5").style.display="none";
	current_butt_id = "sb3";	
	lit['sb1'] = lit['sb2'] = lit['sb4'] = lit['sb5'] = false;	
	}
	
function screen4() {
	if (lit["sb4"]) {return; }									// already lit
	if(do_chat) {do_chat = false; chat_stop();}		
	get_messages(0);	
	if((!lit_r['sb1']) && ($('sb1'))) {CngClass("sb1", "screen_but_plain");}
	if((!lit_r['sb2']) && ($('sb2'))) {CngClass("sb2", "screen_but_plain");}
	if((!lit_r['sb3']) && ($('sb3'))) {CngClass("sb3", "screen_but_plain");}
	if((!lit_r['sb4']) && ($('sb4'))) {
		CngClass("sb4", "signal_b_but");
		} else if ((lit_r['sb4']) && ($('sb4'))) {
		CngClass("sb4", "signal_r_but");
		}
	lit['sb4'] = true;		
	if((!lit_r['sb5']) && ($('sb5'))) {CngClass("sb5", "screen_but_plain");}
	$("screen1").style.display="none"; 
	$("screen2").style.display="none"; 
	$("screen3").style.display="none"; 
	$("screen4").style.display="block"; 
	$("screen5").style.display="none";
	current_butt_id = "sb4";	
	lit['sb1'] = lit['sb2'] = lit['sb3'] = lit['sb5'] = false;
	}

function screen5() {
	if (lit["sb5"]) {return; }									// already lit
	if(!do_chat) {do_chat = true; chat_start(); }
	if((!lit_r['sb1']) && ($('sb1'))) {CngClass("sb1", "screen_but_plain");}
	if((!lit_r['sb2']) && ($('sb2'))) {CngClass("sb2", "screen_but_plain");}
	if((!lit_r['sb3']) && ($('sb3'))) {CngClass("sb3", "screen_but_plain");}
	if((!lit_r['sb4']) && ($('sb4'))) {CngClass("sb4", "screen_but_plain");}
	if((!lit_r['sb5']) && ($('sb5'))) {
		CngClass("sb5", "signal_b_but");
		} else if ((lit_r['sb5']) && ($('sb5'))) {
		CngClass("sb5", "signal_r_but");
		chat_invite_off();	
		}
	lit['sb5'] = true;
	$("screen1").style.display="none"; 
	$("screen2").style.display="none"; 
	$("screen3").style.display="none"; 
	$("screen4").style.display="none"; 
	$("screen5").style.display="block";
	current_butt_id = "sb5";	
	lit['sb1'] = lit['sb2'] = lit['sb3'] = lit['sb4'] = false;	
	}
	
function showhideTitle() {
	if($('app_title').style.display == "none") {
		$('title_button').style.display = "none";
		$('app_title').style.display = "inline-block";
		} else {
		$('app_title').style.display = "none";
		$('title_button').style.display = "inline-block";
		}
	}
	
function toggleTracks() {
	if(!theTrack) return;
	if(tracksOn) {
		map.removeLayer(theTrack);
		$('toggle_tracks_but').style.display = "none";		
		$('toggle_tracks_off_but').style.display = "inline-block";
		tracksOn = false;
		} else {
		map.addLayer(theTrack);
		$('toggle_tracks_but').style.display = "inline-block";		
		$('toggle_tracks_off_but').style.display = "none";		
		tracksOn = true;
		}
	}
	
function map_controls_onoff() {
	if($('map_controls').style.display == "none") {
		$('map_controls').style.display = "inline-block";
		} else {
		$('map_controls').style.display = "none";
		}
	}

// Chat functions
var me = "<?php print $_SESSION['user'];?>";
var colors = new Array();
colors[0] = '#DEE3E7';
colors[1] = '#EFEFEF';
colors[2] = '#FFFFFF';
var the_to = false;				// timeout object
var first = true;
window.onBlur = clearTimeout (the_to);

String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};
	
var last_msg_id=0;									// initial value at page load

function rd_chat_msg() {							// read chat messages via ajax xfer - 5/29/10
	var our_max = (first)? 5 : <?php print $list_length ;?>;		// startup limiter
	var params = "last_id=" + last_msg_id + "&max_ct=" + our_max ;
	first = false;													// standard limiter
	sendRequest ('./ajax/chat_rd.php',handleResult, params);	// 
	}

function handleResult(req) {									// the called-back phone lookup function
	var payload = req.responseText;		
	if (payload.substring(0,1)=="-") {
		alert ("chat failed -  <?php print __LINE__;?>");
		return false;
		}
	else {
		var person = document.getElementById("person");
		var lines = payload.split(0xFF, 99) 											// lines FF-delimited
		for (i=0;i<lines.length; i++) {
			var theLine = lines[i].split("\t", 6);										// tab-delimited
			if (theLine.length>1){
				var tr = person.insertRow(-1);
				var the_color = (theLine[0]==me)? colors[2]: colors[theLine[3] % 2];	// highlight if this user
				tr.style.backgroundColor = the_color;
				tr.insertCell(-1).appendChild(document.createTextNode(theLine[1]));		// time
				tr.insertCell(-1).appendChild(document.createTextNode(theLine[0]));		// user
				tr.insertCell(-1).appendChild(document.createTextNode(theLine[2]));		// message
				last_msg_id = (theLine[3]>last_msg_id)? theLine[3]:last_msg_id ;
				location.href = "#bottom";				// make input line visible
				}
			}			// end for (i=... )
		}			// end if/else (payload.substring(... )
	trim_list(<?php print $list_length; ?>);		// delete rows
	
	ctr = $('person').rows.length;		// now clear out local-inserted rows
	for (i=ctr-1; i>=0;i--) {
		while (($('person').rows[i]) && ($('person').rows[i].cells[0].innerHTML == "")) {
			$('person').deleteRow(i);
			}
		}
	}		// end function handleResult()



function wr_invite(target) {							// write chat message via ajax xfer
	var url = "./ajax/chat_invite.php?frm_to=" + target.trim() + "&frm_user=" + document.chat_form.frm_user.value;		// user id or broadcast
	var payload = syncAjax(url);						// send lookup url
	if (payload.substring(0,1)=="-") {					// stringObject.substring(start,stop)
		alert ("chat failed -  <?php print __LINE__;?>");
		set_to();										// set timeout again
		return false;
		}
	else {
		return;
		}				// end if/else (payload.substring(... )
	}		// end function wr invite msg()
	

function wr_chat_msg(the_Form) {							// write chat message via ajax xfer
	if (the_Form.frm_message.value.trim()=="") {return;}

	var person = document.getElementById("person");		// into table

	var new_tr = person.insertRow(-1);
	new_tr.style.backgroundColor = colors[2];
	var timecell = new_tr.insertCell(-1).appendChild(document.createTextNode(""));		// empty time
	var userCell = new_tr.insertCell(-1);
	userCell.style.backgroundColor = "#707070";
	userCell.style.color = "#FFFFFF";	
	userCell.style.width = "20%";
	userCell.appendChild(document.createTextNode("<?php print $_SESSION['user'];?> said \n\r"));		// user
	var messageCell = new_tr.insertCell(-1);
	messageCell.style.backgroundColor = "#FFFFFF";
	messageCell.style.color = "#000000";		
	messageCell.style.width = "80%";		
	messageCell.appendChild(document.createTextNode(the_Form.frm_message.value.trim()));		// message
	var newline = new_tr.insertCell(-1).appendChild(document.createTextNode("\n\r"));		// message		

	clear_to();
	var querystr = "?frm_message=" + URLEncode(the_Form.frm_message.value.trim());
	querystr += "&frm_room=" + URLEncode(the_Form.frm_room.value.trim());
	querystr += "&frm_user=" + URLEncode(the_Form.frm_user.value.trim());
	querystr += "&frm_from=" + URLEncode(the_Form.frm_from.value.trim());

	var url = "./ajax/chat_wr.php" + querystr;					// phone no. or addr string
	var payload = syncAjax(url);						// send lookup url
	if (payload.substring(0,1)=="-") {					// stringObject.substring(start,stop)
		alert ("wr_chat msg failed -  <?php print __LINE__;?>");
		set_to();										// set timeout again
		return false;
		}
	else {
		set_to();										// set timeout again
		the_Form.frm_message.value="";
//			the_Form.frm_message.focus();
		do_focus ()
		}				// end if/else (payload.substring(... )
	}		// end function wr_chat_ msg()

function show_hide(the_id) {						// display then hide given id
	$(the_id).style.display='inline';
	setTimeout("$('sent_msg').style.display='none';", 3000);
	}

function do_focus () {	
	document.chat_form.frm_message.focus();
	}	

function do_enter(e) {										// enter key submits form
	var keynum;
	var keychar;
	if(window.event)	{keynum = e.keyCode;	} 			// IE
	else if(e.which)	{keynum = e.which;	}				// Mozilla/Opera
	if (keynum==13) {										// allow enter key
		wr_chat_msg(document.forms['chat_form']) ;					// submit to server-side script
		do_focus ()
		}
	else {
		keychar = String.fromCharCode(keynum);
		return keychar;
			}
	} //	end function do_enter(e)

function announce() {										//end announcement
	wr_chat_msg(document.chat_form);
	}

function set_to() {										// set timeout
	if (!the_to) {the_to=setTimeout('getMessages(false)', <?php print $cycle;?>)}
	}
	
function clear_to() {
	clearTimeout (the_to);
	the_to = false;
	}
	
function getMessages(ignore){
	clear_to();
	rd_chat_msg();
	set_to();												// set timeout again
	do_focus ();
	get_chatusers();
	}
	
function get_chatusers() {
	$('whos_chatting').innerHTML = "Checking ......";
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/chat_wl.php?version=" + randomnumber;
	sendRequest (url, cu_cb, "");
	function cu_cb(req) {
		var chatusers=JSON.decode(req.responseText);
		$('whos_chatting').innerHTML = chatusers[0];
		}
	}

function do_send_inv(in_val) {
	show_hide('sent_msg');
	wr_invite(in_val);
	$('send_butt').style.display='none';
	if(!the_to) {window.setTimeout('set_to()',10000);}	//	10/29/13
	do_can ();			// hide some buttons and reset select
	}

function trim_list(ctr) {			// delete oldest rows from display
	ctr = $('person').rows.length;
	while ($('person').rows.length>ctr){
		var main = $('person');
		main.deleteRow(-1);
		}
	}

function do_can () {
	$('help').innerHTML = "";
	$('send_butt').style.display='none';
	$('can_butt').style.display='none';
	document.chat_form.chat_invite.options[0].selected = true;
	if(!the_to) {set_to();}	//	10/29/13	
	}		// end function do_can ()
	
function chat_start() {
	if(do_chat) {
		announce();
		getMessages(true);
		set_to();
		do_focus();
		} else {
		wr_chat_msg(document.chat_form_2);
		clearTimeout(the_to);
		}
	}

function chat_stop() {
	if(!do_chat) {
		wr_chat_msg(document.chat_form_2);
		clearTimeout(the_to);
		}
	}
	
function pause_messages() {	//	10/29/13
	clear_to();
	$('help').innerHTML = "Click Cancel to return to chat messages";
	}
// 	end of chat functions
</script>
</head>
<body onUnload='stop_loop(); do_audible();'>
	<div id='outer' style='height: 80%; width: 100%; background-color: #CECECE;'>
		<div id="map_canvas"></div>	
		<div id='plus_but' onClick = "zoomIn();"><IMG SRC = './images/zoomin.png' ALT='Zoom In' BORDER=0 STYLE = 'vertical-align: middle'></div>	
		<div id='minus_but' onClick = "zoomOut();"><IMG SRC = './images/zoomout.png' ALT='Zoom Out' BORDER=0 STYLE = 'vertical-align: middle'></div>
		<div id='help_but' onClick = "broadcast('Responder <?php print $respondername;?> needs assistance', 99);"><IMG SRC = './images/help.png' ALT='Help' BORDER=0 STYLE = 'vertical-align: middle'></div>	
		<div id='map_but' class='plain' style="display: inline-block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"  onClick = "map_controls_onoff();"><IMG SRC = './images/map.png' ALT='Map Controls' BORDER=0 STYLE = 'vertical-align: middle'></div>		
		<div id='map_controls' style='display: none; border: 3px outset #707070; background-color: #CECECE; width: 70px; height: 230px;'>
			<div id='day_but' style='display: none;' onClick = "do_day();"><IMG SRC = './images/day.png' ALT='Day Colorsl' BORDER=0 STYLE = 'vertical-align: middle'></div>
			<div id='night_but' onClick = "do_night();"><IMG SRC = './images/night.png' ALT='Night Colors' BORDER=0 STYLE = 'vertical-align: middle'></div>			
			<div id='toggle_tracks_but' style='display: none;' onClick = "toggleTracks();"><IMG SRC = './images/toggle_tracks.png' ALT='Toggle Tracks' BORDER=0 STYLE = 'vertical-align: middle'></div>	
			<div id='toggle_tracks_off_but' style='display: none;' onClick = "toggleTracks();"><IMG SRC = './images/toggle_tracks_off.png' ALT='Toggle Tracks' BORDER=0 STYLE = 'vertical-align: middle'></div>		
			<div id='center_but' onClick = "map.panTo(theLatLng,{animate: true});"><IMG SRC = './images/center.png' ALT='Center on me' BORDER=0 STYLE = 'vertical-align: middle'></div>	
		</div>
		<div id="buttons" style='position:absolute; left:-300px; top: 130px; z-index: 10; text-align: center; background-color: #CECECE; border: 3px outset #DEDEDE; padding: 10px;'>
			<span id="mylat" class='plain' style="display: inline-block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'alert_coords();'>My Lat Lng</span><BR />	
			<span id="myloc" class='plain' style="display: inline-block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'alert_location();'>My Location</span><BR />	
			<span id="refresh_but" class='plain' style="display: inline-block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'refresh_screen();'>Refresh</span><BR /><BR />
			<span style='font-weight: bold; font-size: 16px;'>Road Conditions</span><BR />					
			<select style='font-size: 16px;' id='condition_selector' name="selectionField" onChange="the_status(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text);"> 
				<option value=0 style='font-size: 16px;'>Select Type</option>
<?php
				foreach($c_types as $val) {

?>
					<option value=<?php print $val['id'];?> style='font-size: 16px;'><?php print $val['title'];?></option>
<?php
				}
?>
			</select><BR />
		</div>					
		<div id="buttons2" style='position:absolute; left:-300px; top: 130px; z-index: 10; text-align: center;'>				
			<span id="disp_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'alert("Dispatch Button");'>Dispatched</span>
			<span id="resp_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'do_sub("frm_responding","resp_but");'>Responding</span>
			<span id="os_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'do_sub("frm_on_scene","os_but");'>On Scene</span>	
			<span id="fenr_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'do_sub("frm_u2fenr","fenr_but");'>Fac Enroute</span>	
			<span id="farr_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'do_sub("frm_u2farr","farr_butt");'>Fac Arr</span>
			<span id="clear_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'do_sub("frm_clear","clear_but");'>Clear</span>
			<span id="mileage_start_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'start_miles();'>Start Miles</span>
			<span id="mileage_os_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'os_miles();'>On Scene Miles</span>						
			<span id="mileage_end_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'end_miles();'>End Miles</span>			
			<span id="notes_but" class='plain' style="display: block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'notes();'>Notes</span>
			<span id="recfac_but" class='plain' style="display: block; z-index: 10; width: auto;"></span>			

		</div>		
		<div id='app_outer'>
			<div id='title_button' class='plain' style='position: absolute; left: 0px; top: 0px; display: inline-block; width: auto;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick='showhideTitle();'><IMG SRC="../<?php print get_variable('logo');?>" ALT='Show Main Controls' style='vertical-align: middle;' BORDER=0 /></div>
			<div id='app_title' style='display: none;'>
<?php
				$temp = get_variable('_version');
				$version_ary = explode ( "-", $temp, 2);
				if(get_variable('title_string')=="") {
					$title_string = "<FONT SIZE='3'>ickets " . trim($version_ary[0]) . "</B></FONT>";
					} else {
					$title_string = "<FONT SIZE='3'><B>" .get_variable('title_string') . "</B></FONT>";
					}
?>		
				<span ID="tagline" CLASS="titlebar_text"><IMG SRC="../<?php print get_variable('logo');?>" style='position: absolute; left: 0px; top: 0px; vertical-align: middle;' BORDER=0 /><?php print $title_string; ?></span><BR />
				<FONT SIZE='+2'>Responder Mobile Screen</FONT><BR />	
				<DIV>
<?php
				if($logged_in == 0) {
?>
					<span id="login_but" class='plain' style="display: inline-block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'do_login();'><?php print get_text("Login"); ?></span>	
<?php
					} else {
?>
					<span id="logout_but" class='plain' style="display: inline-block; z-index: 10;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'do_logout();'><?php print get_text("Logout"); ?></span>	
<?php
					if($the_responder != 0) {
						print $the_status_sel;
						}
					}
?>
					<div id='theFlag' style='display: inline;'></div><BR />
					<DIV id='broadcastWrapper' class='plain' style='display: none; float: right; width: auto;'>
						<SPAN ID = 'usercount' CLASS="titlebar_text" style='float: right; font-weight: bold; padding-left: 20px;'></SPAN>
						<SPAN ID = 'timeText' CLASS="titlebar_text" style='float: right; font-weight: bold;'></SPAN>
					</DIV>
				</DIV>
				<div id='close_title_button' class='plain' style='position: absolute; right: 0px; top: 0px; display: inline-block; width: auto; height: auto;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick='showhideTitle();'><IMG SRC="./images/close2.png" ALT='Close' style='vertical-align: middle;' BORDER=0 /></div>
			<BR />
			</div>
		</div>
		<div id='screen1' class='screen' style='display: block;'>
			<div id='menu_but' class='plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "slideIt('buttons', this.id);">Show Menu</div><BR />
		</div>
		<div id='screen2' class='screen' style='display: none;'><BR /><BR />
			<div id="alert_list" class='lists'></div>	
			<div id="close_alert_detail" style='position: relative; left: 30%; display: none; width: 40px;'><span class="screen_but_plain" style="float: none; z-index: 999999; text-align: center;" onMouseOver="do_sb_hover(this.id);" onMouseOut="do_sb_plain(this.id);"  onClick="close_alert_detail();"><IMG SRC = './images/close.png' ALT='Close Detail' BORDER=0 STYLE = 'vertical-align: middle'></span></div>
			<div id="alert_detail" class='detail_page' style='display: none;'></div>				
		</div>
		<div id='screen3' class='screen' style='display: none;'><BR /><BR />
			<div id="ticket_list" class='lists'></div>
			<div id="ticket_detail_wrapper" style='z-index: 5; text-align: left; display: none; background-color: #EFEFEF;'>
				<div id='menu_but2' class='plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'get_recfac(); slideIt("buttons2", this.id);'>Show Menu</div>
				<div id="ticket_detail" class='detail_page' style='display: none;'></div>	
				<div id="tkt_message_list" class='lists' style='display: none;'></div>	
				<div id="tkt_message_detail" class='detail_page' style='display: none;'></div>		
				<div id="tkt_message_reply" class='detail_page' style='display: none;'>
					<div class='heading' style='width: 100%; height: 30px; text-align: center; color: #FFFFFF; background-color: #707070;'>MESSAGE DETAIL<span id='message_alert' style='color: red; font-weight: bold;'></span>		
						<span id="tkt_sub_msg" class='plain' style="float: right; display: inline-block; z-index: 10; width: 40px;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'send_message();'><IMG SRC = './images/send_email.png' ALT='Send' BORDER=0 STYLE = 'vertical-align: middle'></span>		
						<span id="tkt_can_msg" class='plain' style="float: right; display: inline-block; z-index: 10; width: 40px;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'can_reply();'><IMG SRC = './images/back.png' ALT='Cancel' BORDER=0 STYLE = 'vertical-align: middle'></span>
					</div>						
					<form name="tkt_reply_form" action="send_message();">
						<div style='width: 20%; display: inline-block;'>To:</div><div style='width: 70%; display: inline-block;'><input type="text" size='35' maxlength='128' name="frm_to"></div><BR />
						<div style='width: 20%; display: inline-block;'>From:</div><div style='width: 70%; display: inline-block;'><input type="text" size='35' maxlength='128' name="frm_from"></div><BR />
						<div style='width: 20%; display: inline-block;'>Subject:</div><div style='width: 70%; display: inline-block;'><input type="text" size='35' maxlength='128' name="frm_subject"></div><BR />
						<div style='width: 20%; display: inline-block; vertical-align: top;'>Message:</div><div style='width: 70%; display: inline-block;'><textarea name='frm_msg' rows="10" cols="30">Basic Message</textarea></div>
					</form>		
				</div>					
				<div id='directions_wrapper' style='z-index: 5; text-align: left; height: 100%; display: none; background-color: #EFEFEF;'><BR />
					<div style='width: 100%; color: #FFFFFF; background-color: #707070; height: 25px; font-weight: bold; text-align: center;'>DIRECTIONS				
					<span id="close_directions" class='plain' style="width: auto; display: block; z-index: 10; float: right; width: 40px;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'close_directions();'><IMG SRC = './images/close.png' ALT='Close Directions' BORDER=0 STYLE = 'vertical-align: middle'></span></div>			
					<div id='directions' style='overflow-y: scroll; z-index: 5; text-align: left; background-color: #EFEFEF; margin: 10px; width: 98%;'></div>
				</div>
			</div>
		</div>
		<div id='screen4' class='screen' style='display: none;'><BR /><BR />
			<div id="message_list" class='lists'></div>	
			<div id="message_detail" class='detail_page' style='display: none;'></div>	
			<div id="message_reply" class='detail_page' style='display: none;'>
				<div class='heading' style='width: 100%; height: 30px; text-align: center; color: #FFFFFF; background-color: #707070;'>MESSAGE DETAIL<span id='message_alert' style='color: red; font-weight: bold;'></span>		
					<span id="sub_msg" class='plain' style="float: right; display: inline-block; z-index: 10; width: 40px;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'send_message();'><IMG SRC = './images/send_email.png' ALT='Send' BORDER=0 STYLE = 'vertical-align: middle'></span>		
					<span id="can_msg" class='plain' style="float: right; display: inline-block; z-index: 10; width: 40px;" onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'can_reply();'><IMG SRC = './images/back.png' ALT='Cancel' BORDER=0 STYLE = 'vertical-align: middle'></span>
				</div>						
				<form name="reply_form" action="send_message();">
					<div style='width: 20%; display: inline-block;'>To:</div><div style='width: 70%; display: inline-block;'><input type="text" size='35' maxlength='128' name="frm_to"></div><BR />
					<div style='width: 20%; display: inline-block;'>From:</div><div style='width: 70%; display: inline-block;'><input type="text" size='35' maxlength='128' name="frm_from"></div><BR />
					<div style='width: 20%; display: inline-block;'>Subject:</div><div style='width: 70%; display: inline-block;'><input type="text" size='35' maxlength='128' name="frm_subject"></div><BR />
					<div style='width: 20%; display: inline-block; vertical-align: top;'>Message:</div><div style='width: 70%; display: inline-block;'><textarea name='frm_msg' rows="10" cols="30">Basic Message</textarea></div>
				</form>		
			</div>				
		</div>	
		<div id='screen5' class='chat_screen' style='display: none;'><BR /><BR /><BR />
			<div id="chat" style='z-index: 5; text-align: left; position: relative; left: 0%; width: 100%; height: auto; border: 2px outset #707070; overflow-y: hidden; background-color: #EFEFEF;'>
<?php
		if($logged_in == 1) {
				include('chat.php');
			}
?>
			</div>
		</div>	
	</div>
	<div id='screen_buttons'>
<?php
		if($logged_in == 1) {
?>
			<span id='sb1' class='screen_but_plain' onMouseOver="do_sb_hover(this.id);" onMouseOut="do_sb_plain(this.id);" onClick='screen1();'>Main</span>
			<span id='sb2' class='screen_but_plain' onMouseOver="do_sb_hover(this.id);" onMouseOut="do_sb_plain(this.id);" onClick='screen2();'>Alerts</span>
<?php
			if($the_responder != 0) {
?>
				<span id='sb3' class='screen_but_plain' onMouseOver="do_sb_hover(this.id);" onMouseOut="do_sb_plain(this.id);" onClick='screen3();'>Incidents</span>
				<span id='sb4' class='screen_but_plain' onMouseOver="do_sb_hover(this.id);" onMouseOut="do_sb_plain(this.id);" onClick='screen4();'>Messages</span>	
<?php
				}
?>
			<span id='sb5' class='screen_but_plain' onMouseOver="do_sb_hover(this.id);" onMouseOut="do_sb_plain(this.id);" onClick='screen5();'>Chat</span>			
<?php
			}
?>
	</div>
<?php
	$the_wav_file = get_variable('sound_wav');		// browser-specific cabilities as of 6/12/10
	$the_mp3_file = get_variable('sound_mp3');

	$temp = explode (" ", $browser);
	switch (trim($temp[0])) {		
	    case "firefox" :
			print (empty($the_wav_file))? "\n": "\t\t<audio src=\"../sounds/{$the_wav_file}\" preload></audio>\n";
			break;
	    case "chrome" :
	    case "safari" :
			print (empty($the_mp3_file))? "\n":  "\t\t<audio src=\"../sounds/{$the_mp3_file}\" preload></audio>\n";
			break;
	    default:
		}	// end switch
?>
	<FORM NAME="gout_form" action="index.php">
	<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
	</FORM>
	<FORM NAME="gin_form" action="index.php">
	<INPUT TYPE='hidden' NAME = 'do_login' VALUE = 1 />
	</FORM>
<script>
var map;
var	user_id = 1;
var route;
var theTrack;
var posPopup;
var latLng;

var my_Path = "http://127.0.0.1/_osm/tiles/";
var osmUrl = (in_local_bool=="1")? "../_osm/tiles/{z}/{x}/{y}.png":	"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";

var cm_api = "<?php print get_variable('cloudmade_api');?>";

if(cm_api != "") {
	var cmUrl = 'http://{s}.tile.cloudmade.com/' + cm_api + '/{styleId}/256/{z}/{x}/{y}.png',
		cmAttr = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade';
	var minimal   = L.tileLayer(osmUrl, {attribution: cmAttr}),
		midnight  = L.tileLayer(cmUrl, {styleId: 999,   attribution: cmAttr});
	} else {
	var minimal   = L.tileLayer(osmUrl, {attribution: cmAttr});	
	}
	
function do_day() {
	if(daynight == "day") return;
	daynight = "day";
	map.addLayer(minimal);
	map.removeLayer(midnight);
	$('day_but').style.display = "none";
	$('night_but').style.display = "block";
	$('app_title').style.display = "none";		
	}

function do_night() {
	if(daynight == "night") return;
	daynight = "night";
	map.addLayer(midnight);
	map.removeLayer(minimal);
	$('day_but').style.display = "block";
	$('night_but').style.display = "none";	
	$('app_title').style.display = "none";		
	}
		
function initialise() {	//	4/24/14
		if(cm_api != "") {
			if(!map) { map = L.map('map_canvas', {
				center: [<?php echo get_variable('def_lat');?>, <?php echo get_variable('def_lng');?>],
				maxZoom: 18,
				zoom: 12,
				zoomControl: false,
				layers: [minimal]
			});
			}
		} else {
			if(!map) { map = L.map('map_canvas', {
				center: [<?php echo get_variable('def_lat');?>, <?php echo get_variable('def_lng');?>],
				maxZoom: 18,
				zoom: 12,
				zoomControl: false,				
				layers: [minimal]
			});
			}
		}
	$('toggle_tracks_off_but').style.display = 'none';
	$('toggle_tracks_but').style.display = 'none';
	
	function onLocationFound(e) {
		var theBounds = map.getBounds();
		var radius = 0;
		var the_num;
		var theRotate;
		var info;
		the_lat = parseFloat(e.latlng.lat);
		the_lng = parseFloat(e.latlng.lng);
		theLatLng = new L.LatLng(the_lat, the_lng);
		theAltitude = ((e.altitude) && (e.altitude != null)) ? e.altitude : 0;
		theSpeed = (e.speed != null) ? e.speed : 0;		
		latLng = L.LatLng(the_lat, the_lng);
		if(!theTrack) {
			theTrack = L.polyline(theLatLng, {color: 'black'}).addTo(map);
			if(($('toggle_tracks_off_but').style.display == "none") && (!tracksOn)) {
				map.removeLayer(theTrack);			
				$('toggle_tracks_off_but').style.display = "inline-block";
				$('toggle_tracks_but').style.display = "none";	
				} else if (($('toggle_tracks_but').style.display == "none") && (tracksOn)) {
				map.addLayer(theTrack);
				$('toggle_tracks_but').style.display = "inline-block";	
				$('toggle_tracks_off_but').style.display = "none";
				}
			} else {
			theTrack.addLatLng(theLatLng);
			if(($('toggle_tracks_off_but').style.display == "none") && (!tracksOn)) {
				map.removeLayer(theTrack);			
				$('toggle_tracks_off_but').style.display = "inline-block";
				$('toggle_tracks_but').style.display = "none";		
				} else if (($('toggle_tracks_but').style.display == "none") && (tracksOn)) {
				map.addLayer(theTrack);				
				$('toggle_tracks_but').style.display = "inline-block";	
				$('toggle_tracks_off_but').style.display = "none";
				}			
			}

		radius = (e.accuracy) ? e.accuracy / 2 : 0;
		if((e.heading) && (e.heading.between(-1,91))) {
			the_num = e.heading;
			theRotate = 0;
			} else if((e.heading) && (e.heading.between(90,181))) {
			the_num = e.heading - 90;
			theRotate = 90;
			} else if((e.heading) && (e.heading.between(180,271))) {
			the_num = e.heading - 180;
			theRotate = 180;
			} else if((e.heading) && (e.heading.between(270,361))) {
			the_num = e.heading - 270;
			theRotate = 270;
			} else {
			the_num = 0;
			theRotate = 0;
			}
		the_icon = Math.ceil(the_num).toString() + ".png";				
		var theIconUrl = "./pos_markers/rotate_icon.php?icon="+the_icon+"&degrees=-" + theRotate;
		theIcon = new circIcon({iconUrl: theIconUrl});
		if(!posMarker) {
			posMarker = L.marker(theLatLng, {icon: theIcon}).addTo(map);
			} else {
			posMarker.setLatLng(theLatLng);
			posMarker.setIcon(theIcon);
			}
		if(the_counter == 0) {	//	4/24/14
			the_counter = 1;
			map.panTo(theLatLng,{animate: true});
			}
		if(!theBounds.contains(theLatLng)) {
			map.panTo(theLatLng,{animate: true});
			}
		posMarker.on('click', function(e) {
			do_geolocate(latLng, the_lat, the_lng);
		  //open popup;
			posPopup = L.popup()
			   .setLatLng(theLatLng) //(assuming e.latlng returns the coordinates of the event)
			   .setContent("")
			   .openOn(map);
			});		
		}

	function onLocationError(e) {
//		alert(e.message);
		}

	map.on('locationfound', onLocationFound);
	map.on('locationerror', onLocationError);
	document.addEventListener("deviceready", onDeviceReady, false);
	}

function do_geolocate(the_latlng, theLat, theLng) {
	var theinfo;
	(new google.maps.Geocoder()).geocode({latLng: the_latlng}, function(resp) {
		if ((resp) && (resp[0])) {
			var bits = [];
			for (var i = 0, I = resp[0].address_components.length; i < I; ++i) {
				var component = resp[0].address_components[i];
				if (contains(component.types, 'political')) {
					bits.push(component.long_name);
					}
				if (contains(component.types, 'administrative_area_level_1')) {
					bits.push(component.long_name);
					}	
				if (contains(component.types, 'administrative_area_level_2')) {
					bits.push(component.long_name);
					}	
				if (contains(component.types, 'administrative_area_level_3')) {
					bits.push(component.long_name);
					}
				if (contains(component.types, 'colloquial_area')) {
					bits.push(component.long_name);
					}	
				if (contains(component.types, 'premise')) {
					bits.push(component.long_name);
					}		
				if (contains(component.types, 'sub_premise')) {
					bits.push(component.long_name);
					}										
				if (contains(component.types, 'street_address')) {
					bits.push(component.long_name);
					}
				if (contains(component.types, 'postal_code')) {
					bits.push(component.long_name);
					}						
				if (contains(component.types, 'intersection')) {
					bits.push(component.long_name);
					}	
				if (contains(component.types, 'route')) {
					bits.push(component.long_name);
					}						
				if (contains(component.types, 'locality')) {
					bits.push(component.long_name);
					}			
				if (contains(component.types, 'sublocality')) {
					bits.push(component.long_name);
					}		
				if (contains(component.types, 'neighborhood')) {
					bits.push(component.long_name);
					}	
				if (contains(component.types, 'neighborhood')) {
					bits.push(component.long_name);
					}						
				}
			form_add = resp[0].formatted_address;
			bits.push(form_add);			
			if (bits.length) {
				place = bits.join(' > ');
				}
			theinfo = "You are around here<BR />" + resp[0].formatted_address + "<BR /><BR />Your Latitude is: " + theLat + "<BR />Your Longitude is: " + theLng;
			posPopup.setContent(theinfo);
			}
		});
	}
	
function addScript(url) {
	var script = document.createElement('script');
	script.type="text/javascript";
	script.src=url;
	document.getElementsByTagName('head') [0].appendChild(script);
	}

window.getRoute = function (response) {
	var point, points = [];
	var theText = "";
	for (var i=0; i<response.route_geometry.length; i++) {
		point = new L.LatLng(response.route_geometry[i][0] , response.route_geometry[i][1]);
		points.push(point);
		}
	for (var x=0; x<response.route_instructions.length; x++) {
		if((x != 0) && (x != response.route_instructions.length -1)) {
			theText += "<IMG SRC='http://tile.cloudmade.com/wml/latest/images/routing/arrows/" + response.route_instructions[x][7] + ".png'></IMG>";
			}
		theText += response.route_instructions[x][0] + " ";
		theText += response.route_instructions[x][4] + "<BR />";
		}	
	route= new L.Polyline(points, {
		weight: 3,
		opacity: 0.5,
		smoothFactor: 1
	}).addTo(map);
	route.bringToFront();
	$('directions').innerHTML = theText;
	}
			
function setDirections(toAddress, recfacAddress) {
	$('menu_but2').style.display = 'none';
	$('ticket_detail').style.display = 'none';		
	$('directions_wrapper').style.display = 'block';	
	$('directions').innerHTML = "Getting Route.....";
	fromAddress = the_lat + " " + the_lng;
	fromMarker = new L.Marker(new L.latLng([fromAddress])).addTo(map);
	toMarker=new L.Marker(new L.latLng([toAddress])).addTo(map);
	if(recfacAddress != "") {
		transit = "," + [toAddress] + ",";
		toAddress = recfacAddress;
		} else {
		transit = ",";
		}
		var theAPI = '<?php print get_variable('cloudmade_api');?>';
        console.log('http://routes.cloudmade.com/' + theAPI + '/api/0.3/' + the_lat + ',' + the_lng + transit + toAddress + '/car.js?callback=getRoute');
        addScript('http://routes.cloudmade.com/' + theAPI + '/api/0.3/' + the_lat + ',' + the_lng + transit + toAddress + '/car.js?callback=getRoute');
	}
	
function zoomIn() {
	map.zoomIn();
	$('app_title').style.display = "none";		
	}
	
function zoomOut() {
	map.zoomOut();
	$('app_title').style.display = "none";
	}

function onDeviceReady() {
	get_geo();
	}

window.onload = function () {
	if(! window.device) {
		onDeviceReady();
		}
	}
</script>
<div id='has_line' style='display: none;'>
	<SPAN id='closeHas' class='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' onClick="$('has_line').style.display = 'none';">Close</SPAN>
	<SPAN id='has_wrapper'><marquee id='has_text' behavior="scroll" direction="left"></marquee></SPAN>
</DIV>
<?php
if ((intval(get_variable ('broadcast')==1)) && (intval(get_variable ('internet')==1))) {
	require_once('./incs/mob_sockets.inc.php');
	}
?>
</body>
</html>
