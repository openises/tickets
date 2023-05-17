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
	$table 			= " width:7in; border-collapse: collapse; border:1px solid black; background-color: white; ";
	$upper 			= " width:7in; border-collapse: collapse; border:none; ";

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
		<col style = 'width:5%;'>
		<col style = 'width:30.0%;'>
		<col style = 'width:25.0%;'>
		</colgroup>
		<tr style = '{$tr_fat}' >
		<td colspan = 5>

			<table style = '{$upper}'>
			<colgroup>
			<col style = 'width:auto;'>
			<col style = 'width:auto'>
			<col style = 'width:auto;'>
			<col style = 'width:auto'>
			</colgroup>
			<tr style = '{$tr_plain}'>
			<td colspan=1 style = '{$td_heading}'>&nbsp;1. Incident Name:</td>
			<td colspan=1 style = '{$td_heading}'>&nbsp;2. Operational Period:</td>
			<td colspan=1 style = '{$td_heading}'>&nbsp;Date From: {$item[1]}</td>
			<td colspan=1 style = '{$td_heading}'>&nbsp;Date To: $item[2]</td>
			</tr>
			<tr style = '{$tr_plain}'>
			<td colspan=2 style = '{$td_heading}'>{$item[3]}</td>
			<td colspan=1 style = '{$td_heading}'>&nbsp;Time From: {$item[4]}</td>
			<td colspan=1 style = '{$td_heading}'>&nbsp;Time To: {$item[5]}</td>
			</tr>
			</table>

		</td>
		</tr>\n

		<tr style = '{$tr_thin}'>
		<td colspan=2 style = '{$td_heading}'>&nbsp;3. Name:{$item[6]}</td>
		<td colspan=2 style = '{$td_heading}'>&nbsp;4. ICS Position:{$item[7]}</td>
		<td colspan=1 style = '{$td_heading}'>&nbsp;5. Home Agency (and Unit):{$item[8]}</td>
		<tr style = '{$tr_thin}'>
		<td colspan=5 style = '{$td_heading}'>&nbsp;6. Resources Assigned:</td>
		</tr>\n
		</tr>\n";


			$start = 9;
			for ($i=0; $i<(3*8); $i+=3)  {					// 3 cols, 8 rows (0-23)
				$out_str  .= "<tr style = '{$tr_thin}'>
					<td colspan=2 style = '{$td_plain}'>{$item[$start + $i]}</td>
					<td colspan=2 style = '{$td_plain}'>{$item[$start + $i + 1]}</td>
					<td colspan=1 style = '{$td_plain}'>{$item[$start + $i + 2]}</td>
					</tr>\n";
				}

	$out_str  .= "
		<tr style = '{$tr_thin}'>
		<td colspan=5 style = '{$td_heading}'>&nbsp;7. Activity Log:</td>
		</tr>\n

		<tr style = '{$tr_thin}'>
		<td colspan=1 style = '{$td_plain}'>Date/Time</td>
		<td colspan=4 style = '{$td_plain}'>&nbsp;Notable Activities</td>
		</tr>";

			$start = 33;
			for ($i=0; $i<(2*24); $i+=2)  {					// 2 cols, 24 rows (0-23)
				$out_str  .= "<tr style = '{$tr_thin}'>
					<td colspan=1 style = '{$td_plain}'>{$item[$start + $i]}</td>
					<td colspan=4 style = '{$td_plain}'>{$item[$start + $i + 1]}</td>
					</tr>\n";
				}

	$out_str  .= "
		<tr style = '{$tr_thin}'>
		<td colspan=2 style = '{$td_heading}'>&nbsp;8. Prepared by:{$item[81]}</td>
		<td colspan=2 style = '{$td_heading}'>&nbsp;Position/Title:{$item[82]}</td>
		<td colspan=1 style = '{$td_heading}'>&nbsp;Signature:{$item[83]}</td>
		</tr>
		<tr style = '{$tr_thin}'>
		<td colspan=2 style = '{$td_heading}'>&nbsp;ICS 214, Page 1</td>
		<td colspan=3 style = '{$td_heading}'>Date/Time: {$item[84]} {$item[85]}</td>
		</tr>\n
		</table>";
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
			return "<textarea id='f{$name}'  name='f{$name}' cols={$cols} rows={$rows} tabindex={$tabindex}>{$data}</textarea>";
			}

		function in_text( $name, $size, $tabindex, $data = "") {		//  <input type=text ...
//			$data = $name;		// test
			return "<input type=text id='f{$name}'  name='f{$name}' size={$size} maxlength={$size} value='{$data}' tabindex={$tabindex} />";
			}

		$item = array();
		$item[0] =  "";
		$item[1] =  in_text  (1, 10, 1, $the_date);  				// $name, $size, $tabindex
		$item[2] =  in_text  (2, 10, 2, $the_date);
		$item[3] =  in_text  (3, 40, 3);
		$item[4] =  in_text  (4, 5, 4, $the_time);
		$item[5] =  in_text  (5, 5, 5, $the_time);
		$item[6] =  in_text  (6, 20, 6);
		$item[7] =  in_text  (7, 20, 7);
		$item[8] =  in_text  (8, 20, 8);

		$start = 9;
		for ($i=0; $i<(3*8); $i+=3)  {					// 3 cols, 8 rows (0-23)
			$item[$start + $i] =    in_text  ($start + $i, 36, $start + $i);  			// name
			$item[$start + $i+1] =  in_text  ($start + $i+1, 20, $start + $i+1);  		// ICS position
			$item[$start + $i+2] =  in_text  ($start + $i+2, 24, $start + $i+2);  		// home agency
			}

		$start = 33;
			for ($i=0; $i<(2*24); $i+=2)  {					// 2 cols, 24 rows (0-47)
			$item[$start + $i] =    in_text  ($start + $i, 14, $start + $i);  			// date/time
			$item[$start + $i+1] =  in_text  ($start + $i+1, 68, $start + $i+1);  		// notable activities
			}

		$item[81] =  in_text  (81, 20, 81);
		$item[82] =  in_text  (82, 12, 82);
		$item[83] =  in_text  (83, 12, 83);
		$item[84] =  in_text  (84, 10, 84, $the_date);
		$item[85] =  in_text  (85, 5, 85, $the_time);

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
		$html_message = template_ics ($stuff) ;

		$to_array = explode ("|", $_POST['frm_add_str']);
		$to = $sep = "";
		for ($i=0; $i < count($to_array); $i++) {
			$to .= "{$sep}{$to_array[$i]}";
			$sep = ",";
			}		// end for ()

		$subject ="ICS 214 Message - {$stuff[1]}";		// subject, per form data
		$temp = get_variable('email_from');
		$from_address = (is_email($temp))? $temp: "ticketscad.com";
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
