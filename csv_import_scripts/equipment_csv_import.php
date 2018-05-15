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
			<SPAN ID = 'can_but' class = 'plain' style='text-align: left;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?> <IMG style='vertical-align: middle; float: right;' src="../img/back.png"/></SPAN>
			<SPAN ID = 'sub_but' class = 'plain' style='text-align: left;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['fileForm'].submit();"><?php print get_text('Submit');?> <IMG style='vertical-align: middle; float: right;' src="../img/save.png"/></SPAN>			
		</DIV>
		<BR />
		<BR />			
		<DIV class='tablehead' style='width: 100%; float: right;'><b>Import Equipment from CSV File</b></DIV>							
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
	$from = $_SERVER['REMOTE_ADDR'];			
	for($z = 0; $z < count($the_arr); $z++) {
		$equipment_name = ($_POST['ic_col']['frm_equipment_name'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_equipment_name']] : NULL;
		$description = ($_POST['ic_col']['frm_description'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_description']] : NULL;
		$spec = ($_POST['ic_col']['frm_spec'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_spec']] : NULL;
		$serial = ($_POST['ic_col']['frm_serial'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_serial']] : NULL;		
		$condition = ($_POST['ic_col']['frm_condition'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_condition']] : "New";
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]equipment_types` 
				(`equipment_name`,
				`description`, 				
				`spec`, 
				`serial`, 				
				`condition`)
			VALUES (" . 
				quote_smart(trim($equipment_name)) . "," .
				quote_smart(trim($description)) . "," .
				quote_smart(trim($spec)) . "," .
				quote_smart(trim($serial)) . "," .				
				quote_smart(trim($condition)) . ");";
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
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]equipment_types`"; 
	$result = mysql_query($query);
	$i = 1;
	$numfields = mysql_num_fields($result);
	while ($i < $numfields) {
		$meta = mysql_fetch_field($result);
		$label[$i][0] = $meta->name;
		$label[$i][1] = "frm_" . $meta->name;
		$label[$i][2] = $meta->type;
		$i++;
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
			<SPAN ID = 'can_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?><IMG style='vertical-align: middle;' src="../img/back.png"/></SPAN>
			<SPAN ID = 'sub_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['theForm'].submit();"><?php print get_text('Submit');?><IMG style='vertical-align: middle;' src="../img/save.png"/></SPAN>			
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
<?php
				foreach($label as $val) {
				if($val != "Not Used") {
					print "<TR><TD style='width: 50%; background-color: #707070; color: #FFFFFF; font-weight: bold; font-size: 100%;'>" . $val[0] . "</TD>";
?>
						<TD style='width: 50%;'>
						<SELECT NAME='ic_col[<?php print $val[1];?>]'>
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
						</TR>
<?php
					}
			}
?>
			</TABLE>
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