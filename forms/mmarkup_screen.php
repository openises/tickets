<?php

error_reporting(E_ALL);				// 9/13/08
$side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
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
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var minimap;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var inner_listwidth;
var celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var latest_markup = 0;
var markup_last_display = 0;
var do_markup_update = true;
var markup_updated = [];
var colors = new Array ('odd', 'even');
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
	set_fontsizes(viewportwidth, "fullscreen");
	mapWidth = viewportwidth * .40;
	mapHeight = viewportheight * .55;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	leftcolwidth = viewportwidth * .45;
	rightcolwidth = viewportwidth * .40;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = leftcolwidth;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = rightcolwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('mmarkuplist').style.maxHeight = listHeight + "px";
	$('mmarkuplist').style.width = leftcolwidth + "px";
	$('the_mmarkuplist').style.maxHeight = listHeight + "px";
	$('the_mmarkuplist').style.width = leftcolwidth + "px";
	$('mmarkupheading').style.width = leftcolwidth + "px";
	load_markup('id', 'ASC');
	load_regions();
	}

function do_tab(tabid, suffix, lat, lng) {
	theTabs = new Array(1,2,3);
	for(var key in theTabs) {
		if(key == (suffix -1)) {
			}
		}
	if(tabid == "tab1") {
		if($('tab1')) {CngClass('tab1', 'tabinuse');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content1')) {$("content1").style.display = "block";}
		} else if(tabid == "tab2") {
		if($('tab2')) {CngClass('tab2', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content2')) {$("content2").style.display = "block";}
		} else {
		if($('tab3')) {CngClass('tab3', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "block";}
		init_minimap(3, lat,lng, "", 13, <?php print get_variable('locale');?>, 1);
		minimap.setView([lat,lng], 13);
		}
	}
	
</SCRIPT>
</HEAD>
<?php
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;	
	$gunload = "";				// 3/23/12
	$from_right = 20;	//	5/3/11
	$from_top = 10;		//	5/3/11
?>
<BODY onLoad = "ck_frames(); parent.frames['upper'].document.getElementById('gout').style.display  = 'inline'; location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<?php
	include("./incs/links.inc.php");		// 8/13/10
?>

<A NAME='top'></A>
<DIV id='screenname' style='display: none;'>Map Markup</DIV>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></div>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->
	<DIV id='outer' style='position: relative; left: 0px;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<DIV id='mmarkupheading' class = 'heading' style='border: 1px outset #707070;'>
				<DIV CLASS='heading text' style='text-align: center;'>Map Markup Items</DIV>
				<SPAN class='text_medium text_center text_italic' style='color: #FFFFFF; width: 100%; display: block;' id='caption'>click on item to view / edit, Click headers to sort</SPAN>
			</DIV>
			<DIV class="scrollableContainer" id='mmarkuplist' style='border: 1px outset #707070;'>
				<DIV class="scrollingArea" id='the_mmarkuplist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
			</DIV>
			<BR />
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
<?php
				if (!(is_guest())) {
					if ((!(is_user())) && (!(is_unit())) || (get_variable('oper_can_edit') == "1")) {
?>
						<SPAN id='add_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='do_add();'>Add <?php print get_text("Markup");?><BR /><IMG id='show_asgn_img' SRC='./images/plus.png' /></SPAN>
<?php
						}
?>
					<SPAN id='can_but' class='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseOver='do_hover_centerbuttons(this.id);' onMouseOut='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'>Back to Config<BR /><IMG id='show_asgn_img' SRC='./images/cancel.png' /></SPAN>
<?php
					}
?>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
		</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);	//	09/05/14
?>
	</DIV>
<SCRIPT>
//	setup map-----------------------------------//
var map;
var minimap;
var sortby = '`date`';
var sort = "DESC";
var thelevel = '<?php print $the_level;?>';
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
set_fontsizes(viewportwidth, "fullscreen");
mapWidth = viewportwidth * .40;
mapHeight = viewportheight * .55;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
leftcolwidth = viewportwidth * .45;
rightcolwidth = viewportwidth * .40;
colheight = outerheight * .95;
listHeight = viewportheight * .7;
listwidth = leftcolwidth;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = leftcolwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = rightcolwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('mmarkuplist').style.maxHeight = listHeight + "px";
$('mmarkuplist').style.width = leftcolwidth + "px";
$('the_mmarkuplist').style.maxHeight = listHeight + "px";
$('the_mmarkuplist').style.width = leftcolwidth + "px";
$('mmarkupheading').style.width = leftcolwidth + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
load_markup('id', 'ASC');
load_regions();
var bounds = map.getBounds();	
var zoom = map.getZoom();
var got_points = false;	// map is empty of points
<?php
do_kml();
?>
</SCRIPT>
<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>
<FORM NAME='doit_form' METHOD='get' ACTION='mmarkup.php'>
<INPUT TYPE='hidden' NAME='func' VALUE=''>
<INPUT TYPE='hidden' NAME='view' VALUE=''>
<INPUT TYPE='hidden' NAME='add' VALUE=''>
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
	$("to_bottom").style.display = $("to_top").style.display = "none";
</script>
<?php
	}
?>
</HTML>
