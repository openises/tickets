<!--
3/18/11 initial release - AS
-->
	<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php print get_variable('gmaps_api_key');?>"
			type="text/javascript"></SCRIPT>
	<SCRIPT type="text/javascript">

	var geocoder;		// note GLOBAL!
	var map;

	function initialize() {
	  if (GBrowserIsCompatible()) {
		geocoder = new GClientGeocoder();
		map = new GMap2(document.getElementById("map_canvas"));
		map.setUIToDefault();										// 8/13/10

		map.addControl(new GMapTypeControl());
<?php print (get_variable('terrain') == 1)? "\t\tmap.addMapType(G_PHYSICAL_MAP);\n" : "";?>
		map.addControl(new GOverviewMapControl());
		map.enableScrollWheelZoom();
		
		var center = new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>);
		map.setCenter(center, <?php echo get_variable('def_zoom'); ?>);

		var marker = new GMarker(center, {draggable: true});

		GEvent.addListener(map, "click", function(marker, point) {
			if (point) {
				document.c.frm_lat.value = point.lat().toFixed(6);
				document.c.frm_lon.value = point.lng().toFixed(6);
				map.clearOverlays();
				map.addOverlay(new GMarker(point));										// to center
				var center = new GLatLng( point.lat(),point.lng());
				map.setCenter(center, (<?php echo get_variable('def_zoom'); ?>+2));		// zoom in 2 levels
				}
				});

		map.addOverlay(marker);
	  }
	}			// end function initialize()	

	function addrlkup(theForm) {		   //
		var address = theForm.the_city.value + " "  + theForm.the_st.value;
		if (geocoder) {								// defined in function initialize()
			geocoder.getLatLng(
				address,
				function(point) {
					if (!point) {
						alert(address + " not found");
						} 
					else {
						map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);
						var marker = new GMarker(point);
						document.c.frm_lat.value = point.lat().toFixed(6);
						document.c.frm_lon.value = point.lng().toFixed(6);
						map.clearOverlays();
						map.addOverlay(new GMarker(point));										// to center
						var center = new GLatLng( point.lat(),point.lng());
						map.setCenter(center, (<?php echo get_variable('def_zoom'); ?>+2));		// zoom in 2 levels		
						}
					}
				);
			}
		}				// end function addrlkup()
	

	function is_float(str) {
	    return /^[-+]?\d+(\.\d+)?$/.test(str);
		}

	function validate(theForm) {	//
		var errmsg="";
		if (theForm.frm_name.value == "")		{errmsg+= "\tPlace Name is required\n";}
		if (theForm.frm_lat.value == "")		{errmsg+= "\tLatitude value is required\n";}
		else if (!
			(is_float(theForm.frm_lat.value) && 
			(theForm.frm_lat.value <=90.0) && 
			(theForm.frm_lat.value >= -90.0)
			)) 											{errmsg+= "\tValid latitude is required\n";}
		if (theForm.frm_lon.value == "")		{errmsg+= "\tLongitude value is required\n";}
		else if (!
			(is_float(JSfnTrim(theForm.frm_lon.value)) && 
			(theForm.frm_lon.value <=180.0) && 
			(theForm.frm_lon.value >= -180.0)
			)) 											{errmsg+= "\tValid longitude is required\n";}		
		
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.submit();
			}
		}				// end function validate(theForm)


</SCRIPT>
		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>"/>
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->


		<TABLE BORDER=0 ID='outer' ALIGN= 'center'>
		<TR><TD COLSPAN=2 ALIGN='center'><FONT CLASS="header"><?php echo get_variable('map_caption');?></FONT><BR /><BR /></TD></TR>
		<TR><TD>
			<TABLE BORDER="0" ALIGN="center">
			<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'places' - Add New Entry</FONT></TD></TR>
			<TR><TD>&nbsp;</TD></TR>
			<TR CLASS = "even"><TD CLASS="td_label">Lookup:</TD>
				<TD COLSPAN=3>&nbsp;&nbsp;<?php print get_text("City");?>:&nbsp;<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="the_city" VALUE="" />
				&nbsp;&nbsp;&nbsp;&nbsp;<?php print get_text("St");?>:&nbsp;<INPUT MAXLENGTH="4" SIZE="2" TYPE="text" NAME="the_st" VALUE="" /></TD></TR>
			<TR CLASS = "odd">
				<TD COLSPAN=4 ALIGN="center"><button type="button" onClick="addrlkup(this.form)">
				<img src="./markers/glasses.png" alt="Lookup location." /></TD></TR> <!-- 1/21/09 -->
			<TR><TD><BR /><BR /></TD></TR>
		<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right">Name:</TD>
			<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="64" SIZE="64" type="text" NAME="frm_name" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right">Lat:</TD>
			<TD><INPUT ID="ID2" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lat" VALUE="<?php echo get_variable('def_lat'); ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/>
			<SPAN CLASS="td_label" STYLE ="margin-left:20px;">Lon:&nbsp;&nbsp;&nbsp;
			<INPUT ID="ID3" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lon" VALUE="<?php echo get_variable('def_lng'); ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/></SPAN>
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
initialize();
</SCRIPT>
<?php
