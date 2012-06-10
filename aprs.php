<?php
/*
6/25/08	- initial implementation
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/

@session_start();
require_once('./incs/functions.inc.php');		//7/28/10
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$self = "http://$host$uri/";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Configuration Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->

<script type="text/javascript">
//<![CDATA[

  /**
   * Center Coordinates & Display Size
   *
   * Change the following variables to control the map center point,
   * display width and height.
   *
   * NOTES:
   *        o Center coordinates should specified as "<longitude>,<latitude>"
   *        o If openaprs_center AND openaprs_find are both set the initial
   *          map center will be openaprs_center, but the map will shift
   *          if openaprs_find is found.
   *        o Make sure when specifying height and width that the suffix
   *          is either "%" or "px" only.
   *        o Latitude/Longitude values should not have leading 0's
   *          for example "-077.3893847" is in error, it should be
   *          "-77.3893847".
   *        o Make sure to set openaprs_me to your webpage that has the
   *          embeded map to be included in our "Sites Using OpenAPRS" page.
   *        o To load a saved view set the openaprs_find variable to
   *          "load:<KEY>", an example of this would be:
   *
   *          var openaprs_find = "load:7ef7ee10f6b95020101a4ea42a8a68a8";
   *
   *          To learn more about saving views click the options button
   *          on our front page.
   *
   * [CHANGE VARIABLES BELOW]
   */
  // var openaprs_find="callsign-here";
  var openaprs_center = "<?php print get_variable('def_lat');?>,<?php print get_variable('def_lng');?>";
  var openaprs_width = "500px";
  var openaprs_height = "400px";
  var openaprs_units = "metric";
  var openaprs_zoom = "<?php print get_variable('def_zoom');?>";
  var openaprs_timezone = "America/New_York";
  var openaprs_me = "<?php print $self;?>";

//]]>
</script>
</HEAD><BODY>
  <div id="embededOpenAPRSMap">
    <script type="text/javascript" src="http://www.openaprs.net/embed/embed.js">
    </script>
  </div>
</BODY></HTML>
