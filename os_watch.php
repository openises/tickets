<?php
/*
ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `watch` INT(2) NOT NULL DEFAULT '0' COMMENT 'Used in on-scene-watch' AFTER `set_severity`;
alert (<?php echo __LINE__;?>);
<!-- <?php echo __LINE__;?> -->
$_SESSION['osw_ntrupt_ok'] = TRUE;
2/15/2015 - initial release

	$guest = is_guest();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$status_table}` ORDER BY `group` ASC, `sort` ASC, `{$status_field}` ASC";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$dis = ($guest)? " DISABLED": "";								// 9/17/08
	$the_grp = strval(rand());			//  force initial OPTGROUP value
	$i = 0;
	$outstr = ($tbl_in == "u") ? "\t\t<SELECT CLASS='sit' id='frm_status_id_u_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color};' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update({$unit_in}, this.value)' >" :
	"\t\t<SELECT CLASS='sit' id='frm_status_id_f_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color}; width: 90%;' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; do_sel_update_fac({$unit_in}, this.value)' >";	// 12/19/09, 1/1/10. 3/15/11
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		if ($the_grp != $row['group']) {
			$outstr .= ($i == 0)? "": "\t</OPTGROUP>";
			$the_grp = $row['group'];
			$outstr .= "\t\t<OPTGROUP LABEL='$the_grp'>";
			}
		$sel = ($row['id']==$status_val_in)? " SELECTED": "";
		$outstr .= "\t\t\t<OPTION VALUE=" . $row['id'] . $sel ." STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'  onMouseover = 'style.backgroundColor = this.backgroundColor;'>$row[$status_field] </OPTION>";
		$i++;
		}		// end while()
	$outstr .= "\t\t</OPTGROUP>\t\t</SELECT>";
	return $outstr;
	}


*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED', 8192 );}		//
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
require_once('./incs/functions.inc.php');

/*
snap(basename(__FILE__), __LINE__);
$_SESSION['osw_ntrupt_ok'] = TRUE;				// false if form is active
snap(__LINE__, $_SESSION['osw_ntrupt_ok']);
*/
if (!array_key_exists ("user_id", $_SESSION)) {$_POST['mode'] = 0;}		//3/6/2015 - close this window

//$GLOBALS['LOG_UNIT_COMMENT']	=24;		// 2/24/2015 - add to log_codes.inc.php!!!!

$evenodd = array ("even", "odd");
$handle = 		get_text("Handle");
$contact_via = 	get_text("Contact_via");
$callsign = 	get_text("Callsign");
$on_scene = 	get_text("On_scene");
$nature = 		get_text("Nature");
$units = 		get_text("Units");
$unit = 		get_text("Unit");
$on_scene = 	get_text("On_scene");
$incident = 	get_text("Incident");
$contact = 		get_text("Contact-via");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" />
<META http-equiv="X-UA-Compatible" content="IE=EmulateIE7"/>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<style>
td: 		font-size: 1.0em;
th:			font-weight: 100;  text-align: center;
td.closed:  text-decoration: line-through;
</style>

<script>
var do_LOG 		 = 10;
var do_LOG_DB 	 = 11;
var do_NOTE 	 = 12;
var do_NOTE_DB 	 = 13;
var do_MAIL 	 = 14;
var do_MAIL_SEND = 15;

function reSizeScr() {				// 244			-- 5/23/09
	var the_height = (document.getElementById('data_tbl').offsetHeight) +200;	// 3/21/2015
//	alert("55 " + the_height);
	window.resizeTo((0.60)* screen.width, the_height);		// 10/31/09 - derived via trial/error (more of the latter, mostly)
	}		// end function re SizeScr()

String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};
function set_orig() {
<?php
	if ( array_key_exists ("mode", $_GET) ) { echo "\t document.osw_form.mode_orig.value = {$_GET['mode']};\n";  }
?>
	}		// end function set orig()

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
	document.osw_form.mode.value = document.osw_form.mode_orig.value;		// back to normal
	document.osw_form.submit();
	}

</script>
</HEAD>
<?php
//dump($_POST);
$mode = ( array_key_exists ("mode", $_GET) ) ? $_GET['mode'] : $_POST['mode'];

@session_start();

	$query_core = "SELECT `t`.`severity`, `t`.`scope`, `t`.`status` AS `tickstatus`, CONCAT_WS(' ', `t`.`street`, `t`.`city`, `t`.`state`) AS `tickaddr`, `y`.`type`, `on_scene`, `handle`, `contact_via`, `r`.`callsign` AS `unit_call`, `r`.`id` AS `unitid`, `t`.`id` AS `tickid`, `u`.`expires`
			FROM `$GLOBALS[mysql_prefix]assigns` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	`t` 	ON (`a`.`ticket_id` 	= `t`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder`	`r` 	ON (`a`.`responder_id` 	= `r`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types`	`y` 	ON (`t`.`in_types_id` 	= `y`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]user`		`u` 	ON (`u`.`responder_id` 	= `a`.`responder_id`)
			WHERE ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
			AND ((`on_scene` IS  NOT NULL) AND  (DATE_FORMAT(`on_scene`,'%y') <> '00')) ";

$severities = array();
$severities[$GLOBALS['SEVERITY_NORMAL']] 	= "severity_normal";
$severities[$GLOBALS['SEVERITY_MEDIUM']] 	= "severity_medium";
$severities[$GLOBALS['SEVERITY_HIGH']] 		= "severity_high";

$os_w_setting = get_variable('os_watch');
$os_w_arr = explode ("/", $os_w_setting);			// p, n, r

switch ($mode) {
    case 0:		// close window
?>
<BODY onload = "setTimeout(function(){ window.close(); }, 2500);">		<!-- window.resizeBy(-200, -100); window.resizeTo(aWidth, aHeight) -->
<br /><br /><br /><br /><br /><br /><center><h2>Window closing</h2></center>
</body>
<?php
		break;		// end case 0

    case 1:		// show data
    case 2:
//        $_SESSION['osw_ntrupt_ok'] = TRUE;		// on-scene watch interrupt allowed (monitored by get_latest_id)
?>
<BODY onload = "reSizeScr()set_orig();">			<!-- window.resizeBy(-200, -100); window.resizeTo(aWidth, aHeight) -->
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
<form name = "osw_form" method = post action = "<?php echo basename(__FILE__) ;?>" >
<input type = hidden name = "mode" value = "<?php echo $mode;?>" />
<?php
	$mode_orig = (array_key_exists( "mode_orig", $_POST)) ? $_POST['mode_orig'] : "" ;
?>
<input type = hidden name = "mode_orig" value = "<?php echo $mode_orig;?>" />
<input type = hidden name = "ref" value = "" />
</form>
<center>
<?php

	$mode_cl = ($mode == 2) ? "": " AND `severity` <> {$GLOBALS['SEVERITY_NORMAL']} ";		// 1 = med and high, 2 = all
	$query = "{$query_core}
			{$mode_cl}
			ORDER BY `severity` DESC, `scope` ASC, `handle` ASC ;";

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	echo "<table id='data_tbl' border = 1 cellpadding = 2 cellspacing = 2 style = 'border-collapse: collapse;'>";
	echo "<tr class = 'even header'><th colspan=99 align = center>{$units} Watch - ({$os_w_arr[($mode-1)]} min. cycle)</th></tr>\n";
	echo "<tr><th>{$handle}</th><th><img src= 'images/online.png' /></th><th >{$on_scene}</th><th >{$contact_via}</th><th >{$callsign}</th><th>{$nature}</th></tr>\n";
	$i = 0;
	$id_arr = array();
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if ( ! ( in_array ( $row['unitid'] , $id_arr ) ) ) {				// enforce singleton
			array_push ( $id_arr , $row['unitid']);
//			dump($id_arr);
			$sev_cls = $severities[$row['severity']];					// severity class
		 	$date = date(get_variable("date_format"), strtotime($row['on_scene']) );
			$mail_onclick = (is_email($row['contact_via'])) ? "onclick = 'do_mail({$row['unitid']});'" : "" ;
			$now = now_ts();
			$online = ($row['expires'] > $now)? "<IMG SRC = './markers/checked.png' BORDER=0>" : "";
			$closed = ($row['tickstatus'] == $GLOBALS['STATUS_CLOSED'] ) ? " closed" : "";				// line-through 4/9/2015
			$addr = addslashes($row['tickaddr']);
			echo "<tr class = '{$evenodd[($i%2)]}'>
				<td class = '{$sev_cls}' onclick = 'do_log({$row['unitid']})'>{$row['handle']}</td>
				<td class='{$sev_cls}' align = 'center'>{$online}</td>
				<td class='{$sev_cls}'>{$date}</td>
				<td class='{$sev_cls}' {$mail_onclick}>{$row['contact_via']}</td>
				<td class='{$sev_cls}' onclick = 'do_log({$row['unitid']})'>{$row['unit_call']}</td>
				<td class='{$sev_cls} {$closed}' onclick = 'do_note({$row['tickid']})' onmouseout=\"UnTip();\" onmouseover=\"Tip('{$addr}');\" >{$row['type']} ({$row['scope']})</td>
				</tr>\n";
			$i++;
			}			// end if ( ! ( in_array ... ) )
		}			// end while ( ... )
	echo "<tr class = '{$evenodd[($i%2)]}'><td colspan = 99 align = 'center'><i>Click <b>{$handle}</b> to add {$unit} log entry -- click <b>{$contact}</b> to send text/email  -- click <b>{$nature}</b> to add {$incident} note</i></td></tr>";
	echo "</table>\n";
?>
<button onclick = "window.close();" class='plain text_small' style='float: none; margin-top: 12px;'>Close</button>
<?php
        break;

    case 3:			// 3/28/2015
        $_SESSION['osw_ntrupt_ok'] = TRUE;		// on-scene watch interrupt allowed (monitored by get_latest_id)
?>
<BODY onload = "reSizeScr(); set_orig();">									<!-- window.resizeBy(-200, -100); window.resizeTo(aWidth, aHeight) -->
<form name = "osw_form" method = post action = "<?php echo basename(__FILE__) ;?>" >
<input type = hidden name = "mode" value = "<?php echo $mode;?>" />
<?php
	$mode_orig = (array_key_exists( "mode_orig", $_POST)) ? $_POST['mode_orig'] : "" ;
?>
<input type = hidden name = "mode_orig" value = "<?php echo $mode_orig;?>" />
<input type = hidden name = "ref" value = "" />
</form>
<center>
<?php

	$mode_cl = ($mode == 2) ? "": " AND `severity` <> {$GLOBALS['SEVERITY_NORMAL']} ";		// 1 = med and high, 2 = all
	$query = "{$query_core}
			{$mode_cl}
			ORDER BY `severity` DESC, `scope` ASC, `handle` ASC ;";
	snap(__LINE__, $query);

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	echo "<table id='data_tbl' border = 1 cellpadding = 2 cellspacing = 2 style = 'border-collapse: collapse;'>";
	echo "<tr class = 'even header'><th colspan=99 align = center>{$units} Watch - ({$os_w_arr[($mode-1)]} min. cycle)</th></tr>\n";
	echo "<tr><th >{$handle}</th><th><img src= 'images/online.png' /></th><th >{$on_scene}</th><th >{$contact_via}</th><th >{$callsign}</th><th>{$nature}</th></tr>\n";
	$i = 0;

	$id_arr = array();
	$now = now_ts();

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if ( ! ( in_array ( $row['unitid'] , $id_arr ) ) ) {			// ensure singleton
			array_push ( $id_arr , $row['unitid']);
//			dump($id_arr);
			$sev_cls = $severities[$row['severity']];					// severity class
		 	$date = date(get_variable("date_format"), strtotime($row['on_scene']) );
			$mail_onclick = (is_email($row['contact_via'])) ? "onclick = 'do_mail({$row['unitid']});'" : "" ;
			$online = ($row['expires'] > $now)? "<IMG SRC = './markers/checked.png' BORDER=0>" : "";
			echo "<tr class = '{$evenodd[($i%2)]}'>
				<td class = '{$sev_cls}' onclick = 'do_log({$row['unitid']})'>{$row['handle']}</td>
				<td class='{$sev_cls}' align = 'center'>{$online}</td>
				<td class='{$sev_cls}'>{$date}</td>
				<td class='{$sev_cls}' {$mail_onclick}>{$row['contact_via']}</td>
				<td class='{$sev_cls}' onclick = 'do_log({$row['unitid']})'>{$row['unit_call']}</td>
				<td class='{$sev_cls}' onclick = 'do_note({$row['tickid']})'>{$row['type']} ({$row['scope']})</td>
				</tr>\n";
			$i++;
			}		// end if ( ! ( in_array ... ) )
		}		// end while()

	$query = "SELECT `r`.`handle`, `r`.`contact_via`, `r`.`callsign` AS `unit_call`, `r`.`id` AS `unitid`, `s`.`status_val`, NULL AS `expires`
		FROM `$GLOBALS[mysql_prefix]responder` `r`
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`r`.`un_status_id` = `s`.`id`)
		WHERE `s`.`watch` > 0
		ORDER BY `handle` ASC;";															// routine
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$sev_cls = "severity_normal";
	$gt_status = get_text("Status");

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if ( ! ( in_array ( $row['unitid'] , $id_arr ) ) ) {			// ensure singleton
			array_push ( $id_arr , $row['unitid']);
//			dump($id_arr);
			$mail_onclick = (is_email($row['contact_via'])) ? "onclick = 'do_mail({$row['unitid']});'" : "" ;
			$online =  "";
			echo "<tr class = '{$evenodd[($i%2)]}'>
				<td class = '{$sev_cls}' onclick = 'do_log({$row['unitid']})'>{$row['handle']}</td>
				<td class='{$sev_cls}' align = 'center'>{$online}</td>
				<td class='{$sev_cls}'>{$gt_status}: {$row['status_val']}</td>
				<td class='{$sev_cls}' {$mail_onclick}>{$row['contact_via']}</td>
				<td class='{$sev_cls}' onclick = 'do_log({$row['unitid']})'>{$row['unit_call']}</td>
				<td class='{$sev_cls}'></td>
				</tr>\n";
			$i++;
			}				// end if ()
		}				// end while()

	echo "<tr class = '{$evenodd[($i%2)]}'><td colspan = 99 align = 'center'><i>Click <b>{$handle}</b> to add {$unit} log entry -- click <b>{$contact}</b> to send text/email  -- click <b>{$nature}</b> to add {$incident} note</i></td></tr>";
	echo "</table>\n";
?>
<button onclick = "window.close();"  class='plain text_small' style = 'float: none; margin-top: 12px;'>Close</button>
<?php
		break;		// end case 3 - 3/28/2015

    case 10:		// log entry

    	$query = "SELECT `handle` FROM `$GLOBALS[mysql_prefix]responder` `r` WHERE `id` = {$_POST['ref']} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
?>
<body onload = "document.osw_form.frm_comment.focus();">		<!-- log entry  <?php echo __LINE__;?> -->
<form name="osw_form" method = "post" action="<?php echo basename(__FILE__);?>">
<center><table border = 0 style = 'margin-top:40px;'>
<tr class = 'even'><th colspan=2><?php echo $row['handle']; ?> Log entry</th></tr>
<tr class = 'odd'><td><textarea name="frm_comment" cols="72" rows="1" wrap="virtual" placeholder="Log entry here ..."></textarea></td></tr>
<tr class = 'even'><td><span style ='display: table; margin: 0 auto;'>
	<input type = 'button' value='Next' 	class='plain text_small' onClick="document.osw_form.submit()" />
	<input type = 'button' value='Reset'  	class='plain text_small' style = "margin-left:20px;" onClick="document.osw_form.reset()" />
	<input type = 'button' value='Cancel'  	class='plain text_small' style = "margin-left:20px;" onClick = 'document.osw_form.mode.value = document.osw_form.mode_orig.value; document.osw_form.submit()' />
	</span></td></tr>
</table>
<input type = hidden name = "mode" 		value = 11 /> <!-- do_LOG_DB  -->
<input type = hidden name = "mode_orig" value = "<?php echo $_POST['mode_orig'];?>" />
<input type = hidden name = "ref" 		value = "<?php echo $_POST['ref'];?>" />
</form>

</body>
<?php

        break;
    case 11:		// log entry db
        $_SESSION['osw_ntrupt_ok'] = TRUE;		// on-scene watch interrupt allowed (monitored by get_latest_id)
		$comment = shorten (htmlentities(strip_tags($_POST['frm_comment']), ENT_QUOTES), 2040 );
	    do_log($GLOBALS['LOG_UNIT_COMMENT'], 0, $_POST['ref'], $comment);	//
?>
<script>
function do_cont() {
	document.osw_form.submit());
	}

</script>
<body onload = "setTimeout(function(){ osw_form.submit(); }, 2500);">			<!-- <?php echo __LINE__;?> -->
<form name="osw_form" method = "post" 	action="<?php echo basename(__FILE__);?>">
<input type = hidden name = "mode" 		value = "<?php echo $_POST['mode_orig'];?>" />
<input type = hidden name = "mode_orig" value = "<?php echo $_POST['mode_orig'];?>" />
<input type = hidden name = "ref" 		value = "<?php echo $_POST['ref'];?>" />
</form>
<center><span style = "margin-top:60px"><br /><<br /><h2>Log entry complete</h2></span>
</body>
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
//		alert(335);
		if(document.osw_form.frm_text.value.trim().length==0) {
			alert("Enter text - or Cancel");
			return false;
			}
		else {
			document.osw_form.submit();
			}
		}

	function set_signal(inval) {
//		alert("346 " + inval );
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.osw_form.frm_text.value+=" " + temp_ary[1] + ' ';		// text only
		document.osw_form.frm_text.focus();
		}		// end function set_signal()
</script>
<body onLoad = "document.osw_form.frm_text.focus();">	<!--  add note  <?php echo __LINE__;?> -->
<center>
<br /><br />

<h3><?php echo "{$incident}: {$row['type']} ({$row['scope']})";?>
<br /><br />

<form name='osw_form' method='post' action = '<?php echo basename(__FILE__) ;?>'>
<input type = hidden name = "mode" 			value = "13" />									<!-- do_NOTE_DB -->
<input type = hidden name = "mode_orig" 	value = "<?php echo $_POST['mode_orig'];?>" />
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
<input type = 'button' value = 'Next' 		class='plain text_small' onClick = 'validate();' />
<input type = 'button' value = 'Reset' 		class='plain text_small' style = "margin-left:20px;" onClick = 'this.form.reset()' />
<input type = 'button' value = 'Cancel'  	class='plain text_small' style = "margin-left:20px;" onClick = 'document.osw_form.mode.value = document.osw_form.mode_orig.value; document.osw_form.submit()' />
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
<body onload = "setTimeout(function(){ osw_form.submit(); }, 2500);" >			<!-- <?php echo __LINE__;?> -->
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
</body>
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
//		alert("438 " + inval);
		var temp_ary = inval.split("|", 2);		// inserted separator
		if (set_text) {
			var sep = (document.mail_form.frm_text.value=="")? "" : " ";
			document.mail_form.frm_text.value+=sep + temp_ary[1] + ' ';
			document.mail_form.frm_text.focus();
			}
		else {
			var sep = (document.mail_form.frm_subj.value=="")? "" : " ";
			document.mail_form.frm_subj.value+= sep + temp_ary[1] + ' ';
			document.mail_form.frm_subj.focus();
			}
		}		// end function set_signal()

	function set_message(message) {	//	10/23/12
		alert("453 " + message);

		var randomnumber=Math.floor(Math.random()*99999999);
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
				document.mail_form.frm_text.value += replacement_text;
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
<input type = hidden name = "mode_orig" value = "<?php echo $_POST['mode_orig'];?>" />
<input type = hidden name = "ref" 		value = "<?php echo $_POST['ref'];?>" />
<center>
			<TABLE ALIGN='center' BORDER = 0 style = 'margin-top:20px;'>
				<TR CLASS='odd'><TH COLSPAN=2>Mail to: <?php print $row['handle']; ?></TH></TR> <!-- 7/2/10 -->

				<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>
				<TR VALIGN = 'TOP' CLASS='even'><TD ALIGN='right'  CLASS="td_label">To: </TD>
					<TD><INPUT TYPE='text' NAME='frm_add_str' VALUE='<?php print $row['contact_via']; ?>' SIZE = 36></TD></TR>
				<TR VALIGN = 'TOP' CLASS='odd'>
					<TD ALIGN='right' CLASS="td_label">Subject: </TD><TD><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='right' CLASS="td_label">Message: </TD><TD><TEXTAREA NAME='frm_text' COLS=60 ROWS=2></TEXTAREA></TD></TR>
				<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
					<TD ALIGN='right' CLASS="td_label">Signal: </TD><TD>
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
						<SPAN STYLE='margin-left:20px;'><span style = 'font-weight: bold;'>Apply to:</span>&nbsp;&nbsp; Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>
						<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'>&nbsp;&nbsp;</SPAN>
					</TD>
				</TR>
				<TR VALIGN = 'TOP' CLASS='even' style = 'display: none;'>
					<TD ALIGN='right' CLASS="td_label">Standard Message: </TD><TD>

						<SELECT NAME='signals' onChange = 'set_message(this.options[this.selectedIndex].text);'>	<!--  11/17/10 -->
						<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
//					dump(__LINE__);
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]std_msgs` ORDER BY `id` ASC";	//	10/23/12
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
							print "\t<OPTION VALUE='{$row['id']}'>{$row['message']}</OPTION>\n";
							}
?>
						</SELECT>
						<BR />
					</TD>
				</TR>
				<TR VALIGN = 'TOP' CLASS='odd'>
					<TD ALIGN='center' COLSPAN=2><BR /><BR /><span style ='display: table; margin: 0 auto;'>
						<input type='button' 	value='Next'   class='plain text_small' onClick = "validate();">
						<input type='reset' 	value='Reset'  class='plain text_small' style = 'margin-left:40px;'>
						<input type='button'	value='Cancel' class='plain text_small' style = "margin-left:40px;" onClick = 'document.osw_form.mode.value = document.osw_form.mode_orig.value; document.osw_form.submit()' />
						</span>
					</TD>
				</TR>
				<TR><TD>&nbsp;</TD></TR>
				</TABLE>
				<INPUT type='hidden' NAME='frm_resp_ids' VALUE=''>
				<INPUT type='hidden' NAME='frm_smsg_ids' VALUE='<?php print $smsg_ids;?>'>
				<INPUT TYPE='hidden' NAME="use_smsg" VALUE='0'> 		<!-- see do_unit_mail -->
		</form>

</body>
<?php
        break;

    case 15:		// var do_MAIL_SEND = 15;
	        $_SESSION['osw_ntrupt_ok'] = TRUE;		// on-scene watch interrupt allowed (monitored by get_latest_id)
/*
//			$smsg_ids = ((isset($_POST['use_smsg'])) && ($_POST['use_smsg'] == 1)) ? $_POST['frm_smsg_ids'] : "";
			$smsg_ids = ((array_key_exists('use_smsg', $_POST)) && ($_POST['use_smsg'] == 1)) ? $_POST['frm_smsg_ids'] : "";
			$address_str = $_POST['frm_add_str'];
			$resp_ids = ((isset($_POST['frm_resp_ods'])) && ($_POST['frm_resp_ids'] != "") && ($_POST['frm_resp_ids'] != 0)) ? $_POST['frm_resp_ids'] : 0;
			$count = 0;
			$tik_id = ((isset($_POST['frm_ticket_id'])) && ($_POST['frm_ticket_id'] != 0)) ? $_POST['frm_ticket_id'] : 0;
//			$count = do_send ($address_str, $smsg_ids, $_POST['frm_subj'], $_POST['frm_text'], $tik_id, $_POST['frm_resp_ids']);	// ($to_str, $to_smsr, $subject_str, $text_str, $ticket_id, $responder_id )
*/
			$headers = "";
			$email_from =   	filter_var(get_variable('email_from'), FILTER_VALIDATE_EMAIL) ;
			$email_reply_to = 	filter_var(get_variable('email_reply_to'), FILTER_VALIDATE_EMAIL) ;
			$headers = ($email_from)? 	"From: {$email_from}\r\n" : "";
			$headers .= ($email_reply_to)? 	"Reply-To: {$email_reply_to}\r\n" : "";

			mail ( $address_str , $_POST['frm_subj'] , $_POST['frm_text'] , $headers );		// pending do_send() working
?>
<BODY onload = "setTimeout(function(){ osw_form.submit(); }, 2500);"><CENTER>		<!-- 1/14/10 -->
<CENTER><BR /><BR /><BR /><H3><?php print "Messages sent: {$count}";?></H3>
<BR /><BR /><BR /><INPUT TYPE='button' VALUE='Continue' class='plain text_small' onClick = 'do_can();'><BR /><BR />

<?php


?>
<body >			<!-- <?php echo __LINE__;?> -->
<form name = 'osw_form' method = post 	action = "<?php echo basename(__FILE__); ?>">
<input type = hidden name = "mode" 		value = "<?php echo $_POST['mode_orig'];?>" /> <!-- original entry  -->
<input type = hidden name = "mode_orig" value = "<?php echo $_POST['mode_orig'];?>" />
<input type = hidden name = "ref" 		value = "<?php echo $_POST['ref'];?>" />
</form>
<center>


</body>
<?php
        break;

    default:
        echo "error - error - error - error - error at line  " . __LINE__;
	}		// end switch ($mode)
?>
</BODY>
</HTML>
