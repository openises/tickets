<?php
require_once('functions.inc.php');
//do_login(basename(__FILE__));
$api_key = get_variable('gmaps_api_key');
$tablename = "what_ever"; 

extract($_POST);

$LI = (isset($LI))? $LI: 0;			// logged-in status
function ago($timestamp){
	$difference	= time()	-	$timestamp;
	$periods = array("second",	"minute",	"hour",	"day",	"week",	"month",	"years",	"decade");
	$lengths = array("60","60","24","7","4.35","12","10");
	for($j	=	0;	$difference	>=	$lengths[$j];	$j++)
		$difference	/=	$lengths[$j];
	$difference	=	round($difference);
	if($difference	!=	1)	$periods[$j].=	"s";
	$text =	"$difference $periods[$j]	ago";
	return	$text;
	}

function doText($instr) {							// protects/converts newlines for js and display
	return addcslashes(nl2br($instr), "\0..\37");
	}

$_title = "asasas";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<!--<TITLE>CRIS Situation Map</TITLE> -->
<META HTTP-EQUIV="Expires" 				CONTENT="0">
<META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<title><?php print basename(__FILE__); ?></title>
<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>" </SCRIPT>
<script src='js/sha1.js'></script>
	<style type="text/css">
	    v\:* {
	      behavior:url(#default#VML);
	    }

		#detailmap, #mapDiv { font: normal 10px verdana;}
		#detailmap { width: 250px; height: 150px; border:1px solid gray;}
		A:hover 							{text-decoration: underline; color: red;}
		TH:hover 							{text-decoration: underline; color: red;}
		td.mylink:hover 					{background-color: rgb(255, 255, 255); }
		.clean								{color:silver;}
		.dirty								{color:black;}
		.CHECKED							{background-color: black; color:white; }
		INPUT.button 						{background-color: rgb(255, 255, 255); }
		input.text:focus, textarea:focus	{background-color: lightyellow; color:black;}
		.center 							{font-size: medium; font-weight: bold; text-align: center; background-color:black; color: white; WIDTH:30%; }
		/* input:blur, textarea:blur		{background-color: white; color:black; font-size: +2;} */
		.new_opt							{background-color: gray; color:white;  }
	</style>
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<style>
#infowin 	{width:	150px; height: 100px}		
</style>
	</head>
<BODY background="./imgs/stripe_background.gif" onload = "load()"; onunload="GUnload();">
<CENTER><BR /><SPAN CLASS='heading2'><?php print $_title; ?></SPAN><BR /><BR />
<?php
//	print navbar("situation.php");	// navigation bar
?>
<table border=0>
<tr class="even" valign="middle"><td colspan=2 align="center"><h3>Current Incidents</h3></td></tr>
	<tr>
		<td width = 150 valign="top" class="odd" style="text-decoration: underline; color: #4444ff;">
			 <div id="side_bar"></div>
		</td>
		<td>
			 <div id="map" style="width: 720px; height: 600px"></div>
		</td>
	</tr>
<!--

(1, 'Airport closed', 1),
(2, 'Area evacuated', 1),
(3, 'Bridge closed', 1),
(4, 'Citizen injured - emergency', 1),
(5, 'Citizen injured - non emergency', 1),
(6, 'Citizen shelter opened', 1),
(8, 'Commodity distribution center opened', 1),
(9, 'Danger - Use extreme caution', 1),
(10, 'Debris blocking roads', 1),
(11, 'Emergency vehicles only', 1),
(12, 'Government offices closed', 1),
(13, 'Information center opened', 1),
(14, 'Looting reported', 1),
(15, 'Marine facility closed', 1),
(16, 'Power outage', 1),
(17, 'Residential damage', 1),
(18, 'Road out', 1),
(19, 'Roads closed', 1),
(20, 'Shelter opened', 1),
(21, 'Well water contaminated', 1);

-->
<tr class="even"><td colspan="2" align="center"><font size="-2">
		Legend:
Area &raquo; <img src="./markers/sm_red.png">
Airport closed  &raquo;<img src="./markers/sm_black.png">
Area evacuated  &raquo;<img src="./markers/sm_blue.png">
Bridge closed	&raquo; <img src="./markers/sm_gray.png">
Citizen injury - emergency &raquo; <img src="./markers/sm_green.png">
Citizen injury - non emergency  &raquo;<img src="./markers/sm_orange.png">
<br>
Citizen shelter  &raquo;<img src="./markers/sm_purple.png">
Commodity distribution center  &raquo;<img src="./markers/sm_red.png">
Danger - Use extreme caution.  &raquo;<img src="./markers/sm_white.png">
Debris blocking roads  &raquo;<img src="./markers/sm_yellow.png"> 
</font></td></tr>
</table>


<!-- fail gently if the browser has no Javascript -->
<noscript><b>JavaScript must be enabled in order for you to use Google Maps.</b> However, it seems JavaScript is either disabled or not supported by your browser. 
	To view Google Maps, enable JavaScript by changing your browser options, and thentry again.	</noscript>

<script type="text/javascript">
//<![CDATA[

var myMarkers = new Array (30);		// note some repeats
myMarkers[0] = "sm_red.png"; myMarkers[1] = "sm_black.png"; myMarkers[2] = "sm_blue.png"; myMarkers[3] = "sm_gray.png"; myMarkers[4] = "sm_green.png"; myMarkers[5] = "sm_orange.png"; myMarkers[6] = "sm_purple.png";
myMarkers[7] = "sm_red.png"; myMarkers[8] = "sm_white.png"; myMarkers[9] = "sm_yellow.png"; myMarkers[10] = "sm_black.png"; myMarkers[11] = "sm_blue.png"; myMarkers[12] = "sm_gray.png";
myMarkers[13] = "sm_green.png"; myMarkers[14] = "sm_orange.png"; myMarkers[15] = "sm_purple.png"; myMarkers[16] = "sm_red.png"; myMarkers[17] = "sm_white.png"; myMarkers[18] = "sm_yellow.png";
myMarkers[19] = "sm_black.png"; myMarkers[20] = "sm_blue.png"; myMarkers[21] = "sm_gray.png"; myMarkers[22] = "sm_green.png";
myMarkers[23] = "sm_orange.png"; myMarkers[24] = "sm_purple.png"; myMarkers[25] = "sm_red.png"; myMarkers[26] = "sm_white.png"; myMarkers[27] = "sm_yellow.png";


function getElement(aID){ 
	return (document.getElementById) ? document.getElementById(aID) : document.all[aID];
	} 

function writeConsole(content) {
	top.consoleRef=window.open('','myconsole',
		'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=0' +',resizable=0')
 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
		+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></html>'
		)				// end top.consoleRef.document.writeln()
 	top.consoleRef.document.close();
	}				// end function write Console(content)

var request;
var sqlStmt;   									//the SQL statement

function doSql(theString){
	sqlStmt = "query=" + encodeURIComponent(theString);
	var url="dosql_s.php";						// server_side sql processor
	httpRequest("POST",url,true);
	}				// end function sendData()
	
function handleCheck(){							//event handler for XMLHttpRequest
	if(request.readyState == 4){
		if (request.status == 200) {
			if (request.responseText.substring(0,1) == '-') {
				writeConsole(request.responseText) ;
				}	// otherwise success
			else {
				alert ("Deleting from CRIS database");
				document.reload_frm.submit();
				}
			}
		else {
			alert("63: A problem occurred with communicating between the XMLHttpRequest object and the server program.");
			}
		}		//end outer if
	}
function initReq(reqType,url,bool){				// Initialize a Request object that is already constructed 
	request.onreadystatechange=handleCheck;			// Specify the function that will handle the HTTP response */
	request.open(reqType,url,bool);
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
	request.send(sqlStmt);
	}
  	
function httpRequest(reqType,url,asynch){			// Wrapper function for constructing a Request object. Parameters:
	if(window.XMLHttpRequest){						// 		reqType: The HTTP request type such as GET or POST.
		request = new XMLHttpRequest();				// 		url: The URL of the server program.
		} 						//  	asynch: Whether to send the request asynchronously or not.
	else if (window.ActiveXObject){						
		request=new ActiveXObject("Msxml2.XMLHTTP");
		if (! request){
			request=new ActiveXObject("Microsoft.XMLHTTP");
			}
	 	}
	if(request)	{initReq(reqType,url,asynch);}		//the request could still be null if neither ActiveXObject
													//initializations succeeded
	else 		{alert("83: Your browser does not meet this application's requirements.  Contact Tech Support."); }
	}

function doDel (theID) {
	if (confirm('Please confirm DELETE action')) {
		doSql("DELETE FROM `incidents` WHERE `id` = " + theID + " LIMIT 1")
		return true;
		}
	else return false;
	}

function toNew(theform){
	if (theform.LI.value==1) {
		theform.submit();
		}
	else {	
		var myhash = 'f41c19c580f7b5b2914d71b45b593282066b6ab4';
		var reply = prompt('Edit password, please:', 'flame');
		if (hex_sha1(trim(reply.toLowerCase()))==myhash)  {
			theform.LI.value=1;
			theform.submit();}
		else {alert ('Fails!  Valid password needed.');
			return;}
		}				// end else {}
	}			// end function

function trim(instr) {
	return instr.replace(/(^\s+)([^\s]*)(\s+$)/, '$2');
	}
	
function shorten (instr) {
	var temp = (""+instr.length> 10)?  instr.substring (0, 10) + " ..." : instr;
	return temp;
	}

function deparens(instr) {									// returns a float
	var temp = instr.toString().replace(/[\(\)]/g, "" );	// strip open, close parens
	var artemp = temp.split('.');
	return  parseFloat(artemp[0] + "." + artemp[1].substr(0, 6));		// trim to six dec's
	}

function myclick(i) {									// Picks up the click and opens the corresponding info window
	GEvent.trigger(gmarkers[i], "click");
	}
var map;		// global
var gmarkers = [];				// arrays to hold copies of the markers and html used by the side_bar

function load () {
	if (GBrowserIsCompatible()) {		// Check to see if this browser can run the Google API
	
		var side_bar_html = "Click link for details<br><br>";		// Will collect the html which will eventually be placed in the side_bar
//		var gmarkers = [];				// arrays to hold copies of the markers and html used by the side_bar
		var htmls = [];		 			// because the function closure trick (whatever that is!) doesn't work there
		var center;
		var i = 0;
	
		function createTabbedMarker(point,label,tabs, decor, iconid) {		// Create a tabbed marker and set up the event window
//			alert (iconid);
//			icon.image = "./markers/marker" + letter + ".png";
		
			icon.image = "./markers/sm_black.png";
			icon.image = "./markers/" + myMarkers[iconid];

//			var marker = new GMarker(point);						// Accepts a variable number of tabs, passed in the 
			var marker = new GMarker(point, icon);					// Accepts a variable number of tabs, passed in the 
			var marker_num = gmarkers.length;						//   arrays htmls[] and labels[]
			marker.marker_num = marker_num;
			marker.tabs = tabs;
			gmarkers[marker_num] = marker;
			bounds.extend(point);
			
			GEvent.addListener(gmarkers[marker_num], "click", function() {
				marker.openInfoWindowTabsHtml(gmarkers[marker_num].tabs);
			
				var dMapDiv = document.getElementById("detailmap");
				var detailmap = new GMap2(dMapDiv);
				detailmap.addControl(new GSmallMapControl());
				
				detailmap.setCenter(point, 13);
	//			var marker2 = detailmap.addOverlay(marker.copy()); 					
				var marker2 = gmarkers[marker_num]; 					
				detailmap.addOverlay(marker2);
				});				// end GEvent.addListener()
				
			side_bar_html += decor + '<a href="javascript:myclick(' + marker_num + ')">' + label + '</a><br>';			// add a line to the sidebar html
			i++;
			map.addOverlay(marker);
			return marker;
			}
	
//		function myclick(i) {									// Picks up the click and opens the corresponding info window
//			GEvent.trigger(gmarkers[i], "click");
//			}
	
		var map = new GMap2(document.getElementById("map"));	// Display the map, with some controls and set the initial location 
	
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());
		map.addControl(new GOverviewMapControl());
		var baseIcon = new GIcon();										// Create a base icon specifing the
		baseIcon.shadow = "./markers/sm_shadow.png";					// shadow, icon dimensions, etc.
		baseIcon.iconSize = new GSize(12, 20);							// size per above
		baseIcon.shadowSize = new GSize(12, 20);
		baseIcon.iconAnchor = new GPoint(9, 14);
		baseIcon.infoWindowAnchor = new GPoint(9, 2);
		baseIcon.infoShadowAnchor = new GPoint(18, 25);
		var icon = new GIcon(baseIcon);
		
		map.setCenter(new GLatLng(<?php print get_variable("def_lat");?>, <?php print get_variable("def_lng");?>), <?php print get_variable("def_zoom");?>);		// defaults
		var bounds = new GLatLngBounds();						// coords of bounding box
		
		GEvent.addListener(map, "infowindowclose", function() {		// re-center after possible move
			load();
			});
	<?php
	
		$types = array();
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]event_types` ORDER BY `type`;";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row = mysql_fetch_array($result)) {
			$types[$row['id']] = $row['type'];
			}
	
		$query ="SELECT * FROM `$mysql_prefix$tablename`";		// each entry
		$resultj = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$rows = mysql_affected_rows ();
	/*
																								id
																								name
																								status
																								descr
																								auth
																								pora
																								coord_lat
																								coord_lng
																								a_outline
																								a_color
																								a_width
																								a_opacity
																								inc_types_id
																								by
																								on
																								ip
	*/	
		while ($row = mysql_fetch_array($resultj)) {
			$theText = $types[$row['inc_types_id']]	. "<br>" .  doText($row['descr']);	
//			dump ($theText);
			$decoration = (($row['pora']=="l")||($row['pora']=="a"))? "&bull; ": "";
	?>		
			var tabs = new Array() ;
			var point = new GLatLng(<?php print $row['coord_lat'];?>, <?php print $row['coord_lng'];?>) ; // substr($instring, 0, $limit-4) 
			var label="<?php print $row['name'];?>" ; 					// sidebar
			var toplabel = shorten("<?php print $row['name']; ?>");
			
			tabs.push(new GInfoWindowTab(toplabel,"<div class='infowin' ><?php print $row['name'] . '<br>' . $theText ;?>"));	
			
			var theLinks = 	"<br><br><a href='#' onClick='doEdit(" + <?php print $row['id'];?> + ")'>Update this</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; 
			theLinks +=  	"<a href='#' onClick='doDel(" + <?php print $row['id'];?> + ")'>Delete this</a></div>"; 
			
			tabs.push(new GInfoWindowTab("Add`l","Information: <?php print $row['auth'] ;?>" + theLinks));
			tabs.push(new GInfoWindowTab("Zoom",'<div id="detailmap"></div>'));
	
			var marker = createTabbedMarker(point,label,tabs, "<?php print $decoration;?>", <?php print $row['inc_types_id']; ?> );				// iconID 4/24
			var pora = "<?php print $row['pora'];?>";
			
			if ((pora=="a") || (pora=="l")) {				// area or line?
		        var points = []; 
		        var theStr= "<?php print $row['a_outline'];?>";
		        var tempAr = theStr.split('~');				// break the points 
		        for (var i = 0; i <tempAr.length; i++) { 
					var temp = tempAr[i].split(',');		
			        points.push(new GLatLng(deparens(temp[0]), deparens(temp[1]))); 
					bounds.extend(points[i]);
			        }
			    var color ="#<?php print $row['a_color'];?>";
			    var weight ="<?php print $row['a_width'];?>";
			    var opacity = ".<?php print $row['a_opacity'];?>";
			    
				switch(pora){		
					case "l":									// line
				        map.addOverlay(new GPolyline(points, color, weight, opacity)); 	// (points,  color,  weight,  opacity)
				    	break;
	
			    	case "a":									// area
				        map.addOverlay(new GPolygon(points, color, weight, opacity, color, .1)); 	// (points,  color,  weight,  opacity fillColor,  fillOpacity))
				        break;
					}		// end switch (pora)
				}		// end line or area
	<?php			
			}		// end while (...)
	?>		
			if (i>0) {
				var center_lat = (bounds.getNorthEast().lat() +bounds.getSouthWest().lat()) / 2.0;
				var center_lng = (bounds.getNorthEast().lng() +bounds.getSouthWest().lng()) / 2.0;
				center = new GLatLng(center_lat,center_lng)
				zoom = map.getBoundsZoomLevel(bounds);
				if (zoom>10) {zoom=10};
				map.setCenter(center,zoom);
				}
			else {
				map.openInfoWindow(map.getCenter(), document.createTextNode("No reported incidents at this time."));			
				}
			side_bar_html += "<br><br><a href='#' onclick='Javascript: toNew(document.sit_sum_frm);'>OEM Only</a>";
			side_bar_html += "<br><a href='situation.php'>Home</a>";
			document.getElementById("side_bar").innerHTML = side_bar_html;	// put the assembled side_bar_html contents into the side_bar div
			}
		else {							// display a warning if the browser isn't compatible
			alert("Sorry, Google Maps isn't compatible with your browser. Contact technical support for more information.");
			}
	
		}		// end function load()
	
	// This Javascript is based on code generously provided by the
	// Blackpool Community Church Javascript Team
	// http://www.commchurch.freeserve.co.uk/	 
		// http://www.econym.demon.co.uk/googlemaps/
		//]]>
		</script>
	<form name='sit_sum_frm' action = 'sit_map2.php' method='post'>
	<input type= 'hidden' NAME= 'LI' value='<?php echo $LI; ?>' >	<!-- logged-in -->
	</form>		
	<form name='reload_frm' action = "<?php print $_SERVER['PHP_SELF'] ?>" method='post'>
	<input type= 'hidden' NAME= 'LI' value='<?php echo $LI; ?>' >	<!-- logged-in -->
	</form>		
	</body>
</html>




