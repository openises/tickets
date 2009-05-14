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
12/20/08 $ function added
12/24/08 edit position data for fixed stations only
12/25/08 changed to settings-driven unit types, set tightest zoom
12/27/08 revised unit types logic
1/1/09   converted to variable unit-types
1/5/09   aprs added to u_types array
1/5/09   letters for icons and sidebar
1/21/09 top.calls.start added, aprs moved from unit type to responder schema
1/23/09 revised aprs unit handling, auto refresh
1/27/09 corrections re mobile, aprs
2/2/09 fixes for bad unit_status values
2/13/09 added to_str()  for # units > 25
2/24/09 allow NULL coords, accommodate blank status vals
2/25/09 disable directions
3/3/09 added UL for no-mapped units
3/13/09 instamapper handling added
3/16/09 assignments added, get curent re aprs, instam
3/18/09 'aprs_poll' to 'auto_poll'
3/19/09 'directions' added
3/22/09 added 2nd tab for instamapper data
4/2/09 added fixed default map bounds
4/9/09 restored directions
4/10/09 street view added
5/4/09 my_is_float() repl is_float
*/


error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
//snap(basename(__FILE__), __LINE__);

//$tolerance = 5 * 60;		// nr. seconds report time may differ from UTC
extract($_GET);
extract($_POST);

if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}

$remotes = get_current();		// returns array - 3/16/09

$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}
//dump($u_types);
unset($result);

$icons = $GLOBALS['icons'];				// 1/1/09
$sm_icons = $GLOBALS['sm_icons'];

function get_icon_legend (){			// returns legend string - 1/1/09
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$temp = $u_types[$row['type']];
		$print .= "\t\t" .$temp[0] . " &raquo; <IMG SRC = './icons/" . $sm_icons[$temp[1]] . "' BORDER=0>&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()
$interval = intval(get_variable('auto_poll'));		// array_key_exists ( mixed key, array search )
$refresh = ((!(array_key_exists ('func', $_GET ))) && ($interval>0))? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>": "";	//10/4/08, 1/24/09
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

	<META HTTP-EQUIV="Script-date" CONTENT="1/1/09">
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT  SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT> <!-- 11/3/08 -->
	<SCRIPT  SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>	<!-- 8/23/08 -->
	<SCRIPT  SRC='./js/graticule.js' type='text/javascript'></SCRIPT>
	<SCRIPT >

	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();												// 11/2/08

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08

	function $() {															// 12/20/08
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}

	String.prototype.trim = function () {									// added 6/10/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()

	function to_str(instr) {			// 0-based conversion - 2/13/09
		function ord( string ) {
		    return (string+'').charCodeAt(0);
			}

		function chr( ascii ) {
		    return String.fromCharCode(ascii);
			}
		function to_char(val) {
			return(chr(ord("A")+val));
			}

		var lop = (instr % 26);													// low-order portion, a number
		var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
		return hop+to_char(lop);
		}


	function do_usng_conv(theForm){						// usng to LL array			- 12/4/08
		tolatlng = new Array();
		USNGtoLL(theForm.frm_ngs.value, tolatlng);

		var point = new GLatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
		map.setCenter(point, 13);
		var marker = new GMarker(point);
		theForm.frm_lat.value = point.lat(); theForm.frm_lng.value = point.lng();
		do_lat (point.lat());
		do_lng (point.lng());
		do_ngs(theForm);
		domap();			// show it
		}				// end function

	function do_unlock_pos(theForm) {				// 12/20/08
		theForm.frm_ngs.disabled=false;
		$("lock_p").style.visibility = "hidden";
		$("usng_link").style.textDecoration = "underline";
		}

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

    var trafficInfo = new GTrafficOverlay();
    var toggleState = true;

	function doTraffic() {				// 10/16/08
		if (toggleState) {
	        map.removeOverlay(trafficInfo);
	     	}
		else {
	        map.addOverlay(trafficInfo);
	    	}
        toggleState = !toggleState;			// swap
	    }				// end function doTraffic()

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
			map.closeInfoWindow();
			var width = <?php print get_variable('map_width');?>+360;
			var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
			var url = "track_u.php?source="+callsign;

			newwindow=window.open(url, callsign,  spec);
			if (isNull(newwindow)) {
				alert ("Track display requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow.focus();
			}
		}				// end function

	function to_routes(id) {
		document.routes_Form.ticket_id.value=id;			// 10/16/08, 10/25/08
		document.routes_Form.submit();
		}

	function whatBrows() {									//Displays the generic browser type
		window.alert("Browser is : " + type);
		}

	function ShowLayer(id, action){							// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
		if (type=="IE") 				eval("document.all." + id + ".style.display='" + action + "'");  	// id is the span/layer, action is either hidden or visible
		if (type=="NN") 				eval("document." + id + ".display='" + action + "'");
		if (type=="MO" || type=="OP") 	eval("$('" + id + "').style.display='" + action + "'");
		}

	function hideit (elid) {
		ShowLayer(elid, "none");
		}

	function showit (elid) {
		ShowLayer(elid, "block");
		}

	function validate(theForm) {						// Responder form contents validation	10/6/08, 1/13/09
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				var str = "Please confirm removing '" + theForm.frm_name.value + "'";
				if(confirm(str)) 	{return true;}
				else 				{return false;}
				}
			}

		theForm.frm_mobile.value = (theForm.frm_mob_disp.checked)? 1:0;
		theForm.frm_aprs.value =   (theForm.frm_aprs_disp.checked)? 1:0;
		theForm.frm_instam.value = (theForm.frm_instam_disp.checked)? 1:0;
		theForm.frm_direcs.value = (theForm.frm_direcs_disp.checked)? 1:0;
		var errmsg="";
								// 2/24/09
		if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)					{errmsg+="Unit TYPE is required.\n";}	// 1/1/09
		if (theForm.frm_un_status_id.options[theForm.frm_un_status_id.selectedIndex].value==0)	{errmsg+="Unit STATUS is required.\n";}
		if (theForm.frm_name.value.trim()=="")													{errmsg+="Unit NAME is required.\n";}
		if (theForm.frm_descr.value.trim()=="")													{errmsg+="Unit DESCRIPTION is required.\n";}
		var is_mobile = (theForm.frm_mobile.value==1)
		if ((is_mobile) && (!((theForm.frm_mob_disp.checked) || (theForm.frm_instam_disp.checked)))) {errmsg+="Mobile unit error.\n"}
		if ((is_mobile) && (theForm.frm_callsign.value=="")) 									{errmsg+="CALLSIGN is required for mobile units\n";}

		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {																	// good to go!
			top.upper.calls_start();											// 1/21/09
			return true;
			}
		}				// end function va lidate(theForm)

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

	function do_ngs() {											// LL to USNG
//		alert(document.forms[0].frm_lat.value);
//		alert(document.forms[0].frm_lng.value);
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
		$('incidents').style.display='block';
		$('view_unit').style.display='none';
		}

	function do_add_reset(the_form) {								// 1/22/09
//		map.clearOverlays();
		the_form.reset();
		do_ngs();
		}

	</SCRIPT>


<?php

function list_responders($addon = '', $start) {
	global $u_types, $my_session, $tolerance;

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

	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";		// 12/17/08
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$units = mysql_affected_rows()>0 ?  mysql_affected_rows(): "<I>none</I>";
	unset($result);
//dump (__LINE__);

?>

<SCRIPT >

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
		elem = $("allIcons");
		elem.style.visibility = "visible";
		}			// end function

	function showAll() {
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
		elem = $("allIcons");
		elem.style.visibility = "hidden";

		}			// end function
//																		// Creates marker and sets up click event infowindow
	function createMarker(point,tabs, color, id) {						// (point, myinfoTabs,<?php print $row['type'];?>, i)
		points = true;													// at least one
//		var letter = String.fromCharCode("A".charCodeAt(0) + id);		// start with A - 1/5/09
		var letter = to_str(id);										// 2/13/09

		var icon = new GIcon(listIcon);
		var icon_url = "./icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + letter;				// 1/5/09

		icon.image = icon_url;		// ./icons/gen_icon.php?blank=4&text=zz"

		var marker = new GMarker(point, icon);
		marker.id = color;				// for hide/unhide - unused

		GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
//			alert(510);
			if (marker) {
				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				marker.openInfoWindowTabsHtml(infoTabs[id]);

				setTimeout(function() {										// wait for rendering complete - 12/17/08
					if ($("detailmap")) {
						var dMapDiv = $("detailmap");
						var detailmap = new GMap2(dMapDiv);
						detailmap.addControl(new GSmallMapControl());
						detailmap.setCenter(point, 17);  						// larger # = closer
						detailmap.addOverlay(marker);
						}
					else {
	//					alert(62);
	//					alert($("detailmap"));
						}
					},4000);				// end setTimeout(...)

				}		// end if (marker)
			});			// end GEvent.add Listener()

		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
		if (!(map_is_fixed)) {				// 4/3/09
			bounds.extend(point);
			}
		return marker;
		}				// end function create Marker()

	function do_sidebar (sidebar, id, the_class) {
//		var letter = String.fromCharCode("A".charCodeAt(0) + id);								// start with A - 1/5/09
		var letter = to_str(id)

		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='" + the_class + "'>" + letter + ". "+ sidebar +"</TD></TR>\n";		// 1/5/09, 3/4/09
		}

	function do_sidebar_nm (sidebar, line_no, id) {							// no map - view responder // view_Form
//		var letter = String.fromCharCode("A".charCodeAt(0) + line_no);							// start with A - 1/5/09
		var letter = to_str(line_no);
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + letter + ". "+ sidebar +"</TD></TR>\n";		// 1/23/09
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [id]
		GEvent.trigger(gmarkers[id], "click");
		}

	function do_lat (lat) {
		document.forms[0].frm_lat.value=lat.toFixed(6);
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.value=lng.toFixed(6);
		}

	function do_ngs() {						// LL to USNG into form
		document.forms[0].frm_ngs.disabled=false;
		document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
		document.forms[0].frm_ngs.disabled=true;
		}

	var icons=new Array;							// maps type to icon blank

<?php
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$icons = $GLOBALS['icons'];

while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// map type to blank icon id
	$blank = $icons[$row['icon']];
	print "\ticons[" . $row['id'] . "] = " . $row['icon'] . ";\n";	//
	}
unset($result);

$dzf = get_variable('def_zoom_fixed');
print "\tvar map_is_fixed = ";

print (((my_is_int($dzf)) && ($dzf==2)) || ((my_is_int($dzf)) && ($dzf==3)))? "true;\n":"false;\n";

?>
	var map;
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
	side_bar_html += "<TR class='even'>	<TD colspan=99 ALIGN='center'><B>Units (<?php print $units; ?>)</B></TD></TR>";
	side_bar_html += "<TR class='odd'>	<TD colspan=99 ALIGN='center'>Click line or icon for details - or to dispatch</TD></TR>";
	side_bar_html += "<TR class='even'>	<TD></TD><TD ALIGN='center'>Name</TD><TD ALIGN='center'>Dispatch</TD><TD ALIGN='center'>Status</TD><TD>M</TD><TD ALIGN='center'>As of</TD></TR>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = <?php print $start; ?>;					// sidebar/icon index
	var points = false;								// none

	map = new GMap2($("map"));						// create the map
	map.addControl(new GSmallMapControl());					// 10/6/08
	map.addControl(new GMapTypeControl());
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>

	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
	mapBounds=new GLatLngBounds(map.getBounds().getSouthWest(), map.getBounds().getNorthEast());		// 4/4/09

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
	$instam = FALSE;
	$i=0;				// counter
// =============================================================================
	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");

	$utc = gmdate ("U");
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {		// ==========  major while() for RESPONDER ==========
		$got_point = FALSE;
		print "\n\t\tvar i=$i;\n";
		$todisp = (is_guest())? "": "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&disp=true&id=" . $row['id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";	// 08/8/02
		$toedit = (is_guest())? "" :"&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;	// 10/8/08
		$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U></SPAN>" ;

		$temp = $row['un_status_id'] ;		// 2/24/09
		$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09

		if ($row['aprs']==1) {				// get most recent aprs position data
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_aprs = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
//			if (($row_aprs) && (settype($row_aprs['latitude'], "float"))) {
			if (($row_aprs) && (my_is_float($row_aprs['latitude']))) {
				echo "\t\tvar point = new GLatLng(" . $row_aprs['latitude'] . ", " . $row_aprs['longitude'] ."); // 677\n";
				$got_point = TRUE;

				}
			unset($result_tr);
			}
		else { $row_aprs = FALSE; }
//		dump($row_aprs);

		if ($row['instam']==1) {			// get most recent instamapper data
			$temp = explode ("/", $row['callsign']);			// callsign/account no. 3/22/09

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_instam = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			if (($row_instam) && (my_is_float($row_instam['latitude']))) {
				echo "\t\tvar point = new GLatLng(" . $row_instam['latitude'] . ", " . $row_instam['longitude'] ."); // 687\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_instam = FALSE; }

		if (!($got_point) && ((my_is_float($row['lat'])))) {
			echo "\t\tvar point = new GLatLng(" . $row['lat'] . ", " . $row['lng'] .");	// 718\n";
			$got_point= TRUE;
			}

//		print __LINE__ . "<BR />";
		$the_bull = "";											// define the bullet
		if ($row['aprs']==1) {
			if ($row_aprs) {
				$spd = 2;										// default
				if($row_aprs['speed'] == 0) {$spd = 1;}			// stopped
				if($row_aprs['speed'] >= 50) {$spd = 3;}		// fast
				}
			else {
				$spd = 0;				// no data
				}
			$the_bull = "<FONT COLOR=" . $bulls[$spd] ."><B>&bull;</B></FONT>";
			}			// end aprs

		if ($row['instam']==1) {
			if ($row_instam['speed']>50) {$the_bull = "<FONT COLOR = 'white'><B>I</B></FONT>";}		// 3/22/09
			if ($row_instam['speed']<50) {$the_bull = "<FONT COLOR = 'green'><B>I</B></FONT>";}
			if ($row_instam['speed']==0) {$the_bull = "<FONT COLOR = 'red'><B>I</B></FONT>";}
			}
						// end bullet stuff
// name

		$sidebar_line = "<TD TITLE = '" . htmlentities ($row['name'], ENT_QUOTES) . "'><U>" . shorten($row['name'], 24) ."</U></TD>";

// assignments 3/16/09

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
			WHERE `responder_id` = '{$row['id']}' AND `clear` IS NULL ";
//		dump($query);

		$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_assign = (mysql_affected_rows()==0)?  FALSE : stripslashes_deep(mysql_fetch_assoc($result_as)) ;
		unset($result_as);

		switch($row_assign['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 							$severityclass=''; break;
			}

		$tick_ct = (mysql_affected_rows()>1)? "(" .mysql_affected_rows() . ") ": "";
		$ass_td =  (mysql_affected_rows()>0)? "<TD CLASS='$severityclass' TITLE = '" .$row_assign['scope'] . "' >" .$tick_ct . shorten($row_assign['scope'], 24) . "</TD>": "<TD>na</TD>";

		$sidebar_line .= ($row_assign)? $ass_td : "<TD>na</TD>";

// status, mobility
		$sidebar_line .= "<TD CLASS='td_data' TITLE = '" . htmlentities ($the_status, ENT_QUOTES) . "'> " . shorten($the_status, 10) .
				"</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";
//		$sidebar_line .= "<TD CLASS='td_data' TITLE = \"{$the_status}\"> " . shorten($the_status, 10) . "</TD><TD CLASS='td_data'> " . $the_bull . "</TD>";	// 4/5/09

// as of
		$strike = $strike_end = "";
		if ((($row['instam']==1) && $row_instam ) || (($row['aprs']==1) && $row_aprs )) {		// either remote source?
			$the_class = "emph";
			if ($row['instam']==1) {															// 3/24/09
				$the_time = $row_instam['updated'];
				$instam = TRUE;				// show footer legend
				}
			else {
				$the_time = $row_aprs['packet_date'];
				$aprs = TRUE;				// show footer legend
				}
			if (abs($utc - $the_time) > $GLOBALS['TOLERANCE']) {								// attempt to identify  non-current values
				$strike = "<STRIKE>";
				$strike_end = "</STRIKE>";
				};
			}

		else {
			$the_time = $row['updated'];
			$the_class = "td_data";
			}

//	    snap(basename( __FILE__) . __LINE__, $the_class );

		$sidebar_line .= "<TD CLASS='$the_class'> $strike" . format_sb_date($the_time) . "$strike_end</TD>";				// 6/17/08
// tab 1
		if (((my_is_float($row['lat']))) || ($row_aprs) || ($row_instam)) {						// position data?
			$temptype = $u_types[$row['type']];
			$the_type = $temptype[0];																	// 1/1/09

			$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $the_type . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
			if (array_key_exists($row['id'], $assigns)) {
				$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['id']] . "'>" . shorten($assigns[$row['id']], 20) . "</A></TD></TR>";
				}
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $todisp . $totrack . $toedit . "&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
			$tab_1 .= "</TABLE>";


// tab 2
		$tabs_done=FALSE;		// default

		if ($row_aprs) {		// three tabs
			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_aprs['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_aprs['course'] . ", Speed:  " . $row_aprs['speed'] . ", Alt: " . $row_aprs['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $row_aprs['closest_city'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $row_aprs['status'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike" . format_date($row_aprs['packet_date']) . "$strike_end (UTC)</TD></TR></TABLE>";
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("<?php print $row_aprs['source']; ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
<?php
			$tabs_done=TRUE;
			}	// end if ($row_aprs)

		if ($row_instam) {		// three tabs if instam data
			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_instam['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_instam['course'] . ", Speed:  " . $row_instam['speed'] . ", Alt: " . $row_instam['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_instam['updated']) . " $strike_end </TD></TR></TABLE>";
			$tabs_done=TRUE;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("<?php print $row_instam['source']; ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
<?php
			}	// end if ($row_instam)


		if (!($tabs_done)) {	// else two tabs
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
<?php
			}		// end if/else aprs
?>
		var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";		// 4/3/09

		do_sidebar ("<?php print $sidebar_line; ?>", i, the_class);
		var marker = createMarker(point, myinfoTabs,<?php print $row['type'];?>, i);	// 771 (point,tabs, color, id)
		map.addOverlay(marker);
<?php
		}		// end position data available

		else {
			print "\tdo_sidebar_nm (\" {$sidebar_line} \" , i, {$row['id']});\n";	// sidebar only - no map
			}

	$i++;				// zero-based
	}				// end  ==========  while() for RESPONDER ==========

// =======================================
//dump (__LINE__);

	$source_legend = (($aprs)||($instam))? "<TD CLASS='emph' ALIGN='center'>Source time</TD>": "<TD></TD>";		// if any remote data/time 3/24/09
?>
	if (!(map_is_fixed)) {		// 4/3/09
		if (!points) {		// any?
			map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);
			map.setCenter(center,zoom);
			}
		}

	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'><TD COLSPAN=5>&nbsp;</TD><?php print $source_legend;?></TR>";
	side_bar_html+= "<TR CLASS='" + colors[(i+1)%2] +"'><TD COLSPAN=6 ALIGN='center'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></TD></TR>";
<?php

	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div

<?php
	do_kml();
?>


</SCRIPT>
<?php
	}				// end function list_responders() ===========================================================

function map($mode, $lat, $lng, $icon) {						// Responder add, edit, view 2/24/09
	$have_coords = is_numeric($lat);
	$the_lat = is_numeric($lat)? $lat : get_variable('def_lat')  ;
	$the_lng = is_numeric($lat)? $lng : get_variable('def_lng')  ;
?>

<SCRIPT >
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
		var point = new GLatLng(<?php print $the_lat;?>, <?php print $the_lng;?>);
		map.setCenter(point, <?php print get_variable('def_zoom');?>);
		map.addOverlay(new GMarker(point, myIcon));
		}
	function map_cen_reset() {				// reset map center icon
		map.clearOverlays();
		}

	var map = new GMap2($('map'));
	var	gdir = new GDirections(map, $("directions"));	// 12/16/08, 4/9/09

   	G_START_ICON.image = "";
   	G_END_ICON.image = "";

	var bounds = new GLatLngBounds();										// create empty bounding box

	var myZoom;						// note globals
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
	map.addControl(new GOverviewMapControl());
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>

	map.enableScrollWheelZoom();

	var tab1contents;				// info window contents - first/only tab
									// default point - possible dummy
	map.setCenter(new GLatLng(<?php print $the_lat; ?>, <?php print $the_lng; ?>), <?php print get_variable('def_zoom');?>);	// larger # => tighter zoom

<?php
	if ($icon)	{							// icon display?
?>
		var point = new GLatLng(<?php print $the_lat . ", " . $the_lng; ?>); // 888
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
		if ($mode=="v") {				// only in view mode
?>
		function handleErrors(){
			if (gdir.getStatus().code == G_GEO_UNKNOWN_DIRECTIONS ) {
				alert("501: directions unavailable\n\nClick map point for directions.");
				}
			else if (gdir.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
				alert("440: No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_SERVER_ERROR)
				alert("442: A map request could not be processed, reason unknown.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_MISSING_QUERY)
				alert("444: Technical error.\n Error code: " + gdir.getStatus().code);
	//		else if (gdir.getStatus().code == G_UNAVAILABLE_ADDRESS)  <--- Doc bug... this is either not defined, or Doc is wrong
	//			alert("446: The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.\n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_KEY)
				alert("448: The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_REQUEST)
				alert("450: A directions request could not be successfully parsed.\n Error code: " + gdir.getStatus().code);
			else alert("451: An unknown error occurred.");
			}		// end function handleErrors()

//		function onGDirectionsLoad(){
//			var temp = gdir.getSummaryHtml();
//			alert(extr_num(temp));
//	 		Use this function to access information about the latest load() results.
//	 			e.g.
//	 		$("getStatus").innerHTML = gdir.getStatus().code;
//	 		and yada yada yada...
//			}		// function onGDirectionsLoad()

	    function setDirections(fromAddress, toAddress, locale) {				// 12/15/08
	    	var Direcs = gdir.load("from: " + fromAddress + " to: " + toAddress, { "locale": locale, preserveViewport : true  });
			GEvent.addListener(Direcs, "addoverlay", GEvent.callback(Direcs, cb()));
	    	}		// end function set Directions()

	    function cb() {
//			alert(847);	    							// onto floor ??
	    	}

		GEvent.addListener(map, "click", function(marker, point) {				// 12/16/08

			bounds.extend(point);											// endpoint to bounding box
	    	var the_start = new GLatLng(<?php print $the_lat;?>, <?php print $the_lng;?>);
	    	bounds.extend(the_start);									// start to bounding box

			var the_start = "<?php print $the_lat . " " . $the_lng;?>";
			var the_end = point.lat().toFixed(6).toString() + " " + point.lng().toFixed(6).toString();

			center = bounds.getCenter();
//			zoom = map.getBoundsZoomLevel(bounds)-1;
			zoom = map.getBoundsZoomLevel(bounds);
			map.clearOverlays();
			map.setCenter(center,zoom);

			setDirections(the_start, the_end, "en_US");

			});				// end GEvent.add Listener()

<?php
		}				// end if ($mode=="v")
		else {					// disallow if view mode
?>

	var the_zoom = <?php print get_variable('def_zoom');?>;

	map.enableScrollWheelZoom();
	var is_mobile = ((document.forms[0].frm_mobile.value==1) && ((document.forms[0].frm_aprs.value==1) || (document.forms[0].frm_instam.value==1)));

//	if ((mode=="a") || ((mode=="e") && (!is_mobile))){
	if ((mode=="a") || (mode=="e")){
		the_marker = new GMarker(map.getCenter(), {draggable: true	});

		GEvent.addListener(map, "click", function(overlay, latlng) {

//			if(is_mobile) {
//				alert("Map position not allowed for mobile units!");
//				return;
//				}

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
			});		// end GEvent.add Listener()

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
		$print = "\n<SCRIPT >\n";
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
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? "NULL" : quote_smart(trim($_POST['frm_lat'])) ; // 2/24/09
			$the_lng = empty($_POST['frm_lng'])? "NULL" : quote_smart(trim($_POST['frm_lng'])) ;
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
				`name`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",
				`capab`= " . 		quote_smart(trim($_POST['frm_capab'])) . ",
				`un_status_id`= " . quote_smart(trim($_POST['frm_un_status_id'])) . ",
				`callsign`= " . 	quote_smart(trim($_POST['frm_callsign'])) . ",
				`mobile`= " . 		quote_smart(trim($_POST['frm_mobile'])) . ",
				`aprs`= " . 		quote_smart(trim($_POST['frm_aprs'])) . ",
				`instam`= " . 		quote_smart(trim($_POST['frm_instam'])) . ",
				`direcs`= " . 		quote_smart(trim($_POST['frm_direcs'])) . ",
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`contact_name`= " . quote_smart(trim($_POST['frm_contact_name'])) . ",
				`contact_via`= " . 	quote_smart(trim($_POST['frm_contact_via'])) . ",
				`type`= " . 		quote_smart(trim($_POST['frm_type'])) . ",
				`user_id`= " . 		quote_smart(trim($my_session['user_id'])) . ",
				`updated`= " . 		quote_smart(trim($now)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

//			dump ($query);
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_UNIT_STATUS'], 0, $_POST['frm_id'], $_POST['frm_un_status_id']);}	// 6/2/08
			$mobstr = (($frm_mobile) && ($frm_aprs)||($frm_instam))? "Mobile": "Unit ";
			$caption = "<B>" . $mobstr . " '<i>" . stripslashes_deep($_POST['frm_name']) . "</i>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {

//		$is_mobile = ($_POST['frm_mobile']==1) && ($_POST['frm_aprs']==1);							// set boolean
		$frm_lat = (empty($_POST['frm_lat']))? 'NULL': quote_smart(trim($_POST['frm_lat']));						// 9/3/08
		$frm_lng = (empty($_POST['frm_lng']))? 'NULL': quote_smart(trim($_POST['frm_lng']));						// 9/3/08
//		$frm_lng = ($is_mobile)? 'NULL': quote_smart(trim($_POST['frm_lng']));						// 9/3/08
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));							// 1/27/09
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]responder` (
			`name`, `description`, `capab`, `un_status_id`, `callsign`, `mobile`, `aprs`, `instam`, `direcs`, `contact_name`, `contact_via`, `lat`, `lng`, `type`, `user_id`, `updated` )
			VALUES (" .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_descr'])) . "," .
				quote_smart(trim($_POST['frm_capab'])) . "," .
				quote_smart(trim($_POST['frm_un_status_id'])) . "," .
				quote_smart(trim($_POST['frm_callsign'])) . "," .
				quote_smart(trim($_POST['frm_mobile'])) . "," .
				quote_smart(trim($_POST['frm_aprs'])) . "," .
				quote_smart(trim($_POST['frm_instam'])) . "," .
				quote_smart(trim($_POST['frm_direcs'])) . "," .
				quote_smart(trim($_POST['frm_contact_name'])) . "," .
				quote_smart(trim($_POST['frm_contact_via'])) . "," .
				$frm_lat . "," .
				$frm_lng . "," .
				quote_smart(trim($_POST['frm_type'])) . "," .
				quote_smart(trim($my_session['user_id'])) . "," .
				quote_smart(trim($now)) . ");";								// 8/23/08

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		do_log($GLOBALS['LOG_UNIT_STATUS'], 0, mysql_insert_id(), $_POST['frm_un_status_id']);	// 6/2/08

		$mobstr = ($frm_mobile)? "Mobile Unit ": "Station ";
		$caption = "<B>Unit  <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";

		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
// add ===========================================================================================================================
// add ===========================================================================================================================

	if ($_getadd == 'true') {
		print do_calls();		// call signs to JS array for validation
?>
		</HEAD>
		<BODY  onLoad = "ck_frames();" onunload="GUnload()">
		<FONT CLASS="header">Add Unit</FONT><BR /><BR />
		<TABLE BORDER=0 ID='outer' BORDER=><TR><TD>
		<TABLE BORDER="0" ID='addform'>
		<FORM NAME= "res_add_Form" METHOD="POST" onSubmit="return validate(document.res_add_Form);" ACTION="units.php?func=responder&goadd=true">

		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label">Type: <font color='red' size='-1'>*</font></TD><TD>
				<SELECT NAME='frm_type'><OPTION VALUE=0>Select one</OPTION>		<!-- 1/8/09 -->
<?php
	foreach ($u_types as $key => $value) {								// 12/27/08
		$temp = $value; 												// 2-element array
		print "\t\t\t\t<OPTION VALUE='" . $key . "'>" .$temp[0] . "</OPTION>\n";
		}
?>
				</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Mobile  &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" />
			</TD></TR>

		<TR CLASS = "odd" VALIGN='top'  TITLE = 'Check if YES'><TD CLASS="td_label" >Operation:</TD>
			<TD>&nbsp;&nbsp;<!-- 1/21/09 -->
				APRS  &raquo;<INPUT TYPE="checkbox" NAME="frm_aprs_disp" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Instamap &raquo;<INPUT TYPE="checkbox" NAME="frm_instam_disp" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Directions &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked />
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
		&nbsp;Callsign: <INPUT SIZE="12" MAXLENGTH="12" TYPE="text" NAME="frm_callsign" VALUE="" onmouseover = "$('instam_label').style.visibility = 'visible';" onmouseout= "$('instam_label').style.visibility = 'hidden';";/>
			&nbsp;<SPAN ID = 'instam_label' STYLE = 'visibility: hidden'><I>(Label/API key)</I></SPAN></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Name: <font color='red' size='-1'>*</font></TD>			<TD><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD>	<TD><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Capability: </TD>	<TD><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">
			<SPAN onClick = 'javascript: do_coords(document.res_add_Form.frm_lat.value ,document.res_add_Form.frm_lng.value)'>
				<U>Lat/Lng</U></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle'
					onClick = 'do_unlock_pos(document.res_add_Form);'><TD>
			<INPUT TYPE="text" NAME="show_lat" SIZE=11 VALUE="" disabled />
			<INPUT TYPE="text" NAME="show_lng" SIZE=11 VALUE="" disabled />&nbsp;&nbsp;<SPAN ID = 'usng_link' onClick = "do_usng_conv(res_add_Form)">USNG:</SPAN>
			<INPUT TYPE="text" SIZE=19 NAME="frm_ngs" VALUE="" disabled /></TD></TR>
		<TR><TD COLSPAN=2 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD></TR>
		<TR CLASS = "odd"><TD COLSPAN=2 ALIGN='center'>
			<INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="reset" VALUE="Reset" onClick = "do_add_reset(this.form);">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <!-- 1/22/09 -->
			<INPUT TYPE="submit" VALUE="Submit for Update"></TD></TR>
		<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_mobile' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_aprs' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_instam' VALUE=0 />
		<INPUT TYPE='hidden' NAME = 'frm_direcs' VALUE=1 />  <!-- note default -->
		</FORM></TABLE> <!-- end inner left -->
		</TD><TD ALIGN='center'>
		<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
		<BR /><BR /><B>Drag/Click to unit location</B>
		<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A>

		<BR /><BR />Units:&nbsp;&nbsp;&nbsp;&nbsp;
<?php
		print get_icon_legend ();
?>

		</TD></TR></TABLE><!-- end outer -->

<?php
		map("a",get_variable('def_lat') , get_variable('def_lng'), FALSE) ;				// call GMap js ADD mode, no icon
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- 1100 -->
		</BODY>
		<SCRIPT>
//		if (!(document.res_add_Form.frm_lat.value=="")){
//			do_ngs();		// 1/24/09
//			}
		</SCRIPT>
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
		$is_mobile = (($row['mobile']==1) && ($row['callsign'] != ''));		// 1/27/09

//		dump($row);
		$lat = $row['lat'];
		$lng = $row['lng'];

		$type_checks = array ("", "", "", "", "");
		$type_checks[$row['type']] = " checked";
		$mob_checked = (($row['mobile']==1))? " CHECKED" : "" ;				// 1/24/09
		$aprs_checked = (($row['aprs']==1))? " CHECKED" : "" ;
		$instam_checked = (($row['instam']==1))? " CHECKED" : "" ;			// 3/11/09
		$direcs_checked = (($row['direcs']==1))? " CHECKED" : "" ;			// 3/19/09

		print do_calls($id);								// generate JS calls array

?>
		</HEAD>
		<BODY onLoad = "ck_frames(); " onunload="GUnload()">
		<FONT CLASS="header">&nbsp;Edit unit '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)<BR /><BR />
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER=0 ID='editform'>
		<FORM METHOD="POST" NAME= "res_edit_Form" onSubmit="return validate(document.res_edit_Form);" ACTION="units.php?func=responder&goedit=true">


		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label">Type: <font color='red' size='-1'>*</font></TD><TD><FONT SIZE='-2'>
				<SELECT NAME='frm_type'>
<?php
	foreach ($u_types as $key => $value) {								// 1/9/09
		$temp = $value; 												// 2-element array
		$sel = ($row['type']==$temp[0])? " SELECTED ": "";
		print "\t\t\t\t<OPTION VALUE='" . $key .$sel. "'>" .$temp[0] . "</OPTION>\n";
		}

?>
				</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Mobile  &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" <?php print $mob_checked; ?> />
		</TD></TR>
		<TR CLASS = "odd" VALIGN='top'  TITLE = 'Check if YES'><TD CLASS="td_label">Operation:</TD>
			<TD>&nbsp;&nbsp;&nbsp;&nbsp;<!-- 1/21/09 -->
				APRS &raquo;<INPUT TYPE="checkbox" NAME="frm_aprs_disp" <?php print $aprs_checked; ?> />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				InstaMapper &raquo;<INPUT TYPE="checkbox" NAME="frm_instam_disp" <?php print $instam_checked; ?> />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Directions &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" <?php print $direcs_checked; ?> />
			</TD></TR>

		<TR CLASS = "even"><TD CLASS="td_label">Status:</TD>
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
			print "\t\t<OPTGROUP LABEL='$the_grp'>\n";
			}
		$sel = ($row['un_status_id']== $row_st['id'])? " SELECTED" : "";
		print "\t\t<OPTION VALUE=" . $row_st['id'] . $sel .">" . $row_st['status_val']. "</OPTION>\n";
		$i++;
		}
	print "\n\t\t</SELECT>\n";
	unset($result_st);
																							// check any assign records this unit - added 5/23/08
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id`=$id AND `clear` IS NULL";		// 6/27/08
//	dump($query);
	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$cbcount = mysql_affected_rows();				// count of incomplete assigns
	$dis_rmv = ($cbcount==0)? "": " DISABLED";		// allow/disallow removal
	$cbtext = ($cbcount==0)? "": "&nbsp;&nbsp;<FONT size=-2>(NA - calls in progress: " .$cbcount . " )</FONT>";
	$im_hint = ($instam_checked)? "&nbsp;&nbsp;<SPAN ID = 'instam_label' STYLE = 'visibility: visible'><I>(Label/API key)</I></SPAN>": "";
?>
	&nbsp;&nbsp;&nbsp;&nbsp;Callsign: <INPUT SIZE="12" MAXLENGTH="12" TYPE="text" NAME="frm_callsign" VALUE="<?php print $row['callsign'];?>" /><?php print $im_hint;?>
			</TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Name: <font color='red' size='-1'>*</font></TD>			<TD><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD>	<TD><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Capability: </TD>										<TD><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2><?php print $row['capab'];?></TEXTAREA></TD></TR>


		<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_via" VALUE="<?php print $row['contact_via'] ;?>" /></TD></TR>
<?php
		$map_capt = (!$is_mobile)? 	"<BR /><BR /><CENTER><B>Click to revise unit location</B>" : "";
		$lock_butt = (!$is_mobile)? "<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_edit_Form);'>" : "" ;
		$usng_link = (!$is_mobile)? "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_edit_Form)'>USNG:</SPAN>": "USNG:";
?>
		<TR CLASS = "even">
			<TD CLASS="td_label">
				<SPAN onClick = 'javascript: do_coords(document.res_edit_Form.frm_lat.value ,document.res_edit_Form.frm_lng.value  )' ><U>Lat/Lng</U></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;<?php print $lock_butt;?>
				</TD><TD>
			<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
			<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;
			<?php print $usng_link;?> <INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($row['lat'], $row['lng']) ;?>" SIZE=19 disabled />
			</TD></TR>	<!-- 9/13/08 -->

		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="odd" VALIGN='baseline'><TD CLASS="td_label">Remove Unit:</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
		<?php print $cbtext; ?></TD></TR>
		<TR CLASS = "even">
			<TD COLSPAN=2 ALIGN='center'><BR><INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="reset" VALUE="Reset" onClick="map_reset()";>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="submit" VALUE="Submit for Update"></TD></TR>
		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
		<INPUT TYPE="hidden" NAME = "frm_mobile" VALUE=<?php print $row['mobile'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_aprs" VALUE=<?php print $row['aprs'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_instam" VALUE=<?php print $row['instam'] ;?> />
		<INPUT TYPE="hidden" NAME = "frm_direcs" VALUE=<?php print $row['direcs'] ;?> />
		</FORM></TABLE>
		</TD><TD ALIGN='center'><DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset'></DIV>
		<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A><BR />

		<?php print $map_capt; ?></TD></TR></TABLE>
<?php
		print do_calls($id);					// generate JS calls array
		if (is_numeric($row['lat'])) {
			map("e", $lat, $lng, TRUE) ;		// do icon
			}
		else {									// mobile
			map("e", get_variable('def_lat'),  get_variable('def_lng'), FALSE) ;	// no icon
			}
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- 1231 -->
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
			$is_mobile = (($row['mobile']==1) && ($row['callsign'] != ''));				// 1/27/09
			$lat = $row['lat'];
			$lng = $row['lng'];

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

		$mob_checked = (!empty($row['mobile']))? " checked" : "" ;				// 1/24/09
		$aprs_checked = (!empty($row['aprs']))? " checked" : "" ;				// 3/11/09
		$instam_checked = (!empty($row['instam']))? " checked" : "" ;			// 3/11/09
		$direcs_checked = (!empty($row['direcs']))? " checked" : "" ;			// 3/19/09

?>
		<SCRIPT >
	var starting = false;					// 4/10/09

	function sv_win(theForm) {				// 2/11/09
		if(starting) {return;}				// dbl-click proof
		starting = true;

		var thelat = theForm.frm_lat.value;
		var thelng = theForm.frm_lng.value;
		var url = "street_view.php?thelat=" + thelat + "&thelng=" + thelng;
		newwindow_sl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (!(newwindow_sl)) {
			alert ("Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_sl.focus();
		starting = false;
		}		// end function sv win()


		</SCRIPT>
		</HEAD>
<?php
		if ($_dodisp == 'true') {				// dispatch
			print "\t<BODY onLoad = 'ck_frames(); do_disp();' onunload='GUnload()'>\n";
			}
		else {
			print "\t<BODY onLoad = 'ck_frames()' onunload='GUnload()'>\n";
			}

		$temp = $u_types[$row['type']];
		$the_type = $temp[0];			// name of type

?>
			<FONT CLASS="header">&nbsp;'<?php print $row['name'] ;?>' Data</FONT> (#<?php print$row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER=0 ID='view_unit' STYLE='display: block'>
			<FORM METHOD="POST" NAME= "res_view_Form" ACTION="units.php?func=responder">
			<TR CLASS = "even"><TD CLASS="td_label">Type: </TD>
				<TD><?php print $the_type;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Mobile  &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" <?php print $mob_checked; ?> DISABLED />

				</TD></TR> <!-- // 1/8/09 -->
			<TR CLASS = "odd" VALIGN='top'><TD CLASS="td_label">Operation:</TD>
				<TD>&nbsp;&nbsp;&nbsp;&nbsp;<!-- 1/21/09 -->
					APRS &raquo;<INPUT TYPE="checkbox" NAME="frm_aprs_disp"<?php print $aprs_checked; ?> DISABLED />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Instamapper &raquo;<INPUT TYPE="checkbox" NAME="frm_instam_disp"<?php print $instam_checked; ?> DISABLED />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Directions &raquo;<INPUT TYPE="checkbox" NAME="frm_instam_disp"<?php print $instam_checked; ?> DISABLED />
				</TD></TR>


			<TR CLASS = "even"><TD CLASS="td_label">Status:</TD>		<TD><?php print $un_st_val;?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Call: <?php print $row['callsign'];?>
			</TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Name: </TD>			<TD><?php print $row['name'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Description: </TD>	<TD><?php print $row['description'];?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Capability: </TD>	<TD><?php print $row['capab'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><?php print $row['contact_name'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Contact via:</TD>	<TD><?php print $row['contact_via'] ;?></TD></TR>

			<TR CLASS = 'even'><TD CLASS="td_label">As of:</TD>	<TD><?php print format_date($row['updated']); ?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"  onClick = 'javascript: do_coords(<?php print "$lat,$lng";?>)'><U>Lat/Lng</U>:</TD><TD>
				<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
				<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;&nbsp;USNG:
				<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($row['lat'], $row['lng']) ;?>" SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php
		if ((get_variable('UTM')==1)&& (!empty($lat))) {
			$coords =  $lat . "," . $lng;
			print "<TR CLASS='odd'><TD CLASS='td_label'>UTM Grid:</TD><TD>" . toUTM($coords) . "</TD></TR>\n";
			}

		if (isset($rowtr)) {																	// got tracks?
			print "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>APRS</B></TD></TR>";
			print "<TR CLASS='odd'><TD>Course: </TD><TD>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
			print "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $rowtr['closest_city'] . "</TD></TR>";
			print "<TR CLASS='odd'><TD>Status: </TD><TD>" . $rowtr['status'] . "</TD></TR>";
			print "<TR CLASS='even'><TD>As of: </TD><TD>" . format_date($rowtr['packet_date']) . " (UTC)</TD></TR>";
			$lat = $rowtr['latitude'];
			$lng = $rowtr['longitude'];
			}

		$toedit = (is_administrator() || is_super())? "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;": "" ;
?>
			<TR><TD>&nbsp;</TD></TR>
<?php
		if (is_administrator() || is_super()) {
?>
			<TR CLASS = "even"><TD COLSPAN=2 ALIGN='center'>
			<INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="to Edit" 	onClick= "to_edit_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
		if (!(empty($lat))) {						// 6/27/08 - dispatch able?
?>
			<INPUT TYPE="button" VALUE="to Dispatch" 	onClick= "$('incidents').style.display='block'; $('view_unit').style.display='none';">

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
			<TABLE BORDER=0 ID = 'incidents' STYLE = 'display:none' >
			<TR CLASS='even'><TH COLSPAN=99 CLASS='header'> Click incident to dispatch '<?php print $row['name'] ;?>'</TH></TR>
			<TR><TD></TD></TR>

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
				<INPUT TYPE="button" VALUE="Cancel" onClick = "$('incidents').style.display='none'; $('view_unit').style.display='block';">
			</TD></TR>
			</TABLE><BR><BR>
			</TD><TD ALIGN='center'><DIV ID='map' style="width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset"></DIV>
			<BR />
			<DIV ID="directions" STYLE="width: <?php print get_variable('map_width');?>"><BR />Click map point for directions</DIV>
			<BR /><SPAN onClick='doGrid()'><u>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN onClick='doTraffic()'><U>Traffic</U></SPAN>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN ID='do_sv' onClick = 'sv_win(document.res_view_Form)'><u>Street view</U></SPAN>
				<BR /><BR />		<!-- 4/10/09 -->

			</TD></TR></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "units.php?func=responder&edit=true&id=<?php print $id; ?>"></FORM>
			<FORM NAME="routes_Form" METHOD="get" ACTION = "routes.php">
			<INPUT TYPE="hidden" NAME="ticket_id" 	VALUE="">						<!-- 10/16/08 -->
			<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
			</FORM>
							<!-- END UNIT VIEW -->
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
			<!-- 1408 -->
			</BODY>
			</HTML>
<?php
			exit();
			}		// end if ($_GET['view'])
// ============================================= initial display =======================

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
			<BR />
			<SPAN onClick='doGrid()'><u>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN onClick='doTraffic()'><U>Traffic</U></SPAN><BR /><BR /><BR />		<!-- 4/10/09 -->
			<BR /><BR />
			Legend:
<?php
		print get_icon_legend ();
?>
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
			<!-- 1452 -->
			</BODY>				<!-- END RESPONDER LIST and ADD -->
<?php
		print do_calls();		// generate JS calls array

		$buttons = "<TR><TD COLSPAN=99 ALIGN='center'><BR /><INPUT TYPE = 'button' onClick = 'document.tracks_Form.submit();' VALUE='Unit Tracks'>";
		$buttons .= (is_guest())? "" :"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' value= 'Add a Unit'  onClick ='document.add_Form.submit();'>";	// 10/8/08
		$buttons .= "</TD></TR>";

		print list_responders($buttons, 0);				// ($addon = '', $start)
		print "\n</HTML> \n";
		exit();
		}				// end if($do_list_and_map)
    break;
?>
