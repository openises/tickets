<?php
	require_once('functions.inc.php');
	$api_key = get_variable('gmaps_api_key');		// empty($_GET)
	if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {  
		do_logout();
		exit();
		}
	else {
	do_login(basename(__FILE__));
		}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="REFRESH" CONTENT="180">	
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
	<SCRIPT>
	if(document.all && !document.getElementById) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}				

<?php
	print "var user = '";
	print $_SESSION['user_name'];
	print "'\n";
	print "\nvar level = '" . get_level_text ($_SESSION['level']) . "'\n";
?>	
		parent.frames["upper"].document.getElementById("whom").innerHTML  = user;
		parent.frames["upper"].document.getElementById("level").innerHTML  = level;
	</SCRIPT>
<script src="graticule.js" type="text/javascript"></script>
	
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
</HEAD>

<BODY onunload="GUnload()">
<?php
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		"";
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			"";
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	"" ;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	"" ;

	if ($get_print == 'true') {
		show_ticket($get_id,'true');
		print "<BR /><P ALIGN='left'>";
		}
	else if ($get_id) {
		add_header($get_id);
		show_ticket($get_id);
		print "<BR /><P ALIGN='left'>";
		}
	else if ($get_sort_by_field && $_GET['sort_value']) {
		list_tickets($get_sort_by_field, $get_sort_value);
		}
	else {
		list_tickets();
		}
?>
</BODY></HTML>
