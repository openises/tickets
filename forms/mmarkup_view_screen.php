<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$temp = get_variable('auto_poll');				// 1/28/09
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
$id = mysql_real_escape_string($_GET['id']);
$gunload = "";
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`= " . $id;
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
extract($row);
$filled = ($row['filled'] == 1) ? 1: 0;
$fill_color = ($row['fill_color'] == null) ? "": $row['fill_color'];
$fill_opacity = ($row['fill_opacity'] == null) ? "0": $row['fill_opacity'];
$line_opacity = ($row['line_opacity'] == null) ? 5: $row['line_opacity'];
switch($row['line_type']) {
	case "b":
		$theType = "Banner";
        break;
	case "p":
		$theType = "Polygon";
        break;		
	case "l":
		$theType = "Line";
        break;	
	case "c":
		$theType = "Circle";
        break;
		}
if($row['use_with_bm'] == 1) {
	$applyto = "Use with Basemap";
	} elseif($row['use_with_r'] == 1) {
	$applyto = "Use with Regions";
	} elseif($row['use_with_f'] == 1) {
	$applyto = "Use with Facilities";
	} elseif($row['use_with_u_ex'] == 1) {	
	$applyto = "Use for Exclusion Zones";
	} elseif($row['use_with_u_rf'] == 1) {
	$applyto = "Use for Ring Fences";
	}

$isVisible = ($row['line_status'] == 0) ? "Yes" : "No";

$query_cat	= "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` WHERE `id`= " . $row['line_cat_id'];
$result_cat	= mysql_query($query_cat) or do_error($query_cat, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row_cat = stripslashes_deep(mysql_fetch_assoc($result_cat));
$cat_name = $row_cat['category'];

$temp = preg_split("/;/", $row['line_data']);
$banner_text = (($row['line_type'] == "b") && (($temp[1]) && ($temp[1] !=""))) ? $temp[1] : "";
$theRadius = (($row['line_type'] == "c") && (($temp[1]) && ($temp[1] != 0))) ? $temp[1] : 0;
	
?>

<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var polygon;
var polyline;
var circle;
var banner;
var theType = "<?php print $row['line_type'];?>";
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
	}
	});
var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
	}
	});

var colors = new Array ('odd', 'even');

function change_type(id) {
	if(id=="p") {
		$('radius').style.display='none';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('font_size2').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Polygon";
		type = "p";
		} else if(id=="l") {
		$('radius').style.display='none';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('font_size2').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Line";
		type = "l";
		} else if(id=="c") {
		$('radius').style.display='inline';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('font_size2').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Circle";
		type = "c";
		} else if(id=="b") {
		$('radius').style.display='none';
		$('ban_text').style.display='inline';
		$('font_size').style.display='inline';
		$('font_size2').style.display='inline';
		$('line_width').style.display='none';
		$('line_width2').style.display='none';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Banner";
		type = "b";
		} else {
		$('radius').style.display='none';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('font_size2').style.display='none';
		$('line_width').style.display='none';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Error";
		type = "e";
		}
	}
	
function set_fieldview() {
	var filled = <?php print $filled;?>;
	if(filled == 1) {
		$('fill_cb_tr').style.display = '';
		} else {
		$('fill_cb_tr').style.display = 'none';
		}
	}


function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	leftcolwidth = viewportwidth * .45;
	rightcolwidth = viewportwidth * .40;
	listwidth = colwidth * .95
	fieldwidth = colwidth * .6;
	medfieldwidth = colwidth * .3;		
	smallfieldwidth = colwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('markup_form_table').style.width = leftcolwidth + "px";
	$('rightcol').style.width = rightcolwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = rightcolwidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	change_type(theType);
	set_fieldview();
	set_fontsizes(viewportwidth, "fullscreen");
	}
</SCRIPT>
</HEAD>
<BODY>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10, 10/23/12 -->		
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<A NAME='top'>	
			<TABLE id='markup_form_table' BORDER="0" ALIGN="center" width='100%'>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
						<SPAN CLASS='text_green text_biggest'>View <SPAN id='type_flag'></SPAN> "<?php print $row['line_name'];?>" Map Markup</SPAN>
						<BR />
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>
				<TR VALIGN="baseline" CLASS="odd">
					<TD CLASS="td_label text text_left">Description:</TD>
					<TD CLASS='td_data text text_left'><?php print $row['line_name'];?>
						<SPAN STYLE = 'margin-left:20px' CLASS="td_label text text_left" >Visible&nbsp;&raquo;&nbsp;</SPAN>
						<SPAN STYLE = 'margin-left:20px' CLASS="td_data text text_left" ><?php print $isVisible;?></SPAN>
					</TD>
				</TR>
				<TR VALIGN="baseline" CLASS="even">
					<TD CLASS="td_label text text_left">Ident:</TD>
					<TD CLASS='td_data text text_left'><?php print $row['line_ident'];?>
						<SPAN STYLE = 'margin-left:20px'  CLASS="td_label text text_left">Category:&nbsp;&raquo;&nbsp;</SPAN><SPAN STYLE = 'margin-left:20px' CLASS="td_data text text_left" ><?php print $cat_name;?></SPAN>
						<SPAN ID='radius' CLASS="td_label text text_left" STYLE = 'margin-left:20px; display: none;'>Radius&nbsp;&raquo;&nbsp;<?php print $theRadius;?>&nbsp;&nbsp; <i>(mi)</i></SPAN>
						<SPAN ID='ban_text' CLASS="td_label text text_left" STYLE = 'margin-left:20px; display: none;'>Banner text:&nbsp;&raquo;&nbsp;<?php print $banner_text;?></SPAN>

					</TD>
				</TR>
				<TR VALIGN="baseline" CLASS="odd">
					<TD CLASS="td_label text text_left" ALIGN="left">Apply to:</TD>
					<TD CLASS='td_data text text_left'><?php print $applyto;?></TD>
				</TR>
				<TR VALIGN="baseline" CLASS="even">
					<TD CLASS="td_label text text_left"><SPAN id='type_flag2'></SPAN>:</TD>
					<TD CLASS='td_data text text_left'>
						<SPAN CLASS="td_label text text_left">Color &raquo;&nbsp;</SPAN>
						<SPAN style='background-color: #<?php print $row['line_color'];?>; border: 1px inset #707070;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
						<SPAN CLASS="td_label text text_left" id='line_opacity'>&nbsp;&nbsp;&nbsp;&nbsp;Opacity &raquo;&nbsp;</SPAN>
						<SPAN CLASS='td_data text text_left' id='line_opacity2'><?php print $row['line_opacity'];?>&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
						<SPAN id='line_width' CLASS="td_label text text_left" style='display: none;'>Width &raquo;&nbsp;</SPAN>
						<SPAN id='line_width2' CLASS='td_data text text_left'><?php print $row['line_width'];?>(px)</SPAN>
						<SPAN CLASS='td_label text text_left' id='font_size' style='display: none;'>Font Size &raquo;&nbsp;</SPAN>
						<SPAN CLASS='td_data text text_left' id='font_size2' style='display: none;'><?php print $row['line_width'];?>(px)</SPAN>
					</TD>
				</TR>
				<TR VALIGN="baseline" CLASS="odd" ID='fill_cb_tr' style='display: none; width: 100%;'>
					<TD CLASS="td_label text text_left">Fill:&nbsp;&nbsp;</TD>
					<TD CLASS='td_data text text_left'>
						<SPAN ID='fill_details'>
							<SPAN CLASS="td_label text text_left">Color &raquo;&nbsp;</SPAN>
							<SPAN style='background-color: #<?php print $row['fill_color'];?>; border: 1px inset #707070;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
							<SPAN CLASS="td_label text text_left" >&nbsp;&nbsp;&nbsp;&nbsp;Opacity &raquo;&nbsp;</SPAN>
							<SPAN CLASS="td_data text text_left" ><?php print $row['fill_opacity'];?></SPAN>
						</SPAN>
					</TD>
				</TR>
				<TR CLASS="spacer" VALIGN="top" >
					<TD CLASS='spacer' COLSPAN="2" ALIGN="CENTER">&nbsp;</TD>
				</TR>
			</TABLE>			
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: relative; top: 50px; z-index: 1;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='ed_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.edit_Form.submit();'><?php print get_text("Edit");?><BR /><IMG id='can_img' SRC='./images/edit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id= 'map_canvas' style = 'border: 1px outset #707070;'></DIV>
			<SPAN id='map_caption' CLASS='text_blue text text_bold' style='width: 100%; text-align: center; display: block;'><?php print get_variable('map_caption');?></SPAN><BR />
		</DIV>
	</DIV>
	<div id="Test"></div>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "mmarkup.php"></FORM>
		<FORM NAME='edit_Form' METHOD="get" ACTION = "mmarkup.php">
		<INPUT TYPE='hidden' NAME='func' VALUE='edit'>
		<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
		<INPUT TYPE='hidden' NAME='id' VALUE='<?php print $id;?>'>		
		</FORM>
		<FORM NAME='reset_Form' METHOD='get' ACTION='mmarkup.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
		<INPUT TYPE='hidden' NAME='add' VALUE='true'>
		</FORM>

		<!-- 2829 -->
		<A NAME="bottom" /> <!-- 5/3/10 -->
		<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>

		<SCRIPT>
		var max_zoom = <?php print get_variable('def_zoom');?>;
		var thePoly;
		var theMarker;
		var map;				// make globally visible
		var thelevel = '<?php print $the_level;?>';
		var the_icon;
		var zoom = <?php print get_variable('def_zoom');?>;
		var locale = <?php print get_variable('locale');?>;
		var my_Local = <?php print get_variable('local_maps');?>;
		var latLng;
		if (typeof window.innerWidth != 'undefined') {
			viewportwidth = window.innerWidth,
			viewportheight = window.innerHeight
			} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
			viewportwidth = document.documentElement.clientWidth,
			viewportheight = document.documentElement.clientHeight
			} else {
			viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
			viewportheight = document.getElementsByTagName('body')[0].clientHeight
			}
		mapWidth = viewportwidth * .40;
		mapHeight = viewportheight * .55;
		outerwidth = viewportwidth * .99;
		outerheight = viewportheight * .95;
		colwidth = outerwidth * .42;
		colheight = outerheight * .95;
		leftcolwidth = viewportwidth * .40;
		rightcolwidth = viewportwidth * .45;
		listwidth = colwidth * .95
		fieldwidth = colwidth * .6;
		medfieldwidth = colwidth * .3;		
		smallfieldwidth = colwidth * .15;
		$('outer').style.width = outerwidth + "px";
		$('outer').style.height = outerheight + "px";
		$('leftcol').style.width = leftcolwidth + "px";
		$('leftcol').style.height = colheight + "px";	
		$('markup_form_table').style.width = leftcolwidth + "px";
		$('rightcol').style.width = rightcolwidth + "px";
		$('rightcol').style.height = colheight + "px";	
		$('map_canvas').style.width = rightcolwidth + "px";
		$('map_canvas').style.height = mapHeight + "px";
		set_fontsizes(viewportwidth, "fullscreen");
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		var initZoom = <?php print get_variable('def_zoom');?>;
		init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
		map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
		var bounds = map.getBounds();	
		var zoom = map.getZoom();
		var got_points = false;	// map is empty of points
		if(theType =="p") {
			draw_polygon("<?php print $row['line_name'];?>", 
					"#<?php print $row['line_color'];?>", 
					<?php print $line_opacity;?>, 
					<?php print $row['line_width'];?>, 
					<?php print $filled;?>, 
					"#<?php print $fill_color;?>",
					<?php print $fill_opacity;?>,
					"<?php print $row['line_data'];?>",
					<?php print $row['id'];?>);
			} else if(theType == "c") {
			draw_circle("<?php print $row['line_name'];?>", 
					"<?php print $row['line_data'];?>",
					"#<?php print $row['line_color'];?>", 
					<?php print $row['line_width'];?>, 
					<?php print $line_opacity;?>, 
					"#<?php print $fill_color;?>",
					<?php print $fill_opacity;?>,
					<?php print $row['id'];?>);
			} else if(theType == "l") {
			draw_polyline("<?php print $row['line_name'];?>", 
					"#<?php print $row['line_color'];?>", 
					<?php print $line_opacity;?>, 
					<?php print $row['line_width'];?>, 
					"<?php print $row['line_data'];?>",
					<?php print $row['id'];?>);	
			} else if(theType == 'b') {
			draw_banner("<?php print $row['line_name'];?>", 
					"<?php print $row['line_data'];?>", 
					<?php print $row['line_width'];?>,
					"#<?php print $row['line_color'];?>", 
					<?php print $row['id'];?>);
			}
					
					
		function draw_polygon(linename, color, opacity, width, filled, fillcolor, fillopacity, linedata, theID) {
			if(!linedata) {return;}
			var path = new Array();
			var thelineData = linedata.split(';');
			for (i = 0; i < thelineData.length; i++) { 
				var theCoords = thelineData[i].split(',');
				var theLatLng = new L.LatLng(theCoords[0], theCoords[1]);
				path[i] = theLatLng;
				}
			polygon = L.polygon([path],{
			color: color,
			weight: width,
			opacity: opacity,
			fill: filled,
			fillColor: fillcolor,
			fillOpacity: fillopacity,
			stroke: true
			}).addTo(map);
			polygon.bindPopup(linename);
			var theBounds = polygon.getBounds();
			map.fitBounds(theBounds);
			return polygon;
			}
			
		function draw_polyline(linename, color, opacity, width, linedata, theID) {
			if(!linedata) {return;}
			var path = new Array();
			var thelineData = linedata.split(';');
			for (i = 0; i < thelineData.length; i++) { 
				var theCoords = thelineData[i].split(',');
				var theLatLng = new L.LatLng(theCoords[0], theCoords[1]);
				path[i] = theLatLng;
				}
			polyline = L.polyline(path,{
			color: color,
			weight: width,
			opacity: opacity,
			stroke: true
			}).addTo(map);
			polyline.bindPopup(linename);
			var theBounds = polyline.getBounds();
			map.fitBounds(theBounds);
			return polyline;
			}

		function draw_circle(linename, linedata, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity, theID) {
			var theData = linedata.split(';');
			var thelineData = theData[0].split(',');
			var theRadius = theData[1];
			var radius = theRadius*1000
			if((!(bound_names[theID])) && (!(boundary[theID]))){
				var circle = L.circle([thelineData[0], thelineData[1]], radius, {
					color: strokeColor,
					opacity: strokeOpacity,
					fillColor: fillColor,
					fillOpacity: fillOpacity
					}).addTo(map);	
				circle.bindPopup(linename);
				}
			var lat = thelineData[0];
			var lng = thelineData[1];
			var point = new L.LatLng(lat, lng);
			map.setView(point, 11);
			}

		function draw_banner(linename, linedata, width, color, theID) {        // Create the banner - 6/5/2013
			var theData = linedata.split(';');
			var thelineData = theData[0].split(',');
			var lat = thelineData[0];
			var lng = thelineData[1];
			var theBanner = theData[1];
			var point = new L.LatLng(lat, lng);
			var font_size = width;
			var the_color = (typeof color == 'undefined')? "000000" : color ;	// default to black
			$('Test').innerHTML = theBanner;
			$('Test').style.fontSize = font_size;
			var test = document.getElementById("Test");
			var height = (test.clientHeight + 1) + "px";
			var width = (test.clientWidth + 1) + "px";
			var x = (height/2) * -1;
			var y = (width/2) * -1;
			var html = "<SPAN style=\"vertical-align: middle; text-align: center; font-size: " + font_size + "px; color: " + the_color + ";\">" + theBanner + "</SPAN>";
			var banner = L.marker(point, {
				icon: L.divIcon({
					className: "textLabelclass",
					html: html
				}),
				draggable: false
			});
			banner.addTo(map);
			var point = new L.LatLng(lat, lng);
			map.setView(point, 11);
			}				// end function draw Banner()			
</SCRIPT>
</BODY>
</HTML>
<?php
exit();
