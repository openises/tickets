<?php
/*
7/28/10 Initial Release - no maps version of incident popup.
3/15/11 changed stylesheet.php to stylesheet.php
*/

error_reporting(E_ALL);
 
@session_start();
session_write_close();
require_once($_SESSION['fip']); 
$api_key = get_variable('gmaps_api_key');		// empty($_GET)

if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {
	do_logout();
	exit();
	}
else {
//	snap(__LINE__, basename(__FILE__));
	do_login(basename(__FILE__));
	}
if ($istest) {
	print "GET<BR/>\n";
	if (!empty($_GET)) {
		dump ($_GET);
		}
	print "POST<BR/>\n";
	if (!empty($_POST)) {
		dump ($_POST);
		}
	}

//	$remotes = get_current();							// set auto-refresh if any mobile units														
//	$interval = intval(get_variable('auto_poll'));
//	$refresh = ((($remotes['aprs']) || ($remotes['instam']) || ($remotes['locatea']) || ($remotes['gtrack']) || ($remotes['glat'])) && ($interval>0))? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>\n": "";
$temp = get_variable('auto_poll');
$poll_val = ($temp==0)? "none" : $temp ;
$id =	(array_key_exists('id', ($_GET)))?	$_GET['id']  :	NULL;

$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'");
$row = mysql_fetch_assoc($result);
$title = $row['scope'];
$ticket_severity = get_severity($row['severity']);
$ticket_type = get_type($row['in_types_id']);
$ticket_status = get_status($row['status']);
$ticket_updated = format_date_time($row['updated']);
$ticket_addr = "{$row['street']}, {$row['city']} {$row['state']} ";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Incident Popup - Incident <?php print $title;?> <?php print $ticket_updated;?></TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
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
	print "<SCRIPT>\n";
?>
<script>
	function ck_frames() {
		}

	function $() {
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

	/* function $() Sample Usage:
	var obj1 = document.getElementById('element1');
	var obj2 = document.getElementById('element2');
	function alertElements() {
	  var i;
	  var elements = $('a','b','c',obj1,obj2,'d','e');
	  for ( i=0;i
	  }
	*/


	</SCRIPT>
	
</HEAD>
<?php
$severities = $colors = array();
$severities[$GLOBALS['SEVERITY_NORMAL']] = "#DEE3E7";
$severities[$GLOBALS['SEVERITY_MEDIUM']] = "#00FF00";
$severities[$GLOBALS['SEVERITY_HIGH']] = "#F80000";

$colors[$GLOBALS['SEVERITY_NORMAL']] = "black";
$colors[$GLOBALS['SEVERITY_MEDIUM']] = "black";
$colors[$GLOBALS['SEVERITY_HIGH']] = "yellow";

echo "<BODY style='background-color:{$severities[$row['severity']]}; text-color: {$colors[$row['severity']]};' onload = 'ck_frames();' >";


/* Creates statistics header and details of responding and en-route units 7/29/09 */

$result_dispatched = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'
	AND `dispatched` IS NOT NULL AND `responding` IS NULL AND `on_scene` IS NULL AND `clear` IS NULL");
$num_rows_dispatched = mysql_num_rows($result_dispatched);

$result_responding = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'
	AND `responding` IS NOT NULL AND `on_scene` IS NULL AND `clear` IS NULL");
$num_rows_responding = mysql_num_rows($result_responding);

$result_on_scene = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id' 
	AND `on_scene` IS NOT NULL AND `clear` IS NULL");
$num_rows_on_scene = mysql_num_rows($result_on_scene);
	
$result_cleared = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id' 
	AND `clear` IS NOT NULL");
$num_rows_cleared = mysql_num_rows($result_cleared);
	
$end_date = (is_date($row['problemend']))? totime($row['problemend']) : (time() - (get_variable('delta_mins')*60));
$elapsed = my_date_diff($end_date, totime($row['problemstart']));		// integer values req'd - 3/12/10

$stats = "<B>Severity:&nbsp;{$ticket_severity}, <SPAN STYLE='background-color:white; color:black;'>&nbsp;age: $elapsed&nbsp;</SPAN>";

echo $stats;

echo "<BR>Units dispatched:&nbsp;({$num_rows_dispatched})&nbsp;";
while ($row_base= mysql_fetch_array($result_dispatched, MYSQL_ASSOC)) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
	$row = mysql_fetch_assoc($result);
	echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
	}

echo "<BR>Units responding: ($num_rows_responding)&nbsp;";
while ($row_base= mysql_fetch_array($result_responding, MYSQL_ASSOC)) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
	$row = mysql_fetch_assoc($result);
	echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
	}

echo "<BR>Units on scene: ($num_rows_on_scene)&nbsp;";
while ($row_base= mysql_fetch_array($result_on_scene, MYSQL_ASSOC)) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
	$row = mysql_fetch_assoc($result);
	echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
	}

echo "<BR>Units clear:&nbsp;({$num_rows_cleared})&nbsp;";
while ($row_base= mysql_fetch_array($result_cleared, MYSQL_ASSOC)) {
	$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='{$row_base['responder_id']}'");
	$row = mysql_fetch_assoc($result);
	echo "{$row['name']}:&nbsp;{$row['handle']}&nbsp;&nbsp;";
	}


echo "</B><BR><BR>";

	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
//	snap(basename(__FILE__) . __LINE__, $get_id);
	if ($get_id) {
		popup_ticket($get_id);
		}

echo "<CENTER><br clear = 'both'/><br /><br /><SPAN STYLE='background-color:white; font-weight:bold; color:black;'>&nbsp;{$ticket_addr}&nbsp;</SPAN>" ;
echo "<BR /><BR /&nbsp;><U><SPAN onClick = 'window.close();' STYLE='background-color:white; font-weight:bold; color:black; text-decoration:underline'>Finished</SPAN></U>&nbsp;</CENTER>";
?>
<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>'>
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE=''>
</FORM>
</BODY></HTML>
