<?php
require_once('./incs/functions.inc.php');
@session_start();
session_write_close();
$server_name = "http://www." . $_SERVER['SERVER_NAME'];
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
$popup_type = $_GET['type'];
if($popup_type == 'ticket') {
	$label1 = get_text('Scope');
	$label2 = get_text('Synopsis');
	$label3 = get_text('Problemstart');
	$label4 = get_text('Problemend');
	$label5 = get_text('Disposition');
	$label6 = get_text('Severity');
	$label7 = get_text('Type');
	$label8 = get_text('Status');
	$u_types = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
		}
	unset($result);

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$assigns = array();					// 8/3/08
	$tickets = array();					// ticket id's
	$responders = array();				// responder details

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

	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, 
		`$GLOBALS[mysql_prefix]assigns`.`responder_id`, 
		`$GLOBALS[mysql_prefix]responder`.`name` AS `r_name`,
		`$GLOBALS[mysql_prefix]responder`.`handle` AS `r_handle`,
		`$GLOBALS[mysql_prefix]responder`.`street` AS `r_street`,
		`$GLOBALS[mysql_prefix]responder`.`city` AS `r_city`,
		`$GLOBALS[mysql_prefix]responder`.`status_updated` AS `r_updated`,
		`$GLOBALS[mysql_prefix]responder`.`lat` AS `r_lat`,
		`$GLOBALS[mysql_prefix]responder`.`lng` AS `r_lng`
		FROM `$GLOBALS[mysql_prefix]assigns`
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` ON `$GLOBALS[mysql_prefix]assigns`.`responder_id`=`$GLOBALS[mysql_prefix]responder`.`id`
		WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = " . $id;
	$result_ras = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_ras = stripslashes_deep(mysql_fetch_array($result_ras))) {
		$responders[$row_ras['responder_id']][0] = $row_ras['responder_id'];
		$responders[$row_ras['responder_id']][1] = $row_ras['r_name'];
		$responders[$row_ras['responder_id']][2] = $row_ras['r_lat'];
		$responders[$row_ras['responder_id']][3] = $row_ras['r_lng'];
		$responders[$row_ras['responder_id']][4] = $row_ras['r_street'];
		$responders[$row_ras['responder_id']][5] = $row_ras['r_city'];
		$responders[$row_ras['responder_id']][6] = $row_ras['r_updated'];
		$responders[$row_ras['responder_id']][7] = $row_ras['r_handle'];
		}
	unset($result_ras);	

	$result = mysql_query("SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart ,UNIX_TIMESTAMP(problemend) AS problemend FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'");
	$row = mysql_fetch_assoc($result);
	$lat = $row['lat'];
	$lng = $row['lng'];
	$title = $text1 = $row['scope'];
	$sev = $row['severity'];
	$ticket_severity = $text6 = get_severity($row['severity']);
	$ticket_type = $text7 = get_type($row['in_types_id']);
	$ticket_status = $text7 = get_status($row['status']);
	$ticket_updated = format_date_time($row['updated']);
	$ticket_addr = "{$row['street']}, {$row['city']} {$row['state']} ";
	$ticket_synopsis = $text2 = $row['description'];
	$ticket_disposition = $text5 = $row['comments'];
	$ticket_start = $row['problemstart'];		//
	$ticket_end = $row['problemend'];		//
	$ticket_start_str = $text3 = format_date_2($row['problemstart']);		//
	$ticket_end_str = $text4 = format_date_2($row['problemend']);		//
	$thetabs = "<TABLE style=\"width: 250px;\">";
	$thetabs .= "<TR class=\"even\"><TD class=\"td_label\">" . get_text('Scope') . "</TD><TD class=\"td_data\">" . $title . "</TD></TR>";
	$thetabs .= "<TR class=\"odd\"><TD class=\"td_label\">" . get_text('Address') . "</TD><TD class=\"td_data\">" . $row['street'] . "</TD></TR>";
	$thetabs .= "<TR class=\"even\"><TD class=\"td_label\">" . get_text('City') . "</TD><TD class=\"td_data\">" . $row['city'] . "</TD></TR>";
	$thetabs .= "<TR class=\"odd\"><TD class=\"td_label\">" . get_text('Problemstart') . "</TD><TD class=\"td_data\">" . format_date_2($ticket_start) . "</TD></TR>";
	$thetabs .= "</TABLE>";
	} elseif($popup_type == 'responder') {
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
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$id'");
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
	} elseif($popup_type == 'facility') {
	
	$fac_status_vals = array();											// build array of $status_vals
	$fac_status_vals[''] = $fac_status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `id`";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$facid = $row_st['id'];
		$fac_status_vals[$facid][0] = $row_st['status_val'];
		$fac_status_vals[$facid][1] = $row_st['description'];
		$fac_status_vals[$facid][2] = $row_st['bg_color'];	
		$fac_status_vals[$facid][3] = $row_st['text_color'];			
		}

	unset($result_st);
	$label1 = get_text('Name');
	$label2 = get_text('Handle');
	$label3 = get_text('Address');
	$label4 = get_text('Opening Hours');
	$label5 = get_text('Description');
	$label6 = get_text('Status');
	$label7 = get_text('Contact Email');
	$label8 = get_text('Updated');
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE id='$id'");
	$row = mysql_fetch_assoc($result);	
	$opening_arr_serial = base64_decode($row['opening_hours']);
	$opening_arr = unserialize($opening_arr_serial);
	$openingHours = "Opening Hours<BR />";
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
		if($dayname == get_day()) {
			$the_day .= $dayname;
			$outputstring .= " Opens: " . $val[1] . " Closes: " . $val[2];
			}
		$z++;
		}
	$openingHours = "Opening Times Today (" . $the_day . ")<BR />" . $outputstring;
	
	$name = $text1 = $row['name'];
	$handle = $text2 = $row['handle'];
	$address = $text3 = shorten($row['street'] . ", " . $row['city'] . ", " . $row['state'], 30);
	$opening = $text4 = $openingHours;
	$description = $text5 = "<textarea cols=30 cols=6 readonly>" . $row['description'] . "</textarea>";
	$status = $text6 = $fac_status_vals[$row['status_id']][0];
	$email = $text7 = $row['contact_email'];
	$updated = $text8 = format_date_2($row['updated']);	
	$lat = $row['lat'];
	$lng = $row['lng'];	
	} else {
	exit();
	}
$openspace_api = get_variable('openspace_api');
$_SERVER['HTTP_REFERER'] = $server_name;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Ordnance Survey Map</title>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
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
<script type="text/javascript" src="./js/usng.js"></script>
<script type="text/javascript" src="./js/osgb.js"></script>
<script type="text/javascript" src="./js/geotools2.js"></script>
<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT>
<script src="./js/proj4js.js"></script>
<script src="./js/proj4-compressed.js"></script>
<script src="./js/leaflet/leaflet.js"></script>
<script src="./js/proj4leaflet.js"></script>
<script src="./js/leaflet/KML.js"></script>
<script src="./js/leaflet/gpx.js"></script>  
<script src="./js/OSOpenspace.js"></script>
<script src="./js/leaflet-openweathermap.js"></script>
<script src="./js/esri-leaflet.js"></script>
<script src="./js/Control.Geocoder.js"></script>
<script type="text/javascript" src="./js/osm_map_functions.js.php"></script>
<script type="text/javascript" src="./js/L.Graticule.js"></script>
<script type="text/javascript" src="./js/leaflet-providers.js"></script>
<script type="text/javascript" src="./js/usng.js"></script>
<script type="text/javascript" src="https://openspace.ordnancesurvey.co.uk/osmapapi/openspace.js?key=<?php print $openspace_api;?>"></script>
<script type= "text/javascript" src="https://openspace.ordnancesurvey.co.uk/osmapapi/script/mapbuilder/basicmap.js"></script>
<script type= "text/javascript" src="https://openspace.ordnancesurvey.co.uk/osmapapi/script/mapbuilder/searchbox.js"></script>
<script type="text/javascript">
//declare marker variables
var pos, size, offset, infoWindowAnchor, icon, content, popUpSize;

var osgrid = LLtoOSGB(<?php print $lat;?>, <?php print $lng;?>, 5)

function initmapbuilder() {
	//initiate the map
	var options = {resolutions: [2500, 1000, 500, 200, 100, 50, 25, 10, 5, 4, 2.5, 2, 1]};
	osMap = new OpenSpace.Map('map', options);

	//configure map options (basicmap.js)
	setglobaloptions();
	
	// add a marker
	var lonlat = new OpenLayers.LonLat(<?php print $lng;?>, <?php print $lat;?>);

	var gridProjection = new OpenSpace.GridProjection();
	var pos = gridProjection.getMapPointFromLonLat(lonlat);
	osMap.setCenter(pos,3);
	size = new OpenLayers.Size(20,32);
	offset = new OpenLayers.Pixel(-6,-5);
	infoWindowAnchor = new OpenLayers.Pixel(16,16);
	popUpSize = new OpenLayers.Size(300,150);
	icon = new OpenSpace.Icon('./our_icons/red.png', size, offset, null, infoWindowAnchor);
	osMap.createMarker(pos, icon, '<?php print $thetabs;?>', popUpSize);
	$('osgrid').innerHTML = osgrid;
<?php
		if($popup_type == "ticket") {
			foreach($responders AS $theVal) {
?>
			var lonlat<?php print $theVal[0];?> = new OpenLayers.LonLat(<?php print $theVal[3];?>, <?php print $theVal[2];?>);
			var pos<?php print $theVal[0];?> = gridProjection.getMapPointFromLonLat(lonlat<?php print $theVal[0];?>);
			icon = new OpenSpace.Icon('./our_icons/blue.png', size, offset, null, infoWindowAnchor);
			var theHTML = "<TABLE style='width: 250px;'>";
			theHTML += "<TR class='even'><TD class='td_label'>Name</TD><TD class='td_data'><?php print $theVal[1];?> (<?php print $theVal[7];?>)</TD></TR>";
			theHTML += "<TR class='odd'><TD class='td_label'>Address</TD><TD class='td_data'><?php print $theVal[4];?></TD></TR>";
			theHTML += "<TR class='even'><TD class='td_label'>City</TD><TD class='td_data'><?php print $theVal[5];?></TD></TR>";
			theHTML += "<TR class='odd'><TD class='td_label'>Updated</TD><TD class='td_data'><?php print format_date_2($theVal[6]);?></TD></TR>";
			theHTML += "</TABLE>";
			osMap.createMarker(pos<?php print $theVal[0];?>, icon, theHTML, popUpSize);
<?php
			}
		}
?>
	}
</script>
</HEAD>
<?php
if($popup_type == 'ticket') {
	$severities = $colors = array();
	$severities[$GLOBALS['SEVERITY_NORMAL']] = "#DEE3E7";
	$severities[$GLOBALS['SEVERITY_MEDIUM']] = "#00FF00";
	$severities[$GLOBALS['SEVERITY_HIGH']] = "#F80000";

	$colors[$GLOBALS['SEVERITY_NORMAL']] = "black";
	$colors[$GLOBALS['SEVERITY_MEDIUM']] = "black";
	$colors[$GLOBALS['SEVERITY_HIGH']] = "yellow";
	
	$styleStr = "style='background-color: " . $severities[$row['severity']] . "; color: " . $colors[$row['severity']] . ";'";
	} else {
	$styleStr = "style='background-color: " . get_css("row_light", $day_night) . "; color: " . get_css("row_light_text", $day_night) . ";'";
	}
switch($popup_type) {
	case "ticket":
	$header = "Ticket Details - Ordnance Survey Map - Grid Reference";
	break;
	case "responder":
	$header = "Responder Details - Ordnance Survey Map - Grid Reference";
	break;
	case "facility":
	$header = "Facility Details - Ordnance Survey Map - Grid Reference";
	break;
	}
?>
<BODY <?php print $styleStr;?> onload="initmapbuilder();">
<DIV id='outer' style='width: 100%;'>
	<DIV id='theTop'>
		<DIV id='header' style='text-align: center;'>
			<SPAN class='heading'><?php print $header;?>: </SPAN><SPAN class='heading' id='osgrid'></SPAN>
		</DIV>
		<DIV style='width: 100%;'>
			<TABLE style='width: 100%; table-layout: fixed; word-wrap: break-all;'>
				<TR class='even'>
<?php
				if($popup_type == "ticket") {
?>
					<TD style='width: 60%; border: 1px outset #707070;'>
<?php
					} else {
?>
					<TD style='width: 100%; border: 1px outset #707070;'>
<?php
					}
?>
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
<?php
					if($popup_type == "ticket") {
?>
						<TD style='width: 40%; border: 1px outset #707070;'>
							<TABLE>
								<TR class='even'>
									<TD>
										<DIV style='max-height: 170px; overflow-y: auto;'>
<?php
											/* Creates statistics header and details of responding and en-route units 7/29/09 */

											$result_dispatched = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
												WHERE ticket_id='$id'
												AND `dispatched` IS NOT NULL 
												AND `responding` IS NULL 
												AND `on_scene` IS NULL 
												AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))");		// 6/25/10
											$num_rows_dispatched = mysql_num_rows($result_dispatched);

											$result_responding = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
												WHERE ticket_id='$id'
												AND `responding` IS NOT NULL 
												AND `on_scene` IS NULL 
												AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))");		// 6/25/10
											$num_rows_responding = mysql_num_rows($result_responding);

											$result_on_scene = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
												WHERE ticket_id='$id' 
												AND `on_scene` IS NOT NULL 
												AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')	
												");		// 6/25/10
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
													WHERE (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')
													AND ticket_id='$id' ";

											$result_cleared  = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
											$num_rows_cleared = mysql_affected_rows();
											$ticket_end = ($ticket_end > 1)? $ticket_end:  (time() - (get_variable('delta_mins')*60));
											$tick_end_str = format_date_2($ticket_end);
											$elapsed = my_date_diff(mysql_format_date($ticket_start), mysql_format_date($ticket_end));		// 5/13/10
											echo "<BR /><B>Ticket:&nbsp;{$title}<BR />Opened:&nbsp;{$ticket_start_str},&nbsp;&nbsp;&nbsp;&nbsp;Status: {$ticket_status}</B><BR />";
											$stats = "<B>Severity:&nbsp;{$ticket_severity}, &nbsp;age: {$elapsed}";

											echo $stats;

											echo "<BR>Units dispatched:&nbsp;({$num_rows_dispatched})&nbsp;";
											while ($row_base= mysql_fetch_array($result_dispatched, MYSQL_ASSOC)) {
												$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
												$row = mysql_fetch_assoc($result);
												echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
												}

											echo "<BR>Units responding: ($num_rows_responding)&nbsp;";
											while ($row_base= mysql_fetch_array($result_responding, MYSQL_ASSOC)) {
												$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
												$row = mysql_fetch_assoc($result);
												echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
												}

											echo "<BR>Units on scene: ($num_rows_on_scene)&nbsp;";
											while ($row_base= mysql_fetch_array($result_on_scene, MYSQL_ASSOC)) {
												$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
												$row = mysql_fetch_assoc($result);
												echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
												}

											echo "<BR>Units clear:&nbsp;({$num_rows_cleared})&nbsp;";
											while ($row_base= mysql_fetch_array($result_cleared, MYSQL_ASSOC)) {
												echo "{$row_base['unit_name']}:&nbsp;{$row_base['handle']}&nbsp;&nbsp;";
												}
	?>
									</DIV>
								</TD>
							</TR>
						</TABLE>
					</TD>
<?php
					}
?>
				</TR>
			</TABLE>
		</DIV>
	</DIV>
	<DIV id='thebottom' style='width: 100%; text-align: center;'><CENTER>
		<DIV id="map" style="border: 1px solid black; width:500px; height:450px;"></DIV></CENTER><BR />
		<SPAN id="close-but" class='plain' style="float: none; text-align: center;" onMouseover="do_hover(this.id);" onMouseout="do_plain(this.id);" onClick="window.close();">Close</SPAN>
	</DIV>
</DIV>
</BODY>
</HTML>