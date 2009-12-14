<?php
/*
8/20/09 created facilities.php from units.php
10/6/09 Added links button
10/8/09 Index in list and on marker changed to part of name after /
10/8/09 Added Display name to remove part of name after / in name field of sidebar and in infotabs
10/29/09 Removed period after index in sidebar
11/11/09 Fixed sidebar display when not using map location
11/11/09 Made map location mandatory for form input, added 'top' anchor.
*/

error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
$key_field_size = 30;

extract($_GET);
extract($_POST);

if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}

$u_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);
	}
//dump($u_types);
unset($result);

$icons = $GLOBALS['fac_icons'];
$sm_icons = $GLOBALS['fac_icons'];

function get_icon_legend (){			// returns legend string
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `type`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$temp = $u_types[$row['type']];
		$print .= "\t\t" .$temp[0] . " &raquo; <IMG SRC = './icons/" . $sm_icons[$temp[1]] . "' BORDER=0>&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()
$interval = intval(get_variable('auto_poll'));		// array_key_exists ( mixed key, array search )
$refresh = ((!(array_key_exists ('func', $_GET ))) && ($interval>0))? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>": "";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Configuration Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<?php print $refresh; ?>	<!-- 10/4/08 -->

	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT  SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
	<SCRIPT  SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT  SRC='./js/graticule.js' type='text/javascript'></SCRIPT>
	<SCRIPT >

	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;

	function $() {
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

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		}		// end function ck_frames()

	function to_str(instr) {
		function ord( string ) {
		    return (string+'').charCodeAt(0);
			}

		function chr( ascii ) {
		    return String.fromCharCode(ascii);
			}
		function to_char(val) {
			return(chr(ord("A")+val));
			}

		var lop = (instr % 26);								// low-order portion, a number
		var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
		return hop+to_char(lop);
		}


	function do_usng_conv(theForm){						// usng to LL array
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

	function do_unlock_pos(theForm) {
		theForm.frm_ngs.disabled=false;
		$("lock_p").style.visibility = "hidden";
		$("usng_link").style.textDecoration = "underline";
		}

	function do_coords(inlat, inlng) {
		if(inlat.toString().length==0) return;
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's
		var d = new Number(inval);
		d  = (inval>0)?  Math.floor(d):Math.round(d);
		var mi = (inval-d)*60;
		var m = Math.floor(mi)				// min's
		var si = (mi-m)*60;
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	function lat2ddm(inlat) {				// lat to degr, dec min's
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

	function do_lat_fmt(inlat) {
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

	var grid_obj = new LatLonGraticule();;
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

	function doTraffic() {
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

	function do_mail_win() {
		if(starting) {return;}					
		starting=true;	
	
		newwindow_um=window.open('do_unit_mail.php', 'E_mail_Window',  'titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');

		if (isNull(newwindow_um)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_um.focus();
		starting = false;
		}

	function do_mail_in_win(id) {			// individual email
		if(starting) {return;}					
		starting=true;	
		var url = "do_fac_mail.php?fac_id=" + id;	
		newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height=300,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
		if (isNull(newwindow_in)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_in.focus();
		starting = false;
		}


	function to_routes(id) {
		document.routes_Form.ticket_id.value=id;
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

	function validate(theForm) {						// Facility form contents validation
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				var str = "Please confirm removing '" + theForm.frm_name.value + "'";
				if(confirm(str)) 	{
					theForm.submit();
					return true;}
				else 				{return false;}
				}
			}

		var errmsg="";
		if (theForm.frm_name.value.trim()=="")											{errmsg+="Facility NAME is required.\n";}
		if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)			{errmsg+="Facility TYPE is required.\n";}
		if (theForm.frm_status_id.options[theForm.frm_status_id.selectedIndex].value==0)	{errmsg+="Facility STATUS is required.\n";}
		if (theForm.frm_descr.value.trim()=="")											{errmsg+="Facility DESCRIPTION is required.\n";}
		if ((theForm.frm_lat.value=="") || (theForm.frm_lng.value==""))					{errmsg+="Facility LOCATION must be set - click map location to set.\n";}	// 11/11/09 position mandatory
		
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {														// good to go!
			top.upper.calls_start();
			theForm.submit();
//			return true;
			}
		}				// end function va lidate(theForm)

	function old_validate(theForm) {						// Facility form contents validation
		if (theForm.frm_remove) {
			if (theForm.frm_remove.checked) {
				var str = "Please confirm removing '" + theForm.frm_name.value + "'";
				if(confirm(str)) 	{return true;}
				else {return false;}
				}
			}

		var errmsg="";
		if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)				{errmsg+="Facility TYPE is required.\n";}	
		if (theForm.frm_status_id.options[theForm.frm_status_id.selectedIndex].value==0)			{errmsg+="Facility STATUS is required.\n";}
		if (theForm.frm_name.value.trim()=="")									{errmsg+="Facility NAME is required.\n";}
		if (theForm.frm_descr.value.trim()=="")									{errmsg+="Facility DESCRIPTION is required.\n";}
		
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {														// good to go!
			top.upper.calls_start();
			theForm.submit();
//			return true;
			}
		}				// end function va lidate(theForm)

	function add_res () {		// turns on add responder form
		showit('res_add_form');
		hideit('tbl_facilities');
		hideIcons();			// hides responder icons
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}

	function hideIcons() {
		map.clearOverlays();
		}				// end function hideicons()

	function do_lat (lat) {
		document.forms[0].frm_lat.value=lat.toFixed(6);
		document.forms[0].show_lat.disabled=false;
		document.forms[0].show_lat.value=do_lat_fmt(document.forms[0].frm_lat.value);
		document.forms[0].show_lat.disabled=true;
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.value=lng.toFixed(6);
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

	function do_disp(){											// show incidents for dispatch
		$('incidents').style.display='block';
		$('view_unit').style.display='none';
		}

	function do_add_reset(the_form) {
//		map.clearOverlays();
		the_form.reset();
		do_ngs();
		}
	
	</SCRIPT>


<?php

function list_responders($addon = '', $start) {
	global $u_types, $my_session, $tolerance;

//	$assigns = array();
//	$tickets = array();

	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";

	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);
	$calls = array();
	$calls_nr = array();
	$calls_time = array();

	$query = "SELECT * , UNIX_TIMESTAMP(packet_date) AS `packet_date` FROM `$GLOBALS[mysql_prefix]tracks` ORDER BY `packet_date` ASC";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	while ($row = mysql_fetch_array($result)) {
		if (isset($calls[$row['source']])) {		// array_key_exists ( mixed key, array search )
			$calls_nr[$row['source']]++;
			}
		else {
			array_push ($calls, trim($row['source']));
			$calls[trim($row['source'])] = TRUE;
			$calls_nr[$row['source']] = 1;
			}
		$calls_time[$row['source']] = $row['packet_date'];		// save latest - note query order
		}

	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]facilities`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$facilities = mysql_affected_rows()>0 ?  mysql_affected_rows(): "<I>none</I>";
	unset($result);

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




	function createMarker(point, tabs, color, id, fac_id) {						// (point, myinfoTabs,<?php print $row['type'];?>, i)
		points = true;													// at least one
//		var letter = to_str(id);
		var fac_id = fac_id;	
		
		var icon = new GIcon(listIcon);
		var icon_url = "./icons/gen_fac_icon.php?blank=" + escape(icons[color]) + "&text=" + fac_id;

		icon.image = icon_url;		// 

		var marker = new GMarker(point, icon);
		marker.id = color;				// for hide/unhide - unused

		GEvent.addListener(marker, "click", function() {		// here for both side bar and icon click
			if (marker) {
				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				marker.openInfoWindowTabsHtml(infoTabs[id]);

				setTimeout(function() {										// wait for rendering complete
					if ($("detailmap")) {
						var dMapDiv = $("detailmap");
						var detailmap = new GMap2(dMapDiv);
						detailmap.addControl(new GSmallMapControl());
						detailmap.setCenter(point, 17);  						// larger # = closer
						detailmap.addOverlay(marker);
						}
					else {
						}
					},4000);				// end setTimeout(...)

				}		// end if (marker)
			});			// end GEvent.add Listener()

		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
		if (!(map_is_fixed)) {
			bounds.extend(point);
			}
		return marker;
		}				// end function create Marker()

	function do_sidebar (sidebar, id, the_class, fac_id) {
//		var letter = to_str(id)
		var fac_id = fac_id;

		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"' onClick = myclick(" + id + ");>";
		side_bar_html += "<TD CLASS='" + the_class + "'>" + fac_id + sidebar +"</TD></TR>\n";	//10/29/09 removed period
		}

	function do_sidebar_nm (sidebar, line_no, id, fac_id) {							// no map - view responder // view_Form, 11/11/09
		var letter = to_str(line_no);
		var fac_id = fac_id;
		
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"' onClick = myclick_nm(" + id + ");>";
		side_bar_html += "<TD CLASS='td_label'>" + fac_id + sidebar +"</TD></TR>\n";	//10/29/09 removed period, 11/11/09 fixed display when no map postion
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [id]
		GEvent.trigger(gmarkers[id], "click");
		location.href = '#top';		// 11/11/090
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
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$icons = $GLOBALS['fac_icons'];

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
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_facilities'>";
	side_bar_html += "<TR class='even'>	<TD colspan='99' ALIGN='center'><B>Facilities (<?php print $facilities; ?>)</B></TD></TR>";
	side_bar_html += "<TR class='odd'>	<TD colspan='99' ALIGN='center'>Click line or icon for details</TD></TR>";
	side_bar_html += "<TR class='even'>	<TD></TD><TD ALIGN='center'>Name</TD><TD ALIGN='center'>Type&nbsp;&nbsp;</TD><TD ALIGN='center'>Status</TD><TD ALIGN='center'>As of</TD></TR>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = <?php print $start; ?>;					// sidebar/icon index
	var points = false;								// none

	map = new GMap2($("map"));						// create the map
<?php
$maptype = get_variable('maptype');

	switch($maptype) { 
		case "1":
		break;

		case "2":?>
		map.setMapType(G_SATELLITE_MAP);<?php
		break;
	
		case "3":?>
		map.setMapType(G_PHYSICAL_MAP);<?php
		break;
	
		case "4":?>
		map.setMapType(G_HYBRID_MAP);<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}
?>

	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>

	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
	mapBounds=new GLatLngBounds(map.getBounds().getSouthWest(), map.getBounds().getNorthEast());

	var bounds = new GLatLngBounds();						// create  bounding box
	map.enableScrollWheelZoom();

	var listIcon = new GIcon();
	listIcon.image = "./markers/yellow.png";	// yellow.png - 16 X 28
	listIcon.shadow = "./markers/sm_shadow.png";
	listIcon.iconSize = new GSize(30, 30);
	listIcon.shadowSize = new GSize(16, 28);
	listIcon.iconAnchor = new GPoint(8, 28);
	listIcon.infoWindowAnchor = new GPoint(9, 2);
	listIcon.infoShadowAnchor = new GPoint(18, 25);

	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		map.addOverlay(gmarkers[which])
		});

<?php

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `id`";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);

	$type_vals = array();											// build array of $status_vals
	$type_vals[''] = $type_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";
	$result_ty = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_ty = stripslashes_deep(mysql_fetch_array($result_ty))) {
		$temp = $row_ty['id'];
		$type_vals[$temp] = $row_ty['name'];
		}
	unset($result_ty);

	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `type`";	//
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$i=0;				// counter
// =============================================================================
	$utc = gmdate ("U");
	while ($row = stripslashes_deep(mysql_fetch_array($result))) {		// ==========  major while() for Facility ==========
		$got_point = FALSE;
		print "\n\t\tvar i=$i;\n";

	if(is_guest()) {
		$toedit = $tomail = $toroute = "";
		}
	else {
		$toedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='facilities.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>" ;
		$tomail = "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = 'do_mail_in_win({$row['id']})'><U><B>Email</B></U></SPAN>" ;
		$toroute = "&nbsp;<A HREF='fac_routes.php?fac_id=" . $row['id'] . "'><U>Route To Facility</U></A>";	
		}
		

		$temp = $row['status_id'] ;	
		$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";	

		$temp_type = $row['type'] ;	
		$the_type = (array_key_exists($temp_type, $type_vals))? $type_vals[$temp_type] : "??";

		if (!($got_point) && ((my_is_float($row['lat'])))) {
			echo "\t\tvar point = new GLatLng(" . $row['lat'] . ", " . $row['lng'] .");\n";
			$got_point= TRUE;
			}

		$update_error = strtotime('now - 6 hours');							// set the time for silent setting
// name

		$name = $row['name'];		//	10/8/09
		$temp = explode("/", $name );
		$display_name = $temp[0];

		$sidebar_line = "<TD TITLE = '" . addslashes($display_name) . "'><U>" . addslashes(shorten($display_name, 24)) ."</U></TD>";	//	10/8/09

		$sidebar_line .= "<TD CLASS='td_data' TITLE = '" . addslashes ($the_type) . "'> " . shorten($the_type, 10) .
				"&nbsp;&nbsp;</TD>";
		$sidebar_line .= "<TD CLASS='td_data' TITLE = '" . addslashes ($the_status) . "'> " . shorten($the_status, 10) .
				"&nbsp;&nbsp;</TD>";	

// as of
		$strike = $strike_end = "";
		$the_time = $row['updated'];
		$the_class = "td_data";

		$strike = $strike_end = "";

		$sidebar_line .= "<TD CLASS='$the_class'> $strike" . format_sb_date($the_time) . "$strike_end</TD>";
// tab 1

		if (my_is_float($row['lat'])) {										// position data?
			$temptype = $u_types[$row['type']];
			$the_type = $temptype[0];
		
			$tab_1 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($display_name, 48)) . "</B> - " . $the_type . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(shorten(str_replace($eols, " ", $row['description']), 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . $the_status . " </TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['contact_name']). " Via: " . addslashes($row['contact_email']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date($row['updated']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $toroute . $toedit . $tomail ."&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='facilities.php?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";
			$tab_1 .= "</TABLE>";

			$tab_2 = "<TABLE CLASS='infowin' width='" . $my_session['scr_width']/4 . "'>";
			$tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['security_contact']) . " </TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['security_email']) . " </TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['security_phone']) . " </TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row['access_rules'])) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row['security_reqs'])) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row['opening_hours'])) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['pager_p']) . " </TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['pager_s']) . " </TD></TR>";
			$tab_2 .= "</TABLE>";


// tab 2
		$tabs_done=FALSE;		// default

		if (!($tabs_done)) {	//
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row['name'], 10)));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $tab_2);?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];

<?php
			}		// end if/else

		$name = $row['name'];	// 10/8/09		
		$temp = explode("/", $name );
		$index =  (strlen($temp[count($temp) -1])<3)? substr($temp[count($temp) -1] ,0,strlen($temp[count($temp) -1])): substr($temp[count($temp) -1] ,-3 ,strlen($temp[count($temp) -1]));
			
?>
		var fac_id = "<?php print $index;?>";	//	10/8/09
		var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";

		do_sidebar ("<?php print $sidebar_line; ?>", i, the_class, fac_id);
		var marker = createMarker(point, myinfoTabs,<?php print $row['type'];?>, i, fac_id);	// 771 (point,tabs, color, id)
		map.addOverlay(marker);
<?php
		}		// end position data available
		else {
			$name = $row['name'];	// 11/11/09		
			$temp = explode("/", $name );
			$index =  (strlen($temp[count($temp) -1])<3)? substr($temp[count($temp) -1] ,0,strlen($temp[count($temp) -1])): substr($temp[count($temp) -1] ,-3 ,strlen($temp[count($temp) -1]));
			
?>
			var fac_id = "<?php print $index;?>";	//	11/11/09
<?php		
			print "\tdo_sidebar_nm (\" {$sidebar_line} \" , i, {$row['id']}, fac_id);\n";	// sidebar only - no map
			}

	$i++;				// zero-based
	}				// end  ==========  while() for Facility ==========


?>
	if (!(map_is_fixed)) {
		if (!points) {		// any?
			map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);
			map.setCenter(center,zoom);
			}
		}

	side_bar_html+= "<TR CLASS='" + colors[i%2] +"'></TR>";
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

function map($mode, $lat, $lng, $icon) {						// Facility add, edit, view
	$have_coords = is_numeric($lat);
	$the_lat = my_is_float($lat)? $lat : get_variable('def_lat')  ;
	$the_lng = my_is_float($lat)? $lng : get_variable('def_lng')  ;

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
<?php
	$maptype = get_variable('maptype');

	switch($maptype) { 
		case "1":
		break;

		case "2":?>
		map.setMapType(G_SATELLITE_MAP);<?php
		break;
	
		case "3":?>
		map.setMapType(G_PHYSICAL_MAP);<?php
		break;
	
		case "4":?>
		map.setMapType(G_HYBRID_MAP);<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}

?>

	var	gdir = new GDirections(map, $("directions"));

   	G_START_ICON.image = "";
   	G_END_ICON.image = "";

	var bounds = new GLatLngBounds();										// create empty bounding box

	var myZoom;						// note globals
	var marker;

	var myIcon = new GIcon();
	myIcon.image = "./markers/yellow.png";
	myIcon.shadow = "./markers/sm_shadow.png";
	myIcon.iconSize = new GSize(16, 28);
	myIcon.shadowSize = new GSize(16, 28);
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
		var point = new GLatLng(<?php print $the_lat . ", " . $the_lng; ?>);
		var marker = new GMarker(point, {icon: myIcon, draggable:false});
		map.addOverlay(new GMarker(point, myIcon));

<?php
			}		// end if ($icon)

	else {
?>
		var baseIcon = new GIcon();
		baseIcon.iconSize=new GSize(30,30);
		baseIcon.iconAnchor=new GPoint(16,16);
		var cross = new GIcon(baseIcon, "./markers/crosshair.png", null);
		var center = new GLatLng(<?php print get_variable('def_lat') ?>, <?php print get_variable('def_lng'); ?>);
		map.setCenter(center, <?php print get_variable('def_zoom');?>);
		var thisMarker  = new GMarker(center, {icon: cross, draggable:false} );
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
			else if (gdir.getStatus().code == G_GEO_BAD_KEY)
				alert("448: The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir.getStatus().code);
			else if (gdir.getStatus().code == G_GEO_BAD_REQUEST)
				alert("450: A directions request could not be successfully parsed.\n Error code: " + gdir.getStatus().code);
			else alert("451: An unknown error occurred.");
			}		// end function handleErrors()


	    function setDirections(fromAddress, toAddress, locale) {
	    	var Direcs = gdir.load("from: " + fromAddress + " to: " + toAddress, { "locale": locale, preserveViewport : true  });
			GEvent.addListener(Direcs, "addoverlay", GEvent.callback(Direcs, cb()));
	    	}		// end function set Directions()

	    function cb() {
//			alert(847);	    							// onto floor ??
	    	}

		GEvent.addListener(map, "click", function(marker, point) {

			bounds.extend(point);								// endpoint to bounding box
	    	var the_start = new GLatLng(<?php print $the_lat;?>, <?php print $the_lng;?>);
	    	bounds.extend(the_start);									// start to bounding box

			var the_start = "<?php print $the_lat . " " . $the_lng;?>";
			var the_end = point.lat().toFixed(6).toString() + " " + point.lng().toFixed(6).toString();

			center = bounds.getCenter();
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
	if ((mode=="a") || (mode=="e")){
		the_marker = new GMarker(map.getCenter(), {draggable: true	});

		GEvent.addListener(map, "click", function(overlay, latlng) {

			if (latlng) {
				map.clearOverlays();
				marker = new GMarker(latlng, {draggable:true});
				map.setCenter(marker.getPoint(), the_zoom);
				do_lat(marker.getPoint().lat());			// set form values
				do_lng(marker.getPoint().lng());
				do_ngs();

				GEvent.addListener(marker, "dragend", function() {
					map.setCenter(marker.getPoint(), 13);
					do_lat (marker.getPoint().lat());		// set form values
					do_lng (marker.getPoint().lng());
					do_ngs();

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
		require_once('./incs/links.inc.php');	// 10/6/09
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
		print "</FORM></BODY></HTML>";
		}

	function do_calls($id = 0) {				// generates js callsigns array
		$print = "\n<SCRIPT >\n";
		$print .="\t\tvar calls = new Array();\n";
		$query	= "SELECT `id`, `callsign` FROM `$GLOBALS[mysql_prefix]facilities` where `id` != $id";
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
	if ($_postfrm_remove == 'yes') {					//delete Facility - checkbox
		$query = "DELETE FROM $GLOBALS[mysql_prefix]facilities WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Facility <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? "NULL" : quote_smart(trim($_POST['frm_lat'])) ;
			$the_lng = empty($_POST['frm_lng'])? "NULL" : quote_smart(trim($_POST['frm_lng'])) ;
			$query = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET
				`name`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`handle`= " . 		quote_smart(trim($_POST['frm_handle'])) . ",
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",
				`capab`= " . 		quote_smart(trim($_POST['frm_capab'])) . ",
				`status_id`= " . quote_smart(trim($_POST['frm_status_id'])) . ",
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`contact_name`= " . quote_smart(trim($_POST['frm_contact_name'])) . ",
				`contact_email`= " . 	quote_smart(trim($_POST['frm_contact_email'])) . ",
				`contact_phone`= " . 	quote_smart(trim($_POST['frm_contact_phone'])) . ",
				`security_contact`= " . quote_smart(trim($_POST['frm_security_contact'])) . ",
				`security_email`= " . 	quote_smart(trim($_POST['frm_security_email'])) . ",
				`security_phone`= " . 	quote_smart(trim($_POST['frm_security_phone'])) . ",
				`opening_hours`= " . 	quote_smart(trim($_POST['frm_opening_hours'])) . ",
				`access_rules`= " . 	quote_smart(trim($_POST['frm_access_rules'])) . ",
				`security_reqs`= " . 	quote_smart(trim($_POST['frm_security_reqs'])) . ",
				`pager_p`= " . 	quote_smart(trim($_POST['frm_pager_p'])) . ",
				`pager_s`= " . 	quote_smart(trim($_POST['frm_pager_s'])) . ",
				`type`= " . 		quote_smart(trim($_POST['frm_type'])) . ",
				`user_id`= " . 		quote_smart(trim($my_session['user_id'])) . ",
				`updated`= " . 		quote_smart(trim($now)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_UNIT_STATUS'], 0, $_POST['frm_id'], $_POST['frm_status_id']);}
			$caption = "<i>" . stripslashes_deep($_POST['frm_name']) . "</i><B>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {

		$frm_lat = (empty($_POST['frm_lat']))? 'NULL': quote_smart(trim($_POST['frm_lat']));
		$frm_lng = (empty($_POST['frm_lng']))? 'NULL': quote_smart(trim($_POST['frm_lng']));
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]facilities` (
			`name`, `handle`, `description`, `capab`, `status_id`, `contact_name`, `contact_email`, `contact_phone`, `security_contact`, `security_email`, `security_phone`, `opening_hours`, `access_rules`, `security_reqs`, `pager_p`, `pager_s`, `lat`, `lng`, `type`, `user_id`, `updated` )
			VALUES (" .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_handle'])) . "," .
				quote_smart(trim($_POST['frm_descr'])) . "," .
				quote_smart(trim($_POST['frm_capab'])) . "," .
				quote_smart(trim($_POST['frm_status_id'])) . "," .
				quote_smart(trim($_POST['frm_contact_name'])) . "," .
				quote_smart(trim($_POST['frm_contact_email'])) . "," .
				quote_smart(trim($_POST['frm_contact_phone'])) . "," .
				quote_smart(trim($_POST['frm_security_contact'])) . "," .
				quote_smart(trim($_POST['frm_security_email'])) . "," .
				quote_smart(trim($_POST['frm_security_phone'])) . "," .
				quote_smart(trim($_POST['frm_opening_hours'])) . "," .
				quote_smart(trim($_POST['frm_access_rules'])) . "," .
				quote_smart(trim($_POST['frm_security_reqs'])) . "," .
				quote_smart(trim($_POST['frm_pager_p'])) . "," .
				quote_smart(trim($_POST['frm_pager_s'])) . "," .
				$frm_lat . "," .
				$frm_lng . "," .
				quote_smart(trim($_POST['frm_type'])) . "," .
				quote_smart(trim($my_session['user_id'])) . "," .
				quote_smart(trim($now)) . ");";

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		do_log($GLOBALS['LOG_UNIT_STATUS'], 0, mysql_insert_id(), $_POST['frm_status_id']);

		$caption = "<B>Facility  <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";

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
		<?php
		require_once('./incs/links.inc.php');
		?>
		<TABLE BORDER=0 ID='outer' BORDER=><TR><TD>
		<TABLE BORDER="0" ID='addform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>Add Facility</FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>		
		<FORM NAME= "res_add_Form" METHOD="POST" ACTION="facilities.php?func=responder&goadd=true">
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker">Name</A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility">Handle</A>:&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_handle" VALUE="" /></TD></TR>
		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label"><A HREF="#" TITLE="Facility Type - Select from pulldown menu">Type</A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN='left'><SELECT NAME='frm_type'><OPTION VALUE=0>Select one</OPTION>
<?php
	foreach ($u_types as $key => $value) {
		$temp = $value; 												// 2-element array
		print "\t\t\t\t<OPTION VALUE='" . $key . "'>" .$temp[0] . "</OPTION>\n";
		}
?>
			</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<A HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked /></TD>
			</TR>

		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility Status - Select from pulldown menu">Status</A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN ='left'><SELECT NAME="frm_status_id" onChange = "document.res_add_Form.frm_log_it.value='1'">
				<OPTION VALUE=0 SELECTED>Select one</OPTION>
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";
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
			</TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3 ><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution">Capability</A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility main contact name">Contact name</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility contact email - main contact email address">Contact email</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility contact phone number - main contact phone number">Contact phone</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility security contact">Security contact</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility security contact email">Security email</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility security contact phone number">Security phone</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc.">Opening hours</A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_opening_hours" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc">Access rules</A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_access_rules" COLS=40 ROWS=5></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility securtiy requirements - e.g. phone security first, visitors must be security cleared etc.">Security reqs</A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_security_reqs" COLS=40 ROWS=5></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility contact primary pager number">Pager Primary</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility contact secondary pager number">Pager Secondary</A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Latitude and Longitude - set from map click">
			<SPAN onClick = 'javascript: do_coords(document.res_add_Form.frm_lat.value ,document.res_add_Form.frm_lng.value)'>
				Lat/Lng</A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle'
					onClick = 'do_unlock_pos(document.res_add_Form);'><TD COLSPAN=3>
			<INPUT TYPE="text" NAME="show_lat" SIZE=11 VALUE="" disabled />
			<INPUT TYPE="text" NAME="show_lng" SIZE=11 VALUE="" disabled />&nbsp;&nbsp;
<?php
	$locale = get_variable('locale');
	switch($locale) { 
		case "0":
		case "2":
?>
		<SPAN ID = 'usng_link' onClick = "do_usng_conv(res_add_Form)">USNG:</SPAN><INPUT TYPE="text" SIZE=19 NAME="frm_ngs" VALUE="" disabled /></TD></TR>
<?php
		break;

		case "1":
?>
		<SPAN ID = 'usng_link' onClick = "do_usng_conv(res_add_Form)"></SPAN><INPUT TYPE="hidden" SIZE=19 NAME="frm_ngs" VALUE="" disabled /></TD></TR>
<?php
		break;
	
		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				

	}
?>

		<TR><TD COLSPAN=4 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD></TR>
		<TR CLASS = "even"><TD COLSPAN=4 ALIGN='center'>
			<INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="reset" VALUE="Reset" onClick = "do_add_reset(this.form);">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="Submit for Update"  onClick="validate(document.res_add_Form);" ></TD></TR>
		<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_direcs' VALUE=1 />  <!-- note default -->
		</FORM></TABLE> <!-- end inner left -->
		</TD><TD ALIGN='center'>
		<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
		<BR /><BR /><B>Drag/Click to unit location</B>
		<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A>

		<BR /><BR /><B>Facilities:</B>&nbsp;&nbsp;&nbsp;&nbsp;
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
		</HTML>
<?php
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($_getedit == 'true') {
		$id = $_GET['id'];
		$query	= "SELECT * FROM $GLOBALS[mysql_prefix]facilities WHERE id=$id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_array($result);
		$is_mobile = FALSE;

		$lat = $row['lat'];
		$lng = $row['lng'];
		$type = $row['type'];

		$type_checks = array ("", "", "", "", "");
		$type_checks[$row['type']] = " checked";
		$direcs_checked = (($row['direcs']==1))? " CHECKED" : "" ;

//		print do_calls($id);								// generate JS calls array
?>
		</HEAD>
		<BODY onLoad = "ck_frames(); " onunload="GUnload()">
		<?php
		require_once('./incs/links.inc.php');
		?>
		<TABLE BORDER=0 ID='outer'><TR><TD>
		<TABLE BORDER=0 ID='editform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit Facility '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>
		<FORM METHOD="POST" NAME= "res_edit_Form" ACTION="facilities.php?func=responder&goedit=true">

		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker">Name</A>:&nbsp;<font color='red' size='-1'>*</font></TD>			<TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility">Handle</A>:&nbsp;</TD>			<TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_handle" VALUE="<?php print $row['handle'] ;?>" /></TD></TR>
		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label"><A HREF="#" TITLE="Facility Type - Select from pulldown menu">Type</A>:&nbsp;<font color='red' size='-1'>*</font></TD>
		<TD ALIGN='left'><FONT SIZE='-2'>
			<SELECT NAME='frm_type'>
<?php
	foreach ($u_types as $key => $value) {
		$temp = $value; 												// 2-element array
		$sel = ($row['type']==$key)? " SELECTED": "";
		print "\t\t\t\t<OPTION VALUE='{$key}'{$sel}>{$temp[0]}</OPTION>\n";
		}
?>
				</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<A HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked /></TD>
				
		</TD>
		</TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility Status - Select from pulldown menu">Status</A>:&nbsp;</TD>
			<TD ALIGN='left'><SELECT NAME="frm_status_id" onChange = "document.res_edit_Form.frm_log_it.value='1'">
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `status_val` ASC, `group` ASC, `sort` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		if ($the_grp != $row_st['group']) {
			print ($i == 0)? "": "</OPTGROUP>\n";
			$the_grp = $row_st['group'];
			print "\t\t<OPTGROUP LABEL='$the_grp'>\n";
			}
		$sel = ($row['status_id']== $row_st['id'])? " SELECTED" : "";
		print "\t\t<OPTION VALUE=" . $row_st['id'] . $sel .">" . $row_st['status_val']. "</OPTION>\n";
		$i++;
		}
	print "\n\t\t</SELECT>\n";
	unset($result_st);

	$dis_rmv = " ENABLED";
?>
			</TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution">Capability</A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2><?php print $row['capab'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility main contact name">Contact name</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility contact email - main contact email address">Contact email</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="<?php print $row['contact_email'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility contact phone number - main contact phone number">Contact phone</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="<?php print $row['contact_phone'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility security contact">Security contact</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="<?php print $row['security_contact'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility security contact email">Security email</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="<?php print $row['security_email'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility security contact phone number">Security phone</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="<?php print $row['security_phone'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc.">Opening hours</A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_opening_hours" COLS=40 ROWS=2><?php print $row['opening_hours'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc">Access rules</A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_access_rules" COLS=40 ROWS=5><?php print $row['access_rules'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility securtiy requirements - e.g. phone security first, visitors must be security cleared etc.">Security reqs</A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_security_reqs" COLS=40 ROWS=5><?php print $row['security_reqs'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A HREF="#" TITLE="Facility contact primary pager number">Pager Primary</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="<?php print $row['pager_p'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A HREF="#" TITLE="Facility contact secondary pager number">Pager Secondary</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="<?php print $row['pager_s'] ;?>" /></TD></TR>

<?php
		$map_capt = (!$is_mobile)? 	"<BR /><BR /><CENTER><B>Click to revise facility location</B>" : "";
		$lock_butt = (!$is_mobile)? "<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_edit_Form);'>" : "" ;
		$usng_link = (!$is_mobile)? "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_edit_Form)'>USNG:</SPAN>": "USNG:";
?>
		<TR CLASS = "odd">
			<TD CLASS="td_label">
				<SPAN onClick = 'javascript: do_coords(document.res_edit_Form.frm_lat.value ,document.res_edit_Form.frm_lng.value  )' ><A HREF="#" TITLE="Latitude and Longitude - set from map click">
				Lat/Lng</A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;<?php print $lock_butt;?>
				</TD>
			<TD COLSPAN=3>
				<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
				<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;

<?php
	$locale = get_variable('locale');
	switch($locale) { 
		case "0":
		print $usng_link;?> <INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($row['lat'], $row['lng']) ;?>" SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php 	break;

		case "1":
?> 
		&nbsp;OSGB:<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoOSGB($row['lat'], $row['lng']) ;?>" SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php 
		break;

		default:
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				
		
		}
?>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="even" VALIGN='baseline'><TD CLASS="td_label"><A HREF="#" TITLE="Delete Facility from system">Remove Facility</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
		</TD></TR>
		<TR CLASS = "odd">
			<TD COLSPAN=4 ALIGN='center'><BR><INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="reset" VALUE="Reset" onClick="map_reset()";>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="button" VALUE="Submit for Update" onClick="validate(document.res_edit_Form);"></TD></TR>
		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
		</FORM></TABLE>
		</TD><TD ALIGN='center'><DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset'></DIV>
		<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A><BR />

		<?php print $map_capt; ?></TD></TR></TABLE>
<?php
		if (my_is_float($row['lat'])) {
			map("e", $lat, $lng, TRUE) ;
			}
		else {
			map("e", get_variable('def_lat'),  get_variable('def_lng'), FALSE) ;
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
			$query	= "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`=$id LIMIT 1";

			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= stripslashes_deep(mysql_fetch_assoc($result));
			$lat = $row['lat'];
			$lng = $row['lng'];

			if (isset($row['status_id'])) {
				$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` WHERE `id`=" . $row['status_id'];	// status value
				$result_st	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
				$row_st	= mysql_fetch_assoc($result_st);
				unset($result_st);
				}
			$un_st_val = (isset($row['status_id']))? $row_st['status_val'] : "?";
			$type_checks = array ("", "", "", "", "", "");
			$type_checks[$row['type']] = " checked";
			$coords =  $row['lat'] . "," . $row['lng'];		// for UTM

		$direcs_checked = (!empty($row['direcs']))? " checked" : "" ;

?>
		<SCRIPT >
	var starting = false;

	function sv_win(theForm) {
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
		print "\t<BODY onLoad = 'ck_frames()' onunload='GUnload()'>\n";
		print "<A NAME='top'>\n";			// 11/11/09
		require_once('./incs/links.inc.php');

		$temp = $u_types[$row['type']];
		$the_type = $temp[0];			// name of type

?>
			<FONT CLASS="header">&nbsp;'<?php print $row['name'] ;?>' Data</FONT> (#<?php print$row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER=0 ID='view_unit' STYLE='display: block'>
			<FORM METHOD="POST" NAME= "res_view_Form" ACTION="facilities.php?func=responder">
			<TR CLASS = "even"><TD CLASS="td_label">Name: </TD>			<TD><?php print $row['name'];?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Handle: </TD>			<TD><?php print $row['handle'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Type: </TD>
				<TD><?php print $the_type;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Status:</TD>		<TD><?php print $un_st_val;?>
			</TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Description: </TD>	<TD><?php print $row['description'];?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Capability: </TD>	<TD><?php print $row['capab'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Contact name:</TD>	<TD><?php print $row['contact_name'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Contact email:</TD>	<TD><?php print $row['contact_email'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Contact phone:</TD>	<TD><?php print $row['contact_phone'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Security contact:</TD>	<TD><?php print $row['security_contact'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Security email:</TD>	<TD><?php print $row['security_email'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Security phone:</TD>	<TD><?php print $row['security_phone'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Opening hours:</TD>	<TD><?php print $row['opening_hours'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Access rules:</TD>	<TD><?php print $row['access_rules'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Security reqs:</TD>	<TD><?php print $row['security_reqs'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Primary pager:</TD>	<TD><?php print $row['pager_p'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Secondary pager:</TD>	<TD><?php print $row['pager_s'] ;?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label">As of:</TD>	<TD><?php print format_date($row['updated']); ?></TD></TR>
<?php
		if (my_is_float($lat)) {
?>		
			<TR CLASS = "even"><TD CLASS="td_label"  onClick = 'javascript: do_coords(<?php print "$lat,$lng";?>)'><U>Lat/Lng</U>:</TD><TD>
				<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
				<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;

<?php
	$locale = get_variable('locale');
		switch($locale) { 
			case "0":?>
			&nbsp;USNG:<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($row['lat'], $row['lng']) ;?>" SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php 		break;

			case "1":?>
			&nbsp;OSGB:<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoOSGB($row['lat'], $row['lng']) ;?>" SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php
			break;
			default:
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				

			}

			}		// end if (my_is_float($lat))

		$toedit = (is_administrator() || is_super())? "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;": "" ;
?>
			<TR><TD>&nbsp;</TD></TR>
<?php
		if (is_administrator() || is_super()) {
?>
			<TR CLASS = "even"><TD COLSPAN=2 ALIGN='center'>
			<INPUT TYPE="button" VALUE="Cancel" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" VALUE="to Edit" 	onClick= "to_edit_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>" />
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>" />
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			</TD></TR>
<?php
			}		// end if (is_administrator() || is_super())
		print "</FORM></TABLE>\n";
?>
			<BR /><BR /><BR />
			</TD><TD ALIGN='center'><DIV ID='map' style="width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset"></DIV>
			<BR />
			<DIV ID="directions" STYLE="width: <?php print get_variable('map_width');?>"><BR />Click map point for directions</DIV>
			<BR /><SPAN onClick='doGrid()'><u>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN onClick='doTraffic()'><U>Traffic</U></SPAN>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN ID='do_sv' onClick = 'sv_win(document.res_view_Form)'><u>Street view</U></SPAN>
				<BR /><BR />
			</TD></TR></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "facilities.php?func=responder&edit=true&id=<?php print $id; ?>"></FORM>
			<INPUT TYPE="hidden" NAME="fac_id" 	VALUE="">						<!-- 10/16/08 -->
			<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
			</FORM>
							<!-- END UNIT VIEW -->
<?php
				if(!(my_is_float($lat))) {	
					map("v", get_variable('def_lat'),  get_variable('def_lng'), FALSE) ;	// default center, no icon
					}
				else {
					map("v", $lat, $lng, TRUE) ;						// do icon
					}

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
		<?php
		require_once('./incs/links.inc.php');
		?>
		<TABLE ID='outer'><TR><TD>
			<DIV ID='side_bar'></DIV>
			</TD><TD ALIGN='center'>
			<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR />
			<SPAN onClick='doGrid()'><u>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN onClick='doTraffic()'><U>Traffic</U></SPAN><BR /><BR /><BR />
			<BR /><BR />
			Facility Legend:
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

			<FORM NAME='can_Form' METHOD="post" ACTION = "facilities.php?func=responder"></FORM>
			<!-- 1452 -->
			</BODY>				<!-- END RESPONDER LIST and ADD -->
<?php
		print do_calls();		// generate JS calls array

		$buttons = "";
		if (!(is_guest())) {
			$buttons ="<TR><TD COLSPAN=99 ALIGN='center'><BR /><INPUT TYPE='button' value= 'Add a Facility'  onClick ='document.add_Form.submit();'>";
			}
		$buttons .= "</TD></TR>";

		print list_responders($buttons, 0);				// ($addon = '', $start)
		print "\n</HTML> \n";
		exit();
		}				// end if($do_list_and_map)
    break;
?>

