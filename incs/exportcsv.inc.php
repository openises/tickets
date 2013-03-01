<?php
 
function exportMysqlToCsv($table,$filename, $start, $end, $del){
    $csv_terminated = "\n";
    $csv_separator = ",";
    $csv_enclosed = '"';
    $csv_escaped = "\\";
    $sql_query = "select * from `$GLOBALS[mysql_prefix]$table` WHERE `date` BETWEEN '" . $start . "' AND '" . $end . "'";
 
    // Gets the data from the database
    $result = mysql_query($sql_query);
    $fields_cnt = mysql_num_fields($result);
 
 
    $schema_insert = '';
 
    for ($i = 0; $i < $fields_cnt; $i++) {
        $l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,
            stripslashes(mysql_field_name($result, $i))) . $csv_enclosed;
        $schema_insert .= $l;
        $schema_insert .= $csv_separator;
		} // end for
 
    $out = trim(substr($schema_insert, 0, -1));
    $out .= $csv_terminated;
	$the_ids = array();
    // Format the data
    while ($row = mysql_fetch_array($result)){
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
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]$table` WHERE `id` = " . $val;
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				}
			}
		return "100";	
		}
	}
 
?> 
