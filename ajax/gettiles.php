<?php
$failed = "failed";

if(empty($_GET)) {
	print $failed;
	exit();
	}
require_once('../incs/functions.inc.php');
$completed = array();
$dir = $_GET['dir'];
$subdir = $_GET['subdir'];
$file = $_GET['file'];

do_login(basename(__FILE__));
error_reporting(E_ALL);	
set_time_limit(0);
$got_curl = function_exists("curl_init");
$base = "http://tile.openstreetmap.org";
$local = "../_osm/tiles";
$url = "";

function change_osm_permissions() {
	global $local;
	$dp = opendir($local);
	while($file = readdir($dp)) {
		if (($file == ".") || ($file == ".."))
			continue;
		$fullPath = $local."/".$file;
		if(is_dir($fullPath)) {
			chmod($fullPath, 0777);
			chmod_r($fullPath, 0777, 0777);
			} else {
			chmod($fullPath, 0777);
			}
		}
	closedir($dp);
	}

function do_file ($dir, $subdir, $file) {
	global $got_curl, $base, $local, $url, $completed;
	if (!(file_exists($local))) {
		mkdir($local) OR die(__LINE__);
		}	
	$my_addr = "{$local}/{$dir}/{$subdir}/{$file}.png";
	if (!(file_exists($my_addr))) {							// check for pre-existence
		sleep(1);											// don't hammer OSM
		$dirname = (string) "{$local}/{$dir}";
		if (!(file_exists($dirname))) {						// zoom directory
			mkdir($dirname) OR die(__LINE__);
			}
		$dirname = (string) "{$local}/{$dir}/{$subdir}";
		if (!(file_exists($dirname))) {		
			mkdir($dirname) OR die(__LINE__);
			}
	
		$url = "{$base}/{$dir}/{$subdir}/{$file}.png";
		$theFileName = "_osm/tiles/{$dir}/{$subdir}/{$file}.png";
		if ($got_curl) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$the_tile = curl_exec ($ch);
			$completed[1] = "{$theFileName} downloaded";
			curl_close ($ch);
			}
		else {				// not CURL
			$the_tile = file_get_contents($url);
			}
	
		if ($fp = fopen($my_addr, 'wb')) {
			fwrite ($fp, $the_tile);
			$completed[1] = "{$theFileName} downloaded";
			fclose ($fp);
			}		
		else {
//			print "error " . __LINE__ . "<br />";		// @fopen fails
			}
		} else {
			$theFileName = "_osm/tiles/{$dir}/{$subdir}/{$file}.png";
			$completed[1] = "{$theFileName} existed already";
		}

	}		// end function do_file ()

do_file($dir, $subdir, $file);
if($_GET['lastfile'] == "yes") {
	change_osm_permissions();
	}
$completed[0] = "Completed";
$completed[2] = $_GET['lastfile'];
print json_encode($completed);
exit();
?>