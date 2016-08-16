<?php
require_once('../incs/functions.inc.php');

function write_shutdown() {
	if(is_dir('_ws_server')) {
		if(!is_dir('_ws_server_shutdown')) {mkdir("_ws_server_shutdown", 0777);}
		}
	}

write_shutdown();
exit();
?>
