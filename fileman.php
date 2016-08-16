<?php
/*
9/10/13 - Stored File Manager
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once($_SESSION['fip']);
do_login(basename(__FILE__));
if(!is_administrator() && !is_user()) {
	print "Not Authorised";
	exit();
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets File Manager</TITLE>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE type="text/css">
.hover 	{ text-align: center; margin-left: 4px; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
			  padding: 4px 0.5em;text-decoration: none; background-color: #DEE3E7; font-weight: bolder;}
.plain 	{ text-align: center; margin-left: 4px; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
			  padding: 4px 0.5em;text-decoration: none; background-color: #EFEFEF; font-weight: bolder;}
.wrap_data { width: 200px; background-color: inherit;	font-size: 12px; color: #000000; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none; }
.wrap_label { width: 100px; background-color: #707070; font-size: 12px; color: #FFFFFF; font-weight: bold; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none; }
.tab_row { border: 1px solid #CECECE; width: 300px; }
</STYLE>	
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT>
function ck_frames() {		// onLoad = "ck_frames()"
	}		// end function ck_frames()

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

function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {
	CngClass(the_id, 'plain');
	return true;
	}

function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}
		
function sendRequest(url,callback,postData) {
	var req = createXMLHTTPObject();
	if (!req) return;
	var method = (postData) ? "POST" : "GET";
	req.open(method,url,true);
	if (postData)
		req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	req.onreadystatechange = function () {
		if (req.readyState != 4) return;
		if (req.status != 200 && req.status != 304) {
			return;
			}
		callback(req);
		}
	if (req.readyState == 4) return;
	req.send(postData);
	}

var XMLHttpFactories = [
	function () {return new XMLHttpRequest()	},
	function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
	function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
	function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
	];

function createXMLHTTPObject() {
	var xmlhttp = false;
	for (var i=0;i<XMLHttpFactories.length;i++) {
		try {
			xmlhttp = XMLHttpFactories[i]();
			}
		catch (e) {
			continue;
			}
		break;
		}
	return xmlhttp;
	}

function syncAjax(strURL) {
	if (window.XMLHttpRequest) {						 
		AJAX=new XMLHttpRequest();						 
		} 
	else {																 
		AJAX=new ActiveXObject("Microsoft.XMLHTTP");
		}
	if (AJAX) {
		AJAX.open("GET", strURL, false);														 
		AJAX.send(null);
		return AJAX.responseText;																				 
		} 
	else {
		alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
		return false;
		}																						 
	}
	
function do_delete(id) {
	randomnumber=Math.floor(Math.random()*99999999);
	$('waiting_wrapper').style.display='block';
	$('waiting').innerHTML = "Please Wait, Deleting File<BR /><BR /><IMG style='vertical-align: middle;' src='./images/progressbar3.gif'/>";
	var url ="./ajax/delfile.php?id=" + id + "&version=" + randomnumber;
	sendRequest (url, del_cb, "");	
	function del_cb(req) {
		var the_result=JSON.decode(req.responseText);
		if(the_result[0] == 100) {
			$('waiting').innerHTML = "File Deleted<BR />";
			$('end_waiting').style.display = 'block';	
			} else {
			$('waiting').innerHTML = "<BR />Error Deleting File<BR />";
			$('end_waiting').style.display = 'block';
			}
		}
	}
	
function end_wait() {
	$('waiting_wrapper').style.display = 'none';
	get_files();
	}

function get_files() {
	$('file_list').innerHTML = "Please Wait, loading files";
	var the_files = "<TABLE BORDER=1>";
	var the_color = "#CECECE";
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/fileman_list.php?version=" + randomnumber;
	sendRequest (url, filelist_cb, "");
	function filelist_cb(req) {
		var theFiles=JSON.decode(req.responseText);
		if(theFiles[0]['id'] == 0) {
			the_files = "";
			} else {
			the_files += "<TR class='heading'>";
			the_files += "<TD class='heading' style='font-size: 1em;'>ID</TD>";
			the_files += "<TD class='heading' style='font-size: 1em;'>Filename</TD>";
			the_files += "<TD class='heading' style='font-size: 1em;'>Ticket ID</TD>";
			the_files += "<TD class='heading' style='font-size: 1em;'>Responder ID</TD>";
			the_files += "<TD class='heading' style='font-size: 1em;'>Facility ID</TD>";
			the_files += "<TD class='heading' style='font-size: 1em;'>Type</TD>";
			the_files += "<TD class='heading' style='font-size: 1em;'>File Type</TD>";				
			the_files += "<TD class='heading' style='font-size: 1em;'>Who By</TD>";	
			the_files += "<TD class='heading' style='font-size: 1em;'>When</TD>";
			the_files += "<TD class='heading' style='font-size: 1em;'>User ID</TD>";
			the_files += "<TD class='heading' style='font-size: 1em;'>&nbsp;</TD>";	
			the_files += "<TD class='heading' style='font-size: 1em;'>&nbsp;</TD>";	
			the_files += "</TR>";			
			for(var key in theFiles) {
				var the_id =  theFiles[key]['id'];
				the_files += "<TR style='background: " + the_color + "; height: 30px;'>";
				the_files += "<TD style='font-size: 1em;'>" + the_id + "</TD>";
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['filename'] + "</TD>";
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['ticket_id'] + "</TD>";
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['responder_id'] + "</TD>";
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['facility_id'] + "</TD>";
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['type'] + "</TD>";
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['filetype'] + "</TD>";				
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['_by'] + "</TD>";	
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['_on'] + "</TD>";
				the_files += "<TD style='font-size: 1em;'>" + theFiles[key]['user_id'] + "</TD>";
				the_files += "<TD style='font-size: 1em; vertical-align: middle;'><A id='view_" + the_id +"' CLASS='plain' HREF='./ajax/download.php?filename=" + theFiles[key]['target_filename'] + "&origname=" + theFiles[key]['filename'] + "&type=" + theFiles[key]['type'] + "' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);'>View</A></TD>";	
				the_files += "<TD style='font-size: 1em; vertical-align: middle;'><SPAN id='file_" + the_id + "' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='do_delete(" + the_id + ");'>Del</SPAN></TD>";	
				the_files += "</TR>";
				the_color = (the_color == "#CECECE") ? "#DEDEDE" : "#CECECE";
				}
			}
		the_files += "</TABLE>";
		$('file_list').innerHTML = the_files;		
		}
	}
	
function file_window() {										// 9/10/13
	var url = "file_upload.php";
	var nfWindow = window.open(url, 'NewFileWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	setTimeout(function() { nfWindow.focus(); }, 1);
	}
</SCRIPT>
</HEAD>
<BODY onLoad = 'get_files(); ck_frames();'>
<CENTER>
<DIV CLASS='heading' style='width: 100%; text-align: center; font-size: 2em; color: #FFFFFF;'>FILE MANAGER</DIV> 
<BR /><BR /><BR /><BR />
<DIV id='waiting_wrapper' style='position: fixed; top: 30%; left: 40%; width: 20%; height: 20%; background-color: yellow; display: none;'>
	<DIV id='waiting'></DIV><BR /><BR />
	<SPAN id='end_waiting' style='display: none;' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='end_wait();'>Continue</SPAN>
</DIV>
<DIV ID='file_list' style='width: 60%;'></DIV>
<BR /><BR /><BR /><BR />
<SPAN id='up_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='file_window();'>Upload File</SPAN>
<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Back to Config</SPAN>
</CENTER>
<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>
</BODY></HTML>
