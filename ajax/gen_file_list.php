<?php
/*
9/10/13 - new file, lists stored files allocated to General Tickets use
*/
require_once('../incs/functions.inc.php');

if(empty($_GET)) {
	exit;
	}
$ret_arr=array();	
$where = "WHERE `type` = 1";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` " . $where . " ORDER BY `id` ASC"; 
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_affected_rows() == 0) { 
	$print = "";
	} else {
	$print = "<SELECT ID='f_sel' name='file_select' style='min-width: 150px;'>";	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
		$filename = $row['filename'];
		$orig_filename = $row['orig_filename'];
		$title = $row['title'];
		$type =  $row['type'];
		$print .= "<OPTION VALUE='./ajax/download.php?filename=" . $filename . "&origname=" . $orig_filename . "&type=" . $type . "'>" . $title . "</OPTION>";
		}
		$print .= "</SELECT>";
	}	//	end else

$print2 = "<SPAN id='the_go_but' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onclick='open_FWindow(f_sel.value); hide_files();'>View File</SPAN>";

$ret_arr[0] = $print;
$ret_arr[1] = $print2;
print json_encode($ret_arr);
exit();
?>