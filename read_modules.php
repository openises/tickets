<?php
require_once('./incs/functions.inc.php');

function get_mod_to_install() {
	$to_install = array();
	$current = array();
	$query = "SELECT `mod_name` FROM $GLOBALS[mysql_prefix]modules`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$current[] = $row['mod_name'];
		}
			
	$entry = array();
	$path = "./modules";
	if ($handle = opendir($path)) {
		while (false !== ($dirname = readdir($handle))) {
			if ($dirname != "." && $dirname != "..") {
				$entry[] = $dirname;
				}
			}
		closedir($handle);
		}
	foreach ($entry as $val) {
		if(!(in_array($val, $current))) {
			$to_install[] = $val;
			}
		}
	$ret_str = "<SELECT NAME='frm_module'>";
	$i=1;
	foreach($to_install as $val) {
		$ret_str .= "<OPTION VALUE=" . $i . ">" . $val . "</OPTION>";
		}
	$ret_str .= "</SELECT>";
	return $ret_str;
	}

print get_mod_to_install();
?>
