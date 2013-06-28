<?php

error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
session_start();						// 

require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
/*
10/14/08 moved js includes here fm function_major
1/11/09  handle callboard frame
1/19/09 dollar function added
1/21/09 added show butts - re button menu
1/24/09 auto-refresh iff situation display and setting value
1/28/09 poll time added to top frame
3/16/09 added updates and auto-refresh if any mobile units
3/18/09 'aprs_poll' to 'auto_poll'
4/10/09 frames check for call board
7/16/09	protocol handling added
11/11/09 'top' and 'bottom' anchors added - 
12/26/09 handle 'log_in' $_GET variable
1/3/10 wz tooltips added for usage in FMP
1/8/10 added do_init logic - called ONLY from index.php
1/23/10 refresh meta removed
3/27/10 $zoom_tight added
4/10/10 hide 'board' button if setting = 0
4/11/10 do_blink added, poll_id dropped
6/24/10 compression added
7/18/10 redundant $() removed
7/20/10 cb frame resize/refresh added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/13/10 links incl relocated
8/25/10 hide top buttons if ..., $_POST logout test
8/29/10 dispatch status style added
11/29/10 added to_listtype form when adding scheduled list type select dropdown
3/15/11	Added reference to stylesheet.php for revisable day night colors
3/19/11 added top term button value
4/22/11 gunload correction
5/16/11 Added code to support Ticker Module
6/10/11	added groups and boundaries
6/28/11 auto refresh added
7/3/11 lazy logout button moved out of try/catch
3/5/12 handle empty GMaps API key
3/23/12 auto-refresh changes
4/12/12 Revised regions control buttons
6/1/12 Revised loading of main page modules so tha they only load on the main screen, not the Ticket Detail screen.
6/14/12 Moved position of ck_frames() in onLoad string.
10/23/12 Added code for Messaging
3/26/2013 revised per RC Charlie
5/26/2013 made auto_refresh conditional on setting value
*/

if (isset($_GET['logout'])) {
	do_logout();
	exit();
	}
else {		// 
	do_login(basename(__FILE__));
	$do_mu_init = (array_key_exists('log_in', $_GET))? "parent.frames['upper'].mu_init();" : "";	// start multi-user function
	}

if ($istest) {
	print "GET<BR/>\n";
	if (!empty($_GET)) {
		dump ($_GET);
		}
	print "POST<BR/>\n";
	if (!empty($_POST)) {
		dump ($_POST);
		}
	print "SESSION<BR/>\n";	//	3/15/11
	if (!empty($_SESSION)) {
		dump ($_SESSION);
		}
	}
														// set auto-refresh if any mobile units														
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	</STYLE>
	<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT><!-- 10/23/12-->
<?php 
@session_start();	
if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
	require_once('./incs/modules.inc.php');
	}	
if ($_SESSION['internet']) {				// 8/22/10
	$api_key = trim(get_variable('gmaps_api_key'));
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
?>
	<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $key_str;?>&libraries=geometry,weather&sensor=false"></SCRIPT>
	<SCRIPT  TYPE="text/javascript"SRC="./js/epoly.js"></SCRIPT>
	<!--
	<SCRIPT  TYPE="text/javascript"SRC="./js/epoly_v3.js"></SCRIPT>	
	-->
	<SCRIPT TYPE="text/javascript" src="./js/elabel_v3.js"></SCRIPT> 	<!-- 8/1/11 -->
	<SCRIPT TYPE="text/javascript" SRC="./js/gmaps_v3_init.js"></script>	<!-- 1/29/2013 -->
<?php } ?>
<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
<SCRIPT TYPE="text/javascript" SRC="./js/messaging.js"></SCRIPT>	<!-- 10/23/12 -->
<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>

<SCRIPT>
var map;				// make globally visible
var sortby = '`date`';	//	10/23/12
var sort = "DESC";	//	10/23/12
var columns = "<?php print get_msg_variable('columns');?>";	//	10/23/12
var the_columns = new Array(<?php print get_msg_variable('columns');?>);	//	10/23/12
var thescreen = 'ticket';	//	10/23/12
var thelevel = '<?php print $the_level;?>';
<?php
if ( get_variable('call_board') == 2) {		// 7/20/10
	$cb_per_line = 22;						// adjust as needed
	$cb_fixed_part = 60;
	$cb_min = 96;
	$cb_max = 300;
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";	// 6/15/09
	$result = @mysql_query($query);
	$lines = mysql_affected_rows();
	unset($result);
	$height = (($lines*$cb_per_line ) + $cb_fixed_part);
	$height = ($height<$cb_min)? $cb_min: $height;		// vs min
	$height = ($height>$cb_max)? $cb_max: $height;		// vs max
?>
frame_rows = parent.document.getElementById('the_frames').getAttribute('rows');	// get current configuration
var rows = frame_rows.split(",", 4);
rows[1] = <?php print $height ;?>;						// new cb frame height, re-use top
frame_rows = rows.join(",");
parent.document.getElementById('the_frames').setAttribute('rows', frame_rows);
parent.calls.location.href = 'board.php';							// 7/21/10



<?php
	}		// end if ( get_variable('call_board') == 2) 
	
if (!($_SESSION['internet'])) {				// 8/25/10 
?>
	parent.frames["upper"].$("full").style.display  = "none";		// hide 'full screen' button
<?php
	}
if (is_guest()) {													// 8/25/10
?>	
	parent.frames["upper"].$("add").style.display  = 				"none";			// guests disallowed
	try { parent.frames["upper"].$("ics").style.display  =			"none";}	
	catch(e) { }
	try { parent.frames["upper"].$("has_button").style.display  = 	"none";}
	catch(e) { }	
<?php
	}		// end guest - needs other levels!
?>

	var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php
	var check_initialized = false;
	var check_interval = null;

	function change_status_sel(the_control, the_val) {
		var oldval = false;
		var newval = the_val;
		var existing = false;
		var thelength = false;
		if(document.getElementById(the_control)) {
			thelength = document.getElementById(the_control).options.length;
			existing = document.getElementById(the_control).selectedIndex;
			if(document.getElementById(the_control).options[existing].value) {		
				oldval = document.getElementById(the_control).options[existing].value;
				}
			for(var f = 0; f < thelength; f++) {
				if((document.getElementById(the_control).options[f].value == newval) && (f != existing)) {
					document.getElementById(the_control).options[f].selected = true;
					parent.frames["upper"].show_msg ('Responder Status Changed');
					}
				}
			}
		}
	
	function logged_in() {								// returns boolean
		var temp = parent.frames["upper"].$("whom").innerHTML==NOT_STR;
		return !temp;
		}

	function set_regions_control() {
		var reg_control = "<?php print get_variable('regions_control');?>";
		var regions_showing = "<?php print get_num_groups();?>";
		if(regions_showing) {
			if (reg_control == 0) {
				$('top_reg_box').style.display = 'none';
				$('regions_outer').style.display = 'block';
				} else {
				$('top_reg_box').style.display = 'block';
				$('regions_outer').style.display = 'none';			
				}
			}
		}
		
	function fence_get() {								// set cycle
		if (check_interval!=null) {return;}			// ????
		check_interval = window.setInterval('check_fence_loop()', 60000);		// 4/7/10 
		}			// end function mu get()

	function fence_init() {								// get initial values from server -  4/7/10
		if (check_initialized) { return; }
		check_initialized = true;
			ring_fence();
			exclude();				
			fence_get();				// start loop
		}				// end function mu_init()		
		
	function check_fence_loop() {								// monitor for changes - 4/10/10, 6/10/11	
			ring_fence();
			exclude();			
		}			// end function do_loop()			
	
	function blink_text(id, bgcol, bgcol2, maincol, seccol) {	//	6/10/11
		if(!document.getElementById(id)) {
			alert("A unit in your group is\noutside a ring fence\nhowever you aren't currently\nviewing the group it is allocated to");
		} else {	
			function BlinkIt () {
				if(document.getElementById (id)) {
					var blink = document.getElementById (id);
					var flag = id + "_flag";	
					color = (color == maincol) ? seccol : maincol;
					back = (back == bgcol) ? bgcol2 : bgcol;
					blink.style.background = back;
					blink.style.color = color;
					document.getElementById(id).title = "Outside Ringfence";
					if($(flag)) {	
						$(flag).innerHTML = "RF";
						}							
					}
				}
			window.setInterval (BlinkIt, 1000);
			var color = maincol;
			var back = bgcol;				
			}
		}
		
	function unblink_text(id) {	//	6/10/11
		if(!document.getElementById(id)) {
		} else {	
		if(document.getElementById (id)) {
			var unblink = document.getElementById (id);
			unblink.style.background = "";
			unblink.style.color = "";			
				}
			}
		}

	function blink_text2(id, bgcol, bgcol2, maincol, seccol) {	//	6/10/11
		if(!document.getElementById(id)) {
			alert("A unit in your group is\ninside an exclusion zone\nhowever you aren't currently\nviewing the group it is allocated to");
		} else {	
			function BlinkIt () {
				if(document.getElementById (id)) {
					var blink = document.getElementById (id);
					var flag = id + "_flag";
					color = (color == maincol) ? seccol : maincol;
					back = (back == bgcol) ? bgcol2 : bgcol;
					blink.style.background = back;
					blink.style.color = color;
					document.getElementById(id).title = "Inside Exclusion Zone";
					if($(flag)) {	
						$(flag).innerHTML = "EZ";
						}				
					}
				}
			window.setInterval (BlinkIt, 1000);
			var color = maincol;
			var back = bgcol;				
			}
		}			
		
	function unblink_text2(id) {	//	6/10/11
		if(!document.getElementById(id)) {
		} else {	
		if(document.getElementById (id)) {
			var unblink = document.getElementById (id);
			unblink.style.background = "";
			unblink.style.color = "";			
				}
			}
		}	
<?php
	if (array_key_exists('log_in', $_GET)) {			// 12/26/09- array_key_exists('internet', $_SESSION)
?>
		parent.frames["upper"].$("gout").style.display  = "inline";								// logout button - 7/3/11
		parent.frames["upper"].mu_init ();					// start polling
		if (parent.frames.length == 3) {										// 1/20/09, 4/10/09
			parent.calls.location.href = 'board.php';							// 1/11/09
			}
<?php
		}
		$temp = get_unit();															// 3/19/11
		$term_str = ($temp )? $temp : "Mobile" ;

?>
/*
//	parent.frames["upper"].location.reload( true );
	if(document.all && !(document.getElementById)) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}		
*/		

		parent.frames["upper"].$("user_id").innerHTML  = "<?php print $_SESSION['user_id'];?>";	
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";			// user name
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename(__FILE__));?>";				// module name
	try {
		parent.frames["upper"].$("main_body").style.backgroundColor  = "<?php print get_css('page_background', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("main_body").style.color  = "<?php print get_css('normal_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("tagline").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("user_id").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("unit_id").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("script").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("time_of_day").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("whom").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("level").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("logged_in_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("perms_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("modules_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("time_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11

		parent.frames["upper"].$("term").innerHTML  = "<?php print $term_str;?>";				// responder or 'Mobile' name - 3/19/11

		}
	catch(e) {
		}
		
	function get_new_colors() {													// 5/3/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	function ck_frames() {		//  onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			parent.upper.do_day_night("<?php print $_SESSION['day_night'];?>")
			}
		}		// end function ck_frames()
		
	function ring_fence() {	//	run when new tracked data is received	6/10/11
		if (!google.maps.Polygon.prototype.Contains) {   						// 3/29/2013
				google.maps.Polygon.prototype.Contains = function(latLng) {
						// Outside the bounds means outside the polygon
						if (this.getBounds && !this.getBounds().Contains(latLng)) {
								return false;
						}
					   
						var lat = latLng.lat();
						var lng = latLng.lng();
						var paths = this.getPaths();
						var path, pathLength, inPath, i, j, vertex1, vertex2;
					   
						// Walk all the paths
						for (var p = 0; p < paths.getLength(); p++) {
							   
								path = paths.getAt(p);
								pathLength = path.getLength();
								j = pathLength - 1;
								inPath = false;
							   
								for (i = 0; i < pathLength; i++) {

										vertex1 = path.getAt(i);
										vertex2 = path.getAt(j);

										if (vertex1.lng() < lng && vertex2.lng() >= lng || vertex2.lng() < lng && vertex1.lng() >= lng) {
												if (vertex1.lat() + (lng - vertex1.lng()) / (vertex2.lng() - vertex1.lng()) * (vertex2.lat() - vertex1.lat()) < lat) {
										inPath = !inPath;
									}
								}

								j = i;
									   
								}
							   
								if (inPath) {
										return true;
								}
							   
						}
					   
						return false;
				}				// end function()
			}			// end if (!google.maps.Polygon.prototype.Contains)	
		var thepoint;
		var bound_names = new Array();

<?php

		$query_al = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	6/10/11
		$result_al = mysql_query($query_al);	// 6/10/11
		$al_groups = array();
		while ($row_al = stripslashes_deep(mysql_fetch_assoc($result_al))) 	{	//	6/10/11
			$al_groups[] = $row_al['group'];
			}	

		if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
			$where2 = "WHERE `a`.`type` = 2 AND `r`.`ring_fence` > 0 AND `r`.`lat` != '' AND `r`.`lng` != ''";
			} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}

			$where2 .= " AND `a`.`type` = 2 AND `r`.`ring_fence` > 0 AND `r`.`lat` != '' AND `r`.`lng` != ''";	//	6/10/11
			}			
			
		$query66 = "SELECT `r`.`id` AS `responder_id`,
					`a`.`id` AS `all_id`, 
					`a`.`resource_id` AS `resource_id`,
					`a`.`type` AS `resource_type`,
					`r`.`ring_fence` AS `ring_fence`,
					`r`.`lat` AS `lat`,
					`r`.`lng` AS `lng`,
					`r`.`name` AS `name`
					FROM `$GLOBALS[mysql_prefix]responder` `r`
					LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )	
					{$where2} GROUP BY `r`.`id`";

		$result66 = mysql_query($query66)or do_error($query66, mysql_error(), basename(__FILE__), __LINE__);
		while ($row66 = stripslashes_deep(mysql_fetch_assoc($result66))) 	{
			extract ($row66);
			if((my_is_float($lat)) && (my_is_float($lng))) {		
				print "\t\t	var resp_name = \"$name\";\n";
				print "\t\t var thepoints = new Array();\n";
				print "\t\tvar newpoint = new google.maps.LatLng({$lat}, {$lng});\n";
				$query67 = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id` = {$ring_fence}";
				$result67 = mysql_query($query67)or do_error($query67, mysql_error(), basename(__FILE__), __LINE__);
				$row67 = stripslashes_deep(mysql_fetch_assoc($result67));
					extract ($row67);
					$points = explode (";", $line_data);
					print "\t\t var boundary1 = new Array();\n";					
					print "\t\t var fencename = \"$line_name\";\n";
					for ($yy = 0; $yy < count($points); $yy++) {
						$coords = explode (",", $points[$yy]);
						print "\t\t thepoint = new google.maps.LatLng(parseFloat($coords[0]), parseFloat($coords[1]));\n";
						print "\t\t thepoints.push(thepoint);\n";
					}			// end for ($yy = 0 ... )
				print "\t\t var pline = new google.maps.Polygon({
					paths: 			thepoints,
					strokeColor: 	add_hash(\"$line_color\"),
					strokeOpacity: 	$line_opacity,
					strokeWeight: 	$line_width,
					fillColor: 		add_hash(\"$fill_color\"),
					fillOpacity: 	$fill_opacity
					});\n";
				print "\t\t boundary1.push(pline);\n";
				print "\t\t if(!google.maps.geometry.poly.containsLocation(newpoint,pline)) {\n";
					print "\t\t blink_text(resp_name, '#FF0000', '#FFFF00', '#FFFF00', '#FF0000');\n";
					print "\t\t }\n";
				}
			}
?>
		}	// end function ring_fence		
		
	function exclude() {	//	run when new tracked data is received	6/10/11
		if (!google.maps.Polygon.prototype.Contains) {   						// 3/29/2013
				google.maps.Polygon.prototype.Contains = function(latLng) {
						// Outside the bounds means outside the polygon
						if (this.getBounds && !this.getBounds().Contains(latLng)) {
								return false;
						}
					   
						var lat = latLng.lat();
						var lng = latLng.lng();
						var paths = this.getPaths();
						var path, pathLength, inPath, i, j, vertex1, vertex2;
					   
						// Walk all the paths
						for (var p = 0; p < paths.getLength(); p++) {
							   
								path = paths.getAt(p);
								pathLength = path.getLength();
								j = pathLength - 1;
								inPath = false;
							   
								for (i = 0; i < pathLength; i++) {

										vertex1 = path.getAt(i);
										vertex2 = path.getAt(j);

										if (vertex1.lng() < lng && vertex2.lng() >= lng || vertex2.lng() < lng && vertex1.lng() >= lng) {
												if (vertex1.lat() + (lng - vertex1.lng()) / (vertex2.lng() - vertex1.lng()) * (vertex2.lat() - vertex1.lat()) < lat) {
										inPath = !inPath;
									}
								}

								j = i;
									   
								}
							   
								if (inPath) {
										return true;
								}
						}
						return false;
				}				// end function()
			}			// end if (!google.maps.Polygon.prototype.Contains)	
<?php

		$query_al = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	6/10/11
		$result_al = mysql_query($query_al);	// 6/10/11
		$al_groups = array();
		while ($row_al = stripslashes_deep(mysql_fetch_assoc($result_al))) 	{	//	6/10/11
			$al_groups[] = $row_al['group'];
			}	

		if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
			$where2 = "WHERE `a`.`type` = 2 AND `r`.`excl_zone` > 0 AND `r`.`lat` != '' AND `r`.`lng` != ''";
			} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
				$where2 .= "`a`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}

			$where2 .= " AND `a`.`type` = 2 AND `r`.`excl_zone` > 0 AND `r`.`lat` != '' AND `r`.`lng` != ''";	//	6/24/13
			}			

		$query66 = "SELECT `r`.`id` AS `responder_id`,
					`a`.`id` AS `all_id`, 
					`a`.`resource_id` AS `resource_id`,
					`a`.`type` AS `resource_type`,
					`r`.`excl_zone` AS `excl_zone`,
					`r`.`lat` AS `lat`,
					`r`.`lng` AS `lng`,
					`r`.`name` AS `name`
					FROM `$GLOBALS[mysql_prefix]responder` `r`
					LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON ( `r`.`id` = `a`.`resource_id` )	
					{$where2} GROUP BY `r`.`id`";

		$result66 = mysql_query($query66)or do_error($query66, mysql_error(), basename(__FILE__), __LINE__);
		while ($row66 = stripslashes_deep(mysql_fetch_assoc($result66))) 	{
			extract ($row66);
			if((my_is_float($lat)) && (my_is_float($lng))) {
				print "\t\t	var resp_name = \"$name\";\n";
				print "\t\t var thepoints = new Array();\n";
				print "\t\t var newpoint = new google.maps.LatLng({$lat}, {$lng});\n";
				$query67 = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id` = {$excl_zone}";
				$result67 = mysql_query($query67)or do_error($query67, mysql_error(), basename(__FILE__), __LINE__);
				$row67 = stripslashes_deep(mysql_fetch_assoc($result67));
					extract ($row67);
					$points = explode (";", $line_data);
					print "\t\t var boundary1 = new Array();\n";					
					print "\t\t var fencename = \"$line_name\";\n";
					for ($yy = 0; $yy < count($points); $yy++) {
						$coords = explode (",", $points[$yy]);	
						print "\t\t thepoint = new google.maps.LatLng(parseFloat($coords[0]), parseFloat($coords[1]));\n";
						print "\t\t thepoints.push(thepoint);\n";
					}			// end for ($yy = 0 ... )
					print "\t\t var pline = new google.maps.Polygon({
					    paths: 			thepoints,
					    strokeColor: 	add_hash(\"$line_color\"),
					    strokeOpacity: 	$line_opacity,
					    strokeWeight: 	$line_width,
					    fillColor: 		add_hash(\"$fill_color\"),
					    fillOpacity: 	$fill_opacity
						});\n";
					print "\t\t boundary1.push(pline);\n";
					print "\t\t if(google.maps.geometry.poly.containsLocation(newpoint, pline)) {\n";
					print "\t\t blink_text2(resp_name, '#00FF00', '#FFFF00', '#FFFF00', '#FF0000');\n";
					print "\t\t }\n";
				}
			}
?>
		}	// end function exclude	
<?php																	// 4/10/10
	if (intval(get_variable('call_board')) == 0) {						// hide the button
		print "\t parent.frames['upper'].$('call').style.display = 'none';";
		}
?>		
/* *
 * Concatenates the values of a variable into an easily readable string
 * by Matt Hackett [scriptnode.com]
 * @param {Object} x The variable to debug
 * @param {Number} max The maximum number of recursions allowed (keep low, around 5 for HTML elements to prevent errors) [default: 10]
 * @param {String} sep The separator to use between [default: a single space ' ']
 * @param {Number} l The current level deep (amount of recursion). Do not use this parameter: it's for the function's own use
 */
	function print_r(x, max, sep, l) {
		l = l || 0;
		max = max || 10;
		sep = sep || ' ';
		if (l > max) {
			return "[WARNING: Recursion limit exceeded]\n";
			}
		var
			i,
			r = '',
			t = typeof x,
			tab = '';
		if (x === null) {
			r += "(null)\n";
			} 
		else if (t == 'object') {
			l++;
			for (i = 0; i < l; i++) {
				tab += sep;
				}
			if (x && x.length) {
				t = 'array';
				}
			r += '(' + t + ") :\n";
			for (i in x) {
				try {
					r += tab + '[' + i + '] : ' + print_r(x[i], max, sep, (l + 1));
					} 
				catch(e) {
					return "[ERROR: " + e + "]\n";
					}
				}
			} 
		else {
			if (t == 'string') {
				if (x == '') {
					x = '(empty)';
					}
				}
			r += '(' + t + ') ' + x + "\n";
			}
		return r;
		};
//	var_dump = print_r;
	function show_btns_closed() {						// 4/30/10
		$('btn_go').style.display = 'inline';
		$('btn_can').style.display = 'inline';
		}
	function hide_btns_closed() {
		$('btn_go').style.display = 'none';
		$('btn_can').style.display = 'none';
		document.frm_interval_sel.frm_interval.selectedIndex=0;
		}
	function show_btns_scheduled() {						// 4/30/10
		$('btn_scheduled').style.display = 'inline';
		$('btn_can').style.display = 'inline';
		}
	function hide_btns_scheduled() {
		$('btn_scheduled').style.display = 'none';
		$('btn_can').style.display = 'none';
		document.frm_interval_sel.frm_sched.selectedIndex=0;
		}
		
	function sendRequest(url,callback,postData) {
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

	function syncAjax(strURL) {							// synchronous ajax function - 4/5/10
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name
			return AJAX.responseText;																				 
			} 
		else {
			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
			return false;
			}																						 
		}		// end function sync Ajax()
		
	function do_mail_all_win(the_ticket) {			// 6/16/09
		if(starting) {return;}					
		starting=true;	
		newwindow_um=window.open("do_unit_mail.php?the_ticket=" + the_ticket, "Email",  "titlebar, resizable=1, scrollbars, height=640,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
		if (isNull(newwindow_um)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_um.focus();
		starting = false;
		}		
</SCRIPT>

<?php 
	if ($_SESSION['internet']) {	
?>
		<SCRIPT SRC='./js/usng.js' 			TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 -->
		<SCRIPT SRC="./js/graticule_V3.js" 	TYPE="text/javascript"></SCRIPT>
<?php 
		$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;	 	//	10/23/12
		if((module_active("Ticker")==1) && (!($sit_scr))) {	//	6/1/12, 10/23/12
?>
			<SCRIPT SRC='./modules/Ticker/js/mootools-1.2-core.js' type='text/javascript'></SCRIPT>
			<SCRIPT SRC='./modules/Ticker/js/ticker_core.js' type='text/javascript'></SCRIPT>
			<LINK REL=StyleSheet HREF="./modules/Ticker/css/ticker_css.php?version=<?php print time();?>" TYPE="text/css">
<?php
			$ld_ticker = "ticker_init();";	//	3/23/11 To support ticket module
			}
		}
//								// 3/23/12 - auto-refresh; develop the notification string
	$info_str ="";
	$our_time = mysql_format_date(now() - 30);			// seconds ago ********
	$query = "
		(SELECT '2' AS `which`, `t`.`id`, `scope` AS `the_value`  
			FROM `$GLOBALS[mysql_prefix]patient` `p`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON ( `t`.`id` = `p`.`ticket_id` )
			WHERE `t`.`updated` > '{$our_time}'  AND `t`.`status` <> {$GLOBALS['STATUS_RESERVED']} LIMIT 1)
	UNION 
		(SELECT '1' AS `which`, `t`.`id`, `scope` AS `the_value`  
			FROM `$GLOBALS[mysql_prefix]action` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON ( `t`.`id` = `a`.`ticket_id` )
			WHERE `t`.`updated` > '{$our_time}' AND `t`.`status` <> {$GLOBALS['STATUS_RESERVED']} LIMIT 1)
	UNION 
		(SELECT '0' AS `which`, `id`, `scope` AS `the_value`
			FROM `$GLOBALS[mysql_prefix]ticket` `t`
			WHERE `updated` > '{$our_time}' AND `t`.`status` <> {$GLOBALS['STATUS_RESERVED']} LIMIT 1)
	UNION
		(SELECT  '3' AS `which`, `id`, NULL  AS `the_value` FROM `$GLOBALS[mysql_prefix]log` 
			WHERE `when` = ( SELECT MAX(`when`) FROM `$GLOBALS[mysql_prefix]log` WHERE (`code` IN 
			('{$GLOBALS['LOG_CALL_DISP']}', '{$GLOBALS['LOG_CALL_RESP']}', '{$GLOBALS['LOG_CALL_ONSCN']}', '{$GLOBALS['LOG_CALL_CLR']}', '{$GLOBALS['LOG_CALL_U2FENR']}', '{$GLOBALS['LOG_CALL_U2FARR']}')
			))
		AND (`when` > '{$our_time}') LIMIT 1)
 		";

	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	$do_blink = (mysql_num_rows ($result) > 0);
	if (mysql_num_rows ($result) > 0 ) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		extract ($row);
		$au_refr_key = "A_R_{$which}_{$our_time}";		// auto-refresh key
		@session_start();
		if (!(array_key_exists ($au_refr_key, $_SESSION) && ($_SESSION[$au_refr_key] == $our_time ))) { 		// once only
			$_SESSION[$au_refr_key] = TRUE;
			$gt_new = get_text ("New");
			$gt_incident = get_text ("Incident");
			$gt_action = get_text ("Add Action");
			$gt_patient = get_text ("Add Patient");
			$gt_unit = get_text ("Unit");
			switch ($which) {
				case "0": 
					$info_str = "{$gt_incident}: {$the_value}"; 
					break;
				case "1": 
					$info_str = "{$gt_incident} {$the_value} : {$gt_action}"; 
					break;
				case "2": 
					$info_str = "{$gt_incident} {$the_value} : {$gt_patient}"; 
					break;
				case "3": 
//										4/4/12 - find latests relevant log record
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]log` `l`
						LEFT JOIN `$GLOBALS[mysql_prefix]ticket` 	`t` ON (`l`.`ticket_id` = `t`.`id`)
						LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`l`.`responder_id` = `r`.`id`)
						WHERE `l`.`id` = {$row['id']}
						LIMIT 1";
					$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
					$row = stripslashes_deep(mysql_fetch_assoc($result));
					extract($row);
					$temp =  explode("/", get_variable('disp_stat'));
					if (count ($temp) < 6) {$temp =  explode("/", "D/R/O/FE/FA/Clear");}		// enforce default if user error					
					switch ($code) {
						case $GLOBALS['LOG_CALL_DISP']:
							$info_str = "{$gt_incident} {$scope} : {$gt_unit} {$handle} {$temp[0]}"; 					
							break;
						
						case $GLOBALS['LOG_CALL_RESP']:
							$info_str = "{$gt_incident} {$scope} : {$gt_unit} {$handle} {$temp[1]}"; 					
							break;
						
						case $GLOBALS['LOG_CALL_ONSCN']:
							$info_str = "{$gt_incident} {$scope} : {$gt_unit} {$handle} {$temp[2]}"; 					
							break;
						
						case $GLOBALS['LOG_CALL_U2FENR']:
							$info_str = "{$gt_incident} {$scope} : {$gt_unit} {$handle} {$temp[3]}"; 					
							break;
						
						case $GLOBALS['LOG_CALL_U2FARR']:
							$info_str = "{$gt_incident} {$scope} : {$gt_unit} {$handle} {$temp[4]}"; 					
							break;			
						case $GLOBALS['LOG_CALL_CLR']:
							$info_str = "{$gt_incident} {$scope} : {$gt_unit} {$handle} {$temp[5]}"; 					
							break;
						}				// end switch ($code) 
			}				// end switch ($which)
			
			}				// end if (!(array_key_exists ($au_refr_key ... )))
			
		}				// end if (mysql_num_rows ($result) > 0 ) 
	
?>	
<STYLE TYPE="text/css">
.box { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:1000; width: 180px; }
.bar { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
/* 3/26/2013
.bar_header { height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
*/
.bar_header { height: 30px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
.content { padding: 1em; }
</STYLE>
<SCRIPT>
	var watch_val;
	function start_watch() {							// get initial values from top - 3/23/12
//		alert(681 + parent.frames["upper"].$("div_assign_id").innerHTML);
		parent.frames['upper'].mu_init();				// start the polling
		$("div_ticket_id").innerHTML = parent.frames["upper"].$("div_ticket_id").innerHTML;		// copy for monitoring
		$("div_assign_id").innerHTML = parent.frames["upper"].$("div_assign_id").innerHTML;
		$("div_action_id").innerHTML = parent.frames["upper"].$("div_action_id").innerHTML;	
		$("div_patient_id").innerHTML = parent.frames["upper"].$("div_patient_id").innerHTML;

<?php
	$temp =  explode("/", get_variable('auto_refresh'));			// 5/26/2013
	if ( (count($temp) == 3 ) && (intval ($temp[0]) == 1) ) {		// do set for window.location.reload()
?>		
		watch_val = window.setInterval("do_watch()",5000);		// - 5 seconds
<?php
		}
?>
		}				// end function start watch()

	function end_watch(){
		if (watch_val) {						// possible null
			window.clearInterval(watch_val);
			window.location.reload();
			}
		}				// end function end_watch()

	function do_watch() {								// monitor for changes
//		alert(697);
		if (							// any change?
			($("div_ticket_id").innerHTML != parent.frames["upper"].$("div_ticket_id").innerHTML) ||
			($("div_assign_id").innerHTML != parent.frames["upper"].$("div_assign_id").innerHTML) ||
			($("div_action_id").innerHTML != parent.frames["upper"].$("div_action_id").innerHTML) ||
			($("div_patient_id").innerHTML != parent.frames["upper"].$("div_patient_id").innerHTML)			
			)
				{			  // a change
				end_watch();
				window.location.reload();				
			}
		}			// end function do_watch()		

	
	function do_blink() {																// 3/23/12 - 4/5/12
		$("hdr_td_str").innerHTML = ($("hdr_td_str").innerHTML == "&nbsp;")? the_info  : "&nbsp;" ;
		blink_count--;								// limit blink duration
		if (blink_count==0) {end_blink();}
		}		// end function do_blink()

	var blink_var = false;
	var blink_count;												// duration of blink
	var orig_head_str;												// header string value at start of blink
	var the_info = "<?php echo $info_str; ?>";

	function start_blink () {
		orig_head_str = $("hdr_td_str").innerHTML;
		blink_var = setInterval('do_blink()',500);					// on/off cycle is once per second
		blink_count = 30;											// = 30 seconds
		}
	function end_blink() {
		if (blink_var) {
			$("hdr_td_str").innerHTML = orig_head_str; 					// restore original value		
			clearInterval(blink_var);
			}
		}		// end function
		
	function get_wastebin() {	//	10/23/12
		$(waste_but).style.display = "none";
		$(inbox_but).style.display = "inline";	
		get_wastelist('','',sortby, 'DESC','');
		$('the_box').innerHTML = "Showing Wastebasket";		
		}
		
	function get_inbox() {	//	10/23/12
		$(waste_but).style.display = "inline";
		$(inbox_but).style.display = "none";	
		$('the_box').innerHTML = "Showing Inbox";	
/*		get_main_messagelist(ticket_id,'',sortby, 'DESC','', 'ticket');  3/26/2013  */
		get_all_messagelist(ticket_id,'',sortby, 'DESC','', 'ticket');

		}			
		
	function get_mainmessages(ticket_id, responder_id, sortby, sort, filter, thescreen) {	//	10/23/12
		ticket_id = ticket_id;
/*		get_main_messagelist(ticket_id,'',sortby, sort, filter, 'ticket');	3/26/2013  */
		get_all_messagelist(ticket_id,'',sortby, sort, filter, 'ticket');

		}
<?php
	$do_blink_str = ($do_blink)? "start_blink()" : "";
	$end_blink_str = ($do_blink)? "end_blink()" : "";
?>
</SCRIPT>
</HEAD>
<?php
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;	
	
	if((!(is_guest())) && ($_SESSION['internet']) && (!($get_id))) {	//	4/6/11 Added for add on modules, 6/1/12 only on situation screen, not on ticket detail.
		if(file_exists("./incs/modules.inc.php")) {
			get_modules('main');
			}
		}	
	
	$gunload = "";				// 3/23/12
	$fences = (($_SESSION['internet']) && (!($get_id)))? "fence_init();" : "" ;				// 4/22/11	
	$set_showhide = ((array_key_exists('print', ($_GET)) || (array_key_exists('id', ($_GET)))))? "" : "set_initial_pri_disp(); set_categories(); set_fac_categories();";	//	3/15/11
	$from_right = 20;	//	5/3/11
	$from_top = 10;		//	5/3/11
	$temp = intval(trim(get_variable('situ_refr')));		// 6/27/11
	$refresh =  ($temp < 15)? 15000: $temp * 1000;
	$set_to = (intval(trim(get_variable('situ_refr')))>0)? "setTimeout('location.reload(true);', {$refresh});": "";
	$set_bnds = (($_SESSION['internet']) && (!($get_id)))? "set_bnds();" : "";
	$the_api_key = trim(get_variable('gmaps_api_key'));							// 3/5/12	
//	$set_map = (empty($the_api_key))? "document.to_map.submit();" : "";			 - 1/16/2013
	$set_map = "";	// 1/16/2013
	$set_regions_control = ((!($get_id)) && ((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))) ? "set_regions_control();" : "";	//	6/1/12
	$get_messages = ($get_id) ? "get_mainmessages(" . $get_id . " ,'',sortby, sort, '', 'ticket');" : "";
?>
<BODY onLoad = "ck_frames(); <?php print $ld_ticker;?> <?php print $set_regions_control;?> <?php print $get_messages;?> <?php print $set_showhide;?> <?php print $set_bnds;?> parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; start_watch(); location.href = '#top'; <?php print $do_mu_init;?> <?php print $fences;?> <?php print $do_blink_str;?> " onUnload = "end_watch(); end_blink(); <?php print $gunload;?>";>	<!-- 3/15/11, 10/23/12 -->
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>
<A NAME='top'></A>
<DIV id='top_reg_box' style='display: none;'>
	<DIV id='region_boxes' class='header_reverse' style='align: center; width: 100%; text-align: center; margin-left: auto; margin-right: auto; height: 30px; z-index: 1;'></DIV>
</DIV>
<!-- 3/23/12
<DIV ID='latest' style="display: block-inline; position: fixed; top: 00px; left: 10px; height: auto; width: auto;" ><h3>the ID information:the ID information</h3></div>
 -->
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></div>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->

<A NAME="top" /> <!-- 11/11/09 -->
<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>	<!-- 3/23/12 -->
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<?php
if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	//	6/10/11
?>
		<DIV id = 'regions_outer' style = "position: fixed; right: 20%; top: 10%; z-index: 1000;">
			<DIV id="boxB" class="box" style="z-index:1000;">
<!-- 3/26/2013
				<DIV class="bar_header" class="heading_2" STYLE="z-index: 1000; height: 30px;">Viewed Regions
				<DIV id="collapse_regs" class='plain' style =" display: inline-block; z-index:1001; cursor: pointer; float: right;" onclick="$('top_reg_box').style.display = 'block'; $('regions_outer').style.display = 'none';">Dock</DIV><BR /><BR />
				<DIV class="bar" STYLE="color:red; z-index: 1000; position: relative; top: 2px;"
					onmousedown="dragStart(event, 'boxB')"><i>Drag me</i></DIV>
-->					
 				<DIV class="bar_header" class="heading_2" STYLE="z-index: 1000; height: 30px;">Viewed Regions
 				<DIV id="collapse_regs" class='plain' style =" display: inline-block; z-index:1001; cursor: pointer; float: right;" onclick="$('top_reg_box').style.display = 'block'; $('regions_outer').style.display = 'none';">Dock</DIV><BR /><BR />
 				<DIV class="bar" STYLE="color:red; z-index: 1000; position: relative; top: 2px;"
 					onmousedown="dragStart(event, 'boxB')"><i>Drag me</i></DIV>

				<DIV id="region_boxes2" class="content" style="z-index: 1000;"></DIV> 
				</DIV>
			</DIV>
		</DIV>
<?php
}
	if ($get_print) {
		show_ticket($get_id,'true');
		print "<BR /><P ALIGN='left'>";
		}
	else if ($get_id) {
		add_header($get_id);
		show_ticket($get_id);
		print "<BR /><P ALIGN='left'>";
		}
	else if ($get_sort_by_field && $get_sort_value) {
		list_tickets($get_sort_by_field, $get_sort_value);
		}
	else {
		list_tickets();
		}
		
	$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;		//	10/23/12	
	if((module_active("Ticker")==1) && (!($sit_scr))) {			//	10/23/12
		require_once('./modules/Ticker/incs/ticker.inc.php');
		$the_markers = buildmarkers();
		foreach($the_markers AS $value) {
?>
<SCRIPT>
			var the_point = new google.maps.LatLng(<?php print $value[3];?>, <?php print $value[4];?>);		//	10/23/12
			var the_header = "Traffic Alert";		//	10/23/12
			var the_text = "<?php print $value[1];?>";		//	10/23/12
			var the_id = "<?php print $value[0];?>";		//	10/23/12
			var the_category = "<?php print $value[5];?>";		//	10/23/12
			var the_descrip = "<DIV style='font-size: 14px; color: #000000; font-weight: bold;'>" + the_header + "</DIV><BR />";		//	10/23/12
			the_descrip = "<DIV style='font-size: 14px; color: #000000; font-weight: bold;'>" + the_text + "</DIV><BR />";		//	10/23/12			
			the_descrip += "<DIV style='font-size: 14px; color: #FFFFFF; background-color: #707070; font-weight: bold;'>" + the_category + "</DIV><BR />";		//	10/23/12
			the_descrip += "<DIV style='font-size: 12px; color: blue; font-weight: normal;'>";		//	10/23/12
			the_descrip += "<?php print $value[2];?>";		//	10/23/12
			the_descrip += "</DIV>";		//	10/23/12
			var rss_marker = create_feedMarker(the_point, the_text, the_descrip, the_id, the_id);		//	10/23/12
			rss_marker.setMap(map);			
</SCRIPT>
<?php
		}
	}
?>
<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 11/28/10 not now used - replaced with form to_listtype -->
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>' /> <!-- 11/28/10 not now used - replaced with form to_listtype -->
<INPUT TYPE='hidden' NAME='func' VALUE='' /> <!-- 11/28/10 not now used - replaced with form to_listtype -->
</FORM> <!-- 11/28/10 not now used - replaced with form to_listtype -->
<FORM NAME='to_listtype' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 11/29/10 not now used - replaced with form to_listtype -->

<INPUT TYPE='hidden' NAME='func' VALUE='' />
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' />
</FORM>
<FORM NAME='to_scheduled' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' />
<INPUT TYPE='hidden' NAME='func' VALUE='1' />
</FORM>
<FORM NAME='to_map' METHOD='get' ACTION = 'config.php'> 	<!-- 3/5/12 -->
<INPUT TYPE='hidden' NAME='func' VALUE='api_key' />
</FORM>

<!--
<span onclick = "parent.top.calls.location.reload(true)">Test1</span>
<br />
<span onclick = "parent.top.calls.document.page_refresh_form.submit()">Test2</span>
<br />
<span onclick = "alert(parent.$('what').rows)">Test3</span>
-->
<br /><br />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" ID = "up" BORDER=0></div>
<A NAME="bottom" /> <!-- 11/11/09 -->
</BODY>
<?php
if (array_key_exists('print', ($_GET))) {
?>
<script>
$("down").style.display = $("up").style.display = "none";
</script>
<?php
	}
?>
</HTML>
