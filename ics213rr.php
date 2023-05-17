<?php
/*
10/4/2014 - revised for inline style, vs css class
12/27/2014 - draggable buttom div
1/2/2015 - Estimated arrival date/time field sizes revised per LW email

*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
require_once('incs/functions.inc.php');		//7/28/10
//dump($_POST);

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= 'ics_date' LIMIT 1";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if ((mysql_affected_rows())==0) {		// copy user's date format

	$query_d = "SELECT `name`, `value` FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= 'date_format' LIMIT 1";
	$result_d = mysql_query($query_d) or do_error($query_d, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_d = stripslashes_deep(mysql_fetch_assoc($result_d));
	$date_val = $row_d['value'];
	unset($result_d);

	$query = "INSERT INTO `$GLOBALS[mysql_prefix]settings` (`name`,`value`) VALUES('ics_date','{$date_val}')";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	}

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
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="6/13/09">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE>
table		{ width: 10in; border-style: solid; border-width: 2px; border-color: black;}
td			{ text-align:left;}
.bordered		{border-style: solid 2px black;}
.bordered_top	{border-top: solid 2px black;}
.bordered_left	{border-left: solid 2px black;}
</STYLE>
<script src = "./js/jquery-1.4.2.min.js"></script>
<script src="./js/jss.js" TYPE="application/x-javascript"></script>
<script src="./js/misc_function.js" TYPE="application/x-javascript"></script>

</HEAD>
<?php
if ( array_key_exists ( 'step',  $_POST) )	{ $step = $_POST['step'] ;}	//	array_key_exists(key,array) 	AK (K, A)
else										{ $step = 1;}
function template ($item) {
	global $step;
	$table_style = 		' width:10in; border-collapse: collapse; background-color: white; ';
	$footer_style = 	' width:auto; border-collapse: collapse; border:1px solid black; background-color: white; ';
	$tr_thin_style = 	' height: 21px; vertical-align:middle;';
	$td_heading_style = ' FONT-WEIGHT: 900; FONT-SIZE: 10px;  border:1px solid black;';
	$td_heading_style_c = ' FONT-WEIGHT: 900; FONT-SIZE: 10px;  border:1px solid black; text-align: center;';
	$td_plain_style = 	' FONT-WEIGHT: 400; FONT-SIZE: 10px; text-align: left; ';
	$td_plain_c_style = ' FONT-WEIGHT: 400; FONT-SIZE: 10px; text-align: center; ';
	$td_plain_l_style = ' FONT-WEIGHT: 400; FONT-SIZE: 10px; text-align: left; ';
	$vertical = 		' FONT-WEIGHT: 900; FONT-SIZE: 12px; text-align: center; ';

	$out_str = "\n
		<table border = 1 style = '{$table_style}'>\n
		<colgroup>\n
		<col style = 'width: .3in;'>\n
		<col style = 'width: .6in;'>\n
		<col style = 'width: .6in;'>\n
		<col style = 'width: .6in;'>\n
		<col style = 'width: 2.3in;'>\n
		<col style = 'width: 2.3in;'>\n
		<col style = 'width: 1.2in;'>\n
		<col style = 'width: 1.2in;'>\n
		<col style = 'width: auto;'>\n
		<col >\n
		</colgroup>\n
		<tr style = '{$tr_thin_style}' class = 'bordered' >\n
		<td colspan=5 style = '{$td_heading_style}'>&nbsp;1. Incident Name: {$item[0]}</td>\n
		<td colspan=1 style = '{$td_heading_style}'>&nbsp;2. Date/Time {$item[1]}</td>\n
		<td colspan=3 style = '{$td_heading_style}'>&nbsp;3. Resource Request No: {$item[2]}</td>\n
		</tr>\n";

	$out_str .= "\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 rowspan = 15 style = '{$vertical}; '>R<br />
			e<br />
			q<br />
			u<br />
			e<br />
			s<br />
			t<br />
			e<br />
			r</td>\n
		<td colspan=8 style = '{$td_heading_style}'>4. Order (Use additional forms when requesting different resource sources of supply.):</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_heading_style_c}' rowspan = 2 valign = 'top'>Qty</td>\n
		<td colspan=1 style = '{$td_heading_style_c}' rowspan = 2 valign = 'top'>Kind</td>\n
		<td colspan=1 style = '{$td_heading_style_c}' rowspan = 2 valign = 'top'>Type</td>\n
		<td colspan=2 style = '{$td_heading_style}' rowspan = 2 valign = 'top'>Detailed Item Description: (Vital characteristics, brand, specs,<br /> experience, size, etc.) </td>\n
		<td colspan=2 style = '{$td_heading_style}'>Arrival Date and Time</td>\n
		<td colspan=1 style = '{$td_heading_style_c}' rowspan = 2 valign = 'top'>Cost</td>\n
		</tr>\n

		<tr><td colspan=1 style = '{$td_plain_l_style}'>Requested</td><td colspan=1 style = '{$td_plain_l_style}'>Estimated</td></tr>\n

		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[3]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[4]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[5]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[6]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[7]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[8]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[9]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[10]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[11]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[12]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[13]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[14]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[15]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[16]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[17]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[18]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[19]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[20]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[21]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[22]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[23]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[24]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[25]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[26]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[27]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[28]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[29]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[30]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[31]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[32]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[33]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[34]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[35]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[36]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[37]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[38]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[39]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[40]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[41]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[42]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[43]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[44]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[45]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[46]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[47]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[48]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[49]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[50]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[51]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[52]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[53]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[54]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[55]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[56]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[57]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[58]}</td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[59]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[60]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[61]}</td>\n
		<td colspan=2 style = '{$td_plain_l_style}'>{$item[62]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[63]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[64]}</td>\n
		<td colspan=1 style = '{$td_plain_l_style}'>{$item[65]}</td>\n
		</tr>\n

		<tr style = '{$tr_thin_style}' >\n
		<td colspan=8 style = '{$td_heading_style}' class = 'bordered' >5. Requested Delivery/Reporting Location: {$item[66]}</td>\n
		</tr>
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=8 style = '{$td_heading_style} bordered_left; ' class = 'bordered' >6. Suitable Substitutes/and/or Suggested Sources: {$item[67]}</td>\n
		</tr>
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=4 style = '{$td_heading_style}'>7. Requested by Name/Position: {$item[68]}</td>\n
		<td colspan=1 style = '{$td_heading_style}'>8. Priority: {$item[69]}</td>\n
		<td colspan=3 style = '{$td_heading_style}'>9. Section Chief Approval: {$item[70]}</td>\n
		</tr>";

	$out_str .= "\n
		<tr style = '{$tr_thin_style};'>\n
		<td colspan=1 rowspan = 4 style = '{$vertical} border-top: solid 2px black;'>L<br />
			o<br />
			g<br />
			</td>\n
		<td colspan=5 style = '{$td_heading_style} border-top: solid 2px black;' class = 'bordered_top'>10. Logistics Order Number: {$item[71]}</td>\n
		<td colspan=3 rowspan = 2 style = '{$td_heading_style};border-top: solid 2px black;' valign = 'top' >11. Supplier Phone/Fax/Email:<br /> {$item[72]} </td>\n
		</tr>\n

		<tr style = '{$tr_thin_style}' >\n
		<td colspan=5 style = '{$td_heading_style}border-top: solid 2px black;'>12. Name of Supplier/POC: {$item[73]}</td>\n
		</tr>\n

		<tr style = '{$tr_thin_style}' >\n
		<td valign = 'top' colspan=8 style = '{$td_heading_style}border-top: solid 2px black; '><span style = 'vertical-align: top;'>13. Notes:</span> {$item[74]}</td>\n
		</tr>\n

		<tr style = '{$tr_thin_style}' valign = top >\n
		<td colspan=5 style = '{$td_heading_style} border-top: solid 2px black;'>14. Approval Signature of Auth Logistics Rep: {$item[75]}</td>\n
		<td colspan=3 rowspan = 2  valign = 'top' style = '{$td_heading_style} border-top: solid 2px black;'>15. Date/Time: {$item[76]}</td>\n
		</tr>\n";

	$out_str .= "\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 rowspan = 1 style = '{$td_heading_style} border-top: solid 2px black;'></td>\n
		<td colspan=8 style = '{$td_heading_style} border-top: solid 2px black;'>16. Order placed by (check box): {$item[77]} </td>\n
		</tr>\n";

	$out_str .= "\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=1 rowspan = 2 style = '{$vertical} border-top: solid 2px black;'>F<br />
			i<br />
			n<br />
			</td>\n
		<td colspan=8 style = '{$td_heading_style} border-top: solid 2px black;'>17. Reply/Comments from Finance: {$item[78]} </td>\n
		</tr>\n
		<tr style = '{$tr_thin_style}' >\n
		<td colspan=5 style = '{$td_heading_style} border-top: solid 2px black;' >18. Finance Section Signature: {$item[79]}</td>\n
		<td colspan=3 style = '{$td_heading_style} border-top: solid 2px black;'>19. Date/Time: {$item[80]}</td>\n
		</tr>\n";

	$out_str  .= "<tr><td colspan = 9 style = 'border-top: solid 2px black;'><B>ICS-213 RR Page 1</B>
		</td></tr>
		</table>\n";	// end of returned string

	return $out_str;
	}		// end function template ()


switch ($step) {

	case 1 :
?>

<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LINK="#FF0000" VLINK="#800000" ALINK="#FF00FF" >
<center>

<form name = 'theForm' method = 'post' action = '<?php echo basename(__FILE__);?>' >
<?php
// dump($_POST);

		function in_area ($name, $cols, $rows, $data,  $dis) {			// <textarea ...
			$tabindex = $name + 1;
			return "<textarea id='f{$name}'  name='f{$name}' cols={$cols} rows={$rows} tabindex={$tabindex}>{$data}</textarea>";
			}

		function in_text( $name, $size, $data, $dis) {		//  <input type=text ...
			$tabindex = $name + 1;
			return "<input type=text id='f{$name}'  name='f{$name}' size={$size} maxlength={$size} value='{$data}' tabindex=($tabindex} /> ";
			}

		$item = array();
		$item[0] =  in_text  (  0, 48, "", "");		// name, size, data, dis

		$the_date = ($step ==1)? date(get_variable("ics_date"), now()) : $item[1];
		$item[1] =  in_text  (  1, 16, $the_date, "");

		$item[2] =  in_text  (  2, 16, "", "");
		$item[3] =  in_text  (  3, 10, "", "");
		$item[4] =  in_text  (  4, 10, "", "");
		$item[5] =  in_text  (  5, 10, "", "");
		$item[6] =  in_text  (  6, 60, "", "");
		$item[7] =  in_text  (  7, 14, "", "");
		$item[8] =  in_text  (  8, 14, "", "");
		$item[9] =  in_text  (  9,  8, "", "");

 		$item[10] =  in_text  ( 10, 10, "", "");
 		$item[11] =  in_text  ( 11, 10, "", "");
 		$item[12] =  in_text  ( 12, 10, "", "");
 		$item[13] =  in_text  ( 13, 60, "", "");
 		$item[14] =  in_text  ( 14, 14, "", "");
 		$item[15] =  in_text  ( 15, 14, "", "");
 		$item[16] =  in_text  ( 16,  8, "", "");
 		$item[17] =  in_text  ( 17, 10, "", "");
 		$item[18] =  in_text  ( 18, 10, "", "");
 		$item[19] =  in_text  ( 19, 10, "", "");

 		$item[20] =  in_text  ( 20, 60, "", "");
 		$item[21] =  in_text  ( 21, 14, "", "");
 		$item[22] =  in_text  ( 22, 14, "", "");
 		$item[23] =  in_text  ( 23,  8, "", "");
 		$item[24] =  in_text  ( 24, 10, "", "");
 		$item[25] =  in_text  ( 25, 10, "", "");
 		$item[26] =  in_text  ( 26, 10, "", "");
 		$item[27] =  in_text  ( 27, 60, "", "");
 		$item[28] =  in_text  ( 28, 14, "", "");
 		$item[29] =  in_text  ( 29, 14, "", "");

 		$item[30] =  in_text  ( 30,  8, "", "");
 		$item[31] =  in_text  ( 31, 10, "", "");
 		$item[32] =  in_text  ( 32, 10, "", "");
 		$item[33] =  in_text  ( 33, 10, "", "");
 		$item[34] =  in_text  ( 34, 60, "", "");
 		$item[35] =  in_text  ( 35, 14, "", "");
 		$item[36] =  in_text  ( 36, 14, "", "");
 		$item[37] =  in_text  ( 37,  8, "", "");
 		$item[38] =  in_text  ( 38, 10, "", "");
 		$item[39] =  in_text  ( 39, 10, "", "");

 		$item[40] =  in_text  ( 40, 10, "", "");
 		$item[41] =  in_text  ( 41, 60, "", "");
 		$item[42] =  in_text  ( 42, 14, "", "");
 		$item[43] =  in_text  ( 43, 14, "", "");
 		$item[44] =  in_text  ( 44,  8, "", "");
 		$item[45] =  in_text  ( 45, 10, "", "");
 		$item[46] =  in_text  ( 46, 10, "", "");
 		$item[47] =  in_text  ( 47, 10, "", "");
 		$item[48] =  in_text  ( 48, 60, "", "");
 		$item[49] =  in_text  ( 49, 14, "", "");

 		$item[50] =  in_text  ( 50, 14, "", "");
 		$item[51] =  in_text  ( 51,  8, "", "");
 		$item[52] =  in_text  ( 52, 10, "", "");
 		$item[53] =  in_text  ( 53, 10, "", "");
 		$item[54] =  in_text  ( 54, 10, "", "");
 		$item[55] =  in_text  ( 55, 60, "", "");
 		$item[56] =  in_text  ( 56, 14, "", "");
 		$item[57] =  in_text  ( 57, 14, "", "");
 		$item[58] =  in_text  ( 58,  8, "", "");
 		$item[59] =  in_text  ( 59, 10, "", "");

 		$item[60] =  in_text  ( 60, 10, "", "");
 		$item[61] =  in_text  ( 61, 10, "", "");
 		$item[62] =  in_text  ( 62, 60, "", "");
 		$item[63] =  in_text  ( 63, 14, "", "");
 		$item[64] =  in_text  ( 64, 14, "", "");
 		$item[65] =  in_text  ( 65,  8, "", "");
 		$item[66] =  in_text  ( 66, 102, "", "");
 		$item[67] =  in_text  ( 67, 94, "", "");
 		$item[68] =  in_text  ( 68, 32, "", "");
 		$item[69] =  in_text  ( 69, 16, "", "");

 		$item[70] =  in_text  ( 70, 14, "", "");
 		$item[71] =  in_text  ( 71, 76, "", "");
 		$item[72] =  in_text  ( 72, 38, "", "");
 		$item[73] =  in_text  ( 73, 76, "", "");
 		$item[74] =  in_area  ( 74, 90, 1, "", "");
 		$item[75] =  in_text  ( 75, 48, "", "");
 		$item[76] =  in_text  ( 76, 24, "", "");
 		$item[77] =  in_text  ( 77, 10, "", "");
 		$item[78] =  in_text  ( 78, 112, "", "");
 		$item[79] =  in_text  ( 79, 64, "", "");

 		$item[80] =  in_text  ( 80, 24, "", "");

echo template ($item);
?>
<div class="text" style="position: fixed; top: 20px; left: 10px; width:auto;">
 	<SPAN ID='reset_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="this.form.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.can_form.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='sub_but' class='plain text' style='float: none; width: 120px;; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate(this.form);"><SPAN STYLE='float: left;'><?php print get_text("Next");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN><BR />
</div>
<input type = 'hidden' name = 'step' value = 2 />
<input type = 'hidden' name = 'frm_add_str' value = '<?php echo $_POST["frm_add_str"];?>'/>
</form>
<script>
	function validate(our_form) {		// ics form name check
		if (our_form.f0.value.trim().length > 0) {our_form.submit();}
		else {
			alert("Incident Name is required");
			our_form.f0.focus();
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
		$html_message = template ($stuff) ;

//		snap (__LINE__, $html_message );
		$to_array = explode ("|", $_POST['frm_add_str']);
		$to = $sep = "";
		for ($i=0; $i < count($to_array); $i++) {
			$to .= "{$sep}{$to_array[$i]}";
			$sep = ",";
			}		// end for ()

		$subject ="ICS 213-RR Message - {$stuff[0]} - {$stuff[1]}";		// subject, per form data
		$temp = get_variable('email_from');
		$from_address = (is_email($temp))? $temp: "ticketscad.com";
		$from_display_name=get_variable('title_string');
		$temp = shorten(strip_tags(get_variable('title_string')), 30);
		$from_display_name = str_replace ( "'", "", $temp);
		$result = html_mail ($to, $subject, $html_message, $from_address, $from_display_name);	// does native mail
?>
<script>
function myfade( elem, time ) {
	alert(<?php echo __LINE__;?>);
	var startOpacity = elem.style.opacity || 1;
	elem.style.opacity = startOpacity;
	(function go() {
		alert(446);
		elem.style.opacity -= startOpacity / ( time / 100 );
		elem.style.filter = 'alpha(opacity=' + elem.style.opacity * 100 + ')';		// for IE
		if( elem.style.opacity > 0 ) setTimeout( go, 100 );
		else elem.style.display = 'none';
		})();
	}
</script>
<body onload = 'setTimeout(function(){ window.close()}, 5000 );' >
<center>
<div id = 'complete' style = 'margin-top:40px;'><H2 >ICS 213-RR Message sent - <?php echo $stuff[0] ; ?></h2><i>window closing</i></div>

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
</HTML>
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
