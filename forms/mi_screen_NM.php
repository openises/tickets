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
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<SCRIPT>
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var minimap;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var leftcolwidth;
var rightcolwidth;
var listwidth;
var leftcolwidth;
var rightcolwidth
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
var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
	}
	});
var baseHxIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -40]
	}
	});
var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
	}
	});
			
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
	listHeight = viewportheight * .9;
	listwidth = leftcolwidth;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = rightcolwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('milist').style.maxHeight = listHeight + "px";
	$('milist').style.width = leftcolwidth + "px";
	$('the_milist').style.maxHeight = listHeight + "px";
	$('the_milist').style.width = leftcolwidth + "px";
	$('misheading').style.width = leftcolwidth + "px";
	load_mi_list("id", "ASC");
	set_fontsizes(viewportwidth, "fullscreen");	
	}
</SCRIPT>

<?php 
	if ($_SESSION['internet']) {	
?>
		<SCRIPT SRC='./js/usng.js' 			TYPE='application/x-javascript'></SCRIPT>		<!-- 10/14/08 -->
		<SCRIPT SRC='./js/osgb.js' 			TYPE='application/x-javascript'></SCRIPT>		<!-- 10/14/08 -->
		<SCRIPT SRC='./js/geotools2.js' 			TYPE='application/x-javascript'></SCRIPT>		<!-- 10/14/08 -->
<?php
	}
?>	
</HEAD>
<?php
	$gunload = "clearInterval(mi_interval);";				// 3/23/12
?>
<BODY onLoad = "ck_frames(); parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>

<A NAME='top'></A>
<DIV id='screenname' style='display: none;'>major_incidents</DIV>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->
<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
	<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
		<DIV id = "misheading" class = 'heading' style='border: 1px outset #707070;'>
			<DIV style='text-align: center;'>Major Incidents</DIV>
		</DIV>				
		<DIV class="scrollableContainer" id='milist' style='border: 1px outset #707070;'>
			<DIV class="scrollingArea" id='the_milist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
		</DIV>
		<BR />
	</DIV>
	<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
		<DIV style='position: fixed; top: 50px; z-index: 9999;'>
<?php
			if (!(is_guest())) {
				if ((!(is_user())) && (!(is_unit())) || (get_variable('oper_can_edit') == "1")) {
?>
					<SPAN id='add_but' class='plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='document.add_Form.submit();'>Add <?php print get_text("MI");?><BR /><IMG id='show_asgn_img' SRC='./images/plus.png' /></SPAN>
<?php
					}
				}
?>
		</DIV>
	</DIV>
	<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
		<DIV id = 'map_canvas' style = 'display: none;'></DIV>
	</DIV>
</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);	//	09/05/14
?>
<FORM NAME='view_form' METHOD='get' ACTION='<?php print basename(__FILE__); ?>'>
<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
<?php 		
$the_val = (can_edit())? "edit" : "view"; 
?>
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
<A NAME="bottom" />
<SCRIPT>
var map;
var minimap;
var sortby = '`date`';
var sort = "DESC";
var thelevel = '<?php print $the_level;?>';
var tmarkers = [];	//	Incident markers array
var rmarkers = [];	//	Responder markers array
var lmarkers = [];	//	Control locations markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
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
listHeight = viewportheight * .9;
listwidth = leftcolwidth;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = leftcolwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = rightcolwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('milist').style.maxHeight = listHeight + "px";
$('milist').style.width = leftcolwidth + "px";
$('the_milist').style.maxHeight = listHeight + "px";
$('the_milist').style.width = leftcolwidth + "px";
$('misheading').style.width = leftcolwidth + "px";
load_mi_list("id", "ASC");
set_fontsizes(viewportwidth, "fullscreen");	
<?php
if($good_internet) {
?>
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = <?php print get_variable('use_osmap');?>;
	var initZoom = <?php print get_variable('def_zoom');?>;
	init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
	map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
	var bounds = map.getBounds();	
	var zoom = map.getZoom();
<?php
	}
?>
</SCRIPT>
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
