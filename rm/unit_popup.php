<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
function get_day() {
	$timestamp = (time() - (intval(get_variable('delta_mins'))*60));
	if(strftime("%w",$timestamp)==0) {$timestamp = $timestamp + 86400;}
	return strftime("%A",$timestamp);
	}
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
$alt_day_night = ($day_night=="Day") ? "Night" : "Day"; 
$label1 = "";
$label2 = "";
$label3 = "";
$label4 = "";
$label5 = "";
$label6 = "";
$label7 = "";
$label8 = "";
$text1 = "";
$text2 = "";
$text3 = "";
$text4 = "";
$text5 = "";
$text6 = "";
$text7 = "";
$text8 = "";

$id = $_GET['id'];
$status_vals = array();											// build array of $status_vals
$status_vals[''] = $status_vals['0']="TBD";
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
	$temp = $row_st['id'];
	$status_vals[$temp] = $row_st['status_val'];
	$status_hide[$temp] = $row_st['hide'];
	}

unset($result_st);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
	WHERE `responder_id` = '{$id}' AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";

$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$units_assigned = mysql_num_rows($result_as);

switch ($units_assigned) {		
	case 0:
		$ass_td = "";
		break;			
	case 1:
		$row_assign = stripslashes_deep(mysql_fetch_assoc($result_as));
		$the_disp_stat =  get_disp_status ($row_assign) . "&nbsp;";
		$tip = htmlentities ("{$row_assign['contact']}/{$row_assign['street']}/{$row_assign['city']}/{$row_assign['phone']}/{$row_assign['scope']}", ENT_QUOTES );
		switch($row_assign['severity'])		{		//color tickets by severity
			case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 							$severityclass='severity_normal'; break;
			}		// end switch()
		$ass_td = shorten($row_assign['scope'], 20);
		break;
	default:							// multiples
		$ass_td = $units_assigned;
		break;
	}						// end switch(($units_assigned))

$label1 = get_text('Name');
$label2 = get_text('Handle');
$label3 = get_text('Address');
$label4 = get_text('Assignments');
$label5 = get_text('Description');
$label6 = get_text('Status');
$label7 = get_text('Contact Via');
$label8 = get_text('Updated');
$the_day = "";
$outputstring = "";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id=" . $id;
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
$lat = $row['lat'];
$lng = $row['lng'];
$name = $text1 = $row['name'];
$handle = $text2 = $row['handle'];
$address = $text3 = shorten($row['street'] . ", " . $row['city'] . ", " . $row['state'], 30);
$assigns = $text4 = $ass_td;
$description = $text5 = "<textarea cols=30 cols=6 readonly>" . $row['description'] . "</textarea>";
$status = $text6 = $status_vals[$row['un_status_id']];
$email = $text7 = $row['contact_via'];
$updated = $text8 = format_date_2($row['updated']);
$thetabs = "<TABLE style=\"width: 250px;\">";
$thetabs .= "<TR class=\"even\"><TD class=\"td_label\">" . get_text('Name') . "</TD><TD class=\"td_data\">" . $name . "</TD></TR>";
$thetabs .= "<TR class=\"odd\"><TD class=\"td_label\">" . get_text('Address') . "</TD><TD class=\"td_data\">" . $row['street'] . "</TD></TR>";
$thetabs .= "<TR class=\"even\"><TD class=\"td_label\">" . get_text('City') . "</TD><TD class=\"td_data\">" . $row['city'] . "</TD></TR>";
$thetabs .= "<TR class=\"odd\"><TD class=\"td_label\">" . get_text('Updated') . "</TD><TD class=\"td_data\">" . format_date_2($updated) . "</TD></TR>";
$thetabs .= "</TABLE>";
$lat = $row['lat'];
$lng = $row['lng'];	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Responder Assistance Request Popup</title>
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<link rel="stylesheet" href="../js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="../js/leaflet/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="../js/Control.Geocoder.css" />
<STYLE>
	.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	#regions_control { font-family: verdana, arial, helvetica, sans-serif; font-size: 5px; background-color: #FEFEFE; font-weight: bold;}
	table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
	table.cruises td {overflow: hidden; }
	div.scrollableContainer { position: relative; padding-top: 1.3em; border: 1px solid #999; }
	div.scrollableContainer2 { position: relative; padding-top: 1.3em; }
	div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
	div.scrollingArea2 { max-height: 460px; overflow: auto; overflow-x: hidden; }
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
			position: relative;	z-index: 101; cursor: normal; height: 250px;}
	div.contentwrapper { width: 260px; background-color: #F0F0F0; cursor: normal;}
	.text-labels {font-size: 2em; font-weight: 700;}
</STYLE>
<SCRIPT TYPE="text/javascript" SRC="../js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
<SCRIPT TYPE="text/javascript" SRC="../js/domready.js"></script>
<script src="../js/proj4js.js"></script>
<script src="../js/proj4-compressed.js"></script>
<script src="../js/leaflet/leaflet.js"></script>
<script src="../js/proj4leaflet.js"></script>
<script src="../js/leaflet/KML.js"></script>
<script src="../js/leaflet/gpx.js"></script>
<script src="../js/leaflet-openweathermap.js"></script>
<script src="../js/esri-leaflet.js"></script>
<script src="../js/osopenspace.js"></script>
<script src="../js/Control.Geocoder.js"></script>
<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
<script src="../js/Google.js"></script>
<script type="text/javascript" src="../js/L.Graticule.js"></script>
<script type="text/javascript" src="../js/leaflet-providers.js"></script>
</HEAD>
<?php
$styleStr = "style='background-color: " . get_css("row_light", $day_night) . "; color: " . get_css("row_light_text", $day_night) . ";'";
$header = "Responder Assistance Request for Responder " . $handle;
?>
<BODY <?php print $styleStr;?>>
<DIV id='outer' style='width: 100%;'>
	<DIV id='theTop'>
		<DIV id='header' style='text-align: center;'>
			<SPAN class='heading'><?php print $header;?>: </SPAN><SPAN class='heading' id='osgrid'></SPAN>
		</DIV>
		<DIV style='width: 100%;'>
			<TABLE style='width: 100%; table-layout: fixed; word-wrap: break-all;'>
				<TR class='even'>
					<TD style='width: 100%; border: 1px outset #707070;'>
						<TABLE style='table-layout: fixed; width: 100%; word-wrap: break-all;'>
							<TR class='even' style='width: 100%;'>
								<TD class='td_label' style='width: 40%;'><?php print $label1;?></TD>
								<TD class='td_data_wrap' style='width: 60%;'><?php print $text1;?></TD>
							</TR>
							<TR class='odd' style='width: 100%;'>
								<TD class='td_label' style='width: 40%;'><?php print $label2;?></TD>
								<TD class='td_data_wrap' style='width: 60%;'><?php print $text2;?></TD>
							</TR>
							<TR class='even' style='width: 100%;'>
								<TD class='td_label' style='width: 40%;'><?php print $label3;?></TD>
								<TD class='td_data_wrap' style='width: 60%;'><?php print $text3;?></TD>
							</TR>
							<TR class='odd' style='width: 100%;'>
								<TD class='td_label' style='width: 40%;'><?php print $label4;?></TD>
								<TD class='td_data_wrap' style='width: 60%;'><?php print $text4;?></TD>
							</TR>
							<TR class='even' style='width: 100%;'>
								<TD class='td_label' style='width: 40%;'><?php print $label5;?></TD>
								<TD class='td_data_wrap' style='width: 60%; height: auto;'><?php print $text5;?></TD>
							</TR>
							<TR class='odd' style='width: 100%;'>
								<TD class='td_label' style='width: 40%;'><?php print $label6;?></TD>
								<TD class='td_data_wrap' style='width: 60%;'><?php print $text6;?></TD>
							</TR>
							<TR class='even' style='width: 100%;'>
								<TD class='td_label' style='width: 40%;'><?php print $label7;?></TD>
								<TD class='td_data_wrap' style='width: 60%;'><?php print $text7;?></TD>
							</TR>
							<TR class='odd' style='width: 100%;'>
								<TD class='td_label' style='width: 40%;'><?php print $label8;?></TD>
								<TD class='td_data_wrap' style='width: 60%;'><?php print $text8;?></TD>
							</TR>
						</TABLE>
					</TD>

				</TR>
			</TABLE>
		</DIV>
	</DIV>
	<DIV id='thebottom' style='width: 100%; text-align: center;'><CENTER>
		<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV></CENTER><BR />
		<SPAN id="close-but" class='plain' style="float: none; text-align: center;" onMouseover="do_hover(this.id);" onMouseout="do_plain(this.id);" onClick="window.close();">Close</SPAN>
	</DIV>
</DIV>
<SCRIPT>
var map;
var thelevel = '<?php print $the_level;?>';
var rmarkers = [];			//	Responder Markers array
var cmarkers = [];			//	Responder Markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var latLng;
var baseIcon = L.Icon.extend({options: {shadowUrl: '../our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [0, 0],	shadowAnchor: [5, -5], popupAnchor: [6, -5]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [0, 0], popupAnchor: [6, -5]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [0, 0], popupAnchor: [6, -5]
}
});

function isFloat(n){
    return n != "" && !isNaN(n) && Math.round(n) != n;
	}

function createstdMarker(lat, lon) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconurl = "../our_icons/yellow.png";
		icon = new baseIcon({iconUrl: iconurl});	
		marker = L.marker([lat, lon], {icon: icon});
		marker.addTo(map);
		}
	}
	
function init_map(lat, lng) {
		var latLng;
		var in_local_bool = <?php print get_variable('local_maps');?>;
		var osmUrl = (in_local_bool=="1")? "../_osm/tiles/{z}/{x}/{y}.png":	"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
		var	cmAttr = '';
		var OSM = L.tileLayer(osmUrl, {attribution: cmAttr});
		if(!map) { map = L.map('map_canvas',
			{
			zoom: 13,
			layers: [OSM],
			zoomControl: true,
			});
		createstdMarker(lat, lng);
		map.setView([lat, lng], 13);
		bounds = map.getBounds();	
		zoom = map.getZoom();
		}
	return map;
	}

var mapWidth = <?php print get_variable('map_width');?>-20;
var mapHeight = <?php print get_variable('map_height');?>-20;;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var boundary = [];			//	exclusion zones array
var bound_names = [];
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
init_map(<?php print $lat;?>, <?php print $lng;?>);
map.setView([<?php print $lat;?>, <?php print $lng;?>], 13);
var bounds = map.getBounds();	
var zoom = map.getZoom();
<?php
do_kml();
?>
</SCRIPT>
</BODY>
</HTML>