<?php
require_once '../incs/functions.inc.php';
require_login_or_401();

function write_shutdown() {
    if(is_dir('_ws_server')) {
        if(!is_dir('_ws_server_shutdown')) {mkdir("_ws_server_shutdown", 0777);}
        }
    }

write_shutdown();
exit();
?>
