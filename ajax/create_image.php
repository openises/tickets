<?php 
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
require_once('../incs/functions.inc.php');
$string = $_GET['string'];

function isFreetype() {
	$temp = gd_info();
	if($temp['FreeType Support']) {
		return true;
		} else {
		return false;
		}
	}
	
//Send a generated image to the browser 
create_image($string); 
exit(); 



function create_image($string) { 
    //Set the session to store the security code
	$im = imagecreatetruecolor(175, 40);

	// Create some colors
	$white = imagecolorallocate($im, 255, 255, 255);
	$grey = imagecolorallocate($im, 128, 128, 128);
	$black = imagecolorallocate($im, 0, 0, 0);
	imagefilledrectangle($im, 0, 0, 459, 39, $white);
	
	if(isFreetype()) {
		// Replace path by your own font path
		$font = '../fonts/AriBlk.ttf';
		
		// Add some shadow to the text
		imagettftext($im, 21, 0, 18, 30, $grey, $font, $string);

		// Add the text
		imagettftext($im, 21, 0, 16, 28, $black, $font, $string);
		} else {
		// Add some shadow to the text
		imagestring ($im, 5, 58, 9, $string, $grey);	

		// Add the text		
		imagestring ($im, 5, 60, 10, $string, $black);
		}
		
	header('Content-type: image/png');
	// Using imagepng() results in clearer text compared with imagejpeg()
	imagepng($im);
	imagedestroy($im);
	} 
?>