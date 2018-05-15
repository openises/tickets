<?php 
/*
8/28/08 mysql_fetch_array to  mysql_fetch_assoc
9/19/08 add injection protection to query parameters
1/21/09 added show butts - re button menu
2/24/09 added dollar function
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
do_login(basename(__FILE__));
if ($istest) {
	dump ($_POST);
	dump ($_GET);
	}
$evenodd = array ("even", "odd");

?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - MDB Search Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size();}
var viewportwidth, viewportheight, outerwidth, outerheight, colwidth, leftcolwidth, rightcolwidth, mapWidth, mapHeight;

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
	mapWidth = viewportwidth * .35;
	mapHeight = viewportheight * .80;
	colwidth = viewportwidth * .45;
	leftcolwidth = viewportwidth * .45;
	rightcolwidth = viewportwidth * .35;
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
	set_fontsizes(viewportwidth, "fullscreen");
	}

function ck_frames() {		//  onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	}

try {
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
	}
catch(e) {
	}
	
function $() {									// 2/11/09
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
			element = document.getElementById(element);
		if (arguments.length == 1)
			return element;
		elements.push(element);
		}
	return elements;
	}

function validate(theForm) {
	function TrimString(sInString) {
		sInString = sInString.replace( /^\s+/g, "" );// strip leading
		return sInString.replace( /\s+$/g, "" );// strip trailing
		}
	theForm.frm_query.value = TrimString(document.queryForm.frm_query.value);
	return true;
	}				// end function validate(theForm)
</SCRIPT>
<SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo get_variable('gmaps_api_key'); ?>"></SCRIPT>
</HEAD>

<BODY onLoad = "ck_frames()">
<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
	<DIV CLASS='header' style = "height:32px; width: 100%; float: none; text-align: center;">
		<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'>Tickets - MDB Search</SPAN>
	</DIV>
<?php 
	$post_frm_query = (array_key_exists('frm_query', ($_POST))) ? $_POST['frm_query']  : "" ;

	if ($post_frm_query) {
?>
		<DIV id='rightcol' style='display: inline-block; position: relative; left: 50px;'>
<?php
		print "<FONT CLASS='header text_large'>Search results for '$_POST[frm_query]'</FONT><BR /><BR />\n";
		$patterns = 
		$_POST['frm_query'] = str_replace(' ', '|', $_POST['frm_query']);
		if($_POST['frm_search_in'])	{	//what field are we searching?
			$search_fields = "$_POST[frm_search_in] REGEXP '$_POST[frm_query]'";
			} else {
			//list fields and form the query to search all of them
			$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]member`");
			$search_fields = "";
			for ($i = 0; $i < mysql_num_fields($result); $i++)
    			$search_fields .= mysql_field_name($result, $i) ." REGEXP '" . $_POST['frm_query'] . "' OR ";
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);
			}
		
		$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";		// 9/19/08

		$query = "SELECT *,`field16` AS `duedate`,
		`field17` AS `joindate`,
		`field18` AS `dob` ,
		`field21` AS `member_status`,
		`_on` AS `updated` 
		FROM `$GLOBALS[mysql_prefix]member` 
		WHERE " . $search_fields . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;		// 9/19/08
		$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
		if (mysql_num_rows($result)) {
			$member_found = $counter = 1;
			print "<TABLE BORDER='0' CELLPADDING='5'><TR CLASS='odd'><TH class='header text_large'>In Member Data</TH></TR><TR CLASS='even'><TD CLASS='td_header text'>Member</TD><TD CLASS='td_header text'>Date</TD><TD CLASS='td_header text'>Description</TD><TD CLASS='td_header text'>Status</TD></TR>";
			while($row = stripslashes_deep(mysql_fetch_assoc($result))){				// 8/28/08
				print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD CLASS='text' ><A CLASS='text' HREF='member.php?view=true&id={$row['id']}'>#{$row['id']}</A>&nbsp;&nbsp;</TD><TD CLASS='text' >".format_date(strtotime($row['_on']))."&nbsp;&nbsp;&nbsp;</TD><TD CLASS='text' ><A CLASS='text'  HREF='member.php?view=true&id={$row['id']}'>" . $row['field2'] . " " . $row['field1'] . "</A></TD><TD CLASS='text' >".get_status_name($row['member_status'])."</TD></TR>\n";				// 2/25/09
				$counter++;
				}
			
			print '</TABLE><BR /><BR />';
			} else {
			print 'No matching member data found.  <BR /><BR />';
			}

			$query = "SELECT 
				`a`.`completed` AS `completed`,
				`a`.`refresh_due` AS `refresh_due`,
				`a`.`member_id` AS `member_id`,
				`tp`.`package_name` AS `package_name`,
				`tp`.`description` AS `description`	 
				FROM `$GLOBALS[mysql_prefix]allocations` `a`
				LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` `tp` ON ( `a`.`skill_id` = tp.id AND `a`.`skill_type`= '1' )";
			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			
			$search_fields = "";
			for ($i = 0; $i < mysql_num_fields($result); $i++)
				if((mysql_field_name($result, $i) != 'id')) {
					$search_fields .= mysql_field_name($result, $i) ." REGEXP '" . $_POST['frm_query'] . "' OR ";
				}
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);
			
			$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";

			$query = "SELECT 
			`a`.`completed` AS `completed`,
			`a`.`refresh_due` AS `refresh_due`,
			`a`.`_on` AS `_on`,
			`m`.`_on` AS `member_updated`,
			`m`.`id` AS `id`,
			`a`.`member_id` AS `member_id`,
			`tp`.`package_name` AS `package_name`,
			`tp`.`description` AS `description`,
			`m`.`field1` AS `field1`,
			`m`.`field2` AS `field2`				
			FROM `$GLOBALS[mysql_prefix]allocations` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]training_packages` `tp` ON ( `a`.`skill_id` = tp.id AND `a`.`skill_type`= '1' )
			LEFT JOIN `$GLOBALS[mysql_prefix]member` `m` ON ( `a`.`member_id` = `m`.`id` )					
			WHERE " . $search_fields . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;
			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			if (mysql_num_rows($result)) {
				$member_found = $counter = 1;
				print "<TABLE BORDER='0' CELLPADDING='5'><TR CLASS='odd'><TH class='header text_large'>In Training</TH></TR><TR CLASS='even'><TD CLASS='td_header text'>Member</TD><TD CLASS='td_header text'>Date</TD><TD CLASS='td_header text'>Description</TD><TD CLASS='td_header text'>Status</TD></TR>";
				while($row = stripslashes_deep(mysql_fetch_assoc($result))){
					print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD><A HREF='member.php?view=true&id={$row['id']}'>#{$row['id']}</A>&nbsp;&nbsp;</TD><TD>".format_date(strtotime($row['_on']))."&nbsp;&nbsp;&nbsp;</TD><TD><A HREF='member.php?view=true&id={$row['id']}'>" . $row['field2'] . " " . $row['field1'] . "</A></TD></TR>\n";				// 2/25/09
					$counter++;
					}
				
				print '</TABLE><BR /><BR />';
				} else {
				print 'No matching allocated training found.  <BR /><BR />';
				}
				
														//Capabilities

			$query = "SELECT 
				`a`.`member_id` AS `member_id`,
				`ca`.`name` AS `name`,
				`ca`.`description` AS `description`	 
				FROM `$GLOBALS[mysql_prefix]allocations` `a`
				LEFT JOIN `$GLOBALS[mysql_prefix]capability_types` `ca` ON ( `a`.`skill_id` = ca.id AND `a`.`skill_type`= '2' )";
			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			
			$search_fields = "";
			for ($i = 0; $i < mysql_num_fields($result); $i++) {
				if((mysql_field_name($result, $i) != 'id')) {
					$search_fields .= mysql_field_name($result, $i) ." REGEXP '" . $_POST['frm_query'] . "' OR ";
					}
				}
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);
			
			$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";

			$query = "SELECT 
			`a`.`member_id` AS `member_id`,
			`ca`.`name` AS `equipment_name`,
			`ca`.`description` AS `description`, 
			`a`.`_on` AS `_on`,
			`m`.`_on` AS `member_updated`,		
			`m`.`field1` AS `field1`,
			`m`.`field2` AS `field2`			
			FROM `$GLOBALS[mysql_prefix]allocations` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]capability_types` `ca` ON ( `a`.`skill_id` = `ca`.`id` AND `a`.`skill_type`= '2' )	
			LEFT JOIN `$GLOBALS[mysql_prefix]member` `m` ON ( `a`.`member_id` = `m`.`id` )				
			WHERE " . $search_fields . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;
			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			if (mysql_num_rows($result)) {
				$member_found = $counter = 1;
				print "<TABLE BORDER='0' CELLPADDING='5'><TR CLASS='odd'><TH class='header text_large'>In Capabilities Data</TH></TR><TR CLASS='even'><TD CLASS='td_header text'>Member</TD><TD CLASS='td_header text'>Date</TD><TD CLASS='td_header text'>Description</TD><TD CLASS='td_header text'>Status</TD></TR>";
				while($row = stripslashes_deep(mysql_fetch_assoc($result))){
					print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD><A HREF='member.php?view=true&id={$row['member_id']}'>#{$row['member_id']}</A>&nbsp;&nbsp;</TD><TD>".format_date(strtotime($row['_on']))."&nbsp;&nbsp;&nbsp;</TD><TD><A HREF='member.php?view=true&id={$row['member_id']}'>" . $row['field2'] . " " . $row['field1'] . "</A></TD></TR>\n";				// 2/25/09
					$counter++;
					}
				
				print '</TABLE><BR /><BR />';
				} else {
				print 'No matching member capabilities found.  <BR /><BR />';
				}
				
														//Equipment

			$query = "SELECT 
				`a`.`member_id` AS `member_id`,
				`eq`.`equipment_name` AS `equipment_name`,
				`eq`.`description` AS `description`	 
				FROM `$GLOBALS[mysql_prefix]allocations` `a`
				LEFT JOIN `$GLOBALS[mysql_prefix]equipment_types` `eq` ON ( `a`.`skill_id` = `eq`.`id` AND `a`.`skill_type`= '2' )";
			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			
			$search_fields = "";
			for ($i = 0; $i < mysql_num_fields($result); $i++) {
				if((mysql_field_name($result, $i) != 'id')) {
					$search_fields .= mysql_field_name($result, $i) ." REGEXP '" . $_POST['frm_query'] . "' OR ";
					}
				}
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);

			
			$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";

			$query = "SELECT 
			`a`.`member_id` AS `member_id`,
			`eq`.`equipment_name` AS `name`,
			`eq`.`description` AS `description`,	 
			`a`.`_on` AS `_on`,
			`m`.`_on` AS `member_updated`,			
			`m`.`field1` AS `field1`,
			`m`.`field2` AS `field2`			
			FROM `$GLOBALS[mysql_prefix]allocations` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]equipment_types` `eq` ON ( `a`.`skill_id` = `eq`.`id` AND `a`.`skill_type`= '3' )	
			LEFT JOIN `$GLOBALS[mysql_prefix]member` `m` ON ( `a`.`member_id` = `m`.`id` )				
			WHERE " . $search_fields . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;

			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);

			if (mysql_num_rows($result)) {
				$member_found = $counter = 1;
				print "<TABLE BORDER='0' CELLPADDING='5'><TR CLASS='odd'><TH class='header text_large'>In Equipment Data</TH></TR><TR CLASS='even'><TD CLASS='td_header text'>Member</TD><TD CLASS='td_header text'>Date</TD><TD CLASS='td_header text'>Description</TD><TD CLASS='td_header text'>Status</TD></TR>";
				while($row = stripslashes_deep(mysql_fetch_assoc($result))){
					print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD><A HREF='member.php?view=true&id={$row['member_id']}'>#{$row['member_id']}</A>&nbsp;&nbsp;</TD><TD>".format_date(strtotime($row['_on']))."&nbsp;&nbsp;&nbsp;</TD><TD><A HREF='member.php?view=true&id={$row['member_id']}'>" . $row['field2'] . " " . $row['field1'] . "</A></TD></TR>\n";				// 2/25/09
					$counter++;
					}
				
				print '</TABLE><BR /><BR />';
				} else {
				print 'No matching member equipment allocation found.  <BR /><BR />';
				}
				
														//Clothing

			$query = "SELECT 
				`a`.`member_id` AS `member_id`,
				`cl`.`clothing_item` AS `clothing_item`,
				`cl`.`description` AS `description`	 
				FROM `$GLOBALS[mysql_prefix]allocations` `a`
				LEFT JOIN `$GLOBALS[mysql_prefix]clothing_types` `cl` ON ( `a`.`skill_id` = `cl`.`id` AND `a`.`skill_type`= '2' )";
			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			
			$search_fields = "";
			for ($i = 0; $i < mysql_num_fields($result); $i++) {
				if((mysql_field_name($result, $i) != 'id')) {
					$search_fields .= mysql_field_name($result, $i) ." REGEXP '" . $_POST['frm_query'] . "' OR ";
					}
				}
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);
			
			$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";

			$query = "SELECT 
			`a`.`member_id` AS `member_id`,
			`cl`.`clothing_item` AS `clothing_item`,
			`cl`.`description` AS `description`,	 
			`a`.`_on` AS `_on`,
			`m`.`_on` AS `member_updated`,		
			`m`.`field1` AS `field1`,
			`m`.`field2` AS `field2`			
			FROM `$GLOBALS[mysql_prefix]allocations` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]clothing_types` `cl` ON ( `a`.`skill_id` = `cl`.`id` AND `a`.`skill_type`= '3' )	
			LEFT JOIN `$GLOBALS[mysql_prefix]member` `m` ON ( `a`.`member_id` = `m`.`id` )				
			WHERE " . $search_fields . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;

			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			if (mysql_num_rows($result)) {
				$member_found = $counter = 1;
				print "<TABLE BORDER='0' CELLPADDING='5'><TR CLASS='odd'><TH class='header text_large'>In Clothing Data</TH></TR><TR CLASS='even'><TD CLASS='td_header text'>Member</TD><TD CLASS='td_header text'>Date</TD><TD CLASS='td_header text'>Description</TD><TD CLASS='td_header text'>Status</TD></TR>";
				while($row = stripslashes_deep(mysql_fetch_assoc($result))){
					print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD><A HREF='member.php?view=true&id={$row['member_id']}'>#{$row['member_id']}</A>&nbsp;&nbsp;</TD><TD>".format_date(strtotime($row['_on']))."&nbsp;&nbsp;&nbsp;</TD><TD><A HREF='member.php?view=true&id={$row['member_id']}'>" . $row['field2'] . " " . $row['field1'] . "</A></TD></TR>\n";				// 2/25/09
					$counter++;
					}
				
				print '</TABLE><BR /><BR />';
				} else {
				print 'No matching member clothing allocation found.  <BR /><BR />';
				}				

														//Files

			$query = "SELECT 
			`f`.`name` AS `name`,			
			`f`.`shortname` AS `shortname`
			FROM `$GLOBALS[mysql_prefix]mdb_files` `f`";
			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
			
			$search_fields = "";
			for ($i = 0; $i < mysql_num_fields($result); $i++) {
				if((mysql_field_name($result, $i) != 'id')) {
					$search_fields .= mysql_field_name($result, $i) ." REGEXP '" . $_POST['frm_query'] . "' OR ";
					}
				}
			$search_fields = substr($search_fields,0,strlen($search_fields) - 4);
			
			$desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";

			$query = "SELECT `f`.`member_id` AS `member_id`,
			`f`.`name` AS `name`,			
			`f`.`shortname` AS `shortname`,			
			`f`.`_on` AS `_on`,	
			`m`.`_on` AS `member_updated`,				
			`m`.`field1` AS `field1`,
			`m`.`field2` AS `field2`			
			FROM `$GLOBALS[mysql_prefix]mdb_files` `f`
			LEFT JOIN `$GLOBALS[mysql_prefix]member` `m` ON ( `f`.`member_id` = `m`.`id` )				
			WHERE " . $search_fields . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;

			$result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);

			if (mysql_num_rows($result)) {
				$member_found = $counter = 1;
				print "<TABLE BORDER='0' CELLPADDING='5'><TR CLASS='odd'><TH class='header text_large'>In Files</TH></TR><TR CLASS='even'><TD CLASS='td_header text'>Member</TD><TD CLASS='td_header text'>Date</TD><TD CLASS='td_header text'>Description</TD><TD CLASS='td_header text'>Status</TD></TR>";
				while($row = stripslashes_deep(mysql_fetch_assoc($result))){
					print "<TR CLASS='" . $evenodd[$counter%2] . "'><TD><A HREF='member.php?view=true&id={$row['member_id']}'>#{$row['member_id']}</A>&nbsp;&nbsp;</TD><TD>".format_date(strtotime($row['_on']))."&nbsp;&nbsp;&nbsp;</TD><TD><A HREF='member.php?view=true&id={$row['member_id']}'>" . $row['field2'] . " " . $row['field1'] . "</A></TD></TR>\n";				// 2/25/09
					$counter++;
					}
				
				print '</TABLE><BR /><BR />';
				} else {
				print "<DIV id='rightcol' style='display: inline-block; position: relative; left: 50px;'>";
				print 'No matching data in member files found.  <BR /><BR />';
				print "</DIV>";
				}
		print "	</DIV>";				
		} else {
		print "<DIV id='rightcol' style='display: inline-block; position: relative; left: 50px;'>";
		print "<FONT CLASS='header' style='float: right;'>Search Results will show here.</FONT><BR /><BR />\n";
		print "</DIV>";
		}	// end if ($_POST['frm_query'])
?>

	<DIV id = "leftcol" style='padding-left: 100px; display: inline-block; float: left;'>		
		<FORM METHOD="post" NAME="queryForm" ACTION="mdb_search.php" onSubmit="return validate(document.queryForm)">
		<TABLE id='searchtable' CELLPADDING="2" BORDER="0">
			<TR>
				<TD style='width: 80%;'>
					<TABLE id='searchtable' style='width: 100%;'>
						<TR CLASS = "even">
							<TD VALIGN="top" CLASS="td_label text">Query: &nbsp;</TD>
							<TD>
								<INPUT TYPE="text" SIZE="40" MAXLENGTH="255" VALUE="<?php print $post_frm_query;?>" NAME="frm_query">
							</TD>
						</TR>
						<TR CLASS = "odd">
							<TD VALIGN="top" CLASS="td_label text">Search in: &nbsp;</TD>
							<TD>
								<SELECT NAME="frm_search_in">
									<OPTION VALUE="" checked>All</OPTION>
									<OPTION VALUE="field9">Address</OPTION>
									<OPTION VALUE="field10">City</OPTION>
									<OPTION VALUE="field20">Description</OPTION>
									<OPTION VALUE="_by">Owner</OPTION>
								</SELECT>
							</TD>
						</TR>
						<TR CLASS = "even">
							<TD VALIGN="top" CLASS="td_label text">Order By: &nbsp;</TD>
							<TD>
								<SELECT NAME="frm_ordertype">
									<OPTION VALUE="_on">Updated</OPTION>
									<OPTION VALUE="field1">Surname</OPTION>
								</SELECT>&nbsp;
								Descending: <INPUT TYPE="checkbox" NAME="frm_order_desc" VALUE="DESC" CHECKED>
							</TD>
						</TR>
						<TR CLASS = "even">
							<TD></TD>
							<TD ALIGN = "center">
								<SPAN class='plain text' id='reset_but' style='width: 100px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="document.queryForm.reset();"><?php print get_text('Reset');?> <IMG style='vertical-align: middle; float: right;' src="./images/reset.png"/></SPAN>
								<SPAN class='plain text' id='sub_but' style='width: 100px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick="document.queryForm.submit();"><?php print get_text('Submit');?> <IMG style='vertical-align: middle; float: right;' src="./images/submit_small.png"/></SPAN>
							</TD>
						</TR>
					</TABLE>
				</TD>
				<TD style='width: 15%;'>
					<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
						<DIV style='position: fixed; top: 50px; z-index: 1;'>
							<SPAN ID = 'cancel_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?><BR /><IMG src="./images/back_small.png"/></SPAN>
						</DIV>
					</DIV>
				</TD>
			</TR>
		</TABLE>
		</FORM>
	</DIV>
</DIV>
<FORM NAME='can_Form' METHOD="post" ACTION = "member.php"></FORM>
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
mapWidth = viewportwidth * .35;
mapHeight = viewportheight * .80;
colwidth = viewportwidth * .45;
leftcolwidth = viewportwidth * .45;
rightcolwidth = viewportwidth * .35;
if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
</HTML>
