<?php
if (PHP_SAPI !== 'cli') {        // command line only: this script is never served over HTTP
    http_response_code(404);
    exit();
    }
/*
*/
error_reporting(E_ALL);
print md5("admin");

?>
