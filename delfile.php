<?php
if (PHP_SAPI !== 'cli') {        // command line only: this script is never served over HTTP
    http_response_code(404);
    exit();
    }
unlink('../files/516906_1379631591.docx');
?>