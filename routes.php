<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

$GLOBALS['NM_LAT_VAL'] 		= 0.999999;												// 2/3/2013

$sortby_distance = TRUE;			// user: set to TRUE or FALSE to determine unit ordering

$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
@session_start();			// 1/7/10

require_once('./incs/functions.inc.php');
require_once($_SESSION['fmp']);		//8/25/10
$locale = get_variable('locale');
$routesUnits = (($locale == 0) || ($locale == 1)) ? 'imperial' : 'metric';
$sidebar_width = round( .5 * $_SESSION['scr_width']);		// pixels - 3/6/11

$from_top = 50;				// buttons alignment, user-reviseable as needed
$from_left =  intval(floor( 0.4 * $_SESSION['scr_width']));		// 5/22/11


$show_tick_left = FALSE;	// controls left-side vs. right-side appearance of incident details - 11/27/09
$distunits = "imperial";

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
12/13/2013 correction to drawCirle
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
$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;

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
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> 
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7"/> 
	
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
    <link rel="stylesheet" href="./js/leaflet/leaflet-routing-machine.css" />
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
    <STYLE TYPE="text/css">
		body 				{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
		table 				{border-collapse: collapse; }
		table.directions	{width: 100%;}
		table.directions th {background-color:#EEEEEE;}
		table.directions tr {background-color:#EEEEEE;}
		.stagelink {color: blue; text-decoration: underline; white-space: nowrap;}
		.stagedistance {color: black; font-weight: bold; white-space: nowrap;}		
		img 				{color: #000000;}
		span.even 			{background-color: #DEE3E7;}
		span.warn			{display:none; background-color: #FF0000; color: #FFFFFF; font-weight: bold; font-family: Verdana, Arial, sans serif; }

		span.mylink			{margin-right: 32PX; text-decoration:underline; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_1		{margin-right: 32PX; text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_2		{margin-right: 8PX;  text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	</STYLE>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT SRC="./js/json2.js"></SCRIPT>
	<SCRIPT>
	window.onresize=function(){set_size()};
	var viewportwidth;
	var viewportheight;
	var mapWidth;
	var mapHeight;
	var leftcolheight;
	var outerwidth;
	var outerheight;
	var colwidth;	
	var colheight;
	var distunits = "<?php print $distunits;?>";
	
	function set_size() {
		if (typeof window.innerWidth != 'undefined') {
			viewportwidth = window.innerWidth,
			viewportheight = window.innerHeight
			} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
			viewportwidth = document.documentElement.clientWidth,
			viewportheight = document.documentElement.clientHeight
			} else {
			viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
			viewportheight = document.getElementsByTagName('body')[0].clientHeight
			}
		set_fontsizes(viewportwidth, "fullscreen");
		mapWidth = viewportwidth * .40;
		mapHeight = viewportheight * .55;
		leftcolheight = viewportheight * .75;
		outerwidth = viewportwidth * .99;
		outerheight = viewportheight * .95;
		colwidth = outerwidth * .42;
		colheight = outerheight * .95;
		$('outer').style.width = outerwidth + "px";
		$('outer').style.height = outerheight + "px";
		$('leftcol').style.width = colwidth + "px";
		$('leftcol').style.height = colheight + "px";
		$('side_bar').style.width = colwidth + "px";
		$('side_bar').style.height = leftcolheight + "px";	
		$('rightcol').style.width = colwidth + "px";
		$('rightcol').style.height = colheight + "px";	
		$('map_canvas').style.width = mapWidth + "px";
		$('map_canvas').style.height = mapHeight + "px";
		$('map_caption').style.width = mapWidth + "px";
		$('bottom').style.width = mapWidth + "px";
		$('loading').style.width = mapWidth + "px";
		$('legend').style.width = mapWidth + "px";
		$('inc_addr').style.width = mapWidth + "px";
		}
	
	try {	
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
		
	var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;

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
		
	var routesUnits = '<?php print $routesUnits;?>';

	String.prototype.trim = function () {									// added 6/10/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function add_hash(in_str) { // prepend # if absent
		return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
		}			
			
	var to_visible = "inline-block";
	var to_hidden = "none";
	function show_butts(strValue) {								// 3/15/11
		$('mail_dir_but').style.display = strValue;
		$('reset_but').style.display = strValue;
		$('can_but').style.display = strValue;
		if ($('disp_but')) {$('disp_but').style.display = strValue;}
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
		
							//	Automatic Status Update by Dispatch Status
		$use_status_update = get_variable('use_disp_autostat');		//	9/10/13
		if($use_status_update == "1") {		//	9/10/13
			auto_disp_status(1, $assigns[$i]);
			}
		
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
	$mail_str = (empty($addr_str) && empty($smsg_add_str))? "" :  "do_mail_win('{$addr_str}', '{$smsg_add_str}', '{$_REQUEST['frm_ticket_id']}');";
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
	<FORM NAME='more_form' METHOD = 'get' ACTION = "<?php print basename(__FILE__); ?>" style="display: inline;">
	<SPAN ID='more_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.more_form.submit();'><SPAN STYLE='float: left;'><?php print get_text("More");?></SPAN><IMG STYLE='float: right;' SRC='./images/more_small.png' BORDER=0></SPAN>
	<INPUT TYPE = 'hidden' NAME = 'ticket_id' VALUE="<?php print get_ticket_id ();?>">
	</FORM>
	<FORM NAME='cont_form' METHOD = 'get' ACTION = "main.php" STYLE = 'margin-left:20px; display: inline;'>
	<SPAN ID='fin_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.cont_form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
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
?>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT SRC="./js/json2.js"></SCRIPT>
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/leaflet/leaflet-routing-machine.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/osopenspace.js"></script>
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
<?php
	if ($_SESSION['internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
			if($https) {
?>
				<script src="https://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php
				} else {
?>
				<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php				
				}
			}
		}
?>
	<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
	<script type="application/x-javascript" src="./js/usng.js"></script>
	<script type="application/x-javascript" src="./js/osgb.js"></script>
	<script type="application/x-javascript" src="./js/geotools2.js"></script>
<?php
	require_once('./incs/all_forms_js_variables.inc.php');
?>
	<SCRIPT>
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
		`problemstart` AS `problemstart`,
		`problemend` AS `problemend`,
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

	if ($rec_fac > 0) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`=" . $rec_fac . "";			// 10/6/09
		$result_rfc = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_rec_fac = stripslashes_deep(mysql_fetch_array($result_rfc));
		$rf_lat = $row_rec_fac['lat'];
		$rf_lng = $row_rec_fac['lng'];
		$rf_name = $row_rec_fac['name'];		
		
		unset ($result_rfc);
		}
	
	function get_addr(){				// returns incident address 11/27/09
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`= " . get_ticket_id () . " LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_array($result));
		return "{$row['street']} {$row['city']} {$row['state']}"; 
		}		// end function get_addr()

	$addr = get_addr();

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
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>		<!-- 3/4/11 -->
<A NAME='page_top' />
<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
	<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
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
		<DIV ID='side_bar' style="border: 1px outset #CECECE; overflow-y: auto; overflow-x: hidden;"></DIV><!-- 5/12/10 -->
<?php
			$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
			if($unit_id=="") { 	// 11/18/10
?>
				<DIV ID='theform' style='position: relative; top: 10px; background-color: transparent; border-color: #000000;'>
					<FORM NAME='filter_Form' METHOD="GET" ACTION="routes.php">						
					<TABLE ALIGN='center' BORDER='0'>
						<TR CLASS='even'>
							<TD COLSPAN=99 CLASS='td_data text text_center'>
								<B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B>
								</FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>
								&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>
								&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT>
							</TD>
						</TR>
						<TR>
							<TD COLSPAN=99>&nbsp;</TD>
						</TR>
						<TR class='heading'>
							<TH class='heading text_big'>FILTER BY CAPABILITIES</TH>
						</TR>
						<TR class='odd'>
							<TD CLASS='td_data text text_center'>Filter Type: <b>OR </b><INPUT TYPE='radio' NAME='searchtype' VALUE='OR' checked><b>AND </b><INPUT TYPE='radio' NAME='searchtype' VALUE='AND'></TD>
						</TR>
						<TR class='even'>
							<TD CLASS='td_data text text_center'>
								<INPUT SIZE='48' TYPE='text' NAME='capabilities' VALUE='<?php print $capabilities;?>' MAXLENGTH='64' />
							</TD>
						</TR>
						<TR>
							<TD COLSPAN=99>&nbsp;</TD>
						</TR>
						<TR>
							<TD align="center" style='height: 30px;'>
								<SPAN ID='filter_button' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='filterSubmit();'><SPAN STYLE='float: left;'><?php print get_text("Filter");?></SPAN><IMG STYLE='float: right;' SRC='./images/filter_small.png' BORDER=0></SPAN>
								<SPAN ID='filter_reset' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='filterReset();'><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
							</TD>
						</TR>
					</TABLE>
					<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print get_ticket_id (); ?>' />
					<INPUT TYPE='hidden' NAME='unit_id' 	VALUE='<?php print $unit_id; ?>' />
					</FORM>
				</DIV>
<?php 
				}
?>

	</DIV>
	<DIV ID="middle_col" style='position: relative; left: 20px; float: left; width: 100px; text-align: center; padding: 10px;'>
		<DIV ID='buttons_outer' style='position: relative; top: 100px;'>
<?php

?>
			<SPAN ID='mail_dir_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: none;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.email_form.submit();'><?php print get_text("Mail Dir");?><BR /><IMG SRC='./images/send.png' BORDER=0></SPAN>
			<SPAN ID='reset_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: none;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='show_butts(to_hidden) ; doReset();'><?php print get_text("Reset");?><BR /><IMG SRC='./images/restore.png' BORDER=0></SPAN>
			<SPAN ID='can_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG SRC='./images/cancel.png' BORDER=0></SPAN>
<?php
			$thefunc = (is_guest())? "guest()" : "validate()";		// disallow guest attempts
			$nr_units = 1;
			if ($nr_units>0) {
?>
				<SPAN ID='disp_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: none;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='<?php print $thefunc;?>;'><?php print get_text("DISPATCH UNITS");?><BR /><IMG SRC='./images/dispatch.png' BORDER=0></SPAN>
<?php
				}
?>
			<FORM NAME='email_form' METHOD = 'post' ACTION='do_direcs_mail.php' target='_blank' onsubmit='return mail_direcs(this);'>
				<INPUT TYPE='hidden' NAME='frm_u_id' VALUE='' />
				<INPUT TYPE='hidden' NAME='frm_direcs' VALUE='' />
				<INPUT TYPE='hidden' NAME='frm_mail_subject' VALUE='Directions to Incident' />
				<INPUT TYPE='hidden' NAME='frm_scope' VALUE='' />
				<INPUT TYPE='hidden' NAME='frm_tick_id' VALUE='<?php print get_ticket_id();?>' />
			</FORM>
		</DIV>
	</DIV>
	<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
<?php
	$the_width = get_variable('map_width');

	if ($show_tick_left) { 				// 11/27/09
		print "\n<BR>\n<DIV ID='the_ticket' STYLE='width: {$the_width}'>\n";	
		print do_ticket($row_ticket, $the_width, FALSE, FALSE); 
		print "\n</DIV>\n";		
		}
		

?>
		<DIV id = 'map_canvas' style = 'border: 1px outset #707070;'></DIV>
		<SPAN id='map_caption' class='text_center bold text_big' style='display: inline-block;'><?php print get_variable('map_caption');?></SPAN>
		<BR />
		<BR />
		<DIV ID='legend' CLASS="legend" STYLE="text-align: center; vertical-align: middle;">
			<SPAN CLASS='header text_big'><?php print get_text("Units");?> Legend:</SPAN><BR />
<?php
			print get_icon_legend ();
?>

		</DIV>
		<BR />
		<BR />
		<SPAN id='inc_addr' class='text_center bold text_big' style='display: inline-block;'>Dispatching to: <I><?php print $addr;?></I></SPAN><BR /><BR />
		<SPAN ID="loading" STYLE="display: 'inline-block'; text-align: center;">
			<TABLE BGCOLOR='red' WIDTH='100%'>
				<TR>
					<TD><FONT COLOR='white'><B>Loading Directions, Please wait........</B></FONT></TD>
				</TR>
			</TABLE>
		</SPAN>
		<DIV ID='bottom' STYLE='display:none'>
			<H3>Dispatching ... please wait ...</H3>
		</DIV>		
		<span id='toggle_dirs' class='plain text' style='position: fixed; top: 0px; right: 0px; width: 100px; z-index: 9998; height: 30px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_div('directions_outer', 'toggle_dirs', 'Directions')">Show Directions</span><BR />
		<span id='toggle_tkt' class='plain text' style='position: fixed; top: 40px; right: 0px; width: 100px; z-index: 9998; height: 30px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_div('the_ticket', 'toggle_tkt', 'Ticket')">Show Ticket</span><BR />
		<span id='toggle_msgs' class='plain text' style='position: fixed; top: 80px; right: 0px; width: 100px; z-index: 9998; height: 30px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_div('the_messages', 'toggle_msgs', 'Messages')">Show Messages</span><BR />
		<span id='toggle_dispatch' class='plain text' style='position: fixed; top: 120px; right: 0px; width: 100px; z-index: 9998; height: 30px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_div('disp_details', 'toggle_dispatch', 'Disp details')">Show Disp Details</span><BR />
		<DIV id='directions_outer' class='even' STYLE="position: fixed; top: 165px; right: 0px; text-align: left; font-weight: bold; display: none; border: 2px outset #707070; padding: 20px; z-index: 9999;">
			<SPAN class='heading' style='height: 60px; width: 100%; display: block; font-size: 12px;'>Click stage to show on map, Click title to show alternative route</SPAN><BR />
			<DIV ID="directions" style='width:100%; height: 80%; overflow-y: auto; overflow-x: hidden; padding: 10px;'>No Directions Available</DIV>
		</DIV>
		<DIV ID="disp_details" STYLE="position: fixed; top: 165px; right: 0px; width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; text-align: left; font-weight: bold; display: none; border: 2px outset #707070; overflow-y: scroll; z-index: 9999;">
			<?php print do_ticket_extras($row_ticket, $the_width, FALSE, FALSE);?>
		</DIV>
		<DIV ID="the_messages" STYLE="position: fixed; top: 165px; right: 0px; width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; text-align: left; font-weight: bold; display: none; border: 2px outset #707070; overflow-y: scroll; overflow-x: hidden; z-index: 9999;">
			<?php print	do_ticket_messages($row_ticket, $the_width, FALSE, FALSE);?>
		</DIV>	

<?php
		if (!($show_tick_left)) {				// 11/27/09
			print "\n<DIV ID='the_ticket' STYLE=\"position: fixed; top: 165px; right: 0px; width: " . get_variable('map_width') . "px; height: " . get_variable('map_height') . "px; text-align: left; font-weight: bold; display: none; border: 2px outset #707070; overflow-y: scroll; overflow-x: hidden; z-index: 9999;\">\n";	
			print do_ticket_only($row_ticket, $the_width, FALSE, FALSE); 
			print "\n</DIV>\n";		
			}
?>
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
</DIV>		<!-- End of outer -->
<?php
$user_level = is_super() ? 9999 : $_SESSION['user_id']; 
$regions_inuse = get_regions_inuse($user_level);	//	6/10/11
$group = get_regions_inuse_numbers($user_level);	//	6/10/11		

$al_groups = $_SESSION['user_groups'];
?>	
<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="routes.php">
<INPUT TYPE='hidden' NAME='ticket_id' VALUE='<?php print get_ticket_id (); ?>' />
</FORM>			
<A NAME="page_bottom" />	
<SCRIPT>
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .55;
	leftcolheight = viewportheight * .75;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";
	$('side_bar').style.width = colwidth + "px";
	$('side_bar').style.height = leftcolheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('map_caption').style.width = mapWidth + "px";
	$('loading').style.width = mapWidth + "px";
	$('bottom').style.width = mapWidth + "px";
	$('legend').style.width = mapWidth + "px";
	$('inc_addr').style.width = mapWidth + "px";
	set_fontsizes(viewportwidth, "fullscreen");
	var map;				// make globally visible
	var minimap;
	var thelevel = '<?php print $the_level;?>';
	var tmarkers =  new Array();	//	Incident markers array
	var rmarkers = new Array();			//	Responder Markers array
	var fmarkers = new Array();			//	Facility markers array
	var cmarkers = new Array();			//	conditions markers array
	var rss_markers = new Array();		//	RSS markers array

	var latLng;
	var in_local_bool = "0";
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = <?php print get_variable('use_osmap');?>;
	init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 13, theLocale, useOSMAP, "br");
	map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 13);
	var bounds = map.getBounds();	
	var zoom = map.getZoom();
	var got_points = false;	// map is empty of points
</SCRIPT>
		
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