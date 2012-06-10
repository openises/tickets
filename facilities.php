<?php

error_reporting(E_ALL);
$facs_side_bar_height = .5;		// max height of facilities sidebar as decimal fraction of screen height - default is 0.6 (60%)
$zoom_tight = FALSE;				// replace with a decimal number to over-ride the standard default zoom setting
$iw_width= "300px";					// map infowindow with
/*
8/20/09 created facilities.php from units.php
10/6/09 Added links button
10/8/09 Index in list and on marker changed to part of name after /
10/8/09 Added Display name to remove part of name after / in name field of sidebar and in infotabs
10/29/09 Removed period after index in sidebar
11/11/09 Fixed sidebar display when not using map location
11/11/09 Made map location mandatory for form input, added 'top' anchor.
11/27/09 Changed edit 'Cancel' action
3/24/10 removed 'top' function calls
7/5/10 Added Location fields and phone number fields as for Incident. Geocoding of address and reverse geocoding of map click implemented.
7/7/10 mysql_fetch_array -> mysql_fetch_assoc
7/7/10 removed refresh, add mail button, list_xxx function name changed
7/22/10 NULL handling revised, miscjs, google reverse geocode parse added
7/27/10 unit-leel limitation applied
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
7/28/10 Added default icon for tickets entered in no-maps operation
8/13/10 map.setUIToDefault();
8/25/10 light top-frame button
11/29/10 locale 2 handling added
12/6/10 internet test relocated
2/17/11 Changed wrong log events from log_unit_status to LOG_FACILITY_ADD or LOG_FACILITY_CHANGE as appropriate
3/15/11 Added reference to stylesheet.php for revisable day night colors.
3/19/11 changed index length to 6 chars
4/27/11 icon logic added, top/bottom nav added
5/4/11 get_new_colors() added
7/1/11 permissions corrected
8/1/11 state length increased to 4 chars
6/10/11 Added Groups and Boundaries
*/

@session_start();	

if (!($_SESSION['internet'])) {				// 12/6/10
	header("Location: facilities_nm.php");
	}

require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));

$key_field_size = 30;
$st_size = (get_variable("locale") ==0)?  2: 4;		

extract($_GET);
extract($_POST);
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}
$usng = get_text('USNG');
$osgb = get_text('OSGB');

$u_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);

$icons = $GLOBALS['fac_icons'];
$sm_icons = $GLOBALS['sm_fac_icons'];	//	3/15/11

function get_icon_legend (){			// returns legend string
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `type`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$temp = $u_types[$row['type']];
		$print .= "\t\t<DIV class='legend' style='height: 3em; text-align: center; vertical-align: middle; float: left;'> ". $temp[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$temp[1]] . "' STYLE = 'vertical-align: middle' BORDER=0 PADDING='10'>&nbsp;&nbsp;&nbsp;</DIV>\n";
		}
	return $print;
	}			// end function get_icon_legend ()
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Facilities Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
	<SCRIPT  SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
	<SCRIPT  SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT  SRC="./js/lat_lng.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
	<SCRIPT  SRC="./js/geotools2.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
	<SCRIPT  SRC="./js/osgb.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->		
	<SCRIPT  SRC='./js/graticule.js' type='text/javascript'></SCRIPT>
	<SCRIPT  SRC='./js/misc_function.js' type='text/javascript'></SCRIPT>  <!-- 7/22/10 -->
	<script src="./js/epoly.js" type="text/javascript"> </script>	<!-- 6/10/11 -->		
	<SCRIPT TYPE="text/javascript" src="./js/ELabel.js"></SCRIPT> <!-- 8/1/11 -->	
	<SCRIPT>

	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();
	parent.upper.light_butt('facy');		// light the button - 8/25/10

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;

	function set_regions_control() {
		var reg_control = "<?php print get_variable('regions_control');?>";
		var regions_showing = "<?php print get_num_groups();?>";
		if(regions_showing) {
			if (reg_control == 0) {
				$('top_reg_box').style.display = 'none';
				$('regions_outer').style.display = 'block';
				} else {
				$('top_reg_box').style.display = 'block';
				$('regions_outer').style.display = 'none';			
				}
			}
		}

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

	function get_new_colors() {								// 5/4/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}

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
		map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);
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
	
		newwindow_um=window.open('do_fac_mail.php', 'E_mail_Window',  'titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');

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
		if (theForm.frm_handle.value.trim()=="")										{errmsg+="Facility HANDLE is required.\n";}
		if (theForm.frm_icon_str.value.trim()=="")										{errmsg+="Facility ICON is required.\n";}
		if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)			{errmsg+="Facility TYPE is required.\n";}
		if (theForm.frm_status_id.options[theForm.frm_status_id.selectedIndex].value==0)	{errmsg+="Facility STATUS is required.\n";}
		if (theForm.frm_descr.value.trim()=="")											{errmsg+="Facility DESCRIPTION is required.\n";}
		if ((theForm.frm_lat.value=="") || (theForm.frm_lng.value==""))					{errmsg+="Facility LOCATION must be set - click map location to set.\n";}	// 11/11/09 position mandatory
		
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {														// good to go!
//			top.upper.calls_start();
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
//			top.upper.calls_start();								// 3/24/10
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

// *********************************************************************
	function pt_to_map (my_form, lat, lng) {						// 7/5/10
		map.clearOverlays();	
		var loc = <?php print get_variable('locale');?>;
		my_form.frm_lat.value=lat;	
		my_form.frm_lng.value=lng;		
			
		my_form.show_lat.value=do_lat_fmt(my_form.frm_lat.value);
		my_form.show_lng.value=do_lng_fmt(my_form.frm_lng.value);
			
		if(loc == 0) {	
			my_form.frm_ngs.value=LLtoUSNG(my_form.frm_lat.value, my_form.frm_lng.value, 5);
			}
		if(loc == 1) {
			my_form.frm_ngs.value=LLtoOSGB(my_form.frm_lat.value, my_form.frm_lng.value, 5);
			}
		if(loc == 2) {
			my_form.frm_ngs.value=LLtoUTM(my_form.frm_lat.value, my_form.frm_lng.value, 5);
			}
	
		map.setCenter(new GLatLng(my_form.frm_lat.value, my_form.frm_lng.value), <?php print get_variable('def_zoom');?>);
		var marker = new GMarker(map.getCenter());		// marker to map center
		var myIcon = new GIcon();
		myIcon.image = "./markers/sm_red.png";
		map.removeOverlay(marker);
		
		map.addOverlay(marker, myIcon);
		}				// end function pt_to_map ()

	function loc_lkup(my_form) {		   						// 7/5/10
//		alert (my_form.frm_city.value);
		if ((my_form.frm_city.value.trim()==""  || my_form.frm_state.value.trim()=="")) {
			alert ("City and State are required for location lookup.");
			return false;
			}
		var geocoder = new GClientGeocoder();
		var address = my_form.frm_street.value.trim() + ", " +my_form.frm_city.value.trim() + " "  +my_form.frm_state.value.trim();
		
		if (geocoder) {
			geocoder.getLatLng(
				address,
				function(point) {
					if (!point) {
						alert(address + " not found");
						} 
					else {
						pt_to_map (my_form, point.lat(), point.lng())
						}
					}
				);
			}
		}				// end function addrlkup()

	function getAddress(overlay, latlng, currform) {		//7/5/10
		var rev_coding_on = '<?php print get_variable('reverse_geo');?>';		// 7/5/10	
		if (rev_coding_on == 1) {	
			if (latlng != null) {
				geocoder.getLocations(latlng, function(response) {
				map.clearOverlays();  
					if(response.Status.code != 200) {
						alert("948: Status Code:" + response.Status.code);
					} else { 
						place = response.Placemark[0];    
						point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
// 						locality = response.Placemark[0].AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality;    5/22/11
						marker = new GMarker(point);
						map.addOverlay(marker);

						results = pars_goog_addr(place.address);		// 7/22/10
						
						switch(currform) {
						case "a":
							document.res_add_Form.frm_street.value = results[0];		// 7/22/10
							document.res_add_Form.frm_city.value = results[1] ;
							document.res_add_Form.frm_state.value = results[2];
							document.res_add_Form.frm_street.focus();	
							break;
						case "e":
							document.res_edit_Form.frm_street.value = results[0];		// 7/22/10
							document.res_edit_Form.frm_city.value = results[1] ;
							document.res_edit_Form.frm_state.value = results[2];
							document.res_edit_Form.frm_street.focus();
							break;
						default:
							alert ("441: error");
						}
						}
					});
				}
			}
		}

	function capWords(str){ 											// 7/5/10
		var words = str.split(" "); 
		for (var i=0 ; i < words.length ; i++){ 
			var testwd = words[i]; 
			var firLet = testwd.substr(0,1); 
			var rest = testwd.substr(1, testwd.length -1) 
			words[i] = firLet.toUpperCase() + rest 
	  	 	} 
		return( words.join(" ")); 
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
		var loc = <?php print get_variable('locale');?>;
		document.forms[0].frm_ngs.disabled=false;
		if(loc == 0) {
			document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
			}
		if(loc == 1) {
			document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);
			}
		if(loc == 2) {
			document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);
			}			
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

	function to_top() {
		location.href = '#top';
		}
		
	function to_bottom() {
		location.href = '#bottom';
		}
		
	function add_hash(in_str) { // prepend # if absent
		return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
		}		

	function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity) {		// 8/19/09
	
//		drawCircle(53.479874, -2.246704, 10.0, "#000080", 1, 0.75, "#0000FF", .5);

		var d2r = Math.PI/180;
		var r2d = 180/Math.PI;
		var Clat = radius * 0.014483;
		var Clng = Clat/Math.cos(lat * d2r);
		var Cpoints = [];
		for (var i=0; i < 33; i++) {
			var theta = Math.PI * (i/16);
			Cy = lat + (Clat * Math.sin(theta));
			Cx = lng + (Clng * Math.cos(theta));
			var P = new GPoint(Cx,Cy);
			Cpoints.push(P);
			}
		var polygon = new GPolygon(Cpoints, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity);
		map.addOverlay(polygon);
		}
		
	function drawBanner(point, html, text, font_size, color) {        // Create the banner
	//	alert("<?php echo __LINE__;?> " + color);
		var invisibleIcon = new GIcon(G_DEFAULT_ICON, "./markers/markerTransparent.png");      // Custom icon is identical to the default icon, except invisible

		map.setCenter(point, 8);
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());
		var the_color = (typeof color == 'undefined')? "#000000" : color ;	// default to black

		var style_str = 'background-color:transparent;font-weight:bold;border:0px black solid;white-space:nowrap; font-size:' + font_size + 'px; font-family:arial; opacity: 0.9; color:' + add_hash(the_color) + ';';

		var contents = '<div><div style= "' + style_str + '">'+text+'<\/div><\/div>';
		var label=new ELabel(point, contents, null, new GSize(-8,4), 75, 1);
		map.addOverlay(label);
		
		var marker = new GMarker(point,invisibleIcon);	        // Create an invisible GMarker
	//	map.addOverlay(marker);
		
		}				// end function draw Banner()		

	function do_landb() {				// JS function - 8/1/11
	

		var points = new Array();
<?php
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `line_status` = 0 AND (`use_with_f` = 1 OR `use_with_r` = 1)";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);

		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$empty = FALSE;
			extract ($row);
			$name = $row['line_name'];
			switch ($row['line_type']) {
				case "p":		// poly
					$points = explode (";", $line_data);
					echo "\n\tvar points = new Array();\n";
		
					for ($i = 0; $i<count($points); $i++) {
						$coords = explode (",", $points[$i]);
?>
						var thepoint = new GLatLng(<?php print $coords[0];?>, <?php print $coords[1];?>);
						bounds.extend(thepoint);
						points.push(thepoint);
		
<?php					}			// end for ($i = 0 ... )
			 	if ((intval($filled) == 1) && (count($points) > 2)) {?>
						var polyline = new GPolygon(points,add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>,add_hash("<?php print $fill_color;?>"), <?php print $fill_opacity;?>);
<?php			} else {?>
				        var polyline = new GPolyline(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>,0 ,0);
<?php			} ?>				        
						map.addOverlay(polyline);
<?php				
					break;
			
				case "c":		// circle
					$temp = explode (";", $line_data);
					$radius = $temp[1];
					$coords = explode (",", $temp[0]);
					$lat = $coords[0];
					$lng = $coords[1];
					$fill_opacity = (intval($filled) == 0)?  0 : $fill_opacity;
					
					echo "\n drawCircle({$lat}, {$lng}, {$radius}, add_hash('{$line_color}'), {$line_width}, {$line_opacity}, add_hash('{$fill_color}'), {$fill_opacity}); // 513\n";
					break;
			
				case "t":		// text banner

					$temp = explode (";", $line_data);
					$banner = $temp[1];
					$coords = explode (",", $temp[0]);
					echo "\n var point = new GLatLng(parseFloat({$coords[0]}) , parseFloat({$coords[1]}));\n";
					$the_banner = htmlentities($banner, ENT_QUOTES);
					$the_width = intval( trim($line_width), 10);		// font size
					echo "\n drawBanner( point, '{$the_banner}', '{$the_banner}', {$the_width});\n";
					break;
			
				}	// end switch
				
		}			// end while ()
		
		unset($query, $result);
?>
		}		// end function do_landb()
/*
	try {
		do_landb();				// 7/3/11 - show lines
		}
	catch (e) {	}
*/	

	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}		
</SCRIPT>
<?php
function list_facilities($addon = '', $start) {
//	global {$_SESSION['fip']}, $fmp, {$_SESSION['editfile']}, {$_SESSION['addfile']}, {$_SESSION['unitsfile']}, {$_SESSION['facilitiesfile']}, {$_SESSION['routesfile']}, {$_SESSION['facroutesfile']};
	global $iw_width, $u_types, $tolerance;

//	$assigns = array();
//	$tickets = array();

	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";

	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_assoc($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);
	$calls = array();
	$calls_nr = array();
	$calls_time = array();

	$query = "SELECT * , UNIX_TIMESTAMP(packet_date) AS `packet_date` FROM `$GLOBALS[mysql_prefix]tracks` ORDER BY `packet_date` ASC";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($result)) {
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

	function hideDiv(div_area, hide_cont, show_cont) {	//	3/15/11
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "resp_list_sh") {
			var controlarea = "resp_list";
			}
		if (div_area == "facs_list_sh") {
			var controlarea = "facs_list";
			}
		if (div_area == "incs_list_sh") {
			var controlarea = "incs_list";
			}
		if (div_area == "region_boxes") {
			var controlarea = "region_boxes";
			}			
		var divarea = div_area 
		var hide_cont = hide_cont 
		var show_cont = show_cont 
		if($(divarea)) {
			$(divarea).style.display = 'none';
			$(hide_cont).style.display = 'none';
			$(show_cont).style.display = '';
			} 
		var params = "f_n=" +controlarea+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);			
		} 

	function showDiv(div_area, hide_cont, show_cont) {	//	3/15/11
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "resp_list_sh") {
			var controlarea = "resp_list";
			}
		if (div_area == "facs_list_sh") {
			var controlarea = "facs_list";
			}
		if (div_area == "incs_list_sh") {
			var controlarea = "incs_list";
			}
		if (div_area == "region_boxes") {
			var controlarea = "region_boxes";
			}				
		var divarea = div_area
		var hide_cont = hide_cont 
		var show_cont = show_cont 
		if($(divarea)) {
			$(divarea).style.display = '';
			$(hide_cont).style.display = '';
			$(show_cont).style.display = 'none';
			}
		var params = "f_n=" +controlarea+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);					
		} 	
		
	function gb_handleResult(req) {							// 12/03/10	The persist callback function
		}

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

	function checkArray(form, arrayName)	{	//	5/3/11
		var retval = new Array();
		for(var i=0; i < form.elements.length; i++) {
			var el = form.elements[i];
			if(el.type == "checkbox" && el.name == arrayName && el.checked) {
				retval.push(el.value);
			}
		}
	return retval;
	}		
		
	function checkForm(form)	{	//	6/10/11
		var errmsg="";
		var itemsChecked = checkArray(form, "frm_group[]");
		if(itemsChecked.length > 0) {
			var params = "f_n=viewed_groups&v_n=" +itemsChecked+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
			var url = "persist3.php";	//	3/15/11	
			sendRequest (url, fvg_handleResult, params);				
//			form.submit();
		} else {
			errmsg+= "\tYou cannot Hide all the regions\n";
			if (errmsg!="") {
				alert ("Please correct the following and re-submit:\n\n" + errmsg);
				return false;
			}
		}
	}
	
	function fvg_handleResult(req) {	// 6/10/11	The persist callback function for viewed groups.
		document.region_form.submit();
		}
		
	function form_validate(theForm) {	//	5/3/11
//		alert("Validating");
		checkForm(theForm);
		}				// end function validate(theForm)			

	function sendRequest(url,callback,postData) {								// 2/14/09
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
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
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}

	function createMarker(point, tabs, color, id, fac_id) {						// (point, myinfoTabs,<?php print $row['type'];?>, i)
		points = true;													// at least one
//		var letter = to_str(id);
		var fac_id = fac_id;	
		
		var icon = new GIcon(listIcon);
		var icon_url = "./our_icons/gen_fac_icon.php?blank=" + escape(icons[color]) + "&text=" + fac_id;

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

	function createdummyMarker(point, tabs, color, id, fac_id) {						// 7/28/10
		points = true;													// at least one
//		var letter = to_str(id);
		var fac_id = fac_id;	
		
		var icon = new GIcon(listIcon);
		var icon_url = "./our_icons/question1.png";

		icon.image = icon_url;		// 

		var dummymarker = new GMarker(point, icon);
		dummymarker.id = color;				// for hide/unhide - unused

		GEvent.addListener(dummymarker, "click", function() {		// here for both side bar and icon click
			if (dummymarker) {
				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				dummymarker.openInfoWindowTabsHtml(infoTabs[id]);

				setTimeout(function() {										// wait for rendering complete
					if ($("detailmap")) {
						var dMapDiv = $("detailmap");
						var detailmap = new GMap2(dMapDiv);
						detailmap.addControl(new GSmallMapControl());
						detailmap.setCenter(point, 17);  						// larger # = closer
						detailmap.addOverlay(dummymarker);
						}
					else {
						}
					},4000);				// end setTimeout(...)

				}		// end if (marker)
			});			// end GEvent.add Listener()

		gmarkers[id] = dummymarker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
		if (!(map_is_fixed)) {
			bounds.extend(point);
			}
		return dummymarker;
		}				// end function createdummyMarker()		

	function do_sidebar (sidebar, id, the_class, fac_id) {
		var fac_id = fac_id;
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"'>";
		side_bar_html += "<TD CLASS='" + the_class + "' onClick = myclick(" + id + "); >" + fac_id + sidebar +"</TD></TR>\n";		// 3/15/11
		}

	function do_sidebar_nm (sidebar, line_no, id, fac_id) {	
		var fac_id = fac_id;	
		var letter = to_str(line_no);	
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"'>";
		side_bar_html += "<TD onClick = myclick_nm(" + id + "); >" + fac_id + sidebar +"</TD></TR>\n";		// 1/23/09, 10/29/09 removed period, 11/11/09, 3/15/11
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

	function do_ngs() {											// LL to USNG
		var loc = <?php print get_variable('locale');?>;
		document.forms[0].frm_ngs.disabled=false;
		if(loc == 0) {
			document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
			}
		if(loc == 1) {
			document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);
			}
		if(loc == 2) {
			document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);
			}			
		document.forms[0].frm_ngs.disabled=true;
		}

	function do_sel_update_fac (in_unit, in_val) {							// 3/15/11
		to_server_fac(in_unit, in_val);
		}

	function to_server_fac(the_unit, the_status) {									// 3/15/11
		var querystr = "frm_responder_id=" + the_unit;
		querystr += "&frm_status_id=" + the_status;
	
		var url = "as_up_fac_status.php?" + querystr;			// 3/15/11
		var payload = syncAjax(url);						// 
		if (payload.substring(0,1)=="-") {	
			alert ("<?php print __LINE__;?>: msg failed ");
			return false;
			}
		else {
			parent.frames['upper'].show_msg ('Facility status update applied!')
			return true;
			}				// end if/else (payload.substring(... )
		}		// end function to_server()

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
	side_bar_html += "<TR class='even'>	<TD><B>Icon</B></TD><TD><B>Handle</B></TD><TD ALIGN='left'><B>Name</B></TD><TD ALIGN='left'><B><?php print get_text("Type"); ?></B></TD><TD ALIGN='left'><B><?php print get_text("Status"); ?></B></TD><TD ALIGN='left'><B><?php print get_text("As of"); ?></B></TD></TR>";
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

//	map.addControl(new GSmallMapControl());
	map.setUIToDefault();										// 8/13/10

	map.addControl(new GMapTypeControl());
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>

	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
	mapBounds=new GLatLngBounds(map.getBounds().getSouthWest(), map.getBounds().getNorthEast());

	var bounds = new GLatLngBounds();						// create  bounding box
	map.enableScrollWheelZoom();

	do_landb();				// 8/1/11 - show scribbles		
	
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

//-----------------------BOUNDARIES STUFF--------------------6/10/11

	var thepoint;
	var points = new Array();
	var boundary = new Array();	
	var bound_names = new Array();
	
	GEvent.addListener(map, "click", function(overlay,boundpoint) {
	for (var n = 0; n < boundary.length; n++) {
		if (boundary[n].Contains(boundpoint)) {
			map.openInfoWindowHtml(boundpoint,"This is " + bound_names[n]);
			}
		}
	});	
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	//	6/10/11
	$result = mysql_query($query);	//	6/10/11
	$a_gp_bounds = array();	
	$gp_bounds = array();	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	//	6/10/11
		$al_groups[] = $row['group'];
		$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	//	6/10/11
		$result2 = mysql_query($query2);	// 4/18/11
		while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	//	//	6/10/11	
			if($row2['boundary'] != 0) {
				$a_gp_bounds[] = $row2['boundary'];	
				}
		}
	}

	if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
		foreach(explode(",",$_SESSION['viewed_groups']) as $val_vg) {
			$query3 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$val_vg';";
			$result3 = mysql_query($query3);	//	6/10/11		
			while ($row3 = stripslashes_deep(mysql_fetch_assoc($result3))) 	{
					if($row3['boundary'] != 0) {
						$gp_bounds[] = $row3['boundary'];	
						}
				}
			}
		} else {
			$gp_bounds = $a_gp_bounds;
		}
		
	foreach($gp_bounds as $value) {		//	6/10/11
?>
		var points = new Array();
<?php	
		if($value !=0) {
			$query_bn = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`='$value' AND `use_with_f`=1";
//			print $query_bn;
			$result_bn = mysql_query($query_bn)or do_error($query_bn, mysql_error(), basename(__FILE__), __LINE__);
			while($row_bn = stripslashes_deep(mysql_fetch_assoc($result_bn))) {
				extract ($row_bn);
				$bn_name = $row_bn['line_name'];
				$points = explode (";", $line_data);
				for ($xx = 0; $xx < count($points); $xx++) {
					$coords = explode (",", $points[$xx]);
		?>
					thepoint = new GLatLng(parseFloat(<?php print $coords[0];?>), parseFloat(<?php print $coords[1];?>));
					points.push(thepoint);
<?php
					}			// end for ($i = 0 ... )
			?>

<?php
			switch ($row_bn['line_type']) {
				case "p":		// poly
					if (intval($filled) == 1) {		//	6/10/11
?>
						var polyline = new GPolygon(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>, "<?php print $fill_color;?>", <?php print $fill_opacity;?>, {clickable:false});
						boundary.push(polyline);
						bound_names.push("<?php print $bn_name;?>"); 			
						<?php	
						} else {
?>
						var polyline = new GPolyline(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>,0 , 0, {clickable:false});
						boundary.push(polyline);
						bound_names.push("<?php print $bn_name;?>"); 			
<?php		
						}
?>	
					map.addOverlay(polyline);
<?php
				break;
				
				case "c":
					$temp = explode (";", $line_data);
					$radius = $temp[1];
					$coords = explode (",", $temp[0]);
					$lat = $coords[0];
					$lng = $coords[1];
					$fill_opacity = (intval($filled) == 0)?  0 : $fill_opacity;
					echo "\n drawCircle({$lat}, {$lng}, {$radius}, add_hash('{$line_color}'), {$line_opacity}, {$line_width}, add_hash('{$fill_color}'), {$fill_opacity}); // 513\n";

				break;				
				}
?>
				map.addOverlay(polyline);
<?php
			}	//	End while
		}	//	end if $value !=0
	}	//	end foreach $gp_bounds

//-------------------------END OF BOUNDARIES STUFF-------------------------			
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `id`";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		}
	unset($result_st);

	$type_vals = array();											// build array of $status_vals
	$type_vals[''] = $type_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";
	$result_ty = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row_ty = stripslashes_deep(mysql_fetch_assoc($result_ty))) {
		$temp = $row_ty['id'];
		$type_vals[$temp] = $row_ty['name'];
		}
	unset($result_ty);
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	// 6/10/11
	$result = mysql_query($query);	// 6/10/11
	$al_groups = array();
	while ($row_gp = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 6/10/11
		$al_groups[] = $row_gp['group'];
		}	

	if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}

	if(!isset($curr_viewed)) {	
		$x=0;	//	6/10/11
		$where2 = "WHERE (";	//	6/10/11
		foreach($al_groups as $grp) {	//	6/10/11
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
	} else {
		$x=0;	//	6/10/11
		$where2 = "WHERE (";	//	6/10/11
		foreach($curr_viewed as $grp) {	//	6/10/11
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
	}
	$where2 .= "AND `a`.`type` = 3";	//	6/10/11				
	unset($result);
	//	3/15/11, 6/10/11

	$query = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, 
		`f`.id AS id, 
		`f`.status_id AS status_id,
		`f`.boundary AS boundary,		
		`f`.description AS facility_description,
		`t`.name AS fac_type_name, 
		`f`.name AS name,
		`f`.type AS type,
		`f`.street AS street,
		`f`.city AS city,
		`f`.state AS state 
		FROM `$GLOBALS[mysql_prefix]facilities`  `f`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `f`.`id` = a.resource_id )			
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` ON `f`.type = `t`.id 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` ON `f`.status_id = `s`.id 
		{$where2} GROUP BY `f`.id ORDER BY `f`.type ASC";		

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_facilities = mysql_affected_rows();
	$i=0;				// counter
// =============================================================================
	$utc = gmdate ("U");
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// ==========  major while() for Facility ==========
	$boundary = $row['boundary'];
	
//-----------------------FACILITY BOUNDARIES / CATCHMENT STUFF--------------------6/10/11
?>
	var thepoint;
	var points = new Array();
<?php	
		$query_bn = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`='$boundary' AND `use_with_f`=1";
		$result_bn = mysql_query($query_bn)or do_error($query_bn, mysql_error(), basename(__FILE__), __LINE__);
		while($row_bn = stripslashes_deep(mysql_fetch_assoc($result_bn))) {
			$the_type = $row_bn['line_type'];
			switch ($the_type) {
		
			case "p":
				extract ($row_bn);
				$bn_name = $row_bn['line_name'];
				$points = explode (";", $line_data);
				for ($i = 0; $i < count($points); $i++) {
					$coords = explode (",", $points[$i]);
?>
					thepoint = new GLatLng(parseFloat(<?php print $coords[0];?>), parseFloat(<?php print $coords[1];?>));
					points.push(thepoint);
<?php
					}			// end for ($i = 0 ... )
				if (intval($filled) == 1) {		//	6/10/11
?>
					var polyline = new GPolygon(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>, "<?php print $fill_color;?>", <?php print $fill_opacity;?>, {clickable:false});
					boundary.push(polyline);
					bound_names.push("<?php print $bn_name;?>"); 			
<?php	
					} else {
?>
					var polyline = new GPolyline(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>,0 , 0, {clickable:false});
					boundary.push(polyline);
					bound_names.push("<?php print $bn_name;?>"); 			
<?php		
					}
?>
					map.addOverlay(polyline);
<?php
			break;
		
			case "c":
				extract ($row_bn);
				$temp = explode (";", $line_data);
				$radius = $temp[1];
				$coords = explode (",", $temp[0]);
				$lat = $coords[0];
				$lng = $coords[1];
				$fill_opacity = (intval($filled) == 0)?  0 : $fill_opacity;
				echo "\n drawCircle({$lat}, {$lng}, {$radius}, add_hash('{$line_color}'), {$line_opacity}, {$line_width}, add_hash('{$fill_color}'), {$fill_opacity}); // 513\n";

			break;
		
			}	// end switch
		}	//	End while
//-------------------------END OF FACILITY BOUNDARIES STUFF-------------------------			
	$fac_gps = get_allocates(3, $row['id']);	//	6/10/11
		$grp_names = "Groups Assigned: ";	//	6/10/11
		$y=0;	//	6/10/11
		foreach($fac_gps as $value) {	//	6/10/11
			$counter = (count($fac_gps) > ($y+1)) ? ", " : "";
			$grp_names .= get_groupname($value);
			$grp_names .= $counter;
			$y++;
			}
		$grp_names .= " / ";
		$the_bg_color = 	$GLOBALS['FACY_TYPES_BG'][$row['icon']];		// 2/8/10
		$the_text_color = 	$GLOBALS['FACY_TYPES_TEXT'][$row['icon']];		// 2/8/10	
		$the_on_click = (my_is_float($row['lat']))? " onClick = myclick({$i}); " : " onClick = myclick_nm({$row['unit_id']}); ";	//	3/15/11
		$got_point = FALSE;
		print "\n\t\tvar i=$i;\n";

		if(is_guest()) {
			$toedit = $tomail = $toroute = "";
			}
		else {
			$toedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['facilitiesfile']}?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>" ;
			$tomail = "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = 'do_mail_in_win({$row['id']})'><U><B>Email</B></U></SPAN>" ;
			$toroute = "&nbsp;<A HREF='{$_SESSION['facroutesfile']}?fac_id=" . $row['id'] . "'><U>Route To Facility</U></A>";	
			}		

		$temp = $row['status_id'] ;	
		$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";	

		$temp_type = $row['type'] ;	
		$the_type = (array_key_exists($temp_type, $type_vals))? $type_vals[$temp_type] : "??";

		if (!($got_point) && ((my_is_float($row['lat'])))) {
			if(($row['lat']==0.999999) && ($row['lng']==0.999999)) {
				echo "\t\tvar point = new GLatLng(" . get_variable('def_lat') . ", " . get_variable('def_lng') .");\n";
			} else {
				echo "\t\tvar point = new GLatLng(" . $row['lat'] . ", " . $row['lng'] .");\n";
			}
			$got_point= TRUE;
			}

		$update_error = strtotime('now - 6 hours');							// set the time for silent setting
		$index = $row['icon_str'];
// name

		$display_name = $name = htmlentities($row['name'], ENT_QUOTES);	
		$handle = htmlentities($row['handle'], ENT_QUOTES);					// 7/7/11

		$sidebar_line = "&nbsp;&nbsp;<TD WIDTH='15%' TITLE = '{$row['handle']}' {$the_on_click}><U><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>" . addslashes(shorten($handle, 15)) ."</SPAN></U></TD>";	//	6/10/11
		$sidebar_line .= "<TD WIDTH='40%' TITLE = '" . addslashes($name) . "' {$the_on_click}><U><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'><NOBR>" . addslashes(shorten($name, 24)) ."</NOBR></SPAN></U></TD><TD WIDTH='15%'>{$the_type}</TD>";
		$sidebar_line .= "<TD WIDTH='20%' CLASS='td_data' TITLE = '" . addslashes ($the_status) . "'> " . get_status_sel($row['id'], $row['status_id'], 'f') . "</TD>";	//	3/15/11

// as of
		$strike = $strike_end = "";
		$the_time = $row['updated'];
		$the_class = "";

		$strike = $strike_end = "";

		$sidebar_line .= "<TD WIDTH='20%' CLASS='$the_class'> $strike <NOBR>" . format_sb_date($the_time) . "</NOBR> $strike_end</TD>";
// tab 1

		if (my_is_float($row['lat'])) {										// position data?
			$temptype = $u_types[$row['type']];
			$the_type = $temptype[0];
		
			$tab_1 = "<TABLE CLASS='infowin' width='{$iw_width}'>";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($display_name, 48)) . "</B> - " . $the_type . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(shorten(str_replace($eols, " ", $row['description']), 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . $the_status . " </TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['contact_name']). " Via: " . addslashes($row['contact_email']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date($row['updated']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $toedit . $tomail . "&nbsp;&nbsp;<A HREF='{$_SESSION['facilitiesfile']}?func=responder&view=true&id=" . $row['id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
			$tab_1 .= "</TABLE>";

			$tab_2 = "<TABLE CLASS='infowin' width='{$iw_width}' ALIGN = 'center' >";
			$tab_2 .= "<TR CLASS='odd'><TD ALIGN='right' STYLE= 'width:50%'>Security contact:&nbsp;</TD><TD ALIGN='left' STYLE= 'width:50%'>" . addslashes($row['security_contact']) . " </TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['security_email']) . " </TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row['security_phone']) . " </TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>" . get_text("Access rules") . ":&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row['access_rules'])) . "</TD></TR>";
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

		$name = $row['name'];	// 10/8/09		 4/28/11
		if(($row['lat']==0.999999) && ($row['lng']==0.999999)) {	// check for facilities added in no mpas mode 7/28/10
			
?>
		var fac_id = "<?php print $index;?>";	//	10/8/09
		var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";

		do_sidebar ("<?php print $sidebar_line; ?>", i, the_class, fac_id);
		var dummymarker = createdummyMarker(point, myinfoTabs,<?php print $row['type'];?>, i, fac_id);	// 771 (point,tabs, color, id)
		map.addOverlay(dummymarker);
<?php
		} else {
?>
		var fac_id = "<?php print $index;?>";	//	10/8/09
		var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";

		do_sidebar ("<?php print $sidebar_line; ?>", i, the_class, fac_id);
		var marker = createMarker(point, myinfoTabs,<?php print $row['type'];?>, i, fac_id);	// 771 (point,tabs, color, id)
		map.addOverlay(marker);
<?php
			}	// End of check for facilities added in no maps mode 7/28/10
		} else {		// end position data available

			$name = $row['name'];	// 11/11/09		
			$temp = explode("/", $name );
			$index = substr($temp[count($temp) -1], -6, strlen($temp[count($temp) -1]));		// 3/19/11

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

var buttons_html = "";
<?php

	if(!empty($addon)) {
		print "\n\tbuttons_html +=\"" . $addon . "\"\n";
		}
?>
	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div
	$("buttons").innerHTML = buttons_html;	// append the assembled side_bar_html contents to the side_bar div
	$("num_facilities").innerHTML = <?php print $num_facilities;?>;

<?php
	do_kml();
?>


</SCRIPT>
<?php
	}				// end function list_facilities() ===========================================================

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

	function ReadOnlyCheckBox() {
		alert("You can't change this value");
		return false;
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
	var geocoder = null;												// 	7/5/10
	var rev_coding_on;												//	7/5/10
	geocoder = new GClientGeocoder();										//	7/5/10


	var myZoom;						// note globals
	var marker;

	var myIcon = new GIcon();
<?php
	if(($the_lat==0.999999) && ($the_lng==0.999999)) {	// check of Tickets entered in "no maps" mode 7/28/10	
?>
		myIcon.image = "./our_icons/question1.png";	// 7/28/10
		myIcon.iconSize = new GSize(16, 28);		
		myIcon.iconAnchor = new GPoint(8, 28);
		myIcon.infoWindowAnchor = new GPoint(5, 1);			
<?php	} else { ?>		
		myIcon.image = "./markers/yellow.png";
		myIcon.shadow = "./markers/sm_shadow.png";
		myIcon.iconSize = new GSize(16, 28);
		myIcon.shadowSize = new GSize(16, 28);
		myIcon.iconAnchor = new GPoint(8, 28);
		myIcon.infoWindowAnchor = new GPoint(5, 1);
<?php 	}	// end of check of Tickets entered in "no maps" mode 7/28/10	
?>	
//	map.addControl(new GSmallMapControl());
	map.setUIToDefault();										// 8/13/10

	map.addControl(new GMapTypeControl());
	map.addControl(new GOverviewMapControl());
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>

	map.enableScrollWheelZoom();

	var tab1contents;				// info window contents - first/only tab
									// default point - possible dummy
<?php
	if(($the_lat==0.999999) && ($the_lng==0.999999)) {	// check of Tickets entered in "no maps" mode 7/28/10	
?>									
	map.setCenter(new GLatLng(<?php print get_variable('def_lat'); ?>, <?php print get_variable('def_lng'); ?>), <?php print get_variable('def_zoom');?>);	// larger # => tighter zoom
<?php
	} else {	
?>
	map.setCenter(new GLatLng(<?php print $the_lat; ?>, <?php print $the_lng; ?>), <?php print get_variable('def_zoom');?>);	// larger # => tighter zoom
<?php
}	// end of check of Tickets entered in "no maps" mode 7/28/10

	if ($icon)	{							// icon display?
		if(($the_lat==0.999999) && ($the_lng==0.999999)) {	// check of Tickets entered in "no maps" mode 7/28/10	
?>
		var point = new GLatLng(<?php print get_variable('def_lat') . ", " . get_variable('def_lng'); ?>);
		var marker = new GMarker(point, {icon: myIcon, draggable:false});
		map.addOverlay(new GMarker(point, myIcon));
<?php } else { ?>
		var point = new GLatLng(<?php print $the_lat . ", " . $the_lng; ?>); // 888
		var marker = new GMarker(point, {icon: myIcon, draggable:false});
		map.addOverlay(new GMarker(point, myIcon));
<?php
		}	// end of check of Tickets entered in "no maps" mode 7/28/10		
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
					map.setCenter(marker.getPoint(), <?php echo get_variable('def_zoom'); ?>);
					do_lat (marker.getPoint().lat());		// set form values
					do_lng (marker.getPoint().lng());
					do_ngs();

					});
				map.addOverlay(marker);
				}		// end if (latlng)
			switch(mode) {		// 7/5/10 added for reverse geocoding of map click
				case "a":
					currform="a";				
					getAddress(overlay, latlng, currform);				// 7/5/10
					break;
				case "e":
					currform="e";				
					getAddress(overlay, latlng, currform);				// 7/5/10
					break;
				default:
					alert("Invalid Function");
				}			

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
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
		print "</FORM>\n<A NAME='bottom' />\n</BODY></HTML>";
		}

	function do_calls($id = 0) {				// generates js callsigns array
		$print = "\n<SCRIPT >\n";
		$print .="\t\tvar calls = new Array();\n";
		$query	= "SELECT `id`, `callsign` FROM `$GLOBALS[mysql_prefix]facilities` where `id` != $id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
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
			
			$curr_groups = $_POST['frm_exist_groups']; 	//	4/14/11
			$groups = isset($_POST['frm_group']) ? ", " . implode(',', $_POST['frm_group']) . "," : $_POST['frm_exist_groups'];	//	3/28/12 - fixes error when accessed from view ticket screen..	
			$fac_id = $_POST['frm_id'];
			$fac_stat = $_POST['frm_status_id'];
			$by = $_SESSION['user_id'];			
			
			$query = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET
				`name`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) . ",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) . ",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`handle`= " . 		quote_smart(trim($_POST['frm_handle'])) . ",
				`icon_str`= " . 	quote_smart(trim($_POST['frm_icon_str'])) . ",
				`boundary`= " . 	quote_smart(trim($_POST['frm_boundary'])) . ",				
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",
				`capab`= " . 		quote_smart(trim($_POST['frm_capab'])) . ",
				`status_id`= " .	quote_smart(trim($_POST['frm_status_id'])) . ",
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
				`pager_p`= " . 		quote_smart(trim($_POST['frm_pager_p'])) . ",
				`pager_s`= " . 		quote_smart(trim($_POST['frm_pager_s'])) . ",
				`type`= " . 		quote_smart(trim($_POST['frm_type'])) . ",
				`user_id`= " . 		quote_smart(trim($_SESSION['user_id'])) . ",
				`updated`= " . 		quote_smart(trim($now)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_FACILITY_CHANGE'], 0, $_POST['frm_id'], $_POST['frm_status_id']);}	//2/17/11
			$list = $_POST['frm_exist_groups']; 	//	4/14/11
			$ex_grps = explode(',', $list); 	//	4/14/11 
			
			if($curr_groups != $groups) { 	//	4/14/11
				foreach($_POST['frm_group'] as $posted_grp) { 	//	4/14/11
					if(!in_array($posted_grp, $ex_grps)) {
						$query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
								($posted_grp, 3, '$now', $fac_stat, $fac_id, 'Allocated to Group' , $by)";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				foreach($ex_grps as $existing_grps) { 	//	4/14/11
					if(!in_array($existing_grps, $_POST['frm_group'])) {
						$query  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type` = 3 AND `group` = $existing_grps AND `resource_id` = {$fac_id}";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				}				
			
			
			$caption = "<i>" . stripslashes_deep($_POST['frm_name']) . "</i><B>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$by = $_SESSION['user_id'];		//	4/14/11
		$frm_lat = (empty($_POST['frm_lat']))? 'NULL': quote_smart(trim($_POST['frm_lat']));		// 7/22/10
		$frm_lng = (empty($_POST['frm_lng']))? 'NULL': quote_smart(trim($_POST['frm_lng']));		// 7/15/10
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]facilities` (
			`name`, `street`, `city`, `state`, `handle`, `icon_str`, `boundary`, `description`, `capab`, `status_id`, `contact_name`, `contact_email`, `contact_phone`, `security_contact`, `security_email`, `security_phone`, `opening_hours`, `access_rules`, `security_reqs`, `pager_p`, `pager_s`, `lat`, `lng`, `type`, `user_id`, `updated` )
			VALUES (" .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_street'])) . "," .
				quote_smart(trim($_POST['frm_city'])) . "," .
				quote_smart(trim($_POST['frm_state'])) . "," .
				quote_smart(trim($_POST['frm_handle'])) . "," .
				quote_smart(trim($_POST['frm_icon_str'])) . "," .
				quote_smart(trim($_POST['frm_boundary'])) . "," .				
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
				quote_smart(trim($_SESSION['user_id'])) . "," .
				quote_smart(trim($now)) . ");";

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();

		$status_id = $_POST['frm_status_id'];	//4/14/11
		foreach ($_POST['frm_group'] as $grp_val) {	// 6/10/11
		if(test_allocates($new_id, $grp_val, 3))	{		
			$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
					($grp_val, 3, '$now', $status_id, $new_id, 'Allocated to Group' , $by)";
			$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
			}
		}
		
		do_log($GLOBALS['LOG_FACILITY_ADD'], 0, mysql_insert_id(), $_POST['frm_status_id']);	//	2/17/11

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
		<BODY onLoad = "ck_frames();" onUnload="GUnload()">
		<A NAME='top'>		<!-- 11/11/09 -->
<?php
		require_once('./incs/links.inc.php');
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
?>
		<TABLE BORDER=0 ID='outer' WIDTH='80%'><TR><TD WIDTH='50%'>
		<TABLE BORDER="0" ID='addform' WIDTH='98%'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'><?php print get_text("Add Facility"); ?></FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>		
		<FORM NAME= "res_add_Form" METHOD="POST" ACTION="<?php print basename(__FILE__);?>?func=responder&goadd=true">
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker"><?php print get_text("Name"); ?></A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility"><?php print get_text("Handle"); ?></A>:&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="48" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="" />
				<SPAN STYLE = "margin-left:40px;" CLASS="td_label" TITLE="A 3-letter value to be used in the map icon">Icon:</SPAN>&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;
					<INPUT TYPE="text" SIZE = 3 MAXLENGTH=3 NAME="frm_icon_str" VALUE="" />			
			</TD></TR>
<?php
	if(get_num_groups() > 1) {
		if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>		
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
			<?php
			
			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
			print get_user_group_butts(($_SESSION['user_id']));	//	6/10/11		
			
			} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11
?>		
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php

			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
			print get_user_group_butts(($_SESSION['user_id']));	//	6/10/11		
?>	
			</TD></TR>
<?php
			} elseif(COUNT(get_allocates(4, $_SESSION['user_id'])) > 1) {	//	6/10/11
?>
			<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD
<?php
			$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
			print get_user_group_butts_readonly($_SESSION['user_id'])		
?>	
			</TD></TR>
<?php
			} else {
?>
			<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 6/10/11 -->
<?php
			}
		} else {
?>
		<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 6/10/11 -->
<?php
		}
		if(is_administrator()) {	//	6/10/11
?>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Sets Facility Boundary"><?php print get_text("Boundary");?></A>:</TD>
			<TD><SELECT NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_f` = 1 ORDER BY `line_name` ASC";
				$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
					print "\t<OPTION VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT></TD></TR>
<?php
			}		
?>
		<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>			
		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Type - Select from pulldown menu"><?php print get_text("Type"); ?></A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN='left'><SELECT NAME='frm_type'><OPTION VALUE=0>Select one</OPTION>
<?php
	foreach ($u_types as $key => $value) {
		$temp = $value; 												// 2-element array
		print "\t\t\t\t<OPTION VALUE='" . $key . "'>" .$temp[0] . "</OPTION>\n";
		}
?>
			</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<A CLASS="td_label" HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked /></TD>
			</TR>

		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Status - Select from pulldown menu"><?php print get_text("Status"); ?></A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN ='left'><SELECT NAME="frm_status_id" onChange = "document.res_add_Form.frm_log_it.value='1'">
				<OPTION VALUE=0 SELECTED>Select one</OPTION>
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
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
		<TR CLASS='even'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location - type in location in fields or click location on map "><?php print get_text("Location"); ?></A>:</TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='odd'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.res_add_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD> <!-- 7/5/10 -->
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)"> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="<?php print $st_size;?>"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3 ><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution"><?php print get_text("Capability"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility main contact name"><?php print get_text("Contact name"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact email - main contact email address"><?php print get_text("Contact email"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact phone number - main contact phone number"><?php print get_text("Contact phone"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact"><?php print get_text("Security contact"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact email"><?php print get_text("Security email"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact phone number"><?php print get_text("Security phone"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc."><?php print get_text("Opening hours"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_opening_hours" COLS=40 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc"><?php print get_text("Access rules"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_access_rules" COLS=40 ROWS=5></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility securtiy requirements - e.g. phone security first, visitors must be security cleared etc."><?php print get_text("Security reqs"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><TEXTAREA NAME="frm_security_reqs" COLS=40 ROWS=5></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact primary pager number"><?php print get_text("Primary pager"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact secondary pager number"><?php print get_text("Secondary pager"); ?></A>:&nbsp;</TD><TD COLSPAN=3 ><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Latitude and Longitude - set from map click">
			<SPAN onClick = 'javascript: do_coords(document.res_add_Form.frm_lat.value ,document.res_add_Form.frm_lng.value)'>
				<?php print get_text("Lat/Lng"); ?></A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle'
					onClick = 'do_unlock_pos(document.res_add_Form);'><TD COLSPAN=3>
			<INPUT TYPE="text" NAME="show_lat" SIZE=11 VALUE="" disabled />
			<INPUT TYPE="text" NAME="show_lng" SIZE=11 VALUE="" disabled />&nbsp;&nbsp;
<?php
	$locale = get_variable('locale');
	switch($locale) { 
		case "0":
?>
		<SPAN ID = 'usng_link' onClick = "do_usng_conv(res_add_Form)" style='font-weight: bold;'>USNG:</SPAN><INPUT TYPE="text" SIZE=19 NAME="frm_ngs" VALUE="" disabled /></TD></TR>
<?php
		break;

		case "1":
?>
		<SPAN ID = 'osgb_link' style='font-weight: bold;'>OSGB:</SPAN><INPUT TYPE="text" SIZE=19 NAME="frm_ngs" VALUE="" disabled /></TD></TR>
<?php
		break;
	
		default:
?>
		<SPAN ID = 'utm_link' style='font-weight: bold;'>UTM:</SPAN><INPUT TYPE="text" SIZE=19 NAME="frm_utm" VALUE="" disabled /></TD></TR>
<?php

	}
?>

		<TR CLASS='even'><TD COLSPAN=4 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD></TR>
		<TR CLASS = "odd"><TD COLSPAN='2' ALIGN='center'>
			<INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();" STYLE = 'margin-left: 50px' >
			<INPUT TYPE="reset" VALUE="<?php print get_text("Reset"); ?>" onClick = "do_add_reset(this.form);" STYLE = 'margin-left: 20px' />
			<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>"  onClick="validate(document.res_add_Form);"  STYLE = 'margin-left: 20px' /></TD></TR>
		<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_direcs' VALUE=1 />  <!-- note default -->
		</FORM></TABLE> <!-- end inner left -->
		</TD><TD ALIGN='center' WIDTH='50%'>
		<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
		<BR /><BR /><B>Drag/Click to unit location</B>
		<BR /><A HREF='#' onClick='doGrid()'><u>Grid</U></A>

		<BR /><BR />
		<SPAN CLASS="legend" STYLE="text-align: center; vertical-align: middle;">Facility Legend:</SPAN><BR /><BR /><DIV CLASS="legend" ALIGN='center' VALIGN='middle' style='padding: 20px; text-align: center; vertical-align: middle; width: <?php print get_variable('map_width');?>px;'>  <!-- 3/15/11 -->

<?php
		print get_icon_legend ();
?>

		</TD></TR></TABLE><!-- end outer -->

<?php
		map("a",get_variable('def_lat') , get_variable('def_lng'), FALSE) ;				// call GMap js ADD mode, no icon
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- 1100 -->
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>		
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
		$row	= mysql_fetch_assoc($result);
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
		<BODY onLoad = "ck_frames(); " onUnload="GUnload()">
		<A NAME='top'>		<!-- 11/11/09 -->
<?php
		require_once('./incs/links.inc.php');
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
?>
		<TABLE BORDER=0 ID='outer' WIDTH='80%'><TR><TD WIDTH='50%'>
		<TABLE BORDER=0 ID='editform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit Facility '<?php print $row['name'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>
		<FORM METHOD="POST" NAME= "res_edit_Form" ACTION="<?php print  basename(__FILE__);?>?func=responder&goedit=true">

		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker">Name</A>:&nbsp;<font color='red' size='-1'>*</font></TD>			<TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility">Handle</A>:&nbsp;<font color='red' size='-1'>*</font></TD>			
			<TD COLSPAN=3><INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="<?php print $row['handle'] ;?>" />
				<SPAN STYLE = "margin-left:40px;" CLASS="td_label"  TITLE="A 3-letter value to be used in the map icon">Icon:</SPAN>&nbsp;<font color='red' size='-1'>*</font>
				<INPUT TYPE="text" SIZE = 3 MAXLENGTH=3 NAME="frm_icon_str" VALUE="<?php print $row['icon_str'];?>" />			
			</TD></TR>
<?php
		if(get_num_groups() > 1) {
			if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>			
			<TR CLASS='even' VALIGN='top'>;
			<TD CLASS='td_label'><?php print get_text('Region');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php			
			$alloc_groups = implode(',', get_allocates(3, $id));	//	6/10/11
			print get_sub_group_butts(($_SESSION['user_id']), 3, $id) ;	//	6/10/11	
			print "</TD></TR>";		// 6/10/11
			
			} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11	
?>
			<TR CLASS='even' VALIGN='top'>;
			<TD CLASS='td_label'><?php print get_text('Region');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>
<?php
			$alloc_groups = implode(',', get_allocates(3, $id));	//	6/10/11
			print get_sub_group_butts(($_SESSION['user_id']), 3, $id) ;	//	6/10/11	
			print "</TD></TR>";		// 6/10/11		

			} else {
?>
			<TR CLASS='even' VALIGN='top'>;
			<TD CLASS='td_label'><?php print get_text('Regions');?></A>:
			<SPAN id='expand_gps' onClick="$('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
			<SPAN id='collapse_gps' onClick="$('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
			<TD>

<?php
			$alloc_groups = implode(',', get_allocates(3, $id));	//	6/10/11	
			print get_sub_group_butts_readonly(($_SESSION['user_id']), 3, $id) ;	//	4/
			print "</TD></TR>";		// 6/10/11				
			}
		} else {
?>
		<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 6/10/11 -->
<?php
		}
		
		if(is_administrator()) {
?>
			<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#"  TITLE="Sets Facility Boundary"><?php print get_text("Boundary");?></A>:</TD>
			<TD><SELECT NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">	<!--  11/17/10 -->
				<OPTION VALUE=0>Select</OPTION>
<?php
				$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_f` = 1 ORDER BY `line_name` ASC";		// 12/18/10
				$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
					$sel = ($row['boundary'] == $row_bound['id']) ? "SELECTED" : "";
					print "\t<OPTION VALUE='{$row_bound['id']}' {$sel}>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT></TD></TR>
<?php
		}
?>					
		<TR class='spacer'><TD class='spacer' COLSPAN='2'>&nbsp;</TD></TR>

		<TR CLASS = "even" VALIGN='middle'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Type - Select from pulldown menu">Type</A>:&nbsp;<font color='red' size='-1'>*</font></TD>
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
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Status - Select from pulldown menu">Status</A>:&nbsp;</TD>
			<TD ALIGN='left'><SELECT NAME="frm_status_id" onChange = "document.res_edit_Form.frm_log_it.value='1'">
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `status_val` ASC, `group` ASC, `sort` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	$the_grp = strval(rand());			//  force initial optgroup value
	$i = 0;
	while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
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
		<TR CLASS='even'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location - type in location in fields or click location on map ">Location</A>:</TD><TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS='odd'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.res_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD> <!-- 7/5/10 -->
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)"> <!-- 7/5/10 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3><TEXTAREA NAME="frm_descr" COLS=40 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution"><?php print get_text("Capability"); ?></A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_capab" COLS=40 ROWS=2><?php print $row['capab'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility main contact name">Contact name</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="<?php print $row['contact_name'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact email - main contact email address"><?php print get_text("Contact email"); ?></A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="<?php print $row['contact_email'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact phone number - main contact phone number">Contact phone</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="<?php print $row['contact_phone'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact">Security contact</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="<?php print $row['security_contact'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact email">Security email</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="<?php print $row['security_email'] ;?>" /></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility security contact phone number">Security phone</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="<?php print $row['security_phone'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc.">Opening hours</A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_opening_hours" COLS=40 ROWS=2><?php print $row['opening_hours'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc"><?php print get_text("Access rules"); ?></A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_access_rules" COLS=40 ROWS=5><?php print $row['access_rules'];?></TEXTAREA></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility securtiy requirements - e.g. phone security first, visitors must be security cleared etc.">Security reqs</A>:&nbsp;</TD><TD COLSPAN=3><TEXTAREA NAME="frm_security_reqs" COLS=40 ROWS=5><?php print $row['security_reqs'];?></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact primary pager number">Pager Primary</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="<?php print $row['pager_p'] ;?>" /></TD></TR>
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Facility contact secondary pager number">Pager Secondary</A>:&nbsp;</TD><TD COLSPAN=3><INPUT SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="<?php print $row['pager_s'] ;?>" /></TD></TR>

<?php
		$map_capt = (!$is_mobile)? 	"<BR /><BR /><CENTER><B><FONT CLASS = 'normal_text'>Click Map to revise facility location</FONT></B>" : "";
		$lock_butt = (!$is_mobile)? "<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_edit_Form);'>" : "" ;
		$usng_link = (!$is_mobile)? "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_edit_Form)'>{$usng}:</SPAN>": "{$usng}:";
		$osgb_link = (!$is_mobile)? "<SPAN ID = 'osgb_link'>{$osgb}:</SPAN>": "{$osgb}:";		
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

	$usng_val = LLtoUSNG($row['lat'], $row['lng']);
	$osgb_val = LLtoOSGB($row['lat'], $row['lng']) ;
	$utm_val = toUTM("{$row['lat']}, {$row['lng']}");

	$locale = get_variable('locale');
	switch($locale) { 
		case "0":
		?>&nbsp;USNG:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $usng_val;?>' SIZE=19 disabled /></TD></TR>	<!-- 9/13/08, 2/10/11 -->
<?php 	break;

		case "1":
?> 
		&nbsp;OSGB:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $osgb_val;?>' SIZE=19 disabled /></TD></TR>	<!-- 9/13/08, 2/10/11 -->
<?php 
		break;

		default:
?> 
		&nbsp;UTM:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $utm_val;?>' SIZE=19 disabled /></TD></TR>	<!-- 9/13/08, 2/10/11 -->
<?php 		
		}
?>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="even" VALIGN='baseline'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Delete Facility from system">Remove Facility</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
		</TD></TR>
		<TR CLASS = "odd">
			<TD ALIGN='center'><BR>
			<TD ALIGN='center'><BR><INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <!-- 11/27/09 -->
				<INPUT TYPE="reset" VALUE="<?php print get_text("Reset"); ?>" onClick="map_reset()";>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>" onClick="validate(document.res_edit_Form);"></TD></TR>
				</TD></TR>

		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
		<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
		<INPUT TYPE="hidden" NAME="frm_exist_groups" VALUE="<?php print (isset($alloc_groups)) ? $alloc_groups : 1;?>">			
		</FORM></TABLE>
		</TD><TD ALIGN='center' WIDTH='50%'><DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset'></DIV>
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
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>		
		</BODY>
		</HTML>
<?php
		exit();
		}		// end if ($_GET['edit'])
// view =================================================================================================================
// view =================================================================================================================
// view =================================================================================================================

		if ($_getview == 'true') {
			$query_fa = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 3 AND `resource_id` = '$_GET[id]' ORDER BY `id` ASC;";	// 6/10/11
			$result_fa = mysql_query($query_fa);	// 6/10/11
			$fa_groups = array();
			$fa_names = "";	
			while ($row_fa = stripslashes_deep(mysql_fetch_assoc($result_fa))) 	{	// 6/10/11
				$fa_groups[] = $row_fa['group'];
				$query_fa2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row_fa[group]';";	// 6/10/11
				$result_fa2 = mysql_query($query_fa2);	// 6/10/11
				while ($row_fa2 = stripslashes_deep(mysql_fetch_assoc($result_fa2))) 	{	// 6/10/11		
					$fa_names .= $row_fa2['group_name'] . " ";
					}
				}
				
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
		print "\t<BODY onLoad = 'ck_frames()' onUnload='GUnload()'>\n";
		print "<A NAME='top'>\n";			// 11/11/09
		require_once('./incs/links.inc.php');
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
		$temp = $u_types[$row['type']];
		$the_type = $temp[0];			// name of type

?>
			<FONT CLASS="header">&nbsp;'<?php print $row['name'] ;?>' Data</FONT> (#<?php print$row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER=0 ID='view_unit' STYLE='display: block'>
			<FORM METHOD="POST" NAME= "res_view_Form" ACTION="<?php print basename(__FILE__);?>?func=responder">
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Name"); ?>: </TD>			<TD><?php print $row['name'];?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label"><?php print get_text("Location"); ?>: </TD><TD><?php print $row['street'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = 'even'><TD CLASS="td_label"><?php print get_text("City"); ?>: &nbsp;&nbsp;&nbsp;&nbsp;</TD><TD><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Handle"); ?>: </TD>
				<TD><?php print $row['handle'];?>
				<SPAN STYLE = "margin-left:40px;" CLASS="td_label">Icon:</SPAN>&nbsp;<?php print $row['icon_str'];?>
				</TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label">Regions: </TD>			<TD><?php print $fa_names;?></TD></TR><!-- 6/10/11 -->					
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Type"); ?>: </TD>
				<TD><?php print $the_type;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Status"); ?>:</TD>		<TD><?php print $un_st_val;?>
			</TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Description"); ?>: </TD>	<TD><?php print $row['description'];?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Capability"); ?>: </TD>	<TD><?php print $row['capab'];?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Contact name"); ?>:</TD>	<TD><?php print $row['contact_name'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Contact email"); ?>:</TD>	<TD><?php print $row['contact_email'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Contact phone"); ?>:</TD>	<TD><?php print $row['contact_phone'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Security contact"); ?>:</TD>	<TD><?php print $row['security_contact'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Security email"); ?>:</TD>	<TD><?php print $row['security_email'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Security phone"); ?>:</TD>	<TD><?php print $row['security_phone'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Opening hours"); ?>:</TD>	<TD><?php print $row['opening_hours'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Access rules"); ?>:</TD>	<TD><?php print $row['access_rules'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Security reqs"); ?>:</TD>	<TD><?php print $row['security_reqs'] ;?></TD></TR>
			<TR CLASS = "odd"><TD CLASS="td_label"><?php print get_text("Primary pager"); ?>:</TD>	<TD><?php print $row['pager_p'] ;?></TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Secondary pager"); ?>:</TD>	<TD><?php print $row['pager_s'] ;?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label">As of:</TD>	<TD><?php print format_date($row['updated']); ?></TD></TR>
<?php
		if (my_is_float($lat)) {
?>		
			<TR CLASS = "even"><TD CLASS="td_label"  onClick = 'javascript: do_coords(<?php print "$lat,$lng";?>)'><U>Lat/Lng</U>:</TD><TD>
				<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
				<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;

<?php

	$usng_val = LLtoUSNG($row['lat'], $row['lng']);
	$osgb_val = LLtoOSGB($row['lat'], $row['lng']) ;
	$utm_val = toUTM("{$row['lat']}, {$row['lng']}");

	$locale = get_variable('locale');
		switch($locale) { 
			case "0":?>
			&nbsp;USNG:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $usng_val;?>}' SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php 		break;

			case "1":
?>
			&nbsp;OSGB:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $osgb_val;?>}' SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php
			break;
			default:
?>
			&nbsp;UTM:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $utm_val;?>' SIZE=19 disabled /></TD></TR>	<!-- 9/13/08 -->
<?php
			}		// end switch()

			}		// end if (my_is_float($lat))

		$toedit = (is_administrator() || is_super())? "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;": "" ;
?>
			<TR><TD>&nbsp;</TD></TR>
<?php
		if (is_administrator() || is_super()) {
?>
			<TR CLASS = "even"><TD COLSPAN=2 ALIGN='center'>
			<INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "<?php print basename(__FILE__);?>?func=responder&edit=true&id=<?php print $id; ?>"></FORM>
			<INPUT TYPE="hidden" NAME="fac_id" 	VALUE="">						<!-- 10/16/08 -->
			<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
			</FORM>
							<!-- END UNIT VIEW -->
<?php
				if(!(my_is_float($lat))) {	
					map("v", get_variable('def_lat'),  get_variable('def_lng'), FALSE) ;	// default center, no icon
					}
				else {
					if(($lat==0.999999) && ($lng==0.999999)) {	// checks for facilities input in no maps mode 7/28/10
						map("v", get_variable('def_lat'),  get_variable('def_lng'), FALSE) ;	// default center, no icon
					} else {
						map("v", $lat, $lng, TRUE) ;						// do icon
					}											
					}

?>
			<!-- 1408 -->
			<A NAME="bottom" /> 
			<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
			</BODY>
			</HTML>
<?php
			exit();
			}		// end if ($_GET['view'])
// ============================================= initial display =======================
		if (!isset($mapmode)) {$mapmode="a";}
		print "<SPAN STYLE = 'margin-left:100px;'>{$caption}</SPAN>";
?>
		</HEAD><!-- 1387 -->
		<BODY onLoad = "ck_frames(); set_regions_control();" onUnload="GUnload()">
		<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->
		<SCRIPT TYPE="text/javascript" src="./js/ELabel.js"></SCRIPT><!-- 8/1/11 -->		
		<A NAME='top'>		<!-- 11/11/09 -->
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;z-index: 1;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></DIV>
<?php
		require_once('./incs/links.inc.php');
		$required = 250 + (mysql_affected_rows()*40);
		$facs_side_bar_height = .9;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)		
		$the_height = (integer)  min (round($facs_side_bar_height * $_SESSION['scr_height']), $required );		// set the max	
		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
		$regions_inuse = get_regions_inuse($user_level);	//	6/10/11
		$group = get_regions_inuse_numbers($user_level);	//	6/10/11		
		
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 6/10/11
		$result = mysql_query($query);	// 6/10/11
		$al_groups = array();
		$al_names = "";	
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 6/10/11
			$al_groups[] = $row['group'];
			if(!(is_super())) {
				$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 6/10/11
				$result2 = mysql_query($query2);	// 6/10/11
				while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	// 6/10/11		
					$al_names .= $row2['group_name'] . ", ";
					}
				} else {
					$al_names = "ALL. Superadmin Level";
				}
			}
			
		if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
			$curr_viewed= explode(",",$_SESSION['viewed_groups']);
			} else {
			$curr_viewed = $al_groups;
			}

		$curr_names="";	//	6/10/11
		$z=0;	//	6/10/11
		foreach($curr_viewed as $grp_id) {	//	6/10/11
			$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
			$curr_names .= get_groupname($grp_id);
			$curr_names .= $counter;
			$z++;
			}	
			
		$heading = "Facilities - " . get_variable('map_caption');	//	6/10/11
		if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	//	6/10/11		
			$regs_string = "<FONT SIZE='-1'>Allocated Regions:&nbsp;&nbsp;" . $al_names . "&nbsp;&nbsp;|&nbsp;&nbsp;Currently Viewing Regions:&nbsp;&nbsp;" . $curr_names . "</FONT>";	//	6/10/11		
		} else {
			$regs_string = "";
		}
		
?>
			<DIV id='top_reg_box' style='display: none;'>
				<DIV id='region_boxes' class='header_reverse' style='align: center; width: 100%; text-align: center; margin-left: auto; margin-right: auto; height: 30px; z-index: 1;'></DIV>
			</DIV>
			<DIV style='z-index: 1;'>		
				<TABLE ID='outer' WIDTH='100%'>
					<TR CLASS='spacer'>
						<TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;
						</TD>
					</TR>
					<TR CLASS='header'>
						<TD COLSPAN='99' ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'><?php print $heading; ?> </FONT>
						</TD>
					</TR>	<!-- 6/10/11 -->
					<TR CLASS='spacer'>
						<TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;
						</TD>
					</TR>				<!-- 6/10/11 -->
					<TR>
						<TD WIDTH = '50%'>
							<TABLE ID = 'sidebar' BORDER = 0 WIDTH='98%'>
								<TR class='even'>
									<TD ALIGN='center'><B>Facilities (<DIV id="num_facilities" style="display: inline;"></DIV>)</B>
									</TD>
								</TR>
								<TR class='odd'>	
									<TD ALIGN='center'>Click line or icon for details
									</TD>
								</TR>			
								<TR>
									<TD>
										<DIV ID='side_bar' style="max-height: <?php print $the_height; ?>px;  overflow-y: scroll; overflow-x: hidden;"></DIV>
									</TD>
								</TR>
								<TR class='spacer'>
									<TD class='spacer'>&nbsp;
									</TD>
								</TR>
								<TR>
									<TD ALIGN='center'>
										<DIV style='width: 100%;'><?php print get_facilities_legend();?></DIV>
									</TD>
								</TR>
								<TR class='spacer'>
									<TD class='spacer'>&nbsp;
									</TD>
								</TR>
								<TR>
									<TD ALIGN='center' COLSPAN=99>
										<DIV ID='buttons' style="width: 100%; align: center;"></DIV>
									</TD>
								</TR>
							</TABLE>
						</TD>
						<TD WIDTH = '50%'>	
							<TABLE ID = 'MAP' BORDER=0>
								<TR class='even'>
									<TD ALIGN='center'>	<!-- 3/15/11 -->
										<DIV ID='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
									</TD>
								</TR>	<!-- 3/15/11 -->
								<TR class='even'>
									<TD ALIGN='center' class='td_label'>  <!-- 3/15/11 -->
										<SPAN onClick='doGrid()'><u>Grid</U></SPAN>  <!-- 3/15/11 -->
										<SPAN onClick='doTraffic()'STYLE = 'margin-left:80px;'><U>Traffic</U></SPAN>
									</TD>
								</TR>		<!-- 4/10/09, 3/15/11 -->
								<TR>
									<TD>&nbsp;</TD>
								</TR>
								<TR class = 'odd'>
									<TD ALIGN='center' class='td_label'>  <!-- 3/15/11 -->
										<SPAN CLASS="legend" STYLE="font-size: 14px; text-align: center; vertical-align: middle; width: <?php print get_variable('map_width');?>-25px;"><B>Facility Legend:</B></SPAN>
									</TD>
								</TR>  <!-- 3/15/11 -->
								<TR class = 'even'>
									<TD ALIGN='center'>
										<DIV CLASS="legend" ALIGN='center' VALIGN='middle' style='padding: 20px; text-align: center; vertical-align: middle; width: <?php print get_variable('map_width');?>-25px;'>  <!-- 3/15/11 -->
	<?php
											print get_icon_legend ();
											$from_right = 20;	//	5/3/11
											$from_top = 10;		//	5/3/11	
	?>
										</DIV>
									</TD>
								</TR>
							</TABLE>
						</TD>
					</TR>
				</TABLE>
			</DIV>	<!-- end of outer -->
<?php
		if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	//	6/10/11
			$regs_col_butt = ((isset($_SESSION['regions_boxes'])) && ($_SESSION['regions_boxes'] == "s")) ? "" : "none";	//	6/10/11
			$regs_exp_butt = ((isset($_SESSION['regions_boxes'])) && ($_SESSION['regions_boxes'] == "h")) ? "" : "none";	//	6/10/11	
?>
			<DIV id = 'regions_outer' style = "position: fixed; right: 20%; top: 10%; z-index: 1000;">
				<DIV id="boxB" class="box" style="z-index:1000;">
					<div class="bar_header" class="heading_2" STYLE="z-index: 1000; height: 30px;">Viewed Regions
					<DIV id="collapse_regs" class='plain' style =" display: inline-block; z-index:1001; cursor: pointer; float: right;" onclick="$('top_reg_box').style.display = 'block'; $('regions_outer').style.display = 'none';">Dock</DIV><BR /><BR />
					<DIV class="bar" STYLE="color:red; z-index: 1000; position: relative; top: 2px;"
						onmousedown="dragStart(event, 'boxB')"><i>Drag me</i></DIV>
					<DIV id="region_boxes2" class="content" style="z-index: 1000;"></DIV>
					</DIV>
				</DIV>
			</DIV>
<?php			
		}
			print get_buttons_inner();	//	3/28/12
			print get_buttons_inner2();	//	3/28/12				
?>			
			<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='view' VALUE='true'>
			<INPUT TYPE='hidden' NAME='id' VALUE=''>
			</FORM>

			<FORM NAME='add_Form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
			<INPUT TYPE='hidden' NAME='add' VALUE='true'>
			</FORM>

			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print  basename(__FILE__);?>?func=responder"></FORM>
			<!-- 1452 -->
			<A NAME="bottom" /> 
			<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
			</BODY>				<!-- END RESPONDER LIST and ADD -->
<?php
//		print do_calls();		// generate JS calls array

		$buttons = "<TR><TD COLSPAN=99 ALIGN='center'>";
		if ((!(is_guest())) && (!(is_unit()))) {		// 7/27/10
			$buttons .="<INPUT TYPE='button' value= 'Add a Facility'  onClick ='document.add_Form.submit();'  STYLE = 'margin-left: 60px;'>";
			}
		if (may_email()) {
			$buttons .= "<INPUT TYPE = 'button' onClick = 'do_mail_win()' VALUE='Email facilities'  style = 'margin-left:20px'>";	// 6/13/09
			}
		$buttons .= "</TD></TR>";

		print list_facilities($buttons, 0);				// ($addon = '', $start)
		print "\n</HTML> \n";
		exit();
    break;
?>