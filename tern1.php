<?php
if (PHP_SAPI !== 'cli') {        // command line only: this script is never served over HTTP
    http_response_code(404);
    exit();
    }
function the_func() {
    return false;
    }
if ($temp = the_func()) {
    print __LINE__;
    print $temp;
    }
else {
    print __LINE__;
    }
?>