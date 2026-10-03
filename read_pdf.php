<?php
if (PHP_SAPI !== 'cli') {        // command line only: this script is never served over HTTP
    http_response_code(404);
    exit();
    }
$file = sanitize_string($_GET['file']);
    include './lib/PdfToText.phpclass';

    function  output ( $message )
       {
        if  ( php_sapi_name ( )  ==  'cli' )
            echo ( $message ) ;
        else
            echo ( nl2br ( $message ) ) ;
        }

    $pdf    =  new PdfToText ( "$file.pdf" ) ;
    output ( $pdf -> Text ) ;