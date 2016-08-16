<?php
/*
8/10/09	initial release
1/27/10 corrections applied to update field
3/16/10 ceck for empty note
7/12/10 <br. -> '\n'
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
12/1/10 added get_text(disposition)
3/15/11 changed stylesheet.php to stylesheet.php
1/7/2013 added user ident to inserted string, strip_tags as XSS prevention
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('./incs/functions.inc.php');		//7/28/10


if($istest) {
//	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
$theFacility = get_user_facility($_SESSION['user_id']);
if($theFacility == 0) {
	exit();
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Case Categories for Facility</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT>
	String.prototype.trim = function () {				// 3/16/10
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
function validate () {
	document.frm_facnote.submit();	
	}
	
function go_edit(id) {
	document.go_form.edit.value = "true";
	document.go_form.id.value = id;
	document.go_form.submit();
	}
	
function go_view(id) {
	document.go_form.view.value = "true";
	document.go_form.id.value = id;
	document.go_form.submit();
	}
	
function go_add() {
	document.go_form.add.value = "true";
	document.go_form.submit();
	}
</SCRIPT>
<STYLE>
	table.cruises { font-family: verdana, arial, helvetica, sans-serif; font-size: 11px; cellspacing: 0; border-collapse: collapse; }
	table.cruises td {overflow: hidden; }
	div.scrollableContainer { position: relative; padding-top: 2em; border: 1px solid #999; }
	div.scrollingArea { max-height: 200px; overflow: auto; overflow-x: hidden; }
	table.scrollable thead tr { position: absolute; left: -1px; top: 0px; }
	table.cruises th { text-align: center; overflow: hidden; font-size: 18px; border: 2px outset #505050; cursor: default;}
	.plain_listheader 	{color:#000000; border: 1px outset #606060;	text-decoration: none; background-color: #DEE3E7; font-weight: bolder;}
	.hover_listheader 	{color:#000000; border: 1px inset #606060; text-decoration: none; background-color: #DEE3E7; font-weight: bolder;}
	.listEntry 	{padding: 0px 10px 0px 10px; text-align: left; word-wrap: normal; white-space: normal; color: inherit; border: 1px inset #606060; text-decoration: none; background-color: inherit; font-weight: bolder; cursor: pointer; font-size: 14px;}
	.noentries 	{padding: 0px 10px 0px 10px; text-align: center; word-wrap: normal; white-space: normal; color:#FFFFFF; border: 1px inset #606060; text-decoration: none; background-color: green; font-weight: bolder; font-size: 14px; cursor: default;}

</STYLE>
</HEAD>
<BODY>
<SPAN CLASS='header' style='text-align: center; width: 100%; display: inline-block;'>Facility Case Categories</SPAN><BR /><BR />
<DIV style='text-align: center; width: 100%; display: block;'>
<?php
if(!empty($_POST)) {
	$goadd = (array_key_exists('goadd', $_GET) && $_GET['goadd'] == true) ? true : false;
	$goedit = (array_key_exists('goedit', $_GET) && $_GET['goedit'] == true) ? true : false;
	if($goadd) {
		$query  = "INSERT INTO `$GLOBALS[mysql_prefix]fac_case_cat` (
				`category`, `description`, `color`, `bgcolor`, `facility`
				) VALUES (" .
				quote_smart(trim($_POST['frm_category'])) . "," .
				quote_smart(trim($_POST['frm_description'])) . "," .
				quote_smart(trim($_POST['frm_color'])) . "," .
				quote_smart(trim($_POST['frm_bgcolor'])) . "," .
				quote_smart(trim($_POST['frm_facility'])) . ");";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		if($result) {
			print "Case Category inserted<BR /><BR />";
?>
			<TABLE>
				<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
					<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
						<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>More</SPAN>
						<SPAN id='close_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
					</TD>
				</TR>
			</TABLE>
<?php
			} else {
			print "Case Category could not be inserted<BR /><BR />";
?>
			<TABLE>
				<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
					<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
						<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Back</SPAN>
						<SPAN id='close_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
					</TD>
				</TR>
			</TABLE>
<?php				
			}
		} elseif($goedit) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]fac_case_cat` SET
			`category`= " . 	quote_smart(trim($_POST['frm_category'])) . ",
			`description`= " . 	quote_smart(trim($_POST['frm_description'])) . ",
			`color`= " . 		quote_smart(trim($_POST['frm_color'])) . ",
			`bgcolor`= " . 		quote_smart(trim($_POST['frm_bgcolor'])) . ",
			`facility`= " . 	quote_smart(trim($_POST['frm_facility'])) . "
			WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
		if($result) {
			print "<CENTER>Case Category updated<BR /><BR />";
?>
			<TABLE>
				<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
					<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
						<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>More</SPAN>
						<SPAN id='close_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
					</TD>
				</TR>
			</TABLE>
			</CENTER>
<?php
			} else {
			print "<CENTER>Case Category could not be updated<BR /><BR />";
?>
			<TABLE>
				<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
					<TD COLSPAN="2" ALIGN="center" style='vertical-align: middle;'>
						<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Back</SPAN>
						<SPAN id='close_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
					</TD>
				</TR>
			</TABLE>
			</CENTER>
<?php				
			}
		} else {
		print "Error in Script<BR />";
		}
	} elseif((empty($_POST)) && (empty($_GET))) { 		//	Initial List
?>
	<CENTER>
	<TABLE>
		<TR class='heading'>
			<TH class='heading' style='padding: 0px 10px 0px 10px; text-align: left; border: 1px outset #707070;'>Category</TH>
			<TH class='heading' style='padding: 0px 10px 0px 10px; text-align: left; border: 1px outset #707070;'>Description</TH>
			<TH class='heading' style='padding: 0px 10px 0px 10px; text-align: left; border: 1px outset #707070;'>Text Color</TH>
			<TH class='heading' style='padding: 0px 10px 0px 10px; text-align: left; border: 1px outset #707070;'>Background Color</TH>
		</TR>
<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_case_cat`";		
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$class = "even";
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		print "<TR class='" . $class . "' onClick='go_view(" . $row['id'] . ");'>";
		print "<TD class='listEntry' style='color: " . $row['color'] . "; background-color: " . $row['bgcolor'] . ";text-align: left; border: 1px outset #707070;'>" . $row['category'] . "</TD>";
		print "<TD class='listEntry' style='text-align: left; border: 1px outset #707070;'>" . htmlentities(shorten($row['description'], 30), ENT_QUOTES) . "</TD>";
		print "<TD class='listEntry' style='text-align: left; border: 1px outset #707070;'>" . $row['color'] . "</TD>";
		print "<TD class='listEntry' style='text-align: left; border: 1px outset #707070;'>" . $row['bgcolor'] . "</TD>";
		print "</TR>";
		$class = ($class = "even") ? "odd" : "even";
		}
?>
		<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
			<TD COLSPAN=99 ALIGN="center" style='vertical-align: middle;'>
				<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='go_add()();'>Add</SPAN>
				<SPAN id='close_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
			</TD>
		</TR>
	</TABLE>
	</CENTER>
<?php
	} elseif((empty($_POST)) && (array_key_exists('add', $_GET) && $_GET['add'] == true)) {
?>
	<CENTER>
	<H4>Add Case Category</H4>
	<TABLE>
	<FORM NAME='frm_add' METHOD='post' ACTION = 'faccategories.php?goadd=true'>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Name:</TD>
			<TD class='td_data' style='border: 1px outset #707070;'><INPUT NAME="frm_category" tabindex=1 SIZE="48" MAXLENGTH="64" TYPE="text" VALUE="" /></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Description: </TD>
			<TD class='td_data' style='border: 1px outset #707070;'><TEXTAREA NAME='frm_description' tabindex=2 COLS=60 ROWS = 3></TEXTAREA></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Text Color: </TD>
			<TD class='td_data' style='border: 1px outset #707070;'><INPUT NAME="frm_color" tabindex=3 SIZE="7" MAXLENGTH="7" TYPE="text" VALUE="" /></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Background Color: </TD>
			<TD class='td_data' style='border: 1px outset #707070;'><INPUT NAME="frm_bgcolor" tabindex=4 SIZE="7" MAXLENGTH="7" TYPE="text" VALUE="" /></TD>
		</TR>
		<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
			<TD COLSPAN=99 ALIGN="center" style='vertical-align: middle;'>
				<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN>
				<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.frm_add.submit();'>Submit</SPAN>
			</TD>
		</TR>
	<INPUT TYPE = 'hidden' NAME = 'frm_facility' VALUE='<?php print $theFacility; ?>' />
	</FORM>
	</TABLE>
	</CENTER>
<?php
	} elseif((empty($_POST)) && (array_key_exists('edit', $_GET) && $_GET['edit'] == true)) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_case_cat` WHERE `id` = " . $_GET['id'];		
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$id = $_GET['id'];
	$category = $row['category'];
	$description = htmlentities($row['description'], ENT_QUOTES);
	$color = $row['color'];
	$bgcolor = $row['bgcolor'];
	$facility = $row['facility'];
?>
	<CENTER>
	<H4>Edit Case Category</H4>
	<TABLE>
	<FORM NAME='frm_edit' METHOD='post' ACTION = 'faccategories.php?goedit=true'>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Name:</TD>
			<TD class='td_data' style='border: 1px outset #707070;'><INPUT NAME="frm_category" tabindex=1 SIZE="48" MAXLENGTH="64" TYPE="text" VALUE="<?php print $category;?>" /></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Description: </TD>
			<TD class='td_data' style='border: 1px outset #707070;'><TEXTAREA NAME='frm_description' tabindex=2 COLS=60 ROWS = 3><?php print $description;?></TEXTAREA></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Text Color: </TD>
			<TD class='td_data' style='border: 1px outset #707070;'><INPUT NAME="frm_color" tabindex=3 SIZE="7" MAXLENGTH="7" TYPE="text" VALUE="<?php print $color;?>" /></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Background Color: </TD>
			<TD class='td_data' style='border: 1px outset #707070;'><INPUT NAME="frm_bgcolor" tabindex=4 SIZE="7" MAXLENGTH="7" TYPE="text" VALUE="<?php print $bgcolor;?>" /></TD>
		</TR>
		<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
			<TD COLSPAN=99 ALIGN="center" style='vertical-align: middle;'>
				<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Cancel</SPAN>
				<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.frm_edit.submit();'>Submit</SPAN>
			</TD>
		</TR>
	<INPUT TYPE = 'hidden' NAME = 'frm_id' VALUE='<?php print $id; ?>' />
	<INPUT TYPE = 'hidden' NAME = 'frm_facility' VALUE='<?php print $theFacility; ?>' />	
	</FORM>
	</TABLE>
	</CENTER>
<?php
	} elseif((empty($_POST)) && (array_key_exists('view', $_GET) && $_GET['view'] == true)) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_case_cat` WHERE `id` = " . $_GET['id'];		
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$id = $_GET['id'];
	$category = $row['category'];
	$description = htmlentities($row['description'], ENT_QUOTES);
	$color = $row['color'];
	$bgcolor = $row['bgcolor'];
	$facility = $row['facility'];
?>
	<CENTER>
	<H4>View Case Category</H4>
	<TABLE style='width: 400px;'>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Name:</TD>
			<TD class='td_data' style='border: 1px outset #707070;'><?php print $category;?></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Description: </TD>
			<TD class='td_data' style='word-wrap: normal; white-space: normal; border: 1px outset #707070;'><?php print $description;?></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Text Color: </TD>
			<TD class='td_data' style='border: 1px outset #707070;'><?php print $category;?></TD>
		</TR>
		<TR>
			<TD class='td_label' style='border: 1px outset #707070;'>Background Color: </TD>
			<TD class='td_data' style='border: 1px outset #707070;'><?php print $category;?></TD>
		</TR>
		<TR CLASS="odd" style='height: 30px; vertical-align: middle;'>
			<TD COLSPAN=99 ALIGN="center" style='vertical-align: middle;'>
				<SPAN id='can_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'>Back</SPAN>
				<SPAN id='sub_but' CLASS='plain' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='go_edit(<?php print $id;?>);'>Edit</SPAN>
			</TD>
		</TR>
	</TABLE>
	</CENTER>
<?php
	} else {
	print "Error<BR />";
	}		// end if (empty($_POST))
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "faccategories.php"></FORM>
<FORM NAME='go_form' METHOD='get' ACTION='faccategories.php'>
<INPUT TYPE='hidden' NAME='edit' VALUE=''>
<INPUT TYPE='hidden' NAME='add' VALUE=''>
<INPUT TYPE='hidden' NAME='view' VALUE=''>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>
</DIV>
</BODY>
</HTML>