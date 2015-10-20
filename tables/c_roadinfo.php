<?php
function get_types() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions` ORDER BY `id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_ret = "<SELECT NAME='frm_conditions'>";
	$the_ret .= "<OPTION VALUE='0' SELECTED>Select Condition Type</OPTION>";	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_ret .= "<OPTION VALUE=" .  $row['id'] . ">" . $row['title'] . "</OPTION>";
		}
	$the_ret .= "</SELECT>";
	return $the_ret;
	}
?>
		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="frm__by" 	VALUE="<?php print $_SESSION['user_id']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__from" 	VALUE="<?php print $_SERVER['REMOTE_ADDR']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__on" 	VALUE="<?php print mysql_format_date(time() - (get_variable('delta_mins')*60));?>" />
		<INPUT TYPE="hidden" NAME="frm_username" VALUE="<?php print $_SESSION['user']; ?>" />	
		
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top">
			<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Road Condition Alerts - Add New Entry</FONT></TD>
		</TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Title:</TD>
			<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="16" SIZE="16" type="text" NAME="frm_title" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Description:</TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_description" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Address:</TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_address" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Type:</TD>
			<TD><?php print get_types();?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Latitude:</TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_lat" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Longitude:</TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_lng" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<tr><td colspan=99 align='center'>
		</td></tr>
		<TR>
			<TD COLSPAN="99">
				<DIV id = "map_canvas" style = "width: 500px; height: 500px; text-align: center;"></DIV>
			</TD>
		</TR>
		<TR><TD COLSPAN="99" ALIGN="center">
		<BR />
		<INPUT TYPE="button"				VALUE="Cancel" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button"				VALUE="Reset" onClick = "Javascript: $('ID3').style.visibility='hidden'; document.c.frm_icon.value = ''; document.c.reset();" />&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" NAME="sub_but" VALUE="               Submit                " onclick="this.disabled=true; JSfnCheckInput(this.form, this );"/> 
		</TD></TR>
		</FORM>
		</TD></TR></TABLE>
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
		init_map(2, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 13, theLocale, 1);
		map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 13);
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
