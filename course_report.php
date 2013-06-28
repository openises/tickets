<?php
/*
12/15/11 - initial release
12/19/11 'group' => 'group_name'
*/
error_reporting(E_ALL);	
require_once('./incs/functions.inc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Classes Taken Report</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"> <!-- 3/15/11 -->
</HEAD>
<BODY>

<?php
//		dump($_POST);
		$where = (intval($_POST['user_id']) > 0)? " WHERE `t`.`user_id` = {$_POST['user_id']}" : "";
		
		
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]courses_taken` `t`
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON `u`.`id`=`t`.`user_id` 			
			LEFT JOIN `$GLOBALS[mysql_prefix]courses` `c` ON `t`.`courses_id`=`c`.`id` 
			{$where}
			ORDER BY `u`.`name_l` ASC, `u`.`name_f` ASC, `c`.`group_name` ASC, `c`.`course` ASC";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

		if (mysql_num_rows($result)==0) {		// no results - get selected userid 
			$query 	= "SELECT * FROM  `$GLOBALS[mysql_prefix]user` WHERE `id` = {$_POST['user_id']} LIMIT 1";    			
			$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			$the_user_str = "";
			if (mysql_num_rows($result)>0) {		// got a name?
				$row = stripslashes_deep(mysql_fetch_assoc($result));
				$the_user_str = " for {$row['user']}";
				}
		
			echo "<BR /><BR /><center><H2>No class data{$the_user_str}</H2><BR /><BR /><BR />";
			}
		else {
			$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
			$i = 0;
			$cum_credits = 0;
			$this_user= 0;
			echo "<BR /><BR /><TABLE BORDER = 1 ALIGN=CENTER CELLPADDING = 2>
				<TR CLASS = 'even'><TH COLSPAN=99>Classes Taken - <i><small>as of " . date('M j, y', time()) . "</small></i></TH></TR>";
			
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
				if ($row['user_id'] == $this_user ) {$cum_credits += $row['credits'];}
				else								{$cum_credits = $row['credits']; $this_user = $row['user_id'];}
				echo "<TR CLASS='{$evenodd[($i+1)%2]}' VALIGN='baseline'>\n\t\t
					<TD>{$row['name_l']}, {$row['name_f']} {$row['name_mi']}</TD>
					<TD>{$row['email']}</TD>
					<TD>{$row['group_name']}</TD>
					<TD>{$row['course']}</TD>
					<TD>{$row['ident']}</TD>
					<TD>{$row['credits']}</TD>
					<TD>{$cum_credits}</TD>
					<TD>{$row['date']}</TD>
					";
				$i++;
				}
?>
	</TABLE>
<?php
	}			// end if/else
?>

	<FORM NAME = 'course_form' METHOD = 'post' ACTION = '<?php echo basename(__FILE__)?>'>
	<INPUT TYPE = 'hidden' NAME = 'user_id' VALUE = ''>
	</FORM>
	<SPAN STYLE='text-align: center; display: block;'><BR />
	Another &raquo; <SELECT NAME='frm_user_id' onChange = "document.course_form.user_id.value=this.options[this.selectedIndex].value; document.course_form.submit();">
				<OPTION VALUE='' selected>Select</OPTION>
				<OPTION VALUE='0' >All users</OPTION>
<?php
	$query 	= "SELECT * FROM  `$GLOBALS[mysql_prefix]user` WHERE ((`name_l` IS NOT NULL) AND (LENGTH(`name_l`) > 0)) ORDER BY `name_l` ASC, `name_f` ASC";    			
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_opt = shorten("({$row['user']}) {$row['name_l']}, {$row['name_f']} {$row['name_mi']} ", 48);
		echo "\t\t<OPTION VALUE='{$row['id']}'>{$the_opt}</OPTION>\n";
		}				// end while()
?>
			</SELECT>

<INPUT TYPE = 'button' VALUE= "Finished" onClick = "window.close();" STYLE = 'margin-left: 40px;'>
</span>
</BODY>
</HTML>
