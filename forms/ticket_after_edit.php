<?php
?>
<SCRIPT>
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var tmarkers = [];	//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var fmarkers = [];			//	Responder Markers array

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
	mapHeight = viewportheight * .65;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('leftTable').style.width = colwidth + "px";	
	$('left').style.width = colwidth + "px";
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	}
</SCRIPT>
<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
	<DIV id='leftcol' style='position: absolute; left: 10px; top: 10px; z-index: 3;'>
<?php

	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#{$id})</I>" : "";			// 1/25/09, 2/18/12
	$un_stat_cats = get_all_categories();
	$istest = FALSE;
	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}

	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}

	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
										// 1/7/10
	$query = "SELECT *,
		`problemstart` AS `my_start`,
		`problemstart` AS `problemstart`,
		`problemend` AS `problemend`,
		`date` AS `date`,
		`booked_date` AS `booked_date`,		
		`$GLOBALS[mysql_prefix]ticket`.`updated` AS `updated`,		
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
		`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`street` AS `rec_fac_street`,
		`rf`.`city` AS `rec_fac_city`,
		`rf`.`state` AS `rec_fac_state`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` 	ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 		ON (`$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.`facility`) 
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf 	ON (`rf`.id = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $id $restrict_ticket";			// 7/16/09, 8/12/09

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">Internal error " . basename(__FILE__) ."/" .  __LINE__  .".  Notify developers of this message.</FONT>";	// 8/18/09
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_array($result));
	$type = get_type($row['in_types_id']);
	$severity = $row['severity'];
	$scope = $row['scope'];
	$locale = get_variable('locale');    // 10/29/09
	switch($locale) {
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($row['lat'], $row['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($row['lat'], $row['lng']);    // 8/23/08, 10/15/08, 8/3/09
		break;

		case "2":
		$coords =  $row['lat'] . "," . $row['lng'];                                    // 8/12/09
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);    // 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}


?>
	<TABLE id='leftTable' style='border: 1px solid #707070;'>
	<TR VALIGN="top" style='width: 100%;'><TD CLASS="print_TD, even" ALIGN="left" style='width: 100%;'>
	<DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 100%; font-size: 1.5em; font-weight: bold; border: 2px outset #707070;'></DIV><BR />	

<?php

	print do_ticket($row, "100%", FALSE) ;				// 2/25/09
	print show_actions($row['id'], "date", FALSE, TRUE, 0);		/* lists actions and patient data belonging to ticket */
	print "</TD></TR></TABLE>\n";	
	$lat = $row['lat']; $lng = $row['lng'];
?>
	</DIV>
	<DIV id='rightcol' style='position: absolute; right: 60px; top: 10px; z-index: 3;'>
		<DIV ID='map_canvas' style='border: 1px outset #707070; z-index: 2'></DIV>
	</DIV>
<SCRIPT>
var map;
var minimap;
var latLng;
var mapWidth = <?php print get_variable('map_width');?>+20;
var mapHeight = <?php print get_variable('map_height');?>+20;;
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var theLat = "<?php print $lat;?>";
var theLng = "<?php print $lng;?>";
find_warnings(theLat, theLng);
init_map(1, theLat, theLng, "", 13, theLocale, useOSMAP, "tr");
var bounds = map.getBounds();
var zoom = map.getZoom();
var i = 0;
<?php
do_kml();
?>
</SCRIPT>
<?php
	if ((($lat == $GLOBALS['NM_LAT_VAL']) && ($lng == $GLOBALS['NM_LAT_VAL'])) || (($lat == "") || ($lat == NULL)) || (($lng == "") || ($lng == NULL))) {	// check for lat and lng values set in no maps state, or errors 7/28/10, 10/23/12
		$lat = get_variable('def_lat'); $lng = get_variable('def_lng');
		$icon_file = "./our_icons/question1.png";
		}
	else {
		$icon_file = "./markers/crosshair.png";
		}

// ====================================Add Facilities to Map 8/1/09================================================
	$query_fac = "SELECT *,`updated` AS `updated`, 
		`$GLOBALS[mysql_prefix]facilities`.id AS fac_id, 
		`$GLOBALS[mysql_prefix]facilities`.description AS facility_description, 
		`$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, 
		`$GLOBALS[mysql_prefix]fac_types`.icon AS type_icon, 
		`$GLOBALS[mysql_prefix]facilities`.name AS facility_name 
		FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	while($row_fac = mysql_fetch_array($result_fac)){

		$fac_name = $row_fac['facility_name'];			//	10/8/09
		$fac_temp = explode("/", $fac_name );
		$fac_index = substr($fac_temp[count($fac_temp) -1] , -6, strlen($fac_temp[count($fac_temp) -1]));	// 3/19/11
		$icon_str = $row_fac['icon_str'];
		$fac_id=($row_fac['id']);
		$fac_type=($row_fac['type_icon']);

		$f_disp_name = $row_fac['facility_name'];		//	10/8/09
		$f_disp_temp = explode("/", $f_disp_name );
		$facility_display_name = $f_disp_temp[0];
		$faclat = $row_fac['lat'];
		$faclng = $row_fac['lng'];

		if ((my_is_float($faclat)) && (my_is_float($faclng))) {
?>

<?php
			}	// end if my_is_float
		}	// end while
// ================================End of Facilities========================================
// ====================================Add Responding Units to Map================================================

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while($row = mysql_fetch_array($result)){
		$responder_id=($row['responder_id']);
		if ($row['clear'] == NULL) {

			$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder_id'";
			$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while($row_unit = mysql_fetch_array($result_unit)){
				$unit_id = $row_unit['id'];
				$mobile = $row_unit['mobile'];
				$handle = $row_unit['handle'];
				$index = $row_unit['icon_str'];
				$resp_cat = $un_stat_cats[$row_unit['id']];
				$temp = $row_unit['un_status_id'] ;
				$the_time = $row_unit['updated'];
				$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
				$theType = $row_unit['type'];
				if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {
					$theTabs = "<div class='infowin'><BR />";
					$theTabs .= '<div class="tabBox" style="float: left; width: 100%;">';
					$theTabs .= '<div class="tabArea">';
					$theTabs .= '<span id="tab1" class="tabinuse" style="cursor: pointer;" onClick="do_tab(\'tab1\', 1, null, null);">Summary</span>';
					$theTabs .= '<span id="tab2" class="tab" style="cursor: pointer;" onClick="do_tab(\'tab3\', 3, ' . $row_unit['lat'] . ',' . $row_unit['lng'] . ');">Location</span>';
					$theTabs .= '</div>';
					$theTabs .= '<div class="contentwrapper">';
					
					$tab_1 = "<TABLE width='{$iw_width}' style='height: 280px;'><TR><TD><TABLE>";			
					$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_unit['name'], 48)) . "</B></TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . addslashes(shorten(str_replace($eols, " ", $row_unit['description']), 32)) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
					$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . addslashes($row_unit['contact_name']). " Via: " . addslashes($row_unit['contact_via']) . "</TD></TR>";
					$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($the_time) . "</TD></TR>";		// 4/11/10
					if (array_key_exists($unit_id, $assigns)) {
						$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$unit_id] . "'>" . addslashes(shorten($assigns[$unit_id], 20)) . "</A></TD></TR>";
						}
					$tab_1 .= "</TABLE></TD></TR></TABLE>";
				
					$tab_2 = "<TABLE width='{$iw_width}' style='height: 280px;'><TR><TD>";
					$tab_2 .= "<TABLE width='100%'>";
					$locale = get_variable('locale');	// 08/03/09
					switch($locale) { 
						case "0":
						$tab_2 .= "<TR CLASS='odd'><TD class='td_label' ALIGN='left'>USNG:</TD><TD ALIGN='left'>" . LLtoUSNG($row_unit['lat'], $row_unit['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						case "1":
						$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>OSGB:</TD><TD ALIGN='left'>" . LLtoOSGB($row_unit['lat'], $row_unit['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						case "2":
						$coords =  $row_unit['lat'] . "," . $row_unit['lng'];							// 8/12/09
						$tab_2 .= "<TR CLASS='odd'>	<TD class='td_label' ALIGN='left'>UTM:</TD><TD ALIGN='left'>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
						break;
					
						default:
						print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
						}
					$tab_2 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lat</TD><TD class='td_data' style='font-size: 80%;'>" . $row_unit['lat'] . "</TD></TR>";
					$tab_2 .= "<TR><TD class='td_label' style='font-size: 80%;'>Lng</TD><TD class='td_data' style='font-size: 80%;'>" . $row_unit['lng'] . "</TD></TR>";
					$tab_2 .= "</TABLE></TD></TR><R><TD><TABLE width='100%'>";			// 11/6/08
					$tab_2 .= "<TR><TD style='text-align: center;'><CENTER><DIV id='minimap' style='height: 180px; width: 180px; border: 2px outset #707070;'>Map Here</DIV></CENTER></TD></TR>";
					$tab_2 .= "</TABLE></TD</TR></TABLE>";
						
					$theTabs .= "<div class='content' id='content1' style = 'display: block;'>" . $tab_1 . "</div>";
					$theTabs .= "<div class='content' id='content3' style = 'display: none;'>" . $tab_2 . "</div>";
					$theTabs .= "</div>";
					$theTabs .= "</div>";
					$theTabs .= "</div>";
					if ($mobile == 1) {
?>				
<SCRIPT>
						var unitmarker = createUnitMarker(<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>, "<?php print quote_smart($theTabs);?>", 0, 0, <?php print $unit_id;?>, '<?php print $index;?>', '<?php print $resp_cat;?>', 0, '<?php print $handle;?>', '<?php print $theType;?>');
						unitmarker.addTo(map);
						bounds.extend([<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>]);
						i++
</SCRIPT>						
<?php
						} else {
?>
<SCRIPT>
						var unitmarker = createUnitMarker(<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>, "<?php print quote_smart($theTabs);?>", 4, 0, <?php print $unit_id;?>, '<?php print $index;?>', '<?php print $resp_cat;?>', 0, '<?php print $handle;?>', '<?php print $theType;?>');
						unitmarker.addTo(map);
						bounds.extend([<?php print $row_unit['lat'];?>, <?php print $row_unit['lng'];?>]);
						i++
</SCRIPT>						
<?php
						}	// end if mobile
					}	// end if mys_is_float
				}	// end while row unit
			}	// end if $row['clear'] == NULL
		}	//	end while row

// =====================================End of functions to show responding units========================================================================
?>

<SCRIPT>
var incs_icons=[];
incs_icons[<?php echo $GLOBALS['SEVERITY_NORMAL'];?>] = 1;	// blue
incs_icons[<?php echo $GLOBALS['SEVERITY_MEDIUM'];?>] = 2;	// yellow
incs_icons[<?php echo $GLOBALS['SEVERITY_HIGH']; ?>] =  3;	// red

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
mapHeight = viewportheight * .65;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('leftTable').style.width = colwidth + "px";	
$('left').style.width = colwidth + "px";
$('rightcol').style.width = colwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
}

function createMarkerInc(lat, lon, info, color, stat, theid, sym, category, region, tip, z) {
	if((isFloat(lat)) && (isFloat(lon))) {
		if(!sym) { sym = "UNK"; }
		var origin = ((sym.length)>3)? (sym.length)-3: 0;
		var iconStr = sym.substring(origin);
		var iconurl = "./our_icons/gen_icon.php?blank=" + escape(window.incs_icons[color]) + "&text=" + iconStr;	
		icon = new baseIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon, title: tip, zIndexOffset: z}).bindPopup(info).openPopup();
		return marker;
		} else {
		return false;
		}
	}
	
var incMarker = createMarkerInc(theLat, theLng, "<?php print $scope;?>", <?php print $severity;?>, "<?php print shorten($type, 18);?>", 1, "1", "Incident", 0, "<?php print $scope;?>", i);
incMarker.addTo(map);
bounds.extend([theLat, theLng]);
map.fitBounds(bounds); 
map.setView([theLat, theLng], 13);
</SCRIPT>
</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, $id, 0, 0, 0)
?>
<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
