<?php 
require_once('functions.inc.php');
do_login(basename(__FILE__));
$api_key = get_variable('gmaps_api_key');

$get_add = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['add'])))) ) ? "" : $_GET['add'] ;
$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;
$post_frm_owner = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_owner'])))) ) ? "" : $_POST['frm_owner'] ;
$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;

//	if ($_GET['add'] == 'true')	{
	if ($get_add == 'true')	{

		$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//replace HTML tags with customs
		$post_frm_affected    		= strip_html($post_frm_affected);
		$_POST['frm_scope']       	= strip_html($_POST['frm_scope']);

		$frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";

		if (!get_variable('military_time'))	{			//put together date from the dropdown box and textbox values
			if ($post_frm_meridiem_problemstart == 'pm'){
				$_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
				}
			if (isset($_POST['frm_meridiem_problemend'])) {
				if ($_POST['frm_meridiem_problemend'] == 'pm'){
					$_POST[frm_hour_problemend] = ($_POST[frm_hour_problemend] + 12) % 24;
					}
				}
			}
		$frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  quote_smart($_POST['frm_year_problemend'] . "-" . $_POST['frm_month_problemend'] . "-" . $_POST['frm_day_problemend']." " . $_POST['frm_hour_problemend'] . ":". $_POST['frm_minute_problemend'] .":00") : "NULL";
			
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
		if(empty($post_frm_owner)) {$post_frm_owner=0;}
		$post_frm_owner=(empty($post_frm_owner))? 0: $post_frm_owner;

		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]ticket` (`contact`,`street`,`city`,`state`,`phone`,`lat`,`lng`,
											`scope`,`affected`,`description`,`comments`,`owner`,`severity`,`status`,
											`date`,`problemstart`,`problemend`,`updated`)
							VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
							
								quote_smart(trim($_POST['frm_contact'])),
								quote_smart(trim($_POST['frm_street'])),
								quote_smart(trim($_POST['frm_city'])),
								quote_smart(trim($_POST['frm_state'])),
								quote_smart(trim($_POST['frm_phone'])),
								quote_smart($_POST['frm_lat']),
								quote_smart($_POST['frm_lng']),
								quote_smart($_POST['frm_scope']),
								quote_smart(trim($post_frm_affected)),
								quote_smart(trim($_POST['frm_description'])),
								quote_smart(trim($_POST['frm_comments'])),
								quote_smart($post_frm_owner),
								quote_smart($_POST['frm_severity']),
								$GLOBALS['STATUS_OPEN'],
								quote_smart($now),
								quote_smart($frm_problemstart),
								$frm_problemend,
								quote_smart($now));
		$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

		$ticket_id = mysql_insert_id();								// just inserted id
//		report_action($GLOBALS[ACTION_OPEN],0,0,$_POST[$frm_owner]);

		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder` LIMIT 1";	//  any at all?
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if (mysql_affected_rows()>0) {
			header("Location: routes.php?ticket_id=$ticket_id");				// show routes from units to incident
			}
		else {
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
			<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
			</HEAD>
			<BODY onunload="GUnload()">
<?php
			print "<FONT CLASS=\"header\">Added Ticket: '".substr($_POST['frm_description'],0,50)."' by user '$_SESSION[user_name]'</FONT><BR /><BR />";
			list_tickets();
			}
		}				// end if ($_GET['add'] ...
		
	else {
			$log_file = "log.dat";
			$tab = "\t";
			$tzoffset = 5*60*60;
			$localtime=(gmdate("M d h:i a", date('U') - $tzoffset));
			
			if (!file_exists($log_file)) {
			   if ($f = fopen($log_file,"w")) fclose($f);
			   chmod ($log_file, 0666);};
			
			$lf = fopen($log_file,"a");
			$newdata = $tab . $localtime . $tab . basename(__FILE__) . $tab .gethostbyaddr($_SERVER['REMOTE_ADDR']) ."\n"; //_SERVER["REMOTE_ADDR"]
			$newdata = stripslashes($newdata);
			fwrite($lf,$newdata);
			fclose($lf);		
		

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
<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
</HEAD>
<BODY onunload="GUnload()">

<SCRIPT SRC="graticule.js" type="text/javascript"></SCRIPT>
<SCRIPT>
	var map;						// note globals
	var geocoder = null;
	geocoder = new GClientGeocoder();
	var request;
	var querySting;   				// will hold the POSTed data
	var tab1contents				// info window contents - first/only tab
	var grid = false;				// toggle
	var thePoint;
	
	function writeConsole(content) {
		top.consoleRef=window.open('','myconsole',
			'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=0' +',resizable=0')
	 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
			+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></html>'
			)				// end top.consoleRef.document.writeln()
	 	top.consoleRef.document.close();
		}				// end function writeConsole(content)
	
	function getRes() {
		return window.screen.width + ' x ' + window.screen.height;
		}

	function doLog(){
		document.theLog.frm_res.value=getRes();
		setQueryString();
		var url="../cris/areas_sc.php";					// server_side 'create/insert entry'
//		alert ("53 " + url);
		
		httpRequest("POST",url,true);
		}				// end function sendData()
		
	function setQueryString(){							// pick up 2nd form -- fix!!!
		queryString="";
		var frm = document.forms[0];
		for(var i = 0; i < frm.elements.length; i++)  {
			queryString += frm.elements[i].name+ "="+ encodeURIComponent(frm.elements[i].value)+"&";
			}
		
		queryString= queryString.substring(0, queryString.length-1);		// drop terminal &
		}				// end function setQueryString()
			
	function httpRequest(reqType,url,asynch){	// Wrapper function for constructing a Request object. Parameters:
		if(window.XMLHttpRequest){				//		reqType: 	HTTP request type: GET or POST.
			request = new XMLHttpRequest();		//		url: 		URL of the server program.
			} 									//  	asynch: 	Whether to send the request asynchronously or not.
		else if (window.ActiveXObject){						
			request=new ActiveXObject("Msxml2.XMLHTTP");
			if (! request){
				request=new ActiveXObject("Microsoft.XMLHTTP");
				}
		 	}
		if(request)	{initReq(reqType,url,asynch);}		//the request could still be null if neither ActiveXObject
														//initializations succeeded
		else 		{alert("83: Browser problem; pls notify developer."); }
		}
		
	function initReq(reqType,url,bool){					// Initialize a Request object that is already constructed 
		request.onreadystatechange=handleCheck;			// Specify the function that will handle the HTTP response 
		request.open(reqType,url,bool);
		request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
		request.send(queryString);
		}
	  	
	function handleCheck(){								//event handler for XMLHttpRequest
		if(request.readyState == 4){
			if(request.status == 200){
//				alert (93);
				if (request.responseText[0] == '-') {	// error id			
					writeConsole(request.responseText);
					alert ("error 81");
					}
				} 
//			else {
//				alert("error 85: A XMLHttpRequest problem has occurred; pls notify developer.");
//				}
			}		//end outer if
		}		// end function handleCheck()


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
				document.add.frm_lat.disabled=document.add.frm_lat.disabled=false;
				document.add.frm_lat.value=document.add.frm_lng.value="";
				document.add.frm_lat.disabled=document.add.frm_lat.disabled=true;
				if (grid) {map.addOverlay(new LatLonGraticule());}
				}
			
			if (point) {
				map.clearOverlays();
				do_lat (point.lat())				// display
				do_lng (point.lng())	
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
		map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);		// <?php echo get_variable('def_lat'); ?>
	
		GEvent.addListener(map, "click", function(marker, point) {
			if (marker) {									// undo it
				map.removeOverlay(marker);
				thePoint = "";
				document.add.frm_lat.disabled=document.add.frm_lat.disabled=false;
				document.add.frm_lat.value=document.add.frm_lng.value="";
				document.add.frm_lat.disabled=document.add.frm_lat.disabled=true;
				if (grid) {map.addOverlay(new LatLonGraticule());}
				}
			if (point) {
				map.clearOverlays();
				do_lat (point.lat())				// display
				do_lng (point.lng())
				map.addOverlay(new GMarker(point));	// GLatLng
				thePoint = point;
				if (grid) {map.addOverlay(new LatLonGraticule());}
				}
			});
 			document.add.frm_lat.disabled=document.add.frm_lng.disabled=true;			
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
	
	function addrlkup() {		   // added 8/3 by AS -- getLocations(address,  callback)
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
						domap();																					// show it
						}
					}
				);
			}
		}				// end function addrlkup()
	
	function do_lat (lat) {
		document.add.frm_lat.disabled=false;
		document.add.frm_lat.value=lat.toFixed(6);
		document.add.frm_lat.disabled=true;
		}
	function do_lng (lng) {
		document.add.frm_lng.disabled=false;
		document.add.frm_lng.value=lng.toFixed(6);
		document.add.frm_lng.disabled=true;
		}
	
	function phonelkup() {
		var goodno = document.add.frm_phone.value.replace(/\D/g, "" );							// strip all non-digits
		if (goodno.length != 10) {
//			document.add.sub_but.disabled = false;
			alert ("10-digit phone no. reqd. (any format)"); document.add.frm_phone.focus(); return false;}
	
		var url = "./incs/phonelkup.php?phone=" + document.add.frm_phone.value;
		var callback = phonelkupresults;
		executePXhr(callback, url);
		}
	
	function executePXhr(callback, url) {				// phone-specific - see above
		if (window.XMLHttpRequest) {					//  for native XMLHttpRequest object
			request = new XMLHttpRequest();
			request.onreadystatechange = callback;
			request.open("GET", url, true);
			request.send(null);
			}
		else if (window.ActiveXObject) { 				//  for IE/Windows ActiveX version
			request = new ActiveXObject("Microsoft.XMLHTTP");
			if (request) {
				request.onreadystatechange = callback;
				request.open("GET", url, true);
				request.send();
				}
			}
		}
	
	function phonelkupresults() {					// only if request shows "loaded"
		if (request.readyState == 4) {					// only if "OK"
			if (request.status == 200){
				if (request.responseText.substring(0,1) == '-') {writeConsole(request.responseText) ;}
	
				var response= request.responseText.split('\t');
				if (response.length<8) {
					alert ("phone lookup failed")
					return false;
					}
				else {				// if successful
					document.add.frm_contact.value=	response[1];
					document.add.frm_street.value=	response[2];
					var response2 = response[3].split(' ');				// parse address
					
					document.add.frm_city.value=	response2[0];
					if (response2.length>3) {document.add.frm_city.value+=response2[1];}		// ex: Something City
					document.add.frm_city.value = document.add.frm_city.value.substring(0, document.add.frm_city.value.length-1);	// drop trailing comma
					document.add.frm_state.value=	response2[response2.length - 2];
	
					do_lat (response[6])
					do_lng (response[7])
					domap();
					}
				}		// end if (request.status == 200)
			else {
//				alert("374 error.");
//				document.add.sub_but.disabled = false;
				}
			}			// end if (request.readyState == 4)
		}		// end function
	
	function validate(theForm) {
	
//		alert (theForm.frm_status.value);
//		alert (theForm.re_but.checked) 
		var errmsg="";
		if ((theForm.frm_status.value==<?php print $GLOBALS['STATUS_CLOSED'];?>) && (!theForm.re_but.checked)) 
													{errmsg+= "\tRun end-date is required for Status=Closed\n";}
		if (theForm.frm_contact.value == "")		{errmsg+= "\tReported-by is required\n";}
		if (theForm.frm_scope.value == "")			{errmsg+= "\tIncident name is required\n";}
		if (theForm.frm_description.value == "")	{errmsg+= "\tDescription is required\n";}
			theForm.frm_lat.disabled=false;
		if (theForm.frm_lat.value == "")			{errmsg+= "\tMap position is required\n";}
			theForm.frm_lat.disabled=true;
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.frm_lat.disabled=false;
			theForm.frm_lng.disabled=false;	
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
	
	function do_end() {				// make run-end date/time inputs available for posting
		elem = document.getElementById("runend1");
		elem.style.visibility = "visible";
		document.add.frm_year_problemend.disabled = false;
		document.add.frm_month_problemend.disabled = false;
		document.add.frm_day_problemend.disabled = false;
	<?php
		if (!get_variable('military_time')){
			print "\tdocument.add.frm_meridiem_problemend.disabled = false;\n";
			}
	?>
		document.add.frm_hour_problemend.disabled = false;	
		document.add.frm_minute_problemend.disabled = false;
		}
	
	function reset_end() {				// make run-end date/time inputs available for posting
		clearmap();
		elem = document.getElementById("runend1");
		elem.style.visibility = "hidden";
		document.add.frm_year_problemend.disabled = true;
		document.add.frm_month_problemend.disabled = true;
		document.add.frm_day_problemend.disabled = true;
<?php
		if (!get_variable('military_time')){
			print "\tdocument.add.frm_meridiem_problemend.disabled = true;\n";
			}
?>
		document.add.frm_hour_problemend.disabled = true;	
		document.add.frm_minute_problemend.disabled = true;
		}

</SCRIPT>
</HEAD>

<BODY onload="load()" onunload="GUnload()"> 

<TABLE BORDER="0"ID = "outer">
<TR><TD COLSPAN='2' ALIGN='center'><FONT CLASS='header'>New Run Ticket</FONT><BR /><BR /></TD></TR>

<TR><TD>
<TABLE BORDER="0">
<FORM METHOD="post" ACTION="add.php?add=true" NAME="add" onSubmit="return validate(document.add)">
<TR CLASS='even'><TD CLASS="td_label">Reported By: <font color='red' size='-1'>*</font></TD>	<TD><INPUT SIZE="48" TYPE="text" NAME="frm_contact" VALUE="" MAXLENGTH="48"></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label">Phone:</TD>		<TD><INPUT SIZE="16" TYPE="text" NAME="frm_phone" VALUE="" MAXLENGTH="16"></TD></TR>
<TR CLASS='even'><TD CLASS='td_label'>Status:</TD><TD>
				<SELECT NAME='frm_status'><OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' selected>Open</OPTION><OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED']; ?>'>Closed</OPTION></SELECT></TD></TR>

<TR CLASS='odd'><TD CLASS="td_label" COLSPAN=2>&nbsp;</TD></TR>
<TR CLASS='even'><TD CLASS="td_label">Incident: <font color='red' size='-1'>*</font></TD>		<TD><INPUT SIZE="48" TYPE="text" NAME="frm_scope" VALUE="" MAXLENGTH="48"></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label">Address:</TD>		<TD><INPUT SIZE="48" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="48"></TD></TR>
<TR CLASS='even'><TD CLASS="td_label"><a href="#" onClick="Javascript:addrlkup();">City:&nbsp;&nbsp;<IMG SRC="glasses.png" BORDER="0"/></A></TD> <TD><INPUT SIZE="32" TYPE="text" 		NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;St:&nbsp;&nbsp;<INPUT SIZE="2" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="2"></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label">Priority:</TD>		<TD><SELECT NAME="frm_severity">
<OPTION VALUE="0" SELECTED><?php print get_severity($GLOBALS['SEVERITY_NORMAL']);?></OPTION>
<OPTION VALUE="1"><?php print get_severity($GLOBALS['SEVERITY_MEDIUM']);?></OPTION>
<OPTION VALUE="2"><?php print get_severity($GLOBALS['SEVERITY_HIGH']);?></OPTION>
</SELECT></TD></TR>
<TR CLASS='even' VALIGN="top"><TD CLASS="td_label">Description: <font color='red' size='-1'>*</font></TD><TD><TEXTAREA NAME="frm_description" COLS="35" ROWS="4"></TEXTAREA></TD></TR>
<!--
<TR CLASS='even'><TD CLASS="td_label">Affected:</TD><TD><INPUT SIZE="48" TYPE="text" 	NAME="frm_affected" VALUE="" MAXLENGTH="48"></TD></TR>
-->
<?php
/*
	if (get_variable("restrict_user_add") && !($_SESSION['level'] == $GLOBALS['LEVEL_ADMINISTRATOR'])) {
		print "<INPUT TYPE=\"hidden\" NAME=\"frm_owner\" VALUE=\"$_SESSION[user_id]\">";
		}
	else {		//generate dropdown menu of users
		$result = mysql_query("SELECT id,user FROM $GLOBALS[mysql_prefix]user") or do_error('add.php::generate_owner_dropdown','mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		print '<TR><TD CLASS="td_label">Owner:</TD><TD><SELECT NAME="frm_owner">';
    	while ($row = stripslashes_deep(mysql_fetch_array($result))) {
			print "<OPTION VALUE=\"$row[id]\" ";
			if ($row[id] == $_SESSION[user_id]) print "SELECTED";
			print ">$row[user]</OPTION>";
			}
		print '</SELECT></TD></TR>';
		}
*/
?>
<TR CLASS='odd'><TD CLASS="td_label">Run Start: &nbsp;&nbsp;</TD><TD><?php print generate_date_dropdown('problemstart');?></TD></TR>
<TR CLASS='even' valign="middle"><TD CLASS="td_label">Run End: &nbsp;&nbsp;<input type="radio" name="re_but" onClick ="do_end();" /></TD><TD>
	<SPAN style = "visibility:hidden" ID = "runend1"><?php print generate_date_dropdown('problemend','' , TRUE);?></SPAN>
	</TD></TR>
<TR CLASS='odd' VALIGN="top"><TD CLASS="td_label">Comments:</TD><TD><TEXTAREA NAME="frm_comments" COLS="35" ROWS="4"></TEXTAREA></TD></TR>
<TR CLASS='even'><TD CLASS="td_label">Map: <font color='red' size='-1'>*</font></TD><TD ALIGN="center">Lat:<INPUT SIZE="12" TYPE="text" 			NAME="frm_lat" VALUE="" MAXLENGTH="12">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lon:&nbsp;&nbsp;<INPUT SIZE="12" TYPE="text" NAME="frm_lng" VALUE="" MAXLENGTH="12"></TD></TR>
<TR CLASS='odd'><TD COLSPAN="2" ALIGN="center"><BR /><INPUT TYPE="button" VALUE="Cancel"  onClick="document.can_Form.submit();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="reset" VALUE="Reset" onclick= "reset_end();" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="Submit"></TD></TR>
<TR CLASS='even'><TD COLSPAN="2" ALIGN="center"><br /><IMG SRC="glasses.png" BORDER="0"/>: Lookup fields</TD></TR>
</FORM></TABLE>
</TD><TD>
<TABLE ID='four'><TR><TD id='three' ALIGN='center'><div id='map' style='width: 500px; height: 500px'></div>
<BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR /><BR />

<A HREF='#' onClick='toglGrid()'><u>Grid</U></A></TD></TR /></TABLE>
</TD></TR>
</TABLE>
<?php 
	} //end if/else
?>
<FORM NAME='can_Form' ACTION="main.php">
</FORM>	
</BODY></HTML>
<?php /*
onLoad = "document.add.frm_lat.disabled.. added 10/28/2007
*/	?>
