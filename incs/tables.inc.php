<?php
/*

*/
	
function check_index($tablename, $index) {
	$indexes = array();
	$query = "SHOW INDEXES FROM `{$GLOBALS['mysql_prefix']}" . $tablename . "`";
	$result = db_query($query);
	$i=0;
	while ($row = stripslashes_deep($result->fetch_assoc())) {
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
	$result = db_query($query);
	$i=0;
	while ($row = stripslashes_deep($result->fetch_assoc())) {
		$indexes[$i] = $row['Key_name'];
		$i++;
		}
	foreach($indexes as $theindex) {
		$query = "SHOW INDEXES FROM `" . $tablename . "` WHERE `Key_name` LIKE ?";
		$result = db_query($query, [$theindex . '_%']);
		if($result->num_rows <> 0) {
			while ($row = stripslashes_deep($result->fetch_assoc())) {
				$query2 = "DROP INDEX " . $row['Key_name'] . " ON " . $tablename;
				$result2 = db_query($query2);
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