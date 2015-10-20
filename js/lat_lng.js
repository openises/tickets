//	tickets-specific coordinates conversion functions
/*
9/16/08 initial release
*/

	function IncludeJavaScript(jsFile) {
		document.write('<script type="text/javascript" src="' + jsFile + '"></scr' + 'ipt>'); 
		}

	IncludeJavaScript('./js/geotools2.js');	
	
	function do_coords(inlat, inlng) { 										 //9/14/08
		if((inlat.length==0)||(inlng.length==0)) {return;}
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);		
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(Math.abs(inval));
		d  = Math.floor(d);
		var mi = (Math.abs(inval)-d)*60;	// fraction * 60
		var m = Math.floor(mi)				// min's as fraction
		var si = (mi-m)*60;					// to sec's
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	function lat2ddm(inlat) {				//  lat to degr, dec.min's - 9/9/089/7/08
		var x = new Number(Math.abs(inlat));
		var degs  = Math.floor(x);				// degrees
		var mins = ((Math.abs(x-degs)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return degs + '\260'  + mins +"'" + nors;
		}
	
	function lng2ddm(inlng) {				//  lng to degr, dec.min's - 9/9/089/7/08
		var x = new Number(Math.abs(inlng));
		var degs  = Math.floor(x);				// degrees
		var mins = ((Math.abs(x-degs)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return degs + '\260' + mins +"'" + eorw;
		}

	function do_lat_fmt(inlat) {				// 9/9/08
		switch(lat_lng_frmt) {
			case 0:
				return inlat;
			  	break;
			case 1:
				return ll2dms(inlat);
			  	break;
			case 2:
				return lat2ddm(inlat);
			 	break;
			default:
				alert (220);
			}	
		}

	function do_lng_fmt(inlng) {
		switch(lat_lng_frmt) {
			case 0:
				return inlng;
			  	break;
			case 1:
				return ll2dms(inlng);
			  	break;
			case 2:
				return lng2ddm(inlng);
			 	break;
			default:
				alert (236);
			}	
		}
