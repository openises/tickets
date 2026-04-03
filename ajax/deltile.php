<?php
$failed = "failed";
if(empty($_GET)) {
    exit();
    }
require_once('../incs/functions.inc.php');

do_login(basename(__FILE__));
error_reporting(E_ALL);
set_time_limit(0);
if(empty($_GET)) {
    exit;
    }

function directory_empty($path) {
    if(($files = @scandir($path)) && (count($files) > 2)) {
        return false;
        } else {
        return true;
        }
    }

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

$filestore = substr(getcwd(), 0, -5) . "/_osm/tiles/";
$zoom = sanitize_string($_GET['zoom']);
$col = sanitize_string($_GET['col']);
$tile = sanitize_string($_GET['tile']);

// Path traversal prevention: reject any path component containing '..' or directory separators
if (strpos($zoom, '..') !== false || strpos($col, '..') !== false || strpos($tile, '..') !== false ||
    strpos($zoom, '/') !== false || strpos($col, '/') !== false || strpos($tile, '/') !== false ||
    strpos($zoom, '\\') !== false || strpos($col, '\\') !== false || strpos($tile, '\\') !== false) {
    print json_encode(array("Failed", "Invalid path", "continue", "", ""));
    exit();
}

$thecolDir =  $filestore . $zoom . "/" . $col;
$thezoomDir =  $filestore . $zoom;
$theFile = $zoom . "/" . $col . "/" . $tile;
$file = $filestore . $theFile;

// Verify resolved path is within the tile store
$realFilestore = realpath($filestore);
$realFile = realpath(dirname($file));
if ($realFilestore === false || $realFile === false || strpos($realFile, $realFilestore) !== 0) {
    print json_encode(array("Failed", "Invalid path", "continue", "", ""));
    exit();
}

$ret_arr = array();
$addition = "";

if(file_exists($file) && unlink($file)) {
    $ret_arr[0] = "Completed";
    } else {
    $ret_arr[0] = "Failed";
    }

if(is_dir($thecolDir) && directory_empty($thecolDir)) {
    rmdir($thecolDir);
    $addition .= "Directory /_osm/tiles/" . e($col) . " deleted";
    }

if(is_dir($thezoomDir) && directory_empty($thezoomDir)) {
    rmdir($thezoomDir);
    $addition .= "Directory /_osm/tiles/" . e($zoom) . " deleted";
    }

$ret_arr[1] = e($theFile) . " deleted";

if(directory_empty($filestore)) {
    $ret_arr[2] = "alldone";
    // All tiles removed — clear cached bounds.  3/14/26
    recalculate_tile_bounds($filestore);
    } else {
    $ret_arr[2] = "continue";
    }
$ret_arr[4] = ($addition != "") ? $addition : "";
print json_encode($ret_arr);
exit();
