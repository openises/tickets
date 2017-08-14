<?php

error_reporting(E_ALL);				// 9/13/08
$ld_ticker = "";
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);												

?>
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var listHeight;
var colwidth;
var leftcolwidth;
var rightcolwidth;
var listwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var wl_interval = null;
var latest_wlocation = 0;
var do_wl_update = true;
var locations_updated = [];
		
var colors = new Array ('odd', 'even');

function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	leftcolwidth = viewportwidth * .70;
	rightcolwidth = viewportwidth * .10;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = leftcolwidth;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = rightcolwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('wllist').style.maxHeight = listHeight + "px";
	$('wllist').style.width = leftcolwidth + "px";
	$('the_wllist').style.maxHeight = listHeight + "px";
	$('the_wllist').style.width = leftcolwidth + "px";
	$('wlocationsheading').style.width = leftcolwidth + "px";
	load_warnloclist('id', "ASC");
	set_fontsizes(viewportwidth, "fullscreen");
	}

</SCRIPT>
</HEAD>
<?php
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;	
	
	$gunload = "clearInterval(wl_interval);";				// 3/23/12
	$from_right = 20;	//	5/3/11
	$from_top = 10;		//	5/3/11
?>
<BODY onLoad = "set_size(); ck_frames(); parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>

<A NAME='top'></A>
<DIV id='screenname' style='display: none;'>warnlocations</DIV>
<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
	<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
		<DIV id="wlocationsheading" class='heading text' style='border: 1px outset #707070;'>
			<DIV CLASS='heading text' style='text-align: center;'>Warn Locations</DIV>
		</DIV>			
		<DIV class="scrollableContainer2" id='wllist' style='border: 1px outset #707070;'>
			<DIV class="scrollingArea2" id='the_wllist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
		<DIV style='z-index: 1; position: relative; text-align: center;'>
			<DIV style='width: 100%; font-size: 12px;'><?php print get_wl_legend();?></DIV>
			<BR />
		</DIV>
	</DIV>
	<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
		<DIV style='position: fixed; top: 50px; z-index: 9999;'>
			<SPAN id='fin_but' class='plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='document.finform.submit();'><?php print get_text("Back");?><BR /><IMG id='show_asgn_img' SRC='./images/back.png' /></SPAN>
<?php
			if (!(is_guest()) && $good_internet) {
				if ((!(is_user())) && (!(is_unit())) || (get_variable('oper_can_edit') == "1")) {
?>
					<SPAN id='add_but' class='plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='document.add_Form.submit();'>Add <?php print get_text("Warn Location");?><BR /><IMG id='show_asgn_img' SRC='./images/plus.png' /></SPAN>
<?php
					}
				}
?>
		</DIV>
	</DIV>		
	<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>&nbsp;
	</DIV>
</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);	//	09/05/14
?>
<SCRIPT>
//	setup map-----------------------------------//
var sortby = '`date`';
var sort = "DESC";
var thescreen = 'ticket';
var wlmarkers = [];
var latLng;
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
leftcolwidth = viewportwidth * .70;
rightcolwidth = viewportwidth * .10;
colheight = outerheight * .95;
listHeight = viewportheight * .7;
listwidth = leftcolwidth;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = leftcolwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = rightcolwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('wllist').style.maxHeight = listHeight + "px";
$('wllist').style.width = leftcolwidth + "px";
$('the_wllist').style.maxHeight = listHeight + "px";
$('the_wllist').style.width = leftcolwidth + "px";
$('wlocationsheading').style.width = leftcolwidth + "px";
load_warnloclist('id', "ASC");
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>

<FORM NAME='add_Form' METHOD='get' ACTION='warn_locations.php'>
<INPUT TYPE='hidden' NAME='add' VALUE='true'>
</FORM>

<FORM NAME='view_Form' METHOD='get' ACTION='warn_locations.php'>
<INPUT TYPE='hidden' NAME='view' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='edit_Form' METHOD='get' ACTION='warn_locations.php'>
<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='wl_form' METHOD='get' ACTION='warn_locations.php'>
<INPUT TYPE='hidden' NAME='edit' VALUE='false'>
<INPUT TYPE='hidden' NAME='view' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME = 'finform' METHOD = 'post' ACTION = 'config.php'>
</FORM>

<FORM NAME='can_Form' METHOD="post" ACTION = "warn_locations.php?func=location"></FORM>
<A NAME="bottom" /> <!-- 11/11/09 -->
</BODY>
<?php
if (array_key_exists('print', ($_GET))) {
?>
<script>
$("down").style.display = $("up").style.display = "none";
</script>
<?php
	}
?>
</HTML>
