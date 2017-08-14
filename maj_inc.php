<?php
error_reporting(E_ALL);

$units_side_bar_height = .5;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$zoom_tight = FALSE;				// replace with a decimal number to over-ride the standard default zoom setting
$iw_width= "300px";					// map infowindow with
$groupname = isset($_SESSION['group_name']) ? $_SESSION['group_name'] : "";	//	4/11/11

$the_resp_id = (isset($_GET['id']))? $_GET['id']: 0;	//	11/18/13
/*
02/04/14 New file.
*/

@session_start();	
session_write_close();
require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));

extract($_GET);
extract($_POST);

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}
	
$sm_inc_icons = array("sm_blue.png", "sm_green.png", "sm_red.png");
$inc_sev_names = array("Normal", "Medium", "High");
$inc_severities = array();
for ($i = 0;$i <= 2;$i++) {
    $inc_severities[$i] = array($inc_sev_names[$i], $i);
	}

$icons = array("square_gold.png", "square_silver.png", "square_bronze.png");
$sm_icons = array("sm_square_gold.png", "sm_square_silver.png", "sm_square_bronze.png");
$mi_level_names = array("Gold", "Silver", "Bronze");
$mi_levels = array();
for ($i = 0;$i <= 2;$i++) {
    $mi_levels[$i] = array($mi_level_names[$i], $i);
	}

function get_mi_level_icon_legend (){			// returns legend string - 1/1/09
	global $mi_levels, $sm_icons;
	$print = "";
	foreach($mi_levels as $val) {
		$temp = $val;
		$print .= "\t\t<SPAN class='legend' style='height: 3em; text-align: center; vertical-align: middle; float: none;'> ". $temp[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$temp[1]] . "' STYLE = 'vertical-align: middle' BORDER=0 PADDING='10'>&nbsp;&nbsp;&nbsp;</SPAN>";
		}
	return $print;
	}			// end function get_icon_legend ()
	
function get_inc_icon_legend (){			// returns legend string - 1/1/09
	global $inc_severities, $sm_inc_icons;
	$print = "";
	foreach($inc_severities as $val) {
		$temp = $val;
		$print .= "\t\t<SPAN class='legend' style='height: 3em; text-align: center; vertical-align: middle; float: none;'> ". $temp[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_inc_icons[$temp[1]] . "' STYLE = 'vertical-align: middle' BORDER=0 PADDING='10'>&nbsp;&nbsp;&nbsp;</SPAN>";
		}
	return $print;
	}			// end function get_icon_legend ()
	
$comm_arr = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` ORDER BY `id` ASC";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$comm_arr[$row['id']][0] = $row['id'];
	$comm_arr[$row['id']][1] = $row['user'];	
	$comm_arr[$row['id']][2] = $row['name_f'];	
	$comm_arr[$row['id']][3] = $row['name_l'];	
	$comm_arr[$row['id']][4] = $row['email'];	
	$comm_arr[$row['id']][5] = $row['email_s'];
	$comm_arr[$row['id']][6] = $row['phone_p'];	
	$comm_arr[$row['id']][7] = $row['phone_s'];
	}
	
function get_building_details($id) {
	$ret_arr = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `id` = " . $id;		// types in use
	$result = mysql_query($query);
	if($result) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr[0] = $row['name'];
		$ret_arr[1] = $row['street'];
		$ret_arr[2] = $row['city'];
		$ret_arr[3] = $row['state'];
		$ret_arr[4] = $row['lat'];
		$ret_arr[5] = $row['lon'];
		} else {
		$ret_arr[0] = 0;
		}
	return $ret_arr;
	}

function get_building($theName, $id = NULL) {
	$query_bldg = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' ORDER BY `name` ASC";		// types in use
	$result_bldg = mysql_query($query_bldg) or do_error($query_bldg, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result_bldg) > 0) {
		$sel_str = "<SELECT name='" . $theName . "' onChange='clear_data(document.mi_add_Form, \"" . $theName . "\", this.options[selectedIndex].value);'>\n";
		$sel_str .= "\t<OPTION value = 0 selected>Select building</OPTION>\n";
		while ($row_bldg = stripslashes_deep(mysql_fetch_assoc($result_bldg))) {
			if($id) {
				$sel = ($row_bldg['id'] == $id) ? "SELECTED" : "";
				} else {
				$sel = "";
				}
			$sel_str .= "\t<OPTION value = " . $row_bldg['id'] . " " . $sel . ">" . $row_bldg['name'] . "</OPTION>\n";
			}		// end while ()

		$sel_str .= "\t</SELECT>\n<BR />";
		if($theName == "frm_gold_loc") {
			$theType = 'gold';
			$theStreet = "frm_gold_street";
			$theCity = "frm_gold_city";
			$theState = "frm_gold_state";
			$theLat = "frm_gold_lat";
			$theLng = "frm_gold_lng";
			$theDiv = "gold_address_data";
			} elseif($theName == "frm_silver_loc") {
			$theType = 'silver';
			$theStreet = "frm_silver_street";
			$theCity = "frm_silver_city";
			$theState = "frm_silver_state";
			$theLat = "frm_silver_lat";
			$theLng = "frm_silver_lng";
			$theDiv = "silver_address_data";
			} elseif($theName == "frm_bronze_loc") {
			$theType = 'bronze';
			$theStreet = "frm_bronze_street";
			$theCity = "frm_bronze_city";
			$theState = "frm_bronze_state";
			$theLat = "frm_bronze_lat";
			$theLng = "frm_bronze_lng";
			$theDiv = "bronze_address_data";			
			} elseif($theName == "frm_level4_loc") {
			$theType = 'level4';
			$theStreet = "frm_level4_street";
			$theCity = "frm_level4_city";
			$theState = "frm_level4_state";
			$theLat = "frm_level4_lat";
			$theLng = "frm_level4_lng";
			$theDiv = "level4_address_data";
			} elseif($theName == "frm_level5_loc") {
			$theType = 'level5';
			$theStreet = "frm_level5_street";
			$theCity = "frm_level5_city";
			$theState = "frm_level5_state";
			$theLat = "frm_level5_lat";
			$theLng = "frm_level5_lng";
			$theDiv = "level5_address_data";
			} elseif($theName == "frm_level6_loc") {
			$theType = 'level6';
			$theStreet = "frm_level6_street";
			$theCity = "frm_level6_city";
			$theState = "frm_level6_state";
			$theLat = "frm_level6_lat";
			$theLng = "frm_level6_lng";
			$theDiv = "level6_address_data";
			}
		$sel_str .= "<DIV id='" . $theDiv . "' style='vertical-align: top;'><TABLE>";
		$sel_str .= "<TR>";		
		$sel_str .= "<TD CLASS='td_label text'>Street&nbsp;&nbsp;<BUTTON type='button' style='vertical-align: top;' onClick='mi_loc_lkup(document.mi_add_Form, \"" . $theType . "\");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON></TD>";
		$sel_str .= "<TD CLASS='td_data text'><INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='" . $theStreet . "' VALUE='' /></TD>";
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>City</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='" . $theCity . "' VALUE='" . get_variable('def_city') . "' /></TD>";
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>State</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='" . $theState . "' VALUE='" . get_variable('def_st') . "' /></TD>";
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>Lat / Lng</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='" . $theLat . "' VALUE=''>";
		$sel_str .= "<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='" . $theLng . "' VALUE=''></TD>";
		$sel_str .= "</TR></TABLE>";
		$sel_str .= "</DIV>";
		} else {
		if($theName == "frm_gold_loc") {
			$theType = 'gold';
			$theStreet = "frm_gold_street";
			$theCity = "frm_gold_city";
			$theState = "frm_gold_state";
			$theLat = "frm_gold_lat";
			$theLng = "frm_gold_lng";
			$theDiv = "gold_address_data";			
			} elseif($theName == "frm_silver_loc") {
			$theType = 'silver';
			$theStreet = "frm_silver_street";
			$theCity = "frm_silver_city";
			$theState = "frm_silver_state";
			$theLat = "frm_silver_lat";
			$theLng = "frm_silver_lng";
			$theDiv = "silver_address_data";			
			} elseif($theName == "frm_bronze_loc") {
			$theType = 'bronze';
			$theStreet = "frm_bronze_street";
			$theCity = "frm_bronze_city";
			$theState = "frm_bronze_state";
			$theLat = "frm_bronze_lat";
			$theLng = "frm_bronze_lng";
			$theDiv = "bronze_address_data";						
			} elseif($theName == "frm_level4_loc") {
			$theType = 'level4';
			$theStreet = "frm_level4_street";
			$theCity = "frm_level4_city";
			$theState = "frm_level4_state";
			$theLat = "frm_level4_lat";
			$theLng = "frm_level4_lng";
			$theDiv = "level4_address_data";
			} elseif($theName == "frm_level5_loc") {
			$theType = 'level5';
			$theStreet = "frm_level5_street";
			$theCity = "frm_level5_city";
			$theState = "frm_level5_state";
			$theLat = "frm_level5_lat";
			$theLng = "frm_level5_lng";
			$theDiv = "level5_address_data";
			} elseif($theName == "frm_level6_loc") {
			$theType = 'level6';
			$theStreet = "frm_level6_street";
			$theCity = "frm_level6_city";
			$theState = "frm_level6_state";
			$theLat = "frm_level6_lat";
			$theLng = "frm_level6_lng";
			$theDiv = "level6_address_data";
			}
		$sel_str = "<DIV id='" . $theDiv . "' style='vertical-align: top;'><TABLE>";
		$sel_str .= "<TR>";		
		$sel_str .= "<TD CLASS='td_label text'>Street&nbsp;&nbsp;<BUTTON type='button' style='vertical-align: top;' onClick='mi_loc_lkup(document.mi_add_Form, \"" . $theType . "\");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON></TD>";
		$sel_str .= "<TD CLASS='td_data text'><INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='" . $theStreet . "' VALUE='' /></TD>";
		$sel_str .= "</TR><TR>";		
		$sel_str .= "<TD CLASS='td_label text'>City</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='" . $theCity . "' VALUE='" . get_variable('def_city') . "' /></TD>";
		$sel_str .= "</TR><TR>";	
		$sel_str .= "<TD CLASS='td_label text'>State</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='" . $theState . "' VALUE='" . get_variable('def_st') . "' /></TD>";
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>Lat / Lng</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='" . $theLat . "' VALUE=''>";
		$sel_str .= "<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='" . $theLng . "' VALUE=''></TD>";
		$sel_str .= "</TR></TABLE>";	
		$sel_str .= "</DIV>";	
		}
	return $sel_str;
	}
	
function get_building_edit($theName, $id = NULL) {
	if($theName == "frm_gold_loc") {
		$theType = 'gold';
		$theStreet = "frm_gold_street";
		$theCity = "frm_gold_city";
		$theState = "frm_gold_state";
		$theLat = "frm_gold_lat";
		$theLng = "frm_gold_lng";
		$theDiv = "gold_address_data";
		$theButton = "gold_loc_button";
		} elseif($theName == "frm_silver_loc") {
		$theType = 'silver';
		$theStreet = "frm_silver_street";
		$theCity = "frm_silver_city";
		$theState = "frm_silver_state";
		$theLat = "frm_silver_lat";
		$theLng = "frm_silver_lng";
		$theDiv = "silver_address_data";
		$theButton = "silver_loc_button";
		} elseif($theName == "frm_bronze_loc") {
		$theType = 'bronze';
		$theStreet = "frm_bronze_street";
		$theCity = "frm_bronze_city";
		$theState = "frm_bronze_state";
		$theLat = "frm_bronze_lat";
		$theLng = "frm_bronze_lng";
		$theDiv = "bronze_address_data";
		$theButton = "bronze_loc_button";
		} elseif($theName == "frm_level4_loc") {
		$theType = 'level4';
		$theStreet = "frm_level4_street";
		$theCity = "frm_level4_city";
		$theState = "frm_level4_state";
		$theLat = "frm_level4_lat";
		$theLng = "frm_level4_lng";
		$theDiv = "level4_address_data";
		$theButton = "level4_loc_button";
		} elseif($theName == "frm_level5_loc") {
		$theType = 'level5';
		$theStreet = "frm_level5_street";
		$theCity = "frm_level5_city";
		$theState = "frm_level5_state";
		$theLat = "frm_level5_lat";
		$theLng = "frm_level5_lng";
		$theDiv = "level5_address_data";
		$theButton = "level5_loc_button";
		} elseif($theName == "frm_level6_loc") {
		$theType = 'level6';
		$theStreet = "frm_level6_street";
		$theCity = "frm_level6_city";
		$theState = "frm_level6_state";
		$theLat = "frm_level6_lat";
		$theLng = "frm_level6_lng";
		$theDiv = "level6_address_data";
		$theButton = "level6_loc_button";
		}
	$query_bldg = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' ORDER BY `name` ASC";		// types in use
	$result_bldg = mysql_query($query_bldg) or do_error($query_bldg, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result_bldg) > 0) {
		$sel_str = "<select name='" . $theName . "' onChange='get_building(this.options[selectedIndex].value, \"" . $theType . "\", document.mi_edit_Form); tidyDiv(\"" . $theDiv . "\", this.options[selectedIndex].value, \"" . $theButton . "\");'>";
		$sel_str .= "<option value = 0 selected>Select building</option>";
		while ($row_bldg = stripslashes_deep(mysql_fetch_assoc($result_bldg))) {
			if($id) {
				$sel = ($row_bldg['id'] == $id) ? "SELECTED" : "";
				} else {
				$sel = "";
				}
			$sel_str .= "<option value = " . $row_bldg['id'] . " " . $sel . ">" . $row_bldg['name'] . "</option>";
			}		// end while ()

		if($id > 0) {
			$display = "display: none;";
			} else {
			$display = "";
			}
		$sel_str .= "</SELECT><BR />";
		$sel_str .= "<DIV id='" . $theDiv . "' style='vertical-align: top;" . $display . "'><TABLE>";
		$sel_str .= "<TR>";		
		$sel_str .= "<TD CLASS='td_label text'>Street&nbsp;&nbsp;<BUTTON type='button' id='" . $theButton . "' style='vertical-align: middle; display: inline-block;' onClick='mi_loc_lkup(document.mi_edit_Form, \"" . $theType . "\");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON></TD>";
		$sel_str .= "<TD CLASS='td_data text'><INPUT style='vertical-align: middle;' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='" . $theStreet . "' VALUE='' /></TD>";
		$sel_str .= "</TR><TR>";	
		$sel_str .= "<TD CLASS='td_label text'>City</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='" . $theCity . "' VALUE='" . get_variable('def_city') . "' /></TD>";
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>State</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='" . $theState . "' VALUE='" . get_variable('def_st') . "' /></TD>";
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>Lat / Lng</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='" . $theLat . "' VALUE=''>";
		$sel_str .= "<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='" . $theLng . "' VALUE=''></TD>";
		$sel_str .= "</TR></TABLE>";
		$sel_str .= "</DIV>";	
		} else {
		$sel_str = "<DIV id='" . $theDiv . "' style='vertical-align: top;'><TABLE>";
		$sel_str .= "<TR>";		
		$sel_str .= "<TD CLASS='td_label text'>Street&nbsp;&nbsp;<BUTTON type='button' id='" . $theButton . "' style='vertical-align: middle; display: inline-block;' onClick='mi_loc_lkup(document.mi_add_Form, \"" . $theType . "\");return false;'><img src='./markers/glasses.png' alt='Lookup location.' /></BUTTON></TD>";
		$sel_str .= "<TD CLASS='td_data text'><INPUT style='vertical-align: middle;' MAXLENGTH='64' SIZE='48' TYPE='text' NAME='" . $theStreet . "' VALUE='' /></TD>";
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>City</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='64' SIZE='48' TYPE='text' NAME='" . $theCity . "' VALUE='" . get_variable('def_city') . "' /></TD>";
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>State</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='4' SIZE='4' TYPE='text' NAME='" . $theState . "' VALUE='" . get_variable('def_st') . "' /></TD>";	
		$sel_str .= "</TR><TR>";
		$sel_str .= "<TD CLASS='td_label text'>Lat / Lng</TD><TD CLASS='td_data text'><INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='" . $theLat . "' VALUE=''>";
		$sel_str .= "<INPUT MAXLENGTH='10' SIZE='10' TYPE='text' NAME='" . $theLng . "' VALUE=''></TD>";
		$sel_str .= "</TR></TABLE>";
		$sel_str .= "</DIV>";	
		}
	return $sel_str;
	}
	
function get_building_only($theName) {
	$query_bldg = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' ORDER BY `name` ASC";		// types in use
	$result_bldg = mysql_query($query_bldg) or do_error($query_bldg, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result_bldg) > 0) {
		$sel_str = "<select name='" . $theName . "' onChange='clear_data(document.mi_edit_Form, \"" . $theName . "\", this.options[selectedIndex].value);'>\n";
		$sel_str .= "\t<option value = 0 selected>Select building</option>\n";
		while ($row_bldg = stripslashes_deep(mysql_fetch_assoc($result_bldg))) {
			$sel_str .= "\t<option value = " . $row_bldg['id'] . ">" . $row_bldg['name'] . "</option>\n";
			}		// end while ()

		$sel_str .= "\t</SELECT>\n";
		} else {
		$sel_str = "";
		}
	return $sel_str;
	}
	
function get_loc_name($id) {
	if($id == 0) {
		return "";
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' AND `id` = " . $id . " ORDER BY `name` ASC";		// types in use
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['name'];
		}
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Major Incidents Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT>
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/osopenspace.js"></script>
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
<?php
	if ($_SESSION['internet'] || $_SESSION['good_internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
?>
			<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
			<script type="application/x-javascript" src="./js/Google.js"></script>
<?php 
			}
		}
?>
	<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
	<script type="application/x-javascript" src="./js/usng.js"></script>
	<script type="application/x-javascript" src="./js/osgb.js"></script>
	<script type="application/x-javascript" src="./js/geotools2.js"></script>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
	var sortby = '`date`';	//	11/18/13
	var sort = "DESC";	//	11/18/13
	var columns = "<?php print get_msg_variable('columns');?>";	//	11/18/13
	var the_columns = new Array(<?php print get_msg_variable('columns');?>);	//	11/18/13
	var thescreen = 'units';	//	11/18/13
	var map, label;		// note global
	var layercontrol;
	var mi_interval = null;
	var micell1 = 0;
	var micell2 = 0;
	var micell3 = 0
	var micell4 = 0;
	var micell5 = 0;
	var micell6 = 0;
	var micell7 = 0;
	var micell8 = 0;
	var micell9 = 0;
	var micell10 = 0;
	var comm_arr = <?php echo json_encode($comm_arr); ?>;
	var dzf = parseInt("<?php print get_variable('def_zoom_fixed');?>");
	var colors = new Array ('odd', 'even');
	var icons=[];
	var goldmarker;
	var silvermarker;
	var bronzemarker;
	var level4marker;
	var level5marker;
	var level6marker;
	icons[<?php echo $GLOBALS['SEVERITY_NORMAL'];?>] = 1;	// blue
	icons[<?php echo $GLOBALS['SEVERITY_MEDIUM'];?>] = 2;	// yellow
	icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>] =  3;	// red
	icons[4] =  4;	// white
	
	var loc_icons=[];
	loc_icons[0] = 0;	// Gold
	loc_icons[1] = 1;	// Silver
	loc_icons[2] = 2;	// Bronze	
	
	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();												// 11/2/08

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08

	function JSfnTrim(argvalue) {					// drops leading and trailing spaces and cr's
		var tmpstr = ltrim(argvalue);
		return rtrim(tmpstr);
			function ltrim(argvalue) {
				while (1) {
					if ((argvalue.substring(0, 1) != " ") && (argvalue.substring(0, 1) != "\n"))
						break;
					argvalue = argvalue.substring(1, argvalue.length);
					}
				return argvalue;
				}								// end function ltrim()
			function rtrim(argvalue) {
				while (1) {
					if ((argvalue.substring(argvalue.length - 1, argvalue.length) != " ") && (argvalue.substring(argvalue.length - 1, argvalue.length) != "\n"))
						break;
					argvalue = argvalue.substring(0, argvalue.length - 1);
					}
				return argvalue;
			}									// end rtrim()
		}										// end JSfnTrim()
		
	function load_bldg_mkr(id, my_form, theType) {
		var randomnumber=Math.floor(Math.random()*99999999);
		var sessID = "<?php print $_SESSION['id'];?>";
		var url = './ajax/get_bldg_details.php?id=' + id + '&version=' + randomnumber+'&q='+sessID;
		sendRequest (url,theBldgCB, "");
		function theBldgCB(req) {
			var theDetails = JSON.decode(req.responseText);
			var lat = parseFloat(theDetails[4]);
			var lng = parseFloat(theDetails[5]);
			var theAddress = theDetails[0] + "," + theDetails[1] + ", " + theDetails[2] + ", " + theDetails[3];
			pt_to_map (my_form, theType, lat, lng, theAddress)
			}
		}
		
	function clear_data(my_form, commandType, theID) {
		var theType;
		if(theID != "") {
			if(commandType == "frm_gold_loc") {
				theType = "gold";
				if(goldmarker) {map.removeLayer(goldmarker);}			
				if(theID == 0) {
					my_form.frm_gold_street.style.visibility = my_form.frm_gold_city.style.visibility = my_form.frm_gold_state.style.visibility = $('gold_loc_button').style.visibility = 'visible';
					$('gold_address_data').style.display = "inline-block";	
					} else {
					$('gold_address_data').style.display = "none";		
					my_form.frm_gold_street.value = "";
					my_form.frm_gold_city.value = "";
					my_form.frm_gold_state.value = "";
					$('gold_loc_button').style.visibility = 'hidden';
					my_form.frm_gold_street.style.visibility = my_form.frm_gold_city.style.visibility = my_form.frm_gold_state.style.visibility = 'hidden';
					}
				} else if(commandType == "frm_silver_loc") {
				theType = "silver";
				if(silvermarker) {map.removeLayer(silvermarker);}		
				if(theID == 0) {
					my_form.frm_silver_street.style.visibility = my_form.frm_silver_city.style.visibility = my_form.frm_silver_state.style.visibility = $('silver_loc_button').style.visibility = 'visible';
					$('silver_address_data').style.display = "inline-block";	
					} else {
					$('silver_address_data').style.display = "none";			
					my_form.frm_silver_street.value = "";
					my_form.frm_silver_city.value = "";
					my_form.frm_silver_state.value = "";
					$('silver_loc_button').style.visibility = 'hidden';
					my_form.frm_silver_street.style.visibility = my_form.frm_silver_city.style.visibility = my_form.frm_silver_state.style.visibility = 'hidden';
					}
				} else if(commandType == "frm_bronze_loc") {
				theType = "bronze";
				if(bronzemarker) {map.removeLayer(bronzemarker);}		
				if(theID == 0) {
					my_form.frm_bronze_street.style.visibility = my_form.frm_bronze_city.style.visibility = my_form.frm_bronze_state.style.visibility = $('bronze_loc_button').style.visibility = 'visible';
					$('bronze_address_data').style.display = "inline-block";	
					} else {
					$('bronze_address_data').style.display = "none";						
					my_form.frm_bronze_street.value = "";
					my_form.frm_bronze_city.value = "";
					my_form.frm_bronze_state.value = "";
					$('bronze_loc_button').style.visibility = 'hidden';
					my_form.frm_bronze_street.style.visibility = my_form.frm_bronze_city.style.visibility = my_form.frm_bronze_state.style.visibility = 'hidden';
					}
				} else if(commandType == "frm_level4_loc") {
				theType = "level4";
				if(level4marker) {map.removeLayer(level4marker);}		
				if(theID == 0) {
					my_form.frm_level4_street.style.visibility = my_form.frm_level4_city.style.visibility = my_form.frm_level4_state.style.visibility = $('level4_loc_button').style.visibility = 'visible';
					$('level4_address_data').style.display = "inline-block";	
					} else {
					$('level4_address_data').style.display = "none";						
					my_form.frm_level4_street.value = "";
					my_form.frm_level4_city.value = "";
					my_form.frm_level4_state.value = "";
					$('level4_loc_button').style.visibility = 'hidden';
					my_form.frm_level4_street.style.visibility = my_form.frm_level4_city.style.visibility = my_form.frm_level4_state.style.visibility = 'hidden';
					}
				} else if(commandType == "frm_level5_loc") {
				theType = "level5";
				if(level5marker) {map.removeLayer(level5marker);}		
				if(theID == 0) {
					my_form.frm_level5_street.style.visibility = my_form.frm_level5_city.style.visibility = my_form.frm_level5_state.style.visibility = $('level5_loc_button').style.visibility = 'visible';
					$('level5_address_data').style.display = "inline-block";
					} else {
					$('level5_address_data').style.display = "none";
					my_form.frm_level5_street.value = "";
					my_form.frm_level5_city.value = "";
					my_form.frm_level5_state.value = "";
					$('level5_loc_button').style.visibility = 'hidden';
					my_form.frm_level5_street.style.visibility = my_form.frm_level5_city.style.visibility = my_form.frm_level5_state.style.visibility = 'hidden';
					}
				} else if(commandType == "frm_level6_loc") {
				theType = "level6";
				if(level6marker) {map.removeLayer(level6marker);}		
				if(theID == 0) {
					my_form.frm_level6_street.style.visibility = my_form.frm_level6_city.style.visibility = my_form.frm_level6_state.style.visibility = $('level6_loc_button').style.visibility = 'visible';
					$('level6_address_data').style.display = "inline-block";
					} else {
					$('level6_address_data').style.display = "none";
					my_form.frm_level6_street.value = "";
					my_form.frm_level6_city.value = "";
					my_form.frm_level6_state.value = "";
					$('level6_loc_button').style.visibility = 'hidden';
					my_form.frm_level6_street.style.visibility = my_form.frm_level6_city.style.visibility = my_form.frm_level6_state.style.visibility = 'hidden';
					}
				}
			} else {
			$('gold_loc_button').style.visibility = 'visible';
			$('silver_loc_button').style.visibility = 'visible';
			$('bronze_loc_button').style.visibility = 'visible';
			$('level4_loc_button').style.visibility = 'visible';
			$('level5_loc_button').style.visibility = 'visible';
			$('level6_loc_button').style.visibility = 'visible';
			my_form.gold_address_data,style.display = my_form.silver_address_data,style.display = my_form.bronze_address_data,style.display = "inline-block";
			my_form.level4_address_data,style.display = my_form.level5_address_data,style.display = my_form.level6_address_data,style.display = "inline-block";
			my_form.frm_gold_street.style.visibility = my_form.frm_gold_city.style.visibility = my_form.frm_gold_state.style.visibility = 'visible';
			my_form.frm_silver_street.style.visibility = my_form.frm_silver_city.style.visibility = my_form.frm_silver_state.style.visibility = 'visible';
			my_form.frm_bronze_street.style.visibility = my_form.frm_bronze_city.style.visibility = my_form.frm_bronze_state.style.visibility = 'visible';
			my_form.frm_level4_street.style.visibility = my_form.frm_level4_city.style.visibility = my_form.frm_level4_state.style.visibility = 'visible';	
			my_form.frm_level5_street.style.visibility = my_form.frm_level5_city.style.visibility = my_form.frm_level5_state.style.visibility = 'visible';	
			my_form.frm_level6_street.style.visibility = my_form.frm_level6_city.style.visibility = my_form.frm_level6_state.style.visibility = 'visible';				
			}
		get_building(theID, theType, my_form);
		}
		
	function mi_loc_lkup(my_form, commandType) {		   						// 7/5/10
		var streetInput, cityInput, stateInput;
		if(commandType == "gold") {
			streetInput = my_form.frm_gold_street;
			cityInput = my_form.frm_gold_city;
			stateInput = my_form.frm_gold_state;
			} else if(commandType == "silver") {
			streetInput = my_form.frm_silver_street;
			cityInput = my_form.frm_silver_city;
			stateInput = my_form.frm_silver_state;
			} else if(commandType == "bronze") {
			streetInput = my_form.frm_bronze_street;
			cityInput = my_form.frm_bronze_city;
			stateInput = my_form.frm_bronze_state;
			} else if(commandType == "level4") {
			streetInput = my_form.frm_level4_street;
			cityInput = my_form.frm_level4_city;
			stateInput = my_form.frm_level4_state;
			} else if(commandType == "level5") {
			streetInput = my_form.frm_level5_street;
			cityInput = my_form.frm_level5_city;
			stateInput = my_form.frm_level5_state;
			} else if(commandType == "level6") {
			streetInput = my_form.frm_level6_street;
			cityInput = my_form.frm_level6_city;
			stateInput = my_form.frm_level6_state;
			}			
		if(!$('map_canvas')) {return; }
		if(streetInput.value.trim() != "" && cityInput.value.trim() == "") {
			var theCity = cityInput.value.trim();
			var theStreet = "";
			} else {
			var theCity = cityInput.value.trim();
			var theStreet = streetInput.value.trim();
			}
		if (theCity == "" || cityInput.value.trim() == "") {
			alert ("City and State are required for location lookup.");
			return false;
			}
		var theState = stateInput.value.trim();
		var myAddress = theStreet + ", " + theCity + " " + theState;
		control.options.geocoder.geocode(myAddress, function(results) {
			if(!results[0]) {alert("Cannot find location");}
			var r = results[0]['center'];
			var theLat = r.lat;
			var theLng = r.lng;
			pt_to_map (my_form, commandType, theLat, theLng, myAddress);
			});
		}				// end function mi_loc_lkup()
		
	function get_building(id, theType, myForm) {
		if(id == 0) {return;}
		var randomnumber=Math.floor(Math.random()*99999999);
		var sessID = "<?php print $_SESSION['id'];?>";
		var url = './ajax/get_bldg_details.php?id='+id+'&version='+randomnumber+'&q='+sessID;
		sendRequest (url,bldgdetails_cb, "");		
		function bldgdetails_cb(req) {
			var bldg_arr = JSON.decode(req.responseText);
			var theName = bldg_arr[0];
			var theStreet = bldg_arr[1];
			var theCity = bldg_arr[2];
			var theState = bldg_arr[3];
			var theLat = bldg_arr[4];
			var theLng = bldg_arr[5];
			var theAddress = theStreet + ", " + theCity + ", " + theState;
			plot_bldg (myForm, theType, theLat, theLng, theAddress);
			return false;
			}
		}
		
	function plot_bldg(my_form, commandType, lat, lng, theAddress) {
		if(commandType == "gold") {
			iconNumber = 0;
			theSym = "G";
			theTitle = "<?php print get_text('Gold Command');?>";
			if(goldmarker) {map.removeLayer(goldmarker);}
			goldmarker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			goldmarker.addTo(map);
			} else if(commandType == "silver") {
			iconNumber = 1;
			theSym = "S";
			theTitle = "<?php print get_text('Silver Command');?>";
			if(silvermarker) {map.removeLayer(silvermarker);}
			silvermarker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			silvermarker.addTo(map);
			} else if(commandType == "bronze") {
			iconNumber = 2;
			theSym = "B";
			theTitle = "<?php print get_text('Bronze Command');?>";
			if(bronzemarker) {map.removeLayer(bronzemarker);}
			bronzemarker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			bronzemarker.addTo(map);
			} else if(commandType == "level4") {
			iconNumber = 2;
			theSym = "L4";
			theTitle = "<?php print get_text('Level 4 Command');?>";
			if(level4marker) {map.removeLayer(level4marker);}
			level4marker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			level4marker.addTo(map);
			} else if(commandType == "level5") {
			iconNumber = 2;
			theSym = "L5";
			theTitle = "<?php print get_text('Level 5 Command');?>";
			if(level5marker) {map.removeLayer(level5marker);}
			level5marker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			level5marker.addTo(map);
			} else if(commandType == "level6") {
			iconNumber = 2;
			theSym = "L6";
			theTitle = "<?php print get_text('Level 6 Command');?>";
			if(level6marker) {map.removeLayer(level6marker);}
			level6marker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			level6marker.addTo(map);
			}
		map.setView([lat, lng], 16);
		}
		
	function pt_to_map (my_form, commandType, lat, lng, theAddress) {
		if(!$('map_canvas')) {return; }
		var latInput, lngInput, iconNumber, theTitle, theSym;
		if(marker) {map.removeLayer(marker);}
		if(commandType == "gold") {
			my_form.frm_gold_lat.value=lat.toFixed(6);
			my_form.frm_gold_lng.value=lng.toFixed(6);
			iconNumber = 0;
			theTitle = "<?php print get_text('Gold Command');?>";
			theSym = "G";
			} else if(commandType == "silver") {
			my_form.frm_silver_lat.value=lat.toFixed(6);
			my_form.frm_silver_lng.value=lng.toFixed(6);
			iconNumber = 1;
			theSym = "S";
			theTitle = "<?php print get_text('Silver Command');?>";
			} else if(commandType == "bronze") {
			my_form.frm_bronze_lat.value=lat.toFixed(6);
			my_form.frm_bronze_lng.value=lng.toFixed(6);
			iconNumber = 2;
			theTitle = "<?php print get_text('Bronze Command');?>";
			theSym = "B";
			} else if(commandType == "level4") {
			my_form.frm_level4_lat.value=lat.toFixed(6);
			my_form.frm_level4_lng.value=lng.toFixed(6);
			iconNumber = 2;
			theTitle = "<?php print get_text('Level 4 Command');?>";
			theSym = "L4";
			} else if(commandType == "level5") {
			my_form.frm_level5_lat.value=lat.toFixed(6);
			my_form.frm_level5_lng.value=lng.toFixed(6);
			iconNumber = 2;
			theTitle = "<?php print get_text('Level 5 Command');?>";
			theSym = "L5";
			} else if(commandType == "level6") {
			my_form.frm_level6_lat.value=lat.toFixed(6);
			my_form.frm_level6_lng.value=lng.toFixed(6);
			iconNumber = 2;
			theTitle = "<?php print get_text('Level 6 Command');?>";
			theSym = "L6";
			}
		if(commandType == "gold") {
			if(goldmarker) {map.removeLayer(goldmarker);}
			goldmarker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			goldmarker.addTo(map);
			} else if(commandType == "silver") {
			if(silvermarker) {map.removeLayer(silvermarker);}
			silvermarker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			silvermarker.addTo(map);
			} else if(commandType == "bronze") {
			if(bronzemarker) {map.removeLayer(bronzemarker);}
			bronzemarker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			bronzemarker.addTo(map);
			} else if(commandType == "level4") {
			if(level4marker) {map.removeLayer(level4marker);}
			level4marker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			level4marker.addTo(map);
			} else if(commandType == "level5") {
			if(level5marker) {map.removeLayer(level5marker);}
			level5marker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			level5marker.addTo(map);
			} else if(commandType == "level6") {
			if(level6marker) {map.removeLayer(level6marker);}
			level6marker = createLocMarker(lat, lng, theAddress, iconNumber, "A", theSym, theAddress + theTitle);
			level6marker.addTo(map);
			}
		map.setView([lat, lng], 16);
		}				// end function pt_to_map ()
	
	function mymiclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
		document.mi_form.id.value=id;
		document.mi_form.view.value='true';
		document.mi_form.action='maj_inc.php';
		document.mi_form.submit();
		}
		
	function showtheDiv(theDiv) {
		$(theDiv).style.display = "inline-block";
		}
		
	function tidyDiv(theDiv, id, theButton) {
		if(id == 0) {
			$(theDiv).style.display = "inline-block";
			$(theButton).style.visibility = 'visible';
			} else {
			$(theDiv).style.display = "none";
			$(theButton).style.visibility = 'hidden';			
			}
		}
	
	function set_command_info(id, theDiv) {
		var email1 = (comm_arr[id]) ? comm_arr[id][4] : "";
		var email2 = (comm_arr[id]) ? comm_arr[id][5] : "";
		var phone1 = (comm_arr[id]) ? comm_arr[id][6] : "";
		var phone2 = (comm_arr[id]) ? comm_arr[id][7] : "";
		var theHTML = "<TABLE>";
		theHTML += "<TR>";
		theHTML += "<TD class='td_label'>Email 1</TD>";
		theHTML += "<TD class='td_data'>" + email1 + "</TD>";
		theHTML += "</TR><TR>";
		theHTML += "<TD class='td_label'>Email 2</TD>";
		theHTML += "<TD class='td_data'>" + email2 + "</TD>";
		theHTML += "</TR><TR>";
		theHTML += "<TD class='td_label'>Phone 1</TD>";
		theHTML += "<TD class='td_data'>" + phone1 + "</TD>";
		theHTML += "</TR><TR>";
		theHTML += "<TD class='td_label'>Phone 1</TD>";
		theHTML += "<TD class='td_data'>" + phone2 + "</TD>";
		theHTML += "</TR><TR></TABLE>";	
		$(theDiv).innerHTML = theHTML;
		$(theDiv).style.display = "block";
		}

	function get_new_colors() {
		window.location.href = '<?php print basename(__FILE__);?>';
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
		
	function createLocMarker(lat, lon, info, color, theid, sym, tip) {
		if((isFloat(lat)) && (isFloat(lon))) {
			var iconStr = sym;
			var iconurl = "./our_icons/gen_mi_icon.php?blank=" + escape(window.loc_icons[color]) + "&text=" + iconStr;
			icon = new baseHxIcon({iconUrl: iconurl});	
			var marker = L.marker([lat, lon], {icon: icon, title: tip, riseOnHover: true, riseOffset: 30000}).bindPopup(info).openPopup();
			marker.id = color;
			if(theid != "A") {
				lmarkers[theid] = marker;
				lmarkers[theid][lat] = lat;
				lmarkers[theid][lon] = lon;
				}
			var point = new L.LatLng(lat, lon);
			bounds.extend(point);
			if((dzf == 1) || (dzf == 3)) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
			return marker;
			} else {
			return false;
			}
		}		

	function createTicMarker(lat, lon, info, color, theid, sym, tip) {
		if((isFloat(lat)) && (isFloat(lon))) {
			var iconStr = sym;
			var iconurl = "./our_icons/gen_icon.php?blank=" + escape(window.icons[color]) + "&text=" + iconStr;	
			icon = new baseIcon({iconUrl: iconurl});	
			var marker = L.marker([lat, lon], {icon: icon, title: tip, riseOnHover: true, riseOffset: 30000}).bindPopup(info).openPopup();
			marker.id = color;
			tmarkers[theid] = marker;
			tmarkers[theid][lat] = lat;
			tmarkers[theid][lon] = lon;
			var point = new L.LatLng(lat, lon);
			bounds.extend(point);
			if((dzf == 1) || (dzf == 3)) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
			return marker;
			} else {
			return false;
			}
		}
		
	function createRespMarker(lat, lon, theid, sym, tip) {
		if((isFloat(lat)) && (isFloat(lon))) {
			var iconStr = sym;
			var iconurl = "./our_icons/gen_icon.php?blank=4&text=" + iconStr;	
			icon = new baseIcon({iconUrl: iconurl});
			var info = "Responder";
			var marker = L.marker([lat, lon], {icon: icon, title: tip, riseOnHover: true, riseOffset: 30000}).bindPopup(info).openPopup();
			rmarkers[theid] = marker;
			rmarkers[theid][lat] = lat;
			rmarkers[theid][lon] = lon;
			var point = new L.LatLng(lat, lon);
			bounds.extend(point);
			if((dzf == 1) || (dzf == 3)) {
				map_is_fixed = true;
				} else {
				map_is_fixed = false;
				}
			if(!map_is_fixed) {
				map.fitBounds(bounds);
				}
			return marker;
			} else {
			return false;
			}
		}

	var mi1_text = "<?php print get_text('ID');?>"; 
	var mi2_text = "<?php print get_text('Name');?>"; 
	var mi3_text = "<?php print get_text('Gold');?>"; 
	var mi4_text = "<?php print get_text('Silver');?>"; 
	var mi5_text = "<?php print get_text('Bronze');?>"; 
	var mi6_text = "<?php print get_text('Start');?>"; 
	var mi7_text = "<?php print get_text('End');?>";
	var mi8_text = "<?php print get_text('Status');?>";
	var mi9_text = "<?php print get_text('As of');?>"; 
	var changed_mi_sort = false;
	var mi_direct = "ASC";
	var mi_field = "id";
	var mi_id = "mi1";
	var mi_header = "<?php print get_text('ID');?>";

	function set_mi_headers(id, header_text, the_bull) {
		if(id == "mi1") {
			window.mi1_text = header_text + the_bull;
			window.mi2_text = "<?php print get_text('Name');?>";
			window.mi3_text = "<?php print get_text('Gold');?>";
			window.mi4_text = "<?php print get_text('Silver');?>";
			window.mi5_text = "<?php print get_text('Bronze');?>";
			window.mi6_text = "<?php print get_text('Start');?>";
			window.mi7_text = "<?php print get_text('End');?>";
			window.mi8_text = "<?php print get_text('Status');?>";
			window.mi9_text = "<?php print get_text('As of');?>";
			} else if(id == "mi2") {
			window.mi2_text = header_text + the_bull;
			window.mi1_text = "<?php print get_text('ID');?>";
			window.mi3_text = "<?php print get_text('Gold');?>";
			window.mi4_text = "<?php print get_text('Silver');?>";
			window.mi5_text = "<?php print get_text('Bronze');?>";
			window.mi6_text = "<?php print get_text('Start');?>";
			window.mi7_text = "<?php print get_text('End');?>";
			window.mi8_text = "<?php print get_text('Status');?>";
			window.mi9_text = "<?php print get_text('As of');?>";
			} else if(id == "mi3") {
			window.mi3_text = header_text + the_bull;
			window.mi1_text = "<?php print get_text('ID');?>";
			window.mi2_text = "<?php print get_text('Name');?>";
			window.mi4_text = "<?php print get_text('Silver');?>";
			window.mi5_text = "<?php print get_text('Bronze');?>";
			window.mi6_text = "<?php print get_text('Start');?>";
			window.mi7_text = "<?php print get_text('End');?>";
			window.mi8_text = "<?php print get_text('Status');?>";
			window.mi9_text = "<?php print get_text('As of');?>";
			} else if(id == "mi4") {
			window.mi4_text = header_text + the_bull;
			window.mi1_text = "<?php print get_text('ID');?>";
			window.mi2_text = "<?php print get_text('Name');?>";
			window.mi3_text = "<?php print get_text('Gold');?>";
			window.mi5_text = "<?php print get_text('Bronze');?>";
			window.mi6_text = "<?php print get_text('Start');?>";
			window.mi7_text = "<?php print get_text('End');?>";
			window.mi8_text = "<?php print get_text('Status');?>";
			window.mi9_text = "<?php print get_text('As of');?>";
			} else if(id == "mi5") {
			window.mi5_text = header_text + the_bull;
			window.mi1_text = "<?php print get_text('ID');?>";
			window.mi2_text = "<?php print get_text('Name');?>";
			window.mi3_text = "<?php print get_text('Gold');?>";
			window.mi4_text = "<?php print get_text('Silver');?>";
			window.mi6_text = "<?php print get_text('Start');?>";
			window.mi7_text = "<?php print get_text('End');?>";
			window.mi8_text = "<?php print get_text('Status');?>";
			window.mi9_text = "<?php print get_text('As of');?>";
			} else if(id == "mi6") {
			window.mi6_text = header_text + the_bull;
			window.mi1_text = "<?php print get_text('ID');?>";
			window.mi2_text = "<?php print get_text('Name');?>";
			window.mi3_text = "<?php print get_text('Gold');?>";
			window.mi4_text = "<?php print get_text('Silver');?>";
			window.mi5_text = "<?php print get_text('Bronze');?>";
			window.mi7_text = "<?php print get_text('End');?>";
			window.mi8_text = "<?php print get_text('Status');?>";
			window.mi9_text = "<?php print get_text('As of');?>";
			} else if(id == "mi7") {
			window.mi6_text = header_text + the_bull;
			window.mi1_text = "<?php print get_text('ID');?>";
			window.mi2_text = "<?php print get_text('Name');?>";
			window.mi3_text = "<?php print get_text('Gold');?>";
			window.mi4_text = "<?php print get_text('Silver');?>";
			window.mi5_text = "<?php print get_text('Bronze');?>";
			window.mi6_text = "<?php print get_text('Start');?>";
			window.mi8_text = "<?php print get_text('Status');?>";
			window.mi9_text = "<?php print get_text('As of');?>";
			} else if(id == "mi8") {
			window.mi6_text = header_text + the_bull;
			window.mi1_text = "<?php print get_text('ID');?>";
			window.mi2_text = "<?php print get_text('Name');?>";
			window.mi3_text = "<?php print get_text('Gold');?>";
			window.mi4_text = "<?php print get_text('Silver');?>";
			window.mi5_text = "<?php print get_text('Bronze');?>";
			window.mi6_text = "<?php print get_text('Start');?>";
			window.mi7_text = "<?php print get_text('End');?>";
			window.mi9_text = "<?php print get_text('As of');?>";
			} else if(id == "mi9") {
			window.mi6_text = header_text + the_bull;
			window.mi1_text = "<?php print get_text('ID');?>";
			window.mi2_text = "<?php print get_text('Name');?>";
			window.mi3_text = "<?php print get_text('Gold');?>";
			window.mi4_text = "<?php print get_text('Silver');?>";
			window.mi5_text = "<?php print get_text('Bronze');?>";
			window.mi6_text = "<?php print get_text('Start');?>";
			window.mi7_text = "<?php print get_text('End');?>";
			window.mi8_text = "<?php print get_text('Status');?>";
			}
		}
		
	function do_mi_sort(id, field, header_text) {
		window.changed_mi_sort = true;
		window.mi_last_display = 0;
		if(window.mi_field == field) {
			if(window.mi_direct == "ASC") {
				window.mi_direct = "DESC"; 
				var the_bull = "&#9660"; 
				window.mi_header = header_text;
				window.mi_field = field;
				set_mi_headers(id, header_text, the_bull);
				} else if(window.mi_direct == "DESC") { 
				window.mi_direct = "ASC"; 
				var the_bull = "&#9650"; 
				window.mi_header = header_text; 
				window.mi_field = field;
				set_mi_headers(id, header_text, the_bull);
				}
			} else {
			$(mi_id).innerHTML = mi_header;
			window.mi_field = field;
			window.mi_direct = "ASC";
			window.mi_id = id;
			window.mi_header = header_text;
			var the_bull = "&#9650";
			set_mi_headers(id, header_text, the_bull);
			}
		load_mi_list(field, mi_direct);
		return true;
		}

	function load_mi_list(sort, dir) {
		window.miFin = false;
		if(sort != window.mi_field) {
			window.mi_field = sort;
			}
		if(dir != window.mi_direct) {
			window.mi_direct = dir;
			}
		if($('the_milist').innerHTML == "") {
			$('the_milist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
			}
		var randomnumber=Math.floor(Math.random()*99999999);
		var sessID = "<?php print $_SESSION['id'];?>";
		var url = './ajax/mt_list.php?sort='+window.mi_field+'&dir='+ window.mi_direct+'&version='+randomnumber+'&q='+sessID;
		sendRequest (url,milist_cb, "");		
		function milist_cb(req) {
			var i = 1;
			var mi_number = 0;	
			var mi_arr = JSON.decode(req.responseText);
			if((mi_arr[0]) && (mi_arr[0][0] == 0)) {
				var outputtext = "<marquee direction='left' style='font-size: 2em; font-weight: bold;'>......No Major Incidents to view.........</marquee>";
				$('the_milist').innerHTML = outputtext;
				window.latest_mi = 0;
				} else {
				var outputtext = "<TABLE id='majorincidentstable' class='fixedheadscrolling scrollable' style='width: " + window.leftcolwidth + "px;'>";
				outputtext += "<thead>";
				outputtext += "<TR style='width: " + window.leftcolwidth + "px; background-color: #EFEFEF;'>";
				outputtext += "<TH id='mi1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('ID');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'id', '<?php print get_text('ID');?>')\">" + window.mi1_text + "</TH>";
				outputtext += "<TH id='mi2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Major Incident Name');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'name', '<?php print get_text('Name');?>')\">" + window.mi2_text + "</TH>";
				outputtext += "<TH id='mi3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Gold Command');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'gold', '<?php print get_text('Gold');?>')\">" + window.mi3_text + "</TH>";
				outputtext += "<TH id='mi4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Silver Command');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'silver', '<?php print get_text('Silver');?>')\">" + window.mi4_text + "</TH>";
				outputtext += "<TH id='mi5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Bronze Command');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'bronze', '<?php print get_text('Bronze');?>')\">" + window.mi5_text + "</TH>";
				outputtext += "<TH id='mi6' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Start Time');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'start', '<?php print get_text('Start');?>')\">" + window.mi6_text + "</TH>";
				outputtext += "<TH id='mi7' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('End Time');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'end', '<?php print get_text('End');?>')\">" + window.mi7_text + "</TH>";
				outputtext += "<TH id='mi8' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Status');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'end', '<?php print get_text('Status');?>')\">" + window.mi8_text + "</TH>";
				outputtext += "<TH id='mi9' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('<?php print get_tip('Updated');?>');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\" onClick=\"do_mi_sort(this.id, 'updated', '<?php print get_text('As of');?>')\">" + window.mi9_text + "</TH>";
				outputtext += "<TH id='mi10'>" + pad(5, " ", "\u00a0") + "</TH>";
				outputtext += "</TR>";
				outputtext += "</thead>";
				outputtext += "<tbody>";
				for(var key in mi_arr) {
					if(key != 0) {
						if(mi_arr[key][2]) {
							var mi_id = mi_arr[key][10];
							outputtext += "<TR id='" + mi_arr[key][10] + mi_id +"' CLASS='" + colors[i%2] +"' style='width: " + window.leftcolwidth + "px;'>";
							outputtext += "<TD class='plain_list text' onClick='mymiclick(" + mi_id + ");'>" + pad(6, mi_id, "\u00a0") + "</TD>";
							outputtext += "<TD class='plain_list text' onClick='mymiclick(" + mi_id + ");' style='color: " + mi_arr[key][13] + "; background-color: " + mi_arr[key][12] + ";'>" + mi_arr[key][0] + "</TD>";
							outputtext += "<TD class='plain_list text' onClick='mymiclick(" + mi_id + ");'>" + pad(15, mi_arr[key][6], "\u00a0") + "</TD>";
							outputtext += "<TD class='plain_list text' onClick='mymiclick(" + mi_id + ");'>" + pad(15, mi_arr[key][7], "\u00a0") + "</TD>";
							outputtext += "<TD class='plain_list text' onClick='mymiclick(" + mi_id + ");'>" + pad(15, mi_arr[key][8], "\u00a0") + "</TD>";
							var theStyle = (mi_arr[key][18] != "N/A") ? "style='background-color: red; color: #FFFFFF;'" : "";
							outputtext += "<TD class='plain_list text' " + theStyle + " onClick='mymiclick(" + mi_id + ");'>" + mi_arr[key][17] + "</TD>";
							outputtext += "<TD class='plain_list text' " + theStyle + " onClick='mymiclick(" + mi_id + ");'>" + mi_arr[key][18] + "</TD>";
							outputtext += "<TD class='plain_list text' " + theStyle + " onClick='mymiclick(" + mi_id + ");'>" + mi_arr[key][19] + "</TD>";
							outputtext += "<TD class='plain_list text' onClick='mymiclick(" + mi_id + ");'>" + mi_arr[key][3] + "</TD>";
							outputtext += "<TD>" + pad(5, " ", "\u00a0") + "</TD>";
							outputtext += "</TR>";
							if(window.mis_updated[mi_arr[key][10]]) {
								if(window.mis_updated[mi_arr[key][10]] != mi_arr[key][3]) {
									window.do_mi_update = true;
									} else {
									window.do_mi_update = false;
									}
								} else {
								window.mis_updated[mi_arr[key][10]] = mi_arr[key][3];
								window.do_mi_update = true;
								}
							mi_number = mi_id;
							var markup_arr = mi_arr[key][9];
							if(markup_arr) {
								if($('map_canvas')) {
									var theID = markup_arr['id'];
									var theLinename = markup_arr['name'];
									var theIdent = markup_arr['ident'];
									var theCategory = markup_arr['cat'];
									var theData = markup_arr['data'];
									var theColor = "#" + markup_arr['color'];
									var theOpacity = markup_arr['opacity'];
									var theWidth = markup_arr['width'];
									var theFilled = markup_arr['filled'];
									var theFillcolor = "#" + markup_arr['fill_color'];
									var theFillopacity = markup_arr['fill_opacity'];
									var theType = markup_arr['type'];
									if(theType == "p") {
										var polygon = draw_poly(theLinename, theCategory, theColor, theOpacity, theWidth, theFilled, theFillcolor, theFillopacity, theData, "basemarkup", theID);
										} else if(theType == "c") {
										var circle = drawCircle(theLinename, theData, theColor, theWidth, theOpacity, theFilled, theFillcolor, theFillopacity, "basemarkup", theID);
										} else if(theType == "t") {
										var banner = drawBanner(theLinename, theData, theWidth, theColor, "basemarkup", theID);
										}
									}
								}
							var goldloc = mi_arr[key][14];
							var silverloc = mi_arr[key][15];
							var bronzeloc = mi_arr[key][16];
							var mi_name = mi_arr[key][0];
							if($('map_canvas')) {
								if(goldloc[0] != 0) {
									var g_lmarker = createLocMarker(goldloc[2], goldloc[3], goldloc[1], 0, mi_id, mi_id, mi_name + ": <?php print get_text('Gold Command');?>");
									g_lmarker.addTo(map);	
									}
								if(goldloc[4] != "" && goldloc[5] != "" && goldloc[6] != "" && goldloc[7] != "" && goldloc[8] != "" ) {
									var g_lmarker = createLocMarker(goldloc[7], goldloc[8], goldloc[4] + " " + goldloc[5] + " " + goldloc[6], 0, mi_id, mi_id, mi_name + ": <?php print get_text('Gold Command');?>");
									g_lmarker.addTo(map);									
									}
								if(silverloc[0] != 0) {
									var s_lmarker = createLocMarker(silverloc[2], silverloc[3], silverloc[1], 1, mi_id, mi_id, mi_name + ": <?php print get_text('Silver Command');?>");
									s_lmarker.addTo(map);									
									}
								if(silverloc[4] != "" && silverloc[5] != "" && silverloc[6] != "" && silverloc[7] != "" && silverloc[8] != "" ) {
									var s_lmarker = createLocMarker(silverloc[7], silverloc[8], silverloc[4] + " " + silverloc[5] + " " + silverloc[6], 1, mi_id, mi_id, mi_name + ": <?php print get_text('Silver Command');?>");
									s_lmarker.addTo(map);									
									}
								if(bronzeloc[0] != 0) {
									var b_lmarker = createLocMarker(bronzeloc[2], bronzeloc[3], bronzeloc[1], 2, mi_id, mi_id, mi_name + ": <?php print get_text('Bronze Command');?>");
									b_lmarker.addTo(map);
									}
								if(bronzeloc[4] != "" && bronzeloc[5] != "" && bronzeloc[6] != "" && bronzeloc[7] != "" && bronzeloc[8] != "" ) {
									var b_lmarker = createLocMarker(bronzeloc[7], bronzeloc[8], bronzeloc[4] + " " + bronzeloc[5] + " " + bronzeloc[6], 2, mi_id, mi_id, mi_name + ": <?php print get_text('Bronze Command');?>");
									b_lmarker.addTo(map);									
									}
								}
							if(mi_arr[key][11]) {
								var tic_arr = mi_arr[key][11];
								if(tic_arr[key]) {
									if($('map_canvas')) {
										if(i == 1) {
											var thePoint = L.latLng(tic_arr[key][2],tic_arr[key][3]);
											window.bounds = L.latLngBounds(thePoint);
											}
										}
									}
								for (n = 0; n < tic_arr.length; n++) {
									var theTickid = tic_arr[n][0];
									var theScope = tic_arr[n][1];
									var theLat = tic_arr[n][2];
									var theLng = tic_arr[n][3];
									var theType = tic_arr[n][4];
									var theSeverity = tic_arr[n][5];
									if($('map_canvas')) {
										if((isFloat(theLat)) && (isFloat(theLng))) {
											var marker = createTicMarker(theLat, theLng, "Ticket: " + theScope + "<BR />Major Incident: " + mi_arr[key][0], theSeverity, theTickid, mi_id, theScope);
											marker.addTo(map);
											}
										}
									var theResp_arr = tic_arr[n][6];
									if(theResp_arr) {
										for(z = 0; z < theResp_arr.length; z++) {
											var resp_id = theResp_arr[z][0];
											var resp_handle = theResp_arr[z][1];
											var resp_lat = theResp_arr[z][2];
											var resp_lng = theResp_arr[z][3];
											if($('map_canvas')) {
												if((isFloat(resp_lat)) && (isFloat(resp_lng))) {
													var rmarker = createRespMarker(resp_lat, resp_lng, resp_id, mi_id, resp_handle)
													rmarker.addTo(map);
													}
												}
											}
										}
									}
								}
							}
						i++;
						}
					}
				outputtext += "</tbody>";
				outputtext += "</TABLE>";
				setTimeout(function() {
					if(window.mi_last_display == 0) {
						$('the_milist').innerHTML = outputtext;
						window.latest_mi = mi_number;
						} else {
						if((mi_number != window.latest_mi) || (window.do_mip_update == true) || (window.changed_mi_sort == true)) {
							$('the_milist').innerHTML = "";
							$('the_milist').innerHTML = outputtext;
							window.latest_mi = mi_number;
							}
						}
					var mitbl = document.getElementById('majorincidentstable');
					if(mitbl) {
						var headerRow = mitbl.rows[0];
						var tableRow = mitbl.rows[1];
						if(tableRow) {
//							alert(tableRow.cells.length);
							for (var i = 0; i < tableRow.cells.length; i++) {
								if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
//								alert(headerRow.cells[i].clientWidth + ", " + tableRow.cells[i].clientWidth);
								}
							if(getHeaderHeight(headerRow) >= 30) {
								var theRow = mitbl.insertRow(1);
								theRow.style.height = "20px";
								for (var i = 0; i < tableRow.cells.length; i++) {
									var theCell = theRow.insertCell(i);
									theCell.innerHTML = " ";
									}
								}
							} else {
							var cellwidthBase = window.leftcolwidth / 30;
							micell1 = cellwidthBase * 3;
							micell2 = cellwidthBase * 6;
							micell3 = cellwidthBase * 2;
							micell4 = cellwidthBase * 2;
							micell5 = cellwidthBase * 2;
							micell6 = cellwidthBase * 5;
							micell7 = cellwidthBase * 3;
							micell8 = cellwidthBase * 3;
							micell9 = cellwidthBase * 3;
							micell10 = cellwidthBase * 1;
							headerRow.cells[0].style.width = micell1 + "px";
							headerRow.cells[1].style.width = micell2 + "px";
							headerRow.cells[2].style.width = micell3 + "px";
							headerRow.cells[3].style.width = micell4 + "px";
							headerRow.cells[4].style.width = micell5 + "px";
							headerRow.cells[5].style.width = micell6 + "px";
							headerRow.cells[6].style.width = micell7 + "px";
							headerRow.cells[7].style.width = micell8 + "px";
							headerRow.cells[8].style.width = micell9 + "px";
							headerRow.cells[9].style.width = micell10 + "px";
							}
						}
					window.mi_last_display = mi_number;
					window.miFin = true;
					mi_list_get();
					},500);
				}
			}				// end function responderlist_cb()
		}				// end function load_responderlist()

	function isViewable(element){
		return (element.clientHeight > 0);
		}
		
	function mi_list_setwidths() {
		var viewableRow = 1;
		var mitbl = document.getElementById('majorincidentstable');
		var headerRow = mitbl.rows[0];
		for (i = 1; i < mitbl.rows.length; i++) {
			if(!isViewable(mitbl.rows[i])) {
				} else {
				viewableRow = i;
				break;
				}
			}
		var tableRow = mitbl.rows[viewableRow];
		if(tableRow) {
			for (var i = 0; i < tableRow.cells.length; i++) {
				if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
				}
			if(getHeaderHeight(headerRow) >= 30) {
				var theRow = mitbl.insertRow(1);
				theRow.style.height = "20px";
				for (var i = 0; i < tableRow.cells.length; i++) {
					var theCell = theRow.insertCell(i);
					theCell.innerHTML = " ";
					}
				}
			} else {
			var cellwidthBase = window.leftcolwidth / 28;
			micell1 = cellwidthBase * 3;
			micell2 = cellwidthBase * 6;
			micell3 = cellwidthBase * 2;
			micell4 = cellwidthBase * 2;
			micell5 = cellwidthBase * 2;
			micell6 = cellwidthBase * 5;
			micell7 = cellwidthBase * 3;
			micell8 = cellwidthBase * 3;
			micell9 = cellwidthBase * 3;
			micell10 = cellwidthBase * 1;
			headerRow.cells[0].style.width = micell1 + "px";
			headerRow.cells[1].style.width = micell2 + "px";
			headerRow.cells[2].style.width = micell3 + "px";
			headerRow.cells[3].style.width = micell4 + "px";
			headerRow.cells[4].style.width = micell5 + "px";
			headerRow.cells[5].style.width = micell6 + "px";
			headerRow.cells[6].style.width = micell7 + "px";
			headerRow.cells[7].style.width = micell8 + "px";
			headerRow.cells[8].style.width = micell9 + "px";
			headerRow.cells[9].style.width = micell10 + "px";
			}
		}
		
	function mi_list_get() {
		if (mi_interval!=null) {return;}
		mi_interval = window.setInterval('mi_list_loop()', 60000); 
		}			// end function mu get()

	function mi_list_loop() {
		load_mi_list(mi_field, mi_direct);
		}			// end function do_loop()
		

	</SCRIPT>


<?php
	$_postmap_clear = 	(array_key_exists ('frm_clr_pos',$_POST ))? 	$_POST['frm_clr_pos']: "";	// 11/19/09
	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? 		$_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? 			$_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? 			$_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? 				$_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? 				$_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? 			$_GET['view']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Responder - checkbox - 8/12/09
		$query = "DELETE FROM $GLOBALS[mysql_prefix]major_incidents WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Unit <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		} else {
		if ($_getgoedit == 'true') {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `id` = " . $_POST['frm_id'];
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			$current_status = $row['mi_status'];
			$current_end = $row['inc_endtime'];
			if(!array_key_exists('frm_year_inc_endtime', $_POST) && $current_status == "Closed") {
				$frm_miend = "0000-00-00 00:00:00";
				$status = "Open";
				} else {
				$frm_miend = (array_key_exists('frm_year_inc_endtime', $_POST)) ? "$_POST[frm_year_inc_endtime]-$_POST[frm_month_inc_endtime]-$_POST[frm_day_inc_endtime] $_POST[frm_hour_inc_endtime]:$_POST[frm_minute_inc_endtime]:00" : NULL;
				$status = (array_key_exists('frm_status', $_POST) && $_POST['frm_status'] == "Open" || $_POST['frm_status'] == "Closed") ? $_POST['frm_status'] : "Open";
				$status = ($frm_miend != NULL && $frm_miend != "0000-00-00 00:00:00") ? "Closed" : $status;
				}
			$frm_mistart = "$_POST[frm_year_inc_startime]-$_POST[frm_month_inc_startime]-$_POST[frm_day_inc_startime] $_POST[frm_hour_inc_startime]:$_POST[frm_minute_inc_startime]:00";
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));		
			$mi_id = $_POST['frm_id'];
			$by = $_SESSION['user_id'];
			$from = $_SERVER['REMOTE_ADDR'];
			$incs_arr = (isset($_POST['frm_inc'])) ? $_POST['frm_inc'] : array();

			if(quote_smart(trim($_POST['frm_gold_loc'])) != 0) {
				$_POST['frm_gold_street'] = $_POST['frm_gold_city'] = $_POST['frm_gold_state'] = $_POST['frm_gold_lat'] = $_POST['frm_gold_lng'] = "";
				}
			if(quote_smart(trim($_POST['frm_silver_loc'])) != 0) {
				$_POST['frm_silver_street'] = $_POST['frm_silver_city'] = $_POST['frm_silver_state'] = $_POST['frm_silver_lat'] = $_POST['frm_silver_lng'] = "";
				}
			if(quote_smart(trim($_POST['frm_bronze_loc'])) != 0) {
				$_POST['frm_bronze_street'] = $_POST['frm_bronze_city'] = $_POST['frm_bronze_state'] = $_POST['frm_bronze_lat'] = $_POST['frm_bronze_lng'] = "";
				}
			if(quote_smart(trim($_POST['frm_level4_loc'])) != 0) {
				$_POST['frm_level4_street'] = $_POST['frm_level4_city'] = $_POST['frm_level4_state'] = $_POST['frm_level4_lat'] = $_POST['frm_level4_lng'] = "";
				}
			if(quote_smart(trim($_POST['frm_level5_loc'])) != 0) {
				$_POST['frm_level5_street'] = $_POST['frm_level5_city'] = $_POST['frm_level5_state'] = $_POST['frm_level5_lat'] = $_POST['frm_level5_lng'] = "";
				}
			if(quote_smart(trim($_POST['frm_level6_loc'])) != 0) {
				$_POST['frm_level6_street'] = $_POST['frm_level6_city'] = $_POST['frm_level6_state'] = $_POST['frm_level6_lat'] = $_POST['frm_level6_lng'] = "";
				}
			if($_POST['frm_gold_street'] == "") {
				$_POST['frm_gold_city'] = $_POST['frm_gold_state'] = $_POST['frm_gold_lat'] = $_POST['frm_gold_lng'] = "";
				}
			if($_POST['frm_silver_street'] == "") {
				$_POST['frm_silver_city'] = $_POST['frm_silver_state'] = $_POST['frm_silver_lat'] = $_POST['frm_silver_lng'] = "";
				}
			if($_POST['frm_bronze_street'] == "") {
				$_POST['frm_bronze_city'] = $_POST['frm_bronze_state'] = $_POST['frm_bronze_lat'] = $_POST['frm_bronze_lng'] = "";
				}
			if($_POST['frm_level4_street'] == "") {
				$_POST['frm_level4_city'] = $_POST['frm_level4_state'] = $_POST['frm_level4_lat'] = $_POST['frm_level4_lng'] = "";
				}
			if($_POST['frm_level5_street'] == "") {
				$_POST['frm_level5_city'] = $_POST['frm_level5_state'] = $_POST['frm_level5_lat'] = $_POST['frm_level5_lng'] = "";
				}
			if($_POST['frm_level6_street'] == "") {
				$_POST['frm_level6_city'] = $_POST['frm_level6_state'] = $_POST['frm_level6_lat'] = $_POST['frm_level6_lng'] = "";
				}
			$query = "UPDATE `$GLOBALS[mysql_prefix]major_incidents` SET
				`name`= " . 			quote_smart(trim($_POST['frm_name'])) . ",
				`description`= " . 		quote_smart(trim($_POST['frm_descr'])) . ",
				`type`= " . 			quote_smart(trim($_POST['frm_type'])) . ",
				`mi_status`= " . 		quote_smart(trim($status)) . ",
				`gold`= " . 			quote_smart(trim($_POST['frm_gold'])) . ",
				`silver`= " . 			quote_smart(trim($_POST['frm_silver'])) . ",
				`bronze`= " . 			quote_smart(trim($_POST['frm_bronze'])) . ",
				`level4`= " . 			quote_smart(trim($_POST['frm_level4'])) . ",
				`level5`= " . 			quote_smart(trim($_POST['frm_level5'])) . ",
				`level6`= " . 			quote_smart(trim($_POST['frm_level6'])) . ",
				`gold_loc`= " . 		quote_smart(trim($_POST['frm_gold_loc'])) . ",
				`gold_street`= " . 		quote_smart(trim($_POST['frm_gold_street'])) . ",
				`gold_city`= " . 		quote_smart(trim($_POST['frm_gold_city'])) . ",
				`gold_state`= " . 		quote_smart(trim($_POST['frm_gold_state'])) . ",
				`gold_lat`= " . 		quote_smart(trim($_POST['frm_gold_lat'])) . ",
				`gold_lng`= " . 		quote_smart(trim($_POST['frm_gold_lng'])) . ",
				`silver_loc`= " . 		quote_smart(trim($_POST['frm_silver_loc'])) . ",
				`silver_street`= " . 	quote_smart(trim($_POST['frm_silver_street'])) . ",
				`silver_city`= " . 		quote_smart(trim($_POST['frm_silver_city'])) . ",
				`silver_state`= " . 	quote_smart(trim($_POST['frm_silver_state'])) . ",
				`silver_lat`= " . 		quote_smart(trim($_POST['frm_silver_lat'])) . ",
				`silver_lng`= " . 		quote_smart(trim($_POST['frm_silver_lng'])) . ",
				`bronze_loc`= " . 		quote_smart(trim($_POST['frm_bronze_loc'])) . ",
				`bronze_street`= " . 	quote_smart(trim($_POST['frm_bronze_street'])) . ",
				`bronze_city`= " . 		quote_smart(trim($_POST['frm_bronze_city'])) . ",
				`bronze_state`= " . 	quote_smart(trim($_POST['frm_bronze_state'])) . ",
				`bronze_lat`= " . 		quote_smart(trim($_POST['frm_bronze_lat'])) . ",
				`bronze_lng`= " . 		quote_smart(trim($_POST['frm_bronze_lng'])) . ",
				`level4_loc`= " . 		quote_smart(trim($_POST['frm_level4_loc'])) . ",
				`level4_street`= " . 	quote_smart(trim($_POST['frm_level4_street'])) . ",
				`level4_city`= " . 		quote_smart(trim($_POST['frm_level4_city'])) . ",
				`level4_state`= " . 	quote_smart(trim($_POST['frm_level4_state'])) . ",
				`level4_lat`= " . 		quote_smart(trim($_POST['frm_level4_lat'])) . ",
				`level4_lng`= " . 		quote_smart(trim($_POST['frm_level4_lng'])) . ",
				`level5_loc`= " . 		quote_smart(trim($_POST['frm_level5_loc'])) . ",
				`level5_street`= " . 	quote_smart(trim($_POST['frm_level5_street'])) . ",
				`level5_city`= " . 		quote_smart(trim($_POST['frm_level5_city'])) . ",
				`level5_state`= " . 	quote_smart(trim($_POST['frm_level5_state'])) . ",
				`level5_lat`= " . 		quote_smart(trim($_POST['frm_level5_lat'])) . ",
				`level5_lng`= " . 		quote_smart(trim($_POST['frm_level5_lng'])) . ",
				`level6_loc`= " . 		quote_smart(trim($_POST['frm_level6_loc'])) . ",
				`level6_street`= " . 	quote_smart(trim($_POST['frm_level6_street'])) . ",
				`level6_city`= " . 		quote_smart(trim($_POST['frm_level6_city'])) . ",
				`level6_state`= " . 	quote_smart(trim($_POST['frm_level6_state'])) . ",
				`level6_lat`= " . 		quote_smart(trim($_POST['frm_level6_lat'])) . ",
				`level6_lng`= " . 		quote_smart(trim($_POST['frm_level6_lng'])) . ",
				`boundary`= " . 		quote_smart(trim($_POST['frm_boundary'])) . ",
				`inc_startime`=".		quote_smart(trim($frm_mistart)) . ",
				`inc_endtime`=".		quote_smart(trim($frm_miend)) . ",
				`incident_notes`= " . 	quote_smart(trim($_POST['frm_notes'])) . ",
				`_by`= " . 		$by . ",
				`_on`= '" . 	$now . "',
				`_from`= '" . $from . "'
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
			
			$existing_incs = array();
			$query_x = "SELECT * FROM `$GLOBALS[mysql_prefix]mi_x` WHERE `mi_id` = " . $mi_id . " ORDER BY `id`;";
			$result_x = mysql_query($query_x) or do_error($query_x, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			while ($row_x = stripslashes_deep(mysql_fetch_assoc($result_x))) {
				$existing_incs[] = $row_x['ticket_id'];
				}
				
			if(isset($_POST['frm_inc'])) {
				foreach($_POST['frm_inc'] AS $val) {
					if(!in_array($val, $existing_incs, TRUE)) {
						$query  = "INSERT INTO `$GLOBALS[mysql_prefix]mi_x` (`mi_id`, `ticket_id`) VALUES ($mi_id, $val)";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				}
			foreach($existing_incs AS $val) {
				if(!in_array($val, $incs_arr, TRUE)) {
					$query  = "DELETE FROM `$GLOBALS[mysql_prefix]mi_x` WHERE `mi_id` = $mi_id AND `ticket_id` = $val";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
					}
				}

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
						`title` ,
						`filename` ,
						`orig_filename`,
						`ticket_id`,
						`responder_id`,
						`facility_id`,
						`mi_id`,
						`type`,
						`filetype`,
						`_by`,
						`_on`,
						`_from`
					) VALUES (
						'" . $_POST['frm_file_title'] . "',
						'" . $filename . "',
						'" . $realfilename . "',
						0,
						0,
						0,
						" . $mi_id . ",
						0,
						'" . $_FILES['frm_file']['type'] . "',
						$by,
						'" . $now . "',
						'" . $from . "')";
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
			
			$caption = "<B>Major Incident<i> " . stripslashes_deep($_POST['frm_name']) . "</i>' data has been updated </B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$frm_mistart = "$_POST[frm_year_inc_startime]-$_POST[frm_month_inc_startime]-$_POST[frm_day_inc_startime] $_POST[frm_hour_inc_startime]:$_POST[frm_minute_inc_startime]:00";
		$frm_miend  = (array_key_exists('frm_year_inc_endtime', $_POST)) ? quote_smart("$_POST[frm_year_inc_endtime]-$_POST[frm_month_inc_endtime]-$_POST[frm_day_inc_endtime] $_POST[frm_hour_inc_endtime]:$_POST[frm_minute_inc_endtime]:00") : "NULL";
		$by = $_SESSION['user_id'];
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$from = $_SERVER['REMOTE_ADDR'];
		$status = (array_key_exists('frm_status', $_POST) && $_POST['frm_status'] == "Open" || $_POST['frm_status'] == "Closed") ? $_POST['frm_status'] : "Open";
		$incs_arr = (isset($_POST['frm_inc'])) ? $_POST['frm_inc'] : array();
		$gold_loc = (isset($_POST['frm_gold_loc'])) ? $_POST['frm_gold_loc'] : 0;
		$silver_loc = (isset($_POST['frm_silver_loc'])) ? $_POST['frm_silver_loc'] : 0;
		$bronze_loc = (isset($_POST['frm_bronze_loc'])) ? $_POST['frm_bronze_loc'] : 0;
		$level4_loc = (isset($_POST['frm_level4_loc'])) ? $_POST['frm_level4_loc'] : 0;
		$level5_loc = (isset($_POST['frm_level5_loc'])) ? $_POST['frm_level5_loc'] : 0;
		$level6_loc = (isset($_POST['frm_level6_loc'])) ? $_POST['frm_level6_loc'] : 0;
		$goldStreet = ($gold_loc == 0) ? $_POST['frm_gold_street'] : "";
		$goldCity = ($gold_loc == 0) ? $_POST['frm_gold_city'] : "";
		$goldState = ($gold_loc == 0) ? $_POST['frm_gold_state'] : "";
		$goldLat = ($gold_loc == 0) ? $_POST['frm_gold_lat'] : "";
		$goldLng = ($gold_loc == 0) ? $_POST['frm_gold_lng'] : "";
		$silverStreet = ($silver_loc == 0) ? $_POST['frm_silver_street'] : "";
		$silverCity = ($silver_loc == 0) ? $_POST['frm_silver_city'] : "";
		$silverState = ($silver_loc == 0) ? $_POST['frm_silver_state'] : "";
		$silverLat = ($silver_loc == 0) ? $_POST['frm_silver_lat'] : "";
		$silverLng = ($silver_loc == 0) ? $_POST['frm_silver_lng'] : "";
		$bronzeStreet = ($bronze_loc == 0) ? $_POST['frm_bronze_street'] : "";
		$bronzeCity = ($bronze_loc == 0) ? $_POST['frm_bronze_city'] : "";
		$bronzeState = ($bronze_loc == 0) ? $_POST['frm_bronze_state'] : "";
		$bronzeLat = ($bronze_loc == 0) ? $_POST['frm_bronze_lat'] : "";
		$bronzeLng = ($bronze_loc == 0) ? $_POST['frm_bronze_lng'] : "";
		$level4Street = ($level4_loc == 0) ? $_POST['frm_level4_street'] : "";
		$level4City = ($level4_loc == 0) ? $_POST['frm_level4_city'] : "";
		$level4State = ($level4_loc == 0) ? $_POST['frm_level4_state'] : "";
		$level4Lat = ($level4_loc == 0) ? $_POST['frm_level4_lat'] : "";
		$level4Lng = ($level4_loc == 0) ? $_POST['frm_level4_lng'] : "";
		$level5Street = ($level5_loc == 0) ? $_POST['frm_level5_street'] : "";
		$level5City = ($level5_loc == 0) ? $_POST['frm_level5_city'] : "";
		$level5State = ($level5_loc == 0) ? $_POST['frm_level5_state'] : "";
		$level5Lat = ($level5_loc == 0) ? $_POST['frm_level5_lat'] : "";
		$level5Lng = ($level5_loc == 0) ? $_POST['frm_level5_lng'] : "";
		$level6Street = ($level6_loc == 0) ? $_POST['frm_level6_street'] : "";
		$level6City = ($level6_loc == 0) ? $_POST['frm_level6_city'] : "";
		$level6State = ($level6_loc == 0) ? $_POST['frm_level6_state'] : "";
		$level6Lat = ($level6_loc == 0) ? $_POST['frm_level6_lat'] : "";
		$level6Lng = ($level6_loc == 0) ? $_POST['frm_level6_lng'] : "";
		if($goldStreet == "") {
			$goldCity = $goldState = $goldLat = $goldLng = "";
			}
		if($silverStreet == "") {
			$silverCity = $silverState = $silverLat = $silverLng = "";
			}
		if($bronzeStreet == "") {
			$bronzeCity = $bronzeState = $bronzeLat = $bronzeLng = "";
			}
		if($level4Street == "") {
			$level4City = $level4State = $level4Lat = $level4Lng = "";
			}
		if($level5Street == "") {
			$level5City = $level5State = $level5Lat = $level5Lng = "";
			}
		if($level6Street == "") {
			$level6City = $level6State = $level6Lat = $level6Lng = "";
			}
		
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]major_incidents` 
				(`name`, 
				`description`, 
				`type`,
				`mi_status`,
				`gold`, 
				`silver`, 
				`bronze`, 
				`level4`,
				`level5`,
				`level6`,
				`gold_loc`, 
				`gold_street`, 
				`gold_city`, 
				`gold_state`, 
				`gold_lat`, 
				`gold_lng`, 
				`silver_loc`, 
				`silver_street`, 
				`silver_city`, 
				`silver_state`, 
				`silver_lat`, 
				`silver_lng`, 				
				`bronze_loc`, 
				`bronze_street`, 
				`bronze_city`, 
				`bronze_state`, 
				`bronze_lat`, 
				`bronze_lng`,
				`level4_loc`, 
				`level4_street`, 
				`level4_city`, 
				`level4_state`, 
				`level4_lat`, 
				`level4_lng`,
				`level5_loc`, 
				`level5_street`, 
				`level5_city`, 
				`level5_state`, 
				`level5_lat`, 
				`level5_lng`,
				`level6_loc`, 
				`level6_street`, 
				`level6_city`, 
				`level6_state`, 
				`level6_lat`, 
				`level6_lng`, 					
				`boundary`,
				`inc_startime`,
				`inc_endtime`,
				`incident_notes`,
				`_by`,
				`_on`,
				`_from` )
			VALUES (" .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_descr'])) . "," .
				quote_smart(trim($_POST['frm_type'])) . "," .
				quote_smart(trim($status)) . "," .
				quote_smart(trim($_POST['frm_gold'])) . "," .
				quote_smart(trim($_POST['frm_silver'])) . "," .
				quote_smart(trim($_POST['frm_bronze'])) . "," .
				quote_smart(trim($_POST['frm_level4'])) . "," .
				quote_smart(trim($_POST['frm_level5'])) . "," .
				quote_smart(trim($_POST['frm_level6'])) . "," .
				quote_smart(trim($gold_loc)) . "," .
				quote_smart(trim($goldStreet)) . "," .
				quote_smart(trim($goldCity)) . "," .
				quote_smart(trim($goldState)) . "," .
				quote_smart(trim($goldLat)) . "," .
				quote_smart(trim($goldLng)) . "," .		
				quote_smart(trim($silver_loc)) . "," .
				quote_smart(trim($silverStreet)) . "," .
				quote_smart(trim($silverCity)) . "," .
				quote_smart(trim($silverState)) . "," .
				quote_smart(trim($silverLat)) . "," .
				quote_smart(trim($silverLng)) . "," .
				quote_smart(trim($bronze_loc)) . "," .
				quote_smart(trim($bronzeStreet)) . "," .
				quote_smart(trim($bronzeCity)) . "," .
				quote_smart(trim($bronzeState)) . "," .
				quote_smart(trim($bronzeLat)) . "," .
				quote_smart(trim($bronzeLng)) . "," .
				quote_smart(trim($level4_loc)) . "," .
				quote_smart(trim($level4Street)) . "," .
				quote_smart(trim($level4City)) . "," .
				quote_smart(trim($level4State)) . "," .
				quote_smart(trim($level4Lat)) . "," .
				quote_smart(trim($level4Lng)) . "," .
				quote_smart(trim($level5_loc)) . "," .
				quote_smart(trim($level5Street)) . "," .
				quote_smart(trim($level5City)) . "," .
				quote_smart(trim($level5State)) . "," .
				quote_smart(trim($level5Lat)) . "," .
				quote_smart(trim($level5Lng)) . "," .
				quote_smart(trim($level6_loc)) . "," .
				quote_smart(trim($level6Street)) . "," .
				quote_smart(trim($level6City)) . "," .
				quote_smart(trim($level6State)) . "," .
				quote_smart(trim($level6Lat)) . "," .
				quote_smart(trim($level6Lng)) . "," .
				quote_smart(trim($_POST['frm_boundary'])) . "," .
				quote_smart(trim($frm_mistart)) . "," .
				quote_smart(trim($frm_miend)) . "," .
				quote_smart(trim($_POST['frm_notes'])) . "," .
				quote_smart(trim($_SESSION['user_id'])) . "," .
				quote_smart(trim($now)) . "," .
				quote_smart(trim($from)) . ");";

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
						`title` ,
						`filename` ,
						`orig_filename`,
						`ticket_id`,
						`responder_id`,
						`facility_id`,
						`mi_id`,
						`type`,
						`filetype`,
						`_by`,
						`_on`,
						`_from`
					) VALUES (
						'" . $_POST['frm_file_title'] . "',
						'" . $filename . "',
						'" . $realfilename . "',
						0,
						0,
						0,
						" . $new_id . ",
						0,
						'" . $_FILES['frm_file']['type'] . "',
						$by,
						'" . $now . "',
						'" . $from . "')";
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
		
		foreach($incs_arr AS $val) {
			$query  = "INSERT INTO `$GLOBALS[mysql_prefix]mi_x` (`mi_id`, `ticket_id`) VALUES ($new_id, $val)";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
			}

		$caption = "<B>Major Incident<i> " . stripslashes_deep($_POST['frm_name']) . "</i>' has been created </B><BR /><BR />";
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
// add ===========================================================================================================================
// add ===========================================================================================================================

	if ($_getadd == 'true') {
		require_once('./incs/links.inc.php');
		if (!($_SESSION['internet'])) {
			require_once('./forms/mi_add_screen_NM.php');			
			} else {
			require_once('./forms/mi_add_screen.php');
			}
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($_getedit == 'true') {
		require_once('./incs/links.inc.php');
		if (!($_SESSION['internet'])) {
			require_once('./forms/mi_edit_screen_NM.php');			
			} else {
			require_once('./forms/mi_edit_screen.php');
			}
		exit();
		}		// end if ($_GET['edit'])
// =================================================================================================================
// view =================================================================================================================

	if ($_getview == 'true') {
		require_once('./incs/links.inc.php');
		if (!($_SESSION['internet'])) {
			require_once('./forms/mi_view_screen_NM.php');			
			} else {
			require_once('./forms/mi_view_screen.php');
			}
		exit();
		}
// ============================================= initial display =======================
	if (!isset($mapmode)) {$mapmode="a";}
	require_once('./incs/links.inc.php');
	if (!($_SESSION['internet'])) {
		require_once('./forms/mi_screen_NM.php');			
		} else {
		require_once('./forms/mi_screen.php');
		}
	exit();
?>
