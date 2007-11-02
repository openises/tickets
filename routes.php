<?php 
error_reporting(E_ALL);
require_once('functions.inc.php');
do_login(basename(__FILE__));
//	foreach ($_POST as $VarName=>$VarValue) {echo "POST:$VarName => $VarValue, <BR />";};
//	foreach ($_GET as $VarName=>$VarValue) 	{echo "GET:$VarName => $VarValue, <BR />";};
//	echo "<BR/>";
$api_key = get_variable('gmaps_api_key');
$_GET = stripslashes_deep($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">	
	<HEAD><TITLE>Tickets - Routes Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
    <style type="text/css">
      body 					{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
      table.directions th 	{background-color:#EEEEEE;}	  
      img 					{color: #000000;}
    </style>
	<SCRIPT type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
	

<?php
	print "<SCRIPT>\n";
	print "var user = '";
	print $_SESSION['user_name'];
	print "'\n";
	print "\nvar level = '" . get_level_text ($_SESSION['level']) . "'\n";
?>	
	parent.frames["upper"].document.getElementById("whom").innerHTML  = user;
	parent.frames["upper"].document.getElementById("level").innerHTML  = level;

	String.prototype.parseDeg = function() {
		if (!isNaN(this)) return Number(this);								// signed decimal degrees without NSEW
		
		var degLL = this.replace(/^-/,'').replace(/[NSEW]/i,'');			// strip off any sign or compass dir'n
		var dms = degLL.split(/[^0-9.,]+/);									// split out separate d/m/s
		for (var i in dms) if (dms[i]=='') dms.splice(i,1);					// remove empty elements (see note below)
		switch (dms.length) {												// convert to decimal degrees...
			case 3:															// interpret 3-part result as d/m/s
				var deg = dms[0]/1 + dms[1]/60 + dms[2]/3600; break;
			case 2:															// interpret 2-part result as d/m
				var deg = dms[0]/1 + dms[1]/60; break;
			case 1:															// decimal or non-separated dddmmss
				if (/[NS]/i.test(this)) degLL = '0' + degLL;	// - normalise N/S to 3-digit degrees
				var deg = dms[0].slice(0,3)/1 + dms[0].slice(3,5)/60 + dms[0].slice(5)/3600; break;
			default: return NaN;
			}
		if (/^-/.test(this) || /[WS]/i.test(this)) deg = -deg; // take '-', west and south as -ve
		return deg;
		}
	Number.prototype.toRad = function() {  // convert degrees to radians
		return this * Math.PI / 180;
		}

	Number.prototype.toDeg = function() {  // convert radians to degrees (signed)
		return this * 180 / Math.PI;
		}
	Number.prototype.toBrng = function() {  // convert radians to degrees (as bearing: 0...360)
		return (this.toDeg()+360) % 360;
		}
	function brng(lat1, lon1, lat2, lon2) {
		lat1 = lat1.toRad(); lat2 = lat2.toRad();
		var dLon = (lon2-lon1).toRad();
	
		var y = Math.sin(dLon) * Math.cos(lat2);
		var x = Math.cos(lat1)*Math.sin(lat2) -
						Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
		return Math.atan2(y, x).toBrng();
		}

	distCosineLaw = function(lat1, lon1, lat2, lon2) {
		var R = 6371; // earth's mean radius in km
		var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
				Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
		return d;
		}
    var km2feet = 3280.83;

	function extr_num (instr) {						// extracts the ho number
		outstr="";
		var OKchars = '0123456789,.';
		for (i=0;i< instr.length; i++) {
			if (OKchars.indexOf(instr.charAt(i)) ==-1) {
				break;
				}
			else {
				outstr+=instr.charAt(i);
				}
			}				// end for ()
		return outstr;
		}				// end function extr_num ()
	
	function min(inArray) {				// returns index of least float value in inArray
		var minsofar =  40076.0;				// initialize to earth circumference (km)
		var j=-1;
		for (var i=0; i< inArray.length; i++){
			if (parseFloat(inArray[i]) < parseFloat(minsofar)) {
				j=i;
				minsofar=inArray[i];
				}
			}
		return j;
		}		// end function min()

</SCRIPT>
<?php
	$query = "SELECT * FROM $GLOBALS[mysql_prefix]ticket WHERE ID=" . $_GET['ticket_id'];			// get Incident location
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_ticket = stripslashes_deep(mysql_fetch_array($result));
	unset ($result);
?>
	<BODY onunload='GUnload()'>
	<TABLE ID='outer' BORDER = 0>
	<TR><TD VALIGN='top'><DIV ID='side_bar' STYLE='width: 400px'></DIV>
		<BR>
			<DIV ID='the_ticket' style='width: 500px;'><?php print do_ticket($row_ticket, 500); ?></DIV>
		</TD>
		<TD VALIGN="top" ALIGN='center'>
			<DIV ID='map_canvas' style='width: 500px; height: 400px; border-style: outset'></DIV>
			<BR /><INPUT TYPE='button' VALUE='Cancel' onClick='document.can_Form.submit();'>
			<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
				Medical: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
				Fire: 		<IMG SRC = './markers/sm_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
				Police: 	<IMG SRC = './markers/sm_blue.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;
				Other: 		<IMG SRC = './markers/sm_green.png' BORDER=0><BR />
			<DIV ID="directions" STYLE="width: 500px"></DIV>
		</TD></TR></TABLE><!-- end outer -->
	<FORM NAME='can_Form' ACTION="main.php">
	<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>">
	</FORM>	
	</BODY>
<?php

	print list_responders();
	print "\n</HTML> \n";

	function list_responders($addon = '') {
		global $row_ticket;
	
?>
	<SCRIPT>

	var color=0;
	var last;				// id of last/current responder sidebar element
	var last_from;
	var last_to;
	
	if (GBrowserIsCompatible()) {
		var colors = new Array ('odd', 'even');
	    function setDirections(fromAddress, toAddress, locale) {
	    	last_from = fromAddress;
	    	last_to = toAddress;
	    	gdir.load("from: " + fromAddress + " to: " + toAddress, { "locale": locale });
	    	}		// end function set Directions()
	
		function createMarker(point,sidebar,tabs, color, id) {		// Creates marker and sets up click event infowindow
//			alert (159);
			var icon = new GIcon(listIcon);
			icon.image = icons[color] + (id % 100) + ".png";		//e.g.,marker9.png, 100 icons limit
			var marker = new GMarker(point, icon);
			marker.id = color;				// for hide/unhide - unused
	
			GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
				alert (165);
				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				marker.openInfoWindowTabsHtml(infoTabs[id]);
				var dMapDiv = document.getElementById("detailmap");
				var detailmap = new GMap2(dMapDiv);
				detailmap.addControl(new GSmallMapControl());
				detailmap.setCenter(point, 13);  					// larger # = closer
				detailmap.addOverlay(marker);
				});
	
			gmarkers[id] = marker;							// marker to array for side_bar click function
			infoTabs[id] = tabs;							// tabs to array
	
			side_bar_html += "<TR CLASS='" + colors[(id+1)%2] +"' VALIGN='bottom' onClick = myclick(" + id + ");><TD>";
			side_bar_html += "<IMG BORDER=0 SRC='rtarrow.gif' ID = \"R" + id + "\"  STYLE = 'visibility:hidden;'></TD>";
			side_bar_html += "<TD CLASS='td_label'>" + (id) + ". "+ sidebar +"</TD></TR>\n";
			bounds.extend(point);							// extend the bounding box
	
			return marker;
			}				// end function create Marker()
	
		function myclick(id) {								// Responds to sidebar click
//			alert (190);
			which = id;
			document.getElementById(last).style.visibility = "hidden";		// hide last check
			var element= "R"+id;
			document.getElementById(element).style.visibility = "visible";
			last = element;													// new 'last' = current selection
//			GEvent.trigger(gmarkers[id], "click");
			var thelat = <?php print $row_ticket['lat'];?>; var thelng = <?php print $row_ticket['lng'];?>;
			setDirections(lats[id] + " " + lngs[id], thelat + " " + thelng, "en_US");
			}
	
		function doGrid() {
			map.addOverlay(new LatLonGraticule());
			}
	
		function handleErrors(){
			if (gdir.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
			alert("No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_SERVER_ERROR)
				alert("A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_MISSING_QUERY)
				alert("The HTTP q parameter was either missing or had no value. For geocoder requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.\n Error code: " + gdir.getStatus().code);
	//		else if (gdir.getStatus().code == G_UNAVAILABLE_ADDRESS)  <--- Doc bug... this is either not defined, or Doc is wrong
	//			alert("The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_KEY)
				alert("The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_REQUEST)
				alert("A directions request could not be successfully parsed.\n Error code: " + gdir.getStatus().code);
			else alert("An unknown error occurred.");
			}		// end function handleErrors()
	
		function onGDirectionsLoad(){ 
			var temp = gdir.getSummaryHtml();
//			alert(extr_num(temp));
	// 		Use this function to access information about the latest load() results.
	// 			e.g.
	// 		document.getElementById("getStatus").innerHTML = gdir.getStatus().code;
	// 		and yada yada yada...
			}		// function onGDirectionsLoad()
	
		var icons=[];						// note globals
		icons[1] = "./markers/YellowIcons/marker";		//e.g.,marker9.png
		icons[2] = "./markers/RedIcons/marker";
		icons[3] = "./markers/BlueIcons/marker";
		icons[4] = "./markers/GreenIcons/marker";
	
		var map;
		var center;
		var zoom;
		
	    var gdir;				// directions
	    var geocoder = null;
	    var addressMarker;
		
		var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
		side_bar_html += "<TR class='even'>	<TD colspan=99 ALIGN='center'><B>Routes to Incident <I><?php print shorten($row_ticket['scope'], 20); ?></I></B></TD></TR>";
		side_bar_html += "<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for route</TD></TR>";
		side_bar_html += "<TR class='even'>	<TD COLSPAN=2></TD><TD ALIGN='center'>Unit</TD><TD ALIGN='center'>SLD</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD></TR>";
		var gmarkers = [];
		var infoTabs = [];
		var lats = [];
		var lngs = [];
		var distances = [];
		var which;			// marker last selected
		var i = 0;			// sidebar/icon index
	
		map = new GMap2(document.getElementById("map_canvas"));		// create the map
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());
		gdir = new GDirections(map, document.getElementById("directions"));
		
		GEvent.addListener(gdir, "load", onGDirectionsLoad);
		GEvent.addListener(gdir, "error", handleErrors);
		
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>
	
		var bounds = new GLatLngBounds();						// create empty bounding box
	
		var listIcon = new GIcon();
		listIcon.image = "./markers/yellow.png";	// yellow.png - 16 X 28
		listIcon.shadow = "./markers/sm_shadow.png";
		listIcon.iconSize = new GSize(20, 34);
		listIcon.shadowSize = new GSize(37, 34);
		listIcon.iconAnchor = new GPoint(8, 28);
		listIcon.infoWindowAnchor = new GPoint(9, 2);
		listIcon.infoShadowAnchor = new GPoint(18, 25);
	
		var newIcon = new GIcon();
		newIcon.image = "./markers/white.png";	// yellow.png - 20 X 34
		newIcon.shadow = "./markers/shadow.png";
		newIcon.iconSize = new GSize(20, 34);
		newIcon.shadowSize = new GSize(37, 34);
		newIcon.iconAnchor = new GPoint(8, 28);
		newIcon.infoWindowAnchor = new GPoint(9, 2);
		newIcon.infoShadowAnchor = new GPoint(18, 25);
																	// set Incident position
		var point = new GLatLng(<?php print $row_ticket['lat'];?>, <?php print $row_ticket['lng'];?>);	
		bounds.extend(point);										// Incident into BB
	
		GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
			setDirections(last_from, last_to, "en_US") ;
//			map.setCenter(center,zoom);
//			alert ("289 " + which);
//			map.addOverlay(gmarkers[which])
//			alert ("290 " + zoom);
			});
	
//		GEvent.addListener(map, "click", function(marker, point) {
//			if (marker) {
//				document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=false;
//				document.forms[0].frm_lat.value=document.forms[0].frm_lng.value="";
//				document.forms[0].frm_lat.disabled=document.forms[0].frm_lat.disabled=true;
//				}
//			});				// end GEvent.addListener() "click"
<?php
	
		$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]responder ORDER BY `name`";	
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if(mysql_affected_rows()>0) {
									// major while ... for RESPONDER data starts here
			while ($row = stripslashes_deep(mysql_fetch_array($result))) {
				if ($row['lat'] !=0) {
	
					$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "px'>";
					$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . shorten($row['name'], 48) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Description:</TD><TD>" . shorten($row['description'], 32) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Status:</TD><TD>" . $row['status'] . " </TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
					$tab_1 .= "</TABLE>";
			
					$thespeed = "";
					$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
						WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";
					$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			
					if (mysql_affected_rows()>0) {		// got a track?
						$rowtr = stripslashes_deep(mysql_fetch_array($result_tr));			// most recent track report
			
						$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "px'>";
						$tab_2 .= "<TR><TH CLASS='even' COLSPAN=2>" . $rowtr['source'] . "</TH></TR>";
						$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $rowtr['closest_city'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $rowtr['status'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($rowtr['packet_date']) . "</TD></TR>";
						$tab_2 .= "</TABLE>";
?>
						i++;
						var myinfoTabs = [
							new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 8));?>", "<?php print $tab_1;?>"),
							new GInfoWindowTab("<?php print $rowtr['source']; ?>", "<?php print $tab_2;?>"),
							new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
							];
		
						lats[i] = <?php print $rowtr['latitude'];?>;									// now compute distance - in km
						lngs[i] = <?php print $rowtr['longitude'];?>;
						distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_ticket['lat'];?>), parseFloat(<?php print $row_ticket['lng'];?>));
					    var km2mi = 0.6214;				// 
						var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to miles
<?php
						$thespeed = ($rowtr['speed'] == 0)?"<FONT COLOR='red'><B>&bull;</B></FONT>"  : "<FONT COLOR='green'><B>&bull;</B></FONT>" ;
						if ($rowtr['speed'] >= 50) { $thespeed = "<FONT COLOR='WHITE'><B>&bull;</B></FONT>";}
?>
						var point = new GLatLng(<?php print $rowtr['latitude'];?>, <?php print $rowtr['longitude'];?>);	// mobile position
						bounds.extend(point);															// point into BB
		
						sidebar_line = "<TD><?php print shorten($row['name'], 32);?></TD><TD>"+ dist_mi+"</TD>";
						sidebar_line += "<TD CLASS='td_data'><?php print shorten($row['status'], 12);?></TD>";
						sidebar_line += "<TD CLASS='td_data'><?php print $thespeed;?></TD><TD CLASS='td_data'><?php print format_sb_date($row['updated']);?></TD>";
		
						var marker = createMarker(point, sidebar_line, myinfoTabs,<?php print $row['type'];?>, i);	// (point,sidebar,tabs, color, id)
						map.addOverlay(marker);
			
<?php
						}				// end if (mysql_affected_rows()>0;) for track data
			
					else {				// no track data
?>
						i++;
						var myinfoTabs = [
							new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 12));?>", "<?php print $tab_1;?>"),
							new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
							];
						
						lats[i] = <?php print $row['lat'];?>;									// now compute distance - in km
						lngs[i] = <?php print $row['lng'];?>;
						distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_ticket['lat'];?>), parseFloat(<?php print $row_ticket['lng'];?>));	// note: km
//						distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_ticket['lat'];?>), parseFloat(<?php print $row_ticket['lng'];?>));	// note: km
					    var km2mi = 0.6214;				// 
						var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to feet
			
						sidebar_line = "<TD><?php print shorten($row['name'], 16);?></TD><TD>"+ dist_mi+"</TD>";
						sidebar_line += "<TD CLASS='td_data'><?php print shorten($row['status'],12);?></TD>";
						sidebar_line += "<TD CLASS='td_data'></TD><TD CLASS='td_data'><?php print format_sb_date($row['updated']);?></TD>"; 
		
						var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// for each responder
						bounds.extend(point);																// point into BB
						
						var marker = createMarker(point, sidebar_line, myinfoTabs, <?php print $row['type'];?>, i);	// (point,sidebar,tabs, color, id)
						map.addOverlay(marker);						
<?php
						}				// end if/else (mysql_affected_rows()>0;) - no track data
					}				// end if (valid position)
				}				// end major while ($row = ...) for each responder
			}				// end if(mysql_affected_rows()>0)
			
//					responders complete
?>
		if (i == 0) {
			side_bar_html +="<TR CLASS='odd'><TD ALIGN='center' COLSPAN=99><BR /><B>No Units!</B></TD></TR>";;		
			map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);		// -1 for further out
			map.setCenter(center,zoom);
			side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=99>&nbsp;</TD></TR>";
			side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=99 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>";
	
			}
	
		side_bar_html +="</TABLE>\n";
		document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div
	
		var thelat = <?php print $row_ticket['lat'];?>; var thelng = <?php print $row_ticket['lng'];?>;
		var start = min(distances);		// min straight-line distance to Incident
		
		var last= "R"+start;			//
		document.getElementById(last).style.visibility = "visible";		// show link check image at the selected sidebar element
		
		setDirections(lats[start] + " " + lngs[start], thelat + " " + thelng, "en_US");
	
		}		// end if (GBrowserIsCompatible())

	else {
		alert("Sorry,  browser compatibility problem. Contact your tech support group.");
		}
	</SCRIPT>
	
<?php
	}				// end function list_responders() ===========================================================
?>