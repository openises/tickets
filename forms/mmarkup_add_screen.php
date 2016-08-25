<?php
error_reporting(E_ALL);				// 9/13/08
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
require_once('./incs/functions.inc.php');
$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
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
$gunload = "";
?>
<SCRIPT>
window.onresize=function(){set_size()};

window.onload = function(){set_size();};
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var type = "e";
var selected_position = false;
var circle;
var polygon;
var polyline;
var banner;
var myMarker;
var theDragStart;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var inner_listwidth;
var celwidth;
var colheight;
var viewportwidth;
var viewportheight;
var outerwidth;
var outerheight;
var count = 0;
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
var centeredIcon = L.Icon.extend({options: {iconSize: [25, 25], iconAnchor: [12, 12], popupAnchor: [12, -12]
}
});
			
var colors = new Array ('odd', 'even');

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
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	$('markup_form_table').style.width = colwidth + "px";
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	}

function change_type(id) {
	if(id==1) {
		$('radius').style.display='none';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Polygon";
		type = "p";
		} else if(id==2) {
		$('radius').style.display='none';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Line";
		type = "l";
		} else if(id==3) {
		$('radius').style.display='inline';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Circle";
		type = "c";
		} else if(id==4) {
		$('radius').style.display='none';
		$('ban_text').style.display='inline';
		$('font_size').style.display='inline';
		$('line_width').style.display='none';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Banner";
		type = "b";
		} else {
		$('radius').style.display='none';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('line_width').style.display='none';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Error";
		type = "e";
		}
	document.mkup_add.frm_line_type.value = type;
	$('mainForm').style.display = 'inline';
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

function JSfnCheckInput(myform, mybutton, test) {		// reject empty form elements
	var errmsg = "";
	if (myform.frm_name.value.trim()=="") 			{errmsg+= "\tDescription is required\n";}
	if (myform.frm_ident.value.trim()=="") 			{errmsg+= "\tIdent is required\n";}
	if (myform.frm_line_cat_id.value ==0) 			{errmsg+= "\tCategory selection is required\n";}
	if(type == "p" || type == "l") {
		if (!(points.length>1))							{errmsg+= "\tAt least two map points are required\n";}
		}
	if (myform.frm_line_color.value.trim()=="") 	{errmsg+= "\tColor is required\n";}
	if (myform.frm_line_opacity.value.trim()=="") 	{errmsg+= "\tOpacity is required\n";}
	if (myform.frm_line_width.value.trim()=="") 	{errmsg+= "\tWidth is required\n";}
	if(type == "c") {
		if (!(points.length > 0))						{errmsg+= "\tCircle center is required\n";}
		if(points.length > 0) {
			var thelat = points[0].lat.toFixed(6);
			var thelng = points[0].lng.toFixed(6);
			}
		if (!(is_ok_radius (myform.circ_radius.value.trim()))) {errmsg += "\tValid circle radius is required\n";};
		if(is_ok_radius(myform.circ_radius.value.trim())) {
			var theradius = myform.circ_radius.value.trim();
			myform.frm_line_data.value = (thelat + "," + thelng + ";" + theradius);
			}
		}
	if(type == "b") {
		if (myform.banner_text.value.trim()=="") 		{errmsg+= "\tBanner text is required\n";};
		if (!(points.length > 0))						{errmsg+= "\tBanner position is required\n";}
		if((myform.banner_text.value.length > 0) && (points.length > 0)) {
			var thelat = points[0].lat.toFixed(6);
			var thelng = points[0].lng.toFixed(6);
			var thetext = myform.banner_text.value.trim();
			myform.frm_line_data.value = (thelat + "," + thelng + ";" + thetext);
			}
		}
			
	if ((myform.frm_use_with_bm.value == '0') &&
		(myform.frm_use_with_r.value == '0') &&
		(myform.frm_use_with_f.value == '0') &&
		(myform.frm_use_with_u_ex.value == '0') &&
		(myform.frm_use_with_u_rf.value == '0'))			{errmsg+= "\tAt least one 'Apply to ...' is required\n";}
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
		if(type == "p" || type == "l") {
			var comma = ","; 
			var semic = ";"; 
			myform.frm_line_data.value = sep = ""; 
			for (i=0; i<points.length; i++ ) {
				myform.frm_line_data.value += sep + points[i].lat.toFixed(6) + comma +  points[i].lng.toFixed(6); 
				sep = semic;
				}
			}
//		alert(myform.frm_line_data.value);
		myform.submit(); 
		}			// end if/else errormsg 
	}		// end function JSfnCheckInput	

function do_reset() {
	if(thePoly) {map.removeLayer(thePoly);}
	if(theMarker) {map.removeLayer(theMarker);}
	if(markers.length > 0) {
		for(var i = 0; i < markers.length; i++) {
			map.removeLayer(markers[i]);
			}
		markers = [];
		points = [];
		}
	$('selectType').selectedIndex = 0;
	}
	
function draw_banner() {
	if(points.length > 0 && document.mkup_add.banner_text.value.length > 0 && document.mkup_add.frm_font_size.value.length > 0) {
		var theData = points;
		var lat = points[0].lat.toFixed(6);
		var lng = points[0].lng.toFixed(6);
		var theBanner = document.mkup_add.banner_text.value.trim();
		var point = new L.LatLng(lat, lng);
		var font_size = document.mkup_add.frm_font_size.value.trim();
		var the_color = document.mkup_add.frm_line_color.value;
		var html = "<DIV style=\"background: transparent; font-size: " + font_size + "px; color: #" + the_color + ";\">" + theBanner + "</DIV>";
		var myTextLabel = L.marker(point, {
			icon: L.divIcon({
				html: html
			}),
			draggable: false
		});
		myTextLabel.addTo(map);
		}
	}				// end function draw Banner()
	
	function draw_circle(linename, linedata, strokeColor, strokeWidth, strokeOpacity, filled, fillColor, fillOpacity, filled) {
		if(circle) { map.removeLayer(circle);}
		if(myMarker) { map.removeLayer(myMarker);}
		var theData = linedata.split(';');
		var thelineData = theData[0].split(',');
		var theRadius = theData[1];
		var radius = theRadius*1000
		circleRadius = radius;
		circleFillcolor = fillColor;
		circleFillopacity = fillOpacity;
		circleColor = strokeColor;
		circleOpacity = strokeOpacity;
		circleLinename = linename;
		var lat = thelineData[0];
		var lng = thelineData[1];
		var point = new L.LatLng(lat, lng);
		var iconurl = "./our_icons/circle_dot.png";
		var icon = new centeredIcon({iconUrl: iconurl});
		if(filled == 1) {
			circle = L.circle(point, radius, {
					color: strokeColor,
					opacity: strokeOpacity,
					fill: filled,
					fillColor: fillColor,
					fillOpacity: fillOpacity
					}).addTo(map);
			} else {
			circle = L.circle(point, radius, {
					color: strokeColor,
					opacity: strokeOpacity,
					fill: 0
					}).addTo(map);
			}
		myMarker = new L.marker(point, {icon:icon, draggable:'true'});
		myMarker.on('dragend', circ_drag_end);
		myMarker.addTo(map);
		points.push(point);	
		map.setView(point, 11);
		}
		
	function do_draw_circle() {
		if(document.mkup_add.circ_radius.value == 0 || document.mkup_add.circ_radius.value == "" || points.length == 0) {
			return false;
			}
		var theRadius = document.mkup_add.circ_radius.value.trim();
		if(is_ok_radius (theRadius)){
			var thelat = points[0].lat.toFixed(6);
			var thelng = points[0].lng.toFixed(6);
			var theLineData = (thelat + "," + thelng + ";" + theRadius);
			var theColor = "#" + document.mkup_add.frm_line_color.value;
			draw_circle(document.mkup_add.frm_name.value, 
				theLineData,
				theColor, 
				document.mkup_add.frm_line_width.value,
				document.mkup_add.frm_line_opacity.value,
				"#" + document.mkup_add.frm_fill_color.value,
				document.mkup_add.frm_fill_opacity.value,
				document.mkup_add.frm_filled.value);
			}
		}
		
</SCRIPT>
</HEAD>
<BODY onLoad = "set_size(); ck_frames(); parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id='outer' style='position: absolute; left: 0px;'>
		<DIV id='leftcol' style='position: absolute; left: 10px;'>
			<A NAME='top'>	
			<FORM ID='mkup_Add_form' NAME="mkup_add" METHOD="post" ACTION="mmarkup.php?goadd=true">		
			<TABLE id='markup_form_table' BORDER="0" ALIGN="center" width='100%'>
				<TR CLASS="even" VALIGN="top" >
					<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">New <SPAN id='type_flag'></SPAN></FONT><BR /><BR />
						<FONT SIZE = 'normal'><EM><SPAN id='caption'>click map to add points - drag icons to move points</SPAN></EM></FONT>
					</TD>
				</TR>
				<TR CLASS="spacer" VALIGN="top" >
					<TD CLASS='spacer' COLSPAN="2" ALIGN="CENTER">&nbsp;</TD>
				</TR>
				<TR CLASS="even" VALIGN="top" >
					<TD COLSPAN="2" ALIGN="CENTER">
						<SELECT ID = 'selectType' NAME='markup_type' onChange = "change_type(this.value)">
							<OPTION VALUE=0 SELECTED>Select Type</OPTION>
							<OPTION VALUE=1>Polygon</OPTION>
							<OPTION VALUE=2>Line</OPTION>							
							<OPTION VALUE=3>Circle</OPTION>
							<OPTION VALUE=4>Banner</OPTION>
						</SELECT>
					</TD>
				</TR>
				<TR>
					<TD COLSPAN=99>
						<TABLE id='mainForm' style='display: none;'>
							<TR CLASS="spacer" VALIGN="top" >
								<TD CLASS='spacer' COLSPAN="2" ALIGN="CENTER">&nbsp;</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="odd">
								<TD CLASS="td_label" ALIGN="left">Description:</TD>
								<TD><INPUT MAXLENGTH="32" SIZE="32" type="text" NAME="frm_name" VALUE="" onChange = "this.value.trim();" />
									<SPAN STYLE = 'margin-left:20px' CLASS="td_label" >Visible&nbsp;&raquo;&nbsp;</SPAN>
									<SPAN STYLE = 'margin-left:10px'>Yes&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'rb_line_is_vis' onClick = "document.mkup_Add_form.rb_line_not_vis.checked = false;document.c.frm_line_status.value=0" CHECKED /></SPAN>
									<SPAN STYLE = 'margin-left:20px'>No&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'rb_line_not_vis' onClick = "document.mkup_Add_form.rb_line_is_vis.checked = false;document.c.frm_line_status.value=1" /></SPAN>
								
								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="even">
								<TD CLASS="td_label" ALIGN="left">Ident:</TD>
								<TD ALIGN="left"><INPUT MAXLENGTH="10" SIZE="10" type="text" NAME="frm_ident" VALUE="" onChange = "this.value.trim();" />
									<SPAN STYLE = 'margin-left:20px'  CLASS="td_label">Category:&nbsp;&raquo;&nbsp;</SPAN><?php echo $cats_sel;?>
									<SPAN ID='radius' CLASS="td_label" STYLE = 'margin-left:20px; display: none;'>Radius&nbsp;&raquo;&nbsp;<INPUT NAME = 'circ_radius' VALUE= '' TYPE = 'text' SIZE = 6 MAXLENGTH = 6 onChange = "do_draw_circle();"/>&nbsp;&nbsp; <i>(mi)</i></SPAN>
									<SPAN ID='ban_text' CLASS="td_label" STYLE = 'margin-left:20px; display: none;'>Banner text:&nbsp;&raquo;&nbsp;<INPUT NAME = 'banner_text' VALUE= '' TYPE = 'text' SIZE = 24 MAXLENGTH = 64 onChange='draw_banner();'/></SPAN>

								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="left">Apply to:</TD>
								<TD ALIGN='left' CLASS="td_label"  STYLE = 'white-space:nowrap;' >
									<SPAN STYLE="margin-left: 20px;border:1px; width:20%">Base Map&nbsp;&raquo;&nbsp;<INPUT TYPE= "radio" NAME="useWith" value="box_use_with_bm" onClick = "this.form.frm_use_with_bm.value=1"/></SPAN>
									<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;<?php print get_text("Regions");?>&nbsp;&raquo;&nbsp;<INPUT TYPE= "radio" NAME="useWith" value="box_use_with_r" onClick = 	"this.form.frm_use_with_r.value=1"/></SPAN>
									<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Facilities&nbsp;&raquo;&nbsp;<INPUT TYPE= "radio" NAME="useWith" value="box_use_with_f" onClick = "this.form.frm_use_with_f.value=1"/></SPAN>
									<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Unit Exclusion Zone&nbsp;&raquo;&nbsp;<INPUT  TYPE= "radio" NAME="useWith" value="box_use_with_u_ex"  onClick = "this.form.frm_use_with_u_ex.value=1"/></SPAN>
									<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Unit Ringfence&nbsp;&raquo;&nbsp;<INPUT  TYPE= "radio" NAME="useWith" value="box_use_with_u_rf"  onClick = "this.form.frm_use_with_u_rf.value=1"/></SPAN>
								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="left"><SPAN id='type_flag2'></SPAN>:</TD>
								<TD ALIGN="left">
									<SPAN CLASS="td_label" STYLE= "margin-left:20px" >
										Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_line_color" VALUE="#FF0000"  class="color" />&nbsp;&nbsp;&nbsp;&nbsp;
										<SPAN id='line_opacity'>Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_line_opacity" VALUE="0.5" />&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
										<SPAN id='line_width' style='display: none;'>Width &raquo;&nbsp;<INPUT MAXLENGTH=2 SIZE=2 TYPE= "text" NAME="frm_line_width" VALUE="2" /> (px)</SPAN>
										<SPAN id='font_size' style='display: none;'>Font Size &raquo;&nbsp;<INPUT MAXLENGTH=2 SIZE=2 TYPE= "text" NAME="frm_font_size" VALUE="16" /> (px)</SPAN>
									</SPAN>
								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="odd" ID = 'fill_cb_tr'  >
								<TD CLASS="td_label" ALIGN="left">Filled:&nbsp;&nbsp;&nbsp;</TD>
								<TD ALIGN="left">
									<SPAN CLASS="td_label" STYLE = "margin-left: 20px;" >
									No&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_n' value = 'n'	onClick = 'do_un_checked(this.form)' CHECKED  />&nbsp;&nbsp;&nbsp;&nbsp;
									Yes&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_y' value = 'y'  onClick = 'do_checked(this.form);'/>				
									</SPAN>
								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="even" ID = 'fill_tr' STYLE = 'display:none'>
								<TD CLASS="td_label" ALIGN="left">Fill:</TD>
								<TD ALIGN="left">
									<SPAN CLASS="td_label" STYLE= "margin-left:20px" >
										Color &raquo;&nbsp;<INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_fill_color" VALUE="#FF0000"  class="color" />&nbsp;&nbsp;&nbsp;&nbsp;
										Opacity &raquo;&nbsp;<INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_fill_opacity" VALUE="0.5" />&nbsp;&nbsp;&nbsp;&nbsp;
									</SPAN>
								</TD>
							</TR>
							<TR CLASS="spacer" VALIGN="top" >
								<TD CLASS='spacer' COLSPAN="2" ALIGN="CENTER">&nbsp;</TD>
							</TR>
							<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
								<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
									<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN>
									<SPAN id='reset_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_reset();this.form.reset();'>Reset</SPAN>
									<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='this.disabled=true; JSfnCheckInput(document.mkup_add, this);'>Submit</SPAN>
								</TD>
							</TR>
							<TR CLASS="spacer" VALIGN="top" >
								<TD CLASS='spacer' COLSPAN="2" ALIGN="CENTER">&nbsp;</TD>
							</TR>
							<TR  VALIGN="baseline"CLASS="odd">
								<TD COLSPAN="2" ALIGN="center" STYLE = 'white-space:nowrap;'>
									<INPUT TYPE='hidden' NAME = '_func' VALUE='cp' />
									<INPUT TYPE='hidden' NAME = 'frm_line_status' VALUE='0' />	
									<INPUT TYPE='hidden' NAME = 'frm_line_cat_id' VALUE='0' />	
									<INPUT TYPE='hidden' NAME = 'frm_line_type' VALUE='' />
									<INPUT TYPE='hidden' NAME = 'frm_line_data' VALUE='' />
									<INPUT TYPE='hidden' NAME = 'frm_filled' VALUE='0' />
									<INPUT TYPE='hidden' NAME = 'frm_use_with_bm' VALUE='0' />
									<INPUT TYPE='hidden' NAME = 'frm_use_with_r' VALUE='0' />
									<INPUT TYPE='hidden' NAME = 'frm_use_with_f' VALUE='0' />
									<INPUT TYPE='hidden' NAME = 'frm_use_with_u_ex' VALUE='0' />
									<INPUT TYPE='hidden' NAME = 'frm_use_with_u_rf' VALUE='0' />	
								</TD>
							</TR>
						</TABLE>
					</TD>
				</TR>
			</TABLE>			
			</FORM>
		</DIV>
		<DIV id='rightcol' style='position: absolute; right: 170px;'>
			<DIV id= 'map_canvas' style = 'border: 1px outset #707070;'></DIV>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, FALSE, $allow_filedelete, 0, 0, 0, 0);
?>
		<FORM NAME='can_Form' METHOD="post" ACTION = "mmarkup.php"></FORM>
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
		var boundary = [];			//	exclusion zones array
		var bound_names = [];
		var mapWidth = <?php print get_variable('map_width');?>+20;
		var mapHeight = <?php print get_variable('map_height');?>+20;;
		$('map_canvas').style.width = mapWidth + "px";
		$('map_canvas').style.height = mapHeight + "px";
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		init_map(2, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 13, theLocale, useOSMAP, "tr");
		var bounds = map.getBounds();	
		var zoom = map.getZoom();
		var got_points = false;	// map is empty of points
		

		function onMapClick(e) {
			semic = ";";			// separator
			count++;
			if((type == "p") || (type == "l")) {
				var iconurl = "./our_icons/sm_red.png";
				icon = new baseIcon({iconUrl: iconurl});	
				var marker = new L.marker(e.latlng, {icon:icon, draggable:'true'}).on('click', onMarkerClick);
				marker.on('dragend', drag_end);
				marker.addTo(map);
				markers.push(marker);
				points.push(e.latlng);
				if(thePoly) {
					map.removeLayer(thePoly);
					}
				if(type == "p") {
					thePoly = L.polygon(points, {color: 'red'}).addTo(map);
					} else if(type == "l") {
					thePoly = L.polyline(points, {color: 'red'}).addTo(map);
					}
				} else if((type == "c") || (type == "b")) {
				var iconurl = "./our_icons/circle_dot.png";
				icon = new centeredIcon({iconUrl: iconurl});
				if(theMarker) {
					map.removeLayer(theMarker);
					theMarker = new L.marker(e.latlng, {icon:icon, draggable:'true'}).on('click', onMarkerClick);
					points.push(e.latlng);	
					theMarker.on('dragend', function (e) {
						var coords = e.target.getLatLng();
						selected_position = coords;
						});
					theMarker.addTo(map);
					do_draw_circle();
					} else {
					theMarker = new L.marker(e.latlng, {icon:icon, draggable:'true'}).on('click', onMarkerClick);
					points.push(e.latlng);						
					theMarker.on('dragend', function (e) {
						var coords = e.target.getLatLng();
						selected_position = coords;
						});					
					theMarker.addTo(map);
					do_draw_circle();
					}
				} else if(type == "e") {
				alert("Set Type of Markup first");
				}	
			};
			
		function drag_end(e) {
			var m = e.target;
			var coords = e.target.getLatLng();
			for(var n = 0; n < markers.length; n++) {	  // Find out which marker to remove
				if(markers[n] == m) {
					setTimeout(function() {
						points[n] = coords;
						if(window.thePoly) {
							map.removeLayer(window.thePoly);
							}
						if(type == "p") {
							window.thePoly = L.polygon(window.points, {color: 'red'}).addTo(map);
							} else {
							window.thePoly = L.polyline(window.points, {color: 'red'}).addTo(map);
							}						

						},100);
					break;
					}
				}
			}
			
		function onMarkerClick(e) {
			if((type == "p") || (type == "l")) {
				for(var n = 0; n < markers.length; n++) {	  // Find out which marker to remove
					if(points[n] == e.latlng) {
						map.removeLayer(markers[n]);
						break;
						}
					}
				markers.splice(n, 1);
				points.splice(n, 1);
				if(thePoly) {
					map.removeLayer(thePoly);
					}
				if(type == "p") {
					thePoly = L.polygon(points, {color: 'red'}).addTo(map);
					} else {
					thePoly = L.polyline(points, {color: 'red'}).addTo(map);
					}	
				if(markers.length == 0) {
					count = 0;
					}
				else {
					count = markers.length-1;
					}
				} else if(type == "c") {
				alert(e.latlng);
				} else {
				alert("Set Type of Markup first");
				}
			};

		map.on('click', onMapClick);
		</SCRIPT>
		</BODY>
		</HTML>
<?php
exit();