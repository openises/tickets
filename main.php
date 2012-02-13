<?php

error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10

session_start();						// 

require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
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
<?php 
@session_start();	
if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
	require_once('./incs/modules.inc.php');
	}	
if ($_SESSION['internet']) {				// 8/22/10
	$api_key = get_variable('gmaps_api_key');	
?>
<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT SRC="./js/epoly.js" TYPE="text/javascript"></SCRIPT>	<!-- 6/10/11 -->
<SCRIPT TYPE="text/javascript" src="./js/ELabel.js"></SCRIPT><!-- 8/1/11 -->
<?php } ?>
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>	<!-- 5/3/11 -->	
<SCRIPT>
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
	parent.frames["upper"].$("add").style.display  = "none";		// hide 'new' button
<?php
	}
?>

	var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php
	var check_initialized = false;
	var check_interval = null;
	
	function logged_in() {								// returns boolean
		var temp = parent.frames["upper"].$("whom").innerHTML==NOT_STR;
		return !temp;
		}

	function $() {									// 1/21/09, 7/18/10
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
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
					$(flag).innerHTML = "RF";							
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
					$(flag).innerHTML = "EZ";					
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
	
		var thepoint;
		var bound_names = new Array();

		  // === A method for testing if a point is inside a polygon
		  // === Returns true if poly contains point
		  // === Algorithm shamelessly stolen from http://alienryderflex.com/polygon/ 
		  
		  GPolygon.prototype.Contains = function(point) {
			var j=0;
			var oddNodes = false;
			var x = point.lng();
			var y = point.lat();
			for (var i=0; i < this.getVertexCount(); i++) {
			  j++;
			  if (j == this.getVertexCount()) {j = 0;}
			  if (((this.getVertex(i).lat() < y) && (this.getVertex(j).lat() >= y))
			  || ((this.getVertex(j).lat() < y) && (this.getVertex(i).lat() >= y))) {
				if ( this.getVertex(i).lng() + (y - this.getVertex(i).lat())
				/  (this.getVertex(j).lat()-this.getVertex(i).lat())
				*  (this.getVertex(j).lng() - this.getVertex(i).lng())<x ) {
				  oddNodes = !oddNodes
				}
			  }
			}
			return oddNodes;
		  }
<?php

		$query_al = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	6/10/11
		$result_al = mysql_query($query_al);	// 6/10/11
		$al_groups = array();
		while ($row_al = stripslashes_deep(mysql_fetch_assoc($result_al))) 	{	//	6/10/11
			$al_groups[] = $row_al['group'];
			}	

		$x=0;	//	6/10/11
		$where2 = "WHERE (";	//	6/10/11
		foreach($al_groups as $grp) {	//	6/10/11
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}

		$where2 .= " AND `a`.`type` = 2 AND `r`.`ring_fence` > 0 AND `r`.`lat` != '' AND `r`.`lng` != ''";	//	6/10/11
		
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
				print "\t\t var points = new Array();\n";
				print "\t\t var newpoint = new GLatLng({$lat}, {$lng});\n";
				$query67 = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id` = {$ring_fence}";
				$result67 = mysql_query($query67)or do_error($query67, mysql_error(), basename(__FILE__), __LINE__);
				$row67 = stripslashes_deep(mysql_fetch_assoc($result67));
					extract ($row67);
					$points = explode (";", $line_data);
					print "\t\t var boundary1 = new Array();\n";					
					print "\t\t var fencename = \"$line_name\";\n";
					for ($yy = 0; $yy < count($points); $yy++) {
						$coords = explode (",", $points[$yy]);		
						print "\t\t thepoint = new GLatLng(parseFloat($coords[0]), parseFloat($coords[1]));\n";
						print "\t\t points.push(thepoint);\n";
					}			// end for ($yy = 0 ... )
					print "\t\t var pline = new GPolygon(points, \"$line_color\", $line_width, $line_opacity, \"$fill_color\", $fill_opacity, {clickable:false});\n";
					print "\t\t boundary1.push(pline);\n";
					print "\t\t if (!(boundary1[0].Contains(newpoint))) {\n";
					print "\t\t blink_text(resp_name, '#FF0000', '#FFFF00', '#FFFF00', '#FF0000');\n";
					print "\t\t }\n";
				}
			}
?>
		}	// end function ring_fence	
		
	function exclude() {	//	run when new tracked data is received	6/10/11
	
		var thepoint;
		var bound_names = new Array();

		  // === A method for testing if a point is inside a polygon
		  // === Returns true if poly contains point
		  // === Algorithm shamelessly stolen from http://alienryderflex.com/polygon/ 
		  
		  GPolygon.prototype.Contains = function(point) {
			var j=0;
			var oddNodes = false;
			var x = point.lng();
			var y = point.lat();
			for (var i=0; i < this.getVertexCount(); i++) {
			  j++;
			  if (j == this.getVertexCount()) {j = 0;}
			  if (((this.getVertex(i).lat() < y) && (this.getVertex(j).lat() >= y))
			  || ((this.getVertex(j).lat() < y) && (this.getVertex(i).lat() >= y))) {
				if ( this.getVertex(i).lng() + (y - this.getVertex(i).lat())
				/  (this.getVertex(j).lat()-this.getVertex(i).lat())
				*  (this.getVertex(j).lng() - this.getVertex(i).lng())<x ) {
				  oddNodes = !oddNodes
				}
			  }
			}
			return oddNodes;
		  }
<?php

		$query_al = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	6/10/11
		$result_al = mysql_query($query_al);	// 6/10/11
		$al_groups = array();
		while ($row_al = stripslashes_deep(mysql_fetch_assoc($result_al))) 	{	//	6/10/11
			$al_groups[] = $row_al['group'];
			}	

		$x=0;	//	6/10/11
		$where2 = "WHERE (";	//	6/10/11
		foreach($al_groups as $grp) {	//	6/10/11
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`a`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}

		$where2 .= " AND `a`.`type` = 2 AND `r`.`excl_zone` > 0 AND `r`.`lat` != '' AND `r`.`lng` != ''";	//	6/10/11
		
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
				print "\t\t var points = new Array();\n";
				print "\t\t var newpoint = new GLatLng({$lat}, {$lng});\n";
				$query67 = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id` = {$excl_zone}";
				$result67 = mysql_query($query67)or do_error($query67, mysql_error(), basename(__FILE__), __LINE__);
				$row67 = stripslashes_deep(mysql_fetch_assoc($result67));
					extract ($row67);
					$points = explode (";", $line_data);
					print "\t\t var boundary1 = new Array();\n";					
					print "\t\t var fencename = \"$line_name\";\n";
					for ($yy = 0; $yy < count($points); $yy++) {
						$coords = explode (",", $points[$yy]);		
						print "\t\t thepoint = new GLatLng(parseFloat($coords[0]), parseFloat($coords[1]));\n";
						print "\t\t points.push(thepoint);\n";
					}			// end for ($yy = 0 ... )
					print "\t\t var pline = new GPolygon(points, \"$line_color\", $line_width, $line_opacity, \"$fill_color\", $fill_opacity, {clickable:false});\n";
					print "\t\t boundary1.push(pline);\n";
					print "\t\t if ((boundary1[0].Contains(newpoint))) {\n";
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
	</SCRIPT>

<?php 
	if ($_SESSION['internet']) {	
?>
		<SCRIPT SRC='./js/usng.js' TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 -->
		<SCRIPT SRC='./js/graticule.js' type='text/javascript'></SCRIPT>
<?php 
 
		if(module_active("Ticker")==1) {
?>
			<SCRIPT SRC='./modules/Ticker/js/mootools-1.2-core.js' type='text/javascript'></SCRIPT>
			<SCRIPT SRC='./modules/Ticker/js/ticker_core.js' type='text/javascript'></SCRIPT>
			<LINK REL=StyleSheet HREF="./modules/Ticker/css/ticker_css.php?version=<?php print time();?>" TYPE="text/css">
<?php
			$ld_ticker = "ticker_init();";	//	3/23/11 To support ticket module
			} else {
			$ld_ticker = "";	//	3/23/11 To support ticket module
		}
	} else {
		$ld_ticker = "";	//	3/23/11 To support ticket module
	}
?>	
<STYLE TYPE="text/css">
.box { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:1000; width: 180px; }
.bar { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
.bar_header { height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
.content { padding: 1em; }
</STYLE>

</HEAD>
<?php
	if(!(is_guest())) {	//	4/6/11 Added for add on modules
		if(file_exists("./incs/modules.inc.php")) {
			get_modules('main');
			}
		}
		
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;	
	
	$gunload = ($_SESSION['internet'])? " onUnload='GUnload();'" : "" ;				// 4/22/11
	$fences = (($_SESSION['internet']) && (!($get_id)))? "fence_init();" : "" ;				// 4/22/11	
	$set_showhide = ((array_key_exists('print', ($_GET)) || (array_key_exists('id', ($_GET)))))? "" : "set_initial_pri_disp(); set_categories(); set_fac_categories();";	//	3/15/11
	$from_right = 20;	//	5/3/11
	$from_top = 10;		//	5/3/11
	$temp = intval(trim(get_variable('situ_refr')));		// 6/27/11
	$refresh =  ($temp < 15)? 15000: $temp * 1000;
	$set_to = (intval(trim(get_variable('situ_refr')))>0)? "setTimeout('location.reload(true);', {$refresh});": "";
	$set_bnds = (($_SESSION['internet']) && (!($get_id)))? "set_bnds();" : "";


	
?>
<BODY onLoad = "<?php print $set_showhide;?> <?php print $set_bnds;?> parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; ck_frames(); location.href = '#top'; <?php print $do_mu_init;?> <?php print $ld_ticker;?> <?php print $fences;?>" <?php print $gunload;?>>	<!-- 3/15/11 -->
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 /></div>

<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->

<A NAME="top" /> <!-- 11/11/09 -->
<?php
if((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1))  {	//	6/10/11
$regs_col_butt = ((isset($_SESSION['regions_boxes'])) && ($_SESSION['regions_boxes'] == "s")) ? "" : "none";	//	6/10/11
$regs_exp_butt = ((isset($_SESSION['regions_boxes'])) && ($_SESSION['regions_boxes'] == "h")) ? "" : "none";	//	6/10/11	
?>
<div id = 'outer' style = "position:fixed; right:<?php print $from_right;?>%; top:<?php print $from_top;?>%; z-index: 1000; ">		<!-- 6/10/11 -->
<div id="boxB" class="box" style="z-index:5000;">
	<div class="bar_header" class="heading_2" STYLE="z-index: 5000;">Viewed <?php print get_text("Regions");?>
	<SPAN id="collapse_regs" style = "display: <?php print $regs_col_butt;?>; z-index:5001; cursor: pointer;" onclick="hideDiv('region_boxes', 'collapse_regs', 'expand_regs');"><IMG SRC = "./markers/collapse.png" ALIGN="right"></SPAN>
	<SPAN id="expand_regs" style = "display: <?php print $regs_exp_butt;?>; z-index:5001; cursor: pointer;" onclick="showDiv('region_boxes', 'collapse_regs', 'expand_regs');"><IMG SRC = "./markers/expand.png" ALIGN="right"></SPAN></div>
	<div class="bar" STYLE="color:red; z-index: 5000;"
       onmousedown="dragStart(event, 'boxB')"><i>Drag me</i></div>
	<div 
  <div id="region_boxes" class="content" style="z-index: 5000;"></div>
</div>
</div>
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
<!--
<span onclick = "parent.top.calls.location.reload(true)">Test1</span>
<br />
<span onclick = "parent.top.calls.document.page_refresh_form.submit()">Test2</span>
<br />
<span onclick = "alert(parent.$('what').rows)">Test3</span>
-->
<br /><br />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
<A NAME="bottom" /> <!-- 11/11/09 -->
</BODY></HTML>
