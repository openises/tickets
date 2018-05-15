<?php
/*
2/26/2014 - initial release
3/8/2014 - revised for inline style vs. css
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
require_once('incs/functions.inc.php');		//7/28/10

// dump($_POST);

function template_ics ($item) {

	$body 			= " BACKGROUND-COLOR: #EFEFEF; MARGIN:0; FONT-WEIGHT: normal; FONT-SIZE: 12px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none ";
	$table 			= " width:9in; border-collapse: collapse; border:1px solid black; background-color: white; ";
	$upper 			= " width:7in; border-collapse: collapse; border:none; ";
	$inner			= " width:4in; border-collapse: collapse; border:none; ";

	$tr_fat 		= " background-color: white; height: 40px; vertical-align:text-top;";
	$tr_thin 		= " background-color: white; height: 19px; vertical-align:middle;";
	$tr_plain 		= " background-color: white; height: 19px; ";

	$td_plain		= " FONT-WEIGHT: 400; FONT-SIZE: 10px; border:1px solid black; text-align: left; ";
	$td_heading		= " FONT-WEIGHT: 900; FONT-SIZE: 10px; border:1px solid black; ";
	$td_heading_c	= " FONT-WEIGHT: 400; FONT-SIZE: 10px; border:1px solid black;text-align: center; ";
	$input			= " font-size: 12px; font-family: monospace; ";
	$textarea  		= " font-size: 12px; font-family: monospace; ";

	$out_str = "\n
		<table style = '{$table}'>
		<colgroup>
		<col style = 'width:20.0%;'>
		<col style = 'width:20.0%;'>
		<col style = 'width:20.0%;'>
		<col style = 'width:20.0%;'>
		<col style = 'width:20.0%;'>
		</colgroup>
		<tr style = '{$tr_plain}'>
		<td colspan=2 style = '{$td_heading}'>&nbsp;1. Incident Name:</td>
		<td colspan=1 style = '{$td_heading}'>&nbsp;2. Operational Period:</td>
		<td colspan=2 style = '{$td_heading}'>			
			<table style = '{$inner}'>
			<colgroup>
			<col style = 'width:auto;'>
			<col style = 'width:auto'>
			<col style = 'width:auto;'>
			<col style = 'width:auto'>
			</colgroup>
			<tr style = '{$tr_plain}'>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Date From: {$item[1]}</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Date To: {$item[2]}</td>
			</tr>
			<tr style = '{$tr_plain}'>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Time From: {$item[3]}</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Time To: {$item[4]}</td>
			</tr>
			</table>
		</td>
		</tr>
		<tr style = '{$tr_fat}'>
		<td colspan=5 style = '{$td_heading}'>&nbsp;3. Objective(s):</td>
		</tr>\n
		<tr style = '{$tr_fat}'>		
		<td colspan=5 style = '{$textarea} height: 200px;'>{$item[5]}</td>
		</tr>
		<tr style = '{$tr_fat}'>
		<td colspan=5 style = '{$td_heading}'>&nbsp;4. Operational Period Command Emphasis:</td>
		</tr>\n
		<tr style = '{$tr_fat}'>		
		<td colspan=5 style = '{$textarea} height: 150px;'>{$item[6]}</td>
		</tr>
		<tr style = '{$tr_fat}'>
		<td colspan=5 style = '{$td_plain}'>&nbsp;General Situational Awareness:</td>
		</tr>\n
		<tr style = '{$tr_fat}'>		
		<td colspan=5 style = '{$textarea} height: 130px;'>{$item[7]}</td>
		</tr>
		<tr style = '{$tr_fat}'>
		<td colspan=5 style = '{$td_heading}'>&nbsp;5. Site Safety Plan Required:&nbsp;{$item[8]} {$item[9]}<BR /><BR />Approved Site Safety Plan(s) Located at:&nbsp;{$item[10]}</td>
		</tr>
		<tr style = '{$tr_fat}'>
		<td colspan=5 style = '{$td_heading}'>&nbsp;6. Incident Action Plan (the items checked below are included ub this Incident Acion Plan):&nbsp;</td>
		</tr>
		<tr style = '{$tr_fat}'>		
		<td colspan=2 style = '{$td_plain}'>{$item[11]}&nbsp;&nbsp;&nbsp;{$item[16]} <BR /> {$item[12]}&nbsp;&nbsp;&nbsp;{$item[17]} <BR /> {$item[13]}&nbsp;&nbsp;&nbsp;{$item[18]} <BR /> {$item[14]}&nbsp;&nbsp;&nbsp;{$item[19]} <BR /> {$item[15]}</td>
		<td colspan=3 style = '{$td_plain}'>Other Attachments:<BR />{$item[20]} {$item[21]}<BR />{$item[22]} {$item[23]}<BR />{$item[24]} {$item[25]}<BR />{$item[26]} {$item[27]}</td>
		</tr>
		<tr style = '{$tr_thin}'>
		<td style = '{$td_heading}' colspan=5>&nbsp;7. Prepared by: (Communications Unit):{$item[60]} Position/Title:{$item[61]} Signature:{$item[62]} </td>
		</tr>
		<td style = '{$td_heading}' colspan=5>&nbsp;8. Approved by Incident Commander: &nbsp; Name: {$item[63]} Signature: {$item[64]} </td>		
		</tr>
		<tr style = '{$tr_thin}'>
		<td style= '{$td_heading}' colspan=1>ICS 202</td>
		<td style= '{$td_heading}' colspan=1>IAP Page {$item[65]}</td>
		<td style= '{$td_heading}' colspan=3>Date/Time: {$item[66]}</td>
		</tr>
		</tr>\n";

	$out_str  .= "
		</table><br /><br />";
	return $out_str;

	}		// end function template 214

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
<style type="text/css">
.textarea { font-size: 12px; font-family: monospace; width: 99%; height: 99%; }
</style>
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
		$user_id = $_SESSION['user_id'];		// 3/24/2015
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
			return "<textarea id='f{$name}' class='textarea' name='f{$name}' tabindex={$tabindex}>{$data}</textarea>";
			}

		function in_text( $name, $size, $tabindex, $data = "") {		//  <input type=text ...
//			$data = $name;		// test
			return "<input type=text id='f{$name}' name='f{$name}' size={$size} maxlength={$size} value='{$data}' tabindex={$tabindex} />";
			}
			
		function in_check( $name, $tabindex, $value, $ischecked) {		//  <input type=text ...
//			$data = $name;		// test
			return "<input type=checkbox id='f{$name}' name='f{$name}' value='{$value}' {$ischecked} tabindex={$tabindex} />{$value}";
			}

		$item = array();
		$item[0] =  "";
		$item[1] =  in_text  (1, 10, 1, $the_date);  				// $name, $size, $tabindex
		$item[2] =  in_text  (2, 10, 2, $the_date);
		$item[3] =  in_text  (3, 5, 4, $the_time);
		$item[4] =  in_text  (4, 5, 4, $the_time);
		$item[5] =  in_area  (5, 100, 20, 5);
		$item[6] =  in_area  (6, 100, 20, 6);
		$item[7] =  in_area  (7, 100, 20, 7);
		$item[8] =  in_check (8, 8, 'Yes', '');
		$item[9] =  in_check (9, 9, 'No', '');
		$item[10] =  in_text  (10, 90, 10);
		$item[11] =  in_check (11, 11, 'ICS 203', '');
		$item[12] =  in_check (12, 12, 'ICS 204', '');
		$item[13] =  in_check (13, 13, 'ICS 205', '');
		$item[14] =  in_check (14, 14, 'ICS 205A', '');
		$item[15] =  in_check (15, 15, 'ICS 206', '');
		$item[16] =  in_check (16, 16, 'ICS 207', '');
		$item[17] =  in_check (17, 17, 'ICS 208', '');
		$item[18] =  in_check (18, 18, 'Map/Chart', '');
		$item[19] =  in_check (19, 19, 'Weather Forecast/Tides/Current', '');
		$item[20] =  in_check (20, 20, ' ', '');
		$item[21] =  in_text  (21, 80, 21);
		$item[22] =  in_check (22, 22, ' ', '');
		$item[23] =  in_text  (23, 80, 23);
		$item[24] =  in_check (24, 24, ' ', '');
		$item[25] =  in_text  (25, 80, 25);
		$item[26] =  in_check (26, 26, ' ', '');
		$item[27] =  in_text  (27, 80, 27);
		$item[60] =  in_text  (60, 23, 60, $the_by);
		$item[61] =  in_text  (61, 23, 61);
		$item[62] =  in_text  (62, 23, 62);
		$item[63] =  in_text  (63, 30, 63);
		$item[64] =  in_text  (64, 30, 64);
		$item[65] =  in_text  (65, 10, 65);	
		$item[66] =  in_text  (66, 70, 66);	
?>
<BODY onload = "document.form_214.f1.focus();">		<!-- <?php echo __LINE__ ; ?> -->
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<SPAN ID='reset_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.form_214.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.can_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE='OK - Mail this' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate(document.form_214);"><SPAN STYLE='float: left;'><?php print get_text("Send");?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN><BR />
</div>
<center><br />
<form name = "form_214" method = "post" action = "<?php echo basename(__FILE__); ?>" >
<input type = 'hidden' name = 'f0' value = "" />
<h2>ACTIVITY LOG (ICS 214)</h2>
<?php
	echo template_ics ($item);		// fills form with default $item entries
?>
<input type = 'hidden' name = 'step' value = 2 />
<input type = 'hidden' name = 'frm_add_str' value = '<?php echo $_POST['frm_add_str'];?>'/>
</form>
<script>
	function validate(our_form) {		// ics form name check
		if (our_form.f3.value.trim().length > 0) {our_form.submit();}
		else {
			alert("Incident name is required");
			our_form.f3.focus();
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
			$from = is_email($from)? $from : "info@ticketscad.org";
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
		$html_message = template_ics ($stuff) ;

		$to_array = explode ("|", $_POST['frm_add_str']);
		$to = $sep = "";
		for ($i=0; $i < count($to_array); $i++) {
			$to .= "{$sep}{$to_array[$i]}";
			$sep = ",";
			}		// end for ()

		$subject ="ICS 214 Message - {$stuff[1]}";		// subject, per form data
		$temp = get_variable('email_from');
		$from_address = (is_email($temp))? $temp: "ticketscad.org";
		$from_display_name=get_variable('title_string');
		$temp = shorten(strip_tags(get_variable('title_string')), 30);
		$from_display_name = str_replace ( "'", "", $temp);
		$result = html_mail ($to, $subject, $html_message, $from_address, $from_display_name);	// does native mail

// <body onload = '$( "#complete" ).fadeOut( 3000, function() {document.can_form.submit()});'>	<!-- <?php echo __LINE__ ;?> -->


?>
<body onload = 'setTimeout(function(){ window.close()}, 4000 );' >
<center>
<div id = 'complete' style = 'margin-top:40px;'><H2 >ICS 214 Message sent - <?php echo $stuff[3] ; ?></h2></div>

<?php
//		echo template_ics ($stuff) ;
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
