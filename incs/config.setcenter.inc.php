<?php
/*
6/8/12 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}	
error_reporting (E_ALL  ^ E_DEPRECATED);

function tz_list() {
    $zones_array = array();
    $timestamp = time();
    foreach(timezone_identifiers_list() as $key => $zone) {
//		date_default_timezone_set($zone);
		$zones_array[$key]['zone'] = $zone;
		$zones_array[$key]['offset'] = (int) ((int) date('O', $timestamp))/100;
		$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
		}
    return $zones_array;
	}
	
$theTimezones = tz_list();

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$states[$row['name']] = $row['code'];
	}
$mapzooms = array();
$dir = './_osm/tiles';
$mapdir = scandir($dir);
foreach($mapdir as $val) {
	if($val <> "." && $val <> "..") {
		if(is_dir('./_osm/tiles/' . $val)) {
			$mapzooms[] = intval($val);
			}
		}
	}
if(count($mapzooms) > 0) {$localZoomMin = min($mapzooms); $localZoomMax = max($mapzooms);} else {$localZoomMin = 0; $localZoomMax = 0;}
?>

		<STYLE> label, input[type="radio"]{font-size:10px; vertical-align:bottom;} 
		</STYLE> 
		</HEAD> 
		
		<BODY onLoad = "ck_frames();" >  		<!-- <?php echo __LINE__;?> -->
<?php
		if (array_key_exists ( 'update', $_GET )) {
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_lat]' WHERE `name`='def_lat';";
			$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_lng]' WHERE `name`='def_lng';";
			$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_zoom]' WHERE `name`='def_zoom';";
			$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_map_caption]' WHERE `name`='map_caption';";
			$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_dfz]' WHERE `name`='def_zoom_fixed';";
			$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
			if($_POST['frm_timezone'] == "") {$_POST['frm_timezone'] = "America/New_York";}
			$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$_POST[frm_timezone]' WHERE `name`='timezone';";
			$result = mysql_query($query) or do_error($query, 'query failed', mysql_error(), __FILE__, __LINE__);
			$top_notice = "Settings saved to database.";
			}
		else {
?>
		<script>
//										some globals		
	var map = null;				// the map object - note GLOBAL
	var OSM;
	var localMap
	var myMarker;					// the marker object
	var lat_var;					// see init.js
	var lng_var;
	var zoom;
	var bounds;
	var states_arr = <?php echo json_encode($states); ?>;
	var geo_provider = <?php print get_variable('geocoding_provider');?>;
	var BingKey = "<?php print get_variable('bing_api_key');?>";
	var GoogleKey = "<?php print get_variable('gmaps_api_key');?>";
	var localZoomMin = <?php print $localZoomMin;?>;
	var localZoomMax = <?php print $localZoomMax;?>;
	
	function do_point_stuff(lat, lng) {
		if(myMarker) {map.removeLayer(myMarker);}			// destroy predecessor
		lat_var = lat;
		lng_var = lng;
		do_lat (lat_var);
		do_lng (lng_var);
		do_grids(document.cen_Form);			// 9/16/08

		var dp_latlng = new L.LatLng(lat_var, lng_var);
		map.setView(dp_latlng, <?php echo get_variable('def_zoom'); ?>);		

		var iconurl = "./markers/crosshair.png";
		icon = new baseIcon({iconUrl: iconurl});	
		myMarker = L.marker([lat, lng], {icon: icon}).addTo(map);
		}				// end function do point stuff()
	
	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(Math.abs(inval));
		d  = Math.floor(d);
		var mi = (Math.abs(inval)-d)*60;	// fraction * 60
		var m = Math.floor(mi)				// min's as fraction
		var si = (mi-m)*60;					// to sec's
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	function lat2ddm(inlat) {				//  lat to degr, dec.min's - 9/9/089/7/08
		var x = new Number(Math.abs(inlat));
		var degs  = Math.floor(x);				// degrees
		var mins = ((Math.abs(x-degs)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return degs + '\260'  + mins +"'" + nors;
		}
	
	function lng2ddm(inlng) {				//  lng to degr, dec.min's - 9/9/089/7/08
		var x = new Number(Math.abs(inlng));
		var degs  = Math.floor(x);				// degrees
		var mins = ((Math.abs(x-degs)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return degs + '\260' + mins +"'" + eorw;
		}

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08		

	function do_lat_fmt(inlat) {				// 9/9/08
		switch(lat_lng_frmt) {
			case 0:		return inlat;			break;
			case 1:		return ll2dms(inlat);  	break;
			case 2:		return lat2ddm(inlat); 	break;
			default:	alert ("error " + <?php echo __LINE__;?>);
			}	
		}

	function do_lng_fmt(inlng) {
		switch(lat_lng_frmt) {
			case 0:		return inlng;  			break;
			case 1:		return ll2dms(inlng);	break;
			case 2:		return lng2ddm(inlng); 	break;
			default:	alert ("error " + <?php echo __LINE__;?>);
			}	
		}

	function usng_to_map(){			// usng to LL array			- 5/4/09
		tolatlng = new Array();
		USNGtoLL(document.cen_Form.frm_ngs.value, tolatlng);
		var point = new L.LatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
		var theLat = tolatlng[0].toFixed(6);
		var theLng = tolatlng[1].toFixed(6)
		map.setView([theLat, theLng], <?php echo get_variable('def_zoom'); ?>);
		var iconurl = "./markers/crosshair.png";
		icon = new baseIcon({iconUrl: iconurl});	
		myMarker = L.marker([lat, lng], {icon: icon}).addTo(map);
		do_lat (theLat);
		do_lng (theLng);
		}				// end function


	function map_cen_reset() {	do_map(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, 10, theSource); }			// reset map center icon

	var markersArray = [];
	
	function addrlkup() {
		var myAddress = document.forms[0].frm_city.value.trim() + " " + document.forms[0].frm_st.value.trim();
		control.options.geocoder.geocode(myAddress, function(results) {
			var r = results[0]['center'];
			var theLat = r.lat;
			var theLng = r.lng;
			do_point_stuff (theLat, theLng);
			});
		}				// end function addrlkup()
		
	function GetAddress(latlng) {
		var popup = L.popup();
		var loc = <?php print get_variable('locale');?>;
		control.options.geocoder.reverse(latlng, 20, function(results) {
		if(window.geo_provider == 0){
			var r1 = results[0];
			var r = r1['properties']['address'];
			if(loc == "1") { r.state = "UK";}
			} else if(window.geo_provider == 1) {
			var r = results[0];
			if(loc == "1") { r.state = "UK";}
			} else if(window.geo_provider == 2) {
			var r1 = results[0];
			var r = {city: r1.city, house_number: "", road: r1.street, state: r1.state};
			if(loc == "1") { r.state = "UK";}
			}

		var lat = parseFloat(latlng.lat.toFixed(6));
		var lng = parseFloat(latlng.lng.toFixed(6));
		var theCity = "";
		if(!r.city) {
			if(r.suburb && (r.suburb != "")) {
				theCity = r.suburb;
				} else if(r.locality && (r.locality != "")) {
				theCity = r.locality;
				} else {
				theCity = "";
				}
			} else {
			theCity = r.city;
			}
		if(!r.state || r.state == "") {
			if(r.county) {
				var state = r.county;
				} else {
				var state = "";
				}
			} else {
			var state = r.state;
			}

		if (r) {
			document.cen_Form.frm_city.value = theCity;
			if(states_arr[state]){
				document.cen_Form.frm_st.value = states_arr[state];
				var theState = states_arr[state];
				} else {
				document.cen_Form.frm_st.value = r.state;
				var theState = r.state;
				}
			document.cen_Form.show_lat.value = lat; 
			document.cen_Form.show_lng.value = lng;
			document.cen_Form.frm_lat.value = lat; 
			document.cen_Form.frm_lng.value = lng; 
			if(theCity != "" && theCity != "Unknown" && theState != "") {
				var theContent = theCity + ", " + theState;
				popup.setLatLng(latlng).setContent(theContent).openOn(map);
				}
			}
			});
		}
		
	function swap_source(the_source) {
		if(the_source == 1) {
			map.removeLayer(OSM);
			map.addLayer(localMap);
			map.options.maxZoom = localZoomMax;
			map.options.minZoom = localZoomMin;			
			} else {
			map.removeLayer(localMap);
			map.addLayer(OSM);
			map.options.maxZoom = 20;
			map.options.minZoom = 1;						
			}
		}


    </SCRIPT>
<?php
			$st_size = (get_variable("locale") ==0)?  2: 4;			
			$lat = get_variable('def_lat');
			$lng = get_variable('def_lng');
			$checks_ar = array("","","","");
			$which = get_variable('def_zoom_fixed');
			$checks_ar[$which] = " CHECKED ";
?>
			<FORM METHOD="POST" NAME= "cen_Form"  onSubmit="return validate_cen(document.cen_Form);" ACTION="config.php?func=center&update=true">
			<TABLE BORDER=0 ID='outer'>
				<TR>
					<TD style='vertical-align: top;'>
						<TABLE BORDER="0">
							<TR CLASS='even'>
								<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
							</TR>
							<TR CLASS='even'>
								<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
									<SPAN CLASS='text_green text_biggest'>Select Map Center/Zoom, Caption and Timezone</SPAN>
									<BR />
									<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
									<BR />
									<BR />
								</TD>
							</TR>
							<TR class='even' VALIGN='baseline'>
								<TD COLSPAN=99></TD>
							</TR>
							<TR class='spacer' VALIGN='baseline'>
								<TD CLASS="spacer" COLSPAN=99></TD>
							</TR>
							<TR class='odd' VALIGN='baseline'>
								<TD CLASS='td_label text text_left'>Timezone:</TD>
								<TD CLASS='td_data text text_left' COLSPAN=2>
									<SELECT name="frm_timezone" CLASS='text'>
										<OPTION value="">Select a time zone</OPTION>
<?php
										$currentTZ = date_default_timezone_get();
										foreach($theTimezones as $t) { 
											$sel = ($t['zone'] == $currentTZ) ? "SELECTED" : "";
?>
											<OPTION value="<?php print $t['zone'];?>" <?php print $sel;?>><?php echo $t['zone'];?></OPTION>
<?php 
											} 
?>
									</SELECT>
								</TD>
							</TR>
							<TR class='spacer' VALIGN='baseline'>
								<TD CLASS="spacer" COLSPAN=99></TD>
							</TR>
							<TR class='odd' VALIGN='baseline'>
								<TD CLASS="td_label" ALIGN='right'>Use Network or Local Maps:</TD>
								<TD ALIGN='center' COLSPAN=2>
									&nbsp;&nbsp;Network &raquo;<INPUT TYPE='radio' NAME='frm_mapsource' VALUE='0' CHECKED onClick = "swap_source(0);">
									&nbsp;&nbsp;Local &raquo;<INPUT TYPE='radio' NAME='frm_mapsource' VALUE='0' onClick = "swap_source(1);">
								</TD>
							</TR>
							<TR CLASS = "even">
								<TD CLASS="td_label">Lookup:</TD>
								<TD COLSPAN=3>
									<button type="button" onClick="addrlkup()"><img src="./markers/glasses.png" alt="Lookup location." /></BUTTON>&nbsp;&nbsp;City:&nbsp;
									<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_city" VALUE="" />&nbsp;&nbsp;&nbsp;&nbsp;
									State:&nbsp;<INPUT MAXLENGTH="2" SIZE="2" TYPE="text" NAME="frm_st" VALUE="" />
								</TD>
							</TR>
							<TR CLASS = "even"><TD CLASS="td_label">Caption:</TD><TD COLSPAN=3><INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_map_caption" VALUE="<?php print get_variable('map_caption');?>" onChange = "document.getElementById('caption').innerHTML=this.value "/></TD></TR>
							<TR CLASS = "odd" VALIGN='baseline'>
								<TD CLASS="td_label" ROWSPAN=6>Map:</TD>
								<TD ALIGN='right'>&nbsp;&nbsp;Lat:&nbsp;</TD>
								<TD colspan=2><INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=12 DISABLED />
								<SPAN STYLE='margin-left:20px'>Long:</SPAN>&nbsp;<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=12 DISABLED /></TD></TR>
							<TR>
<?php
								$coords = "{$lat},{$lng}";
?>
								<TD ALIGN='right' onClick = "usng_to_map()">USNG:&nbsp;</TD>
								<TD COLSPAN=2>
									<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($lat, $lng) ;?>" SIZE=22 DISABLED />
								</TD>
							</TR>
							<TR>
								<TD ALIGN='right' onClick = "utm_to_map()">OSGB:&nbsp;</TD>
								<TD COLSPAN=2>
									<INPUT TYPE="text" NAME="frm_osgb" VALUE="<?php print LLtoOSGB($lat,$lng);?>" SIZE=22 DISABLED />
								</TD>
							</TR>
							<TR>
								<TD ALIGN='right' onClick = "utm_to_map()">UTM:&nbsp;</TD>
								<TD COLSPAN=2>
									<INPUT TYPE="text" NAME="frm_utm" VALUE="<?php print toUTM($coords);?>" SIZE=22 DISABLED />
								</TD>
							</TR>
							<TR CLASS = "odd">
								<TD ALIGN='right'>&nbsp;&nbsp;Zoom:&nbsp;</TD>
								<TD>
									<INPUT TYPE="text" NAME="frm_zoom" VALUE="<?php print get_variable('def_zoom');?>" SIZE=4/>
								</TD>
							</TR>	<!-- 4/5/09 -->
							<TR VALIGN='baseline'>
								<TD CLASS="td_label" ALIGN='right'>Dynamic zoom:</TD>
								<TD ALIGN='center' COLSPAN=2>&nbsp;&nbsp;
									Yes &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='0' <?php print $checks_ar[0]; ?> onClick = "document.cen_Form.frm_dfz.value=0";> &nbsp;&nbsp;
									<B>Situation</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='1' <?php print $checks_ar[1]; ?> onClick = "document.cen_Form.frm_dfz.value=1";>&nbsp;&nbsp;
									<B>Units</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='2' <?php print $checks_ar[2]; ?> onClick = "document.cen_Form.frm_dfz.value=2";>&nbsp;&nbsp;
									<B>Both</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='3' <?php print $checks_ar[3]; ?> onClick = "document.cen_Form.frm_dfz.value=3";></TD></TR>
										
							<TR><TD>&nbsp;</TD></TR>
								<TD COLSPAN="99" ALIGN="center">
									<SPAN id='can_but' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="history.back();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
									<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="map_cen_reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
									<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.cen_Form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
								</TD>
							</TR>
						</TABLE>

					</TD>
					<TD>&nbsp;</TD>
					<TD>
						<DIV id='map_outer'>
							<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
						</DIV>
						<BR />
						<CENTER>
							<FONT CLASS="header"><SPAN ID="caption">Click/Zoom to new default position</SPAN></FONT>
						</CENTER>
					</TD>
				</TR>
			</TABLE>
			<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>">				<!-- // 9/16/08 -->
			<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>">
			<INPUT TYPE="hidden" NAME="frm_dfz" VALUE="<?php print $which;?>">
			</FORM>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"></FORM>		
<SCRIPT>
			var baseIcon = L.Icon.extend({options: {iconSize: [32, 32],	iconAnchor: [16, 16], popupAnchor: [6, -5]
				}
				});
			var iconurl = "./markers/crosshair.png";	
			function do_map(lat, lng, zoom, sourcemap) {
				var osmUrl = "http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
				var localUrl = "./_osm/tiles/{z}/{x}/{y}.png";
				var	cmAttr = '';
				OSM = L.tileLayer(osmUrl, {attribution: cmAttr});
				localMap = L.tileLayer(localUrl, {attribution: cmAttr});
				if (map) { map.remove(); map = null;} 
				map = L.map('map_canvas',
					{
					maxZoom: 20,
					minZoom: 1,
					zoom: zoom,
					layers: [OSM],
					zoomControl: true,
					attributionControl: false,
					},
					geocoders = {
						'Nominatim': L.Control.Geocoder.nominatim(),
						'Bing': L.Control.Geocoder.bing('AoArA0sD6eBGZyt5PluxhuN7N7X1vloSEIhzaKVkBBGL37akEVbrr0wn17hoYAMy'),
						'MapQuest': L.Control.Geocoder.mapQuest('Fmjtd%7Cluur2l6825%2Crn%3Do5-90125r')
					},
					control = new L.Control.Geocoder()
					);

				if(window.geo_provider == 1) {
					geocoder = L.Control.Geocoder.google(window.GoogleKey), 
					control = L.Control.geocoder({
						showResultIcons: false,
						collapsed: true,
						expand: 'click',
						position: 'topleft',
						placeholder: 'Search...',
						errorMessage: 'Nothing found.',
						geocoder: geocoder
						});
					} else if(window.geo_provider == 2) {
					geocoder = L.Control.Geocoder.bing(window.BingKey), 
					control = L.Control.geocoder({
						showResultIcons: false,
						collapsed: true,
						expand: 'click',
						position: 'topleft',
						placeholder: 'Search...',
						errorMessage: 'Nothing found.',
						geocoder: geocoder
						});				
					} else {
					geocoder = L.Control.Geocoder.nominatim(), 
					control = L.Control.geocoder({
						showResultIcons: false,
						collapsed: true,
						expand: 'click',
						position: 'topleft',
						placeholder: 'Search...',
						errorMessage: 'Nothing found.',
						geocoder: geocoder
						});
					}
				if(!isIE()) {
					control.addTo(map);
					}
	
				icon = new baseIcon({iconUrl: iconurl});	
				myMarker = L.marker([lat, lng], {icon: icon}).addTo(map);					
				return map;
				}
				
			function onMapClick(e) {
				if(myMarker) {map.removeLayer(myMarker); }
				icon = new baseIcon({iconUrl: iconurl});	
				myMarker = new L.marker(e.latlng, {id:1, icon:icon});
				myMarker.addTo(map);
				GetAddress(e.latlng);
				var zoom = map.getZoom();
				document.cen_Form.frm_zoom.value = zoom;
				};
				
			function getZoomLevel() {
				var zoom = map.getZoom();
				document.cen_Form.frm_zoom.value = zoom;
				}
			
			do_map(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, 10, 0);
			map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 10);
			map.on('click', onMapClick);
			map.on('zoomend', getZoomLevel);
			var bounds = map.getBounds();	
			var zoom = map.getZoom();
			

</SCRIPT>
			</BODY>
			</HTML> <!-- <?php echo __LINE__;?>  -->
<?php		
			exit();
			}		// end if/else ($_GET['update'] 	

