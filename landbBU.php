<?php
/*
5/25/11 initial release
7/2/11 corrections to include filled data as hiddens
7/3/11 added 2 fields to schema
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
require_once('incs/functions.inc.php');	
//dump ($mysql_db);
//dump ($_POST);
	$tablename = "$GLOBALS['mysql_prefix']lines";


	$query = "CREATE TABLE IF NOT EXISTS `{$tablename}` (
		  `id` bigint(4) NOT NULL AUTO_INCREMENT,
		  `line_name` varchar(32) NOT NULL,
		  `line_status` int(2) NOT NULL DEFAULT '0' COMMENT '0 => show, 1 => hide',
		  `line_type` int(2) NOT NULL DEFAULT '0' COMMENT 'poly, circle, ellipse',
		  `line_data` varchar(4096) NOT NULL,
		  `use_with_i` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with incidents',
		  `use_with_u` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with units',
		  `use_with_f` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with facilities',
		  `use_with_r` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use with regions',
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
		
		


//do_login(basename(__FILE__));
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
<HEAD><TITLE>Tickets Boundaries Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"/>
<META HTTP-EQUIV="Expires" CONTENT="0"/>
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE"/>
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE"/>
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript"/>
<META HTTP-EQUIV="Script-date" CONTENT="12/15/10 3:55"> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<STYLE>
/* comment */
A:hover 					{text-decoration: underline; color: red;}
TH:hover 					{text-decoration: underline; color: red;}
td.mylink:hover 			{background-color: rgb(255, 255, 255); }
INPUT.button 				{background-color: rgb(255, 255, 255); }
input.text:focus, textarea:focus	{background-color: lightyellow; color:black;}
tr 							{height: 30px; }
tr.front 					{height: 18px; }

</STYLE>
<?php
$_func = (empty($_POST)) ?  "l" : $_POST['_func'];							// list mode as default	
?>
<script type="text/javascript" src="./js/jscolor.js"></script>
<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2.173&amp;sensor=false&amp;key=<?php print get_variable('gmaps_api_key');?>""></SCRIPT>

<SCRIPT>

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
		
var TimeToFade =  8000.0;

function fade(eid) {
	var element = document.getElementById(eid);
	if(element == null)
	return;
	 
	if(element.FadeState == null) {
	if(element.style.opacity == null
		|| element.style.opacity == ''
		|| element.style.opacity == '1'){
		element.FadeState = 2;
		}
	else {
		element.FadeState = -2;
		}
		}
	 
	if(element.FadeState == 1 || element.FadeState == -1){
		element.FadeState = element.FadeState == 1 ? -1 : 1;
		element.FadeTimeLeft = TimeToFade - element.FadeTimeLeft;
		}
	else {
		element.FadeState = element.FadeState == 2 ? -1 : 1;
		element.FadeTimeLeft = TimeToFade;
		setTimeout("animateFade(" + new Date().getTime() + ",'" + eid + "')", 33);
		}
	}			// end function fade() 
	
function animateFade(lastTick, eid) {	
	var curTick = new Date().getTime();
	var elapsedTicks = curTick - lastTick;
 
	var element = document.getElementById(eid);
 
	if(element.FadeTimeLeft <= elapsedTicks) 	{
		element.style.opacity = element.FadeState == 1 ? '1' : '0';
		element.style.filter = 'alpha(opacity = '
			+ (element.FadeState == 1 ? '100' : '0') + ')';
		element.FadeState = element.FadeState == 1 ? 2 : -2;
		return;
		}
 
	element.FadeTimeLeft -= elapsedTicks;
	var newOpVal = element.FadeTimeLeft/TimeToFade;
	if(element.FadeState == 1)
		newOpVal = 1 - newOpVal;

	element.style.opacity = newOpVal;
	element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
 
	setTimeout("animateFade(" + curTick + ",'" + eid + "')", 33);
	}			// end function animateFade()
	


	function JSfnCheckInput(myform, mybutton) {		// reject empty form elements
		var errmsg = "";
		if (myform.frm_name.value.trim()=="") 			{errmsg+= "\tName is required\n";}
		if (!(points.length>1))							{errmsg+= "\tAt least two map points are required\n";}
		if (myform.frm_line_color.value.trim()=="") 	{errmsg+= "\tColor is required\n";}
		if (myform.frm_line_opacity.value.trim()=="") 	{errmsg+= "\tOpacity is required\n";}
		if (myform.frm_line_width.value.trim()=="") 	{errmsg+= "\tWidth is required\n";}
		if (!((myform.box_use_with_i.checked) ||
			(myform.box_use_with_u.checked) ||
			(myform.box_use_with_f.checked) ||
			(myform.box_use_with_r.checked) ))		 	{errmsg+= "\tAt least one 'Apply to ...' is required\n";}
	
		if (errmsg!="") {
			$(mybutton).disabled = false;
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			myform.frm_use_with_i.value=(myform.box_use_with_i.checked)? 1: 0;
			myform.frm_use_with_u.value=(myform.box_use_with_u.checked)? 1: 0;
			myform.frm_use_with_f.value=(myform.box_use_with_f.checked)? 1: 0;
			myform.frm_use_with_r.value=(myform.box_use_with_r.checked)? 1: 0;
			
			var comma = ","; 
			var semic = ";"; 
			myform.frm_line_data.value = sep = ""; 
			for (i=0; i<points.length; i++ ) {
				myform.frm_line_data.value += sep + points[i].lat().toFixed(6) + comma +  points[i].lng().toFixed(6); 
				sep = semic;
				}
			myform.submit(); 
			}			// end if/else errormsg 
		myform.submit();
		}		// end function JSfnCheckInput

var map, poly;					// Global variables
var count = 0;
var points = new Array();
var markers = new Array();
var icon_url ="http://labs.google.com/ridefinder/images/";
var tooltip;
//var report= document.getElementById("status");

function to_string (in_array) {
	var sep = "";					// separator
	var out_str = "";
	for (i=0;i<in_array.length;i++) {
		temp = in_array[i].join(",");		//  comma-separate the coords
		out_str += (sep + temp);
		sep="\t";							// tab-separate the points
		}
	}
function addIcon(icon) { // Add icon properties
	 icon.shadow= icon_url + "mm_20_shadow.png";
	 icon.iconSize = new GSize(12, 20);
	 icon.shadowSize = new GSize(22, 20);
	 icon.iconAnchor = new GPoint(6, 20);
	 icon.infoWindowAnchor = new GPoint(5, 1);
	}

function showTooltip(marker) { // Display tooltips
	 tooltip.innerHTML = marker.tooltip;
	 tooltip.style.display = "block";
	 if(typeof(tooltip.style.filter) == "string") { // Tooltip transparency specially for IE
		 tooltip.style.filter = "alpha(opacity:70)";
		 }
	 var currtype = map.getCurrentMapType().getProjection();
	 var point= currtype.fromLatLngToPixel(map.fromDivPixelToLatLng(new GPoint(0,0),true),map.getZoom());
	 var offset= currtype.fromLatLngToPixel(marker.getLatLng(),map.getZoom());
	 var anchor = marker.getIcon().iconAnchor;
	 var width = marker.getIcon().iconSize.width + 6;
	// var height = tooltip.clientHeight +18;
	 var height = 10;
	 var pos = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(offset.x - point.x - anchor.x + width, offset.y - point.y -anchor.y - height)); 
	 pos.apply(tooltip);
	}


var semic = "";		// pair separator
function leftClick(overlay, point) {

	if(point) {
		semic = ";";			// separator
		count++;
		var icon = new GIcon();	  // Red marker icon
		icon.image = icon_url + "mm_20_red.png";
		addIcon(icon);	 
										  				// Make markers draggable
		var marker = new GMarker(point, {icon:icon, draggable:true, bouncy:false, dragCrossMove:true});
		map.addOverlay(marker);
		marker.content = count;
		markers.push(marker);
		marker.tooltip = "Point "+ count;
		GEvent.addListener(marker, "mouseover", function() {
		 showTooltip(marker);
		});
		GEvent.addListener(marker, "mouseout", function() {
		 tooltip.style.display = "none";
		});
	
		GEvent.addListener(marker, "drag", function() {	  // Drag listener
		 tooltip.style.display= "none";
		 drawOverlay();
		});
		
		GEvent.addListener(marker, "click", function() {  // Click listener to remove a marker
		 tooltip.style.display = "none";
	
		for(var n = 0; n < markers.length; n++) {	  // Find out which marker to remove
		 if(markers[n] == marker) {
		  map.removeOverlay(markers[n]);
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
		drawOverlay();
		}
	}

function toggleMode() {
	 if(markers.length > 1) drawOverlay();
	}

function drawOverlay(){				// edit function - input is markers array

//	var lineMode = document.forms["f"].elements["mode"][0].checked;	// Check radio button
	var lineMode = true;	 											// Check mode
	if (poly) { map.removeOverlay(poly); }
	points.length = 0;
	for (i = 0; i < markers.length; i++) {
		points.push(markers[i].getLatLng());
		}
	if (lineMode) {		 // Polyline mode
		poly = new GPolyline(points, "#ff0000", 2, .9);
		var length = poly.getLength()/1000;
		var unit = " km";
//		report.innerHTML = "Total line length:<br> " + length.toFixed(3) + unit;
		}
	 else {		 // Polygon mode
		points.push(markers[0].getLatLng());
		poly = new GPolygon(points, "#ff0000", 2, .9, "#ff0000", .2);
//		var area = poly.getArea()/(1000*1000);
//		var unit = " km&sup2;";
//		report.innerHTML = "Area of polygon:<br> " + area.toFixed(3) + unit;
		}
	 map.addOverlay(poly);
	}

function clearMap() { // Clear current map and reset globals
	 map.clearOverlays();
	 points.length = 0;
	 markers.length = 0;
	 count = 0;
//	 report.innerHTML = "&nbsp;";
	}

function to_view(id) {						// invoke switch case 'u' for selected id
	document.to_view_form.id.value = id;
	document.to_view_form.submit();
	}
	

	function buildMap_l() {				// 'list' version
	
		var container = document.getElementById("map");
		map = new GMap2(container, {draggableCursor:"auto", draggingCursor:"move"});
		tooltip = document.createElement("DIV"); // Add a DIV element for toolips
		tooltip.className = "tooltip";
		map.getPane(G_MAP_MARKER_PANE).appendChild(tooltip);
		var bounds = new GLatLngBounds();						// create  bounding box for centering		
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
			$use_w_i = ($use_with_i==1) ? "CHECKED" : "";	// checkbox settings
			$use_w_u = ($use_with_u==1) ? "CHECKED" : "";
			$use_w_f = ($use_with_f==1) ? "CHECKED" : "";
			$use_w_r = ($use_with_r==1) ? "CHECKED" : "";

			$points = explode (";", $line_data);
			echo "\n\tvar points = new Array();\n";

			for ($i = 0; $i<count($points); $i++) {
				$coords = explode (",", $points[$i]);
?>
				var thepoint = new GLatLng(<?php print $coords[0];?>, <?php print $coords[1];?>);
				bounds.extend(thepoint);
				points.push(thepoint);

<?php	}			// end for ($i = 0 ... )
	 	if ((intval($filled) == 1) && (count($points) > 2)) {?>
				var polyline = new GPolygon(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>, "<?php print $fill_color;?>", <?php print $fill_opacity;?>);
<?php	} else {?>
		        var polyline = new GPolyline(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>);
<?php			} ?>				        
				map.addOverlay(polyline);
<?php
		}			// end while ()
		
		unset($query, $result);
		if ($empty) {
?>
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
<?php
			}
		else {
?>		
		center = bounds.getCenter();
		zoom = map.getBoundsZoomLevel(bounds) -1;
		map.setCenter(center,zoom);
<?php
			}
?>	
		map.addControl(new GLargeMapControl3D()); 									// Zoom control
		map.addMapType(G_PHYSICAL_MAP);
		var hierarchy = new GHierarchicalMapTypeControl(); 							// Create a hierarchical map type control
		hierarchy.addRelationship(G_SATELLITE_MAP, G_HYBRID_MAP, "Labels", true);	// make Hybrid the Satellite default
		map.addControl(hierarchy);													// add the control to the map
		map.addControl(new GScaleControl());										// Scale bar
//		map.disableDoubleClickZoom();
//		GEvent.addListener(map, "click", leftClick);								// Add click event listener
	
		}				// end function buildMap_l()

</SCRIPT>
</HEAD>
<?php

switch ($_func) {

	case "l":				// list
?>
<BODY onLoad = "buildMap_l()" onUnload="GUnload();">
<SCRIPT TYPE='text/javascript' src='./js/wz_tooltip.js'></SCRIPT>
<TABLE ID = 'outer' ALIGN='center' BORDER = 0 STYLE = 'margin-left:20px;margin-top:20px;'>
<TR CLASS='even'><TH colspan=2>Lines and Boundaries</TH></TR>
<TR VALIGN='top'><TD>
<TABLE ALIGN='center' ID = 'sidebar_tbl'>

<?php
	$query 	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `{$tablename}`";				// 1/27/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)==0) {		
		print "<TR CLASS = 'odd'><TH COLSPAN=99>No data</TH></TR>\n";
		}
	else {	
		print "<TR STYLE = 'height:8px;'><TD COLSPAN=6 ALIGN='center'><I>Click to view/edit</I></TD></TR>";
		print "<TR CLASS = 'odd'  STYLE = 'height:16px;'><TD ALIGN='left'><B>&nbsp;Name</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to incidents');\"><B>&nbsp;I&nbsp;</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to units');\"><B>&nbsp;U&nbsp;</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to facilities');\"><B>&nbsp;F&nbsp;</B></TD>
			<TD onmouseout=\"UnTip()\" onmouseover=\"Tip('Apply to regions');\"><B>&nbsp;R&nbsp;</B></TD>
			<TD><B>&nbsp;As of</B></TD></TR>\n";

		$i = 0;
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$use_with_i = (intval($row['use_with_i'])==1)? "<IMG SRC = './markers/checked.png' BORDER=0 />" : "";
			$use_with_u = (intval($row['use_with_u'])==1)? "<IMG SRC = './markers/checked.png' BORDER=0 />" : "";
			$use_with_f = (intval($row['use_with_f'])==1)? "<IMG SRC = './markers/checked.png' BORDER=0 />" : "";
			$use_with_r = (intval($row['use_with_r'])==1)? "<IMG SRC = './markers/checked.png' BORDER=0 />" : "";
			print "<TR CLASS = '{$evenodd[$i%2]} front ' onClick = 'to_view({$row['id']})'>
				<TD ALIGN='left'>{$row['line_name']}&nbsp;&nbsp;</TD>
				<TD ALIGN='center'>{$use_with_i}</TD>
				<TD ALIGN='center'>{$use_with_u}</TD>
				<TD ALIGN='center'>{$use_with_f}</TD>
				<TD ALIGN='center'>{$use_with_r}</TD>
				<TD ALIGN='right'>&nbsp;" . format_date($row['_on']) . "</TD></TR>\n";
			$i++;
			}
		}		// end if/else (mysql_num_rows($result)==0)
?>		
		<TR CLASS = 'odd'><TD COLSPAN=99 ALIGN='center'  STYLE = 'white-space:nowrap;'><BR />	
		  	<INPUT TYPE="button" VALUE="Add new boundary" onClick="new_form.submit();" ></TD>
			</TR></TABLE>
			</TD>
			<TD  ID = 'map_td'>
			<DIV id="map" STYLE = "margin-left:8px; width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px;" ></DIV>			
			</TD>
			</TR></TABLE>
	<FORM NAME = 'new_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
	<INPUT TYPE= 'hidden' NAME = '_func' VALUE = 'c'>
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
function buildMap_c() {															// 'create' version
	var container = document.getElementById("map");
	map = new GMap2(container, {draggableCursor:"auto", draggingCursor:"move"});
	tooltip = document.createElement("DIV"); 									// Add a DIV element for toolips
	tooltip.className = "tooltip";
	map.getPane(G_MAP_MARKER_PANE).appendChild(tooltip);
	
	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);

	map.addControl(new GLargeMapControl3D()); 									// Zoom control
	map.addMapType(G_PHYSICAL_MAP);
	var hierarchy = new GHierarchicalMapTypeControl(); 							// Create a hierarchical map type control
	hierarchy.addRelationship(G_SATELLITE_MAP, G_HYBRID_MAP, "Labels", true);	// make Hybrid the Satellite default
	map.addControl(hierarchy); 													// add the control to the map
	map.addControl(new GScaleControl()); 										// Scale bar
	map.disableDoubleClickZoom();
	GEvent.addListener(map, "click", leftClick); // Add listener for the click event
	}				// end function buildMap_c()


	function do_c_filled() {
		if (count < 3)	{alert("At least three map points required for fill"); document.c.frm_filled_y.checked = false; return;}
		
		$('fill_cb_tr').style.visibility = "hidden";			// hide rb row
		$('fill_tr').style.visibility = "visible";				// show input row
		document.c.frm_filled.value = 1;
		document.c.frm_filled_n.checked = false;
		document.c.frm_filled_y.checked = true;
		document.c.frm_filled = 1;
		}
	function undo_c_filled() {
		$('fill_cb_tr').style.visibility = "visible";			// show rb row
		$('fill_tr').style.visibility = "hidden";				// hide input row
		document.c.frm_filled.value = 0;
		document.c.frm_filled_n.checked = true;
		document.c.frm_filled_y.checked = false;
		}

	function do_u_filled() {
		$('fill_cb_tr').style.visibility = "hidden";			// hide rb row
		$('fill_tr').style.visibility = "visible";				// show input row
		document.u.frm_filled.value = 1;
		document.u.frm_filled_n.checked = false;
		document.u.frm_filled_y.checked = true;
		}
	function undo_u_filled() {
		$('fill_cb_tr').style.visibility = "visible";			// show rb row
		$('fill_tr').style.visibility = "hidden";				// hide input row
		document.u.frm_filled.value = 0;
		document.u.frm_filled_n.checked = true;
		document.u.frm_filled_y.checked = false;
		}

</SCRIPT>
<BODY onLoad = "buildMap_c(); undo_c_filled(); document.c.frm_name.focus();"  onUnload='GUnload();'>	
<?php
	print (array_key_exists("caption", $_POST))? "<H3>{$_POST['caption']}</H3>" : "";
?>	
		<FORM NAME="c" METHOD="post" ACTION="<?php print basename(__FILE__); ?>">		
	
		<TABLE BORDER="0" ALIGN="center" ID = 'outer'  STYLE = 'margin-left:20px;margin-top:20px;'><TR VALIGN='top'><TD>
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top" >
			<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">New Boundary</FONT><BR /><BR />
			<FONT SIZE = 'normal'><EM>click map - drag icons</EM></FONT></TD>
			</TR>
		<TR CLASS="odd" VALIGN="top" >
			<TD COLSPAN="2" ALIGN="CENTER">&nbsp;</TD>
			</TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="left">Name:</TD>
			<TD><INPUT MAXLENGTH="32" SIZE="32" type="text" NAME="frm_name" VALUE="" onChange = "this.value.trim();" />
			</TD></TR>

		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="left">Apply to:</TD>
			<TD ALIGN='left' CLASS="td_label"  STYLE = 'white-space:nowrap;' >
				<SPAN STYLE="margin-left: 20px;border:1px; width:20%">&nbsp;&nbsp;Incidents&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" NAME="box_use_with_i" onClick = "this.form.frm_use_with_i.value=1"/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Units&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" NAME="box_use_with_u"  onClick = 	"this.form.frm_use_with_u.value=1"/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Facilities&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" NAME="box_use_with_f"  onClick = "this.form.frm_use_with_f.value=1"/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;<?php print get_text("Regions");?>&nbsp;&raquo;&nbsp;<INPUT  TYPE= "checkbox" NAME="box_use_with_r"  onClick = "this.form.frm_use_with_r.value=1"/></SPAN>
				</TD>
			</TR>

		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="left">Line:</TD>
				<TD ALIGN="left"><SPAN CLASS="td_label" STYLE= "margin-left:20px" >
					Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_line_color" VALUE="#FF0000"  class="color" />&nbsp;&nbsp;&nbsp;&nbsp;
					Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_line_opacity" VALUE="0.5" />&nbsp;&nbsp;&nbsp;&nbsp;
					Width &raquo;&nbsp;<INPUT MAXLENGTH=2 SIZE=2 TYPE= "text" NAME="frm_line_width" VALUE="2" />
					</SPAN></TD>
			</TR>
		<TR VALIGN="baseline" CLASS="odd" ID = 'fill_cb_tr'  STYLE='visibility:visible'><TD CLASS="td_label" ALIGN="left">Filled:&nbsp;&nbsp;&nbsp;</TD>
				<TD ALIGN="left"><SPAN CLASS="td_label" STYLE = "margin-left: 20px;" >
				No&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_n' value = 'n' onClick = '' CHECKED  />&nbsp;&nbsp;&nbsp;&nbsp;
				Yes&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_y' value = 'y'  onClick = 'do_c_filled();'/>				
				</SPAN></TD>
			</TR>

		<TR VALIGN="baseline" CLASS="odd" ID = 'fill_tr' STYLE='visibility:hidden'><TD CLASS="td_label" ALIGN="left">Fill:</TD>
				<TD ALIGN="left"><SPAN CLASS="td_label" STYLE= "margin-left:20px" >
					Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_fill_color" VALUE="#FF0000"  class="color" />&nbsp;&nbsp;&nbsp;&nbsp;
					Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_fill_opacity" VALUE="0.5" />&nbsp;&nbsp;&nbsp;&nbsp;
					</SPAN>
					<SPAN onClick = "undo_c_filled();" STYLE = 'margin-left:20px'><I><U>Cancel fill</U></I></SPAN></TD>
			</TR>


		<TR  VALIGN="baseline"CLASS="even"><TD COLSPAN="2" ALIGN="center" STYLE = 'white-space:nowrap;'>
			<INPUT TYPE='hidden' NAME = '_func' VALUE='cp' />
			<INPUT TYPE='hidden' NAME = 'frm_line_data' VALUE='' />
			<INPUT TYPE='hidden' NAME = 'frm_filled' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_i' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_u' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_f' VALUE='0' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_r' VALUE='0' />

			<INPUT TYPE="button" VALUE="Cancel" STYLE = 'width:auto;' onClick = "document.navform._func.value='l'; document.navform.submit();"/>
			<INPUT TYPE="button" VALUE="Reset"  STYLE = 'width:auto; margin-left:40px;' onClick = "undo_c_filled();this.form.reset(); clearMap();buildMap_c();"/>
			<INPUT TYPE="button" NAME="sub_but" VALUE="Submit" STYLE = 'width:120px; margin-left:40px;' onclick="this.disabled=true; JSfnCheckInput(this.form, this);"/> 			
			</TD></TR>
			<TR><TD COLSPAN=3>&nbsp;</TD></TR>
			</FORM>
		</TD></TR></TABLE>
		</TD><TD>
			<DIV id="map" STYLE = "margin-left:8px; width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px;" ></DIV>
			</TD></TR></TABLE>
		
<CENTER>

<?php
	    break;				// end case "c"
	    
	case "cp":				// 'create' process
	
		$query = "INSERT INTO `{$tablename}` (`line_name`, `line_data`, `use_with_i`, `use_with_u`, `use_with_f`, `use_with_r`, `line_color`, `line_opacity`, `filled`, `fill_color`, `fill_opacity`,`line_width`,
		`_by`, `_from`, `_on`) 
			VALUES (" .
			 quote_smart(trim($_POST['frm_name'])) ."," .
			 quote_smart(trim($_POST['frm_line_data'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_i'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_u'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_f'])) ."," .
			 quote_smart(trim($_POST['frm_use_with_r'])) ."," .
			 quote_smart(trim($_POST['frm_line_color'])) ."," .
			 quote_smart(trim($_POST['frm_line_opacity'])) ."," .
			 quote_smart(trim($_POST['frm_filled'])) ."," .
			 quote_smart(trim($_POST['frm_fill_color'])) ."," .
			 quote_smart(trim($_POST['frm_fill_opacity'])) ."," .
			 quote_smart(trim($_POST['frm_line_width'])) ."," .
			 quote_smart($by) ."," .
			 quote_smart($from) ."," .
			 quote_smart(trim($now)) . ")" ;

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$insert_id = mysql_insert_id();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN"><HTML><HEAD><TITLE><?php print basename(__FILE__);?></TITLE>
<SCRIPT>

function waiter() {
	document.navform.id.value=<?php echo $insert_id;?>;
	document.navform._func.value="r";					// view the new entry
//	fade("c_id;")
	setTimeout("document.navform.submit()",1500);
	}
</SCRIPT>
</HEAD>
<BODY onLoad = "waiter();">
<DIV align="center" ID = 'c_id'><BR /><BR /><BR/><H3>'<?php echo $_POST['frm_name'];?>' added</H3></DIV>
</BODY></HTML>
<?php
	break;			// end case "c"
	
	case "u":
	case "r":				// similar - use common structure
	
		$dis = ($_func == "r") ? "DISABLED" : "";
		$capt = ($_func == "r") ? "View" : "Update";
?>			
<SCRIPT>
	function do_delete(id_val) {
		if (confirm("Really, really DELETE this boundary?")) {
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
		var icon = new GIcon();	  // Red marker icon
		icon.image = icon_url + "mm_20_red.png";
		addIcon(icon);	 
										  				// Make markers draggable
		var marker = new GMarker(point, {icon:icon, draggable:true, bouncy:false, dragCrossMove:true});
		map.addOverlay(marker);
		marker.content = count;
		markers.push(marker);
		marker.tooltip = "Point "+ count;
		GEvent.addListener(marker, "mouseover", function() {
			 showTooltip(marker);
			});
		GEvent.addListener(marker, "mouseout", function() {
			 tooltip.style.display = "none";
			});
	
		GEvent.addListener(marker, "drag", function() {	  // Drag listener
			 tooltip.style.display= "none";
			 drawOverlay();
			});
		
		GEvent.addListener(marker, "click", function() {  // Click listener to remove a marker
			tooltip.style.display = "none";
		
			for(var n = 0; n < markers.length; n++) {	  // Find out which marker to remove
			 if(markers[n] == marker) {
			 	map.removeOverlay(markers[n]);
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
		drawOverlay();
		}				// end function add marker()

	function buildMap_r() {				// 'view' version
	
		var container = document.getElementById("map");
		map = new GMap2(container, {draggableCursor:"auto", draggingCursor:"move"});
		tooltip = document.createElement("DIV"); // Add a DIV element for toolips
		tooltip.className = "tooltip";
		map.getPane(G_MAP_MARKER_PANE).appendChild(tooltip);
		var bounds = new GLatLngBounds();						// create  bounding box for centering
		var points = new Array();
		
<?php
			$query = "SELECT * FROM `{$tablename}` WHERE `id`='{$_POST['id']}' LIMIT 1";
			$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			extract ($row);
			$name = $row['line_name'];
			$use_w_i = ($use_with_i==1) ? "CHECKED" : "";	// checkbox settings
			$use_w_u = ($use_with_u==1) ? "CHECKED" : "";
			$use_w_f = ($use_with_f==1) ? "CHECKED" : "";
			$use_w_r = ($use_with_r==1) ? "CHECKED" : "";
	
			$points = explode (";", $line_data);
			for ($i = 0; $i<count($points); $i++) {
				$coords = explode (",", $points[$i]);
?>
		var thepoint = new GLatLng(<?php print $coords[0];?>, <?php print $coords[1];?>);
		bounds.extend(thepoint);
		points.push(thepoint);
<?php
				}			// end for ($i = 0 ... )
?>
<?php 	if (intval($filled) == 1) {?>
		var polyline = new GPolygon(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>, "<?php print $fill_color;?>", <?php print $fill_opacity;?>);
<?php	} else {?>
		var polyline = new GPolyline(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>);
<?php		} ?>		
		map.addOverlay(polyline);

		center = bounds.getCenter();
		zoom = map.getBoundsZoomLevel(bounds) -1;
		map.setCenter(center,zoom);
	
		map.addControl(new GLargeMapControl3D()); 									// Zoom control
		map.addMapType(G_PHYSICAL_MAP);
		var hierarchy = new GHierarchicalMapTypeControl(); 							// Create a hierarchical map type control
		hierarchy.addRelationship(G_SATELLITE_MAP, G_HYBRID_MAP, "Labels", true);	// make Hybrid the Satellite default
		map.addControl(hierarchy);													// add the control to the map
		map.addControl(new GScaleControl());										// Scale bar
		map.disableDoubleClickZoom();
		GEvent.addListener(map, "click", leftClick);								// Add click event listener
	
		}				// end function buildMap_r()

	function buildMap_u() {				// 'update' version
		
		var container = document.getElementById("map");
		map = new GMap2(container, {draggableCursor:"auto", draggingCursor:"move"});		
							// Add a div element for toolips
		tooltip = document.createElement("div");
		tooltip.className = "tooltip";
		map.getPane(G_MAP_MARKER_PANE).appendChild(tooltip);		
							// Load initial map and a bunch of controls
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
		map.addControl(new GLargeMapControl3D()); // Zoom control
		map.addMapType(G_PHYSICAL_MAP);
							// Create a hierarchical map type control
		var hierarchy = new GHierarchicalMapTypeControl();
							// make Hybrid the Satellite default
		hierarchy.addRelationship(G_SATELLITE_MAP, G_HYBRID_MAP, "Labels", true);
							// add the control to the map
		map.addControl(hierarchy);		
		map.addControl(new GScaleControl()); // Scale bar
		map.disableDoubleClickZoom();		
							// Add listener for the click event
		GEvent.addListener(map, "click", leftClick);
		}				// end function buildMap_u()

	function fillmap() {
		var bounds = new GLatLngBounds();						// create  bounding box for centering	
<?php
	for ($i = 0; $i< count($points_ary); $i++) {
		$temp = explode(",", $points_ary[$i]);
?>
		var thepoint = new GLatLng(<?php echo $temp[0];?>, <?php echo $temp[1];?>);
		bounds.extend(thepoint);
		do_point(<?php echo $temp[0];?>, <?php echo $temp[1];?>);
<?php	
		}		// end for ($i ... )
?>
		center = bounds.getCenter();
		zoom = map.getBoundsZoomLevel(bounds);
		map.setCenter(center,zoom);
		}				// end function fillmap()

function do_point(in_lat, in_lng) {
	var point = new GLatLng( in_lat, in_lng);

// if(point) {
	count++;
	var icon = new GIcon();	// Red marker icon
	icon.image = icon_url + "mm_20_red.png";		// sm_red.png
	addIcon(icon); 

	var marker = new GMarker(point, {icon:icon, draggable:true, bouncy:false, dragCrossMove:true});	// Make markers draggable
	map.addOverlay(marker);
	marker.content = count;
	markers.push(marker);
	marker.tooltip = "Point "+ count;

	GEvent.addListener(marker, "mouseover", function() { showTooltip(marker);	});

	GEvent.addListener(marker, "mouseout", function() { tooltip.style.display = "none";	});
							 // Drag listener
	GEvent.addListener(marker, "drag", function() { tooltip.style.display= "none"; drawOverlay();	});
								// Click listener to remove a marker
	GEvent.addListener(marker, "click", function() {
	tooltip.style.display = "none";
											// Find out which marker to remove
	for(var n = 0; n < markers.length; n++) {
		if(markers[n] == marker) {
			map.removeOverlay(markers[n]);
			break;
			}
		}
	markers.splice(n, 1);	// Shorten array of markers and adjust counter
	if(markers.length == 0) {
		count = 0;
		}
	else {
		count = markers[markers.length-1].content;
		drawOverlay();
		}
	});
 drawOverlay();
// }		// end	if(point)
}				// end function do_point()



	function toggle(the_value) {
		return (the_value==0)? 1 : 0 ;
		}
	
</SCRIPT>

<?php
if ($_func == "r") {
?>
<BODY onLoad = "buildMap_r(); document.u.frm_name.focus();"  onUnload='GUnload();'>
<?php
	}
else {
?>
	<BODY onLoad = "buildMap_u(); fillmap(); document.u.frm_name.focus();"  onUnload='GUnload();'>	
<?php	
	}
?>
		<FORM NAME="u" METHOD="post" ACTION="<?php print basename(__FILE__); ?>">		
	
		<TABLE BORDER="0" ALIGN="left" ID = 'outer'  STYLE = 'margin-left:20px;margin-top:20px;'><TR VALIGN='top'><TD>
		<TABLE BORDER="0" ALIGN="left" STYLE = 'white-space:nowrap; verticalAlign:bottom'>
		<TR CLASS="even">
			<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1"><?php print $capt;?> Boundary '<?php print $name;?>'</FONT></TD>
			</TR>
		<TR CLASS="odd" >
			<TD CLASS="td_label" ALIGN="right">Name:</TD>
			<TD><INPUT MAXLENGTH="32" SIZE="32" type="text" NAME="frm_name" VALUE="<?php print $row['line_name'];?>" <?php print $dis;?> onChange = "this.value.trim();">
			</TD></TR>

		<TR CLASS="even" ><TD CLASS="td_label" ALIGN="left">Apply to:</TD>
			<TD ALIGN='left' CLASS="td_label" >
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Incidents&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" 	NAME="box_use_with_i" 	<?php print $use_w_i;?> onClick = "this.form.frm_use_with_i.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Units&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox" 		NAME="box_use_with_u"	<?php print $use_w_u;?> onClick = "this.form.frm_use_with_u.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Facilities&nbsp;&raquo;&nbsp;<INPUT TYPE= "checkbox"	NAME="box_use_with_f"	<?php print $use_w_f;?> onClick = "this.form.frm_use_with_f.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;<?php print get_text("Regions");?>&nbsp;&raquo;&nbsp;<INPUT  TYPE= "checkbox" 		NAME="box_use_with_r"	<?php print $use_w_r;?> onClick = "this.form.frm_use_with_r.value=toggle(this.value)" <?php print $dis;?>/></SPAN>
				</TD>
			</TR>

		<TR CLASS="odd" ><TD CLASS="td_label" ALIGN="left">Line:</TD>
				<TD ALIGN="left"><SPAN CLASS="td_label" >
				Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_line_color" VALUE="<?php print $row['line_color'];?>"  class="color" <?php print $dis;?> />&nbsp;&nbsp;&nbsp;&nbsp;
				Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_line_opacity" VALUE="<?php print $row['line_opacity'];?>" <?php print $dis;?>/>&nbsp;&nbsp;&nbsp;&nbsp;
				Width &raquo;&nbsp;<INPUT MAXLENGTH=2 SIZE=2 TYPE= "text" NAME="frm_line_width" VALUE="<?php print $row['line_width'];?>" <?php print $dis;?> />
			</SPAN></TD>
			</TR>
<?php
	if (intval($row['filled'])==1) {
?>
		<TR CLASS="even" ID = 'fill_tr'><TD CLASS="td_label" ALIGN="left">Fill: &nbsp; &nbsp; &nbsp;</TD>
				<TD ALIGN="left"><SPAN CLASS="td_label" >
					Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_fill_color" VALUE="<?php print $row['fill_color'];?>"  class="color" <?php print $dis;?> />&nbsp;&nbsp;&nbsp;&nbsp;
					Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_fill_opacity" VALUE="<?php print $row['fill_opacity'];?>" <?php print $dis;?> />&nbsp;&nbsp;&nbsp;&nbsp;
					</SPAN></TD>
			</TR>
<?php
		}
	else {				// 7/2/11
?>
			<INPUT TYPE='hidden' NAME = 'frm_fill_color' VALUE='<?php print $row['fill_color'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_fill_opacity' VALUE='<?php print $row['fill_opacity'];?>' />
<?php
		}
?>

		<TR ><TD COLSPAN="2" ALIGN="center" STYLE = 'white-space:nowrap;'>
			<INPUT TYPE='hidden' NAME = '_func' VALUE='up' />
			<INPUT TYPE='hidden' NAME = 'frm_line_data' VALUE='<?php print $row['line_data'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_filled' VALUE='<?php print $row['filled'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_id' VALUE='<?php print $row['id'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_i' VALUE='<?php print $row['use_with_i'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_u' VALUE='<?php print $row['use_with_u'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_f' VALUE='<?php print $row['use_with_f'];?>' />
			<INPUT TYPE='hidden' NAME = 'frm_use_with_r' VALUE='<?php print $row['use_with_r'];?>' />
			
			<INPUT TYPE="button" VALUE="Delete" STYLE = 'width:auto;'  onClick = "do_delete(<?php echo $row['id'];?>)"/>
			<INPUT TYPE="button" VALUE="Cancel" STYLE = 'width:auto; margin-left:20px;'  onClick = "document.navform._func.value='l'; document.navform.submit();"/>
<?php
		if ($_func == "r") {
?>
			<INPUT TYPE="button" NAME="sub_but" VALUE="Edit" STYLE = 'width:120px; margin-left:20px;' onclick="this.disabled=true; document.navform._func.value='u'; document.navform.submit();"/> 			
<?php
			}
		else {
?>
			<INPUT TYPE="button" VALUE="Reset"  STYLE = 'width:auto; margin-left:20px;' onClick = "this.form.reset(); clearMap(); buildMap_r();"/>
			<INPUT TYPE="button" NAME="sub_but" VALUE="Submit" STYLE = 'width:100px; margin-left:20px;' onclick=" JSfnCheckInput(this.form, this);" /> 			
<?php
			}
?>

				</TD></TR>
			<TR ><TD COLSPAN=3>&nbsp;</TD></TR>
			</FORM>
		</TD></TR></TABLE>
		</TD><TD>
			<DIV id="map" STYLE = "margin-left:8px; width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px;" ></DIV>
			</TD></TR></TABLE>
		
<CENTER>
<?php
	    break;				// end case "u"
	
	case "up":				// process 'update'

		$query = "UPDATE `{$tablename}` SET 
			`line_name` = " . 		quote_smart(trim($_POST['frm_name'])) .",
			`line_data` = " .  		quote_smart(trim($_POST['frm_line_data'])) .",
			`use_with_i` = " .  	quote_smart(trim($_POST['frm_use_with_i'])) .",
			`use_with_u` = " .  	quote_smart(trim($_POST['frm_use_with_u'])) .",
			`use_with_f` = " .  	quote_smart(trim($_POST['frm_use_with_f'])) .",
			`use_with_r` = " .  	quote_smart(trim($_POST['frm_use_with_r'])) .",
			`line_color` = " .  	quote_smart(trim($_POST['frm_line_color'])) .",
			`line_opacity` = " .  	quote_smart(trim($_POST['frm_line_opacity'])) .",
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
//	fade("up_id;")	
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
//	fade("dp_id;")	
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
<FORM NAME = 'navform' METHOD = 'post' ACTION = "<?php print basename(__FILE__);?>">
<INPUT TYPE='hidden' NAME = '_func' VALUE = ''>
<INPUT TYPE='hidden' NAME = 'id' VALUE = '<?php print $the_id;?>'>
</FORM>

</BODY>
</HTML>