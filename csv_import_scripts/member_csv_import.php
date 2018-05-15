<?php
/*
6/7/12	New file, allows upload of existing csv file with member details on, processing of this and adding to DB.
*/

require_once('../incs/functions.inc.php');
require_once('../incs/mysql.inc.php');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets Personnel Database - Member Details</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<meta http-equiv=”X-UA-Compatible” content=”IE=EmulateIE7" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="../default.css?version=<?php print time();?>" TYPE="text/css">	
	<SCRIPT type="text/javascript">
	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}
		
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}
		
	function do_hover_centered (the_id) {
		CngClass(the_id, 'hover_centered');
		return true;
		}
		
	function do_lo_hover (the_id) {
		CngClass(the_id, 'lo_hover');
		return true;
		}
		
	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
		return true;
		}
		
	function do_plain_centered (the_id) {				// 8/21/10
		CngClass(the_id, 'plain_centered');
		return true;
		}
		
	function do_lo_plain (the_id) {
		CngClass(the_id, 'lo_plain');
		return true;
		}		

	function $() {															// 12/20/08
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
		
	function do_hide_val(fieldid, id) {
		var selectfield = fieldid;
		var blankfield = "isblank_" + id;
		if($(selectfield).value != 999999) {
			$(blankfield).style.display = "none";
			} else {
			$(blankfield).style.display = "inline";
			}
		}
	</SCRIPT>
	</HEAD>
<?php

if(empty($_POST)) {	//	Upload a file for import
?>
	<BODY style='background-color: #EFEFEF;'>
	<CENTER>
	<DIV id='topbar' style='position: fixed; top: 0px; left: 0px; margin-left: 5%; font-size: 14px; z-index: 999; width: 90%; background-color: #DEDEDE; border: 2px outset #CECECE;'>
		<DIV id='buttonbar' style='float: right;'>
			<SPAN class = 'plain' style='border: 0px; background-color: #DEDEDE;'>FORM CONTROLS&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
			<SPAN ID = 'can_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?> <IMG style='vertical-align: middle; float: right;' src="../img/back.png"/></SPAN>
			<SPAN ID = 'sub_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['fileForm'].submit();"><?php print get_text('Submit');?> <IMG style='vertical-align: middle; float: right;' src="../img/save.png"/></SPAN>			
		</DIV>
		<BR />
		<BR />			
		<DIV class='tablehead' style='width: 100%; float: right;'><b>Import Members from CSV File</b></DIV>							
	</DIV>	
	<DIV id='outer' style='position: absolute; width: 100%;'>
		<DIV style='position: relative; top: 100px; width: 60%; border: 2px outset #FFFFFF; background-color: #FEF7D6; padding: 20px;'>
			<FORM enctype="multipart/form-data" METHOD="POST" NAME= "fileForm" ACTION="<?php print basename(__FILE__);?>" />
			<FIELDSET>
				<LEGEND><?php print get_text('Import CSV File');?></LEGEND>
					<LABEL for="the_file"><?php print get_text('Select File to Upload');?>:</LABEL>
						<INPUT TYPE="file" NAME="the_file" SIZE="48" VALUE="" style='cursor: pointer;'>
						<BR />
			</FIELDSET>
			<INPUT type='hidden' name='stage' value=1>
			</FORM>
			
		</DIV>
	</DIV>
	</CENTER>
	<FORM NAME='can_Form' METHOD="post" ACTION = "../config.php"></FORM>			
	</BODY>
<?php
} elseif((isset($_POST)) && ($_POST['stage'] == 2)) {	//	Process the data
//	dump($_POST);
	$i = 1;
	$row = 0;
	$the_arr = array();
	$col_names = array();
	$titles = "";	
	if (($handle = fopen("./tmp_uploads/import.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$num = count($data);
			if($row==0) {
				for($f=0; $f < $num; $f++) {
					$col_names[$f] = $data[$f];
					}	
				} else {		
				for ($c=0; $c < $num; $c++) {
					$thedata = trim( preg_replace( '/\s+/', ' ', $data[$c] ) );
					$the_arr[$row-1]["$col_names[$c]"] = $thedata;
				}
			}
			$row++;		
		}
		fclose($handle);
	}

	$who = 1;
//	$who = (array_key_exists('user_id', $_SESSION))? $_SESSION['user_id']: 0;
	$from = $_SERVER['REMOTE_ADDR'];			
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	for($z = 0; $z < count($the_arr); $z++) {
		$field1 = ($_POST['ic_col']['frm_field1'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field1']] : $_POST['def_val']['frm_field1'];
		$field2 = ($_POST['ic_col']['frm_field2'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field2']] : $_POST['def_val']['frm_field2'];
		$field3 = ($_POST['ic_col']['frm_field3'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field3']] : $_POST['def_val']['frm_field3'];
		$field4 = ($_POST['ic_col']['frm_field4'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field4']] : $_POST['def_val']['frm_field4'];
		$field6 = ($_POST['ic_col']['frm_field6'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field6']] : $_POST['def_val']['frm_field6'];
		$field7 = ($_POST['ic_col']['frm_field7'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field7']] : $_POST['def_val']['frm_field7'];
		$field8 = ($_POST['ic_col']['frm_field8'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field8']] : $_POST['def_val']['frm_field8'];
		$field9 = ($_POST['ic_col']['frm_field9'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field9']] : $_POST['def_val']['frm_field9'];
		$field10 = ($_POST['ic_col']['frm_field10'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field10']] : $_POST['def_val']['frm_field10'];
		$field11 = ($_POST['ic_col']['frm_field11'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field11']] : $_POST['def_val']['frm_field11'];
		$field12 = ($_POST['ic_col']['frm_field12'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field12']] : $_POST['def_val']['frm_field12'];
		$field13 = ($_POST['ic_col']['frm_field13'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field13']] : $_POST['def_val']['frm_field13'];
		$field14 = ($_POST['ic_col']['frm_field14'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field14']] : $_POST['def_val']['frm_field14'];
		$field15 = ($_POST['ic_col']['frm_field15'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field15']] : $_POST['def_val']['frm_field15'];
		$field16 = ($_POST['ic_col']['frm_field16'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field16']] : "$_POST[frm_year_frm_field16]-$_POST[frm_month_frm_field16]-$_POST[frm_day_frm_field16] 00:00:00";
		$field17 = ($_POST['ic_col']['frm_field17'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field17']] : "$_POST[frm_year_frm_field17]-$_POST[frm_month_frm_field17]-$_POST[frm_day_frm_field17] 00:00:00";
		$field18 = ($_POST['ic_col']['frm_field18'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field18']] : "$_POST[frm_year_frm_field18]-$_POST[frm_month_frm_field18]-$_POST[frm_day_frm_field18] 00:00:00";
		$field19 = ($_POST['ic_col']['frm_field19'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field19']] : $_POST['def_val']['frm_field19'];
		$field20 = ($_POST['ic_col']['frm_field20'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field20']] : $_POST['def_val']['frm_field20'];
		$field21 = ($_POST['ic_col']['frm_field21'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field21']] : $_POST['def_val']['frm_field21'];
		$field22 = ($_POST['ic_col']['frm_field22'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field22']] : $_POST['def_val']['frm_field22'];			
		$field23 = ($_POST['ic_col']['frm_field23'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field23']] : $_POST['def_val']['frm_field23'];
		$field24 = ($_POST['ic_col']['frm_field24'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field24']] : $_POST['def_val']['frm_field24'];
		$field25 = ($_POST['ic_col']['frm_field25'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field25']] : $_POST['def_val']['frm_field25'];
		$field26 = ($_POST['ic_col']['frm_field26'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field26']] : $_POST['def_val']['frm_field26'];			
		$field27 = ($_POST['ic_col']['frm_field27'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field27']] : $_POST['def_val']['frm_field27'];			
		$field28 = ($_POST['ic_col']['frm_field28'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field28']] : $_POST['def_val']['frm_field28'];			
		$field29 = ($_POST['ic_col']['frm_field29'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field29']] : $_POST['def_val']['frm_field29'];			
		$field30 = ($_POST['ic_col']['frm_field30'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field30']] : $_POST['def_val']['frm_field30'];			
		$field31 = ($_POST['ic_col']['frm_field31'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field31']] : $_POST['def_val']['frm_field31'];			
		$field32 = ($_POST['ic_col']['frm_field32'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field32']] : $_POST['def_val']['frm_field32'];			
		$field33 = ($_POST['ic_col']['frm_field33'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field33']] : $_POST['def_val']['frm_field33'];
		$field34 = ($_POST['ic_col']['frm_field34'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field34']] : $_POST['def_val']['frm_field34'];
		$field35 = ($_POST['ic_col']['frm_field35'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field35']] : $_POST['def_val']['frm_field35'];
		$field36 = ($_POST['ic_col']['frm_field36'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field36']] : $_POST['def_val']['frm_field36'];			
		$field37 = ($_POST['ic_col']['frm_field37'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field37']] : $_POST['def_val']['frm_field37'];			
		$field38 = ($_POST['ic_col']['frm_field38'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field38']] : $_POST['def_val']['frm_field38'];			
		$field39 = ($_POST['ic_col']['frm_field39'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field39']] : $_POST['def_val']['frm_field39'];			
		$field40 = ($_POST['ic_col']['frm_field40'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field40']] : $_POST['def_val']['frm_field40'];			
		$field41 = ($_POST['ic_col']['frm_field41'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field41']] : $_POST['def_val']['frm_field41'];			
		$field42 = ($_POST['ic_col']['frm_field42'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field42']] : $_POST['def_val']['frm_field42'];			
		$field43 = ($_POST['ic_col']['frm_field43'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field43']] : $_POST['def_val']['frm_field43'];
		$field44 = ($_POST['ic_col']['frm_field44'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field44']] : $_POST['def_val']['frm_field44'];
		$field45 = ($_POST['ic_col']['frm_field45'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field45']] : $_POST['def_val']['frm_field45'];
		$field46 = ($_POST['ic_col']['frm_field46'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field46']] : $_POST['def_val']['frm_field46'];			
		$field47 = ($_POST['ic_col']['frm_field47'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field47']] : $_POST['def_val']['frm_field47'];			
		$field48 = ($_POST['ic_col']['frm_field48'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field48']] : $_POST['def_val']['frm_field48'];			
		$field49 = ($_POST['ic_col']['frm_field49'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field49']] : $_POST['def_val']['frm_field49'];			
		$field50 = ($_POST['ic_col']['frm_field50'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field50']] : $_POST['def_val']['frm_field50'];			
		$field51 = ($_POST['ic_col']['frm_field51'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field51']] : $_POST['def_val']['frm_field51'];			
		$field52 = ($_POST['ic_col']['frm_field52'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field52']] : $_POST['def_val']['frm_field52'];			
		$field53 = ($_POST['ic_col']['frm_field53'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field53']] : $_POST['def_val']['frm_field53'];
		$field54 = ($_POST['ic_col']['frm_field54'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field54']] : $_POST['def_val']['frm_field54'];
		$field55 = ($_POST['ic_col']['frm_field55'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field55']] : $_POST['def_val']['frm_field55'];
		$field56 = ($_POST['ic_col']['frm_field56'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field56']] : "$_POST[frm_year_frm_field56]-$_POST[frm_month_frm_field56]-$_POST[frm_day_frm_field56] 00:00:00";
		$field57 = ($_POST['ic_col']['frm_field57'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field57']] : "$_POST[frm_year_frm_field57]-$_POST[frm_month_frm_field57]-$_POST[frm_day_frm_field57] 00:00:00";
		$field58 = ($_POST['ic_col']['frm_field58'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field58']] : "$_POST[frm_year_frm_field58]-$_POST[frm_month_frm_field58]-$_POST[frm_day_frm_field58] 00:00:00";
		$field59 = ($_POST['ic_col']['frm_field59'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field59']] : "$_POST[frm_year_frm_field59]-$_POST[frm_month_frm_field59]-$_POST[frm_day_frm_field59] 00:00:00";
		$field60 = ($_POST['ic_col']['frm_field60'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field60']] : "$_POST[frm_year_frm_field60]-$_POST[frm_month_frm_field60]-$_POST[frm_day_frm_field60] 00:00:00";
		$field61 = ($_POST['ic_col']['frm_field61'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field61']] : "$_POST[frm_year_frm_field61]-$_POST[frm_month_frm_field61]-$_POST[frm_day_frm_field61] 00:00:00";
		$field62 = ($_POST['ic_col']['frm_field62'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field62']] : "$_POST[frm_year_frm_field62]-$_POST[frm_month_frm_field62]-$_POST[frm_day_frm_field62] 00:00:00";
		$field63 = ($_POST['ic_col']['frm_field63'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field63']] : "$_POST[frm_year_frm_field63]-$_POST[frm_month_frm_field63]-$_POST[frm_day_frm_field63] 00:00:00";
		$field64 = ($_POST['ic_col']['frm_field64'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field64']] : "$_POST[frm_year_frm_field64]-$_POST[frm_month_frm_field64]-$_POST[frm_day_frm_field64] 00:00:00";
		$field65 = ($_POST['ic_col']['frm_field65'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_field65']] : "$_POST[frm_year_frm_field65]-$_POST[frm_month_frm_field65]-$_POST[frm_day_frm_field65] 00:00:00";

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]member` 
				(`field1`,
				`field2`, 
				`field4`, 
				`field6`, 
				`field7`, 				
				`field9`, 
				`field10`, 
				`field11`, 
				`field12`, 
				`field13`, 
				`field14`, 
				`field16`, 
				`field17`, 
				`field18`, 
				`field20`, 
				`field22`, 
				`field23`, 
				`field24`, 
				`field25`,
				`field26`, 
				`field27`, 
				`field28`, 
				`field29`, 
				`field30`, 
				`field31`, 
				`field32`, 
				`field33`, 
				`field34`, 
				`field35`, 
				`field36`, 
				`field37`, 
				`field38`, 
				`field39`, 
				`field40`, 
				`field41`, 
				`field42`,
				`field43`, 
				`field44`, 
				`field45`, 
				`field46`, 
				`field47`, 
				`field48`, 
				`field49`, 
				`field50`, 
				`field51`, 
				`field52`, 
				`field53`, 
				`field54`, 
				`field55`,
				`field56`,
				`field57`,
				`field58`,
				`field59`,
				`field60`,
				`field61`,
				`field62`,
				`field63`,
				`field64`,
				`field65`,
				`_by`, 
				`_on`, 						
				`_from`)
			VALUES (" . 
				quote_smart(trim($field1)) . "," .
				quote_smart(trim($field2)) . "," .
				quote_smart(trim($field4)) . "," .	
				quote_smart(trim($field6)) . "," .
				quote_smart(trim($field7)) . "," .					
				quote_smart(trim($field9)) . "," .	
				quote_smart(trim($field10)) . "," .				
				quote_smart(trim($field11)) . "," .	
				quote_smart(trim($field12)) . "," .					
				quote_smart(trim($field13)) . "," .
				quote_smart(trim($field14)) . "," .	
				quote_smart(trim($field16)) . "," .						
				quote_smart(trim($field17)) . "," .	
				quote_smart(trim($field18)) . "," .
				quote_smart(trim($field20)) . "," .	
				quote_smart(trim($field22)) . "," .
				quote_smart(trim($field23)) . "," .		
				quote_smart(trim($field24)) . "," .		
				quote_smart(trim($field25)) . "," .	
				quote_smart(trim($field26)) . "," .				
				quote_smart(trim($field27)) . "," .	
				quote_smart(trim($field28)) . "," .					
				quote_smart(trim($field29)) . "," .
				quote_smart(trim($field30)) . "," .	
				quote_smart(trim($field31)) . "," .	
				quote_smart(trim($field32)) . "," .	
				quote_smart(trim($field33)) . "," .	
				quote_smart(trim($field34)) . "," .	
				quote_smart(trim($field35)) . "," .	
				quote_smart(trim($field36)) . "," .	
				quote_smart(trim($field37)) . "," .	
				quote_smart(trim($field38)) . "," .	
				quote_smart(trim($field39)) . "," .	
				quote_smart(trim($field40)) . "," .	
				quote_smart(trim($field41)) . "," .	
				quote_smart(trim($field42)) . "," .	
				quote_smart(trim($field43)) . "," .	
				quote_smart(trim($field44)) . "," .	
				quote_smart(trim($field45)) . "," .						
				quote_smart(trim($field46)) . "," .	
				quote_smart(trim($field47)) . "," .
				quote_smart(trim($field48)) . "," .	
				quote_smart(trim($field49)) . "," .	
				quote_smart(trim($field50)) . "," .	
				quote_smart(trim($field51)) . "," .	
				quote_smart(trim($field52)) . "," .	
				quote_smart(trim($field53)) . "," .	
				quote_smart(trim($field54)) . "," .	
				quote_smart(trim($field55)) . "," .	
				quote_smart(trim($field56)) . "," .	
				quote_smart(trim($field57)) . "," .	
				quote_smart(trim($field58)) . "," .	
				quote_smart(trim($field59)) . "," .	
				quote_smart(trim($field60)) . "," .	
				quote_smart(trim($field61)) . "," .	
				quote_smart(trim($field62)) . "," .	
				quote_smart(trim($field63)) . "," .	
				quote_smart(trim($field64)) . "," .	
				quote_smart(trim($field65)) . "," .	
				$who . "," .	
				quote_smart(trim($now)) . "," .					
				quote_smart(trim($from)) . ");";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	
		}
?>
	<BODY style='background-color: #EFEFEF;'>
	<CENTER>
	<DIV id='outer' style='position: absolute; width: 100%;'>
		<DIV style='position: relative; top: 5%; width: 60%; max-height: 350px; border: 2px outset #FFFFFF; background-color: #FEF7D6; padding: 20px;'>
		Data successfully imported.<BR />
		<A HREF='../config.php'>Back to Config</A>
		</DIV>
	</DIV>
	</CENTER>
	</BODY>
<?php 		
	exit();
} elseif((isset($_POST)) && ($_POST['stage'] == 1)) {	//	Process the uploaded file
	if (isset($_FILES['the_file'])) {
		$errmsg = "";	
		$upload_directory = "./tmp_uploads/";
		if (!(file_exists($upload_directory))) {				
			mkdir ($upload_directory, 0777);
			}
		chmod($upload_directory, 0777);		
		$file = $upload_directory . "import.csv";
		if (move_uploaded_file($_FILES['the_file']['tmp_name'], $file)) {	// If file uploaded OK
			if (strlen(filesize($file)) < 2000000) {
				$filename = $file;
				$errmsg = "";
				} else {
				$filename = NULL;
				$errmsg = "Attached file is too large!";
				}
		} else {
			$errmsg = "Could not proces file";
		}
	}

	$field_names = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member`"; 
	$result = mysql_query($query);
	$i = 1;
	$numfields = mysql_num_fields($result);
	while ($i < $numfields) {
		$meta = mysql_fetch_field($result, $i);
		$label[$i][0] = get_field_label('defined_fields', $i);
		$label[$i][1] = "frm_field" . $i;
		$label[$i][2] = get_field_type('member', $i);
		$label[$i][3] = get_field_name('member',$i);
		$i++;
	}
	
	$teams = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]team`"; 
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$teams[$row['id']] = $row['name'];
		}
		
	$memtypes = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_types`"; 
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$memtypes[$row['id']] = $row['name'];
		}
		
	$memstats = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_status`"; 
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$memstats[$row['id']] = $row['status_val'];
		}

	$i = 1;
	$row = 0;
	$the_arr = array();
	$col_names = array();
	$titles = "";

	if (($handle = fopen("./tmp_uploads/import.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$num = count($data);
			if($row==0) {
				for($f=0; $f < $num; $f++) {
					$col_names[$f] = $data[$f];
					}	
				} else {		
				for ($c=0; $c < $num; $c++) {
					$thedata = trim( preg_replace( '/\s+/', ' ', $data[$c] ) );
					$the_arr[$row-1]["$col_names[$c]"] = $thedata;
				}
			}
			$row++;		
		}
		fclose($handle);
	}
?>
	<BODY style='background-color: #EFEFEF;'>
	<CENTER>
	<DIV id='topbar' style='position: fixed; top: 0px; left: 0px; margin-left: 5%; font-size: 14px; z-index: 999; width: 90%; background-color: #DEDEDE; border: 2px outset #CECECE;'>
		<DIV id='buttonbar' style='float: right;'>
			<SPAN class = 'plain' style='border: 0px; background-color: #DEDEDE;'>FORM CONTROLS&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>
			<SPAN ID = 'can_but' class = 'plain' style='text-align: left;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?><IMG style='vertical-align: middle;' src="../img/back.png"/></SPAN>
			<SPAN ID = 'sub_but' class = 'plain' style='text-align: left;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['theForm'].submit();"><?php print get_text('Submit');?><IMG style='vertical-align: middle;' src="../img/save.png"/></SPAN>			
		</DIV>
	</DIV>	
	<DIV ID='outer' style='width: 100%; align: center; position: relative; top: 50px; left: 0px; z-index: 998;'>
		<DIV style='position: relative; top 0%; width: 80%; height: auto; text-align: center; background-color: #DEDEDE; color: #000000; font-weight: bold; border: 2px outset #FFFFFF; padding: 20px;'>
			<DIV>Number of rows in original file is <?php print $row;?>&nbsp;&nbsp;&nbsp;Number of columns in original file is <?php print $num;?></DIV>
			<DIV><B>Columns from original file are</B></DIV><BR />
			<DIV style='text-align: center; font-size: 0.9em; padding: 5px; max-height: 50px; overflow-y: scroll;'>
<?php
				foreach($col_names as $thename) {
					print "<DIV style='width: auto; padding: 5px; background-color: #EFEFEF; color: #000000; border: 1px outset #FFFFFF; display: inline-block; font-weight: normal; margin-left: 5px;'>" . $thename . "</DIV>";
					}
?>
			</DIV>
		</DIV>
		<DIV style='position: relative; top: 20px; width: 60%; max-height: 350px; overflow-y: scroll; border: 2px outset #FFFFFF; background-color: #FEF7D6; padding: 20px;'>
			<FORM NAME='theForm' METHOD='post' ACTION="<?php print basename(__FILE__);?>" />

			<TABLE>
				<TR>
					<TH class='heading' style='text-align: left;'>Tickets MDB Field</TH>
					<TH class='heading' style='text-align: left;'>CSV Field</TH>
					<TH class='heading' style='text-align: left;'>Other Value</TH>
				</TR>
<?php
				$i = 1;
				foreach($label as $val) {
					if($val[0] != "Not Used" && $val[0] != "Updated By" && $val[0] != "Updated" && $val[0] != "IP Address" && $val[0] != "Picture") {
						if($i == 3) {
							print "<TR><TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>FIELD " . $i . " " . $val[0] . "</TD>";
?>
								<TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>
									<SELECT ID='ic_col_<?php print $i;?>' NAME='ic_col[<?php print $val[1];?>]' onChange='do_hide_val(this.id, "<?php print $i;?>");'>
										<OPTION style='font-size: 100%;' VALUE=999999 SELECTED>Select one</OPTION>
<?php
										for($b=0; $b < $num; $b++) {
?>
											<OPTION style='font-size: 100%;' VALUE='<?php print $col_names[$b];?>'><?php print $col_names[$b];?></OPTION>
<?php
											}
?>
									</SELECT>
								</TD>
								<TD id='isblank_<?php print $i;?>' style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>							
									<SELECT id='def_val[<?php print $val[1];?>]' NAME='def_val[<?php print $val[1];?>]'>
										<OPTION style='font-size: 100%;' VALUE=0 SELECTED>Select one</OPTION>
<?php
										foreach($teams as $key => $theTeam) {
?>
											<OPTION style='font-size: 100%;' VALUE=<?php print $key;?>><?php print $theTeam;?></OPTION>
<?php
											}
?>
									</SELECT>
								</TD>
							</TR>
<?php
							} elseif($i == 5) {
							print "<TR><TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>FIELD " . $i . " " . $val[0] . "</TD>";
?>
								<TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>&nbsp;</TD>
								<TD id='isblank_<?php print $i;?>' style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>&nbsp;								
								</TD>
							</TR>
<?php
							} elseif($i == 7) {
							print "<TR><TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>FIELD " . $i . " " . $val[0] . "</TD>";
?>
								<TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>
									<SELECT ID='ic_col_<?php print $i;?>' NAME='ic_col[<?php print $val[1];?>]' onChange='do_hide_val(this.id, "<?php print $i;?>");'>
										<OPTION style='font-size: 100%;' VALUE=999999 SELECTED>Select one</OPTION>
<?php
										for($b=0; $b < $num; $b++) {
?>
											<OPTION style='font-size: 100%;' VALUE='<?php print $col_names[$b];?>'><?php print $col_names[$b];?></OPTION>
<?php
											}
?>
									</SELECT>
								</TD>
								<TD id='isblank_<?php print $i;?>' style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>							
									<SELECT id='def_val[<?php print $val[1];?>]' NAME='def_val[<?php print $val[1];?>]'>
										<OPTION style='font-size: 100%;' VALUE=0 SELECTED>Select one</OPTION>
<?php
										foreach($memtypes as $key => $theType) {
?>
											<OPTION style='font-size: 100%;' VALUE=<?php print $key;?>><?php print $theType;?></OPTION>
<?php
											}
?>
									</SELECT>
								</TD>
							</TR>
<?php								
							} elseif($i == 21) {
							print "<TR><TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>FIELD " . $i . " " . $val[0] . "</TD>";
?>
								<TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>
									<SELECT ID='ic_col_<?php print $i;?>' NAME='ic_col[<?php print $val[1];?>]' onChange='do_hide_val(this.id, "<?php print $i;?>");'>
										<OPTION style='font-size: 100%;' VALUE=999999 SELECTED>Select one</OPTION>
<?php
										for($b=0; $b < $num; $b++) {
?>
											<OPTION style='font-size: 100%;' VALUE='<?php print $col_names[$b];?>'><?php print $col_names[$b];?></OPTION>
<?php
											}
?>
									</SELECT>
								</TD>
								<TD id='isblank_<?php print $i;?>' style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>							
									<SELECT id='def_val[<?php print $val[1];?>]' NAME='def_val[<?php print $val[1];?>]'>
										<OPTION style='font-size: 100%;' VALUE=0 SELECTED>Select one</OPTION>
<?php
										foreach($memstats as $key => $theStat) {
?>
											<OPTION style='font-size: 100%;' VALUE=<?php print $key;?>><?php print $theStat;?></OPTION>
<?php
											}
?>
									</SELECT>
								</TD>
							</TR>
<?php								
							} else {
							print "<TR><TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>FIELD " . $i . " " . $val[0] . "</TD>";
?>
								<TD style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>
									<SELECT ID='ic_col_<?php print $i;?>' NAME='ic_col[<?php print $val[1];?>]' onChange='do_hide_val(this.id, "<?php print $i;?>");'>
										<OPTION style='font-size: 100%;' VALUE=999999 SELECTED>Select one</OPTION>
<?php
										for($b=0; $b < $num; $b++) {
?>
											<OPTION style='font-size: 100%;' VALUE='<?php print $col_names[$b];?>'><?php print $col_names[$b];?></OPTION>
<?php
											}
?>
									</SELECT>
								</TD>
								<TD id='isblank_<?php print $i;?>' style='background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>
<?php
									switch($val[2]) {
										case "STRING":
										case "VAR_STRING":
?>									
											<INPUT MAXLENGTH="48" SIZE="24" TYPE="text" NAME='def_val[<?php print $val[1];?>]' VALUE="" />
<?php
											break;
										case "DATETIME":
											generate_date_dropdown("frm_field" . $i,"",0, false);
											break;
										case "LONG":
										case "LONGLONG":
											$fieldlength = get_field_size('member', $i);
?>									
											<INPUT MAXLENGTH="<?php print $fieldlength;?>" SIZE="<?php print $fieldlength;?>" TYPE="text" NAME='def_val[<?php print $val[1];?>]' VALUE="" />
<?php
											break;									
									
										case "ENUM":
?>
											<SELECT id='def_val[<?php print $val[1];?>]' NAME='def_val[<?php print $val[1];?>]'>
												<OPTION style='font-size: 100%;' VALUE=0 SELECTED>Select one</OPTION>
<?php
												$theVals = get_enum_vals('member', "field" . $i);
												foreach($theVals as $theVal) {
?>
													<OPTION style='font-size: 100%;' VALUE='<?php print $theVal;?>'><?php print $theVal;?></OPTION>
<?php
													}
?>
											</SELECT>
<?php
											break;
										case "DOUBLE":
?>
											<INPUT MAXLENGTH="48" SIZE="24" TYPE="text" NAME='def_val[<?php print $val[1];?>]' VALUE=0.0 />
<?php
											break;
										case "BLOB":
?>
											<INPUT type='hidden' NAME='def_val[<?php print $val[1];?>]' value="">
											&nbsp;
<?php
											break;
										default:
?>
											<INPUT type='hidden' NAME='def_val[<?php print $val[1];?>]' value="">
											&nbsp;
<?php
										}	
?>
								</TD>
							</TR>
<?php
							}
						}
					$i++;
					}
?>
			</TABLE>
<?php 
			$i = 1;
			foreach($label as $val) {
				if($val[0] == "Not Used" || $val[0] == "Picture") {
					switch($val[2]) {
						case "DATETIME":
?>
							<INPUT type='hidden' NAME='ic_col[<?php print $val[1];?>]' VALUE=999999>
							<INPUT type='hidden' NAME='def_val[<?php print $val[1];?>]' value="">
							<INPUT type='hidden' NAME='frm_year_frm_field<?php print $i;?>' VALUE="0000">
							<INPUT type='hidden' NAME='frm_month_frm_field<?php print $i;?>' VALUE="00">
							<INPUT type='hidden' NAME='frm_day_frm_field<?php print $i;?>' VALUE="00">
<?php
							break;
							
						default:
?>
							<INPUT type='hidden' NAME='ic_col[<?php print $val[1];?>]' VALUE=999999>
							<INPUT type='hidden' NAME='def_val[<?php print $val[1];?>]' value="">
<?php
						}
					}
				$i++;
				}
?>
			<INPUT type='hidden' name='stage' value=2>
			</FORM>
		</DIV>
	</DIV>
	</CENTER>
	<FORM NAME='can_Form' METHOD="post" ACTION = "../config.php"></FORM>
	</BODY>
<?php
} else {	//	For errors
?>
	<BODY style='background-color: #EFEFEF;'>
	<CENTER>
	<DIV id='outer' style='position: absolute; width: 100%;'>
		<DIV style='position: relative; top: 5%; width: 60%; max-height: 350px; border: 2px outset #FFFFFF; background-color: #FEF7D6; padding: 20px;'>
		Error calling the file
		<A HREF='../config.php'>Back to Config</A>
		</DIV>
	</DIV>
	</CENTER>
	</BODY>
<?php 
}
?>
</HTML>