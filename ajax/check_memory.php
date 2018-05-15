<?php
require_once('../incs/functions.inc.php');

@session_start();
session_write_close();
/* if($_GET['q'] != $_SESSION['id']) {
	exit();
	} */

function getServerMemoryUsage($getPercentage=true) {
	$memoryTotal = null;
	$memoryFree = null;

	if (stristr(PHP_OS, "win")) {
		// Get total physical memory (this is in bytes)
		$cmd = "wmic ComputerSystem get TotalPhysicalMemory";
		@exec($cmd, $outputTotalPhysicalMemory);

		// Get free physical memory (this is in kibibytes!)
		$cmd = "wmic OS get FreePhysicalMemory";
		@exec($cmd, $outputFreePhysicalMemory);

		if ($outputTotalPhysicalMemory && $outputFreePhysicalMemory) {
			// Find total value
			foreach ($outputTotalPhysicalMemory as $line) {
				if ($line && preg_match("/^[0-9]+\$/", $line)) {
					$memoryTotal = $line;
					break;
				}
			}

			// Find free value
			foreach ($outputFreePhysicalMemory as $line) {
				if ($line && preg_match("/^[0-9]+\$/", $line)) {
					$memoryFree = $line;
					$memoryFree *= 1024;  // convert from kibibytes to bytes
					break;
					}
				}
			}
		} else {
		if (is_readable("/proc/meminfo")) {
			$stats = @file_get_contents("/proc/meminfo");

			if ($stats !== false) {
				// Separate lines
				$stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
				$stats = explode("\n", $stats);

				// Separate values and find correct lines for total and free mem
				foreach ($stats as $statLine) {
					$statLineData = explode(":", trim($statLine));

					//
					// Extract size (TODO: It seems that (at least) the two values for total and free memory have the unit "kB" always. Is this correct?
					//

					// Total memory
					if (count($statLineData) == 2 && trim($statLineData[0]) == "MemTotal") {
						$memoryTotal = trim($statLineData[1]);
						$memoryTotal = explode(" ", $memoryTotal);
						$memoryTotal = $memoryTotal[0];
						$memoryTotal *= 1024;  // convert from kibibytes to bytes
						}

					// Free memory
					if (count($statLineData) == 2 && trim($statLineData[0]) == "MemFree") {
						$memoryFree = trim($statLineData[1]);
						$memoryFree = explode(" ", $memoryFree);
						$memoryFree = $memoryFree[0];
						$memoryFree *= 1024;  // convert from kibibytes to bytes
						}
					}
				}
			}
		}

	if (is_null($memoryTotal) || is_null($memoryFree)) {
		return null;
		} else {
		if ($getPercentage) {
			return (100 - ($memoryFree * 100 / $memoryTotal));
			} else {
			return array(
				"total" => $memoryTotal,
				"free" => $memoryFree,
			);
			}
		}
	}

function getNiceFileSize($bytes, $binaryPrefix=true) {
	if ($binaryPrefix) {
		$unit=array('B','KiB','MiB','GiB','TiB','PiB');
		if ($bytes==0) return '0 ' . $unit[0];
		return @round($bytes/pow(1024,($i=floor(log($bytes,1024)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
		} else {
		$unit=array('B','KB','MB','GB','TB','PB');
		if ($bytes==0) return '0 ' . $unit[0];
		return @round($bytes/pow(1000,($i=floor(log($bytes,1000)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
		}
	}

	
function _getServerLoadLinuxData() {
	if (is_readable("/proc/stat")) {
		$stats = @file_get_contents("/proc/stat");
		if ($stats !== false) {
			// Remove double spaces to make it easier to extract values with explode()
			$stats = preg_replace("/[[:blank:]]+/", " ", $stats);
			// Separate lines
			$stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
			$stats = explode("\n", $stats);
			// Separate values and find line for main CPU load
			foreach ($stats as $statLine) {
				$statLineData = explode(" ", trim($statLine));
				// Found!
				if ( (count($statLineData) >= 5) && ($statLineData[0] == "cpu")) {
					return array($statLineData[1],$statLineData[2],$statLineData[3],$statLineData[4]);
					}
				}
			}
		}
	return null;
	}

// Returns server load in percent (just number, without percent sign)
function getServerLoad() {
	$load = null;
	if (stristr(PHP_OS, "win")) {
		$cmd = "wmic cpu get loadpercentage /all";
		@exec($cmd, $output);
		if ($output) {
			foreach ($output as $line) {
				if ($line && preg_match("/^[0-9]+\$/", $line)) {
					$load = $line;
					break;
					}
				}
			}
		} else {
		if (is_readable("/proc/stat")) {
			// Collect 2 samples - each with 1 second period
			// See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
			$statData1 = _getServerLoadLinuxData();
			sleep(1);
			$statData2 = _getServerLoadLinuxData();
			if((!is_null($statData1)) && (!is_null($statData2))) {
				// Get difference
				$statData2[0] -= $statData1[0];
				$statData2[1] -= $statData1[1];
				$statData2[2] -= $statData1[2];
				$statData2[3] -= $statData1[3];

				// Sum up the 4 values for User, Nice, System and Idle and calculate
				// the percentage of idle time (which is part of the 4 values!)
				$cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];

				// Invert percentage to get CPU time, not idle time
				$load = 100 - ($statData2[3] * 100 / $cpuTime);
				}
			}
		}
	return $load;
	}

    //----------------------------

$cpuLoad = getServerLoad();
	
// Memory usage: 4.55 GiB / 23.91 GiB (19.013557664178%)
$memUsage = getServerMemoryUsage(false);
$mem_usage = memory_get_peak_usage(true);
$memActual = memory_get_usage(true);

$ret_arr = array();
$ret_arr[0] = "T: " . getNiceFileSize($memUsage["total"]);
$ret_arr[1] = "F: " . getNiceFileSize($memUsage["free"]);
$ret_arr[2] = "U: " . getNiceFileSize($memUsage["total"] - $memUsage["free"]);
$ret_arr[3] = "C: " . round($cpuLoad, 2) . "%";
$ret_arr[4] = "A: " . getNiceFileSize($memActual);
$ret_arr[5] = "P: " . getNiceFileSize($mem_usage);


print json_encode($ret_arr);

