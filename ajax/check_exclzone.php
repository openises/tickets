<?php
require_once('../incs/functions.inc.php');

@session_start();
session_write_close();
$resp_id = $_GET['resp_id'];
$lat = $_GET['lat'];
$lng = $_GET['lng'];
$ret_arr = array();

function get_exclusion_zone($id) {
	$excl_zone = 0;
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) > 0) {		
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		if(intval($row['excl_zone']) > 0) {
			$excl_zone = intval($row['excl_zone']);
			}
		}
	return $excl_zone;
	}

function get_fencetype($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`= " . $id . " LIMIT 1";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['line_type'];
		} else {
		return false;
		}
	}
	
function get_circlecenter($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`= " . $id . " LIMIT 1";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$temp = explode(":", $row['line_data']);
		$center = explode(",", $temp[0]);
		return $center;
		} else {
		return false;
		}
	}
	
function get_circleradius($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`= " . $id . " LIMIT 1";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$temp = explode(";", $row['line_data']);
		$radius = $temp[1];
		return $radius;
		} else {
		return false;
		}
	}	

function get_points($theZone) {
	$ret_arr =array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `id`= " . $theZone . " LIMIT 1";
	$result = mysql_query($query)or do_error($query, mysql_error(), basename(__FILE__), __LINE__);
	if(mysql_num_rows($result) != 0) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		return $row['line_data'];
		} else {
		return false;
		}
	} 

function distance($lat1, $lon1, $lat2, $lon2, $unit) { //	unit - M = miles, K = Kilometers, N = Nautical Miles
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$miles = $dist * 60 * 1.1515; 
	$unit = strtoupper($unit); 
	if ($unit == "K") { 
		return ($miles * 1.609344); 
		} else if ($unit == "N") { 
		return ($miles * 0.8684); 
		} else { 
		return $miles; 
		} 
	}

function inCircle($lat1, $lng1, $lat2, $lng2, $unit, $id) {
	$dist = distance($lat1, $lng1, $lat2, $lng2, $unit);
	$radius = get_circleradius($id);
	if($dist <= $radius) {
		return 1;
		} else {
		return 0;
		}
	}
/*
Description: The point-in-polygon algorithm allows you to check if a point is
inside a polygon or outside of it.
Author: Michaël Niessen (2009)
Website: http://AssemblySys.com
 
If you find this script useful, you can show your
appreciation by getting Michaël a cup of coffee ;)
PayPal: michael.niessen@assemblysys.com
 
As long as this notice (including author name and details) is included and
UNALTERED, this code is licensed under the GNU General Public License version 3:
http://www.gnu.org/licenses/gpl.html
*/
 
class pointLocation {
    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices?
 
    function pointLocation() {
    }
 
        function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;
 
        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = array(); 
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex); 
        }
 
        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }
 
        // Check if the point is inside the polygon or on the boundary
        $intersections = 0; 
        $vertices_count = count($vertices);
 
        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1]; 
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) { 
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x']; 
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++; 
                }
            } 
        } 
        // If the number of edges we passed through is odd, then it's in the polygon. 
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }
 
    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
 
    }
 
    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }
 
}

$thezone = get_exclusion_zone($resp_id);
$thepoints = get_points($thezone);
$theType = get_fencetype($thezone);

if($theType == "p") {
	if($thepoints != "") {
		$thepointarray = explode(";", $thepoints);
		$polygon = array();
		$lastlat = "";
		$lastlng = "";
		$i=0;
		foreach($thepointarray as $var) {
			$thelatlng = explode(',', $var);
			$theLat = $thelatlng[0];
			$theLng = $thelatlng[1];
			if($i==0) {$firstlat = $theLat; $firstlng = $theLng;}
			$lastlat = $theLat;
			$lastlng = $theLng;
			$polygon[] = $theLat . " " . $theLng;
			$i++;
			}

		if(($lastlat != $firstlat) && ($lastlng != $firstlng)) {
			$polygon[] = $firstlat . " " . $firstlng;
			}

		$pointLocation = new pointLocation();
		$points = array($lat . " " . $lng);

		// The last point's coordinates must be the same as the first one's, to "close the loop"
		foreach($points as $key => $point) {
			if($pointLocation->pointInPolygon($point, $polygon) == "inside") {
				$ret_arr[0] = 1;
				} else {
				$ret_arr[0] = 0;
				}
			}
		} else {
		$ret_arr[0] = 99;
		}
	} elseif($theType == "c") {
	$coords = get_circlecenter($thezone);
	$lat1 = $coords[0];
	$lng1 = $coords[1];
	$ret_arr[0] = inCircle($lat1, $lng1, $lat, $lng, "K", $thezone);
	} else {
	$ret_arr[0] = 99;
	}

print json_encode($ret_arr);

