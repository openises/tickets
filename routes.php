<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

$GLOBALS['NM_LAT_VAL'] 		= 0.999999;												// 2/3/2013

$sortby_distance = TRUE;			// user: set to TRUE or FALSE to determine unit ordering

$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
@session_start();			// 1/7/10

require_once('./incs/functions.inc.php');
require_once($_SESSION['fmp']);		//8/25/10

$sidebar_width = round( .5 * $_SESSION['scr_width']);		// pixels - 3/6/11

$from_top = 50;				// buttons alignment, user-reviseable as needed
//$from_left =  $sidebar_width + (get_variable('map_width')/2);
$from_left =  intval(floor( 0.4 * $_SESSION['scr_width']));		// 5/22/11


$show_tick_left = FALSE;	// controls left-side vs. right-side appearance of incident details - 11/27/09

/*
5/23/08 per AD7PE - line 432
8/25/08 handling sgl quotes in unit names
8/25/08 TITLE to td's
9/23/08 small map control
10/7/08	added auto-mail feature
10/13/08 added onClick() directions
10/13/08 accommodate no location data
10/14/08 added graticule
10/16/08 changed ticket_id to frm_ticket_id - tbd
10/16/08 added traffic functions
10/17/08 allow map click for directions if error
10/25/08 pointer housekeeping when can't route
10/26/08 always accept click
11/8/08 commas as separator
1/21/09 added show butts - re button menu
1/29/09 icon letter to number
2/15/09 added do_mail_win() for mail text editing
2/25/09 handle empty lat/lng
3/30/09 drop htmlentities for utf-8 handling
4/27/09 addslashes vs htmlentities, for easy
5/22/09	Multi handling, 
6/3/09	checkbox relocated
6/14/09	guest handling corrected
7/7/09	float check corrected, div transparent
7/13/09	fetch_assoc, direcs array
7/24/09	pick up in_types for protocol display
8/2/09	floating div location revised
8/7/09	disallow multiple assigns unless 'multi' is set
8/10/09	`tick_descr` added to query to resolve 'description' ambiguity
8/17/09	 street view added, select only cleared units
10/6/09 Added multi point routes for receiving facility and mail route to unit capability, added links button
10/28/09 Mail Direcs button hidden on load, shown on select after timer
10/28/09 Add Loading Directions message in floating menu.
10/29/09 Added ticket scope to hidden form filed for passing to do_direcs_mail script
11/12/09 corrections for 'direcs' handling, array indexing
11/15/09 revised logic re identifying units with position data
11/23/09 'quick' operation restored
11/27/09 relocated incident information to underneath map, added address to floating div
12/09/09 Changed order of unit display to match that in situation screen.
1/7/10 session start correction, 'call_taker' alias added to query
4/24/10 added sort by responder proximity to incident
3/30/10 div height 100%, get_cd_str() corrected
5/6/10 'from_left' retired, sidebar width revised, per 5/6/10 msg
5/21/10 sql prefix correction
5/28/10 removed gratuitous unit status update
5/30/10 added status dispatch disallowed
6/25/10 added year check to NULL check three places
6/29/10 target added as correction two places
7/9/10 div height calc, form -> get, 'more' button added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
7/29/10 onload revised  for 'quick' and mail handling
8/9/10 corrections to resolve ambiguous address field names
8/25/10 require FMP added
8/30/10 to main.php vs. index
9/23/10 div position from left, top repaired
11/18/10 Added filter by capabilities and fixed individual unit dispatch.
2/5/11 - drag bar visibility correction for IE 
3/4/11 added assigned incident to table display - via function get_assigned_td(), up/down arrows relocated
3/15/11 added reference to stylesheet.php for revisable day mnight colors plus other small fixes
5/4/11 get_new_colors() added
5/22/11 revised drag bar location to approx screen center
5/28/11 intrusion detection added
6/10/11 Added Regions / Groups
8/1/11 Added functions do_landb, drawBanner to support banners and boundaries.
3/13/12 corrected log record written re dispatch
6/20/12 applied get_text() to "Units"
3/29/2013 conform to 20C
*/

do_login(basename(__FILE__));		// 
if ((isset($_REQUEST['ticket_id'])) && (!(strval(intval($_REQUEST['ticket_id']))===$_REQUEST['ticket_id']))) {	shut_down();}	// 5/28/11
//$istest = TRUE;
if($istest) {
	print "GET<br />\n";
	dump($_GET);
	}
	
if (!(isset ($_SESSION['allow_dirs']))) {	
	$_SESSION['allow_dirs'] = 'true';			// note js-style LC
	}

function get_ticket_id () {				// 5/4/11
	if (array_key_exists('ticket_id', ($_REQUEST))) {
		$_SESSION['active_ticket'] = $_REQUEST['ticket_id'];
		return (integer) $_REQUEST['ticket_id'];
		}
	elseif (array_key_exists('active_ticket', $_SESSION)) {
		return (integer) $_SESSION['active_ticket'];	
		}
	else {
		echo "error at "	 . __LINE__;
		}								// end if/else
	}				// end function

$_GET = stripslashes_deep($_GET);
$eol = "< br />\n";

$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}

$icons = $GLOBALS['icons'];				// 1/1/09
$sm_icons = $GLOBALS['sm_icons'];

function get_icon_legend (){			// returns legend string - 1/1/09
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle` ASC, `name` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$type_data = $u_types[$row['type']];
		$print .= "\t\t" .$type_data[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$type_data[1]] . "' BORDER=0 />&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<HEAD><TITLE>Tickets - Routes Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> 
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7"/> 
	
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
    <STYLE TYPE="text/css">
		body 				{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
		table 				{border-collapse: collapse; }
		table.directions th {background-color:#EEEEEE;}	  
		img 				{color: #000000;}
		span.even 			{background-color: #DEE3E7;}
		span.warn			{display:none; background-color: #FF0000; color: #FFFFFF; font-weight: bold; font-family: Verdana, Arial, sans serif; }

		span.mylink			{margin-right: 32PX; text-decoration:underline; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_1		{margin-right: 32PX; text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_2		{margin-right: 8PX;  text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}

		.box { background-color: transparent; border: none; color: #000000; padding: 0px; position: absolute; }
		.bar { background-color: transparent; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em; }
		.bar_header { height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}			
		
		.box2 { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:10000; width: 180px; }
		.bar2 { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:10000; text-align: center;}
		.content { padding: 1em; text-align: center; }		
	</STYLE>

<SCRIPT>
	try {	
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
	
	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name
//			alert ("332 " + AJAX.responseText);
			return AJAX.responseText;																				 
			} 
		else {
//			alert ("158: failed");
			alert("failed at line <?php print __LINE__;?>");
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

	function get_new_colors() {								// 5/4/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	function docheck(in_val){				// JS boolean  - true/false
		document.routes_Form.frm_allow_dirs.value = in_val;	
		url = "do_session_get.php?the_name=allow_dirs&the_value=" + in_val.trim();
		syncAjax(url);			// note asynch call
		}
		
	function isNull(arg) {
		return arg===null;
		}

	function $() {									// 2/11/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}
		
	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}	
		
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
		return true;
		}
	String.prototype.trim = function () {									// added 6/10/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity) {		// 8/19/09

		var circle = new google.maps.Circle({
				center: new google.maps.LatLng(lat,lng),
				map: map,
				fillColor: fillColor,
				fillOpacity: fillOpacity,
				strokeColor: strokeColor,
				strokeOpacity: strokeOpacity,
				strokeWeight: strokeWidth
			});
		circle.setRadius(radius*5000); 

		}		// end drawCircle 
		
	function drawBanner(point, html, text, font_size, color, name) {        // Create the banner - 6/5/2013
		var invisibleIcon = new google.maps.MarkerImage("./markers/markerTransparent.png");
		map.setCenter(point, 8);
		var the_color = (typeof color == 'undefined')? "#000000" : color ;	// default to black
		var label = new ELabel({
			latlng: point, 
			label: html, 
			classname: "label", 
			offset: new google.maps.Size(-8, 4), 
			opacity: 100,
			theSize: font_size + "px",		
			theColor:add_hash(the_color),
			overlap: true,
			clicktarget: false
			});	
		label.setMap(map);		
		var marker = new google.maps.Marker(point,invisibleIcon);	        // Create an invisible google.maps.Marker
		marker.setMap(map);				
		}				// end function draw Banner()

	function add_hash(in_str) { // prepend # if absent
		return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
		}			
			
//	$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `line_status` = 0 AND (`use_with_bm` = 1 OR `use_with_r` = 1)";

	function do_landb() {				// JS function - 8/1/11
//		alert(347);
		var points = new Array();
<?php
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `line_status` = 0 AND (`use_with_bm` = 1 OR `use_with_r` = 1)";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			$empty = FALSE;
			extract ($row);
			$name = $row['line_name'];
			switch ($row['line_type']) {
				case "p":				// poly
					$points = explode (";", $line_data);

					$sep = "";
					echo "\n\t var points = [\n";
					for ($i = 0; $i<count($points); $i++) {
						$coords = explode (",", $points[$i]);
						echo	"{$sep}\n\t\tnew google.maps.LatLng({$coords[0]}, {$coords[1]})";
						$sep = ",";					
						}			// end for ($i = 0 ... )
					echo "];\n";

			 	if ((intval($filled) == 1) && (count($points) > 2)) {
?>
//					446
					  polyline = new google.maps.Polygon({
					    paths: 			 points,
					    strokeColor: 	 add_hash("<?php echo $line_color;?>"),
					    strokeOpacity: 	 <?php echo $line_opacity;?>,
					    strokeWeight: 	 <?php echo $line_width;?>,
					    fillColor: 		 add_hash("<?php echo $fill_color;?>"),
					    fillOpacity: 	 <?php echo $fill_opacity;?>
						});

<?php			} else {
?>
//					457
//				    var polyline = new google.maps.Polyline(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>);
					  polyline = new google.maps.Polygon({
					    paths: 			points,
					    strokeColor: 	add_hash("<?php echo $line_color;?>"),
					    strokeOpacity: 	<?php echo $line_opacity;?>,
					    strokeWeight: 	<?php echo $line_width;?>,
					    fillColor: 		add_hash("<?php echo $fill_color;?>"),
					    fillOpacity: 	<?php echo $fill_opacity;?>
						});
<?php			} ?>				        
					polyline.setMap(map);		
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
					echo "\n var point = new google.maps.LatLng(parseFloat({$coords[0]}) , parseFloat({$coords[1]}));\n";
					$the_banner = htmlentities($banner, ENT_QUOTES);
					$the_width = intval( trim($line_width), 10);		// font size
					echo "\n drawBanner( point, '{$the_banner}', '{$the_banner}', {$the_width}, add_hash('{$line_color}'));\n";
					break;
				}	// end switch
		}			// end while ()
		unset($query, $result);
?>
		}		// end function do landb()

	var to_visible = "visible";
	var to_hidden = "hidden";
	function show_butts(strValue) {								// 3/15/11
		$('mail_dir_but').style.visibility = strValue;
		$('reset_but').style.visibility = strValue;
		$('can_but').style.visibility = strValue;
		if ($('disp_but')) {$('disp_but').style.visibility = strValue;}
		}

	function hideDiv(div_area, hide_cont, show_cont) {	//	3/15/11
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "resp_list_sh") {
			var controlarea = "resp_list";
			}
		if (div_area == "facs_list_sh") {
			var controlarea = "facs_list";
			}
		if (div_area == "incs_list_sh") {
			var controlarea = "incs_list";
			}
		if (div_area == "region_boxes") {
			var controlarea = "region_boxes";
			}			
		var divarea = div_area 
		var hide_cont = hide_cont 
		var show_cont = show_cont 
		if($(divarea)) {
			$(divarea).style.display = 'none';
			$(hide_cont).style.display = 'none';
			$(show_cont).style.display = '';
			} 
		var params = "f_n=" +controlarea+ "&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);			
		} 

	function showDiv(div_area, hide_cont, show_cont) {	//	3/15/11
		if (div_area == "buttons_sh") {
			var controlarea = "hide_controls";
			}
		if (div_area == "resp_list_sh") {
			var controlarea = "resp_list";
			}
		if (div_area == "facs_list_sh") {
			var controlarea = "facs_list";
			}
		if (div_area == "incs_list_sh") {
			var controlarea = "incs_list";
			}
		if (div_area == "region_boxes") {
			var controlarea = "region_boxes";
			}				
		var divarea = div_area
		var hide_cont = hide_cont 
		var show_cont = show_cont 
		if($(divarea)) {
			$(divarea).style.display = '';
			$(hide_cont).style.display = '';
			$(show_cont).style.display = 'none';
			}
		var params = "f_n=" +controlarea+ "&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);					
		}
</SCRIPT>	
<script type="text/javascript">//<![CDATA[
//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************
// Determine browser and version.
function Browser() {
	var ua, s, i;
	this.isIE		= false;
	this.isNS		= false;
	this.version = null;
	ua = navigator.userAgent;
	s = "MSIE";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isIE = true;
		this.version = parseFloat(ua.substr(i + s.length));
		return;
		}
	s = "Netscape6/";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isNS = true;
		this.version = parseFloat(ua.substr(i + s.length));
		return;
		}
	// Treat any other "Gecko" browser as NS 6.1.
	s = "Gecko";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isNS = true;
		this.version = 6.1;
		return;
		}
	}
var browser = new Browser();
var dragObj = new Object();		// Global object to hold drag information.
dragObj.zIndex = 0;
function dragStart(event, id) {
	var el;
	var x, y;
	if (id)										// If an element id was given, find it. Otherwise use the element being
		dragObj.elNode = document.getElementById(id);	// clicked on.
	else {
		if (browser.isIE)
			dragObj.elNode = window.event.srcElement;
		if (browser.isNS)
			dragObj.elNode = event.target;
		if (dragObj.elNode.nodeType == 3)		// If this is a text node, use its parent element.
			dragObj.elNode = dragObj.elNode.parentNode;
		}
	if (browser.isIE) {			// Get cursor position with respect to the page.
		x = window.event.clientX + document.documentElement.scrollLeft
			+ document.body.scrollLeft;
		y = window.event.clientY + document.documentElement.scrollTop
			+ document.body.scrollTop;
		}
	if (browser.isNS) {
		x = event.clientX + window.scrollX;
		y = event.clientY + window.scrollY;
		}
	dragObj.cursorStartX = x;		// Save starting positions of cursor and element.
	dragObj.cursorStartY = y;
	dragObj.elStartLeft	= parseInt(dragObj.elNode.style.left, 10);
	dragObj.elStartTop	 = parseInt(dragObj.elNode.style.top,	10);
	if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
	if (isNaN(dragObj.elStartTop))	dragObj.elStartTop	= 0;
	dragObj.elNode.style.zIndex = ++dragObj.zIndex;		// Update element's z-index.
	if (browser.isIE) {									// Capture mousemove and mouseup events on the page.
		document.attachEvent("onmousemove", dragGo);
		document.attachEvent("onmouseup",	 dragStop);
		window.event.cancelBubble = true;
		window.event.returnValue = false;
		}
	if (browser.isNS) {
		document.addEventListener("mousemove", dragGo,	 true);
		document.addEventListener("mouseup",	 dragStop, true);
		event.preventDefault();
		}
	}
function dragGo(event) {
	var x, y;
	if (browser.isIE) {	// Get cursor position with respect to the page.
		x = window.event.clientX + document.documentElement.scrollLeft
			+ document.body.scrollLeft;
		y = window.event.clientY + document.documentElement.scrollTop
			+ document.body.scrollTop;
		}
	if (browser.isNS) {
		x = event.clientX + window.scrollX;
		y = event.clientY + window.scrollY;
		}
	dragObj.elNode.style.left = (dragObj.elStartLeft + x - dragObj.cursorStartX) + "px";	// Move drag element by the same amount the cursor has moved.
	dragObj.elNode.style.top	= (dragObj.elStartTop	+ y - dragObj.cursorStartY) + "px";
	if (browser.isIE) {
		window.event.cancelBubble = true;
		window.event.returnValue = false;
		}
	if (browser.isNS)
		event.preventDefault();
	}
function dragStop(event) {
	if (browser.isIE) {	// Stop capturing mousemove and mouseup events.
		document.detachEvent("onmousemove", dragGo);
		document.detachEvent("onmouseup",	 dragStop);
		}
	if (browser.isNS) {
		document.removeEventListener("mousemove", dragGo,	 true);
		document.removeEventListener("mouseup",	 dragStop, true);
		}
	}
//]]></script>
<?php
if((array_key_exists('func', $_REQUEST)) && ($_REQUEST['func'] == "do_db")) {	// 		new, populate 10/2/08

	extract($_REQUEST);
	$the_ticket_id = (integer) $_REQUEST["frm_ticket_id"];
	$addrs = array();		// 10/7/08
	$smsgaddrs = array();	// 10/23/12
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 
	$assigns = explode ("|", $_REQUEST['frm_id_str']);		// pipe sep'd id's in frm_id_str
	for ($i=0;$i<count($assigns); $i++) {		//10/6/09 added facility and receiving facility
		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`, `dispatched`, `facility_id`, `rec_facility_id`)
						VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
							quote_smart($now),
							quote_smart($frm_status_id),
							quote_smart($frm_ticket_id),
							quote_smart($assigns[$i]),
							quote_smart($frm_comments),
							quote_smart($frm_by_id),
							quote_smart($now),
							quote_smart($frm_facility_id),
							quote_smart($frm_rec_facility_id));
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//										remove placeholder inserted by 'add'		
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . quote_smart($frm_ticket_id) . " AND `responder_id` = 0 LIMIT 1";
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

							// apply status update to unit status

		$query = "SELECT `id`, `contact_via`, `smsg_id` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . quote_smart($assigns[$i])  ." LIMIT 1";		// 10/7/08
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row_addr = stripslashes_deep(mysql_fetch_assoc($result));
		if (is_email($row_addr['contact_via'])) {array_push($addrs, $row_addr['contact_via']); }		// to array for emailing to unit
		if ($row_addr['smsg_id'] != "") {array_push($smsgaddrs, $row_addr['smsg_id']); }		// to array for sending message via SMS Gateway to unit	//	10/23/12
		do_log($GLOBALS['LOG_CALL_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);		// 3/13/12
		if ($frm_facility_id != 0) {
			do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);
			}
		if ($frm_rec_facility_id != 0) {
			do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);
			}
		}
?>	
<SCRIPT>
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
//				alert('HTTP error ' + req.status);
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
	
	
	function handleResult(req) {				// the 'called-back' function
												// onto floor!
		}

	var starting = false;						// 2/15/09

	function do_mail_win(addrs, smsgaddrs, ticket_id) {	
		if(starting) {return;}					// dbl-click catcher
//		alert(" <?php print __LINE__; ?> " +addrs);
		starting=true;	
		var url = "mail_edit.php?ticket_id=" + ticket_id + "&addrs=" + addrs + "&smsgaddrs=" + smsgaddrs + "&text=";	// no text
		newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_mail)) {
			alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_mail.focus();
		starting = false;
		}		// end function do mail_win()

<?php 
	$temp = get_variable('call_board');		// refresh call board
	switch ($temp) {
		case 1 :		// window
//			print "\n alert(305);\n";
//			print "\n\tparent.top.calls.newwindow_cb.do_refresh();\n";
			break;
		case 2 :		// frame
//			print "\n alert(309);\n";
			print "\n\tparent.top.calls.do_refresh();\n";
			break;
		default :
//			print "\n alert(306);\n";	
				alert("failed at line <?php print __LINE__;?>");
		}	// end switch ($temp)
	
?>	

</SCRIPT>
</HEAD>

<?php
								// 7/29/10
	$addr_str = urlencode( implode("|", array_unique($addrs)));
	$smsg_add_str = urlencode( implode(",", array_unique($smsgaddrs)));
	$mail_str = (empty($addr_str))? "" :  "do_mail_win('{$addr_str}', '{$smsg_add_str}', '{$_REQUEST['frm_ticket_id']}');";
	$quick_str = ((get_variable('quick'))==1)? "document.more_form.submit();" : "";
	$extra =  (((empty($mail_str)) && (empty($quick_str))))? "" : " onLoad = \"{$mail_str}{$quick_str}\"";

	print "\n<BODY{$extra}> <!-- " . __LINE__ . " --> \n";		
?>	
<SCRIPT>
if (window.opener && !window.opener.closed) {
	window.opener.parent.frames['upper'].show_msg ('Email sent!');
	}
else {	
	parent.frames['upper'].show_msg ('Email sent!');
	}
</SCRIPT>
	<CENTER><BR><BR><BR><BR><H3>Call Assignments made to:<BR /><?php print substr((str_replace ( "\n", ", ", $_REQUEST['frm_name_str'])) , 0, -2);?><BR><BR> <!-- 11/8/08 -->
<?php print (intval(get_variable("call_board")) == 1)? "See Call Board": "";?>	
	</H3>
	<NOBR>
	<FORM NAME='more_form' METHOD = 'get' ACTION = "<?php print basename(__FILE__); ?>" style="display: inline;"><!-- 7/9/10 -->
	<INPUT TYPE='button' VALUE='More' onClick = "document.more_form.submit()" />
	<INPUT TYPE = 'hidden' NAME = 'ticket_id' VALUE="<?php print get_ticket_id ();?>">
	</FORM>
	<FORM NAME='cont_form' METHOD = 'get' ACTION = "main.php" STYLE = 'margin-left:20px; display: inline;'><!-- 8/30/10  -->
	<INPUT TYPE='button' VALUE='Finished' onClick = "document.cont_form.submit()" />
	</FORM>
	</NOBR>
	</BODY></HTML>
<?php		
	unset ( $_SESSION['active_ticket']);
	}		// end if ("do_db")

//	=============================  major split =============================== 7/9/10

else {	 
	require_once ('./incs/routes_inc.php');		// 7/8/10

	$the_ticket_id = get_ticket_id ();
	$api_key = trim(get_variable('gmaps_api_key'));
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
?>
<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $key_str;?>sensor=false"></SCRIPT>

<SCRIPT SRC="./js/usng.js"></SCRIPT>		<!-- 10/14/08 -->
<SCRIPT SRC="./js/graticule_V3.js"></SCRIPT>
<SCRIPT SRC="./js/elabel_v3.js" TYPE="text/javascript"></SCRIPT><!-- 8/1/11 -->	
<SCRIPT SRC="./js/gmaps_v3_init.js"	TYPE="text/javascript" ></script>	<!-- 1/29/2013 -->
<SCRIPT SRC="./js/domready.js"		TYPE="text/javascript" ></script>

<SCRIPT>


	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

	String.prototype.parseDeg = function() {
		if (!isNaN(this)) return Number(this);								// signed decimal degrees without NSEW
		
		var degLL = this.replace(/^-/,'').replace(/[NSEW]/i,'');			// strip off any sign or compass dir'n
		var dms = degLL.split(/[^0-9.,]+/);									// split out separate d/m/s
		for (var i in dms) if (dms[i]=='') dms.splice(i,1);					// remove empty elements (see note below)
		switch (dms.length) {												// convert to decimal degrees...
			case 3:															// interpret 3-part result as d/m/s
				var deg = dms[0]/1 + dms[1]/60 + dms[2]/3600; break;
			case 2:															// interpret 2-part result as d/m
				var deg = dms[0]/1 + dms[1]/60; break;
			case 1:															// decimal or non-separated dddmmss
				if (/[NS]/i.test(this)) degLL = '0' + degLL;	// - normalise N/S to 3-digit degrees
				var deg = dms[0].slice(0,3)/1 + dms[0].slice(3,5)/60 + dms[0].slice(5)/3600; break;
			default: return NaN;
			}
		if (/^-/.test(this) || /[WS]/i.test(this)) deg = -deg; // take '-', west and south as -ve
		return deg;
		}
	Number.prototype.toRad = function() {  // convert degrees to radians
		return this * Math.PI / 180;
		}

	Number.prototype.toDeg = function() {  // convert radians to degrees (signed)
		return this * 180 / Math.PI;
		}
	Number.prototype.toBrng = function() {  // convert radians to degrees (as bearing: 0...360)
		return (this.toDeg()+360) % 360;
		}
	function brng(lat1, lon1, lat2, lon2) {
		lat1 = lat1.toRad(); lat2 = lat2.toRad();
		var dLon = (lon2-lon1).toRad();
	
		var y = Math.sin(dLon) * Math.cos(lat2);
		var x = Math.cos(lat1)*Math.sin(lat2) -
						Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
		return Math.atan2(y, x).toBrng();
		}

	distCosineLaw = function(lat1, lon1, lat2, lon2) {
		var R = 6371; // earth's mean radius in km
		var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
				Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
		return d;
		}
    var km2feet = 3280.83;

	function min(inArray) {				// returns index of least float value in inArray
		var minsofar =  40076.0;		// initialize to earth circumference (km)
		var j=-1;
		for (var i=1; i< inArray.length; i++){											// 11/12/09
			if ((lats[i]) &&  (parseFloat(inArray[i]) < parseFloat(minsofar))) { 		// 11/12/09
				j=i;
				minsofar=inArray[i];
				}
			}
		return (j>0) ? j: false;
		}		// end function min()

	function ck_frames() {		// onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
function doReset() {
	document.reLoad_Form.submit();
	}	// end function doReset()
	
<?php
	$addrs = FALSE;												// notifies address array doesn't exist
	if (array_key_exists ( "email", $_GET)) {						// 10/23/08
		$addrs = notify_user(0,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE
		}				// end if (array_key_exists())

	$dispatches_disp = array();										// unit id to ticket descr	- 5/23/09
	$dispatches_act = array();										// actuals
	
	$query = "SELECT *, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,  `t`.`scope` AS `theticket`,
		`r`.`id` AS `theunit_id` 
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";				// 6/25/10

	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if(!(empty($row['theunit_id']))) {
			$dispatches_act[$row['theunit_id']] = (empty($row['clear']))? $row['ticket_id']:"";	// blank = unit unassigned

			if ($row['multi']==1) {
				$dispatches_disp[$row['theunit_id']] = "**";					// identify as multiple - 5/22/09
				}
			else {
				$dispatches_disp[$row['theunit_id']] = (empty($row['clear']))? $row['theticket']:"";	// blank = unit unassigned
				}		// end if/else(...)
			}
		}		// end while (...)

	
//										8/10/09, 10/6/09, 1/7/10, 8/9/10
	$query = "SELECT *,
		UNIX_TIMESTAMP(problemstart) AS problemstart,
		UNIX_TIMESTAMP(problemend) AS problemend,
		UNIX_TIMESTAMP(booked_date) AS booked_date,		
		UNIX_TIMESTAMP(date) AS date,
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated,
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`lat` AS `rf_lat`,
		`rf`.`lng` AS `rf_lng`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` 
		FROM `$GLOBALS[mysql_prefix]ticket`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`={$the_ticket_id} LIMIT 1";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_ticket = stripslashes_deep(mysql_fetch_array($result));
	$facility = $row_ticket['facility'];
	$rec_fac = $row_ticket['rec_facility'];
	$lat = $row_ticket['lat'];
	$lng = $row_ticket['lng'];
	
	print "var thelat = " . $lat . ";\nvar thelng = " . $lng . ";\n";		// set js-accessible location data
//	unset ($result);

	if ($rec_fac > 0) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`=" . $rec_fac . "";			// 10/6/09
		$result_rfc = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_rec_fac = stripslashes_deep(mysql_fetch_array($result_rfc));
		$rf_lat = $row_rec_fac['lat'];
		$rf_lng = $row_rec_fac['lng'];
		$rf_name = $row_rec_fac['name'];		
		
		unset ($result_rfc);
		} else {
//		print "var thereclat;\nvar thereclng;\n";		// set js-accessible location data for receiving facility
	}

	if(empty($_SESSION)) {session_start();}		// 

?>
var the_position;
function get_position () {
	var myDiv = document.getElementById('side_bar');
	var side_bar_width = myDiv.offsetWidth; 		
	var myDiv = document.getElementById('map_canvas');
	var map_width = myDiv.offsetWidth; 		
	the_position = side_bar_width + map_width + 10;
	}


function filterSubmit() {		//	11/18/10
	document.filter_Form.submit();
	}

function filterReset() {		//	11/18/10
	document.filter_Form.capabilities.value="";
	document.filter_Form.submit();
	}

function checkArray(form, arrayName)	{	//	5/3/11
	var retval = new Array();
	for(var i=0; i < form.elements.length; i++) {
		var el = form.elements[i];
		if(el.type == "checkbox" && el.name == arrayName && el.checked) {
			retval.push(el.value);
		}
	}
return retval;
}	
	
function checkForm(form)	{	//	6/10/11
	var errmsg="";
	var itemsChecked = checkArray(form, "frm_group[]");
	if(itemsChecked.length > 0) {
		var params = "f_n=viewed_groups&v_n=" +itemsChecked+ "&sess_id=<?php print get_sess_key(__LINE__); ?>";	//	3/15/11
		var url = "persist3.php";	//	3/15/11	
		sendRequest (url, fvg_handleResult, params);				
//			form.submit();
	} else {
		errmsg+= "\tYou cannot Hide all the regions\n";
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
		}
	}
}

function fvg_handleResult(req) {	// 6/10/11	The persist callback function for viewed groups.
	document.region_form.submit();
	}
	
function form_validate(theForm) {	//	6/10/11
//		alert("Validating");
	checkForm(theForm);
	}				// end function validate(theForm)

function sendRequest(url,callback,postData) {	//	6/10/11
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

function toggle_div(theDiv, theButton, theText) {
	if($(theDiv).style.display == 'block') {
			$(theDiv).style.display = 'none';
			$(theButton).innerHTML = "Show " + theText; 
		} else {
		if(theButton == "toggle_dirs") {
			$('the_ticket').style.display = 'none';
			$('disp_details').style.display = 'none';	
			$('the_messages').style.display = 'none';				
			$('toggle_tkt').innerHTML = "Show Ticket";	
			$('toggle_dispatch').innerHTML = "Show Disp Details";
			$('toggle_msgs').innerHTML = "Show Messages";			
			} else if(theButton == "toggle_tkt") {
			$('directions').style.display = 'none';
			$('disp_details').style.display = 'none';
			$('the_messages').style.display = 'none';				
			$('toggle_dirs').innerHTML = "Show Directions";	
			$('toggle_dispatch').innerHTML = "Show Disp Details";
			$('toggle_msgs').innerHTML = "Show Messages";				
			} else if(theButton == "toggle_dispatch") {
			$('directions').style.display = 'none';
			$('disp_details').style.display = 'none';	
			$('the_messages').style.display = 'none';	
			$('toggle_dirs').innerHTML = "Show Directions";	
			$('toggle_dispatch').innerHTML = "Show Disp Details";
			$('toggle_msgs').innerHTML = "Show Messages";				
			} else if(theButton == "toggle_msgs") {
			$('the_ticket').style.display = 'none';			
			$('directions').style.display = 'none';
			$('disp_details').style.display = 'none';	
			$('toggle_tkt').innerHTML = "Show Ticket";				
			$('toggle_dirs').innerHTML = "Show Directions";	
			$('toggle_dispatch').innerHTML = "Show Disp Details";
			}
		$(theButton).innerHTML = "Hide " + theText;				
		$(theDiv).style.display = 'block';
		$(theButton).innerHTML = "Hide " + theText;
		}
	}
		

</SCRIPT>
</HEAD>
<BODY onLoad = "get_position(); do_notify(); ck_frames()" >
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>		<!-- 3/4/11 -->

<A NAME='page_top' />
	<TABLE BORDER = 0 ID= 'main' STYLE='display:block;'>
	<TR><TD VALIGN='top' STYLE = 'height: 1px;'>
<?php
		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";		// 5/12/10
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		unset($result);		
		$required = 96 + (mysql_affected_rows()*22);		// 7/9/10
		$the_height = (integer)  min (round($units_side_bar_height * $_SESSION['scr_height']), $required );		// set the max
		$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
		$capabilities = (array_key_exists('capabilities', $_GET))? stripslashes(trim(str_replace('/', '|', $_GET['capabilities']))) : "" ;	// 11/18/10
		$disabled = ($capabilities=="")? "disabled" : "" ;	// 11/18/10

?>
		<DIV ID='side_bar' style="height:<?php print $the_height; ?>px;  width:<?php print $sidebar_width; ?>px;  overflow-y: auto; overflow-x: auto;"></DIV><!-- 5/12/10 -->
<?php
		$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
		if($unit_id=="") { 	// 11/18/10
?>
			<DIV ID='theform' style='position: relative; top: 10px; background-color: transparent; border-color: #000000;'><!-- 11/18/10 -->	
			<TABLE ALIGN='center' BORDER='0'>
			<TR class='heading'><TH class='heading'>FILTER BY CAPABILITIES</TH></TR>	<!-- 3/15/11 -->
			<FORM NAME='filter_Form' METHOD="GET" ACTION="routes.php">
			<TR class='odd'><TD ALIGN='center'>Filter Type: <b>OR </b><INPUT TYPE='radio' NAME='searchtype' VALUE='OR' checked><b>AND </b><INPUT TYPE='radio' NAME='searchtype' VALUE='AND'></TD></TR>	<!-- 3/15/11 -->
			<TR class='even'><TD><INPUT SIZE='48' TYPE='text' NAME='capabilities' VALUE='<?php print $capabilities;?>' MAXLENGTH='64'></TD></TR>	<!-- 3/15/11 -->
			<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print get_ticket_id (); ?>' />
			<INPUT TYPE='hidden' NAME='unit_id' 	VALUE='<?php print $unit_id; ?>' />
			<TR class='odd'><TD align="center"><input type="button" OnClick="filterSubmit();" VALUE="Filter"/>&nbsp;&nbsp;<input type="button" OnClick="filterReset();" VALUE="Reset Filter" <?php print $disabled;?>/></TD></TR>	<!-- 3/15/11 -->	
			</FORM></TABLE></DIV></TD>
		<?php }
	?>

</DIV>

<?php
	$the_width = get_variable('map_width');

	if ($show_tick_left) { 				// 11/27/09
		print "\n<BR>\n<DIV ID='the_ticket' STYLE='width: {$the_width}'>\n";	
		print do_ticket($row_ticket, $the_width, FALSE, FALSE); 
		print "\n</DIV>\n";		
		}
?>
		</TD>
		<TD VALIGN="top" ALIGN='center'>
			<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset; display: inline-block;'></DIV>
			<span id='toggle_dirs' class='plain' style='position: fixed; top: 0px; right: 0px; width: 100px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_div('directions', 'toggle_dirs', 'Directions')">Show Directions</span><BR />
			<span id='toggle_tkt' class='plain' style='position: fixed; top: 25px; right: 0px; width: 100px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_div('the_ticket', 'toggle_tkt', 'Ticket')">Show Ticket</span><BR />
			<span id='toggle_msgs' class='plain' style='position: fixed; top: 50px; right: 0px; width: 100px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_div('the_messages', 'toggle_msgs', 'Messages')">Show Messages</span><BR />
			<span id='toggle_dispatch' class='plain' style='position: fixed; top: 75px; right: 0px; width: 100px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_div('disp_details', 'toggle_dispatch', 'Disp details')">Show Disp Details</span><BR />
			<DIV ID="directions" STYLE="position: fixed; top: 125px; right: 0px; width: <?php print get_variable('map_width') * .35;?>px; height: <?php print get_variable('map_height');?>px; text-align: left; font-weight: bold; display: none; border: 2px outset #707070; overflow-y: auto; overflow-x: auto;"></DIV>
			<DIV ID="disp_details" STYLE="position: fixed; top: 125px; right: 0px; width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; text-align: left; font-weight: bold; display: none; border: 2px outset #707070; overflow-y: scroll;">
			<?php print do_ticket_extras($row_ticket, $the_width, FALSE, FALSE);?>
			</DIV>
			<DIV ID="the_messages" STYLE="position: fixed; top: 125px; right: 0px; width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; text-align: left; font-weight: bold; display: none; border: 2px outset #707070; overflow-y: scroll; overflow-x: hidden;">
			<?php print	do_ticket_messages($row_ticket, $the_width, FALSE, FALSE);?>
			</DIV>			
			<BR />
			<SPAN CLASS = "span_link" onClick ='toglGrid()'>Grid</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 3/15/11 -->
			<SPAN CLASS = "span_link" onClick ='doTraffic()'>Traffic</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 3/15/11 -->
			<SPAN CLASS = "span_link" onClick = "sv_win('<?php print $row_ticket['lat'];?>','<?php print $row_ticket['lng'];?>' );">Street view</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <!-- 8/17/09, 3/15/11 -->
			<SPAN CLASS = "warn" ID = "loading_2">Loading Directions, Please wait........</SPAN>
			<SPAN CLASS = "even" ID = "directions_ok_no">&nbsp;
			<SPAN CLASS = "other_1">Directions&nbsp;&raquo;</SPAN>
			<SPAN CLASS = "other_2">
<?php
		$checked_ok = ($_SESSION['allow_dirs'] =='true')? " CHECKED ": "";
		$checked_no = ($_SESSION['allow_dirs'] =='true')? "": " CHECKED ";
?>
				OK: <INPUT TYPE='radio' name='frm_dir' VALUE = true  <?php print $checked_ok; ?> onClick = "docheck(this.value);" />&nbsp;&nbsp;
				No: <INPUT TYPE='radio' name='frm_dir' VALUE = false <?php print $checked_no; ?> onClick = "docheck(this.value);" /></SPAN>
				&nbsp;</SPAN>
			<BR />
			<BR />
			<SPAN CLASS="legend" STYLE="text-align: center; vertical-align: middle;"><?php print get_text("Units");?> Legend:</SPAN><BR /><BR /><DIV CLASS="legend" ALIGN='center' VALIGN='middle' style='padding: 20px; text-align: center; vertical-align: middle; width: <?php print get_variable('map_width');?>px;'>	<!-- 3/15/11 -->
<?php
		print get_icon_legend ();
?>

			</DIV>	<!-- 3/15/11 -->
			<BR /><BR />
<?php
	if (!($show_tick_left)) {				// 11/27/09
		print "\n<DIV ID='the_ticket' STYLE=\"position: fixed; top: 125px; right: 0px; width: " . get_variable('map_width') . "px; height: " . get_variable('map_height') . "px; text-align: left; font-weight: bold; display: none; border: 2px outset #707070; overflow-y: scroll; overflow-x: hidden;\">\n";	
		print do_ticket_only($row_ticket, $the_width, FALSE, FALSE); 
		print "\n</DIV>\n";		
		}
?>
		</TD></TR></TABLE><!-- end outer -->
	<DIV ID='bottom' STYLE='display:none'>
	<CENTER>
	<H3>Dispatching ... please wait ...</H3><BR /><BR /><BR />
	</DIV>

	
	<FORM NAME='can_Form' ACTION="main.php" ><!-- 8/30/10 -->
	<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print get_ticket_id ();?>" />	
	</FORM>	

	<FORM NAME='routes_Form' METHOD='get' ACTION="<?php print basename( __FILE__); ?>"> <!-- 7/9/10 -->

	<INPUT TYPE='hidden' NAME='func' 			VALUE='do_db' />
	<INPUT TYPE='hidden' NAME='frm_ticket_id' 	VALUE='<?php print get_ticket_id (); ?>' />
	<INPUT TYPE='hidden' NAME='frm_by_id' 		VALUE= "<?php print $_SESSION['user_id'];?>" />
	<INPUT TYPE='hidden' NAME='frm_id_str' 		VALUE= "" />
	<INPUT TYPE='hidden' NAME='frm_name_str' 	VALUE= "" />
	<INPUT TYPE='hidden' NAME='frm_status_id' 	VALUE= "1" />
	<INPUT TYPE='hidden' NAME='frm_facility_id' 	VALUE= "<?php print $facility;?>" /> <!-- 10/6/09 -->
	<INPUT TYPE='hidden' NAME='frm_rec_facility_id' VALUE= "<?php print $rec_fac;?>" /> <!-- 10/6/09 -->
	<INPUT TYPE='hidden' NAME='frm_comments' 	VALUE= "New" />
	<INPUT TYPE='hidden' NAME='frm_allow_dirs' VALUE = <?php print $_SESSION['allow_dirs']; ?> />	<!-- 11/21/09 -->
		</FORM>
	<!-- 8/2/09 -->

	<DIV STYLE="position:fixed; width:60px; height:auto; top:<?php print $from_top;?>px; left:<?php print $from_left;?>px; background-color: transparent; text-align:left">	<!-- 5/17/09, 7/7/09 -->
		
<?php
			function get_addr(){				// returns incident address 11/27/09
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`= " . get_ticket_id () . " LIMIT 1";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_array($result));
				return "{$row['street']}<br />{$row['city']}<br /> {$row['state']}"; 
				}		// end function get_addr()

			$thefunc = (is_guest())? "guest()" : "validate()";		// disallow guest attempts
			$nr_units = 1;
			$addr = get_addr();
?>
		<div id='boxB' class='box' style='left:<?php print $from_left;?>px;top:<?php print $from_top;?>px; position:fixed;' > <!-- 9/23/10 -->
		<div class="bar" STYLE="width:12em; color:red; background-color : transparent; text-align: center "
			 onmousedown="dragStart(event, 'boxB')"><I>Drag me</I></div><!-- drag bar - 2/5/11 -->
		<div style = "margin-top:10px;">
		<IMG SRC="markers/down.png" BORDER=0  onclick = "location.href = '#page_bottom';" STYLE = 'margin-left:2px;' />		
		<IMG SRC="markers/up.png" BORDER=0  onclick = "location.href = '#page_top';" STYLE = 'margin-left:40px;'/><br />
		</div>
		 <div style = 'height:10px;'/>&nbsp;</div>
			 

<?php
			print "<SPAN ID='mail_button' STYLE='display: none'>";	//10/6/09
			print "<FORM NAME='email_form' METHOD = 'post' ACTION='do_direcs_mail.php' target='_blank' onsubmit='return mail_direcs(this);'>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_direcs' VALUE='' />";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_u_id' VALUE='' />";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_mail_subject' VALUE='Directions to Incident' />";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_scope' VALUE='' />"; // 10/29/09
			print "<INPUT TYPE='hidden' NAME='frm_tick_id' VALUE='" . get_ticket_id() . "' />"; // 3/29/2013	
			print "<INPUT TYPE='submit' value='Mail Direcs' ID = 'mail_dir_but' STYLE = 'visibility: hidden;' />";	//10/6/09
			print "</FORM>";	
			print "<INPUT TYPE='button' VALUE='Reset' onClick = 'show_butts(to_hidden) ; doReset()' ID = 'reset_but' STYLE = 'visibility: hidden;'  />";
			print "</SPAN>";			
			print "<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();'  ID = 'can_but'  STYLE = 'visibility: hidden;' />";
			if ($nr_units>0) {			
				print "<BR /><INPUT TYPE='button' value='DISPATCH\nUNITS' onClick = '" . $thefunc . "' ID = 'disp_but'  STYLE = 'visibility: hidden;' />\n";	// 6/14/09
				}
			print "<BR /><BR /><SPAN STYLE='display: 'inline-block' class='normal_text'><NOBR><H3>to:<BR /><I>{$addr}</I></H3></NOBR></SPAN>\n";
?>
		</div>	 <!-- end of outer -->
<?php
			print "<SPAN ID=\"loading\" STYLE=\"display: 'inline-block'\">";
			print "<TABLE BGCOLOR='red' WIDTH='80%'><TR><TD><FONT COLOR='white'><B>Loading Directions, Please wait........</B></FONT></TD></TR></TABLE>";		// 10/28/09
			print "</SPAN>";

?>
	</DIV>
<?php

		$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
		$regions_inuse = get_regions_inuse($user_level);	//	6/10/11
		$group = get_regions_inuse_numbers($user_level);	//	6/10/11		
		
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 4/13/11
		$result = mysql_query($query);	// 4/13/11
		$al_groups = array();
		$al_names = "";	
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{	// 4/13/11
			$al_groups[] = $row['group'];
			if(!(is_super())) {
				$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 4/13/11
				$result2 = mysql_query($query2);	// 4/13/11
				while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) 	{	// 4/13/11		
					$al_names .= $row2['group_name'] . ", ";
					}
				} else {
					$al_names = "ALL. Superadmin Level";
				}
			}
?>				
		<A NAME="page_bottom" /> <!-- 5/13/10 -->	
		<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
		<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print get_ticket_id (); ?>' />	<!-- 10/25/08 -->
		</FORM>
	</BODY>

<?php
			if ($addrs) {				// 10/21/08
?>			
<SCRIPT>
	function do_notify() {
//		alert(352);
		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "ATTENTION - New Ticket: ";
		var theId = '<?php print get_ticket_id ();?>';
		
//		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + escape(theId);		// ($to_str, $text, $ticket_id)   10/15/08
		var params = "frm_to="+ theAddresses + "&frm_text=" + theText + "&frm_ticket_id=" + theId ;		// ($to_str, $text, $ticket_id)   10/15/08
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()
	
	function handleResult(req) {				// the 'called-back' function  - ignore returned data
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
//				alert('HTTP error ' + req.status);
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
	
</SCRIPT>
<?php

			}		// end if($addrs) 
		else {
?>		
<SCRIPT>
	function do_notify() {
//		alert(414);
		return;
		}			// end function do notify()
</SCRIPT>
<?php		
//	print __LINE__;
			}
	$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
	$capabilities = (array_key_exists('capabilities', $_GET))? stripslashes(trim(str_replace('/', '|', $_GET['capabilities']))) : "" ;	// 11/18/10
	$searchtype = (array_key_exists('searchtype', $_GET))? $_GET['searchtype'] : "OR" ;	// 11/18/10
	print $capabilities;
	print do_list($unit_id, $capabilities, $searchtype);	// 11/18/10
	print "</HTML> \n";

	}			// end if/else !empty($---)

?>