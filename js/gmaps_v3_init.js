/*
1/11/2013 - initial release
*/
//	gmaps_v3_init(call_back, map_obj, 'map_canvas', ...

	function gmaps_v3_init(callback, in_canvas, in_lat, in_lng, in_zoom, in_icon, in_maptype, rd_only) {
		var in_map;
		function do_point(LatLng_in){								// latLng object
			window.lat_var = LatLng_in.lat().toFixed(6); 			// set globals
			window.lng_var = LatLng_in.lng().toFixed(6);
			myMarker.setMap(null);						// erase prior
			myMarker = new google.maps.Marker({
				position: LatLng_in,
				icon: myIcon, 
				draggable: allow_drag,
				map: in_map
				});
			myMarker.setMap(in_map);		// add marker with icon
			
			in_map.setCenter(LatLng_in );	// now center it
			
			if(!rd_only) {
				google.maps.event.addListener(myMarker, "dragend", function(event) {
					var point = myMarker.getPosition();				// returns LatLng
					do_point (point);
					});
				var posnObj={lat:parseFloat(lat_var), lng:parseFloat(lng_var), zoom:parseInt(in_map.getZoom())};		// define position object
				callback(posnObj);										// return it
				}

			}				// end function do point()

		window.lat_var = in_lat;		// set globals
		window.lng_var = in_lng;
		window.zoom_var = in_zoom;

		var allow_drag = !(rd_only);
	    myIcon = new google.maps.MarkerImage(in_icon);
//	    myIcon.anchor= new google.maps.Point(in_icon.width/2, in_icon.height/2);		// 8/11/12 - center offset = half icon width and height
	    myIcon.anchor= new google.maps.Point(0, 16);		// 8/11/12 - center offset = half icon width ?
//		alert(document.images[in_icon].width)
//	    alert(in_icon.offsetWidth );
		var iconImg = new Image();														// obtain icon dimensions
		iconImg.src = in_icon;
	    myIcon.anchor= new google.maps.Point(iconImg.width/2, iconImg.height/2);		// 8/11/12 - center offset = half icon width and height
		switch(in_maptype) {
			case (2): the_type= google.maps.MapTypeId.SATELLITE; 	break;
			case (3): the_type= google.maps.MapTypeId.TERRAIN; 		break;
			case (4): the_type= google.maps.MapTypeId.HYBRID; 		break;
			default:  the_type= google.maps.MapTypeId.ROADMAP;
			}		// end switch

		var myOptions = { 
			mapTypeId:				the_type,
			zoom: 					in_zoom, 
			overviewMapControl: 	true,
			panControl: 			true,
			zoomControl: 			true,
			scaleControl: 			true,	
			center: 				new google.maps.LatLng(in_lat, in_lng)
			};

		in_map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);		// instantiate
		
		var myLatlng = new google.maps.LatLng(in_lat, in_lng);
	    myMarker = new google.maps.Marker({position: myLatlng, map: in_map, icon: myIcon, draggable:allow_drag, title:"Map center"});
		myMarker.setMap(in_map);
		
		var infoWindow = new google.maps.InfoWindow;

		if(!rd_only) {	
			google.maps.event.addListener(in_map, 'click', function(point) {
				var myLatLng = point.latLng;
				window.lat_var = myLatLng.lat(); 		// set globals
				window.lng_var = myLatLng.lng();
				var latlng = new google.maps.LatLng(myLatLng.lat(), myLatLng.lng());
				myMarker.setMap(null);															// erase prior
	
				myMarker = new google.maps.Marker({
					position: new google.maps.LatLng(window.lat_var, window.lng_var),
					icon: myIcon, 
					map: in_map
					});
				myMarker.setMap(in_map);		// add icon
				
				in_map.setCenter(latlng );		// now center it
				myMarker.setMap(in_map);		// add icon
	
				if (point) 	{ do_point (myLatLng); }
				else 		{ alert("88: err err err");	}
				infoWindow.close();
				});

			google.maps.event.addListener(in_map, 'zoom_changed', function() {
				zoom_var = in_map.getZoom();		// set global
				var posnObj={lat:parseFloat(window.lat_var), lng:parseFloat(window.lng_var), zoom:parseInt(in_map.getZoom())};		// define position object
				callback(posnObj);										// return it
				});		// end zoom listener

			google.maps.event.addListener(myMarker, "dragend", function(event) {
				var point = myMarker.getPosition();				// retuns LatLng
				do_point (point);
				});
			}	// end if(!rd_only)

		google.maps.event.addDomListener(window, 'unload', google.maps.Unload);
		return in_map;		// the object
		}		// end function gmaps_v3_init 
