<?php
require_once('../incs/functions.inc.php');
@session_start();

do_login(basename(__FILE__));
$all_id = mysql_real_escape_string($_GET['all_id']);

$query_all = "SELECT * FROM `$GLOBALS[mysql_prefix]allocations` WHERE `id` = {$all_id}";
$result_all = mysql_query($query_all) or do_error($query_all, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$row_all	= mysql_fetch_array($result_all);

$id = $row_all['member_id'];

$query	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `$GLOBALS[mysql_prefix]member` `m` 
	WHERE `m`.`id`={$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));
?>
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<script src="../js/jquery-1.5.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="../js/jquery.textarea-expander.js"></script>
<script src="../js/jss.js" type="text/javascript"></script>
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
</SCRIPT>
</HEAD>
<BODY>
<?php
	$query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]capability_types` WHERE `id` = " . $row_all['skill_id'] . " LIMIT 1";
	$result2 = mysql_query($query2) or do_error($query2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row2 = stripslashes_deep(mysql_fetch_assoc($result2));
	$description = $row2['description'];
	$name = $row2['name'];
?>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV id = "leftcol" style='position: relative; left: 30px; top: 70px; float: left;'>
			<DIV id='button_bar' class='but_container'>
				<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>View <?php print get_text('Capability');?> for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</SPAN>
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='../images/close.png' BORDER=0></SPAN>
			</DIV>
			<FORM METHOD="POST" NAME= "capab_edit_Form" ACTION="member.php?goeditcapab=true">
				<FIELDSET>
				<LEGEND><?php print get_text('Capability');?></LEGEND>
					<BR />
					<LABEL><?php print get_text('Capability');?>:</LABEL>
						<DIV CLASS='td_data_wrap'><?php print $name;?></DIV>
					<BR />
					<LABEL><?php print get_text('Description');?>:</LABEL>
					<DIV class='td_data_wrap'><?php print $description;?></DIV>
					<BR />
				</FIELDSET>
			</FORM>						
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