<?php
/*
2/9/09 initial version
2/21/09 delete old tracks
3/3/09 disallow guests
*/
error_reporting(E_ALL);									// 10/1/08
require_once('./incs/functions.inc.php'); 				// 11/21/08
//$requested_page = "track_me_bb.php";
//do_login($requested_page, FALSE, TRUE) ;	

$hours_to_keep = 24;

$api_key = get_variable('gmaps_api_key');

$the_date = mysql_format_date(time() - (get_variable('delta_mins')*60));
$query = "DELETE from `$GLOBALS[mysql_prefix]tracks_hh` WHERE `updated` < ('{$the_date}' - INTERVAL {$hours_to_keep} HOUR)";
//dump($query);
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	// 

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"> 
<TITLE>Track hand-helds</TITLE>
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<?php
//		

	if((empty($_POST)) || (!(array_key_exists ("frm_unit_name", $_POST)))) {
	
//		if (is_guest()) {													// 3/3/09
//			print "</HEAD><BODY><CENTER><H3>No Guests, please.</H3>";
//			print "<BR /><BR /><INPUT TYPE='button' VALUE='log out' onClick = \"javascript:location.href ='hh_login.php'\">";
//			print "</CENTER></BODY></HTML>";
//			}
?>
<SCRIPT>
function wrapup(theForm) {
	if(theForm.frm_unit_id.value == ""){
		alert('Please make selection')
		return false;
		}
	else {
		document.sel_form.submit();
		}	
	}		// end function wrapup()
	
function changer(theForm) {
	theForm.frm_unit_id.value=theForm.frm_unit_sel.options[theForm.frm_unit_sel.selectedIndex].value;		// text
	theForm.frm_unit_name.value=theForm.frm_unit_sel.options[theForm.frm_unit_sel.selectedIndex].text;		// text
	}
</SCRIPT>
</HEAD>
<BODY><BR/><BR/><BR/>

<?php
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile` <> 0 ORDER BY `name` ASC";		// 2/21/09   
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if (mysql_affected_rows()==0) {
			print "<CENTER><H3> Error - no mobile responder units defined!</H3>";
			print "<BR /><BR /><INPUT TYPE='button' VALUE='log out' onClick = \"javascript:location.href ='hh_login.php'\">";
			print "</CENTER></BODY></HTML>";
			}
		else {
?>
		<TABLE ALIGN='center' BORDER=0 cellpadding = 4 cellspacing = 4>
		<FORM NAME='sel_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__); ?>'>
		<TR CLASS = 'even'><TH COLSPAN=99 ALIGN='center'>Select this unit/device</TH></TR>
		<TR CLASS="odd" VALIGN="baseline">
			<TD><SELECT name="frm_unit_sel" onChange = "changer(document.sel_form)" >
				<OPTION VALUE= '0' SELECTED>Select</OPTION>
<?php
			while ($row = mysql_fetch_assoc($result))  {
				print "\t\t<OPTION VALUE='" . $row['id'] . "'>" . $row['name'] . "</OPTION>\n";		
				}
			unset ($result);
			print "</SELECT></TD></TR>";
			print '<TR CLASS="even" VALIGN="baseline"><TD COLSPAN=99 ALIGN="center">';
			;
			print "<INPUT TYPE='button' VALUE='Next' onClick = wrapup(document.sel_form);>\n";
			print "<INPUT TYPE='hidden' NAME='frm_unit_name' VALUE=''>\n";		
			print "<INPUT TYPE='hidden' NAME='frm_unit_id' VALUE=''></FORM>\n";		
			print "</TD></TR></TABLE>\n</BODY></HTML>";
			}
		}
	else {
//		dump($_POST);
		$now = time() - (get_variable('delta_mins')*60);
?>

<SCRIPT SRC="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php print $api_key;?>"></SCRIPT> 
<SCRIPT>
	String.prototype.trim = function () {				// 9/14/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

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
	var thisMarker = false;		

	function sendRequest(url,callback,postData) {		// ajax function set - 1/17/09
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
			alert("error - 164");
			}
		else {
//			alert(req.responseText);
			}		// end else ...			
		}		// end function handleResult()
	
	function send_pos(the_lat, the_lng, the_id){	
		var params = "frm_lat=" + escape(the_lat) + "&frm_lng=" + escape(the_lng) + "&frm_id=" + escape(the_id);
//		alert(params);
		sendRequest ('add_hh_point.php',handleResult, params);		// (url,callback,postData) 
		}

	if (GBrowserIsCompatible()) {
		var map; 												// note global
		var lat = <?php echo get_variable('def_lat'); ?>;										// start
		var lng = <?php echo get_variable('def_lng'); ?>;
		var zoom = <?php echo get_variable('def_zoom'); ?>;
		var prior_lat;
		var prior_lng;
		var ident = "<?php print $_POST['frm_unit_id']; ?>";	// mobile unit ident
		var prior = false;										// no start point
		var counter = 0;
		var direcs=new Array("north.png","north_east.png","east.png","south_east.png","south.png","south_west.png","west.png","north_west.png", "north.png");	// 10/4/08
	
	    function initialize() {
	        map = new GMap2(document.getElementById("map_canvas"));
	        map.setCenter(new GLatLng(parseFloat(lat), parseFloat(lng)), parseInt(zoom));
	
	        map.addControl(new GSmallMapControl());
	        map.addControl(new GMapTypeControl());

<?php if ($istest) { ?>

			GEvent.addListener(map, "click", function(marker, point) {				
				if (point) {
					lat = point.lat().toFixed(6);
					lng = point.lng().toFixed(6);
					add();
					}
				});				// end GEvent.addListener()
<?php } ?>				
	        
	        } 		// end function initialize()
	 
		function add() {									// entered on point arrival
	 
	        var myIcon = new GIcon(G_DEFAULT_ICON);      
	        if (!prior) {
	        	myIcon.image = "./markers/start.png";
        		myIcon.shadow = ""; 	        	
				myIcon.iconSize = new GSize(16, 16);
				myIcon.iconAnchor = new GPoint(8, 8);
					        	}
	        else {
				var the_brng = brng(parseFloat(prior_lat), parseFloat(prior_lng), parseFloat(lat), parseFloat(lng));
				var heading = Math.round(the_brng/45);		//
		        myIcon.image = "./markers/" + direcs[heading];
        		myIcon.shadow = "";
				myIcon.iconSize = new GSize(16, 16);
				myIcon.iconAnchor = new GPoint(8, 8);
	        	}
			markerOptions = { icon:myIcon };				// Set up our GMarkerOptions object
	 
	        map.setCenter(new GLatLng(parseFloat(lat), parseFloat(lng)), 13);
	        var latlng = new GLatLng(parseFloat(lat), parseFloat(lng));
	        map.addOverlay(new GMarker(latlng, markerOptions));

			send_pos(lat, lng, ident);							// send position & id to server
			
			if (prior){ 
				var polyline = new GPolyline([
				    new GLatLng(prior_lat, prior_lng),			// prior point
				    new GLatLng( lat, lng )						// current point
					], "#FF0000", 2);
				map.addOverlay(polyline);
				}
			if (counter> 0) {
				var the_brng = brng(parseFloat(prior_lat), parseFloat(prior_lng), parseFloat(lat), parseFloat(lng));
				var heading = Math.round(the_brng/45);		//
				}
	
			prior = true;
			prior_lat = lat;
			prior_lng = lng;        
	
			counter++;
			document.getElementById("show_count").innerHTML = counter;	// 
			}				// end function add()
	 
		function locationCB() {		 							// called when bb location object changes - bb-specific
			lat = blackberry.location.latitude;
			lng = blackberry.location.longitude;
			add();												// add point to map canvas
			return true;
			}
	 
		if( window.blackberry && blackberry.location.GPSSupported) {	 // is the blackberry location API supported?
			blackberry.location.onLocationUpdate("locationCB()");
			blackberry.location.setAidMode(2);	 						// set to Autonomous mode
			blackberry.location.refreshLocation();	 					// refresh the location
			}
		else  {															// complain
			var ua = navigator.userAgent;
			alert(ua + " doesn't support the Blackberry Location API");
			}
		}
	else {
		alert("gmaps compatibility error"); 
		}
	</script> 
  </head> 
  <body onload="initialize()" onunload="GUnload()"> 
	<div id="map_canvas" style="width: 500px; height: 300px"></div> 
	<BR /><B>Unit: <?php print $_POST['frm_unit_name'];?></B>
	&nbsp;&nbsp;&nbsp;&nbsp;
	points: <span id='show_count'>0</span> since <?php print date(get_variable("date_format"),$now); ?> 
	<BR /><BR />Blackberry/Google maps/Tickets Integration
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN ID='logout' onClick = "javascript:location.href ='hh_login.php'"> <U>Log out</U></SPAN>
  </body> 
</html> 
<?php
	}		// end else
?>

