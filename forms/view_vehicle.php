<?php
require_once('../incs/functions.inc.php');
@session_start();

do_login(basename(__FILE__));
$all_id = mysql_real_escape_string($_GET['all_id']);

$query_all = "SELECT * FROM `$GLOBALS[mysql_prefix]allocations` WHERE `id` = {$all_id}";
$result_all = mysql_query($query_all) or do_error($query_all, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$row_all	= mysql_fetch_array($result_all);
$id = $row_all['member_id'];
$skill_id = $row_all['skill_id'];

$query	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `$GLOBALS[mysql_prefix]member` `m` 
	WHERE `m`.`id`={$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));
?>
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<script src="../js/jquery-1.5.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="../js/jquery.textarea-expander.js"></script>
<script src="../js/misc_function.js" type="text/javascript"></script>
<SCRIPT TYPE="application/x-javascript" SRC="../js/jss.js"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size()};
var viewportwidth, viewportheight, outerwidth, outerheight, colwidth;

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
	set_fontsizes(viewportwidth, "popup");
	outerwidth = viewportwidth * .98;
	outerheight = viewportheight * .95;
	colwidth = viewportwidth * .93;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = colwidth + "px";}
	}

function pop_veh(veh_id) {								// get initial values from server -  4/7/10
	var url = '../ajax/view_vehicle_details.php?session=<?php print MD5(session_id());?>&veh_id=' + veh_id;
	sendRequest (url ,pop_cb, "");		
		function pop_cb(req) {
			var the_det_arr=JSON.decode(req.responseText);
				$('f1').innerHTML = the_det_arr[0];
				$('f2').innerHTML = the_det_arr[1];
				$('f3').innerHTML = the_det_arr[2];
				$('f4').innerHTML = the_det_arr[3];
				$('f5').innerHTML = the_det_arr[4];
				$('f6').innerHTML = the_det_arr[5];
				$('f7').innerHTML = the_det_arr[6];
				$('f8').innerHTML = the_det_arr[7];
				$('f9').innerHTML = the_det_arr[8];
				$('f10').innerHTML = the_det_arr[9];
				$('f11').innerHTML = the_det_arr[10];
				$('f12').innerHTML = the_det_arr[11];
				$('f13').innerHTML = the_det_arr[12];
				$('f14').innerHTML = the_det_arr[13];	
		}				// end function pop_cb()
	}				// end function pop_veh()
</SCRIPT>
</HEAD>
<BODY onload='pop_veh(<?php print $row_all['skill_id'];?>)'>	
<?php
	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]vehicles` WHERE `id` = '" . $skill_id . "' LIMIT 1";
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
	$text = $row2['make'] . " " . $row2['model'] . " " . $row2['regno'];
?>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV id = "leftcol" style='position: relative; left: 30px; top: 70px; float: left;'>
			<DIV id='button_bar' class='but_container'>
				<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>View <?php print get_text('Vehicle Allocation');?> for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</SPAN>
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='../images/close.png' BORDER=0></SPAN>
			</DIV>
			<FORM METHOD="POST" NAME= "veh_edit_Form" ACTION="member.php?func=member&goeditveh=true">
				<FIELDSET>
				<LEGEND><?php print get_text('Vehicle');?> Allocation</LEGEND>
					<BR />
					<LABEL><?php print get_text('Vehicle');?>:</LABEL>
						<DIV CLASS='td_data_wrap'><?php print $text;?></DIV>
					<BR />
				</FIELDSET>
			</FORM>						
			<DIV id='veh_details' style='width: 90%; border: 2px outset #CECECE; padding: 20px; text-align: left;'>
				<DIV style='width: 100%; text-align: center;' CLASS='tablehead'>SELECTED <?php print get_text('VEHICLE');?> DETAILS</DIV><BR /><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'><?php print get_text('Owner');?>:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f1'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Make:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f2'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Model:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f3'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Year:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f4'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Colour:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f5'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> <?php print get_text('Registration');?>:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f6'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Type:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f7'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Fuel Type:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f8'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Pessenger Seats:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f9'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Roof Rack:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f10'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Tow Bar:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f11'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Winch:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f12'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Trailer:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f13'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'><?php print get_text('Vehicle');?> Notes:</DIV><DIV class='text' style='width: 40%; display: inline-block;' ID='f14'>TBA</DIV><BR />
			</DIV>
		</DIV>
	</DIV>	
	<FORM NAME='can_Form' METHOD="post" ACTION = "member.php?func=member&view=true&id=<?php print $id;?>"></FORM>			
</BODY>
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
set_fontsizes(viewportwidth, "popup");
outerwidth = viewportwidth * .98;
outerheight = viewportheight * .95;
colwidth = viewportwidth * .93;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
if($('leftcol')) {$('leftcol').style.width = colwidth + "px";}
</SCRIPT>
</HTML>						