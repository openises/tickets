<?php
function ntp_time() {
// ntp time servers to contact
// we try them one at a time if the previous failed (failover)
// if all fail then wait till tomorrow
//	$time_servers = array("time.nist.gov",
//	$time_servers = array("nist1.datum.com",
//							"time-a.timefreq.bldrdoc.gov",
//							"utcnist.colorado.edu");
//
	$time_server = "nist1.datum.com";							// I'm in California and the clock will be set to -0800 UTC [8 hours] for PST
	$fp = fsockopen($time_server, 37, $errno, $errstr, 30);		// you will need to change this value for your region (seconds)
	if (!$fp) {
		return FALSE;
		} 
	else {
		$data = NULL;
		while (!feof($fp)) {
			$data .= fgets($fp, 128);
			}
		fclose($fp);

		if (strlen($data) != 4) {								// we have a response...is it valid? (4 char string -> 32 bits)
			echo "NTP Server {$time_server	} returned an invalid response.\n";
			return FALSE;
			}
		else {
			$NTPtime = ord($data{0	})*pow(256, 3) + ord($data{1	})*pow(256, 2) + ord($data{2	})*256 + ord($data{3	});
			$TimeFrom1990 = $NTPtime - 2840140800;			// convert the seconds to the present date & time
			$TimeNow = $TimeFrom1990 + 631152000;			// 2840140800 = Thu, 1 Jan 2060 00:00:00 UTC
			return 	$TimeNow;
			}
		}
	}		// end function ntp_time() 
	
print "NIST  date/time is " . date ("m/d/Y H:i:s",ntp_time());
echo "\n<BR />System date and time is " . date("m/d/Y H:i:s");
?>
