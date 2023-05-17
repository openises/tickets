<?php
/*
2/26/2014 - initial release
3/8/2014 - revised for inline style, vs css class
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
require_once('incs/functions.inc.php');		//7/28/10

// dump($_POST);

function template_205a ($item) {
	$table_style = 		' width:auto; border-collapse: collapse; border:2px solid black; background-color: white; ';
	$footer_style = 	' width:auto; border-collapse: collapse; border:1px solid black; background-color: white; ';
	$tr_thin_style = 	' height: 21px; vertical-align:middle;';
	$td_heading_style = ' FONT-WEIGHT: 900; FONT-SIZE: 10px; border-collapse: collapse; border:1px solid black; ';
	$td_plain_style = 	' FONT-WEIGHT: 400; FONT-SIZE: 10px; text-align: left; border-collapse: collapse; border:1px solid black; ';
	$td_plain_c_style = ' FONT-WEIGHT: 400; FONT-SIZE: 10px; text-align: center; border-collapse: collapse; border:1px solid black; ';

	$out_str = "\n
		<table style = '{$table_style}'>\n
		<colgroup>\n
		<col style = 'width: 1.0in;'>\n
		<col style = 'width: .75in;'>\n
		<col style = 'width: 1.0in;'>\n
		<col style = 'width: .5in;'>\n
		<col style = 'width: 2.0in;'>\n
		<col style = 'width: 1.75in'>\n
		</colgroup>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=2 style = '{$td_heading_style}'>&nbsp;1. Incident Name:</td>\n
		<td colspan=2 style = '{$td_heading_style}'>&nbsp;2. Operational Period:</td>\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp; Date From {$item[1]}</td>\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp; Date To {$item[2]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=4 style = '{$td_heading_style}'> {$item[3]}</td>\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp; Time From {$item[4]}</td>\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp; Time To {$item[5]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=6 style = '{$td_heading_style}'>&nbsp;3. Basic Local Communications Information</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_c_style}'>Incident Assigned Position</td>\n
		<td colspan=2 style = '{$td_plain_c_style}'>Name (Alphabetized)</td>\n
		<td colspan=3 style = '{$td_plain_c_style}'>Method(s) of Contact<br />(phone, pager, cell, etc.)</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_style}'>{$item[6]}</td>\n
		<td colspan=2 style = '{$td_plain_style}'>{$item[7]}</td>\n
		<td colspan=3 style = '{$td_plain_style}'>{$item[8]}</td>
		</tr>\n";
	$start = 9;
	for ($i=0; $i<(3*29); $i+=3)  {					// 3 cols, 30 rows (0-29)
		$out_str  .= "<tr style = '{$tr_thin_style}'>
			<td colspan=1 style = '{$td_plain_style}'>{$item[$start + $i]}</td>
			<td colspan=2 style = '{$td_plain_style}'>{$item[$start + $i + 1]}</td>
			<td colspan=3 style = '{$td_plain_style}'>{$item[$start + $i + 2]}</td>
			</tr>\n";
		}

	$out_str  .= "<tr><td colspan = 6>\n
		<table style = '{$footer_style}'>\n
		<colgroup>\n
		<col style = 'width: 1.5in;'>\n
		<col style = 'width: 2.0in;'>\n
		<col style = 'width: 2.5in;'>\n
		<col style = 'width: 2.0in;'>\n
		</colgroup>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=2 style = '{$td_heading_style}'>&nbsp;4. Prepared by:&nbsp;{$item[96]}</td>\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp;Position/Title: {$item[97]}</td>\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp;Signature: {$item[98]}</td>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp;ICS 205A</td>\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp; IAP Page {$item[99]}</td>\n
		<td colspan=2 style = '{$td_heading_style}'>&nbsp;Date/Time: {$item[100]} {$item[101]}</td>\n
		</tr>\n
		</table>\n
		\n
		</td></tr>
		</table>\n";	// end of returned string
	return $out_str;
	}		// end function template 205a


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
$step = (array_key_exists( 'step', $_POST )) ?  $_POST['step']: "one";

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
			return "<textarea id='f{$name}'  name='f{$name}' cols={$cols} rows={$rows} tabindex={$tabindex}>{$data}</textarea>";
			}

		function in_text( $name, $size, $tabindex, $data = "") {		//  <input type=text ...
//			$data = $name;		// test
			return "<input type=text id='f{$name}'  name='f{$name}' size={$size} maxlength={$size} value='{$data}' tabindex={$tabindex} />";
			}

		$item = array();
		$item[0] =  "";
		$item[1] =  in_text  (1, 10, 1, $the_date);
		$item[2] =  in_text  (2, 10, 2, $the_date);
		$item[3] =  in_text  (3, 36, 3); 				// $name, $size, $tabindex
		$item[4] =  in_text  (4, 5, 4, $the_time);
		$item[5] =  in_text  (5, 5, 5, $the_time);

		for ($i=6; $i<96; $i+=3)  {
			$item[$i] =  in_text  ($i, 12, $i);
			$item[$i+1] =  in_text  ($i+1, 22, $i+1);
			$item[$i+2] =  in_text  ($i+2, 56, $i+2);
			}

		$item[96] =  in_text  (96, 24, 96, $the_by);  		// $name, $size, $tabindex
		$item[97] =  in_text  (97, 10, 97);
		$item[98] =  in_text  (98, 10, 98);
		$item[99] =  in_text  (99, 2, 99, 1);
		$item[100] =  in_text  (100, 10, 100, $the_date);
		$item[101] =  in_text  (101, 5, 101, $the_time);


?>
<BODY onload = "document.form_205a.f3.focus();">
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<SPAN ID='reset_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.form_205a.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.can_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE='OK - Mail this' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate(document.form_205a);"><SPAN STYLE='float: left;'><?php print get_text("Send");?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN><BR />
</div>
<center><br />
<h3>COMMUNICATIONS LIST (ICS 205A)</h3>
<form name = "form_205a" method = "post" action = "<?php echo basename(__FILE__); ?>" >
<input type = 'hidden' name = 'f0' value = "" />
<?php
	echo template_205a ($item);		// fills form with default $item entries
?>
<input type = 'hidden' name = 'step' value = 2 />
<input type = 'hidden' name = 'frm_add_str' value = '<?php echo $_POST['frm_add_str'];?>'/>
</form>
<script>
	function validate(our_form) {		// ics form name check
		if (our_form.f3.value.trim().length > 0) {our_form.submit();}
		else {
			alert("Incident Name is required");
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
		$html_message = template_205a ($stuff) ;

//		snap (__LINE__, $html_message );
		$to_array = explode ("|", $_POST['frm_add_str']);
		$to = $sep = "";
		for ($i=0; $i < count($to_array); $i++) {
			$to .= "{$sep}{$to_array[$i]}";
			$sep = ",";
			}		// end for ()

		$subject ="ICS 205A Message - {$stuff[3]} - {$stuff[1]}";		// subject, per form data
		$temp = get_variable('email_from');
		$from_address = (is_email($temp))? $temp: "ticketscad.com";
		$from_display_name=get_variable('title_string');
		$temp = shorten(strip_tags(get_variable('title_string')), 30);
		$from_display_name = str_replace ( "'", "", $temp);
		$result = html_mail ($to, $subject, $html_message, $from_address, $from_display_name);	// does native mail
?>
<script>
// fade ()
function myfade( elem, time ) {
	alert(243);
	var startOpacity = elem.style.opacity || 1;
	elem.style.opacity = startOpacity;
	(function go() {
		alert(247);
		elem.style.opacity -= startOpacity / ( time / 100 );
		elem.style.filter = 'alpha(opacity=' + elem.style.opacity * 100 + ')';		// for IE
		if( elem.style.opacity > 0 ) setTimeout( go, 100 );
		else elem.style.display = 'none';
		})();
	}
</script>
<body onload = 'setTimeout(function(){ window.close()}, 4000 );' >
<center>
<div id = 'complete' style = 'margin-top:40px;'><H2 >ICS 205A Message sent - <?php echo $stuff[3] ; ?></h2></div>

<?php
//		echo template_205a ($stuff) ;
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
