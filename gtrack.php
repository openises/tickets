<?php
/*
7/29/09 Created test script for Gtrack vehicle tracking system.
3/15/11 changed stylesheet.php to stylesheet.php
*/



error_reporting(E_ALL);
 
@session_start();
session_write_close();
require_once($_SESSION['fip']); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Gtrack</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" 			CONTENT="7/29/09">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<?php
if (empty($_POST)) {
?>
</HEAD>
<BODY>
<BR />
<BR />
<BR />
<BR />
<CENTER><H3>Gtrack test</H3>
<BR />
<BR />
<FORM NAME='glat_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
Gtrack ID : <INPUT TYPE='text' NAME = 'frm_gtrack_id' SIZE = '5'/>
<BR />
<BR />
Gtrack URL: <INPUT TYPE='text' NAME = 'frm_gtrack_url' SIZE = '40'/>
<BR />
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

function do_gt($user, $url) {
	
		$request_url = "http://" . $url . "/data.php?userid=$user";		//change to reflect the server address
		$data="";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			}
		else {				// not CURL
			if ($fp = @fopen($request_url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				print "-error 1";		// @fopen fails
				}
			}

		$ret_array = new SimpleXMLElement($data);

	return $ret_array;

}	// end function do_gt()

	$user = $_POST['frm_gtrack_id'];
	$url = $_POST['frm_gtrack_url'];
	$xml = do_gt($user, $url);
	$caption = ($xml)? "Successful": "Fails";

	if ($xml) {
		$api_key = get_variable('gmaps_api_key');		// empty($_GET)

		$user_id = $xml->marker['userid'];
		$lat = $xml->marker['lat'];
		$lng = $xml->marker['lng'];

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
?>
			<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
			<script type="text/javascript" src="./js/Google.js"></script>
<?php 
			}
		}
?>
    <script type="text/javascript">

    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map_canvas"));
        map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>), 11);
        map.setUIToDefault();
        var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);		// marker to map center
        map.addOverlay(new GMarker(point));
      }
    }

    </script>
  </head>
  <body onload="initialize()" onunload="GUnload()">
  <CENTER>
  <br /><br />
  <H3>Gtrack Successful<br />
	with public ID: <?php print $user_id; ?></H3><br /><br />
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