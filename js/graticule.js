// useage:  map.addOverlay(new LatLonGraticule());
// This shows a lat/lon graticule on the map. Interval is automatic
// Bill Chadwick,

function LatLonGraticule() {
}
LatLonGraticule.prototype = new GOverlay();


LatLonGraticule.prototype.initialize = function(map) {

  //save for later
  this.map_ = map;
  //array for divs used for lines and labels
  this.divs_ = new Array();

	}

LatLonGraticule.prototype.remove = function() {

  try{
  var i = 0;
  var div = this.map_.getPane(G_MAP_MARKER_SHADOW_PANE);
  for(i=0; i< this.divs_.length; i++)
	div.removeChild(this.divs_[i]);
	}
  catch(e){
	  }

}

LatLonGraticule.prototype.copy = function() {
	return new LatLonGraticule();
	}

LatLonGraticule.prototype.redraw = function(force) {			// Redraw the graticule based on the current projection and zoom level
  this.remove();  										//clear old

  this.color_ = this.map_.getCurrentMapType().getTextColor(); 	 //best color for writing on the map

  var bnds = this.map_.getBounds(); 						 //determine graticule interval

  var l = bnds.getSouthWest().lng();
  var b = bnds.getSouthWest().lat();
  var t = bnds.getNorthEast().lat();
  var r = bnds.getNorthEast().lng();

  if (b < -90.0)  //sanity
	b = -90.0;
  if(t > 90.0)
	t = 90.0;
  if(l < -180.0)
    l = -180.0;
  if(r > 180.0)
    r = 180.0;

  if(l == r){
	l = -180.0;
	r = 180.0;
  	}

  if(t == b){
	b = -90.0;
	t = 90.0;
	}


  var dLat = this.gridIntervalMins(t-b);  //grid interval in minutes
  var dLng;
  if(r>l)
	dLng = this.gridIntervalMins(r-l);
  else
    dLng = this.gridIntervalMins((180-l)+(r+180));

  l = Math.floor(l*60/dLng)*dLng/60;  //round iteration limits to the computed grid interval
  b = Math.floor(b*60/dLat)*dLat/60;
  t = Math.ceil(t*60/dLat)*dLat/60;
  r = Math.ceil(r*60/dLng)*dLng/60;


  if (b <= -90.0)  //Sanity
	b = -90;
  if(t >= 90.0)
	t = 90;
  if(l < -180.0)
    l = -180.0;
  if(r > 180.0)
    r = 180.0;


  dLat /= 60;  //to whole degrees
  dLng /= 60;

  var latDecs = this.gridPrecision(dLat);  //# digits after DP for labels
  var lonDecs = this.gridPrecision(dLng);

  this.divs_ = new Array();
  var i=0;//count inserted divs

  var pbl = this.map_.fromLatLngToDivPixel(new GLatLng(b,l));  //min and max x and y pixel values for graticule lines
  var ptr = this.map_.fromLatLngToDivPixel(new GLatLng(t,r));

  this.maxX = ptr.x;
  this.maxY = pbl.y;
  this.minX = pbl.x;
  this.minY = ptr.y;

  var x;															//coord for label
  																//labels on second column to avoid peripheral controls
  var y = this.map_.fromLatLngToDivPixel(new GLatLng(b+dLat+dLat,l)).y + 2;	//coord for label


  var mapDiv = this.map_.getPane(G_MAP_MARKER_SHADOW_PANE);  //pane/layer to write on

  var lo = l;//copy to save original

  if(r<lo)
      r += 360.0;


  while(lo<=r){  //vertical lines

		 var p = this.map_.fromLatLngToDivPixel(new GLatLng(b,lo));

		 this.divs_[i] = this.createVLine(p.x);		 //line
		 mapDiv.insertBefore(this.divs_[i],null);
		 i++;
		 var d = document.createElement("DIV"); //label
		 x = p.x + 3;
		 d.style.position = "absolute";
         	 d.style.left = x.toString() + "px";
        	 d.style.top = y.toString() + "px";
		 d.style.color = this.color_;
		 d.style.fontFamily='Arial';
		 d.style.fontSize='x-small';
         	 d.innerHTML = (Math.abs(lo)).toFixed(lonDecs);
		 mapDiv.insertBefore(d,null);
		 this.divs_[i] = d;
		 i++;
		 lo += dLng;
		 if (lo > 180.0){
			r -= 360.0;
			lo -= 360.0;
			}
  	}

  var j = 0;


  x = this.map_.fromLatLngToDivPixel(new GLatLng(b,l+dLng+dLng)).x + 3;  //labels on second row to avoid controls
  while(b<=t){  												//horizontal lines
		 var p = this.map_.fromLatLngToDivPixel(new GLatLng(b,l));

		 if(r < l){ //draw lines across the dateline		 //line
			this.divs_[i] = this.createHLine3(b);
			mapDiv.insertBefore(this.divs_[i],null);
			i++;
		 	}
		 else if (r == l){ //draw lines for world scale zooms
			this.divs_[i] = this.createHLine3(b);
			mapDiv.insertBefore(this.divs_[i],null);
			i++;
		 	}
		 else{
			this.divs_[i] = this.createHLine(p.y);
			mapDiv.insertBefore(this.divs_[i],null);
			i++;
		 	}
		
		 var d = document.createElement("DIV"); //label
		 y = p.y + 2;

		 d.style.position = "absolute";
		 d.style.left = x.toString() + "px";
		 d.style.top = y.toString() + "px";
		 d.style.color = this.color_;
		 d.style.fontFamily='Arial';
		 d.style.fontSize='x-small';
		 d.innerHTML = (Math.abs(b)).toFixed(latDecs);
		 if(j != 2)//dont put two labels in the same place
		 {
			 mapDiv.insertBefore(d,null);
			 this.divs_[i] = d;
			 i++;
		 	}
		 j++;
		 b += dLat;
	  	}
	}

LatLonGraticule.prototype.gridIntervalMins = function(dDeg) {	// calculate rounded graticule interval for supplied lat/lon span
	var dDeg = dDeg/10;								// return is in minutes
	dDeg *= 6000;//to minutes*100						// want around 10 lines in the graticule
	dDeg = Math.ceil(dDeg)/100;//minutes and hundredths of mins
	
	if(dDeg <= 0.06)
		dDeg = 0.06;//0.001 degrees
	else if(dDeg <= 0.12)
		dDeg = 0.12;//0.002 degrees
	else if(dDeg <= 0.3)
		dDeg = 0.3;//0.005 degrees
	else if(dDeg <= 0.6)
		dDeg = 0.6;//0.01 degrees
	else if (dDeg <=  1.2)
		dDeg = 1.2;//0.02 degrees
	else if(dDeg <= 3)
		dDeg = 3;//0.05 degrees
	else if(dDeg <= 6)
		dDeg = 6;//0.1 degrees
	else if (dDeg <=  12)
		dDeg = 12;//0.2 degrees
	else if (dDeg <=  30)
		dDeg = 30;//0.5
	else if (dDeg <=  60)
		dDeg = 60;//1
	else if (dDeg <=  (60*2))
		dDeg = 60*2;
	else if (dDeg <=  (60*5))
		dDeg = 60*5;
	else if (dDeg <=  (60*10))
		dDeg = 60*10;
	else if (dDeg <=  (60*20))
		dDeg = 60*20;
	else if (dDeg <=  (60*30))
		dDeg = 60*30;
	else
		dDeg = 60*45;
	
	
	return dDeg;
	
	}


LatLonGraticule.prototype.gridPrecision = function(dDeg) {		//calculate grid label precision from grid interval in degrees
	if(dDeg < 0.01)
		return 3;
	else if(dDeg < 0.1)
		return 2;
	else if(dDeg < 1)
		return 1;
	else return 0;
	}



LatLonGraticule.prototype.createVLine = function(x) {			//returns a div that is a vertical single pixel line
	var div = document.createElement("DIV");
	div.style.position = "absolute";
	div.style.overflow = "hidden";
	div.style.backgroundColor = this.color_;
	div.style.left = x + "px";
	div.style.top = this.minY + "px";
	div.style.width = "1px";
	div.style.height = (this.maxY-this.minY) + "px";

	return div;
	}

LatLonGraticule.prototype.createHLine = function(y) {			//returns a div that is a horizontal single pixel line
	var div = document.createElement("DIV");
	div.style.position = "absolute";
	div.style.overflow = "hidden";
	div.style.backgroundColor = this.color_;
	div.style.left = this.minX + "px";
	div.style.top = y + "px";
	div.style.width = (this.maxX-this.minX) + "px";
	div.style.height = "1px";

	return div;
	}




LatLonGraticule.prototype.createHLine3 = function(lat) {			// returns a div that is a horizontal single pixel line, across the dateline
														// we find the start and width of a 180 degree line and draw the same amount
	var f = this.map_.fromLatLngToDivPixel(new GLatLng(lat,0));		// to its left and right
	var t = this.map_.fromLatLngToDivPixel(new GLatLng(lat,180));

	var div = document.createElement("DIV");
	div.style.position = "absolute";
	div.style.overflow = "hidden";
	div.style.backgroundColor = this.color_;

	var x1 = f.x;
	var x2 = t.x;
	if(x2 < x1) {	
		x2 = f.x;
		x1 = t.x;
		}
	div.style.left = (x1-(x2-x1)) + "px";
	div.style.top = f.y + "px";
	div.style.width = ((x2-x1)*3) + "px";
	div.style.height = "1px";

	return div;
	}
