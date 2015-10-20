<?php
/*
generates numbered icon given the icon id and value string
*/
header("Content-type: image/png");

function do_icon ($icon, $text, $color) {
	$im = imagecreatefrompng($icon);
	imageAlphaBlending($im, true);
	imageSaveAlpha($im, true);
		
	$len = strlen($text);
	$p1 = ($len <= 2)? 1:2 ;
	$p2 = ($len <= 2)? 3:2 ;
	$px = (imagesx($im) - 7 * $len) / 2 + $p1;
	$font = 'arial.ttf';
	$contrast = ($color)? imagecolorallocate($im, 255, 255, 255): imagecolorallocate($im, 0, 0, 0); // white on dark?

	imagestring($im, $p2, $px, 3, $text, $contrast);	// imagestring  ( $image, $font, $x, $y, $string, $color)
//	imagettftext ( $im , 1 , 0 , 0 ,0 , $contrast , $font , $text)

	imagepng($im);
	imagedestroy($im);
	}
				// the following array must be kept in synch with $GLOBALS['icons'] 
				
$icons =   array("red.png", "green.png", "yellow.png", "black.png", "blue.png", "red_circle.png");		// 1/9/09
$light =   array( FALSE, 		FALSE, 		FALSE, 		 TRUE,       TRUE,    FALSE);		// white text?
	
$the_icon = $icons[$_GET['blank']];				// 0 thru 8 (note: total 9)
$the_text = substr($_GET['text'], 0, 3);		// enforce 2-char limit
do_icon ($the_icon, $the_text,$light[$_GET['blank']] );	

?>

