<?php
/*
7/08/09 Created Incident Popup from track_u.php
7/29/09 Revised code for statistics display and background color determined by severity
3/12/10 added incident age to stats, revised display 
3/25/10 added 'dispatched' and 'cleared' to display
6/25/10 added year check to NULL for cleared assigns
7/4/10 added ticket details to head section
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/26/10 fmp added - AH
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
$ticket_severity = get_severity($row['severity']);
$ticket_type = get_type($row['in_types_id']);
$ticket_status = get_status($row['status']);
$ticket_updated = format_date_time($row['updated']);
$ticket_addr = "{$row['street']}, {$row['city']} {$row['state']} ";
$ticket_start = $row['problemstart'];		//
$ticket_end = $row['problemend'];		//
$ticket_start_str = format_date($row['problemstart']);		//
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Incident Popup - Incident <?php print $title;?> <?php print $ticket_updated;?></TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
	<STYLE type="text/css">
	.hover 	{ text-align: center; margin-left: 4px; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; background-color: #DEE3E7; font-weight: bolder;}
	.plain 	{ text-align: center; margin-left: 4px; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; background-color: #EFEFEF; font-weight: bolder;}
  	</STYLE>	
	<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $key_str;?>&libraries=geometry&sensor=false"></SCRIPT>	<!-- 4/23/13 -->
	<SCRIPT TYPE="text/javascript" src="./js/elabel_v3.js"></SCRIPT> 	<!-- 4/23/13 -->
	<SCRIPT TYPE="text/javascript" SRC="./js/gmaps_v3_init.js"></script>	<!-- 4/23/13 -->
	<SCRIPT>
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
	
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {
		CngClass(the_id, 'plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

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

echo "<BODY style='background-color:{$severities[$row['severity']]}; text-color: {$colors[$row['severity']]};' onLoad = 'ck_frames();' onUnload='GUnload();'>";
echo "<TABLE ALIGN = 'center'><TR><TD>";

/* Creates statistics header and details of responding and en-route units 7/29/09 */

$result_dispatched = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
	WHERE ticket_id='$id'
	AND `dispatched` IS NOT NULL 
	AND `responding` IS NULL 
	AND `on_scene` IS NULL 
	AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))");		// 6/25/10
$num_rows_dispatched = mysql_num_rows($result_dispatched);

$result_responding = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
	WHERE ticket_id='$id'
	AND `responding` IS NOT NULL 
	AND `on_scene` IS NULL 
	AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))");		// 6/25/10
$num_rows_responding = mysql_num_rows($result_responding);

$result_on_scene = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]assigns` 
	WHERE ticket_id='$id' 
	AND `on_scene` IS NOT NULL 
	AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')	
	");		// 6/25/10
$num_rows_on_scene = mysql_num_rows($result_on_scene);
	
$query = "SELECT *,UNIX_TIMESTAMP(as_of) AS as_of, UNIX_TIMESTAMP(problemstart) AS problemstart, 
	`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
	`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
	`r`.`id` AS `unit_id`,
	`r`.`name` AS `unit_name` ,
	`r`.`type` AS `unit_type` ,
	`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
	FROM `$GLOBALS[mysql_prefix]assigns` 
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		WHERE (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00')
		AND ticket_id='$id' ";

$result_cleared  = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
$num_rows_cleared = mysql_affected_rows();
$ticket_end = ($ticket_end > 1)? $ticket_end:  (time() - (get_variable('delta_mins')*60));
$elapsed = my_date_diff($ticket_start, $ticket_end);		// 5/13/10
echo "<BR /><B>Ticket:&nbsp;{$title}<BR />Opened:&nbsp;{$ticket_start_str},&nbsp;&nbsp;&nbsp;&nbsp;Status: {$ticket_status}</B><BR />";
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
	echo "{$row_base['unit_name']}:&nbsp;{$row_base['handle']}&nbsp;&nbsp;";
	}

echo "</B><BR><BR>";

	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;

	if ($get_id) {
		if($_SESSION['internet']) {
			popup_ticket($get_id);
		} else {
			show_ticket($get_id, $print='false', $search = FALSE);
			}
		}

echo "<CENTER><BR /><BR clear=all/><BR /><SPAN STYLE='background-color:white; font-weight:bold; color:black;'>&nbsp;{$ticket_addr}&nbsp;</SPAN></CEMTER>";
echo "<BR /><BR /><BR />";
echo "<CENTER><SPAN id='fin_button' class='plain' style='text-align: center;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Finished</SPAN></CENTER>";

?>
<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>'>
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE=''>
</FORM>
</TD></TR></TABLE>
</BODY></HTML>
