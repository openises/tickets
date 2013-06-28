/*
do_sel_update - functions_major.inc.php
function to_server - functions_major.inc.php
function syncAjax - - functions_major.inc.php
7/26/10 CngClass(), do_time() added
9/27/10 added Canada reverse geocoding.
1/6/11 json decode added
2/19/11 drag function added
*/
	function do_sel_update (in_unit, in_val) {							// 12/17/09
		to_server(in_unit, in_val);
		}


	function to_server(the_unit, the_status) {							// write unit status data via ajax xfer
		var querystr = "frm_responder_id=" + the_unit;
		querystr += "&frm_status_id=" + the_status;
	
		var url = "as_up_un_status.php?" + querystr;			// 
		var payload = syncAjax(url);						// 
		if (payload.substring(0,1)=="-") {	
			alert ("<php print __LINE__;?>: msg failed ");
			return false;
			}
		else {
			parent.frames['upper'].show_msg ('Unit status update applied!')
			return true;
			}				// end if/else (payload.substring(... )
		}		// end function to_server()
	
	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
//			alert("257 " + strURL);
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// e
			return AJAX.responseText;																				 
			} 
		else {
			alert ("<?php print __LINE__; ?>: failed");
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

/*
Slippers Pl,  [0]
Camberwell,  [0]
Greater London SE16 2  [1]
UK [2]

Arniston,  [0]
Midlothian EH23 4,  [1]
UK  [2]

39-55 Cheviot Rd, 
South Tyneside NE32 5, 
UK	 [2]

*/

	function pars_goog_addr(addr_str) {
		var addr = "";
		var city = "";
		var st = "";

		var addr_ar = addr_str.split(",", 5);
		switch (addr_ar[(addr_ar.length-1)].trim()) {
		
			case "USA":	// frm_street frm_city frm_state - string.substring(from, to)
			case "Canada":					// 9/27/10
				switch (addr_ar.length) {
					case 3:					
						addr = "";
						city = addr_ar[0].trim();
						st = addr_ar[1].trim().substring(0, 2);					
						break;
				
					case 4:
						addr = addr_ar[0].trim();
						city = addr_ar[1].trim();
						st = addr_ar[2].trim().substring(0, 2);					
						break;
					default:
						alert ("<?php print __LINE__; ?> err: " + addr_ar.length);
					}			
				break;
		
			case "UK":
				switch (addr_ar.length) {
					case 3:
						addr = addr_ar[0].trim();
						city = addr_ar[1].trim();
						st = addr_ar[2].trim();					
						break;
				
					case 4:
						addr = addr_ar[0].trim() + ", " + addr_ar[1].trim() ;
						city = addr_ar[2].trim();
						st = addr_ar[3].trim();					
						break;
					default:
						alert ("<?php print __LINE__; ?> err: " + addr_ar.length);
					}			
				break;		
		
			default:
				alert ( "<?php print __LINE__; ?> error");
			}		// end switch
		
		var return_ar = new Array(addr, city, st);
		return return_ar;
		}		// end function pars_goog_addr(addr_str) 

	function CngClass(obj, the_class){		// 7/26/10
		$(obj).className=the_class;
		return true;
		}

	function do_time() {							//7/26/10
		var today=new Date();
		today.setDate(today.getSeconds()+7.5);		// half-adjust
		var hours = today.getHours();
		var h=(hours < 10)?  "0" + hours : hours ;
		var mins = today.getMinutes();
		var m=(mins < 10)?  "0" + mins : mins ;
		return h+":"+m;
		}

	
JSON = new function(){	
	
	/* Section: Methods - Public */	
	
	/*	
	Method: decode	
		decodes a valid JSON encoded string.	
	
	Arguments:	
		[String / Function] - Optional JSON string to decode or a filter function if method is a String prototype.	
		[Function] - Optional filter function if first argument is a JSON string and this method is not a String prototype.	
	
	Returns:	
		Object - Generic JavaScript variable or undefined	
	
	Example [Basic]:	
		>var	arr = JSON.decode('[1,2,3]');	
		>alert(arr);	// 1,2,3	
		>	
		>arr = JSON.decode('[1,2,3]', function(key, value){return key * value});	
		>alert(arr);	// 0,2,6	
	
	Example [Prototype]:	
		>String.prototype.parseJSON = JSON.decode;	
		>	
		>alert('[1,2,3]'.parseJSON());	// 1,2,3	
		>	
		>try {	
		>	alert('[1,2,3]'.parseJSON(function(key, value){return key * value}));	
		>	// 0,2,6	
		>}	
		>catch(e) {	
		>	alert(e.message);	
		>}	
	
	Note:	
		Internet Explorer 5 and other old browsers should use a different regular expression to check if a JSON string is valid or not.	
		This old browsers dedicated RegExp is not safe as native version is but it required for compatibility.	
	*/	
	this.decode = function(){	
		var	filter, result, self, tmp;	
		if($$("toString")) {	
			switch(arguments.length){	
				case	2:	
					self = arguments[0];	
					filter = arguments[1];	
					break;	
				case	1:	
					if($[typeof arguments[0]](arguments[0]) === Function) {	
						self = this;	
						filter = arguments[0];	
					}	
					else	
						self = arguments[0];	
					break;	
				default:	
					self = this;	
					break;	
			};	
			if(rc.test(self)){	
				try{	
					result = e("(".concat(self, ")"));	
					if(filter && result !== null && (tmp = $[typeof result](result)) && (tmp === Array || tmp === Object)){	
						for(self in result)	
							result[self] = v(self, result) ? filter(self, result[self]) : result[self];	
					}	
				}	
				catch(z){}	
			}	
			else {	
				throw new JSONError("bad data");	
			}	
		};	
		return result;	
	};	
	
	/*	
	Method: encode	
		encode a generic JavaScript variable into a valid JSON string.	
	
	Arguments:	
		[Object] - Optional generic JavaScript variable to encode if method is not an Object prototype.	
	
	Returns:	
		String - Valid JSON string or undefined	
	
	Example [Basic]:	
		>var	s = JSON.encode([1,2,3]);	
		>alert(s);	// [1,2,3]	
	
	Example [Prototype]:	
		>Object.prototype.toJSONString = JSON.encode;	
		>	
		>alert([1,2,3].toJSONString());	// [1,2,3]	
	*/	
	this.encode = function(){	
		var	self = arguments.length ? arguments[0] : this,	
			result, tmp;	
		if(self === null)	
			result = "null";	
		else if(self !== undefined && (tmp = $[typeof self](self))) {	
			switch(tmp){	
				case	Array:	
					result = [];	
					for(var	i = 0, j = 0, k = self.length; j < k; j++) {	
						if(self[j] !== undefined && (tmp = JSON.encode(self[j])))	
							result[i++] = tmp;	
					};	
					result = "[".concat(result.join(","), "]");	
					break;	
				case	Boolean:	
					result = String(self);	
					break;	
				case	Date:	
					result = '"'.concat(self.getFullYear(), '-', d(self.getMonth() + 1), '-', d(self.getDate()), 'T', d(self.getHours()), ':', d(self.getMinutes()), ':', d(self.getSeconds()), '"');	
					break;	
				case	Function:	
					break;	
				case	Number:	
					result = isFinite(self) ? String(self) : "null";	
					break;	
				case	String:	
					result = '"'.concat(self.replace(rs, s).replace(ru, u), '"');	
					break;	
				default:	
					var	i = 0, key;	
					result = [];	
					for(key in self) {	
						if(self[key] !== undefined && (tmp = JSON.encode(self[key])))	
							result[i++] = '"'.concat(key.replace(rs, s).replace(ru, u), '":', tmp);	
					};	
					result = "{".concat(result.join(","), "}");	
					break;	
			}	
		};	
		return result;	
	};	
	
	/*	
	Method: toDate	
		transforms a JSON encoded Date string into a native Date object.	
	
	Arguments:	
		[String/Number] - Optional JSON Date string or server time if this method is not a String prototype. Server time should be an integer, based on seconds since 1970/01/01 or milliseconds / 1000 since 1970/01/01.	
	
	Returns:	
		Date - Date object or undefined if string is not a valid Date	
	
	Example [Basic]:	
		>var	serverDate = JSON.toDate("2007-04-05T08:36:46");	
		>alert(serverDate.getMonth());	// 3 (months start from 0)	
	
	Example [Prototype]:	
		>String.prototype.parseDate = JSON.toDate;	
		>	
		>alert("2007-04-05T08:36:46".parseDate().getDate());	// 5	
	
	Example [Server Time]:	
		>var	phpServerDate = JSON.toDate(<?php echo time(); ?>);	
		>var	csServerDate = JSON.toDate(<%=(DateTime.Now.Ticks/10000-62135596800000)%>/1000);	
	
	Example [Server Time Prototype]:	
		>Number.prototype.parseDate = JSON.toDate;	
		>var	phpServerDate = (<?php echo time(); ?>).parseDate();	
		>var	csServerDate = (<%=(DateTime.Now.Ticks/10000-62135596800000)%>/1000).parseDate();	
	
	Note:	
		This method accepts an integer or numeric string too to mantain compatibility with generic server side time() function.	
		You can convert quickly mtime, ctime, time and other time based values.	
		With languages that supports milliseconds you can send total milliseconds / 1000 (time is set as time * 1000)	
	*/	
	this.toDate = function(){	
		var	self = arguments.length ? arguments[0] : this,	
			result;	
		if(rd.test(self)){	
			result = new Date;	
			result.setHours(i(self, 11, 2));	
			result.setMinutes(i(self, 14, 2));	
			result.setSeconds(i(self, 17, 2));	
			result.setMonth(i(self, 5, 2) - 1);	
			result.setDate(i(self, 8, 2));	
			result.setFullYear(i(self, 0, 4));	
		}	
		else if(rt.test(self))	
			result = new Date(self * 1000);	
		return result;	
	};	
	
	/* Section: Properties - Private */	
	
	/*	
	Property: Private	
	
	List:	
		Object - 'c' - a dictionary with useful keys / values for fast encode convertion	
		Function - 'd' - returns decimal string rappresentation of a number ("14", "03", etc)	
		Function - 'e' - safe and native code evaulation	
		Function - 'i' - returns integer from string ("01" => 1, "15" => 15, etc)	
		Array - 'p' - a list with different "0" strings for fast special chars escape convertion	
		RegExp - 'rc' - regular expression to check JSON strings (different for IE5 or old browsers and new one)	
		RegExp - 'rd' - regular expression to check a JSON Date string	
		RegExp - 'rs' - regular expression to check string chars to modify using c (char) values	
		RegExp - 'rt' - regular expression to check integer numeric string (for toDate time version evaluation)	
		RegExp - 'ru' - regular expression to check string chars to escape using "\u" prefix	
		Function - 's' - returns escaped string adding "\\" char as prefix ("\\" => "\\\\", etc.)	
		Function - 'u' - returns escaped string, modifyng special chars using "\uNNNN" notation	
		Function - 'v' - returns boolean value to skip object methods or prototyped parameters (length, others), used for optional decode filter function	
		Function - '$' - returns object constructor if it was not cracked (someVar = {}; someVar.constructor = String <= ignore them)	
		Function - '$$' - returns boolean value to check native Array and Object constructors before convertion	
	*/	
	var	c = {"\b":"b","\t":"t","\n":"n","\f":"f","\r":"r",'"':'"',"\\":"\\","/":"/"},	
		d = function(n){return n<10?"0".concat(n):n},	
		e = function(c,f,e){e=eval;delete eval;if(typeof eval==="undefined")eval=e;f=eval(""+c);eval=e;return f},	
		i = function(e,p,l){return 1*e.substr(p,l)},	
		p = ["","000","00","0",""],	
		rc = null,	
		rd = /^[0-9]{4}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/,	
		rs = /(\x5c|\x2F|\x22|[\x0c-\x0d]|[\x08-\x0a])/g,	
		rt = /^([0-9]+|[0-9]+[,\.][0-9]{1,3})$/,	
		ru = /([\x00-\x07]|\x0b|[\x0e-\x1f])/g,	
		s = function(i,d){return "\\".concat(c[d])},	
		u = function(i,d){	
			var	n=d.charCodeAt(0).toString(16);	
			return "\\u".concat(p[n.length],n)	
		},	
		v = function(k,v){return $[typeof result](result)!==Function&&(v.hasOwnProperty?v.hasOwnProperty(k):v.constructor.prototype[k]!==v[k])},	
		$ = {	
			"boolean":function(){return Boolean},	
			"function":function(){return Function},	
			"number":function(){return Number},	
			"object":function(o){return o instanceof o.constructor?o.constructor:null},	
			"string":function(){return String},	
			"undefined":function(){return null}	
		},	
		$$ = function(m){	
			function $(c,t){t=c[m];delete c[m];try{e(c)}catch(z){c[m]=t;return 1}};	
			return $(Array)&&$(Object)	
		};	
	try{rc=new RegExp('^("(\\\\.|[^"\\\\\\n\\r])*?"|[,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t])+?$')}	
	catch(z){rc=/^(true|false|null|\[.*\]|\{.*\}|".*"|\d+|\d+\.\d+)$/}	
	};
// __________________  2/19/11  ___________________________

//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************

// Determine browser and version.

function Browser() {

  var ua, s, i;

  this.isIE    = false;
  this.isNS    = false;
  this.version = null;

  ua = navigator.userAgent;

  s = "MSIE";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  // Treat any other "Gecko" browser as NS 6.1.

  s = "Gecko";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }
}

var browser = new Browser();

// Global object to hold drag information.

var dragObj = new Object();
dragObj.zIndex = 0;

function dragStart(event, id) {

  var el;
  var x, y;

  // If an element id was given, find it. Otherwise use the element being
  // clicked on.

  if (id)
    dragObj.elNode = document.getElementById(id);
  else {
    if (browser.isIE)
      dragObj.elNode = window.event.srcElement;
    if (browser.isNS)
      dragObj.elNode = event.target;

    // If this is a text node, use its parent element.

    if (dragObj.elNode.nodeType == 3)
      dragObj.elNode = dragObj.elNode.parentNode;
  }

  // Get cursor position with respect to the page.

  if (browser.isIE) {
    x = window.event.clientX + document.documentElement.scrollLeft
      + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop
      + document.body.scrollTop;
  }
  if (browser.isNS) {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }

  // Save starting positions of cursor and element.

  dragObj.cursorStartX = x;
  dragObj.cursorStartY = y;
  dragObj.elStartLeft  = parseInt(dragObj.elNode.style.left, 10);
  dragObj.elStartTop   = parseInt(dragObj.elNode.style.top,  10);

  if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
  if (isNaN(dragObj.elStartTop))  dragObj.elStartTop  = 0;

  // Update element's z-index.

  dragObj.elNode.style.zIndex = ++dragObj.zIndex;

  // Capture mousemove and mouseup events on the page.

  if (browser.isIE) {
    document.attachEvent("onmousemove", dragGo);
    document.attachEvent("onmouseup",   dragStop);
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS) {
    document.addEventListener("mousemove", dragGo,   true);
    document.addEventListener("mouseup",   dragStop, true);
    event.preventDefault();
  }
}

function dragGo(event) {

  var x, y;

  // Get cursor position with respect to the page.

  if (browser.isIE) {
    x = window.event.clientX + document.documentElement.scrollLeft
      + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop
      + document.body.scrollTop;
  }
  if (browser.isNS) {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }

  // Move drag element by the same amount the cursor has moved.

  dragObj.elNode.style.left = (dragObj.elStartLeft + x - dragObj.cursorStartX) + "px";
  dragObj.elNode.style.top  = (dragObj.elStartTop  + y - dragObj.cursorStartY) + "px";

  if (browser.isIE) {
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS)
    event.preventDefault();
}

function dragStop(event) {  // Stop capturing mousemove and mouseup events.

  if (browser.isIE) {
    document.detachEvent("onmousemove", dragGo);
    document.detachEvent("onmouseup",   dragStop);
  }
  if (browser.isNS) {
    document.removeEventListener("mousemove", dragGo,   true);
    document.removeEventListener("mouseup",   dragStop, true);
  }
}
function getWinDims() {							// 2/20/11
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		myWidth = window.innerWidth; myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth ||document.documentElement.clientHeight ) ) {
		myWidth = document.documentElement.clientWidth; myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		myWidth = document.body.clientWidth; myHeight = document.body.clientHeight;
	}
	return new Array(myWidth, myHeight);
}

function hideDiv(div_area) {
	var divarea = div_area 
	if (document.getElementById) { // DOM3 = IE5, NS6 
		document.getElementById(divarea).style.visibility = 'hidden'; 
	} else { 
		if (document.layers) { // Netscape 4 
		document.divarea.visibility = 'hidden'; 
	} else { // IE 4 
		document.all.divarea.style.visibility = 'hidden'; 
		} 
	} 
	} 

function showDiv(div_area) {
	var divarea = div_area 
	if (document.getElementById) { // DOM3 = IE5, NS6 
		document.getElementById(divarea).style.visibility = 'visible'; 
	} else { 
	if (document.layers) { // Netscape 4 
		document.divarea.visibility = 'visible'; 
	} else { // IE 4 
		document.all.divarea.style.visibility = 'visible'; 
		} 
	} 
	} 
	
var min=8;
var max=18;
function increaseFontSize() {
 
   var p = document.getElementsByTagName('p');
   for(i=0;i<p.length;i++) {
 
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
 
         var s = 12;
      }
      if(s!=max) {
 
         s += 1;
      }
      p[i].style.fontSize = s+"px"
 
   }
}
function decreaseFontSize() {
   var p = document.getElementsByTagName('p');
   for(i=0;i<p.length;i++) {
 
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
 
         var s = 12;
      }
      if(s!=min) {
 
         s -= 1;
      }
      p[i].style.fontSize = s+"px"
 
   }
} 

function $() {									// 1/21/09, 7/18/10
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
	
function do_hover (the_id) {
	CngClass(the_id, 'hover');
	return true;
	}

function do_plain (the_id) {				// 8/21/10
	CngClass(the_id, 'plain');
	return true;
	}

function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}	
