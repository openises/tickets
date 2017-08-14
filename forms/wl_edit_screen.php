<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
?>
<SCRIPT>
window.onresize=function(){set_size();}
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
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
var fields = ["name",
			"street",
			"description"];
var medfields = ["city",
				"type"];
var smallfields = ["show_lat",
					"show_lng",
					"grid",
					"state"];

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
	mapHeight = mapWidth * .9;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	fieldwidth = colwidth * .6;
	medfieldwidth = colwidth * .3;		
	smallfieldwidth = colwidth * .2;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
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
	load_exclusions();
	load_ringfences();
	load_basemarkup();
	load_groupbounds();
	map.invalidateSize();
	set_fontsizes(viewportwidth, "fullscreen");
	}

function contains(array, item) {
	for (var i = 0, I = array.length; i < I; ++i) {
		if (array[i] == item) return true;
		}
	return false;
	}

function validate(theForm) {
	theForm.submit();
	}				// end function validate(theForm)

</SCRIPT>
<?php

$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT * FROM $GLOBALS[mysql_prefix]warnings WHERE id=$id";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= mysql_fetch_assoc($result);

$lat = $row['lat'];
$lng = $row['lng'];
?>
</HEAD>
<BODY>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<A NAME='top'>
			<FORM NAME= "loc_edit_Form" METHOD="POST" ACTION="warn_locations.php?goedit=true">
			<TABLE BORDER="0" ID='editform' WIDTH='98%'>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='3'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='3'>
						<SPAN CLASS='text_green text_biggest'>Edit <?php print get_text("Warn Location"); ?> '<?php print $row['title'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></SPAN>
						<BR />
						<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
						<BR />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Location Name - fill in with Name of location">Name</A>:&nbsp;<font color='red' size='-1'>*</font></TD>
					<TD COLSPAN=3><INPUT ID='name' CLASS='text' MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['title'] ;?>" /></TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN='2'></TD>
				</TR>

<?php
				$dis_rmv = " ENABLED";
?>
				<TR CLASS='even'>
					<TD CLASS="td_label text"><A CLASS="td_label text" HREF="#" TITLE="Street Address - type in street address in fields or click location on map ">Location</A>:</TD>
					<TD><INPUT ID='street' CLASS='text' SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61"></TD>
				</TR>
				<TR CLASS='odd'>
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:
						&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.loc_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button>
					</TD>
					<TD CLASS='td_data text'>
						<INPUT ID='city' SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label text" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:
						&nbsp;&nbsp;<INPUT CLASS='text' ID='state' SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>">
					</TD>
				</TR>
				<TR class='even'>
					<TD class='td_label text'>
						<A CLASS="td_label text" HREF="#" TITLE="Select Warning Type">Warning Type</A>:
					</TD>
					<TD CLASS='td_data text' COLSPAN=99>
						<SELECT ID='type' NAME='frm_loc_type'>
<?php
							$warn_types = array();
							$l_types = $GLOBALS['LOC_TYPES'];
							foreach($l_types as $val) {
								$warn_types[$val] = $GLOBALS['LOC_TYPES_NAMES'][$val];
								}
							foreach ($warn_types as $key => $value) {								// 1/9/09
								$sel = ($row['loc_type']==$key)? " SELECTED": "";					// 9/11/09
								print "\t\t\t\t<OPTION VALUE={$key}{$sel}>{$warn_types[$key]}</OPTION>\n";
								}
?>
						</SELECT>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>	
					<TD COLSPAN=3>
						<TEXTAREA CLASS='text' ID='description' NAME="frm_descr" COLS=60 ROWS=2><?php print $row['description'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text">
						<SPAN CLASS='td_label text' TITLE="Latitude and Longitude - set from map click" onClick = 'javascript: do_coords(document.loc_edit_Form.frm_lat.value ,document.loc_edit_Form.frm_lng.value  )' ><u>Lat/Lng</u></SPAN>:&nbsp;&nbsp;
						<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.loc_edit_Form);'>
					</TD>
					<TD CLASS='td_data text' COLSPAN=3>
						<INPUT ID='show_lat' CLASS='text' TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
						<INPUT ID='show_lng' CLASS='text' TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;
					</TD>
				</TR>
<?php
				$usng_val = LLtoUSNG($row['lat'], $row['lng']);
				$osgb_val = LLtoOSGB($row['lat'], $row['lng']) ;
				$utm_val = toUTM("{$row['lat']}, {$row['lng']}");
				$locale = get_variable('locale');
				switch($locale) { 
					case "0":
						$label = "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(loc_edit_Form)' style='font-weight: bold;'>USNG:</SPAN>";
						$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_ngs' VALUE='" . $usng_val . "' disabled />";
						break;
						
					case "1":
						$label = "<SPAN ID = 'osgb_link' style='font-weight: bold;'>OSGB:</SPAN>";
						$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_ngs' VALUE='" . $osgb_val . "' disabled />";
						break;
						
					default:
						$label = "<SPAN ID = 'utm_link' style='font-weight: bold;'>UTM:</SPAN>";
						$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_ngs' VALUE='" . $utm_val . "' disabled />";

					}
?>
				<TR CLASS = "odd">
					<TD CLASS="td_label text">
						<?php print $label;?>
					</TD>
					<TD CLASS='td_data text' COLSPAN=3>
						<?php print $input;?>					
					</TD>
				</TR>
				<TR>
					<TD>&nbsp;</TD>
				</TR>
				<TR CLASS="odd" VALIGN='baseline'>
					<TD CLASS="td_label text">
						<A CLASS="td_label text" HREF="#" TITLE="Delete Location from system">Remove Location</A>:&nbsp;
					</TD>
					<TD CLASS='td_label text'>
						<INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
					</TD>
				</TR>
				<TR>
					<TD COLSPAN=99>&nbsp;</TD>
				</TR>
			</TABLE>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
			<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
			<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
			</FORM>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='reset_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='map_reset();'><?php print get_text("Reset");?><BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
				<SPAN id='sub_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='validate(document.loc_edit_Form);'><?php print get_text("Submit");?><BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
			<SPAN CLASS='td_label text' style='text-align: center;'><B>Click Map to revise location</B></SPAN>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "warn_locations.php"></FORM>
<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
<SCRIPT>
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
mapHeight = mapWidth * .9;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
fieldwidth = colwidth * .6;
medfieldwidth = colwidth * .3;		
smallfieldwidth = colwidth * .2;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = colwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
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
load_exclusions();
load_ringfences();
load_basemarkup();
load_groupbounds();
set_fontsizes(viewportwidth, "fullscreen");
var latLng;
var boundary = [];			//	exclusion zones array
var bound_names = [];
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
var bounds = map.getBounds();	
var zoom = map.getZoom();
var doReverse = <?php print intval(get_variable('reverse_geo'));?>;
function onMapClick(e) {
	if(doReverse == 0) {return;}
	if(marker) {map.removeLayer(marker); }
	var iconurl = "./our_icons/yellow.png";
	icon = new baseIcon({iconUrl: iconurl});	
    marker = new L.marker(e.latlng, {id:1, icon:icon, draggable:'true'});
    marker.addTo(map);
	newGetAddress(e.latlng, "we");
	};

map.on('click', onMapClick);
<?php
do_kml();
?>
</SCRIPT>
</BODY>
</HTML>
