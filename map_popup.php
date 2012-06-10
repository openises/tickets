<?php
/*
9/6/10 initial release
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);		// 8/26/10
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

$temp = get_variable('auto_poll');
$poll_val = ($temp==0)? "none" : $temp ;
$id =	(array_key_exists('id', ($_GET)))?	$_GET['id']  :	NULL;

$result = mysql_query("SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart ,UNIX_TIMESTAMP(problemend) AS problemend FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'");
$row = mysql_fetch_assoc($result);
$title = $row['scope'];
$ticket_addr = "{$row['street']}, {$row['city']} {$row['state']} ";
$ticket_updated = format_date_time($row['updated']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Incident <?php print $title;?> <?php print $ticket_updated;?></TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css" />	<!-- 3/15/11 -->
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
<BODY><CENTER>
<?php
	$get_id = (array_key_exists('id', ($_GET)))? $_GET['id'] : NULL;

	if ($get_id) {
		if($_SESSION['internet']) {
			popup_ticket($get_id);
		} else {
			show_ticket($get_id, $print='false', $search = FALSE);
			}
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
</TD></TR></TABLE>
</BODY></HTML>
