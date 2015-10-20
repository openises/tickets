<?php
/*
9/10/13 - New file, generates position marker for mobile screen, rotates input marker by 0,90,180 or 27 degrees as appropriate
*/

header("Content-type: image/png");

function rotate_image($icon, $degs) {
	$source = imagecreatefrompng($icon); //	create image for manipulation from source icon
    imagealphablending($source, false);
	imagesavealpha($source, true);	//	keeps alpha transparency correct for source
	$im = imagerotate($source, $degs, imageColorAllocateAlpha($source, 0, 0, 0, 127));	//	rotate image keeping transparency
	imagealphablending($im, false);
	imagesavealpha($im, true);	//	keeps alpha transparency correct for rotated image
	imagepng($im);	//	create final PNG
	imagedestroy($im);	// remove from memory
	}
	
$the_icon = $_GET['icon'];
$the_degrees = $_GET['degrees'];
rotate_image($the_icon, $the_degrees);	
?>

