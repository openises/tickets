<?php
/*
8/9/09	handle missing curl 
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<HEAD>
<TITLE>Google Latitude Test</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
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
<CENTER><H3>Google Latitude Test</H3>
<BR />
<BR />
<FORM NAME='glat_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
<B>Enter Public Location Badge</B>: <INPUT TYPE='text' NAME = 'frm_badge' SIZE = '24' value='' />
<BR />
<BR />
<INPUT TYPE='submit' VALUE='Go' />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE="button" VALUE = "Finished" onClick = "self.close()" /></FORM>
</BODY>
</HTML>

<?php
		}				// end if (empty($_POST)) {
	else {
		 
		@session_start();
		require_once($_SESSION['fip']); 

		function get_remote($url) {				// 8/9/09
			
				$data="";
				if (function_exists("curl_init")) {
					$ch = curl_init();
					$timeout = 5;
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					$data = curl_exec($ch);
					curl_close($ch);
					return ($data)?  json_decode($data): FALSE;			// FALSE if fails
					}
				else {				// no CURL
					if ($fp = @fopen($url, "r")) {
						while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
						fclose($fp);
						}		
					else {
//						print "-error 1";		// @fopen fails
						return FALSE;		// @fopen fails
						}
					}
		
			return json_decode($data);
		
			}	// end function get_remote()
		
//dump($json );
//dump($_POST);
$frm_badge = $_POST['frm_badge'];

$the_url = "http://www.google.com/latitude/apps/badge/api?user={$frm_badge}&type=json";

	function test_glat($user, $test_url) {				// given user id and url,  returns Google Latitude id, timestamp and coords as a 4-element array, if found - else FALSE
		$ret_val = array("", "", "", "");
		$json = get_remote($test_url) ;
		if(!($json)) return FALSE;
		
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
	
		return (!(empty($ret_val[0])) && (!(empty($ret_val[1])))  && (my_is_float($ret_val[2] )) && (my_is_float($ret_val[3])))? $ret_val : FALSE;

		}			// end function test_glat();

//	$user = "-681721551039318347";					// known good value
	$user = $_POST['frm_badge'];
	$results = test_glat($user, $the_url);
	$is_good = $results;
	$api_key = get_variable('gmaps_api_key');
	if ($is_good) {

?>	
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
<?php
		}
	else {
		print "\n</HEAD><BODY>\n";	
		}
?>		
  <CENTER>
  <br /><br />
  <H3>Google Latitude Test <?php print $is_good? "Succeeds":"Fails"; ?></H3>
	<H4>using public location badge: <?php print $_POST["frm_badge"]; ?></H4>
<?php
		if ($is_good) {print "<div id='map_canvas' style='width: 500px; height: 300px'></div>\n";}
?>
    <br />
    <br /><br /><input type='button' value="Again" onClick = 'location.href="<?php print basename(__FILE__); ?>"' />&nbsp;&nbsp;&nbsp;&nbsp;
  </body><input type='button' value="Finished" onClick = "self.close()" /><br /><br />
  </body>
</html>


<?php
	}				// end outer if/else
?>
</BODY>
</HTML>