<?php
/*	include this file immediately after the <body> tag in any script in which a links menu should be opened */

/*
9/18/09	initial release
*/
error_reporting(E_ALL);			// 9/13/08

$the_keys = array("func_key1", "func_key2", "func_key3");		
$str="";

for ($i=0; $i<count($the_keys); $i++) {
	$func_key = explode(",", get_variable($the_keys[$i]));			//8/5/09
	if (count($func_key) == 2) {
//		$str.="<SPAN id = 'func_key{$i}'  CLASS = 'unselected'  onMouseover = \"ChngClass(this.id, 'selected')\" onMouseout = \"ChngClass(this.id, 'unselected')\"	onClick = 'launch(\"$func_key[0]\", \"$func_key[1]\")'>$func_key[1]&nbsp;&nbsp;&nbsp;&nbsp;</SPAN>\n";
		$str.="<A ID = 'func_key{$i}'  CLASS = 'unselected' href='$func_key[0]' target='$func_key[1]' onMouseover = \"ChngClass(this.id, 'selected')\" onMouseout = \"ChngClass(this.id, 'unselected')\" onClick = 'launch(\"$func_key[0]\", \"$func_key[1]\")'; return false;\">$func_key[1]</a>\n";

		}
	}
?>
<CENTER>
<DIV ID='links' STYLE="display:none;"><?php print $str;?> <br/> <br/></DIV><!-- display:none/block/inline -->


<SCRIPT>
	function ChngClass(obj, the_class){					// changes object obj to class the_class - 8/24/09
        $(obj).className=the_class;
        }

	var wor; 				// global variable - window reference
	var PreviousUrl; 		// global variable - will hold the url currently in the opened window for this link

	starting = false;

	function launch(strUrl, strName ) {
		var random_x=100 + Math.floor(Math.random()*200);
		var random_y=100 + Math.floor(Math.random(random_x)*200);	// a different random
		var strFeat = "titlebar, location=0, resizable=1, scrollbars, height=640,width=800,status=1,toolbar=1,menubar=1,location=0, left=" + random_x + ",top=" + random_y + ",screenX=" + random_x + ",screenY=" + random_y;

		if(wor == null || wor.closed) {
			wor = window.open(strUrl, strName, strFeat);
			}
		else if(previousUrl != strUrl) {
			wor = window.open(strUrl, strName, strFeat);
			if(wor.focus) {	wor.focus();};
			}
		else { if(wor.focus) { wor.focus();	};
			};
		PreviousUrl = strUrl;	/* explanation: we store the current url in order to compare url in the event of another call of this function. */
		$('links').style.display='none';		// hide links
		}		// end function launch()
</SCRIPT>		
</CENTER>
