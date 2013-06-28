// ELabels3.js
//
//   2010-06-10  Port to GoogleMaps V3 API by Pat Horton
//
//   This Javascript was originally provided by Mike Williams
//   Blackpool Community Church Javascript Team
//   http://www.commchurch.freeserve.co.uk/
//   http://econym.googlepages.com/index.htm
//
//   This work is licenced under a Creative Commons Licence
//   http://creativecommons.org/licenses/by/2.0/uk/
//	 usage label = new ELabel(map, map.getCenter(), '<canvas id="arrowCanvas" width="32" height="32"><\/canvas>',null,new 

function ELabel(map, point, html, classname, pixelOffset, percentOpacity, overlap)
{
        this.div_ = null;
        this.map_ = map;
        this.point = point;
        this.html = html;
        this.classname = classname || "";
        this.pixelOffset = pixelOffset || new google.maps.Size(0,0);
        if (percentOpacity)
        {
                if (percentOpacity<0) percentOpacity=0;
                if (percentOpacity>100) percentOpacity=100;
        }
        this.percentOpacity = percentOpacity;
        this.overlap=overlap || false;
        this.hidden = false;
}

ELabel.prototype = new google.maps.OverlayView();

ELabel.prototype.onAdd = function()
{
        var div = document.createElement("div");
        div.style.position = "absolute";
        div.innerHTML = '<div class="' + this.classname + '">' + this.html +'</div>' ;
        this.div_ = div;
        if (this.percentOpacity)
        {
                if(typeof(div.style.filter)=='string')
					{div.style.filter='alpha(opacity:'+this.percentOpacity+')';}
                if(typeof(div.style.KHTMLOpacity)=='string')
					{div.style.KHTMLOpacity=this.percentOpacity/100;}
                if(typeof(div.style.MozOpacity)=='string')
					{div.style.MozOpacity=this.percentOpacity/100;}
                if(typeof(div.style.opacity)=='string')
					{div.style.opacity=this.percentOpacity/100;}
        }
        if (this.overlap)
        {
                // you may need to work on this "hack" to replace V2 getZindex
                // GOverlay.getZIndex(this.point.lat());
                var z = 1000*(90-this.point.lat());
                this.div_.style.zIndex = parseInt(z);
        }
        if (this.hidden)
        {
                this.hide();
        }

        // add ourselves to the shadow overlay layer

        var panes = this.getPanes();
        panes.floatShadow.appendChild(div);
}


ELabel.prototype.onRemove = function()
{
        this.div_.parentNode.removeChild(this.div_);
}

ELabel.prototype.draw = function(force)
{
        var proj = this.getProjection();
        var p = proj.fromLatLngToDivPixel(this.point);
        var h = parseInt(this.div_.clientHeight);
        this.div_.style.left = (p.x + this.pixelOffset.width) + "px";
        this.div_.style.top = (p.y +this.pixelOffset.height - h) + "px";
}

ELabel.prototype.show = function()
{
        if (this.div_)
        {
                this.div_.style.display="";
                this.redraw();
        }
        this.hidden = false;
}

ELabel.prototype.hide = function()
{
        if (this.div_)
        {
                this.div_.style.display="none";
        }
        this.hidden = true;
}



ELabel.prototype.copy = function()
{
        return new ELabel(this.point, this.html, this.classname, this.pixelOffset, this.percentOpacity, this.overlap);
}

ELabel.prototype.isHidden = function()
{
        return this.hidden;
}

ELabel.prototype.supportsHide = function()
{
        return true;
}

ELabel.prototype.setContents = function(html)
{
        this.html = html;
        this.div_.innerHTML = '<div class="' + this.classname + '">' + this.html + '</div>' ;
        this.redraw(true);
}

ELabel.prototype.setPoint = function(point)
{
        this.point = point;
        if (this.overlap)
        {
                var z = GOverlay.getZIndex(this.point.lat());
                this.div_.style.zIndex = z;
        }
        this.redraw(true);
}

ELabel.prototype.setOpacity = function(percentOpacity)
{
        if (percentOpacity)
        {
                if(percentOpacity<0){percentOpacity=0;}
                if(percentOpacity>100){percentOpacity=100;}
        }
        this.percentOpacity = percentOpacity;
        if (this.percentOpacity)
        {
                if(typeof(this.div_.style.filter)=='string')
					{this.div_.style.filter='alpha(opacity:'+this.percentOpacity+')';}
                if(typeof(this.div_.style.KHTMLOpacity)=='string')
					{this.div_.style.KHTMLOpacity=this.percentOpacity/100;}
                if(typeof(this.div_.style.MozOpacity)=='string')
					{this.div_.style.MozOpacity=this.percentOpacity/100;}
                if(typeof(this.div_.style.opacity)=='string')
					{this.div_.style.opacity=this.percentOpacity/100;}
        }
}

ELabel.prototype.getPoint = function()
{
        return this.point;
}

