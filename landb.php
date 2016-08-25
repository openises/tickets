<?php
/*
5/25/11 initial release
7/2/11 corrections to include filled data as hiddens
7/3/11 added 2 fields to schema
7/30/11 table renamed
5/22/13 Totally revised for google maps API V3
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
require_once('incs/functions.inc.php');	
//	dump ($_POST);
	$tablename = "{$GLOBALS['mysql_prefix']}mmarkup";		// 7/30/11


	$query = "CREATE TABLE IF NOT EXISTS `{$tablename}` (
		  `id` bigint(4) NOT NULL AUTO_INCREMENT,
		  `line_name` varchar(32) NOT NULL,
		  `line_status` int(2) NOT NULL DEFAULT '0' COMMENT '0 => show, 1 => hide',
		  `line_type` int(2) NOT NULL DEFAULT '0' COMMENT 'poly, circle, ellipse',
		  `line_data` varchar(4096) NOT NULL,
		  `use_with_bm` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with base map',
		  `use_with_r` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with regions',		  
		  `use_with_f` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with facilities',
		  `use_with_u_ex` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with units - exclusion zone',
		  `use_with_u_rf` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with units - ringfence',
		  `line_color` varchar(8) DEFAULT NULL,
		  `line_opacity` float DEFAULT NULL,
		  `line_width` int(2) DEFAULT NULL,
		  `fill_color` varchar(8) DEFAULT NULL,
		  `fill_opacity` float DEFAULT NULL,
		  `filled` int(1) DEFAULT '0',
		  `_by` int(7) NOT NULL DEFAULT '0',
		  `_from` varchar(16) DEFAULT NULL,
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `ID` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Lines and borders'" ;
	$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
		
		

@session_start();
session_write_close();
do_login(basename(__FILE__));

$by = empty($_SESSION)? 0: $_SESSION['user_id'];
$from = $_SERVER['REMOTE_ADDR'];
$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10

if (array_key_exists("id", $_POST) && (!(empty($_POST['id'])))) {
	$query 	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `{$tablename}` WHERE `id` = {$_POST['id']}";				// 1/27/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

	if (mysql_num_rows ($result) > 0) {	
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		extract ($row);
		$points_ary = array();
		$points = explode (";", $line_data);
		for ($i = 0; $i<count($points); $i++) {
			array_push($points_ary, $points[$i]);
			}
	//	dump($points_ary );
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets Map Markup Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"/>
<META HTTP-EQUIV="Expires" CONTENT="0"/>
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE"/>
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE"/>
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript"/>
<META HTTP-EQUIV="Script-date" CONTENT="12/15/10 3:55"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE>
/* comment */
A:hover 					{text-decoration: underline; color: red;}
TH:hover 					{text-decoration: underline; color: red;}
td.mylink:hover 			{background-color: rgb(255, 255, 255); }
INPUT.button 				{background-color: rgb(255, 255, 255); }
input.text:focus, textarea:focus	{background-color: lightyellow; color:black;}
tr 							{height: 30px; }
tr.front 					{height: 18px; }
.style1 {background-color:transparent;font-weight:bold;border:0px black solid;white-space:nowrap; font-size : 1.5em; font-family:"arial"; opacity: 0.75; font-style:italic}
</STYLE>

<?php
$_func = (empty($_POST)) ?  "l" : $_POST['_func'];							// list mode as default	
?>
<SCRIPT SRC="./js/jscolor/jscolor.js"  type="text/javascript"></SCRIPT>
<?php
if ($_SESSION['internet']) {
	$api_key = get_variable('gmaps_api_key');
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
	if($key_str) {
?>
		<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
		<script type="text/javascript" src="./js/Google.js"></script>
<?php 
		}
	}
?>
<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>	<!-- 8/23/08 -->
<SCRIPT SRC="./js/lat_lng.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/geotools2.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/osgb.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->	
<SCRIPT SRC='./js/graticule_V3.js' type='text/javascript'></SCRIPT> 
<SCRIPT SRC='./js/misc_function.js' type='text/javascript'></SCRIPT>  <!-- 4/14/10 -->
<SCRIPT SRC="./js/domready.js"		TYPE="text/javascript" ></script>
<SCRIPT SRC="./js/gmaps_v3_init.js"	TYPE="text/javascript" ></script>
<SCRIPT src = "./js/elabel_v3.js"></SCRIPT>
<SCRIPT>
var map, poly;					// Global variables
var count = 0;
var points = new Array();
var markers = new Array();
var icon_url ="./markers/";
var tooltip;

var icon_file = new google.maps.MarkerImage(
	'./markers/yellow.png',
	new google.maps.Size(16, 28),
	new google.maps.Point(0,0),
	new google.maps.Point(8, 28)
	);
var shadow = new google.maps.MarkerImage(
	'./markers/sm_shadow.png',
	new google.maps.Size(22, 20),
	new google.maps.Point(0,0),
	new google.maps.Point(8, 28)
	);
	
var base_icon = new google.maps.MarkerImage(
	'./markers/sm_red.png',
	new google.maps.Size(16, 28),
	new google.maps.Point(0,0),
	new google.maps.Point(8, 28)
	);
			
function $() {									// 12/20/08
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
	
function add_hash(in_str) { // prepend # if absent
	return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
	}

function hex_OK (instr) {
	return (/^(#[A-F0-9]{6})$/i.test(instr));
	}

function do_checked(theForm) {								// 126
	$('fill_tr').style.display = '';
	theForm.frm_filled.value = 1;
	theForm.frm_filled_n.checked = false;
	theForm.frm_filled_y.checked = true;
	}
	
function do_un_checked(theForm) {
	$('fill_tr').style.display = "none";					// hide input row
	theForm.frm_filled.value = 0;
	theForm.frm_filled_n.checked = true;
	theForm.frm_filled_y.checked = false;
	}

function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity) {
	draw_circle = new google.maps.Circle({
		center: new google.maps.LatLng(lat,lng),
		map: map,
		fillColor: fillColor,
		fillOpacity: fillOpacity,
		strokeColor: strokeColor,
		strokeOpacity: strokeOpacity,
		strokeWeight: strokeWidth,
		editable: false
		});
	draw_circle.setRadius(radius*5000); 	
	draw_circle.setMap(map);	
	}

function drawBanner(point, html, text, font_size, color, name) {        // Create the banner
	var invisibleIcon = new google.maps.MarkerImage("./markers/markerTransparent.png");
	var the_color = (typeof color == 'undefined')? "000000" : color ;	// default to black
	var label = new ELabel({
		latlng: point, 
		label: html, 
		offset: new google.maps.Size(-8, 4), 
		opacity: 100,
		theSize: font_size + "px",		
		theColor:add_hash(the_color),
		overlap: true,
		clicktarget: false
		});	
	label.setMap(map);		
	var marker = new google.maps.Marker(point,invisibleIcon);	        // Create an invisible google.maps.Marker
	marker.setMap(map);		
	}				// end function draw Banner()

var circle_OK = false;	

function chk_circle(theForm) {
	var err_msg = "";
	if (!(count == 1)) 									{err_msg += "Click map for circle center\n";}
	if (!(is_ok_radius (theForm.circ_radius.value))) 	{err_msg += "Valid circle radius is required\n";};

	if (!(err_msg == "")) {
		alert ("Please correct the following:\n\n" + err_msg);
		return;
		}
	else {
		circle_OK = true;

		var lat = parseFloat(points[0].lat().toFixed(6));
		var lng = parseFloat(points[0].lng().toFixed(6));
		var radius = parseFloat(theForm.circ_radius.value);
		strokeColor = theForm.frm_line_color.value;
		strokeWidth = parseInt(theForm.frm_line_width.value);
		strokeOpacity = parseFloat(theForm.frm_line_opacity.value);
		fillColor = theForm.frm_fill_color.value;
		fillOpacity = (theForm.frm_filled.value = 0)? 0: parseFloat(theForm.frm_fill_opacity.value);
		drawCircle(lat, lng, radius, add_hash(strokeColor), strokeWidth, strokeOpacity, add_hash(fillColor), fillOpacity);	// 210

		}
	}

function is_ok_radius (instr) {
	if(instr.trim() == "") 								{return false;}
	if(instr.trim() == "0.0") 							{return false;}
	instr_ary = instr.split(".");
	if ((instr_ary.length)>2)							{return false;}
	if (instr_ary[0].NaN) 								{return false;}
	if (((instr_ary.length)==2) && (instr_ary[1].NaN))	{return false;}
	return true;
	}
<?php
if (!(empty($_POST))) {
?>
	function do_display(the_type) {
		theForm  = (document.u)? document.u : document.c ;
		switch (the_type) {
		case "p":				// poly
			points.length = 0;							// build points array
			for (i = 0; i < markers.length; i++) {
				points.push(markers[i].getPosition());
				}

			strokeColor = theForm.frm_line_color.value;
			strokeWidth = parseInt(theForm.frm_line_width.value);
			strokeOpacity = parseFloat(theForm.frm_line_opacity.value);
			fillColor = theForm.frm_fill_color.value;
			fillOpacity = parseFloat(theForm.frm_fill_opacity.value);

			if (theForm.frm_filled.value == 1) {		 // Polygon mode - filled
				points.push(markers[0].getPosition());
				var poly = new google.maps.Polygon({
					paths: 			points,
					strokeColor: 	add_hash(strokeColor),
					strokeOpacity: 	strokeOpacity,
					strokeWeight: 	strokeWidth,
					fillColor: 		add_hash(fillColor),
					fillOpacity: 	fillOpacity
					});				
				}
			 else {		 								// Polyline mode - no fill
				var poly = new google.maps.Polygon({
					paths: 			points,
					strokeColor: 	add_hash(strokeColor),
					strokeOpacity: 	strokeOpacity,
					strokeWeight: 	strokeWidth
					});					
				}
			polyline.setMap(map);	
			break;					// end poly
		
		case "c":				// circle
			theForm  = (document.u)? document.u : document.c ;
			var temp = theForm.frm_line_data.value;
			var temp = theForm.frm_line_data.value.split(semic);
			var coords = temp[0].split(comma);
			var lat = parseFloat(coords[0]);
			var lng = parseFloat(coords[1]);
			var radius = parseFloat(theForm.circ_radius.value);
			strokeColor = theForm.frm_line_color.value;
			strokeWidth = parseInt(theForm.frm_line_width.value);
			strokeOpacity = parseFloat(theForm.frm_line_opacity.value);
			fillColor = (theForm.frm_filled.value = 0)? 0: theForm.frm_fill_color.value;
			fillOpacity = (theForm.frm_filled.value = 0)? 0: parseFloat(theForm.frm_fill_opacity.value);
			drawCircle(lat, lng, radius, add_hash(strokeColor), strokeWidth, strokeOpacity, add_hash(fillColor), fillOpacity);	// 324	
			break;		
		
		case "t":				// text
			var theForm = document.c;
			var html = text = theForm.text_text.value.trim();
			drawBanner(points[0],html,text) ;		
			break;		
			}		// end switch()
		}		// end function do_display() 

	function JSfnCheckInput(myform, mybutton, test) {		// reject empty form elements
		var errmsg = "";
		if (myform.frm_name.value.trim()=="") 			{errmsg+= "\tDescription is required\n";}
		if (myform.frm_ident.value.trim()=="") 			{errmsg+= "\tIdent is required\n";}
		if (myform.frm_line_cat_id.value ==0) 			{errmsg+= "\tCategory selection is required\n";}
		if (!(points.length>1))							{errmsg+= "\tAt least two map points are required\n";}
		if (myform.frm_line_color.value.trim()=="") 	{errmsg+= "\tColor is required\n";}
		if (myform.frm_line_opacity.value.trim()=="") 	{errmsg+= "\tOpacity is required\n";}
		if (myform.frm_line_width.value.trim()=="") 	{errmsg+= "\tWidth is required\n";}
		if (!((myform.box_use_with_bm.checked) ||
			(myform.box_use_with_r.checked) ||
			(myform.box_use_with_f.checked) ||
			(myform.box_use_with_u_ex.checked) ||			
			(myform.box_use_with_u_rf.checked) ))		 	{errmsg+= "\tAt least one 'Apply to ...' is required\n";}
	
		if (errmsg!="") {
			$(mybutton).disabled = false;
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else { // test? 
			if (!(typeof test == 'undefined' )) {		// display for review/approval 
				fillmap(); 
				return; 
				}

			myform.frm_use_with_bm.value=(myform.box_use_with_bm.checked)? 1: 0;
			myform.frm_use_with_r.value=(myform.box_use_with_r.checked)? 1: 0;
			myform.frm_use_with_f.value=(myform.box_use_with_f.checked)? 1: 0;
			myform.frm_use_with_u_ex.value=(myform.box_use_with_u_ex.checked)? 1: 0;
			myform.frm_use_with_u_rf.value=(myform.box_use_with_u_rf.checked)? 1: 0;
			
			var comma = ","; 
			var semic = ";"; 
			myform.frm_line_data.value = sep = ""; 
			for (i=0; i<points.length; i++ ) {
				myform.frm_line_data.value += sep + points[i].lat().toFixed(6) + comma +  points[i].lng().toFixed(6); 
				sep = semic;
				}
			myform.submit(); 
			}			// end if/else errormsg 
		}		// end function JSfnCheckInput


<?php
	}			// end if (!(empty($_POST)))
?>	

function to_string (in_array) {
	var sep = "";					// separator
	var out_str = "";
	for (i=0;i<in_array.length;i++) {
		temp = in_array[i].join(",");		//  comma-separate the coords
		out_str += (sep + temp);
		sep="\t";							// tab-separate the points
		}
	}

function toggleMode() {
	if(markers.length > 1) drawOverlay();
	}

function drawOverlay(){				// edit function - input is markers array
	var lineMode = true;	 											// Check mode
	if (poly) { poly.setMap(null); }
	points.length = 0;
	for (i = 0; i < markers.length; i++) {
		points.push(markers[i].getPosition());
		}
	if (lineMode) {		 // Polyline mode
		poly = new google.maps.Polygon({
			paths: 			points,
			strokeColor: 	"#ff0000",
			strokeOpacity: 	.9,
			strokeWeight: 	2
			});	
		}
	 else {		 // Polygon mode
		points.push(markers[0].getPosition());
		poly = new google.maps.Polygon({
			paths: 			points,
			strokeColor: 	"#ff0000",
			strokeOpacity: 	.9,
			strokeWeight: 	2,
			fillColor: 		"#ff0000",
			fillOpacity: 	.2
			});		
		}
	poly.setMap(map);
	}

function clearMap() { // Clear current map and reset globals
	map.clearOverlays();
	points.length = 0;
	markers.length = 0;
	count = 0;
	}

function to_view(id) {						// invoke switch case 'u' for selected id
	document.to_view_form.id.value = id;
	document.to_view_form.submit();
	}

function buildMap_l() {				// 'list' version	
	var container = document.getElementById("map_canvas");
	var myLatlng = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
	var mapOptions = {
		zoom: <?php print get_variable('def_zoom');?>,
		center: myLatlng,
		panControl: true,
		zoomControl: true,
		scaleControl: true,
		mapTypeId: google.maps.MapTypeId.<?php echo get_maptype_str(); ?>
		}	

	map = new google.maps.Map($('map_canvas'), mapOptions);				// 481
	map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
	var bounds = new google.maps.LatLngBounds();		// Initialize bounds for the map
	var points = new Array();
<?php
	$query = "SELECT * FROM `{$tablename}`";
	$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
	$empty = TRUE;
//
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$empty = FALSE;
		extract ($row);
		$name = $row['line_name'];
		$use_w_bm = ($use_with_bm==1) ? "CHECKED" : "";	// checkbox settings
		$use_w_r = ($use_with_r==1) ? "CHECKED" : "";
		$use_w_f = ($use_with_f==1) ? "CHECKED" : "";
		$use_w_u_ex = ($use_with_u_ex==1) ? "CHECKED" : "";
		$use_w_u_rf = ($use_with_u_rf==1) ? "CHECKED" : "";
		
		switch ($row['line_type']) {
			case "p":		// poly
				$points = explode (";", $line_data);
				for ($i = 0; $i<count($points); $i++) {		//
					$coords = explode (",", $points[$i]);
?>
					var thepoint = new google.maps.LatLng(<?php echo round ( $coords[0], 6);?>, <?php echo round ( $coords[1], 6);?>); 
					bounds.extend(thepoint);
					points.push(thepoint);		
<?php					}			// end for ($i = 0 ... )

			if ((intval($filled) == 1) && (count($points) > 2)) {?>
					var polyline = new google.maps.Polyline({		// Create the polyline
						paths: points,
						map: map,
						strokeColor: 	add_hash("<?php echo $line_color;?>"),
						strokeOpacity: 	<?php echo $line_opacity;?>,
						strokeWeight: 	<?php echo $line_width;?>,
						fillColor: 		add_hash("<?php echo $fill_color;?>"),
						fillOpacity: 	<?php echo $fill_opacity;?>
						});
<?php			} else {?>
					var polygon = new google.maps.Polygon({		// Create the polygon
						paths: points,
						map: map,
						strokeColor: add_hash("<?php print $line_color;?>"),
						strokeOpacity: <?php print $line_opacity;?>,
						strokeWeight: <?php print $line_width;?>
						});
<?php			} 
				break;
		
			case "c":		// circle
				$temp = explode (";", $line_data);
				$radius = $temp[1];
				$coords = explode (",", $temp[0]);
				$lat = $coords[0];
				$lng = $coords[1];
				$fill_opacity = (intval($filled) == 0)?  0 : $fill_opacity;
				echo "\n drawCircle({$lat}, {$lng}, {$radius}, add_hash('{$line_color}'), {$line_width}, {$line_opacity}, add_hash('{$fill_color}'), {$fill_opacity}); //  472\n";
				break;
		
			case "t":		// banner
				$temp = explode (";", $line_data);
				$banner = $temp[1];
				$coords = explode (",", $temp[0]);
				echo "\n var point = new google.maps.LatLng(parseFloat({$coords[0]}) , parseFloat({$coords[1]}));\n";
				$the_banner = htmlentities($banner, ENT_QUOTES);
				$the_width = intval( trim($line_width), 10);		// font size
				echo "\n drawBanner( point, '{$the_banner}', '{$the_banner}', {$the_width});\n";
				break;
		
			}	// end switch
			
		}			// end while ()
	
	unset($query, $result);
	$dzf = get_variable('def_zoom_fixed');
	print "\tvar map_is_fixed = ";
	print (((my_is_int($dzf)) && ($dzf==2)) || ((my_is_int($dzf)) && ($dzf==3)))? "true;\n":"false;\n";
?>
	if (!(map_is_fixed)) {
		if (points.length==0) {		// any? - 6/18/12
			map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			map.fitBounds(bounds);					// Now fit the map to the bounds  - ({Z:{b:33.7489954, d:49.3844788492429}, ca:{b:-97.23322530034568, d:-76.612189}})
			var listener = google.maps.event.addListenerOnce (map, "idle", function() { 
				if (map.getZoom() > 16) map.setZoom(15); 
				});			
			}
		}
	}				// end function buildMap l()

<?php
	$func = (has_admin())? "u": "v";
?>
function to_p(the_id) {		// poly
	document.navform.id.value = the_id;
	document.navform._func.value = "<?php echo $func;?>";
	document.navform.action = "<?php echo basename(__FILE__);?>";
	document.navform.submit();

	}
function to_c(the_id, the_func) {		// circle
	document.navform.id.value = the_id;
	document.navform._func.value = the_func;
	document.navform.action = "circle.php";
	document.navform.submit();
	}

function to_t(the_id, the_func) {		// text/banner
	document.navform.id.value = the_id;
	document.navform._func.value = the_func;
	document.navform.action = "banner.php";
	document.navform.submit();
	}

function to_k(the_id) {		// kml
	document.navform.id.value = the_id;
	document.navform._func.value = "<?php echo $func;?>";
	document.navform.action = "kml.php";
	document.navform.submit();
	}

</SCRIPT>
</HEAD>
<?php

switch ($_func) {

	case "l":				// list
?>
<BODY onLoad = "buildMap_l()">			<!-- <?php echo basename(__FILE__); ?> -->
<SCRIPT TYPE='text/javascript' src='./js/wz_tooltip.js'></SCRIPT>
<TABLE ID = 'outer' ALIGN='center' BORDER = 0 STYLE = 'margin-left:20px;margin-top:20px;'>
<TR CLASS='even'><TH colspan=2>Map Markup</TH></TR>
<TR VALIGN='top'><TD>
<TABLE ALIGN='center' ID = 'sidebar_tbl'>

<?php
	$line_types =  array("p" => "Poly", "c" =>"Circle", "t" =>"Banner");
	$query 	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `{$tablename}`";				// 1/27/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)==0) {		
		print "<TR CLASS = 'odd'><TH COLSPAN=99>No data</TH></TR>\n";
		}
	else {	
		print "<TR STYLE = 'height:8px;'><TD COLSPAN=99 ALIGN='center'><I>Click to view/edit</I></TD></TR>";
		print "<TR CLASS = 'odd'  STYLE = 'height:16px;'><TD ALIGN='left'><B>&nbsp;Name</B></TD>
			<TD><B>Type&nbsp;</B></TD>
			<TD><B>&nbsp;Visible&nbsp;</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to base map');\"><B>&nbsp;BM&nbsp;</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to regions');\"><B>&nbsp;R&nbsp;</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to facilities');\"><B>&nbsp;F&nbsp;</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to units - Exclusion zone');\"><B>&nbsp;EX&nbsp;</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to units - Ringfence');\"><B>&nbsp;RF&nbsp;</B></TD>			
			<TD><B>&nbsp;&nbsp;As of</B></TD></TR>\n";

		$i = 0;
		$targets = array( "p" =>"to_p",	"c" => "to_c", "t" => "to_t",  "k" => "to_k");
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			extract ($row);
			$visible = (intval($row['line_status'])==0)? "<IMG SRC = './markers/checked.png' BORDER=0 />" : "";

			$to_func = "{$targets[$row['line_type']]}({$row['id']}, \"{$func}\")";
			print "<TR CLASS = '{$evenodd[$i%2]} front ' onClick = '{$to_func}'>
				<TD ALIGN='left'>{$row['line_name']}&nbsp;&nbsp;</TD>
				<TD ALIGN='left'>{$line_types[$row['line_type']]}</TD>
				<TD ALIGN='center'>{$visible}</TD>
				<TD ALIGN='center'>{$use_with_bm}</TD>
				<TD ALIGN='center'>{$use_with_r}</TD>
				<TD ALIGN='center'>{$use_with_f}</TD>
				<TD ALIGN='center'>{$use_with_u_ex}</TD>
				<TD ALIGN='center'>{$use_with_u_rf}</TD>				
				<TD ALIGN='right'>&nbsp;" . format_date($row['_on']) . "</TD></TR>\n";
			$i++;
			}
		}		// end if/else (mysql_num_rows($result)==0)
?>		
		<TR CLASS = 'odd'><TD COLSPAN=99 ALIGN='center'  STYLE = 'white-space:nowrap;'><BR />	
		  	<INPUT TYPE="button" VALUE="Add new &raquo;" STYLE = 'border:none; background-color:transparent;'>
		  	<INPUT TYPE = 'button' VALUE = "Polygon" onClick = "document.new_form._type.value='p'; document.new_form.submit();"/>
		  	<INPUT TYPE = 'button' VALUE = "Circle" onClick = "to_c('', 'c');"/>	<!-- id, func -->
		  	<INPUT TYPE = 'button' VALUE = "Banner" onClick = "to_t('', 'c');"/>
		  	</TD>
			</TR></TABLE>
			</TD>
			<TD  ID = 'map_td'>
			<DIV id="map_canvas" STYLE = "margin-left:8px; width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px;" ></DIV>			
			</TD>
			</TR></TABLE>
	<FORM NAME = 'new_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
	<INPUT TYPE= 'hidden' NAME = '_func' VALUE = 'c'>
	<INPUT TYPE= 'hidden' NAME = '_type' VALUE = ''>
	</FORM>

	<FORM NAME = 'to_view_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
	<INPUT TYPE= 'hidden' NAME = '_func' VALUE = 'r'>
	<INPUT TYPE= 'hidden' NAME = 'id' VALUE = ''>
	</FORM>

<?php
	    break;
	case "c":			// create 
?>
<SCRIPT>
function buildMap_c() {															// 'create' version - 691
	var container = document.getElementById("map_canvas");
	var myLatlng = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
	var mapOptions = {
		zoom: <?php print get_variable('def_zoom');?>,
		center: myLatlng,
		panControl: true,
		zoomControl: true,
		scaleControl: true,
		mapTypeId: google.maps.MapTypeId.<?php echo get_maptype_str(); ?>
		}	

	map = new google.maps.Map($('map_canvas'), mapOptions);				// 705
	var bounds = new google.maps.LatLngBounds();		// Initialize bounds for the map	
	
	google.maps.event.addListener(map, "click", function(event) {	
		var thePos = event.latLng;
		var thelat = thePos.lat().toFixed(6);
		var thelng = thePos.lng().toFixed(6);
<?php 
		if ($_func == "r") {	
			echo "\n\t\t return;\n";		// bypass click handling
			} else {
?>
			if(thePos) {
				count++;
													// Make markers draggable
				var marker = new google.maps.Marker({
					position:thePos, 
					icon:base_icon, 
					draggable:true, 
					bouncy:false, 
					dragCrossMove:true,
					title: "Point " + count,
					map: map
					});
				marker.setMap(map);		// add marker with icon		
				marker.content = count;
				markers.push(marker);

				drawOverlay();
				}
<?php	
			}	
?>
		});		// end google.maps.event.add Listener()
	}				// end function buildMap c()

</SCRIPT>
<BODY onLoad = "buildMap_c(); do_un_checked(document.c); document.c.frm_name.focus();">	
<?php
	print (array_key_exists("caption", $_POST))? "<H3>{$_POST['caption']}</H3>" : "";

	$type_ary = array( "p" =>"Polygon",					"c" => "Circle", "t" => "Banner", "k" => "kml");
	$capt_ary = array( "p" =>"click map - drag icons",	"c" => "Click map and enter form values", "t" => "Click map and enter form values",  "k" => "kml");
	$line_ary = array( "p" =>"Line", 					"c" =>"Circle", "t" =>"Banner", "k" => "kml");

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` ORDER BY `category` ASC";		
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$cats_sel = "<SELECT NAME = 'frm_cat_list' onChange = 'this.form.frm_line_cat_id.value = this.options[this.selectedIndex].value;'>\n";
	$cats_sel .= "<OPTION VALUE=0 SELECTED >Select</OPTION>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$cats_sel .= "<OPTION VALUE=\"{$row['id']}\">" . shorten($row['category'], 30) . "</OPTION>\n";
		}
   $cats_sel .= "</SELECT>\n";

?>	
		<FORM NAME="c" METHOD="post" ACTION="<?php print basename(__FILE__); ?>">		
	
		<TABLE BORDER="0" ALIGN="center" ID = 'outer'  STYLE = 'margin-left:20px;margin-top:20px;'><TR VALIGN='top'><TD>
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top" >
			<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">New <?php echo $type_ary[$_POST['_type']];?></FONT><BR /><BR />
			<FONT SIZE = 'normal'><EM><?php echo $capt_ary[$_POST['_type']];?></EM></FONT></TD>
			</TR>
		<TR CLASS="odd" VALIGN="top" >
			<TD COLSPAN="2" ALIGN="CENTER">&nbsp;</TD>
			</TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="left">Description:</TD>
			<TD><INPUT MAXLENGTH="32" SIZE="32" type="text" NAME="frm_name" VALUE="" onChange = "this.value.trim();" />
				<SPAN STYLE = 'margin-left:20px' CLASS="td_label" >Visible&nbsp;&raquo;&nbsp;</SPAN>
				<SPAN STYLE = 'margin-left:10px'>Yes&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'rb_line_is_vis' onClick = "document.c.rb_line_not_vis.checked = false;document.c.frm_line_status.value=0" CHECKED /></SPAN>
				<SPAN STYLE = 'margin-left:20px'>No&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'rb_line_not_vis' onClick = "document.c.rb_line_is_vis.checked = false;document.c.frm_line_status.value=1" /></SPAN>
			
			</TD></TR>

		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="left">Ident:</TD>
			<TD ALIGN="left"><INPUT MAXLENGTH="10" SIZE="10" type="text" NAME="frm_ident" VALUE="" onChange = "this.value.trim();" />
				<SPAN STYLE = 'margin-left:20px'  CLASS="td_label">Category:&nbsp;&raquo;&nbsp;</SPAN><?php echo $cats_sel;?></TD>
			</TR>


		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="left">Apply to:</TD>
			<TD ALIGN='left' CLASS="td_label"  STYLE = 'white-space:nowrap;' >
				<SPAN STYLE="margin-left: 20px;border:1px; width:20%">Base Map&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" NAME="box_use_with_bm" onClick = "this.form.frm_use_with_bm.value=1"/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;<?php print get_text("Regions");?>&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" NAME="box_use_with_r"  onClick = 	"this.form.frm_use_with_r.value=1"/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Facilities&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" NAME="box_use_with_f"  onClick = "this.form.frm_use_with_f.value=1"/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Unit Exclusion Zone&nbsp;&raquo;&nbsp;<INPUT  TYPE= "checkbox" NAME="box_use_with_u_ex"  onClick = "this.form.frm_use_with_u_ex.value=1"/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Unit Ringfence&nbsp;&raquo;&nbsp;<INPUT  TYPE= "checkbox" NAME="box_use_with_u_rf"  onClick = "this.form.frm_use_with_u_rf.value=1"/></SPAN>
				</TD>
			</TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="left"><?php echo $line_ary[$_POST['_type']];?>:</TD>
				<TD ALIGN="left"><SPAN CLASS="td_label" STYLE= "margin-left:20px" >
					Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_line_color" VALUE="#FF0000"  class="color" />&nbsp;&nbsp;&nbsp;&nbsp;
					Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_line_opacity" VALUE="0.5" />&nbsp;&nbsp;&nbsp;&nbsp;
					Width &raquo;&nbsp;<INPUT MAXLENGTH=2 SIZE=2 TYPE= "text" NAME="frm_line_width" VALUE="2" /> (px)
					</SPAN></TD>
			</TR>

		<TR VALIGN="baseline" CLASS="odd" ID = 'fill_cb_tr'  >
			<TD CLASS="td_label" ALIGN="left">Filled:&nbsp;&nbsp;&nbsp;</TD>
			<TD ALIGN="left"><SPAN CLASS="td_label" STYLE = "margin-left: 20px;" >
				No&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_n' value = 'n'	onClick = 'do_un_checked(this.form)' CHECKED  />&nbsp;&nbsp;&nbsp;&nbsp;
				Yes&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_y' value = 'y'  onClick = 'do_checked(this.form);'/>				
				</SPAN></TD>
			</TR>
		<TR VALIGN="baseline" CLASS="even" ID = 'fill_tr' STYLE = 'display:none'>
			<TD CLASS="td_label" ALIGN="left">Fill:</TD>
			<TD ALIGN="left"><SPAN CLASS="td_label" STYLE= "margin-left:20px" >
					Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_fill_color" VALUE="#FF0000"  class="color" />&nbsp;&nbsp;&nbsp;&nbsp;
					Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_fill_opacity" VALUE="0.5" />&nbsp;&nbsp;&nbsp;&nbsp;
					</SPAN>
					</TD>
			</TR>

		<TR  VALIGN="baseline"CLASS="odd"><TD COLSPAN="2" ALIGN="center" STYLE = 'white-space:nowrap;'>
			<INPUT TYPE='hidden' NAME = '_func' VALUE='cp' />
			<INPUT TYPE='hidden' NAME = 'frm_line_status' VALUE='0' />	
			<INPUT TYPE='hidden' NAME = 'frm_line_cat_id' VALUE='0' />	
			<INPUT TYPE='hidden' NAME = 'frm_line_type' VALUE='<?php echo $_POST['_type'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_line_data' VALUE='' />
			<INPUT TYPE='hidden' NAME = 'frm_filled' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_bm' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_r' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_f' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_u_ex' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_u_rf' VALUE='0' />
			<INPUT TYPE="button" VALUE="Cancel" STYLE = 'width:auto;'  onClick = "location.href='<?php echo basename(__FILE__);?>'"/>
			<INPUT TYPE="button" VALUE="Reset"  STYLE = 'width:auto; margin-left:40px;' onClick = "do_un_checked();this.form.reset(); clearMap();buildMap_c();"/>
			<INPUT TYPE="button" NAME="sub_but" VALUE="Next" STYLE = 'width:120px; margin-left:40px;' onclick="this.disabled=true; JSfnCheckInput(this.form, this);"/> 			
			</TD></TR>
			<TR><TD COLSPAN=3>&nbsp;</TD></TR>
			</FORM>
		</TD></TR></TABLE>
		</TD><TD>
			<DIV id="map_canvas" STYLE = "margin-left:8px; width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px;" ></DIV>
			</TD></TR></TABLE>
		
<CENTER>

<?php
	    break;				// end case "c"
	    
	case "cp":				// 'create' process
//		dump($_POST);
		$filled =		(trim($_POST['frm_line_type']) == "t")?	"NULL" : quote_smart(trim($_POST['frm_filled'])) ; 
		$fill_color =	(trim($_POST['frm_line_type']) == "t")?	"NULL" : quote_smart(trim($_POST['frm_fill_color'])) ; 
		$fill_opacity =	(trim($_POST['frm_line_type']) == "t")?	"NULL" : quote_smart(trim($_POST['frm_fill_opacity'])) ; 
//		dump($fill_opacity);
		$query = "INSERT INTO `{$tablename}` (`line_name`, `line_ident`, `line_cat_id`, `line_status`, `line_type`, `line_data`, `use_with_bm`, `use_with_r`, `use_with_f`, `use_with_u_ex`, `use_with_u_rf`, `line_color`, `line_opacity`, `filled`, `fill_color`, `fill_opacity`,`line_width`,
		`_by`, `_from`, `_on`) 
			VALUES (" .
			 quote_smart(trim($_POST['frm_name'])) ."," .
			 quote_smart(trim($_POST['frm_ident'])) ."," .
			 quote_smart(trim($_POST['frm_line_cat_id'])) ."," .
			 quote_smart(trim($_POST['frm_line_status'])) ."," .
			 quote_smart(trim($_POST['frm_line_type'])) ."," .
			 quote_smart(trim($_POST['frm_line_data'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_bm'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_r'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_f'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_u_ex'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_u_rf'])) ."," .			 
			 quote_smart(trim($_POST['frm_line_color'])) ."," .
			 quote_smart(trim($_POST['frm_line_opacity'])) ."," .
			 $filled ."," .
			 $fill_color ."," .
			 $fill_opacity ."," .
			 quote_smart(trim($_POST['frm_line_width'])) ."," .
			 quote_smart($by) ."," .
			 quote_smart($from) ."," .
			 quote_smart(trim($now)) . ")" ;

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$insert_id = mysql_insert_id();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML><HEAD><TITLE><?php echo basename(__FILE__);?></TITLE></HEAD>
<BODY onload = 'document.dummy.submit();'>
<FORM NAME='dummy' METHOD = 'post' ACTION = '<?php echo basename(__FILE__);?>'>
<INPUT TYPE = 'hidden' NAME = '_func' VALUE = 'r' />
<INPUT TYPE = 'hidden' NAME = 'id' VALUE = '<?php echo $insert_id;?>' />
</FORM></BODY></HTML>

<?php
		break;			// end case "cp"
	
	case "u":
	case "r":				// similar - use common structure
	
		$dis = ($_func == "r") ? "DISABLED" : "";
		$capt = ($_func == "r") ? "View" : "Revise";
?>			
<SCRIPT>
	function do_delete(id_val) {
		if (confirm("Really, really DELETE this?")) {
			document.navform._func.value="dp";
			document.navform.id.value=id_val;
			document.navform.submit();
			}
		else {
			return false;
			}
		}		// end function do delete()

	function add_marker( point) {
		semic = ";";			// separator
		count++;
<?php $do_drag = ($_func == "r")? "false": "true"; ?>
										  				// Make markers draggable?
		var marker = new google.maps.Marker({
			position: point,
			icon: base_icon, 
			draggable: <?php echo $do_drag;?>,
			bouncy: false,
			dragCrossMove:true,
			title: "Point " + count,
			map: map
			});
		marker.setMap(map);
		marker.content = count;
		markers.push(marker);
<?php if ($_func != "r") {	?>
		google.maps.event.addListener(marker, "drag", function() {		
			 drawOverlay();
			});
		
		google.maps.event.addListener(marker, "click", function() {		
			for(var n = 0; n < markers.length; n++) {	  // Find out which marker to remove
				if(markers[n] == marker) {
					marker[n].setMap(null);			 
					break;
					}
				}
		
			markers.splice(n, 1);	  						// Shorten array of markers and adjust counter
			if(markers.length == 0) {
				count = 0;
				}
			else {
				count = markers[markers.length-1].content;
			  	drawOverlay();
				}
			});
<?php }	?>			
		drawOverlay();
		}				// end function add marker()

	function buildMap_r() {				// 'view' version
		var container = document.getElementById("map_canvas");
		var myLatlng = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
		var mapOptions = {
			zoom: <?php print get_variable('def_zoom');?>,
			center: myLatlng,
			panControl: true,
			zoomControl: true,
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.<?php echo get_maptype_str(); ?>
			}	

		map = new google.maps.Map($('map_canvas'), mapOptions);				// 481
		map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		var bounds = new google.maps.LatLngBounds();		// Initialize bounds for the map	
		var points = new Array();
	
<?php
			$query = "SELECT * FROM `{$tablename}` WHERE `id`='{$_POST['id']}' LIMIT 1";
			$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			extract ($row);
			$name = $row['line_name'];

			$points = explode (";", $line_data);
			echo "\n\tvar points = new Array();\n";
		
			for ($i = 0; $i<count($points); $i++) {
				$coords = explode (",", $points[$i]);
?>
				var thepoint = new google.maps.LatLng(<?php print $coords[0];?>, <?php print $coords[1];?>);
				bounds.extend(thepoint);
				points.push(thepoint);
		
<?php					}			// end for ($i = 0 ... )
			 	if ((intval($filled) == 1) && (count($points) > 2)) {?>
					var polyline = new google.maps.Polygon({
						paths: 			 points,
						strokeColor: 	 add_hash("<?php echo $line_color;?>"),
						strokeOpacity: 	 <?php echo $line_opacity;?>,
						strokeWeight: 	 <?php echo $line_width;?>,
						fillColor: 		 add_hash("<?php echo $fill_color;?>"),
						fillOpacity: 	 <?php echo $fill_opacity;?>
						});
<?php				} else {?>
					var polyline = new google.maps.Polygon({
						paths: 			points,
						strokeColor: 	add_hash("<?php echo $line_color;?>"),
						strokeOpacity: 	<?php echo $line_opacity;?>,
						strokeWeight: 	<?php echo $line_width;?>
						});
<?php				} ?>				        
				polyline.setMap(map);	
		map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo (get_variable('def_zoom')-4); ?>);
		}				// end function buildMap r()

	function fillmap() {
		var bounds = new google.maps.LatLngBounds();		// Initialize bounds for the map	
<?php
		for ($i = 0; $i< count($points_ary); $i++) {
			$temp = explode(",", $points_ary[$i]);
?>
			var thepoint = new google.maps.LatLng(<?php echo $temp[0];?>, <?php echo $temp[1];?>);
			bounds.extend(thepoint);
			do_point(<?php echo $temp[0];?>, <?php echo $temp[1];?>);
<?php	
			}		// end for ($i ... )
?>
		center = bounds.getCenter();
		zoom = bounds.getCenter();
		map.setCenter(center);
		map.setZoom(zoom);
		}				// end function fillmap()


	function buildMap_u() {				// 'update' version
		var myLatlng = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
		var mapOptions = {
			zoom: <?php print get_variable('def_zoom');?>,
			center: myLatlng,
			panControl: true,
			zoomControl: true,
			scaleControl: true,
			draggableCursor:"auto",
			draggingCursor:"move",			
			mapTypeId: google.maps.MapTypeId.<?php echo get_maptype_str(); ?>
			}	

		map = new google.maps.Map($('map_canvas'), mapOptions);				// 705		
							// Add dummy listener for the click event
		google.maps.event.addListener(map, "click", function() {	
			});		// end google.maps.event.add Listener()
		}				// end function buildMap u()
		
	function do_point(in_lat, in_lng) {
		var point = new google.maps.LatLng( in_lat, in_lng);
		count++;
<?php 
		if ($_func != "r") {
			}	
		$do_drag = ($_func == "r")? "false": "true"; 
 ?>
		var marker = new google.maps.Marker({
			position: point,
			icon: base_icon, 
			draggable: <?php echo $do_drag;?>,
			bouncy: false,
			dragCrossMove:true,
			title: "Point " + count,
			map: map
			});
		marker.setMap(map); 
		marker.content = count;
		markers.push(marker);
<?php 
		if ($_func != "r") {	
?>
			google.maps.event.addListener(marker, "click", function() {		
			});

			google.maps.event.addListener(marker, 'dragend', function(event) {
				var thePos = event.latLng;
				var thelat = thePos.lat().toFixed(6);
				var thelng = thePos.lng().toFixed(6);
			
				for(var n = 0; n < markers.length; n++) {		// Find out which marker to remove
					if(markers[n] == marker) {
						if (thePos) {
							drawOverlay();
							}		// end if (thePos)
						else {
							alert(<?php echo __LINE__;?>);
							}					
						break;
						}
					}
				});		// end google.maps.event.add Listener()
<?php
			}
?>
		drawOverlay();
		}				// end function do_point()

	function toggle(the_value) {
		return (the_value==0)? 1 : 0 ;
		}
	
</SCRIPT>

<?php
if ($_func == "r") {
?>
<BODY onLoad = "buildMap_r(); fillmap(); document.u.frm_name.focus();"'>
<?php
	}
else {
?>
<BODY onLoad = "buildMap_u(); fillmap(); document.u.frm_name.focus();"'>	
<?php	
	}
	$visible_true = (intval($row['line_status'])==0)? "CHECKED" : "";
	$visible_false = ($visible_true)? "" : "CHECKED";

//	dump ($row);

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` ORDER BY `category` ASC";		
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$cats_sel = "<SELECT NAME = 'frm_cat_list' onChange = 'this.form.frm_line_cat_id.value = this.options[this.selectedIndex].value;' {$dis}>\n";
	while ($row_cat = mysql_fetch_assoc($result)) {
		$sel = ($row_cat['id']== $line_cat_id)? "SELECTED": "";
		$cats_sel .= "<OPTION VALUE=\"{$row_cat['id']}\" {$sel}>" . shorten($row_cat['category'], 30) . "</OPTION>\n";
		}
   $cats_sel .= "</SELECT>\n";

?>
		<FORM NAME="u" METHOD="post" ACTION="<?php print basename(__FILE__); ?>">		
	
		<TABLE BORDER="0" ALIGN="left" ID = 'outer'  STYLE = 'margin-left:20px;margin-top:20px;'><TR VALIGN='top'><TD>
		<TABLE BORDER="0" ALIGN="left" STYLE = 'white-space:nowrap; verticalAlign:bottom;'>
		<TR CLASS="even">
			<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1"><?php print $capt;?> '<?php print $name;?>'</FONT></TD>
			</TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR CLASS="odd" VALIGN = baseline >
			<TD CLASS="td_label" ALIGN="left">Description:</TD>
			<TD><INPUT MAXLENGTH="32" SIZE="32" type="text" NAME="frm_name" VALUE="<?php print $row['line_name'];?>" <?php print $dis;?> onChange = "this.value.trim();">
			<SPAN CLASS = 'td_label' STYLE = 'margin-left:20px;'>Visible: <?php if ($_func == "r") {?>
			
							<SPAN STYLE = 'margin-left:10px'>Yes&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'frm_line_is_vis' <?php echo $visible_true;?> DISABLED /></SPAN>
							<SPAN STYLE = 'margin-left:20px'>No&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'frm_line_not_vis' <?php echo $visible_false;?> DISABLED /></SPAN>
			<?php			} else {?>		
							<SPAN STYLE = 'margin-left:10px'>Yes&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'frm_line_is_vis' <?php echo $visible_true;?> onClick = "document.u.frm_line_not_vis.checked = false;document.u.frm_line_status.value=0" /></SPAN>
							<SPAN STYLE = 'margin-left:20px'>No&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'frm_line_not_vis' <?php echo $visible_false;?> onClick = "document.u.frm_line_is_vis.checked = false;document.u.frm_line_status.value=1" /></SPAN>
			<?php }?>	
			</SPAN></TD></TR>

		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="left">Ident:</TD>
			<TD ALIGN="left"><INPUT MAXLENGTH="10" SIZE="10" type="text" NAME="frm_ident" VALUE="<?php echo $line_ident;?>" onChange = "this.value.trim();" <?php echo $dis;?> />
				<SPAN STYLE = 'margin-left:20px'  CLASS="td_label">Category:&nbsp;&raquo;&nbsp;</SPAN><?php echo $cats_sel;?></TD>
			</TR>


		<TR CLASS="odd" VALIGN = baseline ><TD CLASS="td_label" ALIGN="left">Apply to:</TD>
			<TD ALIGN='left' CLASS="td_label" >
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Base Map&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" 	NAME="box_use_with_bm" 	<?php print $use_w_bm;?> onClick = "this.form.frm_use_with_bm.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;<?php print get_text("Regions");?>&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" 		NAME="box_use_with_r"	<?php print $use_w_r;?> onClick = "this.form.frm_use_with_r.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Facilities&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox"	NAME="box_use_with_f"	<?php print $use_w_f;?> onClick = "this.form.frm_use_with_f.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Unit Exclusion Zone&nbsp;&raquo;&nbsp;<INPUT  TYPE= "checkbox" 		NAME="box_use_with_u_ex"	<?php print $use_w_u_ex;?> onClick = "this.form.frm_use_with_u_ex.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Unit Ringfence&nbsp;&raquo;&nbsp;<INPUT  TYPE= "checkbox" 		NAME="box_use_with_u_rf"	<?php print $use_w_u_rf;?> onClick = "this.form.frm_use_with_u_rf.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				</TD>
			</TR>

		<TR CLASS="even" VALIGN = baseline  ><TD CLASS="td_label" ALIGN="left">Line:</TD>
				<TD ALIGN="left"><SPAN CLASS="td_label" >
				Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_line_color" VALUE="<?php print $row['line_color'];?>"  class="color" <?php print $dis;?> />&nbsp;&nbsp;&nbsp;&nbsp;
				Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_line_opacity" VALUE="<?php print $row['line_opacity'];?>" <?php print $dis;?>/>&nbsp;&nbsp;&nbsp;&nbsp;
				Width &raquo;&nbsp;<INPUT MAXLENGTH=2 SIZE=2 TYPE= "text" NAME="frm_line_width" VALUE="<?php print $row['line_width'];?>" <?php print $dis;?> /> (px)
			</SPAN></TD>
			</TR>

<?php
	if (intval($row['filled'])==1) {
		$cb_y_checked = "CHECKED";
		$cb_n_checked = "";
		$tr_display = "";
		}
	else {
		$cb_y_checked = "";
		$cb_n_checked = "CHECKED";
		$tr_display = "none";
	}
?>
		<TR VALIGN="baseline" CLASS="odd" ID = 'fill_cb_tr'  ><TD CLASS="td_label" ALIGN="left">Filled:&nbsp;&nbsp;&nbsp;</TD>
				<TD ALIGN="left"><SPAN CLASS="td_label" STYLE = "margin-left: 20px;" >
				No&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_n' value = 'n' 	onClick = 'do_un_checked(this.form)' <?php echo $cb_n_checked . " " . $dis;?>  />&nbsp;&nbsp;&nbsp;&nbsp;
				Yes&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_y' value = 'y'	onClick = 'do_checked(this.form);'  <?php echo $cb_y_checked . " " . $dis;?> />				
				</SPAN></TD>
			</TR>


		<TR CLASS="even" ID = 'fill_tr'  VALIGN = baseline STYLE = 'display:<?php echo $tr_display;?>' >
			<TD CLASS="td_label" ALIGN="left">Fill: &nbsp; &nbsp; &nbsp;</TD>
			<TD ALIGN="left"><SPAN CLASS="td_label" >
					Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_fill_color" VALUE="<?php print $row['fill_color'];?>"  class="color" <?php print $dis;?> />&nbsp;&nbsp;&nbsp;&nbsp;
					Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_fill_opacity" VALUE="<?php print $row['fill_opacity'];?>" <?php print $dis;?> />&nbsp;&nbsp;&nbsp;&nbsp;
					</SPAN></TD>
			</TR>
		<TR  CLASS="odd"  ><TD COLSPAN="2" ALIGN="center" STYLE = 'white-space:nowrap;'>
			<INPUT TYPE='hidden' NAME = '_func' VALUE='up' />
			<INPUT TYPE='hidden' NAME = 'frm_id' VALUE='<?php print $row['id'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_line_status' VALUE='<?php print $row['line_status'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_line_cat_id' VALUE='<?php print $row['line_cat_id'];?>' />	

			<INPUT TYPE='hidden' NAME = 'frm_line_type' VALUE='<?php print $row['line_type'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_line_data' VALUE='<?php print $row['line_data'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_filled' VALUE='<?php print $row['filled'];?>' />

			<INPUT TYPE='hidden' NAME = 'frm_use_with_bm' VALUE='<?php print $row['use_with_bm'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_r' VALUE='<?php print $row['use_with_r'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_f' VALUE='<?php print $row['use_with_f'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_u_ex' VALUE='<?php print $row['use_with_u_ex'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_u_rf' VALUE='<?php print $row['use_with_u_rf'];?>' />			
			<BR />
<?php if (is_administrator() || is_super()) { ?>	
			<INPUT TYPE="button" VALUE="Delete" STYLE = 'width:auto;'  onClick = "do_delete(<?php echo $row['id'];?>)"/>
<?php	} ?>			
			&nbsp;&nbsp;<INPUT TYPE="button" VALUE="Cancel" STYLE = 'width:auto;'  onClick = "location.href='<?php echo basename(__FILE__);?>'"/>
<?php
		if ($_func == "r") {
?>
			<INPUT TYPE="button" NAME="sub_but" VALUE="Edit" STYLE = 'width:120px; margin-left:20px;' onclick="this.disabled=true; document.navform._func.value='u'; document.navform.submit();"/> 			
<?php
			}
		else {
?>
<!-- 1169 -->
			<INPUT TYPE="button" VALUE="Reset"  STYLE = 'width:auto; margin-left:20px;' onClick = "to_p(<?php echo $id;?>);"/>
			<INPUT TYPE="button" NAME="sub_but" VALUE="Next" STYLE = 'width:100px; margin-left:20px;' onclick=" JSfnCheckInput(this.form, this);" /> 			
<!-- 		<INPUT TYPE="button" VALUE="Test" onClick = "JSfnCheckInput(this.form, this, true);" STYLE = 'margin-left:20px' />  -->
<?php
			}
?>

				</TD></TR>
			<TR ><TD COLSPAN=3>&nbsp;</TD></TR>
			</FORM>
		</TD></TR></TABLE>
		</TD><TD>
			<DIV id="map_canvas" STYLE = "margin-left:8px; width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px;" ></DIV>
			</TD></TR></TABLE>
		
<CENTER>
<?php
	    break;				// end case "u"
	
	case "up":				// process 'update'
		$line_status = (trim($_POST['frm_line_is_vis'])=='on')?  0: 1;

		$query = "UPDATE `{$tablename}` SET 
			`line_name` = " . 		quote_smart(trim($_POST['frm_name'])) .",
			`line_ident` = " . 		quote_smart(trim($_POST['frm_ident'])) .",
			`line_cat_id` = " . 	quote_smart(trim($_POST['frm_line_cat_id'])) .",
			`line_status` = 		'{$line_status}',
			`line_type` = " . 		quote_smart(trim($_POST['frm_line_type'])) .",
			`line_data` = " .  		quote_smart(trim($_POST['frm_line_data'])) .",
			`use_with_bm` = " .  	quote_smart(trim($_POST['frm_use_with_bm'])) .",
			`use_with_r` = " .  	quote_smart(trim($_POST['frm_use_with_r'])) .",
			`use_with_f` = " .  	quote_smart(trim($_POST['frm_use_with_f'])) .",
			`use_with_u_ex` = " .  	quote_smart(trim($_POST['frm_use_with_u_ex'])) .",
			`use_with_u_rf` = " .  	quote_smart(trim($_POST['frm_use_with_u_rf'])) .",			
			`line_color` = " .  	quote_smart(trim($_POST['frm_line_color'])) .",
			`line_opacity` = " .  	quote_smart(trim($_POST['frm_line_opacity'])) .",
			`filled` = " .  		quote_smart(trim($_POST['frm_filled'])) .",
			`fill_color` = " .  	quote_smart(trim($_POST['frm_fill_color'])) .",
			`fill_opacity` = " .  	quote_smart(trim($_POST['frm_fill_opacity'])) .",
			`line_width` = " .  	quote_smart(trim($_POST['frm_line_width'])) .",
			`_by` =   				'{$by}' ,
			`_from` =	 			'{$from}' ,
			`_on` =   				'{$now}'
			WHERE `id` = 			{$_POST['frm_id']}";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
// _______________________________________________

?>
<SCRIPT>
function waiter() {
	document.navform._func.value="r";
	document.navform.id.value=<?php echo $_POST['frm_id'];?>
	setTimeout("document.navform.submit()",1500);
	}
</SCRIPT>
</HEAD>
<BODY onLoad = "waiter();">
<DIV align="center" ID = 'up_id'><BR /><BR /><BR/><H3>'<?php echo $_POST['frm_name'];?>' update complete</H3></DIV>
</BODY>
</HTML>
<?php

	    break;				// end case "up" -  process 'update'
	    
	case "dp":
	
		$query = "SELECT `line_name` FROM `{$tablename}` WHERE `id` = {$_POST['id']} LIMIT 1" ;
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$row = mysql_fetch_assoc($result);
	
		$query = "DELETE FROM `{$tablename}` WHERE `id` = {$_POST['id']} LIMIT 1" ;
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN"><HTML><HEAD><TITLE><?php print basename(__FILE__);?></TITLE>
<SCRIPT>

function waiter() {
	document.navform._func.value="l";					// view the new entry
	setTimeout("document.navform.submit()",1500);
	}
</SCRIPT>
</HEAD>
<BODY onLoad = "waiter();">
<DIV align="center" ID = 'dp_id'><BR /><BR /><BR/><H3>'<?php echo $row['line_name'];?>' deleted</H3></DIV>
</BODY></HTML>
<?php
	break;			// end case "dp"
	    
	default:
		print "ERROR - ERROR - ERROR - ERROR: {$_func} " ;
	    
	}				// end switch()

$the_id = isset($row['id'])? $row['id']: "";
?>
<FORM NAME = 'navform' METHOD = 'post' ACTION = "<?php print basename(__FILE__);?>" TARGET = "_self" >
<INPUT TYPE='hidden' NAME = '_func' VALUE = ''>
<INPUT TYPE='hidden' NAME = 'id' VALUE = '<?php print $the_id;?>'>
</FORM>

</BODY>
</HTML>