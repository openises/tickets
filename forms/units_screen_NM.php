<?php

error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;	//	3/15/11
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";	//	3/15/11
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";		//	3/15/11
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;	//	3/15/11
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";	//	3/15/11
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";	//	3/15/11	
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;	//	3/15/11
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";	//	3/15/11
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";	//	3/15/11
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
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
	<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="text/javascript"></SCRIPT><!-- 10/23/12-->
<?php 

if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
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
var incFin = true;
var respFin = false;
var facFin = true;
var logFin = true;
var mapCenter;
var mapZoom;
var minimap;
var mapWidth;
var mapHeight;
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
var r_interval = null;
var c_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = new Array();
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

	mapWidth = viewportwidth * .40;
	mapHeight = mapWidth * .9;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	leftcolwidth = colwidth * 1.2;
	rightcolwidth = colwidth * .8;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
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
	$('responderlist').style.maxHeight = listHeight + "px";
	$('responderlist').style.width = leftlistwidth + "px";
	$('the_rlist').style.maxHeight = listHeight + "px";
	$('the_rlist').style.width = leftlistwidth + "px";
	$('respondersheading').style.width = leftlistwidth + "px";
	load_status_control();
	load_regions();
	}
	
function pageLoaded() {
	if(respFin) {
		} else {
		return;
		}
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
	$gunload = "clearInterval(r_interval);";				// 3/23/12
?>
<BODY onLoad = "set_size(); ck_frames(); parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>

<A NAME='top'></A>
<DIV id='screenname' style='display: none;'>responders</DIV>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></div>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->

<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>	<!-- 3/23/12 -->
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV id = "outer" style='position: absolute; left: 0px;'>
	<DIV id = "leftcol" style='position: absolute; left: 10px;'>
		<DIV id = "respondersheading" class = 'heading'>
			<DIV style='text-align: center;'>Responders</DIV>
		</DIV>				
		<DIV class="scrollableContainer" id='responderlist' style='border: 1px outset #707070;'>
			<DIV class="scrollingArea" id='the_rlist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
		<DIV style='z-index: 1; position: relative; text-align: center;'>
			<DIV style='width: 100%;'><B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'><B>&bull;</B></FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'><B>&bull;</B></FONT></DIV>
			<BR />
			<DIV style='width: 100%; font-size: 12px;'><?php print get_units_legend();?></DIV>
			<BR />
			<SPAN id='tracks_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='document.tracks_Form.submit();'><?php print get_text("Unit");?> Tracks</SPAN>
<?php
			if (!(is_guest())) {
				if ((!(is_user())) && (!(is_unit())) || (get_variable('oper_can_edit') == "1")) {
?>
					<SPAN id='add_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='document.add_Form.submit();'>Add a <?php print get_text("Unit");?></SPAN>
<?php
					}
?>
				<SPAN id='mail_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='do_mail_win();'>Email <?php print get_text("Units");?></SPAN>
<?php
				}
?>
		</DIV>				
	</DIV>

	<DIV id='rightcol' style='position: absolute; right: 170px;'>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, TRUE, TRUE, $allow_filedelete, 0, 0, 0, 0);	//	09/05/14
?>
</DIV>
<SCRIPT>
var thelevel = '<?php print $the_level;?>';
var tmarkers = [];	//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var theLocale = <?php print get_variable('locale');?>;
</SCRIPT>

<FORM NAME='view_form' METHOD='get' ACTION='units.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='view' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='add_Form' METHOD='get' ACTION='units.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='add' VALUE='true'>
</FORM>

<FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__);?>?func=responder"></FORM>
<FORM NAME='tracks_Form' METHOD="get" ACTION = "tracks.php"></FORM>

<FORM NAME='resp_form' METHOD='get' ACTION='units_nm.php?'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
<INPUT TYPE='hidden' NAME='view' VALUE=''>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<br /><br />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" ID = "up" BORDER=0></div>
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
