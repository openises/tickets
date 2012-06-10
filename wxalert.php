<?php
// 	DEC 17 2010
//	"M d Y"

function dump($variable) {
	echo "\n//<PRE>";				// pretty it a bit
	var_dump($variable) ;
	echo "//</PRE>\n";
	}



//Original script by Tom Chaplin, http://www.carterlake.org/weatherphp.php
//Modified by Kevin Bednar to add write-to-file and write-to-db functionality
//to allow for use 
// Force Defaults for Zone and County
if ( ! isset($_REQUEST['warnzone']) )
        $_REQUEST['warnzone']="NJZ001";
if ( ! isset($_REQUEST['warncounty']) )
        $_REQUEST['warncounty']="NJC037";
if ( ! isset($_REQUEST['warnlocal']) )
        $_REQUEST['warnlocal']="";

//You can pass data to this script with:
//http://www.k2kmb.org/testadvisory.php?warnzone=NJZ001&warncounty=NJC037&warnlocal=Newton
//Where the zone is your zone and the county is your county and location is your location
//using pluses in place of spaces

$warnzone = $_REQUEST['warnzone'];
$warncounty = $_REQUEST['warncounty'];
$warnlocal = $_REQUEST['warnlocal'];
//$fp = fopen("m:/temp/alert.txt", "w"); //Open file to write alert text to. We always overwrite.
$fp = fopen("./alert.txt", "w"); //Open file to write alert text to. We always overwrite.

//import NOAA Advisory info
//data can be altered by changing the zone and county numbers
//Target data ends up in $targetwarn and $targettext[0]

$html = strip_tags ( implode('', file("http://www.crh.noaa.gov/showsigwx.php?warnzone=${warnzone}&warncounty=${warncounty}")));
dump($html);

//Get the advisory headers and put them in an array

		preg_match_all('|<h3>(.*)</h3>|', $html, $headers);
		$warnheaders = $headers[1];

//Get the advisory text and put them into an array as well

		preg_match_all('|<pre>(.*)</pre>|Uis', $html, $headers);
		$warntext = $headers[1];

//If there is more than one advisory, we need to set its priority

if (count($warnheaders) >= 1) {

$i = 0;
$flag = 0;
$targetwarn ="";
//First, around here tornados are the biggest danger. A warning is critical information.
//Display this one first no matter what!

	while ($i < count($warnheaders)):
		if (preg_match("/Tornado Warning/i", $warnheaders[$i])) {  
			$targetwarn = $warnheaders[$i];
			$targettext = $warntext[$i];
			$targettext = explode("$$",$targettext);
			$flag = 1;
			break;
		}
		$i++;
   	endwhile;

//Next if there are none of the above found. Display the first warning message.

	if ($flag == 0) {
		$i = 0;
		while ($i < count($warnheaders)):
			if (preg_match("/Warning/i", $warnheaders[$i])) {  
			$targetwarn = $warnheaders[$i];
			$targettext = $warntext[$i];
			$targettext = explode("$$",$targettext);
				$flag = 1;
				break;
			}
			$i++;
		endwhile;
	}

//Next if there are none of the above found. Display the first watch message.

	if ($flag == 0) {
		$i = 0;
		while ($i < count($warnheaders)):
			if (preg_match("/Watch/i", $warnheaders[$i])) {  
			$targetwarn = $warnheaders[$i];
			$targettext = $warntext[$i];
			$targettext = explode("$$",$targettext);
				$flag = 1;
				break;
			}
			$i++;
		endwhile;
	}

//Next if there are none of the above found. Display the first advisory message.

	if ($flag == 0) {
		$i = 0;
		while ($i < count($warnheaders)):
			if (preg_match("/Advisory/i", $warnheaders[$i])) {  
			$targetwarn = $warnheaders[$i];
			$targettext = $warntext[$i];
			$targettext = explode("$$",$targettext);
				$flag = 1;
				break;
			}
			$i++;
		endwhile;
	}

//Next if there are none of the above found. Display the first statement message.

	if ($flag == 0) {
		$i = 0;
		while ($i < count($warnheaders)):
			if (preg_match("/Statement/i", $warnheaders[$i])) {  
			$targetwarn = $warnheaders[$i];
			$targettext = $warntext[$i];
			$targettext = explode("$$",$targettext);
				$flag = 1;
				break;
			}
			$i++;
		endwhile;
	}

//Next if there are none of the above found. Set the advisory to default message.

	if ($targetwarn == "Hazardous Weather Outlook") {
		$targetwarn = "NO CURRENT ADVISORIES";
		$targettext[0] = "THERE ARE NO ACTIVE WATCHES, WARNINGS OR ADVISORIES";
	} else if ($targetwarn == "No Active Hazardous Weather Conditions Found") {
		$targetwarn = "NO CURRENT ADVISORIES";
		$targettext[0] = "THERE ARE NO ACTIVE WATCHES, WARNINGS OR ADVISORIES";
	} else if (empty($targetwarn)) {
		$targetwarn = "NO CURRENT ADVISORIES";
		$targettext[0] = "THERE ARE NO ACTIVE WATCHES, WARNINGS OR ADVISORIES";
	} else if ($targetwarn == "Short Term Forecast") {
		$targetwarn = "NO CURRENT ADVISORIES";
		$targettext[0] = "THERE ARE NO ACTIVE WATCHES, WARNINGS OR ADVISORIES";
	}

	if ($targetwarn <> "NO CURRENT ADVISORIES") {

	$warnlist = 'ALL CURRENT ADVISORIES:';

		for ($i = 0; $i <= count($warnheaders); $i++) {
	    		$warnheaderplus = preg_replace( '| |', '+', $warnheaders[$i] );
	    		$warnlist = $warnlist . '<br><a href="http://www.crh.noaa.gov/showsigwx.php?warnzone=' . $warnzone. '&warncounty=' . $warncounty . '&local_place1=' . $warnlocal . '&product1=' . $warnheaderplus . '" target="_new">' . $warnheaders[$i] . '</a>';
		}
	}
    
}

echo "<b>";
echo $targetwarn;
echo "</b><br><br><pre>";
echo $targettext[0];
echo $warnlist;
echo "</pre>";
fputs($fp, $targettext[0]); //Write alert text out to file
?>
