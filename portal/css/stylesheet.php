<?php
header('Content-type: text/css');
/* 
9/10/13 - Portal CSS file
*/
require_once('../../incs/functions.inc.php');
session_start();
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
$alt_day_night = ($day_night=="Day") ? "Night" : "Day"; 

?>
/* Core Elements */
BODY 	{ background-color: <?php print get_css("page_background", $day_night);?>;	margin:0; font-weight: normal; font-style: normal; 
		color: <?php print get_css("normal_text", $day_night);?>; font-family: Segoe, Tahoma, Verdana, Arial, Helvetica, sans-serif; text-decoration: none;}
TABLE 	{border-collapse: collapse;}
INPUT 	{background-color: <?php print get_css("form_input_background", $day_night);?>; font-weight: normal; font-size: 1em; 
		color: <?php print get_css("form_input_text", $day_night);?>;}
TEXTAREA {background-color: <?php print get_css("form_input_background", $day_night);?>; font-weight: normal; font-size: 1em; 
		color: <?php print get_css("form_input_text", $day_night);?>; white-space: pre; word-wrap: break-word;}
SELECT 	{background-color: <?php print get_css("select_menu_background", $day_night);?>; font-weight: normal; font-size: 0.7em; 
		color: <?php print get_css("select_menu_text", $day_night);?>; text-decoration: underline;}
OPTION 	{font-weight: normal; font-size: 0.7em;}
A 		{font-weight: bold; font-size: 1em; color: <?php print get_css("links", $day_night);?>;}
li.mylink {font-weight: bold; font-size: 24px; color: <?php print get_css("links", $day_night);?>;}

/* Tables */
TD 		{background-color: inherit; font-size: .9em; color: #000000; vertical-align: top; word-wrap: break-all;}
.print_TD {background-color: #FFFFFF; font-size: 1em; color: #000000;}
.td_label {background-color: inherit; color: <?php print get_css("label_text", $day_night);?>; font-weight: bold; font-size: 1em;}
.td_mand {font-weight: bold; font-size: 1em; color: #CC0000;}
.td_data {white-space:nowrap; background-color: inherit; font-size: 1em; color: #000000;}
.td_data_wrap {word-wrap: break-all; background-color: inherit; font-size: 1em; color: #000000;}
#td_header {font-weight: bold; font-size: 1em; color: <?php print get_css("header_text", $day_night);?>;
		background-color: <?php print get_css("header_background", $day_night);?>;}
.td_link {font-weight: bold; font-size: 1em; color: #000099;}

/* Severities /*
.severity_high {font-weight: bold; font-size: .9em; color: #C00000;}
.severity_medium {font-weight: bold; font-size: .9em; color: #008000;}
.severity_normal {font-weight: bold; font-size: .9em; color: #0000FF;}
.sev_counts {font-weight: bold; font-size: .8em; background-color: #CECECE; margin-left: 40px; margin-right: 40px;}

.header { 
	font-weight: bold; 
	font-size: 0.75em; 
	color: <?php print get_css("header_text", $day_night);?>;
	background-color: <?php print get_css("header_background", $day_night);?>;
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.header_reverse { 
	font-weight: bold; 
	font-size: 0.75em; 
	color: <?php print get_css("header_background", $day_night);?>;
	background-color: <?php print get_css("header_text", $day_night);?>;
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}

.warn { 
	font-weight: normal; 
	font-size: 0.75em; 
	color: #CC0000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
/* Text Colors */
.text_green {color: #009000;}
.text_orange {color: #EBA500;}
.text_blue {color: #0000E0;}
.text_red {color: #C00000;}	
.text_black {color: #000000;}

/* Text Sizes */
.text_verysmall {font-size: .6em;}
.text_small {font-size: .7em;}
.text {font-size: 1em;}
.text_medium {font-size: .8em;}
.text_large {font-size: 1.1em;}
.text_big {font-size: 1.2em;}
.text_biggest {font-size: 2em;}

/* Text Weight */
.bold {font-weight: bold;}
.bolder {font-weight: bolder;}

/* Text Decoration */
.italic {text-decoration: italic;}
.underline {text-decoration: underline;}

/* Text Wrap */
.nowrap {white-space:nowrap;}

/* Borders */
.solidborder {border:1px solid gray;}
.outsetborder {border:1px outset #707070;}
.insetborder {border:1px inset #707070;}

/* Text Overflow */
.listoverflow {	overflow-y: auto; overflow-x: hidden;}

/* Div and Span floats */
.left {float: left;}
.right {float: right;}
.nofloat {float: none;}

/* Text Alignment */
.middle {vertical-align: middle;}
.text_left {text-align: left;}
.text_right {text-align: right;}
.text_center {text-align: center;}
	
.scheduled {
	white-space:nowrap; 
	background-color: #0000FF; 
	color: #FFFFFF; 
	font-size: 0.875em; 
	font-weight: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
.scheduled_notify {
	white-space:nowrap; 
	background-color: #FF0000; 
	color: #FFFF00; 
	font-size: 0.875em; 
	font-weight: bold; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

.spacer {
	background-color: <?php print get_css("row_spacer", $day_night);?>;
	font-size: 0.4em;
	height: 2px;
	}

.input { 
	background-color: <?php print get_css("form_input_background", $day_night);?>;
	font-weight: normal; 
	font-size: 0.875em; 
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
	background-color: <?php print get_css("row_heading_background", $day_night);?>;
	color: <?php print get_css("row_heading_text", $day_night);?>;	
	font-size: 1em; 
	font-weight: bold; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 	
	}

.heading { 
	background-color: <?php print get_css("row_heading_background", $day_night);?>;
	color: <?php print get_css("row_heading_text", $day_night);?>;	
	font-size: 1em; 
	font-weight: bold; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 	
	}	

.list_heading { 
	background-color: <?php print get_css("row_heading_background", $day_night);?>;
	color: <?php print get_css("row_heading_text", $day_night);?>;	
	font-size: 1em; 
	font-weight: bold; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	}	
	
.list_entry {
	white-space:nowrap; 
	}	
	
.list_row {
	text-align: left;	
	}	

tr.spacer { 
	background-color: <?php print get_css("row_spacer", $day_night);?>;
	height: 2px;
	font-size: 0.4em;
	}	
	
tr.fs_buttons { 
	background-color: <?php print get_css("page_background", $day_night);?>;
	color: <?php print get_css("normal_text", $day_night);?>
	}		
	
td {
	cursor: pointer; 
	cursor: hand;
	}
	
.hovermenu ul {
	font: bold 0.8em arial;
	padding-left: 0;
	margin-left: 0;
	height: 20px;
	}
	
.hovermenu ul li {
	list-style: none;
	display: inline;
	}
	
.hovermenu ul li a {
	padding: 2px 0.5em;
	text-decoration: none;
	float: left;
	color: black;
	background-color: #FFF2BF;
	border: 2px solid #FFF2BF;
	}
	
.hovermenu ul li a:hover{
	background-color: #FFE271;
	border-style: outset;
	}
	
/* Apply mousedown effect only to NON IE browsers */
html>body .hovermenu ul li a:active{ border-style: inset;}
/*option {font-size: 8px;}*/

option.main {
	color: #FFFFFF;
	background-color: #000000;
	}
	
option.Critical {
	background-color: #FF0066;
	}
	
option.Hazardous {
	background-color: #66FFFF;
	}
	
option.Medical {
	background-color: #CCFF99;
	}
	
option.Trauma {
	background-color: #FF9900;
	}

checkbox {
	border-width: 0px;
	}
	
*.unselected {
	font: bold 0.8em arial;
	padding-left: 0;
	margin-left: 0;
	height: 20px;
	color: <?php print get_css("links", $day_night);?>;
	background-color: <?php print get_css("page_background", $day_night);?>;
	border-style: none; 
	border: 3px solid transparent; 
	}
	
*.selected {
	font: bold 0.8em arial;
	color: <?php print get_css("page_background", $day_night);?>;
	background-color: <?php print get_css("links", $day_night);?>;
	border-style: outset;
	border: 3px solid #CCCCCC; 
	}

select.sit { 
	font: 0.6em Verdana, Geneva, Arial, Helvetica, sans-serif; 
	background-color: transparent; 
	color: #102132; 
	border: none;
	}

.unk {
	font-size:smaller;
	background-color:gray;
	color:white;
	font-weight:bold;
	}
	
.fast {
	font-size:smaller;
	background-color:white;
	color:black;
	font-weight:bold;
	}
	
.stopped {
	font-size:smaller;
	background-color:red;
	color:black;
	font-weight:bold;
	}
	
.moving {
	font-size:smaller;
	background-color:blue;
	color:black;
	font-weight:bold;
	}

.conf_button {
	font-size: 10px; 
	color: <?php print get_css("normal_text", $day_night);?>; 
	}

.conf_next_button {
	text-align: left;
	font-size: 10px; 
	color: green; 
	float:left;
	padding:2px; 
	vertical-align:middle; 
	}
	
.conf_can_button {
	text-align: left;
	font-size: 10px; 
	color: red; 
	float:left; 
	padding:2px; 
	vertical-align:middle; 
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

.span_link {
	font: bold 12px arial;
	color: <?php print get_css("links", $day_night);?>; 
	}

.legend {
	font: bold 12px arial;
	color: <?php print get_css("legend", $day_night);?>; 
	}

#the_messages {
	background-color: <?php print get_css("row_light", $day_night);?>;
	color: <?php print get_css("row_light_text", $day_night);?>;
	}
	
.right_menu {
	text-align: center; 
	padding: 2px;
	color: <?php print get_css("normal_text", $day_night);?>; 	
	background: <?php print get_css("page_background", $day_night);?>; 
	border-top: 4px outset #CECECE; 
	border-left: 4px outset #CECECE; 
	border-bottom: 4px outset #CECECE; 	
	z-index: 3;
	}
	
.right_menu_lit {
	text-align: center; 
	padding: 2px;
	color: <?php print get_css("label_text", $day_night);?>; 	
	background: #00FFFF; 
	border-top: 4px outset #CECECE; 
	border-left: 4px outset #CECECE; 
	border-bottom: 4px outset #CECECE; 	
	z-index: 3;
	}	
	
.right_menu_container {
	padding-top: 5px;
	padding-bottom: 5px;
	z-index: 3;
	}
	
.but_hdr 	{
	margin-right: 10px;  
	font: normal 14px Arial, Helvetica, sans-serif; 
	color:#000000; 
	padding: 4px 0.5em;
	text-decoration: none; 
	background-color: #EFEFEF; 
	font-weight: bold;
	}	
	
.reg_button { 
	font: normal 12px Arial, Helvetica, sans-serif; 
	color:#000000; 
	padding: 4px 0.5em;
	text-decoration: none; 
	background-color: #EFEFEF; 
	font-weight: bold; 
	padding-left: 10px;
	}		
	
.disp_stat	{
	FONT-WEIGHT: bold; 
	FONT-SIZE: 9px; 
	COLOR: #FFFFFF; 
	BACKGROUND-COLOR: #000000; 
	FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
	}
.plain 	{ 
	margin-left: 4px;  
	font: normal 1em Arial, Helvetica, sans-serif; 
	color:#000000; 
	border: 1px outset #FFFFFF;
	padding: 4px 0.5em;
	text-decoration: none; 
	float: left; 
	background-color: #EFEFEF;
	font-weight: bolder;
	cursor: pointer;
	border-radius:.5em;
	}		
	
.hover 	{ 
	margin-left: 4px;  
	font: normal 1em Arial, Helvetica, sans-serif; 
	color:#000000; 
	border: 1px inset #FFFFFF;
	padding: 4px 0.5em;
	text-decoration: none; 
	float: left; 
	background-color: #DEE3E7;
	font-weight: bolder;
	cursor: pointer;
	border-radius:.5em;
	}

#detailmap, #mapDiv { 
	font: normal 10px verdana; 
	}

#detailmap {
	width: 300px; 
	height: 120px; 
	border:1px solid gray; 
	}

.infowin {
	width:	300px; 
	height: 250px;
	overflow-y: auto; 
	overflow-x: hidden;
	} 

#map_canvas {
	line-height: normal
	}

.content { 
	padding: 1em; 
	float: left; 
	}
	
.infowindow-content {
	background-color: #DEDEDE;
	border: 2px inset #CECECE;
	text-align: left;
	width:	300px; 
	height: 250px;
	overflow-y: auto; 
	overflow-x: hidden;
	} 

.plain_listheader 	{
	color:#000000; 
	border: 1px outset #606060;
	text-decoration: none; 
	background-color: #EFEFEF;
	font-weight: bolder;
	cursor: pointer;
	}
	
.plain_listheader_fs 	{ 
	font-size: 1.2em;
	color:#000000; 
	border: 2px outset #606060;
	text-decoration: none; 
	background-color: #EFEFEF;
	font-weight: bolder;
	cursor: pointer;
	}

.plain_list 	{
	white-space:nowrap; 
	text-decoration: none; 
	font-weight: bolder;
	cursor: pointer;
	}	
	
.hover_listheader 	{ 
	color:#000000; 
	border: 1px inset #606060;
	text-decoration: none; 
	background-color: #DEE3E7;
	font-weight: bolder;
	cursor: pointer;
	}