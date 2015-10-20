<?php
header('Content-type: text/css');
/* 
9/10/13 - new file, the stylesheet for the mobile page
*/
require_once('../../incs/functions.inc.php');
session_start();
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
$alt_day_night = ($day_night=="Day") ? "Night" : "Day"; 
?>
TABLE {
	border-collapse: collapse; 
	}
	
INPUT { 
	background-color: <?php print get_css("form_input_background", $day_night);?>;
	font-weight: normal; 
	font-size: 1em; 
	color: <?php print get_css("form_input_text", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

TEXTAREA { 
	background-color: <?php print get_css("form_input_background", $day_night);?>;
	font-weight: normal; 
	font-size: 1em; 
	color: <?php print get_css("form_input_text", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

SELECT {
	background-color: <?php print get_css("select_menu_background", $day_night);?>; 
	font-weight: normal; 
	font-size: 1em; 
	color: <?php print get_css("select_menu_text", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: underline; 
	}
	
A { 
	font-weight: bold; 
	font-size: 1em; 
	color: <?php print get_css("links", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}

li.mylink { 
	font-weight: bold; 
	font-size: 1.2em; 
	color: <?php print get_css("links", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}

TD { 
	background-color: inherit; 
	font-weight: inherit; 
	font-size: inherit; 
	color: inherit; 
	font-style: inherit; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	VERTICAL-ALIGN: top;  
	}
	
.print_TD { 
	background-color: #FFFFFF; 
	font-weight: normal; 
	font-size: 1em; 
	color: #000000; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
.td_label { 
	background-color: inherit;
	color: <?php print get_css("label_text", $day_night);?>; 
	font-weight: bold; 
	font-size: 1em; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.td_fs_buttons {
	background-color: <?php print get_css("page_background", $day_night);?>;
	color: <?php print get_css("normal_text", $day_night);?>
	}

.fs_buttons {
	font-weight: bold; 
	font-size: 1.2em; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}	
	
.td_mand { 
	font-weight: bold; 
	font-size: 1em; 
	color: #CC0000; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
.td_data { 
	white-space:nowrap; 
	background-color: inherit;
	font-size: 1em; 
	color: #000000; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
#td_header { 
	font-weight: bold; 
	font-size: 1.2em; 
	color: <?php print get_css("header_text", $day_night);?>;
	background-color: <?php print get_css("header_background", $day_night);?>;
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.td_link { 
	font-weight: bold; 
	font-size: 1.2em; 
	color: #000099; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	}
	
.header { 
	font-weight: bold; 
	font-size: 1em; 
	color: <?php print get_css("header_text", $day_night);?>;
	background-color: <?php print get_css("header_background", $day_night);?>;
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.header_reverse { 
	font-weight: bold; 
	font-size: 1em; 
	color: <?php print get_css("header_background", $day_night);?>;
	background-color: <?php print get_css("header_text", $day_night);?>;
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}

.text { 
	font-weight: normal; 
	font-size: 1em; 
	color:	#000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.warn { 
	font-weight: normal; 
	font-size: 1em; 
	color: #CC0000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.severity_high { 
	font-weight: bold; 
	font-size: 1em; 
	color: #C00000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.severity_medium { 
	font-weight: bold; 
	font-size: 1em; 
	color: #008000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.severity_normal { 
	font-weight: bold; 
	font-size: 1em; 
	color: #0000FF; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

.text_green { 
	font-weight: normal; 
	font-size: 1em; 
	color: #009000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_orange { 
	font-weight: normal; 
	font-size: 1em; 
	color: #EBA500; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_blue { 
	font-weight: normal; 
	font-size: 1em; 
	color: #0000E0; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_red { 
	font-weight: normal; 
	font-size: 1em; 
	color: #C00000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_black { 
	font-weight: normal; 
	font-size: 1em; 
	color: #000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_small { 
	font-weight: normal; 
	font-size: .8em; 
	color: #000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_medium { 
	font-weight: normal; 
	font-size: .9em; 
	color: #000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_big { 
	font-weight: normal; 
	font-size: 1.2em; 
	color: #000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.spacer {
	background-color: <?php print get_css("row_spacer", $day_night);?>;
	font-size: 3px;
	height: 2px;
	}

.input { 
	background-color: <?php print get_css("form_input_background", $day_night);?>;
	font-weight: normal; 
	font-size: 1em; 
	color: <?php print get_css("form_input_text", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
tr.even { 
	background-color: <?php print get_css("row_light", $day_night);?>;
	color: <?php print get_css("row_light_text", $day_night);?>;
	}
	
tr.odd { 
	background-color: <?php print get_css("row_dark", $day_night);?>;
	color: <?php print get_css("row_dark_text", $day_night);?>;
	}

.even { 
	background-color: <?php print get_css("row_light", $day_night);?>;
	}
	
.odd { 
	background-color: <?php print get_css("row_dark", $day_night);?>;
	}	
	
tr.plain { 
	background-color: <?php print get_css("row_plain", $day_night);?>;
	color: <?php print get_css("row_plain_text", $day_night);?>;
	}
	
tr.heading { 
	background-color: #707070;
	color: #FFFFFF;	
	font-size: 1em; 
	font-weight: bold; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 	
	}

.heading { 
	background-color: #707070;
	color: #FFFFFF;	
	font-size: 1em; 
	font-weight: bold; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 	
	}	
	
tr.heading_2 { 
	background-color: #707070;
	color: #FFFFFF;	
	font-size: 1.2em; 
	font-weight: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 	
	}	

tr.spacer { 
	background-color: <?php print get_css("row_spacer", $day_night);?>;
	height: 2px;
	font-size: 3px;
	}	
	
tr.fs_buttons { 
	background-color: <?php print get_css("page_background", $day_night);?>;
	color: <?php print get_css("normal_text", $day_night);?>
	}		
	
td {
	cursor: pointer; 
	cursor: hand;
	}
	
/* Apply mousedown effect only to NON IE browsers */
html>body .hovermenu ul li a:active{ border-style: inset;}
/*option {font-size: 8px;}*/

checkbox {
	border-width: 0px;
	}
	
*.unselected {
	font: bold 13px arial;
	padding-left: 0;
	margin-left: 0;
	height: 20px;
	color: <?php print get_css("links", $day_night);?>;
	background-color: <?php print get_css("page_background", $day_night);?>;
	border-style: none; 
	border: 3px solid transparent; 
	}
	
*.selected {
	font: bold 13px arial;
	color: <?php print get_css("page_background", $day_night);?>;
	background-color: <?php print get_css("links", $day_night);?>;
	border-style: outset;
	border: 3px solid #CCCCCC; 
	}

.other_text {
	color: <?php print get_css("other_text", $day_night);?>; 
	} 

.normal_text {
	color: <?php print get_css("normal_text", $day_night);?>; 
	} 

.titlebar_text {
	color: <?php print get_css("titlebar_text", $day_night);?>; 
	} 

.mobile { 
	background-color: <?php print get_css("page_background", $day_night);?>;
	color: <?php print get_css("normal_text", $day_night);?>; 
	}

#directions {
	background-color: <?php print get_css("row_light", $day_night);?>;
	color: <?php print get_css("row_light_text", $day_night);?>;
	}

#the_ticket {
	background-color: <?php print get_css("row_light", $day_night);?>;
	color: <?php print get_css("row_light_text", $day_night);?>;
	}
	
#disp_details {
	background-color: <?php print get_css("row_light", $day_night);?>;
	color: <?php print get_css("row_light_text", $day_night);?>;
	}
	
#the_messages {
	background-color: <?php print get_css("row_light", $day_night);?>;
	color: <?php print get_css("row_light_text", $day_night);?>;
	}
	
.hover 	{ 
	margin-left: 4px;  
	font: normal 12px Arial, Helvetica, sans-serif; 
	color:#000000; 
	border: 1px inset #FFFFFF;
	padding: 4px 0.5em;
	text-decoration: none; 
	float: left; 
	background-color: #DEE3E7;
	font-weight: bolder;
	cursor: pointer;
	}
