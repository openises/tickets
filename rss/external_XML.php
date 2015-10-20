<?php
error_reporting(E_ALL);
require_once('../incs/functions.inc.php');

$query_01 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`";
$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_rows01 = mysql_num_rows(result_01);

$query_02 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
$result_02 = mysql_query($query_02) or do_error($query_02, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

$query_03 = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`";
$result_03 = mysql_query($query_03) or do_error($query_03, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

$query_04 = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";
$result_04 = mysql_query($query_04) or do_error($query_04, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_rows04 = mysql_num_rows(result_04);

if(($num_rows01 != 0) && ($num_rows04 != 0)) {
	$current_status = "Active";
	} elseif (($num_rows01 != 0) && ($num_rows04 == 0)) {
	$current_status = "Standby";
	} else {
	$current_status = "Inactive";
	}

#----------------------------------------------- 
function output_xml_field($col_name,$value){

	$value = str_replace('&', '&amp;',	$value);
    $value = str_replace('<', '&lt;',	$value);
    $value = str_replace('>', '&gt;',	$value);
    $value = str_replace('"', '&quot;',	$value);
    return '<'.$col_name.'>'.$value.'</'.$col_name.'>';
}
#----------------------------------------------- 

// Optional: add the name of XSLT file.
// $xslt_file = "mysql-result.xsl"; 
$xslt_file = ""; 
 
header("Content-type: text/xml");
$XML = "<?xml version=\"1.0\"?>\n";
if ($xslt_file) $XML .= "<?xml-stylesheet href=\"$xslt_file\" type=\"text/xsl\" ?>";

$XML .= "<result>\n";
$i=1;
$XML .= "<incidents>\n";
while ($row_01 = mysql_fetch_assoc($result_01)) {    
  $XML .= "\t<incident id='$i'>\n"; 
  foreach ($row_01 as $col_name => $cell) {
    $XML .= "\t\t".output_xml_field($col_name,$cell)."\n";
  }
  $XML .= "\t</incident>\n";
  $i++;  
}
$XML .= "</incidents>\n";
$XML .= "<responders>\n";
$r=1;
while ($row_02 = mysql_fetch_assoc($result_02)) {    
  $XML .= "\t<responder id='$r'>\n"; 
  foreach ($row_02 as $col_name => $cell) {
    $XML .= "\t\t".output_xml_field($col_name,$cell)."\n";
  }
  $XML .= "\t</responder>\n";
  $r++;
}
$XML .= "</responders>\n";
$XML .= "<facilities>\n";
$f=1;
while ($row_03 = mysql_fetch_assoc($result_03)) {    
	$XML .= "\t<facility id='$f'>\n"; 
	foreach ($row_03 as $col_name => $cell) {
		$XML .= "\t\t".output_xml_field($col_name,$cell)."\n";
		}
	$XML .= "\t</facility>\n";
	$f++;
	}
$XML .= "</facilities>\n";
$XML .= "<status>\n";
$XML .= $current_status . "\n";
$XML .= "</status>\n";
$XML .= "</result>\n";
 
echo $XML;
?>
