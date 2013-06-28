<?php
	function do_landb_server($query_arg) {

?>
	function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity) {		// 8/19/09, 2/26/2013
	
//		drawCircle(53.479874, -2.246704, 10.0, "#000080", 1, 0.75, "#0000FF", .5);

		var circle = new google.maps.Circle({
				center: new google.maps.LatLng(lat,lng),
				map: map,
				fillColor: fillColor,
				fillOpacity: fillOpacity,
				strokeColor: strokeColor,
				strokeOpacity: strokeOpacity,
				strokeWeight: strokeWidth
			});
		circle.setRadius(radius*5000); 

		}
		
	function drawBanner(point, html, text, font_size, color) {        // Create the banner
	//	alert("<?php echo __LINE__;?> " + color);
//		var invisibleIcon = new GIcon(G_DEFAULT_ICON, "./markers/markerTransparent.png");      // Custom icon is identical to the default icon, except invisible
		var invisibleIcon = new google.maps.MarkerImage("./markers/markerTransparent.png");
		map.setCenter(point, 8);
		var the_color = (typeof color == 'undefined')? "#000000" : color ;	// default to black

		var style_str = 'background-color:transparent;font-weight:bold;border:0px black solid;white-space:nowrap; font-size:' + font_size + 'px; font-family:arial; opacity: 0.9; color:' + add_hash(the_color) + ';';

		var contents = '<div><div style= "' + style_str + '">'+text+'<\/div><\/div>';
		var label=new ELabel(point, contents, null, new GSize(-8,4), 75, 1);
		google.maps.addOverlay(label);							// 658
		
		var marker = new GMarker(point,invisibleIcon);	        // Create an invisible GMarker
	//	map.addOverlay(marker);														// 661
		
		}				// end function draw Banner()		


	function do_landb_V3() {				// JS function - 8/1/11	

		var points = new Array();	// <?php echo basename(__FILE__) . __LINE__;?>
		
<?php
//		$query = "SELECT * FROM `{$GLOBALS['mysql_prefix']}mmarkup` WHERE `line_status` = 0 AND (`use_with_f` = 1 OR `use_with_r` = 1)";
		$query = $query_arg;
		$query .= " LIMIT 1";
		$result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
//		snap(basename(__FILE__), __LINE__);
		while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
//			snap(basename(__FILE__), __LINE__);
//			snap( __LINE__, $row['line_type']);
			$empty = FALSE;
			extract ($row);
			$name = $row['line_name'];
			switch ($row['line_type']) {
				case "p":		// poly
					$points = explode (";", $line_data);
					for ($i = 0; $i<count($points); $i++) {		//
						$coords = explode (",", $points[$i]);
?>
						var thepoint = new google.maps.LatLng(<?php echo round ( $coords[0], 6);?>, <?php echo round ( $coords[1], 6);?>); bounds.extend(thepoint); points.push(thepoint);		
<?php					}			// end for ($i = 0 ... )

			 	if ((intval($filled) == 1) && (count($points) > 2)) {?>
						var polyline = new google.maps(points,add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>,add_hash("<?php print $fill_color;?>"), <?php print $fill_opacity;?>);	// 698
// 701						
<?php			} else {?>

//				        var polyline = new google.maps.Polyline(points, add_hash("<?php print $line_color;?>"), <?php print $line_width;?>, <?php print $line_opacity;?>,0 ,0);		// 701
// 704
//						alert (typeof (map_obj));
//						alert (typeof (map));
//						var polyline = new google.maps.Polyline({
//							path:points
//							});

						var polygon = new google.maps.Polygon({		// Create the polygon
							paths: points,
							map: map_obj,
							strokeColor: '<?php print $line_color;?>',
							strokeOpacity: <?php print $line_opacity;?>,
							strokeWeight: <?php print $line_width;?>,
							fillColor: '#0000ff',
							fillOpacity: <?php print $fill_opacity;?>
							});
//						alert("add poly @ 715");
<?php			} 

					break;
			
				case "c":		// circle
					$temp = explode (";", $line_data);
					$radius = $temp[1];
					$coords = explode (",", $temp[0]);
					$lat = $coords[0];
					$lng = $coords[1];
					$fill_opacity = (intval($filled) == 0)?  0 : $fill_opacity;
					
					echo "\n drawCircle({$lat}, {$lng}, {$radius}, add_hash('{$line_color}'), {$line_width}, {$line_opacity}, add_hash('{$fill_color}'), {$fill_opacity}); // 513\n";
					break;
			
				case "t":		// text banner

					$temp = explode (";", $line_data);
					$banner = $temp[1];
					$coords = explode (",", $temp[0]);
					echo "\n var point = new google.maps.LatLng(parseFloat({$coords[0]}) , parseFloat({$coords[1]}));\n";
					$the_banner = htmlentities($banner, ENT_QUOTES);
					$the_width = intval( trim($line_width), 10);		// font size
					echo "\n drawBanner( point, '{$the_banner}', '{$the_banner}', {$the_width});\n";
					break;
			
				}	// end switch
				
		}			// end while ()
		
		unset($query, $result);
?>
		}		// end function do_landb()
		
		unset($query, $result);
		echo "\t}\t// end function do_landb()\n";

?>
