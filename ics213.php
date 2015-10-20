<?php
/*
3/22/12 - initial release
3/8/2014 - additional ICS forms, cancel button added

*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
require_once('incs/functions.inc.php');		//7/28/10
$evenodd = array ("even", "odd");	// CLASS names for alternating tbl row colors

function html_mail ($to, $subject, $html_message, $from_address, $from_display_name=''){

//	$headers = 'From: ' . $from_display_name . ' <shoreas@gmail.com>' . "\n";
	$from = get_variable('email_from');
	$from = is_email($from)? $from : "info@ticketscad.org";
	$headers = "From: {$from_display_name}<{$from}>\n";

	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$temp = get_variable('email_reply_to');
	if (is_email($temp)){
	    $headers .= "Reply-To: {$temp}\r\n";
	    }

	$temp = @mail($to, $subject, $html_message, $headers); // boolean


//	snap(__LINE__, $temp);
	}			// end function html_mail ()

function template_213_t () {	// table-only portion of page
	global $item;
	$out_str = "<TABLE DIR='LTR' BORDER=1 BORDERCOLOR='#000000' CELLPADDING=0 CELLSPACING=0 STYLE='width: 20.32cm;'>";
	$out_str .= "\n<FORM NAME = 'ics213_form' METHOD = 'post' ACTION = '" . basename(__FILE__) . "' >\n";
	$out_str .= "\n<INPUT TYPE = 'hidden' NAME = 'frm_add_str' VALUE = '{$_POST['frm_add_str']}'/>\n";
	$out_str .= "<INPUT TYPE = 'hidden' NAME = 'step' VALUE = 2>
		<COL WIDTH=46*>
		<COL WIDTH=54*>
		<COL WIDTH=23*>
		<COL WIDTH=9*>
		<COL WIDTH=44*>
		<COL WIDTH=79*>
		<TR>
			<TD COLSPAN=6 WIDTH=100% VALIGN=TOP BGCOLOR=\"#f2f2f2\">
				<P CLASS=\"western\" ALIGN=CENTER >&nbsp;GENERAL MESSAGE</FONT></P>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD COLSPAN=3 WIDTH=48% HEIGHT=30> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;<B>TO</B></FONT><FONT SIZE=1 STYLE=\"font-size: 8pt\">:&nbsp;{$item[1]}</FONT></P> </TD>
			<TD COLSPAN=3 WIDTH=52%> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;POSITION:&nbsp;{$item[2]}</FONT></P> </TD>
		</TR>
		<TR VALIGN=TOP>
			<TD COLSPAN=3 WIDTH=48% HEIGHT=30> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;FROM:&nbsp;{$item[3]} </FONT></P> </TD>
			<TD COLSPAN=3 WIDTH=52%> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;POSITION:&nbsp;{$item[4]}</FONT></P> </TD>
		</TR>
		<TR VALIGN=TOP>
			<TD COLSPAN=3 WIDTH=48% HEIGHT=30> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;SUBJECT:&nbsp;{$item[5]} </FONT></P> </TD>
			<TD COLSPAN=2 WIDTH=28%> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;DATE:&nbsp;{$item[6]}</FONT></P> </TD>
			<TD WIDTH=24%> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;TIME:&nbsp;{$item[7]}</FONT></P> </TD>
		</TR>
		<TR>
			<TD COLSPAN=6 WIDTH=100% VALIGN=TOP BGCOLOR=\"#e5e5e5\"> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;MESSAGE:</FONT></P> </TD>
		</TR>
		<TR>
			<TD COLSPAN=6 WIDTH=100% HEIGHT=100 VALIGN=TOP> <P CLASS=\"western\" > {$item[8]} <BR> </P> </TD>
		</TR>
		<TR VALIGN=TOP>
			<TD COLSPAN=4 WIDTH=52% HEIGHT=27> <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\"> <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;SIGNATURE:&nbsp;{$item[9]}</FONT></P>  </TD>
			<TD COLSPAN=2 WIDTH=48%> <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\"> <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;POSITION:&nbsp;{$item[10]}</FONT></P>  </TD>
		</TR>
		<TR>
			<TD COLSPAN=6 WIDTH=100% VALIGN=TOP BGCOLOR=\"#e5e5e5\"> <P CLASS=\"western\" > <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;REPLY:</FONT></P> </TD>
		</TR>
		<TR>
			<TD COLSPAN=6 WIDTH=100% HEIGHT=100 VALIGN=TOP> <P CLASS=\"western\" > {$item[11]}<BR> </P> </TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=30%> <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\"> <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;DATE:&nbsp;{$item[12]}</FONT></P>  </TD> <TD WIDTH=20%> <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\"> <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;TIME:&nbsp;{$item[13]}</FONT></P>  </TD>
			<TD COLSPAN=4 WIDTH=50%> <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\"> <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;SIGNATURE/POSITION:&nbsp;{$item[14]}</FONT></P>  </TD>
		</TR>
		</TABLE><BR />";
	return $out_str;
	}							// end function template_213_t ()


	function template_213 ($do_form = TRUE) {		// returns full page
		global $item;
		$out_str = "<!DOCTYPE html>
	<HTML>
	<HEAD>
		<META HTTP-EQUIV=\"CONTENT-TYPE\" CONTENT=\"text/html; charset=windows-1252\">
		<TITLE>ICS-213 GENERAL MESSAGE</TITLE>
	<META NAME=\"CHANGEDBY\" CONTENT=\"Arnie Shore\">
	<META NAME=\"CHANGED\" CONTENT=\"20071223;14270000\">
	<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=UTF-8\">
	<META HTTP-EQUIV=\"Expires\" CONTENT=\"0\">
	<META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"NO-CACHE\">
	<META HTTP-EQUIV=\"Pragma\" CONTENT=\"NO-CACHE\">

	<STYLE TYPE=\"text/css\">--
	<!--
		@page { size: 8.5in 11in; margin: 0.5in }
		P { margin-bottom: 0.08in; direction: ltr; color: #000000; text-align: left; widows: 0; orphans: 0 }
		P.western { font-family: \"Arial, sans-serif; font-size: 10pt; so-language: en-US; margin-left: 0.01in; margin-top: 0.04in;}
		P.cjk { font-family: \"Times New Roman\", serif; font-size: 10pt; so-language: zxx }
		P.ctl { font-family: \"Times\", \"Times New Roman\", serif; font-size: 10pt; so-language: ar-SA }
		A.sdfootnotesym-western { font-size: 8pt }
		A.sdfootnotesym-cjk { font-size: 8pt }
	-->
	</STYLE>

</HEAD>
<BODY LANG=\"en-US\" TEXT=\"#000000\" BGCOLOR=\"#ffffff\" DIR=\"LTR\"> <!-- 115 -->
<P CLASS=\"western\" ALIGN=LEFT STYLE=\"margin-bottom: 0in\">";

	$out_str .= template_213_t ();		// table string

	$out_str .=  "</BODY></HTML>";
	return $out_str;
	}							// end function template_213 ()

// do_login(basename(__FILE__));
?>
<!DOCTYPE html>
<HTML>	<!-- 171 -->
<HEAD>
<TITLE><?php echo LessExtension(basename(__FILE__));?></TITLE>
<META NAME="Description" CONTENT="<?php print basename(__FILE__);?>">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="6/13/09">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<script src = "./js/jquery-1.4.2.min.js"></script>
<script src="./js/misc_function.js" TYPE="text/javascript"></script>	<!-- 9/14/12 -->

<?php

$step = (array_key_exists ("step", $_POST))? $_POST['step']: 0 ;
switch ($step) {
	case 0:								/*  collect addresses */
?>

<script type="application/javascript">

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

	function do_mail_str(in_action) {		// argument is scriptname as string - 3/8/2014
		sep = "";
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
		document.mail_form.action = in_action;
		document.mail_form.submit();
		return true;
		}


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
	</HEAD> <!-- 217 c a s e  0 -->
	<BODY><CENTER><BR /><BR />
<?php
	$i=0;		// 3/6/2014
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts`
		ORDER BY `organization` ASC,`name` ASC" ;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	if(mysql_affected_rows()>0) {

	function do_row($i, $addr, $name, $org) {
		global $evenodd;
		$return_str = "<TR CLASS= '{$evenodd[($i)%2]}'>";
		$js_i = $i+1;
		$return_str .= "\t\t<TD>&nbsp;<INPUT TYPE='checkbox' CHECKED NAME='cb{$js_i}' VALUE='{$addr}'>";
		$return_str .= "&nbsp;{$addr} / {$name} / {$org}</TD></TR>\n";
		return $return_str;
		}				// end function do_row()

?>
	<P>
		<TABLE ALIGN='center'>
		<TR CLASS = 'even'><TH>ICS Form to Contacts</TH></TR>
		<TR CLASS = 'odd'><TD ALIGN = 'center'><BR />
			<SPAN ID='clr_spn' STYLE = 'display:block' onClick = 'do_clear()'>&raquo; <U>Un-check all</U></SPAN>
			<SPAN ID='chk_spn' STYLE = 'display:none'  onClick = 'do_check()'>&raquo; <U>Check all</U></SPAN><BR />
		</TD></TR>


		<FORM NAME='mail_form' METHOD='post' ACTION='void(0)'>
		<INPUT TYPE='hidden' NAME='step' VALUE='1'>
		<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->
<?php
	while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
																				// count valid addresses
		if (is_email($row['email']))	{ echo do_row($i, $row['email'], $row['name'], $row['organization']);$i++;}
		if (is_email($row['mobile'])) 	{ echo do_row($i, $row['mobile'], $row['name'], $row['organization']);$i++;}
		if (is_email($row['other'])) 	{ echo do_row($i, $row['other'], $row['name'], $row['organization']);$i++;}
		}		// end while()

?>
		<TR CLASS='<?php print $evenodd[($i)%2]; ?>'><TD ALIGN='center' COLSPAN=3><BR /><BR />&nbsp;
			<INPUT TYPE='button' 	VALUE='ICS205' 		onClick = "do_mail_str('ics205.php');">
			<INPUT TYPE='button' 	VALUE='ICS205-A' 	onClick = "do_mail_str('ics205a.php');"  style = "margin-left:20px;">
			<INPUT TYPE='button' 	VALUE='ICS213' 		onClick = "do_mail_str('ics213.php');"   style = "margin-left:20px;">
			<INPUT TYPE='button' 	VALUE='ICS213-RR' 	onClick = "do_mail_str('ics213rr.php');" style = "margin-left:20px;">	<!-- 10/2/2014 -->
			<INPUT TYPE='button' 	VALUE='ICS214' 		onClick = "do_mail_str('ics214.php');"   style = "margin-left:20px;">
			<p>
			<INPUT TYPE='reset' 	VALUE='Reset'>
			<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();' style = "margin-left:60px;">
			</p>
			</TD></TR>
			</FORM></TABLE>

<?php
			}		// end if(mysql_affected_rows()>0)
		if (($i==0) || (mysql_affected_rows()==0)){
?>
	<H3>No Contact e-mail addresses!</H3><BR /><BR />
	<INPUT TYPE='button' VALUE='Cancel' onClick = 'window.close();'><BR /><BR />

<?php
			}
		else {
			}
// ------------------------------
		break;		// end case 0
	case 1:								/* present form to user */
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = {$_SESSION['user_id']} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));

		$the_date = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
		$the_time =  date( "H:i",(time()- (intval(get_variable('delta_mins')*60)) ));
		$the_from = "{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";
		$temp = $row['name_l'].$row['name_f'].$row['name_mi'];
		$the_from = (empty($temp))? "" : "{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";

//		$the_from = (empty($row['name_l'].$row['name_f'].$row['name_mi']))? "" :
//								"{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";

		function in_str( $name, $size, $tabindex, $data = "") {
			return "<input type=text id='f{$name}'  name='f{$name}' size={$size} maxlength={$size} value='{$data}' tabindex={$tabindex} />";
			}

		function in_text ($name, $cols, $rows, $tabindex, $data = "") {
			return "<textarea id='f{$name}'  name='f{$name}' cols={$cols} rows={$rows} tabindex={$tabindex}>{$data}</textarea>";
			}

		$item[1] =  in_str  (1, 36, 1); // $name, $size, $tabindex
		$item[2] =  in_str  (2, 36, 2);
		$item[3] =  in_str  (3, 36, 3, $the_from);
		$item[4] =  in_str  (4, 36, 4);
		$item[5] =  in_str  (5, 36, 5);
		$item[6] =  in_str  (6, 16, 6, $the_date);
		$item[7] =  in_str  (7, 12, 7, $the_time);
		$item[8] =  in_text (8, 90, 4, 8); // $name, $cols, $rows, $tabindex
//		$item[8] =  $_POST['frm_add_str']; // $name, $cols, $rows, $tabindex
		$item[9] =  in_str  (9, 36, 9, $the_from);
		$item[10] = in_str  (10, 32, 10);
		$item[11] = in_text (11, 90, 4, 11);
		$item[12] = in_str  (12, 16, 12);
		$item[13] = in_str  (13, 8, 13);
		$item[14] = in_str  (14, 34, 14);
?>

<SCRIPT type='text/javascript'>
	function validate(theForm) {						// form contents validation
		var errmsg='';
		if (theForm.f1.value.trim()=='')	{errmsg+='TO is required.\n';}
		if (theForm.f3.value.trim()=='')	{errmsg+='FROM is required.\n';}
		if (theForm.f5.value.trim()=='')	{errmsg+='SUBJECT is required.\n';}
		if (theForm.f8.value.trim()=='')	{errmsg+='MESSAGE is required.\n';}
		if (errmsg!='') {
			alert ('Please correct the following and re-submit:\n\n' + errmsg);
			return false;
			}
		else {			// good to go!
			return true;
			}
		}				// end function validate(theForm)

</SCRIPT>
<!-- 1/1/2015 -->
<STYLE TYPE="text/css">
.box { background-color: transparent; border: 0px solid #000000; color: #000000; padding: 0px; position: absolute; z-index:1000; }
.bar { background-color: #DEE3E7; color: #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; }
.content { padding: 1em; }
</STYLE>

<?php
		echo "\n<center>\n";
		echo template_213_t();
?>
<div id="boxB" class="box" style="left:5px; top:20px;  background-color : transparent;">
  <div class="bar" STYLE="color:red; background-color : transparent; width:60px;"
       onmousedown="dragStart(event, 'boxB')"><i>Drag us</i></div>
  <div class="content" style="width:auto; text-align: left">
		<input type = 'button' value= 'Submit' onclick = 'if(validate(document.ics213_form)) {document.ics213_form.submit();}' /><br />
		<input type = 'reset'  value= 'Reset'  onclick = 'document.ics213_form.reset();'  style='margin-top: 10px;'/><br />
		<input type = 'button' value= 'Cancel' onclick = 'window.close();' style='margin-top: 10px;' />
		</div>
	</div>

<?php
		break;		// end case 1

	case 2:								/*  process form and address data */

//		dump($_POST);

		$item[1] =  trim($_POST['f1']); 	// to
		$item[2] =  trim($_POST['f2']); 	// position
		$item[3] =  trim($_POST['f3']); 	// from
		$item[4] =  trim($_POST['f4']); 	// position
		$item[5] =  trim($_POST['f5']); 	// subject
		$item[6] =  trim($_POST['f6']); 	// date
		$item[7] =  trim($_POST['f7']); 	// time
		$item[8] =  trim($_POST['f8']); 	// message
		$item[9] =  trim($_POST['f9']); 	// signature
		$item[10] = trim($_POST['f10']); 	// position
		$item[11] = trim($_POST['f11']); 	// reply
		$item[12] = trim($_POST['f12']); 	// date
		$item[13] = trim($_POST['f13']); 	// time
		$item[14] = trim($_POST['f14']); 	// signature/position

		$html_message = template_213(FALSE);

		$to_array = explode ("|", $_POST['frm_add_str']);
		$to = $sep = "";
		for ($i=0; $i < count($to_array); $i++) {
			$to .= "{$sep}{$to_array[$i]}";
			$sep = ",";
			}		// end for ()
		$subject ="ICS-213 Message - {$item[5]}";		// subject, per form data
		$temp = get_variable('email_from');
		$from_address = (is_email($temp))? $temp: "ticketscad.org";
		$from_display_name=get_variable('title_string');
		$temp = shorten(strip_tags(get_variable('title_string')), 30);
		$from_display_name = str_replace ( "'", "", $temp);
		$result = html_mail ($to, $subject, $html_message, $from_address, $from_display_name);

//		do_log($GLOBALS['LOG_ICS213_MESSAGE_SEND'], 0, 0, $item[5], 0, 0,0);	// subject line as info column
?>
</HEAD> <!-- 399 case 2 -->

<BODY onLoad = "setTimeout('window.close()',3500);">	<!-- 379 -->
<DIV style = 'margin-left:400px; margin-top100px;'><H2>ICS-213 MAIL SENT - window closing ... </H2></DIV>

<?php

		break;							/* end process form and address data */

	default:							/* error????  */
	    echo " error  error  error at;  " . __LINE__;
	}				// end switch
?>
</BODY>
</HTML>
