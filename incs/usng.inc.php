<?php
/*
8/12/08	initial release, a port of the javascript equivalent by Larry Moore
*/
error_reporting(E_ALL);

$ngFunctionsPresent = true;
$UNDEFINED_STR = "undefined";
$UTMEasting = null;
$UTMNorthing = null;
$zoneNumber = null;

$FOURTHPI	= pi() / 4;
$DEG_2_RAD	= pi() / 180;
$RAD_2_DEG	= 180.0 / pi();
$BLOCK_SIZE	= 100000; // size of square identifier (within grid zone designation),
											// (meters)

$IS_NAD83_DATUM = true;	// if false, assumes NAD27 datum

$GRIDSQUARE_SET_COL_SIZE = 8;	// column width of grid square set	
$GRIDSQUARE_SET_ROW_SIZE = 20; // row height of grid square set

$EASTING_OFFSET	= 500000.0;	 // (meters)
$NORTHING_OFFSET = 10000000.0; // (meters)

$k0 = 0.9996;

$EQUATORIAL_RADIUS = null;
$ECCENTRICTY_SQUARED = null;
$ECC_PRIME_SQUARED = null;

if ($IS_NAD83_DATUM) {
	$EQUATORIAL_RADIUS		= 6378137.0; // GRS80 ellipsoid (meters)
	$ECC_SQUARED = 0.006694380023; 
	}
else {
	$EQUATORIAL_RADIUS	= 6378206.4;	// Clarke 1866 ellipsoid (meters)
	$ECC_SQUARED 		= 0.006768658;
	}

$ECC_PRIME_SQUARED = $ECC_SQUARED / (1 - $ECC_SQUARED);

$E1 = (1 - sqrt(1 - $ECC_SQUARED)) / (1 + sqrt(1 - $ECC_SQUARED));
$UTMZone = null;

function getZoneNumber($lat, $lon) {

	$lat = (float)$lat;
	$lon = (float)$lon;

	// sanity check on input
////////////////////////////////	 /*
	if ($lon > 360 || $lon < -180 || $lat > 90 || $lat < -90) {
		print ('Bad input. $lat: ' . $lat . ' $lon: ' . $lon);
		}
////////////////////////////////	*/

	// convert 0-360 to [-180 to 180] range
	$lonTemp = ($lon + 180) - (int)(($lon + 180) / 360) * 360 - 180; 
	$zoneNumber = (int)(($lonTemp + 180) / 6) + 1;

	// Handle special case
	if ( $lat >= 56.0 && $lat < 64.0 && $lonTemp >= 3.0 && $lonTemp < 12.0 ) {
		$zoneNumber = 32;
		}

	// Special zones for Svalbard
	if ( $lat >= 72.0 && $lat < 84.0 ) {
		if ( $lonTemp >= 0.0	&& $lonTemp <	9.0 ) {
			$zoneNumber = 31;
			} 
		else if ( $lonTemp >= 9.0	&& $lonTemp < 21.0 ) {
			$zoneNumber = 33;
			}
		else if ( $lonTemp >= 21.0 && $lonTemp < 33.0 ) {
			$zoneNumber = 35;
			}
		else if ( $lonTemp >= 33.0 && $lonTemp < 42.0 ) {
			$zoneNumber = 37;
			}
		}
	return $zoneNumber;	
	} // END get$zoneNumber() function


function LLtoUTM($lat,$lon, $utmcoords) {
	global $UTMZone, $ECC_SQUARED, $UNDEFINED_STR, $UTMEasting, $UTMNorthing, $zoneNumber, $DEG_2_RAD, $EASTING_OFFSET, $k0, $EQUATORIAL_RADIUS, $ECC_PRIME_SQUARED, $ECC_PRIME_SQUARED;
	// $utmcoords is a 2-D array declared by the calling routine

	$lat = (float)$lat;
	$lon = (float)$lon;

// Constrain reporting USNG coords to the latitude range [80S .. 84N]
/////////////////
	if ($lat > 84.0 || $lat < -80.0){
		return($UNDEFINED_STR);
		}
//////////////////////

	// sanity check on input - turned off when testing with Generic Viewer
/////////////////////	/*
	if ($lon > 360 || $lon < -180 || $lat > 90 || $lat < -90) {
		print('Bad input. $lat: ' . $lat . ' $lon: ' . $lon);
		}
//////////////////////	*/

	// Make sure the longitude is between -180.00 .. 179.99..
	// Convert values on 0-360 range to this range.
	$lonTemp = ($lon + 180) - (int)(($lon + 180) / 360) * 360 - 180;
	$latRad = $lat		 * $DEG_2_RAD;
	$lonRad = $lonTemp * $DEG_2_RAD;

	$zoneNumber = getZoneNumber($lat, $lon);
	$lonOrigin = ($zoneNumber - 1) * 6 - 180 + 3;	// +3 puts origin in middle of zone
	$lonOriginRad = $lonOrigin * $DEG_2_RAD;

	// compute the UTM Zone from the latitude and longitude
	$UTMZone = $zoneNumber . "" . UTMLetterDesignator($lat) . " ";

	$N = $EQUATORIAL_RADIUS / sqrt(1 - $ECC_SQUARED * sin($latRad) * sin($latRad));
	$T = tan($latRad) * tan($latRad);
	$C = $ECC_PRIME_SQUARED * cos($latRad) * cos($latRad);
	$A = cos($latRad) * ($lonRad - $lonOriginRad);

	// Note that the term Mo drops out of the "$M" equation, because phi 
	// (latitude crossing the central meridian, lambda0, at the origin of the
	//	x,y coordinates), is equal to zero for UTM.
	$M = $EQUATORIAL_RADIUS * (( 1 - $ECC_SQUARED / 4				 
				- 3 * ($ECC_SQUARED * $ECC_SQUARED) / 64		 
				- 5 * ($ECC_SQUARED * $ECC_SQUARED * $ECC_SQUARED) / 256) * $latRad 
				- ( 3 * $ECC_SQUARED / 8 + 3 * $ECC_SQUARED * $ECC_SQUARED / 32	
				+ 45 * $ECC_SQUARED * $ECC_SQUARED * $ECC_SQUARED / 1024) 
						* sin(2 * $latRad) + (15 * $ECC_SQUARED * $ECC_SQUARED / 256 
				+ 45 * $ECC_SQUARED * $ECC_SQUARED * $ECC_SQUARED / 1024) * sin(4 * $latRad) 
				- (35 * $ECC_SQUARED * $ECC_SQUARED * $ECC_SQUARED / 3072) * sin(6 * $latRad));

	
	$UTMEasting = ($k0 * $N * ($A + (1 - $T + $C) * ($A * $A * $A) / 6
								+ (5 - 18 * $T + $T * $T + 72 * $C - 58 * $ECC_PRIME_SQUARED )
								* ($A * $A * $A * $A * $A) / 120)
								+ $EASTING_OFFSET);

	$UTMNorthing = ($k0 * ($M + $N * tan($latRad) * ( ($A * $A) / 2 + (5 - $T + 9 
									* $C + 4 * $C * $C ) * ($A * $A * $A * $A) / 24
									+ (61 - 58 * $T + $T * $T + 600 * $C - 330 * $ECC_PRIME_SQUARED )
									* ($A * $A * $A * $A * $A * $A) / 720)));

	$utmcoords[0] = $UTMEasting;
	$utmcoords[1] = $UTMNorthing;
	return $utmcoords;
	}

function LLtoUSNG($lat, $lon, $precision=5) {				// note default precision
	if((empty($lat)) || (empty($lon))) {return "";}
	global $NORTHING_OFFSET, $UTMNorthing, $zoneNumber, $UTMEasting, $UTMZone;
	global $BLOCK_SIZE;

	$lat = (float)$lat;
	$lon = (float)$lon;

	// convert $lat/$lon to UTM coordinates
	$coords = array(2);
	$coords = LLtoUTM($lat, $lon, $coords);
	// ...then convert UTM to $USNG

	if ($lat < 0) {
		// Use offset for southern hemisphere
		$UTMNorthing += $NORTHING_OFFSET; 
		}
	$USNGLetters	= findGridLetters($zoneNumber, $UTMNorthing, $UTMEasting);
	$USNGNorthing = round($UTMNorthing) % $BLOCK_SIZE;
	$USNGEasting	= round($UTMEasting)	% $BLOCK_SIZE;

	// added... truncate digits to achieve specified $precision
	$USNGNorthing = floor($USNGNorthing / pow(10,(5-$precision)));
	$USNGEasting = floor($USNGEasting / pow(10,(5-$precision)));
	$USNG = $UTMZone . $USNGLetters . " ";

	// REVISIT: Modify to incorporate dynamic $precision ?
	for ($i = strlen($USNGEasting); $i < $precision; $i++) {
		$USNG .= "0";
		}

	$USNG .= $USNGEasting . " ";

	for ( $i = strlen($USNGNorthing); $i < $precision; $i++) {
		$USNG .= "0";
		}

	$USNG .= $USNGNorthing;

	return ($USNG);

	}	 // END LLtoUSNG() function


/************** retrieve grid zone designator letter **********************

		This routine determines the correct UTM letter designator for the given 
		latitude returns 'Z' if latitude is outside the UTM limits of 84N to 80S

		Returns letter designator for a given latitude. 
		Letters range from C (-80 lat) to X (+84 lat), with each zone spanning
		8 degrees of latitude.

***************************************************************************/

function UTMLetterDesignator($lat) {
	$lat = (float)$lat;

	if ((84 >= $lat) && ($lat >= 72)) 
		$letterDesignator = 'X';
	else if ((72 > $lat) && ($lat >= 64)) 
		$letterDesignator = 'W';
	else if ((64 > $lat) && ($lat >= 56)) 
		$letterDesignator = 'V';
	else if ((56 > $lat) && ($lat >= 48)) 
		$letterDesignator = 'U';
	else if ((48 > $lat) && ($lat >= 40)) 
		$letterDesignator = 'T';
	else if ((40 > $lat) && ($lat >= 32)) 
		$letterDesignator = 'S';
	else if ((32 > $lat) && ($lat >= 24)) 
		$letterDesignator = 'R';
	else if ((24 > $lat) && ($lat >= 16)) 
		$letterDesignator = 'Q';
	else if ((16 > $lat) && ($lat >= 8)) 
		$letterDesignator = 'P';
	else if (( 8 > $lat) && ($lat >= 0)) 
		$letterDesignator = 'N';
	else if (( 0 > $lat) && ($lat >= -8))
		$letterDesignator = 'M';
	else if ((-8> $lat) && ($lat >= -16)) 
		$letterDesignator = 'L';
	else if ((-16 > $lat) && ($lat >= -24)) 
		$letterDesignator = 'K';
	else if ((-24 > $lat) && ($lat >= -32)) 
		$letterDesignator = 'J';
	else if ((-32 > $lat) && ($lat >= -40)) 
		$letterDesignator = 'H';
	else if ((-40 > $lat) && ($lat >= -48)) 
		$letterDesignator = 'G';
	else if ((-48 > $lat) && ($lat >= -56)) 
		$letterDesignator = 'F';
	else if ((-56 > $lat) && ($lat >= -64)) 
		$letterDesignator = 'E';
	else if ((-64 > $lat) && ($lat >= -72)) 
		$letterDesignator = 'D';
	else if ((-72 > $lat) && ($lat >= -80)) 
		$letterDesignator = 'C';
	else 
		$letterDesignator = 'Z'; // This is here as an error flag to show 
														// that the latitude is outside the UTM limits
	return $letterDesignator;
	}
// END UTMLetterDesignator() function


/****************** Find the set for a given zone. ************************

		There are six unique sets, corresponding to individual grid numbers in 
		sets 1-6, 7-12, 13-18, etc. Set 1 is the same as sets 7, 13, ..; Set 2 
		is the same as sets 8, 14, ..

		See p. 10 of the "United States National Grid" white paper.

***************************************************************************/

function findSet ($zoneNum) {

	$zoneNum = (int) $zoneNum;
	$zoneNum = $zoneNum % 6; 
	switch ($zoneNum) {

		case 0: 
			return 6; 
			break;

		case 1: 
			return 1; 
			break;

		case 2: 
			return 2; 
			break;

		case 3: 
			return 3; 
			break;

		case 4: 
			return 4; 
			break;

		case 5: 
			return 5; 
			break;

		default: 
			return -1; 
			break;
		}
	}		
// END findSet() function


/**************************************************************************	
	Retrieve the square identification for a given coordinate pair & zone	
	See "lettersHelper" function documentation for more details.

***************************************************************************/

function findGridLetters($zoneNum, $northing, $easting) {
	global $BLOCK_SIZE,$GRIDSQUARE_SET_ROW_SIZE, $GRIDSQUARE_SET_COL_SIZE ;

	$zoneNum	= (int)$zoneNum;
	$northing = (float)$northing;
	$easting = (float)$easting;
	$row = 1;

	// $northing coordinate to single-meter precision
	$north_1m = round($northing);

	// Get the $row position for the square identifier that contains the point
	while ($north_1m >= $BLOCK_SIZE) {
		$north_1m = $north_1m - $BLOCK_SIZE;
		$row++;
		}

	// cycle repeats (wraps) after 20 rows
	$row = $row % $GRIDSQUARE_SET_ROW_SIZE;
	$col = 0;

	// $easting coordinate to single-meter precision
	$east_1m = round($easting);

	// Get the column position for the square identifier that contains the point
	while ($east_1m >= $BLOCK_SIZE){
		$east_1m = $east_1m - $BLOCK_SIZE;
		$col++;
		}

	// cycle repeats (wraps) after 8 columns
	$col = $col % $GRIDSQUARE_SET_COL_SIZE;

	return lettersHelper(findSet($zoneNum), $row, $col);
	}

// END findGridLetters() function 





/**************************************************************************	
		Retrieve the Square Identification (two-character letter code), for the
		given row, column and set identifier (set refers to the zone set: 
		zones 1-6 have a unique set of square identifiers; these identifiers are 
		repeated for zones 7-12, etc.) 

		See p. 10 of the "United States National Grid" white paper for a diagram
		of the zone sets.

***************************************************************************/

function lettersHelper($set, $row, $col) {
	global $GRIDSQUARE_SET_COL_SIZE, $GRIDSQUARE_SET_ROW_SIZE;

	// handle case of last $row
	if ($row == 0) {
		$row = $GRIDSQUARE_SET_ROW_SIZE - 1;
		} 
	else {
		$row--;
		}

	// handle case of last column
	if ($col == 0) {
		$col = $GRIDSQUARE_SET_COL_SIZE - 1;
		}
	else {
		$col--;		 
		}

	switch($set) {

		case 1:
			$el1="ABCDEFGH";							// column ids
			$el2="ABCDEFGHJKLMNPQRSTUV";	// $row ids
			return 	charat($el1, $col) . charat($el2, $row);

			break;

		case 2:
			$el1="JKLMNPQR";
			$el2="FGHJKLMNPQRSTUVABCDE";
			return 	charat($el1, $col) . charat($el2, $row);

			break;

		case 3:
			$el1="STUVWXYZ";
			$el2="ABCDEFGHJKLMNPQRSTUV";
			return 	charat($el1, $col) . charat($el2, $row);

			break;

		case 4:
			$el1="ABCDEFGH";
			$el2="FGHJKLMNPQRSTUVABCDE";
			return 	charat($el1, $col) . charat($el2, $row);

			break;

		case 5:
			$el1="JKLMNPQR";
			$el2="ABCDEFGHJKLMNPQRSTUV";
			return 	charat($el1, $col) . charat($el2, $row);

			break;

		case 6:
			$el1="STUVWXYZ";
			$el2="FGHJKLMNPQRSTUVABCDE";
			return 	charat($el1, $col) . charat($el2, $row);

			break;
		}
	}
// END lettersHelper() function

	function charat($str, $pos) {
		return substr ($str, $pos, 1 );
		}

// ex:  print LLtoUSNG(-44.12345, 78.25000, 5);

?>