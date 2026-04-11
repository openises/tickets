<?php
$failed = "failed";

if(empty($_GET)) {
    print $failed;
    exit();
    }
require_once '../incs/functions.inc.php';
require_once '../incs/versions.inc.php';
$completed = array();
$dir = $_GET['dir'];
$subdir = $_GET['subdir'];
$file = $_GET['file'];

do_login(basename(__FILE__));
error_reporting(E_ALL);
set_time_limit(0);
$got_curl = function_exists("curl_init");

// Use configurable tile server URL instead of hardcoded OSM
$tile_server_tpl = get_variable('tile_server_url');
if ($tile_server_tpl === false || trim($tile_server_tpl) === '') {
    $tile_server_tpl = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
}
$tile_user_agent = get_tile_user_agent();
$directory_separator = DIRECTORY_SEPARATOR;
$ajax_dir = dirname( realpath( __FILE__ ) ) . DIRECTORY_SEPARATOR;
$tickets_root = preg_replace( '~[/\\\\][^/\\\\]*[/\\\\]$~' , DIRECTORY_SEPARATOR , $ajax_dir );
$local = $tickets_root . "_osm" . DIRECTORY_SEPARATOR . "tiles";
$url = "";

function chmod_r($Path) {
    global $directory_separator;
    $dp = opendir($Path);
    while($File = readdir($dp)) {
        if($File != "." AND $File != "..") {
            if(is_dir($File)){
                chmod($File, 0750);
                chmod_r($Path.$directory_separator.$File);
                } else {
                chmod($Path.$directory_separator.$File, 0644);
                }
            }
        }
    closedir($dp);
    }

function do_file ($dir, $subdir, $file) {
    global $got_curl, $local, $url, $completed, $tile_server_tpl, $tile_user_agent;
    if (!(file_exists($local))) {
        mkdir($local) OR die(__LINE__);
        }
    $my_addr = "{$local}/{$dir}/{$subdir}/{$file}.png";
    if (!(file_exists($my_addr))) {                            // check for pre-existence
        sleep(1);                                            // don't hammer OSM
        $dirname = (string) "{$local}/{$dir}";
        if (!(file_exists($dirname))) {                        // zoom directory
            mkdir($dirname) OR die(__LINE__);
            }
        $dirname = (string) "{$local}/{$dir}/{$subdir}";
        if (!(file_exists($dirname))) {
            mkdir($dirname) OR die(__LINE__);
            }

        // Build URL from configurable tile server template
        $subdomains = array('a', 'b', 'c');
        $s = $subdomains[array_rand($subdomains)];
        $url = str_replace(array('{z}', '{x}', '{y}', '{s}'), array($dir, $subdir, $file, $s), $tile_server_tpl);
        $theFileName = "_osm/tiles/{$dir}/{$subdir}/{$file}.png";
        if ($got_curl) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, $tile_user_agent);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            $the_tile = curl_exec ($ch);
            $completed[1] = "{$theFileName} downloaded";
            curl_close ($ch);
            }
        else {                // not CURL - use stream context with proper User-Agent
            $ctx = stream_context_create(array('http' => array('user_agent' => $tile_user_agent)));
            $the_tile = file_get_contents($url, false, $ctx);
            }

        if ($fp = fopen($my_addr, 'wb')) {
            fwrite ($fp, $the_tile);
            $completed[1] = "{$theFileName} downloaded";
            fclose ($fp);
            }
        else {
//            print "error " . __LINE__ . "<br />";        // @fopen fails
            }
        } else {
            $theFileName = "_osm/tiles/{$dir}/{$subdir}/{$file}.png";
            $completed[1] = "{$theFileName} existed already";
        }

    }        // end function do_file ()

do_file($dir, $subdir, $file);
if($_GET['lastfile'] == "yes") {
    chmod_r($local);
    // Recalculate and cache tile bounds in the database so get_tile_bounds()
    // doesn't need to scan the filesystem on every page load.  3/14/26
    recalculate_tile_bounds($local);
    }
$completed[1] = ($completed[1]) ? $completed[1] : "";
$completed[0] = "Completed";
$completed[2] = $_GET['lastfile'];
print json_encode($completed);
exit();
?>