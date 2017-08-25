<?php
#
# stats_scr.php - Management Statistics from Tickets - calls statistics.php vai AJAX.
#
/*
6/14/11	First version
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$userid = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : 0;

if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {
	do_logout();
	exit();
	}
else {
	do_login(basename(__FILE__));
	}	
function found_user() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_settings` WHERE `user_id` = {$_SESSION['user_id']}";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$found_user = mysql_num_rows($result);
	if ($found_user > 0) {
		$user_exists = TRUE;
		} else {
		$user_exists = FALSE;
		}
	return $user_exists;
	}

	$do_mu_init = "mu_init();";	// start multi-user function
	
	function get_stat_type_name($value) {
		$type_name = "Not Used";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` WHERE `st_id` = {$value}";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
			$type_name = $row['name'];
			}		
		return $type_name;
		}
		
	function get_stat_type_type($value) {
		$stat_type = "Not Used";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` WHERE `st_id` = {$value}";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
			$stat_type = $row['stat_type'];
			}
		return $stat_type;
		}

	function parsedate($diff){
		$days = 0;
		$seconds = 0;   
		$hours   = 0;   
		$minutes = 0;   

		if($diff % 86400 <= 0){$days = $diff / 86400;}  // 86,400 seconds in a day   
		if($diff % 86400 > 0)   
		{   
			$rest = ($diff % 86400);   
			$days = ($diff - $rest) / 86400;   
			if($rest % 3600 > 0)   
			{   
				$rest1 = ($rest % 3600);   
				$hours = ($rest - $rest1) / 3600;   
				if($rest1 % 60 > 0)   
				{   
					$rest2 = ($rest1 % 60);   
				$minutes = ($rest1 - $rest2) / 60;   
				$seconds = $rest2;   
				}   
				else{$minutes = $rest1 / 60;}   
			}   
			else{$hours = $rest / 3600;}   
		}   

		if($days > 0){$days = floor($days);}   
		else{$days = 0;}   
		if($hours > 0){$hours = $hours;}   
		else{$hours = 0;}   
		if($minutes > 0){$minutes = $minutes;}   
		else{$minutes = 0;}   
		$seconds = $seconds; // always be at least one second
		if(($days==0) && ($hours==0) && ($minutes==0) && ($seconds==0)) {
			$ret_val =0;
			} else {
			$ret_val = $days.'-'.$hours.'-'.$minutes.'-'.$seconds;
			}

		return $ret_val;   
	}			
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
	<HEAD><TITLE>Tickets - Statistics Screen</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<STYLE>
		.header_wrapper	{ position: absolute; left: 2%; top: 2%; width: 98%;}	
		.header_wrapper2	{ position: absolute; left: 2%; top: 5%; width: 98%;}		
		.header_row		{ color: #000000; text-align: center; width: 100%;}	
		.header			{ color: #000000; width: 5%;}
		.page_heading	{ font-weight: bold; width: 100%; display: inline-block; text-align: left; background: #707070; color: #FFFFFF;}	
		.page_heading_s	{ font-weight: bold; display: inline-block; text-align: left; background: #707070; color: #FFFFFF;}			
		.date_time		{ padding-right: 20px; font-weight: bold; display: inline-block; float: right; vertical-align: bottom;}
		.button_bar 	{ padding-right: 80px; text-align: center; padding-left: 25px;}			
		.buttons 		{ border: 2px outset #FFFFFF; padding: 2px; background-color: #EFEFEF; font-weight: bold; display: table-cell; cursor: pointer;}	
		.stats_heading	{ text-align: center; font-weight: bold; color: #000000; background: #CECECE;}
		.stats_wrapper	{ align: center; position: absolute; left: 2%; top: 130px; width: 96%; height: 80%; float: left;}		
		.stats_outer	{ border: 4px outset; width: 24%; height: 22em; float: left; background: #FFFFFF;}
		.stats_inner	{ position: relative; top: 10%; padding: 10px; text-align: center; font-size: 3em; color: #000000; vertical-align: middle;}
		.stats_inner_warn	{ position: relative; top: 10%; padding: 10px; text-align: center; font-size: 3em; color: #000000; background: red; vertical-align: middle;}		
		.config_wrapper	{ position: absolute; right: 10%; top: 20%; width: 80%; align: center; display: table; border: 1px solid;}	
		.config_row		{ align: center; display: table-row; border: 1px solid;}
		.config_cell_heading	{ font-size: 1.3em; align: left; display: table-cell; border: 1px solid; padding: 3px; color: #FFFF00; background: #707070;}			
		.config_cell_title	{ align: left; display: table-cell; border: 1px solid; padding: 3px; color: #000000; background: #CECECE;}	
		.config_cell_data	{ align: center; display: table-cell; border: 1px solid; padding: 3px; color: #000000; background: #FFFFFF;}
		.config_cell_hint	{ align: center; display: table-cell; border: 1px solid; padding: 3px; color: #000000; background: #FFFFFF;}
		.config_cell_butts	{ align: center; display: table-cell; border: 1px solid; padding: 3px; color: #000000; background: #FFFFFF;}		
		.error_page	{ text-align: center; font-weight: bold; font-size: 2em; position: absolute; left: 2%; top: 50%; width: 96%;}		
		</STYLE>	
	<SCRIPT SRC="./js/misc_function.js" type="application/x-javascript"></SCRIPT>
<SCRIPT>
	function out_frames() {		//  onLoad = "out_frames()"
		if (top.location != location) top.location.href = document.location.href;
		}		// end function out_frames()
		
	function set_hint(form, selbox, hint_loc){
		for (var i = 0; i < form.elements[selbox].options.length; i++) {
			if (form.elements[selbox].options[i].selected){
				var url = "./ajax/stats_type.php?type=" + form.elements[selbox].options[i].value;
				sendRequest (url,stats_cb,"");			
				function stats_cb(req) {
					var the_ret_str=JSON.decode(req.responseText);	
					if(the_ret_str == "int") {
						if($(hint_loc)) {$(hint_loc).innerHTML = "Integer - input a number";}	
						} else if(the_ret_str == "avg") {
						if($(hint_loc)) {$(hint_loc).innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
						} else {
						if($(hint_loc)) {$(hint_loc).innerHTML = "Not Used";}
						}
					}
				}
			}
		}
	
	Number.prototype.timeLeft = function(){ 
		var days = Math.floor(this / 86400); 
		var hours = Math.floor((this - (days * 86400)) / 3600); 
		var minutes = Math.floor((this - ((hours * 3600) + (days * 86400))) / 60); 
		var seconds = this - ((days * 86400) + (hours * 3600) + (minutes * 60)); 
		var result = new String(); 
		if((days == 1) === true){result += days + ' Day,';} 		
		if((days > 1) === true){result += days + ' Days,';} 
		if((hours == 1) === true){result += ' ' + hours + ' Hour, ';} 		
		if((hours > 1) === true){result += ' ' + hours + ' Hours, ';} 
		if((seconds > 30)){
			minutes = minutes + 1;
			}
		if((minutes == 1) === true){	result += ' ' + minutes + ' Minute,';} 			
		if((minutes > 1) === true){	result += ' ' + minutes + ' Minutes,';} 
		result = result.slice(0, -1); 
		return result; 
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
		
	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}
		
	function do_logout() {
		clearInterval(mu_interval);
		mu_interval = null;
		is_initialized = false;
		document.gout_form.submit();			// send logout 
		}	
</SCRIPT>
<?php

if((isset($_GET['fm_sub'])) && ($_GET['fm_sub'])) {
?>
<SCRIPT>
	var is_initialized = false;
	var mu_interval = 10000;
</SCRIPT>
<?php
	function makeSeconds($a) {
		$string = explode("-", $a);
		$days = $string[0];
		$hours = $string[1];
		$minutes = $string[2];
		$secs = $string[3];
		$days2 = $days * 24 * 60 * 60;
		$hours2 = $hours * 60 * 60;
		$minutes2 = $minutes * 60;
		$seconds = $hours2 + $days2 + $minutes2 + $secs;
		return $seconds;
		}
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_settings` WHERE `user_id` = {$_SESSION['user_id']}";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0) {
		$th1 = ((get_stat_type_type($_POST['frm_box1']) == "avg") && ($_POST['frm_t1'] !="0")) ?  makeSeconds($_POST['frm_t1']) : $_POST['frm_t1'];
		$th2 = ((get_stat_type_type($_POST['frm_box2']) == "avg") && ($_POST['frm_t2'] !="0")) ?  makeSeconds($_POST['frm_t2']) : $_POST['frm_t2'];
		$th3 = ((get_stat_type_type($_POST['frm_box3']) == "avg") && ($_POST['frm_t3'] !="0")) ?  makeSeconds($_POST['frm_t3']) : $_POST['frm_t3'];
		$th4 = ((get_stat_type_type($_POST['frm_box4']) == "avg") && ($_POST['frm_t4'] !="0")) ?  makeSeconds($_POST['frm_t4']) : $_POST['frm_t4'];
		$th5 = ((get_stat_type_type($_POST['frm_box5']) == "avg") && ($_POST['frm_t5'] !="0")) ?  makeSeconds($_POST['frm_t5']) : $_POST['frm_t5'];
		$th6 = ((get_stat_type_type($_POST['frm_box6']) == "avg") && ($_POST['frm_t6'] !="0")) ?  makeSeconds($_POST['frm_t6']) : $_POST['frm_t6'];
		$th7 = ((get_stat_type_type($_POST['frm_box7']) == "avg") && ($_POST['frm_t7'] !="0")) ?  makeSeconds($_POST['frm_t7']) : $_POST['frm_t7'];		
		$th8 = ((get_stat_type_type($_POST['frm_box8']) == "avg") && ($_POST['frm_t8'] !="0")) ?  makeSeconds($_POST['frm_t8']) : $_POST['frm_t8'];
		$thw1 = ((get_stat_type_type($_POST['frm_box1']) == "avg") && ($_POST['frm_tw1'] !="0")) ?  makeSeconds($_POST['frm_tw1']) : $_POST['frm_tw1'];
		$thw2 = ((get_stat_type_type($_POST['frm_box2']) == "avg") && ($_POST['frm_tw2'] !="0")) ?  makeSeconds($_POST['frm_tw2']) : $_POST['frm_tw2'];
		$thw3 = ((get_stat_type_type($_POST['frm_box3']) == "avg") && ($_POST['frm_tw3'] !="0")) ?  makeSeconds($_POST['frm_tw3']) : $_POST['frm_tw3'];
		$thw4 = ((get_stat_type_type($_POST['frm_box4']) == "avg") && ($_POST['frm_tw4'] !="0")) ?  makeSeconds($_POST['frm_tw4']) : $_POST['frm_tw4'];
		$thw5 = ((get_stat_type_type($_POST['frm_box5']) == "avg") && ($_POST['frm_tw5'] !="0")) ?  makeSeconds($_POST['frm_tw5']) : $_POST['frm_tw5'];
		$thw6 = ((get_stat_type_type($_POST['frm_box6']) == "avg") && ($_POST['frm_tw6'] !="0")) ?  makeSeconds($_POST['frm_tw6']) : $_POST['frm_tw6'];
		$thw7 = ((get_stat_type_type($_POST['frm_box7']) == "avg") && ($_POST['frm_tw7'] !="0")) ?  makeSeconds($_POST['frm_tw7']) : $_POST['frm_tw7'];		
		$thw8 = ((get_stat_type_type($_POST['frm_box8']) == "avg") && ($_POST['frm_tw8'] !="0")) ?  makeSeconds($_POST['frm_tw8']) : $_POST['frm_tw8'];
		$thf1 = ((get_stat_type_type($_POST['frm_box1']) == "avg") && ($_POST['frm_tf1'] !="0")) ?  makeSeconds($_POST['frm_tf1']) : $_POST['frm_tf1'];
		$thf2 = ((get_stat_type_type($_POST['frm_box2']) == "avg") && ($_POST['frm_tf2'] !="0")) ?  makeSeconds($_POST['frm_tf2']) : $_POST['frm_tf2'];
		$thf3 = ((get_stat_type_type($_POST['frm_box3']) == "avg") && ($_POST['frm_tf3'] !="0")) ?  makeSeconds($_POST['frm_tf3']) : $_POST['frm_tf3'];
		$thf4 = ((get_stat_type_type($_POST['frm_box4']) == "avg") && ($_POST['frm_tf4'] !="0")) ?  makeSeconds($_POST['frm_tf4']) : $_POST['frm_tf4'];
		$thf5 = ((get_stat_type_type($_POST['frm_box5']) == "avg") && ($_POST['frm_tf5'] !="0")) ?  makeSeconds($_POST['frm_tf5']) : $_POST['frm_tf5'];
		$thf6 = ((get_stat_type_type($_POST['frm_box6']) == "avg") && ($_POST['frm_tf6'] !="0")) ?  makeSeconds($_POST['frm_tf6']) : $_POST['frm_tf6'];
		$thf7 = ((get_stat_type_type($_POST['frm_box7']) == "avg") && ($_POST['frm_tf7'] !="0")) ?  makeSeconds($_POST['frm_tf7']) : $_POST['frm_tf7'];		
		$thf8 = ((get_stat_type_type($_POST['frm_box8']) == "avg") && ($_POST['frm_tf8'] !="0")) ?  makeSeconds($_POST['frm_tf8']) : $_POST['frm_tf8'];		
		$ttype1 = ($_POST['frm_t_type1'] == "0") ? "Less" : $_POST['frm_t_type1'];
		$ttype2 = ($_POST['frm_t_type2'] == "0") ? "Less" : $_POST['frm_t_type2'];
		$ttype3 = ($_POST['frm_t_type3'] == "0") ? "Less" : $_POST['frm_t_type3'];
		$ttype4 = ($_POST['frm_t_type4'] == "0") ? "Less" : $_POST['frm_t_type4'];
		$ttype5 = ($_POST['frm_t_type5'] == "0") ? "Less" : $_POST['frm_t_type5'];
		$ttype6 = ($_POST['frm_t_type6'] == "0") ? "Less" : $_POST['frm_t_type6'];
		$ttype7 = ($_POST['frm_t_type7'] == "0") ? "Less" : $_POST['frm_t_type7'];
		$ttype8 = ($_POST['frm_t_type8'] == "0") ? "Less" : $_POST['frm_t_type8'];	
	
		$query = "UPDATE `$GLOBALS[mysql_prefix]stats_settings` SET
			`refresh_rate`= " . 		quote_smart(trim($_POST['frm_refresh'])) . ",
			`f1`= " . 				quote_smart(trim($_POST['frm_box1'])) . ",
			`f2`= " . 				quote_smart(trim($_POST['frm_box2'])) . ",
			`f3`= " . 				quote_smart(trim($_POST['frm_box3'])) . ",
			`f4`= " . 				quote_smart(trim($_POST['frm_box4'])) . ",
			`f5`= " . 				quote_smart(trim($_POST['frm_box5'])) . ",
			`f6`= " . 				quote_smart(trim($_POST['frm_box6'])) . ",
			`f7`= " . 				quote_smart(trim($_POST['frm_box7'])) . ",
			`f8`= " . 				quote_smart(trim($_POST['frm_box8'])) . ",
			`threshold_1`= " . 		quote_smart(trim($th1)) . ",
			`threshold_2`= " . 		quote_smart(trim($th2)) . ",
			`threshold_3`= " . 		quote_smart(trim($th3)) . ",
			`threshold_4`= " . 		quote_smart(trim($th4)) . ",
			`threshold_5`= " . 		quote_smart(trim($th5)) . ",
			`threshold_6`= " . 		quote_smart(trim($th6)) . ",
			`threshold_7`= " . 		quote_smart(trim($th7)) . ",
			`threshold_8`= " . 		quote_smart(trim($th8)) . ",
			`thresholdw_1`= " . 	quote_smart(trim($thw1)) . ",
			`thresholdw_2`= " . 	quote_smart(trim($thw2)) . ",
			`thresholdw_3`= " . 	quote_smart(trim($thw3)) . ",
			`thresholdw_4`= " . 	quote_smart(trim($thw4)) . ",
			`thresholdw_5`= " . 	quote_smart(trim($thw5)) . ",
			`thresholdw_6`= " . 	quote_smart(trim($thw6)) . ",
			`thresholdw_7`= " . 	quote_smart(trim($thw7)) . ",
			`thresholdw_8`= " . 	quote_smart(trim($thw8)) . ",
			`thresholdf_1`= " . 	quote_smart(trim($thf1)) . ",
			`thresholdf_2`= " . 	quote_smart(trim($thf2)) . ",
			`thresholdf_3`= " . 	quote_smart(trim($thf3)) . ",
			`thresholdf_4`= " . 	quote_smart(trim($thf4)) . ",
			`thresholdf_5`= " . 	quote_smart(trim($thf5)) . ",
			`thresholdf_6`= " . 	quote_smart(trim($thf6)) . ",
			`thresholdf_7`= " . 	quote_smart(trim($thf7)) . ",
			`thresholdf_8`= " . 	quote_smart(trim($thf8)) . ",
			`t_type1`= " . 			quote_smart(trim($ttype1)) . ",
			`t_type2`= " . 			quote_smart(trim($ttype2)) . ",
			`t_type3`= " . 			quote_smart(trim($ttype3)) . ",
			`t_type4`= " . 			quote_smart(trim($ttype4)) . ",
			`t_type5`= " . 			quote_smart(trim($ttype5)) . ",
			`t_type6`= " . 			quote_smart(trim($ttype6)) . ",
			`t_type7`= " . 			quote_smart(trim($ttype7)) . ",
			`t_type8`= " . 			quote_smart(trim($ttype8)) . "			
			WHERE `user_id`= " . 	quote_smart(trim($_POST['frm_user'])) . ";";

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);	
		if($result) {
			
			print "<DIV class='header_wrapper'>";
				print "<DIV class='header_row'>";
					print "<DIV class='page_heading'>";
						print "<IMG SRC='" . get_variable('logo') . "' BORDER=0 />";
						print "<SPAN class='page_heading text_biggest' style='display: inline;'>" . get_variable('title_string') . " - Statistics Module, Config Save</SPAN>";
						print "<SPAN class='page_heading_s text_biggest' style='display: inline;'></SPAN>";
						print "<SPAN id='stats8_inner' class='date_time text text_right' style='display: inline; vertical-align: bottom;'></SPAN>";
					print "</DIV>";
				print "</DIV>";
				print "<DIV class='header_row'>";
					print "<DIV class='button_bar'>";
						print "<SPAN id='links' CLASS='plain text' style='float: right; display: inline;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onclick=\"window.location='stats_scr.php?stats=stats' \">Statistics</SPAN>";
						print "<SPAN id='links' CLASS='plain text' style='float: right; display: inline;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);'onclick=\"window.location='stats_scr.php?config=config' \">Configuration</SPAN>";						
						print "<SPAN ID='gout' CLASS='plain text' style='float: right; display: inline;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick=\"do_logout()\">Logout</SPAN>";
					print "</DIV>";
				print "</DIV>";
			print "</DIV>";
			print "<DIV class='stats_wrapper'>";
				print "<DIV class='error_page'>The settings have been updated</DIV>";			
			print "</DIV>";		
			print "</BODY></HTML>";				
			}			
		} else {
		$th1 = ((get_stat_type_type($_POST['frm_box1']) == "avg") && ($_POST['frm_t1'] !=0)) ?  makeSeconds($_POST['frm_t1']) : $_POST['frm_t1'];
		$th2 = ((get_stat_type_type($_POST['frm_box2']) == "avg") && ($_POST['frm_t2'] !=0)) ?  makeSeconds($_POST['frm_t2']) : $_POST['frm_t2'];
		$th3 = ((get_stat_type_type($_POST['frm_box3']) == "avg") && ($_POST['frm_t3'] !=0)) ?  makeSeconds($_POST['frm_t3']) : $_POST['frm_t3'];
		$th4 = ((get_stat_type_type($_POST['frm_box4']) == "avg") && ($_POST['frm_t4'] !=0)) ?  makeSeconds($_POST['frm_t4']) : $_POST['frm_t4'];
		$th5 = ((get_stat_type_type($_POST['frm_box5']) == "avg") && ($_POST['frm_t5'] !=0)) ?  makeSeconds($_POST['frm_t5']) : $_POST['frm_t5'];
		$th6 = ((get_stat_type_type($_POST['frm_box6']) == "avg") && ($_POST['frm_t6'] !=0)) ?  makeSeconds($_POST['frm_t6']) : $_POST['frm_t6'];
		$th7 = ((get_stat_type_type($_POST['frm_box7']) == "avg") && ($_POST['frm_t7'] !=0)) ?  makeSeconds($_POST['frm_t7']) : $_POST['frm_t7'];		
		$th8 = ((get_stat_type_type($_POST['frm_box8']) == "avg") && ($_POST['frm_t8'] !=0)) ?  makeSeconds($_POST['frm_t8']) : $_POST['frm_t8'];
		$thw1 = ((get_stat_type_type($_POST['frm_box1']) == "avg") && ($_POST['frm_tw1'] !=0)) ?  makeSeconds($_POST['frm_tw1']) : $_POST['frm_tw1'];
		$thw2 = ((get_stat_type_type($_POST['frm_box2']) == "avg") && ($_POST['frm_tw2'] !=0)) ?  makeSeconds($_POST['frm_tw2']) : $_POST['frm_tw2'];
		$thw3 = ((get_stat_type_type($_POST['frm_box3']) == "avg") && ($_POST['frm_tw3'] !=0)) ?  makeSeconds($_POST['frm_tw3']) : $_POST['frm_tw3'];
		$thw4 = ((get_stat_type_type($_POST['frm_box4']) == "avg") && ($_POST['frm_tw4'] !=0)) ?  makeSeconds($_POST['frm_tw4']) : $_POST['frm_tw4'];
		$thw5 = ((get_stat_type_type($_POST['frm_box5']) == "avg") && ($_POST['frm_tw5'] !=0)) ?  makeSeconds($_POST['frm_tw5']) : $_POST['frm_tw5'];
		$thw6 = ((get_stat_type_type($_POST['frm_box6']) == "avg") && ($_POST['frm_tw6'] !=0)) ?  makeSeconds($_POST['frm_tw6']) : $_POST['frm_tw6'];
		$thw7 = ((get_stat_type_type($_POST['frm_box7']) == "avg") && ($_POST['frm_tw7'] !=0)) ?  makeSeconds($_POST['frm_tw7']) : $_POST['frm_tw7'];		
		$thw8 = ((get_stat_type_type($_POST['frm_box8']) == "avg") && ($_POST['frm_tw8'] !=0)) ?  makeSeconds($_POST['frm_tw8']) : $_POST['frm_tw8'];
		$thf1 = ((get_stat_type_type($_POST['frm_box1']) == "avg") && ($_POST['frm_tf1'] !=0)) ?  makeSeconds($_POST['frm_tf1']) : $_POST['frm_tf1'];
		$thf2 = ((get_stat_type_type($_POST['frm_box2']) == "avg") && ($_POST['frm_tf2'] !=0)) ?  makeSeconds($_POST['frm_tf2']) : $_POST['frm_tf2'];
		$thf3 = ((get_stat_type_type($_POST['frm_box3']) == "avg") && ($_POST['frm_tf3'] !=0)) ?  makeSeconds($_POST['frm_tf3']) : $_POST['frm_tf3'];
		$thf4 = ((get_stat_type_type($_POST['frm_box4']) == "avg") && ($_POST['frm_tf4'] !=0)) ?  makeSeconds($_POST['frm_tf4']) : $_POST['frm_tf4'];
		$thf5 = ((get_stat_type_type($_POST['frm_box5']) == "avg") && ($_POST['frm_tf5'] !=0)) ?  makeSeconds($_POST['frm_tf5']) : $_POST['frm_tf5'];
		$thf6 = ((get_stat_type_type($_POST['frm_box6']) == "avg") && ($_POST['frm_tf6'] !=0)) ?  makeSeconds($_POST['frm_tf6']) : $_POST['frm_tf6'];
		$thf7 = ((get_stat_type_type($_POST['frm_box7']) == "avg") && ($_POST['frm_tf7'] !=0)) ?  makeSeconds($_POST['frm_tf7']) : $_POST['frm_tf7'];		
		$thf8 = ((get_stat_type_type($_POST['frm_box8']) == "avg") && ($_POST['frm_tf8'] !=0)) ?  makeSeconds($_POST['frm_tf8']) : $_POST['frm_tf8'];		
		$ttype1 = ($_POST['frm_t_type1'] == "0") ? "Less" : $_POST['frm_t_type1'];
		$ttype2 = ($_POST['frm_t_type2'] == "0") ? "Less" : $_POST['frm_t_type2'];
		$ttype3 = ($_POST['frm_t_type3'] == "0") ? "Less" : $_POST['frm_t_type3'];
		$ttype4 = ($_POST['frm_t_type4'] == "0") ? "Less" : $_POST['frm_t_type4'];
		$ttype5 = ($_POST['frm_t_type5'] == "0") ? "Less" : $_POST['frm_t_type5'];
		$ttype6 = ($_POST['frm_t_type6'] == "0") ? "Less" : $_POST['frm_t_type6'];
		$ttype7 = ($_POST['frm_t_type7'] == "0") ? "Less" : $_POST['frm_t_type7'];
		$ttype8 = ($_POST['frm_t_type8'] == "0") ? "Less" : $_POST['frm_t_type8'];	

		$query = "INSERT INTO `$GLOBALS[mysql_prefix]stats_settings` ( `user_id`, `refresh_rate`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `threshold_1`, `threshold_2`, `threshold_3`, `threshold_4`, `threshold_5`, `threshold_6`, `threshold_7`, `threshold_8`, `thresholdw_1`, `thresholdw_2`, `thresholdw_3`, `thresholdw_4`, `thresholdw_5`, `thresholdw_6`, `thresholdw_7`, `thresholdw_8`, `thresholdf_1`, `thresholdf_2`, `thresholdf_3`, `thresholdf_4`, `thresholdf_5`, `thresholdf_6`, `thresholdf_7`, `thresholdf_8`,`t_type1`, `t_type2`, `t_type3`, `t_type4`, `t_type5`, `t_type6`, `t_type7`, `t_type8` )
			VALUES (" .
			quote_smart(trim($_POST['frm_user'])) . "," .
			quote_smart(trim($_POST['frm_refresh'])) . "," .
			quote_smart(trim($_POST['frm_box1'])) . "," .
			quote_smart(trim($_POST['frm_box2'])) . "," .
			quote_smart(trim($_POST['frm_box3'])) . "," .
			quote_smart(trim($_POST['frm_box4'])) . "," .
			quote_smart(trim($_POST['frm_box5'])) . "," .
			quote_smart(trim($_POST['frm_box6'])) . "," .
			quote_smart(trim($_POST['frm_box7'])) . "," .
			quote_smart(trim($_POST['frm_box8'])) . "," .
			quote_smart(trim($th1)) . "," .
			quote_smart(trim($th2)) . "," .
			quote_smart(trim($th3)) . "," .
			quote_smart(trim($th4)) . "," .
			quote_smart(trim($th5)) . "," .
			quote_smart(trim($th6)) . "," .
			quote_smart(trim($th7)) . "," .
			quote_smart(trim($th8)) . "," .
			quote_smart(trim($thw1)) . "," .
			quote_smart(trim($thw2)) . "," .
			quote_smart(trim($thw3)) . "," .
			quote_smart(trim($thw4)) . "," .
			quote_smart(trim($thw5)) . "," .
			quote_smart(trim($thw6)) . "," .
			quote_smart(trim($thw7)) . "," .
			quote_smart(trim($thw8)) . "," .
			quote_smart(trim($thf1)) . "," .
			quote_smart(trim($thf2)) . "," .
			quote_smart(trim($thf3)) . "," .
			quote_smart(trim($thf4)) . "," .
			quote_smart(trim($thf5)) . "," .
			quote_smart(trim($thf6)) . "," .
			quote_smart(trim($thf7)) . "," .
			quote_smart(trim($thf8)) . "," .
			quote_smart(trim($ttype1)) . "," .
			quote_smart(trim($ttype2)) . "," .
			quote_smart(trim($ttype3)) . "," .
			quote_smart(trim($ttype4)) . "," .
			quote_smart(trim($ttype5)) . "," .
			quote_smart(trim($ttype6)) . "," .
			quote_smart(trim($ttype7)) . "," .
			quote_smart(trim($ttype8)) . ");";			
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		if($result) {
			print "<DIV class='header_wrapper'>";
				print "<DIV class='header_row'>";
					print "<DIV class='page_heading'>TICKETS CAD Statistics Module - Config</DIV><DIV class='page_heading_s'></DIV>";
				print "</DIV>";
			print "</DIV>";
			print "<DIV class='header_wrapper2'>";	
				print "<DIV class='header_row'>";
					print "<DIV id='stats8_inner' class='date_time'></DIV>";
					print "<DIV class='button_bar'>";
						print "<SPAN id='links' class='buttons' onclick=\"window.location='stats_scr.php?stats=stats' \">Statistics</SPAN>";
						print "<SPAN id='links' class='buttons' onclick=\"window.location='stats_scr.php?config=config' \">Configuration</SPAN>";						
						print "<SPAN ID='gout' CLASS='buttons' onClick=\"do_logout()\">Logout</SPAN>";
					print "</DIV>";
				print "</DIV>";
			print "</DIV>";	
			print "<DIV class='stats_wrapper'>";
				print "<DIV class='error_page'>The settings have been inserted</DIV>";			
			print "</DIV>";		
			print "</BODY></HTML>";	
			}
		}
	}
	
if ((isset($_GET['stats'])) && ($_GET['stats'] == "stats") && (!isset($_GET['frm_sub']))) {
?>
<SCRIPT>


	var is_initialized = false;
	var mu_interval = null;
<?php
	if(!(found_user())) {
//		$host  = $_SERVER['HTTP_HOST'];
//		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
//		$extra = 'stats_scr.php?config=config';
//		header("Location: http://$host$uri/$extra");
		print "</SCRIPT></HEAD><BODY>";
		print "<DIV style='font-size: 14px; position: fixed; top: 250px; left: 100px;'>";
		print "This is the first time you have logged in as this statistics user. Please go to <a style='font-size: 14px;' href=\"stats_scr.php?config=config \">Statistics Configuration</a> To set up the required user configuration.";
		print "</DIV></BODY></HTML>";
	} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_settings` WHERE `user_id` = {$_SESSION['user_id']}";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$refresh_time = ($row['refresh_rate'] * 1000);
		$f1 = $row['f1'];
		$f2 = $row['f2'];
		$f3 = $row['f3'];
		$f4 = $row['f4'];
		$f5 = $row['f5'];
		$f6 = $row['f6'];
		$f7 = $row['f7'];
		$f8 = $row['f8'];	
		$type1 = ((isset($row['f1'])) && ($f1 != 0)) ? get_stat_type_type($f1): "Not Used";
		$type2 = ((isset($row['f2'])) && ($f2 != 0)) ? get_stat_type_type($f2): "Not Used";
		$type3 = ((isset($row['f3'])) && ($f3 != 0)) ? get_stat_type_type($f3): "Not Used";
		$type4 = ((isset($row['f4'])) && ($f4 != 0)) ? get_stat_type_type($f4): "Not Used";
		$type5 = ((isset($row['f5'])) && ($f5 != 0)) ? get_stat_type_type($f5): "Not Used";
		$type6 = ((isset($row['f6'])) && ($f6 != 0)) ? get_stat_type_type($f6): "Not Used";
		$type7 = ((isset($row['f7'])) && ($f7 != 0)) ? get_stat_type_type($f7): "Not Used";
		$type8 = ((isset($row['f8'])) && ($f8 != 0)) ? get_stat_type_type($f8): "Not Used";	
		$t1 = $row['threshold_1'];
		$t2 = $row['threshold_2'];
		$t3 = $row['threshold_3'];
		$t4 = $row['threshold_4'];
		$t5 = $row['threshold_5'];
		$t6 = $row['threshold_6'];
		$t7 = $row['threshold_7'];
		$t8 = $row['threshold_8'];
		$tw1 = $row['thresholdw_1'];
		$tw2 = $row['thresholdw_2'];
		$tw3 = $row['thresholdw_3'];
		$tw4 = $row['thresholdw_4'];
		$tw5 = $row['thresholdw_5'];
		$tw6 = $row['thresholdw_6'];
		$tw7 = $row['thresholdw_7'];
		$tw8 = $row['thresholdw_8'];	
		$tf1 = $row['thresholdf_1'];
		$tf2 = $row['thresholdf_2'];
		$tf3 = $row['thresholdf_3'];
		$tf4 = $row['thresholdf_4'];
		$tf5 = $row['thresholdf_5'];
		$tf6 = $row['thresholdf_6'];
		$tf7 = $row['thresholdf_7'];
		$tf8 = $row['thresholdf_8'];		
		$tt1 = $row['t_type1'];
		$tt2 = $row['t_type2'];
		$tt3 = $row['t_type3'];
		$tt4 = $row['t_type4'];
		$tt5 = $row['t_type5'];
		$tt6 = $row['t_type6'];
		$tt7 = $row['t_type7'];
		$tt8 = $row['t_type8'];	
	
?>

		var t1 = <?php print $t1;?>;
		var t2 = <?php print $t2;?>;
		var t3 = <?php print $t3;?>;
		var t4 = <?php print $t4;?>;
		var t5 = <?php print $t5;?>;
		var t6 = <?php print $t6;?>;
		var t7 = <?php print $t7;?>;
		var t8 = <?php print $t8;?>;
		var tw1 = <?php print $tw1;?>;
		var tw2 = <?php print $tw2;?>;
		var tw3 = <?php print $tw3;?>;
		var tw4 = <?php print $tw4;?>;
		var tw5 = <?php print $tw5;?>;
		var tw6 = <?php print $tw6;?>;
		var tw7 = <?php print $tw7;?>;
		var tw8 = <?php print $tw8;?>;
		var tf1 = <?php print $tf1;?>;
		var tf2 = <?php print $tf2;?>;
		var tf3 = <?php print $tf3;?>;
		var tf4 = <?php print $tf4;?>;
		var tf5 = <?php print $tf5;?>;
		var tf6 = <?php print $tf6;?>;
		var tf7 = <?php print $tf7;?>;
		var tf8 = <?php print $tf8;?>;	
		var tt1 = "<?php print $tt1;?>";
		var tt2 = "<?php print $tt2;?>";
		var tt3 = "<?php print $tt3;?>";
		var tt4 = "<?php print $tt4;?>";
		var tt5 = "<?php print $tt5;?>";
		var tt6 = "<?php print $tt6;?>";
		var tt7 = "<?php print $tt7;?>";
		var tt8 = "<?php print $tt8;?>";
		var type1 = "<?php print $type1;?>";
		var type2 = "<?php print $type2;?>";
		var type3 = "<?php print $type3;?>";
		var type4 = "<?php print $type4;?>";
		var type5 = "<?php print $type5;?>";
		var type6 = "<?php print $type6;?>";
		var type7 = "<?php print $type7;?>";
		var type8 = "<?php print $type8;?>";
		
		function do_loop() {								// monitor for changes
			sendRequest ('./ajax/statistics.php?user=<?php print $userid;?>',get_statistics_cb, "");
			}			// end function do_loop()	

		function out_threshold(val, stat, threshold, threshold_warn, threshold_flag, threshold_type) {	//	Checks whether statistic fails threshold test or not.
			if(threshold_type == "Less") {
				if(threshold != 0) {
					if(val < threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFF00";}
						}
					if(val < threshold_warn) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF9900";}
						}
					if (val < threshold) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF0000";}
						}
					if (val >= threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFFFF";}
						}
					}
				}
			if(threshold_type == "Less or Equal") {
				if(threshold != 0) {
					if(val <= threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFF00";}
						}
					if(val <= threshold_warn) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF9900";}
						}
					if (val <= threshold) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF0000";}
						}
					if (val > threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFFFF";}
						}
					}
				}
			if(threshold_type == "Equal") {
				if(threshold != 0) {
					if(val == threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFF00";}
						}
					if(val == threshold_warn) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF9900";}
						}
					if (val == threshold) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF0000";}
						}
					if ((val != threshold_flag) && (val != threshold_warn) && (val != threshold)) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFFFF";}
						}
					}
				}
			if(threshold_type == "More or Equal") {
				if(threshold != 0) {
					if(val >= threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFF00";}
						}
					if(val >= threshold_warn) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF9900";}
						}
					if (val >= threshold) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF0000";}
						}
					if (val < threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFFFF";}
						}
					}
				}
			if(threshold_type == "More") {
				if(threshold != 0) {
					if(val > threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFF00";}
						}
					if(val > threshold_warn) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF9900";}
						}
					if (val > threshold) {
						if($(stat)) {$(stat).style.backgroundColor = "#FF0000";}
						}
					if (val <= threshold_flag) {
						if($(stat)) {$(stat).style.backgroundColor = "#FFFFFF";}
						}
					}
				}
			}	

		function get_statistics_cb(req) {
			var the_id_arr=JSON.decode(req.responseText);
			if (the_id_arr.length != 9)  {
				alert("server error at <?php print basename(__FILE__) . " " . __LINE__;?> ");
				}
				out_threshold(the_id_arr[0], "stats0", t1, tw1, tf1, tt1);
				out_threshold(the_id_arr[1], "stats1", t2, tw2, tf2, tt2);
				out_threshold(the_id_arr[2], "stats2", t3, tw3, tf3, tt3);
				out_threshold(the_id_arr[3], "stats3", t4, tw4, tf4, tt4);
				out_threshold(the_id_arr[4], "stats4", t5, tw5, tf5, tt5);
				out_threshold(the_id_arr[5], "stats5", t6, tw6, tf6, tt6);
				out_threshold(the_id_arr[6], "stats6", t7, tw7, tf7, tt7);
				out_threshold(the_id_arr[7], "stats7", t8, tw8, tf8, tt8);
				if(type1 == "int") {
					if($('stats0_inner')) {$('stats0_inner').innerHTML = the_id_arr[0];}
					if($('hint1')) {$('hint1').innerHTML = "Integer - input a number";}				
					} else if (type1 == "avg") {
					if($('stats0_inner')) {$('stats0_inner').innerHTML = the_id_arr[0].timeLeft();}
					if($('hint1')) {$('hint1').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
					} else {
					if($('hint1')) {$('hint1').innerHTML = "Not Used";}				
					}
				if(type2 == "int") {				
					if($('stats1_inner')) {$('stats1_inner').innerHTML = the_id_arr[1];}
					if($('hint2')) {$('hint2').innerHTML = "Integer - input a number";}					
					} else if (type2 == "avg") {
					if($('stats1_inner')) {$('stats1_inner').innerHTML = the_id_arr[1].timeLeft();}	
					if($('hint2')) {$('hint2').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
					} else {
					if($('hint2')) {$('hint2').innerHTML = "Not Used";}			
					}
				if(type3 == "int") {				
					if($('stats2_inner')) {$('stats2_inner').innerHTML = the_id_arr[2];}
					if($('hint3')) {$('hint3').innerHTML = "Integer - input a number";}					
					} else if (type3 == "avg") {
					if($('stats2_inner')) {$('stats2_inner').innerHTML = the_id_arr[2].timeLeft();}
					if($('hint3')) {$('hint3').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
					} else {
					if($('hint3')) {$('hint3').innerHTML = "Not Used";}		
					}
				if(type4 == "int") {				
					if($('stats3_inner')) {$('stats3_inner').innerHTML = the_id_arr[3];}
					if($('hint4')) {$('hint4').innerHTML = "Integer - input a number";}	
					} else if (type4 == "avg") {
					if($('stats3_inner')) {$('stats3_inner').innerHTML = the_id_arr[3].timeLeft();}
					if($('hint4')) {$('hint4').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
					} else {
					if($('hint4')) {$('hint4').innerHTML = "Not Used";}	
					}
				if(type5 == "int") {					
					if($('stats4_inner')) {$('stats4_inner').innerHTML = the_id_arr[4];}
					if($('hint5')) {$('hint5').innerHTML = "Integer - input a number";}					
					} else if (type5 == "avg") {
					if($('stats4_inner')) {$('stats4_inner').innerHTML = the_id_arr[4].timeLeft();}
					if($('hint5')) {$('hint5').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
					} else {
					if($('hint5')) {$('hint5').innerHTML = "Not Used";}	
					}
				if(type6 == "int") {
					if($('stats5_inner')) {$('stats5_inner').innerHTML = the_id_arr[5];}
					if($('hint6')) {$('hint6').innerHTML = "Integer - input a number";}					
					} else if (type6 == "avg") {
					if($('stats5_inner')) {$('stats5_inner').innerHTML = the_id_arr[5].timeLeft();}
					if($('hint6')) {$('hint6').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
					} else {
					if($('hint6')) {$('hint6').innerHTML = "Not Used";}	
					}
				if(type7 == "int") {				
					if($('stats6_inner')) {$('stats6_inner').innerHTML = the_id_arr[6];}
					if($('hint7')) {$('hint7').innerHTML = "Integer - input a number";}					
					} else if (type7 == "avg") {
					if($('stats6_inner')) {$('stats6_inner').innerHTML = the_id_arr[6].timeLeft();}
					if($('hint7')) {$('hint7').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
					} else {
					if($('hint7')) {$('hint7').innerHTML = "Not Used";}	
					}
				if(type8 == "int") {				
					if($('stats7_inner')) {$('stats7_inner').innerHTML = the_id_arr[7];}
					if($('hint8')) {$('hint8').innerHTML = "Integer - input a number";}					
					} else if (type8 == "avg") {
					if($('stats7_inner')) {$('stats7_inner').innerHTML = the_id_arr[7].timeLeft();}
					if($('hint8')) {$('hint8').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
					} else {
					if($('hint8')) {$('hint8').innerHTML = "Not Used";}
					}
				if($('stats8_inner')) {$('stats8_inner').innerHTML = "Current date and time: " + the_id_arr[8]};				
			}			// end function get_statistics_cb()		

		function toHex(x) {
			hex="0123456789ABCDEF";almostAscii=' !"#$%&'+"'"+'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ['+'\\'+']^_`abcdefghijklmnopqrstuvwxyz{|}';r="";
			for(i=0;i<x.length;i++){
				let=x.charAt(i);pos=almostAscii.indexOf(let)+32;
				h16=Math.floor(pos/16);h1=pos%16;r+=hex.charAt(h16)+hex.charAt(h1);
				};
			return r;
			};

		function mu_get() {								// set cycle
			if (mu_interval!=null) {return;}			// ????
			mu_interval = window.setInterval('do_loop()', <?php print $refresh_time;?>);		// 4/7/10
			}			// end function mu get()


		function mu_init() {								// get initial values from server -  4/7/10
			if (is_initialized) { return; }
			is_initialized = true;

			sendRequest ('./ajax/statistics.php?user=<?php print $userid;?>',init_cb, "");			
				function init_cb(req) {
					var the_id_arr=JSON.decode(req.responseText);
					if (the_id_arr.length != 9)  {
						alert("server error at <?php print basename(__FILE__) . " " . __LINE__;?> ");
						}
					else {
						out_threshold(the_id_arr[0], "stats0", t1, tw1, tf1, tt1);
						out_threshold(the_id_arr[1], "stats1", t2, tw2, tf2, tt2);
						out_threshold(the_id_arr[2], "stats2", t3, tw3, tf3, tt3);
						out_threshold(the_id_arr[3], "stats3", t4, tw4, tf4, tt4);
						out_threshold(the_id_arr[4], "stats4", t5, tw5, tf5, tt5);
						out_threshold(the_id_arr[5], "stats5", t6, tw6, tf6, tt6);
						out_threshold(the_id_arr[6], "stats6", t7, tw7, tf7, tt7);
						out_threshold(the_id_arr[7], "stats7", t8, tw8, tf8, tt8);
						if(type1 == "int") {
							if($('stats0_inner')) {$('stats0_inner').innerHTML = the_id_arr[0];}
							if($('hint1')) {$('hint1').innerHTML = "Integer - input a number";}				
							} else {
							if($('stats0_inner')) {$('stats0_inner').innerHTML = the_id_arr[0].timeLeft();}
							if($('hint1')) {$('hint1').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
							}
						if(type2 == "int") {				
							if($('stats1_inner')) {$('stats1_inner').innerHTML = the_id_arr[1];}
							if($('hint2')) {$('hint2').innerHTML = "Integer - input a number";}					
							} else {
							if($('stats1_inner')) {$('stats1_inner').innerHTML = the_id_arr[1].timeLeft();}	
							if($('hint2')) {$('hint2').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
							}
						if(type3 == "int") {				
							if($('stats2_inner')) {$('stats2_inner').innerHTML = the_id_arr[2];}
							if($('hint3')) {$('hint3').innerHTML = "Integer - input a number";}					
							} else {
							if($('stats2_inner')) {$('stats2_inner').innerHTML = the_id_arr[2].timeLeft();}
							if($('hint3')) {$('hint3').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
							}
						if(type4 == "int") {				
							if($('stats3_inner')) {$('stats3_inner').innerHTML = the_id_arr[3];}
							if($('hint4')) {$('hint4').innerHTML = "Integer - input a number";}	
							} else {
							if($('stats3_inner')) {$('stats3_inner').innerHTML = the_id_arr[3].timeLeft();}
							if($('hint4')) {$('hint4').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
							}
						if(type5 == "int") {					
							if($('stats4_inner')) {$('stats4_inner').innerHTML = the_id_arr[4];}
							if($('hint5')) {$('hint5').innerHTML = "Integer - input a number";}					
							} else {
							if($('stats4_inner')) {$('stats4_inner').innerHTML = the_id_arr[4].timeLeft();}
							if($('hint5')) {$('hint5').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
							}
						if(type6 == "int") {
							if($('stats5_inner')) {$('stats5_inner').innerHTML = the_id_arr[5];}
							if($('hint6')) {$('hint6').innerHTML = "Integer - input a number";}					
							} else {
							if($('stats5_inner')) {$('stats5_inner').innerHTML = the_id_arr[5].timeLeft();}
							if($('hint6')) {$('hint6').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
							}
						if(type7 == "int") {				
							if($('stats6_inner')) {$('stats6_inner').innerHTML = the_id_arr[6];}
							if($('hint7')) {$('hint7').innerHTML = "Integer - input a number";}					
							} else {
							if($('stats6_inner')) {$('stats6_inner').innerHTML = the_id_arr[6].timeLeft(); }
							if($('hint7')) {$('hint7').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
							}
						if(type8 == "int") {				
							if($('stats7_inner')) {$('stats7_inner').innerHTML = the_id_arr[7];}
							if($('hint8')) {$('hint8').innerHTML = "Integer - input a number";}					
							} else {
							if($('stats7_inner')) {$('stats7_inner').innerHTML = the_id_arr[7].timeLeft();}
							if($('hint8')) {$('hint8').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
							}
						if($('stats8_inner')) {$('stats8_inner').innerHTML = "Current date and time: " + the_id_arr[8]};				
						}
					mu_get();				// start loop
					}				// end function init_cb()
			}				// end function mu_init()
		</SCRIPT>
		</HEAD>
		<BODY onLoad = "out_frames(); location.href = '#top'; <?php print $do_mu_init;?>">
		<A NAME="top" />	
<?php
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_settings` WHERE `user_id` = {$_SESSION['user_id']}";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$name1 = get_stat_type_name($row['f1']);
		$name2 = get_stat_type_name($row['f2']);
		$name3 = get_stat_type_name($row['f3']);
		$name4 = get_stat_type_name($row['f4']);
		$name5 = get_stat_type_name($row['f5']);
		$name6 = get_stat_type_name($row['f6']);
		$name7 = get_stat_type_name($row['f7']);
		$name8 = get_stat_type_name($row['f8']);
?>
		<DIV class='header_wrapper'>
			<DIV class='header_row'>
				<DIV class='page_heading'>
					<IMG SRC="<?php print get_variable('logo');?>" BORDER=0 />
					<SPAN class='page_heading text_biggest' style='display: inline;'><?php print get_variable('title_string'); ?> - Statistics</SPAN>
					<SPAN class='page_heading_s text_biggest' style='display: inline;'></SPAN>
					<SPAN id='stats8_inner' class='date_time text text_right' style='display: inline; vertical-align: bottom;'></SPAN>
				</DIV>
			</DIV>
			<DIV class='header_row'>
				<DIV class='button_bar'>
					<SPAN id='links' class='plain text' style='float: right; display: inline;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onclick="window.location='stats_scr.php?config=config' ">Configuration</SPAN>
					<SPAN ID='gout' CLASS='plain text' style='float: right; display: inline;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_logout()">Logout</SPAN>
				</DIV>
			</DIV>
		</DIV>
		<DIV id='statistics' class='stats_wrapper'>
<?php 
		if($f1 != 0) { 
?>
			<DIV id='stats0' class='stats_outer'>
				<DIV class='stats_heading'><?php print $name1;?></DIV>
				<DIV id='stats0_inner' class='stats_inner'></DIV>
			</DIV>
<?php 
		}
		if($f2 != 0) { 
?>
			<DIV id='stats1' class='stats_outer'>
				<DIV class='stats_heading'><?php print $name2;?></DIV>
				<DIV id='stats1_inner' class='stats_inner'></DIV>
			</DIV>
<?php 
		} 
		if($f3 != 0) { 
?>		
			<DIV id='stats2' class='stats_outer'>
				<DIV class='stats_heading'><?php print $name3;?></DIV>
				<DIV id='stats2_inner' class='stats_inner'></DIV>
			</DIV>
<?php 
		} 
		if($f4 != 0) { 
?>		
			<DIV id='stats3' class='stats_outer'>
				<DIV class='stats_heading'><?php print $name4;?></DIV>
				<DIV id='stats3_inner' class='stats_inner'></DIV>
			</DIV>
<?php 
		} 
		if($f5 != 0) { 
?>
			<DIV id='stats4' class='stats_outer'>
				<DIV class='stats_heading'><?php print $name5;?></DIV>
				<DIV id='stats4_inner' class='stats_inner'></DIV>
			</DIV>
<?php 
		} 
		if($f6 != 0) { 
?>
			<DIV id='stats5' class='stats_outer'>
				<DIV class='stats_heading'><?php print $name6;?></DIV>
				<DIV id='stats5_inner' class='stats_inner'></DIV>
			</DIV>
<?php 
		} if($f7 != 0) { 
		?>		
			<DIV id='stats6' class='stats_outer'>
				<DIV class='stats_heading'><?php print $name7;?></DIV>
				<DIV id='stats6_inner' class='stats_inner'></DIV>
			</DIV>
<?php 
		} 
		if($f8 != 0) { 
?>		
			<DIV id='stats7' class='stats_outer'>
				<DIV class='stats_heading'><?php print $name8;?></DIV>
				<DIV id='stats7_inner' class='stats_inner'></DIV>
			</DIV>
<?php 
		} 
?>		
		</DIV>	
		<A NAME="bottom" />
		</BODY>
		</HTML>
<?php
		}	//	end if / else found_user == 0
	}	//	End if stats=stats.
	
if (((isset($_GET['config'])) && ($_GET['config'] == "config"))) {
?>
<SCRIPT>


	var is_initialized = false;
	var mu_interval = 10000;
<?php

	function makeSeconds($a) {
		$string = explode("-", $a);
		$days = $string[0];
		$hours = $string[1];
		$minutes = $string[2];
		$secs = $string[3];
		$days2 = $days * 24 * 60 * 60;
		$hours2 = $hours * 60 * 60;
		$minutes2 = $minutes * 60;
		$seconds = $hours2 + $days2 + $minutes2 + $secs;
		return $seconds;
		}
		
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_settings` WHERE `user_id` = {$_SESSION['user_id']}";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$refresh_time = (isset($row['refresh_rate'])) ? ($row['refresh_rate'] * 1000) : 1000;
	$rr = found_user() ? $row['refresh_rate'] : 30;
	$f1 = found_user() ? $row['f1'] : 1;
	$f2 = found_user() ? $row['f2'] : 2;
	$f3 = found_user() ? $row['f3'] : 3;
	$f4 = found_user() ? $row['f4'] : 4;
	$f5 = found_user() ? $row['f5'] : 5;
	$f6 = found_user() ? $row['f6'] : 6;
	$f7 = found_user() ? $row['f7'] : 7;
	$f8 = found_user() ? $row['f8'] : 8;
	$type1 = get_stat_type_type($f1);
	$type2 = get_stat_type_type($f2);
	$type3 = get_stat_type_type($f3);
	$type4 = get_stat_type_type($f4);
	$type5 = get_stat_type_type($f5);
	$type6 = get_stat_type_type($f6);
	$type7 = get_stat_type_type($f7);
	$type8 = get_stat_type_type($f8);		
	$t1 = found_user() ? $row['threshold_1'] : 0;
	$t2 = found_user() ? $row['threshold_2'] : 0;
	$t3 = found_user() ? $row['threshold_3'] : 0;
	$t4 = found_user() ? $row['threshold_4'] : 0;
	$t5 = found_user() ? $row['threshold_5'] : 0;
	$t6 = found_user() ? $row['threshold_6'] : 0;
	$t7 = found_user() ? $row['threshold_7'] : 0;
	$t8 = found_user() ? $row['threshold_8'] : 0;
	$tw1 = found_user() ? $row['thresholdw_1'] : 0;
	$tw2 = found_user() ? $row['thresholdw_2'] : 0;
	$tw3 = found_user() ? $row['thresholdw_3'] : 0;
	$tw4 = found_user() ? $row['thresholdw_4'] : 0;
	$tw5 = found_user() ? $row['thresholdw_5'] : 0;
	$tw6 = found_user() ? $row['thresholdw_6'] : 0;
	$tw7 = found_user() ? $row['thresholdw_7'] : 0;
	$tw8 = found_user() ? $row['thresholdw_8'] : 0;	
	$tf1 = found_user() ? $row['thresholdf_1'] : 0;
	$tf2 = found_user() ? $row['thresholdf_2'] : 0;
	$tf3 = found_user() ? $row['thresholdf_3'] : 0;
	$tf4 = found_user() ? $row['thresholdf_4'] : 0;
	$tf5 = found_user() ? $row['thresholdf_5'] : 0;
	$tf6 = found_user() ? $row['thresholdf_6'] : 0;
	$tf7 = found_user() ? $row['thresholdf_7'] : 0;
	$tf8 = found_user() ? $row['thresholdf_8'] : 0;	
	$tt1 = found_user() ? $row['t_type1'] : "More";
	$tt2 = found_user() ? $row['t_type2'] : "More";
	$tt3 = found_user() ? $row['t_type3'] : "More";
	$tt4 = found_user() ? $row['t_type4'] : "More";
	$tt5 = found_user() ? $row['t_type5'] : "More";
	$tt6 = found_user() ? $row['t_type6'] : "More";
	$tt7 = found_user() ? $row['t_type7'] : "More";
	$tt8 = found_user() ? $row['t_type8'] : "More";			
?>
	var type1 = "<?php print $type1;?>";
	var type2 = "<?php print $type2;?>";
	var type3 = "<?php print $type3;?>";
	var type4 = "<?php print $type4;?>";
	var type5 = "<?php print $type5;?>";
	var type6 = "<?php print $type6;?>";
	var type7 = "<?php print $type7;?>";
	var type8 = "<?php print $type8;?>";
	
	function set_init_cfg() {
		if(type1 == "int") {
			if($('hint1')) {$('hint1').innerHTML = "Integer - input a number";}				
			} else if (type1 == "avg") {
			if($('hint1')) {$('hint1').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
			} else {
			if($('hint1')) {$('hint1').innerHTML = "Not Used";}
			}
		if(type2 == "int") {				
			if($('hint2')) {$('hint2').innerHTML = "Integer - input a number";}					
			} else if (type2 == "avg") {
			if($('hint2')) {$('hint2').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
			} else {
			if($('hint2')) {$('hint2').innerHTML = "Not Used";}
			}
		if(type3 == "int") {				
			if($('hint3')) {$('hint3').innerHTML = "Integer - input a number";}					
			} else if (type3 == "avg") {
			if($('hint3')) {$('hint3').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
			} else {
			if($('hint3')) {$('hint3').innerHTML = "Not Used";}
			}
		if(type4 == "int") {				
			if($('hint4')) {$('hint4').innerHTML = "Integer - input a number";}	
			} else if (type4 == "avg") {
			if($('hint4')) {$('hint4').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
			} else {
			if($('hint4')) {$('hint4').innerHTML = "Not Used";}	
			}
		if(type5 == "int") {					
			if($('hint5')) {$('hint5').innerHTML = "Integer - input a number";}					
			} else if (type5 == "avg") {
			if($('hint5')) {$('hint5').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
			} else {
			if($('hint5')) {$('hint5').innerHTML = "Not Used";}	
			}
		if(type6 == "int") {
			if($('hint6')) {$('hint6').innerHTML = "Integer - input a number";}					
			} else if (type6 == "avg") {
			if($('hint6')) {$('hint6').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}	
			} else {
			if($('hint6')) {$('hint6').innerHTML = "Not Used";}	
			}
		if(type7 == "int") {				
			if($('hint7')) {$('hint7').innerHTML = "Integer - input a number";}					
			} else if (type7 == "avg") {
			if($('hint7')) {$('hint7').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
			} else {
			if($('hint7')) {$('hint7').innerHTML = "Not Used";}
			}
		if(type8 == "int") {				
			if($('hint8')) {$('hint8').innerHTML = "Integer - input a number";}					
			} else if (type8 == "avg") {
			if($('hint8')) {$('hint8').innerHTML = "Date time, input as Days-Hours-Minutes-Seconds";}					
			} else {
			if($('hint8')) {$('hint8').innerHTML = "Not Used";}	
			}
		}			// end function get_statistics_cb()			
</SCRIPT>
</HEAD>
<BODY onLoad = "out_frames(); location.href = '#top'; set_init_cfg();">
<A NAME="top" />

	<DIV class='header_wrapper'>
		<DIV class='header_row'>
			<DIV class='page_heading'>
				<IMG SRC="<?php print get_variable('logo');?>" BORDER=0 />
				<SPAN class='page_heading text_biggest' style='display: inline;'><?php print get_variable('title_string'); ?> - Statistics configuration</SPAN>
				<SPAN class='page_heading_s text_biggest' style='display: inline;'></SPAN>
				<SPAN id='stats8_inner' class='date_time text text_right' style='display: inline; vertical-align: bottom;'>Current date and time: <?php print date("D M j Y G:i:s", time());?></SPAN>
			</DIV>
			<DIV class='button_bar'>
				<SPAN id='links' class='plain text' style='float: right; display: inline;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onclick="window.location='stats_scr.php?stats=stats' ">Statistics</SPAN>
				<SPAN ID='gout' class='plain text' style='float: right; display: inline;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_logout()">Logout</SPAN>
			</DIV>
		</DIV>
	</DIV>
	<DIV id='config' class='config_wrapper'>
		<FORM NAME="stats_config" METHOD="post" ACTION="stats_scr.php?fm_sub=true" />
		<INPUT NAME="frm_user" TYPE="hidden" VALUE=<?php print $_SESSION['user_id'];?>>
		<DIV class='config_row'>
			<DIV class='config_cell_heading' style='width: 15%;'>Setting</DIV>
			<DIV class='config_cell_heading' style='width: 15%;'>Value</DIV>
			<DIV class='config_cell_heading' style='width: 10%;'>Threshold</DIV>
			<DIV class='config_cell_heading' style='width: 10%;'>Threshold Warn</DIV>
			<DIV class='config_cell_heading' style='width: 10%;'>Threshold Flag</DIV>
			<DIV class='config_cell_heading' style='width: 20%;'>Stats Type</DIV>
			<DIV class='config_cell_heading' style='width: 20%;'>Threshold Type</DIV>
		</DIV>
		<DIV class='config_row'>
			<DIV class='config_cell_title'>Refresh Rate (Seconds)</DIV>
			<DIV class='config_cell_data'><INPUT MAXLENGTH="2" SIZE="3" type="text" NAME="frm_refresh" VALUE="<?php print $rr;?>"></DIV>
			<DIV class='config_cell_data'>&nbsp;</DIV>
			<DIV class='config_cell_data'>&nbsp;</DIV>
			<DIV class='config_cell_data'>&nbsp;</DIV>
			<DIV class='config_cell_data'>&nbsp;</DIV>
			<DIV class='config_cell_data'>&nbsp;</DIV>			
		</DIV>
<?php
	$menu1 = "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` ORDER BY `st_id` ASC";
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
		$sel = ($row1['st_id'] == $f1) ? "SELECTED" : "";
		$menu1 .= "<OPTION VALUE='{$row1['st_id']}' {$sel}>{$row1['name']}</OPTION>";
		}
		$menu1 .= "</SELECT>";
	
	$sel3 = ($tt1 == 0) ? "SELECTED" : "";
	$menu2 = "<OPTION VALUE=0 {$sel3}>Select</OPTION>";
	$choices = array();
	$choices[0] = "Less";
	$choices[1] = "Less or Equal";
	$choices[2] = "Equal";
	$choices[3] = "More or Equal";
	$choices[4] = "More";	
	foreach($choices as $value) {
		$sel2 = ($tt1 == $value) ? "SELECTED" : "";
		$menu2 .= "<OPTION VALUE='$value' {$sel2}>{$value}</OPTION>";
		}
		$menu2 .= "</SELECT>";
		
?>
		<DIV class='config_row'>
			<DIV class='config_cell_title' style='width: 15%;'>Box 1</DIV>
			<DIV class='config_cell_data' style='width: 15%;'><SELECT NAME="frm_box1" onChange='set_hint(this.form, "frm_box1", "hint1")'><?php print $menu1;?></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_t1" VALUE="<?php print ($type1 == 'avg') ? parsedate($t1) : $t1;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tw1" VALUE="<?php print ($type1 == 'avg') ? parsedate($tw1) : $tw1;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tf1" VALUE="<?php print ($type1 == 'avg') ? parsedate($tf1) : $tf1;;?>"></DIV>
			<DIV id='hint1' class='config_cell_hint' style='width: 20%;'></DIV>	
			<DIV class='config_cell_data' style='width: 20%;'><SELECT NAME='frm_t_type1'><?php print $menu2;?></DIV>
		</DIV>
<?php
	$menu1 = "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` ORDER BY `st_id` ASC";
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
		$sel = ($row1['st_id'] == $f2) ? "SELECTED" : "";
		$menu1 .= "<OPTION VALUE='{$row1['st_id']}' {$sel}>{$row1['name']}</OPTION>";
		}
		$menu1 .= "</SELECT>";

	$sel3 = ($tt2 == 0) ? "SELECTED" : "";
	$menu2 = "<OPTION VALUE=0 {$sel3}>Select</OPTION>";		
	$choices = array();
	$choices[0] = "Less";
	$choices[1] = "Less or Equal";
	$choices[2] = "Equal";
	$choices[3] = "More or Equal";
	$choices[4] = "More";	
	foreach($choices as $value) {
		$sel2 = ($tt2 == $value) ? "SELECTED" : "";
		$menu2 .= "<OPTION VALUE='$value' {$sel2}>{$value}</OPTION>";
		}
		$menu2 .= "</SELECT>";		
?>		
		<DIV class='config_row'>
			<DIV class='config_cell_title' style='width: 15%;'>Box 2</DIV>
			<DIV class='config_cell_data' style='width: 15%;'><SELECT NAME="frm_box2" onChange='set_hint(this.form, "frm_box2", "hint2")'><?php print $menu1;?></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_t2" VALUE="<?php print ($type2 == 'avg') ? parsedate($t2) : $t2;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tw2" VALUE="<?php print ($type2 == 'avg') ? parsedate($tw2) : $tw2;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tf2" VALUE="<?php print ($type2 == 'avg') ? parsedate($tf2) : $tf2;?>"></DIV>	
			<DIV id='hint2' class='config_cell_hint' style='width: 20%;'></DIV>	
			<DIV class='config_cell_data' style='width: 20%;'><SELECT NAME='frm_t_type2'><?php print $menu2;?></DIV>
		</DIV>	
<?php
	$menu1 = "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` ORDER BY `st_id` ASC";
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
		$sel = ($row1['st_id'] == $f3) ? "SELECTED" : "";
		$menu1 .= "<OPTION VALUE='{$row1['st_id']}' {$sel}>{$row1['name']}</OPTION>";
		}
		$menu1 .= "</SELECT>";

	$sel3 = ($tt3 == 0) ? "SELECTED" : "";
	$menu2 = "<OPTION VALUE=0 {$sel3}>Select</OPTION>";
	$choices = array();
	$choices[0] = "Less";
	$choices[1] = "Less or Equal";
	$choices[2] = "Equal";
	$choices[3] = "More or Equal";
	$choices[4] = "More";	
	foreach($choices as $value) {
		$sel2 = ($tt3 == $value) ? "SELECTED" : "";
		$menu2 .= "<OPTION VALUE='$value' {$sel2}>{$value}</OPTION>";
		}
		$menu2 .= "</SELECT>";
?>		
		<DIV class='config_row'>
			<DIV class='config_cell_title' style='width: 15%;'>Box 3</DIV>
			<DIV class='config_cell_data' style='width: 15%;'><SELECT NAME="frm_box3" onChange='set_hint(this.form, "frm_box3", "hint3")'><?php print $menu1;?></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_t3" VALUE="<?php print ($type3 == 'avg') ? parsedate($t3) : $t3;?>"></DIV>	
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tw3" VALUE="<?php print ($type3 == 'avg') ? parsedate($tw3) : $tw3;?>"></DIV>	
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tf3" VALUE="<?php print ($type3 == 'avg') ? parsedate($tf3) : $tf3;?>"></DIV>
			<DIV id='hint3' class='config_cell_hint' style='width: 20%;'></DIV>			
			<DIV class='config_cell_data' style='width: 20%;'><SELECT NAME='frm_t_type3'><?php print $menu2;?></DIV>
		</DIV>
<?php
	$menu1 = "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` ORDER BY `st_id` ASC";
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
		$sel = ($row1['st_id'] == $f4) ? "SELECTED" : "";
		$menu1 .= "<OPTION VALUE='{$row1['st_id']}' {$sel}>{$row1['name']}</OPTION>";
		}
		$menu1 .= "</SELECT>";
		
	$sel3 = ($tt4 == 0) ? "SELECTED" : "";
	$menu2 = "<OPTION VALUE=0 {$sel3}>Select</OPTION>";
	$choices = array();
	$choices[0] = "Less";
	$choices[1] = "Less or Equal";
	$choices[2] = "Equal";
	$choices[3] = "More or Equal";
	$choices[4] = "More";	
	foreach($choices as $value) {
		$sel2 = ($tt4 == $value) ? "SELECTED" : "";
		$menu2 .= "<OPTION VALUE='$value' {$sel2}>{$value}</OPTION>";
		}
		$menu2 .= "</SELECT>";
?>		
		<DIV class='config_row'>
			<DIV class='config_cell_title' style='width: 15%;'>Box 4</DIV>
			<DIV class='config_cell_data' style='width: 15%;'><SELECT NAME="frm_box4" onChange='set_hint(this.form, "frm_box4", "hint4")'><?php print $menu1;?></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_t4" VALUE="<?php print ($type4 == 'avg') ? parsedate($t4) : $t4;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tw4" VALUE="<?php print ($type4 == 'avg') ? parsedate($tw4) : $tw4;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tf4" VALUE="<?php print ($type4 == 'avg') ? parsedate($tf4) : $tf4;?>"></DIV>
			<DIV id='hint4' class='config_cell_hint' style='width: 20%;'></DIV>			
			<DIV class='config_cell_data' style='width: 20%;'><SELECT NAME='frm_t_type4'><?php print $menu2;?></DIV>
		</DIV>
<?php
	$menu1 = "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` ORDER BY `st_id` ASC";
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
		$sel = ($row1['st_id'] == $f5) ? "SELECTED" : "";
		$menu1 .= "<OPTION VALUE='{$row1['st_id']}' {$sel}>{$row1['name']}</OPTION>";
		}
		$menu1 .= "</SELECT>";

	$sel3 = ($tt5 == 0) ? "SELECTED" : "";
	$menu2 = "<OPTION VALUE=0 {$sel3}>Select</OPTION>";
	$choices = array();
	$choices[0] = "Less";
	$choices[1] = "Less or Equal";
	$choices[2] = "Equal";
	$choices[3] = "More or Equal";
	$choices[4] = "More";	
	foreach($choices as $value) {
		$sel2 = ($tt5 == $value) ? "SELECTED" : "";
		$menu2 .= "<OPTION VALUE='$value' {$sel2}>{$value}</OPTION>";
		}
		$menu2 .= "</SELECT>";
?>		
		<DIV class='config_row'>
			<DIV class='config_cell_title' style='width: 15%;'>Box 5</DIV>
			<DIV class='config_cell_data' style='width: 15%;'><SELECT NAME="frm_box5" onChange='set_hint(this.form, "frm_box5", "hint5")'><?php print $menu1;?></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_t5" VALUE="<?php print ($type5 == 'avg') ? parsedate($t5) : $t5;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tw5" VALUE="<?php print ($type5 == 'avg') ? parsedate($tw5) : $tw5;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tf5" VALUE="<?php print ($type5 == 'avg') ? parsedate($tf5) : $tf5;?>"></DIV>
			<DIV id='hint5' class='config_cell_hint' style='width: 20%;'></DIV>			
			<DIV class='config_cell_data' style='width: 20%;'><SELECT NAME='frm_t_type5'><?php print $menu2;?></DIV>
		</DIV>
<?php
	$menu1 = "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` ORDER BY `st_id` ASC";
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
		$sel = ($row1['st_id'] == $f6) ? "SELECTED" : "";
		$menu1 .= "<OPTION VALUE='{$row1['st_id']}' {$sel}>{$row1['name']}</OPTION>";
		}
		$menu1 .= "</SELECT>";

	$sel3 = ($tt6 == 0) ? "SELECTED" : "";
	$menu2 = "<OPTION VALUE=0 {$sel3}>Select</OPTION>";
	$choices = array();
	$choices[0] = "Less";
	$choices[1] = "Less or Equal";
	$choices[2] = "Equal";
	$choices[3] = "More or Equal";
	$choices[4] = "More";	
	foreach($choices as $value) {
		$sel2 = ($tt6 == $value) ? "SELECTED" : "";
		$menu2 .= "<OPTION VALUE='$value' {$sel2}>{$value}</OPTION>";
		}
		$menu2 .= "</SELECT>";
?>		
		<DIV class='config_row'>
			<DIV class='config_cell_title' style='width: 15%;'>Box 6</DIV>
			<DIV class='config_cell_data' style='width: 15%;'><SELECT NAME="frm_box6" onChange='set_hint(this.form, "frm_box6", "hint6")'><?php print $menu1;?></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_t6" VALUE="<?php print ($type6 == 'avg') ? parsedate($t6) : $t6;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tw6" VALUE="<?php print ($type6 == 'avg') ? parsedate($tw6) : $tw6;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tf6" VALUE="<?php print ($type6 == 'avg') ? parsedate($tf6) : $tf6;?>"></DIV>
			<DIV id='hint6' class='config_cell_hint' style='width: 20%;'></DIV>			
			<DIV class='config_cell_data' style='width: 20%;'><SELECT NAME='frm_t_type6'><?php print $menu2;?></DIV>
		</DIV>
<?php
	$menu1 = "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` ORDER BY `st_id` ASC";
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
		$sel = ($row1['st_id'] == $f7) ? "SELECTED" : "";
		$menu1 .= "<OPTION VALUE='{$row1['st_id']}' {$sel}>{$row1['name']}</OPTION>";
		}
		$menu1 .= "</SELECT>";

	$sel3 = ($tt7 == 0) ? "SELECTED" : "";
	$menu2 = "<OPTION VALUE=0 {$sel3}>Select</OPTION>";
	$choices = array();
	$choices[0] = "Less";
	$choices[1] = "Less or Equal";
	$choices[2] = "Equal";
	$choices[3] = "More or Equal";
	$choices[4] = "More";	
	foreach($choices as $value) {
		$sel2 = ($tt7 == $value) ? "SELECTED" : "";
		$menu2 .= "<OPTION VALUE='$value' {$sel2}>{$value}</OPTION>";
		}
		$menu2 .= "</SELECT>";
?>		
		<DIV class='config_row'>
			<DIV class='config_cell_title' style='width: 15%;'>Box 7</DIV>
			<DIV class='config_cell_data' style='width: 15%;'><SELECT NAME="frm_box7" onChange='set_hint(this.form, "frm_box7", "hint7")'><?php print $menu1;?></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_t7" VALUE="<?php print ($type7 == 'avg') ? parsedate($t7) : $t7;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tw7" VALUE="<?php print ($type7 == 'avg') ? parsedate($tw7) : $tw7;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tf7" VALUE="<?php print ($type7 == 'avg') ? parsedate($tf7) : $tf7;?>"></DIV>
			<DIV id='hint7' class='config_cell_hint' style='width: 20%;'></DIV>
			<DIV class='config_cell_data' style='width: 20%;'><SELECT NAME='frm_t_type7'><?php print $menu2;?></DIV>
		</DIV>
<?php
	$menu1 = "<OPTION VALUE=0 SELECTED>Select</OPTION>";
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]stats_type` ORDER BY `st_id` ASC";
	$result1 = mysql_query($query1) or do_error($query1, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row1 = stripslashes_deep(mysql_fetch_assoc($result1))) {
		$sel = ($row1['st_id'] == $f8) ? "SELECTED" : "";
		$menu1 .= "<OPTION VALUE='{$row1['st_id']}' {$sel}>{$row1['name']}</OPTION>";
		}
		$menu1 .= "</SELECT>";

	$sel3 = ($tt8 == 0) ? "SELECTED" : "";
	$menu2 = "<OPTION VALUE=0 {$sel3}>Select</OPTION>";
	$choices = array();
	$choices[0] = "Less";
	$choices[1] = "Less or Equal";
	$choices[2] = "Equal";
	$choices[3] = "More or Equal";
	$choices[4] = "More";	
	foreach($choices as $value) {
		$sel2 = ($tt8 == $value) ? "SELECTED" : "";
		$menu2 .= "<OPTION VALUE='$value' {$sel2}>{$value}</OPTION>";
		}
		$menu2 .= "</SELECT>";
?>		
		<DIV class='config_row'>
			<DIV class='config_cell_title' style='width: 15%;'>Box 8</DIV>
			<DIV class='config_cell_data' style='width: 15%;'><SELECT NAME="frm_box8" onChange='set_hint(this.form, "frm_box8", "hint8")'><?php print $menu1;?></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_t8" VALUE="<?php print ($type8 == 'avg') ? parsedate($t8) : $t8;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tw8" VALUE="<?php print ($type8 == 'avg') ? parsedate($tw8) : $tw8;?>"></DIV>
			<DIV class='config_cell_data' style='width: 10%;'><INPUT MAXLENGTH="12" SIZE="12" type="text" NAME="frm_tf8" VALUE="<?php print ($type8 == 'avg') ? parsedate($tf8) : $tf8;?>"></DIV>
			<DIV id='hint8' class='config_cell_hint' style='width: 20%;'></DIV>			
			<DIV class='config_cell_data' style='width: 20%;'><SELECT NAME='frm_t_type8'><?php print $menu2;?></DIV>
		</DIV>
		</FORM>
		<DIV class='config_row'>
			<DIV class='config_cell_butts' style='width: 96%; text-align: center;'><INPUT TYPE="button" VALUE="Cancel"  onClick="window.location='stats_scr.php?stats=stats'">
			&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="button" VALUE="Submit" onClick="document.stats_config.submit()">
			</DIV>
		</DIV> 

	</DIV>
	<A NAME="bottom" />
	</BODY>
	</HTML>	
<?php
}
if((!isset($_GET['stats'])) && (!isset($_GET['config'])) && (!isset($_GET['fm_sub']))) {
?>
	<DIV class='header_wrapper'>
		<DIV class='header_row'>
			<DIV class='page_heading'>TICKETS CAD Statistics Module</DIV>
		</DIV>
		<DIV class='header_row'>
			<DIV id='stats8_inner' class='date_time'></DIV>
			<DIV class='button_bar'>
				<SPAN id='links' class='plain text' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onclick="window.location='stats_scr.php?stats=stats' ">Statistics</SPAN>			
				<SPAN id='links' class='plain text' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onclick="window.location='stats_scr.php?config=config' ">Configuration</SPAN>
				<SPAN ID='gout' CLASS='plain text' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_logout()">Logout</SPAN>
			</DIV>
		</DIV>
	</DIV>
	<DIV id='error_text' class='error_page'>
	You are seeing this page because you arrived here in error. <BR />Please log in to Tickets as a Statistics user and you will automatically <BR />be taken to the Statistics page appropriate for your login ID
	</DIV>
<?php
}
?>
<FORM METHOD='POST' NAME="gout_form" action="index.php">
<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
</FORM>
</BODY>
</HTML>
