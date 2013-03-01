<?php
/*
6/7/12	New file, allows upload of existing csv file with member details on, processing of this and adding to DB.
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
require_once('../incs/functions.inc.php');
function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
		}
	return $the_ret;
	}
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
	<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT type="text/javascript">
	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}
		
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}
		
	function do_plain (the_id) {				// 8/21/10
		CngClass(the_id, 'plain');
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
	<BODY>
		<DIV id='banner' class='heading' style='font-size: 20px; position: absolute: top: 5%; width: 95%; border: 1px outset #000000; text-align: center;'>Upload requests as a csv file
			<SPAN ID='close_but' CLASS='plain' style='float: right;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.close();">Close</SPAN>
		</DIV><BR /><BR />
		<DIV id='outer' style='position: relative; top: 1%; left: 1%; width: 95%; text-align: center; margin: 10px;'>
			<DIV id='inner' style='position: relative; top: 20%; left: 20%; height: 30%; width: 60%;'>
				<FORM enctype="multipart/form-data" METHOD="POST" NAME= "fileForm" ACTION="<?php print basename(__FILE__);?>" />
				<INPUT TYPE="file" NAME="the_file" SIZE="48" VALUE="" style='cursor: pointer;'>
				<INPUT type='hidden' name='stage' value=1>
				</FORM>
			</DIV><BR /><BR />
			<DIV id='controls' style='position: relative; width: 60%; top: 20%; left: 20%; text-align: center;'>
				<SPAN ID = 'sub_but' class = 'plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['fileForm'].submit();"><?php print get_text('Submit');?></SPAN>				
			</DIV>
		</DIV>
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

	$who = $_SESSION['user_id'];
	$from = $_SERVER['REMOTE_ADDR'];			
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));				
	for($z = 0; $z < count($the_arr); $z++) {
		$the_contact = quote_smart(trim(get_user_name($who)));
		$the_street = ($_POST['ic_col']['frm_street'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_street']] : "Not Imported";
		$the_city = ($_POST['ic_col']['frm_city'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_city']] : "Not Imported";	
		$the_state = ($_POST['ic_col']['frm_state'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_state']] : "Not Imported";	
		$the_name = ($_POST['ic_col']['frm_the_name'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_the_name']] : 1;		
		$the_phone = ($_POST['ic_col']['frm_phone'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_phone']] : "Not Imported";	
		$the_scope = ($_POST['ic_col']['frm_scope'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_scope']] : "Not Imported";	
		$the_description = ($_POST['ic_col']['frm_description'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_description']] : "Not Imported";	
		$the_comments = ($_POST['ic_col']['frm_comments'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_comments']] : "Not Imported";	
		$the_request_date = ($_POST['ic_col']['frm_request_date'] != '999999') ? $the_arr[$z][$_POST['ic_col']['frm_request_date']] : $now;			
//		$the_request_date =  $now;
		$the_status = 'Open';
		$the_requester = $who;
		$by = $who;
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
					`contact`, 
					`street`, 
					`city`, 
					`state`, 
					`the_name`, 
					`phone`, 
					`rec_facility`, 
					`scope`, 
					`description`, 
					`comments`, 
					`request_date`, 
					`status`, 
					`accepted_date`,
					`declined_date`, 
					`resourced_date`, 
					`completed_date`, 
					`closed`, 
					`requester`, 
					`_by`, 
					`_on`, 
					`_from` 
					) VALUES (
					" . $the_contact . ",
					" . quote_smart(trim($the_street)) . ",	
					" . quote_smart(trim($the_city)) . ",	
					" . quote_smart(trim($the_state)) . ",	
					" . quote_smart(trim($the_name)) . ",
					" . quote_smart(trim($the_phone)) . ",		
					0,	
					" . quote_smart(trim($the_scope)) . ",	
					" . quote_smart(trim($the_description)) . ",					
					" . quote_smart(trim($the_comments)) . ",		
					" . quote_smart(trim($the_request_date)) . ",
					'Open',
					NULL,
					NULL,
					NULL,
					NULL,
					NULL,
					" . $who . ",
					" . $who . ",				
					'" . $now . "',
					'" . $from . "')";
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		}
		$addrs = notify_newreq($_SESSION['user_id']);		// returns array of adddr's for notification, or FALSE
		if ($addrs) {				// any addresses?
			$to_str = implode("|", $addrs);
			$smsg_to_str = "";
			$subject_str = "New " . get_text('Service User') . " Requests";
			$text_str = "New requests have been loaded by \n\n" . get_user_name($_SESSION['user_id']) . "\n\nDated " . $now . "\n\nPlease log on to Tickets and check"; 
			do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);
			}				// end if/else ($addrs)			
?>
	<BODY style='background-color: #EFEFEF;'>
	<CENTER>
	<DIV id='outer' style='position: absolute; width: 100%;'>
		<DIV style='position: relative; top: 5%; width: 60%; max-height: 350px; border: 2px outset #FFFFFF; background-color: #FEF7D6; padding: 20px;'>
		Data successfully imported.<BR />
		<SPAN ID = 'close_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.close();">Finish</SPAN>			
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
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests`"; 
	$result = mysql_query($query);
	$i = 1;
	while ($i < mysql_num_fields($result)) {
		$meta = mysql_fetch_field($result, $i);
		$label[$i][0] = $meta->name;
		$label[$i][1] = "frm_" . $meta->name;	
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
	<BODY>
	<CENTER>
	<DIV id='banner' class='heading' style='font-size: 20px; position: absolute: top: 5%; width: 95%; border: 1px outset #000000; text-align: center;'>Upload requests as a csv file</DIV><BR /><BR />
	<DIV id='outer' style='position: relative; top: 1%; left: 1%; width: 95%; text-align: center; margin: 10px;'>	
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
		<DIV style='position: relative; top: 20px; left: 10%; width: 60%; max-height: 200px; overflow-y: auto; border: 2px outset #FFFFFF; background-color: #FEF7D6; padding: 20px;'>
			<FORM NAME='theForm' METHOD='post' ACTION="<?php print basename(__FILE__);?>" />

			<TABLE>
<?php
				foreach($label as $val) {
				if(($val[0] == "accepted_date") || ($val[0] == "declined_date") || ($val[0] == "resourced_date") || ($val[0] == "completed_date") || ($val[0] == "closed") || ($val[0] == "requester") || ($val[0] == "ticket_id") || ($val[0] == "_by") || ($val[0] == "_on") || ($val[0] == "_from")) {
					} else {
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
	<BR /><BR />
	<DIV id='controls' style='position: relative; width: 60%; top: 20%; left: 20%; text-align: center;'>
		<SPAN ID = 'can_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?></SPAN>
		<SPAN ID = 'sub_but' class = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="document.forms['theForm'].submit();"><?php print get_text('Submit');?></SPAN>			
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
		<A HREF='import_requests.php'>Try Again</A>
		<A HREF='#' onClick='window.close();'>Cancel</A>		
		</DIV>
	</DIV>
	</CENTER>
	</BODY>
<?php 
}
?>
</HTML>