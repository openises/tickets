<?php
$file = $_GET['file'];
	include ( './lib/PdfToText.phpclass' ) ;

	function  output ( $message )
	   {
		if  ( php_sapi_name ( )  ==  'cli' )
			echo ( $message ) ;
		else
			echo ( nl2br ( $message ) ) ;
	    }

	$pdf	=  new PdfToText ( "$file.pdf" ) ;
	output ( $pdf -> Text ) ;