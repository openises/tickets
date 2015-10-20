<?php
#
# external.php - XML interface - from Tickets to xml.
#

 
error_reporting(E_ALL);
require_once('incs/functions.inc.php');		//7/28/10

$query_01 = "SELECT `$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
		`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.problemstart AS `problemstart`,
		`$GLOBALS[mysql_prefix]ticket`.scope AS `title`,
		IF (`$GLOBALS[mysql_prefix]ticket`.status = '2', 'Open', 'Scheduled') AS `status`,
		`$GLOBALS[mysql_prefix]ticket`.severity AS `severity`,
		`$GLOBALS[mysql_prefix]ticket`.booked_date AS `booked_date`,
		`$GLOBALS[mysql_prefix]in_types`.type AS `type`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id`  
		WHERE `status`='{$GLOBALS['STATUS_OPEN']}' OR `status`='{$GLOBALS['STATUS_SCHEDULED']}'";
$result_01 = mysql_query($query_01) or do_error($query_01, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_rows01 = mysql_num_rows($result_01);

$query_02 = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
$result_02 = mysql_query($query_02) or do_error($query_02, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

$query_03 = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`";
$result_03 = mysql_query($query_03) or do_error($query_03, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

$query_04 = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";
$result_04 = mysql_query($query_04) or do_error($query_04, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_rows04 = mysql_num_rows($result_04);

if(($num_rows01 != 0) && ($num_rows04 != 0)) {
//	$current_status = "Team Status : On Call - " . $num_rows04 . " members are deployed.";
	$current_status = "Team Status : On Call";
	} elseif (($num_rows01 != 0) && ($num_rows04 == 0)) {
	$current_status = "Team Status : Standby.";
	} else {
	$current_status = "Team Status : Stood Down.";
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
ob_clean(); 
header("Content-type: text/xml");
$XML = "<?xml version=\"1.0\"?>\n";
if ($xslt_file) $XML .= "<?xml-stylesheet href=\"$xslt_file\" type=\"text/xsl\" ?>";

$XML .= "<result>\n";
if((isset($_GET['list']) && (($_GET['list'] == "tickets") || ($_GET['list'] == "all"))) || (!isset($_GET['list'])))  {
	$i=1;
	$XML .= "<incidents>\n";
	while ($row_01 = mysql_fetch_assoc($result_01)) {    
	  $XML .= "\t<incident id='$i'>\n"; 
	  foreach ($row_01 as $col_name => $cell) {
		if($col_name=="severity") {
			switch ($cell) {
			case 0:
				$cell="Normal";
				break;
			case 1:
				$cell="Medium";
				break;
			case 2:
				$cell="High";
				break;
			default;
				$cell='Error';
				break;
			}
		}
		$XML .= "\t\t".output_xml_field($col_name,$cell)."\n";
	  }
	  $XML .= "\t</incident>\n";
	  $i++;  
	}
	$XML .= "</incidents>\n";
}
if((isset($_GET['list']) && (($_GET['list'] == "responders") || ($_GET['list'] == "all"))) || (!isset($_GET['list'])))  {
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
}
if((isset($_GET['list']) && (($_GET['list'] == "facilities") || ($_GET['list'] == "all"))) || (!isset($_GET['list'])))  {
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
}
if((isset($_GET['list']) && (($_GET['list'] == "status") || ($_GET['list'] == "all"))) || (!isset($_GET['list'])))  {
	$XML .= "<status>\n";
	$XML .= $current_status."\n";
	$XML .= "</status>\n";
}
$XML .= "</result>\n";
 
echo $XML;
?>
