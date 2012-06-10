<?php
	// ntp time servers to contact
	// we try them one at a time if the previous failed (failover)
	// if all fail then wait till tomorrow
//	$time_servers = array("time.nist.gov",
	$time_servers = array("nist1.datum.com",
							"time-a.timefreq.bldrdoc.gov",
							"utcnist.colorado.edu");

	$valid_response = false;	// a flag and number of servers
	$ts_count = sizeof($time_servers);      

	for ($i=0; $i<$ts_count; $i++) {								// time adjustment
		$time_server = $time_servers[$i];							// I'm in California and the clock will be set to -0800 UTC [8 hours] for PST
		$fp = fsockopen($time_server, 37, $errno, $errstr, 30);		// you will need to change this value for your region (seconds)
		if (!$fp) {
			echo "$time_server: $errstr ($errno)\n";
			echo "Trying next available server...\n\n";
			} else {
			$data = NULL;
			while (!feof($fp)) {
				$data .= fgets($fp, 128);
				}
			fclose($fp);

			if (strlen($data) != 4) {			// we have a response...is it valid? (4 char string -> 32 bits)
				echo "NTP Server {$time_server	} returned an invalid response.\n";
				if ($i != ($ts_count - 1)) {
					echo "Trying next available server...\n\n";
					} else {
					echo "Time server list exhausted\n";
					}
				} else {
				$valid_response = true;
				break;
				}
			}
		}

	if ($valid_response) {		// time server response is a string - convert to numeric

		$NTPtime = ord($data{0	})*pow(256, 3) + ord($data{1	})*pow(256, 2) + ord($data{2	})*256 + ord($data{3	});

		$TimeFrom1990 = $NTPtime - 2840140800;			// convert the seconds to the present date & time
		$TimeNow = $TimeFrom1990 + 631152000;			// 2840140800 = Thu, 1 Jan 2060 00:00:00 UTC

		$TheDate = date("m/d/Y H:i:s", $TimeNow );		// set the system time

		echo "NTP date and time is $TheDate\n";
		echo "\n<BR />System date and time is " . date("m/d/Y H:i:s") . "<BR />\n";
		} else {
		echo "The system time could not be updated. No time servers available.\n";
		}
?>
