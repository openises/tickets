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

window.onload = function(){set_size();}
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
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	load_exclusions();
	load_ringfences();
	load_basemarkup();
	load_groupbounds();
	map.invalidateSize();
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
<BODY onLoad='set_size();'>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id='outer' style='position: absolute; left: 0px;'>
		<DIV id='leftcol' style='position: absolute; left: 10px; z-index: 1;'>
			<A NAME='top'>
			<FORM NAME= "loc_edit_Form" METHOD="POST" ACTION="warn_locations.php?goedit=true">
			<TABLE BORDER="0" ID='editform' WIDTH='98%'>
				<TR>
					<TD ALIGN='center' COLSPAN='2'>
						<FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>&nbsp;Edit Warn Location '<?php print $row['title'];?>' data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></FONT><BR /><BR />
						<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Location Name - fill in with Name of location">Name</A>:&nbsp;<font color='red' size='-1'>*</font></TD>
					<TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="<?php print $row['title'] ;?>" /></TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN='2'>&nbsp;</TD>
				</TR>

<?php
				$dis_rmv = " ENABLED";
?>
				<TR CLASS='even'>
					<TD CLASS="td_label"><A CLASS="td_label" HREF="#" TITLE="Street Address - type in street address in fields or click location on map ">Location</A>:</TD>
					<TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="<?php print $row['street'] ;?>"  MAXLENGTH="61"></TD>
				</TR>
				<TR CLASS='odd'>
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:
						&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.loc_edit_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button>
					</TD>
					<TD>
						<INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print $row['city'] ;?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:
						&nbsp;&nbsp;<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print $row['state'] ;?>" MAXLENGTH="<?php print $st_size;?>">
					</TD>
				</TR>
				<TR class='even'>
					<TD class='td_label'>
						<A CLASS="td_label" HREF="#" TITLE="Select Warning Type">Warning Type</A>:
					</TD>
					<TD CLASS='td_data' COLSPAN=99>
						<SELECT NAME='frm_loc_type'>
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
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>	
					<TD COLSPAN=3>
						<TEXTAREA NAME="frm_descr" COLS=60 ROWS=2><?php print $row['description'];?></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label">
						<SPAN onClick = 'javascript: do_coords(document.loc_edit_Form.frm_lat.value ,document.loc_edit_Form.frm_lng.value  )' ><A HREF="#" TITLE="Latitude and Longitude - set from map click">
						Lat/Lng</A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;
						<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.loc_edit_Form);'>
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
?>
								<SPAN ID = 'usng_link' onClick = 'do_usng_conv(loc_edit_Form)'>USNG:</SPAN><INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $usng_val;?>' SIZE=19 disabled />
<?php 	
								break;

							case "1":
?> 
								<SPAN ID = 'osgb_link'>OSGB:</SPAN><INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $osgb_val;?>' SIZE=19 disabled />
<?php 
								break;

							default:
?> 
								&nbsp;UTM:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $utm_val;?>' SIZE=19 disabled />
<?php 		
							}	//	end switch
?>
					</TD>
				</TR>
				<TR>
					<TD>&nbsp;</TD>
				</TR>
				<TR CLASS="odd" VALIGN='baseline'>
					<TD CLASS="td_label">
						<A CLASS="td_label" HREF="#" TITLE="Delete Location from system">Remove Location</A>:&nbsp;
					</TD>
					<TD>
						<INPUT TYPE="checkbox" VALUE="yes" NAME="frm_remove" <?php print $dis_rmv; ?>>
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>
				<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
					<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
						<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN>
						<SPAN id='reset_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='map_reset();'>Reset</SPAN>
						<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate(document.loc_edit_form);'>Submit</SPAN>
					</TD>
				</TR>
			</TABLE>
			<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
			<INPUT TYPE="hidden" NAME = "frm_lat" VALUE="<?php print $row['lat'] ;?>"/>
			<INPUT TYPE="hidden" NAME = "frm_lng" VALUE="<?php print $row['lng'] ;?>"/>
			<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>
			</FORM>
		</DIV>
		<DIV id='rightcol' style='position: absolute; right: 170px; z-index: 1;'>
			<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
			<SPAN style='text-align: center;'><B>Click Map to revise location</B></SPAN>
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
var latLng;
var boundary = [];			//	exclusion zones array
var bound_names = [];
var mapWidth = <?php print get_variable('map_width');?>+20;
var mapHeight = <?php print get_variable('map_height');?>+20;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
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
