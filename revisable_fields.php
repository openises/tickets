<?php
/*
*/
require_once('./incs/functions.inc.php');
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
do_login(basename(__FILE__));	// session_start()
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - User Defined Fields for Tickets MDB</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type" CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT type="text/javascript">
window.onresize=function(){set_size()};	
var viewportwidth, viewportheight, outerWidth, outerHeight, colWidth, colHeight;

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
	outerWidth = viewportwidth *.98;
	outerHeigth = viewportheight *.85;
	colWidth = outerWidth * .8;
	colHeight = outerHeight *.6;
	set_fontsizes(viewportwidth, "fullscreen");
	if($('the_fieldlist')) {$('the_fieldlist').style.maxHeight = colHeight + "px";}
	if($('outer')) {$('outer').style.width = outerWidth + "px";}
	if($('outer')) {$('outer').style.height = outerHeight + "px";}
	if($('maincol')) {$('maincol').style.width = colWidth + "px";}
	if($('maincol')) {$('maincol').style.height = colHeight + "px";}
	setWidths();
	}	

function ck_frames() {
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		} else {
		parent.upper.show_butts();
		}
	}		// end function ck_frames()

function go_there (where) {
	document.go_Form.action = where;
	document.go_Form.submit();
	}				// end function go there ()

function setWidths() {
	var theTable = document.getElementById('fieldstable');
	var headerRow = theTable.rows[0];
	var tableRow = theTable.rows[1];
	for (var i = 0; i < tableRow.cells.length; i++) {
		if(tableRow.cells[i] && headerRow.cells[i]) {
			headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";
			}
		}
	}
	
</SCRIPT>		
</HEAD>
<BODY onLoad = 'ck_frames()' style='overflow: hidden;'>
<?php

if(!(empty($_POST))) {
	$query = "UPDATE `$GLOBALS[mysql_prefix]defined_fields` SET
		`label`= " . 		quote_smart(trim($_POST['frm_label'])) . ",
		`size`= " . 		quote_smart(trim($_POST['frm_size'])) . ",
		`fieldset`= " . 		quote_smart(trim($_POST['frm_fieldset'])) . "				
		WHERE `id`= " . 	quote_smart(trim($_POST['frm_fieldid'])) . ";";

	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);	
	if($result) {
		print "CHANGES SUBMITTED";
		} else {
		print "CHANGES FAILED";				
		}
	}

if((empty($_GET)) || ($_GET['edit'] == 'field_submit')) {	
?>
	<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
		<DIV id='button_bar' class='but_container'>
			<SPAN CLASS='text_biggest text_white' style='text-align: center;'>User Defined Fields</SPAN>
			<SPAN id='can_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.forms['can_Form'].submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
		</DIV>
		<DIV ID='maincol' style='position: absolute; top: 60px; width: 80%; padding: 5px; margin-left: 9.5%;'>	
			<DIV class="scrollableContainer" id='fieldlist' style='border: 1px outset #707070;'>
				<DIV class="scrollingArea" id='the_fieldlist'>
					<TABLE id='fieldstable' class='fixedheadscrolling scrollable' style='width: 100%;'>
						<thead>
							<TR style='width: 100%; background-color: #EFEFEF;'>
								<TH class='plain_listheader text'>Name</TH>
								<TH class='plain_listheader text'>Label</TH>
								<TH class='plain_listheader text'>Type</TH>
								<TH class='plain_listheader text'>Fieldset</TH>
								<TH class='plain_listheader text'>Size</TH>
								<TH class='plain_listheader text'>Action</TH>
							</TR>
						</thead>
						<tbody>
<?php
						$z=0;
						for	($i=0; $i < get_field_numbers('member'); $i++) {
							$type = get_field_type('member', $i);
							$name = get_field_name('member', $i);
							$size = get_display_field_size('defined_fields', $i);	
							$fieldset = get_fieldset_name('fieldsets', get_fieldset('defined_fields', $i));	
							$form_field = "frm_" . $name;
							$can_edit = get_editable('defined_fields', $i);
							$inuse = get_field_inuse('member', $name, $i);
							if($can_edit) {
								if ($inuse) {
									$icons="<SPAN ID = 'edit_but" . $i . "' class = 'plain text' style='background: red;'>Edit Field</SPAN>";
									$icons2="<SPAN ID = 'lab_but" . $i . "' class = 'plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = \"go_there('revisable_fields.php?edit=label&id={$i}');\">Edit Label</SPAN>";
									$inuse = 1;
									} else {
									$icons="<SPAN ID = 'edit_but" . $i . "' class = 'plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = \"go_there('revisable_fields.php?edit=field&id={$i}');\">Edit Field</SPAN>";
									$icons2="<SPAN ID = 'lab_but" . $i . "' class = 'plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = \"go_there('revisable_fields.php?edit=label&id={$i}');\">Edit Label</SPAN>";
									$inuse = 0;
									}
								} else {
								$icons="<SPAN ID = 'edit_but" . $i . "' class = 'plain text' style='background: red; color: grey;'>Edit Field</SPAN>";
								$icons2="<SPAN ID = 'lab_but" . $i . "' class = 'plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = \"go_there('revisable_fields.php?edit=label&id={$i}');\">Edit Label</SPAN>";
								}
							if(($name == "id") || ($name == "_by") || ($name == "updated") || ($name == "_on") || ($name == "_from")) {
								} else {
								$label = get_field_label('defined_fields', $i);
								if($type == "ENUM") {
?>
									<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>
										<TD CLASS='plain_list text_bolder'><?php print $name;?></TD>	
										<TD CLASS='plain_list text_bolder'><?php print $label;?></TD>
										<TD CLASS='plain_list text_bolder'><b>ENUM&nbsp;&nbsp;<i>Values</i></b>&nbsp;
<?php
											$temp_options = get_enum_vals('member', $name);
											$output = "";
											foreach($temp_options as $tmp) {
												$output .= "\"$tmp\",";
												}
											$output = rtrim($output, ",");	
											print $output;
?>
										</TD>	
										<TD CLASS='plain_list text_bolder'><?php print $fieldset;?></TD>								
										<TD CLASS='plain_list text_bolder'>NA</TD>			
										<TD CLASS='plain_list text_bolder'><?php print $icons;?>&nbsp;&nbsp;&nbsp;<?php print $icons2;?></TD>	
									</TR>
<?php
									} else {
?>
									<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>
										<TD CLASS='plain_list text_bolder'><?php print $name;?></TD>
										<TD CLASS='plain_list text_bolder'><?php print $label;?></TD>
										<TD CLASS='plain_list text_bolder'><?php print $type;?></TD>
										<TD CLASS='plain_list text_bolder'><?php print $fieldset;?></TD>						
										<TD CLASS='plain_list text_bolder'><?php print $size;?></TD>			
										<TD CLASS='plain_list text_bolder'><?php print $icons;?>&nbsp;&nbsp;&nbsp;<?php print $icons2;?></TD>	
									</TR>			
<?php
									}
								}
							if($z==0) {
								$z=1;
								} else {
								$z=0;
								}
							}
		
?>
						</tbody>
					</TABLE>
				</DIV>
			</DIV>
		</DIV>
	</DIV>
	<FORM NAME='go_Form' METHOD="post" ACTION = ""></FORM>
	<FORM NAME='can_Form' METHOD="post" ACTION = "config.php"></FORM>		
	
<?php
	} elseif ((isset($_GET['edit']) && ($_GET['edit'] == 'field'))) {
	$fieldid = $_GET['id'];
	$type = get_field_type('member', $fieldid);
	$name = get_field_name('member', $fieldid);
	$size = get_display_field_size('defined_fields', $fieldid);	
	$fieldset = get_fieldset('defined_fields', $fieldid);		
	$form_field = "frm_" . $name;
	$inuse = get_field_inuse('member', $name, $fieldid);
	$label = get_field_label('defined_fields', $fieldid);	
?>
	<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
		<DIV id='button_bar' class='but_container'>
			<SPAN CLASS='text_biggest text_white' style='text-align: center;'><b>User Defined Fields - Editing field "<?php print $name;?>"</b></SPAN>
			<SPAN id='can_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.forms['can_Form'].submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
			<SPAN id='sub_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.forms['field_form'].submit();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
		</DIV>
		<DIV ID='maincol' style='position: absolute; top: 60px; width: 80%; padding: 5px; margin-left: 9.5%;'>	
			<FORM METHOD='POST'NAME='field_form' ACTION='revisable_fields.php?edit=field_submit&id=<?php print $fieldid;?>'>
			<FIELDSET>
				<LEGEND>Field Details</LEGEND>				
<?php			
				if($type == "ENUM") {
?>
					<LABEL for="frm_fieldname">Field Name:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_fieldname" VALUE="<?php print $name;?>" READONLY='readonly' STYLE='background: #CECECE;' />
					<BR />	
					<LABEL for="frm_label">Label:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_label" VALUE="<?php print $label;?>" />
					<BR />								
<?php
					$temp_options = get_enum_vals('member', $name);
					$values = "";
					foreach($temp_options as $tmp) {
						$values .= "\"$tmp\",";
						}
					$values = rtrim($values, ",");
?>
					<LABEL for="frm_values">Values for ENUM or Length:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_values" VALUE='<?php print $values;?>' />
					<BR />
					<INPUT TYPE='hidden' NAME='frm_size' VALUE=''>
<?php
					print get_fieldset_control('defined_fields', $fieldid);
					} else {
?>
					<LABEL for="frm_fieldname">Field Name:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_fieldname" VALUE="<?php print $name;?>" READONLY='readonly' STYLE='background: #CECECE;'/>
					<BR />	
					<LABEL for="frm_label">Label:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_label" VALUE="<?php print $label;?>" />
					<BR />								
					<LABEL for="frm_size">Size:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_size" VALUE="<?php print $size;?>" />
					<BR />	
<?php
					print get_fieldset_control('defined_fields', $fieldid);
					}
?>
			</FIELDSET>
			<INPUT TYPE='hidden' NAME='frm_size' VALUE='<?php print $size;?>' />
			<INPUT TYPE='hidden' NAME='frm_fieldid' VALUE='<?php print $fieldid;?>'>					
			</FORM>
		</DIV>
		</DIV>
	<FORM NAME='can_Form' METHOD="post" ACTION = "revisable_fields.php"></FORM>	
		
<?php
	} elseif ((isset($_GET['edit']) && ($_GET['edit'] == 'label'))) {
	$fieldid = $_GET['id'];
	$type = get_field_type('member', $fieldid);
	$name = get_field_name('member', $fieldid);
	$size = get_display_field_size('defined_fields', $fieldid);	
	$fieldset = get_fieldset('defined_fields', $fieldid);		
	$form_field = "frm_" . $name;
	$inuse = get_field_inuse('member', $name, $fieldid);
	$label = get_field_label('defined_fields', $fieldid);	
?>
	<DIV id='outer' style='position: absolute; left: 0px; z-index: 1;'>
		<DIV id='button_bar' class='but_container'>
			<SPAN CLASS='text_biggest text_white' style='text-align: center;'><b>User Defined Fields - Editing field label for "<?php print $name;?>"</b></SPAN>
			<SPAN id='can_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.forms['can_Form'].submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
			<SPAN id='sub_but' class='plain' style='float: right; vertical-align: middle; display: inline-block; width: 100px;;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.forms['field_form'].submit();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
		</DIV>
		<DIV ID='maincol' style='position: absolute; top: 60px; width: 80%; padding: 5px; margin-left: 9.5%;'>	
			<FORM METHOD='POST'NAME='field_form' ACTION='revisable_fields.php?edit=field_submit&id=<?php print $fieldid;?>'>
			<FIELDSET>
				<LEGEND>Field Details</LEGEND>				
<?php			
				if($type == "enum") {
?>
					<LABEL for="frm_fieldname">Field Name:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_fieldname" VALUE="<?php print $name;?>" READONLY='readonly' STYLE='background: #CECECE;' />
					<BR />	
					<LABEL for="frm_label">Label:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_label" VALUE="<?php print $label;?>" />
					<BR />	
<?php
					} else {
?>
					<LABEL for="frm_fieldname">Field Name:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_fieldname" VALUE="<?php print $name;?>" READONLY='readonly' STYLE='background: #CECECE;' />
					<BR />	
					<LABEL for="frm_label">Label:</LABEL>
					<INPUT MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_label" VALUE="<?php print $label;?>" />
					<BR />	
<?php
					}
?>
			<INPUT TYPE='hidden' NAME='frm_fieldset' VALUE='<?php print $fieldset;?>' />	
			<INPUT TYPE='hidden' NAME='frm_size' VALUE='<?php print $size;?>' />
			<INPUT TYPE='hidden' NAME='frm_fieldid' VALUE='<?php print $fieldid;?>'>	
			</FIELDSET>
			</FORM>
		</DIV>
	</DIV>
	<FORM NAME='can_Form' METHOD="post" ACTION = "revisable_fields.php"></FORM>	
		
<?php
	}
?>
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
outerWidth = viewportwidth *.98;
outerHeigth = viewportheight *.70;
colWidth = outerWidth * .80;
colHeight = outerHeight *.65;
set_fontsizes(viewportwidth, "fullscreen");
if($('the_fieldlist')) {$('the_fieldlist').style.maxHeight = colHeight + "px";}
if($('outer')) {$('outer').style.width = outerWidth + "px";}
if($('outer')) {$('outer').style.height = outerHeight + "px";}
if($('maincol')) {$('maincol').style.width = colWidth + "px";}
if($('maincol')) {$('maincol').style.height = colHeight + "px";}
setWidths();
</SCRIPT>
</HTML>