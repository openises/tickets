<?php
/*
7/25/09	initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('incs/functions.inc.php');		//7/28/10
$api_key = get_variable('gmaps_api_key');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Test Tickets Tracker</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css">	<!-- 3/15/11 -->
<?php
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
if((array_key_exists('HTTPS', $_SERVER)) && ($_SERVER['HTTPS'] == 'on')) {
	$gmaps_url =  "https://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
	} else {
	$gmaps_url =  "http://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
	}
?>
<SCRIPT TYPE="text/javascript" src="<?php print $gmaps_url;?>"></SCRIPT>
<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
<SCRIPT SRC="../js/graticule.js" type="text/javascript"></SCRIPT>

<SCRIPT>
	function $() {								// 1/23/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}
</SCRIPT>
</HEAD>
<BODY>
<?php

if (!(empty($_POST))) {

$user = $_POST['dev_key'];

function do_gt($user) {
		$ret_array = array();
		$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]remote_devices` WHERE `user` = '$user'";	//	read location data from incoming table
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
		if($result) {
		while ($row = @mysql_fetch_assoc($result)) {
			$id = $row['user'];
			$ret_array[0] = $id;
			$lat = $row['lat'];
			$ret_array[1] = $lat;
			$lng = $row['lng'];
			$ret_array[2] = $lng;
			$time = $row['time'];
			$ret_array[3] = $time;
			}	// end while	
		} else {
			print "-error 1";
		}
	return $ret_array;
	}	// end function do_gt()


	
$ary = do_gt($_POST['dev_key']) ;
if($ary) {
	$usr_id = $ary[0];
	$usr_lat = $ary[1];
	$usr_lng = $ary[2];
	$up_time = $ary[3];
}

if (!(isset($usr_id))) {
?>
	<BR />
	<FORM NAME= 'frm_locatea' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
	<TABLE ALIGN='center'>
	<TR CLASS  = 'even'><TH COLSPAN=2>Tickets Tracker Test Fails for User: <?php print $_POST['dev_key'];?></TH></TR>

	<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR /><BR />
		<INPUT TYPE='button' VALUE = 'Another' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE='button' VALUE = 'Cancel' onClick = 'window.close();' />
	</TD></TR></TABLE>
<?php
	} else {
?>
	<FORM NAME= 'frm_t_tracker' METHOD='get' ACTION = '<?php print basename(__FILE__);?>'>
	<TABLE ALIGN='center'>
	<TR><TH COLSPAN=2>Tickets Tracker Test Succeeds for User ID: <?php print $_POST['dev_key'];?></TH></TR>
	<TR><TD COLSPAN = '2' align='center'><DIV ID='map_canvas' style='width: 400px; height: 400px;'></DIV></TD></TR>
	<TR CLASS='odd'><TD class='td_label'>User ID:</TD><TD class='td_data'><?php print $usr_id;?></TD></TR>
	<TR CLASS='even'><TD class='td_label'>lat</TD><TD class='td_data'><?php print $usr_lat;?></TD></TR>
	<TR CLASS='odd'><TD class='td_label'>Lng:</TD><TD class='td_data'><?php print $usr_lng;?></TD></TR>
	<TR CLASS='even'><TD class='td_label'>Time:</TD><TD class='td_data'><?php print $up_time;?></TD></TR>
	<TR><TD COLSPAN = '2' ALIGN='center'><HR SIZE=1 COLOR='blue' WIDTH='75%'></TD></TR>
	<SCRIPT>
		
	// map = new GMap2($("map_canvas"));
	// map.setUIToDefault();
	// map.enableScrollWheelZoom();
	// map.addControl(new GLargeMapControl());
	// map.setCenter(new GLatLng(<?php print $usr_lat;?>, <?php print $usr_lng;?>),9); 
	// var point = new GLatLng(<?php print $usr_lat;?>, <?php print $usr_lng;?>);		
	// var marker = new GMarker(point, 1);
	// map.addOverlay(marker);
	

    var map = new GMap2($("map_canvas"),{size: new GSize(400,400)}); 
//	map = new GMap2(document.getElementById("map_canvas")); 
	var point = new GLatLng(<?php print $usr_lat;?>, <?php print $usr_lng;?>);
	map.setCenter(point, 9); 
	var marker = new GMarker(point, 1);	
	map.addOverlay(marker);		

	</SCRIPT>
	<TR CLASS  = 'odd'><TD COLSPAN=2 ALIGN='center'><BR /><BR />
		<INPUT TYPE='button' VALUE = 'Another' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE='button' VALUE = 'Cancel' onClick = 'window.close();' />
	</TD></TR></TABLE></FORM>
<?php
	}
} else {
?>
<BR /><BR />
<TABLE ALIGN = 'center' BORDER = 0>
	<TR CLASS  = 'even'><TH COLSPAN=2>Tickets Tracker Test</TH></TR>
	<FORM NAME= 'frm_t_tracker' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
	</TD></TR>
	<TR CLASS  = 'odd'><TD>User ID:</TD><TD><INPUT NAME = 'dev_key' TYPE = 'text' SIZE = '30' VALUE=''>	<BR /><BR /></TD></TR>
	<TR CLASS  = 'even'><TD COLSPAN=2 ALIGN='center'>
		<INPUT TYPE='button' VALUE = 'Test' onClick = 'this.form.submit();' />&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE='button' VALUE = 'Cancel' onClick = 'window.close();' />
	</TD></TR>
</TABLE>
<?php
	}		// end else {}
?>	

</BODY>
</HTML>
