<?php
$file_id = mysql_real_escape_string($_GET['all_id']);
$id = mysql_real_escape_string($_GET['mem_id']);

$query_f = "SELECT * FROM `$GLOBALS[mysql_prefix]mdb_files` WHERE `id` = {$file_id}";
$result_f = mysql_query($query_f) or do_error($query_f, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$row_f	= mysql_fetch_array($result_f);

$query	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `$GLOBALS[mysql_prefix]member` `m` 
	WHERE `m`.`id`={$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));
?>
<SCRIPT>
window.onresize=function(){set_size()};
var viewportwidth, viewportheight, outerwidth, outerheight, colwidth, leftcolwidth, rightcolwidth;

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
	outerwidth = viewportwidth * .98;
	outerheight = viewportheight * .95;
	colwidth = viewportwidth * .45;
	leftcolwidth = viewportwidth * .45;
	rightcolwidth = viewportwidth * .35;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
	set_fontsizes(viewportwidth, "fullscreen");
	}

function rem_record() {
	if (confirm("Are you sure you want to delete the file")) { 
		document.file_edit_Form.frm_all_remove.value="yes";
		document.forms['file_edit_Form'].submit();
		}
	}
</SCRIPT>
</HEAD>
<BODY>	
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV CLASS='header text_large' style = "height:32px; width: 100%; float: none; text-align: center;">
			<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'><b>Edit <?php print get_text('File');?> for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</b></SPAN>
		</DIV>
		<DIV id = "leftcol" style='position: relative; left: 30px; float: left;'>
			<FORM enctype="multipart/form-data" METHOD="POST" NAME= "file_edit_Form" ACTION="member.php?goeditfile=true&id=<?php print $id;?>&extra=edit">
				<FIELDSET>
				<LEGEND>Edit <?php print get_text('File Store');?></LEGEND>
					<BR />
					<LABEL for="frm_file"><?php print get_text('File');?> Name: </LABEL>
					<INPUT TYPE="text" NAME="frm_file" SIZE="48" VALUE="<?php print $row_f['shortname'];?>" />
					<BR />
					<BR />
					<LABEL for="frm_description"><?php print get_text('File');?> Description: </LABEL>
					<INPUT TYPE="text" NAME="frm_description" MAXLENGTH='48' SIZE="48" VALUE="<?php print $row_f['description'];?>" />
					<BR />
				</FIELDSET>
				<INPUT TYPE='hidden' NAME='frm_id' VALUE='<?php print $file_id;?>' />
				<INPUT TYPE='hidden' NAME='id' VALUE='<?php print $id;?>' />
				<INPUT TYPE="hidden" NAME = "frm_all_remove" VALUE="" />						
				<INPUT TYPE='hidden' NAME='frm_file_id' VALUE='<?php print $row_f['id'];?>' />
				<INPUT TYPE='hidden' NAME='frm_file_name' VALUE='<?php print $row_f['name'];?>' />
			</FORM>						
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 1;'>
				<SPAN ID = 'rem_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="rem_record();">Remove <?php print get_text('File');?> <IMG style='vertical-align: middle; float: right;' src="./images/delete.png"/></SPAN>
				<SPAN ID = 'can_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?> <IMG style='vertical-align: middle; float: right;' src="./images/back_small.png"/></SPAN>
				<SPAN ID = 'sub_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="validate_skills(document.file_edit_Form);"><?php print get_text('Save');?> <IMG style='vertical-align: middle; float: right;' src="./images/save.png"/></SPAN>			
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
			<DIV class='tablehead' style='width: 100%; float: left; z-index: 999'><b><?php print get_text('File Store');?></b></DIV><BR /><BR />					
			<DIV style='padding: 10px; float: left;'>This is used for secure storing of specific <?php print get_text('files');?> for a member.
			<BR />
			Examples could be training certificates, Police record checks etc.
			<BR />
			<BR />					
			</DIV>
		</DIV>
	</DIV>	
	<FORM NAME='can_Form' METHOD="post" ACTION = "member.php?func=member&edit=true&id=<?php print $id;?>"></FORM>			
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
outerwidth = viewportwidth * .98;
outerheight = viewportheight * .95;
colwidth = viewportwidth * .45;
leftcolwidth = viewportwidth * .45;
rightcolwidth = viewportwidth * .35;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
</HTML>						