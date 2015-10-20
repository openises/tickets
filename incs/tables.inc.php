<?php
/*

*/
	
function check_index($tablename, $index) {
	$indexes = array();
	$query = "SHOW INDEXES FROM `$GLOBALS[mysql_prefix]" . $tablename . "`";
	$result = mysql_query($query);
	$i=0;
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$indexes[$i] = $row['Key_name'];
		$i++;
		}
	if(in_array($index, $indexes)) {
		return true;
		} else {
		return false;
		}
	}
	
function remove_dupes($tablename) {
	$counter = 0;
	$indexes = array();
	$query = "SHOW INDEXES FROM `" . $tablename . "`";
	$result = mysql_query($query);
	$i=0;
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$indexes[$i] = $row['Key_name'];
		$i++;
		}
	foreach($indexes as $theindex) {
		$query = "SHOW INDEXES FROM `" . $tablename . "` WHERE `Key_name` LIKE '" . $theindex . "_%'";
		$result = mysql_query($query);
		if(mysql_num_rows($result) <> 0) {
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$query2 = "DROP INDEX " . $row['Key_name'] . " ON " . $tablename;
				$result2 = mysql_query($query2);
				if($result2) {
					$counter++;
					}
				}
			} else {
			$counter = 0;
			}
		}
	return $counter;
	}
?>