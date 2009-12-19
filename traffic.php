<?php
	require_once('./incs/functions.inc.php');
	$api_key = get_variable('gmaps_api_key');
/*
1/21/09 added show butts - re button menu
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Traffic Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
		<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key=<?php print $api_key; ?>"
			type="text/javascript"></script>
		<script src="./js/extmaptypecontrol.js" type="text/javascript"></script>
		<script type="text/javascript">

		try{
			parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $my_session['user_name'];?>";
			parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($my_session['level']);?>";
			parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
			}
		catch(e) {
			}
		
	    var map;
	    
	    var trafficInfo = new GTrafficOverlay();
	    var toggleState = 1;
		
	    function toggleTraffic() {
	    	if (toggleState == 1) {
		        map.removeOverlay(trafficInfo);
		        toggleState = 0;
		     	} 
			else {
		        map.addOverlay(trafficInfo);
		        toggleState = 1;
		    	}
		    }				// end function toggleTraffic()

		function load() {
			if (GBrowserIsCompatible()) {
				map = new GMap2(document.getElementById("map"));
				map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
				map.addControl(new ExtMapTypeControl({showTraffic: true, showTrafficKey: true}));
				map.addControl(new GSmallMapControl());
		        map.addOverlay(trafficInfo);
		        map.enableScrollWheelZoom(); 	

				}
			}		// end function load()
		//]]>
		</script>
<STYLE>
 BODY { BACKGROUND-COLOR: #EEEEEE; FONT-WEIGHT: normal; FONT-SIZE: 12px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
</STYLE>
<SCRIPT>
function ck_frames() {		//  onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();										// 1/21/09
		}
	}		// end function ck_frames()
	
</SCRIPT>
	</head>

	<BODY onload="ck_frames(); load()" onunload="GUnload()">
    <center><font size="+1"><b><nobr>Click map traffic light for display&nbsp;&nbsp;<img src="traffic.png" border=0></nobr></b></font><br/><br/>	
		<div id="map" style="width: 960px; height: 600px"></div>
    <br clear="all"/>
    <br/>
    <input type="button" value="Toggle Traffic" onClick="toggleTraffic();"/>
    <br/>
		
	</center></body>
</html>
