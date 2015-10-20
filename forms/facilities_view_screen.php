<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;	//	3/15/11
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";	//	3/15/11
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";		//	3/15/11
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;	//	3/15/11
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";	//	3/15/11
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";	//	3/15/11	
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;	//	3/15/11
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";	//	3/15/11
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";	//	3/15/11
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
?>
<SCRIPT>
window.onresize=function(){set_size()};

window.onload = function(){set_size();};

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
	map.invalidateSize();
	mapWidth = viewportwidth * .40;
	mapHeight = mapWidth * .9;
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
	$('titlebar').style.width = outerwidth + "px";	
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('legend').style.width = mapWidth + "px";
	load_catchments();
	load_basemarkup();
	load_groupbounds();
	load_regions();
	load_poly_controls();
	}
	
function do_disp(){												// show incidents for dispatch - added 6/7/08
	$('incidents').style.display='block';
	$('view_unit').style.display='none';
	}

function do_dispfac(){												// show incidents for dispatch - added 6/7/08
	$('facilities').style.display='block';
	$('view_unit').style.display='none';
	}
	
function to_routes(id) {
	document.routes_Form.ticket_id.value=id;			// 10/16/08, 10/25/08
	document.routes_Form.submit();
	}

function to_fac_routes(id) {
	document.fac_routes_Form.fac_id.value=id;			// 10/6/09
	document.fac_routes_Form.submit();
	}
</SCRIPT>
<?php
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
	
$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`= " . $id . " LIMIT 1";	// 1/19/2013
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
		$temp = $f_types[$row['type']];
		$the_type = $temp[0];			// name of type
?>
</HEAD>
<BODY onLoad='set_size();'>
<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
<DIV id='outer' style='position: absolute; left: 0px;'>
	<DIV id='leftcol' style='position: absolute; left: 10px;'>
		<A NAME='top'>		<!-- 11/11/09 -->
		<FONT CLASS="header">&nbsp;'<?php print $row['name'] ;?>' Data</FONT> (#<?php print $row['id'];?>) <BR /><BR />
		<FORM METHOD="POST" NAME= "res_view_Form" ACTION="facilities?func=responder">
		<TABLE BORDER=0 ID='view_unit' STYLE='display: block'>
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Name"); ?>: </TD>			
				<TD><?php print $row['name'];?></TD>
			</TR>
			<TR CLASS = 'odd'>
				<TD CLASS="td_label"><?php print get_text("Location"); ?>: </TD>
				<TD><?php print $row['street'] ;?></TD>
			</TR>
			<TR CLASS = 'even'>
				<TD CLASS="td_label"><?php print get_text("City"); ?>: &nbsp;&nbsp;&nbsp;&nbsp;</TD>
				<TD><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Handle"); ?>: </TD>
				<TD><?php print $row['handle'];?>
					<SPAN STYLE = "margin-left:40px;" CLASS="td_label">Icon:</SPAN>&nbsp;<?php print $row['icon_str'];?>
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label">Regions: </TD>			
				<TD><?php print $fa_names;?></TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>			
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Type"); ?>: </TD>
				<TD><?php print $the_type;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Status"); ?>:</TD>
				<TD><?php print $un_st_val;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label">About Status</TD>  
				<TD><?php print $row['status_about'] ;?></TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Description"); ?>: </TD>	
				<TD><?php print $row['description'];?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label">
					<A CLASS="td_label" HREF="#" TITLE="Facility beds "><?php print get_text("Beds"); ?> Available/Occupied:</A>&nbsp;
				</TD>
				<TD><?php print $row['beds_a'];?>/<?php print $row['beds_o'];?>	</TD>
			</TR><!-- 	6/4/2013 -->
			<TR CLASS = "even">
				<TD CLASS="td_label">
					<A CLASS="td_label" HREF="#" TITLE="Beds information"><?php print get_text("Beds"); ?> information</A>:&nbsp;
				</TD>
				<TD><?php print $row['beds_info'];?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Capability"); ?>: </TD>	
				<TD><?php print $row['capab'];?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Contact name"); ?>:</TD>	
				<TD><?php print $row['contact_name'] ;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Contact email"); ?>:</TD>	
				<TD><?php print $row['contact_email'] ;?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Contact phone"); ?>:</TD>	
				<TD><?php print $row['contact_phone'] ;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Security contact"); ?>:</TD>	
				<TD><?php print $row['security_contact'] ;?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Security email"); ?>:</TD>	
				<TD><?php print $row['security_email'] ;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Security phone"); ?>:</TD>	
				<TD><?php print $row['security_phone'] ;?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label">
					<A CLASS="td_label" HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc.">Opening hours</A>:&nbsp;
				</TD>
				<TD>
					<TABLE style='width: 100%;'>
						<TR>
							<TH style='text-align: left;'><A CLASS="td_label" HREF="#" TITLE="Day of the Week"><?php print get_text("Day"); ?></A></TH>
							<TH style='text-align: left;'><A CLASS="td_label" HREF="#" TITLE="Opening Time"><?php print get_text("Opening"); ?></A></TH>
							<TH style='text-align: left;'><A CLASS="td_label" HREF="#" TITLE="Opening Time"><?php print get_text("Closing"); ?></A></TH>
						</TR>
<?php
						$opening_arr_serial = base64_decode($row['opening_hours']);
						$opening_arr = unserialize($opening_arr_serial);
						$z=0;
						foreach($opening_arr as $val) {
							switch($z) {
								case 0:
								$dayname = "Monday";
								break;
								case 1:
								$dayname = "Tuesday";
								break;
								case 2:
								$dayname = "Wednesday";
								break;
								case 3:
								$dayname = "Thursday";
								break;
								case 4:
								$dayname = "Friday";
								break;
								case 5:
								$dayname = "Saturday";
								break;
								case 6:
								$dayname = "Sunday";
								break;
								}
							if($val[0] == "on") {
?>
						<TR>
							<TD style='text-align: left;'><SPAN CLASS='td_data'><?php print $dayname;?></SPAN></TD>
							<TD style='text-align: left;'><SPAN CLASS='td_data'><?php print $val[1];?></SPAN></TD>
							<TD style='text-align: left;'><SPAN CLASS='td_data'><?php print $val[2];?></SPAN></TD>
						</TR>
<?php
						}
					$z++;
					}
?>
					</TABLE>
				</TD>			
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Access rules"); ?>:</TD>	
				<TD><?php print $row['access_rules'] ;?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Security reqs"); ?>:</TD>	
				<TD><?php print $row['security_reqs'] ;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Primary pager"); ?>:</TD>	
				<TD><?php print $row['pager_p'] ;?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Secondary pager"); ?>:</TD>	
				<TD><?php print $row['pager_s'] ;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label"><?php print get_text("Notify Mail List"); ?>:</TD>	
				<TD><?php print get_mailgroup_name($row['notify_mailgroup']);?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label"><?php print get_text("Notify Email Address"); ?>:</TD>	
				<TD><?php print $row['notify_email'];?></TD>
			</TR>
			<TR CLASS = 'odd'>
				<TD CLASS="td_label">As of:</TD>	
				<TD><?php print fac_format_date(strtotime($row['updated'])); ?></TD>
			</TR>
<?php
			if (my_is_float($lat)) {
?>		
			<TR CLASS = "even">
				<TD CLASS="td_label"  onClick = 'javascript: do_coords(<?php print "$lat,$lng";?>)'><U>Lat/Lng</U>:</TD>
				<TD>
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
								&nbsp;USNG:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $usng_val;?>}' SIZE=19 disabled />
<?php 		
								break;

							case "1":
?>
								&nbsp;OSGB:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $osgb_val;?>}' SIZE=19 disabled />
<?php
							break;
							
							default:
?>
								&nbsp;UTM:<INPUT TYPE="text" NAME="frm_ngs" VALUE='<?php print $utm_val;?>' SIZE=19 disabled />
<?php
							}		// end switch()

				}		// end if (my_is_float($lat))
?>
				</TD>
			</TR>
<?php
			$toedit = (is_administrator() || is_super())? "<INPUT TYPE='button' VALUE='to Edit' onClick= 'to_edit_Form.submit();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;": "" ;
?>
			<TR>
				<TD>&nbsp;</TD>
			</TR>
<?php
			if (is_administrator() || is_super()) {
?>
				<TR CLASS = "even">
					<TD COLSPAN=2 ALIGN='center'>
						<INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<INPUT TYPE="button" VALUE="to Edit" 	onClick= "to_edit_Form.submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</TD>
				</TR>
				<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>" />
				<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>" />
				<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />

<?php
		}		// end if (is_administrator() || is_super())
?>
		</TABLE>
	</DIV>
	<DIV id='rightcol' style='position: absolute; right: 170px; z-index: 1;'>
		<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
		<?php print get_variable('map_caption'); ?>
	</DIV>
</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, $id, 0)
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "facilities.php"></FORM>
<FORM NAME="to_edit_Form" METHOD="post" ACTION = "facilities.php?func=responder&edit=true&id=<?php print $id; ?>"></FORM>
<INPUT TYPE="hidden" NAME="fac_id" 	VALUE="">						<!-- 10/16/08 -->
<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
</FORM>
<A NAME="bottom" /> 
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>	
<SCRIPT>
var latLng;
var in_local_bool = "0";
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
<?php
exit();
