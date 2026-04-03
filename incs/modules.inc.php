<?php
/*
9/15/10 New File
*/

/*
This is the main modules include file. It includes the helper file of all modules that are installed and enabled.
*/
//require_once('functions.inc.php');
require_once('mysql.inc.php');


function get_modules($calling_file) {
    global $handle;
    $query         = "SELECT COUNT(*) FROM `{$GLOBALS['mysql_prefix']}modules`";
    $result     = db_query($query);
    $num_rows     = @$result->num_rows;
    if($num_rows) {
        // AND `affecting_files` LIKE '%{$calling_file}%'" - this is a check in the below statement, but has been disabled for now
        $query2 = "SELECT * FROM `{$GLOBALS['mysql_prefix']}modules` WHERE `mod_status`=1";
        $result2 = db_query($query2);
        $numb_rows = @$result2->num_rows;
        while ($row2 = stripslashes_deep($result2->fetch_assoc())){
            $name = $row2['mod_name']; $status=$row2['mod_status'] ;
            // SECURITY: Sanitize module name to prevent directory traversal via LFI.
            // Only allow alphanumeric, underscore, and hyphen characters.
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
                continue; // skip modules with suspicious names
            }
            $inc_path="./modules/" . $name . "/helper.php";
            if (!file_exists($inc_path)) {
                continue; // skip if module helper file doesn't exist
            }
            $display="get_display_" . $name;
            include($inc_path);
            $display($calling_file);
            }
        }
    }

function module_active($module) {
    global $handle;
    $query         = "SELECT * FROM `{$GLOBALS['mysql_prefix']}modules` WHERE `mod_name`= ?";
    $result     = db_query($query, [$module]);
    $num_rows     = @$result->num_rows;
    if($num_rows > 0) {
    while($row = stripslashes_deep($result->fetch_assoc())) {
        $name = $row['mod_name'];
        $status = $row['mod_status'] ;
        return $status;
        }
    } else {
        $status=0;
        return $status;
        }
    }

?>