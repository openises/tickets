<?php 
require_once('functions.inc.php'); 

//	dump($_GET);	// (505) 266-4450
//	$url = "http://www.google.com/search?q=4108498721";
//	$url = "http://www.google.com/search?q=Arnold+Shore+Annapolis%2C+MD";
	$url = "http://www.google.com/search?q=". urlencode($_GET['qq']);

	$data = "";
	if (function_exists("curl_init")) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec ($ch);
		curl_close ($ch);
		}
	else {
		if ($fp = @fopen($url, "r")) {
			while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
			fclose($fp);
			}
		else { 
			print "-error 1";
			}
		}
		
	$thelhe =  strpos ( $data, "Phonebook");
//	dump ($thelhe);
	if ($thelhe > 0) {								// got it!
		$str = substr($data, $thelhe, 500);				// substr (string, start, length )
//		dump ($str);

		$str0 = str_replace ('&nbsp;', ' ', $str);
		$str1 = strip_tags($str0, '<td>');				// keep separator
		$str2 = str_replace ('<td>', '\t', $str1);
		$str3 = strip_tags($str2);
		$arr = trim_deep(explode('\t', $str3, 10));
		array_shift($arr);								// 1st element is trash - drop it
//		dump ($arr);
		$str3 = implode  (';', $arr );
//		dump ($str3);
		print $str3;									// finished
		}		// end got it!
	else { 
		print "-error 2 " . strlen($data) . $url;
		}				// not found

/*
array(4) {
  [0]=>
  string(105) "Phonebook results for 5052664450#mr{width:40%}#res #mr{width:auto}"
  [1]=>
  string(14) "Barney Metzner"
  [2]=>
  string(14) "(505) 266-4450"
  [3]=>
  string(57) "3033 San Joaquin Ave SE,  Albuquerque, NM 87106"
}
*/
?>
