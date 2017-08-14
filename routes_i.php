<?php
/*
routes to incident from selected unit
08/7/31 cloned from routes.php 
1/21/09 added show butts - re button menu
3/11/09 scroll wheel operation added
1/7/10 added 'call taker' alias
7/16/10 detailmap.setCenter correction
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
11/23/10 - mi vs km per locale
3/15/11 changed stylesheet.php to stylesheet.php

*/
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));
if($istest) {
	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
$conversion = get_dist_factor();				// KM vs mi - 11/23/10
	
$api_key = get_variable('gmaps_api_key');
$_GET = stripslashes_deep($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">	
	<HEAD><TITLE>Tickets - Routes to Incident Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
    <style type="text/css">
      body 					{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
      table.directions th 	{background-color:#EEEEEE;}	  
      img 					{color: #000000;}
    </style>
<?php
if (!empty($_POST)) {
	extract($_POST);
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 
	$assigns = explode (",", $_POST['frm_id_str']);		// comma sep'd
	for ($i=0;$i<count($assigns); $i++) {
		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`, `dispatched`)
						VALUES (%s,%s,%s,%s,%s,%s,%s)",
							quote_smart($now),
							quote_smart($frm_status_id),
							quote_smart($frm_ticket_id),
							quote_smart($assigns[$i]),
							quote_smart($frm_comments),
							quote_smart($frm_by_id),
							quote_smart($now));
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//										remove placeholder inserted by 'add'		
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . quote_smart($frm_ticket_id) . " AND `responder_id` = 0 LIMIT 1";
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
							// apply status update to unit status
		$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `un_status_id`= " . quote_smart($frm_status_id) . " WHERE `id` = " .quote_smart($assigns[$i])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $assigns[$i], $frm_status_id);
		}
?>	
<SCRIPT>

try {
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
	}
catch(e) {
	}

</SCRIPT>
</HEAD>
<BODY>
	<CENTER><BR><BR><BR><BR><H3>Call Assignments made to:<BR /><?php print $_POST['frm_name_str'];?><BR><BR>
	See call Board</H3>
	<FORM NAME='cont_form' METHOD = 'get' ACTION = "main.php">
	<INPUT TYPE='button' VALUE='Continue' onClick = "document.cont_form.submit()">
	</FORM></BODY></HTML>
<?php		
	}		// end if (!empty($_POST))
else {	
	if ($_SESSION['internet']) {
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
<SCRIPT>
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

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
		var minsofar =  40076.0;		// initialize to earth circumference (km)
		var j=-1;
		for (var i=0; i< inArray.length; i++){
			if (parseFloat(inArray[i]) < parseFloat(minsofar)) {
				j=i;
				minsofar=inArray[i];
				}
			}
		return j;
		}		// end function min()

	function ck_frames() {		// onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
function doReset() {
	document.reLoad_Form.submit();
	}	// end function doReset()
	
</SCRIPT>
<?php								// 1/7/10
	$query = "SELECT *,
		UNIX_TIMESTAMP(problemstart) AS problemstart,
		UNIX_TIMESTAMP(problemend) AS problemend,
		UNIX_TIMESTAMP(date) AS date,
		UNIX_TIMESTAMP(updated) AS updated,
		`_by` AS `call_taker`
		FROM $GLOBALS[mysql_prefix]ticket 
		WHERE ID=" . $_GET['ticket_id'] . " LIMIT 1";			// get Incident location
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_unit = stripslashes_deep(mysql_fetch_array($result));
	unset ($result);
?>
<!--	<BODY onLoad = "ck_frames()" onUnload='GUnload()'> -->
	<BODY onUnload='GUnload()'>
	<TABLE ID='outer' BORDER = 0>
	<TR><TD VALIGN='top'><DIV ID='side_bar' STYLE='width: 400px'></DIV>
		<BR>
			<DIV ID='the_ticket' style='width: 500px;'><?php print do_ticket($row_unit, 500, FALSE, FALSE); ?></DIV>
		</TD>
		<TD VALIGN="top" ALIGN='center'>
			<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR />
			<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
				EMS: 	<IMG SRC = './markers/sm_yellow.png' 	BORDER=0>&nbsp;&nbsp;&nbsp;
				Fire: 		<IMG SRC = './markers/sm_red.png' 		BORDER=0>&nbsp;&nbsp;&nbsp;
				Police: 	<IMG SRC = './markers/sm_blue.png' 		BORDER=0>&nbsp;&nbsp;&nbsp;
				Mutual: 	<IMG SRC = './markers/sm_white.png' 	BORDER=0>&nbsp;&nbsp;&nbsp;
				Other: 		<IMG SRC = './markers/sm_green.png' 	BORDER=0><BR />
			<DIV ID="directions" STYLE="width: <?php print get_variable('map_width');?>"></DIV>
		</TD></TR></TABLE><!-- end outer -->
	<FORM NAME='can_Form' ACTION="main.php">
	<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>">
	</FORM>	
	<FORM NAME='routes_Form' METHOD='post' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='func' 			VALUE='do_db'>
	<INPUT TYPE='hidden' NAME='frm_ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>'>
	<INPUT TYPE='hidden' NAME='frm_by_id' 		VALUE= "<?php print $_SESSION['user_id'];?>">
	<INPUT TYPE='hidden' NAME='frm_id_str' 		VALUE= "">
	<INPUT TYPE='hidden' NAME='frm_name_str' 	VALUE= "">
	<INPUT TYPE='hidden' NAME='frm_status_id' 	VALUE= "1">
	<INPUT TYPE='hidden' NAME='frm_comments' 	VALUE= "New">
	</FORM>
	<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
	<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>'>
	</FORM>	
	
	</BODY>

<?php
	$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
	print do_list($unit_id);
	print "</HTML> \n";

	}			// end if/else !empty($_POST)

function do_list($unit_id ="") {
	global $row_unit;
	
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
//	    	alert("from: " + fromAddress + " to: " + toAddress, { "locale": locale });
	    	gdir.load("from: " + fromAddress + " to: " + toAddress, { "locale": locale });
//	    	alert (235);
	    	}		// end function set Directions()
	
		function createMarker(point,sidebar,tabs, color, id) {		// Creates marker and sets up click event infowindow
			var icon = new GIcon(listIcon);
			icon.image = icons[color] + (id % 100) + ".png";		//e.g.,marker9.png, 100 icons limit
			var marker = new GMarker(point, icon);
			marker.id = color;				// for hide/unhide - unused
	
			GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				marker.openInfoWindowTabsHtml(infoTabs[id]);
				var dMapDiv = document.getElementById("detailmap");
				var detailmap = new GMap2(dMapDiv);
				detailmap.addControl(new GSmallMapControl());
				detailmap.setCenter(point, 17);  					// larger # = closer - 7/16/10
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
			if (!(lats[id])) {
				alert("Cannot route -  no position data currently available");
				return false;
				}
			else {
				which = id;
				document.getElementById(last).style.visibility = "hidden";		// hide last check
				var element= "R"+id;
				document.getElementById(element).style.visibility = "visible";
				last = element;													// new 'last' = current selection
//				GEvent.trigger(gmarkers[id], "click");
				var thelat = <?php print $row_unit['lat'];?>; var thelng = <?php print $row_unit['lng'];?>;
				setDirections(lats[id] + " " + lngs[id], thelat + " " + thelng, "en_US");
				}
			}
	
		function doGrid() {
			map.addOverlay(new LatLonGraticule());
			}
	
		function handleErrors(){		//G_GEO_UNKNOWN_DIRECTIONS 
			if (gdir.getStatus().code == G_GEO_UNKNOWN_DIRECTIONS )
				alert("290: No driving directions are available to/from this location.\nError code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
				alert("292: No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_SERVER_ERROR)
				alert("294: A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_MISSING_QUERY)
				alert("296: The HTTP q parameter was either missing or had no value. For geocoder requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.\n Error code: " + gdir.getStatus().code);
	//		else if (gdir.getStatus().code == G_UNAVAILABLE_ADDRESS)  <--- Doc bug... this is either not defined, or Doc is wrong
	//			alert("296: The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_KEY)
				alert("300: The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_REQUEST)
				alert("302: A directions request could not be successfully parsed.\n Error code: " + gdir.getStatus().code);
			else alert("303: An unknown error occurred.");
			}		// end function handleErrors()
	
		function onGDirectionsLoad(){ 
			var temp = gdir.getSummaryHtml();
//			alert(extr_num(temp));
//	 		Use this function to access information about the latest load() results.
//	 			e.g.
//	 		document.getElementById("getStatus").innerHTML = gdir.getStatus().code;
//	 		and yada yada yada...
			}		// function onGDirectionsLoad()

		function guest () {
			alert ("Demonstration only.  Guests may not commit dispatch!");
			}
			
		function validate(){		// frm_id_str
			msgstr="";
			for (var i =0;i<unit_sets.length;i++) {
				if (unit_sets[i]) {
					msgstr+=unit_names[i]+"\n";
					document.routes_Form.frm_id_str.value += unit_ids[i] + ",";
					}
				}
			if (msgstr.length==0) {
				var more = (nr_units>1)? "s": ""
				alert ("Please select unit" + more + ", or cancel");
				return false;
				}
			else {
				if (confirm ("Please confirm Unit dispatch as follows\n\n" + msgstr)) {
					document.routes_Form.frm_id_str.value = document.routes_Form.frm_id_str.value.substring(0, document.routes_Form.frm_id_str.value.length - 1);	// drop trailing separator
					document.routes_Form.frm_name_str.value = msgstr;	// for re-use
					document.routes_Form.submit();
					}
				else {
					document.routes_Form.frm_id_str.value="";	
					return false;
					}
				}

			}		// end function validate()
	
		function ifexists(myarray,myid) {
			var str_key = " " + myid;		// force associative
			return ((typeof myarray[str_key])!="undefined");		// exists if not undefined
			}		// end function ifexists()
			
		var icons=[];						// note globals
		icons[1] = "./markers/YellowIcons/marker";		//e.g.,marker9.png
		icons[2] = "./markers/RedIcons/marker";
		icons[3] = "./markers/BlueIcons/marker";
		icons[4] = "./markers/GreenIcons/marker";
		icons[5] = "./markers/WhiteIcons/marker";
	
		var map;
		var center;
		var zoom;
		
	    var gdir;				// directions
	    var geocoder = null;
	    var addressMarker;
		
		var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
		side_bar_html += "<TR class='even'>	<TD colspan=99 ALIGN='center'><B>Routes to Incident <I><?php print shorten($row_unit['scope'], 20); ?></I></B></TD></TR>";
		side_bar_html += "<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for route</TD></TR>";
		side_bar_html += "<TR class='even'>	<TD COLSPAN=2></TD><TD ALIGN='center'>Unit</TD><TD ALIGN='center'>SLD</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD><TD>Assign</TD></TR>";
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
<?php if (get_variable('terrain') == 1) { ?>
		map.addMapType(G_PHYSICAL_MAP);
<?php } ?>	
		map.enableScrollWheelZoom(); 	

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
		var point = new GLatLng(<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>);	
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
		unit_names = 	new Array();				// names 
		unit_sets = 	new Array();				// settings
		unit_ids = 		new Array();				// id's
		unit_assigns = 	new Array();				// unit id's assigned this incident
		var nr_units = 	0;
<?php
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
		
//						build js array of responders to this ticket - possibly none
		$query = "SELECT `ticket_id`, `responder_id` FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . $_GET['ticket_id'];
//		dump($query);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
		while ($assigns_row = stripslashes_deep(mysql_fetch_array($result))) {
			print "\t\tunit_assigns[' '+ " . $assigns_row['responder_id']. "]= true;\n";	// note string forced
			}
		print "\n";

		$where = (empty($unit_id))? "" : " WHERE `$GLOBALS[mysql_prefix]responder`.`id` = $unit_id ";		// revised 5/23/08 per AD7PE 
		$query = "SELECT *, UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]responder`.`id` AS `unit_id`, `s`.`status_val` AS `unitstatus` FROM $GLOBALS[mysql_prefix]responder
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`$GLOBALS[mysql_prefix]responder`.`un_status_id` = `s`.`id`)
			$where
			ORDER BY `name` ASC, `unit_id` ASC";	

//		dump($query);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if(mysql_affected_rows()>0) {
													// major while ... for RESPONDER data starts here
			$i = 1;				// sidebar/icon index
			while ($unit_row = stripslashes_deep(mysql_fetch_array($result))) {
?>
				nr_units++;
				var i = <?php print $i;?>;						// top of loop
				unit_names[i] = '<?php print $unit_row['name'];?>';	// unit name
				unit_sets[i] = false;								// pre-set checkbox settings				
				unit_ids[i] = <?php print $unit_row['unit_id'];?>;
				distances[i]=9999.9;
<?php
				$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "px'>";
				$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . shorten($unit_row['name'], 48) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $unit_row['description']), 32) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Status:</TD><TD>" . $unit_row['unitstatus'] . " </TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>Contact:</TD><TD>" . $unit_row['contact_name']. " Via: " . $unit_row['contact_via'] . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($unit_row['updated']) . "</TD></TR>";
				$tab_1 .= "</TABLE>";
?>
				new_element = document.createElement("input");								// please don't ask!
				new_element.setAttribute("type", 	"checkbox");
				new_element.setAttribute("name", 	"unit_<?php print $unit_row['unit_id'];?>");
				new_element.setAttribute("id", 		"element_id");
				new_element.setAttribute("style", 	"visibility:hidden");
				document.forms['routes_Form'].appendChild(new_element);
				var dist_mi = "na";
<?php
				if (intval($unit_row['mobile'])==1) {
					$thespeed = "na";
					$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS packet_date, UNIX_TIMESTAMP(updated) AS updated FROM $GLOBALS[mysql_prefix]tracks
						WHERE `source`= '$unit_row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";
//					dump ($query);
					$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_affected_rows()>0) {		// got a track?
						$track_row = stripslashes_deep(mysql_fetch_array($result_tr));			// most recent track report
			
						$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "px'>";
						$tab_2 .= "<TR><TH CLASS='even' COLSPAN=2>" . $track_row['source'] . "</TH></TR>";
						$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $track_row['course'] . ", Speed:  " . $track_row['speed'] . ", Alt: " . $track_row['altitude'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $track_row['closest_city'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $track_row['status'] . "</TD></TR>";
						$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($track_row['packet_date']) . "</TD></TR>";
						$tab_2 .= "</TABLE>";
?>
						var myinfoTabs = [
							new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['name'], 8));?>", "<?php print $tab_1;?>"),
							new GInfoWindowTab("<?php print $track_row['source']; ?>", "<?php print $tab_2;?>"),
							new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
							];
		
						lats[i] = <?php print $track_row['latitude'];?>;									// now compute distance - in km
						lngs[i] = <?php print $track_row['longitude'];?>;
						distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_unit['lat'];?>), parseFloat(<?php print $row_unit['lng'];?>));
					    var km2mi = <?php print $conversion ;?>;				// 
						var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to miles
<?php
						$thespeed = ($track_row['speed'] == 0)?"<FONT COLOR='red'><B>&bull;</B></FONT>"  : "<FONT COLOR='green'><B>&bull;</B></FONT>" ;
						if ($track_row['speed'] >= 50) { $thespeed = "<FONT COLOR='WHITE'><B>&bull;</B></FONT>";}
?>
						var point = new GLatLng(<?php print $track_row['latitude'];?>, <?php print $track_row['longitude'];?>);	// mobile position
						bounds.extend(point);															// point into BB
<?php
						}			// end if (mysql_affected_rows()>0;) for track data
					else {				// no track data
?>
						var dist_mi = "na";
<?php						
						}				// end  no track data
?>						
					sidebar_line = "<TD><?php print shorten($unit_row['name'], 32);?></TD><TD>"+ dist_mi+"</TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print shorten($unit_row['unitstatus'], 12);?></TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print $thespeed;?></TD>";
					sidebar_line += "<TD CLASS='td_data'><?php print format_sb_date($unit_row['updated']);?></TD>";
					var is_checked = (ifexists(unit_assigns,'<?php print $unit_row['unit_id'];?>'))? " CHECKED ": "";
					var is_disabled = (ifexists(unit_assigns,'<?php print $unit_row['unit_id'];?>'))? " DISABLED ": "";
					sidebar_line += "<TD ALIGN='center'><INPUT TYPE='checkbox' " + is_checked + is_disabled + " NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='unit_sets[<?php print $i; ?>]=this.checked;'></TD>";
					var marker = createMarker(point, sidebar_line, myinfoTabs,<?php print $unit_row['type'];?>, i);	// (point,sidebar,tabs, color, id)
					map.addOverlay(marker);
<?php
					}		// if mobile
			
					else {				// fixed position with location info.
?>
						var myinfoTabs = [
							new GInfoWindowTab("<?php print nl2brr(shorten($unit_row['name'], 12));?>", "<?php print $tab_1;?>"),
							new GInfoWindowTab("Zoom", "<DIV ID='detailmap' CLASS='detailmap'></DIV>")
							];
						
						lats[i] = <?php print $unit_row['lat'];?>;									// now compute distance - in km
						lngs[i] = <?php print $unit_row['lng'];?>;
						distances[i] = distCosineLaw(parseFloat(lats[i]), parseFloat(lngs[i]), parseFloat(<?php print $row_unit['lat'];?>), parseFloat(<?php print $row_unit['lng'];?>));	// note: km
					    var km2mi = <?php print $conversion ;?>;				// 
						var dist_mi = ((distances[i] * km2mi).toFixed(1)).toString();				// to feet
			
						sidebar_line = "<TD><?php print shorten($unit_row['name'], 16);?></TD><TD>"+ dist_mi+"</TD>";
						sidebar_line += "<TD CLASS='td_data'><?php print shorten($unit_row['unitstatus'],12);?></TD>";
						sidebar_line += "<TD CLASS='td_data'></TD><TD CLASS='td_data'><?php print format_sb_date($unit_row['updated']);?></TD>"; 
						var is_checked = (ifexists(unit_assigns,'<?php print $unit_row['unit_id'];?>'))? " CHECKED ": "";
						var is_disabled = (ifexists(unit_assigns,'<?php print $unit_row['unit_id'];?>'))? " DISABLED ": "";
						sidebar_line += "<TD ALIGN='center'><INPUT TYPE='checkbox' " + is_checked  + is_disabled + " NAME = 'unit_" + <?php print $unit_row['unit_id'];?> + "' onClick='unit_sets[<?php print $i; ?>]=this.checked;'></TD>";
		
						var point = new GLatLng(<?php print $unit_row['lat'];?>, <?php print $unit_row['lng'];?>);	// for each responder
						bounds.extend(point);																// point into BB
						
						var marker = createMarker(point, sidebar_line, myinfoTabs, <?php print $unit_row['type'];?>, i);	// (point,sidebar,tabs, color, id)
						map.addOverlay(marker);						
<?php
						}				// end if/else (mysql_affected_rows()>0;) - no track data

				$i++;
				}				// end major while ($unit_row = ...) for each responder
			}				// end if(mysql_affected_rows()>0)
			
//					responders complete
?>
		if (nr_units==0) {
			side_bar_html +="<TR CLASS='odd'><TD ALIGN='center' COLSPAN=99><BR /><B>No Units!</B></TD></TR>";;		
			map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);		// -1 for further out	
			map.setCenter(center,zoom);
			side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=99>&nbsp;</TD></TR>";
			side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=99 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>";
			side_bar_html+= "<TR><TD>&nbsp;</TD></TR>";
<?php
			$thefunc = (is_guest())? "guest()" : "validate()";		// reject guest attempts
?>
			side_bar_html+= "<TR><TD COLSPAN=99 ALIGN='center'><INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();'>";
			side_bar_html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' value='DISPATCH SELECTED UNITS' onClick = '<?php print $thefunc;?>' />";
			side_bar_html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='RESET' VALUE='Reset' onClick = 'doReset()'>";
			side_bar_html+= "</TD></TR>";
			}
	
		side_bar_html +="</TABLE>\n";
		document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div
	
		var thelat = <?php print $row_unit['lat'];?>; var thelng = <?php print $row_unit['lng'];?>;
		var start = min(distances);		// min straight-line distance to Incident
		if (start>0) {
			var last= "R"+start;			//
			document.getElementById(last).style.visibility = "visible";		// show link check image at the selected sidebar element
			setDirections(lats[start] + " " + lngs[start], thelat + " " + thelng, "en_US");
			}	
		}		// end if (GBrowserIsCompatible())

	else {
		alert("Sorry,  browser compatibility problem. Contact your tech support group.");
		}
	</SCRIPT>
	
<?php
	}				// end function do_list() ===========================================================
	
?>