<?php
/*
2/9/09 inintial version
2/22/09 per revised schema
2/23/09 added responder position update
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
	
@session_start();
require_once($_SESSION['fip']);		//7/28/10
	extract ($_POST);	
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
//	var params = "frm_lat=" + escape(the_lat) + "&frm_lng=" + escape(the_lng) + "&frm_id=" + escape(the_id);

	$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (`source`, `latitude`, `longitude`,  `updated`,  `from`)
					VALUES (%s,%s,%s,%s,%s)",
						quote_smart($frm_id),
						quote_smart($frm_lat),
						quote_smart($frm_lng),
						quote_smart($now),
						quote_smart($_SERVER['REMOTE_ADDR']));

	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//	if (!(result= mysql_query($query)))  {
//		snap(basename( __FILE__) . __LINE__, $query);
//		print "-error";							// notify browser
//		}
//	else {
		print " " . mysql_insert_id();	
//		}
																 // 2/23/09
	$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
		`lat`= " . 			quote_smart($frm_lat) . ",
		`lng`= " . 			quote_smart($frm_lng) . ",		
		`user_id`= " . 		quote_smart(trim($_SESSION['user_id'])) . ",
		`updated`= " . 		quote_smart($now) . " 
		WHERE `id`= " . 	quote_smart($frm_id) . ";";

//	dump ($query);	
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);


/*
// -- Table structure for table `tracks_hh`		- 2/8/09
// -- 		
		$table_name = prefix("tracks_hh");
		$query = "CREATE TABLE `$table_name` (
		  `id` bigint(7) NOT NULL auto_increment,
		  `source` int(3) NOT NULL default '0',
		  `latitude` double default NULL,
		  `longitude` double default NULL,
		  `speed` int(8) default NULL,
		  `course` int(8) default NULL,
		  `altitude` int(8) default NULL,
		  `status` varchar(96) default NULL,
		  `updated` datetime NOT NULL,
		  `from` varchar(16) NOT NULL COMMENT 'ip addr',
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=59 ;
*/		  
?>
