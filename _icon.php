<html>
<head>
<title>MapIconMaker - Simple</title>
 <script type="text/javascript"  src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BRi_j0U6kJrkFvY4-OX2XYmEAa76BSxM3tBbKeopztUxxRu-Em4ds4HHg">
 </script>
 <script src="./js/mapiconmaker.js" type="text/javascript"></script>
<script type="text/javascript">

function load() {
  if (GBrowserIsCompatible()) {
    var map = new GMap2(document.getElementById("map"));
    map.setCenter(new GLatLng(37.441944, -122.141944), 13);
    var newIcon = MapIconMaker.createLabeledMarkerIcon({addStar: false, label: "", primaryColor: "#ffffff"});
    var marker = new GMarker(map.getCenter(), {icon: newIcon});
    map.addOverlay(marker);
  }
}
</script>
</head>
<body onload="load()">
<div id="map" style="width: 500px; height: 300px"></div>
</body>
</html>
