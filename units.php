<?php
/*
5/23/08	added check for associated assign records before allowing deletions line 843 area
5/29/08	addded do_kml calls 
6/1/08	revised 'type' display
6/1/08	added latest APRS data to 'view' display
6/2/08  do_log revisions for explicit parameter values
6/7/08  added show incidents for dispatch -  
6/10/08 added js trim function numerous JS checks
6/11/08 revised mobile/fixed JS datatypes to radio buttons
6/15/08	revised terminology to 'Station', 'Mobile unit'
6/17/08	added tracks handling for date/time, per tracks.php
6/25/08	added APRS window handling
6/27/08	added condionality for Dispatch button
6/27/08	added conditional to detect active dispatches
8/02/08	added link to dispatch function in infowindow
8/3/08	added dispatch target to unit infowin
8/23/08	added usng position, with 'other' as repository	
8/23/08	corrected theForm.frm_m_or_f disable
8/24/08 added TITLE to TD's
8/26/08 added default lat/lng to new mobile units (needs usng conversions)
9/3/08  NULL to mobile units center
9/9/08	added selectable lat/lng formats
9/13/08	LLtoUSNG implemented, replacing 8/23/08 rev's above
10/4/08 bypass position check if mode = edit
10/4/08 set auto-refresh if APRS active
10/6/08	renamed validate_res
10/6/08	bypass map check if in edit.
10/8/08	operator-level may now create/edit Units
10/14/08 added grid function
10/15/08 added check for empty arguments
10/15/08 $frm_ngs removed fm update/insert
10/16/08 changed ticket_id to frm_ticket_id
10/25/08 unchanged 10/16/08 entry
11/3/08 single call for graticule.js, relocated gmaps call
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));

do_aprs();

if ($istest) {
	if (!empty($_GET)) {dump ($_GET);}
	if (!empty($_POST)) {dump ($_POST);}
	}
extract($_GET);
extract($_POST);

$types = array();	$types[$GLOBALS['TYPE_EMS']] = "EMS";	$types[$GLOBALS['TYPE_FIRE']] = "Fire";
					$types[$GLOBALS['TYPE_COPS']] = "Police";	$types[$GLOBALS['TYPE_MUTU']] = "Mutual";	$types[$GLOBALS['TYPE_OTHR']] = "Other";

$interval = intval(get_variable('aprs_poll'));
$refresh = ($interval>0)? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>": "";	//10/4/08
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Configuration Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">

<?php print $refresh; ?>	<!-- 10/4/08 -->

	<META HTTP-EQUIV="Script-date" CONTENT="8/24/08">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT DEFER SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT> <!-- 11/3/08 -->
	<SCRIPT DEFER SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>	<!-- 8/23/08 -->
	<SCRIPT DEFER SRC='./js/graticule.js' type='text/javascript'></SCRIPT> <!-- 70 -->
	
	<SCRIPT DEFER>
//	 S C R I P T DEFER SRC="./incs/mapiconmaker.js" 
	
	String.prototype.trim = function () {						// added 6/10/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
	
	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();										// 11/2/08

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;	// 9/9/08

	function do_coords(inlat, inlng) { 										// 9/14/08
		if(inlat.toString().length==0) return;								// 10/15/08
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);		
		alert(str);
		}
	
	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(inval);
		d  = (inval>0)?  Math.floor(d):Math.round(d);
		var mi = (inval-d)*60;
		var m = Math.floor(mi)				// min's
		var si = (mi-m)*60;
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	function lat2ddm(inlat) {				// lat to degr, dec min's  9/7/08
		var x = new Number(inlat);
		var y  = (inlat>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return Math.abs(y) + '\260 ' + z +"'" + nors;
		}
	
	function lng2ddm(inlng) {				// lng to degr, dec min's 
		var x = new Number(inlng);
		var y  = (inlng>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return Math.abs(y) + '\260 ' + z +"'" + eorw;
		}
	
	function do_lat_fmt(inlat) {				// 9/9/08
		switch(lat_lng_frmt) {
		case 0:
			return inlat;
		  	break;
		case 1:
			return ll2dms(inlat);
		  	break;
		case 2:
			return lat2ddm(inlat);
		 	break;
		default:
			alert ("invalid LL format selector");
			}	
		}

	function do_lng_fmt(inlng) {
		switch(lat_lng_frmt) {
		case 0:
			return inlng;
		  	break;
		case 1:
			return ll2dms(inlng);
		  	break;
		case 2:
			return lng2ddm(inlng);
		 	break;
		default:
			alert ("invalid LL format selector");
			}	
		}

	var grid_obj = new LatLonGraticule();;				// 11/2/08
	var grid_bln = false;
	function doGrid() {
		if (grid_bln) {
			map.removeOverlay(grid_obj);
			}
		else {
			map.addOverlay(grid_obj);
			}
		grid_bln = !grid_bln;				// flip
		}				// end function do Grid()

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && document.getElementById) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && document.getElementById) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}
		
	var starting = false;

	function do_aprs_window() {				// 6/25/08
		var url = "http://www.openaprs.net/?center=" + <?php print get_variable('def_lat');?> + "," + <?php print get_variable('def_lng');?>;
		var spec ="titlebar, resizable=1, scrollbars, height=640,width=640,status=0,toolbar=0,menubar=0,location=0, left=50,top=250,screenX=50,screenY=250";
		newwindow=window.open(url, 'openaprs',  spec);
		if (isNull(newwindow)) {
			alert ("APRS display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}				// end function

	function do_track(callsign) {
		if (parent.frames["upper"].logged_in()) {
//			if(starting) {return;}					// 6/6/08
//			starting=true;	
			map.closeInfoWindow();
			var width = <?php print get_variable('map_width');?>+360;
			var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
			var url = "track_u.php?source="+callsign;
			
			newwindow=window.open(url, callsign,  spec);
			if (isNull(newwindow)) {
				alert ("Track display requires popups to be enabled. Please adjust your browser options.");
				return;
				}
//			starting = false;
			newwindow.focus();
			}
		}				// end function

	function to_routes(id) {
		document.routes_Form.ticket_id.value=id;				// 10/16/08, 10/25/08
		document.routes_Form.submit();
		}
	
	function whatBrows() {					//Displays the generic browser type
		window.alert("Browser is : " + type);
		}
	
	function ShowLayer(id, action){												// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
		if (type=="IE") 				eval("document.all." + id + ".style.display='" + action + "'");  	// id is the span/layer, action is either hidden or visible
		if (type=="NN") 				eval("document." + id + ".display='" + action + "'");
		if (type=="MO" || type=="OP") 	eval("document.getElementById('" + id + "').style.display='" + action + "'");
		}
	
	function hideit (elid) {
		ShowLayer(elid, "none");
		}
	
	function showit (elid) {
		ShowLayer(elid, "block");
		}

	function validate(theForm) {						// Responder form contents validation	10/6/08
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				var str = "Please confirm removing '" + theForm.frm_name.value + "'";
				if(confirm(str)) 	{return true;}
				else 				{return false;}
				}
			}
		var errmsg="";
		var got_type = false;
		for (i=0; i<theForm.frm_type.length; i++){
			if (theForm.frm_type[i].checked) {	got_type = true;	}
			}
		if (theForm.frm_name.value.trim()=="")				{errmsg+="\tUnit NAME is required.\n";}
		if (theForm.frm_descr.value.trim()=="")				{errmsg+="\tUnit DESCRIPTION is required.\n";}
		if (theForm.frm_un_status_id.value.trim()==0)		{errmsg+="\tUnit STATUS is required.\n";}
		if (!got_type)										{errmsg+="\tUnit TYPE is required.\n";}

		if (document.res_edit_Form) {							// 10/6/08
			theForm.frm_lat.disabled = true;					// prevent posting
			theForm.frm_lng.disabled = true;
			}
		else {													// add form
			var gotmap = false;
			if (theForm.frm_lat) {
				theForm.frm_lat.disabled=false;					// set map boolean
				gotmap = (!theForm.frm_lat.value == "");
				theForm.frm_lat.disabled=true;
				}
			if (theForm.frm_mobile.value==1) {	
				if (gotmap) 							 		{errmsg+= "\tMAP LOCATION not allowed for mobile units\n";}
				if (theForm.frm_callsign.value.trim()=="") 		{errmsg+= "\tCALL SIGN is required for mobile units\n";}
				else {
					for (i=0; i< calls.length;i++) {	// duplicate?
						if (calls[i] == theForm.frm_callsign.value.trim()) {
							errmsg+="\tDuplicate CALLSIGN - not permitted.\n";
							break;
							}			
						}		// end dupl check
					}		// end else ...
				}		// end theForm.frm_mobile.value==1
				
			else {				// fixed-location unit
				if (!gotmap) 								{errmsg+= "\tMAP LOCATION is required for Stations\n";}
				}
				
			}			
			
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {										// good to go!
			if (theForm.frm_mobile.value==0) {		// mobile, no coords
				theForm.frm_lat.disabled = false;
				theForm.frm_lng.disabled = false;
//				theForm.frm_ngs.disabled = false;						// 8/23/08, 10/15/08
				theForm.frm_callsign.disabled = false;
				}
			if (theForm.frm_m_or_f) {theForm.frm_m_or_f.disabled = true;}		// 8/23/08
			return true;
			}
		}				// end function validate res(theForm)

	function add_res () {		// turns on add responder form
		showit('res_add_form'); 
		hideit('tbl_responders');
		hideIcons();			// hides responder icons
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
		
	function hideIcons() {
		map.clearOverlays();
		}				// end function hideicons() 

	function do_lat (lat) {							// 9/14/08
		document.forms[0].frm_lat.value=lat.toFixed(6);			// 9/9/08	
		document.forms[0].show_lat.disabled=false;
		document.forms[0].show_lat.value=do_lat_fmt(document.forms[0].frm_lat.value);
		document.forms[0].show_lat.disabled=true;
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.value=lng.toFixed(6);			// 9/9/08	
		document.forms[0].show_lng.disabled=false;
		document.forms[0].show_lng.value=do_lng_fmt(document.forms[0].frm_lng.value);
		document.forms[0].show_lng.disabled=true;
		}
	
	function do_ngs() {
		document.forms[0].frm_ngs.disabled=false;
		document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
		document.forms[0].frm_ngs.disabled=true;
		}

	function collect(){				// constructs a string of id's for deletion
		var str = sep = "";
		for (i=0; i< document.del_Form.elements.length; i++) {
			if (document.del_Form.elements[i].type == 'checkbox' && (document.del_Form.elements[i].checked==true)) {
				str += (sep + document.del_Form.elements[i].name.substring(1));		// drop T
				sep = ",";
				}
			}
		document.del_Form.idstr.value=str;	
		}
		
	function all_ticks(bool_val) {									// set checkbox = true/false
		for (i=0; i< document.del_Form.elements.length; i++) {
			if (document.del_Form.elements[i].type == 'checkbox') {
				document.del_Form.elements[i].checked = bool_val;		
				}
			}			// end for (...)
		}				// end function all ticks()
		
	function do_disp(){												// show incidents for dispatch - added 6/7/08
		document.getElementById('incidents').style.display='block';
		document.getElementById('view_unit').style.display='none';
		}

	function set_f(the_form) {		// position: fixed - 6/11/08
//		the_form.frm_callsign.disabled = true;
		the_form.frm_mobile.value=0;
		}
	function set_m(the_form) {		// position: mobile - 6/11/08
//		the_form.frm_callsign.disabled = false;
		map.clearOverlays();
		
		document.forms[0].frm_lat.disabled=false;
		document.forms[0].frm_lat.value="";
		document.forms[0].frm_lat.disabled=true;
		
		document.forms[0].frm_lng.disabled=false;
		document.forms[0].frm_lng.value="";
		document.forms[0].frm_lng.disabled=true;

		the_form.frm_mobile.value=1;		
		}

	</SCRIPT>
	

<?php

function list_responders($addon = '', $start) {
	global $types, $my_session;

	$assigns = array();					// 08/8/3
	$tickets = array();					// ticket id's
	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";

	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);
	$calls = array();									// 6/17/08
	$calls_nr = array();
	$calls_time = array();
	
	$query = "SELECT * , UNIX_TIMESTAMP(packet_date) AS `packet_date` FROM `$GLOBALS[mysql_prefix]tracks` ORDER BY `packet_date` ASC";		// 6/17/08
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	while ($row = mysql_fetch_array($result)) {
		if (isset($calls[$row['source']])) {		// array_key_exists ( mixed key, array search )
			$calls_nr[$row['source']]++;
			}
		else {
//			array_push ($calls, trim($row['source']));
			$calls[trim($row['source'])] = TRUE;
			$calls_nr[$row['source']] = 1;
			}
		$calls_time[$row['source']] = $row['packet_date'];		// save latest - note query order
		}

//	dump($calls);
//	dump($calls_nr);
//	dump($calls_time);

?>

<SCRIPT DEFER>

var color=0;
	var colors = new Array ('odd', 'even');

	function hideGroup(color) {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				if (gmarkers[i].id == color) {
					gmarkers[i].show();
					}
				else {
					gmarkers[i].hide();
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		elem = document.getElementById("allIcons");
		elem.style.visibility = "visible";
		}			// end function

	function showAll() {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
		elem = document.getElementById("allIcons");
		elem.style.visibility = "hidden";

		}			// end function
	
	function get_icon(my_star, my_label, my_color) {
		var theIcon = MapIconMaker.createLabeledMarkerIcon({addStar: my_star, label: my_label, primaryColor: my_color});
		return theIcon
		}
	

	function createMarker(point,tabs, color, id) {				// Creates marker and sets up click event infowindow
		points = true;											// at least one
		var icon = new GIcon(listIcon);
		icon.image = icons[color] + ((id % 100)+1) + ".png";	// e.g.,marker9.png, 100 icons limit
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
			detailmap.setCenter(point, 13);  					// larger # = closer
			detailmap.addOverlay(marker);
			});

		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
		bounds.extend(point);									// extend the bounding box
		return marker;
		}				// end function create Marker()
		
	function do_sidebar (sidebar, id) {
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (id+1) + ". "+ sidebar +"</TD></TR>\n";
		}

	function do_sidebar_nm (sidebar, line_no, rcd_id) {							// no map - view responder // view_Form
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + rcd_id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + (line_no+1) + ". "+ sidebar +"</TD></TR>\n";
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [id]
		GEvent.trigger(gmarkers[id], "click");
		}

	function do_lat (lat) {
		document.forms[0].frm_lat.disabled=false;
		document.forms[0].frm_lat.value=lat.toFixed(6);
		document.forms[0].frm_lat.disabled=true;
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.disabled=false;
		document.forms[0].frm_lng.value=lng.toFixed(6);
		document.forms[0].frm_lng.disabled=true;
		}
	
	function do_ngs() {
		document.forms[0].frm_lat.disabled=false;
		document.forms[0].frm_lng.disabled=false;
		document.forms[0].frm_ngs.disabled=false;
		document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
		document.forms[0].frm_ngs.disabled=true;
		document.forms[0].frm_lat.disabled=true;
		document.forms[0].frm_lng.disabled=true;	
		}
	var icons=[];						// note globals
	icons[1] = "./markers/YellowIcons/marker";		// e.g.,marker9.png
	icons[2] = "./markers/RedIcons/marker";			// BlueIcons/GreenIcons/YellowIcons/RedIcons/WhiteIcons
	icons[3] = "./markers/BlueIcons/marker";		// see GLOBALS re ordering
	icons[4] = "./markers/GreenIcons/marker";
	icons[5] = "./markers/WhiteIcons/marker";

	var map;
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
	side_bar_html += "<TR class='even'>	<TD colspan=99 ALIGN='center'><B>Units</B></TD></TR>";
	side_bar_html += "<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for details - or to dispatch</TD></TR>";
	side_bar_html += "<TR class='even'>	<TD></TD><TD ALIGN='center'>Name</TD><TD ALIGN='center'>Description</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD></TR>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = <?php print $start; ?>;					// sidebar/icon index
	var points = false;								// none

	map = new GMap2(document.getElementById("map"));		// create the map
	map.addControl(new GSmallMapControl());					// 10/6/08
	map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>

	var bounds = new GLatLngBounds();						// create  bounding box
//	map.addControl(new GOverviewMapControl());
	map.enableScrollWheelZoom(); 	

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

	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])
		});

<?php
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black"); 
	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);
	
	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";	//
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$aprs = FALSE;
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {		// ==========  major while() for RESPONDER ==========
	
		$todisp = (is_guest())? "": "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&disp=true&id=" . $row['id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";	// 08/8/02
//		$toedit = ((is_administrator() || is_super()))?  "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;":"" ;
		$toedit = (is_guest())? "" :"&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;	// 10/8/08
		$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U></SPAN>" ;

		if (intval($row['mobile'])==0) {
			$mode = 0;																			// for fixed units
			print "\t\tvar point = new GLatLng(" . $row['lat'] .", " .  $row['lng'] . ");\n";	// fixed position
			print "\t\tpoints=true;\n";
			}			// end fixed
		else {								// is mobile, any tracks?
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
			if (mysql_affected_rows()>0) {		// got track stuff. do tab 2 and 3
				$rowtr = stripslashes_deep(mysql_fetch_array($result_tr));
				$mode = ($rowtr['speed'] == 0)? 1: 2 ;
				if ($rowtr['speed'] >= 50) { $mode = 3;}
				print "\t\tvar point = new GLatLng(" . $rowtr['latitude'] . ", " . $rowtr['longitude'] . ");\n";	// latest mobile position
				print "\t\tpoints=true;\n";			
				}				// end got tracks 
			else {				// mobile unit but no track data
				$mode = 4;			
				}			// end if/else (mysql_affected_rows()>0;) - no track data
			}		// end mobile
//										common to all modes
//		dump ($mode);
		$the_bull = ($mode == 0)? "" : "<FONT COLOR=" . $bulls[$mode] ."><B>&bull;</B></FONT>";
			
		$sidebar_line = "<TD TITLE = '" . htmlentities ($row['name'], ENT_QUOTES) . "'>" . shorten($row['name'], 30) . "</TD><TD TITLE = '" . htmlentities ($row['description'], ENT_QUOTES) . "' >" . shorten(str_replace($eols, " ", $row['description']), 16) . "</TD>"; 	// 8/24/08
		$temp = $row['un_status_id'];

		$sidebar_line .= "<TD CLASS='td_data' TITLE = '" . htmlentities ($status_vals[$temp], ENT_QUOTES) . "'> " . shorten($status_vals[$temp], 10) . "</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";

//		$the_time = (isset($calls[$row['callsign']]))? $calls_time[$row['callsign']]: $row['updated'];		// latest report time
		if (isset($calls[$row['callsign']])) {
//			print __LINE__;
			$the_time = $calls_time[$row['callsign']];
			$the_class = "emph";
			$aprs = TRUE;				// show footer legend
			}
		else {
			$the_time = $row['updated'];
			$the_class = "td_data";
			}
		
		$sidebar_line .= "<TD CLASS='$the_class'> " . format_sb_date($the_time) . "</TD>";				// 6/17/08

		print "\tvar do_map = true;\n";		// default

		$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
		$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $types[$row['type']] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $status_vals[$temp] . " </TD></TR>";
		$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
		$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
		if (array_key_exists($row['id'], $assigns)) { 
			$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['id']] . "'>" . shorten($assigns[$row['id']], 20) . "</A></TD></TR>";
			}		
		$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $todisp . $totrack . $toedit . "&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
		$tab_1 .= "</TABLE>";

		switch ($mode) {
			case 0:				// not mobile
?>			
				do_sidebar ("<?php print $sidebar_line; ?>", i);
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php
			    break;
			case 1:				// stopped
			case 2:				// moving
			case 3:				// fast
?>			
				do_sidebar ("<?php print $sidebar_line; ?>", i);
<?php			
				$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $rowtr['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $rowtr['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $rowtr['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($rowtr['packet_date']) . "(UTC)</TD></TR>";
				$tab_2 .= "</TABLE>";
?>

				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("<?php print $rowtr['source']; ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php			
			    break;
			case 4:				// mobile - no track
?>
				do_sidebar_nm ("<?php print $sidebar_line; ?>", i, <?php print $row['id'];?>);	// special sidebar link - adds id for view
				var do_map = false;
<?php			
			    break;
			default:
			    echo "mode error: $mode";
			    break;
			}		// end switch
?>
			if (do_map) {
				var marker = createMarker(point, myinfoTabs,<?php print $row['type'];?>, i);	// (point,tabs, color, id)
				map.addOverlay(marker);
				}
			i++;				// zero-based
<?php

		}				// end  ==========  while() for RESPONDER ==========
	$aprs_legend = ($aprs)? "<TD CLASS='emph' ALIGN='center'>APRS time</TD>": "<TD></TD>";
?>
	if (!points) {		// any?
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}
	else {
		center = bounds.getCenter();
		zoom = map.getBoundsZoomLevel(bounds)-1;
		map.setCenter(center,zoom);
		}
	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=5>&nbsp;</TD><?php print $aprs_legend;?></TR>";
	side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=6 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>";
<?php

	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	document.getElementById("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div

<?php
	do_kml();
?>


</SCRIPT>
<?php
	}				// end function list_responders() ===========================================================
	
function map($mode, $lat, $lng, $icon) {						// Responder add, edit, view
?>

<SCRIPT DEFER>
		var mode = "<?php print $mode; ?>";
		function writeConsole(content) {
			top.consoleRef=window.open('','myconsole',
				'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=0' +',resizable=1')
		 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
				+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></HTML>'
				)				// end top.consoleRef.document.writeln()
		 	top.consoleRef.document.close();
			}				// end function writeConsole(content)
		
		function map_reset() {
			map.clearOverlays();
			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	
			map.setCenter(point, <?php print get_variable('def_zoom');?>);
			map.addOverlay(new GMarker(point, myIcon));		
			}
		function map_cen_reset() {				// reset map center icon
			map.clearOverlays();
			}
		
		var map = new GMap2(document.getElementById('map'));
								// note globals
		var myZoom;
		var marker;
		
		var myIcon = new GIcon();
		myIcon.image = "./markers/yellow.png";	
		myIcon.shadow = "./markers/sm_shadow.png";
		myIcon.iconSize = new GSize(16, 28);
		myIcon.shadowSize = new GSize(22, 20);
		myIcon.iconAnchor = new GPoint(8, 28);
		myIcon.infoWindowAnchor = new GPoint(5, 1);
		
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
//		map.addControl(new GOverviewMapControl());
		map.addMapType(G_PHYSICAL_MAP);					// 10/6/08
		map.enableScrollWheelZoom(); 	

		var tab1contents;				// info window contents - first/only tab
										// default point - possible dummy
		map.setCenter(new GLatLng(<?php print $lat; ?>, <?php print $lng; ?>), <?php print get_variable('def_zoom');?>);	// larger # => tighter zoom

<?php
		if ($icon)	{							// icon display?
?>		
	 		var point = new GLatLng(<?php print $lat . ", " . $lng; ?>);
			var marker = new GMarker(point, {icon: myIcon, draggable:false});
	 		map.addOverlay(new GMarker(point, myIcon));
<?php
			}		// end if ($icon)
		else {
?>
		var baseIcon = new GIcon();				// 9/16/08
		baseIcon.iconSize=new GSize(32,32);
		baseIcon.iconAnchor=new GPoint(16,16);
		var cross = new GIcon(baseIcon, "./markers/crosshair.png", null);
		var center = new GLatLng(<?php print get_variable('def_lat') ?>, <?php print get_variable('def_lng'); ?>);
		map.setCenter(center, <?php print get_variable('def_zoom');?>);
		var thisMarker  = new GMarker(center, {icon: cross, draggable:false} );				// 9/16/08
		map.addOverlay(thisMarker);

<?php
			}							// end else
			
		if (!($mode=="v")) {						// disallow if view mode
?>
	var the_zoom = <?php print get_variable('def_zoom');?>;

	map.enableScrollWheelZoom();
	if ((mode=="a") || ((mode=="e") && (document.forms[0].frm_mobile.value==0))){	

		the_marker = new GMarker(map.getCenter(), {draggable: true	});
	
		GEvent.addListener(map, "click", function(overlay, latlng) {
//			alert(795);
			if(document.forms[0].frm_mobile.value==1) {
				alert("Map position not allowed for mobile units!");
				return;
				}

			if (latlng) {
				map.clearOverlays();
				marker = new GMarker(latlng, {draggable:true});
				map.setCenter(marker.getPoint(), the_zoom);
				do_lat(marker.getPoint().lat());			// set form values
				do_lng(marker.getPoint().lng());
				do_ngs();									// 8/22/08
	
				GEvent.addListener(marker, "dragend", function() {
					map.setCenter(marker.getPoint(), 13);
					do_lat (marker.getPoint().lat());		// set form values
					do_lng (marker.getPoint().lng());
					do_ngs();								// 8/22/08
					
					});
				map.addOverlay(marker);
				}		// end if (latlng)
			});		// end GEvent.addListener()
	
		}		//  end if ((mode=="a") ...
<?php
			}				// end if ($mode=="v")

		do_kml();			// kml functions
?>			
			
	</SCRIPT>
<?php
	}		// end function map()

	function finished ($caption) {
		print "</HEAD><BODY>";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
		print "</FORM></BODY></HTML>";	
		}

	function do_calls($id = 0) {				// generates js callsigns array
		$print = "\n<SCRIPT DEFER>\n";
		$print .="\t\tvar calls = new Array();\n";
		$query	= "SELECT `id`, `callsign` FROM `$GLOBALS[mysql_prefix]responder` where `id` != $id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		while($row = stripslashes_deep(mysql_fetch_array($result))) {
			if (!empty($row['callsign'])) {
				$print .="\t\tcalls.push('" .$row['callsign'] . "');\n";
				}
			}				// end while();
		$print .= "</SCRIPT>\n";
		return $print;
		}		// end function do calls()

	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";
	$_dodisp = 			(array_key_exists ('disp',$_GET ))? $_GET['disp']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Responder	
		$query = "DELETE FROM $GLOBALS[mysql_prefix]responder WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Unit <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$station = (intval($frm_mobile)==0);			// 
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
				`name`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",
				`capab`= " . 		quote_smart(trim($_POST['frm_capab'])) . ",
				`un_status_id`= " . quote_smart(trim($_POST['frm_un_status_id'])) . ",
				`callsign`= " . 	quote_smart(trim($_POST['frm_callsign']));

			if ($station) {
				$query .= ",
				`lat`= " . 			quote_smart(trim($_POST['frm_lat'])) . ",
				`lng`= " . 			quote_smart(trim($_POST['frm_lng']));			
				}
			$query .= ", 				
				`contact_name`= " . quote_smart(trim($_POST['frm_contact_name'])) . ",
				`contact_via`= " . 	quote_smart(trim($_POST['frm_contact_via'])) . ",
				`type`= " . 		quote_smart(trim($_POST['frm_type'])) . ",
				`user_id`= " . 		quote_smart(trim($my_session['user_id'])) . ",
				`updated`= " . 		quote_smart(trim($now)) . " 
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

//			dump ($query);	
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_UNIT_STATUS'], 0, $_POST['frm_id'], $_POST['frm_un_status_id']);}	// 6/2/08 
			$mobstr = ($frm_mobile)? "Mobile Unit": "Station ";
			$caption = "<B>" . $mobstr . " '<i>" . stripslashes_deep($_POST['frm_name']) . "</i>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}
		
	if ($_getgoadd == 'true') {

		$is_mobile = ($_POST['frm_mobile']==1);		// set boolean
//		$frm_lat = ($is_mobile)? get_variable('def_lat'): quote_smart(trim($_POST['frm_lat']));		// 8/26/08
//		$frm_lng = ($is_mobile)? get_variable('def_lng'): quote_smart(trim($_POST['frm_lng']));
		$frm_lat = ($is_mobile)? 'NULL': quote_smart(trim($_POST['frm_lat']));						// 9/3/08
		$frm_lng = ($is_mobile)? 'NULL': quote_smart(trim($_POST['frm_lng']));						// 9/3/08
//		$frm_ngs = ($is_mobile)? 'NULL': quote_smart(trim($_POST['frm_ngs']));						// 10/15/08
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]responder` (
			`name`, `description`, `capab`, `un_status_id`, `callsign`, `mobile`, `contact_name`, `contact_via`, `lat`, `lng`, `type`, `user_id`, `updated` ) 
			VALUES (" .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_descr'])) . "," .
				quote_smart(trim($_POST['frm_capab'])) . "," .
				quote_smart(trim($_POST['frm_un_status_id'])) . "," .
				quote_smart(trim($_POST['frm_callsign'])) . "," .
				$frm_mobile . "," .
				quote_smart(trim($_POST['frm_contact_name'])) . "," .
				quote_smart(trim($_POST['frm_contact_via'])) . "," .
				$frm_lat . "," .
				$frm_lng . "," .
				quote_smart(trim($_POST['frm_type'])) . "," .
				quote_smart(trim($my_session['user_id'])) . "," .
				quote_smart(trim($now)) . ");";								// 8/23/08

//		dump($query);

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
//		do_log($GLOBALS['LOG_UNIT_STATUS'], mysql_insert_id(), $_POST['frm_un_status_id']);
		do_log($GLOBALS['LOG_UNIT_STATUS'], 0, mysql_insert_id(), $_POST['frm_un_status_id']);	// 6/2/08 

		$mobstr = ($frm_mobile)? "Mobile Unit ": "Station ";
		$caption = "<B>" .$mobstr . " <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";
		
		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================	
// add ===========================================================================================================================	
// add ===========================================================================================================================	

	if ($_getadd == 'true') {
		print do_calls();		// call signs to JS array for validation
?>		
		</HEAD>
		<BODY  onLoad = "ck_frames()" onunload="GUnload()">
		<FONT CLASS="header">Add Unit</FONT><BR /><BR />
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER="0" ID='addform'>
		<FORM NAME= "res_add_Form" METHOD="POST" onSubmit="return validate(document.res_add_Form);" ACTION="units.php?func=responder&goadd=true">
		<TR CLASS = "even" VALIGN='top'><TD CLASS="td_label">Unit category:</TD>		
			<TD ALIGN='center'>	<!-- // 6/11/08, 6/15/08 -->
				Station &raquo;<INPUT TYPE="radio" VALUE="" NAME="frm_m_or_f" <?php print "" ;?> onClick = 'set_f(this.form)' CHECKED/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Mobile unit &raquo;<INPUT TYPE="radio" VALUE="" NAME="frm_m_or_f" <?php print "";?>  onClick = 'set_m(this.form)'/>
			</TD></TR>
		<TR CLASS = "odd" VALIGN='middle'><TD CLASS="td_label">Type: <font color='red' size='-1'>*</font></TD><TD><FONT SIZE='-2'>
		   EMS  &raquo;<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_EMS'];?>"  NAME="frm_type">   &nbsp;
		   Fire &raquo;<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_FIRE'];?>" NAME="frm_type">   &nbsp;
		   Police &raquo;<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_COPS'];?>" NAME="frm_type"> &nbsp;
		   Mutual &raquo;<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_MUTU'];?>" NAME="frm_type"> &nbsp;
		   Other &raquo;<INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_OTHR'];?>" NAME="frm_type">  &nbsp;

			</TD></TR>

		<TR CLASS = "even"><TD CLASS="td_label">Status: <font color='red' size='-1'>*</font></TD>
			<TD><SELECT NAME="frm_un_status_id" onChange = "document.res_add_Form.frm_log_it.value='1'">
				<OPTION VALUE=0 SELECTED>Select one</OPTION>
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		if ($the_grp != $row_st['group']) {
			print ($i == 0)? "": "\t</OPTGROUP>\n";
			$the_grp = $row_st['group'];
			print "\t<OPTGROUP LABEL='$the_grp'>\n";
			}
		print "\t<OPTION VALUE=' {$row_st['id']}'  CLASS='{$row_st['group']}' title='{$row_st['description']}'> {$row_st['status_val']} </OPTION>\n";
		$i++;
		}		// end while()
	print "\n</OPTGROUP>\n";
	unset($result_st);
?>
		</SELECT>
		&nbsp;&nbsp;&nbsp;&nbsp;Callsign: <INPUT SIZE="12" MAXLENGTH="12" TYPE="text" NAME="frm_callsign" VALUE=""/></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Name: <font color='red' size='-1'>*</font></TD>			<TD><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD>	<TD><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Capability: </TD>	<TD><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label" onClick = 'javascript: do_coords(document.res_add_Form.frm_lat.value ,document.res_add_Form.frm_lng.value  )'><U>Position</U>:<TD>
			<INPUT TYPE="text" NAME="show_lat" SIZE=13 VALUE="" disabled /> 
			<INPUT TYPE="text" NAME="show_lng" SIZE=13 VALUE="" disabled />&nbsp;&nbsp;
			<INPUT TYPE="text" SIZE=19 NAME="frm_ngs" VALUE="" disabled /></TD></TR>
		<TR><TD COLSPAN=2 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD></TR>
		<TR CLASS = "odd"><TD COLSPAN=2 ALIGN='center'>
		<INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset" onClick = "this.form.reset();set_f(this.form);">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit for Update"></TD></TR>
		<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_mobile' VALUE=0 />
		</FORM></TABLE> <!-- end inner left -->
		</TD><TD ALIGN='center'>
		<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
		<BR /><BR /><B>Drag/Click to fixed station location</B>
		<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A>
		
		<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
			EMS: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0>&nbsp;&nbsp;&nbsp;
			Fire: 		<IMG SRC = './markers/sm_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;
			Police: 	<IMG SRC = './markers/sm_blue.png' BORDER=0>&nbsp;&nbsp;&nbsp;
			Mutual: 	<IMG SRC = './markers/sm_white.png' BORDER=0>&nbsp;&nbsp;&nbsp;
			Other: 		<IMG SRC = './markers/sm_green.png' BORDER=0>		
		</TD></TR></TABLE><!-- end outer -->

<?php
		map("a",get_variable('def_lat') , get_variable('def_lng'), FALSE) ;				// call GMap js ADD mode, no icon
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($_getedit == 'true') {
		$id = $_GET['id'];
		$query	= "SELECT * FROM $GLOBALS[mysql_prefix]responder WHERE id=$id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_array($result);
//		dump($row);
		$lat = $row['lat'];
		$lng = $row['lng'];
		
		$type_checks = array ("", "", "", "", "");
		$type_checks[$row['type']] = " checked";
//		$checked = (!empty($row['mobile']))? " checked" : "" ;
		print do_calls($id);								// generate JS calls array
		$m_or_f = ($row['mobile']==1)? "Mobile Unit": "Station";

		$fixed = ($row['mobile']==0)? " CHECKED" :"" ;
		$mobile = ($row['mobile']==1)? " CHECKED"  : "";
	
		$dis = empty($row['mobile'])? " DISABLED": "";
?>
		</HEAD>
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
		<FONT CLASS="header">&nbsp;Edit <?php print $m_or_f . " '" . $row['name'];?>' Data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)<BR /><BR />
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER="0" ID='editform'>
		<FORM METHOD="POST" NAME= "res_edit_Form" onSubmit="return validate(document.res_edit_Form);" ACTION="units.php?func=responder&goedit=true">

<!--	<TR VALIGN = 'baseline' CLASS = "even" VALIGN='bottom'><TD CLASS="td_label">Unit category:</TD>
			<TD ALIGN='center'>
				Station &raquo;<INPUT TYPE="radio" VALUE="" NAME="frm_m_or_f" <?php print $fixed ;?> DISABLED />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Mobile unit &raquo;<INPUT TYPE="radio" VALUE="" NAME="frm_m_or_f" <?php print $mobile ;?>  DISABLED /></TD></TR>-->
		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label">Type: <font color='red' size='-1'>*</font></TD><TD><FONT SIZE='-2'>
<?php
		$type_checks = array ("", "", "", "", "", "");	// all empty
		$type_checks[$row['type']] = " checked";		// set the nth entry

?>		
		&nbsp;EMS   &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_EMS']; ?>"  NAME="frm_type" <?php print $type_checks[1];?>>
		&nbsp;Fire  &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_FIRE']; ?>" NAME="frm_type" <?php print $type_checks[2];?>>
		&nbsp;Police  &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_COPS']; ?>" NAME="frm_type" <?php print $type_checks[3];?>>
		&nbsp;Mutual  &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_MUTU']; ?>" NAME="frm_type" <?php print $type_checks[4];?>>
		&nbsp;Other  &raquo; <INPUT TYPE="radio" VALUE="<?php print $GLOBALS['TYPE_OTHR']; ?>" NAME="frm_type" <?php print $type_checks[5];?>>

		</TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Status:</TD>
			<TD><SELECT NAME="frm_un_status_id" onChange = "document.res_edit_Form.frm_log_it.value='1'">
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `status_val` ASC, `group` ASC, `sort` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		if ($the_grp != $row_st['group']) {
			print ($i == 0)? "": "</OPTGROUP>\n";
			$the_grp = $row_st['group'];
			print "<OPTGROUP LABEL='$the_grp'>\n";
			}
		$sel = ($row['un_status_id']== $row_st['id'])? " SELECTED" : "";
		print "\t<OPTION VALUE=" . $row_st['id'] . $sel .">" . $row_st['status_val']. "</OPTION>\n";
		$i++;
		}
	print "\n\t</SELECT>\n";
	unset($result_st);
																							// check any assign records this unit - added 5/23/08	
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id`=$id AND `clear` IS NULL";		// 6/27/08
//	dump($query);
	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$cbcount = mysql_affected_rows();				// count of incomplete assigns
	$dis_rmv = ($cbcount==0)? "": " DISABLED";		// allow/disallow removal 
	$cbtext = ($cbcount==0)? "": "&nbsp;&nbsp;<FONT size=-2>(NA - calls in progress: " .$cbcount . " )</FONT>";
?>
	&nbsp;&nbsp;&nbsp;&nbsp;Callsign: <INPUT SIZE="12" MAXLENGTH="12" TYPE="text" NAME="frm_callsign" VALUE="<?php print $row['callsign'];?>" />
	</TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Name: <font color='red' size='-1'>*</font></TD>			<TD><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD>	<TD><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Capability: </TD>										<TD><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2><?php print $row['capab'];?></TEXTAREA></TD></TR>


		<TR CLASS = "odd"><TD CLASS="td_label">Contact name:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Contact via:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="<?php print $row['contact_via'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label" onClick = 'javascript: do_coords(document.res_edit_Form.frm_lat.value ,document.res_edit_Form.frm_lng.value  )'><U>Position</U>:</TD><TD>
			<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=13 disabled />&nbsp;
			<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=13 disabled />&nbsp;&nbsp;
			<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($row['lat'], $row['lng']) ;?>" SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php
		$map_capt = ($row['mobile']==0)? "<BR /><BR /><CENTER><B>Click/drag to revise station location</B>" : "";
?>			
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="even" VALIGN='baseline'><TD CLASS="td_label">Remove Unit:</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
		<?php print $cbtext; ?></TD></TR>
		<TR CLASS = "odd">
			<TD COLSPAN=2 ALIGN='center'><BR><INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="reset" VALUE="Reset" onClick="map_reset()";>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="submit" VALUE="Submit for Update"></TD></TR>
		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
		<INPUT TYPE="hidden" NAME = "frm_mobile" VALUE=<?php print $row['mobile'] ;?> />
		</FORM></TABLE>
		</TD><TD ALIGN='center'><DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset'></DIV>
		<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A><BR />

		<?php print $map_capt; ?></TD></TR></TABLE>
<?php
		print do_calls($id);					// generate JS calls array
		if (empty($row['mobile'])){				// fixed?
			map("e", $lat, $lng, TRUE) ;		// do icon
			}
		else {									// mobile
			if(empty($lat)) {															// possible	no data, use default
				map("e", get_variable('def_lat'),  get_variable('def_lng'), FALSE) ;	// no icon
				}
			else {
				map("e", $lat, $lng, TRUE) ;	// do icon
				}
			}		// end mobile
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['edit'])
// view =================================================================================================================
// view =================================================================================================================
// view =================================================================================================================

		if ($_getview == 'true') {
			$id = $_GET['id'];
			$query	= "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id`=$id LIMIT 1";
			
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= stripslashes_deep(mysql_fetch_assoc($result));
			$lat = $row['lat'];
			$lng = $row['lng'];
//			$ngs = (!empty($row['lat']) && (empty($row['order'])))? "do_ngs();":"";

			if (isset($row['un_status_id'])) {
				$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id`=" . $row['un_status_id'];	// status value
				$result_st	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$row_st	= mysql_fetch_assoc($result_st);
				unset($result_st);
				}
			$un_st_val = (isset($row['un_status_id']))? $row_st['status_val'] : "?";
			$type_checks = array ("", "", "", "", "", "");
			$type_checks[$row['type']] = " checked";
			$checked = (!empty($row['mobile']))? " checked" : "" ;			

			$coords =  $row['lat'] . "," . $row['lng'];		// for UTM
			
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_affected_rows()>0) {						// got track stuff?
				$rowtr = stripslashes_deep(mysql_fetch_array($result_tr));
				$lat = $rowtr['latitude'];
				$lng = $rowtr['longitude'];				
				}
?>			
		</HEAD>
<?php
		if ($_dodisp == 'true') {				// dispatch 
			print "\t<BODY onLoad = 'ck_frames(); do_disp();' onunload='GUnload()'>\n";
			}
		else {
			print "\t<BODY onLoad = 'ck_frames()' onunload='GUnload()'>\n";
			}
		$fixed = ($row['mobile']==1)? "" :" CHECKED" ;
		$mobile = ($row['mobile']==1)? " CHECKED"  : "";
//		$call = (!$row['mobile']==1)? "": "Callsign: " . $row['callsign'];

		$m_or_f = ($row['mobile']==1)? "Mobile Unit": "Station";

?>
			<FONT CLASS="header">&nbsp;<?php print $m_or_f." '" . $row['name'] ;?>' Data</FONT> (#<?php print$row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER="0" ID='view_unit' STYLE='display: block'>
			<FORM METHOD="POST" NAME= "res_view_Form" ACTION="units.php?func=responder">
<!--			<TR VALIGN = 'baseline' CLASS = "odd"><TD CLASS="td_label">Unit category:</TD><TD ALIGN='center'>
			Station &raquo;<INPUT TYPE="radio" VALUE="" NAME="frm_m_or_f" <?php print $fixed ;?> DISABLED />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Mobile unit &raquo;<INPUT TYPE="radio" VALUE="" NAME="frm_m_or_f" <?php print $mobile ;?>  DISABLED />
			</TD></TR> -->
			<TR CLASS = "even"><TD CLASS="td_label">Status:</TD>		<TD><?php print $un_st_val;?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Call: <?php print $row['callsign'];?>
			</TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Name: </TD>			<TD><?php print $row['name'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Type: </TD><TD><?php print $types[$row['type']];?></TD></TR> <!-- // 6/1/08 -->
			<TR CLASS = "odd"><TD CLASS="td_label">Description: </TD>	<TD><?php print $row['description'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Capability: </TD>	<TD><?php print $row['capab'];?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Contact name:</TD>	<TD><?php print $row['contact_name'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Contact via:</TD>	<TD><?php print $row['contact_via'] ;?></TD></TR>
				
			<TR CLASS = 'odd'><TD CLASS="td_label">As of:</TD>	<TD><?php print format_date($row['updated']); ?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"  onClick = 'javascript: do_coords(<?php print "$lat,$lng";?>)'><U>Position</U>:</TD><TD>
				<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=13 disabled />&nbsp;
				<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=13 disabled />&nbsp;&nbsp;
				<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($row['lat'], $row['lng']) ;?>" SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php
		if ((get_variable('UTM')==1)&& (!empty($lat))) {
			$coords =  $lat . "," . $lng;
			print "<TR CLASS='even'><TD CLASS='td_label'>UTM Grid:</TD><TD>" . toUTM($coords) . "</TD></TR>\n";
			}

		if (isset($rowtr)) {																	// got tracks?
			print "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>APRS</B></TD></TR>";
			print "<TR CLASS='even'><TD>Course: </TD><TD>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
			print "<TR CLASS='odd'><TD>Closest city: </TD><TD>" . $rowtr['closest_city'] . "</TD></TR>";
			print "<TR CLASS='even'><TD>Status: </TD><TD>" . $rowtr['status'] . "</TD></TR>";
			print "<TR CLASS='odd'><TD>As of: </TD><TD>" . format_date($rowtr['packet_date']) . " (UTC)</TD></TR>";
			$lat = $rowtr['latitude'];
			$lng = $rowtr['longitude'];
			}

		$toedit = (is_administrator() || is_super())? "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;": "" ;
?>			
			<TR><TD>&nbsp;</TD></TR>
<?php
		if (is_administrator() || is_super()) {
?>		
			<TR CLASS = "odd"><TD COLSPAN=2 ALIGN='center'>
			<INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;			
			<INPUT TYPE="button" VALUE="to Edit" 	onClick= "to_edit_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
		if (!(empty($lat))) {						// 6/27/08 - dispatch able?
?>		
			<INPUT TYPE="button" VALUE="to Dispatch" 	onClick= "document.getElementById('incidents').style.display='block'; document.getElementById('view_unit').style.display='none';">
			
<?php
			}
?>			
			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>" />
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>" />
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			</TD></TR>
<?php
			}		// end if (is_administrator() || is_super())
		print "</FORM></TABLE>\n";
		print "\n" . show_assigns(1,$row['id'] ) . "\n";
?>			
			<BR /><BR /><BR />
			<TABLE BORDER=1 ID = 'incidents' STYLE = 'display:none' >
			<TR CLASS='even'><TH COLSPAN=99> Click incident to assign to <?php print $row['name'] ;?></TH></TR>
			
<?php																								// 6/1/08 - added
		$query = "SELECT * FROM $GLOBALS[mysql_prefix]ticket ORDER BY `id`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
							// major while ... starts here
		$i=0;
		while ($row = stripslashes_deep(mysql_fetch_array($result))) 	{
			switch($row['severity'])		{		//color tickets by severity
			 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
				case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
				default: 							$severityclass=''; break;
				}
//			dump ($row);

			print "\t<TR CLASS ='" .  $evenodd[($i+1)%2] . "' onClick = 'to_routes(\"" . $row['id'] . "\")'>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row['scope']}'>" . shorten($row['scope'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row['description']}'>" . shorten($row['description'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row['street']} {$row['city']}'>" . shorten($row['street'], 24) . "</TD>\n";
			print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row['city']}'>" . shorten($row['city'], 10). "</TD>";
			print "\t\t</TR>\n";
			$i++;
			}
?>
			<TR><TD ALIGN="center" COLSPAN=99><BR /><BR />
				<INPUT TYPE="button" VALUE="Cancel" onClick = "document.getElementById('incidents').style.display='none'; document.getElementById('view_unit').style.display='block';">
			</TD></TR>
			</TABLE><BR><BR>
			</TD><TD ALIGN='center'><DIV ID='map' style="width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset"></DIV>
			<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A><BR /><BR />
			
			</TD></TR></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>		
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "units.php?func=responder&edit=true&id=<?php print $id; ?>"></FORM>		
			<FORM NAME="routes_Form" METHOD="get" ACTION = "routes.php">
			<INPUT TYPE="hidden" NAME="ticket_id" 	VALUE="">						<!-- 10/16/08 -->
			<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
			</FORM>		
			</BODY>					<!-- END RESPONDER VIEW -->
<?php
			if (!(empty($row['mobile']))){							// fixed?
				map("v", $lat, $lng, TRUE) ;						// do icon
				}
			else {													// mobile
				if(empty($lat)) {									// possible
					map("v", get_variable('def_lat'),  get_variable('def_lng'), FALSE) ;	// default center, no icon
					}
				else {
					map("v", $lat, $lng, TRUE) ;						// do icon
					}
				}		// end mobile
?>
			</BODY>
			</HTML>
<?php
			exit();
			}		// end if ($_GET['view'])

		$do_list_and_map = TRUE;
		
		if($do_list_and_map) {
			if (!isset($mapmode)) {$mapmode="a";}
			print $caption;
?>
		</HEAD><!-- 1387 -->
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
		<TABLE ID='outer'><TR><TD>
			<DIV ID='side_bar'></DIV>
			</TD><TD ALIGN='center'>
			<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A><BR /><BR />

			Units:&nbsp;&nbsp;&nbsp;&nbsp;
				EMS: 	<IMG SRC = './markers/sm_yellow.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Fire: 		<IMG SRC = './markers/sm_red.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Police: 	<IMG SRC = './markers/sm_blue.png' BORDER=0>&nbsp;&nbsp;&nbsp;
				Mutual: 	<IMG SRC = './markers/sm_white.png' BORDER=0>&nbsp;&nbsp;
				Other: 		<IMG SRC = './markers/sm_green.png' BORDER=0>		
			</TD></TR></TABLE><!-- end outer -->
			
			<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='view' VALUE='true'>
			<INPUT TYPE='hidden' NAME='id' VALUE=''>
			</FORM>
			
			<FORM NAME='add_Form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='add' VALUE='true'>
			</FORM>
			
			<FORM NAME='can_Form' METHOD="post" ACTION = "units.php?func=responder"></FORM>
			<FORM NAME='tracks_Form' METHOD="get" ACTION = "tracks.php"></FORM>
			</BODY>				<!-- END RESPONDER LIST and ADD -->
<?php
		print do_calls();		// generate JS calls array

		$buttons = "<TR><TD COLSPAN=99 ALIGN='center'><BR /><INPUT TYPE = 'button' onClick = 'document.tracks_Form.submit();' VALUE='Unit Tracks'>";
		$buttons .= (is_guest())? "" :"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' value= 'Add a Unit'  onClick ='document.add_Form.submit();'>";	// 10/8/08
//		$buttons .= (intval(get_variable('aprs_poll'))>0)? "<BR><BR><INPUT TYPE='button' value= 'APRS'  onClick ='do_aprs_window();'>": "";
		$buttons .= "</TD></TR>";

		print list_responders($buttons, 0);				// ($addon = '', $start)
		print "\n</HTML> \n";
		exit();
		}				// end if($do_list_and_map)
    break;
/*
<TR><TD>aqua</TD><TD>#00FFFF</TD><TD>green</TD><TD>#008000</TD><TD>navy</TD><TD>#000080</TD><TD>silver</TD><TD>#C0C0C0</TD></TR>
<TR><TD>black</TD><TD>#000000</TD><TD>gray</TD><TD>#808080</TD><TD>olive</TD><TD>#808000</TD><TD>teal</TD><TD>#008080</TD></TR>
<TR><TD>blue</TD><TD>#0000FF</TD><TD>lime</TD><TD>#00FF00</TD><TD>purple</TD><TD>#800080</TD><TD>white</TD><TD>#FFFFFF</TD></TR>
<TR><TD>fuchsia</TD><TD>#FF00FF</TD><TD>maroon</TD><TD>#800000</TD><TD>red</TD><TD>#FF0000</TD><TD>yellow</TD><TD>#FFFF00</TD></TR>
*/
?>
