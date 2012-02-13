<?php
header('Content-type: text/css');
/* 
3/15/11 new file - dynamic css file
*/
require_once('incs/functions.inc.php');
session_start();
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
$alt_day_night = ($day_night=="Day") ? "Night" : "Day"; 

?>
BODY { 
	background-color: <?php print get_css("page_background", $day_night);?>;
	margin:0;
	font-weight: normal; 
	font-size: 12px; 
	color: <?php print get_css("normal_text", $day_night);?>; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

TABLE {
	border-collapse: collapse; 
	}
	
INPUT { 
	background-color: <?php print get_css("form_input_background", $day_night);?>;
	font-weight: normal; 
	font-size: 12px; 
	color: <?php print get_css("form_input_text", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

TEXTAREA { 
	background-color: <?php print get_css("form_input_background", $day_night);?>;
	font-weight: normal; 
	font-size: 12px; 
	color: <?php print get_css("form_input_text", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

SELECT {
	background-color: <?php print get_css("select_menu_background", $day_night);?>; 
	font-weight: normal; 
	font-size: 12px; 
	color: <?php print get_css("select_menu_text", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: underline; 
	}
	
A { 
	font-weight: bold; 
	font-size: 12px; 
	color: <?php print get_css("links", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}

li.mylink { 
	font-weight: bold; 
	font-size: 24px; 
	color: <?php print get_css("links", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}

TD { 
	background-color: inherit; 
	font-weight: normal; 
	font-size: 10px; 
	color: #000000; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	VERTICAL-ALIGN: top;  
	}
	
.print_TD { 
	background-color: #FFFFFF; 
	font-weight: normal; 
	font-size: 12px; 
	color: #000000; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
.td_label { 
	background-color: inherit;
	color: <?php print get_css("label_text", $day_night);?>; 
	font-weight: bold; 
	font-size: 12px; 
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
	font-size: 12px; 
	color: #CC0000; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
.td_data { 
	white-space:nowrap; 
	background-color: inherit;
	font-size: 12px; 
	color: #000000; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

.emph { 
	background-color: #99b2cc;
	font-size: 12px; 
	color: #ffffff; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}

.nodir { 
	background-color: #99b2cc;
	font-size: 12px; 
	color: #ffffff; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
#td_header { 
	font-weight: bold; 
	font-size: 15px; 
	color: <?php print get_css("header_text", $day_night);?>;
	background-color: <?php print get_css("header_background", $day_night);?>;
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.td_link { 
	font-weight: bold; 
	font-size: 15px; 
	color: #000099; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	}
	
.header { 
	font-weight: bold; 
	font-size: 12pt; 
	color: <?php print get_css("header_text", $day_night);?>;
	background-color: <?php print get_css("header_background", $day_night);?>;
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}

.update_conf { 
	font-weight: bold; 
	font-size: 12pt; 
	color: <?php print get_css("header_text", $day_night);?>;
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text { 
	font-weight: normal; 
	font-size: 12px; 
	color:	#000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.warn { 
	font-weight: normal; 
	font-size: 12px; 
	color: #CC0000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.severity_high { 
	font-weight: bold; 
	font-size: 10px; 
	color: #C00000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.severity_medium { 
	font-weight: bold; 
	font-size: 10px; 
	color: #008000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.severity_normal { 
	font-weight: bold; 
	font-size: 10px; 
	color: #0000FF; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}

.sev_counts { 
	font-weight: bold; 
	font-size: 10px; 
	background-color: #CECECE;
	margin-left: 40px; 
	margin-right: 40px; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
.text_green { 
	font-weight: normal; 
	font-size: 10px; 
	color: #009000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_orange { 
	font-weight: normal; 
	font-size: 10px; 
	color: #EBA500; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_blue { 
	font-weight: normal; 
	font-size: 10px; 
	color: #0000E0; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_red { 
	font-weight: normal; 
	font-size: 10px; 
	color: #C00000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_black { 
	font-weight: normal; 
	font-size: 10px; 
	color: #000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_small { 
	font-weight: normal; 
	font-size: 8px; 
	color: #000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_medium { 
	font-weight: normal; 
	font-size: 10px; 
	color: #000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.text_big { 
	font-weight: normal; 
	font-size: 14px; 
	color: #000000; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none;
	}
	
.found  { 
	background-color: #000000; 
	color: #ffffff;
	}
	
.scheduled {
	white-space:nowrap; 
	background-color: #0000FF; 
	color: #FFFFFF; 
	font-size: 12px; 
	font-weight: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
.scheduled_notify {
	white-space:nowrap; 
	background-color: #FF0000; 
	color: #FFFF00; 
	font-size: 12px; 
	font-weight: bold; 
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
	font-size: 12px; 
	color: <?php print get_css("form_input_text", $day_night);?>; 
	font-style: normal; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	text-decoration: none; 
	}
	
#detailmap, #mapDiv { 
	font: normal 10px verdana; 
	}
	
#detailmap {
	width: 300px; 
	height: 120px; 
	border:1px solid gray; 
	}

#infowin {
	width:	600px; 
	overflow:auto; 
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
	font-size: 14px; 
	font-weight: bold; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 	
	}

.heading { 
	background-color: <?php print get_css("row_heading_background", $day_night);?>;
	color: <?php print get_css("row_heading_text", $day_night);?>;	
	font-size: 14px; 
	font-weight: bold; 
	font-family: Verdana, Arial, Helvetica, sans-serif; 	
	}	
	
tr.heading_2 { 
	background-color: <?php print get_css("row_heading_background", $day_night);?>;
	color: <?php print get_css("row_heading_text", $day_night);?>;	
	font-size: 12px; 
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
	
.hovermenu ul {
	font: bold 13px arial;
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

select.sit { 
	font: 9px Verdana, Geneva, Arial, Helvetica, sans-serif; 
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

.cat_button {
	font-size: 11px;
	font-weight: bold;	
	color: <?php print get_css("label_text", $day_night);?>;
	float:left; 
	padding:2px; 
	vertical-align:middle;
	}
	
.pri_button {
	font-size: 11px;
	font-weight: bold;	
	color: <?php print get_css("label_text", $day_night);?>; 
	float:left; 
	padding:2px; 
	vertical-align:middle; 
	}
	
.cat_button_fs {
	text-align: left;
	font-size: 11px;
	font-weight: bold;	
	color: <?php print get_css("label_text", $day_night);?>; 
	float:left; 
	padding:2px; 
	vertical-align:middle;
	}
	
.pri_button_fs {
	text-align: left;
	font-size: 11px;
	font-weight: bold;
	color: <?php print get_css("label_text", $day_night);?>; 
	float:left; 
	padding:2px; 
	vertical-align:middle; 
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

.full_screen_buttons {
	font: bold 15px arial;
	color: <?php print get_css("legend", $day_night);?>; 
	}

.mobile { 
	background-color: <?php print get_css("page_background", $day_night);?>;
	color: <?php print get_css("normal_text", $day_night);?>; 
	}

#directions {
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



