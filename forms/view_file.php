<?php
require_once('../incs/functions.inc.php');
@session_start();

do_login(basename(__FILE__));

$file_id = (array_key_exists('id', $_GET)) ? mysql_real_escape_string($_GET['id']): 0;
$all_id =(array_key_exists('all_id', $_GET)) ? mysql_real_escape_string( $_GET['all_id']) : 0;

$query_f = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `id` = {$all_id}";
$result_f = mysql_query($query_f) or do_error($query_f, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$row_f	= mysql_fetch_array($result_f);

$id = $row_f['member_id'];
$realfile = ltrim($row_f['name'],"./");

$query	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `$GLOBALS[mysql_prefix]member` `m` 
	WHERE `m`.`id`={$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));

?>
<LINK REL=StyleSheet HREF="../default.css?version=<?php print time();?>" TYPE="text/css">
<script src="../js/jquery-1.5.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="../js/jquery.textarea-expander.js"></script>
<script src="../js/misc_function.js" type="text/javascript"></script>
<SCRIPT TYPE="application/x-javascript" SRC="../js/jss.js"></SCRIPT>
		</HEAD>
		<BODY>	
			<DIV id='topbar' style='position: fixed; top: 0px; left: 0px; width: 99%; z-index: 999; background-color: #DEDEDE; border: 2px outset #CECECE;'>			
				<DIV class='tablehead text_large' style='width: 100%; float: left; z-index: 999'><b>View <?php print get_text('File');?> for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</b>			
				<SPAN ID = 'close_but' class = 'plain text' style='height: auto; margin-right: 10px; float: right; width: 100px; font-size: 12px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.close();"><?php print get_text('Close');?> <IMG style='vertical-align: middle; float: right;' src="../img/close.png"/></SPAN>
				</DIV>
			</DIV>
			<DIV ID='outer' style='align: center; position: relative; top: 50px; left: 0px; z-index: 998;'>
				<DIV id='leftcol' style='position: absolute; top: 10px; left: 0px; border: 2px outset #FFFFFF; padding: 20px; z-index: 2; background-color: #FEF7D6;'>
					<DIV style='width: 100%;'><A CLASS='td_label' HREF="#" TITLE="File Name - Click to Open">File</A>:&nbsp;<A HREF="../<?php print $realfile;?>"><I><U><?php print $row_f['shortname'];?></U></I></A></DIV><BR />
					<FORM enctype="multipart/form-data" METHOD="POST" NAME= "file_edit_Form" ACTION="#">
						<FIELDSET>
						<LEGEND>View <?php print get_text('Stored File');?></LEGEND>
							<BR />
							<LABEL for="frm_file"><?php print get_text('File');?> Name:</LABEL>
							<INPUT TYPE="text" NAME="frm_file" SIZE="48" VALUE="<?php print $row_f['shortname'];?>" READONLY>
							<BR />
							<BR />
							<LABEL for="frm_description"><?php print get_text('File');?> Description:</LABEL>
							<INPUT TYPE="text" NAME="frm_description" MAXLENGTH='48' SIZE="48" VALUE="<?php print $row_f['description'];?>" READONLY>
							<BR />
						</FIELDSET>
					</FORM>						
				</DIV>
			</DIV>	
			<FORM NAME='can_Form' METHOD="post" ACTION = "member.php?func=member&view=true&id=<?php print $id;?>"></FORM>			
		</BODY>
		<SCRIPT>
		var viewportwidth, viewportheight, outerwidth, outerheight, mapHeight, colwidth, rightcolwidth, leftcolwidth;
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
		outerwidth = viewportwidth * .98;
		outerheight = viewportheight * .95;
		set_fontsizes(viewportwidth, "popup");
		</SCRIPT>
		</HTML>						