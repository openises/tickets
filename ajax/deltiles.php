<?php
// $failed = "failed";
// if(empty($_GET)) {
    // print $failed;
    // exit();
    // }
require_once '../incs/functions.inc.php';
error_reporting(E_ALL);
set_time_limit(0);
$local = substr(getcwd(), 0, -5) . "/_osm/tiles/";

function rmdir_recurse($path) {
    $path = rtrim($path, '/').'/';
    $handle = opendir($path);
    while(false !== ($file = readdir($handle))) {
        if($file != '.' and $file != '..' ) {
            $fullpath = $path.$file;
            if(is_dir($fullpath)) {
                rmdir_recurse($fullpath);
                } else {
                unlink($fullpath);
                }
        }
    }
    closedir($handle);
    rmdir($path);
    return true;
    }

$deltiles = isset($_GET['deltiles']) ? sanitize_string($_GET['deltiles']) : "";
if($deltiles == "yes") {
    rmdir_recurse($local);
    mkdir($local);
    // Clear cached tile bounds since all tiles are removed.  3/14/26
    recalculate_tile_bounds($local);
    $completed = "Completed";
    print json_encode($completed);
    }
exit();
?>