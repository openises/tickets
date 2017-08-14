<?php
/*
07/03/14	New file, to remove duplicate indexes in tables
*/
error_reporting(E_ALL);
set_time_limit(0);
require_once('./incs/functions.inc.php');
$tables = array();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Database Table Optimization</title>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	
<style type="text/css">
	html, body { margin: 0; padding: 0; font-size: 75%;}
	.hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; text-align: center; }
	.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #EFEFEF;font-weight: bolder; cursor: pointer; text-align: center; }	
	.plain_centered 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; color: black;background-color: #EFEFEF;font-weight: bolder;}					  
	.hover_lo 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 1px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }
	.plain_lo 	{  margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 3px; border-STYLE: hidden; border-color: #FFFFFF;}
	.data 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;
  				  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: yellow;font-weight: bolder;}		
	.message { FONT-WEIGHT: bold; FONT-SIZE: 20px; COLOR: #0000FF; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
</style>
<script src="./js/misc_function.js" type="application/x-javascript"></script>
<SCRIPT>
	var the_table_arr = new Array();
	var progressText = "";

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}
		
	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}
		
	function do_lo_hover (the_id) {
		CngClass(the_id, 'lo_hover');
		return true;
		}
		
	function do_plain (the_id) {
		CngClass(the_id, 'plain');
		return true;
		}
		
	function do_lo_plain (the_id) {
		CngClass(the_id, 'lo_plain');
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

	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name
			return AJAX.responseText;																				 
			} 
		else {
			alert ("201: failed")
			return false;
			}																						 
		}		// end function sync Ajax()		

	function init_optimize() {
		var randomnumber=Math.floor(Math.random()*99999999);
		for(var i=0; i<the_table_arr.length; i++){
			var url = "./ajax/optimize_table.php?tablename=" + the_table_arr[i] + "&version=" + randomnumber;
			var payload = syncAjax(url);						// does the work
			var the_ret_arr=JSON.decode(payload);
			if(the_ret_arr[0] != 0) {
				progressText += "Table: " + the_ret_arr[1] + " - indexes have been optimized<BR />";
				$('file_list').innerHTML = progressText;
				} else {
				progressText += "Table: " + the_ret_arr[1] + " - indexes are OK<BR />";
				$('file_list').innerHTML = progressText;
				}
			pause(500);
			}
		$('finish').style.display = 'block';
		}

	function initTables() {
		$('file_list').style.display='block';
		var randomnumber=Math.floor(Math.random()*99999999);
		var url = "./ajax/list_tables.php?version=" + randomnumber;
		var payload = syncAjax(url);						// does the work	
		the_table_arr=JSON.decode(payload);
		for(var i=0; i<the_table_arr.length; i++){
			$('table_list').innerHTML += the_table_arr[i] + "<BR />";
			}
		}
		
	function pause(milliseconds) {
		var dt = new Date();
		while ((new Date()) - dt <= milliseconds) { /* Do nothing */ }
		}
		
	function go_toconfig() {
		document.to_config_Form.submit();
		}
</SCRIPT>
</HEAD>
<BODY onLoad="initTables();">
<DIV id='outer' style='position: absolute; top: 0px; left: 0px; width: 100%;'>
	<DIV id='banner' style='position: absolute; top: 0px; left: 10%; width: 80%; height: 30px; background: #707070; color: #FFFFFF; font-size: 24px; font-weight: bold; text-align: center;'>Optimize Tickets Database Indexes</DIV>
	<BR />
	<BR />
	<DIV id='top' style='width: 100%; position: relative;'>
		<DIV id='leftcol' style='width: 40%; position: absolute; top: 100px; left: 10%;'>
			<DIV id='file_list_header' class='heading'>Tables in Database</DIV>
			<DIV id='table_list' style='border: 1px solid #707070; height: 200px; overflow-y: scroll; font-weight: normal; font-size: 1.4em; padding-left: 20px;'></DIV><BR /><BR />
			<CENTER>
			<SPAN id='b1' class = 'plain' style='text-align: center; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'init_optimize();'>Start Operation</SPAN>
			<SPAN id='b2' class = 'plain' style='text-align: center; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'go_toconfig();'>Return to Config</SPAN>
			</CENTER>
		</DIV>
		<DIV id='rightcol' style='width: 40%; position: absolute; top: 100px; right: 10%;'>
			<DIV id='file_list_header' class='heading'>Progress</DIV>
			<DIV id='file_list' style='border: 1px solid #707070; height: 200px; overflow-y: scroll; display: none; font-weight: normal; font-size: 1.4em; padding-left: 20px;'></DIV>

		</DIV>
	</DIV>
	<DIV id='bottom' style='width: 100%; position: relative; top: 400px;'>
		<DIV id='help' style='position: relative; left: 10%; width: 80%; font-size: 16px; font-weight: bold; border: 1px outset #707070;'>
			<DIV id='help_header' class='heading'>Help</DIV>
			<DIV id='help_text'><BR />
			This page allows you to review and optimize the indexes in the Tickets Database.<BR /><BR />
			This will be mainly used to optimize performance. Required indexes are automatically kept and duplicates are deleted.<BR /><BR />
			</DIV>
		</DIV>
	</DIV>
</DIV>
<DIV id='finish' style='position: absolute; top: 46%; left: 40%; width: 20%; height: 10%; background-color: green; color: yellow; text-align: center; display: none; z-index: 1000;' class='heading'>
Operation Complete<BR /><BR />
<SPAN id='b3' class = 'plain' style='text-align: center; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = 'go_toconfig();'>Finish</SPAN>
</DIV>
<FORM NAME='to_config_Form' METHOD="post" ACTION = "config.php"></FORM>	
</BODY>
</HTML>

