<?php
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" 			CONTENT="6/22/09">
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<?php
if (empty($_POST)) {
?>
</HEAD>
<BODY>
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

//dump($json );
//dump($_POST);

function do_galt($user) {				// given user id,  returns Google Latitude id, timestamp and coords as a 4-element array, if found - else FALSE
	$ret_val = array("", "", "", "");
	$the_url = "http://www.google.com/latitude/apps/badge/api?user={$user}&type=json";
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $the_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
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
	}			// end function do_galt();

//	$user = "-681721551039318347";				// known good value
	$user = $_POST['frm_badge'];
	$results = do_galt($user);
	$caption = ($results)? "Successful": "Fails";

	if ($results) {
		$api_key = get_variable('gmaps_api_key');		// empty($_GET)

echo $results[3];
echo "<br>";
echo $results[2];
?>	

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Google Maps JavaScript API Example: Simple Map</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=<?php print $api_key;?>"
            type="text/javascript"></script>
    <script type="text/javascript">

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
  <br /><br />
  <H3>Google Latitude Test Successful<br />
	with public location badge: <?php print $results[0]; ?></H3><br /><br />
    <div id="map_canvas" style="width: 500px; height: 300px"></div>
    <br /><br /><input type='button' value="Again" onClick = 'location.href="<?php print basename(__FILE__); ?>"' />&nbsp;&nbsp;&nbsp;&nbsp;
  </body><input type='button' value="Finished" onClick = "self.close()" /><br /><br />
  </body>
</html><?php
		}
	else {
?>


<?php
		}		// end else
?>		
	
	
	
	
	








<?php
	}				// end outer else

?>
</BODY>
</HTML>