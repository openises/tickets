<?php
/*
10/28/07 added onLoad = "document.add.frm_lat.disabled..
11/38/07 added frame jump prevention
11/98/07 added map under image
5/29/08  added do_kml() call
8/11/08	 added problem start lock/unlock
8/23/08	 added usng handling 
8/23/08  corrected problem-end hskpng
9/9/08	 added lat/lng-to-CG format functions
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
$api_key = get_variable('gmaps_api_key');
if ($istest) {
	dump($_POST);
	dump ($_GET);
	}

$get_add = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['add'])))) ) ? "" : $_GET['add'] ;
$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;
$post_frm_owner = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_owner'])))) ) ? "" : $_POST['frm_owner'] ;
$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;

	if ($get_add == 'true')	{

		$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//replace HTML tags with customs
		$post_frm_affected    		= strip_html($post_frm_affected);
		$_POST['frm_scope']       	= strip_html($_POST['frm_scope']);

		$frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";

//		if (!get_variable('military_time'))	{			//put together date from the dropdown box and textbox values
		$do_ampm = (!get_variable('military_time')==1);
		if ($do_ampm){
			if ($post_frm_meridiem_problemstart == 'pm'){
				$_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
				}
			if (isset($_POST['frm_meridiem_problemend'])) {
				if ($_POST['frm_meridiem_problemend'] == 'pm'){
					$_POST['frm_hour_problemend'] = ($_POST['frm_hour_problemend'] + 12) % 24;
					}
				}
			}		// end if ($do_ampm)
			
		$frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  quote_smart($_POST['frm_year_problemend'] . "-" . $_POST['frm_month_problemend'] . "-" . $_POST['frm_day_problemend']." " . $_POST['frm_hour_problemend'] . ":". $_POST['frm_minute_problemend'] .":00") : "NULL";
		$affected = "";			// 9/13/08
			
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
//		if(empty($post_frm_owner)) {$post_frm_owner=0;}
		$post_frm_owner=(empty($post_frm_owner))? 0: $post_frm_owner;				// in_types_id -- 

		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]ticket` (`in_types_id`, `contact`,`street`,`city`,`state`,`phone`,`lat`,`lng`,
											`scope`,`affected`,`description`,`comments`,`owner`,`severity`,`status`,
											`date`,`problemstart`,`problemend`,`updated`)
							VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
							
								quote_smart(trim($_POST['frm_in_types_id'])),		
								quote_smart(trim($_POST['frm_contact'])),
								quote_smart(trim($_POST['frm_street'])),
								quote_smart(trim($_POST['frm_city'])),
								quote_smart(trim($_POST['frm_state'])),
								quote_smart(trim($_POST['frm_phone'])),
								quote_smart($_POST['frm_lat']),
								quote_smart($_POST['frm_lng']),
								quote_smart($_POST['frm_scope']),
								quote_smart($affected),
								quote_smart(trim($_POST['frm_description'])),
								quote_smart(trim($_POST['frm_comments'])),
								quote_smart($my_session['user_id']),
								quote_smart($_POST['frm_severity']),
								$GLOBALS['STATUS_OPEN'],
								quote_smart($now),
								quote_smart($frm_problemstart),
								$frm_problemend,
								quote_smart($now));				// 9/13/08
//		dump($query);								
		$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

		$ticket_id = mysql_insert_id();								// just inserted id
		do_log($GLOBALS['LOG_INCIDENT_OPEN'], $ticket_id);
		
		$frm_unit_id = 0; $frm_status_id=1;$frm_comments = "New";				// into assignments
		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`)
						VALUES (%s,%s,%s,%s,%s,%s)",
							quote_smart($now),
							quote_smart($frm_status_id),
							quote_smart($ticket_id),
							quote_smart($frm_unit_id),
							quote_smart($frm_comments),
							quote_smart($my_session['user_id']));

		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder` LIMIT 1";	//  any at all?
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//		if (mysql_affected_rows()>0) {
			header("Location: routes.php?ticket_id=$ticket_id");				// show routes from units to incident
//			}
//		else {
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
			<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
			<SCRIPT>
				function ck_frames() {		// ck_frames()
					if(self.location.href==parent.location.href) {
						self.location.href = 'index.php';
						}
					}		// end function ck_frames()
		try {
			parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
			parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
			parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
			}
		catch(e) {
			}
	
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "ck_frames()" onunload="GUnload()">
<?php
			print "<FONT CLASS=\"header\">Added Ticket: '".substr($_POST['frm_description'],0,50)."' by user '$my_session[user_name]'</FONT><BR /><BR />";
			list_tickets();
//			}
		}				// end if ($_GET['add'] ...
		
	else {
		if (is_guest() && !get_variable('guest_add_ticket')) {
			print '<FONT CLASS="warn">Guest users may not add tickets on this system.  Contact administrator for further information.</FONT>';
			exit();
			}
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

	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08		
	
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
				alert (220);
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
				alert (236);
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
	
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function chknum(val) { 
		return ((val.trim().replace(/\D/g, "")==val.trim()) && (val.trim().length>0));}
	
	function chkval(val, lo, hi) { 
		return  (chknum(val) && !((val> hi) || (val < lo)));}
	
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
		load();
		if (grid) {map.addOverlay(new LatLonGraticule());}
		}
	
	function domap() {										// called from phone, addr lookups
		map = new GMap2(document.getElementById('map'));
		document.getElementById("map").style.backgroundImage = "url(./markers/loading.jpg)";
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
		map.setCenter(new GLatLng(document.add.frm_lat.value, document.add.frm_lng.value), 13);			// larger # => tighter zoom
		map.addControl(new GOverviewMapControl());
		map.enableScrollWheelZoom(); 	
		var myIcon = new GIcon();
		myIcon.image = "./markers/blank.png";
		myIcon.shadow = "./markers/sm_shadow.png";
		myIcon.iconSize = new GSize(12, 20);
		myIcon.shadowSize = new GSize(22, 20);
		myIcon.iconAnchor = new GPoint(6, 20);
		myIcon.infoWindowAnchor = new GPoint(5, 1);
	
		var sep = (document.add.frm_street.value=="")? "": ", ";
		var tab1contents = "<B>" + document.add.frm_contact.value + "</B>" +
			"<BR/>"+document.add.frm_street.value + sep +
			document.add.frm_city.value +" " +
			document.add.frm_state.value;
	
		var marker = new GMarker(map.getCenter());						// Place a marker in the center of the map
		map.addOverlay(marker, myIcon);									// 
		marker.openInfoWindowHtml(tab1contents);						// 
		
		GEvent.addListener(map, "click", function(marker, point) {		// lookup
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
		}				// end function do map()
	
	function load() {									// onLoad function
		if (GBrowserIsCompatible()) {
			function drawCircle(lng,lat,radius) { 		// drawCircle(-87.628092,41.881906,2);
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
		map = new GMap2(document.getElementById('map'));
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());

		var baseIcon = new GIcon();				// 9/16/08
		baseIcon.iconSize=new GSize(32,32);
		baseIcon.iconAnchor=new GPoint(16,16);
		var cross = new GIcon(baseIcon, "./markers/crosshair.png", null);
		var center = new GLatLng(<?php print get_variable('def_lat') ?>, <?php print get_variable('def_lng'); ?>);
		map.setCenter(center, <?php print get_variable('def_zoom');?>);
		var thisMarker  = new GMarker(center, {icon: cross, draggable:false} );				// 9/16/08
		map.addOverlay(thisMarker);
		
//		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>
	
		GEvent.addListener(map, "click", function(marker, point) {
			if (marker) {									// undo it
				map.removeOverlay(marker);
				thePoint = "";
//				document.add.frm_lat.disabled=document.add.frm_lat.disabled=false;		// 9/9/08
				document.add.frm_lat.value=document.add.frm_lng.value="";
//				document.add.frm_lat.disabled=document.add.frm_lat.disabled=true;
				if (grid) {map.addOverlay(new LatLonGraticule());}
				}
			if (point) {
				map.clearOverlays();
				do_lat (point.lat())				// display
				do_lng (point.lng())
				do_ngs(document.add);
				map.addOverlay(new GMarker(point));	// GLatLng
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
	
	function do_addr_lkup() {		   // added 8/3 by AS -- getLocations(address,  callback)
		var address = document.add.frm_street.value + " " +document.add.frm_city.value + " " +document.add.frm_state.value;
		if (geocoder) {
			geocoder.getLatLng(
				address,
				function(point) {
					if (!point) {
						alert(address + " not found");
						} 
					else {
						map.setCenter(point, 13);
						var marker = new GMarker(point);
						document.add.frm_lat.value = point.lat(); document.add.frm_lng.value = point.lng(); 	
						do_lat (point.lat());
						do_lng (point.lng());
						do_ngs(document.add);
						domap();																					// show it
						}
					}
				);
			}
		}				// end function addr lkup()
	
	function do_lat (lat) {
		document.add.frm_lat.value=lat.toFixed(6);			// 9/9/08
		document.add.show_lat.disabled=false;				// permit read/write
		document.add.show_lat.value=do_lat_fmt(document.add.frm_lat.value);
		document.add.show_lat.disabled=true;
		}
	function do_lng (lng) {
		document.add.frm_lng.value=lng.toFixed(6);
		document.add.show_lng.disabled=false;
		document.add.show_lng.value=do_lng_fmt(document.add.frm_lng.value);
		document.add.show_lng.disabled=true;
		}

	function do_ngs(theForm) {								// 8/23/08
		theForm.frm_ngs.disabled=false;						// 9/9/08
		theForm.frm_ngs.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);
		theForm.frm_ngs.disabled=true;
		}
//	*****************************************************************		
	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name ???
			return AJAX.responseText;																				 
			} 
		else {
			alert ("57: failed")
			return false;
			}																						 
		}		// end function sync Ajax(strURL)
	
	function do_phone_lkup() {		// 
		var goodno = document.add.frm_phone.value.replace(/\D/g, "" );							// strip all non-digits
		if (goodno.length != 10) {
			alert ("10-digit phone no. reqd - any format)"); document.add.frm_phone.focus(); return false;
			}
		else {
			do_lkup(goodno);			// generic lookup
			}
		}		// end function do_phone_lkup()
	
	function do_name_lkup() {		// 
		if ((document.add.frm_contact.value.length==0) || (document.add.frm_city.value.length==0) || (document.add.frm_state.value.length==0)) {
				alert ("Name, city, state required for name lookup");
				return false;
				}
			var name_str = document.add.frm_contact.value + " " + document.add.frm_city.value + " " + document.add.frm_state.value;
			var test = do_lkup(name_str);			// generic lookup
		}		// end function do_name_lkup()
	
	function do_lkup(instr) {								// generic
//		var url = "plkup.php?phone=" + URLEncode(instr);	// phone no. or addr string
		var url = "plkup.php?q=" + URLEncode(instr);		// phone no. or addr string 8/16/08
		var payload = syncAjax(url);						// send lookup url
		if (payload.substring(0,1)=="-") {					// stringObject.substring(start,stop)
			alert ("lookup failed");
			return false;
			}
		else {
			var temp1=payload.split(";");					// good return - now parse results
			alert(temp1[0]);
			document.add.frm_contact.value=temp1[0].trim();
			document.add.frm_phone.value=temp1[1].trim();
			var temp2=temp1[2].split(",");					// 	address portion
			if (temp2.length>3) {
				for (var i=0;i<temp2.length;i++) {
					alert (temp2[i]);
					}
				}
			document.add.frm_street.value=temp2[0].trim();				// street
			document.add.frm_city.value=temp2[1].trim();				// city
			document.add.frm_state.value=temp2[2].trim();				// state
			do_addr_lkup();												// to map
			}				// end if/else (payload.substring(... )
		}		// end function do_lkup()
	
	
// *********************************************************************		


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
		if (theForm.frm_in_types_id.value == 0)		{errmsg+= "\tNature of Incident is required\n";}
		if (theForm.frm_contact.value == "")		{errmsg+= "\tReported-by is required\n";}
		if (theForm.frm_scope.value == "")			{errmsg+= "\tIncident name is required\n";}
		if (theForm.frm_description.value == "")	{errmsg+= "\tSynopsis is required\n";}
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
//			theForm.frm_ngs.disabled=false;						// 8/23/08
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
		elem = document.getElementById("runend1");
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
		elem = document.getElementById("runend1");
		elem.style.visibility = "hidden";
		theForm.frm_lat.value=theForm.frm_lng.value="";
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
		document.getElementById("lock_s").style.visibility = "hidden";		
		}
		
	function do_lock_ps(theForm) {												// 8/10/08
		do_problemstart(theForm, true)
		document.getElementById("lock_s").style.visibility = "visible";
		}

	function do_unlock_pe(theForm) {											// 8/10/08
		do_problemend(theForm, false)
//		document.getElementById("lock_e").style.visibility = "hidden";		
		}
		
	function do_lock_pe(theForm) {												// 8/10/08
		do_problemend(theForm, true)
//		document.getElementById("lock_e").style.visibility = "visible";
		}
		
</SCRIPT>
</HEAD>

<BODY onload="ck_frames();do_lock_pe(document.add); load()" onunload="GUnload()">  <!-- 558 -->		<!-- // 8/23/08 -->

<TABLE BORDER="0" ID = "outer">
<TR><TD COLSPAN='2' ALIGN='center'><FONT CLASS='header'>New Call</FONT><BR /><BR /></TD></TR>

<TR><TD>
<TABLE BORDER="0"></TD><TD>
<FORM METHOD="post" ACTION="add.php?add=true" NAME="add" onSubmit="return validate(document.add)">
<TR CLASS='even'><TD CLASS="td_label" onClick="Javascript:do_name_lkup();">Reported By:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD>
	<TD ALIGN='center' onClick="Javascript:do_name_lkup();"><IMG SRC="glasses.png" BORDER="0"/></TD>
	<TD><INPUT SIZE="48" TYPE="text" NAME="frm_contact" VALUE="" MAXLENGTH="48"></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label" onClick="Javascript:do_phone_lkup();">Phone:</TD>
	<TD ALIGN='center' onClick="Javascript:do_phone_lkup();" ><IMG SRC="glasses.png" BORDER="0" STYLE="visibility: hidden"/></TD>
	<TD><INPUT SIZE="16" TYPE="text" NAME="frm_phone" VALUE="" MAXLENGTH="16">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN CLASS="td_label" >Status:</SPAN>
		<SELECT NAME='frm_status'><OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' selected>Open</OPTION><OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED']; ?>'>Closed</OPTION></SELECT></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label" COLSPAN=2>&nbsp;</TD></TR>
<TR CLASS='even'><TD CLASS="td_label">Priority:</TD><TD></TD>		<TD><SELECT NAME="frm_severity">
	<OPTION VALUE="0" SELECTED><?php print get_severity($GLOBALS['SEVERITY_NORMAL']);?></OPTION>
	<OPTION VALUE="1"><?php print get_severity($GLOBALS['SEVERITY_MEDIUM']);?></OPTION>
	<OPTION VALUE="2"><?php print get_severity($GLOBALS['SEVERITY_HIGH']);?></OPTION>
	</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	
	<SPAN CLASS="td_label">Nature: <FONT COLOR='RED' SIZE='-1'>*</FONT>
		<SELECT NAME="frm_in_types_id">
		<OPTION VALUE=0 CLASS='main' SELECTED>Select</OPTION>
<?php
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
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
	
<TR CLASS='odd'><TD CLASS="td_label">Incident name: <font color='red' size='-1'>*</font></TD><TD></TD>		<TD><INPUT SIZE="61" TYPE="text" NAME="frm_scope" VALUE="" MAXLENGTH="61"></TD></TR>
<TR CLASS='even'><TD CLASS="td_label">Location:</TD><TD></TD>		<TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61"></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label" onClick="Javascript:do_addr_lkup();">City:</TD><TD ALIGN='center' onClick="Javascript:do_addr_lkup();"><IMG SRC="glasses.png" BORDER="0"/></TD> <TD><INPUT SIZE="32" TYPE="text" 		NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;St:&nbsp;&nbsp;<INPUT SIZE="2" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="2"></TD></TR>
<TR CLASS='even' VALIGN="top"><TD CLASS="td_label">Synopsis: <font color='red' size='-1'>*</font></TD><TD></TD><TD><TEXTAREA NAME="frm_description" COLS="45" ROWS="2"></TEXTAREA></TD></TR>
<!--
<TR CLASS='even'><TD CLASS="td_label">Affected:</TD><TD></TD><TD><INPUT SIZE="48" TYPE="text" 	NAME="frm_affected" VALUE="" MAXLENGTH="48"></TD></TR>
-->
<TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label">Run Start: &nbsp;&nbsp;</TD><TD></TD><TD><?php print generate_date_dropdown('problemstart',0,TRUE);?>&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock_s' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock_ps(document.add);'></TD></TR>
<TR CLASS='even' valign="middle"><TD CLASS="td_label">Run End: &nbsp;&nbsp;<input type="radio" name="re_but" onClick ="do_end(this.form);" /></TD><TD></TD><TD>
	<SPAN style = "visibility:hidden" ID = "runend1"><?php print generate_date_dropdown('problemend',0, TRUE);?></SPAN>
	</TD></TR>
<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label">Comments:</TD><TD></TD><TD><TEXTAREA NAME="frm_comments" COLS="45" ROWS="2"></TEXTAREA></TD></TR>
<TR CLASS='even'><TD CLASS="td_label" onClick = 'javascript: do_coords(document.add.frm_lat.value ,document.add.frm_lng.value  )'><U>Position</U>: <font color='red' size='-1'>*</font></TD><TD></TD>
	<TD><INPUT SIZE="13" TYPE="text" NAME="show_lat" VALUE="" >
			<INPUT SIZE="13" TYPE="text" NAME="show_lng" VALUE="" >&nbsp;&nbsp;
			<B>USNG:&nbsp;</B><INPUT SIZE="19" TYPE="text" NAME="frm_ngs" VALUE="" DISABLED ></TD></TR> <!-- 9/13/08 -->
<TR CLASS='odd'><TD COLSPAN=99 ALIGN='center'><IMG SRC="glasses.png" BORDER="0"/>&nbsp;&nbsp;Lookups:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<!--<A HREF='#' onClick = "Javascript:do_phone_lkup();">Phone</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
	<A HREF='#' onClick = "Javascript:do_name_lkup();">Name</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<A HREF='#' onClick = "Javascript:do_addr_lkup();">Address</A></TD></TR>
<TR CLASS='even'><TD COLSPAN="3" ALIGN="center"><BR />
	<INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="reset" VALUE="Reset" onclick= "do_reset(this.form);" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="submit" VALUE="Submit"></TD></TR>	<!-- 8/11/08 -->
<TR CLASS='odd'><TD COLSPAN="3" ALIGN="center"><br /><IMG SRC="glasses.png" BORDER="0"/>: Lookup fields</TD></TR>

	<INPUT TYPE="hidden" NAME="frm_lat" VALUE="">				<!-- // 9/9/08 -->
	<INPUT TYPE="hidden" NAME="frm_lng" VALUE="">

</FORM></TABLE>
</TD><TD>
<TABLE ID='four'><TR><TD id='three' ALIGN='center'><div id='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px'></div>
<BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR /><BR />

<A HREF='#' onClick='toglGrid()'><u>Grid</U></A></TD></TR /></TABLE>
</TD></TR>
</TABLE>
<?php 
//	dump($my_session['user_id']);
	} //end if/else
?>
<FORM NAME='can_Form' ACTION="main.php">
</FORM>	
</BODY></HTML>
