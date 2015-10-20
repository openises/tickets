<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
if (empty($_SESSION)) {
	header("Location: index.php");
	}

require_once('incs/functions.inc.php');
/*
*/


$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id` ASC"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$select_r = "<SELECT NAME='frm_responder_id'><OPTION VALUE=0 SELECTED>Select Responder</OPTION>";
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$sel = ((!empty($_GET)) && (isset($_GET['responder_id'])) && ($_GET['responder_id'] != 0) && ($_GET['responder_id'] == $row['id'])) ? "SELECTED" : "";
	$select_r .= "<OPTION VALUE=" . $row['id'] . " " . $sel . ">" . $row['name'] . " - " . $row['handle'] . "</OPTION>";
	}
$select_r .= "</SELECT>";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` ORDER BY `id` ASC"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$select_t = "<SELECT NAME='frm_ticket_id'><OPTION VALUE=0 SELECTED>Select Ticket</OPTION>";
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$sel = ((!empty($_GET)) && (isset($_GET['ticket_id'])) && ($_GET['ticket_id'] != 0) && ($_GET['ticket_id'] == $row['id'])) ? "SELECTED" : "";
	$select_t .= "<OPTION VALUE=" . $row['id'] . " " . $sel . ">" . $row['scope'] . "</OPTION>";
	}
$select_t .= "</SELECT>";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `id` ASC"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$select_f = "<SELECT NAME='frm_facility_id'><OPTION VALUE=0 SELECTED>Select Facility</OPTION>";
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$sel = ((!empty($_GET)) && (isset($_GET['facility_id'])) && ($_GET['facility_id'] != 0) && ($_GET['facility_id'] == $row['id'])) ? "SELECTED" : "";
	$select_f .= "<OPTION VALUE=" . $row['id'] . " " . $sel . ">" . $row['name'] . " - " . $row['handle'] . "</OPTION>";
	}
$select_f .= "</SELECT>";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `level` = 7 ORDER BY `id` ASC"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$select_u = "<SELECT NAME='frm_user_id'><OPTION VALUE=0 SELECTED>Select Portal User</OPTION>";
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$sel = ((!empty($_GET)) && (isset($_GET['portaluser'])) && ($_GET['portaluser'] != 0) && ($_GET['portaluser'] == $row['id'])) ? "SELECTED" : "";
	$select_u .= "<OPTION VALUE=" . $row['id'] . " " . $sel . ">" . $row['user'] . " - " . $row['name_f'] . " " . $row['name_l'] . "</OPTION>";
	}
$select_u .= "</SELECT>";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets CAD - File Upload</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<meta http-equiv=”X-UA-Compatible” content=”IE=EmulateIE7" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<STYLE type="text/css">
	.hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em; text-decoration: none;float: left; background-color: #DEE3E7; font-weight: bolder; cursor: pointer; }
	.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em; text-decoration: none; float: left; background-color: #EFEFEF; font-weight: bolder; cursor: pointer; }	
  	</STYLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<script src="./js/misc_function.js" type="text/javascript"></script>
	<script type="text/javascript">
	
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
		
	function $() {
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

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function to_str(instr) {
		function ord( string ) {
		    return (string+'').charCodeAt(0);
			}

		function chr( ascii ) {
		    return String.fromCharCode(ascii);
			}
		function to_char(val) {
			return(chr(ord("A")+val));
			}

		var lop = (instr % 26);													// low-order portion, a number
		var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
		return hop+to_char(lop);
		}

	var starting = false;

	function isNull(val) {
		return val === null;
		}

	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && $) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && $) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}

	var starting = false;

	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_file.value.trim()=="")													{errmsg+="File selection is required.\n";}
		if (theForm.frm_title.value.trim()=="")													{errmsg+="File title is required.\n";}
		if ((theForm.frm_type.value.trim()==0) && 
			((theForm.frm_ticket_id.value.trim()==0) && (theForm.frm_responder_id.value.trim()==0) && (theForm.frm_facility_id.value.trim()==0))
			) {
				errmsg+="The file either needs to associated with a Ticket,\n Responder or Facility or set as a general Tickets or Portal type.\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			theForm.submit();
			}
		}				// end function va lidate(theForm)		
		
	function resizeIt() {
		var LwinW;
		var RwinW;
		if (document.body && document.body.offsetWidth) {
			LwinW = document.body.offsetWidth * .6;
			RwinW = document.body.offsetWidth * .33;
		}
		if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
			LwinW = document.documentElement.offsetWidth * .6;
			RwinW = document.documentElement.offsetWidth * .33;
		}
		if (window.innerWidth && window.innerHeight) {
			LwinW = window.innerWidth * .6;
			RwinW = window.innerWidth * .33;
		}
		$('left_col').style.width = LwinW + "px";
		$('list').style.width = LwinW + "px";		
		$('right_col').style.width = RwinW + "px";
		$('map').style.width = RwinW + "px";	
		}
		
	function show_portaluser(id) {
		if(id==2) {
			$('portal_user').style.display="";
			} else {
			$('portal_user').style.display="none";	
			}
		}
		
	function close_win() {
		try {
			window.opener.get_files();
			} catch (err) {
			var error = "error";
			}
		window.close(); 
		}
		
	</SCRIPT>
	</HEAD>
	<BODY>
<?php
if(!empty($_POST)) {	//	$_POST data exists, process the file
	$print = "File Uploaded OK<BR />";
	
//	Has the file been uploaded correctly
	
	if (isset($_FILES['frm_file'])) {
	
//	File blacklist check
	
	$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py"); 
	foreach ($blacklist as $file) { 
		if(preg_match("/$file\$/i", $_FILES['frm_file']['name'])) { 
?>
			<DIV style='width: 100%; text-align: center;'><BR /><BR /><BR /><BR /><BR /><BR />File Type disallowed
			<BR /><BR /><A HREF='file_upload.php' id='retry_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"/>Retry</A><BR /><BR />
			<SPAN id='close_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.close()" />Close</SPAN>
			</DIV>
<?php
			exit; 
			} 
		}	
//	end of blacklist check
		$exists = false;
		$existing_file = "";
		$upload_directory = "./files/";
		if (!(file_exists($upload_directory))) {				
			mkdir ($upload_directory, 0770);
			}
		chmod($upload_directory, 0770);	
		$filename = rand(1,999999);
		$realfilename = $_FILES["frm_file"]["name"];
		$file = $upload_directory . $filename;

//	Does the file already exist in the files table		

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `orig_filename` = '" . $realfilename . "'";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);	
		if(mysql_affected_rows() == 0) {	//	file doesn't exist already
			if (move_uploaded_file($_FILES['frm_file']['tmp_name'], $file)) {	// If file uploaded OK
				if (strlen(filesize($file)) < 20000000) {
					$print .= "File Size OK<BR />";
					} else {
					$print = "Attached file is too large!<BR />";
					}
				} else {
				$print = "Error uploading file<BR />";
				}
			} else {
			$row = stripslashes_deep(mysql_fetch_assoc($result));			
			$exists = true;
			$existing_file = $row['filename'];	//	get existing file name
			}
			
// end of dupe test

//	Insert file details in the DB

		$by = $_SESSION['user_id'];
		$from = $_SERVER['REMOTE_ADDR'];	
		$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
		$filename = ($existing_file == "") ? $filename : $existing_file;	//	if existing file, use this file and write new db entry with it.
		$query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]files` (
				`title` , `filename` , `orig_filename`, `ticket_id` , `responder_id` , `facility_id`, `type`, `filetype`, `_by`, `_on`, `_from`
			) VALUES (
				'" . $_POST['frm_title'] . "', '" . $filename . "', '" . $realfilename . "', " . $_POST['frm_ticket_id'] . ", " . $_POST['frm_responder_id'] . ",
				" . $_POST['frm_facility_id'] . ", " . $_POST['frm_type'] . " , '" . $_FILES['frm_file']['type'] . "', $by, '" . $now . "', '" . $from . "'
			)";
			
		$result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		$last_id = mysql_insert_id();
		if($result_insert) {	//	is the database insert successful
			$print .= "Inserted in Database OK<BR />";
			if($_POST['frm_type'] == 2) {
				$query_user_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]files_x` (`file_id` , `user_id`) VALUES (" . $last_id . ", " . $_POST['frm_user_id'] . ")";
				$result_user_insert	= mysql_query($query_user_insert) or do_error($query_user_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);	
				if($result_user_insert) {
					$print .= "Inserted Portal User details in Database OK<BR />";
?>
					<DIV style='width: 100%; text-align: center;'><BR /><BR /><BR /><BR /><BR /><BR /><?php print $print;?>
					<BR /><BR /><SPAN id='close_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="close_win();" />Finish</SPAN>
					</DIV>
<?php
					} else {
					$print .= "Problem with inserting Portal User details in database<BR />";
					}
				} else {
?>
				<DIV style='width: 100%; text-align: center;'><BR /><BR /><BR /><BR /><BR /><BR /><?php print $print;?>
				<BR /><BR /><SPAN id='close_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="close_win()" />Finish</SPAN>
				</DIV>
<?php				
				}
			} else {	//	problem with the database insert
			$print .= "Database Error<BR />";
?>
			<DIV style='width: 100%; text-align: center;'><BR /><BR /><BR /><BR /><BR /><BR /><?php print $print;?>
			<BR /><BR /><A HREF='file_upload.php' id='retry_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"/>Retry</A><BR /><BR />
			<SPAN id='close_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="close_win()" />Close</SPAN>
			</DIV>
<?php		
			}
		} else {	// Problem with the file upload
?>
			<DIV style='width: 100%; text-align: center;'><BR /><BR /><BR /><BR /><BR /><BR />File upload error
			<BR /><BR /><A HREF='file_upload.php' id='retry_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"/>Retry</A><BR /><BR />
			<SPAN id='close_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="close_win()" />Close</SPAN>
			</DIV>
<?php
		}
	} else {	//	$_POST is empty, show the file upload form
	$ticket_id = ((!empty($_GET)) && (isset($_GET['ticket_id'])) && ($_GET['ticket_id'] != 0)) ? intval($_GET['ticket_id']) : false;
	$responder_id = ((!empty($_GET)) && (isset($_GET['responder_id'])) && ($_GET['responder_id'] != 0)) ? intval($_GET['responder_id']) : false;
	$facility_id = ((!empty($_GET)) && (isset($_GET['facility_id'])) && ($_GET['facility_id'] != 0)) ? intval($_GET['facility_id']) : false;
	$the_ticket = ($ticket_id) ? $ticket_id: 0;
	$the_responder = ($responder_id) ? $responder_id: 0;
	$the_facility = ($facility_id) ? $facility_id: 0;
?>
	<FORM NAME="file_frm" ENCTYPE="multipart/form-data" ACTION="file_upload.php" METHOD="POST">
	<DIV style='width: 100%; text-align: center;'><CENTER>
		<DIV style='width: 80%; border: 2px outset #606060; padding: 20px;'>
			<TABLE style='width: 100%;'>
				<TR class='heading'>
					<TD COLSPAN='2' class='heading'>File Upload Form</TD>
				</TR>
				<TR class='odd'>
					<TD COLSPAN='2' class='td_data'>&nbsp;</TD>
				</TR>
				<TR class='even'>
					<TD class='td_label' style='text-align: left;'>Choose a file to upload:</TD>
					<TD class='td_data' style='text-align: left;'><INPUT NAME="frm_file" TYPE="file" /></TD>
				</TR>
				<TR class='odd'>
					<TD class='td_label' style='text-align: left;'>File Name</TD>
					<TD class='td_data' style='text-align: left;'><INPUT NAME="frm_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE=""></TD>
				</TR>
<?php
				if((!$ticket_id) && (!$responder_id) && (!$facility_id)) {
?>
					<TR class='even'>
						<TD class='td_label' style='text-align: left;'>For Ticket
						<TD class='td_data' style='text-align: left;'><?php print $select_t;?></TD>
					</TR>
					<TR class='odd'>
						<TD class='td_label' style='text-align: left;'>For Responder
						<TD class='td_data' style='text-align: left;'><?php print $select_r;?></TD>
					</TR>
					<TR class='even'>
						<TD class='td_label' style='text-align: left;'>For Facility
						<TD class='td_data' style='text-align: left;'><?php print $select_f;?></TD>
					</TR>
<?php
					} else {
?>
					<INPUT TYPE='hidden' NAME='frm_ticket_id' VALUE=<?php print $the_ticket;?>>
					<INPUT TYPE='hidden' NAME='frm_responder_id' VALUE=<?php print $the_responder;?>>
					<INPUT TYPE='hidden' NAME='frm_facility_id' VALUE=<?php print $the_facility;?>>
<?php
					}
				if(($ticket_id) || ($responder_id) || ($facility_id)) {
?>
					<INPUT TYPE='hidden' NAME='frm_type' VALUE=1>
<?php
					} else {
?>
					<TR class='odd'>
						<TD class='td_label' style='text-align: left;'>Available for</TD>
						<TD class='td_data' style='text-align: left;'>
							<SELECT NAME='frm_type' onChange='show_portaluser(this.options[this.selectedIndex].value);'>
								<OPTION VALUE=0 SELECTED>Select File For</OPTION>
								<OPTION VALUE=1>Tickets</OPTION>
								<OPTION VALUE=2>Portal</OPTION>	
							</SELECT>
						</TD>
					</TR>
					<TR class='even' id='portal_user' style='display: none;'>
						<TD class='td_label' style='text-align: left;'>Portal User</TD>
						<TD class='td_data' style='text-align: left;'><?php print $select_u;?></TD>
					</TR>
<?php
					}
?>
				<TR class='odd'>
					<TD COLSPAN='2' class='td_data'>&nbsp;</TD>
				</TR>
				<TR class='odd'>
					<TD COLSPAN='2' class='td_data'>&nbsp;</TD>
				</TR>
			</TABLE>
		</DIV><BR /><BR /><BR /><BR />
		<DIV style='text-align: center; width: 40%;'>
			<SPAN id='sub_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='validate(document.file_frm);'>Submit</SPAN>
			<SPAN id='can_but' class='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="close_win();" />Cancel</SPAN>
		</DIV>
	</CENTER>
	</DIV>
	</FORM>
<?php
	}
?>
</BODY>
</HTML>
	