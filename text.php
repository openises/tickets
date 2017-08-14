<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <style type="text/css">

    .style1 {background-color:transparent;font-weight:bold;border:0px black solid;white-space:nowrap; font-size : 1.5em; font-family:"arial"; opacity: 0.75; font-style:italic}

    </style>


    <title>Google Maps</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAAPDUET0Qt7p2VcSk6JNU1sBSM5jMcmVqUpI7aqV44cW1cEECiThQYkcZUPRJn9vy_TWxWvuLoOfSFBw" type="application/x-javascript"></script>
  </head>
  <body onunload="GUnload()">


    <div id="map" style="width: 550px; height: 450px"></div>
    <a href="elabel.htm">Back to the tutorial page</a>

    <script src="elabel.js" type="application/x-javascript"></script>

    <script type="application/x-javascript">
    //<![CDATA[

    if (GBrowserIsCompatible()) {
// ______________________________________________
// ELabel.js 
//
//   This Javascript is provided by Mike Williams
//   Community Church Javascript Team
//   http://www.bisphamchurch.org.uk/   
//   http://econym.org.uk/gmap/
//
//   This work is licenced under a Creative Commons Licence
//   http://creativecommons.org/licenses/by/2.0/uk/
//
// Version 0.2      the .copy() parameters were wrong
// version 1.0      added .show() .hide() .setContents() .setPoint() .setOpacity() .overlap
// version 1.1      Works with GMarkerManager in v2.67, v2.68, v2.69, v2.70 and v2.71
// version 1.2      Works with GMarkerManager in v2.72, v2.73, v2.74 and v2.75
// version 1.3      add .isHidden()
// version 1.4      permit .hide and .show to be used before addOverlay()
// version 1.5      fix positioning bug while label is hidden
// version 1.6      added .supportsHide()
// version 1.7      fix .supportsHide()
// version 1.8      remove the old GMarkerManager support due to clashes with v2.143


      function ELabel(point, html, classname, pixelOffset, percentOpacity, overlap) {
        // Mandatory parameters
        this.point = point;
        this.html = html;
        
        // Optional parameters
        this.classname = classname||"";
        this.pixelOffset = pixelOffset||new GSize(0,0);
        if (percentOpacity) {
          if(percentOpacity<0){percentOpacity=0;}
          if(percentOpacity>100){percentOpacity=100;}
        }        
        this.percentOpacity = percentOpacity;
        this.overlap=overlap||false;
        this.hidden = false;
      } 
      
      ELabel.prototype = new GOverlay();

      ELabel.prototype.initialize = function(map) {
        var div = document.createElement("div");
        div.style.position = "absolute";
        div.innerHTML = '<div class="' + this.classname + '">' + this.html + '</div>' ;
        map.getPane(G_MAP_FLOAT_SHADOW_PANE).appendChild(div);
        this.map_ = map;
        this.div_ = div;
        if (this.percentOpacity) {        
          if(typeof(div.style.filter)=='string'){div.style.filter='alpha(opacity:'+this.percentOpacity+')';}
          if(typeof(div.style.KHTMLOpacity)=='string'){div.style.KHTMLOpacity=this.percentOpacity/100;}
          if(typeof(div.style.MozOpacity)=='string'){div.style.MozOpacity=this.percentOpacity/100;}
          if(typeof(div.style.opacity)=='string'){div.style.opacity=this.percentOpacity/100;}
        }
        if (this.overlap) {
          var z = GOverlay.getZIndex(this.point.lat());
          this.div_.style.zIndex = z;
        }
        if (this.hidden) {
          this.hide();
        }
      }

      ELabel.prototype.remove = function() {
        this.div_.parentNode.removeChild(this.div_);
      }

      ELabel.prototype.copy = function() {
        return new ELabel(this.point, this.html, this.classname, this.pixelOffset, this.percentOpacity, this.overlap);
      }

      ELabel.prototype.redraw = function(force) {
        var p = this.map_.fromLatLngToDivPixel(this.point);
        var h = parseInt(this.div_.clientHeight);
        this.div_.style.left = (p.x + this.pixelOffset.width) + "px";
        this.div_.style.top = (p.y +this.pixelOffset.height - h) + "px";
      }

      ELabel.prototype.show = function() {
        if (this.div_) {
          this.div_.style.display="";
          this.redraw();
        }
        this.hidden = false;
      }
      
      ELabel.prototype.hide = function() {
        if (this.div_) {
          this.div_.style.display="none";
        }
        this.hidden = true;
      }
      
      ELabel.prototype.isHidden = function() {
        return this.hidden;
      }
      
      ELabel.prototype.supportsHide = function() {
        return true;
      }

      ELabel.prototype.setContents = function(html) {
        this.html = html;
        this.div_.innerHTML = '<div class="' + this.classname + '">' + this.html + '</div>' ;
        this.redraw(true);
      }
      
      ELabel.prototype.setPoint = function(point) {
        this.point = point;
        if (this.overlap) {
          var z = GOverlay.getZIndex(this.point.lat());
          this.div_.style.zIndex = z;
        }
        this.redraw(true);
      }
      
      ELabel.prototype.setOpacity = function(percentOpacity) {
        if (percentOpacity) {
          if(percentOpacity<0){percentOpacity=0;}
          if(percentOpacity>100){percentOpacity=100;}
        }        
        this.percentOpacity = percentOpacity;
        if (this.percentOpacity) {        
          if(typeof(this.div_.style.filter)=='string'){this.div_.style.filter='alpha(opacity:'+this.percentOpacity+')';}
          if(typeof(this.div_.style.KHTMLOpacity)=='string'){this.div_.style.KHTMLOpacity=this.percentOpacity/100;}
          if(typeof(this.div_.style.MozOpacity)=='string'){this.div_.style.MozOpacity=this.percentOpacity/100;}
          if(typeof(this.div_.style.opacity)=='string'){this.div_.style.opacity=this.percentOpacity/100;}
        }
      }

      ELabel.prototype.getPoint = function() {
        return this.point;
      }
// ______________________________________________

      var map = new GMap2(document.getElementById("map"));
      map.setCenter(new GLatLng(43.907787,-79.359741),8);
      map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());

      // remember which label was associated with the open info window
      var lastlabel; 

      // Custom icon is identical to the default icon, except that its invisible
      var invisibleIcon = new GIcon(G_DEFAULT_ICON, "http://www.google.com/intl/en_ALL/mapfiles/markerTransparent.png");

      function createMarkeredLabel(point,html,text) {
        // Create the Label
        var contents = '<div>  <div class="style1">'+text+'<\/div><img src="http://maps.google.com/intl/en_ALL/mapfiles/markerie.gif" width=20 height=34><\/div>';
        var label=new ELabel(point, contents, null, new GSize(-8,4), 75, 1);
        map.addOverlay(label);

        // Create an invisible GMarker
        var marker = new GMarker(point,invisibleIcon);
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
          label.setOpacity(100);
          lastlabel = label;
        });
        map.addOverlay(marker);
      }

      // Reset the opacity of the label when the info window closes
      GEvent.addListener(map,"infowindowclose", function() {
        if (lastlabel) {
          lastlabel.setOpacity(75);
        }
      });


/*    
      var point = new GLatLng(43.64855,-79.38535);
      var marker = createMarkeredLabel(point,'Some stuff to display in the Toronto Info Window','Toronto')
      
      var point = new GLatLng(43.15635, -79.24866);
      var marker = createMarkeredLabel(point,'Some stuff to display in the St Catharine\'s Info Window','St Catharine\'s')

      var point = new GLatLng(43.21208, -79.68987);
      var marker = createMarkeredLabel(point,'Some stuff to display in the Stoney Creek Info Window.<br>A two-line label','Stoney<br>Creek')

      var point = new GLatLng(43.32616, -79.79855);
      var marker = createMarkeredLabel(point,'Some stuff to display in the Burlington Info Window','Burlington')

      var point = new GLatLng(43.68432, -79.75875);
      var marker = createMarkeredLabel(point,'Some stuff to display in the Brampton Info Window','Brampton')

      var point = new GLatLng(43.5883, -79.64373);
      var marker = createMarkeredLabel(point,'Some stuff to display in the Mississauga Info Window','Mississauga')
*/
      var point = new GLatLng(37.46619, -79.68957);
      var marker = createMarkeredLabel(point,'Some Italic partially opaque text to place on a map','Some  partially opaque text')


      
    }
    
    // display a warning if the browser was not compatible
    else {
      alert("Sorry, the Google Maps API is not compatible with this browser");
    }

    // This Javascript is based on code provided by the
    // Community Church Javascript Team
    // http://www.bisphamchurch.org.uk/   
    // http://econym.org.uk/gmap/

    //]]>
    </script>
  </body>

</html>




