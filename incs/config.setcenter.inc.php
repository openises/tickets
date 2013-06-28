<?php
/*
6/8/12 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}	
error_reporting (E_ALL  ^ E_DEPRECATED);
?>

		<STYLE> label, input[type="radio"]{font-size:10px; vertical-align:bottom;} 
		</STYLE> 
		</HEAD> 
		<BODY onLoad = "ck_frames()" > 		<!-- <?php echo __LINE__;?> -->
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

			$top_notice = "Settings saved to database.";
			}
		else {

	switch(intval(trim(get_variable('maptype')))) { 
		case 2: 	$maptype = "SATELLITE"; 	break;	
		case 3: 	$maptype = "HYBRID"; 		break;	
		case 4: 	$maptype = "TERRAIN";		break;		
		default:	$maptype = "ROADMAP";
		}		// end switch()
?>
		<SCRIPT TYPE="text/javascript" SRC="http://maps.google.com/maps/api/js?sensor=false"></SCRIPT>
		<SCRIPT TYPE="text/javascript" SRC="./js/usng.js"></SCRIPT> 
		<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
		<SCRIPT TYPE="text/javascript" SRC="./js/gmaps_v3_init.js"></SCRIPT>
		<SCRIPT TYPE="text/javascript">
//										some globals		
		var map_obj = null;				// the map object - note GLOBAL
		var myMarker;					// the marker object
		var lat_var;					// see init.js
		var lng_var;
		var zoom_var;

		function do_point_stuff(in_array) {
			myMarker.setMap(null);			// 
			lat_var = in_array[0].geometry.location.lat();
			lng_var = in_array[0].geometry.location.lng();
			do_lat (in_array[0].geometry.location.lat());
			do_lng (in_array[0].geometry.location.lng());
			do_grids(document.cen_Form);			// 9/16/08

			var dp_latlng = new google.maps.LatLng(lat_var, lng_var);
			map_obj.setCenter(dp_latlng, <?php echo get_variable('def_zoom'); ?>);		

			var iconImg = new Image();														// obtain icon dimensions
			iconImg.src ='./markers/crosshair.png';
		    myIcon.anchor= new google.maps.Point(iconImg.width/2, iconImg.height/2);		// 8/11/12 - center offset = half icon width and height
			myMarker = new google.maps.Marker({
				position: dp_latlng,
				icon: myIcon, 
				draggable: true,
				map: map_obj
				});
			myMarker.setMap(map_obj);		// add marker with icon
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
		var point = new google.maps.LatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));

		map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);
		var marker = new GMarker(point);
		map.addOverlay(new GMarker(point, cross));
		
		do_lat (point.lat());
		do_lng (point.lng());
		}				// end function


	function map_cen_reset() {	initialize(); }			// reset map center icon

	var markersArray = [];
	var geocoder = new google.maps.Geocoder();
	
	function addrlkup() {
		var myAddress = document.forms[0].frm_city.value + " "  +document.forms[0].frm_st.value; 
		geocoder.geocode( { 'address': myAddress}, function(results, status) {		
			if (status == google.maps.GeocoderStatus.OK)	{ do_point_stuff (results)}					
			else 											{ alert("Geocode lookup failed: " + status);}
			});				// end geocoder.geocode()
		}		// end function addr lkup()

	function call_back (in_obj){				// callback function - called from gmaps_v3_init()
		do_lat (in_obj.lat.toFixed(6));
		do_lng (in_obj.lng.toFixed(6));
		do_zoom (in_obj.zoom);	
		do_grids(document.cen_Form);			// 9/16/08
		var cbLatLng = new google.maps.LatLng(lat_var, lng_var);
		map_obj.setCenter(cbLatLng);	// now center it		
		}		
		
	var icon_file = "./markers/crosshair.png";

	DomReady.ready(function() {										// instantiate the map and API
		map_obj = gmaps_v3_init(call_back, 'map_canvas', 
			<?php echo get_variable('def_lat');?>, 
			<?php echo get_variable('def_lng');?>, 
			<?php echo get_variable('def_zoom');?>, 
			icon_file, 
			<?php echo get_variable('maptype');?>, 
			false);		
			});

//		alert("165 " + typeof map_obj);
    </SCRIPT>
		
		</HEAD> 
		<BODY onLoad = "ck_frames()" >  		<!-- <?php echo __LINE__;?> -->
<?php
		
			$lat = get_variable('def_lat');
			$lng = get_variable('def_lng');
			$checks_ar = array("","","","");
			$which = get_variable('def_zoom_fixed');
			$checks_ar[$which] = " CHECKED ";
?>	
			<TABLE BORDER=0 ID='outer'>
			<TR><TD COLSPAN=2 ALIGN='center'><FONT CLASS="header">Select Map Center/Zoom and Caption</FONT><BR /><BR /></TD></TR>
			<TR><TD>
			<TABLE BORDER="0">
			<FORM METHOD="POST" NAME= "cen_Form"  onSubmit="return validate_cen(document.cen_Form);" ACTION="config.php?func=center&update=true">
			<TR CLASS = "even"><TD CLASS="td_label">Lookup:</TD><TD COLSPAN=3>&nbsp;&nbsp;City:&nbsp;<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="frm_city" VALUE="" />
			&nbsp;&nbsp;&nbsp;&nbsp;State:&nbsp;<INPUT MAXLENGTH="2" SIZE="2" TYPE="text" NAME="frm_st" VALUE="" /></TD></TR>
			<TR CLASS = "odd"><TD COLSPAN=4 ALIGN="center"><button type="button" onClick="addrlkup()"><img src="./markers/glasses.png" alt="Lookup location." /></TD></TR> <!-- 1/21/09 -->
			<TR><TD><BR /><BR /><BR /><BR /><BR /></TD></TR>
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
				<TD COLSPAN=2><INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($lat, $lng) ;?>" SIZE=22 DISABLED />
				</TD></TR>
			<TR>
				<TD ALIGN='right' onClick = "utm_to_map()">OSGB:&nbsp;</TD>
				<TD COLSPAN=2><INPUT TYPE="text" NAME="frm_osgb" VALUE="<?php print LLtoOSGB($lat,$lng);?>" SIZE=22 DISABLED />
				</TD></TR>
			<TR>
				<TD ALIGN='right' onClick = "utm_to_map()">UTM:&nbsp;</TD>
				<TD COLSPAN=2><INPUT TYPE="text" NAME="frm_utm" VALUE="<?php print toUTM($coords);?>" SIZE=22 DISABLED />
				</TD></TR>
			<TR CLASS = "odd">
				<TD ALIGN='right'>&nbsp;&nbsp;Zoom:&nbsp;</TD>
				<TD><INPUT TYPE="text" NAME="frm_zoom" VALUE="<?php print get_variable('def_zoom');?>" SIZE=4 disabled /></TD></TR>	<!-- 4/5/09 -->
			<TR VALIGN='baseline'><TD CLASS="td_label" ALIGN='right'>Dynamic zoom:</TD><TD ALIGN='center' COLSPAN=2>&nbsp;&nbsp;
			 		Yes &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='0' <?php print $checks_ar[0]; ?> onClick = "document.cen_Form.frm_dfz.value=0";> &nbsp;&nbsp;
					<B>Situation</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='1' <?php print $checks_ar[1]; ?> onClick = "document.cen_Form.frm_dfz.value=1";>&nbsp;&nbsp;
					<B>Units</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='2' <?php print $checks_ar[2]; ?> onClick = "document.cen_Form.frm_dfz.value=2";>&nbsp;&nbsp;
					<B>Both</B> fixed &raquo;<INPUT TYPE='radio' NAME='frm_zoom_fixed' VALUE='3' <?php print $checks_ar[3]; ?> onClick = "document.cen_Form.frm_dfz.value=3";></TD></TR>
						
			<TR><TD>&nbsp;</TD></TR>
			<TR CLASS = "even"><TD COLSPAN=5 ALIGN='center'>
				<INPUT TYPE='button' VALUE='Cancel' onClick='history.back();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='reset' VALUE='Reset' onClick = "map_cen_reset();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' VALUE='Submit'></TD></TR>
				<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>">				<!-- // 9/16/08 -->
				<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>">
				<INPUT TYPE="hidden" NAME="frm_dfz" VALUE="<?php print $which;?>">
			</FORM></TABLE>
			</TD><TD><DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR><CENTER><FONT CLASS="header"><SPAN ID="caption">Click/Drag/Zoom to new default position</SPAN></FONT></CENTER>
			</TD></TR>
			</TABLE>
			<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"></FORM>		
			</BODY>
			</HTML> <!-- <?php echo __LINE__;?>  -->
<?php		
			exit();
			}		// end if/else ($_GET['update'] 	

