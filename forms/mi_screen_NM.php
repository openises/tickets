<?php

error_reporting(E_ALL);
$units_side_bar_height = .6;
$do_blink = TRUE;
$ld_ticker = "";
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";	
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";
$temp = get_variable('auto_poll');
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
$curr_cats = get_category_butts();
$cat_sess_stat = get_session_status($curr_cats);
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);												


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
		table.cruises td {overflow: hidden; }
		div.scrollableContainer { position: relative; padding-top: 1.8em; border: 1px solid #999; }
		div.scrollableContainer2 { position: relative; padding-top: 1.3em; }
		div.scrollingArea { max-height: 240px; overflow: auto; overflow-x: hidden; }
		div.scrollingArea2 { max-height: 400px; overflow: auto; overflow-x: hidden; }
		table.scrollable thead tr { position: absolute; left: -1px; top: 0px; }
		table.cruises th { text-align: left; border-left: 1px solid #999; background: #CECECE; color: black; font-weight: bold; overflow: hidden; }
		.olPopupCloseBox{background-image:url(img/close.gif) no-repeat;cursor:pointer;}	
		div.tabBox {}
		div.tabArea { font-size: 80%; font-weight: bold; padding: 0px 0px 3px 0px; }
		span.tab { background-color: #CECECE; color: #8060b0; border: 2px solid #000000; border-bottom-width: 0px; border-radius: .75em .75em 0em 0em;	border-radius-topleft: .75em; border-radius-topright: .75em;
				padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px; z-index: 100; }
		span.tabinuse {	background-color: #FFFFFF; color: #000000; border: 2px solid #000000; border-bottom-width: 0px;	border-color: #f0d0ff #b090e0 #b090e0 #f0d0ff; border-radius: .75em .75em 0em 0em;
				border-radius-topleft: .75em; border-radius-topright: .75em; padding: 2px 1em 2px 1em; position: relative; text-decoration: none; top: 3px;	z-index: 100;}
		span.tab:hover { background-color: #FEFEFE; border-color: #c0a0f0 #8060b0 #8060b0 #c0a0f0; color: #ffe0ff;}
		div.content { font-size: 80%; background-color: #F0F0F0; border: 2px outset #707070; border-radius: 0em .5em .5em 0em;	border-radius-topright: .5em; border-radius-bottomright: .5em; padding: .5em;
				position: relative;	z-index: 101; cursor: auto; height: 250px;}
		div.contentwrapper { width: 260px; background-color: #F0F0F0; cursor: auto;}
	</STYLE>
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT>
<?php 

if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}	
?>
<script src="./js/proj4js.js"></script>
<script src="./js/proj4-compressed.js"></script>
<script src="./js/leaflet/leaflet.js"></script>
<script src="./js/proj4leaflet.js"></script>
<script src="./js/leaflet/KML.js"></script> 
<script src="./js/leaflet/gpx.js"></script>  
<script src="./js/leaflet-openweathermap.js"></script>
<script src="./js/esri-leaflet.js"></script>
<script src="./js/osopenspace.js"></script>
<script src="./js/Control.Geocoder.js"></script>
<script type="text/javascript" src="./js/osm_map_functions.js.php"></script>
<script type="text/javascript" src="./js/L.Graticule.js"></script>
<script type="text/javascript" src="./js/leaflet-providers.js"></script>
<SCRIPT>
window.onresize=function(){set_size()};

window.onload = function(){set_size();};
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>

var listHeight;
var colwidth;
var leftcolwidth;
var rightcolwidth;
var listwidth;
var inner_listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var mi_interval = null;
var latest_mi = 0;
var mi_last_display = 0;
var mi_period_changed = 0;
var do_mi_update = true;
var mis_updated = [];
var mi_last_display = 0;
			
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
	leftcolwidth = colwidth;
	rightcolwidth = colwidth;
	colheight = outerheight * .95;
	listHeight = viewportheight * .9;
	listwidth = colwidth * .99;
	leftlistwidth = leftcolwidth * .99;
	rightlistwidth = rightcolwidth * .99;
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = rightcolwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('milist').style.maxHeight = listHeight + "px";
	$('milist').style.width = leftlistwidth + "px";
	$('the_milist').style.maxHeight = listHeight + "px";
	$('the_milist').style.width = leftlistwidth + "px";
	$('misheading').style.width = leftlistwidth + "px";
	load_mi_list("id", "ASC");
	}
</SCRIPT>

<?php 
	if ($_SESSION['internet']) {	
?>
		<SCRIPT SRC='./js/usng.js' 			TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 -->
		<SCRIPT SRC='./js/osgb.js' 			TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 -->
		<SCRIPT SRC='./js/geotools2.js' 			TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 -->
<?php
	}
?>	
<STYLE TYPE="text/css">
.box { background-color: #DEE3E7; border: 2px outset #606060; color: #000000; padding: 0px; position: absolute; z-index:1000; width: 180px; }
.bar { background-color: #FFFFFF; border-bottom: 2px solid #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
/* 3/26/2013
.bar_header { height: 20px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
*/
.bar_header { height: 30px; background-color: #CECECE; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; text-align: center;}
.content { padding: 1em; }
</STYLE>
</HEAD>
<?php
	$gunload = "clearInterval(mi_interval);";				// 3/23/12
?>
<BODY onLoad = "set_size(); ck_frames(); parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>

<A NAME='top'></A>
<DIV id='screenname' style='display: none;'>major_incidents</DIV>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->

<DIV id = "outer" style='position: absolute; left: 0px;'>
	<DIV id = "leftcol" style='position: absolute; left: 10px;'>
		<DIV id = "misheading" class = 'heading' style='border: 1px outset #707070;'>
			<DIV style='text-align: center;'>Major Incidents</DIV>
		</DIV>				
		<DIV class="scrollableContainer" id='milist' style='border: 1px outset #707070;'>
			<DIV class="scrollingArea" id='the_milist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
		<DIV style='z-index: 1; position: relative; text-align: center;'>
<?php
			if (!(is_guest())) {
				if ((!(is_user())) && (!(is_unit()))) {
?>
					<SPAN id='add_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='document.add_Form.submit();'>Add new <?php print get_text("Major Incident");?></SPAN>
<?php
					}
				}
?>
		</DIV>		
	</DIV>
	<DIV id='rightcol' style='position: absolute; right: 170px;'>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);	//	09/05/14
?>
</DIV>
<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<?php 		$the_val = (can_edit())? "edit" : "view"; ?>
<INPUT TYPE='hidden' NAME='<?php print $the_val;?>' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='add_Form' METHOD='get' ACTION='maj_inc.php'>
<INPUT TYPE='hidden' NAME='add' VALUE='true'>
</FORM>

<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__);?>?func=responder"></FORM>
<FORM NAME='tracks_Form' METHOD="get" ACTION = "tracks.php"></FORM>

<FORM NAME='mi_form' METHOD='get' ACTION='maj_inc.php?edit=true'>
<INPUT TYPE='hidden' NAME='view' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<br /><br />
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
