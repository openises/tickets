<?php
/*
7/08/09 Created Incident Popup from track_u.php
7/29/09 Revised code for statistics display and background color determined by severity
*/

error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
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

$remotes = get_current();							// set auto-refresh if any mobile units														
$interval = intval(get_variable('auto_poll'));
$refresh = ((($remotes['aprs']) || ($remotes['instam']) || ($remotes['locatea']) || ($remotes['gtrack']) || ($remotes['glat'])) && ($interval>0))? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>\n": "";
$temp = get_variable('auto_poll');
$poll_val = ($temp==0)? "none" : $temp ;
$id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;

$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'");
$row = mysql_fetch_assoc($result);
$title = $row['scope'];
$ticket_severity = get_severity($row['severity']);
$ticket_type = get_type($row['in_types_id']);
$ticket_status = get_status($row['status']);
$ticket_updated = format_date_time($row['updated']);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Incident Popup - Incident <?php print $title;?> <?php print $ticket_updated;?></TITLE>

<?php print $refresh; ?>
	
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
	<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>

<?php
	print "<SCRIPT>\n";
?>
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



switch($ticket_severity) {			// Background color determined by Ticket Severity 7/29/09

	case "normal" :
	?>	
	<BODY style="background-color:#0066FF;" onload = "ck_frames();" onunload="GUnload();">
	<A NAME='top'>
	<FONT COLOR="white">
	<?php
	break;

	case "medium" :
	?>
	<BODY style="background-color:#00FF00;" onload = "ck_frames();" onunload="GUnload();">
	<A NAME='top'>
	<FONT COLOR="black">
	<?php
	break;

	case "high" :
	?>
	<BODY style="background-color:#F80000;" onload = "ck_frames();" onunload="GUnload();">
	<A NAME='top'>
	<FONT COLOR="yellow">
	<?php
	break;
}

/* Creates statistics header and details of responding and en-route units 7/29/09 */

	$result_responding = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id' AND `responding` IS NOT NULL AND `on_scene` IS NULL AND `clear` IS NULL");
	$num_rows_responding = mysql_num_rows($result_responding);

	$result_on_scene = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id' AND `on_scene` IS NOT NULL AND `clear` IS NULL");
	$num_rows_on_scene = mysql_num_rows($result_on_scene);

$stats = "Severity:&nbsp;" . $ticket_severity . "<BR>" . "Tot units en-route&nbsp;=&nbsp;" . $num_rows_responding . "&nbsp;&nbsp;&nbsp;---&nbsp;&nbsp;&nbsp;Tot units on scene&nbsp;=&nbsp;" . $num_rows_on_scene . "<BR>" . "Units on scene:&nbsp;";

echo $stats;

	while ($row_on_scene = mysql_fetch_array($result_on_scene, MYSQL_ASSOC)) {
	$responder = $row_on_scene['responder_id'];		
		$result_resp = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder'");
		$row_resp = mysql_fetch_assoc($result_resp);
		$resp_name = $row_resp['name'];
		$handle = $row_resp['handle'];

	$responding = $resp_name . ":&nbsp;" . $handle . ",&nbsp;&nbsp;";
	echo $responding;
	}

echo "<BR>Units en-route: ";

	while ($row_responding = mysql_fetch_array($result_responding, MYSQL_ASSOC)) {
	$responder = $row_responding['responder_id'];		
		$result_resp = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder'");
		$row_resp = mysql_fetch_assoc($result_resp);
		$resp_name = $row_resp['name'];
		$handle = $row_resp['handle'];

$enroute = $resp_name . ":&nbsp;" . $handle . ",&nbsp;&nbsp;";
echo $enroute;
}

echo "</FONT><BR><BR>";

	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	snap(basename(__FILE__) . __LINE__, $get_id);
	if ($get_id) {
		popup_ticket($get_id);
		}
?>
<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>'>
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE=''>
</FORM>
</BODY></HTML>
