<?php
if (PHP_SAPI !== 'cli') {        // command line only: this script is never served over HTTP
    http_response_code(404);
    exit();
    }
print $_SERVER['DOCUMENT_ROOT'];
$filestore = explode("/", $_SERVER['DOCUMENT_ROOT']);
print end($filestore);
?>