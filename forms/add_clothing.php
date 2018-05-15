<?php
		$id = mysql_real_escape_string($_GET['id']);
		$query	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `$GLOBALS[mysql_prefix]member` `m` 
			WHERE `m`.`id`={$id} LIMIT 1";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$row	= stripslashes_deep(mysql_fetch_assoc($result));
?>
		</HEAD>
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
		</SCRIPT>
		<BODY>	
			<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
				<DIV CLASS='header text_large' style = "height:32px; width: 100%; float: none; text-align: center;">
					<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'><b>Add <?php print get_text('Clothing');?> for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</b></SPAN>
				</DIV>
				<DIV id = "leftcol" style='position: relative; left: 30px; float: left;'>
					<FORM METHOD="POST" NAME= "clothing_add_Form" ACTION="member.php?func=member&goaddcloth=true&extra=edit">
						<FIELDSET>
						<LEGEND>Add <?php print get_text('Clothing');?> Issued</LEGEND>
							<BR />
							<LABEL for="frm_skill"><?php print get_text('Clothing');?> Item:</LABEL>
							<SELECT NAME="frm_skill">
								<OPTION class='normalSelect' VALUE=0 SELECTED>Select</OPTION>
<?php
								$query = "SELECT * FROM `$GLOBALS[mysql_prefix]clothing_types` ORDER BY `clothing_item` ASC";
								$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
									$text = $row['clothing_item'] . " " . $row['size'];
									print "\t<OPTION class='normalSelect' VALUE='{$row['id']}'>{$text}</OPTION>\n";
									}
?>
							</SELECT>
							<BR />
						</FIELDSET>
						<INPUT TYPE="hidden" NAME = "frm_id" VALUE="<?php print $id;?>" />
						<INPUT TYPE="hidden" NAME = "id" VALUE="<?php print $id;?>" />	
						<INPUT TYPE="hidden" NAME = "frm_log_it" VALUE=""/>	
						<INPUT TYPE="hidden" NAME = "frm_remove" VALUE=""/>						
					</FORM>						
				</DIV>
				<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
					<DIV style='position: fixed; top: 50px; z-index: 1;'>
						<SPAN ID = 'can_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?> <IMG style='vertical-align: middle;' src="./images/back_small.png"/></SPAN>
						<SPAN ID = 'sub_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="validate_skills(document.clothing_add_Form);"><?php print get_text('Save');?> <IMG style='vertical-align: middle;' src="./images/save.png"/></SPAN>			
					</DIV>
				</DIV>
				<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
					<DIV class='tablehead text_large text_bold text_center'><?php print get_text('Clothing');?></DIV><BR /><BR />
					<DIV style='padding: 10px; float: left;'><?php print get_text('Clothing');?> Items are those articles of PPE or other classes of <?php print get_text('clothing');?> issued to members.
					<BR />
					<BR />
					<SPAN style='display: inline-block; float: left;'><?php print get_text('Clothing');?> items need to be added first to the system either from "Config" or by clicking</SPAN>
					<SPAN ID='to_clothing' class = 'plain' style='display: inline-block; float: left;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_Post('clothing_types');">Here</SPAN><BR />
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