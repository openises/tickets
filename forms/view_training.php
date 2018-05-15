<?php
require_once('../incs/functions.inc.php');
@session_start();
do_login(basename(__FILE__));

$all_id = mysql_real_escape_string($_GET['all_id']);
$query_all = "SELECT `id`, `member_id`, `skill_type`, `skill_id`, `_on`, UNIX_TIMESTAMP(completed) AS `completed`, UNIX_TIMESTAMP(refresh_due) AS `refresh_due` FROM `$GLOBALS[mysql_prefix]allocations` WHERE `id` = {$all_id}";
$result_all = mysql_query($query_all) or do_error($query_all, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$row_all	= mysql_fetch_array($result_all);
$id = $row_all['member_id'];
$skill_id = $row_all['skill_id'];
$completed = $row_all['completed'];
$refresh = $row_all['refresh_due'];

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

function pop_tra(tp_id) {								// get initial values from server -  4/7/10
	var url = "../ajax/view_training_package.php?session=<?php print MD5(session_id());?>&tp_id=" + tp_id;
	sendRequest (url  ,pop_cb, "");			
		function pop_cb(req) {
			var the_det_arr=JSON.decode(req.responseText);
				$('f1').innerHTML = the_det_arr[2];
				$('f2').innerHTML = the_det_arr[3];
				$('f3').innerHTML = the_det_arr[4];
				$('f4').innerHTML = the_det_arr[5];
				$('f5').innerHTML = the_det_arr[6];
				$('f6').innerHTML = '<a href="mailto:' + the_det_arr[7] + '">' + the_det_arr[7] + '</a>';
				$('f7').innerHTML = the_det_arr[8];						
				$('f8').innerHTML = the_det_arr[9];						
		}				// end function pop_cb()
	}	
</SCRIPT>
</HEAD>
<BODY onload='pop_tra(<?php print $row_all['skill_id'];?>)'>
<?php
	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]training_packages` WHERE `id` = " . $skill_id . " LIMIT 1";
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
	$description = $row2['description'];
	$name = $row2['package_name'];
?>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV id = "leftcol" style='position: relative; left: 30px; top: 70px; float: left;'>
			<DIV id='button_bar' class='but_container'>
				<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>View <?php print get_text('Training');?> Completed for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</SPAN>
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='../images/close.png' BORDER=0></SPAN>
			</DIV>
			<FORM METHOD="POST" NAME= "tpack_view_Form" ACTION="#">
				<FIELDSET>
				<LEGEND><?php print get_text('Training');?> Record</LEGEND>
					<BR />
					<LABEL><?php print get_text('Training');?> Completed:</LABEL>
						<DIV CLASS='td_data_wrap'><?php print $name;?></DIV>
					<BR />
				</FIELDSET>
			</FORM>	
			<DIV id='tra_details' style='width: 90%; border: 2px outset #CECECE; padding: 20px; text-align: left;'>
				<DIV style='width: 100%; text-align: center;' CLASS='tablehead'>SELECTED <?php print get_text('TRAINING');?> PACKAGE DETAILS</DIV><BR /><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Description:</DIV><DIV class='text' style='width: 60%; display: inline-block; vertical-align: text-top;' ID='f1'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Availablilty:</DIV><DIV class='text' style='width: 60%; display: inline-block; vertical-align: text-top;' ID='f2'>TBA</DIV><BR />						
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Provider:</DIV><DIV class='text' style='width: 60%; display: inline-block; vertical-align: text-top;' ID='f3'>TBA</DIV><BR />						
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Address:</DIV><DIV class='text' style='width: 60%; display: inline-block; vertical-align: text-top;' ID='f4'>TBA</DIV><BR />						
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Contact Name:</DIV><DIV class='text' style='width: 60%; display: inline-block; vertical-align: text-top;' ID='f5'>TBA</DIV><BR />
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Email:</DIV><DIV class='text' style='width: 60%; display: inline-block; vertical-align: text-top;' ID='f6'>TBA</DIV><BR />	
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Phone:</DIV><DIV class='text' style='width: 60%; display: inline-block; vertical-align: text-top;' ID='f7'>TBA</DIV><BR />							
				<DIV class='td_label text' style='width: 40%; display: inline-block;'>Cost <?php print get_text('$');?>:</DIV><DIV style='width: 60%; display: inline-block; vertical-align: text-top;' ID='f8'>TBA</DIV><BR />
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