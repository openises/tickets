<?php
/*
2/26/2014 - initial release
3/8/2014 - revised for inline style vs. css
<?php echo __LINE__; ?>
*/
define ( "FORM", "ICS 213RR" ) ;
define ( "TITLE", "Resource Request" ) ;

if ( !defined ( 'E_DEPRECATED' ) ) { define ( 'E_DEPRECATED',8192 ) ;}		// 11/8/09
error_reporting ( E_ALL ^ E_DEPRECATED ) ;
@session_start () ;
require_once ( '../incs/functions.inc.php' ) ;		//7/28/10

if ( empty ($_SESSION) ) {
?>
<body onload = 'setTimeout ( function () { this.window.close () }, 2000 ) ;' >
	<h1 style = 'text-align: center; margin-top:200px;'>Closing window!</h1>
	</BODY>
<?php
	}
else {			// NOTE!

session_write_close () ;
include ( './ics.css.php' ) ;
extract ( $_POST ) ;

$do_blur = ( can_edit () ) ? "" : "onfocus = 'this.blur () ;'" ;		// limit edit access
$payload_arr = array () ;											// payload as a global PHP associative array
$tabindex = 1;

function get_name ( $the_id ) {
	$query = "SELECT `name` FROM `$GLOBALS[mysql_prefix]ics` WHERE `id` = {$the_id} LIMIT 1";
	$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
	$row = mysql_fetch_assoc ( $result ) ;
	return FORM . " '" . $row ['name'] . "'";
	}				// end function get_name ()

function in_area ( $name, $cols, $rows, $ph = NULL, $margin=0 ) {			// <textarea ...
	global $tabindex, $payload_arr, $func, $do_blur, $textarea;

	$tabindex++;
	$key = "f_{$name}";
	$value = array_key_exists ( $key, $payload_arr ) ? $payload_arr [$key] : "" ;
	$placeholder = ( ( $_POST['func'] == "m2" ) || ( is_null ( $ph ) ) || ( ! empty ( $value ) ) ) ? "" : " placeholder = '{$ph}' " ;
	return ( $_POST['func'] == "m2" ) ?
		"<span style = '{$textarea}'>{$value}</span>" :
		"<textarea  style = '{$textarea}' id='f_{$name}' cols = {$cols} rows = {$rows} name='f_{$name}' tabindex={$tabindex} onchange = 'this.form.dirty.value = 1;' {$do_blur} style = 'margin-left: {$margin}px;' {$placeholder} >{$value}</textarea>";
	}		// end function

function in_text ( $name, $size, $ph = NULL, $margin=0 ) {		// <input type=text ...
	global $input, $tabindex, $payload_arr, $func, $do_blur;
	$tabindex++;
	$key = "f_{$name}";
	$value = array_key_exists ( $key, $payload_arr ) ? $payload_arr [$key] : "" ;
	$placeholder = ( ( $_POST['func'] == "m2" ) || ( is_null ( $ph ) ) || ( ! empty ( $value ) ) ) ? "" : " placeholder = '{$ph}' " ;
	$ml = strval ( round ( ( floatval ( $size ) ) * 2.0 ) ) ;	// maximum length
	return ( $_POST['func'] == "m2" ) ?
		"<span style = '$input'>{$value}</span>" :
		"<input type=text style = '{$input} margin-left: {$margin}px;' id='f_{$name}' name='f_{$name}' size={$size} maxlength={$ml} value='{$value}' tabindex={$tabindex} {$placeholder} onchange = 'this.form.dirty.value = 1;' {$do_blur}'/>";
	}		// end function

function in_check ( $name, $value, $ph = NULL, $margin=0 ) {		// <input type=checkbox ...
	global $tabindex, $payload_arr, $func, $do_blur;
	$tabindex++;
	$key = "f_{$name}";
	$ischecked = array_key_exists ( $key, $payload_arr ) ? "checked" : "" ;
	$placeholder = ( ( $_POST['func'] == "m2" ) || ( is_null ( $ph ) ) || ( ! empty ( $value ) ) ) ? "" : " placeholder = '{$ph}' " ;
	return ( $_POST['func'] == "m2" ) ?
		"<input type=checkbox id='f_{$name}' name='f_{$name}' value='{$value}' {$ischecked} tabindex={$tabindex} />{$value}\n":
		"<input type=checkbox id='f_{$name}' name='f_{$name}' value='{$value}' {$ischecked} tabindex={$tabindex} onchange = 'this.form.dirty.value = 1;' {$do_blur} style = 'margin-left: {$margin}px;'/>{$value}";
	}

function set_input_strings () {

	$out_arr = array () ;
	$out_arr['f_0'] = in_text ( 0, 30, " (required)" ) ; 	// name

	$out_arr['f_1d'] = in_text ( '1d', 12 ) ;	// date
	$out_arr['f_1t'] = in_text ( '1t', 6 ) ;	// time

	$out_arr['f_2'] = in_text ( 2, 32 ) ;		// res req no.

	for ( $i = 0; $i< 66 ; $i+=7 ) {
			$t = "f_" . strval ( $i+3 ) ;
			$out_arr[$t] = in_text ( strval ( $i + 3 ), 5 ) ;

			$t = "f_" . strval ( $i+4 ) ;
			$out_arr[$t] = in_text ( strval ( $i + 4 ), 5 ) ;

			$t = "f_" . strval ( $i+5 ) ;
			$out_arr[$t] = in_text ( strval ( $i + 5 ), 5 ) ;

			$t = "f_" . strval ( $i+6 ) ;
			$out_arr[$t] = in_text ( strval ( $i + 6 ), 34 ) ;

			$t = "f_" . strval ( $i+7 ) ;
			$out_arr[$t] = in_text ( strval ( $i + 7 ), 9 ) ;

			$t = "f_" . strval ( $i+8 ) ;
			$out_arr[$t] = in_text ( strval ( $i + 8 ), 9 ) ;

			$t = "f_" . strval ( $i+9 ) ;
			$out_arr[$t] = in_text ( strval ( $i + 9 ), 9 ) ;

			}

 	$out_arr['f_66'] = in_text ( 66, 46, ' Enter location' ) ;					// 5.
 	$out_arr['f_67'] = in_text ( 67, 36, ' Enter substitutes ...'  ) ;
 	$out_arr['f_68'] = in_text ( 68, 26 ) ;										// Requested by
 	$out_arr['f_69'] = in_text ( 69, 26) ;
 	$out_arr['f_70'] = in_text ( 70, 32) ;

 	$out_arr['f_71'] = in_text ( 71, 24, ' Order no. here' ) ;
 	$out_arr['f_72'] = in_text ( 72, 24 ) ;
 	$out_arr['f_73'] = in_text ( 73, 28,  ' Supplier POC here');

 	$out_arr['f_74'] = in_area ( 74, 76, 1, ' Enter notes here' ) ;		// note IN_AREA ()

 	$out_arr['f_75'] = in_text ( 75, 56 ) ;
 	$out_arr['f_76_d'] = in_text ( '76_d', 12 ) ;
 	$out_arr['f_76_t'] = in_text ( '76_t', 5 ) ;
 	$out_arr['f_77'] = in_text ( 77, 18 ) ;
 	$out_arr['f_78'] = in_text ( 78, 90 ) ;
 	$out_arr['f_79'] = in_text ( 79, 55 ) ;

 	$out_arr['f_80'] = in_text ( 80, 12 ) ;
 	$out_arr['f_81'] = in_text ( 81, 5 ) ;

	return $out_arr;
	}		// end function set input strings ()

function merge_template () {		// merge argument array with template -- e.g., <td> $my_inputs_arr['fn'] </td>
	include ( './ics.css.php' ) ;

	$my_inputs_arr = set_input_strings () ;

	$out_str = "\n
		<table border = 1 style = '{$table} width: 8in;'>\n
		<tr style = 'height: 0px;'>\n
		<td style = 'width:  4%;   background-color: transparent;'></td>\n
		<td style = 'width:  7.5%; background-color: transparent;'></td>\n
		<td style = 'width:  7.5%; background-color: transparent;'></td>\n
		<td style = 'width:  7.5%; background-color: transparent;'></td>\n
		<td style = 'width:  7.5%; background-color: transparent;'></td>\n
		<td style = 'width:  30%;  background-color: transparent;'></td>\n
		<td style = 'width:  12%;  background-color: transparent;'></td>\n
		<td style = 'width:  12%;  background-color: transparent;'></td>\n
		<td style = 'width:  12%;  background-color: transparent;'></td>\n
		</tr>\n
		<tr style = '{$tr_thin}' class = 'bordered' >\n
		<td colspan=5 style = '{$td_heading}'>&nbsp;1. Incident Name:<br> {$my_inputs_arr['f_0']}</td>\n
		<td colspan=1 style = '{$td_heading}'>&nbsp;2. Date/Time<br> {$my_inputs_arr['f_1d']} {$my_inputs_arr['f_1t']}</td>\n
		<td colspan=3 style = '{$td_heading}'>&nbsp;3. Resource Request No:<br> {$my_inputs_arr['f_2']}</td>\n
		</tr>\n";

	$out_str .= "\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 rowspan = 15 style = '{$vertical}; '>R<br />
			e<br />
			q<br />
			u<br />
			e<br />
			s<br />
			t<br />
			e<br />
			r</td>\n
		<td colspan=8 style = '{$td_heading}'>4. Order ( Use additional forms when requesting different resource sources of supply.):</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_heading}' rowspan = 2 valign = 'top'>Qty</td>\n
		<td colspan=1 style = '{$td_heading}' rowspan = 2 valign = 'top'>Kind</td>\n
		<td colspan=1 style = '{$td_heading}' rowspan = 2 valign = 'top'>Type</td>\n
		<td colspan=2 style = '{$td_heading}' rowspan = 2 valign = 'top'>Detailed Item Description:  </td>\n
		<td colspan=2 style = '{$td_heading}'>Arrival Date and Time</td>\n
		<td colspan=1 style = '{$td_heading}' rowspan = 2 valign = 'top'>Cost</td>\n
		</tr>\n

		<tr><td colspan=1 style = '{$td_plain}'>Requested</td><td colspan=1 style = '{$td_plain}'>Estimated</td></tr>\n

		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_3']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_4']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_5']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_6']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_7']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_8']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_9']}</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_10']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_11']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_12']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_13']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_14']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_15']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_16']}</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_17']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_18']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_19']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_20']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_21']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_22']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_23']}</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_24']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_25']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_26']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_27']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_28']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_29']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_30']}</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_31']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_32']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_33']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_34']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_35']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_36']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_37']}</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_38']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_39']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_40']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_41']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_42']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_43']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_44']}</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_45']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_46']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_47']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_48']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_49']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_50']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_51']}</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_52']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_53']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_54']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_55']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_56']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_57']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_58']}</td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_59']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_60']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_61']}</td>\n
		<td colspan=2 style = '{$td_plain}'>{$my_inputs_arr['f_62']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_63']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_64']}</td>\n
		<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr['f_65']}</td>\n
		</tr>\n

		<tr style = '{$tr_thin}' >\n
		<td colspan=8 style = '{$td_heading}' class = 'bordered' >5. Requested Delivery/Reporting Location: {$my_inputs_arr['f_66']}</td>\n
		</tr>
		<tr style = '{$tr_thin}' >\n
		<td colspan=8 style = '{$td_heading} bordered_left; ' class = 'bordered' >6. Suitable Substitutes/and/or Suggested Sources: {$my_inputs_arr['f_67']}</td>\n
		</tr>
		<tr style = '{$tr_thin}' >\n
		<td colspan=4 style = '{$td_heading}'>7. Requested by:<br> {$my_inputs_arr['f_68']}</td>\n
		<td colspan=1 style = '{$td_heading}'>8. Priority:<br> {$my_inputs_arr['f_69']}</td>\n
		<td colspan=3 style = '{$td_heading}'>9. Section Chief Approval:<br> {$my_inputs_arr['f_70']}</td>\n
		</tr>";

	$out_str .= "\n
		<tr style = '{$tr_thin};'>\n
		<td colspan=1 rowspan = 4 style = '{$vertical} border-top: solid 2px black;'>L<br />
			o<br />
			g<br />
			</td>\n
		<td colspan=5 style = '{$td_heading} border-top: solid 2px black;' class = 'bordered_top'>10. Logistics Order Number: {$my_inputs_arr['f_71']}</td>\n
		<td colspan=3 rowspan = 2 style = '{$td_heading};border-top: solid 2px black;' valign = 'top' >11. Supplier Phone/Fax/Email:<br /> {$my_inputs_arr['f_72']} </td>\n
		</tr>\n

		<tr style = '{$tr_thin}' >\n
		<td colspan=5 style = '{$td_heading}border-top: solid 2px black;'>12. Name of Supplier/POC: {$my_inputs_arr['f_73']}</td>\n
		</tr>\n

		<tr style = '{$tr_thin}' >\n
		<td valign = 'top' colspan=8 style = '{$td_heading}border-top: solid 2px black; '><span style = 'vertical-align: top;'>13. Notes:</span> {$my_inputs_arr['f_74']}</td>\n
		</tr>\n

		<tr style = '{$tr_thin}'>\n
		<td colspan=5 style = '{$td_heading} border-top: solid 2px black;'>14. Approval Signature of Auth Logistics Rep:<br> {$my_inputs_arr['f_75']}</td>\n
		<td colspan=3 rowspan = 2 valign = top style = '{$td_heading} border-top: solid 2px black;'>15. Date/Time:<br> {$my_inputs_arr['f_76_d']} {$my_inputs_arr['f_76_t']}</td>\n
		</tr>\n";

	$out_str .= "\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 rowspan = 1 style = '{$td_heading} border-top: solid 2px black;'></td>\n
		<td colspan=8 style = '{$td_heading} border-top: solid 2px black;'>16. Order placed by ( check box ) : {$my_inputs_arr['f_77']} </td>\n
		</tr>\n";

	$out_str .= "\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=1 rowspan = 2 style = '{$vertical} border-top: solid 2px black;'>F<br />
			i<br />
			n<br />
			</td>\n
		<td colspan=8 style = '{$td_heading} border-top: solid 2px black;'>17. Reply/Comments from Finance:<br> {$my_inputs_arr['f_78']} </td>\n
		</tr>\n
		<tr style = '{$tr_thin}' >\n
		<td colspan=5 style = '{$td_heading} border-top: solid 2px black;' >18. Finance Section Signature:<br> {$my_inputs_arr['f_79']}</td>\n
		<td colspan=3 style = '{$td_heading} border-top: solid 2px black;'>19. Date/Time:<br> {$my_inputs_arr['f_80']}{$my_inputs_arr['f_81']}</td>\n
		</tr>\n";

	$out_str .= "<tr><td colspan = 9 style = 'border-top: solid 2px black;'><B>ICS-213 RR Page 1</B>
		</td></tr>
		</table>\n<br>\n";	// end of returned string

	return $out_str;
	}		// end function merge template ()

?>
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE><?php echo FORM . "/" . $func; ?></TITLE>
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time () ;?>" TYPE="text/css">
<!--
-->
<style type="text/css">
/*	input[type=text] 			{ border: none; }
	input[type=text]			{ background-color: yellow; }
	textarea	 				{ background-color: yellow; }

*/
	input[type=text]:focus 		{ background-color: yellow; }
	textarea:focus 				{ background-color: yellow; }
</style>

<script>

	String.prototype.trim = function () {
		return this.replace ( /^\s* ( \S* ( \s+\S+ ) * ) \s*$/, "$1" ) ;
		};

	function do_focus () {
		var success = false;
		for (i = 0; i < document.main_form.elements.length; i++) {
			if ( ( ( document.main_form.elements[i].type = "text" ) || ( document.main_form.elements[i].type = "textarea" ) ) && ( document.main_form.elements[i].value.length == 0 ) ) {
				success = true;
				break;
				}
			}		// end for ()
		if ( success ) {document.main_form.elements[i].focus ();}
		}		// end function

	function validate ( our_form ) {		// ics form name check
		if ( our_form.f_0.value.trim () .length > 0 ) {our_form.submit () ;}
		else {
			alert ( "Incident name is required." ) ;
			our_form.f_0.focus () ;
			return false;
			}
		}		// end function validate ()

	function save ( our_form ) {
		if ( our_form.f_0.value.trim () == "" ) {
			alert ( "Incident name is required." ) ;
			our_form.f_0.focus () ;
			return false;
			}
		else {
			our_form.submit () ;					// do it
			return true;
			}
		}		//end function save ()

	 function chk_del ( our_form ) {
	 	if ( confirm ( "Press OK to confirm Delete ( Cannot be undone! ) " ) ) {
	 		our_form.func.value = "d";
	 		our_form.submit () ;
	 		}
	 	else {
	 		return false;
	 		}		// end if/else
	 	}		// end function chk_del ()

</script>

</HEAD>
<?php
$now_ts = now_ts();
$func = ( array_key_exists ( 'func', $_POST ) ) ? $_POST['func']: "c";

switch ( $func ) {


	case "c" :

?>
<BODY style = '<?php echo $body;?>'  onload = "init () ;">		<!-- <?php echo __LINE__ ; ?> -->
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<SPAN ID='reset_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ;init();document.main_form.f_0.focus () ;"><SPAN style='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' 							class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN style='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG style='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
 	<SPAN ID='save_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="save ( document.main_form ) ;">			 <SPAN style='float: left;'><?php print get_text ( "Save to DB" ) ;?></SPAN>	<IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE='OK - Mail this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='m';validate ( document.main_form ) ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
</div>
<center><br />
<form name = "main_form" method = "post" action = "<?php echo basename ( __FILE__ ) ; ?>" >
<h2><?php echo TITLE . " - " . FORM; ?></h2>

<?php

	echo merge_template () ;		// fills form with default $inputs_arr entries
	$user_id = $_SESSION['user_id'];		// 3/24/2015
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = {$user_id} LIMIT 1";
	$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
	$row = stripslashes_deep ( mysql_fetch_assoc ( $result ) ) ;
	$the_by = "{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";

	$the_date_str = format_date ( strval ( time () - ( intval ( get_variable ( 'delta_mins' ) *60 ) ) ) ) ;
	$the_date_arr = explode ( " ", $the_date_str ) ;
	if ( count ( $the_date_arr ) !=2 ) {
		$the_date_arr = explode ( " ", now_ts () ) ; 			// 2038-01-19 03:14:07
		}
	$the_date = $the_date_arr[0];
	$the_time = $the_date_arr[1];
?>

<script>
	function init () {
		document.main_form.f_1d.value = '<?php echo $the_date;?>';
		document.main_form.f_1t.value = '<?php echo $the_time;?>';
		document.main_form.f_68.value = '<?php echo $the_by;?>';
		document.main_form.f_76_d.value = '<?php echo $the_date;?>';
		document.main_form.f_76_t.value = '<?php echo $the_time;?>';
		document.main_form.f_80.value = '<?php echo $the_date;?>';
		document.main_form.f_81.value = '<?php echo $the_time;?>';
		do_focus () ;
		}		// end function init ()
	</script>
<input type = 'hidden' name = 'func' value = "c2" />		<!-- do INSERT sql -->
<input type = 'hidden' name = 'dirty' value = 0 />
<input type = 'hidden' name = 'ics_id' value = 0 />
</form>		<!-- main_form	-->

<?php
	break;		// end case "c"

	case "c2" :				// insert new data

		$name = mysql_real_escape_string ( $_POST['f_0'] ) ;				// incident name
		$payload = base64_encode ( json_encode ( $_POST ) ) ;				// whew!!
		$now_ts = now_ts();
		$script = basename ( __FILE__ );
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]ics` ( `name`, `type`, `script`, `payload`, count, `_by`, `_from`, `_as-of`, `_sent` ) VALUES
														 ( '{$name}', '" . FORM ."', '{$script}', '{$payload}', 0, {$_SESSION['user_id']}, '{$_SERVER['REMOTE_ADDR']}', '{$now_ts}', NULL ) ; ";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
		$temp = mysql_insert_id () ;				// append id # in order to enforce uniqueness
		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			`name` = CONCAT_WS ( '/', `name`, '{$temp}' )
			WHERE `id` = '{$temp}' LIMIT 1";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;

?>
<body onload = 'setTimeout ( function () { document.dummy.submit () }, 4000 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top:250px;'>
<h2><?php echo get_name ( $temp ) ; ?> database insert complete! </h2>
<form name = 'dummy' method = post action = '../ics.php' ></form>
</div>
<?php
			break;		// end case "c2"

	case "u" :
		$query = "SELECT `payload`, `archived` FROM`$GLOBALS[mysql_prefix]ics` WHERE `id` = {$_POST ['id']} LIMIT 1";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
		if ( mysql_num_rows ( $result ) <> 1 ) { dump ( query ) ;}

		$row = mysql_fetch_assoc ( $result ) ;
		$temp = base64_decode ( $row['payload'] ) ;
		$payload_arr = json_decode ( $temp, true ) ;		// 	get payload as a PHP associative array

//		dump ( $row['archived'] ) ;

?>

<BODY style = '<?php echo $body;?>'  onload = "do_focus () ;">		<!-- <?php echo __LINE__ ; ?> -->
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<SPAN ID='reset_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ; document.main_form.f_0.focus () ;"><SPAN style='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' 							class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN style='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG style='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
 	<SPAN ID='save_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="save ( document.main_form ) ;">				<SPAN style='float: left;'><?php print get_text ( "Save to DB" ) ;?></SPAN>	<IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE='OK - Mail this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='m';validate ( document.main_form ) ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
<?php
	if ( is_null ( $row['archived'] ) ) {
?>
<SPAN ID='arch_but' TITLE='Archive this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='a';document.main_form.submit () ;"><SPAN style='float: left;'><?php print get_text ( 'Archive this' ) ;?></SPAN><IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
<?php
		} else {
?>
	<SPAN ID='arch_but' TITLE='De-archive this' 	class='plain text' style='float: none; width: 140px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='e';document.main_form.submit () ;">
		<SPAN STYLE='float: left;'><?php print get_text ( 'De-archive this' ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='dele_but' TITLE='Delete this' 		class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="chk_del ( document.main_form ) ";>
		<SPAN STYLE='float: left;'><?php print get_text ( 'Delete this' ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/delete.png' BORDER=0></SPAN><BR />
<?php
		}
?>
</div>
<center><br />
<form name = "main_form" method = "post" action = "<?php echo basename ( __FILE__ ) ; ?>" >
<h2><?php echo TITLE . " - " . FORM; ?></h2>

<?php

	echo merge_template () ;		// merge form with default $inputs_arr entries - case "u"

	if ( mysql_num_rows ( $result ) <> 1 ) { dump ( query ) ;}
	$payload = $row['payload'];
	$temp = base64_decode ( $payload ) ;
	$in_array = json_decode ( $temp, true ) ;		// 	 	 10/12/17
?>

<input type = 'hidden' name = 'func' value = "u2" />		<!-- do UPDATE sql -->
<input type = 'hidden' name = 'dirty' value = 0 />
<input type = 'hidden' name = 'ics_id' value = <?php echo $_POST['id']; ?> />
</form>		<!-- main_form	-->
<?php

	break;		// end case "u"

	case "u2" :				// db update
		$name = mysql_real_escape_string ( $_POST['f_0'] ) . "/" . strval ( $_POST['ics_id'] ) ;
		$payload = base64_encode ( json_encode ( $_POST ) ) ;
		$now_ts = now_ts();

		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			 `name` = 		'{$name}',
			 `payload` = 	'{$payload}',
			 `_by` = 		{$_SESSION['user_id']},
			 `_from` = 		'{$_SERVER['REMOTE_ADDR']}',
			 `_as-of` = 	'{$now_ts}'
			WHERE `id` = {$_POST['ics_id']} LIMIT 1 ";

		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
?>
<body onload = 'setTimeout ( function () { document.dummy.submit () }, 4000 ) ;' >		<!-- <?php echo __LINE__ ;?> -->

<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo get_name ( $_POST['ics_id'] ) ; ?> database update complete! </h2>
<form name = 'dummy' method = post action = '../ics.php' ></form>
</div>
<?php
		break;		// end case "u2"

	case "m" :			// first check data/db status

	if ( intval ( $_POST['dirty'] == 1 ) ) {				// either update or insert
		if ( intval ( $_POST['ics_id'] ) > 0 ) {				// do UPDATE
//			dump ( __LINE__ ) ;

			$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
				 `name` = 		'{$name}',
				 `payload` = 	'{$payload}',
				 `_by` = 		{$_SESSION['user_id']},
				 `_from` = 		'{$_SERVER['REMOTE_ADDR']}',
				 `_as-of` = 	'{$now_ts}',
				 `_sent` = 		NULL
				WHERE `id` = {$_POST['ics_id'] } LIMIT 1 ";

			$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
			}			// end if ()

		else {								// do INSERT

			$name = mysql_real_escape_string ( $_POST['f_0'] ) ;
			$payload = base64_encode ( json_encode ( $_POST ) ) ;				// whew!!
			$script = basename ( __FILE__ ) ;
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]ics` ( `name`, `type`, `script`, `payload`, count, `_by`, `_from`, `_as-of`, `_sent` ) VALUES
														 ( '{$name}', '" . FORM ."', '{$script}', '{$payload}', 0, {$_SESSION['user_id']}, '{$_SERVER['REMOTE_ADDR']}', '{$now_ts}', NULL ) ; ";
			$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
			$temp = mysql_insert_id () ;				// append id # in order to enforce uniqueness
			$_POST['ics_id'] = $temp;				// update default 0 value
			$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
				`name` = CONCAT_WS ( '/', `name`, '{$temp}' )
				WHERE `id` = '{$temp}' LIMIT 1";
			$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
			}		// end else
		 }		// end if ( dirty )
?>
<script>

	function do_mail_str () {
		sep = "";
		for ( i=0;i<document.main_form.elements.length; i++ ) {
			if ( ( document.main_form.elements[i].type =='checkbox' ) && ( document.main_form.elements[i].checked ) ) {		// frm_add_str - pipe-delimited plain addresses
				document.main_form.frm_add_str.value += sep + document.main_form.elements[i].value;
				sep = "|";
				}
			}
		if ( document.main_form.frm_add_str.value.trim () =="" ) {
			alert ( "Addressees required" ) ;
			return false;
			}
		document.main_form.func.value = "m2";		// mail, step 2
		document.main_form.submit () ;
		return true;
		}


	function do_clear () {
		for ( i=0;i<document.main_form.elements.length; i++ ) {
			if ( document.main_form.elements[i].type =='checkbox' ) {
				document.main_form.elements[i].checked = false;
				}
			}		// end for ()
		$ ( 'clr_spn' ) .style.display = "none";
		$ ( 'chk_spn' ) .style.display = "inline-block";
		}		// end function do_clear

	function do_check () {
		for ( i=0;i<document.main_form.elements.length; i++ ) {
			if ( document.main_form.elements[i].type =='checkbox' ) {
				document.main_form.elements[i].checked = true;
				}
			}		// end for ()
		$ ( 'clr_spn' ) .style.display = "inline-block";
		$ ( 'chk_spn' ) .style.display = "none";
		}		// end function do_clear

	</script>
	</HEAD>
	<BODY>
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<span id='reset_but'	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<span id='can_but'		class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
	<span id='mail_but'		class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="do_mail_str () ;document.main_form.submit () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
</div>

	<center><br /><br />
<?php
	$i=0;		// 3/6/2014
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts`
		ORDER BY `organization` ASC,`name` ASC" ;
	$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;

	if ( mysql_affected_rows () >0 ) {

	function do_row ( $i, $addr, $name, $org ) {
		global $evenodd;
		$return_str = "<TR CLASS= '{$evenodd[ ( $i ) %2]}'>";
		$js_i = $i+1;
		$return_str .= "\t\t<TD>&nbsp;<INPUT TYPE='checkbox' CHECKED NAME='cb{$js_i}' VALUE='{$addr}'>";
		$return_str .= "&nbsp;{$addr} / {$name} / {$org}</TD></TR>\n";
		return $return_str;
		}				// end function do_row ()

?>
	<P><h2>Select Mail Recipients</h2>
	<FORM NAME='main_form' METHOD='post' ACTION='<?php echo basename ( __FILE__ ) ; ?>'>
	<TABLE ALIGN='center'>
		<TR CLASS = 'even'>
			<TD ALIGN = 'center'><BR />
				<SPAN id='clr_spn' CLASS='plain text' style='width: 120px; display: inline-block; float: none;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="do_clear () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Uncheck All" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/unselect_all_small.png' BORDER=0></SPAN>
				<SPAN id='chk_spn' CLASS='plain text' style='width: 120px; display: none; float: none;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="do_check () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Check All" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/select_all_small.png' BORDER=0></SPAN>
			</TD>
		</TR>
<?php
		while ( $row = stripslashes_deep ( mysql_fetch_assoc ( $result ) , MYSQL_ASSOC ) ) {
																					// count valid addresses
			if ( is_email ( $row['email'] ) ) 	{ echo do_row ( $i, $row['email'], $row['name'], $row['organization'] ) ;$i++;}
			if ( is_email ( $row['mobile'] ) ) 	{ echo do_row ( $i, $row['mobile'], $row['name'], $row['organization'] ) ;$i++;}
			if ( is_email ( $row['other'] ) ) 	{ echo do_row ( $i, $row['other'], $row['name'], $row['organization'] ) ;$i++;}
			}		// end while ()
?>
	</TABLE>
<input type = 'hidden' name = 'func' VALUE='m2' />
<input type = 'hidden' name = 'ics_id' value = <?php echo $_POST['ics_id']; ?> />
<input type = 'hidden' name = 'frm_add_str' VALUE='' />						<!-- for pipe-delimited addr string -->
</form>
<?php
			}		// end if ( mysql_affected_rows () >0 )
		if ( ( $i==0 ) || ( mysql_affected_rows () ==0 ) ) {
?>
			<H3>No Contact e-mail addresses!</H3><BR /><BR />
			<INPUT TYPE='button' VALUE='Cancel' onClick = 'window.close () ;'><BR /><BR />
<?php
			}
// ------------------------------
		break;		// end case m

	case "m2" :			// mail step 2 - send mail to selected addresses
		function html_mail ( $to, $subject, $html_message, $from_address, $from_display_name='' ) {
		//	$headers = 'From: ' . $from_display_name . ' <shoreas@gmail.com>' . "\n";
			$from = get_variable ( 'email_from' ) ;
			$from = is_email ( $from ) ? $from : "no-rely@ticketscad.com";
			$headers = "From: {$from_display_name}<{$from}>\n";
			$headers .= 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$temp = get_variable ( 'email_reply_to' ) ;
			if ( is_email ( $temp ) ) {
			 $headers .= "Reply-To: {$temp}\r\n";
			 }

			$temp = @mail ( $to, $subject, $html_message, $headers ) ; // boolean
			}			// end function html mail ()

		$query = "SELECT `name`, `type`, `payload` FROM `$GLOBALS[mysql_prefix]ics` WHERE `id` = {$_POST ['ics_id']} LIMIT 1";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
		if ( mysql_num_rows ( $result ) <> 1 ) { dump ( $query ) ;}

		$row = mysql_fetch_assoc ( $result ) ;
		$temp = base64_decode ( $row['payload'] ) ;
		$payload_arr = json_decode ( $temp, true ) ;			// get payload as a PHP associative array
		$html_message = merge_template () ;					// case "m2"
//		echo $html_message;									// test - test - test - test - test -

		$to_array = explode ( "|", $_POST['frm_add_str'] ) ;	// de-stringify addresses
		$to = $sep = "";
		for ( $i=0; $i < count ( $to_array ) ; $i++ ) {
			$to .= "{$sep}{$to_array[$i]}";
			$sep = ",";
			}		// end for ()

		$subject = FORM . " - " . $row ['name'];		// subject, per form data
		$temp = get_variable ( 'email_from' ) ;
		$from_address = ( is_email ( $temp ) ) ? $temp: "ticketscad.com";
		$from_display_name = get_variable ( 'title_string' ) ;
		$temp = shorten ( strip_tags ( get_variable ( 'title_string' ) ) , 30 ) ;
		$from_display_name = str_replace ( "'", "", $temp ) ;
		$result = html_mail ( $to, $subject, $html_message, $from_address, $from_display_name ) ;

//				 ( $code, 							$ticket_id=0, 		$responder_id=0, 	$info="", 	$facility_id=0, $rec_facility_id=0, $mileage=0 )
		do_log	 ( $GLOBALS['LOG_ICS_MESSAGE_SEND'], 	$_POST ['ics_id'], 	0, 					$subject, 	0, 				0, 					0 ) ;			// incident name as subject

		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
					`count` = ( `count` + 1 ) ,
					`_sent` = '{$now_ts}'
					WHERE `id` = '{$_POST ['ics_id']}' LIMIT 1";

		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
?>
<body onload = 'setTimeout ( function () { document.can_form.submit () }, 4000 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo get_name ( $_POST ['ics_id'] ) ; ?> Mail sent!</h2>
</div>
<?php
	break;						// end case m2

case "a" :						// archive
		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			 `_by` = 		{$_SESSION['user_id']},
			 `archived` = 	'{$now_ts}'
			WHERE `id` = {$_POST['ics_id']} LIMIT 1 ";

		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;

?>
<body onload = 'setTimeout ( function () { document.can_form.submit () }, 4000 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo get_name ( $_POST ['ics_id'] ) ; ?> to archive complete! </h2>
</div>
<?php
		break;		// end case "a"

case "e" :						// de-archive
		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			 `_by` = 		{$_SESSION['user_id']},
			 `archived` = 	NULL
			WHERE `id` = {$_POST['ics_id']} LIMIT 1 ";

		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;

?>
<body onload = 'setTimeout ( function () { document.can_form.submit () }, 4000 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo get_name ( $_POST ['ics_id'] ) ; ?> <u>De</u>-archiving complete! </h2>
</div>
<?php
		break;		// end case "e"

case "d" :						// delete
		$msg = get_name ( $_POST ['ics_id'] ) ;
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]ics` WHERE `id` = {$_POST['ics_id']} LIMIT 1 ";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
?>
<body onload = 'setTimeout ( function () { document.can_form.submit () }, 4000 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo $msg; ?> deleted! </h2>
</div>
<?php
		break;		// end case "d"

default:
 echo "err-err-err-err-err at: " . __LINE__;

	}		// end switch
	}		// end if/else
?>
<form name = "can_form" method = 'post' action = '../ics.php'>
</form>

</BODY>
<script>
if ( typeof window.innerWidth != 'undefined' ) {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if ( typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0 ) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName ( 'body' ) [0].clientWidth,
	viewportheight = document.getElementsByTagName ( 'body' ) [0].clientHeight
	}
set_fontsizes ( viewportwidth, "popup" ) ;
</script>
</HTML>
