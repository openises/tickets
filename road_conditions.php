<?php

error_reporting(E_ALL);
$facs_side_bar_height = .5;		// max height of facilities sidebar as decimal fraction of screen height - default is 0.6 (60%)
$zoom_tight = FALSE;				// replace with a decimal number to over-ride the standard default zoom setting
$iw_width= "300px";					// map infowindow with
/*
1/3/14 New File - for Road Condition Alerts
*/

@session_start();	
session_write_close();
require_once('./incs/functions.inc.php');		//7/28/10
do_login(basename(__FILE__));
$key_field_size = 30;
$st_size = (get_variable("locale") ==0)?  2: 4;		

extract($_GET);
extract($_POST);
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}


function loc_format_date($date){
	if (get_variable('locale')==1)	{return date("j/n/y H:i",$date);}					// 08/27/10 - Revised to show UK format for locale = 1	
	else 							{return date(get_variable("date_format"),$date);}	// return date(get_variable("date_format"),strtotime($date));
	}				// end function fac format date
function isempty($arg) {
	return (bool) (strlen($arg) == 0) ;
	}

$usng = get_text('USNG');
$osgb = get_text('OSGB');

$c_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$c_types [$row['id']] = array ($row['title'], $row['icon']);
	}
unset($result);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Road Condition Alerts Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
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
	<SCRIPT SRC="./js/usng.js" 			TYPE="application/x-javascript"></SCRIPT>
	<SCRIPT SRC="./js/lat_lng.js" 		TYPE="application/x-javascript"></SCRIPT>
	<SCRIPT SRC="./js/geotools2.js" 	TYPE="application/x-javascript"></SCRIPT>
	<SCRIPT SRC="./js/osgb.js" 			TYPE="application/x-javascript"></SCRIPT>		
	<SCRIPT SRC='./js/misc_function.js' TYPE='application/x-javascript'></SCRIPT>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<SCRIPT SRC="./js/domready.js"		TYPE="application/x-javascript" ></script>
	<SCRIPT>
	var map;		// note global
	var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
	var theAlertTypeIcon;
	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;
	var map;								// map object

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
		var point = new google.maps.LatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
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

	var grid_bool = false;		
	function toglGrid() {						// toggle
		grid_bool = !grid_bool;
		if (grid_bool)	{ grid = new Graticule(map); }
		else 			{ grid.setMap(null); }
		}		// end function toglGrid()

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

	function validate(theForm) {						// form contents validation
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
		if (theForm.frm_name.value.trim()=="")											{errmsg+="Location NAME is required.\n";}
		if (theForm.frm_descr.value.trim()=="")											{errmsg+="Location DESCRIPTION is required.\n";}
		if ((theForm.frm_lat.value=="") || (theForm.frm_lng.value==""))					{errmsg+="Location LOCATION must be set - click map location to set.\n";}	// 11/11/09 position mandatory
		
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

	function add_res () {		// turns on add responder form
		showit('loc_add_form');
		hideit('tbl_locations');
		hideIcons();			// hides responder icons
		map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		}

// *********************************************************************

	function pt_to_map (my_form, theicon, lat, lng) {
		if(myMarker) {myMarker.setMap(null);}			// destroy predecessor

		my_form.frm_lat.value=lat;	
		my_form.frm_lng.value=lng;	
		my_form.show_lat.value=do_lat_fmt(lat);
		my_form.show_lng.value=do_lng_fmt(lng);
			
		var loc = <?php print get_variable('locale');?>;
		if(loc == 0) { my_form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
		if(loc == 1) { my_form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
		if(loc == 2) { my_form.frm_ngs.value=LLtoUTM(lat, lng, 5); }
	
		map.setCenter(new google.maps.LatLng(lat, lng), <?php print get_variable('def_zoom');?>);
		var image = theicon;
		var dp_latlng = new google.maps.LatLng(lat, lng);		

		myMarker = new google.maps.Marker({
			position: dp_latlng,
			icon: image, 
			draggable: true,
			map: map
			});
		myMarker.setMap(map);		// add marker with icon
		}				// end function pt_to_map ()
		
	function show_theType(id) {
		var url = "./ajax/get_alerticon.php?id=" + id;
		sendRequest (url, icon_cb, "");
		function icon_cb(req) {
			var theIcon=JSON.decode(req.responseText)
			var theTypeIcon = "<IMG SRC=\"./rm/roadinfo_icons/" + theIcon + "\">";
			$('icon_flag').innerHTML = theTypeIcon;	
			theAlertTypeIcon = "./rm/roadinfo_icons/" + theIcon;
			}		
		}
		
	function loc_lkup(my_form) {
		if(my_form.frm_type.value==0) {
			alert("Set Type first");
			return false;
			}
		if (my_form.frm_street.value.trim()=="") {
			alert ("Address is required for location lookup.");
			return false;
			}
		var geocoder = new google.maps.Geocoder();
		var myAddress = my_form.frm_street.value.trim();

		geocoder.geocode( { 'address': myAddress}, function(results, status) {		
			if (status == google.maps.GeocoderStatus.OK)	{ pt_to_map (my_form, theAlertTypeIcon, results[0].geometry.location.lat(), results[0].geometry.location.lng());}					
			else 											{ alert("Geocode lookup failed: " + status);}
			});				// end geocoder.geocode()

		}				// end function loc_lkup()

	function getAddress(latlng, currform) {
		var rev_coding_on = '<?php print get_variable('reverse_geo');?>';
		if (rev_coding_on == 0) return;		
		if(markersArray.length > 1) {
			clearOverlays(); 
			marker = new google.maps.Marker({position: latlng, map: map, draggable: true});			
			}
		map.setCenter(latlng);
		map.setZoom(18);
		var theCity = "";
		var thePostCode = "";
		var theState = "";
		var theStreet = "";

		(new google.maps.Geocoder()).geocode({latLng: latlng}, function(resp) {
			if (resp[0]) {
				var bits = [];
				for (var i = 0, I = resp[0].address_components.length; i < I; ++i) {
					var component = resp[0].address_components[i];
					if (contains(component.types, 'political')) {
						bits.push(component.long_name);
						}
					if (contains(component.types, 'administrative_area_level_1')) {
						theState = component.short_name;
						bits.push(component.long_name);
						}	
					if (contains(component.types, 'administrative_area_level_2')) {
						bits.push(component.long_name);
						}	
					if (contains(component.types, 'administrative_area_level_3')) {
						bits.push(component.long_name);
						}
					if (contains(component.types, 'colloquial_area')) {
						bits.push(component.long_name);
						}	
					if (contains(component.types, 'premise')) {
						bits.push(component.long_name);
						}		
					if (contains(component.types, 'sub_premise')) {
						bits.push(component.long_name);
						}										
					if (contains(component.types, 'street_address')) {
						theStreet = component.long_name;
						bits.push(component.long_name);
						}
					if (contains(component.types, 'postal_code')) {
						thePostCode = component.long_name
						bits.push(component.long_name);
						}						
					if (contains(component.types, 'intersection')) {
						bits.push(component.long_name);
						}	
					if (contains(component.types, 'route')) {
						bits.push(component.long_name);
						}						
					if (contains(component.types, 'locality')) {
						theCity = component.long_name;
						bits.push(component.long_name);
						}			
					if (contains(component.types, 'sublocality')) {
						bits.push(component.long_name);
						}		
					if (contains(component.types, 'neighborhood')) {
						bits.push(component.long_name);
						}	
					if (contains(component.types, 'neighborhood')) {
						bits.push(component.long_name);
						}						
					}
					switch(currform) {
					case "a":
						document.loc_add_form.frm_street.value = resp[0].formatted_address;
						document.loc_add_form.frm_city.value = theCity;
						document.loc_add_form.frm_state.value = theState;
						document.loc_add_form.frm_street.focus();	
						break;

					case "e":
						document.res_edit_Form.frm_street.value = resp[0].formatted_address;
						document.res_edit_Form.frm_city.value = theCity;
						document.res_edit_Form.frm_state.value = theState;
						document.res_edit_Form.frm_street.focus();
						break;
					default:
						alert ("596: error");
					}		// end switch()		
				}
			});
		}		

	function capWords(str){ 
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

	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {
		CngClass(the_id, 'plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}		
		
	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
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
</SCRIPT>
<?php
function list_locations($addon = '', $start) {
	global $iw_width, $c_types, $tolerance;

	$query = "SELECT *,
			`r`.`id` AS `cond_id`,
			`c`.`id` AS `type_id`,
			`r`.`description` AS `r_description`,
			`c`.`description` AS `type_description`,
			`r`.`title` AS `r_title`,
			`c`.`title` AS `type_title`,
			`c`.`icon`AS `icon_url`,
			`r`.`_on` AS `updated`
			FROM `$GLOBALS[mysql_prefix]roadinfo` `r` 
			LEFT JOIN `$GLOBALS[mysql_prefix]conditions` `c` ON `r`.`conditions`=`c`.`id` 
			ORDER BY `cond_id`";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	unset($result);

?>

<SCRIPT >
	var map = null;				// the map object - note GLOBAL
	var myMarker;					// the marker object
	var lat_var;						// see init.js
	var lng_var;
	var zoom_var;

	var icon_file = "./markers/crosshair.png";

	function call_back (in_obj){				// callback function - from gmaps_v3_init()
		do_lat(in_obj.lat);			// set form values
		do_lng(in_obj.lng);
		do_ngs();	
		}
//				826

		map =  gmaps_v3_init(call_back, 'map_canvas', 
			<?php echo get_variable('def_lat');?>, 
			<?php echo get_variable('def_lng');?>, 
			<?php echo (get_variable('def_zoom')*2);?>, 
			icon_file, 
			<?php echo get_variable('maptype');?>, 
			true);									// read-only

	var color=0;
	var colors = new Array ('odd', 'even');

	function hideDiv(div_area, hide_cont, show_cont) {
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "locs_list_sh") {
			var controlarea = "locs_list";
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

	function showDiv(div_area, hide_cont, show_cont) {
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "locs_list_sh") {
			var controlarea = "locs_list";
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

	function checkArray(form, arrayName)	{
		var retval = new Array();
		for(var i=0; i < form.elements.length; i++) {
			var el = form.elements[i];
			if(el.type == "checkbox" && el.name == arrayName && el.checked) {
				retval.push(el.value);
			}
		}
	return retval;
	}		

	function createMarker(point, tabs, id, icon, loc_id) {
		got_points = true;													// at least one
		var theImage = "./rm/roadinfo_icons/" + icon;
		var marker = new google.maps.Marker({position: point, map: map, icon: theImage});
		marker.id = loc_id;				// for hide/unhide - unused
		google.maps.event.addListener(marker, "click", function() {		// here for both side bar and icon click
			try {open_iw.close()} catch(err) {;}
			map.setZoom(8);			
			map.setCenter(point);
			var infowindow = new google.maps.InfoWindow({ content: tabs, maxWidth: 300});	 
			open_iw = infowindow;
			infowindow.open(map, marker);		
			});			// end google.maps.event.add Listener()

		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = tabs;									// tabs to array
		bounds.extend(point);
		return marker;
		}				// end function create Marker()

	function createdummyMarker(point, tabs, id, loc_id) {
		got_points = true;
		var image_file = "./our_icons/question1.png";
		var dummymarker = new google.maps.Marker({position: point, map: map, icon: image_file});		
		dummymarker.id = loc_id;				// for hide/unhide - unused
		google.maps.event.addListener(dummymarker, "click", function() {		// here for both side bar and icon click
			if (dummymarker) {
				try {open_iw.close()} catch(err) {;}
				map.setZoom(8);
				map.setCenter(point);
				infowindow = new google.maps.InfoWindow({ content: tabs, maxWidth: 300});	 
				open_iw = infowindow;				
				infowindow.open(map, dummymarker);
				}		// end if (marker)
			});			// end google.maps.Event.add Listener()
		gmarkers[id] = dummymarker;									// marker to array for side bar click function
		infoTabs[id] = tabs;									// tabs to array
		if (!(map_is_fixed)) {
			bounds.extend(point);
			map.fitBounds(bounds);			
			}
		return dummymarker;
		}				// end function create dummy Marker()
		
	function do_sidebar (sidebar, id, the_class, loc_id) {
		var loc_id = loc_id;
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"'>";
		side_bar_html += "<TD CLASS='" + the_class + "' onClick = myclick(" + id + "); >" + loc_id + sidebar +"</TD></TR>\n";		// 3/15/11
		}

	function do_sidebar_nm (sidebar, line_no, id, loc_id) {	
		var loc_id = loc_id;	
		var letter = to_str(line_no);	
		side_bar_html += "<TR CLASS='" + colors[(line_no)%2] +"'>";
		side_bar_html += "<TD onClick = myclick_nm(" + id + "); >" + loc_id + sidebar +"</TD></TR>\n";		// 1/23/09, 10/29/09 removed period, 11/11/09, 3/15/11
		}

	function myclick_nm(v_id) {				// Responds to sidebar click - view responder data
		document.view_form.id.value=v_id;
		document.view_form.submit();
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [id]
		google.maps.event.trigger(gmarkers[id], "click");
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

		function get_info_win_ary( loc_id) { 					// gmaps API V3
				var contentString = [
				  '<div id="tabs">',
				  '<ul>',
					'<li><a href="#tab-1"><span>One</span></a></li>',
					'<li><a href="#tab-2"><span>Two</span></a></li>',
					'<li><a href="#tab-3"><span>Three</span></a></li>',
				  '</ul>',
				  '<div id="tab-1">',
					'<p>Tab 1</p>',
				  '</div>',
				  '<div id="tab-2">',
				   '<p>Tab 2</p>',
				  '</div>',
				  '<div id="tab-3">',
					'<p>Tab 3</p>',
				  '</div>',
				  '</div>'
				].join('');
				return contentString;
				}


	var icons=new Array;							// maps type to icon blank

<?php
$dzf = get_variable('def_zoom_fixed');
print "\tvar map_is_fixed = ";
print (((my_is_int($dzf)) && ($dzf==2)) || ((my_is_int($dzf)) && ($dzf==3)))? "true;\n":"false;\n";

?>
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_locations' WIDTH='100%'>";
	side_bar_html += "<TR class='even'>	<TD WIDTH='5%'><B>ID</B></TD><TD WIDTH='30%' ALIGN='left'><B>Name</B></TD>";
	side_bar_html += "<TD WIDTH='40%' ALIGN='left'><B><?php print get_text("Street"); ?></B></TD><TD WIDTH='25%' ALIGN='left'><B><?php print get_text("As of"); ?></B></TD></TR>";
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = <?php print $start; ?>;					// sidebar/icon index
	var got_points = false;							// none
	var open_iw = false;							// no open infowindow

	var myLatlng = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
	var mapOptions = {
		zoom: <?php print get_variable('def_zoom');?>,
		center: myLatlng,
		panControl: true,
	    zoomControl: true,
	    scaleControl: true,
	    mapTypeId: google.maps.MapTypeId.<?php echo get_maptype_str(); ?>
		}	

	var map = new google.maps.Map($('map_canvas'), mapOptions);				// 1145

	map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);

	var bounds = new google.maps.LatLngBounds();		// Initialize bounds for the map
	
	var listIcon = new google.maps.MarkerImage("./markers/yellow.png");		<?php echo "// " . __LINE__ . "\n";?>
	listIcon.shadow = "./markers/sm_shadow.png";
	listIcon.iconSize = new google.maps.Size(30, 30);
	listIcon.shadowSize = new google.maps.Size(16, 28);
	listIcon.iconAnchor = new google.maps.Point(8, 28);
	listIcon.infoWindowAnchor = new google.maps.Point(9, 2);
	listIcon.infoShadowAnchor = new google.maps.Point(18, 25);

<?php
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$query = "SELECT *,
			`r`.`id` AS `cond_id`,
			`c`.`id` AS `type_id`,
			`r`.`description` AS `r_description`,
			`c`.`description` AS `type_description`,
			`r`.`title` AS `r_title`,
			`c`.`title` AS `type_title`,
			`c`.`icon`AS `icon_url`,
			`r`.`_on` AS `updated`
			FROM `$GLOBALS[mysql_prefix]roadinfo` `r` 
			LEFT JOIN `$GLOBALS[mysql_prefix]conditions` `c` ON `r`.`conditions`=`c`.`id` 
			ORDER BY `cond_id` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_locations = mysql_affected_rows();
	$i=1;				// counter
// =============================================================================
	$utc = gmdate ("U");
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// ==========  major while() for Location ==========
		$the_on_click = (my_is_float($row['lat']))? " onClick = myclick({$row['cond_id']}); " : " onClick = myclick_nm({$row['cond_id']}); ";
		$got_point = FALSE;
		print "\n\t\tvar i=$i;\n";

		if(is_guest()) {
			$toedit = "";
			}
		else {
			$toedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='road_conditions.php?func=location&edit=true&id=" . $row['cond_id'] . "'><U>Edit</U></A>" ;
			}		

		if (!($got_point) && ((my_is_float($row['lat'])))) {
			if(((float) $row['lat']==$GLOBALS['NM_LAT_VAL']) && ((float)$row['lng']==$GLOBALS['NM_LAT_VAL'])) {
				echo "\t\tvar point = new google.maps.LatLng(" . get_variable('def_lat') . ", " . get_variable('def_lng') .");\n";
			} else {
				echo "\t\tvar point = new google.maps.LatLng(" . $row['lat'] . ", " . $row['lng'] .");\n";
			}
			$got_point= TRUE;
			}

		$update_error = strtotime('now - 6 hours');							// set the time for silent setting
// name

		$display_name = $name = shorten(htmlentities($row['title'], ENT_QUOTES), 20);	
		$display_street = $street = shorten(htmlentities($row['street'], ENT_QUOTES), 40);			

		$sidebar_line = "&nbsp;&nbsp;<TD WIDTH='30%' TITLE = '{$row['r_title']}' {$the_on_click}><U><SPAN STYLE='background-color: #FFFFFF;  opacity: .7; color:#000000;'>" . addslashes($name) ."</SPAN></U></TD>";	//	6/10/11
		$sidebar_line .= "<TD WIDTH='40%' TITLE = '" . addslashes($street) . "' {$the_on_click}><U><SPAN STYLE='background-color: #FFFFFF;  opacity: .7; color:#000000;'><NOBR>" . addslashes($street) ."</NOBR></SPAN></U></TD>";

// as of
		$strike = $strike_end = "";
		$the_time = $row['updated'];
		$the_class = "";
		$sidebar_line .= "<TD WIDTH='25%' CLASS='$the_class'> $strike <NOBR>" . new_format_sb_date($the_time) . "</NOBR> $strike_end</TD>";

// tab 1

		if (my_is_float($row['lat'])) {										// position data of any type?
		
			$line_ctr = 0;
			$tab_1 = "<TABLE CLASS='infowin' width='{$iw_width}'>";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($display_name, 48)) . "</B></TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD class='td_label' ALIGN='left'>Description:&nbsp;</TD><TD ALIGN='left' class='td_data'>" . addslashes(shorten(str_replace($eols, " ", $row['r_description']), 32)) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD class='td_label' ALIGN='left'>As of:&nbsp;</TD><TD ALIGN='left' class='td_data'>" . format_date(strtotime($row['updated'])) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='spacer'><TD COLSPAN=2 ALIGN='center'>" . $toedit . "&nbsp;&nbsp;<A HREF='road_conditions.php?func=location&view=true&id=" . $row['cond_id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
			$tab_1 .= "</TABLE>";
?>
			var myinfoTabs = "<?php echo $tab_1;?>";
<?php


// tab 2
		$tabs_done=FALSE;		// default

		if (!($tabs_done)) {	//
			}		// end if/else

		$name = $row['r_title'];	// 10/8/09		 4/28/11
		if(((float)$row['lat']==$GLOBALS['NM_LAT_VAL']) && ((float)$row['lng']==$GLOBALS['NM_LAT_VAL'])) {		
?>
		var loc_id = "<?php print $index;?>";	//	10/8/09
		var the_class = ((map_is_fixed) && (!(bounds.contains(point))))? "emph" : "td_label";

		do_sidebar ("<?php print $sidebar_line; ?>", loc_id, the_class, loc_id);
		var dummymarker = createdummyMarker(point, myinfoTabs, loc_id, loc_id);
		dummymarker.setMap(map);
<?php
		} else {
?>
		var loc_id = "<?php print $row['cond_id'];?>";	//	10/8/09
		var the_class = ((map_is_fixed) && (!(bounds.contains(point))))? "emph" : "td_label";

		do_sidebar ("<?php print $sidebar_line; ?>", loc_id, the_class, loc_id);
		var marker = createMarker(point, myinfoTabs, loc_id, '<?php print $row['icon_url'];?>', loc_id);	// 1548 (point,tabs, color, id)	6/17/13 Changed from $row['type'] to $row['icon']
		marker.setMap(map);		// 1578

<?php
			}	// End if/else 
		} else {		// end ANY position data available

			$name = $row['title'];		
			$temp = explode("/", $name );
			$index = substr($temp[count($temp) -1], -6, strlen($temp[count($temp) -1]));

?>
			var loc_id = "<?php print $index;?>";
<?php		
			print "\tdo_sidebar_nm (\" {$sidebar_line} \" , i, {$row['id']}, loc_id);\n";	// sidebar only - no map
			}
	$i++;				// zero-based
	}				// end  ==========  while() for Location ==========

?>
	if (!(map_is_fixed)) {
		if (!got_points) {		// any? - 6/18/12
			map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			map.fitBounds(bounds);					// Now fit the map to the bounds  - ({Z:{b:33.7489954, d:49.3844788492429}, ca:{b:-97.23322530034568, d:-76.612189}})
			var listener = google.maps.event.addListenerOnce (map, "idle", function() { 
				if (map.getZoom() > 16) map.setZoom(15); 
				});			
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
	$("num_locations").innerHTML = <?php print $num_locations;?>;

</SCRIPT>
<?php
	}				// end function list_locations() ===========================================================


	function finished ($caption) {
		print "</HEAD><BODY><!--" . __LINE__ . " -->";
		require_once('./incs/links.inc.php');	// 10/6/09
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='location'>";
		print "</FORM>\n<A NAME='bottom' />\n</BODY></HTML>";
		}

	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";
	$_dodisp = 			(array_key_exists ('disp',$_GET ))? $_GET['disp']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Location - checkbox
		$query = "DELETE FROM $GLOBALS[mysql_prefix]roadinfo WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Location <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		}
	else {
		if ($_getgoedit == 'true') {
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? "NULL" : quote_smart(trim($_POST['frm_lat'])) ;
			$the_lng = empty($_POST['frm_lng'])? "NULL" : quote_smart(trim($_POST['frm_lng'])) ;
			
			$loc_id = $_POST['frm_id'];
			$by = $_SESSION['user_id'];					// 6/4/2013
			$from = $_SERVER['REMOTE_ADDR'];			
			$query = "UPDATE `$GLOBALS[mysql_prefix]roadinfo` SET
				`title`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`description`= " . 	quote_smart(trim($_POST['frm_state'])) . ",		
				`address`= " . 		quote_smart(trim($_POST['frm_street'])) . ",
				`conditions`= " . 	$_POST['frm_type'] . ",		
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`_by`= " . 			quote_smart(trim($by)) . ",
				`_on`= " . 			quote_smart(trim($now)) . ",
				`_from`= " . 		quote_smart(trim($from)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_WARNLOCATION_CHANGE'], 0, $_POST['frm_id'], $_POST['frm_status_id']);}	//2/17/11
			$caption = "<i>" . stripslashes_deep($_POST['frm_name']) . "</i><B>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$by = $_SESSION['user_id'];		//	4/14/11
		$frm_lat = (empty($_POST['frm_lat']))? 'NULL': quote_smart(trim($_POST['frm_lat']));
		$frm_lng = (empty($_POST['frm_lng']))? 'NULL': quote_smart(trim($_POST['frm_lng']));
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));	
		$by = $_SESSION['user_id'];					// 6/4/2013
		$from = $_SERVER['REMOTE_ADDR'];			
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]roadinfo` (
			`title`, 
			`description`, 
			`address`, 
			`conditions`,
			`lat`, 
			`lng`, 
			`_by`, 
			`_on`, 
			`_from` )
			VALUES (" .
			quote_smart(trim($_POST['frm_name'])) . "," .
			quote_smart(trim($_POST['frm_descr'])) . "," .
			quote_smart(trim($_POST['frm_street'])) . "," .
			$_POST['frm_type'] . "," .
			$frm_lat . "," .
			$frm_lng . "," .				
			quote_smart(trim($by)) . "," .
			quote_smart(trim($now)) . "," .
			quote_smart(trim($from)) . ");";

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();

		do_log($GLOBALS['LOG_WARNLOCATION_ADD'], 0, mysql_insert_id(), 0);	//	2/17/11

		$caption = "<B>Location  <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";

		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
// add ===========================================================================================================================
// add ===========================================================================================================================

	if ($_getadd == 'true') {
?>
		</HEAD>
		<BODY onLoad = "ck_frames();">		<!-- <?php echo __LINE__; ?> -->
		<A NAME='top'>
<?php
		require_once('./incs/links.inc.php');
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
?>
		<TABLE BORDER=0 ID='outer' WIDTH='80%'><TR><TD WIDTH='50%'>
		<TABLE BORDER="0" ID='addform' WIDTH='98%'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'><?php print get_text("Add Road Conditions Alert"); ?></FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>		
		<FORM NAME= "loc_add_form" METHOD="POST" ACTION="<?php print basename(__FILE__);?>?func=location&goadd=true">
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Give the alert a Title"><?php print get_text("Title"); ?></A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
			<TD COLSPAN=3 ><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" /></TD>
		</TR>
		<TR CLASS = "even" VALIGN='middle'>
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Road Condition Type"><?php print get_text("Type"); ?></A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN='left'>
				<SELECT NAME='frm_type' onChange = 'show_theType(this.value);'>
					<OPTION VALUE=0>Select one</OPTION>
<?php
					foreach ($c_types as $key => $value) {
						$temp = $value; 												// 2-element array
						print "\t\t\t\t<OPTION VALUE='" . $key . "'>" . $temp[0] . "</OPTION>\n";
						}
?>
				</SELECT><SPAN id='icon_flag'></SPAN>
			</TD>
		</TR>
		<TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>			
		<TR CLASS='even'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Street Address - type in street address in fields or click location on map "><?php print get_text("Address"); ?></A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.loc_add_form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD>
		<TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Description - additional details about the alert">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3 ><TEXTAREA NAME="frm_descr" COLS=60 ROWS=2></TEXTAREA></TD></TR>
		<TR CLASS = "odd"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Latitude and Longitude - set from map click">
			<SPAN onClick = 'javascript: do_coords(document.loc_add_form.frm_lat.value ,document.loc_add_form.frm_lng.value)'>
				<?php print get_text("Lat/Lng"); ?></A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle'
					onClick = 'do_unlock_pos(document.loc_add_form);'><TD COLSPAN=3>
			<INPUT TYPE="text" NAME="show_lat" SIZE=11 VALUE="" disabled />
			<INPUT TYPE="text" NAME="show_lng" SIZE=11 VALUE="" disabled />&nbsp;&nbsp;
<?php
	$locale = get_variable('locale');
	switch($locale) { 
		case "0":
?>
		<SPAN ID = 'usng_link' onClick = "do_usng_conv(loc_add_form)" style='font-weight: bold;'>USNG:</SPAN><INPUT TYPE="text" SIZE=19 NAME="frm_ngs" VALUE="" disabled /></TD></TR>
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
			<INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>"  onClick="validate(document.loc_add_form);"  STYLE = 'margin-left: 20px' /></TD></TR>
		<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
		<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
		</FORM></TABLE> <!-- end inner left -->
		</TD><TD ALIGN='center' WIDTH='50%'>
		<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
		<BR /><BR /><B>Drag/Click to unit location</B>
		<BR /><A HREF='#' onClick='toglGrid()'><u>Grid</U></A>

		<BR /><BR />
		</TD></TR></TABLE><!-- end outer -->

<?php
		$icon_file = "./markers/crosshair.png";
?>
<script>
//										some globals		
		var map = null;				// the map object - note GLOBAL
		var myMarker;					// the marker object
		var lat_var;						// see init.js
		var lng_var;
		var zoom_var;

		var icon_file = "./markers/crosshair.png";

		function call_back (in_obj){				// callback function - from gmaps_v3_init()
			do_lat(in_obj.lat);			// set form values
			do_lng(in_obj.lng);
			do_ngs();	
			}
	//				2192 - Add

		map = gmaps_v3_init(call_back, 'map_canvas', 
			<?php echo get_variable('def_lat');?>, 
			<?php echo get_variable('def_lng');?>, 
			<?php echo get_variable('def_zoom');?>, 
			icon_file, 
			<?php echo get_variable('maptype');?>, 
			false);		

</script>
		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- <?php echo __LINE__;?> -->
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
		$query = "SELECT *,
				`r`.`id` AS `cond_id`,
				`c`.`id` AS `type_id`,
				`r`.`description` AS `r_description`,
				`c`.`description` AS `type_description`,
				`r`.`title` AS `r_title`,
				`c`.`title` AS `type_title`,
				`c`.`icon`AS `icon_url`,
				`r`.`lat` AS `lat`,
				`r`.`lng` AS `lng`,
				`r`.`address` AS `address`,
				`r`.`_on` AS `updated`
				FROM `$GLOBALS[mysql_prefix]roadinfo` `r` 
				LEFT JOIN `$GLOBALS[mysql_prefix]conditions` `c` ON `r`.`conditions`=`c`.`id` 
				WHERE `r`.`id`=$id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= mysql_fetch_assoc($result);
		$lat = $row['lat'];
		$lng = $row['lng'];
?>
		</HEAD>
		<BODY onLoad = "ck_frames(); " > 	<!-- <?php echo __LINE__; ?> -->
		<A NAME='top'>
<?php
		require_once('./incs/links.inc.php');
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
?>
		<TABLE BORDER=0 ID='outer' WIDTH='80%'><TR><TD WIDTH='50%'>
		<TABLE BORDER=0 ID='editform'>
		<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit Road Condition Alert '<?php print $row['title'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
		<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>
		<FORM METHOD="POST" NAME= "res_edit_Form" ACTION="<?php print  basename(__FILE__);?>?func=location&goedit=true">

		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location Name - fill in with Name of location">Name</A>:&nbsp;<font color='red' size='-1'>*</font></TD>			<TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['title'] ;?>" /></TD></TR>
		<TR CLASS = "even" VALIGN='middle'>
			<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Road Condition Type"><?php print get_text("Type"); ?></A>:&nbsp;<font color='red' size='-1'>*</font></TD>
			<TD ALIGN='left'>
				<SELECT NAME='frm_type' onChange = 'show_theType(this.value);'>
					<OPTION VALUE=0>Select one</OPTION>
<?php
					foreach ($c_types as $key => $value) {
						$temp = $value; 						// 2-element array
						$sel = ($row['conditions'] == $key) ? " SELECTED" : "";
						print "\t\t\t\t<OPTION VALUE='" . $key . "'" . $sel . ">" . $temp[0] . "</OPTION>\n";
						}
?>
				</SELECT><SPAN id='icon_flag'></SPAN>
			</TD>
		</TR>

<?php
	$dis_rmv = " ENABLED";
?>
			</TD></TR>
		<TR CLASS='even'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Street Address - type in street address in fields or click location on map ">Location</A>:&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.res_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD>
		<TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['address'] ;?>"  MAXLENGTH="61"></TD></TR> <!-- 7/5/10 -->
		<TR CLASS = "even"><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font></TD>	<TD COLSPAN=3><TEXTAREA NAME="frm_descr" COLS=60 ROWS=2><?php print $row['description'];?></TEXTAREA></TD></TR>
<?php
		$map_capt = "<BR /><BR /><CENTER><B><FONT CLASS = 'normal_text'>Click Map to revise location</FONT></B>";
		$lock_butt ="<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_edit_Form);'>";
		$usng_link = "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_edit_Form)'>{$usng}:</SPAN>";
		$osgb_link = "<SPAN ID = 'osgb_link'>{$osgb}:</SPAN>";		
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
		<TR CLASS="even" VALIGN='baseline'><TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Delete Road Condition Alert from System">Remove Alert</A>:&nbsp;</TD><TD><INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
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
		</FORM></TABLE>
		</TD><TD ALIGN='center' WIDTH='50%'><DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset'></DIV>
		<BR /><A HREF='#' onClick='toglGrid()'><u>Grid</U></A><BR />

		<?php print $map_capt; ?></TD></TR></TABLE>
<?php
		if (my_is_float($row['lat'])) {
			}
		else {
			}
		$icon_file = "./rm/roadinfo_icons/" . $row['icon_url'];
?>
<script>
		var icon_file = "<?php print $icon_file;?>";

		function call_back (in_obj){				// callback function - from gmaps_v3_init()
			do_lat(parseFloat(in_obj.lat));			// set form values
			do_lng(parseFloat(in_obj.lng));
			do_ngs();	
			}

		map = gmaps_v3_init(call_back, 'map_canvas', 
			<?php echo $row['lat'];?>, 
			<?php echo $row['lng'];?>, 
			<?php echo (get_variable('def_zoom'));?>, 
			icon_file, 
			<?php echo get_variable('maptype');?>, 
			false);		

</script>

		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
		<!-- 2431 -->
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

			
			$id = $_GET['id'];
			$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]roadinfo` WHERE `id`= " . $id . " LIMIT 1";	// 1/19/2013
			$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
			$row	= stripslashes_deep(mysql_fetch_assoc($result));
			$lat = $row['lat'];
			$lng = $row['lng'];
			$coords =  $row['lat'] . "," . $row['lng'];		// for UTM			
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
			</HEAD>	<!-- <?php echo __LINE__; ?> -->
<?php
			print "\t<BODY onLoad = 'ck_frames()' > <!-- " . __LINE__ . "-->\n";
			print "<A NAME='top'>\n";			// 11/11/09
			require_once('./incs/links.inc.php');
			print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";

?>
			<FONT CLASS="header">Warn Location'<?php print $row['title'] ;?>' Data</FONT> (#<?php print $row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='outer'><TR><TD>
			<TABLE BORDER=0 ID='view_location' STYLE='display: block'>
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Name"); ?>: </TD>			<TD><?php print $row['title'];?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label"><?php print get_text("Location"); ?>: </TD><TD><?php print $row['street'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = 'even'><TD CLASS="td_label"><?php print get_text("City"); ?>: &nbsp;&nbsp;&nbsp;&nbsp;</TD><TD><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD></TR> <!-- 7/5/10 -->
			<TR CLASS = "even"><TD CLASS="td_label"><?php print get_text("Description"); ?>: </TD>	<TD><?php print $row['description'];?></TD></TR>
			<TR CLASS = 'odd'><TD CLASS="td_label">As of:</TD>	<TD><?php print loc_format_date(strtotime($row['_on'])); ?></TD></TR>
<?php
			if (my_is_float($lat)) {
?>		
				<TR CLASS = "even"><TD CLASS="td_label"  onClick = 'javascript: do_coords(<?php print "$lat,$lng";?>)'><U>Lat/Lng</U>:</TD><TD>
					<?php print get_lat($lat);?> <?php print get_lng($lng);?>&nbsp;

<?php

					$usng_val = LLtoUSNG($row['lat'], $row['lng']);
					$osgb_val = LLtoOSGB($row['lat'], $row['lng']) ;
					$utm_val = toUTM("{$row['lat']}, {$row['lng']}");

					$locale = get_variable('locale');
					switch($locale) { 
						case "0":?>
						&nbsp;USNG: <?php print $usng_val;?></TD></TR>	<!-- 9/13/08 -->
<?php 					
						break;
						case "1":
?>
						&nbsp;OSGB: <?php print $osgb_val;?></TD></TR>	<!-- 9/13/08 -->
<?php
						break;
						default:
?>
						&nbsp;UTM: <?php print $utm_val;?>'</TD></TR>	<!-- 9/13/08 -->
<?php
						}		// end switch()

				}		// end if (my_is_float($lat))

				$toedit = (is_administrator() || is_super())? "<SPAN id='edit_but' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;</SPAN>": "" ;
?>
				<TR><TD COLSPAN=2>&nbsp;</TD></TR>
<?php
			if (is_administrator() || is_super()) {
?>
				<TR CLASS = "even">
					<TD COLSPAN=99 ALIGN='center'>
						<DIV style='text-align: center;'>
							<SPAN id='edit_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick= 'to_edit_Form.submit();'>Edit</SPAN>
							<SPAN id='can_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick= 'document.can_Form.submit();'>Cancel</SPAN>
						</DIV>
					</TD>
				</TR>
<?php
				}		// end if (is_administrator() || is_super())
			print "</TABLE>\n";
?>
			</TD><TD ALIGN='center'><DIV ID='map_canvas' style="width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: inset;"></DIV>
			<BR />
			<BR /><SPAN onClick='toglGrid()'><u>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN onClick='doTraffic()'><U>Traffic</U></SPAN>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN ID='do_sv' onClick = 'sv_win(document.res_view_Form)'><u>Street view</U></SPAN>
				<BR /><BR />
			</TD></TR></TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename( __FILE__);?>"></FORM>
			<FORM NAME="to_edit_Form" METHOD="post" ACTION = "<?php print basename(__FILE__);?>?func=location&edit=true&id=<?php print $id; ?>"></FORM>
							<!-- END Location VIEW -->
<?php
				if(!(my_is_float($lat))) {	
					} else {
					if(((float)$lat==$GLOBALS['NM_LAT_VAL']) && ((float)$lng==$GLOBALS['NM_LAT_VAL'])) {	// checks for facilities input in no maps mode 7/28/10
						}											
					}
			$icon_file =  ((float)$lat==(float)$GLOBALS['NM_LAT_VAL'])? "./our_icons/question1.png" : "./markers/yellow.png";
?>
<script>
			map = gmaps_v3_init(null, 'map_canvas', 
				<?php echo $lat;?>, 
				<?php echo $lng;?>, 
				<?php echo (get_variable('def_zoom')*2);?>, 
				'<?php echo $icon_file;?>',  
				<?php echo get_variable('maptype');?>, 
				true);		
</script>

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
?>
		</HEAD>
		<BODY onLoad = "ck_frames();" ><!-- <?php echo __LINE__ ;?> -->
		<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
		<SCRIPT TYPE="application/x-javascript" src="./js/elabel_v3.js"></SCRIPT>		
		<A NAME='top'>		<!-- 11/11/09 -->
<?php
		print "<SPAN STYLE = 'margin-left:100px;'>{$caption}</SPAN>";
?>
		<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;z-index: 1;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png"  BORDER=0></DIV>
<?php
		require_once('./incs/links.inc.php');
		$required = 250 + (mysql_affected_rows()*40);
		$facs_side_bar_height = .9;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)		
		$the_height = (integer)  min (round($facs_side_bar_height * $_SESSION['scr_height']), $required );		// set the max	
		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
			
		$heading = "Warn Locations - " . get_variable('map_caption');
?>
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
				</TR>
				<TR>
					<TD WIDTH = '50%'>
						<TABLE ID = 'sidebar' BORDER = 0 WIDTH='98%'>
							<TR class='even'>
								<TD ALIGN='center'><B>Warn Locations (<DIV id="num_locations" style="display: inline;"></DIV>)</B>
								</TD>
							</TR>
							<TR class='odd'>	
								<TD ALIGN='center'>Click line or icon for details
								</TD>
							</TR>			
							<TR>
								<TD>
									<DIV ID='side_bar' style="height: auto;  overflow-y: scroll; overflow-x: hidden;"></DIV>
								</TD>
							</TR>
							<TR class='spacer'>
								<TD class='spacer'>&nbsp;
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
								<TD ALIGN='center'>
									<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
								</TD>
							</TR>	<!-- 3/15/11 -->
							<TR>
								<TD>&nbsp;</TD>
							</TR>
							<TR class = 'odd'>
								<TD ALIGN='center' class='td_label'>
									<SPAN CLASS="legend" STYLE="font-size: 14px; text-align: center; vertical-align: middle; width: <?php print get_variable('map_width');?>-25px;"><B>Legend:</B></SPAN>
								</TD>
							</TR>  <!-- 3/15/11 -->
						</TABLE>
					</TD>
				</TR>
			</TABLE>
		</DIV>	<!-- end of outer -->
		<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='func' VALUE='location'>
		<INPUT TYPE='hidden' NAME='view' VALUE='true'>
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>

		<FORM NAME='add_Form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='func' VALUE='location'>
		<INPUT TYPE='hidden' NAME='add' VALUE='true'>
		</FORM>

		<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print  basename(__FILE__);?>?func=location"></FORM>
		<!-- 1452 -->
		<A NAME="bottom" /> 
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
		</BODY>				<!-- END LOCATION LIST and ADD -->
<?php
		$buttons = "<TR><TD COLSPAN=99 ALIGN='center'>";
		if ((!(is_guest())) && (!(is_unit()))) {		// 7/27/10
			$buttons .="<INPUT TYPE='button' value= 'Add a Location'  onClick ='document.add_Form.submit();'  STYLE = 'margin-left: 60px;'>";
			}
		$buttons .= "</TD></TR>";

		print list_locations($buttons, 0);				// ($addon = '', $start)
		print "\n</HTML> \n";
		exit();
    break;
?>