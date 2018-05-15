<?php
$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$iw_width = 	"300px";		// map infowindow with
$col_width= max(320, intval($_SESSION['scr_width']* 0.45));
$zoom_tight = false;
$get_print = (array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
$get_id = (array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
$tick_id = mysql_real_escape_string($get_id);
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$theNotice = (isset($theNotice)) ? $theNotice : "";
$mode = (isset($mode)) ? $mode: 0;
$https = (array_key_exists('HTTPS', $_SERVER)) ? 1 : 0;

$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
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
<?php 

if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
	require_once('./incs/modules.inc.php');
	}
?>	
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
var tmarkers = [];	//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var fmarkers = [];			//	Responder Markers array
var mapWidth;
var mapHeight;
var listHeight;
var listwidth;
var viewportwidth;
var viewportheight;
var colwidth;
var colheight;
var outerwidth;
var outerheight;
var r_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = new Array();
var colors = new Array ('odd', 'even');
var bounds;
var zoom;
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
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .65;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('leftTable').style.width = colwidth + "px";	
	$('left').style.width = colwidth + "px";
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	set_fontsizes(viewportwidth, "fullscreen");
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
	
function find_warnings(tick_lat, tick_lng) {	//	9/10/13
	randomnumber=Math.floor(Math.random()*99999999);
	var theurl ="./ajax/loc_warn_list.php?version=" + randomnumber + "&lat=" + tick_lat + "&lng=" + tick_lng;
	sendRequest(theurl, loc_w, "");	//	11/14/13
	function loc_w(req) {
		var the_warnings=JSON.decode(req.responseText);
		var the_count = the_warnings[0];
		if(the_count != 0) {
			$('loc_warnings').innerHTML = the_warnings[1];
			$('loc_warnings').style.display = 'block';
			}
		}			
	}
</SCRIPT>
</HEAD>
<?php
if($mode == 0) {
?>
	<BODY onLoad = "set_size(); ck_frames(); location.href = '#top';">
	<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
<?php
	} else {
?>
	<BODY onLoad = "set_size(); location.href = '#top';">
	<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
	<DIV id='button_bar' class='but_container'>
		<SPAN id='can_but' roll='button' aria-label='Cancel' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
	</DIV>
<?php
	}
?>

<DIV id = "outer" style='position: absolute; left: 0px; top: 70px; width: 90%;'>
	<DIV id='button_bar' class='but_container' style='text-align: left; position: fixed; top: 60px;'>
	<?php print add_header($tick_id, TRUE, TRUE);?>
	</DIV>
	<DIV id = "leftcol" style='position: relative; top: 70px; left: 30px; float: left;'>

<?php

	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#{$tick_id})</I>" : "";			// 1/25/09, 2/18/12
	$un_stat_cats = get_all_categories();
	$istest = FALSE;
	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}

	if ($tick_id == '' OR $tick_id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$tick_id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$tick_id'<BR />";
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
		`rf`.`street` AS `rec_fac_street`,
		`rf`.`city` AS `rec_fac_city`,
		`rf`.`state` AS `rec_fac_state`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $tick_id $restrict_ticket";


	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){
		print "<FONT CLASS=\"warn\">Internal error " . basename(__FILE__) ."/" .  __LINE__  .".  Notify developers of this message.</FONT>";
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_array($result));
	$type = get_type($row['in_types_id']);
	$severity = $row['severity'];
	$scope = $row['scope'];
	$locale = get_variable('locale');    // 10/29/09
	switch($locale) {
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($row['lat'], $row['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($row['lat'], $row['lng']);
		break;

		case "2":
		$coords =  $row['lat'] . "," . $row['lng'];
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}


?>
	<DIV style='width: 100%;'><?php print $theNotice;?></DIV>;
	<TABLE id='leftTable' style='width: <?php print $col_width;?>; border: 1px solid #707070;'>
	<TR VALIGN="top" style='width: 100%;'><TD CLASS="print_TD, even" ALIGN="left" style='width: 100%;'>
	<DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 100%; font-size: 1.5em; font-weight: bold; border: 2px outset #707070;'></DIV><BR />	

<?php

	print do_ticket_only($row, $col_width, FALSE) ;
	print show_actions($row['tick_id'], "date", FALSE, FALSE, 1);
	print "</TD></TR></TABLE>\n";	
	$lat = $row['lat']; $lng = $row['lng'];
?>
	</DIV>
	<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
	</DIV>
	<DIV id='rightcol' style='position: relative; top: 70px;  left: 40px; float: left;'>
		<DIV ID='map_canvas' style='border: 1px outset #707070; z-index: 2'></DIV>
	</DIV>
<SCRIPT>
var map;
var minimap;
var latLng;
var mapWidth = <?php print get_variable('map_width');?>+20;
var mapHeight = <?php print get_variable('map_height');?>+20;;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var theLat = "<?php print $lat;?>";
var theLng = "<?php print $lng;?>";
find_warnings(theLat, theLng);
init_map(1, theLat, theLng, "", 13, theLocale, useOSMAP, "tr");
var bounds = map.getBounds();
var zoom = map.getZoom();
var i = 0;
<?php
do_kml();
?>
</SCRIPT>
<?php
	if ((($lat == $GLOBALS['NM_LAT_VAL']) && ($lng == $GLOBALS['NM_LAT_VAL'])) || (($lat == "") || ($lat == NULL)) || (($lng == "") || ($lng == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
		$lat = get_variable('def_lat'); $lng = get_variable('def_lng');
		$icon_file = "./our_icons/question1.png";
		}
	else {
		$icon_file = "./markers/crosshair.png";
		}

// ====================================Add Facilities to Map 8/1/09================================================
	$query_fac = "SELECT *,`updated` AS `updated`, 
		`$GLOBALS[mysql_prefix]facilities`.id AS fac_id, 
		`$GLOBALS[mysql_prefix]facilities`.description AS facility_description, 
		`$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, 
		`$GLOBALS[mysql_prefix]fac_types`.icon AS type_icon, 
		`$GLOBALS[mysql_prefix]facilities`.name AS facility_name 
		FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	while($row_fac = mysql_fetch_array($result_fac)){

		$fac_name = $row_fac['facility_name'];			//	10/8/09
		$fac_temp = explode("/", $fac_name );
		$fac_index = substr($fac_temp[count($fac_temp) -1] , -6, strlen($fac_temp[count($fac_temp) -1]));	// 3/19/11
		$icon_str = $row_fac['icon_str'];
		$fac_id=($row_fac['id']);
		$fac_type=($row_fac['type_icon']);

		$f_disp_name = $row_fac['facility_name'];		//	10/8/09
		$f_disp_temp = explode("/", $f_disp_name );
		$facility_display_name = $f_disp_temp[0];
		$faclat = $row_fac['lat'];
		$faclng = $row_fac['lng'];

		if ((my_is_float($faclat)) && (my_is_float($faclng))) {
?>
<SCRIPT>
			var facmarker = createFacilityMarker(<?php print $faclat;?>, <?php print $faclng;?>, '<?php print $facility_display_name;?>', <?php print $fac_type;?>, 0, <?php print $fac_id;?>, '<?php print $icon_str;?>', 0, 0, '<?php print $facility_display_name;?>');
			facmarker.addTo(map);
			i++;
			bounds.extend([<?php print $row_fac['lat'];?>, <?php print $row_fac['lng'];?>]);
</SCRIPT>
<?php
			}	// end if my_is_float
		}	// end while
// ================================End of Facilities========================================
// ====================================Add Responding Units to Map================================================

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$tick_id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while($row = mysql_fetch_array($result)){
		$responder_id=($row['responder_id']);
		if ($row['clear'] == NULL) {

			$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder_id'";
			$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while($row_unit = mysql_fetch_array($result_unit)){
				$unit_id = $row_unit['id'];
				$mobile = $row_unit['mobile'];
				$handle = $row_unit['handle'];
				$index = $row_unit['icon_str'];
				$resp_cat = $un_stat_cats[$row_unit['id']];
				$temp = $row_unit['un_status_id'] ;
				$the_time = $row_unit['updated'];
				$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
				$theType = $row_unit['type'];
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
					if ($mobile == 1) {
?>				
<SCRIPT>
						var unitmarker = createUnitMarker(<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>, "<?php print quote_smart($theTabs);?>", 0, 0, <?php print $unit_id;?>, '<?php print $index;?>', '<?php print $resp_cat;?>', 0, '<?php print $handle;?>', '<?php print $theType;?>');
						unitmarker.addTo(map);
						bounds.extend([<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>]);
						i++
</SCRIPT>						
<?php
						} else {
?>
<SCRIPT>
						var unitmarker = createUnitMarker(<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>, "<?php print quote_smart($theTabs);?>", 4, 0, <?php print $unit_id;?>, '<?php print $index;?>', '<?php print $resp_cat;?>', 0, '<?php print $handle;?>', '<?php print $theType;?>');
						unitmarker.addTo(map);
						bounds.extend([<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>]);
						i++
</SCRIPT>						
<?php
						}	// end if mobile
					}	// end if mys_is_float
				}	// end while row unit
			}	// end if $row['clear'] == NULL
		}	//	end while row

// =====================================End of functions to show responding units========================================================================
?>

<SCRIPT>
var incs_icons=[];
incs_icons[<?php echo $GLOBALS['SEVERITY_NORMAL'];?>] = 1;	// blue
incs_icons[<?php echo $GLOBALS['SEVERITY_MEDIUM'];?>] = 2;	// yellow
incs_icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>] =  3;	// red

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
mapHeight = viewportheight * .65;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('leftTable').style.width = colwidth + "px";	
$('left').style.width = colwidth + "px";
$('rightcol').style.width = colwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
set_fontsizes(viewportwidth, "fullscreen");

function createMarkerInc(lat, lon, info, color, stat, theid, sym, category, region, tip, z) {
	if((isFloat(lat)) && (isFloat(lon))) {
		if(!sym) { sym = "UNK"; }
		var origin = ((sym.length)>3)? (sym.length)-3: 0;
		var iconStr = sym.substring(origin);
		var iconurl = "./our_icons/gen_icon.php?blank=" + escape(window.incs_icons[color]) + "&text=" + iconStr;	
		icon = new baseIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon, title: tip, zIndexOffset: z}).bindPopup(info).openPopup();
		return marker;
		} else {
		return false;
		}
	}
	
var incMarker = createMarkerInc(theLat, theLng, "<?php print $scope;?>", <?php print $severity;?>, "<?php print shorten($type, 18);?>", 1, "1", "Incident", 0, "<?php print $scope;?>", i);
incMarker.addTo(map);
bounds.extend([theLat, theLng]);
map.fitBounds(bounds); 
map.setView([theLat, theLng], 13);
</SCRIPT>
</DIV>
<?php
if($mode == 0) {
	$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
	print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, $tick_id, 0, 0, 0);
	}
?>
<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
</BODY>
</HTML>
<?php
exit();