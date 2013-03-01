<?php
/*
10/25/09 Created from functions_maj.inc.php
10/26/09 Added Facilities and hide and show unavailable units from functions_major.inc.php
10/27/09 Added check for scheduled incidents being due and bring to current situation screen if due and mark with * in list.
10/27/09 Added Booked date to Info Window tab 1.
11/27/09 corrections to indexing
3/27/10 added 'elapsed time' to IW
4/21/10 added closed incidents selection by time period, call history incident display replacing infowin
8/13/10 map.setUIToDefault();										// 
11/6/10 map size calculations for 2-screen operation
11/29/10 locale == 2 handling added
12/1/10 get_text disposition added
3/29/11 Added Incident List, Assignment List and moved marker show / hide controls to hide-able div. Added side menu controls.
4/5/11 Set shorten length by client screen width.
4/11/11 Where clause updated in all major queries to support Group functionality
6/10/11 Added Groups and Boundaries
7/3/11 added lines data, do_landb() - 
6/22/12 set def_zoom as zoom limit
*/
error_reporting(E_ALL);
$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();

//	dump ( $_GET);

//	snap(basename(__FILE__), __LINE__);
//	{ -- dummy

		$now_num = (time() - get_variable('delta_mins')*60);
		$temp = explode ("-", mysql_format_date($now_num));		// 2009-07-23 07:20:00
		$temp1 = explode (" ", $temp[2]);
		$now_day = (integer) $temp1[0];
		$now_mon = (integer) $temp[1];
		$now_year = (integer) $temp[0];
		for ($i=0; $i<7; $i++) {												// find time() at last Monday
			$temp_monday = mktime(0, 0, 0, date("m"), date("d")-$i, date("Y"));
			if (date('w', $temp_monday) == 1){
				break;
				}
			}
		$monday =  $temp_monday;
		
		
function fs_get_disp_status ($row_in) {			// 3/25/11
	$tags_arr = explode("/", get_variable('disp_stat'));
	if (is_date($row_in['u2farr'])) 	{ return $tags_arr[4];}
	if (is_date($row_in['u2fenr'])) 	{ return $tags_arr[3];}
	if (is_date($row_in['on_scene'])) 	{ return $tags_arr[2];}
	if (is_date($row_in['responding'])) { return $tags_arr[1];}
	if (is_date($row_in['dispatched'])) { return $tags_arr[0];}
	}		

	function full_scr($sort_by_field='',$sort_value='') {	// list tickets ===================================================
		global $now_num, $now_day, $now_mon, $now_year, $monday, $disposition, $curr_cats, $hidden, $shown, $un_stat_cats, $cat_sess_stat;
		
		if(($_SESSION['scr_width'] < 1300) && ($_SESSION['scr_width'] > 1050)) {		//	4/5/11	sets shorten length depending on client screen width
			$shorten_length = 11;
		} elseif ($_SESSION['scr_width'] < 1050) {
			$shorten_length = 10;
		} elseif ($_SESSION['scr_width'] > 1300) {
			$shorten_length = 15;
		} else {
			$shorten_length = 10;
		}
				
		extract ($_GET);
		$func = (isset($func))? $func : 0; 
		global $istest;
	//	$dzf = get_variable('def_zoom_fixed');			// 4/2/09
		$cwi = get_variable('closed_interval');			// closed window interval in hours
		$captions = array("Current situation", "Incidents closed today", "Incidents closed yesterday+", "Incidents closed this week", "Incidents closed last week", "Incidents closed last week+", "Incidents closed this month", "Incidents closed last month", "Incidents closed this year", "Incidents closed last year");
		$pri_buttons_width = (integer) get_variable('map_width') * .2; 	
		$fac_buttons_width = (integer) get_variable('map_width') * .4; 	
		$units_buttons_width = (integer) get_variable('map_width') * .4; 			
		$heading = $captions[$func];
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
		$group = isset($_SESSION['group']) ? $_SESSION['group'] : 0;	//	4/11/11
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_CLOSED']} ";		// 10/26/09
	
			$result_ct = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_closed = mysql_num_rows($result_ct); 
			unset($result_ct);
	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = {$GLOBALS['STATUS_SCHEDULED']} ";		// 10/26/09
			$result_scheduled = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$num_scheduled = mysql_num_rows($result_scheduled); 
			unset($result_scheduled);
			
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 6/10/11
		$result = mysql_query($query);	// 6/10/11
		$al_groups = array();
		$al_names = "";	
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 6/10/11
			$al_groups[] = $row['group'];
			$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 6/10/11
			$result2 = mysql_query($query2);	// 6/10/11
			while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	// 6/10/11		
					$al_names .= $row2['group_name'] . ", ";
				}
			}			
	
?>
<!-- 3/29/11 DIVS Incident List & Assignments List -->

	<DIV ID = 'inc_list' style='width: 46%; position: fixed; top: 5%; left: 3%; z-index:3; display: block; border: 2px outset #CECECE;'>
	<DIV class='heading' style='text-align: center; height: 20px; '><?php print get_text('Incidents');?><DIV style='float: right'>
	<SPAN id='collapse_ticks' onClick="hideDiv('inc_list', 'collapse_ticks', 'expand_ticks'); CngMenuClass('incs_but', 'right_menu');" style = "display: 'none';"><IMG SRC = './buttons/close.png' BORDER=0 STYLE = 'vertical-align: middle'></SPAN></DIV></DIV>
	<DIV ID = 'incidents' style='background: #CECECE; width: 100%; max-height: 200px; overflow-y: scroll; overflow-x: hidden; position: relative; z-index:2; display: block;'></DIV>		<!-- 3/24/11 List of current open incidents -->
	</DIV>

	<DIV ID = 'assigns_list' style='width: 46%; position: fixed; top: 5%; right: 3%; z-index:3; display: block; border: 2px outset #CECECE;'>
	<DIV class='heading' style='text-align: center; height: 20px;'><?php print get_text('Assignments');?><DIV style='float: right'>
	<SPAN id='collapse_ass' onClick="hideDiv('assigns_list', 'collapse_ass', 'expand_ass'); CngMenuClass('assign_but', 'right_menu');" style = "display: 'none';"><IMG SRC = './buttons/close.png' BORDER=0 STYLE = 'vertical-align: middle'></SPAN></DIV></DIV>
	<DIV ID = 'assignments' style='background: #CECECE; width: 100%; max-height: 200px; overflow-y: scroll; overflow-x: hidden; position: relative; z-index:2; display: block;'></DIV>		<!-- 3/24/11 List of current assignments -->
	</DIV>

<!-- 3/29/11 DIVS for show hide side menu for lists and marker controls -->
	<DIV ID='side_menu_cont' class='right_menu_container' style='position: fixed; top: 10%; right: 0px;'>
	<BR />

<script>
	var text_size = window.screen.width > 1000 ? "16" : "12";

	var div_button1 = "<DIV ID='marker_but' CLASS='right_menu' style='font-size:" + text_size + "px' onMouseover=\"showDiv('buttons_sh', 'collapse_buttons', 'expand_buttons'); CngMenuClass('marker_but', 'right_menu_lit');\">M<BR />A<BR />R<BR />K<BR />E<BR />R<BR />S</DIV><BR />";
	var div_button2 = "<DIV ID='incs_but' CLASS='right_menu' style='font-size:" + text_size + "px' onMouseover=\"showDiv('inc_list', 'collapse_ticks', 'expand_ticks'); CngMenuClass('incs_but', 'right_menu_lit');\">I<BR />N<BR />C<BR />I<BR />D<BR />E<BR />N<BR />T<BR />S</DIV><BR />";
	var div_button3 = "<DIV ID='assign_but' CLASS='right_menu' style='font-size:" + text_size + "px' onMouseover=\"showDiv('assigns_list', 'collapse_ass', 'expand_ass'); CngMenuClass('assign_but', 'right_menu_lit');\">A<BR />S<BR />S<BR />I<BR />G<BR />N<BR />M<BR />E<BR />N<BR />T<BR />S</DIV><BR />";
	document.write (div_button1);
	document.write (div_button2);
	document.write (div_button3);
</script>
	</DIV>

<!-- 3/29/11 Marker controls -->	
	<DIV ID = 'buttons_sh' class='fs_buttons' style='display: none; background: #CECECE; width: 300px; overflow-y: hidden; overflow-x: hidden; position: fixed; right: 10%; top: 20%; z-index:4; border: 3px outset #505050;'>
	<DIV class='heading' style='text-align: center; height: 20px;' style='z-index:4; padding: 3px;'>Show / Hide Markers<DIV style='float: right'><SPAN id='collapse_buttons' STYLE = 'text-align: right' onClick="hideDiv('buttons_sh', 'collapse_buttons', 'expand_buttons'); CngMenuClass('marker_but', 'right_menu');"><IMG SRC = './buttons/close.png' BORDER=0 STYLE = 'vertical-align: middle'></SPAN></DIV></DIV>
	<DIV ID = 'incidents_sh' style='display: block; position: relative; padding: 10px; overflow-y: scroll; overflow-x: scroll;'>
	<DIV class='heading' style='text-align: center; height: 20px; ' style='z-index:4; padding: 3px;'><?php print get_text('Incidents');?></DIV>
	<DIV class='pri_button_fs' onClick="set_pri_chkbox('normal'); hideGroup(1, 'Incident');"><IMG SRC = './our_icons/sm_blue.png' STYLE = 'vertical-align: middle'BORDER=0>&nbsp;&nbsp;Normal: <input type=checkbox id='normal'  onClick="set_pri_chkbox('normal')"/>&nbsp;&nbsp;</DIV>
	<DIV class='pri_button_fs' onClick="set_pri_chkbox('medium'); hideGroup(2, 'Incident');"><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;Medium: <input type=checkbox id='medium'  onClick="set_pri_chkbox('medium')"/>&nbsp;&nbsp;</DIV>
	<DIV class='pri_button_fs' onClick="set_pri_chkbox('high'); hideGroup(3, 'Incident');"><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;High: <input type=checkbox id='high'  onClick="set_pri_chkbox('high')"/>&nbsp;&nbsp;</DIV>
	<DIV class='pri_button_fs' ID = 'pri_all' class='pri_button' STYLE = 'display: none;' onClick="set_pri_chkbox('all'); hideGroup(4, 'Incident');"><IMG SRC = './our_icons/sm_blue.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>All <input type=checkbox id='all'  STYLE = 'display:none;' onClick="set_pri_chkbox('all')"/>&nbsp;&nbsp;</DIV>
	<DIV class='pri_button_fs' ID = 'pri_none' class='pri_button' onClick="set_pri_chkbox('none'); hideGroup(5, 'Incident');"><IMG SRC = './our_icons/sm_white.png' BORDER=0 STYLE = 'vertical-align: middle'> None <input type=checkbox id='none' STYLE = 'display:none;' onClick="set_pri_chkbox('none')"/></DIV></b>
	</DIV><BR />
	<DIV ID = 'units_sh' style='display: block; position: relative; padding: 10px; overflow-y: scroll; overflow-x: scroll;'>
	<DIV class='heading' style='text-align: center; height: 20px;' style='z-index:4; padding: 3px;'><?php print get_text('Units');?></DIV>
	<DIV ID = 'boxes' style='position: relative; padding: 3px;'></DIV>		<!-- 2/16/11 Units show and hide -->
	</DIV><BR />
	<DIV ID = 'facs_sh' style='display: block; position: relative; padding: 10px; overflow-y: scroll; overflow-x: scroll;'>
	<DIV class='heading' style='text-align: center; height: 20px;' style='z-index:4; padding: 3px;'><?php print get_text('Facilities');?></DIV>	
	<DIV ID = 'fac_boxes' style='position: relative; padding: 3px;'></DIV>		<!-- 2/16/11 Facilities show and hide -->
	</DIV><BR />
	</DIV>
	</DIV>

	<DIV ID = 'bottom_bar' class='td_fs_buttons' style='display: table-cell; position: fixed; bottom: 0%; left: 0%; width: 100%; z-index: 3; height: 5%; text-align: center; vertical-align: middle; padding-top: 5px; border-top: 4px outset #CECECE;'>
	<B><NOBR>	<!-- 2/16/11 Change CSS classes -->
	<FORM>		
		<SPAN class='fs_buttons' onClick='maxWindow();'><U>Full screen</U></SPAN>
		<SPAN class='fs_buttons' onClick='doGrid()' STYLE = 'margin-left: 60px'><U>Grid</U></SPAN>
		<SPAN class='fs_buttons' onClick='doTraffic()' STYLE = 'margin-left: 60px'><U>Traffic</U></SPAN>
<?php
//		if(((!empty($num_closed)) && ($num_closed > 0)) || ($num_scheduled > 0)) {					// 10/26/09  added button, 10/21/09 added check for closed incidents on the database, 3/29/11 added scheduled runs option
			echo "<SPAN class='fs_buttons' STYLE =  'margin-left: 60px'><U>Change display</U>&nbsp;&raquo;&nbsp;</SPAN>";
			echo "\n\t\t <SELECT NAME = 'frm_interval' onChange = 'document.to_all.func.value=this.value; show_btns_closed();'>
				<OPTION VALUE='99' SELECTED>Select</OPTION>
				<OPTION VALUE='0'>Current situation</OPTION>
				<OPTION VALUE='1'>Incidents closed today</OPTION>
				<OPTION VALUE='2'>Incidents closed yesterday+</OPTION>
				<OPTION VALUE='3'>Incidents closed this week</OPTION>
				<OPTION VALUE='4'>Incidents closed last week</OPTION>
				<OPTION VALUE='5'>Incidents closed last week+</OPTION>
				<OPTION VALUE='6'>Incidents closed this month</OPTION>
				<OPTION VALUE='7'>Incidents closed last month</OPTION>
				<OPTION VALUE='8'>Incidents closed this year</OPTION>
				<OPTION VALUE='9'>Incidents closed last year</OPTION>
				<OPTION VALUE='10'>Scheduled Runs</OPTION>				
				</SELECT>\n";
			echo "<SPAN ID = 'btn_go' class='fs_buttons' onClick='document.to_all.submit()' STYLE = 'margin-left: 10px; display:none; color: #006600;'><U>Go</U></SPAN>";
			echo "<SPAN ID = 'btn_can' class='fs_buttons' onClick='hide_btns_closed()' STYLE = 'margin-left: 10px; display:none; color: #FF0000;'><U>Cancel</U></SPAN>";

//			}
?>
		<SPAN class='fs_buttons' onClick = "opener.focus()" STYLE =  'margin-left: 60px'><U>Back</U></SPAN>
		<A HREF="mailto:shoreas@Gmail.com?subject=Comment%20on%20Tickets%20Dispatch%20System"><SPAN STYLE = 'margin-left: 20px; font-size:10px; '><U>Contact us</U> <IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom; margin-left: 10px;"></SPAN></A>
		<SPAN class='fs_buttons' onClick = "window.close();" STYLE =  'margin-left: 60px'><U>Close</U></SPAN>
		</NOBR>
		</B>
		</FORM>
		</DIV>
		
			
		<FORM NAME='view_form' METHOD='get' ACTION='units.php'>
		<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
		<INPUT TYPE='hidden' NAME='view' VALUE='true'>
		<INPUT TYPE='hidden' NAME='id' VALUE=''>
		</FORM>
	
	<DIV style='position: fixed; top: 0px; left: 0px, z-index: 1'>
	<TABLE BORDER=1 STYLE= "margin-top:0;">
		<TR CLASS='header'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header'><?php print get_variable('map_caption') . " - " .  $heading;?> <SPAN ID='sev_counts' STYLE = 'margin-left: 40px'></SPAN></FONT></TD></TR>	<!-- 1/17/09 -->

		<TR ID='map_row'>
			<TD COLSPAN='99' CLASS='td_label' width="100%" height="82%">

<?php
	@session_start();							// 
	$by_severity = array(0, 0, 0);				// counters
?>
<script>
			var map_width = window.screen.width -16;		// 11/6/10
			var map_height = window.screen.height *.82;		// browser-dependent
			var div_style_str = "<DIV ID='map' STYLE='WIDTH:" + map_width + "px; height:" + map_height + "px';></DIV>"
			document.write (div_style_str);
</script>
		</TD></TR>
		</TABLE></DIV>
<SCRIPT>
	function show_btns_closed() {
		$('btn_go').style.display = 'inline';
		$('btn_can').style.display = 'inline';
		}
	function hide_btns_closed() {
		$('btn_go').style.display = 'none';
		$('btn_can').style.display = 'none';
		document.dummy.frm_interval.selectedIndex=99;
		}
		
</SCRIPT>

	
	<SCRIPT>
		function isNull(val) {								// checks var stuff = null;
			return val === null;
			}
	
		function to_str(instr) {			// 0-based conversion - 2/13/09
	//		alert("143 " + instr);
			function ord( string ) {
			    return (string+'').charCodeAt(0);
				}
	
			function chr( ascii ) {
			    return String.fromCharCode(ascii);
				}
			function to_char(val) {
				return(chr(ord("A")+val));
				}
	
			var lop = (instr % 26);													// low-order portion, a number
			var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
			return hop+to_char(lop);
			}
	
		function sendRequest(url,callback,postData) {								// 2/14/09
			var req = createXMLHTTPObject();
			if (!req) return;
			var method = (postData) ? "POST" : "GET";
			req.open(method,url,true);
			req.setRequestHeader('User-Agent','XMLHTTP/1.0');
			if (postData)
				req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
			req.onreadystatechange = function () {
				if (req.readyState != 4) return;
				if (req.status != 200 && req.status != 304) {
<?php
		if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
	?>
					return;
					}
				callback(req);
				}
			if (req.readyState == 4) return;
			req.send(postData);
			}
	
		var XMLHttpFactories = [
			function () {return new XMLHttpRequest()	},
			function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
			function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
			function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
			];
	
		function createXMLHTTPObject() {
			var xmlhttp = false;
			for (var i=0;i<XMLHttpFactories.length;i++) {
				try {
					xmlhttp = XMLHttpFactories[i]();
					}
				catch (e) {
					continue;
					}
				break;
				}
			return xmlhttp;
			}
	
	if (GBrowserIsCompatible()) {
	
		$("map").style.backgroundImage = "url('http://maps.google.com/staticmap?center=<?php echo get_variable('def_lat');?>,<?php echo get_variable('def_lng');?>&zoom=<?php echo get_variable('def_zoom');?>&size=<?php echo get_variable('map_width');?>x<?php echo get_variable('map_height');?>&key=<?php echo get_variable('gmaps_api_key');?> ')";
	
		var colors = new Array ('odd', 'even');

		function add_hash(in_str) { // prepend # if absent
			return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
			}

		function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity) {		// 8/19/09
		
	//		drawCircle(53.479874, -2.246704, 10.0, "#000080", 1, 0.75, "#0000FF", .5);

			var d2r = Math.PI/180;
			var r2d = 180/Math.PI;
			var Clat = radius * 0.014483;
			var Clng = Clat/Math.cos(lat * d2r);
			var Cpoints = [];
			for (var i=0; i < 33; i++) {
				var theta = Math.PI * (i/16);
				Cy = lat + (Clat * Math.sin(theta));
				Cx = lng + (Clng * Math.cos(theta));
				var P = new GPoint(Cx,Cy);
				Cpoints.push(P);
				}
			var polygon = new GPolygon(Cpoints, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity);
			map.addOverlay(polygon);
			}
			
		function drawBanner(point, html, text, font_size, color) {        // Create the banner
		//	alert("<?php echo __LINE__;?> " + color);
			var invisibleIcon = new GIcon(G_DEFAULT_ICON, "./markers/markerTransparent.png");      // Custom icon is identical to the default icon, except invisible

			map.setCenter(point, 8);
			map.addControl(new GLargeMapControl());
			map.addControl(new GMapTypeControl());
			var the_color = (typeof color == 'undefined')? "#000000" : color ;	// default to black

			var style_str = 'background-color:transparent;font-weight:bold;border:0px black solid;white-space:nowrap; font-size:' + font_size + 'px; font-family:arial; opacity: 0.9; color:' + add_hash(the_color) + ';';

			var contents = '<div><div style= "' + style_str + '">'+text+'<\/div><\/div>';
			var label=new ELabel(point, contents, null, new GSize(-8,4), 75, 1);
			map.addOverlay(label);
			
			var marker = new GMarker(point,invisibleIcon);	        // Create an invisible GMarker
		//	map.addOverlay(marker);
			
			}				// end function draw Banner()		

		function do_landb() {				// JS function - 8/1/11
			var points = new Array();
	<?php
			$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `line_status` = 0 AND `use_with_bm` = 1";
			$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
				$empty = FALSE;
				extract ($row);
				$name = $row['line_name'];
				switch ($row['line_type']) {
					case "p":		// poly
						$points = explode (";", $line_data);
						echo "\n\tvar points = new Array();\n";
						for ($i = 0; $i<count($points); $i++) {
							$coords = explode (",", $points[$i]);
	?>
							var thepoint = new GLatLng(<?php print $coords[0];?>, <?php print $coords[1];?>);
							bounds.extend(thepoint);
							points.push(thepoint);
	<?php					}			// end for ($i = 0 ... )
					if ((intval($filled) == 1) && (count($points) > 2)) {?>
							var polyline = new GPolygon(points,add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>,add_hash("<?php print $fill_color;?>"), <?php print $fill_opacity;?>);
	<?php			} else {?>
							var polyline = new GPolyline(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>);
	<?php			} ?>				        
							map.addOverlay(polyline);
	<?php				
						break;
				
					case "c":		// circle
						$temp = explode (";", $line_data);
						$radius = $temp[1];
						$coords = explode (",", $temp[0]);
						$lat = $coords[0];
						$lng = $coords[1];
						$fill_opacity = (intval($filled) == 0)?  0 : $fill_opacity;
						
						echo "\n drawCircle({$lat}, {$lng}, {$radius}, add_hash('{$line_color}'), {$line_width}, {$line_opacity}, add_hash('{$fill_color}'), {$fill_opacity}, {$name}); // 513\n";
						break;
					case "t":		// text banner

						$temp = explode (";", $line_data);
						$banner = $temp[1];
						$coords = explode (",", $temp[0]);
						echo "\n var point = new GLatLng(parseFloat({$coords[0]}) , parseFloat({$coords[1]}));\n";
						$the_banner = htmlentities($banner, ENT_QUOTES);
						$the_width = intval( trim($line_width), 10);		// font size
						echo "\n drawBanner( point, '{$the_banner}', '{$the_banner}', {$the_width});\n";
						break;
					}	// end switch
			}			// end while ()
			unset($query, $result);
	?>
			}		// end function do_landb()
			
		function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
															// NOT correspond with what browsers actually do...
			var SAFECHARS = "0123456789" +					// Numeric
							"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
							"abcdefghijklmnopqrstuvwxyz" +	// guess
							"-_.!*'()";					// RFC2396 Mark characters
			var HEX = "0123456789ABCDEF";
		
			var encoded = "";
			for (var i = 0; i < plaintext.length; i++ ) {
				var ch = plaintext.charAt(i);
				if (ch == " ") {
					encoded += "+";				// x-www-urlencoded, rather than %20
				} else if (SAFECHARS.indexOf(ch) != -1) {
					encoded += ch;
				} else {
					var charCode = ch.charCodeAt(0);
					if (charCode > 255) {
						alert( "Unicode Character '"
								+ ch
								+ "' cannot be encoded using standard URL encoding.\n" +
								  "(URL encoding only supports 8-bit characters.)\n" +
								  "A space (+) will be substituted." );
						encoded += "+";
					} else {
						encoded += "%";
						encoded += HEX.charAt((charCode >> 4) & 0xF);
						encoded += HEX.charAt(charCode & 0xF);
						}
					}
				} 			// end for(...)
			return encoded;
			};			// end function					

//	Tickets show / hide by Priority functions

	function set_initial_pri_disp() {
		$('normal').checked = true;
		$('medium').checked = true;
		$('high').checked = true;
		$('all').checked = true;
		$('none').checked = false;
		$('buttons_sh').style.display = 'none';
		$('incidents').style.display = '';
		$('assignments').style.display = '';		
	}

	function hideGroup(color, category) {			// 8/7/09 Revised function to correct incorrect display, revised 12/03/10 completely revised
		var priority = color;
		var priority_name="";
		if(priority == 1) {
			priority_name="normal";
		}
		if(priority == 2) {
			priority_name="medium";
		}
		if(priority == 3) {
			priority_name="high";
		}
		if(priority == 4) {
			priority_name="all";
		}

		if(priority == 5) {
			priority_name="none";
		}

		if(priority == 1) {
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if ((gmarkers[i].id == priority) && (gmarkers[i].category == category)) {
						gmarkers[i].show();
						}
					if ((gmarkers[i].id != priority) && (gmarkers[i].category == category)) {
						gmarkers[i].hide();
						}

					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = true;
			$('medium').checked = false;
			$('high').checked = false;
			$('all').checked = false;
			$('none').checked = false;
			$('pri_all').style.display = '';
			$('pri_none').style.display = '';
			}	//	end if priority == 1
		if(priority == 2) {
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if ((gmarkers[i].id == priority) && (gmarkers[i].category == category)) {
						gmarkers[i].show();
						}
					if ((gmarkers[i].id != priority) && (gmarkers[i].category == category)) {
						gmarkers[i].hide();
						}

					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = false;
			$('medium').checked = true;
			$('high').checked = false;
			$('all').checked = false;
			$('none').checked = false;
			$('pri_all').style.display = '';
			$('pri_none').style.display = '';
			}	//	end if priority == 2
		if(priority == 3) {
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if ((gmarkers[i].id == priority) && (gmarkers[i].category == category)) {
						gmarkers[i].show();
						}
					if ((gmarkers[i].id != priority) && (gmarkers[i].category == category)) {
						gmarkers[i].hide();
						}

					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = false;
			$('medium').checked = false;
			$('high').checked = true;
			$('all').checked = false;
			$('none').checked = false;
			$('pri_all').style.display = '';
			$('pri_none').style.display = '';
			}	//	end if priority == 3
		if(priority == 4) {		//	show All
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if (gmarkers[i].category == category) {
						gmarkers[i].show();
						}
					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = true;
			$('medium').checked = true;
			$('high').checked = true;
			$('all').checked = true;
			$('none').checked = false;
			$('pri_all').style.display = 'none';
			$('pri_none').style.display = '';
			}	//	end if priority == 4
		if(priority == 5) {		// hide all
			for (var i = 0; i < gmarkers.length; i++) {
				if (gmarkers[i]) {
					if (gmarkers[i].category == category) {
						gmarkers[i].hide();
						}
					}		// end if (gmarkers[i])
				} 	// end for ()
			$('normal').checked = false;
			$('medium').checked = false;
			$('high').checked = false;
			$('all').checked = false;
			$('none').checked = true;
			$('pri_all').style.display = '';
			$('pri_none').style.display = 'none';
			}	//	end if priority == 5
		}			// end function hideGroup(color, category)

	function set_pri_chkbox(control) {
		var pri_control = control;
		if($(pri_control).checked == true) {
			$(pri_control).checked = false;
			} else {
			$(pri_control).checked = true;
			}
		}

//	End of Tickets show / hide by Priority functions		

// 	Units show / hide functions				
		
	function set_categories() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show.
		var curr_cats = <?php echo json_encode($curr_cats); ?>;
		var cat_sess_stat = <?php echo json_encode($cat_sess_stat); ?>;
		var hidden = <?php print json_encode($hidden); ?>;
		var shown = <?php print json_encode($shown); ?>;
		var number_of_units = <?php print get_no_units(); ?>;
		if(hidden!=0) {
			$('ALL').style.display = '';
			$('ALL_BUTTON').style.display = '';
			$('ALL').checked = false;	
		} else {			
			$('ALL').style.display = 'none';
			$('ALL_BUTTON').style.display = 'none';
			$('ALL').checked = false;
		}
		if(shown!=0) {
			$('NONE').style.display = '';
			$('NONE_BUTTON').style.display = '';
			$('NONE').checked = false;	

		} else {
			$('NONE').style.display = 'none';
			$('NONE_BUTTON').style.display = 'none';
			$('NONE').checked = false;
		}
		if(number_of_units==0) {
			$('ALL').style.display = 'none';
			$('ALL_BUTTON').style.display = 'none';
			$('ALL').checked = false;
			$('NONE').style.display = 'none';
			$('NONE_BUTTON').style.display = 'none';
			$('NONE').checked = false;				
		}
		for (var i = 0; i < curr_cats.length; i++) {
			var catname = curr_cats[i];
			if(cat_sess_stat[i]=="s") {
				for (var j = 0; j < gmarkers.length; j++) {
					if ((gmarkers[j]) && (gmarkers[j].category) && (gmarkers[j].category == catname)) {
						gmarkers[j].show();
						}
					}
				$(catname).checked = true;
			} else {
				for (var j = 0; j < gmarkers.length; j++) {
					if ((gmarkers[j]) && (gmarkers[j].category) && (gmarkers[j].category == catname)) {
						gmarkers[j].hide();
						}
					}
				$(catname).checked = false;
				}				
			}
		}
		

	function do_view_cats() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('go_can').style.display = 'inline';
		$('can_button').style.display = 'inline';
		$('go_button').style.display = 'inline';
		}

	function cancel_buttons() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('go_can').style.display = 'none';
		$('can_button').style.display = 'none';
		$('go_button').style.display = 'none';
		$('ALL').checked = false;
		$('NONE').checked = false;
		}

	function set_chkbox(control) {
		var units_control = control;
		if($(units_control).checked == true) {
			$(units_control).checked = false;
			} else {
			$(units_control).checked = true;
			}
		do_view_cats();
		}

	function do_go_button() {							// 12/03/10	Show Hide categories
		var curr_cats = <?php echo json_encode(get_category_butts()); ?>;
		if ($('ALL').checked == true) {
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];
				var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
				var url = "persist2.php";
				sendRequest (url, gb_handleResult, params);
				$(category).checked = true;				
				for (var j = 0; j < gmarkers.length; j++) {
					if((gmarkers[j]) && (gmarkers[j].category!="Incident")) {				
					gmarkers[j].show();
					}
					}
				}
				$('ALL').checked = false;
				$('ALL').style.display = 'none';
				$('ALL_BUTTON').style.display = 'none';				
				$('NONE').style.display = '';
				$('NONE_BUTTON').style.display = '';				
				$('go_button').style.display = 'none';
				$('can_button').style.display = 'none';				

		} else if ($('NONE').checked == true) {
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];
				var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
				var url = "persist2.php";
				sendRequest (url, gb_handleResult, params);	
				$(category).checked = false;				
				for (var j = 0; j < gmarkers.length; j++) {
					if((gmarkers[j]) && (gmarkers[j].category!="Incident")) {
						gmarkers[j].hide();
					}
					}
				}
				$('NONE').checked = false;
				$('ALL').style.display = '';
				$('ALL_BUTTON').style.display = '';				
				$('NONE').style.display = 'none';
				$('NONE_BUTTON').style.display = 'none';					
				$('go_button').style.display = 'none';
				$('can_button').style.display = 'none';
		} else {
			var x = 0;
			var y = 0;
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];
				if (category!="Incident") {
					if ($(category).checked == true) {
						x++;
						var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
						var url = "persist2.php";
						sendRequest (url, gb_handleResult, params);
						$(category).checked = true;			
						for (var j = 0; j < gmarkers.length; j++) {
							if ((gmarkers[j]) && (gmarkers[j].category) && (gmarkers[j].category == category)) {	
//								alert("Showing gmarker " + j + " in Category " + category);
								gmarkers[j].show();
								}
							}
						}
					}
				}
			for (var i = 0; i < curr_cats.length; i++) {
				var category = curr_cats[i];
				if (category!="Incident") {				
					if ($(category).checked == false) {
						y++;
						var params = "f_n=show_hide_" +URLEncode(category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
						var url = "persist2.php";
						sendRequest (url, gb_handleResult, params);
						$(category).checked = false;
						var y=0;
						for (var j = 0; j < gmarkers.length; j++) {
							if ((gmarkers[j]) && (gmarkers[j].category) && (gmarkers[j].category == category)) {
//								alert("Hiding gmarker " + j + " in Category " + category);							
								gmarkers[j].hide();
								}
							}
						}	
					}
				}
			}

		$('go_button').style.display = 'none';
		$('can_button').style.display = 'none';

		if((x > 0) && (x < curr_cats.length)) {
			$('ALL').style.display = '';
			$('ALL_BUTTON').style.display = '';
			$('NONE').style.display = '';
			$('NONE_BUTTON').style.display = '';
		}
		if(x == 0) {
			$('ALL').style.display = '';
			$('ALL_BUTTON').style.display = '';
			$('NONE').style.display = 'none';
			$('NONE_BUTTON').style.display = 'none';
		}
		if(x == curr_cats.length) {
			$('ALL').style.display = 'none';
			$('ALL_BUTTON').style.display = 'none';
			$('NONE').style.display = '';
			$('NONE_BUTTON').style.display = '';
		}


	}	// end function do_go_button()

	function gb_handleResult(req) {							// 12/03/10	The persist callback function
		}

// Facilities show / hide functions		

	function set_fac_categories() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show.
		var fac_curr_cats = <?php echo json_encode(get_fac_category_butts()); ?>;
		var fac_cat_sess_stat = <?php echo json_encode(get_fac_session_status()); ?>;
		var fac_hidden = <?php print find_fac_hidden(); ?>;
		var fac_shown = <?php print find_fac_showing(); ?>;
		if(fac_hidden!=0) {
			$('fac_ALL').style.display = '';
			$('fac_ALL_BUTTON').style.display = '';
			$('fac_ALL').checked = false;	
		} else {			
			$('fac_ALL').style.display = 'none';
			$('fac_ALL_BUTTON').style.display = 'none';
			$('fac_ALL').checked = false;
		}
		if(fac_shown!=0) {
			$('fac_NONE').style.display = '';
			$('fac_NONE_BUTTON').style.display = '';
			$('fac_NONE').checked = false;
		} else {
			$('fac_NONE').style.display = 'none';
			$('fac_NONE_BUTTON').style.display = 'none';
			$('fac_NONE').checked = false;
		}
		for (var i = 0; i < fac_curr_cats.length; i++) {
			var fac_catname = fac_curr_cats[i];
			if(fac_cat_sess_stat[i]=="s") {
				for (var j = 0; j < fmarkers.length; j++) {
					if (fmarkers[j].category == fac_catname) {
						fmarkers[j].show();
						}
					}
				$(fac_catname).checked = true;
			} else {
				for (var j = 0; j < fmarkers.length; j++) {
					if (fmarkers[j].category == fac_catname) {
						fmarkers[j].hide();
						}
					}
				$(fac_catname).checked = false;
				}				
			}
		}

	function do_view_fac_cats() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('fac_go_can').style.display = 'inline';
		$('fac_can_button').style.display = 'inline';
		$('fac_go_button').style.display = 'inline';
		}

	function fac_cancel_buttons() {							// 12/03/10	Show Hide categories, Showing and setting onClick attribute for Next button for category show / hide.
		$('fac_go_can').style.display = 'none';
		$('fac_can_button').style.display = 'none';
		$('fac_go_button').style.display = 'none';
		$('fac_ALL').checked = false;
		$('fac_NONE').checked = false;
		}

	function set_fac_chkbox(control) {
		var fac_control = control;
		if($(fac_control).checked == true) {
			$(fac_control).checked = false;
			} else {
			$(fac_control).checked = true;
			}
		do_view_fac_cats();
		}

	function do_go_facilities_button() {							// 12/03/10	Show Hide categories
		var fac_curr_cats = <?php echo json_encode(get_fac_category_butts()); ?>;
		if ($('fac_ALL').checked == true) {
			for (var i = 0; i < fac_curr_cats.length; i++) {
				var fac_category = fac_curr_cats[i];
				var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
				var url = "persist2.php";
				sendRequest (url, gb_handleResult, params);
				$(fac_category).checked = true;				
				for (var j = 0; j < fmarkers.length; j++) {
					if(fmarkers[j].category != "Incident") {				
					fmarkers[j].show();
					}
					}
				}
				$('fac_ALL').checked = false;
				$('fac_ALL').style.display = 'none';
				$('fac_ALL_BUTTON').style.display = 'none';				
				$('fac_NONE').style.display = '';
				$('fac_NONE_BUTTON').style.display = '';				
				$('fac_go_button').style.display = 'none';
				$('fac_can_button').style.display = 'none';

		} else if ($('fac_NONE').checked == true) {
			for (var i = 0; i < fac_curr_cats.length; i++) {
				var fac_category = fac_curr_cats[i];
				var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
				var url = "persist2.php";
				sendRequest (url, gb_handleResult, params);	
				$(fac_category).checked = false;				
				for (var j = 0; j < fmarkers.length; j++) {
					if(fmarkers[j].category != "Incident") {
						fmarkers[j].hide();
					}
					}
				}
				$('fac_NONE').checked = false;
				$('fac_ALL').style.display = '';
				$('fac_ALL_BUTTON').style.display = '';				
				$('fac_NONE').style.display = 'none';
				$('fac_NONE_BUTTON').style.display = 'none';					
				$('fac_go_button').style.display = 'none';
				$('fac_can_button').style.display = 'none';
		} else {
			var x = 0;
			var y = 0;
			for (var i = 0; i < fac_curr_cats.length; i++) {

				var fac_category = fac_curr_cats[i];
				if ($(fac_category).checked == true) {
					x++;
					var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
					var url = "persist2.php";
					sendRequest (url, gb_handleResult, params);
					$(fac_category).checked = true;			
					for (var j = 0; j < fmarkers.length; j++) {
						if(fmarkers[j].category == fac_category) {			
							fmarkers[j].show();
							}
						}
					}
				}
			for (var i = 0; i < fac_curr_cats.length; i++) {
				var fac_category = fac_curr_cats[i];				
				if ($(fac_category).checked == false) {
					y++;
					var params = "f_n=show_hide_fac_" +URLEncode(fac_category)+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
					var url = "persist2.php";
					sendRequest (url, gb_handleResult, params);
					$(fac_category).checked = false;
					var y=0;
					for (var j = 0; j < fmarkers.length; j++) {
						if(fmarkers[j].category == fac_category) {			
							fmarkers[j].hide();
							}
						}
					}	
				}
			}

		var hidden = <?php print find_hidden($curr_cats); ?>;
		var shown = <?php print find_showing($curr_cats); ?>;

		$('fac_go_button').style.display = 'none';
		$('fac_can_button').style.display = 'none';

		if((x > 0) && (x < fac_curr_cats.length)) {
			$('fac_ALL').style.display = '';
			$('fac_ALL_BUTTON').style.display = '';
			$('fac_NONE').style.display = '';
			$('fac_NONE_BUTTON').style.display = '';
		}
		if(x == 0) {
			$('fac_ALL').style.display = '';
			$('fac_ALL_BUTTON').style.display = '';
			$('fac_NONE').style.display = 'none';
			$('fac_NONE_BUTTON').style.display = 'none';
		}
		if(x == fac_curr_cats.length) {
			$('fac_ALL').style.display = 'none';
			$('fac_ALL_BUTTON').style.display = 'none';
			$('fac_NONE').style.display = '';
			$('fac_NONE_BUTTON').style.display = '';
		}


	}	// end function do_go_button()

	function gfb_handleResult(req) {							// 12/03/10	The persist callback function
		}

// end of facilities show / hide function

		function hideDiv(div_area, hide_cont, show_cont) {	//	3/29/11, function forhiding DIVS for control areas
			var divarea = div_area 
			var hide_cont = hide_cont 
			var show_cont = show_cont 
			if($(divarea)) {
				$(divarea).style.display = 'none';
				} 
			} 

		function showDiv(div_area, hide_cont, show_cont) {	//	3/29/11, function for showing DIVS for control areas
			var divarea = div_area
			var hide_cont = hide_cont 
			var show_cont = show_cont 
			if($(divarea)) {
				$(divarea).style.display = '';
				} 
			} 
			
		var starting = false;

		function check_sidemenu() {
			if($('inc_list').style.display=="block") {
				$('incs_but').className="right_menu_lit";
				}
			if($('buttons_sh').style.display=="block") {
				$('marker_but').className="right_menu_lit";
				}
			if($('assigns_list').style.display=="block") {
				$('assign_but').className="right_menu_lit";
				}
			}
		
		function myclick(id) {					// 3/29/11	For incident list clicks to launch infoWindow
			GEvent.trigger(gmarkers[id], "click");
			location.href = "#top";
			}		
	
		function do_mail_fac_win(id) {			// Facility email 9/22/09
			if(starting) {return;}					
			starting=true;	
			var url = "do_fac_mail.php?fac_id=" + id;	
			newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height=300,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
			if (isNull(newwindow_in)) {
				alert ("This requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_in.focus();
			starting = false;
			}
	
		function do_show_Units() {
			var params = "f_n=show_hide_unit&v_n=s&sess_id=<?php print get_sess_key(basename(__FILE__) . __LINE__); ?>";					// flag 1, value s
			var url = "persist.php";
			sendRequest (url, s_handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
			}			// end function do notify()
	
		function s_handleResult(req) {					// the 'called-back' persist function - show
			show_Units();
			}
	
		function createMarker(point, tabs, color, stat, id, sym, category) {					// Creates marker and sets up click event infowindow
			points = true;
			var icon = new GIcon(baseIcon);
			var icon_url = "./our_icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + sym;				// 1/6/09
			icon.image = icon_url;
	
			var marker = new GMarker(point, icon);
			marker.id = color;				// for hide/unhide
			marker.category = category;		// 12/03/10 for show / hide by status	
			marker.stat = stat;				// 10/21/09
	
			GEvent.addListener(marker, "click", function() {					// here for icon click
//				if (ticket_ids[(id-1)]) {
//					open_tick_window (ticket_ids[(id-1)]);
//					}
//				else {
					map.closeInfoWindow();
					which = id;
					gmarkers[which].hide();
					marker.openInfoWindowTabsHtml(infoTabs[id]);
		
					setTimeout(function() {											// wait for rendering complete - 11/6/08
						if ($("detailmap")) {				// 10/9/08
							var dMapDiv = $("detailmap");
							var detailmap = new GMap2(dMapDiv);
							detailmap.addControl(new GSmallMapControl());
							detailmap.setCenter(point, 17);  						// larger # = closer
							detailmap.addOverlay(marker);
							}
						else {
							}
						},3000);				// end setTimeout(...)
					});				// end function(marker, point)
			gmarkers[id] = marker;							// marker to array for side_bar click function
			infoTabs[id] = tabs;							// tabs to array
			if (!(map_is_fixed)){
				bounds.extend(point);
				}
			return marker;
			}				// end function create Marker()
			
		function createdummyMarker(point, tabs, id) {					// Creates dummymarker and sets up click event infowindow for "no maps" added tickets and units. 7/28/10 
			points = true;
			var icon = new GIcon(baseIcon);
			var icon_url = "./our_icons/question1.png";				// 7/28/10
			icon.image = icon_url;

			var dummymarker = new GMarker(point, icon);

			GEvent.addListener(dummymarker, "click", function() {

				map.closeInfoWindow();
				which = id;
				gmarkers[which].hide();
				dummymarker.openInfoWindowTabsHtml(infoTabs[id]);

				setTimeout(function() {
					if ($("detailmap")) {
						var dMapDiv = $("detailmap");
						var detailmap = new GMap2(dMapDiv);
						detailmap.addControl(new GSmallMapControl());
						detailmap.setCenter(point, 17);
						detailmap.addOverlay(dummymarker);
						}
					else {
	//					alert($("detailmap"));
						}
					},3000);				// end setTimeout(...)

				});
			gmarkers[id] = dummymarker;							// marker to array for side_bar click function
			infoTabs[id] = tabs;							// tabs to array
			if (!(map_is_fixed)){
				bounds.extend(point);
				}
			return dummymarker;
			}				// end function create dummyMarker()			
	
		var the_grid;
		var grid = false;
		function doGrid() {
			if (grid) {
				map.removeOverlay(the_grid);
				}
			else {
				the_grid = new LatLonGraticule();
				map.addOverlay(the_grid);
				}
			grid = !grid;
			}			// end function doGrid
	
	    var trafficInfo = new GTrafficOverlay();
	    var toggleState = true;
	
		function doTraffic() {				// 10/16/08
			if (toggleState) {
		        map.removeOverlay(trafficInfo);
		     	}
			else {
		        map.addOverlay(trafficInfo);
		    	}
	        toggleState = !toggleState;			// swap
		    }				// end function doTraffic()
	
	
		var icons=[];						// note globals
		icons[0] = 											 4;	// units white
		icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>+1] = 1;	// blue
		icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>+1] = 2;	// yellow
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  3;	// red
		icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+2] =  0;	// black
	
		var map;
		var center;
		var zoom;
		var points = false;
<?php
	
	$dzf = get_variable('def_zoom_fixed');
	print "\tvar map_is_fixed = ";
	print (($dzf==1) || ($dzf==3))? "true;\n":"false;\n";
	
	$kml_olays = array();
	$dir = "./kml_files";
	$dh  = opendir($dir);
	$i = 1;
	$temp = explode ("/", $_SERVER['REQUEST_URI']);
	$temp[count($temp)-1] = "kml_files";				//
	$server_str = "http://" . $_SERVER['SERVER_NAME'] .":" .  $_SERVER['SERVER_PORT'] .  implode("/", $temp) . "/";
	while (false !== ($filename = readdir($dh))) {
		if (!is_dir($filename)) {
		    echo "\tvar kml_" . $i . " = new GGeoXml(\"" . $server_str . $filename . "\");\n";
		    $kml_olays[] = "map.addOverlay(kml_". $i . ");";
		    $i++;
		    }
		}
	//	dump ($kml_olays);
?>
	
	function do_mail_win() {			// 6/13/09
		if(starting) {return;}					
		starting=true;	
	
		newwindow_um=window.open('do_unit_mail.php', 'E_mail_Window',  'titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
	
		if (isNull(newwindow_um)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_um.focus();
		starting = false;
		}
	
	function open_tick_window (id) {				// 4/12/10
		var url = "single.php?ticket_id="+ id;
		var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		tickWindow.focus();
		}
	
	function do_add_note (id) {				// 8/12/09
		var url = "add_note.php?ticket_id="+ id;
		var noteWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100');
		noteWindow.focus();
		}
		
	function do_track(callsign) {		
		if (parent.frames["upper"].logged_in()) {
	//		if(starting) {return;}					// 6/6/08
	//		starting=true;
			map.closeInfoWindow();
			var width = <?php print get_variable('map_width');?>+360;
			var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
			var url = "track_u.php?source="+callsign;
	
			newwindow=window.open(url, callsign,  spec);
			if (isNull(newwindow)) {
				alert ("Track display requires popups to be enabled. Please adjust your browser options.");
				return;
				}
	//		starting = false;
			newwindow.focus();
			}
		}				// end function do track()
	
	//function do_popup(id) {					// added 7/9/09
	//	if (parent.frames["upper"].logged_in()) {
	//		map.closeInfoWindow();
	//		var width = <?php print get_variable('map_width');?>+32;
	//		var spec ="titlebar, resizable=1, scrollbars, height=590,width=" + width + ",status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
	//		var url = "incident_popup.php?id="+id;
	//
	//		newwindow=window.open(url, id, spec);
	//		if (isNull(newwindow)) {
	//			alert ("Popup Incident display requires popups to be enabled. Please adjust your browser options.");
	//			return;
	//			}
	////		starting = false;
	//		newwindow.focus();
	//		}
	//	}				// end function do popup()
	
		var ticket_ids = [];
		var gmarkers = [];
		var fmarkers = [];
		var infoTabs = [];
		var facinfoTabs = [];
		var which;
		var i = 0;			// sidebar/icon index
	
		map = new GMap2($("map"));		// create the map
<?php
	$maptype = get_variable('maptype');	// 08/02/09
	
		switch($maptype) { 
			case "1":
			break;
	
			case "2":?>
			map.setMapType(G_SATELLITE_MAP);<?php
			break;
		
			case "3":?>
			map.setMapType(G_PHYSICAL_MAP);<?php
			break;
		
			case "4":?>
			map.setMapType(G_HYBRID_MAP);<?php
			break;
	
			default:
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}
?>

//		map.addControl(new GSmallMapControl());					// 8/25/08
		map.setUIToDefault();									// 8/13/10

		map.addControl(new GMapTypeControl());
	
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
	
		mapBounds=new GLatLngBounds(map.getBounds().getSouthWest(), map.getBounds().getNorthEast());		// 4/4/09
	
		var bounds = new GLatLngBounds();						// create  bounding box
	<?php if (get_variable('terrain') == 1) { ?>
		map.addMapType(G_PHYSICAL_MAP);
	<?php } ?>
	
		map.enableScrollWheelZoom();
	
		do_landb();				// 7/3/11 - show lines		
	
		var baseIcon = new GIcon();
		baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png
	
		baseIcon.iconSize = new GSize(20, 34);
		baseIcon.shadowSize = new GSize(37, 34);
		baseIcon.iconAnchor = new GPoint(9, 34);
		baseIcon.infoWindowAnchor = new GPoint(9, 2);
		baseIcon.infoShadowAnchor = new GPoint(18, 25);
		GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
			map.setCenter(center,zoom);
			map.addOverlay(gmarkers[which])
			});
	
<?php
		$order_by =  (!empty ($get_sortby))? $get_sortby: $_SESSION['sortorder']; // use default sort order?
																					//fix limits according to setting "ticket_per_page"
		$limit = "";
		if ($_SESSION['ticket_per_page'] && (check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]ticket`") > $_SESSION['ticket_per_page']))	{
			if ($_GET['offset']) {
				$limit = "LIMIT $_GET[offset],$_SESSION[ticket_per_page]";
				}
			else {
				$limit = "LIMIT 0,$_SESSION[ticket_per_page]";
				}
			}
		$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
		$time_back = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($cwi*3600));
		
		if(!isset($_POST['frm_group'])) {
		$x=0;	
		$where2 = "AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		} else {
		$x=0;	
		$where2 = "AND (";	
		foreach($_POST['frm_group'] as $grp) {
			$where3 = (count($_POST['frm_group']) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		}

		switch($func) {				//9/29/09 Added capability for Special Incidents 10/27/09 changed to bring scheduled incidents to front when due.
				case 0: 
					$where = "WHERE (`status`='{$GLOBALS['STATUS_OPEN']}' OR (`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `booked_date` <= (NOW() + INTERVAL 2 DAY)) OR 
					(`status`='{$GLOBALS['STATUS_CLOSED']}'  AND `problemend` >= '{$time_back}')){$where2}";	//	11/29/10, 6/10/11, 6/10/11

					break;
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
					$the_start = get_start($func);		// mysql timestamp format 
					$the_end = get_end($func);
					$where = " WHERE (`status`='{$GLOBALS['STATUS_CLOSED']}' AND `problemend` BETWEEN '{$the_start}' AND '{$the_end}') {$where2} ";		//	6/10/11, 6/10/11
					break;	
				case 10:
					$where = "WHERE (`status`='{$GLOBALS['STATUS_SCHEDULED']}' AND `booked_date` >= (NOW() + INTERVAL 2 DAY)) {$where2}";	//	11/29/10, 6/10/11, 6/10/11
					break;				
				default: print "error - error - error - error " . __LINE__;
//				default: $where = "WHERE `status`='{$GLOBALS['STATUS_OPEN']}' OR (`status`='3'  AND `booked_date` <= (NOW() - INTERVAL 6 HOUR))"; break;
				}				// end switch($func) 
	

		$query = "SELECT *, UNIX_TIMESTAMP(problemstart) AS `problemstart`, 
			UNIX_TIMESTAMP(problemend) AS `problemend`,
			UNIX_TIMESTAMP(booked_date) AS booked_date,UNIX_TIMESTAMP(date) AS `date`,
			UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.updated) AS `updated`, 
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `ticket_id`, 
			`$GLOBALS[mysql_prefix]ticket`.`severity` AS `severity`, 			
			`$GLOBALS[mysql_prefix]in_types`.type AS `type`,
			`$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id`,
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
			`$GLOBALS[mysql_prefix]ticket`.street AS `tick_street`,
			`$GLOBALS[mysql_prefix]ticket`.city AS `tick_city`,
			`$GLOBALS[mysql_prefix]ticket`.state AS `tick_state`,			
			`$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
			`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`,
			`$GLOBALS[mysql_prefix]facilities`.lat AS `fac_lat`,
			`$GLOBALS[mysql_prefix]facilities`.lng AS `fac_lng`, 
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name` 
			FROM `$GLOBALS[mysql_prefix]ticket`
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`				
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id` 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON `$GLOBALS[mysql_prefix]ticket`.rec_facility=`$GLOBALS[mysql_prefix]facilities`.`id` 
			$where $restrict_ticket 
			 GROUP BY ticket_id ORDER BY `status` DESC, `severity` DESC, `$GLOBALS[mysql_prefix]ticket`.`id` ASC";		// 2/2/09, 10/28/09


		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$curr_incs =  mysql_num_rows($result);		
		$sb_indx = 0;				// note zero base!			
								// major while ... starts here
?>
//	3/29/11 Incident List sidebar header
			var sidebar_line = "<DIV style='width: 100%;'>";
			sidebar_line += "<DIV CLASS= 'in_space odd'><DIV CLASS = 'incs'>&nbsp;</DIV></DIV>";	
			sidebar_line += "<DIV CLASS= 'in_space odd'><DIV CLASS = 'incs'>&nbsp;</DIV></DIV>";			
			sidebar_line += "<DIV CLASS= 'in_1 odd'><DIV CLASS = 'incs'><B>Incident</B></DIV></DIV>";
			sidebar_line += "<DIV CLASS= 'in_type odd'><DIV CLASS = 'incs'><B>Type</B></DIV></DIV>";			
			sidebar_line += "<DIV CLASS= 'in_1 odd'><DIV CLASS = 'incs'><B>Addr</B></DIV></DIV>";
			sidebar_line += "<DIV CLASS= 'in_date odd'><DIV CLASS = 'incs'><B>Start</B></DIV></DIV>";
			sidebar_line += "<DIV CLASS= 'in_dur odd'><DIV CLASS = 'incs'><B>Duration</B></DIV></DIV></DIV><BR />";			
								
<?php

		$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors

		if($curr_incs > 0) {
		$z=0;		
		while ($row = stripslashes_deep(mysql_fetch_array($result))) {
			$by_severity[$row['severity']] ++;

			$z==0 ? $background_col = "even" : $background_col = "odd";

			print "\t\t ticket_ids.push({$row['ticket_id']});\n";
		
			switch($row['status']) {				//10/27/09 to Add star to scheduled incidents on current situation screen
				case 1: $sp = ""; break;
				case 2: $sp = ""; break;
				case 3: $sp = "*"; break;
				default: $sp = ""; break;
				}
		
				print "\t\tvar scheduled = '$sp';\n";
?>
		//		var sym = i.toString();						// for sidebar and icon
				var sym = scheduled + (<?php print $sb_indx; ?>+1).toString();					// for sidebar and icon
		
<?php
				$the_id = $row[0];
		
				if ($row['tick_descr'] == '') $row['tick_descr'] = '[no description]';	// 8/12/09
				if (get_variable('abbreviate_description'))	{	//do abbreviations on description, affected if neccesary
					if (strlen($row['tick_descr']) > get_variable('abbreviate_description')) {
						$row['tick_descr'] = substr($row['tick_descr'],0,get_variable('abbreviate_description')).'...';
						}
					}
				if (get_variable('abbreviate_affected')) {
					if (strlen($row['affected']) > get_variable('abbreviate_affected')) {
						$row['affected'] = substr($row['affected'],0,get_variable('abbreviate_affected')).'...';
						}
					}
				switch($row['severity'])		{		//color tickets by severity
				 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
					case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
					default: 				$severityclass='severity_normal'; break;
					}
		
				$street = empty($row['tick_street'])? "" : $row['tick_street'] . "<BR/>" . $row['tick_city'] . " " . $row['state'] ;
				$todisp = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id=" . $the_id . "'><U>Dispatch</U></A>";	// 8/2/08
				$now = now(); 					

				if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
					$strike = "<strike>"; $strikend = "</strike>";
					}
				else { $strike = $strikend = "";}	

				$index_no = $sb_indx +1;

				?>
				//	3/29/11 Incident List sidebar				
				sidebar_line += "<DIV CLASS='in_space <?php print $background_col;?> <?php print $severityclass;?>'><DIV class='incs' onClick = 'myclick(<?php print $sb_indx;?>);' onmouseout=\"UnTip()\" onmouseover=\"Tip('* means the incident is a scheduled one')\"><?php print $strike;?>&nbsp;<?php print $index_no;?><?php print $strikend;?></DIV></DIV>";
				sidebar_line += "<DIV CLASS='in_space <?php print $background_col;?> <?php print $severityclass;?>'><DIV class='incs' onClick = 'myclick(<?php print $sb_indx;?>);' onmouseout=\"UnTip()\" onmouseover=\"Tip('* means the incident is a scheduled one')\"><?php print $strike;?>&nbsp;<?php print $sp;?><?php print $strikend;?></DIV></DIV>";
				sidebar_line += "<DIV CLASS='in_1 <?php print $background_col;?> <?php print $severityclass;?>'><DIV class='incs' onClick = 'myclick(<?php print $sb_indx;?>);' onmouseout=\"UnTip()\" onmouseover=\"Tip('<?php print $row['scope'];?>')\"><?php print $strike;?>&nbsp;<?php print shorten($row['scope'],$shorten_length);?><?php print $strikend;?></DIV></DIV>";
				sidebar_line += "<DIV CLASS='in_type <?php print $background_col;?> <?php print $severityclass;?>'><DIV class='incs' onClick = 'myclick(<?php print $sb_indx;?>);' onmouseout=\"UnTip()\" onmouseover=\"Tip('<?php print $row['type'];?>')\"><?php print $strike;?>&nbsp;<?php print shorten($row['type'], $shorten_length);?><?php print $strikend;?></DIV></DIV>";
				sidebar_line += "<DIV CLASS='in_1 <?php print $background_col;?> <?php print $severityclass;?>'><DIV class='incs' onClick = 'myclick(<?php print $sb_indx;?>);' onmouseout=\"UnTip()\" onmouseover=\"Tip('<?php print $row['tick_street']  . " " . $row['tick_city'] . " " . $row['tick_state'];?>')\"><?php print $strike;?>&nbsp;<?php print shorten(($row['tick_street'] . ' ' . $row['tick_city'] . " " . $row['tick_state']), $shorten_length);?>&nbsp;<?php print $strikend;?></DIV></DIV>";
				sidebar_line += "<DIV CLASS='in_date <?php print $background_col;?> <?php print $severityclass;?>'><DIV class='incs' onClick = 'myclick(<?php print $sb_indx;?>);' onmouseout=\"UnTip()\" onmouseover=\"Tip('<?php print format_date($row['problemstart']);?>')\"><?php print $strike;?>&nbsp;<?php print shorten(format_date($row['problemstart']), $shorten_length);?><?php print $strikend;?></DIV></DIV>";
				sidebar_line += "<DIV CLASS='in_dur <?php print $background_col;?> <?php print $severityclass;?>'><DIV class='incs' onClick = 'myclick(<?php print $sb_indx;?>);' onmouseout=\"UnTip()\" onmouseover=\"Tip('<?php print my_date_diff($row['problemstart'], $now);?>')\"><?php print $strike;?>&nbsp;<?php print shorten(my_date_diff($row['problemstart'], $now), $shorten_length);?><?php print $strikend;?></DIV></DIV></BR>";

<?php
				$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
		
				$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . shorten($row['scope'], 48)  . "$strikend</B></TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
				if (good_date($row['booked_date'])) {	//4/13/10
					$tab_1 .= "<TR CLASS='odd'><TD>Booked Date:</TD><TD>" . format_date($row['booked_date']) . "</TD></TR>";
					}			
				$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>" . $row['tick_street'] . ' ' . $row['tick_city'] . "</TD></TR>";
				$end_date = (intval($row['problemend'])> 1)? $row['problemend']:  (time() - (get_variable('delta_mins')*60));				
				$elapsed = my_date_diff($row['problemstart'], $end_date);		// 5/13/10
				$tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Status:</TD><TD ALIGN='left'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;($elapsed)</TD></TR>";	// 3/27/10
				if (!(empty($row['fac_name']))) {		
					$tab_1 .= "<TR CLASS='even'><TD>Receiving Facility:</TD><TD>" . shorten($row['fac_name'], 30)  . "</TD></TR>";	//10/28/09
					}
		
				$utm = get_variable('UTM');
				if ($utm==1) {
					$coords =  $row['lat'] . "," . $row['lng'];																	// 8/12/09
					$tab_1 .= "<TR CLASS='even'><TD>UTM grid:</TD><TD>" . toUTM($coords) . "</TD></TR>";
					}
				$tab_1 .= 	"</TABLE>";			// 11/6/08
		
				$tab_2 = "<TABLE CLASS='infowin'  width='" . $_SESSION['scr_width']/4 . "'>";	// 8/12/09
				$tab_2 .= "<TR CLASS='even'>	<TD ALIGN='left'>Description:</TD><TD ALIGN='left'>" . replace_quotes(shorten(str_replace($eols, " ", $row['tick_descr']), 48)) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
				$tab_2 .= "<TR CLASS='odd'>		<TD ALIGN='left'>" . get_text('Disposition') . ":</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['comments'], 48)) . "</TD></TR>";		// 8/13/09, 3/15/11
				$tab_2 .= "<TR CLASS='even'>	<TD ALIGN='left'>911 contact:</TD><TD ALIGN='left'>" . replace_quotes(shorten($row['nine_one_one'], 48)) . "</TD></TR>";	// 6/26/10
		
				$locale = get_variable('locale');	// 08/03/09
				switch($locale) { 
					case "0":
					$tab_2 .= "<TR CLASS='even'>	<TD>USNG:</TD><TD>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "1":
					$tab_2 .= "<TR CLASS='even'>	<TD>OSGB:</TD><TD>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					case "2":
					$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
					$tab_2 .= "<TR CLASS='even'>	<TD>UTM:</TD><TD>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
					break;
				
					default:
					print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
					}
		
		//		$tab_2 .= "<TR>					<TD>&nbsp;</TD></TR>";
				$tab_2 .= "<TR>					<TD COLSPAN=2>" . show_assigns(0, $the_id) . "</TD></TR>";
				$tab_2 .= 	"</TABLE>";		// 11/6/08
				$query = "SELECT * FROM $GLOBALS[mysql_prefix]action WHERE `ticket_id` = " . $the_id;
				$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
				$A = mysql_affected_rows();
		
				$query= "SELECT * FROM $GLOBALS[mysql_prefix]patient WHERE `ticket_id` = " . $the_id;
				$resultav = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
				$P = mysql_affected_rows ();
		?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['scope'], 12));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $tab_2);?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
		
				var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// for each ticket
				if (!(map_is_fixed)){																// 4/3/09
					bounds.extend(point);
					}
				var category = "Incident";

				var marker = createMarker(point, myinfoTabs,<?php print $row['severity']+1;?>, 0, i, sym, category);	// (point,tabs, color, id, sym) - 1/6/09
				var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";
		
				map.addOverlay(marker);
					i++;				// step the index				
<?php
				if (intval($row['radius']) > 0) {
					$color= (substr($row['color'], 0, 1)=="#")? $row['color']: "#000000";		// black default
?>	
		//		drawCircle(				38.479874, 				-78.246704, 						50.0, 					"#000080",						 1, 		0.75,	 "#0000FF", 					.2);
				drawCircle(	<?php print $row['lat']?>, <?php print $row['lng']?>, <?php print $row['radius']?>, "<?php print $color?>", 1, 0.75, "<?php print $color?>", .<?php print $row['opacity']?>);
<?php
					}			// end if (intval($row['radius']) 
				$sb_indx++;
				$z==0 ? $z=1 : $z=0;
				}				// end tickets while ($row = ...)
			} else {
?>
				sidebar_line += "<DIV CLASS='even' style='width: 100%;'><DIV style='text-align: center; color: #FF0000; font-size: 12px; font-weight: bold;'>No Current Incidents for selected time period</DIV></DIV><BR />";
<?php
			}
			$sev_string = "Severities: normal ({$by_severity[$GLOBALS['SEVERITY_NORMAL']]}), Medium ({$by_severity[$GLOBALS['SEVERITY_MEDIUM']]}), High ({$by_severity[$GLOBALS['SEVERITY_HIGH']]})";
?>
			$('sev_counts').innerHTML = "<?php print $sev_string; ?>";
			$('incidents').innerHTML = sidebar_line;	//	incident list to sidebar


var sidebar_line = "";
<?php
	// ========================================== 3/29/11 ASSIGNMENTS start    ================================================

	if(isset($_SESSION['viewed_groups'])) {	//	6/10/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}

	if(!isset($curr_viewed)) {	
		$x=0;	//	6/10/11
		$where2 = "AND (";	//	6/10/11
		foreach($al_groups as $grp) {	//	6/10/11
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
	} else {
		$x=0;	//	6/10/11
		$where2 = "AND (";	//	6/10/11
		foreach($curr_viewed as $grp) {	//	6/10/11
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
	}
	$where2 .= "AND `a`.`type` = 2";	
	
	$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of,
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
		`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
		`u`.`user` AS `theuser`, `t`.`scope` AS `tick_scope`,
		`t`.`description` AS `tick_descr`,
		UNIX_TIMESTAMP(`t`.`problemstart`) AS `tick_pstart`,
		`t`.`problemstart` AS `problemstart`,		
		`t`.`status` AS `tick_status`,
		`t`.`street` AS `tick_street`,
		`t`.`city` AS `tick_city`,
		`t`.`state` AS `tick_state`,			
		`r`.`id` AS `unit_id`,
		`r`.`name` AS `unit_name` ,
		`r`.`type` AS `unit_type` ,
		`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`,
		`$GLOBALS[mysql_prefix]assigns`.`clear` AS `clear`		
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder`	`r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )		
			WHERE (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00') {$where2}   
		GROUP BY `unit_id` ORDER BY `severity` DESC, `tick_pstart` ASC";		
//			dump($query);
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$curr_calls =  mysql_num_rows($result);

		$guest = is_guest();
		$user = is_user();
		$doUnit = (($guest)||($user))? "viewU" : "editU";		// 5/11/10
		$doTick = ($guest)? "viewT" : "editT";				// 06/26/08
		$now = time() - (get_variable('delta_mins')*60);
		$items = mysql_affected_rows();
		$tags_arr = explode("/", get_variable('disp_stat'));		// 8/29/10 

		$TBL_INC_PERC = 50;		// incident group - four columns  -  50 percent as default
		$TBL_UNIT_PERC = 35;	// unit group, 
		$COLS_INCID = 18;		// incident name -  18 characters as default
		$COLS_OPENED = 16;		// date/time opened -  0 characters as default
		$COLS_DESCR = 32;		// incident description -  32 characters as default
		$COLS_ADDR = 32;		// address -  32 characters as default
		
		$COLS_UNIT = 15;			// unit name
		
		$COLS_ASOF = 9;			// call as-of date/time -  9 characters as default
		
		$priorities = array("","severity_medium","severity_high" );
		
		if($curr_calls > 0) {
		
?>
//	3/29/11 Assignments List sidebar header
			sidebar_line += "<DIV style='font-size: 12px; color: #000000; background: #FFFFFF;'>";
			sidebar_line += "<DIV CLASS= 'c1 odd'><DIV class='incs'><B>Incident</B></DIV></DIV>";
			sidebar_line += "<DIV CLASS= 'cdate odd'><DIV class='incs'><B>Inc Start</B></DIV></DIV>";			
			sidebar_line += "<DIV CLASS= 'c1 odd'><DIV class='incs'><B>Synopsis</B></DIV></DIV>";
			sidebar_line += "<DIV CLASS= 'c1 odd'><DIV class='incs'><B>Addr</B></DIV></DIV>";
			sidebar_line += "<DIV CLASS= 'cspace odd'><DIV class='incs'>&nbsp;</DIV></DIV>";			
			sidebar_line += "<DIV CLASS= 'unit_n odd'><DIV class='incs'><B>Unit</B></DIV></DIV>";
			sidebar_line += "<DIV CLASS= 'unit_d odd'><DIV class='incs'><B>&nbsp</B></DIV></DIV>";
			sidebar_line += "<DIV CLASS= 'unit_s odd'><DIV class='incs'><B>Unit St</B></DIV></DIV>";			
			sidebar_line += "</DIV><BR />";
<?php		
			$w=0;		
			$unit_ids = array();
			$i = 1;	
			$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
			
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//	While for Assignments
			
//	============================= Regions stuff
				$query_un = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 2 AND `resource_id` = '$row[unit_id]' ORDER BY `id` ASC;";	// 5/4/11
				$result_un = mysql_query($query_un);	// 5/4/11
				$un_groups = array();
				while ($row_un = stripslashes_deep(mysql_fetch_assoc($result_un))) 	{	// 5/4/11
					$un_groups[] = $row_un['group'];
					}
	
//				dump($row);

				$inviewed = 0;	//	5/4/11
				foreach($un_groups as $un_val) {
					if(in_array($un_val, $al_groups)) {
						$inviewed++;
						}
					}
					
//	============================= end of Regions stuff				

				$w==0 ? $bg_color_class = "even" : $bg_color_class = "odd";

				$in_strike = 	((!(empty($row['scope']))) && ($row['tick_status']== $GLOBALS['STATUS_CLOSED']))? "<STRIKE>": "";					// 11/7/08
				$in_strikend = 	((!(empty($row['scope']))) && ($row['tick_status']== $GLOBALS['STATUS_CLOSED']))? "</STRIKE>": "";
				if ($inviewed > 0) {	//	Tests to see whether assigned unit is in one of the users groups 5/4/11	
					if (!(empty($row['scope']))) {	

						$the_name = addslashes ($row['tick_scope']);															// 9/12/09
						$the_short_name = shorten($row['tick_scope'], $shorten_length);

						$the_descr = (empty($row['tick_descr'])) ? "&nbsp;" : addslashes(str_replace($eols, " ", $row['tick_descr']));
						$the_short_one = (empty($row['tick_descr'])) ? "&nbsp; " : shorten(addslashes(str_replace($eols, " ", $row['tick_descr'])), $shorten_length);
							
						$address = (empty($row['tick_street']))? "&nbsp;" : $row['tick_street'] . ", ";		// 8/10/10
						$address = addslashes($address . $row['tick_city']. "&nbsp;". $row['tick_state']);
						$short_addr = shorten($address, $shorten_length);
						
?>
//	3/29/11 Assignments List sidebar					
						sidebar_line += "<DIV CLASS='c1 <?php print $bg_color_class;?>'><DIV class='incs' onmouseover=\"Tip('[#<?php print $row['ticket_id'];?>] <?php print $the_name;?>')\" onmouseout=\"UnTip()\"><?php print $in_strike;?><?php print $the_short_name;?><?php print $in_strikend;?></DIV></DIV>";
						sidebar_line += "<DIV CLASS='cdate <?php print $bg_color_class;?>'><DIV class='incs' onmouseover=\"Tip('Opened: <?php print format_date($row['tick_pstart']);?>')\" onmouseout=\"UnTip()\"><?php print substr($row['problemstart'], 0, $shorten_length);?></DIV></DIV>";
						sidebar_line += "<DIV CLASS='c1 <?php print $bg_color_class;?>'><DIV class='incs' onmouseover=\"Tip('<?php print $the_descr;?>')\" onmouseout=\"UnTip()\"><?php print $in_strike;?><?php print $the_short_one;?><?php print $in_strikend;?></DIV></DIV>";
						sidebar_line += "<DIV CLASS='c1 <?php print $bg_color_class;?>'><DIV class='incs' onmouseover=\"Tip('<?php print $address;?>')\" ALIGN='left' onmouseout=\"UnTip()\"><?php print $in_strike;?><?php print $short_addr;?><?php print $in_strikend;?></DIV></DIV>";
<?php
								} else {
?>							
						sidebar_line += "<DIV CLASS='c0 <?php print $bg_color_class;?>'><DIV class='incs'>135[#<?php print $row['ticket_id'];?>]</DIV></DIV>";
<?php					
						}
?>
						sidebar_line += "<DIV CLASS='cspace <?php print $bg_color_class;?>'><DIV class='incs'>&nbsp;</DIV></DIV>";	
<?php						
//  UNITS			3 col's	- 9/12/09
		
						if (is_date($row['clear'])) {							// 6/26/08
							$strike = "<STRIKE>"; $strikend = "</STRIKE>";		// strikethrough on closed assigns
							}
						else {
							$strike = $strikend = "";
							}			 
						if (!($row['unit_id'] == 0)) {																	// 5/11/09
							$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`	WHERE `id`= '{$row['unit_type']}' LIMIT 1";
							$result_type = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
							$row_type = (mysql_affected_rows() > 0) ? stripslashes_deep(mysql_fetch_assoc($result_type)) : "";
							$the_bg_color = empty($row_type)?	"transparent" : $GLOBALS['UNIT_TYPES_BG'][$row_type['icon']];		// 3/15/10
							$the_text_color = empty($row_type)? "black" :		$GLOBALS['UNIT_TYPES_TEXT'][$row_type['icon']];		// 
							unset ($row_type);

							$unit_name = empty($row['unit_id']) ? "[#{$row['unit_id']}]" : addslashes($row['unit_name']) ;			// id only if absent
							$short_name = shorten($unit_name, 10);
	?>							
							sidebar_line += "<DIV CLASS='unit_n <?php print $bg_color_class;?>' STYLE='background-color:<?php print $the_bg_color;?>;  opacity: .7; color:<?php print $the_text_color;?>;'><DIV class='incs' onmouseover=\"Tip('#<?php print $row['unit_id'];?> <?php print $unit_name;?>')\" onmouseout=\"UnTip()\"><B><?php print $short_name;?></B></DIV></DIV>";								
	<?php
							$the_disp_str =  fs_get_disp_status ($row);		// 3/25/11
	?>						
							sidebar_line += "<DIV CLASS='unit_d disp_stat'><DIV class='incs'<b>&nbsp;<?php print $the_disp_str;?>&nbsp;</b></DIV></DIV>";
	<?php
								$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated`,
									`t`.`id` AS `type_id`, 
									`r`.`id` AS `unit_id`, 
									`r`.`name` AS `name`,
									`s`.`description` AS `stat_descr`,  
									`r`.`name` AS `unit_name`
									FROM `$GLOBALS[mysql_prefix]responder` `r` 
									LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
									LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 
									WHERE `r`.`id` = '{$row['unit_id']}' LIMIT 1";

								$result_unit = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
								$row_unit = stripslashes_deep(mysql_fetch_assoc($result_unit));
?>
								sidebar_line += "<DIV CLASS='unit_s' <?php print $bg_color_class;?>'><DIV class='incs' onmouseover=\"Tip('<?php print substr($row_unit['stat_descr'], 0, 12);?>')\" onmouseout=\"UnTip()\">&nbsp;<?php print substr($row_unit['stat_descr'], 0, 12);?></DIV></DIV>";
								sidebar_line += "</DIV><BR />";	
<?php
								}
							}
						$i++;
				$w==0 ? $w=1 : $w=0;
				}
		} else {						

?>
			sidebar_line += "<DIV CLASS='even' style='width: 100%;'><DIV style='text-align: center; color: #FF0000; font-size: 12px; font-weight: bold;'>No Current Unit Assignments</DIV></DIV><BR />";
<?php
		}
?>
		$('assignments').innerHTML = sidebar_line;	//	assignment list to DIV		
			
	// ==========================================      RESPONDER start    ================================================
			points = false;
			i++;
			var j=0;
<?php
		$u_types = array();												// 1/1/09
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
			}
		//dump($u_types);
		unset($result);
		
		$categories = array();													// 12/03/10
		$categories = get_category_butts();											// 12/03/10		
	
		$assigns = array();					// 08/8/3
		$tickets = array();					// ticket id's
	
		$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";
	
		$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
			$assigns[$row_as['responder_id']] = $row_as['ticket'];
			$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
			}
		unset($result_as);
	
		$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	
		$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
		$status_vals = array();											// build array of $status_vals
		$status_vals[''] = $status_vals['0']="TBD";
	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
		$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
		while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
			$temp = $row_st['id'];
			$status_vals[$temp] = $row_st['status_val'];
			$status_hide[$temp] = $row_st['hide'];
	
			}
		unset($result_st);
		
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	// 6/10/11
		$result = mysql_query($query);	// 6/10/11
		$al_groups = array();
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 6/10/11
			$al_groups[] = $row['group'];
			}	
		if(!isset($_POST['frm_group'])) {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
		} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($_POST['frm_group'] as $grp) {	//	6/10/11
				$where3 = (count($_POST['frm_group']) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
		}
		$where2 .= "AND `a`.`type` = 2";
		
//-----------------------UNIT RING FENCE STUFF--------------------6/10/11
?>
	var thepoint;
	var points = new Array();
	var boundary = new Array();	
	var bound_names = new Array();	
		
<?php	
	$query_bn = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `l`.`id` = `r`.`ring_fence`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )	
				{$where2} AND `use_with_u_rf`=1 GROUP BY `l`.`id`";
	$result_bn = mysql_query($query_bn)or do_error($query_bn, mysql_error(), basename(__FILE__), __LINE__);
	while($row_bn = stripslashes_deep(mysql_fetch_assoc($result_bn))) {
		extract ($row_bn);
		$bn_name = $row_bn['line_name'];
		$all_boundaries[] = $row_bn['ring_fence'];		
		$points = explode (";", $line_data);
		for ($i = 0; $i < count($points); $i++) {
			$coords = explode (",", $points[$i]);
?>
			thepoint = new GLatLng(parseFloat(<?php print $coords[0];?>), parseFloat(<?php print $coords[1];?>));
			points.push(thepoint);
<?php
			}			// end for ($i = 0 ... )
		if (intval($filled) == 1) {		//	6/10/11
?>
			var polyline = new GPolygon(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>, add_hash("<?php print $fill_color;?>"), <?php print $fill_opacity;?>, {clickable:false, id:"ringfence"});
			boundary.push(polyline);
			bound_names.push("<?php print $bn_name;?>"); 
<?php	
			} else {
?>
			var polyline = new GPolyline(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>, 0, 0, {clickable:false, id:"ringfence"});
			boundary.push(polyline);
			bound_names.push("<?php print $bn_name;?>"); 
<?php		
			}
?>
			map.addOverlay(polyline);
<?php
		}	//	End while
//-------------------------END OF UNIT RING FENCE STUFF-------------------------		

//-----------------------UNIT EXCLUSION ZONE STUFF--------------------6/10/11
?>
	var thepoint;
	var points = new Array();
		
<?php	
	$query_bn = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `l`.`id` = `r`.`excl_zone`)
				LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )	
				{$where2} AND `use_with_u_ex`=1 GROUP BY `l`.`id`";
	$result_bn = mysql_query($query_bn)or do_error($query_bn, mysql_error(), basename(__FILE__), __LINE__);
	while($row_bn = stripslashes_deep(mysql_fetch_assoc($result_bn))) {
		extract ($row_bn);
		$bn_name = $row_bn['line_name'];
		$all_boundaries[] = $row_bn['ring_fence'];		
		$points = explode (";", $line_data);
		for ($i = 0; $i < count($points); $i++) {
			$coords = explode (",", $points[$i]);
?>
			thepoint = new GLatLng(parseFloat(<?php print $coords[0];?>), parseFloat(<?php print $coords[1];?>));
			points.push(thepoint);
<?php
			}			// end for ($i = 0 ... )
		if (intval($filled) == 1) {		//	6/10/11
?>
			var polyline = new GPolygon(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>, add_hash("<?php print $fill_color;?>"), <?php print $fill_opacity;?>, {clickable:false, id:"ringfence"});
			boundary.push(polyline);
			bound_names.push("<?php print $bn_name;?>"); 
<?php	
			} else {
?>
			var polyline = new GPolyline(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>, 0, 0, {clickable:false, id:"ringfence"});
			boundary.push(polyline);
			bound_names.push("<?php print $bn_name;?>"); 
<?php		
			}
?>
			map.addOverlay(polyline);
<?php
		}	//	End while
//-------------------------END OF UNIT EXCLUSION ZONE STUFF-------------------------			
	
	//	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle`";	//
	//	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name`";	//
		
	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated`, `r`.`name` AS `unit_name`, `r`.`id` AS `unit_id`, `t`.`name` AS `type_name`, `r`.`type` AS `type`
		FROM `$GLOBALS[mysql_prefix]responder` `r` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = a.resource_id )			
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 
		{$where2} 
		GROUP BY unit_id ";	//	4/11/11, 5/4/11
		
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		
		$units_ct = mysql_affected_rows();			// 1/4/10	
	
		$aprs = FALSE;
		$instam = FALSE;
		$locatea = FALSE;		//7/23/09
		$gtrack = FALSE;		//7/23/09
		$glat = FALSE;		//7/23/09
		$i=0;				// counter
	// =============================================================================
		$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
		$utc = gmdate ("U");				// 3/25/09
	
		while ($row = stripslashes_deep(mysql_fetch_array($result))) {		// ==========  major while() for RESPONDER ==========
			$got_point = FALSE;
		$latitude = $row['lat'];		// 7/18/10		
		$longitude = $row['lng'];		// 7/18/10	
		$name = $row['unit_name'];			//	10/8/09
		$temp = explode("/", $name );
		$index = $row['icon_str'];	// 4/27/11		
		print "\t\tvar sym = '$index';\n";				// for sidebar and icon 10/8/09
		
													// 2/13/09
			$todisp = (is_guest())? "": "&nbsp;&nbsp;<A HREF='units.php?func=responder&view=true&disp=true&id=" . $row['id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;";	// 08/8/02
			$toedit = (is_guest())? "" :"&nbsp;&nbsp;<A HREF='units.php?func=responder&edit=true&id=" . $row['id'] . "'><U>Edit</U></A>&nbsp;&nbsp;" ;	// 10/8/08
			$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U>&nbsp;&nbsp;</SPAN>" ;
			$tofac = (is_guest())? "": "<A HREF='units.php?func=responder&view=true&dispfac=true&id=" . $row['id'] . "'><U>To Facility</U></A>&nbsp;&nbsp;";	// 08/8/02
	
			$temp = $row['un_status_id'] ;		// 2/24/09
			$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
			$hide_status = (array_key_exists($temp, $status_hide))? $status_hide[$temp] : "??";				// 10/21/09
			if ($hide_status == "y") {
				$hide_unit = 1;
				} else {
				$hide_unit = 0;
				}
	
			$temp = $row['un_status_id'] ;		// 2/24/09
			$the_status = (array_key_exists($temp, $status_vals))? $status_vals[$temp] : "??";				// 2/2/09
	
			if ($row['aprs']==1) {				// get most recent aprs position data
				$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
					WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_aprs = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$aprs_updated = $row_aprs['updated'];
				$aprs_speed = $row_aprs['speed'];
	//			if (($row_aprs) && (settype($row_aprs['latitude'], "float"))) {
				if (($row_aprs) && (my_is_float($row_aprs['latitude']))) {
					echo "\t\tvar point = new GLatLng(" . $row_aprs['latitude'] . ", " . $row_aprs['longitude'] ."); // 677\n";
					$got_point = TRUE;
	
					}
				unset($result_tr);
				}
			else { $row_aprs = FALSE; }
	//		dump($row_aprs);
	
			if ($row['instam']==1) {			// get most recent instamapper data
				$temp = explode ("/", $row['callsign']);			// callsign/account no. 3/22/09
	
				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
					WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest
	
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_instam = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$instam_updated = $row_instam['updated'];
				$instam_speed = $row_instam['speed'];
				if (($row_instam) && (my_is_float($row_instam['latitude']))) {											// 4/29/09
					echo "\t\tvar point = new GLatLng(" . $row_instam['latitude'] . ", " . $row_instam['longitude'] ."); // 724\n";
					$got_point = TRUE;
					}
				unset($result_tr);
				}
			else { $row_instam = FALSE; }
	
			if ($row['locatea']==1) {			// get most recent locatea data		// 7/23/09
				$temp = explode ("/", $row['callsign']);			// callsign/account no.
	
				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
					WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest
	
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_locatea = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$locatea_updated = $row_locatea['updated'];
				$locatea_speed = $row_locatea['speed'];
				if (($row_locatea) && (my_is_float($row_locatea['latitude']))) {
					echo "\t\tvar point = new GLatLng(" . $row_locatea['latitude'] . ", " . $row_locatea['longitude'] ."); // 687\n";
					$got_point = TRUE;
					}
				unset($result_tr);
				}
			else { $row_locatea = FALSE; }
	
			if ($row['gtrack']==1) {			// get most recent gtrack data		// 7/23/09
				$temp = explode ("/", $row['callsign']);			// callsign/account no.
	
				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
					WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest
	
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_gtrack = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$gtrack_updated = $row_gtrack['updated'];
				$gtrack_speed = $row_gtrack['speed'];
				if (($row_gtrack) && (my_is_float($row_gtrack['latitude']))) {
					echo "\t\tvar point = new GLatLng(" . $row_gtrack['latitude'] . ", " . $row_gtrack['longitude'] ."); // 687\n";
					$got_point = TRUE;
					}
				unset($result_tr);
				}
			else { $row_gtrack = FALSE; }
	
			if ($row['glat']==1) {			// get most recent latitude data		// 7/23/09
				$temp = explode ("/", $row['callsign']);			// callsign/account no.
	
				$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
					WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest
	
				$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row_glat = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
				$glat_updated = $row_glat['updated'];
				if (($row_glat) && (my_is_float($row_glat['latitude']))) {
					echo "\t\tvar point = new GLatLng(" . $row_glat['latitude'] . ", " . $row_glat['longitude'] ."); // 687\n";
					$got_point = TRUE;
					}
				unset($result_tr);
				}
			else { $row_glat = FALSE; }
	
			if (!($got_point) && ((my_is_float($row['lat'])))) {
				echo "\t\tvar point = new GLatLng(" . $row['lat'] . ", " . $row['lng'] .");	// 753\n";
				$got_point= TRUE;
				}
	
	//		print __LINE__ . "<BR />";
			$the_bull = "";											// define the bullet
			$update_error = strtotime('now - 6 hours');								// set the time for silent setting
	//		echo $update_error;
			if ($row['aprs']==1) {
				if ($row_aprs) {
					$spd = 2;										// default
					if($aprs_speed == 0) {$spd = 1;}			// stopped
					if($aprs_speed >= 50) {$spd = 3;}		// fast
					}
				else {
					$spd = 0;				// no data
					}
				$the_bull = "<FONT COLOR=" . $bulls[$spd] ."><B>AP</B></FONT>";
				}			// end aprs
	
			if ($row['instam']==1) {
				if ($instam_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>IN</B></FONT>";}
				if ($instam_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>IN</B></FONT>";}
				if ($instam_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>IN</B></FONT>";}
				if ($instam_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>IN</B></FONT>";}
				}
	
			if ($row['locatea']==1) {
				if ($locatea_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>LO</B></FONT>";}		// 7/23/09
				if ($locatea_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>LO</B></FONT>";}
				if ($locatea_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>LO</B></FONT>";}
				if ($locatea_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>LO</B></FONT>";}
				}
	
			if ($row['gtrack']==1) {
				if ($gtrack_speed>50) {$the_bull = "<FONT COLOR = 'white'><B>GT</B></FONT>";}		// 7/23/09
				if ($gtrack_speed<50) {$the_bull = "<FONT COLOR = 'green'><B>GT</B></FONT>";}
				if ($gtrack_speed==0) {$the_bull = "<FONT COLOR = 'red'><B>GT</B></FONT>";}
				if ($gtrack_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>GT</B></FONT>";}
				}
			if ($row['glat']==1) {
	
				$the_bull = "<FONT COLOR = 'green'><B>GL</B></FONT>";		// 7/23/09
				if ($glat_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'><B>GL</B></FONT>";}
				}
							// end bullet stuff
	// name
	
			$name = $row['name'];		//	10/8/09
			$temp = explode("/", $name );
			$display_name = $temp[0];
	
	// assignments 3/16/09
	
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
				WHERE `responder_id` = '{$row['id']}' AND `clear` IS NULL ";
	//		dump($query);
	
			$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_assign = (mysql_affected_rows()==0)?  FALSE : stripslashes_deep(mysql_fetch_assoc($result_as)) ;
			unset($result_as);
	
			switch($row_assign['severity'])		{		//color tickets by severity
			 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
				case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
				default: 				$severityclass='severity_normal'; break;
				}
	
			$tick_ct = (mysql_affected_rows()>1)? "(" .mysql_affected_rows() . ") ": "";
			$ass_td =  (mysql_affected_rows()>0)? "<TD COLSPAN=2 CLASS='$severityclass' TITLE = '" .$row_assign['scope'] . "' >" .$tick_ct . shorten($row_assign['scope'], 24) . "</TD>": "<TD>na</TD>";
	
	// status, mobility
	// as of
			$strike = $strike_end = "";
			if ((($row['instam']==1) && $row_instam ) || (($row['aprs']==1) && $row_aprs ) || (($row['locatea']==1) && $row_locatea ) || (($row['gtrack']==1) && $row_gtrack ) || (($row['glat']==1) && $row_glat )) {		// either remote source?
				$the_class = "emph";
				if ($row['aprs']==1) {															// 3/24/09
					$the_time = $aprs_updated;
					$instam = TRUE;				// show footer legend
					}
				if ($row['instam']==1) {															// 3/24/09
					$the_time = $instam_updated;
					$instam = TRUE;				// show footer legend
					}
				if ($row['locatea']==1) {															// 7/23/09
					$the_time = $locatea_updated;
					$locatea = TRUE;				// show footer legend
					}
				if ($row['gtrack']==1) {															// 7/23/09
					$the_time = $gtrack_updated;
					$gtrack = TRUE;				// show footer legend
					}
				if ($row['glat']==1) {																// 7/23/09
					$the_time = $glat_updated;
					$glat = TRUE;				// show footer legend
					}
			} else {
				$the_time = $row['updated'];
				$the_class = "td_data";
			}
	
			if (abs($utc - $the_time) > $GLOBALS['TOLERANCE']) {								// attempt to identify  non-current values
				$strike = "<STRIKE>";
				$strike_end = "</STRIKE>";
			} else {
			$strike = $strike_end = "";
			}
	
	//	    snap(basename( __FILE__) . __LINE__, $the_class );
	
	
	// tab 1
	
	//		if (((settype($row['lat'], "float"))) || ($row_aprs) || ($row_instam)) {						// position data?
			if (((my_is_float($row['lat']))) || ($row_aprs) || ($row_instam) || ($row_locatea) || ($row_gtrack) || ($row_glat)) {						// 5/4/09
	//			dump(__LINE__);
	
				$temptype = $u_types[$row['type']];
				$the_type = $temptype[0];																	// 1/1/09
	
				$tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['unit_name'], 48) . "</B> - " . $the_type . "</TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['description']), 32) . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD>" . $the_status . " </TD></TR>";
				$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
				if (array_key_exists($row['id'], $assigns)) {
					$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['id']] . "'>" . shorten($assigns[$row['id']], 20) . "</A></TD></TR>";
					}
				$tab_1 .= "<TR CLASS='odd'><TD COLSPAN = 2>&nbsp;</TD></TR>";
				$tab_1 .= "<TR CLASS='even'><TD COLSPAN = 2 ALIGN = 'center' onClick = 'do_mail_win();'><B><U>Email units</U></B></TD></TR>";
				$tab_1 .= "</TABLE>";
	
	// tab 2
			$tabs_done=FALSE;
			if ($row_aprs) {		// three tabs if APRS data
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_aprs['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_aprs['course'] . ", Speed:  " . $row_aprs['speed'] . ", Alt: " . $row_aprs['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $row_aprs['closest_city'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $row_aprs['status'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_aprs['packet_date']) . " $strike_end (UTC)</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
	
?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['unit_name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("APRS <?php print addslashes(substr($row_aprs['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php
				}	// end if ($row_aprs)
	
			if ($row_instam) {		// three tabs if instam data
	//			dump(__LINE__);
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_instam['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_instam['course'] . ", Speed:  " . $row_instam['speed'] . ", Alt: " . $row_instam['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_instam['updated']) . " $strike_end</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['unit_name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Instam <?php print addslashes(substr($row_instam['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
					];
<?php
				}	// end if ($row_instam)
	
			if ($row_locatea) {		// three tabs if locatea data		7/23/09
	//			dump(__LINE__);
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_locatea['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_locatea['course'] . ", Speed:  " . $row_locatea['speed'] . ", Alt: " . $row_locatea['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_locatea['updated']) . " $strike_end</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
	?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['unit_name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("LocateA <?php print addslashes(substr($row_locatea['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
					];
<?php
				}	// end if ($row_gtrack)
	
			if ($row_gtrack) {		// three tabs if gtrack data		7/23/09
	//			dump(__LINE__);
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_gtrack['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_gtrack['course'] . ", Speed:  " . $row_gtrack['speed'] . ", Alt: " . $row_gtrack['altitude'] . "</TD></TR>";
				$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_gtrack['updated']) . " $strike_end</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
	?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['unit_name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Gtrack <?php print addslashes(substr($row_gtrack['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
					];
<?php
				}	// end if ($row_gtrack)
	
			if ($row_glat) {		// three tabs if glat data			7/23/09
	//			dump(__LINE__);
				$tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$tab_2 .="<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . $row_glat['source'] . "</B></TD></TR>";
				$tab_2 .= "<TR CLASS='odd'><TD>As of: </TD><TD> $strike " . format_date($row_glat['updated']) . " $strike_end</TD></TR></TABLE>";
				$tabs_done=TRUE;
	//			print __LINE__;
?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['unit_name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("G Lat <?php print addslashes(substr($row_glat['source'], -3)); ?>", "<?php print $tab_2;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
					];
<?php
				}	// end if ($row_gtrack)
	
			if (!($tabs_done)) {	// else two tabs
?>
				var myinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(shorten($row['unit_name'], 10));?>", "<?php print $tab_1;?>"),
					new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
					];
<?php
				}		// end if(!($tabs_done))
	
			$temp = $u_types[$row['type']];		// array ($row['name'], $row['icon'])
	//		dump($temp);
			$the_color = ($row['mobile']=="1")? 0 : 4;		// icon color black, white		-- 4/18/09
			$the_group = get_category($row['unit_id']);			
			if (($latitude == "0.999999") && ($longitude == "0.999999")) {	// check for no maps added points 7/28/10
				$dummylat = get_variable('def_lat');
				$dummylng = get_variable('def_lng');			
				echo "\t\tvar point = new GLatLng(" . $dummylat . ", " . $dummylng ."); // 677\n";
?>
				var dummymarker = createdummyMarker(point, myinfoTabs, <?php print $sb_indx; ?>);	// Plots dummy icon in default position for units added in no maps operation
				map.addOverlay(dummymarker);
<?php
			} else {
?>
				var the_group = '<?php print $the_group;?>';
				
				var marker = createMarker(point, myinfoTabs, <?php print $the_color;?>, <?php print $hide_unit;?>,  <?php print $sb_indx; ?>, sym, the_group); // 7/28/10
				map.addOverlay(marker);
<?php
			} // end check for no maps added points
		}		// end position data available
	
		else {					// (sidebar, line_no, rcd_id, letter)
	//		dump(__LINE__);
				}
	
		$i++;				// zero-based
		print "\t\ti++;\n"; 	// 3/20/09
		$sb_indx++;				// zero-based	
		}				// end  ==========  while() for RESPONDER ==========
	
		$source_legend = (($aprs)||($instam)||($gtrack)||($locatea)||($glat))? "<TD CLASS='emph' ALIGN='center'>Source time</TD>": "<TD></TD>";		// if any remote data/time 3/24/09

?>
		side_bar_html= "<form action='#'>";		//	12/03/10
<?php
	if($units_ct > 0) {	//	3/15/11
		foreach($categories as $key => $value) {		//	12/03/10
?>
			side_bar_html += "<DIV class='cat_button' onClick='set_chkbox(\"<?php print $value;?>\")'><?php print $value;?>: <input type=checkbox id='<?php print $value;?>' onClick='set_chkbox(\"<?php print $value;?>\")'/>&nbsp;&nbsp;&nbsp;</DIV>";			<!-- 12/03/10 -->
<?php
			}
			$all="ALL";		//	12/03/10
			$none="NONE";				//	12/03/10
?>
			side_bar_html += "<DIV ID = 'ALL_BUTTON' class='cat_button' onClick='set_chkbox(\"<?php print $all;?>\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='<?php print $all;?>' onClick='set_chkbox(\"<?php print $all;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
			side_bar_html += "<DIV ID = 'NONE_BUTTON' class='cat_button'  onClick='set_chkbox(\"<?php print $none;?>\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='<?php print $none;?>' onClick='set_chkbox(\"<?php print $none;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
			side_bar_html += "<DIV ID = 'go_can' style='float:right; padding:2px;'><SPAN ID = 'go_button' onClick='do_go_button()' class='conf_next_button' STYLE = 'display:none;'><U>Next</U></SPAN>";
			side_bar_html += "<SPAN ID = 'can_button'  onClick='cancel_buttons()' class='conf_can_button' STYLE = 'display:none;'><U>Cancel</U></SPAN></DIV>";
			side_bar_html+="</form></TD></TR></TABLE>";			<!-- 12/03/10 -->
<?php
	} else {
		foreach($categories as $key => $value) {		//	12/03/10
?>
			side_bar_html += "<DIV class='cat_button' STYLE='display: none;' onClick='set_chkbox(\"<?php print $value;?>\")'><?php print $value;?>: <input type=checkbox id='<?php print $value;?>' onClick='set_chkbox(\"<?php print $value;?>\")'/>&nbsp;&nbsp;&nbsp;</DIV>";			<!-- 12/03/10 -->
<?php
			}
			$all="ALL";		//	12/03/10
			$none="NONE";				//	12/03/10
?>
			side_bar_html += "<DIV class='cat_button' style='color: red;'>None Defined ! </DIV>";			<!-- 12/03/10 -->
			side_bar_html += "<DIV ID = 'ALL_BUTTON' class='cat_button' STYLE='display: none;' onClick='set_chkbox(\"<?php print $all;?>\")'><input type=checkbox id='ALL' style='display: none'></DIV>";			<!-- 12/03/10 -->
			side_bar_html += "<DIV ID = 'NONE_BUTTON' class='cat_button' STYLE='display: none;' onClick='set_chkbox(\"<?php print $none;?>\")'><input type=checkbox id='NONE' style='display: none'></DIV>";			<!-- 12/03/10 -->
			side_bar_html +="</form></TD></TR></TABLE></DIV>";			<!-- 12/03/10 -->
	
			<?php
	}
?>
		$("boxes").innerHTML = side_bar_html;										// 12/03/10 side_bar_html to responders div			

	// ====================================Add Facilities to Map 8/1/09================================================

<?php
		$fac_categories = array();													// 12/03/10
		$fac_categories = get_fac_category_butts();											// 12/03/10
?>	
		var icons=[];	
		var g=0;
	
		var fmarkers = [];
	
		var baseIcon = new GIcon();
		baseIcon.shadow = "./markers/sm_shadow.png";
	
		baseIcon.iconSize = new GSize(30, 30);
		baseIcon.iconAnchor = new GPoint(15, 30);
		baseIcon.infoWindowAnchor = new GPoint(9, 2);
	
		var fac_icon = new GIcon(baseIcon);
		fac_icon.image = icons[1];
	
	function createfacMarker(fac_point, fac_name, id, fac_icon, type) {
		var fac_marker = new GMarker(fac_point, fac_icon);
		// Show this markers index in the info window when it is clicked
		var fac_html = fac_name;
		fac_marker.category = type;		
		fmarkers[id] = fac_marker;
		GEvent.addListener(fac_marker, "click", function() {fac_marker.openInfoWindowHtml(fac_html);});
		return fac_marker;
	}
	
<?php
	
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	5/4/11
		$result = mysql_query($query);	//	5/4/11
		$al_groups = array();
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	//	5/4/11
			$al_groups[] = $row['group'];
			}	
		
		if(!isset($_POST['frm_group'])) {	//	5/4/11
			$x=0;	//	5/4/11
			$where2 = "WHERE (";	//	5/4/11
			foreach($al_groups as $grp) {	//	5/4/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
		} else {
			$x=0;	//	5/4/11
			$where2 = "WHERE (";	//	5/4/11
			foreach($_POST['frm_group'] as $grp) {	//	5/4/11
				$where3 = (count($_POST['frm_group']) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
		}
		$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	5/4/11
		
		$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.id AS fac_id,
		`$GLOBALS[mysql_prefix]facilities`.description AS facility_description, 
		`$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, 
		`$GLOBALS[mysql_prefix]facilities`.name AS facility_name 
		FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.resource_id )			
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id 
		{$where2} 
		GROUP BY fac_id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
		$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	//	dump($query_fac);
		
		while($row_fac = mysql_fetch_array($result_fac)){
		$fac_id=($row_fac['fac_id']);
		$fac_type=($row_fac['icon']);
		$fac_type_name = ($row_fac['fac_type_name']);		
	
		$fac_name = $row_fac['facility_name'];			//	10/8/09
	//	$fac_name = $row_fac['name'];					//	10/8/09
		$fac_temp = explode("/", $fac_name );			//  11/27/09
		$fac_index = $row_fac['icon_str'];		
		
		print "\t\tvar fac_sym = '$fac_index';\n";				// for sidebar and icon 10/8/09
		
			$toroute = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id=" . $fac_id . "'><U>Dispatch</U></A>";	// 8/2/08
	
		if(is_guest()) {
			$facedit = $toroute = $facmail = "";
			}
		else {
			$facedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='facilities.php?func=responder&edit=true&id=" . $row_fac['fac_id'] . "'><U>Edit</U></A>" ;
			$facmail = "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_mail_fac_win('" .$row_fac['fac_id']  . "');><U><B>Email</B></U></SPAN>" ;
			$toroute = "&nbsp;<A HREF='fac_routes.php?fac_id=" . $fac_id . "'><U>Route To Facility</U></A>";	// 8/2/08
			}
	
			if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {
	
			$f_disp_name = $row_fac['facility_name'];		//	10/8/09
			$f_disp_temp = explode("/", $f_disp_name );
			$facility_display_name = $f_disp_temp[0];
	
				$fac_tab_1 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
				$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
				$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
				$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
				$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
				$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
				$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'> " . format_date($row_fac['updated']) . "</TD></TR>";
				$fac_tab_1 .= "</TABLE>";
	
				$fac_tab_2 = "<TABLE CLASS='infowin' width='" . $_SESSION['scr_width']/4 . "'>";
				$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
				$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
				$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
				$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
				$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
				$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
				$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
				$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
				$fac_tab_2 .= "</TABLE>";
				
				?>
	//			var fac_sym = (g + 1).toString();
				var myfacinfoTabs = [
					new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
					new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
					];
				<?php


			if(($row_fac['lat']==0.999999) && ($row_fac['lng']==0.999999)) {	// check for facilities entered in no maps mode 7/28/10
	
				echo "var fac_icon = new GIcon(baseIcon);\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_type_name = \"$fac_type_name\";\n";
				echo "var fac_icon_url = \"./our_icons/question1.png\";\n";
				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new GLatLng(" . get_variable('def_lat') . "," . get_variable('def_lng') . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon, fac_type_name);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
			} else {
				echo "var fac_icon = new GIcon(baseIcon);\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_type_name = \"$fac_type_name\";\n";				
				echo "var fac_icon_url = \"./our_icons/gen_fac_icon.php?blank=$fac_type&text=\" + (fac_sym) + \"\";\n";
				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new GLatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon, fac_type_name);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
				}

?>
				if (fac_marker.isHidden()) {
					fac_marker.show();
				} else {
					fac_marker.hide();
				}
<?php
			}	// end if my_is_float
	
?>
			g++;
	<?php
		}	// end while
	
	//}
	// =====================================End of functions to show facilities========================================================================
	
		for ($i = 0; $i<count($kml_olays); $i++) {				// emit kml overlay calls
			echo "\t\t" . $kml_olays[$i] . "\n";
			}
?>
	function add_hash(in_str) { // prepend # if absent
		return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
		}

	function do_landb() {				// JS function - 8/1/11
		var points = new Array();
<?php
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `line_status` = 0 AND (`use_with_bm` = 1 OR `use_with_r` = 1)";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);

		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$empty = FALSE;
			extract ($row);
			$name = $row['line_name'];
			switch ($row['line_type']) {
				case "p":		// poly
					$points = explode (";", $line_data);
					echo "\n\tvar points = new Array();\n";
		
					for ($i = 0; $i<count($points); $i++) {
						$coords = explode (",", $points[$i]);
?>
						var thepoint = new GLatLng(<?php print $coords[0];?>, <?php print $coords[1];?>);
						bounds.extend(thepoint);
						points.push(thepoint);
		
<?php					}			// end for ($i = 0 ... )
			 	if ((intval($filled) == 1) && (count($points) > 2)) {?>
						var polyline = new GPolygon(points,add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>,add_hash("<?php print $fill_color;?>"), <?php print $fill_opacity;?>);
<?php			} else {?>
				        var polyline = new GPolyline(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>);
<?php			} ?>				        
						map.addOverlay(polyline);
<?php				
					break;
			
				case "c":		// circle
					$temp = explode (";", $line_data);
					$radius = $temp[1];
					$coords = explode (",", $temp[0]);
					$lat = $coords[0];
					$lng = $coords[1];
					$fill_opacity = (intval($filled) == 0)?  0 : $fill_opacity;
					echo "\n drawCircle({$lat}, {$lng}, {$radius}, add_hash('{$line_color}'), {$line_width}, {$line_opacity}, add_hash('{$fill_color}'), {$fill_opacity}); // 513\n";
					break;
			
				case "t":		// text banner

					$temp = explode (";", $line_data);
					$banner = $temp[1];
					$coords = explode (",", $temp[0]);
					echo "\n var point = new GLatLng(parseFloat({$coords[0]}) , parseFloat({$coords[1]}));\n";
					$the_banner = htmlentities($banner, ENT_QUOTES);
					$the_width = intval( trim($line_width), 10);		// font size
					echo "\n drawBanner( point, '{$the_banner}', '{$the_banner}', {$the_width});\n";
					break;
			
				}	// end switch
				
		}			// end while ()
		
		unset($query, $result);
?>
		}		// end function do_landb()
/*
	try {
		do_landb();				// 7/3/11 - show lines
		}
	catch (e) {	}
*/

		if (!(map_is_fixed)){
			if (!points) {		// any?
				map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
				}
			else {
				center = bounds.getCenter();
				var max_zoom = <?php echo get_variable('def_zoom');?>;				// 6/22/12
				zoom = (map.getBoundsZoomLevel(bounds) > max_zoom )? max_zoom : map.getBoundsZoomLevel(bounds);

				map.setCenter(center,zoom);
				}			// end if/else (!points)
		}				// end if (!(map_is_fixed))
	
		side_bar_html = "";
	<?php
	if(!empty($fac_categories)) {
	?>		
		side_bar_html= "<form action='#'>";		//	12/03/10

	<?php
		function get_fac_icon($fac_cat){			// returns legend string
			$icons = $GLOBALS['fac_icons'];
			$sm_fac_icons = $GLOBALS['sm_fac_icons'];
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` WHERE `name` = \"$fac_cat\"";		// types in use
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$print = "";
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$fac_icon = $row['icon'];
				$print .= "<IMG SRC = './our_icons/" . $sm_fac_icons[$fac_icon] . "' STYLE = 'vertical-align: middle'>";
				}
			unset($result);
			return $print;
		}

		foreach($fac_categories as $key => $value) {		//	12/03/10
		$curr_icon = get_fac_icon($value);
	?>
				side_bar_html += "<DIV class='cat_button_fs' onClick='set_fac_chkbox(\"<?php print $value;?>\")'><?php print get_fac_icon($value);?>&nbsp;&nbsp;<?php print $value;?>: <input type=checkbox id='<?php print $value;?>'  onClick='set_fac_chkbox(\"<?php print $value;?>\")'/></DIV>";			<!-- 12/03/10 -->
	<?php
			}



		$all="fac_ALL";		//	12/03/10
		$none="fac_NONE";				//	12/03/10
	?>
		side_bar_html += "<DIV ID = 'fac_ALL_BUTTON'  class='cat_button_fs' onClick='set_fac_chkbox(\"<?php print $all;?>\")'><FONT COLOR = 'red'>ALL</FONT><input type=checkbox id='<?php print $all;?>' onClick='set_fac_chkbox(\"<?php print $all;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
		side_bar_html += "<DIV ID = 'fac_NONE_BUTTON'  class='cat_button_fs' onClick='set_fac_chkbox(\"<?php print $none;?>\")'><FONT COLOR = 'red'>NONE</FONT><input type=checkbox id='<?php print $none;?>' onClick='set_fac_chkbox(\"<?php print $none;?>\")'/></FONT></DIV>";			<!-- 12/03/10 -->
		side_bar_html += "<DIV ID = 'fac_go_can' style='float:middle; padding:2px;'><SPAN ID = 'fac_go_button' onClick='do_go_facilities_button()' class='conf_next_button' STYLE = 'display:none;'><U>Next</U></SPAN>";
		side_bar_html += "<SPAN ID = 'fac_can_button'  onClick='fac_cancel_buttons()' class='conf_can_button' STYLE = 'display:none;'><U>Cancel</U></SPAN></DIV>";
		side_bar_html+="</form>";			<!-- 12/03/10 -->
		$("fac_boxes").innerHTML = side_bar_html;										// 12/03/10 side_bar_html to responders div			

	<?php
	} else {
	?>	
		side_bar_html= "";		//	12/03/10
		side_bar_html += "<DIV class='cat_button' style='color: red;'>None Defined ! </DIV>";			<!-- 12/03/10 -->
	<?php
		$all="fac_ALL";		//	12/03/10
		$none="fac_NONE";				//	12/03/10
	?>
		side_bar_html += "<DIV ID = 'fac_ALL_BUTTON' class='cat_button' style='display: none;'><input type=checkbox id='<?php print $all;?>'/><input type=checkbox id='fac_ALL' style='display: none'></DIV>";			<!-- 12/03/10 -->
		side_bar_html += "<DIV ID = 'fac_NONE_BUTTON' class='cat_button' style='display: none;'><input type=checkbox id='<?php print $none;?>'/><input type=checkbox id='fac_NONE' style='display: none'></DIV>";			<!-- 12/03/10 -->
		side_bar_html += "<DIV ID = 'fac_go_can' style='float:right; padding:2px; display: none'><SPAN ID = 'fac_go_button' class='conf_next_button' STYLE = 'display:none;'><U>Next</U></SPAN>";
		side_bar_html += "<SPAN ID = 'fac_can_button' class='conf_can_button' STYLE = 'display:none;'><U>Cancel</U></SPAN></DIV>";
		side_bar_html+="</form>";			<!-- 12/03/10 -->
		$("fac_boxes").innerHTML = side_bar_html;										// 12/03/10 side_bar_html to responders div			
<?php
	}
?>	
	// =============================================================================================================
		}		// end if (GBrowserIsCompatible())
	else {
		alert("Sorry, browser compatibility problem. Contact your tech support group.");
		}
	</SCRIPT>
	
	
<?php
	
	}				// end function full_scr() ===========================================================

