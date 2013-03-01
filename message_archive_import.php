<?php
/*
6/7/12	New file, allows upload of existing csv file with member details on, processing of this and adding to DB.
*/

require_once('./incs/functions.inc.php');
require_once('./incs/mysql.inc.php');

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
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT type="text/javascript">
function $() {									// 1/21/09
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
		
function ck_frames() {		//  onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();										// 1/21/09
		parent.upper.do_day_night("<?php print $_SESSION['day_night'];?>")
		}
	}		// end function ck_frames()
	
function go_there (where, the_id) {		//
	document.go.action = where;
	document.go.submit();
	}				// end function go there ()	
	
function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}
</SCRIPT>
</HEAD>
<?php

if(empty($_POST)) {	//	Upload a file for import
?>
	<BODY onLoad = "ck_frames();">
	<CENTER>
	<DIV id='outer' style='position: absolute; width: 100%; text-align: center; margin: 10px;'>
		<DIV id='banner' class='heading' style='font-size: 28px; position: relative: top: 5%; width: 100%; border: 1px outset #000000;'>MESSAGE ARCHIVING</DIV><BR /><BR />
		<DIV id='leftcol' style='position: relative; left: 30%; top: 5%; width: 40%; float: left; border: 1px outset #000000;'>
			<FORM enctype="multipart/form-data" METHOD="POST" NAME= "fileForm" ACTION="<?php print basename(__FILE__);?>" />
			<TABLE style='width: 100%;'>
				<TR class='heading'>
					<TH class='heading' COLSPAN=99 style='font-size: 18px;'>IMPORT MESSAGE ARCHIVE</TH>
				</TR>	
				<TR class='spacer'>
					<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
				</TR>			
				<TR class='even'>	
					<TD class='td_label' style='text-align: left;'><?php print get_text('Select Archive File');?></TD>
					<TD class='td_data'><INPUT TYPE="file" NAME="the_file" SIZE="48" VALUE="" style='cursor: pointer;'></TD>
				</TR>	
				<TR class='spacer'>
					<TD COLSPAN=99 class='spacer'>&nbsp;</TD>
				</TR>
				<TR class='even'>	
					<TD COLSPAN=2>
					<BR /><SPAN id='sub_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "document.forms['fileForm'].submit();">Next</SPAN><BR /><BR />				
					</TD>
				</TR>				
			</TABLE>
			<INPUT type='hidden' name='stage' value=1>
			</FORM>
		</DIV>
	</DIV>
	</CENTER>
	<FORM NAME='can_Form' METHOD="post" ACTION = "../config.php"></FORM>			
	</BODY>
<?php
} elseif((isset($_POST)) && ($_POST['stage'] == 1)) {	//	Process the file
	if (isset($_FILES['the_file'])) {
		$errmsg = "";	
		$upload_directory = "./tmp_uploads";
		if (!(file_exists($upload_directory))) {				
			mkdir ($upload_directory, 0777);
			} else {
			chmod($upload_directory, 0777);
			}
		$file = $upload_directory . "/import.csv";
		if (move_uploaded_file($_FILES['the_file']['tmp_name'], $file)) {	// If file uploaded OK
			if (strlen(filesize($file)) < 2000000) {
				$filename = $file;
				$errmsg = "";
				$i = 1;
				$row = 0;
				$the_arr = array();
				$col_names = array();
				$titles = "";	
				if (($handle = fopen($filename, "r")) !== FALSE) {
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
				dump($the_arr);
				
				$query = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]messages_imported`";
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);	
				
				for($z = 0; $z < count($the_arr); $z++) {
					if($z==0) {
						$startdate = $the_arr[$z]['date'];
						}
					$msg_type = $the_arr[$z]['msg_type'];
					$message_id = $the_arr[$z]['message_id'];
					$resp_id = $the_arr[$z]['resp_id'];
					$recipients = $the_arr[$z]['recipients'];
					$from_address = $the_arr[$z]['from_address'];
					$fromname = $the_arr[$z]['fromname'];
					$subject = $the_arr[$z]['subject'];
					$message = $the_arr[$z]['message'];
					$status = $the_arr[$z]['status'];
					$date = date("Y-m-d H:i:s", strtotime($the_arr[$z]['date']));
					print $date . "<BR />";
					$read_status = $the_arr[$z]['read_status'];
					$readby = $the_arr[$z]['readby'];
					$delivered = $the_arr[$z]['delivered'];
					$delivery_status = $the_arr[$z]['delivery_status'];
					$by = $the_arr[$z]['_by'];
					$from = $the_arr[$z]['_from'];
					$on = mysql_format_date(strtotime($the_arr[$z]['_on']));
					
					$query = "INSERT INTO `$GLOBALS[mysql_prefix]messages_imported` 
							(`msg_type`,
							`message_id`, 
							`resp_id`, 
							`recipients`, 
							`from_address`, 				
							`fromname`, 
							`subject`, 
							`message`, 
							`status`, 
							`date`, 
							`read_status`, 
							`readby`, 
							`delivered`, 
							`delivery_status`, 
							`_by`, 
							`_on`, 						
							`_from`)
						VALUES (" . 
							quote_smart(trim($msg_type)) . "," .
							quote_smart(trim($message_id)) . "," .
							quote_smart(trim($resp_id)) . "," .	
							quote_smart(trim($recipients)) . "," .
							quote_smart(trim($from_address)) . "," .					
							quote_smart(trim($fromname)) . "," .	
							quote_smart(trim($subject)) . "," .				
							quote_smart(trim($message)) . "," .	
							quote_smart(trim($status)) . "," .					
							quote_smart(trim($date)) . "," .
							quote_smart(trim($read_status)) . "," .	
							quote_smart(trim($readby)) . "," .						
							quote_smart(trim($delivered)) . "," .	
							quote_smart(trim($delivery_status)) . "," .
							quote_smart(trim($by)) . "," .	
							quote_smart(trim($on)) . "," .
							quote_smart(trim($from)) . ");";
					print $query . "<BR />";
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
					print "message added<BR />";
					$enddate = $the_arr[$z]['date'];
					}					
				} else {
				$filename = NULL;
				$errmsg = "Attached file is too large!";
				}
		} else {
			$errmsg = "Could not proces file";
			print $errmsg . "<BR />";
		}
	}
?>
	<BODY onLoad = "ck_frames();">
	<CENTER>
	<DIV id='outer' style='position: absolute; width: 100%; text-align: center; margin: 10px;'>
		<DIV id='banner' class='heading' style='font-size: 28px; position: relative: top: 5%; width: 100%; border: 1px outset #000000;'>MESSAGE ARCHIVING</DIV><BR /><BR />
		<DIV id='leftcol' style='position: relative; left: 30%; top: 5%; width: 40%; float: left; border: 1px outset #000000;'>
		Messages successfully imported.<BR />
		Start Date is <?php print $startdate;?><BR />
		End Date is <?php print $enddate;?><BR /><BR /><BR />
		<A class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" HREF='config.php'>Back to Config</A><BR /><BR />
		</DIV>
	</DIV>
	</CENTER>
	</BODY>
<?php 		
	exit();

} else {	//	For errors
?>
	<BODY onLoad = "ck_frames();">
	<CENTER>
		<DIV id='banner' class='heading' style='font-size: 28px; position: relative: top: 5%; width: 100%; border: 1px outset #000000;'>MESSAGE ARCHIVING</DIV><BR /><BR />
		<DIV id='leftcol' style='position: relative; left: 30%; top: 5%; width: 40%; float: left; border: 1px outset #000000;'>
		Error calling the file<BR /><BR /><BR />
		<A class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" HREF='config.php'>Back to Config</A><BR /><BR />
		</DIV>
	</DIV>
	</CENTER>
	</BODY>
<?php 
}
?>
</HTML>