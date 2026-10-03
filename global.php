<?php
if (PHP_SAPI !== 'cli') {        // command line only: this script is never served over HTTP
    http_response_code(404);
    exit();
    }
/*
*/
error_reporting(E_ALL);
$a = 1;
function x () {
    $b = 2;

    function y() {
        global $a;
        global $b;
        echo $a;
        echo $b;
        }    //  end y
    }        // end x



?>
