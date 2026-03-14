<?php
/*
generates numbered icon given the icon id and value string

1/7/09 initial release
1/9/09 swapped yellow and green
1/24/09 dark for yellow icon
3/14/26 PHP 8.x fixes: suppress libpng sRGB warning, cast float to int, sanitize input
*/

header("Content-type: image/png");
//$img_url = "./icons/gen_icon5.php?blank=7&text=BB";

function do_icon ($icon, $text, $color) {
	$im = @imagecreatefrompng($icon);		// suppress libpng sRGB profile warning
	if ($im === false) { return; }
	imageAlphaBlending($im, true);
	imageSaveAlpha($im, true);

	$len = strlen($text);
	$p1 = ($len <= 2)? 1:2 ;
	$p2 = ($len <= 2)? 3:2 ;
	$px = (int)((imagesx($im) - 7 * $len) / 2 + $p1);		// cast to int for PHP 8.x
	$font = 'arial.ttf';
	$contrast = ($color)? imagecolorallocate($im, 255, 255, 255): imagecolorallocate($im, 0, 0, 0); // white on dark?

	imagestring($im, $p2, $px, 3, $text, $contrast);	// imagestring  ( $image, $font, $x, $y, $string, $color)

	imagepng($im);
	imagedestroy($im);
	}
			// the following array must be kept in synch with $GLOBALS['icons']

// Sanitize icon filename - only allow .png files in this directory (prevent path traversal)
$the_icon = isset($_GET['icon']) ? basename((string)$_GET['icon']) : '';
if (!preg_match('/^[a-zA-Z0-9_-]+\.png$/', $the_icon) || !is_readable(__DIR__ . '/' . $the_icon)) {
	http_response_code(400);
	exit();
}
$light = isset($_GET['light']) ? (bool)intval($_GET['light']) : false;
$the_text = (array_key_exists('text', $_GET) && !is_null($_GET['text'])) ? substr((string)$_GET['text'], 0, 3) : '';
do_icon ($the_icon, $the_text, $light);

?>
