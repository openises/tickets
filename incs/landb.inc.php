	function do_landb() {				//JS function - 7/3/11
		var points = new Array();
<?php
		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}lines` WHERE `line_status` = 0";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
//
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
			extract ($row);
//			snap(__LINE__, $line_name);

			switch ($line_type) {
				case "p":		// poly
					$points = explode (";", $line_data);		
					for ($i = 0; $i<count($points); $i++) {
						$coords = explode (",", $points[$i]);
?>
						var thepoint = new GLatLng(<?php print $coords[0];?>, <?php print $coords[1];?>);
						bounds.extend(thepoint);
						points.push(thepoint);
<?php					}			// end for ($i = 0 ... )
			 	if ((intval($filled) == 1) && (count($points) > 2)) {?>
						var polyline = new GPolygon(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>, "<?php print $fill_color;?>", <?php print $fill_opacity;?>);
<?php			} else {?>
				        var polyline = new GPolyline(points, "<?php print $line_color;?>", <?php print $line_width;?>, <?php print $line_opacity;?>);
<?php			} ?>				        
						map.addOverlay(polyline);
<?php				
					break;
			
				case "c":		// circle
					$temp = explode (";", $line_data);
					$radius = $temp[1];
					$coords = explode (",", $temp[0]);
					$lat = $coords[0];
					$lng = $coords[1];
					echo "\n drawCircle({$lat}, {$lng}, {$radius}, '{$line_color}', {$line_opacity}, {$line_width}, '{$fill_color}', {$fill_opacity});\n";

//					drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity);		
					break;
			
				case "t":		// banner
					$temp = explode (";", $line_data);
					$banner = $temp[1];
					$coords = explode (",", $temp[0]);
					echo "\n var point = new GLatLng(parseFloat({$coords[0]}) , parseFloat({$coords[1]}));\n";
					$the_banner = htmlentities($banner, ENT_QUOTES);
					$the_width = intval( trim($line_width), 10);		// font size
					echo "\n drawBanner( point, '{$the_banner}', '{$the_banner}', {$the_width});\n";
					break;
			
				}	// end switch
				
		}			// end while ()
		
		unset($query, $result);
?>
		}		// end function do_landb()
