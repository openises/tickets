<?php
function get_types($curr_val) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions` WHERE `id` = " . $curr_val;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_ret = $row['title'];	
	return $the_ret;
	}
?>
		<FORM NAME="v" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Road Condition Alerts - View Entry</FONT></TD></TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Title:</TD>
			<TD><?php print $row['title'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Description:</TD>
			<TD><?php print $row['description'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Address:</TD>
			<TD><?php print $row['address'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Type:</TD>
			<TD><?php print get_types($row['conditions']);?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Latitude:</TD>
			<TD><?php print $row['lat'];?></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Longitude:</TD>
			<TD><?php print $row['lng'];?></TD></TR>
		<TR>
			<TD COLSPAN="99">
				<DIV id = "map_canvas" style = "width: 500px; height: 500px; text-align: center;"></DIV>
			</TD>
		</TR>
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
init_map(3, <?php print $row['lat'];?>, <?php print $row['lng'];?>, "", 13, theLocale, 1);
map.setView([<?php print $row['lat'];?>, <?php print $row['lng'];?>], 13);
var bounds = map.getBounds();	
var zoom = map.getZoom();
</SCRIPT>
<?php
