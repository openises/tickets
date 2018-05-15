/*
do_sel_update - functions_major.inc.php
function to_server - functions_major.inc.php
function syncAjax - - functions_major.inc.php
7/26/10 CngClass(), do_time() added
9/27/10 added Canada reverse geocoding.
1/6/11 json decode added
2/19/11 drag function added
*/
	function do_sel_update (in_unit, in_val, theIcon) {							// 12/17/09
		to_server(in_unit, in_val, theIcon);
		}

	function to_server(the_unit, the_status, theIcon) {							// write unit status data via ajax xfer
		var querystr = "frm_responder_id=" + the_unit;
		querystr += "&frm_status_id=" + the_status;
		var url = "as_up_un_status.php?" + querystr;			// 
		var payload = syncAjax(url);						// 
		if (payload.substring(0,1)=="-") {	
			alert ("<php print __LINE__;?>: msg failed ");
			return false;
			} else {
			parent.frames['upper'].show_msg ('Unit ' + theIcon + ' status update applied!');
			$("rsupd_" + the_unit).style.color = "#FFFFFF";
			$("rsupd_" + the_unit).style.backgroundColor = "#000000";
			$("rsupd_" + the_unit).innerHTML = payload;
			return true;
			}				// end if/else (payload.substring(... )
		}		// end function to_server()
		
	function do_sel_update_fac (in_unit, in_val, theIcon) {							// 3/15/11
		to_server_fac(in_unit, in_val, theIcon);
		}
		
	function to_server_fac(the_unit, the_status, theIcon) {		//	3/15/11							// 3/15/11
		var querystr = "frm_responder_id=" + the_unit;
		querystr += "&frm_status_id=" + the_status;
		var url = "as_up_fac_status.php?" + querystr;
		var payload = syncAjax(url); 
		if (payload.substring(0,1)=="-") {	
			alert ("<?php print __LINE__;?>: msg failed ");
			return false;
			} else {
			parent.frames['upper'].show_msg ('Facility ' + theIcon + ' status update applied!');
			$("fsupd_" + the_unit).style.color = "#FFFFFF";
			$("fsupd_" + the_unit).style.backgroundColor = "#000000";
			$("fsupd_" + the_unit).innerHTML = payload;
			return true;
			}				// end if/else (payload.substring(... )
		}		// end function to_server_fac()
	
	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
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
//				alert(e.message);	
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

var _startX = 0;            // mouse starting positions
var _startY = 0;
var _offsetX = 0;           // current element offset
var _offsetY = 0;
var _dragElement;           // needs to be passed from OnMouseDown to OnMouseMove
var _oldZIndex = 0;         // we temporarily increase the z-index during drag

function dragStart(e) {
    // IE is retarded and doesn't pass the event object
    if (e == null) 
        e = window.event; 
    
    // IE uses srcElement, others use target
    var target = e.target != null ? e.target : e.srcElement;

    // for IE, left click == 1
    // for Firefox, left click == 0
    if ((e.button == 1 && window.event != null || 
        e.button == 0) && 
        target.className == 'bar') {
		target = target.parentNode
        // grab the mouse position
        _startX = e.clientX;
        _startY = e.clientY;
        
        // grab the clicked element's position
        _offsetX = ExtractNumber(target.style.left);
        _offsetY = ExtractNumber(target.style.top);
        
        // bring the clicked element to the front while it is being dragged
        _oldZIndex = target.style.zIndex;
        target.style.zIndex = 10000;
        
        // we need to access the element in OnMouseMove
        _dragElement = target;

        // tell our code to start moving the element with the mouse
        document.onmousemove = dragGo;
        document.onmouseup = dragStop;
        // cancel out any text selections
        document.body.focus();

        // prevent text selection in IE
        document.onselectstart = function () { return false; };
        // prevent IE from trying to drag an image
        target.ondragstart = function() { return false; };
        
        // prevent text selection (except IE)
        return false;
		}
	}

function dragGo(e) {
    if (e == null) 
        var e = window.event; 

    // this is the actual "drag code"
    _dragElement.style.left = (_offsetX + e.clientX - _startX) + 'px';
    _dragElement.style.top = (_offsetY + e.clientY - _startY) + 'px';
    
	}

function dragStop(e) {
    if (_dragElement != null) {
        _dragElement.style.zIndex = _oldZIndex;

        // we're done with these events until the next OnMouseDown
        document.onmousemove = null;
        document.onselectstart = null;
        _dragElement.ondragstart = null;

        // this is how we know we're not dragging      
        _dragElement = null;
		}
	}

function ExtractNumber(value)
{
    var n = parseInt(value);
	
    return n == null || isNaN(n) ? 0 : n;
}

// this is simply a shortcut for the eyes and fingers
function $(id)
{
    return document.getElementById(id);
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

function htmlentities(string, quote_style, charset, double_encode) {
  // discuss at: http://phpjs.org/functions/htmlentities/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: nobbler
  // improved by: Jack
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  // improved by: Dj (http://phpjs.org/functions/htmlentities:425#comment_134018)
  // bugfixed by: Onno Marsman
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // input by: Ratheous
  // depends on: get_html_translation_table
  // note: function is compatible with PHP 5.2 and older
  // example 1: htmlentities('Kevin & van Zonneveld');
  // returns 1: 'Kevin &amp; van Zonneveld'
  // example 2: htmlentities("foo'bar","ENT_QUOTES");
  // returns 2: 'foo&#039;bar'
  var hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style),
      symbol = '';

  string = string == null ? '' : string + '';

  if (!hash_map) {
    return false;
  }

  if (quote_style && quote_style === 'ENT_QUOTES') {
    hash_map["'"] = '&#039;';
  }

  double_encode = double_encode == null || !!double_encode;

  var regex = new RegExp("&(?:#\\d+|#x[\\da-f]+|[a-zA-Z][\\da-z]*);|[" +
                Object.keys(hash_map)
                  .join("")
                  // replace regexp special chars
                  .replace(/([()[\]{}\-.*+?^$|\/\\])/g, "\\$1")
                + "]",
              "g");

  return string.replace(regex, function (ent) {
    if (ent.length > 1) {
      return double_encode ? hash_map["&"] + ent.substr(1) : ent;
    }

    return hash_map[ent];
  });
}

function get_html_translation_table(table, quote_style) {
  //  discuss at: http://phpjs.org/functions/get_html_translation_table/
  // original by: Philip Peterson
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: noname
  // bugfixed by: Alex
  // bugfixed by: Marco
  // bugfixed by: madipta
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: T.Wild
  // improved by: KELAN
  // improved by: Brett Zamir (http://brett-zamir.me)
  //    input by: Frank Forte
  //    input by: Ratheous
  //        note: It has been decided that we're not going to add global
  //        note: dependencies to php.js, meaning the constants are not
  //        note: real constants, but strings instead. Integers are also supported if someone
  //        note: chooses to create the constants themselves.
  //   example 1: get_html_translation_table('HTML_SPECIALCHARS');
  //   returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

  var entities = {},
    hash_map = {},
    decimal;
  var constMappingTable = {},
    constMappingQuoteStyle = {};
  var useTable = {},
    useQuoteStyle = {};

  // Translate arguments
  constMappingTable[0] = 'HTML_SPECIALCHARS';
  constMappingTable[1] = 'HTML_ENTITIES';
  constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
  constMappingQuoteStyle[2] = 'ENT_COMPAT';
  constMappingQuoteStyle[3] = 'ENT_QUOTES';

  useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
  useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() :
    'ENT_COMPAT';

  if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
    throw new Error('Table: ' + useTable + ' not supported');
    // return false;
  }

  entities['38'] = '&amp;';
  if (useTable === 'HTML_ENTITIES') {
    entities['160'] = '&nbsp;';
    entities['161'] = '&iexcl;';
    entities['162'] = '&cent;';
    entities['163'] = '&pound;';
    entities['164'] = '&curren;';
    entities['165'] = '&yen;';
    entities['166'] = '&brvbar;';
    entities['167'] = '&sect;';
    entities['168'] = '&uml;';
    entities['169'] = '&copy;';
    entities['170'] = '&ordf;';
    entities['171'] = '&laquo;';
    entities['172'] = '&not;';
    entities['173'] = '&shy;';
    entities['174'] = '&reg;';
    entities['175'] = '&macr;';
    entities['176'] = '&deg;';
    entities['177'] = '&plusmn;';
    entities['178'] = '&sup2;';
    entities['179'] = '&sup3;';
    entities['180'] = '&acute;';
    entities['181'] = '&micro;';
    entities['182'] = '&para;';
    entities['183'] = '&middot;';
    entities['184'] = '&cedil;';
    entities['185'] = '&sup1;';
    entities['186'] = '&ordm;';
    entities['187'] = '&raquo;';
    entities['188'] = '&frac14;';
    entities['189'] = '&frac12;';
    entities['190'] = '&frac34;';
    entities['191'] = '&iquest;';
    entities['192'] = '&Agrave;';
    entities['193'] = '&Aacute;';
    entities['194'] = '&Acirc;';
    entities['195'] = '&Atilde;';
    entities['196'] = '&Auml;';
    entities['197'] = '&Aring;';
    entities['198'] = '&AElig;';
    entities['199'] = '&Ccedil;';
    entities['200'] = '&Egrave;';
    entities['201'] = '&Eacute;';
    entities['202'] = '&Ecirc;';
    entities['203'] = '&Euml;';
    entities['204'] = '&Igrave;';
    entities['205'] = '&Iacute;';
    entities['206'] = '&Icirc;';
    entities['207'] = '&Iuml;';
    entities['208'] = '&ETH;';
    entities['209'] = '&Ntilde;';
    entities['210'] = '&Ograve;';
    entities['211'] = '&Oacute;';
    entities['212'] = '&Ocirc;';
    entities['213'] = '&Otilde;';
    entities['214'] = '&Ouml;';
    entities['215'] = '&times;';
    entities['216'] = '&Oslash;';
    entities['217'] = '&Ugrave;';
    entities['218'] = '&Uacute;';
    entities['219'] = '&Ucirc;';
    entities['220'] = '&Uuml;';
    entities['221'] = '&Yacute;';
    entities['222'] = '&THORN;';
    entities['223'] = '&szlig;';
    entities['224'] = '&agrave;';
    entities['225'] = '&aacute;';
    entities['226'] = '&acirc;';
    entities['227'] = '&atilde;';
    entities['228'] = '&auml;';
    entities['229'] = '&aring;';
    entities['230'] = '&aelig;';
    entities['231'] = '&ccedil;';
    entities['232'] = '&egrave;';
    entities['233'] = '&eacute;';
    entities['234'] = '&ecirc;';
    entities['235'] = '&euml;';
    entities['236'] = '&igrave;';
    entities['237'] = '&iacute;';
    entities['238'] = '&icirc;';
    entities['239'] = '&iuml;';
    entities['240'] = '&eth;';
    entities['241'] = '&ntilde;';
    entities['242'] = '&ograve;';
    entities['243'] = '&oacute;';
    entities['244'] = '&ocirc;';
    entities['245'] = '&otilde;';
    entities['246'] = '&ouml;';
    entities['247'] = '&divide;';
    entities['248'] = '&oslash;';
    entities['249'] = '&ugrave;';
    entities['250'] = '&uacute;';
    entities['251'] = '&ucirc;';
    entities['252'] = '&uuml;';
    entities['253'] = '&yacute;';
    entities['254'] = '&thorn;';
    entities['255'] = '&yuml;';
  }

  if (useQuoteStyle !== 'ENT_NOQUOTES') {
    entities['34'] = '&quot;';
  }
  if (useQuoteStyle === 'ENT_QUOTES') {
    entities['39'] = '&#39;';
  }
  entities['60'] = '&lt;';
  entities['62'] = '&gt;';

  // ascii decimals to real symbols
  for (decimal in entities) {
    if (entities.hasOwnProperty(decimal)) {
      hash_map[String.fromCharCode(decimal)] = entities[decimal];
    }
  }

  return hash_map;
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

function ck_frames() {
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	}		// end function ck_frames()

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
	if($(the_id).classList.contains("text_large")) {
		CngClass(the_id, 'hover text_large');	
		} else if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'hover text_small');
		} else {
		CngClass(the_id, 'hover text');
		}
	return true;
	}

function do_plain (the_id) {				// 8/21/10
	if($(the_id).classList.contains("text_large")) {
		CngClass(the_id, 'plain text_large');			
		} else if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'plain text_small');
		} else {
		CngClass(the_id, 'plain text');
		}
	return true;
	}
	
function do_hover_mi (the_id) {
	CngClass(the_id, 'hovermi text_bold text_biggest');
	return true;
	}

function do_plain_mi (the_id) {				// 8/21/10
	CngClass(the_id, 'plainmi text_bold text_biggest');
	return true;
	}
	
function do_hover_squarebuttons (the_id) {
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'hover_square text_small');
		} else {
		CngClass(the_id, 'hover_square text');
		}
	return true;
	}

function do_plain_squarebuttons (the_id) {				// 8/21/10
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'plain_square text_small');
		} else {
		CngClass(the_id, 'plain_square text');
		}
	return true;
	}
	
function do_hover_centerbuttons (the_id) {
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'hover_centerbuttons text_small');		
		} else {
		CngClass(the_id, 'hover_centerbuttons text');
		}
	return true;
	}

function do_plain_centerbuttons (the_id) {				// 8/21/10
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'plain_centerbuttons text_small');		
		} else {
		CngClass(the_id, 'plain_centerbuttons text');
		}
	return true;
	}
	
function do_hover_listheader (the_id) {
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'hover_listheader text_small');
		} else {
		CngClass(the_id, 'hover_listheader text');
		}
	return true;
	}

function do_plain_listheader (the_id) {				// 8/21/10
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'plain_listheader text_small');		
		} else {
		CngClass(the_id, 'plain_listheader text');
		}
	return true;
	}
	
function do_hover_vert (the_id) {
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'hover_vert text_small');		
		} else {
		CngClass(the_id, 'hover_vert text');
		}
	return true;
	}

function do_plain_vert (the_id) {
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'plain_vert text_small');		
		} else {
		CngClass(the_id, 'plain_vert text');
		}
	return true;
	}
	
function do_lo_hover (the_id) {
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'lo_hover text_small');		
		} else {
		CngClass(the_id, 'lo_hover text');
		}
	return true;
	}

function do_lo_plain (the_id) {
	if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'lo_plain text_small');
		} else {
		CngClass(the_id, 'lo_plain text');
		}
	return true;
	}
	
function do_plain_medium (the_id) {				// 8/21/10
	CngClass(the_id, 'plain text_medium');
	return true;
	}
	
function do_hover_medium (the_id) {
	CngClass(the_id, 'hover text_medium');
	return true;
	}

function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}

function isNull(what){
	return what==null
	}

function isNullOrEmpty(str) {
	if (null == str || "" == str) {return true;} else { return false;}
	}
	
String.prototype.trim = function () {									// added 6/10/08
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};
	
function chknum(val) { 
	return ((val.trim().replace(/\D/g, "")==val.trim()) && (val.trim().length>0));}

function chkval(val, lo, hi) { 
	return  (chknum(val) && !((val> hi) || (val < lo)));}

function whatBrows() {									//Displays the generic browser type
	window.alert("Browser is : " + type);
	}

function ShowLayer(id, action){							// Show and hide a span/layer -- Seems to work with all versions NN4 plus other browsers
	if (type=="IE") 				eval("document.all." + id + ".style.display='" + action + "'");  	// id is the span/layer, action is either hidden or visible
	if (type=="NN") 				eval("document." + id + ".display='" + action + "'");
	if (type=="MO" || type=="OP") 	eval("$('" + id + "').style.display='" + action + "'");
	}

function hideit (elid) {
	ShowLayer(elid, "none");
	}

function showit (elid) {
	ShowLayer(elid, "block");
	}

function add_hash(in_str) { // prepend # if absent
	return (in_str.substr(0,1)=="#")? in_str : "#" + in_str;
	}

function capWords(str){ 											// 7/5/10
	var words = str.split(" "); 
	for (var i=0 ; i < words.length ; i++){ 
		var testwd = words[i]; 
		var firLet = testwd.substr(0,1); 
		var rest = testwd.substr(1, testwd.length -1) 
		words[i] = firLet.toUpperCase() + rest 
		} 
	return( words.join(" ")); 
	} 


function URLEncode(plaintext ) {					// 3/15/11 The Javascript escape and unescape functions do,
													// NOT correspond with what browsers actually do...
	var SAFECHARS = "0123456789" +					// Numeric
					"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
					"abcdefghijklmnopqrstuvwxyz" +	// guess
					"-_.!*'()";					// RFC2396 Mark characters
	var HEX = "0123456789ABCDEF";

	var encoded = "";
	for (var i = 0; i < plaintext.length; i++ ) {
		var ch = plaintext.charAt(i);
		if (ch == " ") {
			encoded += "+";				// x-www-urlencoded, rather than %20
		} else if (SAFECHARS.indexOf(ch) != -1) {
			encoded += ch;
		} else {
			var charCode = ch.charCodeAt(0);
			if (charCode > 255) {
				alert( "Unicode Character '"
						+ ch
						+ "' cannot be encoded using standard URL encoding.\n" +
						  "(URL encoding only supports 8-bit characters.)\n" +
						  "A space (+) will be substituted." );
				encoded += "+";
			} else {
				encoded += "%";
				encoded += HEX.charAt((charCode >> 4) & 0xF);
				encoded += HEX.charAt(charCode & 0xF);
				}
			}
		} 			// end for(...)
	return encoded;
	};			// end function

function URLDecode(encoded ){   					// Replace + with ' '
   var HEXCHARS = "0123456789ABCDEFabcdef";  		// Replace %xx with equivalent character
   var plaintext = "";   							// Place [ERROR] in output if %xx is invalid.
   var i = 0;
   while (i < encoded.length) {
	   var ch = encoded.charAt(i);
	   if (ch == "+") {
		   plaintext += " ";
		   i++;
	   } else if (ch == "%") {
			if (i < (encoded.length-2)
					&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1
					&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
				plaintext += unescape( encoded.substr(i,3) );
				i += 3;
			} else {
				alert( '-- invalid escape combination near ...' + encoded.substr(i) );
				plaintext += "%[ERROR]";
				i++;
			}
		} else {
			plaintext += ch;
			i++;
			}
	} 				// end  while (...)
	return plaintext;
	};				// end function URLDecode()
	
function sendRequest(url,callback,postData) {
	var req = createXMLHTTPObject();
	if (!req) return;
	var method = (postData) ? "POST" : "GET";
	req.open(method,url,true);
	if (postData)
		req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	req.onreadystatechange = function () {
		if (req.readyState != 4) return;
		if (req.status != 200 && req.status != 304) {
			return;
			}
		callback(req);
		}
	if (req.readyState == 4) return;
	req.send(postData);
	}

var XMLHttpFactories = [
	function () {return new XMLHttpRequest()	},
	function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
	function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
	function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
	];

function createXMLHTTPObject() {
	var xmlhttp = false;
	for (var i=0;i<XMLHttpFactories.length;i++) {
		try {
			xmlhttp = XMLHttpFactories[i]();
			}
		catch (e) {
			continue;
			}
		break;
		}
	return xmlhttp;
	}

function to_str(instr) {			// 0-based conversion - 2/13/09
	function ord( string ) {
		return (string+'').charCodeAt(0);
		}

	function chr( ascii ) {
		return String.fromCharCode(ascii);
		}
	function to_char(val) {
		return(chr(ord("A")+val));
		}

	var lop = (instr % 26);													// low-order portion, a number
	var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
	return hop+to_char(lop);
	}
	
function open_tick_window (id) {										// 4/29/10
	var url = "single.php?ticket_id="+ id;
	var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	setTimeout(function() { tickWindow.focus(); }, 1);
	}
	
function file_window(id) {										// 9/10/13
	var url = "file_upload.php?responder_id="+ id;
	var nfWindow = window.open(url, 'NewFileWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	setTimeout(function() { nfWindow.focus(); }, 1);
	}
	
function twitter_window() {										// 9/10/13
	var url = "load.php";
	var twWindow = window.open(url, 'TwitterWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	setTimeout(function() { twWindow.focus(); }, 1);
	}

var starting = false;

function do_mail_win(the_id) {
	if(starting) {return;}					// dbl-click catcher
	starting=true;
	var the_height = window.screen.height * 0.7;
	var the_width = window.screen.width * 0.5;
	var url = (isNullOrEmpty(the_id))? "do_unit_mail.php?name=0" : "do_unit_mail.php?name=" + escape(the_id);	//
	newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height="+the_height+",width="+the_width+",status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50");
	if (isNull(newwindow_mail)) {
		alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	newwindow_mail.focus();
	starting = false;
	}		// end function do mail_win()
	
function do_direcs_mail_win() {
	if(starting) {return;}					// dbl-click catcher
	starting=true;
	var the_height = window.screen.height * 0.7;
	var the_width = window.screen.width * 0.5;
	var url = "do_direcs_mail.php";
	newwindow_direcs=window.open(url, "mail_direcs",  "titlebar, location=0, resizable=1, scrollbars, height="+the_height+",width="+the_width+",status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50");
	if (isNull(newwindow_direcs)) {
		alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	newwindow_direcs.focus();
	document.email_form.submit();
	starting = false;
	}		// end function do mail_win()

function do_fac_mail_win(the_name, the_addrs) {			// 3/8/10
	if(starting) {return;}					// dbl-click catcher
	starting=true;
	var the_height = window.screen.height * 0.7;
	var the_width = window.screen.width * 0.5;
	var url = (isNullOrEmpty(the_name))? "do_fac_mail.php?" : "do_fac_mail.php?name=" + escape(the_name) + "&addrs=" + escape(the_addrs);	//
	newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height="+the_height+",width="+the_width+",status=0,toolbar=0,menubar=0,location=0, left=50,top=100,screenX=50,screenY=100");
	if (isNull(newwindow_mail)) {
		alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	newwindow_mail.focus();
	starting = false;
	}		// end function do mail_win()

function do_member_mail_win(the_id) {	
	if(starting) {return;}					// dbl-click catcher
	starting=true;
	var url = "do_member_mail.php?name=" + escape(the_id);	//
	newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=320,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=100,screenX=100,screenY=100");
	if (isNull(newwindow_mail)) {
		alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	newwindow_mail.focus();
	starting = false;
	}		// end function do mail_win()
	
function do_mail_in_win(id) {			// individual email 8/17/09
	if(starting) {return;}					
	starting=true;
	var the_height = window.screen.height * 0.4;
	var the_width = window.screen.width * 0.4;
	var url = "do_indiv_mail.php?the_id=" + id;	
	newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height='+the_height+',width='+the_width+',status=0,toolbar=0,menubar=0,location=0, left=50,top=100,screenX=50,screenY=100');
	if (isNull(newwindow_in)) {
		alert ("This requires popups to be enabled. Please adjust your browser options.");
		return;
		}
	newwindow_in.focus();
	starting = false;
	}

function do_close_tick(the_id) {	//	3/15/11
	if(starting) {return;}					// dbl-click catcher
	starting=true;
	var url = "close_in.php?ticket_id=" + escape(the_id);	//
	newwindow_close = window.open(url, "close_ticket", "titlebar, location=0, resizable=1, scrollbars, height=300, width=700, status=0, toolbar=0, menubar=0, left=100,top=100,screenX=100,screenY=100");
	if (isNull(newwindow_close)) {
		alert ("Close Ticket operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	if (window.focus) {newwindow_close.focus()}
	if(tmarkers[the_id]) {tmarkers[the_id].closePopup();}
	starting = false;
	}		// end function do mail_win()
	
function view_log_entry(id) {										// 9/10/13
	var url = "unit_log_view.php?id=" + id;
	var ulvWindow = window.open(url, 'unitLogWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	setTimeout(function() { ulvWindow.focus(); }, 1);
	}
	
function do_mail_all_win(the_ticket) {
	if(starting) {return;}					
	starting=true;	
	newwindow_um=window.open("do_unit_mail.php?the_ticket=" + the_ticket, "Email",  "titlebar, resizable=1, scrollbars, height=640,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=50,screenX=50,screenY=50");
	if (isNull(newwindow_um)) {
		alert ("This requires popups to be enabled. Please adjust your browser options.");
		return;
		}
	newwindow_um.focus();
	starting = false;
	}
	
function unit_log(id) {										// 9/10/13
	var url = "unit_ticket_log.php?responder="+ id + "&ticket=0";
	var ulWindow = window.open(url, 'unitLogWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
	setTimeout(function() { ulWindow.focus(); }, 1);
	}
	
var newwindow_incfs = null;
function do_full_inc_scr() {                            //9/7/09
	if ((newwindow_incfs) && (!(newwindow_incfs.closed))) {newwindow_incfs.focus(); return;}		// 7/28/10
	if (logged_in()) {
		if(starting) {return;}                        // 4/15/10 fullscreen=no
		if(window.focus() && newwindow_incfs) {newwindow_incfs.focus()}    // if already exists
		starting=true;
		params  = 'width='+screen.width;
		params += ', height='+screen.height;
		params += ', top=0, left=0, scrollbars = 1';
		params += ', resizable=1';
		newwindow_incfs=window.open("full_sit_scr.php", "incidents_full_scr", params);
		if (isNull(newwindow_incfs)) {
			alert ("This operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_incfs.focus();
		var params = "f_n=fullscr_sit&v_n=true&sess_id=" + sess_id;
		var url = "persist2.php";
		sendRequest (url, gb_handleResult, params);
		starting = false;
		}
	}        // end function do full_scr()
	
function get_roster_details(theForm, id)	{	//	9/6/13
	if(id==0) {
		$('user_details').innerHTML = "";
		return;
		}
	randomnumber=Math.floor(Math.random()*99999999);
	var theurl ="./ajax/get_roster_details.php?version=" + randomnumber + "&id=" + id;
	sendRequest (theurl, user_cb, "");
	function user_cb(req) {
		var the_details=JSON.decode(req.responseText);
		var the_text = "Team ID: " + the_details[1] + "<BR />";			
		var the_text = "Name: " + the_details[2] + " " + the_details[3] + "<BR />";
		the_text += "Address: " + the_details[4] + "<BR />";
		the_text += "State: " + the_details[5] + "<BR />";
		the_text += "Email: " + the_details[9] + "<BR />";
		the_text += "Home Phone: " + the_details[6] + "<BR />";
		the_text += "Work Phone: " + the_details[7] + "<BR />";
		the_text += "Cellphone: " + the_details[8] + "<BR />";
		the_text += "AR Callsign: " + the_details[10] + "<BR />";
		the_text += "Capabilities: " + the_details[11] + "<BR />";
		the_text += "Notes: " + the_details[12] + "<BR />";
		$('user_details').style.display = 'inline-block';
		$('user_details').innerHTML = the_text;
		}			
	}
	
function writeto_log(code, ticket_id, responder_id, info, fac_id, rec_fac_id, mileage)	{
	randomnumber=Math.floor(Math.random()*99999999);
	var theurl ="./ajax/do_log.php?version=" + randomnumber + "&code=" + code + "&ticket_id=" + ticket_id + "&responder_id=" + responder_id + "&info=" + info + "&fac_id=" + fac_id + "&rec_fac_id=" + rec_fac_id + "&mileage=" + mileage;
	sendRequest (theurl, log_cb, "");
	function log_cb(req) {
		}			
	}
	
function get_html_translation_table(table, quote_style) {
  //  discuss at: http://phpjs.org/functions/get_html_translation_table/
  // original by: Philip Peterson
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: noname
  // bugfixed by: Alex
  // bugfixed by: Marco
  // bugfixed by: madipta
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: T.Wild
  // improved by: KELAN
  // improved by: Brett Zamir (http://brett-zamir.me)
  //    input by: Frank Forte
  //    input by: Ratheous
  //        note: It has been decided that we're not going to add global
  //        note: dependencies to php.js, meaning the constants are not
  //        note: real constants, but strings instead. Integers are also supported if someone
  //        note: chooses to create the constants themselves.
  //   example 1: get_html_translation_table('HTML_SPECIALCHARS');
  //   returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

  var entities = {},
    hash_map = {},
    decimal;
  var constMappingTable = {},
    constMappingQuoteStyle = {};
  var useTable = {},
    useQuoteStyle = {};

  // Translate arguments
  constMappingTable[0] = 'HTML_SPECIALCHARS';
  constMappingTable[1] = 'HTML_ENTITIES';
  constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
  constMappingQuoteStyle[2] = 'ENT_COMPAT';
  constMappingQuoteStyle[3] = 'ENT_QUOTES';

  useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
  useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() :
    'ENT_COMPAT';

  if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
    throw new Error('Table: ' + useTable + ' not supported');
    // return false;
  }

  entities['38'] = '&amp;';
  if (useTable === 'HTML_ENTITIES') {
    entities['160'] = '&nbsp;';
    entities['161'] = '&iexcl;';
    entities['162'] = '&cent;';
    entities['163'] = '&pound;';
    entities['164'] = '&curren;';
    entities['165'] = '&yen;';
    entities['166'] = '&brvbar;';
    entities['167'] = '&sect;';
    entities['168'] = '&uml;';
    entities['169'] = '&copy;';
    entities['170'] = '&ordf;';
    entities['171'] = '&laquo;';
    entities['172'] = '&not;';
    entities['173'] = '&shy;';
    entities['174'] = '&reg;';
    entities['175'] = '&macr;';
    entities['176'] = '&deg;';
    entities['177'] = '&plusmn;';
    entities['178'] = '&sup2;';
    entities['179'] = '&sup3;';
    entities['180'] = '&acute;';
    entities['181'] = '&micro;';
    entities['182'] = '&para;';
    entities['183'] = '&middot;';
    entities['184'] = '&cedil;';
    entities['185'] = '&sup1;';
    entities['186'] = '&ordm;';
    entities['187'] = '&raquo;';
    entities['188'] = '&frac14;';
    entities['189'] = '&frac12;';
    entities['190'] = '&frac34;';
    entities['191'] = '&iquest;';
    entities['192'] = '&Agrave;';
    entities['193'] = '&Aacute;';
    entities['194'] = '&Acirc;';
    entities['195'] = '&Atilde;';
    entities['196'] = '&Auml;';
    entities['197'] = '&Aring;';
    entities['198'] = '&AElig;';
    entities['199'] = '&Ccedil;';
    entities['200'] = '&Egrave;';
    entities['201'] = '&Eacute;';
    entities['202'] = '&Ecirc;';
    entities['203'] = '&Euml;';
    entities['204'] = '&Igrave;';
    entities['205'] = '&Iacute;';
    entities['206'] = '&Icirc;';
    entities['207'] = '&Iuml;';
    entities['208'] = '&ETH;';
    entities['209'] = '&Ntilde;';
    entities['210'] = '&Ograve;';
    entities['211'] = '&Oacute;';
    entities['212'] = '&Ocirc;';
    entities['213'] = '&Otilde;';
    entities['214'] = '&Ouml;';
    entities['215'] = '&times;';
    entities['216'] = '&Oslash;';
    entities['217'] = '&Ugrave;';
    entities['218'] = '&Uacute;';
    entities['219'] = '&Ucirc;';
    entities['220'] = '&Uuml;';
    entities['221'] = '&Yacute;';
    entities['222'] = '&THORN;';
    entities['223'] = '&szlig;';
    entities['224'] = '&agrave;';
    entities['225'] = '&aacute;';
    entities['226'] = '&acirc;';
    entities['227'] = '&atilde;';
    entities['228'] = '&auml;';
    entities['229'] = '&aring;';
    entities['230'] = '&aelig;';
    entities['231'] = '&ccedil;';
    entities['232'] = '&egrave;';
    entities['233'] = '&eacute;';
    entities['234'] = '&ecirc;';
    entities['235'] = '&euml;';
    entities['236'] = '&igrave;';
    entities['237'] = '&iacute;';
    entities['238'] = '&icirc;';
    entities['239'] = '&iuml;';
    entities['240'] = '&eth;';
    entities['241'] = '&ntilde;';
    entities['242'] = '&ograve;';
    entities['243'] = '&oacute;';
    entities['244'] = '&ocirc;';
    entities['245'] = '&otilde;';
    entities['246'] = '&ouml;';
    entities['247'] = '&divide;';
    entities['248'] = '&oslash;';
    entities['249'] = '&ugrave;';
    entities['250'] = '&uacute;';
    entities['251'] = '&ucirc;';
    entities['252'] = '&uuml;';
    entities['253'] = '&yacute;';
    entities['254'] = '&thorn;';
    entities['255'] = '&yuml;';
  }

  if (useQuoteStyle !== 'ENT_NOQUOTES') {
    entities['34'] = '&quot;';
  }
  if (useQuoteStyle === 'ENT_QUOTES') {
    entities['39'] = '&#39;';
  }
  entities['60'] = '&lt;';
  entities['62'] = '&gt;';

  // ascii decimals to real symbols
  for (decimal in entities) {
    if (entities.hasOwnProperty(decimal)) {
      hash_map[String.fromCharCode(decimal)] = entities[decimal];
    }
  }

  return hash_map;
}

function html_entity_decode(string, quote_style) {
  //  discuss at: http://phpjs.org/functions/html_entity_decode/
  // original by: john (http://www.jd-tech.net)
  //    input by: ger
  //    input by: Ratheous
  //    input by: Nick Kolosov (http://sammy.ru)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: marc andreu
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Fox
  //  depends on: get_html_translation_table
  //   example 1: html_entity_decode('Kevin &amp; van Zonneveld');
  //   returns 1: 'Kevin & van Zonneveld'
  //   example 2: html_entity_decode('&amp;lt;');
  //   returns 2: '&lt;'

  var hash_map = {},
    symbol = '',
    tmp_str = '',
    entity = '';
  tmp_str = string.toString();

  if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
    return false;
  }

  // fix &amp; problem
  // http://phpjs.org/functions/get_html_translation_table:416#comment_97660
  delete(hash_map['&']);
  hash_map['&'] = '&amp;';

  for (symbol in hash_map) {
    entity = hash_map[symbol];
    tmp_str = tmp_str.split(entity)
      .join(symbol);
  }
  tmp_str = tmp_str.split('&#039;')
    .join("'");

  return tmp_str;
}

function base64_encode(data) {
  var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
  var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac = 0,
    enc = '',
    tmp_arr = [];

  if (!data) {
    return data;
  }

  data = unescape(encodeURIComponent(data));

  do {
    // pack three octets into four hexets
    o1 = data.charCodeAt(i++);
    o2 = data.charCodeAt(i++);
    o3 = data.charCodeAt(i++);

    bits = o1 << 16 | o2 << 8 | o3;

    h1 = bits >> 18 & 0x3f;
    h2 = bits >> 12 & 0x3f;
    h3 = bits >> 6 & 0x3f;
    h4 = bits & 0x3f;

    // use hexets to index into b64, and append result to encoded string
    tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
  } while (i < data.length);

  enc = tmp_arr.join('');

  var r = data.length % 3;

  return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}

function asyncAJAX(url) {
	var obj; 
	obj = new XMLHttpRequest();
	obj.onreadystatechange = function() {
		if(obj.readyState == 4) {
			//process the response
			}
		}
	obj.open("POST", url, true);
	obj.send(null);
	}
	
function set_fontsizes(screenwidth, screenFormat) {
	var theFonts = {verylarge:"18px",large:"16px",normal:"14px",medium:"12px",small:"10px",verysmall:"8px"};
	var theScreen = ($('screenname')) ? $('screenname').innerHTML : "undefined";
	var listPadding = "1.5em";
	var theScreenSize;
	if(screenFormat == "popup") {
		if(screenwidth > 1000) {
			theFonts.verylarge = "18px";
			theFonts.large = "16px";
			theFonts.normal = "12px";
			theFonts.medium = "11px";
			theFonts.small = "10px";
			theFonts.verysmall = "8px";
			listPadding = "2em";
			theScreenSize = "popup";
			} else {
			theFonts.verylarge = "15px";
			theFonts.large = "14px";
			theFonts.normal = "12px";
			theFonts.medium = "11px";
			theFonts.small = "10px";
			theFonts.verysmall = "8px";
			listPadding = "2em";
			theScreenSize = "popup";			
			}
		} else {
		if(screenwidth > 1900) {
			theFonts.verylarge = "20px";
			theFonts.large = "18px";
			theFonts.normal = "14px";
			theFonts.medium = "12px";
			theFonts.small = "10px";
			theFonts.verysmall = "8px";
			listPadding = "2em";
			theScreenSize = "Extralarge";
			} else if(screenwidth.between(1600, 1900)) {
			theFonts.verylarge = "18px";
			theFonts.large = "14px";
			theFonts.normal = "12px";
			theFonts.medium = "10px";
			theFonts.small = "9px";
			theFonts.verysmall = "7px";
			listPadding = "2em";
			theScreenSize = "Large";
			} else if(screenwidth.between(1200, 1599)) {
			theFonts.verylarge = "15px";
			theFonts.large = "12px";
			theFonts.normal = "11px";
			theFonts.medium = "10px";
			theFonts.small = "8px";
			theFonts.verysmall = "7px";
			listPadding = "1.5em";
			theScreenSize = "Normal";
			} else if(screenwidth.between(1024, 1199)) {
			theFonts.verylarge = "14px";
			theFonts.large = "11px";
			theFonts.normal = "10px";
			theFonts.medium = "9px";
			theFonts.small = "8px";
			theFonts.verysmall = "7px";
			listPadding = "1.5em";
			theScreenSize = "Small";
			} else if(screenwidth < 1024) {
			theFonts.verylarge = "12px";
			theFonts.large = "11px";
			theFonts.normal = "10px";
			theFonts.medium = "9px";
			theFonts.small = "7px";
			theFonts.verysmall = "7px";
			listPadding = "1em";
			theScreenSize = "Extrasmall";		
			}
		}
	jss.set('BODY', { "font-size": theFonts.normal });
	jss.set('SELECT', { "font-size": theFonts.normal });
	jss.set('OPTION', { "font-size": theFonts.normal });
	jss.set('INPUT', { "font-size": theFonts.normal });
	jss.set('TEXTAREA', { "font-size": theFonts.normal });
	jss.set('.text_verysmall', { "font-size": theFonts.verysmall });
	jss.set('.text_small', { "font-size": theFonts.small });
	jss.set('.text_medium', { "font-size": theFonts.medium });
	jss.set('.text', { "font-size": theFonts.normal });
	jss.set('.text_large', { "font-size": theFonts.large });
	jss.set('.text_big', { "font-size": theFonts.large });
	jss.set('.text_biggest', { "font-size": theFonts.verylarge });
	jss.set('.heading', { "font-size": theFonts.large });
	if(theScreen != "responders" && theScreen != "requests") {
		jss.set('div.scrollingArea', { "padding-top": listPadding });
		jss.set('div.scrollingArea2', { "padding-top": listPadding });
		} else {
		jss.set('div.scrollingArea', { "padding-top": "1em" });
		jss.set('div.scrollingArea2', { "padding-top": "1em" });			
		}
	window.screensize = theScreenSize;
	return theFonts;
	}
	
Number.prototype.between = function(first,last){
    return (first < last ? this >= first && this <= last : this >= last && this <= first);
	}

function pausecomp(millis) {
	var date = new Date();
	var curDate = null;

	do { curDate = new Date(); } 
	while(curDate-date < millis);
	}
	
function generatePassword() {
    var length = 12,
        charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
		}
	if(document.user_add_Form) {document.user_add_Form.frm_passwd.value = retVal;}
	if(document.user_add_Form) {document.user_add_Form.frm_passwd_confirm.value = retVal;}
	if($('view_password')) {$('view_password').style.display="inline-block"; $('view_password').innerHTML = retVal;}
	if(document.editProf) {document.editProf.frm_passwd.value = retVal;}
	if(document.editProf) {document.editProf.frm_passwd_confirm.value = retVal;}
	if($('view_password')) {$('view_password').style.display="inline-block"; $('view_password').innerHTML = retVal;}
    return retVal;
	}
	
function get_callboard() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/get_callboard.php?version=' + randomnumber + '&q=' + sess_id;
	sendRequest (url,callboard_cb, "");
	function callboard_cb(req) {
		var board_arr = JSON.decode(req.responseText);
		if(!board_arr) { return;}
		var outputtext = "<TABLE id='cbtable' class='fixedheadscrolling scrollable' style='width: 90%;'>";
		outputtext += "<thead>";
		outputtext += "<TR class='topline' style='width: 90%; background-color: #EFEFEF;'>";
		outputtext += "<TH id='tb1' COLSPAN='4' class='plain_listheader text' style='color: #000000; background-color: green;'>INCIDENT</TH>";
		outputtext += "<TH id='tb2' COLSPAN='9' class='plain_listheader text' style='color: #000000; background-color: orange;'>UNITS</TH>";
		outputtext += "<TH id='tb3' class='plain_listheader text' style='color: #FFFFFF; background-color: blue;'>AS OF</TH>";
		outputtext += "<TH id='tb4' COLSPAN='3' class='plain_listheader text' style='color: #000000; background-color: #FFFFFF;'>DISPATCH</TH>";
		outputtext += "</TR><BR />";
		outputtext += "<TR class='secondline' style='width: 90%; background-color: #EFEFEF;'>";		
		outputtext += "<TH id='b1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Incident Name');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Name</TH>";
		outputtext += "<TH id='b2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Incident Description');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Synopsis</TH>";
		outputtext += "<TH id='b3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Incident Address');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Address</TH>";
		outputtext += "<TH id='b4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Incident Type');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Nature</TH>";
		outputtext += "<TH id='b5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Responder Name');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Name</TH>";
		outputtext += "<TH id='b6' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Contact Unit');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Mail</TH>";
		outputtext += "<TH id='b7' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Dispatched');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">D</TH>";
		outputtext += "<TH id='b8' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Responding');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">R</TH>";
		outputtext += "<TH id='b9' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('On Scene');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">O</TH>";
		outputtext += "<TH id='b10' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('En-route to Facility');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">FE</TH>";
		outputtext += "<TH id='b11' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Arrived at Facility');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">FA</TH>";
		outputtext += "<TH id='b12' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Cleared from Job');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">C</TH>";
		outputtext += "<TH id='b13' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Responder Status');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Status</TH>";
		outputtext += "<TH id='b14' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Updated as of');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">&nbsp;</TH>";
		outputtext += "<TH id='b15' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Dispatched By');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">By</TH>";
		outputtext += "<TH id='b16' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Assignment Comments');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Comment</TH>";
		outputtext += "<TH id='b17' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id); Tip('Reset Dispatch Times');\" onMouseOut=\"do_plain_listheader(this.id); UnTip();\">Reset</TH>";
		outputtext += "</TR>";
		outputtext += "</thead>";
		outputtext += "<tbody>";
		var i = 1;
		for(var f = 0; f < board_arr.length; f++) {
			if(board_arr[f][8] != "") {
				var theMailBut = pad(10, "<DIV style='text-align: center;'><IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit " + board_arr[f][7] + "' onclick = 'do_mail_win(\"" + board_arr[f][1] + "\");'></DIV>", "\u00a0");
				} else {
				var theMailBut = pad(10, "", "\u00a0");
				}
			outputtext += "<TR id='line_" + board_arr[f][0] + "' CLASS='" + colors[i%2] + "' style='width: 90%;'>";
			outputtext += "<TD id='scope_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(30, board_arr[f][3], "\u00a0") + "</TD>";	//	Tickets Name
			outputtext += "<TD id='descr_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(30, board_arr[f][4], "\u00a0") + "</TD>";	//	Ticket Description
			outputtext += "<TD id='addr_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(30, board_arr[f][5], "\u00a0") + "</TD>";	//	Ticket Address
			outputtext += "<TD id='nature_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(10, board_arr[f][6], "\u00a0") + "</TD>";	//	Ticket Type
			outputtext += "<TD id='rname_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(20, board_arr[f][7], "\u00a0") + "</TD>";	//	Responder Name
			outputtext += "<TD id='mail_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + theMailBut + "</TD>";	//	Email
			outputtext += "<TD id='disp_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(10, board_arr[f][9], "\u00a0") + "</TD>";	//	Dispatched
			outputtext += "<TD id='resp_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(10, board_arr[f][10], "\u00a0") + "</TD>";	//	Responding
			outputtext += "<TD id='os_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(10, board_arr[f][11], "\u00a0") + "</TD>";		//	On Scene
			outputtext += "<TD id='enr_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(10, board_arr[f][12], "\u00a0") + "</TD>";	//	En route to Facilty
			outputtext += "<TD id='arr_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(10, board_arr[f][13], "\u00a0") + "</TD>";	//	Arrived at Facility
			outputtext += "<TD id='clear_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(10, board_arr[f][14], "\u00a0") + "</TD>";	//	Clear
			outputtext += "<TD id='comm_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(20, board_arr[f][15], "\u00a0") + "</TD>";	//	Responder Status
			outputtext += "<TD id='asof_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(20, board_arr[f][16], "\u00a0") + "</TD>";	//	Updated
			outputtext += "<TD id='db_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(5, board_arr[f][17], "\u00a0") + "</TD>";	//	Dispatched by
			outputtext += "<TD id='comments_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>" + pad(20, board_arr[f][18], "\u00a0") + "</TD>";	//	Assigns Comments
			outputtext += "<TD id='reset_" + board_arr[f][0] + "' class='plain_list text_bolder' style='border-right: 1px solid red;'>Reset</TD>";	//	Reset Dispatch times
			outputtext += "</TR>";
			i++;
			}
		outputtext += "</tbody>";
		outputtext += "</TABLE>";
		setTimeout(function() {
			if($('the_board')) {$('the_board').innerHTML = outputtext;}
			var cbtable = document.getElementById('cbtable');
			if(cbtable) {
				var headerRow = cbtable.rows[1];
				var tableRow = cbtable.rows[2];
				if(tableRow) {
					for (var i = 0; i < tableRow.cells.length; i++) {
						if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = (tableRow.cells[i].clientWidth + 1.4) + "px";}
						}
					var thePad = 1;
					var b1 = $('b1').clientWidth + thePad;
					var b2 = $('b2').clientWidth + thePad;
					var b3 = $('b3').clientWidth + thePad;
					var b4 = $('b4').clientWidth + thePad;
					var b5 = $('b5').clientWidth + thePad;
					var b6 = $('b6').clientWidth + thePad;
					var b7 = $('b7').clientWidth + thePad;
					var b8 = $('b8').clientWidth + thePad;
					var b9 = $('b9').clientWidth + thePad;
					var b10 = $('b10').clientWidth + thePad;
					var b11 = $('b11').clientWidth + thePad;
					var b12 = $('b12').clientWidth + thePad;
					var b13 = $('b13').clientWidth + thePad;
					var b14 = $('b14').clientWidth + thePad;
					var b15 = $('b15').clientWidth + thePad;
					var b16 = $('b16').clientWidth + thePad;
					var b17 = $('b17').clientWidth + thePad;
					var colheader1 = (b1 + b2 + b3 + b4) + "px";
					var colheader2 = (b5 + b6 + b7 + b8 + b9 + b10 + b11 + b12 + b13) + "px";
					var colheader3 = (b14) + "px";
					var colheader4 = (b15 + b16 + b17) + "px";
					$('tb1').style.width = colheader1;
					$('tb2').style.width = colheader2;
					$('tb3').style.width = colheader3;
					$('tb4').style.width = colheader4;
					} else {
					var cellwidthBase = window.cbWidth / 17;
					for (var i = 0; i < tableRow.cells.length; i++) {		
						headerRow.cells[0].style.width = cellwidthBase + "px";
						}
					}
				}
			},300);	
		}				// end function callboard_cb()
	}				// end function get_callboard()
