<?php
/**
* XMLToArray Generator Class
* @author  :  MA Razzaque Rupom <rupom_315@yahoo.com>, <rupom.bd@gmail.com>
*             Moderator, phpResource (http://groups.yahoo.com/group/phpresource/)
*             URL: http://www.rupom.info  
* @version :  1.0
* @date       06/05/2006
* Purpose  : Creating Hierarchical Array from XML Data
* Released : Under GPL
*/
require_once('functions.inc.php');

require_once "class.xmltoarray.php";

//XML Data
//$xml_data = "
//<result>
//   <studentname>
//      MA Razzaque
//   </studentname>
//   <institute>
//      RUET
//   </institute>
//   <dept>
//      CSE
//   </dept>
//   <roll>
//      99315
//   </roll>
//   <class>
//      First
//   </class>
//</result>";
//
//Creating Instance of the Class
//$xmlObj    = new XmlToArray($xml_data);
$xmlObj    = new XmlToArray(get_stuff("./kml_files/Western_Md.kml"));
//Creating Array
$arrayData = $xmlObj->createArray();

//Displaying the Array 
echo "<pre>";
print_r($arrayData);
echo "</pre>";
?>