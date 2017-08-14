<!--
3/18/11 initial release - AS
3/25/2014 - expanded to handle buildings
-->
<?php
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]places` ADD `apply_to` ENUM( 'city', 'bldg' ) NOT NULL DEFAULT 'city' AFTER `name` ,
	ADD `street` VARCHAR( 96 ) NULL DEFAULT NULL AFTER `apply_to` ,
	ADD `city` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `street` ,
	ADD `state` VARCHAR( 4 ) NULL DEFAULT NULL AFTER `city` ,
	ADD `information` VARCHAR( 1024 ) NULL DEFAULT NULL AFTER `state` ";
	$result = @mysql_query($query) ;		// note STFU
	
?>
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
				document.c.frm_street.value = r.name;
				if(r.city) { var theCity = r.city; } else { theCity = "";}
				document.c.the_city.value = theCity;
				document.c.the_st.value = r.state;
				document.c.frm_lat.value = theLat.toFixed(6); 
				document.c.frm_lon.value = theLng.toFixed(6);
				document.c.frm_street.focus();
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
		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="tablename" 	 VALUE="<?php print $tablename;?>"/>
		<INPUT TYPE="hidden" NAME="indexname" 	 VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortby" 		 VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortdir"		 VALUE=0 />
		<INPUT TYPE="hidden" NAME="func" 		 VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="srch_str" 	 VALUE=""/> <!-- 9/12/10 --> 
		<INPUT TYPE="hidden" NAME="frm_apply_to" VALUE="city" /> <!-- db update value; initially the default; revised onclick -->

		<TABLE BORDER=0 ID='outer' ALIGN= 'center'>
		<TR><TD COLSPAN=2 ALIGN='center'><FONT CLASS="header"><i>Add New City/Building Entry</i></FONT><BR /><BR /></TD></TR>
		<TR><TD>

			<TABLE BORDER="0" ALIGN="center">
			<TR><TD>&nbsp;</TD></TR>
			
			<TR VALIGN="baseline" CLASS="odd" ><TD ALIGN="right" CLASS="header" >Select:</TD>
				<TD ALIGN = 'center' VALIGN='baseline' CLASS="header">&nbsp;&nbsp;<B>city &nbsp;&raquo;&nbsp;
					<INPUT TYPE='radio' onclick = "fn_check_borc(this.value);" NAME="apply_to_c" VALUE= "city" STYLE='vertical-align:baseline;'/>
					<span style = "margin-left:50px;">&nbsp;bldg&nbsp;&raquo;&nbsp;
					<INPUT TYPE='radio' onclick = "fn_check_borc(this.value);" NAME="apply_to_b" VALUE= "bldg" STYLE='vertical-align:baseline; '/></span></B></TD>
				</TR>
			<TR><TD><br /></TD></TR>

			<TR id = 'row1' CLASS = "even" style = "opacity:0.2" ><TD></TD>
				<TD COLSPAN=3>&nbsp;&nbsp;<?php print get_text("City");?>:&nbsp;<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="the_city" VALUE="" />
				&nbsp;&nbsp;&nbsp;&nbsp;<?php print get_text("St");?>:&nbsp;<INPUT MAXLENGTH="4" SIZE="2" TYPE="text" NAME="the_st" VALUE="" /><button type="button" style = "margin-left:40px;" onClick="addrlkup(this.form)">
				<img src="./markers/glasses.png" alt="Lookup location." />&nbsp;&nbsp;Lookup</TD></TR>

			<TR><TD>&nbsp;</TD></TR>

			<TR id = 'row2' VALIGN="baseline" CLASS="even" style = "opacity:0.2" ><TD CLASS="td_label" ALIGN="right">Name:</TD>
				<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="64" SIZE="64" type="text" NAME="frm_name" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
			<TR><TD>&nbsp;</TD></TR>
<!-- new -->
			<TR id = 'row3' VALIGN="baseline" CLASS="even" ID = 'brow4' style = "opacity:0.2"  >
				<TD CLASS="td_label" ALIGN="right">Information:</TD>
				<TD><TEXTAREA ID='ID6' CLASS='dirty' NAME='frm_information' COLS='64' ROWS = '1' onFocus="JSfnChangeClass(this.id, 'dirty');" STYLE='vertical-align:text-top;'></TEXTAREA> </TD>
				</TR>

			<TR><TD><br /></TD></TR>

			<TR ID = 'row4' VALIGN="baseline" CLASS="even" style = "opacity:0.2">
				<TD CLASS="td_label" ALIGN="right" >Bldg addr:</TD>
				<TD><INPUT ID="ID3" readOnly CLASS="dirty" MAXLENGTH="96" SIZE="64" type="text" NAME="frm_street" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD>
				</TR>

			<TR ID = 'row5' VALIGN="baseline" CLASS="odd"  style = "opacity:0.2" >
				<TD CLASS="td_label" ALIGN="right">Bldg city:</TD>
				<TD><INPUT ID="ID4" readOnly CLASS="dirty" MAXLENGTH="32" SIZE="32" type="text" NAME="frm_city" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> 
				<span CLASS="td_label" style = "margin-left:20px;"> St: <INPUT ID="ID5" readOnly CLASS="dirty" MAXLENGTH="4" SIZE="4" type="text" NAME="frm_state" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)" ></span>
				</TD>
				</TR>

<!-- /new -->
		<TR><TD>&nbsp;</TD></TR>

		<TR ID = 'row6' VALIGN="baseline" CLASS="odd" STYLE = "opacity:.2;"><TD CLASS="td_label" ALIGN="right">Lat:</TD>
			<TD><INPUT ID="ID2" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lat" VALUE="<?php echo get_variable('def_lat'); ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/>
			<SPAN CLASS="td_label" STYLE ="margin-left:20px;">Lon:&nbsp;&nbsp;&nbsp;
			<INPUT ID="ID3" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lon" VALUE="<?php echo get_variable('def_lng'); ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/></SPAN>
			 </TD></TR>
		<TR><TD COLSPAN="99" ALIGN="center">
			<BR /><BR />
			<INPUT TYPE="button"	VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT ID = 'but1' style = "opacity:0.2"  TYPE="button"	VALUE="Reset" onclick = "do_reset();"/>&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT ID = 'but2' style = "opacity:0.2"  TYPE="button" VALUE="Submit" NAME="sub_but" onclick="validate(this.form)"/>	
			</TD></TR>
			</FORM>
			</TD></TR></TABLE> <!-- /inner -->
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
		var in_local_bool = "<?php print get_variable('local_maps');?>";
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		var initZoom = <?php print get_variable('def_zoom');?>;
		init_map(2, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "");
		map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
		var bounds = map.getBounds();	
		var zoom = map.getZoom();
		var got_points = false;	// map is empty of points
		function onMapClick(e) {
		if(marker) {map.removeLayer(marker); }
			var iconurl = "./our_icons/yellow.png";
			icon = new baseIcon({iconUrl: iconurl});	
			marker = new L.marker(e.latlng, {id:1, icon:icon, draggable:'true'});
			marker.addTo(map);
			newGetAddress(e.latlng, "c");
			};

		map.on('click', onMapClick);
		</SCRIPT>
<?php
