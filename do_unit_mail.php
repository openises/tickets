<?php
/*
6/13/09	initial release
6/28/09	handle no assigns and empty scope
3/21/10 div, table re-arrange, add color-coding by status,  legend
4/28/10 open tickets only, order by severity
5/25/10 size changes applied
7/1/10 restrict to active assigns this incident
7/2/10 accomodate facility email  
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
11/15/10 signals added
11/19/10 window width set as fixed, MySQL LOCATE() employed for email addr test
1/14/10 status legend, email count added, get_text units, incident
2/8/11 dumps removed, for production
3/15/11 changed stylesheet.php to stylesheet.php
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

@session_start();
require_once('./incs/functions.inc.php');
// dump($_GET);
// dump($_POST);


if (!(empty($_GET))) {
	$step = (((integer) $_GET['name'])==0)? 2 : 0 ;
//	print $_GET['addrs'];
//	$step = 0;		// unit id - or 0 - passed as get ['name']
	}
else {
//	dump(__LINE__);
	if (empty($_POST)) {
		$query = "SELECT DISTINCT `ticket_id` , scope, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
			WHERE `status` = {$GLOBALS['STATUS_OPEN']}
			ORDER BY `t`.`severity` DESC, `t`.`scope` ASC" ;				// 4/28/10
	
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$no_open_tickets = mysql_affected_rows();
		if($no_open_tickets==0) {			// 6/28/09
			$step = 2;
			}
		else{
			$step = 1;
			}
		}
	else {
	
		$step = $_POST['frm_step'];
		}
	}
//dump(__LINE__);
//dump($step);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print LessExtension(basename(__FILE__));?> </TITLE>
<META NAME="Description" CONTENT="Email to units and facilities">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="6/13/09">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT>
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

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
	
	function do_step_1() {
		document.mail_form.submit();
		}

	function do_step_2() {
		if (document.mail_form.frm_text.value.trim()=="") {
			alert ("Message text is required");
			return false;
			}
		var sep = "";
		for (i=0;i<document.mail_form.elements.length; i++) {
			if((document.mail_form.elements[i].type =='checkbox') && (document.mail_form.elements[i].checked)){		// frm_add_str
				document.mail_form.frm_add_str.value += sep + document.mail_form.elements[i].value;
				sep = "|";
				}
			}
		if (document.mail_form.frm_add_str.value.trim()=="") {
			alert ("Addressees required");
			return false;
			}
		document.mail_form.submit();	
		}

	function reSizeScr(lines){							// 5/25/10 
		var the_width = 1200;							// 11/19/10
		var the_height = ((lines * 18)+200);			// values derived via trial/error (more of the latter, mostly)
		if (the_height <400) {the_height = 400;}
		window.resizeTo(the_width,the_height);	
		}
	var set_text = true;

	function set_signal(inval) {
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

</SCRIPT>
</HEAD>
<?php
	switch($step) {
			case 0:
				$where = (((integer) $_GET['name'])==0)? 
					" ORDER BY `name` ASC " : 
					" WHERE `id` = {$_GET['name']} LIMIT 1";		 // if id supplied
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` {$where};";		// 
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_assoc($result));
			
//				$arr = explode(",", $_GET['name']);
?>
			<BODY scroll='auto' onLoad = "reSizeScr(1); document.mail_form.frm_subj.focus();"><CENTER>		<!-- 1/12/09 -->
			<TABLE ALIGN='center' BORDER = 0>
				<TR CLASS='odd'><TH COLSPAN=2>Mail to: <?php print $row['name']; ?></TH></TR> <!-- 7/2/10 -->
				
				<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
				<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>	
				<TR VALIGN = 'TOP' CLASS='even'><TD ALIGN='right'  CLASS="td_label">To: </TD>
					<TD><INPUT TYPE='text' NAME='frm_add_str' VALUE='<?php print $row['contact_via']; ?>' SIZE = 36></TD></TR>	
			
				<TR VALIGN = 'TOP' CLASS='odd'>
					<TD ALIGN='right' CLASS="td_label">Subject: </TD><TD><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>	
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='right' CLASS="td_label">Message: </TD><TD> <TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA></TD></TR>
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
					<SPAN STYLE='margin-left:20px;'>Apply to: Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>
					<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'>&nbsp;&nbsp;</SPAN>
					</TD></TR>	

				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='center' COLSPAN=2><BR /><BR />
						<INPUT TYPE='button' 	VALUE='Next' onClick = "do_step_2()">&nbsp;&nbsp;&nbsp;&nbsp;
						<INPUT TYPE='reset' 	VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;
						<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'><BR /><BR />
					</TD></TR>
				</TABLE></FORM>
	
<?php
								
				break;

		case 1:
			$query = "SELECT DISTINCT `ticket_id` , `scope`, `severity`, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
				WHERE `t`.`status` = {$GLOBALS['STATUS_OPEN']}		
				ORDER BY `t`.`severity` DESC, `t`.`scope` ASC" ;				// 4/28/10
		
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			$no_tickets = mysql_affected_rows();
			if($no_tickets==1) {
				$row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC) ;
//				dump($row);
?>
<BODY scroll='auto' onLoad = "document.mail_form_single.submit();">	<!-- 1/12/09 -->
<FORM NAME='mail_form_single' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
<INPUT TYPE='hidden' NAME='frm_step' VALUE='2'>	<!-- '2' = select units, '3' = send to selected units -->
<INPUT TYPE='hidden' NAME='frm_sel_inc' VALUE='<?php print $row['ticket_id'];?>'>	
</FORM></BODY></HTML>			
<?php			
				}
			
?>		
<BODY scroll='auto' onLoad = "reSizeScr(1); document.mail_form.frm_subj.focus();"><CENTER>		<!-- 1/12/09 -->
<CENTER><H3>Mail to <?php print get_text("Units"); ?></H3>
<P>

	
<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
<INPUT TYPE='hidden' NAME='frm_step' VALUE='2'>	<!-- '2' = select units, '3' = send to selected units -->
<?php
	$bg_colors_arr = array ("transparent", "lime", "red");		// for severity
	if($no_tickets >= 2) {
		print "<EM>". get_text("Units"). " assigned to ". get_text("Incident") . "</EM>: 
			<SELECT NAME='frm_sel_inc' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color;'>\n\t
			<OPTION VALUE=0 SELECTED>All incidents </OPTION>\n";
		while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
			$bg_color = $bg_colors_arr[$row['severity']];
			if(!(empty($row['scope']))) {				// 6/28/09
				print "\t<OPTION VALUE='{$row['incident']}' STYLE='background-color:{$bg_color}; color:black;' >{$row['scope']} </OPTION>\n";
				}
			}
		}		// end if($no_tickets >= 2)
?>
	</SELECT></FORM></P>
	<BR /><BR />
	<INPUT TYPE='button' VALUE='Next' onClick = "do_step_1()">&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE='button' VALUE='Cancel' onClick = 'window.close();'>
	</CENTER>
	
<?php
			break;

		case 2:													// 9/19/10
//			dump(__LINE__);
		
			if ((!array_key_exists ( 'frm_sel_inc', $_POST)) || ($_POST['frm_sel_inc']==0)) {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `r`
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	`s` ON (`r`.`un_status_id` = `s`.`id`)
					WHERE LOCATE('@', `contact_via`) > 1
					ORDER BY  `name` ASC ";
//			dump(__LINE__);
//			dump($step);
				}
			else {												// 7/1/10 - 9/19/10
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` `a`
					LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status`	 `s` ON (`r`.`un_status_id` = `s`.`id`)
					WHERE `ticket_id` = {$_POST['frm_sel_inc']} AND LOCATE('@', `contact_via`) > 1
					AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))
					ORDER BY `name` ASC ";
//			dump(__LINE__);
//			dump($query);
				}
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
			$lines = mysql_affected_rows() +8;
			$no_rows = mysql_affected_rows();
?>
			<SCRIPT>
			
			function do_clear(){
				for (i=0;i<document.mail_form.elements.length; i++) {
					if(document.mail_form.elements[i].type =='checkbox'){
						document.mail_form.elements[i].checked = false;
						}
					}		// end for ()
				$('clr_spn').style.display = "none";
				$('chk_spn').style.display = "block";
				}		// end function do_clear

			function do_check(){
				for (i=0;i<document.mail_form.elements.length; i++) {
					if(document.mail_form.elements[i].type =='checkbox'){
						document.mail_form.elements[i].checked = true;
						}
					}		// end for ()
				$('clr_spn').style.display = "block";
				$('chk_spn').style.display = "none";
				}		// end function do_clear

			</SCRIPT>
		<BODY scroll='auto' onLoad = "reSizeScr(<?php print $lines;?>); document.mail_form.frm_subj.focus();"><CENTER>		<!-- 1/12/09  -->
		<TABLE ALIGN = 'center' border=0>
			<TR><TD COLSPAN=99 ALIGN='center'>
			<CENTER><H3>Mail to <?php print get_text("Units"); ?></H3>
		</TD></TR>
<?php
		if($no_rows>0) {
?>
			<TR><TD COLSPAN=99 ALIGN='center'>

			<SPAN ID='clr_spn' STYLE = 'display:block' onClick = 'do_clear()'>&raquo; <U>Un-check all</U></SPAN>
			<SPAN ID='chk_spn' STYLE = 'display:none'  onClick = 'do_check()'>&raquo; <U>Check all</U></SPAN>
			</TD></TR>
<?php
		}
?>
			<P>
			
			<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>	<!-- '3' = select units, '3' = send to selected units -->
			<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->

<?php			
				if($no_rows>0) {
					$i=1;
//					print "<TABLE ALIGN = 'center'>";
					print "<TR><TD COLSPAN = 3 ALIGN='center'>" . get_units_legend() . "<BR /></TD></TR";
					print "<TR><TD>\n";
					print "<TABLE ALIGN = 'center' BORDER=0><TR><TD>\n";
					print "<DIV  style='width:auto;height:500PX; overflow-y: scroll; overflow-x: none;' >";
					while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
						print "\t<SPAN STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'>
							<INPUT TYPE='checkbox' NAME='cb{$i}' VALUE='{$row['contact_via']}' CHECKED>
							&nbsp;&nbsp;{$row['name']}&nbsp;&nbsp;&nbsp;&nbsp;(<I>{$row['contact_via']}</I>)</SPAN><BR />\n";				
						$i++;
						}		// end while()
?>
			</DIV></TD></TR></TABLE>
			</TD><TD>
			<TABLE BORDER=0>
			<TR VALIGN='top' CLASS='even'><TD CLASS="td_label" ALIGN='right'>Subject: </TD><TD><INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>
			<TR VALIGN='top' CLASS='odd'><TD CLASS="td_label" ALIGN='right'>Message: </TD><TD><TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA></TD></TR>

			<TR VALIGN = 'TOP' CLASS='even'>
				<TD ALIGN='right' CLASS="td_label">Signal: </TD><TD>

					<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
					<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
//					dump(__LINE__);
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
					while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
						print "\t<OPTION VALUE='{$row['code']}'>{$row['code']}|{$row['text']}</OPTION>\n";

						}
?>
					</SELECT>

					<SPAN STYLE='margin-left:20px;'>Apply to: Subject &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='0' CHECKED onClick = 'set_text = false;'></SPAN>
					<SPAN STYLE='margin-left:20px;'>Text &raquo;<INPUT TYPE='radio' NAME='frm_set_where' VALUE='1' CHECKED onClick = 'set_text = true;'></SPAN>
				
					</TD></TR>	

			<TR VALIGN='top' CLASS='odd'><TD ALIGN='center' COLSPAN=2><BR /><BR />
				<INPUT TYPE='button' 	VALUE='Next' onClick = "do_step_2()">&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='reset' 	VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'><BR /><BR />
				</TD></TR>

<?php
				print "</TABLE></TD></TR></TABLE></FORM>";
//				print get_unit_status_legend();
				
				}		// end if(mysql_affected_rows()>0)
			else {
				print "<H3>No addresses available!</H3>\n";
				print "<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'><BR /><BR />";
				}
		
			break;

		case 3:
			$count = do_send ($_POST['frm_add_str'], $_POST['frm_subj'], $_POST['frm_text'] );	// ($to_str, $subject_str, $text_str )
//			snap(__LINE__, $count);
?>
<BODY scroll='auto' onLoad = "reSizeScr(2)"><CENTER>		<!-- 1/14/10 -->
<CENTER><BR /><BR /><BR /><H3><?php print "E-mails sent: {$count}";?></H3>
<BR /><BR /><BR /><INPUT TYPE='button' VALUE='Finished' onClick = 'window.close();'><BR /><BR />

<?php
			break;

		default:
		    echo __LINE__ . " error error error ";
		}

?>
</BODY>
</HTML>
