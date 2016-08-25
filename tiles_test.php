<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META NAME="Generator" CONTENT="TextPad 4.6">
<META NAME="Author" CONTENT="?">
<META NAME="Keywords" CONTENT="?">
<META NAME="Description" CONTENT="?">
</HEAD>

<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LINK="#FF0000" VLINK="#800000" ALINK="#FF00FF" BACKGROUND="?">
<?php

function my_dump($variable) {
	echo "\n<PRE>\n";				// pretty it a bit - 2/23/2013
	var_dump($variable) ;
	echo "</PRE>\n";
	}

function get_tile_bounds ($repository) {

	function tile2long( $x, $z) {
		$n = pow(2, $z);
		return $x / $n * 360.0 - 180.0;
		}

	function tile2lat( $y, $z) {
		$n = pow(2, $z);
		return rad2deg(atan(sinh(pi() * (1 - 2 * $y / $n))));
		}

	function low_high_dir ($path, $low = TRUE) {
		$dh  = opendir($path);
		if ($low) {		// find min
			$return = 99999;					// starter - see below
			while (false !== ($filename = readdir($dh))  ) {
				if ( intval($filename) > 0 && intval ($filename) < intval ($return ) ) {
					$return = $filename ;		// retain extension if file
					}
				}		// end while ()
			}
		else {			//find max
			$return = 0;						// starter - see below
			while (false !== ($filename = readdir($dh))  ) {

				if ( intval($filename) > 0 && intval ($filename) > intval ($return ) ) {
					$return = $filename ;
					}
				}		// end while ()
			}		// end else
		return $return;
		}		// end function

	//	1.  compute zoom
	$dir = $repository;
	$dh  = opendir($dir);
	$zoom = 99;						// starter - see below
	while (false !== ($filename = readdir($dh))  ) {
		if ( is_numeric ($filename ) && intval ($filename) < intval ($zoom ) ) { $zoom = intval ($filename) ; }
		}		// end while ()

	// 2. compute west and east longs

	$west = 99999;		// set extremes
	$east = 0;
	$path = "{$dir}/{$zoom}";
	$dh  = opendir($path);
	while (false !== ($filename = readdir($dh) ) ) {	// walk down the selected zoom directory
		if (is_numeric ($filename) ) {
			if ( intval($filename ) < intval ($west) ) {$west = $filename;}		// min
			if ( intval($filename ) > intval ($east) ) {$east = $filename;}		// max
			}		// end if (is_numeric () )
		}		// end while ()


	// 3. compute northwest tile - OK

	$path = "{$dir}/{$zoom}/{$west}";
	$northwest = low_high_dir ($path, $low = TRUE) ;

	// 4. compute southeast tile

	$path = "{$dir}/{$zoom}/{$east}";
	$southeast = low_high_dir ($path, $low = FALSE) ;

	$west_long = round (tile2long( $west, $zoom), 6) ;
	$north_lat = round (tile2lat( intval($northwest), $zoom), 6);
	$east_long = round (tile2long( $east + 1, $zoom), 6);					// note + 1
	$south_lat = round (tile2lat( intval($southeast) + 1, $zoom), 6);		// note + 1

	return array($west_long, $north_lat, $east_long, $south_lat );
	}		// end function

my_dump (get_tile_bounds ("./_osm/tiles") ) ;
?>


</BODY>
</HTML>
