<?php
/*
2/26/2014 - initial release
3/8/2014 - revised for inline style vs css
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
require_once('incs/functions.inc.php');		//7/28/10

//dump($_POST);

function template_205 ($item) {
		$body_style	= ' MARGIN:0; FONT-WEIGHT: normal; COLOR: #000000; TEXT-DECORATION: none ';
		$table_style	= 'width:auto; border:2px solid black;  border-collapse: collapse;';
		$table_upper_style= 'width:auto; border-collapse: collapse; border:none; ';

		$tr_fat_style = 'background-color: white; height: 40px; vertical-align:text-top; border:1px solid black; ';
		$tr_thin_style = 'background-color: white; height: 21px; vertical-align:middle;  border:none; ';
		$tr_plain_style = 'background-color: white; height: 21px; border:none; ';

		$td_heading_style= 'FONT-WEIGHT: 900; FONT-SIZE: 11px;  border:1px solid black; ';
		$td_heading_3_style = 'FONT-WEIGHT: 400; FONT-SIZE: 12px;  border:1px solid black; text-align: left; ';
		$td_heading_4_style  = 'FONT-WEIGHT: 900; FONT-SIZE: 11px;  text-align: right;  border:1px solid black; ';
		$td_plain_style= 'FONT-WEIGHT: 400; FONT-SIZE: 11px; text-align: left; border: 1px solid black;  ';
		$td_plain_l_style= 'FONT-WEIGHT: 400; FONT-SIZE: 11px; text-align: center;  border:1px solid black; ';
		$input_style	= 'font-size: 12px; font-family: monospace; ';
		$textarea_style  = 'font-size: 12px; font-family: monospace; ';

	return "\n
		<table style = '{$table_style}'>
		<colgroup>
		<col style = 'width: 1.25in;'>
		<col style = 'width: .75in;'>
		<col style = 'width: 1.25in;'>
		<col style = 'width: 1.25in;'>
		<col style = 'width: 1.25in;'>
		<col style = 'width: 1.5in'>
		</colgroup>
		<tr style= '{$tr_thin_style}' >
		<td colspan = 6>
			<table style = '{$table_upper_style}'>
			<colgroup>
			<col style = 'width: 1.25in;'>
			<col style = 'width: 2.0in;'>
			<col style = 'width: 1.75in;'>
			<col style = 'width: 2.75in;'>
			</colgroup>
			<tr style = '{$tr_thin_style}'>
			<td style = '{$td_heading_style}' colspan=1>&nbsp;1. Incident Name</td>
			<td style = '{$td_heading_style}' colspan=2>&nbsp;2. Operational Period (Date / Time)</td>
			<td style= '{$td_heading_4_style}'colspan=1>&nbsp;INCIDENT RADIO COMMUNICATIONS PLAN</td>
			</tr>
			<tr style = '{$tr_thin_style}'>
			<td style = '{$td_heading_style}' colspan=1>{$item[1]}</td>
			<td style = '{$td_heading_style}' colspan=1>&nbsp; From {$item[2]}  {$item[3]}</td>
			<td style = '{$td_heading_style}' colspan=1>&nbsp; To {$item[4]}  {$item[5]}</td>
			<td style= '{$td_heading_4_style}'colspan=1>&nbsp;ICS 205-CG</td>
			</tr>
			</table>
		</td>
		</tr>

		<tr style = '{$tr_thin_style}'>
		<td style = '{$td_heading_style}' colspan=6>&nbsp;3. BASIC RADIO CHANNEL USE</td>
		</tr>
		<tr style = '{$tr_thin_style}'>
		<td style = '{$td_plain_l_style}'> SYSTEM / CACHE</td>
		<td style = '{$td_plain_l_style}'> CHANNEL</td>
		<td style = '{$td_plain_l_style}'> FUNCTION</td>
		<td style = '{$td_plain_l_style}'> FREQUENCY</td>
		<td style = '{$td_plain_l_style}'> ASSIGNMENT</td>
		<td style = '{$td_plain_l_style}'> REMARKS</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[6]}</td>
		<td style = '{$td_plain_style}'> {$item[7]}</td>
		<td style = '{$td_plain_style}'> {$item[8]}</td>
		<td style = '{$td_plain_style}'> {$item[9]}</td>
		<td style = '{$td_plain_style}'> {$item[10]}</td>
		<td style = '{$td_plain_style}'> {$item[11]}</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[12]}</td>
		<td style = '{$td_plain_style}'> {$item[13]}</td>
		<td style = '{$td_plain_style}'> {$item[14]}</td>
		<td style = '{$td_plain_style}'> {$item[15]}</td>
		<td style = '{$td_plain_style}'> {$item[16]}</td>
		<td style = '{$td_plain_style}'> {$item[17]}</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[18]}</td>
		<td style = '{$td_plain_style}'> {$item[19]}</td>
		<td style = '{$td_plain_style}'> {$item[20]}</td>
		<td style = '{$td_plain_style}'> {$item[21]}</td>
		<td style = '{$td_plain_style}'> {$item[22]}</td>
		<td style = '{$td_plain_style}'> {$item[23]}</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[24]}</td>
		<td style = '{$td_plain_style}'> {$item[25]}</td>
		<td style = '{$td_plain_style}'> {$item[26]}</td>
		<td style = '{$td_plain_style}'> {$item[27]}</td>
		<td style = '{$td_plain_style}'> {$item[28]}</td>
		<td style = '{$td_plain_style}'> {$item[29]}</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[30]}</td>
		<td style = '{$td_plain_style}'> {$item[31]}</td>
		<td style = '{$td_plain_style}'> {$item[32]}</td>
		<td style = '{$td_plain_style}'> {$item[33]}</td>
		<td style = '{$td_plain_style}'> {$item[34]}</td>
		<td style = '{$td_plain_style}'> {$item[35]}</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[36]}</td>
		<td style = '{$td_plain_style}'> {$item[37]}</td>
		<td style = '{$td_plain_style}'> {$item[38]}</td>
		<td style = '{$td_plain_style}'> {$item[39]}</td>
		<td style = '{$td_plain_style}'> {$item[40]}</td>
		<td style = '{$td_plain_style}'> {$item[41]}</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[42]}</td>
		<td style = '{$td_plain_style}'> {$item[43]}</td>
		<td style = '{$td_plain_style}'> {$item[44]}</td>
		<td style = '{$td_plain_style}'> {$item[45]}</td>
		<td style = '{$td_plain_style}'> {$item[46]}</td>
		<td style = '{$td_plain_style}'> {$item[47]}</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[48]}</td>
		<td style = '{$td_plain_style}'> {$item[49]}</td>
		<td style = '{$td_plain_style}'> {$item[50]}</td>
		<td style = '{$td_plain_style}'> {$item[51]}</td>
		<td style = '{$td_plain_style}'> {$item[52]}</td>
		<td style = '{$td_plain_style}'> {$item[53]}</td>
		</tr>
		<tr style= '{$tr_fat_style}'>
		<td style = '{$td_plain_style}'> {$item[54]}</td>
		<td style = '{$td_plain_style}'> {$item[55]}</td>
		<td style = '{$td_plain_style}'> {$item[56]}</td>
		<td style = '{$td_plain_style}'> {$item[57]}</td>
		<td style = '{$td_plain_style}'> {$item[58]}</td>
		<td style = '{$td_plain_style}'> {$item[59]}</td>
		</tr>

		<tr style = '{$tr_thin_style}'>
		<td style = '{$td_heading_style}' colspan=4>&nbsp;4. Prepared by: (Communications Unit)</td>
		<td style = '{$td_heading_style}' colspan=2>&nbsp;Date / Time</td>
		</tr>
		<tr style = '{$tr_thin_style}'>
		<td style = '{$td_heading_style}' colspan=4>&nbsp;{$item[60]}</td>
		<td style = '{$td_heading_style}' colspan=2>&nbsp;{$item[61]} {$item[62]}</td>
		</tr>
		<tr style = '{$tr_thin_style}'>
		<td style= '{$td_heading_3_style}' colspan=5>&nbsp;INCIDENT RADIO COMMUNICATIONS PLAN</td>
		<td style= '{$td_heading_3_style}' colspan=1>ICS 205-CG (Rev 07/04)&nbsp;</td>
		</tr>

		</table>\n";	// end of returned string
		}		// end function template 205


?>
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE><?php echo basename(__FILE__); ?></TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="<?php echo basename(__FILE__); ?>">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<script src = "./js/jquery-1.4.2.min.js"></script>
<script src="./js/jss.js" TYPE="application/x-javascript"></script>
<script src="./js/misc_function.js" TYPE="application/x-javascript"></script>

<SCRIPT>
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
</SCRIPT>
</HEAD>
<?php
$step = (array_key_exists( 'step', $_POST )) ?  $_POST['step']: 1;
switch ($step) {

	case 1 :
		$user_id = $_SESSION['user_id'];		//3/24/2015
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = {$user_id} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_by = "{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";


		$the_date_str = format_date ( strval (time() - (intval(get_variable('delta_mins')*60 ) ) ) );
		$the_date_arr = explode (" ", $the_date_str);
		if (count ( $the_date_arr)!=2 ) {
			$the_date_arr = explode (" ", now_ts() ); 			// 2038-01-19 03:14:07
			}
		$the_date = $the_date_arr[0];
		$the_time = $the_date_arr[1];


		function in_area ($name, $cols, $rows, $tabindex, $data = "") {			// <textarea ...
//			$data = $name;		// test
			return "<textarea id='f{$name}'  name='f{$name}' cols={$cols} rows={$rows} tabindex={$tabindex}>{$data}</textarea>";
			}

		function in_text( $name, $size, $tabindex, $data = "") {		//  <input type=text ...
//			$data = $name;		// test
			return "<input type=text id='f{$name}'  name='f{$name}' size={$size} maxlength={$size} value='{$data}' tabindex={$tabindex} />";
			}

		$item = array();
		$item[0] =  "";
		$item[1] =  in_text  (1, 20, 1); 				// $name, $size, $tabindex
		$item[2] =  in_text  (2, 10, 2, $the_date);
		$item[3] =  in_text  (3, 5, 3, $the_time);
		$item[4] =  in_text  (4, 10, 4, $the_date);
		$item[5] =  in_text  (5, 5, 5, $the_time);

		for ( $i = 0; $i<(6*9); $i+=6 ) {
				$item[$i+6] =  in_area  ($i+6, 12, 1, $i+6);  		// $name, $cols, $rows, $tabindex, $data = ""
				$item[$i+7] =  in_area  ($i+7, 10, 1, $i+7);
				$item[$i+8] =  in_area  ($i+8, 12, 1, $i+8);
				$item[$i+9] =  in_area  ($i+9, 12, 1, $i+9);
				$item[$i+10] =  in_area  ($i+10, 12, 1, $i+10);
				$item[$i+11] =  in_area  ($i+11, 22, 1, $i+11);
				}

		$item[60] =  in_text  (60, 48, 60, $the_by);  		// $name, $size, $tabindex
		$item[61] =  in_text  (61, 10, 61, $the_date);
		$item[62] =  in_text  (62, 5, 62, $the_time);

?>
		<BODY onload = "document.form_205.f1.focus();">		<!-- <?php echo __LINE__ ; ?> -->
		<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
			<SPAN ID='reset_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.form_205.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN><BR />
			<SPAN ID='can_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.can_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN><BR />
			<SPAN ID='mail_but' TITLE='OK - Mail this' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate(document.form_205);"><SPAN STYLE='float: left;'><?php print get_text("Send");?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN><BR />
		</div>

		<center><br />
		<h3> INCIDENT RADIO COMMUNICATIONS PLAN (ICS 205)</h3>
		<form name = "form_205" method = "post" action = "<?php echo basename(__FILE__); ?>" >
		<input type = 'hidden' name = 'f0' value = "" />

<?php
		echo template_205 ($item);		// fills form with default $item entries
?>
		<p style = 'margin-top:20px;'>
			<input type = 'hidden' name = 'step' value = 2 />
			<input type = 'hidden' name = 'frm_add_str' value = '<?php echo $_POST['frm_add_str'];?>'/>
			</form>
		</p>

		<script>
			function validate(our_form) {		// ics form name check
				if (our_form.f1.value.trim().length > 0) {our_form.submit();}		// incident name required
				else {
					alert("Incident Name is required");
					our_form.f1.focus();
					return false;
					}
				}		// end function validate()
		</script>

<?php

	break;

	case 2 :			// do mail

		function html_mail ($to, $subject, $html_message, $from_address, $from_display_name='') {
		//	$headers = 'From: ' . $from_display_name . ' <shoreas@gmail.com>' . "\n";
			$from = get_variable('email_from');
			$from = is_email($from)? $from : "no-reply@ticketscad.com";
			$headers = "From: {$from_display_name}<{$from}>\n";
			$headers .= 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$temp = get_variable('email_reply_to');
			if (is_email($temp)){
			    $headers .= "Reply-To: {$temp}\r\n";
			    }

			$temp = @mail($to, $subject, $html_message, $headers); // boolean
			}			// end function html_mail ()

		$stuff = array_values ($_POST);

		$html_message = template_205 ($stuff) ;

		$to_array = explode ("|", $_POST['frm_add_str']);
		$to = $sep = "";
		for ($i=0; $i < count($to_array); $i++) {
			$to .= "{$sep}{$to_array[$i]}";
			$sep = ",";
			}		// end for ()

		$subject ="ICS 205 Message - {$stuff[1]}";		// subject, per form data
		$temp = get_variable('email_from');
		$from_address = (is_email($temp))? $temp: "ticketscad.com";
		$from_display_name=get_variable('title_string');
		$temp = shorten(strip_tags(get_variable('title_string')), 30);
		$from_display_name = str_replace ( "'", "", $temp);
		$result = html_mail ($to, $subject, $html_message, $from_address, $from_display_name);	// does native mail

?>
<script>

function fade( elem, time ) {
	var startOpacity = elem.style.opacity || 1;
	elem.style.opacity = startOpacity;
	(function go() {
		elem.style.opacity -= startOpacity / ( time / 100 );
		// for IE
		elem.style.filter = 'alpha(opacity=' + elem.style.opacity * 100 + ')';
		if( elem.style.opacity > 0 )	{setTimeout( go, 100 );}
		else							{elem.style.display = 'none';}
		})();
	}
</script>
<!--
<body onload = 'fade( $('complete'),  1000 ) ; setTimeout(function(){ window.close()}, 400 );' >
-->

<body onload = "setTimeout(function(){ window.close()}, 4000 );" >
<center>
<div id = 'complete' style = 'margin-top:40px;'><H2 >ICS 205 Message sent - <?php echo $stuff[1] ; ?></h2></div>

<?php
//		echo template_205 ($stuff) ;
		break;

	case 3 :				// what-for tbd

	break;

default:
    echo  "err-err-err-err-err at: " . __LINE__;
	}		// end switch
?>
<form name = "can_form" method = 'post' action = 'ics213.php'>
</form>
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
