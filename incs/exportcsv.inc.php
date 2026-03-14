<?php
 
function exportMysqlToCsv($table,$filename, $start, $end, $del){
    $csv_terminated = "\n";
    $csv_separator = ",";
    $csv_enclosed = '"';
    $csv_escaped = "\\";
    $sql_query = "select * from `{$GLOBALS['mysql_prefix']}$table` WHERE `date` BETWEEN ? AND ?";

    // Gets the data from the database
    $result = db_query($sql_query, [$start, $end]);
    $fields_cnt = $result->field_count;
 
 
    $schema_insert = '';
 
    for ($i = 0; $i < $fields_cnt; $i++) {
        $field_info = $result->fetch_field_direct($i);
        $l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,
            stripslashes($field_info->name)) . $csv_enclosed;
        $schema_insert .= $l;
        $schema_insert .= $csv_separator;
		} // end for
 
    $out = trim(substr($schema_insert, 0, -1));
    $out .= $csv_terminated;
	$the_ids = array();
    // Format the data
    while ($row = $result->fetch_array()){
		$the_ids[] = $row['id'];
        $schema_insert = '';
        for ($j = 0; $j < $fields_cnt; $j++)
        {
            if ($row[$j] == '0' || $row[$j] != '')
            {
 
                if ($csv_enclosed == '')
                {
                    $schema_insert .= $row[$j];
                } else
                {
                    $schema_insert .= $csv_enclosed . 
					str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $row[$j]) . $csv_enclosed;
                }
            } else
            {
                $schema_insert .= '';
            }
 
            if ($j < $fields_cnt - 1)
            {
                $schema_insert .= $csv_separator;
            }
        } // end for
 
        $out .= $schema_insert;
        $out .= $csv_terminated;
    } // end while
	$the_now = time();	
	
	if(file_exists($filename)) {
		return "999";
		} else {
		$thefile = fopen("{$filename}", "w");
		fwrite($thefile, $out);
		fclose($thefile);
		if($del) {
			foreach($the_ids AS $val) {
				$query = "DELETE FROM `{$GLOBALS['mysql_prefix']}$table` WHERE `id` = ?";
				$result = db_query($query, [intval($val)]);
				}
			}
		return "100";	
		}
	}
 
?> 
