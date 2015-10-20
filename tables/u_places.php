<!--
3/18/11 initial release - AS
3/26/2014 - updated to include information re buildinga
-->
	<SCRIPT type="text/javascript">

	function addrlkup(theForm) {		 //
		if ((theForm.the_city.value.trim()==""  || theForm.the_st.value.trim()=="")) {
			alert ("City and State are required for location lookup.");
			return false;
			}
		var myAddress = theForm.the_city.value.trim() + " "  +theForm.the_st.value.trim();
		control.options.geocoder.geocode(myAddress, function(results) {
			var r = results[0]['center'];
			var theLat = r.lat;
			var theLng = r.lng;
			point_on_map(theForm, theLat, theLng);
			});
		}				// end function addrlkup()

	function is_float(str) {
		return /^[-+]?\d+(\.\d+)?$/.test(str);
		}

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function validate(theForm) {	//
		var errmsg="";
		if (theForm.frm_name.value == "")		{errmsg+= "\tPlace name is required\n";}
		if (theForm.frm_lat.value == "")		{errmsg+= "\tLatitude value is required\n";}
		else if (!
			(is_float(theForm.frm_lat.value) && 
			(theForm.frm_lat.value <=90.0) && 
			(theForm.frm_lat.value >= -90.0)
			)) 											{errmsg+= "\tValid latitude is required\n";}
		if (theForm.frm_lon.value == "")				{errmsg+= "\tLongitude value is required\n";}
		else if (!
			(is_float(JSfnTrim(theForm.frm_lon.value)) && 
			(theForm.frm_lon.value <=180.0) && 
			(theForm.frm_lon.value >= -180.0)
			)) 											{errmsg+= "\tValid longitude is required\n";}		
				// 3/26/2014
		if (theForm.frm_apply_to.value == "bldg") {	
			if (theForm.frm_street.value.trim() == "") 	{errmsg+= "\tBuilding street addr is required\n";}
			if (theForm.frm_city.value.trim() == "") 	{errmsg+= "\tBuilding city is required\n";}
			if (theForm.frm_state.value.trim() == "") 	{errmsg+= "\tBuilding state is required\n";}		
			} 

		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.submit();
			}
		}				// end function validate()

	function fn_check_borc(inval) {
		$('but1').style.opacity = $('but2').style.opacity = $('row1').style.opacity = $('row2').style.opacity = $('row3').style.opacity = $('row4').style.opacity = $('row5').style.opacity = 1.0;		

		document.c.frm_apply_to.value = inval;				// set as 'db apply' value	
		if ( (inval == 'city') && (!($('ID3').readOnly)) ) {
			$('ID3').value = $('ID4').value = $('ID5').value = '';			
			}
		var opacity =  (inval=='city')?  0.2 : 1.0;
		$('row4').style.opacity = $('row5').style.opacity = opacity;		
		$('ID3').readOnly = $('ID4').readOnly = $('ID5').readOnly = (inval=='city');		

		}		// end function fn_check_borc()
		

	function addrFromClick(latlng) {
		var popup = L.popup();	
		control.options.geocoder.reverse(latlng, map.options.crs.scale(map.getZoom()), function(results) {
			var r = results[0];
			var theLat = latlng.lat;
			var theLng = latlng.lng;
			if (r) {
				document.u.frm_street.value = r.name;
				if(r.city) { var theCity = r.city; } else { theCity = "";}
				document.u.the_city.value = theCity;
				document.u.the_st.value = r.state;
				document.u.frm_lat.value = theLat.toFixed(6); 
				document.u.frm_lon.value = theLng.toFixed(6);
				document.u.frm_street.focus();
				}
			});
		}
		
	function point_on_map(my_form, lat, lng) {
		if(marker) {map.removeLayer(marker);}
		if(myMarker) {map.removeLayer(myMarker);}
		my_form.frm_lat.value=lat.toFixed(6);	
		my_form.frm_lon.value=lng.toFixed(6);		
		var iconurl = "./our_icons/yellow.png";
		icon = new baseIcon({iconUrl: iconurl});	
		marker = L.marker([lat, lng], {icon: icon});
		marker.addTo(map);
		map.setView([lat, lng], 13);
		}				// end function pt_to_map ()
</SCRIPT>
		<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="tablename" 	 VALUE="<?php print $tablename;?>"/>
		<INPUT TYPE="hidden" NAME="indexname" 	 VALUE="id"/>
		<INPUT TYPE="hidden" NAME="id" 			 VALUE="<?php print $row['id'];?>" />
		<INPUT TYPE="hidden" NAME="sortby" 		 VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortdir"		 VALUE=0 />
		<INPUT TYPE="hidden" NAME="func" 		 VALUE="pu"/>
		<INPUT TYPE="hidden" NAME="srch_str"  	 VALUE=""/> <!-- 9/12/10 -->
		<INPUT TYPE="hidden" NAME="frm_apply_to" VALUE="<?php print $row['apply_to'];?>" /> <!-- db update value; initially the default; revised onclick -->

<?php
$label = ($row['apply_to'] == "bldg") ? "Building" : "Place" ;
$lat = $row['lat'];
$lng = $row['lon'];
?>
		<TABLE BORDER=0 ID='outer' ALIGN= 'center'>
		<TR><TD COLSPAN=2 ALIGN='center'><FONT CLASS="header"><?php echo get_variable('map_caption');?></FONT><BR /><BR /></TD></TR>
		<TR><TD>
			<TABLE BORDER="0" ALIGN="center">
			<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER" CLASS="td_label" ><FONT SIZE="+1">Update '<?php echo $label;?>' Data</FONT></TD></TR>
			<TR><TD><P />&nbsp;</TD></TR>
			<TR CLASS = "even"><TD></TD>
				<TD COLSPAN=3>&nbsp;&nbsp;<?php print get_text("City");?>:&nbsp;<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="the_city" VALUE="<?php echo $row['city'];?>" />
				&nbsp;&nbsp;&nbsp;&nbsp;<?php print get_text("St");?>:&nbsp;<INPUT MAXLENGTH="4" SIZE="2" TYPE="text" NAME="the_st" VALUE="<?php echo $row['state'];?>" /><button type="button" style = "margin-left:40px;" onClick="addrlkup(this.form)">
				<img src="./markers/glasses.png" alt="Lookup location."  />&nbsp;&nbsp;Lookup</TD></TR>
			<TR><TD>&nbsp;</TD></TR>

		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php echo $label;?> name:</TD>
			<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="64" SIZE="64" type="text" NAME="frm_name" VALUE="<?php echo $row['name'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>

			<TR><TD>&nbsp;</TD></TR>
<!-- new -->
			<TR VALIGN="baseline" CLASS="even" ID = 'brow1' >
				<TD CLASS="td_label" ALIGN="right">Street:</TD>
				<TD><INPUT ID="ID3" CLASS="dirty" MAXLENGTH="96" SIZE="64" type="text" NAME="frm_street" VALUE="<?php echo $row['street']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD>
				</TR>
			<TR VALIGN="baseline" CLASS="odd" ID = 'brow2' >
				<TD CLASS="td_label" ALIGN="right">City:</TD>
				<TD><INPUT ID="ID4" CLASS="dirty" MAXLENGTH="32" SIZE="32" type="text" NAME="frm_city" VALUE="<?php echo $row['city']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> 
				<span CLASS="td_label" style = "margin-left:20px;"> St: <INPUT ID="ID5" CLASS="dirty" MAXLENGTH="4" SIZE="4" type="text" NAME="frm_state" VALUE="<?php echo $row['state']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)" ></span>
				</TD>
				</TR>

			<TR VALIGN="baseline" CLASS="even" ID = 'brow4' >
				<TD CLASS="td_label" ALIGN="right"><?php echo $label;?> information:</TD>
				<TD><TEXTAREA ID='ID6' CLASS='dirty' NAME='frm_information' COLS='64' ROWS = '1' onFocus="JSfnChangeClass(this.id, 'dirty');" STYLE='vertical-align:text-top;'><?php echo $row['information']; ?></TEXTAREA> </TD>
				</TR>
<!-- /new -->
		<TR><TD>&nbsp;</TD></TR>

		<TR VALIGN="baseline" CLASS="even" STYLE = "opacity:.2;"><TD CLASS="td_label" ALIGN="right">Lat:</TD>
			<TD><INPUT ID="ID2" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lat" VALUE="<?php echo $row['lat']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/>
			<SPAN CLASS="td_label" STYLE ="margin-left:20px;">Lon:&nbsp;&nbsp;&nbsp;
			<INPUT ID="ID3" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lon" VALUE="<?php echo $row['lon']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/></SPAN>
			 </TD></TR>
				<TR><TD COLSPAN="99" ALIGN="center">
			<BR /><BR />
			<INPUT TYPE="button"	VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="reset"		VALUE="Reset"/>&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE="button" NAME="sub_but" VALUE="Submit" onclick="validate(this.form)"/>
	
			</TD></TR>
			</FORM>
			</TD></TR></TABLE>
		</TD><TD>
			<div id="map_canvas" style="width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px; margin-left:20px;"></div>
		</TD></TR>
		</TABLE>
<SCRIPT>
		var map;
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
		var latLng;
		var in_local_bool = "0";
		var mapWidth = <?php print get_variable('map_width');?>+20;
		var mapHeight = <?php print get_variable('map_height');?>+20;
		$('map_canvas').style.width = mapWidth + "px";
		$('map_canvas').style.height = mapHeight + "px";
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", 13, theLocale, useOSMAP, "tr");
		map.setView([<?php print $lat;?>, <?php print $lng;?>], 13);
		var bounds = map.getBounds();	
		var zoom = map.getZoom();

		function onMapClick(e) {
			if(marker) {map.removeLayer(marker); }
			var iconurl = "./our_icons/yellow.png";
			icon = new baseIcon({iconUrl: iconurl});	
			marker = new L.marker(e.latlng, {id:1, icon:icon, draggable:'true'});
			marker.addTo(map);
			addrFromClick(e.latlng);
			};

		map.on('click', onMapClick);
</SCRIPT>
<?php
