<?php
/*
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
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
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` `m`
			ORDER BY `m`.`id` ASC" ;
	
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$no_members = mysql_affected_rows();
		$step = 1;
		} else {
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
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE>
BODY {FONT-SIZE: 1vw;}
INPUT {FONT-SIZE: 1vw;}
SELECT {FONT-SIZE: 1vw;}
OPTION {FONT-SIZE: 1vw;}
TABLE {FONT-SIZE: 1vw;}
TEXTAREA {FONT-SIZE: 1vw;}
.td_label {FONT-SIZE: 1vw;}
.plain {FONT-SIZE: 1vw;}
.hover {FONT-SIZE: 1vw;}
.container {display: table; border: 1px outset #FFFFFF; padding: 10px;}
.row { white-space: nowrap; display: table-row;}
.cell {display: table-cell; padding-left: 10px; padding-right: 10px;}
</STYLE>
<SCRIPT SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
	var viewportwidth, viewportheight;
	window.onresize=function(){set_size()};

	function set_size() {
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

	function reSizeScr(lines){
		var the_width = 720;
		var the_height = ((lines * 10)+200);			// values derived via trial/error (more of the latter, mostly)
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
					" WHERE `id` = " . quote_smart($_GET['name']) . " LIMIT 1";		 // if id supplied
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` {$where};";		// 
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_assoc($result));
?>
			<BODY scroll='auto' onLoad = "reSizeScr(1); document.mail_form.frm_subj.focus();"><CENTER>
			<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
			<TABLE ALIGN='center' BORDER = 0>
				<TR CLASS='odd'>
					<TH COLSPAN=2>Mail to: <?php print $row['field6']; ?></TH>
				</TR>
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='right' CLASS="td_label text">To: </TD>
					<TD>
						<INPUT TYPE='text' NAME='frm_add_str' VALUE='<?php print $row['field25']; ?>' SIZE = 36 />
					</TD>
				</TR>	
				<TR VALIGN = 'TOP' CLASS='odd'>
					<TD ALIGN='right' CLASS="td_label text">Subject: </TD>
					<TD>
						<INPUT TYPE = 'text' NAME = 'frm_subj' />
					</TD>
				</TR>	
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='right' CLASS="td_label text">Message: </TD>
					<TD>
						<TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA>
					</TD>
				</TR>
				<TR VALIGN = 'TOP' CLASS='even'>
					<TD ALIGN='center' COLSPAN=2>
						<SPAN id='sub_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_step_2();">Next</SPAN>
						<SPAN id='reset_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "document.mail_form.reset();">Reset</SPAN>
						<SPAN id='can_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();">Cancel</SPAN>
					</TD></TR>
				</TABLE>
				<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>	
				</FORM>	
<?php
								
				break;

		case 1:
			$query = "SELECT DISTINCT `ticket_id` , `scope`, `severity`, `ticket_id` AS `incident` FROM `$GLOBALS[mysql_prefix]assigns` 
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
				WHERE `t`.`status` = {$GLOBALS['STATUS_OPEN']}		
				ORDER BY `t`.`severity` DESC, `t`.`scope` ASC" ;
		
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
			<CENTER><H3>Mail to <?php print get_text("Members"); ?></H3>
			<P>

				
			<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='frm_step' VALUE='2'>	<!-- '2' = select units, '3' = send to selected units -->
			</FORM></P>
			<SPAN id='sub_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_step_1();">Next</SPAN>
			<SPAN id='can_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();">Cancel</SPAN>
			</CENTER>
	
<?php
			break;

		case 2:
	
			if ((!array_key_exists ( 'frm_sel_inc', $_POST)) || ($_POST['frm_sel_inc']==0)) {
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` `m`
					LEFT JOIN `$GLOBALS[mysql_prefix]member_status`	`s` ON (`m`.`field21` = `s`.`id`)
					WHERE LOCATE('@', `field25`) > 1
					ORDER BY  `field1` ASC ";
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
		<BODY scroll='auto' onLoad = "reSizeScr(<?php print $lines;?>); document.mail_form.frm_subj.focus();"><CENTER>
		<DIV id='outer' style='width: 80%; position: absolute; top: 10px; left: 10%;'>		
			<DIV id='topbar'><CENTER><H3>Mail to <?php print get_text("Members"); ?></H3></CENTER></DIV>
<?php
		if($no_rows>0) {
?>
			<DIV id='controls' style='position: relative; top: 10px; width: 100%;'>

			<SPAN ID='clr_spn' STYLE = 'display:block' onClick = 'do_clear()'>&raquo; <U>Un-check all</U></SPAN>
			<SPAN ID='chk_spn' STYLE = 'display:none'  onClick = 'do_check()'>&raquo; <U>Check all</U></SPAN>
			</DIV>
<?php
			}
?>
			<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
			<INPUT TYPE='hidden' NAME='frm_step' VALUE='3'>	<!-- '3' = select units, '3' = send to selected units -->
			<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->

<?php			
			if($no_rows>0) {
				$i=1;
?>
				<DIV id='leftcol' style='position: relative; top: 10px; left: 0px; width: 90%; border: 2px outset #707070; padding-left: 10%; padding-top: 5%; text-align: left;'>
					<DIV style='position: relative: left: 10%; top: 5%; width: 90%; max-height: 400px; overflow-y: scroll; overflow-x: none;' >
<?php
						while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
							$nickname = ($row['field6'] != "") ? $row['field6'] : "&nbsp;"; 
?>
							<SPAN STYLE='background-color:#F0F0F0; color:blue; display: inline; width: 80%;'>
								<SPAN style='width: 10%; display: inline-block;'>
									<INPUT style='display: inline;' TYPE='checkbox' NAME='cb<?php print $i;?>' VALUE="<?php print $row['field25'];?>" CHECKED>
								</SPAN>
								<SPAN style='width: 20%; display: inline-block;'>
									<?php print $nickname;?>
								</SPAN>
								<SPAN style='width: 60%; display: inline-block;'>
									(<I><?php print $row['field25'];?></I>)
								</SPAN>
							</SPAN>
							<BR />
<?php					
							$i++;
							}		// end while()
?>
					</DIV>
					<BR />
					<BR />
					<DIV STYLE='width: 100%; height: 100px;'>
						<DIV CLASS='td_label text_large' style='display: inline-block; width: 48%; vertical-align: top;'>Subject: </DIV>
						<DIV style='display: inline-block; width: 48%; vertical-align: top;'>
							<INPUT TYPE = 'text' NAME = 'frm_subj'>
						</DIV>
						<BR />
						<DIV CLASS='td_label text_large' style='display: inline-block; width: 48%; vertical-align: top;'>Message:</DIV>
						<DIV style='display: inline-block; width: 48%; vertical-align: top;'>
							<TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA>
						</DIV>
					</DIV>
					<BR />
					<BR />
					<BR />
					<BR />
					<DIV style='width: 100%; text-align: center;'>
						<SPAN id='sub_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_step_2();">Next</SPAN>
						<SPAN id='reset_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "document.mail_form.reset();">Reset</SPAN>
						<SPAN id='can_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();">Cancel</SPAN>
					</DIV>
					<BR />
					<BR />
				</DIV>


<?php
				} else {
?>
				<DIV id='leftcol' style='position: relative; top: 10px; width: 80%; padding-left: 10%; padding-top: 5%; text-align: left;'>
					<H3>No addresses available!</H3>
					<BR />
					<BR />
					<CENTER>
					<SPAN id='can_but' class = 'plain text' style='width: 100px; float: none; display: inline-block;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();">Cancel</SPAN>
					</CENTER>
					<BR />
					<BR />
				</DIV>
				<BR />
				<BR />
<?php
				}
?>
			</DIV>
<?php
			break;

		case 3:
			$count = do_send ($_POST['frm_add_str'], $_POST['frm_subj'], $_POST['frm_text'] );	// ($to_str, $subject_str, $text_str )

?>
<BODY scroll='auto' onLoad = "reSizeScr(2)"><CENTER>
<CENTER><BR /><BR /><BR /><H3><?php print "E-mails sent: {$count}";?></H3>
<BR /><BR /><BR /><INPUT TYPE='button' VALUE='Finished' onClick = 'window.close();'><BR /><BR />

<?php
			break;

		default:
		    echo __LINE__ . " error error error ";
		}

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
