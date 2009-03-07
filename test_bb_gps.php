<?php
/*
*/
error_reporting(E_ALL);									// 10/1/08
require_once('./incs/functions.inc.php'); 				// 11/21/08
$api_key = get_variable('gmaps_api_key');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"> 
  <head> 
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/> 
	<title>Google Maps JavaScript API Example: Blackberry GPS Integration</title> 
	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php print $api_key;?>"
			type="text/javascript"></script> 
	<script type="text/javascript"> 

	if (GBrowserIsCompatible()) {
		var map; 												//  note global
		var lat = 38.99;										// start
		var lng = -76.54;
		var prior_lat;
		var prior_lng;
		var prior = false;										// no start point
		var counter = 0;
	
	    function initialize() {
	        map = new GMap2(document.getElementById("map_canvas"));
	        map.setCenter(new GLatLng(parseFloat(lat), parseFloat(lng)), 12);
	
	        map.addControl(new GSmallMapControl());
	        map.addControl(new GMapTypeControl());
	        													 // un-comment to accept clicks
			GEvent.addListener(map, "click", function(marker, point) {				
				if (point) {
					lat = point.lat()
					lng = point.lng()
					add();
					}
				});				// end GEvent.addListener()
	        
	        } 		// end function initialize()
	 
		function add() {									// enteredd on point arrival
	        var blueIcon = new GIcon(G_DEFAULT_ICON);      
	        blueIcon.image = "http://gmaps-samples.googlecode.com/svn/trunk/markers/blue/blank.png";		
			markerOptions = { icon:blueIcon };				// Set up our GMarkerOptions object
	 
	        map.setCenter(new GLatLng(parseFloat(lat), parseFloat(lng)), 13);
	        var latlng = new GLatLng(parseFloat(lat), parseFloat(lng));
	        map.addOverlay(new GMarker(latlng, markerOptions));
	        
			if (prior){ 
				var polyline = new GPolyline([
				    new GLatLng(prior_lat, prior_lng),			// prior point
				    new GLatLng( lat, lng )						// current point
					], "#FF0000", 2);
				map.addOverlay(polyline);
				}
	
			prior = true;
			prior_lat = lat;
			prior_lng = lng;        
	
			counter++;
			document.getElementById("show_count").innerHTML = counter;
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
			var ua = "This is " +navigator.userAgent;
			alert(ua + "\n\nIt doesn't support the Blackberry Location API");
			}
		}
	else {
		alert("gmaps compatibility error"); 
		}
	</script> 
  </head> 
  <body onload="initialize()" onunload="GUnload()"> 
	<div id="map_canvas" style="width: 500px; height: 300px"></div> 
	<br />Blackberry - Google maps Integration -- 
	points: <span id='show_count'>0</span>
  </body> 
</html> 