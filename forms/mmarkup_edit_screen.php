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
$checked_visible = ($row['line_status'] == 0) ? "CHECKED": "";
$checked_hidden = ($row['line_status'] == 1) ? "CHECKED": "";
$checked_basemap = ($row['use_with_bm'] == 1) ? "CHECKED": "";
$checked_regions = ($row['use_with_r'] == 1) ? "CHECKED": "";
$checked_facilities = ($row['use_with_f'] == 1) ? "CHECKED": ""; 
$checked_exclusions = ($row['use_with_u_ex'] == 1) ? "CHECKED": "";
$checked_ringfences = ($row['use_with_u_rf'] == 1) ? "CHECKED": "";
$checked_filled = ($row['filled'] == 1) ? "CHECKED": "";
$checked_unfilled = ($row['filled'] == 0) ? "CHECKED": "";
$temp = preg_split("/;/", $row['line_data']);
$banner_text = (($row['line_type'] == "b") && (($temp[1]) && ($temp[1] !=""))) ? $temp[1] : "";
$theRadius = (($row['line_type'] == "c") && (($temp[1]) && ($temp[1] != 0))) ? $temp[1] : 0;
$query_cats = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup_cats` ORDER BY `category` ASC";		
$result_cats = mysql_query($query_cats) or do_error($query_cats, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$cats_sel = "<SELECT ID='cat_list' NAME = 'frm_cat_list' onChange = 'this.form.frm_line_cat_id.value = this.options[this.selectedIndex].value;'>\n";
$cats_sel .= "<OPTION VALUE=0 >Select</OPTION>\n";
while ($row_cats = mysql_fetch_assoc($result_cats)) {
	$sel = ($row_cats['id'] == $line_cat_id) ? " SELECTED" : "";
	$cats_sel .= "<OPTION VALUE=\"{$row_cats['id']}\"" . $sel . ">" . shorten($row_cats['category'], 30) . "</OPTION>\n";
	}
$cats_sel .= "</SELECT>\n";
switch($row['line_type']) {
	case "t":
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

	
?>

<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var count = 0;
var type;
var polygon;
var polyline;
var circle;
var circleRadius;
var circleColor;
var circleOpacity;
var circleFillcolor;
var circleFillopacity;
var circleLinename;
var banner;
var theMarker;
var myMarker;
var theType = "<?php print $row['line_type'];?>";
switch(theType) {
	case "p":
		var typeInt = 1;
		break;
	case "p":
		var typeInt = 2;
		break;
	case "c":
		var typeInt = 3;
		break;
	case "t":
		var typeInt = 4;
		break;
	default:
		var typeInt = 0;
	}
		
	
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var inner_listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var r_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = new Array();
var rmarkers = [];
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
var fields = ["name",
				"bannerText"];
var medfields = ["selectType",
				"fillColor",
				"lineColor",
				"cat_list",
				"ident"];
var smallfields = ["fillOpacity",
					"lineOpacity",
					"lineWidth",
					"fontSize",
					"circRadius"];

function set_type(id) {
	if(id=="p") {
		$('radius').style.display='none';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Polygon";
		type = "p";
		} else if(id=="l") {
		$('radius').style.display='none';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Line";
		type = "l";
		} else if(id=="c") {
		$('radius').style.display='inline';
		$('ban_text').style.display='none';
		$('font_size').style.display='none';
		$('line_width').style.display='inline';
		$('type_flag').innerHTML = $('type_flag2').innerHTML = "Circle";
		type = "c";
		} else if(id=="b") {
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
	$('mainForm').style.display = "block";
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
	document.mkup_edit.frm_line_type.value = type;
	$('mainForm').style.display = 'block';
	}

function set_fieldview() {
	var filled = <?php print $filled;?>;
	if(filled == 1) {
		$('fill_tr').style.display = '';
		} else {
		$('fill_tr').style.display = 'none';
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
	for (var i = 0; i < fields.length; i++) {
		$(fields[i]).style.width = fieldwidth + "px";
		} 
	for (var i = 0; i < medfields.length; i++) {
		$(medfields[i]).style.width = medfieldwidth + "px";
		}
	for (var i = 0; i < smallfields.length; i++) {
		$(smallfields[i]).style.width = smallfieldwidth + "px";
		}
	set_type(theType);
	set_fieldview();
	set_fontsizes(viewportwidth, "fullscreen");
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
		myform.submit(); 
		}			// end if/else errormsg 
	}		// end function JSfnCheckInput	

function do_reset() {
	var description = document.mkup_edit.frm_name.value;
	if(polyline) {map.removeLayer(polyline);}
	if(polygon) {map.removeLayer(polygon);}
	if(circle) {map.removeLayer(circle);}
	if(banner) {map.removeLayer(banner);}	
	if(myMarker) {map.removeLayer(myMarker);}
	if(markers.length > 0) {
		for(var i = 0; i < markers.length; i++) {
			map.removeLayer(markers[i]);
			}
		markers = [];
		points = [];
		}
	$('type_flag').innerHTML = "";
	$('selectType').style.display = 'block';
	$('mainForm').style.display = 'none';
	$('selectType').selectedIndex = 0;
	document.mkup_edit.frm_name.value = description;
	}
</SCRIPT>

</HEAD>
<BODY onLoad = "set_size(); ck_frames();">
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10, 10/23/12 -->		
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<A NAME='top'>	
			<FORM ID='mkup_Edit_form' NAME="mkup_edit" METHOD="post" ACTION="mmarkup.php?goedit=true">		
			<TABLE id='markup_form_table' BORDER="0" ALIGN="center" width='100%'>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
						<SPAN CLASS='text_green text_biggest'>Edit <SPAN id='type_flag'></SPAN> "<?php print $row['line_name'];?>" Map Markup</SPAN>
						<BR />
						<SPAN CLASS='text_white'>click map to add points - drag icons to move points</SPAN>
						<BR />
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>
				<TR CLASS="even" VALIGN="top" >
					<TD COLSPAN="2" ALIGN="CENTER">
						<SELECT ID = 'selectType' NAME='markup_type' style='display: none;' onChange = "change_type(this.value)">
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
							<TR VALIGN="baseline" CLASS="odd">
								<TD CLASS="td_label text text_left" ALIGN="left">Description:</TD>
								<TD CLASS='td_data text text_left'>
									<INPUT ID='name' MAXLENGTH="32" SIZE="32" type="text" NAME="frm_name" VALUE="<?php print $row['line_name'];?>" onChange = "this.value.trim();" />
									<SPAN STYLE = 'margin-left:20px' CLASS="td_label text text_left" >Visible&nbsp;&raquo;&nbsp;</SPAN>
									<SPAN STYLE = 'margin-left:10px'>Yes&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'rb_line_is_vis' onClick = "document.mkup_Edit_form.rb_line_not_vis.checked = false;document.c.frm_line_status.value=0" <?php print $checked_visible;?> /></SPAN>
									<SPAN STYLE = 'margin-left:20px'>No&nbsp;&raquo;&nbsp;<INPUT TYPE='radio' NAME = 'rb_line_not_vis' onClick = "document.mkup_Edit_form.rb_line_is_vis.checked = false;document.c.frm_line_status.value=1" <?php print $checked_hidden;?> /></SPAN>
								
								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="even">
								<TD CLASS="td_label text text_left" ALIGN="left">Ident:</TD>
								<TD CLASS='td_label text text_left'>
									<INPUT ID='ident' MAXLENGTH="10" SIZE="10" type="text" NAME="frm_ident" VALUE="<?php print $row['line_name'];?>" onChange = "this.value.trim();" />
									<SPAN STYLE = 'margin-left:20px'  CLASS="td_label text text_left">Category:&nbsp;&raquo;&nbsp;</SPAN><?php echo $cats_sel;?>
									<SPAN ID='radius' CLASS="td_label text text_left" STYLE = 'margin-left:20px; display: none;'>Radius&nbsp;&raquo;&nbsp;<INPUT ID='circRadius' NAME = 'circ_radius' VALUE= '<?php print $theRadius;?>' TYPE = 'text' SIZE = 6 MAXLENGTH = 6 />&nbsp;&nbsp; <i>(mi)</i></SPAN>
									<SPAN ID='ban_text' CLASS="td_label text text_left" STYLE = 'margin-left:20px; display: none;'>Banner text:&nbsp;&raquo;&nbsp;<INPUT ID='bannerText' NAME = 'banner_text' VALUE= '<?php print $banner_text;?>' TYPE = 'text' SIZE = 24 MAXLENGTH = 64 onChange='draw_banner();'/></SPAN>

								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label text text_left" ALIGN="left">Apply to:</TD>
								<TD CLASS="td_label text text_left" STYLE = 'white-space:nowrap;' >
									<SPAN STYLE="margin-left: 20px;border:1px; width:20%">Base Map&nbsp;&raquo;&nbsp;<INPUT TYPE= "radio" NAME="useWith" value="box_use_with_bm" onClick = "this.form.frm_use_with_bm.value=1" <?php print $checked_basemap;?>/></SPAN>
									<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;<?php print get_text("Regions");?>&nbsp;&raquo;&nbsp;<INPUT TYPE= "radio" NAME="useWith" value="box_use_with_r" onClick = 	"this.form.frm_use_with_r.value=1" <?php print $checked_regions;?>/></SPAN>
									<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Facilities&nbsp;&raquo;&nbsp;<INPUT TYPE= "radio" NAME="useWith" value="box_use_with_f" onClick = "this.form.frm_use_with_f.value=1" <?php print $checked_facilities;?>/></SPAN>
									<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Unit Exclusion Zone&nbsp;&raquo;&nbsp;<INPUT  TYPE= "radio" NAME="useWith" value="box_use_with_u_ex"  onClick = "this.form.frm_use_with_u_ex.value=1" <?php print $checked_exclusions;?>/></SPAN>
									<SPAN STYLE="border:1px; width:20%">&nbsp;&nbsp;Unit Ringfence&nbsp;&raquo;&nbsp;<INPUT  TYPE= "radio" NAME="useWith" value="box_use_with_u_rf"  onClick = "this.form.frm_use_with_u_rf.value=1" <?php print $checked_ringfences;?>/></SPAN>
								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label text text_left" ALIGN="left"><SPAN id='type_flag2'></SPAN>:</TD>
								<TD CLASS="td_label text text_left">
									<SPAN CLASS="td_data text text_left" STYLE= "margin-left:20px" >
										Color &raquo;&nbsp;<INPUT ID='lineColor' MAXLENGTH="8" SIZE="8" type="text" NAME="frm_line_color" VALUE="<?php print $row['line_color'];?>"  class="color" />&nbsp;&nbsp;&nbsp;&nbsp;
										<SPAN id='line_opacity'>Opacity &raquo;&nbsp;<INPUT ID='lineOpacity' MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_line_opacity" VALUE="<?php print $row['line_opacity'];?>" />&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
										<SPAN id='line_width' style='display: none;'>Width &raquo;&nbsp;<INPUT ID='lineWidth' MAXLENGTH=2 SIZE=2 TYPE= "text" NAME="frm_line_width" VALUE="<?php print $row['line_width'];?>" /> (px)</SPAN>
										<SPAN id='font_size' style='display: none;'>Font Size &raquo;&nbsp;<INPUT ID='fontSize' MAXLENGTH=2 SIZE=2 TYPE= "text" NAME="frm_font_size" VALUE="<?php print $row['line_width'];?>" /> (px)</SPAN>
									</SPAN>
								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="odd" ID = 'fill_cb_tr'  >
								<TD CLASS="td_label text text_left" ALIGN="left">Filled:&nbsp;&nbsp;&nbsp;</TD>
								<TD CLASS="td_label text text_left">
									<SPAN CLASS="td_data text text_left" STYLE = "margin-left: 20px;" >
									No&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_n' value = 'n'	onClick = 'do_un_checked(this.form)' <?php print $checked_unfilled;?>/>&nbsp;&nbsp;&nbsp;&nbsp;
									Yes&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_filled_y' value = 'y'  onClick = 'do_checked(this.form);' <?php print $checked_filled;?>/>				
									</SPAN>
								</TD>
							</TR>
							<TR VALIGN="baseline" CLASS="even" ID = 'fill_tr' STYLE = 'display:none'>
								<TD CLASS="td_label text text_left" ALIGN="left">Fill:</TD>
								<TD CLASS="td_label text text_left">
									<SPAN CLASS="td_data text text_left" STYLE= "margin-left:20px" >
										Color &raquo;&nbsp;<INPUT ID='fillColor' MAXLENGTH="8" SIZE="8" type="text" NAME="frm_fill_color" VALUE="<?php print $row['fill_color'];?>"  class="color" />&nbsp;&nbsp;&nbsp;&nbsp;
										Opacity &raquo;&nbsp;<INPUT ID='fillOpacity' MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_fill_opacity" VALUE="<?php print $row['fill_opacity'];?>" />&nbsp;&nbsp;&nbsp;&nbsp;
									</SPAN>
								</TD>
							</TR>
							<TR CLASS="spacer" VALIGN="top" >
								<TD CLASS='spacer' COLSPAN="2" ALIGN="CENTER"></TD>
							</TR>
							<TR CLASS="odd" VALIGN='baseline'>
								<TD COLSPAN=2 CLASS="td_label text text_left">
									<A CLASS="td_label text text_left" HREF="#" TITLE="Delete Markup from system">Remove Map Markup</A>:&nbsp;
									<INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove">
								</TD>
							</TR>
						</TABLE>
					</TD>
				</TR>
				<TR  VALIGN="baseline"CLASS="odd">
					<TD COLSPAN="2" ALIGN="center" STYLE = 'white-space:nowrap;'>
						<INPUT TYPE='hidden' NAME = 'frm_id' VALUE='<?php print $row['id'];?>' />
						<INPUT TYPE='hidden' NAME = 'frm_line_status' VALUE='<?php print $row['line_status'];?>' />	
						<INPUT TYPE='hidden' NAME = 'frm_line_cat_id' VALUE='<?php print $row['line_cat_id'];?>' />	
						<INPUT TYPE='hidden' NAME = 'frm_line_type' VALUE='<?php print $row['line_type'];?>' />
						<INPUT TYPE='hidden' NAME = 'frm_line_data' VALUE='<?php print $row['line_data'];?>' />
						<INPUT TYPE='hidden' NAME = 'frm_filled' VALUE='<?php print $filled;?>' />
						<INPUT TYPE='hidden' NAME = 'frm_use_with_bm' VALUE='<?php print $row['use_with_bm'];?>' />
						<INPUT TYPE='hidden' NAME = 'frm_use_with_r' VALUE='<?php print $row['use_with_r'];?>' />
						<INPUT TYPE='hidden' NAME = 'frm_use_with_f' VALUE='<?php print $row['use_with_f'];?>' />
						<INPUT TYPE='hidden' NAME = 'frm_use_with_u_ex' VALUE='<?php print $row['use_with_u_ex'];?>' />
						<INPUT TYPE='hidden' NAME = 'frm_use_with_u_rf' VALUE='<?php print $row['use_with_u_rf'];?>' />	
					</TD>
				</TR>
			</TABLE>			
			</FORM>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='reset_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='do_reset(); document.mkup_edit.reset();'><?php print get_text("Reset");?><BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
				<SPAN id='sub_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='JSfnCheckInput(document.mkup_edit, this.id);'><?php print get_text("Submit");?><BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id= 'map_canvas' style = 'border: 1px outset #707070;'></DIV>
			<SPAN id='map_caption' CLASS='text_blue text text_bold' style='width: 100%; text-align: center; display: block;'><?php print get_variable('map_caption');?></SPAN><BR />
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
		var boundary = [];			//	exclusion zones array
		var bound_names = [];
		
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
		for (var i = 0; i < fields.length; i++) {
			$(fields[i]).style.width = fieldwidth + "px";
			} 
		for (var i = 0; i < medfields.length; i++) {
			$(medfields[i]).style.width = medfieldwidth + "px";
			}
		for (var i = 0; i < smallfields.length; i++) {
			$(smallfields[i]).style.width = smallfieldwidth + "px";
			}			
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		var initZoom = <?php print get_variable('def_zoom');?>;
		init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
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
					<?php print $filled;?>,
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
			if(polygon) { map.removeLayer(polygon);}
			if(!linedata) {return;}
			var path = new Array();
			var thelineData = linedata.split(';');
			for (i = 0; i < thelineData.length; i++) { 
				var theCoords = thelineData[i].split(',');
				var theLatLng = new L.LatLng(theCoords[0], theCoords[1]);
				path[i] = theLatLng;
				points.push(theLatLng);	
				var iconurl = "./our_icons/sm_red.png";
				var icon = new baseIcon({iconUrl: iconurl});	
				var marker = new L.marker(theLatLng, {icon:icon, draggable:'true'}).on('click', onMarkerClick);
				marker.on('dragend', drag_end);
				marker.addTo(map);
				markers.push(marker);
				}
			var polygon = L.polygon([path],{
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
			if(polyline) { map.removeLayer(polyline);}
			if(!linedata) {return;}
			var path = new Array();
			var thelineData = linedata.split(';');
			for (i = 0; i < thelineData.length; i++) { 
				var theCoords = thelineData[i].split(',');
				var theLatLng = new L.LatLng(theCoords[0], theCoords[1]);
				path[i] = theLatLng;
				points.push(theLatLng);
				var iconurl = "./our_icons/sm_red.png";
				var icon = new baseIcon({iconUrl: iconurl});	
				var marker = new L.marker(theLatLng, {icon:icon, draggable:'true'}).on('click', onMarkerClick);
				marker.on('dragend', drag_end);
				marker.addTo(map);
				markers.push(marker);
				}
			var polyline = L.polyline(path,{
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

		function draw_circle(linename, linedata, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity, filled, theID) {
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

		function draw_banner(linename, linedata, width, color, theID) {        // Create the banner - 6/5/2013
			if(banner) { map.removeLayer(banner);}
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
			banner = L.marker(point, {
				icon: L.divIcon({
					className: "textLabelclass",
					html: html
				}),
				draggable: false
			});
			banner.addTo(map);
			var point = new L.LatLng(lat, lng);
			var iconurl = "./markers/pin.png";
			var icon = new centeredIcon({iconUrl: iconurl});
			myMarker = new L.marker(point, {icon:icon, draggable:'true'});
			myMarker.on('dragend', banner_drag_end);
			myMarker.addTo(map);
			points.push(point);	
			map.setView(point, 11);
			}				// end function draw Banner()

		function onMapClick(e) {
			semic = ";";			// separator
			count++;
			if((type == "p") || (type == "l")) {
				var iconurl = "./our_icons/sm_red.png";
				var icon = new baseIcon({iconUrl: iconurl});	
				var marker = new L.marker(e.latlng, {icon:icon, draggable:'true'}).on('click', onMarkerClick);
				marker.on('dragend', drag_end);
				marker.addTo(map);
				markers.push(marker);
				points.push(e.latlng);
				var color = "#" + document.mkup_edit.frm_line_color.value;
				var width = document.mkup_edit.frm_line_width.value;
				var opacity = document.mkup_edit.frm_line_opacity.value;
				var fillcolor = "#"+ document.mkup_edit.frm_fill_color.value;
				var filled = document.mkup_edit.frm_filled.value;
				var fillopacity = document.mkup_edit.frm_fill_opacity.value;
				if(polyline) {
					map.removeLayer(polyline);
					}
				if(polygon) {
					map.removeLayer(polygon);
					}
				if(type == "p") {
					polygon = L.polygon(points,{
					color: color,
					weight: width,
					opacity: opacity,
					fill: filled,
					fillColor: fillcolor,
					fillOpacity: fillopacity,
					stroke: true
					}).addTo(map);
					} else if(type == "l") {
					polyline = L.polyline(points,{
					color: color,
					weight: width,
					opacity: opacity,
					stroke: true
					}).addTo(map);					
					}
				} else if((type == "c") || (type == "b")) {
				var iconurl = "./our_icons/circle_dot.png";
				var icon = new centeredIcon({iconUrl: iconurl});
				if(circle || banner) {
					if(type == "c") {
						map.removeLayer(circle);
						map.removeLayer(myMarker);
						points.length=0;
						points.push(e.latlng);	
						var thelat = points[0].lat.toFixed(6);
						var thelng = points[0].lng.toFixed(6);
						var theRadius = document.mkup_edit.circ_radius.value.trim();
						if(is_ok_radius (theRadius)){
							var theLineData = (thelat + "," + thelng + ";" + theRadius);
							var theColor = "#" + document.mkup_edit.frm_line_color.value;
							draw_circle(document.mkup_edit.frm_name.value, 
									theLineData,
									theColor, 
									document.mkup_edit.frm_line_width.value,
									document.mkup_edit.frm_line_opacity.value,
									"#" + document.mkup_edit.frm_fill_color.value,
									document.mkup_edit.frm_fill_opacity.value,
									document.mkup_edit.frm_filled.value,
									<?php print $row['id'];?>);
							}
						}
					if(type == "b") {
						map.removeLayer(banner);
						map.removeLayer(myMarker);
						points.length=0;
						points.push(e.latlng);	
						var thelat = points[0].lat.toFixed(6);
						var thelng = points[0].lng.toFixed(6);
						var theBanner = document.mkup_edit.banner_text.value.trim();
						var theLineData = (thelat + "," + thelng + ";" + theBanner);
						var theColor = "#" + document.mkup_edit.frm_line_color.value;
						draw_banner(document.mkup_edit.frm_name.value,
								theLineData,
								document.mkup_edit.frm_line_width.value,
								theColor, 
								<?php print $row['id'];?>);
						}
					} else {
					if(type == "c") {
						points.push(e.latlng);	
						var thelat = points[0].lat.toFixed(6);
						var thelng = points[0].lng.toFixed(6);
						var theRadius = document.mkup_edit.circ_radius.value.trim();
						if(is_ok_radius (theRadius)){
							var theLineData = (thelat + "," + thelng + ";" + theRadius);
							var theColor = "#" + document.mkup_edit.frm_line_color.value;
							draw_circle(document.mkup_edit.frm_name.value, 
									theLineData,
									theColor, 
									document.mkup_edit.frm_line_width.value,
									document.mkup_edit.frm_line_opacity.value,
									"#" + document.mkup_edit.frm_fill_color.value,
									document.mkup_edit.frm_fill_opacity.value,
									document.mkup_edit.frm_filled.value,
									<?php print $row['id'];?>);
							}
						}
					if(type == "b") {
						points.push(e.latlng);	
						var thelat = points[0].lat.toFixed(6);
						var thelng = points[0].lng.toFixed(6);
						var theBanner = document.mkup_edit.banner_text.value.trim();
						var theLineData = (thelat + "," + thelng + ";" + theBanner);
						var theColor = "#" + document.mkup_edit.frm_line_color.value;
						if(document.mkup_edit.frm_name.value != "") {
							draw_banner(document.mkup_edit.frm_name.value,
									theLineData,
									document.mkup_edit.frm_line_width.value,
									theColor, 
									<?php print $row['id'];?>);
							}
						}
					}
				} else if(type == "e") {
				alert("Set Type of Markup first");
				}	
			};
			
		function circ_drag_end(e) {
			var m = e.target;
			var coords = e.target.getLatLng();
			map.removeLayer(circle);
			map.removeLayer(myMarker);
			points.length=0;
			points.push(coords);	
			var thelat = points[0].lat.toFixed(6);
			var thelng = points[0].lng.toFixed(6);
			var theRadius = document.mkup_edit.circ_radius.value.trim();
			if(is_ok_radius (theRadius)){
				var theLineData = (thelat + "," + thelng + ";" + theRadius);
				var theColor = "#" + document.mkup_edit.frm_line_color.value;
				draw_circle(document.mkup_edit.frm_name.value, 
						theLineData,
						theColor, 
						document.mkup_edit.frm_line_width.value,
						document.mkup_edit.frm_line_opacity.value,
						"#" + document.mkup_edit.frm_fill_color.value,
						document.mkup_edit.frm_fill_opacity.value,
						document.mkup_edit.frm_filled.value,
						<?php print $row['id'];?>);
				}
			}

		function banner_drag_end(e) {
			var m = e.target;
			var coords = e.target.getLatLng();
			map.removeLayer(banner);
			map.removeLayer(myMarker);
			points.length=0;
			points.push(coords);
			var thelat = points[0].lat.toFixed(6);
			var thelng = points[0].lng.toFixed(6);
			var theBanner = document.mkup_edit.banner_text.value.trim();
			var theLineData = (thelat + "," + thelng + ";" + theBanner);
			var theColor = "#" + document.mkup_edit.frm_line_color.value;
			if(document.mkup_edit.frm_name.value != "") {
				draw_banner(document.mkup_edit.frm_name.value,
						theLineData,
						document.mkup_edit.frm_line_width.value,
						theColor, 
						<?php print $row['id'];?>);
				}
			}			

			
		function drag_end(e) {
			var m = e.target;
			var coords = e.target.getLatLng();
			for(var n = 0; n < markers.length; n++) {	  // Find out which marker to remove
				if(markers[n] == m) {
					setTimeout(function() {
						points[n] = coords;
						if(window.polygon) {
							map.removeLayer(window.polygon);
							}
						if(window.polyline) {
							map.removeLayer(window.polyline);
							}
						if(type == "p") {
							window.polygon = L.polygon(window.points, {color: 'red'}).addTo(map);
							} else {
							window.polyline = L.polyline(window.points, {color: 'red'}).addTo(map);
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
				var color = "#<?php print $row['line_color'];?>";
				var width = <?php print $row['line_width'];?>;
				var opacity = <?php print $line_opacity;?>;
				var fillcolor = "#<?php print $fill_color;?>";
				var filled = <?php print $filled;?>;
				var fillcolor = "#<?php print $fill_color;?>";
				var fillopacity = <?php print $fill_opacity;?>;
				if(polygon) {
					map.removeLayer(polygon);
					}
				if(polyline) {
					map.removeLayer(polyline);
					}
				if(type == "p") {
					polygon = L.polygon(points,{
					color: color,
					weight: width,
					opacity: opacity,
					fill: filled,
					fillColor: fillcolor,
					fillOpacity: fillopacity,
					stroke: true
					}).addTo(map);
					} else if(type == "l") {
					polyline = L.polyline(points,{
					color: color,
					weight: width,
					opacity: opacity,
					stroke: true
					}).addTo(map);					
					}
				if(markers.length == 0) {
					count = 0;
					}
				else {
					count = markers.length-1;
					}
				} else if(type == "c") {
//					do nothing
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
