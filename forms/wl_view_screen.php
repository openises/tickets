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
window.onresize=function(){set_size()};

window.onload = function(){set_size();};
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var mapWidth;
var mapHeight;
var colwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var wlmarkers = [];
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
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	map.invalidateSize();
	}

</SCRIPT>
<?php
$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]warnings` WHERE `id`= " . $id . " LIMIT 1";	// 1/19/2013
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));
$lat = $row['lat'];
$lng = $row['lng'];
$coords =  $row['lat'] . "," . $row['lng'];		// for UTM			
?>
</HEAD>
<BODY onLoad='set_size();'>
<A NAME='top'>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></DIV>
	<DIV id='outer' style='position: absolute; left: 0px;'>
		<DIV id='leftcol' style='position: absolute; left: 10px;'>
			<FONT CLASS="header">Warn Location'<?php print $row['title'] ;?>' Data</FONT> (#<?php print $row['id'];?>) <BR /><BR />
			<TABLE BORDER=0 ID='view_location' STYLE='display: block'>
				<TR CLASS = "even">
					<TD CLASS="td_label"><?php print get_text("Name"); ?>: </TD>
					<TD><?php print $row['title'];?></TD>
				</TR>
				<TR CLASS = 'odd'>
					<TD CLASS="td_label"><?php print get_text("Location"); ?>: </TD>
					<TD><?php print $row['street'] ;?></TD>
				</TR>
				<TR CLASS = 'even'>
					<TD CLASS="td_label"><?php print get_text("City"); ?>: &nbsp;&nbsp;&nbsp;&nbsp;</TD>
					<TD><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label"><?php print get_text("Description"); ?>: </TD>
					<TD><?php print $row['description'];?></TD>
				</TR>
				<TR CLASS = 'odd'>
					<TD CLASS="td_label">As of:</TD>	
					<TD><?php print loc_format_date(strtotime($row['_on'])); ?></TD>
				</TR>
<?php
				if (my_is_float($lat)) {
?>		
					<TR CLASS = "even">
						<TD CLASS="td_label"  onClick = 'javascript: do_coords(<?php print "$lat,$lng";?>)'><U>Lat/Lng</U>:</TD>
						<TD><?php print get_lat($lat);?> <?php print get_lng($lng);?>&nbsp;
<?php
							$usng_val = LLtoUSNG($row['lat'], $row['lng']);
							$osgb_val = LLtoOSGB($row['lat'], $row['lng']) ;
							$utm_val = toUTM("{$row['lat']}, {$row['lng']}");

							$locale = get_variable('locale');
							switch($locale) { 
								case "0":
?>
									&nbsp;USNG: 
<?php 
									print $usng_val;
									break;
									
								case "1":
?>
									&nbsp;OSGB: 
<?php
									print $osgb_val;
									break;
									
								default:
?>
									&nbsp;UTM: 
<?php								
									print $utm_val;
								}		// end switch()
?>
						</TD>
					</TR>
<?php
					}		// end if (my_is_float($lat))

?>
				<TR>
					<TD COLSPAN=2>&nbsp;</TD>
				</TR>
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
?>
			</TABLE>
		</DIV>
		<DIV id='rightcol' style='position: absolute; right: 170px; z-index: 1;'>
			<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "warn_locations.php"></FORM>
<FORM NAME="to_edit_Form" METHOD="post" ACTION = "warn_locations.php?edit=true&id=<?php print $id; ?>"></FORM>
<A NAME="bottom" /> 
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
<SCRIPT>
var latLng;
var mapWidth = <?php print get_variable('map_width');?>+20;
var mapHeight = <?php print get_variable('map_height');?>+20;;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", 13, theLocale, useOSMAP, "tr");
map.setView([<?php print $lat;?>, <?php print $lng;?>], 13);
var bounds = map.getBounds();	
var zoom = map.getZoom();
<?php
do_kml();
?>
</SCRIPT>
</BODY>
</HTML>