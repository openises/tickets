var sortby = '`date`';
var sort = "DESC";
var filterby = '';
var groupby = '';
var thefilter = "";
var the_cal = "";
var filter = "";
var ticket_id = "";
var the_selected_ticket = "";
var the_ticket = "";
var responder_id = "";
var datewidth = "8%";
var the_filter = "";
var theTicket;
var theResponder;
var theFilter;
var theSort;
var theOrder = "DESC";
var theScreen;
var thescreen;
var theStatus;
var msgs_interval = false;
var sentmsgs_interval = false;
var all_msgs_interval = false;
var folder = "";
var colors = new Array ('odd', 'even');

// Browser Window Size and Position
// copyright Stephen Chapman, 3rd Jan 2005, 8th Dec 2005
// you may copy these functions but please keep the copyright notice as well
function pageWidth() {
	return window.innerWidth != null? window.innerWidth : document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body != null ? document.body.clientWidth : null;
	} 
function pageHeight() {
	return  window.innerHeight != null? window.innerHeight : document.documentElement && document.documentElement.clientHeight ?  document.documentElement.clientHeight : document.body != null? document.body.clientHeight : null;
	} 
function posLeft() {
	return typeof window.pageXOffset != 'undefined' ? window.pageXOffset :document.documentElement && document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft ? document.body.scrollLeft : 0;
	} 
function posTop() {
	return typeof window.pageYOffset != 'undefined' ?  window.pageYOffset : document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop ? document.body.scrollTop : 0;
	} 
function posRight() {
	return posLeft()+pageWidth();} function posBottom() {return posTop()+pageHeight();
	}
	
Encoder = {

	// When encoding do we convert characters into html or numerical entities
	EncodeType : "entity",  // entity OR numerical

	isEmpty : function(val){
		if(val){
			return ((val===null) || val.length==0 || /^\s+$/.test(val));
		}else{
			return true;
		}
	},
	
	// arrays for conversion from HTML Entities to Numerical values
	arr1: ['&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&quot;','&amp;','&lt;','&gt;','&OElig;','&oelig;','&Scaron;','&scaron;','&Yuml;','&circ;','&tilde;','&ensp;','&emsp;','&thinsp;','&zwnj;','&zwj;','&lrm;','&rlm;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&Dagger;','&permil;','&lsaquo;','&rsaquo;','&euro;','&fnof;','&Alpha;','&Beta;','&Gamma;','&Delta;','&Epsilon;','&Zeta;','&Eta;','&Theta;','&Iota;','&Kappa;','&Lambda;','&Mu;','&Nu;','&Xi;','&Omicron;','&Pi;','&Rho;','&Sigma;','&Tau;','&Upsilon;','&Phi;','&Chi;','&Psi;','&Omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&thetasym;','&upsih;','&piv;','&bull;','&hellip;','&prime;','&Prime;','&oline;','&frasl;','&weierp;','&image;','&real;','&trade;','&alefsym;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&crarr;','&lArr;','&uArr;','&rArr;','&dArr;','&hArr;','&forall;','&part;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&sum;','&minus;','&lowast;','&radic;','&prop;','&infin;','&ang;','&and;','&or;','&cap;','&cup;','&int;','&there4;','&sim;','&cong;','&asymp;','&ne;','&equiv;','&le;','&ge;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&lceil;','&rceil;','&lfloor;','&rfloor;','&lang;','&rang;','&loz;','&spades;','&clubs;','&hearts;','&diams;'],
	arr2: ['&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;','&#34;','&#38;','&#60;','&#62;','&#338;','&#339;','&#352;','&#353;','&#376;','&#710;','&#732;','&#8194;','&#8195;','&#8201;','&#8204;','&#8205;','&#8206;','&#8207;','&#8211;','&#8212;','&#8216;','&#8217;','&#8218;','&#8220;','&#8221;','&#8222;','&#8224;','&#8225;','&#8240;','&#8249;','&#8250;','&#8364;','&#402;','&#913;','&#914;','&#915;','&#916;','&#917;','&#918;','&#919;','&#920;','&#921;','&#922;','&#923;','&#924;','&#925;','&#926;','&#927;','&#928;','&#929;','&#931;','&#932;','&#933;','&#934;','&#935;','&#936;','&#937;','&#945;','&#946;','&#947;','&#948;','&#949;','&#950;','&#951;','&#952;','&#953;','&#954;','&#955;','&#956;','&#957;','&#958;','&#959;','&#960;','&#961;','&#962;','&#963;','&#964;','&#965;','&#966;','&#967;','&#968;','&#969;','&#977;','&#978;','&#982;','&#8226;','&#8230;','&#8242;','&#8243;','&#8254;','&#8260;','&#8472;','&#8465;','&#8476;','&#8482;','&#8501;','&#8592;','&#8593;','&#8594;','&#8595;','&#8596;','&#8629;','&#8656;','&#8657;','&#8658;','&#8659;','&#8660;','&#8704;','&#8706;','&#8707;','&#8709;','&#8711;','&#8712;','&#8713;','&#8715;','&#8719;','&#8721;','&#8722;','&#8727;','&#8730;','&#8733;','&#8734;','&#8736;','&#8743;','&#8744;','&#8745;','&#8746;','&#8747;','&#8756;','&#8764;','&#8773;','&#8776;','&#8800;','&#8801;','&#8804;','&#8805;','&#8834;','&#8835;','&#8836;','&#8838;','&#8839;','&#8853;','&#8855;','&#8869;','&#8901;','&#8968;','&#8969;','&#8970;','&#8971;','&#9001;','&#9002;','&#9674;','&#9824;','&#9827;','&#9829;','&#9830;'],
		
	// Convert HTML entities into numerical entities
	HTML2Numerical : function(s){
		return this.swapArrayVals(s,this.arr1,this.arr2);
	},	

	// Convert Numerical entities into HTML entities
	NumericalToHTML : function(s){
		return this.swapArrayVals(s,this.arr2,this.arr1);
	},


	// Numerically encodes all unicode characters
	numEncode : function(s){ 
		if(this.isEmpty(s)) return ""; 

		var a = [],
			l = s.length; 
		
		for (var i=0;i<l;i++){ 
			var c = s.charAt(i); 
			if (c < " " || c > "~"){ 
				a.push("&#"); 
				a.push(c.charCodeAt()); //numeric value of code point 
				a.push(";"); 
			}else{ 
				a.push(c); 
			} 
		} 
		
		return a.join(""); 	
	}, 
	
	// HTML Decode numerical and HTML entities back to original values
	htmlDecode : function(s){

		var c,m,d = s;
		
		if(this.isEmpty(d)) return "";

		// convert HTML entites back to numerical entites first
		d = this.HTML2Numerical(d);
		
		// look for numerical entities &#34;
		arr=d.match(/&#[0-9]{1,5};/g);
		
		// if no matches found in string then skip
		if(arr!=null){
			for(var x=0;x<arr.length;x++){
				m = arr[x];
				c = m.substring(2,m.length-1); //get numeric part which is refernce to unicode character
				// if its a valid number we can decode
				if(c >= -32768 && c <= 65535){
					// decode every single match within string
					d = d.replace(m, String.fromCharCode(c));
				}else{
					d = d.replace(m, ""); //invalid so replace with nada
				}
			}			
		}

		return d;
	},		

	// encode an input string into either numerical or HTML entities
	htmlEncode : function(s,dbl){
			
		if(this.isEmpty(s)) return "";

		// do we allow double encoding? E.g will &amp; be turned into &amp;amp;
		dbl = dbl || false; //default to prevent double encoding
		
		// if allowing double encoding we do ampersands first
		if(dbl){
			if(this.EncodeType=="numerical"){
				s = s.replace(/&/g, "&#38;");
			}else{
				s = s.replace(/&/g, "&amp;");
			}
		}

		// convert the xss chars to numerical entities ' " < >
		s = this.XSSEncode(s,false);
		
		if(this.EncodeType=="numerical" || !dbl){
			// Now call function that will convert any HTML entities to numerical codes
			s = this.HTML2Numerical(s);
		}

		// Now encode all chars above 127 e.g unicode
		s = this.numEncode(s);

		// now we know anything that needs to be encoded has been converted to numerical entities we
		// can encode any ampersands & that are not part of encoded entities
		// to handle the fact that I need to do a negative check and handle multiple ampersands &&&
		// I am going to use a placeholder

		// if we don't want double encoded entities we ignore the & in existing entities
		if(!dbl){
			s = s.replace(/&#/g,"##AMPHASH##");
		
			if(this.EncodeType=="numerical"){
				s = s.replace(/&/g, "&#38;");
			}else{
				s = s.replace(/&/g, "&amp;");
			}

			s = s.replace(/##AMPHASH##/g,"&#");
		}
		
		// replace any malformed entities
		s = s.replace(/&#\d*([^\d;]|$)/g, "$1");

		if(!dbl){
			// safety check to correct any double encoded &amp;
			s = this.correctEncoding(s);
		}

		// now do we need to convert our numerical encoded string into entities
		if(this.EncodeType=="entity"){
			s = this.NumericalToHTML(s);
		}

		return s;					
	},

	// Encodes the basic 4 characters used to malform HTML in XSS hacks
	XSSEncode : function(s,en){
		if(!this.isEmpty(s)){
			en = en || true;
			// do we convert to numerical or html entity?
			if(en){
				s = s.replace(/\'/g,"&#39;"); //no HTML equivalent as &apos is not cross browser supported
				s = s.replace(/\"/g,"&quot;");
				s = s.replace(/</g,"&lt;");
				s = s.replace(/>/g,"&gt;");
			}else{
				s = s.replace(/\'/g,"&#39;"); //no HTML equivalent as &apos is not cross browser supported
				s = s.replace(/\"/g,"&#34;");
				s = s.replace(/</g,"&#60;");
				s = s.replace(/>/g,"&#62;");
			}
			return s;
		}else{
			return "";
		}
	},

	// returns true if a string contains html or numerical encoded entities
	hasEncoded : function(s){
		if(/&#[0-9]{1,5};/g.test(s)){
			return true;
		}else if(/&[A-Z]{2,6};/gi.test(s)){
			return true;
		}else{
			return false;
		}
	},

	// will remove any unicode characters
	stripUnicode : function(s){
		return s.replace(/[^\x20-\x7E]/g,"");
		
	},

	// corrects any double encoded &amp; entities e.g &amp;amp;
	correctEncoding : function(s){
		return s.replace(/(&amp;)(amp;)+/,"$1");
	},


	// Function to loop through an array swaping each item with the value from another array e.g swap HTML entities with Numericals
	swapArrayVals : function(s,arr1,arr2){
		if(this.isEmpty(s)) return "";
		var re;
		if(arr1 && arr2){
			//ShowDebug("in swapArrayVals arr1.length = " + arr1.length + " arr2.length = " + arr2.length)
			// array lengths must match
			if(arr1.length == arr2.length){
				for(var x=0,i=arr1.length;x<i;x++){
					re = new RegExp(arr1[x], 'g');
					s = s.replace(re,arr2[x]); //swap arr1 item with matching item from arr2	
				}
			}
		}
		return s;
	},

	inArray : function( item, arr ) {
		for ( var i = 0, x = arr.length; i < x; i++ ){
			if ( arr[i] === item ){
				return i;
			}
		}
		return -1;
	}

}

Array.prototype.inArray = function (value) {
	var i;
	for (i=0; i < this.length; i++) {
		if (this[i] == value) {
			return true;
			}
		}
	return false;
	};
					
function sort_switcher(thescreen, ticket_id, responder_id, sort_by, filter) {
	if(sort_by == '`ticket_id`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('type')) {$('type').innerHTML = "Type";}			
		if(theSort == '`ticket_id`') {
			theSort = '`ticket_id`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'ticket_id') {
			theSort = 'ticket_id';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`ticket_id`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('ticket').innerHTML = "Tkt &#9660";			
			} else if(theOrder == "DESC") {
			$('ticket').innerHTML = "Tkt &#9650";				
			} else {
			$('ticket').innerHTML = "Tkt &#9660";			
			}	
	} else if(sort_by == '`msg_type`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}
		if($('subject')) {$('subject').innerHTML = "Subject";}	
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}		
		if(theSort == '`msg_type`') {
			theSort = '`msg_type`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'msg_type') {
			theSort = 'msg_type';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`msg_type`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('type').innerHTML = "Typ &#9660";
			} else if(theOrder == "DESC") {
			$('type').innerHTML = "Typ &#9650";			
			} else {
			$('type').innerHTML = "Typ &#9660";			
			}	
	} else if(sort_by == '`fromname`') {
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`fromname`') {
			theSort = '`fromname`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'fromname') {
			theSort = 'fromname';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`fromname`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('fromname').innerHTML = "From&#9660";			
			} else if(theOrder == "DESC") {
			$('fromname').innerHTML = "From&#9650";				
			} else {
			$('fromname').innerHTML = "From&#9660";			
			}	
	} else if(sort_by == '`recipients`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}	
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`recipients`') {
			theSort = '`recipients`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}
			} else if(theSort == 'recipients') {
			theSort = 'recipients';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`recipients`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('recipients').innerHTML = "To &#9660";			
			} else if(theOrder == "DESC") {
			$('recipients').innerHTML = "To &#9650";				
			} else {
			$('recipients').innerHTML = "To &#9660";			
			}				
	} else if(sort_by == '`subject`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('message')) {$('message').innerHTML = "Message";}			
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`subject`') {
			theSort = '`subject`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'subject') {
			theSort = 'subject';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`subject`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('subject').innerHTML = "Subject &#9660";			
			} else if(theOrder == "DESC") {
			$('subject').innerHTML = "Subject &#9650";				
			} else {
			$('subject').innerHTML = "Subject &#9660";			
			}	
	} else if(sort_by == '`message`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}			
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`message`') {
			theSort = '`message`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'message') {
			theSort = 'message';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`message`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('message').innerHTML = "Message &#9660";			
			} else if(theOrder == "DESC") {
			$('message').innerHTML = "Message &#9650";				
			} else {
			$('message').innerHTML = "Message &#9660";			
			}			
	} else if(sort_by == '`date`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`date`') {
			theSort = '`date`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}			
			} else if(theSort == 'date') {
			theSort = 'date';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`date`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('date').innerHTML = "Date &#9660";			
			} else if(theOrder == "DESC") {
			$('date').innerHTML = "Date &#9650";				
			} else {
			$('date').innerHTML = "Date &#9660";			
			}
	} else if(sort_by == '`_by`') {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}	
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('date')) {$('date').innerHTML = "Date";}	
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '`_by`') {
			theSort = '`_by`';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}
			} else if(theSort == '_by') {
			theSort = '_by';
			if(theOrder == "DESC") {
				theOrder = "ASC";
				} else {
				theOrder = "DESC";
				}	
			} else {
			theSort = '`_by`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('owner').innerHTML = "Owner &#9660";			
			} else if(theOrder == "DESC") {
			$('owner').innerHTML = "Owner &#9650";				
			} else {
			$('owner').innerHTML = "Owner &#9660";			
			}	
	} else {
		if($('fromname')) {$('fromname').innerHTML = "From";}	
		if($('recipients')) {$('recipients').innerHTML = "To";}	
		if($('subject')) {$('subject').innerHTML = "Subject";}	
		if($('message')) {$('message').innerHTML = "Message";}		
		if($('type')) {$('type').innerHTML = "Type";}	
		if($('owner')) {$('owner').innerHTML = "Owner";}	
		if($('ticket')) {$('ticket').innerHTML = "Tkt";}			
		if(theSort == '') {
			theSort = '`date`';
			theOrder = "DESC";
			}
		if(theOrder == "") {
			$('date').innerHTML = "Date &#9660";			
			} else if(theOrder == "DESC") {
			$('date').innerHTML = "Date &#9650";				
			} else {
			$('date').innerHTML = "Date &#9660";			
			}	
		}
	if(folder == "inbox") {
		if((theScreen == "main") || (theScreen == "ticket")) {		
			if(theTicket != "") {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);
				} else if((theTicket == "") && (theResponder =="")) {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}
			} else {
			if(theTicket != "") {		
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_main_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}		
			}
		} else if(folder == "wastebasket") {
		if((theScreen == "main") || (theScreen == "ticket")) {		
			if(theTicket != "") {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);
				} else if((theTicket == "") && (theResponder =="")) {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}
			} else {
			if(theTicket != "") {		
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}		
			}		
		} else if(folder == "sent") {
		if((theScreen == "main") || (theScreen == "ticket")) {		
			if(theTicket != "") {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);		
				} else if(theResponder != "") {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}
			} else {
			if(theTicket != "") {		
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);			
				} else if(theResponder != "") {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
				} else {
				}		
			}		
		} else if(folder == "archive") {
		if((theScreen == "main") || (theScreen == "ticket")) {		
			if(theTicket != "") {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else if(theResponder != "") {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else {
				}
			} else {
			if(theTicket != "") {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);			
				} else if(theResponder != "") {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else if((theTicket == "") && (theResponder =="")) {
				get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);	
				} else {
				}		
			}
		}
	}
	
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

function syncAjax(strURL) {
	if (window.XMLHttpRequest) {						 
		AJAX=new XMLHttpRequest();						 
		} 
	else {																 
		AJAX=new ActiveXObject("Microsoft.XMLHTTP");
		}
	if (AJAX) {
		AJAX.open("GET", strURL, false);														 
		AJAX.send(null);
		return AJAX.responseText;																				 
		} 
	else {
		alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
		return false;
		}																						 
	}

function startup() {
	$('date').innerHTML = "Date &#9660";
	}
	
function get_arch_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen, archive) {
	$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
	if((sortby == '`ticket_id`') || (sortby == 'ticket_id')) {
		theSort = 'ticket_id';
		} else if((sortby == '`msg_type`') || (sortby == 'msg_type')) { 
		theSort = 'msg_type';
		} else if((sortby == '`fromname`') || (sortby == 'fromname')) {
		theSort = 'fromname';
		} else if((sortby == '`recipients`') || (sortby == 'recipients')) { 
		theSort = 'recipients';
		} else if((sortby == '`subject`') || (sortby == 'subject')) {
		theSort = 'subject';
		} else if((sortby == '`message`') || (sortby == 'message')) { 
		theSort = 'message';
		} else if((sortby == '`date`') || (sortby == 'date')) {
		theSort = 'date';
		} else if((sortby == '`_by`') || (sortby == '_by')) { 
		theSort = '_by';
		} else {
		theSort = 'date';
		}
	theTicket = ticket_id;
	theResponder = responder_id;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
	var url ='./ajax/list_arch_msgs.php?filename='+archive+'&sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, arch_mess_cb, "");
	function arch_mess_cb(req) {
		var the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		var i=1;
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: normal; font-style: normal;";
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					var the_row = parseInt(the_messages[key][12]) + 1;
					the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_string += "<TR title='" + the_messages[key][11] + "' class='" + colors[i%2] + "' style='border-bottom: 2px solid #000000;'>";
					the_string += "<TD style='width: 4%;'>&nbsp;&nbsp;&nbsp;</TD>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + Encoder.htmlDecode(the_messages[key][6]) + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('archive_message.php?filename=" + archive + "&screen=ticket&folder=archive&rownum=" + the_row + "','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_string += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>&nbsp;&nbsp;&nbsp;</TD>";
					the_string += "</TR>";
					}
				}
			i++;
			}
			the_string += "</TABLE>";
			setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
		}
	}

function get_message_totals() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url ='./ajax/list_message_totals.php?version=' + randomnumber;
	sendRequest (url, msg_tots_cb, "");
	function msg_tots_cb(req) {
		var the_message_totals=JSON.decode(req.responseText);
			var new_in = the_message_totals[0][0];
			var new_out = the_message_totals[0][1];
			if($('inbox_new')) { $('inbox_new').innerHTML = "(" + new_in + ")";}
			if($('sent_new')) { $('sent_new').innerHTML = "(" + new_out + ")";}
		}
	}			
	
function get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	clearInterval(all_msgs_interval);
	clearInterval(sentmsgs_interval);
	$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	folder = "inbox";
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	var theSortstring = (theSort) ? "sort=" + theSort : "sort=`date`";
	var theScreenstring = (theScreen) ? "&screen=" + theScreen : "";	
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	if((theTicket) && (theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder) && (theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if((theFilter) && (theFilter != "")) {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
	var url ='./ajax/list_messages.php?'+theSortstring+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder+theScreenstring+"&version=" + randomnumber;
	sendRequest (url, main_mess_cb, "");
	function main_mess_cb(req) {
		var theNew = 0;
		var the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		var i=1;
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: bold; font-style: normal;";
						theNew++;
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					var the_text = "";
					switch(the_messages[key][12]) {
						case "0":
 							the_text = "Undelivered";
							the_del_flag = "color: red;";
   							break;
 						case "1":
   							the_text = "Partially Delivered";
							the_del_flag = "color: blue;";
   							break;
 						case "2":
 							the_text = "Delivered";
							the_del_flag = "color: green;";
   							break;
						case "3":
							the_text = "Not Applicable";
							the_del_flag = "color: black;";
							break;
 						default:
 							the_text = "Error";
 						}
					var the_delstat = "Delivery Status: " + the_text + " ---- ";
					the_string += "<FORM NAME='messages_form' METHOD='post' ACTION='#'>"
					the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_string += "<TR class='" + colors[i%2] + "' style='border-bottom: 2px solid #000000;'>";
					the_string += "<TD style='width: 4%;'><input type='checkbox' name='" + the_message_id + "' value='" + the_message_id + "' onClick='checkIfChecked();'></TD>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + Encoder.htmlDecode(the_messages[key][6]) + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width=" + datewidth + " style='" + theStatus + "; " + the_del_flag + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=800,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_string += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
					if(thelevel == '1') {
						the_string += "<img src='./images/wastebasket.jpg' style='float: right;' onClick='del_message(" + the_message_id + ", \"inbox\")' alt='Delete' height='23px' width='23px'></TD>";
						} else {
						the_string += "&nbsp;&nbsp;&nbsp;</TD>";
						}
					the_string += "</TR>";
					}
				}
			i++;
			}
		the_string += "</TABLE></FORM>";
		if(($('inbox_new')) && ($('sent_new'))) { get_message_totals(); }
		existing_msgs = the_messages.length;
		setTimeout(function() {$('message_list').innerHTML = the_string;},2000);
		setTimeout(function() {main_messagelist_get(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);},30000);
		}
	}		
	
function main_messagelist_get(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	msgs_interval = window.setInterval('do_main_msgs_loop(\''+theTicket+'\',\''+theResponder+'\',\''+theSort+'\',\''+theOrder+'\',\''+theFilter+'\',\''+theScreen+'\')', 30000);
	}	
	
function do_main_msgs_loop(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	if(!msgs_interval) {clearInterval(msgs_interval);return;}
	folder = "inbox";
	if(existing_msgs == 0) {$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";}
	var randomnumber=Math.floor(Math.random()*99999999);
	var theSortstring = (theSort) ? "sort=" + theSort : "sort=`date`";
	var theScreenstring = (theScreen) ? "&screen=" + theScreen : "";	
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	if((theTicket) && (theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder) && (theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if((theFilter) && (theFilter != "")) {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}		
	var url = './ajax/list_messages.php?'+theSortstring+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder+theScreenstring+"&version=" + randomnumber;
	sendRequest (url, main_msg_cb2, "");
	}

function main_msg_cb2(req) {
	var the_string = "";
	var theNew = 0
	var the_messages=JSON.decode(req.responseText);
	var theClass = "background-color: #CECECE";
	var i=1;
	for(var key in the_messages) {
		if(the_messages[key][0] == "No Messages") {
			the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
			the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
			} else {
			var the_message_id = the_messages[key][0]	
			var the_record_id = the_messages[key][10]				
			if(the_record_id) {	
				if(the_messages[key][9] == 0) {
					theStatus = "font-weight: bold; font-style: normal;";
					theNew++;
					} else {
					theStatus = "font-weight: normal; font-style: normal;";
					}
				var the_text = "";
				switch(the_messages[key][12]) {
					case "0":
						the_text = "Undelivered";
						the_del_flag = "color: red;";
						break;
					case "1":
						the_text = "Partially Delivered";
						the_del_flag = "color: blue;";
						break;
					case "2":
						the_text = "Delivered";
						the_del_flag = "color: green;";
						break;
					case "3":
						the_text = "Not Applicable";
						the_del_flag = "color: black;";
						break;
					default:
						the_text = "Error";
					}
				var the_delstat = "Delivery Status: " + the_text + " ---- ";
				the_string += "<FORM NAME='messages_form' METHOD='post' ACTION='#'>"
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
				the_string += "<TR class='" + colors[i%2] + "' style='border-bottom: 2px solid #000000;'>";
				the_string += "<TD style='width: 4%;'><input type='checkbox' name='" + the_message_id + "' value='" + the_message_id + "' onClick='checkIfChecked();'></TD>";
				if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
				if(the_columns.inArray('2')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
				if(the_columns.inArray('3')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
				if(the_columns.inArray('4')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
				if(the_columns.inArray('5')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
				if(the_columns.inArray('6')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + Encoder.htmlDecode(the_messages[key][6]) + "</DIV></TD>";}
				if(the_columns.inArray('7')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width=" + datewidth + " style='" + theStatus + "; " + the_del_flag + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=800,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
				if(the_columns.inArray('8')) {the_string += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
				the_string += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
				if(thelevel == '1') {
					the_string += "<img src='./images/wastebasket.jpg' style='float: right;' onClick='del_message(" + the_message_id + ", \"inbox\")' alt='Delete' height='23px' width='23px'></TD>";
					} else {
					the_string += "&nbsp;&nbsp;&nbsp;</TD>";
					}
				the_string += "</TR>";
				}
			}
		i++;
		}
	the_string += "</TABLE></FORM>";
	get_message_totals();
	if(the_messages.length != existing_msgs) {
		if(!showing_sent && !showing_archive && !showing_waste) {
			setTimeout(function() {$('message_list').innerHTML = the_string; existing_msgs = the_messages.length;},30000);
			}
		}
	}
	
function expand(id) {
	var the_msg = "M" + id;
	var the_control = "C" + id;
	if($(the_msg).style.height == 'auto') {
		$(the_msg).style.height = '14px';
		$(the_control).innerHTML = "&#9660";
		} else {
		$(the_msg).style.height = 'auto';
		$(the_control).innerHTML = "&#9650";		
		}
	}
	
function do_filter(folder) {
	filter = document.the_filter.frm_filter.value;
	theFilter = filter;
	if(filter == "") {
		} else {
		if(folder == "inbox") {
			get_main_messagelist(theTicket,theResponder,theSort, theOrder,theFilter,theScreen);
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			} else if(folder == "sent") {
			get_sent_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			} else if(folder == "wastebasket") {
			get_wastelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);	
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			} else if(folder == "archive") {
			get_arch_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen, archive);				
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			} else if(folder == "all") {
			get_all_messagelist(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);				
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";				
			} else {
			get_main_messagelist(theTicket,theResponder,theSort, theOrder,theFilter,theScreen);
			$('the_clear').style.display = "inline";
			$('filter_box').style.display = "none";	
			}
		}
	}
	
function clear_filter(folder) {
	if(folder == "inbox") {
		if(msgs_interval) {
			get_main_messagelist(theTicket,theResponder,theSort, theOrder,'', theScreen);
			}
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		} else if(folder == "sent") {
		if(sentmsgs_interval) {
			get_sent_messagelist(theTicket, theResponder, theSort, theOrder, '', theScreen);
			}
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		} else if(folder == "wastebasket") {
		get_wastelist(theTicket, theResponder, theSort, theOrder, '', theScreen);
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		} else if(folder == "archive") {
		get_arch_messagelist(theTicket, theResponder, theSort, theOrder, '', theScreen, archive);
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		} else if(folder == "all") {
		if(all_msgs_interval) {
			get_all_messagelist(theTicket, theResponder, theSort, theOrder, '', theScreen);
			}
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";		
		} else {	
//		get_main_messagelist(theTicket,theResponder,theSort, theOrder,'', theScreen);		
		$('the_clear').style.display = "none";	
		$('filter_box').style.display = "inline";			
		document.the_filter.frm_filter.value = "";
		theFilter = "";
		}		
	}
	
function select_ticket(ticket_id, filter) {
	theTicket = ticket_id;
	get_main_messagelist(ticket_id, responder_id,sortby,'DESC', filter, thescreen);
	the_ticket = ticket_id;
	$('filter_box').onclick = do_filter(the_ticket);
	$('the_clear').onclick = clear_filter(the_ticket);	
	}
	
function read_status(thestatus, id, thescreen) {
	if((!folder) || (folder == "")) {
		return false;
		}
	$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/msg_status.php?status=" + thestatus + "&id=" + id + "&folder=" + folder + "&version=" + randomnumber;
	sendRequest (url, msgstat_cb, "");
	function msgstat_cb(req) {
		var theresp=JSON.decode(req.responseText);
		if(theresp[0] == 100) {
			if(folder == 'inbox') {
				get_inbox();
				} else if(folder == 'sent') {
				get_sent();
				} else {
				get_inbox();
				}
			} else {
			}
		}
	$('chk_control').checked = false;
	checkIfChecked();
	}
	
function read_status_selected(thestatus, id, thescreen) {
	var message_status_string = "";
	var sep = "|";
	if((!folder) || (folder == "")) {
		return false;
		}
	for (i=0;i<document.messages_form.elements.length; i++) {
		if((document.messages_form.elements[i].type =='checkbox') && (document.messages_form.elements[i].checked)){
			var the_val = document.messages_form.elements[i].value;
			message_status_string += the_val + sep;
			}
		}
	$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
	message_status_string = message_status_string.substring(0, message_status_string.length - 1);
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/msg_status.php?selected=" + message_status_string + "&status=" + thestatus + "&id=" + id + "&folder=" + folder + "&version=" + randomnumber;
	sendRequest (url, msgstat_cb, "");
	function msgstat_cb(req) {
		var theresp=JSON.decode(req.responseText);
		if(theresp[0] == 100) {
			if(folder == 'inbox') {
				get_inbox();
				} else if(folder == 'sent') {
				get_sent();
				} else {
				get_inbox();
				}
			} else {
			}
		}
	$('chk_control').checked = false;
	checkIfChecked();
	}

function refresh_opener(the_screen, thefolder, ticket_id, responder_id, facility_id, mi_id, sort, dir) {
	if(the_screen == "ticket") {
		if(thefolder== "inbox") {
			window.opener.get_mainmessages(ticket_id, responder_id, facility_id, mi_id, sort, dir, thefolder);
			} else if(thefolder== "sent") {
			window.opener.get_mainmessages(ticket_id, responder_id, facility_id, mi_id, sort, dir, thefolder);	
			} else {
			}
		} else if (the_screen == "messages") {
		get_mainmessages();
		} else {
		get_mainmessages();
		}
	}

function refresh_waste(the_screen) {
	if(the_screen = "ticket") {
		window.opener.get_wastebin();
		} else if (the_screen = "messages") {
		get_wastebin();
		} else {
		get_wastebin();
		}
	}

function del_message(id, folder) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/del_message.php?id=" + id + "&version=" + randomnumber;	
	if (confirm("Are you sure you want to delete this message?")) { 
		$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
		sendRequest (url, msgdel_cb, "");
		}
	function msgdel_cb(req) {
		var resp=JSON.decode(req.responseText);
		if(resp[0] == 100) {
			if(folder == 'inbox') {
				get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
				} else if(folder == 'sent') {
				get_sent_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);
				} else {
				get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
				}
			} else {
			alert("Error deleting the message, please try again.");
			}
		}
	$('chk_control').checked = false;
	checkIfChecked();
	}

function del_all_messages() {
 	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/del_messages.php?version=" + randomnumber;	
	if (confirm("Are you sure you want to delete all the messages?")) {
		$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
		sendRequest (url, msgsdel_cb, "");
		}
	function msgsdel_cb(req) {
		var resp=JSON.decode(req.responseText);
		if(resp[0] == 100) {
			get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
			} else {
			alert("Error deleting messages, please try again.");
			}
		}
	$('chk_control').checked = false;
	checkIfChecked();
	}
	
function del_selected_messages() {
	var message_del_string = "";
	var sep = "|";
	for (i=0;i<document.messages_form.elements.length; i++) {
		if((document.messages_form.elements[i].type =='checkbox') && (document.messages_form.elements[i].checked)){
			var the_val = document.messages_form.elements[i].value;
			message_del_string += the_val + sep;
			}
		}
	$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
	message_del_string = message_del_string.substring(0, message_del_string.length - 1);
 	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/del_sel_messages.php?messages=" + message_del_string + "&version=" + randomnumber;
 	if (confirm("Are you sure you want to delete the selected messages?")) { 	
		sendRequest (url, msgsdel_cb, "");
		}
	function msgsdel_cb(req) {
		var resp=JSON.decode(req.responseText);
		var output = "";
		for (i = 0; i < resp.length; i++) {
			if(resp[i][0] == 100) {output += "Message " + resp[i][1] + " Deleted -- ";} else {output += "Message " + resp[i][1] + " Not deleted -- ";}
			}
		get_main_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
		if($('status_box')) {
			$('status_box').innerHTML = "<marquee direction='left' style='font-size: 1em; font-weight: bold;'>" + output + "</marquee>";
			setTimeout(function() {$('status_box').innerHTML = " ";},15000);			
			}
		}
	$('chk_control').checked = false;
	checkIfChecked();
	}
	
function rest_selected_messages() {
	var message_rest_string = "";
	var sep = "|";
	for (i=0;i<document.messages_form.elements.length; i++) {
		if((document.messages_form.elements[i].type =='checkbox') && (document.messages_form.elements[i].checked)){
			var the_val = document.messages_form.elements[i].value;
			message_rest_string += the_val + sep;
			}
		}
	$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
	message_rest_string = message_rest_string.substring(0, message_rest_string.length - 1);
 	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/restore_sel_messages.php?messages=" + message_rest_string + "&version=" + randomnumber;
 	if (confirm("Are you sure you want to restore the selected messages?")) { 	
		sendRequest (url, msgsrest_cb, "");
		}
	function msgsrest_cb(req) {
		var resp=JSON.decode(req.responseText);
		var output = "";
		for (i = 0; i < resp.length; i++) {
			if(resp[i][0] == 100) {output += "Message " + resp[i][1] + " Restore -- ";} else {output += "Message " + resp[i][1] + " Not restored -- ";}
			}
		get_wastelist(ticket_id, responder_id, sortby, sort, filter, thescreen);
		if($('status_box')) {
			$('status_box').innerHTML = "<marquee direction='left' style='font-size: 1em; font-weight: bold;'>" + output + "</marquee>";
			setTimeout(function() {$('status_box').innerHTML = " ";},15000);			
			}
		}
	$('chk_control').checked = false;
	checkIfChecked();
	}
	
function toggle_select_all() {
	for (i=0;i<document.messages_form.elements.length; i++) {
		if((document.messages_form.elements[i].type =='checkbox')){
			if(document.messages_form.elements[i].checked == true) {document.messages_form.elements[i].checked = false;} else {document.messages_form.elements[i].checked = true;}
			}
		}
	checkIfChecked();	
	}
	
function deadButton(id) {
	$(id).className = "plain_inactive";
	$(id).onclick = function(){null};
	$(id).onmouseover = function(){null};
	$(id).onmouseout = function(){null};
	}
	
function aliveButton(id) {
	$(id).className = "plain";
	if(id == "sel_unread_but") {
		$(id).onclick = function() {read_status_selected("unread", 0, "messages");};
		} else if(id == "sel_read_but") {
		$(id).onclick = function() {read_status_selected("read", 0, "messages");};
		} else if(id == "del_sel") {
		$(id).onclick = function() {del_selected_messages();};
		} else if(id == "empty_waste") {
		$(id).onclick = function() {empty_waste();};
		} else if(id == "rest_sel_but") {
		$(id).onclick = function() {rest_selected_messages();};
		}
	$(id).onmouseover = function() {do_hover(id); };
	$(id).onmouseout = function() {do_plain(id); };
	}
	
function checkIfChecked() {
	var counter = 0;
	for (i=0;i<document.messages_form.elements.length; i++) {
		if((document.messages_form.elements[i].type =='checkbox')){
			if(document.messages_form.elements[i].checked == true) {
				counter++;
				}
			}
		}
//		read_status_selected(status, id, thescreen, ticket_id, responder_id)
	if(counter > 0) {
		aliveButton("sel_unread_but");
		aliveButton("sel_read_but");
		aliveButton("del_sel");		
		if(window.showing_waste) {if($('rest_sel_but')) {aliveButton("rest_sel_but");}}
		return true;
		} else {
		deadButton("sel_unread_but");
		deadButton("sel_read_but");
		deadButton("del_sel");	
		if(window.showing_waste) {if($('rest_sel_but')) {deadButton("rest_sel_but");}}
		return false;
		}
	}
	
function empty_waste() {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/empty_wastebasket.php?version=" + randomnumber;	
	if (confirm("Are you sure you want to empty the wastebin?")) { 	
		sendRequest (url, emp_waste_cb, "");
		}
	function emp_waste_cb(req) {
		var resp=JSON.decode(req.responseText);
		if(resp[0] == 100) {
			alert("Wastebasket Emptied");
			get_wastelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
			} else {
			alert("Error emptying the wastebasket, please try again.");			
			}
		}
	checkIfChecked();
	}
	
function restore_msg(id, folder) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url="./ajax/restore_message.php?id=" + id + "&version=" + randomnumber;	
	if (confirm("Are you sure you want to restore this message?")) { 	
		sendRequest (url, restore_cb, "");
		}
	function restore_cb(req) {
		var resp=JSON.decode(req.responseText);
		if(resp[0] == 100) {
			get_wastelist(ticket_id, responder_id, sortby, sort, filter, thescreen);	
			} else {
			alert("Error restoring the message, please try again.");			
			}
		}
	checkIfChecked();
	}
	
function get_wastelist(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	clearInterval(msgs_interval);
	clearInterval(all_msgs_interval);
	clearInterval(sentmsgs_interval);
	$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
	var datewidth = "8%";
	if(screen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((ticket_id != "") && (ticket_id != 0)) { 
		the_selected_ticket = "&ticket_id=" + ticket_id;
		} else {
		the_selected_ticket = "";
		}
	if((responder_id != "") && (responder_id != 0)) { 
		the_selected_responder = "&responder_id=" + responder_id;
		} else {
		the_selected_responder = "";
		}		
	if(filter != "") {
		thefilter = "&filter=" + filter;
		} else {
		thefilter = "";
		}	
	var url ='./ajax/list_waste_messages.php?sort='+sortby+'&columns='+columns+'&way='+sort+thefilter+the_selected_ticket+the_selected_responder+"&screen=" + thescreen + "&version=" + randomnumber;
	sendRequest (url, waste_mess_cb, "");
	function waste_mess_cb(req) {
		var the_string = "";
		var the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		var theStatus = "font-weight: normal";
		var i=1;
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				if($('empty_waste')) { $('empty_waste').style.display = "none";}
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: normal; font-style: normal;";
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					the_string += "<FORM NAME='messages_form' METHOD='post' ACTION='#'>"
					the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_string += "<TR class='" + colors[i%2] + "' style='border-bottom: 2px solid #000000;'>";
					the_string += "<TD style='width: 4%;'><input type='checkbox' name='" + the_message_id + "' value='" + the_message_id + "' onClick='checkIfChecked();'></TD>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + Encoder.htmlDecode(the_messages[key][6]) + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&wastebasket=true','view_message','width=600,height=800','titlebar, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_string += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
					the_string += "<img src='./images/restore.jpg' style='float: right;' onClick='restore_msg(" + the_message_id + ", \"inbox\")' alt='Restore' height='23px' width='23px'></TD>";			
					the_string += "</TR>";
					if($('empty_waste')) { $('empty_waste').style.display = "inline-block";}
					}
				}
			i++;
			}
		the_string += "</TABLE></FORM>";
		if(!showing_inbox && !showing_archive && !showing_sent) {
			setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
			}
		}
	}	

function get_sent_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	clearInterval(msgs_interval);
	clearInterval(all_msgs_interval);
	var the_sentstring = "";
	$('message_list').innerHTML = "";
	$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	var theSortstring = (theSort) ? "sort=" + theSort : "sort=`date`";
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	var theScreenstring = (theScreen) ? "&screen=" + theScreen : "";	
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket) && (theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder) && (theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if((theFilter) && (theFilter != "")) {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
	var url ='./ajax/list_sent_messages.php?'+theSortstring+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder+theScreenstring+"&version=" + randomnumber;
	sendRequest (url, sent_mess_cb, "");
	function sent_mess_cb(req) {
		var theNew = 0;
		var the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		var i=1;
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				the_sentstring += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_sentstring += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_sentstring += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: bold; font-style: normal;";
						theNew++;
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					var the_text = "";
					switch(the_messages[key][12]) {
						case "0":
 							the_text = "Undelivered";
							the_del_flag = "color: red;";
   							break;
 						case "1":
   							the_text = "Partially Delivered";
							the_del_flag = "color: blue;";
   							break;
 						case "2":
 							the_text = "Delivered";
							the_del_flag = "color: green;";
   							break;
						case "3":
							the_text = "Not Applicable";
							the_del_flag = "color: black;";
							break;
 						default:
 							the_text = "Error";
 						}
					var the_delstat = "Delivery Status: " + the_text + " ---- ";
					the_sentstring += "<FORM NAME='messages_form' METHOD='post' ACTION='#'>"
					the_sentstring += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_sentstring += "<TR class='" + colors[i%2] + "' style='border-bottom: 2px solid #000000;'>";
					the_sentstring += "<TD style='width: 4%;'><input type='checkbox' name='" + the_message_id + "' value='" + the_message_id + "' onClick='checkIfChecked();'></TD>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + Encoder.htmlDecode(the_messages[key][6]) + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width=" + datewidth + " style='" + theStatus + "; " + the_del_flag + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_sentstring += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
					if(thelevel == '1') {
						the_sentstring += "<img src='./images/wastebasket.jpg' style='float: right;' onClick='del_message(" + the_message_id + ", \"inbox\")' alt='Delete' height='23px' width='23px'></TD>";
						} else {
						the_sentstring += "&nbsp;&nbsp;&nbsp;</TD>";
						}
					the_sentstring += "</TR>";
					}
				}
			i++;
			}
			the_sentstring += "</TABLE></FORM>";
			existing_msgs = the_messages.length;
			get_message_totals();
			setTimeout(function() {$('message_list').innerHTML = the_sentstring ;},1000);
			setTimeout(function() {sent_messagelist_get(theTicket, theResponder, theSort, theOrder, theFilter, theScreen)}, 5000);			
		}
	}		
	
function sent_messagelist_get(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	sentmsgs_interval = window.setInterval('do_sent_msgs_loop(\''+theTicket+'\',\''+theResponder+'\',\''+theSort+'\',\''+theOrder+'\',\''+theFilter+'\',\''+theScreen+'\')', 5000);
	}	
	
function do_sent_msgs_loop(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	if(!sentmsgs_interval) {return;}
	folder = "sent";
	if(existing_msgs == 0) {$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";}
	var theSortstring = (theSort) ? "sort=" + theSort : "sort=`date`";
	var theScreenstring = (theScreen) ? "&screen=" + theScreen : "";	
	if(thescreen == "ticket") {
		datewidth = "10%";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket) && (theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder) && (theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if((theFilter) && (theFilter != "")) {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
		var url = './ajax/list_sent_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder+theScreenstring+"&version=" + randomnumber;
	sendRequest (url, sent_msg_cb2, "");
	}

function sent_msg_cb2(req) {
	var theNew = 0;
	var the_sentstring = "";	
	var the_messages=JSON.decode(req.responseText);
	var theClass = "background-color: #CECECE";
	var i=1;
	for(var key in the_messages) {
		if(the_messages[key][0] == "No Messages") {
			the_sentstring += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
			the_sentstring += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_sentstring += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
			} else {
			var the_message_id = the_messages[key][0]	
			var the_record_id = the_messages[key][10]				
			if(the_record_id) {	
				if(the_messages[key][9] == 0) {
					theStatus = "font-weight: bold; font-style: normal;";
					theNew++;
					} else {
					theStatus = "font-weight: normal; font-style: normal;";
					}
				var the_text = "";
				switch(the_messages[key][12]) {
					case "0":
						the_text = "Undelivered";
						the_del_flag = "color: red;";
						break;
					case "1":
						the_text = "Partially Delivered";
						the_del_flag = "color: blue;";
						break;
					case "2":
						the_text = "Delivered";
						the_del_flag = "color: green;";
						break;
					case "3":
						the_text = "Not Applicable";
						the_del_flag = "color: black;";
						break;
					default:
						the_text = "Error";
					}
				var the_delstat = "Delivery Status: " + the_text + " ---- ";
				the_sentstring += "<FORM NAME='messages_form' METHOD='post' ACTION='#'>"
				the_sentstring += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
				the_sentstring += "<TR class='" + colors[i%2] + "' style='border-bottom: 2px solid #000000;'>";
				the_sentstring += "<TD style='width: 4%;'><input type='checkbox' name='" + the_message_id + "' value='" + the_message_id + "' onClick='checkIfChecked();'></TD>";
				if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_sentstring += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
				if(the_columns.inArray('2')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
				if(the_columns.inArray('3')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
				if(the_columns.inArray('4')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
				if(the_columns.inArray('5')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
				if(the_columns.inArray('6')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + Encoder.htmlDecode(the_messages[key][6]) + "</DIV></TD>";}
				if(the_columns.inArray('7')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width=" + datewidth + " style='" + theStatus + "; " + the_del_flag + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
				if(the_columns.inArray('8')) {the_sentstring += "<TD title='" + the_delstat + the_messages[key][11] + "' class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=sent','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
				the_sentstring += "<TD class='cols' width='3%' style='vertical-align: top;" + theStatus + ";'>";
				if(thelevel == '1') {
					the_sentstring += "<img src='./images/wastebasket.jpg' style='float: right;' onClick='del_message(" + the_message_id + ", \"inbox\")' alt='Delete' height='23px' width='23px'></TD>";
					} else {
					the_sentstring += "&nbsp;&nbsp;&nbsp;</TD>";
					}
				the_sentstring += "</TR>";
				}
			}
		i++;
		}
	the_sentstring += "</TABLE></FORM>";
	get_message_totals();
	if(the_messages.length != existing_msgs) {
		if(!showing_inbox && !showing_archive && !showing_waste) {
			setTimeout(function() {$('message_list').innerHTML = the_sentstring; existing_msgs = the_messages.length;},1000);
			}
		}
	}
	
function get_all_messagelist(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	folder="all";
	window.clearInterval(all_msgs_interval);
	window.clearInterval(msgs_interval);
	window.clearInterval(sentmsgs_interval);
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	if(theScreen == "ticket") {
		datewidth = "10%";
		}
	var the_string = "";	
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
	var url ='./ajax/list_all_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, all_mess_cb, "");
	function all_mess_cb(req) {
		var the_messages=JSON.decode(req.responseText);
		var theClass = "background-color: #CECECE";
		var i=1;
		for(var key in the_messages) {
			if(the_messages[key][0] == "No Messages") {
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
				the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
				the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
				} else {
				var the_message_id = the_messages[key][0]	
				var the_record_id = the_messages[key][10]				
				if(the_record_id) {	
					if(the_messages[key][9] == 0) {
						theStatus = "font-weight: bold; font-style: normal;";
						} else {
						theStatus = "font-weight: normal; font-style: normal;";
						}
					var the_text = "";
					switch(the_messages[key][12]) {
						case "0":
 							the_text = "Undelivered";
							the_del_flag = "color: red;";
   							break;
 						case "1":
   							the_text = "Partially Delivered";
							the_del_flag = "color: blue;";
   							break;
 						case "2":
 							the_text = "Delivered";
							the_del_flag = "color: green;";
   							break;
						case "3":
							the_text = "Not Applicable";
							the_del_flag = "color: black;";
							break;
 						default:
 							the_text = "Error";
 						}
					var the_delstat = "Delivery Status: " + the_text + " ---- ";
					the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
					the_string += "<TR class='" + colors[i%2] + "' title='" + the_delstat + the_messages[key][11] + "' style='border-bottom: 2px solid #000000;'>";
					if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
					if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
					if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
					if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
					if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
					if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + Encoder.htmlDecode(the_messages[key][6]) + "</DIV></TD>";}
					if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; " + the_del_flag + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=800,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
					if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0, toolbar=0, menubar=0, location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
					the_string += "</TR>";
					}
				}
			i++;
			}
			the_string += "</TABLE>";
			$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
			setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
			all_messagelist_get(theTicket, theResponder, theSort, theOrder, theFilter, theScreen);			
		}
	}		
	
function all_messagelist_get(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	theTicket = ticket_id;
	theResponder = responder_id;
	theSort = sortby;
	theOrder = sort;
	theFilter = filter;
	theScreen = thescreen;
	all_msgs_interval = window.setInterval('do_all_msgs_loop(\''+theTicket+'\',\''+theResponder+'\',\''+theSort+'\',\''+theOrder+'\',\''+theFilter+'\',\''+theScreen+'\')', 60000);
	}	
	
function do_all_msgs_loop(ticket_id, responder_id, sortby, sort, filter, thescreen) {
	folder = "all";
	if(thescreen == "ticket") {
		datewidth = "10%";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	if((theTicket != "") && (theTicket != 0)) { 
		the_selected_ticket = "&ticket_id=" + theTicket;
		} else {
		the_selected_ticket = "";
		}
	if((theResponder != "") && (theResponder != 0)) { 
		the_selected_responder = "&responder_id=" + theResponder;
		} else {
		the_selected_responder = "";
		}		
	if(theFilter != "") {
		thefilter = "&filter=" + theFilter;
		} else {
		thefilter = "";
		}
		var url = './ajax/list_all_messages.php?sort='+theSort+'&columns='+columns+'&way='+theOrder+thefilter+the_selected_ticket+the_selected_responder + "&screen=" + theScreen + "&version=" + randomnumber;
	sendRequest (url, all_msg_cb, "");
	}

function all_msg_cb(req) {
	folder="all";
	var the_string = "";	
	var the_messages=JSON.decode(req.responseText);
	var theClass = "background-color: #CECECE";
	var i=1;
	for(var key in the_messages) {
		if(the_messages[key][0] == "No Messages") {
			the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
			the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
			the_string += "<TD width='100%' style='font-weight: bold; font-size: 18px; text-align: center;'>No Messages</TD></TR>";
			} else {
			var the_message_id = the_messages[key][0]	
			var the_record_id = the_messages[key][10]				
			if(the_record_id) {	
				if(the_messages[key][9] == 0) {
					theStatus = "font-weight: bold; font-style: normal;";
					} else {
					theStatus = "font-weight: normal; font-style: normal;";
					}		
				the_string += "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";			
				the_string += "<TR class='" + colors[i%2] + "' style='border-bottom: 2px solid #000000;'>";
				if((the_columns.inArray('1')) && (thescreen != 'ticket')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"open_tick_window(" + the_messages[key][1] + ");\">" + the_messages[key][1] + "</TD>";}
				if(the_columns.inArray('2')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][2] + "</TD>";}
				if(the_columns.inArray('3')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][3] + "</TD>";}
				if(the_columns.inArray('4')) {the_string += "<TD class='cols' width='5%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][4] + "</TD>";}
				if(the_columns.inArray('5')) {the_string += "<TD class='cols' width='16%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][5] + "</TD>";}
				if(the_columns.inArray('6')) {the_string += "<TD class='msg_col' width='40%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"><DIV class='msg_div'>" + Encoder.htmlDecode(the_messages[key][6]) + "</DIV></TD>";}
				if(the_columns.inArray('7')) {the_string += "<TD class='cols' width=" + datewidth + " style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=800,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][7] + "</TD>";}
				if(the_columns.inArray('8')) {the_string += "<TD class='cols' width='7%' style='" + theStatus + "; white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap;' onClick=\"window.open('message.php?id=" + the_message_id + "&screen=ticket&folder=inbox','view_message','width=600,height=800,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_messages[key][8] + "</TD>";}		
				the_string += "</TR>";
				}
			}
		i++;
		}
		the_string += "</TABLE>";
		$('message_list').innerHTML = "<CENTER><BR /><BR /><BR /><BR /><BR /><IMG src='./images/animated_spinner.gif'></CENTER>";
		setTimeout(function() {$('message_list').innerHTML = the_string;},1000);
	}