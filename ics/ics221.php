<?php
/*

//  see ics205a.php for merge_template loop control
//	see ics205.php for set_input_strings loop control

Template
2/26/2014 - initial release
3/8/2014 - revised for inline style vs. css
<?php echo __LINE__; ?>
*/
define ( "FORM", "ICS 221" ) ;
define ( "TITLE", "DEMOBILIZATION CHECK-OUT" ) ;
if ( !defined ( 'E_DEPRECATED' ) ) { define ( 'E_DEPRECATED',8192 ) ;}		// 11/8/09
error_reporting ( E_ALL ^ E_DEPRECATED ) ;
@session_start () ;
if ( ! ( array_key_exists ( 'user_id ', $_SESSION ) ) ) { $POST ['func'] = 'x'; }
session_write_close () ;
require_once ( '../incs/functions.inc.php' ) ;
include ( './ics.css.php' ) ;
$now_ts = now_ts();
extract ( $_POST ) ;
/*
dump ( $_POST ) ;
dump ( $_POST ) ;
dump ( $_POST ) ;
dump ( $_POST ) ;
*/

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

function in_text ( $name, $size, $ph = NULL, $margin=6) {		// <input type=text ...
	global $input, $tabindex, $payload_arr, $func, $do_blur;
	$tabindex++;
	$key = "f_{$name}";
//	$value = array_key_exists ( $key, $payload_arr ) ? $payload_arr [$key] : "{$name}" ;
	$value = array_key_exists ( $key, $payload_arr ) ? $payload_arr [$key] : "" ;
	$placeholder = ( ( $_POST['func'] == "m2" ) || ( is_null ( $ph ) ) || ( ! empty ( $value ) ) ) ? "" : " placeholder = '{$ph}' " ;
	$ml = strval ( round ( ( floatval ( $size ) ) * 2.0 ) ) ;	// maximum length
	return ( $_POST['func'] == "m2" ) ?
		"<span style = '$input'>{$value}</span>" :
		"<input type=text style = '{$input} margin-left: {$margin}px;' id='f_{$name}' name='f_{$name}' size={$size} maxlength={$ml} value='{$value}' tabindex={$tabindex} {$placeholder} onchange = 'this.form.dirty.value = 1;' {$do_blur}'/>";
	}		// end function

function in_check ( $name, $value, $pair=NULL, $margin=6 ) {		// <input type=checkbox ...
	global $tabindex, $payload_arr, $func, $do_blur;
	$tabindex++;
	$key = "f_{$name}";
	$ischecked = array_key_exists ( $key, $payload_arr ) ? "checked" : "" ;
	$paired_cb = ( is_null ( $pair ) ) ? "" :  "onclick = 'this.form.{$pair}.checked = 0;'";
	return ( $_POST['func'] == "m2" ) ?
		"<input type=checkbox id='f_{$name}' name='f_{$name}' value='{$value}' {$ischecked} tabindex={$tabindex} />{$value}\n":
		"<input type=checkbox id='f_{$name}' name='f_{$name}' value='{$value}' {$ischecked} tabindex={$tabindex} {$paired_cb} onchange = 'this.form.dirty.value = 1;' {$do_blur} style = 'margin-left: {$margin}px;'/>{$value}";
	}

function set_input_strings () {
	$out_arr = array () ;
//					  in_check ( $name, $value, $pair=NULL, $margin=0 )
//					  in_check ( 8, 'Yes' , 'f_9')

	$out_arr['f_0'] = in_text ( 0,  26 , ' required') ;		// name
	$out_arr['f_1'] = in_text ( 1,  24 ) ;		// number
	$out_arr['f_2'] = in_text ( 2,  12, ' date' ) ;		// planned release date
	$out_arr['f_3'] = in_text ( 3,  5, ' time' ) ;		// planned release time
	$out_arr['f_4'] = in_text ( 4,  30 ) ;		// 4. Resource or Personnel Released:
	$out_arr['f_5'] = in_text ( 5,  28 ) ;		// 5. Order Request Number:
										// Logistics
	$out_arr['f_6'] = in_check ( 6, 'Supply Unit' ) ;		// Supply Unit checkbox
	$out_arr['f_7'] = in_text ( 7, 20 ) ;			// Remarks
	$out_arr['f_8'] = in_text ( 8, 20 ) ;			// Name
	$out_arr['f_9'] = in_text ( 9, 16 ) ;			// Signature

	$out_arr['f_10'] = in_check ( 10, 'Communications Unit ' ) ;		// Communications Unit checkbox
	$out_arr['f_11'] = in_text ( 11, 20 ) ;
	$out_arr['f_12'] = in_text ( 12, 20 ) ;
	$out_arr['f_13'] = in_text ( 13, 16 ) ;

	$out_arr['f_14'] = in_check ( 14, 'Facilities Unit' ) ;		// Facilities Unit checkbox
	$out_arr['f_15'] = in_text ( 15, 20 ) ;
	$out_arr['f_16'] = in_text ( 16, 20 ) ;
	$out_arr['f_17'] = in_text ( 17, 16 ) ;

	$out_arr['f_18'] = in_check ( 18, 'Ground Support Unit' ) ;		// Ground Support Unit checkbox
	$out_arr['f_19'] = in_text ( 19, 20 ) ;
	$out_arr['f_20'] = in_text ( 20, 20 ) ;
	$out_arr['f_21'] = in_text ( 21, 16 ) ;

	$out_arr['f_22'] = in_check ( 22, 'Security Manager' ) ;		// Security Manager checkbox
	$out_arr['f_23'] = in_text ( 23, 20 ) ;
	$out_arr['f_24'] = in_text ( 24, 20 ) ;
	$out_arr['f_25'] = in_text ( 25, 16 ) ;

	$out_arr['f_26'] = in_check ( 26, '' ) ;		// checkbox tbd
	$out_arr['f_27'] = in_text ( 27, 20 ) ;
	$out_arr['f_28'] = in_text ( 28,20 ) ;
	$out_arr['f_29'] = in_text ( 29,20 ) ;
	$out_arr['f_30'] = in_text ( 30,16 ) ;
											// Finance
	$out_arr['f_31'] = in_check ( 31, 'Time Unit' ) ;	// Time Unit checkbox
	$out_arr['f_32'] = in_text ( 32, 20 ) ;
	$out_arr['f_33'] = in_text ( 33, 20 ) ;
	$out_arr['f_34'] = in_text ( 34, 16 ) ;

	$out_arr['f_35'] = in_check ( 35, '' ) ;		// tbd 1 checkbox
	$out_arr['f_36'] = in_text ( 36, 20 ) ;

	$out_arr['f_37'] = in_text ( 37, 20 ) ;
	$out_arr['f_38'] = in_text ( 38, 20 ) ;
	$out_arr['f_39'] = in_text ( 39, 16 ) ;

	$out_arr['f_40'] = in_check ( 40, '' ) ;		// tbd 2 checkbox
	$out_arr['f_41'] = in_text ( 41, 20 ) ;

	$out_arr['f_42'] = in_text ( 42, 20 ) ;
	$out_arr['f_43'] = in_text ( 43, 20 ) ;
	$out_arr['f_44'] = in_text ( 44, 16 ) ;
											// Other S/S
	$out_arr['f_45'] = in_check ( 45, '' ) ;		// tbd 1 checkbox
	$out_arr['f_46'] = in_text ( 46, 20 ) ;
	$out_arr['f_47'] = in_text ( 47, 20 ) ;
	$out_arr['f_48'] = in_text ( 48, 20 ) ;
	$out_arr['f_49'] = in_text ( 49, 16 ) ;

	$out_arr['f_50'] = in_check ( 51, '' ) ;		// tbd 2 checkbox
	$out_arr['f_51'] = in_text ( 51, 20 ) ;
	$out_arr['f_52'] = in_text ( 52, 20 ) ;
	$out_arr['f_53'] = in_text ( 53, 20 ) ;
	$out_arr['f_54'] = in_text ( 54, 16 ) ;
											// PLANNING SECTION
	$out_arr['f_55'] = in_check ( 55, '' ) ;		// tbd checkbox
	$out_arr['f_56'] = in_text ( 56, 20 ) ;

	$out_arr['f_57'] = in_text ( 57, 20 ) ;
	$out_arr['f_58'] = in_text ( 58, 20 ) ;
	$out_arr['f_59'] = in_text ( 59, 16 ) ;

	$out_arr['f_60'] = in_check ( 60, 'Documentation Leader' ) ;		// Documentation Leader checkbox
	$out_arr['f_61'] = in_text ( 61, 20 ) ;
	$out_arr['f_62'] = in_text ( 62, 20 ) ;
	$out_arr['f_63'] = in_text ( 63, 16 ) ;

	$out_arr['f_64'] = in_check ( 64, 'Demobilization Leader' ) ;		// Demobilization Leader checkbox
	$out_arr['f_65'] = in_text ( 65, 20 ) ;
	$out_arr['f_66'] = in_text ( 66, 20 ) ;
	$out_arr['f_67'] = in_text ( 67, 16 ) ;

	$out_arr['f_68'] = in_area ( 68, 90, 1 ) ;		// 7. Remarks:
											// 8. Travel Information:

	$out_arr['f_69'] = in_check ( 69, 'Yes', 'f_70' ) ;		// Room Overnight: - Yes
	$out_arr['f_70'] = in_check ( 70, 'No',  'f_69' ) ;		// - No

	$out_arr['f_71'] = in_text ( 71, 12, ' date' ) ;		// Estimated departure Date:
	$out_arr['f_72'] = in_text ( 72, 6, ' time' ) ;			// Estimated departure  Time:

	$out_arr['f_73'] = in_text ( 73, 12, ' date'  ) ;		// Actual Release date
	$out_arr['f_74'] = in_text ( 74, 6, ' time' ) ;			// Actual Release time
	$out_arr['f_75'] = in_text ( 75, 32) ;					// Destination

	$out_arr['f_76'] = in_text ( 76, 12, ' date' ) ;		// Estimated Time of Arrival date:
	$out_arr['f_77'] = in_text ( 77, 6, ' time'  ) ;		// Estimated Time of Arrival time

	$out_arr['f_78'] = in_text ( 78, 30 ) ;					// Travel Method:
	$out_arr['f_79'] = in_text ( 79, 28 ) ;					// Contact Information While Traveling:

	$out_arr['f_80'] = in_check ( 80, 'Yes', 'f_81' ) ;		// Manifest: - Yes
	$out_arr['f_81'] = in_check ( 81, 'No',  'f_80' ) ;		// Manifest: - No
	$out_arr['f_82'] = in_text ( 82, 24 ) ;					// Number:

	$out_arr['f_83'] = in_text ( 83, 20 ) ;					// Area/Agency/Region Notified:

	$out_arr['f_84'] = in_check ( 84, 'Yes', 'f_85' ) ;		// 9. Reassignment Information: - Yes
	$out_arr['f_85'] = in_check ( 85, 'No', 'f_84' ) ;		//    Reassignment Information: - No
	$out_arr['f_86'] = in_text ( 86, 24 ) ;					// unused
	$out_arr['f_87'] = in_text ( 87, 24 ) ;					// unused
	$out_arr['f_88'] = in_text ( 88, 32 ) ;					// 9. Incident Name
	$out_arr['f_89'] = in_text ( 89, 30 ) ;					// Incident Number
	$out_arr['f_90'] = in_text ( 90, 36 ) ;					// Location
	$out_arr['f_91'] = in_text ( 91, 16 ) ;					// Order Request Number
	$out_arr['f_92'] = in_text ( 92, 22 ) ;					// 10. Prep'd by
	$out_arr['f_93'] = in_text ( 93, 16 ) ;					//
	$out_arr['f_94'] = in_text ( 94, 10 ) ;					//
	$out_arr['f_95'] = in_text ( 95, 12,  ' date' ) ;		// date
	$out_arr['f_96'] = in_text ( 96, 6, ' time' ) ;			// time

	return $out_arr;

	}		// end function set input strings ()

function merge_template () {		// merge argument array with template -- e.g., <td> $my_inputs_arr['fn'] </td>

	include ( './ics.css.php' ) ;

	$my_inputs_arr = set_input_strings () ;

	$out_str = "\n
		<table id = 'top' style = '{$table} width: 8in;'>
		<tr style = 'height:1px;'>
			<td style = 'width: 30%; background-color: pink;'> </td>
			<td style = 'width: 20%; background-color: yellow;'> </td>
			<td style = 'width: 17%; background-color: pink;'> </td>
			<td style = 'width: 33%; background-color: yellow;'> </td>
			</tr>\n

		<tr>
			<td colspan=2 style = '{$td_heading}'>&nbsp;1. Incident Name: {$my_inputs_arr['f_0']}</td>
			<td colspan=2 style = '{$td_heading}'>&nbsp;2. Incident Number: {$my_inputs_arr['f_1']}</td>
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_heading}'>&nbsp;3. Planned Release:		<br> {$my_inputs_arr['f_2']}&nbsp;&nbsp;{$my_inputs_arr['f_3']}</td>
			<td colspan=2 style = '{$td_heading}'>&nbsp;4. Resource/Personnel Released: 	<br>&nbsp;&nbsp;&nbsp;&nbsp; {$my_inputs_arr['f_4']}</td>
			<td colspan=1 style = '{$td_heading}'>&nbsp;5. Order Request Number: 			<br>{$my_inputs_arr['f_5']}</td>
			</tr>\n
			</table>
		<table id = '2'  style = '{$table}width: 8in;'>
		<tr style = 'height:1px;'>
			<td style = 'width: 30%; background-color: pink;'> </td>
			<td style = 'width: 25%; background-color: yellow;'> </td>
			<td style = 'width: 25%; background-color: pink;'> </td>
			<td style = 'width: 20%; background-color: yellow;'> </td>
			</tr>\n
		<tr>
			<td colspan=4 style = '{$td_heading}'>&nbsp;6. Resource or Personnel:
			<br>You and your resources are ...<br>
			LOGISTICS SECTION
			</td>
			</tr>\n

<tr>
	<td style = '{$td_plain} margin-left: 40px;'>Unit/Manager </td>
	<td style = '{$td_plain}'>Remarks</td>
	<td style = '{$td_plain}'>Name</td>
	<td style = '{$td_plain}'>Signature</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_6']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_7']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_8']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_9']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_10']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_11']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_12']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_13']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_14']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_15']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_16']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_17']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_18']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_19']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_20']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_21']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_22']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_23']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_24']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_25']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_26']}{$my_inputs_arr['f_27']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_28']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_29']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_30']}</td>
	</tr>
<tr><td colspan = 4 style = '{$td_heading}'><b>FINANCE/ADMINISTRATION SECTION</b></td></tr>
<tr>
	<td style = '{$td_plain} margin-left: 40px;'>Unit/Leader </td>
	<td style = '{$td_plain}'>Remarks</td>
	<td style = '{$td_plain}'>Name</td>
	<td style = '{$td_plain}'>Signature</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_31']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_32']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_33']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_34']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_35']}{$my_inputs_arr['f_36']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_37']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_38']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_39']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_40']}{$my_inputs_arr['f_41']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_42']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_43']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_44']}</td>
	</tr>
<tr><td colspan = 4 style = '{$td_heading}'><b>OTHER SECTION/STAFF</b></td></tr>
<tr>
	<td style = '{$td_plain} margin-left: 40px;'>Unit/Other </td>
	<td style = '{$td_plain}'>Remarks</td>
	<td style = '{$td_plain}'>Name</td>
	<td style = '{$td_plain}'>Signature</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_45']}{$my_inputs_arr['f_46']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_47']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_48']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_49']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_50']}{$my_inputs_arr['f_51']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_52']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_53']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_54']}</td>
	</tr>
<tr><td colspan = 4 style = '{$td_heading}'><b>PLANNING SECTION</b></td></tr>
<tr>
	<td style = '{$td_plain} margin-left: 40px;'>Unit/Leader </td>
	<td style = '{$td_plain}'>Remarks</td>
	<td style = '{$td_plain}'>Name</td>
	<td style = '{$td_plain}'>Signature</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_55']}{$my_inputs_arr['f_56']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_57']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_58']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_59']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_60']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_61']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_62']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_63']}</td>
	</tr>
<tr>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_64']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_65']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_66']}</td>
	<td style = '{$td_plain}'>{$my_inputs_arr['f_67']}</td>
	</tr>
	</table>
		<table id = 'bottom'  style = '{$table} width: 8in;'>
		<tr style = 'height:1px;'>
			<td style = 'width: 50%; background-color: pink;'> </td>
			<td style = 'width: 50%; background-color: yellow;'> </td>
			</tr>\n
		<tr>
			<td colspan=2 style = '{$td_heading}'>&nbsp;7. Remarks:<br>&nbsp;&nbsp;&nbsp;&nbsp;{$my_inputs_arr['f_68']}</td>
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_heading}'>&nbsp;8. Travel Information:</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Room Overnight: {$my_inputs_arr['f_69']}&nbsp;&nbsp;&nbsp;&nbsp;{$my_inputs_arr['f_70']}</td>
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Estimated Departure: {$my_inputs_arr['f_71']} {$my_inputs_arr['f_72']}
			<td colspan=1 style = '{$td_plain}'>&nbsp;Actual Release: {$my_inputs_arr['f_73']} &nbsp;{$my_inputs_arr['f_74']}</td>
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Destination: {$my_inputs_arr['f_75']}</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Estimated Arrival: {$my_inputs_arr['f_76']} {$my_inputs_arr['f_77']}
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Travel Method: {$my_inputs_arr['f_78']}</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Contact Information: {$my_inputs_arr['f_79']}</td>
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Manifest: {$my_inputs_arr['f_80']}&nbsp;&nbsp;&nbsp;&nbsp;{$my_inputs_arr['f_81']}<br> &nbsp;&nbsp;&nbsp;&nbsp;Number: &nbsp;{$my_inputs_arr['f_82']}</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Area/Agency/Region Notified: {$my_inputs_arr['f_83']} </td>
			</tr>\n
		<tr>
			<td colspan=2 style = '{$td_heading}'>&nbsp;9. Reassignment Information:{$my_inputs_arr['f_84']}{$my_inputs_arr['f_85']}</td>
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Incident Name:  {$my_inputs_arr['f_88']}</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Incident Number: {$my_inputs_arr['f_89']}</td>
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Location: {$my_inputs_arr['f_90']}</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Order Request Number: {$my_inputs_arr['f_91']}</td>
			</tr>\n
		<tr>
			<td colspan=2 style = '{$td_plain}'>&nbsp;<b>10.Prepared by:</b> &nbsp;&nbsp;Name:{$my_inputs_arr['f_92']} &nbsp;Position:{$my_inputs_arr['f_93']} &nbsp; &nbsp; &nbsp;Sig;{$my_inputs_arr['f_94']}</td>
			</tr>\n
		<tr>
			<td colspan=1 style = '{$td_heading}'>&nbsp;ICS 221</td>
			<td colspan=1 style = '{$td_plain}'>&nbsp;Date/Time: {$my_inputs_arr['f_95']}{$my_inputs_arr['f_96']}</td>
			</tr>\n
			</table>\n<br>\n";

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
/*
	input[type=text] 			{ border: none; }
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
			alert ( "Incident name is required" ) ;
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
	 	if ( confirm ( "Press  to confirm Delete ( Cannot be undone! ) " ) ) {
	 		our_form.func.value = "d";
	 		our_form.submit () ;
	 		}
	 	else {
	 		return false;
	 		}		// end if/else
	 	}		// end function chk del ()


</script>

</HEAD>
<?php
$now_ts = now_ts();
$func = ( array_key_exists ( 'func', $_POST ) ) ? $_POST['func']: "c";

switch ( $func ) {

	case "c" :

?>
<BODY style = "{$body}"  onload = "init () ;">		<!-- <?php echo __LINE__ ; ?> -->
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<SPAN ID='reset_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ;init () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' 							class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
 	<SPAN ID='save_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="save ( document.main_form ) ;">			 <SPAN STYLE='float: left;'><?php print get_text ( "Save to DB" ) ;?></SPAN>	<IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE=' - Mail this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='m';validate ( document.main_form ) ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
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
		document.main_form.f_95.value = '<?php echo $the_date;?>';
		document.main_form.f_96.value = '<?php echo $the_time;?>';
		document.main_form.f_92.value = '<?php echo $the_by;?>';
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

		$now_ts = now_ts();
		$name = mysql_real_escape_string ( $_POST['f_0'] ) ;
		$payload = base64_encode ( json_encode ( $_POST ) ) ;				// whew!!
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

<BODY style = "{$body}"  onload = "do_focus () ;">		<!-- <?php echo __LINE__ ; ?> -->
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<SPAN ID='reset_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ;document.main_form.f_0.focus () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' 							class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
 	<SPAN ID='save_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="save ( document.main_form ) ;">				<SPAN STYLE='float: left;'><?php print get_text ( "Save to DB" ) ;?></SPAN>	<IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE=' - Mail this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='m';validate ( document.main_form ) ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
<?php
	if ( is_null ( $row['archived'] ) ) {
?>
<SPAN ID='arch_but' TITLE='Archive this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='a';document.main_form.submit () ;">
<SPAN STYLE='float: left;'><?php print get_text ( 'Archive this' ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
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
	$in_array = json_decode ( $temp, true ) ;		// 	f1 	 10/12/17
?>

<input type = 'hidden' name = 'func' value = "u2" />		<!-- do UPDATE sql -->
<input type = 'hidden' name = 'dirty' value = 0 />
<input type = 'hidden' name = 'ics_id' value = <?php echo $_POST['id']; ?> />
</form>		<!-- main_form	-->
<?php

	break;		// end case "u"

	case "u2" :				// update

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
			$from = is_email ( $from ) ? $from : "no-reply@ticketscad.com";
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
