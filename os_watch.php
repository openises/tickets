<?php
/*
2/15/2015 - initial release
9/25/2015 - important correction to base schedule increment on original
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED', 8192 );}		//
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
require_once('./incs/functions.inc.php');

if (!array_key_exists ("user_id", $_SESSION)) {$_POST['mode'] = 0;}		//3/6/2015 - close this window

//$GLOBALS['LOG_UNIT_COMMENT']	=24;		// 2/24/2015 - add to log_codes.inc.php!!!!

$evenodd = array ("even", "odd");
$handle = 		get_text("Handle");
$contact_via = 	get_text("Contact-via");
$callsign = 	get_text("Callsign");
$on_scene = 	get_text("On-scene");
$nature = 		get_text("Nature");
$units = 		get_text("Units");
$unit = 		get_text("Unit");
$incident = 	get_text("Incident");
$contact = 		get_text("Contact-via");
$addr = 		get_text("Addr");
$as_of = 		get_text("As-of");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="expires" content="0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="content-script-type"	content="application/x-javascript" />
<meta http-equiv="script-date" content="<?php print date("n/j/y g:i", filemtime(basename(__file__)));?>" />
<meta http-equiv="x-ua-compatible" content="ie=emulateie7"/>
<link rel=stylesheet href="stylesheet.php?version=<?php print time();?>" type="text/css">
<style>
td: 		font-size: 1.0em;
th:			font-weight: 100;  text-align: center;
td.closed:  text-decoration: line-through;
</style>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<script>
var do_LOG 		 = 10;
var do_LOG_DB 	 = 11;
var do_NOTE 	 = 12;
var do_NOTE_DB 	 = 13;
var do_MAIL 	 = 14;
var do_MAIL_SEND = 15;
var do_REFRESH	 = 99;
var viewportheight;
var viewportwidth;

function reSizeScr() {				// 244			-- 5/23/09
	var the_height = (document.getElementById('data_tbl').offsetHeight) + 120;	// 3/21/2015
//	alert("57 " + the_height);
	window.resizeTo((0.60)* screen.width, the_height);		// 10/31/09 - derived via trial/error (more of the latter, mostly)
	}		// end function re SizeScr()

String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};

function do_log (the_unit) {
	document.osw_form.ref.value = the_unit;
	document.osw_form.mode.value = do_LOG;		// write log entry
	document.osw_form.submit();
	}
function do_note (the_tick) {
	document.osw_form.ref.value = the_tick;
	document.osw_form.mode.value = do_NOTE;		// write note entry
	document.osw_form.submit();
	}
function do_mail (the_unit) {
	document.osw_form.ref.value = the_unit;
	document.osw_form.mode.value = do_MAIL;		//
	document.osw_form.submit();
	}
function do_can () {					// cancel
	document.osw_form.mode.value = do_REFRESH;		// force refresh
	document.osw_form.submit();
	}

</script>
</head>
<?php
//dump($_POST);
//	$mode = ( array_key_exists ("mode", $_GET) ) ? $_GET['mode'] : $_POST['mode'];
	$mode = ( empty($_POST) ) ? 1 : $_POST['mode'];		// 9/14/2015

@session_start();

$severities = array();
$severities[$GLOBALS['SEVERITY_NORMAL']] 	= "severity_normal";
$severities[$GLOBALS['SEVERITY_MEDIUM']] 	= "severity_medium";
$severities[$GLOBALS['SEVERITY_HIGH']] 		= "severity_high";

$os_w_setting = get_variable('os_watch');
$os_w_arr = explode ("/", $os_w_setting);			// p, n, r

switch ($mode) {
    case 0:		// force close window
?>
<BODY onload = "setTimeout(function(){ window.close(); }, 2500);">		<!-- window.resizeBy(-200, -100); window.resizeTo(aWidth, aHeight) -->
<br /><br /><br /><br /><br /><br /><center><h2>Window closing</h2></center>
</body>

<?php
		break;		// end case 0

  case 1:		// show data

		// 		following sql is cloned from os_watch.php - differentiated by $limit value

		function qc () {		//	 `a`.`as_of` AS `the_asof`, un_status status_val
			return "SELECT '0' AS `mode`,
				`t`.`severity`,
				`t`.`scope`,
				`t`.`status` AS `tickstatus`,
				CONCAT_WS(' ', `t`.`street`, `t`.`city`, `t`.`state`) AS `tickaddr`,
				`y`.`type`,
				`a`.`on_scene`,
				`r`.`handle`,
				`r`.`contact_via`,
				`r`.`callsign` AS `unit_call`,
				`r`.`id` AS `unitid`,
				`t`.`id` AS `tickid`,
				`u`.`expires`,
				`a`.`as_of` AS `the_asof`,
				`s`.`status_val` AS `unit_status`
					FROM `$GLOBALS[mysql_prefix]assigns` `a`
					LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	`t` 	ON (`a`.`ticket_id` 	= `t`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]responder`	`r` 	ON (`a`.`responder_id` 	= `r`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]in_types`	`y` 	ON (`t`.`in_types_id` 	= `y`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]user`		`u` 	ON (`u`.`responder_id` 	= `a`.`responder_id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	`s` 	ON (`r`.`un_status_id` 	= `s`.`id`)
					WHERE ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
					AND ((`on_scene` IS NOT NULL) AND (DATE_FORMAT(`on_scene`,'%y') <> '00')) ";
					}		// end function

		function q0 ($lim) {	//	PRIORITIES medium and high on-scene:
			return qc () . " AND `severity` <> {$GLOBALS['SEVERITY_NORMAL']} LIMIT {$lim}";
			}		// end function

		function q1 ($lim) {	//	PRIORITY normal on-scene:
			return qc () . " AND `severity` = {$GLOBALS['SEVERITY_NORMAL']} LIMIT {$lim}";
			}		// end function

		function q2 ($lim) { return	"SELECT
					'1' AS `mode`,
					`t`.`id` AS `tickid`,
					CONCAT_WS(' ', `t`.`street`, `t`.`city`, `t`.`state`) AS `tickaddr`,
					`t`.`severity`,
					`t`.`scope`,
					`t`.`status` AS `tickstatus`,
					`t`.`updated` AS `the_asof`,
					`y`.`type`
						FROM `$GLOBALS[mysql_prefix]ticket` `t`
						LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` ON (`t`.`in_types_id` = `y`.`id`)
						LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON (`a`.`ticket_id` = `t`.`id`)
					WHERE ( `t`.`status` = {$GLOBALS['STATUS_OPEN']} OR `t`.`status` = {$GLOBALS['STATUS_SCHEDULED']} )
					AND ( (`a`.`on_scene` IS NULL ) OR ( DATE_FORMAT(`a`.`on_scene`,'%y') = '00') )
					AND `y`.`watch` = 1
					ORDER BY `t`.`severity` DESC, `t`.`scope` ASC LIMIT {$lim} ";
					}		// end function

		function q3($lim) {
			$bits = explode('/', get_variable('status_watch'));
			if(count($bits) == 2) {
		        $watch_group = $bits[0];
        		$watch_interval = $bits[1];

			return "SELECT 'N/A' as tickstatus, '$watch_group' as tickaddr, 0 as severity, status_updated as on_scene, status_updated as the_asof,
						'Overdue - $watch_group' as unit_status, '' as contact_via, unix_timestamp(now()) as expires, handle as handle, 'Overdue $watch_group' as scope FROM `$GLOBALS[mysql_prefix]responder` 
						left join $GLOBALS[mysql_prefix]un_status on 
		$GLOBALS[mysql_prefix]responder.un_status_id = $GLOBALS[mysql_prefix]un_status.id where $GLOBALS[mysql_prefix]un_status.group = '$watch_group' and status_updated < (now() - interval $watch_interval minute)";
			} else {
				return "select 1 from dual where false";
			}
		}

		function do_row($row) {
			global $severities, $now, $i, $incs_shown, $evenodd ;
			$closed = ($row['tickstatus'] == $GLOBALS['STATUS_CLOSED'] ) ? " closed" : "";				// line-through 4/9/2015
			$the_addr = addslashes(shorten($row['tickaddr'], 24) );
			if ( $row ['mode'] == 0) {										// responder on-scene
				$sev_cls = $severities[$row['severity']];					// severity class
				$date = date(get_variable("date_format"), strtotime($row['on_scene']) );
				$the_asof = date(get_variable("date_format"), strtotime($row['the_asof']) );
				$the_status = shorten ($row['unit_status'], 10);
				$mail_onclick = (is_email($row['contact_via'])) ? "onclick = 'do_mail({$row['unitid']});'" : "" ;
				$online = ($row['expires'] > $now)? "<IMG SRC = './markers/checked.png' BORDER=0>" : "";
				echo "<tr class = '{$evenodd[($i%2)]}'>
					<td class = '{$sev_cls} text_small text_left' onclick = 'do_log({$row['unitid']})'>{$row['handle']}</td>
					<td class = '{$sev_cls} text_small text_center' align = 'center'>{$online}</td>
					<td class = '{$sev_cls} text_small text_left'>{$date} ({$the_status})</td>
					<td class = '{$sev_cls} text_small text_left' {$mail_onclick}>{$row['contact_via']}</td>
					<td class = '{$sev_cls} {$closed} text_small text_left' onclick = 'do_note({$row['tickid']})' onmouseout=\"UnTip();\" onmouseover=\"Tip('{$the_addr}');\" >{$row['type']} ({$row['scope']})</td>
					<td class = '{$sev_cls} text_small text_left'>{$the_addr}</td>
					<td class = '{$sev_cls} text_small text_left'>{$the_asof}</td>
					</tr>\n";
				}
			else {				// mode = 1, watch this incident
 				if ( ! ( in_array (  $row['tickid'] ,  $incs_shown ) ) ) {  		// show incident exactly once
					array_push ( $incs_shown , $row['tickid'] );
					$sev_cls = $severities[$row['severity']];					// severity class
					$the_asof = date(get_variable("date_format"), strtotime($row['the_asof']) );
					$the_scope = shorten ("{$row['scope']}/{$row['tickaddr']}", 48);

					echo "<tr class = '{$evenodd[($i%2)]}'>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td class = '{$sev_cls} {$closed} text_small text_left' onclick = 'do_note({$row['tickid']})'>{$row['type']} ({$row['scope']})</td>
						<td class = '{$sev_cls} {$closed} text_small text_left' >{$the_addr}</td>
						<td class = '{$sev_cls} text_small text_left'>{$the_asof}</td>
						</tr>\n";
					}				// end if ( ! ( in_array ... ) )

				}		// end if/else
			}		// end function

			function do_tbl_header($val){
				global $handle, $units, $on_scene, $contact_via, $addr, $nature, $watch_val, $as_of;
				echo "<center><table id='data_tbl' border = 1 cellpadding = 2 cellspacing = 2 style = 'border-collapse: collapse; overflow: auto;'>";
				echo "<tr class = 'even header text'><th colspan=99 align = center>{$units} Watch ({$val} min. cycle)</th></tr>\n";
				echo "<tr><th CLASS='text'>{$handle}</th><th CLASS='text'><img src= 'images/online.png' /></th><th CLASS='text'>{$on_scene} (Status)</th><th CLASS='text'>{$contact_via}</th><th CLASS='text'>{$nature}</th><th CLASS='text'>{$addr}</th><th CLASS='text'>{$as_of}</th></tr>\n";
				}
			function do_tbl_footer() {
				global $handle, $unit, $contact, $nature, $incident;
				echo "<tr class = 'even header'><td colspan = 99 CLASS='text text_center'><i>Click <b>{$handle}</b> to add {$unit} log entry -- click <b>{$contact}</b> to send text/email -- click <b>{$nature}</b> to add {$incident} note</i></td></tr>";
				echo "</table>\n";
				}

		$osw_str = get_variable('os_watch');
		$osw_arr = explode ("/", $osw_str);			// p, n, r

		$do_audible = ( (count($osw_arr) > 4 ) && ( intval ( $osw_arr[4] ) == 1 ) ) ? "window.opener.parent.frames['upper'].do_audible(); " : "";		// audible notification

?>
<BODY onload = "reSizeScr(); document.getElementById('data_tbl').style.overflow = 'auto'; <?php echo $do_audible; ?> ">			<!-- window.resizeBy(-200, -100); window.resizeTo(aWidth, aHeight) -->
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
<form name = "osw_form" method = post action = "<?php echo basename(__FILE__) ;?>" >
<input type = hidden name = "mode" value = "<?php echo $mode;?>" />
<input type = hidden name = "ref" value = "" />
</form>
<center>
<?php
//			dump ( $_SESSION['osw_run_at'] );
			$now = ( time() - 30 );								// seconds
			if ( ! (array_key_exists ( "osw_run_at", $_SESSION ) ) ) { $_SESSION['osw_run_at'] = array ( $now-1, $now-1, $now-1 ) ; }		// initialize routines, normals, priorities


			$watch_val = "";
			$q_arr = array( q0 (1) , q1 (1) , q2 (1), q3(1));			// first, probe

			for ($j=0; $j< 3; $j++) {
				if ($now >= $_SESSION['osw_run_at'][$j]) {						// seconds
					$result = mysql_query($q_arr[$j]) or do_error($q_arr[$j], 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_num_rows($result)> 0) {
						$watch_val = $osw_arr[$j];								// time slice for display purposes
						}
					}						// end if ($now ... )
				}						// end for ()

			do_tbl_header($watch_val);
			$incs_shown = array();											// empty

			$q_arr = array( q0 (9999) , q1 (9999) , q2 (9999) , q3(9999));			// then, pull n/j/y H:i
			for ($j=0; $j< 3; $j++) {
				if ($now >= $_SESSION['osw_run_at'][$j]) {					// seconds
					$_SESSION['osw_run_at'][$j] +=  ( $osw_arr[$j] * 60 ) ;				// set next - nth entry to seconds - 9/25/2015

					$result_x = mysql_query($q_arr[$j]) or do_error($q_arr[$j], 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					if (mysql_num_rows($result_x) > 0) {
						while ( $row = mysql_fetch_array ( $result_x, MYSQL_ASSOC)) {
							do_row ( $row );
							}
						}				// end if (mysql_num_rows ... )
					}				// end if ($now >= ... )
				}				// end for ()

			do_tbl_footer();
?>
			<SPAN ID='close_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
<?php
			break;		// end case 1
    case 10:		// log entry

    	$query = "SELECT `handle` FROM `$GLOBALS[mysql_prefix]responder` `r` WHERE `id` = {$_POST['ref']} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
?>
<body onload = "document.osw_form.frm_comment.focus();">		<!-- log entry  <?php echo __LINE__;?> -->
<form name="osw_form" method = "post" action="<?php echo basename(__FILE__);?>">
<center>
<table border = 0 style = 'margin-top:40px;'>
	<tr class = 'even'>
		<th colspan=2><?php echo $row['handle']; ?> Log entry</th>
	</tr>
	<tr class = 'odd'>
		<td>
			<textarea name="frm_comment" cols="72" rows="1" wrap="virtual" placeholder="Log entry here ..."></textarea>
		</td>
	</tr>
	<tr class = 'even'>
		<td align='center'>
			<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.osw_form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.osw_form.reset();'><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
			<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_can();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
		</td>
	</tr>
</table>
<input type = hidden name = "mode"	value = 11 /> <!-- do_LOG_DB  -->
<input type = hidden name = "ref" 	value = "<?php echo $_POST['ref'];?>" />
</form>

</body>
<?php

        break;
    case 11:		// log entry db
        $_SESSION['osw_ntrupt_ok'] = TRUE;		// on-scene watch interrupt allowed (monitored by get_latest_id)
		$comment = shorten (htmlentities(strip_tags($_POST['frm_comment']), ENT_QUOTES), 2040 );
	    do_log($GLOBALS['LOG_COMMENT'], 0, $_POST['ref'], $comment);	//
?>
<body onload = "setTimeout(function(){ do_can();}, 1500);">			<!-- <?php echo __LINE__;?> -->
<center><span style = "margin-top:60px"><br /><br /><h3>Log entry written</h3></span>
<form name="osw_form" method = "post" action="<?php echo basename(__FILE__);?>">
<input type = hidden name = "mode" value = "" /> <!-- do_LOG_DB  -->
</form>
<?php

        break;

    case 12:		// add note
    	$query = "SELECT  `t`.`scope`, `y`.`type` FROM `$GLOBALS[mysql_prefix]ticket` `t`
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types`	`y` 	ON (`t`.`in_types_id` 	= `y`.`id`)
    		WHERE `t`.`id` = " . intval($_POST['ref']) . " LIMIT 1;";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if (mysql_num_rows(	$result) !== 1) {
			$log_message = basename(__FILE__) . "/" . __LINE__;
			if (!(array_key_exists ( $log_message, $_SESSION ))) {		// limit to once per session
				$_SESSION[$log_message] = TRUE;
				do_log ($GLOBALS['LOG_ERROR'], 0, 0, basename(__FILE__) . __LINE__);
				}
			}		// end error
		else {
			$row = mysql_fetch_assoc ($result);
			}
//		dump($row);

?>
<script>
	function validate () {
		if(document.osw_form.frm_text.value.trim().length==0) {
			alert("Enter text - or Cancel");
			return false;
			}
		else {
			document.osw_form.submit();
			}
		}

	function set_signal(inval) {
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.osw_form.frm_text.value+=" " + temp_ary[1] + ' ';		// text only
		document.osw_form.frm_text.focus();
		}		// end function set_signal()
</script>
<body onLoad = "document.osw_form.frm_text.focus();">	<!--  add note  <?php echo __LINE__;?> -->
<center>
<br /><br />

<h3><?php echo "{$incident}: {$row['type']} ({$row['scope']})";?></h3>
<br /><br />

<form name='osw_form' method='post' action = '<?php echo basename(__FILE__) ;?>'>
<input type = hidden name = "mode" 			value = "13" />									<!-- do_NOTE_DB -->
<input type = hidden name = "ref" 			value = "<?php echo $_POST['ref'];?>" />
<input type = hidden name = 'frm_type' 		value='' />
<textarea name='frm_text' cols=70 rows = 2 placeholder = "Enter note text"></textarea>
<br />
Signal &raquo;
<select name='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); '>
<option value=0 selected>Select</option>
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
		echo "\t<option value='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</option>\n";		// note pipe separator
		}
	echo "\t</select>\n";
?>
<br />

<B>Apply to</B>&nbsp;:&nbsp;&nbsp;
Description &raquo; <input type = 'radio' name='frm_add_to' value='0' checked />&nbsp;&nbsp;&nbsp;&nbsp;
Disposition &raquo; <input type = 'radio' name='frm_add_to' value='1' /><br /><br />
<span style ='display: table; margin: 0 auto;'>
	<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
	<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='this.form.reset();'><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
	<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_can();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
</span>
</form>
<?php

        break;

    case 13:		// add note db (cloned from 'add_note.php')
        $_SESSION['osw_ntrupt_ok'] = TRUE;		// on-scene watch interrupt allowed (monitored by get_latest_id)

		$field_name = array('description', 'comments');
		$the_date = date(get_variable('date_format', now()));
		$now_ts = now_ts();
		$the_text = "[{$_SESSION['user']}:{$the_date}]" . strip_tags(trim($_POST['frm_text'])) . "\n";		// identify 'by'
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `{$field_name[$_POST['frm_add_to']]}`= CONCAT (`{$field_name[$_POST['frm_add_to']]}`, '{$the_text}'), `updated` = '{$now_ts}'  WHERE `id` = " . intval($_POST['ref']) ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$query = "SELECT `y`. `type` FROM `$GLOBALS[mysql_prefix]ticket` `t`
					LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` ON (`t`.`in_types_id` = `y`.`id`)
					WHERE  `t`.`id` = " . intval($_POST['ref']) . " LIMIT 1";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
?>
<BODY onload = "setTimeout(function(){ do_can(); }, 1500);">		<!-- 1/14/10 -->
<form name = "osw_form" method = "post" 	action = "<?php echo basename(__FILE__); ?>">
<input type = hidden name = "mode" 		value = "<?php echo $_POST['mode_orig'];?>" /> <!-- original entry  -->
<input type = hidden name = "mode_orig" value = "<?php echo $_POST['mode_orig'];?>" />
<input type = hidden name = "ref" 		value = "<?php echo $_POST['ref'];?>" />
</form>

<center>
<br /> <br /> <br /> <br />
<span style = "margin-top:150px;">
<h3>Note added to <?php echo $incident . ": '" . $row['type'] . "'"; ?> </h3><br /><br />
</span>
<?php
        break;

    case 14:		// var do_MAIL 	 = 7  cloned from do_unit_mail

		$smsg_ids = ((array_key_exists( "use_smsg", $_POST)) && ($_POST['use_smsg'] == 1)) ? $_POST['frm_smsg_ids'] : "";
//		$smsg_ids = ((isset($_POST['use_smsg'])) && ($_POST['use_smsg'] == 1)) ? $_POST['frm_smsg_ids'] : "";

    	$query = "SELECT `handle`, `contact_via` FROM `$GLOBALS[mysql_prefix]responder` `r` WHERE `id` = {$_POST['ref']} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));

		$tik_id = $_POST['ref'];
?>
<body >			<!-- <?php echo __LINE__;?> -->
<script>
	var set_text = true;		// default: apply signal to msg text vs. subject
	function set_signal(inval) {
		var temp_ary = inval.split("|", 2);		// inserted separator
		if (set_text) {
			var sep = (document.osw_form.frm_text.value=="")? "" : " ";
			document.osw_form.frm_text.value+=sep + temp_ary[1] + ' ';
			document.osw_form.frm_text.focus();
			}
		else {
			var sep = (document.osw_form.frm_subj.value=="")? "" : " ";
			document.osw_form.frm_subj.value+= sep + temp_ary[1] + ' ';
			document.osw_form.frm_subj.focus();
			}
		}		// end function set_signal()

	function set_message(id) {	//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
		var theMessages = <?php echo json_encode($std_messages);?>;
		var message = theMessages[parseInt(id)]['message'];
		var tick_id = <?php print $tik_id;?>;
		var url = './ajax/get_replacetext.php?tick=' + tick_id + '&version=' + randomnumber + '&text=' + encodeURIComponent(message);
		sendRequest (url,replacetext_cb, "");
			function replacetext_cb(req) {
				var the_text=JSON.decode(req.responseText);
				if (the_text[0] == "") {
					var replacement_text = message;
					} else {
					var replacement_text = the_text[0];
					}
				document.osw_form.frm_text.value += replacement_text;
				}			// end function replacetext_cb()
		}		// end function set_message(message)

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}

	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];

	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}

	function validate () {
		if ( ( document.osw_form.frm_add_str.value.trim().length==0)
			|| (document.osw_form.frm_subj.value.trim().length==0)
			|| (document.osw_form.frm_text.value.trim().length==0) )
			{ alert("Enter text - or Cancel"); return false; }
		else { document.osw_form.submit(); }
		}				// end function validate ()

</script>

<form name = 'osw_form' method = post 	action = "<?php echo basename(__FILE__); ?>">
<input type = hidden name = "mode" 		value = 15 /> 	<!-- do_MAIL_SEND -->
<input type = hidden name = "ref" 		value = "<?php echo $_POST['ref'];?>" />
<center>
			<TABLE ALIGN='center' BORDER = 0 style = 'margin-top:20px;'>
				<TR CLASS='odd'><TH COLSPAN=2>Mail to: <?php print $row['handle']; ?></TH></TR> <!-- 7/2/10 -->

				<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>
				<TR VALIGN = 'TOP' CLASS='even'><TD ALIGN='right'  CLASS="td_label">To: </TD>
					<TD ALIGN = 'left'><INPUT TYPE='text' NAME='frm_add_str' VALUE='<?php print $row['contact_via']; ?>' SIZE = 36></TD></TR>
				<TR VALIGN = 'TOP' CLASS='odd'>
					<TD ALIGN='right' CLASS="td_label">Subject: </TD><TD ALIGN = 'left'><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='right' CLASS="td_label">Message: </TD><TD ALIGN = 'left'><TEXTAREA NAME='frm_text' COLS=60 ROWS=2></TEXTAREA></TD></TR>
				<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
					<TD></TD><TD ALIGN = 'left'><SPAN  CLASS="td_label">Signal: </SPAN>
						<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
						<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
							print "\t<OPTION VALUE='{$row['code']}'>{$row['code']}|{$row['text']}</OPTION>\n";		// pipe separator
							}
?>
					</SELECT>
					</TD>
				</TR>
				<TR><TD></TD>
					<TD ALIGN = 'center'>
						<SPAN STYLE='margin-left:20px;'><span style = 'font-weight: bold;'>Apply Signal to:</span>&nbsp;&nbsp; Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>
						<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'>&nbsp;&nbsp;</SPAN>
						</TD></TR>
				<TR VALIGN = 'TOP' CLASS='even' style = 'display: none;'>
					<TD ALIGN='right' CLASS="td_label">Standard Message: </TD><TD>

						<SELECT NAME='signals' onChange = 'set_message(this.options[this.selectedIndex].value);'>	<!--  11/17/10 -->
							<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
							print get_standard_messages_sel();
?>
						</SELECT>
						<BR />
					</TD>
				</TR>
				<TR VALIGN = 'TOP' CLASS='odd'>
					<TD></TD>
					<TD ALIGN='center' >
						<BR />
						<BR />
						<span style ='display: table; margin: 0 auto;'>
							<SPAN ID='sub_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='validate();'><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
							<SPAN ID='reset_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.osw_form..reset();'><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
							<SPAN ID='can_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_can();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
						</span>
					</TD>
				</TR>
				<TR><TD>&nbsp;</TD></TR>
				</TABLE>
				<INPUT type='hidden' NAME='frm_resp_ids' VALUE=''>
				<INPUT type='hidden' NAME='frm_smsg_ids' VALUE='<?php print $smsg_ids;?>'>
				<INPUT TYPE='hidden' NAME="use_smsg" VALUE='0'> 		<!-- see do_unit_mail -->
		</form>	<!-- <?php echo __LINE__ ;?> -->

<?php
        break;

    case 15:		// var do_MAIL_SEND = 15;
			$email_from =   	filter_var(get_variable('email_from'), FILTER_VALIDATE_EMAIL) ;
			$email_reply_to = 	filter_var(get_variable('email_reply_to'), FILTER_VALIDATE_EMAIL) ;
			$headers = ($email_from)? 	"From: {$email_from}\r\n" : "";
			$headers .= ($email_reply_to)? 	"Reply-To: {$email_reply_to}\r\n" : "";

			mail ( $_POST['frm_add_str'] , $_POST['frm_subj'] , $_POST['frm_text'] , $headers );		// pending do_send() working
			@session_start();
			unset ( $_SESSION['osw_run_at']);		// force refresh
?>
<BODY onload = "setTimeout(function(){ do_can(); }, 1500);"><CENTER>		<!-- 1/14/10 -->
<CENTER><BR /><BR /><BR /><H3>Message sent - window closing</H3>
<form name = 'osw_form' method = post 	action = "<?php echo basename(__FILE__); ?>">
<input type = hidden name = "mode" 		value = "" />
<input type = hidden name = "ref" 		value = "<?php echo $_POST['ref'];?>" />
</form>

<?php
        break;

    case 99:		// refresh
		@session_start();
		unset ( $_SESSION['osw_run_at']);		// force refresh
?>
<body onload = "document.osw_form.submit();">		<!-- refresh  <?php echo __LINE__;?> -->
<form name="osw_form" method = "post" action="<?php echo basename(__FILE__);?>">
<input type = hidden name = "mode" 		value = 1 />
</form>
</body>
<?php
        break;

    default:
        echo "error - error - error - error - error at line  " . __LINE__;
	}		// end switch ($mode)
?>
</BODY>
<SCRIPT>
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
set_fontsizes(viewportwidth, "popup");
</SCRIPT>
</HTML>
