<?php
/*
10/28/07 added onLoad = "document.add.frm_lat.disabled..
11/38/07 added frame jump prevention
11/98/07 added map under image
5/29/08  added do_kml() call
8/11/08	 added problem-start lock/unlock
8/23/08	 added usng handling 
8/23/08  corrected problem-end hskpng
9/9/08	 added lat/lng-to-CG format functions
10/4/08	 added function do_inc_name() 
10/7/08	 set WRAP="virtual"
10/8/08 synopsis made non-mandatory
10/15/08 changed 'Comments' to 'Disposition'
10/16/08 changed ticket_id to frm_ticket_id
10/17/08 removed 10/16/08 change
10/19/08 added insert_id to description
12/6/08 allow user input of NGS values; common icon marker function
1/11/09 TBD as default, auto_route setting option
1/17/09 replaced ajax functions - for consistency
1/18/09 added script-specific CONSTANTS
1/19/09 added geocode function
1/21/09 show/hide butts
1/22/09 - serial no. to ticket description
1/25/09 serial no. pre-set
1/27/09 area code vaiable added
2/4/09  added function get_add_id()
2/10/09 added function sv_win() 
2/11/09 added dollar function, streetview functions
3/3/09 cleaned trash as page bottom
3/10/09 intrusive space in ticket_id
4/30/09 $ replaces document.getElementById, USNG text underline
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
if($istest) {dump($_GET);}
if($istest) {dump($_POST);}
$api_key = get_variable('gmaps_api_key');

function get_add_id() {				// 2/4/09
	$query  = "SELECT `id`, `contact` FROM `$GLOBALS[mysql_prefix]ticket` 
		WHERE `status`= '" . $GLOBALS['STATUS_RESERVED'] . "' AND  `contact` = " . quote_smart($_SERVER['REMOTE_ADDR']) . " LIMIT 1";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	if (mysql_affected_rows() > 0) {
		$row = stripslashes_deep(mysql_fetch_array($result));
		unset($result);
		return $row['id'];				// return it
		}

	else {
		$query  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
				`id` , `in_types_id` , `contact` , `street` , `city` , `state` , `phone` , `lat` , `lng` , `date` ,
				`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` , `severity` , `updated` 
			) VALUES (
				NULL , '', " . quote_smart($_SERVER['REMOTE_ADDR']) . "	, NULL , NULL , NULL , NULL , NULL , NULL , NULL , 
				NULL , NULL , '', NULL , '', NULL , '" . $GLOBALS['STATUS_RESERVED'] . "', '0', '0', NULL
			)";
			
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		return mysql_insert_id();
		}
	}

$get_add = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['add'])))) ) ? "" : $_GET['add'] ;

	if ($get_add == 'true')	{

		function updt_ticket($id) {							/* 1/25/09 */
			global $addrs, $NOTIFY_TICKET;
	
			$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;
			$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;
	
			$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
			$post_frm_affected 	 		= strip_html($post_frm_affected);
			$_POST['frm_scope']			= strip_html($_POST['frm_scope']);
	
			if (!get_variable('military_time'))	{		//put together date from the dropdown box and textbox values
				if ($post_frm_meridiem_problemstart == 'pm'){
					$post_frm_meridiem_problemstart	= ($post_frm_meridiem_problemstart + 12) % 24;
					}
				}
			if(empty($post_frm_owner)) {$post_frm_owner=0;}
			$frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";
	
	
			if (!get_variable('military_time'))	{			//put together date from the dropdown box and textbox values
				if ($post_frm_meridiem_problemstart == 'pm'){
					$_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
					}
				if (isset($_POST['frm_meridiem_problemend'])) {
					if ($_POST['frm_meridiem_problemend'] == 'pm'){
						$_POST['frm_hour_problemend'] = ($_POST['frm_hour_problemend'] + 12) % 24;
						}
					}
				}
			$frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  quote_smart("$_POST[frm_year_problemend]-$_POST[frm_month_problemend]-$_POST[frm_day_problemend] $_POST[frm_hour_problemend]:$_POST[frm_minute_problemend]:00") : "NULL";
			
			$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
			if(empty($post_frm_owner)) {$post_frm_owner=0;}

			switch (get_variable('serial_no_ap')) {									// 1/22/09
			
				case 0:								/*  no serial no. */
				    $name_rev = $_POST['frm_scope'];
				    break;
				case 1:								/*  prepend  */
					$name_rev =  $id . "/" . $_POST['frm_scope'];
				    break;
				case 2:								/*  append  */
				    $name_rev = $_POST['frm_scope'] . "/" .  $id;
				    break;
				default:							/* error????  */
				    $name_rev = " error  error  error ";
				}
																						// 8/23/08, 9/20/08
			// perform db update
			$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET 
			`contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
			`street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
			`city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
			`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
			`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
			`lat`= " . 			quote_smart(trim($_POST['frm_lat'])) . ",
			`lng`= " . 			quote_smart(trim($_POST['frm_lng'])) . ",
			`scope`= " . 		quote_smart(trim($name_rev)) . ",
			`owner`= " . 		quote_smart(trim($post_frm_owner)) . ",
			`severity`= " . 	quote_smart(trim($_POST['frm_severity'])) . ",
			`in_types_id`= " . 	quote_smart(trim($_POST['frm_in_types_id'])) . ",
			`status`=" . 		quote_smart(trim($_POST['frm_status'])) . ",
			`problemstart`=".	quote_smart(trim($frm_problemstart)) . ",
			`problemend`=".		$frm_problemend . ",
			`description`= " .	quote_smart(trim($_POST['frm_description'])) .",
			`comments`= " . 	quote_smart(trim($_POST['frm_comments'])) .",
			`updated`='$now'
			WHERE ID='$id'";
	
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	
			do_log($GLOBALS['LOG_INCIDENT_OPEN'], $id);
	

//			$where = ($_POST['frm_severity']> $GLOBALS['SEVERITY_NORMAL'] )? "" : " WHERE `severities` = 3";	// 2/22/09
//			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` $where";
//			$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//			if (mysql_affected_rows()>0) {			// do mail
//			
//				}
//		
			return $name_rev;
			}				// end function edit ticket() 
			
		$ticket_name = updt_ticket(trim($_POST['ticket_id']));				// 1/25/09
?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<HEAD><TITLE>Tickets - Add Module</TITLE>
			<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
			<META HTTP-EQUIV="Expires" CONTENT="0">
			<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
			<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
			<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
			<META HTTP-EQUIV="Script-date" CONTENT="9/13/08">
			<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
		<SCRIPT>
<?php
		$addrs = notify_user($_POST['ticket_id'],$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array of adddr's for notification, or FALSE
		if ($addrs) {				// any addresses?
//		snap(basename( __FILE__) . __LINE__, count($addrs));
?>	
	function do_notify() {

//		alert(178);
		
		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "TICKET - New: ";
		var theId = '<?php print $_POST['ticket_id'];?>';
		
//		mail_it ($to_str, $text, $theId, $text_sel=1;, $txt_only = FALSE)

		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + theId + "&text_sel=1";		// ($to_str, $text, $ticket_id)   10/15/08
		
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()
	
	function handleResult(req) {				// the 'called-back' function
<?php
	if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
		}

	function sendRequest(url,callback,postData) {
//		alert(165);
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
<?php
		}				// end if ($addrs)
	else {
?>
	function do_notify() {	// dummy
		return;
		}
<?php
	
		}				// end if/else ($addrs)

	$form_name = (get_variable('auto_route')==0)? "to_main" : "to_routes";	
?>

		</SCRIPT>
		</HEAD>
	<BODY onLoad = "do_notify();document.<?php print $form_name;?>.submit();">
<?php
	$now = time() - (get_variable('delta_mins')*60);
	
	print "<BR /><BR /><BR /><CENTER><FONT CLASS=\"header\">Ticket: '".$ticket_name  ."' Added by '$my_session[user_name]' at " . date(get_variable("date_format"),$now) . "</FONT></CENTER><BR /><BR />";
?>	
	<FORM NAME='to_main' METHOD='post' ACTION='main.php'>
	<CENTER><INPUT TYPE='submit' VALUE='Main' />
	</FORM>

	<FORM NAME='to_routes' METHOD='get' ACTION='routes.php'>
	<INPUT TYPE='hidden' NAME='ticket_id' VALUE='<?php print $_POST['ticket_id'];?>' />
	<INPUT TYPE='submit' VALUE='Routes' /></CENTER>
	</FORM>
<?php
//			list_tickets();
		}				// end if ($_GET['add'] ...
// ========================================================================================		
	else {
		if (is_guest() && !get_variable('guest_add_ticket')) {
			print '<FONT CLASS="warn">Guest users may not add tickets on this system.  Contact administrator for further information.</FONT>';
			exit();
			}
//	$query  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` ( `id` , `in_types_id` , `contact` , `street` , `city` , `state` , `phone` , `lat` , `lng` , `date` ,
//				`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` , `severity` , `updated` )
//				VALUES (NULL , '', '', NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , '', NULL , '', NULL , '0', '0', '0', NULL);";
//		
//	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//	$ticket_id = mysql_insert_id();

	$ticket_id = get_add_id();				// 2/4/09
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Add Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<SCRIPT SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT SRC="./js/graticule.js" type="text/javascript"></SCRIPT>
<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>

<SCRIPT>
	function ck_frames() {		// onLoad = "ck_frames()"
//		if(self.location.href==parent.location.href) {
//			self.location.href = 'index.php';
//			}
		}		// end function ck_frames()

	parent.frames["upper"].$("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
	parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
	parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08		

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
	function isNullOrEmpty(str) {
		if (null == str || "" == str) {return true;} else { return false;}
		}
	var starting = false;

	function sv_win(theForm) {				// 2/11/09
		if(starting) {return;}				// dbl-click proof
		starting = true;					

		var thelat = theForm.frm_lat.value;
		var thelng = theForm.frm_lng.value;
		var url = "street_view.php?thelat=" + thelat + "&thelng=" + thelng;
		newwindow_sl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (!(newwindow_sl)) {
			alert ("Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_sl.focus();
		starting = false;
		}		// end function sv win()


	function isNullOrEmpty(str) {
		if (null == str || "" == str) {return true;} else { return false;}
		}
	
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
	
	function chknum(val) { 
		return ((val.trim().replace(/\D/g, "")==val.trim()) && (val.trim().length>0));}
	
	function chkval(val, lo, hi) { 
		return  (chknum(val) && !((val> hi) || (val < lo)));}
	
	
	function do_coords(inlat, inlng) { 										 //9/14/08
		if((inlat.length==0)||(inlng.length==0)) {return;}
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);		
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(Math.abs(inval));
		d  = Math.floor(d);
		var mi = (Math.abs(inval)-d)*60;	// fraction * 60
		var m = Math.floor(mi)				// min's as fraction
		var si = (mi-m)*60;					// to sec's
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	function lat2ddm(inlat) {				//  lat to degr, dec.min's - 9/9/089/7/08
		var x = new Number(Math.abs(inlat));
		var degs  = Math.floor(x);				// degrees
		var mins = ((Math.abs(x-degs)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return degs + '\260'  + mins +"'" + nors;
		}
	
	function lng2ddm(inlng) {				//  lng to degr, dec.min's - 9/9/089/7/08
		var x = new Number(Math.abs(inlng));
		var degs  = Math.floor(x);				// degrees
		var mins = ((Math.abs(x-degs)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return degs + '\260' + mins +"'" + eorw;
		}

	function do_lat_fmt(inlat) {				// 9/9/08
		switch(lat_lng_frmt) {
			case 0:
				return inlat;
			  	break;
			case 1:
				return ll2dms(inlat);
			  	break;
			case 2:
				return lat2ddm(inlat);
			 	break;
			default:
				alert ( "error 219");
			}	
		}

	function do_lng_fmt(inlng) {
		switch(lat_lng_frmt) {
			case 0:
				return inlng;
			  	break;
			case 1:
				return ll2dms(inlng);
			  	break;
			case 2:
				return lng2ddm(inlng);
			 	break;
			default:
				alert ("error 235");
			}	
		}

	var map;						// note globals
	var geocoder = null;
	geocoder = new GClientGeocoder();
	var request;
	var querySting;   				// will hold the POSTed data
	var tab1contents				// info window contents - first/only tab
	var grid = false;				// toggle
	var thePoint;
	var baseIcon;
	var cross;
	
	function writeConsole(content) {
		top.consoleRef=window.open('','myconsole',
			'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=1' +',resizable=1')
	 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
			+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></html>'
			)				// end top.consoleRef.document.writeln()
	 	top.consoleRef.document.close();
		}				// end function writeConsole(content)
	
	function getRes() {
		return window.screen.width + ' x ' + window.screen.height;
		}

	function toglGrid() {						// toggle
		grid = !grid;
		if (!grid) {							// check prior value
			map.clearOverlays();
			}
		else {
			map.closeInfoWindow();
			map.addOverlay(new LatLonGraticule());
			}
		if (thePoint) {map.addOverlay(new GMarker(thePoint));}	// restore it
		}		// end function toglGrid()

	function clearmap(){
		map.clearOverlays();
		load(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>, <?php echo get_variable('def_zoom'); ?>);
		if (grid) {map.addOverlay(new LatLonGraticule());}
		}
	
	function do_marker(lat, lng, zoom) {		// 9/16/08 - 12/6/08
		map.clearOverlays();
		var center = isNullOrEmpty(lat)?  GLatLng(map.getCenter()) : new GLatLng(lat, lng);
		var myzoom = isNullOrEmpty(zoom)? map.getZoom(): zoom;
		map.setCenter(center, myzoom);
		thisMarker  = new GMarker(center, {icon: cross});				// 9/16/08
		map.addOverlay(thisMarker);
		}
		

	function domap() {										// called from phone, addr lookups
		map = new GMap2($('map'));
		$("map").style.backgroundImage = "url(./markers/loading.jpg)";
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
<?php print (get_variable('terrain') == 1)? "\t\tmap.addMapType(G_PHYSICAL_MAP);\n" : "";?>
		map.setCenter(new GLatLng(document.add.frm_lat.value, document.add.frm_lng.value), 13);			// larger # => tighter zoom
		map.addControl(new GOverviewMapControl());
		map.enableScrollWheelZoom();
		do_marker(null, null, null)	;		// 12/6/08
		
		var sep = (document.add.frm_street.value=="")? "": ", ";
		var tab1contents = "<B>" + document.add.frm_contact.value + "</B>" +
			"<BR/>"+document.add.frm_street.value + sep +
			document.add.frm_city.value +" " +
			document.add.frm_state.value;
	
		
		GEvent.addListener(map, "click", function(marker, point) {		// lookup
//			alert(349);
			if (marker) {
				map.removeOverlay(marker);
//				document.add.frm_lat.disabled=document.add.frm_lat.disabled=false;
				document.add.frm_lat.value=document.add.frm_lng.value="";
//				document.add.frm_lat.disabled=document.add.frm_lat.disabled=true;
				if (grid) {map.addOverlay(new LatLonGraticule());}
				}
			
			if (point) {
				map.clearOverlays();
				do_lat (point.lat())				// display
				do_lng (point.lng())	
				do_ngs(document.add);
				map.addOverlay(new GMarker(point));	// GLatLng.
				map.openInfoWindowHtml(point,tab1contents);
				if (grid) {map.addOverlay(new LatLonGraticule());}
				}
				
			});				// end GEvent.addListener()
		if (grid) {map.addOverlay(new LatLonGraticule());}
		$("lock_p").style.visibility = "visible";		
		}				// end function do map()
	
	function load(the_lat, the_lng, the_zoom) {				// onLoad function - 4/28/09
		if (GBrowserIsCompatible()) {
			function drawCircle(lng,lat,radius) { 			// drawCircle(-87.628092,41.881906,2);
				var cColor = "#3366ff";
				var cWidth = 2;
				var Cradius = radius;
				var d2r = Math.PI/180;
				var r2d = 180/Math.PI;
				var Clat = (Cradius/3963)*r2d;
				var Clng = Clat/Math.cos(lat*d2r);
				var Cpoints = [];
				for (var i=0; i < 33; i++) {
					var theta = Math.PI * (i/16);
					Cx = lng + (Clng * Math.cos(theta));
					Cy = lat + (Clat * Math.sin(theta));
					var P = new GPoint(Cx,Cy);				// note long, lat order
					Cpoints.push(P);
					};
				map.addOverlay(new GPolyline(Cpoints,cColor,cWidth));
				}
			map = new GMap2($('map'));
			map.addControl(new GSmallMapControl());
			map.addControl(new GMapTypeControl());
<?php print (get_variable('terrain') == 1)? "\t\tmap.addMapType(G_PHYSICAL_MAP);\n" : "";?>
			baseIcon = new GIcon();				// 
			baseIcon.iconSize=new GSize(32,32);
			baseIcon.iconAnchor=new GPoint(16,16);
			cross = new GIcon(baseIcon, "./markers/crosshair.png", null);	

			do_marker(the_lat, the_lng, the_zoom);		// 12/6/08
		
			GEvent.addListener(map, "click", function(marker, point) {
				if (marker) {									// undo it
					map.removeOverlay(marker);
					thePoint = "";
					document.add.frm_lat.value=document.add.frm_lng.value="";
					if (grid) {map.addOverlay(new LatLonGraticule());}
					}
				if (point) {
					$("do_sv").style.display = "block";
					map.clearOverlays();
					do_lat (point.lat().toFixed(6))				// display
					do_lng (point.lng().toFixed(6))
					do_ngs(document.add);
					do_marker(point.lat(), point.lng(), null);		// 12/6/08
	
					thePoint = point;
					if (grid) {map.addOverlay(new LatLonGraticule());}
					}
				});
	 			document.add.show_lat.disabled=document.add.show_lng.disabled=true;
<?php
			do_kml();
?>		
			}			// end if (GBrowserIsCompatible())
		}			// end function load()

	function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
														// NOT correspond with what browsers actually do...
		var SAFECHARS = "0123456789" +					// Numeric
						"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
						"abcdefghijklmnopqrstuvwxyz" +	// guess
						"-_.!~*'()";					// RFC2396 Mark characters
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
	
	function URLDecode(encoded ){   					// Replace + with ' '
	   var HEXCHARS = "0123456789ABCDEFabcdef";  		// Replace %xx with equivalent character
	   var plaintext = "";   							// Place [ERROR] in output if %xx is invalid.
	   var i = 0;
	   while (i < encoded.length) {
	       var ch = encoded.charAt(i);
		   if (ch == "+") {
		       plaintext += " ";
			   i++;
		   } else if (ch == "%") {
				if (i < (encoded.length-2)
						&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1
						&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
					plaintext += unescape( encoded.substr(i,3) );
					i += 3;
				} else {
					alert( '-- invalid escape combination near ...' + encoded.substr(i) );
					plaintext += "%[ERROR]";
					i++;
				}
			} else {
				plaintext += ch;
				i++;
				}
		} 				// end  while (...)
		return plaintext;
		};				// end function URLDecode()
	
	function do_lat (lat) {
		document.add.frm_lat.value=lat;			// 9/9/08
		document.add.show_lat.disabled=false;				// permit read/write
		document.add.show_lat.value=do_lat_fmt(document.add.frm_lat.value);
		document.add.show_lat.disabled=true;
		}
	function do_lng (lng) {
		document.add.frm_lng.value=lng;
		document.add.show_lng.disabled=false;
		document.add.show_lng.value=do_lng_fmt(document.add.frm_lng.value);
		document.add.show_lng.disabled=true;
		}

	function do_ngs(theForm) {								// 8/23/08
		theForm.frm_ngs.disabled=false;						// 9/9/08
		theForm.frm_ngs.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);
		theForm.frm_ngs.disabled=true;
		}
// *********************************************************************
	var the_form;
	function sendRequest(my_form, url,callback,postData) {		// ajax function set - 1/17/09
		the_form = my_form;
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
				alert('HTTP error ' + req.status);
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

	function handleResult(req) {			// the called-back function
		if (req.responseText.substring(0,1)=="-") {
			alert("lookup failed");
			}
		else {
			var result=req.responseText.split(";");					// good return - now parse the puppy
// "Juan Wzzzzz;(123) 456-9876;1689 Abcd St;Abcdefghi;MD;16701;99.013297;-88.544775;"
//  0           1              2            3         4  5     6         7
			the_form.frm_contact.value=result[0].trim();
			the_form.frm_phone.value=result[1].trim();		// phone
			the_form.frm_street.value=result[2].trim();		// street
			the_form.frm_city.value=result[3].trim();		// city
			the_form.frm_state.value=result[4].trim();		// state 
//			the_form.frm_zip.value=result[5].trim();		// frm_zip - unused

			pt_to_map (the_form, result[6].trim(), result[7].trim());				// 1/19/09
			$("do_sv").style.display = "block";										// 2/11/09
			
			}		// end else ...			
		}		// end function handleResult()
	
	function phone_lkup(){	
		var goodno = document.add.frm_phone.value.replace(/\D/g, "" );		// strip all non-digits - 1/18/09
		if (goodno.length<10) {
			alert("10-digit phone no. required - any format");
			return;}
		var params = "phone=" + URLEncode(goodno)
		sendRequest (document.add, 'wp_lkup.php',handleResult, params);		//1/17/09
		}
		
// *********************************************************************
		function pt_to_map (my_form, lat, lng) {				// 1/19/09
			my_form.frm_lat.value=lat;	
			my_form.frm_lng.value=lng;		
			
			my_form.show_lat.value=do_lat_fmt(my_form.frm_lat.value);
			my_form.show_lng.value=do_lng_fmt(my_form.frm_lng.value);
			
			my_form.frm_ngs.value=LLtoUSNG(my_form.frm_lat.value, my_form.frm_lng.value, 5);
		
			map.setCenter(new GLatLng(my_form.frm_lat.value, my_form.frm_lng.value), <?php print get_variable('def_zoom');?>);
			var marker = new GMarker(map.getCenter());		// marker to map center
			var myIcon = new GIcon();
			myIcon.image = "./markers/sm_red.png";
			map.removeOverlay(marker);
			
			map.addOverlay(marker, myIcon);
			}				// end function pt_to_map ()
		

// *********************************************************************
	function loc_lkup(my_form) {		   // added 1/19/09 -- getLocations(address,  callback -- not currently used )
		if ((my_form.frm_city.value.trim()==""  || my_form.frm_state.value.trim()=="")) {
			alert ("City and State are required for location lookup.");
			return false;
			}
		var geocoder = new GClientGeocoder();
//				"1521 1st Ave, Seattle, WA"		
		var address = my_form.frm_street.value.trim() + ", " +my_form.frm_city.value.trim() + " "  +my_form.frm_state.value.trim();
		
		if (geocoder) {
			geocoder.getLatLng(
				address,
				function(point) {
					if (!point) {
						alert(address + " not found");
						} 
					else {
						pt_to_map (my_form, point.lat(), point.lng())
						}
					}
				);
			}
		}				// end function addrlkup()

// *****************************************************************************
	var tbd = "TBD";									// 1/11/09
	function do_inc_name(str) {								// 10/4/08
		if((document.add.frm_scope.value.trim()=="") || (document.add.frm_scope.value.trim()==tbd)) {	// 1/11/09
			document.add.frm_scope.value = str+"/";
			}
		}			// end function
	function datechk_s(theForm) {		// pblm start vs now
		var start = new Date();
		start.setFullYear(theForm.frm_year_problemstart.value, theForm.frm_month_problemstart.value-1, theForm.frm_day_problemstart.value);
		start.setHours(theForm.frm_hour_problemstart.value, theForm.frm_minute_problemstart.value, 0,0);
		var now = new Date();
		return (start.valueOf() <= now.valueOf());	
		}
	function datechk_e(theForm) {		// pblm end vs now
		var end = new Date();
		end.setFullYear(theForm.frm_year_problemend.value, theForm.frm_month_problemend.value-1, theForm.frm_day_problemend.value);
		end.setHours(theForm.frm_hour_problemend.value, theForm.frm_minute_problemend.value, 0,0);
		var now = new Date();
		return (end.valueOf() <= now.valueOf());	
		}
	function datechk_r(theForm) {		// pblm start vs end
		var start = new Date();
		start.setFullYear(theForm.frm_year_problemstart.value, theForm.frm_month_problemstart.value-1, theForm.frm_day_problemstart.value);
		start.setHours(theForm.frm_hour_problemstart.value, theForm.frm_minute_problemstart.value, 0,0);
	
		var end = new Date();
		end.setFullYear(theForm.frm_year_problemend.value, theForm.frm_month_problemend.value-1, theForm.frm_day_problemend.value);
		end.setHours(theForm.frm_hour_problemend.value,theForm.frm_minute_problemend.value, 0,0);
		return (start.valueOf() <= end.valueOf());	
		}
		
	function validate(theForm) {	// 
		do_unlock_ps(theForm);								// 8/11/08
	
		var errmsg="";
		if ((theForm.frm_status.value==<?php print $GLOBALS['STATUS_CLOSED'];?>) && (!theForm.re_but.checked)) 
													{errmsg+= "\tRun end-date is required for Status=Closed\n";}
//		if (theForm.frm_in_types_id.value == 0)		{errmsg+= "\tNature of Incident is required\n";}			// 1/11/09
		if (theForm.frm_contact.value == "")		{errmsg+= "\tReported-by is required\n";}
		if (theForm.frm_scope.value == "")			{errmsg+= "\tIncident name is required\n";}
//		if (theForm.frm_description.value == "")	{errmsg+= "\tSynopsis is required\n";}
//			theForm.frm_lat.disabled=false;														// 9/9/08
		if (theForm.frm_lat.value == "")			{errmsg+= "\tMap position is required\n";}
//			theForm.frm_lat.disabled=true;
		if (!chkval(theForm.frm_hour_problemstart.value, 0,23)) 		{errmsg+= "\tRun start time error - Hours\n";}
		if (!chkval(theForm.frm_minute_problemstart.value, 0,59)) 		{errmsg+= "\tRun start time error - Minutes\n";}
		if (!datechk_s(theForm))										{errmsg+= "\tRun start time error - future date\n" ;}

		if (theForm.re_but.checked) {				// run end?
			do_unlock_pe(theForm);								// problemend values
			if (!datechk_e(theForm)){errmsg+= "\tRun start time error - future\n" ;}
			if (!datechk_e(theForm)){errmsg+= "\tRun start time error - future\n" ;}
			if (!datechk_r(theForm)){errmsg+= "\tRun start time error - future\n" ;}
		
			if (!chkval(theForm.frm_hour_problemend.value, 0,23)) 		{errmsg+= "\tRun end time error - Hours\n";}
			if (!chkval(theForm.frm_minute_problemend.value, 0,59)) 	{errmsg+= "\tRun end time error - Minutes\n";}
			}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			do_unlock_ps(theForm);								// 8/11/08
			theForm.frm_phone.value=theForm.frm_phone.value.replace(/\D/g, "" ); // strip all non-digits
			return true;
			}
		}				// end function validate(theForm)
	
	function capWords(str){ 
		var words = str.split(" "); 
		for (var i=0 ; i < words.length ; i++){ 
			var testwd = words[i]; 
			var firLet = testwd.substr(0,1); 
			var rest = testwd.substr(1, testwd.length -1) 
			words[i] = firLet.toUpperCase() + rest 
	  	 	} 
		return( words.join(" ")); 
		} 
	
	function do_end(theForm) {			// enable run-end date/time inputs
		elem = $("runend1");
		elem.style.visibility = "visible";
<?php
		$show_ampm = (!get_variable('military_time')==1);
		if ($show_ampm){	//put am/pm optionlist if not military time
//			dump (get_variable('military_time'));
			print "\tdocument.add.frm_meridiem_problemend.disabled = false;\n";
			}
?>
		do_unlock_pe(theForm);								// problemend values
		}
	
	function do_reset(theForm) {				// disable run-end date/time inputs
		clearmap();
		do_lock_ps(theForm);				// hskp problem start date
		do_lock_pe(theForm);				// hskp problem end date
		$("runend1").visibility = "hidden";
		$("lock_p").style.visibility = "visible";	
		$("runend1").style.visibility = "hidden";	
		theForm.frm_lat.value=theForm.frm_lng.value="";
		document.add.frm_ngs.disabled=true;									// 4/30/09	
		$("USNG").style.textDecoration = "none";

		}		// end function reset()

	function do_problemstart(theForm, theBool) {							// 8/10/08
		theForm.frm_year_problemstart.disabled = theBool;
		theForm.frm_month_problemstart.disabled = theBool;
		theForm.frm_day_problemstart.disabled = theBool;
		theForm.frm_hour_problemstart.disabled = theBool;
		theForm.frm_minute_problemstart.disabled = theBool;
		if (theForm.frm_meridiem_problemstart) {theForm.frm_meridiem_problemstart.disabled = theBool;}
		}

	function do_problemend(theForm, theBool) {								// 8/10/08
		theForm.frm_year_problemend.disabled = theBool;
		theForm.frm_month_problemend.disabled = theBool;
		theForm.frm_day_problemend.disabled = theBool;
		theForm.frm_hour_problemend.disabled = theBool;
		theForm.frm_minute_problemend.disabled = theBool;
		if (theForm.frm_meridiem_problemend) {theForm.frm_meridiem_problemend.disabled = theBool;}
		}

	function do_unlock_ps(theForm) {											// 8/10/08
		do_problemstart(theForm, false)
		$("lock_s").style.visibility = "hidden";		
		}
		
	function do_lock_ps(theForm) {												// 8/10/08
		do_problemstart(theForm, true)
		$("lock_s").style.visibility = "visible";
		}

	function do_unlock_pe(theForm) {											// 8/10/08 
		do_problemend(theForm, false)
//		$("lock_e").style.visibility = "hidden";		
		}
		
	function do_lock_pe(theForm) {												// 8/10/08
		do_problemend(theForm, true)
//		$("lock_e").style.visibility = "visible";
		}

	function do_unlock_pos(theForm) {											// 12/5/08
		document.add.frm_ngs.disabled=false;
		$("lock_p").style.visibility = "hidden";		
		$("USNG").style.textDecoration = "underline";							// 4/30/09		
		}
		
	function do_usng() {														// 12/5/08
		if (document.add.frm_ngs.value.trim().length>6) {do_usng_conv();}
		}

	function do_usng_conv(){			// usng to LL array			- 12/4/08
		tolatlng = new Array();
		USNGtoLL(document.add.frm_ngs.value, tolatlng);
		var point = new GLatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
		map.setCenter(point, 13);

		var marker = new GMarker(point);
		document.add.frm_lat.value = point.lat(); document.add.frm_lng.value = point.lng(); 	
		do_lat (point.lat());
		do_lng (point.lng());
		do_ngs(document.add);
		load(point.lat(), point.lng(), <?php echo get_variable('def_zoom'); ?>);			// show it

		}				// end function
		
</SCRIPT>
</HEAD>

<BODY onload="ck_frames();do_lock_pe(document.add); load(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>, <?php echo get_variable('def_zoom'); ?>)" onunload="GUnload()">  <!-- 558 -->		<!-- // 8/23/08 -->

<TABLE BORDER="0" ID = "outer" >
<TR><TD COLSPAN='2' ALIGN='center'><FONT CLASS='header'>New Call</FONT><BR /><BR /></TD></TR>

<TR><TD>
<TABLE BORDER="0"></TD><TD>
<FORM METHOD="post" ACTION="add.php?add=true" NAME="add" onSubmit="return validate(document.add)">
<TR CLASS='even'><TD CLASS="td_label">Reported By:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD>
	<TD><INPUT SIZE="48" TYPE="text" NAME="frm_contact" VALUE="TBD" MAXLENGTH="48" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}"></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label">Phone: &nbsp;&nbsp;&nbsp;&nbsp;
		<button type="button" onClick="Javascript:phone_lkup(document.add.frm_phone.value);"><img src="./markers/glasses.png" alt="Lookup phone no." />
		</button>&nbsp;&nbsp;</TD>
	<TD><INPUT SIZE="16" TYPE="text" NAME="frm_phone" VALUE="<?php print get_variable('def_area_code');?>"  MAXLENGTH="16"> <!-- 1/27/09 -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS="td_label" >Status:</SPAN>
		<SELECT NAME='frm_status'><OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' selected>Open</OPTION><OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED']; ?>'>Closed</OPTION></SELECT></TD></TR>
<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='67%' /></TD></TR>
<TR CLASS='even'><TD CLASS="td_label">Priority:</TD>	<TD><SELECT NAME="frm_severity">
	<OPTION VALUE="0" SELECTED><?php print get_severity($GLOBALS['SEVERITY_NORMAL']);?></OPTION>
	<OPTION VALUE="1"><?php print get_severity($GLOBALS['SEVERITY_MEDIUM']);?></OPTION>
	<OPTION VALUE="2"><?php print get_severity($GLOBALS['SEVERITY_HIGH']);?></OPTION>
	</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	
	<SPAN CLASS="td_label">Nature: <FONT COLOR='RED' SIZE='-1'>*</FONT>
		<SELECT NAME="frm_in_types_id" onChange="do_inc_name(this.options[selectedIndex].text.trim());">	<!--  10/4/08 -->
		<OPTION VALUE=0 SELECTED>TBD</OPTION>				<!-- 1/11/09 -->
<?php
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$the_grp = strval(rand());			//  force initial optgroup value
		$i = 0;
		while ($temp_row = stripslashes_deep(mysql_fetch_array($temp_result))) {
			if ($the_grp != $temp_row['group']) {
				print ($i == 0)? "": "</OPTGROUP>\n";
				$the_grp = $temp_row['group'];
				print "<OPTGROUP LABEL='{$temp_row['group']}'>\n";
				}

//			print "\t<OPTION VALUE=' {$temp_row['id']}'  CLASS='{$temp_row['group']}' onMouseOver = 'this.title='''> {$temp_row['type']} </OPTION>\n";
			print "\t<OPTION VALUE=' {$temp_row['id']}'  CLASS='{$temp_row['group']}' title='{$temp_row['description']}'> {$temp_row['type']} </OPTION>\n";
			$i++;
			}		// end while()
		print "\n</OPTGROUP>\n";
?>
	</SELECT></TD></TR>
<?php 
		switch (get_variable('serial_no_ap')) {									// 1/22/09
		
			case 0:								/*  no serial no. */
			    $prepend = $append = "";
			    break;
			case 1:								/*  prepend  */
				$prepend = $ticket_id . "/";
				$append = "";
			    break;
			case 2:								/*  append  */
				$prepend = "";
				$append = "/" . $ticket_id;
			    break;
			default:							/* error????  */
			    $prepend = $append = " error ";			    
			}
?>
	
	<TR CLASS='odd'><TD CLASS="td_label">Incident name: <font color='red' size='-1'>*</font></TD>
		<TD><?php print $prepend;?> <INPUT SIZE="61" TYPE="text" NAME="frm_scope" VALUE="TBD" MAXLENGTH="61" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}"><?php print $append;?></TD></TR>	<!-- 1/11/09 -->
	<TR CLASS='even'><TD CLASS="td_label">Location:</TD>
		<TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61"></TD></TR>
	<TR CLASS='odd'><TD CLASS="td_label">City:
		&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onClick="Javascript:loc_lkup(document.add);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD>
		<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;St:&nbsp;&nbsp;<INPUT SIZE="2" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="2"></TD></TR>
	<TR CLASS='even' VALIGN="top"><TD CLASS="td_label">Synopsis: </TD><TD><TEXTAREA NAME="frm_description" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA></TD></TR>
	<!--
	<TR CLASS='even'><TD CLASS="td_label">Affected:</TD><TD><INPUT SIZE="48" TYPE="text" 	NAME="frm_affected" VALUE="" MAXLENGTH="48"></TD></TR>
	-->
	<TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label">Run Start:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock_s' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_ps(document.add);'></TD><TD>
<?php print generate_date_dropdown('problemstart',0,TRUE);?>
		</TD></TR>
	<TR CLASS='even' valign="middle"><TD CLASS="td_label">Run End: &nbsp;&nbsp;<input type="radio" name="re_but" onClick ="do_end(this.form);" /></TD><TD>
		<SPAN style = "visibility:hidden" ID = "runend1"><?php print generate_date_dropdown('problemend',0, TRUE);?></SPAN>
		</TD></TR>
	<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label">Disposition:</TD><TD><TEXTAREA NAME="frm_comments" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA></TD></TR>
	<TR CLASS='even'>
		<TD CLASS="td_label">
			<SPAN ID="pos" onClick = 'javascript: do_coords(document.add.frm_lat.value ,document.add.frm_lng.value );'> <U>Lat/Lng</U></SPAN>: 
				<font color='red' size='-1'>*</font>&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock_p' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.add);'>
		</TD>
		<TD><INPUT SIZE="11" TYPE="text" NAME="show_lat" VALUE="" >
			<INPUT SIZE="11" TYPE="text" NAME="show_lng" VALUE="" >&nbsp;&nbsp;
			<B><SPAN ID = 'USNG' onClick = "do_usng()">USNG</SPAN></B>:&nbsp;<INPUT SIZE="19" TYPE="text" NAME="frm_ngs" VALUE="" DISABLED ></TD>
			</TR> <!-- 9/13/08, 12/3/08 -->
	<TR CLASS='even'><TD COLSPAN="3" ALIGN="center"><BR />
		<INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="reset" VALUE="Reset" onclick= "do_reset(this.form);" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="submit" VALUE="Submit"></TD></TR>	<!-- 8/11/08 -->
	<TR CLASS='odd'><TD COLSPAN="3" ALIGN="center"><br /><IMG SRC="glasses.png" BORDER="0"/>: Lookup </TD></TR>
	
		<INPUT TYPE="hidden" NAME="frm_lat" VALUE="">				<!-- // 9/9/08 -->
		<INPUT TYPE="hidden" NAME="frm_lng" VALUE="">
		<INPUT TYPE="hidden" NAME="ticket_id" VALUE="<?php print $ticket_id;?>">	<!-- 1/25/09, 3/10/09 -->
	
	</FORM></TABLE>
	</TD><TD>
	<TABLE ID='four' border=0><TR><TD id='three' ALIGN='center'><div id='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px'></div>
	<BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR /><BR />
		<SPAN ID='do_grid' onclick = "toglGrid()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>Grid</U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<SPAN ID='do_sv' onClick = "sv_win(document.add)" style='display:none'><u>Street view</U></SPAN> <!-- 2/11/09 -->
		
	</TD></TR /></TABLE>
	</TD></TR>
	</TABLE>
	
<?php 
//	dump($my_session['user_id']);
	} //end if/else
?>
<FORM NAME='can_Form' ACTION="main.php">
</FORM>	
</BODY></HTML>