<?php
/*
2/26/2014 - initial release
3/8/2014 - revised for inline style vs. css
<?php echo __LINE__; ?>
*/
define ( "FORM", "ICS 206" ) ;
define ( "TITLE", "Medical Plan" ) ;

if ( !defined ( 'E_DEPRECATED' ) ) { define ( 'E_DEPRECATED',8192 ) ;}		// 11/8/09
error_reporting ( E_ALL ^ E_DEPRECATED ) ;
@session_start () ;
if ( ! ( array_key_exists ( 'user_id ', $_SESSION ) ) ) { $POST ['func'] = 'x'; }
session_write_close () ;
require_once ( '../incs/functions.inc.php' ) ;		//7/28/10
if ( empty ($_SESSION) ) {
?>
<body onload = 'setTimeout ( function () { this.window.close () }, 2000 ) ;' >
	<h1 style = 'text-align: center; margin-top:200px;'>Closing window!</h1>
	</BODY>
<?php
	}
else {			// NOTE!

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

function in_check ( $name, $value, $pair=NULL, $margin=8 ) {		// <input type=checkbox ...
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
	$out_arr['f_0'] = 	in_text ( 0, 8 ) ;
	$out_arr['f_1'] = 	in_text ( 1, 8 ) ;
	$out_arr['f_2'] = 	in_text ( 2, 24, " (required)" ) ;
	$out_arr['f_3'] = 	in_text ( 3, 8 ) ;
	$out_arr['f_4'] = 	in_text ( 4, 8 ) ;

	$out_arr['f_5'] = 	in_text ( 5, 15 ) ;
	$out_arr['f_6'] = 	in_text ( 6, 26 ) ;
	$out_arr['f_7'] = 	in_text ( 7, 17 ) ;
	$out_arr['f_8'] = 	in_check ( 8, 'Yes' , 'f_9') ;
	$out_arr['f_9'] = 	in_check ( 9, 'No' , 'f_8') ;

	$out_arr['f_10'] = 	in_text ( 24, 15 ) ;
	$out_arr['f_11'] = 	in_text ( 11, 26 ) ;
	$out_arr['f_12'] = 	in_text ( 12, 17 ) ;
	$out_arr['f_13'] = 	in_check ( 13, 'Yes' , 'f_14') ;
	$out_arr['f_14'] = 	in_check ( 14, 'No' , 'f_13') ;

	$out_arr['f_15'] = 	in_text ( 15, 15  ) ;
	$out_arr['f_16'] = 	in_text ( 16, 26  ) ;
	$out_arr['f_17'] = 	in_text ( 17, 17  ) ;
	$out_arr['f_18'] = 	in_check ( 18, 'Yes' , 'f_19') ;
	$out_arr['f_19'] = 	in_check ( 19, 'No' , 'f_18') ;

	$out_arr['f_20'] = 	in_text ( 18, 15 ) ;
	$out_arr['f_21'] = 	in_text ( 21, 26 ) ;
	$out_arr['f_22'] = 	in_text ( 20, 17 ) ;
	$out_arr['f_23'] = 	in_check ( 23, 'Yes' , 'f_24') ;
	$out_arr['f_24'] = 	in_check ( 24, 'No' , 'f_23') ;

	$out_arr['f_25'] = 	in_text ( 25, 15 ) ;
	$out_arr['f_26'] = 	in_text ( 26, 26 ) ;
	$out_arr['f_27'] = 	in_text ( 27, 17 ) ;
	$out_arr['f_28'] = 	in_check ( 28, 'Yes' , 'f_29') ;
	$out_arr['f_29'] = 	in_check ( 29, 'No' , 'f_28') ;

	$out_arr['f_30'] = 	in_text ( 30, 15 ) ;
	$out_arr['f_31'] = 	in_text ( 31, 26 ) ;
	$out_arr['f_32'] = 	in_text ( 32, 17 ) ;
	$out_arr['f_33'] = 	in_check ( 33, 'Yes' , 'f_34') ;
	$out_arr['f_34'] = 	in_check ( 34, 'No' , 'f_33') ;

	$out_arr['f_35'] = 	in_text ( 35, 15 ) ;
	$out_arr['f_36'] = 	in_text ( 36, 26 ) ;
	$out_arr['f_37'] = 	in_text ( 37, 17 ) ;
	$out_arr['f_38'] = 	in_check ( 38, 'ALS' , 'f_39') ;
	$out_arr['f_39'] = 	in_check ( 39, 'BLS' , 'f_38') ;

	$out_arr['f_40'] = 	in_text ( 40, 15 ) ;
	$out_arr['f_41'] = 	in_text ( 41, 26 ) ;
	$out_arr['f_42'] = 	in_text ( 42, 17 ) ;
	$out_arr['f_43'] = 	in_check ( 43, 'ALS' , 'f_44') ;
	$out_arr['f_44'] = 	in_check ( 44, 'BLS' , 'f_43') ;

	$out_arr['f_45'] = 	in_text ( 45, 15 ) ;
	$out_arr['f_46'] = 	in_text ( 46, 26 ) ;
	$out_arr['f_47'] = 	in_text ( 47, 17 ) ;
	$out_arr['f_48'] = 	in_check ( 48, 'ALS' , 'f_49') ;
	$out_arr['f_49'] = 	in_check ( 49, 'BLS' , 'f_48') ;

	$out_arr['f_50'] = 	in_text ( 50, 15 ) ;
	$out_arr['f_51'] = 	in_text ( 51, 26 ) ;
	$out_arr['f_52'] = 	in_text ( 52, 17 ) ;
	$out_arr['f_53'] = 	in_check ( 53, 'ALS' , 'f_54') ;
	$out_arr['f_54'] = 	in_check ( 54, 'BLS' , 'f_53') ;

	$out_arr['f_55'] = 	in_text ( 55, 7 ) ;
	$out_arr['f_56'] = 	in_text ( 56, 15 ) ;
	$out_arr['f_57'] = 	in_text ( 57, 7 ) ;
	$out_arr['f_58'] = 	in_text ( 58, 7 ) ;
	$out_arr['f_59'] = 	in_text ( 59, 6 ) ;
	$out_arr['f_60'] = 	in_check ( 60, 'Yes', NULL, 16 ) ;
	$out_arr['f_61'] = 	in_text ( 61, 3 ) ;
	$out_arr['f_62'] = 	in_check ( 62, 'Yes' , 'f_63') ;
	$out_arr['f_63'] = 	in_check ( 63, 'No' , 'f_62') ;
	$out_arr['f_64'] = 	in_check ( 64, 'Yes' , 'f_65') ;
	$out_arr['f_65'] = 	in_check ( 65, 'No' , 'f_64') ;

	$out_arr['f_66'] = 	in_text ( 66, 7 ) ;
	$out_arr['f_67'] = 	in_text ( 67, 15 ) ;
	$out_arr['f_68'] = 	in_text ( 68, 7 ) ;
	$out_arr['f_69'] = 	in_text ( 69, 7 ) ;
	$out_arr['f_70'] = 	in_text ( 70, 6 ) ;
	$out_arr['f_71'] = 	in_check ( 71, 'Yes', NULL, 16 ) ;
	$out_arr['f_72'] = 	in_text ( 72, 3 ) ;
	$out_arr['f_73'] = 	in_check ( 73, 'Yes' , 'f_74') ;
	$out_arr['f_74'] = 	in_check ( 74, 'No' , 'f_73') ;
	$out_arr['f_75'] = 	in_check ( 75, 'Yes' , 'f_76') ;
	$out_arr['f_76'] = 	in_check ( 76, 'No' , 'f_75') ;

	$out_arr['f_77'] = 	in_text ( 77, 7 ) ;
	$out_arr['f_78'] = 	in_text ( 78, 15 ) ;
	$out_arr['f_79'] = 	in_text ( 79, 7 ) ;
	$out_arr['f_80'] = 	in_text ( 80, 7 ) ;
	$out_arr['f_81'] = 	in_text ( 81, 6 ) ;
	$out_arr['f_82'] = 	in_check ( 82, 'Yes', NULL, 16 ) ;
	$out_arr['f_83'] = 	in_text ( 83, 3 ) ;
	$out_arr['f_84'] = 	in_check ( 84, 'Yes' , 'f_85') ;
	$out_arr['f_85'] = 	in_check ( 85, 'No' , 'f_84') ;
	$out_arr['f_86'] = 	in_check ( 86, 'Yes' , 'f_87') ;
	$out_arr['f_87'] = 	in_check ( 87, 'No' , 'f_86') ;

	$out_arr['f_88'] = 	in_text ( 88, 7 ) ;
	$out_arr['f_89'] = 	in_text ( 89, 15 ) ;
	$out_arr['f_90'] = 	in_text ( 90, 7 ) ;
	$out_arr['f_91'] = 	in_text ( 91, 7 ) ;
	$out_arr['f_92'] = 	in_text ( 92, 6 ) ;
	$out_arr['f_93'] = 	in_check ( 93, 'Yes', NULL, 16) ;
	$out_arr['f_94'] = 	in_text ( 94, 3 ) ;
	$out_arr['f_95'] = 	in_check ( 95, 'Yes' , 'f_96') ;
	$out_arr['f_96'] = 	in_check ( 96, 'No' , 'f_95') ;
	$out_arr['f_97'] = 	in_check ( 97, 'Yes' , 'f_98') ;
	$out_arr['f_98'] = 	in_check ( 98, 'No' , 'f_97') ;

	$out_arr['f_99'] = 		in_text ( 99,  7 ) ;
	$out_arr['f_100'] = 	in_text ( 100, 15 ) ;
	$out_arr['f_101'] = 	in_text ( 101, 7 ) ;
	$out_arr['f_102'] = 	in_text ( 102, 7 ) ;
	$out_arr['f_103'] = 	in_text ( 103, 6 ) ;
	$out_arr['f_104'] = 	in_check ( 104, 'Yes', NULL, 16 ) ;
	$out_arr['f_105'] = 	in_text ( 105, 3 ) ;
	$out_arr['f_106'] = 	in_check ( 106, 'Yes' , 'f_107') ;
	$out_arr['f_107'] = 	in_check ( 107, 'No' , 'f_106') ;
	$out_arr['f_108'] = 	in_check ( 108, 'Yes' , 'f_109') ;
	$out_arr['f_109'] = 	in_check ( 109, 'No' , 'f_108') ;

	$out_arr['f_110'] = 	in_area ( 110, 82 , 2, ' Enter information here.' ) ;

	$out_arr['f_111'] = 	in_check ( 111, '&larr;&nbsp;Check box if aviation assets are utilized for rescue. If yes, coordinate with Air Operations' ) ;

	$out_arr['f_112'] = 	in_text ( 112, 24, NULL, 16 ) ;
	$out_arr['f_113'] = 	in_text ( 113, 16, NULL, 16 ) ;
	$out_arr['f_114'] = 	in_text ( 114, 24, NULL, 16  ) ;
	$out_arr['f_115'] = 	in_text ( 115, 16, NULL, 16  ) ;
	$out_arr['f_116'] = 	in_text ( 116, 10 ) ;
	$out_arr['f_117'] = 	in_text ( 117, 10, NULL, 16  ) ;
	$out_arr['f_118'] = 	in_text ( 118, 6 ) ;


	return $out_arr;

	}		// end function set input strings ()

function merge_template () {		// merge argument array with template -- e.g., <td> $my_inputs_arr['fn'] </td>

	include ( './ics.css.php' ) ;

	$my_inputs_arr = set_input_strings () ;

//	<table style = 'table-layout: fixed; width: 10in; border:1px solid black; border-collapse: collapse; '>

	$out_str = "\n
	<table style = '{$table} width: 8in;'>

	<tr style = 'height:0px;'>
	<td style = 'width: 11%; background-color: transparent;'></td>
	<td style = 'width: 9%;  background-color: transparent;'></td>
	<td style = 'width: 11%; background-color: transparent;'></td>
	<td style = 'width: 11%; background-color: transparent;'></td>
	<td style = 'width: 11%; background-color: transparent;'></td>
	<td style = 'width: 10%; background-color: transparent;'></td>
	<td style = 'width: 12%; background-color: transparent;'></td>
	<td style = 'width: 12%; background-color: transparent;'></td>
	<td style = 'width: 12%; background-color: transparent;'></td>
	</tr>
	<tr valign = 'top'>
		<td colspan=3 style = 'text-align: left; vertical-align: top;' ><b>&nbsp;1. Incident Name:</b></td>
		<td colspan=2 rowspan = 2 style = 'text-align: left; vertical-align: top;'><b>&nbsp;2. Operational Period:<br /></b></td>
		<td colspan=4 style = 'text-align: left; vertical-align: top;'>&nbsp;Date from {$my_inputs_arr['f_0']} &nbsp;to {$my_inputs_arr['f_1']}</td>
		</tr>
		<tr>
		<td colspan=3 > {$my_inputs_arr['f_2']} </td>
		<td colspan=4>&nbsp;Time from {$my_inputs_arr['f_3']} &nbsp;to {$my_inputs_arr['f_4']}</td>
		</tr>

	<tr>
		<td colspan = 9 style = 'text-align: left; border: 2px solid black;'><b>3. Medical Aid Stations: </b> </td>
		</tr>

	<tr>
		<td colspan = 2 style = 'text-align: center; vertical-align: bottom;' >Name </td>
		<td colspan = 3 style = 'text-align: center; vertical-align: bottom;' >Location</td>
		<td colspan = 2 style = 'text-align: center; vertical-align: bottom;' >Contact Number(s)/<br>Frequency </td>
		<td colspan = 2 style = 'text-align: center; vertical-align: bottom;' >Paramedics<br>on Site?</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_5']}</td>
		<td colspan = 3>{$my_inputs_arr['f_6']}</td>
		<td colspan = 2>{$my_inputs_arr['f_7']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_8']} {$my_inputs_arr['f_9']}</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_10']}</td>
		<td colspan = 3>{$my_inputs_arr['f_11']}</td>
		<td colspan = 2>{$my_inputs_arr['f_12']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_13']} {$my_inputs_arr['f_14']}</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_15']}</td>
		<td colspan = 3>{$my_inputs_arr['f_16']}</td>
		<td colspan = 2>{$my_inputs_arr['f_17']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_18']} {$my_inputs_arr['f_19']}</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_20']}</td>
		<td colspan = 3>{$my_inputs_arr['f_21']}</td>
		<td colspan = 2>{$my_inputs_arr['f_22']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_23']} {$my_inputs_arr['f_24']}</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_25']}</td>
		<td colspan = 3>{$my_inputs_arr['f_26']}</td>
		<td colspan = 2>{$my_inputs_arr['f_27']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_28']} {$my_inputs_arr['f_29']}</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_30']}</td>
		<td colspan = 3>{$my_inputs_arr['f_31']}</td>
		<td colspan = 2>{$my_inputs_arr['f_32']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_33']} {$my_inputs_arr['f_34']}</td>
		</tr>

	<tr>
		<td colspan = 9 style = 'text-align: left; border: 2px solid black;'><b>4. Transportation</b> (indicate air or ground): </b> </td>
		</tr>

	<tr>
		<td colspan = 2 style = 'text-align: center; vertical-align: bottom;' >Ambulance Service</td>
		<td colspan = 3 style = 'text-align: center; vertical-align: bottom;' >Location</td>
		<td colspan = 2 style = 'text-align: center; vertical-align: bottom;' >Contact Number(s)/<br>Frequency</td>
		<td colspan = 2 style = 'text-align: center; vertical-align: bottom;' >Service Level</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_35']}</td>
		<td colspan = 3>{$my_inputs_arr['f_36']}</td>
		<td colspan = 2>{$my_inputs_arr['f_37']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_38']} {$my_inputs_arr['f_39']}</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_40']}</td>
		<td colspan = 3>{$my_inputs_arr['f_41']}</td>
		<td colspan = 2>{$my_inputs_arr['f_42']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_43']} {$my_inputs_arr['f_44']}</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_45']}</td>
		<td colspan = 3>{$my_inputs_arr['f_46']}</td>
		<td colspan = 2>{$my_inputs_arr['f_47']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_48']} {$my_inputs_arr['f_49']}</td>
		</tr>
	<tr>
		<td colspan = 2>{$my_inputs_arr['f_50']}</td>
		<td colspan = 3>{$my_inputs_arr['f_51']}</td>
		<td colspan = 2>{$my_inputs_arr['f_52']}</td>
		<td colspan = 2 style = 'text-align: center;'> {$my_inputs_arr['f_53']} {$my_inputs_arr['f_54']}</td>
		</tr>
	<tr>
		<td colspan = 9 style = 'text-align: left; border: 2px solid black;'><b>5. Hospitals</b>:</b> </td>
		</tr>
	<tr style = 'border: 1px solid black;'>
		<td style = 'text-align: center; vertical-align: bottom;' >Hospital Name </td>
		<td colspan =2 style = 'text-align: center; vertical-align: bottom;' >Address, Latitude & Longitude if Helipad </td>
		<td style = 'text-align: center; vertical-align: bottom;' >Contact Number(s)/ Frequency</td>
		<td style = 'text-align: center; vertical-align: bottom;' >Air Travel Time </td>
		<td style = 'text-align: center; vertical-align: bottom;' >Ground Travel Time </td>
		<td style = 'text-align: center; vertical-align: bottom;' >Trauma Center</td>
		<td style = 'text-align: center; vertical-align: bottom;' >Burn Center</td>
		<td style = 'text-align: center; vertical-align: bottom;' >Helipad</td>
		</tr>
	<tr>
		<td>{$my_inputs_arr['f_55']}</td>
		<td colspan=2>{$my_inputs_arr['f_56']}</td>
		<td>{$my_inputs_arr['f_57']}</td>
		<td>{$my_inputs_arr['f_58']}</td>
		<td>{$my_inputs_arr['f_59']}</td>
		<td style = 'white-space: nowrap;'>{$my_inputs_arr['f_60']}<br>Level {$my_inputs_arr['f_61']}</td>
		<td>{$my_inputs_arr['f_62']}<br>{$my_inputs_arr['f_63']}</td>
		<td>{$my_inputs_arr['f_64']}<br>{$my_inputs_arr['f_65']}</td>
		</tr>
	<tr>
		<td>{$my_inputs_arr['f_66']}</td>
		<td colspan=2>{$my_inputs_arr['f_67']}</td>
		<td>{$my_inputs_arr['f_68']}</td>
		<td>{$my_inputs_arr['f_69']}</td>
		<td>{$my_inputs_arr['f_70']}</td>
		<td>{$my_inputs_arr['f_71']}<br>Level {$my_inputs_arr['f_72']}</td>
		<td>{$my_inputs_arr['f_73']}<br>{$my_inputs_arr['f_74']}</td>
		<td>{$my_inputs_arr['f_75']}<br>{$my_inputs_arr['f_76']}</td>
		</tr>
	<tr>
		<td>{$my_inputs_arr['f_77']}</td>
		<td colspan=2>{$my_inputs_arr['f_78']}</td>
		<td>{$my_inputs_arr['f_79']}</td>
		<td>{$my_inputs_arr['f_80']}</td>
		<td>{$my_inputs_arr['f_81']}</td>
		<td style = 'white-space: nowrap;'>{$my_inputs_arr['f_82']}<br>Level {$my_inputs_arr['f_83']}</td>
		<td>{$my_inputs_arr['f_84']}<br>{$my_inputs_arr['f_85']}</td>
		<td>{$my_inputs_arr['f_86']}<br>{$my_inputs_arr['f_87']}</td>
		</tr>

	<tr>
		<td>{$my_inputs_arr['f_88']}</td>
		<td colspan=2>{$my_inputs_arr['f_89']}</td>
		<td>{$my_inputs_arr['f_90']}</td>
		<td>{$my_inputs_arr['f_91']}</td>
		<td>{$my_inputs_arr['f_92']}</td>
		<td style = 'white-space: nowrap;'>{$my_inputs_arr['f_93']}<br>Level {$my_inputs_arr['f_94']}</td>
		<td>{$my_inputs_arr['f_95']}<br>{$my_inputs_arr['f_96']}</td>
		<td>{$my_inputs_arr['f_97']}<br>{$my_inputs_arr['f_98']}</td>
		</tr>
	<tr>
		<td>{$my_inputs_arr['f_99']}</td>
		<td colspan=2>{$my_inputs_arr['f_100']}</td>
		<td>{$my_inputs_arr['f_101']}</td>
		<td>{$my_inputs_arr['f_102']}</td>
		<td>{$my_inputs_arr['f_103']}</td>
		<td style = 'white-space: nowrap;'>{$my_inputs_arr['f_104']}<br>Level {$my_inputs_arr['f_105']}</td>
		<td>{$my_inputs_arr['f_106']}<br>{$my_inputs_arr['f_107']}</td>
		<td>{$my_inputs_arr['f_108']}<br>{$my_inputs_arr['f_109']}</td>
		</tr>

	<tr>
		<td colspan = 9 style = 'text-align: left; border: 2px solid black;'><b>6. Special Medical Emergency Procedures: </b>:</b> </td>
		</tr>

	<tr>
		<td colspan = 9 style = 'text-align: center;'>{$my_inputs_arr['f_110']}
		<br>{$my_inputs_arr['f_111']}.
		</td>
		</tr>
	<tr style = 'border: 2px solid black;'>
		<td colspan = 3><b>7. Prepared by (Medical Unit Leader):</b></td>
		<td colspan = 6> Name:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$my_inputs_arr['f_112']} <br>Signature: {$my_inputs_arr['f_113']} </td>
		</tr>
	<tr style = 'border: 2px solid black;'>
		<td colspan = 3><b>8. Approved by (Safety Officer):</b></td>
		<td colspan = 6> Name:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$my_inputs_arr['f_114']} <br>Signature: {$my_inputs_arr['f_115']} </td>
		</tr>
	<tr>
		<td colspan = 1 style = 'text-align: left; border: 2px solid black;'><b> ICS 206 </b> </td>
		<td colspan = 2 style = 'text-align: left; border: 2px solid black;'><b> IAP Page </b> {$my_inputs_arr['f_116']}</td>
		<td colspan = 6> Date/Time {$my_inputs_arr['f_117']}  {$my_inputs_arr['f_118']} </td>
		</tr>



		</table><br /><br />";

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
	input[type=text]:focus 		{ background-color: yellow; }
	textarea:focus 				{ background-color: yellow; }
	td							{ border: 1px solid black; }
</style>

<script>

	String.prototype.trim = function() {
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
		if ( our_form.f_2.value.trim () .length > 0 ) {our_form.submit () ;}
		else {
			alert ( "Incident name is required" ) ;
			our_form.f_2.focus () ;
			return false;
			}
		}		// end function validate ()

	function save ( our_form ) {
		if ( our_form.f_2.value.trim () == "" ) {
			alert ( "Incident name is required." ) ;
			our_form.f_2.focus () ;
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
	 	}		// end function chk del ()


</script>

</HEAD>

<?php
$now_ts = now_ts();
$func = ( array_key_exists ( 'func', $_POST ) ) ? $_POST['func']: "c";

switch ( $func ) {

	case "c" :

?>
<BODY style = '<?php echo $body;?>' onload = "init () ;">		<!-- <?php echo __LINE__ ; ?> -->
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<SPAN ID='reset_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ;init () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' 							class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
 	<SPAN ID='save_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="save ( document.main_form ) ;">			 <SPAN STYLE='float: left;'><?php print get_text ( "Save to DB" ) ;?></SPAN>	<IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
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
		document.main_form.f_0.value = '<?php echo $the_date;?>';
		document.main_form.f_1.value = '<?php echo $the_date;?>';
		document.main_form.f_3.value = '<?php echo $the_time;?>';
		document.main_form.f_4.value = '<?php echo $the_time;?>';

//		document.main_form.f_60.value = '<?php echo $the_by;?>';
		document.main_form.f_117.value = '<?php echo $the_date;?>';
		document.main_form.f_118.value = '<?php echo $the_time;?>';
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

		$name = mysql_real_escape_string ( $_POST['f_2'] ) ;
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
 	<SPAN ID='reset_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ;document.main_form.f_0.focus () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' 							class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
 	<SPAN ID='save_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="save ( document.main_form ) ;">				<SPAN STYLE='float: left;'><?php print get_text ( "Save to DB" ) ;?></SPAN>	<IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE='OK - Mail this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='m';validate ( document.main_form ) ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
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
		$script = basename ( __FILE__ );

		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			 `name` = 		'{$name}',
			 `payload` = 	'{$payload}',
			 `script` = 	'{$script}',
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

			$name = mysql_real_escape_string ( $_POST['f_2'] ) ;
			$payload = base64_encode ( json_encode ( $_POST ) ) ;				// whew!!
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]ics` ( `name`, `type`, `payload`, count, `_by`, `_from`, `_as-of`, `_sent` ) VALUES
															 ( '{$name}', '" . FORM . "', '{$payload}', 0, {$_SESSION['user_id']}, '{$_SERVER['REMOTE_ADDR']}', '{$now_ts}', NULL ) ; ";
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
