<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
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
var mapWidth;
var mapHeight;
var colwidth;
var leftcolwidth;
var rightcolwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var listHeight;
var listwidth;
var wlmarkers = [];

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
	colheight = outerheight * .95;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";
	$('view_location').style.width = colwidth + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	set_fontsizes(viewportwidth, "fullscreen");
	}

</SCRIPT>
<?php
$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]warnings` WHERE `id`= " . $id . " LIMIT 1";	// 1/19/2013
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));
$lat = $row['lat'];
$lng = $row['lng'];
$coords =  $row['lat'] . "," . $row['lng'];		// for UTM			
?>
</HEAD>
<BODY>
	<A NAME='top'>
	<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 10px; height: 12px; width: 10px; z-index: 9999;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<TABLE ID='view_location'>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='2'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='2'>
						<SPAN CLASS='text_green text_biggest'>&nbsp;View Warn Location '<?php print $row['title'] ;?>' Data</FONT>&nbsp;&nbsp;(#<?php print $id; ?>)</FONT></SPAN>
						<BR />
						<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
						<BR />
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=2></TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text"><?php print get_text("Name"); ?>: </TD>
					<TD CLASS='td_data text'><?php print $row['title'];?></TD>
				</TR>
				<TR CLASS = 'odd'>
					<TD CLASS="td_label text"><?php print get_text("Location"); ?>: </TD>
					<TD CLASS='td_data text'><?php print $row['street'] ;?></TD>
				</TR>
				<TR CLASS = 'even'>
					<TD CLASS="td_label text"><?php print get_text("City"); ?>: &nbsp;&nbsp;&nbsp;&nbsp;</TD>
					<TD CLASS='td_data text'><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label text"><?php print get_text("Description"); ?>: </TD>
					<TD CLASS='td_data_wrap text' style='height: 50px;'><?php print $row['description'];?></TD>
				</TR>
				<TR CLASS = 'even'>
					<TD CLASS="td_label text">As of:</TD>	
					<TD CLASS='td_data text'><?php print loc_format_date(strtotime($row['_on'])); ?></TD>
				</TR>
<?php
				if (my_is_float($lat)) {
					$usng_val = LLtoUSNG($row['lat'], $row['lng']);
					$osgb_val = LLtoOSGB($row['lat'], $row['lng']) ;
					$utm_val = toUTM("{$row['lat']}, {$row['lng']}");
					$locale = get_variable('locale');
					switch($locale) { 
						case "0":
							$label = "USNG:";
							$input = $usng_val;
							break;
							
						case "1":
							$label = "OSGB:";
							$input = $osgb_val;
							break;
							
						default:
							$label = "UTM:";
							$input = $utm_val;
						}
?>
					<TR CLASS = "odd">
						<TD CLASS="td_label text">
							<?php print $label;?>
						</TD>
						<TD CLASS='td_data text' COLSPAN=3>
							<?php print $input;?>					
						</TD>
					</TR>
<?php
					}		// end if (my_is_float($lat))

?>
				<TR>
					<TD COLSPAN=2>&nbsp;</TD>
				</TR>
			</TABLE>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Back");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
<?php
				if($good_internet) {
?>
					<SPAN id='ed_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='to_edit_Form.submit();'><?php print get_text("Edit");?><BR /><IMG id='edit_img' SRC='./images/edit.png' /></SPAN>
<?php
					}
?>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
		</DIV>
	</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "warn_locations.php"></FORM>
<FORM NAME="to_edit_Form" METHOD="post" ACTION = "warn_locations.php?edit=true&id=<?php print $id; ?>"></FORM>
<A NAME="bottom" /> 
<DIV ID='to_top' style="position:fixed; bottom:50px; left:10px; height: 12px; width: 10px; z-index: 9999;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>			
<SCRIPT>
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
mapHeight = viewportheight * .55;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = colheight + "px";
$('view_location').style.width = colwidth + "px";	
$('rightcol').style.width = colwidth + "px";
$('rightcol').style.height = colheight + "px";	
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
</BODY>
</HTML>