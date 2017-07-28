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
		return FALSE;
		} else {
		return TRUE;
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
	return TRUE;	
	} 

$filestore = substr(getcwd(), 0, -5) . "/_osm/tiles/";
$zoom = $_GET['zoom'];
$col = $_GET['col'];
$tile = $_GET['tile'];
$thecolDir =  $filestore . $zoom . "/" . $col;
$thezoomDir =  $filestore . $zoom;
$theFile = $zoom . "/" . $col . "/" . $tile;
$file = $filestore . $theFile;

$ret_arr = array();
$addition = "";

if(@unlink($file)) {
	$ret_arr[0] = "Completed";
	} else {
	$ret_arr[0] = "Failed";
	}

if(directory_empty($thecolDir)) {
	@rmdir($thecolDir);
	$addition .= "Directory /_osm/tiles/" . $zoom . "/" . $col . " deleted";
	}
	
if(directory_empty($thezoomDir)) {
	@rmdir($thezoomDir);
	$addition .= "Directory /_osm/tiles/" . $zoom . " deleted";
	}
	
$ret_arr[1] = $theFile . " deleted";
	
if(directory_empty($filestore)) {
	$ret_arr[2] = "alldone";
	} else {
	$ret_arr[2] = "continue";
	}
$ret_arr[4] = ($addition != "") ? $addition : "";
print json_encode($ret_arr);
exit();
?>