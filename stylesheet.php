<?php
header('Content-type: text/css');
/* 
3/15/11 new file - dynamic css file
10/23/12 Added styles for messaging
*/
require_once('incs/functions.inc.php');
session_start();
session_write_close();
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
$alt_day_night = ($day_night=="Day") ? "Night" : "Day"; 

?>
/* Core Elements */
BODY 	{ background-color: <?php print get_css("page_background", $day_night);?>;	margin:0; font-weight: normal; font-style: normal; 
		color: <?php print get_css("normal_text", $day_night);?>; font-family: Arial, Verdana, Geneva, "Trebuchet MS", Tahoma, Helvetica, sans-serif; text-decoration: none;}
TABLE 	{border-collapse: collapse;}
INPUT 	{background-color: <?php print get_css("form_input_background", $day_night);?>; font-weight: normal;; 
		color: <?php print get_css("form_input_text", $day_night);?>;}
INPUT:focus {background-color: yellow;}
TEXTAREA {background-color: <?php print get_css("form_input_background", $day_night);?>; font-weight: normal;; 
		color: <?php print get_css("form_input_text", $day_night);?>;}
TEXTAREA:focus {background-color: yellow;}
SELECT 	{background-color: <?php print get_css("select_menu_background", $day_night);?>; font-weight: normal;; 
		color: <?php print get_css("select_menu_text", $day_night);?>; text-decoration: underline;}
OPTION 	{font-weight: normal;}
A 		{font-weight: bold; color: <?php print get_css("links", $day_night);?>;}

/* Lists and Tables */
TD 		{background-color: inherit; color: #000000; vertical-align: middle; word-wrap: break-all;}
.print_TD {background-color: #FFFFFF; color: #000000;}
.td_label {background-color: inherit; color: <?php print get_css("label_text", $day_night);?>; font-weight: bold;}
.td_mand {font-weight: bold; color: #CC0000;}
.td_data {white-space:nowrap; background-color: inherit; color: #000000; font-weight: normal;}
.td_data_wrap {word-wrap: break-all; background-color: inherit; color: #000000; font-weight: normal;}
#td_header {font-weight: bold; color: <?php print get_css("header_text", $day_night);?>; background-color: <?php print get_css("header_background", $day_night);?>;}
.td_link {font-weight: bold; color: #000099;}
.header {font-weight: bold; color: <?php print get_css("header_text", $day_night);?>; background-color: <?php print get_css("header_background", $day_night);?>;}
.header_reverse {font-weight: bold; color: <?php print get_css("header_background", $day_night);?>; background-color: <?php print get_css("header_text", $day_night);?>;}
.spacer {background-color: <?php print get_css("row_spacer", $day_night);?>; height: 2px;}
tr.even {background-color: <?php print get_css("row_light", $day_night);?>; color: <?php print get_css("row_light_text", $day_night);?>;}
tr.odd {background-color: <?php print get_css("row_dark", $day_night);?>; color: <?php print get_css("row_dark_text", $day_night);?>;}
.even {background-color: <?php print get_css("row_light", $day_night);?>;}
.odd {background-color: <?php print get_css("row_dark", $day_night);?>;}
tr.plain {background-color: <?php print get_css("row_plain", $day_night);?>; color: <?php print get_css("row_plain_text", $day_night);?>;}
tr.heading {background-color: <?php print get_css("row_heading_background", $day_night);?>;	color: <?php print get_css("row_heading_text", $day_night);?>; font-weight: bold;}
.heading {background-color: <?php print get_css("row_heading_background", $day_night);?>; color: <?php print get_css("row_heading_text", $day_night);?>; font-weight: bold;}
.config_heading {font-weight: bold; padding: 4px;}		
tr.heading_2 {background-color: <?php print get_css("row_heading_background", $day_night);?>; color: <?php print get_css("row_heading_text", $day_night);?>;}	
tr.heading_3 {background-color: #909090; color: #FFFFFF; font-style: italic;}	
tr.spacer {background-color: <?php print get_css("row_spacer", $day_night);?>; height: 2px;}
td.spacer {background-color: <?php print get_css("row_spacer", $day_night);?>; height: 2px;}
td {cursor: pointer;}
.plain_list {word-wrap: break-all; cursor: pointer; -ms-word-wrap : sWrap; }
.list_entry {word-wrap: break-all; cursor: pointer; -ms-word-wrap : sWrap;}
.plain_listheader {overflow-wrap: break-all; word-wrap: break-all; color:#000000; border: 1px outset #606060; background-color: #EFEFEF; font-weight: bold; cursor: pointer; -ms-word-wrap : sWrap; box-sizing: border-box; moz-box-sizing: border-box;}
.hover_listheader {overflow-wrap: break-all; word-wrap: break-all; color:#000000; border: 1px inset #606060; background-color: #DEE3E7; font-weight: bold; cursor: pointer; -ms-word-wrap : sWrap; box-sizing: border-box; moz-box-sizing: border-box;}
.cols_h {font-weight: bold; display: inline-block; background-color: #CECECE; color: #000000; padding-top: 3px; padding-bottom: 3px; border: 1px outset #DEDEDE; cursor: pointer;}
.cols_h_chk {font-weight: bold; display: inline-block; background-color: #CECECE; color: #000000; border: 1px outset #DEDEDE; cursor: pointer;}
.msg_col_h {font-weight: bold; display: inline-block; background-color: #CECECE; color: #000000; padding-top: 3px; padding-bottom: 3px;	border: 1px outset #FFFFFF;}
.cols {display: inline-block; white-space: normal; word-wrap: break-all; padding-top: 5px; padding-bottom: 5px; height: auto; -ms-word-wrap : sWrap;}
.msg_col {display: inline-block; white-space: normal; word-wrap: break-all; padding-top: 5px; padding-bottom: 5px; -ms-word-wrap : sWrap;}	
.msg_div {max-height: 150px; overflow-y: auto; overflow-x: hidden; word-wrap: break-all; display: block;}

/* Severities */
.severity_high {font-weight: bold; color: #C00000;}
.severity_medium {font-weight: bold; color: #008000;}
.severity_normal {font-weight: bold; color: #0000FF;}
.sev_counts {font-weight: bold; background-color: #CECECE; margin-left: 40px; margin-right: 40px;}

/* Full Screen */	
.td_fs_buttons {background-color: <?php print get_css("page_background", $day_night);?>; color: <?php print get_css("normal_text", $day_night);?>; cursor: pointer;}
.fs_buttons {font-weight: bold; cursor: pointer;}
tr.fs_even {background-color: <?php print get_css("row_light", $day_night);?>; color: #707070;}
tr.fs_odd {background-color: <?php print get_css("row_dark", $day_night);?>; color: #000000;}
.fs_even {background-color: <?php print get_css("row_light", $day_night);?>;}
.fs_odd {background-color: <?php print get_css("row_dark", $day_night);?>;}
.fs_td {font-weight: bold; height: 25px;}
tr.fs_buttons {background-color: <?php print get_css("page_background", $day_night);?>;	color: <?php print get_css("normal_text", $day_night);?>;}		
.plain_listheader_fs {text-align: left; color:#000000; border: 2px outset #606060; background-color: #EFEFEF; cursor: pointer; box-sizing: border-box; moz-box-sizing: border-box;}
.hover_listheader_fs {text-align: left; color:#000000; border: 2px inset #606060; background-color: #DEE3E7; cursor: pointer; box-sizing: border-box; moz-box-sizing: border-box;}
.plain_list_fs {text-align: left; white-space:nowrap; cursor: pointer;}

/* Miscellaneous */
.emph {background-color: #99b2cc; color: #ffffff;}
.nodir {background-color: #99b2cc; color: #ffffff;}
.warn {font-weight: normal; color: #CC0000;}
.found  {background-color: #000000; color: #ffffff;}
.scheduled {white-space:nowrap; background-color: #0000FF; color: #FFFFFF;}
.scheduled_notify {white-space:nowrap; background-color: #FF0000; color: #FFFF00;}
.unk {background-color:gray; color:white; font-weight:bold;}
.fast {background-color:white; color:black;	font-weight:bold;}
.stopped {background-color:red;	color:black; font-weight:bold;}
.moving {background-color:blue;	color:black; font-weight:bold;}
.legend {font-weight: bold;	color: <?php print get_css("legend", $day_night);?>;}
.mobile {background-color: <?php print get_css("page_background", $day_night);?>; color: <?php print get_css("normal_text", $day_night);?>;}
.disp_stat	{font-weight: bold; color: #FFFFFF; background-color: #000000;}
.but_container{border: 1px solid black; text-align: center; position: fixed; top: 5px; left: 1px; z-index: 999; padding: 10px; background-color: rgb(0%,0%,0%);	background-color: rgba(0%, 0%, 0%, 0.5); width: 96%;}
.midline {vertical-align: middle; display: inline-block; width: 100px;}
	
/* buttons and links */
.link {text-decoration: underline;}
.update_conf {font-weight: bold; color: <?php print get_css("header_text", $day_night);?>;}
.hovermenu ul {font-weight: bold; padding-left: 0; margin-left: 0; height: 20px; cursor: pointer;}
.hovermenu ul li {list-style: none;	display: inline;}
.hovermenu ul li a {padding: 2px 0.5em;	text-decoration: none; float: left;	color: black; background-color: #FFF2BF; border: 2px solid #FFF2BF;}
.hovermenu ul li a:hover{background-color: #FFE271; border-style: outset;}
.cat_button, .pri_button,.cat_button_fs, .pri_button_fs {
	font-weight: bold;	color: <?php print get_css("label_text", $day_night);?>; float:left; padding:2px; vertical-align:middle; cursor: pointer;}
.conf_button {color: <?php print get_css("normal_text", $day_night);?>; cursor: pointer;}
.conf_next_button, .conf_can_button{text-align: left; float:left; padding:2px; vertical-align:middle; cursor: pointer;}
.conf_next_button {color: green;}
.conf_can_button {color: red;}
.full_screen_buttons {font-weight: bold; color: <?php print get_css("legend", $day_night);?>;}
.span_link {font-weight: bold; color: <?php print get_css("links", $day_night);?>;}
.right_menu {text-align: center; padding: 2px; color: <?php print get_css("normal_text", $day_night);?>; background: <?php print get_css("page_background", $day_night);?>; 
	border-top: 4px outset #CECECE;	border-left: 4px outset #CECECE; border-bottom: 4px outset #CECECE; z-index: 3;}
.right_menu_lit {text-align: center; padding: 2px; color: <?php print get_css("label_text", $day_night);?>; background: #00FFFF; border-top: 4px outset #CECECE; 
	border-left: 4px outset #CECECE; border-bottom: 4px outset #CECECE; z-index: 3;}	
.right_menu_container {padding-top: 5px; padding-bottom: 5px; z-index: 3;}
.but_hdr {margin-right: .9em; color:#000000; padding: 4px 0.5em; background-color: #EFEFEF; font-weight: bold;}	
.reg_button {color:#000000; padding: 4px 0.5em; background-color: #EFEFEF; font-weight: bold; padding-left: .9em;}
.plain_square {border: 1px outset #FFFFFF; background-color: #EFEFEF; color:#000000; text-decoration: none; float: left; font-weight: bold; cursor: pointer; border-radius:.5em; width: 19px; height: 19px; padding: 2px; margin: 2px;}
.hover_square {border: 1px outset #FFFFFF; background-color: #DEE3E7; color:#000000; text-decoration: none; float: left; font-weight: bold; cursor: pointer; border-radius:.5em; width: 19px; height: 19px; padding: 2px; margin: 2px;}
.plain, .hover, .plain_inactive {margin-left: 4px; color:#000000; padding: 4px 0.5em; text-decoration: none; float: left; font-weight: bold; cursor: pointer; border-radius:.5em;}
.plain {border: 1px outset #FFFFFF; background-color: #EFEFEF;}
.hover {border: 1px inset #FFFFFF; background-color: #DEE3E7;}
.plainmi, .hovermi {margin-left: 4px; color:#FFFFFF; padding: 4px 0.5em; text-decoration: none; float: left; font-weight: bold; cursor: pointer; border-radius:.5em; display: inline-block; width: 300px;}
.plainmi {border: 1px outset #FFFFFF; background-color: red;}
.hovermi {border: 1px inset #FFFFFF; background-color: orange;}
.plain_centerbuttons, .hover_centerbuttons, .isselected {text-align: center; margin-left: 4px; color:#000000; padding: 4px 0.5em; text-decoration: none; float: left; font-weight:
	bold; cursor: pointer; border-radius:.5em; height: 60px;}
.plain_centerbuttons {border: 1px outset #FFFFFF; background-color: #EFEFEF;}
.hover_centerbuttons {border: 1px inset #FFFFFF; background-color: #DEE3E7;}
.plain_inactive {color:#909090; background-color: #DEE3E7;}
.signal_r,.signal_o,.signal_b,.signal_w,.hover_lo,.isselected {margin-left: 4px; border: 1px outset #FF3366; padding: 4px 0.5em; float: left; color: #000000; font-weight: bold;
	border-radius:.5em; cursor: pointer;}
.signal_r {background-color: #FF3366;}
.signal_o {background-color: #CC9900;}
.signal_b {background-color: #00CCFF; color: #FFFFFF;}
.signal_w {background-color: #3366FF; color: #FFFFFF;}
.isselected {background-color: #3366FF; color: #FFFFFF; border: 1px inset #FFFFFF;}
.hover_lo {padding: 1px 0.5em; background-color: #DEE3E7;}
.plain_lo {margin-left: 4px; color:#000000;	border: 3px hidden #FFFFFF;	border-radius:.5em;}
.plain_vert, .hover_vert {color:#000000; padding: 4px 0.5em; font-weight: bold; cursor: pointer; -webkit-transform: rotate(270deg); 
	-moz-transform: rotate(270deg); -ms-transform: rotate(270deg); -o-transform: rotate(270deg); transform: rotate(270deg); filter: none;}
.plain_vert {border: 1px outset #FFFFFF; background-color: #EFEFEF;}
.hover_vert {border: 1px inset #FFFFFF;	background-color: #DEE3E7;}
li.mylink {font-weight: bold; font-size: 24px; cursor: pointer; color: <?php print get_css("links", $day_night);?>;}
.centerbuttons {width: 80px; font-size: 1.2em;}
.submitbut {background:url(/images/submit_small.png); background-repeat: no-repeat;}

/* Text Colors */
.text_green {color: #009000;}
.text_orange {color: #EBA500;}
.text_blue {color: #0000E0;}
.text_red {color: #C00000;}	
.text_black {color: #000000;}
.text_white {color: #FFFFFF;}

/* Text Sizes */
.text_verysmall {font-size: .6em;}
.text_small {font-size: .7em;}
.text {font-size: 1em;}
.text_medium {font-size: .8em;}
.text_large {font-size: 1.1em;}
.text_big {font-size: 1.3em;}
.text_biggest {font-size: 1.5em;}

/* Text Weight */
.text_light {font-weight: lighter;}
.text_normal {font-weight: normal;}
.text_bold {font-weight: bold;}
.text_bolder {font-weight: bolder;}
.text_boldest {font-weight: 900;}

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
.top {vertical-align: text-top;}
.bottom {vertical-align: text-bottom;}
.text_left {text-align: left;}
.text_right {text-align: right;}
.text_center {text-align: center;}

/* Other Text */
.other_text {color: <?php print get_css("other_text", $day_night);?>;} 
.normal_text {color: <?php print get_css("normal_text", $day_night);?>;} 
.titlebar_text {color: <?php print get_css("titlebar_text", $day_night);?>;}
.sev_infobar {background-color: <?php print get_css("sev_background", $day_night);?>; color: <?php print get_css("sev_text", $day_night);?>;}
.text-labels {font-weight: 700;}

/* Forms */
.input {background-color: <?php print get_css("form_input_background", $day_night);?>; font-weight: normal; color: <?php print get_css("form_input_text", $day_night);?>;}
option.main {color: #FFFFFF; background-color: #000000;}
option.Critical {background-color: #FF0066;}
option.Hazardous {background-color: #66FFFF;}
option.Medical {background-color: #CCFF99;}
option.Trauma {background-color: #FF9900;}
checkbox {border-width: 0px;}
*.unselected {font-weight: bold; height: 20px;	color: <?php print get_css("links", $day_night);?>;	background-color: <?php print get_css("page_background", $day_night);?>;
	border-style: none;	border: 3px solid transparent;}
*.selected {font-weight: bold; color: <?php print get_css("page_background", $day_night);?>; background-color: <?php print get_css("links", $day_night);?>;
	border-style: outset; border: 3px solid #CCCCCC;}
select.sit {background-color: transparent; color: #102132; border: none;}
	
/* Mapping */
#detailmap {width: 300px; height: 120px; border:1px solid gray;}
.infowin {position: relative; top: 5px;width: 300px; height: 250px; padding-right: 20px;	overflow-y: auto; overflow-x: hidden;} 
#directions {background-color: <?php print get_css("row_light", $day_night);?>;	color: <?php print get_css("row_light_text", $day_night);?>;}
.fence_warn {background-color: #FF0000; font-weight: bold;}
.olPopupCloseBox{background-image:url(img/close.gif) no-repeat;cursor:pointer;}	
div.tabBox {}
div.tabArea { font-size: 90%; font-weight: bold; padding: 0px 0px 3px 0px; }
span.tab { background-color: #CECECE; color: #8060b0; border: 2px solid #000000; border-bottom-width: 0px; border-radius: .75em .75em 0em 0em;	border-radius-topleft: .75em; border-radius-topright: .75em;
		padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px; z-index: 100; }
span.tabinuse {	background-color: #FFFFFF; color: #000000; border: 2px solid #000000; border-bottom-width: 0px;	border-color: #f0d0ff #b090e0 #b090e0 #f0d0ff; border-radius: .75em .75em 0em 0em;
		border-radius-topleft: .75em; border-radius-topright: .75em; padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px;	z-index: 100;}
span.tab:hover { background-color: #FEFEFE; border-color: #c0a0f0 #8060b0 #8060b0 #c0a0f0; color: #ffe0ff;}
div.content { font-size: 90%; background-color: #F0F0F0; border: 2px outset #707070; border-radius: 0em .5em .5em 0em;	border-radius-topright: .5em; border-radius-bottomright: .5em; padding: .5em;
		position: relative;	z-index: 101; cursor: auto; height: auto; font-size: 70%;}
div.contentwrapper { width: 250px; background-color: #F0F0F0; cursor: auto;}
.leaflet-control-layers-expanded { padding: 10px 10px 10px 10px; color: #333; background-color: #F1F1F1; border: 3px outset #707070;}
.leaflet-control-layers-expanded .leaflet-control-layers-list {height: auto; display: block; position: relative; margin-bottom: 20px;}

/* Special Divs */
#the_ticket {background-color: <?php print get_css("row_light", $day_night);?>;	color: <?php print get_css("row_light_text", $day_night);?>;}
#disp_details {background-color: <?php print get_css("row_light", $day_night);?>; color: <?php print get_css("row_light_text", $day_night);?>;}
#the_messages {background-color: <?php print get_css("row_light", $day_night);?>; color: <?php print get_css("row_light_text", $day_night);?>;}
	
/* Apply mousedown effect only to NON IE browsers */
html>body .hovermenu ul li a:active{ border-style: inset;}
/*option {font-size: 8px;}*/

/* Moveable boxes */
.box {background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:1000; width: 180px;}
.bar {background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em; z-index:1000; text-align: center;}
.bar_header {height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em; z-index:1000; text-align: center;}
.content {padding: 1em; float: left;}

/* Scrolling Lists */
table.fixedheadscrolling { cellspacing: 0; border-collapse: collapse; }
table.fixedheadscrolling td {overflow: hidden; }
div.scrollableContainer {position: relative; top: 0px; border: 1px solid #999;}
div.scrollableContainer2 {position: relative; top: 0px; border: 1px solid #999;}
div.scrollingArea {max-height: 240px; overflow: auto; overflow-x: hidden;}
div.scrollingArea2 {max-height: 600px; overflow: auto; overflow-x: hidden;}
table.scrollable thead tr {position: absolute; left: -1px; top: 0px; }
table.fixedheadscrolling th {text-align: left; border-left: 1px solid #999;}

/* Mobile Screen */
div#has_line {z-index: 100; position: fixed; bottom: 50%; left: 10%; width: 80%; line-height: 40px; background-color: yellow; border: 2px outset #707070;}
#has_wrapper {color: black; font-size: 20px; font-weight: bold; width: 80%; display: inline-block; line-height: 40px; vertical-align: middle;}
#closeHas {display: inline-block; vertical-align: middle; float: right;}
div.sel {margin-top: 5px; width: 160px; height: 50px; color:#050; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; } 

/* New Ticket Workflow */
.modal {display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0,0,0); background-color: rgba(0,0,0,0.4);}
.modal-content {background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 40%; border-radius:3em;}
.close {color: #aaa; float: right; font-size: 28px; font-weight: bold;}
.close:hover, .close:focus {color: black; text-decoration: none; cursor: pointer;}
.modal-body {background-color: #FEFEFE; color: #000000; padding: 1%; height: 300px; text-align: center;}
.modal-header {background-color: #707070; color: #000000; padding-top: 3%; padding-bottom: 3%; width: 100%; text-align: center;}
.modal-footer {background-color: #707070; color: #000000; padding-top: 3%; padding-bottom: 3%; width: 100%; text-align: center;}

