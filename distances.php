<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Saefern</title>
<SCRIPT type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BTcqCCC0L2PGwpMHagbpUz5UypCABQASgZU9oiwa2VI2x6B95iXYOM7rg"></SCRIPT>

    <script type="text/javascript">
    //<![CDATA[
    var km2feet = 3280.83;
    var lastmarker = null;
	var map;
    function load() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());	// 
		map.addControl(new GScaleControl());	// 

	GEvent.addListener(map, "click", function(marker, point) {
//		if (marker) {
//			map.removeOverlay(marker);
//			document.add.frm_lat.disabled=document.add.frm_lat.disabled=false;
//			document.add.frm_lat.value=document.add.frm_lng.value="";
//			document.add.frm_lat.disabled=document.add.frm_lat.disabled=true;
//			}
		if (point) {
			if (document.mapform.lat1.value=="") {
				do_lat1 (point.lat())				// display
				do_lng1 (point.lng())
				map.addOverlay(new GMarker(point));	// GLatLng.
				}
			else {
				if (!document.mapform.lat2.value=="") {
					map.removeOverlay(lastmarker);
					}
				do_lat2 (point.lat())				// display
				do_lng2 (point.lng())
				var marker = new GMarker(point);
				lastmarker = marker;
				map.addOverlay(marker);	// GLatLng. 
				var km=distCosineLaw(parseFloat(document.mapform.lat1.value), parseFloat(document.mapform.lng1.value), parseFloat(document.mapform.lat2.value), parseFloat(document.mapform.lng2.value));
				var dist = ((km * km2feet).toFixed(1));
				document.getElementById("length").innerHTML  = dist + " feet";
				}
			}
		});
        map.setCenter(new GLatLng(39.017123, -76.542024), 17, G_SATELLITE_MAP); 
      }
    }

    //]]>
    
function do_lat1 (lat) {
	document.mapform.lat1.disabled=false;
	document.mapform.lat1.value=lat.toFixed(6);
	document.mapform.lat1.disabled=true;
	}
function do_lng1 (lng) {
	document.mapform.lng1.disabled=false;
	document.mapform.lng1.value=lng.toFixed(6);
	document.mapform.lng1.disabled=true;
	}
function do_lat2 (lat) {
	document.mapform.lat2.disabled=false;
	document.mapform.lat2.value=lat.toFixed(6);
	document.mapform.lat2.disabled=true;
	}
function do_lng2 (lng) {
	document.mapform.lng2.disabled=false;
	document.mapform.lng2.value=lng.toFixed(6);
	document.mapform.lng2.disabled=true;
	}
Number.prototype.toRad = function() {  // convert degrees to radians
	return this * Math.PI / 180;
	}

distCosineLaw = function(lat1, lon1, lat2, lon2) {
	var R = 6371; // earth's mean radius in km
	var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
			Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
	return d;
	}

</script>
  </head>

  <body onload="document.mapform.reset(); load()" onunload="GUnload()"><center>
    <div id="map" style="width: 1200px; height: 640px"></div><br>
    <form name="mapform"> First point:
    <input type="text" size=10 name="lat1" value="" disabled >&nbsp;&nbsp;
    <input type="text" size=10 name="lng1" value="" disabled>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Second point:
    <input type="text" size=10 name="lat2" value="" disabled>&nbsp;&nbsp;&nbsp;
    <input type="text" size=10 name="lng2" value="" disabled>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Distance: <span id="length"></span></b></center>
    <br><center><input type="reset" onClick="map.clearOverlays();"></center>
  </body>
</html>

