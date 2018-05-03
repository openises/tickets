<?php
error_reporting(E_ALL);
/*
8/13/10 handle non-curl configurations
8/25/10 revised to handle failures gracefully
3/15/11 changed stylesheet.php to stylesheet.php
*/
 
@session_start();
session_write_close();
require_once($_SESSION['fip']); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Latitude</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" 			CONTENT="6/22/09">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<?php
if (empty($_POST)) {
?>
</HEAD>
<BODY onLoad = "document.glat_form.frm_badge.focus();">
<BR />
<BR />
<BR />
<BR />
<CENTER><H3>Google Latitude test</H3>
<BR />
<BR />
<FORM NAME='glat_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
Badge: <INPUT TYPE='text' NAME = 'frm_badge' SIZE = '24' value='' />	<!-- ex: -681721551039318347 -->
<BR />
<BR />
<INPUT TYPE='submit' VALUE='Go' />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE="button" VALUE = "Finished" onClick = "self.close()" /></FORM>
</BODY>
</HTML>

<?php
		}				// end if (empty($_POST)) {
	else {
		require_once('./incs/functions.inc.php');

function do_glat_test($user) {				// given user id,  returns Google Latitude id, timestamp and coords as a 4-element array, if found - else FALSE
	$ret_val = array("", "", "", "");
	$the_url = "http://www.google.com/latitude/apps/badge/api?user={$user}&type=json";

	if (function_exists("curl_init")) {				// 8/13/10

		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $the_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		}
	else {										// not CURL
		$data = "";
		if ($fp = @fopen($the_url, "r")) {
			while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
			fclose($fp);
			}		
		else {
			$message = "CURL and FOPEN FAIL " . basename(__FILE__);
			}
		 }

	$json = json_decode($data);
	error_reporting(0);
	foreach ($json as $key => $value) {				// top
	    $temp = $value;
		foreach ($temp as $key1 => $value1) {		// 1
		    $temp = $value1;
			foreach ($temp as $key2 => $value2) {		// 2
				$temp = $value2;
				foreach ($temp as $key3 => $value3) {		// 3
					switch (strtolower($key3)) {
						case "id":
							$ret_val[0] = $value3;
						    break;
						case "timestamp":
							$ret_val[1] = $value3;
						    break;
						case "coordinates":
							$ret_val[2] = $value3[0];
							$ret_val[3] = $value3[1];
						    break;
						}		// end switch()
					}		// end for each()
		    	}		// end for each()
			}		// end for each()
		}
	error_reporting(E_ALL);

	foreach ($ret_val as $value) {				// any hole?
		if( empty($value)){
			return FALSE;
			}				// end if()
		}
	return $ret_val;
	}			// end function do_glat_test();

//	$user = "-681721551039318347";				// known good value
	$user = $_POST['frm_badge'];
	$results = do_glat_test($user);
	
	$caption = ($results)? "Successful": "Fails";
	$api_key = get_variable('gmaps_api_key');		// empty($_GET)
?>	

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Google Maps JavaScript API Example: Simple Map</title>
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
    <script type="application/x-javascript">

    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map_canvas"));
        map.setCenter(new GLatLng(<?php print $results[3];?>, <?php print $results[2];?>), 11);
        map.setUIToDefault();
        var point = new GLatLng(<?php print $results[3];?>, <?php print $results[2];?>);		// marker to map center
        map.addOverlay(new GMarker(point));
      }
    }

    </script>
  </head>
  <body onload="initialize()" onunload="GUnload()">
  <CENTER>
  <H3>Google Latitude Test <?php print $caption; ?><br />
	with public location badge: <?php print $_POST['frm_badge']; ?></H3>
	<input type='button' value="Again" onClick = 'location.href="<?php print basename(__FILE__); ?>"' />&nbsp;&nbsp;&nbsp;&nbsp;
  </body><input type='button' value="Finished" onClick = "self.close()" /><br /><br />
<?php	if ($results) { ?>	
    <div id="map_canvas" style="width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px"></div>
<?php } ?>    
  </body>
</html>	

<?php
	}				// end if/else
?>
</BODY>
</HTML>