<?php
/*
8/13/2017 initial release
Installation:
1.  Save your existing file ics213.php as a possible fallback.  (This is the only file replaced in the attached zip.)
2.  Unzip this zipfile into your existing Tix application root directory.  You will see one new file there, ics.php, and a new directory, ics.
3.  For access, use your existing ICS links, whether in Top or Config.
4.  Your first link here will install the new DB table, which should happen quietly and quickly.

Operation:
5.  Your existing Contacts table provides the candidate email addresses.
6.  Mail is sent using PHP native mail, rather than any SMTP links you may otherwise be using.  (This might pose a problem if you're running off localhost, which often doesn't support native PHP mail.)
7.  Form aesthetics is a work-in-progress.  Chrome and Firefox seem to behave OK, at least on a preliminary basis.  IE's presentation/appearance needs, and is receiving, some work.
8.  Operation should be clear from the top-left button links on each processor page.  ('Save to DB' is the important new function.)
9.  Once a form file is created, you may update, save, and mail it any number of times.  A form may be 'archived' in order to get it out of the way.  Only archived forms may be deleted.  Archived forms may be 'de-archived'.
10.  Within any form, you may navigate from field-to-field by tapping the Tab key.  The field ready to accept input is highlighted with a yellow background.
11.  As in the earliest version, the form is sent as mail, and not as, say, a PDF attachment.  I think this is an improvement, but YMMV so let us know reactions.



*/

if ( !defined ( 'E_DEPRECATED' ) ) { define ( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting ( E_ALL ^ E_DEPRECATED);
require_once ( './incs/functions.inc.php');
/**/
$limit = 99999;
//dump ( !mysql_table_exists ( "$GLOBALS[mysql_prefix]ics") );
if (!mysql_table_exists( "$GLOBALS[mysql_prefix]ics")) {
//	dump ( __LINE__ );

	$query = "CREATE TABLE `$GLOBALS[mysql_prefix]ics` (
	 `id` bigint ( 8) NOT NULL,
	 `to` varchar ( 256) DEFAULT NULL COMMENT 'comma sep''d, 0 = all',
	 `name` varchar ( 256) NOT NULL COMMENT 'form name',
	 `type` varchar ( 64) NOT NULL COMMENT 'form type',
	 `script` varchar ( 24) NOT NULL COMMENT 'php script',
	 `payload` varchar ( 10000) DEFAULT NULL COMMENT 'form data as JSON',
	 `count` int ( 3) NOT NULL DEFAULT '0' COMMENT 'times sent',
	 `_by` int ( 7) NOT NULL,
	 `_from` varchar ( 16) NOT NULL,
	 `_as-of` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'updated on',
	 `_sent` timestamp NULL DEFAULT NULL COMMENT 'last sent on - see log',
	 `archived` timestamp NULL DEFAULT NULL
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;
	";

	$result = mysql_query ( $query) or do_error ( $query, 'mysql query failed', mysql_error ( ), __FILE__, __LINE__);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ics` ADD PRIMARY KEY ( `id`);";
	$result = mysql_query ( $query) or do_error ( $query, 'mysql query failed', mysql_error ( ), __FILE__, __LINE__);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ics` MODIFY `id` bigint ( 8) NOT NULL AUTO_INCREMENT;";
	$result = mysql_query ( $query) or do_error ( $query, 'mysql query failed', mysql_error ( ), __FILE__, __LINE__);
	}

/*
*/

?>
<!DOCTYPE html>
<html>
<head>
<title>ICS forms</title>
<link rel=stylesheet href="stylesheet.php?version=1502624325" TYPE="text/css">	<!-- 3/15/11 -->
<link rel=stylesheet href="ics/balloon.css">
<style>
	.left-side 		{ font-weight: 900; font-family: Arial, Helvetica, sans-serif;}
	.right-side 	{ font-style: italic; font-weight: 400; font-family: Arial, Helvetica, sans-serif;}
</style>
<script>
	function get_ics ( func, script, id ) {		//	get_ics ( 'u', '{$row['script']}',{$row['id']})
		document.ics_go_form.func.value = func;
		document.ics_go_form.id.value = id;
		document.ics_go_form.action = "./ics/" + script + "?";		// naive cache-buster
		document.ics_go_form.submit ( );
		}		// end function
	</script>
</head>
<body>
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ics` WHERE `archived` IS NULL LIMIT {$limit}" ;				//
	$result_act = mysql_query ( $query) or do_error ( $query, 'mysql query failed', mysql_error ( ), __FILE__, __LINE__);
	$no_act_entries = mysql_num_rows ( $result_act);

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ics` WHERE `archived` IS NOT NULL LIMIT {$limit}" ;				//
	$result_arch = mysql_query ( $query) or do_error ( $query, 'mysql query failed', mysql_error ( ), __FILE__, __LINE__);
	$no_arch_entries = mysql_num_rows ( $result_arch);

	if ( array_key_exists ( 'do_arch', $_POST) ) {		// show archived
		$result_do = $result_arch;

		if ( $no_act_entries == 0 )	{ $caption = "<span class = 'left-side' > Archived entries</span> 	<span class = 'right-side' > - ( None active ) </span>"; }
		else 						{ $caption = "<span class = 'left-side' > Archived entries </span> 	<span class = 'right-side' onclick = 'document.act_form.submit ( );' title = 'click to view' data-balloon='Click to view active forms' data-balloon-pos='right' > - <u>active</u> : {$no_act_entries}</span>"; }
		}			// end archived
	else {												// show actives if any
		$num = ( $no_act_entries == 0 )? "No" : "";
		$result_do = $result_act;
		if ( $no_arch_entries == 0 ) { $caption = "<span class = 'left-side' > {$num } Active entries </span>	<span class = 'right-side' > - ( None archived) </span>"; }
		else 						{ $caption = "<span class = 'left-side' > {$num } Active entries </span>	<span class = 'right-side' onclick = 'document.arch_form.submit ( );' title = 'click to view' data-balloon='Click to view archived forms' data-balloon-pos='right' > - ( <u>archived</u> : {$no_arch_entries} )</span>"; }
		}		// end active

	$result_act = $result_arch = NULL;		// common
	echo "<CENTER><h2>ICS Forms</h2>\n";
	echo $caption;
	if ( ( $no_act_entries > 0 ) || ( $no_arch_entries > 0 ) ) {
		echo "<p style = 'margin-bottom: 16px;'><i>Click row to edit or send</i></p>\n";
		echo "<table border = 1 style = 'border-collape: collapse; margin-top:10px;'>\n";
		echo "<tr><th>Type</th><th>Name</th><th># sent</th><th>Last sent</th><th>as-of</th></tr>\n";
		while ( $row = mysql_fetch_assoc ( $result_do)) {
			$target = $row['script'];
			echo "<tr onclick = \"get_ics ( 'u', '{$target}',{$row['id']});\">";
			echo "<td>{$row['type']}</td><td>{$row['name']}</td><td align='center'>{$row['count']}</td><td>" . substr ( $row['_sent'], 5, 11 ) . "</td><td>" . substr ( $row['_as-of'], 5, 11) . "</td>";
			echo "</tr>\n";
			}		// end while ( )
		echo "\n</table>\n";
		}
	?>
<FORM name = 'ics_go_form' action = '' method = 'post'>
<p style= 'margin-left: 40px;margin-top: 40px; font-weight: bold; '>
New &raquo;
<?php
$labels = array () ;

$dir = "./ics";
if ( is_dir ( "$dir")){
	if ( $dh = opendir ( $dir)) {
		while ( ( $file = readdir ( $dh )) !== false){
			$temp = explode ( ".", $file, 3 );
			if ( ( ( !is_dir ( $file ) ) && ( count ( $temp ) == 2 ) && ( @strtolower ( $temp[1]) == "php") ) ) {
				$target = strtolower ( $file );
				array_push($labels, $target);
				}
			}
	closedir ( $dh );
	sort($labels);
	foreach ($labels as $value) {
		$temp = explode ( ".", $value, 3 );
 		$target = strtolower ( $value );
		echo "<button type = 'button' NAME = '{$value}' onClick = 'get_ics ( \"c\", \"{$value}\", \"\") ' style = 'margin-left:40px;' data-balloon='{$temp[0]}!' data-balloon-pos='up'>" . strtoupper ( $temp[0] ) . "</button>\n";
		}
	}
else { dump ( __LINE__) ; }
}		// ????
?>
</p>
	<input type = 'hidden' name = 'func' value = ''>
	<input type = 'hidden' name = 'id' value = ''>
	</form>

<form name = 'arch_form' action = '<?php echo basename ( __FILE__ );?>' method = 'post'>
<input type = 'hidden' name = 'do_arch' value = 1 />
</form>
<form name = 'act_form' action = '<?php echo basename ( __FILE__ );?>' method = 'post'>
</form>
<!-- <button data-balloon='Yeah, Cancel!' data-balloon-pos='up' onclick = 'this.window.close ( );' style = 'margin-top: 16px; '> Balloon</button> -->
<button type = 'button' onClick = 'window.close();' style = '' data-balloon='Close this window'data-balloon-pos='up'>Finished</button>
</BODY>
</HTML>

